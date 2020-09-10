Ext6.define('common.EMK.EvnPLDispDop.controller.SSRiskController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPLDispDop13SSRiskController',
	setParams: function(record) {
		var view = this.getView(),
			base_form = view.DataForm.getForm(),
			ownerViewModel = view.ownerPanel.getViewModel(),
			cd = new Date(),
			cdate = cd.dateFormat('d.m.Y'),
			ctime = cd.dateFormat('H:i'),
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		view.isLoaded = false;
		
		if(!Ext6.isEmpty(record)) {//возможно создание раздела без услуги
			view.record = record;
			view.EvnUslugaDispDop_id = record.get('EvnUslugaDispDop_id');

			base_form.findField('total_cholesterol').prevValue = ownerViewModel.get('total_cholesterol');

			base_form.setValues({
				'UslugaComplex_id': record.get('UslugaComplex_id'),
				'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
				'DopDispInfoConsent_id': record.get('DopDispInfoConsent_id'),
				'EvnVizitDispDop_pid': ownerViewModel.get('EvnPLDispDop13_id'),
				'PersonEvn_id': ownerViewModel.get('PersonEvn_id'),
				'Server_id': ownerViewModel.get('Server_id'),
				'EvnUslugaDispDop_setDate': cdate,
				'EvnUslugaDispDop_setTime': ctime,
				'EvnUslugaDispDop_didDate': cdate,
				'EvnUslugaDispDop_didTime': ctime,
				'EvnUslugaDispDop_disDate': cdate,
				'EvnUslugaDispDop_disTime': ctime,
				'Diag_id': 10944,//Z10.8 Рутинная общая проверка здоровья
				'LpuSection_id': !Ext6.isEmpty(msf.LpuSection_id) ? msf.LpuSection_id : null,
				'MedStaffFact_id': !Ext6.isEmpty(msf.MedStaffFact_id) ? msf.MedStaffFact_id : null,
				'MedPersonal_id': !Ext6.isEmpty(msf.MedPersonal_id) ? msf.MedPersonal_id : null,
				'ExaminationPlace_id': 1
			});
		}
	},
	onExpand: function() {
		this.load();
		this.getView().ownerPanel.AccordionPanel.collapseOtherPanels(this.getView());
	},
	load: function() {
		var contr = this,
			view = contr.getView(),
			vm = view.ownerPanel.getViewModel(),
			base_form = view.DataForm.getForm(),
			params = {};
		if(view.isLoaded) return;
		view.isLoaded = true;
		if(!Ext6.isEmpty(base_form.findField('EvnUslugaDispDop_id').getValue())) {
			base_form.load({
				url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop',
				failure: function() {
					view.unmask();
				}.createDelegate(this),
				params: {
					EvnUslugaDispDop_id: base_form.findField('EvnUslugaDispDop_id').getValue(),
					ExtVersion: 6,
					panel: 'SSR'
				},
				success: function(result_form, action) {
					view.unmask();
					
					var responseObj = new Object();
					
					if ( action && action.response && action.response.responseText ) {
						responseObj = Ext.util.JSON.decode(action.response.responseText);

						if ( responseObj.length > 0 ) {
							responseObj = responseObj[0];
							
						}
					}
				}
			});
		}
	},
	updateStatus: function() {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			base_form = view.DataForm.getForm(),
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
			if(view.record) {
				view.record.set('EvnUslugaDispDop_MedPersonalFio', msf.MedPersonal_FIO);
				view.record.set('EvnUslugaDispDop_Lpu_Nick', Ext6.isEmpty(msf.Lpu_Nick) ? '' : msf.Lpu_Nick);
				view.record.set('EvnUslugaDispDop_didDate', dt);
			}
			vm.set('MedPersonal_SurveyType_Code'+code, CaseLettersPersonFio(msf.MedPersonal_FIO));
			vm.set('LpuNick_SurveyType_Code'+code, view.record.get('EvnUslugaDispDop_Lpu_Nick'));
			let ddt = view.record.get('EvnUslugaDispDop_didDate');
			vm.set('Date_SurveyType_Code'+code, Ext6.isDate(ddt) ? Ext6.Date.format(ddt,'d.m.Y') : ddt);
		}
	},
	saveSSRisk: function() {
		var contr = this,
			view = contr.getView(),
			vm = view.ownerPanel.getViewModel(),
			base_form = view.DataForm.getForm(),
			params = {};
		
		if(!Ext6.isEmpty(base_form.findField('UslugaComplex_id').getValue()) ) {
			params.EvnPLDispDop13_SumRick = base_form.findField('EvnPLDispDop13_SumRick').getValue();
			params.RiskType_id = base_form.findField('RiskType_id').getValue();
			params.ExtVersion = 6;
			view.mask('Сохранение');
			base_form.submit({
				url: '/?c=EvnUslugaDispDop&m=saveEvnUslugaDispDop',
				failure: function(result_form, action) {
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
		}
	},
	// сохранение уровня холестерина
	saveCholesterol: function() {
		var contr = this,
			view = this.getView(),
			base_form = view.DataForm.getForm(),
			ownerViewModel = view.ownerPanel.getViewModel(),
			params = {},
			msf = view.ownerPanel.ownerWin.userMedStaffFact,
			record = null,
			cd = new Date(),
			total_cholesterol = ownerViewModel.get('total_cholesterol');
		if(view.ownerPanel && view.ownerPanel.getConsentStore())
			record = view.ownerPanel.getConsentStore().findRecord('SurveyType_id', 5, undefined, undefined, undefined, true);
		if(!Ext6.isEmpty(total_cholesterol)) {
			if( Ext6.isEmpty(record) || !(record.get('DopDispInfoConsent_IsAgree') || record.get('DopDispInfoConsent_IsEarlier')) ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Нет согласия на определение уровня холестерина'));
			} else {
				//параметры надо определить заново (просто брать от сср нехорошо, мы же можем в сср их править)
				params = {
					total_cholesterol: total_cholesterol,
					DopDispInfoConsent_id: record.get('DopDispInfoConsent_id'),
					EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id'),
					UslugaComplex_id: record.get('UslugaComplex_id'),
					Diag_id: 10944,
					ExaminationPlace_id: 1,
					LpuSection_id: !Ext6.isEmpty(msf.LpuSection_id) ? msf.LpuSection_id : null,
					MedStaffFact_id: !Ext6.isEmpty(msf.MedStaffFact_id) ? msf.MedStaffFact_id : null,
					EvnUslugaDispDop_didDate: cd.dateFormat('d.m.Y'),
					EvnUslugaDispDop_didTime: cd.dateFormat('H:i'),
					Server_id: ownerViewModel.get('Server_id'),
					MedPersonal_id: !Ext6.isEmpty(msf.MedPersonal_id) ? msf.MedPersonal_id : null,
					PersonEvn_id: ownerViewModel.get('PersonEvn_id'),
					EvnVizitDispDop_pid: ownerViewModel.get('EvnPLDispDop13_id'),
					ExtVersion: 6
				}

				Ext6.Ajax.request({
					url: '/?c=EvnUslugaDispDop&m=saveEvnUslugaDispDop',
					params: params,
					success: function(response) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if(response_obj.Error_Msg) {
							Ext6.Msg.alert('Ошибка', response_obj.Error_Msg);
							return;
						}
						if(response_obj.EvnUslugaDispDop_id) record.set('EvnUslugaDispDop_id', response_obj.EvnUslugaDispDop_id);
						contr.loadScoreField();
					}
				});
			}
		}
	},
	loadScoreField: function(field) {
		var me = this,
			view = me.getView(),
			base_form = view.DataForm.getForm(),
			ownerViewModel = view.ownerPanel.getViewModel();

		let total_cholesterol = ownerViewModel.get('total_cholesterol'),
			systolic_blood_pressure = ownerViewModel.get('systolic_blood_pressure'),
			diastolic_blood_pressure = ownerViewModel.get('diastolic_blood_pressure');

		if(Ext6.isEmpty(total_cholesterol) || Ext6.isEmpty(systolic_blood_pressure) || Ext6.isEmpty(diastolic_blood_pressure) || !ownerViewModel.get('EvnPLDispDop13_IsSmoking'))
			return;
		
		view.mask('Расчёт суммарного сердечно-сосудистого риска');
		
		/*Ext6.Ajax.request({
			callback: function(options, success, response) {
				view.unmask();
				if ( success ) {
					var response_obj = Ext6.util.JSON.decode(response.responseText);
					var score = response_obj.SCORE;
					if ( score ) {
						base_form.findField('EvnPLDispDop13_SumRick').setValue(score);

						if(score < 1) base_form.findField('RiskType_id').setValue(1);
						else if(score >= 1 && score < 5) base_form.findField('RiskType_id').setValue(2);
						else if(score >= 5 && score < 10) base_form.findField('RiskType_id').setValue(3);
						else base_form.findField('RiskType_id').setValue(4);

						base_form.findField('EvnPLDispDop13_SumRick').fireEvent('loadScore', base_form.findField('RiskType_id').getValue(), base_form.findField('EvnPLDispDop13_SumRick').getValue());
					}
					me.saveSSRisk();
				}
			},
			params: {
				EvnPLDisp_id: ownerViewModel.get('EvnPLDispDop13_id')
			},
			url: '/?c=EvnUslugaDispDop&m=loadScoreField'
		});
		*/
		
		Ext6.Ajax.request({
			callback: function(options, success, response) {
				view.unmask();
				if ( success ) {
					var response_obj = Ext6.util.JSON.decode(response.responseText);
					var score = response_obj.SCORE,
						RiskType_id = response_obj.RiskType_id;
					if ( score && RiskType_id) {
						base_form.findField('EvnPLDispDop13_SumRick').setValue(score);
						base_form.findField('RiskType_id').setValue(RiskType_id);
						
						me.saveSSRisk();
					}
				}
			},
			params: {
				EvnPLDispDop13_id: ownerViewModel.get('EvnPLDispDop13_id')
			},
			url: '/?c=EvnPLDispDop13&m=saveEvnPLDispDop13_SumRick'
		});
	},
	/*openModalForm: function () {
		let me = this,
			ViewForm = me.getView().ViewForm.getForm(),
			DataForm = me.getView().DataForm.getForm(),
			vm = this.getView().ownerPanel.getViewModel();
		getWnd("swEvnUslugaDispDop13EditWindowExt6").show({
			needLoad: true,
			params: {
				title: "Сердечно-сосудистый риск",
				blocktype: 'SSRisk',
				EvnPLDispDop13_id: vm.get('EvnPLDispDop13_id'),
				UslugaComplex_Date: vm.get('EvnPLDispDop13_consDate'),
				ViewValues: ViewForm.getValues(),
				DataValues: DataForm.getValues()
			},
			callback: function (data) {

			}
		});
	}*/
	
	openModalForm: function () {
		let view = this.getView(),
			DataForm = view.DataForm.getForm(),
			vm = this.getView().ownerPanel.getViewModel();
		if(DataForm.findField('EvnUslugaDispDop_id').getValue()) {
			getWnd("swEvnUslugaDispDop13EditWindowExt6").show({
				needLoad: true,
				params: {
					panelCode: "Gynecologist",
					SurveyType_isVizit: view.record ? view.record.get('SurveyType_isVizit') : null,
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