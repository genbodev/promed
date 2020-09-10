/**
 * Направление
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.EvnDirectionEditWindow', {
	alias: 'widget.EvnDirectionEditWindow',
	title: 'Направление',
	extend: 'base.BaseForm',
	layout: 'anchor',

	addCodeRefresh: Ext6.emptyFn,
	closeToolText: 'Закрыть',
	maximized: false,
	width: 760,
	modal: true,
	cls: 'arm-window-new emk-forms-window evn-direction-edit-window',
	renderTo: Ext6.getBody(),

	onSprLoad: function(args) {
		var win = this;
		var form = this;
		var arguments = args;

		win.taskButton.hide();

		var form = this;
		var base_form = win.MainPanel.getForm();

		this.center();

		if ( !arguments[0] || !arguments[0].formParams ) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'));
			return false;
		}
		var lpu_sort = arguments[0].formParams.LpuSection_did||0;
		this.action = arguments[0].action || null;
		this.TimetableGraf_id = arguments[0].TimetableGraf_id || null;
		this.type = arguments[0].type || null;
		this.TimetableStac_id = arguments[0].TimetableStac_id || null;
		this.TimetableMedService_id = arguments[0].TimetableMedService_id || null;
		this.TimetableResource_id = arguments[0].TimetableResource_id || null;
		this.EvnUsluga_id = arguments[0].EvnUsluga_id || null;
		this.timetable = arguments[0].formParams.timetable || '';
		this.From_MedStaffFact_id = arguments[0].formParams.From_MedStaffFact_id || null;
		this.callback = arguments[0].callback || Ext6.emptyFn;
		//~ this.onHide = arguments[0].onHide|| Ext6.emptyFn;
		this.closable = (arguments[0].disableClose)?false:true;
		this.isWasChosenRemoteConsultCenter = (arguments[0].formParams.DirType_id
			&& 17 == arguments[0].formParams.DirType_id
			&& arguments[0].formParams.MedService_id) ? true : false;
		if (this.queryById('cancelBtn') ) {
			this.queryById('cancelBtn').setVisible(this.closable);
		}
		this.allowQuestionPrintEvnDirection = arguments[0].disableQuestionPrintEvnDirection ? false : true;
		this.isNotForSystem = arguments[0].formParams.isNotForSystem || null;
		this.directionNumberParams = {
			DirType_id: null,
			EvnDirection_Num: null,
			processing: false,
			year: null
		};
		this.ignorePersonPrivilegeCheck = false;

		this.mode = (arguments[0].mode && arguments[0].mode == 'nosave')?arguments[0].mode:'';

		win.Person_id = (arguments[0].Person_id ? arguments[0].Person_id : '');
		win.Person_Birthday = (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : '');
		win.Person_Firname = (arguments[0].Person_Firname ? arguments[0].Person_Firname : '');
		win.Person_Secname = (arguments[0].Person_Secname ? arguments[0].Person_Secname : '');
		win.Person_Surname = (arguments[0].Person_Surname ? arguments[0].Person_Surname : '');

		var base_form = win.MainPanel.getForm();
		base_form.reset();

		var loadMask = new Ext6.LoadMask(this, { msg: LOAD_WAIT });
		loadMask.show();

		if(arguments[0].personData){
			base_form.setValues(arguments[0].personData);
		}
		if (!Ext.isEmpty(arguments[0].formParams.DirType_Code) && arguments[0].formParams.DirType_Code == 9) {
			form.DirType_Code = arguments[0].formParams.DirType_Code;
		} else {
			form.DirType_Code = null;
		}

		base_form.setValues(arguments[0].formParams);

		if (Ext6.isEmpty(base_form.findField('PayType_id').getValue())) {
			// по умолчанию ОМС
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}

		this.checkEvnDirectionIsReceive();
		this.filterLpuSectionProfileCombo({});

		base_form.findField('Lpu_did').getStore().load(
			{
				// where: "where Lpu_endDate > CURRENT_DATE"
				callback: function()
				{
					var lpu_did_combo = base_form.findField('Lpu_did');
					lpu_did_combo.setValue(base_form.findField('Lpu_did').getValue());
					lpu_did_combo.getStore().clearFilter();
					lpu_did_combo.lastQuery = '';
					if (getRegionNick().inlist(['astra', 'ekb'])) {
						form.refreshFieldsVisibility(['Lpu_did']);
					}
				}
			});
		base_form.findField('Org_oid').getStore().load(
			{
				callback: function()
				{
					var org_oid_combo = base_form.findField('Org_oid');
					org_oid_combo.setValue(base_form.findField('Org_oid').getValue());
					org_oid_combo.getStore().clearFilter();
					org_oid_combo.lastQuery = '';
					form.refreshFieldsVisibility(['Lpu_did', 'Org_oid']);
				}
			});
		if(this.type=='recwp'){
			base_form.findField('EvnDirection_noSetDateTime').setValue('');
			base_form.findField('EvnDirection_noSetDateTime').setDisabled(false);
			base_form.findField('EvnDirection_noSetDateTime').setAllowBlank(false);
		}else{
			base_form.findField('EvnDirection_noSetDateTime').setDisabled(true);
			base_form.findField('EvnDirection_noSetDateTime').setAllowBlank(true);
		}

		this.setDisabled(this.action.inlist(['view', 'editpaytype']));
		if (this.action == 'editpaytype') {
			base_form.findField('PayType_id').enable();
		}
		
		if (getRegionNick() == 'msk') {
			form.CVIConsultRKC_id = arguments[0].formParams.CVIConsultRKC_id || null;
			form.RepositoryObserv_sid = arguments[0].formParams.RepositoryObserv_sid || null;
			form.isRKC = arguments[0].formParams.isRKC || null;
		}

		if ( arguments[0].is_cito) {
			base_form.findField('DirType_id').setValue(5);
			base_form.findField('DirType_id').setDisabled(true);
		}
		if ( arguments[0].formParams.time ) {
			base_form.findField('EvnDirection_setDateTime').setValue(arguments[0].formParams.time);
			base_form.findField('EvnDirection_setDateTime').setContainerVisible(true);
			base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(false);
			base_form.findField('EvnDirection_desDT').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
			base_form.findField('EvnDirection_desDT').setAllowBlank(true);
		} else {
			// вообще скрываем это поле, если времени нет, значит направление в очередь
			base_form.findField('EvnDirection_setDateTime').setContainerVisible(false);
			base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(true);
			base_form.findField('EvnDirection_desDT').setContainerVisible(base_form.findField('DirType_id').getValue() == 20 || getRegionNick().inlist(['astra','ekb','penza']) || (getRegionNick() == 'krym' && !Ext6.isEmpty(base_form.findField('DirType_id').getValue()) && base_form.findField('DirType_id').getValue().inlist([ 1, 5 ])));

			var allowBlank = true;

			switch ( getRegionNick() ) {
				case 'astra':
				case 'penza':
					allowBlank = (base_form.findField('DirType_id').getValue() != 1);
					break;

				case 'krym':
					allowBlank = (Ext6.isEmpty(base_form.findField('DirType_id').getValue()) || !base_form.findField('DirType_id').getValue().inlist([ '1', '5' ]));
					break;
			}

			base_form.findField('EvnDirection_desDT').setAllowBlank(allowBlank);
		}

		if (arguments[0].formParams.StudyTarget_id) {
			base_form.findField('StudyTarget_id').disable();
		}

		base_form.findField('EvnDirection_desDT').setMinValue(undefined);

		base_form.findField('StudyTarget_id').setContainerVisible(false);
		base_form.findField('StudyTarget_id').setAllowBlank(true);
		var lsp_combo = base_form.findField('LpuSectionProfile_id');
		if ( arguments[0].formParams.LpuSectionProfile_id ) {
			lsp_combo.setValue(arguments[0].formParams.LpuSectionProfile_id);
		}

		var diag_id = arguments[0].formParams.Diag_id;

		var dir_type_combo = base_form.findField('DirType_id');
		var dir_type_id = parseInt(dir_type_combo.value);
		if (!Ext6.isEmpty(dir_type_id)&&dir_type_id) {
			// если передан конкретный тип направления, его и устанавливаем
			dir_type_combo.getStore().clearFilter();
			dir_type_combo.lastQuery = '';
		} else {
			// иначе фильтруем
			dir_type_combo.getStore().clearFilter();
			dir_type_combo.lastQuery = '';
			dir_type_combo.getStore().filterBy(function(record) {
				if (base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
					// форма добавления внешнего направления, убрать направление на ВК или МСЭ
					if (record.get('DirType_id').toString().inlist(['9'])) {
						return false;
					}
				}
				if(record.get('DirType_id').toString().inlist(['15','20','21'])){
					return false;
				}
				var filter_flag = true;
				switch (form.timetable)
				{
					case 'TimetableGraf':
						filter_flag = record.get('DirType_id').toString().inlist(['2','3','16']);
						dir_type_id = 16;
						break;
					case 'TimetableStac':
						filter_flag = record.get('DirType_id').toString().inlist(['1','2','4','5','6']);
						break;
					case 'TimetableResource':
					case 'TimetableMedService':
						filter_flag = (10 == record.get('DirType_id'));
						dir_type_id = 10;
						break;
					default:
						filter_flag = true;
				}
				if ( !filter_flag ) {
					return false;
				}

				return true;
			});
		}

		dir_type_combo.setValue(dir_type_id);
		if (!dir_type_combo.getFieldValue('DirType_Code')) {
			dir_type_combo.setValue(null);
			dir_type_combo.setRawValue('');
		}

		if (dir_type_id > 0 || this.action == 'edit') { //костыль, при открытии формы на редактирование это поле еще не подгрузилось
			dir_type_combo.setDisabled(true);
			if(dir_type_id.inlist([1,5])&&getRegionNick() == 'ekb'){
				base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(true);
			}else{
				base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(false);
			}
		} else {
			base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(false);
		}

		var Lpu_IsNotForSystem = base_form.findField('Lpu_sid').getFieldValue('Lpu_IsNotForSystem') != 2;// 1 - не работает в системе, 2 - работает
		var medstaff_id = base_form.findField('MedStaffFact_id');

		if(getRegionNick() == 'ekb') {
			base_form.findField('MedPersonal_Code').setAllowBlank( !dir_type_id.inlist([1,5,10,16]) || Lpu_IsNotForSystem );
			var medstaff_id = base_form.findField('MedStaffFact_id');
			medstaff_id.codeField = 'MedPersonal_DloCode';
			medstaff_id.tpl = new Ext6.XTemplate(
				'<tpl for="."><div class="x6-boundlist-item x-combo-list-item" >',
				'<table style="border: 0;">',
				'<td style="width: 45px;">{MedPersonal_TabCode}&nbsp;</td>',
				'<td style="width: 45px;"><font color="red">{MedPersonal_DloCode}&nbsp;</font></td>',
				'<td>',
				'<div style="font-weight: bold;">{MedPersonal_Fio}&nbsp;{[Ext6.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
				'<div style="font-size: 10px;">{PostMed_Name}{[!Ext6.isEmpty(values.MedStaffFact_Stavka) ? ", ст." : ""]} {MedStaffFact_Stavka}</div>',
				'<div style="font-size: 10px;">{[!Ext6.isEmpty(values.WorkData_begDate) ? "Дата начала работы: " + values.WorkData_begDate:""]} {[!Ext6.isEmpty(values.WorkData_endDate) ? "Дата увольнения: " + this.formatWorkDataEndDate(values.WorkData_endDate) :""]}</div>',
				'<div style="font-size: 10px;">{[!Ext6.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div>',
				'</td>',
				'</tr></table>',
				'</div></tpl>',
				{
					formatWorkDataEndDate: function(endDate) {
						var fixed = (typeof endDate == 'object' ? Ext6.util.Format.date(endDate, 'd.m.Y') : endDate);
						return fixed;
					}
				}
			);
		} else
			base_form.findField('MedPersonal_Code').setContainerVisible(false);

		switch ( this.action ) {
			case 'add':
				setCurrentDateTime({
					callback: function() {
						// В случае создания заявки на экстренную операцию из АРМ приемного поле «Желаемая дата» автоматически заполнять текущей датой
						if (base_form.findField('EvnDirection_IsCito').getValue() == 2 && base_form.findField('DirType_id').getValue() == 20) {
							base_form.findField('EvnDirection_desDT').setValue(base_form.findField('EvnDirection_setDate').getValue());
						}

						base_form.findField('EvnDirection_setDate').fireEvent('change', base_form.findField('EvnDirection_setDate'), base_form.findField('EvnDirection_setDate').getValue());
						base_form.findField('EvnDirection_setDate').focus(true, 0);
						dir_type_combo.fireEvent('change', dir_type_combo, dir_type_combo.getValue());
						base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
						this.loadMedPersonalCombo();
					}.createDelegate(this),
					dateField: base_form.findField('EvnDirection_setDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: false,
					windowId: 'EvnDirectionEditForm'
				});
				//this.setTitle(WND_POL_EDIRADD);
				this.enableEdit(true);

				if (this.isNotForSystem && getRegionNick().inlist(['astra', 'ekb'])) {
					base_form.findField('Lpu_did').clearValue();
				}

				base_form.findField('EvnDirection_desDT').setMinValue(getValidDT(getGlobalOptions().date, '')); // желаемая дата ограничена текущей датой

				if (base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
					// форма добавления внешнего направления
					base_form.findField('Lpu_did').setValue(getGlobalOptions().lpu_id);
					base_form.findField('Lpu_did').disable();
					base_form.findField('Lpu_sid').clearValue();
				}
				if (17 == dir_type_id && base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
					base_form.findField('Diag_id').setAllowBlank(false);
					base_form.findField('MedStaffFact_id').setAllowBlank(true);
				}

				if(getRegionNick()=='ufa' && (17 == base_form.findField('DirType_id').getValue()) ) base_form.findField('ConsultingForm_id').setAllowBlank(false);
				base_form.findField('ConsultingForm_id').setContainerVisible(base_form.findField('DirType_id').getValue() == 17);
				base_form.findField('LpuSection_did').setContainerVisible(base_form.findField('DirType_id').getValue() == 5);
				base_form.findField('LpuSection_did').setAllowBlank(base_form.findField('DirType_id').getValue() != 5 || getRegionNick() != 'kareliya');
				base_form.findField('EvnXml_id').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
				if (!getRegionNick().inlist([ 'astra', 'krym', 'penza' ])) {
					base_form.findField('EvnDirection_desDT').setAllowBlank(base_form.findField('DirType_id').getValue() != 20);
				}
				base_form.findField('EvnDirectionOper_IsAgree').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
				form.queryById('EDEW_AnestButton').setVisible(base_form.findField('DirType_id').getValue() == 20);
				form.queryById('EDEW_OperButton').setVisible(base_form.findField('DirType_id').getValue() == 20);
				base_form.findField('EvnXml_id').getStore().removeAll();
				if (base_form.findField('DirType_id').getValue() == 20 && !Ext6.isEmpty(base_form.findField('EvnDirection_pid').getValue())) {
					// грузим операционные эпикризы
					base_form.findField('EvnXml_id').getStore().load({
						params: {
							Evn_id: base_form.findField('EvnDirection_pid').getValue(),
							XmlType_ids: Ext6.util.JSON.encode([3,10])
						}
					});
				}

				loadMask.hide();
				base_form.findField('EvnDirection_setDate').focus(true, 250);
				this.queryById('printBtn').setVisible(false);

				//установка значений из входящих параметров
				if ( diag_id != null && diag_id.toString().length > 0 ) { //если во входящих параметрах есть диагноз - настраиваем комбо
					base_form.findField('Diag_id').getStore().load(
						{
							callback: function() {
								base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
							},
							params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
						});
				}
				break;

			case 'edit':
				//this.setTitle(WND_POL_EDIREDIT);
				this.enableEdit(true);

				if ( arguments[0].EvnDirection_id ) {
					this.loadRecord({
							EvnDirection_id: arguments[0].EvnDirection_id,
							action: 'edit'
						},
						loadMask
					)
				} else {
					if ( diag_id != null && diag_id.toString().length > 0 )
					{
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
							},
							params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
						});
					}

					this.loadMedPersonalCombo();

					dir_type_combo.fireEvent('change', dir_type_combo, dir_type_combo.getValue());
					base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
				}
				loadMask.hide();
				base_form.findField('EvnDirection_setDate').focus(true, 250);
				this.queryById('printBtn').setVisible(true);
				break;

			case 'view':
			case 'editpaytype':
				if (this.action == 'editpaytype') {
					this.enableEdit(true);
				} else {
					this.enableEdit(false);
					this.queryById('printBtn').setVisible(true);
				}

				if ( arguments[0].EvnDirection_id ) {
					this.loadRecord({
							EvnDirection_id: arguments[0].EvnDirection_id,
							action: 'view'
						},
						loadMask
					);
				} else if ( arguments[0].EvnQueue_id ) {
					this.loadRecord({
							EvnQueue_id: arguments[0].EvnQueue_id,
							EvnDirection_id: null,
							action: 'view'
						},
						loadMask
					);
				} else {
					if ( diag_id != null && diag_id.toString().length > 0 )
					{
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
							},
							params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
						});
					}

					this.loadMedPersonalCombo();

					loadMask.hide();
					dir_type_combo.fireEvent('change', dir_type_combo, dir_type_combo.getValue());
					base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
					this.queryById('printBtn').setVisible(true);
					//~ this.buttons[this.buttons.length - 1].focus();
				}

				break;
		}

		var toothsPanel = form.queryById(form.id + "_" + 'ToothNumFieldsPanel');
		//~ toothsPanel.clearPanel();

		// скрываем зубы
		if (!arguments[0].formParams.PrescriptionType_Code || (arguments[0].formParams.PrescriptionType_Code && parseInt(arguments[0].formParams.PrescriptionType_Code) != 12)
			|| !arguments[0].parentEvnClass_SysNick || (arguments[0].parentEvnClass_SysNick && arguments[0].parentEvnClass_SysNick != "EvnVizitPLStom")
		) {
			toothsPanel.hide();
		}

		base_form.findField('Lpu_did').fireEvent('change',base_form.findField('Lpu_did'),base_form.findField('Lpu_did').getValue());


	},

	show: function() {
		this.callParent(arguments);
	},

	/**
	 * Возвращает данные введенные-выбранные в форме без сохранения объекта в БД
	 */
	returnData: function(params) {
		var f = this.MainPanel;
		params = Ext6.apply(f.getForm().getValues(), params);
		this.callback({evnDirectionData: params});
		this.saved = true;
		this.hide();
	},
	doSave: function() {
		var win = this;
		var form = win.MainPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var index;
		var params = new Object();
		var record;

		if (getRegionNick() == 'kz' && base_form.findField('DirType_id').getValue().inlist([1,5]) && !win.ignorePersonPrivilegeCheck) {
			var conf = {
				Person_id: base_form.findField('Person_id').getValue(),
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
				win: win,
				callback: function() {
					win.ignorePersonPrivilegeCheck = true;
					win.doSave();
				}
			};
			sw.Promed.PersonPrivilege.checkExists(conf);
			return false;
		}

		params.EvnDirection_Num = base_form.findField('EvnDirection_Num').getValue();

		params.From_MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id')||0);
		if (base_form.findField('EvnDirection_IsReceive').getValue() != 2) {
			base_form.findField('LpuSection_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id') || 0);
		}
		params.MedStaffFact_sid = this.From_MedStaffFact_id;
		base_form.findField('MedPersonal_zid').setValue(base_form.findField('MedStaffFact_zid').getFieldValue('MedPersonal_id')||0);

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}
		if ( base_form.findField('DirType_id').disabled ) {
			params.DirType_id = base_form.findField('DirType_id').getValue();
		}
		if ( base_form.findField('Lpu_did').disabled ) {
			params.Lpu_did = base_form.findField('Lpu_did').getValue();
		}
		if ( base_form.findField('Org_oid').disabled ) {
			params.Org_oid = base_form.findField('Org_oid').getValue();
		}
		if ( base_form.findField('LpuUnitType_did').disabled ) {
			params.LpuUnitType_did = base_form.findField('LpuUnitType_did').getValue();
		}
		if ( base_form.findField('MedService_id').disabled ) {
			params.MedService_id = base_form.findField('MedService_id').getValue();
		}
		if ( base_form.findField('MedicalCareFormType_id').disabled ) {
			params.MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue();
		}
		if ( base_form.findField('StudyTarget_id').disabled ) {
			params.StudyTarget_id = base_form.findField('StudyTarget_id').getValue();
		}
		if ( base_form.findField('ConsultationForm_id').disabled ) {
			params.ConsultationForm_id = base_form.findField('ConsultationForm_id').getValue();
		}
		if( !base_form.findField('EvnDirection_IsCito').disabled ) {
			if(base_form.findField('EvnDirection_IsCito').getValue()) {
				params.EvnDirection_IsCito = 2;
			} else params.EvnDirection_IsCito = 1;
		}
		
		if (getRegionNick() == 'msk') {
			params.CVIConsultRKC_id = win.CVIConsultRKC_id || null;
			params.RepositoryObserv_sid = win.RepositoryObserv_sid || null;
			params.isRKC = win.isRKC || null;
		}
		
		this.closable = true;
		if (this.mode == 'nosave') { // если режим формы = без сохранения, то просто возвращаетм данные
			this.returnData(params);
			return true;
		}

		var loadMask = new Ext6.LoadMask(this, { msg: LOAD_WAIT_SAVE });
		loadMask.show();
		if (base_form.findField('DirType_id').getValue() == 17 || base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
			params.toQueue = 1;

			if(base_form.findField('MedService_id').isVisible() && !base_form.findField('MedService_id').disabled)
				params.MedService_isEnabled = true;//в помощь серверной проверке на обязательность поля, иначе ложное срабатывание: https://redmine.swan.perm.ru/issues/114276#note-52
		}

		if (this.action == 'editpaytype') {
			// сохраняем только вид оплаты, т.к. направление может быть и автоматическим и на форме нет всех полей направления, то вызывать метод сохранения всей формы как то непрваильно
			Ext6.Ajax.request({
				url: '/?c=EvnDirection&m=setPayType',
				params: {
					EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
					PayType_id: base_form.findField('PayType_id').getValue()
				},
				callback: function() {
					loadMask.hide();
					win.hide();
				}
			});
		} else {
			base_form.submit({
				failure: function (result_form, action) {
					loadMask.hide();

					if (action.result) {
						if (action.result.Error_Msg) {
							Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				},
				params: params,
				success: function (result_form, action) {
					loadMask.hide();

					if (action.result && action.result.EvnDirection_id > 0) {
						base_form.findField('EvnDirection_id').setValue(action.result.EvnDirection_id);

						var response = new Object();

						response.Diag_id = base_form.findField('Diag_id').getValue();
						response.PayType_id = base_form.findField('PayType_id').getValue();
						response.DirType_id = base_form.findField('DirType_id').getValue();
						response.DirType_Name = '';
						response.EvnDirection_Descr = base_form.findField('EvnDirection_Descr').getValue();
						response.EvnDirection_id = base_form.findField('EvnDirection_id').getValue();
						response.EvnDirection_Num = params.EvnDirection_Num;
						response.EvnDirection_pid = base_form.findField('EvnDirection_pid').getValue();
						response.DopDispInfoConsent_id = base_form.findField('DopDispInfoConsent_id').getValue();
						response.EvnDirection_IsCito = base_form.findField('EvnDirection_IsCito').getValue();
						response.MedService_id = base_form.findField('MedService_id').getValue();
						response.Resource_id = base_form.findField('Resource_id').getValue();
						response.EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue();
						response.LpuSectionProfile_Name = '';
						response.MedPersonal_id = params.MedPersonal_id;
						response.MedPersonal_zid = params.MedPersonal_zid;
						response.Person_id = base_form.findField('Person_id').getValue();
						response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
						response.Server_id = base_form.findField('Server_id').getValue();
						response.EvnXml_id = base_form.findField('EvnXml_id').getValue();
						response.EvnDirection_desDT = base_form.findField('EvnDirection_desDT').getValue();
						response.EvnDirectionOper_IsAgree = base_form.findField('EvnDirectionOper_IsAgree').getValue();

						record = base_form.findField('DirType_id').getStore().getById(base_form.findField('DirType_id').getValue());
						if (record) {
							response.DirType_Name = record.get('DirType_Name');
						}

						record = base_form.findField('LpuSectionProfile_id').getStore().getById(base_form.findField('LpuSectionProfile_id').getValue());
						if (record) {
							response.LpuSectionProfile_Name = record.get('LpuSectionProfile_Name');
						}
						this.callback({evnDirectionData: response});
						this.saved = true;
						this.hide();
						this.action = 'view';
						if (getRegionNick() == 'ufa' && this.allowQuestionPrintEvnDirection) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								msg: langs('Распечатать стат. талон?'),
								title: langs('Вопрос'),
								icon: Ext6.MessageBox.QUESTION,
								fn: function (buttonId) {
									if (buttonId === 'yes') {
										var params = new Object();
										params.type = 'EvnPL';
										printEvnPLBlank(params);
									}
									else {
										this.hide();
									}
								}.createDelegate(this)
							});
						} else if (this.allowQuestionPrintEvnDirection) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								msg: langs('Вывести направление на печать?'),
								title: langs('Вопрос'),
								icon: Ext6.MessageBox.QUESTION,
								fn: function (buttonId) {
									if (buttonId === 'yes') {
										this.printEvnDirection();
									}
									else {
										this.hide();
									}
								}.createDelegate(this)
							});
						}
					}
					else {
						Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
					}
				}.createDelegate(this)
			});
		}
	},
	enableEdit: function(enable) {
		if ( enable ) {
			// this.queryById('EDirEF_').enable();

			this.queryById('saveBtn').enable();
		}
		else {
			// this.queryById('EDirEF_').disable();

			this.queryById('saveBtn').disable();
		}
	},
	directionNumberParams: {
		DirType_id: null,
		EvnDirection_Num: null,
		processing: false,
		year: null
	},
	/**
	 * @task https://redmine.swan.perm.ru//issues/108892
	 */
	getEvnDirectionNumber: function() {
		var
			win = this,
			base_form = win.MainPanel.getForm();
		var
			DirType_id = base_form.findField('DirType_id').getValue(),
			EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue(),
			loadMask,
			year = (typeof EvnDirection_setDate == 'object' && EvnDirection_setDate!=null) ? EvnDirection_setDate.format('Y') : new Date().format('Y');

		if (
			!Ext6.isEmpty(EvnDirection_setDate)
			&& Ext6.isEmpty(win.directionNumberParams.EvnDirection_Num)
			&& (
				(getRegionNick() == 'astra' && (win.directionNumberParams.year != year || win.directionNumberParams.DirType_id != DirType_id))
				|| (getRegionNick().inlist([ 'ekb', 'khak' ]) && win.directionNumberParams.year != year)
				|| (!getRegionNick().inlist([ 'astra', 'ekb', 'khak' ]) && Ext6.isEmpty(base_form.findField('EvnDirection_Num').getValue()) && win.directionNumberParams.processing == false)
			)
		) {
			if ( 17 == DirType_id || base_form.findField('EvnDirection_IsReceive').getValue() != 2 ) {
				loadMask = new Ext6.LoadMask(this, { msg: "Генерация номера направления..." });
				loadMask.show();

				win.directionNumberParams.DirType_id = DirType_id;
				win.directionNumberParams.processing = true;
				win.directionNumberParams.year = year;

				Ext6.Ajax.request({
					callback: function (options, success, response) {
						loadMask.hide();

						win.directionNumberParams.processing = false;

						if (success) {
							var response_obj = Ext6.util.JSON.decode(response.responseText);
							base_form.findField('EvnDirection_Num').setValue(response_obj.EvnDirection_Num);
							base_form.findField('EvnDirection_Num').disable();
						}
						else {
							Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при определении номера направления'), function () {
								base_form.findField('EvnDirection_setDate').focus(true);
							});
						}
					}.createDelegate(this),
					params: {
						DirType_id: DirType_id,
						year: year
					},
					url: '/?c=EvnDirection&m=getEvnDirectionNumber'
				});
			}
		}
	},
	openRecordWindow:function(){
		var win = this;
		var base_form = win.MainPanel.getForm();
		var params = {
			Person_Firname:win.Person_Firname,
			Person_Secname:win.Person_Secname,
			Person_Surname:win.Person_Surname,
			Person_id:base_form.findField('Person_id').getValue(),
			PersonEvn_id:base_form.findField('PersonEvn_id').getValue(),
			Server_id:base_form.findField('Server_id').getValue(),
			type:this.type,
			directionData: {
				DirType_id:base_form.findField('DirType_id').getValue(),
				MedStaffFact_id:base_form.findField('MedStaffFact_id').getValue(),
				Lpu_did:base_form.findField('Lpu_did').getValue(),
				MedPersonal_id:base_form.findField('MedPersonal_id').getValue(),
				LpuSection_id:base_form.findField('LpuSection_id').getValue(),
				LpuSectionProfile_id:base_form.findField('LpuSectionProfile_id').getValue(),
				Diag_id:base_form.findField('Diag_id').getValue(),
				EvnDirection_Num:base_form.findField('EvnDirection_Num').getValue(),
				Lpu_Nick:base_form.findField('Lpu_did').getRawValue()
			},
			personData:{
				Person_Firname:win.Person_Firname,
				Person_Secname:win.Person_Secname,
				Person_Surname:win.Person_Surname,
				Person_id:base_form.findField('Person_id').getValue(),
				PersonEvn_id:base_form.findField('PersonEvn_id').getValue(),
				Server_id:base_form.findField('Server_id').getValue()
			},
			onDirection:win.actionEdit.createDelegate(this)
		}
		getWnd('swDirectionMasterWindow').show(params);
	},
	loadMedPersonalCombo: function() {
		var win = this,
			base_form = win.MainPanel.getForm(),
			is_receive_field = base_form.findField('EvnDirection_IsReceive'),
			lpu_send_field = base_form.findField('Lpu_sid'),
			mp_field = base_form.findField('MedPersonal_id'),
			dlocode_field = base_form.findField('MedPersonal_Code'),
			ls_field = base_form.findField('LpuSection_id'),
			msf_field = base_form.findField('MedStaffFact_id'),
			mpz_field = base_form.findField('MedPersonal_zid'),
			msfz_field = base_form.findField('MedStaffFact_zid');

		var on_load_msf = function() {
			log('on_load_msf', msf_field.getStore().getCount(), msf_field.getValue(), mp_field.getValue());
			win.filterMedPersonalCombo();
			var rec, index = -1;
			if (msf_field.getValue()) {
				rec = msf_field.getStore().getById(msf_field.getValue());
			} else if (mp_field.getValue()) {
				index = msf_field.getStore().findBy(function(rec) {
					return (
						rec.get('MedPersonal_id') == mp_field.getValue()
						&& rec.get('LpuSection_id') == ls_field.getValue()
					);
				});
				if ( index >= 0) {
					rec = msf_field.getStore().getAt(index);
				} else {
					index = msf_field.getStore().findBy(function (rec) {
						return ( rec.get('MedPersonal_id') == mp_field.getValue() );
					});
					if (index >= 0) {
						rec = msf_field.getStore().getAt(index);
					}
				}
			}
			if (rec) {
				log('on_load_msf', rec, rec.get('MedStaffFact_id'));
				msf_field.setValue(rec.get('MedStaffFact_id'));
				dlocode_field.setValue( msf_field.getStore().getById(rec.get('MedStaffFact_id')).data.MedPersonal_DloCode );
			} else {
				log('on_load_msf not found rec', msf_field.getStore());
				msf_field.setValue(null);
				dlocode_field.setValue(null);
			}
		};

		var on_load_msfz = function() {
			log('on_load_msfz', msfz_field.getStore().getCount(), msfz_field.getValue(), mpz_field.getValue());
			win.filterMedPersonalCombo();
			var rec, index = -1;
			if (msfz_field.getValue()) {
				rec = msfz_field.getStore().getById(msfz_field.getValue());
			} else if (mpz_field.getValue()) {
				index = msfz_field.getStore().findBy(function(rec) {
					return ( rec.get('MedPersonal_id') == mpz_field.getValue() );
				});
				if ( index >= 0) {
					rec = msfz_field.getStore().getAt(index);
				}
			}
			if (rec) {
				log('on_load_msfz', rec, rec.get('MedStaffFact_id'));
				msfz_field.setValue(rec.get('MedStaffFact_id'));
			} else {
				log('on_load_msfz not found rec', msfz_field.getStore());
				msfz_field.setValue(null);
			}
			var lpu_sort = getGlobalOptions().CurLpuSection_id;
			msfz_field.getStore().each(function(s){
				if(s.get('LpuSection_id')===lpu_sort){s.set('SortVal',1);}
				else {s.set('SortVal',2)}
				s.commit();
			});
			msfz_field.getStore().sort('MedPersonal_Fio');
			msfz_field.getStore().sort('SortVal');
			//~ msfz_field.getStore().applySort(); //TODO: applySort is not a function
		};

		msf_field.getStore().removeAll();
		msf_field.lastQuery = '';
		msfz_field.getStore().removeAll();
		msfz_field.lastQuery = '';
		sw4.swMedStaffFactGlobalStore.clearFilter();
		var msf_load_params = {mode: 'combo'};
		var msfz_load_params = {mode: 'combo'};
		if (is_receive_field.getValue() == 2 || lpu_send_field.getValue()) {
			// если внешнее направление, то врач и заведующий зависят от данного комбо
			msf_load_params.Lpu_id = lpu_send_field.getValue();
			msfz_load_params.Lpu_id = lpu_send_field.getValue();
		} else {
			msf_load_params.Lpu_id = getGlobalOptions().lpu_id;
			msfz_load_params.Lpu_id = getGlobalOptions().lpu_id;
		}
		switch (true) {
			case (ls_field.getValue() > 0 && mp_field.getValue() > 0 && mpz_field.getValue() > 0):
				msf_load_params.LpuSection_id = ls_field.getValue();
				//msfz_load_params.LpuSection_id = ls_field.getValue();
				msf_load_params.MedPersonal_id = mp_field.getValue();
				msfz_load_params.MedPersonal_id = mpz_field.getValue();
				break;
			case (ls_field.getValue() > 0 && mp_field.getValue() > 0):
				msf_load_params.LpuSection_id = ls_field.getValue();
				//msfz_load_params.LpuSection_id = ls_field.getValue();
				msf_load_params.MedPersonal_id = mp_field.getValue();
				break;
			case (mp_field.getValue() > 0 && mpz_field.getValue() > 0):
				msf_load_params.MedPersonal_id = mp_field.getValue();
				msfz_load_params.MedPersonal_id = mpz_field.getValue();
				break;
			case (mp_field.getValue() > 0):
				msf_load_params.MedPersonal_id = mp_field.getValue();
				break;
			case (mpz_field.getValue() > 0):
				msfz_load_params.MedPersonal_id = mpz_field.getValue();
				break;
		}
		if (sw4.swMedStaffFactGlobalStore.getCount() > 0 && msf_load_params.Lpu_id == getGlobalOptions().lpu_id) {
			// используем swMedStaffFactGlobalStore
			msf_load_params = false;
		}
		if (sw4.swMedStaffFactGlobalStore.getCount() > 0 && msfz_load_params.Lpu_id == getGlobalOptions().lpu_id) {
			// используем swMedStaffFactGlobalStore
			msfz_load_params = false;
		}

		log('loadMedPersonalCombo', msf_load_params, msfz_load_params);
		if (msf_load_params) {
			log('load_msf', msf_load_params,1);

			if (msf_load_params.Lpu_id) {
				msf_field.getStore().load({
					params: msf_load_params,
					callback: on_load_msf
				});
			} else {
				msf_field.getStore().removeAll();
				on_load_msf();
			}
		} else {
			msf_field.getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
			on_load_msf();
		}
		if (msfz_load_params) {
			log('load_msfz', msfz_load_params,2);

			if (msfz_load_params.Lpu_id) {
				msfz_field.getStore().load({
					params: msfz_load_params,
					callback: on_load_msfz
				});
			} else {
				msfz_field.getStore().removeAll();
				on_load_msfz();
			}
		} else {
			msfz_field.getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
			on_load_msfz();
		}
	},
	filterMedPersonalCombo: function() {
		var win = this;
		var base_form = win.MainPanel.getForm();

		var
			curDate = base_form.findField('EvnDirection_setDate').getValue(),
			index,
			MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue(),
			MedStaffFact_zid = base_form.findField('MedStaffFact_zid').getValue();

		base_form.findField('MedStaffFact_zid').getStore().clearFilter();
		base_form.findField('MedStaffFact_id').getStore().clearFilter();
		base_form.findField('MedStaffFact_zid').lastQuery = '';
		base_form.findField('MedStaffFact_id').lastQuery = '';

		if (!Ext6.isEmpty(curDate)) {
			base_form.findField('MedStaffFact_id').getStore().filterBy(function (rec) {
				return ( rec.get('WorkData_begDate') <= curDate && (Ext6.isEmpty(rec.get('WorkData_endDate')) || rec.get('WorkData_endDate') >= curDate ) );
			});
			base_form.findField('MedStaffFact_zid').getStore().filterBy(function (rec) {
				return ( rec.get('WorkData_begDate') <= curDate && (Ext6.isEmpty(rec.get('WorkData_endDate')) || rec.get('WorkData_endDate') >= curDate ) );
			});
		}

		if ( !Ext6.isEmpty(MedStaffFact_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_id);
			});

			if ( index == -1 ) {
				base_form.findField('MedStaffFact_id').clearValue();
				base_form.findField('MedPersonal_Code').setValue('');
			}
		}

		if ( !Ext6.isEmpty(MedStaffFact_zid) ) {
			index = base_form.findField('MedStaffFact_zid').getStore().findBy(function (rec) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_zid);
			});

			if ( index == -1 ) {
				base_form.findField('MedStaffFact_zid').clearValue();
			}
		}
	},
	actionEdit:function(status,response){
		if(status&&response&&response.responseText){
			var response_obj = Ext6.util.JSON.decode(response.responseText);
		}else{
			return false;
		}
		var base_form = this.MainPanel.getForm();

		var EvnDirection_id = response_obj.EvnDirection_id;
		//this.setTitle(WND_POL_EDIREDIT);
		base_form.findField('EvnDirection_id').setValue(EvnDirection_id);
		this.enableEdit(true);
		var loadMask = new Ext6.LoadMask(this, { msg: LOAD_WAIT });
		if (EvnDirection_id) {
			this.loadRecord({
					EvnDirection_id: EvnDirection_id,
					action: 'edit'
				},
				loadMask
			)
		}
		loadMask.hide();
		//base_form.findField('EvnDirection_setDate').focus(true, 250);
		this.queryById('printBtn').setVisible(true);
	},
	filterLpuSectionProfileCombo: function(options) {
		var win = this,
			base_form = this.MainPanel.getForm(),
			dir_combo = base_form.findField('DirType_id'),
			lsp_combo = base_form.findField('LpuSectionProfile_id'),
			LpuSectionProfile_id = options.LpuSectionProfile_id || lsp_combo.getValue(),
			setDate = options.EvnDirection_setDate || base_form.findField('EvnDirection_setDate').getValue(),
			ms_combo = base_form.findField('MedService_id'),
			ms_rec = ms_combo.getById(options.MedService_id || ms_combo.getValue()),
			LpuSectionProfileIdList = null,
			index;

		if (17 == dir_combo.getValue() && ms_rec && ms_rec.get('LpuSectionProfile_id_List')) {
			LpuSectionProfileIdList = ms_rec.get('LpuSectionProfile_id_List').split(',');
		}
		if (win.action.inlist(['view', 'editpaytype']) && LpuSectionProfile_id) {
			LpuSectionProfileIdList = [LpuSectionProfile_id];
		}
		if (win.action.inlist(['view', 'editpaytype']) && setDate) {
			setDate = null;
		}
		if (Ext6.isArray(LpuSectionProfileIdList) && 1 == LpuSectionProfileIdList.length && !LpuSectionProfile_id ) {
			LpuSectionProfile_id = LpuSectionProfileIdList[0];
		}
		// Фильтруем список профилей отделений
		lsp_combo.clearValue();
		lsp_combo.getStore().clearFilter();
		lsp_combo.lastQuery = '';

		lsp_combo.store.filterBy(function (rec) {
			if (Ext6.isArray(LpuSectionProfileIdList) && LpuSectionProfileIdList.length > 0
				&& !rec.get('LpuSectionProfile_id').toString().inlist(LpuSectionProfileIdList)
			) {
				return false;
			}
			if (!Ext6.isEmpty(setDate)) {
				return (Ext6.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
					&& (Ext6.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate);
			} else {
				return true;
			}
		});

		index = lsp_combo.getStore().findBy(function(rec) {
			return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
		});
		if ( index >= 0 ) {
			//LpuSectionProfile_id
		} else if(Ext6.isArray(LpuSectionProfileIdList) && 1 == LpuSectionProfileIdList.length) {
			LpuSectionProfile_id = LpuSectionProfileIdList[0];
		} else LpuSectionProfile_id = null;

		if ( LpuSectionProfile_id >= 0 ) {
			lsp_combo.setValue(LpuSectionProfile_id);
			lsp_combo.fireEvent('select', lsp_combo, lsp_combo.getStore().getAt(index));
		}
	},
	checkEvnDirectionIsReceive: function() {
		var base_form = this.MainPanel.getForm();

		base_form.findField('Diag_id').setAllowBlank(false);
		if (base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
			base_form.findField('Diag_id').setAllowBlank(true);
		}

		base_form.findField('Lpu_sid').hideContainer();
		base_form.findField('Lpu_sid').setAllowBlank(true);
		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_Num').setAllowBlank(true);
		if (base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
			base_form.findField('Lpu_sid').showContainer();
			base_form.findField('Lpu_sid').setAllowBlank(false);
			base_form.findField('EvnDirection_Num').setDisabled(this.action.inlist(['view', 'editpaytype']));
			base_form.findField('EvnDirection_Num').setAllowBlank(false);
			this.refreshFieldsVisibility(['Lpu_sid']);
		}

		//~ this.syncShadow();
	},
	filterMedicalCareFormType: function() {
		if (getRegionNick() == 'penza') {
			var base_form = this.MainPanel.getForm();
			// Если выбрано отделение из группы отделений типа «3. Дневной стационар при стационаре» или «4. Стационар на дому» или «5. Дневной стационар при поликлинике», то значение «экстренная» не доступно для выбора.
			// Если тип направления «на плановую госпитализация», то для выбора доступны только «плановая», «неотложная».
			// Если тип направления на «на экстренную госпитализацию», то доступны для выбора: «неотложная», «экстренная».
			var LpuUnitType_SysNick = base_form.findField('LpuSection_did').getFieldValue('LpuUnitType_SysNick');
			var DirType_Code = base_form.findField('DirType_id').getFieldValue('DirType_Code');
			base_form.findField('MedicalCareFormType_id').getStore().clearFilter();
			base_form.findField('MedicalCareFormType_id').lastQuery = '';
			base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
				switch(rec.get('MedicalCareFormType_id')) {
					case 1: // Экстренная
						return false;
						break;
					case 2: // Неотложная
						return true;
						break;
					case 3: // Плановая
						return (!DirType_Code || !DirType_Code.toString().inlist(['5'])); // не на экстренную
						break;
				}
			});

			// если значения больше нет в сторе очищаем поле
			var MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue();
			index = base_form.findField('MedicalCareFormType_id').getStore().findBy(function(rec) {
				if ( rec.get('MedicalCareFormType_id') == MedicalCareFormType_id ) {
					return true;
				}
				else {
					return false;
				}
			});
			if (index < 0) {
				base_form.findField('MedicalCareFormType_id').clearValue();
				base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
			}
		}
	},
	/** #refs #121771
	 * Изменяет обязательность поля LpuSection_did. Поле обязательно если МО работает в системе и для ее заведена структура
	 *
	 * @param Lpu_IsNotForSystem - работает ли МО в системе. 1 - работает в системе, 2 - не работает в системе
	 * @param countLpuSectionCombo - Количество отделений в структуре
	 */
	changeRequireField_LpuSection_did: function (Lpu_IsNotForSystem, countLpuSectionCombo) {

		var base_form = this.MainPanel.getForm();

		base_form.findField('LpuSection_did').setAllowBlank(true);
		if (countLpuSectionCombo != 0 && Lpu_IsNotForSystem == 1) {
			base_form.findField('LpuSection_did').setAllowBlank(false);
		}

	},

	filterLpuSectionCombo: function () {
		var win = this;

		var base_form = win.MainPanel.getForm(),
			LpuSectionCombo = base_form.findField('LpuSection_did'),
			LpuSection_id = base_form.findField('LpuSection_did').getValue();
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue(),
			Lpu_IsNotForSystem = base_form.findField('Lpu_did').getFieldValue('Lpu_IsNotForSystem'),
			LpuSectionProfile_Code = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code');


		if (!LpuSectionCombo.isVisible()) {
			return false;
		}

		LpuSectionCombo.getStore().clearFilter();
		LpuSectionCombo.lastQuery = '';

		var setComboValue = function(combo, id) {
			if ( Ext6.isEmpty(id) ) {
				return false;
			}

			var index = combo.getStore().findBy(function(rec) {
				return (rec.get('LpuSection_id') == id);
			});

			if ( index == -1 && combo.isVisible() ) {
				combo.clearValue();
			}
			else {
				combo.setValue(id);
			}

			combo.fireEvent('change', combo, combo.getValue());

			return true;
		}

		if (
			!Ext6.isEmpty(base_form.findField('Lpu_did').getValue())
			&& (
				LpuSectionCombo.getStore().getCount() == 0
				|| LpuSectionCombo.getStore().getAt(0).get('Lpu_id') != base_form.findField('Lpu_did').getValue()
			)
		) {
			if ( base_form.findField('Lpu_did').getValue() == getGlobalOptions().lpu_id ) {


				setLpuSectionGlobalStoreFilter({
					isOnlyStac: true,
					lpuSectionProfileCode: LpuSectionProfile_Code
				});
				LpuSectionCombo.getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));

				if(getRegionNick() == 'penza'){
					win.changeRequireField_LpuSection_did(Lpu_IsNotForSystem, LpuSectionCombo.getStore().getCount());
				}



				setComboValue(LpuSectionCombo, LpuSection_id);
			}
			else {
				LpuSectionCombo.getStore().load({
					params: {
						mode: 'combo',
						Lpu_id: base_form.findField('Lpu_did').getValue()
					},
					callback: function () {

						if(getRegionNick() == 'penza') {
							// ВАЖНО: функция должна выполнять до фильтра, иначе она не видит структуру
							win.changeRequireField_LpuSection_did(Lpu_IsNotForSystem, LpuSectionCombo.getStore().getCount());
						}

						// Фильтр
						LpuSectionCombo.getStore().filterBy(function (rec) {
							return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id && rec.get('LpuUnitType_SysNick') == 'stac');
						});

						setComboValue(LpuSectionCombo, LpuSection_id);

						win.refreshFieldsVisibility(['LpuUnitType_did']);
					}
				});
			}
		} else {
			LpuSectionCombo.getStore().filterBy(function (rec) {
				return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id && rec.get('LpuUnitType_SysNick') == 'stac');
			});

			setComboValue(LpuSectionCombo, LpuSection_id);
		}
	},

	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.MainPanel.getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();

		Ext6.Array.each(win.MainPanel.items.items, function(field, index, allitems)
			{
				if (!Ext6.isEmpty(fieldNames) && (typeof field.getName=='function' ? !field.getName().inlist(fieldNames) : true) ) return;

				var value = field.getValue();
				var allowBlank = null;
				var enable = null;
				var visible = null;
				var filter = null;

				var EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue();
				var EvnDirection_IsReceive = base_form.findField('EvnDirection_IsReceive').getValue();
				var Lpu_IsNotForSystem = base_form.findField('Lpu_did').getFieldValue('Lpu_IsNotForSystem');
				var DirType_Code = Number(base_form.findField('DirType_id').getFieldValue('DirType_Code'));
				var LpuUnitType_SysNick = String(base_form.findField('LpuUnitType_did').getFieldValue('LpuUnitType_SysNick'));
				var Lpu_did = base_form.findField('Lpu_did').getValue();
				var Org_oid = base_form.findField('Org_oid').getValue();
				var MedicalCareFormType_Code = Number(base_form.findField('MedicalCareFormType_id').getFieldValue('MedicalCareFormType_Code'))

				if (!Ext6.isEmpty(Org_oid)) Lpu_IsNotForSystem = 2;

				var set_or_cur_date = !Ext6.isEmpty(EvnDirection_setDate)?EvnDirection_setDate:new Date(new Date().format('Y-m-d')+' 00:00');

				switch(field.getName()) {
					case 'Lpu_did':
						enable = (
							win.isNotForSystem ||
							!( /*DirType_Code == 13 ||*/ EvnDirection_IsReceive == 2 || action.inlist(['editpaytype']))
						);
						if (Region_Nick.inlist(['penza'])) {
							filter = function(record) {
								var beg_date = !Ext6.isEmpty(record.get('Lpu_BegDate'))?record.get('Lpu_BegDate'):null;
								if (beg_date && !(beg_date instanceof Date)) {
									beg_date = Date.parseDate(beg_date, 'd.m.Y');
								}
								var end_date = !Ext6.isEmpty(record.get('Lpu_EndDate'))?record.get('Lpu_EndDate'):null;
								if (end_date && !(end_date instanceof Date)) {
									end_date = Date.parseDate(end_date, 'd.m.Y');
								}
								return (
									(!beg_date || beg_date <= set_or_cur_date) &&
									(!end_date || end_date > set_or_cur_date)
								);
							}
						}
						if (Region_Nick.inlist(['astra','ekb'])) {
							filter = function(record) {
								var end_date = !Ext6.isEmpty(record.get('Lpu_EndDate'))?record.get('Lpu_EndDate'):null;
								if (end_date && !(end_date instanceof Date)) {
									end_date = Date.parseDate(end_date, 'd.m.Y');
								}
								return (!win.isNotForSystem || record.get('Lpu_IsNotForSystem') == 2) && (!end_date || end_date > set_or_cur_date);
							}
						}
						visible = (DirType_Code != 26 && !win.isNotForSystem) && Ext6.isEmpty(Org_oid);
						allowBlank = !visible;
						break;
					case 'Org_oid':
						// filter = function (record) {
						// 	return (record.get('OrgType_id') == '11');
						// };
						visible = (DirType_Code == 26 || !!win.isNotForSystem) || !Ext6.isEmpty(Org_oid);
						allowBlank = !visible;
						break;
					case 'Lpu_sid':
						if (EvnDirection_IsReceive == 2 && Region_Nick.inlist(['astra','ekb'])) {
							filter = function(record) {
								var end_date = !Ext6.isEmpty(record.get('Lpu_EndDate'))?record.get('Lpu_EndDate'):null;
								if (end_date && !(end_date instanceof Date)) {
									end_date = Date.parseDate(end_date, 'd.m.Y');
								}
								return !end_date || end_date > set_or_cur_date;
							}
						}
						break;
					case 'MedService_id':
						visible = Ext6.isEmpty(Org_oid) && !win.isNotForSystem && Lpu_IsNotForSystem != 2;
						// allowBlank = !visible || !DirType_Code.inlist([16]);
						allowBlank = !visible || base_form.findField('DirType_id').getValue()!=17;
						break;
					case 'LpuUnitType_did':
						visible = false;

						if (Region_Nick.inlist(['astra','ekb','penza'])) {
							var LpuSectionCount = base_form.findField('LpuSection_did').getStore().getCount();

							visible = (
								DirType_Code.inlist([1,5]) &&
								(Lpu_IsNotForSystem == 2 || LpuSectionCount == 0)
							);
							if (visible) {

								filter = function(record){
									return String(record.get('LpuUnitType_SysNick')).inlist(['stac','dstac','hstac','pstac']);
								};

							}
						}

						allowBlank = !visible;
						break;
					case 'LpuSectionProfile_id':
						allowBlank = DirType_Code == 26;
						if (Region_Nick.inlist(['ekb']) && (Lpu_IsNotForSystem == 2 || !Ext6.isEmpty(Org_oid))) {
							allowBlank = !DirType_Code.inlist([1,5]) || !LpuUnitType_SysNick.inlist(['stac']);
						}
						else if (Region_Nick.inlist(['astra']) && (Lpu_IsNotForSystem == 2 || !Ext6.isEmpty(Org_oid))) {
							allowBlank = !DirType_Code.inlist([13]);
						}
						break;
					case 'MedSpec_fid':
						if ( Region_Nick.inlist(['astra']) ) {
							visible = (Lpu_IsNotForSystem == 2 || !Ext6.isEmpty(Org_oid)) && DirType_Code.inlist([2,3,10,13]);
							allowBlank = !(visible && DirType_Code == 13);
						}
						else if ( Region_Nick.inlist(['ekb']) ) {
							visible = (Lpu_IsNotForSystem == 2 || !Ext6.isEmpty(Org_oid)) && (
								DirType_Code.inlist([2,3,4,6,10,12,13]) ||
								DirType_Code.inlist([1]) && LpuUnitType_SysNick.inlist(['dstac','hstac','pstac'])
							);
							allowBlank = !(visible && (
								DirType_Code.inlist([12,13]) ||
								DirType_Code.inlist([1]) && LpuUnitType_SysNick.inlist(['dstac','hstac','pstac'])
							));
						}
						else {
							visible = (Lpu_IsNotForSystem == 2 || !Ext6.isEmpty(Org_oid)) && DirType_Code.inlist([2,3,10]);
							allowBlank = !visible;
						}
						break;
					case 'UslugaCategory_did':
						visible = !Ext6.isEmpty(value)
							|| (
								(Lpu_IsNotForSystem == 2 || !Ext6.isEmpty(Org_oid))
								&& (
									DirType_Code.inlist([2,9])
									|| (DirType_Code == 11 && Region_Nick.inlist(['ekb']))
									|| (DirType_Code == 13 && Region_Nick.inlist(['astra']))
								)
							);
						allowBlank = !(
							visible
							&& (
								(getRegionNick() == 'ekb' && DirType_Code == 9)
								|| (getRegionNick() == 'astra' && DirType_Code == 13)
							)
						);
						break;
					case 'UslugaComplex_did':
						visible = !Ext6.isEmpty(value)
							|| (
								(Lpu_IsNotForSystem == 2 || !Ext6.isEmpty(Org_oid))
								&& (
									DirType_Code.inlist([2,9])
									|| (DirType_Code == 11 && Region_Nick.inlist(['ekb']))
									|| (DirType_Code == 13 && Region_Nick.inlist(['astra']))
								)
							);
						allowBlank = !(
							visible
							&& (
								(getRegionNick() == 'ekb' && DirType_Code == 9)
								|| (getRegionNick() == 'astra' && DirType_Code == 13)
							)
						);
						break;
					case 'MedicalCareFormType_id':
						visible = Region_Nick.inlist(['penza']) && DirType_Code.inlist([1,5]);
						allowBlank = !visible;
						break;
				}

				if (visible === false) {
					value = null;
				}
				if (typeof filter == 'function' && field.store) {
					field.lastQuery = '';

					field.store.filterBy(filter);

					if (!Ext6.isEmpty(value) && field.store.find(field.store.key, value) == -1) {
						value = null;
					}
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
			}
		);

		//~ win.syncShadow();
		win.center();
	},

	setDisabled: function (fl)
	{
		var bform = this.MainPanel.getForm();
		bform.findField('EvnDirection_id').setDisabled(fl);
		bform.findField('ConsultingForm_id').setDisabled(fl);
		bform.findField('LpuSectionProfile_id').setDisabled(fl);
		bform.findField('LpuSection_did').setDisabled(fl);
		bform.findField('MedicalCareFormType_id').setDisabled(fl);
		bform.findField('StudyTarget_id').setDisabled(fl);
		bform.findField('Lpu_did').setDisabled(fl);
		bform.findField('Org_oid').setDisabled(fl);
		bform.findField('LpuUnitType_did').setDisabled(fl);
		bform.findField('Lpu_sid').setDisabled(fl);
		bform.findField('UslugaCategory_did').setDisabled(fl);
		bform.findField('UslugaComplex_did').setDisabled(fl);
		bform.findField('MedSpec_fid').setDisabled(fl);
		bform.findField('Lpu_sid').setDisabled(fl);
		bform.findField('EvnDirection_Num').setDisabled(fl);
		bform.findField('Diag_id').setDisabled(fl);
		bform.findField('EvnXml_id').setDisabled(fl);
		bform.findField('EvnDirectionOper_IsAgree').setDisabled(fl);
		bform.findField('MedStaffFact_id').setDisabled(fl);
		bform.findField('MedPersonal_Code').setDisabled(fl);
		bform.findField('MedStaffFact_zid').setDisabled(fl);
		bform.findField('EvnDirection_IsNeedOper').setDisabled(fl);
		bform.findField('EvnDirection_setDate').setDisabled(fl);
		bform.findField('EvnDirection_desDT').setDisabled(fl);
		bform.findField('EvnDirection_Descr').setDisabled(fl);
		bform.findField('EvnDirection_IsCito').setDisabled(fl);
		bform.findField('MedService_id').setDisabled(fl);
		bform.findField('Person_Surname').setDisabled(fl);
		bform.findField('Person_Firname').setDisabled(fl);
		bform.findField('Person_Secname').setDisabled(fl);
		bform.findField('Person_Birthday').setDisabled(fl);
		//bform.findField('EvnDirection_setDateTime').setDisabled(fl);
		bform.findField('DirType_id').setDisabled(fl);
		bform.findField('PayType_id').setDisabled(fl);
		bform.findField('ConsultationForm_id').setDisabled(fl);
	},
	printEvnDirection: function() {
		if ( this.action.inlist(['view', 'edit', 'editpaytype']) ) {
			var evndirection_id = this.MainPanel.getForm().findField('EvnDirection_id').getValue();

			this.printDir({
				EvnDirection_id: evndirection_id
			});
		}
	},
	printDir: function (params) {
		if (!params || !params.EvnDirection_id) {
			return false;
		}

		//getGlobalLoadMask('Получение данных направления...').show();
		var loadMask = new Ext6.LoadMask(this, { msg: langs('Получение данных направления...') });
		loadMask.show();
		Ext6.Ajax.request({
			url: '/?c=EvnDirection&m=getEvnDirectionForPrint',
			params: {
				EvnDirection_id: params.EvnDirection_id
			},
			callback: function(options, success, response)  {
				loadMask.hide();
				if (success) {
					var result  = Ext6.util.JSON.decode(response.responseText);

					if (getRegionNick() == 'kz') {
						if (result.DirType_Code && result.DirType_Code.inlist([1, 5])) {
							printBirt({
								'Report_FileName': 'rec_EvnDirection_Stac.rptdesign',
								'Report_Params': '&paramEvnDirection=' + params.EvnDirection_id,
								'Report_Format': 'pdf'
							});
						} else {
							printBirt({
								'Report_FileName': 'rec_EvnDirection_Usl.rptdesign',
								'Report_Params': '&paramEvnDirection=' + params.EvnDirection_id,
								'Report_Format': 'pdf'
							});
						}
					} else if (getRegionNick() == 'ekb' && (!result.DirType_Code || result.DirType_Code != 8)) {
						printBirt({
							'Report_FileName': 'HospNapr.rptdesign',
							'Report_Params': '&paramEvnDirection_id=' + params.EvnDirection_id,
							'Report_Format': 'pdf'
						});
					} else {

						var url = '/?c=EvnDirection&m=printEvnDirection&EvnDirection_id=' + params.EvnDirection_id;
						var addParams = '';
						// включена опция печати тестов с мнемоникой
						if (Ext.globalOptions.lis.PrintMnemonikaDirections)
						{
							addParams += '&PrintMnemonikaDirections=1';
						} // или просто опция печати исследований
						else if (Ext.globalOptions.lis.PrintResearchDirections)
						{
							addParams += '&PrintResearchDirections=1';
						}
						//если это исследование
						if (result.DirType_Code == 9)
						{
							var birtParams = {
								'Report_FileName': 'printEvnDirection.rptdesign',
								'Report_Params': '&paramEvnDirection=' + params.EvnDirection_id + addParams,
								'Report_Format': 'pdf'
							};

							if (
								getRegionNick() == 'perm' &&
								result.MedServiceType_SysNick != 'func' &&
								!Ext.isEmpty(Ext.globalOptions.lis.direction_print_form) &&
								Ext.globalOptions.lis.direction_print_form == 2
							) {
								birtParams.Report_FileName = 'printEvnDirectionCKDL.rptdesign';
							}
							printBirt(birtParams);
						} else window.open(url+addParams, '_blank');
					}
				}
			}
		});
	},
	/* Чтение записи EvnDirection */
	loadRecord: function(params, loadMask)
	{
		var win = this;
		var bform = win.MainPanel;
		var url = '/?c=EvnDirection&m=loadEvnDirectionEditForm',
			prms = { EvnDirection_id: params.EvnDirection_id };
		if (win.DirType_Code == 9) {
			prms.DirType_id = 10;
		}
		if (params.EvnQueue_id)
		{
			url = '/?c=Queue&m=loadEvnDirectionEditForm';
			prms = { EvnQueue_id: params.EvnQueue_id };
		}
		if (params.action != 'add')
		{
			bform.load(
				{
					params: prms,
					failure: function()
					{
						loadMask.hide();
						Ext6.Msg.show(
							{
								buttons: Ext6.Msg.OK,
								fn: function()
								{
									form.hide();
								},
								icon: Ext6.Msg.ERROR,
								msg: langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'),
								title: langs('Ошибка')
							});
					},
					success: function(frm, opt)
					{
						win.directionNumberParams.EvnDirection_Num = bform.getForm().findField('EvnDirection_Num').getValue();

						// Здесь в обратке выполняем все необходимые операции после загрузки данных
						params.EvnDirection_id = bform.getForm().findField('EvnDirection_id').getValue();
						params.ConsultingForm_id = bform.getForm().findField('ConsultingForm_id').getValue();
						params.LpuSectionProfile_id = bform.getForm().findField('LpuSectionProfile_id').getValue();
						params.Lpu_did = bform.getForm().findField('Lpu_did').getValue();
						params.LpuUnitType_did = bform.getForm().findField('LpuUnitType_did').getValue();
						params.EvnDirection_Num = bform.getForm().findField('EvnDirection_Num').getValue();
						params.Diag_id = bform.getForm().findField('Diag_id').getValue();
						params.MedPersonal_id = bform.getForm().findField('MedPersonal_id').getValue();
						params.MedStaffFact_id = bform.getForm().findField('MedStaffFact_id').getValue();
						params.LpuSection_id = bform.getForm().findField('LpuSection_id').getValue();
						params.Post_id = bform.getForm().findField('Post_id').getValue();
						params.MedPersonal_zid = bform.getForm().findField('MedPersonal_zid').getValue();
						params.EvnDirection_setDate = bform.getForm().findField('EvnDirection_setDate').getValue();
						params.EvnDirection_Descr = bform.getForm().findField('EvnDirection_Descr').getValue();
						params.EvnDirection_IsCito = bform.getForm().findField('EvnDirection_IsCito').getValue();
						params.MedService_id = bform.getForm().findField('MedService_id').getValue();
						params.Resource_id = bform.getForm().findField('Resource_id').getValue();
						params.Person_Surname = bform.getForm().findField('Person_Surname').getValue();
						params.Person_Firname = bform.getForm().findField('Person_Firname').getValue();
						params.Person_Secname = bform.getForm().findField('Person_Secname').getValue();
						params.Person_Birthday = bform.getForm().findField('Person_Birthday').getValue();
						params.Org_oid = bform.getForm().findField('Org_oid').getValue();
						params.ConsultationForm_id = bform.getForm().findField('ConsultationForm_id').getValue();

						// если есть зубы
						var ToothNums = bform.getForm().findField('ToothNums').getValue();
						if (ToothNums) {

							var toothsPanel = bform.queryById(win.id + "_" + 'ToothNumFieldsPanel');
							toothsPanel.fillPanelByData({panelValues: ToothNums});
						}

						bform.getForm().findField('Lpu_did').fireEvent('change',bform.getForm().findField('Lpu_did'),bform.getForm().findField('Lpu_did').getValue());
						bform.getForm().findField('MedicalCareFormType_id').fireEvent('change', bform.getForm().findField('MedicalCareFormType_id'), bform.getForm().findField('MedicalCareFormType_id').getValue());

						this.loadParams(params, loadMask);

					}.createDelegate(this),
					url: url
				});
		}
		else
		{
			this.loadParams(params, loadMask);
		}
	},
	/* Установка полей EvnDirection */
	loadParams: function(params, loadMask)
	{
		var win = this;
		var base_form = win.MainPanel.getForm();
		if ( params.Diag_id != null && params.Diag_id.toString().length > 0 )
		{
			base_form.findField('Diag_id').getStore().load({
				callback: function() {
					base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getValue());
				},
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + params.Diag_id }
			});
		}
		if( params.MedService_id != null && base_form.findField('MedService_id').getStore().getCount() == 0) {
			base_form.findField('MedService_id').getStore().load({
				callback: function() {
					base_form.findField('MedService_id').setValue(base_form.findField('MedService_id').getValue());
				}
			});
		}

		if ( base_form.findField('EvnDirection_setDateTime').getValue() ) {
			base_form.findField('EvnDirection_setDateTime').setContainerVisible(true);
			base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(false);
			base_form.findField('EvnDirection_desDT').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
			base_form.findField('EvnDirection_desDT').setAllowBlank(true);
		} else {
			// вообще скрываем это поле, если времени нет, значит направление в очередь
			base_form.findField('EvnDirection_setDateTime').setContainerVisible(false);
			base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(true);
			base_form.findField('EvnDirection_desDT').setContainerVisible(base_form.findField('DirType_id').getValue() == 20 || getRegionNick().inlist(['astra','ekb','penza']) || (getRegionNick() == 'krym' && !Ext6.isEmpty(base_form.findField('DirType_id').getValue()) && base_form.findField('DirType_id').getValue().inlist([ 1, 5 ])));

			var allowBlank = true;

			switch ( getRegionNick() ) {
				case 'astra':
				case 'penza':
					allowBlank = (base_form.findField('DirType_id').getValue() != 1);
					break;

				case 'krym':
					allowBlank = (Ext6.isEmpty(base_form.findField('DirType_id').getValue()) || !base_form.findField('DirType_id').getValue().inlist([ '1', '5' ]));
					break;
			}

			base_form.findField('EvnDirection_desDT').setAllowBlank(allowBlank);
		}

		if(getRegionNick()=='ufa' && (17 == base_form.findField('DirType_id').getValue()) ) base_form.findField('ConsultingForm_id').setAllowBlank(false);
		base_form.findField('ConsultingForm_id').setContainerVisible(base_form.findField('DirType_id').getValue() == 17);
		base_form.findField('LpuSection_did').setContainerVisible(base_form.findField('DirType_id').getValue() == 5);
		base_form.findField('LpuSection_did').setAllowBlank(base_form.findField('DirType_id').getValue() != 5 || getRegionNick() != 'kareliya');
		base_form.findField('EvnXml_id').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
		base_form.findField('EvnDirectionOper_IsAgree').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
		win.queryById('EDEW_AnestButton').setVisible(base_form.findField('DirType_id').getValue() == 20);
		win.queryById('EDEW_OperButton').setVisible(base_form.findField('DirType_id').getValue() == 20);
		base_form.findField('EvnXml_id').getStore().removeAll();
		if (base_form.findField('DirType_id').getValue() == 20 && !Ext6.isEmpty(base_form.findField('EvnDirection_pid').getValue())) {
			// грузим операционные эпикризы
			base_form.findField('EvnXml_id').getStore().load({
				params: {
					Evn_id: base_form.findField('EvnDirection_pid').getValue(),
					XmlType_ids: Ext6.util.JSON.encode([3,10])
				},
				callback: function() {
					base_form.findField('EvnXml_id').setValue(base_form.findField('EvnXml_id').getValue()); // а могли и удалить уже эпикриз то..
				}
			});
		}

		var usluga_category = base_form.findField('UslugaCategory_did');
		var usluga_complex = base_form.findField('UslugaComplex_did');

		if (!Ext6.isEmpty(usluga_category.getValue())) {
			usluga_category.getStore().load({
				params: {UslugaComplex_id: usluga_category.getValue()},
				callback: function() {
					usluga_category.setValue(usluga_category.getValue());
				}
			});
			usluga_complex.setUslugaCategoryList([usluga_category.getFieldValue('UslugaCategory_SysNick')]);
		}
		if (!Ext6.isEmpty(usluga_complex.getValue())) {
			usluga_complex.getStore().load({
				params: {UslugaComplex_id: usluga_complex.getValue()},
				callback: function() {
					usluga_complex.setValue(usluga_complex.getValue());
				}
			});
		}

		if(params.Org_oid){
			var Org_oidCombo = base_form.findField('Org_oid');
			var Lpu_didCombo = base_form.findField('Lpu_did');
			//isNotForSystem
			var Org_oid = Org_oidCombo.getValue();
			if(Org_oid){
				Org_oidCombo.getStore().load({
					callback: function(records, options, success) {
						Org_oidCombo.clearValue();
						if ( success ) {
							Org_oidCombo.setValue(Org_oid);
							if(!Lpu_didCombo.getValue()) Lpu_didCombo.setContainerVisible(false);
							Org_oidCombo.setContainerVisible(true);
						}
					},
					params: {
						Org_id: Org_oid
					}
				});
			}
		}

		this.checkEvnDirectionIsReceive();

		//~ win.syncShadow();

		this.loadMedPersonalCombo();

		loadMask.hide();
		var dir_type_combo = base_form.findField('DirType_id');
		dir_type_combo.fireEvent('change', dir_type_combo, dir_type_combo.getValue());
		base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());

		if(dir_type_combo.getValue().inlist([1,5])&&getRegionNick() == 'ekb'){
			base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(true);
		}else{
			base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(false);
		}

		//~ this.buttons[this.buttons.length - 1].focus();
	},
	initComponent: function() { //TAG: конструктор
		var win = this,
			v;

		win.MainPanel = new Ext6.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 20px 5px 30px 30px',
			border: false,
			defaults: {
				labelWidth: 160,
				width: 650
			},
			region: 'center',
			items: [
				{
					name: 'EvnDirection_id',
					value: 0,
					xtype: 'hidden'
				},{
					name: 'EvnDirection_IsReceive',
					xtype: 'hidden'
				},{
					name: 'DirectType_id',
					value: 0,
					xtype: 'hidden'
				},{
					name: 'DirectClass_id',
					value: 0,
					xtype: 'hidden'
				},{
					name: 'EvnDirection_pid',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'DopDispInfoConsent_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'LpuSection_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'LpuUnitType_SysNick',
					value: '',
					xtype: 'hidden'
				}, {
					name: 'Post_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'MedPersonal_zid',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'TimetableMedService_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Resource_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'TimetableResource_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PrescriptionType_Code',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPrescr_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'TimetableGraf_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'TimetableStac_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUsluga_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnQueue_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'QueueFailCause_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: -1,
					xtype: 'hidden'
				}, {
					name: 'Person_Surname',
					xtype: 'hidden'
				}, {
					name: 'Person_Firname',
					xtype: 'hidden'
				}, {
					name: 'Person_Secname',
					xtype: 'hidden'
				},{
					name: 'Person_Birthday',
					xtype: 'hidden'
				}, {
					name: 'ARMType_id',
					xtype: 'hidden'
				},{
					name: 'ToothNums',
					value: '',
					xtype: 'hidden'
				}, {
					width: 271+20,
					//~ allowDecimals: false,
					//~ allowNegative: false,
					userCls: 'notrigger',
					disabled: true,
					fieldLabel: langs('Номер'),
					name: 'EvnDirection_Num',
					tabIndex: TABINDEX_EDIREF + 1,
					//~ autoCreate: {tag: "input", type: "text", maxLength: "6",  autocomplete: "off"},
					xtype: 'numberfield',
					minValue: 1,

				},
				{
					width: 271+20,
					allowBlank: false,
					fieldLabel: langs('Дата'),
					format: 'd.m.Y',
					name: 'EvnDirection_setDate',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: TABINDEX_EDIREF + 2,
					xtype: 'datefield',
					listeners: {
						'change': function(field, newValue, oldValue) {
							//~ blockedDateAfterPersonDeath('personpanelid', 'EDirEF_PersonInformationFrame', field, newValue, oldValue);
							var date = Ext6.util.Format.date(newValue, 'd.m.Y'),
								base_form = win.MainPanel.getForm();

							win.getEvnDirectionNumber();

							win.filterMedPersonalCombo();
							win.filterLpuSectionProfileCombo({
								EvnDirection_setDate: newValue
							});

							if (getRegionNick().inlist([ 'krym', 'penza' ])) {
								base_form.findField('EvnDirection_desDT').setMinValue(newValue);
							}

							win.refreshFieldsVisibility(['Lpu_did','Lpu_sid']);
						}
					}
				}, {
					allowBlank: true,
					tabIndex: TABINDEX_EDIREF + 3,
					useCommonFilter: true,
					xtype: 'swPayTypeCombo',
					name: 'PayType_id'
				}, {
					xtype: 'swConsultingFormCombo',
					name: 'ConsultingForm_id',
					allowBlank: true
				}, {
					allowBlank: false,
					name: 'DirType_id',
					tabIndex: TABINDEX_EDIREF + 3,
					xtype: 'swDirTypeBaseJournalCombo',
					listeners:
						{
							change: function(combo,nv,ov)
							{
								var base_form = win.MainPanel.getForm(),
									lpu_combo = base_form.findField('Lpu_did'),
									ms_combo = base_form.findField('MedService_id'),
									rcc_combo = base_form.findField('RemoteConsultCause_id'),
									descr_field = base_form.findField('EvnDirection_Descr'),
									cito_field = base_form.findField('EvnDirection_IsCito'),
									lsp_combo = base_form.findField('LpuSectionProfile_id'),
									studytarget_combo = base_form.findField('StudyTarget_id'),
									uslugacomplex_combo = base_form.findField('UslugaComplex_did'),
									MedicalCareFormType = base_form.findField('MedicalCareFormType_id'),
									consultationForm_combo = base_form.findField('ConsultationForm_id');

								if (getRegionNick() == 'astra' && nv == 17) {
									uslugacomplex_combo.setAllowedUslugaComplexAttributeList([ 'telemed' ]);
								}
								else {
									uslugacomplex_combo.setAllowedUslugaComplexAttributeList();
								}
								if (getRegionNick() == 'penza' && nv == 5)
								{
									MedicalCareFormType.setValue(2);
									MedicalCareFormType.setDisabled(true);
								} else if (getRegionNick() == 'penza')
								{
									MedicalCareFormType.setDisabled(false);
								}

								// Если госпитализация экстренная - узкое место
								if (lpu_combo.getValue() != getGlobalOptions().lpu_id) {
									//добавил условие в рамках задачи http://redmine.swan.perm.ru/issues/23877
									base_form.findField('MedStaffFact_zid').setAllowBlank((nv==5));
								}
								ms_combo.getStore().removeAll();
								if (17 == nv) {
									// направление в ЦУК
									ms_combo.setContainerVisible(true);
									rcc_combo.setContainerVisible(true);
									cito_field.setContainerVisible(true);
									consultationForm_combo.setContainerVisible(true);
									//~ win.syncShadow();
									descr_field.setFieldLabel(langs('Вопросы:'));
									ms_combo.setAllowBlank(false);
									rcc_combo.setAllowBlank(false);
									cito_field.setAllowBlank(false);
									if(getRegionNick() != 'kz') {
										consultationForm_combo.setAllowBlank(false);
									}
									//нельзя изменить службу, если она была выбрана до открытия этой формы или форма открыта для просмотра
									ms_combo.setDisabled(win.isWasChosenRemoteConsultCenter || win.action.inlist(['view', 'editpaytype']));
									rcc_combo.setDisabled(win.action.inlist(['view', 'editpaytype']));
									cito_field.setDisabled(win.action.inlist(['view', 'editpaytype']));
									consultationForm_combo.setDisabled(win.action.inlist(['view', 'editpaytype']));
									ms_combo.getStore().proxy.extraParams = {
										isDirection: 1,
										setDate: base_form.findField('EvnDirection_setDate').getRawValue() || getGlobalOptions().date,
										Lpu_id: lpu_combo.getValue(),//must be == getGlobalOptions().lpu_id
										MedServiceType_SysNick: 'remoteconsultcenter'
									};

									lpu_combo.getStore().proxy.setExtraParam('MedServiceType_SysNick','remoteconsultcenter');


									if (!win.isWasChosenRemoteConsultCenter && win.action == 'add') {
										ms_combo.setValue(null);
										lsp_combo.setValue(null);
									} else {
										if(ms_combo.getValue())
											ms_combo.store.proxy.setExtraParam('MedService_id', ms_combo.getValue());
									}
									ms_combo.getStore().load({params: {}, callback: function(){
											var old_value = ms_combo.getValue();
											if (-1 != ms_combo.getStore().findBy(function(rec) { return rec.get('MedService_id') == old_value;})) {
												ms_combo.setValue(old_value);
											} else {
												ms_combo.setValue(null);
											}
											// ms_combo.fireEvent('change', ms_combo, ms_combo.getValue(), old_value);
										}});
								} else {
									ms_combo.setContainerVisible(false);
									rcc_combo.setContainerVisible(false);
									cito_field.setContainerVisible(false);
									consultationForm_combo.setContainerVisible(false);
									//~ win.syncShadow();
									descr_field.setFieldLabel(langs('Обоснование:'));
									ms_combo.setAllowBlank(true);
									rcc_combo.setAllowBlank(true);
									cito_field.setAllowBlank(true);
									consultationForm_combo.setAllowBlank(true);
									ms_combo.setDisabled(win.action.inlist(['view', 'editpaytype']));
									rcc_combo.setDisabled(true);
									cito_field.setDisabled(win.action.inlist(['view', 'editpaytype']));
									consultationForm_combo.setDisabled(true);
									win.filterLpuSectionProfileCombo({});
								}

								studytarget_combo.setContainerVisible(nv==10);
								studytarget_combo.setAllowBlank(nv!=10 || !uslugacomplex_combo.getFieldValue('UslugaComplex_AttributeList') || uslugacomplex_combo.getFieldValue('UslugaComplex_AttributeList').indexOf('func') < 0);
								if (win.action == 'add' && Ext6.isEmpty(studytarget_combo.getValue()) && studytarget_combo.isVisible()) {
									studytarget_combo.setValue(2)
								}

								win.getEvnDirectionNumber();
								win.filterMedicalCareFormType();
								win.refreshFieldsVisibility([
									'Lpu_did', 'Org_oid', 'MedService_id', 'LpuUnitType_did', 'LpuSectionProfile_id',
									'MedSpec_fid', 'UslugaCategory_did', 'UslugaComplex_did', 'MedicalCareFormType_id'
								]);
							}
						}
				}, {
					comboSubject: 'MedicalCareFormType',
					name: 'MedicalCareFormType_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.refreshFieldsVisibility(['LpuUnitType_did']);
						}
					},
					fieldLabel: 'Форма помощи',
					lastQuery: '',
					prefix: 'nsi_',
					xtype: 'commonSprCombo'
				}, {
					fieldLabel: 'Цель исследования',
					xtype: 'commonSprCombo',
					allowBlank: false,
					name: 'StudyTarget_id',
					value: 2,
					comboSubject: 'StudyTarget'
				},
				{
					ownerWindow: win,
					xtype: 'numberfield',
					fieldLabel: 'Номер зуба',
					name: 'ToothNumEvnUsluga_ToothNum',
					itemId: win.id + '_' + 'ToothNumFieldsPanel',
					viewMode: true
				},
				{
					xtype: 'baseCombobox',
					allowBlank: false,
					itemId: 'lpucombo',
					fieldLabel: langs('МО направления'),
					name : 'Lpu_did',
					displayField: 'Lpu_Nick',
					codeField: 'Lpu_Nick',
					valueField: 'Lpu_id',
					queryMode: 'local',
					anyMatch: true,
					/*tpl: new Ext6.XTemplate( //красиво, но наверно не в тему
						'<tpl for="."><div class="selectlpu-combo-item x6-boundlist-item">',
						'<div class="selectlpu-combo-nick">{Lpu_Nick}</div>',
						'<div class="selectlpu-combo-address">{Address}</div>',
						'</div></tpl>'
					),*/
					store: new Ext6.create('Ext6.data.Store', {
						getById: function(id) {
							var indx = this.findBy(function(rec) {if(rec.data.Lpu_id == id) return rec;});
							if(indx>=0) return this.getAt(indx); else return false;
						},
						fields: [
							{name: 'Lpu_id', mapping: 'Lpu_id'},
							{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
							{name: 'Lpu_Name', mapping: 'Lpu_Name'},
							{name: 'Address', mapping: 'Address'}
						],
						autoLoad: false,
						sorters: {
							property: 'Lpu_Nick',
							direction: 'ASC'
						},
						proxy: {
							type: 'ajax',
							actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
							url : '/?c=User&m=getLpuList',
							reader: {
								type: 'json'
							}
						},
						mode: 'local',

					}),
					enableKeyEvents: true,
					listeners: {
						'blur': function(combo) {
							var base_form = win.MainPanel.getForm();
							var ms_combo = base_form.findField('MedService_id');
							if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
								combo.clearValue();
								ms_combo.clearValue();
								ms_combo.getStore().removeAll();
							} else {
								var newvalue = combo.getValue();
								if(newvalue == getGlobalOptions().lpu_id){
									base_form.findField('MedStaffFact_zid').setAllowBlank(true);
								} else {
									base_form.findField('MedStaffFact_zid').setAllowBlank(false);
								}
								win.filterLpuSectionCombo();
								win.refreshFieldsVisibility([
									'MedService_id','LpuUnitType_did','LpuSectionProfile_id',
									'MedSpec_fid','UslugaCategory_did','UslugaComplex_did'
								]);

								var ms_value = ms_combo.getValue();
								ms_combo.getStore().proxy.setExtraParam('Lpu_id', newvalue);
								ms_combo.getStore().reload({
									callback: function() {
										if(ms_value) {
											if( -1 == ms_combo.store.findBy(function(rec) {
												return rec.get('MedService_id') == ms_value;
											})) {
												ms_combo.clearValue();
											}
										}
									}
								});
							}
						}
					}
				},
				{
					fieldLabel: langs('Организация направления'),
					name: 'Org_oid',
					ctxSerach: true,
					tabIndex: TABINDEX_EDIREF + 5,
					triggers: {
						search: {
							handler: function () {
								var combo = this;
								var base_form = win.MainPanel.getForm();
								var DirType_Code = base_form.findField('DirType_id').getFieldValue('DirType_Code');

								if (combo.disabled) {
									return false;
								}

								getWnd('swOrgSearchWindowExt6').show({
									object: 'org',
									isNotForSystem: DirType_Code != 26 ? win.isNotForSystem : null,
									defaultOrgType: DirType_Code != 26 ? 11 : null,
									onClose: function () {
										combo.focus(true, 200)
									},
									onSelect: function (org_data) {
										if (!Ext6.isEmpty(org_data.Org_id)) {
											combo.setValue(org_data.Org_id);
											combo.fireEvent('change', combo, org_data.Org_id);
											getWnd('swOrgSearchWindowExt6').hide();
											combo.collapse();
											win.refreshFieldsVisibility([
												'MedService_id', 'LpuUnitType_did', 'LpuSectionProfile_id',
												'MedSpec_fid', 'UslugaCategory_did', 'UslugaComplex_did'
											]);
										}
									}
								});
							}
						}
					},
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.refreshFieldsVisibility([
								'MedService_id', 'LpuUnitType_did', 'LpuSectionProfile_id',
								'MedSpec_fid', 'UslugaCategory_did', 'UslugaComplex_did'
							]);
						}
					},
					xtype: 'swOrgCombo'
				}, {
					fieldLabel: 'Условия оказания медицинской помощи',
					name: 'LpuUnitType_did',
					tabIndex: TABINDEX_EDIREF + 5.5,
					xtype: 'swLpuUnitTypeCombo',
					listeners: {
						change: function(combo, newValue, oldValue) {
							win.refreshFieldsVisibility(['LpuSectionProfile_id','MedSpec_fid']);
						}
					}
				}, {
					disabled: true,
					allowBlank: true,
					fieldLabel:  'Служба',
					name: 'MedService_id',
					tabIndex: TABINDEX_EDIREF + 6,
					xtype: 'swMedService2Combo',
					value: null,
					listeners:{
						change: function(combo, newValue) {
							win.filterLpuSectionProfileCombo({MedService_id: newValue});
						}
					}
				}, {
					disabled: true,
					allowBlank: false,
					name: 'LpuSectionProfile_id',
					fieldLabel: langs('Профиль'),
					tabIndex: TABINDEX_EDIREF + 6,
					xtype: 'swLpuSectionProfileCombo',
					listeners:{
						change: function(combo, newValue, oldValue){
							win.filterLpuSectionCombo();
							var base_form = win.MainPanel.getForm(),
								ms_combo = base_form.findField('MedService_id'),
								old_value = ms_combo.getValue();
							if (base_form.findField('DirType_id').getValue() == 17 && !win.isWasChosenRemoteConsultCenter && win.action == 'add') {
								//чтобы можно было снова выбирать профиль:
								/*	ms_combo.getStore().proxy.extraParams = {
										LpuSectionProfile_id: newValue,
										isDirection: 1,
										setDate: base_form.findField('EvnDirection_setDate').getRawValue() || getGlobalOptions().date,
										Lpu_id: base_form.findField('Lpu_did').getValue(),//must be == getGlobalOptions().lpu_id
										MedServiceType_SysNick: 'remoteconsultcenter'
									};
									ms_combo.clearValue();
									ms_combo.getStore().clearFilter();
									ms_combo.lastQuery = '';
									ms_combo.getStore().load({callback: function(){
										if (ms_combo.getById(old_value)) {
											ms_combo.setValue(old_value);
										} else {
											ms_combo.setValue(null);
										}
									}});*/
							}
						}
					}
				}, {
					editable: true,
					xtype: 'swMedSpecFedCombo',
					name: 'MedSpec_fid',
					fieldLabel: 'Специальность врача',
				}, {
					xtype: 'swUslugaCategoryCombo',
					name: 'UslugaCategory_did',
					fieldLabel: 'Категория услуги',
					listeners: {
						'select': function(combo, record, index) {
							var base_form = win.MainPanel.getForm();

							base_form.findField('UslugaComplex_did').clearValue();
							base_form.findField('UslugaComplex_did').getStore().removeAll();

							if (!record) {
								base_form.findField('UslugaComplex_did').setUslugaCategoryList(); //TODO: добавить метод
							} else {
								base_form.findField('UslugaComplex_did').setUslugaCategoryList([record.get('UslugaCategory_SysNick')]);
							}
						}
					}
				}, {
					xtype: 'swUslugaComplexCombo',
					name: 'UslugaComplex_did',
					fieldLabel: 'Услуга',
				}, {
					name: 'LpuSection_did',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (combo.isVisible()) {
								win.filterMedicalCareFormType();
							}
							win.refreshFieldsVisibility(['LpuUnitType_did']);
						}
					},
					tabIndex: TABINDEX_EDIREF + 6,
					xtype: 'SwLpuSectionGlobalCombo'
				}, {
					disabled: true,
					allowBlank: true,
					fieldLabel: langs('Цель консультации'),
					name: 'RemoteConsultCause_id',
					tabIndex: TABINDEX_EDIREF + 6,
					xtype: 'commonSprCombo',
					comboSubject: 'RemoteConsultCause',
					autoLoad: false,
					value: null
				}, {
					fieldLabel: langs('Форма оказания консультации'),
					xtype: 'commonSprCombo',
					comboSubject: 'ConsultationForm',
					name: 'ConsultationForm_id',
					autoLoad: true,
					allowBlank: true
				}, {
					layout: 'column',
					border: false,
					padding: '0 0 5 0',
					items: [{
						labelWidth: 160,
						fieldLabel: langs('Время записи'),
						name: 'EvnDirection_noSetDateTime',
						xtype: 'trigger',
						userCls: 'x-form-clock-trigger',
						value: langs('Очередь'),
						plugins: [ new Ext6.ux.InputTextMask('99.99.9999 99:99', false) ],
						readOnly: true,
						stripCharsRe: new RegExp('__.__.____ __:__'),
						validationEvent: 'blur',
						onTriggerClick: function() {
							var field = win.MainPanel.getForm().findField('EvnDirection_noSetDateTime');
							if (!field.disabled)
								win.openRecordWindow();
						}.createDelegate(this)

					}, {
						labelWidth: 160,
						fieldLabel: langs('Желаемая дата'),
						name: 'EvnDirection_desDT',
						xtype: 'datefield',
						plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					}, {
						labelWidth: 160,
						fieldLabel: langs('Время записи'),
						name: 'EvnDirection_setDateTime',
						xtype: 'trigger',
						userCls: 'x-form-clock-trigger',
						disabled: true,
						plugins: [ new Ext6.ux.InputTextMask('99.99.9999 99:99', false) ],
						readOnly: true,
						stripCharsRe: new RegExp('__.__.____ __:__'),
						tabIndex: TABINDEX_EDIREF + 7,
						validationEvent: 'blur',

						onTriggerClick: function() {
							var field = win.MainPanel.getForm().findField('EvnDirection_setDateTime');
							if (!field.disabled)
								getWnd('swTagSelectWindow').show({
									callback: Ext6.emptyFn,
									onHide: Ext6.emptyFn
								});
						}.createDelegate(this)
					}
					]
				},
				{
					checkAccessRights: true,
					allowBlank: false,
					name: 'Diag_id',
					userCls: 'diagnoz',
					tabIndex: TABINDEX_EDIREF + 11,
					xtype: 'swDiagCombo'
				}, {
					checkAccessRights: true,
					allowBlank: true,
					name: 'EvnXml_id',
					tabIndex: TABINDEX_EDIREF + 11,
					valueField: 'EvnXml_id',
					displayField: 'EvnXml_Name',

					store: Ext6.create('Ext6.data.Store', {
						type: 'json',
						model: Ext6.create('Ext6.data.Model', {
							fields: [{
								name: 'UslugaComplex_Name',
								type: 'string'
							},{
								name: 'UslugaComplex_id',
								type: 'int'
							}]
						}),
						autoLoad: false,
						folderSort: true,
						proxy: {
							type: 'ajax',
							actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
							url: '/?c=EvnXml&m=loadEvnXmlCombo',
							reader: {
								type: 'json'
							}
						},
						pageSize: null
					}),
					xtype: 'baseCombobox',
					fieldLabel: 'Предоперационный эпикриз' // выбор предоперационного эпикриза из прикрепленных к случаю лечения эпикризов
				}, {
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						border: false,
						items: [{
							xtype: 'checkbox',
							name: 'EvnDirectionOper_IsAgree',
							value: 2,
							tabIndex: TABINDEX_EDIREF + 11,
							allowBlank: true,
							width: 100,
							fieldLabel: langs('Согласие пациента')
						}]
					}, {
						layout: 'form',
						border: false,
						bodyStyle: 'margin-left:5px;',
						items: [{
							xtype: 'button',
							itemId: 'EDEW_AnestButton',
							text: langs('на анестезиологическое обеспечение'),
							handler: function () {
								var base_form = win.MainPanel.getForm();

								var paramPerson = base_form.findField('Person_id').getValue();
								var paramLpu = getGlobalOptions().lpu_id;

								if ( getRegionNick() == 'kz' ) {
									printBirt({
										'Report_FileName': 'Person_soglasie_stac_anst.rptdesign',
										'Report_Params': '&paramPerson=' + paramPerson + '&paramLpu=' + paramLpu,
										'Report_Format': 'pdf'
									});
								} else {
									printBirt({
										'Report_FileName': 'Person_soglasie_stac_anst.rptdesign',
										'Report_Params': '&paramPerson=' + paramPerson,
										'Report_Format': 'pdf'
									});
								}
							}
						}]
					}, {
						layout: 'form',
						border: false,
						bodyStyle: 'margin-left:5px;',
						items: [{
							xtype: 'button',
							itemId: 'EDEW_OperButton',
							text: langs('на операцию'),
							handler: function () {
								var base_form = win.MainPanel.getForm();

								var paramPerson = base_form.findField('Person_id').getValue();
								var paramLpu = getGlobalOptions().lpu_id;

								if ( getRegionNick() == 'kz' ) {
									printBirt({
										'Report_FileName': 'PersonInfoSoglasie_OperStac.rptdesign',
										'Report_Params': '&paramPerson=' + paramPerson + '&paramLpu=' + paramLpu,
										'Report_Format': 'pdf'
									});
								} else {
									printBirt({
										'Report_FileName': 'PersonInfoSoglasie_OperStac.rptdesign',
										'Report_Params': '&paramPerson=' + paramPerson,
										'Report_Format': 'pdf'
									});
								}
							}
						}]
					}]
				}, {
					layout: 'form',
					border: false,
					style: "margin-left:156px",
					items: [{
						xtype: 'checkbox',
						hideLabel: true,
						name: 'EvnDirection_IsNeedOper',
						name: 'EvnDirection_IsNeedOper',
						hidden: false,
						boxLabel: langs('Необходимость операционного вмешательства')
					}]
				}, {
					fieldLabel: langs('Обоснование:'),
					height: 64,
					name: 'EvnDirection_Descr',
					tabIndex: TABINDEX_EDIREF + 12,
					xtype: 'textarea'
				}, {
					fieldLabel: langs('Направившая МО'),
					name: 'Lpu_sid',
					ctxSerach: true,
					tabIndex: TABINDEX_EDIREF + 13,
					xtype: 'swLpuCombo',
					listeners:
						{
							change: function(combo, newValue) {
								var base_form = win.MainPanel.getForm();
								base_form.findField('MedPersonal_id').setValue(null);
								base_form.findField('LpuSection_id').setValue(null);
								base_form.findField('MedStaffFact_id').setValue(null);
								base_form.findField('MedPersonal_Code').setValue(null);
								base_form.findField('MedPersonal_zid').setValue(null);
								base_form.findField('MedStaffFact_zid').setValue(null);

								if(base_form.findField('EvnDirection_IsReceive').getValue()==2) {
									var Lpu_IsNotForSystem = base_form.findField('Lpu_sid').getFieldValue('Lpu_IsNotForSystem') != 2;// 1 - не работает в системе, 2 - работает
									base_form.findField('MedStaffFact_id').setAllowBlank( getRegionNick() == 'buryatiya' || Lpu_IsNotForSystem);
									if(getRegionNick() == 'ekb') {
										var dir_type_combo = base_form.findField('DirType_id');
										var dir_type_id = parseInt(dir_type_combo.value);
										base_form.findField('MedPersonal_Code').setAllowBlank( !dir_type_id.inlist([1,5,10,16]) || Lpu_IsNotForSystem );
									}
								}

								win.loadMedPersonalCombo();
							}
						}
				}, {
					allowBlank: (getRegionNick() == 'buryatiya'),
					fieldLabel: langs('Врач'),
					name: 'MedStaffFact_id',
					listWidth: 670,
					lastQuery: '',
					tabIndex: TABINDEX_EDIREF + 13,
					xtype: 'swMedStaffFactCombo', //'SwMedStaffFactGlobalCombo'
					listeners: {
						'collapse' : function(combo) {
							if (getRegionNick() == 'ekb') {
								var base_form = win.MainPanel.getForm();
								var DloCode = base_form.findField('MedPersonal_Code');
								if(combo.getValue()) {
									DloCode.setValue( combo.getFieldValue('MedPersonal_DloCode') );
								} else DloCode.setValue('');
							}
						}
					}
				}, {
					allowBlank: true,
					fieldLabel: langs('Код врача'),
					name: 'MedPersonal_Code',
					xtype: 'textfield',
					enableKeyEvents: true,
					listeners: {
						'keyup': function (inp, e) {
							var base_form = win.MainPanel.getForm();
							var Lpu_IsNotForSystem = base_form.findField('Lpu_sid').getFieldValue('Lpu_IsNotForSystem')!=2;// 1 - не работает в системе, 2 - работает
							if(!Lpu_IsNotForSystem) {
								var DloCode = base_form.findField('MedPersonal_Code');
								var combo = base_form.findField('MedStaffFact_id');
								combo.getStore().clearFilter(true);
								if(DloCode.getValue().length>0) {
									var doctor = {id:0, count:0};
									for(i=0; i<combo.getStore().totalLength; i++) {
										if(combo.getStore().getAt(i).data.MedPersonal_DloCode == DloCode.getValue()) {
											doctor.id = combo.getStore().getAt(i).data.MedStaffFact_id;
											doctor.count++;
										}
									}
									if(doctor.count==1) {
										base_form.findField('MedStaffFact_id').setValue( doctor.id );
									} else combo.clearValue();
								} else combo.clearValue();
							}
						}
					}
				}, {
					allowBlank: false,
					fieldLabel: langs('Зав. отделением'),
					name: 'MedStaffFact_zid',
					listWidth: 670,
					lastQuery: '',
					tabIndex: TABINDEX_EDIREF + 14,
					xtype: 'swMedStaffFactCombo'
				}, {
					disabled: true,
					padding: '0 0 0 165',
					boxLabel: 'Cito!',
					name: 'EvnDirection_IsCito',
					tabIndex: TABINDEX_EDIREF + 6,
					xtype: 'checkbox',
					autoLoad: false,
					inputValue: 2,
					uncheckedValue: 1
				}
			],//TAG: конец конструктора
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'EvnDirection_id' },
						{ name: 'EvnDirection_IsReceive' },
						{ name: 'Lpu_sid' },
						{ name: 'EvnDirection_pid' },
						{ name: 'ConsultingForm_id' },
						{ name: 'DopDispInfoConsent_id' },
						{ name: 'MedService_id' },
						{ name: 'RemoteConsultCause_id' },
						{ name: 'TimetableMedService_id' },
						{ name: 'Resource_id' },
						{ name: 'TimetableResource_id' },
						{ name: 'PrescriptionType_Code' },
						{ name: 'EvnPrescr_id' },
						{ name: 'TimetableGraf_id' },
						{ name: 'TimetableStac_id' },
						{ name: 'Person_id' },
						{ name: 'PersonEvn_id' },
						{ name: 'EvnDirection_IsNeedOper' },
						{ name: 'Server_id' },
						{ name: 'Person_Surname' },
						{ name: 'Person_Firname' },
						{ name: 'Person_Secname' },
						{ name: 'Person_Birthday' },
						{ name: 'EvnDirection_Num' },
						{ name: 'EvnDirection_setDate' },
						{ name: 'EvnDirection_desDT' },
						{ name: 'PayType_id' },
						{ name: 'DirType_id' },
						{ name: 'MedicalCareFormType_id' },
						{ name: 'StudyTarget_id' },
						{ name: 'Lpu_did' },
						{ name: 'Org_oid' },
						{ name: 'LpuSectionProfile_id' },
						{ name: 'LpuSection_did' },
						{ name: 'LpuUnitType_did' },
						{ name: 'LpuUnitType_SysNick' },
						{ name: 'EvnDirection_setDateTime' },
						{ name: 'UslugaCategory_did' },
						{ name: 'UslugaComplex_did' },
						{ name: 'MedSpec_fid' },
						{ name: 'Diag_id' },
						{ name: 'EvnDirection_Descr' },
						{ name: 'EvnDirection_IsCito' },
						{ name: 'MedStaffFact_id' },
						{ name: 'MedPersonal_id' },
						{ name: 'LpuSection_id' },
						{ name: 'Post_id' },
						{ name: 'MedPersonal_zid' },
						{ name: 'ARMType_id' },
						{ name: 'EvnXml_id' },
						{ name: 'EvnDirectionOper_IsAgree' },
						{ name: 'ToothNums' },
						{ name: 'ConsultationForm_id' }
					]
				})
			}),
			url: '/?c=EvnDirection&m=saveEvnDirection'
		});

		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			border: false,
			buttons:
				[ '->',
					{
						userCls:'buttonCanсel buttonPoupup',
						text: langs('Печать'),
						itemId: 'printBtn',
						handler: function() {
							win.printEvnDirection();
						}
					}, {
					userCls:'buttonCanсel buttonPoupup',
					text: langs('Отмена'),
					itemId: 'cancelBtn',
					handler: function() {
						win.hide();
					}
				}, {
					userCls:'buttonAccept buttonPoupup',
					text: langs('Сохранить'),
					itemId: 'saveBtn',
					handler: function() {
						var base_form = win.MainPanel.getForm();
						var setDate = base_form.findField('EvnDirection_setDate').getValue(),
							curDate = new Date();

						curDate.setHours(0,0,0,0);

						if (curDate > setDate) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.YESNO,
								fn: function(buttonId) {
									if (buttonId == 'yes')
										win.doSave();
								},
								icon: Ext6.Msg.WARNING,
								msg: "Проверьте дату выписки направления. При постановке в очередь в направлении должна быть указана текущая дата. Продолжить?",
								title: ERR_INVFIELDS_TIT
							});
						} else {
							win.doSave();
						}
					}
				}]
		});

		win.callParent(arguments);
		
		// #15304 Настроим фильтрацию в комбобоксе "Организация направления":
		if ((v = win.MainPanel.getForm()) &&
			(v = v.findField('Org_oid')) &&
			(v = v.getStore()) &&
			(v = v.getProxy()))
		{
			if (!v.extraParams)
				v.extraParams = {};
			
			// Только медицинские организации:
			v.extraParams.OrgType_id = 11;
			
			// Только организации, не работающие в системе:
			v.extraParams.isNotForSystem = 1;
		}
	}
});