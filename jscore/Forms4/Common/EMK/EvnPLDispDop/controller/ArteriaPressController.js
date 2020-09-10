Ext6.define('common.EMK.EvnPLDispDop.controller.ArteriaPressController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPLDispDop13ArteriaPressController',
	setParams: function(record) {
		var view = this.getView(),
			base_form = view.ArteriaPressForm.getForm(),
			ownerViewModel = view.ownerPanel.getViewModel(),
			cd = new Date(),
			cdate = cd.dateFormat('d.m.Y'),
			ctime = cd.dateFormat('H:i'),
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		view.record = record;
		base_form.findField('systolic_blood_pressure').prevValue = ownerViewModel.get('systolic_blood_pressure');
		base_form.findField('diastolic_blood_pressure').prevValue = ownerViewModel.get('diastolic_blood_pressure');

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
	updateStatus: function() {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			base_form = view.ArteriaPressForm.getForm(),
			dt = base_form.findField('EvnUslugaDispDop_didDate').getValue(),
			code = view.SurveyType_Code,
			msf = null;
		if (!Ext6.isEmpty(view.ownerPanel.ownerWin)) {
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		}
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
	onExpand: function() {
		this.load();
		this.getView().ownerPanel.AccordionPanel.collapseOtherPanels(this.getView());
	},
	load: function() {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			base_form = view.ArteriaPressForm.getForm();
		if(view.isLoaded) return;
		view.isLoaded = true;
		base_form.findField('systolic_blood_pressure').setAllowBlank(false);
		base_form.findField('diastolic_blood_pressure').setAllowBlank(false);
		
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
					panel: 'arteri'
				},
				success: function(result_form, action) {
					view.unmask();
				}
			});
		}
		if(view.ownerPanel && view.ownerPanel.getConsentStore()) //поиск холестерина
			record = view.ownerPanel.getConsentStore().findRecord('SurveyType_id', 5, undefined, undefined, undefined, true);
		if(record && record.get('EvnUslugaDispDop_id')) {
			view.mask('Загрузка');

			Ext6.Ajax.request({
				url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop',
				params: {
					EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id'),
					ExtVersion: 6,
					panel: 'arteri'
				},
				failure: function() {
					view.unmask();
				}.createDelegate(this),
				success: function(response) {
					view.unmask();
					
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj && response_obj.length > 0) {
						let data=response_obj[0];
						
						if(data.Error_Msg) {
							Ext6.Msg.alert('Ошибка', data.Error_Msg);
							return;
						}
						if(data.EvnUslugaDispDop_id) record.set('EvnUslugaDispDop_id', data.EvnUslugaDispDop_id);
						vm.set('total_cholesterol', data.total_cholesterol);
					}
				}
			});
		}
	},
	saveArteriaPress: function(me, evn, eOpts) {
		if(me.prevValue == me.value)
			return;
		var contr = this,
			view = contr.getView(),
			vm = view.ownerPanel.getViewModel(),
			base_form = view.ArteriaPressForm.getForm(),
			ssriskPanel = view.ownerPanel.getSSRblock(),
			params = {};

		me.prevValue = me.value;

		params.systolic_blood_pressure = vm.get('systolic_blood_pressure');
		params.diastolic_blood_pressure = vm.get('diastolic_blood_pressure');
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
						
						if(ssriskPanel) {
							ssriskPanel.getController().loadScoreField();
						}
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
	onChangeSystolicBP: function (field) {
		let view = this.getView();
		let sbp_maxVal = view.ArteriaPressForm.SystolicBP_maxValue;
		let sbp_minVal = view.ArteriaPressForm.SystolicBP_minValue;

		if(field.value > sbp_maxVal) {
			view.SystolicWarningLowerMin.hide();
			view.SystolicWarningOverMax.show();
		} else if (field.value < sbp_minVal) {
			view.SystolicWarningLowerMin.show();
			view.SystolicWarningOverMax.hide();
		} else {
			view.SystolicWarningLowerMin.hide();
			view.SystolicWarningOverMax.hide();
		}
	},
	onChangeDiastolicBP: function (field) {
		let view = this.getView();
		let dbp_maxVal = view.ArteriaPressForm.DiastolicBP_maxValue;
		let dbp_minVal = view.ArteriaPressForm.DiastolicBP_minValue;

		if(field.value > dbp_maxVal) {
			view.DiastolicWarningLowerMin.hide();
			view.DiastolicWarningOverMax.show();
		} else if (field.value < dbp_minVal) {
			view.DiastolicWarningLowerMin.show();
			view.DiastolicWarningOverMax.hide();
		} else {
			view.DiastolicWarningLowerMin.hide();
			view.DiastolicWarningOverMax.hide();
		}
	},
	openModalForm: function () {
		let me = this,
			DataForm = me.getView().ArteriaPressForm.getForm(),
			vm = this.getView().ownerPanel.getViewModel();
		if(DataForm.findField('EvnUslugaDispDop_id').getValue()) {
			getWnd("swEvnUslugaDispDop13EditWindowExt6").show({
				needLoad: true,
				params: {
					title: "Артериальное давление",
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