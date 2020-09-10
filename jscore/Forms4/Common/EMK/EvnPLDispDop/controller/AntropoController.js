Ext6.define('common.EMK.EvnPLDispDop.controller.AntropoController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPLDispDop13AntropoController',
	setParams: function(record) {
		var view = this.getView(),
			DataForm = view.DataForm.getForm(),
			vm = view.ownerPanel.getViewModel(),
			currentDate = new Date(),
			cdate = currentDate.dateFormat('d.m.Y'),
			ctime = currentDate.dateFormat('H:i'),
			//currentTime = Ext6.util.Format.date(currentDate ,'H:i'),
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		view.isLoaded = false;
		view.record = record;

		DataForm.setValues({
			'EvnUslugaDispDop_id': record.get('EvnUslugaDispDop_id'),
			'UslugaComplex_id': record.get('UslugaComplex_id'),
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
			DataForm = view.DataForm.getForm(),
			dt = DataForm.findField('EvnUslugaDispDop_didDate').getValue(),
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
			DataForm = view.DataForm.getForm();
		if(view.isLoaded) return;
		view.isLoaded = true;
		
		if(!Ext6.isEmpty(DataForm.findField('EvnUslugaDispDop_id').getValue())) {
			view.mask('Загрузка');

			DataForm.load({
				url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop',
				failure: function() {
					view.unmask();
				}.createDelegate(this),
				params: {
					EvnUslugaDispDop_id: DataForm.findField('EvnUslugaDispDop_id').getValue(),
					ExtVersion: 6,
					panel: 'antropo'
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
	doSave: function() {
		var contr = this,
			view = contr.getView(),
			vm = view.ownerPanel.getViewModel(),
			DataForm = view.DataForm.getForm(),
			params = {};
		params.body_mass_index = vm.get('body_mass_index');
		params.ExtVersion = 6;
		
		DataForm.submit({
			url: '/?c=EvnUslugaDispDop&m=saveEvnUslugaDispDop',
			failure: function(result_form, action) {
				view.unmask();
			},
			params: params,
			success: function(result_form, action) {
				view.unmask();
				if ( action.result ) {
					if ( action.result.EvnUslugaDispDop_id ) {

						DataForm.findField('EvnUslugaDispDop_id').setValue(action.result.EvnUslugaDispDop_id);

						if (action.result.EvnVizitDispDop_id) {
							DataForm.findField('EvnVizitDispDop_id').setValue(action.result.EvnVizitDispDop_id);
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
		let view = this.getView(),
			DataForm = view.DataForm.getForm(),
			vm = view.ownerPanel.getViewModel();
		if(DataForm.findField('EvnUslugaDispDop_id').getValue()) {
			getWnd("swEvnUslugaDispDop13EditWindowExt6").show({
				needLoad: true,
				params: {
					title: "Антропометрия",
					blocktype: 'Antropo',
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