/**
* Журнал учета клинико-экспертной работы МУ - форма добавления/редактирования ВК
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      01.08.2011
*/

sw.Promed.swClinExWorkEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	width: 800,
	id: 'swClinExWorkEditWindow',
	objectName: 'swClinExWorkEditWindow',
	height: 600,
	closable: true,
	maximizable: false,
	split: true,
	modal: true,
	maximized: true,
	onHide: Ext.emptyFn,
	action: null,
	EvnPrescrVK_id: null,
	plain: true,
	autoScroll: true,
	defaults: {
		bodyStyle: 'background: #DFE8F6;'
	},
	draggable: false,
	resizable: false,
	deletable: false, //нужно ли удалять протокол при закрытии формы? (true - да, false - нет)
	buttonAlign : "right",
	listeners: {
		beforehide: function() {
			if( !this.isChairmanSaved() && this.showtype == 'edit' ) {
				sw.swMsg.alert('Ошибка', 'Необходимо указать Председателя врачебной комиссии!');
				return false;
			}
		},
		hide: function(win) {			
			win.MseButton.setVisible(true);
			var fill = win.Form8.findById('form8_EvnVK_isAutoFill').getValue();
			win.CenterForm.getForm().reset();
			if(!fill){
				win.findById('EvnVKExpertsGrid').removeAll();
				win.Form8.collapse();
				win.Form8.findById('form8_EvnVK_isAutoFill').setValue(fill);
			} else {
				win.Form8.findById('form8_EvnVK_isAutoFill').setValue(fill);
			}
			if(!win.buttons[0].isVisible()) {
				win.buttons[0].setVisible(true);
			}
			if(win.formFields) {
				for (var i = 0; i<win.formFields.length; i++) {
					if(win.formFields[i].disabled == true) {
						win.formFields[i].enable();
					}
				}
			}
			if(win.deletable) {
				win.deleteEvnVK();
				win.deletable = false;
			}
			win.EvnVK_id = null;
			win.CenterForm.getForm().findField('EvnStickWorkRelease_id').getStore().removeAll();
		}
	},

	show: function()
	{
		sw.Promed.swClinExWorkEditWindow.superclass.show.apply(this, arguments);
		
		if ( !arguments[0] ) { 
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.formMode = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		
		var form = this;
		var base_form = this.CenterForm.getForm();
		this.arguments = arguments;
		// отделение
		//var cewew_lpusection_combo = this.findById('cewew_lpusection_combo');
		//var cewew_medstafffact_combo = this.findById('cewew_medstafffact_combo');
		var ExpertiseNameType_combo = this.findById('cewew_ExpertiseNameType');
		var ExpertiseEventTypeLink_combo = this.findById('cewew_ExpertiseEventTypeLink');
		
		this.Grid.ViewGridPanel.getStore().isLoaded = false;
		//setLpuSectionGlobalStoreFilter({
		//	isStacAndPolka: true
		//});
		//cewew_lpusection_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		//setMedStaffFactGlobalStoreFilter({
		//	isStacAndPolka: true
		//});
		//cewew_medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		if(arguments[0].EvnPrescrVK_id)
			base_form.findField('EvnPrescrVK_id').setValue(arguments[0].EvnPrescrVK_id);
		
		this.PalliatQuestion_id = null;
		this.PalliatFamilyCarePanel.reset();
		this.PalliatFamilyCarePanel.addCombo();
		this.findById('PalliatFamilyCareCheckbox').setValue(false);
		this.findById('PalliatFamilyCareCheckbox').fireEvent('change', this.findById('PalliatFamilyCareCheckbox'), false);
		this.findById('PalliatEvnVK_TextTIR').hide();
		this.findById('PalliatEvnVK_TextTIR').setAllowBlank(true);
		this.findById('PalliativeType_idSelect').hideContainer();
		this.findById('PalliativeType_id').hideContainer();
		
		form.formFields = [
			base_form.findField('EvnVK_NumProtocol'),
			base_form.findField('EvnVK_setDT'),
			base_form.findField('EvnVK_isReserve'),
			//base_form.findField('EvnVK_didDT'),
			//base_form.findField('EvnVK_isControl'),
			//cewew_lpusection_combo,
			//cewew_medstafffact_combo,
			base_form.findField('MedPersonal_id'),
			this.findById('EvnVK_NumCard'),
			this.findById('cewew_patientstatustype_combo'),
			//base_form.findField('Okved_id'),
			this.findById('cewew_CauseTreatmentType'),
			this.findById('cewew_Diag_id'),
			this.findById('cewew_Diag_sid'),
			base_form.findField('EvnVK_MainDisease'),
			ExpertiseNameType_combo,
			this.findById('cewew_ExpertiseEventTypeLink'),
			this.findById('cewew_ExpertiseSubjectType'),
			this.findById('cewew_LVN_trigger'),
			//this.findById('editLVN'),
			this.findById('EvnVK_ExpertiseStickNumber'),
			this.findById('EvnVK_StickPeriod'),
			this.findById('EvnVK_StickDuration'),
			//this.findById('EvnVK_DirectionDate'),
			//this.findById('EvnVK_ConclusionDate'),
			//this.findById('EvnVK_ConclusionPeriodDate'),
			//base_form.findField('EvnVK_ConclusionDescr'),
			//base_form.findField('EvnVK_AddInfo'),
			base_form.findField('EvnVK_isUseStandard'),
			base_form.findField('EvnVK_isAberration'),
			base_form.findField('EvnVK_isErrors'),
			base_form.findField('EvnVK_isResult'),
			base_form.findField('EvnVK_UseStandard'),
			base_form.findField('EvnVK_AberrationDescr'),
			base_form.findField('EvnVK_ErrorsDescr'),
			base_form.findField('EvnVK_ResultDescr'),
			base_form.findField('EvnVK_ExpertDescr'),
			base_form.findField('EvnVK_isAutoFill'),
			base_form.findField('EvnVK_Prof'),
			this.findById('EvnVKExpertsGrid')
		];
		this.showtype = arguments[0].showtype;
		this.buttons[2].setVisible( this.showtype !== 'add' );
		this.numChangedBy = {num:'',by:'',print:''};
		switch(this.showtype) {
			case 'add':
				// Пока что так проверяем есть ли права на создание протокола
				if(arguments[0].MedService_id) {
					base_form.findField('MedService_id').setValue(parseInt(arguments[0].MedService_id));
				} else {
					sw.swMsg.alert('Сообщение', 'Не указана служба!', function() { this.hide(); }.createDelegate(this) );
					return false;
				}
				form.action = 'ins';
				this.PersonData = arguments[0].PersonData;
				//this.EvnVK_NumProtocol = arguments[0].EvnVK_NumProtocol;
		
				this.SopDiagListPanel.reset();
				this.OslDiagListPanel.reset();
				
				EvnVK_NumProtocolField = this.findById('cewew_EvnVK_NumProtocol');
				//EvnVK_NumProtocolField.setValue(this.EvnVK_NumProtocol);
				//EvnVK_NumProtocolField.disable();
				
				//this.MseButton.setVisible(false);
				this.HtmButton.setVisible(false);

				// <!-- Эта херня только когда открываем протокол из АРМа ВК на добавление
				/*if(arguments[0].LpuSection_id)
					cewew_lpusection_combo.setValue(arguments[0].LpuSection_id);
				if(arguments[0].MedPersonal_id){
					cewew_medstafffact_combo.setValue(arguments[0].MedPersonal_id);
					cewew_medstafffact_combo.getStore().each(function(rec){
						if(rec.get('MedPersonal_id') == cewew_medstafffact_combo.getValue() && rec.get('LpuSection_id') == cewew_lpusection_combo.getValue())
							log(rec);//cewew_medstafffact_combo.setValue(rec.get('MedStaffFact_id'));
					});
				}*/
				if( arguments[0].MedPersonal_id ) {
					base_form.findField('MedPersonal_id').setValue(arguments[0].MedPersonal_id);
					base_form.findField('MedPersonal_id').disable();
				}

				if( arguments[0].EvnVK_isInternal ) {
					base_form.findField('EvnVK_isInternal').setValue(arguments[0].EvnVK_isInternal);
				}
				
				if ( arguments[0].EvnVK_NumCard && arguments[0].EvnPrescrVK_pid ) {
					base_form.findField('EvnVK_NumCard').setValue(arguments[0].EvnVK_NumCard);
					this.Evn_id = arguments[0].EvnPrescrVK_pid;
					/*var stick_combo = base_form.findField('EvnStickBase_id');
					stick_combo.getStore().baseParams = {
						Evn_id: arguments[0].EvnPrescrVK_pid
					};
					stick_combo.getStore().load({
						callback: function() {
							if( stick_combo.getStore().getCount() > 0 )
								stick_combo.fireEvent('select', stick_combo);
						}
					});*/
				}
				
				if(arguments[0].CauseTreatmentType_id)
					this.findById('cewew_CauseTreatmentType').setValue(arguments[0].CauseTreatmentType_id);
							
				if (arguments[0].CauseTreatmentType_id && arguments[0].CauseTreatmentType_id == 21) {
					this.Form65.show();
				} else {
					this.Form65.hide();
				}
				
				if ( arguments[0].EvnStickBase_id && arguments[0].EvnStick_all ) {
					base_form.findField('EvnStickBase_id').setValue(arguments[0].EvnStickBase_id);
					this.loadStickTrigger({EvnStick_id: arguments[0].EvnStickBase_id});
				} else if ( arguments[0].EvnVK_LVN ) {
					base_form.findField('EvnVK_LVN').setValue(arguments[0].EvnVK_LVN);
				}
				if (arguments[0].EvnVK_Note){
					base_form.findField('EvnVK_Note').setValue(arguments[0].EvnVK_Note);
				}
				// -->
				
				var diag_combo = this.findById('cewew_Diag_id');
				var EvnPrescrVK_id = base_form.findField('EvnPrescrVK_id').getValue();
				if (EvnPrescrVK_id) {
					Ext.Ajax.request({
						url: '/?c=Mse&m=getEvnPrescrMseData',
						params: { 
							EvnPrescrVK_id: EvnPrescrVK_id
						},
						callback: function( o, s, r ) {
							if( s ) {
								var obj = Ext.util.JSON.decode(r.responseText)[0];
								var mpcombo = base_form.findField('MedPersonal_id');
								mpcombo.setValue(obj.MedPersonal_sid);
								var index = mpcombo.getStore().findBy(function(rec) {
									return (rec.get('MedPersonal_sid') == obj.MedPersonal_sid);
								});
								mpcombo.fireEvent('select', mpcombo, mpcombo.getStore().getAt(index));
								base_form.findField('Diag_id').setValue(obj.Diag_id);
								diag_combo.getStore().load({
									callback: function() {
										diag_combo.getStore().each(function(rec) {
											if ( rec.get('Diag_id') == diag_combo.getValue() ) {
												diag_combo.fireEvent('select', diag_combo, rec, 0);
											}
										});
									},
									params: { where: "where Diag_id = " + diag_combo.getValue() }
								});
								base_form.findField('EvnVK_MainDisease').setValue(obj.EvnPrescrMse_MainDisease);
								// Проставляем диагнозы
								form.SopDiagListPanel.setValues(obj.SopDiagList);
								form.OslDiagListPanel.setValues(obj.OslDiagList);
								if (obj.PalliatQuestion_id) {
									form.PalliatQuestion_id = obj.PalliatQuestion_id;
								}
								
								if(obj.CauseTreatmentType_id == 21) {
									var ExpertiseNameTypeCombo = base_form.findField('ExpertiseNameType_id');
									ExpertiseNameTypeCombo.setValue(10);
									ExpertiseNameTypeCombo.fireEvent('change', ExpertiseNameTypeCombo, 10);
								}
							}
						}
					});
				} else if(arguments[0].Diag_id) {
					diag_combo.setValue(arguments[0].Diag_id);
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(rec) {
								if ( rec.get('Diag_id') == diag_combo.getValue() ) {
									diag_combo.fireEvent('select', diag_combo, rec, 0);
								}
							});
						},
						params: { where: "where Diag_id = " + diag_combo.getValue() }
					});
				}
				
				this.setTitle('Протокол заседания ВК: Добавление');
				
				this.PersonDataForm.setTitle('...');
				this.PersonDataForm.load({
					callback: function() {
						this.PersonDataForm.setPersonTitle();
						this.Form8.expand(false);
					}.createDelegate(this),
					Person_id: this.PersonData.Person_id,
					Server_id: this.PersonData.Server_id
				});
				
				ExpertiseDate = this.findById('cewew_ExpertiseDate');
				ExpertiseDate.setValue(Ext.util.Format.date(new Date(), 'd.m.Y'));
				//ExpertiseDate.disable();
				
				base_form.findField('EvnVK_isUseStandard').setValue(1);
				base_form.findField('EvnVK_UseStandard').setDisabled(true);
				base_form.findField('EvnVK_UseStandard').setAllowBlank(true);
				
				base_form.findField('EvnVK_isAberration').setValue(1);
				base_form.findField('EvnVK_isErrors').setValue(1);
				if(getRegionNick()!='kz')
				{
					base_form.findField('EvnVK_isAberration').setAllowBlank(true);
					base_form.findField('EvnVK_isAberration').hideContainer();
					base_form.findField('EvnVK_AberrationDescr').hideContainer();
				
					base_form.findField('EvnVK_AberrationDescr').setDisabled(true);
					base_form.findField('EvnVK_AberrationDescr').setAllowBlank(true);
					base_form.findField('EvnVK_ErrorsDescr').setDisabled(true);
					base_form.findField('EvnVK_ErrorsDescr').setAllowBlank(true);
				}

				if(arguments[0].EvnPrescrMse_id) base_form.findField('EvnPrescrMse_id').setValue(arguments[0].EvnPrescrMse_id);
				//base_form.findField('EvnVK_didDT').focus(true, 100);
				this.definitionEvnVKStickPeriod();

				base_form.items.each(function(f){ f.validate(); });
				
				this.setGridEditable();
			break;
			
			case 'edit':
			case 'view':
				this.action = 'upd';
				this.EvnVK_id = arguments[0].EvnVK_id;
				if(arguments[0].EvnPrescrMse_id) this.EvnPrescrMse_id = arguments[0].EvnPrescrMse_id;
				base_form.findField('EvnVK_NumProtocol').focus(true);
				this.getLoadMask().show();
				base_form.load({
					params: {
						EvnVK_id: form.EvnVK_id
					},
					failure: function() {
						form.getLoadMask().hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					success: function(f, r) {
						// В зависимости от showtype переопределяем this.action
						form.getLoadMask().hide();
						var resp_obj = Ext.util.JSON.decode(r.response.responseText)[0];
						form.PersonDataForm.setTitle('...');
						form.PersonDataForm.load({
							callback: function() {
								form.PersonDataForm.setPersonTitle();
								//this.Form8.expand(false);
							}.createDelegate(this),
							Person_id: resp_obj.Person_id,
							Server_id: resp_obj.Server_id
						});
						
						//var record = cewew_lpusection_combo.getStore().getById(resp_obj.LpuSection_id);
						//cewew_lpusection_combo.fireEvent('select', cewew_lpusection_combo, record, resp_obj.LpuSection_id);
						//cewew_medstafffact_combo.setValue(resp_obj.MedStaffFact_id);
						
						if (resp_obj.PalliatEvnVKMainSyndrome) {
							var PalliatEvnVKMainSyndrome = resp_obj.PalliatEvnVKMainSyndrome.split(',');
							var combo = this.findById('PalliatEvnVKMainSyndrome');
							Ext.each(PalliatEvnVKMainSyndrome, function(el) {
								combo.setCheckedValue(el);
							});
						}
						
						if (resp_obj.PalliatEvnVKTechnicInstrumRehab) {
							var PalliatEvnVKTechnicInstrumRehab = resp_obj.PalliatEvnVKTechnicInstrumRehab.split(',');
							var combo = this.findById('PalliatEvnVKTechnicInstrumRehab');
							Ext.each(PalliatEvnVKTechnicInstrumRehab, function(el) {
								combo.setCheckedValue(el);
							});
						}
						
						form.Form65.findBy(function(el) {
							if (el.xtype == 'radiogroup') {
								var v = resp_obj[el.name];
								if (v) {
									el.items.each(function(item){
										item.setValue(false);
										if(item.inputValue == v) {
											item.setValue(true);
										}
									});
								}
							}
						});
						
						if (resp_obj.PalliativeType_id) {
							var el = form.findById('PalliativeType_idSelect');
							var v = (resp_obj.PalliativeType_id > 2) ? 1 : 2;
							el.items.each(function(item){
								item.setValue(false);
								if(item.inputValue == v) {
									item.setValue(true);
								}
							});
						}
						
						if (resp_obj.PalliatFamilyCare.length && !!resp_obj.PalliatFamilyCare[0].FamilyRelationType_id) {
							this.findById('PalliatFamilyCareCheckbox').setValue(true);
							this.findById('PalliatFamilyCareCheckbox').fireEvent('change', this.findById('PalliatFamilyCareCheckbox'), true);
							this.PalliatFamilyCarePanel.reset();
							Ext.each(resp_obj.PalliatFamilyCare, function(el) {
								this.PalliatFamilyCarePanel.addCombo(el);
							}.createDelegate(this));
						}
						
						if (!Ext.isEmpty(resp_obj.PalliatEvnVK_TextTIR)) {
							this.findById('PalliatEvnVKTechnicInstrumRehab').setCheckedValue(10);
						}
									
						if (resp_obj.CauseTreatmentType_id == 21) {
							this.Form65.show();
						} else {
							this.Form65.hide();
						}
						
						var ExpertiseEventType_id = base_form.findField('ExpertiseEventType_id').getValue();
						var ExpertiseNameType_id = base_form.findField('ExpertiseNameType_id').getValue();

						ExpertiseNameType_combo.fireEvent('change', ExpertiseNameType_combo, ExpertiseNameType_id);

						var index = ExpertiseEventTypeLink_combo.getStore().findBy(function(rec) {
							return (rec.get('ExpertiseNameType_id') == ExpertiseNameType_id && rec.get('ExpertiseEventType_id') == ExpertiseEventType_id);
						});

						if ( index >= 0 ) {
							ExpertiseEventTypeLink_combo.setValue(ExpertiseEventTypeLink_combo.getStore().getAt(index).get('ExpertiseEventTypeLink_id'));
						}

						base_form.findField('ExpertiseEventType_id').setValue(ExpertiseEventType_id);
						form.HtmButton.setVisible(form.showtype == 'edit' && ExpertiseEventType_id == 61);
						// Прочитать все справочники (Diag)
						var diag_id = base_form.findField('Diag_id').getValue();
						if ( diag_id != null && diag_id.toString().length > 0 ) {
							base_form.findField('Diag_id').getStore().load({
								callback: function() {
									base_form.findField('Diag_id').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}
						var diag_sid = base_form.findField('Diag_sid').getValue();
						if ( diag_sid != null && diag_sid.toString().length > 0 ) {
							base_form.findField('Diag_sid').getStore().load({
								callback: function() {
									base_form.findField('Diag_sid').getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_sid ) {
											base_form.findField('Diag_sid').fireEvent('select', base_form.findField('Diag_sid'), record, 0);
										}
									});
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_sid }
							});
						}
						
						var EvnStickBase_id = resp_obj.EvnStickBase_id;
						if ( EvnStickBase_id != null && EvnStickBase_id.toString().length > 0 ) {
							/*base_form.findField('EvnStickBase_id').getStore().baseParams = { EvnStickBase_id: EvnStickBase_id };
							base_form.findField('EvnStickBase_id').getStore().load();*/
							this.loadStickTrigger({EvnStick_id: EvnStickBase_id});
						}
						
						var eswr_combo = base_form.findField('EvnStickWorkRelease_id');
						if ( !Ext.isEmpty(eswr_combo.getValue()) && !Ext.isEmpty(EvnStickBase_id) ) {
							eswr_combo.getStore().load({
								params: {
									EvnStick_id: EvnStickBase_id,
									EvnStickWorkRelease_id: eswr_combo.getValue()
								}
							});
						}
						
						base_form.findField('MedPersonal_id').setDisabled( base_form.findField('EvnPrescrVK_id').getValue() > 0 );

						this.numChangedBy.num = base_form.findField('EvnVK_NumProtocol').getValue();
						this.numChangedBy.print = base_form.findField('EvnVK_NumProtocol').getValue();
						this.numChangedBy.by = 'system';

						this.SopDiagListPanel.setValues(resp_obj.SopDiagList);
						this.OslDiagListPanel.setValues(resp_obj.OslDiagList);
						
						if (this.showtype == 'view') {
							this.SopDiagListPanel.disable();
							this.OslDiagListPanel.disable();
						}
						
						//base_form.findField('EvnVK_didDT').focus(true, 100);
						//base_form.findField('EvnVK_NumProtocol').disable();
						//base_form.findField('EvnVK_setDT').disable();
						if(getRegionNick()!='kz' && form.showtype == 'edit')
						{
							base_form.findField('EvnVK_UseStandard').setDisabled(resp_obj.EvnVK_isUseStandard == 1);
							//base_form.findField('EvnVK_UseStandard').setAllowBlank(resp_obj.EvnVK_isUseStandard == 1);
							base_form.findField('EvnVK_AberrationDescr').setDisabled(resp_obj.EvnVK_isAberration != 2);
							base_form.findField('EvnVK_AberrationDescr').setAllowBlank(resp_obj.EvnVK_isAberration != 2);
							base_form.findField('EvnVK_ErrorsDescr').setDisabled(resp_obj.EvnVK_isErrors != 2);
							base_form.findField('EvnVK_ErrorsDescr').setAllowBlank(resp_obj.EvnVK_isErrors != 2);
						}
						if(getRegionNick()!='kz')
						{
							if(resp_obj.EvnVK_isUseStandard == 2)
							{
								base_form.findField('EvnVK_isAberration').setAllowBlank(false);
								base_form.findField('EvnVK_isAberration').showContainer();
								base_form.findField('EvnVK_AberrationDescr').showContainer();
							}
							else
							{
								base_form.findField('EvnVK_isAberration').setAllowBlank(true);
								base_form.findField('EvnVK_isAberration').hideContainer();
								base_form.findField('EvnVK_AberrationDescr').hideContainer();
							}
						}
						if(!Ext.isEmpty(resp_obj.PatientStatusType_List))
						{
							var PatientStatusType_Str = resp_obj.PatientStatusType_List;
							form.findById('cewew_patientstatustype_combo').clearValue();
							form.findById('cewew_patientstatustype_combo').setValue(PatientStatusType_Str.replace(/\s/g,''));
						}
						
						if(!base_form.findField('EvnPrescrMse_id').getValue() && this.EvnPrescrMse_id){
							base_form.findField('EvnPrescrMse_id').setValue(this.EvnPrescrMse_id)
						}
						base_form.items.each(function(f){ f.validate(); });
						
						form.setGridEditable();
					}.createDelegate(this),
					url: '/?c=ClinExWork&m=getEvnVK'
				});
				
				if(this.showtype == 'edit') {
					this.setTitle('Протокол заседания ВК: Редактирование');
				} else if (this.showtype == 'view') {
					this.setTitle('Протокол заседания ВК: Просмотр');
					this.buttons[0].hide();
					
					for (var i = 0; i<this.formFields.length; i++) {
						this.formFields[i].disable();
					}
				}
			break;
		}
		if(!this.Numerator_id){
			this.getNum();
		}
	},
	setGridEditable: function() {
		
		if (getRegionNick() != 'vologda' || this.action == 'view') return false;
		
		var base_form = this.CenterForm.getForm();
		var isInternal = base_form.findField('EvnVK_isInternal').getValue() == 2;
		
		this.Grid.setReadOnly(!isInternal);
	},
	loadStickTrigger: function(data) {
		var frm = this.CenterForm.getForm(),
			stick_trigger = frm.findField('EvnStick_all'),
			stick_field = frm.findField('EvnStickBase_id');

		var params = data;

		Ext.Ajax.request({
			params: params,
			url: '/?c=Stick&m=searchEvnStick',
			callback: function(options, success, response) {
				if ( response.responseText.length > 0 )
				{
					var responseObj = Ext.util.JSON.decode(response.responseText);
					stick_trigger.onStickSelect(responseObj[0]);
				}
			}.createDelegate(this)
		});
	},
	doSave: function(options)
	{
		if ( this.formMode == 'save' ) {
			return false;
		}
		this.formMode = 'save';

		var cur_date = new Date(),
			base_form = this.CenterForm.getForm(),
			params = {};

		params.numChangedByHand = 0;
		if(this.numChangedBy.by == 'hand' || (this.action == 'add' && this.numChangedBy.by == '') || (this.action == 'upd' && this.numChangedBy.by == 'system')){
			params.numChangedByHand = 1;
		}
		params.action = this.action;
		if(base_form.findField('EvnVK_setDT').disabled) {
			params.EvnVK_setDT = Ext.util.Format.date(base_form.findField('EvnVK_setDT').getValue(), 'd.m.Y');
		}
		if(this.Form8.collapsed) {
			params.EvnVK_isAutoFill = (base_form.findField('EvnVK_isAutoFill').checked)? 'on' : 'off';
		}
		if(base_form.findField('EvnVK_NumProtocol').disabled) {
			params.EvnVK_NumProtocol = base_form.findField('EvnVK_NumProtocol').getValue();
		}
		if(base_form.findField('MedPersonal_id').disabled) {
			params.MedPersonal_id = base_form.findField('MedPersonal_id').getValue();
		}
		
		params.EvnVK_isAccepted = base_form.findField('EvnVK_isAccepted').getValue();
		params.PatientStatusType_List = this.findById('cewew_patientstatustype_combo').getValue();
		params.PersonEvn_id = this.PersonDataForm.getFieldValue('PersonEvn_id');
		params.Server_id = this.PersonDataForm.getFieldValue('Server_id');

		params.checkNumProtocol = 1;
		if (options.checkNumProtocol !== undefined) {
			params.checkNumProtocol = options.checkNumProtocol;
		}
		
		params.SopDiagList = Ext.util.JSON.encode(this.SopDiagListPanel.getValues());
		params.OslDiagList = Ext.util.JSON.encode(this.OslDiagListPanel.getValues());
		
		params.PalliatEvnVKMainSyndrome = this.findById('PalliatEvnVKMainSyndrome').getValue();
		params.PalliatEvnVKTechnicInstrumRehab = this.findById('PalliatEvnVKTechnicInstrumRehab').getValue();
		
		params.PalliatFamilyCare = Ext.util.JSON.encode(this.PalliatFamilyCarePanel.getValues());
		
		params.isPalliat = this.Form65.isVisible() ? 1 : 0;

		if(!base_form.isValid()) {
			sw.swMsg.alert('Ошибка', 'Не все обязательные поля заполнены!');
			this.CenterForm.getFirstInvalidEl().focus(false);
			this.formMode = 'edit';
			return false;
		}
		if(base_form.findField('EvnVK_DirectionDate').getValue() != '' && base_form.findField('EvnVK_DirectionDate').getValue() > cur_date) {
			sw.swMsg.alert('Ошибка', 'Дата направления должна быть не позднее текущей даты!');
			this.formMode = 'edit';
			return false;
		}
		if(base_form.findField('EvnVK_ConclusionDate').getValue() != '' && base_form.findField('EvnVK_ConclusionDate').getValue() > cur_date) {
			sw.swMsg.alert('Ошибка', 'Дата получения заключения должна быть не позднее текущей даты!');
			this.formMode = 'edit';
			return false;
		}
		if(base_form.findField('EvnVK_ConclusionPeriodDate').getValue() != '' && base_form.findField('EvnVK_ConclusionDate').getValue() != '' &&
			(base_form.findField('EvnVK_ConclusionDate').getValue() > base_form.findField('EvnVK_ConclusionPeriodDate').getValue())) {
			sw.swMsg.alert('Ошибка', 'Дата, до которой действует заключение, должна быть не ранее даты получения заключения МСЭ!');
			this.formMode = 'edit';
			return false;
		}

		var index = base_form.findField('ExpertiseNameType_id').getStore().findBy(function(rec) {
			return (rec.get('ExpertiseNameType_id') == base_form.findField('ExpertiseNameType_id').getValue());
		});

		// https://redmine.swan.perm.ru/issues/18328
		// Если в поле «Вид экспертизы» выбрано значение «Экспертиза временной нетрудоспособности», то...
		if ( index >= 0 && base_form.findField('ExpertiseNameType_id').getStore().getAt(index).get('ExpertiseNameType_Code') == 1 ) {
			// ... хотя бы одно из полей «ЛВН» или «ЛВН (ручной ввод)» должно быть заполнено
			if ( Ext.isEmpty(base_form.findField('EvnStickBase_id').getValue()) && Ext.isEmpty(base_form.findField('EvnVK_LVN').getValue()) ) {
				sw.swMsg.alert('Ошибка', 'Хотя бы одно из полей «ЛВН» или «ЛВН (ручной ввод)» должно быть заполнено!');
				this.formMode = 'edit';
				return false;
			}

			// ... хотя бы одно из полей «Период освобождения от работы» или «Период освобождения от работы (ручной ввод)» должно быть заполнено
			if ( Ext.isEmpty(base_form.findField('EvnStickWorkRelease_id').getValue()) && Ext.isEmpty(base_form.findField('EvnVK_WorkReleasePeriod').getValue()) ) {
				sw.swMsg.alert('Ошибка', 'Хотя бы одно из полей «Период освобождения от работы» или «Период освобождения от работы (ручной ввод)» должно быть заполнено!');
				this.formMode = 'edit';
				return false;
			}
		}

		var lm = this.getLoadMask('Сохранение протокола...');
		lm.show();
		params.Numerator_id = this.Numerator_id;
		
		if (!options.presave) {
			if( !this.isChairmanSaved() && !(this.showtype == 'view' && options.print) ) {
				lm.hide();
				sw.swMsg.alert('Ошибка', 'Необходимо указать Председателя врачебной комиссии!');
				this.formMode = 'edit';
				return false;
			}

			this.deletable = false;
			base_form.submit({
				params: params,
				failure: function(r, action) {
					lm.hide();
					this.formMode = 'edit';
					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						} else {
							sw.swMsg.alert('Ошибка', 'При сохранении протокола произошла ошибка!');
						}
					}
				}.createDelegate(this),
				success: function(r, action) {
					lm.hide();
					this.formMode = 'edit';
					if (!Ext.isEmpty(action.result.Alert_Msg)) {
						if(getRegionNick().inlist(['perm','ekb','astra','ufa'])){
							sw.swMsg.show({
								buttons: {
									cancel: true
								},
								fn: function ( buttonId ) {
									if ( buttonId == 'ok' ) {
										this.doSave(options);
										return;
									}
								}.createDelegate(this),
								msg: action.result.Alert_Msg,
								title: 'Предупреждение'
							});
						} else {
							sw.swMsg.show({
								buttons: {
									ok: {text: 'Продолжить'},
									cancel: true
								},
								fn: function ( buttonId ) {
									if ( buttonId == 'ok' ) {
										options.checkNumProtocol = 0;
										this.doSave(options);
										return;
									}
								}.createDelegate(this),
								msg: action.result.Alert_Msg + " Продолжить сохранение?",
								title: 'Предупреждение'
							});
						}
					} else {
						if( options.cb ) {
							base_form.findField('EvnVK_id').setValue(action.result.EvnVK_id);
							options.cb();
						} else {
							this.hide();
							this.onHide();
						}
					}
				}.createDelegate(this)
			});
		} else {
			/*
			if (!this.findById('cewew_lpusection_combo').isValid() || !this.findById('cewew_medstafffact_combo').isValid()) {
				sw.swMsg.alert('Ошибка', 'Не все обязательные поля заполнены!');
				this.Form8.collapse();
				return false;
			}
			*/
			//params.LpuSection_id = this.findById('cewew_lpusection_combo').getValue();
			//params.MedStaffFact_id = this.findById('cewew_medstafffact_combo').getValue();
			base_form.submit({
				params: params,
				failure: function(r, action) {
					lm.hide();
					this.formMode = 'edit';
					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert('Ошибка', action.result.Error_Msg);
						} else {
							sw.swMsg.alert('Ошибка', 'При сохранении протокола произошла ошибка!');
						}
					}
				}.createDelegate(this),
				clientValidation: false,
				success: function(f, action) {
					lm.hide();
					this.formMode = 'edit';
					if (!Ext.isEmpty(action.result.Alert_Msg)) {
						if(getRegionNick().inlist(['perm','ekb','astra','ufa'])){
							sw.swMsg.show({
								buttons: {
									cancel: true
								},
								fn: function ( buttonId ) {
									if ( buttonId == 'ok' ) {
										this.doSave(options);
										return;
									}
								}.createDelegate(this),
								msg: action.result.Alert_Msg,
								title: 'Предупреждение'
							});
						} else {
							sw.swMsg.show({
								buttons: {
									ok: {text: 'Продолжить'},
									cancel: true
								},
								fn: function ( buttonId ) {
									if ( buttonId == 'ok' ) {
										options.checkNumProtocol = 0;
										this.doSave(options);
										return;
									}
								}.createDelegate(this),
								msg: action.result.Alert_Msg + " Продолжить сохранение?",
								title: 'Предупреждение'
							});
						}
					} else {
						var obj = Ext.util.JSON.decode(action.response.responseText),
							grid = this.findById('EvnVKExpertsGrid').ViewGridPanel;
						
						this.EvnVK_id = obj.EvnVK_id;
						this.findById('EvnVKExpertsGrid').getGrid().getStore().baseParams.EvnVK_id = this.EvnVK_id;
						this.CenterForm.getForm().findField('EvnVK_id').setValue(this.EvnVK_id);
						this.action = 'upd';
						this.deletable = true;
						
						if(options.cb) {
							options.cb();
						} else {
							grid.getStore().baseParams = {
								EvnVK_id: this.EvnVK_id,
								newEvnVK: 1
							};
							grid.getStore().load({
								method: 'post',
								callback: function(action) {
									if ( action.length > 0 ) {
										grid.getView().focusRow(0);
										grid.getSelectionModel().selectFirstRow();
									}
								}
							});
						}
					}
				}.createDelegate(this)
			});
		}
	},
	
	definitionEvnVKStickPeriod: function()
	{
		var Person_id = this.PersonData.Person_id;
		Ext.Ajax.request({
			url: '/?c=ClinExWork&m=getEvnVKStickPeriod',
			params: {Person_id: Person_id},
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					EvnVK_StickPeriod = Ext.getCmp('swClinExWorkEditWindow').findById('EvnVK_StickPeriod');
					EvnVK_StickPeriod.setValue(parseInt(result[0].EvnVKStickPeriod));
				}
			}
		});
	},
	
	openEvnVKExpertWindow: function( action )
	{
		var win = this,
			rec = this.findById('EvnVKExpertsGrid').ViewGridPanel.getSelectionModel().getSelected();
			
		if(!rec && action == 'edit')
			return false;
		
		if( this.EvnVK_id ) {
			var params = ( action == 'edit' ) ? rec.data : {};
			params.EvnVK_id = this.EvnVK_id;
			params.MedService_id = this.CenterForm.getForm().findField('MedService_id').getValue();
			params.fromEvnVK = true;
			getWnd('swClinExWorkSelectExpertWindow').show({
				action: action,
				params: params,
				onHide: function() {
					win.findById('EvnVKExpertsGrid').ViewGridPanel.getStore().load();
				}
			});
		} else {
			this.doSave({
				presave: true,
				cb: function() { win.openEvnVKExpertWindow(action); }.createDelegate(this)
			});
		}
	},
	
	deleteEvnVKExpert: function()
	{
		var grid = this.findById('EvnVKExpertsGrid').ViewGridPanel,
			action_redresh = this.findById('EvnVKExpertsGrid').ViewActions.action_refresh,
			record = grid.getSelectionModel().getSelected();
		if(!record)
			return false;
		
		Ext.Msg.show({
			title: 'Внимание!',
			msg: 'Удалить врача из списка экспертов?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					var lm = new Ext.LoadMask(Ext.get('EvnVKExpertsGrid'), {msg: 'Удаление врача...'});
					lm.show();
					Ext.Ajax.request({
						params: {EvnVKExpert_id: record.get('EvnVKExpert_id')},
						url: '/?c=ClinExWork&m=deleteEvnVKExpert',
						callback: function(options, success, response) {	
							lm.hide();
							if(success) {
								action_redresh.execute();
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},
	
	deleteEvnVK: function()
	{
		var EvnVK_id = this.EvnVK_id;
		Ext.Ajax.request({
			url: '/?c=ClinExWork&m=deleteEvnVK',
			params: {EvnVK_id: EvnVK_id},
			method: 'post'
		});
	},
	
	goToEvnPrescrMse: function()
	{
		var win = this,
			b_f = this.CenterForm.getForm(),
			persPanel = this.PersonDataForm,
			Person_id = this.PersonDataForm.personId,
			Server_id = this.PersonDataForm.getFieldValue('Server_id'),
			PersonEvn_id = this.PersonDataForm.getFieldValue('PersonEvn_id'),
			DopParams = b_f.getValues(),
			EvnPrescrMse_id = b_f.findField('EvnPrescrMse_id').getValue();
		var cb = function() {
			getWnd('swSelectMedServiceWindow').show({
				ARMType: 'mse',
				isRecord: true, // на запись
				onSelect: function(msdata) {
					getWnd('swTTMSScheduleRecordWindow').show({
						Person: {
							Person_Surname: persPanel.getFieldValue('Person_Surname'),
							Person_Firname: persPanel.getFieldValue('Person_Firname'),
							Person_Secname: persPanel.getFieldValue('Person_Secname'),
							Person_Birthday: persPanel.getFieldValue('Person_Birthday'),
							Person_id: persPanel.getFieldValue('Person_id'),
							Server_id: persPanel.getFieldValue('Server_id'),
							PersonEvn_id: persPanel.getFieldValue('PersonEvn_id')
						},
						MedService_id: msdata.MedService_id,
						MedServiceType_id: msdata.MedServiceType_id,
						MedService_Nick: msdata.MedService_Nick,
						MedService_Name: msdata.MedService_Name,
						MedServiceType_SysNick: msdata.MedServiceType_SysNick,
						Lpu_did: msdata.Lpu_id,
						LpuUnitType_SysNick: msdata.LpuUnitType_SysNick,
						LpuSection_uid: msdata.LpuSection_id,
						LpuSection_Name: msdata.LpuSection_Name,
						LpuUnit_did: msdata.LpuUnit_id,
						LpuSectionProfile_id: msdata.LpuSectionProfile_id,
						EvnVK_id: win.EvnVK_id,
						ARMType: 'mse',
						fromEmk: false,
						callback: function(data){
							getWnd('swTTMSScheduleRecordWindow').hide();
							// По идее после сохранения направления на МСЭ надо перечитать форму, пока так сделаем:
							win.show({
								showtype: 'edit',
								EvnVK_id: win.EvnVK_id
							});
						},
						/*
						userMedStaffFact: form.userMedStaffFact,
						UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id') || null,
						UslugaComplex_id: rec.get('UslugaComplex_id') || null,
						Diag_id: this.params.Diag_id || null,
						EvnDirection_pid: this.params.EvnDirection_pid || null,
						EvnQueue_id: this.params.EvnQueue_id || null,
						QueueFailCause_id: this.params.QueueFailCause_id || null,
						EvnPrescr_id: this.params.EvnPrescr_id || null,
						PrescriptionType_Code: this.params.PrescriptionType_Code || null,
						*/
						userClearTimeMS: function() {
							this.getLoadMask().hide();
							sw.swMsg.alert('Сообщение', 'Нельзя удалить направление!');
						}
					});
				}
			});
		}
		
		if ( EvnPrescrMse_id != '' ) {
			// Тоесть сразу ясно что и протокол ВК (EvnVK_id) создан, открываем направление на редактирование
			getWnd('swDirectionOnMseEditForm').show({
				Person_id: Person_id,
				Server_id: Server_id,
				EvnVK_id: this.EvnVK_id
			});
		} else {
			// А здесь надо преверить создан ли протокол ВК
			
			// Если протокол уже создан, тогда сразу выбираем службу и открываем расписание
			if( this.EvnVK_id != null ) {
				cb();
			// Иначе предварительно сохраняем протокол дабы получить EvnVK_id
			} else {
				this.doSave({
					presave: true,
					cb: function() {cb();}.createDelegate(this)
				});
			}
		}
	},
	
	goToEvnDirectionHTM: function() {
		var win = this,
			b_f = this.CenterForm.getForm(),
			persPanel = this.PersonDataForm,
			Person_id = this.PersonDataForm.personId,
			Server_id = this.PersonDataForm.getFieldValue('Server_id'),
			PersonEvn_id = this.PersonDataForm.getFieldValue('PersonEvn_id'),
			DopParams = b_f.getValues(),
			EvnDirectionHTM_id = b_f.findField('EvnDirectionHTM_id').getValue();

		if ( Ext.isEmpty(DopParams.Diag_id) ) {
			sw.swMsg.alert('Ошибка', 'Укажите основной диагноз');
			return false;
		}

		var cb = function() {
			getWnd('swSelectMedServiceWindow').show({
				ARMType: 'htm',
				isRecord: true, // на запись
				onSelect: function(msdata) {
					getWnd('swTTMSScheduleRecordWindow').show({
						Person: {
							Person_Surname: persPanel.getFieldValue('Person_Surname'),
							Person_Firname: persPanel.getFieldValue('Person_Firname'),
							Person_Secname: persPanel.getFieldValue('Person_Secname'),
							Person_Birthday: persPanel.getFieldValue('Person_Birthday'),
							Person_id: persPanel.getFieldValue('Person_id'),
							Server_id: persPanel.getFieldValue('Server_id'),
							PersonEvn_id: persPanel.getFieldValue('PersonEvn_id')
						},
						Diag_id: DopParams.Diag_id,
						MedService_id: msdata.MedService_id,
						MedServiceType_id: msdata.MedServiceType_id,
						MedService_Nick: msdata.MedService_Nick,
						MedService_Name: msdata.MedService_Name,
						MedServiceType_SysNick: msdata.MedServiceType_SysNick,
						Lpu_did: msdata.Lpu_id,
						LpuUnitType_SysNick: msdata.LpuUnitType_SysNick,
						LpuSection_uid: msdata.LpuSection_id,
						LpuSection_Name: msdata.LpuSection_Name,
						LpuUnit_did: msdata.LpuUnit_id,
						LpuSectionProfile_id: msdata.LpuSectionProfile_id,
						EvnVK_id: win.EvnVK_id,
						EvnVK_setDT: b_f.findField('EvnVK_setDT').getValue(),
						EvnVK_NumProtocol: b_f.findField('EvnVK_NumProtocol').getValue(),
						ARMType: 'htm',
						fromEmk: false,
						callback: function(data){
							getWnd('swTTMSScheduleRecordWindow').hide();
							win.show({
								showtype: 'edit',
								EvnVK_id: win.EvnVK_id
							});
						}.createDelegate(win),
						/*
						 userMedStaffFact: form.userMedStaffFact,
						 UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id') || null,
						 UslugaComplex_id: rec.get('UslugaComplex_id') || null,
						 Diag_id: this.params.Diag_id || null,
						 EvnDirection_pid: this.params.EvnDirection_pid || null,
						 EvnQueue_id: this.params.EvnQueue_id || null,
						 QueueFailCause_id: this.params.QueueFailCause_id || null,
						 EvnPrescr_id: this.params.EvnPrescr_id || null,
						 PrescriptionType_Code: this.params.PrescriptionType_Code || null,
						 */
						userClearTimeMS: function() {
							this.getLoadMask().hide();
							sw.swMsg.alert('Сообщение', 'Нельзя удалить направление!');
						}
					});
				}
			});
		}

		if ( !Ext.isEmpty(EvnDirectionHTM_id) ) {
			getWnd('swDirectionOnHTMEditForm').show({
				Person_id: Person_id,
				Server_id: Server_id,
				EvnDirectionHTM_id: EvnDirectionHTM_id,
				action: 'edit'
			});
		} else {
			// А здесь надо преверить создан ли протокол ВК

			// Если протокол уже создан, тогда сразу выбираем службу и открываем расписание
			if( this.EvnVK_id != null ) {
				cb();
				// Иначе предварительно сохраняем протокол дабы получить EvnVK_id
			} else {
				this.doSave({
					presave: true,
					cb: function() {cb();}.createDelegate(this)
				});
			}
		}
	},

	printEvnVK: function()
	{
		var b_f = this.CenterForm.getForm();
		if(b_f.findField('EvnVK_id').getValue() == '') {
			//sw.swMsg.alert('Ошибка', 'Протокол ВК не сохранен, печать невозможна.');
			return false;
		}
		var EvnVK_id = b_f.findField('EvnVK_id').getValue();
		if(getRegionNick() == 'kz') {
			printBirt({
                'Report_FileName': 'VK_Protokol.rptdesign',
                'Report_Params': '&paramEvnVK_id=' + EvnVK_id,
                'Report_Format': 'pdf'
            });
		} else {
			printBirt({
				'Report_FileName': 'VK_Protocol.rptdesign',
				'Report_Params': '&paramEvnVK_id=' + EvnVK_id,
				'Report_Format': 'pdf'
			});
		}
	},
	
	/*	Проверка сохранен ли у протокола Председатель ВК
	*
	*/
	isChairmanSaved: function()
	{
		var store = this.findById('EvnVKExpertsGrid').ViewGridPanel.getStore(),
			charExists = false;
		
		// Такой вариант возможен когда форма открыта на редактир-е и store еще не загружено
		if( !store.isLoaded && this.showtype == 'edit' )
			return true;
		store.each(function(r) {
			if( r.get('ExpertMedStaffType_id') == 1 ) charExists = true; 
		});
		return charExists;
	},
	
	setMseButtonDisabled: function(data) {
		var frm = this.CenterForm.getForm();
		var isDisabled = !(frm.findField('EvnVK_setDT').getValue() < new Date(2019,8,16) && frm.findField('ExpertiseNameType_id').getValue() == 2);
		if (this.action == 'view') isDisabled = true;
		this.MseButton.setDisabled(isDisabled);
	},
	
	setEvnVKisAccepted: function() {
		if (getRegionNick() != 'vologda') return false;
		var frm = this.CenterForm.getForm();
		var grid = this.Grid.getGrid();
		var accept = 0;
		grid.getStore().each(function(rec) {
			var vote = rec.get('EvnVKExpert_isApproved') == 2 ? 1 : -1;
			if (rec.get('EvnVKExpert_IsChairman') == 2) {
				vote *= 1.5; // Голос председателя решает в случае равенства голосов
			}
			accept += vote;
		});
		if (accept != 0) {
			frm.findField('EvnVK_isAccepted').setValue(accept > 0 ? 2 : 1);
		}
	},

	getNum:function(clearNumerator){
		if(clearNumerator){
			this.Numerator_id = null;
		}
		var win = this;
		if(win.Numerator_id > 0){
			win.getNumByNumerator(win.Numerator_id);
		} else {
			var params = {
				NumeratorObject_SysName: 'EvnVK',
				Lpu_id: getGlobalOptions().lpu_id,
				onDate: Ext.util.Format.date(this.findById('cewew_ExpertiseDate').getValue(),'d.m.Y')
			};
			win.getLoadMask('Получение нумератора').show();
			Ext.Ajax.request({ //заполнение номера
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if(response_obj && response_obj.length > 0){
							win.Numerators = response_obj;
							win.Numerator_id = response_obj[0].Numerator_id;
							win.NumeratorField.triggers[0].setActive = 1;
							win.NumeratorField.triggers[0].setOpacity(1);
							win.NumeratorField.triggers[1].setActive = 1;
							win.NumeratorField.triggers[1].setOpacity(1);
						} else {
							win.NumeratorField.triggers[0].setActive = 0;
							win.NumeratorField.triggers[1].setActive = 0;
							win.NumeratorField.triggers[0].setOpacity(0.5);
							win.NumeratorField.triggers[1].setOpacity(0.5);
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], 'При получении нумератора возникли ошибки');
					}
				},
				params: params,
				url: '/?c=Numerator&m=getActiveNumeratorList'
			});
		}
	},
	getNumByNumerator: function (numerator_id) {
		if(numerator_id > 0){
			var win = this;
			var numparams = {
				Numerator_id: numerator_id,
				Lpu_id: getGlobalOptions().lpu_id,
				onDate: Ext.util.Format.date(this.findById('cewew_ExpertiseDate').getValue(),'Y-m-d')
			};
			
			win.getLoadMask('Получение номера протокола').show();
			Ext.Ajax.request({ //заполнение номера
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success && response.responseText != '') {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj && response_obj.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
							win.NumeratorField.setValue('');
						} else if (response_obj && response_obj.success == false) {
							sw.swMsg.alert(lang['oshibka'], 'При получении номера протокола из активного нумератора возникли ошибки');
							win.NumeratorField.setValue('');
						} else {
							win.NumeratorField.enable();
							var intnum = response_obj.intnum;
							var prefix = response_obj.prenum;
							prefix = prefix.replace(/\\\\/g, "\\");
							prefix = prefix.replace(/\\\//g, "\/");
							var postfix = response_obj.postnum;
							postfix = postfix.replace(/\\\\/g, "\\");
							postfix = postfix.replace(/\\\//g, "\/");
							win.NumeratorField.setValue(prefix+intnum+postfix);
							win.numChangedBy.num = prefix+intnum+postfix;
							win.numChangedBy.by = 'numerator';
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], 'При получении номера протокола из активного нумератора возникли ошибки');
					}
				},
				params: numparams,
				url: '/?c=ClinExWork&m=getEvnVKNum'
			});
		}
	},
	
	initComponent: function()
	{
		var cur_w = this;
		
		this.inT = function(){
			var ts = this.trigger.select('.x-form-trigger', true);
			this.wrap.setStyle('overflow', 'hidden');
			var triggerField = this;
			ts.each(function(t, all, index){
				t.hide = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = 'none';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				t.show = function(){
					var w = triggerField.wrap.getWidth();
					this.dom.style.display = '';
					triggerField.el.setWidth(w-triggerField.trigger.getWidth());
				};
				var triggerIndex = 'Trigger'+(index+1);
				if(this['hide'+triggerIndex]){
					t.dom.style.display = 'none';
				}
				t.on("click", this['on'+triggerIndex+'Click'], this, {preventDefault:true});
				t.addClassOnOver('x-form-trigger-over');
				t.addClassOnClick('x-form-trigger-click');
			}, this);
			this.triggers = ts.elements;
		};

		this.NumeratorField = new Ext.form.TwinTriggerField({
			allowBlank: false,
			autoCreate: {tag: "input", autocomplete: "off", maxLength: (getRegionNick() == 'kareliya' ? 5 : 10)},
			width: 200,
			readOnly : false,
			maskRe: (getRegionNick().inlist([ 'kareliya', 'ufa' ]) ? /[0-9]/ : null),
			maxLength: (getRegionNick() == 'kareliya' ? 5 : 10),
			forceSelection: true,
			id: 'cewew_EvnVK_NumProtocol',
			name: 'EvnVK_NumProtocol',
			tabIndex: TABINDEX_EVNVK + 1,
			fieldLabel: 'Протокол заседания ВК номер',
			typeAhead: false,
			trigger1Class: 'x-form-plus-trigger',
			trigger2Class: 'x-form-search-trigger',
			firsttime: true,
			onTrigger1Click: function() {
				if(this.NumeratorField.disabled)
					return false;
				if(this.NumeratorField.triggers[0].setActive == 0)
					return false;
				if(this.Numerator_id > 0){
					this.getNumByNumerator(this.Numerator_id);
				} else {
					sw.swMsg.alert(lang['oshibka'], 'Не задан активный нумератор');
				}
			}.createDelegate(this),
			onTrigger2Click: function() {
				if(this.NumeratorField.disabled)
					return false;
				if(this.NumeratorField.triggers[1].setActive == 0)
					return false;
				if(this.Numerators && this.Numerators.length > 0){
					var me = this;
					if (me.numeratorMenu) {
						me.numeratorMenu.destroy();
						me.numeratorMenu = null;
					}
					me.numeratorMenu = new Ext.menu.Menu();
					for(var i = 0;i<this.Numerators.length;i++){
						if(this.Numerators[i].Numerator_id == this.Numerator_id){
							me.numeratorMenu.add({
								iconCls : 'checked16',
								text: this.Numerators[i].Numerator_Name,
								value: this.Numerators[i].Numerator_id,
								handler: function() {
									Ext.getCmp('swClinExWorkEditWindow').Numerator_id = this.value;
									Ext.getCmp('swClinExWorkEditWindow').NumeratorField.triggers[0].setActive = 1;
									Ext.getCmp('swClinExWorkEditWindow').NumeratorField.triggers[0].setOpacity(1);
									Ext.getCmp('swClinExWorkEditWindow').NumeratorField.onTrigger2Click();
								}
							});
						} else {
							me.numeratorMenu.add({
								iconCls : '',
								text: this.Numerators[i].Numerator_Name,
								value: this.Numerators[i].Numerator_id,
								handler: function() {
									Ext.getCmp('swClinExWorkEditWindow').Numerator_id = this.value;
									Ext.getCmp('swClinExWorkEditWindow').NumeratorField.triggers[0].setActive = 1;
									Ext.getCmp('swClinExWorkEditWindow').NumeratorField.triggers[0].setOpacity(1);
									Ext.getCmp('swClinExWorkEditWindow').NumeratorField.onTrigger2Click();
								}
							});
						}
						
					}
					me.numeratorMenu.show(this.NumeratorField.trigger.getFxEl());
					
				} else {
					sw.swMsg.alert(lang['oshibka'], 'Нет активных нумераторов');
				}
			}.createDelegate(this),
			listeners: {
				'change':function(comp,newval){
					if(this.numChangedBy.num != newval){
						this.numChangedBy.num = newval;
						this.numChangedBy.by = 'hand';
					}
				}.createDelegate(this)
			}
		});
		
		this.ProtocolNunPanel = new sw.Promed.Panel({
			defaults: {	border: false },
			autoHeight: true,
			style: 'background: #fff;',
			items: [
				{
					layout: 'form',
					labelAlign: 'right',
					labelWidth: 200,
					style: 'margin-top:3px;',
					items: [
						{
							xtype: 'hidden',
							name: 'EvnVK_id'
						}, {
							xtype: 'hidden',
							name: 'EvnVK_isInternal'
						}, {
							xtype: 'hidden',
							name: 'EvnPrescrVK_id'
						}, {
							xtype: 'hidden',
							name: 'MedService_id'
						}, this.NumeratorField
						/*{
							allowBlank: false,
							autoCreate: {tag: "input", autocomplete: "off", maxLength: (getRegionNick() == 'kareliya' ? 5 : 10)},
							xtype: 'textfield',
							width: 100,
							tabIndex: TABINDEX_EVNVK + 1,
							name: 'EvnVK_NumProtocol',
							maskRe: (getRegionNick() == 'kareliya' ? /[0-9]/ : null),
							maxLength: (getRegionNick() == 'kareliya' ? 5 : 10),
							style: 'margin-left: 10px; margin-top: 3px;',
							id: 'cewew_EvnVK_NumProtocol',
							fieldLabel: 'Протокол заседания ВК номер'
						}*/
					]
				}
			]
		});
		
		
		this.PersonDataForm = new sw.Promed.PersonInfoPanel({
			floatable: false,
			collapsed: true,
			region: 'north',
			title: '<div>Загрузка...</div>',
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			titleCollapse: true,
			collapsible: true,
			listeners: {
				'expand': function(p) {
					p.load({
						onExpand: true,
						PersonEvn_id: p.personEvnId,
						Person_id: p.personId,
						Server_id: p.serverId
					});
				}.createDelegate(this),
				'render': function(panel) {
					if (panel.header)
					{
						panel.header.on({
							'click': {
								fn: this.toggleCollapse,
								scope: panel
							}
						});
					}
				}
			}
		});
		
		this.Form1 = new sw.Promed.Panel({
			autoHeight: true,
			title: 'Общие данные',
			collapsible: true,
			animCollapse: false,
			defaults:
			{
				border: false
			},
			labelAlign: 'right',
			items: [
				{
					layout: 'column',
					style: 'margin-top: 3px;',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							labelWidth: 160,
							items: [
								{
									xtype: 'swdatefield',
									name: 'EvnVK_setDT',
									tabIndex: TABINDEX_EVNVK + 2,
									id: 'cewew_ExpertiseDate',
									allowBlank: false,
									listeners: {
										'change': function (field, newValue, oldValue) {
											var base_form = cur_w.CenterForm.getForm();

											base_form.findField('Diag_id').setFilterByDate(newValue);
											base_form.findField('Diag_sid').setFilterByDate(newValue);
											cur_w.setMseButtonDisabled();
										}
									},
									fieldLabel: 'Дата экспертизы'
								}
							]
						},
						{
							layout: 'form',
							style: 'margin-left: 5px;',
							items: [
								{
									xtype: 'checkbox',
									hideLabel: true,
									tabIndex: TABINDEX_EVNVK + 3,
									name: 'EvnVK_isReserve',
									listeners:
									{
										check: function(checkbox, c)
										{
											var win = Ext.getCmp('swClinExWorkEditWindow');
											//var EvnVK_NumCard = win.findById('EvnVK_NumCard');
											var patientstatustype = win.findById('cewew_patientstatustype_combo');
											var CauseTreatmentType = win.findById('cewew_CauseTreatmentType');
											var Diag = win.findById('cewew_Diag_id');
											var ExpertiseNameType = win.findById('cewew_ExpertiseNameType');
											var ExpertiseEventTypeLink = win.findById('cewew_ExpertiseEventTypeLink');
											var ExpertiseSubjectType = win.findById('cewew_ExpertiseSubjectType');
											var EvnVK_StickPeriod = win.findById('EvnVK_StickPeriod');
											var EvnVK_isUseStandard = win.CenterForm.getForm().findField('EvnVK_isUseStandard');
											var EvnVK_isAberration = win.CenterForm.getForm().findField('EvnVK_isAberration');
											var EvnVK_isErrors = win.CenterForm.getForm().findField('EvnVK_isErrors');
											var EvnVK_isResult = win.CenterForm.getForm().findField('EvnVK_isResult');
											if(c)
											{
												//EvnVK_NumCard.allowBlank = true;
												patientstatustype.allowBlank = true;
												CauseTreatmentType.allowBlank = true;
												Diag.allowBlank = true;
												ExpertiseNameType.allowBlank = true;
												ExpertiseEventTypeLink.allowBlank = true;
												ExpertiseSubjectType.allowBlank = true;
												EvnVK_isUseStandard.allowBlank = true;
												EvnVK_isAberration.allowBlank = true;
												EvnVK_isErrors.allowBlank = true;
												EvnVK_isResult.allowBlank = true;
												//EvnVK_StickPeriod.allowBlank = true;
											}
											else
											{
												//EvnVK_NumCard.allowBlank = false;
												patientstatustype.allowBlank = false;
												CauseTreatmentType.allowBlank = false;
												Diag.allowBlank = false;
												ExpertiseNameType.allowBlank = false;
												ExpertiseEventTypeLink.allowBlank = false;
												ExpertiseSubjectType.allowBlank = false;
												EvnVK_isUseStandard.allowBlank = false;
												EvnVK_isAberration.allowBlank = false;
												EvnVK_isErrors.allowBlank = false;
												EvnVK_isResult.allowBlank = false;
												//EvnVK_StickPeriod.allowBlank = false;
											}
											//EvnVK_NumCard.validate();
											patientstatustype.validate();
											CauseTreatmentType.validate();
											Diag.validate();
											ExpertiseNameType.validate();
											ExpertiseEventTypeLink.validate();
											ExpertiseSubjectType.validate();
											EvnVK_isUseStandard.validate();
											EvnVK_isAberration.validate();
											EvnVK_isErrors.validate();
											EvnVK_isResult.validate();
										}
									},
									boxLabel: 'Зарезервировано'
								}
							]
						}
					]
				}/*,
				{
					layout: 'column',
					defaults:
					{
						border: false,
						width: 210
					},
					items: [
						{
							layout: 'form',
							items: [
								{
									xtype: 'swdatefield',
									allowBlank: true,
									tabIndex: TABINDEX_EVNVK + 4,
									name: 'EvnVK_didDT',
									id: 'cewew_ControlDate',
									fieldLabel: 'Дата контроля'
								}
							]
						},
						{
							layout: 'form',
							//style: 'margin-left: 5px;',
							items: [
								{
									xtype: 'checkbox',
									tabIndex: TABINDEX_EVNVK + 5,
									name: 'EvnVK_isControl',
									listeners:
									{
										check: function(checkbox, c)
										{
											controlDate = this.ownerCt.ownerCt.findById('cewew_ControlDate');
											if(c) { 
												controlDate.allowBlank = false;
											} else {
												controlDate.allowBlank = true;
											}
											controlDate.validate();
										}
									},
									hideLabel: true,
									boxLabel: '<font style="color: red;">Контроль!</font>'
								}
							]
						}						
					]
				},
				{
					layout: 'column',
					defaults: 
					{
						border: false
					},
					items: [
						{
							layout: 'form',
							items: [
								{
									xtype: 'swlpusectionglobalcombo',
									id: 'cewew_lpusection_combo',
									name: 'LpuSection_id',
									//editable: false,
									tabIndex: TABINDEX_EVNVK + 6,
									allowBlank: false,
									width: 290,
									listWidth: 400,
									linkedElements: [
										'cewew_medstafffact_combo'
									],
									listeners:
									{
										change: function(combo, newValue, oldValue)
										{
											var LpuSection_id = newValue;
											cewew_medstafffact_combo = this.ownerCt.ownerCt.findById('cewew_medstafffact_combo');
											cewew_medstafffact_combo.reset();
											if(LpuSection_id != '')
											{
												setMedStaffFactGlobalStoreFilter({
													LpuSection_id: LpuSection_id,
													isStacAndPolka: true
												});
											}
											else
											{
												setMedStaffFactGlobalStoreFilter({
													isStacAndPolka: true
												});
											}
											cewew_medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
										}
									},
									fieldLabel: 'Отделение'
								}
							]
						},
						{
							layout: 'form',
							labelWidth: 40,
							style: 'margin-left: 5px;',
							items: [
								{
									xtype: 'swmedstafffactglobalcombo',
									width: 290,
									listWidth: 600,
									parentElementId: 'cewew_lpusection_combo',
									tabIndex: TABINDEX_EVNVK + 7,
									//editable: false,
									allowBlank: false,
									id: 'cewew_medstafffact_combo',
									boxLabel: 'Врач'
								}
							]
						}
					]
				}*/
				,
				{
					layout: 'column',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'form',
							labelWidth: 160,
							items: [
								{
									xtype: 'swmedpersonalcombo',
									editable: true,
									allowBlank: true,
									width: 400,
									listeners: {
										render: function() {
											this.getStore().load({ params: {MedPersonalNotNeeded: "true" }});
										}
									},
									hiddenName: 'MedPersonal_id',
									fieldLabel: 'Врач, направивший на ВК'
								}
							]
						}
					]
				}
			]
		});
		
		this.Form2 = new sw.Promed.Panel({
			autoHeight: true,
			title: 'Пациент',
			collapsible: true,
			animCollapse: false,
			defaults:
			{
				border: false,
				bodyStyle: 'padding-top: 3px;'
			},
			labelAlign: 'right',
			labelWidth: 160,
			items: [
				{
					layout: 'form',
					items: [
						{
							xtype: 'trigger',
							editable: false,
							//allowBlank: false,
							readOnly: true,
							width: 400,
							tabIndex: TABINDEX_EVNVK + 8,
							enableKeyEvents: true,
							mode: 'local',
							triggerClass: 'x-form-search-trigger',
							fieldLabel: 'Номер КВС(ТАП)',
							name: 'EvnVK_NumCard',
							id: 'EvnVK_NumCard',
							valueField: 'EvnVK_NumCard',
							displayField: 'EvnVK_NumCard',
							onTriggerClick: function() {
								var evn_trigger = this.CenterForm.getForm().findField('EvnVK_NumCard'),
									stick_combo = this.CenterForm.getForm().findField('EvnStickBase_id'),
									workrelease_combo = this.CenterForm.getForm().findField('EvnStickWorkRelease_id');
								if (evn_trigger.disabled) return false;
								
								if( this.PersonDataForm.personId == null ) {
									sw.swMsg.alert('Сообщение', 'Не выбран пациент!');
									return false;
								}
								getWnd('swEvnPLEvnPSSearchWindow').show({
									Person_id: this.PersonDataForm.personId,
									onHide: function() {
										evn_trigger.focus(false);
									},
									onSelect: function(persData) {
										persData.EvnVK_NumCard = persData.Evn_NumCard;
										evn_trigger.setValue(persData[evn_trigger.valueField]);
										evn_trigger.hiddenValue = persData[evn_trigger.valueField];
										evn_trigger.setRawValue(persData.EvnVK_NumCard);
										getWnd('swEvnPLEvnPSSearchWindow').hide();
										evn_trigger.focus(true, 100);
										stick_combo.reset();
										this.Evn_id = persData.Evn_id;
										/*this.loadStickTrigger({Evn_id: persData.Evn_id});
										stick_combo.getStore().baseParams = {
											Evn_id: persData.Evn_id
										};
										stick_combo.getStore().load({
											callback: function() {
												workrelease_combo.reset();
												workrelease_combo.getStore().removeAll();
												if( stick_combo.getStore().getCount() > 0 )
													stick_combo.fireEvent('select', stick_combo);
											}
										});*/
									}.createDelegate(this)
								});
							}.createDelegate(this)
						}, /*{
							xtype: 'swpatientstatustypecombo',
							width: 400,
							allowBlank: false,
							tabIndex: TABINDEX_EVNVK + 9,
							anchor: '',
							maskRe: /[а-яА-Я]/,
							forceSelection: true,
							lastQuery : '',
							listeners: {
								'blur': function(combo) {
									if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
										combo.clearValue();
									}
								}
							},
							name: 'PatientStatusType_id',
							id: 'cewew_patientstatustype_combo',
							fieldLabel: 'Статус пациента'
						}, */
						
						new Ext.ux.Andrie.Select({
							clearBaseFilter: function() {
								this.baseFilterFn = null;
								this.baseFilterScope = null;
							},
							setBaseFilter: function(fn, scope) {
								this.baseFilterFn = fn;
								this.baseFilterScope = scope || this;
								this.store.filterBy(fn, scope);
							},
							allowBlank: (getRegionNick()=='kz'),
							multiSelect: (getRegionNick()!='kz'),
							mode: 'local',
							anchor: '50%',
							fieldLabel: 'Статус пациента',
							store: new Ext.db.AdapterStore({
								dbFile: 'Promed.db',
								tableName: 'PatientStatusType',
								key: 'PatientStatusType_id',
								autoLoad: false,
								fields: [
									{name: 'PatientStatusType_id',  type:'int'},
									{name: 'PatientStatusType_SysNick',  type:'string'},
									{name: 'PatientStatusType_Name',  type:'string'}
								],
								sortInfo: {
									field: 'PatientStatusType_id'
								}
							}),
							listeners: {
								'change': function(combo,value)
								{
									//Если есть "Работающий пациент трудоспособного возраста", то делаем обязательным поле "Профессия пациента"
									if(getRegionNick()!='kz')
									{
										var value_str = combo.getValue();
										var value_arr = value_str.split(',');
										if(value_arr.indexOf('1') >= 0)
											this.CenterForm.getForm().findField('EvnVK_Prof').setAllowBlank(false);
										else
											this.CenterForm.getForm().findField('EvnVK_Prof').setAllowBlank(true);
									}
									
								}.createDelegate(this)
							},
							displayField: 'PatientStatusType_Name',
							valueField: 'PatientStatusType_id',
							name: 'PatientStatusType_id',
							id: 'cewew_patientstatustype_combo',
							tpl: '<tpl for="."><div class="x-combo-list-item"><table height="20" style="border: 0;"><tr>'+
									'<td>{PatientStatusType_Name}</td>'+
									'</tr></table></div></tpl>',
					}),
					
						/*{
							xtype: 'swokvedcombo',
							width: 400,
							tabIndex: TABINDEX_EVNVK + 10,
							hiddenName: 'Okved_id',
							editable: true,
							fieldLabel: 'Профессия пациента'
						}*/
						{
							fieldLabel: 'Профессия пациента',
							width: 400,
							name: 'EvnVK_Prof',
							allowBlank: true,
							tabIndex: TABINDEX_EVNVK + 10,
							xtype: 'textfield'
						}
					]
				}
			]
		});

		this.SopDiagListPanel = new sw.Promed.DiagListPanelWithDescr({
			win: this,
			width: 1200,
			buttonAlign: 'left',
			buttonLeftMargin: 220,
			labelWidth: 220,
			fieldWidth: 400,
			style: 'background: transparent; margin: 0; padding: 0;',
			fieldLabel: 'Сопутствующие заболевания',
			onChange: function() {
				
			}
		});
		
		this.OslDiagListPanel = new sw.Promed.DiagListPanel({
			win: this,
			width: 800,
			buttonAlign: 'left',
			buttonLeftMargin: 220,
			labelWidth: 220,
			fieldWidth: 400,
			style: 'background: transparent; margin: 0; padding: 0;',
			fieldLabel: 'Осложнения основного заболевания',
			onChange: function() {
				
			}
		});
		
		this.Form3 = new sw.Promed.Panel({
			autoHeight: true,
			title: 'Причина обращения и диагнозы',
			collapsible: true,
			animCollapse: false,
			defaults: {
				border: false,
				bodyStyle: 'padding-top: 3px;'
			},
			labelAlign: 'right',
			labelWidth: 220,
			items: [
				{
					layout: 'form',
					items: [
						{
							xtype: 'swcausetreatmenttypecombo',
							allowBlank: false,
							width: 400,
							tabIndex: TABINDEX_EVNVK + 11,
							//editable: false,
							listeners: {
								select: function(c ,r, i) {
									var frm = this.CenterForm.getForm(),
										ent_field = frm.findField('ExpertiseNameType_id');
									
									if( r.get('CauseTreatmentType_id').inlist([1,2,3,4]) ) {
										ent_field.setValue(1);
									} else if( r.get('CauseTreatmentType_id') == 7 ) {
										ent_field.setValue(5);
									}
									
									if (r.get('CauseTreatmentType_id') == 21) {
										this.Form65.show();
									} else {
										this.Form65.hide();
									}
								}.createDelegate(this)
							},
							anchor: '',
							id: 'cewew_CauseTreatmentType',
							name: 'CauseTreatmentType_id',
							fieldLabel: 'Причина обращения'
						},
						{
							xtype: 'swdiagcombo',
							width: 400,
							allowBlank: false,
							//editable: false,
							tabIndex: TABINDEX_EVNVK + 12,
							anchor: '',
							id: 'cewew_Diag_id',
							name: 'Diag_id',
							fieldLabel: 'Код основного заболевания по МКБ'
						},
						{
							xtype: 'swdiagcombo',
							width: 400,
							//editable: false,
							tabIndex: TABINDEX_EVNVK + 13,
							anchor: '',
							hidden: true,
							hideLabel: true,
							id: 'cewew_Diag_sid',
							name: 'Diag_sid',
							hiddenName: 'Diag_sid',
							fieldLabel: 'Диагноз сопутствующий'
						},
						{
							xtype: 'textarea',
							maxLength: 1000,
							grow: true,
							maxLengthText: 'Значение поля не должно превышать 1000 символов',
							name: 'EvnVK_MainDisease',
							width: 400,
							allowBlank: false,
							fieldLabel: 'Основное заболевание'
						},
						this.SopDiagListPanel,
						this.OslDiagListPanel
					]
				}
			]
		});
		
		this.Form4 = new sw.Promed.Panel({
			autoHeight: true,
			animCollapse: false,
			title: 'Экспертиза',
			collapsible: true,
			defaults: {
				border: false,
				bodyStyle: 'padding-top: 3px;'
			},
			labelAlign: 'right',
			labelWidth: 160,
			items: [
				{
					layout: 'form',
					items: [
						{
							xtype: 'swexpertisenametypecombo',
							id: 'cewew_ExpertiseNameType',
							hiddenName: 'ExpertiseNameType_id',
							editable: false,
							allowBlank: false,
							tabIndex: TABINDEX_EVNVK + 14,
							fieldLabel: 'Вид экспертизы',
							width: 400,
							anchor: '',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('ExpertiseNameType_id') == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index));
								},
								'select': function(combo, record, index) {
									var base_form = this.CenterForm.getForm();

									expertiseeventtypelink_combo = base_form.findField('ExpertiseEventTypeLink_id');
									expertiseeventtypelink_combo.reset();
									expertiseeventtypelink_combo.validate();

									store = expertiseeventtypelink_combo.getStore();
									store.clearFilter();

									var hasDecisionVKTemplate = false;
									if ( typeof record == 'object' && !Ext.isEmpty(record.get('ExpertiseNameType_id')) ) {
										store.filterBy(function(rec) {
											return (rec.get('ExpertiseNameType_id') == record.get('ExpertiseNameType_id'));
										});
										hasDecisionVKTemplate = record.get('ExpertiseNameType_id').inlist([1,2,5]);
									}
									this.findById('EvnVK_selectDecisionVKTemplateButton').setDisabled(!hasDecisionVKTemplate);

									expertiseeventtypelink_combo.setAllowBlank(store.getCount() == 0);

									if ( store.getCount() == 1 ) {
										expertiseeventtypelink_combo.setValue(store.getAt(0).get('ExpertiseEventTypeLink_id'));
									}

									expertiseeventtypelink_combo.fireEvent('change', expertiseeventtypelink_combo, expertiseeventtypelink_combo.getValue());
									
									cur_w.setMseButtonDisabled();
								}.createDelegate(this)
							}
						}, {
							xtype: 'swexpertiseeventtypelinkcombo',
							width: 400,
							listWidth: 600,
							anchor: '',
							mode: 'local',
							tabIndex: TABINDEX_EVNVK + 15,
							editable: false,
							hiddenName: 'ExpertiseEventTypeLink_id',
							id: 'cewew_ExpertiseEventTypeLink',
							lastQuery: '',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('ExpertiseEventTypeLink_id') == newValue);
									});
									if (cur_w.showtype == 'edit' && combo.getFieldValue('ExpertiseEventType_id') == 61) {
										cur_w.HtmButton.show();
									} else {
										cur_w.HtmButton.hide();
									}
									combo.fireEvent('select', combo, combo.getStore().getAt(index));
								},
								'select': function(combo, record, index) {
									var base_form = this.CenterForm.getForm();

									if ( typeof record == 'object' && !Ext.isEmpty(record.get('ExpertiseEventType_id')) ) {
										base_form.findField('ExpertiseEventType_id').setValue(record.get('ExpertiseEventType_id'));
									}
									else {
										base_form.findField('ExpertiseEventType_id').setValue('');
									}
								}.createDelegate(this)
							},
							fieldLabel: 'Хар-ка случая экспертизы'
						}, {
							xtype: 'hidden',
							name: 'ExpertiseEventType_id',
							value: ''
						}, {
							xtype: 'swexpertisenamesubjecttypecombo',
							width: 400,
							tabIndex: TABINDEX_EVNVK + 16,
							allowBlank: false,
							anchor: '',
							id: 'cewew_ExpertiseSubjectType',
							name: 'ExpertiseNameSubjectType_id',
							editable: false,
							fieldLabel: 'Предмет экспертизы'
						}
					]
				}
			]
		});
		
		this.Form5 = new sw.Promed.Panel({
			autoHeight: true,
			animCollapse: false,
			title: 'Нетрудоспособность',
			collapsible: true,
			defaults: {
				border: false,
				bodyStyle: 'padding-top: 3px;'
			},
			labelAlign: 'right',
			labelWidth: 300,
			items: [
				{
					layout: 'form',
					items: [
						{
							layout: 'column',
							border: false,
							defaults:
							{
								border: false
							},
							items: [
								{
									layout: 'form',
									items: [
										{
											xtype: 'hidden',
											name: 'EvnStickBase_id'
										}, {
											xtype: 'trigger',
											editable: false,
											readOnly: true,
											width: 300,
											tabIndex: TABINDEX_EVNVK + 17,
											enableKeyEvents: true,
											mode: 'local',
											triggerClass: 'x-form-search-trigger',
											fieldLabel: 'ЛВН',
											name: 'EvnStick_all',
											id: 'cewew_LVN_trigger',
											valueField: 'EvnStick_all',
											displayField: 'EvnStick_all',
											onStickSelect: function(data) {
												var frm = this.CenterForm.getForm(),
													stick_trigger = frm.findField('EvnStick_all'),
													stick_field = frm.findField('EvnStickBase_id');

												stick_trigger.setValue(data.EvnStick_all);
												stick_field.setValue(data.EvnStick_id);

												if(stick_trigger.disabled) return false;

												Ext.Ajax.request({
													params: { EvnStick_id: stick_field.getValue() },
													url: '/?c=ClinExWork&m=getCountEvnStickToVK',
													callback: function(options, success, response) {
														var ExpertiseStickNumber = frm.findField('EvnVK_ExpertiseStickNumber'),
															EvnVK_isReserve = frm.findField('EvnVK_isReserve'),
															EvnVK_StickPeriod = frm.findField('EvnVK_StickPeriod');

														ExpertiseStickNumber.allowBlank = false;
														EvnVK_StickPeriod.allowBlank = false;
														ExpertiseStickNumber.setValue(response.responseText);
													}.createDelegate(this)
												});
												var eswr_combo =  frm.findField('EvnStickWorkRelease_id');
												eswr_combo.getStore().load({
													params: {
														EvnStick_id: stick_field.getValue()														
													}
												});
											}.createDelegate(this),
											onTriggerClick: function() {
												var evn_trigger = this.CenterForm.getForm().findField('EvnVK_NumCard'),
													stick_trigger = this.CenterForm.getForm().findField('EvnStick_all');

												if( this.PersonDataForm.personId == null ) {
													sw.swMsg.alert('Сообщение', 'Не выбран пациент!');
													return false;
												}
												getWnd('swEvnStickSearchWindow').show({
													Person_id: this.PersonDataForm.personId,
													Evn_id: this.Evn_id || null,
													onHide: function() {
														stick_trigger.focus(false);
													},
													onSelect: function(data) {
														getWnd('swEvnStickSearchWindow').hide();
														stick_trigger.onStickSelect(data);
														stick_trigger.focus(true, 100);
													}
												});
											}.createDelegate(this)
										}, {
											allowBlank: true,
											width: 300,
											fieldLabel: 'ЛВН (ручной ввод)',
											name: 'EvnVK_LVN',
											xtype: 'textfield'
										}, {
											fieldLabel: 'Примечание',
											name: 'EvnVK_Note',
											allowBlank: true,
											xtype: 'textfield',
											width: 300,
											maxLength: 100
										}, {
											fieldLabel: 'Период освобождения от работы',
											xtype: 'swbaselocalcombo',
											anchor: '100%',
											editable: false,
											tabIndex: TABINDEX_EVNVK + 18,
											listWidth: 600,
											triggerAction: 'all',
											mode: 'local',
											store: new Ext.data.Store({
												autoLoad: false,
												listeners: {
													load: function(s, r, i) {
														var combo = this.CenterForm.getForm().findField('EvnStickWorkRelease_id');
														if ( s.getCount()>0 ) {
															combo.setValue(r[0].get('EvnStickWorkRelease_id'));
															if ( s.getCount()>1 ) {
																combo.focus(true);
																combo.expand();
															}
														}
													}.createDelegate(this)
												},
												reader: new Ext.data.JsonReader({
													id: 'EvnStickWorkRelease_id'
												}, [
													{ mapping: 'EvnStickWorkRelease_id', name: 'EvnStickWorkRelease_id', type: 'int' },
													{ mapping: 'EvnStickWorkRelease_info', name: 'EvnStickWorkRelease_info', type: 'string' }
												]),
												url: '/?c=ClinExWork&m=getEvnStickWorkRelease'
											}),
											onTriggerClick: function() {
												if(this.getStore().getCount()>0) {
													if( this.isExpanded() ) {
														this.collapse();
													} else {
														this.focus(true);
														this.expand();
													}
												} else {
													sw.swMsg.alert('Сообщение', 'Не выбран ЛВН');
												}
											},
											hiddenName: 'EvnStickWorkRelease_id',
											valueField: 'EvnStickWorkRelease_id',
											displayField: 'EvnStickWorkRelease_info'
										}, {
											allowBlank: true,
											width: 300,
											fieldLabel: 'Период освобождения от работы (ручной ввод)',
											name: 'EvnVK_WorkReleasePeriod',
											xtype: 'textfield'
										}
									]
								}/*,
								{
									layout: 'form',
									style: 'margin-left: 5px;',
									items: [
										{
											xtype: 'button',
											id: 'editLVN',
											tabIndex: TABINDEX_EVNVK + 19,
											text: 'Изменить',
											handler: function()
											{
												var lt = this.ownerCt.ownerCt.findById('cewew_LVN_trigger');
												var win = Ext.getCmp('swClinExWorkEditWindow');
												if(lt.getValue() != '') {
													var parentClass = (win.EvnStickData.EvnStickParentType == 'ТАП')?'EvnPL':'EnvPS';
													var PersonData = win.PersonDataForm.DataView.getStore().data.items[0].data;
													params = {
														Person_id: win.PersonDataForm.personId,
														Person_Birthday: PersonData.Person_Birthday,
														Person_Surname: PersonData.Person_Surname,
														Person_Secname: PersonData.Person_Secname,
														Person_Firname: PersonData.Person_Firname,
														evnStickType: 1,
														action: 'edit',
														parentClass: parentClass,
														formParams:
														{
															EvnStick_id: lt.getValue(),
															Person_id: win.PersonDataForm.personId,
															Server_id: win.PersonDataForm.serverId
														}
													};
													getWnd('swEvnStickEditWindow').show(params);
												} else {
													sw.swMsg.alert('Ошибка', 'Не выбран больничный лист!');
												}
											}
										}
									]
								}*/
							]
						},
						{
							xtype: 'textfield',
							width: 50,
							maxLength: 4,
							tabIndex: TABINDEX_EVNVK + 20,
							maskRe: /[0-9]/,
							name: 'EvnVK_ExpertiseStickNumber',
							id: 'EvnVK_ExpertiseStickNumber',
							fieldLabel: 'Экспертиза временной нетрудоспособности №'
						},
						{
							xtype: 'textfield',
							maskRe: /[0-9]/,
							tabIndex: TABINDEX_EVNVK + 21,
							id: 'EvnVK_StickPeriod',
							name: 'EvnVK_StickPeriod',
							maxLength: 3,
							width: 50,
							fieldLabel: 'Срок нетрудоспособности, дней'
						},
						{
							xtype: 'textfield',
							maskRe: /[0-9]/,
							tabIndex: TABINDEX_EVNVK + 22,
							maxLength: 3,
							id: 'EvnVK_StickDuration',
							width: 50,
							fieldLabel: 'Длительность пребывания в ЛПУ, дней'
						}
					]
				}
			]
		});
		
		
		this.MseButton = new Ext.Button({
			text: 'Направление на МСЭ',
			handler: function(){
				this.goToEvnPrescrMse();
			}.createDelegate(this)
		});
		
		this.HtmButton = new Ext.Button({
			text: 'Направление на ВМП',
			handler: function(){
				this.goToEvnDirectionHTM();
			}.createDelegate(this)
		});

		this.Form6 = new sw.Promed.Panel({
			autoHeight: true,
			animCollapse: false,
			title: 'Медико-социальная экспертиза',
			collapsible: true,
			defaults:
			{
				border: false,
				bodyStyle: 'padding-top: 3px;'
			},
			labelAlign: 'right',
			items: [
				{
					layout: 'form',
					items: [
						{
							layout: 'form',
							border: false,
							labelWidth: 380,
							items: [
								{
									xtype: 'hidden',
									name: 'EvnPrescrMse_id'
								}, {
									xtype: 'hidden',
									name: 'EvnDirectionHTM_id'
								}, {
									xtype: 'swdatefield',
									tabIndex: TABINDEX_EVNVK + 23,
									disabled: true,
									name: 'EvnVK_DirectionDate',
									id: 'EvnVK_DirectionDate',
									fieldLabel: 'Дата направления в бюро МСЭ (или др. спец. учреждения)'
								}, {
									xtype: 'swdatefield',
									tabIndex: TABINDEX_EVNVK + 24,
									name: 'EvnVK_ConclusionDate',
									disabled: true,
									id: 'EvnVK_ConclusionDate',
									listeners:
									{
										change: function(field, nV, oV)
										{
											var win = Ext.getCmp('swClinExWorkEditWindow');
											var field = win.CenterForm.getForm().findField('EvnVK_ConclusionDescr');
											if(nV != '')
											{
												field.allowBlank = false;
											}
											else
											{
												field.allowBlank = true;
											}
											field.validate();
										}
									},
									fieldLabel: 'Дата получения заключения МСЭ (или др. спец. учреждений)'
								}, {
									xtype: 'swdatefield',
									tabIndex: TABINDEX_EVNVK + 25,
									disabled: true,
									id: 'EvnVK_ConclusionPeriodDate',
									name: 'EvnVK_ConclusionPeriodDate',
									fieldLabel: 'Срок действия заключения'
								}
							]
						},
						{
							layout: 'column',
							border: false,
							labelWidth: 120,
							items: [
								{
									layout: 'form',
									border: false,
									items: [
										{
											xtype: 'textfield',
											tabIndex: TABINDEX_EVNVK + 26,
											width: 355,
											disabled: true,
											name: 'EvnVK_ConclusionDescr',
											listeners:
											{
												change: function(field, nV, oV)
												{
													var win = this;
													var field = win.CenterForm.getForm().findField('EvnVK_ConclusionDate');
													if(nV != '')
													{
														field.allowBlank = false;
													}
													else
													{
														field.allowBlank = true;
													}
													field.validate();
												}.createDelegate(this)
											},
											fieldLabel: 'Заключение МСЭ'
										}
									]
								}/*,
								{
									layout: 'form',
									style: 'margin-left: 3px;',
									border: false,
									items: [
										{
											xtype: 'button',
											text: '+'
										}
									]
								}*/
							]
						},
						{
							layout: 'column',
							border: false,
							labelWidth: 120,
							items: [
								{
									layout: 'form',
									border: false,
									items: [
										{
											xtype: 'textfield',
											tabIndex: TABINDEX_EVNVK + 27,
											width: 355,
											disabled: true,
											name: 'EvnVK_AddInfo',
											fieldLabel: 'Доп. информация'
										}
									]
								
								}/*,
								{
									layout: 'form',
									style: 'margin-left: 3px;',
									border: false,
									items: [
										cur_w.MseButton
									]
								}*/
							]
						}
					]
				}
			]
		});

		this.PalliatFamilyCarePanel = new sw.Promed.Panel({
			border: false,
			id: 'PalliatFamilyCarePanel',
			style: 'background: transparent; margin: 10px 20px; padding: 10px;',
			autoHeight: true,
			lastItemsIndex: 0,
			items: [{
				title: 'Сведения о родственниках, осуществляющих уход за пациентом',
				xtype: 'fieldset',
				autoHeight: true,
				items: [{
					border: false,
					id: 'PalliatFamilyCareFieldSet',
					items: []
				}, {
					height: 25,
					width: 100,
					border: false,
					style: 'margin: 2px 10px 0 315px;',
					html: '<a href="#" onclick="Ext.getCmp(\'PalliatFamilyCarePanel\').addCombo();">Добавить</a>'
				}]
			}],
			setAllowBlank: function(ab) {
				Ext.each(this.findById('PalliatFamilyCareFieldSet').items.items, function(el) {
					el.items.items[0].items.items[0].setAllowBlank(ab),
					el.items.items[1].items.items[0].setAllowBlank(ab),
					el.items.items[2].items.items[0].setAllowBlank(ab)
				});
			},
			getValues: function() {
				var data = [];
				Ext.each(this.findById('PalliatFamilyCareFieldSet').items.items, function(el) {
					var a = {
						PalliatFamilyCare_id: el.oId,
						PalliatFamilyCare_Age: el.items.items[0].items.items[0].getValue(),
						FamilyRelationType_id:  el.items.items[1].items.items[0].getValue(),
						PalliatFamilyCare_Phone:  el.items.items[2].items.items[0].getValue(),
					};
					data.push(a);
				});
				return data;
			},
			reset: function() {
				this.lastItemsIndex = 0;
				this.findById('PalliatFamilyCareFieldSet').removeAll();
				this.findById('PalliatFamilyCareFieldSet').doLayout();
			},
			deleteCombo: function(index) {
				this.findById('PalliatFamilyCareFieldSet').remove(this.findById(this.id + 'PalliatFamilyCareEl' + index),true);
				if (!this.findById('PalliatFamilyCareFieldSet').items.items.length) this.addCombo();
				this.findById('PalliatFamilyCareFieldSet').doLayout();
			},
			addCombo: function(data) {
				if (!data) data = {};
				this.lastItemsIndex++;
				var element = {
					id: this.id + 'PalliatFamilyCareEl' + this.lastItemsIndex,
					oId: data.PalliatFamilyCare_id || null,
					layout: 'column',
					style: 'margin-top: 5px;',
					border: false,
					defaults:{
						border: false
					},
					items: [{
						layout: 'form',
						labelWidth: 310,
						width: 380,
						items: [{
							xtype: 'textfield',
							fieldLabel: 'Возраст',
							allowBlank: false,
							value: data.PalliatFamilyCare_Age || '',
							width: 60,
							id:	this.id + 'PalliatFamilyCare_Age' + this.lastItemsIndex,
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						width: 300,
						items: [{
							fieldLabel: 'Степень родства',
							value: data.FamilyRelationType_id || null,
							width: 160,
							allowBlank: false,
							id:	this.id + 'FamilyRelationType_id' + this.lastItemsIndex,
							comboSubject: 'FamilyRelationType',
							xtype: 'swcommonsprcombo'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						width: 300,
						items: [{
							xtype: 'textfield',
							width: 160,
							fieldLabel: 'Телефон',
							allowBlank: false,
							value: data.PalliatFamilyCare_Phone || '',
							id:	this.id + 'PalliatFamilyCare_Phone' + this.lastItemsIndex,
						}]
					}, {
						height: 25,
						width: 100,
						style: 'margin: 2px 10px 0;',
						html: '<a href="#" onclick="Ext.getCmp(\''+this.id+'\').deleteCombo(\''+this.lastItemsIndex+'\');">Удалить</a>'
					}]
				};
				this.findById('PalliatFamilyCareFieldSet').add(element);
				this.findById(this.id + 'FamilyRelationType_id' + this.lastItemsIndex).getStore().load();
				this.findById('PalliatFamilyCareFieldSet').doLayout();
			}
		});

		this.Form65 = new sw.Promed.Panel({
			autoHeight: true,
			animCollapse: false,
			title: 'Паллиативная медицинская помощь',
			collapsible: true,
			defaults:
			{
				border: false,
				bodyStyle: 'padding-top: 3px;'
			},
			labelAlign: 'right',
			items: [{
				layout: 'form',
				border: false,
				labelWidth: 350,
				items: [{
					xtype: 'button',
					style: 'margin: 10px 0 15px 355px;',
					text: 'Анкета',
					handler: function() {
						if (cur_w.PalliatQuestion_id) {
							sw.Promed.PersonOnkoProfile.openEditWindow('view', {
								Person_id: cur_w.PersonData.Person_id,
								ReportType: 'palliat',
								PersonOnkoProfile_id: cur_w.PalliatQuestion_id
							});
						}
					}
				}, {
					fieldLabel: 'В паллиативной помощи',
					xtype: 'radiogroup',
					width: 220,
					columns: 2,
					name: 'PalliatEvnVK_IsPMP',
					id: 'PalliatEvnVK_IsPMP',
					items: [{
						name: 'PalliatEvnVK_IsPMP',
						boxLabel: 'Нуждается',
						inputValue: 2
					}, {
						name: 'PalliatEvnVK_IsPMP',
						boxLabel: 'Не нуждается',
						inputValue: 1,
						checked: true
					}],
					listeners: {
						'change': function (radioGroup, radioBtn) {
							if (radioBtn.inputValue == 2) {
								cur_w.findById('PalliativeType_idSelect').showContainer();
								cur_w.findById('PalliativeType_id').showContainer();
							} else {
								cur_w.findById('PalliativeType_idSelect').hideContainer();
								cur_w.findById('PalliativeType_id').hideContainer();
							}
						}
					}
				}, {
					fieldLabel: 'Информирован пациент о заболевании',
					xtype: 'swyesnocombo',
					hiddenName: 'PalliatEvnVK_IsInfoDiag'
				}, {
					id: 'PalliatFamilyCareCheckbox',
					boxLabel: 'Наличие родственников, имеющих возможность осуществлять уход за пациентом',
					xtype: 'checkbox',
					labelSeparator: '',
					listeners: {
						'change': function (checkbox, checked) {
							cur_w.findById('PalliatFamilyCarePanel').setVisible(checked);
							cur_w.findById('PalliatFamilyCarePanel').setAllowBlank(!checked);
						}
					}
				},
				this.PalliatFamilyCarePanel,
				{
					fieldLabel: 'Ведущий синдром',
					xtype: 'checkboxgroup',
					width: 450,
					columns: 2,
					name: 'PalliatEvnVKMainSyndrome',
					id: 'PalliatEvnVKMainSyndrome',
					items: [
						{name: 'PalliatEvnVKMainSyndrome1', boxLabel: 'Хронический болевой синдром', value: 1},
						{name: 'PalliatEvnVKMainSyndrome2', boxLabel: 'Одышка', value: 2},
						{name: 'PalliatEvnVKMainSyndrome3', boxLabel: 'Отеки', value: 3},
						{name: 'PalliatEvnVKMainSyndrome4', boxLabel: 'Слабость', value: 4},
						{name: 'PalliatEvnVKMainSyndrome5', boxLabel: 'Прогрессирование заболевания', value: 5},
						{name: 'PalliatEvnVKMainSyndrome6', boxLabel: 'Тошнота', value: 6},
						{name: 'PalliatEvnVKMainSyndrome7', boxLabel: 'Рвота', value: 7},
						{name: 'PalliatEvnVKMainSyndrome8', boxLabel: 'Запор', value: 8},
						{name: 'PalliatEvnVKMainSyndrome9', boxLabel: 'Асцит', value: 9},
						{name: 'PalliatEvnVKMainSyndrome10', boxLabel: 'Другое', value: 10},
					],
					getValue: function() {
						var out = [];
						this.items.each(function(item){
							if(item.checked){
								out.push(item.value);
							}
						});
						return out.join(',');
					},
					setCheckedValue: function(v) {
						this.items.each(function(item){
							if(item.value == v) {
								item.setValue(true);
							}
						});
						return true;
					}
				}, {
					fieldLabel: 'Форма оказания паллиативной медицинской помощи',
					xtype: 'radiogroup',
					width: 400,
					columns: 2,
					name: 'PalliativeType_idSelect',
					id: 'PalliativeType_idSelect',
					items: [{
						name: 'PalliativeType_idSelect',
						boxLabel: 'В амбулаторных условиях',
						inputValue: 2,
						checked: true
					}, {
						name: 'PalliativeType_idSelect',
						boxLabel: 'В стационарных условиях',
						inputValue: 1, 
					}],
					listeners: {
						'change': function (radioGroup, radioBtn) {
							Ext.each(cur_w.findById('PalliativeType_id').items.items, function(el) {
								if (radioBtn.inputValue == 2 && el.inputValue <= 2) {
									el.show();
								} else if (radioBtn.inputValue == 1 && el.inputValue > 2) {
									el.show();
								} else {
									el.hide();
								}
							});
						}
					}
				}, {
					xtype: 'radiogroup',
					labelSeparator: '',
					width: 600,
					columns: 1,
					name: 'PalliativeType_id',
					id: 'PalliativeType_id',
					items: [
						{name: 'PalliativeType_id',boxLabel: 'в кабинете паллиативной медицинской помощи',inputValue: 1, checked: true}, 
						{name: 'PalliativeType_id',boxLabel: 'в отделении выездной патронажной службы',inputValue: 2}, 
						{name: 'PalliativeType_id',boxLabel: 'в отделениях паллиативной медицинской помощи ',inputValue: 3}, 
						{name: 'PalliativeType_id',boxLabel: 'в Краевом центре ',inputValue: 6}, 
						{name: 'PalliativeType_id',boxLabel: 'в отделениях паллиативной медицинской помощи медицинских организаций, оказывающих специализированную, в том числе высокотехнологичную, медицинскую помощь', height: 40, inputValue: 4}, 
						{name: 'PalliativeType_id',boxLabel: 'в отделениях сестринского ухода',inputValue: 5}, 
					]
				}, {
					fieldLabel: 'Необходимость в респираторной поддержке',
					xtype: 'radiogroup',
					width: 200,
					columns: 2,
					name: 'PalliatEvnVK_IsIVL',
					id: 'PalliatEvnVK_IsIVL',
					items: [{
						name: 'PalliatEvnVK_IsIVL',
						boxLabel: 'Да',
						inputValue: 2
					}, {
						name: 'PalliatEvnVK_IsIVL',
						boxLabel: 'Нет',
						inputValue: 1, 
						checked: true
					}]
				}, {
					fieldLabel: 'Показания к получению специализированной, в том числе высокотехнологичной медицинской помощи',
					xtype: 'radiogroup',
					width: 200,
					columns: 2,
					name: 'PalliatEvnVK_IsSpecMedHepl',
					id: 'PalliatEvnVK_IsSpecMedHepl',
					items: [{
						name: 'PalliatEvnVK_IsSpecMedHepl',
						boxLabel: 'Да',
						inputValue: 2
					}, {
						name: 'PalliatEvnVK_IsSpecMedHepl',
						boxLabel: 'Нет',
						inputValue: 1, 
						checked: true
					}]
				}, {
					fieldLabel: 'Объем и виды рекомендуемой специализированной, в том числе высокотехнологичной медицинской помощи',
					width: 370,
					xtype: 'textarea',
					name: 'PalliatEvnVK_VolumeMedHepl',
					id: 'PalliatEvnVK_VolumeMedHepl'
				}, {
					fieldLabel: 'Условия получения специализированной, в том числе высокотехнологичной медицинской помощи',
					xtype: 'radiogroup',
					width: 200,
					columns: 2,
					name: 'ConditMedCareType_id',
					id: 'ConditMedCareType_id',
					items: [{
						name: 'ConditMedCareType_id',
						boxLabel: 'Амбулаторно',
						inputValue: 2
					}, {
						name: 'ConditMedCareType_id',
						boxLabel: 'Стационарно',
						inputValue: 1, 
						checked: true
					}]
				}, {
					fieldLabel: 'Показания к  обследованию и/или получению лечения (не паллиативной медицинской помощи) в медицинских организациях, оказывающих ПМСП',
					xtype: 'radiogroup',
					width: 200,
					columns: 2,
					name: 'PalliatEvnVK_IsSurvey',
					id: 'PalliatEvnVK_IsSurvey',
					items: [{
						name: 'PalliatEvnVK_IsSurvey',
						boxLabel: 'Есть',
						inputValue: 2
					}, {
						name: 'PalliatEvnVK_IsSurvey',
						boxLabel: 'Нет',
						inputValue: 1, 
						checked: true
					}]
				}, {
					fieldLabel: 'Объем и виды рекомендуемых обследований и лечения',
					width: 370,
					xtype: 'textarea',
					name: 'PalliatEvnVK_VolumeSurvey',
					id: 'PalliatEvnVK_VolumeSurvey'
				}, {
					fieldLabel: 'Целесообразность направления в учреждения социальной защиты населения',
					width: 370,
					xtype: 'textarea',
					name: 'PalliatEvnVK_DirSocialProt',
					id: 'PalliatEvnVK_DirSocialProt'
				}, {
					fieldLabel: 'Необходимость обеспечения ТСР',
					xtype: 'checkboxgroup',
					width: 200,
					columns: 1,
					name: 'PalliatEvnVKTechnicInstrumRehab',
					id: 'PalliatEvnVKTechnicInstrumRehab',
					items: [
						{name: 'PalliatEvnVKTechnicInstrumRehab1', boxLabel: 'Кресло-каталка', value: 1},
						{name: 'PalliatEvnVKTechnicInstrumRehab2', boxLabel: 'Стульчак', value: 2},
						{name: 'PalliatEvnVKTechnicInstrumRehab3', boxLabel: 'Аспиратор', value: 3},
						{name: 'PalliatEvnVKTechnicInstrumRehab4', boxLabel: 'Мешок Амбу', value: 4},
						{name: 'PalliatEvnVKTechnicInstrumRehab5', boxLabel: 'Функциональная кровать', value: 5},
						{name: 'PalliatEvnVKTechnicInstrumRehab6', boxLabel: 'Матрац пртивопролежневый', value: 6},
						{name: 'PalliatEvnVKTechnicInstrumRehab7', boxLabel: 'Вертикализатор', value: 7},
						{name: 'PalliatEvnVKTechnicInstrumRehab8', boxLabel: 'Откашливатель', value: 8},
						{name: 'PalliatEvnVKTechnicInstrumRehab9', boxLabel: 'Кислородный концентратор', value: 9},
						{name: 'PalliatEvnVKTechnicInstrumRehab10', boxLabel: 'Иное', value: 10, listeners:{
							change: function(obj, el){
								if (!el) this.findById('PalliatEvnVK_TextTIR').setValue('');
								this.findById('PalliatEvnVK_TextTIR').setVisible(el);
								this.findById('PalliatEvnVK_TextTIR').setAllowBlank(!el);
							}.createDelegate(this)
						}},
					],
					getValue: function() {
						var out = [];
						this.items.each(function(item){
							if(item.checked){
								out.push(item.value);
							}
						});
						return out.join(',');
					},
					setCheckedValue: function(v) {
						this.items.each(function(item){
							if(item.value == v) {
								item.setValue(true);
								item.fireEvent('change', item, true);
							}
						});
						return true;
					}
				}, {
					hideLabel: true,
					style: 'margin-left: 372px;',
					xtype: 'textfield',
					width: 200,
					name: 'PalliatEvnVK_TextTIR',
					id: 'PalliatEvnVK_TextTIR'
				}]
			}]
		});
		
		
		this.Form7 = new sw.Promed.Panel({
			autoHeight: true,
			animCollapse: false,
			title: 'Стандарты, дефекты, результаты, заключения',
			collapsible: true,
			defaults:
			{
				border: false,
				bodyStyle: 'padding-top: 3px;'
			},
			labelAlign: 'right',
			items: [
				{
					layout: 'form',
					items: [
						{
							xtype: 'label',
							style: 'margin-left: 295px;',
							text: 'Подробности:'
						},
						{
							layout: 'column',
							style: 'margin-top: 5px;',
							border: false,
							defaults:
							{
								border: false
							},
							items: [
								{
									layout: 'form',
									labelWidth: 220,
									defaults:
									{
										width: 60
									},
									items: [
										{
											xtype: 'swyesnocombo',
											allowBlank: false,
											enableKeyEvents: false,
											tabIndex: TABINDEX_EVNVK + 28,
											hiddenName: 'EvnVK_isUseStandard',
											name: 'EvnVK_isUseStandard',
											fieldLabel: 'Использовались стандарты',
											hidden: (getRegionNick() == 'kz'),
											hideLabel: (getRegionNick() == 'kz'),
											listeners:{
												'change': function(field,value,old_value)
												{
													if(getRegionNick() != 'kz')
													{
														var base_form = this.CenterForm.getForm();
														base_form.findField('EvnVK_UseStandard').setDisabled(value!=2);
														//base_form.findField('EvnVK_UseStandard').setAllowBlank(value!=2);

														if(value == 2)
														{
															base_form.findField('EvnVK_isAberration').showContainer();
															base_form.findField('EvnVK_isAberration').setAllowBlank(false);
															base_form.findField('EvnVK_AberrationDescr').showContainer();
															//EvnVK_AberrationDescr
														}
														else
														{
															base_form.findField('EvnVK_UseStandard').setValue('');
															base_form.findField('EvnVK_isAberration').hideContainer();
															base_form.findField('EvnVK_AberrationDescr').hideContainer();
															base_form.findField('EvnVK_isAberration').setValue(1);
															base_form.findField('EvnVK_isAberration').setAllowBlank(true);
															base_form.findField('EvnVK_AberrationDescr').setValue('');
														}
														//if(value == 1)
														//	base_form.findField('EvnVK_UseStandard').setValue('');
													}
												}.createDelegate(this)
											}
										},
										{
											xtype: 'swyesnocombo',
											allowBlank: false,
											enableKeyEvents: false,
											tabIndex: TABINDEX_EVNVK + 28,
											hiddenName: 'EvnVK_isAberration',
											name: 'EvnVK_isAberration',
											fieldLabel: 'Отклонение от стандартов',
											listeners:{
												'change': function(field,value,old_value)
												{
													if(getRegionNick() != 'kz')
													{
														var base_form = this.CenterForm.getForm();
														base_form.findField('EvnVK_AberrationDescr').setDisabled(value!=2);
														base_form.findField('EvnVK_AberrationDescr').setAllowBlank(value!=2);
														if(value == 1)
															base_form.findField('EvnVK_AberrationDescr').setValue('');
													}
												}.createDelegate(this)
											}
										},
										{
											xtype: 'swyesnocombo',
											allowBlank: false,
											enableKeyEvents: false,
											tabIndex: TABINDEX_EVNVK + 29,
											hiddenName: 'EvnVK_isErrors',
											name: 'EvnVK_isErrors',
											fieldLabel: 'Дефекты, нарушения и ошибки',
											listeners:{
												'change': function(field,value,old_value)
												{
													if(getRegionNick() != 'kz')
													{
														var base_form = this.CenterForm.getForm();
														base_form.findField('EvnVK_ErrorsDescr').setDisabled(value!=2);
														base_form.findField('EvnVK_ErrorsDescr').setAllowBlank(value!=2);
														if(value == 1)
															base_form.findField('EvnVK_ErrorsDescr').setValue('');
													}
												}.createDelegate(this)
											}
										},
										{
											xtype: 'swyesnocombo',
											allowBlank: false,
											enableKeyEvents: false,
											tabIndex: TABINDEX_EVNVK + 30,
											hiddenName: 'EvnVK_isResult',
											name: 'EvnVK_isResult',
											allowBlank: false,
											fieldLabel: 'Достижение результата или исхода'
										}
									]
								},
								{
									layout: 'form',
									style: 'margin-left: 10px;',
									defaults:
									{
										hideLabel: true,
										width: 300,
										style: 'padding: 1px 3px;'
									},
									items: [
										{
											xtype: 'textfield',
											tabIndex: TABINDEX_EVNVK + 31,
											name: 'EvnVK_UseStandard',
											disabled: (getRegionNick() != 'kz'),
											hidden: (getRegionNick() == 'kz')
										},
										{
											xtype: 'textfield',
											tabIndex: TABINDEX_EVNVK + 31,
											name: 'EvnVK_AberrationDescr'
										},
										{
											xtype: 'textfield',
											tabIndex: TABINDEX_EVNVK + 32,
											name: 'EvnVK_ErrorsDescr'
										},
										{
											xtype: 'textfield',
											tabIndex: TABINDEX_EVNVK + 33,
											name: 'EvnVK_ResultDescr',
											allowBlank: (getRegionNick()=='kz')
										}
									]
								}/*,
								{
									layuot: 'form',
									style: 'margin-left: 3px;',
									items: [
										{
											xtype: 'button',
											text: '+'
										},
										{
											xtype: 'button',
											style: 'margin-top: 3px;',
											text: '+'
										},
										{
											xtype: 'button',
											style: 'margin-top: 3px;',
											text: '+'
										}
									]
								}*/
							]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 220,
							items: [
								{
									xtype: 'textfield',
									width: 370,
									maxLength: 256,
									name: 'EvnVK_ExpertDescr',
									fieldLabel: 'Заключ. экспертов, рекомендации'
								},
								{
									xtype: 'swyesnocombo',
									width: 370,
									name: 'EvnVK_isAccepted',
									disabled: true,
									hidden: getRegionNick() != 'vologda',
									hideLabel: getRegionNick() != 'vologda',
									fieldLabel: 'Решение комиссии'
								}
							]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 220,
							items: [
								{
									style: 'margin-left: 225px',
									xtype: 'button',
									id: 'EvnVK_selectDecisionVKTemplateButton',
									text: 'Выбор шаблона решения ВК',
									handler: function() {
										var base_form = this.CenterForm.getForm();

										getWnd('swDecisionVKTemplateSelectWindow').show({
											ExpertiseNameType_id: base_form.findField('ExpertiseNameType_id').getValue(),
											onSelect: function(template_data) {
												base_form.findField('EvnVK_DecisionVK').setValue(template_data.DecisionVKTemplate_Name);
											}
										});
									}.createDelegate(this)
								}
							]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 220,
							items: [
								{
									xtype: 'textarea',
									width: 370,
									name: 'EvnVK_DecisionVK',
									fieldLabel: 'Описание решения ВК',
									allowBlank: getRegionNick() != 'vologda',
									autoCreate: {tag: "textarea", autocomplete: "off", rows: 5, cols: 60}
								}
							]
						}
					]
				}
			
			]
		});
		
		this.Grid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			id: 'EvnVKExpertsGrid',
			pageSize: 20,
			autoScroll: true,
			border: false,
			autoLoadData: false,
			root: 'data',
			actions: [
				{ name: 'action_add', tooltip: 'Добавить врача в список экспертов', hidden: false, handler: this.openEvnVKExpertWindow.createDelegate(this, ['add']) },
				{ name: 'action_edit', handler: this.openEvnVKExpertWindow.createDelegate(this, ['edit']) },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', tooltip: 'Удалить врача из списка экспертов', hidden: false, handler: function(){this.deleteEvnVKExpert();}.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'EvnVKExpert_id', type: 'int', hidden: true, key: true },
				{ name: 'MedServiceMedPersonal_id', type: 'int', hidden: true },
				{ name: 'MedStaffFact_id', type: 'int', hidden: true },
				{ name: 'EvnVKExpert_isApproved', type: 'int', hidden: true },
				{ name: 'ExpertMedStaffType_id', type: 'int', hidden: true },
				{ name: 'MF_Person_FIO', type: 'string',  header: 'ФИО эксперта', width: 300 },
				{ name: 'MedStaffFact_Info', type: 'string',  header: 'Место работы', width: 300 },
				{ name: 'EvnVKExpert_IsChairman', type: 'checkbox', header: 'Председатель ВК', width: 120 },
				{ name: 'EvnVKExpert_isApprovedName', type: 'string',  header: 'Решение эксперта', width: 120, hidden: getRegionNick() != 'vologda' },
				{ name: 'EvnVKExpert_Descr', type: 'string',  header: 'Комментарий', width: 300, hidden: getRegionNick() != 'vologda', id: (getRegionNick() == 'vologda' ? 'autoexpand' : null) },
				{ name: 'none', header: ' ', id: (getRegionNick() == 'vologda' ? null : 'autoexpand'), hideable: false }
			],
			dataUrl: '/?c=ClinExWork&m=getEvnVKExpert',
			totalProperty: 'totalCount'
		});
		
		this.Grid.ViewGridPanel.getStore().on('load', function(s) {
			s.isLoaded = true;
			cur_w.setEvnVKisAccepted();
		});
		
		this.Form8 = new sw.Promed.Panel({
			autoHeight: true,
			animCollapse: false,
			title: 'Состав экспертов',
			collapsed: true,
			collapsible: true,
			autoScroll: true,
			defaults: {
				border: false,
				bodyStyle: 'padding-top: 3px;'
			},
			listeners: {
				expand: function(p) {
					var grid = p.findById('EvnVKExpertsGrid').getGrid();
					var base_form = this.CenterForm.getForm();
					if(
						this.EvnVK_id != null || (
							getRegionNick() == 'vologda' &&
							base_form.findField('EvnPrescrVK_id').getValue() != null &&
							base_form.findField('EvnVK_isInternal').getValue() == 1
						)
					) {
						grid.getStore().baseParams.EvnVK_id = this.EvnVK_id;
						grid.getStore().baseParams.EvnPrescrVK_id = base_form.findField('EvnPrescrVK_id').getValue();
						grid.getStore().baseParams.EvnVK_isInternal = base_form.findField('EvnVK_isInternal').getValue();
						grid.getStore().load({
							method: 'post',
							callback: function(r) {
								if ( r.length > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
						});
					} else {
						// this.doSave({presave: true});
					}
				}.createDelegate(this)
			},
			items: [
				{
					xtype: 'panel',
					style: 'padding-left: 5px;',
					layout: 'form',
					items: [
						{
							xtype: 'checkbox',
							name: 'EvnVK_isAutoFill',
							id: 'form8_EvnVK_isAutoFill',
							tabIndex: TABINDEX_EVNVK + 34,
							hideLabel: true,
							boxLabel: 'Сохранить состав экспертов'
						}
					]
				},
				this.Grid
			]
		});
	
		this.CenterForm = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			labelAlign: 'right',
			layout: 'form',
			url: '/?c=ClinExWork&m=saveEvnVK',
			id: 'EvnVKCenterForm',
			region: 'center',
			items: [
				this.ProtocolNunPanel,
				this.Form1,
				this.Form2,
				this.Form3,
				this.Form4,
				this.Form5,
				this.Form6,
				this.Form65,
				this.Form7,
				this.Form8
			],
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[	
				{ name: 'EvnVK_id' },
				{ name: 'EvnVK_isInternal' },
				{ name: 'EvnPrescrVK_id' },
				{ name: 'MedService_id' },
				{ name: 'EvnVK_NumProtocol' },
				{ name: 'LpuSection_id' },
				{ name: 'EvnVK_setDT' },
				{ name: 'EvnVK_isReserve' },
				{ name: 'MedPersonal_id' },
				//{ name: 'EvnVK_didDT' },
				//{ name: 'EvnVK_isControl' },
				{ name: 'PatientStatusType_id' },
				//{ name: 'Okved_id' },
				{ name: 'EvnVK_Prof' },
				{ name: 'Diag_id' },
				{ name: 'Diag_sid' },
				{ name: 'EvnVK_MainDisease' },
				{ name: 'EvnVK_NumCard' },
				{ name: 'CauseTreatmentType_id' },
				{ name: 'ExpertiseNameType_id' },
				{ name: 'ExpertiseNameSubjectType_id' },
				{ name: 'EvnVK_ExpertiseStickNumber' },
				{ name: 'EvnVK_StickPeriod' },
				{ name: 'EvnStickWorkRelease_id' },
				{ name: 'EvnPrescrMse_id' },
				{ name: 'EvnVK_StickDuration' },
				{ name: 'EvnVK_DirectionDate' },
				{ name: 'EvnVK_ConclusionDate' }, 
				{ name: 'EvnVK_ConclusionPeriodDate' },
				{ name: 'EvnVK_ConclusionDescr' },
				{ name: 'EvnVK_AddInfo' },
				{ name: 'EvnVK_isUseStandard' },
				{ name: 'EvnVK_isAberration' },
				{ name: 'EvnVK_isErrors' },
				{ name: 'EvnVK_isResult' },
				{ name: 'EvnVK_UseStandard'},
				{ name: 'EvnVK_AberrationDescr' },
				{ name: 'EvnVK_ErrorsDescr' },
				{ name: 'EvnVK_ResultDescr' },
				{ name: 'EvnVK_ExpertDescr' },
				{ name: 'EvnVK_DecisionVK' },
				{ name: 'EvnVK_isAccepted' },
				{ name: 'EvnVK_isAutoFill' },
				{ name: 'EvnVK_LVN' },
				{ name: 'EvnVK_Note'},
				{ name: 'EvnVK_WorkReleasePeriod' },
				{ name: 'ExpertiseEventType_id' },
				{ name: 'PalliatEvnVK_IsPMP' },
				{ name: 'PalliativeType_id' },
				{ name: 'PalliatEvnVK_IsIVL' },
				{ name: 'PalliatEvnVK_IsSpecMedHepl' },
				{ name: 'PalliatEvnVK_VolumeMedHepl' },
				{ name: 'ConditMedCareType_id' },
				{ name: 'PalliatEvnVK_IsSurvey' },
				{ name: 'PalliatEvnVK_VolumeSurvey' },
				{ name: 'PalliatEvnVK_DirSocialProt' },
				{ name: 'PalliatEvnVK_IsInfoDiag' },
				{ name: 'PalliatEvnVK_TextTIR' },
				{ name: 'EvnDirectionHTM_id' }
			])
		});
	
		Ext.apply(this,
		{
			xtype: 'panel',
			layout: 'border',
			items: [
				this.PersonDataForm,
				this.CenterForm
			],
			buttons: [
				{
					handler: function() {
						this.doSave({presave: false});
					}.createDelegate(this),
					iconCls: 'save16',
					tabIndex: TABINDEX_EVNVK + 34,
					text: 'Сохранить'
				}, {
					handler: function() {
						var win = this,
							field = win.CenterForm.getForm().findField('EvnVK_id');
						var base_form = win.CenterForm.getForm();
						var numProtocol = base_form.findField('EvnVK_NumProtocol').getValue();
						/*if( Ext.isEmpty(field.getValue()) ) {
							win.doSave({
								presave: true,
								cb: function() {win.printEvnVK();}
							});
						} else {
							if( win.showtype == 'add' ) {
								win.buttons[0].setVisible(false);
								win.deletable = false;
							}
							win.printEvnVK();
						}*/
						if(win.showtype == 'view'){
							win.printEvnVK();
						} else {
							var printParams = {presave: false, print: true, cb: function(){win.printEvnVK()}};
							if(win.showtype == 'edit' && this.numChangedBy.print == numProtocol){
								if(!getRegionNick().inlist(['perm','ekb','astra','ufa'])){
									printParams.checkNumProtocol = 0;
								}
							}
							win.doSave(printParams);
						}
						
					}.createDelegate(this),
					tabIndex: TABINDEX_EVNVK + 35,
					iconCls: 'print16',
					text: BTN_FRMPRINT
				},
				cur_w.MseButton,
				cur_w.HtmButton,
				'-',
				HelpButton(this),
				{
					text: 'Отмена',
					tabIndex: -1,
					tabIndex: TABINDEX_EVNVK + 36,
					tooltip: 'Отмена',
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});
		sw.Promed.swClinExWorkEditWindow.superclass.initComponent.apply(this, arguments);
	}

});