/**
 * swEvnPrescrAllUslugaInputWnd - Окно детализации назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @author       gtp_fox
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.tools.swEvnPrescrAllUslugaInputWnd', {
	/* свойства */


	extend: 'base.BaseForm',
	maximized: true,
	callback: Ext6.emptyFn,
	/* свойства */
	alias: 'widget.swEvnPrescrAllUslugaInputWnd',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new EvnPrescrUslugaInputPanel',
	constrain: true,
	autoHeight: true,
	findWindow: false,
	header: false,
	modal: false,
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	width: 1000,
	title: 'Все услуги',
	layout: 'fit',
	refId: 'swEvnPrescrAllUslugaInputWnd',
	resizable: false,
	border: false,
	bodyPadding: 0,
	data: {},
	parentPanel: {},
	show: function (data) {
		var me = this;

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		} else {
			me.callback = Ext6.emptyFn;
		}
		me.PersonInfoPanel = data.PersonInfoPanel;
		me.evnParams = data.EvnParams;
		me.parentPanel = data.parentPanel;
		me.evnPrescrCntr = data.evnPrescrCntr;
		me.PacketPrescr_id = data.PacketPrescr_id;
		me.data = data; // вот эту строчку никогда бы не удалять
		me.PrescriptionType_Code = null;
		me.MedServiceType_SysNick = 'func';
		me.PatientInfoPanel.clearParams();
		switch(me.data.objectPrescribe) {
			case 'EvnCourseProc':
			case 'ProcData':
				me.PrescriptionType_Code = 6;
				me.MedServiceType_SysNick = 'prock';
				break;
			case 'EvnPrescrLabDiag':
			case 'LabDiagData':
				me.PrescriptionType_Code = 11;
				me.MedServiceType_SysNick = 'lab';
				break;
			case 'EvnPrescrFuncDiag':
			case 'FuncDiagData':
				me.PrescriptionType_Code = 12;
				break;
			case 'EvnPrescrConsUsluga':
			case 'ConsUslData':
				me.PrescriptionType_Code = 13;
				break;
			case 'EvnPrescrOperBlock':
			case 'OperBlockData':
				me.PrescriptionType_Code = 7;
				me.MedServiceType_SysNick = 'oper';
				break;
			default:
				Ext6.Msg.alert("Ошибка", "Неизвестный тип назначения: " + me.data.objectPrescribe);
				break;
		}

		if(!data.grid && me.evnPrescrCntr && !data.isKVS)
			me.data.grid = me.evnPrescrCntr.getGridByObject(me.data.objectPrescribe);
		me.MedServiceFilterCombo.getStore().proxy.extraParams.MedServiceType_SysNick = me.MedServiceType_SysNick;
		me.UslugaComplexGrid.getStore().proxy.extraParams.Evn_id = me.data.Evn_id;
		me.UslugaComplexFilterCombo.clearValue();
		me.contractCheckBox.setValue(false);
		this.callParent(arguments);
		me.UslugaComplexFilterCombo.focus();
		if(me.UslugaComplexGrid.getSelection().length != 0) {
			me.UslugaComplexGrid.getSelection().select([]);
		}
		me.addButton.disable();
		me.addButtonWithRec.disable();
	},
	onSprLoad: function(args) {
		var me = this;
		me.LpuFilterCombo.setValue(getGlobalOptions().lpu_id); // по умолчанию своя МО
		me.LpuFilterCombo.fireEvent('change', me.LpuFilterCombo, me.LpuFilterCombo.getValue());
		me.MedServiceFilterCombo.insertAdditionalRecords();
		me.MedServiceFilterCombo.setValue(-1);
		me.UslugaComplexGrid.clearHeaderFilters();
		me.contractCheckBox.setValue(false);
		if (me.queryById('groupByMedService').pressed) {
			me.setGroupByMedService(true);
		} else {
			me.setGroupByMedService(false);
		}
		me.PrescriptionTypeCombo.setValueByCode(me.PrescriptionType_Code);
		me.PatientInfoPanel.clearParams();
		if(me.PersonInfoPanel){
			var pers = me.PersonInfoPanel;
			var	fio = pers.getFieldValue('Person_Surname').charAt(0).toUpperCase()
				+ pers.getFieldValue('Person_Surname').slice(1).toLowerCase()
				+' '+ pers.getFieldValue('Person_Firname').charAt(0).toUpperCase()
				+ pers.getFieldValue('Person_Firname').slice(1).toLowerCase()
				+' '+pers.getFieldValue('Person_Secname').charAt(0).toUpperCase()
				+ pers.getFieldValue('Person_Secname').slice(1).toLowerCase();
			me.PatientInfoPanel.setParams({fio: fio});
		}
		if(me.evnParams && me.evnParams.Diag_Name){
			var Diag_name = '(Диагноз ' + me.evnParams.Diag_Code + ' ' + me.evnParams.Diag_Name + ')';
			me.PatientInfoPanel.setParams({diag: Diag_name})
		}
		me.loadUslugaComplexGrid();
	},
	setGroupByMedService: function(groupByMedService) {
		var me = this;
		if (groupByMedService) {
			me.groupByMedService = true;
			me.groupingFeature.enable();
			me.UslugaComplexGrid.addCls('group-by-medservice');
			me.UslugaComplexGrid.setColumnHidden('location', true);
		} else {
			me.groupByMedService = false;
			me.groupingFeature.disable();
			me.UslugaComplexGrid.removeCls('group-by-medservice');
			me.UslugaComplexGrid.setColumnHidden('location', false);
		}
	},
	groupByMedService: true,
	onlyByContract: false,
	showUslugaComplexCode: true,
	allowLoadGrid: false,
	setUslugaComplexGridFilters: function() {
		var store = this.UslugaComplexGrid.getStore(),
			extraParams = store.proxy.extraParams;
		if (this.groupByMedService) {
			store.setGroupField('Group_id');
			extraParams.groupByMedService = 1;
		} else {
			store.setGroupField(undefined);
			extraParams.groupByMedService = 0;
		}

		if (this.onlyByContract) {
			extraParams.onlyByContract = 1;
		} else {
			extraParams.onlyByContract = 0;
		}

		if (!this.byAllLpu && this.MedServiceFilterCombo.getValue() > 0) {
			extraParams.filterByMedService_id = this.MedServiceFilterCombo.getValue();
		} else {
			extraParams.filterByMedService_id = null;
		}

		if (!this.byAllLpu && this.LpuFilterCombo.getValue() > 0) {
			extraParams.filterByLpu_id = this.LpuFilterCombo.getValue();
			this.byAllLpu = false;
		} else {
			extraParams.filterByLpu_id = null;
			this.byAllLpu = true;
		}

		if (this.UslugaComplexFilterCombo.getValue() > 0) {
			extraParams.filterByUslugaComplex_id = this.UslugaComplexFilterCombo.getValue();
		} else {
			extraParams.filterByUslugaComplex_id = null;
		}

		extraParams.userLpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || null;
	},
	byAllLpu: false, // признак загрузки по всем МО
	loadedByAllLpu: false, // признак, что были загружены данные по всем МО
	loadUslugaComplexGrid: function(options) {
		var me = this;
		if (!options) {
			options = {};
		}
		if (!options.addRecords) {
			options.addRecords = false;
			this.UslugaComplexGrid.getStore().removeAll();
		}

		if (options.byAllLpu) {
			this.byAllLpu = options.byAllLpu;
		} else {
			this.byAllLpu = false;
		}

		this.setUslugaComplexGridFilters();

		if (options.onExpandGroup) {
			this.UslugaComplexGrid.getStore().proxy.extraParams.groupByMedService = 0;
			this.UslugaComplexGrid.getStore().proxy.extraParams.expandOnLoad = 1;
		} else {
			this.UslugaComplexGrid.getStore().proxy.extraParams.expandOnLoad = null;
		}

		var params = {};
		if (options.start) {
			params.start = options.start;
		} else {
			params.start = 0;
		}

		if (options.onExpandGroup) {
			params.limit = 500;
		} else {
			params.limit = 100;
		}

		if (options.MedService_id) {
			params.MedService_id = options.MedService_id;
		}

		if (options.pzm_MedService_id) {
			params.pzm_MedService_id = options.pzm_MedService_id;
		}
		if (options.Resource_id) {
			params.Resource_id = options.Resource_id;
		}
		if(me.PrescriptionTypeCombo.getValue() && me.PrescriptionTypeCombo.getSelectedRecord()){
			var selRec = me.PrescriptionTypeCombo.getSelectedRecord();
			if(selRec)
				this.PrescriptionType_Code = parseInt(selRec.get('PrescriptionType_Code'));
		}
		me.MedServiceType_SysNick = 'func';
		switch (this.PrescriptionType_Code) {
			case 6: // Манипуляции и процедуры
				this.UslugaComplexGrid.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['manproc']);
				me.MedServiceType_SysNick = 'prock';
				break;
			case 7: // Оперативное лечение
				this.UslugaComplexGrid.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['oper']);
				me.MedServiceType_SysNick = 'oper';
				break;
			case 11: // Лабораторная диагностика
				this.UslugaComplexGrid.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['lab']);
				me.MedServiceType_SysNick = 'lab';
				break;
			case 12: // Функциональная диагностика
				this.UslugaComplexGrid.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['func']);
				break;
			case 13: // Консультационная услуга
				this.UslugaComplexGrid.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['consult']);
				break;
			default:
				return false;
				break;
		}

		if(options.updateFields){
			me.MedServiceFilterCombo.getStore().proxy.extraParams.MedServiceType_SysNick = me.MedServiceType_SysNick;
			me.MedServiceFilterCombo.getStore().load({
				callback: function() {
					me.MedServiceFilterCombo.setValue(-1);
				}
			});
		}

		this.UslugaComplexGrid.setColumnHidden('composition_cnt', (this.PrescriptionType_Code && this.PrescriptionType_Code != 11));
		this.UslugaComplexGrid.setColumnHidden('study_target', (this.PrescriptionType_Code && this.PrescriptionType_Code != 12));
		this.UslugaComplexGrid.setColumnHidden('timetable', this.PacketPrescr_id != null);

		this.UslugaComplexGrid.getStore().proxy.extraParams.formMode = 'ExtJS6';

		this.allowLoadGrid = true;

		this.UslugaComplexGrid.getStore().load({
			params: params,
			addRecords: options.addRecords,
			callback: function() {
				me.UslugaComplexFilterCombo.focus();
				if (typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	loadComposition: function(rec) {
		var me = this;

		rec.set('composition', 'loading');
		rec.commit();

		me.evnPrescrCntr.loadUslugaComplexComposition({
			UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id'),
			UslugaComplex_id: rec.get('UslugaComplex_id'),
			Lpu_id: rec.get('Lpu_id')
		}, function(response_obj) {
			if (response_obj.length > 1) {
				rec.set('compositionCntChecked', response_obj.length);
				rec.set('compositionCntAll', response_obj.length);
			}

			rec.set('composition', '');
			rec.commit();
		});
	},
	saveEvnPrescr: function(options) {
		var me = this,
			save_url = null,
			prescr_code = '',
			EvnPrescr_id = null,
			rec = options.rec;

		var MedService_id;
		// Порядок записи(если нет бирки, переходим к следующей в списке): бирка услуги у ПЗ, бирка ПЗ, бирка услуги у службы, бирка службы
		// Пробуем записаться на ПЗ
		if(rec.get('pzm_MedService_id'))
			MedService_id = rec.get('pzm_MedService_id');
		// Берем id службы на чью бирку записываемся, если нет записи на ПЗ
		if(!MedService_id && rec.get('ttms_MedService_id'))
			MedService_id = rec.get('ttms_MedService_id');
		// Если и бирки у службы нет нет, только тогда записываем на саму службу
		if(!MedService_id)
			MedService_id = rec.get('MedService_id');
		var params = {
			PersonEvn_id: me.data.PersonEvn_id,
			Server_id: me.data.Server_id,
			parentEvnClassSysNick: "EvnVizitPL",
			DopDispInfoConsent_id: '',
			StudyTarget_id: rec.get('StudyTarget_id') ? rec.get('StudyTarget_id') : 2, // Тип
			MedService_id: MedService_id,
			UslugaComplex_id: rec.get('UslugaComplex_id')
		};

		switch (me.PrescriptionType_Code) {
			case 6:
				var date = new Date();
				if (rec.get('TimetableMedService_begTime')) {
					date = Ext6.Date.parse(rec.get('TimetableMedService_begTime'), 'd.m.Y H:i');
				}

				var formParams = {
					EvnCourseProc_id: null,
					EvnCourseProc_pid: me.data.Evn_id,
					PersonEvn_id: params.PersonEvn_id,
					Server_id: params.Server_id,
					MedPersonal_id: me.data.MedPersonal_id,
					LpuSection_id: me.data.LpuSection_id,
					MedService_id: params.MedService_id,
					parentEvnClass_SysNick: params.parentEvnClassSysNick,
					UslugaComplex_id: params.UslugaComplex_id,
					StudyTarget_id: params.StudyTarget_id,
					EvnCourseProc_setDate: date.format('d.m.Y'),
					EvnCourseProc_setTime: date.format('H:i')
				};

				var callback = function(response) {
					if (response && response.EvnPrescrProc_id0) {
						EvnPrescr_id = response.EvnPrescrProc_id0;

						rec.set('EvnPrescr_id', EvnPrescr_id);
						me.callback({'EvnPrescr_id': EvnPrescr_id,'action': 'add'});
						if (typeof options.callback == 'function') {
							options.callback(EvnPrescr_id);
						}
						rec.set('UslugaComplexMedService_HasPrescr', true);

						if (!options.withoutInfoMsg) sw4.showInfoMsg({
							panel: me,
							type: 'warning',
							text: 'Услуга добавлена. Требуется запись.'
						});
					} else {
						rec.set('UslugaComplexMedService_HasPrescr', false); // не удалось сохранить, убираем галку
					}

					rec.commit();
				};

				getWnd('swEvnCourseProcEditWindow').show({
					formParams: formParams,
					callback: callback
				});
				break;
			case 7:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrOperBlock';
				prescr_code = 'EvnPrescrOperBlock';
				params.UslugaComplex_id = rec.get('UslugaComplex_id');
				break;
			case 11:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrLabDiag';
				prescr_code = 'EvnPrescrLabDiag';
				params.UslugaComplex_id = rec.get('UslugaComplex_id');
				params.EvnPrescrLabDiag_uslugaList = rec.get('UslugaComplex_id');
				params.UslugaComplexMedService_pid = rec.get('UslugaComplexMedService_id');
				break;
			case 12:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrFuncDiag';
				prescr_code = 'EvnPrescrFuncDiag';
				params.EvnPrescrFuncDiag_uslugaList = rec.get('UslugaComplex_id');
				break;
			case 13:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrConsUsluga';
				prescr_code = 'EvnPrescrConsUsluga';
				params.UslugaComplex_id = rec.get('UslugaComplex_id');
				break;
		}

		if (!save_url) {
			return false;
		}

		params[prescr_code +'_id'] = null;
		params[prescr_code +'_pid'] = me.data.Evn_id;
		params[prescr_code +'_IsCito'] = rec.get('UslugaComplex_IsCito') ? 'on' : 'off';
		params[prescr_code +'_setDate'] = me.data.Evn_setDate;
		params[prescr_code +'_Descr'] = '';

		if(!options.loadMask) {
			me.mask('Сохранение назначения');
		} else {
			me.mask(options.loadMask);
		}
		if (me.PrescriptionType_Code == 11) {
			Ext6.Ajax.request({
				url: '/?c=MedService&m=loadCompositionMenu',
				success: function(response) {
					var list = [];
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.length > 0) {
							for (var i = 0; i < response_obj.length; i++) {
								list.push(response_obj[i].UslugaComplex_id);
							}
						}
					}
					if(rec.get('UslugaComposition')) list = rec.get('UslugaComposition');//todo: если все норм, то сделать без лишней загрузки полного списка
					if (list.length > 0) {
						params.EvnPrescrLabDiag_uslugaList = list.toString();
						params.EvnPrescrLabDiag_CountComposit = list.length;
					}
					Ext6.Ajax.request({
						url: save_url,
						params: params,
						callback: function(opt, success, response) {
							if (response && response.responseText) {
								var response_obj = Ext6.JSON.decode(response.responseText);

								if (6 == me.PrescriptionType_Code) {
									EvnPrescr_id = response_obj[prescr_code +'_id0'];
								} else {
									EvnPrescr_id = response_obj[prescr_code +'_id'];
								}

								if (EvnPrescr_id) {
									rec.set('EvnPrescr_id', EvnPrescr_id);
									rec.set('EvnPrescr_pid', me.data.Evn_id);
									me.callback({'EvnPrescr_id': EvnPrescr_id,'action': 'add'});
									if (typeof options.callback == 'function') {
										options.callback(EvnPrescr_id);
									}
									rec.set('UslugaComplexMedService_HasPrescr', true);
								} else {
									rec.set('UslugaComplexMedService_HasPrescr', false); // не удалось сохранить, убираем галку
								}
							}
							rec.commit();
							if(!options.loadMask) {
								me.unmask();
							}

							if (!options.withoutInfoMsg) {
								sw4.showInfoMsg({
									panel: me,
									type: 'warning',
									text: 'Услуга добавлена. Требуется запись.'
								});
							}
						}
					});
				},
				params: {
					UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id'),
					UslugaComplex_pid: rec.get('UslugaComplex_id'),
					Lpu_id: rec.get('Lpu_id')
				}
			});
		} else
			Ext6.Ajax.request({
			url: save_url,
			callback: function(opt, success, response) {
				if (response && response.responseText) {
					var response_obj = Ext6.JSON.decode(response.responseText);

					if (6 == me.PrescriptionType_Code) {
						EvnPrescr_id = response_obj[prescr_code +'_id0'];
					} else {
						EvnPrescr_id = response_obj[prescr_code +'_id'];
					}

					if (EvnPrescr_id) {
						rec.set('EvnPrescr_id', EvnPrescr_id);
						me.callback({'EvnPrescr_id': EvnPrescr_id,'action': 'add'});
						if (typeof options.callback == 'function') {
							options.callback(EvnPrescr_id);
						}
						rec.set('UslugaComplexMedService_HasPrescr', true);
					} else {
						rec.set('UslugaComplexMedService_HasPrescr', false); // не удалось сохранить, убираем галку
					}
				}
				rec.commit();
				if(!options.loadMask) {
					me.unmask();
				}

				if (!options.withoutInfoMsg) {
					sw4.showInfoMsg({
						panel: me,
						type: 'warning',
						text: 'Услуга добавлена. Требуется запись.'
					});
				}

				if (me.PrescriptionType_Code == 12 && params.StudyTarget_id == 2) {
					var code = rec.get('UslugaComplex_Code'),
						name = rec.get('UslugaComplex_Name'),
						type;
					if (code.match(/(A06|A\.06)/gm)){
						type = 'error';
					} else {
						type = 'warning';
					}
					sw4.showInfoMsg({
						panel: me,
						type: type,
						bottom: 55,
						text: 'Внимание! Для "' + name + '" цель исследования по умолчанию "2. Диагностическая"!'
					});
				}
			},
			params: params
		});
	},
	saveCheckedUslInComposit: function(rec,arrCheckedUsl){
		var me = this,
			uslArrStr = arrCheckedUsl.join(',');

		if(me.data && me.data.Server_id && me.data.PersonEvn_id){
			me.mask('Сохранение состава услуги...');

			Ext6.Ajax.request({
				params: {
					Server_id: me.data.Server_id,
					PersonEvn_id: me.data.PersonEvn_id,
					EvnPrescrLabDiag_id: rec.get('EvnPrescr_id'),
					UslugaComplex_id: rec.get('UslugaComplex_id'),
					EvnPrescrLabDiag_pid: rec.get('EvnPrescr_pid'),
					EvnUslugaOrder_id: rec.get('EvnUslugaPar_id'),
					EvnUslugaOrder_UslugaChecked: uslArrStr,
					EvnPrescrLabDiag_uslugaList: uslArrStr,
					EvnDirection_id: rec.get('EvnDirection_id'),
					UslugaComplexContent_ids: Ext.util.JSON.encode(arrCheckedUsl),
					EvnPrescrLabDiag_CountComposit: arrCheckedUsl.length,
					UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id'),
					isExt6: 1
				},
				callback: function (opt, success, response) {
					me.unmask();
					if(me.callback) me.callback();
				},
				url: '/?c=EvnPrescr&m=saveEvnPrescrLabDiag'
			});
		}
		else{
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: Ext6.emptyFn,
				icon: Ext6.Msg.WARNING,
				msg: langs('Ошибка получения данных'),
				title: ERR_WND_TIT
			});
		}
	},
	cancelEvnPrescr: function(rec) {
		var me = this;

		rec.set('UslugaComplexMedService_HasPrescr', true); // пока вернём
		rec.commit();

		sw.Promed.EvnPrescr.cancel({
			ownerWindow: me,
			withoutQuestion: true,
			getParams: function(){
				return {
					EvnPrescr_id: rec.get('EvnPrescr_id'),
					parentEvnClass_SysNick: 'EvnVizitPL',
					PrescriptionType_id: me.PrescriptionType_Code
				};
			},
			callback: function(){
				me.callback();
				rec.set('UslugaComplexMedService_HasPrescr', false); // а теперь уберём
				me.parentPanel.getController().loadGrids();
				rec.set('EvnDirection_id', null);
				rec.set('EvnPrescr_id', null);
				rec.commit();
				me.expandGroup(rec.get('Group_id'));
			}
		});

		return true;
	},
	cancelEvnDirectionLis: function(rec) {
		//взято с грида "назначения и направления"(EvnPrescribePanelCntr)
		var me = this,
			personPanel = me.PersonInfoPanel,
			grid = me.UslugaComplexGrid,
			parentPanel = me.parentPanel;
		
		var params = {
			cancelType: 'cancel',
			ownerWindow: grid,
			EvnDirection_id: rec.get('EvnDirection_id'),
			DirType_id: 10,
			DirType_Code: "9",
			TimetableGraf_id: rec.get('TimetableGraf_id'),
			TimetableMedService_id: rec.get('TimetableMedService_id'),
			TimetableResource_id: rec.get('TimetableResource_id'),
			TimetableStac_id: rec.get('TimetableStac_id'),
			EvnQueue_id: rec.get('EvnQueue_id'),
			allowRedirect: false,
			userMedStaffFact: parentPanel.data.userMedStaffFact,
			personData: {
				Person_id: parentPanel.data.Person_id,
				Server_id: parentPanel.data.Server_id,
				PersonEvn_id: parentPanel.data.PersonEvn_id
			},
			callback: function() {
				grid.unmask();
				parentPanel.getController().loadGrids();
				rec.set('EvnDirection_id', null);
				me.expandGroup(rec.get('Group_id'));
			}
		};
		if (personPanel) {
			params.personData.Person_Birthday = personPanel.getFieldValue('Person_Birthday');
			params.personData.Person_Surname = personPanel.getFieldValue('Person_Surname');
			params.personData.Person_Firname = personPanel.getFieldValue('Person_Firname');
			params.personData.Person_Secname = personPanel.getFieldValue('Person_Secname');
			params.personData.Person_IsDead = personPanel.getFieldValue('Person_IsDead');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}
		sw.Promed.Direction.cancel(params);
	},
	cancelEvnDirection: function(rec) {
		var me = this;

		if (!rec.get('EvnDirection_id')) {
			return false;
		}

		if(rec.get('PrescriptionType_Code') == 11 && getRegionNick() == 'perm') {
			me.cancelEvnDirectionLis(rec);
			return ;
		}

		me.mask('Получение данных направления');
		Ext6.Ajax.request({
			url: '/?c=EvnDirection&m=getEvnDirectionData',
			callback: function(opt, success, response) {
				me.unmask();
				if (response && response.responseText) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if (response_obj.success) {
						sw.Promed.Direction.cancel({
							cancelType: 'cancel',
							ownerWindow: me,
							TimetableMedService_id: response_obj.TimetableMedService_id,
							TimetableResource_id: response_obj.TimetableResource_id,
							EvnQueue_id: response_obj.EvnQueue_id,
							EvnDirection_id: rec.get('EvnDirection_id'),
							callback: function(cfg) {
								me.callback();
								me.parentPanel.getController().loadGrids();
								rec.set('EvnDirection_id', null);
								rec.commit();
								me.expandGroup(rec.get('Group_id'));
							}
						});
					}
				}
			},
			params: {
				EvnDirection_id: rec.get('EvnDirection_id')
			}
		});

		return true;
	},
	onLoadGrid: function(store, records, successful, operation, eOpts) {
		log('onLoadGrid', store, records, successful, operation, eOpts);
		var me = this;
		var countAllPanel = me.down('#countAll');
		if(!records) return true;

		if(me.infoMsg)
			me.infoMsg.destroy();

		me.getMedServicesQueueAccess(records);

		me.UslugaComplexGrid.getEl().query(".show-more-div").forEach(function(showMoreDiv) {
			showMoreDiv.remove();
		});
		this.UslugaComplexGrid.UslugaConflictsList = new Map();

		if (me.byAllLpu) {
			me.loadedByAllLpu = true;
			me.byAllLpu = false;
			me.setUslugaComplexGridFilters();
		} else {
			me.loadedByAllLpu = false;
		}

		if (operation && operation.request && operation.request.config && operation.request.config.params && operation.request.config.params.expandOnLoad) {
			if (records.length > 0) {
				var Group_id = records[0].data.Group_id;
				var temperStore = me.UslugaComplexGrid.getStore();
				var undeleteIds = [];
				for(var k in records) {
					if (records[k].data && records[k].data.id) {
						if(records[k].get('MedService_Nick') == '') records[k].set('MedService_Nick', temperStore.findRecord('Unique_id', records[k].get('Unique_id')).get('MedService_Nick'));
						undeleteIds.push(records[k].data.id);
					}
				}
				// убираем из грида записи по группе, которые были до загрузки
				var recordsToRemove = [];
				me.UslugaComplexGrid.getStore().findBy(function(rec) {
					if (!rec.get('id').inlist(undeleteIds) && (rec.get('Group_id') == Group_id)) {
						recordsToRemove.push(rec);
					}
				});
				me.UslugaComplexGrid.getStore().remove(recordsToRemove);

				if(!me.groupingFeature.disabled && undeleteIds.length != 0) {
					me.groupingFeature.expand(Group_id);
				}
			}
		} else {
			if (me.groupByMedService) {
				me.groupingFeature.collapseAll();
			} else {
				me.groupingFeature.expandAll();
			}
		}

		if (records && records.length >= 100) {
			var cont = me.UslugaComplexGrid.getEl().query(".x6-grid-item-container");
			if (cont && cont[0]) {
				var showMoreDiv = document.createElement('div');
				showMoreDiv.innerHTML = "<a href='#' onClick='Ext6.getCmp(\"" + me.id + "\").showMore(\"" + me.loadedByAllLpu + "\");' class='show-more-button'>Показать ещё</a>";
				showMoreDiv.className = "show-more-div";
				Ext6.get(cont[0]).append(showMoreDiv);
			}
		} else {
			var cont = me.UslugaComplexGrid.getEl().query(".x6-grid-item-container");
			if (cont && cont[0]) {
				var s = "";
				if (me.UslugaComplexGrid.getStore().getCount() == 0) {
					s = s + "<div class='not-found'>Услуга не найдена</div><br>";
				}
				if (!this.loadedByAllLpu && !this.onlyByContract) {
					s = s + "<a href='#' onClick='Ext6.getCmp(\"" + me.id + "\").showByAll();' class='show-more-button'>Показать по всем МО</a>";
				}

				if (s.length > 0) {
					var showMoreDiv = document.createElement('div');
					showMoreDiv.innerHTML = s;
					showMoreDiv.className = "show-more-div";
					Ext6.get(cont[0]).append(showMoreDiv);
				}
			}
		}
		var htmlCount = 'Найдено услуг: <span style="color: #000;">'+store.getCount()+'</span>';
		if (me.groupByMedService && store.getGroups() && store.getGroups().length) {
			htmlCount = 'Найдено служб: <span style="color: #000;">'+store.getGroups().length+'</span>';
		}
		countAllPanel.setHtml(htmlCount);
	},
	showMore: function(byAllLpu) {
		this.loadUslugaComplexGrid({
			addRecords: true,
			start: this.UslugaComplexGrid.getStore().getCount(),
			byAllLpu: byAllLpu
		});
	},
	showByAll: function() {
		this.loadUslugaComplexGrid({
			byAllLpu: true
		});
	},
	showTimetableMenu: function(link, key) {
		this.showTimetableSelectionPanel(key);
	},
	showTimetableSelectionPanel: function(key) {
		var me = this;
		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		if(!rec) {
			return false;
		}
		var withResource = rec.get('withResource');
		var begTime = withResource == 1 ? rec.get('TimetableResource_begTime') : rec.get('TimetableMedService_begTime');

		var medServ_id_save = rec.get('ttms_MedService_id');
		var uslugaComplexMedServ_id_save = rec.get('UslugaComplexMedService_id');
		var pzm_MedServ_id_save = rec.get('pzm_MedService_id');
		if(withResource == 1)
			rec.set('ttms_MedService_id', null);

		if(medServ_id_save)
			rec.set('UslugaComplexMedService_id', null);

		if(rec.get('ttms_MedService_id') !== pzm_MedServ_id_save && rec.get('pzm_UslugaComplexMedService_id') === null)
			rec.set('pzm_MedService_id', null);

		var params = {
			rec: rec,
			StartDay: begTime,
			target: me.UslugaComplexGrid.getView(),
			align: 'c-c?',
			toQueueAccess: me.checkInQueueEnable(rec)
		};

		me.TimetableSelectionPanel.show(params);

		rec.set('ttms_MedService_id', medServ_id_save);
		rec.set('pzm_MedService_id', pzm_MedServ_id_save);
		rec.set('UslugaComplexMedService_id', uslugaComplexMedServ_id_save);
	},
	showEvnDirectionMenu: function(link, key) {
		var me = this;

		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		if (!rec) {
			return false;
		}

		var begTime = rec.get('withResource') == 1 ? rec.get('TimetableResource_begTime') : rec.get('TimetableMedService_begTime');

		me.timetableMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Отменить назначение',
				handler: function() {
					me.cancelEvnPrescr(rec);
				}
			}, {
				text: begTime ? 'Отменить запись на это время' : 'Отменить постановку в очередь',
				handler: function() {
					me.cancelEvnDirection(rec);
				}
			}]
		});

		me.timetableMenu.showBy(link);
	},
	showSelectedCellsMenu: function(link, key) {
		var me = this;
		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		var cells = me.getEl().query("[data-cell='cell_composite_"+rec.get('Unique_id')+"']");
		if(cells.length)
			link = cells[0];
		var cell_comp = Ext6.get(link);
		if (!rec) {
			return false;
		}
		me.selRec(link, key);

		me.selectedCellsMenu = Ext6.create('Ext6.menu.Menu', {
			resizable: false,
			minWidth: 16,
			maxWidth: 250,
			clickTTRec: null,
			StartDay: null,
			shadow: false,
			cls: 'selectedDateTime',
			style: {
				borderRadius: '2px;',
				border: '1px solid #C5C5C5;',
				boxShadow: '0 3px 6px rgba(0,0,0, .16) !important'
			},
			items: [{
				text: 'Записать на это время',
				cls: 'selectedDateTime-menu',
				style: {
					'padding-left': '6px',
					'padding-right': '10px'
				},
				handler: function() {
					me.applySelectedPrescribeWithRecord([rec], me, true);
				}
			}, {
				text: 'Открыть расписание на день',
				name: 'openTT',
				cls: 'selectedDateTime-menu',
				style: {
					'padding-left': '6px',
					'padding-right': '10px'
				},
				handler: function (e) {
					me.showTimetableSelectionPanel(key);
				}
			}, {
				text: 'Поставить в очередь',
				itemId: 'recToQueue',
				cls: 'selectedDateTime-menu',
				style: {
					'padding-left': '6px',
					'padding-right': '10px'
				},
				handler: function (e) {
					me.doApplyToQueue(link, key);
				}
			}
			]
		});

		var queueBtn = me.selectedCellsMenu.queryById('recToQueue');

		if(me.checkInQueueEnable(rec)) {
			queueBtn.enable();
		}
		else {
			queueBtn.disable();
		}

		me.selectedCellsMenu.showBy(cell_comp);
	},
	checkMedServiceQueue: function(ms, notQueueSel) {
		var me = this;
		if(!ms) return true;
		if (!notQueueSel)
			return me.medServicesQueueAccessMap.has(ms) && me.medServicesQueueAccessMap.get(ms) != 1;
		return me.medServicesQueueAccessMap.has(ms) && me.medServicesQueueAccessMap.get(ms) == 3;
	},
	checkInQueueEnable: function(rec) {
		var me = this;
		if(!me.medServicesQueueAccessMap) return false;
		let withResource = rec.get('withResource');
		var notQueueSelected = withResource == 1 ? rec.get('TimetableResource_begTime') : rec.get('TimetableMedService_begTime');
		
		if(withResource == 1) return me.checkMedServiceQueue(rec.get('MedService_id'), notQueueSelected) || rec.get('UslugaComplex_IsCito');

		var queueAccess = me.checkMedServiceQueue(rec.get('MedService_id'), notQueueSelected);
		if(queueAccess) queueAccess = me.checkMedServiceQueue(rec.get('pzm_MedService_id'), notQueueSelected);
		if(queueAccess) queueAccess = me.checkMedServiceQueue(rec.get('lab_MedService_id'), notQueueSelected);
		if(queueAccess) queueAccess = me.checkMedServiceQueue(rec.get('ttms_MedService_id'), notQueueSelected);
		
		return  queueAccess || rec.get('UslugaComplex_IsCito');
	},
	showCompositionMenu: function(link, key) {
		var me = this;
		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		var cells = me.getEl().query("[data-cell='cell_composite_"+rec.get('Unique_id')+"']");
		if(cells.length)
			link = cells[0];
		var cell_comp = Ext6.get(link);
		var checked = false;
		if (!rec) {
			return false;
		}

		if (!rec.compositionMenu) {
			me.mask('Получение состава услуги...');
			var url = '/?c=MedService&m=loadCompositionMenu';
			// Если уже существует запись на услугу - у нее есть состав (заявка), грузим его
			// В заявке будут выделены галочкой услуги, которые выбраны
			if(me.PrescriptionType_Code == 11 && rec.get('EvnDirection_id'))
				url = '/?c=EvnLabRequest&m=loadCompositionMenu';
			else
				checked = true; // Если грузим состав услуги как таковой - выделяем весь состав

			Ext6.Ajax.request({
				params: {
					UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id'),
					UslugaComplex_pid: rec.get('UslugaComplex_id'),
					Lpu_id: rec.get('Lpu_id'),
					EvnDirection_id: rec.get('EvnDirection_id'),
					EvnPrescr_id: rec.get('EvnPrescr_id'),
					isExt6: 1
				},
				callback: function(opt, success, response) {
					me.unmask();
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.length > 0) {
							var menuHeight = response_obj.length > 10? 490: undefined;
							rec.compositionMenu = Ext6.create('Ext6.menu.Menu', {
								cls: 'timetable-menu',
								minWidth: 330,
								height: menuHeight,
								items: [],
								buttons: [{
									xtype: 'checkbox',
									boxLabel: 'Снять выделение',
									checked: true,
									handler: function(check, val){
										var boxLabel = val?'Снять выделение':'Выделить всё';
										check.setBoxLabel(boxLabel);
										if(rec && rec.compositionMenu){
											rec.compositionMenu.items.each(function(item){
												if (item.xtype == 'menucheckitem') {
													item.setChecked(val);
												}
											});
										}
									}
								},'->', {
									text: 'Отмена',
									handler: function() {
										rec.compositionMenu.hide();
									}
								}, {
									text: 'Применить',
									cls: 'flat-button-primary',
									handler: function() {
										rec.compositionMenu.hide();

										// надо посчитать кол-во услуг отмеченых в меню и проставить в грид
										var compositionCntAll = 0;
										var compositionCntChecked = 0,
											arrCheckedUsl = [];
										rec.compositionMenu.items.each(function(item){
											if (item.xtype == 'menucheckitem') {
												compositionCntAll++;
												if (item.checked) {
													compositionCntChecked++;
													arrCheckedUsl.push(item.UslugaComplex_id);
												}
											}
										});
										rec.set('UslugaComposition', arrCheckedUsl);
										if(!checked){
											me.saveCheckedUslInComposit(rec,arrCheckedUsl);
											delete rec.compositionMenu;
										}

										rec.set('EvnPrescr_CountComposit', compositionCntChecked);
										rec.set('compositionCntChecked', compositionCntChecked);
										rec.set('compositionCntAll', compositionCntAll);
										//rec.set('composition_cnt', compositionCntAll);
										//rec.commit();
										//me.UslugaComplexGrid.reconfigure();

									}
								}]
							});

							var checked_count = 0;
							var ichecked = false;
							for (var i = 0; i < response_obj.length; i++) {
								ichecked = (
									checked
									|| (response_obj[i].checkedUsl && response_obj[i].checkedUsl == true) // Если через '/?c=MedService&m=loadCompositionMenu';
									|| (response_obj[i].UslugaComplex_InRequest && response_obj[i].UslugaComplex_InRequest > 0) // через  '/?c=EvnLabRequest&m=loadCompositionMenu';
								);
								if(ichecked) checked_count++;
								rec.compositionMenu.add({
									text: response_obj[i].UslugaComplex_Name,
									UslugaComplex_id: response_obj[i].UslugaComplex_id,
									hideLabel: true,
									xtype: 'menucheckitem',
									rec: rec,
									checked: ichecked
								});
							}
							rec.set('compositionCntChecked', checked_count);
							//rec.set('compositionCntAll', response_obj.length);
							//rec.set('composition_cnt', response_obj.length);
							rec.commit();
							//me.UslugaComplexGrid.reconfigure();
							rec.compositionMenu.add({
								xtype: 'menuseparator'
							});
							//Что-то меняется ептыж в этой тупой ячейке, и она уже с позицией xy: 0 0
							cells = me.getEl().query("[data-cell='cell_composite_"+rec.get('Unique_id')+"']");
							if(cells.length)
								link = cells[0];
							cell_comp = Ext6.get(link);
							rec.compositionMenu.showBy(cell_comp);
						}
					}
				},
				url: url
			});
		} else {
			rec.compositionMenu.showBy(cell_comp);
		}
	},
	openTTMSScheduleRecordPanel: function(key) {
		var me = this;
		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		if (!rec) {
			return false;
		}

		if (!rec.get('EvnPrescr_id')) {
			// сперва сохраняем назначение
			me.saveEvnPrescr({
				rec: rec,
				callback: function(EvnPrescr_id) {
					me.callback({'EvnPrescr_id': EvnPrescr_id});
					// затем открываем расписание
					me.openTTMSScheduleRecordPanel(key);
				}
			});

			return false;
		}

		// переходим к расписанию
		me.evnPrescrCntr.onLoadStoresFn = function(){
			me.evnPrescrCntr.openSpecification('TTMSScheduleRecordPanel', me.data.grid, rec);
		};
		var arrGrids = [];
		if(me.data && me.data.grid)
			arrGrids = [me.data.grid.objectPrescribe];
		me.evnPrescrCntr.loadGrids(arrGrids);
	},
	getTimetableNext: function(rec) {
		var me = this;
		if (!Ext6.isEmpty(rec.get('TimetableMedService_id')) || !Ext6.isEmpty(rec.get('TimetableResource_id'))) {
			var TimetableMedService_id = rec.get('TimetableMedService_id');
			var TimetableResource_id = rec.get('TimetableResource_id');
			// если в списке есть ещё записи с такими же бирками, то надо запросить следующее время
			var count = 0;
			this.UslugaComplexGrid.getStore().each(function(record) {
				if (
					Ext6.isEmpty(record.get('EvnDirection_id')) && (
						(TimetableMedService_id && record.get('TimetableMedService_id') == TimetableMedService_id)
						|| (TimetableResource_id && record.get('TimetableResource_id') == TimetableResource_id)
					)
				) {
					count++;
				}
			});

			if (count > 0) {
				me.mask('Получение следующей свободной бирки...');
				Ext6.Ajax.request({
					url: '/?c=MedService&m=getTimetableNext',
					callback: function(opt, success, response) {
						me.unmask();

						if (response && response.responseText) {
							var response_obj = Ext6.JSON.decode(response.responseText);
							if (response_obj.success) {
								me.UslugaComplexGrid.getStore().each(function(record) {
									if (
										Ext6.isEmpty(record.get('EvnDirection_id')) && (
											(TimetableMedService_id && record.get('TimetableMedService_id') == TimetableMedService_id && response_obj.TimetableMedService_begTime >= new Date())
											|| (TimetableResource_id && record.get('TimetableResource_id') == TimetableResource_id && response_obj.TimetableResource_begTime >= new Date())
										)
									) {
										record.set('TimetableMedService_id', response_obj.TimetableMedService_id);
										record.set('TimetableMedService_begTime', response_obj.TimetableMedService_begTime);
										record.set('TimetableResource_id', response_obj.TimetableResource_id);
										record.set('TimetableResource_begTime', response_obj.TimetableResource_begTime);
										record.commit();
									}
								});
							}
						}
					},
					params: {
						TimetableMedService_id: TimetableMedService_id,
						TimetableResource_id: TimetableResource_id
					}
				});
			}
		}
	},
	saveEvnDirection: function(rec, params) {
		var me = this;
		var begTime = rec.get('withResource') == 1 ? rec.get('TimetableResource_begTime') : rec.get('TimetableMedService_begTime');
		if(rec.get('UslugaComplex_IsCito') && !begTime) {
			var cb = function(success, response, selectedRecords, form, addedRecords) {
				me.addTTMSDop(rec, rec.object, selectedRecords, form, addedRecords);
				addedRecords.push(rec);
			};
			me.setCito(rec, params.selectedRecords, params.form, params.addedRecords, cb);
		} else {
			me.evnPrescrCntr.saveEvnDirection(params);
		}
	},
	checkEvnDirection: function(params) {
		var me = this;

		if (params.PrescriptionType_Code == 11 && !params.modeDirection) {
			Ext6.Ajax.request({
				url: '/?c=EvnDirection&m=checkEvnDirectionExists',
				params: {
					Person_id: me.parentPanel.data.Person_id,
					MedService_id: params.MedService_id,
					UslugaComplex_id: params.UslugaComplex_id,
					EvnDirection_pid: params.EvnPrescr_pid,
					EvnPrescr_id: params.EvnPrescr_id
				},
				callback: function (swn, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						params.modeDirection = true;
						if (response_obj.EvnDirections) {
							// если нашли направление задаём вопрос
							getWnd('swPrescDirectionIncludeWindow').show({
								EvnDirections: response_obj.EvnDirections,
								callback: function (data) {
									if (data.include == 'yes') {
										params.IncludeInDirection = data.EvnDirection_id;
										params.EvnDirection_id = data.EvnDirection_id;
										params.UslugaList = response_obj.UslugaList;
										me.includeToDirection(params);
										return true;
									}
									me.saveEvnDirection(params.rec, params);
								},
								onHideED: function () {
									me.saveEvnDirection(params.rec, params);
								}
							});
						} else {
							me.saveEvnDirection(params.rec, params);
						}
					}
				}
			});
		} else {
			me.saveEvnDirection(params.rec, params);
		}
	},
	selRec: function (link, key) {
		var me = this;
		var grid = me.UslugaComplexGrid;
		var selection = grid.getSelection();

		var item = grid.getStore().findRecord('Unique_id', key);

		if(!selection.includes(item)) {
			selection.push(item);
			grid.setSelection(selection);
		}
	},
	doApplyToQueue: function(link, key) {
		var me = this;
		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		rec.set('TimetableResource_id', null);
		rec.set('TimetableResource_begTime', null);
		rec.set('TimetableMedService_id', null);
		rec.set('TimetableMedService_begTime', null);
		rec.set('not_to_queue', false);
		rec.set('nolimit', false);
		me.applySelectedPrescribeWithRecord([rec], me, true);
	},
	includeToDirection: function (prescrParams) {
		var me = this;
		me.UslugaComplexGrid.setSelection(null);
		me.mask('Объединение назначений');
		Ext6.Ajax.request({
			url: '/?c=EvnDirection&m=includeToDirection',
			params: {
				EvnDirection_id: prescrParams.EvnDirection_id,
				pmUser_id: prescrParams.pmUser_id,
				Lpu_id: prescrParams.Lpu_id,
				UslugaList: prescrParams.UslugaList,
				MedService_id: prescrParams.MedService_id,
				EvnPrescr_id: prescrParams.EvnPrescr_id,
				Evn_id: prescrParams.EvnPrescr_pid,
				UslugaComplex_id: prescrParams.UslugaComplex_id,
				UslugaComplexMedService_pid: prescrParams.UslugaComplexMedService_pid,
				checked: prescrParams.checked
			},
			callback: function (swn, success, response) {
				me.unmask();
				me.expandGroup(prescrParams.Group_id);
				me.parentPanel.getController().loadGrids();//переподгружаем гриды "Направления и назначения" в ЭМК, чтобы обновить информацию
			}
		});
	},
	doApply: function(options) {
		if (!options) {
			options = {};
		}
		var me = this,
			recData = false;

		var rec = options.rec;
		if (!rec) {
			rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', options.key);
			if (!rec) {
				return false;
			}
		}
		// @todo а нужна ли строка ниже?
		recData = rec.getData();
		if (options.toQueue) {
			rec.set('TimetableMedService_id', null);
			rec.set('TimetableMedService_begTime', null);
			rec.set('ttms_MedService_id', null);
			rec.set('TimetableResource_id', null);
			rec.set('TimetableResource_begTime', null);
			rec.set('Resource_id', null);
			rec.set('Resource_Name', null);
			rec.set('ttr_Resource_id', null);
			rec.commit();
		}

		if (!rec.get('EvnPrescr_id')) {
			// сперва сохраняем назначение
			me.saveEvnPrescr({
				rec: rec,
				withoutInfoMsg: true,
				callback: function() {
					// затем снова направление пытаемся сохранить
					me.doApply(options);
				}
			});

			return false;
		}
		var params = rec.data;
		params.PrescriptionType_Code = me.PrescriptionType_Code;
		params.onSaveEvnDirection = function(data) {
			//rec = me.UslugaComplexGrid.getStore().findRecord('Unique_id', options.key);
			if(data && data.Error_Msg){
				// @todo Быстрое решение - нужно убрать
				if(recData){
					rec.set('TimetableMedService_id', recData.TimetableMedService_id);
					rec.set('TimetableMedService_begTime', recData.TimetableMedService_begTime);
					rec.set('ttms_MedService_id', recData.ttms_MedService_id);
					rec.set('TimetableResource_id', recData.TimetableResource_id);
					rec.set('TimetableResource_begTime', recData.TimetableResource_begTime);
					rec.set('Resource_id', recData.Resource_id);
					rec.set('Resource_Name', recData.Resource_Name);
					rec.set('ttr_Resource_id', recData.ttr_Resource_id);
				}
				rec.set('not_to_queue', true);
				rec.commit();
				sw4.showInfoMsg({
					panel: me,
					type: 'error',
					bottom: 55,
					text: 'Постановка в очередь ограничена. Обратитесь к администратору МО'
				});
			} else if(rec && data && data.EvnDirection_id){
				rec.set('EvnDirection_id', data.EvnDirection_id);
				rec.commit();
				me.getTimetableNext(rec);
				if(options.callback &&  typeof options.callback == 'function') {
					options.callback(rec);
				}
			}
		};
		me.evnPrescrCntr.saveEvnDirection(params);
	},
	getTimetableNoLimit: function(link, key) {
		var me = this;

		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		if (!rec) {
			return false;
		}

		me.mask('Получение первой свободной бирки...');
		Ext6.Ajax.request({
			url: '/?c=MedService&m=getTimetableNoLimit',
			callback: function(opt, success, response) {
				me.unmask();
				rec = me.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
				if (response && response.responseText) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if (response_obj.success) {
						rec.set('TimetableMedService_id', response_obj.TimetableMedService_id);
						rec.set('TimetableMedService_begTime', response_obj.TimetableMedService_begTime);
						rec.set('ttms_MedService_id', response_obj.ttms_MedService_id);
						rec.set('TimetableResource_id', response_obj.TimetableResource_id);
						rec.set('TimetableResource_begTime', response_obj.TimetableResource_begTime);
						rec.set('Resource_id', response_obj.Resource_id);
						rec.set('Resource_Name', response_obj.Resource_Name);
						rec.set('ttr_Resource_id', response_obj.ttr_Resource_id);
					}
					rec.set('nolimit', 1);
					rec.commit();
					//me.UslugaComplexGrid.reconfigure();
				}
			},
			params: {
				UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id'),
				pzm_MedService_id: rec.get('pzm_MedService_id'),
				Resource_id: rec.get('Resource_id'),
				PrescriptionType_Code: me.PrescriptionType_Code
			}
		});
	},
	expandAllGroups: function() {
		var me = this;
		var metaGroupCache = me.groupingFeature.getCache();
		var groupNames = [];
		for (groupName in metaGroupCache) {
			if (metaGroupCache.hasOwnProperty(groupName)) {
				groupNames.push(groupName);
			}
		}

		me.mask('Пожалуйста, подождите');
		//Чтобы не дублировались прелоадеры/маски
		me.UslugaComplexGrid.getView().loadMask.disable();
		me.doExpandAllGroups(groupNames);
	},
	doExpandAllGroups: function(groupNames) {
		var me = this;
		if (groupNames.length > 0) {
			var groupName = groupNames.shift();
			me.expandGroup(groupName, function() {
				me.doExpandAllGroups(groupNames);
			});
		} else {
			me.UslugaComplexGrid.getView().loadMask.enable();
			me.unmask();
		}
	},
	expandGroup: function(groupName, callback) {
		var me = this;
		var MedService_id = groupName;
		var pzm_MedService_id = null;
		var Resource_id = null;
		if (groupName.indexOf('_') > -1) {
			var groupParts = groupName.split('_');
			MedService_id = groupParts[0];
			switch (this.PrescriptionType_Code) {
				case 11: // Лабораторная диагностика
					pzm_MedService_id = groupParts[1];
					break;
				case 12: // Функциональная диагностика
					Resource_id = groupParts[1];
					break;
				default:
					pzm_MedService_id = groupParts[1];
					break;
			}
		}

		me.loadUslugaComplexGrid({
			addRecords: true,
			onExpandGroup: true,
			MedService_id: MedService_id,
			pzm_MedService_id: pzm_MedService_id,
			Resource_id: Resource_id,
			byAllLpu: me.loadedByAllLpu,
			callback: function() {
				if (typeof callback == 'function') {
					callback();
				}
			}
		});
	},
	renderTimetableBegTime: function(rec) {
		var me = this,
			text = '',
			dt;

		var begTime = rec.get('withResource') == 1 ? rec.get('TimetableResource_begTime') : rec.get('TimetableMedService_begTime');

		var key = rec.get('Unique_id');
		var to_queue = me.checkInQueueEnable(rec);

		if (rec.get('EvnDirection_id')) {
			if (begTime) {
				dt = Date.parseDate(begTime, 'd.m.Y H:i');
				text = '<a href="#" ' +
					'onclick="Ext6.getCmp(\'' + me.id + '\').showEvnDirectionMenu(this, ' +
					"'" + key + "'" +
					')">Записан ' + dt.format('d.m.Y D H:i').toLowerCase() + '</a>';
			} else {
				text = '<a href="#" ' +
					'onclick="Ext6.getCmp(\'' + me.id + '\').showEvnDirectionMenu(this, ' +
					"'" + key + "'" +
					')">В очереди</a>';
			}
		} else {
			if (begTime) {
				dt = Date.parseDate(begTime, 'd.m.Y H:i');
				text = '<a class="float-left" href="#" '+
					'onclick="Ext6.getCmp(\''+me.id+'\').showSelectedCellsMenu(this, ' +
					"'" + key + "'" + ')">' + dt.format('d.m.Y D H:i').toLowerCase() + '</a>';
				if(to_queue)
					text += '<a class="prescr-queue-button" data-qtip="Поставить в очередь" href="#" ' + 'onclick="Ext6.getCmp(\'' + me.id + '\').doApplyToQueue(this, ' + "'" + key + "'" + ')"></a>';
			} else if (rec.get('nolimit')) {
				//text = '<span style="white-space: pre-wrap;" class="float-left">Нет бирок. </span>';
				if (to_queue){
					text += '<a href="#" ' + 'onclick="Ext6.getCmp(\'' + me.id + '\').doApplyToQueue(this, ' + "'" + key + "'" + ')">В очередь</a>';
					//text = '<span href="#" ' + 'onclick="Ext6.getCmp(\'' + me.id + '\').selRec(this, ' + "'" + key + "'" + ')">В очередь</span>';
				} else {
					text = '<span href="#">Запись на службу запрещена</span>';
				}
			} else {
				if(rec.get('not_to_queue') !== false) {
					text = '<a href="#" ' + 'onclick="Ext6.getCmp(\'' + me.id + '\').getTimetableNoLimit(this, ' + "'" + key + "'" + ')">Уточнить далее</a>';
				} else if(to_queue) {
					text = '<a href="#" '+
						'onclick="Ext6.getCmp(\''+me.id+'\').showSelectedCellsMenu(this, ' +
						"'" + key + "'" + ')">В очередь</a>';
				}
			}
		}
		return text;
	},
	setFavorite: function(rec,view){
		inDevelopmentAlert();
	},
	setSelectedPrescrCount: function(model, record){
		var me = this,
			countSel = me.UslugaComplexGrid.getSelectionModel().getCount();
		me.PatientInfoPanel.setParams({count: countSel});
	},
	applySelectedPrescribe: function(){
		var me = this;
		var save_url = '/?c=PacketPrescr&m=applySelectedPrescribe';

		var params = {
			loadComposition: true,
			PersonEvn_id: me.data.PersonEvn_id,
			Server_id: me.data.Server_id,
			parentEvnClassSysNick: "EvnVizitPL",
			DopDispInfoConsent_id: '',
			StudyTarget_id: '2' // Тип
		};
		var citoAndNormal = me.getApplyUslugaCitoAndNormalArrs();
		params.save_data = Ext6.JSON.encode(citoAndNormal['normalList']).toString();
		var prescr_code = me.getDataByPrescriptionTypeCode('prescr_code');
		if (!prescr_code)
			return false;

		params[prescr_code +'_id'] = null;
		params[prescr_code +'_pid'] = me.data.Evn_id;
		params[prescr_code +'_IsCito'] = 'off';
		params[prescr_code +'_setDate'] = me.data.Evn_setDate;
		params[prescr_code +'_Descr'] = '';
		params.Evn_pid = me.data.Evn_id;

		me.mask('Сохранение назначения');
		Ext6.Ajax.request({
			url: save_url,
			callback: function(opt, success, response) {
				me.applyPrescrToCito(citoAndNormal['cito']);
			},
			params: params
		});
	},
	getApplyUslugaCitoAndNormalArrs: function() {
		var me = this,
			arrUslugaSelected = me.UslugaComplexGrid.getSelectionModel().getSelection(),
			arrPrescr = [],
			arrToCito = [],
			str = '', arr_type = '', uslugalist = {};
		if (!(arrUslugaSelected.length > 0))
			return false;
		switch (me.PrescriptionType_Code) {
			case 6:
				arr_type = 'proc';
				arrUslugaSelected.forEach(function (el) {
					if (el.get('UslugaComplex_IsCito')) {
						arrToCito.push(el);
					} else {
						arrPrescr.push({
							'UslugaComplex_id': el.get('UslugaComplex_id'),
							'Lpu_id': el.get('Lpu_id')
						});
					}
				});
				break;
			case 7:
				arr_type = 'oper';
				arrUslugaSelected.forEach(function (el) {
					if (el.get('UslugaComplex_IsCito')) {
						arrToCito.push(el);
					} else {
						arrPrescr.push(el.get('UslugaComplex_id'));
					}
				});
				break;
			case 11:
				arr_type = 'labdiag';
				arrPrescr = {};
				arrUslugaSelected.forEach(function (el) {
					if (el.get('UslugaComplex_IsCito')) {
						arrToCito.push(el);
					} else {
						str = el.get('UslugaComplex_id').toString();
						if (str)
							arrPrescr[str] = {
								'MedService_id': el.get('MedService_id'),
								'Lpu_id': el.get('Lpu_id'),
								'UslugaComplexMedService_pid': el.get('UslugaComplexMedService_id'),
								'UslugaComplex_id': [el.get('UslugaComplex_id')],
								'UslugaComposition': el.get('UslugaComposition')
							};
					}
				});
				break;
			case 12:
				arr_type = 'funcdiag';
				arrUslugaSelected.forEach(function (el) {
					if (el.get('UslugaComplex_IsCito')) {
						arrToCito.push(el);
					} else {
						arrPrescr.push({
							'MedService_id': el.get('MedService_id'),
							'Lpu_id': el.get('Lpu_id'),
							'UslugaComplex_id': el.get('UslugaComplex_id'),
							'StudyTarget_id': el.get('study_target')
						});
					}
				});
				break;
			case 13:
				arr_type = 'consusl';
				arrUslugaSelected.forEach(function (el) {
					if (el.get('UslugaComplex_IsCito')) {
						arrToCito.push(el);
					} else {
						arrPrescr.push({
							'MedService_id': el.get('MedService_id'),
							'Lpu_id': el.get('Lpu_id'),
							'UslugaComplex_id': el.get('UslugaComplex_id')
						});
					}
				});
				break;
		}

		uslugalist[arr_type] = arrPrescr;
		return {'cito': arrToCito, 'normalList': uslugalist};
	},
	cancelRecord: function(rec, selectedRecords, form, addedRecords) {
		var me = this;
		sw.Promed.EvnPrescr.cancel({
			ownerWindow: form,
			withoutQuestion: true,
			getParams: function(){
				return {
					EvnPrescr_id: rec.get('EvnPrescr_id'),
					parentEvnClass_SysNick: 'EvnVizitPL',
					PrescriptionType_id: form.PrescriptionType_Code
				};
			},
			callback: function(){
				rec.set('UslugaComplexMedService_HasPrescr', false);
				rec.set('EvnDirection_id', null);
				rec.set('EvnPrescr_id', null);
				rec.commit();
				addedRecords.push(rec);
				me.doApplySelectedPrescribeWithRecord(selectedRecords, form, addedRecords);
			}
		});
	},
	addTTMSDop: function(rec, objectPrescribe, selectedRecords, form, addedRecords) {
		var me = this,
			Resource_id = null,
			url = '/?c=TimetableMedService&m=addTTMSDop',
			dt;

		var MedService_id = rec.get('MedService_id');
		if (rec.get('pzm_MedService_id')) {
			MedService_id = rec.get('pzm_MedService_id');
		}

		if (rec.get('Resource_id')) {
			Resource_id = rec.get('Resource_id');
			url = '/?c=TimetableResource&m=addTTRDop';
		}
		var params = {
			Day: null,
			StartTime: null,
			MedService_id: MedService_id,
			Resource_id: Resource_id,
			UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id'),
			TimetableExtend_Descr: ''
		};

		var RecParams = rec.getData();
		RecParams.onSaveEvnDirection = function(data) {
			me.doApplySelectedPrescribeWithRecord(selectedRecords, form, addedRecords);
		};
		Ext6.apply(RecParams,rec);
		Ext6.Ajax.request({
			url: url,
			callback: function(opt, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.Error_Msg) {
						sw4.showInfoMsg({
							panel: me,
							type: 'error',
							bottom: 60,
							hideDelay: 20000,
							zIndex: 6,
							text: 'При записи услуги "' + rec.get('UslugaComplex_Code') + ' ' + rec.get('UslugaComplex_Name') + '" возникла ошибка:<br>' + response_obj.Error_Msg
						});
						Ext6.Msg.close();
						me.cancelRecord(rec, selectedRecords, form, addedRecords);
						return false;
					} else {
						if (response_obj.TimetableMedService_id && response_obj.TimetableMedService_begTime) {
							rec.set('TimetableMedService_id', response_obj.TimetableMedService_id);
							RecParams.TimetableMedService_id = response_obj.TimetableMedService_id;
							dt = Date.parseDate(response_obj.TimetableMedService_begTime, 'Y-m-d H:i:s');
							rec.set('TimetableMedService_begTime', dt.format('d.m.Y H:i'));
							RecParams.TimetableMedService_begTime = dt.format('d.m.Y H:i');
						}
						if (response_obj.TimetableResource_id && response_obj.TimetableResource_begTime) {
							rec.set('TimetableResource_id', response_obj.TimetableResource_id);
							RecParams.TimetableResource_id = response_obj.TimetableResource_id;
							dt = Date.parseDate(response_obj.TimetableResource_begTime, 'Y-m-d H:i:s');
							rec.set('TimetableResource_begTime', dt.format('d.m.Y H:i'));
							RecParams.TimetableResource_begTime = dt.format('d.m.Y H:i');
						}
						if(response_obj.TimetableMedService_id || response_obj.TimetableResource_id){
							rec.commit();
							me.evnPrescrCntr.saveEvnDirection(RecParams);
						}
						return true;
					}
				}
			},
			params: params
		});

		return true;
	},
	setCito: function(rec, selectedRecords, form, addedRecords, cb) {
		if(!cb) cb = Ext6.emptyFn;
		var id = rec.get('EvnPrescr_id');
		var cito = rec.get('UslugaComplex_IsCito') ? 2 : 1;
		Ext.Ajax.request({
			params: {
				EvnPrescr_id: id,
				EvnPrescr_IsCito: cito
			},
			callback: function(options, success, response) {
				cb(success,response, selectedRecords, form, addedRecords);
			},
			url: '/?c=EvnPrescr&m=setCitoEvnPrescr'
		});
	},
	findAndCheckCanceledRecords: function(records, grid){
		if(records){
			var to_check = [];
			var store = grid.getStore().getData().items;
			store.forEach(function (rec) {
				if(records.get(rec.get('Unique_id')) !== undefined) {
					rec.set('UslugaComplex_IsCito', records.get(rec.get('Unique_id')));
					rec.commit();
					to_check.push(rec);
				}
			});
			if(to_check != []){
				grid.getSelectionModel().select(to_check);
			}
		}
	},
	expandGroups: function(groups, callback) {
		var me = this;
		var grLn = groups.length;
		if (grLn > 0) {
			var groupName = groups.shift();
			me.expandGroup(groupName, function() {
				me.expandGroups(groups, callback);
				if(grLn == 1 && typeof callback == 'function') {
					callback();
				}
			});
		}
	},
	applySelectedPrescribeWithRecord: function(selectction, form, soloPrescr) {
		var me = this;
		me.mask('Пожалуйста подождите. Идет запись...');

		this.doApplySelectedPrescribeWithRecord(selectction, form, [], soloPrescr);
	},
	getCitoMap: function (records) {
		var citoMap = new Map();
		records.forEach(function (rec) {
			citoMap.set(rec.get('Unique_id'), rec.get('UslugaComplex_IsCito'));
		});
		return citoMap;
	},
	doApplySelectedPrescribeWithRecord: function(selectedRecords, form, addedRecords, soloPrescr) {
		var rec = selectedRecords.pop();
		var me = this;

		if(rec == null) {
			me.unmask();
			var unqGruopIds = [];
			addedRecords.forEach(function (el) {
				if(unqGruopIds.indexOf(el.get('Group_id')) == -1)
					unqGruopIds.push(el.get('Group_id'));
			});

			var records = me.getCitoMap(addedRecords);

			me.UslugaComplexGrid.setSelection(null);

			form.expandGroups(
				unqGruopIds,
				function () {
					form.findAndCheckCanceledRecords(records, form.UslugaComplexGrid);
					me.parentPanel.getController().loadGrids();
				}
			);
			return ;
		}

		if (!rec.get('EvnPrescr_id')) {
			me.saveEvnPrescr({
				rec: rec,
				withoutInfoMsg: true,
				loadMask: 'Пожалуйста подождите. Идет запись услуги "' + rec.get('UslugaComplex_Code') + ' ' + rec.get('UslugaComplex_Name') + '"',
				callback: function () {
					var params = rec.data;
					params.rec = rec;
					params.form = form;
					params.addedRecords = addedRecords;
					params.selectedRecords = selectedRecords;
					params.PrescriptionType_Code = me.PrescriptionType_Code;
					params.onSaveEvnDirection = function (data) {
						if (!(rec && data)) {
							return;
						}
						if (data.Error_Msg) {
							me.unmask();
							sw4.showInfoMsg({
								panel: me,
								type: 'error',
								bottom: 41,
								hideDelay: 20000,
								zIndex: 5,
								text: data.Error_Msg
							});
							me.cancelRecord(rec, selectedRecords, form, addedRecords);
						} else {
							addedRecords.push(rec);
							me.doApplySelectedPrescribeWithRecord(selectedRecords, form, addedRecords);
						}
					};
					if(soloPrescr === true) {
						me.checkEvnDirection(params);
					} else {
						me.saveEvnDirection(rec, params);
					}
				}
			});
			return false;
		}
	},
	applyToPacket: function() {
		var me = this;

		var allUsluga = me.getApplyUslugaArr(true);
		var options = {
			PacketPrescr_id: me.PacketPrescr_id,
			PrescriptionType_Code: me.PrescriptionType_Code,
			callback: me.callback
		};

		if (me.PrescriptionType_Code == 11) {
			Ext6.Object.each(allUsluga, function (key, item) {
				options = Ext6.apply(options, item);
				me.evnPrescrCntr.addPrescrToPacket(options, me);
			});
		} else {
			allUsluga.forEach(function (item) {
				options = Ext6.apply(options, item);
				me.evnPrescrCntr.addPrescrToPacket(options, me);
			});
		}

		var panel = Ext6.ComponentQuery.query('[refId=swPacketPrescrSelectWindow]');

		if (panel[0]) {
			panel[0].loadGrid();
		}
	},
	getDataByPrescriptionTypeCode: function(param, all){
		var me = this, prescr = {}, data = false;
		switch (me.PrescriptionType_Code) {
			case 6:
				prescr.prescr_code = 'EvnCourseProc';
				break;
			case 7:
				prescr.prescr_code = 'EvnPrescrOperBlock';
				break;
			case 11:
				prescr.prescr_code = 'EvnPrescrLabDiag';
				break;
			case 12:
				prescr.prescr_code = 'EvnPrescrFuncDiag';
				break;
			case 13:
				prescr.prescr_code = 'EvnPrescrConsUsluga';
				break;
		}
		if(param)
			data = prescr[param];
		if(all)
			data = prescr;
		return data;
	},
	applyPrescrToCito: function(records) {
		var me = this,
			rec = records.pop();
		if(rec == null) {
			me.unmask();
			me.hide();
			if(me.callback) me.callback();
			return;
		}
		me.saveEvnPrescr({
			rec: rec,
			withoutInfoMsg: true,
			callback: function (data) {
				if(!(rec && data))
					return;
				if(!data.Error_Msg)
					me.applyPrescrToCito(records);
			}
		});
	},
	resetTimetableResource: function(rec) {
		var me = this;
		var store = me.UslugaComplexGrid.getStore();
		var index = store.findBy(function(record) {
			return record.get('TimetableResource_id') && record.get('MedService_id');
		});
		if(index != -1) {
			rec.set('TimetableResource_id', store.getAt(index).get('TimetableResource_id'));
			me.getTimetableNext(rec);
		}
	},
	resetTimetableMedService: function(rec) {
		if(!rec || !this.UslugaComplexGrid) return;
		var me = this;
		var store = me.UslugaComplexGrid.getStore();
		var index = store.findBy(function(record) {
			return record.get('TimetableMedService_id') && record.get('MedService_id');
		});
		if(index != -1) {
			rec.set('TimetableMedService_id', store.getAt(index).get('TimetableMedService_id'));
			me.getTimetableNext(rec);
		}
	},
	getApplyUslugaArr: function(notJSON) {
		var me = this,
			arrUslugaSelected = me.UslugaComplexGrid.getSelectionModel().getSelection(),
			arrPrescr = [],
			str = '',arr_type = '', uslugalist = {};
		if(!(arrUslugaSelected.length > 0))
			return false;
		log('selectedRec:');
		switch (me.PrescriptionType_Code) {
			case 6:
				arr_type = 'proc';
				arrUslugaSelected.forEach(function (el) {
					log(el);
					arrPrescr.push(el.get('UslugaComplex_id'));
					if(notJSON)
						arrPrescr.push({
							'UslugaComplex_id': el.get('UslugaComplex_id')
						});
				});
				break;
			case 7:
				arr_type = 'oper';
				arrUslugaSelected.forEach(function (el) {
					log(el);
					arrPrescr.push(el.get('UslugaComplex_id'));
					if(notJSON)
						arrPrescr.push({
							'UslugaComplex_id': el.get('UslugaComplex_id')
						});
				});
				break;
			case 11:
				arr_type = 'labdiag';
				arrPrescr = {};
				arrUslugaSelected.forEach(function (el) {
					log(el);
					str = el.get('UslugaComplex_id').toString();
					if(str)
						arrPrescr[str] = {
							'MedService_id': el.get('MedService_id'),
							'Lpu_id': el.get('Lpu_id'),
							'UslugaComplexMedService_pid': el.get('UslugaComplexMedService_id'),
							'UslugaComplex_id': [el.get('UslugaComplex_id')],
							'UslugaComposition': el.get('UslugaComposition'),
							'MedService_pzmid': el.get('pzm_MedService_id')
						};
				});
				break;
			case 12:
				arr_type = 'funcdiag';
				arrUslugaSelected.forEach(function (el) {
					log(el);
					arrPrescr.push({
						'MedService_id': el.get('MedService_id'),
						'UslugaComplex_id': el.get('UslugaComplex_id'),
						'StudyTarget_id': el.get('study_target'),
						'Resource_id': el.get('Resource_id'),
						'Lpu_id': el.get('Lpu_id'),
					});
				});
				break;
			case 13:
				arr_type = 'consusl';
				arrUslugaSelected.forEach(function (el) {
					log(el);
					arrPrescr.push({
						'MedService_id': el.get('MedService_id'),
						'UslugaComplex_id': el.get('UslugaComplex_id'),
						'Lpu_id': el.get('Lpu_id')
					});
				});
				break;
		}

		if (notJSON) {
			return arrPrescr;
		}
		uslugalist[arr_type] = arrPrescr;
		return Ext6.JSON.encode(uslugalist).toString();
	},
	searchConflictsUsl: function(selected, searchField, conflictList, rec) {
		selected.each(function (el) {
			if (rec.get(searchField) == el.get(searchField) && rec != el && !conflictList.has(el.get('UslugaComplex_id'))) {
				conflictList.set(el.get('UslugaComplex_id'), "<br>" + el.get('UslugaComplex_Code') + " " + el.get('UslugaComplex_Name'));
			}
		});
	},
	showConflictMsg: function() {
		let me = this;
		let conflictList = me.UslugaComplexGrid.UslugaConflictsList;
		if(me.infoMsg)
			me.infoMsg.destroy();
		if(conflictList.size < 2)
			return;
		var msg = 'Услуги, из-за времени записи которых могут возникнуть конфликты:';

		for(let val of conflictList.values()) {
			msg += val;
		}

		me.infoMsg = sw4.showInfoMsg({
			panel: me,
			type: 'warning',
			bottom: 41,
			hideDelay: 15000,
			text: msg
		});
	},
	checkPrescrTime: function(rec, sm) {
		var me = this;
		var searchField = "";
		if (rec.get('TimetableGraf_id')) searchField = "TimetableGraf_id";
		else if (rec.get('TimetableMedService_id')) searchField = "TimetableMedService_id";
		else if (rec.get('TimetableResource_id')) searchField = "TimetableResource_id";
		else return ;

		var selected = sm.getSelected();
		var conflictList = me.UslugaComplexGrid.UslugaConflictsList;
		if(conflictList.size == 0) {
			me.searchConflictsUsl(selected, searchField, conflictList, rec);
		}
		selected.each(function (el) {
			if (rec.get(searchField) == el.get(searchField) && rec != el && !conflictList.has(rec.get('UslugaComplex_id'))) {
				conflictList.set(rec.get('UslugaComplex_id'), "<br>" + rec.get('UslugaComplex_Code') + " " + rec.get('UslugaComplex_Name'));
				return;
			}
		});
		me.showConflictMsg();
	},
	getMedServicesQueueAccess: function(records) {
		var me = this;
		if(!me.medServicesQueueAccessMap) {
			var serviceAcessDict = new Map();
			records.forEach(function (rec) {
				serviceAcessDict.set(rec.get('MedService_id'), rec.get('RecordQueue_id'));
				if(!serviceAcessDict.has(rec.get('pzm_MedService_id'))) {
					serviceAcessDict.set(rec.get('pzm_MedService_id'), rec.get('pzm_RecordQueue_id'));
				}
			});
			me.medServicesQueueAccessMap = serviceAcessDict;
		} else {
			records.forEach(function (rec) {
				if(!me.medServicesQueueAccessMap.has(rec.get('MedService_id'))) {
					me.medServicesQueueAccessMap.set(rec.get('MedService_id'), rec.get('RecordQueue_id'));
				}
				if(!me.medServicesQueueAccessMap.has(rec.get('pzm_MedService_id'))) {
					me.medServicesQueueAccessMap.set(rec.get('pzm_MedService_id'), rec.get('pzm_RecordQueue_id'));
				}
			});
		}
	},
	/* конструктор */
	initComponent: function() {
		var me = this;
		me.PrescriptionTypeCombo = Ext6.create('swCommonSprCombo', {
			labelWidth: 73,
			minWidth: 73 + 300,
			flex: 1,
			comboSubject: 'PrescriptionType',
			name: 'PrescriptionType_id',
			codeField: 'PrescriptionType_Code',
			fieldLabel: 'Тип услуги',
			listeners: {
				'select': function() {
					me.loadUslugaComplexGrid({'updateFields': true});
				}
			}
		});

		this.TimetableSelectionPanel = Ext6.create('common.EMK.tools.swTimetableSelectionWindow', {
			parentPanel: me,
			reference: 'TimetablePanel',
			onSelect: function(selRec,EvnPrescr_id){
				var clickTTRec = this.clickTTRec;
				if(EvnPrescr_id && me && me.UslugaComplexGrid){
					clickTTRec = me.UslugaComplexGrid.findRecord('EvnPrescr_id',EvnPrescr_id);
				}
				if(!clickTTRec) return;

				if(!selRec) {
					if(clickTTRec.get('TimetableResource_begTime'))
						clickTTRec.set('TimetableResource_begTime', null);
					if(clickTTRec.get('TimetableMedService_begTime'))
						clickTTRec.set('TimetableMedService_begTime', null);
					if(clickTTRec.get('TimetableMedService_id'))
						clickTTRec.set('TimetableMedService_id', null);
					if(clickTTRec.get('TimetableGraf_id'))
						clickTTRec.set('TimetableGraf_id', null);
					if(clickTTRec.get('TimetableResource_id'))
						clickTTRec.set('TimetableResource_id', null);
					if(clickTTRec.get('not_to_queue'))
						clickTTRec.set('not_to_queue', false);
					return;
				}
				if(clickTTRec.get('withResource') == 1)
					clickTTRec.set('TimetableResource_begTime',  Ext6.util.Format.date(selRec.getData().TimetableResource_begTime, 'd.m.Y H:i'));
				else
					clickTTRec.set('TimetableMedService_begTime',  Ext6.util.Format.date(selRec.getData().TimetableMedService_begTime, 'd.m.Y H:i'));
				var recData = selRec.getData();

				if(recData.TimetableMedService_id)
					clickTTRec.set('TimetableMedService_id',  recData.TimetableMedService_id);
				if(recData.TimetableGraf_id)
					clickTTRec.set('TimetableGraf_id',  recData.TimetableGraf_id);
				if(recData.TimetableResource_id)
					clickTTRec.set('TimetableResource_id',  recData.TimetableResource_id);

				me.UslugaComplexGrid.UslugaConflictsList.delete(clickTTRec.get('UslugaComplex_id'));

				me.checkPrescrTime(clickTTRec, me.UslugaComplexGrid.getSelectionModel());
			}
		});

		me.PrescriptionTypeCombo.getStore().filterBy(function (rec) {
			return (rec.get('PrescriptionType_Code').inlist(['6', '7', '11', '12', '13']));
		});
		me.LpuFilterCombo = Ext6.create('swLpuCombo', {
			additionalRecord: {
				value: -1,
				text: langs('Все'),
				code: 0
			},
			anyMatch: true,
			hideEmptyRow: true,
			labelWidth: 48,
			labelStyle: 'padding:7px 5px 3px 0;',
			minWidth: 353,
			flex: 1,
			margin: '0 13px 0 0',
            filterFn: function(field) {
			    if (field.data.Lpu_EndDate == null) {
                    return true;
                } else {
                    return false;
                }
            },
			listeners: {
				'select': function() {
					me.loadUslugaComplexGrid();
				},
				'change': function(combo, newValue, oldValue) {
					if (newValue > 0) {
						me.MedServiceFilterCombo.getStore().proxy.extraParams.Lpu_id = newValue;
						me.MedServiceFilterCombo.getStore().proxy.extraParams.Lpu_isAll = 0;
					} else {
						me.MedServiceFilterCombo.getStore().proxy.extraParams.Lpu_id = null;
						me.MedServiceFilterCombo.getStore().proxy.extraParams.Lpu_isAll = 1;
					}
					me.MedServiceFilterCombo.setValue(-1);
					me.MedServiceFilterCombo.getStore().load({
						callback: function() {
							me.MedServiceFilterCombo.setValue(-1);
						}
					});
				}
			},
			listConfig:{
				minWidth: 500
			},
			labelAlign: 'right',
			fieldLabel: 'МО',
			name: 'Lpu_id'
		});
		me.MedServiceFilterCombo = Ext6.create('swMedServiceCombo', {
			additionalRecord: {
				value: -1,
				text: langs('Все'),
				code: 0
			},
			anyMatch: true,
			flex: 1,
			hideEmptyRow: true,
			queryMode: 'local',
			labelWidth: 105,
			minWidth: 105 + 300,
			needDisplayLpu: function() {
				return me.LpuFilterCombo.getValue() == -1;
			},
			listeners: {
				'select': function() {
					me.loadUslugaComplexGrid();
				}
			},
			listConfig:{
				minWidth: 430
			},
			labelAlign: 'right',
			fieldLabel: 'Место оказания',
			name: 'MedService_id'
		});
		me.UslugaComplexFilterCombo = Ext6.create('swUslugaComplexSearchCombo', {
			type: 'string',
			filterByValue: true,
			listConfig: {
				cls: 'choose-bound-list-menu update-scroller'
			},
			listeners: {
				'render': function (combo) {
					combo.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['gost2011']);
				},
				'change': function (combo, newValue, oldValue) {
					me.loadUslugaComplexGrid();
					//me.gridHeaderFilters.applyFilters();
				},
				'beforequery': function(queryPlan, eOpts ){
					this.getStore().proxy.extraParams = me.UslugaComplexGrid.getStore().proxy.extraParams;
					this.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['gost2011']);
					this.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['noprescr']);
					this.getStore().proxy.extraParams.PrescriptionType_Code = me.PrescriptionType_Code;
					this.getStore().proxy.extraParams.to = 'EvnPrescrUslugaInputWindow';
					//this.getStore().proxy.extraParams.withoutLpuFilter = 2;
					delete this.getStore().proxy.extraParams.filterByUslugaComplex_id;
					delete this.getStore().proxy.extraParams.Evn_id;
					delete this.getStore().proxy.extraParams.userLpuSection_id;
					//delete this.getStore().proxy.extraParams.formMode;

				}
			},
			hideLabel: false,
			fieldLabel: 'Услуга',
			labelWidth: 48,
			labelAlign: 'right',
			minWidth: 48 + 574,
			emptyText: 'Поиск услуги по коду или наименованию'
		});

		this.groupingFeature = Ext6.create('swGridPrescrGroupingFeature', {
			enableGroupingMenu: false,
			onBeforeGroupClick: function(view, rowElement, groupName, e) {
				log('onBeforeGroupClick', view, rowElement, groupName, e);

				var groupIsCollapsed = !me.groupingFeature.isExpanded(groupName);
				me.UslugaComplexGrid.setSelection(null);
				if (groupIsCollapsed) {
					me.expandGroup(groupName);

					return false;
				} else {
					return true;
				}
			},
			groupHeaderTpl: new Ext6.XTemplate(
				'{[this.formatName(values.rows)]}',
				{
					formatName: function(rows) {

						var s = '';
						switch (me.PrescriptionType_Code){
							case 11:
								if(rows[0] && rows[0].get('Unique_id') == '201468_2263_6490') {
								}
								if (rows[0] && rows[0].get('pzm_MedService_Nick')) {
									if(rows[0].get('Unique_id') == '201468_2263_6490') {
									}
									s = s + rows[0].get('pzm_MedService_Nick');
								}
								if (rows[0] && rows[0].get('MedService_Nick')) {
									if(rows[0].get('Unique_id') == '201468_2263_6490') {
									}
									if (s.length > 0) {
										s = s + ' / ';
									}
									s = s + rows[0].get('MedService_Nick');
								}
								break;
							case 12:
								if (rows[0] && rows[0].get('Resource_Name')) {
									s = s + rows[0].get('Resource_Name');
								}
								if (rows[0] && rows[0].get('MedService_Nick')) {
									if (s.length > 0) {
										s = ' / ' + s;
									}
									s = rows[0].get('MedService_Nick') + s;
								}
								break;
							default:
								if (rows[0] && (rows[0].get('pzm_MedService_Nick') || rows[0].get('Resource_Name'))) {
									s = s + (rows[0].get('pzm_MedService_Nick')?rows[0].get('pzm_MedService_Nick'):rows[0].get('Resource_Name'));
								}
								if (rows[0] && rows[0].get('MedService_Nick')) {
									if (s.length > 0) {
										s = s + ' / ';
									}
									s = s + rows[0].get('MedService_Nick');
								}
						}


						if ((me.loadedByAllLpu || me.onlyByContract) && rows[0] && rows[0].get('Lpu_Nick')) {
							s = rows[0].get('Lpu_Nick') + ' / ' + s;
						}

						return s;
					}
				}
			)
		});

		this.gridHeaderFilters = Ext6.create('Ext6.ux.GridHeaderFilters', {
			enableTooltip: false,
			reloadOnChange: false
		});

		this.UslugaComplexGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common EvnPrescrUslugaInputGrid',
			xtype: 'grid',
			alias: 'widget.EvnPrescrUslugaInputGrid',
			viewModel: true,
			buttonAlign: 'center',
			scrollable: 'y',
			autoHeight: true,
			autoWidth: true,
			frame: false,
			border: false,
			dockedItems:[
				{
					xtype: 'toolbar',
					dock: 'top',
					border: true,
					style:{
						background: '#f5f5f5',
						borderTop: '1px solid #d0d0d0 !important',
						padding: '6px 19px 7px 19px',
					},
					items: [
						{
							xtype: 'tbtext',
							itemId: 'countAll',
							userCls: 'count-usl-search',
							html: 'Найдено услуг: <span style="color: #000;">0</span>',
							width: 100,
							margin: '9 10 0 0'
						},
						'->',
						{
							xtype: 'tbtext',
							text: 'Сортировать:',
							width: 100,
							margin: '9 10 0 0'
						}, {
							xtype: 'segmentedbutton',
							userCls: 'segmentedButtonGroup',
							items: [{
								text: 'По месту оказания',
								itemId: 'groupByMedService',
								pressed: true,
								handler: function () {
									me.setGroupByMedService(true);
									me.loadUslugaComplexGrid();
								}
							}, {
								text: 'По услугам',
								handler: function () {
									me.setGroupByMedService(false);
									me.loadUslugaComplexGrid();
								}
							}]
						}
					]
				}
			],
			/*tbar: [
				{
					xtype: 'tbtext',
					userCls: 'count-usl-search',
					html: 'Найдено услуг: <span>10</span>',
					width: 100,
					margin: '9 10 0 0'
				},
				'->',
				{
					xtype: 'tbtext',
					text: 'Сортировать:',
					width: 100,
					margin: '9 10 0 0'
				}, {
					xtype: 'segmentedbutton',
					userCls: 'segmentedButtonGroup',
					items: [{
						text: 'По месту оказания',
						itemId: 'groupByMedService',
						pressed: true,
						handler: function () {
							me.setGroupByMedService(true);
							me.loadUslugaComplexGrid();
						}
					}, {
						text: 'По услугам',
						handler: function () {
							me.setGroupByMedService(false);
							me.loadUslugaComplexGrid();
						}
					}]
				}
			],*/
			defaults: {
				border: 0
			},
			bind: {
				selection: '{theRow}'
			},
			selModel: Ext6.create('Ext6.selection.CheckboxModel',{
				headerWidth: 44,
				checkOnly: true,
				style: {
					borderLeft: '1px solid #d0d0d0'
				},
				updateHeaderState: function() {
					// check to see if all records are selected
					var me = this,
						store = me.store,
						storeCount = store.getCount(),
						views = me.views,
						hdSelectStatus = false,
						selectedCount = 0,
						selected, len, i;

					if (!store.isBufferedStore && storeCount > 0) {
						selected = me.selected;
						hdSelectStatus = true;
						for (i = 0, len = selected.getCount(); i < len; ++i) {
							if (store.indexOfId(selected.getAt(i).id) > -1) {
								++selectedCount;
							}
						}
						var notSelectedRec = store.queryBy(function(rec){return (rec.get('EvnDirection_id') || rec.get('EvnPrescr_id'))}),
							notSelectedRecCount = 0;
						if(notSelectedRec) notSelectedRecCount = notSelectedRec.getCount();
						hdSelectStatus = (storeCount-notSelectedRecCount) === selectedCount;
					}

					if (views && views.length) {
						me.column.setHeaderStatus(hdSelectStatus);
					}
				},
				listeners: {
					beforedeselect: function(sm, record, index, eOpts) {
						me.UslugaComplexGrid.UslugaConflictsList.delete(record.get('UslugaComplex_id'));
						me.showConflictMsg();
						var begTime = record.get('withResource') == 1 ? record.get('TimetableResource_begTime') : record.get('TimetableMedService_begTime');
						if(record.get('UslugaComplex_IsCito'))
							record.set('UslugaComplex_IsCito', false);
						if(!begTime) {
							if(record.get('withResource') == 1) me.resetTimetableResource(record);
							else me.resetTimetableMedService(record);
						}
						record.commit();
					},
					beforeselect:function(sm,rec,index, eOpts){
						var begTime = rec.get('withResource') == 1? rec.get('TimetableResource_begTime') : rec.get('TimetableMedService_begTime');

						if(!rec.get('nolimit') && !me.checkInQueueEnable(rec) && !begTime && !rec.get('EvnPrescr_id') && !rec.get('EvnDirection_id')) {
							Ext6.getCmp(me.id).getTimetableNoLimit(this, rec.get('Unique_id'));
						}
						if(!me.checkInQueueEnable(rec) && !begTime) {
							return false;
						}
						if (me.PacketPrescr_id == null) {
							if (rec.get('EvnDirection_id') || rec.get('EvnPrescr_id')) {
								return false;
							}
						}
						if(!begTime) {
							let key = rec.get('Unique_id'),
								store = Ext6.getCmp(me.id).UslugaComplexGrid.getStore();
							Ext6.getCmp(me.id).getTimetableNoLimit(this, key);
							rec = store.findRecord('Unique_id', key);
						}
						me.checkPrescrTime(rec, sm);
					},
					deselect: function(sm,rec,index, eOpts) {
						if(sm.getSelection().length == 0) {
							me.addButton.disable();
							me.addButtonWithRec.disable();
						}
					},
					select: function(sm,rec,index, eOpts) {
						if(sm.getSelection().length != 0) {
							me.addButton.enable();
							me.addButtonWithRec.enable();
						}
					},
					selectionchange: {
						fn: 'setSelectedPrescrCount',
						scope: me
					}
				}
			}),
			listeners: {
				beforeEdit: function(grid, context) {
					log('beforeEdit', grid, context);
					if (context.field == 'location') {
						var MedServiceEditor = Ext6.getCmp(me.id + '_MedServiceEditor');
						MedServiceEditor.getStore().proxy.extraParams.filterByUslugaComplex_id = context.record.get('UslugaComplex_id');
						if (me.loadedByAllLpu || me.onlyByContract) {
							MedServiceEditor.getStore().proxy.extraParams.filterByLpu_id = null;
						} else {
							MedServiceEditor.getStore().proxy.extraParams.filterByLpu_id = context.record.get('Lpu_id');
						}
						MedServiceEditor.getStore().proxy.extraParams.userLpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id || null;
						MedServiceEditor.getStore().proxy.extraParams.PrescriptionType_Code = me.PrescriptionType_Code;
						MedServiceEditor.getStore().proxy.extraParams.formMode = 'ExtJS6';
						MedServiceEditor.getStore().load({
							callback: function() {
								// выбрать запись с той же службой
								var rec = MedServiceEditor.getStore().findRecord('MedService_id', context.record.get('MedService_id'));
								if (rec) {
									MedServiceEditor.setValue(rec.get('UslugaComplexMedService_key'));
								}
							}
						});
					}

					if (context.field == 'study_target') {
						var StudyTargetEditor = Ext6.getCmp(me.id + '_StudyTargetEditor');
						StudyTargetEditor.getStore().load({
							callback: function() {
								StudyTargetEditor.setValue(2);
							}
						});
					}
				},
				edit: function(grid, context) {
					log('edit', grid, context);
					if (context.field == 'study_target') {
						var StudyTargetEditor = Ext6.getCmp(me.id + '_StudyTargetEditor');
						var rec = StudyTargetEditor.getSelectedRecord();
						if(rec && rec.get('StudyTarget_id')) {
							context.record.set('StudyTarget_id', rec.get('StudyTarget_id'));
							context.record.commit();
						}
					}
					if (context.field == 'location') {
						var MedServiceEditor = Ext6.getCmp(me.id + '_MedServiceEditor');
						var sel_rec = MedServiceEditor.getSelectedRecord();
						if (sel_rec && sel_rec.get('MedService_id')) {
							// возможно была выбрана другая служба и услуга
							if (sel_rec.get('UslugaComplexMedService_id') != context.record.get('UslugaComplexMedService_id')) {
								context.record.compositionMenu = null;
								context.record.set('isComposite', sel_rec.get('isComposite'));
							}
							context.record.set('UslugaComplexMedService_id', sel_rec.get('UslugaComplexMedService_id'));
							context.record.set('pzm_UslugaComplexMedService_id', sel_rec.get('pzm_UslugaComplexMedService_id'));
							// не должна меняться? context.record.set('UslugaComplex_2011id', sel_rec.get('UslugaComplex_2011id'));
							context.record.set('UslugaComplex_id', sel_rec.get('UslugaComplex_id'));
							context.record.set('UslugaComplex_Code', sel_rec.get('UslugaComplex_Code'));
							context.record.set('UslugaComplex_IsCito', sel_rec.get('UslugaComplex_IsCito'));
							context.record.set('UslugaComplex_Name', sel_rec.get('UslugaComplex_Name')); // при смене места оказания показывать наименование из данного места оказания
							context.record.set('MedService_id', sel_rec.get('MedService_id'));
							context.record.set('MedServiceType_id', sel_rec.get('MedServiceType_id'));
							context.record.set('MedServiceType_SysNick', sel_rec.get('MedServiceType_SysNick'));
							context.record.set('MedService_Nick', sel_rec.get('MedService_Nick'));
							context.record.set('MedService_Name', sel_rec.get('MedService_Name'));
							context.record.set('Lpu_id', sel_rec.get('Lpu_id'));
							context.record.set('Lpu_Nick', sel_rec.get('Lpu_Nick'));
							context.record.set('LpuBuilding_id', sel_rec.get('LpuBuilding_id'));
							context.record.set('LpuBuilding_Name', sel_rec.get('LpuBuilding_Name'));
							context.record.set('LpuUnit_id', sel_rec.get('LpuUnit_id'));
							context.record.set('LpuUnit_Name', sel_rec.get('LpuUnit_Name'));
							context.record.set('LpuUnitType_id', sel_rec.get('LpuUnitType_id'));
							context.record.set('LpuUnitType_SysNick', sel_rec.get('LpuUnitType_SysNick'));
							context.record.set('LpuSection_id', sel_rec.get('LpuSection_id'));
							context.record.set('LpuSection_Name', sel_rec.get('LpuSection_Name'));
							context.record.set('LpuSectionProfile_id', sel_rec.get('LpuSectionProfile_id'));
							context.record.set('ttms_MedService_id', sel_rec.get('ttms_MedService_id'));
							context.record.set('TimetableMedService_id', sel_rec.get('TimetableMedService_id'));
							context.record.set('TimetableMedService_begTime', sel_rec.get('TimetableMedService_begTime'));
							context.record.set('TimetableResource_begTime', sel_rec.get('TimetableResource_begTime'));
							context.record.set('TimetableResource_id', sel_rec.get('TimetableResource_id'));
							context.record.set('Resource_id', sel_rec.get('Resource_id'));
							context.record.set('Resource_Name', sel_rec.get('Resource_Name'));
							context.record.set('ttr_Resource_id', sel_rec.get('ttr_Resource_id'));
							if (me.PrescriptionType_Code == 11) {
								// возможно была выбрана другая лаборатория или другой пункт забора
								context.record.set('MedService_id', sel_rec.get('lab_MedService_id')); // лаборатория должна попасть в EvnDirection.
								context.record.set('pzm_Lpu_id', sel_rec.get('pzm_Lpu_id'));
								context.record.set('pzm_MedService_id', sel_rec.get('pzm_MedService_id'));
								context.record.set('pzm_MedServiceType_id', sel_rec.get('pzm_MedServiceType_id'));
								context.record.set('pzm_MedServiceType_SysNick', sel_rec.get('pzm_MedServiceType_SysNick'));
								context.record.set('pzm_MedService_Nick', sel_rec.get('pzm_MedService_Nick'));
								context.record.set('pzm_MedService_Name', sel_rec.get('pzm_MedService_Name'));
							}
							context.record.commit();
						}
					}
				}
			},
			features: [
				me.groupingFeature
			],
			viewConfig: {
				getRowClass: function(rec, rowIndex, rowParams, store){
					// Здесь происходит выделение существующих назначений
					var cls = '';
					
					if (me.PacketPrescr_id != null) {
					} else if (rec.get('EvnDirection_id')) {
						cls = cls + 'x-grid-rowbacklightgreen ';
					} else if (rec.get('EvnPrescr_id')) {
						cls = cls + 'x-grid-rowbacklightblue ';
					}
					return cls;
				}
			},
			columns: [
				/*{
					text: '',
					dataIndex: 'UslugaComplexMedService_HasPrescr',
					align: 'left',
					xtype: 'checkcolumn',
					sortable: false,
					hideable: false,
					resizable: false,
					menuDisabled: true,
					style: {
						'borderRight': 'none'
					},
					tdCls: 'padLeft',
					listeners: {
						'checkchange': function (column, rowIndex, checked, rec, e, eOpts) {
							rec.commit();
							// тут магия будет происходить, услуга либо назначается либо удаляется сразу + грузится бирка и состав услуги
							if (checked) {
								me.loadComposition(rec); // загружаем состав
								me.saveEvnPrescr({
									rec: rec
								});
							} else {
								me.cancelEvnPrescr(rec);
							}
						}
					},
					width: 32
				},*/ {
					text: 'Избр.',
					border: false,
					dataIndex: 'UslugaComplex_IsFavorite',
					xtype: 'actioncolumn',
					style:{
						borderTop: 'none',
						borderLeft: '1px solid #d0d0d0',
						color: '#000'
					},
					width: 44,
					//align: 'start', // 'left'
					items: [{
						getClass: function (value) {
							return (value == 2)
								? 'icon-star-active'
								: 'icon-star';
						},
						getTip: function (value) {
							return (value == 2)
								? 'Убрать из избранных'
								: 'Добавить в избранное';
						},
						handler: function (view, rowIndex, colIndex, item, e, record) {
							me.setFavorite(record, view);
						}
					}]
				},
				{
					text: 'Код',
					style:{
						borderTop: 'none',
						borderLeft: '1px solid #d0d0d0'
					},
					dataIndex: 'UslugaComplex_Code',
					align: 'left',
					width: 120
				},
				{
					text: 'Услуга',
					style:{
						borderTop: 'none',
						color: '#000'
					},
					dataIndex: 'UslugaComplex_Name',
					flex: 3,
					minWidth: 390
				},
				{
					text: 'Состав',
					align: 'center',
					style:{
						borderTop: 'none',
						color: '#000'
					},
					dataIndex: 'composition_cnt',
					width: 75,
					renderer: function (val, metadata, rec) {
						//if (rec.get('EvnPrescr_id')) {
						if(rec.get('Unique_id')){
							metadata.id = 'cell_composite_'+rec.get('Unique_id');
							metadata.tdAttr = " data-cell='cell_composite_"+rec.get('Unique_id')+"' ";
						}

						if (val && val == 'loading') {
							return '<img src="/img/icons/2017/preloader.gif" width="16" height="16" />';
						} else {
							if (me.PrescriptionType_Code == 11) {
								var cnt_all = rec.get('composition_cnt'),
									cnt = rec.get('compositionCntChecked');
								if (cnt_all > 1) {
									val = '<a href="#" ' +
										'onclick="Ext6.getCmp(\'' + me.id + '\').showCompositionMenu(this, ' +
										"'" + rec.get('Unique_id') + "'" +
										')">' + (cnt ? (cnt + '/' + cnt_all) : cnt_all) + '</a>';
								} else val = '';
							}
						}
						//}
						return val;
					}
				}, {
					text: 'Cito',
					align: 'left',
					dataIndex: 'UslugaComplex_IsCito',
					xtype: 'checkcolumn',
					style: {
						borderTop: 'none',
						borderRight: '1px solid #d0d0d0',
						color: '#000'
					},
					width: 44,
					listeners: {
						beforecheckchange: function( thisEl, rowIndex, checked, rec, e, eOpts ) {
							return !rec.get('EvnPrescr_id');
						},
						checkchange: function (view, rowIndex, colIndex, rec, e) {
							rec.commit();
							if(me.selectedCellsMenu) {
								var queueBtn = me.selectedCellsMenu.queryById('recToQueue');
								if (me.checkInQueueEnable(rec)) {
									queueBtn.enable();
								} else {
									queueBtn.disable();
								}
							}
							if(rec.get('UslugaComplex_IsCito')) {
								me.selRec(this, rec.get('Unique_id'));
							} else if(!rec.get('TimetableResource_id')) {
								me.resetTimetableResource(rec);
							} else if(!rec.get('TimetableMedService_id')) {
								me.resetTimetableMedService(rec);
							}
						}
					}
				}, {
					text: 'Цель исследования',
					align: 'center',
					style:{
						borderTop: 'none',
						color: '#000'
					},
					dataIndex: 'study_target',
					width: 130,
					renderer: function (val, metadata, rec) {
						var text = 'Диагностическая';
						if(val) {
							var StudyTargetEditor = Ext6.getCmp(me.id + '_StudyTargetEditor');
							var sel_rec = StudyTargetEditor.getStore().findRecord('StudyTarget_id', val);
							text = sel_rec.get('StudyTarget_Name');
						}

						return '<span style="white-space: nowrap;">' + text + '</span>';
					},
					editor: {
						xtype: 'swStudyTargetCombo',
						hideLabel: true,
						valueField: 'StudyTarget_id',
						displayField: 'StudyTarget_Name',
						queryMode: 'local',
						name: 'StudyTarget_Name',
						id: me.id + '_StudyTargetEditor',
					}
				},
				{
					header: 'Место оказания',
					dataIndex: 'location',
					renderer: function (val, metadata, rec) {
						if (!rec.get('UslugaComplex_Name')) return '';
						if (!rec.get('MedService_id')) {
							return '';
						}

						//если есть одна служба, то в этой колонке должен быть текст
						var text = rec.get('MedService_Nick');
						var hint = rec.get('MedService_Name') + ' / ' + rec.get('Lpu_Nick') + ' / ' +
							rec.get('LpuUnit_Name') + ' / ' + rec.get('LpuUnit_Address');
						// если это назначение лабораторной диагностики и есть пункт забора
						if (rec.get('pzm_MedService_id') || rec.get('Resource_id')) {
							//то отображаем пункт забора как место оказания
							if(rec.get('MedServiceType_SysNick') === 'func'){
								text = rec.get('MedService_Nick') + ' / ' + rec.get('Resource_Name');
								hint = rec.get('Lpu_Nick') + ' / ' + rec.get('MedService_Name') + ' / ' + rec.get('Resource_Name');
							} else {
								text = rec.get('pzm_MedService_Nick') + ' / ' + rec.get('MedService_Nick');
								hint = rec.get('Lpu_Nick') + ' / ' + rec.get('pzm_MedService_Name') + ' / ' + rec.get('MedService_Name');
							}
						}

						if (me.loadedByAllLpu || me.onlyByContract) {
							text = rec.get('Lpu_Nick') + ' / ' + text;
						}

						return '<span style="white-space: nowrap;" data-qtip="' + hint + '">' + text + '</span>';
					},
					width: 150,
					style:{
						borderTop: 'none',
						color: '#000'
					},
					editor: {
						xtype: 'swMedServicePrescrCombo',
						hideLabel: true,
						valueField: 'UslugaComplexMedService_key',
						displayField: 'displayField',
						queryMode: 'local',
						id: me.id + '_MedServiceEditor'
					}
				},
				{
					text: 'Ближ. запись',
					dataIndex: 'timetable',
					style:{
						borderTop: 'none',
						color: '#000'
					},
					border: false,
					width: 200,
					renderer: function (val, metadata, rec) {
						if (val && val == 'loading') {
							return '<img src="/img/icons/2017/preloader.gif" width="16" height="16" />';
						} else {
							if (!rec.get('UslugaComplex_Name')) return '';
							var text = me.renderTimetableBegTime(rec);
							return '<span id="render_timetable_begtime_' + rec.get('Unique_id') + '">' + text + '</span>';
						}

						return '';
					}
				}
			],
			requires: [
				'Ext6.ux.GridHeaderFilters'
			],
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				}),
				me.gridHeaderFilters,
				Ext6.create('Ext6.grid.plugin.CellEditing', {
					clicksToEdit: 1
				})
			],
			store: {
				groupField: 'Group_id',
				fields: [{
					name: 'MedService_Nick',
					type: 'string'
				}, {
					name: 'UslugaComplex_Name',
					type: 'string'
				}, {
					name: 'UslugaComplex_Code',
					type: 'string'
				}, {
					name: 'UslugaComplex_id',
					type: 'string'
				}, {
					name: 'UslugaComplex_IsCito',
					type: 'bool'
				}, {
					name: 'EvnPrescr_id',
					type: 'int',
					allowNull: true
				}, {
					name: 'EvnDirection_id',
					type: 'int',
					allowNull: true
				}, {
					name: 'PrescriptionType_Code',
					type: 'int',
					calculate: function (data) {
						var PrescriptionType_Code = null;
						if (!Ext6.isEmpty(me.PrescriptionType_Code)) {
							PrescriptionType_Code = (me.PrescriptionType_Code);
						}
						return PrescriptionType_Code;
					}
				},{
					name: 'EvnStatus_SysNick',
					type: 'string',
					allowNull: true
				}, {
					name: 'object',
					type: 'string',
					calculate: function (data) {
						var object = null;
						if (!Ext6.isEmpty(me.PrescriptionType_Code) && me.evnPrescrCntr) {
							object = me.evnPrescrCntr.getObjectByCode(me.PrescriptionType_Code);
						}
						return object;
					}
				}
				],
				autoLoad: false,
				folderSort: true,
				proxy: {
					extraParams: {
						uslugaCategoryList: '["gost2011"]'
					},
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MedService&m=getUslugaComplexSelectList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				listeners: {
					load: function(store, records, successful, operation, eOpts) {
						me.onLoadGrid(store, records, successful, operation, eOpts);
					},
					beforeload: function() {
						return me.allowLoadGrid; // разрешаем грузить грид, только если заданы параметры
					}
				},
				extend: 'Ext6.data.Store',
				//remoteFilter: true,
				pageSize: null
			}
		});

		/*me.showCodeCheckBox = Ext6.create('Ext6.form.Checkbox', {
			hideLabel: true,
			margin: '0 10 0 12',
			checked: true,
			boxLabel: 'Отображать код услуг',
			listeners: {
				'change': function(checkbox, newValue) {
					if (newValue) {
						me.showUslugaComplexCode = true;
					} else {
						me.showUslugaComplexCode = false;
					}

					me.UslugaComplexGrid.reconfigure();
				}
			}
		});*/

		/*me.onlyPrescript = Ext6.create('Ext6.form.Checkbox', {
			hideLabel: true,
			margin: '0 10 0 10',
			boxLabel: 'Только назначенные',
			listeners: {
				'change': function(checkbox, newValue) {
					var grid = me.UslugaComplexGrid;
					if (newValue) {
						grid.getStore().filterBy(function (rec) {
							return (rec.get('UslugaComplexMedService_HasPrescr') || rec.get('EvnPrescr_id'));
						});
					}
					else{
						grid.getStore().clearFilter();
					}
				}
			}
		});*/

		me.contractCheckBox = Ext6.create('Ext6.form.Checkbox', {
			hideLabel: true,
			margin: '0 10 0 0',
			boxLabel: 'Только услуги по договорам',
			listeners: {
				'change': function(checkbox, newValue) {
					if (newValue) {
						me.onlyByContract = true;
						me.LpuFilterCombo.setValue(-1);
						me.LpuFilterCombo.disable();
						me.MedServiceFilterCombo.disable();
					} else {
						me.onlyByContract = false;
						me.LpuFilterCombo.setValue(getGlobalOptions().lpu_id);
						me.LpuFilterCombo.enable();
						me.MedServiceFilterCombo.enable();
					}

					me.loadUslugaComplexGrid();
				}
			}
		});

		me.toolMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [me.showCodeCheckBox, me.contractCheckBox]
		});

		me.comboBar = Ext6.create('Ext6.toolbar.Toolbar', {
			border: false,
			docked: 'top',
			padding: '0 60px 0 40px',
			margin: '18px 0 6px 0',
			defaults:{
				labelStyle: 'padding:7px 5px 3px 0;',
			},
			items: [
				me.LpuFilterCombo,
				me.MedServiceFilterCombo,
				me.PrescriptionTypeCombo
			]
		});
		me.filterBar = Ext6.create('Ext6.toolbar.Toolbar', {
			border: false,
			docked: 'top',
			padding: '0 60px 0 40px',
			margin: '0 0 15px 0',
			defaults:{
				labelStyle: 'padding:7px 5px 3px 0;',
			},
			items: [
				me.UslugaComplexFilterCombo,
				me.contractCheckBox,
				//me.showCodeCheckBox,
				//me.onlyPrescript,
				{
					xtype: 'tbspacer',
					flex: 1
				},
				{
					xtype: 'button',
					text: 'Найти',
					style:{
						font: '400 12px/1.4em Roboto;'
					},
					cls: 'button-primary button-primary-min',
					margin: '0 0 0 10',
					handler: function () {
						me.loadUslugaComplexGrid();
					}
				},
				{
					xtype: 'button',
					text: 'Очистить',
					style:{
						font: '400 12px/1.4em Roboto;'
					},
					cls: 'button-secondary button-secondary-min',
					margin: '0 7 0 10',
					handler: function () {
						me.UslugaComplexFilterCombo.clearValue();
						me.contractCheckBox.setValue(false);
						//me.UslugaComplexGrid.clearHeaderFilters();
					}
				}
			]
		});
		this.PatientInfoPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			height: 200,
			flex: 1,
			margin: '0 0 0 30px',
			cls: 'patient-apply-panel',
			bodyStyle:{
				background: 'transparent',
				margin: 0
			},
			params: {
				fio: '',
				diag: '',
				count: 0
			},
			setParams: function(params){
				var data = Ext6.Object.merge(this.params,params);
				this.applyData(data);
			},
			clearParams: function(){
				this.applyData({fio: '', diag: '', count: 0});
			},
			tpl: new Ext6.Template([
				'Для пациента <span>{fio}</span> {diag} выбрано назначений: {count}'
			]),
			items: [

			]
		});
		
		me.addButton = Ext6.create('Ext6.button.Button', {
			xtype: 'button',
			cls: 'button-primary-white button-primary-white-min',
			style:{
				font: '400 12px/1.4em Roboto'
			},
			text: 'Добавить',
			disabled: me.UslugaComplexGrid.getSelectionModel().getSelection().length == 0,
			handler: function () {
				if (me.PacketPrescr_id == null) {
					me.applySelectedPrescribe();
				} else {
					me.applyToPacket();
				}
			},
			margin: '0 0 0 10'
		});

		me.addButtonWithRec = Ext6.create('Ext6.button.Button', {
			xtype: 'button',
			text: 'Добавить с записью',
			disabled: me.UslugaComplexGrid.getSelectionModel().getSelection().length == 0,
			cls: 'button-primary-white button-primary-white-min',
			style:{
				font: '400 12px/1.4em Roboto'
			},
			margin: '0 25 0 10',
			handler: function () {
				var selection = me.UslugaComplexGrid.getSelectionModel().getSelection();
				me.applySelectedPrescribeWithRecord(selection, me, selection.length===1);
			}
		});
		
		me.footerBar = Ext6.create('Ext6.toolbar.Toolbar', {
			style: 'background-color: #2196f3',
			cls: 'packet-select-footer',
			padding:'9 0 17 6',
			height: 50,
			margin: 0,
			layout: {
				type: 'hbox',
				pack: 'end',
				align: 'stretch'
			},
			border: false,
			items: [
				this.PatientInfoPanel,
				{
					xtype: 'button',
					cls: 'button-secondary-blue button-secondary-blue-min',
					style:{
						font: '400 12px/1.4em Roboto'
					},
					text: 'Отмена',
					handler: function () {
						me.hide();
					},
					margin: '0 0 0 10'
				}, 
				me.addButton,
				me.addButtonWithRec
			]
		});
		Ext6.apply(me, {
			autoHeight: true,
			dockedItems: [
				me.comboBar,
				me.filterBar
			],
			items: [me.UslugaComplexGrid],
			bbar: me.footerBar
		});

		this.callParent(arguments);
	}
});