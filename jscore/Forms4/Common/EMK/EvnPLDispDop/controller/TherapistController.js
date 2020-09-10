Ext6.define('common.EMK.EvnPLDispDop.controller.TherapistController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPLDispDop13TherapistController',

	setParams: function (record) {
		let contr = this, 
			view = contr.getView(), 
			data_form = view.DataForm.getForm(),
			vm = view.ownerPanel.getViewModel(),
			currentDate = new Date(),
			cdate = currentDate.dateFormat('d.m.Y'),
			ctime = currentDate.dateFormat('H:i'),
			//currentTime = Ext6.util.Format.date(currentDate ,'H:i'),
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		view.therapistEditorPanel.enable();
		view.isLoaded = false;
		if(!view.collapsed){//если сразу открыт
			contr.load();
		}
		
		view.record = record;
		view.loaded = false;
		data_form.setValues({
			'UslugaComplex_id': record.get('UslugaComplex_id'),
			'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
			'DopDispInfoConsent_id': record.get('DopDispInfoConsent_id'),
			'EvnVizitDispDop_pid': vm.get('EvnPLDispDop13_id'),
			'PersonEvn_id': vm.get('PersonEvn_id'),
			'Server_id': vm.get('Server_id'),
			'EvnUslugaDispDop_setDate': currentDate,
			'EvnUslugaDispDop_setTime': ctime,
			'EvnUslugaDispDop_didDate': currentDate,
			'EvnUslugaDispDop_didTime': ctime,
			'EvnUslugaDispDop_disDate': currentDate,
			'EvnUslugaDispDop_disTime': ctime,
			'Diag_id': 10944,
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
			data_form = view.DataForm.getForm(),
			dt = data_form.findField('EvnUslugaDispDop_didDate').getValue(),
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
	load: function () {
		let contr = this,
			view = this.getView(), 
			view_form = view.ViewForm.getForm(),
			data_form = view.DataForm.getForm();
		
		if(view.isLoaded) return;
		view.isLoaded = true;
		
		if(!Ext6.isEmpty(data_form.findField('EvnUslugaDispDop_id').getValue())) {
			view.mask('Загрузка');

			data_form.load({
				url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop',
				failure: function() {
					view.unmask();
				}.createDelegate(this),
				params: {
					EvnUslugaDispDop_id: data_form.findField('EvnUslugaDispDop_id').getValue(),
					ExtVersion: 6,
					panel: 'terapevt'
				},
				success: function(result_form, action) {
					view.unmask();
					view.therapistEditorPanel.setHtmlText(data_form.findField('therapist_text').getValue());
				}
			});
		}
	},
	
	doSave: function(field) {
		if(!Ext6.isEmpty(field)) {
			if(!field.containsFocus) {
				return;
			}
		}
		var contr = this,
			view = contr.getView(),
			vm = view.ownerPanel.getViewModel(),
			data_form = view.DataForm.getForm(),
			params = {};
		if(vm.get('action') == 'view') return;
		data_form.submit({
			url: '/?c=EvnUslugaDispDop&m=saveEvnUslugaDispDop',
			failure: function(result_form, action) {
				view.unmask();
			},
			params: {
				ExtVersion: 6
			},
			success: function(result_form, action) {
				view.unmask();
				if ( action.result ) {
					if ( action.result.EvnUslugaDispDop_id ) {

						data_form.findField('EvnUslugaDispDop_id').setValue(action.result.EvnUslugaDispDop_id);
						view.record.set('EvnUslugaDispDop_id', action.result.EvnUslugaDispDop_id);

						if (action.result.EvnVizitDispDop_id) {
							data_form.findField('EvnVizitDispDop_id').setValue(action.result.EvnVizitDispDop_id);
							view.record.set('EvnVizitDispDop_id', action.result.EvnVizitDispDop_id);
						}
						
						contr.updateStatus();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
						}
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
				}
			}
		});
	},
	openModalForm: function () {
		let me = this,
			DataForm = me.getView().DataForm.getForm(),
			vm = this.getView().ownerPanel.getViewModel();
		if(DataForm.findField('EvnUslugaDispDop_id').getValue()) {
			if(DataForm.findField('EvnUslugaDispDop_id').getValue()) {
				getWnd("swEvnUslugaDispDop13EditWindowExt6").show({
					needLoad: true,
					params: {
						title: "Прием (осмотр) врача-терапевта",
						blocktype: 'TherOverview',
						EvnPLDispDop13_id: vm.get('EvnPLDispDop13_id'),
						EvnUslugaDispDop_id: DataForm.findField('EvnUslugaDispDop_id').getValue(),
						UslugaComplex_Date: vm.get('EvnPLDispDop13_consDate'),
						ownerWin: me.getView()
					},
					callback: function (data) {
						if(data.isOnkoDiag && data.Diag_Code) {
							vm.set('Terapevt_OnkoDiag_Code', data.Diag_Code);
						} else vm.set('Terapevt_OnkoDiag_Code', null);
						
						me.getView().ownerPanel.getController().checkSpecifics();
						
						/*if(data.isOnkoDiag){
							var cntr = me.ownerPanel.getController();
							cntr.checkSpecifics(true);
						}*/
					}
				});
			}
		}
	}

});