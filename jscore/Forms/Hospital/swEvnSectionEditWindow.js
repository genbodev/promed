/**
 * swEvnSectionEditWindow - окно редактирования/добавления случая движения пациента в стационаре.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Stas Bykov aka Savage (savage@swan.perm.ru)
 * @version      0.001-17.03.2010
 * @comment      Префикс для id компонентов ESecEF (EvnSectionEditForm)
 *
 *
 * @input data: action - действие (add, edit, view)
 *              EvnSection_id - ID случая движения для редактирования или просмотра
 *              EvnSection_id - ID родительского события
 *              Person_id - ID человека
 *              PersonEvn_id - ID состояния человека
 *              Server_id - ID сервера
 *
 *
 * Использует: окно редактирования диагноза в стационаре (swEvnDiagPSEditWindow)
 */
sw.Promed.swEvnSectionEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	editAnatom: false,
	editPersonNewBorn: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	codeRefresh: true,
	objectName: 'swEvnSectionEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnSectionEditWindow.js',
	collapsible: true,
	flbr: false,
	changedDates: false,
	evnSectionIsFirst: false,
	EvnUslugaGridIsModified: false,
	formStatus: 'edit',
	height: 550,
	id: 'EvnSectionEditWindow',
	/*tempExpertVals: {},
	tempValsPatoExpert: function(action) {
		if (!action.inlist(['set', 'get'])) {
			return true;
		}

		var _this = this,
			base_form = this.findById('EvnSectionEditForm').getForm();

		switch (action){
			case 'set':

				//Востанавливаем значения блока 'паталогоанатомическая экспертиза'
				['EvnDie_expDate', 'EvnDie_expTime', 'Org_aid', 'AnatomWhere_id', 'Diag_aid', 'LpuSection_aid', 'MedStaffFact_aid'].forEach(function(el){
					if (_this.tempExpertVals[el] && Ext.isEmpty(base_form.findField(el).getValue())){
						base_form.findField(el).setValue(_this.tempExpertVals[el]);
					}
				});
				break;
			case 'get':

				//Сохраняем значения блока 'паталогоанатомическая экспертиза'
				['AnatomWhere_id', 'Diag_aid', 'EvnDie_expDate', 'EvnDie_expTime', 'LpuSection_aid', 'MedStaffFact_aid', 'Org_aid'].forEach(function(el){
					if (!Ext.isEmpty(base_form.findField(el).getValue())){
						_this.tempExpertVals[el] = base_form.findField(el).getValue();
					}
				});
				break;
		}
	},*/
	setDiagSpidComboDisabled: function() {

		if (!getRegionNick().inlist(['perm', 'msk']) || this.action == 'view') return false;

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var diag_spid_combo = base_form.findField('Diag_spid');
		var iszno_checkbox = this.findById('ESecEF_EvnSection_IsZNOCheckbox');

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
		var base_form = this.findById('EvnSectionEditForm').getForm();
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
									Person_Firname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname'),
									Person_Surname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname'),
									Person_Secname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname'),
									Person_Birthday: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
									EvnPLDispScreenOnko_id: response_obj[0],
									callback: function() {
										me.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnSection_id').getValue()}});
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
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var Evn_pid = base_form.findField('EvnSection_id').getValue();
		if (Ext.isEmpty(Evn_pid) || Evn_pid == 0) {
			this.doSave({
				openChildWindow: function () {
					me._addNewEvnPLDispScreenOnko();
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
						Person_Firname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname'),
						Person_Surname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname'),
						Person_Secname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname'),
						Person_Birthday: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
						EvnPLDispScreenOnko_id: response_obj['EvnPLDispScreenOnko_id'],
						callback: function() {
							me.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnSection_id').getValue()}});
						}
					}
					getWnd('swEvnPLDispScreenOnkoWindow').show(params);
				}
			}
		});

	},
	openEvnPLDispScreenOnko: function(){
		var me = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
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
			Person_Firname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_Surname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname'),
			Person_Secname: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Birthday: me.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			EvnPLDispScreenOnko_id: rec.get('EvnPLDispScreenOnko_id'),
			callback: function() {
				me.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnSection_id').getValue()}});
			}
		}
		getWnd('swEvnPLDispScreenOnkoWindow').show(params);
	},
	deleteEvnPLDispScreenOnko: function(){
		var me = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
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
					me.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnSection_id').getValue()}});
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
	deleteRepositoryObserv: function(){
		var me = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid = this.RepositoryObservGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();

		if (!rec || !rec.get('RepositoryObserv_id')) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление..."});
		loadMask.show();

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();

				if (success) {
					me.RepositoryObservGrid.loadData({
						globalFilters: {
							Evn_id: base_form.findField('EvnSection_id').getValue()
						}
					});
				}
				else {
					sw.swMsg.alert('Ошибка', 'При удалении возникли ошибки');
					return false;
				}
			}.createDelegate(this),
			params: {
				RepositoryObserv_id: rec.get('RepositoryObserv_id')
			},
			url: '/?c=RepositoryObserv&m=delete'
		});
	},
	printRepositoryObserv: function(){
		var grid = this.RepositoryObservGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();

		if (!rec || !rec.get('RepositoryObserv_id')) {
			return false;
		}
		
		printBirt({
			'Report_FileName': 'printObserv_covid_daily.rptdesign',
			'Report_Params': '&RepositoryObserv_id=' + rec.get('RepositoryObserv_id'),
			'Report_Format': 'pdf'
		});
	},
	getBirthSpecStacDefaults: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var cat_form = this.WizardPanel.getCategory('Result').getForm();
		var oldValues = cat_form.getValues();
		//Параметры исхода, которые можно рассчитать на клиенте
		var values = {
			Lpu_oid: getGlobalOptions().lpu_id,
			MedPersonal_oid: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
			AbortLpuPlaceType_id: Ext.isEmpty(oldValues.AbortLpuPlaceType_id) ? oldValues.AbortLpuPlaceType_id : 2,
			BirthCharactType_id: Ext.isEmpty(oldValues.BirthCharactType_id) ? oldValues.BirthCharactType_id : 1,
			QuestionType_521: oldValues.QuestionType_521,
			QuestionType_522: oldValues.QuestionType_522,
			QuestionType_523: oldValues.QuestionType_523,
			QuestionType_532: oldValues.QuestionType_532,
			QuestionType_540: oldValues.QuestionType_540,
			QuestionType_541: oldValues.QuestionType_541
		};

		var diag_list = [];

		var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
		if (!Ext.isEmpty(diag_code)) {
			diag_list.push(diag_code);

			if ((diag_code.slice(0, 3) >= 'O81' && diag_code.slice(0, 3) <= 'O83') || (diag_code >= 'O84.1' && diag_code <= 'O84.8')) {
				values.BirthCharactType_id = 2;
			}
		}
		this.findById('ESecEF_EvnDiagPSGrid').getStore().each(function (rec) {
			diag_list.push(rec.get('Diag_Code'));
		});

		diag_list.forEach(function (code) {
			if (code.slice(0, 3) == 'O15') {
				values.QuestionType_521 = 2;
			}
			if (code.slice(0, 3) == 'O42') {
				values.QuestionType_522 = 2;
			}
			if (code >= 'O62.0' && code <= 'O62.2') {
				values.QuestionType_523 = 2;
			}
			if (code == 'O62.3') {
				values.QuestionType_524 = 2;
			}
			if (code.slice(0, 3) == 'O45') {
				values.QuestionType_532 = 2;
			}
			if (code == 'O69.0') {
				values.QuestionType_540 = 2;
			}
			if (code == 'O69.1') {
				values.QuestionType_541 = 2;
			}
		});

		return values;
	},
	ONMKDiagCode: ['G45','I60','I61','I62','I63','I64'],
	getONMKDiag: function() {
		var DiagRecepCombo = this.findById(this.id + '_DiagCombo');
		var arr_diag = DiagRecepCombo.getCode().split('.');

		if (arr_diag[0].inlist(this.ONMKDiagCode) && DiagRecepCombo.getCode() != "G45.3")
			return { id: DiagRecepCombo.getValue(), name: DiagRecepCombo.getRawValue() };
		return false;
	},
	recalcBirthSpecStacDefaults: function () {
		if (this.WizardPanel) {
			var category = this.WizardPanel.getCategory('Result');
			var cat_form = category.getForm();

			if (category.loaded) {
				cat_form.findField('MedPersonal_oid').reset();

				var values = Ext.apply(cat_form.getValues(), this.getBirthSpecStacDefaults());
				cat_form.setValues(values);
			}
		}
	},
	changeDiag: function (diagCombo, value) {
		var store = this.findById('dataViewDiag').getStore();
		store.clearFilter();
		var evn_diag_ps_id = this.findById('EvnSectionEditForm').getForm().findField('EvnDiagPS_id').getValue();
		var indexDiag = store.findBy(function (rec) {
			return rec.get('EvnDiagPS_id') == evn_diag_ps_id
		});
		var record = store.getAt(indexDiag);

		var index = diagCombo.getStore().findBy(function (rec) {
			return rec.get('Diag_id') == value
		});

		if (index >= 0 && record != null) {
			record.set('Diag_id', diagCombo.getStore().getAt(index).get('Diag_id'));
			record.set('Diag_Name', diagCombo.getStore().getAt(index).get('Diag_Name'));
			record.set('Diag_Code', diagCombo.getStore().getAt(index).get('Diag_Code'));
			if (record.get('RecordStatus_Code') == 1) {
				record.set('RecordStatus_Code', 2);
			}
			record.commit();
		}
		this.filterDS();
	},
	createEvnDiagCopyMenu: function () {
		var wnd = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var btn = Ext.getCmp('EvnDiagCopyButton');
		btn.menu = new Ext.menu.Menu();
		var menu = btn.menu;
		var params = new Object();

		params.EvnDiagPS_rid = base_form.findField('EvnSection_pid').getValue();
		params.EvnDiagPS_pid = base_form.findField('EvnSection_id').getValue();

		btn.disable();
		btn.EvnDiagPS_id = null;
		btn.handler = Ext.emptyFn;

		Ext.Ajax.request({
			params: params,
			url: '/?c=EvnDiag&m=loadEvnDiagForCopy',
			success: function (result_form, action) {
				var diag_list = Ext.util.JSON.decode(result_form.responseText);
				if (diag_list.length == 0) {
					btn.disable();
				} else if (diag_list.length == 1) {
					btn.enable();
					btn.EvnDiagPS_id = diag_list[0].EvnDiagPS_id;
					btn.handler = function () {
						wnd.doCopyEvnDiagPS(this.EvnDiagPS_id);
					}
				} else {
					btn.enable();
					for (i = 0; i < diag_list.length; i++) {
						menu.add({
							id: 'EvnDiagPS_' + diag_list[i].EvnDiagPS_id,
							text: diag_list[i].Diag_FullName,
							EvnDiagPS_id: diag_list[i].EvnDiagPS_id,
							handler: function () {
								wnd.doCopyEvnDiagPS(this.EvnDiagPS_id);
							}
						});
					}
				}
			}.createDelegate(this)
		});
	},
	deleteClinDiag: function (event, id) {
		var that = this;
		var store = this.findById('dataViewDiag').getStore();
		var index = store.findBy(function (record, idd) {
			return idd == id;
		});
		var record = store.getAt(index);
		if (this.action == 'edit') {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
						loadMask.show();
						if (record.get('RecordStatus_Code') != 0) {
							loadMask.hide();
							record.set('RecordStatus_Code', 3);
							record.commit();
							this.filterDS();
						} else {
							loadMask.hide();
							store.removeAt(index);
							this.filterDS();
						}
					}
					else {
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Удалить диагноз?',
				title: 'Вопрос'
			});
		}
	},
	doCopyEvnDiagPS: function (evn_diag_ps_id) {
		var wnd = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var params = new Object();

		params.EvnDiagPS_id = evn_diag_ps_id;
		params.EvnDiagPS_pid = base_form.findField('EvnSection_id').getValue();
		params["class"] = 'EvnDiagPSSect';
		if (Ext.isEmpty(params.EvnDiagPS_pid) || params.EvnDiagPS_pid == 0 || this.changedDates == true) {
			this.doSave({
				openChildWindow: function () {
					wnd.changedDates = false;
					wnd.doCopyEvnDiagPS(params.EvnDiagPS_id);
				}
			});
			return;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Копирование диагноза"});
		loadMask.show();

		Ext.Ajax.request({
			params: params,
			url: '/?c=EvnDiag&m=copyEvnDiagPS',
			failure: function () {
				loadMask.hide();
			},
			success: function () {
				loadMask.hide();
				this.createEvnDiagCopyMenu();
				this.findById('ESecEF_EvnDiagPSGrid').getStore().load({
					params: {
						"class": params["class"],
						EvnDiagPS_pid: params.EvnDiagPS_pid
					}
				});
			}.createDelegate(this)
		});
	},
	/*HTMedicalCareDiagStore: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{ name: 'HTMedicalCareDiag_id', type: 'int' },
			{ name: 'HTMedicalCareClass_id', type: 'int' },
			{ name: 'Diag_id', type: 'int' }
		],
		key: 'HTMedicalCareDiag_id',
		tableName: 'HTMedicalCareDiag'
	}),
	filterHTMedicalCareClassCombo: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		if ( this.HTMedicalCareDiagStore.getCount() == 0 ) {
			this.HTMedicalCareDiagStore.load({
				callback: function {
					this.filterHTMedicalCareClassCombo();
				}.createDelegate(this)
			});
			return false;
		}

		var
			Diag_id = base_form.findField('Diag_id').getValue(),
			HTMedicalCareClass_id = base_form.findField('HTMedicalCareClass_id').getValue(),
			HTMedicalCareClassIdList = new Array();

		base_form.findField('HTMedicalCareClass_id').clearValue();

		if ( !Ext.isEmpty(Diag_id) ) {
			this.HTMedicalCareDiagStore.each(function(rec) {
				if ( rec.get('Diag_id') == Diag_id ) {
					HTMedicalCareClassIdList.push(rec.get('HTMedicalCareClass_id'));
				}
			});
		}

		base_form.findField('HTMedicalCareClass_id').getStore().filterBy(function(rec) {
			return (rec.get('HTMedicalCareClass_id').inlist(HTMedicalCareClassIdList));
		});

		var index = base_form.findField('HTMedicalCareClass_id').getStore().findBy(function(rec) {
			return (rec.get('HTMedicalCareClass_id') == HTMedicalCareClass_id);
		});

		if ( index >= 0 ) {
			base_form.findField('HTMedicalCareClass_id').setValue(HTMedicalCareClass_id);
		}
	},*/
	loadHTMedicalCareClassCombo: function (options, allowLoad) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var combo = base_form.findField('HTMedicalCareClass_id');
		var params = new Object();

		if ( !allowLoad && !combo.allowLoad ) {
			return false;
		}

		var diag_ids = [];
		if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
			diag_ids.push(base_form.findField('Diag_id').getValue());
		}

		if (getRegionNick().inlist(['ufa', 'astra', 'kareliya', 'krym', 'perm', 'pskov'])){
			this.findById('ESecEF_EvnDiagPSGrid').getStore().each(function (record) {
				if (!Ext.isEmpty(record.get('Diag_id')) && record.get('DiagSetClass_id').inlist([2, 3])) {
					diag_ids.push(record.get('Diag_id'));
				}
			});
		}
		if (diag_ids.length > 0) {
			params.Diag_ids = Ext.util.JSON.encode(diag_ids);
		}

		if (getRegionNick().inlist(['ufa', 'astra', 'kareliya', 'krym', 'perm', 'pskov'])){
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}

		params.begDate = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y');

		combo.getStore().load({
			callback: function () {
				if (combo.getStore().indexOfId(combo.getValue()) < 0) {
					combo.clearValue();
				}

				if (typeof options.callback == 'function') {
					options.callback();
				}
				else {
					combo.fireEvent('change', combo, combo.getValue());
				}
			},
			params: params
		});
	},

	getPregnancyPersonRegister: function (callback) {
		callback = callback || Ext.emptyFn;
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var params = {
			Person_id: base_form.findField('Person_id').getValue(),
			EvnSection_id: base_form.findField('EvnSection_id').getValue(),
			EvnSection_setDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
			EvnSection_disDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y')
		};

		Ext.Ajax.request({
			url: '/?c=PersonPregnancy&m=getPersonRegisterByEvnSection',
			params: params,
			callback: function (options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) {
					this.PersonRegister_id = response_obj.PersonRegister_id || null;
					callback();
				}
			}.createDelegate(this)
		});
	},

	deleteEvent: function (event) {
		var that = this;

		if (typeof event != 'string' || !event.toString().inlist(['EvnDiagPS', 'EvnSectionDrugPSLink', 'EvnDiagPSDie', 'EvnSectionNarrowBed', 'EvnUsluga','TransfusionFact'])) {
			return false;
		}
		if (event == 'EvnDiagPSDie') {
			if (this.action == 'view' && this.editAnatom == false) {
				return false;
			}

			var grid = this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid();

			if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDiagPS_id')) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						switch (Number(record.get('RecordStatus_Code'))) {
							case 0:
								grid.getStore().remove(record);
								break;

							case 1:
							case 2:
								record.set('RecordStatus_Code', 3);
								record.commit();

								grid.getStore().filterBy(function (rec) {
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
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Удалить диагноз?',
				title: 'Вопрос'
			});

		}
		else {
			if (this.action == 'view') {
				return false;
			}
			var base_form = this.findById('EvnSectionEditForm').getForm();
			var error = '';
			var grid = null;
			var question = '';
			var params = new Object();
			var url = '';

			switch (event) {
				case 'TransfusionFact':
					error = 'При случая переливания крови возникли ошибки';
					grid = this.findById('ESecEF_TransfusionGrid');
					question = 'Удалить случай переливания крови?';
					url = '/?c=EvnSection&m=deleteTransfusionFact';
					break;
				case 'EvnDiagPS':
					error = 'При удалении диагноза возникли ошибки';
					grid = this.findById('ESecEF_EvnDiagPSGrid');
					question = 'Удалить диагноз?';
					url = '/?c=EvnDiag&m=deleteEvnDiag';
					break;
				case 'EvnSectionDrugPSLink':
					error = 'При удалении медикамента/мероприятия возникли ошибки';
					grid = this.findById('ESecEF_EvnSectionDrugPSLinkGrid');
					question = 'Удалить медикамент/мероприятие?';
					url = '/?c=EvnSectionDrugPSLink&m=deleteEvnSectionDrugPSLink';
					break;
				case 'EvnUsluga':
					error = 'При удалении услуги возникли ошибки';
					grid = this.findById('ESecEF_EvnUslugaGrid');
					question = 'Удалить услугу?';
					url = '/?c=EvnUsluga&m=deleteEvnUsluga';
					break;
				case 'EvnSectionNarrowBed':
					error = 'При удалении профилей коек возникли ошибки';
					grid = this.findById('ESecEF_EvnSectionNarrowBedGrid');
					question = 'Удалить профиль коек?';
					url = '/?c=EvnSectionNarrowBed&m=deleteEvnSectionNarrowBed';
					break;
			}
			if (!grid || !grid.getSelectionModel().getSelected()) {
				return false;
			}
			else if (!grid.getSelectionModel().getSelected().get(event + '_id')) {
				return false;
			}
			var selected_record = grid.getSelectionModel().getSelected();

			if (selected_record.get('EvnClass_SysNick') == 'EvnUslugaPar') {
				return false;
			}

			switch (event) {
				case 'TransfusionFact':
					params['TransfusionFact_id'] = selected_record.get('TransfusionFact_id');
					break;
				case 'EvnDiagPS':
					params['class'] = 'EvnDiagPS';
					params['id'] = selected_record.get('EvnDiagPS_id');
					break;
				case 'EvnSectionDrugPSLink':
					params['EvnSectionDrugPSLink_id'] = selected_record.get('EvnSectionDrugPSLink_id');
					break;
				case 'EvnUsluga':
					params['class'] = selected_record.get('EvnClass_SysNick');
					params['id'] = selected_record.get('EvnUsluga_id');

					if (getRegionNick() == 'perm' && base_form.findField('EvnSection_RepFlag').checked) {
						params.ignorePaidCheck = 1;
					}
					break;
				case 'EvnSectionNarrowBed':
					params['EvnSectionNarrowBed_id'] = selected_record.get('EvnSectionNarrowBed_id');
					break;
			}
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
						loadMask.show();

						Ext.Ajax.request({
							failure: function (response, options) {
								loadMask.hide();
								sw.swMsg.alert('Ошибка', error);
							},
							params: params,
							success: function (response, options) {
								loadMask.hide();

								var response_obj = Ext.util.JSON.decode(response.responseText);

								if (response_obj.success == false) {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
								}
								else {
									grid.getStore().remove(selected_record);

									if (event == 'EvnUsluga') {
										this.EvnUslugaGridIsModified = true;
									}

									if (grid.getStore().getCount() == 0) {
										grid.getTopToolbar().items.items[1].disable();
										grid.getTopToolbar().items.items[2].disable();
										grid.getTopToolbar().items.items[3].disable();
										LoadEmptyRow(grid);
									}

									if (event.inlist(['EvnUsluga', 'EvnDiagPS', 'EvnSectionNarrowBed'])) {
										that.loadKSGKPGKOEF();
										that.loadEvnSectionKSGGrid();
									}
									if (event == 'EvnDiagPS') {
										that.createEvnDiagCopyMenu();
										that.loadHTMedicalCareClassCombo();
										that.loadSpecificsTree();
										that.checkMesOldUslugaComplexFields();
									}
									if (event == 'EvnUsluga') {
										that.checkMesOldUslugaComplexFields();
									}
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							},
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
		}
	},
	setEvnSectionDiag: function (options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var params = {
			EvnSection_id: base_form.findField('EvnSection_id').getValue(),
			MedPersonal_id: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
			MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
			Diag_id: base_form.findField('Diag_id').getValue()
		};

		params.ignoreCheckMorbusOnko = (options && !Ext.isEmpty(options.ignoreCheckMorbusOnko) && options.ignoreCheckMorbusOnko === 1) ? 1 : 0;

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение диагноза..."});
		loadMask.show();
		Ext.Ajax.request({
			callback: function (opt, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.Alert_Msg && 'YesNo' == response_obj.Error_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId, text, obj) {
								if (buttonId == 'yes') {
									if (response_obj.Error_Code == 289) {
										options.ignoreCheckMorbusOnko = 1;
									}
									win.setEvnSectionDiag(options);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: response_obj.Alert_Msg,
							title: 'Продолжить сохранение?'
						});
					} else if (!response_obj.Error_Msg && options.callback && typeof options.callback == 'function') {
						options.callback();
					}
				} else {
					sw.swMsg.alert('Ошибка', 'При сохранении диагноза');
				}
			},
			params: params,
			url: '/?c=EvnSection&m=setEvnSectionDiag'
		});
	},
	saveDrugTherapyScheme: function (options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var params = {
			EvnSection_id: base_form.findField('EvnSection_id').getValue(),
			DrugTherapyScheme_ids: this.DrugTherapySchemePanel.getIds()
		};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение схем лекарственного лечения..."});
		loadMask.show();

		Ext.Ajax.request({
			callback: function (opt, success, response) {
				loadMask.hide();

				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (options.callback && typeof options.callback == 'function') {
						options.callback();
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении схем лекарственного лечения');
				}
			},
			params: params,
			url: '/?c=EvnSection&m=saveDrugTherapyScheme'
		});
	},
	doSave: function (options) {
		var that = this;
		var params = new Object();

		if(!that.control_departmentProfile_typeOfPayment_typeOfHospitalization()) return false;

		// options @Object
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		if (this.formStatus == 'save' || (this.action == 'view' && this.editAnatom == false)) {
			return false;
		}

		if (typeof options != 'object') {
			options = new Object();
		}

		this.formStatus = 'save';
		if (options.isPersonNewBorn) {
			params.isPersonNewBorn = options.isPersonNewBorn;
		}
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var tree = this.specificsTree;

		var isNotPerm = (getGlobalOptions().region && getGlobalOptions().region.nick != 'perm');
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
		var isPerm = (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm');


		if (getRegionNick() == 'ufa') {
			if (!options.ignoreIsPaid) {
				if (!Ext.isEmpty(base_form.findField('EvnSection_IsPaid').getValue()) && base_form.findField('EvnSection_IsPaid').getValue() == 2) {
					this.formStatus = 'edit';
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								options.ignoreIsPaid = true;
								this.doSave(options);
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: 'Данный случай оплачен, Вы действительно хотите внести изменения?',
						title: 'Продолжить сохранение?'
					});
					return false;
				}
			}
		}

		var index, LpuSectionProfile_Code = '', lpu_section_id = base_form.findField('LpuSection_id').getValue();

		index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
			return (rec.get('LpuSection_id') == lpu_section_id);
		});

		if (index >= 0) {
			LpuSectionProfile_Code = base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSectionProfile_Code');
		}

		if (isUfa && LpuSectionProfile_Code.inlist(['1086', '1073', '1072', '1066', '2066', '3066', '1084', '2084', '3084'])) {
			var childForm = this.findById('ESecEF_PersonChildForm');
			if (this.editPersonNewBorn && !this.checkChildWeight()) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function () {
						this.formStatus = 'edit';
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: "Не указан вес при рождении.",
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		var diagGroup = base_form.findField('Diag_id').getRawValue()[0];
		var traumaField = base_form.findField('PrehospTrauma_id');
		if (
			!Ext.isEmpty(diagGroup) && diagGroup.inlist(['S', 'T'])
			&& (base_form.findField('Diag_id').getRawValue().substr(0, 2) != "T9" || isUfa)
			&& this.AT != ''
			&& !traumaField.getValue()
		) {
			getWnd('swSelectFromSprWindow').show({
				comboSubject: 'PrehospTrauma',
				callback: function (val) {
					params = {
						id: base_form.findField('EvnSection_pid').getValue(),
						object: 'EvnPS',
						param_name: 'PrehospTrauma_id',
						param_value: val
					}
					Ext.Ajax.request({
						failure: function (response, options) {
							sw.swMsg.alert('Ошибка', 'Ошибка');
							this.formStatus = 'edit';
							return false
						},
						params: params,
						success: function (response, options) {
							traumaField.setValue(val);
							that.doSave()
							//this.formStatus = 'edit';
							//return false
						},
						url: '/?c=EvnVizit&m=setEvnVizitParameter'
					});

				}
			});
			this.formStatus = 'edit';
			return false;
		}

		if (!this.findById('HTMedicalCareClass').isVisible()) {
			base_form.findField('HTMedicalCareClass_id').clearValue();
			base_form.findField('HTMedicalCareClass_id').fireEvent('change', base_form.findField('HTMedicalCareClass_id'), base_form.findField('HTMedicalCareClass_id').getValue());
		}

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
					log(this.findById('EvnSectionEditForm').getFirstInvalidEl(), 123)
					this.findById('EvnSectionEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			this.getInvalidFields();
			return false;
		}
		if (this.WizardPanel && !this.WizardPanel.isValid()) {
			this.formStatus = 'edit';
			return false;
		}

		if (
			!Ext.isEmpty(base_form.findField('EvnSection_KoikoDni').getValue())
			&& !Ext.isEmpty(base_form.findField('EvnSection_Absence').getValue())
			&& base_form.findField('EvnSection_KoikoDni').getValue() < 0
		) {
			sw.swMsg.alert('Ошибка', 'Внимание! Количество дней, которые отсутствовал пациент, не должно превышать общее количество дней, фактически проведенных в стационаре. Проверьте данные, указанные в полях: «Дата поступления», «Дата выписки», «Отсутствовал (дней)»');
			this.formStatus = 'edit';
			return false;
		}

		if (
			!Ext.isEmpty(base_form.findField('EvnSection_Absence').getValue())
			&& !base_form.findField('EvnSection_Absence').getValue() > 0
		) {
			sw.swMsg.alert('Ошибка', 'Внимание! Количество дней, которые отсутствовал пациент, не может быть меньше 0. Проверьте данные, указанные в полe: «Отсутствовал (дней)»');
			this.formStatus = 'edit';
			return false;
		}

		var
			EvnSection_IsAdultEscort = base_form.findField('EvnSection_IsAdultEscort').getValue(),
			EvnSection_IsMedReason = base_form.findField('EvnSection_IsMedReason').getValue(),
			Person_Age = swGetPersonAge(this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());

		if (!options.ignoreAdultEscortValue && EvnSection_IsAdultEscort == 2 && Person_Age >= 4 && EvnSection_IsMedReason == 1) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						options.ignoreAdultEscortValue = true;
						this.doSave(options);
					} else {
						base_form.findField('EvnSection_IsAdultEscort').focus(true);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Внимание! Возраст пациента более 4 лет. Сопровождение взрослым допускается при наличии медицинских показаний. Продолжить сохранение?',
				title: 'Вопрос'
			});
			return false;
		}

		var diag_code = '', diag_name = '', pay_type_nick = '', record;

		// Получаем вид оплаты
		index = base_form.findField('PayType_id').getStore().findBy(function (rec) {
			return (rec.get('PayType_id') == base_form.findField('PayType_id').getValue());
		});

		if (index >= 0) {
			pay_type_nick = base_form.findField('PayType_id').getStore().getAt(index).get('PayType_SysNick');
		}

		index = base_form.findField('Diag_id').getStore().findBy(function (rec) {
			return (rec.get('Diag_id') == base_form.findField('Diag_id').getValue());
		});
		record = base_form.findField('Diag_id').getStore().getAt(index);

		if (record) {
			diag_code = record.get('Diag_Code');
			diag_name = record.get('Diag_Name');

			if (getRegionNick() == 'ekb') {
				var sex_code = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Sex_Code');
				var person_age = swGetPersonAge(this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());
				var person_age_month = swGetPersonAgeMonth(this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());
				var person_age_day = swGetPersonAgeDay(this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());

				if (person_age == -1 || person_age_month == -1 || person_age_day == -1) {
					this.formStatus = 'edit';
					sw.swMsg.alert('Ошибка', 'Ошибка при определении возраста пациента');
					return false;
				}
				if (!sex_code || !(sex_code.toString().inlist(['1', '2']))) {
					this.formStatus = 'edit';
					sw.swMsg.alert('Ошибка', 'Не указан пол пациента');
					return false;
				}
				// если Sex_id не соответсвует полу пациента то "Выбранный диагноз не соответствует полу пациента"
				if (!Ext.isEmpty(record.get('Sex_Code')) && Number(record.get('Sex_Code')) != Number(sex_code)) {
					this.formStatus = 'edit';
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function (buttonId, text, obj) {
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
						fn: function (buttonId, text, obj) {
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: 'Выбранный диагноз не соответствует возрасту пациента',
						title: 'Ошибка'
					});
					return false;
				}
			} else if (getRegionNick() == 'buryatiya') {
				if (pay_type_nick == 'oms') {
					var sex_code = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Sex_Code');
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
					params.checkIsOMS = 0;
				}
			} else if (getRegionNick() == 'astra') {
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
								msg: 'Выбранный диагноз не оплачивается по ОМС, продолжить сохранение?',
								title: 'Продолжить сохранение?'
							});
							return false;
						}
					}
					params.checkIsOMS = 0;
				}
			} else if (getRegionNick() == 'kaluga') {
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
								msg: 'Выбранный диагноз не оплачивается по ОМС, поэтому случай не будет включен в реестр. Продолжить сохранение?',
								title: 'Продолжить сохранение?'
							});
							return false;
						}
					}
					params.checkIsOMS = 0;
				}
			}
			else {
				// https://redmine.swan.perm.ru/issues/4081
				// https://redmine.swan.perm.ru/issues/26975
				// https://redmine.swan.perm.ru/issues/28745
				// Проверка на финансирование по ОМС основного диагноза
				if (isNotPerm == true && pay_type_nick == 'oms' && (isUfa == false || (Ext.isEmpty(base_form.findField('Mes_tid').getValue()) && Ext.isEmpty(base_form.findField('Mes_sid').getValue())))) {
					params.checkIsOMS = 1;
				}
			}
		}
		if (
			!options.ignoreLpuSectionBedProfile
			&& !getRegionNick().inlist(['kareliya', 'perm', 'krym', 'penza', 'buryatiya', 'astra', 'pskov', 'ufa', 'khak'])
			&& pay_type_nick == 'oms'
			&& Ext.isEmpty(base_form.findField('LpuSectionBedProfileLink_fedid').getValue())
		) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						options.ignoreLpuSectionBedProfile = true;
						this.doSave(options);
					} else {
						base_form.findField('LpuSectionBedProfileLink_fedid').focus(true);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Не указан профиль койки. Профиль необходимо указывать для передачи данных о госпитализации в ТФОМС. Продолжить сохранение без указания профиля?',
				title: 'Вопрос'
			});
			return false;
		}

		// проверяем, есть ли незаполненные специфики
		var tree = this.findById(this.id + '_SpecificsTree');
		var root = tree.getRootNode();
		var isMorbusOnkoBlank = false;
		root.eachChild(function (child) {
			if (child.attributes.id = 'MorbusOnko') {
				child.eachChild(function (cld) {
					if (Ext.isEmpty(cld.attributes.Morbus_id)) {
						isMorbusOnkoBlank = true;
					}
				});
			}
		});
		var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');

		if (getRegionNick() != 'kz' && base_form.findField('EvnSection_disDate').getValue() && isMorbusOnkoBlank && !options.openChildWindow && (
			(diag_code.substr(0, 3).toUpperCase() >= 'C00' && diag_code.substr(0, 3).toUpperCase() <= 'C97')
			|| (diag_code.substr(0, 3).toUpperCase() >= 'D00' && diag_code.substr(0, 3).toUpperCase() <= 'D09')
		) && !(
			getRegionNick() == 'krym'
			&& base_form.findField('EvnSection_IsZNO').getValue() == 2
		)) {
			sw.swMsg.alert('Ошибка', 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела. Обязательные поля раздела отмечены символом *.');
			this.findById(this.id + '_SpecificsPanel').expand();
			this.formStatus = 'edit';
			return false;
		}

		var evn_section_dis_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_disTime').getValue());
		var evn_section_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_setTime').getValue());
		var evn_section_set_time = base_form.findField('EvnSection_setTime').getValue();
		var evn_ps_outcome_dt = getValidDT(Ext.util.Format.date(that.EvnPS_OutcomeDate, 'd.m.Y'), that.EvnPS_OutcomeTime ? that.EvnPS_OutcomeTime : '');
		var evn_ps_outcome_section_id = that.LpuSection_eid;
		var LpuSection_pid = that.LpuSection_pid;
		var LpuSection_Name = base_form.findField('LpuSection_id').getFieldValue('LpuSection_Name');

		if (evn_section_set_dt == null) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Неверное значение даты/времени поступления в отделение');
			return false;
		}

		if ( this.evnSectionIsFirst ) {
			if (Ext.isEmpty(evn_ps_outcome_dt) ) {
				if ( this.evnPSSetDT != null && evn_section_set_dt < this.evnPSSetDT ) {
					this.formStatus = 'edit';
					sw.swMsg.alert(
						langs('Ошибка'),
						langs('Дата и время поступления в стационар') + ' ' + this.evnPSSetDT.format('d.m.Y H:i') + ' ' + langs('позже даты и времени начала движения в профильном отделении') + ' ' + LpuSection_Name + ' ' + evn_section_set_dt.format('d.m.Y H:i'),
						function() {
							base_form.findField('EvnSection_setDate').focus(false);
						}
					);
					return false;
				}

				if (!options.ignoreSutkiDT && this.evnPSSetDT != null && (evn_section_set_dt.getTime() - this.evnPSSetDT.getTime()) > 86400000) {
					this.formStatus = 'edit';
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								options.ignoreSutkiDT = true;
								this.doSave(options);
							}

						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: 'Дата и время поступления в стационар ' + this.evnPSSetDT.format('d.m.Y H:i') + ' раньше даты и времени поступления в отделение ' + LpuSection_Name + ' ' + evn_section_set_dt.format('d.m.Y H:i') + ' больше чем на сутки. Продолжить?',
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
			else {
				if (evn_ps_outcome_dt > evn_section_set_dt) {
					this.formStatus = 'edit';
					sw.swMsg.alert(
						langs('Ошибка'),
						langs('Дата и время исхода из приемного отделения') + ' ' + evn_ps_outcome_dt.format('d.m.Y H:i') + ' ' + langs('позже даты и времени начала движения в профильном отделении') + ' ' + LpuSection_Name + ' ' + evn_section_set_dt.format('d.m.Y H:i'),
						function() {
							base_form.findField('EvnSection_setDate').focus(false);
						}
					);
					return false;
				}

				if (!options.ignoreOutDT && (evn_section_set_dt.getTime() - evn_ps_outcome_dt.getTime()) > 86400000) {
					this.formStatus = 'edit';
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								options.ignoreOutDT = true;
								this.doSave(options);
							}
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: 'Дата и время исхода из приемного отделения ' + evn_ps_outcome_dt.format('d.m.Y H:i') + ' раньше даты и времени поступления в отделение ' + LpuSection_Name + ' ' + evn_section_set_dt.format('d.m.Y H:i') + ' больше, чем на сутки. Продолжить?',
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		}

		if (!Ext.isEmpty(LpuSection_pid) && (Ext.isEmpty(evn_ps_outcome_dt) || Ext.isEmpty(evn_ps_outcome_section_id) || evn_ps_outcome_dt.format('d.m.Y') != evn_section_set_dt.format('d.m.Y') || evn_ps_outcome_section_id != base_form.findField('LpuSection_id').getValue()) && this.evnSectionIsFirst && !options.ignoreDTandSection) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						this.EvnPS_OutcomeDate = evn_section_set_dt;
						this.EvnPS_OutcomeTime = evn_section_set_time;
						this.LpuSection_eid = base_form.findField('LpuSection_id').getValue();
						options.ignoreDTandSection = true;
						this.doSave(options);
					}

				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: 'Сведения об исходе из приемного отделения не совпадают с параметрами первого движения. Исправить дату исхода на ' + evn_section_set_dt.format('d.m.Y') + ' и отделение госпитализации на ' + LpuSection_Name + ' ?',
				title: ERR_INVFIELDS_TIT
			});
			return false;
		} else if (evn_section_dis_dt != null && evn_section_set_dt > evn_section_dis_dt) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Дата/время выписки из отделения меньше даты/времени поступления');
			return false;
		}
		else if (!evn_section_dis_dt && base_form.findField('LeaveType_id').getValue()) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'При указанном исходе госпитализации должна быть заполнена дата выписки из отделения');
			return false;
		}
		else if (evn_section_dis_dt && !base_form.findField('LeaveType_id').getValue()) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'При указанной дате выписки из отделения должен быть заполнен исход госпитализации');
			return false;
		}
		else if (this.evnSectionIsLast == true && evn_section_dis_dt != null && this.evnLeaveSetDT && typeof this.evnLeaveSetDT == 'object' && evn_section_dis_dt.getTime() != this.evnLeaveSetDT.getTime()) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Сохранение отменено, т.к. не совпадают дата/время выписки из отделения и дата/время исхода госпитализации.');
			return false;
		}
		if (
			!options.ignoreOutcomeOrgDate && base_form.findField('EvnSection_disDate').getValue()
			&& base_form.findField('LeaveType_id').getValue()
			&& base_form.findField('LeaveType_id').getStore().getById(base_form.findField('LeaveType_id').getValue())
			&& base_form.findField('LeaveType_id').getStore().getById(base_form.findField('LeaveType_id').getValue()).get('LeaveType_SysNick') == 'other'
			&& base_form.findField('Org_oid').getValue()
		) {
			Ext.Ajax.request({
				callback: function (opt, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj[0] && response_obj[0].Org_id) {
							that.formStatus = 'edit';
							sw.swMsg.alert('Ошибка', 'МО, в которую переведен пациент, закрыта, на дату выписки пациента. Выберите другую МО');
							return false;
						} else {
							that.formStatus = 'edit';
							options.ignoreOutcomeOrgDate = true;
							that.doSave(options);
						}
					}
					else {
						that.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Ошибка при проверке МО перевода.');
					}
				},
				params: {
					Org_oid: base_form.findField('Org_oid').getValue(),
					EvnSection_OutcomeDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y')
				},
				url: '/?c=EvnSection&m=checkEvnSectionOutcomeOrgDate'
			});
			return false;
		}
		//BOB - 28.04.2018 Контроль закрытия Реанимационных периодов и соотвентствия исхода
		//if (!options.ignoreReanimatPeriodClose && base_form.findField('EvnSection_disDate').getValue() && (haveArmType('stac') || haveArmType('stacpriem') || haveArmType('reanimation'))) {
		if (!options.ignoreReanimatPeriodClose && base_form.findField('EvnSection_disDate').getValue()) {
			Ext.Ajax.request({
				callback: function (opt, success, response) {
					if (success && response.responseText != 'false') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						console.log('BOB_response_obj=', response_obj);
						//alert("111");
						if (response_obj.Status == "norm") {
							that.formStatus = 'edit';
							options.ignoreReanimatPeriodClose = true;
							that.doSave(options);
						} else if (response_obj.Status == "stop") {
							that.formStatus = 'edit';
							sw.swMsg.alert('Ошибка', response_obj.Message);
							return false;
						} else {
							that.formStatus = 'edit';
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										options.ignoreReanimatPeriodClose = true;
										that.doSave(options);
									}

								}.createDelegate(this),
								icon: Ext.Msg.WARNING,
								msg: response_obj.Message + ' Продолжить?',
								title: ERR_INVFIELDS_TIT
							});
							return false;
						}
					}
					else {
						that.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Ошибка при проверке закрытия Реанимационного периода.');
					}
				},
				params: {
					EvnSection_id: this.formParams.EvnSection_id,
					EvnSection_disDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'),
					EvnSection_disTime: base_form.findField('EvnSection_disTime').getValue(),
					LeaveType_id: base_form.findField('LeaveType_id').getValue()

				},
				url: '/?c=EvnReanimatPeriod&m=checkEvnSectionByRPClose'
			});
			return false;
		}
		//BOB - 28.04.2018

		var Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		if (evn_section_set_dt && Person_Birthday) {
			var age = swGetPersonAge(Person_Birthday, evn_section_set_dt);
			if (!options.ignoreLpuSectionAgeCheck && ((base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 1 && age <= 17) || (base_form.findField('LpuSection_id').getFieldValue('LpuSectionAge_id') == 2 && age >= 18))) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							options.ignoreLpuSectionAgeCheck = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Возрастная группа отделения не соответствуют возрасту пациента. Продолжить?',
					title: 'Вопрос'
				});

				return false;
			}
		}

		if (!Ext.isEmpty(base_form.findField('LpuSection_oid').getValue()) && evn_section_dis_dt && Person_Birthday) {
			var age = swGetPersonAge(Person_Birthday, evn_section_dis_dt);
			if (!options.ignoreLpuSectionAgeCheck && ((base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id') == 1 && age <= 17) || (base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id') == 2 && age >= 18))) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							options.ignoreLpuSectionAgeCheck = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Возрастная группа отделения не соответствуют возрасту пациента. Продолжить?',
					title: 'Вопрос'
				});

				return false;
			}
		}
		if (
			!options.ignoreCheckKSGKPGKoef
			&& (getRegionNick().inlist(['kareliya']))
			&& Ext.isEmpty(base_form.findField('Mes_tid').getValue())
			&& Ext.isEmpty(base_form.findField('Mes_sid').getValue())
			&& Ext.isEmpty(base_form.findField('Mes_kid').getValue())
			&& Ext.isEmpty(base_form.findField('MesTariff_id').getValue())
		) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						options.ignoreCheckKSGKPGKoef = true;
						this.doSave(options);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				//msg: 'По указанному диагнозу КСГ не определен. Случай не оплачивается по ОМС, продолжить сохранение?',
				msg: 'Необходимо указать услугу для введенного диагноза с целью определения группы КСГ и оплаты случая. Продолжить  сохранение?',
				title: 'Вопрос'
			});

			return false;
		}

		var diagRec = new Object();
		var DataViewDiagStore = this.findById('dataViewDiag').getStore();
		DataViewDiagStore.clearFilter();

		if (DataViewDiagStore.getCount() == 0 && base_form.findField('EvnSection_id').getValue() == 0) {
			/****/
			diagRec = [{
				EvnDiagPS_id: -swGenTempId(DataViewDiagStore),
				Diag_Code: diag_name,
				Diag_Name: diag_code,
				Diag_id: base_form.findField('Diag_id').getValue(),
				EvnDiagPS_pid: base_form.findField('EvnSection_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				DiagSetClass_id: 1,
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				RecordStatus_Code: 0,
				EvnDiagPS_setDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y')
			}];
			diagRec.Rec = 0;
			//log(diagRec);
			DataViewDiagStore.loadData(diagRec, true);
		}
		/****/

		log(DataViewDiagStore, DataViewDiagStore.getCount(), 23423423);
		if (DataViewDiagStore.getCount() > 0) {
			this.filterDS('save');
			log(DataViewDiagStore, DataViewDiagStore.getCount(), 2342222222222222222);
			var DataViewDiag = getStoreRecords(DataViewDiagStore, {convertDateFields: true});
			params.DataViewDiag = Ext.util.JSON.encode(DataViewDiag);
		}
		/*this.DataViewStore();*/
		log(params, 32423423);
		var med_staff_fact_aid = base_form.findField('MedStaffFact_aid').getValue();
		var med_staff_fact_did = base_form.findField('MedStaffFact_did').getValue();
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

		base_form.findField('MedPersonal_aid').setValue(0);
		base_form.findField('MedPersonal_did').setValue(0);
		base_form.findField('MedPersonal_id').setValue(0);

		record = base_form.findField('MedStaffFact_aid').getStore().getById(med_staff_fact_aid);
		if (record) {
			base_form.findField('MedPersonal_aid').setValue(record.get('MedPersonal_id'));
		}

		record = base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_did);
		if (record) {
			base_form.findField('MedPersonal_did').setValue(record.get('MedPersonal_id'));
		}

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if (record) {
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
		}

		if (!options.ignoreCheckBeforeLeave
			&& !options.openChildWindow
			&& base_form.findField('LpuSection_id').getValue()
			&& base_form.findField('LeaveType_id').getValue()
		) {
			var age = swGetPersonAge(Person_Birthday, evn_section_set_dt);
			var EvnSection_IsZNO;
			if(base_form.findField('ESecEF_EvnSection_IsZNOCheckbox').getValue() == true ) {
				EvnSection_IsZNO = 'on';
			} else {
				EvnSection_IsZNO = 'off';
			}

			sw.Promed.EvnSection.checkBeforeLeave(that,
				function (isOk) {
					if (isOk) {
						that.formStatus = 'edit';
						options.ignoreCheckBeforeLeave = true;
						that.doSave(options);
					} else {
						that.formStatus = 'edit';
					}
				},
				base_form.findField('EvnSection_pid').getValue(),
				base_form.findField('EvnSection_id').getValue(),
				base_form.findField('LpuSection_id').getValue(),
				base_form.findField('MedPersonal_id').getValue(),
				base_form.findField('MedStaffFact_id').getValue(),
				evn_section_set_dt,
				base_form.findField('UslugaComplex_id').getValue(),
				base_form.findField('HTMedicalCareClass_id').getValue(),
				this.childPS || age === 0,
				EvnSection_IsZNO
			);
			return true;
		}

		if (this.DrugTherapySchemePanel.isVisible()) {
			params.DrugTherapyScheme_ids = this.DrugTherapySchemePanel.getIds();
		}

		params.EvnSection_disDate = Ext.util.Format.date(evn_section_dis_dt, 'd.m.Y');
		params.EvnSection_setDate = Ext.util.Format.date(evn_section_set_dt, 'd.m.Y');

		if (base_form.findField('EvnSection_disTime').disabled) {
			params.EvnSection_disTime = base_form.findField('EvnSection_disTime').getRawValue();
		}

		if (base_form.findField('CureResult_id').disabled) {
			params.CureResult_id = base_form.findField('CureResult_id').getValue();
		}

		if (base_form.findField('EvnSection_setTime').disabled) {
			params.EvnSection_setTime = base_form.findField('EvnSection_setTime').getRawValue();
		}

		if (base_form.findField('LpuSection_id').disabled) {
			params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}

		if (base_form.findField('MedStaffFact_id').disabled) {
			params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		}

		if (base_form.findField('LeaveType_fedid').disabled) {
			params.LeaveType_fedid = base_form.findField('LeaveType_fedid').getValue();
		}

		if (base_form.findField('ResultDeseaseType_fedid').disabled) {
			params.ResultDeseaseType_fedid = base_form.findField('ResultDeseaseType_fedid').getValue();
		}

		if (base_form.findField('DeseaseBegTimeType_id').disabled) {
			params.DeseaseBegTimeType_id = base_form.findField('DeseaseBegTimeType_id').getValue();
		}

		if ( base_form.findField('Diag_spid').disabled ) {
			params.Diag_spid = base_form.findField('Diag_spid').getValue();
		}

		if (base_form.findField('UslugaComplex_id').disabled) {
			params.UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
		}

		if (base_form.findField('PayType_id').disabled) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}
		if (base_form.findField('PersonNewBorn_id') && !base_form.findField('PersonNewBorn_id').disabled) {
			var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();
			apgarGrid.getStore().clearFilter();
			if (apgarGrid.getStore().getCount() > 0) {
				var ApgarData = getStoreRecords(apgarGrid.getStore());


				params.ApgarData = Ext.util.JSON.encode(ApgarData);

				apgarGrid.getStore().filterBy(function (rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});
			}
			var PersonBirthTraumaData = [];
			var tGrid;
			for (var x = 1; x < 5; x++) {
				tGrid = this.findById('ESEW_PersonBirthTraumaGrid' + x).getGrid();
				tGrid.getStore().clearFilter();
				if (tGrid.getStore().getCount() > 0) {
					[].push.apply(PersonBirthTraumaData, getStoreRecords(tGrid.getStore()))
					tGrid.getStore().filterBy(function (rec) {
						return (Number(rec.get('RecordStatus_Code')) != 3);
					});
				}
			}
			params.PersonBirthTraumaData = Ext.util.JSON.encode(PersonBirthTraumaData);

		}
		if (base_form.findField('PersonNewBorn_CountChild')) {
			this.specBirthData.countChild = base_form.findField('PersonNewBorn_CountChild').getValue();
		} else {
			this.specBirthData.countChild = null;
		}
		// Собираем данные из гридов в специфике по новорожденным
		this.specBirthData.birthHeight = null;
		/*if (this.findById('ESecEF_PersonHeightGrid')) {
			var person_height_grid = this.findById('ESecEF_PersonHeightGrid').getGrid();
			person_height_grid.getStore().clearFilter();
			if (person_height_grid.getStore().getCount() > 0) {
				var person_height_data = getStoreRecords(person_height_grid.getStore(), {convertDateFields:true});
				params.personHeightData = Ext.util.JSON.encode(person_height_data);
				for (var i = 0; i < person_height_data.length; i++) {
					if ((person_height_data[i].HeightMeasureType_Code == 1) && (person_height_data[i].RecordStatus_Code != 3)) {
						this.specBirthData.birthHeight = person_height_data[i].PersonHeight_Height;
					}
				}
				person_height_grid.getStore().filterBy(function (rec) {
					return Number(rec.get('RecordStatus_Code')) != 3;
				});
			}
		}*/
		this.specBirthData.birthWeight = null;
		this.specBirthData.Okei_id = null;
		/*if (this.findById('ESecEF_PersonWeightGrid')) {
			var person_weight_grid = this.findById('ESecEF_PersonWeightGrid').getGrid();
			person_weight_grid.getStore().clearFilter();
			if (person_weight_grid.getStore().getCount() > 0) {
				var person_weight_data = getStoreRecords(person_weight_grid.getStore(), {convertDateFields:true});
				for (var i = 0; i < person_weight_data.length; i++) {
					if ((person_weight_data[i].WeightMeasureType_Code == 1) && (person_weight_data[i].RecordStatus_Code != 3)) {
					}
					this.specBirthData.birthWeight = person_weight_data[i].PersonWeight_Weight;
					this.specBirthData.PersonWeight_text = person_weight_data[i].PersonWeight_text;
					this.specBirthData.Okei_id = person_weight_data[i].Okei_id;
				}
				params.personWeightData = Ext.util.JSON.encode(person_weight_data);
				person_weight_grid.getStore().filterBy(function (rec) {
					return Number(rec.get('RecordStatus_Code')) != 3;
				});
			}
		}*/
		/*
		// Гриды специфики
		params.MorbusHepatitisDiag = this.collectGridData('MorbusHepatitisDiag');
		params.MorbusHepatitisDiagSop = this.collectGridData('MorbusHepatitisDiagSop');
		params.MorbusHepatitisLabConfirm = this.collectGridData('MorbusHepatitisLabConfirm');
		params.MorbusHepatitisFuncConfirm = this.collectGridData('MorbusHepatitisFuncConfirm');
		params.MorbusHepatitisCure = this.collectGridData('MorbusHepatitisCure');
		params.MorbusHepatitisCureEffMonitoring = this.collectGridData('MorbusHepatitisCureEffMonitoring');
		params.MorbusHepatitisVaccination = this.collectGridData('MorbusHepatitisVaccination');
		params.MorbusHepatitisQueue = this.collectGridData('MorbusHepatitisQueue');
		*/

		if (this.WizardPanel) {
			var cancel = false;
			this.WizardPanel.categories.each(function (category) {
				var categoryData = category.getCategoryData(category);
				if (categoryData && categoryData.status != 3 && category.saveCategory(category) === false) {
					cancel = true;
					return false;
				}
			});
			if (cancel) {
				this.formStatus = 'edit';
				return false;
			}
		}

		//Если "Госпитализирован в" и Дата исхода из КВС не совпадает с отделением и датой поступления в первом движении выводим предупреждение
		if (!options.ignoreOutcomeAndAction && !Ext.isEmpty(base_form.findField('LpuSection_id').getValue()) && !Ext.isEmpty(base_form.findField('EvnSection_id').getValue()) && !Ext.isEmpty(base_form.findField('EvnSection_setDate').getValue())) {
			Ext.Ajax.request({
				callback: function (optionscb, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj[0].ignoreOutcomeAndAction && (base_form.findField('LpuSection_id').getValue() != that.LpuSection_eid || !!(base_form.findField('EvnSection_setDate').getValue() - that.EvnPS_OutcomeDate) || base_form.findField('EvnSection_setTime').getValue() != that.EvnPS_OutcomeTime)) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									that.formStatus = 'edit';

									if ('yes' == buttonId) {
										options.ignoreOutcomeAndAction = true;
										that.doSave(options);
									} else {
										that.buttons[0].focus();
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: 'Отдление и/или дата исхода отличается от указанных в КВС. Продолжить сохранение?',
								title: 'Вопрос'
							});
						} else {
							that.formStatus = 'edit';
							options.ignoreOutcomeAndAction = true;
							that.doSave(options);
						}
					} else {
						that.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Ошибка при проверке даты и отделенеия в движении.');
					}
				},
				params: {
					EvnSection_id: base_form.findField('EvnSection_id').getValue(),
					LpuSection_id: base_form.findField('LpuSection_id').getValue(),
					EvnSection_setDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
					EvnSection_setTime: base_form.findField('EvnSection_setTime').getValue()
				},
				url: '/?c=EvnPS&m=checkEvnSectionSectionAndDateEqual'
			});
			return false;
		}

		this.DataViewStore();
		if (getRegionNick() == 'vologda'){
			this.DoctorHistoryDataViewStore();
			this.LpuSectionWardHistoryDataViewStore();
			this.LpuSectionBedProfileHistoryDataViewStore();
		}

		// проверка на существование связи между специальностью врача и профилем отделения на установленную дату. Поиск происходит в глобальном сторе swLpuSectionProfileMedSpecOms
		if (getRegionNick() == 'pskov' && Ext.globalOptions.stac.evnsection_profile_medspecoms_check != 0 && !options.ignoreLpuSectionProfile_MedSpecOms) {
			var onDate = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
				LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue(),
				MedSpecOms_id = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_id');

			if (checkLpuSectionProfile_MedSpecOms_Exists(MedSpecOms_id, LpuSectionProfile_id, onDate) === false) {
				that.formStatus = 'edit';

				if (Ext.globalOptions.stac.evnsection_profile_medspecoms_check == 1) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							that.formStatus = 'edit';

							if ('yes' == buttonId) {
								options.ignoreLpuSectionProfile_MedSpecOms = true;
								that.doSave(options);
							} else {
								that.buttons[0].focus();
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: 'Нарушено соответствие между профилем и специальностью. Продолжить сохранение?',
						title: 'Вопрос'
					});

				} else {
					sw.swMsg.alert('Ошибка', 'Нарушено соответствие между профилем и специальностью');
				}

				return false;

			}
		}

		/**/
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение случая движения пациента в стационаре..."});
		loadMask.show();

		// Собираем данные из таблицы "Сопутствующие патологоанатомические диагнозы"
		var anatom_diag_grid = this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid();

		anatom_diag_grid.getStore().clearFilter();
		if (anatom_diag_grid.getStore().getCount() > 0 && anatom_diag_grid.getStore().getAt(0).get('EvnDiagPS_id')) {
			var anatom_diag_data = getStoreRecords(anatom_diag_grid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					'EvnDiagPS_pid'
					, 'Person_id'
					, 'PersonEvn_id'
					, 'Server_id'
					, 'DiagSetClass_Name'
					, 'Diag_Code'
					, 'Diag_Name'
				]
			});

			params.anatomDiagData = Ext.util.JSON.encode(anatom_diag_data);

			anatom_diag_grid.getStore().filterBy(function (rec) {
				if (Number(rec.get('RecordStatus_Code')) == 3) {
					return false;
				}
				else {
					return true;
				}
			});
		}

		params.PersonRegister_id = (this.PersonRegister_id > 0) ? this.PersonRegister_id : null;

		if (this.WizardPanel) {
			this.WizardPanel.setReadOnly(true);
			params = Ext.apply(params, this.WizardPanel.getDataForSave(true));
		}

		if (options && (options.openChildWindow)) {
			params.silentSave = '1';
			params.isAutoCreate = 1;//(this.action == 'add' || this.changedDates == true) ? 1 : 0;
		} else {
			params.silentSave = '0';
			params.isAutoCreate = 0;
		}

		params.editAnatom = (this.editAnatom == true) ? 2 : 1;
		if (this.editAnatom) {
			this.enableEdit(true);
		}

		if (this.findById('ESecEF_EvnSection_IsZNOCheckbox').getValue() == true) {
			base_form.findField('EvnSection_IsZNO').setValue(2);
		}
		else {
			base_form.findField('EvnSection_IsZNO').setValue(1);
		}

		params.vizit_direction_control_check = (options && !Ext.isEmpty(options.vizit_direction_control_check) && options.vizit_direction_control_check === 1) ? 1 : 0;
		params.ignoreDiagKSGCheck = (options && !Ext.isEmpty(options.ignoreDiagKSGCheck) && options.ignoreDiagKSGCheck === 1) ? 1 : 0;
		params.ignoreParentEvnDateCheck = (options && !Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaChange = (options && !Ext.isEmpty(options.ignoreCheckEvnUslugaChange) && options.ignoreCheckEvnUslugaChange === 1) ? 1 : 0;
		params.ignoreCheckEvnUslugaDates = (options && !Ext.isEmpty(options.ignoreCheckEvnUslugaDates) && options.ignoreCheckEvnUslugaDates === 1) ? 1 : 0;
		params.ignoreCheckKSGisEmpty = (options && !Ext.isEmpty(options.ignoreCheckKSGisEmpty) && options.ignoreCheckKSGisEmpty === 1) ? 1 : 0;
		params.ignoreCheckCardioFieldsEmpty = (options && !Ext.isEmpty(options.ignoreCheckCardioFieldsEmpty) && options.ignoreCheckCardioFieldsEmpty === 1) ? 1 : 0;
		params.skipPersonRegisterSearch = (options && !Ext.isEmpty(options.skipPersonRegisterSearch) && options.skipPersonRegisterSearch === 1) ? 1 : 0;
		params.ignoreEvnUslugaHirurgKSGCheck = (options && !Ext.isEmpty(options.ignoreEvnUslugaHirurgKSGCheck) && options.ignoreEvnUslugaHirurgKSGCheck === 1) ? 1 : 0;
		params.ignoreCheckTNM = (options && !Ext.isEmpty(options.ignoreCheckTNM) && options.ignoreCheckTNM === 1) ? 1 : 0;
		params.ignoreCheckMorbusOnko = (options && !Ext.isEmpty(options.ignoreCheckMorbusOnko) && options.ignoreCheckMorbusOnko === 1) ? 1 : 0;
		params.ignoreMorbusOnkoDrugCheck = (options && !Ext.isEmpty(options.ignoreMorbusOnkoDrugCheck) && options.ignoreMorbusOnkoDrugCheck === 1) ? 1 : 0;
		params.ignoreFirstDisableCheck = (!Ext.isEmpty(options.ignoreFirstDisableCheck) && options.ignoreFirstDisableCheck === 1) ? 1 : 0;

		if (options && options.ignoreCheckIsOms) {
			params.checkIsOMS = 0;
		}

		if (this.showSTField() == false) {
			base_form.findField('EvnSection_IsST').clearValue();
		}

		//log(params,123213,options);return false;
		base_form.submit({
			failure: function (result_form, action) {
				if (this.WizardPanel) {
					this.WizardPanel.setReadOnly(false);
				}
				if (this.editAnatom) {
					this.enableEdit(false);
					this.enableAnatomFormEdit(true);
				}
				this.formStatus = 'edit';
				loadMask.hide();


				if (action.result) {
					if (action.result.Error_Msg && 'YesNo' != action.result.Error_Msg && 'Ok' != action.result.Error_Msg) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					} else if('Ok' == action.result.Error_Msg){
						switch(action.result.Error_Code) {
							case 301:
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function (buttonId, text, obj) {

										if(buttonId == 'ok') {
											that.doSave({
												openChildWindow: function () {
													var params = {
														EvnSection_id: base_form.findField('EvnSection_id').getValue(),
														MorbusOnko_pid: base_form.findField('EvnSection_id').getValue(),
														Person_id: base_form.findField('Person_id').getValue(),
														PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
														Server_id: base_form.findField('Server_id').getValue(),
														allowSpecificEdit: true
													};
													getWnd('swMorbusOnkoWindow').show(params);
												}.createDelegate(this)
											});

										}
									}.createDelegate(this),
									icon: Ext.Msg.WARNING,
									msg: action.result.Alert_Msg,
									title: 'Ошибка'
								});
								break;
						}
					} else if (action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg) {
						if (action.result.Error_Code == 120) {
							sw.swMsg.show({
								buttons: {
									yes: 'Связать',
									no: 'Не связывать',
									cancel: 'Исправить данные исхода'
								},
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										this.PersonRegister_id = action.result.PersonRegister_id;
										this.doSave(options);
									}
									if (buttonId == 'no') {
										options.skipPersonRegisterSearch = 1;
										this.doSave(options);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: 'Вопрос'
							});
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function (buttonId, text, obj) {
									if (buttonId == 'yes') {
										if (action.result.Error_Code == 112) {
											options.vizit_direction_control_check = 1;
										}
										if (action.result.Error_Code == 103) {
											options.ignoreDiagKSGCheck = 1;
										}
										if (action.result.Error_Code == 106) {
											options.ignoreMorbusOnkoDrugCheck = 1;
										}
										if (action.result.Error_Code == 109) {
											options.ignoreParentEvnDateCheck = 1;
										}
										if (action.result.Error_Code == 114) {
											options.ignoreCheckEvnUslugaChange = 1;
											this.EvnUslugaGridIsModified = true;
										}
										if (action.result.Error_Code == 115) {
											options.ignoreCheckEvnUslugaDates = 1;
										}
										if (action.result.Error_Code == 116) {
											options.ignoreCheckKSGisEmpty = 1;
										}
										if (action.result.Error_Code == 117) {
											options.ignoreCheckCardioFieldsEmpty = 1;
										}
										if (action.result.Error_Code == 118) {
											options.ignoreCheckIsOms = 1;
										}
										if (action.result.Error_Code == 119) {
											options.ignoreEvnUslugaHirurgKSGCheck = 1;
										}
										if (action.result.Error_Code == 181) {
											options.ignoreCheckTNM = 1;
										}
										if (action.result.Error_Code == 107) {
											options.ignoreFirstDisableCheck = 1;
										}
										if (action.result.Error_Code == 289) {
											options.ignoreCheckMorbusOnko = 1;
										}
										if (this.WizardPanel && action.result.Error_Code == 201) {
											this.WizardPanel.getCategory('Result').ignoreCheckBirthSpecStacDate = 1;
										}
										if (this.WizardPanel && action.result.Error_Code == 202) {
											this.WizardPanel.getCategory('Result').ignoreCheckChildrenCount = 1;
										}

										this.doSave(options);
									}
									else {
										base_form.findField('EvnSection_setDate').focus(true);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: 'Продолжить сохранение?'
							});
						}
					} else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			}.createDelegate(this),
			params: params,
			success: function (result_form, action) {
				if (this.WizardPanel) {
					this.WizardPanel.setReadOnly(false);
				}
				if (this.editAnatom) {
					this.enableEdit(false);
					this.enableAnatomFormEdit(true);
				}
				this.formStatus = 'edit';
				loadMask.hide();

				if (action.result) {
					if (action.result.Alert_Msg) {
						sw.swMsg.alert('Внимание', action.result.Alert_Msg);
					}
					if (action.result.EvnSection_id) {
						var evn_section_id = action.result.EvnSection_id;
						base_form.findField('EvnSection_id').setValue(evn_section_id);
						this.formParams.EvnSection_id = evn_section_id;

						if (action.result.PersonRegister_id !== undefined) {
							this.PersonRegister_id = action.result.PersonRegister_id;
						}
						if (this.WizardPanel) {
							this.WizardPanel.categories.each(function (category) {
								//Замена идентификаторов записей в мастере редактирования сведений о беременности
								if (category.name == 'Anketa' && action.result.PersonPregnancy_id !== undefined) {
									if (action.result.PersonPregnancy_id) {
										category.replaceCategoryDataId(category, category.PersonPregnancy_id, action.result.PersonPregnancy_id);
										category.setCategoryDataValue(category, 'status', 1);
									} else {
										category.removeCategoryData(category);
									}
								}
								if (category.name == 'Result' && action.result.BirthSpecStac_id !== undefined) {
									category.AddedPersonNewBorn_ids = [];
									if (action.result.BirthSpecStac_id) {
										if (Ext.isEmpty(category.BirthSpecStac_id) || category.BirthSpecStac_id < 0) {
											that.createdObjects.BirthSpecStac_id = action.result.BirthSpecStac_id;
										}
										category.replaceCategoryDataId(category, category.BirthSpecStac_id, action.result.BirthSpecStac_id);
										category.setCategoryDataValue(category, 'status', 1);
										category.setCategoryDataValue(category, 'EvnSection_id', action.result.EvnSection_id);
										category.getForm().findField('EvnSection_id').setValue(action.result.EvnSection_id);
										category.ChildDeathGridPanel.loadData({
											globalFilters: {BirthSpecStac_id: category.BirthSpecStac_id},
											callback: function () {
												category.refreshPregnancyResultDisable();
												category.collectCategoryData(category);
											},
											noFocusOnLoad: true
										});
									} else {
										category.removeCategoryData(category);
									}
								}
								if (category.name == 'Certificate' && action.result.BirthCertificate_id !== undefined) {
									if (action.result.BirthCertificate_id) {
										category.replaceCategoryDataId(category, category.BirthCertificate_id, action.result.BirthCertificate_id);
										category.setCategoryDataValue(category, 'status', 1);
									} else {
										category.removeCategoryData(category);
									}
								}
								if (category.name == 'Screen' && action.result.PregnancyScreenResponse) {
									var PregnancyScreenResponse = action.result.PregnancyScreenResponse;
									for (oldId in PregnancyScreenResponse) {
										var newId = PregnancyScreenResponse[oldId];
										if (newId) {
											category.replaceCategoryDataId(category, oldId, newId);
											category.setCategoryDataValue(category, 'status', 1);
										} else {
											category.removeCategoryData(category, oldId);
										}
									}
								}
							});
						}

						if (this.specificsPanel.isExpanded && this.PersonRegister_id) {
							this.treeLoaded = false;
							this.onSpecificsExpand(this.specificsPanel, true);
							if (this.WizardPanel) this.WizardPanel.show();
						}

						if (action.result.PersonNewBorn_id !== undefined) {
							base_form.findField('PersonNewBorn_id').setValue(action.result.PersonNewBorn_id);

							var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();

							apgarGrid.getStore().load({params: {PersonNewBorn_id: action.result.PersonNewBorn_id}});

							if (this.isTraumaTabGridLoaded) {
								var grid1 = this.findById('ESEW_PersonBirthTraumaGrid1').getGrid();
								var grid2 = this.findById('ESEW_PersonBirthTraumaGrid2').getGrid();
								var grid3 = this.findById('ESEW_PersonBirthTraumaGrid3').getGrid();
								var grid4 = this.findById('ESEW_PersonBirthTraumaGrid4').getGrid();

								grid1.getStore().baseParams.BirthTraumaType_id = 1;
								grid2.getStore().baseParams.BirthTraumaType_id = 2;
								grid3.getStore().baseParams.BirthTraumaType_id = 3;
								grid4.getStore().baseParams.BirthTraumaType_id = 4;

								grid1.getStore().load({params: {PersonNewBorn_id: action.result.PersonNewBorn_id}});
								grid2.getStore().load({params: {PersonNewBorn_id: action.result.PersonNewBorn_id}});
								grid3.getStore().load({params: {PersonNewBorn_id: action.result.PersonNewBorn_id}});
								grid4.getStore().load({params: {PersonNewBorn_id: action.result.PersonNewBorn_id}});
							}
						}

						if (options && typeof options.openChildWindow == 'function' /*&& (this.action == 'add' || this.changedDates == true)*/) {
							options.openChildWindow(action.result);
							return true;
						}
						else {
							var evn_section_narrow_bed_set_dt = null;
							var lpu_section_profile_name = '';
							var response = new Object();

							var leave_type_code = null;
							var leave_type_id = base_form.findField('LeaveType_id').getValue();
							var leave_type_name = '';

							index = base_form.findField('LeaveType_id').getStore().findBy(function (rec) {
								if (parseInt(leave_type_id) == parseInt(rec.get('LeaveType_id'))) {
									return true;
								}
								else {
									return false;
								}
							});

							record = base_form.findField('LeaveType_id').getStore().getAt(index);

							if (record) {
								leave_type_code = record.get('LeaveType_Code');
								leave_type_name = record.get('LeaveType_Name');
							}

							response = {
								accessType: 'edit',
								Diag_id: base_form.findField('Diag_id').getValue(),
								Diag_Code: base_form.findField('Diag_id').getFieldValue('Diag_Code'),
								EvnSection_disDate: base_form.findField('EvnSection_disDate').getValue(),
								EvnSection_disTime: base_form.findField('EvnSection_disTime').getValue(),
								EvnSection_id: evn_section_id,
								EvnSection_KoikoDni: base_form.findField('EvnSection_KoikoDni').getValue(),
								LpuSectionTransType_id: base_form.findField('LpuSectionTransType_id').getValue(),
								EvnSection_KoikoDniInterval: base_form.findField('EvnSection_KoikoDniInterval').getValue(),
								EvnSection_IsAdultEscort: base_form.findField('EvnSection_IsAdultEscort').getValue(),
								EvnSection_IsMedReason: base_form.findField('EvnSection_IsMedReason').getValue(),
								EvnSection_IsMeal: base_form.findField('EvnSection_IsMeal').getValue(),
								EvnSection_KoikoDniNorm: base_form.findField('EvnSection_KoikoDniNorm').getValue(),
								EvnSection_KSG: base_form.findField('EvnSection_KSG').getValue(),
								EvnSection_KPG: base_form.findField('EvnSection_KPG').getValue(),
								EvnSection_pid: base_form.findField('EvnSection_pid').getValue(),
								EvnSection_setDate: base_form.findField('EvnSection_setDate').getValue(),
								EvnSection_setTime: base_form.findField('EvnSection_setTime').getValue(),
								MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
								LpuSection_id: base_form.findField('LpuSection_id').getValue(),
								EvnSection_Index: base_form.findField('EvnSection_Index').getValue(),
								LpuSectionWard_id: base_form.findField('LpuSectionWard_id').getValue(),
								EvnSection_insideNumCard: base_form.findField('EvnSection_insideNumCard').getValue(),
								LpuSectionWard_Name: base_form.findField('LpuSectionWard_id').getFieldValue('LpuSectionWard_Name'),
								Mes_id: base_form.findField('Mes_id').getValue(),
								EvnSection_KSGKPG: base_form.findField('Mes_rid').getFieldValue('Mes_Name'),
								Mes2_id: base_form.findField('Mes2_id').getValue(),
								PayType_id: base_form.findField('PayType_id').getValue(),
								TariffClass_id: base_form.findField('TariffClass_id').getValue(),
								Person_id: base_form.findField('Person_id').getValue(),
								PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								DiagSetPhase_id: base_form.findField('DiagSetPhase_id').getValue(),
								DiagSetPhase_Name: base_form.findField('DiagSetPhase_id').getRawValue(),
								EvnSection_PhaseDescr: base_form.findField('EvnSection_PhaseDescr').getValue(),
								EvnDie_id: base_form.findField('EvnDie_id').getValue(),
								EvnLeave_id: base_form.findField('EvnLeave_id').getValue(),
								EvnOtherLpu_id: base_form.findField('EvnOtherLpu_id').getValue(),
								EvnOtherSection_id: base_form.findField('EvnOtherSection_id').getValue(),
								EvnOtherSectionBedProfile_id: base_form.findField('EvnOtherSectionBedProfile_id').getValue(),
								EvnOtherStac_id: base_form.findField('EvnOtherStac_id').getValue(),
								CureResult_Code: base_form.findField('CureResult_id').getFieldValue('CureResult_Code'),
								DeseaseBegTimeType_id: base_form.findField('DeseaseBegTimeType_id').getValue(),
								LeaveType_Code: leave_type_code,
								LeaveType_id: leave_type_id,
								LeaveType_Name: leave_type_name,

								birthHeight: this.specBirthData.birthHeight,
								birthWeight: this.specBirthData.birthWeight,
								Okei_id: this.specBirthData.Okei_id,
								countChild: this.specBirthData.countChild,
								PersonWeight_text: this.specBirthData.PersonWeight_text
							}

							if (this.evnSectionIsFirst) {
								response.EvnPS_OutcomeDate = that.EvnPS_OutcomeDate;
								response.LpuSection_eid = that.LpuSection_eid;
								response.EvnPS_OutcomeTime = that.EvnPS_OutcomeTime;
							}

							if (true || getRegionNick() == 'ufa') {
								this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().each(function (rec) {
									if (typeof evn_section_narrow_bed_set_dt != 'object' || evn_section_narrow_bed_set_dt < getValidDT(Ext.util.Format.date(rec.get('EvnSectionNarrowBed_setDate'), 'd.m.Y'), typeof rec.get('EvnSectionNarrowBed_setTime') == 'string' && rec.get('EvnSectionNarrowBed_setTime').length == 5 ? rec.get('EvnSectionNarrowBed_setTime') : '00:00')) {
										evn_section_narrow_bed_set_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSectionNarrowBed_setDate'), 'd.m.Y'), typeof rec.get('EvnSectionNarrowBed_setTime') == 'string' && rec.get('EvnSectionNarrowBed_setTime').length == 5 ? rec.get('EvnSectionNarrowBed_setTime') : '00:00');
										lpu_section_profile_name = rec.get('LpuSectionProfile_Name');
									}
								});
							}

							record = base_form.findField('LpuSection_id').getStore().getById(response.LpuSection_id);
							if (record) {
								response.LpuUnitType_id = record.get('LpuUnitType_id');
								response.LpuUnitType_SysNick = record.get('LpuUnitType_SysNick');
								response.LpuSection_Name = record.get('LpuSection_Name');

								if (lpu_section_profile_name.length == 0) {
									lpu_section_profile_name = record.get('LpuSectionProfile_Name');
								}
							}

							/*if ( !getRegionNick().inlist([ 'ufa' ]) && !Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue()) ) {
								lpu_section_profile_name = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Name');
							}*/

							record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
							if (record) {
								response.MedPersonal_Fio = record.get('MedPersonal_Fio');
								response.MedPersonal_id = record.get('MedPersonal_id');
							}

							record = base_form.findField('PayType_id').getStore().getById(response.PayType_id);
							if (record) {
								response.PayType_Name = record.get('PayType_Name');
							}

							response.Diag_Name = (diag_code.length > 0) ? (diag_code + '. ' + diag_name) : null;

							/*
							var mh_reg = new RegExp("^B1[5-9]");
							if(mh_reg.test(diag_code)) {
								requestEvnInfectNotify({
									EvnInfectNotify_pid: base_form.findField('EvnSection_id').getValue()
									,Diag_Name: diag_code + '. ' + diag_name
									//,Diag_id: base_form.findField('Diag_id').getValue()
									,Server_id: base_form.findField('Server_id').getValue()
									,PersonEvn_id: base_form.findField('PersonEvn_id').getValue()
									,MedPersonal_id: base_form.findField('MedPersonal_id').getValue()
									,EvnInfectNotify_FirstTreatDate: base_form.findField('EvnSection_setDate').getValue()
									,EvnInfectNotify_SetDiagDate: base_form.findField('EvnSection_setDate').getValue()
								});
							}

							var onko_reg = new RegExp("^C|D0");
							if(onko_reg.test(diag_code)) {
							}
							*/

							response.LpuSectionProfile_Name = lpu_section_profile_name;
							this.callback({evnSectionData: [response]});

							if(getRegionNick() == 'ufa' && this.getONMKDiag()) {
								var evnSectionPsSetDT = base_form.findField('EvnSection_setDate').getValue().format('Y-m-d') + ' ' + base_form.findField('EvnSection_setTime').getValue();
								Ext.getCmp("EvnPSEditWindow").saveOnmkFromKvc(this.getONMKDiag(), base_form.findField('RankinScale_id').getValue(), base_form.findField('RankinScale_sid').getValue(), base_form.findField('EvnSection_InsultScale').getValue(), base_form.findField('LeaveType_id').getValue(),evn_section_id,evnSectionPsSetDT);
							}

							if (options && typeof options.silent == 'function') {
								options.silent();
							} else {
								sw.Promed.EvnSection.onSaveEditForm({
									EvnSection_id: evn_section_id,
									EvnSection_pid: base_form.findField('EvnSection_pid').getValue(),
									Diag_id: base_form.findField('Diag_id').getValue(),
									EvnSection_setDate: base_form.findField('EvnSection_setDate').getValue(),
									EvnSection_setTime: base_form.findField('EvnSection_setTime').getValue(),
									LpuSection_id: base_form.findField('LpuSection_id').getValue(),
									MedPersonal_id: base_form.findField('MedPersonal_id').getValue(),
									MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
									LpuSectionProfile_eid: base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'),
									Person_id: base_form.findField('Person_id').getValue(),
									PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
									Server_id: base_form.findField('Server_id').getValue(),
									Person_Surname: that.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname'),
									Person_Firname: that.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname'),
									Person_Secname: that.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname'),
									Person_Birthday: that.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
									Person_IsDead: that.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_IsDead'),
									EvnSection_disDate: base_form.findField('EvnSection_disDate').getValue(),
									EvnSection_disTime: base_form.findField('EvnSection_disTime').getValue(),
									LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
									EvnDie_id: base_form.findField('EvnDie_id').getValue(),
									EvnLeave_id: base_form.findField('EvnLeave_id').getValue(),
									EvnOtherSection_id: base_form.findField('EvnOtherSection_id').getValue(),
									EvnOtherSectionBedProfile_id: base_form.findField('EvnOtherSectionBedProfile_id').getValue(),
									EvnOtherLpu_id: base_form.findField('EvnOtherLpu_id').getValue(),
									Org_oid: base_form.findField('Org_oid').getValue(),
									Lpu_oid: base_form.findField('Org_oid').getFieldValue('Lpu_id'),
									EvnOtherStac_id: base_form.findField('EvnOtherStac_id').getValue(),
									LpuUnitType_oid: base_form.findField('LpuUnitType_oid').getValue(),
									LpuSection_oid: base_form.findField('LpuSection_oid').getValue(),
									LpuUnit_oid: base_form.findField('LpuSection_oid').getFieldValue('LpuUnit_id'),
									LpuSectionProfile_oid: base_form.findField('LpuSection_oid').getFieldValue('LpuSectionProfile_id'),
									LpuSectionAge_oid: base_form.findField('LpuSection_oid').getFieldValue('LpuSectionAge_id'),
									callback: function () {
										that.hide();
									}
								});
							}
							if (typeof this.onChangeLpuSectionWard == 'function' && this.oldLpuSectionWard_id != response.LpuSectionWard_id) {
								this.onChangeLpuSectionWard(response);
							}
						}
					}
					else {
						if (action.result.Error_Msg) {
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
	enableEdit: function (enable) {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var form_fields = new Array(
			'Diag_id',
			'EvnSection_setDate',
			'EvnSection_setTime',
			'EvnSection_disDate',
			'EvnSection_disTime',
			'LpuSection_id',
			'EvnSection_insideNumCard',
			'LpuSectionWard_id',
			'LpuSectionBedProfile_id',
			'DiagSetPhase_id',
			'DiagSetPhase_aid',
			'PrivilegeType_id',
			'EvnSection_PhaseDescr',
			'CureResult_id',
			'LeaveType_id',
			'LeaveTypeFed_id',
			'MedStaffFact_did',
			'AnatomWhere_id',
			'Diag_aid',
			'EvnDie_expDate',
			'EvnDie_expTime',
			'LpuSection_aid',
			'Org_aid',
			'MedStaffFact_aid',
			'EvnDie_IsWait',
			'EvnDie_IsAnatom',
			'EvnLeave_IsAmbul',
			'EvnLeave_UKL',
			'LeaveCause_id',
			'Org_oid',
			'LpuSection_oid',
			//'LpuSectionBedProfile_oid',
			'LpuSectionBedProfileLink_fedoid',
			'LpuUnitType_oid',
			'ResultDesease_id',
			'MedStaffFact_id',
			'PayType_id',
			'TariffClass_id',
			'EvnSection_IsAdultEscort',
			'EvnSection_IsMedReason',
			'EvnSection_IsMeal',
			'EvnSection_IsTerm',
			'Mes_id',
			'UslugaComplex_id',
			'EvnSection_RepFlag',
			'GetRoom_id',
			'GetBed_id',
			'PayTypeERSB_id',
			'Mes_rid'
		);
		var i = 0;

		//if ( getRegionNick().inlist([ 'astra', 'kz']) ) {
		form_fields.push('LpuSectionProfile_id');
		//}

		for (i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if (enable) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	loadSpecificsTree: function () {
		var tree = this.findById(this.id + '_SpecificsTree');
		var root = tree.getRootNode();
		var win = this;

		if (win.specLoading) {
			clearTimeout(win.specLoading);
		}
		;

		win.specLoading = setTimeout(function () {
			if (!root.expanded) {
				root.expand();
			} else {
				if (tree.getLoader().isLoading()) {
					tree.getLoader().abort();
				}
				var spLoadMask = new Ext.LoadMask(this.getEl(), {msg: "Загрузка специфик..."});
				spLoadMask.show();
				tree.getLoader().load(root, function () {
					spLoadMask.hide();
					win.treeLoaded = true;
					win.onSpecificsExpand(win.specificsPanel);
				});
			}
		}.createDelegate(this), 100);
	},
	resizeSpecificForWizardPanel: function () {
		if (!this.WizardPanel || !this.WizardPanel.isVisible() || this.WizardPanelResizing) {
			return;
		}

		this.WizardPanelResizing = true;
		var defaultHeight = 220;
		var page = this.WizardPanel.getCurrentPage();

		if (page) {
			this.WizardPanel.show();
			if (page instanceof sw.Promed.ViewFrame) {
				var height = defaultHeight;

				page.setHeight(height - 36);
				this.WizardPanel.setHeight(height - 36);
				this.specificsPanel.setHeight(height);
				page.doLayout();
			} else {
				var height = 0;
				page.items.each(function (item) {
					if (item.hidden) return;
					var el = item.getEl();
					var margins = el.getMargins();
					height += el.getHeight() + margins.top + margins.bottom;
				});
				height += 38;
				if (height <= defaultHeight) {
					height = defaultHeight;
				}
				if (this.WizardPanel.DataToolbar.isVisible()) {
					this.specificsPanel.setHeight(height + 5 + 26);
				} else {
					this.specificsPanel.setHeight(height + 5);
				}
				this.WizardPanel.setHeight(height);
				page.doLayout();
			}
		} else {
			this.WizardPanel.hide();
			this.specificsPanel.setHeight(defaultHeight);
		}
		this.WizardPanelResizing = false;
	},
	createPersonPregnancyCategory: function (categoryName) {
		if (!this.WizardPanel) {
			this.createPersonPregnancyWizardPanel();
		}
		this.WizardPanel.show();
		this.WizardPanel.createCategoryController(categoryName);
	},
	deletePersonPregnancyCategory: function (categoryName, id) {
		if (!this.WizardPanel) {
			this.createPersonPregnancyWizardPanel();
		}
		this.WizardPanel.deleteCategoryController(categoryName, id);
	},
	printPregnancyResult: function () {
		var wnd = this;
		var category = this.WizardPanel.getCurrentCategory();

		if (!category || category.name != 'Result') {
			return false;
		}

		if (!Ext.isEmpty(this.PersonRegister_id) && category.BirthSpecStac_id < 0) {
			category.saveCategory(category, function () {
				wnd.doSave({
					silent: function () {
						wnd.printPregnancyResult()
					}
				});
			});
			return false;
		}

		if (!(category.BirthSpecStac_id > 0) || !(this.PersonRegister_id > 0)) {
			return false;
		}

		printBirt({
			'Report_FileName': 'PregnancyResult_print.rptdesign',
			'Report_Params': '&paramPersonRegister=' + this.PersonRegister_id,
			'Report_Format': 'pdf'
		});

		return true;
	},
	createPersonPregnancyWizardPanel: function () {
		var wnd = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var tree = this.specificsTree;
		var personInfoPanel = Ext.getCmp('ESecEF_PersonInformationFrame');

		var inputData = new sw.Promed.PersonPregnancy.InputData({
			fn: function () {
				return {
					Person_id: base_form.findField('Person_id').getValue(),
					PersonRegister_id: wnd.PersonRegister_id,
					Person_SurName: personInfoPanel.getFieldValue('Person_Surname'),
					Person_FirName: personInfoPanel.getFieldValue('Person_Firname'),
					Person_SecName: personInfoPanel.getFieldValue('Person_Secname'),
					Evn_id: base_form.findField('EvnSection_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue(),
					Lpu_id: getGlobalOptions().lpu_id,
					LpuSection_id: base_form.findField('LpuSection_id').getValue(),
					MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
					MedPersonal_id: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
					userMedStaffFact: wnd.userMedStaffFact
				};
			}
		});
		var afterPregnancyResultChange = function (options) {
			if (options && options.resize) {
				wnd.resizeSpecificForWizardPanel();
			}
			if (options && options.recalc) {
				wnd.recalcBirthSpecStacDefaults();
			}
		};
		var beforeChildAdd = function (objectToReturn, addFn) {
			var category = wnd.WizardPanel.getCategory('Result');
			var categoryData = category.getCategoryData(category);
			if (categoryData && (categoryData.status.inlist([-1, 0]) || Ext.isEmpty(categoryData.EvnSection_id))) {
				//Перед добавлением новорожденного происходит сохранение движения
				//с измененными данными по беременности, если исход беременности ещё не был сохранен
				category.saveCategory(category, function () {
					wnd.doSave({silent: addFn});
				});
				return false;
			}
			return true;
		};
		var wizardValidator = function () {
			var valid = true;
			wnd.WizardPanel.categories.each(function (category) {
				if (category.loaded && category.validateCategory(category, true) === false) {
					valid = false;
					return false;
				}
			});
			return valid;
		};
		var afterPageChange = function () {
			wnd.resizeSpecificForWizardPanel();

			var category = wnd.WizardPanel.getCurrentCategory();

			if (category) {
				var values = category.getForm().getValues();
				wnd.PersonRegister_id = values.PersonRegister_id;

				wnd.WizardPanel.PrintResultButton.setVisible(category.name == 'Result' && !Ext.isEmpty(wnd.PersonRegister_id));
			}
		};

		var updateScreenNode = function (categoryData) {
			var nodeId = 'PregnancyScreen_' + categoryData.PregnancyScreen_id;
			var text = new Ext.Template('{date}, {period} нед., Пер. риск {risk}').apply({
				date: categoryData.PregnancyScreen_setDate,
				period: categoryData.amenordate || categoryData.embriondate || categoryData.uzidate || categoryData.fmovedate || '*',
				risk: '*'
			});

			switch (categoryData.status) {
				case 0:
					text += ' <span class="status created">Новый</span>';
					break;
				case 2:
					text += ' <span class="status updated">Изменен</span>';
					break;
				case 3:
					text += ' <span class="status deleted">Удален</span>';
					break;
			}

			var tplDelete = new Ext.Template('<span class="link delete" onclick="{method}(\'{categoryName}\', {id})">Удалить</span>');
			if (categoryData.status.inlist([0, 1, 2])) {
				text += tplDelete.apply({
					id: categoryData.PregnancyScreen_id,
					categoryName: 'Screen',
					method: "Ext.getCmp('" + wnd.getId() + "').deletePersonPregnancyCategory"
				});
			}

			var screenListNode = tree.nodeHash.ScreenList;
			var screenNode = screenListNode.findChild('id', nodeId);

			if (screenNode) {
				screenNode.attributes.date = categoryData.PregnancyScreen_setDate;
				screenNode.setText(text);
			} else {
				screenListNode.leaf = false;
				screenNode = screenListNode.appendChild({
					id: nodeId,
					object: 'Screen',
					value: 'PersonPregnancy',
					key: categoryData.PregnancyScreen_id,
					date: categoryData.PregnancyScreen_setDate,
					text: text,
					leaf: true
				});
				screenListNode.expand();
			}

			screenListNode.sort(function (node1, node2) {
				return Date.parseDate(node1.attributes.date, 'd.m.Y') > Date.parseDate(node2.attributes.date, 'd.m.Y');
			});

			tree.getSelectionModel().select(screenNode);
		};

		var updateCategoryNode = function (category, id, action) {
			var categoryData = category.getCategoryData(category, id);

			if (category.name == 'Screen') {
				updateScreenNode(categoryData);
			} else {
				var node = tree.nodeHash[category.name];
				if (node) {
					if (action == 'delete') {
						node.attributes.key = null;
						node.attributes.readOnly = true;
						node.attributes.deleted = true;
					} else {
						node.attributes.key = id;
						if (id < 0) {
							node.attributes.readOnly = false;
						}
						delete node.attributes.deleted;
					}
					node.attributes.key = (action == 'delete') ? null : id;

					var textEl = Ext.get(node.ui.elNode).child('.x-tree-node-anchor').child('span');

					if (textEl.child('.status')) {
						textEl.child('.status').remove();
					}
					if (textEl.child('.link')) {
						textEl.child('.link').remove();
					}

					switch (categoryData && categoryData.status) {
						case 0:
							textEl.createChild('<span class="status created">Новый</span>');
							break;
						case 2:
							textEl.createChild('<span class="status updated">Изменен</span>');
							break;
						case 3:
							textEl.createChild('<span class="status deleted">Удален</span>');
							break;
					}

					var tplCreate = new Ext.Template('<span class="link create" onclick="{method}(\'{categoryName}\')">Создать</span>');
					if (!categoryData) {
						textEl.createChild(tplCreate.apply({
							categoryName: category.name,
							method: "Ext.getCmp('" + wnd.getId() + "').createPersonPregnancyCategory"
						}));
					}

					var tplDelete = new Ext.Template('<span class="link delete" onclick="{method}(\'{categoryName}\', {id})">Удалить</span>');
					if (categoryData && categoryData.status.inlist([0, 1, 2])) {
						textEl.createChild(tplDelete.apply({
							id: node.attributes.key,
							categoryName: category.name,
							method: "Ext.getCmp('" + wnd.getId() + "').deletePersonPregnancyCategory"
						}));
					}
				}
			}
		};

		var saveCategory = function (category, callback) {
			if (category.validateCategory(category, true) === false) {
				return false;
			}

			if (category.beforeSaveCategory(category) === false) {
				return false;
			}

			var id = category[category.idField];
			category.collectCategoryData(category, (id < 0) ? 0 : 2);
			category.afterSaveCategory(category);

			if (typeof callback == 'function') callback();
		};

		var afterSaveCategory = function (category) {
			var categoryData = category.getCategoryData(category);

			if (category.name == 'Screen') {
				category.data.sort(function (a, b) {
					return Date.parseDate(a.PregnancyScreen_setDate, 'd.m.Y') > b.parseDate(b.PregnancyScreen_setDate, 'd.m.Y');
				});
			}

			if (category.name == 'Anketa' && categoryData.status == 0) {
				var node = tree.nodeHash.Anketa;
				tree.getLoader().baseParams.PersonRegister_id = categoryData.PersonRegister_id;
				tree.getLoader().baseParams.object = node.attributes.object;
				tree.getLoader().load(node, function () {
					node.expand(true);
					updateCategoryNode(category, categoryData[category.idField]);
					tree.getSelectionModel().select(node);
					wnd.specificsPanel.el.scrollIntoView(base_form.el);
				});
			} else /*if(category.name == 'Result') {

			} else*/ {
				updateCategoryNode(category, categoryData[category.idField]);
				wnd.specificsPanel.el.scrollIntoView(base_form.el);
			}
		};

		var beforeDeleteCategory = function (category, id) {
			if (category.name == 'Result') {
				if (sw.Promed.PersonPregnancy.ResultCategory.prototype.beforeDeleteCategory.apply(category, arguments) === false) {
					return false;
				}

				if (!category.allowDelete && id > 0) {
					var loadMask = wnd.WizardPanel.getLoadMask({msg: "Проверка возможности удаляения исхода..."});
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=PersonPregnancy&m=beforeDeleteBirthSpecStac',
						params: {BirthSpecStac_id: id},
						success: function (response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success) {
								category.allowDelete = true;
								category.deleteCategory(category, id);
							}
						},
						failure: function () {
							loadMask.hide();
						}
					});
					return false;
				}
			}
		};

		var deleteCategory = function (category, id) {
			var deleteCategory = function () {
				if (category.beforeDeleteCategory(category, id) === false) {
					return false;
				}

				var categoryData = category.getCategoryData(category, id);
				if (categoryData) {
					if (categoryData.status == 0) {
						category.removeCategoryData(category, id);
					} else {
						category.setCategoryDataValue(category, 'status', 3);
					}
				} else if (id > 0) {
					var conf = {status: 3, loaded: false};
					conf[category.idField] = id;

					category.data.add(id, conf);
				}

				delete category.wantDelete;
				delete category.allowDelete;
				category.afterDeleteCategory(category, id);
			};

			if (category.wantDelete) {
				deleteCategory();
			} else {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							category.wantDelete = true;
							deleteCategory();
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Вы хотите удалить запись?'),
					title: langs('Подтверждение')
				});
			}
		};

		var afterDeleteCategory = function (category, id) {
			switch (category.name) {
				case 'Screen':
					var parentNode = tree.nodeHash.ScreenList;
					var node = parentNode.findChild('key', id);

					parentNode.removeChild(node);
					break;
				case 'Anketa':
					updateCategoryNode(category, id, 'delete');

					var anketaNode = tree.nodeHash.Anketa;
					while (anketaNode.childNodes.length != 0) {
						anketaNode.removeChild(anketaNode.childNodes[anketaNode.childNodes.length - 1]);
					}
					anketaNode.leaf = true;
					anketaNode.ui.updateExpandIcon();
					break;
				default:
					updateCategoryNode(category, id, 'delete');
					break;
			}

			if (wnd.WizardPanel.getCurrentCategory() == category) {
				wnd.WizardPanel.resetCurrentCategory();
				wnd.WizardPanel.hide();
				wnd.specificsPanel.setHeight(220);
			}
		};

		var cancelCategory = function (category, onCancel) {
			switch (true) {
				case (category.name == 'Result' && !wnd.WizardPanel.deleteEvnSection && wnd.createdObjects.BirthSpecStac_id > 0):
					var childGrid = category.ChildGridPanel.getGrid();
					var childDeathGrid = category.ChildDeathGridPanel.getGrid();
					var allowDeleteChildren = true;

					childGrid.getStore().each(function (rec) {
						if (rec.get('PersonNewBorn_id').inlist(category.AddedPersonNewBorn_ids) &&
							(!Ext.isEmpty(rec.get('ChildEvnPS_id')) || !Ext.isEmpty(rec.get('BirthSvid_id')) || !Ext.isEmpty(rec.get('PntDeathSvid_id')))
						) {
							allowDeleteChildren = false;
							return false;
						}
					});

					childDeathGrid.getStore().each(function (rec) {
						if (rec.get('ChildDeath_id').inlist(category.AddedChildDeath_ids) && !Ext.isEmpty(rec.get('PntDeathSvid_id'))) {
							allowDeleteChildren = false;
							return false;
						}
					});

					if (!allowDeleteChildren) {
						sw.swMsg.alert(langs('Сообщение'), 'Для отмены исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
						return false;
					}

					var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Отмена добавления исхода беременности..."});
					loadMask.show();

					Ext.Ajax.request({
						success: function (response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								delete wnd.createdObjects.BirthSpecStac_id;
								wnd.treeLoaded = false;
								wnd.onSpecificsExpand(wnd.specificsPanel);
								onCancel();
							}
						},
						failure: function (response) {
							loadMask.hide();
						},
						params: {
							BirthSpecStac_id: wnd.createdObjects.BirthSpecStac_id
						},
						url: '/?c=PersonPregnancy&m=deleteBirthSpecStac'
					});
					break;
				case (category.name == 'Result' && !wnd.WizardPanel.deleteEvnSection && category.AddedChildDeath_ids.length > 0):
					var childDeathGrid = category.ChildDeathGridPanel.getGrid();
					var allowDeleteChildren = true;

					childDeathGrid.getStore().each(function (rec) {
						if (rec.get('ChildDeath_id').inlist(category.AddedChildDeath_ids) && !Ext.isEmpty(rec.get('PntDeathSvid_id'))) {
							allowDeleteChildren = false;
							return false;
						}
					});

					if (!allowDeleteChildren) {
						sw.swMsg.alert(langs('Сообщение'), 'Для отмены исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
						return false;
					}
					onCancel();
					break;
				case (category.name == 'Result' && !wnd.WizardPanel.deleteEvnSection && category.AddedPersonNewBorn_ids.length > 0):
					var childGrid = category.ChildGridPanel.getGrid();
					var allowDeleteChildren = true;

					childGrid.getStore().each(function (rec) {
						if (rec.get('PersonNewBorn_id').inlist(category.AddedPersonNewBorn_ids) &&
							(!Ext.isEmpty(rec.get('ChildEvnPS_id')) || !Ext.isEmpty(rec.get('BirthSvid_id')) || !Ext.isEmpty(rec.get('PntDeathSvid_id')))
						) {
							allowDeleteChildren = false;
							return false;
						}
					});

					if (!allowDeleteChildren) {
						sw.swMsg.alert(langs('Сообщение'), 'Для отмены исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
						return false;
					}

					var params = {
						PersonNewBorn_ids: Ext.util.JSON.encode(category.AddedPersonNewBorn_ids)
					}

					var loadMask = category.wizard.getLoadMask({msg: "Отмена добавления детей..."});
					loadMask.show();

					Ext.Ajax.request({
						url: '/?c=BirthSpecStac&m=deleteChildren',
						params: params,
						success: function (response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								loadMask.hide();
								category.AddedPersonNewBorn_ids = [];
								onCancel();
							}
						},
						failure: function (response) {
							loadMask.hide();
						}
					});
					break;
				default:
					onCancel();
			}
		};

		wnd.WizardPanel = new sw.Promed.PersonPregnancy.WizardFrame({
			id: 'ESEW_PersonPregnancyWizard',
			maskEl: wnd.specificsPanel.getEl(),
			readOnly: wnd.action == 'view',
			inputData: inputData,
			isValid: wizardValidator,
			afterPageChange: afterPageChange,
			saveCategory: saveCategory,
			afterSaveCategory: afterSaveCategory,
			beforeDeleteCategory: beforeDeleteCategory,
			deleteCategory: deleteCategory,
			afterDeleteCategory: afterDeleteCategory,
			cancelCategory: cancelCategory,
			allowCollectData: true,
			categories: [
				/*new sw.Promed.PersonPregnancy.AnketaCategory({
					saveCategory: saveCategory
				}),
				new sw.Promed.PersonPregnancy.ScreenCategory({
					saveCategory: saveCategory,
					deleteCategory: deleteCategory
				}),
				new sw.Promed.PersonPregnancy.EvnListCategory,
				new sw.Promed.PersonPregnancy.ConsultationListCategory,
				new sw.Promed.PersonPregnancy.ResearchListCategory,
				new sw.Promed.PersonPregnancy.CertificateCategory,*/
				new sw.Promed.PersonPregnancy.ResultCategory({
					saveCategory: saveCategory,
					afterPregnancyResultChange: afterPregnancyResultChange,
					beforeChildAdd: beforeChildAdd,
					allowSaveButton: false,
					id: "resultCategoryEvnSectionPanel"
				})/*,
				new sw.Promed.PersonPregnancy.DeathMotherCategory({
					readOnly: true
				})*/
			]
		});

		wnd.specificsFormsPanel.add(wnd.WizardPanel);
		wnd.specificsFormsPanel.doLayout();
		wnd.WizardPanel.init();

		/*wnd.WizardPanel.PrintResultButton = wnd.WizardPanel.DataToolbar.insertButton(4, {
			hidden: true,
			handler: function() {wnd.printPregnancyResult()},
			iconCls: 'print16',
			text: 'Печать исхода беременности'
		});*/
		wnd.WizardPanel.PrintResultButton = Ext.getCmp('ESEW_PrintPregnancyResultButton');
	},
	checkPersonNewBorn: function (callback) {
		callback = callback || Ext.emptyFn;
		var parentWin = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();

		Ext.Ajax.request({
			callback: function (options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText)[0];
					parentWin.editPersonNewBorn = response_obj.editPersonNewBorn;
					callback();
				} else {
					sw.swMsg.alert('Ошибка', 'Ошибка при проверке возможности заполнения специфики новорожденного');
				}
			},
			params: {
				EvnPS_id: parentWin.EvnPS_id,
				Person_id: base_form.findField('Person_id').getValue()
			},
			url: '/?c=PersonNewBorn&m=chekPersonNewBorn',
			method: 'POST'
		});
	},
	specificsFormsPanelEnableEdit: function (formName, enable) {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		switch (formName) {
			// Сведения об аборте
			case 'EvnAbortForm':
				base_form.findField('EvnAbort_setDate').setDisabled(!enable);
				base_form.findField('AbortType_id').setDisabled(!enable);
				base_form.findField('EvnAbort_PregSrok').setDisabled(!enable);
				base_form.findField('EvnAbort_PregCount').setDisabled(!enable);
				base_form.findField('AbortPlace_id').setDisabled(!enable);
				base_form.findField('EvnAbort_IsMed').setDisabled(!enable);
				base_form.findField('EvnPLAbort_IsHIV').setDisabled(!enable);
				base_form.findField('EvnPLAbort_IsInf').setDisabled(!enable);
				break;
			// Сведения о новорожденном
			case 'PersonNewBornForm':
				base_form.findField('ChildTermType_id').setDisabled(!enable);
				base_form.findField('FeedingType_id').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsBCG').setDisabled(!enable);
				base_form.findField('PersonNewBorn_BCGSer').setDisabled(!enable);
				base_form.findField('PersonNewBorn_BCGNum').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsAidsMother').setDisabled(!enable);
				base_form.findField('ChildPositionType_id').setDisabled(!enable);
				base_form.findField('PersonNewBorn_CountChild').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsRejection').setDisabled(!enable);
				base_form.findField('PersonNewBorn_id').setDisabled(!enable);

				base_form.findField('PersonNewBorn_IsHepatit').setDisabled(!enable);
				base_form.findField('PersonNewBorn_BCGDate').setDisabled(!enable);
				base_form.findField('PersonNewBorn_Weight').setDisabled(!enable);
				base_form.findField('PersonNewBorn_Height').setDisabled(!enable);
				base_form.findField('PersonNewBorn_Head').setDisabled(!enable);
				base_form.findField('PersonNewBorn_Breast').setDisabled(!enable);
				base_form.findField('PersonNewBorn_HepatitNum').setDisabled(!enable);
				base_form.findField('PersonNewBorn_HepatitSer').setDisabled(!enable);
				base_form.findField('PersonNewBorn_HepatitDate').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsAudio').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsBleeding').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsNeonatal').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsBreath').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsHeart').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsPulsation').setDisabled(!enable);
				base_form.findField('PersonNewBorn_IsMuscle').ssetDisabled(!enable);
				base_form.findField('NewBornWardType_id').setDisabled(!enable);
				if (getRegionNick() == 'ufa') {
					base_form.findField('RefuseType_pid').setDisabled(!enable);
					base_form.findField('RefuseType_aid').setDisabled(!enable);
					base_form.findField('RefuseType_bid').setDisabled(!enable);
					base_form.findField('RefuseType_gid').setDisabled(!enable);
				}
				break;
		}
	},
	setTariffComboEnabled: function (record, base_form, TariffClass_id) {
		if (getRegionNick().inlist(['ufa', 'adygeya'])) {
			return false;
		}

		if (record && Number(record.get('LpuUnitType_Code')) == 4) { // Стационар на дому
			base_form.findField('TariffClass_id').setAllowBlank(getRegionNick().inlist(['astra', 'kareliya', 'penza']));

			if (getRegionNick() == 'astra' && this.action == 'add') {
				base_form.findField('TariffClass_id').disable();
			} else if (this.action != 'view') {
				base_form.findField('TariffClass_id').enable();
			}

			if ( !Ext.isEmpty(TariffClass_id) ) {
				var index = base_form.findField('TariffClass_id').getStore().findBy(function(rec) {
					return rec.get('TariffClass_id') == TariffClass_id;
				});
				if ( index >= 0 ) {
					base_form.findField('TariffClass_id').setValue(TariffClass_id);
				}
			}
		}
		else {
			base_form.findField('TariffClass_id').setAllowBlank(true);
			base_form.findField('TariffClass_id').clearValue();
			base_form.findField('TariffClass_id').disable();
		}
	},
	onchange_LpuSectionCombo: function (combo, newValue, oldValue) {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var index = this.findById(this.id + '_LpuSectionCombo').getStore().findBy(function (rec) {
			return (rec.get('LpuSection_id') == newValue);
		});
		var record = this.findById(this.id + '_LpuSectionCombo').getStore().getAt(index);
		var TariffClass_id = base_form.findField('TariffClass_id').getValue();

		if (combo.oldValue === undefined) {
			combo.oldValue = combo.originalValue;
		}

		base_form.findField('TariffClass_id').clearValue();
		base_form.findField('TariffClass_id').setAllowBlank(true);

		if (
			getRegionNick().inlist(['krym']) && newValue != combo.oldValue &&
			!Ext.isEmpty(combo.getFieldValue('LpuSectionBedProfile_id')) && combo.getFieldValue('LpuSectionBedProfile_id') > 0
		) {
			base_form.findField('LpuSectionBedProfile_id').setValue(combo.getFieldValue('LpuSectionBedProfile_id'));
		}

		// Добавить еще загрузку справочника МЭС
		//...
		this.setTariffComboEnabled(record, base_form, TariffClass_id);
		this.loadMesCombo();
		this.loadKSGKPGKOEF();
		this.checkMesOldUslugaComplexFields();
		this.loadLpuSectionProfileDop();
		this.filterRehabScaleCombo();
		this.checkAccessAddEvnSectionDrugPSLink();
		this.refreshFieldsVisibility(['EvnSection_BarthelIdx']);
		combo.oldValue = newValue;
	},
	collectGridData: function (gridName) {
		var result = '';
		if (this.findById('MHW_' + gridName)) {
			var grid = this.findById('MHW_' + gridName).getGrid();
			grid.getStore().clearFilter();
			if (grid.getStore().getCount() > 0) {
				if ((grid.getStore().getCount() == 1) && ((grid.getStore().getAt(0).data.RecordStatus_Code == undefined))) {
					return '';
				}
				var gridData = getStoreRecords(grid.getStore(), {convertDateFields: true});
				result = Ext.util.JSON.encode(gridData);
			}
			grid.getStore().filterBy(function (rec) {
				return Number(rec.get('RecordStatus_Code')) != 3;
			});
		}
		return result;
	},
	openPersonHeightEditWindow: function (action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swPersonHeightEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования измерения длины уже открыто');
			return false;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid = this.findById('ESecEF_PersonHeightGrid').getGrid();
		var params = new Object();

		var measure_type_exceptions = new Array();

		grid.getStore().each(function (rec) {
			if (rec.get('HeightMeasureType_id') && rec.get('HeightMeasureType_Code').toString().inlist(['1', '2'])) {
				measure_type_exceptions.push(rec.get('HeightMeasureType_Code'));
			}
		});

		params.action = action;
		params.callback = function (data) {
			if (!data || !data.personHeightData) {
				return false;
			}

			data.personHeightData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.personHeightData.PersonHeight_id);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.personHeightData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.personHeightData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PersonHeight_id')) {
					grid.getStore().removeAll();
				}

				data.personHeightData.PersonHeight_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([data.personHeightData], true);
			}
		}
		params.formMode = 'local';
		params.formParams = new Object();
		params.measureTypeExceptions = measure_type_exceptions;
		params.personMode = 'child';

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
			params.formParams.Person_id = base_form.findField('Person_id').getValue();
			params.formParams.Server_id = base_form.findField('Server_id').getValue();
			params.formParams.PersonHeight_setDate = base_form.findField('EvnSection_setDate').getValue();
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function () {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('swPersonHeightEditWindow').show(params);
	},
	openPersonWeightEditWindow: function (action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swPersonWeightEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования измерения массы уже открыто');
			return false;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid = this.findById('ESecEF_PersonWeightGrid').getGrid();
		var params = new Object();

		var measure_type_exceptions = new Array();

		grid.getStore().each(function (rec) {
			if (rec.get('WeightMeasureType_id') && rec.get('WeightMeasureType_Code').toString().inlist(['1', '2'])) {
				measure_type_exceptions.push(rec.get('WeightMeasureType_Code'));
			}
		});

		params.action = action;
		params.callback = function (data) {
			if (!data || !data.personWeightData) {
				return false;
			}

			data.personWeightData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.personWeightData.PersonWeight_id);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.personWeightData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.personWeightData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PersonWeight_id')) {
					grid.getStore().removeAll();
				}

				data.personWeightData.PersonWeight_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([data.personWeightData], true);
			}
		}
		params.formMode = 'local';
		params.formParams = new Object();
		params.measureTypeExceptions = measure_type_exceptions;
		params.personMode = 'child';

		if (action == 'add') {
			params.onHide = Ext.emptyFn;
			params.formParams.Person_id = base_form.findField('Person_id').getValue();
			params.formParams.Server_id = base_form.findField('Server_id').getValue();
			params.formParams.PersonWeight_setDate = base_form.findField('EvnSection_setDate').getValue();
		}
		else {
			if (!grid.getSelectionModel().getSelected()) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			params.formParams = selected_record.data;
			params.onHide = function () {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('swPersonWeightEditWindow').show(params);
	},
	setDiagPred: function (DiagPred_id) {
		var diag_combo = this.findById('EvnSectionEditForm').getForm().findField('Diag_id');
		var diag_id = DiagPred_id;
		diag_combo.getStore().removeAll();
		diag_combo.clearValue();
		diag_combo.getStore().load({
			callback: function () {
				diag_combo.setValue(diag_id);
				diag_combo.onChange(diag_combo, diag_id);
				diag_combo.getStore().each(function (record) {
					if (record.get('Diag_id') == diag_id) {
						diag_combo.fireEvent('select', diag_combo, record, 0);
						this.loadMesCombo();
						this.loadKSGKPGKOEF();
						this.checkMesOldUslugaComplexFields();
						this.loadMes2Combo(-1, true);
						this.changeDiag(diag_combo, diag_id);
						if (this.showHTMedicalCareClass) {
							this.findById('EvnSectionEditForm').getForm().findField('HTMedicalCareClass_id').clearValue();
							this.loadHTMedicalCareClassCombo();
						}
					}
				}.createDelegate(this));
			}.createDelegate(this),
			params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
		});
	},
	deleteGridSelectedRecord: function (gridId, idField) {
		var grid = this.findById(gridId).getGrid();
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
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

							grid.getStore().filterBy(function (rec) {
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
			msg: 'Вы действительно хотите удалить эту запись?',
			title: 'Вопрос'
		});
	},
	deletePersonHeight: function () {
		this.deleteGridSelectedRecord('ESecEF_PersonHeightGrid', 'PersonHeight_id');
	},
	deletePersonWeight: function () {
		this.deleteGridSelectedRecord('ESecEF_PersonWeightGrid', 'PersonWeight_id');
	},
	checkChildWeight: function () {
		if (this.NewBorn_Weight == 0)
			return false;
		else
			return true;
	},
	checkBeamForm: function () { // проверка на заполненность формы лучевого лечения
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var form_fields = new Array(
			'EvnUslugaOnkoBeam_setDate',
			'EvnUslugaOnkoBeam_setTime',
			'EvnUslugaOnkoBeam_disDate',
			'EvnUslugaOnkoBeam_disTime',
			'OnkoUslugaBeamIrradiationType_id',
			'OnkoUslugaBeamKindType_id',
			'OnkoUslugaBeamMethodType_id',
			'OnkoUslugaBeamRadioModifType_id',
			'OnkoUslugaBeamFocusType_id',
			'EvnUslugaOnkoBeam_TotalDoseTumor',
			'EvnUslugaOnkoBeam_TotalDoseRegZone',
			'OnkoUslugaBeamUnitType_id',
			'OnkoUslugaBeamUnitType_did'
		);
		var res = false;
		for (var i = 0; i < form_fields.length; i++) {
			var a = base_form.findField(form_fields[i]).getValue();
			if (a.length != 0 && a != 0 && a != null) {
				res = true;
			}
		}
		return res;
	},
	checkUslugaGrid: function (gridName) {
		if (this.findById(this.id + gridName).getCount() > 0) {
			var res = false;
			this.findById(this.id + gridName).getGrid().getStore().each(function (record) {
				if (record.data.EvnUsluga_pid == this.formParams.EvnSection_id) {
					res = true;
				}
			}.createDelegate(this));
			return res;
		} else {
			return false;
		}
	},
	enableBeamFormEdit: function (enable) { //
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var form_fields = new Array(
			'EvnUslugaOnkoBeam_setDate',
			'EvnUslugaOnkoBeam_setTime',
			'EvnUslugaOnkoBeam_disDate',
			'EvnUslugaOnkoBeam_disTime',
			'OnkoUslugaBeamIrradiationType_id',
			'OnkoUslugaBeamKindType_id',
			'OnkoUslugaBeamMethodType_id',
			'OnkoUslugaBeamRadioModifType_id',
			'OnkoUslugaBeamFocusType_id',
			'EvnUslugaOnkoBeam_TotalDoseTumor',
			'EvnUslugaOnkoBeam_TotalDoseRegZone',
			'OnkoUslugaBeamUnitType_id',
			'OnkoUslugaBeamUnitType_did'
		);
		for (var i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}
	},
	enableAnatomFormEdit: function (enable) {
		var wnd = this;
		var base_form = wnd.findById('EvnSectionEditForm').getForm();
		var form_fields = new Array(
			'EvnDie_IsAnatom',
			'EvnDie_expDate',
			'EvnDie_expTime',
			'AnatomWhere_id',
			'Diag_aid'
		);
		for (var i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if (enable) {
			this.buttons[0].show();
		} else {
			this.buttons[0].hide();
		}
	},
	checkOneSpecThreat: function () {

		this.findById(this.id + '_EvnUslugaGrid').setActionDisabled('action_add', false);
		this.findById(this.id + '_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', false);
		this.findById(this.id + '_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', false);
		this.enableBeamFormEdit(true);

		if (this.checkUslugaGrid('_EvnUslugaGrid')) {
			this.findById(this.id + '_EvnUslugaGrid').setActionDisabled('action_add', false);
			this.findById(this.id + '_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', true);
			this.findById(this.id + '_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', true);
			this.enableBeamFormEdit(false);
		}
		else if (this.checkBeamForm()) {
			this.findById(this.id + '_EvnUslugaGrid').setActionDisabled('action_add', true);
			this.findById(this.id + '_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', true);
			this.findById(this.id + '_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', true);
			this.enableBeamFormEdit(true);
		}
		else if (this.checkUslugaGrid('_EvnUslugaOnkoChemGrid')) {
			this.findById(this.id + '_EvnUslugaGrid').setActionDisabled('action_add', true);
			this.findById(this.id + '_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', true);
			this.findById(this.id + '_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', false);
			this.enableBeamFormEdit(false);
		}
		else if (this.checkUslugaGrid('_EvnUslugaOnkoGormunGrid')) {
			this.findById(this.id + '_EvnUslugaGrid').setActionDisabled('action_add', true);
			this.findById(this.id + '_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', false);
			this.findById(this.id + '_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', true);
			this.enableBeamFormEdit(false);
		}

	},
	openEvnUslugaEditWindow: function (action, grid_id, sys_nick, confirmed) {
		if (this.findById('ESecEF_EvnUslugaPanel').hidden) {
			return false;
		}

		if (action != 'add' && action != 'addOper' && action != 'edit' && action != 'view') {
			return false;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid = this.findById('ESecEF_EvnUslugaGrid');

		if (this.action == 'view') {
			if (action == 'add' || action == 'addOper') {
				return false;
			}
			else if (action == 'edit') {
				action = 'view';
			}
		}

		var params = new Object();

		params.action = action;
		params.callback = function (data) {
			if (!data || !data.evnUslugaData) {
				grid.getStore().load({
					params: {
						pid: base_form.findField('EvnSection_id').getValue()
					}
				});
				return false;
			}

			var record = grid.getStore().getById(data.evnUslugaData.EvnUsluga_id);

			if (!record) {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUsluga_id')) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([data.evnUslugaData], true);
			}
			else {
				var evn_usluga_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function (key, item) {
					evn_usluga_fields.push(key);
				});

				for (i = 0; i < evn_usluga_fields.length; i++) {
					record.set(evn_usluga_fields[i], data.evnUslugaData[evn_usluga_fields[i]]);
				}

				record.commit();

			}

			this.savedMesTariff_id = null; // при сохранении услуги, сохранённый коэфф уже не учитываем, выставляем КСГ автоматически
			this.loadKSGKPGKOEF();
			this.loadEvnSectionKSGGrid();
			this.checkMesOldUslugaComplexFields();
			this.EvnUslugaGridIsModified = true;
		}.createDelegate(this);
		params.onHide = function () {
			if (grid.getSelectionModel().getSelected()) {
				grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
			}
			else {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}.createDelegate(this);
		params.parentClass = 'EvnSection';
		params.Person_id = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname');

		// Собрать данные для ParentEvnCombo
		var parent_evn_combo_data = new Array();

		// Формируем parent_evn_combo_data
		var evn_section_id = base_form.findField('EvnSection_id').getValue();
		var evn_section_set_date = base_form.findField('EvnSection_setDate').getValue();
		var evn_section_set_time = base_form.findField('EvnSection_setTime').getValue();
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var lpu_section_profile_id = base_form.findField('LpuSectionProfile_id').getValue();
		var lpu_unit_type_sys_nick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');
		var lpu_section_name = '';
		var med_personal_fio = '';
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var diag_id = base_form.findField('Diag_id').getValue();

		if ((action == 'add' || action == 'addOper') && (!evn_section_set_date || !lpu_section_id || !med_staff_fact_id)) {
			sw.swMsg.alert('Ошибка', 'Не заполнены обязательные поля по движению');
			return false;
		}

		record = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id);
		if (record) {
			lpu_section_name = record.get('LpuSection_Name');
		}

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if (record) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		parent_evn_combo_data.push({
			Evn_id: evn_section_id,
			Evn_Name: Ext.util.Format.date(evn_section_set_date, 'd.m.Y') + ' / ' + lpu_section_name + ' / ' + med_personal_fio,
			Evn_setDate: evn_section_set_date,
			Evn_disDate: base_form.findField('EvnSection_disDate').getValue(),
			Evn_setTime: evn_section_set_time,
			MedStaffFact_id: med_staff_fact_id,
			LpuSection_id: lpu_section_id,
			LpuSectionProfile_id: lpu_section_profile_id,
			LpuUnitType_SysNick: lpu_unit_type_sys_nick,
			MedPersonal_id: med_personal_id,
			Diag_id: diag_id
		});

		if (getRegionNick() == 'perm' && base_form.findField('EvnSection_RepFlag').checked) {
			params.ignorePaidCheck = 1;
		}

		if (getRegionNick() == 'perm') {
			var disDate = new Date();
			if (base_form.findField('CureResult_id').getFieldValue('CureResult_Code') == 1 && !Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())) {
				disDate = base_form.findField('EvnSection_disDate').getValue();
			}
			else if (Ext.isArray(this.OtherEvnSectionList) && this.OtherEvnSectionList.length > 0) {
				for (var i = 0; i < this.OtherEvnSectionList.length; i++) {
					var EvnSection = this.OtherEvnSectionList[i];
					if (EvnSection.CureResult_Code == 1) {
						disDate = EvnSection.EvnSection_disDate;
						break;
					}
				}
			}
			params.UslugaComplex_Date = Ext.util.Format.date(disDate, 'd.m.Y');
		}

		switch (action) {
			case 'add':
			case 'addOper':

				params.action = 'add';
				if (base_form.findField('EvnSection_id').getValue() == 0) {
					this.doSave({
						ignoreEvnUslugaCountCheck: true,
						openChildWindow: function () {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this),
						print: false
					});
					return false;
				}

				params.formParams = {
					Diag_id: base_form.findField('Diag_id').getValue(),
					PayType_id: base_form.findField('PayType_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue()
				}
				params.parentEvnComboData = parent_evn_combo_data;

				if (action == 'addOper') {
					getWnd('swEvnUslugaOperEditWindow').show(params);
				} else {
					getWnd('swEvnUslugaEditWindow').show(params);
				}
				break;

			case 'edit':
			case 'view':
				// Открываем форму редактирования услуги (в зависимости от EvnClass_SysNick)

				var selected_record = grid.getSelectionModel().getSelected();

				if (!selected_record || !selected_record.get('EvnUsluga_id')) {
					return false;
				}

				params.archiveRecord = this.archiveRecord;

				var evn_usluga_id = selected_record.get('EvnUsluga_id');

				switch (selected_record.get('EvnClass_SysNick')) {
					case 'EvnUslugaOnkoBeam':
					case 'EvnUslugaOnkoChem':
					case 'EvnUslugaOnkoGormun':
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: evn_usluga_id
						}
						params.parentEvnComboData = parent_evn_combo_data;

						getWnd('swEvnUslugaEditWindow').show(params);
						break;
					case 'EvnUslugaOnkoSurg':
						params.EvnUslugaOnkoSurg_id = evn_usluga_id;
						params.formParams = {
							EvnUslugaOnkoSurg_id: evn_usluga_id
						}
						getWnd('swEvnUslugaOnkoSurgEditWindow').show(params);
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
	openRepositoryObservEditWindow: function (action) {
		if (this.RepositoryObservGrid.hidden) {
			return false;
		}

		if (Ext.isEmpty(action) || !action.inlist([ 'add', 'edit', 'view'])) {
			return false;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid = this.RepositoryObservGrid.getGrid();

		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			}
			else if (action == 'edit') {
				action = 'view';
			}
		}

		if (action == 'add' && base_form.findField('EvnSection_id').getValue() == 0) {
			this.doSave({
				openChildWindow: function () {
					this.openRepositoryObservEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		var params = {};

		params.action = action;
		params.useCase = 'evnsection';
		params.callback = function() {
			this.RepositoryObservGrid.loadData({
				globalFilters: {
					Evn_id: base_form.findField('EvnSection_id').getValue()
				}
			});
		}.createDelegate(this);
		params.Evn_id = base_form.findField('EvnSection_id').getValue();
		params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		params.RepositoryObserv_Height = this.RepositoryObserv_Height;
		params.RepositoryObserv_Weight = this.RepositoryObserv_Weight;

		if (action.inlist(['edit','view'])) {
			var selected_record = grid.getSelectionModel().getSelected();

			if (!selected_record || !selected_record.get('RepositoryObserv_id')) {
				return false;
			}

			params.RepositoryObserv_id = selected_record.get('RepositoryObserv_id');
		} else {
			params.CovidType_id = this.getCovidTypeId();
		}

		getWnd('swRepositoryObservEditWindow').show(params);
	},
	uslugaComplexLoading: false,
	refreshPregnancyEvnPSFieldSet: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var fieldSet = this.findById(this.id + '_PregnancyEvnPSFields');
		var persFrame = this.findById('ESecEF_PersonInformationFrame');

		var date = base_form.findField('EvnSection_setDate').getValue();
		var sex = persFrame.getFieldValue('Sex_Code');
		var birthday = persFrame.getFieldValue('Person_Birthday');

		if (Ext.isEmpty(date) || Ext.isEmpty(birthday) || Ext.isEmpty(sex)) {
			fieldSet.hide();
			return;
		}

		var age = swGetPersonAge(birthday, date);

		if (sex == 2 && age >= 15 && age <= 50) {
			fieldSet.show();
		} else {
			fieldSet.hide();
			base_form.findField('PregnancyEvnPS_Period').setValue(null);
		}
	},
	refreshFieldsVisibility: function (fieldNames, noReset) {
		var win = this;
		var base_form = win.findById('EvnSectionEditForm').getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();
		var EvnSectionList = win.OtherEvnSectionList;
		var isLast = win.evnSectionIsLast;

		var createDT = function (date, time) {
			var dt = (date instanceof Date) ? date : new Date();
			var t = (!Ext.isEmpty(time) ? time : '00:00').split(':');
			dt.setHours(t[0], t[1], 0, 0);
			return dt;
		};

		base_form.items.each(function (field) {
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var visible = null;
			var enable = null;
			var filter = null;

			var date20180101 = new Date(2018, 0, 1); // 01.01.2018
			var dateX = new Date(2017, 8, 1); // 01.09.2017
			var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();
			var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();
			var EvnSection_setTime = base_form.findField('EvnSection_setTime').getValue();
			var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');
			var LpuSection_Code = base_form.findField('LpuSection_id').getFieldValue('LpuSection_Code');
			var LpuSectionProfile_Code = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code');
			var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
			var DeseaseType_SysNick = base_form.findField('DeseaseType_id').getFieldValue('DeseaseType_SysNick');
			var CureResult_Code = base_form.findField('CureResult_id').getFieldValue('CureResult_Code');
			var PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
			var LeaveType_SysNick = base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick');
			var HTMedicalCareClass_Code = base_form.findField('HTMedicalCareClass_id').getFieldValue('HTMedicalCareClass_Code');

			var set_or_cur_date = createDT(EvnSection_setDate, EvnSection_setTime);
			var diag_code_full = !Ext.isEmpty(Diag_Code) ? String(Diag_Code).slice(0, 3) : '';
			var lpu_section_code_part = !Ext.isEmpty(LpuSection_Code) ? String(LpuSection_Code).slice(2) : '';

			var filterPrevEvnSectionList = function (EvnSection) {
				return EvnSection.EvnSection_setDT < set_or_cur_date;
			};
			var filterEvnSectionListWithSameDiag = function (EvnSection) {
				return EvnSection.Diag_Code == Diag_Code;
			};
			var filterPrevEvnSectionListWithSameDiag = function (EvnSection) {
				return filterPrevEvnSectionList(EvnSection) && filterEvnSectionListWithSameDiag(EvnSection);
			}
			var filterEvnSectionWithDeseaseBegTimeType = function (EvnSection) {
				return !Ext.isEmpty(EvnSection.DeseaseBegTimeType_id);
			};
			var getLastEvnSection = function (last, current) {
				return (last && last.EvnSection_setDT > current.EvnSection_setDT) ? last : current;
			};

			switch (field.getName()) {
				case 'DeseaseBegTimeType_id':
					visible = (
						Region_Nick == 'kareliya' &&
						LpuUnitType_SysNick == 'stac' &&
						lpu_section_code_part.inlist(['111', '112']) && (
							(diag_code_full >= 'I60' && diag_code_full <= 'I64') ||
							(diag_code_full >= 'G45' && diag_code_full <= 'G46')
						) && (
							(!Ext.isEmpty(EvnSection_setDate) && EvnSection_setDate >= dateX)
							|| (!Ext.isEmpty(EvnSection_disDate) && EvnSection_disDate >= dateX)
						)
					);
					if (visible && win.formLoaded) {
						var lastEvnSectionWithSameDiag = EvnSectionList
							.filter(filterPrevEvnSectionListWithSameDiag)
							.filter(filterEvnSectionWithDeseaseBegTimeType)
							.reduce(getLastEvnSection, null);

						if (lastEvnSectionWithSameDiag) {
							value = lastEvnSectionWithSameDiag.DeseaseBegTimeType_id;
						}
					}
					allowBlank = !visible;
					break;
				case 'DeseaseType_id':
					var dateX20181101 = new Date(2018, 10, 1); // 01.11.2018

					visible = (
						Region_Nick != 'kz'
						&& !Ext.isEmpty(diag_code_full)
						&& diag_code_full.substr(0, 1) != 'Z'
					);
					allowBlank = true;

					if (
						Region_Nick == 'kareliya'
						&& visible == true
						&& (
							(typeof EvnSection_setDate == 'object' && EvnSection_setDate >= dateX20181101)
							|| (typeof EvnSection_disDate == 'object' && EvnSection_disDate >= dateX20181101)
							|| (diag_code_full >= 'C00' && diag_code_full <= 'C97')
							|| (diag_code_full >= 'D00' && diag_code_full <= 'D09')
						)
					) {
						allowBlank = false;
					}

					if (
						Region_Nick == 'ufa'
						&& visible == true
					) {
						allowBlank = false;
					}
					if(
						visible == true &&
						(
							(typeof EvnSection_setDate == 'object' && EvnSection_setDate >= dateX20181101)
							|| (typeof EvnSection_disDate == 'object' && EvnSection_disDate >= dateX20181101)
						)
					) {
						allowBlank = false;
					}

					var releaseDate = base_form.findField('EvnSection_disDate').getValue();
					if(!releaseDate) {
						var releaseDateArr = getGlobalOptions().date.split('.')
						releaseDate = new Date(releaseDateArr[2], releaseDateArr[1] - 1, releaseDateArr[0]);
					}
					base_form.findField('DeseaseType_id').getStore().clearFilter();
					base_form.findField('DeseaseType_id').lastQuery = '';
					base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
						return (
							(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= releaseDate)
							&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= releaseDate)
						)
					});

					break;
				case 'TumorStage_id':
					visible = (
						(
							Region_Nick.inlist(['kareliya']) && (
								(diag_code_full >= 'C00' && diag_code_full <= 'C97') ||
								(diag_code_full >= 'D00' && diag_code_full <= 'D09')
							) && (
								(!Ext.isEmpty(EvnSection_setDate) && EvnSection_setDate >= dateX) ||
								(!Ext.isEmpty(EvnSection_disDate) && EvnSection_disDate >= dateX)
							)
						) || (
							Region_Nick.inlist(['ekb']) && (
								(diag_code_full >= 'C00' && diag_code_full <= 'C97') ||
								(diag_code_full >= 'D00' && diag_code_full <= 'D09')
							)
						)/* || (
							Region_Nick.inlist(['penza']) && (
								diag_code_full >= 'C00' && diag_code_full <= 'C97'
							)
						)*/
					);
					if (visible) {
						enable = Region_Nick.inlist(['ekb'/*, 'penza'*/]) || DeseaseType_SysNick == 'new';
						if (!enable) value = null;
						if (Region_Nick.inlist(['kareliya', 'ekb'])) {
							if (getRegionNick() != 'ekb') {
								filter = function (record) {
									return record.get('TumorStage_Code').inlist([0, 1, 2, 3, 4])
								};
							}
						}
					}
					allowBlank = !enable;
					break;
				case 'PainIntensity_id':
					visible = (
						Region_Nick.inlist(['penza']) && (
							diag_code_full >= 'C00' && diag_code_full <= 'C97'
						)
					);
					break;
				case 'RankinScale_id':
					if (Region_Nick.inlist(['penza'])) {
						//Для остальных регионов работает showRankinScale
						visible = (LpuSectionProfile_Code == 158);
						allowBlank = !visible;
						if (LpuUnitType_SysNick == 'stac') {
							filter = function (record) {
								return record.get('RankinScale_Code').inlist([3, 4, 5])
							};
						} else {
							filter = function (record) {
								return record.get('RankinScale_Code').inlist([1, 2, 3])
							};
						}
					}
					break;
				case 'DrugTherapyScheme_ids':
					visible = win.hasDrugTherapySchemeLinks;
					break;
				case 'MesDop_ids':
					visible = getRegionNick() != 'kz' && !Ext.isEmpty(Diag_Code);
					break;
				case 'RehabScale_id':
					switch (Region_Nick) {
						case 'ufa':
							var xdate = new Date(2018, 0, 1);
							visible = (
								(!Ext.isEmpty(EvnSection_setDate) && EvnSection_setDate >= xdate)
								|| (!Ext.isEmpty(EvnSection_disDate) && EvnSection_disDate >= xdate)
							) ? true : false;
							break;
						case 'penza':
							visible = (LpuSectionProfile_Code == 158);
							allowBlank = !visible;
							break;
						default:
							visible = win.hasRehabScaleLinks;
							break;
					}
					break;
				case 'RehabScale_vid':
					switch (Region_Nick) {
						case 'ufa':
							visible = !Ext.isEmpty(EvnSection_disDate);
							break;
						default:
							visible = false;
							break;
					}
					break;
				case 'EvnSection_SofaScalePoints':
					visible = win.hasSofaLinks;
					break;
				case 'EvnSection_BarthelIdx':
					var Person_Birthday = win.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
					var age = swGetPersonAge(Person_Birthday, base_form.findField('EvnSection_setDate').getValue());

					var hasSopR54 = false;
					win.findById('ESecEF_EvnDiagPSGrid').getStore().each(function (rec) {
						if (!Ext.isEmpty(rec.get('Diag_Code')) && rec.get('Diag_Code').slice(0, 3) == 'R54') {
							hasSopR54 = true;
						}
					});

					if (
						(
							(Region_Nick.inlist(['perm', 'krym', 'astra', 'penza']) && age >= 60) // возраст пациента на ДНЛ составляет 60 лет или более (60 лет и один день, 60 лет и два дня…, 61 год и так далее);
							|| Region_Nick.inlist(['kareliya'])
						)
						&& LpuUnitType_SysNick == 'stac' // движение относится к КСС;
						&& (
							diag_code_full == 'R54'
							|| (getRegionNick() == 'krym' && hasSopR54)
						)
					) {
						visible = true;
					} else {
						visible = false;
					}

					if (Region_Nick.inlist(['kareliya'])) {
						allowBlank = !visible;
					}
					break;
				case 'EvnSection_isPartialPay':
					visible = false;

					var dateX20190101 = new Date(2019, 0, 1); // 01.01.2019
					if (Region_Nick.inlist(['astra']) && CureResult_Code == 2 && !Ext.isEmpty(EvnSection_disDate) && EvnSection_disDate < dateX20190101) {
						var
							EvnSectionWithKsg = null,
							stacKsgList,
							otherKsgList;

						// @task https://redmine.swan.perm.ru/issues/123628
						if (!Ext.isEmpty(EvnSection_disDate) && EvnSection_disDate >= date20180101) {
							stacKsgList = [2, 3, 4, 5, 11, 12, 16, 86, 99, 146, 147, 148, 149, 150, 151, 152, 153, 154, 155, 157, 159, 167, 168, 172, 173, 174, 198, 219, 271, 301, 314, 316, 320];
							otherKsgList = [3, 4, 6, 7, 35, 37, 38, 44, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 63, 65, 72, 73, 74, 75, 80, 81, 82, 84, 91, 92, 93, 97, 112, 113, 118];
						}
						else {
							stacKsgList = [2, 3, 4, 5, 11, 12, 16, 84, 97, 146, 154, 155, 159, 160, 161, 185, 206, 258, 287, 300, 302, 306];
							otherKsgList = [3, 4, 6, 7, 33, 35, 36, 42, 50, 51, 52, 53, 54, 56, 63, 64, 65, 66, 71, 72, 73, 75, 82, 83, 84, 88, 102, 103, 108];
						}

						var prevEvnSectionList = EvnSectionList.filter(filterPrevEvnSectionList);
						if (prevEvnSectionList.length > 0) {
							var prevEvnSection = prevEvnSectionList[prevEvnSectionList.length - 1];
							if (prevEvnSection.CureResult_Code == 3) {
								EvnSectionWithKsg = prevEvnSectionList.filter(function (EvnSection) {
									return EvnSection.indexNum == prevEvnSection.EvnSectionIndexNum;
								}).reduce(function (tmp, current) {
									return (tmp && tmp.EvnSection_KOEF > current.EvnSection_KOEF) ? tmp : current;
								}, null);
							}
						}

						if (!EvnSectionWithKsg || EvnSectionWithKsg.EvnSection_KOEF < base_form.findField('EvnSection_KOEF').getValue()) {
							EvnSectionWithKsg = {
								Mes_Code: base_form.findField('Mes_rid').getFieldValue('Mes_Code'),
								MesType_id: base_form.findField('Mes_rid').getFieldValue('MesType_id')
							};
						}

						visible = (
							EvnSectionWithKsg && (
								(LpuUnitType_SysNick == 'stac' && Number(EvnSectionWithKsg.Mes_Code).inlist(stacKsgList)) ||
								(LpuUnitType_SysNick != 'stac' && Number(EvnSectionWithKsg.Mes_Code).inlist(otherKsgList))
							)
						);
					}
					if (!field.isVisible() && visible && win.formLoaded && !win.firstTimeLoadedKSGKPGKOEF) {
						value = true;
					}
					break;
				case 'MedicalCareBudgType_id':
					visible = (
						Region_Nick.inlist(['perm', 'astra', 'ufa', 'kareliya', 'krym', 'pskov']) &&
						String(PayType_SysNick).inlist(['bud', 'fbud', 'subrf', 'mbudtrans_mbud']) && (
							(!Ext.isEmpty(LeaveType_SysNick) && !Ext.isEmpty(EvnSection_disDate) && isLast) ||
							!Ext.isEmpty(HTMedicalCareClass_Code)
						)
					);
					if (visible) {
						win.loadMedicalCareBudgType();
					}
					break;
				case 'DiagSetPhase_id':
					field.getStore().clearFilter();
					field.lastQuery = '';
					var cmpdate = new Date();
					if(!Ext.isEmpty(EvnSection_disDate)) cmpdate = EvnSection_disDate;
					else if(!Ext.isEmpty(EvnSection_setDate)) cmpdate = EvnSection_setDate;
					field.getStore().filterBy(function(rec) {
						return (!rec.get('DiagSetPhase_begDT') || rec.get('DiagSetPhase_begDT') <= cmpdate)
								&& (!rec.get('DiagSetPhase_endDT') || rec.get('DiagSetPhase_endDT') >= cmpdate);
					});
					var DSPid = field.getStore().findBy(function(rec){
							return rec.get('DiagSetPhase_id')==field.getValue();
						});
					if(DSPid<0) field.clearValue(); else field.setValue(field.getValue());
					break;
				case 'PrivilegeType_id':
					visible = getRegionNick().inlist(['astra','buryatiya','krym']);
					
					filter = function (rec) {
						return rec.get('PrivilegeType_Code').inlist([81,82,83,84]);
					};
					if(getRegionNick() == 'krym'){
						filter = function (rec) {
							return (rec.get('PrivilegeType_Code').inlist([81,82,83,84]) && rec.get('ReceptFinance_id') == 1);
						};
					}
					
					break;
				case 'PayTypeERSB_id':
					visible = getRegionNick() == 'kz' && !Ext.isEmpty(LeaveType_SysNick) && LeaveType_SysNick.inlist(['leave', 'other', 'die', 'stac', 'ksdiepp', 'ksprerv']);
					allowBlank = !visible || !(base_form.findField('PayType_id').getFieldValue('PayType_SysNick') && base_form.findField('PayType_id').getFieldValue('PayType_SysNick').inlist(['bud', 'Resp']));
					break;
				case 'Diag_id':
					field.lastQuery = '';
					break;
			}


			if (!noReset && visible === false && win.formLoaded) {
				value = null;
			}
			
			var compare = value != field.getValue();
			if (field.getName() == 'EvnSection_setDate' && field.getValue()) {
				compare = value.getTime() != field.getValue().getTime();
			}

			if (compare) {
				field.setValue(value);
				field.fireEvent('change', field, value);
				//BOB - 23.07.2018
				if ((field.getName() == 'EvnSection_SofaScalePoints') && (value == null) && win.savedData) {
					field.setValue(win.savedData.EvnSection_SofaScalePoints);
				}
				//BOB - 23.07.2018
			}

			if (allowBlank !== null) {
				field.setAllowBlank(allowBlank);
			}
			if (visible !== null) {
				field.setContainerVisible(visible);

				if (visible && field.getName() == 'DrugTherapyScheme_ids') {
					// костыль, почему то поле узкое, если ему задавалось значение в тот моемнт, пока оно было скрыто
					field.setWidth(400);
					field.setWidth(500);
				}
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
	reloadUslugaComplexField: function (UslugaComplex_id) {
		if (this.uslugaComplexLoading) {
			return false;
		}
		this.uslugaComplexLoading = true;

		var field = this.findById('EvnSectionEditForm').getForm().findField('UslugaComplex_id'),
			_this = this;

		field.lastQuery = 'This query sample that is not will never appear';
		field.getStore().removeAll();
		field.getStore().baseParams.query = '';
		field.getStore().load({
			callback: function (rec) {

				var index = field.getStore().findBy(function (rec) {
					return (rec.get('UslugaComplex_id') == UslugaComplex_id);
				});

				if (index != -1) {
					field.setValue(UslugaComplex_id);
					field.setRawValue(field.getFieldValue('UslugaComplex_Code') + '. ' + field.getFieldValue('UslugaComplex_Name'));
				} else {
					field.clearValue();
				}
				_this.uslugaComplexLoading = false;
			}
		});
	},
	loadMedicalCareBudgType: function () {
		var win = this;
		var base_form = win.findById('EvnSectionEditForm').getForm();

		var params = {
			EvnPS_setDate: Ext.util.Format.date(win.EvnPS_setDate, 'd.m.Y'),
			EvnPS_disDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'),
			LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
			PayType_SysNick: base_form.findField('PayType_id').getFieldValue('PayType_SysNick'),
			HTMedicalCareClass_id: base_form.findField('HTMedicalCareClass_id').getValue(),
			LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
			LpuSection_id: base_form.findField('LpuSection_id').getValue(),
			LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
			Diag_id: base_form.findField('Diag_id').getValue(),
			Person_id: base_form.findField('Person_id').getValue(),
		};

		var requires = [
			'EvnPS_setDate', 'PayType_SysNick', 'LpuUnitType_SysNick',
			'LpuSectionProfile_id', 'Diag_id', 'Person_id'
		];

		for (var field in params) {
			if (field.inlist(requires) && Ext.isEmpty(params[field])) {
				base_form.findField('MedicalCareBudgType_id').setValue(null);
				return;
			}
		}
		if (Ext6.isEmpty(params.HTMedicalCareClass_id) &&
			(Ext6.isEmpty(params.EvnPS_disDate) || Ext6.isEmpty(params.LeaveType_SysNick) || !win.evnSectionIsLast)
		) {
			base_form.findField('MedicalCareBudgType_id').setValue(null);
			return;
		}

		Ext.Ajax.request({
			params: params,
			url: '/?c=EvnPS&m=getMedicalCareBudgType',
			callback: function (options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.MedicalCareBudgType_id) {
					base_form.findField('MedicalCareBudgType_id').setValue(response_obj.MedicalCareBudgType_id);
				} else {
					base_form.findField('MedicalCareBudgType_id').setValue(null);
				}
			}
		});
	},
	openWindow: function (gridName, action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('sw' + gridName + 'Window').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования уже открыто');
			return false;
		}

		var grid = this.findById('MHW_' + gridName).getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function (data) {

			if (!data || !data.BaseData) {
				return false;
			}

			data.BaseData.RecordStatus_Code = 0;

			// Обновить запись в grid
			var record = grid.getStore().getById(data.BaseData[gridName + '_id']);

			if (record) {
				if (record.get('RecordStatus_Code') == 1) {
					data.BaseData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.BaseData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get(gridName + '_id')) {
					grid.getStore().removeAll();
				}

				data.BaseData[gridName + '_id'] = -swGenTempId(grid.getStore());

				grid.getStore().loadData([data.BaseData], true);
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
			params.onHide = function () {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		getWnd('sw' + gridName + 'Window').show(params);

	},
	filterLpuSectionBedProfileLink: function (LpuSection_id, fieldName) {
		var that = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var lpuSection = base_form.findField('LpuSection_id');
		var lpuSectionBedProfileLink = base_form.findField('LpuSectionBedProfileLink_fedid');
		var LpuSectionProfileCombo = that.findById('EvnSectionEditForm').getForm().findField('LpuSectionProfile_id');

		var params = {
			LpuSection_id: LpuSection_id,
			EvnSection_id: base_form.findField('EvnSection_id').getValue(),
			LpuSectionWard_id: base_form.findField('LpuSectionWard_id').getValue(),
			begDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
			endDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y')
		};

		sw.Promed.LpuSectionBedProfile.getLpuSectionBedProfilesLinkByLpuSection({//--
			params: params,
			callback: function (response_obj) {
				var LpuSectionBedProfilesLink = [];
				response_obj.forEach(function (el) {
					LpuSectionBedProfilesLink.push(parseInt(el.LpuSectionBedProfileLink_id))
				});

				var LpuSectionBedProfileLinkCombo = that.findById('EvnSectionEditForm').getForm().findField(fieldName);
				var LpuSectionBedProfileLink_id = LpuSectionBedProfileLinkCombo.getValue();
				LpuSectionBedProfileLinkCombo.lastQuery = '';
				LpuSectionBedProfileLinkCombo.getStore().filterBy(function (rec) {
					return rec.get(LpuSectionBedProfileLinkCombo.valueField).inlist(LpuSectionBedProfilesLink);
				});

				LpuSectionBedProfileLinkCombo.setBaseFilter(function (rec) {
					return rec.get(LpuSectionBedProfileLinkCombo.valueField).inlist(LpuSectionBedProfilesLink);
				});
				var index = LpuSectionBedProfileLinkCombo.getStore().findBy(function (rec) {
					return (rec.get(LpuSectionBedProfileLinkCombo.valueField) == LpuSectionBedProfileLink_id);
				});

				if ( index >= 0 ) {
					LpuSectionBedProfileLinkCombo.setValue(LpuSectionBedProfileLink_id);
				}
				else if (LpuSectionBedProfileLinkCombo.getStore().getCount() > 0 && that.action == 'add') {// Автозаполнение будет работать только для добавления
					LpuSectionBedProfileLinkCombo.setValue(LpuSectionBedProfileLinkCombo.getStore().getAt(0).get(LpuSectionBedProfileLinkCombo.valueField));
				}
				else {
					LpuSectionBedProfileLinkCombo.clearValue();
				}

				LpuSectionBedProfileLinkCombo.fireEvent('change', LpuSectionBedProfileLinkCombo, LpuSectionBedProfileLinkCombo.getValue());

			}
		});
	},
	filterLpuSectionBedProfilesByLpuSection: function (LpuSection_id, fieldName) {/*только для kareliya, astra*/

		this.filterLpuSectionBedProfileLink(LpuSection_id, fieldName);
		return;
		//фильтрую профиль коек по отделению - оставляю среди них только профили коек подотделений
		var that = this;
		sw.Promed.LpuSectionBedProfile.getLpuSectionBedProfilesByLpuSection({
			LpuSection_id: LpuSection_id,
			callback: function (response_obj) {
				//парсю ответ сую все профили в одномерный массив
				var LpuSectionBedProfiles = [];
				response_obj.forEach(function (el) {
					LpuSectionBedProfiles.push(parseInt(el.LpuSectionBedProfile_id))
				});
				//накладываю фильтр на профили коек
				var LpuSectionBedProfileCombo = that.findById('EvnSectionEditForm').getForm().findField(fieldName);
				LpuSectionBedProfileCombo.lastQuery = '';
				LpuSectionBedProfileCombo.getStore().filterBy(function (el) {
					return 0 <= LpuSectionBedProfiles.indexOf(el.data.LpuSectionBedProfile_id);
				});

				LpuSectionBedProfileCombo.setBaseFilter(function (rec) {
					return (0 <= LpuSectionBedProfiles.indexOf(rec.get('LpuSectionBedProfile_id')));
				});

				//если значение которые было установлено отфильтровалось, очищаю комбик
				if (Ext.isEmpty(LpuSectionBedProfileCombo.getStore().getById(LpuSectionBedProfileCombo.getValue()))) {
					LpuSectionBedProfileCombo.clearValue();
				}
			}
		});
	},
	filterLpuSectionBedProfilesByLpuSectionProfile: function (LpuSectionProfile_id, fieldName) {/*только для kaluga*/

		this.filterLpuSectionBedProfileLink(LpuSection_id, fieldName);
		//фильтрую профиль коек по профилю с помощью v_LpuSectionBedProfileLink
		var that = this;
		sw.Promed.LpuSectionBedProfile.getLpuSectionBedProfilesByLpuSectionProfile({
			LpuSectionProfile_id: LpuSectionProfile_id,
			callback: function (response_obj) {
				//парсю ответ сую все профили в одномерный массив
				var LpuSectionBedProfiles = [];
				response_obj.forEach(function (el) {
					LpuSectionBedProfiles.push(parseInt(el.LpuSectionBedProfile_id))
				});
				//накладываю фильтр на профили коек
				var LpuSectionBedProfileCombo = that.findById('EvnSectionEditForm').getForm().findField(fieldName);
				LpuSectionBedProfileCombo.lastQuery = '';
				LpuSectionBedProfileCombo.getStore().filterBy(function (el) {
					return 0 <= LpuSectionBedProfiles.indexOf(el.data.LpuSectionBedProfile_id);
				});

				LpuSectionBedProfileCombo.setBaseFilter(function (rec) {
					return (0 <= LpuSectionBedProfiles.indexOf(rec.get('LpuSectionBedProfile_id')));
				});

				//если значение которые было установлено отфильтровалось, очищаю комбик
				if (Ext.isEmpty(LpuSectionBedProfileCombo.getStore().getById(LpuSectionBedProfileCombo.getValue()))) {
					LpuSectionBedProfileCombo.clearValue();
				}
			}
		});
	},
	openPersonBirthTraumaEditWindow: function (action, type) {
		if (!type || !action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swPersonBirthTraumaEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования уже открыто');
			return false;
		}
		var grid = this.findById('ESEW_PersonBirthTraumaGrid' + type).getGrid();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('PersonBirthTrauma_id'))) {
				return false;
			}
		}

		var params = new Object();
		params.action = action;
		params.callback = function (data) {
			if (typeof data != 'object') {
				return false;
			}
			data.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function (rec) {
				return rec.get('PersonBirthTrauma_id') == data.PersonBirthTrauma_id;
			});
			var record = grid.getStore().getAt(index);

			if (typeof record == 'object') {
				if (record.get('RecordStatus_Code') == 1) {
					data.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});

				for (var i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data[grid_fields[i]]);
				}

				record.commit();
			} else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PersonBirthTrauma_id')) {
					grid.getStore().removeAll();
				}
				data.PersonBirthTrauma_id = -swGenTempId(grid.getStore());

				var newRecord = new Ext.data.Record(data);
				grid.getStore().loadRecords({records: [newRecord]}, {add: true}, true);
			}
		}.createDelegate(this);
		params.formParams = new Object();

		params.BirthTraumaType_id = type;

		params.Person_BirthDay = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		if (action != 'add') {
			if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonBirthTrauma_id')) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();
			//params.PersonBirthTrauma_id=selected_record.get('PersonBirthTrauma_id');
			params.formParams = record.data;
			params.onHide = function () {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}
		params.formParams.BirthTraumaType_id = type;
		params.formParams.Person_BirthDay = this.BirthDay;
		getWnd('swPersonBirthTraumaEditWindow').show(params);
	},
	deletePersonBirthTrauma: function (type) {
		var grid = this.findById('ESEW_PersonBirthTraumaGrid' + type).getGrid();
		if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected()) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected()

		switch (Number(record.get('RecordStatus_Code'))) {
			case 0:
				grid.getStore().remove(record);
				break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function (rec) {
					if (Number(rec.get('RecordStatus_Code')) == 3) {
						return false;
					}
					else {
						return true;
					}
				});
				break;
		}

		if (grid.getStore().getCount() == 0) {
			//LoadEmptyRow(grid);
		} else {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	deleteApgarRate: function () {
		var grid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();
		if (!grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected()) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected()

		switch (Number(record.get('RecordStatus_Code'))) {
			case 0:
				grid.getStore().remove(record);
				break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function (rec) {
					if (Number(rec.get('RecordStatus_Code')) == 3) {
						return false;
					}
					else {
						return true;
					}
				});
				break;
		}

		if (grid.getStore().getCount() == 0) {
			//LoadEmptyRow(grid);
		} else {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}

	},
	addNewbornApgarRate: function () {
		var win = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();
		var data = {
			NewbornApgarRate_id: -swGenTempId(grid.getStore()),
			NewbornApgarRate_Time: 0,
			RecordStatus_Code: 0
		};
		grid.getStore().loadData([data], true);

	},
	openEvnObservNewBornEditWindow: function (action) {
		if (!action || !action.inlist(['add', 'edit', 'view'])) {
			return false;
		}
		var wnd = this;
		var grid_panel = this.findById('ESEW_EvnObservNewBornGrid');
		var grid = grid_panel.getGrid();
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var person_info = this.findById('ESecEF_PersonInformationFrame');

		var EvnSection_id = base_form.findField('EvnSection_id').getValue();
		var PersonNewBorn_id = base_form.findField('PersonNewBorn_id').getValue();

		if (Ext.isEmpty(EvnSection_id) || EvnSection_id == 0 || Ext.isEmpty(PersonNewBorn_id) || PersonNewBorn_id == 0) {
			this.doSave({
				isPersonNewBorn: 1, silent: function () {
					wnd.openEvnObservNewBornEditWindow(action)
				}
			});
			return false;
		}

		var params = {
			action: action,
			disableChangeTime: false,
			callback: function () {
/*
				Person_id: base_form.findField('Person_id').getValue(), 
					EvnPS_id: parentWin.EvnPS_id,
					EvnSection_id: base_form.findField('EvnSection_id').getValue()				
*/


				// Загрузка данных с сервера в форму и гриды
				Ext.Ajax.request({
					callback: function (options, success, response) {
						loadMask.hide();
						var base_form = this.findById('EvnSectionEditForm').getForm();
						// Загружаем списки измерений массы и длины


						if (success) {
							this.findById('ESecEF_PersonChildForm').isLoaded = true;
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.length > 0) {
								response_obj = response_obj[0];
								this.NewBorn_Weight = (response_obj.PersonNewBorn_Weight > 0) ? response_obj.PersonNewBorn_Weight : 0;
								if (response_obj.ChildTermType_id) {
									base_form.findField('ChildTermType_id').setValue(response_obj.ChildTermType_id);
								} else {
									if (this.childTermType_id) {
										base_form.findField('ChildTermType_id').setValue(this.childTermType_id);
									}
								}
								base_form.findField('PersonNewBorn_id').setValue(response_obj.PersonNewBorn_id);
								this.tabPanel.setActiveTab('tab_ESEWCommon')
								var grid1 = this.findById('ESEW_PersonBirthTraumaGrid1').getGrid();
								var grid2 = this.findById('ESEW_PersonBirthTraumaGrid2').getGrid();
								var grid3 = this.findById('ESEW_PersonBirthTraumaGrid3').getGrid();
								var grid4 = this.findById('ESEW_PersonBirthTraumaGrid4').getGrid();
								var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();

								grid1.getStore().baseParams.BirthTraumaType_id = 1;
								grid2.getStore().baseParams.BirthTraumaType_id = 2;
								grid3.getStore().baseParams.BirthTraumaType_id = 3;
								grid4.getStore().baseParams.BirthTraumaType_id = 4;
								apgarGrid.getStore().load({params: {PersonNewBorn_id: base_form.findField('PersonNewBorn_id').getValue()}});
								base_form.findField('FeedingType_id').setValue(response_obj.FeedingType_id);
								base_form.findField('PersonNewBorn_BCGNum').setRawValue(response_obj.PersonNewBorn_BCGNum);
								base_form.findField('PersonNewBorn_BCGSer').setRawValue(response_obj.PersonNewBorn_BCGSer);
								base_form.findField('PersonNewBorn_BCGDate').setValue(response_obj.PersonNewBorn_BCGDate);
								base_form.findField('BirthSpecStac_id').setValue(response_obj.BirthSpecStac_id);
								base_form.findField('PersonNewBorn_Head').setValue(response_obj.PersonNewBorn_Head);
								base_form.findField('PersonNewBorn_Breast').setValue(response_obj.PersonNewBorn_Breast);
								base_form.findField('PersonNewBorn_Weight').setValue(response_obj.PersonNewBorn_Weight);
								base_form.findField('PersonNewBorn_Height').setValue(response_obj.PersonNewBorn_Height);
								base_form.findField('PersonNewBorn_HepatitNum').setRawValue(response_obj.PersonNewBorn_HepatitNum);
								base_form.findField('PersonNewBorn_HepatitSer').setRawValue(response_obj.PersonNewBorn_HepatitSer);
								base_form.findField('PersonNewBorn_HepatitDate').setValue(response_obj.PersonNewBorn_HepatitDate);
								base_form.findField('PersonNewBorn_id').setValue(response_obj.PersonNewBorn_id);
								base_form.findField('PersonNewBorn_IsAidsMother').setValue(response_obj.PersonNewBorn_IsAidsMother);
								base_form.findField('PersonNewBorn_IsHepatit').setValue(response_obj.PersonNewBorn_IsHepatit);
								base_form.findField('PersonNewborn_BloodBili').setValue(response_obj.PersonNewborn_BloodBili);
								base_form.findField('PersonNewborn_BloodHemoglo').setValue(response_obj.PersonNewborn_BloodHemoglo);
								base_form.findField('PersonNewborn_BloodEryth').setValue(response_obj.PersonNewborn_BloodEryth);
								base_form.findField('PersonNewborn_BloodHemato').setValue(response_obj.PersonNewborn_BloodHemato);
								base_form.findField('NewBornWardType_id').setValue(response_obj.NewBornWardType_id);
								base_form.findField('PersonNewBorn_IsBleeding').setValue(response_obj.PersonNewBorn_IsBleeding);
								base_form.findField('PersonNewBorn_IsAudio').setValue(response_obj.PersonNewBorn_IsAudio);
								base_form.findField('PersonNewBorn_IsNeonatal').setValue(response_obj.PersonNewBorn_IsNeonatal);
								base_form.findField('PersonNewBorn_IsBCG').setValue(response_obj.PersonNewBorn_IsBCG);
								base_form.findField('PersonNewBorn_IsBreath').setValue(response_obj.PersonNewBorn_IsBreath);
								base_form.findField('PersonNewBorn_IsHeart').setValue(response_obj.PersonNewBorn_IsHeart);
								base_form.findField('PersonNewBorn_IsPulsation').setValue(response_obj.PersonNewBorn_IsPulsation);
								base_form.findField('PersonNewBorn_IsMuscle').setValue(response_obj.PersonNewBorn_IsMuscle);

								if (getRegionNick() == 'ufa') {
									base_form.findField('RefuseType_pid').setValue(response_obj.RefuseType_pid);
									base_form.findField('RefuseType_aid').setValue(response_obj.RefuseType_aid);
									base_form.findField('RefuseType_bid').setValue(response_obj.RefuseType_bid);
									base_form.findField('RefuseType_gid').setValue(response_obj.RefuseType_gid);
								}

								var Person_BirthDay = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
								base_form.findField('PersonNewBorn_BCGDate').setMinValue(Person_BirthDay);
								base_form.findField('PersonNewBorn_HepatitDate').setMinValue(Person_BirthDay);
								if (response_obj.PersonNewBorn_CountChild) {
									base_form.findField('PersonNewBorn_CountChild').setValue(response_obj.PersonNewBorn_CountChild);
								} else {
									if (this.PersonNewBorn_CountChild) {
										base_form.findField('PersonNewBorn_CountChild').setValue(this.PersonNewBorn_CountChild);
									}
								}
								var PersonNewBorn_IsAidsMother;
								if (response_obj.PersonNewBorn_IsAidsMother) {
									PersonNewBorn_IsAidsMother = response_obj.PersonNewBorn_IsAidsMother;
									setTimeout(function () {
										base_form.findField('PersonNewBorn_IsAidsMother').setValue(PersonNewBorn_IsAidsMother);
									}, 1500);
								} else {
									if (this.PersonNewBorn_IsAidsMother) {
										PersonNewBorn_IsAidsMother = this.PersonNewBorn_IsAidsMother;
										setTimeout(function () {
											base_form.findField('PersonNewBorn_IsAidsMother').setValue(PersonNewBorn_IsAidsMother);
										}, 1500);
									}
								}
								base_form.findField('PersonNewBorn_CountChild').setValue(response_obj.PersonNewBorn_CountChild);
								base_form.findField('ChildPositionType_id').setValue(response_obj.ChildPositionType_id);
								base_form.findField('PersonNewBorn_IsRejection').setValue(response_obj.PersonNewBorn_IsRejection);

								if (getRegionNick().inlist([ 'ufa' ])){
									if (response_obj.ChildTermType_id_IsEdit == 1){
										base_form.findField('ChildTermType_id').setDisabled(true);
									}else{
										base_form.findField('ChildTermType_id').setDisabled(false);
									}
									if (response_obj.FeedingType_id_IsEdit == 1){
										base_form.findField('FeedingType_id').setDisabled(true);
									}else{
										base_form.findField('FeedingType_id').setDisabled(false);
									}
									if (response_obj.NewBornWardType_id_IsEdit == 1){
										base_form.findField('NewBornWardType_id').setDisabled(true);
									}else{
										base_form.findField('NewBornWardType_id').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_BCGNum_IsEdit == 1){
										base_form.findField('PersonNewBorn_BCGNum').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_BCGNum').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_BCGSer_IsEdit == 1){
										base_form.findField('PersonNewBorn_BCGSer').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_BCGSer').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_BCGDate_IsEdit == 1){
										base_form.findField('PersonNewBorn_BCGDate').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_BCGDate').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_HepatitNum_IsEdit == 1){
										base_form.findField('PersonNewBorn_HepatitNum').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_HepatitNum').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_HepatitSer_IsEdit == 1){
										base_form.findField('PersonNewBorn_HepatitSer').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_HepatitSer').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_HepatitDate_IsEdit == 1){
										base_form.findField('PersonNewBorn_HepatitDate').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_HepatitDate').setDisabled(false);
									}
									if (response_obj.FeedingType_id_IsEdit == 1){
										base_form.findField('FeedingType_id').setDisabled(true);
									}else{
										base_form.findField('ChildTermType_id').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsAidsMother_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsAidsMother').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsAidsMother').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsHepatit_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsHepatit').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsHepatit').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsBCG_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsBCG').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsBCG').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsBreath_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsBreath').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsBreath').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsHeart_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsHeart').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsHeart').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsPulsation_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsPulsation').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsPulsation').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsMuscle_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsMuscle').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsMuscle').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsBleeding_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsBleeding').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsBleeding').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsNeonatal_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsNeonatal').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsNeonatal').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsAudio_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsAudio').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsAudio').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_CountChild_IsEdit == 1){
										base_form.findField('PersonNewBorn_CountChild').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_CountChild').setDisabled(false);
									}
									if (response_obj.ChildPositionType_id_IsEdit == 1){
										base_form.findField('ChildPositionType_id').setDisabled(true);
									}else{
										base_form.findField('ChildPositionType_id').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_IsRejection_IsEdit == 1){
										base_form.findField('PersonNewBorn_IsRejection').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_IsRejection').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_Head_IsEdit == 1){
										base_form.findField('PersonNewBorn_Head').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_Head').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_Weight_IsEdit == 1){
										base_form.findField('PersonNewBorn_Weight').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_Weight').setDisabled(false);
									}
									if (response_obj.PersonNewBorn_Breast_IsEdit == 1){
										base_form.findField('PersonNewBorn_Breast').setDisabled(true);
									}else{
										base_form.findField('PersonNewBorn_Breast').setDisabled(false);
									}
									if (response_obj.PersonNewborn_BloodBili_IsEdit == 1){
										base_form.findField('PersonNewborn_BloodBili').setDisabled(true);
									}else{
										base_form.findField('PersonNewborn_BloodBili').setDisabled(false);
									}
									if (response_obj.PersonNewborn_BloodHemoglo_IsEdit == 1){
										base_form.findField('PersonNewborn_BloodHemoglo').setDisabled(true);
									}else{
										base_form.findField('PersonNewborn_BloodHemoglo').setDisabled(false);
									}
									if (response_obj.PersonNewborn_BloodEryth_IsEdit == 1){
										base_form.findField('PersonNewborn_BloodEryth').setDisabled(true);
									}else{
										base_form.findField('PersonNewborn_BloodEryth').setDisabled(false);
									}
									if (response_obj.PersonNewborn_BloodHemato_IsEdit == 1){
										base_form.findField('PersonNewborn_BloodHemato').setDisabled(true);
									}else{
										base_form.findField('PersonNewborn_BloodHemato').setDisabled(false);
									}

									var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid');
									apgarGrid.setActionDisabled('action_delete', (!Ext.isEmpty(response_obj.PersonNewborn_Id_Last)));
									apgarGrid.setActionDisabled('action_add', (!Ext.isEmpty(response_obj.PersonNewborn_Id_Last)));
									apgarGrid.setReadOnly(!Ext.isEmpty(response_obj.PersonNewborn_Id_Last));

								}
							}
							else {
								if (this.childTermType_id) {
									base_form.findField('ChildTermType_id').setValue(this.childTermType_id);
								}
								if (this.PersonNewBorn_CountChild) {
									base_form.findField('PersonNewBorn_CountChild').setValue(this.PersonNewBorn_CountChild);
								}
								if (this.PersonNewBorn_IsAidsMother) {
									PersonNewBorn_IsAidsMother = this.PersonNewBorn_IsAidsMother;
									setTimeout(function () {
										base_form.findField('PersonNewBorn_IsAidsMother').setValue(PersonNewBorn_IsAidsMother);
									}, 1500);
									base_form.findField('PersonNewBorn_IsAidsMother').setValue(this.PersonNewBorn_IsAidsMother);
								}

								var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();
								var values = [
									{
										NewbornApgarRate_id: -swGenTempId(apgarGrid.getStore()),
										NewbornApgarRate_Time: 1,
										RecordStatus_Code: 0
									},
									{
										NewbornApgarRate_id: -swGenTempId(apgarGrid.getStore()),
										NewbornApgarRate_Time: 5,
										RecordStatus_Code: 0
									},
									{
										NewbornApgarRate_id: -swGenTempId(apgarGrid.getStore()),
										NewbornApgarRate_Time: 10,
										RecordStatus_Code: 0
									},
									{
										NewbornApgarRate_id: -swGenTempId(apgarGrid.getStore()),
										NewbornApgarRate_Time: 15,
										RecordStatus_Code: 0
									}
								];
								apgarGrid.getStore().removeAll();
								apgarGrid.getStore().loadData(values, true);

								if (response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0) {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
								}
							}
						}
						else {
							sw.swMsg.alert('Ошибка', 'При загрузке сведений о новорожденном возникли ошибки');
						}
					}.createDelegate(this),
					params: {
						Person_id: base_form.findField('Person_id').getValue(),
						EvnPS_id: parentWin.EvnPS_id,
						EvnSection_id: base_form.findField('EvnSection_id').getValue()
					},
					url: '/?c=PersonNewBorn&m=loadPersonNewBornData'
				});				
				
				
				/*
				grid.getStore().load({
					params: {PersonNewBorn_id: PersonNewBorn_id}
				});
				
				*/
			}
		};
		if (action == 'add') {
			params.formParams = {
				EvnObserv_pid: base_form.findField('EvnSection_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				Person_Birthday: Ext.util.Format.date(person_info.getFieldValue('Person_Birthday'), 'd.m.Y'),
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				PersonNewBorn_id: PersonNewBorn_id
			};
		} else {
			var record = grid.getSelectionModel().getSelected();

			if (!record || Ext.isEmpty(record.get('EvnObserv_id'))) {
				return false;
			}

			params.formParams = {
				EvnObserv_id: record.get('EvnObserv_id'),
				Person_Birthday: Ext.util.Format.date(person_info.getFieldValue('Person_Birthday'), 'd.m.Y')
			};
		}

		getWnd('swEvnObservEditWindow').show(params);
		return true;
	},
	openEvnNeonatalSurveyEditWindow: function (action) {
		if (!action || !action.inlist(['add', 'edit', 'view'])) {
			return false;
		}
		var wnd = this;

		var grid_panel = this.findById('ESEW_EvnNeonatalSurveyGrid');
		var grid = grid_panel.getGrid();
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var person_info = this.findById('ESecEF_PersonInformationFrame');

		if (getWnd('swEvnNeonatalSurveyEditWindow').isVisible() && getWnd('swEvnNeonatalSurveyEditWindow').changedDatas) {
			Ext.Msg.alert(langs('Сообщение'), langs('Окно Наблюдение состояния младенца уже открыто<br> и в нём имеются несохранённые изменния!'));
			return false;
		}

		var pers_data = {
			Person_id: base_form.findField('Person_id').getValue(),
			PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue(),
			Person_Surname: person_info.getFieldValue('Person_Surname'),
			Person_Firname: person_info.getFieldValue('Person_Firname'),
			Person_Secname: person_info.getFieldValue('Person_Secname'),
			Person_Birthday: person_info.getFieldValue('Person_Birthday'),
			Sex_id: person_info.getFieldValue('Sex_Code')
		};

		var params = {
			ENSEW_title: langs('Наблюдение состояния младенца'),
			action: action,
			fromObject: this,
			pers_data: pers_data,
			EvnNeonatalSurvey_pid: base_form.findField('EvnSection_pid').getValue(),
			EvnNeonatalSurvey_rid: base_form.findField('EvnSection_pid').getValue(),
			EvnNeonatalSurvey_id: action == 'add' ? null : grid.getSelectionModel().getSelected().get('EvnNeonatalSurvey_id'),
			ParentObject: 'EvnPersonNewBorn',
			userMedStaffFact: getGlobalOptions().CurMedStaffFact_id || null,
			ARMType: 'stas_pol',
			LpuSection_id: base_form.findField('LpuSection_id').getValue(),
			Lpu_id: getGlobalOptions().lpu_id,
			FirstConditionLoad: false
		};

		getWnd('swEvnNeonatalSurveyEditWindow').show(params);

		return true;
	},
	deleteEvnObservNewBorn: function () {
		var grid_panel = this.findById('ESEW_EvnObservNewBornGrid');
		var grid = grid_panel.getGrid();
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('EvnObserv_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {
						EvnObserv_id: record.get('EvnObserv_id'),
						PersonNewBorn_id: base_form.findField('PersonNewBorn_id').getValue()
					};

					Ext.Ajax.request({
						callback: function (opt, scs, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (!response_obj.Error_Msg) {
								grid_panel.getAction('action_refresh').execute();
							}
						}.createDelegate(this),
						params: params,
						url: '/?c=EvnObserv&m=deleteEvnObserv'
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Вы хотите удалить запись?'),
			title: langs('Подтверждение')
		});
	},
	getFinanceSource: function() {
		var win = this,
			base_form = this.findById('EvnSectionEditForm').getForm();

		//if (this.IsLoading) return false;
		//if (this.IsProfLoading) return false;

		if (getRegionNick() != 'kz') return false;

		if (this.action.inlist(['view'])) return false;

		if (this.EvnPS_IsWithoutDirection == 2) return false;

		var params = {
			DirType_id: 1,
			EvnDirection_setDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
			Lpu_did: base_form.findField('LpuSection_id').getFieldValue('Lpu_id') || getGlobalOptions().lpu_id,
			LpuUnitType_id: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_id'),
			EvnPS_id: base_form.findField('EvnSection_pid').getValue(),
			isStac: 2,
			Person_id: base_form.findField('Person_id').getValue(),
			Diag_cid: base_form.findField('Diag_cid').getValue(),
			Diag_id: base_form.findField('Diag_id').getValue()
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
	initComponent: function () {
		this.addMaxDateDays = 0;
		this.diagPanel = new sw.Promed.swDiagPanel({
			labelWidth: 180,
			bodyStyle: 'padding: 0px;',
			phaseDescrName: 'EvnSection_PhaseDescr',
			diagSetPhaseName: 'DiagSetPhase_id',
			personId: 0,
			showHSN: true,
			diagField: {
				checkAccessRights: true,
				MKB: null,
				allowBlank: false,
				fieldLabel: 'Основной диагноз',
				hiddenName: 'Diag_id',
				id: this.id + '_DiagCombo',
				onChange: function (combo, value) {

					this.loadMesCombo();
					this.savedMesTariff_id = null; // при смене диагноза, сохранённый коэфф уже не учитываем, выставляем КСГ автоматически
					this.loadKSGKPGKOEF();
					this.checkMesOldUslugaComplexFields();
					this.loadMes2Combo(-1, true);
					this.recountKoikoDni();
					this.changeDiag(combo, value);
					if (this.showHTMedicalCareClass) {
						//this.findById('EvnSectionEditForm').getForm().findField('HTMedicalCareClass_id').clearValue();
						this.loadHTMedicalCareClassCombo();
					}
					this.recalcBirthSpecStacDefaults();
					this.setDiagEidAllowBlank();
					this.showSTField();
					this.refreshFieldsVisibility(['DeseaseBegTimeType_id', 'DeseaseType_id', 'TumorStage_id', 'PainIntensity_id', 'EvnSection_BarthelIdx', 'MedicalCareBudgType_id', 'MesDop_ids']);
					//this.getFinanceSource();
				}.createDelegate(this),
				getCode: function(){
					var record = this.getStore().getById(this.getValue());
					return record != null ? record.get('Diag_Code'):'';
				},
				tabIndex: this.tabIndex + 17,
				enableNativeTabSupport: false,
				width: 500,
				xtype: 'swdiagcombo'
			},
			diagPhase: {
				xtype: 'swdiagsetphasecombo',
				fieldLabel: 'Состояние пациента при поступлении',
				hiddenName: 'DiagSetPhase_id',
				allowBlank: getRegionNick()=='kz',
				tabIndex: this.tabindex + 17,
				width: 500,
				editable: false
			},
			copyBtn: {
				text: '=',
				tooltip: 'Скопировать из предыдущего отделения',
				handler: function () {
					var base_form = this.findById('EvnSectionEditForm').getForm();
					if (!Ext.isEmpty(this.DiagPred_id)) {
						that.setDiagPred(this.DiagPred_id);
					} else {
						// не всегда приходит педыдущий диагноз в параметрах формы, поэтому получаем с сервера предыдущий диагноз
						Ext.Ajax.request({
							url: '/?c=EvnSection&m=getDiagPred',
							params: {
								EvnSection_id: base_form.findField('EvnSection_id').getValue(),
								EvnSection_pid: base_form.findField('EvnSection_pid').getValue()
							},
							callback: function (options, success, response) {
								if (success) {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (!Ext.isEmpty(response_obj.DiagPred_id)) {
										that.setDiagPred(response_obj.DiagPred_id);
									}
								}
							}
						});
					}
				}.createDelegate(this),
				id: this.id + '_copyBtn',
				xtype: 'button'
			}
		});

		if (getGlobalOptions().region) {
			if (getGlobalOptions().region.nick == 'ufa') {
				this.addMaxDateDays = 7;
			} else if (getGlobalOptions().region.nick == 'astra') {
				this.addMaxDateDays = 3;
			}
		}
		var parentWin = this;
		var win = this;

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
			toolbar: true,
			uniqueId: true
		});

		this.RepositoryObservGrid = new sw.Promed.ViewFrame({
			style: 'margin-bottom: 0.5em;',
			actions: [
				{name: 'action_add', handler: function() { win.openRepositoryObservEditWindow('add'); }},
				{name: 'action_edit', handler: function() { win.openRepositoryObservEditWindow('edit'); }},
				{name: 'action_view', handler: function() { win.openRepositoryObservEditWindow('view'); }},
				{name: 'action_delete', handler: function() { win.deleteRepositoryObserv(); }},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: getRegionNick() != 'msk', handler: function() { win.printRepositoryObserv(); }}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RepositoryObserv&m=loadList',
			height: 200,
			paging: false,
			stringfields: [
				{name: 'RepositoryObserv_id', type: 'int', header: 'ID', key: true},
				{name: 'RepositoryObserv_setDT', type: 'datetime', header: 'Дата и время наблюдения', width: 200},
				{name: 'MedPersonal_FIO', type: 'string', header: 'Врач', id: 'autoexpand'}
			],
			title: (getRegionNick() == 'krym' ? '10' : '9') + '. Наблюдения за пациентом с пневмонией, подозрением на COVID-19 и COVID-19',
			toolbar: true,
			uniqueId: true
		});

		this.tabPanel = new Ext.TabPanel({
			region: 'south',
			id: 'ESEW-tabs-panel',
			//autoScroll: true,

			border: false,
			activeTab: 0,
			//resizeTabs: true,
			//enableTabScroll: true,
			//autoWidth: true,
			//tabWidth: 'auto',
			layoutOnTabChange: true,
			listeners: {
				'tabchange': function (tab, panel) {
					var base_form = parentWin.FormPanel.getForm();
					var Person_id = base_form.findField('Person_id').getValue();
					var PersonNewBorn_id = base_form.findField('PersonNewBorn_id').getValue();

					if (!parentWin.isTraumaTabGridLoaded && panel.id == 'tab_ESEWTrauma') {
						parentWin.isTraumaTabGridLoaded = true;

						var grid1 = parentWin.findById('ESEW_PersonBirthTraumaGrid1').getGrid();
						var grid2 = parentWin.findById('ESEW_PersonBirthTraumaGrid2').getGrid();
						var grid3 = parentWin.findById('ESEW_PersonBirthTraumaGrid3').getGrid();
						var grid4 = parentWin.findById('ESEW_PersonBirthTraumaGrid4').getGrid();

						if (!Ext.isEmpty(PersonNewBorn_id) && PersonNewBorn_id > 0) {
							grid1.getStore().baseParams.BirthTraumaType_id = 1;
							grid2.getStore().baseParams.BirthTraumaType_id = 2;
							grid3.getStore().baseParams.BirthTraumaType_id = 3;
							grid4.getStore().baseParams.BirthTraumaType_id = 4;

							grid1.getStore().load({params: {PersonNewBorn_id: PersonNewBorn_id}});
							grid2.getStore().load({params: {PersonNewBorn_id: PersonNewBorn_id}});
							grid3.getStore().load({params: {PersonNewBorn_id: PersonNewBorn_id}});
							grid4.getStore().load({params: {PersonNewBorn_id: PersonNewBorn_id}});
						}
					}
					if (!parentWin.isObservTabGridLoaded && panel.id == 'tab_ESEWObserv') {
						parentWin.isObservTabGridLoaded = true;

						var grid_observ = parentWin.findById('ESEW_EvnObservNewBornGrid').getGrid();

						grid_observ.getStore().load({
							params: {
								Person_id: Person_id,
								PersonNewBorn_id: (PersonNewBorn_id > 0) ? PersonNewBorn_id : null
							}
						});
					}
					if (!parentWin.isNeonatalTabGridLoaded && panel.id == 'tab_ESEWEvnNeonatalSurvey') {
						parentWin.isNeonatalTabGridLoaded = true;

						var panel_neonatal = parentWin.findById("ESEW_EvnNeonatalSurveyGrid");
						if (panel_neonatal) {
							panel_neonatal.addActions({
								name: 'action_all',
								hidden: false,
								iconCls: 'archive16',
								text: 'Все наблюдения',
								tooltip: 'Все наблюдения у пациента',
								handler: function () {
									var grid_neonatal = panel_neonatal.getGrid();
									if (this.getText() == 'Все наблюдения'){
										this.setText('Наблюдения по КВС');
										grid_neonatal.getStore().load({
											params: {
												Person_id: base_form.findField('Person_id').getValue(),
												EvnPS_id: ''
											}
										});
									}else if (this.getText() == 'Наблюдения по КВС'){
										this.setText('Все наблюдения');
										grid_neonatal.getStore().load({
											params: {
												EvnPS_id: base_form.findField('EvnSection_pid').getValue(),
												Person_id: ''
											}
										});
									}
								}
							});

							var grid_neonatal = panel_neonatal.getGrid();

							grid_neonatal.getStore().load({
								params: {
									EvnPS_id: base_form.findField('EvnSection_pid').getValue(),
									Person_id: ''
								}
							});
						}
					}
				}
			},
			items: [
				{
					title: 'Общая информация',
					id: 'tab_ESEWCommon',
					iconCls: 'info16',
					border: false,
					items: [{
						layout: 'form',
						bodyStyle: 'padding: 5px 5px 0',
						labelAlign: 'right',
						border: false,
						labelWidth: 190,
						items: [
							{
								comboSubject: 'ChildTermType',
								fieldLabel: 'Доношенность',
								hiddenName: 'ChildTermType_id',
								width: 300,
								xtype: 'swcommonsprcombo'

							}, {
								fieldLabel: 'Предлежание',
								comboSubject: 'ChildPositionType',
								hiddenName: 'ChildPositionType_id',
								name: 'ChildPositionType_id',
								width: 100,
								xtype: 'swcommonsprcombo'
							},
							{
								comboSubject: 'FeedingType',
								fieldLabel: 'Вид вскармливания',
								hiddenName: 'FeedingType_id',
								width: 300,
								xtype: 'swcommonsprcombo',
								listeners: {
									keydown: function () {
										this.keyPressedOnThisControll = true;
									},
									keypress: function (inp, e) {
										if (!this.keyPressedOnThisControll) {
											return;
										}

										this.keyPressedOnThisControll = false;
									}
								}
							}, {
								fieldLabel: 'Который по счету',
								allowNegative: false,
								allowDecimals: false,
								hiddenName: 'PersonNewBorn_CountChild',
								name: 'PersonNewBorn_CountChild',
								width: 100,
								xtype: 'numberfield'
							}, {
								comboSubject: 'YesNo',
								fieldLabel: 'ВИЧ-инфекция у матери',
								hiddenName: 'PersonNewBorn_IsAidsMother',
								width: 100,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'YesNo',
								fieldLabel: 'Отказ от ребенка',
								hiddenName: 'PersonNewBorn_IsRejection',
								width: 100,
								xtype: 'swcommonsprcombo',
								listeners: {
									keydown: function (inp, e) {
										if (e.getKey() == Ext.EventObject.TAB) {
											if (!e.shiftKey) {
												e.stopEvent();
												parentWin.buttons[0].focus();
											}
										}
									}
								}
							}, {
								fieldLabel: 'Масса(вес) при рождении, г',
								name: 'PersonNewBorn_Weight',
								allowNegative: false,
								allowDecimals: false,
								maxLength: 4,
								width: 100,
								xtype: 'numberfield',
								listeners:
									{
										'change': function (field, value) {
											if (Ext.isEmpty(value))
												parentWin.NewBorn_Weight = 0;
											else
												parentWin.NewBorn_Weight = value;
										}
									}
							}, {
								fieldLabel: 'Рост(длина) при рождении, см',
								name: 'PersonNewBorn_Height',
								allowNegative: false,
								allowDecimals: false,

								maxLength: 2,
								width: 100,
								xtype: 'numberfield'
							}, {
								fieldLabel: 'Окружность головы, см',
								name: 'PersonNewBorn_Head',
								allowNegative: false,
								allowDecimals: false,
								maxLength: 2,
								width: 100,
								xtype: 'numberfield'
							}, {
								fieldLabel: 'Окружность груди, см',
								name: 'PersonNewBorn_Breast',
								maxLength: 2,
								allowNegative: false,
								allowDecimals: false,
								width: 100,
								xtype: 'numberfield'
							}, {
								comboSubject: 'YesNo',
								fieldLabel: 'Наличие кровотечения',
								hiddenName: 'PersonNewBorn_IsBleeding',
								width: 100,
								xtype: 'swcommonsprcombo'
							}, {
								autoHeight: true,
								layout: 'form',
								style: 'padding: 2px 10px;',
								title: 'Критерии живорождения',
								xtype: 'fieldset',
								hidden: getRegionNick() != 'kz',

								items: [{
									layout: 'column',
									border: false,
									defaults: {
										border: false,
										style: 'margin-right: 20px;'
									},
									items: [{
										layout: 'form',
										items: [{
											xtype: 'checkbox',
											name: 'PersonNewBorn_IsBreath',
											hideLabel: true,
											boxLabel: 'Дыхание'
										}]
									}, {
										layout: 'form',
										items: [{
											xtype: 'checkbox',
											name: 'PersonNewBorn_IsHeart',
											hideLabel: true,
											boxLabel: 'Сердцебиение'
										}]
									}, {
										layout: 'form',
										items: [{
											xtype: 'checkbox',
											name: 'PersonNewBorn_IsPulsation',
											hideLabel: true,
											boxLabel: 'Пульсация пуповины'
										}]
									}, {
										layout: 'form',
										items: [{
											xtype: 'checkbox',
											name: 'PersonNewBorn_IsMuscle',
											hideLabel: true,
											boxLabel: 'Произвольное сокращение мускулатуры'
										}]
									}]
								}]
							}, new sw.Promed.ViewFrame({
								//border:false,
								actions: [
									{
										name: 'action_add',
										handler: function () {
											this.addNewbornApgarRate();
										}.createDelegate(this)
									},

									{
										name: 'action_edit',
										hidden: true
									},

									{
										name: 'action_view',
										hidden: true
									},

									{
										name: 'action_delete',
										handler: function () {
											parentWin.deleteApgarRate()
										}
									},

									{
										name: 'action_refresh',
										hidden: true
									},

									{
										name: 'action_print',
										hidden: true
									},
									{name: 'action_save', hidden: true}
								],
								autoLoadData: false,
								focusOnFirstLoad: false,
								saveAtOnce: false,
								dataUrl: '/?c=PersonNewBorn&m=loadNewbornApgarRateGrid',
								height: 140,
								id: 'ESEW_NewbornApgarRateGrid',
								onLoadData: function () {
									//
								},
								onRowSelect: function (sm, index, record) {
									//
								},
								onAfterEdit: function (o) {
									o.grid.stopEditing(true);
									var rec = o.record;
									var isEmp = (Ext.isEmpty(rec.get('NewbornApgarRate_Heartbeat')) && Ext.isEmpty(rec.get('NewbornApgarRate_Breath')) && Ext.isEmpty(rec.get('NewbornApgarRate_SkinColor')) && Ext.isEmpty(rec.get('NewbornApgarRate_ToneMuscle')) && Ext.isEmpty(rec.get('NewbornApgarRate_Reflex')))
									var sum = Number(rec.get('NewbornApgarRate_Heartbeat')) + Number(rec.get('NewbornApgarRate_Breath')) + Number(rec.get('NewbornApgarRate_SkinColor')) + Number(rec.get('NewbornApgarRate_ToneMuscle')) + Number(rec.get('NewbornApgarRate_Reflex'))
									if (!isEmp) rec.set('NewbornApgarRate_Values', sum);
									if (rec.get('RecordStatus_Code') == 1) {
										rec.set('RecordStatus_Code', 2);
									}
									o.record.commit();
									log(o);
								},
								paging: false,
								region: 'center',
								stringfields: [
									{
										name: 'NewbornApgarRate_id',
										type: 'int',
										header: 'ID',
										key: true
									},

									{
										name: 'PersonNewBorn_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'RecordStatus_Code',
										type: 'int',
										hidden: true
									},
									{
										name: 'NewbornApgarRate_Time',
										type: 'string',
										editor: new Ext.form.NumberField({allowDecimals: false, maxValue: 60}),
										header: 'Время после рождения, мин'
									},

									{
										name: 'NewbornApgarRate_Heartbeat',
										type: 'string',
										editor: new Ext.form.NumberField({
											maxLength: 1,
											maxValue: 2,
											allowDecimals: false
										}),
										header: 'Сердцебиение'
									},

									{
										name: 'NewbornApgarRate_Breath',
										type: 'string',
										editor: new Ext.form.NumberField({
											maxLength: 1,
											maxValue: 2,
											allowDecimals: false
										}),
										header: 'Дыхание'
									},

									{
										name: 'NewbornApgarRate_SkinColor',
										type: 'int',
										editor: new Ext.form.NumberField({
											maxLength: 1,
											maxValue: 2,
											allowDecimals: false
										}),
										header: 'Окраска кожи'
									},

									{
										name: 'NewbornApgarRate_ToneMuscle',
										type: 'int',
										editor: new Ext.form.NumberField({
											maxLength: 1,
											maxValue: 2,
											allowDecimals: false
										}),
										header: 'Тонус мышц'
									},

									{
										name: 'NewbornApgarRate_Reflex',
										type: 'int',
										editor: new Ext.form.NumberField({
											maxLength: 1,
											maxValue: 2,
											allowDecimals: false
										}),
										header: 'Рефлексы'
									},

									{
										name: 'NewbornApgarRate_Values',
										type: 'int',
										editor: new Ext.form.NumberField({maxValue: 10, allowDecimals: false}),
										header: 'Масса',
										header: 'Оценка в баллах'
									}
								],
								title: langs('Оценка состояния по шкале Апгар')
							}),
							{
								xtype: 'fieldset',
								title: 'Анализ крови',
								autoHeight: true,
								style: 'padding: 0px;',
								labelWidth: 170,
								items: [{
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 170,
											items: [
												{
													allowDecimals: false,
													xtype: 'numberfield',
													name: 'PersonNewborn_BloodBili',
													fieldLabel: 'Общий билирубин, Ммоль/л'
												}
											]
										}, {
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'label',
													id: 'ESecEF_BloodBili_Xml',
													border: false,
													hidden: true,
													style: 'margin-left: 10px;',
													html: ''
												}
											]
										}
									]
								}, {
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 170,
											items: [
												{
													allowDecimals: false,
													xtype: 'numberfield',
													name: 'PersonNewborn_BloodHemoglo',
													fieldLabel: 'Гемоглобин, г/л'
												}
											]
										}, {
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'label',
													id: 'ESecEF_BloodHemoglo_Xml',
													hidden: true,
													style: 'margin-left: 10px;',
													html: ''
												}
											]
										}
									]
								}, {
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 170,
											items: [
												{
													xtype: 'numberfield',
													name: 'PersonNewborn_BloodEryth',
													fieldLabel: 'Эритроциты, 10^12/л'
												}
											]
										}, {
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'label',
													id: 'ESecEF_BloodEryth_Xml',
													hidden: true,
													style: 'margin-left: 10px;',
													html: ''
												}
											]
										}
									]
								}, {
									layout: 'column',
									border: false,
									items: [
										{
											layout: 'form',
											border: false,
											labelWidth: 170,
											items: [
												{
													xtype: 'numberfield',
													name: 'PersonNewborn_BloodHemato',
													fieldLabel: 'Гематокрит, %'
												}
											]
										}, {
											layout: 'form',
											border: false,
											items: [
												{
													xtype: 'label',
													id: 'ESecEF_BloodHemato_Xml',
													hidden: true,
													style: 'margin-left: 10px;',
													html: ''
												}
											]
										}
									]
								}]
							},
							{
								fieldLabel: 'Переведен в',
								comboSubject: 'NewBornWardType',
								hiddenName: 'NewBornWardType_id',
								name: 'NewBornWardType_id',
								width: 300,
								xtype: 'swcommonsprcombo'
							}, {
								layout: 'form',
								border: false,
								items: [
									{
										layout: 'column',
										border: false,
										items: [
											{
												layout: 'form',
												border: false,
												labelWidth: 190,
												width: 310,
												items: [
													{
														comboSubject: 'YesNo',
														fieldLabel: 'Неонатальный скрининг',
														hiddenName: 'PersonNewBorn_IsNeonatal',
														width: 100,
														xtype: 'swcommonsprcombo',
														listeners: {
															'change': function(combo, newValue, oldValue) {
																if (getRegionNick() == 'ufa') {
																	var base_form = win.findById('EvnSectionEditForm').getForm();
																	if (base_form)
																		var hidefield = base_form.findField('RefuseType_aid');
																	if (hidefield)
																		var hidepanel = hidefield.findParentByType('panel');
																	if (hidepanel) {
																		hidepanel.setVisible(newValue == 1);
																	}
																}
															}
														}
													}
												]
											}, {
												layout: 'form',
												border: false,
												labelWidth: 90,
												width: 310,
												hidden: getRegionNick() != 'ufa',
												items: [
													{
														comboSubject: 'RefuseType',
														fieldLabel: 'Уточнение',
														hiddenName: 'RefuseType_aid',
														width: 150,
														xtype: 'swcommonsprcombo'
													}
												]
											}
										]
									}
								]
							}, {
								layout: 'form',
								border: false,
								items: [
									{
										layout: 'column',
										border: false,
										items: [
											{
												layout: 'form',
												border: false,
												labelWidth: 190,
												width: 310,
												items: [
													{
														comboSubject: 'YesNo',
														fieldLabel: 'Аудиологический скрининг',
														hiddenName: 'PersonNewBorn_IsAudio',
														width: 100,
														xtype: 'swcommonsprcombo',
														listeners: {
															'change': function(combo, record, index) {
																if (getRegionNick() == 'ufa') {
																	var base_form = win.findById('EvnSectionEditForm').getForm();
																	if (base_form)
																		var hidefield = base_form.findField('RefuseType_pid');
																	if (hidefield)
																		var hidepanel = hidefield.findParentByType('panel');
																	if (hidepanel) {
																		hidepanel.setVisible(record == 1);
																	}
																}
															}
														}
													}
												]
											}, {
												layout: 'form',
												border: false,
												labelWidth: 90,
												width: 310,
												hidden: getRegionNick() != 'ufa',
												items: [
													{
														comboSubject: 'RefuseType',
														fieldLabel: 'Уточнение',
														hiddenName: 'RefuseType_pid',
														width: 150,
														xtype: 'swcommonsprcombo'
													}
												]
											}
										]
									}
								]
							},
							{
								autoHeight: true,
								labelWidth: 150,
								layout: 'form',
								style: 'padding: 0px;',
								title: 'Вакцинация',
								xtype: 'fieldset',

								items: [
									{
										layout: 'form',
										border: false,
										items: [
											{
												layout: 'column',
												border: false,
												items: [
													{
														layout: 'form',
														border: false,
														labelWidth: 80,
														width: 210,
														items: [
															{
																comboSubject: 'YesNo',
																fieldLabel: 'БЦЖ',
																hiddenName: 'PersonNewBorn_IsBCG',
																width: 100,
																xtype: 'swcommonsprcombo',
																listeners: {
																	'change': function(combo, record, index) {
																		if (getRegionNick() == 'ufa') {
																			var base_form = win.findById('EvnSectionEditForm').getForm();
																			if (base_form)
																				var hidefield = base_form.findField('RefuseType_bid');
																			if (hidefield)
																				var hidepanel = hidefield.findParentByType('panel');
																			if (hidepanel) {
																				hidepanel.setVisible(record == 1);
																			}
																		}
																	}
																}
															}
														]
													}, {
														layout: 'form',
														border: false,
														labelWidth: 90,
														width: 270,
														hidden: getRegionNick() != 'ufa',
														items: [
															{
																comboSubject: 'RefuseType',
																fieldLabel: 'Уточнение',
																hiddenName: 'RefuseType_bid',
																width: 150,
																xtype: 'swcommonsprcombo'
															}
														]
													}, {
														layout: 'form',
														border: false,
														labelWidth: 50,
														width: 180,
														items: [
															{
																fieldLabel: 'Дата',
																format: 'd.m.Y',
																name: 'PersonNewBorn_BCGDate',
																plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
																selectOnFocus: true,
																width: 100,
																xtype: 'swdatefield'
															}
														]
													}, {
														layout: 'form',
														border: false,
														labelWidth: 50,
														items: [
															{
																fieldLabel: 'Серия',
																name: 'PersonNewBorn_BCGSer',
																width: 100,
																xtype: 'textfield'
															}
														]
													}, {
														layout: 'form',
														border: false,
														labelWidth: 50,
														items: [
															{
																fieldLabel: 'Номер',
																name: 'PersonNewBorn_BCGNum',
																width: 100,
																xtype: 'textfield'
															}
														]
													}
												]
											}, {
												layout: 'column',
												border: false,
												items: [
													{
														layout: 'form',
														border: false,
														labelWidth: 80,
														width: 210,
														items: [
															{
																comboSubject: 'YesNo',
																fieldLabel: 'Гепатит B',
																hiddenName: 'PersonNewBorn_IsHepatit',
																width: 100,
																xtype: 'swcommonsprcombo',
																listeners: {
																	'change': function(combo, record, index) {
																		if (getRegionNick() == 'ufa') {
																			var base_form = win.findById('EvnSectionEditForm').getForm();
																			if (base_form)
																				var hidefield = base_form.findField('RefuseType_gid');
																			if (hidefield)
																				var hidepanel = hidefield.findParentByType('panel');
																			if (hidepanel) {
																				hidepanel.setVisible(record == 1);
																			}
																		}
																	}
																}
															}
														]
													}, {
														layout: 'form',
														border: false,
														labelWidth: 90,
														width: 270,
														hidden: getRegionNick() != 'ufa',
														items: [
															{
																comboSubject: 'RefuseType',
																fieldLabel: 'Уточнение',
																hiddenName: 'RefuseType_gid',
																width: 150,
																xtype: 'swcommonsprcombo'
															}
														]
													}, {
														layout: 'form',
														border: false,
														labelWidth: 50,
														width: 180,
														items: [
															{
																fieldLabel: 'Дата',
																format: 'd.m.Y',
																name: 'PersonNewBorn_HepatitDate',
																plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
																selectOnFocus: true,
																width: 100,
																xtype: 'swdatefield'
															}
														]
													}, {
														layout: 'form',
														border: false,
														labelWidth: 50,
														items: [
															{
																fieldLabel: 'Серия',
																name: 'PersonNewBorn_HepatitSer',
																width: 100,
																xtype: 'textfield'
															}
														]
													}, {
														layout: 'form',
														border: false,
														labelWidth: 50,
														items: [
															{
																fieldLabel: 'Номер',
																name: 'PersonNewBorn_HepatitNum',
																width: 100,
																xtype: 'textfield'
															}
														]
													}
												]
											}
										]
									}

								]
							}

						]
					}]
				}, {
					title: 'Родовые травмы, пороки развития',
					id: 'tab_ESEWTrauma',
					iconCls: 'info16',
					border: false,
					items: [{
						layout: 'form',
						border: false,
						bodyStyle: 'padding: 5px 5px 0',
						labelAlign: 'right',
						labelWidth: 150,
						items: [
							new sw.Promed.ViewFrame({
								//border:false,
								actions: [
									{
										name: 'action_add',
										handler: function () {
											this.openPersonBirthTraumaEditWindow('add', 1);
										}.createDelegate(this)
									},

									{
										name: 'action_edit',
										hidden: true
									},

									{
										name: 'action_view',
										handler: function () {
											this.openPersonBirthTraumaEditWindow('view', 1);
										}.createDelegate(this)
									},

									{
										name: 'action_delete',
										handler: function () {
											parentWin.deletePersonBirthTrauma(1)
										}
									},

									{
										name: 'action_refresh',
										disabled: true
									},

									{
										name: 'action_print',
										disabled: true
									}
								],
								autoLoadData: false,
								focusOnFirstLoad: false,
								dataUrl: '/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height: 130,
								id: 'ESEW_PersonBirthTraumaGrid1',
								onDblClick: function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onEnter: function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onLoadData: function () {
									//
								},
								onRowSelect: function (sm, index, record) {
									this.getAction('action_delete').setDisabled((!Ext.isEmpty(record.get('PersonBirthTrauma_IsEdit')) && record.get('PersonBirthTrauma_IsEdit') == '1'));
								},
								paging: false,
								region: 'center',
								stringfields: [
									{
										name: 'PersonBirthTrauma_id',
										type: 'int',
										header: 'ID',
										key: true
									},

									{
										name: 'PersonNewBorn_id',
										type: 'int',
										hidden: true
									},

									{
										name: 'Diag_Code',
										type: 'string',
										//hidden:true
										header: 'Код'
									},
									{
										name: 'PersonBirthTrauma_setDate',
										type: 'date',
										hidden: true
									},
									{
										name: 'BirthTraumaType_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'RecordStatus_Code',
										type: 'int',
										hidden: true
									},
									{
										name: 'Diag_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'Diag_Name',
										type: 'string',
										//hidden:true
										header: 'Наименование'
									},

									{
										name: 'PersonBirthTrauma_Comment',
										type: 'string',
										//hidden:true
										header: 'Расшифровка'
									},
									{
										name: 'PersonBirthTrauma_IsEdit',
										type: 'string',
										hidden: true,
										header: 'Признак наследования'
									}
								],
								title: 'Родовые травмы'
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions: [
									{
										name: 'action_add',
										handler: function () {
											this.openPersonBirthTraumaEditWindow('add', 2);
										}.createDelegate(this)
									},

									{
										name: 'action_edit',
										hidden: true
									},

									{
										name: 'action_view',
										handler: function () {
											this.openPersonBirthTraumaEditWindow('view', 2);
										}.createDelegate(this)
									},

									{
										name: 'action_delete',
										handler: function () {
											parentWin.deletePersonBirthTrauma(2)
										}
									},

									{
										name: 'action_refresh',
										disabled: true
									},

									{
										name: 'action_print',
										disabled: true
									}
								],
								autoLoadData: false,
								focusOnFirstLoad: false,
								dataUrl: '/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height: 130,
								id: 'ESEW_PersonBirthTraumaGrid2',
								onDblClick: function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onEnter: function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onLoadData: function () {
									//
								},
								onRowSelect: function (sm, index, record) {
									//
									this.getAction('action_delete').setDisabled(!(!Ext.isEmpty(record.get('PersonBirthTrauma_IsEdit')) && record.get('PersonBirthTrauma_IsEdit') == '2'));
								},
								paging: false,
								region: 'center',
								stringfields: [
									{
										name: 'PersonBirthTrauma_id',
										type: 'int',
										header: 'ID',
										key: true
									},

									{
										name: 'PersonNewBorn_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'PersonBirthTrauma_setDate',
										type: 'date',
										hidden: true
									},
									{
										name: 'BirthTraumaType_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'RecordStatus_Code',
										type: 'int',
										hidden: true
									},
									{
										name: 'Diag_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'Diag_Code',
										type: 'string',
										//hidden:true
										header: 'Код'
									},

									{
										name: 'Diag_Name',
										type: 'string',
										//hidden:true
										header: 'Наименование'
									},

									{
										name: 'PersonBirthTrauma_Comment',
										type: 'string',
										//hidden:true
										header: 'Расшифровка'
									},
									{
										name: 'PersonBirthTrauma_IsEdit',
										type: 'string',
										hidden: true,
										header: 'Признак наследования'
									}
								],
								title: 'Поражения плода'
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions: [
									{
										name: 'action_add',
										handler: function () {
											this.openPersonBirthTraumaEditWindow('add', 3);
										}.createDelegate(this)
									},

									{
										name: 'action_edit',
										hidden: true
									},

									{
										name: 'action_view',
										handler: function () {
											this.openPersonBirthTraumaEditWindow('view', 3);
										}.createDelegate(this)
									},

									{
										name: 'action_delete',
										handler: function () {
											parentWin.deletePersonBirthTrauma(3)
										}
									},

									{
										name: 'action_refresh',
										disabled: true
									},

									{
										name: 'action_print',
										disabled: true
									}
								],
								autoLoadData: false,
								focusOnFirstLoad: false,
								dataUrl: '/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height: 130,
								id: 'ESEW_PersonBirthTraumaGrid3',
								onDblClick: function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onEnter: function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onLoadData: function () {
									//
								},
								onRowSelect: function (sm, index, record) {
									//
									this.getAction('action_delete').setDisabled(!(!Ext.isEmpty(record.get('PersonBirthTrauma_IsEdit')) && record.get('PersonBirthTrauma_IsEdit') == '2'));
								},
								paging: false,
								region: 'center',
								stringfields: [
									{
										name: 'PersonBirthTrauma_id',
										type: 'int',
										header: 'ID',
										key: true
									},

									{
										name: 'PersonNewBorn_id',
										type: 'int',
										hidden: true
									},

									{
										name: 'Diag_Code',
										type: 'string',
										//hidden:true
										header: 'Код'
									},
									{
										name: 'PersonBirthTrauma_setDate',
										type: 'date',
										hidden: true
									},
									{
										name: 'BirthTraumaType_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'RecordStatus_Code',
										type: 'int',
										hidden: true
									},
									{
										name: 'Diag_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'Diag_Name',
										type: 'string',
										//hidden:true
										header: 'Наименование'
									},

									{
										name: 'PersonBirthTrauma_Comment',
										type: 'string',
										//hidden:true
										header: 'Расшифровка'
									},
									{
										name: 'PersonBirthTrauma_IsEdit',
										type: 'string',
										hidden: true,
										header: 'Признак наследования'
									}
								],
								title: 'Врожденные пороки развития'
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions: [
									{
										name: 'action_add',
										handler: function () {
											this.openPersonBirthTraumaEditWindow('add', 4);
										}.createDelegate(this)
									},

									{
										name: 'action_edit',
										hidden: true
									},

									{
										name: 'action_view',
										handler: function () {
											this.openPersonBirthTraumaEditWindow('view', 4);
										}.createDelegate(this)
									},

									{
										name: 'action_delete',
										handler: function () {
											parentWin.deletePersonBirthTrauma(4)
										}
									},

									{
										name: 'action_refresh',
										disabled: true
									},

									{
										name: 'action_print',
										disabled: true
									}
								],
								autoLoadData: false,
								focusOnFirstLoad: false,
								dataUrl: '/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height: 130,
								id: 'ESEW_PersonBirthTraumaGrid4',
								onDblClick: function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onEnter: function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onLoadData: function () {
									//
								},
								onRowSelect: function (sm, index, record) {
									//
									this.getAction('action_delete').setDisabled(!(!Ext.isEmpty(record.get('PersonBirthTrauma_IsEdit')) && record.get('PersonBirthTrauma_IsEdit') == '2'));
								},
								paging: false,
								region: 'center',
								stringfields: [
									{
										name: 'PersonBirthTrauma_id',
										type: 'int',
										header: 'ID',
										key: true
									},

									{
										name: 'PersonNewBorn_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'PersonBirthTrauma_setDate',
										type: 'date',
										hidden: true
									},
									{
										name: 'BirthTraumaType_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'RecordStatus_Code',
										type: 'int',
										hidden: true
									},
									{
										name: 'Diag_id',
										type: 'int',
										hidden: true
									},
									{
										name: 'Diag_Code',
										type: 'string',
										//hidden:true
										header: 'Код'
									},

									{
										name: 'Diag_Name',
										type: 'string',
										//hidden:true
										header: 'Наименование'
									},

									{
										name: 'PersonBirthTrauma_Comment',
										type: 'string',
										//hidden:true
										header: 'Расшифровка'
									},
									{
										name: 'PersonBirthTrauma_IsEdit',
										type: 'string',
										hidden: true,
										header: 'Признак наследования'
									}
								],
								title: 'Подозрения на врожденные пороки'
							})
						]
					}]
				}, {
					title: 'Наблюдения',
					id: 'tab_ESEWObserv',
					iconCls: 'info16',
					border: false,
					items: [
						new sw.Promed.ViewFrame({
							id: 'ESEW_EvnObservNewBornGrid',
							border: true,
							autoLoadData: false,
							focusOnFirstLoad: false,
							useEmptyRecord: false,
							dataUrl: '/?c=EvnObserv&m=loadEvnObservGrid',
							height: 600,
							actions: [
								{
									name: 'action_add', handler: function () {
										parentWin.openEvnObservNewBornEditWindow('add')
									}
								},
								{
									name: 'action_edit', handler: function () {
										parentWin.openEvnObservNewBornEditWindow('edit')
									}
								},
								{
									name: 'action_view', handler: function () {
										parentWin.openEvnObservNewBornEditWindow('view')
									}
								},
								{
									name: 'action_delete', handler: function () {
										parentWin.deleteEvnObservNewBorn()
									}
								},
								{name: 'action_refresh', hidden: true}
							],
							stringfields: [
								{name: 'EvnObserv_id', type: 'int', header: 'ID', key: true},
								{name: 'PersonNewBorn_id', type: 'int', hidden: true},
								{name: 'EvnObserv_pid', type: 'int', hidden: true},
								{name: 'EvnObserv_setDate', header: 'Дата', type: 'date', width: 80},
								{name: 'ObservTimeType_Name', header: 'Время', type: 'string', width: 120},
								{name: 'art_davlenie', header: langs('Арт. давление'), type: 'string', width: 80},
								{name: 'temperatura', header: langs('Температура'), type: 'string', width: 80},
								{name: 'puls', header: langs('Пульс'), type: 'string', width: 80},
								{name: 'chastota_dyihaniya', header: langs('Частота дыхания'), type: 'int', width: 80},
								{name: 'ves', header: langs('Вес'), type: 'float', width: 80},
								{name: 'vyipito_jidkosti', header: langs('Выпито жидкости'), type: 'float', width: 80},
								{name: 'kol-vo_mochi', header: langs('Кол-во мочи'), type: 'float', width: 80},
								{
									name: 'reaktsiya_na_osmotr',
									header: langs('Реакция на осмотр'),
									type: 'string',
									width: 80
								},
								{name: 'reaktsiya_zrachka', header: langs('Реакция зрачка'), type: 'string', width: 80},
								{name: 'stul', header: langs('Стул'), type: 'string', width: 80}
							]
						})
					]
				}, {
					title: 'Наблюдение состояния младенца',
					id: 'tab_ESEWEvnNeonatalSurvey',
					iconCls: 'info16',
					border: false,
					items: [
						new sw.Promed.ViewFrame({
							id: 'ESEW_EvnNeonatalSurveyGrid',
							border: true,
							autoLoadData: false,
							focusOnFirstLoad: false,
							useEmptyRecord: false,
							dataUrl: '/?c=EvnNeonatalSurvey&m=loadNeonatalSurveyGrid',
							height: 600,
							actions: [
								{
									name: 'action_add', handler: function () {
										parentWin.openEvnNeonatalSurveyEditWindow('add')
									}
								},
								{
									name: 'action_edit', handler: function () {
										parentWin.openEvnNeonatalSurveyEditWindow('edit')
									}
								},
								{
									name: 'action_view', handler: function () {
										parentWin.openEvnNeonatalSurveyEditWindow('view')
									}
								},
								{
									name: 'action_delete', hidden: true
								}
							],
							stringfields: [
								{name: 'EvnNeonatalSurvey_id', type: 'int', header: 'ID', key: true},
								{name: 'Evn_setD', type: 'string', header: 'Дата', width: 75 },
								{name: 'Evn_setT', type: 'string', header: 'Время', width: 50 },
								{name: 'PersonWeight_Weight', header: 'Масса (г)', type: 'string', width: 60},
								{name: 'PersonTemperature', header: langs('Температура'), type: 'string', width: 80},
								{name: 'BreathFrequency', header: 'Частота дыхания', type: 'string', width: 110},
								{name: 'HeartFrequency', header: 'Частота сердечных сокращений', type: 'string', width: 180},
								{name: 'ReanimConditionType_Name', header: 'Состояние', type: 'string', width: 140},
								{name: 'CheckReact', header: 'Реакция на осмотр', type: 'string', width: 130},
								{name: 'MuscleTone', header: 'Мышечный тонус', type: 'string', width: 120},
								{name: 'Oedemata', header: 'Отеки', type: 'string', width: 80},
								{name: 'HeartTones1', header: 'Ритм сердечных тонов', type: 'string', width: 140},
								{name: 'HeartTones2', header: 'Характер сердечных тонов', type: 'string', width: 160},
								{name: 'RemainUmbilCord', header: 'Пуповинный остаток', type: 'string', width: 130},
								{name: 'UmbilicWound', header: 'Пупочная ранка', type: 'string', width: 130},
								{name: 'EvnSection_pid', header: 'ИД движения', type: 'string', width: 130, hidden: true}
							],
							onRowSelect: function (sm, index, record) {
								var base_form = win.findById('EvnSectionEditForm').getForm();
								if (base_form)
									var EvnSection_id = base_form.findField('EvnSection_pid').getValue();

								this.getAction('action_edit').setDisabled(!(EvnSection_id == record.get('EvnSection_pid')));
							}
						})
					]
				}
			]
		});

		if (getRegionNick() != 'ufa') {
			if (this.tabPanel.getComponent('tab_ESEWEvnNeonatalSurvey'))
				this.tabPanel.remove(this.tabPanel.getComponent('tab_ESEWEvnNeonatalSurvey'));
			if (this.tabPanel.getComponent('tab_ESEWObserv'))
				this.tabPanel.remove(this.tabPanel.getComponent('tab_ESEWObserv'));
		}else {
			if (this.tabPanel.getComponent('tab_ESEWObserv'))
				this.tabPanel.remove(this.tabPanel.getComponent('tab_ESEWObserv'));
		}

		this.CureStandart = new Ext.data.Store({
			autoLoad: false,
			hidden: true,
			rendered: true,
			hiddenName: 'CureStandart_id',
			name: 'CureStandart_id',
			reader: new Ext.data.JsonReader({
				id: 'CureStandart_id'
			}, [
				{name: 'CureStandart_id', mapping: 'CureStandart_id'},
				{name: 'CureStandartTreatment_Duration', mapping: 'CureStandartTreatment_Duration'}
			]),
			url: '/?c=EvnSection&m=getCSDuration'
		});
		this.formFirstShow = true;
		var that = this;
		if (this.id == 'EvnSectionEditWindow') {
			this.tabIndex = TABINDEX_ESECEF;
		} else {
			this.tabIndex = TABINDEX_ESECEF2;
		}
		this.sicknessDiagStore = new Ext.db.AdapterStore({
			autoLoad: true,
			dbFile: 'Promed.db',
			fields: [
				{name: 'SicknessDiag_id', type: 'int'},
				{name: 'Sickness_id', type: 'int'},
				{name: 'Sickness_Code', type: 'int'},
				{name: 'PrivilegeType_id', type: 'int'},
				{name: 'Sickness_Name', type: 'string'},
				{name: 'Diag_id', type: 'int'},
				{name: 'SicknessDiag_begDT', type: 'date', dateFormat: 'd.m.Y'},
				{name: 'SicknessDiag_endDT', type: 'date', dateFormat: 'd.m.Y'}
			],
			key: 'Diag_id',
			sortInfo: {
				field: 'Diag_id'
			},
			tableName: 'SicknessDiag'
		});
		this.morbusDiagStore = new Ext.db.AdapterStore({
			autoLoad: true,
			dbFile: 'Promed.db',
			fields: [
				{name: 'MorbusDiag_id', type: 'int'},
				{name: 'MorbusType_id', type: 'int'},
				{name: 'MorbusType_Code', type: 'int'},
				{name: 'MorbusType_SysNick', type: 'string'},
				{name: 'MorbusType_Name', type: 'string'},
				{name: 'Diag_id', type: 'int'}
			],
			key: 'MorbusDiag_id',
			sortInfo: {
				field: 'Diag_id'
			},
			tableName: 'MorbusDiag'
		});
		this.keyHandlerAlt = {
			alt: true,
			fn: function (inp, e) {
				var current_window = this;

				switch (e.getKey()) {
					case Ext.EventObject.C:
						current_window.doSave({
							print: false
						});
						break;

					case Ext.EventObject.J:
						current_window.onCancelAction();
						break;

					case Ext.EventObject.NUM_ONE:
					case Ext.EventObject.ONE:
						if (!current_window.findById('ESecEF_EvnSectionPanel').hidden) {
							current_window.findById('ESecEF_EvnSectionPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.NUM_TWO:
					case Ext.EventObject.TWO:
						if (!current_window.findById('ESecEF_EvnDiagPSPanel').hidden) {
							current_window.findById('ESecEF_EvnDiagPSPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.NUM_THREE:
					case Ext.EventObject.THREE:
						if (!current_window.findById('ESecEF_EvnSectionNarrowBedPanel').hidden) {
							current_window.findById('ESecEF_EvnSectionNarrowBedPanel').toggleCollapse();
						}
						break;

					case Ext.EventObject.NUM_FOUR:
					case Ext.EventObject.FOUR:
						if (!current_window.specificsPanel.hidden) {
							current_window.specificsPanel.toggleCollapse();
							if (!current_window.specificsPanel.collapsed) {
								parentWin.onSpecificsExpand(parentWin.specificsPanel);

							}
						}
						break;
				}
			},
			key: [
				Ext.EventObject.C,
				Ext.EventObject.J,
				Ext.EventObject.NUM_ONE,
				Ext.EventObject.NUM_TWO,
				Ext.EventObject.NUM_THREE,
				Ext.EventObject.NUM_FOUR,
				Ext.EventObject.ONE,
				Ext.EventObject.TWO,
				Ext.EventObject.THREE,
				Ext.EventObject.FOUR
			],
			stopEvent: true,
			scope: this
		}
		var parentWin = this;
		this.tryFocusOnSpecifics = function () {
			var tree = this.specificsTree;
			tree.focus();
			var selection = tree.getSelectionModel().getSelectedNode();
			var root = tree.getRootNode();
			if (root.attributes.value == selection.attributes.value) {
				//Если выбран корень - значит никакая из еще специфик не открыта. Фокусируемся на дереве специфик
				tree.getRootNode().firstChild.select();
			} else {
				//Смотрим какая из специфик выбрана. Если же вдруг нужного компонента не оказалось, значит, специфика не открыта - фокусируеся на выбранном элементе дерева
				switch (selection.attributes.value) {
					case 'born_data':
						if (parentWin.findById('EvnSectionEditForm').getForm().findField('PersonNewBorn_IsRejection')) {
							parentWin.findById('EvnSectionEditForm').getForm().findField('PersonNewBorn_IsRejection').focus();
						} else {
							selection.select();
						}
						break;
				}
			}
		}.createDelegate(this);
		this.tryFocusOnSpecificsTree = function () {
			var tree = parentWin.specificsTree;
			tree.focus();
			if (tree.getRootNode() == tree.getSelectionModel().getSelectedNode() || tree.getSelectionModel().getSelectedNode() == null) {
				tree.getRootNode().firstChild.select();
			} else {
				tree.getSelectionModel().getSelectedNode().select();
			}
		}
		var mesTemplate = new Ext.XTemplate(
			'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
			'<td style="padding: 2px; width: 50%;">Код</td>',
			'<td style="padding: 2px; width: 50%;">Нормативный срок</td></tr>',
			'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
			'<td style="padding: 2px;">{Mes_Code}&nbsp;</td>',
			'<td style="padding: 2px;">{Mes_KoikoDni}&nbsp;</td>',
			'</tr></tpl>',
			'</table>'
		);

		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') {
			mesTemplate = new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px; width: 20%;">Код</td>',
				'<td style="padding: 2px; width: 30%;">Нормативный срок</td>',
				'<td style="padding: 2px; width: 40%;">Вид мед. помощи</td>',
				'<td style="padding: 2px; width: 10%;">Возрастная группа</td></tr>',
				'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
				'<td style="padding: 2px;">{Mes_Code}&nbsp;</td>',
				'<td style="padding: 2px;">{Mes_KoikoDni}&nbsp;</td>',
				'<td style="padding: 2px;">{MedicalCareKind_Name}&nbsp;</td>',
				'<td style="padding: 2px;">{MesAgeGroup_Name}&nbsp;</td>',
				'</tr></tpl>',
				'</table>'
			);
		}

		this.recalcKSGButton = new Ext.Button({
			border: false,
			layout: 'form',
			hidden: !(getRegionNick().inlist(['perm'])),
			items: [{
				text: '=',
				tooltip: 'Рассчитать КСГ',
				handler: function () {
					parentWin.loadKSGKPGKOEF('button');
				}.createDelegate(this),
				xtype: 'button'
			}]
		});

		this.KSGKPGFields = {
			border: false,
			layout: 'form',
			items: [{
				readOnly: true,
				fieldLabel: getRegionNick() == 'kz' ? 'КЗГ' : 'КСГ',
				name: 'EvnSection_KSG',
				listeners: {
					'change': function (field, newValue) {
						parentWin.checkAccessAddEvnSectionDrugPSLink();
					}
				},
				tabIndex: this.tabIndex + 24,
				width: 500,
				xtype: 'textfield'
			}, {
				readOnly: true,
				fieldLabel: 'КПГ',
				name: 'EvnSection_KPG',
				tabIndex: this.tabIndex + 25,
				width: 500,
				xtype: 'textfield'
			}]
		};
		if (getRegionNick() == 'pskov') {
			this.KSGKPGFields = {
				border: false,
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							readOnly: true,
							fieldLabel: 'КСГ',
							name: 'EvnSection_KSG',
							listeners: {
								'change': function (field, newValue) {
									parentWin.checkAccessAddEvnSectionDrugPSLink();
								}
							},
							tabIndex: this.tabIndex + 24,
							width: 500,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						style: 'padding-left: 10px;',
						layout: 'form',
						items: [{
							readOnly: true,
							hideLabel: true,
							name: 'EvnSection_MesOldUslugaComplexLink_Number',
							width: 80,
							xtype: 'textfield'
						}]
					}]
				}, {
					readOnly: true,
					fieldLabel: 'КПГ',
					name: 'EvnSection_KPG',
					tabIndex: this.tabIndex + 25,
					width: 80,
					xtype: 'textfield'
				}]
			};
		}

		if (getRegionNick() == 'astra') {
			this.KSGKPGFields = {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						readOnly: true,
						fieldLabel: 'КСГ',
						name: 'EvnSection_KSG',
						tabIndex: this.tabIndex + 25,
						width: 80,
						xtype: 'textfield'
					}]
				}, {
					border: false,
					labelWidth: 40,
					layout: 'form',
					items: [{
						readOnly: true,
						fieldLabel: 'КПГ',
						name: 'EvnSection_KPG',
						tabIndex: this.tabIndex + 26,
						width: 80,
						xtype: 'textfield'
					}]
				}, {
					border: false,
					labelWidth: 40,
					style: 'padding-left: 10px;',
					layout: 'form',
					items: [{
						hideLabel: true,
						hidden: true,
						boxLabel: 'по реабилитации',
						name: 'EvnSection_IsRehab',
						tabIndex: this.tabIndex + 27,
						xtype: 'checkbox'
					}]
				}]
			};
		}

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
			limit: getRegionNick().inlist(['kareliya', 'ufa', 'astra', 'khak', 'perm', 'vologda', 'msk', 'krym', 'buryatiya', 'penza', 'pskov', 'ekb', 'adygeya']) ? null : 1,
			baseFilter: null,
			setBaseFilter: function (filterFn) {
				var base_form = this.findById('EvnSectionEditForm').getForm();
				var container = this.DrugTherapySchemePanel;
				container.baseFilter = filterFn;

				for (var num = 0; num <= container.lastNum; num++) {
					var field = base_form.findField('DrugTherapyScheme_id_' + num);
					if (field) {
						var rec = field.getStore().getById(field.getValue());
						if (!rec || !container.baseFilter(rec)) {
							container.deleteFieldSet(num);
						} else {
							field.setBaseFilter(container.baseFilter);
						}
					}
				}
			}.createDelegate(this),
			getIds: function () {
				var base_form = this.findById('EvnSectionEditForm').getForm();
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
				var add_button = this.findById(parentWin.id + '_ButtonDrugTherapySchemePanel');

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
				var base_form = this.findById('EvnSectionEditForm').getForm();
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
					this.loadKSGKPGKOEF();
				}
			}.createDelegate(this),
			addFieldSet: function (options) {
				var base_form = this.findById('EvnSectionEditForm').getForm();
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
							listeners: {
								'change': function (combo, newValue, oldValue) {
									this.loadKSGKPGKOEF();
									if(combo.qtip) {
										combo.qtip.text = combo.getFieldValue('DrugTherapyScheme_Name');
										if(newValue && !oldValue) Ext.QuickTips.register(combo.qtip);
										else if(!newValue && oldValue) Ext.QuickTips.unregister(combo.getEl());
									}
								}.createDelegate(this)
							},
							width: 430
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
			items: [parentWin.DrugTherapySchemeBodyPanel, {
				layout: 'column',
				id: parentWin.id + '_ButtonDrugTherapySchemePanel',
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

		this.EvnSectionKSGGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_refresh', disabled: true, hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=EvnSection&m=loadEvnSectionKSGList',
			height: 200,
			hidden: true,
			paging: false,
			region: 'center',
			onLoadData: function() {
				parentWin.EvnSectionKSGGrid.setActionHidden('action_selectksg', parentWin.EvnSectionKSGGrid.getGrid().getStore().getCount() < 2);
				parentWin.EvnSectionKSGGrid.getGrid().getStore().filterBy(function(rec) {
					return rec.get('EvnSectionKSG_IsPaidMes') == 2;
				});
			},
			stringfields: [
				{name: 'EvnSectionKSG_id', type: 'int', header: 'ID', key: true},
				{name: 'Mes_Code', type: 'string', header: langs('Номер КСГ'), width: 100},
				{name: 'MesOld_Num', type: 'string', header: langs('Код КСГ'), width: 100},
				{name: 'Mes_Name', type: 'string', header: langs('Наименование КСГ'), width: 200, id: 'autoexpand'},
				{name: 'EvnSectionKSG_begDate', type: 'date', header: langs('Дата начала'), width: 120},
				{name: 'EvnSectionKSG_endDate', type: 'date', header: langs('Дата окончания'), width: 120},
				{name: 'MesTariff_Value', type: 'float', header: langs('КЗ'), width: 100},
				{name: 'EvnSectionKSG_ItogKSLP', type: 'float', header: langs('КСЛП'), width: 100},
				{name: 'EvnSectionKSG_IsPaidMes', type: 'int', hidden: true}
			],
			title: 'КСГ',
			toolbar: true,
			uniqueId: true
		});

		Ext.apply(this, {
			keys: [this.keyHandlerAlt],
			buttons: [
				{
					handler: function () {
						if (getRegionNick().inlist([ 'ufa' ])){
							var base_form = this.findById('EvnSectionEditForm').getForm();
							if (base_form && base_form.findField('ChildTermType_id'))
								base_form.findField('ChildTermType_id').setDisabled(false);
						}
						this.doSave({
							print: false
						});
					}.createDelegate(this),
					iconCls: 'save16',
					onShiftTabAction: function () {
						var isBuryatiya = (getRegionNick() == 'buryatiya');
						var isPskov = (getRegionNick() == 'pskov');

						if (!this.specificsPanel.collapsed && this.action != 'view') {
							this.tryFocusOnSpecifics();
						}
						else if (/*getRegionNick().inlist([ 'ufa' ]) &&*/ !this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
							this.findById('ESecEF_EvnSectionNarrowBedGrid').getView().focusRow(0);
							this.findById('ESecEF_EvnSectionNarrowBedGrid').getSelectionModel().selectFirstRow();
						}
						else if (!this.findById('ESecEF_EvnDiagPSPanel').collapsed && this.findById('ESecEF_EvnDiagPSGrid').getStore().getCount() > 0) {
							this.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
							this.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
						}
						else if (!this.findById('ESecEF_EvnUslugaPanel').collapsed && this.findById('ESecEF_EvnUslugaPanel').getStore().getCount() > 0) {
							this.findById('ESecEF_EvnUslugaPanel').getView().focusRow(0);
							this.findById('ESecEF_EvnUslugaPanel').getSelectionModel().selectFirstRow();
						}
						else if (!this.findById('ESecEF_EvnSectionPanel').collapsed && this.action != 'view') {
							if (!isBuryatiya && !isPskov && !this.findById('EvnSectionEditForm').getForm().findField('Mes_id').disabled) {
								this.findById('EvnSectionEditForm').getForm().findField('Mes_id').focus(true);
							}
							else {
								this.findById('EvnSectionEditForm').getForm().findField('Diag_id').focus(true);
							}
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					onTabAction: function () {
						this.buttons[this.buttons.length - 1].focus();
					}.createDelegate(this),
					tabIndex: this.tabIndex + 55,
					text: BTN_FRMSAVE
				},
				{
					hidden: true,
					id: 'ESEW_PrintPregnancyResultButton',
					handler: function () {
						this.printPregnancyResult();
					}.createDelegate(this),
					iconCls: 'print16',
					text: 'Печать исхода беременности'
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function () {
						this.onCancelAction();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						if (this.action != 'view') {
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					onTabAction: function () {
						if (!this.findById('ESecEF_EvnSectionPanel').collapsed && this.action != 'view') {
							if (!this.findById('EvnSectionEditForm').getForm().findField('EvnSection_setDate').disabled) {
								this.findById('EvnSectionEditForm').getForm().findField('EvnSection_setDate').focus(true);
							}
							else {
								this.findById('EvnSectionEditForm').getForm().findField('EvnSection_disDate').focus(true);
							}
						}
						else if (!this.findById('ESecEF_EvnDiagPSPanel').collapsed && this.findById('ESecEF_EvnDiagPSGrid').getStore().getCount() > 0) {
							this.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
							this.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
						}
						else if (!this.findById('ESecEF_EvnUslugaPanel').collapsed && this.findById('ESecEF_EvnUslugaPanel').getStore().getCount() > 0) {
							this.findById('ESecEF_EvnUslugaPanel').getView().focusRow(0);
							this.findById('ESecEF_EvnUslugaPanel').getSelectionModel().selectFirstRow();
						}
						else if (/*getRegionNick().inlist([ 'ufa' ]) &&*/ !this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
							this.findById('ESecEF_EvnSectionNarrowBedGrid').getView().focusRow(0);
							this.findById('ESecEF_EvnSectionNarrowBedGrid').getSelectionModel().selectFirstRow();
						}
						else if (!this.specificsPanel.collapsed) {
							var tree = this.specificsTree;
							tree.focus();
							if (tree.getRootNode() == tree.getSelectionModel().getSelectedNode()) {
								tree.getRootNode().firstChild.select();
							} else {
								tree.getSelectionModel().getSelectedNode().select();
							}
						}
						else if (this.action != 'view') {
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					tabIndex: this.tabIndex + 56,
					text: BTN_FRMCANCEL
				}
			],
			items: [new sw.Promed.PersonInformationPanelShort({
				id: 'ESecEF_PersonInformationFrame',
				region: 'north'
			}),
				new Ext.form.FormPanel({
					autoScroll: true,
					autoheight: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnSectionEditForm',
					labelAlign: 'right',
					labelWidth: 180,
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{name: 'accessType'},
						{name: 'Mes_tid'},
						{name: 'Mes_sid'},
						{name: 'Mes_kid'},
						{name: 'EvnSection_CoeffCTP'},
						{name: 'MesTariff_id'},
						{name: 'MesTariff_sid'},
						{name: 'AnatomWhere_id'},
						{name: 'Diag_aid'},
						{name: 'Diag_id'},
						{name: 'Diag_cid'},
						{name: 'Diag_eid'},
						{name: 'DiagSetPhase_id'},
						{name: 'DiagSetPhase_aid'},
						{name: 'PrivilegeType_id'},
						{name: 'EvnDie_expDate'},
						{name: 'EvnDie_expTime'},
						{name: 'EvnDie_id'},
						{name: 'EvnDie_IsWait'},
						{name: 'EvnDie_IsAnatom'},
						{name: 'EvnLeave_id'},
						{name: 'EvnLeave_IsAmbul'},
						{name: 'EvnLeave_UKL'},
						{name: 'EvnOtherLpu_id'},
						{name: 'EvnOtherSection_id'},
						{name: 'EvnOtherSectionBedProfile_id'},
						{name: 'EvnOtherStac_id'},
						{name: 'EvnSection_PhaseDescr'},
						{name: 'CureResult_id'},
						{name: 'EvnSection_disDate'},
						{name: 'EvnSection_disTime'},
						{name: 'EvnSection_id'},
						{name: 'EvnSection_pid'},
						{name: 'EvnSection_Index'},
						{name: 'EvnDiagPS_id'},
						{name: 'EvnSection_setDate'},
						{name: 'EvnSection_setTime'},
						{name: 'EvnSection_IsPaid'},
						{name: 'EvnSection_IndexRep'},
						{name: 'EvnSection_IndexRepInReg'},
						{name: 'LeaveCause_id'},
						{name: 'LeaveType_id'},
						{name: 'LeaveType_SysNick'},
						{name: 'LeaveTypeFed_id'},
						{name: 'LeaveType_fedid'},
						{name: 'ResultDeseaseType_fedid'},
						{name: 'Org_oid'},
						{name: 'LpuSection_aid'},
						{name: 'LpuSection_id'},
						{name: 'LpuSectionTransType_id'},
						{name: 'LpuSection_oid'},
						{name: 'LpuSectionBedProfile_oid'},
						{name: 'LpuSectionBedProfileLink_fedoid'},
						{name: 'LpuSectionBedProfile_id'},
						{name: 'LpuSectionBedProfileLink_fedid'},
						{name: 'LpuUnitType_oid'},
						{name: 'LpuSectionWard_id'},
						{name: 'EvnSection_insideNumCard'},
						{name: 'MedPersonal_aid'},
						{name: 'MedPersonal_did'},
						{name: 'MedPersonal_id'},
						{name: 'Mes_id'},
						{name: 'Mes2_id'},
						{name: 'Org_aid'},
						{name: 'PayType_id'},
						{name: 'PayTypeERSB_id'},
						{name: 'EvnSection_PlanDisDT'},
						{name: 'Person_id'},
						{name: 'PersonEvn_id'},
						{name: 'ResultDesease_id'},
						{name: 'Server_id'},
						{name: 'TariffClass_id'},
						{name: 'EvnSection_IsAdultEscort'},
						{name: 'EvnSection_IsMedReason'},
						{name: 'EvnSection_IsMeal'},
						{name: 'EvnSection_IsTerm'},
						{name: 'UslugaComplex_id'},
						{name: 'LpuSectionProfile_id'},
						{name: 'HTMedicalCareClass_id'},
						{name: 'PrehospTrauma_id'},
						{name: 'EvnSection_IsRehab'},
						{name: 'RankinScale_id'},
						{name: 'RankinScale_sid'},
						{name: 'EvnSection_InsultScale'},
						{name: 'EvnSection_NIHSSAfterTLT'},
						{name: 'EvnSection_NIHSSLeave'},
						{name: 'EvnSection_IsST'},
						{name: 'EvnSection_IsCardShock'},
						{name: 'EvnSection_StartPainHour'},
						{name: 'EvnSection_StartPainMin'},
						{name: 'EvnSection_GraceScalePoints'},
						{name: 'PregnancyEvnPS_Period'},
						{name: 'EvnSection_BarthelIdx'},
						{name: 'DeseaseBegTimeType_id'},
						{name: 'DeseaseType_id'},
						{name: 'DrugTherapyScheme_ids'},
						{name: 'MesDop_ids'},
						{name: 'RehabScale_id'},
						{name: 'RehabScale_vid'},
						{name: 'EvnSection_SofaScalePoints'},
						{name: 'TumorStage_id'},
						{name: 'PainIntensity_id'},
						{name: 'EvnSection_isPartialPay'},
						{name: 'EvnSection_IsZNO'},
						{name: 'Diag_spid'},
						{name: 'GetRoom_id'},
						{name: 'GetBed_id'},
						{name: 'MedicalCareBudgType_id'},
						{name: 'EvnSection_Absence'}
					]),
					region: 'center',
					url: '/?c=EvnSection&m=saveEvnSection',
					items: [
						{
							name: 'LeaveType_SysNick',
							value: '',
							xtype: 'hidden'
						},
						{
							name: 'Mes_tid', // КСГ найденная через диагноз
							xtype: 'hidden'
						},
						{
							name: 'Mes_sid', // КСГ найденная через услугу
							xtype: 'hidden'
						},
						{
							name: 'PrehospTrauma_id',
							xtype: 'hidden'
						},
						{
							name: 'Mes_kid', // КПГ
							xtype: 'hidden'
						},
						{
							name: 'MesTariff_id', // коэффициент
							xtype: 'hidden'
						},
						{
							name: 'MesTariff_sid', // коэффициент КПГ
							xtype: 'hidden'
						},
						{
							name: 'accessType',
							value: '',
							xtype: 'hidden'
						},
						{
							name: 'Evn_Name',
							value: '',
							xtype: 'hidden'
						},
						{
							name: 'EvnDiagPS_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnDie_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnLeave_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnOtherLpu_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnOtherSection_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnOtherSectionBedProfile_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnSection_Index',
							value: -1,
							xtype: 'hidden'
						},
						{
							name: 'EvnOtherStac_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnSection_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnSection_pid',
							value: -1,
							xtype: 'hidden'
						},
						{
							name: 'EvnSection_IsPaid',
							xtype: 'hidden'
						},
						{
							name: 'EvnSection_IndexRep',
							xtype: 'hidden'
						},
						{
							name: 'EvnSection_IndexRepInReg',
							xtype: 'hidden'
						},
						{
							// Патологоанатом
							name: 'MedPersonal_aid',
							value: 0,
							xtype: 'hidden'
						},
						{
							// Врач, установивший смерть
							name: 'MedPersonal_did',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'MedPersonal_id',
							value: -1,
							xtype: 'hidden'
						},
						{
							name: 'Person_id',
							value: -1,
							xtype: 'hidden'
						},
						{
							name: 'PersonEvn_id',
							value: -1,
							xtype: 'hidden'
						},
						{
							name: 'Server_id',
							value: -1,
							xtype: 'hidden'
						},
						{
							name: 'EvnSection_IsCardioCheck',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'MesOldUslugaComplex_id',
							xtype: 'hidden'
						},
						{
							name: 'LpuSectionBedProfile_id',
							xtype: 'hidden'
						}, {
							name: 'EvnSection_IsZNO',
							xtype: 'hidden'
						},
						new sw.Promed.Panel({
							autoHeight: true,
							bodyStyle: 'padding-top: 0.5em;',
							border: true,
							collapsible: true,
							id: 'ESecEF_EvnSectionPanel',
							layout: 'form',
							listeners: {
								'expand': function (panel) {
									// this.findById('EvnSectionEditForm').getForm().findField('EvnSection_setDate').focus(true);
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: '1. Установка случая движения',
							items: [
								{
									fieldLabel: 'Повторная подача',
									listeners: {
										'check': function (checkbox, value) {
											if (getRegionNick() != 'perm') {
												return false;
											}

											var base_form = this.findById('EvnSectionEditForm').getForm();

											var
												EvnSection_IndexRep = parseInt(base_form.findField('EvnSection_IndexRep').getValue()),
												EvnSection_IndexRepInReg = parseInt(base_form.findField('EvnSection_IndexRepInReg').getValue()),
												EvnSection_IsPaid = parseInt(base_form.findField('EvnSection_IsPaid').getValue());

											var diff = EvnSection_IndexRepInReg - EvnSection_IndexRep;

											if (EvnSection_IsPaid != 2 || EvnSection_IndexRepInReg == 0) {
												return false;
											}

											if (value == true) {
												if (diff == 1 || diff == 2) {
													EvnSection_IndexRep = EvnSection_IndexRep + 2;
												}
												else if (diff == 3) {
													EvnSection_IndexRep = EvnSection_IndexRep + 4;
												}
											}
											else if (value == false) {
												if (diff <= 0) {
													EvnSection_IndexRep = EvnSection_IndexRep - 2;
												}
											}

											base_form.findField('EvnSection_IndexRep').setValue(EvnSection_IndexRep);

											this.loadKSGKPGKOEF();
										}.createDelegate(this)
									},
									name: 'EvnSection_RepFlag',
									xtype: 'checkbox'
								},
								{
									border: false,
									layout: 'column',
									items: [
										{
											border: false,
											layout: 'form',
											items: [
												{
													allowBlank: false,
													fieldLabel: 'Дата поступления',
													format: 'd.m.Y',
													id: this.id + 'ESecEF_EvnSection_setDate',
													listeners: {
														'change': function (field, newValue, oldValue) {
															var base_form = this.findById('EvnSectionEditForm').getForm(),
																UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue(),
																isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'),
																lpu_section_id = base_form.findField('LpuSection_id').getValue(),
																med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue(),
																evn_section_dis_date = base_form.findField('EvnSection_disDate').getValue(),
																WithoutChildLpuSectionAge = false,
																Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');

															this.loadKSGKPGKOEF();
															this.checkMesOldUslugaComplexFields();
															this.setDiagFilterByDate();
															this.setPlanDisDT();

															this.changedDates = true;

															if (blockedDateAfterPersonDeath('personpanelid', 'ESecEF_PersonInformationFrame', field, newValue, oldValue)) return;

															base_form.findField('LpuSection_id').clearValue();
															base_form.findField('MedStaffFact_id').clearValue();

															this.recountKoikoDni();
															if (!newValue) {
																// статистику должны быть доступны все отделения/места работы
																var age = swGetPersonAge(Person_Birthday, getValidDT(getGlobalOptions().date, ''));
																if (age >= 18 && !isUfa) {
																	WithoutChildLpuSectionAge = true;
																}
																setLpuSectionGlobalStoreFilter({
																	// allowLowLevel: (getRegionNick() == 'kareliya' ? 'yes' : ''),
																	isStac: true,
																	WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
																});
																setMedStaffFactGlobalStoreFilter({
																	allowDuplacateMSF: true,
																	dateTo: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
																	EvnClass_SysNick: 'EvnSection',
																	isStac: true,
																	WithoutChildLpuSectionAge: WithoutChildLpuSectionAge/*,
																	isDoctor:true*/
																});
																base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
																base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
																//base_form.findField('MedStaffFact_id').loadData(getStoreRecords(swMedStaffFactGlobalStore));

															}
															else {
																base_form.findField('EvnSection_disDate').setMinValue(newValue);
																var age = swGetPersonAge(Person_Birthday, newValue);
																if (age >= 18 && !isUfa) {
																	WithoutChildLpuSectionAge = true;
																}
																setLpuSectionGlobalStoreFilter({
																	// allowLowLevel: (getRegionNick() == 'kareliya' ? 'yes' : ''),
																	isStac: true,
																	dateFrom: Ext.util.Format.date(newValue, 'd.m.Y'),
																	dateTo: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
																	WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
																});
																setMedStaffFactGlobalStoreFilter({
																	allowDuplacateMSF: true,
																	dateFrom: Ext.util.Format.date(newValue, 'd.m.Y'),
																	dateTo: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
																	EvnClass_SysNick: 'EvnSection',
																	isStac: true,
																	WithoutChildLpuSectionAge: WithoutChildLpuSectionAge/*,
																	isDoctor:true*/
																});
																base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
																base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
																this.setMKB();
															}

															if (base_form.findField('LpuSection_id').getStore().getById(lpu_section_id)) {
																base_form.findField('LpuSection_id').setValue(lpu_section_id);
																base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
															}
															else {
																base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), null);
															}

															if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
																base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
															}

															if (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['buryatiya', 'pskov'])) {
																if (base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date != Ext.util.Format.date(newValue, 'd.m.Y')) {
																	base_form.findField('UslugaComplex_id').setUslugaComplexDate(Ext.util.Format.date(newValue, 'd.m.Y'));

																	this.reloadUslugaComplexField(UslugaComplex_id);
																}
															}
															if (this.specificsPanel.isExpanded) {
																this.onSpecificsExpand(this.specificsPanel);
															}
															this.leaveTypeFilter();
															this.loadHTMedicalCareClassCombo();
															this.refreshPregnancyEvnPSFieldSet();
															this.setDiagEidAllowBlank();
															this.loadLpuSectionProfileDop();
															this.checkEvnSectionKSGGridIsVisible();
															this.refreshFieldsVisibility(['DeseaseBegTimeType_id', 'DeseaseType_id', 'TumorStage_id', 'EvnSection_BarthelIdx', 'DiagSetPhase_id']);
														}.createDelegate(this),
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																e.stopEvent();
																this.buttons[this.buttons.length - 1].focus();
															}
														}.createDelegate(this)
													},
													name: 'EvnSection_setDate',
													plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
													selectOnFocus: true,
													tabIndex: this.tabIndex + 1,
													width: 100,
													xtype: 'swdatefield'
												},
												{
													fieldLabel: 'Дата выписки',
													format: 'd.m.Y',
													id: this.id + 'ESecEF_EvnSection_disDate',
													listeners: {
														'change': function (field, newValue, oldValue) {
															if (blockedDateAfterPersonDeath('personpanelid', 'ESecEF_PersonInformationFrame', field, newValue, oldValue)) return;

															this.loadKSGKPGKOEF();
															this.checkMesOldUslugaComplexFields();
															this.loadMesCombo();
															this.setDiagFilterByDate();
															this.setPlanDisDT();

															var base_form = this.findById('EvnSectionEditForm').getForm();

															var
																evn_section_set_date = base_form.findField('EvnSection_setDate').getValue(),
																isUfa = (getRegionNick() == 'ufa'),
																lpu_section_id = base_form.findField('LpuSection_id').getValue(),
																med_staff_fact_did = base_form.findField('MedStaffFact_did').getValue(),
																med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue(),
																Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
																WithoutChildLpuSectionAge = false;


															base_form.findField('LpuSection_id').clearValue();
															base_form.findField('MedStaffFact_did').clearValue();
															base_form.findField('MedStaffFact_id').clearValue();

															if (!newValue) {
																setMedStaffFactGlobalStoreFilter({
																	allowDuplacateMSF: true,
																	isStac: true
																});
																base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

																var age = swGetPersonAge(Person_Birthday, evn_section_set_date);
																if (age >= 18 && !isUfa) {
																	WithoutChildLpuSectionAge = true;
																}
																setLpuSectionGlobalStoreFilter({
																	isStac: true,
																	WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
																});
																setMedStaffFactGlobalStoreFilter({
																	allowDuplacateMSF: true,
																	dateFrom: Ext.util.Format.date(evn_section_set_date, 'd.m.Y'),
																	EvnClass_SysNick: 'EvnSection',
																	isStac: true,
																	WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
																});
																base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
																base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
															}
															else {
																setMedStaffFactGlobalStoreFilter({
																	allowDuplacateMSF: true,
																	isStac: true,
																	onDate: Ext.util.Format.date(newValue, 'd.m.Y')
																});
																base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

																var age = swGetPersonAge(Person_Birthday, evn_section_set_date);
																if (age >= 18 && !isUfa) {
																	WithoutChildLpuSectionAge = true;
																}
																setLpuSectionGlobalStoreFilter({
																	isStac: true,
																	dateFrom: Ext.util.Format.date(evn_section_set_date, 'd.m.Y'),
																	dateTo: Ext.util.Format.date(newValue, 'd.m.Y'),
																	WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
																});
																setMedStaffFactGlobalStoreFilter({
																	allowDuplacateMSF: true,
																	dateFrom: Ext.util.Format.date(evn_section_set_date, 'd.m.Y'),
																	dateTo: Ext.util.Format.date(newValue, 'd.m.Y'),
																	EvnClass_SysNick: 'EvnSection',
																	isStac: true,
																	WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
																});
																base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
																base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
															}

															if (base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_did)) {
																base_form.findField('MedStaffFact_did').setValue(med_staff_fact_did);
															}

															if (base_form.findField('LpuSection_id').getStore().getById(lpu_section_id)) {
																base_form.findField('LpuSection_id').setValue(lpu_section_id);
																base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
															}
															else {
																base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), null);
															}

															if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
																base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
															}

															this.leaveTypeFilter();

															sw.Promed.EvnSection.calcFedResultDeseaseType({
																date: newValue,
																LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
																LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
																ResultDesease_Code: base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code'),
																fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid'),
																noSetField: parentWin.isProcessLoadForm
															});
															sw.Promed.EvnSection.calcFedLeaveType({
																date: newValue,
																LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
																LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
																LeaveCause_Code: base_form.findField('LeaveCause_id').getFieldValue('LeaveCause_Code'),
																fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
																fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid'),
																noSetField: parentWin.isProcessLoadForm
															});
															sw.Promed.EvnSection.filterFedResultDeseaseType({
																LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
																fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
																fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
															});
															sw.Promed.EvnSection.filterFedLeaveType({
																LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
																fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
																fieldLeaveType: base_form.findField('LeaveType_id'),
																fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
															});

															this.recountKoikoDni();
															this.loadHTMedicalCareClassCombo();
															this.loadLpuSectionProfileDop();
															this.checkEvnSectionKSGGridIsVisible();
															this.showRankinScale();
															this.showCardioFields();
															this.showSTField();

															base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'), base_form.findField('LeaveType_id').getValue());
															this.refreshFieldsVisibility(['RehabScale_vid', 'MedicalCareBudgType_id', 'DeseaseType_id', 'DiagSetPhase_id']);
														}.createDelegate(this),
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																e.stopEvent();
																this.buttons[this.buttons.length - 1].focus();
															}
														}.createDelegate(this)
													},
													name: 'EvnSection_disDate',
													plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
													selectOnFocus: true,
													tabIndex: this.tabIndex + 3,
													width: 100,
													xtype: 'swdatefield'
												}
											]
										},
										{
											border: false,
											labelWidth: 50,
											layout: 'form',
											items: [
												{
													allowBlank: false,
													fieldLabel: 'Время',
													listeners: {
														'change': function (field, newValue, oldValue) {
															this.changedDates = true;
															var base_form = this.findById('EvnSectionEditForm').getForm();
															base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
														}.createDelegate(this),
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.F4) {
																e.stopEvent();
																inp.onTriggerClick();
															}
														}
													},
													name: 'EvnSection_setTime',
													onTriggerClick: function () {
														var base_form = this.findById('EvnSectionEditForm').getForm();
														var time_field = base_form.findField('EvnSection_setTime');

														if (time_field.disabled) {
															return false;
														}

														setCurrentDateTime({
															callback: function () {
																base_form.findField('EvnSection_disDate').setMinValue(base_form.findField('EvnSection_setDate').getValue());
																base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
															}.createDelegate(this),
															dateField: base_form.findField('EvnSection_setDate'),
															loadMask: true,
															setDate: true,
															setDateMaxValue: true,
															setDateMinValue: false,
															setTime: true,
															timeField: time_field,
															windowId: this.id
														});
													}.createDelegate(this),
													plugins: [new Ext.ux.InputTextMask('99:99', true)],
													tabIndex: this.tabIndex + 2,
													validateOnBlur: false,
													width: 60,
													xtype: 'swtimefield'
												},
												{
													fieldLabel: 'Время',
													listeners: {
														'change': function (field, newValue, oldValue) {
															this.changedDates = true;
															var base_form = this.findById('EvnSectionEditForm').getForm();
														}.createDelegate(this),
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.F4) {
																e.stopEvent();
																inp.onTriggerClick();
															}
														}
													},
													name: 'EvnSection_disTime',
													onTriggerClick: function () {
														var base_form = this.findById('EvnSectionEditForm').getForm();
														var time_field = base_form.findField('EvnSection_disTime');

														if (time_field.disabled) {
															return false;
														}

														setCurrentDateTime({
															callback: function () {
																base_form.findField('EvnSection_disDate').fireEvent('change', base_form.findField('EvnSection_disDate'), base_form.findField('EvnSection_disDate').getValue());
															}.createDelegate(this),
															dateField: base_form.findField('EvnSection_disDate'),
															loadMask: true,
															setDate: true,
															setDateMaxValue: true,
															addMaxDateDays: this.addMaxDateDays,
															setDateMinValue: false,
															setTime: true,
															timeField: time_field,
															windowId: this.id
														});
													}.createDelegate(this),
													plugins: [new Ext.ux.InputTextMask('99:99', true)],
													tabIndex: this.tabIndex + 4,
													validateOnBlur: false,
													width: 60,
													xtype: 'swtimefield'
												}
											]
										},
										{
											border: false,
											labelWidth: 210,
											layout: 'form',
											items: [{
												xtype: 'swyesnocombo',
												tabIndex: this.tabIndex + 5,
												name: 'EvnSection_IsAdultEscort',
												hiddenName: 'EvnSection_IsAdultEscort',
												listeners: {
													'change': function (combo, newValue, oldValue) {
														var index = combo.getStore().findBy(function (rec) {
															return (rec.get(combo.valueField) == newValue);
														});
														combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
													},
													'select': function (combo, record, index) {
														if (getRegionNick() == 'kareliya') {
															that.loadKSGKPGKOEF(); // КСЛП зависит от данного поля
														}
														if (getRegionNick() == 'astra') {
															that.setIsMedReason();
														}
													}
												},
												allowBlank: true,
												width: 70,
												fieldLabel: 'Сопровождается взрослым'
											}, {
												xtype: 'swyesnocombo',
												tabIndex: this.tabIndex + 6,
												hiddenName: 'EvnSection_IsMedReason',
												allowBlank: true,
												value: 1,
												width: 70,
												fieldLabel: 'По медицинским показаниям'
											}]
										}
									]
								},
								{
									allowBlank: false,
									hiddenName: 'LpuSection_id',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var base_form = this.findById('EvnSectionEditForm').getForm();
											this.checkLpuUnitType();
											this.wardOnSexFilter();
											this.recountKoikoDni();

											var index = combo.getStore().findBy(function (rec) {
												return (rec.get('LpuSection_id') == newValue);
											});
											var record = combo.getStore().getAt(index);
											/*
											if (getRegionNick().inlist(['kareliya'])) {
												this.filterLpuSectionBedProfilesByLpuSection(newValue, 'LpuSectionBedProfile_id');
											}
											*/
											this.filterLpuSectionBedProfileLink(newValue, 'LpuSectionBedProfileLink_fedid');

											//ручная установка правильного значения профиля коек по умолчанию для Крыма
											if ( getRegionNick() == 'krym' && combo.getStore().getCount() > 0 && !Ext.isEmpty(newValue) && (!Ext.isEmpty(oldValue) || win.action == 'add') ) {
												var defaultLpuSectionBedProfile = combo.getStore().getById(newValue).data.LpuSectionBedProfile_id;
												var LpuSectionBedProfileLinkCombo = base_form.findField('LpuSectionBedProfileLink_fedid');
												if (LpuSectionBedProfileLinkCombo.getStore().getCount() > 0) {
													var ind = LpuSectionBedProfileLinkCombo.getStore().findBy(function (rec, index) {
														if (rec.data.LpuSectionBedProfile_id == defaultLpuSectionBedProfile)
															return index;
													});
													if (ind > -1) {
														LpuSectionBedProfileLinkCombo.setValue(LpuSectionBedProfileLinkCombo.getStore().getAt(ind).get(LpuSectionBedProfileLinkCombo.valueField));
														LpuSectionBedProfileLinkCombo.fireEvent('change', LpuSectionBedProfileLinkCombo, LpuSectionBedProfileLinkCombo.getValue());
													}
												}
											}

											if (getRegionNick() == 'buryatiya') {
												uslugacomplex_combo = base_form.findField('UslugaComplex_id');
												uslugacomplex_combo.setLpuSectionProfileByLpuSection_id(newValue);
												this.reloadUslugaComplexField(uslugacomplex_combo.getValue())
											}

											if (combo.getFieldValue('LpuSection_IsHTMedicalCare') == 2) {
												this.showHTMedicalCareClass = true;
												this.findById('HTMedicalCareClass').show();
												this.loadHTMedicalCareClassCombo();
											} else {
												this.showHTMedicalCareClass = false;
												this.findById('HTMedicalCareClass').hide();
												base_form.findField('HTMedicalCareClass_id').clearValue();
												base_form.findField('HTMedicalCareClass_id').fireEvent('change', base_form.findField('HTMedicalCareClass_id'), base_form.findField('HTMedicalCareClass_id').getValue());
											}

											sw.Promed.EvnSection.calcFedResultDeseaseType({
												date: base_form.findField('EvnSection_disDate').getValue(),
												LpuUnitType_SysNick: (record && record.get('LpuUnitType_SysNick')) || null,
												LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
												ResultDesease_Code: base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code'),
												fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid'),
												noSetField: parentWin.isProcessLoadForm
											});
											sw.Promed.EvnSection.calcFedLeaveType({
												date: base_form.findField('EvnSection_disDate').getValue(),
												LpuUnitType_SysNick: (record && record.get('LpuUnitType_SysNick')) || null,
												LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
												LeaveCause_Code: base_form.findField('LeaveCause_id').getFieldValue('LeaveCause_Code'),
												fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
												fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid'),
												noSetField: parentWin.isProcessLoadForm
											});
											sw.Promed.EvnSection.filterFedResultDeseaseType({
												LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
												fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
												fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
											})
											sw.Promed.EvnSection.filterFedLeaveType({
												LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
												fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
												fieldLeaveType: base_form.findField('LeaveType_id'),
												fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
											});

											this.refreshFieldsVisibility(['DeseaseBegTimeType_id', 'MedicalCareBudgType_id']);
											this.loadRoomList();
											this.setBedListAllowBlank();
										}.createDelegate(this)
									},
									id: this.id + '_LpuSectionCombo',
									linkedElements: [
										'ESecEF_LpuSectionWardCombo',
										this.id + '_MedStaffFactCombo'
									],
									tabIndex: this.tabIndex + 7,
									width: 500,
									xtype: 'swlpusectionglobalcombo'
								}, {
									border: false,
									//labelWidth: 50,
									layout: 'form',
									hidden: !(getRegionNick() == 'pskov'),
									items: [{
										fieldLabel: 'Вид транспортировки',
										hiddenName: 'LpuSectionTransType_id',
										id: this.id + '_LpuSectionTransTypeCombo',
										listWidth: 650,
										tabIndex: this.tabIndex + 8,
										width: 500,
										comboSubject: 'LpuSectionTransType',
										xtype: 'swcommonsprcombo'
									}]
								},
								{
									xtype: 'swyesnocombo',
									tabIndex: this.tabIndex + 9,
									name: 'EvnSection_IsMeal',
									hiddenName: 'EvnSection_IsMeal',
									allowBlank: true,
									value: 1,
									width: 70,
									fieldLabel: 'С питанием'
								}, {
									border: false,
									layout: 'form',
									items: [{
										allowBlank: !(getRegionNick().inlist(['astra', 'kz', 'kareliya'])),
										fieldLabel: 'Профиль',
										hiddenName: 'LpuSectionProfile_id',
										listeners: {
											'change': function (combo, newValue, oldValue) {
												that.onLpuSectionProfileChange();
											}.createDelegate(this)
										},
										listWidth: 600,
										tabIndex: this.tabIndex + 10,
										width: 500,
										xtype: 'swlpusectionprofiledopremotecombo'
									}]
								},
								this.dataViewLpuSectionBedProfileHistory = new Ext.DataView({
									//id: "dataViewLpuSectionBedProfileHistory",
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'LpuSectionBedProfileHistory_id'

										}, [
											{name: 'LpuSectionBedProfile_Text', mapping: 'LpuSectionBedProfile_Text'}
										]),
										url: '/?c=EvnSection&m=loadLpuSectionBedProfileHistory'

									}),

									itemSelector: 'tr',
									autoHeight: true,
									style: "margin-left:185px",
									tpl: new Ext.XTemplate(
										'<table><tpl for="."><tr>',
										'<td style="width:500px;max-width:500px;overflow:hidden;white-space:nowrap">{LpuSectionBedProfile_Text}</td>',
										'</tr></tpl></table>'
									)
									,
									emptyText: ''
								}),
								{
									border: false,
									hidden: false, //!getRegionNick().inlist(['kaluga', 'krym', 'pskov']),
									layout: 'form',
									xtype: 'panel',
									items: [{
										allowBlank: !getRegionNick().inlist(['kareliya','khak']),
										hiddenName: 'LpuSectionBedProfileLink_fedid',
										fieldLabel: 'Профиль коек',
										listeners: {
											'change': function (combo, newValue, oldValue) {
												var base_form = Ext.getCmp('EvnSectionEditForm').getForm();
												var LpuSectionBedProfile_id = combo.getFieldValue('LpuSectionBedProfile_id');
												base_form.findField('LpuSectionBedProfile_id').setValue(LpuSectionBedProfile_id);
												if (getRegionNick().inlist(['kaluga', 'kareliya'])) {
													that.loadKSGKPGKOEF();
												}
												that.setPlanDisDT();
											}
										},
										//id:'ESecEF_LpuSectionBedProfileCombo',
										id: 'ESecEF_LpuSectionBedProfileLinkCombo',
										tabIndex: this.tabIndex + 11,
										width: 500,
										//xtype:'swlpusectionbedprofilecombo'
										xtype: 'swlpusectionbedprofilelinkcombo'
									}]
								},
								this.dataViewLpuSectionWardHistory = new Ext.DataView({
									//id: "dataViewLpuSectionWardHistory",
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'LpuSectionWardHistory_id'

										}, [
											{name: 'LpuSectionWard_Text', mapping: 'LpuSectionWard_Text'}
										]),
										url: '/?c=EvnSection&m=loadLpuSectionWardHistory'

									}),

									itemSelector: 'tr',
									autoHeight: true,
									style: "margin-left:185px",
									tpl: new Ext.XTemplate(
										'<table><tpl for="."><tr>',
										'<td style="width:500px;max-width:500px;overflow:hidden;white-space:nowrap">{LpuSectionWard_Text}</td>',
										'</tr></tpl></table>'
									)
									,
									emptyText: ''
								}),
								{
									fieldLabel: 'Палата',
									allowBlank: true,
									hiddenName: 'LpuSectionWard_id',
									id: 'ESecEF_LpuSectionWardCombo',
									parentElementId: this.id + '_LpuSectionCombo',
									tabIndex: this.tabIndex + 12,
									width: 500,
									xtype: 'swlpusectionwardglobalcombo'
								}, {
									hiddenName: 'GetRoom_id',
									fieldLabel: 'Палата',
									xtype: 'swbaselocalcombo',
									valueField: 'GetRoom_id',
									codeField: 'Number',
									displayField: 'NameSetRoomRuFull',
									store: new Ext.data.JsonStore({
										autoLoad: false,
										url: '/?c=EvnSection&m=getRoomList',
										fields: [
											{name: 'GetRoom_id', type: 'int'},
											{name: 'Number', type: 'string'},
											{name: 'NameSetRoomRuFull', type: 'string'},
										],
										key: 'GetRoom_id',
										sortInfo: {
											field: 'Number'
										}
									}),
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<font color="red">{Number}</font>&nbsp;{NameSetRoomRuFull}',
										'</div></tpl>'
									),
									width: 500,
									listWidth: 800,
									listeners: {
										'change': function(field, newValue, oldValue) {
											this.loadBedList(true);
										}.createDelegate(this)
									}
								}, {
									hiddenName: 'GetBed_id',
									fieldLabel: 'Койка',
									xtype: 'swbaselocalcombo',
									valueField: 'GetBed_id',
									codeField: 'BedProfile',
									displayField: 'BedProfileRuFull',
									store: new Ext.data.JsonStore({
										autoLoad: false,
										url: '/?c=EvnSection&m=getBedList',
										fields: [
											{name: 'GetBed_id', type: 'int'},
											{name: 'BedProfile', type: 'int'},
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
									allowDecimals: false,
									allowNegative: false,
									fieldLabel: 'Внутр. № карты',
									hiddenName: 'EvnSection_insideNumCard',
									name: 'EvnSection_insideNumCard',
									maskRe: /[0-9a-zа-яё./-]/i,
									tabIndex: this.tabIndex + 13,
									width: 80,
									xtype: 'textfield'
								}, {
									allowBlank: getRegionNick() == 'kz',
									fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
									tabIndex: this.tabIndex + 14,
									typeCode: 'int',
									useCommonFilter: true,
									width: 300,
									xtype: 'swpaytypecombo',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var
												base_form = this.findById('EvnSectionEditForm').getForm(),
												PayType_SysNick;

											var index = combo.getStore().findBy(function(rec) {
												return (rec.get('PayType_id') == newValue);
											});

											if ( index >= 0 ) {
												PayType_SysNick = combo.getStore().getAt(index).get('PayType_SysNick');
											}

											if (getRegionNick() == 'buryatiya') {
												base_form.findField('UslugaComplex_id').setAllowBlank(PayType_SysNick != 'oms');
											}

											if (this.showHTMedicalCareClass) {
												base_form.findField('HTMedicalCareClass_id').clearValue();
												this.loadHTMedicalCareClassCombo();
											}

											if (getRegionNick() == 'perm') {
												base_form.findField('LpuSectionBedProfileLink_fedid').setAllowBlank(PayType_SysNick != 'oms' && PayType_SysNick != 'ovd');
											}
											else if (getRegionNick().inlist(['astra', 'ufa', 'krym', 'pskov', 'buryatiya', 'adygeya'])){
												base_form.findField('LpuSectionBedProfileLink_fedid').setAllowBlank(PayType_SysNick != 'oms');
											}
											else if(getRegionNick() == 'penza'){
												base_form.findField('LpuSectionBedProfileLink_fedid').setAllowBlank(false);
											}

											this.refreshFieldsVisibility(['MedicalCareBudgType_id']);
											this.setBedListAllowBlank();

											if (getRegionNick().inlist(['vologda', 'adygeya'])) {
												this.loadKSGKPGKOEF();
											}
										}.createDelegate(this)
									}
								},
								{
									autoLoad: false,
									comboSubject: 'TariffClass',
									fieldLabel: 'Вид тарифа',
									hiddenName: 'TariffClass_id',
									lastQuery: '',
									tabIndex: this.tabIndex + 15,
									typeCode: 'int',
									width: 300,
									xtype: 'swtariffclasscombo'
								},
								new Ext.DataView({
									id: "dataViewDoctorHistory",
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'EvnDiagPS_id'

										}, [
											{name: 'text', mapping: 'text'}
										]),
										url: '/?c=EvnSection&m=loadDoctorHistoryList'

									}),

									itemSelector: 'tr',
									autoHeight: true,
									style: "margin-left:185px",
									tpl: new Ext.XTemplate(
										'<table><tpl for="."><tr>',
										'<td style="width:500px;max-width:500px;overflow:hidden;white-space:nowrap">{text}</td>',
										'</tr></tpl></table>'
									)
									,
									emptyText: ''
								}),
								{
									allowBlank: false,
									dateFieldId: this.id + 'ESecEF_EvnSection_setDate',
									enableOutOfDateValidation: true,
									fieldLabel: 'Врач',
									hiddenName: 'MedStaffFact_id',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var base_form = this.findById('EvnSectionEditForm').getForm();

											this.checkLpuUnitType();
											this.wardOnSexFilter();

											var LpuSection_id;

											var index = combo.getStore().findBy(function (rec) {
												return (rec.get('MedStaffFact_id') == newValue);
											});

											if (index >= 0) {
												LpuSection_id = combo.getStore().getAt(index).get('LpuSection_id');
											}

											if (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['kareliya'])) {

												this.filterLpuSectionBedProfilesByLpuSection(LpuSection_id, 'LpuSectionBedProfile_id');
											}

											if (getGlobalOptions().region && getGlobalOptions().region.nick == 'buryatiya') {

												uslugacomplex_combo = base_form.findField('UslugaComplex_id');
												uslugacomplex_combo.setLpuSectionProfileByLpuSection_id(LpuSection_id);
												this.reloadUslugaComplexField(uslugacomplex_combo.getValue());
											}

											var lpu_section_combo = this.findById(this.id + '_LpuSectionCombo');
											if (lpu_section_combo.getFieldValue('LpuSection_IsHTMedicalCare') == 2) {
												this.showHTMedicalCareClass = true;
												this.findById('HTMedicalCareClass').show();
												this.loadHTMedicalCareClassCombo();
											} else {
												this.showHTMedicalCareClass = false;
												this.findById('HTMedicalCareClass').hide();
												base_form.findField('HTMedicalCareClass_id').clearValue();
												base_form.findField('HTMedicalCareClass_id').fireEvent('change', base_form.findField('HTMedicalCareClass_id'), base_form.findField('HTMedicalCareClass_id').getValue());
											}
											this.recalcBirthSpecStacDefaults();
										}.createDelegate(this)
									},
									id: this.id + '_MedStaffFactCombo',
									listWidth: 650,
									parentElementId: this.id + '_LpuSectionCombo',
									tabIndex: this.tabIndex + 16,
									width: 500,
									xtype: 'swmedstafffactglobalcombo'
								},


								new Ext.DataView({
									id: "dataViewDiag",
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'EvnDiagPS_id'

										}, [
											{name: 'EvnDiagPS_id', mapping: 'EvnDiagPS_id', key: true},
											{name: 'EvnDiagPS_pid', mapping: 'EvnDiagPS_pid'},
											{name: 'Person_id', mapping: 'Person_id'},
											{name: 'PersonEvn_id', mapping: 'PersonEvn_id'},
											{name: 'Server_id', mapping: 'Server_id'},
											{name: 'Diag_id', mapping: 'Diag_id'},
											{name: 'DiagSetPhase_id', mapping: 'DiagSetPhase_id'},
											{name: 'EvnDiagPS_PhaseDescr', mapping: 'EvnDiagPS_PhaseDescr'},
											{name: 'DiagSetClass_id', mapping: 'DiagSetClass_id'},
											{name: 'EvnDiagPS_setDate', mapping: 'EvnDiagPS_setDate'},
											{name: 'DiagSetClass_Name', mapping: 'DiagSetClass_Name'},
											{name: 'Diag_Name', mapping: 'Diag_Name'},
											{name: 'Diag_Code', mapping: 'Diag_Code'},
											{name: 'RecordStatus_Code', mapping: 'RecordStatus_Code'}

										]),
										url: '/?c=EvnDiag&m=loadEvnDiagPSGrid'

									}),

									itemSelector: 'tr',
									autoHeight: true,
									style: "margin-left:185px",
									tpl: new Ext.XTemplate(
										'<table><tpl for="."><tr>',
										'<td>{Diag_Code}</td>\n\
										<td style="width:335px;max-width:335px;overflow:hidden;white-space:nowrap">:{Diag_Name}</td>\n\
										<td> - {EvnDiagPS_setDate}</td>\n\
										<td><div onclick="Ext.getCmp(\'EvnSectionEditWindow\').deleteClinDiag(\'EvnDiagPS\',{EvnDiagPS_id})" class="delete16" style="background-repeat:no-repeat; background-size:23%;cursor:pointer;"><span style="padding-left:17px;">Удалить</span></div></td>',
										'</tr></tpl></table>'
									)
									,
									emptyText: ''
								}),
								{
									width: 800,
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										border: false,
										items: [
											this.diagPanel, {
												fieldLabel: 'Уточняющий диагноз',
												hiddenName: 'Diag_cid',
												id: this.id + '_DiagComboC',
												width: 500,
												xtype: 'swdiagcombo',
												hidden: getRegionNick() != 'kz',
												hideLabel: getRegionNick() != 'kz',
												onChange: function (combo, value) {
													//this.getFinanceSource();
												}.createDelegate(this)
											}, {
												xtype: 'swcommonsprcombo',
												comboSubject: 'DeseaseBegTimeType',
												hiddenName: 'DeseaseBegTimeType_id',
												fieldLabel: 'Время с начала заболевания',
												width: 300
											}, {
												xtype: 'swdeseasetypecombo',
												comboSubject: 'DeseaseType',
												hiddenName: 'DeseaseType_id',
												fieldLabel: 'Характер',
												allowSysNick: true,
												listeners: {
													'change': function (combo, newValue, oldValue) {
														this.refreshFieldsVisibility(['TumorStage_id']);
													}.createDelegate(this)
												},
												width: 300
											},
											this.DrugTherapySchemePanel, {
												xtype: 'swcommonsprcombo',
												comboSubject: 'RehabScale',
												listeners: {
													'change': function (field, newValue, oldValue) {
														this.savedMesTariff_id = null; // сохранённый коэфф уже не учитываем, выставляем КСГ автоматически
														this.loadKSGKPGKOEF();
													}.createDelegate(this)
												},
												hiddenName: 'RehabScale_id',
												fieldLabel: 'Оценка состояния по ШРМ',
												width: 500
											}, {
												xtype: 'swcommonsprcombo',
												comboSubject: 'RehabScale',
												hidden: true,
												listeners: {
													'change': function (field, newValue, oldValue) {
														//this.loadKSGKPGKOEF();
													}.createDelegate(this)
												},
												hiddenName: 'RehabScale_vid',
												fieldLabel: 'Оценка состояния по ШРМ при выписке',
												width: 500
											}, {
												xtype: 'numberfield',
												name: 'EvnSection_SofaScalePoints',
												listeners: {
													'change': function (field, newValue, oldValue) {
														this.loadKSGKPGKOEF();
													}.createDelegate(this)
												},
												fieldLabel: 'Оценка по шкале органной недостаточности (SOFA, pSOFA)',
												allowNegative: false,
												allowDecimals: false,
												minValue: 0,
												maxValue: 24,
												width: 80
											}, {
												fieldLabel: langs('Стадия выявленного ЗНО'),
												width: getRegionNick().inlist(['penza']) ? 500 : 300,
												hiddenName: 'TumorStage_id',
												xtype: 'swtumorstagenewcombo',
												loadParams: getRegionNumber().inlist([58, 66, 101]) ? {mode: 1} : {mode: 0} // только свой регион / + нулловый рег
											}, {
												fieldLabel: langs('Подозрение на ЗНО'),
												id: 'ESecEF_EvnSection_IsZNOCheckbox',
												xtype: 'checkbox',
												listeners: {
													'check': function (checkbox, value) {
														if (value == true) {
															Ext.getCmp('ESecEF_Diag_spid').showContainer();
															Ext.getCmp('ESecEF_Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm', 'msk' ]));
														} else {
															Ext.getCmp('ESecEF_Diag_spid').setValue('');
															Ext.getCmp('ESecEF_Diag_spid').hideContainer();
															Ext.getCmp('ESecEF_Diag_spid').setAllowBlank(true);
														}
													}
												}
											}, {
												fieldLabel: 'Подозрение на диагноз',
												hiddenName: 'Diag_spid',
												id: 'ESecEF_Diag_spid',
												additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
												baseFilterFn: function (rec) {
													if (typeof rec.get == 'function') {
														return (rec.get('Diag_Code').substr(0, 1) == 'C' || rec.get('Diag_Code').substr(0, 2) == 'D0');
													} else if (rec.attributes && rec.attributes.Diag_Code) {
														return (rec.attributes.Diag_Code.substr(0, 1) == 'C' || rec.attributes.Diag_Code.substr(0, 2) == 'D0');
													} else {
														return true;
													}
												},
												onChange: function() {
													win.setDiagSpidComboDisabled();
												},
												width: 500,
												xtype: 'swdiagcombo'
											}, {
												xtype: 'swcommonsprcombo',
												comboSubject: 'PainIntensity',
												hiddenName: 'PainIntensity_id',
												fieldLabel: 'Интенсивность боли',
												width: 500
											}, {
												xtype: 'swcommonsprcombo',
												comboSubject: 'MesDop',
												hiddenName: 'MesDop_ids',
												fieldLabel: 'Дополнительный критерий определения КСГ',
												listeners: {
													'change': function (field, newValue, oldValue) {
														this.loadKSGKPGKOEF();
													}.createDelegate(this)
												},
												tpl: new Ext.XTemplate(
													'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
													'<td style="padding: 2px; width: 10%;">Код</td>',
													'<td style="padding: 2px; width: 90%;">Наименование</td></tr>',
													'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
													'<td style="padding: 2px;">{MesDop_Code}&nbsp;</td>',
													'<td style="padding: 2px;">{MesDop_Name}&nbsp;</td>',
													'</tr></tpl>',
													'</table>'
												),
												width: 500
											}, {
												border: false,
												hidden: (getRegionNick().inlist(['kz', 'ufa'])),
												layout: 'column',
												items: [
													{
														border: false,
														hidden: (getRegionNick().inlist(['kz', 'ufa'])),
														layout: 'form',
														items: [{
															checkAccessRights: true,
															MKB: null,
															fieldLabel: 'Внешняя причина',
															hiddenName: 'Diag_eid',
															registryType: 'ExternalCause',
															baseFilterFn: function (rec) {
																if (typeof rec.get == 'function') {
																	return (rec.get('Diag_Code').search(new RegExp("^[VWXY]", "i")) >= 0);
																} else {
																	return true;
																}
															},
															width: 500,
															xtype: 'swdiagcombo'
														}]
													},
													{
														border: false,
														hidden: (getRegionNick().inlist(['kz', 'ufa'])),
														layout: 'form',
														items: [{
															text: '=',
															tooltip: 'Скопировать из приемного отделения',
															handler: function () {
																var base_form = this.findById('EvnSectionEditForm').getForm();
																Ext.Ajax.request({
																	url: '/?c=EvnSection&m=getPriemDiag',
																	params: {
																		EvnPS_id: base_form.findField('EvnSection_pid').getValue()
																	},
																	callback: function (options, success, response) {
																		if (success) {
																			var response_obj = Ext.util.JSON.decode(response.responseText);
																			if (!Ext.isEmpty(response_obj.Diag_id)) {
																				if (base_form.findField('Diag_eid').getStore().getById(response_obj.Diag_id)) {
																					base_form.findField('Diag_eid').setValue(response_obj.Diag_id);
																				} else {
																					base_form.findField('Diag_eid').getStore().load({
																						params: {where: "where Diag_Code like 'X%' or Diag_Code like 'V%' or Diag_Code like 'W%' or Diag_Code like 'Y%'"},
																						callback: function () {
																							base_form.findField('Diag_eid').setValue(response_obj.Diag_id);
																						}
																					});
																				}
																			}
																		}
																	}
																});
															}.createDelegate(this),
															id: 'copyExternalCauseBtn',
															xtype: 'button'
														}]
													}
												]
											}
											, {
												border: false,
												layout: 'column',
												id: 'HTMedicalCareClass',
												//hidden: true,
												items: [{
													border: false,
													layout: 'form',
													items: [{
														fieldLabel: 'Метод высокотехнологичной медицинской помощи',
														hiddenName: 'HTMedicalCareClass_id',
														listeners: {
															'change': function (combo, newValue, oldValue) {
																var index = combo.getStore().findBy(function (rec) {
																	return (rec.get('HTMedicalCareClass_id') == newValue);
																});
																if (getRegionNick() == 'ufa') {
																	this.loadKSGKPGKOEF(); // КСГ зависит от HTMedicalCareClass_id только в УФе.
																}
																combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
															}.createDelegate(this),
															'select': function (combo, record, idx) {
																var base_form = this.findById('EvnSectionEditForm').getForm();

																/*if ( getRegionNick() == 'pskov' ) {
																	if ( typeof record == 'object' && !Ext.isEmpty(record.get('HTMedicalCareClass_id')) ) {
																		base_form.findField('UslugaComplex_id').setAllowBlank(true);
																		base_form.findField('UslugaComplex_id').clearValue();
																		base_form.findField('UslugaComplex_id').disable();
																	}
																	else {
																		if (base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick') != 'stac') {
																			base_form.findField('UslugaComplex_id').setAllowBlank(false);
																		}

																		if ( this.action != 'view' ) {
																			base_form.findField('UslugaComplex_id').enable();
																		}
																	}
																}*/

																this.refreshFieldsVisibility(['MedicalCareBudgType_id']);
															}.createDelegate(this)
														},
														tabIndex: this.tabIndex + 18,
														width: 500,
														xtype: 'swhtmedicalcareclasscombo'
													}]
												}]
											}, {
												fieldLabel: getRegionNick().inlist(['penza']) ? 'Значение по шкале Рэнкина' : 'Значение по шкале Рэнкина при поступлении',
												width: 500,
												comboSubject: 'RankinScale',
												hiddenName: 'RankinScale_id',
												tabIndex: this.tabIndex + 19,
												xtype: 'swcommonsprcombo'
											}, {
												allowDecimals: false,
												allowNegative: false,
												minValue: 0,
												maxValue: 39,
												width: 80,
												autoCreate: {tag: "input", maxLength: 2, autocomplete: "off"},
												fieldLabel: getRegionNick() == 'ufa' ? 'Значение шкалы инсульта Национального института здоровья (NIHSS) при госпитализации' : 'Значение шкалы инсульта Национального института здоровья',
												name: 'EvnSection_InsultScale',
												tabIndex: this.tabIndex + 20,
												xtype: 'numberfield'
											}, {
												allowDecimals: false,
												allowNegative: false,
												minValue: 0,
												maxValue: 39,
												width: 80,
												autoCreate: {tag: "input", maxLength: 2, autocomplete: "off"},
												fieldLabel: 'Значение шкалы инсульта Национального института здоровья после проведения ТЛТ',
												name: 'EvnSection_NIHSSAfterTLT',
												tabIndex: this.tabIndex + 21,
												xtype: 'numberfield'
											}, {
												allowDecimals: false,
												allowNegative: false,
												minValue: 0,
												maxValue: 39,
												width: 80,
												autoCreate: {tag: "input", maxLength: 2, autocomplete: "off"},
												fieldLabel: 'Значение шкалы инсульта Национального института здоровья при выписке',
												name: 'EvnSection_NIHSSLeave',
												xtype: 'numberfield'
											}, {
												fieldLabel: 'Значение по шкале Рэнкина при выписке',
												width: 500,
												comboSubject: 'RankinScale',
												hiddenName: 'RankinScale_sid',
												xtype: 'swcommonsprcombo'
											}, {
												border: false,
												layout: 'column',
												items: [{
													border: false,
													layout: 'form',
													items: [{
														border: false,
														//labelWidth: 50,
														layout: 'form',
														hidden: (getRegionNick().inlist(['buryatiya', 'pskov', 'kz', 'adygeya'])),
														items: [{
															// allowBlank: false,
															beforeBlur: function () {
																// медитируем
																return true;
															},
															// disabled: true,
															displayField: 'Mes_Code',
															editable: true,
															enableKeyEvents: true,
															fieldLabel: getMESAlias(),
															forceSelection: false,
															hiddenName: 'Mes_id',
															listeners: {
																'change': function (combo, newValue, oldValue) {
																	var base_form = this.findById('EvnSectionEditForm').getForm();

																	var record = combo.getStore().getById(newValue);

																	if (record) {
																		if (record.get('Mes_Code')[0] && record.get('Mes_Code')[0] == 9) {
																			if (this.action != 'view') {
																				base_form.findField('Mes2_id').enable();
																			}
																		} else {
																			base_form.findField('Mes2_id').clearValue();
																			base_form.findField('Mes2_id').disable();
																		}
																		if (getRegionNick() != 'pskov') {
																			base_form.findField('EvnSection_KoikoDniNorm').setValue(record.get('Mes_KoikoDni'))
																			base_form.findField('EvnSection_KoikoDniNorm').setRawValue(record.get('Mes_KoikoDni'));
																		}
																	}
																	else {
																		if (getRegionNick() != 'pskov') {
																			base_form.findField('EvnSection_KoikoDniNorm').setValue('');
																			base_form.findField('EvnSection_KoikoDniNorm').setRawValue('');
																		}
																		base_form.findField('Mes2_id').clearValue();
																		base_form.findField('Mes2_id').disable();
																	}
																}.createDelegate(this),
																'keydown': function (inp, e) {
																	if (e.getKey() == Ext.EventObject.TAB) {
																		if (e.shiftKey == false) {
																			var base_form = this.findById('EvnSectionEditForm').getForm();

																			e.stopEvent();

																			if (!this.findById(that.id + 'ESecEF_EvnLeavePanel').collapsed && !base_form.findField('LeaveType_id').disabled) {
																				base_form.findField('LeaveType_id').focus();
																			}
																			else if (!this.findById('ESecEF_EvnDiagPSPanel').collapsed && this.findById('ESecEF_EvnDiagPSGrid').getStore().getCount() > 0) {
																				this.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
																				this.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
																			}
																			else if (!this.findById('ESecEF_EvnUslugaPanel').collapsed && this.findById('ESecEF_EvnUslugaPanel').getStore().getCount() > 0) {
																				this.findById('ESecEF_EvnUslugaPanel').getView().focusRow(0);
																				this.findById('ESecEF_EvnUslugaPanel').getSelectionModel().selectFirstRow();
																			}
																			else if (/*getRegionNick().inlist([ 'ufa' ]) &&*/ !this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
																				this.findById('ESecEF_EvnSectionNarrowBedGrid').getView().focusRow(0);
																				this.findById('ESecEF_EvnSectionNarrowBedGrid').getSelectionModel().selectFirstRow();
																			}
																			else if (!this.specificsPanel.collapsed) {
																				this.tryFocusOnSpecificsTree();
																			}
																			else if (this.action != 'view') {
																				this.buttons[0].focus();
																			}
																			else {
																				this.buttons[this.buttons.length - 1].focus();
																			}
																		}
																	}
																}.createDelegate(this)
															},
															mode: 'local',
															resizable: true,
															selectOnFocus: true,
															store: new Ext.data.Store({
																autoLoad: false,
																reader: new Ext.data.JsonReader({
																	id: 'Mes_id'
																}, [
																	{name: 'Mes_id', mapping: 'Mes_id'},
																	{name: 'Mes_Code', mapping: 'Mes_Code'},
																	{name: 'Mes_KoikoDni', mapping: 'Mes_KoikoDni'},
																	{
																		name: 'MedicalCareKind_Name',
																		mapping: 'MedicalCareKind_Name'
																	},
																	{
																		name: 'MedicalCareKind_id',
																		mapping: 'MedicalCareKind_id'
																	},
																	{
																		name: 'MesAgeGroup_Name',
																		mapping: 'MesAgeGroup_Name'
																	},
																	{
																		name: 'MesNewUslovie',
																		mapping: 'MesNewUslovie',
																		type: 'int'
																	},
																	{
																		name: 'MesOperType_Name',
																		mapping: 'MesOperType_Name'
																	}
																]),
																url: '/?c=EvnSection&m=loadMesList'
															}),
															tabIndex: this.tabIndex + 22,
															tpl: mesTemplate,
															triggerAction: 'all',
															valueField: 'Mes_id',
															width: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') ? 222 : 500,
															xtype: 'combo'
														}]
													}]
												},

													{
														border: false,
														labelWidth: 50,
														layout: 'form',
														hidden: (getRegionNick().inlist(['buryatiya', 'pskov']) || getRegionNick() != 'ufa'),
														items: [{
															displayField: 'Mes2_Code',
															editable: true,
															enableKeyEvents: true,
															fieldLabel: getMESAlias() + '2',
															forceSelection: false,
															disabled: true,
															hiddenName: 'Mes2_id',
															mode: 'local',
															resizable: true,
															selectOnFocus: true,
															store: new Ext.data.Store({
																autoLoad: false,
																reader: new Ext.data.JsonReader({
																	id: 'Mes2_id'
																}, [
																	{name: 'Mes2_id', mapping: 'Mes2_id'},
																	{name: 'Mes2_Code', mapping: 'Mes2_Code'},
																	{name: 'Mes2_KoikoDni', mapping: 'Mes2_KoikoDni'}
																]),
																url: '/?c=EvnSection&m=loadMes2List'
															}),
															tabIndex: this.tabIndex + 23,
															tpl: new Ext.XTemplate(
																'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
																'<td style="padding: 2px; width: 50%;">Код</td>',
																'<td style="padding: 2px; width: 50%;">Нормативный срок</td></tr>',
																'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
																'<td style="padding: 2px;">{Mes2_Code}&nbsp;</td>',
																'<td style="padding: 2px;">{Mes2_KoikoDni}&nbsp;</td>',
																'</tr></tpl>',
																'</table>'
															),
															triggerAction: 'all',
															valueField: 'Mes2_id',
															width: 222,
															xtype: 'combo'
														}]
													}]
											}], width: 716

									}, {
										layout: 'form',
										border: false,
										items: {
											
											text: '+',
											tooltip: (getRegionNick().inlist(['msk', 'ufa']) ? 'Изменить основной диагноз. Предыдущий установленный диагноз сохранится в истории изменений' : 'Добавить основной диагноз'),
											handler: function () {
												this.openEvnDiagPSEditWindow2('add', 'sect');
											}.createDelegate(this),
											id: 'addDiag',
											xtype: 'button'
										}, width: 40

									}]
								},
								{
									border: false,
									layout: 'form',
									id: that.id + '_UslugaComplex',
									hidden: !(getRegionNick().inlist(['buryatiya'])),
									items: [{
										allowBlank: !(getRegionNick().inlist(['buryatiya'])),
										fieldLabel: (getRegionNick() === 'buryatiya' ? 'Профильная услуга' : 'Услуга лечения'),
										hiddenName: 'UslugaComplex_id',
										to: 'EvnSection',
										listWidth: 600,
										tabIndex: this.tabIndex + 24,
										width: 500,
										xtype: 'swuslugacomplexnewcombo'
									}]
								},
								{
									border: false,
									//labelWidth: 50,
									layout: 'form',
									hidden: !(getRegionNick().inlist(['buryatiya', 'pskov'])),
									items: [{
										disabled: true,
										fieldLabel: 'Срок госпитализации',
										name: 'EvnSection_KoikoDniInterval',
										tabIndex: this.tabIndex + 25,
										width: 80,
										xtype: 'textfield'
									}]
								},
								{
									border: false,
									//labelWidth: 50,
									layout: 'form',
									hidden: (getRegionNick().inlist(['buryatiya', 'pskov', 'kz'])),
									items: [{
										disabled: true,
										fieldLabel: 'Норматив',
										name: 'EvnSection_KoikoDniNorm',
										tabIndex: this.tabIndex + 26,
										width: 80,
										xtype: 'textfield'
									}]
								},
								{
									border: false,
									layout: 'form',
									hidden: (!getRegionNick().inlist([ 'vologda' ])),
									items: [{
										allowDecimals: false,
										allowNegative: false,
										enableKeyEvents: true,
										disabled: false,
										fieldLabel: 'Отсутствовал (дней)',
										listeners: {
											'change': function(field, newValue, oldValue) {
												this.recountKoikoDni();
											}.createDelegate(this)
										},
										minValue: 0,
										name: 'EvnSection_Absence',
										tabIndex: this.tabIndex + 26.1,
										width: 80,
										xtype: 'numberfield'
									}]
								},
								{
									border: false,
									//labelWidth: 50,
									layout: 'form',
									hidden: (getRegionNick().inlist(['buryatiya', 'kz'])),
									items: [{
										disabled: true,
										fieldLabel: 'Факт',
										name: 'EvnSection_KoikoDni',
										tabIndex: this.tabIndex + 27,
										width: 80,
										xtype: 'textfield'
									}]
								},
								{
									border: false,
									layout: 'form',
									id: that.id + '_KSGKPGFields',
									hidden: !(getRegionNick().inlist(['ufa', 'kareliya', 'astra', 'perm', 'buryatiya', 'pskov', 'penza', 'kaluga', 'kz', 'krasnoyarsk', 'krym', 'khak', 'vologda', 'yaroslavl', 'adygeya', 'msk'])),
									items: [{
										border: false,
										layout: 'column',
										items: [{
											border: false,
											layout: 'form',
											hidden: !(getRegionNick().inlist(['astra', 'buryatiya', 'perm', 'penza', 'vologda'])),
											items: [{
												fieldLabel: (getRegionNick().inlist(['perm', 'buryatiya', 'penza', 'vologda'])) ? 'КСГ' : 'КСГ/КПГ для расчёта',
												width: 500,
												hiddenName: 'Mes_rid',
												listeners: {
													'change': function (combo, newValue) {
														var index = combo.getStore().findBy(function (rec) {
															return (rec.get('Mes_id') == newValue);
														});

														combo.fireEvent('beforeselect', combo, combo.getStore().getAt(index));

														if (getRegionNick() == 'astra') {
															that.refreshFieldsVisibility(['EvnSection_isPartialPay']);
														}
													},
													'beforeselect': function (combo, record) {
														var base_form = that.findById('EvnSectionEditForm').getForm();
														// надо обновить MesTariff_id, EvnSection_KOEF
														var MesTariff_id = null;
														var MesTariff_sid = null;
														var EvnSection_KOEF = '';
														var EvnSection_KPGKOEF = '';
														var EvnSection_KSG = '';
														var EvnSection_KPG = '';
														if (record) {
															MesTariff_id = record.get('MesTariff_id');
															MesTariff_sid = record.get('MesTariff_sid');
															EvnSection_KOEF = record.get('MesTariff_Value');
															EvnSection_KPGKOEF = record.get('MesTariff_sValue');
															EvnSection_KSG = record.get('Mes_Code');
															EvnSection_KPG = record.get('MesKpg_Code');
														}
														base_form.findField('MesTariff_id').setValue(MesTariff_id);
														base_form.findField('MesTariff_sid').setValue(MesTariff_sid);
														base_form.findField('EvnSection_KOEF').setValue(EvnSection_KOEF);
														base_form.findField('EvnSection_KPGKOEF').setValue(EvnSection_KPGKOEF);
														base_form.findField('EvnSection_KSG').setValue(EvnSection_KSG);
														base_form.findField('EvnSection_KSG').fireEvent('change', base_form.findField('EvnSection_KSG'), base_form.findField('EvnSection_KSG').getValue());
														base_form.findField('EvnSection_KPG').setValue(EvnSection_KPG);


														if (getRegionNick() == 'astra') {
															if (record && record.get('Mes_IsRehab') && record.get('Mes_IsRehab') == 2) {
																base_form.findField('EvnSection_IsRehab').show();
															} else {
																base_form.findField('EvnSection_IsRehab').hide();
															}
														}
													}
												},
												xtype: 'swmesksgcombo'
											}]
										}, {
											border: false,
											layout: 'form',
											hidden: !getRegionNick().inlist(['astra']),
											style: 'margin-left: 30px;',
											items: [{
												xtype: 'swcheckbox',
												name: 'EvnSection_isPartialPay',
												boxLabel: 'Частичная оплата',
												hideLabel: true
											}]
										}, that.recalcKSGButton]
									}, that.KSGKPGFields, {
										border: false,
										layout: 'column',
										items: [{
											border: false,
											layout: 'form',
											items: [{
												readOnly: true,
												fieldLabel: getRegionNick().inlist(['perm', 'khak', 'vologda', 'yaroslavl', 'msk']) ? 'Коэффициент КСГ' : getRegionNick().inlist(['pskov']) ? 'Коэффициент КПГ' : getRegionNick() == 'kz' ? 'Коэффициент КЗГ' : 'Коэффициент КСГ/КПГ',
												name: 'EvnSection_KOEF',
												tabIndex: this.tabIndex + 28,
												width: 80,
												xtype: 'textfield'
											}]
										}, {
											border: false,
											hidden: true,
											id: that.id + 'ESecEF_KsgNotRecalc',
											layout: 'form',
											items: [{
												xtype: 'label',
												style: 'font-size: 12px; font-weight: bold; padding-left: 3px;',
												html: 'Случай оплачен, КСГ не пересчитано'
											}]
										}]
									}, {
										border: false,
										layout: 'form',
										hidden: !getRegionNick().inlist(['vologda']),
										items: [{
											readOnly: true,
											fieldLabel: 'Коэффициент КПГ',
											name: 'EvnSection_KPGKOEF',
											width: 80,
											xtype: 'textfield'
										}]
									}, {
										border: false,
										layout: 'form',
										hidden: !(getRegionNick().inlist(['perm', 'kareliya', 'astra', 'pskov', 'buryatiya', 'khak', 'krym', 'penza', 'vologda', 'yaroslavl', 'adygeya', 'msk'])),
										items: [{
											readOnly: true,
											fieldLabel: 'КСЛП',
											name: 'EvnSection_CoeffCTP',
											xtype: 'textfield'
										}]
									}]
								}, {
									fieldLabel: 'Подъём сегмента ST',
									hiddenName: 'EvnSection_IsST',
									xtype: 'swyesnocombo'
								}, {
									border: false,
									layout: 'form',
									id: that.id + '_CardioFields',
									items: [{
										fieldLabel: 'Осложнен кардиогенным шоком',
										hiddenName: 'EvnSection_IsCardShock',
										width: 145,
										xtype: 'swyesnocombo'
									}, {
										border: false,
										hidden: ! getRegionNick().inlist(['perm', 'penza']),
										layout: 'column',
										items: [{
											border: false,
											width: 240,
											layout: 'form',
											items: [{
												fieldLabel: 'Время от начала боли, часов',
												width: 45,
												name: 'EvnSection_StartPainHour',
												allowDecimals: false,
												allowNegative: false,
												xtype: 'numberfield'
											}]
										}, {
											border: false,
											layout: 'form',
											labelWidth: 40,
											items: [{
												fieldLabel: 'минут',
												width: 45,
												name: 'EvnSection_StartPainMin',
												maxValue: 59,
												minValue: 0,
												allowDecimals: false,
												allowNegative: false,
												xtype: 'numberfield'
											}]
										}]
									}, {
										border: false,
										layout: 'form',
										hidden: ! getRegionNick().inlist(['perm', 'penza']),
										items: [{
											fieldLabel: 'Кол-во баллов по шкале GRACE',
											width: 145,
											allowNegative: false,
											allowDecimals: false,
											maxLength: 3,
											name: 'EvnSection_GraceScalePoints',
											xtype: 'numberfield'
										}]
									}]
								}, {
									border: false,
									layout: 'form',
									id: that.id + '_PregnancyEvnPSFields',
									hidden: true,
									items: [{
										xtype: 'numberfield',
										name: 'PregnancyEvnPS_Period',
										fieldLabel: 'Срок беременности, недель',
										minValue: 1,
										maxValue: 45,
										width: 80
									}]
								}, {
									xtype: 'numberfield',
									name: 'EvnSection_BarthelIdx',
									fieldLabel: 'Индекс Бартел',
									allowDecimals: false,
									allowNegative: false,
									minValue: 0,
									maxValue: 100,
									width: 80
								}, {
									disabled: true,
									hideTrigger: true,
									xtype: 'swmedicalcarebudgtypecombo',
									hiddenName: 'MedicalCareBudgType_id',
									fieldLabel: 'Тип мед. помощи (бюджет)',
									width: 500
								}, that.EvnSectionKSGGrid
							]
						}),
						new sw.Promed.Panel({
							autoHeight: true,
							bodyStyle: 'padding-top: 0.5em;',
							border: true,
							collapsible: true,
							id: that.id + 'ESecEF_EvnLeavePanel',
							layout: 'form',
							style: 'margin-bottom: 0.5em;',
							title: '2. Исход госпитализации',
							items: [

								//  На форме НЕ ОТОБРАЖАЕТСЯ. Почему?
								{
									fieldLabel: (getRegionNick().inlist(['kareliya', 'krym']) ? 'Результат госпитализации' : 'Исход госпитализации'),
									hiddenName: 'LeaveTypeFed_id',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											log('combo, newValue, oldValue', combo, newValue, oldValue)
											var index = combo.getStore().findBy(function (rec) {
												return (rec.get('LeaveType_id') == newValue);
											});
											that.setPlanDisDT();

											combo.fireEvent('select', combo, combo.getStore().getAt(index));
										},
										'select': function (combo, record) {
											var base_form = that.findById('EvnSectionEditForm').getForm();
											var LeaveTypeCombo = base_form.findField('LeaveType_id');
											LeaveTypeCombo.clearValue();

											if (typeof record == 'object') {
												LeaveTypeCombo.setFieldValue('LeaveType_fedid', record.get('LeaveType_id'));

												switch (record.get('LeaveType_SysNick')) {
													case 'ksdie':
													case 'dsdie':
														base_form.findField('EvnDie_IsWait').setValue(1);
														break;

													case 'diepp':
													case 'ksdiepp':
													case 'dsdiepp':
														base_form.findField('EvnDie_IsWait').setValue(2);
														break;
												}
											}

											var index = LeaveTypeCombo.getStore().findBy(function (rec) {
												return (rec.get('LeaveType_id') == LeaveTypeCombo.getValue());
											});

											LeaveTypeCombo.fireEvent('select', LeaveTypeCombo, LeaveTypeCombo.getStore().getAt(index));
										}
									},
									tabIndex: this.tabIndex + 29,
									width: 300,
									xtype: 'swleavetypefedcombo'
								},


								{
									autoLoad: false,
									fieldLabel: (getRegionNick().inlist(['kareliya', 'krym']) ? 'Результат госпитализации' : 'Исход госпитализации'),
									hiddenName: 'LeaveType_id',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var index = combo.getStore().findBy(function (rec) {
												return (rec.get('LeaveType_id') == newValue);
											});

											combo.fireEvent('select', combo, combo.getStore().getAt(index));
										}.createDelegate(this),
										'select': function (combo, record) {


											var isBuryatiya = (getRegionNick() == 'buryatiya');
											var isKareliya = (getRegionNick() == 'kareliya');
											var isKrym = (getRegionNick() == 'krym');
											var isPenza = (getRegionNick() == 'penza');
											var base_form = this.findById('EvnSectionEditForm').getForm();
											that.leaveCauseFilter();
											that.setPatoMorphGistDirection();
											// 1. Чистим и скрываем все поля
											// 2. В зависимости от выбранного значения, открываем поля

											this.findById(that.id + 'ESecEF_AnatomPanel').hide();
											this.findById(that.id + 'ESecEF_AnatomDiagPanel').hide();

											base_form.findField('AnatomWhere_id').setAllowBlank(true);
											base_form.findField('Diag_aid').setAllowBlank(true);
											base_form.findField('EvnDie_expTime').setAllowBlank(true);
											base_form.findField('LpuSection_aid').setAllowBlank(true);
											base_form.findField('Org_aid').setAllowBlank(true);
											base_form.findField('MedStaffFact_aid').setAllowBlank(true);

											base_form.findField('EvnDie_IsWait').setAllowBlank(true);
											base_form.findField('EvnDie_IsWait').setContainerVisible(false);
											base_form.findField('EvnDie_IsAnatom').setAllowBlank(true);
											base_form.findField('EvnDie_IsAnatom').setContainerVisible(false);
											base_form.findField('EvnLeave_IsAmbul').setAllowBlank(true);
											base_form.findField('EvnLeave_IsAmbul').setContainerVisible(false);
											base_form.findField('EvnLeave_UKL').setAllowBlank(true);
											base_form.findField('EvnLeave_UKL').setContainerVisible(false);
											base_form.findField('LeaveCause_id').setAllowBlank(true);
											base_form.findField('LeaveCause_id').setContainerVisible(false);
											base_form.findField('Org_oid').setAllowBlank(true);
											base_form.findField('Org_oid').setContainerVisible(false);
											base_form.findField('LpuSection_oid').setAllowBlank(true);
											base_form.findField('LpuSection_oid').setContainerVisible(false);
											//base_form.findField('LpuSectionBedProfile_oid').setAllowBlank(true);
											base_form.findField('LpuSectionBedProfileLink_fedoid').setContainerVisible(false);
											base_form.findField('LpuSectionBedProfileLink_fedoid').setAllowBlank(true);
											base_form.findField('LpuUnitType_oid').setAllowBlank(true);
											base_form.findField('LpuUnitType_oid').setContainerVisible(false);
											base_form.findField('MedStaffFact_did').setAllowBlank(true);
											base_form.findField('MedStaffFact_did').setContainerVisible(false);
											base_form.findField('ResultDesease_id').setAllowBlank(true);
											base_form.findField('ResultDesease_id').setContainerVisible(false);
											base_form.findField('PrehospWaifRetired_id').setContainerVisible(false);
											base_form.findField('PrehospWaifRetired_id').setAllowBlank(true);

											if (getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm'])) {
												base_form.findField('CureResult_id').setAllowBlank(!getRegionNick().inlist(['astra', 'krym']));
												base_form.findField('CureResult_id').setContainerVisible(false);
												base_form.findField('CureResult_id').getStore().clearFilter();
												if (!this.isProcessLoadForm && !getRegionNick().inlist(['krasnoyarsk', 'krym'])) {
													base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);//Значение по умолчанию
												}
											}
											
											this.refreshFieldsVisibility(['PayTypeERSB_id']);
											if (getRegionNick() == 'kz' && base_form.findField('PayTypeERSB_id').isVisible()) {
												var BedProfile = base_form.findField('GetBed_id').getFieldValue('BedProfile');
												if (!!BedProfile && BedProfile.inlist(8200, 8300, 10100, 10200, 10300, 10400, 10500, 10600, 10700, 10800, 10900, 11000, 11100, 11200, 11300, 11400)) {
													base_form.findField('PayTypeERSB_id').setValue(2);
												} else if (!!BedProfile && BedProfile.inlist(1700, 1800, 13300)) {
													base_form.findField('PayTypeERSB_id').setValue(3);
												} else {
													base_form.findField('PayTypeERSB_id').setValue(1);
												}
											}

											diag_a_phase_combo = base_form.findField('DiagSetPhase_aid');
											diag_a_phase_combo.setAllowBlank(true);

											sw.Promed.EvnSection.calcFedLeaveType({
												date: base_form.findField('EvnSection_disDate').getValue(),
												LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
												LeaveType_SysNick: (record && record.get('LeaveType_SysNick')) || null,
												LeaveCause_Code: base_form.findField('LeaveCause_id').getFieldValue('LeaveCause_Code'),
												fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
												fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid'),
												noSetField: parentWin.isProcessLoadForm
											});
											sw.Promed.EvnSection.calcFedResultDeseaseType({
												date: base_form.findField('EvnSection_disDate').getValue(),
												LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
												LeaveType_SysNick: (record && record.get('LeaveType_SysNick')) || null,
												ResultDesease_Code: base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code'),
												fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid'),
												noSetField: parentWin.isProcessLoadForm
											});
											sw.Promed.EvnSection.filterFedResultDeseaseType({
												LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
												fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
												fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
											});
											sw.Promed.EvnSection.filterFedLeaveType({
												LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
												fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
												fieldLeaveType: base_form.findField('LeaveType_id'),
												fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
											});
											var isUfa = (getRegionNick() == 'ufa');
											if (typeof record != 'object' || Ext.isEmpty(record.get('LeaveType_id'))) {
												base_form.findField('ResultDesease_id').clearValue();
												base_form.findField('LeaveCause_id').clearValue();
												base_form.findField('LeaveType_fedid').clearValue();
												base_form.findField('ResultDeseaseType_fedid').clearValue();
												base_form.findField('Org_oid').clearValue();
												base_form.findField('MedStaffFact_did').clearValue();
												base_form.findField('LpuUnitType_oid').clearValue();
												base_form.findField('LpuSection_oid').clearValue();

												if (getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm']) || isKareliya) {
													base_form.findField('CureResult_id').clearValue();
													base_form.findField('CureResult_id').setContainerVisible(false);
													base_form.findField('CureResult_id').setAllowBlank(true);
												}

												base_form.findField('CureResult_id').fireEvent('change', base_form.findField('CureResult_id'), base_form.findField('CureResult_id').getValue());

												this.refreshFieldsVisibility(['MedicalCareBudgType_id']);

												return true;
											}

											base_form.findField('EvnLeave_UKL').setAllowBlank(false);
											base_form.findField('EvnLeave_UKL').setContainerVisible(true);
											diag_a_phase_combo.setAllowBlank(false);

											if (getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm'])) {
												base_form.findField('CureResult_id').setContainerVisible(true);
												base_form.findField('CureResult_id').enable();
												base_form.findField('CureResult_id').setAllowBlank(false);

												if (!this.isProcessLoadForm) {
													if (getRegionNick() == 'perm') {
														switch (record.get('LeaveType_SysNick')) {
															case 'stac':
															case 'section':
																base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 2);
																break;
															default:
																base_form.findField('CureResult_id').disable();
																break;
														}
													}
													else if (getRegionNick() == 'krasnoyarsk') {
														if (!Ext.isEmpty(record.get('LeaveType_SysNick'))) {
															if (
																record.get('LeaveType_SysNick').inlist(['ksleave', 'ksother',
																	'ksdie', 'ksinicpac', 'ksiniclpu', 'ksprerv', 'dsleave',
																	'dsother', 'dsdie', 'dsinicpac', 'dsiniclpu'
																])
															) {
																base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);
															}
															else {
																base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 2);
															}
														}
													}
													else if (getRegionNick() == 'krym') {
														if (!Ext.isEmpty(record.get('LeaveType_SysNick'))) {
															if (!record.get('LeaveType_SysNick').inlist(['ksper', 'dsper', 'ksprod', 'psprod'])) {
																base_form.findField('CureResult_id').disable();
																base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);
															}
														}
													}
													else if (getRegionNick() == 'astra') {
														switch (record.get('LeaveType_SysNick')) {
															case 'section':
																base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 3);
																break;
														}
													}
												}
											}


											if (isKareliya && this.isProcessLoadForm == false) {
												this.processFieldCurResult();
											}

											if (!base_form.findField('EvnLeave_UKL').getValue()) {
												base_form.findField('EvnLeave_UKL').setValue(1);
											}

											if (record.get('LeaveType_SysNick') && record.get('LeaveType_SysNick').inlist(sw.Promed.EvnSection.listLeaveTypeSysNickEvnOtherLpu)) {
												// Перевод в другую МО
												base_form.findField('LeaveCause_id').setAllowBlank(false);
												base_form.findField('LeaveCause_id').setContainerVisible(true);
												base_form.findField('Org_oid').setAllowBlank(false);
												base_form.findField('Org_oid').setContainerVisible(true);
												base_form.findField('ResultDesease_id').setAllowBlank(false);
												base_form.findField('ResultDesease_id').setContainerVisible(true);
												base_form.findField('LeaveCause_id').setFieldLabel('Причина перевода:');
											}
											if (record.get('LeaveType_SysNick') && record.get('LeaveType_SysNick').inlist(sw.Promed.EvnSection.listLeaveTypeSysNickEvnOtherStac)) {
												// Перевод в стационар другого типа
												if (isKareliya || isKrym || isBuryatiya || isPenza) {
													var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');
													if (!Ext.isEmpty(LpuUnitType_SysNick)) {
														if (record.get('LeaveType_SysNick') == 'ksstac' && LpuUnitType_SysNick == 'stac') {
															base_form.findField('LpuUnitType_oid').getStore().filterBy(function (rec) {
																return (rec.get('LpuUnitType_Code').inlist([3, 4, 5]));
															});
														}
														else if (record.get('LeaveType_SysNick') == 'dsstac' && LpuUnitType_SysNick.inlist(['dstac', 'hstac'])) {
															base_form.findField('LpuUnitType_oid').getStore().filterBy(function (rec) {
																return (rec.get('LpuUnitType_Code').inlist([2, 3, 4]));
															});
														}
													}
												}
												base_form.findField('LeaveCause_id').setContainerVisible(true);
												base_form.findField('LpuSection_oid').setAllowBlank(false);
												base_form.findField('LpuSection_oid').setContainerVisible(true);
												base_form.findField('LpuUnitType_oid').setAllowBlank(false);
												base_form.findField('LpuUnitType_oid').setContainerVisible(true);
												base_form.findField('ResultDesease_id').setAllowBlank(false);
												base_form.findField('ResultDesease_id').setContainerVisible(true);
												base_form.findField('LeaveCause_id').setFieldLabel('Причина перевода:');
												base_form.findField('LpuUnitType_oid').fireEvent('change', base_form.findField('LpuUnitType_oid'), base_form.findField('LpuUnitType_oid').getValue());
											}

											switch (record.get('LeaveType_SysNick')) {
												// Выписка
												case 'leave':
												// 101 - Выписан
												case 'ksleave':
												case 'dsleave':
													base_form.findField('EvnLeave_IsAmbul').setAllowBlank(false);
													base_form.findField('EvnLeave_IsAmbul').setContainerVisible(true);
													base_form.findField('LeaveCause_id').setAllowBlank(false);
													base_form.findField('LeaveCause_id').setContainerVisible(true);
													base_form.findField('ResultDesease_id').setAllowBlank(false);
													base_form.findField('ResultDesease_id').setContainerVisible(true);
													base_form.findField('PrehospWaifRetired_id').setContainerVisible(getRegionNick() != 'kz' && this.EvnPS_IsWaif == 2);
													base_form.findField('PrehospWaifRetired_id').setAllowBlank(getRegionNick() == 'kz' || this.EvnPS_IsWaif != 2);

													base_form.findField('LeaveCause_id').setFieldLabel('Причина выписки:');

													//base_form.findField('LeaveCause_id').fireEvent('change', base_form.findField('LeaveCause_id'), base_form.findField('LeaveCause_id').getValue());

													if (!base_form.findField('EvnLeave_IsAmbul').getValue()) {
														base_form.findField('EvnLeave_IsAmbul').setValue(1);
													}

													if (getRegionNick() == 'astra') {
														base_form.findField('CureResult_id').setAllowBlank(false);

														base_form.findField('CureResult_id').getStore().filterBy(function (rec) {
															return (rec.get('CureResult_Code') != 3);
														});
														var cure_result = base_form.findField('CureResult_id');
														var idx = cure_result.getStore().findBy(function (rec) {
															return rec.get('CureResult_id') == cure_result.getValue();
														});
														if (Ext.isEmpty(cure_result.getStore().getAt(idx))) {
															log('sdfsdfsdfsd')
															base_form.findField('CureResult_id').clearValue();
														}
														/*if (Ext.isEmpty(base_form.findField('CureResult_id').getStore().getAt(base_form.findField('CureResult_id').getValue()))) {
															base_form.findField('CureResult_id').clearValue();
														}*/
													}

													if (getRegionNick() == 'khak' && base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code') == 6) {

														if (base_form.findField('EvnDie_IsAnatom').getValue() === '2') {
															this.findById(that.id + 'ESecEF_AnatomPanel').show();
														}

														base_form.findField('LeaveCause_id').setContainerVisible(false);
														base_form.findField('LeaveCause_id').setAllowBlank(true);
														base_form.findField('EvnLeave_IsAmbul').setContainerVisible(false);
														base_form.findField('MedStaffFact_did').setContainerVisible(true);
														base_form.findField('EvnDie_IsAnatom').setContainerVisible(true);
														base_form.findField('MedStaffFact_did').setAllowBlank(false);
														base_form.findField('EvnDie_IsAnatom').setAllowBlank(false);
													}
													break;

												// Смерть
												case 'die':
												case 'ksdie':
												case 'ksdiepp':
												case 'diepp':
												case 'dsdie':
												case 'dsdiepp':
												case 'kslet':
												case 'ksletitar':
													diag_a_phase_combo.setAllowBlank(true);
													this.findById(that.id + 'ESecEF_AnatomPanel').show();
													if (isKareliya || isKrym || isBuryatiya || isPenza) {
														base_form.findField('EvnDie_IsWait').setAllowBlank(false);
														base_form.findField('EvnDie_IsWait').setContainerVisible(true);

														switch (record.get('LeaveType_SysNick')) {
															case 'ksdie':
															case 'dsdie':
															case 'kslet':
															case 'ksletitar':
																base_form.findField('EvnDie_IsWait').setValue(1);
																break;

															case 'diepp':
															case 'ksdiepp':
															case 'dsdiepp':
																base_form.findField('EvnDie_IsWait').setValue(2);
																break;
														}
													}
													base_form.findField('EvnDie_IsAnatom').setAllowBlank(false);
													base_form.findField('EvnDie_IsAnatom').setContainerVisible(true);
													base_form.findField('MedStaffFact_did').setAllowBlank(false);
													base_form.findField('MedStaffFact_did').setContainerVisible(true);
													that.setPatoMorphGistDirection();

													base_form.findField('EvnDie_IsAnatom').fireEvent('change', base_form.findField('EvnDie_IsAnatom'), base_form.findField('EvnDie_IsAnatom').getValue());

													if (base_form.findField('EvnDie_IsAnatom').getValue() == 2) {
														base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), base_form.findField('AnatomWhere_id').getValue());
													}

													if (getRegionNick() == 'astra') {
														base_form.findField('CureResult_id').setAllowBlank(false);
														base_form.findField('CureResult_id').getStore().filterBy(function (rec) {
															return (rec.get('CureResult_Code') != 3);
														});
														var cure_result = base_form.findField('CureResult_id');
														var idx = cure_result.getStore().findBy(function (rec) {
															return rec.get('CureResult_id') == cure_result.getValue();
														});
														if (Ext.isEmpty(cure_result.getStore().getAt(idx))) {
															log('sdfsdfsdfsd')
															base_form.findField('CureResult_id').clearValue();
														}
														/*if (Ext.isEmpty(base_form.findField('CureResult_id').getStore().getAt(base_form.findField('CureResult_id').getValue()))) {
															base_form.findField('CureResult_id').clearValue();
														}*/
													}
													break;

												// Перевод в другое отделение
												case 'section':
												case 'dstac':
												case 'kstac':
													base_form.findField('LeaveCause_id').setContainerVisible(true);
													base_form.findField('LpuSection_oid').setAllowBlank(false);
													base_form.findField('LpuSection_oid').setContainerVisible(true);
													base_form.findField('ResultDesease_id').setAllowBlank(false);
													base_form.findField('ResultDesease_id').setContainerVisible(true);

													base_form.findField('LeaveCause_id').setFieldLabel('Причина перевода:');

													var date = base_form.findField('EvnSection_disDate').getValue();
													var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
													var params = new Object();

													base_form.findField('LpuSection_oid').clearValue();

													// params.exceptIds = [ base_form.findField('LpuSection_id').getValue() ];
													params.isStac = true;

													if (getRegionNick() == 'khak') {
														if (record.get('LeaveType_SysNick') == 'dstac') {
															params.arrayLpuUnitType = [3, 5];
														}
														else {
															params.arrayLpuUnitType = [2];
														}
													}

													if (typeof date == 'object') {
														params.onDate = Ext.util.Format.date(date, 'd.m.Y');
													}

													var WithoutChildLpuSectionAge = false;
													var Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');

													if (typeof date == 'object') {
														var age = swGetPersonAge(Person_Birthday, date);
													}
													else {
														var age = swGetPersonAge(Person_Birthday, getValidDT(getGlobalOptions().date, ''));
													}

													if (age >= 18 && !isUfa) {
														params.WithoutChildLpuSectionAge = true;
													}

													setLpuSectionGlobalStoreFilter(params);

													base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

													if (base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid)) {
														base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
													}
													break;

												// Code: 104, Name: Переведён на другой профиль коек, SysNick: ksper
												case 'ksper':
												// Code: 204, Name: Переведён на другой профиль коек, SysNick: dsper
												case 'dsper':
													base_form.findField('LeaveCause_id').setContainerVisible(true);
													base_form.findField('LpuSection_oid').setAllowBlank(false);
													base_form.findField('LpuSection_oid').setContainerVisible(true);
													//base_form.findField('LpuSectionBedProfile_oid').setAllowBlank(false);
													//base_form.findField('LpuSectionBedProfile_oid').setContainerVisible(true);
													if (getRegionNick() != 'kz') {
														base_form.findField('LpuSectionBedProfileLink_fedoid').setContainerVisible(true);
														base_form.findField('LpuSectionBedProfileLink_fedoid').setAllowBlank(false);
													}
													base_form.findField('ResultDesease_id').setAllowBlank(false);
													base_form.findField('ResultDesease_id').setContainerVisible(true);

													base_form.findField('LeaveCause_id').setFieldLabel('Причина перевода:');

													var date = base_form.findField('EvnSection_disDate').getValue();
													var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
													var params = new Object();

													base_form.findField('LpuSection_oid').clearValue();

													params.arrayLpuUnitType = [base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code')]
													//params.exceptIds = [ base_form.findField('LpuSection_id').getValue() ];
													params.isStac = true;

													if (date) {
														params.onDate = Ext.util.Format.date(date, 'd.m.Y');
													}

													var WithoutChildLpuSectionAge = false;
													var Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
													if (date) {
														var age = swGetPersonAge(Person_Birthday, date);
													} else {
														var age = swGetPersonAge(Person_Birthday, getValidDT(getGlobalOptions().date, ''));
													}
													if (age >= 18 && !isUfa) {
														params.WithoutChildLpuSectionAge = true;
													}

													setLpuSectionGlobalStoreFilter(params);

													base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

													if (base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid)) {
														base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
													}

													base_form.findField('LpuSection_oid').fireEvent('change', base_form.findField('LpuSection_oid'), base_form.findField('LpuSection_oid').getValue());


													break;


												// https://redmine.swan.perm.ru/issues/30661
												// 107. Лечение прервано по инициативе пациента
												// 108. Лечение прервано по инициативе МО
												// 110. Самовольно прерванное лечение
												// 207. Лечение прервано по инициативе пациента
												// 208. Лечение прервано по инициативе МО
												case 'inicpac':
												case 'ksinicpac':
												case 'iniclpu':
												case 'ksiniclpu':
												case 'prerv':
												case 'ksprerv':
												case 'dsinicpac':
												case 'dsiniclpu':
												case 'ksprod':
													base_form.findField('ResultDesease_id').setAllowBlank(false);
													base_form.findField('ResultDesease_id').setContainerVisible(true);
													break;
											}

											base_form.findField('LeaveType_SysNick').setValue(record.get('LeaveType_SysNick'));

											if (getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm', 'kareliya'])) {
												base_form.findField('CureResult_id').fireEvent('change', base_form.findField('CureResult_id'), base_form.findField('CureResult_id').getValue());
											}

											if (base_form.findField('ResultDesease_id').hidden) {
												// если поле в итоге скрыли, то очистим его
												base_form.findField('ResultDesease_id').clearValue();
											}

											this.refreshFieldsVisibility(['MedicalCareBudgType_id']);
										}.createDelegate(this),
										'keydown': function (inp, e) {
											if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
												e.stopEvent();

												var
													base_form = this.findById('EvnSectionEditForm').getForm(),
													isBuryatiya = (getRegionNick() == 'buryatiya'),
													isPskov = (getRegionNick() == 'pskov');

												if (!isBuryatiya && !isPskov && !this.findById('ESecEF_EvnSectionPanel').collapsed && !base_form.findField('Mes_id').disabled) {
													base_form.findField('Mes_id').focus();
												}
												else {
													this.buttons[this.buttons.length - 1].focus();
												}
											}
										}.createDelegate(this)
									},
									tabIndex: this.tabIndex + 30,
									width: 300,
									xtype: 'swleavetypecombo'
								},
								{
									allowDecimals: true,
									allowNegative: false,
									fieldLabel: 'Уровень качества лечения',
									maxValue: 1,
									minValue: 0,
									name: 'EvnLeave_UKL',
									tabIndex: this.tabIndex + 31,
									width: 70,
									value: 1,
									xtype: 'numberfield'
								},
								{
									autoLoad: false,
									comboSubject: 'ResultDesease',
									fieldLabel: (getRegionNick().inlist(['kareliya', 'krym']) ? 'Исход госпитализации' : ('khak' == getRegionNick() ? 'Результат госпитализации' : 'Исход заболевания')),
									hiddenName: 'ResultDesease_id',
									lastQuery: '',
									listWidth: 700,
									tabIndex: this.tabIndex + 32,
									typeCode: 'int',
									width: 500,
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var index = combo.getStore().findBy(function (rec) {
												return (rec.get('LeaveType_id') == newValue);
											});

											combo.fireEvent('select', combo, combo.getStore().getAt(index));
										},
										'select': function (combo, record) {
											var base_form = that.findById('EvnSectionEditForm').getForm();
											var LeaveTypeCombo = base_form.findField('LeaveType_id');
											var index = LeaveTypeCombo.getStore().findBy(function (rec) {
												return (rec.get('LeaveType_id') == LeaveTypeCombo.getValue());
											});

											LeaveTypeCombo.fireEvent('select', LeaveTypeCombo, LeaveTypeCombo.getStore().getAt(index));
										}
									},
									xtype: 'swresultdeseasecombo'
								},
								{
									autoLoad: false,
									comboSubject: 'LeaveCause',
									fieldLabel: 'Причина выписки',
									hiddenName: 'LeaveCause_id',
									listWidth: 400,
									tabIndex: this.tabIndex + 33,
									typeCode: 'int',
									width: 300,
									xtype: 'swleavecausecombo'
								},
								{
									autoLoad: false,
									comboSubject: 'YesNo',
									fieldLabel: 'Направлен на амб. лечение',
									hiddenName: 'EvnLeave_IsAmbul',
									tabIndex: this.tabIndex + 34,
									typeCode: 'int',
									width: 70,
									xtype: 'swyesnocombo'
								},
								{
									displayField: 'Org_Name',
									editable: false,
									enableKeyEvents: true,
									fieldLabel: 'МО',
									hiddenName: 'Org_oid',
									listeners: {
										'keydown': function (inp, e) {
											if (inp.disabled)
												return;

											if (e.F4 == e.getKey()) {
												if (e.browserEvent.stopPropagation)
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;

												if (e.browserEvent.preventDefault)
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;

												e.returnValue = false;

												if (Ext.isIE) {
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}

												inp.onTrigger1Click();

												return false;
											}
										},
										'keyup': function (inp, e) {
											if (e.F4 == e.getKey()) {
												if (e.browserEvent.stopPropagation)
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;

												if (e.browserEvent.preventDefault)
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;

												e.returnValue = false;

												if (Ext.isIE) {
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}

												return false;
											}
										}
									},
									mode: 'local',
									onTrigger1Click: function () {
										var base_form = this.findById('EvnSectionEditForm').getForm();
										var combo = base_form.findField('Org_oid');

										if (combo.disabled) {
											return false;
										}

										getWnd('swOrgSearchWindow').show({
											OrgType_id: 11,
											onClose: function () {
												combo.focus(true, 200)
											},
											onSelect: function (org_data) {
												if (org_data.Org_id > 0) {
													combo.getStore().loadData([
														{
															Org_id: org_data.Org_id,
															Lpu_id: org_data.Lpu_id,
															Org_Name: org_data.Org_Name
														}
													]);
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
											{name: 'Org_id', type: 'int'},
											{name: 'Lpu_id', type: 'int'},
											{name: 'Org_Name', type: 'string'}
										],
										key: 'Org_id',
										sortInfo: {
											field: 'Org_Name'
										},
										url: C_ORG_LIST
									}),
									tabIndex: this.tabIndex + 35,
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
								},
								{
									autoLoad: false,
									comboSubject: 'LpuUnitType',
									fieldLabel: 'Тип стационара',
									hiddenName: 'LpuUnitType_oid',
									lastQuery: '',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var base_form = this.findById('EvnSectionEditForm').getForm();
											var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
											var date = base_form.findField('EvnSection_disDate').getValue();
											var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
											var params = new Object();
											var record = combo.getStore().getById(newValue);

											base_form.findField('LpuSection_oid').clearValue();

											params.isStac = true;

											if (record) {
												params.arrayLpuUnitType = [record.get('LpuUnitType_Code')];
											}

											if (date) {
												params.onDate = Ext.util.Format.date(date, 'd.m.Y');
											}

											var WithoutChildLpuSectionAge = false;
											var Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
											if (date) {
												var age = swGetPersonAge(Person_Birthday, date);
											} else {
												var age = swGetPersonAge(Person_Birthday, getValidDT(getGlobalOptions().date, ''));
											}
											if (age >= 18 && !isUfa) {
												params.WithoutChildLpuSectionAge = true;
											}

											setLpuSectionGlobalStoreFilter(params);

											base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

											if (base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid)) {
												base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
											}
										}.createDelegate(this)
									},
									tabIndex: this.tabIndex + 36,
									typeCode: 'int',
									width: 300,
									xtype: 'swlpuunittypecombo'
								},
								{
									fieldLabel: 'Отделение',
									hiddenName: 'LpuSection_oid',
									id: this.id + '_OtherLpuSectionCombo',
									listWidth: 650,
									tabIndex: this.tabIndex + 37,
									width: 500,
									xtype: 'swlpusectionglobalcombo'
								},
								{
									name: 'LpuSectionBedProfile_oid',
									xtype: 'hidden'
								},
								{
									fieldLabel: 'Профиль коек',
									tabIndex: this.tabIndex + 38,
									id: this.id + 'swlpusectionbedprofilelinkcombo',
									width: 500,
									//xtype: 'swlpusectionbedprofilecombo'
									//hiddenName: 'LpuSectionBedProfile_oid',
									hiddenName: 'LpuSectionBedProfileLink_fedoid',
									allowBlank: getRegionNick().inlist(['kz', 'khak']),
									xtype: 'swlpusectionbedprofilelinkcombo',
									listeners: {
										'change': function (combo, newValue, oldValue) {
											var base_form = this.findById('EvnSectionEditForm').getForm();
											var LpuSectionBedProfile_oid = combo.getFieldValue('LpuSectionBedProfile_id');
											base_form.findField('LpuSectionBedProfile_oid').setValue(LpuSectionBedProfile_oid);
										}.createDelegate(this)
									}
								},
								{
									dateFieldId: this.id + 'ESecEF_EvnSection_disDate',
									enableOutOfDateValidation: true,
									fieldLabel: 'Врач, установивший смерть',
									hiddenName: 'MedStaffFact_did',
									id: this.id + '_MedStaffFact_did',
									listWidth: 650,
									tabIndex: this.tabIndex + 39,
									width: 500,
									xtype: 'swmedstafffactglobalcombo'
								},
								{
									autoLoad: false,
									comboSubject: 'YesNo',
									fieldLabel: 'Умер в приемном покое',
									hiddenName: 'EvnDie_IsWait',
									listeners: {},
									tabIndex: this.tabIndex + 40,
									typeCode: 'int',
									width: 70,
									xtype: 'swyesnocombo'
								}, {
									border: false,
									hidden: !(getRegionNick().inlist(['perm'])), // Открыто для Перми
									layout: 'form',
									items: [{
										fieldLabel: 'Фед. результат',
										hiddenName: 'LeaveType_fedid',
										lastQuery: '',
										listWidth: 600,
										tabIndex: this.tabIndex + 41,
										width: 500,
										xtype: 'swleavetypefedcombo'
									}, {
										fieldLabel: 'Фед. исход',
										hiddenName: 'ResultDeseaseType_fedid',
										listWidth: 600,
										lastQuery: '',
										tabIndex: this.tabIndex + 42,
										width: 500,
										xtype: 'swresultdeseasetypefedcombo'
									}]
								}, {
									border: false,
									hidden: !(getRegionNick().inlist(['astra', 'krasnoyarsk', 'krym', 'perm', 'kareliya'])), // Открыто для Астрахани, Красноярска, Крыма и Перми и Карелии
									layout: 'form',
									items: [{
										fieldLabel: 'Итог лечения',
										hiddenName: 'CureResult_id',
										lastQuery: '',
										listeners: {
											'change': function (combo, newValue, oldValue) {

												var index = combo.getStore().findBy(function (rec) {
													return (rec.get(combo.valueField) == newValue);
												});

												combo.fireEvent('select', combo, combo.getStore().getAt(index));
											},
											'select': function (combo, record) {
												var base_form = that.findById('EvnSectionEditForm').getForm();

												var EvnSection_IsTerm = base_form.findField('EvnSection_IsTerm').getValue() || 1;

												if (getRegionNick() == 'perm') {
													if (typeof record == 'object' && record.get('CureResult_Code') == 1) {
														base_form.findField('EvnSection_IsTerm').setContainerVisible(true);
														base_form.findField('EvnSection_IsTerm').setAllowBlank(false);
														base_form.findField('EvnSection_IsTerm').setValue(EvnSection_IsTerm);
													}
													else {
														base_form.findField('EvnSection_IsTerm').setContainerVisible(false);
														base_form.findField('EvnSection_IsTerm').setAllowBlank(true);
														base_form.findField('EvnSection_IsTerm').clearValue();
													}
												}

												if (getRegionNick() == 'astra') {
													that.refreshFieldsVisibility(['EvnSection_isPartialPay']);
													that.loadKSGKPGKOEF();
												}
											}
										},
										tabIndex: this.tabIndex + 43,
										width: 350,
										xtype: 'swcureresultcombo'
									}]
								}, {
									border: false,
									hidden: !(getRegionNick().inlist(['perm'])), // Открыто для Перми
									layout: 'form',
									items: [{
										fieldLabel: 'Случай прерван',
										hiddenName: 'EvnSection_IsTerm',
										lastQuery: '',
										tabIndex: this.tabIndex + 44,
										width: 70,
										xtype: 'swyesnocombo'
									}]
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											autoLoad: false,
											comboSubject: 'YesNo',
											fieldLabel: 'Необходимость экспертизы',
											hiddenName: 'EvnDie_IsAnatom',
											listeners: {
												'change': function (combo, newValue, oldValue) {
													var index = combo.getStore().findBy(function (rec) {
														return (rec.get(combo.valueField) == newValue);
													});
													combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
												}.createDelegate(this),
												'select': function (combo, record) {
													var base_form = this.findById('EvnSectionEditForm').getForm();

													that.setPatoMorphGistDirection();

													if (typeof record != 'object' || record.get('YesNo_Code') == 0) {
														this.findById(that.id + 'ESecEF_AnatomPanel').hide();
														this.findById(that.id + 'ESecEF_AnatomDiagPanel').hide();

														that.findById(that.id + 'ESecEF_EvnLeavePanel').doLayout();

														base_form.findField('LpuSection_aid').setAllowBlank(true);
														base_form.findField('Org_aid').setAllowBlank(true);
														base_form.findField('MedStaffFact_aid').setAllowBlank(true);

														base_form.findField('AnatomWhere_id').clearValue();
														base_form.findField('Diag_aid').clearValue();
														base_form.findField('EvnDie_expDate').setRawValue('');
														base_form.findField('EvnDie_expTime').setRawValue('');
														base_form.findField('LpuSection_aid').clearValue();
														base_form.findField('MedStaffFact_aid').clearValue();
														base_form.findField('Org_aid').clearValue();

														base_form.findField('EvnDie_expDate').fireEvent('change', base_form.findField('EvnDie_expDate'), base_form.findField('EvnDie_expDate').getValue());

														return false;
													}

													this.findById(that.id + 'ESecEF_AnatomPanel').show();
													this.findById(that.id + 'ESecEF_AnatomDiagPanel').show();

													if (this.findById(that.id + 'ESecEF_AnatomDiagPanel').isLoaded == false) {
														this.findById(that.id + 'ESecEF_AnatomDiagPanel').isLoaded = true;

														this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid().removeAll();
														var grid1 = this.findById('ESecEF_PersonWeightGrid');

														this.findById(that.id + 'ESecEF_AnatomDiagGrid').setReadOnly(this.action == 'view');
														if (base_form.findField('EvnDie_id').getValue()) {
															this.findById(that.id + 'ESecEF_AnatomDiagGrid').loadData({
																globalFilters: {
																	'class': 'EvnDiagPSDie',
																	EvnDiagPS_pid: base_form.findField('EvnDie_id').getValue()
																},
																noFocusOnLoad: true
															});
														}
														else {
															LoadEmptyRow(this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid());
														}
													}

													base_form.findField('EvnDie_expDate').fireEvent('change', base_form.findField('EvnDie_expDate'), base_form.findField('EvnDie_expDate').getValue());
													base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), base_form.findField('AnatomWhere_id').getValue());

													that.findById(that.id + 'ESecEF_EvnLeavePanel').doLayout();
												}.createDelegate(this)
											},
											tabIndex: this.tabIndex + 45,
											typeCode: 'int',
											width: 70,
											xtype: 'swyesnocombo'
										}]
									}, {
										border: false,
										layout: 'form',
										style: '{margin: 0 0 0 30px;}',
										items: [{
											id: 'ESEW_addPatoMorphHistoDirectionButton',
											text: "Выписать направление",
											hidden: true,
											tooltip: 'Выписать направление на патоморфогистологическое исследование',
											//tabIndex: TABINDEX_RLW + 20,
											handler: function () {
												that.addPatoMorphHistoDirection();
											},
											xtype: 'button'
										}]
									}]
								},
								{
									autoHeight: true,
									id: that.id + 'ESecEF_AnatomPanel',
									style: 'padding: 0px;',
									title: 'Патологоанатомическая экспертиза',
									width: 750,
									xtype: 'fieldset',
									items: [
										{
											border: false,
											layout: 'column',

											items: [
												{
													border: false,
													layout: 'form',

													items: [
														{
															fieldLabel: 'Дата экспертизы',
															format: 'd.m.Y',
															listeners: {
																'change': function (field, newValue, oldValue) {
																	var base_form = this.findById('EvnSectionEditForm').getForm();

																	var lpu_section_aid = base_form.findField('LpuSection_aid').getValue();
																	var med_staff_fact_aid = base_form.findField('MedStaffFact_aid').getValue();

																	base_form.findField('LpuSection_aid').clearValue();
																	base_form.findField('MedStaffFact_aid').clearValue();

																	if (!newValue) {
																		setLpuSectionGlobalStoreFilter({
																			// isStac: true
																		});
																		base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

																		setMedStaffFactGlobalStoreFilter({
																			allowDuplacateMSF: true
																		});
																		base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

																		base_form.findField('AnatomWhere_id').setAllowBlank(true);
																		base_form.findField('Diag_aid').setAllowBlank(true);
																		base_form.findField('EvnDie_expTime').setAllowBlank(true);
																	}
																	else {
																		setLpuSectionGlobalStoreFilter({
																			// isStac: true,
																			onDate: Ext.util.Format.date(newValue, 'd.m.Y')
																		});
																		base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

																		setMedStaffFactGlobalStoreFilter({
																			allowDuplacateMSF: true,
																			onDate: Ext.util.Format.date(newValue, 'd.m.Y')
																		});
																		base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

																		base_form.findField('AnatomWhere_id').setAllowBlank(false);
																		base_form.findField('Diag_aid').setAllowBlank(false);
																		base_form.findField('EvnDie_expTime').setAllowBlank(false);
																	}

																	if (base_form.findField('LpuSection_aid').getStore().getById(lpu_section_aid)) {
																		base_form.findField('LpuSection_aid').setValue(lpu_section_aid);
																	}

																	if (base_form.findField('MedStaffFact_aid').getStore().getById(med_staff_fact_aid)) {
																		base_form.findField('MedStaffFact_aid').setValue(med_staff_fact_aid);
																	}
																}.createDelegate(this)
															},
															name: 'EvnDie_expDate',
															plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
															selectOnFocus: true,
															tabIndex: this.tabIndex + 46,
															width: 100,
															xtype: 'swdatefield'
														}
													]
												},
												{
													border: false,
													labelWidth: 50,
													layout: 'form',
													items: [
														{
															fieldLabel: 'Время',
															name: 'EvnDie_expTime',
															listeners: {
																'keydown': function (inp, e) {
																	if (e.getKey() == Ext.EventObject.F4) {
																		e.stopEvent();
																		inp.onTriggerClick();
																	}
																}
															},
															onTriggerClick: function () {
																var base_form = this.findById('EvnSectionEditForm').getForm();
																var time_field = base_form.findField('EvnDie_expTime');

																if (time_field.disabled) {
																	return false;
																}

																setCurrentDateTime({
																	callback: function () {
																		base_form.findField('EvnDie_expDate').fireEvent('change', base_form.findField('EvnDie_expDate'), base_form.findField('EvnDie_expDate').getValue());
																	}.createDelegate(this),
																	dateField: base_form.findField('EvnDie_expDate'),
																	loadMask: true,
																	setDate: true,
																	setDateMaxValue: false,
																	setDateMinValue: false,
																	setTime: true,
																	timeField: time_field,
																	windowId: this.id
																});
															}.createDelegate(this),
															plugins: [new Ext.ux.InputTextMask('99:99', true)],
															tabIndex: this.tabIndex + 47,
															validateOnBlur: false,
															width: 60,
															xtype: 'swtimefield'
														}
													]
												}
											]
										},
										{
											autoLoad: false,
											comboSubject: 'AnatomWhere',
											fieldLabel: 'Место проведения',
											hiddenName: 'AnatomWhere_id',
											lastQuery: '',
											listeners: {
												'change': function (combo, newValue, oldValue) {
													var index = combo.getStore().findBy(function (rec) {
														if (rec.get('AnatomWhere_id') == newValue) {
															return true;
														}
														else {
															return false;
														}
													});
													var record = combo.getStore().getAt(index);

													combo.fireEvent('select', combo, record);
												}.createDelegate(this),
												'select': function (combo, record) {
													var base_form = this.findById('EvnSectionEditForm').getForm();

													if (that.setPatoMorphGistDirection()) {
														return false;
													}

													var lpu_section_combo = base_form.findField('LpuSection_aid');
													var med_staff_fact_combo = base_form.findField('MedStaffFact_aid');
													var org_combo = base_form.findField('Org_aid');

													if (!record) {
														lpu_section_combo.disable();
														med_staff_fact_combo.disable();
														org_combo.disable();

														lpu_section_combo.clearValue();
														med_staff_fact_combo.clearValue();
														org_combo.clearValue();

														return false;
													}

													switch (parseInt(record.get('AnatomWhere_Code'))) {
														case 1:
															lpu_section_combo.enable();
															med_staff_fact_combo.enable();
															org_combo.disable();
															org_combo.clearValue();
															break;

														case 2:
														case 3:
															lpu_section_combo.disable();
															med_staff_fact_combo.disable();
															lpu_section_combo.clearValue();
															med_staff_fact_combo.clearValue();
															org_combo.enable();
															break;

														default:
															lpu_section_combo.disable();
															med_staff_fact_combo.disable();
															org_combo.disable();
															lpu_section_combo.clearValue();
															med_staff_fact_combo.clearValue();
															org_combo.clearValue();
															break;
													}
												}.createDelegate(this)
											},
											tabIndex: this.tabIndex + 48,
											typeCode: 'int',
											width: 300,
											xtype: 'swanatomwherecombo'
										},
										{
											displayField: 'Org_Name',
											editable: false,
											enableKeyEvents: true,
											fieldLabel: 'Организация',
											hiddenName: 'Org_aid',
											listeners: {
												'keydown': function (inp, e) {
													if (inp.disabled)
														return;

													if (e.F4 == e.getKey()) {
														if (e.browserEvent.stopPropagation)
															e.browserEvent.stopPropagation();
														else
															e.browserEvent.cancelBubble = true;

														if (e.browserEvent.preventDefault)
															e.browserEvent.preventDefault();
														else
															e.browserEvent.returnValue = false;

														e.returnValue = false;

														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}

														inp.onTrigger1Click();
														return false;
													}
												},
												'keyup': function (inp, e) {
													if (e.F4 == e.getKey()) {
														if (e.browserEvent.stopPropagation)
															e.browserEvent.stopPropagation();
														else
															e.browserEvent.cancelBubble = true;

														if (e.browserEvent.preventDefault)
															e.browserEvent.preventDefault();
														else
															e.browserEvent.returnValue = false;

														e.returnValue = false;

														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}

														return false;
													}
												}
											},
											mode: 'local',
											onTrigger1Click: function () {
												var base_form = this.findById('EvnSectionEditForm').getForm();
												var combo = base_form.findField('Org_aid');

												if (combo.disabled) {
													return false;
												}

												var anatom_where_combo = base_form.findField('AnatomWhere_id');
												var anatom_where_id = anatom_where_combo.getValue();
												var record = anatom_where_combo.getStore().getById(anatom_where_id);

												if (!record) {
													return false;
												}

												var anatom_where_code = record.get('AnatomWhere_Code');
												var org_type = '';

												switch (parseInt(anatom_where_code)) {
													case 2:
														org_type = 'lpu';
														break;

													case 3:
														org_type = 'anatom_old';
														break;

													default:
														return false;
														break;
												}

												getWnd('swOrgSearchWindow').show({
													object: org_type,
													onlyFromDictionary: true,
													onClose: function () {
														combo.focus(true, 200)
													},
													onSelect: function (org_data) {
														if (org_data.Org_id > 0) {
															combo.getStore().loadData([
																{
																	Org_id: org_data.Org_id,
																	Lpu_id: org_data.Lpu_id,
																	Org_Name: org_data.Org_Name
																}
															]);
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
													{name: 'Org_id', type: 'int'},
													{name: 'Lpu_id', type: 'int'},
													{name: 'Org_Name', type: 'string'}
												],
												key: 'Org_id',
												sortInfo: {
													field: 'Org_Name'
												},
												url: C_ORG_LIST
											}),
											tabIndex: this.tabIndex + 50,
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
										},
										{
											hiddenName: 'LpuSection_aid',
											id: this.id + 'ESecEF_LpuSectionAnatomCombo',
											linkedElements: [
												this.id + 'ESecEF_MedStaffFactAnatomCombo'
											],
											tabIndex: this.tabIndex + 49,
											width: 500,
											xtype: 'swlpusectionglobalcombo'
										},
										{
											fieldLabel: 'Врач',
											hiddenName: 'MedStaffFact_aid',
											id: this.id + 'ESecEF_MedStaffFactAnatomCombo',
											listWidth: 650,
											parentElementId: this.id + 'ESecEF_LpuSectionAnatomCombo',
											tabIndex: this.tabIndex + 51,
											width: 500,
											xtype: 'swmedstafffactglobalcombo'
										},
										{
											checkAccessRights: true,
											fieldLabel: 'Осн. патологоанат-й диагноз',
											hiddenName: 'Diag_aid',
											id: that.id + 'ESecEF_DiagAnatomCombo',
											tabIndex: this.tabIndex + 52,
											width: 500,
											xtype: 'swdiagcombo'
										}
									]
								},
								{
									border: false,
									height: 150,
									id: that.id + 'ESecEF_AnatomDiagPanel',
									isLoaded: false,
									layout: 'border',
									// style: 'margin-left: 165px; margin-right: 0.5em; padding-bottom: 4px;',

									items: [new sw.Promed.ViewFrame({
										actions: [
											{
												name: 'action_add', handler: function () {
													this.openEvnDiagPSEditWindow('add', 'die');
												}.createDelegate(this)
											},
											{
												name: 'action_edit', handler: function () {
													this.openEvnDiagPSEditWindow('edit', 'die');
												}.createDelegate(this)
											},
											{
												name: 'action_view', handler: function () {
													this.openEvnDiagPSEditWindow('view', 'die');
												}.createDelegate(this)
											},
											{
												name: 'action_delete', handler: function () {
													this.deleteEvent('EvnDiagPSDie');
												}.createDelegate(this), tooltip: 'Удалить диагноз из списка'
											},
											{name: 'action_refresh', disabled: true, hidden: true},
											{name: 'action_print', disabled: true, hidden: true}
										],
										autoLoadData: false,
										border: false,
										dataUrl: '/?c=EvnDiag&m=loadEvnDiagPSGrid',
										id: that.id + 'ESecEF_AnatomDiagGrid',
										region: 'center',
										stringfields: [
											{name: 'EvnDiagPS_id', type: 'int', header: 'ID', key: true},
											{name: 'EvnDiagPS_pid', type: 'int', hidden: true},
											{name: 'Diag_id', type: 'int', hidden: true},
											{name: 'DiagSetClass_id', type: 'int', hidden: true},
											{name: 'DiagSetPhase_id', type: 'int', hidden: true},
											{name: 'DiagSetType_id', type: 'int', hidden: true},
											{name: 'EvnDiagPS_PhaseDescr', type: 'string', hidden: true},
											{name: 'EvnDiagPS_setTime', type: 'string', hidden: true},
											{name: 'Person_id', type: 'int', hidden: true},
											{name: 'PersonEvn_id', type: 'int', hidden: true},
											{name: 'Server_id', type: 'int', hidden: true},
											{name: 'RecordStatus_Code', type: 'int', hidden: true},
											{
												name: 'EvnDiagPS_setDate',
												type: 'date',
												format: 'd.m.Y',
												header: 'Дата',
												width: 90
											},
											{
												name: 'DiagSetClass_Name',
												type: 'string',
												header: 'Вид диагноза',
												width: 200
											},
											{name: 'Diag_Code', type: 'string', header: 'Код диагноза', width: 100},
											{name: 'Diag_Name', type: 'string', header: 'Диагноз', id: 'autoexpand'}
										],
										style: 'margin-bottom: 0.5em;',
										title: 'Сопутствующие патологоанатомические диагнозы',
										toolbar: true
									})]
								}, {
									xtype: 'swdiagsetphasecombo',
									hiddenName: 'DiagSetPhase_aid',
									fieldLabel: langs('Состояние пациента при выписке'),
									width: 300,
									tabIndex: this.tabIndex + 53,
									editable: false
								}, {
									fieldLabel: 'Впервые выявленная инвалидность',
									hiddenName: 'PrivilegeType_id',
									xtype: 'swprivilegetypecombo',
									width: 300
								}, {
									comboSubject: 'PayTypeERSB',
									fieldLabel: 'Тип оплаты',
									hiddenName: 'PayTypeERSB_id',
									lastQuery: '',
									tabIndex: this.tabIndex + 53,
									typeCode: 'int',
									width: 300,
									xtype: 'swcommonsprcombo'
								}, {
									fieldLabel: 'Планируемая дата выписки',
									format: 'd.m.Y',
									hiddenName: 'EvnSection_PlanDisDT',
									name: 'EvnSection_PlanDisDT',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: this.tabIndex + 54,
									width: 100,
									xtype: 'swdatefield'
								},
								{
									xtype: 'swcommonsprcombo',
									comboSubject: 'PrehospWaifRetired',
									fieldLabel: lang['waif_leave'],
									hiddenName: 'PrehospWaifRetired_id',
									allowBlank: true,
									width: 500,
									hidden: true
								}
							]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 125,
							id: 'ESecEF_EvnDiagPSPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function (panel) {
									if (panel.isLoaded === false) {
										panel.isLoaded = true;
										panel.findById('ESecEF_EvnDiagPSGrid').getStore().load({
											params: {
												'class': 'EvnDiagPSSect',
												EvnDiagPS_pid: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: '3. Сопутствующие диагнозы',
							items: [new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_diag_sect',
								autoExpandMin: 100,
								border: false,
								columns: [
									{
										dataIndex: 'EvnDiagPS_setDate',
										header: 'Дата',
										hidden: false,
										renderer: Ext.util.Format.dateRenderer('d.m.Y'),
										resizable: false,
										sortable: true,
										width: 100
									},
									{
										dataIndex: 'DiagSetClass_Name',
										header: 'Вид диагноза',
										hidden: false,
										resizable: true,
										sortable: true,
										width: 200
									},
									{
										dataIndex: 'Diag_Code',
										header: 'Код диагноза',
										hidden: false,
										resizable: true,
										sortable: true,
										width: 100
									},
									{
										dataIndex: 'Diag_Name',
										header: 'Диагноз',
										hidden: false,
										id: 'autoexpand_diag_sect',
										resizable: true,
										sortable: true
									}
								],
								frame: false,
								height: 200,
								id: 'ESecEF_EvnDiagPSGrid',
								keys: [
									{
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
										fn: function (inp, e) {
											e.stopEvent();

											if (e.browserEvent.stopPropagation)
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if (e.browserEvent.preventDefault)
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if (Ext.isIE) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											var grid = this.findById('ESecEF_EvnDiagPSGrid');

											switch (e.getKey()) {
												case Ext.EventObject.DELETE:
													this.deleteEvent('EvnDiagPS');
													break;

												case Ext.EventObject.END:
													GridEnd(grid);
													break;

												case Ext.EventObject.ENTER:
												case Ext.EventObject.F3:
												case Ext.EventObject.F4:
												case Ext.EventObject.INSERT:
													if (!grid.getSelectionModel().getSelected()) {
														return false;
													}

													var action = 'add';

													if (e.getKey() == Ext.EventObject.F3) {
														action = 'view';
													}
													else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
														action = 'edit';
													}

													this.openEvnDiagPSEditWindow(action, 'sect');
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
													var base_form = this.findById('EvnSectionEditForm').getForm();

													grid.getSelectionModel().clearSelections();
													grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

													if (e.shiftKey == false) {
														if (/*getRegionNick().inlist([ 'ufa' ]) &&*/ !this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
															this.findById('ESecEF_EvnSectionNarrowBedGrid').getView().focusRow(0);
															this.findById('ESecEF_EvnSectionNarrowBedGrid').getSelectionModel().selectFirstRow();
														} else {
															if (!this.specificsPanel.collapsed) {
																var tree = this.specificsTree;
																tree.focus();
																if (tree.getRootNode() == tree.getSelectionModel().getSelectedNode()) {
																	tree.getRootNode().firstChild.select();
																} else {
																	tree.getSelectionModel().getSelectedNode().select();
																}
															} else {
																if (this.action != 'view') {
																	this.buttons[0].focus();
																} else {
																	this.buttons[this.buttons.length - 1].focus();
																}
															}
														}
													}
													else {
														if (!this.findById('ESecEF_EvnSectionPanel').collapsed && this.action != 'view') {
															var
																isBuryatiya = (getRegionNick() == 'buryatiya'),
																isPskov = (getRegionNick() == 'pskov');

															if (!isBuryatiya && !isPskov && !base_form.findField('Mes_id').disabled) {
																base_form.findField('Mes_id').focus(true);
															}
															else {
																base_form.findField('Diag_id').focus(true);
															}
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
									}
								],
								listeners: {
									'rowdblclick': function (grid, number, obj) {
										this.openEvnDiagPSEditWindow('edit', 'sect');
									}.createDelegate(this)
								},
								loadMask: true,
								region: 'center',
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function (sm, rowIndex, record) {
											var evn_diag_ps_id = null;
											var selected_record = sm.getSelected();
											var toolbar = this.grid.getTopToolbar();

											if (selected_record) {
												evn_diag_ps_id = selected_record.get('EvnDiagPS_id');
											}

											if (evn_diag_ps_id) {
												toolbar.items.items[1].enable();
												toolbar.items.items[2].enable();
												toolbar.items.items[3].enable();
											}
											else {
												toolbar.items.items[1].disable();
												toolbar.items.items[2].disable();
												toolbar.items.items[3].disable();
											}
											if (parentWin.action == 'view') {
												toolbar.items.items[1].disable();
												toolbar.items.items[3].disable();
											}
										}
									}
								}),
								stripeRows: true,
								store: new Ext.data.Store({
									autoLoad: false,
									baseParams: {
										'class': 'EvnDiagPSSect'
									},
									listeners: {
										'load': function (store, records, index) {
											if (store.getCount() == 0) {
												LoadEmptyRow(this.findById('ESecEF_EvnDiagPSGrid'));
											}

											// this.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
											// this.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
											this.refreshFieldsVisibility(['EvnSection_BarthelIdx']);
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnDiagPS_id'
									}, [
										{
											mapping: 'EvnDiagPS_id',
											name: 'EvnDiagPS_id',
											type: 'int'
										},
										{
											mapping: 'EvnDiagPS_pid',
											name: 'EvnDiagPS_pid',
											type: 'int'
										},
										{
											mapping: 'Person_id',
											name: 'Person_id',
											type: 'int'
										},
										{
											mapping: 'PersonEvn_id',
											name: 'PersonEvn_id',
											type: 'int'
										},
										{
											mapping: 'Server_id',
											name: 'Server_id',
											type: 'int'
										},
										{
											mapping: 'Diag_id',
											name: 'Diag_id',
											type: 'int'
										},
										{
											mapping: 'DiagSetPhase_id',
											name: 'DiagSetPhase_id',
											type: 'int'
										},
										{
											mapping: 'EvnDiagPS_PhaseDescr',
											name: 'EvnDiagPS_PhaseDescr',
											type: 'string'
										},
										{
											mapping: 'DiagSetClass_id',
											name: 'DiagSetClass_id',
											type: 'int'
										},
										{
											mapping: 'DiagSetType_id',
											name: 'DiagSetType_id',
											type: 'int'
										},
										{
											mapping: 'EvnDiagPS_setTime',
											name: 'EvnDiagPS_setTime',
											type: 'string'
										},
										{
											dateFormat: 'd.m.Y',
											mapping: 'EvnDiagPS_setDate',
											name: 'EvnDiagPS_setDate',
											type: 'date'
										},
										{
											mapping: 'DiagSetClass_Name',
											name: 'DiagSetClass_Name',
											type: 'string'
										},
										{
											mapping: 'Diag_Code',
											name: 'Diag_Code',
											type: 'string'
										},
										{
											mapping: 'Diag_Name',
											name: 'Diag_Name',
											type: 'string'
										}
									]),
									url: '/?c=EvnDiag&m=loadEvnDiagPSGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [
										{
											handler: function () {
												this.openEvnDiagPSEditWindow('add', 'sect');
											}.createDelegate(this),
											iconCls: 'add16',
											text: 'Добавить'
										},
										{
											handler: function () {
												this.openEvnDiagPSEditWindow('edit', 'sect');
											}.createDelegate(this),
											iconCls: 'edit16',
											text: 'Изменить'
										},
										{
											handler: function () {
												this.openEvnDiagPSEditWindow('view', 'sect');
											}.createDelegate(this),
											iconCls: 'view16',
											text: 'Просмотр'
										},
										{
											handler: function () {
												this.deleteEvent('EvnDiagPS');
											}.createDelegate(this),
											iconCls: 'delete16',
											text: 'Удалить'
										},
										{
											text: 'Копировать диагнозы',
											id: 'EvnDiagCopyButton',
											disabled: true,
											menu: {
												xtype: 'menu',
												plain: true,
												items: []
											}
										}
									]
								})
							})]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 200,
							id: 'ESecEF_EvnUslugaPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function (panel) {
									if (panel.isLoaded === false) {
										panel.isLoaded = true;
										panel.findById('ESecEF_EvnUslugaGrid').getStore().load({
											params: {
												pid: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: '4. Услуги',
							items: [new Ext.grid.GridPanel({
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
									dataIndex: 'EvnUsluga_setTime',
									header: 'Время',
									hidden: false,
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
									dataIndex: 'EvnUsluga_Kolvo',
									header: 'Количество',
									hidden: false,
									resizable: true,
									sortable: true,
									width: 100
								}],
								frame: false,
								id: 'ESecEF_EvnUslugaGrid',
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
									fn: function (inp, e) {
										e.stopEvent();

										if (e.browserEvent.stopPropagation)
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if (e.browserEvent.preventDefault)
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.returnValue = false;

										if (Ext.isIE) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}

										var grid = this.findById('ESecEF_EvnUslugaGrid');

										switch (e.getKey()) {
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
												if (!grid.getSelectionModel().getSelected()) {
													return false;
												}

												var action = 'add';

												if (e.getKey() == Ext.EventObject.F3) {
													action = 'view';
												}
												else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
													action = 'edit';
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
												var base_form = this.findById('EvnSectionEditForm').getForm();

												grid.getSelectionModel().clearSelections();
												grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

												if (e.shiftKey == false) {
													if (this.action != 'view') {
														this.buttons[0].focus();
													}
													else {
														this.buttons[1].focus();
													}
												}
												else {
													/*if ( !this.findById('ESecEF_EvnStickPanel').collapsed && this.findById('ESecEF_EvnStickGrid').getStore().getCount() > 0 ) {
													 this.findById('ESecEF_EvnStickGrid').getView().focusRow(0);
													 this.findById('ESecEF_EvnStickGrid').getSelectionModel().selectFirstRow();
													 }
													 else if ( !this.findById('ESecEF_EvnSectionPanel').collapsed && this.findById('ESecEF_EvnSectionGrid').getStore().getCount() > 0 ) {
													 this.findById('ESecEF_EvnSectionGrid').getView().focusRow(0);
													 this.findById('ESecEF_EvnSectionGrid').getSelectionModel().selectFirstRow();
													 }
													 else if ( !this.findById('ESecEF_AdmitDiagPanel').collapsed && this.findById('ESecEF_EvnDiagPSRecepGrid').getStore().getCount() > 0 ) {
													 this.findById('EPSEF_EvnDiagPSRecepGrid').getView().focusRow(0);
													 this.findById('EPSEF_EvnDiagPSRecepGrid').getSelectionModel().selectFirstRow();
													 }
													 else if ( !this.findById('EPSEF_AdmitDepartPanel').collapsed && this.action != 'view' ) {
													 if ( !base_form.findField('Diag_pid').disabled ) {
													 base_form.findField('Diag_pid').focus(true);
													 }
													 else {
													 base_form.findField('MedStaffFact_pid').focus(true);
													 }
													 }
													 else if ( !this.findById('EPSEF_DirectDiagPanel').collapsed && this.findById('EPSEF_EvnDiagPSHospGrid').getStore().getCount() > 0 ) {
													 this.findById('EPSEF_EvnDiagPSHospGrid').getView().focusRow(0);
													 this.findById('EPSEF_EvnDiagPSHospGrid').getSelectionModel().selectFirstRow();
													 }
													 else if ( !this.findById('EPSEF_HospitalisationPanel').collapsed && this.action != 'view' ) {
													 base_form.findField('EvnPS_IsDiagMismatch').focus(true);
													 }
													 else {
													 this.buttons[this.buttons.length - 1].focus();
													 }*/
												}
												break;
										}
									},
									scope: this,
									stopEvent: true
								}],
								listeners: {
									'rowdblclick': function (grid, number, obj) {
										this.openEvnUslugaEditWindow('edit');
									}.createDelegate(this)
								},
								loadMask: true,
								region: 'center',
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function (sm, rowIndex, record) {
											var access_type = 'view';
											var id = null;
											var evnclass_sysnick = null;
											var selected_record = sm.getSelected();
											var toolbar = this.findById('ESecEF_EvnUslugaGrid').getTopToolbar();

											if (selected_record) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnUsluga_id');
												evnclass_sysnick = selected_record.get('EvnClass_SysNick');
											}

											toolbar.items.items[1].disable();
											toolbar.items.items[3].disable();

											if (id) {
												toolbar.items.items[2].enable();

												if (this.action != 'view' /*&& access_type == 'edit'*/) {
													toolbar.items.items[1].enable();
													if (evnclass_sysnick != 'EvnUslugaPar') {
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
										'parent': 'EvnSection'
									},
									listeners: {
										'load': function (store, records, index) {
											if (store.getCount() == 0) {
												LoadEmptyRow(this.findById('ESecEF_EvnUslugaGrid'));
											}

											/*if ( store.getCount() < 3 ) {
												this.findById('ESecEF_EvnUslugaPanel').setHeight(95+store.getCount()*21);
											}
											else
											{
												this.findById('ESecEF_EvnUslugaPanel').setHeight(140);
											}*/

											// this.findById('ESecEF_EvnUslugaGrid').getView().focusRow(0);
											// this.findById('ESecEF_EvnUslugaGrid').getSelectionModel().selectFirstRow();
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
										iconCls: 'add16',
										text: 'Добавить',
										menu: {
											xtype: 'menu',
											plain: true,
											items: [{
												handler: function () {
													this.openEvnUslugaEditWindow('addOper');
												}.createDelegate(this),
												text: 'Добавить операцию'
											}, {
												handler: function () {
													this.openEvnUslugaEditWindow('add');
												}.createDelegate(this),
												text: 'Добавить общую услугу'
											}]
										}
									}, {
										handler: function () {
											this.openEvnUslugaEditWindow('edit');
										}.createDelegate(this),
										iconCls: 'edit16',
										text: 'Изменить'
									}, {
										handler: function () {
											this.openEvnUslugaEditWindow('view');
										}.createDelegate(this),
										iconCls: 'view16',
										text: 'Просмотр'
									}, {
										handler: function () {
											this.deleteEvent('EvnUsluga');
										}.createDelegate(this),
										iconCls: 'delete16',
										text: 'Удалить'
									}]
								})
							})]
						}),
						new sw.Promed.Panel({
							title: '5. Переливание препаратов крови',
							style: 'margin-bottom: 0.5em;',
							border: true,
							height: 225,
							hidden: (getRegionNick()=='kz'),
							id: 'ESecEF_TransfusionPanel',
							collapsible: true,
							collapsed: true,
							isLoaded: false,
							listeners: {
								'expand': function (panel) {
									if (panel.isLoaded === false) {
										panel.isLoaded = true;
										panel.findById('ESecEF_TransfusionGrid').getStore().load({
											params: {
												EvnPS_id : that.EvnPS_id,
												EvnSection_id: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
											}
										});
									}
									panel.doLayout();
								}.createDelegate(this)
							},
							items:[new Ext.grid.GridPanel({
								id: 'ESecEF_TransfusionGrid',
								frame: false,
								height: 200,
								border: false,
								region: 'center',
								columns: [
									{
										dataIndex: 'TransfusionFact_id',
										header: 'id',
										hidden: true,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'TransfusionFact_setDT',
										header: 'Дата',
										renderer: Ext.util.Format.dateRenderer('d.m.Y'),
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'TransfusionMethodType_Name',
										header: 'Способ',
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'TransfusionAgentType_Name',
										header: 'Трансфузионное средство',
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'TransfusionIndicationType_Name',
										header: 'Показания к трансфузии',
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'VizitClass_Name',
										header: 'Тип',
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'TransfusionFact_Dose',
										header: 'Доза(ед)',
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'TransfusionFact_Volume',
										header: 'Объем(мл)',
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'TransfusionReactionType_Name',
										header: 'Трансфузионные реакции',
										hidden: false,
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'TransfusionComplType_Name',
										header: 'Осложнения',
										hidden: false,
										resizable: true,
										sortable: true,
										width: 1000
									}
								],
								listeners: {
									'rowdblclick': function (grid, number, obj) {
										this.openTransfusionForm('edit');
									}.createDelegate(this)
								},
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function (sm, rowIndex, record) {
											var toolbar = this.grid.getTopToolbar();
											toolbar.items.items[1].enable();
											toolbar.items.items[2].enable();
											toolbar.items.items[3].enable();
											if (parentWin.action == 'view') {
												toolbar.items.items[1].disable();
												toolbar.items.items[3].disable();
											}
											if (this.grid.getStore().getCount()==1 && Ext.isEmpty(record.get('TransfusionFact_id'))){
												toolbar.items.items[1].disable();
												toolbar.items.items[2].disable();
												toolbar.items.items[3].disable();
												toolbar.items.items[4].menu.items.items[0].disable();
												toolbar.items.items[4].menu.items.items[1].disable();
											}
										}
									}
								}),
								store: new Ext.data.Store({
									listeners: {
										'load': function (store, records, index) {
											var toolbar = this.findById('ESecEF_TransfusionGrid').getTopToolbar();
											if (store.getCount() == 0) {
												LoadEmptyRow(this.findById('ESecEF_TransfusionGrid'));
												toolbar.items.items[4].menu.items.items[0].disable();
												toolbar.items.items[4].menu.items.items[1].disable();
											} else {
												toolbar.items.items[4].menu.items.items[0].enable();
												toolbar.items.items[4].menu.items.items[1].enable();
											}
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'TransfusionFact_id'
									}, [
										{
											mapping: 'TransfusionFact_id',
											name: 'TransfusionFact_id',
											type: 'string'
										},
										{
											dateFormat: 'd.m.Y',
											mapping: 'TransfusionFact_setDT',
											name: 'TransfusionFact_setDT',
											type: 'date'
										},
										{
											mapping: 'TransfusionMethodType_Name',
											name: 'TransfusionMethodType_Name',
											type: 'string'
										},
										{
											mapping: 'TransfusionAgentType_Name',
											name: 'TransfusionAgentType_Name',
											type: 'string'
										},
										{
											mapping: 'TransfusionIndicationType_Name',
											name: 'TransfusionIndicationType_Name',
											type: 'string'
										},
										{
											mapping: 'VizitClass_Name',
											name: 'VizitClass_Name',
											type: 'string'
										},
										{
											mapping: 'TransfusionFact_Dose',
											name: 'TransfusionFact_Dose',
											type: 'string'
										},
										{
											mapping: 'TransfusionFact_Volume',
											name: 'TransfusionFact_Volume',
											type: 'string'
										},
										{
											mapping: 'TransfusionReactionType_Name',
											name: 'TransfusionReactionType_Name',
											type: 'string'
										},
										{
											mapping: 'TransfusionComplType_Name',
											name: 'TransfusionComplType_Name',
											type: 'string'
										}
									]),
									url: '/?c=EvnSection&m=loadTransfusionFactList'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [
										{
											handler: function () {
												this.openTransfusionForm('add');
											}.createDelegate(this),
											iconCls: 'add16',
											text: 'Добавить'
										},
										{
											handler: function () {
												this.openTransfusionForm('edit');
											}.createDelegate(this),
											iconCls: 'edit16',
											text: 'Изменить',
											disabled: true
										},
										{
											handler: function () {
												this.openTransfusionForm('view');
											}.createDelegate(this),
											iconCls: 'view16',
											text: 'Просмотр',
											disabled: true
										},
										{
											handler: function () {
												this.deleteEvent('TransfusionFact');
											}.createDelegate(this),
											iconCls: 'delete16',
											text: 'Удалить',
											disabled: true
										},
										{
											iconCls: 'print16',
											text: 'Печать',
											menu: {
												xtype: 'menu',
												plain: true,
												items:[{
													text: 'Список',
													disabled: true,
													handler: function() {
														Ext.ux.GridPrinter.print(this.findById('ESecEF_TransfusionGrid'));
													}.createDelegate(this)
												},{
													text: 'Лист регистрации переливания трансфузионных сред (005/у)',
													disabled: true,
													handler: function() {
														printBirt({
															'Report_FileName': 'f005u.rptdesign',
															'Report_Params': '&paramEvnPs=' + that.EvnPS_id,
															'Report_Format': 'pdf'
														});
													}
												}]
											}
										}
									]
								})
							})]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 125,
							id: 'ESecEF_EvnSectionNarrowBedPanel',
							isLoaded: false,
							/*hidden: !getRegionNick().inlist([ 'ufa' ]),*/
							layout: 'border',
							listeners: {
								'expand': function (panel) {
									if (panel.isLoaded === false) {
										panel.isLoaded = true;
										panel.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().load({
											params: {
												EvnSectionNarrowBed_pid: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
											}
										});
									}
									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							title: '6. Профиль коек',
							items: [new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_evn_sect_nb',
								autoExpandMin: 100,
								border: false,
								columns: [
									{
										dataIndex: 'LpuSectionProfile_Name',
										header: 'Профиль',
										hidden: false,
										id: 'autoexpand_evn_sect_nb',
										resizable: true,
										sortable: true
									},
									{
										dataIndex: 'EvnSectionNarrowBed_setDate',
										header: 'Поступление',
										hidden: false,
										renderer: Ext.util.Format.dateRenderer('d.m.Y'),
										resizable: false,
										sortable: true,
										width: 100
									},
									{
										dataIndex: 'EvnSectionNarrowBed_disDate',
										header: 'Выписка',
										hidden: false,
										renderer: Ext.util.Format.dateRenderer('d.m.Y'),
										resizable: false,
										sortable: true,
										width: 100
									}
								],
								frame: false,
								height: 200,
								id: 'ESecEF_EvnSectionNarrowBedGrid',
								keys: [
									{
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
										fn: function (inp, e) {
											e.stopEvent();

											if (e.browserEvent.stopPropagation)
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if (e.browserEvent.preventDefault)
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if (Ext.isIE) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											var grid = this.findById('ESecEF_EvnSectionNarrowBedGrid');

											switch (e.getKey()) {
												case Ext.EventObject.DELETE:
													this.deleteEvent('EvnSectionNarrowBed');
													break;

												case Ext.EventObject.END:
													GridEnd(grid);
													break;

												case Ext.EventObject.ENTER:
												case Ext.EventObject.F3:
												case Ext.EventObject.F4:
												case Ext.EventObject.INSERT:
													if (!grid.getSelectionModel().getSelected()) {
														return false;
													}

													var action = 'add';

													if (e.getKey() == Ext.EventObject.F3) {
														action = 'view';
													}
													else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
														action = 'edit';
													}

													this.openEvnSectionNarrowBedEditWindow(action);
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
													var base_form = this.findById('EvnSectionEditForm').getForm();

													grid.getSelectionModel().clearSelections();
													grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

													if (e.shiftKey == false) {
														if (!this.specificsPanel.collapsed) {
															var tree = this.specificsTree;
															tree.focus();
															if (tree.getRootNode() == tree.getSelectionModel().getSelectedNode()) {
																tree.getRootNode().firstChild.select();
															} else {
																tree.getSelectionModel().getSelectedNode().select();
															}
														}
														else if (!this.findById('ESecEF_EvnSectionPanel').collapsed && this.action != 'view') {
															var
																isBuryatiya = (getRegionNick() == 'buryatiya'),
																isPskov = (getRegionNick() == 'pskov');

															if (!isBuryatiya && !isPskov && !base_form.findField('Mes_id').disabled) {
																base_form.findField('Mes_id').focus(true);
															}
															else {
																base_form.findField('Diag_id').focus(true);
															}
														}
														else {
															this.buttons[this.buttons.length - 1].focus();
														}

														/*if (this.action != 'view') {
														 this.buttons[0].focus();
														 }
														 else {
														 this.buttons[this.buttons.length - 1].focus();
														 }*/
													}
													else {
														if (!this.findById('ESecEF_EvnDiagPSPanel').collapsed && this.findById('ESecEF_EvnDiagPSGrid').getStore().getCount() > 0) {
															this.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
															this.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
														}
														else if (!this.findById('ESecEF_EvnSectionPanel').collapsed && this.action != 'view') {
															var
																isBuryatiya = (getRegionNick() == 'buryatiya'),
																isPskov = (getRegionNick() == 'pskov');

															if (!isBuryatiya && !isPskov && !base_form.findField('Mes_id').disabled) {
																base_form.findField('Mes_id').focus(true);
															}
															else {
																base_form.findField('Diag_id').focus(true);
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
									}
								],
								listeners: {
									'rowdblclick': function (grid, number, obj) {
										this.openEvnSectionNarrowBedEditWindow('edit');
									}.createDelegate(this)
								},
								loadMask: true,
								region: 'center',
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function (sm, rowIndex, record) {
											var evn_section_narrow_bed_id = null;
											var selected_record = sm.getSelected();
											var toolbar = this.findById('ESecEF_EvnSectionNarrowBedGrid').getTopToolbar();

											if (selected_record) {
												evn_section_narrow_bed_id = selected_record.get('EvnSectionNarrowBed_id');
											}

											toolbar.items.items[3].disable();

											if (evn_section_narrow_bed_id) {
												toolbar.items.items[2].enable();

												if (this.action != 'view') {
													toolbar.items.items[1].enable();
													toolbar.items.items[3].enable();
												}
											}
											else {
												toolbar.items.items[1].disable();
												toolbar.items.items[2].disable();
											}
										}.createDelegate(this)
									}
								}),
								stripeRows: true,
								store: new Ext.data.Store({
									autoLoad: false,
									listeners: {
										'load': function (store, records, index) {
											if (store.getCount() == 0) {
												LoadEmptyRow(this.findById('ESecEF_EvnSectionNarrowBedGrid'));
											} else if (!getRegionNick().inlist(['krym'])) {
												var base_form = this.findById('EvnSectionEditForm').getForm();
												if (store.getAt(0) && store.getAt(0).get('LpuSectionBedProfile_id') > 0) {
													base_form.findField('LpuSectionBedProfile_id').setValue(store.getAt(0).get('LpuSectionBedProfile_id'));
												}
											}
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnSectionNarrowBed_id'
									}, [
										{
											mapping: 'EvnSectionNarrowBed_id',
											name: 'EvnSectionNarrowBed_id',
											type: 'int'
										},
										{
											mapping: 'EvnSectionNarrowBed_pid',
											name: 'EvnSectionNarrowBed_pid',
											type: 'int'
										},
										{
											mapping: 'PersonEvn_id',
											name: 'PersonEvn_id',
											type: 'int'
										},
										{
											mapping: 'Server_id',
											name: 'Server_id',
											type: 'int'
										},
										{
											mapping: 'EvnSectionNarrowBed_pid',
											name: 'EvnSectionNarrowBed_pid',
											type: 'int'
										},
										{
											mapping: 'LpuSection_id',
											name: 'LpuSection_id',
											type: 'int'
										},
										{
											mapping: 'LpuSectionBedProfile_id',
											name: 'LpuSectionBedProfile_id',
											type: 'int'
										},
										{
											mapping: 'LpuSectionBedProfileLink_fedid',
											name: 'LpuSectionBedProfileLink_fedid',
											type: 'int'
										},
										{
											dateFormat: 'd.m.Y',
											mapping: 'EvnSectionNarrowBed_setDate',
											name: 'EvnSectionNarrowBed_setDate',
											type: 'date'
										},
										{
											mapping: 'EvnSectionNarrowBed_setTime',
											name: 'EvnSectionNarrowBed_setTime',
											type: 'string'
										},
										{
											dateFormat: 'd.m.Y',
											mapping: 'EvnSectionNarrowBed_disDate',
											name: 'EvnSectionNarrowBed_disDate',
											type: 'date'
										},
										{
											mapping: 'EvnSectionNarrowBed_disTime',
											name: 'EvnSectionNarrowBed_disTime',
											type: 'string'
										},
										{
											mapping: 'LpuSectionProfile_Name',
											name: 'LpuSectionProfile_Name',
											type: 'string'
										}
									]),
									url: '/?c=EvnSectionNarrowBed&m=loadEvnSectionNarrowBedGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [
										{
											handler: function () {
												this.openEvnSectionNarrowBedEditWindow('add');
											}.createDelegate(this),
											iconCls: 'add16',
											text: 'Добавить'
										},
										{
											handler: function () {
												this.openEvnSectionNarrowBedEditWindow('edit');
											}.createDelegate(this),
											iconCls: 'edit16',
											text: 'Изменить'
										},
										{
											handler: function () {
												this.openEvnSectionNarrowBedEditWindow('view');
											}.createDelegate(this),
											iconCls: 'view16',
											text: 'Просмотр'
										},
										{
											handler: function () {
												this.deleteEvent('EvnSectionNarrowBed');
											}.createDelegate(this),
											iconCls: 'delete16',
											text: 'Удалить'
										}
									]
								})
							})]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 125,
							id: 'ESecEF_EvnSectionDrugPSLinkPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function (panel) {
									if (panel.isLoaded === false) {
										panel.isLoaded = true;
										panel.findById('ESecEF_EvnSectionDrugPSLinkGrid').getStore().load({
											params: {
												EvnSection_id: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style: 'margin-bottom: 0.5em;',
							hidden: getRegionNick() != 'krym',
							title: '7. Медикаменты и дополнительные мероприятия',
							items: [new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand_evnsectiondrugpslink_dose',
								autoExpandMin: 100,
								border: false,
								columns: [
									{
										dataIndex: 'DrugPS_Name',
										header: 'Медикамент/мероприятие',
										hidden: false,
										resizable: false,
										sortable: true,
										width: 100
									},
									{
										dataIndex: 'DrugPSForm_Name',
										header: 'Форма',
										hidden: false,
										resizable: true,
										sortable: true,
										width: 200
									},
									{
										dataIndex: 'EvnSectionDrugPSLink_Dose',
										header: 'Дозировка (курсовая)',
										hidden: false,
										resizable: true,
										sortable: true,
										id: 'autoexpand_evnsectiondrugpslink_dose',
										width: 100
									}
								],
								frame: false,
								height: 200,
								id: 'ESecEF_EvnSectionDrugPSLinkGrid',
								keys: [
									{
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
										fn: function (inp, e) {
											e.stopEvent();

											if (e.browserEvent.stopPropagation)
												e.browserEvent.stopPropagation();
											else
												e.browserEvent.cancelBubble = true;

											if (e.browserEvent.preventDefault)
												e.browserEvent.preventDefault();
											else
												e.browserEvent.returnValue = false;

											e.returnValue = false;

											if (Ext.isIE) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											var grid = this.findById('ESecEF_EvnSectionDrugPSLinkGrid');

											switch (e.getKey()) {
												case Ext.EventObject.DELETE:
													this.deleteEvent('EvnSectionDrugPSLink');
													break;

												case Ext.EventObject.END:
													GridEnd(grid);
													break;

												case Ext.EventObject.ENTER:
												case Ext.EventObject.F3:
												case Ext.EventObject.F4:
												case Ext.EventObject.INSERT:
													if (!grid.getSelectionModel().getSelected()) {
														return false;
													}

													var action = 'add';

													if (e.getKey() == Ext.EventObject.F3) {
														action = 'view';
													}
													else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
														action = 'edit';
													}

													this.openEvnSectionDrugPSLinkEditWindow(action);
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
													var base_form = this.findById('EvnSectionEditForm').getForm();

													grid.getSelectionModel().clearSelections();
													grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

													if (e.shiftKey == false) {
														if (/*getRegionNick().inlist([ 'ufa' ]) &&*/ !this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
															this.findById('ESecEF_EvnSectionNarrowBedGrid').getView().focusRow(0);
															this.findById('ESecEF_EvnSectionNarrowBedGrid').getSelectionModel().selectFirstRow();
														} else {
															if (!this.specificsPanel.collapsed) {
																var tree = this.specificsTree;
																tree.focus();
																if (tree.getRootNode() == tree.getSelectionModel().getSelectedNode()) {
																	tree.getRootNode().firstChild.select();
																} else {
																	tree.getSelectionModel().getSelectedNode().select();
																}
															} else {
																if (this.action != 'view') {
																	this.buttons[0].focus();
																} else {
																	this.buttons[this.buttons.length - 1].focus();
																}
															}
														}
													}
													else {
														if (!this.findById('ESecEF_EvnSectionPanel').collapsed && this.action != 'view') {
															var
																isBuryatiya = (getRegionNick() == 'buryatiya'),
																isPskov = (getRegionNick() == 'pskov');

															if (!isBuryatiya && !isPskov && !base_form.findField('Mes_id').disabled) {
																base_form.findField('Mes_id').focus(true);
															}
															else {
																base_form.findField('Diag_id').focus(true);
															}
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
									}
								],
								listeners: {
									'rowdblclick': function (grid, number, obj) {
										this.openEvnSectionDrugPSLinkEditWindow('edit');
									}.createDelegate(this)
								},
								loadMask: true,
								region: 'center',
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function (sm, rowIndex, record) {
											var EvnSectionDrugPSLink_id = null;
											var selected_record = sm.getSelected();
											var toolbar = this.grid.getTopToolbar();

											if (selected_record) {
												EvnSectionDrugPSLink_id = selected_record.get('EvnSectionDrugPSLink_id');
											}

											if (EvnSectionDrugPSLink_id) {
												toolbar.items.items[1].enable();
												toolbar.items.items[2].enable();
												toolbar.items.items[3].enable();
											}
											else {
												toolbar.items.items[1].disable();
												toolbar.items.items[2].disable();
												toolbar.items.items[3].disable();
											}
											if (parentWin.action == 'view') {
												toolbar.items.items[1].disable();
												toolbar.items.items[3].disable();
											}
										}
									}
								}),
								stripeRows: true,
								store: new Ext.data.Store({
									autoLoad: false,
									listeners: {
										'load': function (store, records, index) {
											if (store.getCount() == 0) {
												LoadEmptyRow(this.findById('ESecEF_EvnSectionDrugPSLinkGrid'));
											}

											// this.findById('ESecEF_EvnSectionDrugPSLinkGrid').getView().focusRow(0);
											// this.findById('ESecEF_EvnSectionDrugPSLinkGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnSectionDrugPSLink_id'
									}, [
										{
											mapping: 'EvnSectionDrugPSLink_id',
											name: 'EvnSectionDrugPSLink_id',
											type: 'int'
										},
										{
											mapping: 'EvnSection_id',
											name: 'EvnSection_id',
											type: 'int'
										},
										{
											mapping: 'DrugPS_id',
											name: 'DrugPS_id',
											type: 'int'
										},
										{
											mapping: 'DrugPSForm_id',
											name: 'DrugPSForm_id',
											type: 'int'
										},
										{
											mapping: 'EvnSectionDrugPSLink_Dose',
											name: 'EvnSectionDrugPSLink_Dose',
											type: 'float'
										},
										{
											mapping: 'DrugPS_Name',
											name: 'DrugPS_Name',
											type: 'string'
										},
										{
											mapping: 'DrugPSForm_Name',
											name: 'DrugPSForm_Name',
											type: 'string'
										}
									]),
									url: '/?c=EvnSectionDrugPSLink&m=loadEvnSectionDrugPSLinkGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [
										{
											handler: function () {
												this.openEvnSectionDrugPSLinkEditWindow('add');
											}.createDelegate(this),
											iconCls: 'add16',
											text: 'Добавить'
										},
										{
											handler: function () {
												this.openEvnSectionDrugPSLinkEditWindow('edit');
											}.createDelegate(this),
											iconCls: 'edit16',
											text: 'Изменить'
										},
										{
											handler: function () {
												this.openEvnSectionDrugPSLinkEditWindow('view');
											}.createDelegate(this),
											iconCls: 'view16',
											text: 'Просмотр'
										},
										{
											handler: function () {
												this.deleteEvent('EvnSectionDrugPSLink');
											}.createDelegate(this),
											iconCls: 'delete16',
											text: 'Удалить'
										},
										{
											handler: function () {
												Ext.ux.GridPrinter.print(parentWin.findById('ESecEF_EvnSectionDrugPSLinkGrid'), {addNumberColumn: false});
											}.createDelegate(this),
											iconCls: 'print16',
											text: 'Печать'
										}
									]
								})
							})]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							id: this.id + '_SpecificsPanel',
							isExpanded: false,
							layout: 'border',
							listeners: {
								'expand': function (panel) {
									var base_form = this.findById('EvnSectionEditForm').getForm();
									if (this.editPersonNewBorn == null) {
										this.checkPersonNewBorn(function () {
											this.onSpecificsExpand(panel);
										}.createDelegate(this));
									} else {
										this.onSpecificsExpand(panel);
									}
								}.createDelegate(this)
							},
							split: true,
							style: 'margin-bottom: 0.5em;',
							title: (getRegionNick() == 'krym' ? '8' : '7') + '. Специфика',
							items: [
								{
									autoScroll: true,
									border: false,
									collapsible: false,
									wantToFocus: false,
									id: this.id + '_SpecificsTree',
									listeners: {
										'bodyresize': function (tree) {
											setTimeout(function () {
												parentWin.resizeSpecificForWizardPanel()
											}, 1);
										}.createDelegate(this),
										'beforeload': function (node) {
											var tree = this.findById(this.id + '_SpecificsTree');
											var base_form = this.findById('EvnSectionEditForm').getForm();

											var Diag_ids = [];
											if (base_form.findField('Diag_id').getValue() && base_form.findField('Diag_id').getFieldValue('Diag_Code')) {
												Diag_ids.push([base_form.findField('Diag_id').getValue(), 1, base_form.findField('Diag_id').getFieldValue('Diag_Code'), '']);
											}
											this.findById('ESecEF_EvnDiagPSGrid').getStore().each(function(record) {
												if(record.get('Diag_id')) {
													Diag_ids.push([record.get('Diag_id'), 0, record.get('Diag_Code'), record.get('EvnDiagPS_id').toString()]);
												}
											});
											tree.getLoader().baseParams.Diag_ids = Ext.util.JSON.encode(Diag_ids);

											if (node.attributes.object) {
												tree.getLoader().baseParams.object = node.attributes.object;
											}
											if (this.PersonRegister_id) {
												tree.getLoader().baseParams.PersonRegister_id = this.PersonRegister_id;
											}
											tree.getLoader().baseParams.Person_id = base_form.findField('Person_id').getValue();
											tree.getLoader().baseParams.EvnSection_id = base_form.findField('EvnSection_id').getValue();
											tree.getLoader().baseParams.EvnSection_setDate = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
											tree.getLoader().baseParams.EvnSection_disDate = Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y');
											tree.getLoader().baseParams.createCategoryMethod = "Ext.getCmp('" + this.getId() + "').createPersonPregnancyCategory";
											tree.getLoader().baseParams.deleteCategoryMethod = "Ext.getCmp('" + this.getId() + "').deletePersonPregnancyCategory";
											tree.getLoader().baseParams.allowCreateButton = (this.action != 'view');
											tree.getLoader().baseParams.allowDeleteButton = (this.action != 'view');
										}.createDelegate(this),
										'click': function (node, e) {
											if (e && e.getTarget('.link', this.specificsTree.body)) {
												e.stopEvent();
												return false;
											}
											if (e && node && Ext.get(node.ui.getEl())) {
												var linkEl = Ext.get(node.ui.getEl()).child('.link');
												if (linkEl && linkEl.isVisible() && linkEl.dom.innerText == 'Создать') {
													e.stopEvent();
													return false;
												}
											}

											var base_form = this.findById('EvnSectionEditForm').getForm();

											if (this.findById('ESecEF_EvnAbortForm')) {
												this.findById('ESecEF_EvnAbortForm').hide();
											}

											if (this.findById('ESecEF_PersonChildForm')) {
												this.findById('ESecEF_PersonChildForm').hide();
											}

											if (this.WizardPanel) {
												this.WizardPanel.hide();
											}

											// логика для онкологии
											if (node.attributes.value == 'MorbusOnko' && node.attributes.Diag_id) {
												var params = {};
												params.onHide = function (isChange) {
													this.loadSpecificsTree();
													this.findById('ESecEF_EvnUslugaGrid').getStore().load({
														params: {
															pid: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
														}
													});
													this.loadKSGKPGKOEF();
													this.loadEvnSectionKSGGrid();
													this.checkMesOldUslugaComplexFields();
												}.createDelegate(this);
												params.EvnSection_id = node.attributes.EvnSection_id;
												params.Morbus_id = node.attributes.Morbus_id;
												params.MorbusOnko_pid = base_form.findField('EvnSection_id').getValue();
												params.Person_id = base_form.findField('Person_id').getValue();
												params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
												params.Server_id = base_form.findField('Server_id').getValue();
												params.EvnDiagPLStomSop_id = node.attributes.EvnDiagPLStomSop_id;
												params.EvnDiagPLSop_id = node.attributes.EvnDiagPLSop_id;
												params.allowSpecificEdit = true;
												params.action = (this.action != 'view') ? 'edit' : 'view';

												// всегда пересохраняем движение, чтобы в специфику ушли актуальные данные
												if (base_form.findField('EvnSection_id').getValue() == 0) {
													this.doSave({
														openChildWindow: function () {
															params.EvnSection_id = base_form.findField('EvnSection_id').getValue();
															params.MorbusOnko_pid = base_form.findField('EvnSection_id').getValue();
															getWnd('swMorbusOnkoWindow').show(params);
														}.createDelegate(this)
													});
												} else if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
													this.saveDrugTherapyScheme({callback: function() {
														this.setEvnSectionDiag({callback: function() {
															getWnd('swMorbusOnkoWindow').show(params);
														}});
													}.createDelegate(this)});
												} else {
													getWnd('swMorbusOnkoWindow').show(params);
												}
											}

											switch (node.attributes.value) {
												// Сведения об аборте
												case 'abort_data':
													if (!this.findById('ESecEF_EvnAbortForm')) {
														// Добавляем форму редактирования сведений об аборте
														this.specificsFormsPanel.add({
															autoHeight: true,
															border: false,
															frame: false,
															// height: 200,
															hidden: true,
															id: 'ESecEF_EvnAbortForm',
															isLoaded: false,
															labelWidth: 150,
															layout: 'form',
															xtype: 'panel',
															items: [
																{
																	name: 'EvnAbort_id',
																	value: 0,
																	xtype: 'hidden'
																},
																{
																	fieldLabel: 'Дата аборта',
																	format: 'd.m.Y',
																	name: 'EvnAbort_setDate',
																	plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
																	width: 100,
																	xtype: 'swdatefield'
																},
																{
																	comboSubject: 'AbortType',
																	fieldLabel: 'Тип аборта',
																	hiddenName: 'AbortType_id',
																	width: 300,
																	xtype: 'swcommonsprcombo'
																},
																{
																	allowDecimals: false,
																	allowNegative: false,
																	fieldLabel: 'Срок беременности',
																	maxValue: 28,
																	minValue: 0,
																	name: 'EvnAbort_PregSrok',
																	width: 100,
																	xtype: 'numberfield'
																},
																{
																	allowDecimals: false,
																	allowNegative: false,
																	fieldLabel: 'Которая беременность',
																	maxValue: 99,
																	minValue: 1,
																	name: 'EvnAbort_PregCount',
																	width: 100,
																	xtype: 'numberfield'
																},
																{
																	comboSubject: 'AbortPlace',
																	fieldLabel: 'Место проведения',
																	hiddenName: 'AbortPlace_id',
																	width: 100,
																	xtype: 'swcommonsprcombo'
																},
																{
																	comboSubject: 'YesNo',
																	fieldLabel: 'Медикаментозный',
																	hiddenName: 'EvnAbort_IsMed',
																	width: 100,
																	xtype: 'swcommonsprcombo'
																},
																{
																	comboSubject: 'YesNo',
																	fieldLabel: 'Обследована на ВИЧ',
																	hiddenName: 'EvnPLAbort_IsHIV',
																	width: 100,
																	xtype: 'swcommonsprcombo'
																},
																{
																	comboSubject: 'YesNo',
																	fieldLabel: 'Наличие ВИЧ-инфекции',
																	hiddenName: 'EvnPLAbort_IsInf',
																	width: 100,
																	xtype: 'swcommonsprcombo'
																}
															]
														});

														// прогрузка справочников на фрейме специфики
														var panel = this.findById('ESecEF_EvnAbortForm'); // получаем панель, на которой находятся комбики
														var lists = this.getComboLists(panel); // получаем список комбиков
														this.loadDataLists({}, lists, true); // прогружаем все справочники (третий параметр noclose - без операций над формой)

														this.findById('ESecEF_EvnAbortForm').items.each(function (item) {
															if (item.items) {
																item.items.each(function (item) {
																	if (item.xtype && item.xtype.toString().inlist(['hidden', 'swcommonsprcombo', 'textfield', 'swdatefield', 'swtimefield'])) {
																		base_form.add(item);
																	}
																});
															}
															else if (item.xtype && item.xtype.toString().inlist(['hidden', 'swcommonsprcombo', 'textfield', 'swdatefield', 'swtimefield'])) {
																base_form.add(item);
															}
														});
													}
													this.findById('ESecEF_EvnAbortForm').show();
													this.specificsPanel.setHeight(300);
													this.specificsFormsPanel.doLayout();
													this.specificsFormsPanelEnableEdit('EvnAbortForm', this.action != 'view');
													break;
												// Сведения о новорожденном
												case 'born_data':
													if (!this.findById('ESecEF_PersonChildForm')) {
														var win = this;
														// Добавляем форму редактирования сведений о новорожденном
														this.specificsFormsPanel.add({
															autoHeight: true,
															border: false,
															frame: false,
															//height: 400,
															hidden: true,
															id: 'ESecEF_PersonChildForm',
															isLoaded: false,
															labelWidth: 150,
															layout: 'form',
															xtype: 'panel',
															items: [
																{
																	name: 'PersonNewBorn_id',
																	value: 0,
																	xtype: 'hidden'
																}, {
																	name: 'BirthSpecStac_id',
																	value: 0,
																	xtype: 'hidden'
																}, win.tabPanel
															]
														});

														// прогрузка справочников на фрейме специфики
														var panel = this.findById('ESecEF_PersonChildForm'); // получаем панель, на которой находятся комбики
														var lists = this.getComboLists(panel); // получаем список комбиков
														this.loadDataLists({}, lists, true); // прогружаем все справочники (третий параметр noclose - без операций над формой)
														var formFieldTypes = ['swcombo', 'numberfield', 'checkbox', 'hidden', 'swcommonsprcombo', 'textfield', 'swdatefield', 'swtimefield'];
														var addFieldsRecursive = function (item) {
															if (item.items) {
																item.items.each(addFieldsRecursive);
															}
															else if (item.xtype && item.xtype.toString().inlist(formFieldTypes)) {
																base_form.add(item);
															}
														}
														this.findById('ESecEF_PersonChildForm').items.each(addFieldsRecursive);

														//focusing viewframes

													}

													this.findById('ESecEF_PersonChildForm').show();
													this.specificsPanel.setHeight(getRegionNick() == 'kz' ? 660 : 840);
													this.specificsFormsPanel.doLayout();
													if (!this.findById('ESecEF_PersonChildForm').isLoaded) {
														var loadMask = new Ext.LoadMask(this.specificsFormsPanel.getEl(), {msg: "Загрузка данных..."});
														loadMask.show();

														// Загрузка данных с сервера в форму и гриды
														Ext.Ajax.request({
															callback: function (options, success, response) {
																loadMask.hide();
																var base_form = this.findById('EvnSectionEditForm').getForm();
																// Загружаем списки измерений массы и длины


																if (success) {
																	this.findById('ESecEF_PersonChildForm').isLoaded = true;
																	var response_obj = Ext.util.JSON.decode(response.responseText);
																	this.tabPanel.setActiveTab('tab_ESEWCommon');
																	if (response_obj.length > 0) {
																		response_obj = response_obj[0];
																		this.NewBorn_Weight = (response_obj.PersonNewBorn_Weight > 0) ? response_obj.PersonNewBorn_Weight : 0;
																		if (response_obj.ChildTermType_id) {
																			base_form.findField('ChildTermType_id').setValue(response_obj.ChildTermType_id);
																		} else {
																			if (this.childTermType_id) {
																				base_form.findField('ChildTermType_id').setValue(this.childTermType_id);
																			}
																		}
																		base_form.findField('PersonNewBorn_id').setValue(response_obj.PersonNewBorn_id);
																		this.tabPanel.setActiveTab('tab_ESEWCommon')
																		var grid1 = this.findById('ESEW_PersonBirthTraumaGrid1').getGrid();
																		var grid2 = this.findById('ESEW_PersonBirthTraumaGrid2').getGrid();
																		var grid3 = this.findById('ESEW_PersonBirthTraumaGrid3').getGrid();
																		var grid4 = this.findById('ESEW_PersonBirthTraumaGrid4').getGrid();
																		var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();

																		grid1.getStore().baseParams.BirthTraumaType_id = 1;
																		grid2.getStore().baseParams.BirthTraumaType_id = 2;
																		grid3.getStore().baseParams.BirthTraumaType_id = 3;
																		grid4.getStore().baseParams.BirthTraumaType_id = 4;
																		apgarGrid.getStore().load({params: {PersonNewBorn_id: base_form.findField('PersonNewBorn_id').getValue()}});
																		base_form.findField('FeedingType_id').setValue(response_obj.FeedingType_id);
																		base_form.findField('PersonNewBorn_BCGNum').setRawValue(response_obj.PersonNewBorn_BCGNum);
																		base_form.findField('PersonNewBorn_BCGSer').setRawValue(response_obj.PersonNewBorn_BCGSer);
																		base_form.findField('PersonNewBorn_BCGDate').setValue(response_obj.PersonNewBorn_BCGDate);
																		base_form.findField('BirthSpecStac_id').setValue(response_obj.BirthSpecStac_id);
																		base_form.findField('PersonNewBorn_Head').setValue(response_obj.PersonNewBorn_Head);
																		base_form.findField('PersonNewBorn_Breast').setValue(response_obj.PersonNewBorn_Breast);
																		base_form.findField('PersonNewBorn_Weight').setValue(response_obj.PersonNewBorn_Weight);
																		base_form.findField('PersonNewBorn_Height').setValue(response_obj.PersonNewBorn_Height);
																		base_form.findField('PersonNewBorn_HepatitNum').setRawValue(response_obj.PersonNewBorn_HepatitNum);
																		base_form.findField('PersonNewBorn_HepatitSer').setRawValue(response_obj.PersonNewBorn_HepatitSer);
																		base_form.findField('PersonNewBorn_HepatitDate').setValue(response_obj.PersonNewBorn_HepatitDate);
																		base_form.findField('PersonNewBorn_id').setValue(response_obj.PersonNewBorn_id);
																		base_form.findField('PersonNewBorn_IsAidsMother').setValue(response_obj.PersonNewBorn_IsAidsMother);
																		var personNewBorn_IsHepatit = base_form.findField('PersonNewBorn_IsHepatit');
																		if (personNewBorn_IsHepatit){
																			personNewBorn_IsHepatit.setValue(response_obj.PersonNewBorn_IsHepatit);
																			personNewBorn_IsHepatit.fireEvent('change', personNewBorn_IsHepatit, response_obj.PersonNewBorn_IsHepatit);
																		}
																		base_form.findField('PersonNewborn_BloodBili').setValue(response_obj.PersonNewborn_BloodBili);
																		base_form.findField('PersonNewborn_BloodHemoglo').setValue(response_obj.PersonNewborn_BloodHemoglo);
																		base_form.findField('PersonNewborn_BloodEryth').setValue(response_obj.PersonNewborn_BloodEryth);
																		base_form.findField('PersonNewborn_BloodHemato').setValue(response_obj.PersonNewborn_BloodHemato);
																		base_form.findField('NewBornWardType_id').setValue(response_obj.NewBornWardType_id);
																		base_form.findField('PersonNewBorn_IsBleeding').setValue(response_obj.PersonNewBorn_IsBleeding);
																		var personNewBorn_IsAudio = base_form.findField('PersonNewBorn_IsAudio');
																		if (personNewBorn_IsAudio){
																			personNewBorn_IsAudio.setValue(response_obj.PersonNewBorn_IsAudio);
																			personNewBorn_IsAudio.fireEvent('change', personNewBorn_IsAudio, response_obj.PersonNewBorn_IsAudio);
																		}
																		var personNewBorn_IsNeonatal = base_form.findField('PersonNewBorn_IsNeonatal');
																		if (personNewBorn_IsNeonatal){
																			personNewBorn_IsNeonatal.setValue(response_obj.PersonNewBorn_IsNeonatal);
																			personNewBorn_IsNeonatal.fireEvent('change', personNewBorn_IsNeonatal, response_obj.PersonNewBorn_IsNeonatal);
																		}
																		if (getRegionNick() == 'ufa') {
																			base_form.findField('RefuseType_pid').setValue(response_obj.RefuseType_pid);
																			base_form.findField('RefuseType_aid').setValue(response_obj.RefuseType_aid);
																			base_form.findField('RefuseType_bid').setValue(response_obj.RefuseType_bid);
																			base_form.findField('RefuseType_gid').setValue(response_obj.RefuseType_gid);
																		}
																		var personNewBorn_IsBCG = base_form.findField('PersonNewBorn_IsBCG');
																		if (personNewBorn_IsBCG){
																			personNewBorn_IsBCG.setValue(response_obj.PersonNewBorn_IsBCG);
																			personNewBorn_IsBCG.fireEvent('change', personNewBorn_IsBCG, response_obj.PersonNewBorn_IsBCG);
																		}
																		base_form.findField('PersonNewBorn_IsBreath').setValue(response_obj.PersonNewBorn_IsBreath);
																		base_form.findField('PersonNewBorn_IsHeart').setValue(response_obj.PersonNewBorn_IsHeart);
																		base_form.findField('PersonNewBorn_IsPulsation').setValue(response_obj.PersonNewBorn_IsPulsation);
																		base_form.findField('PersonNewBorn_IsMuscle').setValue(response_obj.PersonNewBorn_IsMuscle);
																		var Person_BirthDay = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
																		base_form.findField('PersonNewBorn_BCGDate').setMinValue(Person_BirthDay);
																		base_form.findField('PersonNewBorn_HepatitDate').setMinValue(Person_BirthDay);
																		if (response_obj.PersonNewBorn_CountChild) {
																			base_form.findField('PersonNewBorn_CountChild').setValue(response_obj.PersonNewBorn_CountChild);
																		} else {
																			if (this.PersonNewBorn_CountChild) {
																				base_form.findField('PersonNewBorn_CountChild').setValue(this.PersonNewBorn_CountChild);
																			}
																		}
																		var PersonNewBorn_IsAidsMother;
																		if (response_obj.PersonNewBorn_IsAidsMother) {
																			PersonNewBorn_IsAidsMother = response_obj.PersonNewBorn_IsAidsMother;
																			setTimeout(function () {
																				base_form.findField('PersonNewBorn_IsAidsMother').setValue(PersonNewBorn_IsAidsMother);
																			}, 1500);
																		} else {
																			if (this.PersonNewBorn_IsAidsMother) {
																				PersonNewBorn_IsAidsMother = this.PersonNewBorn_IsAidsMother;
																				setTimeout(function () {
																					base_form.findField('PersonNewBorn_IsAidsMother').setValue(PersonNewBorn_IsAidsMother);
																				}, 1500);
																			}
																		}
																		base_form.findField('PersonNewBorn_CountChild').setValue(response_obj.PersonNewBorn_CountChild);
																		base_form.findField('ChildPositionType_id').setValue(response_obj.ChildPositionType_id);
																		base_form.findField('PersonNewBorn_IsRejection').setValue(response_obj.PersonNewBorn_IsRejection);

																		if (getRegionNick().inlist([ 'ufa' ])){
																			if (response_obj.ChildTermType_id_IsEdit == 1){
																				base_form.findField('ChildTermType_id').setDisabled(true);
																			}else{
																				base_form.findField('ChildTermType_id').setDisabled(false);
																			}
																			if (response_obj.FeedingType_id_IsEdit == 1){
																				base_form.findField('FeedingType_id').setDisabled(true);
																			}else{
																				base_form.findField('FeedingType_id').setDisabled(false);
																			}
																			if (response_obj.NewBornWardType_id_IsEdit == 1){
																				base_form.findField('NewBornWardType_id').setDisabled(true);
																			}else{
																				base_form.findField('NewBornWardType_id').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_BCGNum_IsEdit == 1){
																				base_form.findField('PersonNewBorn_BCGNum').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_BCGNum').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_BCGSer_IsEdit == 1){
																				base_form.findField('PersonNewBorn_BCGSer').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_BCGSer').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_BCGDate_IsEdit == 1){
																				base_form.findField('PersonNewBorn_BCGDate').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_BCGDate').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_HepatitNum_IsEdit == 1){
																				base_form.findField('PersonNewBorn_HepatitNum').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_HepatitNum').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_HepatitSer_IsEdit == 1){
																				base_form.findField('PersonNewBorn_HepatitSer').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_HepatitSer').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_HepatitDate_IsEdit == 1){
																				base_form.findField('PersonNewBorn_HepatitDate').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_HepatitDate').setDisabled(false);
																			}
																			if (response_obj.FeedingType_id_IsEdit == 1){
																				base_form.findField('FeedingType_id').setDisabled(true);
																			}else{
																				base_form.findField('ChildTermType_id').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsAidsMother_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsAidsMother').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsAidsMother').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsHepatit_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsHepatit').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsHepatit').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsBCG_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsBCG').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsBCG').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsBreath_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsBreath').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsBreath').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsHeart_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsHeart').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsHeart').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsPulsation_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsPulsation').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsPulsation').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsMuscle_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsMuscle').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsMuscle').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsBleeding_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsBleeding').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsBleeding').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsNeonatal_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsNeonatal').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsNeonatal').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsAudio_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsAudio').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsAudio').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_CountChild_IsEdit == 1){
																				base_form.findField('PersonNewBorn_CountChild').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_CountChild').setDisabled(false);
																			}
																			if (response_obj.ChildPositionType_id_IsEdit == 1){
																				base_form.findField('ChildPositionType_id').setDisabled(true);
																			}else{
																				base_form.findField('ChildPositionType_id').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_IsRejection_IsEdit == 1){
																				base_form.findField('PersonNewBorn_IsRejection').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_IsRejection').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_Head_IsEdit == 1){
																				base_form.findField('PersonNewBorn_Head').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_Head').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_Height_IsEdit == 1){
																				base_form.findField('PersonNewBorn_Height').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_Height').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_Weight_IsEdit == 1){
																				base_form.findField('PersonNewBorn_Weight').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_Weight').setDisabled(false);
																			}
																			if (response_obj.PersonNewBorn_Breast_IsEdit == 1){
																				base_form.findField('PersonNewBorn_Breast').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewBorn_Breast').setDisabled(false);
																			}
																			if (response_obj.PersonNewborn_BloodBili_IsEdit == 1){
																				base_form.findField('PersonNewborn_BloodBili').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewborn_BloodBili').setDisabled(false);
																			}
																			if (response_obj.PersonNewborn_BloodHemoglo_IsEdit == 1){
																				base_form.findField('PersonNewborn_BloodHemoglo').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewborn_BloodHemoglo').setDisabled(false);
																			}
																			if (response_obj.PersonNewborn_BloodEryth_IsEdit == 1){
																				base_form.findField('PersonNewborn_BloodEryth').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewborn_BloodEryth').setDisabled(false);
																			}
																			if (response_obj.PersonNewborn_BloodHemato_IsEdit == 1){
																				base_form.findField('PersonNewborn_BloodHemato').setDisabled(true);
																			}else{
																				base_form.findField('PersonNewborn_BloodHemato').setDisabled(false);
																			}

																			
																			var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid');
																			apgarGrid.setActionDisabled('action_delete', (!Ext.isEmpty(response_obj.PersonNewborn_Id_Last)));
																			apgarGrid.setActionDisabled('action_add', (!Ext.isEmpty(response_obj.PersonNewborn_Id_Last)));
																			apgarGrid.setReadOnly(!Ext.isEmpty(response_obj.PersonNewborn_Id_Last));
																		}
																	}
																	else {
																		if (this.childTermType_id) {
																			base_form.findField('ChildTermType_id').setValue(this.childTermType_id);
																		}
																		if (this.PersonNewBorn_CountChild) {
																			base_form.findField('PersonNewBorn_CountChild').setValue(this.PersonNewBorn_CountChild);
																		}
																		if (this.PersonNewBorn_IsAidsMother) {
																			PersonNewBorn_IsAidsMother = this.PersonNewBorn_IsAidsMother;
																			setTimeout(function () {
																				base_form.findField('PersonNewBorn_IsAidsMother').setValue(PersonNewBorn_IsAidsMother);
																			}, 1500);
																			base_form.findField('PersonNewBorn_IsAidsMother').setValue(this.PersonNewBorn_IsAidsMother);
																		}

																		var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();
																		var values = [
																			{
																				NewbornApgarRate_id: -swGenTempId(apgarGrid.getStore()),
																				NewbornApgarRate_Time: 1,
																				RecordStatus_Code: 0
																			},
																			{
																				NewbornApgarRate_id: -swGenTempId(apgarGrid.getStore()),
																				NewbornApgarRate_Time: 5,
																				RecordStatus_Code: 0
																			},
																			{
																				NewbornApgarRate_id: -swGenTempId(apgarGrid.getStore()),
																				NewbornApgarRate_Time: 10,
																				RecordStatus_Code: 0
																			},
																			{
																				NewbornApgarRate_id: -swGenTempId(apgarGrid.getStore()),
																				NewbornApgarRate_Time: 15,
																				RecordStatus_Code: 0
																			}
																		];
																		apgarGrid.getStore().removeAll();
																		apgarGrid.getStore().loadData(values, true);

																		if (response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0) {
																			sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
																		}
																		if (getRegionNick() == 'ufa') {
																			var hidefield = base_form.findField('RefuseType_pid');
																			if (hidefield)
																				var hidepanel = hidefield.findParentByType('panel');
																			if (hidepanel) {
																				hidepanel.setVisible(false);
																			}
																			var hidefield = base_form.findField('RefuseType_aid');
																			if (hidefield)
																				var hidepanel = hidefield.findParentByType('panel');
																			if (hidepanel) {
																				hidepanel.setVisible(false);
																			}
																			var hidefield = base_form.findField('RefuseType_bid');
																			if (hidefield)
																				var hidepanel = hidefield.findParentByType('panel');
																			if (hidepanel) {
																				hidepanel.setVisible(false);
																			}
																			var hidefield = base_form.findField('RefuseType_gid');
																			if (hidefield)
																				var hidepanel = hidefield.findParentByType('panel');
																			if (hidepanel) {
																				hidepanel.setVisible(false);
																			}
																		}
																	}
																	if (getRegionNick() == 'ufa') {
																		var BloodBili = this.findById('ESecEF_BloodBili_Xml');
																		var BloodHemoglo = this.findById('ESecEF_BloodHemoglo_Xml');
																		var BloodEryth = this.findById('ESecEF_BloodEryth_Xml');
																		var BloodHemato = this.findById('ESecEF_BloodHemato_Xml');

																		Ext.Ajax.request({
																			url: '/?c=PersonNewBorn&m=loadNewBornBlood',
																			params: {
																				Person_id: base_form.findField('Person_id').getValue()
																			},
																			callback: function(options, success, response) {
																				if (success) {
																					var response_obj = Ext.util.JSON.decode(response.responseText);
																					response_obj.forEach(function(item) {
																						var EvnXml_id = "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + item.EvnXml_id + " });'>" + "Просмотреть результат" + "</a>";
																						switch (item.UslugaComplex_Code) {
																							case 'A09.05.021':
																								base_form.findField('PersonNewborn_BloodBili').setValue(item.UslugaTest_ResultValue);
																								BloodBili.setText(EvnXml_id,false);
																								BloodBili.show();
																								BloodBili.enable();
															
																								break;
																							case 'A09.05.003':
																								base_form.findField('PersonNewborn_BloodHemoglo').setValue(item.UslugaTest_ResultValue);
																								BloodHemoglo.setText(EvnXml_id,false);
																								BloodHemoglo.show();
																								BloodHemoglo.enable();
															
																								break;
																							case 'A08.05.003':
																								base_form.findField('PersonNewborn_BloodEryth').setValue(item.UslugaTest_ResultValue);
																								BloodEryth.setText(EvnXml_id,false);
																								BloodEryth.show();
																								BloodEryth.enable();
															
																								break;
																							case 'A09.05.002':
																								base_form.findField('PersonNewborn_BloodHemato').setValue(item.UslugaTest_ResultValue);
																								BloodHemato.setText(EvnXml_id,false);
																								BloodHemato.show();
																								BloodHemato.enable();
																								
																								break;
															
																							default:
																								break;
																						}
																					});
																				}
																			}
																		});
																	}
																}
																else {
																	sw.swMsg.alert('Ошибка', 'При загрузке сведений о новорожденном возникли ошибки');
																}
															}.createDelegate(this),
															params: {
																Person_id: base_form.findField('Person_id').getValue(),
																EvnPS_id: parentWin.EvnPS_id,
																EvnSection_id: base_form.findField('EvnSection_id').getValue()
															},
															url: '/?c=PersonNewBorn&m=loadPersonNewBornData'
														});
													}
													//this.specificsFormsPanelEnableEdit('PersonNewBornForm', this.action != 'view');

													/*var grid1 = this.findById('ESecEF_PersonWeightGrid');
													var grid2 = this.findById('ESecEF_PersonHeightGrid');
													if (grid1 && grid2) {
														grid1.setReadOnly(this.action == 'view');
														grid2.setReadOnly(this.action == 'view');
													}*/
													break;
												case 'PersonPregnancy':
													if (!this.WizardPanel) {
														this.createPersonPregnancyWizardPanel();
													}
													if (this.WizardPanel.isLoading()) {
														this.WizardPanel.show();
														if (e) e.stopEvent();
														return false;
													}

													this.WizardPanel.resetCurrentCategory();

													if (!Ext.isEmpty(node.attributes.key) || node.attributes.grid) {
														var params = {};
														switch (node.attributes.object) {
															case 'Anketa':
																if (this.PersonRegister_id) {
																	params.PersonPregnancy_id = node.attributes.key;
																	this.WizardPanel.getCategory('Anketa').loadParams = params;
																	this.WizardPanel.getCategory('Anketa').selectPage(0);
																}
																break;
															case 'AnketaCommonData':
																params.PersonPregnancy_id = node.attributes.key;
																this.WizardPanel.getCategory('Anketa').loadParams = params;
																this.WizardPanel.getCategory('Anketa').selectPage(0);
																break;
															case 'AnketaFatherData':
																params.PersonPregnancy_id = node.attributes.key;
																this.WizardPanel.getCategory('Anketa').loadParams = params;
																this.WizardPanel.getCategory('Anketa').selectPage(1);
																break;
															case 'AnketaAnamnesData':
																params.PersonPregnancy_id = node.attributes.key;
																this.WizardPanel.getCategory('Anketa').loadParams = params;
																this.WizardPanel.getCategory('Anketa').selectPage(2);
																break;
															case 'AnketaExtragenitalDisease':
																params.PersonPregnancy_id = node.attributes.key;
																this.WizardPanel.getCategory('Anketa').loadParams = params;
																this.WizardPanel.getCategory('Anketa').selectPage(3);
																break;
															case 'Screen':
																params.PregnancyScreen_id = node.attributes.key;
																this.WizardPanel.getCategory('Screen').loadParams = params;
																this.WizardPanel.getCategory('Screen').selectPage(0);
																break;
															case 'EvnList':
																this.WizardPanel.getCategory('EvnList').selectPage(0);
																break;
															case 'ConsultationList':
																this.WizardPanel.getCategory('ConsultationList').selectPage(0);
																break;
															case 'ResearchList':
																this.WizardPanel.getCategory('ResearchList').selectPage(0);
																break;
															case 'Certificate':
																params.BirthCertificate_id = node.attributes.key;
																this.WizardPanel.getCategory('Certificate').loadParams = params;
																this.WizardPanel.getCategory('Certificate').selectPage(0);
																break;
															case 'Result':
																params.BirthSpecStac_id = node.attributes.key;
																if (!Ext.isEmpty(params.BirthSpecStac_id) || !Ext.isEmpty(this.PersonRegister_id)) {
																	this.WizardPanel.getCategory('Result').loadParams = params;
																	this.WizardPanel.getCategory('Result').selectPage(0);
																}
																break;
															case 'DeathMother':
																params.DeathMother_id = node.attributes.key;
																this.WizardPanel.getCategory('DeathMother').loadParams = params;
																this.WizardPanel.getCategory('DeathMother').selectPage(0);
																break;
														}

														var status = 0;
														var category = this.WizardPanel.getCurrentCategory();
														if (category && node.attributes.key) {
															var categoryData = category.getCategoryData(category, node.attributes.key);
															status = categoryData ? categoryData.status : 0;
														}

														var page = this.WizardPanel.getCurrentPage();
														var readOnly = (node.attributes.readOnly || this.action == 'view');

														if (page && status != 3) {
															this.WizardPanel.show();
															category.setReadOnly(readOnly);
															category.moveToPage(page, this.WizardPanel.afterPageChange);
														} else {
															this.resizeSpecificForWizardPanel();
														}
													} else {
														if (node.attributes.object == 'Result' && !node.attributes.deleted) {
															this.WizardPanel.show();
															var category = this.WizardPanel.getCategory('Result');
															category.createCategory(category);
														}
													}
													break;
												default:
													this.specificsPanel.setHeight(220);
													this.specificsFormsPanel.doLayout();
													break;
											}
											this.prevNode = node;
										}.createDelegate(this),
										contextmenu: function(node, e) {
											if (!!node.leaf && node.attributes.value == 'MorbusOnko') {
												var c = new Ext.menu.Menu({
												items: [{
													id: 'print',
													text: langs('Печать КЛУ при ЗНО'),
													disabled: !node.attributes.Morbus_id,
													icon: 'img/icons/print16.png',
													iconCls : 'x-btn-text'
												},{
													id: 'printOnko',
													text: langs('Печать выписки при онкологии'),
													disabled: !(node.attributes.Morbus_id && getRegionNick() == 'ekb'),
													hidden: getRegionNick() != 'ekb',
													icon: 'img/icons/print16.png',
													iconCls : 'x-btn-text'
												}],
												listeners: {
													itemclick: function(item) {
														switch (item.id) {
															case 'print':
																var n = item.parentMenu.contextNode;
																printControlCardZno(n.attributes.EvnSection_id, n.attributes.EvnDiagPLSop_id);
																break;
															case 'printOnko':
																var n = item.parentMenu.contextNode;
																printControlCardOnko(n.attributes.EvnSection_id, n.attributes.EvnDiagPLSop_id);
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
									keys: [
										{
											key: [
												Ext.EventObject.TAB
											],
											fn: function (inp, e) {
												var form = parentWin.findById('EvnSectionEditForm').getForm();
												if (e.shiftKey) {
													//перескакиваем на предыдуший контрол
													if (/*getRegionNick().inlist([ 'ufa' ]) &&*/ !parentWin.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
														parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getView().focusRow(0);
														parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getSelectionModel().selectFirstRow();
													} else {
														if (!parentWin.findById('ESecEF_EvnDiagPSPanel').collapsed && parentWin.findById('ESecEF_EvnDiagPSGrid').getStore().getCount() > 0) {
															parentWin.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
															parentWin.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
														} else {
															if (!parentWin.findById('ESecEF_EvnSectionPanel').collapsed && this.action != 'view') {
																var
																	isBuryatiya = (getRegionNick() == 'buryatiya'),
																	isPskov = (getRegionNick() == 'pskov');

																if (!isBuryatiya && !isPskov && !base_form.findField('Mes_id').disabled) {
																	form.findField('Mes_id').focus(true);
																} else {
																	form.findField('Diag_id').focus(true);
																}
															}
															else {
																parentWin.buttons[this.buttons.length - 1].focus();
															}
														}
													}
												} else {
													parentWin.buttons[0].focus();
												}
											},
											scope: this,
											stopEvent: true
										}
									],
									loader: new Ext.tree.TreeLoader({
										dataUrl: '/?c=Specifics&m=getSpecificsTree'
									}),
									region: 'west',
									root: {
										draggable: false,
										id: 'specifics_tree_root',
										nodeType: 'async',
										text: 'Специфика',
										value: 'root'
									},
									rootVisible: false,
									split: true,
									useArrows: true,
									width: 200,
									xtype: 'treepanel'
								},
								{
									border: false,
									layout: 'border',
									region: 'center',
									xtype: 'panel',
									items: [
										{
											autoHeight: true,
											border: false,
											labelWidth: 150,
											split: true,
											items: [
												/*{
												 allowBlank: true,
												 disabled: true,
												 enableKeyEvents: true,
												 fieldLabel: 'В рамках ДУ',
												 listeners: {
												 'keydown': function(inp, e) {
												 switch (e.getKey()) {
												 case Ext.EventObject.F4:
												 e.stopEvent();
												 //this.openEvnPSListWindow();
												 break;
												 }
												 }.createDelegate(this)
												 },
												 name: 'PersonDisp_NumCard',
												 onTriggerClick: function() {
												 // this.openEvnPSListWindow();
												 }.createDelegate(this),
												 readOnly: true,
												 triggerClass: 'x-form-search-trigger',
												 width: 200,
												 xtype: 'trigger'
												 }*/
											],
											layout: 'form',
											region: 'north',
											xtype: 'panel'
										},
										{
											autoHeight: true,
											border: false,
											id: this.id + '_SpecificFormsPanel',
											items: [],
											layout: 'fit',
											region: 'center',
											xtype: 'panel'
										}
									]
								}
							]
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
							title: (getRegionNick() == 'krym' ? '9' : '8') + '. Скрининговые обследования',
							items: [this.EvnPLDispScreenOnkoGrid]
						}),
						this.RepositoryObservGrid
					]
				})]
		});
		/*
		if (isDebug()) {
			Ext.getCmp('EvnSectionEditForm').add(this.DiagPanel);
			Ext.getCmp('EvnSectionEditForm').add(this.SpecThreatPanel);
			Ext.getCmp('EvnSectionEditForm').add(this.MorbusHepatitisSpec);
		}
		*/

		sw.Promed.swEvnSectionEditWindow.superclass.initComponent.apply(this, arguments);

		this.FormPanel = this.findById('EvnSectionEditForm');
		this.FormPanel.on('render', function (formPanel) {
			formPanel.getForm().findField('ResultDeseaseType_fedid').getStore().removeAll();
			formPanel.getForm().findField('ResultDeseaseType_fedid').getStore().load();
			formPanel.getForm().findField('ResultDesease_id').on('change', function (combo, newValue) {
				var index = combo.getStore().findBy(function (rec) {
					return (rec.get('ResultDesease_id') == newValue);
				});
				combo.fireEvent('select', combo, combo.getStore().getAt(index));
			});
			formPanel.getForm().findField('ResultDesease_id').on('select', function (combo, record) {
				var base_form = formPanel.getForm();
				sw.Promed.EvnSection.calcFedResultDeseaseType({
					date: base_form.findField('EvnSection_disDate').getValue(),
					LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
					LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
					ResultDesease_Code: (record && record.get('ResultDesease_Code')) || null,
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid'),
					noSetField: parentWin.isProcessLoadForm
				});
			});
			formPanel.getForm().findField('LeaveCause_id').on('change', function (combo, newValue) {
				var index = combo.getStore().findBy(function (rec) {
					return (rec.get('LeaveCause_id') == newValue);
				});
				combo.fireEvent('select', combo, combo.getStore().getAt(index));
			});
			formPanel.getForm().findField('LeaveCause_id').on('select', function (combo, record) {
				var base_form = formPanel.getForm();
				sw.Promed.EvnSection.calcFedLeaveType({
					date: base_form.findField('EvnSection_disDate').getValue(),
					LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
					LeaveType_SysNick: base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick'),
					LeaveCause_Code: (record && record.get('LeaveCause_Code')) || null,
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid'),
					noSetField: parentWin.isProcessLoadForm
				});
				sw.Promed.EvnSection.filterFedResultDeseaseType({
					LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				})
				sw.Promed.EvnSection.filterFedLeaveType({
					LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldLeaveType: base_form.findField('LeaveType_id'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
			});
		});

		this.findById(this.id + '_OtherLpuSectionCombo').addListener('change', function (combo, newValue, oldValue) {
			//parentWin.filterLpuSectionBedProfilesByLpuSection(newValue, 'LpuSectionBedProfile_oid');
			parentWin.filterLpuSectionBedProfileLink(newValue, 'LpuSectionBedProfileLink_fedoid');

		}.createDelegate(this));
		this.findById(this.id + '_LpuSectionCombo').addListener('change', function (combo, newValue, oldValue) {
			this.showSTField();
			this.onchange_LpuSectionCombo(combo, newValue, oldValue);
		}.createDelegate(this));
		this.findById(this.id + '_MedStaffFactCombo').addListener('change', function (combo, newValue, oldValue) {
			var acombo = this.findById(this.id + '_LpuSectionCombo');
			var anewValue = acombo.getValue();
			var aoldValue = null;
			this.onchange_LpuSectionCombo(acombo, anewValue, aoldValue);
		}.createDelegate(this));
		this.findById(this.id + '_DiagCombo').addListener('keydown', function (inp, e) {
			if (e.getKey() == Ext.EventObject.TAB) {
				if (!e.shiftKey) {
					e.stopEvent();
					var base_form = parentWin.findById('EvnSectionEditForm').getForm();
					var
						isBuryatiya = (getRegionNick() == 'buryatiya'),
						isPskov = (getRegionNick() == 'pskov');

					if (!isBuryatiya && !isPskov && !base_form.findField('Mes_id').disabled) {
						base_form.findField('Mes_id').focus(true);
					}
					else if (!parentWin.findById(that.id + 'ESecEF_EvnLeavePanel').collapsed && !base_form.findField('LeaveType_id').disabled) {
						base_form.findField('LeaveType_id').focus();
					}
					else if (!parentWin.findById('ESecEF_EvnDiagPSPanel').collapsed && parentWin.findById('ESecEF_EvnDiagPSGrid').getStore().getCount() > 0) {
						parentWin.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
						parentWin.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
					}
					else if (/*getRegionNick().inlist([ 'ufa' ]) &&*/ !parentWin.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
						parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getView().focusRow(0);
						parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getSelectionModel().selectFirstRow();
					}
					else if (!parentWin.specificsPanel.collapsed) {
						setTimeout(function () {
							parentWin.tryFocusOnSpecificsTree()
						}, 1);//я знаю что непонятно, но что поделать, иначе никак
					}
					else if (parentWin.action != 'view') {
						parentWin.buttons[0].focus();
					}
					else {
						parentWin.buttons[parentWin.buttons.length - 1].focus();
					}
				}
			}

		}.createDelegate(this));
		this.findById(this.id + '_DiagCombo').addListener('change', function (combo, newValue, oldValue) {
			parentWin.onSpecificsExpand(parentWin.specificsPanel, true);
			if (parentWin.childPS || combo.isPregnancyDiag) {
				parentWin.specificsPanel.expand();

				/*if (combo.isPregnancyDiag) {
					parentWin.specificsTree.nodeHash.PersonPregnancy.expand(false, false, function(){
						var resultNode = parentWin.specificsTree.nodeHash.Result;
						resultNode.fireEvent('click', resultNode);
					});
				}*/
			} else if (combo.getFieldValue('Diag_Code') && combo.getFieldValue('Diag_Code').search(new RegExp("^(C|D0)", "i")) >= 0) {
				if (parentWin.specificsPanel.collapsed) {
					parentWin.specificsPanel.expand();
				} else {
					parentWin.loadSpecificsTree();
				}
			} else {
				parentWin.specificsPanel.collapse();
			}

			if (
				(
					!Ext.isEmpty(combo.getFieldValue('Diag_Code'))
					&& (
						(combo.getFieldValue('Diag_Code').substr(0, 3) >= 'J12' && combo.getFieldValue('Diag_Code').substr(0, 3) <= 'J19')
						|| combo.getFieldValue('Diag_Code') == 'U07.1'
						|| combo.getFieldValue('Diag_Code') == 'U07.2'
					)
				)
				|| (
					getRegionNick() == 'msk'
					&& (parentWin.CovidType_id == 2 || parentWin.CovidType_id == 3)
				)
			) {
				parentWin.RepositoryObservGrid.show();
				parentWin.RepositoryObservGrid.doLayout();
			}
			else {
				parentWin.RepositoryObservGrid.hide();
			}
		});
		this.findById(this.id + '_DiagCombo').addListener('select', function (combo, record) {
			parentWin.showSTField();
			parentWin.showRankinScale();
			parentWin.showCardioFields();

			var diag_code = combo.getFieldValue('Diag_Code');
			if (getRegionNick() != 'krym' && diag_code && diag_code.search(new RegExp("^(C|D0)", "i")) >= 0) {
				parentWin.findById('ESecEF_EvnSection_IsZNOCheckbox').setValue(false);
				parentWin.findById('ESecEF_EvnSection_IsZNOCheckbox').disable();
			} else {
				parentWin.findById('ESecEF_EvnSection_IsZNOCheckbox').enable();

				if (getRegionNick() == 'buryatiya') {
					parentWin.findById('ESecEF_EvnSection_IsZNOCheckbox').setValue(diag_code == 'Z03.1');
				}
			}
			if (
				(
					!Ext.isEmpty(diag_code)
					&& (
						(diag_code.substr(0, 3) >= 'J12' && diag_code.substr(0, 3) <= 'J19')
						|| diag_code == 'U07.1'
						|| diag_code == 'U07.2'
					)
				)
				|| (
					getRegionNick() == 'msk'
					&& (parentWin.CovidType_id == 2 || parentWin.CovidType_id == 3)
				)
			) {
				parentWin.RepositoryObservGrid.show();
				parentWin.RepositoryObservGrid.doLayout();
			}
			else {
				parentWin.RepositoryObservGrid.hide();
			}
		});
		this.findById(this.id + '_DiagCombo').addListener('beforeselect', function (combo, record) {
			combo.setValue(record.get('Diag_id'));
			combo.fireEvent('change', combo, combo.getValue());
			combo.onChange(combo, combo.getValue());
		});
		this.onSpecificsExpand = function (panel, forbidResetSpecific) {
			this.Morbus_id = null;
			panel.isExpanded = true;
			var than = this;
			var tree = parentWin.specificsTree;
			//tree.getRootNode().expand();
			//дизаблить беременность и роды если вызвано движение ребенка
			//дизаблить для мужиков
			var male = ('2' != parentWin.findById('ESecEF_PersonInformationFrame').getFieldValue('Sex_Code'));
			//дизаблить сведения о новорожденном если старше года
			var now;
			var EvnSection_setDateField = that.findById('EvnSectionEditForm').getForm().findField('EvnSection_setDate');
			if (EvnSection_setDateField.getValue()) {
				if (EvnSection_setDateField.getValue() instanceof Date) {
					now = EvnSection_setDateField.getValue();
				} else {
					now = getValidDT(getGlobalOptions().date, '');
				}
			} else {
				now = getValidDT(getGlobalOptions().date, '');
			}
			var bday = parentWin.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
			var olderThanOneYear = false;
			var olderThan365Days = false;
			var olderThanTwoWeeks = false;
			if (bday) {
				olderThanOneYear = (bday.add('Y', 1) < now);
				olderThan365Days = (bday.add('D', 365) < now);
				olderThanTwoWeeks = (bday.add('D', 14) < now);
				olderThan28Days = (bday.add('D', 28) < now);
			}
			var isPregnancyDiag = false;
			var isOnkoDiag = false;
			var base_form = this.findById('EvnSectionEditForm').getForm();
			var diag_id = base_form.findField('Diag_id').getValue();
			var sickness_index = -1;
			var sickness_id = null;
			var morbus_type_index = null;
			var morbus_type_id = null;
			var newValue = parentWin.findById(parentWin.id + '_DiagCombo').getValue();
			parentWin.findById(parentWin.id + '_DiagCombo').isPregnancyDiag = false;
			if (newValue != '') {
				//находим диагноз
				sickness_index = parentWin.sicknessDiagStore.findBy(function (record) {
					if (record.get('Diag_id') == newValue) {
						//заодно определяем заболевание
						sickness_id = record.get('Sickness_id');
						return true;
					}
				});
				morbus_type_index = parentWin.morbusDiagStore.findBy(function (record) {
					if (record.get('Diag_id') == newValue) {
						//заодно определяем заболевание
						morbus_type_id = record.get('MorbusType_id');
						return true;
					}
				});
			}
			if (sickness_index >= 0 && sickness_id != null) {
				//запись найдена
				switch (sickness_id.toString()) {
					// беременность надо показать для
					/*case '9'://9 БЕРЕМЕННОСТЬ И РОДЫ
						isPregnancyDiag = true;
						parentWin.findById(parentWin.id + '_DiagCombo').isPregnancyDiag = true;
						break;*/
					case '10'://10 Онко
						isOnkoDiag = true;
						break;
					default:
						break;
				}
			}
			if (morbus_type_index >= 0 && morbus_type_id != null) {
				switch (morbus_type_id.toString()) {
					case '2'://2 Беренность
						isPregnancyDiag = true;
						parentWin.findById(parentWin.id + '_DiagCombo').isPregnancyDiag = true;
						break;
					default:
						break;
				}
			}
			var func = function (node) {
				while (node) {
					switch (node.id) {
						case 'born_data':
							if (!(getRegionNick() == 'ufa' && !olderThan28Days) && (olderThan365Days || parentWin.editPersonNewBorn == 0)){
								node.disable();
							} else {
								node.enable();
								if (getRegionNick() == 'ufa') {
									Ext.Ajax.request({
										callback: function (options, success, response) {
											if (success) {
												var response_obj = Ext.util.JSON.decode(response.responseText);
												var PersonNewBorn = (response_obj.length > 0) ? response_obj[0] : {};

												if (!Ext.isEmpty(PersonNewBorn.PersonNewBorn_Weight))
													that.NewBorn_Weight = PersonNewBorn.PersonNewBorn_Weight;
												else
													that.NewBorn_Weight = 0;
											}
											else {
												sw.swMsg.alert('Ошибка', 'При загрузке сведений из специфики новорожденного');
											}
										},
										params: {
											Person_id: base_form.findField('Person_id').getValue(),
											EvnPS_id: parentWin.EvnPS_id,
											EvnSection_id: base_form.findField('EvnSection_id').getValue()
										},
										url: '/?c=PersonNewBorn&m=loadPersonNewBornData'
									});
								}
							}
							break;
						case 'PersonPregnancy':
							if (!male && !parentWin.childPS && olderThanOneYear && isPregnancyDiag) {
								node.enable();
								node.expand();
								node.leaf = false;
							} else {
								node.disable();
								node.collapse();
								node.leaf = true;
							}
							node.ui.updateExpandIcon();
							break;
					}
					node = node.nextSibling;
				}
			};
			var loadTree = function (forceLoad) {
				if (!parentWin.treeLoaded || forceLoad) {
					parentWin.treeLoaded = true;
					tree.getLoader().load(tree.getRootNode(), function () {
						func(tree.getRootNode().firstChild);
					});
				} else {
					func(tree.getRootNode().firstChild);
				}
			};
			if (isOnkoDiag && isDebug()) {
				//panel.hide();
				panel.setHeight(200);
			} else {
				if (!forbidResetSpecific) {
					panel.show();
					tree.fireEvent('click', tree.getRootNode());
					tree.setWidth(200);
					panel.doLayout();
				}

				if (isPregnancyDiag) {
					parentWin.getPregnancyPersonRegister(function () {
						if (tree.getLoader().baseParams.PersonRegister_id != parentWin.PersonRegister_id) {
							if (parentWin.WizardPanel && !forbidResetSpecific) {
								var category = parentWin.WizardPanel.getCurrentCategory();
								if (category) {
									category.data.clear();
									parentWin.WizardPanel.resetCurrentCategory(true);
								}
							}
							if (parentWin.PersonRegister_id) {
								tree.getLoader().baseParams.PersonRegister_id = parentWin.PersonRegister_id;
							}
							loadTree(true);
						} else {
							loadTree();
						}
					});
				} else {
					loadTree();
				}
			}
		}.createDelegate(this);
		this.specificsPanel = this.findById(this.id + '_SpecificsPanel');
		this.specificsTree = this.findById(this.id + '_SpecificsTree');
		this.specificsFormsPanel = this.findById(this.id + '_SpecificFormsPanel');

	},
	showRankinScale: function () {
		if (getRegionNick().inlist(['krym', 'perm', 'ufa', 'adygeya'])) {
			var base_form = this.findById('EvnSectionEditForm').getForm();
			if (base_form.findField('Diag_id').getFieldValue('DiagFinance_IsRankin') && base_form.findField('Diag_id').getFieldValue('DiagFinance_IsRankin') == 2) {
				base_form.findField('RankinScale_id').showContainer();
				base_form.findField('RankinScale_id').setAllowBlank(false);
				if (!getRegionNick().inlist(['adygeya'])) {
					base_form.findField('EvnSection_InsultScale').showContainer();
					base_form.findField('EvnSection_InsultScale').setAllowBlank(false);
				}
				if (getRegionNick() == 'ufa') {
					base_form.findField('EvnSection_NIHSSAfterTLT').showContainer();
					base_form.findField('EvnSection_NIHSSAfterTLT').setAllowBlank(true);
					if (!Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())) {
						base_form.findField('EvnSection_NIHSSLeave').showContainer();
						base_form.findField('EvnSection_NIHSSLeave').setAllowBlank(false);
					} else {
						base_form.findField('EvnSection_NIHSSLeave').hideContainer();
						base_form.findField('EvnSection_NIHSSLeave').setValue(null);
						base_form.findField('EvnSection_NIHSSLeave').setAllowBlank(true);
					}
				}
				if (!Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())) {
					base_form.findField('RankinScale_sid').showContainer();
					base_form.findField('RankinScale_sid').setAllowBlank(false);
				} else {
					base_form.findField('RankinScale_sid').hideContainer();
					base_form.findField('RankinScale_sid').clearValue();
					base_form.findField('RankinScale_sid').setAllowBlank(true);
				}
			} else {
				base_form.findField('RankinScale_id').hideContainer();
				base_form.findField('RankinScale_id').clearValue();
				base_form.findField('RankinScale_id').setAllowBlank(true);
				base_form.findField('EvnSection_InsultScale').hideContainer();
				base_form.findField('EvnSection_InsultScale').setValue(null);
				base_form.findField('EvnSection_InsultScale').setAllowBlank(true);
				base_form.findField('EvnSection_NIHSSAfterTLT').hideContainer();
				base_form.findField('EvnSection_NIHSSAfterTLT').setValue(null);
				base_form.findField('EvnSection_NIHSSAfterTLT').setAllowBlank(true);
				base_form.findField('EvnSection_NIHSSLeave').hideContainer();
				base_form.findField('EvnSection_NIHSSLeave').setValue(null);
				base_form.findField('EvnSection_NIHSSLeave').setAllowBlank(true);
				base_form.findField('RankinScale_sid').hideContainer();
				base_form.findField('RankinScale_sid').clearValue();
				base_form.findField('RankinScale_sid').setAllowBlank(true);
			}
		}
	},
	showCardioFields: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code')
		if (diag_code && (diag_code.substr(0, 3).inlist(['I21', 'I22', 'I24']) || diag_code == 'I20.0')) {
			this.findById(this.id + '_CardioFields').show();
			base_form.findField('EvnSection_IsCardioCheck').setValue(1);
		} else {
			this.findById(this.id + '_CardioFields').hide();
			base_form.findField('EvnSection_IsCardioCheck').setValue(0);
		}
	},
	showSTField: function () {
		var
			base_form = this.findById('EvnSectionEditForm').getForm(),
			dateX = new Date(2017, 8, 1), // 01.09.2017
			Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
			EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue(),
			EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue(),
			LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
			result = false;

		if (
			getRegionNick() == 'kareliya'
			&& Diag_Code && (Diag_Code.substr(0, 3).inlist(['I21', 'I22', 'I24']) || Diag_Code == 'I20.0')
			&& LpuUnitType_SysNick == 'stac'
			// @task https://redmine.swan.perm.ru//issues/110323
			&& (
				(!Ext.isEmpty(EvnSection_setDate) && EvnSection_setDate >= dateX)
				|| (!Ext.isEmpty(EvnSection_disDate) && EvnSection_disDate >= dateX)
			)
		) {
			base_form.findField('EvnSection_IsST').setAllowBlank(false);
			base_form.findField('EvnSection_IsST').setContainerVisible(true);
			result = true;
		}
		else {
			base_form.findField('EvnSection_IsST').setAllowBlank(true);
			base_form.findField('EvnSection_IsST').setContainerVisible(false);
		}

		return result;
	},
	layout: 'border',
	listeners: {
		'hide': function (win) {
			win.onHide({
				EvnUslugaGridIsModified: win.EvnUslugaGridIsModified
			});
		},
		'maximize': function (win) {
			win.findById('ESecEF_EvnSectionPanel').doLayout();
			win.findById('ESecEF_EvnUslugaPanel').doLayout();
			win.findById(win.id + 'ESecEF_EvnLeavePanel').doLayout();
			win.findById('ESecEF_EvnDiagPSPanel').doLayout();
			win.findById('ESecEF_EvnSectionDrugPSLinkPanel').doLayout();

			/*if ( getRegionNick().inlist([ 'ufa' ]) ) {
				win.findById('ESecEF_EvnSectionNarrowBedPanel').doLayout();
			}*/

			if (!win.specificsPanel.hidden) {
				win.specificsPanel.doLayout();
				win.specificsPanel.collapsed = true;
			}
			/*
			if (!win.SpecThreatPanel.hidden) {
				win.SpecThreatPanel.doLayout();
			}
			if (!win.MorbusHepatitisSpec.hidden) {
				win.MorbusHepatitisSpec.doLayout();
			}
			*/

		},
		'restore': function (win) {
			win.fireEvent('maximize', win);
		}
	},
	leaveTypeFedFilter: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		if (getRegionNick().inlist(['buryatiya', 'penza', 'pskov', 'vologda'])) {
			var LeaveTypeFed_id = base_form.findField('LeaveTypeFed_id').getValue();

			var fedIdList = new Array();

			// Получаем список доступных исходов из федерального справочника
			base_form.findField('LeaveType_id').getStore().each(function (rec) {
				if (!Ext.isEmpty(rec.get('LeaveType_fedid')) && !rec.get('LeaveType_fedid').toString().inlist(fedIdList)) {
					fedIdList.push(rec.get('LeaveType_fedid').toString());
				}
			});

			base_form.findField('LeaveTypeFed_id').clearFilter();
			base_form.findField('LeaveTypeFed_id').lastQuery = '';

			var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');

			if (!Ext.isEmpty(LpuUnitType_SysNick)) {
				if (LpuUnitType_SysNick == 'stac') {
					// круглосуточный
					base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_id').toString().inlist(fedIdList)
							&& rec.get('LeaveType_Code') > 100
							&& rec.get('LeaveType_Code') < 200
						);
					});
				} else {
					// https://redmine.swan.perm.ru/issues/18318
					base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
						return (
							rec.get('LeaveType_id').toString().inlist(fedIdList)
							&& rec.get('LeaveType_Code') > 200
							&& rec.get('LeaveType_Code') < 300
							&& (getRegionNick() == 'buryatiya' || !(LpuUnitType_SysNick.inlist(['dstac', 'hstac']) && rec.get('LeaveType_Code').toString().inlist(['207', '208'])))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
					return (rec.get('LeaveType_id').toString().inlist(fedIdList));
				});
			}

			if (!Ext.isEmpty(LeaveTypeFed_id)) {
				var index = base_form.findField('LeaveTypeFed_id').getStore().findBy(function (rec) {
					return (rec.get('LeaveType_id') == LeaveTypeFed_id);
				});

				if (index == -1) {
					base_form.findField('LeaveTypeFed_id').clearValue();
					base_form.findField('LeaveTypeFed_id').fireEvent('change', base_form.findField('LeaveTypeFed_id'));
				}
			}
		}
	},

	leaveTypeFilter: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var LeaveType_id = base_form.findField('LeaveType_id').getValue();
		var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');

		var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();
		var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();

		base_form.findField('LeaveType_id').clearFilter();
		base_form.findField('LeaveType_id').lastQuery = '';

		if (getRegionNick().inlist(['kareliya', 'krasnoyarsk', 'krym', 'msk', 'yaroslavl', 'adygeya', 'yakutiya'])) {

			if (!Ext.isEmpty(LpuUnitType_SysNick)) {
				if (LpuUnitType_SysNick == 'stac') {
					base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
						if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
							return false;
						}
						if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
							return false;
						}

						return (
							rec.get('LeaveType_Code') > 100
							&& rec.get('LeaveType_Code') < 200
							&& !(rec.get('LeaveType_Code').toString().inlist(['111', '112', '113', '114', '115']))
						);
					});
				} else {
					base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
						if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
							return false;
						}
						if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
							return false;
						}

						return (
							rec.get('LeaveType_Code') > 200
							&& rec.get('LeaveType_Code') < 300
							&& !(!getRegionNick().inlist(['kareliya', 'adygeya', 'yakutiya']) && LpuUnitType_SysNick.inlist(['dstac', 'hstac']) && rec.get('LeaveType_Code').toString().inlist(['207', '208']))
							&& !(rec.get('LeaveType_Code').toString().inlist(['210', '211', '212', '213', '215']))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
					if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
						return false;
					}
					if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
						return false;
					}

					return (
						!(rec.get('LeaveType_Code').toString().inlist(['111', '112', '113', '114', '115', '210', '211', '212', '213', '215']))
					);
				});
			}

			if (!Ext.isEmpty(LeaveType_id)) {
				var index = base_form.findField('LeaveType_id').getStore().findBy(function (rec) {
					return (rec.get('LeaveType_id') == LeaveType_id);
				});

				if (index == -1) {
					base_form.findField('LeaveType_id').clearValue();
					base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'));
				}
			}
		} else {
			base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
				if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
					return false;
				}
				if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
					return false;
				}
				return true;
			});
		}
	},
	leaveCauseFilter: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		if (getRegionNick().inlist(['buryatiya', 'kareliya', 'krasnoyarsk', 'krym', 'penza', 'pskov', 'vologda', 'yaroslavl'])) {
			var oldValue = base_form.findField('LeaveCause_id').getValue();

			base_form.findField('LeaveCause_id').clearFilter();
			base_form.findField('LeaveCause_id').lastQuery = '';

			switch (base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick')) {
				case 'stac': // Круглосуточный стационар
					base_form.findField('LeaveCause_id').getStore().filterBy(function (rec) {
						return (!rec.get('LeaveCause_Code').inlist([210, 211, 212]));
					});
					break;

				default:
					base_form.findField('LeaveCause_id').getStore().filterBy(function (rec) {
						return (rec.get('LeaveCause_Code').inlist([1, 6, 7, 27, 28, 29, 210, 211, 212]));
					});
					break;
			}

			var index = base_form.findField('LeaveCause_id').getStore().findBy(function (rec) {
				return (rec.get('LeaveCause_id') == oldValue);
			});

			if (index == -1) {
				base_form.findField('LeaveCause_id').clearValue();
			}

			if (base_form.findField('LeaveCause_id').getStore().getCount() == 1) {
				base_form.findField('LeaveCause_id').setValue(base_form.findField('LeaveCause_id').getStore().getAt(0).get('LeaveCause_id'));
			}
		}
	},
	resultDeseaseFilter: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		if (getRegionNick().inlist(['astra', 'buryatiya', 'kareliya', 'krasnoyarsk', 'krym', 'penza', 'pskov', 'vologda', 'msk', 'yaroslavl', 'adygeya', 'yakutiya'])) {
			var oldValue = base_form.findField('ResultDesease_id').getValue();
			base_form.findField('ResultDesease_id').clearFilter();
			base_form.findField('ResultDesease_id').lastQuery = '';
			if (base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 2) {
				// круглосуточный
				base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
					return (rec.get('ResultDesease_Code') > 100 && rec.get('ResultDesease_Code') < 200);
				});
			} else {
				base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
					return (rec.get('ResultDesease_Code') > 200 && rec.get('ResultDesease_Code') < 300);
				});
			}

			var index = base_form.findField('ResultDesease_id').getStore().findBy(function (rec) {
				return (rec.get('ResultDesease_id') == oldValue);
			});

			if (index == -1) {
				base_form.findField('ResultDesease_id').clearValue();
			}
			else {
				base_form.findField('ResultDesease_id').setValue(oldValue);
			}
		}
	},
	checkLpuUnitType: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var Person_Age = swGetPersonAge(this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());

		if (base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 2 && Person_Age != -1) {
			base_form.findField('EvnSection_IsAdultEscort').showContainer();
			if (Ext.isEmpty(base_form.findField('EvnSection_IsAdultEscort').getValue())) {
				if (Person_Age < 4 && (getRegionNick() != 'kareliya' || !this.childPS)) {
					base_form.findField('EvnSection_IsAdultEscort').setValue(2);
				}
				else {
					base_form.findField('EvnSection_IsAdultEscort').setValue(1);
				}
				if(getRegionNick() == 'vologda' && this.Person_Birthday_date == this.EvnPS_setDate_date){
					base_form.findField('EvnSection_IsAdultEscort').setValue(1);
				}
			}
			if (getRegionNick() == 'kareliya') {
				base_form.findField('EvnSection_CoeffCTP').showContainer();
			}
		} else {
			base_form.findField('EvnSection_IsAdultEscort').hideContainer();
			base_form.findField('EvnSection_IsAdultEscort').clearValue();
			if (getRegionNick() == 'kareliya') {
				base_form.findField('EvnSection_CoeffCTP').hideContainer();
			}
		}

		base_form.findField('EvnSection_IsAdultEscort').fireEvent('change', base_form.findField('EvnSection_IsAdultEscort'), base_form.findField('EvnSection_IsAdultEscort').getValue());

		if (getRegionNick() == 'astra') {
			//Дневной стационар при стационаре или дневной стационар при поликлинике (АПУ)
			if ((base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 3) || ((base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 5))) {
				base_form.findField('EvnSection_IsMeal').setContainerVisible(true);
			} else {
				base_form.findField('EvnSection_IsMeal').setContainerVisible(false);
			}
		}
		else {
			base_form.findField('EvnSection_IsMeal').setContainerVisible(false);
		}
		this.leaveTypeFedFilter();
		this.leaveTypeFilter();
		this.leaveCauseFilter();
		this.resultDeseaseFilter();
	},
	loadMes2Combo: function (mes2_id, selectIfOne) {
		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') {
			var base_form = this.findById('EvnSectionEditForm').getForm();
			var diag_id = base_form.findField('Diag_id').getValue();

			if (!diag_id || Ext.isEmpty(diag_id)) {
				return false;
			}

			base_form.findField('Mes2_id').clearValue();
			base_form.findField('Mes2_id').getStore().removeAll();

			base_form.findField('Mes2_id').getStore().load({
				callback: function () {
					var record = null;

					// Записей нет
					if (base_form.findField('Mes2_id').getStore().getCount() == 0) {
					}
					else {
						// Если запись одна
						if (base_form.findField('Mes2_id').getStore().getCount() == 1 && selectIfOne) {
							record = base_form.findField('Mes2_id').getStore().getAt(0);
						}
						// Запись, соответствующая старому значению
						else {
							record = base_form.findField('Mes2_id').getStore().getById(mes2_id);
						}
					}

					if (record && !base_form.findField('Mes2_id').disabled) {
						base_form.findField('Mes2_id').setValue(record.get('Mes2_id'));
						base_form.findField('Mes2_id').fireEvent('change', base_form.findField('Mes2_id'), record.get('Mes2_id'));
					}
				}.createDelegate(this),
				params: {
					Diag_id: diag_id
				}
			});
		}
	},
	hasDrugTherapySchemeLinks: false,
	hasRehabScaleLinks: false,
	hasSofaLinks: false,
	checkMesOldUslugaComplexFields: function () {
		var win = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();

		if (win.blockLoadKSGKPGKOEF) {
			return; // блокируем загрузку на время загрузки формы, аналогично блокированию загрузки КСГ
		}

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
						win.hasDrugTherapySchemeLinks = false;
						win.hasRehabScaleLinks = false;
						win.hasSofaLinks = false;

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

						if (result.MesDopIds) {
							base_form.findField('MesDop_ids').lastQuery = '';
							base_form.findField('MesDop_ids').getStore().filterBy(function (rec) {
								return rec.get('MesDop_id').inlist(result.MesDopIds);
							});
							base_form.findField('MesDop_ids').setBaseFilter(function (rec) {
								return rec.get('MesDop_id').inlist(result.MesDopIds);
							});

							var MesDop_ids = base_form.findField('MesDop_ids').getValue();
							if (!Ext.isEmpty(MesDop_ids)) {
								var mesDopIdsArr = MesDop_ids.toString().split(',');
								for(k in mesDopIdsArr) {
									if (typeof mesDopIdsArr[k] != 'function' && !mesDopIdsArr[k].inlist(result.MesDopIds)) {
										base_form.findField('MesDop_ids').clearValue();
										break;
									}
								}
							}
						}

						if (result.hasRehabScaleLinks) {
							win.hasRehabScaleLinks = true;
						}

						if (result.hasSofaLinks) {
							win.hasSofaLinks = true;
						}

						win.refreshFieldsVisibility(['RehabScale_id', 'EvnSection_SofaScalePoints']);
					}
				}
			}.createDelegate(this),
			params: {
				EvnSection_setDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
				EvnSection_disDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'),
				LpuUnitType_id: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_id'),
				EvnSection_id: base_form.findField('EvnSection_id').getValue(),
				Diag_id: base_form.findField('Diag_id').getValue()
			},
			url: '/?c=EvnSection&m=checkMesOldUslugaComplexFields'
		});
	},
	// определение КСГ/КПГ/коэффициента
	MesTariff_id: null,
	blockLoadKSGKPGKOEF: false, // признак блокирования подгрузки КСГ (например при открытии на редактирование достаточно грузить КСГ 1 раз)
	firstLoadKSGKPGKOEF: true, // признак загрузки КСГ в первый раз
	firstTimeLoadedKSGKPGKOEF: false,
	loadEvnSectionKSGGrid: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		if (!Ext.isEmpty(base_form.findField('EvnSection_id').getValue())) {
			this.EvnSectionKSGGrid.loadData({
				globalFilters: {
					EvnSection_id: base_form.findField('EvnSection_id').getValue()
				}
			});
		} else {
			this.EvnSectionKSGGrid.removeAll();
		}
	},
	loadKSGKPGKOEF: function (byField) {
		var win = this;
		if (win.blockLoadKSGKPGKOEF) {
			return;
		}

		var needCheckChangeKSG = false;
		if (win.firstLoadKSGKPGKOEF) {
			needCheckChangeKSG = true;
			win.firstTimeLoadedKSGKPGKOEF = true;
		}
		win.firstLoadKSGKPGKOEF = false;

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');
		var DrugTherapyScheme_ids = this.DrugTherapySchemePanel.getIds();

		if (getRegionNick().inlist(['astra'])) {
			// грузим комбобокс с КСГ/КПГ
			var MesTariff_id = win.savedMesTariff_id; // берём сохранённый MesTariff
			base_form.findField('EvnSection_KSG').setValue('');
			base_form.findField('EvnSection_KSG').fireEvent('change', base_form.findField('EvnSection_KSG'), base_form.findField('EvnSection_KSG').getValue());
			base_form.findField('EvnSection_KPG').setValue('');
			base_form.findField('EvnSection_KOEF').setValue('');
			base_form.findField('Mes_rid').getStore().load({
				params: {
					EvnSection_setDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
					EvnSection_disDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'),
					Person_id: base_form.findField('Person_id').getValue(),
					EvnSection_id: base_form.findField('EvnSection_id').getValue(),
					PayType_id: base_form.findField('PayType_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue(),
					EvnSection_pid: base_form.findField('EvnSection_pid').getValue(),
					LpuSection_id: base_form.findField('LpuSection_id').getValue(),
					LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
					DrugTherapyScheme_ids: DrugTherapyScheme_ids,
					MesDop_ids: base_form.findField('MesDop_ids').getValue(),
					RehabScale_id: base_form.findField('RehabScale_id').getValue(),
					CureResult_id: base_form.findField('CureResult_id').getValue(),
					EvnSection_SofaScalePoints: base_form.findField('EvnSection_SofaScalePoints').getValue()
				},
				callback: function () {
					var defaultMes_id = null;
					var maxMes_id = null;
					var maxMesTariff_Value = '';
					var maxMesKSG_id = null;
					var maxMesKSGTariff_Value = '';
					base_form.findField('Mes_rid').getStore().each(function (record) {
						if (record.get('MesType_id') == 2) {
							base_form.findField('Mes_sid').setValue(record.get('Mes_id'));
							base_form.findField('EvnSection_KSG').setValue(record.get('Mes_Code'));
							base_form.findField('EvnSection_KSG').fireEvent('change', base_form.findField('EvnSection_KSG'), base_form.findField('EvnSection_KSG').getValue());
						}
						if (record.get('MesType_id') == 3) {
							base_form.findField('Mes_tid').setValue(record.get('Mes_id'));
							base_form.findField('EvnSection_KSG').setValue(record.get('Mes_Code'));
							base_form.findField('EvnSection_KSG').fireEvent('change', base_form.findField('EvnSection_KSG'), base_form.findField('EvnSection_KSG').getValue());
						}
						if (record.get('MesType_id') == 4) {
							base_form.findField('Mes_kid').setValue(record.get('Mes_id'));
							base_form.findField('EvnSection_KPG').setValue(record.get('Mes_Code'));
						}
						if (record.get('Mes_IsDefault') && record.get('Mes_IsDefault') == 2) {
							defaultMes_id = record.get('Mes_id');
						}
						// определяем максимальное КСГ/КПГ
						if (Ext.isEmpty(maxMesTariff_Value) || record.get('MesTariff_Value') > maxMesTariff_Value) {
							maxMes_id = record.get('Mes_id');
							maxMesTariff_Value = record.get('MesTariff_Value');
						}
						// определяем максимальное КСГ
						if (record.get('MesType_id') != 4) {
							if (Ext.isEmpty(maxMesKSGTariff_Value) || record.get('MesTariff_Value') > maxMesKSGTariff_Value) {
								maxMesKSG_id = record.get('Mes_id');
								maxMesKSGTariff_Value = record.get('MesTariff_Value');
							}
						}
					});

					// ищем в сторе, если есть выбираем
					var record = false;
					if (!Ext.isEmpty(MesTariff_id)) {
						record = base_form.findField('Mes_rid').getStore().getAt(base_form.findField('Mes_rid').getStore().findBy(function (rec) {
							return rec.get('MesTariff_id') == MesTariff_id;
						}));
					}
					if (record) {
						base_form.findField('Mes_rid').setValue(record.get('Mes_id'));
						base_form.findField('Mes_rid').fireEvent('change', base_form.findField('Mes_rid'), base_form.findField('Mes_rid').getValue());
					} else {
						if (!Ext.isEmpty(defaultMes_id)) {
							maxMes_id = defaultMes_id; // если есть значение по умолчанию пришедшее с сервера, то выбираем его
						} else if (!Ext.isEmpty(maxMesKSG_id)) {
							maxMes_id = maxMesKSG_id; // если есть КСГ, то ставим КСГ, а не КПГ.
						}
						base_form.findField('Mes_rid').setValue(maxMes_id);
						base_form.findField('Mes_rid').fireEvent('change', base_form.findField('Mes_rid'), base_form.findField('Mes_rid').getValue());
					}
					if (base_form.findField('Mes_rid').getStore().getCount() > 0) {
						base_form.findField('Mes_rid').setAllowBlank(false);
					} else {
						base_form.findField('Mes_rid').setAllowBlank(true);
					}
					win.firstTimeLoadedKSGKPGKOEF = false;
				}
			});
		}
		if (
			(getRegionNick().inlist(['perm']) && byField == 'button') // определяем только если нажали на кнопку, чтобы не грузить сервак.
			|| getRegionNick().inlist(['buryatiya', 'penza', 'vologda'])
		) {
			// грузим комбобокс с КСГ/КПГ
			var MesTariff_id = win.savedMesTariff_id; // берём сохранённый MesTariff
			base_form.findField('EvnSection_KSG').setValue('');
			base_form.findField('EvnSection_KSG').fireEvent('change', base_form.findField('EvnSection_KSG'), base_form.findField('EvnSection_KSG').getValue());
			base_form.findField('EvnSection_KPG').setValue('');
			base_form.findField('EvnSection_KOEF').setValue('');
			win.getLoadMask('Расчёт КСГ...').show();
			base_form.findField('Mes_rid').getStore().load({
				params: {
					EvnSection_setDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
					EvnSection_disDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'),
					Person_id: base_form.findField('Person_id').getValue(),
					EvnSection_id: base_form.findField('EvnSection_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue(),
					PayType_id: base_form.findField('PayType_id').getValue(),
					HTMedicalCareClass_id: base_form.findField('HTMedicalCareClass_id').getValue(),
					DiagPriem_id: win.DiagPriem_id,
					EvnSection_IndexRep: base_form.findField('EvnSection_IndexRep').getValue(),
					EvnSection_IndexRepInReg: base_form.findField('EvnSection_IndexRepInReg').getValue(),
					EvnSection_pid: base_form.findField('EvnSection_pid').getValue(),
					LpuSection_id: base_form.findField('LpuSection_id').getValue(),
					LpuSectionBedProfile_id: base_form.findField('LpuSectionBedProfile_id').getValue(),
					LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
					LpuUnitType_id: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_id'),
					DrugTherapyScheme_ids: DrugTherapyScheme_ids,
					MesDop_ids: base_form.findField('MesDop_ids').getValue(),
					RehabScale_id: base_form.findField('RehabScale_id').getValue(),
					CureResult_id: base_form.findField('CureResult_id').getValue(),
					EvnSection_SofaScalePoints: base_form.findField('EvnSection_SofaScalePoints').getValue()
				},
				callback: function () {
					win.getLoadMask().hide();

					var defaultMes_id = null;
					base_form.findField('Mes_rid').getStore().each(function (record) {
						base_form.findField('EvnSection_KSG').setValue(record.get('Mes_Code'));
						base_form.findField('EvnSection_KSG').fireEvent('change', base_form.findField('EvnSection_KSG'), base_form.findField('EvnSection_KSG').getValue());

						if (record.get('Mes_IsDefault') && record.get('Mes_IsDefault') == 2) {
							defaultMes_id = record.get('Mes_id');
						}
					});

					// ищем в сторе, если есть выбираем
					var record = false;
					if (!Ext.isEmpty(MesTariff_id)) {
						record = base_form.findField('Mes_rid').getStore().getAt(base_form.findField('Mes_rid').getStore().findBy(function (rec) {
							return rec.get('MesTariff_id') == MesTariff_id;
						}));
					}
					if (record) {
						base_form.findField('Mes_rid').setValue(record.get('Mes_id'));
						base_form.findField('Mes_rid').fireEvent('change', base_form.findField('Mes_rid'), base_form.findField('Mes_rid').getValue());
					} else {
						base_form.findField('Mes_rid').setValue(defaultMes_id);
						base_form.findField('Mes_rid').fireEvent('change', base_form.findField('Mes_rid'), base_form.findField('Mes_rid').getValue());
					}
					if (base_form.findField('Mes_rid').getStore().getCount() > 0) {
						base_form.findField('Mes_rid').setAllowBlank(false);
					} else {
						base_form.findField('Mes_rid').setAllowBlank(true);
					}
					win.firstTimeLoadedKSGKPGKOEF = false;
				}
			});
		}
		if (getRegionNick().inlist(['ufa', 'kareliya', 'kaluga', 'kz', 'krasnoyarsk', 'krym', 'pskov', 'khak', 'msk', 'yaroslavl', 'adygeya'])) {
			// запрос, передаём EvnSection_id, Diag_id, LpuSectionProfile_id
			if (win.ksgRequestId) {
				Ext.Ajax.abort(win.ksgRequestId); // прервыем предыдущий, если есть
			}
			win.ksgRequestId = Ext.Ajax.request({
				callback: function (options, success, response) {
					win.ksgRequestId = false;
					if (response.responseText) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success) {
							base_form.findField('EvnSection_KSG').setValue(result.KSG);
							base_form.findField('EvnSection_KSG').fireEvent('change', base_form.findField('EvnSection_KSG'), base_form.findField('EvnSection_KSG').getValue());
							if (getRegionNick() == 'pskov') {
								base_form.findField('EvnSection_MesOldUslugaComplexLink_Number').setValue(result.MesOldUslugaComplexLink_Number);
							}
							base_form.findField('EvnSection_KPG').setValue(result.KPG);
							base_form.findField('EvnSection_KOEF').setValue(result.KOEF);
							base_form.findField('EvnSection_KPGKOEF').setValue(result.KPGKOEF);
							base_form.findField('MesOldUslugaComplex_id').setValue(result.MesOldUslugaComplex_id || null);
							/*if (getRegionNick() == 'pskov') {
								base_form.findField('UslugaComplex_id').setValue(result.UslugaComplex_id);
							}*/

							base_form.findField('Mes_tid').setValue(result.Mes_tid);
							base_form.findField('Mes_sid').setValue(result.Mes_sid);
							base_form.findField('Mes_kid').setValue(result.Mes_kid);
							base_form.findField('MesTariff_id').setValue(result.MesTariff_id);
							base_form.findField('MesTariff_sid').setValue(result.MesTariff_sid);

							if (result.IsPaid && result.IsPaid == 2 && result.MesChanged) {
								// выводим 'Случай оплачен, КСГ не пересчитано'
								win.findById(win.id + 'ESecEF_KsgNotRecalc').show();
							} else {
								win.findById(win.id + 'ESecEF_KsgNotRecalc').hide();
							}
						}

						if (!Ext.isEmpty(result.Alert_Code)) {
							if (result.Alert_Code == 1) {
								// sw.swMsg.alert('Внимание', 'Не найдено КСГ по операциям');
							}

							if (result.Alert_Code == 2) {
								// sw.swMsg.alert('Внимание', 'Не найдено КСГ по операциям и диагнозу');
							}
						}

						if (win.action != 'add') {
							base_form.findField('EvnSection_KSG').getEl().dom.style.background = "";
							base_form.findField('EvnSection_KPG').getEl().dom.style.background = "";
							base_form.findField('EvnSection_KOEF').getEl().dom.style.background = "";
							// если не добавление, значит уже что то есть в БД, поэтому сравниваем сохранённые данные с расчётными и подсвечиваем поля, если не совпадают

							var needAlertChangeKSG = false;

							if (
								win.savedData && (
									(
										win.savedData.hasOwnProperty('Mes_sid')
										&& (!Ext.isEmpty(base_form.findField('Mes_sid').getValue()) || !Ext.isEmpty(win.savedData.Mes_sid))
										&& base_form.findField('Mes_sid').getValue() != win.savedData.Mes_sid
									)
									|| (
										win.savedData.hasOwnProperty('Mes_tid')
										&& (!Ext.isEmpty(base_form.findField('Mes_tid').getValue()) || !Ext.isEmpty(win.savedData.Mes_tid))
										&& base_form.findField('Mes_tid').getValue() != win.savedData.Mes_tid
									)
								)
							) {
								// светим поле КСГ
								base_form.findField('EvnSection_KSG').getEl().dom.style.background = "#fff2c0";
								needAlertChangeKSG = true;
							}

							if (
								win.savedData
								&& win.savedData.hasOwnProperty('Mes_kid')
								&& (!Ext.isEmpty(base_form.findField('Mes_kid').getValue()) || !Ext.isEmpty(win.savedData.Mes_kid))
								&& base_form.findField('Mes_kid').getValue() != win.savedData.Mes_kid
							) {
								// светим поле КПГ
								base_form.findField('EvnSection_KPG').getEl().dom.style.background = "#fff2c0";
								needAlertChangeKSG = true;
							}

							if (
								win.savedData
								&& win.savedData.hasOwnProperty('MesTariff_id')
								&& (!Ext.isEmpty(base_form.findField('MesTariff_id').getValue()) || !Ext.isEmpty(win.savedData.MesTariff_id))
								&& base_form.findField('MesTariff_id').getValue() != win.savedData.MesTariff_id
							) {
								// светим поле тариф
								base_form.findField('EvnSection_KOEF').getEl().dom.style.background = "#fff2c0";
								needAlertChangeKSG = true;
							}

							if (win.action == 'edit' && needCheckChangeKSG && needAlertChangeKSG) {
								sw.swMsg.alert('Внимание', 'Значение КСГ изменилось. Сохраните движение.');
							}
						}
						win.firstTimeLoadedKSGKPGKOEF = false;
					}
				}.createDelegate(this),
				params: {
					EvnSection_setDate: Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'),
					EvnSection_disDate: Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'),
					Person_id: base_form.findField('Person_id').getValue(),
					EvnSection_id: base_form.findField('EvnSection_id').getValue(),
					PayType_id: base_form.findField('PayType_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue(),
					HTMedicalCareClass_id: base_form.findField('HTMedicalCareClass_id').getValue(),
					DiagPriem_id: win.DiagPriem_id,
					EvnSection_IndexRep: base_form.findField('EvnSection_IndexRep').getValue(),
					EvnSection_IndexRepInReg: base_form.findField('EvnSection_IndexRepInReg').getValue(),
					EvnSection_pid: base_form.findField('EvnSection_pid').getValue(),
					LpuSection_id: base_form.findField('LpuSection_id').getValue(),
					LpuSectionBedProfile_id: base_form.findField('LpuSectionBedProfile_id').getValue(),
					LpuSectionProfile_id: getRegionNick().inlist(['buryatiya', 'kareliya', 'krym', 'penza', 'vologda', 'yaroslavl', 'adygeya']) ? base_form.findField('LpuSectionProfile_id').getValue() : base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'),
					LpuUnitType_id: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_id'),
					DrugTherapyScheme_ids: DrugTherapyScheme_ids,
					MesDop_ids: base_form.findField('MesDop_ids').getValue(),
					RehabScale_id: base_form.findField('RehabScale_id').getValue(),
					CureResult_id: base_form.findField('CureResult_id').getValue(),
					EvnSection_SofaScalePoints: base_form.findField('EvnSection_SofaScalePoints').getValue(),
					EvnSection_IsAdultEscort: base_form.findField('EvnSection_IsAdultEscort').getFieldValue('YesNo_id') || 1
				},
				url: '/?c=EvnSection&m=loadKSGKPGKOEF'
			});
		}
	},
	filterRehabScaleCombo: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');
		base_form.findField('RehabScale_id').lastQuery = '';
		base_form.findField('RehabScale_id').getStore().filterBy(function (rec) {
			if (LpuUnitType_SysNick == 'stac') {
				return rec.get('RehabScale_id').inlist([3, 4, 5, 6]);
			} else {
				return rec.get('RehabScale_id').inlist([2, 3]);
			}
		});
	},
	checkEvnSectionKSGGridIsVisible: function() {
		if (getRegionNick() != 'perm') {
			return;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();

		var onDate = null;
		if (!Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())) {
			onDate = base_form.findField('EvnSection_disDate').getValue();
		} else {
			onDate = base_form.findField('EvnSection_setDate').getValue();
		}

		if (onDate && onDate >= new Date(2019, 0, 1)) {
			this.EvnSectionKSGGrid.show();
			this.EvnSectionKSGGrid.doLayout();

			base_form.findField('Mes_rid').hideContainer();
			base_form.findField('EvnSection_KOEF').hideContainer();
			base_form.findField('EvnSection_CoeffCTP').hideContainer();
			this.recalcKSGButton.hide();
		} else {
			this.EvnSectionKSGGrid.hide();

			base_form.findField('Mes_rid').showContainer();
			base_form.findField('EvnSection_KOEF').showContainer();
			base_form.findField('EvnSection_CoeffCTP').showContainer();
			this.recalcKSGButton.show();
		}
	},
	loadLpuSectionProfileDop: function () {
		var win = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var blockLoadKSGKPGKOEF = this.blockLoadKSGKPGKOEF;

		//if (getRegionNick().inlist([ 'astra', 'kz'])) {
		var oldValue = base_form.findField('LpuSectionProfile_id').getValue();
		var onDate = (!Ext.isEmpty(base_form.findField('EvnSection_setDate').getValue()) ? base_form.findField('EvnSection_setDate').getValue().format('d.m.Y') : getGlobalOptions().date);

		if (getRegionNick() == 'perm' && !Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())) {
			onDate = base_form.findField('EvnSection_disDate').getValue().format('d.m.Y');
		}

		if (!Ext.isEmpty(base_form.findField('LpuSection_id').getValue())) {
			if (
				!base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id
				|| base_form.findField('LpuSection_id').getValue() != base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id
				|| onDate != base_form.findField('LpuSectionProfile_id').getStore().baseParams.onDate
			) {
				base_form.findField('LpuSectionProfile_id').lastQuery = '';
				base_form.findField('LpuSectionProfile_id').getStore().removeAll();
				base_form.findField('LpuSectionProfile_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
				base_form.findField('LpuSectionProfile_id').getStore().baseParams.onDate = onDate;
				base_form.findField('LpuSectionProfile_id').getStore().load({
					callback: function () {
						var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
							return (rec.get('LpuSectionProfile_id') == oldValue);
						});

						if (index == -1) {
							base_form.findField('LpuSectionProfile_id').clearValue();
							win.onLpuSectionProfileChange(blockLoadKSGKPGKOEF);

							// ищем основной
							var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
								return (rec.get('LpuSectionProfile_id') == base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'));
							});
							if (index > -1) {
								// выбираем основной
								base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(index).get('LpuSectionProfile_id'));
								win.onLpuSectionProfileChange(blockLoadKSGKPGKOEF);
							} else {
								// выбираем первый попавшийся
								if (base_form.findField('LpuSectionProfile_id').getStore().getCount() > 0) {
									base_form.findField('LpuSectionProfile_id').setValue(base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id'));
									win.onLpuSectionProfileChange(blockLoadKSGKPGKOEF);
								}
							}
						} else {
							base_form.findField('LpuSectionProfile_id').setValue(oldValue);
							win.onLpuSectionProfileChange(blockLoadKSGKPGKOEF);
						}
					}
				});
			}
		}
		//}
	},
	/** В случае если работа идёт из АРМ врача, исход = смерть, неоходимость экспертизы = да, меняет обязательность полей раздела патологоанатомическая экспертиза и открывает кнопку выписки направления
	 **/
	setPatoMorphGistDirection: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm(),
			_this = this,
			EvnDie_IsAnatom = base_form.findField('EvnDie_IsAnatom').getValue(),
			LeaveTypeFed_id = base_form.findField('LeaveTypeFed_id').getValue(),
			LeaveType_Code = base_form.findField('LeaveType_id').getFieldValue('LeaveType_Code');

		if (!Ext.isEmpty(this.ARMType_id) && this.ARMType_id == 3 && EvnDie_IsAnatom == 2 && (LeaveType_Code == 3 || (getRegionNick() == 'khak' && base_form.findField('ResultDesease_id').getFieldValue('ResultDesease_Code') == 6 && LeaveType_Code == 1))) {
			base_form.findField('EvnDie_expTime').setAllowBlank(true);
			base_form.findField('AnatomWhere_id').setAllowBlank(true);
			base_form.findField('Diag_aid').setAllowBlank(true);
			_this.findById('ESEW_addPatoMorphHistoDirectionButton').disable();
			_this.findById('ESEW_addPatoMorphHistoDirectionButton').show();

			if (_this.needCheckMorfoHistologic) {
				_this.needCheckMorfoHistologic = false;
				var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Проверка возможности выписки направления на патоморфогистологическое исследование..."});
				loadMask.show();
				Ext.Ajax.request({
					params: {
						EvnSection_pid: base_form.findField('EvnSection_pid').getValue()
					},
					failure: function () {
						loadMask.hide();
						sw.swMsg.alert('Ошибка', 'При проверке возможности выписки направления на патоморфогистологическое исследование возникли ошибки');
						return false;
					},
					success: function (response, options) {
						loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						_this.setDisabledMorfoHistologic = result.response;
						_this.findById('ESEW_addPatoMorphHistoDirectionButton').setDisabled(_this.setDisabledMorfoHistologic);
					},
					url: '/?c=EvnDirectionMorfoHistologic&m=checkEvnDirectionMorfoHistologic'
				});
			} else {
				_this.findById('ESEW_addPatoMorphHistoDirectionButton').setDisabled(_this.setDisabledMorfoHistologic);
			}
		} else {
			this.findById('ESEW_addPatoMorphHistoDirectionButton').hide();
			return false;
		}
	},
	addPatoMorphHistoDirection: function () {

		var base_form = this.findById('EvnSectionEditForm').getForm(),
			_this = this,
			params = {},
			diagsFromDirection = [],
			Diag_oid, //осложнение
			seek_oid = true,
			Diag_sid,
			seek_sid = true; //Сопутствующий

		this.findById('ESecEF_EvnDiagPSGrid').getStore().each(function (rec) {
			if (rec.get('DiagSetClass_id') == 3) {
				Diag_sid = seek_sid ? rec.get('Diag_id') : null; // если диагнозов больше 1, поле не заполняем
				seek_sid = false;
			}

			if (rec.get('DiagSetClass_id') == 2) {
				Diag_oid = seek_oid ? rec.get('Diag_id') : null; // если диагнозов больше 1, поле не заполняем
				seek_oid = false;
			}

			diagsFromDirection.push(rec.get('Diag_id'));
		});

		this.findById(this.id + 'ESecEF_AnatomDiagGrid').getGrid().getStore().each(function (rec) {
			diagsFromDirection.push(rec.get('Diag_id'));
		});

		diagsFromDirection.push(base_form.findField('Diag_id').getValue());

		params.action = 'add';
		params.Diag_filter = diagsFromDirection;
		params.UserLpuSection_id = base_form.findField('LpuSection_id').getValue() || null;
		params.UserMedStaffFact_id = getGlobalOptions().CurMedStaffFact_id || null;
		params.formParams = {};
		params.formParams.Person_id = base_form.findField('Person_id').getValue() || null;
		params.formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue() || null;
		params.formParams.Server_id = base_form.findField('Server_id').getValue() || null;
		params.formParams.Diag_id = base_form.findField('Diag_id').getValue() || null;
		params.formParams.EvnPS_id = !Ext.isEmpty(this.EvnPS_id) ? this.EvnPS_id : '';
		params.formParams.Diag_oid = Diag_oid || null;
		params.formParams.Diag_sid = Diag_sid || null;
		params.formParams.EvnPS_Title = (!Ext.isEmpty(this.EvnPS_NumCard) ? (this.EvnPS_NumCard + ', ') : '') + Ext.util.Format.date(this.EvnPS_setDate, 'd.m.Y');
		params.callback = function () {
			_this.needCheckMorfoHistologic = true;
			_this.setPatoMorphGistDirection();
		};

		getWnd('swEvnDirectionMorfoHistologicEditWindow').show(params);
	},
	loadMesCombo: function () {
		if (getRegionNick().inlist(['buryatiya', 'pskov'])) {
			return false;
		}

		var win = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		// текущий мэс
		win.mes_id = base_form.findField('Mes_id').getValue() || win.mes_id;

		var diag_id = base_form.findField('Diag_id').getValue();
		var evn_section_dis_date = base_form.findField('EvnSection_disDate').getValue();
		var evn_section_set_date = base_form.findField('EvnSection_setDate').getValue();
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var EvnSection_id = base_form.findField('EvnSection_id').getValue();

		var allowBlankMes = (getRegionNick().inlist(['astra', 'buryatiya', 'ufa', 'pskov', 'kareliya', 'buryatiya']));

		base_form.findField('Mes_id').clearValue();
		base_form.findField('Mes_id').getStore().removeAll();

		base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), null);

		if (!diag_id || !evn_section_set_date || !lpu_section_id || !person_id) {
			return false;
		}

		base_form.findField('Mes_id').getStore().load({
			callback: function () {
				var index, record;

				base_form.findField('Mes_id').setAllowBlank(allowBlankMes || Ext.isEmpty(evn_section_dis_date));

				// Записей нет
				if (base_form.findField('Mes_id').getStore().getCount() == 0) {
					base_form.findField('Mes_id').setAllowBlank(true);
				}
				else {
					// Если запись одна
					// Регион не РБ
					if (base_form.findField('Mes_id').getStore().getCount() == 1 && getGlobalOptions().region.nick != 'ufa') {
						index = 0;
					}
					// Запись, соответствующая старому значению
					else if (!Ext.isEmpty(win.mes_id)) {
						index = base_form.findField('Mes_id').getStore().findBy(function (rec) {
							return (rec.get('Mes_id') == win.mes_id);
						});
					}

					if (index >= 0) {
						record = base_form.findField('Mes_id').getStore().getAt(index);
					}
				}

				// для Перми: если запись одна и выбрана не по новому условию, то нужно сделать поле необязательным.
				if (getGlobalOptions().region.nick == 'perm' && base_form.findField('Mes_id').getStore().getCount() == 1 && record.get('MesNewUslovie') == 0) {
					base_form.findField('Mes_id').setAllowBlank(true);
				}

				if (typeof record == 'object') {
					base_form.findField('Mes_id').setValue(record.get('Mes_id'));
					base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), record.get('Mes_id'));
				}
				this.recountKoikoDni();
			}.createDelegate(this),
			params: {
				Diag_id: diag_id
				, EvnSection_disDate: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y')
				, EvnSection_setDate: Ext.util.Format.date(evn_section_set_date, 'd.m.Y')
				, LpuSection_id: lpu_section_id
				, Person_id: person_id
				, EvnSection_id: EvnSection_id
			}
		});
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	onCancelAction: function () {
		var wnd = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var params = new Object();
		params.EvnSection_id = base_form.findField('EvnSection_id').getValue();

		// Добавляем проверку на заполнение узких коек для реанимации (Уфа)
		if (this.action == 'edit' && getRegionNick().inlist(['ufa']) && !Ext.isEmpty(params.EvnSection_id) && !Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())
			&& (this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() == 0 || Ext.isEmpty(this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getAt(0).get('EvnSectionNarrowBed_id')))
		) {
			var LpuSection_id = base_form.findField('LpuSection_id').getValue();

			if (Ext.isEmpty(LpuSection_id)) {
				sw.swMsg.alert('Ошибка', 'Не указано отделение');
				return false;
			}

			var index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
				return (rec.get('LpuSection_id') == LpuSection_id);
			});

			if (index >= 0 && base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSectionProfile_Code').inlist(['1035', '2035', '3035'])) {
				sw.swMsg.alert('Ошибка', 'Заполнение узких коек обязательно');
				return false;
			}
		}

		if (wnd.WizardPanel) {
			wnd.WizardPanel.deleteEvnSection = (params.EvnSection_id > 0 && wnd.action == 'add');
			var categories = wnd.WizardPanel.categories;
			var category = null;
			var index = -1;

			var cancelCategory = function () {
				if (category = categories.itemAt(++index)) {
					category.cancelCategory(category, cancelCategory);
				} else {
					wnd.WizardPanel.categoryCanceled = true;
					wnd.onCancelAction();
				}
			}

			if (!wnd.WizardPanel.categoryCanceled) {
				cancelCategory();
				return false;
			}
			delete wnd.WizardPanel.deleteEvnSection;
			delete wnd.WizardPanel.categoryCanceled;
		}

		if (params.EvnSection_id > 0 && this.action == 'add') {
			// удалить случай движения пациента в стационаре
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление случая движения пациента в стационаре..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function (options, success, response) {
					loadMask.hide();

					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						var evnSectionData = [{
							EvnSection_id: params.EvnSection_id,
							deleted: true
						}];
						if (response_obj.success) {
							this.callback({evnSectionData: evnSectionData});
							this.hide();
						}
					} else {
						sw.swMsg.alert('Ошибка', 'При удалении случая движения пациента в стационаре возникли ошибки');
						return false;
					}
				}.createDelegate(this),
				params: {
					Evn_id: params.EvnSection_id
				},
				url: '/?c=Evn&m=deleteEvn'
			});
		} else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,

	openTransfusionForm: function(action) {
		var that = this;

		var grid = this.findById('ESecEF_TransfusionGrid');
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var params = {
			action: action,
			EvnSection_id: base_form.findField('EvnSection_id').getValue(),
			EvnPS_id: this.EvnPS_id
		}

		if (action == 'edit' || action == 'view') {
			var record = this.findById('ESecEF_TransfusionGrid').getSelectionModel().getSelected();
			params.TransfusionFact_id = record.get('TransfusionFact_id');
		}

		if (action == 'edit' || action == 'add') {
			params.callback = function(){
				grid.getStore().removeAll();
				grid.getStore().load({
					params: {
						EvnPS_id : that.EvnPS_id,
						EvnSection_id: that.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
					}
				});
				this.hide();
			}
		}

		if (params.EvnSection_id == 0) {
			this.doSave({
				openChildWindow: function () {
					getWnd('swBloodTransfusion').show({
						action: 'add',
						EvnSection_id: base_form.findField('EvnSection_id').getValue(),
						EvnPS_id: that.EvnPS_id,
						callback: function(){
							grid.getStore().removeAll();
							grid.getStore().load({
								params: {
									EvnPS_id : that.EvnPS_id,
									EvnSection_id: that.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
								}
							});
							this.hide();
						}
					});
				}.createDelegate(this)
			})
		} else {
			getWnd('swBloodTransfusion').show(params);
		}
	},

	onHide: Ext.emptyFn,

	openTransfusionForm: function(action) {
		var that = this;

		var grid = this.findById('ESecEF_TransfusionGrid');
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var params = {
			action: action,
			EvnSection_id: base_form.findField('EvnSection_id').getValue(),
			EvnPS_id: this.EvnPS_id
		}

		if (action == 'edit' || action == 'view') {
			var record = this.findById('ESecEF_TransfusionGrid').getSelectionModel().getSelected();
			params.TransfusionFact_id = record.get('TransfusionFact_id');
		}

		if (action == 'edit' || action == 'add') {
			params.callback = function(){
				grid.getStore().removeAll();
				grid.getStore().load({
					params: {
						EvnPS_id : that.EvnPS_id,
						EvnSection_id: that.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
					}
				});
				this.hide();
			}
		}

		if (params.EvnSection_id == 0) {
			this.doSave({
				openChildWindow: function () {
					getWnd('swBloodTransfusion').show({
						action: 'add',
						EvnSection_id: base_form.findField('EvnSection_id').getValue(),
						EvnPS_id: that.EvnPS_id,
						callback: function(){
							grid.getStore().removeAll();
							grid.getStore().load({
								params: {
									EvnPS_id : that.EvnPS_id,
									EvnSection_id: that.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
								}
							});
							this.hide();
						}
					});
				}.createDelegate(this)
			})
		} else {
			getWnd('swBloodTransfusion').show(params);
		}
	},


	openEvnDiagPSEditWindow: function (action, type) {
		var that = this;
		if (typeof action != 'string' || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}
		else if (typeof type != 'string' || !type.toString().inlist(['die', 'sect'])) {
			return false;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid;
		var formMode;
		var formParams = new Object();
		var params = new Object();

		if (this.action == 'view' && (this.editAnatom == false || type != 'die')) {
			if (action == 'add') {
				return false;
			}
			else if (action == 'edit') {
				action = 'view';
			}
		}

		if (getWnd('swEvnDiagPSEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования диагноза уже открыто');
			return false;
		}

		if (action == 'add') {
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
		}

		switch (type) {
			case 'die':
				formMode = 'local';
				grid = this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid();

				if (action == 'add' || action == 'edit') {


				}
				if (action == 'add') {
					formParams.EvnDiagPS_pid = base_form.findField('EvnDie_id').getValue();

					if (base_form.findField('EvnDie_expDate').getValue()) {
						formParams.EvnDiagPS_setDate = base_form.findField('EvnDie_expDate').getValue();
					}

					if (base_form.findField('EvnDie_expTime').getValue()) {
						formParams.EvnDiagPS_setTime = base_form.findField('EvnDie_expTime').getValue();
					}
				}
				else {
					var selected_record = grid.getSelectionModel().getSelected();

					if (!selected_record || !selected_record.get('EvnDiagPS_id')) {
						return false;
					}

					formParams = selected_record.data;
				}
				break;

			case 'sect':
				formMode = 'remote';
				grid = this.findById('ESecEF_EvnDiagPSGrid');

				if (action == 'add' && base_form.findField('EvnSection_id').getValue() == 0) {
					this.doSave({
						openChildWindow: function () {
							this.openEvnDiagPSEditWindow(action, type);
						}.createDelegate(this)
					});
					return false;
				}

				if (!base_form.findField('Diag_id').getValue()) {
					sw.swMsg.alert('Ошибка', 'Не заполнен основной диагноз');
					return false;
				}
				if (action == 'add' || action == 'edit') {
					params.minDate = Date.parseDate(base_form.findField('EvnSection_setDate').getValue().format('d.m.Y')
						+ ' ' + base_form.findField('EvnSection_setTime').getValue(), 'd.m.Y H:i');

					if (base_form.findField('EvnSection_disDate').getValue() != '') {

						var disTime = base_form.findField('EvnSection_disTime').getValue();
						if (disTime == '') {
							disTime = '00:00'
						}
						params.maxDate = Date.parseDate(base_form.findField('EvnSection_disDate').getValue().format('d.m.Y')
							+ ' ' + disTime, 'd.m.Y H:i');
					}
				}
				if (action == 'add') {
					formParams.EvnDiagPS_id = 0;
					formParams.EvnDiagPS_pid = base_form.findField('EvnSection_id').getValue();
					formParams.EvnDiagPS_setDate = base_form.findField('EvnSection_setDate').getValue();
					formParams.EvnDiagPS_setTime = base_form.findField('EvnSection_setTime').getValue();

				}
				else {
					var selected_record = grid.getSelectionModel().getSelected();

					if (!selected_record || !selected_record.get('EvnDiagPS_id')) {
						return false;
					}

					formParams = selected_record.data;
				}
				break;
		}

		params.action = action;
		params.callback = function (data) {
			if (typeof data != 'object' || typeof data.evnDiagPSData != 'object') {
				return false;
			}

			var record = grid.getStore().getById(data.evnDiagPSData[0].EvnDiagPS_id);

			if (type == 'die') {
				data.evnDiagPSData[0].RecordStatus_Code = 0;

				if (record) {
					if (record.get('RecordStatus_Code') == 1) {
						data.evnDiagPSData[0].RecordStatus_Code = 2;
					}
				}
				else {
					data.evnDiagPSData[0].EvnDiagPS_id = -swGenTempId(grid.getStore());
				}
			}

			if (record) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.evnDiagPSData[0][grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDiagPS_id')) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnDiagPSData, true);
			}

			this.savedMesTariff_id = null; // при манипуляции с услугами сохранённый коэфф уже не учитываем, выставляем КСГ автоматически
			this.loadKSGKPGKOEF();
			this.checkMesOldUslugaComplexFields();
			this.createEvnDiagCopyMenu();
			this.loadHTMedicalCareClassCombo();
			this.loadSpecificsTree();
			this.refreshFieldsVisibility(['EvnSection_BarthelIdx']);
		}.createDelegate(this);
		params.formMode = formMode;
		params.formParams = formParams;
		params.onHide = function () {
			if (typeof selected_record == 'object') {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			}
			else if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
			}
		}.createDelegate(this);
		params.Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname');
		params.type = type;
		params.histClin = "2,3";
		switch(getRegionNick()) {
			case 'kz':
			case 'ufa':
			case 'msk':
				params.histClin = "2,3,6,7";
				break;
			case 'kareliya':
				params.histClin = "2,3,6";
				break;
		}
		params.archiveRecord = this.archiveRecord;
		getWnd('swEvnDiagPSEditWindow').show(params);
	},
	openEvnSectionDrugPSLinkEditWindow: function (action) {
		var that = this;
		if (typeof action != 'string' || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid;
		var formMode;
		var formParams = new Object();
		var params = new Object();

		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			}
			else if (action == 'edit') {
				action = 'view';
			}
		}

		if (getWnd('swEvnSectionDrugPSLinkEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования диагноза уже открыто');
			return false;
		}

		formMode = 'remote';
		grid = this.findById('ESecEF_EvnSectionDrugPSLinkGrid');

		if (action == 'add' && base_form.findField('EvnSection_id').getValue() == 0) {
			this.doSave({
				openChildWindow: function () {
					this.openEvnSectionDrugPSLinkEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		if (action == 'add') {
			formParams.EvnSectionDrugPSLink_id = 0;
			formParams.EvnSection_id = base_form.findField('EvnSection_id').getValue();
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if (!selected_record || !selected_record.get('EvnSectionDrugPSLink_id')) {
				return false;
			}

			formParams = selected_record.data;
		}

		params.action = action;
		params.callback = function (data) {
			if (typeof data != 'object' || typeof data.EvnSectionDrugPSLinkData != 'object') {
				return false;
			}

			var record = grid.getStore().getById(data.EvnSectionDrugPSLinkData[0].EvnSectionDrugPSLink_id);

			if (record) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.EvnSectionDrugPSLinkData[0][grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnSectionDrugPSLink_id')) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.EvnSectionDrugPSLinkData, true);
			}
		}.createDelegate(this);
		params.formMode = formMode;
		params.formParams = formParams;
		params.onHide = function () {
			if (typeof selected_record == 'object') {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			}
			else if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
			}
		}.createDelegate(this);
		params.Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname');
		params.archiveRecord = this.archiveRecord;
		params.MesTariff_id = base_form.findField('MesTariff_id').getValue();
		params.EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();
		getWnd('swEvnSectionDrugPSLinkEditWindow').show(params);
	},
	DataViewStore: function () {
		var ds = this.findById('dataViewDiag').getStore();
		ds.load({
			params: {
				'class': 'EvnDiagPSSect',
				DiagSetClass: 1,
				EvnDiagPS_pid: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
			}
		});

	},		
	DoctorHistoryDataViewStore: function () {
		var ds = this.findById('dataViewDoctorHistory').getStore();
		ds.load({
			params: {
				EvnSection_id: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
			}
		});

	},
	LpuSectionWardHistoryDataViewStore: function () {
		var ds = this.dataViewLpuSectionWardHistory.getStore();
		ds.load({
			params: {
				EvnSection_id: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
			}
		});

	},
	LpuSectionBedProfileHistoryDataViewStore: function () {
		var ds = this.dataViewLpuSectionBedProfileHistory.getStore();
		ds.load({
			params: {
				EvnSection_id: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
			}
		});

	},
	openEvnDiagPSEditWindow2: function (action, type) {
		var that = this;
		if (typeof action != 'string' || !action.toString().inlist(['add'])) {
			return false;
		}
		else if (typeof type != 'string' || !type.toString().inlist(['sect'])) {
			return false;
		}
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid;
		var ds;// DiagPanel
		var formMode;
		var formParams = new Object();
		var params = new Object();
		if (getWnd('swEvnDiagPSEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования диагноза уже открыто');
			return false;
		}
		if (type == 'sect') {

			formMode = 'local';
			grid = this.findById('dataViewDiag');
			ds = this.findById('EvnSectionEditForm').getForm().findField('Diag_id');
			if (!base_form.findField('Diag_id').getValue()) {
				sw.swMsg.alert('Ошибка', 'Не заполнен основной диагноз');
				return false;
			}

			formParams.EvnDiagPS_setDate = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
			formParams.EvnDiagPS_setTime = base_form.findField('EvnSection_setTime').getRawValue();
		}
		params.action = action;
		params.callback = function (data) {
			if (typeof data != 'object' || typeof data.evnDiagPSData != 'object') {
				return false;
			}
			data.evnDiagPSData[0].EvnDiagPS_id = -swGenTempId(grid.getStore());
			data.evnDiagPSData[0].EvnDiagPS_pid = base_form.findField('EvnSection_id').getValue();
			data.evnDiagPSData[0].Person_id = base_form.findField('Person_id').getValue();
			data.evnDiagPSData[0].PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			data.evnDiagPSData[0].Server_id = base_form.findField('Server_id').getValue();
			data.evnDiagPSData[0].RecordStatus_Code = 0;
			data.evnDiagPSData[0].EvnDiagPS_setDate = Ext.util.Format.date(data.evnDiagPSData[0].EvnDiagPS_setDate, 'd.m.Y');

			grid.getStore().loadData(data.evnDiagPSData, true);
			ds.getStore().loadData(data.evnDiagPSData);
			base_form.findField('EvnSection_PhaseDescr').setValue(data.evnDiagPSData[0].EvnDiagPS_PhaseDescr);
			base_form.findField('DiagSetPhase_id').setValue(data.evnDiagPSData[0].DiagSetPhase_id);
			base_form.findField('EvnDiagPS_id').setValue(data.evnDiagPSData[0].EvnDiagPS_id);
			ds.getStore().each(function (record) {
				ds.fireEvent('select', ds, record, 0);
			});
			that.filterDS();
			base_form.findField('Diag_id').focus(true);
		}.createDelegate(this);
		params.formMode = formMode;
		params.formParams = formParams;
		params.Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname');
		params.type = type;
		params.histClin = "1";
		getWnd('swEvnDiagPSEditWindow').show(params);
	},
	openEvnSectionNarrowBedEditWindow: function (action) {
		if (action != 'add' && action != 'edit' && action != 'view') {
			return false;
		}

		if (getWnd('swEvnSectionNarrowBedEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования профилей коек уже открыто');
			return false;
		}

		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			}
			else if (action == 'edit') {
				action = 'view';
			}
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var formParams = new Object();
		var grid = this.findById('ESecEF_EvnSectionNarrowBedGrid');
		var maxDate = base_form.findField('EvnSection_disDate').getValue();
		var minDate = base_form.findField('EvnSection_setDate').getValue();
		var params = new Object();
		/*
		 if ( !base_form.findField('LpuSection_id').getValue() ) {
		 return false;
		 }
		 */
		if (((action == 'add' && base_form.findField('EvnSection_id').getValue() == 0)) || this.changedDates == true) {
			this.doSave({
				openChildWindow: function () {
					this.changedDates = false;
					this.openEvnSectionNarrowBedEditWindow(action);
				}.createDelegate(this)
			});
			return false;
		}

		params.action = action;
		params.callback = function (data) {
			if (!data || !data.evnSectionNarrowBedData) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnSectionNarrowBedData[0].EvnSectionNarrowBed_id);

			if (!record) {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnSectionNarrowBed_id')) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData(data.evnSectionNarrowBedData, true);
			}
			else {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function (key, item) {
					grid_fields.push(key);
				});

				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.evnSectionNarrowBedData[0][grid_fields[i]]);
				}

				// Необходимо перезагружать хранилище, так как данные без даты окончания в него не записываются
				grid.getStore().reload();

				record.commit();
			}

			this.loadKSGKPGKOEF();
		}.createDelegate(this);
		params.LpuSection_pid = base_form.findField('LpuSection_id').getValue();
		params.maxDate = maxDate;
		params.minDate = minDate;
		params.onHide = function () {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Surname');

		if (action == 'add') {
			formParams.EvnSectionNarrowBed_id = 0;
			formParams.EvnSectionNarrowBed_pid = base_form.findField('EvnSection_id').getValue();
			formParams.EvnSectionNarrowBed_setDate = base_form.findField('EvnSection_setDate').getValue();
			formParams.EvnSectionNarrowBed_setTime = base_form.findField('EvnSection_setTime').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
			// Для Уфы: При добавлении узких коек, даты копировать из движения, а отделение из исхода госпитализации при переводе в другое отделение
			if (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') {
				formParams.EvnSectionNarrowBed_disDate = base_form.findField('EvnSection_disDate').getValue();
				formParams.EvnSectionNarrowBed_disTime = base_form.findField('EvnSection_disTime').getValue();
				if (base_form.findField('LeaveType_id').getValue() == 5) {
					formParams.LpuSection_id = base_form.findField('LpuSection_oid').getValue();
				} else {
					formParams.LpuSection_id = null;
				}
			}
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if (!selected_record || !selected_record.get('EvnSectionNarrowBed_id')) {
				return false;
			}

			formParams = selected_record.data;
		}

		params.formParams = formParams;
		params.archiveRecord = this.archiveRecord;
		getWnd('swEvnSectionNarrowBedEditWindow').show(params);
	},
	plain: true,
	recountKoikoDniPskov: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var win = this;
		var stat_sutki = false;
		var params = {}
		var Diag_id = base_form.findField('Diag_id').getValue();
		var setDT = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
		var disDT = Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y') || new Date().format('d.m.Y');
		log(setDT, Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'));
		params.Diag_id = Diag_id;
		params.EvnSection_setDT = setDT;

		var age = swGetPersonAge(win.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), setDT);

		if (age && age >= 18) {
			params.AgeGroupType_id = 1
		} else if (age < 18 && age >= 0) {
			params.AgeGroupType_id = 2
		}
		params.MedicalCareKind_id = 117;
		if (params.AgeGroupType_id && Diag_id && setDT && disDT) {
			this.CureStandart.load({
				params: params,
				callback: function (r, c, v) {
					if (r.length > 0) {
						base_form.findField('EvnSection_KoikoDniNorm').setValue(r[0].get('CureStandartTreatment_Duration'));
						base_form.findField('EvnSection_KoikoDniNorm').setRawValue(r[0].get('CureStandartTreatment_Duration'));
						var evn_section_dis_date = getValidDT(disDT, '');
						var evn_section_set_date = getValidDT(setDT, '');
						var index;
						var koiko_dni;
						var koiko_dniNorm;
						var interval = '';
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var record;
						log(2)
						base_form.findField('EvnSection_KoikoDniInterval').setValue("0");
						koiko_dniNorm = r[0].get('CureStandartTreatment_Duration');
						index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
							if (rec.get('LpuSection_id') == lpu_section_id) {
								return true;
							}
							else {
								return false;
							}
						});

						if (index >= 0) {
							record = base_form.findField('LpuSection_id').getStore().getAt(index);
						}
						//log(typeof evn_section_dis_date == 'object' , typeof evn_section_set_date == 'object' ,record)
						if (typeof evn_section_dis_date == 'object' && typeof evn_section_set_date == 'object' && record) {
							var lpu_unit_type_code = 0;
							koiko_dni = 0;

							if (stat_sutki == true) {
								if (evn_section_set_date.getDay() != evn_section_set_date.add(Date.HOUR, -9).getDay()) {
									koiko_dni = koiko_dni + 1;
								}

								evn_section_dis_date = evn_section_dis_date.add(Date.HOUR, -9);
								evn_section_set_date = evn_section_set_date.add(Date.HOUR, -9);
							}

							lpu_unit_type_code = record.get('LpuUnitType_Code');

							koiko_dni = koiko_dni + Math.round((evn_section_dis_date.getTime() - evn_section_set_date.getTime()) / 864e5) + 1;

							if (lpu_unit_type_code && Number(lpu_unit_type_code) == 2 && koiko_dni > 1) {
								koiko_dni = koiko_dni - 1;
							}
						}
						log(4)
						log(koiko_dniNorm, koiko_dni)
						//base_form.findField('EvnSection_KoikoDni').setValue(koiko_dni);
						base_form.findField('EvnSection_KoikoDni').setValue(koiko_dni);
						if (koiko_dniNorm && koiko_dniNorm >= 0 && koiko_dni >= 0) {
							interval = (koiko_dniNorm - koiko_dni)/**-1*/;
							if (interval > 0) interval = /*'+'+*/interval;
							base_form.findField('EvnSection_KoikoDniInterval').setValue(interval);
						} else {
							base_form.findField('EvnSection_KoikoDniInterval').setValue("0");
						}
					} else {
						var evn_section_dis_date = getValidDT(disDT, '');
						var evn_section_set_date = getValidDT(setDT, '');
						var index;
						var koiko_dni;
						var koiko_dniNorm;
						var interval = '';
						var lpu_section_id = base_form.findField('LpuSection_id').getValue();
						var record;
						log(2)
						base_form.findField('EvnSection_KoikoDniInterval').setValue("0");
						koiko_dniNorm = null;
						index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
							if (rec.get('LpuSection_id') == lpu_section_id) {
								return true;
							}
							else {
								return false;
							}
						});

						if (index >= 0) {
							record = base_form.findField('LpuSection_id').getStore().getAt(index);
						}
						//log(typeof evn_section_dis_date == 'object' , typeof evn_section_set_date == 'object' ,record)
						if (typeof evn_section_dis_date == 'object' && typeof evn_section_set_date == 'object' && record) {
							var lpu_unit_type_code = 0;
							koiko_dni = 0;

							if (stat_sutki == true) {
								if (evn_section_set_date.getDay() != evn_section_set_date.add(Date.HOUR, -9).getDay()) {
									koiko_dni = koiko_dni + 1;
								}

								evn_section_dis_date = evn_section_dis_date.add(Date.HOUR, -9);
								evn_section_set_date = evn_section_set_date.add(Date.HOUR, -9);
							}

							lpu_unit_type_code = record.get('LpuUnitType_Code');

							koiko_dni = koiko_dni + Math.round((evn_section_dis_date.getTime() - evn_section_set_date.getTime()) / 864e5) + 1;

							if (lpu_unit_type_code && Number(lpu_unit_type_code) == 2 && koiko_dni > 1) {
								koiko_dni = koiko_dni - 1;
							}
						}
						log(4)
						log(koiko_dniNorm, koiko_dni)
						//base_form.findField('EvnSection_KoikoDni').setValue(koiko_dni);
						base_form.findField('EvnSection_KoikoDni').setValue(koiko_dni);
					}
				}
			})
		}

	},
	recountKoikoDni: function () {
		if (getRegionNick() == 'pskov') {
			this.recountKoikoDniPskov();
			log('3');
			return true;
		}
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var stat_sutki = false;
		var evn_section_dis_date = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'), '');
		var evn_section_set_date = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'), '');
		var index;
		var koiko_dni;
		var koiko_dniNorm;
		var interval = '';
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var record;
		var EvnSection_Absence = base_form.findField('EvnSection_Absence').getValue();
		koiko_dniNorm = base_form.findField('EvnSection_KoikoDniNorm').getRawValue();
		index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
			if (rec.get('LpuSection_id') == lpu_section_id) {
				return true;
			}
			else {
				return false;
			}
		});
		if (index >= 0) {
			record = base_form.findField('LpuSection_id').getStore().getAt(index);
		}
		if (typeof evn_section_dis_date == 'object' && typeof evn_section_set_date == 'object' && record) {
			var lpu_unit_type_code = 0;
			koiko_dni = 0;
			if (stat_sutki == true) {
				if (evn_section_set_date.getDay() != evn_section_set_date.add(Date.HOUR, -9).getDay()) {
					koiko_dni = koiko_dni + 1;
				}
				evn_section_dis_date = evn_section_dis_date.add(Date.HOUR, -9);
				evn_section_set_date = evn_section_set_date.add(Date.HOUR, -9);
			}
			lpu_unit_type_code = record.get('LpuUnitType_Code');
			koiko_dni = koiko_dni + Math.round((evn_section_dis_date.getTime() - evn_section_set_date.getTime()) / 864e5) + 1;
			if (lpu_unit_type_code && Number(lpu_unit_type_code) == 2 && koiko_dni > 1) {
				koiko_dni = koiko_dni - 1;
			}
		}

		if ( !Ext.isEmpty(EvnSection_Absence) ) {
			koiko_dni = koiko_dni - EvnSection_Absence;
		}
		
		//base_form.findField('EvnSection_KoikoDni').setValue(koiko_dni);
		base_form.findField('EvnSection_KoikoDni').setValue(koiko_dni);
		if (koiko_dniNorm && koiko_dniNorm >= 0 && koiko_dni >= 0) {
			interval = (koiko_dniNorm - koiko_dni) * -1;
			if (interval > 0) interval = '+' + interval;
			base_form.findField('EvnSection_KoikoDniInterval').setValue(interval);
		} else {
			base_form.findField('EvnSection_KoikoDniInterval').setValue("0");
		}
	},
	resizable: true,
	getLoadMask: function (txt) {
		if (Ext.isEmpty(txt)) {
			txt = 'Подождите...';
		}

		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: txt});
		}

		return this.loadMask;
	},
	setMKB: function () {
		var parentWin = this
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var sex = parentWin.findById('ESecEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());
		base_form.findField('Diag_id').setMKBFilter(age, sex, true);
	},
	removePersonNewBornFields: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		if (base_form.findField('PersonNewBorn_Weight')) {
			base_form.findField('ChildTermType_id').setValue(null);
			base_form.findField('FeedingType_id').setValue(null);
			base_form.findField('PersonNewBorn_IsBCG').setValue(null);
			base_form.findField('PersonNewBorn_BCGSer').setValue(null);
			base_form.findField('PersonNewBorn_BCGNum').setValue(null);
			base_form.findField('PersonNewBorn_IsAidsMother').setValue(null);
			base_form.findField('ChildPositionType_id').setValue(null);
			base_form.findField('PersonNewBorn_CountChild').setValue(null);
			base_form.findField('PersonNewBorn_IsRejection').setValue(null);
			base_form.findField('PersonNewBorn_id').setValue(null);

			base_form.findField('PersonNewBorn_IsHepatit').setValue(null);
			base_form.findField('PersonNewBorn_BCGDate').setValue(null);
			base_form.findField('PersonNewBorn_Weight').setValue(null);
			base_form.findField('PersonNewBorn_Height').setValue(null);
			base_form.findField('PersonNewBorn_Head').setValue(null);
			base_form.findField('PersonNewBorn_Breast').setValue(null);
			base_form.findField('PersonNewBorn_HepatitNum').setValue(null);
			base_form.findField('PersonNewBorn_HepatitSer').setValue(null);
			base_form.findField('PersonNewBorn_HepatitDate').setValue(null);
			base_form.findField('PersonNewBorn_IsAudio').setValue(null);
			base_form.findField('PersonNewBorn_IsBleeding').setValue(null);
			base_form.findField('PersonNewBorn_IsNeonatal').setValue(null);
			base_form.findField('PersonNewBorn_IsBreath').setValue(null);
			base_form.findField('PersonNewBorn_IsHeart').setValue(null);
			base_form.findField('PersonNewBorn_IsPulsation').setValue(null);
			base_form.findField('PersonNewBorn_IsMuscle').setValue(null);
			base_form.findField('PersonNewborn_BloodBili').setValue(null);
			base_form.findField('PersonNewborn_BloodHemoglo').setValue(null);
			base_form.findField('PersonNewborn_BloodEryth').setValue(null);
			base_form.findField('PersonNewborn_BloodHemato').setValue(null);
			base_form.findField('NewBornWardType_id').setValue(null);
			if (getRegionNick() == 'ufa') {
				base_form.findField('RefuseType_pid').setValue(null);
				base_form.findField('RefuseType_aid').setValue(null);
				base_form.findField('RefuseType_bid').setValue(null);
				base_form.findField('RefuseType_gid').setValue(null);
			}
		}
	},
	setDiagFilterByDate: function () {
		if (!this.formLoaded) {
			return false;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		if (!Ext.isEmpty(base_form.findField('EvnSection_disDate').getValue())) {
			base_form.findField('Diag_id').setFilterByDate(base_form.findField('EvnSection_disDate').getValue());
		} else {
			base_form.findField('Diag_id').setFilterByDate(base_form.findField('EvnSection_setDate').getValue());
		}
	},
	onLpuSectionProfileChange: function (blockLoadKSGKPGKOEF) {
		var that = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		if (!blockLoadKSGKPGKOEF) {
			that.loadKSGKPGKOEF('LpuSectionProfile_id');
		}

		var LpuSectionProfileCombo = that.findById('EvnSectionEditForm').getForm().findField('LpuSectionProfile_id');
		var LpuSectionBedProfileLinkCombo = that.findById('EvnSectionEditForm').getForm().findField('LpuSectionBedProfileLink_fedid');

		if (getRegionNick() == 'krym') {
			var flag = true;
			if (!Ext.isEmpty(LpuSectionProfileCombo) && LpuSectionProfileCombo.lastSelectionText == 'неврологии') {
				LpuSectionBedProfileLinkCombo.getStore().each(function(rec, key){
					if (rec.data.LpuSectionBedProfile_Code == '212')
						flag = key;
				});
			}
			if (flag !== true && LpuSectionBedProfileLinkCombo.getStore().getCount() > 0)
					LpuSectionBedProfileLinkCombo.setValue(LpuSectionBedProfileLinkCombo.getStore().getAt(flag).get(LpuSectionBedProfileLinkCombo.valueField));
		}


		if (getRegionNick().inlist(['kaluga'])) {
			this.filterLpuSectionBedProfilesByLpuSectionProfile(base_form.findField('LpuSectionProfile_id').getValue(), 'LpuSectionBedProfile_id');
		}

		this.refreshFieldsVisibility(['RankinScale_id', 'MedicalCareBudgType_id', getRegionNick().inlist(['penza'])?'RehabScale_id':'']);
	},
	checkAccessAddEvnSectionDrugPSLink: function () {
		if (getRegionNick() == 'krym') {
			var win = this;
			var base_form = win.findById('EvnSectionEditForm').getForm();

			var EvnSection_KSG = base_form.findField('EvnSection_KSG').getValue();
			var enableAdd = false;
			if (EvnSection_KSG && win.action != 'view') {
				if (base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_id') && base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_id').toString().inlist(['6', '7', '9'])) {
					if (EvnSection_KSG.replace(/[\.\s].*/g, '').inlist(['13', '14', '15', '50', '51', '52', '53', '54'])) {
						enableAdd = true;
					}
				} else {
					if (EvnSection_KSG.replace(/[\.\s].*/g, '').inlist(['31', '32', '33', '69', '90', '91', '142', '143', '144', '145', '146'])) {
						enableAdd = true;
					}
				}
			}

			if (enableAdd) {
				win.findById('ESecEF_EvnSectionDrugPSLinkGrid').getTopToolbar().items.items[0].enable();
			} else {
				win.findById('ESecEF_EvnSectionDrugPSLinkGrid').getTopToolbar().items.items[0].disable();
			}
		}
	},
	setPlanDisDT: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var EvnSection_PlanDisDTCombo = base_form.findField('EvnSection_PlanDisDT');
		var person_id = base_form.findField('Person_id').getValue();
		var evnSection_setDT = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'Y-m-d');
		var evnSection_disDT = base_form.findField('EvnSection_disDate').getValue();
		var diag_id = base_form.findField('Diag_id').getValue();
		var lpuSectionBedProfile_id = base_form.findField('LpuSectionBedProfile_id').getValue();

		if (getRegionNick().inlist(['msk','vologda', 'ufa'])
			//&& Ext.isEmpty(EvnSection_PlanDisDTCombo.getValue())
			&& !Ext.isEmpty(person_id)
			&& !Ext.isEmpty(evnSection_setDT)
			&& !Ext.isEmpty(diag_id)
			&& !Ext.isEmpty(lpuSectionBedProfile_id)
		) {
			if (Ext.isEmpty(base_form.findField('LeaveTypeFed_id').getValue())) {
				var loadMaskPlanDisDT = new Ext.LoadMask(this.findById('EvnSectionEditForm').getEl(), {msg: 'Определение плнируемой даты выписки'});
				loadMaskPlanDisDT.show();

				Ext.Ajax.request({
					failure: function (response, options) {
						loadMaskPlanDisDT.hide();
					},
					params: {
						Person_id: person_id,
						Evn_setDT: evnSection_setDT,
						Diag_id: diag_id,
						LpuSectionBedProfile_id: lpuSectionBedProfile_id
					},
					success: function (response, options) {
						loadMaskPlanDisDT.hide();
						responseText = Ext.util.JSON.decode(response.responseText)[0];
						EvnSection_PlanDisDTCombo.setValue(responseText.dateStatement);
					}.createDelegate(this),
					url: '/?c=EvnSection&m=getAverageDateStatement'
				});
			}
			else {
				EvnSection_PlanDisDTCombo.setValue(Ext.isEmpty(evnSection_disDT) ? getGlobalOptions().date : evnSection_disDT);
			}
		}
	},
	getCovidTypeId: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var CovidType_id = this.CovidType_id;
		if (CovidType_id != 3) {
			var Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
			if (!Ext.isEmpty(Diag_Code) && Diag_Code.inlist(['U07.1', 'U07.2'])) {
				CovidType_id = 3;
			} else {
				this.findById('ESecEF_EvnDiagPSGrid').getStore().each(function(rec) {
					if (!Ext.isEmpty(rec.get('Diag_Code')) && rec.get('Diag_Code').inlist(['U07.1', 'U07.2'])) {
						CovidType_id = 3;
					}
				});
			}
		}
		return CovidType_id;
	},
	show: function () {
		var that = this;
		sw.Promed.swEvnSectionEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры');
			return false;
		}
		this.diagPanel.personId = arguments[0].Person_id;
		this.diagPanel.hideHSNField();
		this.editPersonNewBorn = null;
		that.flbr = false;
		that.NewBorn_Weight = -1;
		this.isTraumaTabGridLoaded = false;
		this.isObservTabGridLoaded = false;
		this.isNeonatalTabGridLoaded = false;
		this.needCheckMorfoHistologic = true;
		this.setDisabledMorfoHistologic = true;
		this.isProcessLoadForm = false;
		this.blockLoadKSGKPGKOEF = true;
		this.firstLoadKSGKPGKOEF = true;
		this.firstTimeLoadedKSGKPGKOEF = false;
		this.hasDrugTherapySchemeLinks = false;
		this.hasRehabScaleLinks = false;
		this.hasSofaLinks = false;
		this.CovidType_id = null;
		this.RepositoryObserv_Height = null;
		this.RepositoryObserv_Weight = null;
		this.PersonRegister_id = null;
		this.treeLoaded = false;
		this.formLoaded = false;
		this.createdObjects = {};
		this.OtherEvnSectionList = [];
		this.specificsPanel.collapse();
		this.removePersonNewBornFields();
		this.DrugTherapySchemePanel.resetFieldSets();
		this.DrugTherapySchemePanel.hide();
		this.EvnSectionKSGGrid.removeAll();
		this.EvnSectionKSGGrid.addActions({
			name: 'action_recalcksg',
			text: langs('Рассчитать КСГ'),
			handler: function() {
				// сохраняем движение (при этом определятся и сохранятся КСГ)
				that.doSave({
					openChildWindow: function () {
						// перезагружаем грид с КСГ
						that.loadEvnSectionKSGGrid();
					}
				});
			}
		}, 1);
		this.EvnSectionKSGGrid.addActions({
			name: 'action_selectksg',
			text: langs('Выбрать КСГ для оплаты'),
			handler: function() {
				var base_form = that.findById('EvnSectionEditForm').getForm();
				if (Ext.isEmpty(base_form.findField('EvnSection_id').getValue())) {
					// сохраняем движение (при этом определятся и сохранятся КСГ)
					that.doSave({
						openChildWindow: function() {
							// открываем форму для выбора оплачиваемых КСГ
							getWnd('swEvnSectionKSGWindow').show({
								EvnSection_id: base_form.findField('EvnSection_id').getValue(),
								callback: function() {
									that.loadEvnSectionKSGGrid();
								}
							});
						}
					});
				} else {
					// открываем форму для выбора оплачиваемых КСГ
					getWnd('swEvnSectionKSGWindow').show({
						EvnSection_id: base_form.findField('EvnSection_id').getValue(),
						callback: function() {
							that.loadEvnSectionKSGGrid();
						}
					});
				}
			}
		}, 2);
		if (this.findById('ESecEF_PersonChildForm')) {
			this.findById('ESecEF_PersonChildForm').hide();
			this.findById('ESecEF_PersonChildForm').isLoaded = false;
			var grid1 = this.findById('ESEW_PersonBirthTraumaGrid1').getGrid();
			var grid2 = this.findById('ESEW_PersonBirthTraumaGrid2').getGrid();
			var grid3 = this.findById('ESEW_PersonBirthTraumaGrid3').getGrid();
			var grid4 = this.findById('ESEW_PersonBirthTraumaGrid4').getGrid();
			this.findById('ESEW_NewbornApgarRateGrid').removeAll();
			this.findById('ESEW_PersonBirthTraumaGrid1').removeAll();
			this.findById('ESEW_PersonBirthTraumaGrid2').removeAll();
			this.findById('ESEW_PersonBirthTraumaGrid3').removeAll();
			this.findById('ESEW_PersonBirthTraumaGrid4').removeAll();

			if (this.findById('ESEW_EvnObservNewBornGrid'))
				this.findById('ESEW_EvnObservNewBornGrid').removeAll();

			grid1.getStore().baseParams.BirthTraumaType_id = 1;
			grid2.getStore().baseParams.BirthTraumaType_id = 2;
			grid3.getStore().baseParams.BirthTraumaType_id = 3;
			grid4.getStore().baseParams.BirthTraumaType_id = 4;
			/*this.findById('ESecEF_PersonHeightGrid').removeAll();
			this.findById('ESecEF_PersonWeightGrid').removeAll();*/
		}

		if (this.findById('ESecEF_EvnAbortForm')) {
			this.findById('ESecEF_EvnAbortForm').isLoaded = false;
		}

		this.findById(that.id + 'ESecEF_AnatomDiagPanel').isLoaded = false;

		this.specificsPanel.show();
		this.specificsPanel.isExpanded = false;
		this.specificsTree.getLoader().baseParams = {};

		this.restore();
		this.center();
		this.maximize();

		this.EvnPLDispScreenOnkoGrid.doLayout();
		this.EvnPLDispScreenOnkoGrid.getGrid().getStore().removeAll();

		this.RepositoryObservGrid.hide();
		this.RepositoryObservGrid.getGrid().getStore().removeAll();

		var base_form = this.findById('EvnSectionEditForm').getForm();

		base_form.findField('RankinScale_id').hideContainer();
		base_form.findField('RankinScale_id').clearValue();
		base_form.findField('RankinScale_id').setAllowBlank(true);
		base_form.findField('EvnSection_InsultScale').hideContainer();
		base_form.findField('EvnSection_InsultScale').setValue(null);
		base_form.findField('EvnSection_InsultScale').setAllowBlank(true);
		base_form.findField('EvnSection_NIHSSAfterTLT').hideContainer();
		base_form.findField('EvnSection_NIHSSAfterTLT').setValue(null);
		base_form.findField('EvnSection_NIHSSAfterTLT').setAllowBlank(true);
		base_form.findField('EvnSection_NIHSSLeave').hideContainer();
		base_form.findField('EvnSection_NIHSSLeave').setValue(null);
		base_form.findField('EvnSection_NIHSSLeave').setAllowBlank(true);
		base_form.findField('RankinScale_sid').hideContainer();
		base_form.findField('RankinScale_sid').clearValue();
		base_form.findField('RankinScale_sid').setAllowBlank(true);
		this.findById(this.id + '_CardioFields').hide();
		base_form.findField('PayTypeERSB_id').setContainerVisible(getRegionNick() == 'kz');
		base_form.findField('LpuSectionBedProfileLink_fedid').setContainerVisible(getRegionNick() != 'kz');
		base_form.findField('LpuSectionWard_id').setContainerVisible(getRegionNick() != 'kz');
		base_form.findField('GetRoom_id').setContainerVisible(getRegionNick() == 'kz');
		base_form.findField('GetBed_id').setContainerVisible(getRegionNick() == 'kz');

		this.findById('ESecEF_EvnSectionDrugPSLinkPanel').collapse();

		base_form.reset();
		base_form.findField('Mes_rid').getStore().removeAll();
		base_form.findField('Mes_rid').setAllowBlank(true);

		base_form.findField('LeaveType_fedid').on('change', function (combo, newValue) {
			sw.Promed.EvnSection.filterFedResultDeseaseType({
				LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
				fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
			})
		});
		base_form.findField('ResultDeseaseType_fedid').on('change', function (combo, newValue) {
			sw.Promed.EvnSection.filterFedLeaveType({
				LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
				fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldLeaveType: base_form.findField('LeaveType_id'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
			});
		});
		if (getRegionNick().inlist(['buryatiya', 'perm', 'penza', 'vologda'])) {
			base_form.findField('EvnSection_KSG').hideContainer();
		}
		if (getRegionNick().inlist(['perm', 'kz', 'krym', 'pskov', 'khak', 'krasnoyarsk', 'vologda', 'yaroslavl', 'adygeya', 'msk'])) {
			base_form.findField('EvnSection_KPG').hideContainer();
		}
		if (getRegionNick().inlist(['krasnoyarsk','pskov', 'adygeya'])) {
			base_form.findField('EvnSection_KOEF').hideContainer();
		}

		if (getRegionNick().inlist(['ufa', 'penza', 'adygeya'])) {
			base_form.findField('TariffClass_id').hideContainer();
		}

		base_form.findField('EvnSection_RepFlag').hideContainer();

		if (getRegionNick().inlist(['buryatiya', 'penza', 'pskov', 'vologda'])) {
			// убираем исход гостиптализации и показываем федеральный спрвочник
			base_form.findField('LeaveType_id').hideContainer();
			base_form.findField('LeaveTypeFed_id').showContainer();

			this.leaveTypeFedFilter();
		}
		else {
			base_form.findField('LeaveTypeFed_id').hideContainer();
			base_form.findField('LeaveType_id').showContainer();

			this.leaveTypeFilter();
		}

		base_form.findField('LpuSectionProfile_id').getStore().baseParams = {};
		base_form.findField('LpuUnitType_oid').getStore().clearFilter();
		base_form.findField('LpuSection_aid').getStore().removeAll();
		base_form.findField('MedStaffFact_aid').getStore().removeAll();

		var isBuryatiya = (getRegionNick() == 'buryatiya');
		var isPskov = (getRegionNick() == 'pskov');
		var isUfa = (getRegionNick() == 'ufa');
		var isPerm = (getRegionNick() == 'perm');
		var isKareliya = (getRegionNick() == 'kareliya');

		this.action = null;
		base_form.findField('Diag_id').filterDate = null;
		this.savedData = null;
		base_form.findField('EvnSection_KSG').getEl().dom.style.background = "";
		base_form.findField('EvnSection_KPG').getEl().dom.style.background = "";
		base_form.findField('EvnSection_KOEF').getEl().dom.style.background = "";
		this.savedMesTariff_id = null;
		this.AT = '';
		this.callback = Ext.emptyFn;
		this.evnLeaveSetDT = null;
		this.evnPSSetDT = null;
		this.evnSectionIsFirst = false;
		this.evnSectionIsLast = false;
		this.EvnUslugaGridIsModified = false;
		this.formParams = arguments[0].formParams;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.specBirthData = {};
		this.showHTMedicalCareClass = false;
		this.DiagPriem_id = null;
		if (arguments[0].childPS) {
			this.childPS = true;
			if (arguments[0].childTermType_id) {
				this.childTermType_id = arguments[0].childTermType_id;
			} else {
				this.childTermType_id = null;
			}
			if (arguments[0].PersonNewBorn_CountChild) {
				this.PersonNewBorn_CountChild = arguments[0].PersonNewBorn_CountChild;
			} else {
				this.PersonNewBorn_CountChild = null;
			}
			if (arguments[0].PersonNewBorn_IsAidsMother) {
				this.PersonNewBorn_IsAidsMother = arguments[0].PersonNewBorn_IsAidsMother;
			} else {
				this.PersonNewBorn_IsAidsMother = null;
			}
		}

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].ARMType) {
			this.AT = arguments[0].ARMType;
		}
		if (arguments[0].OtherEvnSectionList) {
			this.OtherEvnSectionList = arguments[0].OtherEvnSectionList;
		}

		this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		if (arguments[0] && arguments[0].ARMType_id) {
			this.ARMType_id = arguments[0].ARMType_id;
		} else if (arguments[0] && arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType_id) {
			this.ARMType_id = arguments[0].userMedStaffFact.ARMType_id;
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			this.ARMType_id = null;
		}

		if (arguments[0] && arguments[0].EvnPS_NumCard) {
			this.EvnPS_NumCard = arguments[0].EvnPS_NumCard;
		}

		if (arguments[0] && arguments[0].EvnPS_setDate) {
			this.EvnPS_setDate = arguments[0].EvnPS_setDate;
		}

		if (arguments[0] && arguments[0].EvnPS_id) {
			this.EvnPS_id = arguments[0].EvnPS_id;
		} else if (arguments[0] && arguments[0].formParams && arguments[0].formParams.EvnSection_pid) {
			this.EvnPS_id = arguments[0].formParams.EvnSection_pid;
		} else {
			this.EvnPS_id = null;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		base_form.findField('HTMedicalCareClass_id').allowLoad = true;

		this.DiagPred_id = arguments[0].DiagPred_id || null;
		this.onChangeLpuSectionWard = arguments[0].onChangeLpuSectionWard || null;
		this.oldLpuSectionWard_id = 0;

		if (arguments[0].EvnLeave_setDT) {
			this.evnLeaveSetDT = arguments[0].EvnLeave_setDT;
		}

		if (arguments[0].EvnPS_setDT) {
			this.evnPSSetDT = arguments[0].EvnPS_setDT;
		}

		if (arguments[0].CovidType_id) {
			this.CovidType_id = arguments[0].CovidType_id;
		}

		if (!Ext.isEmpty(arguments[0].RepositoryObserv_Height)) {
			this.RepositoryObserv_Height = arguments[0].RepositoryObserv_Height;
		}

		if (!Ext.isEmpty(arguments[0].RepositoryObserv_Weight)) {
			this.RepositoryObserv_Weight = arguments[0].RepositoryObserv_Weight;
		}

		if (arguments[0].evnSectionIsFirst) {
			this.evnSectionIsFirst = arguments[0].evnSectionIsFirst;
		}

		if (arguments[0].evnSectionIsLast) {
			this.evnSectionIsLast = arguments[0].evnSectionIsLast;
		}

		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}

		if (arguments[0].DiagPriem_id) {
			this.DiagPriem_id = arguments[0].DiagPriem_id;
		}

		if (arguments[0].EvnUsluga_rid) {
			this.EvnUsluga_rid = arguments[0].EvnUsluga_rid;
		} else {
			this.EvnUsluga_rid = null;
		}

		if (arguments[0].LpuSection_eid) {
			this.LpuSection_eid = arguments[0].LpuSection_eid;
		} else {
			this.LpuSection_eid = null;
		}

		if (arguments[0].LpuSection_pid) {
			this.LpuSection_pid = arguments[0].LpuSection_pid;
		} else {
			this.LpuSection_pid = null;
		}

		if (arguments[0].EvnPS_OutcomeDate) {
			this.EvnPS_OutcomeDate = arguments[0].EvnPS_OutcomeDate;
		} else {
			this.EvnPS_OutcomeDate = null;
		}
		if (arguments[0].PLpuSection_Name) {
			this.PLpuSection_Name = arguments[0].PLpuSection_Name;
		} else {
			this.PLpuSection_Name = null;
		}

		if (arguments[0].EvnPS_OutcomeTime) {
			this.EvnPS_OutcomeTime = arguments[0].EvnPS_OutcomeTime;
		} else {
			this.EvnPS_OutcomeTime = null;
		}

		this.PrehospType_id = arguments[0].PrehospType_id || null;
		this.PrehospType_SysNick = arguments[0].PrehospType_SysNick || null;
		this.EvnPS_IsWithoutDirection = arguments[0].EvnPS_IsWithoutDirection || null;

		var persFrame = this.findById('ESecEF_PersonInformationFrame');
		var parentWin = this;
		var specTreeLoadMask = new Ext.LoadMask(parentWin.specificsTree.getEl(), {msg: 'Ожидание загрузки панели персональной информации...'});
		specTreeLoadMask.show();
		persFrame.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function () {
				var field = base_form.findField('EvnSection_setDate');
				clearDateAfterPersonDeath('personpanelid', 'ESecEF_PersonInformationFrame', field);
				field = base_form.findField('EvnSection_disDate');
				clearDateAfterPersonDeath('personpanelid', 'ESecEF_PersonInformationFrame', field);
				parentWin.checkPersonNewBorn(function () {
					parentWin.onSpecificsExpand(parentWin.specificsPanel);
				});
				parentWin.setMKB();
				parentWin.recountKoikoDni();
				parentWin.checkLpuUnitType();
				var tree = parentWin.specificsTree;
				tree.focus();
				if (tree.getRootNode() == tree.getSelectionModel().getSelectedNode() || tree.getSelectionModel().getSelectedNode() == null) {
					//tree.getRootNode().firstChild.fireEvent('click',tree.getRootNode().firstChild);
				}
				specTreeLoadMask.hide();
				parentWin.refreshPregnancyEvnPSFieldSet();
			}

		});

		if (arguments[0].Person_Birthday){
			this.Person_Birthday_date = arguments[0].Person_Birthday.format('d.m.Y');
		}
		if (arguments[0].EvnPS_setDate ){
			this.EvnPS_setDate_date = arguments[0].EvnPS_setDate.format('d.m.Y');
		}

		this.EvnPS_IsWaif = arguments[0].EvnPS_IsWaif;

		if (this.WizardPanel) {
			this.WizardPanel.resetCurrentCategory(true);
			this.WizardPanel.init();
			this.WizardPanel.PrintResultButton.hide();
			this.WizardPanel.setReadOnly(this.action == 'view');
		}

		base_form.setValues(this.formParams);
		this.findById('ESEW_addPatoMorphHistoDirectionButton').hide();
		if (getRegionNick().inlist(['buryatiya', 'penza', 'pskov', 'vologda']) && !Ext.isEmpty(base_form.findField('LeaveTypeFed_id').getValue())) {
			base_form.findField('LeaveTypeFed_id').fireEvent('change', base_form.findField('LeaveTypeFed_id'), base_form.findField('LeaveTypeFed_id').getValue());
		}

		base_form.findField('HTMedicalCareClass_id').fireEvent('change', base_form.findField('HTMedicalCareClass_id'), base_form.findField('HTMedicalCareClass_id').getValue());

		if (this.action == 'add') {
			this.findById('ESecEF_EvnDiagPSPanel').isLoaded = true;
			this.findById('ESecEF_EvnSectionDrugPSLinkPanel').isLoaded = true;
			this.findById('ESecEF_EvnUslugaPanel').isLoaded = true;
			this.findById('ESecEF_EvnSectionNarrowBedPanel').isLoaded = true;
		} else {
			this.findById('ESecEF_EvnDiagPSPanel').isLoaded = false;
			this.findById('ESecEF_EvnSectionDrugPSLinkPanel').isLoaded = false;
			this.findById('ESecEF_EvnUslugaPanel').isLoaded = false;
			this.findById('ESecEF_EvnSectionNarrowBedPanel').isLoaded = false;
		}

		this.findById('ESecEF_TransfusionPanel').isLoaded = false;
		this.findById('ESecEF_TransfusionGrid').getStore().removeAll();
		if (this.findById('ESecEF_TransfusionGrid').getTopToolbar().items.items.length > 0){
			this.findById('ESecEF_TransfusionGrid').getTopToolbar().items.items[1].disable();
			this.findById('ESecEF_TransfusionGrid').getTopToolbar().items.items[2].disable();
		}

		this.findById('ESecEF_EvnDiagPSGrid').getStore().removeAll();
		this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[0].disable();
		this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[1].disable();
		this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[2].disable();
		this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[3].disable();

		this.findById('ESecEF_EvnSectionDrugPSLinkGrid').getStore().removeAll();
		this.findById('ESecEF_EvnSectionDrugPSLinkGrid').getTopToolbar().items.items[0].disable();
		this.findById('ESecEF_EvnSectionDrugPSLinkGrid').getTopToolbar().items.items[1].disable();
		this.findById('ESecEF_EvnSectionDrugPSLinkGrid').getTopToolbar().items.items[2].disable();
		this.findById('ESecEF_EvnSectionDrugPSLinkGrid').getTopToolbar().items.items[3].disable();

		this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().removeAll();
		this.findById('ESecEF_EvnSectionNarrowBedGrid').getTopToolbar().items.items[0].disable();
		this.findById('ESecEF_EvnSectionNarrowBedGrid').getTopToolbar().items.items[1].disable();
		this.findById('ESecEF_EvnSectionNarrowBedGrid').getTopToolbar().items.items[2].disable();
		this.findById('ESecEF_EvnSectionNarrowBedGrid').getTopToolbar().items.items[3].disable();

		this.findById('ESecEF_EvnUslugaGrid').getStore().removeAll();
		this.findById('ESecEF_EvnUslugaGrid').getTopToolbar().items.items[0].disable();
		this.findById('ESecEF_EvnUslugaGrid').getTopToolbar().items.items[1].disable();
		this.findById('ESecEF_EvnUslugaGrid').getTopToolbar().items.items[2].disable();
		this.findById('ESecEF_EvnUslugaGrid').getTopToolbar().items.items[3].disable();

		this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid().getStore().removeAll();

		base_form.findField('EvnSection_disDate').setMaxValue(undefined);
		base_form.findField('EvnSection_setDate').setMaxValue(undefined);
		base_form.findField('EvnSection_disDate').setMinValue(undefined);
		base_form.findField('EvnSection_setDate').setMinValue(undefined);

		if (this.evnSectionIsFirst) {
			base_form.findField('EvnSection_setDate').enable();
			base_form.findField('EvnSection_setTime').enable();
		}
		else {
			base_form.findField('EvnSection_setDate').disable();
			if (getGlobalOptions().region && getGlobalOptions().region.nick != 'ufa') {
				base_form.findField('EvnSection_setTime').disable();
			}
		}

		// Устанавливаем фильтры для услуг лечения
		if (isBuryatiya == true || isPskov == true) {
			base_form.findField('UslugaComplex_id').clearBaseParams();
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList(['stac_kd']);
		}
		if (isBuryatiya) {
			base_form.findField('UslugaComplex_id').setPersonId(base_form.findField('Person_id').getValue());
			base_form.findField('UslugaComplex_id').setUslugaCategoryList(['tfoms']);
		}

		this.findById('ESecEF_EvnSection_IsZNOCheckbox').setContainerVisible(getRegionNick() != 'kz');

		this.specificsPanel.show();
		/*
		this.MorbusHepatitisSpec.hide();
		*/
		var loadMask = new Ext.LoadMask(this.findById('EvnSectionEditForm').getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		if (this.childPS) {
			this.specificsPanel.toggleCollapse();

		}
		switch (this.action) {
			case 'add':
				this.setTitle(WND_HOSP_ESECADD);
				this.enableEdit(true);
				this.formParams.EvnSection_id = 0;
				this.findById('addDiag').hide();
				this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[0].enable();
				this.findById('ESecEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
				this.findById('ESecEF_EvnSectionNarrowBedGrid').getTopToolbar().items.items[0].enable();
				this.formLoaded = true;

				// Вынес это из persFrame.load({..., т.к. ломалась загрузка списка профилей при открытии формы на редактирование или просмотр
				base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), parentWin.formParams.LpuSection_id || null);

				LoadEmptyRow(this.findById('ESecEF_EvnDiagPSGrid'));
				LoadEmptyRow(this.findById('ESecEF_EvnSectionDrugPSLinkGrid'));
				LoadEmptyRow(this.findById('ESecEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('ESecEF_EvnSectionNarrowBedGrid'));
				LoadEmptyRow(this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid());

				if (base_form.findField('EvnSection_setDate').getValue()) {
					base_form.findField('EvnSection_disDate').setMinValue(base_form.findField('EvnSection_setDate').getValue());
					base_form.findField('EvnSection_setDate').setMinValue(base_form.findField('EvnSection_setDate').getValue());
				}

				var curDate = getValidDT(getGlobalOptions().date, '');

				if (typeof curDate == 'object') {
					base_form.findField('EvnSection_disDate').setMaxValue(getValidDT(getGlobalOptions().date, '').add(Date.DAY, this.addMaxDateDays).format('d.m.Y'));
				}
				else {
					setCurrentDateTime({
						callback: Ext.emptyFn,
						dateField: base_form.findField('EvnSection_disDate'),
						setDateMaxValue: true,
						addMaxDateDays: this.addMaxDateDays,
						setDateMinValue: false,
						setTime: false,
						windowId: this.id
					});
				}

				base_form.findField('EvnSection_setDate').setMaxValue(getGlobalOptions().date);

				base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());

				var diag_id = this.formParams.Diag_id;
				var diag_set_phase_id = this.formParams.DiagSetPhase_id;
				var index;
				var lpu_section_id = this.formParams.LpuSection_id;
				var lpu_section_pid;
				var med_personal_id = this.formParams.MedPersonal_id;
				var record;

				record = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id);

				if (record) {
					lpu_section_pid = record.get('LpuSection_pid');

					if (record.get('LpuSection_IsHTMedicalCare') == 2) {
						this.showHTMedicalCareClass = true;
						this.findById('HTMedicalCareClass').show();
					} else {
						this.showHTMedicalCareClass = false;
						this.findById('HTMedicalCareClass').hide();
					}
				}

				index = base_form.findField('MedStaffFact_id').getStore().findBy(function (record, id) {
					if ((record.get('LpuSection_id') == lpu_section_id || record.get('LpuSection_id') == lpu_section_pid) && record.get('MedPersonal_id') == med_personal_id) {
						return true;
					}
					else {
						return false;
					}
				})

				if (index >= 0) {
					base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
				}

				if (diag_id) {
					base_form.findField('Diag_id').getStore().load({
						callback: function () {
							base_form.findField('Diag_id').getStore().each(function (rec) {
								if (rec.get('Diag_id') == diag_id) {
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
								}
							});
							if (that.showHTMedicalCareClass) {
								that.loadHTMedicalCareClassCombo();
							}
							base_form.findField('EvnSection_setDate').setMaxValue(getGlobalOptions().date);

							base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
							that.refreshFieldsVisibility(['DeseaseBegTimeType_id', 'DeseaseType_id', 'TumorStage_id', 'PainIntensity_id', 'EvnSection_BarthelIdx', 'MedicalCareBudgType_id', 'MesDop_ids']);
							that.formLoaded = true;
						},
						params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
					});
				}
				else {
					if (
						getRegionNick() == 'msk'
						&& (this.CovidType_id == 2 || this.CovidType_id == 3)
					) {
						this.RepositoryObservGrid.show();
						this.RepositoryObservGrid.doLayout();
					}
				}

				if (diag_set_phase_id) {
					base_form.findField('DiagSetPhase_id').setValue(diag_set_phase_id);
				}

				base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'), base_form.findField('LeaveType_id').getValue());
				base_form.findField('PayType_id').fireEvent('change', base_form.findField('PayType_id'), base_form.findField('PayType_id').getValue());

				this.checkLpuUnitType();
				this.setIsMedReason();
				this.createEvnDiagCopyMenu();

				loadMask.hide();

				//base_form.clearInvalid();
				this.Morbus_id = null;

				base_form.items.each(function (f) {
					f.validate();
				});

				if (!base_form.findField('EvnSection_setDate').disabled) {
					base_form.findField('EvnSection_setDate').focus(true, 200);
				}
				else if (!base_form.findField('EvnSection_disDate').disabled) {
					base_form.findField('EvnSection_disDate').focus(true, 200);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
				sw.Promed.EvnSection.filterFedResultDeseaseType({
					LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				})
				sw.Promed.EvnSection.filterFedLeaveType({
					LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
					fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
					fieldLeaveType: base_form.findField('LeaveType_id'),
					fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
				});
				if (!getRegionNick().inlist(['kz', 'ufa'])) {
					Ext.Ajax.request({
						url: '/?c=EvnSection&m=getPriemDiag',
						params: {
							EvnPS_id: base_form.findField('EvnSection_pid').getValue()
						},
						callback: function (options, success, response) {
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (!Ext.isEmpty(response_obj.Diag_id)) {
									base_form.findField('Diag_eid').getStore().load({
										params: {where: "where Diag_Code like 'X%' or Diag_Code like 'V%' or Diag_Code like 'W%' or Diag_Code like 'Y%'"},
										callback: function () {
											base_form.findField('Diag_eid').setValue(response_obj.Diag_id);
										}
									});
								}
							}
						}
					});
				}
				base_form.findField('Diag_spid').hideContainer();
				this.blockLoadKSGKPGKOEF = false;
				this.setDiagEidAllowBlank();
				this.refreshFieldsVisibility();
				this.loadRoomList();
				this.setBedListAllowBlank();
				if (getRegionNick() == 'kz' && this.EvnPS_IsWithoutDirection == 2) {
					base_form.findField('PayType_id').disable();
				}

				this.setPlanDisDT();
				break;
			case 'edit':
			case 'view':
				this.isProcessLoadForm = true;
				base_form.load({
					failure: function () {
						loadMask.hide();

						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function () {
							//this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					params: {
						EvnSection_id: this.formParams.EvnSection_id,
						archiveRecord: that.archiveRecord
					},
					success: function (form, act) {
						this.savedMesTariff_id = base_form.findField('MesTariff_id').getValue();
						if (!act || !act.response || !act.response.responseText) {
							loadMask.hide();
							sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы' + act + "d", function () {
								//this.hide();
							}.createDelegate(this));
						}
						var form_action = this.action,
						    v;
						var response_obj = Ext.util.JSON.decode(act.response.responseText);
						if (response_obj[0].accessType == 'view') {
							this.action = 'view';
						}

						this.savedData = response_obj[0];

						base_form.findField('EvnSection_KSG').fireEvent('change', base_form.findField('EvnSection_KSG'), base_form.findField('EvnSection_KSG').getValue());

						if (getRegionNick() == 'perm' && !Ext.isEmpty(response_obj[0].Mes_ksgid)) {
							// прогрузить комбо Mes_rid сохранённым в БД значением
							base_form.findField('Mes_rid').getStore().loadData([{
								Mes_id: response_obj[0].Mes_ksgid,
								Mes_sid: response_obj[0].Mes_sid,
								Mes_tid: response_obj[0].Mes_tid,
								Mes_Code: response_obj[0].Mes_Code,
								Mes_Name: response_obj[0].Mes_Name,
								MesOld_Num: response_obj[0].MesOld_Num,
								MesTariff_id: response_obj[0].MesTariff_id,
								MesType_id: response_obj[0].MesType_id,
								Mes_IsRehab: 1,
								Mes_IsDefault: 2,
								MesTariff_Value: response_obj[0].MesTariff_Value,
								EvnSection_CoeffCTP: response_obj[0].EvnSection_CoeffCTP
							}]);
							base_form.findField('Mes_rid').setValue(response_obj[0].Mes_ksgid);
							base_form.findField('Mes_rid').fireEvent('change', base_form.findField('Mes_rid'), base_form.findField('Mes_rid').getValue());
							base_form.findField('Mes_rid').setAllowBlank(false);
						}

						switch (this.action) {
							case 'edit':
								this.setTitle(WND_HOSP_ESECEDIT);
								this.enableEdit(true);
								this.findById('addDiag').show();
								this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[0].enable();
								this.findById('ESecEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
								this.findById('ESecEF_EvnSectionNarrowBedGrid').getTopToolbar().items.items[0].enable();

								this.setPlanDisDT();
								break;

							case 'view':
								this.findById('addDiag').hide();
								this.setTitle(WND_HOSP_ESECVIEW);
								this.enableEdit(false);
								break;
						}

						if (getRegionNick() == 'buryatiya') {
							base_form.findField('UslugaComplex_id').setAllowBlank(!(base_form.findField('PayType_id').getFieldValue('PayType_SysNick') == 'oms'));
						}

						if (getRegionNick() == 'perm') {
							var PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
							base_form.findField('LpuSectionBedProfileLink_fedid').setAllowBlank(PayType_SysNick != 'oms' && PayType_SysNick != 'ovd');
						}
						if (getRegionNick().inlist(['astra', 'pskov', 'buryatiya', 'krym', 'ufa', 'adygeya'])) {
							var PayType_SysNick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
							base_form.findField('LpuSectionBedProfileLink_fedid').setAllowBlank(PayType_SysNick != 'oms');
						}
						if (getRegionNick() == 'penza') {
							base_form.findField('LpuSectionBedProfileLink_fedid').setAllowBlank(false);
						}

						var anatom_where_code;
						var anatom_where_id = response_obj[0].AnatomWhere_id;
						var diag_aid = response_obj[0].Diag_aid;
						var diag_id = response_obj[0].Diag_id;
						var diag_cid = response_obj[0].Diag_cid;
						var diag_eid = response_obj[0].Diag_eid;
						var evn_die_exp_date = response_obj[0].EvnDie_expDate;
						var evn_die_exp_time = response_obj[0].EvnDie_expTime;
						var evn_section_dis_date = base_form.findField('EvnSection_disDate').getValue();
						var evn_section_set_date = base_form.findField('EvnSection_setDate').getValue();
						var evn_section_is_paid = response_obj[0].EvnSection_IsPaid;
						var HTMedicalCareClass_id = response_obj[0].HTMedicalCareClass_id;
						var index;
						var evn_diag_ps = response_obj[0].EvnDiagPS_id;
						var lpu_section_aid = response_obj[0].LpuSection_aid;
						var lpu_section_did = null;
						var lpu_section_id = response_obj[0].LpuSection_id;
						var lpu_section_pid;
						var lpu_section_ward_id = response_obj[0].LpuSectionWard_id;
						var lpu_unit_type_id;
						var lpu_unit_type_oid = response_obj[0].LpuUnitType_oid;
						var med_personal_aid = response_obj[0].MedPersonal_aid;
						var med_personal_did = response_obj[0].MedPersonal_did;
						var med_staff_fact_did = response_obj[0].MedStaffFact_did;
						var med_personal_id = response_obj[0].MedPersonal_id;
						var org_aid = response_obj[0].Org_aid;
						var record;
						var tariff_class_id = response_obj[0].TariffClass_id;
						var Org_oidCombo = base_form.findField('Org_oid');
						var Org_oid = response_obj[0].Org_oid;

						var
							CureResult_id = response_obj[0].CureResult_id,
							EvnSection_IsTerm = response_obj[0].EvnSection_IsTerm,
							LeaveType_fedid = response_obj[0].LeaveType_fedid,
							MedStaffFact_id = response_obj[0].MedStaffFact_id,
							ResultDeseaseType_fedid = response_obj[0].ResultDeseaseType_fedid;

						if (isBuryatiya == true || isPskov == true) {
							var usluga_complex_id = base_form.findField('UslugaComplex_id').getValue();
						}

						if (!this.findById('ESecEF_TransfusionPanel').collapsed) {
							this.findById('ESecEF_TransfusionPanel').fireEvent('expand', this.findById('ESecEF_TransfusionPanel'));
						}
						if (!this.findById('ESecEF_EvnDiagPSPanel').collapsed) {
							this.findById('ESecEF_EvnDiagPSPanel').fireEvent('expand', this.findById('ESecEF_EvnDiagPSPanel'));
						}
						if (!this.findById('ESecEF_EvnSectionDrugPSLinkPanel').collapsed) {
							this.findById('ESecEF_EvnSectionDrugPSLinkPanel').fireEvent('expand', this.findById('ESecEF_EvnSectionDrugPSLinkPanel'));
						}
						if (!this.findById('ESecEF_EvnUslugaPanel').collapsed) {
							this.findById('ESecEF_EvnUslugaPanel').fireEvent('expand', this.findById('ESecEF_EvnUslugaPanel'));
						}
						if (/*getRegionNick().inlist([ 'ufa' ]) &&*/ !this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed) {
							this.findById('ESecEF_EvnSectionNarrowBedPanel').fireEvent('expand', this.findById('ESecEF_EvnSectionNarrowBedPanel'));
						}
						if (!this.specificsPanel.collapsed) {
							this.specificsPanel.fireEvent('expand', this.specificsPanel);
						}

						if (!Ext.isEmpty(response_obj[0].DrugTherapyScheme_ids)) {
							this.DrugTherapySchemePanel.show();
							this.DrugTherapySchemePanel.setIds(response_obj[0].DrugTherapyScheme_ids);
						}

						this.loadEvnSectionKSGGrid();

						this.findById('ESecEF_EvnSection_IsZNOCheckbox').setValue(base_form.findField('EvnSection_IsZNO').getValue() == 2);
						base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnSection_IsZNO').getValue() == 2);
						base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm', 'msk' ]) || base_form.findField('EvnSection_IsZNO').getValue() != 2);
						var diag_spid = base_form.findField('Diag_spid').getValue();
						if (diag_spid) {
							base_form.findField('Diag_spid').getStore().load({
								callback: function () {
									base_form.findField('Diag_spid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_spid) {
											base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
											that.setDiagSpidComboDisabled();
										}
									});
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_spid}
							});
						}

						base_form.findField('EvnSection_IsPaid').setValue(evn_section_is_paid);
						base_form.findField('HTMedicalCareClass_id').clearValue();

						if (getRegionNick() == 'perm' && evn_section_is_paid == 2 && parseInt(base_form.findField('EvnSection_IndexRepInReg').getValue()) > 0) {
							base_form.findField('EvnSection_RepFlag').showContainer();

							if (parseInt(base_form.findField('EvnSection_IndexRep').getValue()) >= parseInt(base_form.findField('EvnSection_IndexRepInReg').getValue())) {
								base_form.findField('EvnSection_RepFlag').setValue(true);
							}
							else {
								base_form.findField('EvnSection_RepFlag').setValue(false);
							}
						}

						// Выполняются действия, которые должны выполняться после смены даты госпитализации
						base_form.findField('LpuSection_id').clearValue();
						base_form.findField('MedStaffFact_id').clearValue();
						base_form.findField('EvnSection_disDate').setMinValue(evn_section_set_date);

						var WithoutChildLpuSectionAge = false;
						var Person_Birthday = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday');

						var age = swGetPersonAge(Person_Birthday, evn_section_set_date);
						if (age >= 18 && !isUfa && !isPerm) {
							WithoutChildLpuSectionAge = true;
						}

						setLpuSectionGlobalStoreFilter({
							isStac: true,
							dateFrom: Ext.util.Format.date(evn_section_set_date, 'd.m.Y'),
							dateTo: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
							WithoutChildLpuSectionAge: WithoutChildLpuSectionAge
						});
						base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						setMedStaffFactGlobalStoreFilter({
							allowDuplacateMSF: true,
							dateFrom: Ext.util.Format.date(evn_section_set_date, 'd.m.Y'),
							dateTo: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
							EvnClass_SysNick: 'EvnSection',
							isStac: true,
							WithoutChildLpuSectionAge: WithoutChildLpuSectionAge/*,
							isDoctor:true*/
						});
						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						// Выполняются действия, которые должны выполняться после смены даты выписки
						base_form.findField('MedStaffFact_did').clearValue();
						if (!evn_section_dis_date) {
							setMedStaffFactGlobalStoreFilter({
								allowDuplacateMSF: true,
								isStac: true
							});
							base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
						else {
							setMedStaffFactGlobalStoreFilter({
								allowDuplacateMSF: true,
								isStac: true,
								onDate: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y')
							});
							base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
						// Выполняются действия, которые должны выполняться после изменения даты проведения экспертизы
						base_form.findField('LpuSection_aid').clearValue();
						base_form.findField('MedStaffFact_aid').clearValue();
						if (!evn_die_exp_date) {
							setLpuSectionGlobalStoreFilter();
							base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							setMedStaffFactGlobalStoreFilter({
								allowDuplacateMSF: true
							});
							base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
						else {
							setLpuSectionGlobalStoreFilter({
								onDate: evn_die_exp_date
							});
							base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							setMedStaffFactGlobalStoreFilter({
								allowDuplacateMSF: true,
								onDate: evn_die_exp_date
							});
							base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}

						if (this.action == 'edit' && this.evnSectionIsFirst == true) {
							base_form.findField('EvnSection_setDate').setMinValue(getValidDT(Ext.util.Format.date(this.evnPSSetDT, 'd.m.Y'), ''));
						}
						index = base_form.findField('LpuSection_id').getStore().findBy(function (record, id) {
							return (record.get('LpuSection_id') == lpu_section_id);
						});
						if (index >= 0) {
							record = base_form.findField('LpuSection_id').getStore().getAt(index);

							if (!Ext.isEmpty(lpu_section_ward_id)) {
								base_form.findField('LpuSectionWard_id').setValue(lpu_section_ward_id);
							}

							lpu_section_pid = record.get('LpuSection_pid');
							lpu_unit_type_id = record.get('LpuUnitType_id');
							base_form.findField('LpuSection_id').setValue(lpu_section_id);
							base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);

							if (!getRegionNick().inlist(['ufa', 'penza', 'adygeya'])) {
								if (Number(record.get('LpuUnitType_Code')) == 4) {
									base_form.findField('TariffClass_id').setAllowBlank(getRegionNick().inlist(['astra', 'kareliya', 'krym']));

									if (this.action != 'view') {
										base_form.findField('TariffClass_id').enable();
									}
								}
								else {
									base_form.findField('TariffClass_id').setAllowBlank(true);
									base_form.findField('TariffClass_id').clearValue();
									base_form.findField('TariffClass_id').disable();
								}
							}

							if (record.get('LpuSection_IsHTMedicalCare') == 2) {
								this.showHTMedicalCareClass = true;
								this.findById('HTMedicalCareClass').show();
							} else {
								this.showHTMedicalCareClass = false;
								this.findById('HTMedicalCareClass').hide();
							}
						}

						index = base_form.findField('MedStaffFact_id').getStore().findBy(function (record, id) {
							return (record.get('MedStaffFact_id') == MedStaffFact_id);
						});

						if (index == -1) {
							index = base_form.findField('MedStaffFact_id').getStore().findBy(function (record, id) {
								if ((record.get('LpuSection_id') == lpu_section_id || record.get('LpuSection_id') == lpu_section_pid) && record.get('MedPersonal_id') == med_personal_id) {
									return true;
								}
								else {
									return false;
								}
							});
						}

						if (index >= 0) {
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
						}
						else {
							Ext.Ajax.request({
								failure: function (response, options) {
									loadMask.hide();
								},
								params: {
									LpuSection_id: lpu_section_id,
									MedPersonal_id: med_personal_id,
									ignoreDisableInDocParam: 1
								},
								success: function (response, options) {
									loadMask.hide();

									base_form.findField('MedStaffFact_id').ignoreDisableInDoc = true;
									base_form.findField('MedStaffFact_id').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);
									base_form.findField('MedStaffFact_id').ignoreDisableInDoc = false;

									index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
										return (rec.get('MedStaffFact_id') == MedStaffFact_id);
									});

									if (index == -1) {
										index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
											return (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id);
										});
									}

									if (index >= 0) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
										base_form.findField('MedStaffFact_id').validate();
									}
								}.createDelegate(this),
								url: C_MEDPERSONAL_LIST
							});
						}

						if (!Ext.isEmpty(tariff_class_id)) {
							index = base_form.findField('TariffClass_id').getStore().findBy(function (rec, id) {
								return (rec.get('TariffClass_id') == tariff_class_id);
							});
							if (index >= 0) {
								base_form.findField('TariffClass_id').setValue(tariff_class_id);
							}
						}

						base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'), base_form.findField('LeaveType_id').getValue());

						index = base_form.findField('AnatomWhere_id').getStore().findBy(function (record, id) {
							return (parseInt(record.get('AnatomWhere_id')) == parseInt(anatom_where_id));
						});
						if (index >= 0) {
							anatom_where_code = parseInt(base_form.findField('AnatomWhere_id').getStore().getAt(index).get('AnatomWhere_Code'));

							base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), anatom_where_id);
						}
						//новая реализация
						if ( med_staff_fact_did ){
							base_form.findField('MedStaffFact_did').setValue( med_staff_fact_did );
						} else {
							index = base_form.findField('MedStaffFact_did').getStore().findBy(function (record, id) {
								return (record.get('MedPersonal_id') == med_personal_did);
							});
							if (index >= 0) {
								base_form.findField('MedStaffFact_did').setValue(base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id'));
							}
							else {
								Ext.Ajax.request({
									failure: function (response, options) {
										loadMask.hide();
									},
									params: {
										LpuSection_id: lpu_section_did,
										MedPersonal_id: med_personal_did
									},
									success: function (response, options) {
										loadMask.hide();
										base_form.findField('MedStaffFact_did').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);
										index = base_form.findField('MedStaffFact_did').getStore().findBy(function (rec) {
											return (rec.get('MedPersonal_id') == med_personal_did && rec.get('LpuSection_id') == lpu_section_did);
										});
										if (index >= 0) {
											base_form.findField('MedStaffFact_did').setValue(base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id'));
											base_form.findField('MedStaffFact_did').validate();
										}
									}.createDelegate(this),
									url: C_MEDPERSONAL_LIST
								});
							}
						}
						//старая реализация
						/*index = base_form.findField('MedStaffFact_did').getStore().findBy(function (record, id) {
							return (record.get('MedPersonal_id') == med_personal_did);
						});

						if (index >= 0) {
							base_form.findField('MedStaffFact_did').setValue(base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id'));
						}
						else {
							Ext.Ajax.request({
								failure: function (response, options) {
									loadMask.hide();
								},
								params: {
									LpuSection_id: lpu_section_did,
									MedPersonal_id: med_personal_did
								},
								success: function (response, options) {
									loadMask.hide();

									base_form.findField('MedStaffFact_did').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

									index = base_form.findField('MedStaffFact_did').getStore().findBy(function (rec) {
										return (rec.get('MedPersonal_id') == med_personal_did && rec.get('LpuSection_id') == lpu_section_did);
									});

									if (index >= 0) {
										base_form.findField('MedStaffFact_did').setValue(base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id'));
										base_form.findField('MedStaffFact_did').validate();
									}
								}.createDelegate(this),
								url: C_MEDPERSONAL_LIST
							});
						}*/

						index = base_form.findField('LpuSection_aid').getStore().findBy(function (record, id) {
							return (parseInt(record.get('LpuSection_id')) == parseInt(lpu_section_aid));
						});
						if (index >= 0) {
							base_form.findField('LpuSection_aid').setValue(lpu_section_aid);
						}

						index = base_form.findField('MedStaffFact_aid').getStore().findBy(function (record, id) {
							return (parseInt(record.get('LpuSection_id')) == parseInt(lpu_section_aid) && parseInt(record.get('MedPersonal_id')) == parseInt(med_personal_aid));
						});

						if (index >= 0) {
							base_form.findField('MedStaffFact_aid').setValue(base_form.findField('MedStaffFact_aid').getStore().getAt(index).get('MedStaffFact_id'));
						}
						else {
							Ext.Ajax.request({
								failure: function (response, options) {
									loadMask.hide();
								},
								params: {
									LpuSection_id: lpu_section_aid,
									MedPersonal_id: med_personal_aid
								},
								success: function (response, options) {
									loadMask.hide();

									base_form.findField('MedStaffFact_aid').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

									index = base_form.findField('MedStaffFact_aid').getStore().findBy(function (rec) {
										return (rec.get('MedPersonal_id') == med_personal_aid && rec.get('LpuSection_id') == lpu_section_aid);
									});

									if (index >= 0) {
										base_form.findField('MedStaffFact_aid').setValue(base_form.findField('MedStaffFact_aid').getStore().getAt(index).get('MedStaffFact_id'));
										//base_form.findField('MedStaffFact_aid').validate();
									}
								}.createDelegate(this),
								url: C_MEDPERSONAL_LIST
							});
						}

						if (isBuryatiya == true || isPskov == true) {
							base_form.findField('UslugaComplex_id').setUslugaComplexDate(Ext.util.Format.date(evn_section_set_date, 'd.m.Y'));
							if (!Ext.isEmpty(usluga_complex_id)) {
								base_form.findField('UslugaComplex_id').getStore().load({
									callback: function () {
										index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
											return (rec.get('UslugaComplex_id') == usluga_complex_id);
										});

										if (index >= 0) {
											base_form.findField('UslugaComplex_id').setValue(usluga_complex_id);
										}
										else {
											base_form.findField('UslugaComplex_id').clearValue();
										}
									}.createDelegate(this),
									params: {
										UslugaComplex_id: usluga_complex_id
									}
								});
							}
						}

						setCurrentDateTime({
							callback: Ext.emptyFn,
							dateField: base_form.findField('EvnSection_disDate'),
							loadMask: false,
							setDate: false,
							setDateMaxValue: true,
							addMaxDateDays: this.addMaxDateDays,
							windowId: this.id
						});
						if (diag_aid) {
							base_form.findField('Diag_aid').getStore().load({
								callback: function () {
									base_form.findField('Diag_aid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_aid) {
											base_form.findField('Diag_aid').fireEvent('select', base_form.findField('Diag_aid'), rec, 0);
										}
									});
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_aid}
							});
						}
						if (diag_id) {
							base_form.findField('Diag_id').getStore().load({
								callback: function () {
									base_form.findField('Diag_id').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_id) {
											/*
											var diag_code = rec.get('Diag_Code').substr(0, 3);
											if ( diag_code.inlist(['B15', 'B16', 'B17', 'B18', 'B19']) ) {
												parentWin.MorbusHepatitisSpec.show();
											}
											*/
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
											if (this.showHTMedicalCareClass) {
												base_form.findField('HTMedicalCareClass_id').allowLoad = false;
												this.loadHTMedicalCareClassCombo({
													callback: function () {
														base_form.findField('HTMedicalCareClass_id').allowLoad = true;
														if (!Ext.isEmpty(HTMedicalCareClass_id) && base_form.findField('HTMedicalCareClass_id').getStore().getCount() > 0) {
															var idx = base_form.findField('HTMedicalCareClass_id').getStore().findBy(function (rec) {
																return (rec.get('HTMedicalCareClass_id') == HTMedicalCareClass_id);
															});

															if (idx >= 0) {
																base_form.findField('HTMedicalCareClass_id').setValue(HTMedicalCareClass_id);
															}
														}

														base_form.findField('HTMedicalCareClass_id').fireEvent('change', base_form.findField('HTMedicalCareClass_id'), base_form.findField('HTMedicalCareClass_id').getValue());
													}
												}, true);
											}
											this.setDiagEidAllowBlank();
										}
									}.createDelegate(this));
									parentWin.onSpecificsExpand(parentWin.specificsPanel);
									if (base_form.findField('Diag_id').isPregnancyDiag) {
										parentWin.specificsPanel.expand();
									}
									that.setDiagFilterByDate();
									that.checkMesOldUslugaComplexFields();
									that.setDiagEidAllowBlank();
									that.refreshFieldsVisibility(['DeseaseBegTimeType_id', 'DeseaseType_id', 'TumorStage_id', 'PainIntensity_id', 'EvnSection_BarthelIdx', 'MedicalCareBudgType_id', 'MesDop_ids']);
									that.formLoaded = true;
								}.createDelegate(this),
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_id}
							});
						} else {
							parentWin.onSpecificsExpand(parentWin.specificsPanel);
							this.checkMesOldUslugaComplexFields();
							that.formLoaded = true;

							if (
								getRegionNick() == 'msk'
								&& (this.CovidType_id == 2 || this.CovidType_id == 3)
							) {
								this.RepositoryObservGrid.show();
								this.RepositoryObservGrid.doLayout();
							}
						}
						if (diag_eid) {
							base_form.findField('Diag_eid').getStore().load({
								callback: function () {
									base_form.findField('Diag_eid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_eid) {
											base_form.findField('Diag_eid').fireEvent('select', base_form.findField('Diag_eid'), rec, 0);
										}
									});
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_eid}
							});
						}
						if (diag_cid) {
							base_form.findField('Diag_cid').getStore().load({
								callback: function () {
									base_form.findField('Diag_cid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_cid) {
											base_form.findField('Diag_cid').setValue(diag_cid);
										}
									});
								},
								params: {where: "where DiagLevel_id = 4 and Diag_id = " + diag_cid}
							});
						}
						if (org_aid) {
							var org_type;

							switch (anatom_where_code) {
								case 2:
									org_type = 'lpu';
									break;
								case 3:
									org_type = 'anatom_old';
									break;
							}

							if (org_type) {
								base_form.findField('Org_aid').getStore().load({
									callback: function (records, options, success) {
										if (success) {
											base_form.findField('Org_aid').setValue(org_aid);
										}
									},
									params: {
										Org_id: org_aid,
										OrgType: org_type,
										onlyFromDictionary: true
									}
								});
							}
						}
						if (Org_oid) {
							Org_oidCombo.getStore().load({
								callback: function (records, options, success) {
									Org_oidCombo.clearValue();
									if (success) {
										Org_oidCombo.setValue(Org_oid);
									}
								},
								params: {
									Org_id: Org_oid
								}
							});
						}
						base_form.findField('LpuUnitType_oid').getStore().filterBy(function (rec) {
							return (rec.get('LpuUnitType_id') != lpu_unit_type_id);
						});
						if (lpu_unit_type_oid && lpu_unit_type_oid != lpu_unit_type_id) {
							base_form.findField('LpuUnitType_oid').setValue(lpu_unit_type_oid);
						}
						var mes2_id = base_form.findField('Mes2_id').getValue();
						this.loadMesCombo();
						this.loadMes2Combo(mes2_id, false);
						if (response_obj[0].Morbus_id) {
							this.Morbus_id = response_obj[0].Morbus_id;
						} else {
							this.Morbus_id = null;
						}

						this.checkLpuUnitType();

						//Если случай оплачен, разрешить редактирование экспертизы
						var leave_type_sysnick = base_form.findField('LeaveType_id').getFieldValue('LeaveType_SysNick');
						if (form_action == 'edit' && this.action == 'view' && evn_section_is_paid == 2 && leave_type_sysnick == 'die') {
							this.editAnatom = true;
							this.enableAnatomFormEdit(true);
						}

						this.createEvnDiagCopyMenu();

						/*
						if (isDebug()) {
							this.DiagPanel.collapse();
							this.SpecThreatPanel.collapse();
							this.MorbusHepatitisSpec.collapse();
							if (response_obj[0].onkoData) {
								//this.fillOnko(response_obj[0].onkoData);
							}
						}
						*/
						/*
						if (getRegionNick().inlist(['kareliya', 'kaluga', 'krym', 'pskov'])) {
							base_form.findField('LpuSectionBedProfile_id').setValue(response_obj[0].LpuSectionBedProfile_id);
						} else {
							base_form.findField('LpuSectionBedProfile_id').clearValue();
						}
						*/
						if (response_obj[0].LpuSectionBedProfile_id) {
							base_form.findField('LpuSectionBedProfile_id').setValue(response_obj[0].LpuSectionBedProfile_id);
						} else {
							base_form.findField('LpuSectionBedProfile_id').setValue(null);
						}

						sw.Promed.EvnSection.filterFedLeaveType({
							LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
							fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
							fieldLeaveType: base_form.findField('LeaveType_id'),
							fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
						});

						if (!Ext.isEmpty(LeaveType_fedid)) {
							base_form.findField('LeaveType_fedid').setValue(LeaveType_fedid);
						}

						sw.Promed.EvnSection.filterFedResultDeseaseType({
							LpuUnitType_SysNick: base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
							fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
							fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
						})

						if (!Ext.isEmpty(ResultDeseaseType_fedid)) {
							base_form.findField('ResultDeseaseType_fedid').setValue(ResultDeseaseType_fedid);
						}


						if (!Ext.isEmpty(EvnSection_IsTerm)) {
							base_form.findField('EvnSection_IsTerm').setValue(EvnSection_IsTerm);
						}

						if (!Ext.isEmpty(lpu_section_ward_id)) {
							base_form.findField('LpuSectionWard_id').setValue(lpu_section_ward_id);
						}

						loadMask.hide();


						that.filterDS();

						base_form.items.each(function (f) {
							f.validate();
						});

						this.blockLoadKSGKPGKOEF = false;
						this.loadKSGKPGKOEF();
						this.setIsMedReason();
						this.refreshPregnancyEvnPSFieldSet();

						if (this.action == 'edit') {
							if (!base_form.findField('EvnSection_setDate').disabled) {
								base_form.findField('EvnSection_setDate').focus(true, 200);
							}
							else if (!base_form.findField('EvnSection_disDate').disabled) {
								base_form.findField('EvnSection_disDate').focus(true, 200);
							}
							else {
								this.buttons[this.buttons.length - 1].focus();
							}
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}

						this.setDiagEidAllowBlank();

						if (isKareliya) {
							this.processFieldCurResult();
						}


						if (!Ext.isEmpty(CureResult_id)) {
							base_form.findField('CureResult_id').setValue(CureResult_id);
							base_form.findField('CureResult_id').fireEvent('change', base_form.findField('CureResult_id'), base_form.findField('CureResult_id').getValue());
						}


						this.refreshFieldsVisibility(null, true);
						
						this.findById('ESecEF_EvnDiagPSGrid').getStore().load({
							params: {
								'class': 'EvnDiagPSSect',
								EvnDiagPS_pid: this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
							},
							callback: function() {
								this.loadSpecificsTree();
							}.createDelegate(this)
						});

						this.loadRoomList();
						this.setBedListAllowBlank();

						if (getRegionNick() != 'kz') {
							this.EvnPLDispScreenOnkoGrid.loadData({globalFilters: {EvnSection_id: base_form.findField('EvnSection_id').getValue()}});
						}

						if (
							(
								!Ext.isEmpty(base_form.findField('Diag_id').getFieldValue('Diag_Code'))
								&& (
									(base_form.findField('Diag_id').getFieldValue('Diag_Code').substr(0, 3) >= 'J12' && base_form.findField('Diag_id').getFieldValue('Diag_Code').substr(0, 3) <= 'J19')
									|| base_form.findField('Diag_id').getFieldValue('Diag_Code') == 'U07.1'
									|| base_form.findField('Diag_id').getFieldValue('Diag_Code') == 'U07.2'
								)
							)
							|| (
								getRegionNick() == 'msk'
								&& (this.CovidType_id == 2 || this.CovidType_id == 3)
							)
						) {
							this.RepositoryObservGrid.show();
							this.RepositoryObservGrid.doLayout();
						}

						this.RepositoryObservGrid.loadData({
							globalFilters: {
								Evn_id: base_form.findField('EvnSection_id').getValue()
							},
							noFocusOnLoad: true
						});

						this.isProcessLoadForm = false;

						if (getRegionNick() == 'kz' && this.EvnPS_IsWithoutDirection == 2) {
							base_form.findField('PayType_id').disable();
						}

						if (v = response_obj[0].PrehospWaifRetired_id)
							base_form.findField('PrehospWaifRetired_id').setValue(v);

						var isHSN = base_form.findField('Diag_id').getCode().inlist(['I50.0', 'I50.1', 'I50.9']);
						//debugger;
						if (isHSN) {
							base_form.findField('HSNStage_id').setValue(response_obj[0].HSNStage_id);
							base_form.findField('HSNStage_id').showContainer();
							base_form.findField('HSNFuncClass_id').setValue(response_obj[0].HSNFuncClass_id);
							base_form.findField('HSNFuncClass_id').showContainer();
						} else {
							base_form.findField('HSNStage_id').clearValue();
							base_form.findField('HSNStage_id').hideContainer();
							base_form.findField('HSNFuncClass_id').clearValue();
							base_form.findField('HSNFuncClass_id').hideContainer();
						
						}

					}.createDelegate(this),
					url: '/?c=EvnSection&m=loadEvnSectionEditForm'
				});
				break;

			default:
				loadMask.hide();

				break;
		}

		if (isDebug()) {
			if (this.action == 'edit') {

			}
			else {

			}
		}
		this.DataViewStore();		
		if (getRegionNick() == 'vologda'){
			this.DoctorHistoryDataViewStore();
			this.LpuSectionWardHistoryDataViewStore();
			this.LpuSectionBedProfileHistoryDataViewStore();
		}

	},

	processFieldCurResult: function () {

		var base_form = this.findById('EvnSectionEditForm').getForm();

		var LeaveTypeCombo = base_form.findField('LeaveType_id');


		// Поле видимо и обязательно для заполнения, если поле «Результат госпитализации» имеет ненулевое значение.
		if (!Ext.isEmpty(LeaveTypeCombo.getFieldValue('LeaveType_SysNick'))) {

			// поле видимо
			base_form.findField('CureResult_id').setContainerVisible(true);

			// поле обязательно
			base_form.findField('CureResult_id').setAllowBlank(false);


			// Если в поле «Результат госпитализации» указано значение "103 Переведён в дневной стационар" или "203 переведён в стационар",
			// то поле доступно для редактирования. При этом по умолчанию подставляется значение "Лечение завершено".
			if (LeaveTypeCombo.getFieldValue('LeaveType_SysNick').inlist(['ksstac', 'dsstac'])) {

				// Лечение завершено
				base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);

				base_form.findField('CureResult_id').enable();
			}
			else {

				// При других значениях поля «Результат госпитализации» поле не доступно для редактирования, но заполняется.
				base_form.findField('CureResult_id').disable();

				// Для значений "104. Переведён на другой профиль коек" и "204. Переведён на другой профиль коек"
				// указывается «Лечение продолжено».
				if (LeaveTypeCombo.getFieldValue('LeaveType_SysNick').inlist(['ksper', 'dsper'])) {

					// Лечение продолжено
					base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 2);

				}
				else { // Для других значений указывается «Лечение завершено».

					// Лечение завершено
					base_form.findField('CureResult_id').setFieldValue('CureResult_Code', 1);
				}
			}
		}

		return true;
	},

	/**
	 * Загружаем список палат, в котором должны быть: указанная палата и остальные палаты профильного отделения, соответствующие полу пациента (включая общие палаты), в которых есть свободные места
	 */
	filterDS: function (filt) {
		var store = this.findById('dataViewDiag').getStore();
		var evn_diag_ps_id = this.findById('EvnSectionEditForm').getForm().findField('EvnDiagPS_id').getValue();
		if (filt == 'save') {
			store.filterBy(function (rec) {
				if (rec.get('RecordStatus_Code') != 1) {
					return true;
				}
				else {
					return false;
				}
			});
		} else {
			store.filterBy(function (rec) {
				if (rec.get('EvnDiagPS_id') == evn_diag_ps_id || rec.get('RecordStatus_Code') == 3) {
					return false;
				}
				else {
					return true;
				}
			});
		}
		this.findById('dataViewDiag').refresh();
	},
	setIsMedReason: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var
			EvnSection_IsAdultEscort = base_form.findField('EvnSection_IsAdultEscort').getValue(),
			Person_Age = swGetPersonAge(this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());

		if (getRegionNick().inlist(['astra']) && EvnSection_IsAdultEscort == 2 && Person_Age >= 4) {
			base_form.findField('EvnSection_IsMedReason').setAllowBlank(false);
			base_form.findField('EvnSection_IsMedReason').setContainerVisible(true);

			if (Ext.isEmpty(base_form.findField('EvnSection_IsMedReason').getValue())) {
				base_form.findField('EvnSection_IsMedReason').setValue(1);
			}
		}
		else {
			base_form.findField('EvnSection_IsMedReason').setAllowBlank(true);
			base_form.findField('EvnSection_IsMedReason').setContainerVisible(false);
			base_form.findField('EvnSection_IsMedReason').setValue(1);
		}

		return true;
	},
	wardOnSexFilter: function () {
		var base_form = this.findById('EvnSectionEditForm').getForm(),
			filterdate = null;
		if (base_form.findField('EvnSection_setDate').getValue()) {
			filterdate = Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y');
		}
		sw.Promed.LpuSectionWard.filterWardBySex({
			date: filterdate,
			LpuSection_id: base_form.findField('LpuSection_id').getValue(),
			Sex_id: this.findById('ESecEF_PersonInformationFrame').getFieldValue('Sex_Code'),
			lpuSectionWardCombo: base_form.findField('LpuSectionWard_id'),
			win: this
		});
	},
	fillOnko: function (onkoData) {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var onkoFields = new Array(
			'AutopsyPerformType_id',
			'Diag_did',
			//'Diag_id'                        ,
			'OnkoDiag_mid',
			'Ethnos_id',
			'KLAreaType_id',
			'Lpu_foid',
			'MorbusBase_disDT',
			'MorbusOnko_IsDiagConfCito',
			'MorbusOnkoBase_deathCause',
			'MorbusOnko_IsDiagConfClinic',
			'MorbusOnko_IsDiagConfExplo',
			'MorbusOnko_IsDiagConfLab',
			'MorbusOnko_IsDiagConfMorfo',
			'MorbusOnko_IsDiagConfUnknown',
			'MorbusOnko_IsTumorDepoBones',
			'MorbusOnko_IsTumorDepoBrain',
			'MorbusOnko_IsTumorDepoKidney',
			'MorbusOnko_IsTumorDepoLiver',
			'MorbusOnko_IsTumorDepoLungs',
			'MorbusOnko_IsTumorDepoLympha',
			'MorbusOnko_IsTumorDepoMarrow',
			'MorbusOnko_IsTumorDepoMulti',
			'MorbusOnko_IsTumorDepoOther',
			'MorbusOnko_IsTumorDepoOvary',
			'MorbusOnko_IsTumorDepoPerito',
			'MorbusOnko_IsTumorDepoSkin',
			'MorbusOnko_IsTumorDepoUnknown',
			'MorbusOnko_NumCard',
			'MorbusOnko_NumHisto',
			'MorbusOnko_firstSignDT',
			'MorbusOnko_firstVizitDT',
			'MorbusOnko_specDisDT',
			'MorbusOnko_specSetDT',
			'OnkoLateDiagCause_id',
			'OnkoLesionSide_id',
			'OnkoM_id',
			'OnkoN_id',
			'OnkoRegOutType_id',
			'OnkoRegType_id',
			'OnkoT_id',
			//'Person_id'                      ,
			'TumorAutopsyResultType_id',
			'TumorCircumIdentType_id',
			'TumorPrimaryMultipleType_id',
			'TumorPrimaryTreatType_id',
			'TumorRadicalTreatIncomplType_id',
			'TumorStage_id',
			'pmUser_id',
			'MorbusOnko_IsMainTumor'
		);
		onkoFields.forEach(function (field) {
			var fieldControl = base_form.findField(field);
			if (fieldControl != undefined) {
				//есть такое поле
				fieldControl.reset();
				switch (fieldControl.xtype) {
					case 'hidden':
					case 'textfield':
					case 'swyesnocombo':
					case 'swonkolatediagcausecombo':
					case 'swonkolesionsidecombo':
					case 'swtumorautopsyresulttypecombo':
					case 'swtumorcircumidenttypecombo':
					case 'swtumorstagecombo':
					case 'swtumorprimarymultipletypecombo':
					case 'swtumorprimarytreattypecombo':
					case 'swtumorradicaltreatincompltypecombo':
					case 'swonkoregtypecombo':
					case 'swonkotcombo':
					case 'swonkomcombo':
					case 'swonkoncombo':
					case 'swonkoregouttypecombo':
					case 'swdatefield':
					case 'swonkodiagcombo':
					case 'swlpulocalcombo':
						if (undefined != onkoData[field]) {
							fieldControl.setValue(onkoData[field]);
						}
						break;
					default:
						log('unknown xtype ');
						log(fieldControl.xtype);
						log(fieldControl.id);
						log(fieldControl.name);
						log(fieldControl.hiddenName);
						break;
				}

			}
		});
		var TumorStage_id = base_form.findField('TumorStage_id');
		TumorStage_id.initialConfig.listeners.select(TumorStage_id);
	},
	checkOnkoLateDiagCause: function () {

		/* Причины поздней диагностики.
		 * Обязательно к заполнению и доступно для редактирования в случаях:
		 * а) если в поле «Стадия опухолевого процесса» выбрано одно из значений: «4а», «4б»,  «4с», «4 стадия»,
		 * б) в поле «Стадия опухолевого процесса»  выбрано одно из значений: «3а», «3б», «3с», «3 стадия»
		 *    и в поле «Диагноз» (в заболевании) выбрано одно из значений:  С00, С01, С02, С04, С06, С07, С08, С09, С20, С21, С44, С63.2, С51, С60, С50, С52, С53, С73, С62
		 */
		// TODO: Диагноз надо принимать с заболевания (пока для теста берётся с "Причины смерти")

		var form = this.findById('EvnSectionEditForm').getForm();
		var Diag_id = form.findField('Diag_id');
		var tumor_stage_id = form.findField('TumorStage_id').getValue();
		var diag_cause_id = form.findField('OnkoLateDiagCause_id');
		var diag_code_check = new Array('C00', 'C01', 'C02', 'C04', 'C06', 'C07', 'C08', 'C09', 'C20', 'C21', 'C44', 'C51', 'C60', 'C50', 'C52', 'C53', 'C73', 'C62');
		var diag_code = '';
		var diag_code_5s = '';

		var record = Diag_id.getStore().getById(Diag_id.getValue());
		if (record) {
			diag_code = record.get('Diag_Code').substr(0, 3);
			diag_code_5s = record.get('Diag_Code').substr(0, 5);
		}

		if (
			(tumor_stage_id >= 13 && tumor_stage_id <= 16) ||
			((tumor_stage_id >= 9 && tumor_stage_id <= 12) && (diag_code.inlist(diag_code_check) || diag_code_5s == 'C63.2'))
		) {
			diag_cause_id.allowBlank = false;
			diag_cause_id.enable();
		} else {
			diag_cause_id.allowBlank = true;
			diag_cause_id.setValue('');
			diag_cause_id.disable();
		}

	},
	setDiagEidAllowBlank: function () {
		if (!getRegionNick().inlist(['kz', 'ufa'])) {
			var base_form = this.findById('EvnSectionEditForm').getForm();
			var date = base_form.findField('EvnSection_setDate').getValue();
			var field = base_form.findField('Diag_eid');
			var xdate = new Date(2016, 0, 1);
			var diag_combo = base_form.findField('Diag_id');
			var diag_id = diag_combo.getValue();
			log(!Ext.isEmpty(diag_id));
			log(diag_combo.getStore().getById(diag_id));
			if (diag_combo.getStore().getById(diag_id))
				log(diag_combo.getStore().getById(diag_id).get('Diag_Code').search(new RegExp("^[ST]", "i")) >= 0);
			log((Ext.isEmpty(date) || date >= xdate));

			if (!Ext.isEmpty(diag_id)
				&& diag_combo.getStore().getById(diag_id)
				&& diag_combo.getStore().getById(diag_id).get('Diag_Code').search(new RegExp("^[ST]", "i")) >= 0
				&& (Ext.isEmpty(date) || date >= xdate)
				&& this.action != 'view'
			) {
				field.setAllowBlank(false);
				field.enable();
			} else {
				field.setAllowBlank(true);
				field.disable();
			}
		}
	},
	setBedListAllowBlank: function() {
		var win = this,
			base_form = this.getFormPanel()[0].getForm(),
			getroom_field = base_form.findField('GetRoom_id'),
			getbed_field = base_form.findField('GetBed_id'),
			paytype_sysnick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick'),
			allowBlank = !paytype_sysnick || !paytype_sysnick.inlist(['bud', 'Resp']) || getRegionNick() != 'kz';
			
		getroom_field.setAllowBlank(allowBlank);
		getbed_field.setAllowBlank(allowBlank);
	},
	loadRoomList: function() {
		if (getRegionNick() != 'kz') return false;
		var win = this,
			base_form = this.getFormPanel()[0].getForm(),
			getroom_field = base_form.findField('GetRoom_id');
			getbed_field = base_form.findField('GetBed_id');
		
		getroom_field.lastQuery = '';
		getroom_field.getStore().load({
			params: {
				Lpu_id: base_form.findField('LpuSection_id').getFieldValue('Lpu_id') || getGlobalOptions().lpu_id,
				LpuSection_id: base_form.findField('LpuSection_id').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				GetRoom_id: win.action == 'view' ? getroom_field.getValue() : null
			},
			callback: function() {
				var index = getroom_field.getStore().findBy(function(rec) {
					return rec.get('GetRoom_id') == getroom_field.getValue();
				});
				if (index == -1) {
					getroom_field.clearValue();
					getbed_field.clearValue();
				}
				getroom_field.setValue(getroom_field.getValue());
				win.loadBedList();
			}		
		});
	},
	control_departmentProfile_typeOfPayment_typeOfHospitalization: function(){
		//контроль на соответствие профиля отделения, вида оплаты типу госпитализации.
		if (!getRegionNick().inlist(['kareliya'])){
			return true;
		}
		var win = this,
			base_form = this.getFormPanel()[0].getForm(),
			lpuSectionProfile_Code = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code'),
			payType_Code = base_form.findField('PayType_id').getFieldValue('PayType_Code');
		
		if(this.PrehospType_SysNick && this.PrehospType_SysNick != 'plan' && lpuSectionProfile_Code == 158 && payType_Code == 1){
			sw.swMsg.alert('Ошибка', 'В отделении медицинской реабилитации возможна только плановая госпитализация');
			return false;
		}else{
			return true;
		}
	},
	getInvalidFields: function() {
		var invalidFields = [];
		var base_form = this.getFormPanel()[0].getForm();
		base_form.items.filterBy(function(field) {
			if (field.validate()) return;
			var name = (field.hiddenName) ? field.hiddenName : (field.name) ? field.name : '';
			var fieldLabel = (field.fieldLabel) ? field.fieldLabel : '';
			invalidFields.push('name: ' + name + ', fieldLabel: ' + fieldLabel);
		});
		console.warn('InvalidFields: ' + invalidFields.join(' ; '));
	},
	loadBedList: function(autoValue) {
		if (getRegionNick() != 'kz') return false;
		var win = this,
			base_form = this.getFormPanel()[0].getForm(),
			getroom_id = base_form.findField('GetRoom_id').getValue(),
			getbed_field = base_form.findField('GetBed_id');
			
		if(!getroom_id) return false;
		
		getbed_field.lastQuery = '';
		getbed_field.getStore().load({
			params: {
				GetRoom_id: getroom_id,
				GetBed_id: win.action == 'view' ? getbed_field.getValue() : null
			},
			callback: function() {
				if (autoValue) {
					getbed_field.setValue(getbed_field.getStore().getAt(0).get('GetBed_id'));
				} else {
					getbed_field.setValue(getbed_field.getValue());
				}
				
			}		
		});
	},
	width: 800
});
