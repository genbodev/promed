/**
 * EvnPrescrUslugaInputPanel - Назначение услуг ExtJS 6
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.SpecificationDetail.EvnPrescrUslugaInputPanel', {
	/* свойства */
	//height: 'auto',
	alias: 'widget.EvnPrescrUslugaInputPanel',
	autoShow: false,
	closable: true,
	cls: 'EvnPrescrUslugaInputPanel',
	constrain: true,
	//extend: 'Ext6.panel.Panel',
	extend: 'base.BaseFormPanel',
	findWindow: false,
	header: false,
	modal: true,
	layout: 'fit',
	refId: 'EvnPrescrUslugaInputPanel',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Назначения. Диагностика',
	border: false,
	width: '100%',
	bodyPadding: 10,
	//autoHeight: true,
	//maxWidth: 1193,
	//height: 500,
	data: {},
	parentPanel: {},
	show: function (data) {
		var me = this;

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		} else {
			me.callback = Ext6.emptyFn;
		}

		me.data = data; // вот эту строчку никогда бы не удалять
		me.PrescriptionType_Code = null;
		me.MedServiceType_SysNick = 'func';
		switch(me.data.objectPrescribe) {
			case 'EvnCourseProc':
				me.PrescriptionType_Code = 6;
				me.MedServiceType_SysNick = 'prock';
				break;
			case 'EvnPrescrLabDiag':
				me.PrescriptionType_Code = 11;
				me.MedServiceType_SysNick = 'lab';
				break;
			case 'EvnPrescrFuncDiag':
				me.PrescriptionType_Code = 12;
				break;
			case 'EvnPrescrConsUsluga':
				me.PrescriptionType_Code = 13;
				break;
			case 'EvnPrescrOperBlock':
				me.PrescriptionType_Code = 7;
				me.MedServiceType_SysNick = 'oper';
				break;
			default:
				Ext6.Msg.alert("Ошибка", "Неизвестный тип назначения: " + me.data.objectPrescribe);
				break;
		}

		me.MedServiceFilterCombo.getStore().proxy.extraParams.MedServiceType_SysNick = me.MedServiceType_SysNick;
		me.UslugaComplexGrid.getStore().proxy.extraParams.Evn_id = me.data.Evn_id;
		this.callParent(arguments);
		me.UslComplexFilterCombo.focus();
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

		if (this.UslComplexFilterCombo.getValue() > 0) {
			extraParams.filterByUslugaComplex_id = this.UslComplexFilterCombo.getValue();
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

		switch (this.PrescriptionType_Code) {
			case 6: // Манипуляции и процедуры
				this.UslugaComplexGrid.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['manproc']);
				break;
			case 7: // Оперативное лечение
				this.UslugaComplexGrid.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['oper']);
				break;
			case 11: // Лабораторная диагностика
				this.UslugaComplexGrid.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['lab']);
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

		this.UslugaComplexGrid.getStore().proxy.extraParams.formMode = 'ExtJS6';

		this.allowLoadGrid = true;

		this.UslugaComplexGrid.getStore().load({
			params: params,
			addRecords: options.addRecords,
			callback: function() {
				me.UslComplexFilterCombo.focus();
				if (typeof options.callback == 'function') {
					options.callback();
				}
			}
		})
	},
	loadComposition: function(rec) {
		var me = this;

		rec.set('composition', 'loading');
		rec.commit();

		me.parentPanel.getController().loadUslugaComplexComposition({
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
		var me = this;
		var save_url = null;
		var EvnPrescr_id = null;

		var rec = options.rec;
		var MedService_id = rec.get('MedService_id');

		var params = {
			PersonEvn_id: me.data.PersonEvn_id,
			Server_id: me.data.Server_id,
			parentEvnClassSysNick: "EvnVizitPL",
			DopDispInfoConsent_id: '',
			StudyTarget_id: '2', // Тип
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

		me.mask('Сохранение назначения');
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
					if (list.length > 0) {
						params.EvnPrescrLabDiag_uslugaList = list.toString();
						params.EvnPrescrLabDiag_CountComposit = list.length;
					}
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
							me.unmask();

							if (!options.withoutInfoMsg) {
								sw4.showInfoMsg({
									panel: me,
									type: 'warning',
									text: 'Услуга добавлена. Требуется запись.'
								});
							}
						},
						params: params
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
				me.unmask();

				if (!options.withoutInfoMsg) {
					sw4.showInfoMsg({
						panel: me,
						type: 'warning',
						text: 'Услуга добавлена. Требуется запись.'
					});
				}
			},
			params: params
		});
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
				rec.set('EvnDirection_id', null);
				rec.set('EvnPrescr_id', null);
				rec.commit();
			}
		});

		return true;
	},
	cancelEvnDirection: function(rec) {
		var me = this;

		if (!rec.get('EvnDirection_id')) {
			return false;
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
								rec.set('EvnDirection_id', null);
								rec.commit();
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

		me.UslugaComplexGrid.getEl().query(".show-more-div").forEach(function(showMoreDiv) {
			showMoreDiv.remove();
		});

		if (me.byAllLpu) {
			me.loadedByAllLpu = true;
			me.byAllLpu = false;
			me.setUslugaComplexGridFilters();
		} else {
			me.loadedByAllLpu = false;
		}

		if (operation.request && operation.request.config && operation.request.config.params && operation.request.config.params.expandOnLoad) {
			if (records.length > 0) {
				var Group_id = records[0].data.Group_id;

				var undeleteIds = [];
				for(var k in records) {
					if (records[k].data && records[k].data.id) {
						undeleteIds.push(records[k].data.id)
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

				me.groupingFeature.expand(Group_id);
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
		var me = this;

		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		if (!rec) {
			return false;
		}

		me.timetableMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Записать на это время',
				handler: function() {
					me.doApply({
						rec: rec
					});
				}
			}, {
				text: 'Выбрать другое время',
				handler: function() {
					me.openTTMSScheduleRecordPanel(key);
				}
			}]
		});

		me.timetableMenu.showBy(link);
	},
	showEvnDirectionMenu: function(link, key) {
		var me = this;

		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		if (!rec) {
			return false;
		}

		var begTime = rec.get('withResource') ? rec.get('TimetableResource_begTime') : rec.get('TimetableMedService_begTime');

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
	showCompositionMenu: function(link, key) {
		var me = this;
		var rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', key);
		if (!rec) {
			return false;
		}

		if (!rec.compositionMenu) {
			me.mask('Получение состава услуги...');
			Ext6.Ajax.request({
				params: {
					UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id'),
					UslugaComplex_pid: rec.get('UslugaComplex_id'),
					Lpu_id: rec.get('Lpu_id')
				},
				callback: function(opt, success, response) {
					me.unmask();
					if (response && response.responseText) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						if (response_obj.length > 0) {
							rec.compositionMenu = Ext6.create('Ext6.menu.Menu', {
								cls: 'timetable-menu',
								items: [],
								buttons: ['->', {
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
										var compositionCntChecked = 0;
										rec.compositionMenu.items.each(function(item){
											if (item.xtype == 'menucheckitem') {
												compositionCntAll++;
												if (item.checked) {
													compositionCntChecked++;
												}
											}
										});

										rec.set('compositionCntChecked', compositionCntChecked);
										rec.set('compositionCntAll', compositionCntAll);
										rec.commit();
									}
								}]
							});
							for (var i = 0; i < response_obj.length; i++) {
								rec.compositionMenu.add({
									text: response_obj[i].UslugaComplex_Name,
									UslugaComplex_id: response_obj[i].UslugaComplex_id,
									hideLabel: true,
									xtype: 'menucheckitem',
									rec: rec,
									checked: true
								});
							}

							rec.compositionMenu.add({
								xtype: 'menuseparator'
							});

							rec.compositionMenu.showBy(link);
						}
					}
				},
				url: '/?c=MedService&m=loadCompositionMenu'
			});
		} else {
			rec.compositionMenu.showBy(link);
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
		me.parentPanel.getController().openSpecification('TTMSScheduleRecordPanel', me.data.grid, rec, true);
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
											(TimetableMedService_id && record.get('TimetableMedService_id') == TimetableMedService_id)
											|| (TimetableResource_id && record.get('TimetableResource_id') == TimetableResource_id)
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
	doApplyToQueue: function(link, key) {
		this.doApply({
			key: key,
			toQueue: true
		});
	},
	doApply: function(options) {
		if (!options) {
			options = {};
		}
		var me = this;

		var rec = options.rec;
		if (!rec) {
			rec = this.UslugaComplexGrid.getStore().findRecord('Unique_id', options.key);
			if (!rec) {
				return false;
			}
		}

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
			rec.set('EvnDirection_id', data.EvnDirection_id);
			rec.commit();

			me.getTimetableNext(rec);
			me.callback();
		};

		me.parentPanel.getController().saveEvnDirection(params);
	},
	setCito: function(rec){
		if (rec.get('EvnPrescr_id')) {
			var me = this,
				cito = (rec.get('UslugaComplex_IsCito'))?2:1;
			me.mask('Сохранение параметра');
			var cb = function(success){
				me.unmask();
				me.callback();
			};
			this.parentPanel.getController().setCito(rec.get('EvnPrescr_id'),cito,cb());
		}
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
						rec.set('nolimit', 1);
						rec.commit();
					}
				}
			},
			params: {
				UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id'),
				pzm_MedService_id: rec.get('pzm_MedService_id'),
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
		if (groupName.indexOf('_') > -1) {
			var groupParts = groupName.split('_');
			MedService_id = groupParts[0];
			pzm_MedService_id = groupParts[1];
		}

		me.loadUslugaComplexGrid({
			addRecords: true,
			onExpandGroup: true,
			MedService_id: MedService_id,
			pzm_MedService_id: pzm_MedService_id,
			byAllLpu: me.loadedByAllLpu,
			callback: function() {
				if (typeof callback == 'function') {
					callback();
				}
			}
		});
	},
	renderTimetableBegTime: function(rec) {
		var me = this;

		var begTime = rec.get('withResource') ? rec.get('TimetableResource_begTime') : rec.get('TimetableMedService_begTime');
		var key = rec.get('Unique_id');

		if (rec.get('EvnDirection_id')) {
			if (begTime) {
				var dt = Date.parseDate(begTime, 'd.m.Y H:i');
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
				var dt = Date.parseDate(begTime, 'd.m.Y H:i');
				text = '<a class="float-left" href="#" ' +
					'onclick="Ext6.getCmp(\'' + me.id + '\').showTimetableMenu(this, ' +
					"'" + key + "'" +
					')">' + dt.format('d.m.Y D H:i').toLowerCase() + '</a>';
				text += '<a class="prescr-queue-button" data-qtip="Поставить в очередь" href="#" ' + 'onclick="Ext6.getCmp(\'' + me.id + '\').doApplyToQueue(this, ' + "'" + key + "'" + ')"></a>';
			} else if (rec.get('nolimit')) {
				text = '<span class="float-left">Нет бирок</span>';
				text += '<a class="prescr-queue-button" data-qtip="Поставить в очередь" href="#" ' + 'onclick="Ext6.getCmp(\'' + me.id + '\').doApplyToQueue(this, ' + "'" + key + "'" + ')"></a>';
			} else {
				text = '<a class="prescr-timtable-button onrowhover" data-qtip="Уточнить расписание далее 14 дней" href="#" ' + 'onclick="Ext6.getCmp(\'' + me.id + '\').getTimetableNoLimit(this, ' + "'" + key + "'" + ')"></a>';
				text += '<a class="prescr-queue-button onrowhover" data-qtip="Поставить в очередь" href="#" ' + 'onclick="Ext6.getCmp(\'' + me.id + '\').doApplyToQueue(this, ' + "'" + key + "'" + ')"></a>';
			}
		}
		return text;
	},
	/* конструктор */
	initComponent: function() {
		var me = this;

		me.UslComplexFilterCombo = Ext6.create('swUslugaComplexSearchCombo', {
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
			labelWidth: 42,
			minWidth: 42 + 500,
			emptyText: 'Поиск услуги по коду или наименованию'
		});

		me.LpuFilterCombo = Ext6.create('swLpuCombo', {
			additionalRecord: {
				value: -1,
				text: langs('Все'),
				code: 0
			},
			anyMatch: true,
			hideEmptyRow: true,
			labelWidth: 30,
			width: 180+30,
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
			hideEmptyRow: true,
			queryMode: 'local',
			labelWidth: 40,
			width: 180+40,
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
			labelAlign: 'left',
			fieldLabel: 'Место',
			name: 'MedService_id'
		});

		this.groupingFeature = Ext6.create('swGridPrescrGroupingFeature', {
			enableGroupingMenu: false,
			onBeforeGroupClick: function(view, rowElement, groupName, e) {
				log('onBeforeGroupClick', view, rowElement, groupName, e);

				var groupIsCollapsed = !me.groupingFeature.isExpanded(groupName);
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
						if (rows[0] && rows[0].get('pzm_MedService_Nick')) {
							s = s + rows[0].get('pzm_MedService_Nick');
						}
						if (rows[0] && rows[0].get('MedService_Nick')) {
							if (s.length > 0) {
								s = s + ' / ';
							}
							s = s + rows[0].get('MedService_Nick');
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
			cls: 'grid-common',
			xtype: 'grid',
			alias: 'widget.EvnPrescrUslugaInputGrid',
			viewModel: true,
			buttonAlign: 'center',
			scrollable: true,
			autoHeight: true,
			autoWidth: true,
			frame: false,
			border: false,
			default: {
				border: 0
			},
			bind: {
				selection: '{theRow}'
			},
			selModel: {
				mode: 'SINGLE'
			},
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
				},
				edit: function(grid, context) {
					log('edit', grid, context);
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
					var cls = '';
					if (rec.get('EvnDirection_id')) {
						cls = cls + 'x-grid-rowbacklightgreen x-grid-checkbox-disabled ';
					} else if (rec.get('EvnPrescr_id')) {
						cls = cls + 'x-grid-rowbacklightblue x-grid-checkbox-disabled ';
					}
					return cls;
				}
			},
			getStr: function(rec){
				var s = '';

				if (me.showUslugaComplexCode) {
					s += '<b>' + rec.get('UslugaComplex_Code') + '</b> ';
				}

				s += rec.get('UslugaComplex_Name');
				return s;
			},
			columns: [
				{
					text: '',
					filter: {
						scale: 'small',
						text: '',
						tooltip: 'Развернуть все',
						userCls: 'button-without-frame coll-exp-all button-expand-all',
						margin: '6 0 0 0',
						padding: '5 0 5 15',
						style: {
							visibility: 'hidden'
						},
						pressed: true,
						enableToggle: true,
						toggleHandler: function (button, pressed, eOpts) {
							this.toggleCls('button-expanded-all');

							if (pressed) {
								this.setTooltip('Развернуть все');
								me.groupingFeature.collapseAll();
							} else {
								this.setTooltip('Свернуть все');
								me.expandAllGroups();
							}
						},
						xtype: 'button',
						isValid: function () {return true;},
						setValue: function () {},
						getValue: function () {return '';},
						getRawValue: function () {return '';}
					},
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
					width: 44
				}, {
					text: 'Услуга',
					/*filter: {
						type: 'string',
						xtype: 'swUslugaComplexSearchCombo',
						anchor: '-30',
						filterByValue: true,
						listeners: {
							'render': function(combo) {
								combo.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['gost2011']);
							},
							'change': function(combo, newValue, oldValue) {
								me.gridHeaderFilters.applyFilters();
							}
						},
						hideLabel: true,
						emptyText: 'Поиск услуги по коду или наименованию'
					},*/
					dataIndex: 'UslugaComplex_id',
					renderer: function (val, metaData, rec) {
						return me.UslugaComplexGrid.getStr(rec);
					},
					sorter: function (item1, item2) {
						var g = me.UslugaComplexGrid,
							lhs, rhs;

						lhs = g.getStr(item1);
						rhs = g.getStr(item2);

						return (lhs > rhs) ? 1 : (lhs < rhs ? -1 : 0);
					},
					flex: 3,
					minWidth: 390
				}, {
					text: 'Состав',
					dataIndex: 'composition',
					width: 75,
					renderer: function (val, metadata, rec) {
						if (rec.get('EvnPrescr_id')) {
							if (val && val == 'loading') {
								return '<img src="/img/icons/2017/preloader.gif" width="16" height="16" />';
							} else {
								if (me.PrescriptionType_Code == 11) {
									if (rec.get('compositionCntAll') > 0) {
										return '<a href="#" ' +
											'onclick="Ext6.getCmp(\'' + me.id + '\').showCompositionMenu(this, ' +
											"'" + rec.get('Unique_id') + "'" +
											')">' + rec.get('compositionCntChecked') + '/' + rec.get('compositionCntAll') + '</a>';
									}
								}
							}
						}

						return '';
					}
				}, {
					text: 'Cito!',
					dataIndex: 'UslugaComplex_IsCito',
					align: 'left',
					xtype: 'checkcolumn',
					listeners: {
						'checkchange': function (column, rowIndex, checked, rec, e, eOpts) {
							rec.commit();
							me.setCito(rec);
						}
					},
					width: 60
				}, {
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
						if (rec.get('pzm_MedService_id')) {
							//то отображаем пункт забора как место оказания
							text = rec.get('pzm_MedService_Nick') + ' / ' + rec.get('MedService_Nick');
							hint = rec.get('Lpu_Nick') + ' / ' + rec.get('pzm_MedService_Name') + ' / ' + rec.get('MedService_Name');
						}

						if (me.loadedByAllLpu || me.onlyByContract) {
							text = rec.get('Lpu_Nick') + ' / ' + text;
						}

						return '<span style="white-space: nowrap; text-overflow: ellipsis" data-qtip="' + hint + '">' + text + '</span>';
					},
					width: 150,
					editor: {
						xtype: 'swMedServicePrescrCombo',
						hideLabel: true,
						valueField: 'UslugaComplexMedService_key',
						displayField: 'displayField',
						queryMode: 'local',
						id: me.id + '_MedServiceEditor'
					}
				}, {
					text: 'Ближ. запись',
					dataIndex: 'timetable',
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
					name: 'EvnPrescr_id',
					type: 'int',
					allowNull: true
				}, {
					name: 'EvnDirection_id',
					type: 'int',
					allowNull: true
				}],
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

		me.showCodeCheckBox = Ext6.create('Ext6.form.Checkbox', {
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
		});

		me.contractCheckBox = Ext6.create('Ext6.form.Checkbox', {
			hideLabel: true,
			margin: '0 10 0 10',
			boxLabel: 'Услуги по договорам',
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

		me.onlyPrescript = Ext6.create('Ext6.form.Checkbox', {
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
		});

		me.toolMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [me.showCodeCheckBox, me.contractCheckBox]
		});

		me.toolPanel = Ext6.create('Ext6.Toolbar', {
			height: 36,
			width: 40,
			border: false,
			margin: '0 3 0 0',
			noWrap: true,
			right: 0,
			items: [{
				userCls: 'button-without-frame',
				iconCls: 'panicon-theedots',
				tooltip: langs('Меню'),
				handler : function() {
					me.toolMenu.showBy(this);
				}
			}]
		});

		Ext6.apply(me, {
			autoHeight: true,
			tbar: {
				xtype: 'panel',
				layout: 'fit',
				border: false,
				defaults: {
					border: false
				},
				padding: 0,
				items: [
					{
						xtype: 'panel',
						layout: 'anchor',
						anchor: '100%',
						defaults: {
							border: false
						},
						items: [
							{
								xtype: 'panel',
								layout: 'hbox',
								width: '100%',
								userCls: 'med-service-filter-toolbar',
								bodyPadding: '17 0 17 19',
								items: [
									me.UslComplexFilterCombo,
									{
										xtype: 'button',
										text: 'Найти',
										cls: 'button-primary',
										margin: '0 0 0 10',
										handler: function () {
											me.loadUslugaComplexGrid();
										}
									}
								]
							},
							{
								xtype: 'panel',
								layout: 'fit',
								autoHeight: true,
								bodyPadding: '0 10',
								//height: 185,
								items: [{
									xtype: 'fieldset',
									margin: 0,
									cls: 'usluga-add-filters-fieldset',
									title: 'Фильтры',
									layout: 'anchor',
									defaults: {
										anchor: '100%'
									},
									collapsible: true,
									collapsed: false,
									items: [
										{
											xtype: 'panel',
											layout: 'fit',
											border: false,
											bodyPadding: '0 0 0 9',
											// userCls: 'med-service-filter-toolbar',
											items: [
												{
													defaults: {
														margin: "5px 5px 0 5px"
													},
													//flex: 1,
													layout: 'hbox',
													border: false,
													items: [
														me.LpuFilterCombo,
														me.MedServiceFilterCombo,
														{
															xtype: 'tbspacer',
															flex: 1,
														}, {
															xtype: 'fieldcontainer',
															fieldLabel: 'Сортировка',
															width: 80,
															labelWidth: 80,
															margin: '9 0 0 0',
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
														}]
												}
											]
										},
										{
											xtype: 'panel',
											layout: 'hbox',
											margin: '5 0 0 0',
											bodyPadding: '0 0 0 9',
											border: false,
											//userCls: 'med-service-filter-toolbar',
											items: [
												{
													defaults: {
														margin: "5px 5px 0 5px"
													},
													flex: 1,
													layout: 'column',
													border: false,
													items: [me.showCodeCheckBox, me.contractCheckBox, me.onlyPrescript]
												}
											]
										}
									]
								}]
							}
						]
					}
				]
			},
			items: [me.UslugaComplexGrid]
		});

		this.callParent(arguments);
	}
});