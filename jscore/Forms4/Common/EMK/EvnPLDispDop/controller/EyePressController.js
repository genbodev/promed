Ext6.define('common.EMK.EvnPLDispDop.controller.EyePressController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPLDispDop13EyePressController',
	setParams: function(record) {
		var view = this.getView(),
			base_form = view.EyePressForm.getForm(),
			ownerViewModel = view.ownerPanel.getViewModel(),
			cd = new Date(),
			cdate = cd.dateFormat('d.m.Y'),
			ctime = cd.dateFormat('H:i'),
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		view.record = record;

		base_form.findField('eye_pressure_left').prevValue = ownerViewModel.get('eye_pressure_left');
		base_form.findField('eye_pressure_right').prevValue = ownerViewModel.get('eye_pressure_right');

		base_form.setValues({
			'UslugaComplex_id': record.get('UslugaComplex_id'),
			'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
			'DopDispInfoConsent_id': record.get('DopDispInfoConsent_id'),
			'EvnVizitDispDop_pid': ownerViewModel.get('EvnPLDispDop13_id'),
			'PersonEvn_id': ownerViewModel.get('PersonEvn_id'),
			'Server_id': ownerViewModel.get('Server_id'),
			'EvnUslugaDispDop_setDate': cd,
			'EvnUslugaDispDop_setTime': ctime,
			'EvnUslugaDispDop_didDate': cd,
			'EvnUslugaDispDop_didTime': ctime,
			'EvnUslugaDispDop_disDate': cd,
			'EvnUslugaDispDop_disTime': ctime,
			'Diag_id': 10944,//Z10.8 Рутинная общая проверка здоровья
			'LpuSection_id': !Ext6.isEmpty(msf.LpuSection_id) ? msf.LpuSection_id : null,
			'MedStaffFact_id': !Ext6.isEmpty(msf.MedStaffFact_id) ? msf.MedStaffFact_id : null,
			'MedPersonal_id': !Ext6.isEmpty(msf.MedPersonal_id) ? msf.MedPersonal_id : null,
			'ExaminationPlace_id': 1
		});
	},
	onExpand: function() {
		this.load();
		this.getView().ownerPanel.AccordionPanel.collapseOtherPanels(this.getView());
	},
	updateStatus: function() {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			base_form = view.EyePressForm.getForm(),
			dt = base_form.findField('EvnUslugaDispDop_didDate').getValue(),
			code = view.SurveyType_Code,
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		if(	Ext6.isEmpty(vm.get('MedPersonal_SurveyType_Code'+code)) &&
			Ext6.isEmpty(vm.get('LpuNick_SurveyType_Code'+code)) &&
			Ext6.isEmpty(vm.get('Date_SurveyType_Code'+code)) &&
			msf
		) {
			view.record.set('EvnUslugaDispDop_MedPersonalFio', msf.MedPersonal_FIO);
			view.record.set('EvnUslugaDispDop_Lpu_Nick', Ext6.isEmpty(msf.Lpu_Nick) ? '' : msf.Lpu_Nick);
			view.record.set('EvnUslugaDispDop_didDate', dt);
			
			vm.set('MedPersonal_SurveyType_Code'+code, CaseLettersPersonFio(view.record.get('EvnUslugaDispDop_MedPersonalFio')));
			vm.set('LpuNick_SurveyType_Code'+code, view.record.get('EvnUslugaDispDop_Lpu_Nick'));
			//vm.set('Date_SurveyType_Code'+code, view.record.get('EvnUslugaDispDop_didDate'));
			let ddt = view.record.get('EvnUslugaDispDop_didDate');
			vm.set('Date_SurveyType_Code'+code, Ext6.isDate(ddt) ? Ext6.Date.format(ddt,'d.m.Y') : ddt);
		}
	},
	saveEyePress: function(me, evn, eOpts) {
		if(me.prevValue == me.value)
			return;
		var contr = this,
			view = contr.getView(),
			vm = view.ownerPanel.getViewModel(),
			base_form = view.EyePressForm.getForm(),
			params = {};

		me.prevValue = me.value;

		params.eye_pressure_left = vm.get('eye_pressure_left');
		params.eye_pressure_right = vm.get('eye_pressure_right');
		params.ExtVersion = 6;
		view.mask('Сохранение');

		base_form.submit({
			url: '/?c=EvnUslugaDispDop&m=saveEvnUslugaDispDop',
			failure: function(result_form, action) {
				view.unmask();
			},
			params: params,
			success: function(result_form, action) {
				view.unmask();
				if ( action.result ) {
					if ( action.result.EvnUslugaDispDop_id ) {

						base_form.findField('EvnUslugaDispDop_id').setValue(action.result.EvnUslugaDispDop_id);

						if (action.result.EvnVizitDispDop_id) {
							base_form.findField('EvnVizitDispDop_id').setValue(action.result.EvnVizitDispDop_id);
						}
						
						contr.updateStatus();
					}
					else {
						if ( action.result.Error_Msg ) {
							Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
						}
					}
				}
				else {
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
				}
			}
		});
	},
	load: function() {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			base_form = view.EyePressForm.getForm();
		if(view.isLoaded) return;
		view.isLoaded = true;
		base_form.findField('eye_pressure_left').setAllowBlank(false);
		base_form.findField('eye_pressure_right').setAllowBlank(false);
		
		if(!Ext6.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
			view.mask('Загрузка');

			base_form.load({
				url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop',
				failure: function() {
					view.unmask();
				}.createDelegate(this),
				params: {
					EvnUslugaDispDop_id: base_form.findField('EvnUslugaDispDop_id').getValue(),
					ExtVersion: 6,
					panel: 'eye'
				},
				success: function(result_form, action) {
					view.unmask();
				}
			});
		}
	},
	onChangeODBP: function (field) {
		let view = this.getView();
		let odbp_maxVal = view.EyePressForm.ODBP_maxValue;
		let odbp_minVal = view.EyePressForm.ODBP_minValue;

		if(field.value > odbp_maxVal) {
			view.ODWarningLowerMin.hide();
			view.ODWarningOverMax.show();
		} else if (field.value < odbp_minVal) {
			view.ODWarningLowerMin.show();
			view.ODWarningOverMax.hide();
		} else {
			view.ODWarningLowerMin.hide();
			view.ODWarningOverMax.hide();
		}
	},
	onChangeOSBP: function (field) {
		let view = this.getView();
		let osbp_maxVal = view.EyePressForm.OSBP_maxValue;
		let osbp_minVal = view.EyePressForm.OSBP_minValue;

		if(field.value > osbp_maxVal) {
			view.OSWarningLowerMin.hide();
			view.OSWarningOverMax.show();
		} else if (field.value < osbp_minVal) {
			view.OSWarningLowerMin.show();
			view.OSWarningOverMax.hide();
		} else {
			view.OSWarningLowerMin.hide();
			view.OSWarningOverMax.hide();
		}
	},
	openModalForm: function () {
		let me = this,
			DataForm = me.getView().EyePressForm.getForm(),
			vm = this.getView().ownerPanel.getViewModel();
		if(DataForm.findField('EvnUslugaDispDop_id').getValue()) {
			getWnd("swEvnUslugaDispDop13EditWindowExt6").show({
				needLoad: true,
				params: {
					title: "Измерение внутриглазного давления",
					blocktype: 'ArteriaPress',
					EvnPLDispDop13_id: vm.get('EvnPLDispDop13_id'),
					EvnUslugaDispDop_id: DataForm.findField('EvnUslugaDispDop_id').getValue(),
					UslugaComplex_Date: vm.get('EvnPLDispDop13_consDate')
				},
				callback: function (data) {

				}
			});
		}
	}
});