Ext6.define('common.EMK.EvnPLDispDop.controller.IndividualProphConsultController', {
	extend: 'Ext6.app.ViewController',
	alias: 'controller.EvnPLDispDop13IndividualProphConsultController',
	setParams: function(record) {
		var view = this.getView(),
			data_form = view.DataForm.getForm(),
			main_form = view.ownerPanel.MainForm.getForm(),
			vm = view.ownerPanel.getViewModel(),
			currentDate = new Date(),
			cdate = currentDate.dateFormat('d.m.Y'),
			ctime = currentDate.dateFormat('H:i'),
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		view.record = record;
		
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
	onExpand: function() {
		this.load();
		this.getView().ownerPanel.AccordionPanel.collapseOtherPanels(this.getView());
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
	onChangeGroupBox: function(field, newValues, oldValues) {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			groupbox = view.queryById('IndiProfConsultCheck'),
			ids = groupbox.getValue().DispRiskFactorCons_id;
		if(Ext6.isString(ids)) ids = [ids];
		view.mask('Подождите');
		//скрыть/показать нужные тексты:
		setTimeout(function() {
			groupbox.getBoxes().forEach(function(box) {
				let textfield = view.queryById('risktext'+box.DispRiskFactorCons_id);
				if(textfield)
					textfield.setVisible(Ext6.isEmpty(ids) ? false : ids.in_array(box.DispRiskFactorCons_id));
			});
			view.unmask();
			contr.doSave();
		}, 1);
	},
	doSave: function() {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			data_form = view.DataForm.getForm(),
			groupbox = view.queryById('IndiProfConsultCheck'),
			ids = groupbox.getValue().DispRiskFactorCons_id,
			cons = [];
		
		if(Ext6.isEmpty(data_form.findField('DopDispInfoConsent_id').getValue())) return;
		
		if (!Ext6.isEmpty(view.ownerPanel.ownerWin.userMedStaffFact)) {
			msf = view.ownerPanel.ownerWin.userMedStaffFact;
		}
		if(Ext6.isString(ids)) ids = [ids];
		
		groupbox.getBoxes().forEach(function(box) {
			let textfield = view.queryById('risktext'+box.DispRiskFactorCons_id);
			let isChecked = !Ext6.isEmpty(ids) ? ids.in_array(box.DispRiskFactorCons_id) : false;
			cons.push({
				DispRiskFactorCons_id: box.DispRiskFactorCons_id,
				DispCons_id: box.DispCons_id,
				checked: isChecked,
				DispCons_Text: isChecked ? (Ext6.isEmpty(box.DispCons_id) ? box.DispCons_TextDefault : textfield.getValue() ) : ''
			});
			if(!isChecked) box.DispCons_id = null;
		});
		view.mask('Сохранение');
		data_form.submit({
			url: '/?c=EvnUslugaDispDop&m=saveEvnUslugaDispDop',
			failure: function(result_form, action) {
				view.unmask();
			},
			params: {
				UslugaComplex_id: view.UslugaComplex_id,
				indi_prof_consult: Ext6.util.JSON.encode(cons),
				ExtVersion: 6
			},
			success: function(result_form, action) {
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
					if(Ext6.isArray(action.result.indi_prof_consult)) {
						action.result.indi_prof_consult.forEach(function(el){
							groupbox.getBoxes().forEach(function(box){
								if(box.DispRiskFactorCons_id == el['DispRiskFactorCons_id'])
									box.DispCons_id = el['DispCons_id'];
							});
						});
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
				}
				view.unmask();
			}
		});
	},
	reset: function() {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel();
		let checks_container = view.queryById('IndiProfConsultChecksContainer');
		let checkboxgroup = view.queryById('IndiProfConsultCheck');
		let text_container = view.queryById('TextConsultContainer');
		if(!Ext6.isEmpty(checkboxgroup)) checkboxgroup.destroy();
		text_container.query('textareafield').forEach(function(field){ field.destroy(); });
	},
	load: function() {
		var contr = this,
			view = this.getView(),
			win = view.ownerPanel,
			vm = win.getViewModel(),
			data_form = view.DataForm.getForm();
		view.mask('Загрузка');
		
		Ext6.Ajax.request({
			url: '/?c=EvnPLDispDop13&m=getIndiProfConsult',
			params: {
				EvnPLDispDop13_id: vm.get('EvnPLDispDop13_id')
			},
			failure: function (response, opts) {
				view.unmask();
			},
			success: function (response, opts) {
				view.unmask();
				
				if (response && response.responseText) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					
					if (!response_obj.success) {
						if(!Ext6.isEmpty(response_obj.Error_Msg)){
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
							return;
						}
						sw.swMsg.alert(langs('Ошибка'), 'Ошибка при чтении данных индивидуального профилактического консультирования.');
						return;
					} else {
						if(!Ext6.isEmpty(response_obj.data)) {
							var data = response_obj.data;
							
							let checks_container = view.queryById('IndiProfConsultChecksContainer');
							view.reset();
							let text_container = view.queryById('TextConsultContainer');
							
							let checkboxgroup = {
								xtype: 'checkboxgroup',
								listConfig: {
									resizable: true
								},
								bind: {
									disabled: '{action == "view"}'
								},
								name: 'risk',
								itemId: 'IndiProfConsultCheck',
								columns: 2,
								width: '100%',
								vertical: true,
								fieldLabel: '',
								labelWidth: 120,
								fresh: true,
								listeners: {
									change: 'onChangeGroupBox'
								},
								items: []
							};
							
							data.forEach(function(risk) {
								checkboxgroup.items.push({
									boxLabel: risk.DispRiskFactorCons_Name,
									inputValue: risk.DispRiskFactorCons_id,
									name: 'DispRiskFactorCons_id',
									DispCons_TextDefault: risk.DispProfCons_Text,
									//text: risk.DispCons_Text,
									DispCons_id: risk.DispCons_id,
									DispRiskFactorCons_id: risk.DispRiskFactorCons_id,
									DispCons_Text: risk.DispCons_Text,
									//DispProfCons_Text: risk.DispProfCons_Text
									checked: !Ext6.isEmpty(risk.DispCons_id)
								});
								
								text_container.add({
									xtype: 'textareafield',
									bind: {
										disabled: '{action == "view"}'
									},
									itemId: 'risktext'+risk.DispRiskFactorCons_id,
									width: '100%',
									listeners: {
										blur: 'doSave'
									},
									grow: true,
									hidden: Ext6.isEmpty(risk.DispCons_id),
									value: Ext6.isEmpty(risk.DispCons_id) ? risk.DispProfCons_Text : risk.DispCons_Text
								});
							});
							
							checks_container.add(checkboxgroup);
							
							if(!Ext6.isEmpty(data_form.findField('EvnUslugaDispDop_id').getValue())) {
								data_form.load({
									url: '/?c=EvnUslugaDispDop&m=loadEvnUslugaDispDop',
									failure: function() {
										view.unmask();
									}.createDelegate(this),
									params: {
										EvnUslugaDispDop_id: data_form.findField('EvnUslugaDispDop_id').getValue(),
										ExtVersion: 6,
										panel: 'profcons'
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
						}
					}
				} else return;
			}
		});
	},
	openModalForm: function () {
		let me = this,
			ViewForm = me.getView().ViewForm.getForm(),
			DataForm = me.getView().DataForm.getForm(),
			vm = this.getView().ownerPanel.getViewModel();
		
		if(DataForm.findField('EvnUslugaDispDop_id').getValue()) {
			getWnd("swEvnUslugaDispDop13EditWindowExt6").show({
				needLoad: true,
				params: {
					title: "Индивидуальное проф.консультирование",
					blocktype: 'IndiProf',
					EvnPLDispDop13_id: vm.get('EvnPLDispDop13_id'),
					EvnUslugaDispDop_id: DataForm.findField('EvnUslugaDispDop_id').getValue(),
					UslugaComplex_Date: vm.get('EvnPLDispDop13_consDate'),
					ViewValues: ViewForm.getValues(),
					DataValues: DataForm.getValues()
				},
				callback: function (data) {

				}
			});
		}
	}
});