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
Ext6.define('common.EvnPLStom.EvnPLStomForm', {
	requires: [
		'common.EMK.EvnDiagPLStomPanel',
		'common.EvnPLStom.PersonToothCardPanel',
		'common.EvnPLStom.ParodontogramPanel',
		'common.EvnXml.ItemsPanel',
		'common.EMK.EvnPrescribePanel',
		'common.EMK.EvnDirectionPanel',
		'common.EMK.EvnDrugPanel',
		'common.EMK.EvnUslugaPanel',
		'common.EMK.EvnXmlPanel',
		'common.EMK.PersonBottomPanel',
		'sw.frames.EMD.swEMDPanel'
	],
	alias: 'widget.EvnPLStomForm',
	extend: 'Ext6.Panel',
	layout: 'border',
	region: 'center',
	border: false,
	evnParams: {},
	params: {},
	setParams: function(params) {
		var me = this;

		me.params = params;
		this.EvnPLStom_id = params.EvnPLStom_id;

		this.swEMDPanel.setParams({
			EMDRegistry_ObjectName: 'EvnPLStom',
			EMDRegistry_ObjectID: me.EvnPLStom_id
		});

		me.EvnDirectionPanel.setParams({
			Evn_id: params.EvnPLStom_id,
			Person_Surname: params.Person_Surname,
			Person_Firname: params.Person_Firname,
			Person_Secname: params.Person_Secname,
			Person_Birthday: params.Person_Birthday
		});
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
				isStom: true
			});
			if(base_form.findField('MedicalCareKind_id')){
				base_form.findField('MedicalCareKind_id').getStore().filterBy(function(rec){
					return ((rec.get('additionalSortCode') && rec.get('additionalSortCode') === -2) || rec.get('MedicalCareKind_Code').inlist(['4','11','12','13']));
				});
			}

			me.loadEvnPLStomFormPanel(options);
		});
	},
	filterUslugaComplexCombo: function(eventData,base_form){

		var cmp = base_form.findField('UslugaComplex_uid'),
			v,
			
			// cmp.getStore().getProxy().extraParams:
			xPars,
			
			// Дата, до и после которой фильтрация кодов посещения выполняется по разным кодам разделов услуг:
			xdate = new Date(2015,0,1);

		if (!((v = cmp.getStore()) && (v = v.getProxy())))
			return;
		
		if (!v.extraParams)
			v.extraParams = {};

		xPars = v.extraParams;
			
		cmp.setVizitCodeFilters({
			isStom: true
		});

		if ( getRegionNick() == 'ekb') {
			if (eventData)
			{
				if (eventData.Mes_id) 
					xPars.Mes_id = eventData.Mes_id;

				if ((v = eventData.EvnVizitPLStom_setDate) && (v = Date.parseDate(v, 'd.m.Y')))
					xPars.UslugaComplexPartition_CodeList = Ext.util.JSON.encode(v >= xdate ? [302,303] :[300,301]);
			}
		}

		if ( getRegionNick() == 'perm') {

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
			if (eventData && eventData.PayType_id) {
				cmp.getStore().getProxy().extraParams.PayType_id = eventData.PayType_id;
			}


			// Делаем фильтр по дате для всех
			if ( typeof eventData == 'object' && !Ext.isEmpty(eventData.EvnVizitPLStom_setDate) ) {
				cmp.setUslugaComplexDate(eventData.EvnVizitPLStom_setDate);
			} else if (typeof eventData == 'object' && !Ext.isEmpty(eventData.EvnSection_setDate)) {
				cmp.setUslugaComplexDate(eventData.EvnSection_setDate);
			} else {
				cmp.setUslugaComplexDate(getGlobalOptions().date);
			}

			if (eventData && eventData.EvnVizitPLStom_id) {
				cmp.getStore().getProxy().extraParams.EvnVizitPLStom_id = eventData.EvnVizitPLStom_id;
			}

		}

	},
	filterMedStaffFactCombo: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		var medstafffact_filter_params = {
			allowLowLevel: 'yes',
			isStom: true
		};

		var mid_medstafffact_filter_params = {
			allowLowLevel: 'yes',
			isMidMedPersonalOnly: true,
			isStom: true
		};

		if (!Ext6.isEmpty(base_form.findField('EvnVizitPLStom_setDate').getValue()) && typeof base_form.findField('EvnVizitPLStom_setDate').getValue() == 'object') {
			medstafffact_filter_params.onDate = base_form.findField('EvnVizitPLStom_setDate').getValue().format('d.m.Y');
			mid_medstafffact_filter_params.onDate = base_form.findField('EvnVizitPLStom_setDate').getValue().format('d.m.Y');
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
	getObjectData: function(object,object_id)
	{
		var record = this.viewFormDataStore.getById(object +'_'+ object_id);
		if (record && record.data)
		{
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
	onLoadEvnPLStomFormPanel: function(data) {
		Ext6.suspendLayouts();
		var me = this;
		var isStom = me.ownerWin.userMedStaffFact.ARMType.inlist(['stom', 'stom6']);

		if (data[0]) {
			if (me.ownerWin.PersonInfoPanel && me.ownerWin.PersonInfoPanel.checkIsDead()) {
				me.EvnPLStomFormPanel.accessType = 'view';
			} else {
				me.EvnPLStomFormPanel.accessType = data[0].accessType;
			}
			me.EvnPLStomFormPanel.canCreateVizit = data[0].canCreateVizit == 1 ? true : false;

			if (data[0].AlertReg_Msg) {
				sw4.showInfoMsg({
					panel: me,
					type: 'warning',
					text: data[0].AlertReg_Msg
				});
			}

			if (me.EvnPLStomFormPanel.accessType == 'edit') {
				me.tabToolPanel.down('[refId=addEvnVizitPLStom]').enable();
				me.EvnPLMenu.down('[itemId=deleteEvnPLStom]').enable();
			} else {
				me.tabToolPanel.down('[refId=addEvnVizitPLStom]').disable();
				me.EvnPLMenu.down('[itemId=deleteEvnPLStom]').disable();
			}
			me.tabToolPanel.down('[refId=addEvnVizitPLStom]').setDisabled(!isStom || !me.EvnPLStomFormPanel.canCreateVizit);

			me.swEMDPanel.setIsSigned(data[0].EvnPLStom_IsSigned);

			if (me.EvnPLStomFormPanel.accessType == 'edit') {
				me.swEMDPanel.enable();
			} else {
				me.swEMDPanel.disable();
			}

			me.toolPanel.down('[refId=finishEvnPLStomButton]').disable();
			me.EvnPLMenu.down('[itemId=cancelEvnPLStomFinish]').disable();
			if (data[0].EvnPLStom_IsFinish == 2) {
				me.EvnPLStomFormPanel.show();
				if (me.EvnPLStomFormPanel.accessType == 'edit') {
					me.EvnPLMenu.down('[itemId=cancelEvnPLStomFinish]').enable();
					me.addEvnVizitPLStomBtn.setDisabled(true);
				}
			} else {
				if (me.EvnPLStomFormPanel.accessType == 'edit') {
					me.toolPanel.down('[refId=finishEvnPLStomButton]').enable();
					me.addEvnVizitPLStomBtn.setDisabled(false);
				} else {
					me.addEvnVizitPLStomBtn.setDisabled(!isStom || !me.EvnPLStomFormPanel.canCreateVizit);
				}
				me.EvnPLStomFormPanel.hide();
			}

			me.setMainPanelTitle(data[0]);

			// создаём табы в TabPanel в соответствии с посещениями
			me.resetEvnVizitPLStom();
			me.tabPanel.removeAll();
			for(var k in data[0].EvnVizitPLStom) {
				if (data[0].EvnVizitPLStom[k].EvnVizitPLStom_id) {
					var panel = Ext6.create('Ext6.Panel', {
						title: data[0].EvnVizitPLStom[k].EvnVizitPLStom_setDate,
						border: false,
						html: '',
						EvnVizitPLStom_id: data[0].EvnVizitPLStom[k].EvnVizitPLStom_id
					});
					me.tabPanel.add(panel);
				}
			}

			// грузим данные по первому Tab'у.
			me.tabPanel.setActiveTab(0);

			me.bottomPanel.setParams({
				Evn_id: me.EvnPLStom_id,
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
	loadEvnPLStomFormPanel: function(options) {
		var me = this;
		me.mask(LOADING_MSG);
		me.EvnPLStomFormPanel.getForm().load({
			params: {
				EvnPLStom_id: me.EvnPLStom_id
			},
			success: function (form, action) {
				if (options && typeof options.callback == 'function') {
					options.callback();
				}

				me.unmask();

				if (action.response && action.response.responseText) {
					var data = Ext6.JSON.decode(action.response.responseText);
					me.onLoadEvnPLStomFormPanel(data);
				}
			},
			failure: function (form, action) {
				if (options && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	loadUslugaComplexTariffCombo: function(UslugaComplexTariff_id) {
		var me = this;
		var base_form = me.formPanel.getForm();

		var combo = base_form.findField('UslugaComplexTariff_id'),
			uc_combo = base_form.findField('UslugaComplex_uid'),
			params = {
				LpuSection_id: base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id')
				,PayType_id: base_form.findField('PayType_id').getValue()
				,Person_id: base_form.findField('Person_id').getValue()
				,UslugaComplexTariff_Date: base_form.findField('EvnVizitPLStom_setDate').getValue()
				,UslugaComplex_id: uc_combo.getValue()
			};
		combo.setParams(params);
		combo.fireEvent('change', combo, combo.getValue());
		combo.isAllowSetFirstValue = ('add' == this.action);
		combo.isLpuFilter = true;
		if (getRegionNick() == 'perm') {
			combo.getStore().proxy.extraParams.UEDAboveZero = 1;
		}
		combo.loadUslugaComplexTariffList({
			callback: function() {
				if (UslugaComplexTariff_id) {
					combo.setValue(UslugaComplexTariff_id);
				}
			}
		});
		return true;
	},
	loadDiagNewCombo: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var me = this;
		var base_form = me.formPanel.getForm();
		var currentValue = base_form.findField('Diag_newid').getValue();
		var savedValue = base_form.findField('Diag_id').getValue();

		base_form.findField('Diag_newid').setAllowBlank(false);
		base_form.findField('Diag_newid').enable();

		base_form.findField('Diag_newid').getStore().proxy.extraParams.EvnPLStom_id = me.EvnPLStom_id;
		base_form.findField('Diag_newid').getStore().load({
			params: {
				EvnPLStom_id: me.EvnPLStom_id,
				EvnVizitPLStom_id: base_form.findField('EvnVizitPLStom_id').getValue()
			},
			callback: function() {
				var index = -1;
				if (!options.ignoreCurrentValue) {
					// если нашли выбранное, то выбираем его
					index = base_form.findField('Diag_newid').getStore().findBy(function (rec) {
						return (rec.get('Diag_id') == currentValue);
					});
					if (index == -1) {
						// иначе если совпадает с Diag_id сохранённым в БД
						index = base_form.findField('Diag_newid').getStore().findBy(function (rec) {
							return (rec.get('Diag_id') == savedValue);
						});
					}
				}

				if (index == -1) {
					// иначе если совпадает с Diag_id заболевания по текущему посещению
					index = base_form.findField('Diag_newid').getStore().findBy(function (rec) {
						return (rec.get('Diag_IsCurrent') == 2);
					});
				}

				if (index >= 0) {
					base_form.findField('Diag_newid').setValue(base_form.findField('Diag_newid').getStore().getAt(index).get('Diag_id'));
				} else if (base_form.findField('Diag_newid').getStore().getCount() > 0) {
					// иначе первое попавшееся
					base_form.findField('Diag_newid').setValue(base_form.findField('Diag_newid').getStore().getAt(0).get('Diag_id'));
				} else {
					// иначе очищаем
					base_form.findField('Diag_newid').clearValue();
					base_form.findField('Diag_newid').setAllowBlank(true);
					base_form.findField('Diag_newid').disable();
				}

				base_form.findField('Diag_newid').fireEvent('change', base_form.findField('Diag_newid'), base_form.findField('Diag_newid').getValue());
			}
		});
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
						onDate: (!Ext6.isEmpty(base_form.findField('EvnVizitPLStom_setDate').getValue()) && typeof base_form.findField('EvnVizitPLStom_setDate').getValue() == 'object') ? base_form.findField('EvnVizitPLStom_setDate').getValue().format('d.m.Y') : getGlobalOptions().date
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
	resetEvnVizitPLStom: function() {
		Ext6.suspendLayouts();
		var me = this;
		var base_form = me.formPanel.getForm();
		var accessType = 'view';
		base_form.reset();

		me.EvnVizitPLStomMenu.disable();
		me.formPanel.accessType = accessType;
		me.formPanel.enableEdit(false);
		me.bottomPanel.enableEdit(false);

		me.EvnDiagPLStomPanel.setTitleCounter(0);
		me.EvnDiagPLStomPanel.setAccessType(accessType);

		me.PersonToothCardPanel.setTitleCounter(0);
		me.PersonToothCardPanel.setAccessType(accessType);

		me.ParodontogramPanel.setTitleCounter(0);
		me.ParodontogramPanel.setAccessType(accessType);

		me.ProtocolPanel.collapse();
		me.ProtocolPanel.setTitleCounter(0);
		me.ProtocolPanel.setAccessType(accessType);

		me.EvnPrescrPanel.setTitleCounter(0);
		me.EvnPrescrPanel.setAccessType(accessType);
		me.EvnPrescrPanel.enableEdit(false);

		me.EvnDirectionPanel.setTitleCounter(0);
		me.EvnDirectionPanel.setAccessType(accessType);

		me.EvnDrugPanel.setTitleCounter(0);
		me.EvnDrugPanel.setAccessType(accessType);

		me.EvnUslugaPanel.setTitleCounter(0);
		me.EvnUslugaPanel.setAccessType(accessType);

		me.EvnXmlPanel.setTitleCounter(0);
		me.EvnXmlPanel.setAccessType(accessType);
		Ext6.resumeLayouts(true);
	},
	saveEvnVizitPLStom: function(params) {
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

		base_form.findField('Diag_id').setValue(base_form.findField('Diag_newid').getFieldValue('Diag_id'));
		base_form.findField('DeseaseType_id').setValue(base_form.findField('Diag_newid').getFieldValue('DeseaseType_id'));

		base_form.submit({
			url: '/?c=EvnPLStom&m=saveEvnVizitFromEMK',
			params: params,
			success: function(result_form, action) {
				sw4.showInfoMsg({
					panel: me,
					type: 'success',
					text: 'Данные сохранены.'
				});

				if (me.swEMDPanel.IsSigned == 2) {
					me.swEMDPanel.setIsSigned(1);
				}

				me.ProtocolPanel.editorPanel.refreshSpecMarkerBlocksContent();
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
								me.saveEvnVizitPLStom(params);
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
	getEvnData: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		return {
			EvnPLStom_id: me.EvnPLStom_id,
			LpuSection_Name: base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_Name'),
			MedPersonal_Fin: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_Fin'),
			Evn_setDate: (!Ext6.isEmpty(base_form.findField('EvnVizitPLStom_setDate').getValue()) && typeof base_form.findField('EvnVizitPLStom_setDate').getValue() == 'object') ? base_form.findField('EvnVizitPLStom_setDate').getValue().format('d.m.Y') : getGlobalOptions().date,
			Evn_setTime: base_form.findField('EvnVizitPLStom_setTime').getValue(),
			MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
			LpuSection_id: base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id'),
			MedPersonal_id: base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
			LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
			ServiceType_SysNick: base_form.findField('ServiceType_id').getFieldValue('ServiceType_SysNick'),
			VizitType_SysNick: base_form.findField('VizitType_id').getFieldValue('VizitType_SysNick'),
			Diag_id: base_form.findField('Diag_id').getValue(),
			PayType_id: base_form.findField('PayType_id').getValue(),
			Mes_id: base_form.findField('Mes_id').getValue(),
			UslugaComplex_Code: base_form.findField('UslugaComplex_uid').getFieldValue('UslugaComplex_Code')
		};
	},
	cancelEvnPLStomFinish: function(options) {
		options = options || {};

		var me = this;

		var params = {
			EvnPLStom_id: me.EvnPLStom_id,
			EvnPLStom_IsFinish: 1
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
			url: '/?c=EvnPLStom&m=saveEvnPLStomFinishForm',
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

								me.cancelEvnPLStomFinish(options);
							}
						},
						icon: Ext6.MessageBox.QUESTION,
						msg: response_obj.Alert_Msg,
						title: langs('Продолжить сохранение?')
					});
				} else {
					me.loadEvnPLStomFormPanel();
				}
			}
		});
	},
	filterTreatmentDiag: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		var tratmentClassCombo = base_form.findField('TreatmentClass_id');

		tratmentClassCombo.getStore().clearFilter();
		var Diag_Code = base_form.findField('Diag_newid').getFieldValue('Diag_Code');
		if (!Ext6.isEmpty(Diag_Code)) {
			tratmentClassCombo.getStore().filterBy(function(rec) {
				if (Diag_Code == 'Z51.5') {
					return (rec.get('TreatmentClass_id').inlist([9]));
				} else if (Diag_Code.substr(0, 1) == 'Z' || (getRegionNick() == 'perm' && Diag_Code.substr(0, 3) == 'W57')) {
					return (rec.get('TreatmentClass_id').inlist([6, 7, 8, 9, 10, 11, 12]));
				} else if (getRegionNick() == 'penza') {
					return (rec.get('TreatmentClass_id').inlist([1, 2, 3, 4, 11, 13]));
				} else {
					return (rec.get('TreatmentClass_id').inlist([1, 2, 3, 4, 13]));
				}
			});
		} else {
			tratmentClassCombo.getStore().filterBy(function(rec) {
				return (!rec.get('TreatmentClass_Code').inlist([2]));
			});
		}
		var index = tratmentClassCombo.getStore().findBy(function(rec) {
			return (rec.get('TreatmentClass_id') == tratmentClassCombo.getValue());
		});
		if (index == -1) {
			tratmentClassCombo.clearValue();
		}
	},
	getDiagId: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		return base_form.findField('Diag_id').getValue();
	},
	setVizitTypeFilter: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		var EvnVizitPLStom_setDate = base_form.findField('EvnVizitPLStom_setDate').getValue();

		base_form.findField('VizitType_id').setTreatmentClass(base_form.findField('TreatmentClass_id').getValue());
		if (!Ext6.isEmpty(base_form.findField('PayType_id').getValue()) && getRegionNick() == 'kareliya') {
			var pay_type_nick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');

			if (pay_type_nick && pay_type_nick == 'oms') {
				var denied_visit_type_codes = ['41', '51', '2.4', '3.1'];

				if (EvnVizitPLStom_setDate < new Date('2019-05-01')) {
					denied_visit_type_codes.push('1.2');
				}

				base_form.findField('VizitType_id').setFilterByDateAndCode(EvnVizitPLStom_setDate, denied_visit_type_codes);
			} else {
				base_form.findField('VizitType_id').setFilterByDate(EvnVizitPLStom_setDate);
			}
		} else {
			base_form.findField('VizitType_id').setFilterByDate(EvnVizitPLStom_setDate);
		}
	},
	loadMesEkbCombo: function(options) {
		if (getRegionNick() != 'ekb') {
			return false;
		}

		let base_form = this.formPanel.getForm();
		let callback = Ext.emptyFn;
		let mesCombo = base_form.findField('Mes_id');
		let UslugaComplex_uid = base_form.findField('UslugaComplex_uid').getValue();

		if (options && options.callback && typeof options.callback == 'function') {
			callback = options.callback;
		}

		mesCombo.getStore().proxy.extraParams.MesType_id = 12;

		if (!Ext6.isEmpty(UslugaComplex_uid)) {
			mesCombo.getStore().proxy.extraParams.UslugaComplex_id = UslugaComplex_uid;
		}
		else {
			mesCombo.getStore().proxy.extraParams.UslugaComplex_id = null;
		}

		mesCombo.getStore().load({
			callback: callback
		});
	},
	onLoadEvnVizitPLStomFormPanel: function(data) {
		Ext6.suspendLayouts();
		var me = this;
		var base_form = me.formPanel.getForm(),
			armType = getGlobalOptions().curARMType;

		if (data[0]) {
			if (me.ownerWin.PersonInfoPanel && me.ownerWin.PersonInfoPanel.checkIsDead()) {
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
				me.EvnVizitPLStomMenu.enable();
				me.formPanel.enableEdit(true);
				me.bottomPanel.enableEdit(true);
				// me.EvnVizitPLStomMenu.down('[itemId=deleteEvnVizitPLStom]').enable();
				// me.EvnVizitPLStomMenu.down('[itemId=copyEvnVizitPLStom]').enable();
			} else {
				me.EvnVizitPLStomMenu.disable();
				me.formPanel.enableEdit(false);
				me.bottomPanel.enableEdit(false);
				// me.EvnVizitPLStomMenu.down('[itemId=deleteEvnVizitPLStom]').disable();
				// me.EvnVizitPLStomMenu.down('[itemId=copyEvnVizitPLStom]').disable();
			}

			if (getRegionNick().inlist(['buryatiya', 'ekb', 'pskov', 'ufa'])) {
				base_form.findField('UslugaComplex_uid').setPersonId(data[0].Person_id);
			}

			if (getRegionNick() == 'ekb') {
				if (!Ext6.isEmpty(data[0].Mes_id)) {
					base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.Mes_id = data[0].Mes_id;

					base_form.findField('Mes_id').getStore().load({
						params: {
							Mes_id: data[0].Mes_id
						},
						callback: function () {
							base_form.findField('Mes_id').setValue(data[0].Mes_id);
						}
					});
				}
				else {
					me.loadMesEkbCombo();
				}

				if (!Ext6.isEmpty(data[0].UslugaComplex_uid)) {
					base_form.findField('Mes_id').getStore().proxy.extraParams.UslugaComplex_id = data[0].UslugaComplex_uid;
				}
			}

			me.filterUslugaComplexCombo(data[0],base_form);
			
			if (!Ext6.isEmpty(data[0].UslugaComplex_uid)) {
				// прогружаем услугу
				base_form.findField('UslugaComplex_uid').getStore().load({
					params: {
						UslugaComplex_id: data[0].UslugaComplex_uid
					},
					callback: function () {
						base_form.findField('UslugaComplex_uid').setValue(data[0].UslugaComplex_uid);
						me.loadUslugaComplexTariffCombo(data[0].UslugaComplexTariff_id);
					}
				});
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
								}
								else {
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
								}
								else {
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
								}
								else {
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

			if (data[0].ProfGoal_id == null) {
				base_form.findField('ProfGoal_id').disable();
			}

			if(data[0].EvnPLDisp_id == null) {
				base_form.findField('EvnPLDisp_id').disable();
			}

			if (data[0].DispClass_id != null) {
				base_form.findField('EvnPLDisp_id').enable();
			}

			base_form.findField('PersonDisp_id').getStore().proxy.extraParams  = { Person_id: base_form.findField('Person_id').getValue() };

			if (getRegionNick().inlist([ 'ufa', 'kareliya', 'ekb' ])) {
				base_form.findField('MedicalCareKind_id').disable();
			}

			me.loadDiagNewCombo();

			me.EvnDiagPLStomPanel.setTitleCounter(data[0].EvnDiagPLStomCount);
			me.EvnDiagPLStomPanel.setParams({
				Evn_id: data[0].EvnVizitPLStom_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id,
				diagCount: data[0].EvnDiagPLStomCount
			});
			me.EvnDiagPLStomPanel.setAccessType(me.formPanel.accessType);


			me.PersonToothCardPanel.setTitleCounter(0);
			me.PersonToothCardPanel.setParams({
				Evn_id: data[0].EvnVizitPLStom_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id
			});
			me.PersonToothCardPanel.setAccessType(me.formPanel.accessType);

			me.ParodontogramPanel.setTitleCounter(0);
			me.ParodontogramPanel.setParams({
				Evn_id: data[0].EvnVizitPLStom_id,
				Evn_pid: me.EvnPLStom_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id,
				EvnUslugaStom_id: data[0].EvnUslugaParodontogram_id,
				EvnUslugaStom_setDate: data[0].EvnVizitPLStom_setDate
			});
			me.ParodontogramPanel.setAccessType('view');

			me.ProtocolPanel.setTitleCounter(data[0].ProtocolCount);
			me.ProtocolPanel.setParams({
				Person_id: data[0].Person_id,
				Evn_id: data[0].EvnVizitPLStom_id,
				EvnClass_id: data[0].EvnClass_id,
				LpuSection_id: data[0].LpuSection_id,
				MedPersonal_id: data[0].MedPersonal_id,
				MedStaffFact_id: data[0].MedStaffFact_id
			});
			me.ProtocolPanel.setAccessType(me.formPanel.accessType);

			me.EvnPrescrPanel.setTitleCounter(data[0].EvnPrescrCount);
			me.EvnPrescrPanel.setAccessType(me.formPanel.accessType);
			me.EvnPrescrPanel.enableEdit(me.formPanel.accessType == 'edit');

			me.EvnDirectionPanel.setTitleCounter(data[0].EvnDirectionCount);
			me.EvnDirectionPanel.setParams({
				Evn_id: data[0].EvnVizitPLStom_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id
			});
			me.EvnDirectionPanel.setAccessType(me.formPanel.accessType);

			me.EvnDrugPanel.setTitleCounter(data[0].EvnDrugCount);
			me.EvnDrugPanel.setParams({
				Evn_id: data[0].EvnVizitPLStom_id,
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
			me.EvnUslugaPanel.setTitleCounter(data[0].EvnUslugaCount);
			me.EvnUslugaPanel.setParams({
				Evn_id: data[0].EvnVizitPLStom_id,
				EvnClass_id: data[0].EvnClass_id,
				ownerPanel: me,
				userMedStaffFact: me.ownerWin.userMedStaffFact,
				Person_id: data[0].Person_id,
				Server_id: data[0].Server_id,
				PersonEvn_id: data[0].PersonEvn_id
			});
			me.EvnUslugaPanel.disableButtons = true;
			me.EvnUslugaPanel.setAccessType(me.formPanel.accessType);

			me.EvnXmlPanel.setTitleCounter(data[0].EvnXmlCount);
			me.EvnXmlPanel.setParams({
				Evn_id: data[0].EvnVizitPLStom_id,
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
				Evn_id: data[0].EvnVizitPLStom_id,
				Evn_setDate: data[0].EvnVizitPLStom_setDate,
				LpuSection_id: data[0].LpuSection_id,
				MedPersonal_id: data[0].MedPersonal_id
			}, me.evnParams);
		}

		me.unmask();
		Ext6.resumeLayouts(true);
	},
	setMainPanelTitle: function(data){
		// меняем титл
		var me = this,
			title = '';
		if(data.EvnPLStom_NumCard){
			title = 'Случай стоматологического лечения № ' + data.EvnPLStom_NumCard;
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
	initComponent: function() {
		var me = this,
			params;

		this.titleLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			cls: 'no-wrap-ellipsis',
			style: 'font-size: 16px; padding: 3px 10px;',
			html: 'Случай стоматологического лечения № ...'
		});

		this.EvnPLMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Отменить завершение случая лечения',
				itemId: 'cancelEvnPLStomFinish',
				handler: function() {
					me.cancelEvnPLStomFinish({
						EvnPLStom_id: me.EvnPLStom_id
					});
				}
			}, {
				text: 'Удалить случай АПЛ',
				itemId: 'deleteEvnPLStom',
				handler: function() {
					me.ownerWin.deleteEvnPLStom({
						EvnPLStom_id: me.EvnPLStom_id
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
								type: 'EvnPLStom',
								EvnPL_id: me.EvnPLStom_id
							});
						}
					}, {
						text: 'Печать выписки из мед.карты',
						handler: function() {
							if (getRegionNick() == 'kz') {
								printBirt({
									'Report_FileName': 'f027u_Evn.rptdesign',
									'Report_Params': '&paramEvn=' + me.EvnPLStom_id,
									'Report_Format': 'pdf'
								});
							} else {
								printBirt({
									'Report_FileName': 'f027u_EvnPL.rptdesign',
									'Report_Params': '&paramEvnPL=' + me.EvnPLStom_id,
									'Report_Format': 'doc'
								});
							}
						}
					}, {
						text: 'Справка о стоимости лечения',
						hidden: !getRegionNick().inlist(['kz', 'perm', 'ufa']),
						handler: function(){
							sw.Promed.CostPrint.print({
								Evn_id: me.EvnPLStom_id,
								type: 'EvnPLStom'
							});
						}
					}, {
						text: langs('Форма 043/у'),
						handler: function() {
							var base_form = me.formPanel.getForm();
							var EvnVizitPLStom_id = base_form.findField('EvnVizitPLStom_id').getValue();
							var Person_id = base_form.findField('Person_id').getValue();

							printBirt({
								'Report_FileName': 'f043u.rptdesign',
								'Report_Params': '&paramLpu=' + (!Ext.isEmpty(getGlobalOptions().lpu_id)?getGlobalOptions().lpu_id:0) + '&paramEvnVizitPLStom_id=' + (!Ext.isEmpty(EvnVizitPLStom_id) ? EvnVizitPLStom_id : 0) + '&paramPerson_id=' + Person_id,
								'Report_Format': 'pdf'
							});
						}
					}, {
						text: langs('Вкладыш к форме 043/у'),
						handler: function() {
							var base_form = me.formPanel.getForm();
							var EvnVizitPLStom_id = base_form.findField('EvnVizitPLStom_id').getValue();
							var Person_id = base_form.findField('Person_id').getValue();

							printBirt({
								'Report_FileName': 'f043u_insert.rptdesign',
								'Report_Params': '&paramLpu=' + (!Ext.isEmpty(getGlobalOptions().lpu_id)?getGlobalOptions().lpu_id:0) + '&paramEvnVizitPLStom_id=' + (!Ext.isEmpty(EvnVizitPLStom_id) ? EvnVizitPLStom_id : 0) + '&paramPerson_id=' + Person_id,
								'Report_Format': 'pdf'
							});
						}
					}]
				})
			}, {
				userCls: 'button-without-frame',
				iconCls: 'panicon-flag',
				refId: 'finishEvnPLStomButton',
				tooltip: langs('Завершить случай лечения'),
				handler: function () {
					getWnd('swEvnPLStomFinishWindow').show({
						EvnPLStom_id: me.EvnPLStom_id,
						callback: function () {
							me.loadEvnPLStomFormPanel();
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
				EvnVizitPLStom_id: null
			}],
			listeners: {
				'tabchange': function(tabPanel, newCard) {
					// грузим данные по конркетному посещению
					if (newCard.EvnVizitPLStom_id) {
						var base_form = me.formPanel.getForm();
						me.mask(LOADING_MSG);
						me.resetEvnVizitPLStom();
						me.formPanel.loadingData = true;
						base_form.load({
							params: {
								EvnVizitPLStom_id: newCard.EvnVizitPLStom_id
							},
							success: function (form, action) {
								// good
								if (action.response && action.response.responseText) {
									var data = Ext6.JSON.decode(action.response.responseText);
									me.onLoadEvnVizitPLStomFormPanel(data);
								}
								me.formPanel.loadingData = false;
							},
							failure: function (form, action) {
								// not good
								me.formPanel.loadingData = false;
							}
						});
					}
				}
			}
		});

		this.addEvnVizitPLStomBtn = Ext6.create('Ext6.button.Button',{
			text: 'Добавить посещение',
			refId: 'addEvnVizitPLStom',
			handler: function() {
				me.ownerWin.addEvnVizitPLStom({
					EvnPLStom_id: me.EvnPLStom_id
				});
			}
		});

		this.tabToolPanel = Ext6.create('Ext6.Toolbar', {
			region: 'east',
			width: 200,
			height: 40,
			border: false,
			margin: "0 10px 0 0",
			items: ['->', me.addEvnVizitPLStomBtn]
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

		this.EvnVizitPLStomMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Обработать данные в системе принятия решений',
				itemId: 'openDSSWindow',
                hidden: !( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ),
				handler: function() {
                    const base_form = me.formPanel.getForm();
                    const EvnVizitPLStom_id = base_form.findField('EvnVizitPLStom_id').getValue();

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

                        base_form.dssGlobal.dss.show(me.params.Person_id, EvnVizitPLStom_id);
                    });
				}
			},{
				text: 'Скопировать в новый случай',
				itemId: 'copyEvnVizitPLStom',
				handler: function() {
					var base_form = me.formPanel.getForm();
					var EvnVizitPLStom_id = base_form.findField('EvnVizitPLStom_id').getValue();
					if (!Ext6.isEmpty(EvnVizitPLStom_id)) {
						me.ownerWin.copyEvnVizitPLStom({
							EvnVizitPLStom_id: EvnVizitPLStom_id,
							EvnPLStom_id: me.EvnPLStom_id
						});
					}
				}
			}, {
				text: 'Удалить посещение',
				itemId: 'deleteEvnVizitPLStom',
				handler: function() {
					var base_form = me.formPanel.getForm();
					var EvnVizitPLStom_id = base_form.findField('EvnVizitPLStom_id').getValue();
					if (!Ext6.isEmpty(EvnVizitPLStom_id)) {
						me.ownerWin.checkOnOnlyOneVizitExist({
							callback: function (id) {
								me.ownerWin.deleteEvnVizitPLStom({
									EvnVizitPLStom_id: id
								});
							},
							id: EvnVizitPLStom_id
						});
					}
				}
			}]
		});

		this.formPanel = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			padding: "18 0 30 27",
			userCls:'vizitPanelEmk',
			url: '/?c=EMK&m=loadEvnVizitPLStomForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'EvnVizitPLStom_id'},
						{name: 'accessType'},
						{name: 'EvnVizitPLStom_setDate'},
						{name: 'EvnVizitPLStom_setTime'},
						{name: 'MedStaffFact_id'},
						{name: 'MedPersonal_sid'},
						{name: 'TreatmentClass_id'},
						{name: 'ServiceType_id'},
						{name: 'VizitClass_id'},
						{name: 'VizitType_id'},
						{name: 'MedicalCareKind_id'},
						{name: 'Mes_id'},
						{name: 'UslugaComplex_uid'},
						{name: 'LpuSectionProfile_id'},
						{name: 'PayType_id'},
						{name: 'Diag_id'},
						{name: 'DeseaseType_id'},
						{name: 'EvnClass_id'},
						{name: 'Person_id'},
						{name: 'Server_id'},
						{name: 'PersonEvn_id'},
						{name: 'EvnVizitPLStom_IsPrimaryVizit'},
						{name: 'ProfGoal_id'},
						{name: 'DispClass_id'},
						{name: 'EvnPLDisp_id'},
						{name: 'DispProfGoalType_id'},
						{name: 'BitePersonType_id'}
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
						me.saveEvnVizitPLStom();
					}
				}
			},
			items: [{
				name: 'EvnVizitPLStom_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
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
							me.saveEvnVizitPLStom();
						}
					}
				},
				items: [{
					xtype: 'swDateField',
					height:27,
					width: 295,
					anchor:'100%',
					allowBlank: false,
					format: 'd.m.Y',
					startDay: 1,
					fieldLabel: 'Дата/время приема',
					style: 'margin-right: 10px;',
					name: 'EvnVizitPLStom_setDate',
					maxValue: new Date(),
					listeners: {
						'change': function() {
							me.setVizitTypeFilter();
							me.filterMedStaffFactCombo();
							me.saveEvnVizitPLStom();
						}
					}
				}, {
					xtype: 'swTimeField',
					allowBlank: false,
					width: 100,
					userCls:'vizit-time',
					hideLabel: true,
					name: 'EvnVizitPLStom_setTime'
				}]
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'YesNo',
				fieldLabel: 'Первично в текущем году',
				name: 'EvnVizitPLStom_IsPrimaryVizit',
				listeners: {
					'select': function() {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').clearValue();
						me.saveEvnVizitPLStom();
					},
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.isPrimaryVizit = (newValue) ? newValue : null;
						base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
					}
				}
			},{
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
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').clearValue();
						me.saveEvnVizitPLStom();
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
						me.setVizitTypeFilter();
					},
					'select': function (combo, record, eOpts) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').clearValue();
						me.saveEvnVizitPLStom();
					}
				},
				fieldLabel: 'Вид обращения',
				allowBlank: ( getRegionNick() == 'kareliya' ),
				hidden: ( getRegionNick() == 'kareliya' ),
				name: 'TreatmentClass_id'
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
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').clearValue();
						me.saveEvnVizitPLStom();
					}
				},
				fieldLabel: 'Прием',
				displayCode: false,
				name: 'VizitClass_id'
			}, {
				allowBlank: false,
				xtype: 'swVizitTypeCombo',
				comboSubject: 'VizitType',
				EvnClass_id: 13,
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
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').clearValue();
						me.saveEvnVizitPLStom();
					}
				},
				fieldLabel: 'Цель посещения',
				name: 'VizitType_id'
			}, {
				xtype: 'commonSprCombo',
				comboSubject: 'MedicalCareKind',
				sortField: 'MedicalCareKind_Code',
				typeCode: 'int',
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
				allowBlank: (getRegionNick() != 'perm' && sw.Promed.EvnVizitPLStom.isAllowBlankVizitCode()),
				hidden: (getRegionNick() != 'perm' && !sw.Promed.EvnVizitPLStom.isSupportVizitCode()),
				name: 'UslugaComplex_uid',
				to: 'EvnVizitPLStom',
				listeners: {
					'change': function(combo, newValue, oldValue) {

					},
					'select': function (combo, record, eOpts) {
						me.loadUslugaComplexTariffCombo();
						me.saveEvnVizitPLStom();

						if (getRegionNick() == 'ekb') {
							let mes_combo = me.formPanel.getForm().findField('Mes_id');

							mes_combo.lastQuery = 'This query sample that is not will never appear';
							mes_combo.getStore().removeAll();
							mes_combo.getStore().proxy.extraParams.UslugaComplex_id = (typeof record == 'object' ? record.get('UslugaComplex_uid') : null);
							mes_combo.getStore().proxy.extraParams.query = '';
						}
					}
				}
			}, {
				xtype: 'UslugaComplexTariffCombo',
				fieldLabel: 'Тариф',
				name: 'UslugaComplexTariff_id',
				isStom: true,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = me.formPanel.getForm();
						base_form.findField('EvnUslugaStom_UED').setValue(combo.getFieldValue('UslugaComplexTariff_UED'));
					},
					'select': function (combo, record, eOpts) {
						me.saveEvnVizitPLStom();
					}
				},
				hidden: getRegionNick().inlist(['kareliya'])
			}, {
				fieldLabel: 'УЕТ врача',
				name: 'EvnUslugaStom_UED',
				xtype: 'textfield',
				readOnly: true,
				hidden: getRegionNick().inlist(['kareliya'])
			}, {
				allowBlank: !getRegionNick().inlist(['ekb']),
				hidden: !getRegionNick().inlist(['ekb']),
				listeners: {
					'change': function(combo, newValue, oldValue) {
						let base_form = me.formPanel.getForm();

						base_form.findField('UslugaComplex_uid').lastQuery = 'This query sample that is not will never appear';
						base_form.findField('UslugaComplex_uid').getStore().removeAll();
						base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.Mes_id = newValue;
						base_form.findField('UslugaComplex_uid').getStore().proxy.extraParams.query = '';

						me.saveEvnVizitPLStom();
					}
				},
				name: 'Mes_id',
				xtype: 'swMesEkbCombo'
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
						}
					},
					'select': function (combo, record, eOpts) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').clearValue();
						me.saveEvnVizitPLStom();
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
						me.setVizitTypeFilter();
					},
					'select': function (combo, record, eOpts) {
						var base_form = me.formPanel.getForm();
						base_form.findField('UslugaComplex_uid').clearValue();
						me.saveEvnVizitPLStom();
					}
				},
				fieldLabel: 'Вид оплаты',
				displayCode: false,
				name: 'PayType_id'
			}, {
				xtype: 'hidden',
				name: 'Diag_id'
			}, {
				xtype: 'hidden',
				name: 'DeseaseType_id'
			}, {
				xtype: 'swProfGoalCombo',
				name: 'ProfGoal_id',
				fieldLabel: 'Цель профосмотра'
			}, {
				comboSubject: 'DispClass',
				fieldLabel: 'В рамках дисп./мед.осмотра',
				name: 'DispClass_id',
				lastQuery: '',
				hidden: getRegionNick().inlist(['ufa']),
				listeners: {
					'select': function(combo, record, idx) {
						var base_form = me.formPanel.getForm();
						var EvnPLDisp_id = base_form.findField('EvnPLDisp_id').getValue();

						if ( typeof record == 'object' && !Ext.isEmpty(record.get('DispClass_id')) && record.get('DispClass_id') != 0 ) {
							base_form.findField('EvnPLDisp_id').enable();

							if (
								base_form.findField('EvnPLDisp_id').DispClass_id != record.get('DispClass_id')
								|| base_form.findField('EvnPLDisp_id').Person_id != base_form.findField('Person_id').getValue()
							) {
								base_form.findField('EvnPLDisp_id').clearValue();
								base_form.findField('EvnPLDisp_id').getStore().removeAll();

								base_form.findField('EvnPLDisp_id').DispClass_id = record.get('DispClass_id');
								base_form.findField('EvnPLDisp_id').Person_id = base_form.findField('Person_id').getValue();

								base_form.findField('EvnPLDisp_id').getStore().load({
									callback: function() {
										if ( !Ext.isEmpty(EvnPLDisp_id) && base_form.findField('EvnPLDisp_id').getStore().getCount() > 0 ) {
											var index = base_form.findField('EvnPLDisp_id').getStore().findBy(function(rec) {
												return (rec.get('EvnPLDisp_id') == EvnPLDisp_id);
											});

											if ( index >= 0 ) {
												base_form.findField('EvnPLDisp_id').setValue(EvnPLDisp_id);
											}
											else {
												base_form.findField('EvnPLDisp_id').clearValue();
											}
										}
									},
									params: {
										DispClass_id: record.get('DispClass_id'),
										Person_id: base_form.findField('Person_id').getValue()
									}
								})
							}
							else if ( !Ext.isEmpty(EvnPLDisp_id) && base_form.findField('EvnPLDisp_id').getStore().getCount() > 0 ) {
								var index = base_form.findField('EvnPLDisp_id').getStore().findBy(function(rec) {
									return (rec.get('EvnPLDisp_id') == EvnPLDisp_id);
								});

								if ( index >= 0 ) {
									base_form.findField('EvnPLDisp_id').setValue(EvnPLDisp_id);
								}
								else {
									base_form.findField('EvnPLDisp_id').clearValue();
								}
							}
						}
						else {
							base_form.findField('EvnPLDisp_id').clearValue();
							base_form.findField('EvnPLDisp_id').disable();
						}
						me.saveEvnVizitPLStom();
					}.createDelegate(this),
				},
				onLoadStore: function() {
					this.getStore().filterBy(function(rec) {
						if (getRegionNick() == 'kareliya') {
							return (rec.get('DispClass_id').inlist([ 4, 6, 8, 9, 10, 11, 12 ]));
						}
						else if (getRegionNick() == 'krym') {
							return (rec.get('DispClass_id').inlist([ 1, 2, 3, 4, 5, 7, 8, 10, 12 ]));
						}
						else {
							return (rec.get('DispClass_id').inlist([ 4, 8, 11, 12 ]));
						}
					});
				},
				typeCode: 'int',
				xtype: 'commonSprCombo'
			}, {
				comboSubject: 'DispProfGoalType',
				enableKeyEvents: true,
				fieldLabel: 'В рамках дисп./мед.осмотра',
				name: 'DispProfGoalType_id',
				lastQuery: '',
				moreFields: [{name: 'DispProfGoalType_IsVisible', mapping: 'DispProfGoalType_IsVisible'}],
				onLoadStore: function() {
					this.getStore().filterBy(function(rec) {
						return (rec.get('DispProfGoalType_IsVisible') == 2);
					});
				},
				listeners: {
					'select': function (combo, record, eOpts) {
						me.saveEvnVizitPLStom();
					}
				},
				hidden: !getRegionNick().inlist(['ufa']),
				typeCode: 'int',
				allowBlank: true,
				xtype: 'commonSprCombo'
			}, {
				displayField: 'EvnPLDisp_Name',
				enableKeyEvents: true,
				fieldLabel: 'Карта дисп./мед.осмотра',
				name: 'EvnPLDisp_id',
				hidden: getRegionNick().inlist(['ufa']),
				valueField: 'EvnPLDisp_id',
				xtype: 'swBaseLocalCombo'
			}, {
				displayField: 'PersonDisp_Name',
				enableKeyEvents: true,
				fieldLabel: 'Карта дис. учета',
				editable: false,
				name: 'PersonDisp_id',
				triggerAction: 'all',
				valueField: 'PersonDisp_id',
				xtype: 'swBaseRemoteCombo'
			}, {
				comboSubject: 'BitePersonType',
				enableKeyEvents: true,
				fieldLabel: 'Прикус',
				name: 'BitePersonType_id',
				xtype: 'commonSprCombo',
				listeners: {
					'select': function (combo, record) {
						var base_form = me.formPanel.getForm();

						let conf = {
							Person_id: base_form.findField('Person_id').getValue(),
							EvnVizitPLStom_id: base_form.findField('EvnVizitPLStom_id').getValue(),
							BitePersonType_id: record.get('BitePersonType_id'),
						};

						me.ownerWin.saveBitePersonType(conf, me);
					}
				},
			}, {
				xtype: 'swDiagDeseaseCombo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var rec = {
							'Diag_Code': combo.getFieldValue('Diag_Code'),
							'Diag_Name': combo.getFieldValue('Diag_Name')
						};
						if(combo.getSelectedRecord())
							rec = combo.getSelectedRecord().getData();
						//me.EvnPrescrPanel.getController().checkDiag(rec);
						var base_form = me.formPanel.getForm(),
							values = base_form.getValues(),
							record = combo.getSelectedRecord();

						if (!me.formPanel.loadingData) {
							me.ownerWin.setTreeDataDiag('EvnPLStom', me.EvnPLStom_id, values, rec);
						}

						me.filterTreatmentDiag();
					},
					'select': function (combo, record, eOpts) {
						me.saveEvnVizitPLStom();
					}
				},
				fieldLabel: 'Основной диагноз',
				name: 'Diag_newid'
			}]
		});

		this.EvnVizitPLStomPanel = Ext6.create('swPanel', {
			userCls: 'panel-with-tree-dots accordion-panel-window',
			title: 'ПОСЕЩЕНИЕ',
			collapseOnOnlyTitle: true,
			threeDotMenu: me.EvnVizitPLStomMenu,
			items: [
				me.formPanel
			]
		});

		this.EvnDiagPLStomPanel = Ext6.create('common.EMK.EvnDiagPLStomPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-diagplstom',
			onSaveEvnDiagPLStom: function() {
				me.loadDiagNewCombo();
				me.EvnUslugaPanel.load();
			}
		});
		this.PersonToothCardPanel = Ext6.create('common.EvnPLStom.PersonToothCardPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-diagplstom'
		});
		this.ParodontogramPanel = Ext6.create('common.EvnPLStom.ParodontogramPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-diagplstom'
		});
		this.ProtocolPanel = Ext6.create('common.EvnXml.ItemsPanel', {
			title: 'ОСМОТР',
			userCls: 'accordion-panel-window accordion-panel-protocol',
			maxCount: 1
		});
		this.EvnPrescrPanel = Ext6.create('common.EMK.EvnPrescribePanel', {
			ownerPanel: me,
			userCls: 'accordion-panel-window accordion-panel-prescr'
		});
		this.EvnDirectionPanel = Ext6.create('common.EMK.EvnDirectionPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-with-dropdown-menu'
		});
		this.EvnDrugPanel = Ext6.create('common.EMK.EvnDrugPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin,
			userCls: 'accordion-panel-window accordion-panel-drug'
		});
		this.EvnUslugaPanel = Ext6.create('common.EMK.EvnUslugaPanel', {
			userCls: 'accordion-panel-window accordion-panel-with-dropdown-menu',
			callback: function(data) {
				if (data && !data.EvnUsluga_id) {
					me.ParodontogramPanel.setParam('EvnUslugaStom_id', null);
				}
				me.ParodontogramPanel.collapse();
			}
		});
		this.EvnXmlPanel = Ext6.create('common.EMK.EvnXmlPanel', { userCls: 'accordion-panel-window accordion-panel-xml'});

		this.AccordionPanel = Ext6.create('Ext6.Panel', {
			cls: 'accordion-panel-emk',
			bodyStyle: 'border-width: 0px 1px 1px 1px;',
			defaults: {
				margin: "0px 0px 2px 0px"
			},
			url: '/?c=EMK&m=loadEvnVizitPLStomForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'EvnVizitPLStom_id'},
						{name: 'EvnVizitPLStom_setDate'},
						{name: 'EvnVizitPLStom_setTime'},
						{name: 'MedStaffFact_id'},
						{name: 'MedPersonal_sid'},
						{name: 'TreatmentClass_id'},
						{name: 'ServiceType_id'},
						{name: 'VizitClass_id'},
						{name: 'VizitType_id'},
						{name: 'MedicalCareKind_id'},
						{name: 'UslugaComplex_uid'},
						{name: 'UslugaComplexTariff_id'},
						{name: 'EvnUslugaStom_UED'},
						{name: 'Mes_id'},
						{name: 'LpuSectionProfile_id'},
						{name: 'PayType_id'},
						{name: 'Diag_id'},
						{name: 'DeseaseType_id'},
						{name: 'EvnClass_id'},
						{name: 'Person_id'},
						{name: 'Server_id'},
						{name: 'PersonEvn_id'},
						{name: 'EvnVizitPLStom_IsPrimaryVizit'},
						{name: 'ProfGoal_id'},
						{name: 'DispClass_id'},
						{name: 'EvnPLDisp_id'},
						{name: 'DispProfGoalType_id'},
						{name: 'BitePersonType_id'}
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
				me.EvnVizitPLStomPanel,
				me.EvnDiagPLStomPanel,
				me.PersonToothCardPanel,
				me.ParodontogramPanel,
				me.ProtocolPanel,
				me.EvnPrescrPanel,
				me.EvnDirectionPanel,
				me.EvnUslugaPanel,
				me.EvnDrugPanel,
				me.EvnXmlPanel
			]
		});

		this.bottomPanel = Ext6.create('common.EMK.PersonBottomPanel', {
			ownerPanel: me,
			ownerWin: me.ownerWin
		});

		this.EvnPLStomFormPanel = Ext6.create('Ext6.form.Panel', {
			accessType: 'view',
			region: 'north',
			hidden: true,
			autoHeight: true,
			border: false,
			url: '/?c=EMK&m=loadEvnPLStomForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'EvnPLStom_id'},
						{name: 'EvnPLStom_IsSigned'},
						{name: 'accessType'},
						{name: 'EvnPLStom_IsFinish'},
						{name: 'EvnPLStom_IsFinishName'},
						{name: 'EvnPLStom_UKL'},
						{name: 'EvnPLStom_IsSurveyRefuseName'},
						{name: 'ResultClass_Name'},
						{name: 'InterruptLeaveType_Name'},
						{name: 'ResultDeseaseType_Name'},
						{name: 'DirectType_Name'},
						{name: 'DirectClass_Name'},
						{name: 'Diag_lName'},
						{name: 'Diag_concName'},
						{name: 'PrehospTrauma_Name'},
						{name: 'EvnPLStom_IsUnportName'},
						{name: 'LeaveType_fedName'},
						{name: 'ResultDeseaseType_fedName'},
						{name: 'AlertReg_Msg'},
						{name: 'EvnVizitPLStom_IsPrimaryVizit'}
					]
				})
			}),
			layout: 'anchor',
			items: [{
				xtype: 'fieldset',
				title: 'Данные о завершении случая',
				border: false,
				collapsible: true,
				defaults: {
					margin: "0px 0px 2px 10px",
					anchor: '90%',
					labelWidth: 270
				},
				cls: 'personPanel',
				items: [{
					xtype: 'hidden',
					name: 'EvnPLStom_id'
				}, {
					xtype: 'hidden',
					name: 'EvnPLStom_IsSigned'
				}, {
					xtype: 'hidden',
					name: 'EvnPLStom_IsFinish'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Случай закончен',
					name: 'EvnPLStom_IsFinishName'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Санирован',
					name: 'EvnPLStom_IsSanName'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Санация',
					name: 'SanationStatus_Name'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'УКЛ',
					name: 'EvnPLStom_UKL'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Отказ от прохождения медицинских обследований',
					name: 'EvnPLStom_IsSurveyRefuseName',
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
					hidden: !getRegionNick().inlist(['buryatiya', 'ekb', 'kaluga', 'kareliya', 'krasnoyarsk', 'krym', 'penza', 'pskov','vologda','yaroslavl'])
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
					name: 'EvnPLStom_IsUnportName'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Фед. результат',
					name: 'LeaveType_fedName'
				}, {
					xtype: 'displayfield',
					fieldLabel: 'Фед. исход',
					name: 'ResultDeseaseType_fedName'
				}]
			}]
		});

		Ext6.apply(this, {
			items: [this.titlePanel, {
				region: 'center',
				flex: 400,
				bodyPadding: 10,
				scrollable: true,
				bodyStyle: "border-width: 1px 0;",
				items: [
					this.EvnPLStomFormPanel,
					this.AccordionPanel
				]
			}, this.bottomPanel]
		});

		me.callParent(arguments);
	},

	setAllowed: function(evnClassList, xmlTypeEvnClassLink)
	{
		this.ProtocolPanel.setAllowed(evnClassList, xmlTypeEvnClassLink);
	}
});
