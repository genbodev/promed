/**
* swLeavePriemEditWindow - окно редактирования исхода пребывания в приемном отделении
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @comment      tabIndex: TABINDEX_MS + (80-99)
*/

/*NO PARSE JSON*/
sw.Promed.swLeavePriemEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLeavePriemEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swLeavePriemEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swLeavePriemEditWindow',
	width: 600,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	submit: function() {
		var win = this,
			form = this.formPanel.getForm();

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}

		// https://redmine.swan.perm.ru/issues/76559 - тут сделано
		// https://redmine.swan.perm.ru/issues/78033 - тут закомментировано
		/*if ( getRegionNick() == 'perm' && win.PayType_SysNick == 'oms'
			&& form.findField('LeaveType_fedid').getFieldValue('LeaveType_Code') == '313'
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.findField('PrehospWaifRefuseCause_id').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['sluchai_s_rezultatom_313_konstatatsiya_fakta_smerti_ne_podlejat_oplate_po_oms'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}*/

		this.controlSavingForm_DepartmentSelectionLPU(function(res){
			var EvnPS_id = form.findField('EvnPS_id').getValue();
			var wnd = 'swEvnPSPriemEditWindow';
			var params = {
				action : 'edit',
				title : langs('Поступление пациента в приемное отделение'),
				EvnPS_id : EvnPS_id,
				Person_id : form.findField('Person_id').getValue(),
				Server_id : form.findField('Server_id').getValue(),
				UserLpuSection_id: this.UserLpuSection_id,
				UserMedStaffFact_id: this.UserMedStaffFact_id,
				activatePanel: 'EPSPEF_HospitalisationPanel'
			};

			if ( form.findField('LeaveType_fedid').disabled ) {
				params.LeaveType_fedid = form.findField('LeaveType_fedid').getValue();
			}

			if ( form.findField('ResultDeseaseType_fedid').disabled ) {
				params.ResultDeseaseType_fedid = form.findField('ResultDeseaseType_fedid').getValue();
			}

			if(res){
				sw.swMsg.show({
					title: 'Внимание!',
					msg: 'Не указаны сведения о направлении. При оказании неотложной помощи обязательно должны быть заполнены поля «№ направления» и «Дата направления», или выбрано электронное направление. Заполните раздел «Кем направлен»',
					buttons: {yes: 'Редактировать КВС', no: 'Отмена'},
					fn: function(butn){
						if (butn == 'no'){
							return false;
						}else{
							win.hide();
							getWnd(wnd).show(params);
						}
					}
				});
			}else{
				win.getLoadMask(langs('Подождите, сохраняется запись...')).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				var data = {};
				win.callback(data);
			}
		});
			}
		}.createDelegate(this));

		/*win.getLoadMask(langs('Подождите, сохраняется запись...')).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
	},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				var data = {};
				win.callback(data);
			}
		});*/
	},
	controlSavingForm_DepartmentSelectionLPU: function(callback){
		var cb = callback;
		var form = this.formPanel.getForm();
		var EvnPS_id = form.findField('EvnPS_id').getValue();

		if(!getRegionNick().inlist(['penza']) || !EvnPS_id) {
			if(typeof cb == "function") cb(false);
			return false;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				var cb = this;
				var flag = false;
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var r = response_obj[0];
					if(
						r.MedicalCareFormType_id == 2
						&& r.LpuSection_eid
					){
						var directionNumDate = (!r.EvnDirection_Num && !r.EvnDirection_setDate) ? true : false;
						var extraEvnPS = (r.EvnPS_IsWithoutDirection != 2) ? true : false;
						if(directionNumDate && extraEvnPS){
							flag = true;
						}else{
							flag = false;
						}
					}
				}
				if(typeof cb == "function") cb(flag);
			}.bind(cb),
			params: {
				EvnPS_id: EvnPS_id
			},
			url: '/?c=EvnPS&m=controlSavingForm_DepartmentSelectionLPU'
		});
	},
	allowEdit: function(is_allow) {
		var form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			fields = [
				'EvnPS_IsTransfCall'
				,'PrehospWaifRefuseCause_id'
				,'ResultClass_id'
				,'ResultDeseaseType_id'
				,'LpuSectionProfile_id'
				,'UslugaComplex_id'
				,'LpuSection_id'
			];

		if ( getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
			fields.push('LeaveType_prmid');
		}

		for(var i=0;fields.length>i;i++) {
			form.findField(fields[i]).setDisabled(!is_allow);
		}

		if (is_allow)
		{
			if ( this.IsRefuse == true ) {
				form.findField('PrehospWaifRefuseCause_id').focus(true, 250);
			}
			else {
				form.findField('LpuSection_id').focus(true, 250);
			}

			save_btn.show();
		}
		else
		{
			save_btn.hide();
		}
	},

	initComponent: function() {
		var win = this;
		this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'LeavePriemEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			region: 'center',
			items: [{
				autoLoad: false,
				fieldLabel: lang['ishod_prebyivaniya'],
				hiddenName: 'LeaveType_prmid',
				lastQuery: '',
				listeners: {
					'change':function (combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function (rec) {
							return (rec.get(combo.valueField) == newValue);
						});

						combo.fireEvent('beforeselect', combo, combo.getStore().getAt(index), index);
					}.createDelegate(this),
					'beforeselect':function (combo, record, idx) {
						var base_form = this.formPanel.getForm();

						// 1. Чистим и скрываем все поля
						// 2. В зависимости от выбранного значения, открываем поля

						var
							LpuSection_id = base_form.findField('LpuSection_id').getValue(),
							PrehospWaifRefuseCause_id = base_form.findField('PrehospWaifRefuseCause_id').getValue(),
							UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue(),
							ResultClass_id = base_form.findField('ResultClass_id').getValue(),
							ResultDeseaseType_id = base_form.findField('ResultDeseaseType_id').getValue(),
							EvnPS_IsTransfCall = base_form.findField('EvnPS_IsTransfCall').getValue();

						base_form.findField('LpuSection_id').clearValue();
						base_form.findField('LpuSection_id').setAllowBlank(true);
						base_form.findField('LpuSection_id').setContainerVisible(false);
						base_form.findField('PrehospWaifRefuseCause_id').clearValue();
						base_form.findField('PrehospWaifRefuseCause_id').setAllowBlank(true);
						base_form.findField('PrehospWaifRefuseCause_id').setContainerVisible(false);
						base_form.findField('UslugaComplex_id').clearValue();
						base_form.findField('UslugaComplex_id').setAllowBlank(true);
						base_form.findField('UslugaComplex_id').setContainerVisible(false);
						base_form.findField('ResultClass_id').clearValue();
						base_form.findField('ResultClass_id').setAllowBlank(true);
						base_form.findField('ResultClass_id').setContainerVisible(false);
						base_form.findField('ResultDeseaseType_id').clearValue();
						base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
						base_form.findField('ResultDeseaseType_id').setContainerVisible(false);
						base_form.findField('EvnPS_IsTransfCall').clearValue();
						base_form.findField('EvnPS_IsTransfCall').setAllowBlank(true);
						base_form.findField('EvnPS_IsTransfCall').setContainerVisible(false);
						if (getRegionNick() != 'penza') {
							base_form.findField('MedicalCareFormType_id').setAllowBlank(true);
						} else {
							base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
						}
						base_form.findField('MedicalCareFormType_id').setContainerVisible(false);
						this.findById('LPEF_PrehospWaifRefuseCauseButton').hide();

						if ( typeof record == 'object' && !Ext.isEmpty(record.get('LeaveType_id')) ) {
							switch ( record.get('LeaveType_SysNick') ) {
								case 'gosp': // Госпитализация
									base_form.findField('LpuSection_id').setAllowBlank(false);
									base_form.findField('LpuSection_id').setContainerVisible(true);

									if ( !Ext.isEmpty(LpuSection_id) ) {
										base_form.findField('LpuSection_id').setValue(LpuSection_id);
									}

									if ( getRegionNick() == 'buryatiya' ) {
										base_form.findField('LpuSectionProfile_id').hideContainer();
										base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
									}
								break;

								case 'otk': // Отказ
									base_form.findField('PrehospWaifRefuseCause_id').setAllowBlank(false);
									base_form.findField('PrehospWaifRefuseCause_id').setContainerVisible(true);
									base_form.findField('EvnPS_IsTransfCall').setContainerVisible(true);
									this.findById('LPEF_PrehospWaifRefuseCauseButton').show();

									if ( !Ext.isEmpty(PrehospWaifRefuseCause_id) ) {
										base_form.findField('PrehospWaifRefuseCause_id').setValue(PrehospWaifRefuseCause_id);
									}

									if ( !Ext.isEmpty(EvnPS_IsTransfCall) ) {
										base_form.findField('EvnPS_IsTransfCall').setValue(EvnPS_IsTransfCall);
									}

									if ( getRegionNick() == 'buryatiya' ) {
										base_form.findField('MedicalCareFormType_id').setContainerVisible(true);
										base_form.findField('LpuSectionProfile_id').showContainer();
										if( new Date >= new Date(2019, 6, 1)){
											base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
										}
									}
								break;

								case 'osmpp': // Осмотрен в приемном отделении
									base_form.findField('PrehospWaifRefuseCause_id').setContainerVisible(true);
									base_form.findField('ResultClass_id').setAllowBlank(false);
									base_form.findField('ResultClass_id').setContainerVisible(true);
									base_form.findField('ResultDeseaseType_id').setAllowBlank(false);
									base_form.findField('ResultDeseaseType_id').setContainerVisible(true);

									if ( getRegionNick().inlist([ 'buryatiya' ]) ) {
										base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
										base_form.findField('MedicalCareFormType_id').setContainerVisible(true);
										base_form.findField('UslugaComplex_id').setAllowBlank(false);
										base_form.findField('UslugaComplex_id').setContainerVisible(true);
										base_form.findField('LpuSectionProfile_id').showContainer();
										if( new Date >= new Date(2019, 6, 1)){
											base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
										}

									}

									if ( !Ext.isEmpty(PrehospWaifRefuseCause_id) ) {
										base_form.findField('PrehospWaifRefuseCause_id').setValue(PrehospWaifRefuseCause_id);
									}

									if ( !Ext.isEmpty(UslugaComplex_id) ) {
										base_form.findField('UslugaComplex_id').getStore().load({
											params: {UslugaComplex_id: UslugaComplex_id},
											callback: function() {
												var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
													return (rec.get('UslugaComplex_id') == UslugaComplex_id);
												});
												if ( index >= 0 ) {
													base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
												}
												base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
											}
										});
									}

									if ( !Ext.isEmpty(ResultClass_id) ) {
										base_form.findField('ResultClass_id').setValue(ResultClass_id);
									}

									if ( !Ext.isEmpty(ResultDeseaseType_id) ) {
										base_form.findField('ResultDeseaseType_id').setValue(ResultDeseaseType_id);
									}
								break;
							}
						}
						else{
							if ( getRegionNick() == 'buryatiya' ) {
								base_form.findField('LpuSectionProfile_id').hideContainer();
								base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
							}
						}

						this.syncSize();
						this.syncShadow();
					}.createDelegate(this)
				},
				tabIndex:TABINDEX_EPSPEF + 45,
				width:300,
				xtype:'swleavetypecombo'
			}, {
				hiddenName: 'LpuSection_id',
				fieldLabel: lang['gospitalizirovan_v'],
				id: 'LPEF_LpuSectionCombo',
				tabIndex: TABINDEX_MS+80,
				anchor: '100%',
				xtype: 'swlpusectionglobalcombo', 
				listeners: 
				{
					'select': function (combo,record,index) 
					{
						if( !Ext.isEmpty(record.get('LpuSection_id')) )
						{
							var rc_combo = this.formPanel.getForm().findField('PrehospWaifRefuseCause_id');
							var oldValue = rc_combo.getValue();
							rc_combo.clearValue();
							rc_combo.fireEvent('change',rc_combo,'',oldValue);
						}
					}.createDelegate(this)
				}
			},{
				hiddenName: 'PrehospWaifRefuseCause_id',
				fieldLabel: lang['otkaz'],
				tabIndex: TABINDEX_MS + 81,
				anchor: '100%',
				comboSubject: 'PrehospWaifRefuseCause',
				autoLoad: true,
				typeCode: 'int',
				xtype: 'swcommonsprcombo', 
				listeners: 
				{
					'change': function (combo,newValue,oldValue) {
						win.setMedicalCareFormType();
						win.setMedicalCareFormTypeAllowBlank();

						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, idx) {
						var base_form = this.formPanel.getForm();
						var is_transf_call_combo = base_form.findField('EvnPS_IsTransfCall');

						if( typeof record != 'object' || Ext.isEmpty(record.get(combo.valueField)) ) {
							is_transf_call_combo.disable();
							this.findById('LPEF_PrehospWaifRefuseCauseButton').disable();

							if (getRegionNick().inlist([ 'ekb' ])) {
								base_form.findField('UslugaComplex_id').hideContainer();
								base_form.findField('LpuSectionProfile_id').hideContainer();
								base_form.findField('UslugaComplex_id').setAllowBlank(true);
								base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
							}
							if (getRegionNick().inlist([ 'krym' ])) {
								base_form.findField('LpuSectionProfile_id').clearValue();
								base_form.findField('LpuSectionProfile_id').hideContainer();
								base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
							}
							if (getRegionNick().inlist([ 'penza' ])) {
								base_form.findField('LpuSectionProfile_id').hideContainer();
								base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
							}
							if (getRegionNick().inlist([ 'perm' ])) {
								base_form.findField('LpuSectionProfile_id').clearValue();
								base_form.findField('LpuSectionProfile_id').hideContainer();
								base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').hideContainer();
								base_form.findField('UslugaComplex_id').setAllowBlank(true);
								base_form.findField('LeaveType_fedid').clearValue();
								base_form.findField('LeaveType_fedid').hideContainer();
								base_form.findField('LeaveType_fedid').setAllowBlank(true);
								base_form.findField('ResultDeseaseType_fedid').clearValue();
								base_form.findField('ResultDeseaseType_fedid').hideContainer();
								base_form.findField('ResultDeseaseType_fedid').setAllowBlank(true);
							}
							if (getRegionNick().inlist([ 'kareliya', 'krym' ])) {
								base_form.findField('ResultClass_id').clearValue();
								base_form.findField('ResultClass_id').hideContainer();
								base_form.findField('ResultClass_id').setAllowBlank(true);
								base_form.findField('ResultDeseaseType_id').clearValue();
								base_form.findField('ResultDeseaseType_id').hideContainer();
								base_form.findField('ResultDeseaseType_id').setAllowBlank(true);
							}
						}
						else {
							is_transf_call_combo.enable();
							this.findById('LPEF_PrehospWaifRefuseCauseButton').enable();
							this.findById('LPEF_LpuSectionCombo').clearValue();

							if (getRegionNick().inlist([ 'ekb' ])) {
								base_form.findField('UslugaComplex_id').showContainer();
								base_form.findField('LpuSectionProfile_id').showContainer();
								base_form.findField('UslugaComplex_id').setAllowBlank(false);
								base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
							}
							if (getRegionNick().inlist([ 'krym' ])) {
								base_form.findField('LpuSectionProfile_id').showContainer();
								base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
							}
							if (getRegionNick().inlist([ 'penza' ])) {
								base_form.findField('LpuSectionProfile_id').showContainer();
								base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
							}
							if (getRegionNick().inlist([ 'perm' ])) {
								base_form.findField('LeaveType_fedid').showContainer();
								base_form.findField('LeaveType_fedid').setAllowBlank(false);
								base_form.findField('ResultDeseaseType_fedid').showContainer();
								base_form.findField('ResultDeseaseType_fedid').setAllowBlank(false);

								// Поля "Профиль" и "Код посещения"
								switch ( record.get('PrehospWaifRefuseCause_Code') ) {
									case 1:
									case 9:
										base_form.findField('LpuSectionProfile_id').clearValue();
										base_form.findField('LpuSectionProfile_id').hideContainer();
										base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
										base_form.findField('UslugaComplex_id').clearValue();
										base_form.findField('UslugaComplex_id').hideContainer();
										base_form.findField('UslugaComplex_id').setAllowBlank(true);
									break;

									default:
										base_form.findField('LpuSectionProfile_id').showContainer();
										base_form.findField('UslugaComplex_id').showContainer();
									break;
								}

								// Устанавливаем фед. результат
								switch ( record.get('PrehospWaifRefuseCause_Code') ) {
									case 1:
									case 3:
									case 4:
									case 5:
										base_form.findField('LeaveType_fedid').setFieldValue('LeaveType_Code', '303');
									break;

									case 2:
									case 8:
										base_form.findField('LeaveType_fedid').setFieldValue('LeaveType_Code', '302');
									break;

									case 10:
										base_form.findField('LeaveType_fedid').setFieldValue('LeaveType_Code', '313');
									break;

									default:
										base_form.findField('LeaveType_fedid').setFieldValue('LeaveType_Code', '301');
									break;
								}

								// Устанавливаем фед. исход
								switch ( record.get('PrehospWaifRefuseCause_Code') ) {
									case 10:
										base_form.findField('ResultDeseaseType_fedid').setFieldValue('ResultDeseaseType_Code', '305');
									break;

									default:
										base_form.findField('ResultDeseaseType_fedid').setFieldValue('ResultDeseaseType_Code', '304');
									break;
								}
							}
							if (getRegionNick().inlist([ 'kareliya', 'krym', 'penza' ])) {
								base_form.findField('ResultClass_id').showContainer();
								base_form.findField('ResultClass_id').setAllowBlank(false);
								base_form.findField('ResultDeseaseType_id').showContainer();
								base_form.findField('ResultDeseaseType_id').setAllowBlank(false);

								if ( record.get('PrehospWaifRefuseCause_Code') == 10 ) {
									base_form.findField('ResultClass_id').setFieldValue('ResultClass_Code', '313');
									base_form.findField('ResultDeseaseType_id').setFieldValue('ResultDeseaseType_Code', '305');
								}
							}
						}

						this.syncSize();
						this.syncShadow();
					}.createDelegate(this)
				}
			}, {
				border: false,
				hidden: !(getRegionNick().inlist([ 'buryatiya', 'ekb', 'perm', 'kareliya', 'krym', 'ufa', 'penza' ])),
				layout: 'form',
				items: [{
					comboSubject: 'MedicalCareFormType',
					hiddenName: 'MedicalCareFormType_id',
					fieldLabel: 'Форма помощи',
					lastQuery: '',
					prefix: 'nsi_',
					xtype: 'swcommonsprcombo'
				}]
			}, {
				border: false,
				hidden: !(getRegionNick().inlist([ 'ekb', 'krym', 'perm', 'penza','buryatiya' ])),
				layout: 'form',
				items: [{
					fieldLabel: lang['profil'],
					hiddenName: 'LpuSectionProfile_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if ( !getRegionNick().inlist([ 'perm' ]) ) {
								return false;
							}

							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
						},
						'select': function(combo, record, idx) {
							if ( !getRegionNick().inlist([ 'perm' ]) ) {
								return false;
							}

							var base_form = this.formPanel.getForm();
							var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();

							if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
								var load = (base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSectionProfile_id != record.get(combo.valueField));

								base_form.findField('UslugaComplex_id').setLpuSectionProfile_id(record.get(combo.valueField));

								if ( load == true ) {
									base_form.findField('UslugaComplex_id').clearValue();
									base_form.findField('UslugaComplex_id').getStore().load({
										callback: function() {
											if ( !Ext.isEmpty(UslugaComplex_id) ) {
												var index = base_form.findField('UslugaComplex_id').getStore().findBy(function(rec) {
													return (rec.get('UslugaComplex_id') == UslugaComplex_id);
												});

												if ( index >= 0 ) {
													base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
												}

												base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
											}
										}
									});
								}
							}
							else {
								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').getStore().removeAll();
								base_form.findField('UslugaComplex_id').getStore().baseParams.query = '';

								base_form.findField('UslugaComplex_id').setLpuSectionProfile_id(null);
							}
						}.createDelegate(this)
					},
					listWidth: 600,
					tabIndex: TABINDEX_MS + 82,
					anchor: '100%',
					xtype: 'swlpusectionprofileekbremotecombo'
				}]
			},{
				border: false,
				hidden: !(getRegionNick().inlist([ 'buryatiya', 'ekb', 'perm' ])), // Открыто для Бурятии, Екатеринбурга и Перми
				layout: 'form',
				items: [{
					fieldLabel: lang['kod_posescheniya'],
					hiddenName: 'UslugaComplex_id',
					to: 'EvnSection',
					listWidth: 600,
					tabIndex: TABINDEX_MS + 83,
					anchor: '100%',
					xtype: 'swuslugacomplexnewcombo'
				}]
			}, {
				border: false,
				hidden: getRegionNick()=='kz',
				layout: 'form',
				items: [{
					fieldLabel: langs('Диагноз'),
					name: 'Diag_id',
					xtype: 'swdiagcombo',
					disabled: true,
					anchor: '100%'
				}]
			} ,{
				border: false,
				hidden: getRegionNick()=='kz',
				layout: 'form',
				items: [{
					fieldLabel: langs('Характер'),
					hiddenName: 'DeseaseType_id',
					xtype: 'swdeseasetypecombo',
					comboSubject: 'DeseaseType',
					allowBlank: false,
					disabled: getRegionNick()=='kz',
					anchor: '100%'
				}]
			}, {
				border: false,
				hidden: !(getRegionNick().inlist([ 'buryatiya', 'kareliya', 'krym', 'pskov', 'penza' ])),
				layout: 'form',
				items: [{
					anchor: '100%',
					fieldLabel: lang['rezultat_obrascheniya'],
					hiddenName: 'ResultClass_id',
					tabIndex:TABINDEX_MS + 84,
					xtype: 'swresultclasscombo'
				}, {
					anchor: '100%',
					comboSubject: 'ResultDeseaseType',
					fieldLabel: lang['ishod'],
					hiddenName: 'ResultDeseaseType_id',
					lastQuery: '',
					tabIndex:TABINDEX_MS + 85,
					xtype: 'swcommonsprcombo'
				}]
			}, {
				border: false,
				hidden: !(getRegionNick().inlist([ 'perm' ])), // Открыто для Перми
				layout: 'form',
				items: [{
					disabled: true,
					fieldLabel: lang['fed_rezultat'],
					hiddenName: 'LeaveType_fedid',
					listWidth: 600,
					tabIndex:TABINDEX_MS + 86,
					anchor: '100%',
					xtype: 'swleavetypefedcombo'
				}, {
					fieldLabel: lang['fed_ishod'],
					hiddenName: 'ResultDeseaseType_fedid',
					listWidth: 600,
					tabIndex:TABINDEX_MS + 87,
					anchor: '100%',
					xtype: 'swresultdeseasetypefedcombo'
				}]
			},{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					width: 250,
					items: [{
						allowBlank: false,
						tabIndex: TABINDEX_MS + 88,
						comboSubject: 'YesNo',
						fieldLabel: lang['peredan_aktivnyiy_vyizov'],
						hiddenName: 'EvnPS_IsTransfCall',
						width: 70,
						value: 1,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					border: false,
					layout: 'form',
					width: 300,
					items: [{
						handler: function() {
							printBirt({
								'Report_FileName': 'printEvnPSPrehospWaifRefuseCause.rptdesign',
								'Report_Params': '&paramEvnPsID=' + this.formPanel.getForm().findField('EvnPS_id').getValue(),
								'Report_Format': 'pdf'
							});
						}.createDelegate(this),
						iconCls: 'print16',
						id: 'LPEF_PrehospWaifRefuseCauseButton',
						tabIndex: TABINDEX_MS + 89,
						text: lang['spravka_ob_otkaze_v_gospitalizatsii'],
						tooltip: lang['spravka_ob_otkaze_v_gospitalizatsii'],
						xtype: 'button'
					}]
				}]   
			}, {
				name: 'EvnPS_id',
				xtype: 'hidden'
			}, {
				name: 'EvnPS_OutcomeDate',
				xtype: 'hidden'
			}, {
				name: 'Diag_id',
				xtype: 'hidden'
			}, {
				name: 'DeseaseType_Name',
				xtype: 'hidden'
			},{ 
				name: 'LpuSection_pid',
				xtype: 'hidden'
			}, {
				name: 'MedStaffFact_pid',
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_pid',
				xtype: 'hidden'
			}, {
				name: 'FedMedSpec_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'PrehospArrive_SysNick',
				xtype: 'hidden'
			}, {
				name: 'PrehospType_SysNick',
				xtype: 'hidden'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.submit();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			timeout: 600,
			url: '/?c=EvnPS&m=saveEvnPSWithLeavePriem'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_MS + 90,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'LPEF_LpuSectionCombo',
				tabIndex: TABINDEX_MS + 91,
				text: BTN_FRMCANCEL
			}],
			items: [ 
				this.formPanel
			]
		});
		sw.Promed.swLeavePriemEditWindow.superclass.initComponent.apply(this, arguments);
		
	},
	setMedicalCareFormTypeAllowBlank: function() {
		var
			base_form = this.formPanel.getForm(),
			date = new Date(),
			xdate;

		switch ( getRegionNick() ) {
			case 'perm':
				// Поле обязательно, при отказах с приемном отделении с 01-05-2016, в остальных случаях поле видимо, доступно, но необязательно.
				xdate = new Date(2016, 4, 1);

				base_form.findField('MedicalCareFormType_id').setAllowBlank(true);

				if ( !Ext.isEmpty(date) && date >= xdate && !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
					base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
				}
			break;

			case 'krym':
				// Поле обязательно при отказах с приемном отделении с 01-05-2017, в остальных случаях поле видимо, доступно, но необязательно.
				xdate = new Date(2017, 4, 1);

				base_form.findField('MedicalCareFormType_id').setAllowBlank(true);
				base_form.findField('MedicalCareFormType_id').disable();

				if ( !Ext.isEmpty(date) && date >= xdate && !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
					base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
					base_form.findField('MedicalCareFormType_id').enable();
				}
				else if ( Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
					base_form.findField('MedicalCareFormType_id').clearValue();
				}
			break;

			case 'buryatiya':
				base_form.findField('MedicalCareFormType_id').setAllowBlank(true);

				if ( base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_SysNick') == 'osmpp' ) {
					base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
				}
			break;

			case 'ekb':
			case 'kareliya':
				base_form.findField('MedicalCareFormType_id').setAllowBlank(true);

				if ( !Ext.isEmpty(date) ) {
					base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
				}
			break;
			case 'penza':
				base_form.findField('MedicalCareFormType_id').setAllowBlank(false);
			break;
		}
	},
	filterMedicalCareFormType: function() {
		var base_form = this.formPanel.getForm();

		base_form.findField('MedicalCareFormType_id').getStore().clearFilter();

		switch ( getRegionNick() ) {
			case 'ekb':
				if ( Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) || !base_form.findField('PrehospType_SysNick').getValue().inlist([ 'plan' ]) ) {
					base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
						return (rec.get('MedicalCareFormType_Code') != 3);
					});
				}
			break;

			case 'penza':
				var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');

				base_form.findField('MedicalCareFormType_id').getStore().clearFilter();
				base_form.findField('MedicalCareFormType_id').lastQuery = '';
				base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
					if (rec.get('MedicalCareFormType_id') == 1) {
						// Экстренная
						if (LpuUnitType_SysNick && LpuUnitType_SysNick.inlist(['dstac','hstac','pstac'])) {
							return false;
						} else {
							return true;
						}
					} else {
						return true;
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
				break;

			case 'krym':
				base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
					return (rec.get('MedicalCareFormType_Code') != 1);
				});
			break;
		}
	},
	setMedicalCareFormType: function() {
		var base_form = this.formPanel.getForm();

		switch ( getRegionNick() ) {
			case 'perm':
				if ( !Ext.isEmpty(base_form.findField('PrehospArrive_SysNick').getValue()) && base_form.findField('PrehospArrive_SysNick').getValue().inlist(['quick', 'evak', 'avia', 'nmedp']) ) {
					// Если поле "Кем доставлен" = 2. Скорая помощь или 3. Эвакопункт или 4. Санавиация или Неотложная медицинская помощь
					if ( Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
						// и нет отказа от госпитализации в приемном, то экстренная
						base_form.findField('MedicalCareFormType_id').setValue(1);
					}
					else {
						// Иначе если есть отказ от госпитализации в приемном, то неотложная
						base_form.findField('MedicalCareFormType_id').setValue(2);
					}
				}
				else if ( !Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) && base_form.findField('PrehospType_SysNick').getValue().inlist(['oper', 'extreme']) ) {
					// Иначе если поле "Тип госпитализации" = 2. Экстренно или 3. Экстренно по хирургическим показаниям, то экстренная
					// Иначе если есть входящее направление и поле "Тип направления" = На госпитализацию экстренную, то экстренная (поле Тип госпитализации зависит от этого)
					base_form.findField('MedicalCareFormType_id').setValue(1);
				}
				else {
					// Иначе плановая
					base_form.findField('MedicalCareFormType_id').setValue(3);
				}
			break;

			case 'krym':
				// @task https://redmine.swan.perm.ru/issues/109975
				if (
					!Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue())
				) {
					switch ( base_form.findField('PrehospType_SysNick').getValue() ) {
						// Если поле "Тип госпитализации" = «1. Планово», то «Плановая».
						case 'plan':
							base_form.findField('MedicalCareFormType_id').setValue(3);
						break;

						// Если поле "Тип госпитализации" = «2. Экстренно», то «Неотложная»
						case 'extreme':
						// Если поле "Тип госпитализации" = «3. Экстренно по хирургическим показания», то «Неотложная»
						case 'oper':
							base_form.findField('MedicalCareFormType_id').setValue(2);
						break;
					}
				}
			break;

			case 'kareliya':
				// Если отказ в приемном отделении, то 2 «Неотложная»;
				if ( !Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
					base_form.findField('MedicalCareFormType_id').setValue(2);
				}
				else if ( !Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) && base_form.findField('PrehospType_SysNick').getValue().inlist(['plan']) ) {
					// Иначе если Тип госпитализации = 1. Планово, то 3 «Плановая»;
					base_form.findField('MedicalCareFormType_id').setValue(3);
				}
				else if ( !Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) && base_form.findField('PrehospType_SysNick').getValue().inlist(['oper', 'extreme']) ) {
					// Иначе если Тип госпитализации = 2. Экстренно или 3. Экстренно по хирургическим показаниям, то 1 «Экстренная».
					base_form.findField('MedicalCareFormType_id').setValue(1);
				}
			break;

			case 'penza':
				this.filterMedicalCareFormType();
				if ( !Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) && base_form.findField('PrehospType_SysNick').getValue().inlist(['plan']) ) {
					// если Тип госпитализации = 1. Планово, то 3 «Плановая»;
					base_form.findField('MedicalCareFormType_id').setValue(3);
				}
				else if ( !Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) && base_form.findField('PrehospType_SysNick').getValue().inlist(['oper', 'extreme']) ) {
					// Иначе если Тип госпитализации = 2. Экстренно или 3. Экстренно по хирургическим показаниям
					var LpuUnitType_SysNick = base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick');
					if (LpuUnitType_SysNick && LpuUnitType_SysNick.inlist(['dstac','hstac','pstac'])) {
						// Если отделение из группы отделений типа «3. Дневной стационар при стационаре» или «4. Стационар на дому» или «5. Дневной стационар при поликлинике», то значение «экстренная» не доступна для выбора. то 2 «Плановая».
						base_form.findField('MedicalCareFormType_id').setValue(2);
					} else {
						// иначе 1 «Экстренная».
						base_form.findField('MedicalCareFormType_id').setValue(1);
					}
				}
			break;

			case 'ufa':
				if ( !Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) && base_form.findField('PrehospType_SysNick').getValue().inlist(['oper', 'extreme']) ) {
					// Если поле "Тип госпитализации" = 2. Экстренно или 3. Экстренно по хирургическим показаниям
					if ( Ext.isEmpty(base_form.findField('PrehospWaifRefuseCause_id').getValue()) ) {
						// и нет отказа от госпитализации в приемном, то экстренная
						base_form.findField('MedicalCareFormType_id').setValue(1);
					}
					else {
						// Иначе если есть отказ от госпитализации в приемном, то Неотложная;
						base_form.findField('MedicalCareFormType_id').setValue(2);
					}
				}
				else {
					// Иначе Плановая.
					base_form.findField('MedicalCareFormType_id').setValue(3);
				}
			break;

			case 'buryatiya':
				if ( base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_SysNick') == 'otk' ) {
					base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 2);
				}
				else if ( base_form.findField('LeaveType_prmid').getFieldValue('LeaveType_SysNick') == 'osmpp' && !Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) ) {
					if ( base_form.findField('PrehospType_SysNick').getValue() == 'plan' ) {
						base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 3);
					}
					else {
						base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 1);
					}
				}
			break;

			case 'ekb':
				// @task https://redmine.swan.perm.ru/issues/103200
				this.filterMedicalCareFormType();

				if ( !Ext.isEmpty(base_form.findField('PrehospType_SysNick').getValue()) && base_form.findField('PrehospType_SysNick').getValue().inlist(['plan']) ) {
					base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 3);
				}
				else {
					// Если поле "Тип госпитализации" = 2. Экстренно или 3. Экстренно по хирургическим показаниям или поле не заполнено
					base_form.findField('MedicalCareFormType_id').setFieldValue('MedicalCareFormType_Code', 1);
				}
			break;
		}
	},

	show: function() {
		sw.Promed.swLeavePriemEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].formParams || !arguments[0].formParams.EvnPS_id)
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_parametryi_formyi']);
			return false;
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.IsHosp = arguments[0].IsHosp || false;
		this.IsRefuse = arguments[0].IsRefuse || false;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.PayType_SysNick = null;

		this.UserLpuSection_id = arguments[0].UserLpuSection_id || null;
		this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id || null;

		this.doReset();
		this.center();
		this.formPanel.getForm().findField('LeaveType_fedid').on('select', function (combo, record) {
			var base_form = this.formPanel.getForm();
			sw.Promed.EvnPL.filterFedResultDeseaseType({
				fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
			});
			sw.Promed.EvnPL.filterFedLeaveType({
				fieldFedLeaveType: base_form.findField('LeaveType_fedid'),
				fieldFedResultDeseaseType: base_form.findField('ResultDeseaseType_fedid')
			});
		});
		var win = this,
			form = this.formPanel.getForm(),
			index,
			lpu_section_hosp_combo = form.findField('LpuSection_id'),
			refuse_cause_combo = form.findField('PrehospWaifRefuseCause_id');

		if ( getRegionNick().inlist([ 'krym', 'penza' ]) ) {
			this.filterMedicalCareFormType();
		}

		if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
			form.findField('UslugaComplex_id').clearBaseParams();
			form.findField('UslugaComplex_id').getStore().baseParams.filterByLpuSection = 1;

			if ( getRegionNick().inlist([ 'perm' ]) ) {
				form.findField('UslugaComplex_id').setVizitCodeFilters({
					isStac: true
				});
				form.findField('UslugaComplex_id').getStore().baseParams.isEvnPS = 1;

				form.findField('ResultDeseaseType_fedid').getStore().clearFilter();
				form.findField('ResultDeseaseType_fedid').lastQuery = '';
				form.findField('ResultDeseaseType_fedid').getStore().filterBy(function(rec) {
					return (rec.get('ResultDeseaseType_Code').toString().substr(0, 1) == '3');
				});
			}
		}

		if ( getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
			form.findField('LeaveType_prmid').getStore().clearFilter();
			form.findField('LeaveType_prmid').getStore().lastQuery = '';
			form.findField('LeaveType_prmid').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('LeaveType_Code')) && rec.get('LeaveType_Code').toString().substr(0, 1) == '6');
			});
			form.findField('LeaveType_prmid').fireEvent('change', form.findField('LeaveType_prmid'), null);

			if ( getRegionNick().inlist([ 'buryatiya' ]) ) {
				if ( form.findField('UslugaComplex_id').getStore().getCount() == 0 ) {
					form.findField('UslugaComplex_id').clearBaseParams();
					form.findField('UslugaComplex_id').setVizitCodeFilters({
						isStac: true
					});
					form.findField('UslugaComplex_id').setUslugaComplexCodeList([ '021613', '061129', '161129' ]);
				}
			}
		}
		else {
			form.findField('LeaveType_prmid').setContainerVisible(false);
		}

		if(lpu_section_hosp_combo.getStore().getCount() == 0){
			setLpuSectionGlobalStoreFilter({
				isStac: true
			});
			lpu_section_hosp_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		}
		this.findById('LPEF_PrehospWaifRefuseCauseButton').disable();
		form.setValues(arguments[0].formParams);
		switch (this.action) {
			case 'view':
				this.setTitle(lang['ishod_prebyivaniya_v_priemnom_otdelenii_prosmotr']);
			break;

			case 'edit':
				this.setTitle(lang['ishod_prebyivaniya_v_priemnom_otdelenii_redaktirovanie']);
			break;

			case 'add':
				this.setTitle(lang['ishod_prebyivaniya_v_priemnom_otdelenii_dobavlenie']);

				this.setMedicalCareFormTypeAllowBlank();
				this.setMedicalCareFormType();
			break;

			default:
				log('swLeavePriemEditWindow - action invalid');
				return false;
			break;
		}

		if(this.action == 'add')
		{
			win.allowEdit(true);

			if ( this.IsRefuse == true ) {
				if ( getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
					form.findField('LeaveType_prmid').setFieldValue('LeaveType_SysNick', 'otk');
				}
				else {
					form.findField('LpuSection_id').hideContainer();
				}
			}
			else if ( this.IsHosp == true && getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
				form.findField('LeaveType_prmid').setFieldValue('LeaveType_SysNick', 'gosp');
			}
			else if ( !getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
				form.findField('LpuSection_id').showContainer();
			}
		}
		else
		{
			win.allowEdit(false);
			if(win.action == 'edit')
			{
				win.allowEdit(true);
			}
		}
		
		//#157736 определяем доступность поля Характер:
		var DeseaseCombo = form.findField('DeseaseType_id');
		
		DeseaseCombo.setContainerVisible(false); DeseaseCombo.disable();
		if(getRegionNick()!='kz') {
			form.findField('Diag_id').getStore().load({
				params:{where:'where Diag_id='+form.findField('Diag_id').getValue() },
				callback: function() {
					var DiagCombo = form.findField('Diag_id');
					DiagCombo.setValue(DiagCombo.getValue());
					var diagcode = DiagCombo.getFieldValue('Diag_Code');
					if(diagcode.slice(0,1)!='Z') {//по ТЗ еще есть условие с датой, но оно всегда будет выполняться
						DeseaseCombo.setContainerVisible(true);
						DeseaseCombo.enable();
					}
				}
			});
		}
		// @task https://jira.is-mis.ru/browse/PROMEDWEB-3989
		// если комбобокс пустой, то установим принудительно значение поля "Характер" по параметру DeseaseType_Name
		if(Ext.isEmpty(DeseaseCombo.getValue())){
			var DeseaseType_Name = form.findField('DeseaseType_Name').getValue();
			DeseaseCombo.setFieldValue('DeseaseType_Name',DeseaseType_Name);
			
		}

		if ( getRegionNick().inlist([ 'buryatiya', 'pskov' ]) ) {
			form.findField('LeaveType_prmid').fireEvent('change', form.findField('LeaveType_prmid'), form.findField('LeaveType_prmid').getValue());
		}
		
		if ( arguments[0].formParams.LpuSection_id )
		{
			var index = lpu_section_hosp_combo.getStore().findBy(function(record, id) {
				if ( record.get('LpuSection_id') == lpu_section_hosp_combo.getValue() )
					return true;
				else
					return false;
			});
			lpu_section_hosp_combo.fireEvent('select', lpu_section_hosp_combo, lpu_section_hosp_combo.getStore().getAt(index), index);
		}

		if ( getRegionNick().inlist([ 'ekb', 'perm' ]) ) {
			var uslugacomplex_combo = form.findField('UslugaComplex_id');

			uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
			uslugacomplex_combo.getStore().removeAll();
			uslugacomplex_combo.getStore().baseParams.query = '';

			uslugacomplex_combo.getStore().baseParams.UslugaComplex_Date = form.findField('EvnPS_OutcomeDate').getValue();
			uslugacomplex_combo.getStore().baseParams.LpuSection_id = form.findField('LpuSection_pid').getValue();
			uslugacomplex_combo.getStore().baseParams.MedPersonal_id = form.findField('MedPersonal_pid').getValue();
			uslugacomplex_combo.getStore().baseParams.LpuSectionProfile_id = form.findField('LpuSectionProfile_id').getValue();
			uslugacomplex_combo.getStore().baseParams.FedMedSpec_id = form.findField('FedMedSpec_id').getValue();

			if ( getRegionNick().inlist([ 'ekb' ]) ) {
				form.findField('LpuSectionProfile_id').lastQuery = '';
				form.findField('LpuSectionProfile_id').getStore().removeAll();

				// получаем вид оплаты
				win.getLoadMask(LOAD_WAIT).show();
				Ext.Ajax.request({
					params: {
						'EvnPS_id': form.findField('EvnPS_id').getValue()
					},
					url: '/?c=EvnPS&m=getEvnPSPayTypeSysNick',
					callback: function(opt, success, response) {
						win.getLoadMask().hide();

						var PayType_SysNick = 'oms';
						if (success && response.responseText != '') {
							var result  = Ext.util.JSON.decode(response.responseText);
							if (result.PayType_SysNick) {
								PayType_SysNick = result.PayType_SysNick;
							}
						}

						if (PayType_SysNick == 'bud') {
							uslugacomplex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([350]);
						} else {
							uslugacomplex_combo.getStore().baseParams.UslugaComplexPartition_CodeList = Ext.util.JSON.encode([300, 301]);
						}

						form.findField('LpuSectionProfile_id').getStore().load({
							params: {
								LpuSection_id: form.findField('LpuSection_pid').getValue(),
								MedPersonal_id: form.findField('MedPersonal_pid').getValue(),
								LpuSectionProfileGRAPP_CodeIsNotNull: (PayType_SysNick == 'oms' ? 1 : null)
							},
							callback: function() {

							}
						});
					}
				});
			}
			else if ( getRegionNick() == 'perm' ) {
				form.findField('LpuSectionProfile_id').lastQuery = '';
				form.findField('LpuSectionProfile_id').getStore().removeAll();
				form.findField('LpuSectionProfile_id').getStore().load({
					params: {
						LpuSection_id: form.findField('LpuSection_pid').getValue()
					},
					callback: function() {
						
					}
				});

				// получаем вид оплаты
				win.getLoadMask(LOAD_WAIT).show();
				Ext.Ajax.request({
					params: {
						'EvnPS_id': form.findField('EvnPS_id').getValue()
					},
					url: '/?c=EvnPS&m=getEvnPSPayTypeSysNick',
					callback: function(opt, success, response) {
						win.getLoadMask().hide();

						var PayType_SysNick = 'oms';
						if (success && response.responseText != '') {
							var result  = Ext.util.JSON.decode(response.responseText);
							if (result.PayType_SysNick) {
								PayType_SysNick = result.PayType_SysNick;
							}
						}

						win.PayType_SysNick = PayType_SysNick;
					}
				});
			}
		} else if ( getRegionNick() == 'penza' ) {
			form.findField('LpuSectionProfile_id').lastQuery = '';
			form.findField('LpuSectionProfile_id').getStore().removeAll();
			form.findField('LpuSectionProfile_id').getStore().load({
				params: {},
				callback: function() {

				}
			});
		} else if ( getRegionNick().inlist([ 'krym', 'buryatiya' ])) {
			var filterLSP = function() {
				form.findField('LpuSectionProfile_id').lastQuery = '';
				form.findField('LpuSectionProfile_id').getStore().clearFilter();

				var lpuSectionProfileList = new Array();

				setMedStaffFactGlobalStoreFilter({
					id: form.findField('MedStaffFact_pid').getValue()
				});

				if ( swMedStaffFactGlobalStore.getCount() > 0 ) {
					var medStaffFactData = swMedStaffFactGlobalStore.getAt(0);

					if ( typeof medStaffFactData == 'object' ) {
						setLpuSectionGlobalStoreFilter({
							id: medStaffFactData.get('LpuSection_id')
						});

						if ( swLpuSectionGlobalStore.getCount() > 0 ) {
							var lpuSectionData = swLpuSectionGlobalStore.getAt(0);

							lpuSectionProfileList.push(lpuSectionData.get('LpuSectionProfile_id'));

							if ( !Ext.isEmpty(lpuSectionData.get('LpuSectionLpuSectionProfileList')) ) {
								lpuSectionProfileList = lpuSectionProfileList.concat(lpuSectionData.get('LpuSectionLpuSectionProfileList').split(','));
							}
						}
					}
				}

				form.findField('LpuSectionProfile_id').getStore().filterBy(function(rec) {
					return rec.get('LpuSectionProfile_id').inlist(lpuSectionProfileList);
				});
			}

			if ( form.findField('LpuSectionProfile_id').getStore().getCount() == 0 ) {
				form.findField('LpuSectionProfile_id').getStore().load({
					callback: filterLSP
				});
			}
			else {
				filterLSP();
			}
		}

		refuse_cause_combo.fireEvent('change', refuse_cause_combo, arguments[0].formParams.PrehospWaifRefuseCause_id, null);

        if (!Ext.isEmpty(arguments[0].formParams.ChildEvn_id)) {
            disableItems(win.formPanel);
            this.buttons[0].disable();
        }
		this.syncSize();
		this.doLayout();
	}
});
