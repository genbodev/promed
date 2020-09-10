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
sw.Promed.swEvnSectionEditWindow = Ext.extend(sw.Promed.BaseForm,{
	action:null,
	editAnatom:false,
	editPersonNewBorn:null,
	buttonAlign:'left',
	callback:Ext.emptyFn,
	closable:false,
	closeAction:'hide',
	codeRefresh:true,
	objectName:'swEvnSectionEditWindow',
	objectSrc:'/jscore/Forms/Hospital/swEvnSectionEditWindow.js',
	collapsible:true,
	flbr:false,
	changedDates:false,
	refreshFieldsVisibility: function (fieldNames) {
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

			var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();
			var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();

			switch (field.getName()) {
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
	getBirthSpecStacDefaults: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var cat_form = this.WizardPanel.getCategory('Result').getForm();
		var oldValues = cat_form.getValues();
		//Параметры исхода, которые можно рассчитать на клиенте
		var values = {
			Lpu_oid: getGlobalOptions().lpu_id,
			MedPersonal_oid: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
			AbortLpuPlaceType_id: Ext.isEmpty(oldValues.AbortLpuPlaceType_id)?oldValues.AbortLpuPlaceType_id:2,
			BirthCharactType_id: Ext.isEmpty(oldValues.BirthCharactType_id)?oldValues.BirthCharactType_id:1,
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

			if ((diag_code.slice(0,3) >= 'O81' && diag_code.slice(0,3) <= 'O83') || (diag_code >= 'O84.1' && diag_code <= 'O84.8')) {
				values.BirthCharactType_id = 2;
			}
		}
		this.findById('ESecEF_EvnDiagPSGrid').getStore().each(function(rec){
			diag_list.push(rec.get('Diag_Code'));
		});

		diag_list.forEach(function(code) {
			if (code.slice(0,3) == 'O15') {
				values.QuestionType_521 = 2;
			}
			if (code.slice(0,3) == 'O42') {
				values.QuestionType_522 = 2;
			}
			if (code >= 'O62.0' && code <= 'O62.2') {
				values.QuestionType_523 = 2;
			}
			if (code == 'O62.3') {
				values.QuestionType_524 = 2;
			}
			if (code.slice(0,3) == 'O45') {
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
	recalcBirthSpecStacDefaults: function() {
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
	changeDiag:function(diagCombo,value){
		var store =  this.findById('dataViewDiag').getStore();
		store.clearFilter();
		var evn_diag_ps_id = this.findById('EvnSectionEditForm').getForm().findField('EvnDiagPS_id').getValue();
		var indexDiag= store.findBy(function (rec){return rec.get('EvnDiagPS_id') == evn_diag_ps_id});
		var record = store.getAt(indexDiag);
		var index= diagCombo.getStore().findBy(function (rec) {
		return rec.get('Diag_id') == value});
		if(index>=0&&record!=null){	
		record.set('Diag_id',diagCombo.getStore().getAt(index).get('Diag_id'));
		record.set('Diag_Name',diagCombo.getStore().getAt(index).get('Diag_Name'));
		record.set('Diag_Code',diagCombo.getStore().getAt(index).get('Diag_Code'));
		if(record.get('RecordStatus_Code')==1){
			record.set('RecordStatus_Code',2);
		}
		record.commit();
		}
		this.filterDS();
	},
	deleteClinDiag:function(event,id){
		var that = this;
		var store =  this.findById('dataViewDiag').getStore();
		var index = store.findBy(function(record, idd){return idd == id;});
		var record = store.getAt(index);
		if(this.action=='edit'){
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg:"Удаление записи..."});
					loadMask.show();
					if(record.get('RecordStatus_Code')!=0){
						loadMask.hide();
						record.set('RecordStatus_Code',3);
						record.commit();
						this.filterDS();
					}else{
					loadMask.hide();
					store.removeAt(index);
					this.filterDS();
				}
				}
				else {
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:'Удалить диагноз?',
			title:'Вопрос'
		});
		}
	},

	getPregnancyPersonRegister: function(callback) {
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
			callback: function(options, success, response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) {
					this.PersonRegister_id = response_obj.PersonRegister_id || null;
					callback();
				}
			}.createDelegate(this)
		});
	},

	deleteEvent:function (event) {
		var that = this;

		if (typeof event != 'string' && !event.toString().inlist([ 'EvnDiagPS', 'EvnDiagPSDie', 'EvnSectionNarrowBed' ])) {
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
				LoadEmptyRow(grid);
			}

			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
		else {
			if (this.action == 'view') {
				return false;
			}

			var error = '';
			var grid = null;
			var question = '';
			var params = new Object();
			var url = '';

			switch (event) {
				case 'EvnDiagPS':
					error = 'При удалении диагноза возникли ошибки';
					grid = this.findById('ESecEF_EvnDiagPSGrid');
					question = 'Удалить диагноз?';
					url = '/?c=EvnDiag&m=deleteEvnDiag';
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
				case 'EvnDiagPS':
					params['class'] = 'EvnDiagPS';
					params['id'] = selected_record.get('EvnDiagPS_id');
					break;
				case 'EvnUsluga':
					params['id'] = selected_record.get('EvnUsluga_id');
					break;
				case 'EvnSectionNarrowBed':
					params['EvnSectionNarrowBed_id'] = selected_record.get('EvnSectionNarrowBed_id');
					break;
			}
			sw.swMsg.show({
				buttons:Ext.Msg.YESNO,
				fn:function (buttonId, text, obj) {
					if (buttonId == 'yes') {
						var loadMask = new Ext.LoadMask(this.getEl(), {msg:"Удаление записи..."});
						loadMask.show();

						Ext.Ajax.request({
							failure:function (response, options) {
								loadMask.hide();
								sw.swMsg.alert('Ошибка', error);
							},
							params:params,
							success:function (response, options) {
								loadMask.hide();

								var response_obj = Ext.util.JSON.decode(response.responseText);

								if (response_obj.success == false) {
									sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : error);
								}
								else {
									grid.getStore().remove(selected_record);

									if ( event == 'EvnUsluga' ) {
										this.EvnUslugaGridIsModified = true;
									}

									if (grid.getStore().getCount() == 0) {
										grid.getTopToolbar().items.items[1].disable();
										grid.getTopToolbar().items.items[2].disable();
										grid.getTopToolbar().items.items[3].disable();
										LoadEmptyRow(grid);
									}
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							},
							url:url
						});
					}
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				icon:Ext.MessageBox.QUESTION,
				msg:question,
				title:'Вопрос'
			});
		}
	},
	doSave:function (options) {
		var that = this;
		// options @Object
		// options.openChildWindow @Function Открыть дочернее окно после сохранения
		if (this.formStatus == 'save' || (this.action == 'view' && this.editAnatom == false)) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}
		
		this.formStatus = 'save';

		var base_form = this.findById('EvnSectionEditForm').getForm();

		var isNotPerm = (getGlobalOptions().region && getGlobalOptions().region.nick != 'perm');
		var isSamara = (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara');
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons:Ext.Msg.OK,
				fn:function () {
					this.formStatus = 'edit';
					this.findById('EvnSectionEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon:Ext.Msg.WARNING,
				msg:ERR_INVFIELDS_MSG,
				title:ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (this.WizardPanel && !this.WizardPanel.isValid()) {
			this.formStatus = 'edit';
			return false;
		}

		var diag_code = '', diag_name = '', index, pay_type_nick = '', record;

		// Получаем вид оплаты
		index = base_form.findField('PayType_id').getStore().findBy(function(rec) {
			return (rec.get('PayType_id') == base_form.findField('PayType_id').getValue());
		});

		if ( index >= 0 ) {
			pay_type_nick = base_form.findField('PayType_id').getStore().getAt(index).get('PayType_SysNick');
		}

		index = base_form.findField('Diag_id').getStore().findBy(function(rec) {
			return (rec.get('Diag_id') == base_form.findField('Diag_id').getValue());
		});
		record = base_form.findField('Diag_id').getStore().getAt(index);

		if ( record ) {
			diag_code = record.get('Diag_Code');
			diag_name = record.get('Diag_Name');

			// https://redmine.swan.perm.ru/issues/4081
			// https://redmine.swan.perm.ru/issues/26975
			// https://redmine.swan.perm.ru/issues/28745
			// Проверка на финансирование по ОМС основного диагноза
			if ( isNotPerm == true && pay_type_nick == 'oms' && (isUfa == false || Ext.isEmpty(base_form.findField('Mes_id').getValue())) ) {
				if ( record.get('DiagFinance_IsOms') == 0 ) {
					sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается по ОМС', function() {
						this.formStatus = 'edit';
						base_form.findField('Diag_id').markInvalid('Диагноз не оплачивается по ОМС');
						base_form.findField('Diag_id').focus(true);
					}.createDelegate(this));
					return false;
				}
				else {
					var oms_spr_terr_code = this.findById('ESecEF_PersonInformationFrame').getFieldValue('OmsSprTerr_Code');
					var person_age = swGetPersonAge(this.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'), base_form.findField('EvnSection_setDate').getValue());
					var sex_code = this.findById('ESecEF_PersonInformationFrame').getFieldValue('Sex_Code');

					if ( person_age == -1 ) {
						this.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Ошибка при определении возраста пациента');
						return false;
					}

					if ( Ext.isEmpty(sex_code) || !(sex_code.toString().inlist([ '1', '2' ])) ) {
						this.formStatus = 'edit';
						sw.swMsg.alert('Ошибка', 'Не указан пол пациента');
						return false;
					}

					if ( person_age >= 18 ) {
						if ( Number(record.get('PersonAgeGroup_Code')) == 2 ) {
							sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается для взрослых', function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid('Диагноз не оплачивается для взрослых');
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
					else if ( Number(record.get('PersonAgeGroup_Code')) == 1 ) {
						sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается для детей', function() {
							this.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid('Диагноз не оплачивается для детей');
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}

					if ( Number(sex_code) == 1 ) {
						if ( Number(record.get('Sex_Code')) == 2 ) {
							sw.swMsg.alert('Ошибка', 'Диагноз не соответствует полу пациента', function() {
								this.formStatus = 'edit';
								base_form.findField('Diag_id').markInvalid('Диагноз не соответствует полу пациента');
								base_form.findField('Diag_id').focus(true);
							}.createDelegate(this));
							return false;
						}
					}
					else if ( Number(record.get('Sex_Code')) == 1 ) {
						sw.swMsg.alert('Ошибка', 'Диагноз не соответствует полу пациента', function() {
							this.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid('Диагноз не соответствует полу пациента');
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}

					if ( isUfa == true && oms_spr_terr_code != 61 && record.get('DiagFinance_IsAlien') == '0' ) {
						sw.swMsg.alert('Ошибка', 'Диагноз не оплачивается для пациентов, застрахованных не в РБ', function() {
							this.formStatus = 'edit';
							base_form.findField('Diag_id').markInvalid('Диагноз не оплачивается для пациентов, застрахованных не в РБ');
							base_form.findField('Diag_id').focus(true);
						}.createDelegate(this));
						return false;
					}
				}
			}
		}
			
		var evn_section_dis_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_disTime').getValue());
		var evn_section_set_dt = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_setTime').getValue());

		if (evn_section_set_dt == null) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Неверное значение даты/времени поступления в отделение');
			return false;
		}
		else if (this.evnPSSetDT != null && evn_section_set_dt < this.evnPSSetDT) {
			this.formStatus = 'edit';
			sw.swMsg.alert('Ошибка', 'Дата/время поступления в отделение меньше даты/времени госпитализации');
			return false;
		}
		else if (evn_section_dis_dt != null && evn_section_set_dt > evn_section_dis_dt) {
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
		var diagRec=new Object();
		var DataViewDiagStore = this.findById('dataViewDiag').getStore();
		DataViewDiagStore.clearFilter();

		if ( DataViewDiagStore.getCount()==0 && base_form.findField('EvnSection_id').getValue() == 0 ) {
			/****/
			diagRec = [{
				EvnDiagPS_id:-swGenTempId(DataViewDiagStore),
				Diag_Code:diag_name,
				Diag_Name:diag_code,
				Diag_id:base_form.findField('Diag_id').getValue(),
				EvnDiagPS_pid:base_form.findField('EvnSection_id').getValue(),
				Person_id:base_form.findField('Person_id').getValue(),
				DiagSetClass_id:1,
				PersonEvn_id:base_form.findField('PersonEvn_id').getValue(),
				Server_id:base_form.findField('Server_id').getValue(),
				RecordStatus_Code:0,
				EvnDiagPS_setDate:Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y')
			}];
			diagRec.Rec = 0;
			//log(diagRec);
			DataViewDiagStore.loadData(diagRec, true);
		}
		/****/
		var params = new Object();
		//log(DataViewDiagStore);
		if (DataViewDiagStore.getCount() > 0 ) {
			this.filterDS('save');
			var DataViewDiag = getStoreRecords(DataViewDiagStore, {convertDateFields: true});
			params.DataViewDiag = Ext.util.JSON.encode(DataViewDiag);
		}
		this.DataViewStore();
		//log(params);
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

		params.EvnSection_disDate = Ext.util.Format.date(evn_section_dis_dt, 'd.m.Y');
		params.EvnSection_setDate = Ext.util.Format.date(evn_section_set_dt, 'd.m.Y');

		if (base_form.findField('EvnSection_disTime').disabled) {
			params.EvnSection_disTime = base_form.findField('EvnSection_disTime').getRawValue();
		}

		if (base_form.findField('EvnSection_setTime').disabled) {
			params.EvnSection_setTime = base_form.findField('EvnSection_setTime').getRawValue();
		}

		if(base_form.findField('PersonNewBorn_id')&&!base_form.findField('PersonNewBorn_id').disabled){
			var apgarGrid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();
			apgarGrid.getStore().clearFilter();
			if ( apgarGrid.getStore().getCount() > 0 ) {
				var ApgarData = getStoreRecords(apgarGrid.getStore());


				params.ApgarData = Ext.util.JSON.encode(ApgarData);

				apgarGrid.getStore().filterBy(function(rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});
			}
			var PersonBirthTraumaData =[];
			var tGrid;
			for(var x = 1;x<5;x++){
				tGrid = this.findById('ESEW_PersonBirthTraumaGrid'+x).getGrid();
				tGrid.getStore().clearFilter();
				if ( tGrid.getStore().getCount() > 0 ) {
					[].push.apply(PersonBirthTraumaData,getStoreRecords(tGrid.getStore()))
					tGrid.getStore().filterBy(function(rec) {return (Number(rec.get('RecordStatus_Code')) != 3);});
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
		this.specBirthData.birthWeight = null;
		this.specBirthData.Okei_id = null;
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
		/**/
		var loadMask = new Ext.LoadMask(this.getEl(), {msg:"Подождите, идет сохранение случая движения пациента в стационаре..."});
		loadMask.show();

		// Собираем данные из таблицы "Сопутствующие патологоанатомические диагнозы"
		var anatom_diag_grid = this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid();

		anatom_diag_grid.getStore().clearFilter();

		if (anatom_diag_grid.getStore().getCount() > 0 && anatom_diag_grid.getStore().getAt(0).get('EvnDiagPS_id')) {
			var anatom_diag_data = getStoreRecords(anatom_diag_grid.getStore(), {
				convertDateFields:true,
				exceptionFields:[
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

		params.PersonRegister_id = (this.PersonRegister_id>0)?this.PersonRegister_id:null;

		if (this.WizardPanel) {
			this.WizardPanel.categories.each(function(category){
				var categoryData = category.getCategoryData(category);
				if (categoryData && categoryData.status != 3) {
					category.saveCategory(category);
				}
			});

			this.WizardPanel.setReadOnly(true);
			params = Ext.apply(params, this.WizardPanel.getDataForSave(true));
		}

		if (options && options.openChildWindow ) {
			params.silentSave = '1';
		} else {
			params.silentSave = '0';
		}

		params.editAnatom = (this.editAnatom == true)?2:1;
		if (this.editAnatom) {
			this.enableEdit(true);
		}

		base_form.submit({
			failure:function (result_form, action) {
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
					if ( action.result.Error_Msg && 'YesNo' != action.result.Error_Msg) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					} else if ( action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (this.WizardPanel && action.result.Error_Code == 201) {
										this.WizardPanel.getCategory('Result').ignoreCheckBirthSpecStacDate = 1;
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
					} else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			}.createDelegate(this),
			params:params,
			success:function (result_form, action) {
				if (this.editAnatom) {
					this.enableEdit(false);
					this.enableAnatomFormEdit(true);
				}
				this.formStatus = 'edit';
				loadMask.hide();

				if (action.result) {
					if (action.result.EvnSection_id) {
						if (action.result.PersonRegister_id !== undefined) {
							this.PersonRegister_id = action.result.PersonRegister_id;
						}
						var evn_section_id = action.result.EvnSection_id;
						base_form.findField('EvnSection_id').setValue(evn_section_id);
						this.formParams.EvnSection_id = evn_section_id;
						
						if (this.WizardPanel) {
							this.WizardPanel.categories.each(function(category){
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
											callback: function() {
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
									for(oldId in PregnancyScreenResponse) {
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

							apgarGrid.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});

							if (this.isTraumaTabGridLoaded) {
								var grid1 = this.findById('ESEW_PersonBirthTraumaGrid1').getGrid();
								var grid2 = this.findById('ESEW_PersonBirthTraumaGrid2').getGrid();
								var grid3 = this.findById('ESEW_PersonBirthTraumaGrid3').getGrid();
								var grid4 = this.findById('ESEW_PersonBirthTraumaGrid4').getGrid();

								grid1.getStore().baseParams.BirthTraumaType_id = 1;
								grid2.getStore().baseParams.BirthTraumaType_id = 2;
								grid3.getStore().baseParams.BirthTraumaType_id = 3;
								grid4.getStore().baseParams.BirthTraumaType_id = 4;

								grid1.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});
								grid2.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});
								grid3.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});
								grid4.getStore().load({params:{PersonNewBorn_id:action.result.PersonNewBorn_id}});
							}
						}
					
						if (getGlobalOptions().region.nick == 'samara') {							
						    var onko = this.findById('ESecEF_EvnOnkoForm');
						    if (onko && onko.isLoaded){
						        onko.getForm().submit();
						    }
						}
						
						// var evn_section_id = action.result.EvnSection_id;
						// base_form.findField('EvnSection_id').setValue(evn_section_id);
						// this.formParams.EvnSection_id = evn_section_id;

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
								accessType:'edit',
								Diag_id:base_form.findField('Diag_id').getValue(),
								EvnSection_disDate:base_form.findField('EvnSection_disDate').getValue(),
								EvnSection_disTime:base_form.findField('EvnSection_disTime').getValue(),
								EvnSection_id:evn_section_id,
								EvnSection_KoikoDni:base_form.findField('EvnSection_KoikoDni').getValue(),
								EvnSection_IsAdultEscort:base_form.findField('EvnSection_IsAdultEscort').getValue(),
								EvnSection_IsMeal:base_form.findField('EvnSection_IsMeal').getValue(),
								EvnSection_KoikoDniNorm:base_form.findField('EvnSection_KoikoDniNorm').getValue(),
								EvnSection_pid:base_form.findField('EvnSection_pid').getValue(),
								EvnSection_setDate:base_form.findField('EvnSection_setDate').getValue(),
								EvnSection_setTime:base_form.findField('EvnSection_setTime').getValue(),
								MedStaffFact_id:base_form.findField('MedStaffFact_id').getValue(),
								LpuSection_id:base_form.findField('LpuSection_id').getValue(),
								LpuSectionWard_id:base_form.findField('LpuSectionWard_id').getValue(),
								LpuSectionWard_Name:base_form.findField('LpuSectionWard_id').getFieldValue('LpuSectionWard_Name'),
								Mes_id:base_form.findField('Mes_id').getValue(),
								Mes2_id:base_form.findField('Mes2_id').getValue(),
								PayType_id:base_form.findField('PayType_id').getValue(),
								TariffClass_id:base_form.findField('TariffClass_id').getValue(),
								Person_id:base_form.findField('Person_id').getValue(),
								PersonEvn_id:base_form.findField('PersonEvn_id').getValue(),
								Server_id:base_form.findField('Server_id').getValue(),
								DiagSetPhase_id:base_form.findField('DiagSetPhase_id').getValue(),
								DiagSetPhase_Name:base_form.findField('DiagSetPhase_id').getRawValue(),
								EvnSection_PhaseDescr:base_form.findField('EvnSection_PhaseDescr').getValue(),
								EvnDie_id:base_form.findField('EvnDie_id').getValue(),
								EvnLeave_id:base_form.findField('EvnLeave_id').getValue(),
								EvnOtherLpu_id:base_form.findField('EvnOtherLpu_id').getValue(),
								EvnOtherSection_id:base_form.findField('EvnOtherSection_id').getValue(),
								EvnOtherStac_id:base_form.findField('EvnOtherStac_id').getValue(),
								LeaveType_Code:leave_type_code,
								LeaveType_id:leave_type_id,
								LeaveType_Name:leave_type_name,

								birthHeight:this.specBirthData.birthHeight,
								birthWeight:this.specBirthData.birthWeight,
								Okei_id:this.specBirthData.Okei_id,
								countChild:this.specBirthData.countChild,
								PersonWeight_text:this.specBirthData.PersonWeight_text
							}
							this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().each(function (rec) {
								if (typeof evn_section_narrow_bed_set_dt != 'object' || evn_section_narrow_bed_set_dt < getValidDT(Ext.util.Format.date(rec.get('EvnSectionNarrowBed_setDate'), 'd.m.Y'), typeof rec.get('EvnSectionNarrowBed_setTime') == 'string' && rec.get('EvnSectionNarrowBed_setTime').length == 5 ? rec.get('EvnSectionNarrowBed_setTime') : '00:00')) {
									evn_section_narrow_bed_set_dt = getValidDT(Ext.util.Format.date(rec.get('EvnSectionNarrowBed_setDate'), 'd.m.Y'), typeof rec.get('EvnSectionNarrowBed_setTime') == 'string' && rec.get('EvnSectionNarrowBed_setTime').length == 5 ? rec.get('EvnSectionNarrowBed_setTime') : '00:00');
									lpu_section_profile_name = rec.get('LpuSectionProfile_Name');
								}
							});

							record = base_form.findField('LpuSection_id').getStore().getById(response.LpuSection_id);
							if (record) {
								response.LpuUnitType_id = record.get('LpuUnitType_id');
								response.LpuUnitType_SysNick = record.get('LpuUnitType_SysNick');
								response.LpuSection_Name = record.get('LpuSection_Name');

								if (lpu_section_profile_name.length == 0) {
									lpu_section_profile_name = record.get('LpuSectionProfile_Name');
								}
							}

							record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
							if (record) {
								response.MedPersonal_Fio = record.get('MedPersonal_Fio');
								response.MedPersonal_id = record.get('MedPersonal_id');
							}

							record = base_form.findField('PayType_id').getStore().getById(response.PayType_id);
							if (record) {
								response.PayType_Name = record.get('PayType_Name');
							}

							response.Diag_Name = (diag_code.length > 0)?(diag_code + '. ' + diag_name):null;
							
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
							this.callback({evnSectionData:[ response ]});
							
							if (options && typeof options.silent == 'function') {
								options.silent();
							} else {
								this.hide();
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
	draggable:true,
	enableEdit:function (enable) {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var form_fields = new Array(
			'Diag_id',
			'EvnSection_setDate',
			'EvnSection_setTime',
			'EvnSection_disDate',
			'EvnSection_disTime',
			'LpuSection_id',
			'LpuSectionWard_id',
			'LpuSectionBedProfile_id',
			'DiagSetPhase_id',
			'EvnSection_PhaseDescr',
			'LeaveType_id',
			'LeaveTypeFed_id',
			'MedStaffFact_did',
			'DeathPlace_id',
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
			'LpuUnitType_oid',
			'ResultDesease_id',
			'MedStaffFact_id',
			'PayType_id',
			'TariffClass_id',
			'EvnSection_IsAdultEscort',
			'EvnSection_IsMeal',
			'Mes_id',
			'UslugaComplex_id'
		);
		var i = 0;

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
	resizeSpecificForWizardPanel: function() {
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

				page.setHeight(height-36);
				this.WizardPanel.setHeight(height-36);
				this.specificsPanel.setHeight(height);
				page.doLayout();
			} else {
				var height = 0;
				page.items.each(function(item) {
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
					this.specificsPanel.setHeight(height+5+26);
				} else {
					this.specificsPanel.setHeight(height+5);
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
	createPersonPregnancyCategory: function(categoryName) {
		if (!this.WizardPanel) {
			this.createPersonPregnancyWizardPanel();
		}
		this.WizardPanel.show();
		this.WizardPanel.createCategoryController(categoryName);
	},
	deletePersonPregnancyCategory: function(categoryName, id) {
		if (!this.WizardPanel) {
			this.createPersonPregnancyWizardPanel();
		}
		this.WizardPanel.deleteCategoryController(categoryName, id);
	},
	printPregnancyResult: function() {
		var wnd = this;
		var category = this.WizardPanel.getCurrentCategory();

		if (!category || category.name != 'Result') {
			return false;
		}

		if (!Ext.isEmpty(this.PersonRegister_id) && category.BirthSpecStac_id < 0) {
			category.saveCategory(category, function() {
				wnd.doSave({silent: function(){wnd.printPregnancyResult()}});
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
	createPersonPregnancyWizardPanel: function() {
		var wnd = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var tree = this.specificsTree;
		var personInfoPanel = Ext.getCmp('ESecEF_PersonInformationFrame');

		var inputData = new sw.Promed.PersonPregnancy.InputData({
			fn: function() {
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
		var afterPregnancyResultChange = function(options) {
			if (options && options.resize) {
				wnd.resizeSpecificForWizardPanel();
			}
			if (options && options.recalc) {
				wnd.recalcBirthSpecStacDefaults();
			}
		};
		var beforeChildAdd = function(objectToReturn, addFn) {
			var category = wnd.WizardPanel.getCategory('Result');
			var categoryData = category.getCategoryData(category);
			if (categoryData && (categoryData.status.inlist([-1, 0]) || Ext.isEmpty(categoryData.EvnSection_id))) {
				//Перед добавлением новорожденного происходит сохранение движения
				//с измененными данными по беременности, если исход беременности ещё не был сохранен
				category.saveCategory(category, function() {
					wnd.doSave({silent: addFn});
				});
				return false;
			}
			return true;
		};
		var wizardValidator = function() {
			var valid = true;
			wnd.WizardPanel.categories.each(function(category){
				if (category.loaded && category.validateCategory(category, true) === false) {
					valid = false;
					return false;
				}
			});
			return valid;
		};
		var afterPageChange = function() {
			wnd.resizeSpecificForWizardPanel();

			var category = wnd.WizardPanel.getCurrentCategory();

			if (category) {
				var values = category.getForm().getValues();
				wnd.PersonRegister_id = values.PersonRegister_id;

				wnd.WizardPanel.PrintResultButton.setVisible(category.name == 'Result' && !Ext.isEmpty(wnd.PersonRegister_id));
			}
		};

		var updateScreenNode = function(categoryData) {
			var nodeId = 'PregnancyScreen_'+categoryData.PregnancyScreen_id;
			var text = new Ext.Template('{date}, {period} нед., Пер. риск {risk}').apply({
				date: categoryData.PregnancyScreen_setDate,
				period: categoryData.amenordate || categoryData.embriondate || categoryData.uzidate || categoryData.fmovedate || '*',
				risk: '*'
			});

			switch(categoryData.status) {
				case 0: text += ' <span class="status created">Новый</span>';break;
				case 2: text += ' <span class="status updated">Изменен</span>';break;
				case 3: text += ' <span class="status deleted">Удален</span>';break;
			}

			var tplDelete = new Ext.Template('<span class="link delete" onclick="{method}(\'{categoryName}\', {id})">Удалить</span>');
			if (categoryData.status.inlist([0,1,2])) {
				text += tplDelete.apply({
					id: categoryData.PregnancyScreen_id,
					categoryName: 'Screen',
					method: "Ext.getCmp('"+wnd.getId()+"').deletePersonPregnancyCategory"
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

			screenListNode.sort(function(node1, node2) {
				return Date.parseDate(node1.attributes.date, 'd.m.Y') > Date.parseDate(node2.attributes.date, 'd.m.Y');
			});

			tree.getSelectionModel().select(screenNode);
		};

		var updateCategoryNode = function(category, id, action) {
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
					node.attributes.key = (action == 'delete')?null:id;

					var textEl = Ext.get(node.ui.elNode).child('.x-tree-node-anchor').child('span');

					if (textEl.child('.status')) {
						textEl.child('.status').remove();
					}
					if (textEl.child('.link')) {
						textEl.child('.link').remove();
					}

					switch(categoryData && categoryData.status) {
						case 0: textEl.createChild('<span class="status created">Новый</span>');break;
						case 2: textEl.createChild('<span class="status updated">Изменен</span>');break;
						case 3: textEl.createChild('<span class="status deleted">Удален</span>');break;
					}

					var tplCreate = new Ext.Template('<span class="link create" onclick="{method}(\'{categoryName}\')">Создать</span>');
					if (!categoryData) {
						textEl.createChild(tplCreate.apply({
							categoryName: category.name,
							method: "Ext.getCmp('"+wnd.getId()+"').createPersonPregnancyCategory"
						}));
					}

					var tplDelete = new Ext.Template('<span class="link delete" onclick="{method}(\'{categoryName}\', {id})">Удалить</span>');
					if (categoryData && categoryData.status.inlist([0,1,2])) {
						textEl.createChild(tplDelete.apply({
							id: node.attributes.key,
							categoryName: category.name,
							method: "Ext.getCmp('"+wnd.getId()+"').deletePersonPregnancyCategory"
						}));
					}
				}
			}
		};

		var saveCategory = function(category, callback) {
			if (category.validateCategory(category, true) === false){
				return false;
			}

			if (category.beforeSaveCategory(category) === false) {
				return false;
			}

			var id = category[category.idField];
			category.collectCategoryData(category, (id<0)?0:2);
			category.afterSaveCategory(category);

			if (typeof callback == 'function') callback();
		};

		var afterSaveCategory = function(category) {
			var categoryData = category.getCategoryData(category);

			if (category.name == 'Screen') {
				category.data.sort(function(a, b){
					return Date.parseDate(a.PregnancyScreen_setDate, 'd.m.Y') > b.parseDate(b.PregnancyScreen_setDate, 'd.m.Y');
				});
			}

			if (category.name == 'Anketa' && categoryData.status == 0) {
				var node = tree.nodeHash.Anketa;
				tree.getLoader().baseParams.PersonRegister_id = categoryData.PersonRegister_id;
				tree.getLoader().baseParams.object = node.attributes.object;
				tree.getLoader().load(node, function() {
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

		var beforeDeleteCategory = function(category, id) {
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
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success) {
								category.allowDelete = true;
								category.deleteCategory(category, id);
							}
						},
						failure: function() {
							loadMask.hide();
						}
					});
					return false;
				}
			}
		};

		var deleteCategory = function(category, id) {
			var deleteCategory = function() {
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
				} else if(id > 0) {
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
					buttons:Ext.Msg.YESNO,
					fn:function (buttonId, text, obj) {
						if (buttonId == 'yes') {
							category.wantDelete = true;
							deleteCategory();
						}
					}.createDelegate(this),
					icon:Ext.MessageBox.QUESTION,
					msg:lang['vyi_hotite_udalit_zapis'],
					title:lang['podtverjdenie']
				});
			}
		};

		var afterDeleteCategory = function(category, id) {
			switch(category.name) {
				case 'Screen':
					var parentNode = tree.nodeHash.ScreenList;
					var node = parentNode.findChild('key', id);

					parentNode.removeChild(node);
					break;
				case 'Anketa':
					updateCategoryNode(category, id, 'delete');

					var anketaNode = tree.nodeHash.Anketa;
					while(anketaNode.childNodes.length != 0) {
						anketaNode.removeChild(anketaNode.childNodes[anketaNode.childNodes.length-1]);
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

		var cancelCategory = function(category, onCancel) {
			switch(true) {
				case (category.name == 'Result' && !wnd.WizardPanel.deleteEvnSection && wnd.createdObjects.BirthSpecStac_id > 0):
					var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Отмена добавления исхода беременности..."});
					loadMask.show();

					Ext.Ajax.request({
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								delete wnd.createdObjects.BirthSpecStac_id;
								wnd.treeLoaded = false;
								wnd.onSpecificsExpand(wnd.specificsPanel);
								onCancel();
							}
						},
						failure: function(response) {
							loadMask.hide();
						},
						params: {
							BirthSpecStac_id: wnd.createdObjects.BirthSpecStac_id
						},
						url:'/?c=PersonPregnancy&m=deleteBirthSpecStac'
					});
					break;
				case (category.name == 'Result' && !wnd.WizardPanel.deleteEvnSection && category.AddedChildDeath_ids.length > 0):
					var childDeathGrid = category.ChildDeathGridPanel.getGrid();
					var allowDeleteChildren = true;

					childDeathGrid.getStore().each(function(rec) {
						if (rec.get('ChildDeath_id').inlist(category.AddedChildDeath_ids) && !Ext.isEmpty(rec.get('PntDeathSvid_id'))) {
							allowDeleteChildren = false;
							return false;
						}
					});

					if (!allowDeleteChildren) {
						sw.swMsg.alert(lang['soobschenie'], 'Для отмены исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
						return false;
					}
					onCancel();
					break;
				case (category.name == 'Result' && !wnd.WizardPanel.deleteEvnSection && category.AddedPersonNewBorn_ids.length > 0):
					var childGrid = category.ChildGridPanel.getGrid();
					var allowDeleteChildren = true;

					childGrid.getStore().each(function(rec) {
						if (rec.get('PersonNewBorn_id').inlist(category.AddedPersonNewBorn_ids) &&
							(!Ext.isEmpty(rec.get('ChildEvnPS_id')) || !Ext.isEmpty(rec.get('BirthSvid_id')) || !Ext.isEmpty(rec.get('PntDeathSvid_id')))
						) {
							allowDeleteChildren = false;
							return false;
						}
					});

					if (!allowDeleteChildren) {
						sw.swMsg.alert(lang['soobschenie'], 'Для отмены исхода беременности у детей не должно быть случаев лечения, данных наблюдений и мед. свидетельств.');
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
						success: function(response) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.success) {
								loadMask.hide();
								category.AddedPersonNewBorn_ids = [];
								onCancel();
							}
						},
						failure: function(response) {
							loadMask.hide();
						}
					});
					break;
				default: onCancel();
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
					allowSaveButton: false
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
				base_form.findField('NewBornWardType_id').setDisabled(!enable);
				break;
		}
	},
	evnSectionIsFirst:false,
	EvnUslugaGridIsModified: false,
	formStatus:'edit',
	height:550,
	id:'EvnSectionEditWindow',
	setTariffComboEnabled:function (record, base_form) {
		if (record && Number(record.get('LpuUnitType_Code')) == 4) { // Стационар на дому
			base_form.findField('TariffClass_id').setAllowBlank(false);

			if (this.action != 'view') {
				base_form.findField('TariffClass_id').enable();
			}
		}
		else {
			base_form.findField('TariffClass_id').setAllowBlank(true);
			base_form.findField('TariffClass_id').clearValue();
			base_form.findField('TariffClass_id').disable();
		}
	},
	onchange_LpuSectionCombo:function (combo, newValue, oldValue) {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var index = this.findById(this.id + '_LpuSectionCombo').getStore().findBy(function(rec) {
			return (rec.get('LpuSection_id') == newValue);
		});
		var record = this.findById(this.id + '_LpuSectionCombo').getStore().getAt(index);

		// А если редактируем уже? 
		if (this.action != 'add') {
			base_form.findField('TariffClass_id').clearValue();
		}
		base_form.findField('TariffClass_id').setAllowBlank(true);
		// Добавить еще загрузку справочника МЭС
		//...
		this.setTariffComboEnabled(record, base_form);
		this.loadMesCombo();
		this.loadKSGKPGKOEF();
		this.recountKoikoDni();
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
	openPersonHeightEditWindow:function (action) {
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

				grid.getStore().loadData([ data.personHeightData ], true);
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
	openPersonWeightEditWindow:function (action) {
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

				grid.getStore().loadData([ data.personWeightData ], true);
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
	deleteGridSelectedRecord:function (gridId, idField) {
		var grid = this.findById(gridId).getGrid();
		var record = grid.getSelectionModel().getSelected();
		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
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
			icon:Ext.MessageBox.QUESTION,
			msg:'Вы действительно хотите удалить эту запись?',
			title:'Вопрос'
		});
	},
	deletePersonHeight:function () {
		this.deleteGridSelectedRecord('ESecEF_PersonHeightGrid', 'PersonHeight_id');
	},
	deletePersonWeight:function () {
		this.deleteGridSelectedRecord('ESecEF_PersonWeightGrid', 'PersonWeight_id');
	},
	checkChildWeight:function(){
		if(this.NewBorn_Weight == 0)
			return false;
		else
			return true;
	},
	checkBeamForm: function() { // проверка на заполненность формы лучевого лечения
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var form_fields = new Array(
			'EvnUslugaOnkoBeam_setDT',
			'EvnUslugaOnkoBeam_disDT',
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
			if (a.length != 0 && a!=0 && a!=null) {
				res = true;
			}
		}
		return res;
	},
	checkUslugaGrid: function(gridName) {
		if (this.findById(this.id+gridName).getCount() > 0) {
			var res = false;
			this.findById(this.id+gridName).getGrid().getStore().each(function(record) {
				if (record.data.EvnUsluga_pid == this.formParams.EvnSection_id) {
					res = true;
				} 
			}.createDelegate(this));
			return res;
		} else {
			return false;
		}		
	},
	enableBeamFormEdit: function(enable) { // 
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var form_fields = new Array(
			'EvnUslugaOnkoBeam_setDT',
			'EvnUslugaOnkoBeam_disDT',
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
	enableAnatomFormEdit: function(enable) {
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
		
		this.findById(this.id+'_EvnUslugaGrid').setActionDisabled('action_add', false);
		this.findById(this.id+'_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', false);
		this.findById(this.id+'_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', false);
		this.enableBeamFormEdit(true);
		
		if (this.checkUslugaGrid('_EvnUslugaGrid')) 
		{
			this.findById(this.id+'_EvnUslugaGrid').setActionDisabled('action_add', false);
			this.findById(this.id+'_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', true);
			this.findById(this.id+'_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', true);
			this.enableBeamFormEdit(false);
		} 
		else if (this.checkBeamForm())
		{
			this.findById(this.id+'_EvnUslugaGrid').setActionDisabled('action_add', true);
			this.findById(this.id+'_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', true);
			this.findById(this.id+'_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', true);
			this.enableBeamFormEdit(true);
		}		
		else if (this.checkUslugaGrid('_EvnUslugaOnkoChemGrid'))
		{
			this.findById(this.id+'_EvnUslugaGrid').setActionDisabled('action_add', true);
			this.findById(this.id+'_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', true);
			this.findById(this.id+'_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', false);
			this.enableBeamFormEdit(false);
		}
		else if (this.checkUslugaGrid('_EvnUslugaOnkoGormunGrid'))
		{
			this.findById(this.id+'_EvnUslugaGrid').setActionDisabled('action_add', true);
			this.findById(this.id+'_EvnUslugaOnkoGormunGrid').setActionDisabled('action_add', false);
			this.findById(this.id+'_EvnUslugaOnkoChemGrid').setActionDisabled('action_add', true);
			this.enableBeamFormEdit(false);
		}
		
	},
	openEvnUslugaEditWindow:function (action, grid_id, sys_nick, confirmed) {
		if ( this.findById('ESecEF_EvnUslugaPanel').hidden ) {
			return false;
		}

		if ( action != 'add' && action != 'addOper' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid = this.findById('ESecEF_EvnUslugaGrid');

		if ( this.action == 'view' ) {
			if ( action == 'add' || action == 'addOper' ) {
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
						pid: base_form.findField('EvnSection_id').getValue()
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

			this.EvnUslugaGridIsModified = true;
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
		var lpu_section_name = '';
		var med_personal_fio = '';
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

		if ( (action == 'add' || action == 'addOper') && (!evn_section_set_date || !lpu_section_id || !med_staff_fact_id) ) {
			sw.swMsg.alert('Ошибка', 'Не заполнены обязательные поля по движению');
			return false;
		}

		record = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id);
		if ( record ) {
			lpu_section_name = record.get('LpuSection_Name');
		}

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
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
			MedPersonal_id: med_personal_id
		});

		switch ( action ) {
			case 'add':
			case 'addOper':

				params.action = 'add';
				if ( base_form.findField('EvnSection_id').getValue() == 0 ) {
					this.doSave({
						ignoreEvnUslugaCountCheck: true,
						openChildWindow: function() {
							this.openEvnUslugaEditWindow(action);
						}.createDelegate(this),
						print: false
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

				if ( action == 'addOper' ){
					getWnd('swEvnUslugaOperEditWindow').show(params);
				} else {
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

				var evn_usluga_id = selected_record.get('EvnUsluga_id');

				switch ( selected_record.get('EvnClass_SysNick') ) {
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
				/*
				 if ( evn_usluga_edit_window.isVisible() ) {
				 sw.swMsg.alert('Сообщение', 'Окно редактирования услуги уже открыто', function() {
				 grid.getSelectionModel().selectFirstRow();
				 grid.getView().focusRow(0);
				 });
				 return false;
				 }
				 */

				break;
		}
	},
	openWindow: function(gridName, action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('sw'+gridName+'Window').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования уже открыто');
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
	filterLpuSectionBedProfilesByLpuSection: function (){/*только для samara и kareliya, astra*/
		//фильтрую профиль коек по отделению - оставляю среди них только профили коек подотделений
		var that = this;
		sw.Promed.LpuSectionBedProfile.getLpuSectionBedProfilesByLpuSection({
			LpuSection_id: this.findById('EvnSectionEditForm').getForm().findField('LpuSection_id').getValue(),
			callback: function(response_obj) {
				//парсю ответ сую все профили в одномерный массив
				var LpuSectionBedProfiles = [];
				response_obj.forEach(function (el){LpuSectionBedProfiles.push(parseInt(el.LpuSectionBedProfile_id))});
				//накладываю фильтр на профили коек
				var LpuSectionBedProfileCombo = that.findById('EvnSectionEditForm').getForm().findField('LpuSectionBedProfile_id');
				LpuSectionBedProfileCombo.lastQuery = '';
				LpuSectionBedProfileCombo.getStore().filterBy(function (el) {
					return 0<=LpuSectionBedProfiles.indexOf(el.data.LpuSectionBedProfile_id);
				});
				//если значение которые было установлено отфильтровалось, очищаю комбик
				if (undefined == LpuSectionBedProfileCombo.getStore().getById(LpuSectionBedProfileCombo.getValue())) {
					LpuSectionBedProfileCombo.clearValue();
				}
			}
		});
	},
	openPersonBirthTraumaEditWindow:function (action,type) {
		if (!type || !action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swPersonBirthTraumaEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования уже открыто');
			return false;
		}
		var grid = this.findById('ESEW_PersonBirthTraumaGrid'+type).getGrid();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('PersonBirthTrauma_id'))) {
				return false;
			}
		}

		var params = new Object();
		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' ) {
				return false;
			}
			data.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) { return rec.get('PersonBirthTrauma_id') == data.PersonBirthTrauma_id; });
			var record = grid.getStore().getAt(index);

			if ( typeof record == 'object' ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data[grid_fields[i]]);
				}

				record.commit();
			} else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('PersonBirthTrauma_id') ) {
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
			if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonBirthTrauma_id') ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();
			//params.PersonBirthTrauma_id=selected_record.get('PersonBirthTrauma_id');
			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}
		params.formParams.BirthTraumaType_id = type;
		params.formParams.Person_BirthDay = this.BirthDay;
		getWnd('swPersonBirthTraumaEditWindow').show(params);
	},
	deletePersonBirthTrauma:function(type){
		var grid = this.findById('ESEW_PersonBirthTraumaGrid'+type).getGrid();
		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected()

		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
				break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
				break;
		}

		if ( grid.getStore().getCount() == 0 ) {
			//LoadEmptyRow(grid);
		} else {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	deleteApgarRate:function(){
		var grid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();
		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected()

		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
				break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
				break;
		}

		if ( grid.getStore().getCount() == 0 ) {
			//LoadEmptyRow(grid);
		} else {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}

	},
	addNewbornApgarRate:function(){
		var win = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var grid = this.findById('ESEW_NewbornApgarRateGrid').getGrid();
		var data = {
			NewbornApgarRate_id:-swGenTempId(grid.getStore()),
			NewbornApgarRate_Time:0,
			RecordStatus_Code:0
		};
		grid.getStore().loadData([ data ], true);

	},
	openEvnObservNewBornEditWindow: function(action) {
		if (!action || !action.inlist(['add','edit','view'])) {
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
			this.doSave({isPersonNewBorn: 1, silent: function(){wnd.openEvnObservNewBornEditWindow(action)}});
			return false;
		}

		var params = {
			action: action,
			disableChangeTime: false,
			callback: function() {
				grid.getStore().load({
					params: {PersonNewBorn_id: PersonNewBorn_id}
				});
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
	deleteEvnObservNewBorn: function() {
		var grid_panel = this.findById('ESEW_EvnObservNewBornGrid');
		var grid = grid_panel.getGrid();
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('EvnObserv_id')) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var params = {
						EvnObserv_id: record.get('EvnObserv_id'),
						PersonNewBorn_id: base_form.findField('PersonNewBorn_id').getValue()
					};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
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
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},
	initComponent:function () {
        this.addMaxDateDays = 0;
        if (getGlobalOptions().region){
            if (getGlobalOptions().region.nick == 'ufa'){
                this.addMaxDateDays = 7;
            }else if (getGlobalOptions().region.nick == 'astra'){
                this.addMaxDateDays = 3;
            }
        }

		this.formFirstShow = true;
		var that = this;
		if (this.id == 'EvnSectionEditWindow') {
			this.tabIndex = TABINDEX_ESECEF;
		} else {
			this.tabIndex = TABINDEX_ESECEF2;
		}

		var parentWin = this;
		this.tabPanel = new Ext.TabPanel({
			region: 'south',
			id: 'ESEW-tabs-panel',
			//autoScroll: true,

			border:false,
			activeTab: 0,
			//resizeTabs: true,
			//enableTabScroll: true,
			//autoWidth: true,
			//tabWidth: 'auto',
			layoutOnTabChange: true,
			listeners: {
				'tabchange': function(tab, panel) {
					var base_form = parentWin.FormPanel.getForm();
					var Person_id = base_form.findField('Person_id').getValue();
					var PersonNewBorn_id = base_form.findField('PersonNewBorn_id').getValue();

					if(!parentWin.isTraumaTabGridLoaded && panel.id == 'tab_ESEWTrauma'){
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

							grid1.getStore().load({params:{PersonNewBorn_id:PersonNewBorn_id}});
							grid2.getStore().load({params:{PersonNewBorn_id:PersonNewBorn_id}});
							grid3.getStore().load({params:{PersonNewBorn_id:PersonNewBorn_id}});
							grid4.getStore().load({params:{PersonNewBorn_id:PersonNewBorn_id}});
						}
					}
					if(!parentWin.isObservTabGridLoaded && panel.id == 'tab_ESEWObserv'){
						parentWin.isObservTabGridLoaded = true;

						var grid_observ = parentWin.findById('ESEW_EvnObservNewBornGrid').getGrid();

						grid_observ.getStore().load({
							params: {
								Person_id: Person_id,
								PersonNewBorn_id: (PersonNewBorn_id>0)?PersonNewBorn_id:null
							}
						});
					}
				}
			},
			items:[
				{
					title: 'Общая информация',
					id: 'tab_ESEWCommon',
					iconCls: 'info16',
					border:false,
					items: [{
						layout:'form',
						bodyStyle: 'padding: 5px 5px 0',
						labelAlign: 'right',
						border:false,
						labelWidth: 190,
						items:[
							{
								comboSubject:'ChildTermType',
								fieldLabel:'Доношенность',
								hiddenName:'ChildTermType_id',
								width:300,
								xtype:'swcommonsprcombo'

							},{
								fieldLabel:'Предлежание',
								comboSubject:'ChildPositionType',
								hiddenName:'ChildPositionType_id',
								name:'ChildPositionType_id',
								width:100,
								xtype:'swcommonsprcombo'
							},
							{
								comboSubject:'FeedingType',
								fieldLabel:'Вид вскармливания',
								hiddenName:'FeedingType_id',
								width:300,
								xtype:'swcommonsprcombo',
								listeners:{
									keydown:function () {
										this.keyPressedOnThisControll = true;
									},
									keypress:function (inp, e) {
										if (!this.keyPressedOnThisControll) {
											return;
										}

										this.keyPressedOnThisControll = false;
									}
								}
							},{
								fieldLabel:'Который по счету',
								allowNegative:false,
								allowDecimals:false,
								hiddenName:'PersonNewBorn_CountChild',
								name:'PersonNewBorn_CountChild',
								width:100,
								xtype:'numberfield'
							},{
								comboSubject:'YesNo',
								fieldLabel:'ВИЧ-инфекция у матери',
								hiddenName:'PersonNewBorn_IsAidsMother',
								width:100,
								xtype:'swcommonsprcombo'
							},{
								comboSubject:'YesNo',
								fieldLabel:'Отказ от ребенка',
								hiddenName:'PersonNewBorn_IsRejection',
								width:100,
								xtype:'swcommonsprcombo',
								listeners:{
									keydown:function (inp, e) {
										if (e.getKey() == Ext.EventObject.TAB) {
											if (!e.shiftKey) {
												e.stopEvent();
												parentWin.buttons[0].focus();
											}
										}
									}
								}
							},{
								fieldLabel:'Масса(вес) при рождении, г',
								name:'PersonNewBorn_Weight',
								allowNegative:false,
								allowDecimals:false,
								maxLength:4,
								width:100,
								xtype:'numberfield',
								listeners:
								{
									'change': function(field, value)
									{
										if(Ext.isEmpty(value))
											parentWin.NewBorn_Weight = 0;
										else
											parentWin.NewBorn_Weight = value;
									}
								}
							},{
								fieldLabel:'Рост(длина) при рождении, см',
								name:'PersonNewBorn_Height',
								allowNegative:false,
								allowDecimals:false,

								maxLength:2,
								width:100,
								xtype:'numberfield'
							},{
								fieldLabel:'Окружность головы, см',
								name:'PersonNewBorn_Head',
								allowNegative:false,
								allowDecimals:false,
								maxLength:2,
								width:100,
								xtype:'numberfield'
							},{
								fieldLabel:'Окружность груди, см',
								name:'PersonNewBorn_Breast',
								maxLength:2,
								allowNegative:false,
								allowDecimals:false,
								width:100,
								xtype:'numberfield'
							},{
								comboSubject:'YesNo',
								fieldLabel:'Наличие кровотечения',
								hiddenName:'PersonNewBorn_IsBleeding',
								width:100,
								xtype:'swcommonsprcombo'
							},new sw.Promed.ViewFrame({
								//border:false,
								actions:[
									{
										name:'action_add',
										handler:function () {
											this.addNewbornApgarRate();
										}.createDelegate(this)
									},

									{
										name:'action_edit',
										hidden:true
									},

									{
										name:'action_view',
										hidden:true
									},

									{
										name:'action_delete',
										handler:function(){parentWin.deleteApgarRate()}
									},

									{
										name:'action_refresh',
										hidden:true
									},

									{
										name:'action_print',
										hidden:true
									},
									{name:'action_save', hidden: true}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								saveAtOnce: false,
								dataUrl:'/?c=PersonNewBorn&m=loadNewbornApgarRateGrid',
								height:140,
								id:'ESEW_NewbornApgarRateGrid',
								onLoadData:function () {
									//
								},
								onRowSelect:function (sm, index, record) {
									//
								},
								onAfterEdit: function(o) {
									o.grid.stopEditing(true);
									var rec = o.record;
									var isEmp = (Ext.isEmpty(rec.get('NewbornApgarRate_Heartbeat'))&&Ext.isEmpty(rec.get('NewbornApgarRate_Breath'))&&Ext.isEmpty(rec.get('NewbornApgarRate_SkinColor'))&&Ext.isEmpty(rec.get('NewbornApgarRate_ToneMuscle'))&&Ext.isEmpty(rec.get('NewbornApgarRate_Reflex')))
									var sum = Number(rec.get('NewbornApgarRate_Heartbeat'))+Number(rec.get('NewbornApgarRate_Breath'))+Number(rec.get('NewbornApgarRate_SkinColor'))+Number(rec.get('NewbornApgarRate_ToneMuscle'))+Number(rec.get('NewbornApgarRate_Reflex'))
									if(!isEmp)rec.set('NewbornApgarRate_Values',sum);
									if(rec.get('RecordStatus_Code')==1){
										rec.set('RecordStatus_Code',2);
									}
									o.record.commit();
									log(o);
								},
								paging:false,
								region:'center',
								stringfields:[
									{
										name:'NewbornApgarRate_id',
										type:'int',
										header:'ID',
										key:true
									},

									{
										name:'PersonNewBorn_id',
										type:'int',
										hidden:true
									},
									{
										name:'RecordStatus_Code',
										type:'int',
										hidden:true
									},
									{
										name:'NewbornApgarRate_Time',
										type:'string',
										editor: new Ext.form.NumberField({allowDecimals: false,maxValue:60}),
										header:'Время после рождения, мин'
									},

									{
										name:'NewbornApgarRate_Heartbeat',
										type:'string',
										editor: new Ext.form.NumberField({maxLength:1,maxValue:2,allowDecimals: false}),
										header:'Сердцебиение'
									},

									{
										name:'NewbornApgarRate_Breath',
										type:'string',
										editor: new Ext.form.NumberField({maxLength:1,maxValue:2,allowDecimals: false}),
										header:'Дыхание'
									},

									{
										name:'NewbornApgarRate_SkinColor',
										type:'int',
										editor: new Ext.form.NumberField({maxLength:1,maxValue:2,allowDecimals: false}),
										header:'Окраска кожи'
									},

									{
										name:'NewbornApgarRate_ToneMuscle',
										type:'int',
										editor: new Ext.form.NumberField({maxLength:1,maxValue:2,allowDecimals: false}),
										header:'Тонус мышц'
									},

									{
										name:'NewbornApgarRate_Reflex',
										type:'int',
										editor: new Ext.form.NumberField({maxLength:1,maxValue:2,allowDecimals: false}),
										header:'Рефлексы'
									},

									{
										name:'NewbornApgarRate_Values',
										type:'int',
										editor: new Ext.form.NumberField({maxValue:10,allowDecimals: false}),
										header:'Масса',
										header:'Оценка в баллах'
									}
								],
								title:'Оценка состояния по шкале Апгар'
							}),
							{
								fieldLabel:'Переведен в',
								comboSubject:'NewBornWardType',
								hiddenName:'NewBornWardType_id',
								name:'NewBornWardType_id',
								width:300,
								xtype:'swcommonsprcombo'
							},{
								comboSubject:'YesNo',
								fieldLabel:'Неонатальный скрининг',
								hiddenName:'PersonNewBorn_IsNeonatal',
								width:100,
								xtype:'swcommonsprcombo'
							},{
								comboSubject:'YesNo',
								fieldLabel:'Аудиологический скрининг',
								hiddenName:'PersonNewBorn_IsAudio',
								width:100,
								xtype:'swcommonsprcombo'
							},
							{
								autoHeight:true,
								labelWidth:150,
								layout:'form',
								style:'padding: 0px;',
								title:'Вакцинация',
								xtype:'fieldset',

								items:[
									{
										layout:'form',
										border:false,
										items:[
											{
												layout:'column',
												border:false,
												items:[
													{
														layout:'form',
														border:false,
														labelWidth: 80,
														width:210,
														items:[
															{
																comboSubject:'YesNo',
																fieldLabel:'БЦЖ',
																hiddenName:'PersonNewBorn_IsBCG',
																width:100,
																xtype:'swcommonsprcombo'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														width:180,
														items:[
															{
																fieldLabel:'Дата',
																format:'d.m.Y',
																name:'PersonNewBorn_BCGDate',
																plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
																selectOnFocus:true,
																width:100,
																xtype:'swdatefield'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:'Серия',
																name:'PersonNewBorn_BCGSer',
																width:100,
																xtype:'textfield'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:'Номер',
																name:'PersonNewBorn_BCGNum',
																width:100,
																xtype:'textfield'
															}
														]
													}
												]
											},{
												layout:'column',
												border:false,
												items:[
													{
														layout:'form',
														border:false,
														labelWidth: 80,
														width:210,
														items:[
															{
																comboSubject:'YesNo',
																fieldLabel:'Гепатит B',
																hiddenName:'PersonNewBorn_IsHepatit',
																width:100,
																xtype:'swcommonsprcombo'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														width:180,
														items:[
															{
																fieldLabel:'Дата',
																format:'d.m.Y',
																name:'PersonNewBorn_HepatitDate',
																plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
																selectOnFocus:true,
																width:100,
																xtype:'swdatefield'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:'Серия',
																name:'PersonNewBorn_HepatitSer',
																width:100,
																xtype:'textfield'
															}
														]
													},{
														layout:'form',
														border:false,
														labelWidth: 50,
														items:[
															{
																fieldLabel:'Номер',
																name:'PersonNewBorn_HepatitNum',
																width:100,
																xtype:'textfield'
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
				},{
					title: 'Родовые травмы, пороки развития',
					id: 'tab_ESEWTrauma',
					iconCls: 'info16',
					border:false,
					items: [{
						layout:'form',
						border:false,
						bodyStyle: 'padding: 5px 5px 0',
						labelAlign: 'right',
						labelWidth: 150,
						items:[
							new sw.Promed.ViewFrame({
								//border:false,
								actions:[
									{
										name:'action_add',
										handler:function () {
											this.openPersonBirthTraumaEditWindow('add',1);
										}.createDelegate(this)
									},

									{
										name:'action_edit',
										hidden:true
									},

									{
										name:'action_view',
										handler:function () {
											this.openPersonBirthTraumaEditWindow('view',1);
										}.createDelegate(this)
									},

									{
										name:'action_delete',
										handler:function(){parentWin.deletePersonBirthTrauma(1)}
									},

									{
										name:'action_refresh',
										disabled:true
									},

									{
										name:'action_print',
										disabled:true
									}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								dataUrl:'/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height:130,
								id:'ESEW_PersonBirthTraumaGrid1',
								onDblClick:function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onEnter:function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onLoadData:function () {
									//
								},
								onRowSelect:function (sm, index, record) {
									//
								},
								paging:false,
								region:'center',
								stringfields:[
									{
										name:'PersonBirthTrauma_id',
										type:'int',
										header:'ID',
										key:true
									},

									{
										name:'PersonNewBorn_id',
										type:'int',
										hidden:true
									},

									{
										name:'Diag_Code',
										type:'string',
										//hidden:true
										header:'Код'
									},
									{
										name:'PersonBirthTrauma_setDate',
										type:'date',
										hidden:true
									},
									{
										name:'BirthTraumaType_id',
										type:'int',
										hidden:true
									},
									{
										name:'RecordStatus_Code',
										type:'int',
										hidden:true
									},
									{
										name:'Diag_id',
										type:'int',
										hidden:true
									},
									{
										name:'Diag_Name',
										type:'string',
										//hidden:true
										header:'Наименование'
									},

									{
										name:'PersonBirthTrauma_Comment',
										type:'string',
										//hidden:true
										header:'Расшифровка'
									}
								],
								title:'Родовые травмы'
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions:[
									{
										name:'action_add',
										handler:function () {
											this.openPersonBirthTraumaEditWindow('add',2);
										}.createDelegate(this)
									},

									{
										name:'action_edit',
										hidden:true
									},

									{
										name:'action_view',
										handler:function () {
											this.openPersonBirthTraumaEditWindow('view',2);
										}.createDelegate(this)
									},

									{
										name:'action_delete',
										handler:function(){parentWin.deletePersonBirthTrauma(2)}
									},

									{
										name:'action_refresh',
										disabled:true
									},

									{
										name:'action_print',
										disabled:true
									}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								dataUrl:'/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height:130,
								id:'ESEW_PersonBirthTraumaGrid2',
								onDblClick:function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onEnter:function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onLoadData:function () {
									//
								},
								onRowSelect:function (sm, index, record) {
									//
								},
								paging:false,
								region:'center',
								stringfields:[
									{
										name:'PersonBirthTrauma_id',
										type:'int',
										header:'ID',
										key:true
									},

									{
										name:'PersonNewBorn_id',
										type:'int',
										hidden:true
									},
									{
										name:'PersonBirthTrauma_setDate',
										type:'date',
										hidden:true
									},
									{
										name:'BirthTraumaType_id',
										type:'int',
										hidden:true
									},
									{
										name:'RecordStatus_Code',
										type:'int',
										hidden:true
									},
									{
										name:'Diag_id',
										type:'int',
										hidden:true
									},
									{
										name:'Diag_Code',
										type:'string',
										//hidden:true
										header:'Код'
									},

									{
										name:'Diag_Name',
										type:'string',
										//hidden:true
										header:'Наименование'
									},

									{
										name:'PersonBirthTrauma_Comment',
										type:'string',
										//hidden:true
										header:'Расшифровка'
									}
								],
								title:'Поражения плода'
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions:[
									{
										name:'action_add',
										handler:function () {
											this.openPersonBirthTraumaEditWindow('add',3);
										}.createDelegate(this)
									},

									{
										name:'action_edit',
										hidden:true
									},

									{
										name:'action_view',
										handler:function () {
											this.openPersonBirthTraumaEditWindow('view',3);
										}.createDelegate(this)
									},

									{
										name:'action_delete',
										handler:function(){parentWin.deletePersonBirthTrauma(3)}
									},

									{
										name:'action_refresh',
										disabled:true
									},

									{
										name:'action_print',
										disabled:true
									}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								dataUrl:'/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height:130,
								id:'ESEW_PersonBirthTraumaGrid3',
								onDblClick:function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onEnter:function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onLoadData:function () {
									//
								},
								onRowSelect:function (sm, index, record) {
									//
								},
								paging:false,
								region:'center',
								stringfields:[
									{
										name:'PersonBirthTrauma_id',
										type:'int',
										header:'ID',
										key:true
									},

									{
										name:'PersonNewBorn_id',
										type:'int',
										hidden:true
									},

									{
										name:'Diag_Code',
										type:'string',
										//hidden:true
										header:'Код'
									},
									{
										name:'PersonBirthTrauma_setDate',
										type:'date',
										hidden:true
									},
									{
										name:'BirthTraumaType_id',
										type:'int',
										hidden:true
									},
									{
										name:'RecordStatus_Code',
										type:'int',
										hidden:true
									},
									{
										name:'Diag_id',
										type:'int',
										hidden:true
									},
									{
										name:'Diag_Name',
										type:'string',
										//hidden:true
										header:'Наименование'
									},

									{
										name:'PersonBirthTrauma_Comment',
										type:'string',
										//hidden:true
										header:'Расшифровка'
									}
								],
								title:'Врожденные пороки развития'
							}),
							new sw.Promed.ViewFrame({
								//border:false,
								actions:[
									{
										name:'action_add',
										handler:function () {
											this.openPersonBirthTraumaEditWindow('add',4);
										}.createDelegate(this)
									},

									{
										name:'action_edit',
										hidden:true
									},

									{
										name:'action_view',
										handler:function () {
											this.openPersonBirthTraumaEditWindow('view',4);
										}.createDelegate(this)
									},

									{
										name:'action_delete',
										handler:function(){parentWin.deletePersonBirthTrauma(4)}
									},

									{
										name:'action_refresh',
										disabled:true
									},

									{
										name:'action_print',
										disabled:true
									}
								],
								autoLoadData:false,
								focusOnFirstLoad:false,
								dataUrl:'/?c=PersonNewBorn&m=loadPersonBirthTraumaGrid',
								height:130,
								id:'ESEW_PersonBirthTraumaGrid4',
								onDblClick:function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onEnter:function () {
									if (!this.ViewActions.action_edit.isDisabled()) {
										this.ViewActions.action_edit.execute();
									}
								},
								onLoadData:function () {
									//
								},
								onRowSelect:function (sm, index, record) {
									//
								},
								paging:false,
								region:'center',
								stringfields:[
									{
										name:'PersonBirthTrauma_id',
										type:'int',
										header:'ID',
										key:true
									},

									{
										name:'PersonNewBorn_id',
										type:'int',
										hidden:true
									},
									{
										name:'PersonBirthTrauma_setDate',
										type:'date',
										hidden:true
									},
									{
										name:'BirthTraumaType_id',
										type:'int',
										hidden:true
									},
									{
										name:'RecordStatus_Code',
										type:'int',
										hidden:true
									},
									{
										name:'Diag_id',
										type:'int',
										hidden:true
									},
									{
										name:'Diag_Code',
										type:'string',
										//hidden:true
										header:'Код'
									},

									{
										name:'Diag_Name',
										type:'string',
										//hidden:true
										header:'Наименование'
									},

									{
										name:'PersonBirthTrauma_Comment',
										type:'string',
										//hidden:true
										header:'Расшифровка'
									}
								],
								title:'Подозрения на врожденные пороки'
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
								{name: 'action_add', handler: function(){parentWin.openEvnObservNewBornEditWindow('add')}},
								{name: 'action_edit', handler: function(){parentWin.openEvnObservNewBornEditWindow('edit')}},
								{name: 'action_view', handler: function(){parentWin.openEvnObservNewBornEditWindow('view')}},
								{name: 'action_delete', handler: function(){parentWin.deleteEvnObservNewBorn()}},
								{name: 'action_refresh', hidden: true}
							],
							stringfields:[
								{name: 'EvnObserv_id', type: 'int', header: 'ID', key: true},
								{name: 'PersonNewBorn_id', type: 'int', hidden: true},
								{name: 'EvnObserv_pid', type: 'int', hidden: true},
								{name: 'EvnObserv_setDate', header: 'Дата', type: 'date', width: 80},
								{name: 'ObservTimeType_Name', header: 'Время', type: 'string', width: 120},
								{name: 'art_davlenie', header: lang['art_davlenie'], type: 'string', width: 80},
								{name: 'temperatura', header: lang['temperatura'], type: 'string', width: 80},
								{name: 'puls', header: lang['puls'], type: 'string', width: 80},
								{name: 'chastota_dyihaniya', header: lang['chastota_dyihaniya'], type: 'int', width: 80},
								{name: 'ves', header: lang['ves'], type: 'float', width: 80},
								{name: 'vyipito_jidkosti', header: lang['vyipito_jidkosti'], type: 'float', width: 80},
								{name: 'kol-vo_mochi', header: lang['kol-vo_mochi'], type: 'float', width: 80},
								{name: 'reaktsiya_na_osmotr', header: lang['reaktsiya_na_osmotr'], type: 'string', width: 80},
								{name: 'reaktsiya_zrachka', header: lang['reaktsiya_zrachka'], type: 'string', width: 80},
								{name: 'stul', header: lang['stul'], type: 'string', width: 80}
							]
						})
					]
				}
			]
		});
		this.sicknessDiagStore = new Ext.db.AdapterStore({
			autoLoad:true,
			dbFile:'Promed.db',
			fields:[
				{name:'SicknessDiag_id', type:'int'},
				{name:'Sickness_id', type:'int'},
				{name:'Sickness_Code', type:'int'},
				{name:'PrivilegeType_id', type:'int'},
				{name:'Sickness_Name', type:'string'},
				{name:'Diag_id', type:'int'},
				{name:'SicknessDiag_begDT', type: 'date', dateFormat: 'd.m.Y'},
				{name:'SicknessDiag_endDT', type: 'date', dateFormat: 'd.m.Y'}
			],
			key:'Diag_id',
			sortInfo:{
				field:'Diag_id'
			},
			tableName:'SicknessDiag'
		});
		this.morbusDiagStore = new Ext.db.AdapterStore({
			autoLoad:true,
			dbFile:'Promed.db',
			fields:[
				{name:'MorbusDiag_id', type:'int'},
				{name:'MorbusType_id', type:'int'},
				{name:'MorbusType_Code', type:'int'},
				{name:'MorbusType_SysNick', type:'string'},
				{name:'MorbusType_Name', type:'string'},
				{name:'Diag_id', type:'int'}
			],
			key:'MorbusDiag_id',
			sortInfo:{
				field:'Diag_id'
			},
			tableName:'MorbusDiag'
		});
		this.keyHandlerAlt = {
			alt:true,
			fn:function (inp, e) {
				var current_window = this;

				switch (e.getKey()) {
					case Ext.EventObject.C:
						current_window.doSave();
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
			key:[
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
			stopEvent:true,
			scope:this
		}
		var parentWin = this;

		this.onkoForm = new Ext.form.FormPanel({
		    url: '/?c=EvnSection&m=saveMorbusOnko',
		    border: false,
		    labelWidth: 250,
		    name: 'ESecEF_EvnOnkoForm',
		    id: 'ESecEF_EvnOnkoForm',
		    height: 2500,
		    items: [new sw.Promed.Panel({
                layout: 'form',
		        collabsible: true,
		        title: 'Диагноз',
		        items: [{
                    id: 'Evn_pid',
                    name: 'Evn_pid',
                    xtype: 'hidden'
                }, {
		            name: 'Person_id',
		            xtype: 'hidden'
		        }, {
		            name: 'Diag_id',
		            xtype: 'hidden'
		        }, {
		            id: 'MorbusBase_id',
		            name: 'MorbusBase_id',
		            xtype: 'hidden'
		        }, {
		            id: 'Morbus_id',
		            name: 'Morbus_id',
		            xtype: 'hidden'
		        }, {
		            id: 'MorbusOnkoBase_id',
		            name: 'MorbusOnkoBase_id',
		            xtype: 'hidden'
		        }, {
		            id: 'MorbusOnko_id',
		            name: 'MorbusOnko_id',
		            xtype: 'hidden'
		        }, {
		            id: 'MorbusOnko_pid',
		            name: 'MorbusOnko_pid',
		            xtype: 'hidden'
		        }, {
		            fieldLabel: 'Дата появления первых признаков заболевания',
		            hiddenName: 'MorbusOnko_firstSignDT',
		            name: 'MorbusOnko_firstSignDT',
		            xtype: 'swdatefield'
		        }, {
		            fieldLabel: 'Первое обращение в ЛПУ по поводу данного заболевания. Дата',
		            hiddenName: 'MorbusOnko_firstVizitDT',
		            name: 'MorbusOnko_firstVizitDT',
		            xtype: 'swdatefield'
		        }, {
	//	            fieldLabel: 'ЛПУ',
		//            hiddenName: 'Lpu_foid',
		  //          width: 250,
		    //        xtype: 'oclpusearch'
		      //  }, {
		            fieldLabel: 'Дата установления диагноза',
		            hiddenName: 'MorbusOnko_setDiagDT',
		            name: 'MorbusOnko_setDiagDT',
		            xtype: 'swdatefield'
		        }, {
		            fieldLabel: 'Регистрационный номер',
		            hiddenName: 'MorbusOnkoBase_NumCard',
		            name: 'MorbusOnkoBase_NumCard',
		            xtype: 'textfield'
		        }, {
		            fieldLabel: 'Дата взятия на учет в ОД',
		            hiddenName: 'MorbusBase_setDT',
		            name: 'MorbusBase_setDT',
		            xtype: 'swdatefield'
		        }, {
		            fieldLabel: 'Взят на учет в ОД',
		            comboSubject: 'OnkoRegType',
		            hiddenName: 'OnkoRegType_id',
		            width: 250,
		            xtype: 'swcommonsprlikecombo'
		        }, {
		            fieldLabel: 'Дата снятия с учета в ОД',
		            hiddenName: 'MorbusBase_disDT',
		            name: 'MorbusBase_disDT',
		            xtype: 'swdatefield'
		        }, {
		            fieldLabel: 'Причина снятия с учета',
		            comboSubject: 'OnkoRegOutType',
		            hiddenName: 'OnkoRegOutType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Порядковый номер данной опухоли у данного больного',
		            hiddenName: 'MorbusOnko_NumTumor',
		            name: 'MorbusOnko_NumTumor',
		            readOnly: true,
		            xtype: 'textfield'
		        }, {
		            fieldLabel: 'Первично-множественная опухоль',
		            comboSubject: 'TumorPrimaryMultipleType',
		            hiddenName: 'TumorPrimaryMultipleType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Признак основной опухоли',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsMainTumor',
		            xtype: 'swcommonsprlikecombo',
		            width: 70
		        }, {
		            fieldLabel: 'Топография (локализация) опухоли',
		            hiddenName: 'Diag_id_Name',
		            name: 'Diag_id_Name',
		            width: 250,
		            readOnly: true,
		            xtype: 'textfield'
		        }, {
		            fieldLabel: 'Сторона поражения',
		            comboSubject: 'OnkoLesionSide',
		            hiddenName: 'OnkoLesionSide_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Морфологический тип опухоли. (Гистология опухоли)',
		            hiddenName: 'OnkoDiag_mid',
		            name: 'OnkoDiag_mid',
		            xtype: 'swonkodiagcombo',
		            width: 250
		        }, {
		            fieldLabel: 'Номер гистологического исследования',
		            hiddenName: 'MorbusOnko_NumHisto',
		            name: 'MorbusOnko_NumHisto',
		            xtype: 'numberfield'
		        }, { // енд
		            fieldLabel: 'Стадия опухолевого процесса по системе TNM. T',
		            comboSubject: 'OnkoT',
		            hiddenName: 'OnkoT_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'N',
		            comboSubject: 'OnkoN',
		            hiddenName: 'OnkoN_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'M',
		            comboSubject: 'OnkoM',
		            hiddenName: 'OnkoM_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Стадия опухолевого процесса',
		            comboSubject: 'TumorStage',
		            hiddenName: 'TumorStage_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, { // Локазилация отдаленных метастазов
		            fieldLabel: 'Неизвестна',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoUnknown',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Отдаленные лимфатические узлы',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoLympha',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Кости',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoBones',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Печень',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoLiver',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Легкие и/или плевра',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoLungs',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Головной мозг',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoBrain',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Кожа',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoSkin',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Почки',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoKidney',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Яичники',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoOvary',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Брюшина',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoPerito',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Костный мозг',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoMarrow',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Другие органы',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoOther',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Множественные',
		            comboSubject: 'YesNo',
		            hiddenName: 'MorbusOnko_IsTumorDepoMulti',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Метод подтверждения диагноза',
		            comboSubject: 'OnkoDiagConfType',
		            hiddenName: 'OnkoDiagConfType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Выявлен врачом',
		            comboSubject: 'OnkoPostType',
		            hiddenName: 'OnkoPostType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Обстоятельства выявления опухоли',
		            comboSubject: 'TumorCircumIdentType',
		            hiddenName: 'TumorCircumIdentType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Причины поздней диагностики',
		            comboSubject: 'OnkoLateDiagCause',
		            hiddenName: 'OnkoLateDiagCause_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Дата смерти',
		            hiddenName: 'MorbusOnkoBase_deadDT',
		            name: 'MorbusOnkoBase_deadDT',
		            xtype: 'swdatefield'
		        }, {
		            fieldLabel: 'Причины поздней диагностики',
		            comboSubject: 'OnkoTreatType',
		            hiddenName: 'OnkoTreatType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }, {
		            fieldLabel: 'Результат аутопсии применительно к данной опухоли',
		            comboSubject: 'TumorAutopsyResultType',
		            hiddenName: 'TumorAutopsyResultType_id',
		            xtype: 'swcommonsprlikecombo',
		            width: 250
		        }]
		    }), new sw.Promed.Panel({ 
                collabsible: true,
                title: 'Химиотерапевтическое лечение',
                items: {
                    xtype: 'grid',
                    id: 'ESecEF_OnkoChemGrid',
                    height: 150,
                    width: 800,
                    columns: [
                        { dataIndex: 'EvnUsluga_setDate', header: 'Дата начала', resizable: true, sortable: true, width: 100 },
                        { dataIndex: 'EvnUsluga_disDate', header: 'Дата окончания', resizable: true, sortable: true, width: 100 },
                        { dataIndex: 'Lpu_Name', header: 'ЛПУ', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'OnkoUslugaChemKindType_Name', header: 'Код Вид химиотерапии', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'FocusType_Name', header: 'Преимущественная направленность', resizable: true, sortable: true, width: 150 }
                    ],
                    store: new Ext.data.JsonStore({
                        fields: [
                           { name: 'EvnUsluga_setDate', type: 'string' },
                           { name: 'EvnUsluga_disDate', type: 'string' },
                           { name: 'Lpu_Name', type: 'string' },
                           { name: 'OnkoUslugaChemKindType_Name', type: 'string' },
                           { name: 'FocusType_Name', type: 'string' }
                        ],
                        url: '/?c=EvnSection&m=loadEvnUsluga'
                    }),
                    tbar: new sw.Promed.Toolbar({
                        buttons: [{
                            handler: function () {
                                var section = parentWin.findById('EvnSectionEditForm').getForm(),
                                    onko = parentWin.findById('ESecEF_EvnOnkoForm').getForm(),
                                    grid = parentWin.findById('ESecEF_OnkoChemGrid');

                                var temp,
                                    section_id = (temp = section.findField('EvnSection_id')) && temp.getValue(),
                                    personEvn_id = (temp = section.findField('PersonEvn_id')) && temp.getValue(),
                                    server_id = (temp = section.findField('Server_id')) && temp.getValue(),
                                    person_id = (temp = onko.findField('Person_id')) && temp.getValue(),
                                    lpu_id = (temp = onko.findField('Lpu_foid')) && temp.getValue(),
                                    morbusOnkoBase_id = (temp = onko.findField('MorbusOnkoBase_id')) && temp.getValue(),
                                    morbusOnko_id = (temp = onko.findField('MorbusOnko_id')) && temp.getValue(),
                                    morbus_id = (temp = onko.findField('Morbus_id')) && temp.getValue();

                                var params = {
                                    //EvnUslugaOnkoChem_id: null,
                                    //MorbusOnko_specDisDT: null,
                                    //MorbusOnko_specSetDT: null,
                                    action: "add",
                                    formParams: {
                                        //EvnUslugaOnkoChem_id: null,
                                        EvnUslugaOnkoChem_pid: section_id,
                                        Lpu_id: lpu_id,
                                        Lpu_uid: lpu_id,
                                        MorbusOnkoBase_id: morbusOnkoBase_id,
                                        MorbusOnko_id: morbusOnko_id,
                                        Morbus_id: morbus_id,
                                        PersonEvn_id: personEvn_id,
                                        Person_id: person_id,
                                        Server_id: server_id
                                    },
                                    callback: function () {
                                        grid.getStore().load({
                                            params: {
                                                'class': 'EvnUslugaOnkoChem',
                                                'byMorbus': 1,
                                                'Morbus_id': onko.findField('Morbus_id').getValue(),
                                                'EvnEdit_id': onko.findField('Evn_pid').getValue()
                                            }
                                        });
                                    }
                                };
                                getWnd('swEvnUslugaOnkoChemEditWindow').show(params);
                            },
                            iconCls: 'add16',
                            text: 'Добавить'
                        }]
                    })
                }
            }), new sw.Promed.Panel({
                collabsible: true,
                title: 'Лучевое лечение',
                items: {
                    xtype: 'grid',
                    id: 'ESecEF_OnkoBeamGrid',
                    height: 150,
                    width: 800,
                    columns: [
                        { dataIndex: 'EvnUsluga_setDate', header: 'Дата начала', resizable: true, sortable: true, width: 100 },
                        { dataIndex: 'EvnUsluga_disDate', header: 'Дата окончания', resizable: true, sortable: true, width: 100 },
                        { dataIndex: 'OnkoUslugaBeamIrradiationType_Name', header: 'Способ облучения', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'OnkoUslugaBeamKindType_Name', header: 'Вид радиотерапии', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'OnkoUslugaBeamMethodType_Name', header: 'Метод', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'FocusType_Name', header: 'Преимущественная направленность', resizable: true, sortable: true, width: 150 }
                    ],
                    store: new Ext.data.JsonStore({
                        fields: [
                           { name: 'EvnUsluga_setDate', type: 'string' },
                           { name: 'EvnUsluga_disDate', type: 'string' },
                           { name: 'OnkoUslugaBeamIrradiationType_Name', type: 'string' },
                           { name: 'OnkoUslugaBeamKindType_Name', type: 'string' },
                           { name: 'OnkoUslugaBeamMethodType_Name', type: 'string' },
                           { name: 'FocusType_Name', type: 'string' },
                        ],
                        url: '/?c=EvnSection&m=loadEvnUsluga'
                    }),
                    tbar: new sw.Promed.Toolbar({
                        buttons: [{
                            handler: function () {
                                var section = parentWin.findById('EvnSectionEditForm').getForm(),
                                    onko = parentWin.findById('ESecEF_EvnOnkoForm').getForm(),
                                    grid = parentWin.findById('ESecEF_OnkoBeamGrid');

                                var temp,
                                    section_id = (temp = section.findField('EvnSection_id')) && temp.getValue(),
                                    personEvn_id = (temp = section.findField('PersonEvn_id')) && temp.getValue(),
                                    server_id = (temp = section.findField('Server_id')) && temp.getValue(),
                                    person_id = (temp = onko.findField('Person_id')) && temp.getValue(),
                                    lpu_id = (temp = onko.findField('Lpu_foid')) && temp.getValue(),
                                    morbusOnkoBase_id = (temp = onko.findField('MorbusOnkoBase_id')) && temp.getValue(),
                                    morbusOnko_id = (temp = onko.findField('MorbusOnko_id')) && temp.getValue(),
                                    morbus_id = (temp = onko.findField('Morbus_id')) && temp.getValue();

                                var params = {
                                    //EvnUslugaOnkoBeam_id: null,
                                    //MorbusOnko_specDisDT: null,
                                    //MorbusOnko_specSetDT: null,
                                    action: "add",
                                    formParams: {
                                        //EvnUslugaOnkoBeam_id: null,
                                        EvnUslugaOnkoBeam_pid: section_id,
                                        Lpu_id: lpu_id,
                                        Lpu_uid: lpu_id,
                                        MorbusOnkoBase_id: morbusOnkoBase_id,
                                        MorbusOnko_id: morbusOnko_id,
                                        Morbus_id: morbus_id,
                                        PersonEvn_id: personEvn_id,
                                        Person_id: person_id,
                                        Server_id: server_id
                                    },
                                    callback: function () {
                                        grid.getStore().load({
                                            params: {
                                                'class': 'EvnUslugaOnkoBeam',
                                                'byMorbus': 1,
                                                'Morbus_id': onko.findField('Morbus_id').getValue(),
                                                'EvnEdit_id': onko.findField('Evn_pid').getValue()
                                            }
                                        });
                                    }
                                };
                                getWnd('swEvnUslugaOnkoBeamEditWindow').show(params);
                            }.createDelegate(this),
                            iconCls: 'add16',
                            text: 'Добавить'
                        }]
                    })
                }
            }), new sw.Promed.Panel({
                collabsible: true,
                title: 'Гормоноиммунотерапевтическое лечение',
                items: {
                    xtype: 'grid',
                    id: 'ESecEF_OnkoGormunGrid',
                    height: 150,
                    width: 800,
                    columns: [
                        { dataIndex: 'EvnUsluga_setDate', header: 'Дата начала', resizable: true, sortable: true, width: 100 },
                        { dataIndex: 'EvnUsluga_disDate', header: 'Дата окончания', resizable: true, sortable: true, width: 100 },
                        { dataIndex: 'Lpu_Name', header: 'ЛПУ', resizable: true, sortable: true, width: 150 },
                        //{ dataIndex: 'OnkoUslugaBeamIrradiationType_Name', header: 'Вид терапии', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'FocusType_Name', header: 'Преимущественная направленность', resizable: true, sortable: true, width: 150 },
                    ],
                    store: new Ext.data.JsonStore({
                        fields: [
                           { name: 'EvnUsluga_setDate', type: 'string' },
                           { name: 'EvnUsluga_disDate', type: 'string' },
                           { name: 'Lpu_Name', type: 'string' },
                           { name: 'FocusType_Name', type: 'string' }
                        ],
                        url: '/?c=EvnSection&m=loadEvnUsluga'
                    }),
                    tbar: new sw.Promed.Toolbar({
                        buttons: [{
                            handler: function () {
                                var section = parentWin.findById('EvnSectionEditForm').getForm(),
                                    onko = parentWin.findById('ESecEF_EvnOnkoForm').getForm(),
                                    grid = parentWin.findById('ESecEF_OnkoGormunGrid');

                                var temp,
                                    section_id = (temp = section.findField('EvnSection_id')) && temp.getValue(),
                                    personEvn_id = (temp = section.findField('PersonEvn_id')) && temp.getValue(),
                                    server_id = (temp = section.findField('Server_id')) && temp.getValue(),
                                    person_id = (temp = onko.findField('Person_id')) && temp.getValue(),
                                    lpu_id = (temp = onko.findField('Lpu_foid')) && temp.getValue(),
                                    morbusOnkoBase_id = (temp = onko.findField('MorbusOnkoBase_id')) && temp.getValue(),
                                    morbusOnko_id = (temp = onko.findField('MorbusOnko_id')) && temp.getValue(),
                                    morbus_id = (temp = onko.findField('Morbus_id')) && temp.getValue();

                                var params = {
                                    //EvnUslugaOnkoGormun_id: null,
                                    //MorbusOnko_specDisDT: null,
                                    //MorbusOnko_specSetDT: null,
                                    action: "add",
                                    formParams: {
                                        //EvnUslugaOnkoGormun_id: null,
                                        EvnUslugaOnkoGormun_pid: section_id,
                                        Lpu_id: lpu_id,
                                        Lpu_uid: lpu_id,
                                        MorbusOnkoBase_id: morbusOnkoBase_id,
                                        MorbusOnko_id: morbusOnko_id,
                                        Morbus_id: morbus_id,
                                        PersonEvn_id: personEvn_id,
                                        Person_id: person_id,
                                        Server_id: server_id
                                    },
                                    callback: function () {
                                        grid.getStore().load({
                                            params: {
                                                'class': 'EvnUslugaOnkoGormun',
                                                'byMorbus': 1,
                                                'Morbus_id': onko.findField('Morbus_id').getValue(),
                                                'EvnEdit_id': onko.findField('Evn_pid').getValue()
                                            }
                                        });
                                    }
                                };
                                getWnd('swEvnUslugaOnkoGormunEditWindow').show(params);
                            }.createDelegate(this),
                            iconCls: 'add16',
                            text: 'Добавить'
                        }]
                    })
                }
            }),  new sw.Promed.Panel({
                collabsible: true,
                title: 'Хирургическое лечение',
                items: {
                    xtype: 'grid',
                    id: 'ESecEF_OnkoSurgGrid',
                    height: 150,
                    width: 800,
                    columns: [
                        { dataIndex: 'EvnUsluga_setDate', header: 'Дата', resizable: false, sortable: true, width: 150 },
                        { dataIndex: 'Lpu_Name', header: 'ЛПУ', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'Usluga_Code', header: 'Код услуги', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'Usluga_Name', header: 'Услуга', resizable: true, sortable: true, width: 150 },
                    ],
                    store: new Ext.data.JsonStore({
                        fields: [
                           { name: 'EvnUsluga_setDate', type: 'string' },
                           { name: 'Lpu_Name', type: 'string' },
                           { name: 'Usluga_Code', type: 'string' },
                           { name: 'Usluga_Name', type: 'string' },
                        ],
                        url: '/?c=EvnSection&m=loadEvnUsluga'
                    }),
                    tbar: new sw.Promed.Toolbar({
                        buttons: [{
                            handler: function () {
                                var section = parentWin.findById('EvnSectionEditForm').getForm(),
                                    onko = parentWin.findById('ESecEF_EvnOnkoForm').getForm(),
                                    grid = parentWin.findById('ESecEF_OnkoSurgGrid');

                                var temp,
                                    section_id = (temp = section.findField('EvnSection_id')) && temp.getValue(),
                                    personEvn_id = (temp = section.findField('PersonEvn_id')) && temp.getValue(),
                                    server_id = (temp = section.findField('Server_id')) && temp.getValue(),
                                    person_id = (temp = onko.findField('Person_id')) && temp.getValue(),
                                    lpu_id = (temp = onko.findField('Lpu_foid')) && temp.getValue(),
                                    morbusOnkoBase_id = (temp = onko.findField('MorbusOnkoBase_id')) && temp.getValue(),
                                    morbusOnko_id = (temp = onko.findField('MorbusOnko_id')) && temp.getValue(),
                                    morbus_id = (temp = onko.findField('Morbus_id')) && temp.getValue();

                                var params = {
                                    //EvnUslugaOnkoSurg_id: null,
                                    //MorbusOnko_specDisDT: null,
                                    //MorbusOnko_specSetDT: null,
                                    action: "add",
                                    formParams: {
                                        //EvnUslugaOnkoSurg_id: null,
                                        EvnUslugaOnkoSurg_pid: section_id,
                                        Lpu_id: lpu_id,
                                        Lpu_uid: lpu_id,
                                        MorbusOnkoBase_id: morbusOnkoBase_id,
                                        MorbusOnko_id: morbusOnko_id,
                                        Morbus_id: morbus_id,
                                        PersonEvn_id: personEvn_id,
                                        Person_id: person_id,
                                        Server_id: server_id
                                    },
                                    callback: function () {
                                        grid.getStore().load({
                                            params: {
                                                'class': 'EvnUslugaOnkoSurg',
                                                'byMorbus': 1,
                                                'Morbus_id': onko.findField('Morbus_id').getValue(),
                                                'EvnEdit_id': onko.findField('Evn_pid').getValue()
                                            }
                                        });
                                    }
                                };
                                getWnd('swEvnUslugaOnkoSurgEditWindow').show(params);
                            }.createDelegate(this),
                            iconCls: 'add16',
                            text: 'Добавить'
                        }]
                    })
                }
            }), new sw.Promed.Panel({
                layout: 'form',
                collabsible: true,
                title: 'Специальное лечение',
                items: [{
                    fieldLabel: 'Дата начала специального лечения',
                    hiddenName: 'MorbusOnko_specSetDT',
                    name: 'MorbusOnko_specSetDT',
                    xtype: 'swdatefield'
                }, {
                    fieldLabel: 'Дата окончания специального лечения',
                    hiddenName: 'MorbusOnko_specDisDT',
                    name: 'MorbusOnko_specDisDT',
                    xtype: 'swdatefield'
                }, {
                    fieldLabel: 'Проведенное лечение первичной опухоли',
                    comboSubject: 'TumorPrimaryTreatType',
                    hiddenName: 'TumorPrimaryTreatType_id',
                    xtype: 'swcommonsprlikecombo',
                    width: 250
                }, {
                    fieldLabel: 'Сочетание видов лечения',
                    comboSubject: 'OnkoCombiTreatType',
                    hiddenName: 'OnkoCombiTreatType_id',
                    xtype: 'swcommonsprlikecombo',
                    width: 250
                }, {
                    fieldLabel: 'Позднее осложнение лечения',
                    comboSubject: 'OnkoLateComplTreatType',
                    hiddenName: 'OnkoLateComplTreatType_id',
                    xtype: 'swcommonsprlikecombo',
                    width: 250
                }, {
                    fieldLabel: 'Причины незавершенности радикального лечения',
                    comboSubject: 'TumorRadicalTreatIncomplType',
                    hiddenName: 'TumorRadicalTreatIncomplType_id',
                    xtype: 'swcommonsprlikecombo',
                    width: 250
                }]
            }), new sw.Promed.Panel({
                layout: 'form',
                collabsible: true,
                title: 'Контроль состояния',
                items: [{
                    fieldLabel: 'Клиническая группа',
                    comboSubject: 'OnkoStatusYearEndType',
                    hiddenName: 'OnkoStatusYearEndType_id',
                    xtype: 'swcommonsprlikecombo',
                    width: 250
                }]
            }), new sw.Promed.Panel({ 
                collabsible: true,
                title: 'Госпитализация',
                items: {
                    xtype: 'grid',
                    id: 'ESecEF_OnkoBasePSGrid',
                    height: 150,
                    width: 800,
                    columns: [
                        { dataIndex: 'MorbusOnkoBasePS_setDT', header: 'Поступил', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'MorbusOnkoBasePS_disDT', header: 'Выписан', resizable: true, sortable: true, width: 150 },
                        { dataIndex: 'OnkoPurposeHospType_id_Name', header: 'Цель госпитализации', resizable: true, sortable: true, width: 150 }
                    ],
                    store: new Ext.data.JsonStore({
                        fields: [
                           { name: 'MorbusOnkoBasePS_setDT', type: 'string' },
                           { name: 'MorbusOnkoBasePS_disDT', type: 'string' },
                           { name: 'OnkoPurposeHospType_id_Name', type: 'string' }
                        ],
                        url: '/?c=EvnSection&m=loadBasePS'
                    }),
                    tbar: new sw.Promed.Toolbar({
                        buttons: [{
                            handler: function () {
                                var section = parentWin.findById('EvnSectionEditForm').getForm(),
                                    onko = parentWin.findById('ESecEF_EvnOnkoForm').getForm(),
                                    grid = parentWin.findById('ESecEF_OnkoBasePSGrid');

                                var temp,
                                    section_id = (temp = section.findField('EvnSection_id')) && temp.getValue(),
                                    personEvn_id = (temp = section.findField('PersonEvn_id')) && temp.getValue(),
                                    server_id = (temp = section.findField('Server_id')) && temp.getValue(),
                                    person_id = (temp = onko.findField('Person_id')) && temp.getValue(),
                                    lpu_id = (temp = onko.findField('Lpu_foid')) && temp.getValue(),
                                    morbusOnkoBase_id = (temp = onko.findField('MorbusOnkoBase_id')) && temp.getValue(),
                                    morbusOnko_id = (temp = onko.findField('MorbusOnko_id')) && temp.getValue(),
                                    morbus_id = (temp = onko.findField('Morbus_id')) && temp.getValue();

                                var params = {
                                    //EvnUslugaOnkoSurg_id: null,
                                    //MorbusOnko_specDisDT: null,
                                    //MorbusOnko_specSetDT: null,
                                    action: "add",
                                    formParams: {
                                        //EvnUslugaOnkoSurg_id: null,
                                        //EvnUslugaOnkoSurg_pid: section_id,
                                        Lpu_id: lpu_id,
                                        Lpu_uid: lpu_id,
                                        MorbusOnkoBase_id: morbusOnkoBase_id,
                                        MorbusOnko_id: morbusOnko_id,
                                        Morbus_id: morbus_id,
                                        PersonEvn_id: personEvn_id,
                                        Person_id: person_id,
                                        Server_id: server_id
                                    },
                                    callback: function () {
                                        grid.getStore().load({
                                            params: {
                                                'Morbus_id': onko.findField('Morbus_id').getValue(),
                                                'Evn_id': onko.findField('Evn_pid').getValue()
                                            }
                                        });
                                    }
                                };
                                getWnd('swMorbusOnkoBasePSWindow').show(params);
                            }.createDelegate(this),
                            iconCls: 'add16',
                            text: 'Добавить'
                        }]
                    })
                }
            })]
        });
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
		
/*		
		if (getGlobalOptions().region.nick == 'samara') {
			mesTemplate = new Ext.XTemplate(
				'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold;">',
				'<td style="padding: 2px; width: 50%;">Код</td>',
				'<td style="padding: 2px; width: 20%;">Нормативный срок</td>',
				'<td style="padding: 2px; width: 30%;">Вид лечения</td></tr>',
				'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
				'<td style="padding: 2px;">{Mes_Code}&nbsp;</td>',
				'<td style="padding: 2px;">{Mes_KoikoDni}&nbsp;</td>',
				'<td style="padding: 2px;">{MesOperType_Name}&nbsp;</td>',
				'</tr></tpl>',
				'</table>'
			);
		}
*/
		Ext.apply(this, {
			keys:[this.keyHandlerAlt],
			buttons:[
				{
					handler:function () {
						this.doSave();
					}.createDelegate(this),
					iconCls:'save16',
					onShiftTabAction:function () {
						var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
						var isSamara = (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara');

						if (!this.specificsPanel.collapsed && this.action != 'view') {
							this.tryFocusOnSpecifics();
						}
						else if (!this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
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
							if (!isPskov && !isSamara && !this.findById('EvnSectionEditForm').getForm().findField('Mes_id').disabled) {
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
					onTabAction:function () {
						this.buttons[this.buttons.length - 1].focus();
					}.createDelegate(this),
					tabIndex:this.tabIndex + 51,
					text:BTN_FRMSAVE
				},
				{
					hidden: true,
					id: 'ESEW_PrintPregnancyResultButton',
					handler: function() {
						this.printPregnancyResult();
					}.createDelegate(this),
					iconCls: 'print16',
					text: 'Печать исхода беременности'
				},
				{
					text:'-'
				},
				HelpButton(this, -1),
				{
					handler:function () {
						this.onCancelAction();
					}.createDelegate(this),
					iconCls:'cancel16',
					onShiftTabAction:function () {
						if (this.action != 'view') {
							this.buttons[0].focus();
						}
					}.createDelegate(this),
					onTabAction:function () {
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
						else if (!this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
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
					tabIndex:this.tabIndex + 52,
					text:BTN_FRMCANCEL
				}
			],
			items:[ new sw.Promed.PersonInformationPanelShort({
				id:'ESecEF_PersonInformationFrame',
				region:'north'
			}),
				new Ext.form.FormPanel({
					autoScroll:true,
					autoheight:true,
					bodyBorder:false,
					bodyStyle:'padding: 5px 5px 0',
					border:false,
					frame:false,
					id:'EvnSectionEditForm',
					labelAlign:'right',
					labelWidth:180,
					reader:new Ext.data.JsonReader({
						success:Ext.emptyFn
					}, [
						{name:'accessType'},
						{name:'AnatomWhere_id'},
						{name:'Diag_aid'},
						{name:'Diag_id'},
						{name:'DiagSetPhase_id'},
						{name:'DeathPlace_id'},
						{name:'EvnDie_expDate'},
						{name:'EvnDie_expTime'},
						{name:'EvnDie_id'},
						{name:'EvnDie_IsWait'},
						{name:'EvnDie_IsAnatom'},
						{name:'EvnLeave_id'},
						{name:'EvnLeave_IsAmbul'},
						{name:'EvnLeave_UKL'},
						{name:'EvnOtherLpu_id'},
						{name:'EvnOtherSection_id'},
						{name:'EvnOtherStac_id'},
						{name:'EvnSection_PhaseDescr'},
						{name:'EvnSection_disDate'},
						{name:'EvnSection_disTime'},
						{name:'EvnSection_id'},
						{name:'EvnSection_pid'},
						{name:'EvnDiagPS_id'},
						{name:'EvnSection_setDate'},
						{name:'EvnSection_setTime'},
						{name:'EvnSection_IsPaid'},
						{name:'LeaveCause_id'},
						{name:'LeaveType_id'},
						{name:'LeaveType_Code'},
						{name:'LeaveTypeFed_id'},
						{name:'Org_oid'},
						{name:'LpuSection_aid'},
						{name:'LpuSection_id'},
						{name:'LpuSection_oid'},
						{name:'LpuUnitType_oid'},
						{name:'LpuSectionWard_id'},
						{name:'MedPersonal_aid'},
						{name:'MedPersonal_did'},
						{name:'MedPersonal_id'},
						{name:'Mes_id'},
						{name:'Mes2_id'},
						{name:'Org_aid'},
						{name:'PayType_id'},
						{name:'Person_id'},
						{name:'PersonEvn_id'},
						{name:'ResultDesease_id'},
						{name:'Server_id'},
						{name:'TariffClass_id'},
						{name:'EvnSection_IsAdultEscort'},
						{name:'EvnSection_IsMeal'},
						{ name: 'UslugaComplex_id' }
					]),
					region:'center',
					url:'/?c=EvnSection&m=saveEvnSection',
					items:[
						{
							name:'LeaveType_Code',
							value:'',
							xtype:'hidden'
						},
						{
							name:'accessType',
							value:'',
							xtype:'hidden'
						},
						{
							name:'Evn_Name',
							value:'',
							xtype:'hidden'
						},
						{
							name:'EvnDiagPS_id',
							value:0,
							xtype:'hidden'
						},
						{
							name:'EvnDie_id',
							value:0,
							xtype:'hidden'
						},
						{
							name:'EvnLeave_id',
							value:0,
							xtype:'hidden'
						},
						{
							name:'EvnOtherLpu_id',
							value:0,
							xtype:'hidden'
						},
						{
							name:'EvnOtherSection_id',
							value:0,
							xtype:'hidden'
						},
						{
							name:'EvnOtherStac_id',
							value:0,
							xtype:'hidden'
						},
						{
							name:'EvnSection_id',
							value:0,
							xtype:'hidden'
						},
						{
							name:'EvnSection_pid',
							value:-1,
							xtype:'hidden'
						},
						{
							name:'EvnSection_IsPaid',
							xtype:'hidden'
						},
						{
							// Патологоанатом
							name:'MedPersonal_aid',
							value:0,
							xtype:'hidden'
						},
						{
							// Врач, установивший смерть
							name:'MedPersonal_did',
							value:0,
							xtype:'hidden'
						},
						{
							name:'MedPersonal_id',
							value:-1,
							xtype:'hidden'
						},
						{
							name:'Person_id',
							value:-1,
							xtype:'hidden'
						},
						{
							name:'PersonEvn_id',
							value:-1,
							xtype:'hidden'
						},
						{
							name:'Server_id',
							value:-1,
							xtype:'hidden'
						},
						new sw.Promed.Panel({
							autoHeight:true,
							bodyStyle:'padding-top: 0.5em;',
							border:true,
							collapsible:true,
							id:'ESecEF_EvnSectionPanel',
							layout:'form',
							listeners:{
								'expand':function (panel) {
									// this.findById('EvnSectionEditForm').getForm().findField('EvnSection_setDate').focus(true);
								}.createDelegate(this)
							},
							style:'margin-bottom: 0.5em;',
							title:'1. Установка случая движения',
							items:[
								{
									border:false,
									layout:'column',
									items:[
										{
											border:false,
											layout:'form',
											items:[
												{
													allowBlank:false,
													fieldLabel:'Дата поступления',
													format:'d.m.Y',
													id: this.id + 'ESecEF_EvnSection_setDate',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.changedDates = true;

															if (blockedDateAfterPersonDeath('personpanelid', 'ESecEF_PersonInformationFrame', field, newValue, oldValue)) return;

															var base_form = this.findById('EvnSectionEditForm').getForm();

															var lpu_section_id = base_form.findField('LpuSection_id').getValue();
															//var lpu_section_ward_id = base_form.findField('LpuSectionWard_id').getValue();
															var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
															var evn_section_dis_date = base_form.findField('EvnSection_disDate').getValue();

															base_form.findField('LpuSection_id').clearValue();
															//base_form.findField('LpuSectionWard_id').clearValue();
															base_form.findField('MedStaffFact_id').clearValue();

															base_form.findField('Diag_id').setFilterByDate(newValue);
															if (!newValue) {
																// статистику должны быть доступны все отделения/места работы
																if(sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.ARMType == 'mstat') {
																	setLpuSectionGlobalStoreFilter();
																	setMedStaffFactGlobalStoreFilter();
																} else {
																	setLpuSectionGlobalStoreFilter({
																		// allowLowLevel: 'yes',
																		isStac:true
																	});
																	setMedStaffFactGlobalStoreFilter({
																		dateTo:Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
																		EvnClass_SysNick: 'EvnSection',
																		isStac:true/*,
																		isDoctor:true*/
																	});
																}
																base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
																base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
																
															}
															else {
																base_form.findField('EvnSection_disDate').setMinValue(newValue);
																if(sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.ARMType == 'mstat') {
																	setLpuSectionGlobalStoreFilter();
																	setMedStaffFactGlobalStoreFilter();
																} else {
																	setLpuSectionGlobalStoreFilter({
																		// allowLowLevel: 'yes',
																		isStac:true,
																		onDate:Ext.util.Format.date(newValue, 'd.m.Y')
																	});
																	setMedStaffFactGlobalStoreFilter({
																		dateFrom:Ext.util.Format.date(newValue, 'd.m.Y'),
																		dateTo:Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
																		EvnClass_SysNick: 'EvnSection',
																		isStac:true/*,
																		isDoctor:true*/
																	});
																}
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

															if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov' ]) ) {
																if ( base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date != Ext.util.Format.date(newValue, 'd.m.Y') ) {
																	base_form.findField('UslugaComplex_id').setUslugaComplexDate(Ext.util.Format.date(newValue, 'd.m.Y'));

																	base_form.findField('UslugaComplex_id').clearValue();
																	base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
																	base_form.findField('UslugaComplex_id').getStore().removeAll();
																	base_form.findField('UslugaComplex_id').getStore().baseParams.query = '';
																}
															}
															if (this.specificsPanel.isExpanded) {
																this.onSpecificsExpand(this.specificsPanel);
															}
															this.leaveTypeFilter();
															this.refreshFieldsVisibility(['DiagSetPhase_id']);
														}.createDelegate(this),
														'keydown':function (inp, e) {
															if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																e.stopEvent();
																this.buttons[this.buttons.length - 1].focus();
															}
														}.createDelegate(this)
													},
													name:'EvnSection_setDate',
													plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
													selectOnFocus:true,
													tabIndex:this.tabIndex + 1,
													width:100,
													xtype:'swdatefield'
												},
												{
													fieldLabel:'Дата выписки',
													format:'d.m.Y',
													id: this.id + 'ESecEF_EvnSection_disDate',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.loadMesCombo();

															var base_form = this.findById('EvnSectionEditForm').getForm();

															var med_staff_fact_id = base_form.findField('MedStaffFact_did').getValue();

															base_form.findField('MedStaffFact_did').clearValue();

															if (!newValue) {
																setMedStaffFactGlobalStoreFilter({
																	isStac:true
																});
																base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
															}
															else {
																setMedStaffFactGlobalStoreFilter({
																	isStac:true, onDate:Ext.util.Format.date(newValue, 'd.m.Y')
																});
																base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
															}

															if (base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_id)) {
																base_form.findField('MedStaffFact_did').setValue(med_staff_fact_id);
															}
															this.leaveTypeFilter();
															this.recountKoikoDni();
															this.refreshFieldsVisibility(['DiagSetPhase_id']);
														}.createDelegate(this),
														'keydown':function (inp, e) {
															if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
																e.stopEvent();
																this.buttons[this.buttons.length - 1].focus();
															}
														}.createDelegate(this)
													},
													name:'EvnSection_disDate',
													plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
													selectOnFocus:true,
													tabIndex:this.tabIndex + 3,
													width:100,
													xtype:'swdatefield'
												}
											]
										},
										{
											border:false,
											labelWidth:50,
											layout:'form',
											items:[
												{
													allowBlank:false,
													fieldLabel:'Время',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.changedDates = true;
															var base_form = this.findById('EvnSectionEditForm').getForm();
															base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
														}.createDelegate(this),
														'keydown':function (inp, e) {
															if (e.getKey() == Ext.EventObject.F4) {
																e.stopEvent();
																inp.onTriggerClick();
															}
														}
													},
													name:'EvnSection_setTime',
													onTriggerClick:function () {
														var base_form = this.findById('EvnSectionEditForm').getForm();
														var time_field = base_form.findField('EvnSection_setTime');

														if (time_field.disabled) {
															return false;
														}

														setCurrentDateTime({
															callback:function () {
																base_form.findField('EvnSection_disDate').setMinValue(base_form.findField('EvnSection_setDate').getValue());
																base_form.findField('EvnSection_setDate').fireEvent('change', base_form.findField('EvnSection_setDate'), base_form.findField('EvnSection_setDate').getValue());
															}.createDelegate(this),
															dateField:base_form.findField('EvnSection_setDate'),
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
													tabIndex:this.tabIndex + 2,
													validateOnBlur:false,
													width:60,
													xtype:'swtimefield'
												},
												{
													fieldLabel:'Время',
													listeners:{
														'change':function (field, newValue, oldValue) {
															this.changedDates = true;
															var base_form = this.findById('EvnSectionEditForm').getForm();
														}.createDelegate(this),
														'keydown':function (inp, e) {
															if (e.getKey() == Ext.EventObject.F4) {
																e.stopEvent();
																inp.onTriggerClick();
															}
														}
													},
													name:'EvnSection_disTime',
													onTriggerClick:function () {
														var base_form = this.findById('EvnSectionEditForm').getForm();
														var time_field = base_form.findField('EvnSection_disTime');

														if (time_field.disabled) {
															return false;
														}

														setCurrentDateTime({
															callback:function () {
																base_form.findField('EvnSection_disDate').fireEvent('change', base_form.findField('EvnSection_disDate'), base_form.findField('EvnSection_disDate').getValue());
															}.createDelegate(this),
															dateField:base_form.findField('EvnSection_disDate'),
															loadMask:true,
															setDate:true,
															setDateMaxValue:true,
															addMaxDateDays: this.addMaxDateDays,
															setDateMinValue:false,
															setTime:true,
															timeField:time_field,
															windowId:this.id
														});
													}.createDelegate(this),
													plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
													tabIndex:this.tabIndex + 4,
													validateOnBlur:false,
													width:60,
													xtype:'swtimefield'
												}
											]
										},
										{
											border:false,
											labelWidth:210,
											layout:'form',
											items:[
												{
													xtype: 'swyesnocombo',
													tabIndex:this.tabIndex + 5,
													name: 'EvnSection_IsAdultEscort',
													hiddenName: 'EvnSection_IsAdultEscort',
													allowBlank: true,
													value: 1,
													width: 70,
													fieldLabel: 'Сопровождается взрослым'
												}
											]
										}
									]
								},
								{
									allowBlank:false,
									hiddenName:'LpuSection_id',
									listeners:{
										'change':function (combo) {
											this.checkLpuUnitType();
											this.wardOnSexFilter();
											if (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['samara','kareliya','astra'])) {
												this.filterLpuSectionBedProfilesByLpuSection();
											}
										}.createDelegate(this)
									},
									id:this.id + '_LpuSectionCombo',
									linkedElements:[
										'ESecEF_LpuSectionWardCombo',
										this.id + '_MedStaffFactCombo'
									],
									tabIndex:this.tabIndex + 6,
									width:500,
									xtype:'swlpusectionglobalcombo'
								},
								{
									xtype: 'swyesnocombo',
									tabIndex:this.tabIndex + 5,
									name: 'EvnSection_IsMeal',
									hiddenName: 'EvnSection_IsMeal',
									allowBlank: true,
									value: 1,
									width: 70,
									fieldLabel: 'С питанием'
								},
								{
									allowBlank:true,
									visible: (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['samara','kareliya','astra'])),
									hiddenName:'LpuSectionBedProfile_id',
									id:'ESecEF_LpuSectionBedProfileCombo',
									tabIndex:this.tabIndex + 7,
									width:500,
									xtype:'swlpusectionbedprofilecombo'
								},
								{
									fieldLabel: 'Палата',
									allowBlank:true,
									hiddenName:'LpuSectionWard_id',
									id:'ESecEF_LpuSectionWardCombo',
									parentElementId:this.id + '_LpuSectionCombo',
									tabIndex:this.tabIndex + 7,
									width:500,
									xtype:'swlpusectionwardglobalcombo'
								},
								{
									allowBlank:false,
									tabIndex:this.tabIndex + 8,
									typeCode:'int',
									useCommonFilter: true,
									width:300,
									xtype:'swpaytypecombo'
								},
								{
									autoLoad:false,
									comboSubject:'TariffClass',
									fieldLabel:'Вид тарифа',
									hiddenName:'TariffClass_id',
									lastQuery:'',
									tabIndex:this.tabIndex + 9,
									typeCode:'int',
									width:300,
									xtype:'swtariffclasscombo'
								},
								{
									allowBlank:false,
									dateFieldId: this.id + 'ESecEF_EvnSection_setDate',
									enableOutOfDateValidation: true,
									fieldLabel:'Врач',
									hiddenName:'MedStaffFact_id',
									listeners:{
										'change':function (combo) {
											this.checkLpuUnitType();
											this.wardOnSexFilter();
											if (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['samara','kareliya','astra'])) {
												this.filterLpuSectionBedProfilesByLpuSection();
											}
										}.createDelegate(this)
									},
									id:this.id + '_MedStaffFactCombo',
									listWidth:650,
									parentElementId:this.id + '_LpuSectionCombo',
									tabIndex:this.tabIndex + 10,
									width:500,
									xtype:'swmedstafffactglobalcombo'
								},
									
									
									new Ext.DataView({
									id:"dataViewDiag",
									store:new Ext.data.Store({
											autoLoad:false,
											reader:new Ext.data.JsonReader({
												id:'EvnDiagPS_id'
												
											}, [
												{name: 'EvnDiagPS_id', mapping: 'EvnDiagPS_id',key:true},
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
												{name: 'RecordStatus_Code',mapping: 'RecordStatus_Code'}

											]),
											url:'/?c=EvnDiag&m=loadEvnDiagPSGrid'
											
										}),
										
									itemSelector: 'tr',
									autoHeight: true,
									style:"margin-left:185px",
									tpl : new Ext.XTemplate(
										'<table><tpl for="."><tr>',
										'<td>{Diag_Code}</td>\n\
										<td style="width:335px;max-width:335px;overflow:hidden;white-space:nowrap">:{Diag_Name}</td>\n\
										<td> - {EvnDiagPS_setDate}</td>\n\
										<td><div onclick="Ext.getCmp(\'EvnSectionEditWindow\').deleteClinDiag(\'EvnDiagPS\',{EvnDiagPS_id})" class="delete16" style="background-repeat:no-repeat; background-size:23%;cursor:pointer;"><span style="padding-left:17px;">Удалить</span></div></td>',
										'</tr></tpl></table>'
									)
										,
									emptyText : ''
								}),
								{
									width:800,
									layout:'column',
									border:false,
									items:[{
										layout:'form',
										border:false,
										items:[
										new sw.Promed.swDiagPanel({
										labelWidth:180,
										bodyStyle:'padding: 0px;',
										phaseDescrName:'EvnSection_PhaseDescr',
										diagSetPhaseName:'DiagSetPhase_id',
										diagField:{
											checkAccessRights: true,
											MKB:null,
											allowBlank:false,
											fieldLabel:'Основной диагноз',
											hiddenName:'Diag_id',
											id:this.id + '_DiagCombo',
											onChange: function (combo, value) {
												
												this.loadMesCombo();
												this.loadKSGKPGKOEF();
												this.loadMes2Combo(-1, true);
												this.changeDiag(combo,value);
											}.createDelegate(this),
											tabIndex:this.tabIndex + 11,
											enableNativeTabSupport:false,
											width:500,
											xtype:'swdiagcombo'
										},
										diagPhase: {
											xtype: 'swdiagsetphasecombo',
											fieldLabel: 'Состояние пациента',
											hiddenName: 'DiagSetPhase_id',
											allowBlank: false, //!=kz
											tabIndex: this.tabindex + 11,
											width: 500,
											editable: false
										},
										copyBtn:{
											text:'=',
											tooltip:'Скопировать из предыдущего отделения',
											handler:function () {
												var diag_combo = this.findById('EvnSectionEditForm').getForm().findField('Diag_id');
												var diag_id;
												if (this.DiagPred_id) {
													diag_id = this.DiagPred_id;
													diag_combo.getStore().removeAll();
													diag_combo.clearValue();
													diag_combo.getStore().load({
														callback:function () {
															diag_combo.setValue(diag_id);
															diag_combo.getStore().each(function (record) {
																if (record.get('Diag_id') == diag_id) {
																	diag_combo.fireEvent('select', diag_combo, record, 0);
																	this.loadMesCombo();
																	this.loadMes2Combo(-1, true);
																	this.changeDiag(record);
																}
															}.createDelegate(this));
														}.createDelegate(this),
														params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_id}
													});
												}
											}.createDelegate(this),
											id:this.id + '_copyBtn',
											xtype:'button'
										}
										})	
								
								
								,{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											border: false,
											//labelWidth: 50,
											layout: 'form',
											hidden: (getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov', 'samara' ])),
											items: [{
												// allowBlank: false,
												beforeBlur:function () {
													// медитируем
													return true;
												},
												// disabled: true,
												displayField:'Mes_Code',
												editable:true,
												enableKeyEvents:true,
												fieldLabel: getMESAlias(),
												forceSelection:false,
												hiddenName:'Mes_id',
												listeners:{
													'change':function (combo, newValue, oldValue) {
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
															base_form.findField('EvnSection_KoikoDniNorm').setRawValue(record.get('Mes_KoikoDni'));
														}
														else {
															base_form.findField('EvnSection_KoikoDniNorm').setRawValue('');
															base_form.findField('Mes2_id').clearValue();
															base_form.findField('Mes2_id').disable();
														}
													}.createDelegate(this),
													'keydown':function (inp, e) {
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
																else if (!this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
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
												mode:'local',
												resizable:true,
												selectOnFocus:true,
												store:new Ext.data.Store({
													autoLoad:false,
													reader:new Ext.data.JsonReader({
														id:'Mes_id'
													}, [
														{name: 'Mes_id', mapping: 'Mes_id'},
														{name: 'Mes_Code', mapping: 'Mes_Code'},
														{name: 'Mes_KoikoDni', mapping: 'Mes_KoikoDni'},
														{name: 'MedicalCareKind_Name', mapping: 'MedicalCareKind_Name'},
														{name: 'MesAgeGroup_Name', mapping: 'MesAgeGroup_Name'},
														{name: 'MesNewUslovie', mapping: 'MesNewUslovie', type: 'int'},
														{name: 'MesOperType_Name', mapping: 'MesOperType_Name'}
													]),
													url:'/?c=EvnSection&m=loadMesList'
												}),
												tabIndex:this.tabIndex + 12,
												tpl: mesTemplate,
												triggerAction:'all',
												valueField:'Mes_id',
												width: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa')?222:500,
												xtype:'combo'
											}]
										}]
									}, {
										border: false,
										labelWidth: 50,
										layout: 'form',
										hidden: (getGlobalOptions().region && (getGlobalOptions().region.nick.inlist([ 'pskov', 'samara']) || getGlobalOptions().region.nick != 'ufa')),
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
											tabIndex: this.tabIndex + 13,
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
								}],width:716
										
									},{
										layout:'form',
										border:false,
										items:{
										
										text:'+',
										tooltip:'Добавить основной диагноз',
										handler:function () {
											this.openEvnDiagPSEditWindow2('add', 'sect');
										}.createDelegate(this),
										id:'addDiag',
										xtype:'button'
										},width:40
										
									}]},
								{
									border: false,
									layout: 'form',
									hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov' ])),
									items: [{
										allowBlank: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov' ])),
										fieldLabel: 'Услуга лечения',
										hiddenName: 'UslugaComplex_id',
										to: 'EvnSection',
										listWidth: 600,
										tabIndex:this.tabIndex + 14,
										width: 500,
										xtype: 'swuslugacomplexnewcombo'
									}]
								},
								{
									border: false,
									//labelWidth: 50,
									layout: 'form',
									hidden: (getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov', 'samara' ])),
									items: [{
										disabled: true,
										fieldLabel: 'Норматив',
										name:'EvnSection_KoikoDniNorm',
										tabIndex:this.tabIndex + 14,
										width:80,
										xtype:'textfield'
									}]
								},
								{
									disabled:true,
									fieldLabel:'Факт',
									name:'EvnSection_KoikoDni',
									tabIndex:this.tabIndex + 15,
									width:80,
									xtype:'textfield'
								},
								{
									border: false,
									layout: 'form',
									hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ])),
									items: [{
										readOnly: true,
										fieldLabel: 'КСГ',
										name:'EvnSection_KSG',
										tabIndex:this.tabIndex + 15,
										width:80,
										xtype:'textfield'
									}, {
										readOnly: true,
										fieldLabel: 'КПГ',
										name:'EvnSection_KPG',
										tabIndex:this.tabIndex + 15,
										width:80,
										xtype:'textfield'
									}, {
										readOnly: true,
										fieldLabel: 'коэффициент КСГ/КПГ',
										name:'EvnSection_KOEF',
										tabIndex:this.tabIndex + 15,
										width:80,
										xtype:'textfield'
									}]
								}
							]
						}),
						new sw.Promed.Panel({
							autoHeight:true,
							bodyStyle:'padding-top: 0.5em;',
							border:true,
							collapsible:true,
							id:that.id + 'ESecEF_EvnLeavePanel',
							layout:'form',
							style:'margin-bottom: 0.5em;',
							title:'2. Исход госпитализации',
							items:[
								{
									fieldLabel: ('kareliya' == getGlobalOptions().region.nick)?'Результат госпитализации':'Исход госпитализации',
									hiddenName:'LeaveTypeFed_id',
									listeners: {
										'change':function (combo, newValue, oldValue) {
											var index = combo.getStore().findBy(function (rec) {
												if (rec.get('LeaveType_id') == newValue) {
													return true;
												}
												else {
													return false;
												}
											});
											var record = combo.getStore().getAt(index);

											combo.fireEvent('beforeselect', combo, record);
										},
										'beforeselect':function (combo, record) {
											var base_form = that.findById('EvnSectionEditForm').getForm();
											var LeaveTypeCombo = base_form.findField('LeaveType_id');
											LeaveTypeCombo.clearValue();

											if (record) {
												LeaveTypeCombo.setFieldValue('LeaveType_fedid',record.get('LeaveType_id'));

												switch(record.get('LeaveType_Code')) {
													case 105:
													case 205:
														base_form.findField('EvnDie_IsWait').setValue(1);
													break;

													case 106:
													case 206:
														base_form.findField('EvnDie_IsWait').setValue(2);
													break;
												}
											}
											
											var index = LeaveTypeCombo.getStore().findBy(function (rec) {
												if (rec.get('LeaveType_id') == LeaveTypeCombo.getValue()) {
													return true;
												}
												else {
													return false;
												}
											});
											var record = LeaveTypeCombo.getStore().getAt(index);
											LeaveTypeCombo.fireEvent('beforeselect', combo, record);
										}
									},
									tabIndex:this.tabIndex + 16,
									width:300,
									xtype: 'swleavetypefedcombo'
								}, {
									autoLoad:false,
									comboSubject:'LeaveType',
									fieldLabel:('kareliya' == getGlobalOptions().region.nick)?'Результат госпитализации':'Исход госпитализации',
									hiddenName:'LeaveType_id',
									listeners:{
										'change':function (combo, newValue, oldValue) {
											var index = combo.getStore().findBy(function (rec) {
												if (rec.get('LeaveType_id') == newValue) {
													return true;
												}
												else {
													return false;
												}
											});
											var record = combo.getStore().getAt(index);

											combo.fireEvent('beforeselect', combo, record);
										}.createDelegate(this),
										'beforeselect':function (combo, record) {
											var isKareliya = (getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya');
											var base_form = this.findById('EvnSectionEditForm').getForm();
											that.leaveCauseFilter();
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
											base_form.findField('LpuUnitType_oid').setAllowBlank(true);
											base_form.findField('LpuUnitType_oid').setContainerVisible(false);
											base_form.findField('MedStaffFact_did').setAllowBlank(true);
											base_form.findField('MedStaffFact_did').setContainerVisible(false);
                                            base_form.findField('DeathPlace_id').setAllowBlank(true);
                                            base_form.findField('DeathPlace_id').setContainerVisible(false);
											base_form.findField('ResultDesease_id').setAllowBlank(true);
											base_form.findField('ResultDesease_id').setContainerVisible(false);

											if (!record || !record.get('LeaveType_id')) {
												return true;
											}
											
											base_form.findField('EvnLeave_UKL').setAllowBlank(false);
											base_form.findField('EvnLeave_UKL').setContainerVisible(true);

											if (!base_form.findField('EvnLeave_UKL').getValue()) {
												base_form.findField('EvnLeave_UKL').setValue(1);
											}

											switch (parseInt(record.get('LeaveType_Code'))) {
												// Выписка
												case 1:
												case 101:
												case 201:
													base_form.findField('EvnLeave_IsAmbul').setAllowBlank(false);
													base_form.findField('EvnLeave_IsAmbul').setContainerVisible(true);
													base_form.findField('LeaveCause_id').setAllowBlank(false);
													base_form.findField('LeaveCause_id').setContainerVisible(true);
													base_form.findField('ResultDesease_id').setAllowBlank(false);
													base_form.findField('ResultDesease_id').setContainerVisible(true);

													base_form.findField('LeaveCause_id').setFieldLabel('Причина выписки:');

													base_form.findField('LeaveCause_id').fireEvent('change', base_form.findField('LeaveCause_id'), base_form.findField('LeaveCause_id').getValue());
													
													if (!base_form.findField('EvnLeave_IsAmbul').getValue()) {
														base_form.findField('EvnLeave_IsAmbul').setValue(1);
													}
													break;

												// Перевод в другое ЛПУ
												case 2:
												case 102:
												case 202:
													base_form.findField('LeaveCause_id').setContainerVisible(true);
													base_form.findField('Org_oid').setAllowBlank(false);
													base_form.findField('Org_oid').setContainerVisible(true);
													base_form.findField('ResultDesease_id').setAllowBlank(false);
													base_form.findField('ResultDesease_id').setContainerVisible(true);

													base_form.findField('LeaveCause_id').setFieldLabel('Причина перевода:');
													break;

												// Смерть
												case 3:
												case 105:
												case 106:
												case 205:
												case 206:
													this.findById(that.id + 'ESecEF_AnatomPanel').show();
													if ('kareliya' == getGlobalOptions().region.nick) {
														base_form.findField('EvnDie_IsWait').setAllowBlank(false);
														base_form.findField('EvnDie_IsWait').setContainerVisible(true);
													}
													base_form.findField('EvnDie_IsAnatom').setAllowBlank(false);
													base_form.findField('EvnDie_IsAnatom').setContainerVisible(true);
													base_form.findField('MedStaffFact_did').setAllowBlank(false);
													base_form.findField('MedStaffFact_did').setContainerVisible(true);
													base_form.findField('DeathPlace_id').setAllowBlank(false);
													base_form.findField('DeathPlace_id').setContainerVisible(true);

													base_form.findField('EvnDie_IsAnatom').fireEvent('change', base_form.findField('EvnDie_IsAnatom'), base_form.findField('EvnDie_IsAnatom').getValue());

													if (base_form.findField('EvnDie_IsAnatom').getValue() == 2) {
														base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), base_form.findField('AnatomWhere_id').getValue());
													}
													break;

												// Перевод в стационар другого типа
												case 4:
												case 103:
												case 203:
													if (isKareliya) {
														var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');
														var LeaveType_Code = parseInt(record.get('LeaveType_Code'));
														if (LeaveType_Code == 103 && LpuUnitType_SysNick == 'stac') {
															base_form.findField('LpuUnitType_oid').getStore().filterBy(function (rec) {
																return (rec.get('LpuUnitType_Code').inlist([3,4,5]));
															});
														} else if (LeaveType_Code == 203 && LpuUnitType_SysNick.inlist(['dstac','hstac'])) {
															base_form.findField('LpuUnitType_oid').getStore().filterBy(function (rec) {
																return (rec.get('LpuUnitType_Code').inlist([2,3,4]));
															});
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
													break;

												// Перевод в другое отделение
												case 5:
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

													if (date) {
														params.onDate = Ext.util.Format.date(date, 'd.m.Y');
													}

													setLpuSectionGlobalStoreFilter(params);

													base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

													if (base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid)) {
														base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
													}
													break;
											}

											base_form.findField('LeaveType_Code').setValue(record.get('LeaveType_Code'));
										}.createDelegate(this),
										'keydown':function (inp, e) {
											if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
												e.stopEvent();

												var base_form = this.findById('EvnSectionEditForm').getForm();
												var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
												var isSamara = (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara');

												if (!isPskov && !isSamara && !this.findById('ESecEF_EvnSectionPanel').collapsed && !base_form.findField('Mes_id').disabled) {
													base_form.findField('Mes_id').focus();
												}
												else {
													this.buttons[this.buttons.length - 1].focus();
												}
											}
										}.createDelegate(this)
									},
									tabIndex:this.tabIndex + 16,
									width:300,
									xtype:'swleavetypecombo'
								},
								{
									allowDecimals:true,
									allowNegative:false,
									fieldLabel:'Уровень качества лечения',
									maxValue:1,
									minValue:0,
									name:'EvnLeave_UKL',
									tabIndex:this.tabIndex + 17,
									width:70,
									value:1,
									xtype:'numberfield'
								},
								{
									autoLoad:false,
									comboSubject:'ResultDesease',
									fieldLabel:('kareliya' == getGlobalOptions().region.nick)?'Исход госпитализации':'Исход заболевания',
									hiddenName:'ResultDesease_id',
									lastQuery:'',
									listWidth:700,
									tabIndex:this.tabIndex + 18,
									typeCode:'int',
									width:500,
									xtype:'swresultdeseasecombo'
								},
								{
									autoLoad:false,
									comboSubject:'LeaveCause',
									fieldLabel:'Причина выписки',
									hiddenName:'LeaveCause_id',
									listWidth:400,
									tabIndex:this.tabIndex + 19,
									typeCode:'int',
									width:300,
									xtype:'swleavecausecombo'
								},
								{
									autoLoad:false,
									comboSubject:'YesNo',
									fieldLabel:'Направлен на амб. лечение',
									hiddenName:'EvnLeave_IsAmbul',
									tabIndex:this.tabIndex + 20,
									typeCode:'int',
									width:70,
									xtype:'swyesnocombo'
								},
								{
									displayField:'Org_Name',
									editable:false,
									enableKeyEvents:true,
									fieldLabel:'ЛПУ',
									hiddenName:'Org_oid',
									listeners:{
										'keydown':function (inp, e) {
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
										'keyup':function (inp, e) {
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
									mode:'local',
									onTrigger1Click:function () {
										var base_form = this.findById('EvnSectionEditForm').getForm();
										var combo = base_form.findField('Org_oid');

										if (combo.disabled) {
											return false;
										}

										getWnd('swOrgSearchWindow').show({
											OrgType_id: 11,
											onClose:function () {
												combo.focus(true, 200)
											},
											onSelect:function (org_data) {
												if (org_data.Org_id > 0) {
													combo.getStore().loadData([
														{
															Org_id:org_data.Org_id,
															Org_Name:org_data.Org_Name
														}
													]);
													combo.setValue(org_data.Org_id);
													getWnd('swOrgSearchWindow').hide();
													combo.collapse();
												}
											}
										});
									}.createDelegate(this),
									store:new Ext.data.JsonStore({
										autoLoad:false,
										fields:[
											{name:'Org_id', type:'int'},
											{name:'Org_Name', type:'string'}
										],
										key:'Org_id',
										sortInfo:{
											field:'Org_Name'
										},
										url:C_ORG_LIST
									}),
									tabIndex:this.tabIndex + 21,
									tpl:new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'{Org_Name}',
										'</div></tpl>'
									),
									trigger1Class:'x-form-search-trigger',
									triggerAction:'none',
									valueField:'Org_id',
									width:500,
									xtype:'swbaseremotecombo'
								},
								{
									autoLoad:false,
									comboSubject:'LpuUnitType',
									fieldLabel:'Тип стационара',
									hiddenName:'LpuUnitType_oid',
									lastQuery:'',
									listeners:{
										'change':function (combo, newValue, oldValue) {
											var base_form = this.findById('EvnSectionEditForm').getForm();

											var date = base_form.findField('EvnSection_disDate').getValue();
											var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
											var params = new Object();
											var record = combo.getStore().getById(newValue);

											base_form.findField('LpuSection_oid').clearValue();

											params.isStac = true;

											if (record) {
												params.arrayLpuUnitType = [ record.get('LpuUnitType_Code') ];
											}

											if (date) {
												params.onDate = Ext.util.Format.date(date, 'd.m.Y');
											}

											setLpuSectionGlobalStoreFilter(params);

											base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

											if (base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid)) {
												base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
											}
										}.createDelegate(this)
									},
									tabIndex:this.tabIndex + 22,
									typeCode:'int',
									width:300,
									xtype:'swlpuunittypecombo'
								},
								{
									fieldLabel:'Отделение',
									hiddenName:'LpuSection_oid',
									listWidth:650,
									tabIndex:this.tabIndex + 23,
									width:500,
									xtype:'swlpusectionglobalcombo'
								},
								{
									dateFieldId: this.id + 'ESecEF_EvnSection_disDate',
									enableOutOfDateValidation: true,
									fieldLabel:'Врач, установивший смерть',
									hiddenName:'MedStaffFact_did',
									id: this.id + '_MedStaffFact_did',
									listWidth:650,
									tabIndex:this.tabIndex + 24,
									width:500,
									xtype:'swmedstafffactglobalcombo'
								},
								{
									dateFieldId: this.id + 'ESecEF_EvnSection_disDate',
									//enableOutOfDateValidation: true,
									fieldLabel:'Место смерти',
									hiddenName:'DeathPlace_id',
									id: this.id + '_DeathPlace_id',
                                    allowSysNick: true,
                                    autoLoad: false,
                                    comboSubject: 'DeathPlace',
									listWidth:650,
									tabIndex:this.tabIndex + 25,
									width:500,
									xtype:'swcommonsprcombo'
								},
								{
									autoLoad:false,
									comboSubject:'YesNo',
									fieldLabel:'Умер в приемном покое',
									hiddenName:'EvnDie_IsWait',
									listeners:{},
									tabIndex:this.tabIndex + 26,
									typeCode:'int',
									width:70,
									xtype:'swyesnocombo'
								},
								{
									autoLoad:false,
									comboSubject:'YesNo',
									fieldLabel:'Необходимость экспертизы',
									hiddenName:'EvnDie_IsAnatom',
									listeners:{
										'change':function (combo, newValue, oldValue) {
											var index = combo.getStore().findBy(function (rec) {
												if (rec.get('YesNo_id') == newValue) {
													return true;
												}
												else {
													return false;
												}
											});
											var record = combo.getStore().getAt(index);

											combo.fireEvent('select', combo, record);
										}.createDelegate(this),
										'select':function (combo, record) {
											var base_form = this.findById('EvnSectionEditForm').getForm();

											if (!record || record.get('YesNo_Code') == 0) {
												this.findById(that.id + 'ESecEF_AnatomPanel').hide();
												this.findById(that.id + 'ESecEF_AnatomDiagPanel').hide();

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

												if (base_form.findField('EvnDie_id').getValue()) {
													this.findById(that.id + 'ESecEF_AnatomDiagGrid').loadData({
														globalFilters:{
															'class':'EvnDiagPSDie',
															EvnDiagPS_pid:base_form.findField('EvnDie_id').getValue()
														},
														noFocusOnLoad:true
													});
												}
												else {
													LoadEmptyRow(this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid());
												}
											}

											base_form.findField('EvnDie_expDate').fireEvent('change', base_form.findField('EvnDie_expDate'), base_form.findField('EvnDie_expDate').getValue());
											base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), base_form.findField('AnatomWhere_id').getValue());
										}.createDelegate(this)
									},
									tabIndex:this.tabIndex + 25,
									typeCode:'int',
									width:70,
									xtype:'swyesnocombo'
								},
								{
									autoHeight:true,
									id:that.id + 'ESecEF_AnatomPanel',
									style:'padding: 0px;',
									title:'Патологоанатомическая экспертиза',
									width:750,
									xtype:'fieldset',
									items:[
										{
											border:false,
											layout:'column',

											items:[
												{
													border:false,
													layout:'form',

													items:[
														{
															fieldLabel:'Дата экспертизы',
															format:'d.m.Y',
															listeners:{
																'change':function (field, newValue, oldValue) {
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

																		setMedStaffFactGlobalStoreFilter();
																		base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

																		base_form.findField('AnatomWhere_id').setAllowBlank(true);
																		base_form.findField('Diag_aid').setAllowBlank(true);
																		base_form.findField('EvnDie_expTime').setAllowBlank(true);
																	}
																	else {
																		setLpuSectionGlobalStoreFilter({
																			// isStac: true,
																			onDate:Ext.util.Format.date(newValue, 'd.m.Y')
																		});
																		base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

																		setMedStaffFactGlobalStoreFilter({
																			onDate:Ext.util.Format.date(newValue, 'd.m.Y')
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
															name:'EvnDie_expDate',
															plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
															selectOnFocus:true,
															tabIndex:this.tabIndex + 26,
															width:100,
															xtype:'swdatefield'
														}
													]
												},
												{
													border:false,
													labelWidth:50,
													layout:'form',
													items:[
														{
															fieldLabel:'Время',
															name:'EvnDie_expTime',
															listeners:{
																'keydown':function (inp, e) {
																	if (e.getKey() == Ext.EventObject.F4) {
																		e.stopEvent();
																		inp.onTriggerClick();
																	}
																}
															},
															onTriggerClick:function () {
																var base_form = this.findById('EvnSectionEditForm').getForm();
																var time_field = base_form.findField('EvnDie_expTime');

																if (time_field.disabled) {
																	return false;
																}

																setCurrentDateTime({
																	callback:function () {
																		base_form.findField('EvnDie_expDate').fireEvent('change', base_form.findField('EvnDie_expDate'), base_form.findField('EvnDie_expDate').getValue());
																	}.createDelegate(this),
																	dateField:base_form.findField('EvnDie_expDate'),
																	loadMask:true,
																	setDate:true,
																	setDateMaxValue:false,
																	setDateMinValue:false,
																	setTime:true,
																	timeField:time_field,
																	windowId:this.id
																});
															}.createDelegate(this),
															plugins:[ new Ext.ux.InputTextMask('99:99', true) ],
															tabIndex:this.tabIndex + 27,
															validateOnBlur:false,
															width:60,
															xtype:'swtimefield'
														}
													]
												}
											]
										},
										{
											autoLoad:false,
											comboSubject:'AnatomWhere',
											fieldLabel:'Место проведения',
											hiddenName:'AnatomWhere_id',
											lastQuery:'',
											listeners:{
												'change':function (combo, newValue, oldValue) {
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
												'select':function (combo, record) {
													var base_form = this.findById('EvnSectionEditForm').getForm();

													var lpu_section_combo = base_form.findField('LpuSection_aid');
													var med_staff_fact_combo = base_form.findField('MedStaffFact_aid');
													var org_combo = base_form.findField('Org_aid');

													lpu_section_combo.clearValue();
													med_staff_fact_combo.clearValue();
													org_combo.clearValue();

													if (!record) {
														lpu_section_combo.disable();
														med_staff_fact_combo.disable();
														org_combo.disable();

														return false;
													}

													switch (parseInt(record.get('AnatomWhere_Code'))) {
														case 1:
															lpu_section_combo.enable();
															med_staff_fact_combo.enable();
															org_combo.disable();
															break;

														case 2:
														case 3:
															lpu_section_combo.disable();
															med_staff_fact_combo.disable();
															org_combo.enable();
															break;

														default:
															lpu_section_combo.disable();
															med_staff_fact_combo.disable();
															org_combo.disable();
															break;
													}
												}.createDelegate(this)
											},
											tabIndex:this.tabIndex + 28,
											typeCode:'int',
											width:300,
											xtype:'swanatomwherecombo'
										},
										{
											hiddenName:'LpuSection_aid',
											id:this.id + 'ESecEF_LpuSectionAnatomCombo',
											linkedElements:[
												this.id + 'ESecEF_MedStaffFactAnatomCombo'
											],
											tabIndex:this.tabIndex + 29,
											width:500,
											xtype:'swlpusectionglobalcombo'
										},
										{
											displayField:'Org_Name',
											editable:false,
											enableKeyEvents:true,
											fieldLabel:'Организация',
											hiddenName:'Org_aid',
											listeners:{
												'keydown':function (inp, e) {
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
												'keyup':function (inp, e) {
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
											mode:'local',
											onTrigger1Click:function () {
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
														org_type = 'anatom';
														break;

													default:
														return false;
														break;
												}

												getWnd('swOrgSearchWindow').show({
													object:org_type,
													onlyFromDictionary: true,
													onClose:function () {
														combo.focus(true, 200)
													},
													onSelect:function (org_data) {
														if (org_data.Org_id > 0) {
															combo.getStore().loadData([
																{
																	Org_id:org_data.Org_id,
																	Org_Name:org_data.Org_Name
																}
															]);
															combo.setValue(org_data.Org_id);
															getWnd('swOrgSearchWindow').hide();
															combo.collapse();
														}
													}
												});
											}.createDelegate(this),
											store:new Ext.data.JsonStore({
												autoLoad:false,
												fields:[
													{name:'Org_id', type:'int'},
													{name:'Org_Name', type:'string'}
												],
												key:'Org_id',
												sortInfo:{
													field:'Org_Name'
												},
												url:C_ORG_LIST
											}),
											tabIndex:this.tabIndex + 30,
											tpl:new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{Org_Name}',
												'</div></tpl>'
											),
											trigger1Class:'x-form-search-trigger',
											triggerAction:'none',
											valueField:'Org_id',
											width:500,
											xtype:'swbaseremotecombo'
										},
										{
											fieldLabel:'Врач',
											hiddenName:'MedStaffFact_aid',
											id:this.id + 'ESecEF_MedStaffFactAnatomCombo',
											listWidth:650,
											parentElementId:this.id + 'ESecEF_LpuSectionAnatomCombo',
											tabIndex:this.tabIndex + 31,
											width:500,
											xtype:'swmedstafffactglobalcombo'
										},
										{
											checkAccessRights: true,
											fieldLabel:'Осн. патологоанат-й диагноз',
											hiddenName:'Diag_aid',
											id:that.id + 'ESecEF_DiagAnatomCombo',
											tabIndex:this.tabIndex + 32,
											width:500,
											xtype:'swdiagcombo'
										}
									]
								},
								{
									border:false,
									height:150,
									id:that.id + 'ESecEF_AnatomDiagPanel',
									isLoaded:false,
									layout:'border',
									// style: 'margin-left: 165px; margin-right: 0.5em; padding-bottom: 4px;',

									items:[ new sw.Promed.ViewFrame({
										actions:[
											{name:'action_add', handler:function () {
												this.openEvnDiagPSEditWindow('add', 'die');
											}.createDelegate(this)},
											{name:'action_edit', handler:function () {
												this.openEvnDiagPSEditWindow('edit', 'die');
											}.createDelegate(this)},
											{name:'action_view', handler:function () {
												this.openEvnDiagPSEditWindow('view', 'die');
											}.createDelegate(this)},
											{name:'action_delete', handler:function () {
												this.deleteEvent('EvnDiagPSDie');
											}.createDelegate(this), tooltip:'Удалить диагноз из списка'},
											{name:'action_refresh', disabled:true, hidden:true},
											{name:'action_print', disabled:true, hidden:true}
										],
										autoLoadData:false,
										border:false,
										dataUrl:'/?c=EvnDiag&m=loadEvnDiagPSGrid',
										id:that.id + 'ESecEF_AnatomDiagGrid',
										region:'center',
										stringfields:[
											{name:'EvnDiagPS_id', type:'int', header:'ID', key:true},
											{name:'EvnDiagPS_pid', type:'int', hidden:true},
											{name:'Diag_id', type:'int', hidden:true},
											{name:'DiagSetClass_id', type:'int', hidden:true},
											{name:'DiagSetPhase_id', type:'int', hidden:true},
											{name:'DiagSetType_id', type:'int', hidden:true},
											{name:'EvnDiagPS_PhaseDescr', type:'string', hidden:true},
											{name:'EvnDiagPS_setTime', type:'string', hidden:true},
											{name:'Person_id', type:'int', hidden:true},
											{name:'PersonEvn_id', type:'int', hidden:true},
											{name:'Server_id', type:'int', hidden:true},
											{name:'RecordStatus_Code', type:'int', hidden:true},
											{name:'EvnDiagPS_setDate', type:'date', format:'d.m.Y', header:'Дата', width:90},
											{name:'DiagSetClass_Name', type:'string', header:'Вид диагноза', width:200},
											{name:'Diag_Code', type:'string', header:'Код диагноза', width:100},
											{name:'Diag_Name', type:'string', header:'Диагноз', id:'autoexpand'}
										],
										style:'margin-bottom: 0.5em;',
										title:'Сопутствующие патологоанатомические диагнозы',
										toolbar:true
									})]
								}
							]
						}),
						new sw.Promed.Panel({
							border:true,
							collapsible:true,
							height:125,
							id:'ESecEF_EvnDiagPSPanel',
							isLoaded:false,
							layout:'border',
							listeners:{
								'expand':function (panel) {
									if (panel.isLoaded === false) {
										panel.isLoaded = true;
										panel.findById('ESecEF_EvnDiagPSGrid').getStore().load({
											params:{
												'class':'EvnDiagPSSect',
												EvnDiagPS_pid:this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style:'margin-bottom: 0.5em;',
							title:'3. Сопутствующие диагнозы',
							items:[ new Ext.grid.GridPanel({
								autoExpandColumn:'autoexpand_diag_sect',
								autoExpandMin:100,
								border:false,
								columns:[
									{
										dataIndex:'EvnDiagPS_setDate',
										header:'Дата',
										hidden:false,
										renderer:Ext.util.Format.dateRenderer('d.m.Y'),
										resizable:false,
										sortable:true,
										width:100
									},
									{
										dataIndex:'DiagSetClass_Name',
										header:'Вид диагноза',
										hidden:false,
										resizable:true,
										sortable:true,
										width:200
									},
									{
										dataIndex:'Diag_Code',
										header:'Код диагноза',
										hidden:false,
										resizable:true,
										sortable:true,
										width:100
									},
									{
										dataIndex:'Diag_Name',
										header:'Диагноз',
										hidden:false,
										id:'autoexpand_diag_sect',
										resizable:true,
										sortable:true
									}
								],
								frame:false,
								height:200,
								id:'ESecEF_EvnDiagPSGrid',
								keys:[
									{
										key:[
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
										fn:function (inp, e) {
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
														if (!this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && this.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
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
															var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
															var isSamara = (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara');

															if (!isPskov && !isSamara && !base_form.findField('Mes_id').disabled) {
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
										scope:this,
										stopEvent:true
									}
								],
								listeners:{
									'rowdblclick':function (grid, number, obj) {
										this.openEvnDiagPSEditWindow('edit', 'sect');
									}.createDelegate(this)
								},
								loadMask:true,
								region:'center',
								sm:new Ext.grid.RowSelectionModel({
									listeners:{
										'rowselect':function (sm, rowIndex, record) {
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
										}
									}
								}),
								stripeRows:true,
								store:new Ext.data.Store({
									autoLoad:false,
									baseParams:{
										'class':'EvnDiagPSSect'
									},
									listeners:{
										'load':function (store, records, index) {
											if (store.getCount() == 0) {
												LoadEmptyRow(this.findById('ESecEF_EvnDiagPSGrid'));
											}

											// this.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
											// this.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader:new Ext.data.JsonReader({
										id:'EvnDiagPS_id'
									}, [
										{
											mapping:'EvnDiagPS_id',
											name:'EvnDiagPS_id',
											type:'int'
										},
										{
											mapping:'EvnDiagPS_pid',
											name:'EvnDiagPS_pid',
											type:'int'
										},
										{
											mapping:'Person_id',
											name:'Person_id',
											type:'int'
										},
										{
											mapping:'PersonEvn_id',
											name:'PersonEvn_id',
											type:'int'
										},
										{
											mapping:'Server_id',
											name:'Server_id',
											type:'int'
										},
										{
											mapping:'Diag_id',
											name:'Diag_id',
											type:'int'
										},
										{
											mapping:'DiagSetPhase_id',
											name:'DiagSetPhase_id',
											type:'int'
										},
										{
											mapping:'EvnDiagPS_PhaseDescr',
											name:'EvnDiagPS_PhaseDescr',
											type:'string'
										},
										{
											mapping:'DiagSetClass_id',
											name:'DiagSetClass_id',
											type:'int'
										},
										{
											mapping:'DiagSetType_id',
											name:'DiagSetType_id',
											type:'int'
										},
										{
											mapping:'EvnDiagPS_setTime',
											name:'EvnDiagPS_setTime',
											type:'string'
										},
										{
											dateFormat:'d.m.Y',
											mapping:'EvnDiagPS_setDate',
											name:'EvnDiagPS_setDate',
											type:'date'
										},
										{
											mapping:'DiagSetClass_Name',
											name:'DiagSetClass_Name',
											type:'string'
										},
										{
											mapping:'Diag_Code',
											name:'Diag_Code',
											type:'string'
										},
										{
											mapping:'Diag_Name',
											name:'Diag_Name',
											type:'string'
										}
									]),
									url:'/?c=EvnDiag&m=loadEvnDiagPSGrid'
								}),
								tbar:new sw.Promed.Toolbar({
									buttons:[
										{
											handler:function () {
												this.openEvnDiagPSEditWindow('add', 'sect');
											}.createDelegate(this),
											iconCls:'add16',
											text:'Добавить'
										},
										{
											handler:function () {
												this.openEvnDiagPSEditWindow('edit', 'sect');
											}.createDelegate(this),
											iconCls:'edit16',
											text:'Изменить'
										},
										{
											handler:function () {
												this.openEvnDiagPSEditWindow('view', 'sect');
											}.createDelegate(this),
											iconCls:'view16',
											text:'Просмотр'
										},
										{
											handler:function () {
												this.deleteEvent('EvnDiagPS');
											}.createDelegate(this),
											iconCls:'delete16',
											text:'Удалить'
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
								'expand': function(panel) {
									if ( panel.isLoaded === false ) {
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

										var grid = this.findById('ESecEF_EvnUslugaGrid');

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

												var action = 'add';

												if ( e.getKey() == Ext.EventObject.F3 ) {
													action = 'view';
												}
												else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
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

												if ( e.shiftKey == false ) {
													if ( this.action != 'view' ) {
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
									'rowdblclick': function(grid, number, obj) {
										this.openEvnUslugaEditWindow('edit');
									}.createDelegate(this)
								},
								loadMask: true,
								region: 'center',
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function(sm, rowIndex, record) {
											var access_type = 'view';
											var id = null;
											var evnclass_sysnick = null;
											var selected_record = sm.getSelected();
											var toolbar = this.findById('ESecEF_EvnUslugaGrid').getTopToolbar();

											if ( selected_record ) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnUsluga_id');
												evnclass_sysnick = selected_record.get('EvnClass_SysNick');
											}

											toolbar.items.items[1].disable();
											toolbar.items.items[3].disable();

											if ( id ) {
												toolbar.items.items[2].enable();

												if ( this.action != 'view' /*&& access_type == 'edit'*/ ) {
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
										'load': function(store, records, index) {
											if ( store.getCount() == 0 ) {
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
												handler: function() {
													this.openEvnUslugaEditWindow('addOper');
												}.createDelegate(this),
												text: 'Добавить операцию'
											}, {
												handler: function() {
													this.openEvnUslugaEditWindow('add');
												}.createDelegate(this),
												text: 'Добавить общую услугу'
											}]
										}
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
							border:true,
							collapsible:true,
							height:125,
							id:'ESecEF_EvnSectionNarrowBedPanel',
							isLoaded:false,
							hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara'),
							layout:'border',
							listeners:{
								'expand':function (panel) {
									if (panel.isLoaded === false) {
										panel.isLoaded = true;
										panel.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().load({
											params:{
												EvnSectionNarrowBed_pid:this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
											}
										});
									}

									panel.doLayout();
								}.createDelegate(this)
							},
							style:'margin-bottom: 0.5em;',
							title:'5. Профиль коек',
							items:[ new Ext.grid.GridPanel({
								autoExpandColumn:'autoexpand_evn_sect_nb',
								autoExpandMin:100,
								border:false,
								columns:[
									{
										dataIndex:'LpuSectionProfile_Name',
										header:'Профиль',
										hidden:false,
										id:'autoexpand_evn_sect_nb',
										resizable:true,
										sortable:true
									},
									{
										dataIndex:'EvnSectionNarrowBed_setDate',
										header:'Поступление',
										hidden:false,
										renderer:Ext.util.Format.dateRenderer('d.m.Y'),
										resizable:false,
										sortable:true,
										width:100
									},
									{
										dataIndex:'EvnSectionNarrowBed_disDate',
										header:'Выписка',
										hidden:false,
										renderer:Ext.util.Format.dateRenderer('d.m.Y'),
										resizable:false,
										sortable:true,
										width:100
									}
								],
								frame:false,
								height:200,
								id:'ESecEF_EvnSectionNarrowBedGrid',
								keys:[
									{
										key:[
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
										fn:function (inp, e) {
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
															var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
															var isSamara = (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara');

															if (!isPskov && !isSamara && !base_form.findField('Mes_id').disabled) {
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
															var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
															var isSamara = (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara');

															if (!isPskov && !isSamara && !base_form.findField('Mes_id').disabled) {
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
										scope:this,
										stopEvent:true
									}
								],
								listeners:{
									'rowdblclick':function (grid, number, obj) {
										this.openEvnSectionNarrowBedEditWindow('edit');
									}.createDelegate(this)
								},
								loadMask:true,
								region:'center',
								sm:new Ext.grid.RowSelectionModel({
									listeners:{
										'rowselect':function (sm, rowIndex, record) {
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
								stripeRows:true,
								store:new Ext.data.Store({
									autoLoad:false,
									listeners:{
										'load':function (store, records, index) {
											if (store.getCount() == 0) {
												LoadEmptyRow(this.findById('ESecEF_EvnSectionNarrowBedGrid'));
											}

											// this.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
											// this.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader:new Ext.data.JsonReader({
										id:'EvnSectionNarrowBed_id'
									}, [
										{
											mapping:'EvnSectionNarrowBed_id',
											name:'EvnSectionNarrowBed_id',
											type:'int'
										},
										{
											mapping:'EvnSectionNarrowBed_pid',
											name:'EvnSectionNarrowBed_pid',
											type:'int'
										},
										{
											mapping:'PersonEvn_id',
											name:'PersonEvn_id',
											type:'int'
										},
										{
											mapping:'Server_id',
											name:'Server_id',
											type:'int'
										},
										{
											mapping:'EvnSectionNarrowBed_pid',
											name:'EvnSectionNarrowBed_pid',
											type:'int'
										},
										{
											mapping:'LpuSection_id',
											name:'LpuSection_id',
											type:'int'
										},
										{
											dateFormat:'d.m.Y',
											mapping:'EvnSectionNarrowBed_setDate',
											name:'EvnSectionNarrowBed_setDate',
											type:'date'
										},
										{
											mapping:'EvnSectionNarrowBed_setTime',
											name:'EvnSectionNarrowBed_setTime',
											type:'string'
										},
										{
											dateFormat:'d.m.Y',
											mapping:'EvnSectionNarrowBed_disDate',
											name:'EvnSectionNarrowBed_disDate',
											type:'date'
										},
										{
											mapping:'EvnSectionNarrowBed_disTime',
											name:'EvnSectionNarrowBed_disTime',
											type:'string'
										},
										{
											mapping:'LpuSectionProfile_Name',
											name:'LpuSectionProfile_Name',
											type:'string'
										}
									]),
									url:'/?c=EvnSectionNarrowBed&m=loadEvnSectionNarrowBedGrid'
								}),
								tbar:new sw.Promed.Toolbar({
									buttons:[
										{
											handler:function () {
												this.openEvnSectionNarrowBedEditWindow('add');
											}.createDelegate(this),
											iconCls:'add16',
											text:'Добавить'
										},
										{
											handler:function () {
												this.openEvnSectionNarrowBedEditWindow('edit');
											}.createDelegate(this),
											iconCls:'edit16',
											text:'Изменить'
										},
										{
											handler:function () {
												this.openEvnSectionNarrowBedEditWindow('view');
											}.createDelegate(this),
											iconCls:'view16',
											text:'Просмотр'
										},
										{
											handler:function () {
												this.deleteEvent('EvnSectionNarrowBed');
											}.createDelegate(this),
											iconCls:'delete16',
											text:'Удалить'
										}
									]
								})
							})]
						}),
						new sw.Promed.Panel({
							border:true,
							collapsible:true,
							id:this.id + '_SpecificsPanel',
							isExpanded:false,
							layout:'border',
							listeners:{
								'expand':function (panel) {
									var base_form = this.findById('EvnSectionEditForm').getForm();
									if(this.editPersonNewBorn==null){
										Ext.Ajax.request({
											callback:function (options, success, response) {
												if (success) {
													var response_obj = Ext.util.JSON.decode(response.responseText)[0];
													parentWin.editPersonNewBorn = response_obj.editPersonNewBorn;
													parentWin.onSpecificsExpand(panel);
												}
												else {
													sw.swMsg.alert('Ошибка', 'При загрузке сведений из карты диспансерного учета произошли ошибки');
												}
											},
											params:{
												EvnPS_id:parentWin.EvnPS_id,
												Person_id:base_form.findField('Person_id').getValue()
											},
											url:'/?c=PersonNewBorn&m=chekPersonNewBorn',
											method:'POST'
										});
									}else{
										this.onSpecificsExpand(panel);
									}
								}.createDelegate(this)
							},
							split:true,
							style:'margin-bottom: 0.5em;',
							title:'6. Специфика',
							items:[
								{
									autoScroll:true,
									border:false,
									collapsible:false,
									wantToFocus:false,
									id:this.id + '_SpecificsTree',
									listeners:{
										'bodyresize': function(tree) {
											setTimeout(function() {parentWin.resizeSpecificForWizardPanel()}, 1);
										}.createDelegate(this),
										'beforeload': function(node) {
											var tree = this.findById(this.id + '_SpecificsTree');
											var base_form = this.findById('EvnSectionEditForm').getForm();

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
											tree.getLoader().baseParams.createCategoryMethod = "Ext.getCmp('"+this.getId()+"').createPersonPregnancyCategory";
											tree.getLoader().baseParams.deleteCategoryMethod = "Ext.getCmp('"+this.getId()+"').deletePersonPregnancyCategory";
											tree.getLoader().baseParams.allowCreateButton = (this.action != 'view');
											tree.getLoader().baseParams.allowDeleteButton = (this.action != 'view');
										}.createDelegate(this),
										'click':function (node, e) {
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
											switch (node.attributes.value) {
												// Сведения об аборте
												case 'abort_data':
													if (!this.findById('ESecEF_EvnAbortForm')) {
														// Добавляем форму редактирования сведений об аборте
														this.specificsFormsPanel.add({
															autoHeight:true,
															border:false,
															frame:false,
															// height: 200,
															hidden:true,
															id:'ESecEF_EvnAbortForm',
															isLoaded:false,
															labelWidth:150,
															layout:'form',
															xtype:'panel',
															items:[
																{
																	name:'EvnAbort_id',
																	value:0,
																	xtype:'hidden'
																},
																{
																	fieldLabel:'Дата аборта',
																	format:'d.m.Y',
																	name:'EvnAbort_setDate',
																	plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
																	width:100,
																	xtype:'swdatefield'
																},
																{
																	comboSubject:'AbortType',
																	fieldLabel:'Тип аборта',
																	hiddenName:'AbortType_id',
																	width:300,
																	xtype:'swcommonsprcombo'
																},
																{
																	allowDecimals:false,
																	allowNegative:false,
																	fieldLabel:'Срок беременности',
																	maxValue:28,
																	minValue:0,
																	name:'EvnAbort_PregSrok',
																	width:100,
																	xtype:'numberfield'
																},
																{
																	allowDecimals:false,
																	allowNegative:false,
																	fieldLabel:'Которая беременность',
																	maxValue:99,
																	minValue:1,
																	name:'EvnAbort_PregCount',
																	width:100,
																	xtype:'numberfield'
																},
																{
																	comboSubject:'AbortPlace',
																	fieldLabel:'Место проведения',
																	hiddenName:'AbortPlace_id',
																	width:100,
																	xtype:'swcommonsprcombo'
																},
																{
																	comboSubject:'YesNo',
																	fieldLabel:'Медикаментозный',
																	hiddenName:'EvnAbort_IsMed',
																	width:100,
																	xtype:'swcommonsprcombo'
																},
																{
																	comboSubject:'YesNo',
																	fieldLabel:'Обследована на ВИЧ',
																	hiddenName:'EvnPLAbort_IsHIV',
																	width:100,
																	xtype:'swcommonsprcombo'
																},
																{
																	comboSubject:'YesNo',
																	fieldLabel:'Наличие ВИЧ-инфекции',
																	hiddenName:'EvnPLAbort_IsInf',
																	width:100,
																	xtype:'swcommonsprcombo'
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
																	if (item.xtype && item.xtype.toString().inlist([ 'hidden', 'swcommonsprcombo', 'textfield', 'swdatefield', 'swtimefield' ])) {
																		base_form.add(item);
																	}
																});
															}
															else if (item.xtype && item.xtype.toString().inlist([ 'hidden', 'swcommonsprcombo', 'textfield', 'swdatefield', 'swtimefield' ])) {
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
															autoHeight:true,
															border:false,
															frame:false,
															//height: 400,
															hidden:true,
															id:'ESecEF_PersonChildForm',
															isLoaded:false,
															labelWidth:150,
															layout:'form',
															xtype:'panel',
															items:[
																{
																	name:'PersonNewBorn_id',
																	value:0,
																	xtype:'hidden'
																},{
																	name:'BirthSpecStac_id',
																	value:0,
																	xtype:'hidden'
																},win.tabPanel
															]
														});

														// прогрузка справочников на фрейме специфики
														var panel = this.findById('ESecEF_PersonChildForm'); // получаем панель, на которой находятся комбики
														var lists = this.getComboLists(panel); // получаем список комбиков
														this.loadDataLists({}, lists, true); // прогружаем все справочники (третий параметр noclose - без операций над формой)
														var formFieldTypes = [ 'swcombo', 'numberfield', 'checkbox', 'hidden', 'swcommonsprcombo', 'textfield', 'swdatefield', 'swtimefield'];
														var addFieldsRecursive = function (item) {
															if (item.items) {
																item.items.each(addFieldsRecursive);
															}
															else if (item.xtype && item.xtype.toString().inlist(formFieldTypes)) {
																base_form.add(item);
															}
														};
														this.findById('ESecEF_PersonChildForm').items.each(addFieldsRecursive);

														//focusing viewframes

													}

													this.findById('ESecEF_PersonChildForm').show();
													this.specificsPanel.setHeight(660);
													this.specificsFormsPanel.doLayout();
													if (!this.findById('ESecEF_PersonChildForm').isLoaded) {
														var loadMask = new Ext.LoadMask(this.specificsFormsPanel.getEl(), {msg:"Загрузка данных..."});
														loadMask.show();

														// Загрузка данных с сервера в форму и гриды
														Ext.Ajax.request({
															callback:function (options, success, response) {
																loadMask.hide();
																var base_form = this.findById('EvnSectionEditForm').getForm();
																// Загружаем списки измерений массы и длины


																if (success) {
																	this.findById('ESecEF_PersonChildForm').isLoaded = true;
																	var response_obj = Ext.util.JSON.decode(response.responseText);

																	if (response_obj.length > 0) {
																		response_obj = response_obj[0];
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
																		apgarGrid.getStore().load({params:{PersonNewBorn_id:base_form.findField('PersonNewBorn_id').getValue()}});
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
																		base_form.findField('NewBornWardType_id').setValue(response_obj.NewBornWardType_id);
																		base_form.findField('PersonNewBorn_IsBleeding').setValue(response_obj.PersonNewBorn_IsBleeding);
																		base_form.findField('PersonNewBorn_IsAudio').setValue(response_obj.PersonNewBorn_IsAudio);
																		base_form.findField('PersonNewBorn_IsNeonatal').setValue(response_obj.PersonNewBorn_IsNeonatal);
																		base_form.findField('PersonNewBorn_IsBCG').setValue(response_obj.PersonNewBorn_IsBCG);
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
																			{NewbornApgarRate_id:-swGenTempId(apgarGrid.getStore()),NewbornApgarRate_Time:1,RecordStatus_Code:0},
																			{NewbornApgarRate_id:-swGenTempId(apgarGrid.getStore()),NewbornApgarRate_Time:5,RecordStatus_Code:0},
																			{NewbornApgarRate_id:-swGenTempId(apgarGrid.getStore()),NewbornApgarRate_Time:10,RecordStatus_Code:0},
																			{NewbornApgarRate_id:-swGenTempId(apgarGrid.getStore()),NewbornApgarRate_Time:15,RecordStatus_Code:0}
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
															params:{
																Person_id:base_form.findField('Person_id').getValue()
															},
															url:'/?c=PersonNewBorn&m=loadPersonNewBornData'
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
											    case 'onko_data': 
											        var self = this;
											        var loadOnko = function () {
											            self.specificsPanel.setHeight(2500); 
											            self.specificsFormsPanel.doLayout();

											            var form = onko.getForm();
											            form.reset();

											            form.findField('Evn_pid').setValue(base_form.findField('EvnSection_id').getValue());
											            form.findField('Person_id').setValue(base_form.findField('Person_id').getValue());
											            form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());

											            form.load({
											                url: '/?c=EvnSection&m=loadMorbusOnko',
											                params: {
											                    MorbusOnko_pid: base_form.findField('EvnSection_id').getValue()
											                },
											                //waitMsg: 'Загрузка онкологии'
											                success: function (form, action) {
											                    onko.isLoaded = true;
											                    var morbus_id = form.findField('Morbus_id').getValue(),
                                                                    evn_pid = form.findField('Evn_pid').getValue();

											                    (function (array) {
											                        Ext.each(array, function (obj) {
											                            self.findById(obj.id)
                                                                            .getStore()
                                                                            .load({
                                                                                params: {
                                                                                    'class': obj.cl,
                                                                                    'byMorbus': 1,
                                                                                    'Morbus_id': morbus_id,
                                                                                    'EvnEdit_id': evn_pid
                                                                                }
                                                                            });
											                        });
											                    })([{ id: 'ESecEF_OnkoChemGrid', cl: 'EvnUslugaOnkoChem' },
                                                                    { id: 'ESecEF_OnkoBeamGrid', cl: 'EvnUslugaOnkoBeam' },
                                                                    { id: 'ESecEF_OnkoGormunGrid', cl: 'EvnUslugaOnkoGormun' },
                                                                    { id: 'ESecEF_OnkoSurgGrid', cl: 'EvnUslugaOnkoSurg' }]);

											                    self.findById('ESecEF_OnkoBasePSGrid')
                                                                    .getStore()
                                                                    .load({
                                                                        params: {
                                                                            'Morbus_id': morbus_id,
                                                                            'Evn_id': evn_pid
                                                                        }
                                                                    });
											                }
											            });
                                                    }

											        var onko = this.findById('ESecEF_EvnOnkoForm') || this.specificsFormsPanel.add(this.onkoForm);

											        this.specificsFormsPanel.show();

											        if (!onko.isLoaded) {
											            mybase = base_form;
											            if (base_form.findField('EvnSection_id').getValue() === "0") {
											                this.doSave({
											                    openChildWindow: function () {
											                        loadOnko();
											                        onko.isLoaded = true;
											                    }
											                });
											            } else {
											                loadOnko();
											                onko.isLoaded = true;
											            }											       
											        }

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
														switch(node.attributes.object) {
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
															status = categoryData?categoryData.status:0;
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
										}.createDelegate(this)
									},
									keys:[
										{
											key:[
												Ext.EventObject.TAB
											],
											fn:function (inp, e) {
												var form = parentWin.findById('EvnSectionEditForm').getForm();
												if (e.shiftKey) {
													//перескакиваем на предыдуший контрол
													if (!parentWin.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
														parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getView().focusRow(0);
														parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getSelectionModel().selectFirstRow();
													} else {
														if (!parentWin.findById('ESecEF_EvnDiagPSPanel').collapsed && parentWin.findById('ESecEF_EvnDiagPSGrid').getStore().getCount() > 0) {
															parentWin.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
															parentWin.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
														} else {
															if (!parentWin.findById('ESecEF_EvnSectionPanel').collapsed && this.action != 'view') {
																var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
																var isSamara = (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara');

																if (!isPskov && !isSamara && !form.findField('Mes_id').disabled) {
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
											scope:this,
											stopEvent:true
										}
									],
									loader:new Ext.tree.TreeLoader({
										dataUrl:'/?c=Specifics&m=getSpecificsTree'
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
									width:200,
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
						})
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
		this.findById(this.id + '_LpuSectionCombo').addListener('change', function (combo, newValue, oldValue) {
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
					var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
					var isSamara = (getGlobalOptions().region && getGlobalOptions().region.nick == 'samara');

					if (!isPskov && !isSamara && !base_form.findField('Mes_id').disabled) {
						base_form.findField('Mes_id').focus(true);
					}
					else if (!parentWin.findById(that.id + 'ESecEF_EvnLeavePanel').collapsed && !base_form.findField('LeaveType_id').disabled) {
						base_form.findField('LeaveType_id').focus();
					}
					else if (!parentWin.findById('ESecEF_EvnDiagPSPanel').collapsed && parentWin.findById('ESecEF_EvnDiagPSGrid').getStore().getCount() > 0) {
						parentWin.findById('ESecEF_EvnDiagPSGrid').getView().focusRow(0);
						parentWin.findById('ESecEF_EvnDiagPSGrid').getSelectionModel().selectFirstRow();
					}
					else if (!parentWin.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed && parentWin.findById('ESecEF_EvnSectionNarrowBedGrid').getStore().getCount() > 0) {
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
		this.findById(this.id + '_DiagCombo').addListener('change', function (c, nv, ov) {
			parentWin.onSpecificsExpand(parentWin.specificsPanel, true);
			if(parentWin.childPS){
				parentWin.specificsPanel.expand();
			}else{
				parentWin.specificsPanel.collapse();
			}
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
			}
			var node = tree.getRootNode().firstChild;
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
			var func = function(node) {
				while (node) {
					switch (node.id) {
						case 'born_data':
							if (olderThan365Days||parentWin.editPersonNewBorn==0) {
								node.disable();
							} else {
								node.enable();

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
			var loadTree = function(forceLoad) {
				if (!parentWin.treeLoaded || forceLoad) {
					parentWin.treeLoaded = true;
					tree.getLoader().load(tree.getRootNode(), function(){
						func(tree.getRootNode().firstChild);
					});
				} else {
					func(tree.getRootNode().firstChild);
				}
			};
			if (isOnkoDiag && isDebug()) {
				panel.hide();
			} else {
				if (!forbidResetSpecific) {
					panel.show();
					tree.fireEvent('click', tree.getRootNode());
					tree.setWidth(200);
					panel.doLayout();
				}

				if (isPregnancyDiag) {
					parentWin.getPregnancyPersonRegister(function() {
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
	layout:'border',
	listeners:{
		'hide':function (win) {
			win.onHide({
				EvnUslugaGridIsModified: win.EvnUslugaGridIsModified
			});
		},
		'maximize':function (win) {
			win.findById('ESecEF_EvnSectionPanel').doLayout();
			win.findById('ESecEF_EvnUslugaPanel').doLayout();
			win.findById( win.id + 'ESecEF_EvnLeavePanel').doLayout();
			win.findById('ESecEF_EvnDiagPSPanel').doLayout();
			win.findById('ESecEF_EvnSectionNarrowBedPanel').doLayout();
			if (!win.specificsPanel.hidden) {
				win.specificsPanel.doLayout();
				win.specificsPanel.collapsed = true;
				alert('s');
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
		'restore':function (win) {
			win.fireEvent('maximize', win);
		}
	},
	leaveTypeFilter: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		var LeaveType_id = base_form.findField('LeaveType_id').getValue();
		var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');

		var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();
		var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();

		base_form.findField('LeaveType_id').clearFilter();
		base_form.findField('LeaveType_id').lastQuery = '';

		base_form.findField('LeaveType_id').getStore().filterBy(function (rec) {
			if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(EvnSection_disDate) && rec.get('LeaveType_begDate') > EvnSection_disDate) {
				return false;
			}
			if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && rec.get('LeaveType_endDate') < EvnSection_setDate) {
				return false;
			}
		});

		if ( !Ext.isEmpty(LeaveType_id) ) {
			var index = base_form.findField('LeaveTypeFed_id').getStore().findBy(function(rec) {
				return (rec.get('LeaveType_id') == LeaveType_id);
			});

			if ( index == -1 ) {
				base_form.findField('LeaveTypeFed_id').clearValue();
				base_form.findField('LeaveTypeFed_id').fireEvent('change', base_form.findField('LeaveTypeFed_id'));
			}
		}
	},
	leaveTypeFedFilter: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya') {
			var LeaveTypeFed_id = base_form.findField('LeaveTypeFed_id').getValue();

			var fedIdList = new Array();

			// Получаем список доступных исходов из федерального справочника
			base_form.findField('LeaveType_id').getStore().each(function(rec) {
				if ( !Ext.isEmpty(rec.get('LeaveType_fedid')) && !rec.get('LeaveType_fedid').toString().inlist(fedIdList) ) {
					fedIdList.push(rec.get('LeaveType_fedid').toString());
				}
			});

			base_form.findField('LeaveTypeFed_id').clearFilter();
			base_form.findField('LeaveTypeFed_id').lastQuery = '';

			var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');

			if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
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
							&& !(LpuUnitType_SysNick.inlist([ 'dstac', 'hstac' ]) && rec.get('LeaveType_Code').toString().inlist([ '207', '208' ]))
						);
					});
				}
			}
			else {
				base_form.findField('LeaveTypeFed_id').getStore().filterBy(function (rec) {
					return (rec.get('LeaveType_id').toString().inlist(fedIdList));
				});
			}

			if ( !Ext.isEmpty(LeaveTypeFed_id) ) {
				var index = base_form.findField('LeaveTypeFed_id').getStore().findBy(function(rec) {
					return (rec.get('LeaveType_id') == LeaveTypeFed_id);
				});

				if ( index == -1 ) {
					base_form.findField('LeaveTypeFed_id').clearValue();
					base_form.findField('LeaveTypeFed_id').fireEvent('change', base_form.findField('LeaveTypeFed_id'));
				}
			}
		}
	},
	leaveCauseFilter: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' ) {
			var oldValue = base_form.findField('LeaveCause_id').getValue();

			base_form.findField('LeaveCause_id').clearFilter();
			base_form.findField('LeaveCause_id').lastQuery = '';

			switch ( base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick') ) {
				case 'stac': // Круглосуточный стационар
					base_form.findField('LeaveCause_id').getStore().filterBy(function (rec) {
						return (!rec.get('LeaveCause_Code').inlist([ 210, 211, 212 ]));
					});
				break;

				default:
					base_form.findField('LeaveCause_id').getStore().filterBy(function (rec) {
						return (rec.get('LeaveCause_Code').inlist([ 1, 6, 7, 27, 28, 29, 210, 211, 212 ]));
					});
				break;
			}
			
			var index = base_form.findField('LeaveCause_id').getStore().findBy(function (rec) {
				return (rec.get('LeaveCause_id') == oldValue);
			});
			
			if ( index == -1 ) {
				base_form.findField('LeaveCause_id').clearValue();
			}
			
			if ( base_form.findField('LeaveCause_id').getStore().getCount() == 1 ) {
				base_form.findField('LeaveCause_id').setValue(base_form.findField('LeaveCause_id').getStore().getAt(0).get('LeaveCause_id'));
			}
		}
	},
	resultDeseaseFilter: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya') {
			var oldValue = base_form.findField('ResultDesease_id').getValue();
			base_form.findField('ResultDesease_id').clearFilter();
			base_form.findField('ResultDesease_id').lastQuery = '';
			if (base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 2) {
				// круглосуточный
				base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
					if (rec.get('ResultDesease_Code') > 100 && rec.get('ResultDesease_Code') < 200 ) {
						return true;
					}
					else {
						return false;
					}
				});
			} else {
				base_form.findField('ResultDesease_id').getStore().filterBy(function (rec) {
					if (rec.get('ResultDesease_Code') > 200 && rec.get('ResultDesease_Code') < 300 ) {
						return true;
					}
					else {
						return false;
					}
				});
			}
			
			var index = base_form.findField('ResultDesease_id').getStore().findBy(function (rec) {
				if (rec.get('ResultDesease_id') == oldValue) {
					return true;
				}
				else {
					return false;
				}
			});
			
			if (index == -1) {
				base_form.findField('ResultDesease_id').clearValue();
			}
		}
	},
	checkLpuUnitType:function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		if (base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 2) {
			base_form.findField('EvnSection_IsAdultEscort').showContainer();
		} else {
			base_form.findField('EvnSection_IsAdultEscort').hideContainer();
			base_form.findField('EvnSection_IsAdultEscort').setValue(1);
		}
		if(getGlobalOptions().region.nick == 'astra'){
			//Дневной стационар при стационаре или дневной стационар при поликлинике (АПУ)
			if ((base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 3) || ((base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_Code') == 5))) {
				base_form.findField('EvnSection_IsMeal').setContainerVisible(true);
			} else {
				base_form.findField('EvnSection_IsMeal').setContainerVisible(false);
			}
		}
		else{
			base_form.findField('EvnSection_IsMeal').setContainerVisible(false);
		}
		this.leaveTypeFedFilter();
		this.leaveCauseFilter();
		this.resultDeseaseFilter();
	},
	loadMes2Combo:function(mes2_id, selectIfOne) {
		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa') {
			var base_form = this.findById('EvnSectionEditForm').getForm();
			var diag_id = base_form.findField('Diag_id').getValue();
			
			if ( !diag_id || Ext.isEmpty(diag_id) ) {
				return false;
			}
			
			base_form.findField('Mes2_id').clearValue();
			base_form.findField('Mes2_id').getStore().removeAll();
			
			base_form.findField('Mes2_id').getStore().load({
				callback:function () {
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
				params:{
					Diag_id:diag_id
				}
			});
		}
	},
	// определение КСГ/КПГ/коэффициента
	loadKSGKPGKOEF: function() {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		
		if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'ufa' ]) ) {
			// запрос, передаём EvnSection_id, Diag_id, LpuSectionProfile_id
			Ext.Ajax.request({
				callback:function (options, success, response) {
					if (response.responseText) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success) {
							base_form.findField('EvnSection_KSG').setValue(result.KSG);
							base_form.findField('EvnSection_KPG').setValue(result.KPG);
							base_form.findField('EvnSection_KOEF').setValue(result.KOEF);
						}						
					}
				}.createDelegate(this),
				params:{
					EvnSection_id: base_form.findField('EvnSection_id').getValue(),
					Diag_id: base_form.findField('Diag_id').getValue(),
					LpuSectionProfile_id: base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id')
				},
				url:'/?c=EvnSection&m=loadKSGKPGKOEF'
			});
		}
	},
	loadMesCombo:function () {
		if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov', 'samara' ]) ) {
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

		var allowBlankMes = (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['samara','ufa','pskov','kareliya']));
		
		base_form.findField('Mes_id').clearValue();
		base_form.findField('Mes_id').getStore().removeAll();

		base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), null);

		if (!diag_id || !evn_section_set_date || !lpu_section_id || !person_id) {
			return false;
		}

		base_form.findField('Mes_id').getStore().load({
			callback:function () {
				var index, record;

				base_form.findField('Mes_id').setAllowBlank(allowBlankMes || Ext.isEmpty(evn_section_dis_date));

				// Записей нет
				if ( base_form.findField('Mes_id').getStore().getCount() == 0 ) {
					base_form.findField('Mes_id').setAllowBlank(true);
				}
				else {
					// Если запись одна
					// Регион не РБ
					if ( base_form.findField('Mes_id').getStore().getCount() == 1 && getGlobalOptions().region.nick != 'ufa' ) {
						index = 0;
					}
					// Запись, соответствующая старому значению
					else if ( !Ext.isEmpty(win.mes_id) ) {
						index = base_form.findField('Mes_id').getStore().findBy(function(rec) {
							return (rec.get('Mes_id') == win.mes_id);
						});
					}

					if ( index >= 0 ) {
						record = base_form.findField('Mes_id').getStore().getAt(index);
					}
				}

				// для Перми: если запись одна и выбрана не по новому условию, то нужно сделать поле необязательным.
				if ( getGlobalOptions().region.nick == 'perm' && base_form.findField('Mes_id').getStore().getCount() == 1 && record.get('MesNewUslovie') == 0 ) {
					base_form.findField('Mes_id').setAllowBlank(true);
				}

				if ( typeof record == 'object' ) {
					base_form.findField('Mes_id').setValue(record.get('Mes_id'));
					base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), record.get('Mes_id'));
				}
			}.createDelegate(this),
			params:{
				 Diag_id: diag_id
				,EvnSection_disDate: Ext.util.Format.date(evn_section_dis_date, 'd.m.Y')
				,EvnSection_setDate: Ext.util.Format.date(evn_section_set_date, 'd.m.Y')
				,LpuSection_id: lpu_section_id
				,Person_id: person_id
				,EvnSection_id: EvnSection_id
			}
		});
	},
	maximizable:true,
	minHeight:550,
	minWidth:800,
	modal:true,
	onCancelAction:function () {
		var wnd = this;
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var evn_section_id = base_form.findField('EvnSection_id').getValue();

		if (wnd.WizardPanel) {
			wnd.WizardPanel.deleteEvnSection = (params.EvnSection_id > 0 && wnd.action == 'add');
			var categories = wnd.WizardPanel.categories;
			var category = null;
			var index = -1;

			var cancelCategory = function() {
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

		if (evn_section_id > 0 && this.action == 'add') {
			// удалить случай движения пациента в стационаре
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg:"Удаление случая движения пациента в стационаре..."});
			loadMask.show();

			Ext.Ajax.request({
				callback:function (options, success, response) {
					loadMask.hide();

					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						var evnSectionData = [{
							EvnSection_id: evn_section_id,
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
					Evn_id: evn_section_id
				},
				url:'/?c=Evn&m=deleteEvn'
			});
		}
		else {
			this.hide();
		}
	},
	onHide:Ext.emptyFn,
	openEvnDiagPSEditWindow:function (action, type) {
		var that = this;
		if (typeof action != 'string' || !action.toString().inlist([ 'add', 'edit', 'view' ])) {
			return false;
		}
		else if (typeof type != 'string' || !type.toString().inlist([ 'die', 'sect' ])) {
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
						openChildWindow:function () {
							this.openEvnDiagPSEditWindow(action, type);
						}.createDelegate(this)
					});
					return false;
				}

				if (!base_form.findField('Diag_id').getValue()) {
					sw.swMsg.alert('Ошибка', 'Не заполнен основной диагноз');
					return false;
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
		getWnd('swEvnDiagPSEditWindow').show(params);
	},
	DataViewStore: function () {
		var ds = this.findById('dataViewDiag').getStore();
		ds.load({
				params:{
				'class':'EvnDiagPSSect',
				DiagSetClass:1,
				EvnDiagPS_pid:this.findById('EvnSectionEditForm').getForm().findField('EvnSection_id').getValue()
				}
			});

	},
	openEvnDiagPSEditWindow2:function (action, type) {
		var that = this;
		if (typeof action != 'string' || !action.toString().inlist([ 'add'])) {
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
		if(type=='sect') {
			
				formMode = 'local';
				grid =  this.findById('dataViewDiag');
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
				data.evnDiagPSData[0].EvnDiagPS_pid=base_form.findField('EvnSection_id').getValue();
				data.evnDiagPSData[0].Person_id=base_form.findField('Person_id').getValue();
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
	openEvnSectionNarrowBedEditWindow:function (action) {
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
				openChildWindow:function () {
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

				record.commit();
			}
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
				if ( base_form.findField('LeaveType_id').getValue() == 5) {
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

		getWnd('swEvnSectionNarrowBedEditWindow').show(params);
	},
	plain:true,
	recountKoikoDni:function () {
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var stat_sutki = false;

		// Если по стат. суткам, то время тоже учитывается
		// var evn_section_dis_date = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_disTime').getValue());
		// var evn_section_set_date = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'), base_form.findField('EvnSection_setTime').getValue());
		// Если по календарным суткам, то время не учитывается
		var evn_section_dis_date = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_disDate').getValue(), 'd.m.Y'), '');
		var evn_section_set_date = getValidDT(Ext.util.Format.date(base_form.findField('EvnSection_setDate').getValue(), 'd.m.Y'), '');
		var index;
		var koiko_dni;
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var record;

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

		base_form.findField('EvnSection_KoikoDni').setValue(koiko_dni);
	},
	resizable:true,
	loadFormFieldsStore:function (elements, options) {
		var base_form = this.findById('EvnSectionEditForm').getForm();

		// функция загрузки справочников для нужных элементов.
		if (elements.length < 1) {
			this.show(options);
		}
		else {
			var params = new Object();
			var sprName = elements.shift();

			base_form.findField(sprName).getStore().removeAll();

			switch (sprName) {
				case 'LpuUnitType_oid':
					params.where = 'where LpuUnitType_Code in (2, 3, 4, 5)';
					break;

				case 'TariffClass_id':
					if (getGlobalOptions().region && getGlobalOptions().region.nick != 'ufa') {
						params.where = 'where TariffClass_Code in (5, 6, 8, 10)';
					}
					break;
			}

			base_form.findField(sprName).getStore().load({
				callback:function () {
					this.loadFormFieldsStore(elements, options);
				}.createDelegate(this),
				params:params
			});
		}
	},
	getLoadMask:function (txt) {
		if (Ext.isEmpty(txt)) {
			txt = 'Подождите...';
		}

		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg:txt});
		}

		return this.loadMask;
	},
	setMKB: function(){
		var parentWin =this
		var base_form = this.findById('EvnSectionEditForm').getForm();
		var sex = parentWin.findById('ESecEF_PersonInformationFrame').getFieldValue('Sex_Code');
		var age = swGetPersonAge(parentWin.findById('ESecEF_PersonInformationFrame').getFieldValue('Person_Birthday'),base_form.findField('EvnSection_setDate').getValue());
		base_form.findField('Diag_id').setMKBFilter(age,sex,true);
	},
	removePersonNewBornFields:function(){
		var base_form = this.findById('EvnSectionEditForm').getForm();
		if(base_form.findField('PersonNewBorn_Weight')){
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
			base_form.findField('NewBornWardType_id').setValue(null);
		}
	},
	
	show:function () {
		var that = this;

		sw.Promed.swEvnSectionEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры');
			return false;
		}
		this.editPersonNewBorn = null;
		that.flbr = false;
		that.NewBorn_Weight = -1;
		this.isTraumaTabGridLoaded = false;
		this.isObservTabGridLoaded = false;
		this.PersonRegister_id = null;
		this.treeLoaded = false;
		this.createdObjects = {};
		this.specificsPanel.collapse();
		this.removePersonNewBornFields();
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

        if (this.findById('ESecEF_EvnOnkoForm')) {
            this.findById('ESecEF_EvnOnkoForm').isLoaded = false; 
            this.findById('ESecEF_EvnOnkoForm').getForm().reset();
            this.specificsFormsPanel.hide();
        }
		this.findById(that.id + 'ESecEF_AnatomDiagPanel').isLoaded = (this.action == 'add');

		this.specificsPanel.hide();
		this.specificsPanel.isExpanded = false;
		delete this.specificsTree.getLoader().baseParams.PersonRegister_id;

		this.restore();
		this.center();
		this.maximize();

		var base_form = this.findById('EvnSectionEditForm').getForm();
		base_form.reset();

		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya') {
			// убираем исход гостиптализации и показываем федеральный спрвочник
			base_form.findField('LeaveType_id').hideContainer();
			base_form.findField('LeaveTypeFed_id').showContainer();

			this.leaveTypeFedFilter();
		}
		else {
			base_form.findField('LeaveTypeFed_id').hideContainer();
			base_form.findField('LeaveType_id').showContainer();
		}

		base_form.findField('LpuUnitType_oid').getStore().clearFilter();
		base_form.findField('LpuSection_aid').getStore().removeAll();
		base_form.findField('MedStaffFact_aid').getStore().removeAll();
		base_form.findField('Diag_id').filterDate = null;

		var isPskov = (getGlobalOptions().region && getGlobalOptions().region.nick == 'pskov');
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');

		this.action = null;
		this.callback = Ext.emptyFn;
		this.evnLeaveSetDT = null;
		this.evnPSSetDT = null;
		this.evnSectionIsFirst = false;
		this.evnSectionIsLast = false;
		this.EvnUslugaGridIsModified = false;
		this.formParams = arguments[0].formParams;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.PregnancyData = null;
		this.specBirthData = {};
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

		if (arguments[0] && arguments[0].ARMType_id) {
			this.ARMType_id = arguments[0].ARMType_id;
		} else if (arguments[0] && arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType_id) {
			this.ARMType_id = arguments[0].userMedStaffFact.ARMType_id;
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

		this.DiagPred_id = arguments[0].DiagPred_id || null;
		this.onChangeLpuSectionWard = arguments[0].onChangeLpuSectionWard || null;
		this.oldLpuSectionWard_id = 0;

		if (arguments[0].EvnLeave_setDT) {
			this.evnLeaveSetDT = arguments[0].EvnLeave_setDT;
		}

		if (arguments[0].EvnPS_setDT) {
			this.evnPSSetDT = arguments[0].EvnPS_setDT;
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

		if (arguments[0].EvnUsluga_rid) {
			this.EvnUsluga_rid = arguments[0].EvnUsluga_rid;
		} else {
			this.EvnUsluga_rid = null;
		}

		var persFrame = this.findById('ESecEF_PersonInformationFrame');
		var parentWin = this;
		var specTreeLoadMask = new Ext.LoadMask(parentWin.specificsTree.getEl(), {msg:'Ожидание загрузки панели персональной информации...'});
		specTreeLoadMask.show();
		persFrame.load({
			Person_id:(arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday:(arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname:(arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname:(arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname:(arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback:function () {
				var field = base_form.findField('EvnSection_setDate');
				clearDateAfterPersonDeath('personpanelid', 'ESecEF_PersonInformationFrame', field);
				field = base_form.findField('EvnSection_disDate');
				clearDateAfterPersonDeath('personpanelid', 'ESecEF_PersonInformationFrame', field);
				parentWin.onSpecificsExpand(parentWin.specificsPanel);
				parentWin.setMKB();
				base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'));
				specTreeLoadMask.hide();
			}
			
		});


		if (this.WizardPanel) {
			this.WizardPanel.resetCurrentCategory(true);
			this.WizardPanel.init();
			this.WizardPanel.PrintResultButton.hide();
			this.WizardPanel.setReadOnly(this.action == 'view');
		}

		base_form.setValues(this.formParams);
		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya' && !Ext.isEmpty(base_form.findField('LeaveTypeFed_id').getValue())) {
			base_form.findField('LeaveTypeFed_id').fireEvent('change', base_form.findField('LeaveTypeFed_id'), base_form.findField('LeaveTypeFed_id').getValue());
		}

		base_form.findField('HTMedicalCareClass_id').fireEvent('change', base_form.findField('HTMedicalCareClass_id'), base_form.findField('HTMedicalCareClass_id').getValue());

		if (this.action == 'add') {
			this.findById('ESecEF_EvnDiagPSPanel').isLoaded = true;
			this.findById('ESecEF_EvnUslugaPanel').isLoaded = true;
			this.findById('ESecEF_EvnSectionNarrowBedPanel').isLoaded = true;
		} else {
			this.findById('ESecEF_EvnDiagPSPanel').isLoaded = false;
			this.findById('ESecEF_EvnUslugaPanel').isLoaded = false;
			this.findById('ESecEF_EvnSectionNarrowBedPanel').isLoaded = false;
		}

		this.findById('ESecEF_EvnDiagPSGrid').getStore().removeAll();
		this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[0].disable();
		this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[1].disable();
		this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[2].disable();
		this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[3].disable();

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
		if ( isPskov == true ) {
			base_form.findField('UslugaComplex_id').clearBaseParams();

			if ( isPskov ) {
				base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'stac_kd' ]);
			}
		}

		/*
		this.specificsPanel.show();
		this.MorbusHepatitisSpec.hide();
		*/
		var loadMask = new Ext.LoadMask(this.findById('EvnSectionEditForm').getEl(), {msg:LOAD_WAIT});
		loadMask.show();
		if(this.childPS){
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

				LoadEmptyRow(this.findById('ESecEF_EvnDiagPSGrid'));
				LoadEmptyRow(this.findById('ESecEF_EvnUslugaGrid'));
				LoadEmptyRow(this.findById('ESecEF_EvnSectionNarrowBedGrid'));
				LoadEmptyRow(this.findById(that.id + 'ESecEF_AnatomDiagGrid').getGrid());

				if (base_form.findField('EvnSection_setDate').getValue()) {
					base_form.findField('EvnSection_disDate').setMinValue(base_form.findField('EvnSection_setDate').getValue());
					base_form.findField('EvnSection_setDate').setMinValue(base_form.findField('EvnSection_setDate').getValue());
				}

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
						callback:function () {
							base_form.findField('Diag_id').getStore().each(function (rec) {
								if (rec.get('Diag_id') == diag_id) {
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
									base_form.findField('Diag_id').setFilterByDate(base_form.findField('EvnSection_setDate').getValue());
								}
							});
						},
						params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_id}
					});
				}

				if (diag_set_phase_id) {
					base_form.findField('DiagSetPhase_id').setValue(diag_set_phase_id);
				}

				base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'), base_form.findField('LeaveType_id').getValue());

				this.checkLpuUnitType();
				this.recountKoikoDni();

				loadMask.hide();
				
				//base_form.clearInvalid();
				this.Morbus_id = null;

				if (!base_form.findField('EvnSection_setDate').disabled) {
					base_form.findField('EvnSection_setDate').focus(true, 200);
				}
				else if (!base_form.findField('EvnSection_disDate').disabled) {
					base_form.findField('EvnSection_disDate').focus(true, 200);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
				
				break;
			case 'edit':
			case 'view':
				base_form.load({
					failure:function () {
						loadMask.hide();

						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function () {
							//this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					params:{
						EvnSection_id:this.formParams.EvnSection_id,
						archiveRecord: that.archiveRecord
					},
					success:function (form, act) {
						
						if (!act || !act.response || !act.response.responseText) {
							loadMask.hide();
							sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы'+act+"d", function () {
								//this.hide();
							}.createDelegate(this));
						}
						var form_action = this.action;
						var response_obj = Ext.util.JSON.decode(act.response.responseText);
						if (response_obj[0].accessType == 'view') {
							this.action = 'view';
						}

						switch (this.action) {
							case 'edit':
								this.setTitle(WND_HOSP_ESECEDIT);
								this.enableEdit(true);
								this.findById('addDiag').show();
								this.findById('ESecEF_EvnDiagPSGrid').getTopToolbar().items.items[0].enable();
								this.findById('ESecEF_EvnUslugaGrid').getTopToolbar().items.items[0].enable();
								this.findById('ESecEF_EvnSectionNarrowBedGrid').getTopToolbar().items.items[0].enable();
								break;

							case 'view':
								this.findById('addDiag').hide();
								this.setTitle(WND_HOSP_ESECVIEW);
								this.enableEdit(false);
								break;
						}

						var anatom_where_code;
						var anatom_where_id = response_obj[0].AnatomWhere_id;
						var diag_aid = response_obj[0].Diag_aid;
						var diag_id = response_obj[0].Diag_id;
						var evn_die_exp_date = response_obj[0].EvnDie_expDate;
						var evn_die_exp_time = response_obj[0].EvnDie_expTime;
						var evn_section_dis_date = base_form.findField('EvnSection_disDate').getValue();
						var evn_section_set_date = base_form.findField('EvnSection_setDate').getValue();
						var evn_section_is_paid = response_obj[0].EvnSection_IsPaid;
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
						var med_personal_id = response_obj[0].MedPersonal_id;
						var org_aid = response_obj[0].Org_aid;
						var record;
						var tariff_class_id = response_obj[0].TariffClass_id;
						var Org_oidCombo = base_form.findField('Org_oid');
						var Org_oid = response_obj[0].Org_oid;

						if ( isPskov == true ) {
							var usluga_complex_id = base_form.findField('UslugaComplex_id').getValue();
						}

						if (!this.findById('ESecEF_EvnDiagPSPanel').collapsed) {
							this.findById('ESecEF_EvnDiagPSPanel').fireEvent('expand', this.findById('ESecEF_EvnDiagPSPanel'));
						}
						if (!this.findById('ESecEF_EvnUslugaPanel').collapsed) {
							this.findById('ESecEF_EvnUslugaPanel').fireEvent('expand', this.findById('ESecEF_EvnUslugaPanel'));
						}
						if (!this.findById('ESecEF_EvnSectionNarrowBedPanel').collapsed) {
							this.findById('ESecEF_EvnSectionNarrowBedPanel').fireEvent('expand', this.findById('ESecEF_EvnSectionNarrowBedPanel'));
						}
						if (!this.specificsPanel.collapsed) {
							this.specificsPanel.fireEvent('expand', this.specificsPanel);
						}

						base_form.findField('EvnSection_IsPaid').setValue(evn_section_is_paid);

						// Выполняются действия, которые должны выполняться после смены даты госпитализации
						base_form.findField('LpuSection_id').clearValue();
						base_form.findField('MedStaffFact_id').clearValue();
						base_form.findField('EvnSection_disDate').setMinValue(evn_section_set_date);
						setLpuSectionGlobalStoreFilter({
							isStac:true,
							onDate:Ext.util.Format.date(evn_section_set_date, 'd.m.Y')
						});
						base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						setMedStaffFactGlobalStoreFilter({
							dateFrom:Ext.util.Format.date(evn_section_set_date, 'd.m.Y'),
							dateTo:Ext.util.Format.date(evn_section_dis_date, 'd.m.Y'),
							EvnClass_SysNick: 'EvnSection',
							isStac:true/*,
							isDoctor:true*/
						});
						base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						// Выполняются действия, которые должны выполняться после смены даты выписки
						base_form.findField('MedStaffFact_did').clearValue();
						if (!evn_section_dis_date) {
							setMedStaffFactGlobalStoreFilter({
								isStac:true
							});
							base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
						else {
							setMedStaffFactGlobalStoreFilter({
								isStac:true, onDate:Ext.util.Format.date(evn_section_dis_date, 'd.m.Y')
							});
							base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
						// Выполняются действия, которые должны выполняться после изменения даты проведения экспертизы
						base_form.findField('LpuSection_aid').clearValue();
						base_form.findField('MedStaffFact_aid').clearValue();
						if (!evn_die_exp_date) {
							setLpuSectionGlobalStoreFilter();
							base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							setMedStaffFactGlobalStoreFilter();
							base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}
						else {
							setLpuSectionGlobalStoreFilter({
								onDate:evn_die_exp_date
							});
							base_form.findField('LpuSection_aid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							setMedStaffFactGlobalStoreFilter({
								onDate:evn_die_exp_date
							});
							base_form.findField('MedStaffFact_aid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
						}

						if (this.action == 'edit' && this.evnSectionIsFirst == true) {
							base_form.findField('EvnSection_setDate').setMinValue(getValidDT(Ext.util.Format.date(this.evnPSSetDT, 'd.m.Y'), ''));
						}
						index = base_form.findField('LpuSection_id').getStore().findBy(function (record, id) {
							if (record.get('LpuSection_id') == lpu_section_id) {
								return true;
							}
							else {
								return false;
							}
						});
						if (index >= 0) {
							record = base_form.findField('LpuSection_id').getStore().getAt(index);

							lpu_section_pid = record.get('LpuSection_pid');
							lpu_unit_type_id = record.get('LpuUnitType_id');
							base_form.findField('LpuSection_id').setValue(lpu_section_id);
							base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);

							if (Number(record.get('LpuUnitType_Code')) == 4) {
								base_form.findField('TariffClass_id').setAllowBlank(false);

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

						index = base_form.findField('MedStaffFact_id').getStore().findBy(function (record, id) {
							if ((record.get('LpuSection_id') == lpu_section_id || record.get('LpuSection_id') == lpu_section_pid) && record.get('MedPersonal_id') == med_personal_id) {
								return true;
							}
							else {
								return false;
							}
						});

						if (index >= 0) {
							base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
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
										if ( rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
										base_form.findField('MedStaffFact_id').validate();
									}
								}.createDelegate(this),
								url: C_MEDPERSONAL_LIST
							});
						}

						index = base_form.findField('TariffClass_id').getStore().findBy(function (rec, id) {
							if (rec.get('TariffClass_id') == tariff_class_id) {
								return true;
							}
							else {
								return false;
							}
						});
						if (index >= 0) {
							base_form.findField('TariffClass_id').setValue(tariff_class_id);
						}

						base_form.findField('LeaveType_id').fireEvent('change', base_form.findField('LeaveType_id'), base_form.findField('LeaveType_id').getValue());
						index = base_form.findField('AnatomWhere_id').getStore().findBy(function (record, id) {
							if (parseInt(record.get('AnatomWhere_id')) == parseInt(anatom_where_id)) {
								return true;
							}
							else {
								return false;
							}
						});
						if (index >= 0) {
							anatom_where_code = parseInt(base_form.findField('AnatomWhere_id').getStore().getAt(index).get('AnatomWhere_Code'));

							base_form.findField('AnatomWhere_id').fireEvent('change', base_form.findField('AnatomWhere_id'), anatom_where_id);
						}

						index = base_form.findField('MedStaffFact_did').getStore().findBy(function (record, id) {
							if (record.get('MedPersonal_id') == med_personal_did) {
								return true;
							}
							else {
								return false;
							}
						});

						if (index >= 0) {
							base_form.findField('MedStaffFact_did').setValue(base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id'));
						}
						else {
							Ext.Ajax.request({
								failure: function(response, options) {
									loadMask.hide();
								},
								params: {
									LpuSection_id: lpu_section_did,
									MedPersonal_id: med_personal_did
								},
								success: function(response, options) {
									loadMask.hide();
									
									base_form.findField('MedStaffFact_did').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

									index = base_form.findField('MedStaffFact_did').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_did && rec.get('LpuSection_id') == lpu_section_did ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_did').setValue(base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id'));
										base_form.findField('MedStaffFact_did').validate();
									}
								}.createDelegate(this),
								url: C_MEDPERSONAL_LIST
							});
						}

						index = base_form.findField('LpuSection_aid').getStore().findBy(function (record, id) {
							if (parseInt(record.get('LpuSection_id')) == parseInt(lpu_section_aid)) {
								return true;
							}
							else {
								return false;
							}
						});
						if (index >= 0) {
							base_form.findField('LpuSection_aid').setValue(lpu_section_aid);
						}

						index = base_form.findField('MedStaffFact_aid').getStore().findBy(function (record, id) {
							if (parseInt(record.get('LpuSection_id')) == parseInt(lpu_section_aid) && parseInt(record.get('MedPersonal_id')) == parseInt(med_personal_aid)) {
								return true;
							}
							else {
								return false;
							}
						});

						if (index >= 0) {
							base_form.findField('MedStaffFact_aid').setValue(base_form.findField('MedStaffFact_aid').getStore().getAt(index).get('MedStaffFact_id'));
						}
						else {
							Ext.Ajax.request({
								failure: function(response, options) {
									loadMask.hide();
								},
								params: {
									LpuSection_id: lpu_section_aid,
									MedPersonal_id: med_personal_aid
								},
								success: function(response, options) {
									loadMask.hide();
									
									base_form.findField('MedStaffFact_aid').getStore().loadData(Ext.util.JSON.decode(response.responseText), true);

									index = base_form.findField('MedStaffFact_aid').getStore().findBy(function(rec) {
										if ( rec.get('MedPersonal_id') == med_personal_aid && rec.get('LpuSection_id') == lpu_section_aid ) {
											return true;
										}
										else {
											return false;
										}
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_aid').setValue(base_form.findField('MedStaffFact_aid').getStore().getAt(index).get('MedStaffFact_id'));
										//base_form.findField('MedStaffFact_aid').validate();
									}
								}.createDelegate(this),
								url: C_MEDPERSONAL_LIST
							});
						}

						if ( isPskov == true ) {
							if ( !Ext.isEmpty(usluga_complex_id) ) {
								base_form.findField('UslugaComplex_id').getStore().load({
									callback: function() {
										index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
											return (rec.get('UslugaComplex_id') == usluga_complex_id);
										});

										if ( index >= 0 ) {
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
							callback:Ext.emptyFn,
							dateField:base_form.findField('EvnSection_disDate'),
							loadMask:false,
							setDate:false,
							setDateMaxValue:true,
							addMaxDateDays: this.addMaxDateDays,
							windowId:this.id
						});
						if (diag_aid) {
							base_form.findField('Diag_aid').getStore().load({
								callback:function () {
									base_form.findField('Diag_aid').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_aid) {
											base_form.findField('Diag_aid').fireEvent('select', base_form.findField('Diag_aid'), rec, 0);
										}
									});
								},
								params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_aid}
							});
						}
						if (diag_id) {
							base_form.findField('Diag_id').getStore().load({
								callback:function () {
									base_form.findField('Diag_id').getStore().each(function (rec) {
										if (rec.get('Diag_id') == diag_id) {
											/*
											var diag_code = rec.get('Diag_Code').substr(0, 3);
											if ( diag_code.inlist(['B15', 'B16', 'B17', 'B18', 'B19']) ) {
												parentWin.MorbusHepatitisSpec.show();
											}
											*/
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
										}
									}.createDelegate(this));
									parentWin.onSpecificsExpand(parentWin.specificsPanel);
								}.createDelegate(this),
								params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_id}
							});
						} else {
							parentWin.onSpecificsExpand(parentWin.specificsPanel);
						}
						if (org_aid) {
							var org_type;

							switch (anatom_where_code) {
								case 2:
									org_type = 'lpu';
									break;
								case 3:
									org_type = 'anatom';
									break;
							}

							if (org_type) {
								base_form.findField('Org_aid').getStore().load({
									callback:function (records, options, success) {
										if (success) {
											base_form.findField('Org_aid').setValue(org_aid);
										}
									},
									params:{
										Org_id:org_aid,
										OrgType:org_type,
										onlyFromDictionary: true
									}
								});
							}
						}
						if (Org_oid) {
							Org_oidCombo.getStore().load({
								callback:function (records, options, success) {
									Org_oidCombo.clearValue();
									if (success) {
										Org_oidCombo.setValue(Org_oid);
									}
								},
								params:{
									Org_id:Org_oid
								}
							});
						}
						base_form.findField('LpuUnitType_oid').getStore().filterBy(function (rec) {
							if (rec.get('LpuUnitType_id') != lpu_unit_type_id) {
								return true;
							}
							else {
								return false;
							}
						});
						if (lpu_unit_type_oid && lpu_unit_type_oid != lpu_unit_type_id) {
							base_form.findField('LpuUnitType_oid').setValue(lpu_unit_type_oid);
						}
						var mes2_id = base_form.findField('Mes2_id').getValue();
						this.loadMesCombo();
						this.loadKSGKPGKOEF();
						this.loadMes2Combo(mes2_id, false);
						if (response_obj[0].Morbus_id) {
							this.Morbus_id = response_obj[0].Morbus_id;
						} else {
							this.Morbus_id = null;
						}

						this.checkLpuUnitType();

						//Если случай оплачен, разрешить редактирование экспертизы
						var leave_type_code = base_form.findField('LeaveType_id').getFieldValue('LeaveType_Code');
						if (form_action == 'edit' && this.action == 'view' && evn_section_is_paid == 2 && leave_type_code == 3) {
							this.editAnatom = true;
							this.enableAnatomFormEdit(true);
						}

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

						if (getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['samara','kareliya', 'astra'])) {
							base_form.findField('LpuSectionBedProfile_id').setValue(response_obj[0].LpuSectionBedProfile_id);
						} else {
							base_form.findField('LpuSectionBedProfile_id').clearValue();
						}
						loadMask.hide();
						
						//base_form.clearInvalid();
						
						that.filterDS(
							);

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
						
					}.createDelegate(this),
					url:'/?c=EvnSection&m=loadEvnSectionEditForm'
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
	
	
	},
	/**
	* Загружаем список палат, в котором должны быть: указанная палата и остальные палаты профильного отделения, соответствующие полу пациента (включая общие палаты), в которых есть свободные места
	*/
	   filterDS: function(filt){
		var store= this.findById('dataViewDiag').getStore();
		var evn_diag_ps_id = this.findById('EvnSectionEditForm').getForm().findField('EvnDiagPS_id').getValue();
		if(filt=='save'){
		store.filterBy(function (rec) {
				if (rec.get('RecordStatus_Code') != 1) {
					return true;
				}
				else {
					return false;
				}
			});
		}else{
		store.filterBy(function (rec) {
				if (rec.get('EvnDiagPS_id') == evn_diag_ps_id||rec.get('RecordStatus_Code') == 3) {
					return false;
				}
				else {
					return true;
				}
			});
		}
		this.findById('dataViewDiag').refresh();
			
	   },
	wardOnSexFilter: function() {
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
	fillOnko: function (onkoData){
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
	checkOnkoLateDiagCause:function () {

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
				((tumor_stage_id >= 9 && tumor_stage_id <= 12) && ( diag_code.inlist(diag_code_check) || diag_code_5s == 'C63.2'))
			) {
			diag_cause_id.allowBlank = false;
			diag_cause_id.enable();
		} else {
			diag_cause_id.allowBlank = true;
			diag_cause_id.setValue('');
			diag_cause_id.disable();
		}

	},
	width:800
});
