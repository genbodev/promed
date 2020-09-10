/**
 * Форма редактирования ТАП в ЭМК
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.EvnPLForm', {
	requires: [
		'common.EvnXml.ItemsPanel',
		'common.EMK.EvnPrescribePanel',
		'common.EMK.EvnDirectionPanel',
		'common.EMK.EvnDrugPanel',
		'common.EMK.EvnUslugaPanel',
		'common.EMK.EvnPLDispScreenOnko',
		'common.EMK.EvnReceptPanel',
		'common.EMK.EvnXmlPanel',
		'common.EMK.PersonBottomPanel',
		'common.EMK.RepositoryObservPanel',
		'common.EMK.DrugTherapySchemePanel',
		'sw.frames.EMD.swEMDPanel'
	],
	alias: 'widget.EvnPLForm',
	extend: 'Ext6.Panel',
	layout: 'border',
	region: 'center',
	border: false,
	evnParams: {},
	params: {},
	getParams: function() {
		return this.params;
	},
	setParams: function(params) {
		var me = this;

		me.params = params;
		this.EvnPL_id = params.EvnPL_id;
		this.EvnVizitPL_id = params.EvnVizitPL_id?params.EvnVizitPL_id:null;

		this.swEMDPanel.setParams({
			EMDRegistry_ObjectName: 'EvnPL',
			EMDRegistry_ObjectID: me.EvnPL_id
		});

		/*me.EvnDirectionPanel.setParams({
			Evn_id: params.EvnPL_id,
			Person_Surname: params.Person_Surname,
			Person_Firname: params.Person_Firname,
			Person_Secname: params.Person_Secname,
			Person_Birthday: params.Person_Birthday
		});*/
		me.EvnPrescrPanel.collapse();
	},
	loadData: function(options) {
		var me = this;

		var components = me.query('combobox');
		// загружаем справочники
		loadDataLists(me, components, function() {
			// грузим ещё поля врач и ср. мед. персонал
			var base_form = me.formPanel.getForm();
			me.filterTreatmentDiag();
			me.filterMedStaffFactCombo();
			base_form.findField('UslugaComplex_uid').setVizitCodeFilters({
				isStom: false
			});
			if(base_form.findField('MedicalCareKind_id')){
				base_form.findField('MedicalCareKind_id').getStore().filterBy(function(rec){
					return ((rec.get('additionalSortCode') && rec.get('additionalSortCode') === -2) || rec.get('MedicalCareKind_Code').inlist(['4','11','12','13']));
				});
			}

			me.loadEvnPLFormPanel(options);
		});
	},
	filterUslugaComplexCombo: function(eventData,base_form){
		// cmp - UslugaComplexCombo

		var cmp = base_form.findField('UslugaComplex_uid');

		cmp.setVizitCodeFilters({
			isStom: false
		});

		if ( getRegionNick() == 'perm') {
			// log(eventData);
			if (eventData && eventData.LpuSectionProfile_id) {
				cmp.setLpuSectionProfile_id(eventData.LpuSectionProfile_id);
			}
			if (eventData && eventData.FedMedSpec_id) {
				cmp.setFedMedSpec_id(eventData.FedMedSpec_id);
			}
			if (eventData && eventData.VizitType_id) {
				cmp.getStore().getProxy().extraParams.VizitType_id = eventData.VizitType_id;
			}
			if (eventData && eventData.VizitClass_id) {
				cmp.getStore().getProxy().extraParams.VizitClass_id = eventData.VizitClass_id;
			}
			if (eventData && eventData.TreatmentClass_id) {
				cmp.getStore().getProxy().extraParams.TreatmentClass_id = eventData.TreatmentClass_id;
			}
			/*if (eventData && eventData.EvnVizitPLStom_IsPrimaryVizit) {
				cmp.getStore().baseParams.isPrimaryVizit = eventData.EvnVizitPLStom_IsPrimaryVizit;
			}*/
			if (eventData && eventData.PayType_id) {
				cmp.getStore().getProxy().extraParams.PayType_id = eventData.PayType_id;
			}


			// Делаем фильтр по дате для всех
			if ( typeof eventData == 'object' && !Ext.isEmpty(eventData.EvnVizitPL_setDate) ) {
				cmp.setUslugaComplexDate(eventData.EvnVizitPL_setDate);
			} else if (typeof eventData == 'object' && !Ext.isEmpty(eventData.EvnSection_setDate)) {
				cmp.setUslugaComplexDate(eventData.EvnSection_setDate);
			} else {
				cmp.setUslugaComplexDate(getGlobalOptions().date);
			}

			if (eventData && eventData.EvnVizitPL_id) {
				cmp.getStore().getProxy().extraParams.EvnVizit_id = eventData.EvnVizitPL_id;
			}
		}

	},
	filterMedStaffFactCombo: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		var medstafffact_filter_params = {
			allowLowLevel: 'yes',
			isPolka: true
		};

		var mid_medstafffact_filter_params = {
			allowLowLevel: 'yes',
			isMidMedPersonalOnly: true,
			isPolka: true
		};

		if (!Ext6.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) && typeof base_form.findField('EvnVizitPL_setDate').getValue() == 'object') {
			medstafffact_filter_params.onDate = base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y');
			mid_medstafffact_filter_params.onDate = base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y');
		}

		// Фильтр на конкретное место работы
		if (!Ext6.isEmpty(me.ownerWin.userMedStaffFact.LpuSection_id) && !Ext6.isEmpty(me.ownerWin.userMedStaffFact.MedStaffFact_id)) {
			if (me.ownerWin.userMedStaffFact.MedStaffFactCache_IsDisableInDoc == 2) {
				sw.swMsg.alert(langs('Сообщение'), langs('Текущее рабочее место запрещено для выбора в документах'));
				medstafffact_filter_params.id = -1;
			}
			medstafffact_filter_params.id = me.ownerWin.userMedStaffFact.MedStaffFact_id;
		}

		medstafffact_filter_params.allowDuplacateMSF = true;
		medstafffact_filter_params.EvnClass_SysNick = 'EvnVizit';

		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params, sw4.swMedStaffFactGlobalStore);
		base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));

		setMedStaffFactGlobalStoreFilter(mid_medstafffact_filter_params, sw4.swMedStaffFactGlobalStore);
		base_form.findField('MedStaffFact_sid').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
	},
	getObjectData: function(object, object_id) {
		var record = this.viewFormDataStore.getById(object +'_'+ object_id);
		if (record && record.data) {
			return record.data;
		}
		//log('In viewFormDataStore not found record with id: '+ object +'_'+ object_id);
		//log(this.viewFormDataStore);
		return false;
	},
	loadEvnPrescr: function(d, section_code) {
		var collapsedClass = 'collapsed',
			expandedClass = 'expanded',
			gr_el = Ext6.get(d.section_id),
			id = d.section_id +'_items',
			el = Ext6.get(id),
			ep_data = this.getObjectData(section_code, d.object_id);
		if (gr_el && el && ep_data) {
			if (el.dom.innerHTML.length == 0) {
				this.reloadViewForm({
					section_code: ep_data.EvnClass_SysNick,
					object_key: ep_data.EvnClass_SysNick +'_id',
					object_value: ep_data.PrescriptionType_id,
					parent_object_key: 'EvnPrescr_pid',
					parent_object_value: ep_data.EvnPrescr_pid,
					section_id: id,
					param_name: 'section',
					param_value: section_code
				});
			}
			//this.toggleDisplay(id, el.isDisplayed());
			if (gr_el.hasClass(expandedClass)) {
				gr_el.removeClass(expandedClass);
				gr_el.addClass(collapsedClass);
			} else {
				gr_el.removeClass(collapsedClass);
				gr_el.addClass(expandedClass);
			}
		}
	},
	reloadListMorbus: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		log('reloadListMorbus');
		Ext6.Ajax.request({
			url: '/?c=EMK&m=loadEvnVizitPLListMorbus',
			params: {
				EvnVizitPL_id: base_form.findField('EvnVizitPL_id').getValue()
			},
			success: function(response) {
				var result = Ext6.JSON.decode(response.responseText);
				if (result[0].listMorbus) {
					me.listMorbus = result[0].listMorbus;
				} else {
					me.listMorbus = {};
				}
				me.checkSpecifics();
			}
		});
	},
	checkSpecifics: function() {
		var me = this;
		log('checkSpecifics', me.listMorbus);
		
		me.AccordionPanel.query('panel[cls=emk-morbus-onko-panel]').forEach(function(sp) {
			sp.destroy();
		});

		if (me.listMorbus['narc']) {
			me.listMorbus['crazy'] = me.listMorbus['narc'];
			me.listMorbus['crazy']['morbusTypeSysNick'] = 'narc';
		}
		me.SpecificsPalliatPanel.setHidden(!me.listMorbus['palliat']);
		me.SpecificsHepatitisPanel.setHidden(!me.listMorbus['hepa']);
		me.SpecificsCrazyPanel.setHidden(!me.listMorbus['crazy']);
		me.SpecificsTubPanel.setHidden(!me.listMorbus['tub']);
		me.SpecificsVenerPanel.setHidden(!me.listMorbus['vener']);
		me.SpecificsNephroPanel.setHidden(!me.listMorbus['nephro']);
		me.SpecificsProfPanel.setHidden(!me.listMorbus['prof']);
		me.SpecificsGeriatrics.setHidden(!me.listMorbus['geriatrics']);

		// для специфики по беременности почему то своя логика
		var PersonRegisterType_Array = [];
		var isPregnancy = false;
		var base_form = me.formPanel.getForm();
		var PersonRegisterType_List = base_form.findField('Diag_id').getFieldValue('PersonRegisterType_List');
		if (PersonRegisterType_List && PersonRegisterType_List.length > 0) {
			PersonRegisterType_Array = PersonRegisterType_List.split(',');
		}
		me.SpecificsPregnancyPanel.setHidden(!String('pregnancy').inlist(PersonRegisterType_Array));
		//кнопка извещения об инфекционном заболевании:
		var addEvnInfectNotifyTools = me.queryById('InfectNotifyButton');
		if(!base_form.isValid()) addEvnInfectNotifyTools.hide();//т.к. не сохранено - listMorbus неактуален
		else if (addEvnInfectNotifyTools) {
			var infect058_reg = new RegExp("^(A0[0-9]|A2[0-8]|A[3-4]|A7[5-9]|A[8-9]|B0[0-9]|B1[5-9]|B2|B3[0-4]|B[5-7]|B8[0-3]|B9[0-6]|B97.[0-8]|B99)");
			var diagCode = base_form.findField('Diag_id').getFieldValue('Diag_Code');
			if (!Ext6.isEmpty(diagCode) && infect058_reg.test(diagCode)) {
				if (me.listMorbus['infect058'] /*&& 'onLoadViewPanel' == mode*/) {
					addEvnInfectNotifyTools.setVisible( !me.listMorbus['infect058']['disableAddEvnNotify'] );
				} else {
					me.checkEvnInfectNotify(base_form.findField('EvnVizitPL_id').getValue(), function(isset) {
						addEvnInfectNotifyTools.setVisible(!isset)
					});
				}
			} else {
				addEvnInfectNotifyTools.hide();
			}
		}
		
		if (me.listMorbus['onko'] && me.listMorbus['onko'].length) {
			for (var key in me.listMorbus['onko']) {
				var item = me.listMorbus['onko'][key];
				if (item['Diag_id']) {
					var sp = Ext6.create('swSpecificPanel', {
						cls: 'emk-morbus-onko-panel',
						userCls: 'emk-morbus-onko-panel',
						EvnDiag_id: item['EvnDiagPLSop_id'],
						specificTitle: 'ОНКОЛОГИЯ <span style="'+(!item['diagIsMain'] ? 'font-weight: normal;' : '')+'">' + item['Diag_Code'] + '</span>',
						handler: function() {
							me.openSpecificsWindow('onko', this.EvnDiag_id);
						}
					});
					me.AccordionPanel.insert(1,sp);
				}
			}
		}
	},
	checkMesOldUslugaComplexFields: function () {
		
		if (getRegionNick() == 'kz') return false;
		
		var me = this;
		var base_form = me.formPanel.getForm();
		// проверка связи диагноза/услуги с MesOldUslugaComplex
		if (me.checkRequestId) {
			Ext6.Ajax.abort(me.checkRequestId); // прервыем предыдущий, если есть
		}
		me.checkRequestId = Ext6.Ajax.request({
			callback: function (options, success, response) {
				me.checkRequestId = false;

				if (response.responseText) {
					var result = Ext6.util.JSON.decode(response.responseText);
					if (result.success) {
						if (result.hasDrugTherapySchemeLinks) {
							me.DrugTherapySchemePanel.show();
							if (result.DrugTherapySchemeIds) {
								me.DrugTherapySchemePanel.setParams({
									EvnVizitPL_id: base_form.findField('EvnVizitPL_id').getValue(),
									FilterIds: result.DrugTherapySchemeIds
								});
							}
							me.hasDrugTherapySchemeLinks = true;
						} else {
							me.DrugTherapySchemePanel.hide();
						}
					}
				}
			},
			params: {
				EvnVizitPL_setDate: !!base_form.findField('EvnVizitPL_setDate').getValue() ? base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y') : getGlobalOptions().date,
				LpuUnitType_id: base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id'),
				EvnVizitPL_id: base_form.findField('EvnVizitPL_id').getValue(),
				Diag_id: base_form.findField('Diag_id').getValue()
			},
			url: '/?c=EvnVizit&m=checkMesOldUslugaComplexFields'
		});
	},
	setVizitTypeFilter: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		var EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue();

		base_form.findField('VizitType_id').setTreatmentClass(base_form.findField('TreatmentClass_id').getValue());
		if (!Ext6.isEmpty(base_form.findField('PayType_id').getValue()) && getRegionNick() == 'kareliya') {
			var pay_type_nick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');

			if (pay_type_nick && pay_type_nick == 'oms') {
				var denied_visit_type_codes = ['41', '51', '2.4', '3.1'];

				if (EvnVizitPL_setDate < new Date('2019-05-01')) {
					denied_visit_type_codes.push('1.2');
				}

				base_form.findField('VizitType_id').setFilterByDateAndCode(EvnVizitPL_setDate, denied_visit_type_codes);
			} else {
				base_form.findField('VizitType_id').setFilterByDate(EvnVizitPL_setDate);
			}
		} else {
			base_form.findField('VizitType_id').setFilterByDate(EvnVizitPL_setDate);
		}
	},
	loadUslugaComplex: function(){
		var me = this;
		var base_form = me.formPanel.getForm();
		// прогружаем услугу
		base_form.findField('UslugaComplex_uid').getStore().load({
			callback: function(records) {
				if(records.length==1){
					base_form.findField('UslugaComplex_uid').setValue(records[0].get('UslugaComplex_id'));
				}else{
					base_form.findField('UslugaComplex_uid').clearValue();
				}
			}
		});
	},
	TumorStageComboEdit: function() {
		
		if (getRegionNick() == 'kareliya') {
			var me = this;
			var base_form = me.formPanel.getForm(),
				DeseaseType = base_form.findField('DeseaseType_id'),
				TumorStage = base_form.findField('TumorStage_id');
			
			if (TumorStage.isVisible() && DeseaseType.getValue() == 1) {
				TumorStage.enable();
				TumorStage.setAllowBlank(false);
			} else {
				TumorStage.disable();
				TumorStage.setAllowBlank(true);
				TumorStage.clearValue();
			}
		}
	},
	onLoadEvnVizitPLFormPanel: function(data) {
		Ext6.suspendLayouts();
		var me = this;
		var base_form = me.formPanel.getForm();
		if (data[0]) {
			if ((me.ownerWin.PersonInfoPanel && me.ownerWin.PersonInfoPanel.checkIsDead() || me.EvnPL_IsFinish)) {
				me.formPanel.accessType = 'view';
			} else {
				me.formPanel.accessType = data[0].accessType;
			}

			if (data[0].AlertReg_Msg) {
				sw4.showInfoMsg({
					panel: me,
					type: 'warning',
					text: data[0].AlertReg_Msg
				});
			}

			if (me.formPanel.accessType == 'edit') {
				me.EvnVizitPLMenu.enable();
				me.formPanel.enableEdit(true);
				me.bottomPanel.enableEdit(true);
				// me.EvnVizitPLMenu.down('[itemId=deleteEvnVizitPL]').enable();
				// me.EvnVizitPLMenu.down('[itemId=copyEvnVizitPL]').enable();
			} else {
				me.EvnVizitPLMenu.disable();
				me.formPanel.enableEdit(false);
				me.bottomPanel.enableEdit(false);
				// me.EvnVizitPLMenu.down('[itemId=deleteEvnVizitPL]').disable();
				// me.EvnVizitPLMenu.down('[itemId=copyEvnVizitPL]').disable();
			}

			if (getRegionNick().inlist(['ufa', 'kareliya', 'ekb'])) {
				base_form.findField('MedicalCareKind_id').disable();
			}

			if (getRegionNick().inlist(['buryatiya', 'ekb', 'pskov', 'ufa'])) {
				base_form.findField('UslugaComplex_uid').setPersonId(data[0].Person_id);
			}

			if (getRegionNick() == 'ufa') {
				base_form.findField('EvnVizitPL_IsZNO').disable();
			}
			
			this.TumorStageComboEdit();

			if (!Ext6.isEmpty(data[0].UslugaComplex_uid)) {
				// прогружаем услугу
				base_form.findField('UslugaComplex_uid').getStore().load({
					params: {
						UslugaComplex_id: data[0].UslugaComplex_uid
					},
					callback: function () {
						base_form.findField('UslugaComplex_uid').setValue(data[0].UslugaComplex_uid);
						base_form.findField('UslugaComplex_uid').fireEvent('change', base_form.findField('UslugaComplex_uid'), base_form.findField('UslugaComplex_uid').getValue());
					}
				});
			} else {
				me.filterUslugaComplexCombo(data[0],base_form);
			}
			if (!Ext6.isEmpty(data[0].MedStaffFact_id)) {
				// ищем врача в комбо
				var record = base_form.findField('MedStaffFact_id').getStore().findRecord('MedStaffFact_id', data[0].MedStaffFact_id);
				if (record) {
					base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
					if (!Ext6.isEmpty(data[0].LpuSectionProfile_id)) {
						// прогружаем профиль
						me.loadLpuSectionProfileDop(data[0].LpuSectionProfile_id);
					}
				} else {
					// загружаем с сервера
					base_form.findField('MedStaffFact_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
								if (rec.get('MedStaffFact_id') == data[0].MedStaffFact_id) {
									return true;
								} else {
									return false;
								}
							});

							if (index >= 0) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
								if (!Ext6.isEmpty(data[0].LpuSectionProfile_id)) {
									// прогружаем профиль
									me.loadLpuSectionProfileDop(data[0].LpuSectionProfile_id);
								}
							}
						}.createDelegate(this),
						params: {
							MedStaffFact_id: data[0].MedStaffFact_id
						}
					});
				}
			} else if (!Ext6.isEmpty(data[0].MedPersonal_id)) {
				// ищем врача в комбо
				var record = base_form.findField('MedStaffFact_id').getStore().findRecord('MedPersonal_id', data[0].MedPersonal_id);
				if (record) {
					base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
				} else {
					// загружаем с сервера
					base_form.findField('MedStaffFact_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
								if (rec.get('MedPersonal_id') == data[0].MedPersonal_id && rec.get('LpuSection_id') == data[0].LpuSection_id) {
									return true;
								} else {
									return false;
								}
							});

							if (index >= 0) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}.createDelegate(this),
						params: {
							LpuSection_id: data[0].LpuSection_id,
							MedPersonal_id: data[0].MedPersonal_id
						}
					});
				}
			}
			if (!Ext6.isEmpty(data[0].MedPersonal_sid)) {
				// ищем врача в комбо
				var record = base_form.findField('MedStaffFact_sid').getStore().findRecord('MedPersonal_id', data[0].MedPersonal_sid);
				if (record) {
					base_form.findField('MedStaffFact_sid').setValue(record.get('MedStaffFact_id'));
				} else {
					// загружаем с сервера
					base_form.findField('MedStaffFact_sid').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
								if (rec.get('MedPersonal_id') == data[0].MedPersonal_sid && rec.get('LpuSection_id') == data[0].LpuSection_id) {
									return true;
								} else {
									return false;
								}
							});

							if (index >= 0) {
								base_form.findField('MedStaffFact_sid').setValue(base_form.findField('MedStaffFact_sid').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}.createDelegate(this),
						params: {
							LpuSection_id: data[0].LpuSection_id,
							MedPersonal_id: data[0].MedPersonal_sid
						}
					});
				}
			}
			
			if (!Ext6.isEmpty(data[0].LpuSection_id)) {
				var record = base_form.findField('LpuSection_id').getStore().findRecord('LpuSection_id', data[0].LpuSection_id);
				if (record) {
					base_form.findField('LpuSection_id').setValue(record.get('LpuSection_id'));
				} else {
					base_form.findField('LpuSection_id').getStore().load({
						callback: function() {
							index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
								if (rec.get('LpuSection_id') == data[0].LpuSection_id) {
									return true;
								} else {
									return false;
								}
							});

							if (index >= 0) {
								base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
							}
						}.createDelegate(this),
						params: {
							LpuSection_id: data[0].LpuSection_id,
						}
					});
				}
			}

			if (data[0].ProfGoal_id == null) {
				base_form.findField('ProfGoal_id').disable();
			}

			if (data[0].listMorbus) {
				me.listMorbus = data[0].listMorbus;
			} else {
				me.listMorbus = {};
			}
			me.checkSpecifics();

			me.ProtocolPanel.setTitleCounter(data[0].ProtocolCount);
			me.ProtocolPanel.setAccessType(me.formPanel.accessType);
			me.ProtocolPanel.setParams({
				Person_id: data[0].Person_id,
				Evn_id: data[0].EvnVizitPL_id,
				EvnClass_id: data[0].EvnClass_id,
				LpuSection_id: data[0].LpuSection_id,
				MedPersonal_id: data[0].MedPersonal_id,
				MedStaffFact_id: data[0].MedStaffFact_id
			});

			me.EvnPrescrPanel.setTitleCounter(data[0].EvnPrescrCount);
			me.EvnPrescrPanel.setAccessType(me.formPanel.accessType);
			me.EvnPrescrPanel.enableEdit(me.formPanel.accessType == 'edit');

			/*me.EvnDirectionPanel.setTitleCounter(data[0].EvnDirectionCount);
			me.EvnDirectionPanel.setParams({
				Evn_id: data[0].EvnVizitPL_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id
			});
			me.EvnDirectionPanel.setAccessType(me.formPanel.accessType);*/

			me.EvnDrugPanel.setTitleCounter(data[0].EvnDrugCount);
			me.EvnDrugPanel.setParams({
				Evn_id: data[0].EvnVizitPL_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id
			});
			me.EvnDrugPanel.setAccessType(me.formPanel.accessType);

			var piPanel = me.ownerWin.PersonInfoPanel;

			var srn='',frn='',scn='',pbd;
			if (piPanel && piPanel.getFieldValue('Person_Surname')) {
				srn = piPanel.getFieldValue('Person_Surname');
				if (piPanel.getFieldValue('Person_Firname')) {
					frn = piPanel.getFieldValue('Person_Firname');
				}
				if (piPanel.getFieldValue('Person_Secname')) {
					scn = piPanel.getFieldValue('Person_Secname');
				}
				if (piPanel.getFieldValue('Person_Birthday')) {
					pbd = piPanel.getFieldValue('Person_Birthday');
				}
			}
			
			if (getRegionNick().inlist(['ufa'])) {
				let visible = false;
				let Sex_Code = piPanel.getFieldValue('Sex_Code');
				let person_Birthday = piPanel.getFieldValue('Person_Birthday');
				let EvnVizitPL_setDate = base_form.findField('EvnVizitPL_setDate').getValue();
				let Person_Age = swGetPersonAge(person_Birthday, EvnVizitPL_setDate);
				if (Sex_Code == 2 && Person_Age >= 15 && Person_Age <= 50) {
					visible = true;
				}
				base_form.findField('PregnancyEvnVizitPL_Period').setVisible(visible);
			}
			
			me.EvnUslugaPanel.setTitleCounter(data[0].EvnUslugaCount);
			me.EvnUslugaPanel.setParams({
				Evn_id: data[0].EvnVizitPL_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id
			});
			me.EvnUslugaPanel.setAccessType(me.formPanel.accessType);

			me.EvnPLDispScreenOnko.setTitleCounter(data[0].EvnPLDispScreenOnkoCount);
			me.EvnPLDispScreenOnko.setParams({
				Evn_id: data[0].EvnVizitPL_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id
			});
			me.EvnPLDispScreenOnko.setAccessType(me.formPanel.accessType);

			me.RepositoryObservPanel.setTitleCounter(data[0].RepositoryObservCount);
			me.RepositoryObservPanel.setParams({
				Evn_id: data[0].EvnVizitPL_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id
			});
			me.RepositoryObservPanel.setAccessType(me.formPanel.accessType);

			me.EvnReceptPanel.setTitleCounter(data[0].EvnReceptCount);
			me.EvnReceptPanel.setParams({
				isKardio: data[0].isKardio,
				Evn_id: data[0].EvnVizitPL_id,
				EvnClass_id: data[0].EvnClass_id,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id,
				Evn_setDate: data[0].EvnVizitPL_setDate,
				LpuSection_id: data[0].LpuSection_id,
				Diag_id: data[0].Diag_id,
				MedPersonal_id: data[0].MedPersonal_id
			});
			me.EvnReceptPanel.setAccessType(me.formPanel.accessType);

			me.EvnXmlPanel.setTitleCounter(data[0].EvnXmlCount);
			me.EvnXmlPanel.setParams({
				Evn_id: data[0].EvnVizitPL_id,
				EvnClass_id: data[0].EvnClass_id,
				userMedStaffFact: me.ownerWin.userMedStaffFact
			});
			me.EvnXmlPanel.setAccessType(me.formPanel.accessType);

			// todo перенести загрузку на раскрытие разделов
			me.EvnPrescrPanel.collapse();
			me.EvnPrescrPanel.getController().loadData({
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id,
				Evn_id: data[0].EvnVizitPL_id,
				Evn_setDate: data[0].EvnVizitPL_setDate,
				LpuSection_id: data[0].LpuSection_id,
				MedPersonal_id: data[0].MedPersonal_id
			}, me.evnParams);
			
			if (data[0].DrugTherapyScheme_ids) {
				me.DrugTherapySchemePanel.DrugTherapySchemeGrid.getStore().loadData(data[0].DrugTherapyScheme_ids);
				me.DrugTherapySchemePanel.setAccessType(me.formPanel.accessType);
		}
		}

		me.evnVizitPLDataToLoad = false;
		me.unmask();
		Ext6.resumeLayouts(true);
	},
	setMainPanelTitle: function(data){
		// меняем титл
		var me = this,
			title = '';
		if(data.EvnPL_NumCard){
			title = 'Случай амбулаторного лечения № ' + data.EvnPL_NumCard;
		} else {
			var oldTitle = me.titleLabel.html;
			var partTitle = oldTitle.split(' - ');
			title = (partTitle && partTitle[0])?partTitle[0]:'';
		}

		if (!Ext6.isEmpty(data.Diag_Code)) {
			title = title + ' - <b>' + data.Diag_Code + '</b> ' + data.Diag_Name;
		}
		me.evnParams = {
			Diag_id: data.Diag_id?data.Diag_id:null,
			Diag_Code: data.Diag_Code,
			Diag_Name: data.Diag_Name,
			Diag_lName: data.Diag_lName?data.Diag_lName:null
		};
		me.titleLabel.setHtml(title);
	},
	onLoadEvnPLFormPanel: function(data) {
		Ext6.suspendLayouts();
		var me = this;
		var isStom = me.ownerWin.userMedStaffFact.ARMType.inlist(['stom', 'stom6']);

		if (data[0]) {
			if (me.ownerWin.PersonInfoPanel && me.ownerWin.PersonInfoPanel.checkIsDead()) {
				me.EvnPLFormPanel.accessType = 'view';
			} else {
				me.EvnPLFormPanel.accessType = data[0].accessType;
			}
			me.EvnPLFormPanel.canCreateVizit = data[0].canCreateVizit == 1 ? true : false;
			me.EvnPL_IsFinish = data[0].EvnPL_IsFinish == 2 ? true : false;

			if (data[0].AlertReg_Msg) {
				sw4.showInfoMsg({
					panel: me,
					type: 'warning',
					text: data[0].AlertReg_Msg
				});
			}

			if (me.EvnPLDirectionInfoEditPanel.isVisible()) {
				me.EvnPLDirectionInfoEditPanel.hide();
				me.EvnPLDirectionInfoPanel.show();

			}

			me.EvnPL_NumCard = data[0].EvnPL_NumCard;
			me.EvnPLDirectionInfoPanel.reset();
			me.EvnPLDirectionInfoPanel.loadEvnDirection(data[0].EvnPL_id);
			
			if (me.EvnPLFormPanel.accessType == 'edit') {
				me.tabToolPanel.down('[refId=addEvnVizitPL]').enable();
				me.EvnPLMenu.down('[itemId=deleteEvnPL]').enable();
			} else {
				me.tabToolPanel.down('[refId=addEvnVizitPL]').disable();
				me.EvnPLMenu.down('[itemId=deleteEvnPL]').disable();
			}
			me.tabToolPanel.down('[refId=addEvnVizitPL]').setDisabled(isStom || !me.EvnPLFormPanel.canCreateVizit);

			me.swEMDPanel.setIsSigned(data[0].EvnPL_IsSigned);

			if (me.EvnPLFormPanel.accessType == 'edit') {
				me.swEMDPanel.enable();
			} else {
				me.swEMDPanel.disable();
			}

			me.EvnPLFormPanel.down('[name=EvnPL_IsFirstDisable]').setVisible(!Ext.isEmpty(data[0].EvnPL_IsFirstDisable));
			me.EvnPLFormPanel.down('[name=PrivilegeType_id]').setVisible(!Ext.isEmpty(data[0].PrivilegeType_id));

			me.toolPanel.down('[refId=finishEvnPLButton]').disable();
			me.EvnPLMenu.down('[itemId=cancelEvnPLFinish]').disable();
			if (data[0].EvnPL_IsFinish == 2) {
				me.EvnPLFormPanel.show();
				if (me.EvnPLFormPanel.accessType == 'edit') {
					me.EvnPLMenu.down('[itemId=cancelEvnPLFinish]').enable();
					me.addEvnVizitPLBtn.setDisabled(true);
				}
			} else {
				if (me.EvnPLFormPanel.accessType == 'edit') {
					me.toolPanel.down('[refId=finishEvnPLButton]').enable();
					me.addEvnVizitPLBtn.setDisabled(false);
				} else {
					me.addEvnVizitPLBtn.setDisabled(isStom || !me.EvnPLFormPanel.canCreateVizit);
				}

				me.EvnPLFormPanel.hide();
			}
			me.setMainPanelTitle(data[0]);
			// создаём табы в TabPanel в соответствии с посещениями
			me.resetEvnVizitPL();
			me.tabPanel.removeAll();
			var selectTab = 0;
			for (var k in data[0].EvnVizitPL) {
				if (data[0].EvnVizitPL[k].EvnVizitPL_id) {
					if(me.EvnVizitPL_id == data[0].EvnVizitPL[k].EvnVizitPL_id)
						selectTab = parseInt(k);
					var panel = Ext6.create('Ext6.Panel', {
						title: data[0].EvnVizitPL[k].EvnVizitPL_setDate,
						border: false,
						html: '',
						EvnVizitPL_id: data[0].EvnVizitPL[k].EvnVizitPL_id
					});
					me.tabPanel.add(panel);
				}
			}

			// грузим данные по первому Tab'у.
			me.tabPanel.setActiveTab(selectTab);

			me.bottomPanel.setParams({
				Evn_id: me.EvnPL_id,
				EvnClass_id: data[0].EvnClass_id,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id,
				userMedStaffFact: me.ownerWin.userMedStaffFact
			});
			me.bottomPanel.setTitleCounters(data[0]);
		}
		Ext6.resumeLayouts(true);
	},
	loadEvnPLFormPanel: function(options) {
		if (!options) {
			options = {};
		}

		var me = this;

		me.mask(LOADING_MSG);
		if (options.dataToLoad && options.dataToLoad.evnPLData) {
			if (options.dataToLoad.evnVizitPLData) {
				me.evnVizitPLDataToLoad = options.dataToLoad.evnVizitPLData;
			}
			me.EvnPLFormPanel.getForm().setValues(options.dataToLoad.evnPLData[0]);

			if (options && typeof options.callback == 'function') {
				options.callback();
			}

			me.unmask();

			me.onLoadEvnPLFormPanel(options.dataToLoad.evnPLData);
		} else {
			me.EvnPLFormPanel.getForm().load({
				params: {
					EvnPL_id: me.EvnPL_id
				},
				success: function(form, action) {
					me.unmask();

					if (options && typeof options.callback == 'function') {
						options.callback();
					}

					if (action.response && action.response.responseText) {
						var data = Ext6.JSON.decode(action.response.responseText);
						me.onLoadEvnPLFormPanel(data);
					}
				},
				failure: function(form, action) {
					if (options && typeof options.callback == 'function') {
						options.callback();
					}
				}
			});
		}
	},
	loadLpuSectionProfileDop: function(LpuSectionProfile_id) {
		var me = this;
		var base_form = me.formPanel.getForm();
		base_form.findField('LpuSectionProfile_id').getStore().removeAll();

		if (!Ext6.isEmpty(base_form.findField('MedStaffFact_id').getValue())) {
			var LpuSection_id = base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id');
			if (!Ext6.isEmpty(LpuSection_id)) {
				me.getLoadMask('Загрузка списка профилей').show();
				base_form.findField('LpuSectionProfile_id').getStore().load({
					params: {
						LpuSection_id: base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id'),
						onDate: (!Ext6.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) && typeof base_form.findField('EvnVizitPL_setDate').getValue() == 'object') ? base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y') : getGlobalOptions().date
					},
					callback: function() {
						me.getLoadMask().hide();

						if (LpuSectionProfile_id) {
							base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
						}
					}
				});
			}
		}
	},
	resetEvnVizitPL: function() {
		Ext6.suspendLayouts();
		var me = this;
		var base_form = me.formPanel.getForm();
		var accessType = 'view';
		base_form.reset();

		me.EvnVizitPLMenu.disable();
		me.formPanel.accessType = accessType;
		me.formPanel.enableEdit(false);
		me.bottomPanel.enableEdit(false);
		
		me.AccordionPanel.query('panel[cls=emk-morbus-onko-panel]').forEach(function(sp) {
			sp.destroy();
		});

		me.DrugTherapySchemePanel.hide();
		me.DrugTherapySchemePanel.DrugTherapySchemeGrid.getStore().removeAll();

		me.SpecificsHepatitisPanel.hide();
		me.SpecificsPregnancyPanel.hide();
		me.SpecificsCrazyPanel.hide();
		me.SpecificsTubPanel.hide();
		me.SpecificsVenerPanel.hide();
		me.SpecificsNephroPanel.hide();
		me.SpecificsProfPanel.hide();
		me.SpecificsPalliatPanel.hide();
		me.SpecificsGeriatrics.hide();

		me.ProtocolPanel.collapse();
		me.ProtocolPanel.setTitleCounter(0);
		me.ProtocolPanel.setAccessType(accessType);

		me.EvnPrescrPanel.setTitleCounter(0);
		me.EvnPrescrPanel.setAccessType(accessType);
		me.EvnPrescrPanel.enableEdit(false);

		/*me.EvnDirectionPanel.setTitleCounter(0);
		me.EvnDirectionPanel.setAccessType(accessType);*/

		me.EvnDrugPanel.setTitleCounter(0);
		me.EvnDrugPanel.setAccessType(accessType);

		me.EvnPLDispScreenOnko.setTitleCounter(0);
		me.EvnPLDispScreenOnko.setAccessType(accessType);

		me.RepositoryObservPanel.setTitleCounter(0);
		me.RepositoryObservPanel.setAccessType(accessType);

		me.EvnUslugaPanel.setTitleCounter(0);
		me.EvnUslugaPanel.setAccessType(accessType);

		me.EvnReceptPanel.setTitleCounter(0);
		me.EvnReceptPanel.setAccessType(accessType);

		me.EvnXmlPanel.setTitleCounter(0);
		me.EvnXmlPanel.setAccessType(accessType);
		Ext6.resumeLayouts(true);
	},
	saveEvnVizitPL: function(params) {
		var me = this;
		var base_form = me.formPanel.getForm();

		if (me.formPanel.accessType != 'edit') {
			return false;
		}

		if ( !base_form.isValid() ) {
			return false; // сохраняем только когда всё заполнено
		}

		sw4.showInfoMsg({
			panel: me,
			type: 'loading',
			text: 'Сохранение...'
		});

		if(!params)
			params = {};
		if (!Ext6.isEmpty(base_form.findField('MedStaffFact_sid').getValue())) {
			params.MedPersonal_sid = base_form.findField('MedStaffFact_sid').getFieldValue('MedPersonal_id');
		}

		if (me.checkDiagOMS()) {
			return false;
		}
		
		if (me.checkIsFacult()) {
			return false;
		}
		
		base_form.submit({
			url: '/?c=EvnPL&m=saveEvnVizitFromEMK',
			params: params,
			success: function(result_form, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'success',
					text: 'Данные сохранены.'
				});
				// Меняем дату на вкладке посещения
				if(me.tabPanel.activeTab && base_form.findField('EvnVizitPL_setDate').getRawValue())
					me.tabPanel.activeTab.setTitle(base_form.findField('EvnVizitPL_setDate').getRawValue());

				if (action.result && action.result.listMorbus) {
					me.listMorbus = action.result.listMorbus;
				}
				me.checkSpecifics();

				if (me.swEMDPanel.IsSigned == 2) {
					me.swEMDPanel.setIsSigned(1);
				}

				me.ProtocolPanel.editorPanel.refreshSpecMarkerBlocksContent();
				
				if(getRegionNick()=='vologda') {
					me.checkEvnVizitsPL(3);
				}
			},
			failure: function(result_form, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					text: 'Ошибка сохранения данных.'
				});
				if ( action.result && action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								params[action.result.ignoreParam] = 1;
								if (action.result.ignoreParam == 'ignoreDiagDispCheck') {
									var formParams = new Object();
									var params_disp = new Object();

									formParams.Person_id = base_form.findField('Person_id').getValue();
									formParams.Server_id = base_form.findField('Server_id').getValue();
									formParams.PersonDisp_begDate = getGlobalOptions().date;
									formParams.PersonDisp_DiagDate = getGlobalOptions().date;
									formParams.Diag_id = base_form.findField('Diag_id').getValue();

									params_disp.action = 'add';
									params_disp.callback = Ext.emptyFn;
									params_disp.formParams = formParams;
									params_disp.onHide = Ext.emptyFn;

									getWnd('swPersonDispEditWindow').show(params_disp);
								}
								me.saveEvnVizitPL(params);
							}
							else {
								if (action.result.ignoreParam == 'ignoreDiagDispCheck') {
									params[action.result.ignoreParam] = 1;
									me.saveEvnVizitPL(params);
								}
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: action.result.Alert_Msg,
						title: 'Продолжить сохранение?'
					});
				}
			}
		});
	},
	checkIsFacult: function(){
		let base_form = this.formPanel.getForm();
		
		if ( getRegionNick() == 'ufa' && base_form.findField('Diag_id').getFieldValue('DiagFinance_IsFacult') == '0') {
			sw.swMsg.alert(langs('Ошибка'), langs('Данный диагноз может быть только сопутствующим. Укажите верный основной диагноз.'), function () {
				base_form.findField('Diag_id').focus(true);
			}.createDelegate(this));
			return true;
		}
		return  false;
	},
	checkDiagOMS: function() {
		let base_form = this.formPanel.getForm();
		let diag = base_form.findField('Diag_id').getFieldValue('DiagFinance_IsOms');
		let payType = base_form.findField('PayType_id');

		if (getRegionNick().inlist(['ufa', 'kareliya'])) {
			if (payType.getValue() == 9 && !diag) {
				sw.swMsg.alert(langs('Предупреждение'), langs('Выбранный диагноз не оплачивается по ОМС'));
				return true;
			}
		}
		return false;
	},
	saveEvnPLDirection: function() {
		var me = this;
		var base_form = me.EvnPLDirectionInfoEditPanel.getForm();
		var piPanel = me.ownerWin.PersonInfoPanel;
		let params = {};

		if (me.formPanel.accessType != 'edit') {
			return false;
		}

		if ( !base_form.isValid() ) {
			return false; // сохраняем только когда всё заполнено
		}

		sw4.showInfoMsg({
			panel: me,
			type: 'loading',
			text: 'Сохранение...'
		});

		let EvnPL_IsWithoutDirection = base_form.findField('EvnPL_IsWithoutDirection').getValue();
		if (EvnPL_IsWithoutDirection == 'on') {
			base_form.findField('EvnPL_IsWithoutDirection').setValue(1);
		} else {
			base_form.findField('EvnPL_IsWithoutDirection').setValue(0);
		}

		params.Person_id = me.params.Person_id;
		params.PersonEvn_id = piPanel.getFieldValue('PersonEvn_id');
		params.EvnPL_id = me.EvnPL_id;
		params.EvnPL_NumCard = me.EvnPL_NumCard;
		params.Server_id = me.params.Server_id;

		base_form.submit({
			url: '/?c=EvnPL&m=saveEvnPL',
			params: params,
			success: function() {
				me.EvnPLDirectionInfoPanel.loadEvnDirection(params.EvnPL_id);
				sw4.showInfoMsg({
					panel: me,
					type: 'success',
					text: 'Данные сохранены.'
				});
			},
			failure: function() {
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					text: 'Ошибка сохранения данных.'
				});
			}
		});
	},
	getEvnData: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		return {
			LpuSection_Name: base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_Name'),
			MedPersonal_Fin: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_Fin'),
			Evn_setDate: (!Ext6.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) && typeof base_form.findField('EvnVizitPL_setDate').getValue() == 'object') ? base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y') : getGlobalOptions().date,
			Evn_setTime: base_form.findField('EvnVizitPL_setTime').getValue(),
			MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
			LpuSection_id: base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id'),
			MedPersonal_id: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
			LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
			ServiceType_SysNick: base_form.findField('ServiceType_id').getFieldValue('ServiceType_SysNick'),
			VizitType_SysNick: base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick'),
			Diag_id: base_form.findField('Diag_id').getValue(),
			UslugaComplex_Code: base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_Code')
		};
	},
	cancelEvnPLFinish: function(options) {
		options = options || {};

		var me = this;

		var params = {
			EvnPL_id: me.EvnPL_id,
			EvnPL_IsFinish: 1
		};

		if (options.params) {
			for (var param in options.params) {
				params[param] = options.params[param];
			}
		} else {
			options.params = {};
		}

		me.mask('Сохранение...');
		Ext6.Ajax.request({
			url: '/?c=EvnPL&m=saveEvnPLFinishForm',
			params: params,
			callback: function(options, success, response) {
				me.unmask();

				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj.Alert_Msg && response_obj.Error_Msg == 'YesNo') {
					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if (buttonId == 'yes') {
								options.params[response_obj.ignoreParam] = 1;
								if (action.result.ignoreParam == 'ignoreDiagDispCheck') {
									var formParams = new Object();
									var params_disp = new Object();

									formParams.Person_id = base_form.findField('Person_id').getValue();
									formParams.Server_id = base_form.findField('Server_id').getValue();
									formParams.PersonDisp_begDate = getGlobalOptions().date;
									formParams.PersonDisp_DiagDate = getGlobalOptions().date;
									formParams.Diag_id = base_form.findField('Diag_id').getValue();

									params_disp.action = 'add';
									params_disp.callback = Ext.emptyFn;
									params_disp.formParams = formParams;
									params_disp.onHide = Ext.emptyFn;

									getWnd('swPersonDispEditWindow').show(params_disp);
								}

								me.cancelEvnPLFinish(options);
							}
							else {
								if (action.result.ignoreParam == 'ignoreDiagDispCheck') {
									options.params[response_obj.ignoreParam] = 1;
									me.cancelEvnPLFinish(options);
								}
							}
						},
						icon: Ext6.MessageBox.QUESTION,
						msg: response_obj.Alert_Msg,
						title: langs('Продолжить сохранение?')
					});
				} else {
					me.loadEvnPLFormPanel();
				}
			}
		});
	},
	filterTreatmentDiag: function() {
		var me = this,
			regNick = getRegionNick(), 
			base_form,

			// Поле "Диагноз" и код указанного в нем диагноза:
			diagFld,
			Diag_Code,
			
			// Комбобокс "Вид обращения", его стор, текущее значение и индекс выбранного значения в сторе:
			tcCombo,
			tcStore,
			tcId,
			aindex,
			
			// Идентификаторы места и цели, указанные в посещении:
			stId,
			vtId;

		if (!me.formPanel ||
			!(base_form = me.formPanel.getForm()) ||
			!(tcCombo = base_form.findField('TreatmentClass_id')) ||
			!(tcStore = tcCombo.getStore()) ||
			!(diagFld = base_form.findField('Diag_id')))
			return;

		tcStore.clearFilter();
		Diag_Code = diagFld.getFieldValue('Diag_Code');
		if (!Ext6.isEmpty(Diag_Code)) {
			tcStore.filterBy(function(rec) {
				if (Diag_Code == 'Z51.5') {
					return (rec.get('TreatmentClass_id').inlist([9]));
				} else if (Diag_Code.substr(0, 1) == 'Z' || (regNick == 'perm' && Diag_Code.substr(0, 3) == 'W57')) {
					return (rec.get('TreatmentClass_id').inlist([6, 7, 8, 9, 10, 11, 12]));
				} else if (regNick == 'penza') {
					return (rec.get('TreatmentClass_id').inlist([1, 2, 3, 4, 11, 13]));
				} else {
					return (rec.get('TreatmentClass_id').inlist([1, 2, 3, 4, 13]));
				}
			});
		} else {
			tcStore.filterBy(function(rec) {
				return (!rec.get('TreatmentClass_Code').inlist([2]));
			});
		}

		// Заполняем поле "Вид обращения" (TreatmentClass)
		// #15821 Для Карелии это поле скрыто, заполняем его автоматически по месту (ServiceType_id) и цели
		// (VizitType_id), указанным в посещении. Если вид, соответствующий месту и цели, найти не удается, берем первый
		// вид обращения из имеющихся в сторе:
		if (regNick == 'kareliya')
		{
			// Если в посещении указаны место и цель, ищем вид обращения по ним:
			if ((stId = base_form.findField('ServiceType_id')) && (stId = stId.getValue()) &&
				(vtId = base_form.findField('VizitType_id')) && (vtId = vtId.getValue()))
			{
				aindex = tcStore.findBy(function (rec) {
					var bindex = swTreatmentClassServiceTypeGlobalStore.findBy(function (r) {
						var cindex = swTreatmentClassVizitTypeGlobalStore.findBy(function (r2) {
							return (
								r.get('ServiceType_id') == stId &&
								r.get('TreatmentClass_id') == rec.get('TreatmentClass_id') &&
								r2.get('VizitType_id') == vtId &&
								r2.get('TreatmentClass_id') == rec.get('TreatmentClass_id')
							);
						});
						return (cindex != -1);
					});
					return (bindex != -1);
				});

				if (aindex == -1)
					aindex = 0;
			}
			else
				aindex = 0;

			tcCombo.select(tcStore.getAt(aindex));
		}
		else
		// Для остальных регионов, если в сторе нет элемента, указанного в поле "Вид обращения", чистим это поле:  
		{
			tcId = tcCombo.getValue();
			
			aindex = tcStore.findBy(function(rec) {
				return (rec.get('TreatmentClass_id') == tcId);
			});
		
			if (aindex == -1)
				tcCombo.clearValue();
		}
	},
	checkEvnInfectNotify: function(evn_id, callback) {		
		Ext6.Ajax.request({
			url: '/?c=EvnInfectNotify&m=isIsset',
			params: {EvnInfectNotify_pid: evn_id},
			callback: function(options, success, response) {
				var result = Ext6.util.JSON.decode(response.responseText);
				callback(result.success);
			}
		});
	},
	addEvnInfectNotify: function() {
		var me = this,
			base_form = me.formPanel.getForm(),
			diagcombo = base_form.findField('Diag_id'),
			piPanel = me.ownerWin.PersonInfoPanel,
			addEvnInfectNotifyTools = me.queryById('InfectNotifyButton'),
			formParams = {
				EvnInfectNotify_pid: base_form.findField('EvnVizitPL_id').getValue()
				,Diag_Name: base_form.findField('Diag_id').getFieldValue('Diag_Name')
				//,Diag_id: base_form.findField('Diag_id').getValue()
				,Server_id: piPanel.getFieldValue('Server_id')
				,PersonEvn_id: piPanel.getFieldValue('PersonEvn_id')
				,MedPersonal_id: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id')
				,EvnInfectNotify_FirstTreatDate: base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y')
				,EvnInfectNotify_SetDiagDate: getGlobalOptions().date
			};
		var EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
		
		var callback = function() {
			sw4.showInfoMsg({
				type: 'info',
				text: langs('Извещение создано')
			});
			addEvnInfectNotifyTools.hide();
			if (me.listMorbus['hiv'] /*&& !me.readOnly*/) {
				checkEvnNotify({
					Evn_id: EvnVizitPL_id,
					MorbusType_SysNick: 'hiv'
				});
			}
		};
		getWnd('swEvnInfectNotifyEditWindow').show({formParams: formParams, callback: callback});
	},
	getDiagId: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		return base_form.findField('Diag_id').getValue();
	},
	loadMesCombo:function () {
		var win = this,
			base_form = this.formPanel.getForm(),
			mes_combo = base_form.findField('Mes_id');
		var uslugaComplex_id = base_form.findField('UslugaComplex_uid').getValue();
		mes_combo.setUslugaComplex_id(uslugaComplex_id);
		if (!Ext.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue())) {
			var evn_date = new Date(base_form.findField('EvnVizitPL_setDate').getValue());
			mes_combo.setEvnDate(evn_date.format('Y-m-d'));
		}
		var mes_id = mes_combo.getValue();
		mes_combo.getStore().load({
			callback:function () {
				var index = base_form.findField('Mes_id').getStore().findBy(function(rec) {
					if(rec.get('MesOldVizit_id') == mes_id){

						return true;
					} else {
						return false;
					}
				});

				if ( index >= 0 ) {
					base_form.findField('Mes_id').setValue(mes_id);
					win.setMesInUsluga();
				} else {
					base_form.findField('Mes_id').clearValue();
					base_form.findField('UslugaComplex_uid').setMesOldVizit_id(null)
				}
			}
		});

	},
	openRejectionVaccineWindow: function(sopdiag) {
		var me = this;
		var base_form = me.formPanel.getForm();

		var params = {};
		params.EvnDiag_id = sopdiag;
		params.MorbusOnko_pid = base_form.findField('EvnVizitPL_id').getValue();

		params.EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
		if (this.listMorbus['onko'] && this.listMorbus['onko']['Morbus_id']) {
			params.Morbus_id = this.listMorbus['onko']['Morbus_id'];
		} else {
			params.Morbus_id = null;
		}
		params.Person_id = base_form.findField('Person_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.allowSpecificEdit = true;
		params.action = (me.action !== 'view') ? 'edit' : 'view';
		getWnd('swExemptVaccineWindow').show(params);

	},
	openSpecificsWindow: function(type, sopdiag) {
		var me = this;
		var base_form = me.formPanel.getForm();

		var params = {};
		var wnd = null;
		switch(type) {
			case 'onko':
				params.EvnDiag_id = sopdiag;
				params.MorbusOnko_pid = base_form.findField('EvnVizitPL_id').getValue();
				wnd = 'swMorbusOnkoEditWindow';
				break;
			case 'crazy':
				params.MorbusCrazy_pid = base_form.findField('EvnVizitPL_id').getValue();
				wnd = 'swMorbusCrazyWindow';
				break;
			case 'hepa':
				params.MorbusHepatitis_pid = base_form.findField('EvnVizitPL_id').getValue();
				wnd = 'swMorbusHepatitisWindow';
				break;
			case 'tub':
				params.MorbusTub_pid = base_form.findField('EvnVizitPL_id').getValue();
				wnd = 'swMorbusTubWindow';
				break;
			case 'vener':
				params.MorbusVener_pid = base_form.findField('EvnVizitPL_id').getValue();
				wnd = 'swMorbusVenerWindow';
				break;
			case 'nephro':
				params.MorbusNephro_pid = base_form.findField('EvnVizitPL_id').getValue();
				wnd = 'swMorbusNephroWindow';
				break;
			case 'prof':
				params.MorbusProf_pid = base_form.findField('EvnVizitPL_id').getValue();
				wnd = 'swMorbusProfWindow';
				break;
		}

		if (wnd === null) {
			return false;
		}

		params.EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
		if (this.listMorbus[type] && this.listMorbus[type]['Morbus_id']) {
			params.Morbus_id = this.listMorbus[type]['Morbus_id'];
		} else {
			params.Morbus_id = null;
		}
		params.Person_id = base_form.findField('Person_id').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.allowSpecificEdit = true;
		params.action = (me.action !== 'view') ? 'edit' : 'view';
		getWnd(wnd).show(params);
	},
	checkLpuPeriodOMS: function(org_id, date, callback) {
		var me = this;
		me.mask('Проверка периода ОМС...');

		Ext6.Ajax.request({
			url: '/?c=LpuPassport&m=hasLpuPeriodOMS',
			params: {Org_oid: org_id, Date: date},
			success: function(response) {
				me.unmask();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj && response_obj.success) {
					callback(response_obj.hasLpuPeriodOMS);
				}
			},
			failure: function() {
				me.unmask();
			}
		});
	},
	finishEvnPL: function(edit) {
		let me = this;
		let base_form = me.formPanel.getForm();
		getWnd('swEvnPLFinishWindow').show({
			EvnPL_id: me.EvnPL_id,
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue(),
			PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
			lastEvnVizitPLDate: base_form.findField('EvnVizitPL_setDate').getValue(),
			edit: edit,
			callback: function () {
				me.loadEvnPLFormPanel();
			}
		});
	},
	openPLFinishWindow: function(){
		var me = this;
		var base_form = me.formPanel.getForm();

		getWnd('swEvnPLFinishWindow').show({
			EvnPL_id: me.EvnPL_id,
			Person_id: base_form.findField('Person_id').getValue(),
			Server_id: base_form.findField('Server_id').getValue(),
			PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
			lastEvnVizitPLDate: base_form.findField('EvnVizitPL_setDate').getValue(),
			callback: function () {
				me.loadEvnPLFormPanel();
			}
		});
	},
	checkEvnVizitsPL: function(closePL,newValue) {
		var me = this;
		var base_form = me.formPanel.getForm();

		var EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
		var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

		if(EvnVizitPL_id) {
			Ext6.Ajax.request({
				url: '/?c=EvnPL&m=checkEvnVizitsPL',
				params: {
					EvnVizitPL_id: EvnVizitPL_id,
					LpuSectionProfile_id: (newValue) ? newValue : LpuSectionProfile_id,
					closeAPL: (closePL == '3') ? '1' : closePL
				},
				callback: function (options, success, response) {
					var me = this;
					var base_form = me.formPanel.getForm();
					//PROMEDWEB-9824 Обработка результата не соответсвовало тому, что отдавал сервер
					//var result = Ext6.util.JSON.decode(response.responseText);
					if (success){
						if(closePL == 2)
							me.openPLFinishWindow();
					} else {
						if (closePL == 3)
							base_form.findField('LpuSectionProfile_id').clearValue();
					}
				}.createDelegate(this)
			});
		}
	},
	initComponent: function() {
		var me = this;

		this.titleLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			cls: 'no-wrap-ellipsis',
			style: 'font-size: 16px; padding: 3px 10px;',
			html: 'Случай амбулаторного лечения № ...'
		});

		this.EvnPLMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Отменить завершение случая лечения',
				itemId: 'cancelEvnPLFinish',
				handler: function() {
					me.cancelEvnPLFinish({
						EvnPL_id: me.EvnPL_id
					});
				}
			}, {
				text: 'Удалить случай АПЛ',
				itemId: 'deleteEvnPL',
				handler: function() {
					me.ownerWin.deleteEvnPL({
						EvnPL_id: me.EvnPL_id
					});
				}
			}]
		});

		this.swEMDPanel = Ext6.create('sw.frames.EMD.swEMDPanel');

		this.toolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			height: 40,
			border: false,
			noWrap: true,
			right: 0,
			style: 'background: transparent;',
			items: [{
				xtype: 'tbspacer',
				width: 10
			}, me.swEMDPanel, {
				userCls: 'button-without-frame',
				style: {
					'color': 'transparent'
				},
				iconCls: 'panicon-print',
				tooltip: langs('Печать'),
				menu: new Ext6.menu.Menu({
					userCls: 'menuWithoutIcons',
					items: [{
						text: 'Печать случая АПЛ',
						handler: function () {
							printEvnPL({
								type: 'EvnPL',
								EvnPL_id: me.EvnPL_id
							});
						}
					}, {
						text: 'Печать выписки из мед.карты',
						handler: function() {
							if (getRegionNick() == 'kz') {
								printBirt({
									'Report_FileName': 'f027u_Evn.rptdesign',
									'Report_Params': '&paramEvn=' + me.EvnPL_id,
									'Report_Format': 'pdf'
								});
							} else {
								printBirt({
									'Report_FileName': 'f027u_EvnPL.rptdesign',
									'Report_Params': '&paramEvnPL=' + me.EvnPL_id,
									'Report_Format': 'doc'
								});
							}
						}
					}, {
						text: 'Справка о стоимости лечения',
						hidden: !getRegionNick().inlist(['kz', 'perm', 'ufa']),
						handler: function(){
							sw.Promed.CostPrint.print({
								Evn_id: me.EvnPL_id,
								type: 'EvnPL'
							});
						}
					}, {
						text: 'Печать КЛУ при ЗНО',
						itemId: 'printEvnPLKLU_ZNO',
						handler: function () {
							var base_form = me.formPanel.getForm(),
								EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
							printControlCardZno(EvnVizitPL_id);
						}
					}]
				})
			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-flag',
				refId: 'finishEvnPLButton',
				tooltip: langs('Завершить случай лечения'),
				handler: function () {
					var base_form = me.formPanel.getForm();

					if(getRegionNick()=='vologda'){
						me.checkEvnVizitsPL(2); // 2 - акрытие случая АПЛ
						return false;
					}

					getWnd('swEvnPLFinishWindow').show({
						EvnPL_id: me.EvnPL_id,
						Person_id: base_form.findField('Person_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
						lastEvnVizitPLDate: base_form.findField('EvnVizitPL_setDate').getValue(),
						callback: function () {
							me.loadEvnPLFormPanel();
						}
					});
				}
			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-theedots',
				tooltip: langs('Меню'),
				handler: function () {
					me.EvnPLMenu.showBy(this);
				}
			}]
		});

		this.titlePanel = Ext6.create('Ext6.Panel', {
			region: 'north',
			style: {
				'box-shadow': '0px 1px 6px 2px #ccc',
				zIndex: 2
			},
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #EEEEEE;',
			items: [{
				region: 'center',
				border: false,
				bodyStyle: 'background-color: #EEEEEE;',
				height: 40,
				bodyPadding: 10,
				items: [
					this.titleLabel
				]
			}, this.toolPanel
			],
			xtype: 'panel'
		});

		this.tabPanel = Ext6.create('Ext6.TabPanel', {
			border: false,
			defaults: {
				tabConfig: {
					margin: 0,
					cls: 'evn-pl-tab-panel-items'
				}
			},
			items: [{
				title: LOADING_MSG,
				border: false,
				html: '',
				EvnVizitPL_id: null
			}],
			listeners: {
				'tabchange': function(tabPanel, newCard) {
					// грузим данные по конркетному посещению
					if (newCard.EvnVizitPL_id) {
						var base_form = me.formPanel.getForm();
						me.mask(LOADING_MSG);
						me.resetEvnVizitPL();
						me.formPanel.loadingData = true;
						if (me.evnVizitPLDataToLoad) {
							base_form.setValues(me.evnVizitPLDataToLoad[0]);
							me.onLoadEvnVizitPLFormPanel(me.evnVizitPLDataToLoad);
							me.formPanel.loadingData = false;
						} else {
							base_form.load({
								params: {
									EvnVizitPL_id: newCard.EvnVizitPL_id
								},
								success: function(form, action) {
									// good
									if (action.response && action.response.responseText) {
										var data = Ext6.JSON.decode(action.response.responseText);
										me.onLoadEvnVizitPLFormPanel(data);
									}
									me.formPanel.loadingData = false;
								},
								failure: function(form, action) {
									// not good
									me.formPanel.loadingData = false;
								}
							});
						}
					}
				}
			}
		});

		this.addEvnVizitPLBtn = Ext6.create('Ext6.button.Button',{
			text: 'Добавить посещение',
			refId: 'addEvnVizitPL',
			handler: function() {
				me.ownerWin.addEvnVizitPL({
					EvnPL_id: me.EvnPL_id
				});
			}
		});

		this.tabToolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			width: 200,
			height: 40,
			border: false,
			margin: "0 10px 0 0",
			items: ['->', me.addEvnVizitPLBtn]
		});

		this.TabContainer = Ext6.create('Ext6.Panel', {
			region: 'north',
			layout: 'border',
			border: false,
			height: 50,
			cls: 'topRadius leftPadding emk-top-panel',
			items: [{
				region: 'center',
				border: false,
				height: 40,
				items: [
					this.tabPanel
				]
			}, this.tabToolPanel, {
				region: 'south',
				bodyStyle: 'background-color: #EEEEEE; border-width: 0px 1px 0px 1px; -webkit-box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2); -moz-box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2); box-shadow: inset 0px 7px 7px -5px rgba(0,0,0,0.2);',
				height: 10,
				html: ''
			}],
			xtype: 'panel'
		});

		this.EvnVizitPLMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Обработать данные в системе принятия решений',
				itemId: 'openDSSWindow',
                hidden: !( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ),
				handler: function() {
                    const base_form = me.formPanel.getForm();
                    const EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();

                    if (!base_form.dssGlobal) {
                        base_form.dssGlobal= {};
                    }
                    Ext6.require('common.DSS.Doctor.DSSDoctorWindow', function() {
                        if (!Ext6.getCmp('DSSForm')) {
                            base_form.dssGlobal.dss = new common.DSS.Doctor.DSSDoctorWindow();
                        } else {
                            const f = Ext6.getCmp('DSSForm');
                            const owner = f.ownerCt;
                            owner.remove(f);
                            base_form.dssGlobal.dss = new common.DSS.Doctor.DSSDoctorWindow();
                        }

                        base_form.dssGlobal.dss.show(me.params.Person_id, EvnVizitPL_id);
                    });
				}
			},{
				text: 'Скопировать в новый случай',
				itemId: 'copyEvnVizitPL',
				handler: function() {
					var base_form = me.formPanel.getForm();
					var EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
					if (!Ext6.isEmpty(EvnVizitPL_id)) {
						me.ownerWin.copyEvnVizitPL({
							EvnVizitPL_id: EvnVizitPL_id
						});
					}
				}
			},{
				text: 'Медотвод/Отказ от вакцинации',
				itemId: 'addRejectionVaccine',
				handler: function() {
					me.openRejectionVaccineWindow(this.EvnDiag_id);
				}
			},{
				text: 'Удалить посещение',
				itemId: 'deleteEvnVizitPL',
				handler: function() {
					var base_form = me.formPanel.getForm();
					var EvnVizitPL_id = base_form.findField('EvnVizitPL_id').getValue();
					if (!Ext6.isEmpty(EvnVizitPL_id)) {
						me.ownerWin.checkOnOnlyOneVizitExist({
							callback: function (id) {
								me.ownerWin.deleteEvnVizitPL({
									EvnVizitPL_id: id
								});
							},
							id: EvnVizitPL_id
						});
					}
				}
			}]
		});

		this.formPanel = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			padding: "18 0 30 27",
			userCls:'vizitPanelEmk',
			url: '/?c=EMK&m=loadEvnVizitPLForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'EvnVizitPL_id'},
						{name: 'accessType'},
						{name: 'EvnVizitPL_setDate'},
						{name: 'EvnVizitPL_setTime'},
						{name: 'MedStaffFact_id'},
						{name: 'isKardio'},
						{name: 'MedPersonal_sid'},
						{name: 'TreatmentClass_id'},
						{name: 'ServiceType_id'},
						{name: 'VizitClass_id'},
						{name: 'VizitType_id'},
						{name: 'MedicalCareKind_id'},
						{name: 'UslugaComplex_uid'},
						{name: 'LpuSection_id'},
						{name: 'LpuSectionProfile_id'},
						{name: 'PayType_id'},
						{name: 'Diag_id'},
						{name: 'DeseaseType_id'},
						{name: 'EvnClass_id'},
						{name: 'Person_id'},
						{name: 'Server_id'},
						{name: 'PersonEvn_id'},
						{name: 'PainIntensity_id'},
						{name: 'TumorStage_id'},
						{name: 'EvnVizitPL_IsZNO'},
						{name: 'Diag_spid'},
						{name: 'DispProfGoalType_id'},
						{name: 'HealthKind_id'},
						{name: 'Mes_id'}
					]
				})
			}),
			layout: 'anchor',
			bodyPadding: 10,
			border: false,
			defaults: {
				anchor: '100%',
				width: 615,
				maxWidth: 615 + 147,
				labelWidth: 172,
				listeners: {
					'select': function (combo, record, eOpts) {
						me.saveEvnVizitPL();
					}
				}
			},
			items: [{
				name: 'EvnVizitPL_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				layout: 'column',
				border: false,
				style: 'margin-bottom: 5px;',
				defaults: {
					border: false,
					labelWidth: 172,
					listeners: {
						'change': function () {
								me.saveEvnVizitPL();
						}
					}
				},
				items: [{
					xtype: 'swDateField',
					height:27,
					width: 295,
					anchor:'100%',
					allowBlank: false,
					inputCls: 'date_time_priem',
					format: 'd.m.Y',
					startDay: 1,
					fieldLabel: 'Дата/время приема',
					style: 'margin-right: 10px;',
					name: 'EvnVizitPL_setDate',
					maxValue: new Date(),
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = me.formPanel.getForm();
							base_form.findField('DeseaseType_id').getStore().clearFilter();
							base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
								return (
									(!rec.get('DeseaseType_begDT') || rec.get('DeseaseType_begDT') <= newValue)
									&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= newValue)
								)
							});
							base_form.findField('DeseaseType_id').lastQuery = '';

							me.setVizitTypeFilter();
							me.filterMedStaffFactCombo();
						}
					}
				}, {
					xtype: 'swTimeField',
					allowBlank: false,
					width: 100,
					userCls:'vizit-time',
					hideLabel: true,
					name: 'EvnVizitPL_setTime'
				}]
			}, {
				allowBlank: false,
				fieldLabel: 'Отделение',
				comboSubject: 'LpuSection',
				queryMode: 'local',
				name: 'LpuSection_id',
				itemId: 'EvnPL_LpuSectionCombo',
				tabIndex: 2603,
				xtype: 'SwLpuSectionGlobalCombo'
			}, {
				xtype: 'swMedStaffFactCombo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.FedMedSpec_id = base_form.findField('MedStaffFact_id').getFieldValue('FedMedSpec_id');
						if (getRegionNick().inlist(['ekb', 'kz'])) {
							base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.LpuSection_id = base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id');
						}
						base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';

						me.loadLpuSectionProfileDop();
					},
					'select': function (combo, record, eOpts) {
						//var base_form = me.formPanel.getForm();
						//base_form.findField('UslugaComplex_uid').clearValue();
						me.loadUslugaComplex();
						me.saveEvnVizitPL();
					}
				},
				fieldLabel: 'Врач',
				allowBlank: false,
				name: 'MedStaffFact_id'
			}, {
				xtype: 'swMedStaffFactCombo',
				fieldLabel: 'Сред. мед. перс',
				name: 'MedStaffFact_sid'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'TreatmentClass',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.TreatmentClass_id = newValue;
						base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
						
						if (getRegionNick()=='kz' && newValue) {
							var index = swTreatmentClassServiceTypeGlobalStore.findBy(function(rec) {
								return rec.get('TreatmentClass_id') == newValue;
							});
							
							var serviceTypeId = swTreatmentClassServiceTypeGlobalStore.getAt(index).get('ServiceType_id');
							
							base_form.findField('ServiceType_id').setValue(serviceTypeId);
							
							if (newValue.inlist([22,30])) {
								var vizitActiveTypeId = base_form.findField('VizitActiveType_id').getValue();
								
								base_form.findField('VizitActiveType_id').getStore().clearFilter();
								base_form.findField('VizitActiveType_id').getStore().filterBy(function(rec){
									return rec.get('TreatmentClass_id') == newValue;
								});
								
								index = base_form.findField('VizitActiveType_id').getStore().findBy(function(rec) {
									return rec.get('VizitActiveType_id') == vizitActiveTypeId;
								});
								
								if (index == -1) {
									vizitActiveTypeId = (newValue == 22)?3:8;
									base_form.findField('VizitActiveType_id').setValue(vizitActiveTypeId);
								}
								
								base_form.findField('VizitActiveType_id').setAllowBlank(false);
								base_form.findField('VizitActiveType_id').show();
							} else {
								base_form.findField('VizitActiveType_id').clearValue();
								base_form.findField('VizitActiveType_id').getStore().clearFilter();
								base_form.findField('VizitActiveType_id').setAllowBlank(true);
								base_form.findField('VizitActiveType_id').hide();
							}
						} else {
							base_form.findField('VizitActiveType_id').clearValue();
							base_form.findField('VizitActiveType_id').getStore().clearFilter();
							base_form.findField('VizitActiveType_id').setAllowBlank(true);
							base_form.findField('VizitActiveType_id').hide();
						}
						
						me.setVizitTypeFilter();
					},
					'select': function (combo, record, eOpts) {
						//var base_form = me.formPanel.getForm();
						//base_form.findField('UslugaComplex_uid').clearValue();
						me.loadUslugaComplex();
					}
				},
				fieldLabel: 'Вид обращения',
				allowBlank: ( getRegionNick() == 'kareliya' ),
				hidden: ( getRegionNick() == 'kareliya' ),
				name: 'TreatmentClass_id'
			}, {
				comboSubject: 'VizitActiveType',
				fieldLabel: 'Вид активного посещения',
				name: 'VizitActiveType_id',
				moreFields: [
					{ name: 'TreatmentClass_id', mapping: 'TreatmentClass_id' }
				],
				hidden: getRegionNick()=='kz',
				allowBlank: true,
				xtype: 'commonSprCombo'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'ServiceType',
				fieldLabel: 'Место',
				allowBlank: false,
				name: 'ServiceType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'VizitClass',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.VizitClass_id = newValue;
						base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
					},
					'select': function (combo, record, eOpts) {
						//var base_form = me.formPanel.getForm();
						//base_form.findField('UslugaComplex_uid').clearValue();
						me.loadUslugaComplex();
						me.saveEvnVizitPL();
					}
				},
				fieldLabel: 'Прием',
				displayCode: false,
				name: 'VizitClass_id'
			}, {
				allowBlank: false,
				xtype: 'swVizitTypeCombo',
				comboSubject: 'VizitType',
				EvnClass_id: 11,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.VizitType_id = newValue;
						base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
						
						var prof_goal_combo = base_form.findField('ProfGoal_id');

						if ( newValue != null && newValue.toString().length > 0 ) {
							var record = combo.getStore().getById(newValue);

							if ( record ) {
								if ( record.get('VizitType_SysNick') == 'prof' ) {
									prof_goal_combo.enable();
								}
								else {
									prof_goal_combo.disable();
									prof_goal_combo.clearValue();
								}
							}
						} else {
							prof_goal_combo.disable();
							prof_goal_combo.clearValue();
						}
					},
					'select': function (combo, record, eOpts) {
						//var base_form = me.formPanel.getForm();
						//base_form.findField('UslugaComplex_uid').clearValue();
						me.loadUslugaComplex();
						me.saveEvnVizitPL();
					}
				},
				fieldLabel: 'Цель посещения',
				name: 'VizitType_id'
			}, {
				allowBlank: true,
				hidden: !( getGlobalOptions().region && getGlobalOptions().region.nick == 'ekb' ),
				fieldLabel: langs('МЭС'),
				hiddenName: 'Mes_id',
				name: 'Mes_id',
				xtype: 'SwMesOldVizitCombo'
			}, {
				xtype: 'commonSprCombo',
				allowBlank: false,
				comboSubject: 'MedicalCareKind',
				sortField: 'MedicalCareKind_Code',
				typeCode: 'int',
				hideEmptyRow: true,
				additionalRecord: false,
				prefix: 'nsi_',
				fieldLabel: 'Вид мед. помощи',
				disabled: getRegionNick().inlist([ 'ufa', 'kareliya', 'ekb' ]),
				name: 'MedicalCareKind_id'
			}, {
				xtype: 'swUslugaComplexCombo',
				listConfig: {
					cls: 'choose-bound-list-menu'
				},
				fieldLabel: 'Код посещения',
				allowBlank: sw.Promed.EvnVizitPL.isAllowBlankVizitCode(),
				hidden: !sw.Promed.EvnVizitPL.isSupportVizitCode(),
				to: 'EvnVizitPL',
				name: 'UslugaComplex_uid',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
							var base_form = me.formPanel.getForm();
							var PLsetDate = base_form.findField('EvnVizitPL_setDate').getValue();
							var selected_record = combo.getSelectedRecord();

							//добавлено в контексте задачи #181439
							if (selected_record == null) {return;}

							var usluga_complex_code = selected_record.get('UslugaComplex_Code');
							// https://redmine.swan.perm.ru/issues/31548
							// https://redmine.swan.perm.ru/issues/32218
							if (
								Date.parseDate(Ext.util.Format.date(PLsetDate), 'd.m.Y') >= Date.parseDate('01.07.2013', 'd.m.Y')
								&& !Ext.isEmpty(usluga_complex_code)
								&& (
									usluga_complex_code.substr(-5, 5).inlist([
										//'66805', '00805', '31805', '57805', '71805', '67805', '68805', '69805',
										// Добавил коды
										// @task https://redmine.swan.perm.ru/issues/65411
										'31890', '57890', '71890', '66890', '00890', '67890', '68890', '69890',
										// Добавил коды
										// @task https://redmine.swan.perm.ru/issues/83983
										'69893', '67893', '73893',
										// Заменил 573805 на %73805
										// @task https://redmine.swan.perm.ru/issues/84992
										'73805'
									]) ||
									usluga_complex_code.substr(-3, 3).inlist(['805', '893'])
								)
							) {
								if (me.action != 'view') {
									base_form.findField('HealthKind_id').setVisible(true);
								}
							} else {
								base_form.findField('HealthKind_id').clearValue();
								base_form.findField('HealthKind_id').setVisible(false);
							}

							if (
								me.action != 'view' &&
								!Ext.isEmpty(usluga_complex_code) &&
								usluga_complex_code.substr(-3, 3).inlist(['805', '893']) &&
								Date.parseDate(Ext.util.Format.date(PLsetDate), 'd.m.Y') >= Date.parseDate('01.11.2016', 'd.m.Y')
							) {
								base_form.findField('HealthKind_id').setAllowBlank(false);
							} else {
								base_form.findField('HealthKind_id').setAllowBlank(true);
							}

							if (
								!Ext.isEmpty(usluga_complex_code) && (usluga_complex_code.substr(-3, 3).inlist(['805', '893']))
							) {
								base_form.findField('DispProfGoalType_id').setAllowBlank(false);
							} else {
								base_form.findField('DispProfGoalType_id').setAllowBlank(true);
							}
							
							if ( !Ext.isEmpty(usluga_complex_code) && isProphylaxisVizitOnly(usluga_complex_code) ) 
							{
								sw4.showInfoMsg({
									panel: me,
									type: 'info',
									text: 'Для профилактического/консультативного посещения должен быть указан признак окончания случая лечения и результат лечения'
								});
							}
						}
						// https://redmine.swan.perm.ru/issues/15258
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ekb' ) {
							if(
								newValue == '4568436'
								&& base_form.findField('Diag_id').getStore().getById(base_form.findField('Diag_id').getValue()).data.DiagFinance_IsOms != '1'
								&& base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue()).data.PayType_SysNick == 'bud'
							){
								var textMsg = langs('Услуга В01.069.998 может быть выбрана только при диагнозе, оплачиваемом по ОМС');
								sw.swMsg.alert(langs('Ошибка'), textMsg, function() {
									this.formStatus = 'edit';
									base_form.findField('UslugaComplex_uid').clearValue();
									base_form.findField('UslugaComplex_uid').markInvalid(textMsg);
									base_form.findField('UslugaComplex_uid').focus(true);
								}.createDelegate(this));
								return false;
							}
							me.loadMesCombo();
						}
					},
					'select': function(){
						me.saveEvnVizitPL();
					}
				}
			}, {
				xtype: 'commonSprCombo',
				hidden: !( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ),
				comboSubject: 'DispProfGoalType',
				fieldLabel: 'В рамках дисп./мед.осмотра',
				displayCode: false,
				name: 'DispProfGoalType_id',
				moreFields: [{name: 'DispProfGoalType_IsVisible', type: 'int'}],
				filterFn: function(rec){
					return (rec.get('DispProfGoalType_IsVisible') && rec.get('DispProfGoalType_IsVisible') == 2);
				},

			}, {
				xtype: 'swLpuSectionProfileDopRemoteCombo',
				allowBlank: getRegionNick().inlist(['ufa', 'ekb']),
				comboSubject: 'LpuSectionProfile',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();
						if (getRegionNick() == 'ufa') {
							if (newValue) {
								base_form.findField('UslugaComplex_uid').setLpuLevelCode(combo.getFieldValue('LpuSectionProfile_Code'));
							} else {
								base_form.findField('UslugaComplex_uid').setLpuLevelCode(0);
							}
						} else if (getRegionNick().inlist(['buryatiya', 'perm'])) {
							base_form.findField('UslugaComplex_uid').setLpuSectionProfile_id(newValue);
							base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
						} else if(getRegionNick()=='vologda'){
							me.checkEvnVizitsPL(1,newValue);
						}
					},
					'select': function (combo, record, eOpts) {
						//var base_form = me.formPanel.getForm();
						//base_form.findField('UslugaComplex_uid').clearValue();
						me.loadUslugaComplex();
						me.saveEvnVizitPL();
					}
				},
				fieldLabel: 'Профиль',
				name: 'LpuSectionProfile_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'PayType',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.PayType_id = newValue;
						base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
						var pay_type = combo.getStore().getById(newValue);
						var pay_type_nick = (pay_type && pay_type.get('PayType_SysNick')) || '';
						if ( getRegionNick().inlist([ 'ekb' ]) ) {
							base_form.findField('Mes_id').getStore().removeAll();
							base_form.findField('Mes_id').setMesType_id(('bud' == pay_type_nick || 'fbud' == pay_type_nick) ? 8 : 'oms' == pay_type_nick ? 0 : null);
							base_form.findField('Mes_id').setUslugaComplexPartitionCodeList(('bud' == pay_type_nick || 'fbud' == pay_type_nick) ? [350,351] : null);
							me.loadMesCombo();
						}
						me.setVizitTypeFilter();
						
						if ( getRegionNick().inlist(['ufa']) ) {
							if (newValue == 9) {
								base_form.findField('UslugaComplex_uid').setAllowBlank(false)
							} else {
								base_form.findField('UslugaComplex_uid').setAllowBlank(true);
							}
						}
					},
					'select': function (combo, record, eOpts) {
						//var base_form = me.formPanel.getForm();
						//base_form.findField('UslugaComplex_uid').clearValue();
						me.loadUslugaComplex();
						me.saveEvnVizitPL();
					}
				},
				fieldLabel: 'Вид оплаты',
				displayCode: false,
				name: 'PayType_id'
			}, {
				name: 'ProfGoal_id',
				xtype: 'commonSprCombo',
				comboSubject: 'ProfGoal',
				fieldLabel: 'Цель профосмотра'
			}, {
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield',
				name: 'PregnancyEvnVizitPL_Period',
				fieldLabel: 'Срок беременности, недель',
				minValue: 1,
				maxValue: 45,
				width: 100,
				hidden: !getRegionNick().inlist(['ufa']),
				listeners: {
					'change': function () {
						me.saveEvnVizitPL();
					}
				}
			}, {
				layout: 'column',
				border: false,
				width: 2000,
				maxWidth: 2000,
				items: [{
				xtype: 'swDiagCombo',
				userCls: 'diagnoz',
				allowBlank: !getRegionNick().inlist(['pskov', 'ufa', 'ekb', 'vologda']),
					labelWidth: 172,
					width: 615 + 147,
					anchor: '100%',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var rec = {
							'Diag_Code': combo.getFieldValue('Diag_Code'),
							'Diag_Name': combo.getFieldValue('Diag_Name')
						};
						if (combo.getSelectedRecord())
							rec = combo.getSelectedRecord().getData();
						//me.EvnPrescrPanel.getController().checkDiag(rec);
						var base_form = me.formPanel.getForm(),
							values = base_form.getValues(),
							record = combo.getSelectedRecord(),
							EvnDiagPLStom_setDate = base_form.findField('EvnVizitPL_setDate').getValue(),
							TumorStage = base_form.findField('TumorStage_id'),
							PainIntensity = base_form.findField('PainIntensity_id'),
							printEvnPLKLU_ZNO = me.toolPanel.down('[itemId=printEvnPLKLU_ZNO]'),
							dateX20181101 = new Date(2018, 10, 1), // 01.11.2018 перенес со старого интерфейса
							dateX20170901 = new Date(2017, 8, 1), // 01.09.2017 согласно ТЗ
							dateX20180601 = new Date(2018, 5, 1); // 01.06.2018 согласно ТЗ

						if (!me.formPanel.loadingData) {
							me.ownerWin.setTreeDataDiag('EvnPL', me.EvnPL_id, values, rec);
						}

						if (
							record
							&& !Ext6.isEmpty(record.get('Diag_Code'))
							&& (
								(record.get('Diag_Code').slice(0, 3)>= 'C00' && record.get('Diag_Code').slice(0, 3) <= 'C97')
								|| (record.get('Diag_Code').slice(0, 3) >= 'D00' && record.get('Diag_Code').slice(0, 3) <= 'D09')
							)
						) {
							if (
								getRegionNick() == 'penza'
								&& !Ext.isEmpty(EvnDiagPLStom_setDate)
								&& EvnDiagPLStom_setDate >= dateX20181101
							) {
								PainIntensity.setHidden(false);
								PainIntensity.setAllowBlank(false);
								if (Ext.isEmpty(PainIntensity.getValue()) && PainIntensity.isVisible()) {
									PainIntensity.setValue(1);
								}
							} else {
								PainIntensity.setHidden(true);
								PainIntensity.setAllowBlank(true);
							}
							if (getRegionNick() == 'ekb') printEvnPLKLU_ZNO.setText('Печать выписки при онкологии');
							
							if (
								((getRegionNick() == 'ekb') && EvnDiagPLStom_setDate < dateX20180601) ||
								((getRegionNick() == 'kareliya') && EvnDiagPLStom_setDate >= dateX20170901)
							) {
								TumorStage.show();
								me.TumorStageComboEdit();
							} else {
								TumorStage.hide();
								TumorStage.setAllowBlank(true);
							}
							
							printEvnPLKLU_ZNO.show();
						} else {
							printEvnPLKLU_ZNO.hide();
							TumorStage.hide();
							TumorStage.setAllowBlank(true);
							PainIntensity.setHidden(true);
							PainIntensity.setAllowBlank(true);
						}

						if (record && !Ext6.isEmpty(record.get('Diag_Code')) && record.get('Diag_Code').substr(0, 1).toUpperCase() != 'Z') {
							base_form.findField('DeseaseType_id').setAllowBlank(false);
						} else {
							base_form.findField('DeseaseType_id').setAllowBlank(true);
						}

						if (getRegionNick() == 'ufa') {
							base_form.findField('EvnVizitPL_IsZNO').setValue(combo.getFieldValue('Diag_Code') == 'Z03.1' ? 2 : 1);
							base_form.findField('EvnVizitPL_IsZNO').disable();
						} else {
							if (getRegionNick() != 'krym' && record && !Ext6.isEmpty(record.get('Diag_Code')) && record.get('Diag_Code').search(new RegExp("^(C|D0)", "i")) >= 0) {
								base_form.findField('EvnVizitPL_IsZNO').setValue(1);
								base_form.findField('EvnVizitPL_IsZNO').disable();
							} else {
								base_form.findField('EvnVizitPL_IsZNO').enable();

								if (getRegionNick() == 'buryatiya') {
									base_form.findField('EvnVizitPL_IsZNO').setValue(combo.getFieldValue('Diag_Code') == 'Z03.1' ? 2 : 1);
								}
							}
						}

						if (getRegionNick() != 'ufa') {
							base_form.findField('EvnVizitPL_IsZNO').setDisabled(me.formPanel.accessType == 'view');
						}

						me.filterTreatmentDiag();
						me.checkSpecifics();
							me.checkMesOldUslugaComplexFields();
					},
						'select': function(combo, record, eOpts) {
						me.saveEvnVizitPL();
					}
				},
				fieldLabel: 'Основной диагноз',
				name: 'Diag_id'
			}, {
					xtype: 'button',
					text: '',
					hidden: true,
					itemId: 'InfectNotifyButton',
					cls: 'InfectNotifyButton button-without-frame',
					iconCls: 'notify16-2017',
					style: 'margin-left: 32px; margin-top: 2px;',
					tooltip: 'Добавить экстренное извещение об инфекционном заболевании, отравлении',
					handler: function() {
						me.addEvnInfectNotify();
					}
				}]
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'DeseaseType',
				fieldLabel: 'Характер заболевания',
				displayCode: true,
				moreFields: [
					{name: 'DeseaseType_begDT', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'DeseaseType_endDT', type: 'date', dateFormat: 'd.m.Y'}
				],
				name: 'DeseaseType_id',
				listeners: {
					'change': function (combo, newValue, oldValue) {
						me.TumorStageComboEdit();
					},
					'select': function(combo, record, eOpts) {
						me.saveEvnVizitPL();
					}
				}
			}, {
				comboSubject: 'PainIntensity',
				fieldLabel: 'Интенсивность боли',
				name: 'PainIntensity_id',
				hidden: true,
				displayCode: true,
				xtype: 'commonSprCombo'
			}, {
				comboSubject: 'TumorStage',
				fieldLabel: 'Стадия выявленного ЗНО',
				name: 'TumorStage_id',
				hidden: true,
				displayCode: true,
				xtype: 'commonSprCombo',
				filterFn: function(rec){
					return (rec && rec.get('TumorStage_Code').inlist(['0', '1', '2', '3', '4']));
				},
				listeners: {
					'select': function(combo, record, eOpts) {
						me.saveEvnVizitPL();
					}
				}
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				fieldLabel: 'Подозрение на ЗНО',
				displayCode: false,
				hidden: getRegionNick() == 'kz',
				name: 'EvnVizitPL_IsZNO',
				listeners: {
					'change': function (combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm(),
							Diag_spid = base_form.findField('Diag_spid'),
							record = combo.getSelectedRecord();

						if (
							getRegionNick() != 'kz'
							&& record
							&& !Ext6.isEmpty(record.get('YesNo_Code'))
							&& record.get('YesNo_Code') == '1'
						) {
							Diag_spid.setHidden(false);
							Diag_spid.setAllowBlank(false);
						} else {
							Diag_spid.setHidden(true);
							Diag_spid.setAllowBlank(true);
						}
					}
				}
			}, {
				xtype: 'swDiagCombo',
				userCls: 'diagnoz',
				fieldLabel: 'Подозрение на диагноз',
				name: 'Diag_spid',
				additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
				baseFilterFn: function(rec){
					if(typeof rec.get == 'function') {
						return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
					} else if (rec.attributes && rec.attributes.Diag_Code) {
						return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
					} else {
						return true;
					}
				}
			}, {
				fieldLabel: langs('Группа здоровья'),
				hiddenName: 'HealthKind_id',
				name: 'HealthKind_id',
				hidden: true,
				xtype: 'swHealthKindCombo'
			}]
		});

		this.EvnVizitPLPanel = Ext6.create('swPanel', {
			userCls: 'panel-with-tree-dots accordion-panel-window',
			title: 'ПОСЕЩЕНИЕ',
			collapseOnOnlyTitle: true,
			threeDotMenu: me.EvnVizitPLMenu,
			items: [
				me.formPanel
			]
		});

		this.SpecificsHepatitisPanel = Ext6.create('swSpecificPanel', {
			specificTitle: 'ГЕПАТИТ',
			hidden: true,
			handler: function() {
				me.openSpecificsWindow('hepa');
			}
		});
		this.SpecificsPregnancyPanel = Ext6.create('swSpecificPanel', {
			specificTitle: 'БЕРЕМЕННОСТЬ',
			hidden: true,
			handler: function() {
				var base_form = me.formPanel.getForm();

				var params = {
					Evn_id: base_form.findField('EvnVizitPL_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue(),
					Lpu_id: base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id'),
					LpuSection_id: base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id'),
					MedPersonal_id: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
					MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
					date: (!Ext6.isEmpty(base_form.findField('EvnVizitPL_setDate').getValue()) && typeof base_form.findField('EvnVizitPL_setDate').getValue() == 'object') ? base_form.findField('EvnVizitPL_setDate').getValue().format('d.m.Y') : getGlobalOptions().date
				};

				me.mask(LOADING_MSG);
				Ext6.Ajax.request({
					url: '/?c=PersonPregnancy&m=getPersonRegisterByEvnVizitPL',
					params: params,
					callback: function(options, success, response) {
						me.unmask();
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.success) {
							params.PersonRegister_id = response_obj.PersonRegister_id || null;
							params.PersonDisp_id = response_obj.PersonDisp_id || null;
							params.userMedStafffact = me.ownerWin.userMedStafffact;
							params.action = params.PersonRegister_id ? 'edit' : 'add';

							getWnd('swPersonPregnancyEditWindow').show(params);
						}
					}.createDelegate(this)
				});
			}
		});
		this.SpecificsCrazyPanel = Ext6.create('swSpecificPanel', {
			specificTitle: 'ПСИХИАТРИЯ/НАРКОЛОГИЯ',
			hidden: true,
			handler: function() {
				me.openSpecificsWindow('crazy');
			}
		});
		this.SpecificsTubPanel = Ext6.create('swSpecificPanel', {
			specificTitle: 'ТУБЕРКУЛЕЗ',
			hidden: true,
			handler: function() {
				me.openSpecificsWindow('tub');
			}
		});
		this.SpecificsVenerPanel = Ext6.create('swSpecificPanel', {
			specificTitle: 'ВЕНЕРОЛОГИЯ',
			hidden: true,
			handler: function() {
				me.openSpecificsWindow('vener');
			}
		});
		this.SpecificsNephroPanel = Ext6.create('swSpecificPanel', {
			specificTitle: 'НЕФРОЛОГИЯ',
			hidden: true,
			handler: function() {
				me.openSpecificsWindow('nephro');
			}
		});
		this.SpecificsProfPanel = Ext6.create('swSpecificPanel', {
			specificTitle: 'ПРОФЗАБОЛЕВАНИЯ',
			hidden: true,
			handler: function() {
				me.openSpecificsWindow('prof');
			}
		});
		this.SpecificsPalliatPanel = Ext6.create('swSpecificPanel', {
			specificTitle: 'ПАЛЛИАТИВНАЯ ПОМОЩЬ',
			hidden: true,
			handler: function() {
				var base_form = me.formPanel.getForm();

				me.mask(LOADING_MSG);
				Ext6.Ajax.request({
					url: '/?c=MorbusPalliat&m=getIdForEmk',
					params: {
						Person_id: base_form.findField('Person_id').getValue()
					},
					success: function(response) {
						me.unmask();
						var result = Ext6.JSON.decode(response.responseText);
						if (result.MorbusPalliat_id) {
							getWnd('swMorbusPalliatEditWindowExt6').show({
								action: me.formPanel.accessType,
								MorbusPalliat_id: result.MorbusPalliat_id,
								Person_id: base_form.findField('Person_id').getValue(),
								Evn_id: base_form.findField('EvnVizitPL_id').getValue()
							});
						} else {
							Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось найти запись в регистре по паллиативной помощи'));
						}
					}
				});
			}
		});
		this.SpecificsGeriatrics = Ext6.create('swSpecificPanel', {
			specificTitle: 'ГЕРИАТРИЯ',
			hidden: true,
			handler: function() {
				var base_form = me.formPanel.getForm();

				me.mask(LOADING_MSG);
				Ext.Ajax.request({
					url: '/?c=MorbusGeriatrics&m=getIdForEmk',
					params: {
						Person_id: base_form.findField('Person_id').getValue()
					},
					success: function(response) {
						var result = Ext.util.JSON.decode(response.responseText);

						if (result.MorbusGeriatrics_id) {
							getWnd('swMorbusGeriatricsEditWindow').show({
								action: 'edit',
								MorbusGeriatrics_id: result.MorbusGeriatrics_id,
								Person_id: base_form.findField('Person_id').getValue()
							});
						} else {
							Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось найти запись в регистре по гериатрии'));
						}
					}
				});
			}
		});
		this.ProtocolPanel = Ext6.create('common.EvnXml.ItemsPanel', {
			title: 'ОСМОТР',
			userCls: 'accordion-panel-window accordion-panel-protocol',
			maxCount: 1,
			allowT9: (getRegionNick()=='vologda') //кнопка Т9 в редакторе осмотра. Также контролируется в настройках
		});
		this.EvnPrescrPanel = Ext6.create('common.EMK.EvnPrescribePanel', {
			ownerPanel: me,
			userCls: 'accordion-panel-window accordion-panel-prescr'
		});
		/*this.EvnDirectionPanel = Ext6.create('common.EMK.EvnDirectionPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-with-dropdown-menu'
		});*/
		this.EvnDrugPanel = Ext6.create('common.EMK.EvnDrugPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-drug'
		});
		this.EvnPLDispScreenOnko = Ext6.create('common.EMK.EvnPLDispScreenOnko', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-drug'
		});
		this.RepositoryObservPanel = Ext6.create('common.EMK.RepositoryObservPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-drug'
		});
		this.DrugTherapySchemePanel = Ext6.create('common.EMK.DrugTherapySchemePanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-drug'
		});
		this.EvnUslugaPanel = Ext6.create('common.EMK.EvnUslugaPanel', { userCls: 'accordion-panel-window accordion-panel-with-dropdown-menu'});
		this.EvnReceptPanel = Ext6.create('common.EMK.EvnReceptPanel', {
			refId: 'EvnReceptPanel',
			userCls: 'accordion-panel-window accordion-panel-recept',
			ownerPanel: me,
			ownerWin: me.ownerWin,
		});
		this.EvnXmlPanel = Ext6.create('common.EMK.EvnXmlPanel', { userCls: 'accordion-panel-window accordion-panel-xml'});

		this.AccordionPanel = Ext6.create('Ext6.Panel', {
			cls: 'accordion-panel-emk',
			bodyStyle: 'border-width: 0px 1px 1px 1px;',
			defaults: {
				margin: "0px 0px 2px 0px"
			},
			url: '/?c=EMK&m=loadEvnVizitPLForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'EvnVizitPL_id'},
						{name: 'EvnVizitPL_setDate'},
						{name: 'EvnVizitPL_setTime'},
						{name: 'MedStaffFact_id'},
						{name: 'MedPersonal_sid'},
						{name: 'TreatmentClass_id'},
						{name: 'ServiceType_id'},
						{name: 'VizitClass_id'},
						{name: 'VizitType_id'},
						{name: 'MedicalCareKind_id'},
						{name: 'UslugaComplex_uid'},
						{name: 'LpuSection_id'},
						{name: 'LpuSectionProfile_id'},
						{name: 'PayType_id'},
						{name: 'Diag_id'},
						{name: 'DeseaseType_id'},
						{name: 'EvnClass_id'},
						{name: 'Person_id'},
						{name: 'Server_id'},
						{name: 'PersonEvn_id'}
					]
				})
			}),
			layout: {
				type: 'accordion',
				titleCollapse: false,
				animate: true,
				multi: true,
				activeOnTop: false
			},
			listeners: {
				'resize': function() {
					this.updateLayout();
				}
			},
			dockedItems: [
				this.TabContainer
			],
			items: [
				me.EvnVizitPLPanel,
				me.SpecificsHepatitisPanel,
				me.SpecificsPregnancyPanel,
				me.SpecificsCrazyPanel,
				me.SpecificsTubPanel,
				me.SpecificsVenerPanel,
				me.SpecificsNephroPanel,
				me.SpecificsProfPanel,
				me.SpecificsPalliatPanel,
				me.SpecificsGeriatrics,
				me.DrugTherapySchemePanel,
				me.ProtocolPanel,
				me.EvnPrescrPanel,
				//me.EvnDirectionPanel,
				me.EvnUslugaPanel,
				me.EvnReceptPanel,
				me.EvnDrugPanel,
				me.EvnXmlPanel,
				me.EvnPLDispScreenOnko,
				me.RepositoryObservPanel
			]
		});

		this.bottomPanel = Ext6.create('common.EMK.PersonBottomPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin
		});

		this.EvnPLFormPanel = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			region: 'north',
			hidden: true,
			autoHeight: true,
			border: false,
			itemId: 'EvnPLFormPanel',
			url: '/?c=EMK&m=loadEvnPLForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'EvnPL_id'},
						{name: 'EvnPL_IsSigned'},
						{name: 'accessType'},
						{name: 'EvnPL_IsFinishName'},
						{name: 'EvnPL_UKL'},
						{name: 'EvnPL_IsFirstDisable'},
						{name: 'PrivilegeType_id'},
						{name: 'EvnPL_IsSurveyRefuseName'},
						{name: 'ResultClass_Name'},
						{name: 'InterruptLeaveType_Name'},
						{name: 'ResultDeseaseType_Name'},
						{name: 'DirectType_Name'},
						{name: 'DirectClass_Name'},
						{name: 'LpuSection_FullName'},
						{name: 'Lpu_Name'},
						{name: 'Diag_lName'},
						{name: 'Diag_concName'},
						{name: 'PrehospTrauma_Name'},
						{name: 'EvnPL_IsUnportName'},
						{name: 'LeaveType_fedName'},
						{name: 'ResultDeseaseType_fedName'},
						{name: 'AlertReg_Msg'}
					]
				})
			}),
			layout: 'anchor',
			items: [{
				layout: 'column',
				border: false,
				style: 'margin-bottom: 5px;',
				anchor:'100%',
				defaults: {
					border: false,
					labelWidth: 172
				},
				items: [{
					xtype: 'fieldset',
					title: 'Данные о завершении случая',
					border: false,
					collapsible: true,
					columnWidth: .95,
					defaults: {
						margin: "0px 0px 2px 10px",
						labelWidth: 270,
						style: 'text-align:right',
						fieldStyle: 'font-weight: 700'
					},
					cls: 'personPanel',
					items: [{
						xtype: 'hidden',
						name: 'EvnPL_id'
					}, {
						xtype: 'hidden',
						name: 'EvnPL_IsSigned'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Случай закончен',
						name: 'EvnPL_IsFinishName'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'УКЛ',
						name: 'EvnPL_UKL'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Впервые выявленная инвалидность',
						name: 'EvnPL_IsFirstDisable'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Впервые выявленная инвалидность',
						name: 'PrivilegeType_id'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Отказ от прохождения медицинских обследований',
						name: 'EvnPL_IsSurveyRefuseName',
						hidden: getRegionNick() == 'kz'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Результат',
						name: 'ResultClass_Name'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Случай прерван',
						name: 'InterruptLeaveType_Name'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Исход',
						name: 'ResultDeseaseType_Name',
						hidden: !getRegionNick().inlist(['adygeya', 'vologda', 'buryatiya', 'ekb', 'kaluga', 'kareliya', 'krasnoyarsk', 'krym', 'penza', 'pskov', 'yakutiya', 'yaroslavl'])
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Направление',
						name: 'DirectType_Name'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Куда направлен',
						name: 'DirectClass_Name'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Отделение направления',
						name: 'LpuSection_FullName'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'МО направления',
						name: 'Lpu_Name'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Заключительный диагноз',
						name: 'Diag_lName'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Заключительная внешняя причина',
						name: 'Diag_concName'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Вид травмы (внеш. воздействия)',
						name: 'PrehospTrauma_Name'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Нетранспортабельность',
						name: 'EvnPL_IsUnportName'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Фед. результат',
					name: 'LeaveType_fedName',
					hidden: getRegionNick() == 'ufa'
					}, {
						xtype: 'displayfield',
						fieldLabel: 'Фед. исход',
					name: 'ResultDeseaseType_fedName',
					hidden: getRegionNick() == 'ufa'
					}]
				}, {
					xtype: 'button',
					padding: "13px 10px",
					userCls: 'button-without-frame',
					iconCls: 'panicon-edit-pers-info',
					name: 'editPerson',
					tooltip: langs('Редактировать данные о завершении случая'),
					handler: function () {
						me.finishEvnPL(true);
					}
				}]
			}]
		});

		let reader = Ext6.create('Ext6.data.reader.Json', {
			type: 'json',
			model: Ext6.create('Ext6.data.Model', {
				fields:[
					{name: 'EvnPL_id'},
					{name: 'PrehospDirect_id'},
					{name: 'accessType'},
					{name: 'LpuSection_did'},
					{name: 'MedStaffFact_did'},
					{name: 'Org_did'},
					{name: 'Lpu_did'},
					{name: 'EvnDirection_Num'},
					{name: 'EvnDirection_setDate'},
					{name: 'Diag_did'},
					{name: 'Diag_fid'},
					{name: 'Diag_preid'},
					{name: 'EvnDirection_id'},
					{name: 'EvnDirection_IsAuto'},
					{name: 'EvnDirection_IsReceive'}
				]
			})
		});
		let directionInfoUrl = '/?c=EvnDirection&m=getEvnDirection';

		this.EvnPLDirectionInfoPanel = Ext6.create('widget.swDirectionInfoPanel', {
			ownerWin: me,
			url: directionInfoUrl,
			reader: reader
		});
		this.EvnPLDirectionInfoEditPanel = Ext6.create('widget.swDirectionInfoEditPanel', {
			prefix: 'EPLEF',
			ownerWin: me,
			useCase: 'choose_for_evnpl',
			url: directionInfoUrl,
			reader: reader,
			showMedStaffFactCombo: true,
			personFieldName: 'Person_id',
			medStaffFactFieldName: null,
			fromLpuFieldName: 'Lpu_fid',
			fieldIsWithDirectionName: 'EvnPL_IsWithoutDirection',
			buttonSelectId: 'EPLEF_EvnDirectionSelectButton',
			fieldPrehospDirectName: 'PrehospDirect_id',
			fieldLpuSectionName: 'LpuSection_did',
			fieldMedStaffFactName: 'MedStaffFact_did',
			fieldOrgName: 'Org_did',
			fieldDoctorCode: 'EvnPL_MedPersonalCode',
			fieldNumName: 'EvnDirection_Num',
			fieldSetDateName: 'EvnDirection_setDate',
			fieldDiagName: 'Diag_did',
			fieldDiagFName: 'Diag_fid',
			fieldDiagPredName: 'Diag_preid',
			fieldIdName: 'EvnDirection_id',
			fieldIsAutoName: 'EvnDirection_IsAuto',
			fieldIsExtName: 'EvnDirection_IsReceive',
			parentSetDateFieldName: 'EvnPL_setDate',
			nextFieldName: 'EvnPL_IsFinish',
		});
		
		Ext6.apply(this, {
			items: [this.titlePanel, {
				region: 'center',
				flex: 400,
				bodyPadding: 10,
				scrollable: true,
				bodyStyle: "border-width: 1px 0;",
				items: [
					this.EvnPLFormPanel,
					this.EvnPLDirectionInfoPanel,
					this.EvnPLDirectionInfoEditPanel,
					this.AccordionPanel
				]
			}, this.bottomPanel]
		});

		me.callParent(arguments);
	},

	setAllowed: function(protocolEvnClassList, protocolXmlTypeEvnClassLink,
		evnXmlEvnClassList, evnXmlXmlTypeEvnClassLink)
	{
		this.ProtocolPanel.setAllowed(protocolEvnClassList, protocolXmlTypeEvnClassLink);
		this.EvnXmlPanel.allowedEvnClassList = evnXmlEvnClassList;
		this.EvnXmlPanel.allowedXmlTypeEvnClassLink = evnXmlXmlTypeEvnClassLink;
	}
});
