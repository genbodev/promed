Ext6.define('common.Stick.EvnStickWorkReleaseEditWindowController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnStickWorkReleaseEditWindowController',  
	filterMedStaffFact: function (params, none, binding){
		var base_form = this.getView().FormPanel.getForm(),
			action = this.getView().action,
			msf_params =
				{
					allowLowLevel: 'yes',
					onDate: Ext6.util.Format.date(new Date(), 'd.m.Y')
				};
		
		msf_params.dateFrom = Ext6.util.Format.date(params.EvnStickWorkRelease_begDate, 'd.m.Y');
		msf_params.dateTo = Ext6.util.Format.date(params.EvnStickWorkRelease_endDate, 'd.m.Y');		
		msf_params.LpuSection_id = (params.LpuSection_id && ! isNaN(params.LpuSection_id ) ) ? params.LpuSection_id : null;
		
		var msfValue = base_form.findField('MedStaffFact_id').getValue();
		
		base_form.findField('MedStaffFact_id').getStore().removeAll();
		setMedStaffFactGlobalStoreFilter(msf_params);
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		
		if(params.LpuSection_id && base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id')!=params.LpuSection_id) {
			base_form.findField('MedStaffFact_id').clearValue();
		}
	}
});

Ext6.define('common.Stick.EvnStickWorkReleaseEditWindow', {
	alias: 'widget.swEvnStickWorkReleaseEditWindowExt6',
	controller: 'EvnStickWorkReleaseEditWindowController',
	addCodeRefresh: Ext6.emptyFn,
	closeToolText: 'Закрыть',
	title: 'Освобождение от работы',
	maximized: false,
	width: 780,
	autoHeight: true,
	modal: true,
	closable: true,
	cls: 'arm-window-new emk-forms-window',
	extend: 'base.BaseForm',
	renderTo: main_center_panel.body.dom,
	
	Post_id: null,
	formStatus: 'edit',
	
	listeners: {
		hide: function ()
		{
			typeof this.onHideFn === 'function' ? this.onHideFn() : null;
			return;
		}
	},
	refreshEndDateLimit: function() {
		var win = this,
			base_form = this.FormPanel.getForm(),
			minValue = base_form.findField('EvnStickWorkRelease_begDate').getValue(),
			maxValue;
		

		if( minValue ) {
			base_form.findField('EvnStickWorkRelease_endDate').setMinValue(minValue);
			maxValue = new Date(minValue);
			maxValue.setDate(maxValue.getDate() + 14);
		}

		if (
			maxValue
			&& !(
				win.StickCause_SysNick == 'pregn'
				|| (win.StickParentClass = 'EvnPS' && win.isHasDvijeniaInStac24 == true)
				|| (win.EvnStick_stacBegDate && minValue && win.EvnStick_stacBegDate.getTime() == minValue.getTime() && win.EvnStick_stacEndDate)
				|| win.EvnStick_IsOriginal == 2
			)
		) {
			base_form.findField('EvnStickWorkRelease_endDate').setMaxValue(maxValue);
		}
	},
	setPostValue: function() {
        var base_form = this.FormPanel.getForm();
        var _this = this;

        var PostMed_Code = base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code');

        var index = base_form.findField('Post_id').getStore().findBy(function(rec, id) {
        	return ( rec.get('PostMed_Code') == PostMed_Code )
		});

		var record = base_form.findField('Post_id').getStore().getAt(index);

		if(record){
			base_form.findField('Post_id').setValue(record.get('PostMed_id'));
		} else base_form.findField('Post_id').clearValue();
    },
	
	checkVK: function() {
		var win = this,
			base_form = this.FormPanel.getForm(),
			isPerm = (getRegionNick() == 'perm'),
			isKZ = (getRegionNick() == 'kz'),
			newSumDate = this.sumDate;
		
		if ( !Ext6.isEmpty(base_form.findField('EvnStickWorkRelease_begDate').getValue()) && !Ext6.isEmpty(base_form.findField('EvnStickWorkRelease_endDate').getValue()) ) {
			var newSumDate = this.sumDate + Math.round((base_form.findField('EvnStickWorkRelease_endDate').getValue() - base_form.findField('EvnStickWorkRelease_begDate').getValue()) / 86400000)+1;
		}

		if (isKZ) {
			if (newSumDate >= 7) {
				base_form.findField('MedStaffFact2_id').allowBlank = false;
			} else {
				base_form.findField('MedStaffFact2_id').allowBlank = true;
			}
			if (newSumDate > 20) {
				// для черновика поле скрыто
				if (!base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
					base_form.findField('EvnStickWorkRelease_IsPredVK').setValue(1);
					base_form.findField('EvnStickWorkRelease_IsPredVK').fireEvent('change', base_form.findField('EvnStickWorkRelease_IsPredVK'), base_form.findField('EvnStickWorkRelease_IsPredVK').getValue());
				}
				base_form.findField('EvnStickWorkRelease_IsPredVK').disable();
			} else {
				if (win.action != 'view') {
					base_form.findField('EvnStickWorkRelease_IsPredVK').enable();
				}
			}
			if (win.StickCause_SysNick == 'abort') {
				base_form.findField('MedStaffFact2_id').allowBlank = false;
			}
		} else {
			var maxDays = 15;
			// log(base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code'));
			// проверяем должность врача, для Фельдшер или зубной врач макс. продолжительность 10 дней, а не 15.
			// а также если был ЛВН закрытый в предыдущий день в стационаре.
			if (( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' && base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code') && base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code').inlist([115,117])) || this.MaxDaysLimitAfterStac) {
				maxDays = 10;
			}

			// все 3 врача должны быть заполнены если общий период более 15 дней. (refs #6511, #8492) если код нетрудоспособности Входит в перечень.
			// или если выбран дубликат ЛВН (refs #10085)
			if (//win.EvnStick_IsOriginal == 2 || (newSumDate > maxDays && (
				win.EvnStick_IsOriginal == 2 
				|| (
					newSumDate > maxDays
					&& (this.StickParentClass == 'EvnPL' || (this.StickParentClass == 'EvnPS' && !this.isHasDvijeniaInStac24))
					&& (
					win.StickCause_SysNick == 'desease'
					|| win.StickCause_SysNick == 'trauma'
					|| win.StickCause_SysNick == 'accident'
					|| win.StickCause_SysNick == 'protstac'
					|| win.StickCause_SysNick == 'prof'
					|| win.StickCause_SysNick == 'dolsan'
					|| win.StickCause_SysNick == 'uhodnoreb'
					|| win.StickCause_SysNick == 'inoe'
					|| win.StickCause_SysNick == 'uhodreb'
					|| win.StickCause_SysNick == 'rebinv'
				))
			) {
				// для черновика поле скрыто
				if (!base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
					base_form.findField('EvnStickWorkRelease_IsPredVK').setValue(1);
					base_form.findField('EvnStickWorkRelease_IsPredVK').fireEvent('change', base_form.findField('EvnStickWorkRelease_IsPredVK'), base_form.findField('EvnStickWorkRelease_IsPredVK').getValue());
				}
				base_form.findField('EvnStickWorkRelease_IsPredVK').disable();
			} else {
				base_form.findField('MedStaffFact2_id').allowBlank = true;
				if (win.action != 'view') {
					base_form.findField('EvnStickWorkRelease_IsPredVK').enable();
				}
			}

			if (!getRegionNick().inlist(['kz','by']) && win.EvnStick_IsOriginal == 2) {
				base_form.findField('MedStaffFact2_id').allowBlank = true;
			}
		}

		// для черновика поля не обязательны и вообще скрыты
		if (base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
			base_form.findField('MedStaffFact2_id').allowBlank = true;
		}

		base_form.findField('MedStaffFact2_id').validate();
		base_form.findField('MedStaffFact3_id').validate();
	},
	
	refreshGlobalCombos: function() {
		var win = this,
			base_form = this.FormPanel.getForm(),
			ls_params = new Object(),
			msf_params = new Object(),
			msf3_params = new Object();

		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var med_staff_fact2_id = base_form.findField('MedStaffFact2_id').getValue();
		var med_staff_fact3_id = base_form.findField('MedStaffFact3_id').getValue();
		var beg_date = base_form.findField('EvnStickWorkRelease_begDate').getValue();
		var end_date = base_form.findField('EvnStickWorkRelease_endDate').getValue();

		base_form.findField('LpuSection_id').clearValue();
		base_form.findField('MedStaffFact_id').clearValue();
		base_form.findField('MedStaffFact2_id').clearValue();

		base_form.findField('LpuSection_id').getStore().removeAll();
		base_form.findField('MedStaffFact_id').getStore().removeAll();
		base_form.findField('MedStaffFact2_id').getStore().removeAll();

		if (!Ext6.isEmpty(lpu_section_id)) {
			setLpuSectionGlobalStoreFilter({id: lpu_section_id}, sw4.swLpuSectionGlobalStore);
			base_form.findField('LpuSection_id').getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
		}

		if (!Ext6.isEmpty(med_staff_fact_id)) {
			setMedStaffFactGlobalStoreFilter({id: med_staff_fact_id}, sw4.swMedStaffFactGlobalStore);
			base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		}
		if (!Ext6.isEmpty(med_staff_fact2_id)) {
			setMedStaffFactGlobalStoreFilter({id: med_staff_fact2_id}, sw4.swMedStaffFactGlobalStore);
			base_form.findField('MedStaffFact2_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		}
		if (!Ext6.isEmpty(med_staff_fact3_id)) {
			setMedStaffFactGlobalStoreFilter({id: med_staff_fact3_id}, sw4.swMedStaffFactGlobalStore);
			base_form.findField('MedStaffFact3_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
		}

		if(this.CurLpuSection_id != 0)
		{
			ls_params.LpuSection_id = this.CurLpuSection_id;
		}
		if(this.CurLpuUnit_id != 0)
		{
			ls_params.LpuUnit_id = this.CurLpuUnit_id;
		}
		if(this.CurLpuBuilding_id != 0)
		{
			ls_params.LpuBuilding_id = this.CurLpuBuilding_id;
		}
		ls_params.dateFrom = Ext6.util.Format.date(beg_date, 'd.m.Y');
		ls_params.dateTo = Ext6.util.Format.date(end_date, 'd.m.Y');

		msf_params.dateFrom = Ext6.util.Format.date(beg_date, 'd.m.Y');
		msf_params.dateTo = Ext6.util.Format.date(end_date, 'd.m.Y');

		msf3_params.dateFrom = Ext6.util.Format.date(beg_date, 'd.m.Y');
		msf3_params.dateTo = Ext6.util.Format.date(end_date, 'd.m.Y');

		if ( this.arrayLpuUnitType.length > 0 ) {
			ls_params.arrayLpuUnitType = this.arrayLpuUnitType;
			msf_params.arrayLpuUnitType = this.arrayLpuUnitType;
		}

		setLpuSectionGlobalStoreFilter(ls_params, sw4.swLpuSectionGlobalStore);
		setMedStaffFactGlobalStoreFilter(msf_params, sw4.swMedStaffFactGlobalStore);

		base_form.findField('LpuSection_id').getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore), true);

		msf_params.allowDuplacateMSF = true;
		if(this.IngoreMSFFilter == 0)
			setMedStaffFactGlobalStoreFilter(msf_params, sw4.swMedStaffFactGlobalStore);

		msf_params = new Object();
		if(this.CurLpuSection_id != 0)
		{
			msf_params.LpuSection_id = this.CurLpuSection_id;
		}
		if(this.CurLpuUnit_id != 0)
		{
			msf_params.LpuUnit_id = this.CurLpuUnit_id;
		}
		if(this.CurLpuBuilding_id != 0)
		{
			msf_params.LpuBuilding_id = this.CurLpuBuilding_id;
		}
		msf_params.dateFrom = Ext6.util.Format.date(beg_date, 'd.m.Y');
		msf_params.dateTo = Ext6.util.Format.date(end_date, 'd.m.Y');

		if(this.CurLpuBuilding_id != 0 || this.CurLpuUnit_id != 0 || this.CurLpuSection_id != 0)
		{
			setMedStaffFactGlobalStoreFilter(msf_params, sw4.swMedStaffFactGlobalStore);
		}


		base_form.findField('MedStaffFact2_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore), true);
		if (getRegionNick() != 'kz' && !getGlobalOptions().isMedStatUser && this.StickReg == 0) {
			if ( this.userMedStaffFactId ) {
				msf_params.id = this.userMedStaffFactId;
			}
			else if ( this.userMedStaffFactList != null && typeof this.userMedStaffFactList == 'object' && this.userMedStaffFactList.length > 0 ) {
				msf_params.ids = this.userMedStaffFactList;
			}
		}
		if ( this.arrayLpuUnitType.length > 0 ) msf_params.arrayLpuUnitType = this.arrayLpuUnitType;
		
		setMedStaffFactGlobalStoreFilter(msf_params, sw4.swMedStaffFactGlobalStore);
		base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore), true);
		if (getRegionNick() == 'kareliya') {
			msf3_params.withoutLpuSection = true;
		} else {
			msf3_params.all = true;
		}
		setMedStaffFactGlobalStoreFilter(msf3_params, sw4.swMedStaffFactGlobalStore);
		base_form.findField('MedStaffFact3_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore), true);

		if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
			base_form.findField('LpuSection_id').setValue(lpu_section_id);
			base_form.findField('LpuSection_id').fireEvent('blur', base_form.findField('LpuSection_id'), lpu_section_id);
			//~ win.getController().filterMedStaffFact({LpuSection_id: lpu_section_id, MedStaffFact_id: ''});//ext6: вместо fireEvent
		}

		if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
			base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
			base_form.findField('MedStaffFact_id').fireEvent('blur', base_form.findField('MedStaffFact_id'), med_staff_fact_id);
		}
		else if ( base_form.findField('MedStaffFact_id').getStore().getCount() == 1 ) {
			base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id'));
			base_form.findField('MedStaffFact_id').fireEvent('blur', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id'));
		}
		if (base_form.findField('MedStaffFact2_id').getStore().getById(med_staff_fact2_id)) {
			base_form.findField('MedStaffFact2_id').setValue(med_staff_fact2_id);
			base_form.findField('MedStaffFact2_id').fireEvent('blur', base_form.findField('MedStaffFact2_id'), med_staff_fact2_id);
		}
		if (base_form.findField('MedStaffFact3_id').getStore().getById(med_staff_fact3_id)) {
			base_form.findField('MedStaffFact3_id').setValue(med_staff_fact3_id);
			base_form.findField('MedStaffFact3_id').fireEvent('blur', base_form.findField('MedStaffFact3_id'), med_staff_fact3_id);
		}
	},
	
	show: function(){
		var win = this;
		win.callParent(arguments);
		win.center();

		var base_form = win.FormPanel.getForm();
		base_form.reset();

		win.action = null;
		win.arrayLpuUnitType = new Array();
		win.callback = Ext6.emptyFn;
		win.hideEvnStickWorkReleaseIsDraft = false;
		win.evnStickType = 0;
		win.formMode = 'local';
		win.formStatus = 'edit';
		win.disableBegDate = false;
		win.begDate = null;
		win.endDate = null;
		win.maxDate = null;
		win.sumDate = 0;
		win.StickCause_SysNick = '';
		win.EvnStick_IsOriginal = 1;
		win.StickOrder_Code = 0;
		win.StickPerson_Firname = '';
		win.StickPerson_Secname = '';
		win.StickPerson_Surname = '';
		win.StickPerson_Birthday = '';
		win.EvnStick_setDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		
		
		win.parentClass = null;
		win.UserLpuSection_id = null;
		win.UserLpuSectionList = new Array();
		win.userMedStaffFactId = null;
		win.userMedStaffFactList = new Array();
		win.MaxDaysLimitAfterStac = false;
		win.Post_id = null;
        win.StickReg = 0;
        win.CurLpuSection_id = 0;
        win.CurLpuUnit_id = 0;
        win.CurLpuBuilding_id = 0;
        win.IngoreMSFFilter = 0;
        win.isTubDiag = false;
		if ( !arguments[0] || !arguments[0].formParams ) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { win.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);
		base_form.findField('EvnStickWorkRelease_IsDraft').fireEvent('change', base_form.findField('EvnStickWorkRelease_IsDraft'), base_form.findField('EvnStickWorkRelease_IsDraft').getValue());
		base_form.findField('EvnStickWorkRelease_IsPredVK').fireEvent('change', base_form.findField('EvnStickWorkRelease_IsPredVK'), base_form.findField('EvnStickWorkRelease_IsPredVK').getValue());
		
		var Org_id= arguments[0].formParams.Org_id;
		if (!Ext6.isEmpty(Org_id)) {
			base_form.findField('Org_id').setRawValue(Org_id);
			base_form.findField('Org_id').getStore().load({
				params: {
					OrgType: 'lpu',
					Org_id: Org_id
				},
				callback: function()
				{
					base_form.findField('Org_id').setValue(Org_id);
				}
			});
		}

		if ( arguments[0].action ) {
			win.action = arguments[0].action;
		}

        if( arguments[0].StickReg) {
            win.StickReg = arguments[0].StickReg;
        }

        if( arguments[0].CurLpuSection_id) {
            win.CurLpuSection_id = arguments[0].CurLpuSection_id;
        }
        if( arguments[0].CurLpuUnit_id) {
            win.CurLpuUnit_id = arguments[0].CurLpuUnit_id;
        }
        if( arguments[0].CurLpuBuilding_id) {
            win.CurLpuBuilding_id = arguments[0].CurLpuBuilding_id;
        }
        if( arguments[0].IngoreMSFFilter) {
            win.IngoreMSFFilter = arguments[0].IngoreMSFFilter;
        }

		if ( arguments[0].MaxDaysLimitAfterStac ) {
			win.MaxDaysLimitAfterStac = arguments[0].MaxDaysLimitAfterStac;
		}

		if ( arguments[0].hideEvnStickWorkReleaseIsDraft ) {
			win.hideEvnStickWorkReleaseIsDraft = true;
		}

		if ( arguments[0].callback ) {
			win.callback = arguments[0].callback;
		}

		if ( arguments[0].evnStickType ) {
			win.evnStickType = arguments[0].evnStickType;
		}
		
		if ( arguments[0].EvnStick_IsOriginal ) {
			win.EvnStick_IsOriginal = arguments[0].EvnStick_IsOriginal;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			win.formMode = 'remote';
		}

		if ( !Ext6.isEmpty(arguments[0].disableBegDate) ) {
			win.disableBegDate = arguments[0].disableBegDate;
		}
		if ( arguments[0].begDate ) {
			win.begDate = arguments[0].begDate;
		}
		if ( arguments[0].endDate ) {
			win.endDate = arguments[0].endDate;
		}

		if ( arguments[0].maxDate ) {
			win.maxDate = arguments[0].maxDate;
		}
		
		if ( arguments[0].sumDate ) {
			win.sumDate = arguments[0].sumDate;
		}
		
		if ( arguments[0].StickCause_SysNick ) {
			win.StickCause_SysNick = arguments[0].StickCause_SysNick;
		}
		
		if ( arguments[0].StickOrder_Code ) {
			win.StickOrder_Code = arguments[0].StickOrder_Code;
		}
		
		if ( arguments[0].EvnStick_setDate ) {
			win.EvnStick_setDate = arguments[0].EvnStick_setDate;
		}
		
		if ( arguments[0].StickPerson_Birthday ) {
			win.StickPerson_Birthday = arguments[0].StickPerson_Birthday;
		}
		
		if ( arguments[0].StickPerson_Firname ) {
			win.StickPerson_Firname = arguments[0].StickPerson_Firname;
		}
		
		if ( arguments[0].StickPerson_Secname ) {
			win.StickPerson_Secname = arguments[0].StickPerson_Secname;
		}
		
		if ( arguments[0].StickPerson_Surname ) {
			win.StickPerson_Surname = arguments[0].StickPerson_Surname;
		}

		if ( arguments[0].formParams.Post_id ) {
			win.Post_id = arguments[0].formParams.Post_id;
		}

		if ( arguments[0].isTubDiag ) {
			win.isTubDiag = true;
		}
		
		if ( !Ext6.isEmpty(arguments[0].onHide) && typeof arguments[0].onHide == 'function' ) {
			win.onHideFn = arguments.onHide;
		}

		if ( arguments[0].parentClass ) {
			win.parentClass = arguments[0].parentClass;
		}
		
		if ( arguments[0].EvnStick_stacBegDate ) {
			win.EvnStick_stacBegDate = arguments[0].EvnStick_stacBegDate;
		}

		if ( arguments[0].EvnStick_stacEndDate ) {
			win.EvnStick_stacEndDate = arguments[0].EvnStick_stacEndDate;
		}

		if ( arguments[0].EvnStick_IsOriginal ) {
			win.EvnStick_IsOriginal = arguments[0].EvnStick_IsOriginal;
		}

		if ( arguments[0].isHasDvijeniaInStac24 ) {
			win.isHasDvijeniaInStac24 = arguments[0].isHasDvijeniaInStac24;
		}

		if (arguments[0].parentClass ) {
			win.StickParentClass = arguments[0].parentClass;
		}

		// признак, что в одном из освобождений от работы указано место работы из МО текущего пользователя #135678
		if( arguments[0].MedStaffFactInUserLpu ) {
			win.MedStaffFactInUserLpu = arguments[0].MedStaffFactInUserLpu;
		}

		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 ) {
			win.userMedStaffFactId = arguments[0].UserMedStaffFact_id;
		}
		// если в настройках есть medstafffact, то имеем список мест работы
		else if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 ) {
			win.userMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}

		// является ли ЛВН электронным
		if ( arguments[0].isELN) {
			win.isELN = arguments[0].isELN;
		}

		// определенный LpuSection
		if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 ) {
			win.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		// если в настройках есть lpusection, то имеем список мест работы
		else if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 ) {
			win.UserLpuSectionList = Ext.globalOptions.globals['lpusection'];
		}

		win.mask(LOAD_WAIT);
		
		base_form.findField('EvnStickWorkRelease_IsDraft').setContainerVisible(win.hideEvnStickWorkReleaseIsDraft == false);
		base_form.findField('EvnStickWorkRelease_IsSpecLpu').setContainerVisible(getRegionNick() == 'kz' && win.isTubDiag == true);

		base_form.findField('LpuSection_id').getStore().removeAll();
		//base_form.findField('MedPersonal2_id').getStore().removeAll();
		//base_form.findField('MedPersonal3_id').getStore().removeAll();
		base_form.findField('MedStaffFact_id').getStore().removeAll();
		base_form.findField('MedStaffFact2_id').getStore().removeAll();
		base_form.findField('MedStaffFact3_id').getStore().removeAll();
		base_form.findField('Override30Day').setValue('false');

		base_form.findField('EvnStickWorkRelease_begDate').setMinValue(null);
		base_form.findField('EvnStickWorkRelease_begDate').setMaxValue(null);
		base_form.findField('EvnStickWorkRelease_endDate').setMinValue(null);
		base_form.findField('EvnStickWorkRelease_endDate').setMaxValue(null);

		base_form.findField('EvnStickWorkRelease_begDate').enable();
		
		if (win.parentClass=='EvnPL') {
			win.arrayLpuUnitType = [ '1', '7', '8', '11' ];
		} else if (win.parentClass=='EvnPS') {
			win.arrayLpuUnitType = [ '2', '3', '4', '5' ];
		}

		if (win.isELN) {
			base_form.findField('EvnStickWorkRelease_IsDraft').hide();
		} else {
			base_form.findField('EvnStickWorkRelease_IsDraft').show();
		}

		switch ( win.action ) {
			case 'add':
				win.enableEdit(true);

				win.checkVK();

				var setDateMaxValue = true;
				
				if (win.StickOrder_Code == 2) {
					setDateMaxValue = false;
				}
				setCurrentDateTime({
					callback: function() {
						// Если максимальная дата задана, значит уже есть выписанные освобождения, отталкиваемся от них при выписке следующих
						
						if ( win.maxDate ) {						
							base_form.findField('EvnStickWorkRelease_begDate').setMinValue(win.maxDate.add(Date.DAY, 1));
							//base_form.findField('EvnStickWorkRelease_begDate').setMaxValue(win.maxDate.add(Date.DAY, 1));
							base_form.findField('EvnStickWorkRelease_begDate').setMaxValue(null);
							base_form.findField('EvnStickWorkRelease_begDate').setValue(win.maxDate.add(Date.DAY, 1));
							base_form.findField('EvnStickWorkRelease_endDate').setMinValue(win.maxDate.add(Date.DAY, 1));
							base_form.findField('EvnStickWorkRelease_endDate').setMaxValue(null);
						}
						if ( win.begDate ) {
							base_form.findField('EvnStickWorkRelease_begDate').setValue(win.begDate);
						}
						if ( win.endDate && (getRegionNick() != 'kz' || (getRegionNick() == 'kz' && win.StickCause_SysNick != 'pregn'))) {
							base_form.findField('EvnStickWorkRelease_endDate').setValue(win.endDate);
						}
						if ( win.disableBegDate ) {
							//~ base_form.findField('EvnStickWorkRelease_begDate').disable();//TAG:диз
						}
						//~ base_form.findField('EvnStickWorkRelease_begDate').resumeEvent('change');
						//~ base_form.findField('EvnStickWorkRelease_endDate').resumeEvent('change');
						
						base_form.findField('EvnStickWorkRelease_begDate').fireEvent('blur', base_form.findField('EvnStickWorkRelease_begDate'), base_form.findField('EvnStickWorkRelease_begDate').getValue());

						win.unmask();

						//base_form.clearInvalid();

						base_form.findField('EvnStickWorkRelease_begDate').focus(true, 250);
					}.createDelegate(this),
					dateField: base_form.findField('EvnStickWorkRelease_begDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: setDateMaxValue,
					addMaxDateDays: 1,
					windowId: win.id
				});
				
				if(getRegionNick() != 'kz'){
					var MedStaffFactCombo = base_form.findField('MedStaffFact_id');
					MedStaffFactCombo.setValue(win.userMedStaffFactId);
				}
			break;

			case 'edit':
			case 'view':
				if ( win.action == 'edit' ) {
					win.enableEdit(true);
				}
				else {
					win.enableEdit(false);
					win.queryById('button_cancel').enable();
				}

				if ( win.maxDate ) {
					base_form.findField('EvnStickWorkRelease_begDate').setMinValue(win.maxDate.add(Date.DAY, 1));
					base_form.findField('EvnStickWorkRelease_begDate').setMaxValue(null);
					base_form.findField('EvnStickWorkRelease_endDate').setMinValue(win.maxDate.add(Date.DAY, 1));
					base_form.findField('EvnStickWorkRelease_endDate').setMaxValue(null);
				}
				if ( win.disableBegDate ) {
					//~ base_form.findField('EvnStickWorkRelease_begDate').disable();//TAG:dis
				}
				
				var index;
				var lpu_section_id = arguments[0].formParams.LpuSection_id; //   base_form.findField('LpuSection_id').getValue();
				var med_personal_id = arguments[0].formParams.MedPersonal_id; // base_form.findField('MedPersonal_id').getValue();
				var med_personal2_id = arguments[0].formParams.MedPersonal2_id; // base_form.findField('MedPersonal2_id').getValue();
				var med_personal3_id = arguments[0].formParams.MedPersonal3_id; // base_form.findField('MedPersonal3_id').getValue();

				var
					MedStaffFact_id = arguments[0].formParams.MedStaffFact_id; //  base_form.findField('MedStaffFact_id').getValue(),
					MedStaffFact2_id = arguments[0].formParams.MedStaffFact2_id; //  base_form.findField('MedStaffFact2_id').getValue(),
					MedStaffFact3_id = arguments[0].formParams.MedStaffFact3_id; //  base_form.findField('MedStaffFact3_id').getValue();
				//	base_form.findField('MedStaffFact2_id').clearValue();
				//	base_form.findField('MedStaffFact3_id').clearValue();
				
				// Поля доступны в режиме редактирования если в освобождении указано рабочее место из МО пользователя #135678
				if(getRegionNick() != 'kz' && !(win.action == 'edit' && win.MedStaffFactInUserLpu)) { 
					base_form.findField('LpuSection_id').disable();
					base_form.findField('MedStaffFact_id').disable();
				}
				base_form.findField('EvnStickWorkRelease_endDate').fireEvent('blur', base_form.findField('EvnStickWorkRelease_endDate'), base_form.findField('EvnStickWorkRelease_endDate').getValue());

				if ( win.action == 'edit' ) {
				
                    setLpuSectionGlobalStoreFilter({id: lpu_section_id}, sw4.swLpuSectionGlobalStore);
					base_form.findField('LpuSection_id').getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));	
					
                    setMedStaffFactGlobalStoreFilter({id: MedStaffFact_id}, sw4.swMedStaffFactGlobalStore);
					base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
					
					var store_params = new Object();
                    if(win.CurLpuSection_id != 0)
                        store_params.LpuSection_id = win.CurLpuSection_id;
                    if(win.CurLpuUnit_id != 0)
                        store_params.LpuUnit_id = win.CurLpuUnit_id;
                    if(win.CurLpuBuilding_id != 0)
                        store_params.LpuBuilding_id = win.CurLpuBuilding_id;
                    store_params.dateFrom = Ext6.util.Format.date(base_form.findField('EvnStickWorkRelease_begDate').getValue(), 'd.m.Y');
                    store_params.dateTo = Ext6.util.Format.date(base_form.findField('EvnStickWorkRelease_endDate').getValue(), 'd.m.Y');
					if ( win.arrayLpuUnitType.length > 0 ) {
						store_params.arrayLpuUnitType = win.arrayLpuUnitType;
					}
                    setLpuSectionGlobalStoreFilter(store_params, sw4.swLpuSectionGlobalStore);
                    setMedStaffFactGlobalStoreFilter(store_params, sw4.swMedStaffFactGlobalStore);
					
					base_form.findField('LpuSection_id').getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore), true);
					
					base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore), true);
					base_form.findField('MedStaffFact2_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore), true);

					var msf3_params = {};
					msf3_params.dateFrom = Ext6.util.Format.date(base_form.findField('EvnStickWorkRelease_begDate').getValue(), 'd.m.Y');
					msf3_params.dateTo = Ext6.util.Format.date(base_form.findField('EvnStickWorkRelease_endDate').getValue(), 'd.m.Y');

					if (getRegionNick() == 'kareliya') {
						msf3_params.withoutLpuSection = true;
					} else {
						msf3_params.all = true;
					}

					setMedStaffFactGlobalStoreFilter(msf3_params, sw4.swMedStaffFactGlobalStore);
					base_form.findField('MedStaffFact3_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore), true);

					index = base_form.findField('LpuSection_id').getStore().findBy(function(rec, id) {
						return (rec.get('LpuSection_id') == lpu_section_id);
					}.createDelegate(this));
					var record = base_form.findField('LpuSection_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('LpuSection_id').setValue(lpu_section_id);
					}

					index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
						return (rec.get('MedStaffFact_id') == MedStaffFact_id);
					});
					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
							return (rec.get('LpuSection_id') == lpu_section_id && rec.get('MedPersonal_id') == med_personal_id);
						});
					}
					record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
						base_form.findField('MedStaffFact_id').fireEvent('blur', base_form.findField('MedStaffFact_id'), record.get('MedStaffFact_id'));
					}

					index = base_form.findField('MedStaffFact2_id').getStore().findBy(function(rec, id) {
						return (rec.get('MedStaffFact_id') == MedStaffFact2_id);
					});
					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact2_id').getStore().findBy(function(rec, id) {
							return (rec.get('MedPersonal_id') == med_personal2_id);
						});
					}
					record = base_form.findField('MedStaffFact2_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('MedStaffFact2_id').setValue(record.get('MedStaffFact_id'));
						base_form.findField('MedStaffFact2_id').fireEvent('blur', base_form.findField('MedStaffFact2_id'), record.get('MedStaffFact_id'));
					}

					index = base_form.findField('MedStaffFact3_id').getStore().findBy(function(rec, id) {
						return (rec.get('MedStaffFact_id') == MedStaffFact3_id);
					});
					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact3_id').getStore().findBy(function(rec, id) {
							return (rec.get('MedPersonal_id') == med_personal3_id);
						});
					}
					record = base_form.findField('MedStaffFact3_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('MedStaffFact3_id').setValue(record.get('MedStaffFact_id'));
						base_form.findField('MedStaffFact3_id').fireEvent('blur', base_form.findField('MedStaffFact3_id'), record.get('MedStaffFact_id'));
					}

					// если это черновик и за текущую МО, то делаем его не черновиком и дизаблим галочку
					if (base_form.findField('EvnStickWorkRelease_IsDraft').getValue() && base_form.findField('Org_id').getValue() == getGlobalOptions().org_id) {
						base_form.findField('EvnStickWorkRelease_IsDraft').setValue(false);
						base_form.findField('EvnStickWorkRelease_IsDraft').fireEvent('change', base_form.findField('EvnStickWorkRelease_IsDraft'), base_form.findField('EvnStickWorkRelease_IsDraft').getValue());
						base_form.findField('EvnStickWorkRelease_IsDraft').disable();
						base_form.findField('LpuSection_id').enable();
						base_form.findField('MedStaffFact_id').enable();
					}

					if (win.evnStickType == 2) {
						var form_fields = [
							'EvnStickWorkRelease_begDate',
							'EvnStickWorkRelease_endDate',
							'EvnStickWorkRelease_IsPredVK',
							'EvnVK_id',
							'MedStaffFact2_id',
							'MedStaffFact3_id'
							//'MedPersonal2_id',
							//'MedPersonal3_id'
						];
						if (!Ext6.isEmpty(getGlobalOptions().medpersonal_id) && getGlobalOptions().medpersonal_id.inlist([med_personal_id,med_personal2_id,med_personal3_id])) {
							for (i = 0; i < form_fields.length; i++ ) {
								base_form.findField(form_fields[i]).enable();
							}
						} else {
							for (i = 0; i < form_fields.length; i++ ) {
								base_form.findField(form_fields[i]).disable();
							}
						}
					}
					if (win.evnStickType == 3 || win.EvnStick_IsOriginal == 2) {
						base_form.findField('LpuSection_id').enable();
						base_form.findField('MedStaffFact_id').enable();
					}
				}
				else {
					base_form.findField('LpuSection_id').getStore().load({
						callback: function() {
							index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
								return (rec.get('LpuSection_id') == lpu_section_id);
							});

							if ( index >= 0 ) {
								base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
							}
						}.createDelegate(this),
						params: {
							LpuSection_id: lpu_section_id
						}
					});

					base_form.findField('MedStaffFact_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
								return (rec.get('MedStaffFact_id') == MedStaffFact_id);
							});
							if ( index == -1 ) {
								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
									return (rec.get('LpuSection_id') == lpu_section_id && rec.get('MedPersonal_id') == med_personal_id);
								});
							}

							if ( index >= 0 ) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
								base_form.findField('MedStaffFact_id').fireEvent('blur', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}.createDelegate(this),
						params: {
							LpuSection_id: lpu_section_id,
							MedPersonal_id: med_personal_id
						}
					});

					base_form.findField('MedStaffFact2_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact2_id').getStore().findBy(function(rec, id) {
								return (rec.get('MedStaffFact_id') == MedStaffFact2_id);
							});
							if ( index == -1 ) {
								index = base_form.findField('MedStaffFact2_id').getStore().findBy(function(rec, id) {
									return (rec.get('MedPersonal_id') == med_personal2_id);
								});
							}
							if ( index >= 0 ) {
								base_form.findField('MedStaffFact2_id').setValue(base_form.findField('MedStaffFact2_id').getStore().getAt(index).get('MedStaffFact_id'));
								base_form.findField('MedStaffFact2_id').fireEvent('blur', base_form.findField('MedStaffFact2_id'), base_form.findField('MedStaffFact2_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}.createDelegate(this),
						params: {
							MedPersonal_id: med_personal2_id
						}
					});

					base_form.findField('MedStaffFact3_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact3_id').getStore().findBy(function(rec, id) {
								return (rec.get('MedStaffFact_id') == MedStaffFact3_id);
							});
							if ( index == -1 ) {
								index = base_form.findField('MedStaffFact3_id').getStore().findBy(function(rec, id) {
									return (rec.get('MedPersonal_id') == med_personal3_id);
								});
							}

							if ( index >= 0 ) {
								base_form.findField('MedStaffFact3_id').setValue(base_form.findField('MedStaffFact3_id').getStore().getAt(index).get('MedStaffFact_id'));
								base_form.findField('MedStaffFact3_id').fireEvent('blur', base_form.findField('MedStaffFact3_id'), base_form.findField('MedStaffFact3_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}.createDelegate(this),
						params: {
							MedPersonal_id: med_personal3_id
						}
					});
				}

                base_form.findField('Post_id').setValue(win.Post_id);
				win.unmask();

				//base_form.clearInvalid();

				if ( !base_form.findField('EvnStickWorkRelease_begDate').disabled ) {
					base_form.findField('EvnStickWorkRelease_begDate').focus(true, 250);
				}
			break;

			default:
				win.unmask;
				win.hide();
			break;
		}
	},
	doSave: function() {
		var win = this,
			base_form = win.FormPanel.getForm();
		
		win.checkVK();
		
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}

		win.formStatus = 'save';

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
				},
				icon: Ext6.Msg.WARNING,
				msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
				title: 'Проверка данных формы'
			});
			return false;
		}
		
		win.mask(LOAD_WAIT_SAVE);

		var data = new Object();
		var record = base_form.findField('MedStaffFact_id').getStore().getById(base_form.findField('MedStaffFact_id').getValue());
		var record_2 = base_form.findField('MedStaffFact2_id').getStore().getById(base_form.findField('MedStaffFact2_id').getValue());
		var record_3 = base_form.findField('MedStaffFact3_id').getStore().getById(base_form.findField('MedStaffFact3_id').getValue());
		var med_personal_fio = '';
		var med_personal_id = 0;
		var med_personal2_id = 0;
		var med_personal3_id = 0;

		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}
		if( record_2 ) {
			med_personal2_id = record_2.get('MedPersonal_id');
		}

		if( record_3 ) {
			med_personal3_id = record_3.get('MedPersonal_id');
		}
		base_form.findField('MedPersonal_id').setValue(med_personal_id);
		base_form.findField('MedPersonal2_id').setValue(med_personal2_id);
		base_form.findField('MedPersonal3_id').setValue(med_personal3_id);
		//-----
		var newSumDate = win.sumDate;
		if ( !Ext6.isEmpty(base_form.findField('EvnStickWorkRelease_begDate').getValue()) && !Ext6.isEmpty(base_form.findField('EvnStickWorkRelease_endDate').getValue()) ) {
			var newSumDate = win.sumDate + Math.round((base_form.findField('EvnStickWorkRelease_endDate').getValue() - base_form.findField('EvnStickWorkRelease_begDate').getValue()) / 86400000)+1;
		}

		if( 
			base_form.findField('EvnStickWorkRelease_IsPredVK').getValue() == false
			&& base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code') 
			&& base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code').inlist([115,117]) // фельдшер, зубной врач
			&& newSumDate >= 11
			&& !(
				win.StickCause_SysNick == 'karantin' // Карантин
				|| win.StickCause_SysNick == 'pregn' // Отпуск по беременности и родам
				|| ( getRegionNick().inlist(['kz', 'ekb']) && win.StickCause_SysNick == 'soczab' ) //Соц. значимое заболевание
				|| ( getRegionNick() == 'ekb' && win.StickCause_SysNick == 'deseaseP1' )//Заболевание, указанное в пункте 1 Перечня социально значимых заболеваний
				|| win.StickCause_SysNick == 'postvaccinal' // Поствакцинальное осложнение или злокачественное новообразование у ребенка
				|| win.StickCause_SysNick == 'vich' // ВИЧ-инфицированный ребенок
			)
		) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Фельдшер или зубной врач выдает и продляет ЛВН на срок до 10 дней включительно. Для сохранения внесите информацию о председателе ВК'));
			win.unmask();
			win.formStatus = 'edit';
			return false;
		}//-----

		if ( ! base_form.findField('EvnStickWorkRelease_IsDraft').getValue()){


			// Правила заполнения врачей в периоде освобождения (переключатель в "Параметры системы" Options.php, блок evnstick)
			// 1 - "Разрешить выбирать в поле «Врач 1», «Врач 2», «Врач 3» одного сотрудника. Регион: Все, кроме Пермь, Хакасия. Установлено по умолчанию"
			// 2 - "Запретить выбирать в поле "Врач 3" (председатель ВК) сотрудника, указанного в поле "Врач 1" и/или "Врач 2".  Регион: Пермь, Хакасия"
			// 3 - "Запретить выбирать в поле «Врач 1», «Врач 2», «Врач 3» одного сотрудника."
			var rules_filling_doctors_workrelease = parseInt(getGlobalOptions().rules_filling_doctors_workrelease);

			if(rules_filling_doctors_workrelease == 1) {
				// Если на форме «Параметры системы» на уровне «ЛВН» установлено значение «Разрешить выбирать в поле «Врач 1»,
				// «Врач 2», «Врач 3» одного сотрудника», то значение полей «Врач 1», «Врач 2», «Врач 3» могут совпадать
				// (т.е. может быть указан один и тот же врач) или не совпадать, при этом форма сохраняется

			} else if(rules_filling_doctors_workrelease == 2){
				// Если на форме «Параметры системы» на уровне «ЛВН» установлено значение «Запретить выбирать в поле
				// "Врач 3" (председатель ВК) сотрудника, указанного в поле "Врач 1" и/или "Врач 2"» И  врач, установленный
				// в поле «Врач 3»  совпадает с врачом, установленным в поле «Врач 1» и/или «Врач 2», то отображается
				// сообщение об ошибке: «Сотрудник, указанный в поле "Врач 3", также указан в поле "Врач 1" и/или "Врач 2".
				// Выберите в поле "Врач 3" другого врача». При этом, врач выбранный в поле «Врач 1» и «Врач 2» может совпадать.
				if(med_personal3_id != 0){
					if(
						(med_personal_id != 0 && med_personal_id == med_personal3_id) ||
						(med_personal2_id != 0 && med_personal2_id == med_personal3_id)
					){
						Ext6.Msg.alert(langs('Ошибка'), langs('Сотрудник, указанный в поле "Врач 3", также указан в поле "Врач 1" и/или "Врач 2". Выберите в поле "Врач 3" другого врача'));
						win.formStatus = 'edit';
						win.unmask();
						return false;
					}
				}

			} else if(rules_filling_doctors_workrelease == 3){
				// Если на форме «Параметры системы» на уровне «ЛВН» установлено значение «Запретить выбирать в поле
				// «Врач 1», «Врач 2», «Врач 3» одного сотрудника» И в поле «Врач 1» и/или «Врач 2» и/или «Врач 3» указан
				// один и тот же врач,  то при сохранении отображается сообщение об ошибке: «В полях «Врач 1», «Врач 2»,
				// «Врач 3» должны быть указаны разные сотрудники. Выберите разных врачей». Иными словами,  должны быть
				// указаны разные врачи в полях «Врач 1», «Врач 2», «Врач 3».

				if(
					(
						med_personal_id == med_personal2_id ||
						(med_personal2_id == med_personal3_id && med_personal3_id != 0) ||
						(med_personal_id == med_personal3_id && med_personal3_id != 0)
					)
				){
					Ext6.Msg.alert(langs('Ошибка'), langs('В полях «Врач 1», «Врач 2», «Врач 3» должны быть указаны разные сотрудники. Выберите разных врачей'));
					win.formStatus = 'edit';
					win.unmask();
					return false;
				}

			} else {
				// На случай если еще не успели установить значение параметра "rules_filling_doctors_workrelease" (тогда он будет равен NaN), то мы оставляем старую проверку по умолчанию
				if(
					(
						med_personal_id == med_personal2_id ||
						(med_personal2_id == med_personal3_id && med_personal3_id != 0) ||
						(med_personal_id == med_personal3_id && med_personal3_id != 0)
					)
				){
					Ext6.Msg.alert(langs('Ошибка'), langs('В полях «Врач 1», «Врач 2», «Врач 3» должны быть указаны разные сотрудники. Выберите разных врачей'));
					win.formStatus = 'edit';
					win.unmask();
					return false;
				}
			}
		}

		var EvnStickWorkRelease_IsPredVK = 0;
		
		if (base_form.findField('EvnStickWorkRelease_IsPredVK').getValue()) {
			EvnStickWorkRelease_IsPredVK = 1;
		}

		var EvnStickWorkRelease_IsDraft = 0;

		if (base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
			EvnStickWorkRelease_IsDraft = 1;
		}

		var EvnStickWorkRelease_IsSpecLpu = 0;

		if (base_form.findField('EvnStickWorkRelease_IsSpecLpu').getValue()) {
			EvnStickWorkRelease_IsSpecLpu = 1;
		}

		var Org_id = getGlobalOptions().org_id;
		var Org_Nick = getGlobalOptions().org_nick;
		if (base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
			Org_id = base_form.findField('Org_id').getValue();
			Org_Nick = base_form.findField('Org_id').getFieldValue('Org_Nick');
		}

		data.evnStickWorkReleaseData = {
			'accessType': 'edit',
			'signAccess': 'edit',
			'EvnStickBase_id': base_form.findField('EvnStickBase_id').getValue(),
			'EvnStickWorkRelease_begDate': base_form.findField('EvnStickWorkRelease_begDate').getValue(),
			'EvnStickWorkRelease_endDate': base_form.findField('EvnStickWorkRelease_endDate').getValue(),
			'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
			'LpuUnitType_SysNick': base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
			'MedPersonal_Fio': med_personal_fio,
			'MedPersonal_id': base_form.findField('MedPersonal_id').getValue(),
			'MedPersonal2_id': base_form.findField('MedPersonal2_id').getValue(),
			'MedPersonal3_id': base_form.findField('MedPersonal3_id').getValue(),
			'MedStaffFact_id': base_form.findField('MedStaffFact_id').getValue(),
			'MedStaffFact2_id': base_form.findField('MedStaffFact2_id').getValue(),
			'MedStaffFact3_id': base_form.findField('MedStaffFact3_id').getValue(),
			'Lpu_id': getGlobalOptions().lpu_id,
			'EvnStickWorkRelease_IsPredVK': EvnStickWorkRelease_IsPredVK,
			'EvnStickWorkRelease_IsDraft': EvnStickWorkRelease_IsDraft,
			'EvnStickWorkRelease_IsSpecLpu': EvnStickWorkRelease_IsSpecLpu,
			'Org_id': Org_id,
			'Post_id' : base_form.findField('Post_id').getValue(),
			'EvnVK_id' : base_form.findField('EvnVK_id').getValue(),
			//~ 'EvnVK_descr' : base_form.findField('EvnVK_descr').getValue(),
			'Org_Name': Org_Nick
		};

		switch ( win.formMode ) {
			case 'local':
				win.formStatus = 'edit';
				win.unmask();

				data.evnStickWorkReleaseData.EvnStickWorkRelease_id = base_form.findField('EvnStickWorkRelease_id').getValue();

				win.callback(data);
				win.hide();
			break;

			case 'remote':
				base_form.submit({
                    params: {
                        'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
                        'MedPersonal_id': base_form.findField('MedPersonal_id').getValue(),
                        'Post_id': base_form.findField('Post_id').getValue()
                    },
					failure: function(result_form, action) {
						win.formStatus = 'edit';
						win.unmask();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
							}
							else {
								Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
							}
						}
					}.createDelegate(this),
					success: function(result_form, action) {
						win.formStatus = 'edit';
						win.unmask();

						if ( action.result && action.result.EvnStickWorkRelease_id > 0 ) {
							base_form.findField('EvnStickWorkRelease_id').setValue(action.result.EvnStickWorkRelease_id);

							data.evnStickWorkReleaseData.EvnStickWorkRelease_id = base_form.findField('EvnStickWorkRelease_id').getValue();

							win.callback(data);
							win.hide();
						}
						else {
							Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	initComponent: function() {
		var win = this;
		win.FormPanel = new Ext6.form.FormPanel({
			border: false,
			bodyPadding: '10 0 25 30',
			autoHeight: true,
			url: '/?c=Stick&m=saveEvnStickWorkRelease',
			timeout: 6000,
			defaults: {
				labelWidth: 140,
				width: 663
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{ name: 'accessType' },
						{ name: 'EvnStickBase_id' },
						{ name: 'EvnStickWorkRelease_begDate' },
						{ name: 'EvnStickWorkRelease_endDate' },
						{ name: 'EvnStickWorkRelease_id' },
						{ name: 'LpuSection_id' },
						{ name: 'MedStaffFact_id' },
						{ name: 'MedStaffFact2_id' },
						{ name: 'MedStaffFact3_id' },
						{ name: 'MedPersonal_id' },
						{ name: 'Post_id' },
						{ name: 'MedPersonal2_id' },
						{ name: 'MedPersonal3_id' },
						{ name: 'EvnStickWorkRelease_IsPredVK' },
						{ name: 'EvnVK_id' }
					]
				})
			}),
			items: [
				{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				},
				{
					name: 'EvnStickWorkRelease_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'EvnStickBase_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedPersonal_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedPersonal2_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedPersonal3_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'Override30Day',
					value: 'false',
					xtype: 'hidden'
				},
				{
					boxLabel: langs('Черновик за другую МО'),
					padding: '0 0 0 145',
					name: 'EvnStickWorkRelease_IsDraft',
					listeners: {
						'change': function(checkbox, checked) {
							var base_form = win.FormPanel.getForm();
							
							// действия над полями..
							if (checked) {
								// показываем поле МО, скрываем отделение и врачей
								base_form.findField('Org_id').show();
								base_form.findField('Org_id').setAllowBlank(false);
								base_form.findField('LpuSection_id').hideContainer();
								base_form.findField('LpuSection_id').clearValue();
								base_form.findField('LpuSection_id').setAllowBlank(true);
								base_form.findField('MedStaffFact_id').hideContainer();
								base_form.findField('MedStaffFact_id').clearValue();
								base_form.findField('MedStaffFact_id').setAllowBlank(true);
								base_form.findField('Post_id').hideContainer();
								base_form.findField('Post_id').hide();
								base_form.findField('Post_id').clearValue();

								base_form.findField('MedStaffFact2_id').hideContainer();
								base_form.findField('MedStaffFact2_id').clearValue();
								//~ base_form.findField('MedStaffFact2_id').disable();
								base_form.findField('MedStaffFact3_id').hideContainer();
								base_form.findField('MedStaffFact3_id').clearValue();
								//~ base_form.findField('MedStaffFact3_id').disable();
								base_form.findField('EvnStickWorkRelease_IsPredVK').hideContainer();
								base_form.findField('EvnStickWorkRelease_IsPredVK').setValue(false);
								base_form.findField('EvnStickWorkRelease_IsPredVK').fireEvent('change', base_form.findField('EvnStickWorkRelease_IsPredVK'), base_form.findField('EvnStickWorkRelease_IsPredVK').getValue());
							} else {
								base_form.findField('Org_id').hideContainer();
								base_form.findField('Org_id').setAllowBlank(true);
								base_form.findField('Org_id').clearValue();
								base_form.findField('LpuSection_id').show();
								base_form.findField('LpuSection_id').setAllowBlank(false);
								base_form.findField('MedStaffFact_id').show();
								base_form.findField('MedStaffFact_id').setAllowBlank(false);
								base_form.findField('Post_id').show();
								//base_form.findField('MedPersonal2_id').show();
								//base_form.findField('MedPersonal3_id').show();
								base_form.findField('MedStaffFact2_id').show();
								//~ base_form.findField('MedStaffFact2_id').enable();
								base_form.findField('MedStaffFact3_id').show();
								//~ base_form.findField('MedStaffFact3_id').enable();
								base_form.findField('EvnStickWorkRelease_IsPredVK').show();
							}

							win.checkVK();
						}
					},
					xtype: 'checkbox'
				},
				{
					boxLabel: 'Специализированное МО',
					padding: '0 0 0 145',
					name: 'EvnStickWorkRelease_IsSpecLpu',
					xtype: 'checkbox'
				},
				{
					fieldLabel: langs('МО'),
					valueField: 'Org_id',
					name: 'Org_id',
					allowBlank: false,
					xtype: 'swOrgCombo',
					triggers: {
						picker: {
							hidden: true
						},
						search :{
							handler: function() {
								var combo = this;
								if (combo.disabled) {
									return false;
								}

								var base_form = win.FormPanel.getForm();

								getWnd('swOrgSearchWindowExt6').show({
									enableOrgType: false,
									object: 'lpu',
									//onlyFromDictionary: true,
									onSelect: function(lpuData) {
										if ( lpuData.Org_id > 0 )
										{
											combo.getStore().load({
												params: {
													OrgType: 'lpu',
													Org_id: lpuData.Org_id
												},
												callback: function()
												{
													combo.setValue(lpuData.Org_id);
													combo.focus(true, 500);
												}
											});
										}
										getWnd('swOrgSearchWindowExt6').hide();
									},
									onClose: function() {combo.focus(true, 200)}
								});
							}
						},
						clear: {
							cls: 'sw-clear-trigger',
							extraCls: 'clear-icon-out', // search-icon-out
							hidden: true
						}
					}
				},
				{
					layout: 'column',
					border: false,
					padding: '0 0 5 0',
					items: [
						{
							allowBlank: false,
							fieldLabel: langs('С какого числа'),
							labelWidth: 140,
							width: 257+10,
							xtype: 'swDateField',
							startDay: 1,
							format: 'd.m.Y',
							listeners: {
								'blur': function(field, newValue, oldValue) {
									//var base_form = win.FormPanel.getForm();
									win.refreshEndDateLimit();
									//base_form.findField('EvnStickWorkRelease_endDate').setMinValue(newValue);
									
									//win.getViewModel().set('EvnStickWorkRelease_begDate', newValue);

									win.checkVK();
									win.refreshGlobalCombos();
								}.createDelegate(this)
							},
							name: 'EvnStickWorkRelease_begDate',
							selectOnFocus: true,
							tabIndex: TABINDEX_ESTWREF + 1
						},
						{
							allowBlank: false,
							fieldLabel: langs('По какое число'),
							xtype: 'swDateField',
							startDay: 1,
							labelAlign: 'right',
							labelWidth: 130,
							width: 242+15,
							format: 'd.m.Y',
							listeners: {
								'blur': function(field, newValue, oldValue) {
									//win.getViewModel().set('EvnStickWorkRelease_endDate', newValue);
									win.checkVK();
									win.refreshGlobalCombos();
								}.createDelegate(this)
							},
							name: 'EvnStickWorkRelease_endDate',
							selectOnFocus: true,
							tabIndex: TABINDEX_ESTWREF + 2
						},
					]
				},
				{
					allowBlank: false,
					name: 'LpuSection_id',
					//~ id: 'EStWREF_LpuSectionCombo',
					fieldLabel: langs('Отделение'),
					lastQuery: '',
					listWidth: 500,
					xtype: 'SwLpuSectionGlobalCombo',
					queryMode: 'local',
					minChars: 2,
					listeners: {
						'blur': function(combo, newVal, oldVal) {
							var base_form = win.FormPanel.getForm();
							
							win.getController().filterMedStaffFact({
								MedStaffFact_id: '',
								LpuSection_id: base_form.findField('LpuSection_id').getValue()
							});
						}
					}
					//~ autoFilter: true,
					//~ forceSelection: true,
				},
				{
					allowBlank: false,
					fieldLabel: langs('Врач 1'),
					name: 'MedStaffFact_id',
					lastQuery: '',
					listeners: {
						'blur': function(field, newValue, oldValue) {
							win.checkVK();

							var base_form = win.FormPanel.getForm();

							// проставляем поле должность
							var PostMed_Code = base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code');

							if ( ! Ext6.isEmpty(PostMed_Code) && PostMed_Code.inlist(['59','52','53','54','55','56','57','58','70','20','60','88','89'])){

								// Поле выбора «Должность» включает также дополнительные должности: Терапевт Зубной врач Фельдшер
								base_form.findField('Post_id').getStore().filterBy(function(rec) {
									return ( !Ext6.isEmpty(rec.get('PostMed_Code')) && rec.get('PostMed_Code').inlist(['73','115','117', PostMed_Code]));
								});

								base_form.findField('Post_id').setDisabled(false);

								if(!base_form.findField('Post_id').getValue()) {
									win.setPostValue();
								}

							} else {
								base_form.findField('Post_id').setDisabled(true);
								base_form.findField('Post_id').getStore().reload({callback: function() {
									win.setPostValue();
								}});
							}
							
							var LpuSection_id = base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id');
							if(LpuSection_id) base_form.findField('LpuSection_id').setValue(LpuSection_id);
						}
					},
					listWidth: 600,
					//~ parentElementId: 'EStWREF_LpuSectionCombo',
					tabIndex: TABINDEX_ESTWREF + 4,
					xtype: 'SwMedStaffFactGlobalCombo',
					queryMode: 'local',
					minChars: 2,
					//~ ignoreDisableInDoc: true //?
					//~ autoFilter: true,
					//~ forceSelection: true,
				},
				{
					xtype: 'commonSprCombo',
					comboSubject: 'PostMed',
					tabIndex: TABINDEX_ESTWREF + 4,
					name: 'Post_id',
					lastQuery: '',
					disabled: true,
					fieldLabel: langs('Должность'),
					queryMode: 'local',
					minChars: 2,
					//~ autoFilter: true,
					//~ forceSelection: true,
				},
				{
					allowBlank: false,
					fieldLabel: langs('Врач 2'),
					name: 'MedStaffFact2_id',
					lastQuery: '',
					listWidth: 600,
					tabIndex: TABINDEX_ESTWREF + 4,
					xtype: 'SwMedStaffFactGlobalCombo',
					queryMode: 'local',
					minChars: 2,
					//~ ignoreDisableInDoc: true //?
					//~ autoFilter: true,
					//~ forceSelection: true,
				},
				{
					xtype: 'checkbox',
					boxLabel: langs('Председатель ВК'),
					padding: '0 0 0 145',
					height:24,
					hideLabel: false,
					tabIndex: TABINDEX_ESTWREF + 7,
					name: 'EvnStickWorkRelease_IsPredVK',
					listeners: {
						'change': function(checkbox, value) {
							var base_form = win.FormPanel.getForm();
							if (base_form.findField('EvnStickWorkRelease_IsPredVK').checked) {
								base_form.findField('MedStaffFact3_id').setAllowBlank(false);
							} else {
								base_form.findField('MedStaffFact3_id').setAllowBlank(true);
							}
						}
					}
				},
				{
					allowBlank: false,
					fieldLabel: langs('Врач 3'),
					name: 'MedStaffFact3_id',
					//~ id: 'EStWREF_MedPersonal3Combo',
					lastQuery: '',
					listWidth: 600,
					tabIndex: TABINDEX_ESTWREF + 4,
					xtype: 'SwMedStaffFactGlobalCombo',
					queryMode: 'local',
					minChars: 2,
					//~ ignoreDisableInDoc: true, //?
					//~ autoFilter: true,
					//~ forceSelection: true,
				},
				{
					name: 'EvnVK_id',
					value: '',
					xtype: 'hidden'
				}
			]
		});
			
		Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			border: false,
			buttons:
			[ '->'
			, {
				text: langs('Отмена'),
				itemId: 'button_cancel',
				userCls:'buttonPoupup buttonCancel',
				handler: function() {
					win.hide();
				}
			}, {
				text: langs('Сохранить'),
				itemId: 'button_save',
				userCls:'buttonPoupup buttonAccept',
				handler: function() {
					win.doSave();
				}
			}]
		});

		this.callParent(arguments);
	}
});