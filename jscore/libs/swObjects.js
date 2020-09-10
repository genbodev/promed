/**
 * swObjects. Логические объекты, повсеместно используемые в приложении.
 * @package      Libs
 * @access       public
 * @copyright    Copyright © 2012 Swan Ltd.
 * @version      10.2012
 */


/** Набор методов для направления (в т.ч. записи, постановки в очередь, заказ услуг)
 * Направление по записи состоит из нескольких шагов
 * 1а. Запись на бирку (если это параклиника, то сначала заказ услуги, а потом запись)
 * 2а. Создание электронного или системного направления
 * Направление с постановкой в очередь состоит из нескольких шагов (// для стаца и полки сначала надо создать направление (не всегда), а потом ставить в очередь)
 * 1б. Создание электронного или системного направления
 * 2б. Постановка в очередь (если это параклиника, то сначала заказ услуги, а потом постановка)
 */
sw.Promed.Direction = {
	print: function (params) {
		if (!params || !params.EvnDirection_id) {
			return false;
		}

		var pars = {
			EvnDirection_id: params.EvnDirection_id
		};

		if (!Ext.isEmpty(params.PrescriptionType_id)) {
			pars.PrescriptionType_id = params.PrescriptionType_id;
		}

		getGlobalLoadMask('Получение данных направления...').show(); // не понятно к чему привязывать loadMask, поэтому глобальный лоад маск.
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=getEvnDirectionForPrint',
			params: pars,
			callback: function(options, success, response)  {
				getGlobalLoadMask().hide();
				if (success) {
					var result  = Ext.util.JSON.decode(response.responseText);

					if (getRegionNick() == 'kz') {
						if (result.DirType_Code && result.DirType_Code.inlist([1, 5])) {
							printBirt({
								'Report_FileName': 'rec_EvnDirection_Stac.rptdesign',
								'Report_Params': '&paramEvnDirection=' + params.EvnDirection_id,
								'Report_Format': 'pdf'
							});
						} else {
							printBirt({
								'Report_FileName': 'rec_EvnDirection_Usl.rptdesign',
								'Report_Params': '&paramEvnDirection=' + params.EvnDirection_id,
								'Report_Format': 'pdf'
							});
						}
					} else if (getRegionNick() == 'ekb' && (!result.DirType_Code || result.DirType_Code != 8)) {
						printBirt({
							'Report_FileName': 'HospNapr.rptdesign',
							'Report_Params': '&paramEvnDirection_id=' + params.EvnDirection_id,
							'Report_Format': 'pdf'
						});
					} else if (getRegionNick().inlist(['krym','buryatiya']) && (result.DirType_Code && result.DirType_Code.inlist([1, 2, 3, 5]))) {
						printBirt({
							'Report_FileName': 'printEvnDirection_HospSurvCons.rptdesign',
							'Report_Params': '&paramEvnDirection=' + params.EvnDirection_id,
							'Report_Format': 'pdf'
						});
					} else {

						//если это исследование
						var addParams = '';
						if (result.DirType_Code == 9){
							if (Ext.globalOptions.lis.PrintMnemonikaDirections){// включена опция печати тестов с мнемоникой
								addParams += '&PrintMnemonikaDirections=1';
							}
							else if (Ext.globalOptions.lis.PrintResearchDirections){// или просто опция печати исследований
								addParams += '&PrintResearchDirections=1';
							}
						}
						if (
							getRegionNick() == 'perm' &&
							result.DirType_Code == 9 &&
							result.MedServiceType_SysNick != 'func' &&
							!Ext.isEmpty(Ext.globalOptions.lis.direction_print_form) &&
							Ext.globalOptions.lis.direction_print_form == 2
						) {
							printBirt({
								'Report_FileName': 'printEvnDirectionCKDL.rptdesign',
								'Report_Params': '&paramEvnDirection=' + params.EvnDirection_id,
								'Report_Format': 'pdf'
							});
						} else {
							printBirt({
								'Report_FileName': 'printEvnDirection.rptdesign',
								'Report_Params': '&paramEvnDirection=' + params.EvnDirection_id  + addParams,
								'Report_Format': 'pdf'
							});
						}
					}
				}
			}
		});
	},
	doDelete: function(opt){
		if(opt&&opt.id){
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=deleteEvnDirection',
				callback: function (options, success, response) {
					if(opt.callback){
						opt.callback();
					}
				},
				params: {
					EvnDirection_id: opt.id
				}
			});
		}
	},
	/**
	 * Перезаписать - Выбрать другое время записи
	 */
	rewrite:  function(cfg) {
		if (!cfg || !cfg.EvnDirection_id
			|| !cfg.userMedStaffFact
		) {
			return false
		}
		if (cfg.loadMask) cfg.loadMask.show();
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=getDataEvnDirection',
			callback: function (options, success, response) {
				if (cfg.loadMask) cfg.loadMask.hide();
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					getWnd('swDirectionMasterWindow').show({
						userMedStaffFact: cfg.userMedStaffFact,
						useCase: 'rewrite',
						dirTypeData: {
							DirType_id: result[0].DirType_id,
							DirType_Code: result[0].DirType_Code,
							DirType_Name: result[0].DirType_Name
						},
						directionData: result[0],
						personData: {
							Person_FirName: result[0].Person_FirName,
							Person_SecName: result[0].Person_SecName,
							Person_SurName: result[0].Person_SurName,
							Person_Birthday: result[0].Person_BirthDay,
							Person_id: result[0].Person_id,
							PersonEvn_id: result[0].PersonEvn_id,
							Server_id: result[0].Server_id
						},
						onDirection: cfg.callback || Ext.emptyFn
					});
				} else
					sw.swMsg.alert(langs('Ошибка'), langs('Произошла ошибка.'));
			},
			params: {
				EvnDirection_id: cfg.EvnDirection_id
			}
		});
		return true;
	},
	/**
	 * Перенаправить - Выбрать другой объект для направления
	 */
	redirect:  function(cfg) {
		return false;
		if (!cfg || !cfg.EvnDirection_id
			|| !cfg.userMedStaffFact
		) {
			return false;
		}
		if (cfg.loadMask) cfg.loadMask.show();
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=getDataEvnDirection',
			callback: function (options, success, response) {
				if (cfg.loadMask) cfg.loadMask.hide();
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					var params = {
						userMedStaffFact: cfg.userMedStaffFact,
						type: 'redir',
						RedirTimetableData: {
							type: result[0].type,
							EvnDirection_id: result[0].EvnDirection_id,
							EvnDirection_Num: result[0].EvnDirection_Num,
							MedStaffFact_id: result[0].MedStaffFact_id,
							LpuUnit_did: result[0].LpuUnit_did,
							Lpu_did: result[0].Lpu_did,
							MedPersonal_did: result[0].MedPersonal_did,
							LpuSection_did: result[0].LpuSection_did,
							LpuSectionProfile_id: result[0].LpuSectionProfile_id,
							DirType_id: result[0].DirType_id,
							DirType_Code: result[0].DirType_Code,
							ARMType_id: result[0].ARMType_id,
							MedService_id: result[0].MedService_id,
							UslugaComplexMedService_id: result[0].UslugaComplexMedService_id,
							MedService_Nick: result[0].MedService_Nick
						},
						personData: {
							Person_FirName: result[0].Person_FirName,
							Person_SecName: result[0].Person_SecName,
							Person_SurName: result[0].Person_SurName,
							Person_id: result[0].Person_id,
							PersonEvn_id: result[0].PersonEvn_id,
							Server_id: result[0].Server_id
						},
						onDirection: cfg.callback || Ext.emptyFn
					}
					getWnd('swDirectionMasterWindow').show(params);
				} else
					sw.swMsg.alert(langs('Ошибка'), langs('Произошла ошибка.'));
			},
			params: {
				EvnDirection_id: cfg.EvnDirection_id
			}
		});
		return true;
	},
	/**
	 * Отменить/Отклонить направление c указанием причины (QueueFailCause_id/DirFailType_id)
	 * и записью EvnStatusCause_id, EvnStatusHistory_Cause
	 */
	cancel:  function(cfg) {
		log('sw.Promed.Direction.cancel', cfg);

		if (!cfg || !cfg.cancelType || !cfg.ownerWindow) {
			return false;
		}
		if (!cfg.callback) {
			cfg.callback = Ext.emptyFn;
		}
		/*if (!cfg.formType) {
			cfg.formType = 'reg';
		}*/
		if (!cfg.formType) {
			cfg.formType = 'common';
		}
		if (!cfg.TimetableGraf_id && !cfg.TimetableMedService_id && !cfg.TimetableStac_id && !cfg.EvnQueue_id && !cfg.TimetableResource_id && !cfg.EvnDirection_id) {
			return false; // нечего отменять
		}

		getWnd('swSelectEvnStatusCauseWindow').show({
			Evn_id: cfg.EvnDirection_id,
			EvnClass_id: 27,
			formType: cfg.formType,
			callback: function(data) {
				if (!Ext.isEmpty(data.EvnStatusCause_id)) {
					// продолжаем творить мутные дела
					if (cfg.TimetableGraf_id) {
						// отмена направления на бирку
						cfg.ownerWindow.getLoadMask(langs('Отмена направления')).show();
						submitClearTime({
								id: cfg.TimetableGraf_id,
								cancelType: cfg.cancelType,
								person_id: cfg.person_id,
								TimetableGrafRecList_id: cfg.TimetableGrafRecList_id,
								type: 'polka',
								EvnStatusCause_id: data.EvnStatusCause_id,
								EvnComment_Comment: data.EvnStatusHistory_Cause
							},
							function (response) {
								cfg.ownerWindow.getLoadMask().hide();
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if (!answer.success) {
										if (answer.Error_Code) {
											Ext.Msg.alert(langs('Ошибка #') + answer.Error_Code, answer.Error_Message);
										} else if (!answer.Error_Msg) {
											Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>освобождение приема невозможно</b>!'));
										}
									} else {
										cfg.callback(cfg);
									}
								} else {
									Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>отсутствует ответ сервера</b>.'));
								}
							},
							function () {
								cfg.ownerWindow.getLoadMask().hide();
							}
						);
					} else if (cfg.TimetableMedService_id) {
						// отмена направления на бирку
						cfg.ownerWindow.getLoadMask(langs('Отмена направления')).show();
						submitClearTime({
								id: cfg.TimetableMedService_id,
								cancelType: cfg.cancelType,
								type: 'medservice',
								EvnStatusCause_id: data.EvnStatusCause_id,
								EvnComment_Comment: data.EvnStatusHistory_Cause
							},
							function (response) {
								cfg.ownerWindow.getLoadMask().hide();
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if (!answer.success) {
										if (answer.Error_Code) {
											Ext.Msg.alert(langs('Ошибка #') + answer.Error_Code, answer.Error_Message);
										} else if (!answer.Error_Msg) {
											Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>освобождение приема невозможно</b>!'));
										}
									} else {
										cfg.callback(cfg);
									}
								} else {
									Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>отсутствует ответ сервера</b>.'));
								}
							},
							function () {
								cfg.ownerWindow.getLoadMask().hide();
							}
						);
					} else if (cfg.TimetableResource_id) {
						// отмена направления на бирку
						cfg.ownerWindow.getLoadMask(langs('Отмена направления')).show();
						submitClearTime({
								id: cfg.TimetableResource_id,
								cancelType: cfg.cancelType,
								type: 'resource',
								EvnStatusCause_id: data.EvnStatusCause_id,
								EvnComment_Comment: data.EvnStatusHistory_Cause
							},
							function (response) {
								cfg.ownerWindow.getLoadMask().hide();
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if (!answer.success) {
										if (answer.Error_Code) {
											Ext.Msg.alert(langs('Ошибка #') + answer.Error_Code, answer.Error_Message);
										} else if (!answer.Error_Msg) {
											Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>освобождение приема невозможно</b>!'));
										}
									} else {
										cfg.callback(cfg);
									}
								} else {
									Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>отсутствует ответ сервера</b>.'));
								}
							},
							function () {
								cfg.ownerWindow.getLoadMask().hide();
							}
						);

					} else if (cfg.TimetableStac_id) {
						// отмена направления на бирку
						cfg.ownerWindow.getLoadMask(langs('Отмена направления')).show();
						submitClearTime({
								id: cfg.TimetableStac_id,
								cancelType: cfg.cancelType,
								type: 'stac',
								EvnStatusCause_id: data.EvnStatusCause_id,
								EvnComment_Comment: data.EvnStatusHistory_Cause
							},
							function (response) {
								cfg.ownerWindow.getLoadMask().hide();
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if (!answer.success) {
										if (answer.Error_Code) {
											Ext.Msg.alert(langs('Ошибка #') + answer.Error_Code, answer.Error_Message);
										} else if (!answer.Error_Msg) {
											Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>освобождение приема невозможно</b>!'));
										}
									} else {
										cfg.callback(cfg);
									}
								} else {
									Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции освобождения времени приема<br/>произошла ошибка: <b>отсутствует ответ сервера</b>.'));
								}
							},
							function () {
								cfg.ownerWindow.getLoadMask().hide();
							}
						);
					} else if (cfg.EvnQueue_id) {
						// отмена направления в очереди
						cfg.ownerWindow.getLoadMask(langs('Отмена направления')).show();
						Ext.Ajax.request({
							url: '/?c=Queue&m=cancelQueueRecord',
							callback: function(options, success, response)  {
								cfg.ownerWindow.getLoadMask().hide();
								if (success) {
									getWnd('swMPQueueSelectFailWindow').hide();
									cfg.callback(cfg);
								} else {
									sw.swMsg.alert(langs('Ошибка'), langs('При отмене направления из очереди произошла ошибка.'));
								}
							},
							params: {
								cancelType: cfg.cancelType,
								EvnQueue_id: cfg.EvnQueue_id,
								EvnStatusCause_id: data.EvnStatusCause_id,
								EvnComment_Comment: data.EvnStatusHistory_Cause
							}
						});
					} else if (cfg.EvnDirection_id) {
						// Отмена направления без бирок и очереди
						cfg.ownerWindow.getLoadMask(langs('Отмена направления')).show();
						Ext.Ajax.request({
							url: 'decline' == cfg.cancelType ? '/?c=EvnDirection&m=reject' : '/?c=EvnDirection&m=cancel',
							callback: function(options, success, response)  {
								cfg.ownerWindow.getLoadMask().hide();
								if (success) {
									cfg.callback(cfg);
								} else {
									sw.swMsg.alert(langs('Ошибка'), langs('При отмене направления из очереди произошла ошибка.'));
								}
							},
							params: {
								EvnDirection_id: cfg.EvnDirection_id,
								DirType_id: cfg.DirType_id || null,
								EvnStatusCause_id: data.EvnStatusCause_id,
								EvnComment_Comment: data.EvnStatusHistory_Cause
							}
						});
					}
				}
			}
		});

		return true;

	},
	/**
	 * Убрать в очередь
	 */
	returnToQueue:  function(cfg) {
		log('returnToQueue', cfg);
		if (!cfg
			|| !cfg.EvnDirection_id
			|| (!cfg.TimetableGraf_id && !cfg.TimetableStac_id && !cfg.TimetableMedService_id && !cfg.TimetableResource_id)
		) {
			return false
		}
		if (!cfg.noask) {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Вы хотите освободить время приема?'),
				title: langs('Вопрос'),
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId) {
						if (cfg.loadMask) cfg.loadMask.show();
						Ext.Ajax.request({
							url: '/?c=EvnDirection&m=returnToQueue',
							callback: function (options, success, response) {
								if (cfg.loadMask) cfg.loadMask.hide();
								if (success && cfg.callback) {
									if (response.responseText) {
										var answer = Ext.util.JSON.decode(response.responseText);
										cfg.callback(answer);
									} else {
										cfg.callback(response);
									}
								}
							},
							params: {
								EvnDirection_id: cfg.EvnDirection_id
							}
						});
					}
				}
			});
		} else {
			if (cfg.loadMask) cfg.loadMask.show();
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=returnToQueue',
				callback: function (options, success, response) {
					if (cfg.loadMask) cfg.loadMask.hide();
					if (success && cfg.callback) {
						if (response.responseText) {
							var answer = Ext.util.JSON.decode(response.responseText);
							cfg.callback(answer);
						} else {
							cfg.callback(response);
						}
					}
				},
				params: {
					EvnDirection_id: cfg.EvnDirection_id
				}
			});
		}
		return true;
	},
	/**
	 * Отмена блокировки бирки.
	 */
	unlockTime: function(win, object, time_id, callback) {
		if (!time_id) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указана бирка для снятия блокировки времени записи'));
			return false;
		}
		win.getLoadMask(langs('Отмена блокировки бирки...')).show();
		var params = {};
		params[object + '_id'] = time_id;
		Ext.Ajax.request({
			url: '/?c=Timetable&m=unlock',
			params: params,
			callback: function(o, s, r) {
				win.getLoadMask().hide();
				if (s) {
					var response_obj = Ext.util.JSON.decode(r.responseText);
					if ( response_obj.success && response_obj.success === true && typeof callback == 'function') {
						callback(time_id);
					}
				}
			}
		});
		return true;
	},
	/**
	 * Блокировка бирки.
	 */
	lockTime: function(win, object, time_id, callback) {
		if (!time_id) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не указана бирка для блокировки времени записи'));
			return false;
		}
		log(win, object, time_id, callback,'lockTime');
		win.getLoadMask(langs('Блокировка бирки...')).show();
		var params = {};
		params[object + '_id'] = time_id;
		Ext.Ajax.request({
			url: '/?c=Timetable&m=lock',
			params: params,
			callback: function(o, s, r) {
				win.getLoadMask().hide();
				if (s) {
					var response_obj = Ext.util.JSON.decode(r.responseText);
					if ( response_obj.success && response_obj.success === true && typeof callback == 'function') {
						callback(time_id);
					}
				}
			}
		});
		return true;
	},
	dirTypeStore: null,
	/** Создание списка типов направлений для выбора
	 *
	 * option.id - id списка типов направлений
	 * option.excList
	 * option.onCreate
	 * option.onSelect
	 * @param {Object} option
	 * @return {Object}
	 */
	createDirTypeMenu: function(option) {
		//log(option);
		var store = this.getDirTypeStore();
		var menu = new Ext.menu.Menu({id: option.id || 'menuDirType'});
		if (!option.excList) {
			option.excList = [24]; // тип Регистратура, только для ЭО
		} else {
			option.excList.push(24); // тип Регистратура, только для ЭО
		};
		var conf = {
			callback: function(){
				store.each(function(record) {
					if (false == record.get('DirType_Code').inlist(option.excList)
					) {
						menu.add({text: record.get('DirType_Code')+'.'+record.get('DirType_Name'), DirType_Code: record.get('DirType_Code'), DirType_id:record.get('DirType_id'), iconCls: 'direction-new16', handler: function() {
							option.onSelect(record);
						}});
					}
				});
				if(typeof option.onCreate == 'function') option.onCreate(menu);
			}
		};
		if(store.getCount() > 0) {
			conf.callback();
		} else {
			store.load(conf);
		}
	},
	/**
	 * Создание пунктов меню для новой ЭМК
	 * @param option
	 */
	createDirTypeMenuItems: function(option) {
		var store = this.getDirTypeStore();
		if (!option.excList) {
			option.excList = [24]; // тип Регистратура, только для ЭО
		} else {
			option.excList.push(24); // тип Регистратура, только для ЭО
		};
		var conf = {
			callback: function() {
				store.each(function(record) {
					if (false == record.get('DirType_Code').inlist(option.excList)
					) {
						option.menu.add({
							text: record.get('DirType_Name'),
							DirType_Code: record.get('DirType_Code'),
							DirType_id: record.get('DirType_id'),
							handler: function() {
								option.onSelect(record);
							}
						});
					}
				});
				if (typeof option.onCreate == 'function') option.onCreate();
			}
		};
		if (store.getCount() > 0) {
			conf.callback();
		} else {
			store.load(conf);
		}
	},
	/**
	 * Создание пунктов меню для новой ЭМК по списку необходимых типов
	 * @param option
	 */
	createDirTypeMenuByList: function(option) {
		var store = this.getDirTypeStore();
		var conf = {
			callback: function() {
				store.each(function(record) {
					if (record.get('DirType_Code').inlist(option.excList)
					) {
						option.menu.add({
							text: record.get('DirType_Name'),
							DirType_Code: record.get('DirType_Code'),
							DirType_id: record.get('DirType_id'),
							handler: function() {
								option.onSelect(record);
							}
						});
					}
				});
				if (typeof option.onCreate == 'function') option.onCreate();
			}
		};
		if (store.getCount() > 0) {
			conf.callback();
		} else {
			store.load(conf);
		}
	},
	createTimeTablePrintMenu: function(option){ //https://redmine.swan.perm.ru/issues/54910
		var store = this.getPrintTypeStore();
		var menu = new Ext.menu.Menu({id: option.id || 'menuPrintType'});
		if (!option.excList) option.excList = [];
		var conf = {
			callback: function(){
				store.each(function(record) {
					if (false == record.get('PrintType_Code').inlist(option.excList)) {
						if (!(record.get('PrintType_Code') == '2' && option.disable_talon))
						{
							menu.add({text: record.get('PrintType_Code')+'.'+record.get('PrintType_Name'), DirType_Code: record.get('PrintType_Code'), DirType_id:record.get('PrintType_id'), iconCls: 'direction-new16', handler: function() {
								option.onSelect(record);
							}});
						}
					}
				});
				if(typeof option.onCreate == 'function') option.onCreate(menu);
			}
		};
		if(store.getCount() > 0) {
			conf.callback();
		} else {
			store.load(conf);
		}
	},

	/**
	 * Определение типа направления по типу назначения
	 */
	defineDirTypeByPrescrType: function(prescr_type_id)
	{
		log('defineDirTypeByPrescrType', prescr_type_id);
		var dirtype_id = 10;
		// В зависимости от типа назначения надо создавать разные типы направлений
		switch(parseInt(prescr_type_id)){
			case 6:
				dirtype_id = 15;
				break;
			case 7:
				dirtype_id = 20;
				break;
			case 13:
				dirtype_id = 11;
				break;
		}
		return dirtype_id;
	},
	getDirTypeStore: function()
	{
		if(!this.dirTypeStore) {
			this.dirTypeStore = new Ext.db.AdapterStore({
				autoLoad: false,
				dbFile: 'Promed.db',
				fields: [
					{name: 'DirType_Name', mapping: 'DirType_Name'},
					{name: 'DirType_Code', mapping: 'DirType_Code'},
					{name: 'DirType_id', mapping: 'DirType_id'}
				],
				key: 'DirType_id',
				sortInfo: {field: 'DirType_Code'},
				tableName: 'DirType'
			});
		}
		return this.dirTypeStore;
	},
	getPrintTypeStore: function() //https://redmine.swan.perm.ru/issues/54910
	{
		if(!this.printTypeStore){
			this.printTypeStore = new Ext.data.SimpleStore({
				data: [
					['1','1',langs('Направление')],
					['2','2',langs('Талон на прием к врачу')]
				],
				editable: false,
				key: 'PrintType_id',
				autoLoad: false,
				fields: [
					{name: 'PrintType_id', type:'int'},
					{name: 'PrintType_Code', type:'int'},
					{name: 'PrintType_Name', type:'string'}
				]
			});
		}
		return this.printTypeStore;
	},
	/**
	 *
	 * @access private
	 * @params {string} mode
	 * @params {string} method
	 * @params {Object} conf Опции
	 * @return {*}
	 */
	checkDirectionParams: function(mode,method,conf)
	{
		log(['checkDirectionParams', mode, method, conf]);
		if ( typeof conf != 'object' || typeof conf.direction != 'object' || (!conf.direction.LpuUnitType_SysNick && !conf.direction.isNotForSystem) || (mode == 'record' && !conf.Timetable_id && !conf.Unscheduled) ) {
			return false;
		}

		if ( !conf.person || !conf.person.Person_id ) {
			// На время выбора человека блокируем бирку для записи
			if ( conf.Timetable_id ) {
				this.lockTime(Ext.getCmp(conf.windowId), this.getTimetableObject(conf.direction), conf.Timetable_id);
			}
			getWnd('swPersonSearchWindow').show({
				onClose: function()
				{
					if ( conf.Timetable_id ) {
						this.unlockTime(Ext.getCmp(conf.windowId), this.getTimetableObject(conf.direction), conf.Timetable_id);
					}
				}.createDelegate(this),
				onSelect: function(pdata)
				{
					if (!pdata.Person_IsDead || pdata.Person_IsDead == 'false') {
						getWnd('swPersonSearchWindow').hide();
						conf.person = pdata;
						this[method](conf);
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('Направление невозможно в связи со смертью пациента'));
					}
				}.createDelegate(this),
				searchMode: 'all'
			});
			return false;
		}
		
		if (!conf.ignoreLpuSectionAgeCheck && conf.person && conf.person.Person_Birthday && conf.direction && conf.direction.LpuSectionAge_id ) {
			var age = swGetPersonAge(conf.person.Person_Birthday, new Date());
			if ((conf.direction.LpuSectionAge_id == 1 && age <= 17) || (conf.direction.LpuSectionAge_id == 2 && age >= 18)) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							conf.ignoreLpuSectionAgeCheck = true;
							this[method](conf);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Возрастная группа отделения не соответствуют возрасту пациента. Продолжить?'),
					title: langs('Вопрос')
				});
				
				return false;
			}
		}

		// чтобы отработала логика, зависимая от АРМа
		this.userMedStaffFact = conf.userMedStaffFact || sw.Promed.MedStaffFactByUser.last;

		if (this.userMedStaffFact.ARMType && this.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms'])) {
			// Необходимо, чтобы пользователям АРМа "Оператор Call-центра" не задавался вопрос о необходимости ЭН
			conf.needDirection = false;
		}
		var isUfa = (getRegionNick() == 'ufa');
		var isPskov = ( getRegionNick() == 'pskov' );
		if ((isUfa || isPskov) && conf.direction.LpuUnitType_SysNick.inlist(['stac', 'dstac', 'pstac']) && conf.direction.Lpu_did == getGlobalOptions().lpu_id && conf.mode != 'jodirection' && Ext.isEmpty(conf.needDirection)) {
			sw.swMsg.show(
				{
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Выписать новое электронное направление?'),
					title: langs('Вопрос'),
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj)
					{
						conf.needDirection = ('yes' == buttonId);

						// если Уфа и не выписывать ЭН то не записываем (refs #10244)
						if (isUfa && !conf.needDirection) {
							return false;
						}
						this[method](conf);
					}.createDelegate(this)
				});
			return false;
		}

		//если не используется форма ЭН, где можно выбрать тип направления и тип направления не указан
		if (!conf.needDirection && !conf.direction.DirType_id) {
			// то автоматом указываем или предлагаем выбрать тип направления
			switch (true) {
				case (conf.direction.PrescriptionType_Code):
					//тип направления: На исследование
					conf.direction.DirType_id = this.defineDirTypeByPrescrType(option.direction.PrescriptionType_Code);
					break;
				case (conf.direction.MedServiceType_SysNick && conf.direction.MedServiceType_SysNick.inlist(['vk','mse']) ):
					// тип направления: Направление на ВК или МСЭ
					conf.direction.DirType_id = 9;
					break;
				case (conf.direction.LpuUnitType_SysNick == 'parka'):
					//тип направления: На исследование
					conf.direction.DirType_id = 10;
					break;
				case (conf.direction.LpuUnitType_SysNick == 'polka'):
					//тип направления: На консультацию
					conf.direction.DirType_id = 3;
					break;
				default:
					// предлагаем выбрать тип направления
					getWnd('swEvnDirectionParamsSelectWindow').show({
						onSelect: function(direction) {
							conf.direction = direction;
							this[method](conf);
						}.createDelegate(this),
						direction: conf.direction
					});
					return false;
					break;
			}
		}

		if ( !conf.userMedStaffFact ) {
			conf.userMedStaffFact = {}
		}

		var params = {
			EvnDirection_IsNeedOper: (conf.direction.EvnDirection_IsNeedOper && 2 == conf.direction.EvnDirection_IsNeedOper) ? 'on' : 'off',
			EvnDirection_IsCito: conf.direction.EvnDirection_IsCito || 1, //
			EvnDirection_IsAuto: conf.direction.EvnDirection_IsAuto || 1, // автоматическое направление
			EvnDirection_IsReceive: conf.direction.EvnDirection_IsReceive || 1, //  с признаком “К себе”
			withDirection: conf.direction.withDirection || null, // с электронным направлением
			redirectEvnDirection: conf.direction.redirectEvnDirection || null, // признак перенаправления
			ext6: conf.ext6 || false,
			ignoreCanRecord: conf.direction.ignoreCanRecord || null,
			Server_id: conf.person.Server_id,
			Person_id: conf.person.Person_id,
			PersonEvn_id: conf.person.PersonEvn_id,
			PayType_id: conf.direction.PayType_id,
			LpuUnitType_SysNick: conf.direction.LpuUnitType_SysNick,//LpuUnitType_did
			EvnStatus_id: conf.direction.EvnStatus_id || null,//
			Post_id: conf.direction.Post_id || null,//
			Lpu_id: conf.direction.Lpu_id || null,//
			Lpu_sid: conf.direction.Lpu_sid || null,//
			Org_sid: conf.direction.Org_sid || null,//
			MedPersonal_zid: conf.direction.MedPersonal_zid || null,//
			RemoteConsultCause_id: conf.direction.RemoteConsultCause_id || null,//
			EvnDirection_Descr: conf.direction.EvnDirection_Descr || null,//
			TimetableGraf_id: conf.direction.TimetableGraf_id || null,//
			TimetableType_id: conf.TimetableType_id || null,// Тип бирки - нужен для групповой бирки (14)
			TimetableStac_id: conf.direction.TimetableStac_id || null,//
			TimetableMedService_id: conf.direction.TimetableMedService_id || null,//
			TimetableResource_id: conf.direction.TimetableResource_id || null,//
			EvnQueue_id: conf.direction.EvnQueue_id || null,//
			QueueFailCause_id: conf.direction.QueueFailCause_id || null,//
			Lpu_did: conf.direction.Lpu_did,//ид МО куда направляем
			PrehospDirect_id: conf.direction.PrehospDirect_id || null,//кем направлен
			LpuUnit_did: conf.direction.LpuUnit_did || null,//
			LpuSection_did: conf.direction.LpuSection_did || null,//Отделение, куда направили
			LpuSection_id: conf.direction.LpuSection_id || null,//Отделение, которое направило
			EvnUsluga_id: conf.direction.EvnUsluga_id || null,//Сохраненный заказ
			EvnDirection_id: conf.direction.EvnDirection_id ||null,
			EvnDirection_setDate: conf.direction.EvnDirection_setDate ||null,
			EvnDirection_desDT: conf.direction.EvnDirection_desDT ||null,
			EvnDirection_Num: conf.direction.EvnDirection_Num ||null,
			EvnDirection_pid: conf.direction.EvnDirection_pid || null,//ид посещения или движения, в рамках которого направляем
			Evn_id: conf.direction.EvnDirection_pid || null,//ид посещения или движения, в рамках которого направляем
			EvnPrescr_id: conf.direction.EvnPrescr_id || null,//ид назначения по которому направляем
			PrescriptionType_Code: conf.direction.PrescriptionType_Code || null,//код типа назначения по которому направляем
			DirType_id: conf.direction.DirType_id,//ид типа направления
			LpuSectionProfile_id: conf.direction.LpuSectionProfile_id || conf.direction.LpuSectionProfile_did || null,//ид профиля направления
			LpuSectionLpuSectionProfileList: conf.direction.LpuSectionLpuSectionProfileList || null,//список профилей отделения
			Diag_id: conf.direction.Diag_id || null,//ид диагноза посещения или движения, в рамках которого направляем
			Resource_id: conf.direction.Resource_id || null,
			MedService_id: conf.direction.MedService_id || null,//MedService_did
			MedService_did: conf.direction.MedService_did || null,
			MedService_pzid: conf.direction.MedService_pzid || null,
			MedServiceType_SysNick: conf.direction.MedServiceType_SysNick,//MedService_did
			From_MedStaffFact_id: conf.direction.From_MedStaffFact_id || '0',
			MedStaffFact_id: conf.direction.MedStaffFact_id || null,
			MedStaffFact_did: conf.direction.MedStaffFact_id || null,
			MedPersonal_id: conf.direction.MedPersonal_id || null, //ид медперсонала, который направляет
			MedPersonal_did: conf.direction.MedPersonal_did || null, //ид медперсонала, куда направили
			ARMType_id: conf.userMedStaffFact.ARMType_id || conf.direction.ARMType_id || null, //ид типа АРМа из которого произодится запись
			type: conf.direction.type || null, // тип, откуда создаётся направление LpuReg для регистратора
			time: conf.direction.time || null, // на какое время бирка, нужно чисто для отображения в форме
			DopDispInfoConsent_id: conf.direction.DopDispInfoConsent_id || null, //согласие
			StudyTarget_id: (conf.order && conf.order.StudyTarget_id) ? conf.order.StudyTarget_id : null, //цель
			isNotForSystem: conf.direction.isNotForSystem || null, //Направление в другую МО
			ZNOinfo: conf.direction.ZNOinfo || null,
			UslugaComplex_did: conf.direction.UslugaComplex_did || null,
			TreatmentClass_id: conf.direction.TreatmentClass_id || null,
			MedSpecOms: conf.direction.MedSpecOms || null
		};

		return params;
	},
	/**
	 * Запись человека
	 * @access public
	 * @params {Object} conf Опции:
	 *     Timetable_id (int) бирка (обязательный параметр)
	 *     direction (Object) параметры направления (обязательный параметр)
	 *     person (Object) параметры человека
	 *     loadMask (boolean) отображать маску во время запроса к серверу
	 *     windowId (string) идентификатор окна, на которое накладывается маска (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после сохранения данных направления (обязательный параметр)
	 *     Unscheduled (boolean) признак Незапланированный прием человека (Автоматически добавляется дополнительная бирка текущим временем. На нее сразу происходит запись человека)
	 *	  onSaveRecord {Function} Функция, вызываемая после записи на бирку
	 *     onHide {Function} Функция, вызываемая после закрытия формы эл.направления
	 *     needDirection (boolean) признак необходимости эл.направления
	 *	  fromEmk (boolean) признак направления из ЭМК
	 *	  mode (string) особый режим направления (например, по назначению)
	 */
	recordPerson: function(conf)
	{
		if (isUserGroup('PM')) {
			return false; // запись запрещена
		}
		if ('ufa' == getRegionNick()
			&& getGlobalOptions().groups
			&& getGlobalOptions().groups.toString().indexOf('OperatorCallCenter') >= 0
			&& getGlobalOptions().groups.toString().indexOf('CallCenterAdmin') == -1
			&& conf.direction
			&& conf.direction.ARMType_id
			&& 24 == conf.direction.ARMType_id
		) {
			// Период возможной записи из АРМ оператора call-центра определяется настройками указанными в АРМ Администратора ЦОД/Система/Параметры системы/Электронная регистратура
			var rec_date = null;
			if (conf.date) {
				rec_date = Date.parseDate(conf.date, 'd.m.Y');
			} else if (conf.direction.time) {
				rec_date = Date.parseDate(conf.direction.time, 'd.m.Y H:i');
				rec_date = rec_date.clearTime ? rec_date.clearTime(false) : null;
			}
			if (!rec_date) {
				return false;
			}
			var limit_date = null;
			var curdate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			var one_day_time = 24*60*60*1000;
			switch (this.getTimetableObject(conf.direction)) {
				case 'TimetableGraf':
					if (getGlobalOptions().pol_record_day_count && getGlobalOptions().pol_record_day_count > 0) {
						//limit_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y').add(Date.DAY, getGlobalOptions().pol_record_day_count);
						limit_date = new Date((getGlobalOptions().pol_record_day_count - 1) * one_day_time + curdate.getTime());
					}
					break;
				case 'TimetableStac':
					if (getGlobalOptions().stac_record_day_count && getGlobalOptions().stac_record_day_count > 0) {
						//limit_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y').add(Date.DAY, getGlobalOptions().stac_record_day_count);
						limit_date = new Date((getGlobalOptions().stac_record_day_count - 1) * one_day_time + curdate.getTime());
					}
					break;
				default:
					if (getGlobalOptions().medservice_record_day_count && getGlobalOptions().medservice_record_day_count > 0) {
						//limit_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y').add(Date.DAY, getGlobalOptions().medservice_record_day_count);
						limit_date = new Date((getGlobalOptions().medservice_record_day_count -1) * one_day_time + curdate.getTime());
					}
					break;
			}
			if (limit_date && limit_date < rec_date) {
				return false;
			}
		}

		var directionParams = this.checkDirectionParams('record', 'recordPerson', conf);
		if(directionParams == false) {
			return false;
		}

		conf.onSaveRecord = conf.onSaveRecord || Ext.emptyFn;

		var that = this;
		if ( conf.direction.MedServiceType_SysNick && conf.direction.MedServiceType_SysNick.inlist(['vk','mse','HTM']) ) {
			// На время ввода данных направления блокируем бирку для записи
			if (conf.Timetable_id) {
				that.lockTime(Ext.getCmp(conf.windowId), 'TimetableMedService', conf.Timetable_id);
			}
			var ms_params = {
				Person_id: conf.person.Person_id,
				PersonEvn_id: conf.person.PersonEvn_id,
				Server_id: conf.person.Server_id,
				TimetableMedService_id: conf.Timetable_id,
				MedService_id: conf.direction.MedService_id,
				onClose: function (win) {
					if (conf.Timetable_id && (!win||!win.dataSaved)) {
						that.unlockTime(Ext.getCmp(conf.windowId), 'TimetableMedService', conf.Timetable_id);
					}
					if ( conf.onHide ) {
						conf.onHide.apply(that, arguments);
					}
				},
				onSave: function(par) {
					directionParams.Diag_id = par.Diag_id;
					directionParams.TimetableMedService_id = conf.Timetable_id;
					directionParams.Evn_id = conf.direction.EvnDirection_pid;
					directionParams.LpuUnitType_SysNick = 'parka'; // костыль
					directionParams.DirType_id = 9; // костыль
					directionParams.LpuSectionProfile_id = conf.direction.LpuSectionProfile_id || '0'; // костыль
					directionParams.EvnDirection_IsAuto = 2;// костыль
					directionParams.EvnDirection_setDate = getGlobalOptions().date;// костыль
					directionParams.EvnDirection_Num = '0';// костыль
					directionParams.MedPersonal_zid = '0';// костыль
					directionParams.order = '';// костыль
					directionParams.EvnPrescrVK_id = par.EvnPrescrVK_id;
					that.requestRecord({
						url: C_TTMS_APPLY,
						loadMask: conf.loadMask,
						win: conf.win,
						windowId: conf.windowId,
						params: directionParams,
						date: conf.date || null,
						Timetable_id: conf.Timetable_id,
						fromEmk: conf.fromEmk || false,
						mode: conf.mode || '',
						needDirection: conf.needDirection,
						Unscheduled: conf.Unscheduled,
						onHide: conf.onHide,
						onSaveRecord: conf.onSaveRecord,
						callback: conf.callback
					});
				}
			};
			switch(conf.direction.MedServiceType_SysNick) {
				case 'vk':
					if (conf.direction.EvnPrescrVKData) {
						ms_params.onSave(conf.direction.EvnPrescrVKData);
					} else {
						ms_params.Diag_id = conf.direction.Diag_id;
						ms_params.EvnDirection_pid = conf.direction.EvnDirection_pid;
						getWnd('swEvnPrescrVKWindow').show(ms_params);
					}
					break;
				case 'mse':
					ms_params.EvnVK_id = conf.direction.EvnVK_id;
					ms_params.EvnPL_id = conf.direction.EvnDirection_pid;
					ms_params.withCreateDirection = 1;
					ms_params.LpuSectionProfile_id = conf.direction.LpuSectionProfile_id;
					ms_params.onHide = Ext.emptyFn;
					ms_params.onSave = conf.callback;
					getWnd('swDirectionOnMseEditForm').show(ms_params);
					break;
				case 'HTM':
					ms_params.EvnDirectionHTM_pid = conf.direction.EvnDirectionHTM_pid;
					ms_params.EvnVK_setDT = conf.direction.EvnVK_setDT;
					ms_params.EvnDirectionHTM_VKProtocolNum = conf.direction.EvnDirectionHTM_VKProtocolNum;
					ms_params.EvnDirectionHTM_VKProtocolDate = conf.direction.EvnVK_setDT;
					ms_params.Lpu_sid = conf.direction.Lpu_id;
					ms_params.Lpu_id = conf.direction.Lpu_id;
					ms_params.Lpu_f003mcod = conf.direction.Lpu_f003mcod;
					ms_params.LpuSection_id = conf.direction.LpuSection_id;
					ms_params.Lpu_did = conf.direction.Lpu_did;
					ms_params.LpuSection_did = conf.direction.LpuSection_uid;
					ms_params.MedService_did = conf.direction.MedService_id;
					ms_params.LpuSectionProfile_id = conf.direction.LpuSectionProfile_id;
					ms_params.LpuUnit_did = conf.direction.LpuUnit_did;
					ms_params.Diag_id = conf.direction.Diag_id;
					ms_params.withCreateDirection = (typeof conf.direction.withCreateDirection !== undefined? conf.direction.withCreateDirection: 1);
					ms_params.onHide = Ext.emptyFn;
					ms_params.onSave = conf.callback;
					ms_params.EvnDirection_pid = conf.direction.EvnDirection_pid;
					getWnd('swDirectionOnHTMEditForm').show(ms_params);
					break;
			}
		} else {
			var time_table = this.getTimetableObject(conf.direction);
			directionParams[time_table+'_id'] = conf.Timetable_id;
			this.createDirection({
				conf: conf,
				params: directionParams,
				fromEmk: conf.fromEmk || false,
				mode: conf.mode || '',
				context: 'beforeProcessQueue',
				needDirection: conf.needDirection,
				onHide: function () {
					if (conf.Timetable_id) {
						this.unlockTime(Ext.getCmp(conf.windowId), this.getTimetableObject(conf.direction), conf.Timetable_id);
					}
					if ( conf.onHide ) {
						conf.onHide.apply(this, arguments);
					}
				}.createDelegate(this),
				onNeedDirection: function(evnDirectionParams) {
					if (conf.Timetable_id) {
						this.lockTime(Ext.getCmp(conf.windowId), this.getTimetableObject(conf.direction), conf.Timetable_id);
					}
				}.createDelegate(this),
				callback: function(data){
					// todo: Следующее условие нужно убрать
					if(data && data.evnDirectionData && data.evnDirectionData.EvnDirection_id){
						directionParams.EvnDirection_id = data.evnDirectionData.EvnDirection_id;
					}
					if (data && data.evnDirectionData) {
						directionParams = Ext.apply(directionParams, data.evnDirectionData);
					}
					directionParams.LpuUnitType_SysNick = conf.direction.LpuUnitType_SysNick;
					// передаем и параметры заказа, если он был
					directionParams['order'] = (conf.order)?Ext.util.JSON.encode(conf.order):'{}';

					var url = C_TTG_APPLY;
					if (time_table == 'TimetableResource') {
						url = C_TTR_APPLY;
					} else if (directionParams.LpuUnitType_SysNick.inlist(['stac', 'dstac', 'pstac'])) {
						// Для стационаров свой контроллер и метод для записи
						url = C_TTS_APPLY;
					} else if (directionParams.LpuUnitType_SysNick == 'parka') {
						// Для служб в параклинике свой
						url = C_TTMS_APPLY;
						if (conf.direction.type == 'LpuReg' && conf.direction.MedService_id && !conf.order) {
							// При направлении из АРМ регистратора на службы создаем заказ после заполнения формы направления
							var ms_rec = new Ext.data.Record({
								MedService_id: conf.direction.MedService_id,
								MedServiceType_SysNick: conf.direction.MedServiceType_SysNick,
								MedService_Nick: conf.direction.MedService_Nick,
								LpuSectionProfile_id: conf.direction.LpuSectionProfile_id,
								Lpu_id: conf.direction.Lpu_did,
								LpuSection_id: conf.direction.LpuSection_did,
								UslugaComplexMedService_id: null,
								UslugaComplex_Name: null,
								UslugaComplex_id: null
							});
							var thas = this;
							this.createOrder(ms_rec, conf.person, function(o, f, data) {
								ms_rec.set('UslugaComplex_Name', data.UslugaComplex_Name);
								ms_rec.set('UslugaComplex_id', data.UslugaComplex_id);
								thas.createOrder(ms_rec, conf.person, function(o, f, data) {
									data.TimetableMedService_id = conf.Timetable_id;
									directionParams['order'] = Ext.util.JSON.encode(data);
									thas.requestRecord({
										url: url,
										loadMask: conf.loadMask,
										win: conf.win,
										windowId: conf.windowId,
										params: directionParams,
										date: conf.date || null,
										Timetable_id: conf.Timetable_id,
										fromEmk: conf.fromEmk || false,
										mode: conf.mode || '',
										needDirection: conf.needDirection,
										Unscheduled: conf.Unscheduled,
										onHide: conf.onHide,
										onSaveRecord: conf.onSaveRecord,
										callback: conf.callback
									});
								}, false, null, null, data.Usluga_isCito);
							}, true, null, null, null);
							return true;
						}
					}
					//log([conf, directionParams, url]);
					this.requestRecord({
						url: url,
						loadMask: conf.loadMask,
						win: conf.win,
						windowId: conf.windowId,
						params: directionParams,
						date: conf.date || null,
						Timetable_id: conf.Timetable_id,
						fromEmk: conf.fromEmk || false,
						mode: conf.mode || '',
						needDirection: conf.needDirection,
						Unscheduled: conf.Unscheduled,
						onHide: conf.onHide,
						onSaveRecord: conf.onSaveRecord,
						callback: conf.callback
					});
					return true;
				}.createDelegate(this)
			});

		}
	},
	/**
	 * Постановка человека в очередь
	 * @access public
	 * @params {Object} conf Опции:
	 *     direction (Object) параметры направления (обязательный параметр)
	 *     person (Object) параметры человека
	 *     loadMask (boolean) отображать маску во время запроса к серверу
	 *     windowId (string) идентификатор окна, на которое накладывается маска (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после сохранения данных направления (обязательный параметр)
	 *     onHide {Function} Функция, вызываемая после закрытия формы эл.направления
	 *     needDirection (boolean) признак необходимости эл.направления
	 *	  fromEmk (boolean) признак направления из ЭМК
	 *	  mode (string) особый режим направления (например, по назначению)
	 */
	queuePerson: function(conf)
	{
		log('queuePerson',conf);
		if (isUserGroup('PM')) {
			return false; // запись запрещена
		}
		var directionParams = this.checkDirectionParams('queue', 'queuePerson', conf);
		if(directionParams == false) {
			return false;
		}
		log('queuePerson.directionParams',directionParams);
		directionParams.EvnQueue_pid = directionParams.EvnDirection_pid;
		directionParams.LpuSectionProfile_did = directionParams.LpuSectionProfile_id;
		// направление при записи будет сохраняться всегда
		//directionParams.addDirection = (conf.mode=='nosave')?1:0; // признак, что направление нужно сохранять при записи
		/*
		 Закрыл ветку по параклинике, поскольку к моменту постановки в очередь заказ уже должен быть выполнен
		 if(conf.direction.LpuUnitType_SysNick == 'parka') {
		 // для парки сначала надо заказать услугу, затем создать направление, а потом ставить в очередь
		 this.openUslugaOrderEditWindow({
		 action: 'add',
		 direction: conf.direction,
		 person: conf.person,
		 callback: function(owner, id, records, is_add) {
		 directionParams.EvnUsluga_id = id;
		 //создание системного направления
		 this.saveSysEvnDirection({
		 params: directionParams,
		 callback: function(data){
		 if(data && data.evnDirectionData && data.evnDirectionData.EvnDirection_id){
		 directionParams.EvnDirection_id = data.evnDirectionData.EvnDirection_id;
		 }
		 directionParams.EvnUslugaPar_id = id;
		 directionParams.UslugaComplex_did = records['UslugaComplex_id'];
		 this.requestQueue({
		 params: directionParams,
		 loadMask: conf.loadMask,
		 windowId: conf.windowId,
		 callback: conf.callback
		 });
		 }.createDelegate(this)
		 });
		 }.createDelegate(this)
		 });
		 } else */
		var that = this;
		if (conf.direction.MedServiceType_SysNick && conf.direction.MedServiceType_SysNick == 'mse') {
			var ms_params = {
				Person_id: conf.person.Person_id,
				PersonEvn_id: conf.person.PersonEvn_id,
				Server_id: conf.person.Server_id,
				TimetableMedService_id: null,
				MedService_id: conf.direction.MedService_id,
				onSave: function(par) {
					//создание системного направления
					that.saveSysEvnDirection({
						params: directionParams,
						vkPar:par,
						toQueue:1,
						callback: function(data){
							if(data && data.evnDirectionData && data.evnDirectionData.EvnDirection_id){
								directionParams.EvnDirection_id = data.evnDirectionData.EvnDirection_id;
							}
							directionParams.LpuUnitType_SysNick = 'parka'; // костылища
							directionParams.LpuSection_did = conf.direction.LpuSection_did;
							directionParams.MedService_did = conf.direction.MedService_id;
							directionParams.LpuSectionProfile_did = conf.direction.LpuSectionProfile_id;
							directionParams.LpuUnit_did = conf.direction.LpuUnit_did;
							directionParams.EvnPrescrVK_id = par.EvnPrescrVK_id;
							that.requestQueue({
								params: directionParams,
								loadMask: conf.loadMask,
								windowId: conf.windowId,
								win: conf.win,
								callback: conf.callback
							});
						}
					});
				}
			};
			ms_params.EvnVK_id = conf.direction.EvnVK_id;
			ms_params.EvnPL_id = conf.direction.EvnDirection_pid;
			ms_params.withCreateDirection = 2;
			ms_params.LpuSectionProfile_id = conf.direction.LpuSectionProfile_id;
			ms_params.onHide = Ext.emptyFn;
			ms_params.onSave = conf.callback;
			getWnd('swDirectionOnMseEditForm').show(ms_params);
		}
		else if (conf.direction.MedServiceType_SysNick && conf.direction.MedServiceType_SysNick.inlist(['vk','mse','HTM'])) {
			var saveFunction = function() {
				var ms_params = {
					Person_id: conf.person.Person_id,
					PersonEvn_id: conf.person.PersonEvn_id,
					Server_id: conf.person.Server_id,
					TimetableMedService_id: null,
					MedService_id: conf.direction.MedService_id,
					onSave: function (par) {
						//создание системного направления
						that.saveSysEvnDirection({
							params: directionParams,
							vkPar: par,
							toQueue: 1,
							callback: function (data) {
								if (data && data.evnDirectionData && data.evnDirectionData.EvnDirection_id) {
									directionParams.EvnDirection_id = data.evnDirectionData.EvnDirection_id;
								}
								directionParams.LpuUnitType_SysNick = 'parka'; // костылища
								directionParams.LpuSection_did = conf.direction.LpuSection_did;
								directionParams.MedService_did = conf.direction.MedService_id;
								directionParams.LpuSectionProfile_did = conf.direction.LpuSectionProfile_id;
								directionParams.LpuUnit_did = conf.direction.LpuUnit_did;
								directionParams.EvnPrescrVK_id = par.EvnPrescrVK_id;
								that.requestQueue({
									params: directionParams,
									loadMask: conf.loadMask,
									windowId: conf.windowId,
									win: conf.win,
									callback: conf.callback
								});
							}
						});
					}
				};
				switch (conf.direction.MedServiceType_SysNick) {
					case 'vk':
						if (conf.direction.EvnPrescrVKData) {
							ms_params.onSave(conf.direction.EvnPrescrVKData);
						} else {
							ms_params.Diag_id = conf.direction.Diag_id;
							ms_params.EvnDirection_pid = conf.direction.EvnDirection_pid;
							getWnd('swEvnPrescrVKWindow').show(ms_params);
						}
						break;
					case 'mse':
						ms_params.EvnVK_id = conf.direction.EvnVK_id;
						ms_params.EvnPL_id = conf.direction.EvnDirection_pid;
						ms_params.withCreateDirection = 2;
						ms_params.LpuSectionProfile_id = conf.direction.LpuSectionProfile_id;
						ms_params.onHide = Ext.emptyFn;
						ms_params.onSave = conf.callback;
						getWnd('swDirectionOnMseEditForm').show(ms_params);
						break;
					case 'HTM':
						ms_params.EvnDirectionHTM_pid = conf.direction.EvnDirectionHTM_pid;
						ms_params.EvnVK_setDT = conf.direction.EvnVK_setDT;
						ms_params.EvnDirectionHTM_VKProtocolNum = conf.direction.EvnDirectionHTM_VKProtocolNum;
						ms_params.EvnDirectionHTM_VKProtocolDate = conf.direction.EvnVK_setDT;
						ms_params.Lpu_sid = conf.direction.Lpu_id;
						ms_params.Lpu_f003mcod = conf.direction.Lpu_f003mcod;
						ms_params.LpuSection_id = conf.direction.LpuSection_id;
						ms_params.Lpu_did = conf.direction.Lpu_did;
						ms_params.LpuSection_did = conf.direction.LpuSection_uid;
						ms_params.MedService_did = conf.direction.MedService_id;
						ms_params.LpuSectionProfile_id = conf.direction.LpuSectionProfile_id;
						ms_params.LpuUnit_did = conf.direction.LpuUnit_did;
						ms_params.Diag_id = conf.direction.Diag_id;
						ms_params.EvnDirection_pid = conf.direction.EvnDirection_pid;
						ms_params.withCreateDirection = (typeof conf.direction.withCreateDirection !== undefined? conf.direction.withCreateDirection: 2);
						ms_params.onHide = Ext.emptyFn;
						ms_params.onSave = conf.callback;
						getWnd('swDirectionOnHTMEditForm').show(ms_params);
						break;
				}
			};
			if (conf.direction.MedService_id) {
				getGlobalLoadMask('Проверка возможности постановки в очередь...').show();
				Ext.Ajax.request({
					url: '/?c=Queue&m=checkRecordQueue',
					params: {
						MedService_id: conf.direction.MedService_id
					},
					callback: function (opt, success, response) {
						getGlobalLoadMask().hide();
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success) {
								saveFunction();
							}
						}
					}
				});
			} else {
				saveFunction();
			}
		} else {
			// TODO: Здесь надо понять в каком месте будет создаваться системное направление для параклиники
			// для стаца и полки сначала надо создать направление, а потом ставить в очередь
			this.createDirection({
				params: directionParams,
				fromEmk: conf.fromEmk || false,
				ext6: conf.ext6 || false,
				mode: conf.mode || '',
				context: 'beforeProcessQueue',
				needDirection: conf.needDirection,
				onHide: conf.onHide,
				callback: function(data){
					// todo: Следующее условие нужно убрать
					if(data && data.evnDirectionData && data.evnDirectionData.EvnDirection_id){
						directionParams.EvnDirection_id = data.evnDirectionData.EvnDirection_id;
					}
					if (data && data.evnDirectionData) {
						directionParams = Ext.apply(directionParams, data.evnDirectionData);
					}
					if (directionParams.MedPersonal_id && !directionParams.MedPersonal_did) {
						directionParams.MedPersonal_did = directionParams.MedPersonal_id;
						delete directionParams.MedPersonal_id;
					}
					if (directionParams.MedStaffFact_id && !directionParams.MedStaffFact_did) {
						directionParams.MedStaffFact_did = directionParams.MedStaffFact_id;
						delete directionParams.MedStaffFact_id;
					}
					// передаем и параметры заказа, если он был
					directionParams['order'] = (conf.order)?Ext.util.JSON.encode(conf.order):'{}';

					if (conf.direction.type == 'LpuReg'
						&& conf.direction.MedService_id
						&& conf.direction.LpuUnitType_SysNick == 'parka'
						&& !conf.order
					) {
						// При направлении из АРМ регистратора на службы создаем заказ после заполнения формы направления
						var ms_rec = new Ext.data.Record({
							MedService_id: conf.direction.MedService_id,
							MedServiceType_SysNick: conf.direction.MedServiceType_SysNick,
							MedService_Nick: conf.direction.MedService_Nick,
							LpuSectionProfile_id: conf.direction.LpuSectionProfile_id,
							Lpu_id: conf.direction.Lpu_did,
							LpuSection_id: conf.direction.LpuSection_did,
							UslugaComplexMedService_id: null,
							UslugaComplex_Name: null,
							UslugaComplex_id: null
						});
						var thas = this;
						this.createOrder(ms_rec, conf.person, function(o, f, data) {
							ms_rec.set('UslugaComplex_Name', data.UslugaComplex_Name);
							ms_rec.set('UslugaComplex_id', data.UslugaComplex_id);
							thas.createOrder(ms_rec, conf.person, function(o, f, data) {
								directionParams['order'] = Ext.util.JSON.encode(data);
								thas.requestQueue({
									params: directionParams,
									loadMask: conf.loadMask,
									windowId: conf.windowId,
									win: conf.win,
									callback: conf.callback
								});
							}, false, null, null, data.Usluga_isCito);
						}, true, null, null, null);
						return true;
					}
					this.requestQueue({
						params: directionParams,
						loadMask: conf.loadMask,
						windowId: conf.windowId,
						win: conf.win,
						callback: conf.callback
					});
					return true;
				}.createDelegate(this)
			});
		}
	},
	/**
	 * Запрос для записи на бирку из очереди
	 * @params {Object} conf Опции:
	 *     queue (Object) параметры направления в очереди (обязательный параметр)
	 *     url (string) url (обязательный параметр)
	 *     loadMask (boolean) отображать маску во время запроса к серверу
	 *     windowId (string) идентификатор окна, на которое накладывается маска (обязательный параметр)
	 *     params (Object) параметры направления (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после сохранения данных направления
	 *     onSaveRecord {Function} Функция, вызываемая после записи на бирку
	 *     onHide {Function} Функция, вызываемая после закрытия формы эл.направления
	 *     needDirection (boolean) признак необходимости эл.направления
	 *     Unscheduled (boolean) признак Незапланированный прием человека (Автоматически добавляется дополнительная бирка текущим временем. На нее сразу происходит запись человека)
	 *     Timetable_id (int) ид бирки
	 *     fromEmk (boolean) признак направления из ЭМК
	 *     mode (string) особый режим направления (например, по назначению)
	 */
	recordFromQueue: function(conf)
	{
		log('recordFromQueue', conf);
		var obj = this;
		if (!conf.queue || !conf.queue['EvnQueue_id']) {
			// без этого параметра не будет вызвана хранимка p_EvnDirection_recordFromQueue
			Ext.Msg.alert(langs('Ошибка'), langs('Не передан объект постановки в очередь.'));
			return false;
		}
		if (!conf.params || !conf.params.TimetableGraf_id && !conf.params.TimetableStac_id && !conf.params.TimetableMedService_id && !conf.params.TimetableResource_id) {
			Ext.Msg.alert(langs('Ошибка'), langs('Не передана бирка расписания.'));
			return false;
		}
		/*if (conf.queue.EvnStatus_SysNick && 'Queued' != conf.queue.EvnStatus_SysNick) {
			Ext.Msg.alert(langs('Ошибка'), langs('Направление должно иметь статус Поставлено в очередь.'));
			return false;
		}*/
		// приходится дергать параметры, которые вовсе ненужны для p_EvnDirection_recordFromQueue
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=loadEvnDirectionEditForm',
			callback: function(options, success, response)  {
				if (success) {
					var result  = Ext.util.JSON.decode(response.responseText);
					result[0].EvnPrescr_id = conf.params.EvnPrescr_id || null;
					result[0].TimetableGraf_id = conf.params.TimetableGraf_id || null;
					result[0].TimetableStac_id = conf.params.TimetableStac_id || null;
					result[0].TimetableMedService_id = conf.params.TimetableMedService_id || null;
					result[0].TimetableResource_id = conf.params.TimetableResource_id || null;
					result[0].MedService_id = conf.params.MedService_id || null;
					result[0].Resource_id = conf.params.Resource_id || null;
					result[0].From_MedStaffFact_id = conf.params.From_MedStaffFact_id || null;
					conf.params = result[0];

					conf.params['redirectEvnDirection'] = 600; // говорим, что надо записать на бирку из очереди
					conf.params['AnswerQueue'] = 0; // игнорируем контроль нахождения в очереди Timetable_model::checkQueueExists
					conf.params['EvnQueue_id'] = conf.queue.EvnQueue_id;
					conf.params['EvnDirection_id'] = conf.queue.EvnDirection_id;
					conf.Unscheduled = false;
					obj.requestRecord(conf);
				} else
					sw.swMsg.alert(langs('Ошибка'), langs('Произошла ошибка.'));
			},
			params: {
				EvnDirection_id: conf.queue.EvnDirection_id
			}
		});
		return true;
	},
	/**
	 * Запрос для записи на бирку
	 * @access private
	 * @params {Object} conf Опции:
	 *     url (string) url (обязательный параметр)
	 *     loadMask (boolean) отображать маску во время запроса к серверу
	 *     windowId (string) идентификатор окна, на которое накладывается маска (обязательный параметр)
	 *     params (Object) параметры направления (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после сохранения данных направления
	 *	  onSaveRecord {Function} Функция, вызываемая после записи на бирку
	 *     onHide {Function} Функция, вызываемая после закрытия формы эл.направления
	 *     needDirection (boolean) признак необходимости эл.направления
	 *     Unscheduled (boolean) признак Незапланированный прием человека (Автоматически добавляется дополнительная бирка текущим временем. На нее сразу происходит запись человека)
	 *	  Timetable_id (int) ид бирки
	 *	  fromEmk (boolean) признак направления из ЭМК
	 *	  mode (string) особый режим направления (например, по назначению)
	 */
	requestRecord: function(conf)
	{
		log('requestRecord', conf);
		var isUfa = (getRegionNick() == 'ufa');
		var isPskov = (getRegionNick() == 'pskov');
		var isEkb = (getRegionNick() == 'ekb');
		var isBelarus = (getRegionNick() == 'by');
		//Если в диагнозе стоит Z03.1 для Уфы
		var ZNO = conf.params.ZNOinfo;
		if ( typeof conf != 'object' || typeof conf.params != 'object' || !conf.url  || !conf.windowId ) {
			return false;
		}
		conf.onSaveRecord = conf.onSaveRecord || Ext.emptyFn;
		if ( conf.loadMask ) {
			if (getRegionNick() == 'kz') {
				var loadMask = new Ext.LoadMask(Ext.getBody(), {msg: langs('Подождите, пациент записывается ...')});
			} else if (conf.win) {
				var loadMask = conf.win.getLoadMask(langs('Подождите, пациент записывается ...'));
			} else {
				var loadMask = new Ext.LoadMask(Ext.get(conf.windowId), {msg: langs('Подождите, пациент записывается ...')});
			}
			loadMask.show();
		}

		if ( conf.Unscheduled ) {
			conf.params['Unscheduled'] = 'true';
		}
		conf.onFailure = conf.onFailure || function(code) {
			switch (code) {
				case 0: // отсутствует ответ сервера
					if ( typeof conf.callback == 'function' ) {
						conf.callback(false);
					} else {
						Ext.Msg.alert(langs('Ошибка'), langs('При записи на бирку произошла ошибка:<br/><b>отсутствует ответ сервера</b>.'));
					}
					break;
				case 1: // пришел ответ с ошибкой, она уже выведена
					if ( typeof conf.callback == 'function' ) {
						conf.callback(false);
					}
					break;
				case 2: // failure
					Ext.Msg.alert(langs('Ошибка'), langs('При записи на бирку произошла ошибка!'));
					break;
				default: // ничего не делаем
					break
			}
		}.createDelegate(this);

		conf.onCancel = conf.onCancel || Ext.emptyFn;

		//conf.params.addDirection = (conf.mode=='nosave')?0:1; // признак, что направление нужно сохранять при записи

		Ext.Ajax.request({
			withoutErrorMsgBox: conf.withoutErrorMsgBox || false,
			url: conf.url,
			params: conf.params,
			failure: function(response, options)
			{
				if ( conf.loadMask ) {
					loadMask.hide();
				}
				var resp = response;
				if(response && response.responseText)
					resp = Ext.util.JSON.decode(response.responseText);
				conf.onFailure(2,resp);
			},
			success: function(response, action)
			{
				if ( conf.loadMask ) {
					loadMask.hide();
				}
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if(answer.success) {
						// вызываем функцию после записи на бирку
						if (isUfa && ZNO == true/* && conf.params.EvnDirection_id && conf.params.EvnDirection_id > 0*/) {
							if (conf.params.MedSpecOms == true) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									msg: langs('Создать ещё одно направление?'),
									title: langs('Вопрос'),
									icon: Ext.MessageBox.QUESTION,
									fn: function (buttonId) {
										if (buttonId === 'no') {
											sw.Promed.Direction.loadDirectionDataForZNO({
												typeofdirection: 'all',
												EvnDirection_pid: conf.params.EvnDirection_pid,
												callback: function (data) {
													if (data) {
														getWnd('swZNOinfoWindow').hide();
														var win = getWnd('swPersonEmkWindow');
														if (win.isVisible()) {
															win.openEmkEditWindow(false , getWnd('swPersonEmkWindow').Tree.getSelectionModel().selNode);
														}
													} 
												}
											});
										}
									}.createDelegate(this)
								});
							} else {
								sw.Promed.Direction.loadDirectionDataForZNO({
									typeofdirection: 'consultation',
									EvnDirection_pid: conf.params.EvnDirection_pid,
									callback: function (data) {
										if (data) {
											getWnd('swZNOinfoWindow').hide();
											var win = getWnd('swPersonEmkWindow');
											if (win.isVisible()) {
												win.openEmkEditWindow(false , getWnd('swPersonEmkWindow').Tree.getSelectionModel().selNode);
											}
										}
									}
								});
							}
						}
						conf.Timetable_id = answer.id;
						conf.params[answer.object +'_id'] = answer.id;
						conf.EvnDirection_id = answer.EvnDirection_id;
						conf.context = 'afterSheduleSave';

						if (answer.EvnDirection_TalonCode) {
							sw.swMsg.alert("Номер брони", "Сообщите номер брони пациенту. Номер брони необходим для последующей регистрации пациента в ЭО: " + answer.EvnDirection_TalonCode);
						}

						conf.onSaveRecord(conf, answer);
						if ( typeof conf.callback == 'function' ) {
							conf.callback(true,response);
						}
						if(
							isBelarus
							&&conf
							&&conf.params
							&&conf.params.DirType_id.inlist([3,16])
							&&conf.params.Lpu_did==getGlobalOptions().lpu_id
						){
							printBirt({
								'Report_FileName': 'pan_Talon_kvrachy.rptdesign',
								'Report_Params': '&paramtimetablegraf='+conf.params.TimetableGraf_id,
								'Report_Format': 'pdf'
							});
						}
						// Если сохранили направление, предлагаем его напечатать, пока для Уфы и Пскова
						if ((isUfa || isPskov || isEkb) && answer.evnDirectionData && answer.evnDirectionData.EvnDirection_id > 0) {
							if (conf.params && conf.params.type && conf.params.type == 'LpuReg' && isUfa) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									msg: langs('Распечатать стат. талон?'),
									title: langs('Вопрос'),
									icon: Ext.MessageBox.QUESTION,
									fn: function(buttonId){
										if (buttonId === 'yes') {
											//window.open('/?c=EvnPL&m=printEvnPLBlank&Person_id=' + conf.params.Person_id, '_blank');
											if (isUfa) {
												var params = new Object();
												params.type = 'EvnPL';
												params.PersonId = conf.params.Person_id;
												printEvnPLBlank(params);
											} else {
												window.open('/?c=EvnPL&m=printEvnPLBlank&Person_id=' + conf.params.Person_id + '&TimetableGraf_id='+conf.params.TimetableGraf_id, '_blank');
											}
										}
									}.createDelegate(this)
								});
							} else if (conf.params.Unscheduled != 'true' || conf.windowId != 'swWorkPlacePolkaRegWindow') {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									msg: langs('Вывести направление на печать?'),
									title: langs('Вопрос'),
									icon: Ext.MessageBox.QUESTION,
									fn: function(buttonId){
										if (buttonId === 'yes') {
											sw.Promed.Direction.print({
												EvnDirection_id: answer.evnDirectionData.EvnDirection_id
											});
										}
									}.createDelegate(this)
								});
							}
						}
						
						var win = getWnd('swEvnDirectionEditWindow');
						if (getRegionNick() == 'kz' && win && win.isVisible() && typeof win.hide == 'function') {
							win.hide();
						}

						// нужно сохранять направление при записи на бирку
						//this.createDirection(conf);
					} else {
						if (answer.queue) {
							var buttons = {
								yes: {
									text: langs('Исключить из очереди'),
									iconCls: 'delete16',
									tooltip: langs('Пациент будет исключен из очереди и записан на выбранную бирку')
								},
								cancel: {
									text: langs('Отмена'),
									iconCls: 'close16',
									tooltip: langs('Отмена записи пациента, без исключения его из очереди')
								}
							};
							if (getRegionNick() != 'perm') {
								buttons.no = {
									text: langs('Записать'),
									iconCls: 'ok16',
									tooltip: langs('Пациент будет записан на выбранную бирку, при этом находясь в очереди')
								};
							}
							sw.swMsg.show({
								buttons: buttons,
								msg: answer.queue.warning,
								title: langs('Внимание'),
								icon: Ext.MessageBox.WARNING,
								fn: function(buttonId){
									if (buttonId === 'yes') {
										conf.queue = answer.queue;
										this.recordFromQueue(conf);
									} else if (buttonId === 'no') {
										conf.params['AnswerQueue'] = 0;
										this.requestRecord(conf);
									} else {
										conf.onCancel();
									}
								}.createDelegate(this)
							});
						} else if (answer.alreadyHasRecordOnThisTime) {
							sw.swMsg.show({
								buttons: {
									yes: {
										text: langs('Записать на выбранное время'),
										iconCls: 'ok16',
										tooltip: langs('Пациент будет записан на выбранную бирку')
									},
									cancel: {
										text: langs('Выбрать другое время'),
										iconCls: 'close16',
										tooltip: langs('Пациент не будет записан на выбранную бирку')
									}
								},
								msg: answer.alreadyHasRecordOnThisTime,
								title: langs('Внимание'),
								icon: Ext.MessageBox.WARNING,
								fn: function(buttonId){
									if (buttonId === 'yes') {
										conf.params['IgnoreCheckAlreadyHasRecordOnThisTime'] = 1;
										this.requestRecord(conf);
									} else {
										conf.onCancel();
									}
								}.createDelegate(this)
							});
						} else if (answer.warning) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								msg: answer.warning,
								title: langs('Внимание'),
								icon: Ext.MessageBox.WARNING,
								fn: function(buttonId){
									if (buttonId === 'yes') {
										conf.params['OverrideWarning'] = 1;
										this.requestRecord(conf);
									}
								}.createDelegate(this)
							});
						} else if (answer.ho_warning) {
							var obj = this;
							Ext.Msg.confirm("Внимание!", answer.ho_warning + '<br> Продолжить сохранение?', function(btn){
								if (btn == 'yes') {
									Ext.Msg.alert("Внимание!", 'Данное направление не будет передано в&nbsp;сервис&nbsp;БГ', function(btn){
										conf.params['IgnoreCheckHospitalOffice'] = 2; 
										obj.requestQueue(conf);
									});
								}
							});
						} else {
							conf.onFailure(1,answer);
						}
					}
				} else {
					conf.onFailure(0);
				}
			}.createDelegate(this)
		});
	},
	/**
	 * Открывает форму заказа услуги
	 * @access private
	 * @params {Object} conf Опции:
	 *     Timetable_id (int) параметры (обязательный параметр)
	 *     person (Object) параметры человека(обязательный параметр)
	 *     direction (Object) параметры направления (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после загрузки данных (обязательный параметр)
	 *     onHide {Function} Функция, вызываемая после закрытия формы
	 *     action (string)
	openUslugaOrderEditWindow: function(conf)
	{
		if ( typeof conf != 'object' || !conf.direction || !conf.direction.LpuUnitType_SysNick || !conf.person || typeof conf.callback != 'function' ) {
			return false;
		}
		var formParams = {};
		formParams.Person_id = conf.person.Person_id;
		formParams.PersonEvn_id = conf.person.PersonEvn_id;
		formParams.Server_id = conf.person.Server_id;
		formParams['Person_Surname']=conf.person.Person_Surname;
		formParams['Person_Firname']=conf.person.Person_Firname;
		formParams['Person_Secname']=conf.person.Person_Secname;
		formParams['Person_Birthday']=conf.person.Person_Birthday;
		if(conf.Timetable_id) {
			formParams.TimetableMedService_id = conf.Timetable_id;
			formParams.time_table = this.getTimetableObject({LpuUnitType_SysNick: conf.direction.LpuUnitType_SysNick});
		}
		formParams.EvnUsluga_pid = conf.direction.EvnDirection_pid;
		formParams.PrehospDirect_id = conf.direction.PrehospDirect_id;//Идентификатор типа направления
		formParams.UslugaComplex_id = conf.direction.UslugaComplex_id;
		formParams.LpuSection_Name = conf.direction.LpuSection_Name;
		formParams.MedService_id = conf.direction.MedService_id;//Служба, которой назначается оказание услуги
		formParams.MedService_Nick = conf.direction.MedService_Nick;
		formParams.Lpu_id = conf.direction.Lpu_id;
		formParams.LpuSection_uid = conf.direction.LpuSection_uid;//Отделение, которому назначается оказание услуги
		formParams.Lpu_uid = conf.direction.Lpu_did;//Идентификатор МО, которому назначается оказание услуги
		//formParams.Org_uid = conf.direction.Org_uid;//Идентификатор организации, которой назначается оказание услуги
		formParams.Org_did = conf.direction.Org_did;//Идентификатор организации заказавшего услугу
		formParams.MedPersonal_did = conf.direction.MedPersonal_id;//Идентификатор врача заказавшего услугу
		formParams.LpuSection_did = conf.direction.LpuSection_id;//Идентификатор отделения МО заказавшего услугу
		formParams.Lpu_did = conf.direction.Lpu_id;//Идентификатор МО заказавшей услугу
		formParams['callback'] = conf.callback;
		formParams['action'] = conf.action || 'add';
		formParams['onHide'] = conf.onHide || Ext.emptyFn;
		getWnd('swEvnUslugaOrderEditWindow').show(formParams);
	},
	 */

	/**
	 * Создает заказ услуги в параклинике
	 */
	createOrder: function(record, personData, callback, useWindow, owner, uc_prescid, isCito, isReceive) {
		var p = {};
		p.Resource_id = record.get('Resource_id'); //Ресурс, которму назначается оказание услуги
		p.MedService_id = record.get('MedService_id'); //Служба, которой назначается оказание услуги
		p.MedService_Nick = record.get('MedService_Nick');
		p.Lpu_uid = record.get('Lpu_id');
		p.LpuSection_uid = record.get('LpuSection_id');
		p.UslugaComplexMedService_id = record.get('UslugaComplexMedService_id');
		p.LpuSectionProfile_id = record.get('LpuSectionProfile_id');
		p.UslugaComplex_id = record.get('UslugaComplex_id');
		p.UslugaComplex_Name = record.get('UslugaComplex_Name');
		p.MedServiceType_SysNick = record.get('MedServiceType_SysNick');
		p.EvnDirection_IsReceive = isReceive;
		if (personData && personData.Person_id) {
			p.Person_id = personData.Person_id;
			p.PersonEvn_id = personData.PersonEvn_id;
			p.Server_id = personData.Server_id;
		} else {
			p.Person_id = null;
			p.PersonEvn_id = null;
			p.Server_id = null;
		}
		p.Usluga_isCito = isCito || 1;

		if (useWindow) {
			// в окне выбираем пункт забора и состав для лабораторных услуг
			// Назначенная услуга
			p.UslugaComplex_prescid = uc_prescid || null;
			p.owner = owner || null;
			p.action = 'add';
			p.mode = 'nosave'; // просто возвращаем в калбэке данные
			p.callback = callback;
			getWnd('swEvnUslugaOrderEditWindow').show(p);
		} else {
			//автоматически создаем заказ для НЕ лабораторных услуг
			//без пункта забора и без состава
			callback(owner, null, p);
		}
	},
	/**
	 * Запрос для постановки в очередь
	 * @access private
	 * @params {Object} conf Опции:
	 *     params (Object) параметры (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после загрузки данных
	 *     loadMask (boolean) отображать маску во время запроса к серверу
	 *     windowId (string) идентификатор окна, на которое накладывается маска (обязательный параметр)
	 */
	requestQueue: function(conf)
	{
		log('requestQueue',conf)
		var isUfa = (getRegionNick() == 'ufa');
		var isPskov = (getRegionNick() == 'pskov');
		var isEkb = (getRegionNick() == 'ekb');
		var isKareliya = (getRegionNick() == 'kareliya');
		var isVK = (conf.params&&conf.params.DirType_id&&conf.params.DirType_id==9);
		//Если в диагнозе стоит Z03.1 для Уфы
		var ZNO = conf.params.ZNOinfo;
		if ( typeof conf != 'object' || typeof conf.params != 'object' || (!conf.windowId && !conf.win) ) {
			return false;
		}
		//console.warn(conf);
		if (conf.loadMask && getRegionNick() == 'kz') {
			var loadMask = new Ext.LoadMask(Ext.getBody(), {msg: langs('Подождите, пациент записывается в очередь...')});
			loadMask.show();
		} else if ( conf.loadMask ) {
			if (conf.win) {
				var loadMask = conf.win.getLoadMask(langs('Подождите, пациент записывается в очередь...'));
			} else {
				var loadMask = new Ext.LoadMask(Ext.get(conf.windowId), {msg: langs('Подождите, пациент записывается в очередь...')});
			}
			loadMask.show();
		}

		conf.onFailure = conf.onFailure || function(code) {
			switch (code) {
				case 0: // отсутствует ответ сервера
					if ( typeof conf.callback == 'function' ) {
						conf.callback(false);
					} else {
						Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции постановки в очередь<br/>произошла ошибка: <b>отсутствует ответ сервера</b>.'));
					}
					break;
				case 2: // failure
					Ext.Msg.alert(langs('Ошибка'), langs('При выполнении операции постановки в очередь <br/>произошла ошибка!'));
					break;
				default: // ничего не делаем
					break
			}
		};

		if (!Ext.isEmpty(conf.params.EvnDirection_IsCito))
			conf.params.EvnDirection_IsCito = (conf.params.EvnDirection_IsCito == '2' || conf.params.EvnDirection_IsCito == 'on') ? 2 : 1;
		Ext.Ajax.request({
			withoutErrorMsgBox: conf.withoutErrorMsgBox || false,
			url: C_QUEUE_ADD,
			params: conf.params,
			failure: function(response, options)
			{
				if ( conf.loadMask ) {
					loadMask.hide();
				}
				conf.onFailure(2);
			},
			success: function(response, action)
			{
				if ( conf.loadMask ) {
					loadMask.hide();
				}
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);

					if (answer.success) {
						if (answer.EvnDirection_id && answer.EvnDirection_id > 0 && conf.params && conf.params.onDirSave && (typeof conf.params.onDirSave == 'function')) {
							conf.params.onDirSave(answer.EvnDirection_id);
						}
						// Если сохранили направление, предлагаем его напечатать, пока для Уфы и Пскова
						if ((isUfa || isPskov || isEkb || (isKareliya && isVK)) && answer.EvnDirection_id && answer.EvnDirection_id > 0) {
							if (conf.params && conf.params.type && conf.params.type == 'LpuReg' && isUfa) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									msg: langs('Распечатать стат. талон?'),
									title: langs('Вопрос'),
									icon: Ext.MessageBox.QUESTION,
									fn: function(buttonId){
										if (buttonId === 'yes') {
											if (isUfa) {
												var params = new Object();
												params.type = 'EvnPL';
												params.PersonId = conf.params.Person_id;
												printEvnPLBlank(params);
											} else {
												window.open('/?c=EvnPL&m=printEvnPLBlank&Person_id=' + conf.params.Person_id, '_blank');
											}
										}
									}.createDelegate(this)
								});
							} else {
								sw.swMsg.show({
									buttons: {
										yes: {
											text: langs('Да')
										},
										no: {
											text: langs('Нет')
										}
									},
									msg: langs('Вывести направление на печать?'),
									title: langs('Вопрос'),
									icon: Ext.MessageBox.QUESTION,
									fn: function(buttonId){
										if (buttonId === 'yes') {
											sw.Promed.Direction.print({
												EvnDirection_id: answer.EvnDirection_id
											});
										}
										//#154675 ТАП. Формирование системного сообщения при сохранении ТАПа со случаем подозрения на ЗНО
										if (isUfa && ZNO == true && answer.EvnDirection_id && answer.EvnDirection_id > 0 ) {
											if (conf.params.MedSpecOms == true) {
											sw.swMsg.show({
												buttons: Ext.Msg.YESNO,
												msg: langs('Создать ещё одно направление?'),
												title: langs('Вопрос'),
												icon: Ext.MessageBox.QUESTION,
													fn: function (buttonId) {
														if (buttonId === 'no') {
															sw.Promed.Direction.loadDirectionDataForZNO({
																typeofdirection: 'all',
																EvnDirection_pid: conf.params.EvnDirection_pid,
																callback: function (data) {
																	if (data) {
																		getWnd('swZNOinfoWindow').hide();
																		var win = getWnd('swPersonEmkWindow');
																		if (win.isVisible()) {
																			win.openEmkEditWindow(false , getWnd('swPersonEmkWindow').Tree.getSelectionModel().selNode);
																		}
																	}
																}
															});
														} /*else {
															getWnd('swZNOinfoWindow').hide();
															if (typeof conf.callback == 'function') {
																conf.callback(answer);
															}
														}*/
													}.createDelegate(this)
											});
										} else {
											sw.Promed.Direction.loadDirectionDataForZNO({
												typeofdirection: 'consultation',
												EvnDirection_pid: conf.params.EvnDirection_pid,
												callback: function (data) {
													if (data) {
														getWnd('swZNOinfoWindow').hide();
														var win = getWnd('swPersonEmkWindow');
														if (win.isVisible()) {
															win.openEmkEditWindow(false , getWnd('swPersonEmkWindow').Tree.getSelectionModel().selNode);
														}
													}
												}
											});
										}
										}
									}.createDelegate(this)
								});
							}
						}
						if (conf.windowId) {
							// работает, только если conf.windowId совпадает с именем окна
							// при созданнии направления при добавлении назначения, окно добавления не должно закрываться!
							var win = getWnd(conf.windowId);
							if (win && typeof win.hide == 'function') {
								win.hide();
							}
						}
						
						var win = getWnd('swEvnDirectionEditWindow');
						if (getRegionNick() == 'kz' && win && win.isVisible() && typeof win.hide == 'function') {
							win.hide();
						}

						if ( typeof conf.onSaveQueue == 'function' ) {
							conf.onSaveQueue(answer);
						}

						if ( typeof conf.callback == 'function' ) {
							conf.callback(answer);
						}
					} else {
						if (answer.record) {
							var buttons = {
								yes: {
									text: langs('Освободить запись'),
									iconCls: 'delete16',
									//disabled: !answer.record.allowClear,
									disabled: sw.Promed.Direction.disAllowRejectDirectionOrRecord(answer.record),
									tooltip: langs('Убрать в очередь и освободить время приема')
								},
								no: {
									text: langs('Поставить в очередь'),
									iconCls: 'ok16',
									tooltip: langs('Пациент будет поставлен в очередь, запись на прием к врачу сохранена')
								},
								cancel: {
									text: langs('Отмена'),
									iconCls: 'close16',
									tooltip: langs('Отмена постановки пациента в очередь без отмены записи на прием')
								}
							};
							var msgbox = sw.swMsg.show({
								buttons: buttons,
								msg: answer.record.warning,
								title: langs('Внимание'),
								icon: Ext.MessageBox.WARNING,
								fn: function(buttonId){
									if (buttonId === 'yes') {
										// Убрать в очередь и освободить время приема
										if ( conf.loadMask ) {
											loadMask.show();
										}
										Ext.Ajax.request({
											url: '/?c=EvnDirection&m=returnToQueue',
											callback: function(options, success, response) {
												if ( loadMask ) {
													loadMask.hide();
												}
												var answer = false;
												if (success) {
													answer = Ext.util.JSON.decode(response.responseText);
												} else {
													Ext.Msg.alert(langs('Ошибка'), langs('При освобождении записи к врачу произошла ошибка.'));
												}
												if ( typeof conf.callback == 'function' ) {
													conf.callback(answer);
												}
											},
											params: {
												EvnDirection_id: answer.record.EvnDirection_id
											}
										});
									} else if (buttonId === 'no') {
										conf.params['AnswerRecord'] = 0;
										this.requestQueue(conf);
									}else{
										if ( conf.loadMask ) {
											loadMask.hide();
										}
									}
								}.createDelegate(this)
							});

							if (typeof msgbox.getDialog == 'function' )
								msgbox.getDialog().setWidth(640);
						}else if (answer.queue) {
							var buttons = {
								yes: {
									text: langs('Исключить из очереди'),
									iconCls: 'delete16',
									tooltip: langs('Обновление постановки в очередь к выбранному врачу')
								},
								no: {
									text: langs('Поставить в очередь'),
									iconCls: 'ok16',
									tooltip: langs('Пациент будет поставлен в очередь к выбранному врачу, без обновления текущего направления из очереди')
								},
								cancel: {
									text: langs('Отмена'),
									iconCls: 'close16',
									tooltip: langs('Отмена записи пациента, без исключения его из очереди')
								}
							};

							var msgbox = sw.swMsg.show({
								buttons: buttons,
								msg: answer.queue.warning,
								title: langs('Внимание'),
								icon: Ext.MessageBox.WARNING,
								fn: function(buttonId){
									if (buttonId === 'yes') {
										log('updateQueue',answer.queue, conf.params);
										var obj = this;
										/*пока убрал, т.к. используется хранимка p_EvnDirection_recordFromQueue
										if (!conf.params['EvnDirection_IsAuto']) {
											conf.params['EvnDirection_IsAuto'] = 1;
										}
										if (conf.params['EvnDirection_IsAuto'] != answer.queue.EvnDirection_IsAuto) {
											Ext.Msg.alert(langs('Ошибка'), langs('Автоматическое направление не может внезапно стать электронным и наоборот.'));
										}
										*/
										conf.params['QueueFailCause_id'] = null; // не отменa
										conf.params['EvnQueue_id'] = answer.queue.EvnQueue_id; // чтобы была вызвана хранимка p_EvnDirection_recordFromQueue
										conf.params['EvnDirection_id'] = answer.queue.EvnDirection_id;
										conf.params['EvnDirection_pid'] = answer.queue.EvnDirection_pid;
										conf.params['AnswerQueue'] = 0; // игнорируем контроль нахождения в очереди Queue_Model::checkQueueDuplicates
										conf.params['redirectEvnDirection'] = 700; // отключаю создание/отмену очереди в EvnDirection_model::beforeRedirectEvnDirection, делаю обновление очереди
										if (typeof conf.onCancelQueue == 'function' ) {
											// не было отмены conf.onCancelQueue(answer.queue.EvnQueue_id, function(){obj.requestQueue(conf);});
											obj.requestQueue(conf);
										} else {
											obj.requestQueue(conf);
										}
									} else if (buttonId === 'no') {
										conf.params['AnswerQueue'] = 0;
										this.requestQueue(conf);
									}
								}.createDelegate(this)
							});

							if (typeof msgbox.getDialog == 'function' )
								msgbox.getDialog().setWidth(640);
						} else if (answer.ho_warning) {
							var obj = this;
							Ext.Msg.confirm("Внимание!", answer.ho_warning + '<br> Продолжить сохранение?', function(btn){
								if (btn == 'yes') {
									Ext.Msg.alert("Внимание!", 'Данное направление не будет передано в&nbsp;сервис&nbsp;БГ', function(btn){
										conf.params['IgnoreCheckHospitalOffice'] = 2; 
										obj.requestQueue(conf);
									});
								}
							});
						} else {
							conf.onFailure(1,answer);
						}
					}
				} else {
					conf.onFailure(0);
				}
			}.createDelegate(this)
		});
	},
	/**
	 * Открывает форму электронного направления
	 * @access private
	 * @params {Object} options Опции:
	 *     params (Object) параметры (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после сохранения данных
	 *     onHide {Function} Функция, вызываемая после закрытия формы
	 *     action (string)
	 *     is_cito (boolean) признак экстренного направления
	 */
	openDirectionEditWindow: function(options)
	{
		if ( typeof options != 'object' || typeof options.params != 'object' ) {
			return false;
		}
		var formParams = options.params;
		// log(formParams);
		formParams.EvnDirection_pid = options.params.EvnDirection_pid || 0;
		formParams.EvnPrescr_id = options.params.EvnPrescr_id || null;
		formParams.PrescriptionType_Code = options.params.PrescriptionType_Code || null;
		winType = options.params.ext6 ? 'swEvnDirectionEditWindowExt6' : 'swEvnDirectionEditWindow';

		
		getWnd(winType).show({
			action: options.action || 'add',
			mode: options.mode || null,
			callback: (typeof options.callback == 'function')?options.callback:Ext.emptyFn,
			onHide: (typeof options.onHide == 'function')?options.onHide:Ext.emptyFn,
			Person_id: options.params.Person_id,
			Person_Surname: options.params.Person_Surname,
			Person_Firname: options.params.Person_Firname,
			Person_Secname: options.params.Person_Secname,
			Person_Birthday: options.params.Person_Birthday,
			is_cito: options.is_cito || false,
			disableClose: options.disableClose || false,
			formParams: formParams,
			ZNOinfo: options.params.ZNOinfo || null,
			isPaidVisit: options.isPaidVisit || null
		});
		return true;
	},
	//* @access private
	getTimetableObject:function(params)
	{
		var object = '';
		if (params && !Ext.isEmpty(params.Resource_id)) {
			object = 'TimetableResource'
		} else if (params && params.LpuUnitType_SysNick != undefined) {
			switch (params.LpuUnitType_SysNick)
			{
				case 'polka':
					object = 'TimetableGraf';
					break;
				case 'stac': case 'dstac': case 'hstac': case 'pstac':
				object = 'TimetableStac';
				break;
				case 'parka':
					object = 'TimetableMedService';
					break;
			}
		} else {
			if (ARMIsPolka())
				object = 'TimetableGraf';
			if (ARMIsParka())
				object = 'TimetableMedService';
			if (ARMIsStac())
				object = 'TimetableStac';
		}
		return object;
	},
	/**
	 * Запись к самому себе в поликлинике
	 * @access public
	 * @param {Object} option
	 */
	recordHimSelf: function(option) {
		// сразу заполним directionData
		var directionData = {};
		directionData['LpuUnitType_SysNick'] = 'polka';
		directionData['DirType_id'] = 16; // На поликлинический прием
		directionData['EvnDirection_IsAuto'] = 2; // автоматическое направление
		directionData['EvnDirection_IsReceive'] = 1; // с признаком “К себе”
		directionData['MedStaffFact_id'] = option.userMedStaffFact.MedStaffFact_id;
		directionData['LpuUnit_did'] = option.userMedStaffFact.LpuUnit_id;
		directionData['Lpu_did'] = option.userMedStaffFact.Lpu_id;
		directionData['MedPersonal_did'] = option.userMedStaffFact.MedPersonal_id;
		directionData['MedPersonal_id'] = option.userMedStaffFact.MedPersonal_id;
		directionData['LpuSection_did'] = option.userMedStaffFact.LpuSection_id;
		directionData['LpuSection_id'] = option.userMedStaffFact.LpuSection_id;
		directionData['LpuSectionProfile_id'] = option.userMedStaffFact.LpuSectionProfile_id;
		directionData['LpuSectionProfile_did'] = option.userMedStaffFact.LpuSectionProfile_id;
		directionData['Diag_id'] = option.Diag_id || null;
		directionData['EvnDirection_pid'] = option.EvnDirection_pid || null;
		directionData['ARMType_id'] = option.ARMType_id || option.userMedStaffFact.ARMType_id || null;
		directionData['time'] = (option.date && option.time)?(option.date +' '+ option.time):null;
		var params = {
			Timetable_id: option.TimetableGraf_id
			,userMedStaffFact: option.userMedStaffFact
			,direction: directionData
			,person: option.personData || null
			,loadMask: true
			,windowId: option.windowId
			,callback: option.onDirection || Ext.emptyFn
			,onSaveRecord: option.onSaveRecord || Ext.emptyFn
			,onHide: Ext.emptyFn
			,needDirection: null
			,fromEmk: false
			,mode: 'nosave'
			,date: option.date || null
		};
		this.recordPerson(params);
	},
	/** Возвращает набор параметров системного направления, не сохраняя его
	 * @access private
	 * @params {Object} option Опции:
	 * params (Object) параметры (обязательный параметр)
	 * callback {functions} функция, выполняемая по факту возврата данных
	 */
	getSysEvnDirection: function(option) {
		log('getSysEvnDirection', option);
		if ( typeof option != 'object' || typeof option.params != 'object' || !option.params.Lpu_did || !option.params.LpuUnitType_SysNick ) {
			return false;
		}
		// Если человек не известен, открываем форму поиска человека
		if ( typeof option.params.Server_id == 'undefined' || typeof option.params.Person_id == 'undefined' || typeof option.params.PersonEvn_id == 'undefined' ) {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(pdata)
				{
					if (!pdata.Person_IsDead || pdata.Person_IsDead == 'false') {
						getWnd('swPersonSearchWindow').hide();
						option.params.Server_id = pdata.Server_id;
						option.params.Person_id = pdata.Person_id;
						option.params.PersonEvn_id = pdata.PersonEvn_id;
						this.getSysEvnDirection(option);
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('Направление невозможно в связи со смертью пациента'));
					}
				}.createDelegate(this),
				searchMode: 'all'
			});
			return false;
		}
		if(typeof option.callback != 'function') {
			option.callback = Ext.emptyFn;
		}
		option.params.Diag_id = option.params.Diag_id || '';
		option.params.MedService_id = option.params.MedService_id || '';
		option.params.LpuSectionProfile_id = option.params.LpuSectionProfile_id || '';
		option.params.MedStaffFact_id = option.params.MedStaffFact_id || '';
		option.params.EvnDirection_id = option.params.EvnDirection_id ||'';
		option.params.EvnDirection_pid = option.params.EvnDirection_pid || '';
		option.params.EvnPrescr_id = option.params.EvnPrescr_id || '';
		option.params.PrescriptionType_Code = option.params.PrescriptionType_Code || '';
		if(!option.params.DirType_id) {
			option.params.DirType_id = this.defineDirTypeByPrescrType(option.params.PrescriptionType_Code);
		}
		option.params.EvnDirection_IsAuto = 2;
		option.params.EvnDirection_setDate = getGlobalOptions().date;
		option.params.EvnDirection_Num = option.params.EvnDirection_Num ||'0';
		option.params.MedPersonal_zid = '0';
		option.params.MedPersonal_id = option.params.MedPersonal_id || '';
		option.params.LpuSection_id = option.params.LpuSection_id || '';
		option.params.TimetableType_id = option.params.TimetableType_id || '';
		option.callback({evnDirectionData: option.params});
		return true;
	},


	/** Создание системного направления
	 * @access private
	 * @params {Object} option Опции:
	 *     params (Object) параметры (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после сохранения данных
	 */
	saveSysEvnDirection: function(option) {
		log('saveSysEvnDirection', option);
		if ( typeof option != 'object' || typeof option.params != 'object' || !option.params.Lpu_did || !option.params.LpuUnitType_SysNick ) {
			return false;
		}
		if ( typeof option.params.Server_id == 'undefined' || typeof option.params.Person_id == 'undefined' || typeof option.params.PersonEvn_id == 'undefined' ) {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(pdata)
				{
					if (!pdata.Person_IsDead || pdata.Person_IsDead == 'false') {
						getWnd('swPersonSearchWindow').hide();
						option.params.Server_id = pdata.Server_id;
						option.params.Person_id = pdata.Person_id;
						option.params.PersonEvn_id = pdata.PersonEvn_id;
						this.saveSysEvnDirection(option);
					} else {
						sw.swMsg.alert(langs('Ошибка'), langs('Направление невозможно в связи со смертью пациента'));
					}
				}.createDelegate(this),
				searchMode: 'all'
			});
			return false;
		}
		if(typeof option.callback != 'function') {
			option.callback = Ext.emptyFn;
		}
		option.params.Diag_id = option.params.Diag_id || '0';
		if(option.params.Diag_id==='0'&&option.vkPar&&option.vkPar.Diag_id>0){
			option.params.Diag_id = option.vkPar.Diag_id
		}
		if(option.toQueue&&option.toQueue==1){
			option.params.toQueue=1;
		}
		option.params.MedService_id = option.params.MedService_id || '0';
		option.params.LpuSectionProfile_id = option.params.LpuSectionProfile_id || '0';
		option.params.MedStaffFact_id = option.params.MedStaffFact_id || '0';
		option.params.From_MedStaffFact_id= option.params.MedStaffFact_id || '0';
		option.params.EvnDirection_id = option.params.EvnDirection_id ||null;
		option.params.EvnDirection_pid = option.params.EvnDirection_pid || '0';
		option.params.EvnPrescr_id = option.params.EvnPrescr_id || null;
		option.params.PrescriptionType_Code = option.params.PrescriptionType_Code || null;
		if(!option.params.DirType_id) {
			option.params.DirType_id = this.defineDirTypeByPrescrType(option.params.PrescriptionType_Code);
		}
		option.params.EvnDirection_IsAuto = 2;
		option.params.EvnDirection_setDate = getGlobalOptions().date;
		option.params.EvnDirection_Num = option.params.EvnDirection_Num ||'0';
		option.params.LpuSectionProfile_id = option.params.LpuSectionProfile_id || '0';
		option.params.MedPersonal_zid = '0';
		option.params.MedPersonal_id = option.params.MedPersonal_id || '0';
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=saveEvnDirection',
			params: option.params,
			failure: function(response, options) {
				Ext.Msg.alert(langs('Создание системного направления'), langs('При создании системного направления произошла ошибка!'));
			},
			success: function(response, action)
			{
				//alert('sdfsdfsdf')
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success) {
						option.params.EvnDirection_id = answer.EvnDirection_id;
						option.callback({
							evnDirectionData: option.params
						});
						return true;
					} else if (answer.Error_Msg) {
						Ext.Msg.alert(langs('Создание системного направления'), answer.Error_Msg);
						return false;
					}
				}
				Ext.Msg.alert(langs('Создание системного направления'), langs('При создании системного направления произошла ошибка! Отсутствует ответ сервера.'));
				return false;
			}.createDelegate(this)
		});
	},
	/**
	 * Тут вся логика по выписке направления при записи и постановке в очередь
	 * @access private
	 * @params {Object} option Опции:
	 *     params (Object) параметры (обязательный параметр)
	 *     callback {Function} Функция, вызываемая после сохранения данных
	 *     onHide {Function} Функция, вызываемая после закрытия формы
	 *     onNeedDirection {Function} Функция вызываемая при необходимости направления до вызова окна создания направления
	 *     needDirection (boolean) признак необходимости эл.направления
	 *	  Timetable_id (int) ид бирки
	 *	  fromEmk (boolean) признак направления из ЭМК
	 *	  mode (string) особый режим направления (например, по назначению)
	 *	  context (string) контекст направления (например, перед постановкой в очередь)
	 */
	createDirection: function(option)
	{
		log('createDirection', option);
		if ( typeof option != 'object' || typeof option.params != 'object' || (!option.params.Lpu_did && !option.params.isNotForSystem) || (!option.params.LpuUnitType_SysNick && !option.params.isNotForSystem) ) {
			return false;
		}
		if ( !this.userMedStaffFact ) {
			//параметры направления предварительно обрабатываются в checkDirectionParams
			log('где-то неправильно вызван приватный метод createDirection');
			return false;
		}
		option.mode = option.mode || '';
		option.context = option.context || 'afterRecord';

		var sysEvnDirectionParams = {callback: option.callback, params: option.params, mode: option.mode};

		if (this.userMedStaffFact.ARMType == 'regpol' || this.userMedStaffFact.ARMType == 'regpol6') {
			// для регистраторов направления в свою ЛПУ только на госпитализацию и в другие ЛПУ
			var needDirection = option.params.withDirection || (option.needDirection && ((option.params.DirType_id && option.params.DirType_id.inlist([1, 4, 5, 19])) || (option.params.Lpu_did != getGlobalOptions().lpu_id)));
		} else if (this.userMedStaffFact.ARMType && this.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms'])) {
			// Необходимо, чтобы пользователи АРМа "Оператор Call-центра" могли записывать пациентов во все МО без выписки ЭН и без создания автоматического направления #17785
			needDirection = false;
			sysEvnDirectionParams.params.addDirection = 0; // нет
		} else {
			// указан признак необходимости эл.направления или пациент записывается в чужую МО
			needDirection = (option.params.withDirection || option.needDirection || option.params.Lpu_did != getGlobalOptions().lpu_id);
		}
		
		sysEvnDirectionParams.params.MedStaffFact_did = option.params.MedStaffFact_did || null;
		sysEvnDirectionParams.params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id || option.params.MedStaffFact_id || null;
		sysEvnDirectionParams.params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id || option.params.MedPersonal_id || null;
		sysEvnDirectionParams.params.LpuSection_id = this.userMedStaffFact.LpuSection_id || option.params.LpuSection_id || null;
		sysEvnDirectionParams.params.Lpu_sid = this.userMedStaffFact.Lpu_id || option.params.Lpu_sid || getGlobalOptions().lpu_id;
		sysEvnDirectionParams.params.EvnUsluga_id = option.params.EvnUsluga_id || null;
		sysEvnDirectionParams.params.MedService_id = option.params.MedService_id || null;
		if (option.conf && option.conf.TimetableType_id) {
			sysEvnDirectionParams.params.TimetableType_id = option.conf.TimetableType_id;
		} else {
			sysEvnDirectionParams.params.TimetableType_id = option.params.TimetableType_id || null;
		}

		var object = this.getTimetableObject(option.params);
		sysEvnDirectionParams.params.timetable = object;
		if (option.Timetable_id) {
			//sysEvnDirectionParams.params.time = '00:00';
			// TODO: временная затычка
			switch (object) {
				case 'TimetableMedService':
					sysEvnDirectionParams.params['TimetableMedService_id'] = option.Timetable_id;
					break;
				default:
					sysEvnDirectionParams.params[object+'_id'] = option.Timetable_id;
					break;
			}
		}

		if (option.params.EvnDirection_id && option.params.EvnDirection_id > 0) {
			option.callback({evnDirectionData: option.params});
		} else if (needDirection) {
			var evnDirectionParams = sysEvnDirectionParams;
			evnDirectionParams.onHide = option.onHide || Ext.emptyFn;
			if  (option.onNeedDirection && typeof(option.onNeedDirection) == "function") {
				option.onNeedDirection();
			}
			this.openDirectionEditWindow(evnDirectionParams);
		} else {
			//создание системного направления
			this.getSysEvnDirection(sysEvnDirectionParams);
		}
	},
	loadDirectionDataForLeave: function(conf) {
		conf.loadMask.show();
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=loadDirectionDataForLeave',
			params: {
				EvnDirection_rid: conf.Evn_rid,
				rootEvnClass_SysNick: conf.EvnClass_SysNick
			},
			failure: function() {
				conf.loadMask.hide();
			},
			success: function(response, action) {
				conf.loadMask.hide();
				if (!response.responseText) {
					conf.callback(null);
					return false;
				}
				var answer = Ext.util.JSON.decode(response.responseText);
				if (answer.length == 0) {
					conf.callback(null);
					return false;
				}
				var data = {};
				if (conf.EvnClass_SysNick == 'EvnPS') {
					//при выборе исхода заболевания с кодом 2,4 (переводы) подставлять МО из направления
					data.Org_oid = answer[0].Org_did;
				} else {
					data.DirectType_id = 10;
					switch (parseInt(answer[0].DirType_id)) {
						case 1: case 5:
							switch (answer[0].LpuUnitType_SysNick) {
								case 'stac': data.DirectType_id = 3; break;
								case 'dstac': data.DirectType_id = 4; break;
								case 'pstac': data.DirectType_id = 5; break;
								case 'hstac': data.DirectType_id = 6; break;
							}
							break;
						case 3: data.DirectType_id = 8; break;
						case 4: data.DirectType_id = 9; break;
					}
					if (answer[0].Lpu_did == getGlobalOptions().lpu_id) {
						data.DirectClass_id = 1;
						data.LpuSection_oid = answer[0].LpuSection_did;
					} else {
						data.DirectClass_id = 2;
						data.Lpu_oid = answer[0].Lpu_did;
					}
				}
				conf.callback(data);
				return true;
			}
		});
	},
	loadDirectionDataForZNO: function(conf) {
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=loadDirectionDataForZNO',
			params: {
				EvnDirection_pid: conf.EvnDirection_pid,
				typeofdirection: conf.typeofdirection
			},
			failure: function() {
			},
			success: function (response, action) {
				if (!response.responseText) {
					conf.callback(null);
					return false;
				}
				var data = {};
				var answer = Ext.util.JSON.decode(response.responseText);
				if (answer.length == 0) {
					conf.callback(null);
					return true;
				} else {
					conf.callback(data);
					return true;
				}
			}
		});
	},
	/**
	 * Эта функция содержит логику определения того, требуется ли расписание для направлений
	 *
	 * Cейчас реализовано так.
	 * Есть отдельно генерирующееся расписание для прямой записи и расписание для направлений.
	 * При прямой записи не проверяется тип бирки, можно записывать на любую.
	 * Это например для администратора центра записи или для пользователя своей МО.
	 * При записи в расписание для направлений, не дает записываться на другие типы бирок кроме обычных и внешних всем кроме своей МО и администраторов ЦЗ.
	 * Чтобы получить расписание для направлений надо IsForDirection передать.
	 *
	 * @access public
	 * @params {Object} option Опции:
	 *     option.direction {Object} параметры направления (обязательный параметр)
	 *     option.MedStaffFact_id Врач, к которому записывается
	 * @return {boolean}
	 */
	isGetTimetableForDirection: function(option)
	{
		//log(option);
		if (option.direction && option.direction.Lpu_did == getGlobalOptions().lpu_id) {
			return false;
		}
		return true;
	},

	disAllowRejectDirectionOrRecord: function (data)
	{
		var TimetableGraf_Date = null,
			current_date = new Date();

		console.log(data);

		if (data.TimetableGraf_Date) {
			TimetableGraf_Date = Date.parseDate(data.TimetableGraf_Date.dateFormat('d.m.Y') + ' ' + data.TimetableGraf_begTime, 'd.m.Y H:i');
		}

		var disallow = data.ARMType_id == 24 ||
			(data.EvnStatus_id && data.EvnStatus_id.inlist([12,13,15])) ||
			(
				getGlobalOptions().disallow_canceling_el_dir_for_elapsed_time == true &&
				data.EvnDirection_id && TimetableGraf_Date &&
				current_date > TimetableGraf_Date
			) ||
			(
				getGlobalOptions().allow_canceling_without_el_dir_for_past_days != true &&
				! data.EvnDirection_id && TimetableGraf_Date &&
				current_date > TimetableGraf_Date
			);

		disallow = disallow || {
			incoming: sw.Promed.Direction.getDisallowIncomingConditions(data.MedPersonal_did),
			outcoming: sw.Promed.Direction.getDisallowOutcomingConditions(data.MedPersonal_id),
			both: sw.Promed.Direction.getDisallowIncomingConditions(data.MedPersonal_did) && sw.Promed.Direction.getDisallowOutcomingConditions(data.MedPersonal_id)
		}[data.RecordDirection];


		return disallow;
	},
	getDisallowIncomingConditions: function (MedPersonal_id)
	{
		return ! (
			getGlobalOptions().evn_direction_cancel_right_mo_where_adressed === '2' ||
			'toCurrMoDirCancel'.inlist(getGlobalOptions().groups.split('|')) ||
			(
				(MedPersonal_id || getGlobalOptions().CurMedPersonal_id) &&
				MedPersonal_id == getGlobalOptions().CurMedPersonal_id
			)
		);
	},

	getDisallowOutcomingConditions: function (MedPersonal_id)
	{
		return ! (
			getGlobalOptions().evn_direction_cancel_right_mo_where_created === '2' ||
			'currMoDirCancel'.inlist(getGlobalOptions().groups.split('|')) ||
			(
				(MedPersonal_id || getGlobalOptions().CurMedPersonal_id) &&
				MedPersonal_id == getGlobalOptions().CurMedPersonal_id
			)
		);
	}
};

/**
 *
 */
sw.Promed.UslugaClass = {
	store: null,
	onSelectCode: function(code, params, EvnUsluga_rid, exceptionHandler){
		if (typeof exceptionHandler != 'function') {
			exceptionHandler = Ext.emptyFn;
		}
		switch ( parseInt(code) ) {
			case 1:
				params.formParams.EvnUslugaOper_id = 0;
				params.formParams.EvnUslugaOper_rid = EvnUsluga_rid;
				getWnd('swEvnUslugaOperEditWindow').show(params);
				break;

			case 2:
				params.formParams.Usluga_id = 1012820;
				params.formParams.EvnUslugaPar_id = 0;
				params.formParams.EvnUslugaCommon_rid = EvnUsluga_rid;
				getWnd('swEvnUslugaCommonEditWindow').show(params);
				break;

			case 3:
				params.formParams.Usluga_id = 1012821;
				params.formParams.EvnUslugaPar_id = 0;
				params.formParams.EvnUslugaCommon_rid = EvnUsluga_rid;
				getWnd('swEvnUslugaCommonEditWindow').show(params);
				break;

			case 4:
				params.formParams.Usluga_id = 1012822;
				params.formParams.EvnUslugaPar_id = 0;
				params.formParams.EvnUslugaCommon_rid = EvnUsluga_rid;
				getWnd('swEvnUslugaCommonEditWindow').show(params);
				break;

			case 5:
				params.formParams.EvnUslugaPar_id = 0;
				params.formParams.EvnUslugaPar_rid = EvnUsluga_rid;
				getWnd('swEvnUslugaParEditWindow').show(params);
				break;

			case 6:
				params.formParams.EvnUslugaCommon_id = 0;
				params.formParams.EvnUslugaCommon_rid = EvnUsluga_rid;
				getWnd('swEvnUslugaEditWindow').show(params);
				break;

			case 7:
				params.formParams.EvnUslugaStom_id = 0;
				params.formParams.EvnUslugaStom_pid = EvnUsluga_rid;
				getWnd('swEvnUslugaStomEditWindow').show(params);
				break;

			case 8:
				params.formParams.EvnUslugaComplex_id = 0;
				params.formParams.EvnUslugaComplex_rid = EvnUsluga_rid;
				getWnd('swEvnUslugaComplexEditWindow').show(params);
				break;

			default:
				sw.swMsg.alert('Ошибка', 'Неверный код (' + code + ') класса услуги', exceptionHandler);
				return false;
				break;
		}
		return true;
	},
	getWhereClause: function(parentEvent, MorbusType_SysNick){
		var where = null;
		switch ( parentEvent ) {
			case 'EvnPL':
			case 'EvnPLStom':
			case 'EvnVizit':
			case 'EvnVizitPL':
				where = 'where UslugaClass_Code in (1, 6)';
				break;
			case 'EvnPS':
				if ('onko' == MorbusType_SysNick) {
					where = 'where UslugaClass_Code in (1, 2, 3, 4, 6)';
				} else {
					where = 'where UslugaClass_Code in (1, 6)';
				}
				break;
		}
		return where;
	},
	_onLoadStore: function(records, o, s, options){
		var menu = new Ext.menu.Menu({id: (options.id || 'menuUslugaClass')+options.Evn_pid});
		for(var i = 0;i < records.length;i++) {
			menu.add({
				text: records[i].get('UslugaClass_Code') + '. ' + records[i].get('UslugaClass_Name'),
				UslugaClass_id: records[i].get('UslugaClass_id'),
				UslugaClass_Code: records[i].get('UslugaClass_Code'),
				//iconCls: ico,
				handler: function() {
					if (typeof options.onSelect == 'function') {
						options.onSelect(this.UslugaClass_Code);
					}
				}
			});
		}
		options.callback(menu);
	},
	createMenu: function(options){
		var thas = this;
		if (!this.store) {
			this.store = new sw.Promed.LocalStorage({
				tableName: 'UslugaClass'
				,typeCode: 'int'
				,loadParams: {}
				,onLoadStore: function(){}
			});
		}

		if (!options) {
			options = {};
		}
		var where = this.getWhereClause(options.parentEvent, options.MorbusType_SysNick);
		if (!where) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверное значение параметра parentEvent'));
		} else {
			this.store.load({
				params: {
					where: where
				},
				callback: function(r, o, s){
					thas._onLoadStore(r, o, s, options);
				}
			});
		}
	}
};

sw.Promed.PersonOnkoProfile = {
	getControllerName: function(ReportType) {
		var controller;

		switch ( ReportType ) {
			case 'geriatrics':
				controller = 'GeriatricsQuestion';
				break;

			case 'palliat':
				controller = 'PalliatQuestion';
				break;

			case 'birads':
				controller = 'BIRADSQuestion';
				break;

			case 'previzit':
				controller = 'PreVizitQuestion';
				break;
				
			case 'recist':
				controller = 'RECISTQuestion';
				break;

			default:
				controller = 'OnkoCtrl';
				break;
		}

		return controller;
	},
	doDelete: function(id, cmp, callback, ReportType) {
		var url = '/?c=' + this.getControllerName(ReportType) + '&m=deleteOnkoProfile';

		var loadMask = new Ext.LoadMask(cmp.getEl(), {msg: "Удаление записи..."});
		loadMask.show();

		Ext.Ajax.request({
			failure: function(response, options) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), langs('При удалении записи'));
			},
			params: {
				PersonOnkoProfile_id: id
			},
			success: function(response, options) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( response_obj.success == false ) {
					sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При удалении записи'));
				} else {
					callback(id);
				}
			},
			url: url
		});
	},
	openEditWindow: function(action, formParams, callback, onHide) {
		if (false == action.inlist(['add','view','edit'])) {
			return false;
		}
		var params = {};
		params.action = action;
		params.Person_id = formParams.Person_id;
		params.EvnUslugaPar_id = formParams.EvnUslugaPar_id || null;
		if (('view' == action) || ('edit' == action)) {
			// Просмотр и редактирование
			params.PersonOnkoProfile_id = formParams.PersonOnkoProfile_id || null;
		} else {
			//Добавление 
			params.PersonOnkoProfile_id = null;
			params.PersonOnkoProfile_DtBeg = formParams.PersonOnkoProfile_DtBeg || null;
		}
		params.ReportType = formParams.ReportType || 'onko';
		params.callback = callback || Ext.emptyFn;
		params.onHide = onHide || Ext.emptyFn;

		if (params.ReportType.inlist(['birads', 'recist'])){
			params.MedPersonal_id = formParams.MedPersonal_id;
			params.LpuSection_id = formParams.LpuSection_id;
		}

		getWnd('amm_OnkoCtr_ProfileEditWindow').show(params);
		return true;
	},
	doPrint: function(Person_id, PersonOnkoProfile_id, paramSex) {
		printBirt({
			'Report_FileName': 'onkoPersonProfile.rptdesign',
			'Report_Params': '&paramPerson=' + Person_id + '&paramOnkoProfile=' + PersonOnkoProfile_id + '&paramSex=' + paramSex,
			'Report_Format': 'pdf'
		});
	},
	checkIsNeedOnkoControl: function(vizit_data, cmp, callback) {
		if (vizit_data.Person_id > 0
			&& vizit_data.MedStaffFact_id > 0
			&& Ext.isDate(vizit_data.Person_Birthday)
			&& Ext.isDate(vizit_data.EvnVizitPL_setDate)
			&& swGetPersonAge(vizit_data.Person_Birthday, vizit_data.EvnVizitPL_setDate) >= 18
			&& 'ufa' == getRegionNick()
		) {
			var self = this,
				loadMask = new Ext.LoadMask(cmp.getEl(), {msg: "Проверка необходим ли онкоконтроль..."});
			loadMask.show();
			Ext.Ajax.request({
				failure: function(response, options) {
					loadMask.hide();
					sw.swMsg.alert(langs('Ошибка'), langs('При проверке необходим ли онкоконтроль возникла ошибка'));
				},
				params: {
					Person_id: vizit_data.Person_id,
					MedStaffFact_id: vizit_data.MedStaffFact_id
				},
				success: function(response, options) {
					loadMask.hide();
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( response_obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При проверке необходим ли онкоконтроль произошла ошибка'));
						callback.call(cmp, false, null, null);
					} else if ( !response_obj.IsNeedOnkoControl ) {
						callback.call(cmp, true, null, null);
					} else {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									//При сохранении анкеты (при нажатии функциональной кнопки «Сохранить») сохранить посещение. Если анкета не сохранена (при нажатии функциональной кнопки «Закрыть»), то сохранение посещения отменить, возврат на форму добавления / редактирования посещения.
									self.openEditWindow('add',
										{
											Person_id: vizit_data.Person_id,
											PersonOnkoProfile_DtBeg: vizit_data.EvnVizitPL_setDate,
											PersonOnkoProfile_id: null
										},
										function(win, id) {
											//log('PersonOnkoProfile add callback', arguments);
											if (win.isPersonOnkoProfileSaved) {
												callback.call(cmp, true, function(EvnVizitPL_id){
													//посещение сохранено сохранять идентификатор посещения в поле Evn_id таблицы onko.PersonOnkoProfile
													if (EvnVizitPL_id > 0 && id > 0) {
														Ext.Ajax.request({
															params: {
																Evn_id: EvnVizitPL_id,
																PersonOnkoProfile_id: id
															},
															failure: function(response, options) {
																sw.swMsg.alert(langs('Ошибка'), langs('При сохранении связи посещения с анкетой по онкоконтролю возникла ошибка'));
															},
															success: function(response, options) {
																var response_obj = Ext.util.JSON.decode(response.responseText);
																if ( response_obj.success == false ) {
																	sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При сохранении связи посещения с анкетой по онкоконтролю возникла ошибка'));
																}
															},
															url: '/?c=OnkoCtrl&m=updateEvnId'
														});
													} else {
														sw.swMsg.alert(langs('Ошибка'), langs('Недостаточно параметров для сохранения связи посещения с анкетой по онкоконтролю'));
													}
												}, function(){
													//не удалось сохранить посещение, удалять анкету?
												});
											}
										},
										function(win) {
											//log('PersonOnkoProfile add onHide', arguments);
											if (!win.isPersonOnkoProfileSaved) {
												callback.call(cmp, false, null, null);
											}
										}
									);
								} else {
									callback.call(cmp, false, null, null);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Пациенту необходимо пройти онкоконтроль. <br>Заполнить Анкету?'),
							title: langs('Вопрос')
						});
					}
				},
				url: '/?c=OnkoCtrl&m=checkIsNeedOnkoControl'
			});
		} else {
			callback.call(cmp, true, null, null);
		}
	}
};

sw.Promed.EvnPL = {
	getDateX2016: function() {
		var result = new Date(2016, 0, 1);

		if ( getRegionNick() == 'khak' ) {
			result = new Date(2017, 8, 1);
		}

		return result;
	},
	getEvnPLStomNewBegDate: function() {
		return EVNPLSTOMNEW_BEGDATE; // из jsConstants.php / из конфига
	},
	filterFedLeaveType: function (cfg) {
		log('filterFedLeaveType', cfg);
		if (!cfg.fieldFedLeaveType || !cfg.fieldFedResultDeseaseType) {
			return true;
		}
		var Result_arr = [];


		var ResultDeseaseType_id = cfg.fieldFedResultDeseaseType.getValue();
		if (ResultDeseaseType_id && getRegionNick() != 'khak') {
			swResultDeseaseLeaveTypeGlobalStore.each(function (rec) {
				if (rec.get('ResultDeseaseType_id') == ResultDeseaseType_id) {
					Result_arr.push(rec.get('LeaveType_id'))
				}
			})
		}

		cfg.fieldFedLeaveType.getStore().clearFilter();
		if (Result_arr.length > 0) {
			cfg.fieldFedLeaveType.getStore().filterBy(function (rec) {
				return (rec.get('LeaveType_id').inlist(Result_arr))
			})

		} else {
			var USLOV = cfg.fieldFedLeaveType.USLOV || 3;
			cfg.fieldFedLeaveType.getStore().filterBy(function (rec) {
				return (rec.get('LeaveType_USLOV') == USLOV && rec.get('LeaveType_Code') <= 315)
			})
		}
		var index = cfg.fieldFedLeaveType.getStore().findBy(function (rec, id) {
			return (rec.get('LeaveType_id') == cfg.fieldFedLeaveType.getValue());
		});


		if (index < 0) {
			cfg.fieldFedLeaveType.clearValue();
		}
	},
	filterFedResultDeseaseType: function (cfg) {
		log('filterFedResultDeseaseType', cfg);
		if (!cfg.fieldFedLeaveType || !cfg.fieldFedResultDeseaseType) {
			return true;
		}
		var Result_arr = [];


		var LeaveType_id = cfg.fieldFedLeaveType.getValue();
		if (LeaveType_id) {
			swResultDeseaseLeaveTypeGlobalStore.each(function (rec) {
				if (rec.get('LeaveType_id') == LeaveType_id) {
					Result_arr.push(rec.get('ResultDeseaseType_id'))
				}
			})
		}

		cfg.fieldFedResultDeseaseType.getStore().clearFilter();
		if (Result_arr.length > 0) {
			cfg.fieldFedResultDeseaseType.getStore().filterBy(function (rec) {
				return (rec.get('ResultDeseaseType_id').inlist(Result_arr))
			});
		} else {
			var USLOV = cfg.fieldFedLeaveType.USLOV || 3;
			var reg = new RegExp('^(' + USLOV + ')');
			cfg.fieldFedResultDeseaseType.getStore().filterBy(function (rec) {
				return (reg.test(rec.get('ResultDeseaseType_Code')) && rec.get('ResultDeseaseType_Code') != 306)
			})
		}
		var index = cfg.fieldFedResultDeseaseType.getStore().findBy(function (rec, id) {
			return (rec.get('ResultDeseaseType_id') == cfg.fieldFedResultDeseaseType.getValue());
		});

		if (index < 0) {
			cfg.fieldFedResultDeseaseType.clearValue();
		}
	},
	/**
	 * Расчет значения поля Фед. результат
	 * @param cfg
	 */
	
	calcFedLeaveType: function(cfg) {
		log('calcFedLeaveType', cfg);
		if (!cfg.fieldFedLeaveType) {
			return false;
		}
		var is2016 = cfg.is2016 || false;
		var IsFinish = cfg.IsFinish || 1;
		/*if(!Ext.isEmpty(cfg.fieldFedLeaveType.getValue())){
			
			return true;
		}*/
		var isAllowToogleContainer = true;
		if (cfg.disableToogleContainer) {
			isAllowToogleContainer = false;
		}
		if (false == getRegionNick().inlist([ 'khak', 'perm' ])
			|| (getRegionNick().inlist([ 'khak' ]) && is2016 == false)
			|| (!getRegionNick().inlist([ 'khak' ]) && !cfg.ResultClass_Code)
		) {
			cfg.fieldFedLeaveType.clearValue();
			if (isAllowToogleContainer) {
				cfg.fieldFedLeaveType.hideContainer();
			}
			cfg.fieldFedLeaveType.setAllowBlank(true);
			return false;
		}
		if (isAllowToogleContainer && (!getRegionNick().inlist([ 'khak' ]) || (is2016 == true && IsFinish == 2))) {
			cfg.fieldFedLeaveType.showContainer();
		}
		cfg.fieldFedLeaveType.setAllowBlank(cfg.fieldFedLeaveType.hidden);
		/*if ( getRegionNick().inlist([ 'khak' ]) ) {
			return false;
		}*/
		if(!cfg.ResultClass_Code) {
			cfg.ResultClass_Code = '0';
		}
		var FedLeaveType_Code;
		var ResultClass_Code = cfg.ResultClass_Code.toString();
		//var ResultClass_id = cfg.ResultClass_id;
		if (!cfg.DirectType_Code) {
			cfg.DirectType_Code = '0';
		}
		if (!cfg.DirectClass_Code) {
			cfg.DirectClass_Code = '0';
		}
		var
			DirectType_Code = cfg.DirectType_Code.toString(),
			DirectClass_Code = cfg.DirectClass_Code.toString();
			//log('direct',DirectType_Code,DirectClass_Code)
		switch ( true ) {
			case (is2016 == false && '1' == DirectType_Code):
			case (is2016 == true && '1' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '305'; // Направлен на госпитализацию
				break;
			case (is2016 == false && DirectType_Code.inlist(['3','4'])):
			case (is2016 == true && DirectType_Code.inlist(['3','4']) && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '306'; // Направлен в дневной стационар
				break;
			case (is2016 == false && '5' == DirectType_Code):
			case (is2016 == true && '5' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '307'; // Направлен в стационар на дому
				break;
			case (is2016 == false && '6' == DirectType_Code && '2' == DirectClass_Code):
			case (is2016 == true && '6' == DirectType_Code && '2' == DirectClass_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '309'; // Направлен на консультацию в другое ЛПУ
				break;
			case (is2016 == false && '6' == DirectType_Code):
			case (is2016 == true && '6' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '308'; // Направлен на консультацию
				break;
			case (is2016 == false && '2' == DirectType_Code):
			case (is2016 == true && '2' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '310'; // Направлен в реабилитационное отделение
				break;
			case (is2016 == false && '7' == DirectType_Code):
			case (is2016 == true && '7' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '311'; // Направлен на санаторно-курортное лечение
				break;
			case (is2016 == false && ResultClass_Code.inlist(['1','2','5']) && '0' == DirectType_Code):
			case (is2016 == true && ResultClass_Code.inlist(['1','2','3']) && '0' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
			//case (false == ResultClass_Code.inlist(['1','2','3','4','6','7']) && '0' == DirectType_Code):
				FedLeaveType_Code = '301'; // Лечение завершено
				break;
			case ('0' == DirectType_Code && ResultClass_Code.inlist(['6'])):
			case (is2016 == true && cfg.InterruptLeaveType_id == 1):
				FedLeaveType_Code = '302'; // Лечение прервано по инициативе пациента
				break;
			case ('0' == DirectType_Code && ResultClass_Code.inlist(['7'])):
			case (is2016 == true && cfg.InterruptLeaveType_id == 2):
				FedLeaveType_Code = '303'; // Лечение прервано по инициативе ЛПУ
				break;
			case (is2016 == true && ResultClass_Code.inlist(['4']) && '0' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '304'; // Ухудшение
				break;
			case (false):
				FedLeaveType_Code = '312'; // Заполняется при ДВН и ДДС
				break;
			case (is2016 == false && ResultClass_Code.inlist(['4']) && '0' == DirectType_Code):
			case (is2016 == true && ResultClass_Code.inlist(['5']) && '0' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '313'; // Констатация факта смерти
				break;
			case (is2016 == false && ResultClass_Code.inlist(['3'])&& '0' == DirectType_Code):
			case (is2016 == true && false): // Не используем
				FedLeaveType_Code = '314'; // Динамическое наблюдение
				break;
			case (is2016 == false && '8' == DirectType_Code):
			case (is2016 == true && '8' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedLeaveType_Code = '315'; // Направлен на обследования
				break;
			default:
				FedLeaveType_Code = null;
				break;
		}
		if (!FedLeaveType_Code||FedLeaveType_Code==null) {
			return false;
		}
		if(FedLeaveType_Code == '313'){
			cfg.fieldFedLeaveType.getStore().clearFilter();
		}
		var index = cfg.fieldFedLeaveType.getStore().findBy(function(rec, id) {
			return (rec.get('LeaveType_Code') == FedLeaveType_Code);
		});
		
		if ( index < 0 && !cfg.isEmk) { log('FedLeaveType',index,cfg.isEmk,cfg.fieldFedLeaveType.getStore())
			return false;
		}
		if (getRegionNick() != 'khak') cfg.fieldFedLeaveType.setFieldValue('LeaveType_Code', FedLeaveType_Code);

			return cfg.fieldFedLeaveType.getValue();
	},
	/**
	 * Расчет значения поля Фед. исход
	 * @param cfg
	 */
	calcFedResultDeseaseType: function(cfg) {
		log('calcFedResultDeseaseType', cfg);
		if (!cfg.fieldFedResultDeseaseType) {
			return false;
		}
		var is2016 = cfg.is2016 || false;
		var IsFinish = cfg.IsFinish || 1;
		var isAllowToogleContainer = true;
		if (cfg.disableToogleContainer) {
			isAllowToogleContainer = false;
		}
		if (false == getRegionNick().inlist([ 'khak', 'perm' ])
			|| (getRegionNick().inlist([ 'khak' ]) && is2016 == false)
			|| (!getRegionNick().inlist([ 'khak' ]) && !cfg.ResultClass_Code)
		) {
			cfg.fieldFedResultDeseaseType.clearValue();
			if (isAllowToogleContainer) {
				cfg.fieldFedResultDeseaseType.hideContainer();
			}
			cfg.fieldFedResultDeseaseType.setAllowBlank(true);
			return false;
		}
		if (isAllowToogleContainer && (!getRegionNick().inlist([ 'khak' ]) || (is2016 == true && IsFinish == 2))) {
			cfg.fieldFedResultDeseaseType.showContainer();
		}
		cfg.fieldFedResultDeseaseType.setAllowBlank(cfg.fieldFedResultDeseaseType.hidden);
		var FedResultDeseaseType_Code;
		if (!cfg.DirectType_Code) {
			cfg.DirectType_Code = '0';
		}
		if(!cfg.ResultClass_Code) {
			cfg.ResultClass_Code = '0';
		}
		var DirectType_Code = cfg.DirectType_Code.toString();
		var ResultClass_Code = cfg.ResultClass_Code.toString();
		switch ( true ) {
			case (is2016 == false && ResultClass_Code.inlist(['1']) && '0' == DirectType_Code):
			case (is2016 == true && ResultClass_Code.inlist(['1']) && '0' == DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedResultDeseaseType_Code = '301'; // Выздоровление
				break;
			case (is2016 == false && ResultClass_Code.inlist(['2']) && '1' != DirectType_Code):
			case (is2016 == true && ResultClass_Code.inlist(['3']) && '1' != DirectType_Code && Ext.isEmpty(cfg.InterruptLeaveType_id)):
				FedResultDeseaseType_Code = '303'; // Улучшение
				break;
			case (
				is2016 == false
				&& (
					'1' == DirectType_Code
					|| (ResultClass_Code.inlist(['3','5','6','7']) && '5' != DirectType_Code)
				)
			):
			case (is2016 == true
				&& (
					(Ext.isEmpty(cfg.InterruptLeaveType_id) && ((ResultClass_Code.inlist(['2']) && '5' != DirectType_Code) || '1' == DirectType_Code))
					|| !Ext.isEmpty(cfg.InterruptLeaveType_id)
					|| ResultClass_Code.inlist(['6', '7'])
				)
			):
				FedResultDeseaseType_Code = '304'; // Без перемен
				break;
			case (is2016 == false && ResultClass_Code.inlist(['4']) && false == DirectType_Code.inlist(['2','7'])):
			case (
				is2016 == true && Ext.isEmpty(cfg.InterruptLeaveType_id)
				&& (
					ResultClass_Code.inlist(['5'])
					|| (ResultClass_Code.inlist(['4']) && false == DirectType_Code.inlist(['2','7']))
				)
			):
				FedResultDeseaseType_Code = '305'; // Ухудшение
				break;
			default:
				//FedResultDeseaseType_Code = null;
				FedResultDeseaseType_Code = '302'; // Ремиссия	Не используем
				break;
		}
		if (!FedResultDeseaseType_Code) {
			return false;
		}
		/*if(ResultClass_Code==4){
			cfg.fieldFedResultDeseaseType.getStore().clearFilter();
		}*/
		var index = cfg.fieldFedResultDeseaseType.getStore().findBy(function(rec, id) {
			return (rec.get('ResultDeseaseType_Code') == FedResultDeseaseType_Code);
		});

		if ( index < 0 && !cfg.isEmk) { log('FedResultDeseaseType',index,cfg.isEmk,cfg.fieldFedResultDeseaseType.getStore())
			FedResultDeseaseType_Code = '302';
		}
		if (getRegionNick() != 'khak') cfg.fieldFedResultDeseaseType.setFieldValue('ResultDeseaseType_Code', FedResultDeseaseType_Code);
		return cfg.fieldFedResultDeseaseType.getValue();
	},
	createFedViewHtmlFormField: function(sectionName, objectId, name,Store) {
		var store=Store;
		if(!store){
			if ('ResultDeseaseType' == name) {
				store = new Ext.db.AdapterStore({
					autoLoad: false,
					dbFile: 'Promed.db',
					fields: [
						{ name: 'ResultDeseaseType_id', mapping: 'ResultDeseaseType_id' },
						{ name: 'ResultDeseaseType_Code', mapping: 'ResultDeseaseType_Code' },
						{ name: 'ResultDeseaseType_Name', mapping: 'ResultDeseaseType_Name' }
					],
					key: 'ResultDeseaseType_id',
					sortInfo: { field: 'ResultDeseaseType_Code' },
					tableName: 'nsi_ResultDeseaseType'
				});
			} else {
				store = new Ext.db.AdapterStore({
					autoLoad: false,
					dbFile: 'Promed.db',
					fields: [
						{ name: 'LeaveType_id', mapping: 'LeaveType_id' },
						{ name: 'LeaveType_Code', mapping: 'LeaveType_Code' },
						{ name: 'LeaveType_Name', mapping: 'LeaveType_Name' },
						{ name: 'LeaveType_USLOV', mapping: 'LeaveType_USLOV' },
						{ name: 'LeaveType_begDT', mapping: 'LeaveType_begDT', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'LeaveType_endDT', mapping: 'LeaveType_endDT', type: 'date', dateFormat: 'd.m.Y' }
					],
					key: 'LeaveType_id',
					sortInfo: { field: 'LeaveType_Code' },
					tableName: 'LeaveTypeFed'
				});
			}
		}
		return new sw.Promed.viewHtmlForm.Field({
			sectionName: sectionName,
			objectId: objectId,
			name: 'Fed' + name,
			valueField: name + '_id',
			codeField: name + '_Code',
			displayField: name + '_Name',
			disabled: true,
			store: store
			//callback:callback
		});
	},
	isHiddenFedResultFields: function() {
		// Открыто для Перми, Хакасии
		return (false == getRegionNick().inlist([ 'khak', 'perm' ]));
	}
};

sw.Promed.EvnVizitPL = {
	isAllowBlankDiag: function() {
		return !getRegionNick().inlist([ 'ekb', 'pskov', 'ufa' ]);
	},
	isAllowBlankVizitCode: function() {
		return !getRegionNick().inlist([ 'pskov', 'ufa', 'buryatiya', 'kz', 'perm', 'vologda' ]);
	},
	isSupportVizitCode: function() {
		return getRegionNick().inlist([ 'pskov', 'ufa', 'ekb', 'kz', 'buryatiya', 'perm', 'vologda' ]);
	}
};

sw.Promed.EvnUslugaStom = {
	getMinUetValue: function() {
		var result = 0;

		switch ( getRegionNick() ) {
			//case 'ekb': result = 1; break;
		}
		
		return result;
	},
	getMaxUetValue: function() {
		var result = 20;

		switch ( getRegionNick() ) {
			case 'buryatiya': result = 100; break;
			case 'ekb': result = 50; break;
			case 'ufa': result = 20; break;
		}
		
		return result;
	}
};

sw.Promed.EvnVizitPLStom = {
	isAllowBlankVizitCode: function() {
		return !getRegionNick().inlist([ 'pskov', 'ufa', 'kz' ]);
	},
	isSupportVizitCode: function() {
		return getRegionNick().inlist([ 'pskov', 'ufa', 'ekb', 'kz' ]);
	}
};

sw.Promed.EvnStick = {
	getDeleteAlertCodes: function(cfg) {
		if (!cfg.Ext) {
			cfg.Ext = Ext;
		}
		return {
			'704': {
				buttons: cfg.Ext.Msg.YESNO,
				fn: function(buttonId, scope) {
					if (buttonId == 'yes') {
						if (!cfg.options.params) {
							cfg.options.params = {};
						}
						cfg.options.params.ignoreStickFromFSS = true;
						cfg.callback(cfg.options);
					}
				}
			},
			'706': {
				buttons: cfg.Ext.Msg.YESNO,
				fn: function(buttonId, scope) {
					if (buttonId == 'yes') {
						if (!cfg.options.params) {
							cfg.options.params = {};
						}
						cfg.options.params.ignoreStickHasProlongation = true;
						cfg.callback(cfg.options);
					}
				}
			},
			'707': {
				buttons: cfg.Ext.Msg.YESNO,
				fn: function(buttonId, scope) {
					if (buttonId == 'yes') {
						if (!cfg.options.params) {
							cfg.options.params = {};
						}
						cfg.options.params.ignoreStickHasPrevious = true;
						cfg.callback(cfg.options);
					}
				}
			}
		};
	}
};

sw.Promed.EvnSection = {
	listLeaveTypeSysNickEvnOtherLpu: [ // Перевод в другую МО
		'other','dsother','ksother','ksperitar'
	],
	listLeaveTypeSysNickEvnOtherStac: [// Перевод в стационар другого типа
		'stac','ksstac','dsstac'
	],
	onSaveEditForm: function(params) {
		var directionParams = null;
		if (params.LeaveType_SysNick && params.LeaveType_SysNick.inlist(sw.Promed.EvnSection.listLeaveTypeSysNickEvnOtherLpu)) {
			/*
			При выборе в исходе госпитализаций значений «переведен в другое ЛПУ» при сохранении,
			если в случае госпитализации отсутствуют выписанные из случая электронные направления стационарных типов (см. выше),
			открывать окно обязательной выписки направлений (мастер направлений, типы ограничить стационарными),
			подставлять в фильтр МО из поля «МО».
			*/
			directionParams = {
				Lpu_did: params.Lpu_oid,
				Org_did: params.Org_oid
			};
		}
		if (params.LeaveType_SysNick && params.LeaveType_SysNick.inlist(sw.Promed.EvnSection.listLeaveTypeSysNickEvnOtherStac)) {
			/*
			При выборе в исходе госпитализаций значений «переведен в стационар другого типа» при сохранении,
			если в случае госпитализации отсутствуют выписанные из случая электронные направления стационарных типов (см. выше),
			открывать  мастер направлений, типы ограничить стационарными,
			подставлять свое МО в фильтр и профиль из профиля выбранного в КВС отделения.
			Либо сразу после выбора типа направления переходить к расписанию/очереди выбранного отделения (если технически это возможно и не будет на данном этапе источником ошибок).
			*/
			directionParams = {
				Lpu_did: getGlobalOptions().lpu_id,
				Org_did: getGlobalOptions().org_id,
				LpuUnitType_did: params.LpuUnitType_oid,
				LpuUnit_did: params.LpuUnit_oid,
				LpuSectionProfile_id: params.LpuSectionProfile_oid || params.LpuSectionProfile_eid,
				LpuSectionProfile_did: params.LpuSectionProfile_oid || params.LpuSectionProfile_eid,
				LpuSectionAge_id: params.LpuSectionAge_oid || null,
				LpuSection_did: params.LpuSection_oid
			};
		}
		if (typeof params.callback != 'function') {
			params.callback = Ext.emptyFn;
		}
		if (!params.userMedStaffFact) {
			params.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		}
		// Добавил условие на открытие мастера направлений
		// https://redmine.swan.perm.ru/issues/68773
		if (directionParams && (userIsDoctor() || haveArmType('regpol') || haveArmType('regpol6'))) {
			directionParams.Lpu_sid = getGlobalOptions().lpu_id;
			directionParams.LpuSection_id = params.LpuSection_id;
			directionParams.MedPersonal_id = params.MedPersonal_id;
			directionParams.MedStaffFact_id = params.MedStaffFact_id;
			directionParams.From_MedStaffFact_id = params.MedStaffFact_id;
			directionParams.MedStaffFact_sid = params.MedStaffFact_id;
			directionParams.Diag_id = params.Diag_id;
			directionParams.Person_id = params.Person_id;
			directionParams.PersonEvn_id = params.PersonEvn_id;
			directionParams.Server_id = params.Server_id;
			directionParams.EvnDirection_pid = params.EvnSection_id;
			directionParams.ARMType_id = (params.userMedStaffFact && typeof params.userMedStaffFact == 'object' && !Ext.isEmpty(params.userMedStaffFact.ARMType_id) ? params.userMedStaffFact.ARMType_id : null);
			// проверка выписанных из случая электронных направлений стационарных типов 
			Ext.Ajax.request({
				params: {
					useCase: 'check_exists_dir_stac_in_evn',
					Person_id: directionParams.Person_id,
					EvnDirection_pid: directionParams.EvnDirection_pid,
					Lpu_did: directionParams.Lpu_did,
					LpuSectionProfile_did: directionParams.LpuSectionProfile_did || null
				},
				callback: function(opt, success, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if ( Ext.isArray(response_obj) ) {
						if ( response_obj.length > 0 ) {
							params.callback();
						} else {
							if (getWnd('swDirectionMasterWindow').isVisible()) {
								getWnd('swDirectionMasterWindow').hide();
							}
							var org_store = new Ext.data.JsonStore({
								autoLoad: false,
								reader: new Ext.data.JsonReader({
									id: 'Org_id'
								},[ {
									mapping: 'Org_id',
									name: 'Org_id',
									type: 'int'
								}, {
									mapping: 'Lpu_id',
									name: 'Lpu_id',
									type: 'int'
								},{
									mapping: 'Org_Nick',
									name: 'Org_Nick',
									type: 'string'
								}]),
								fields: [
									'Org_id',
									'Lpu_id',
									'Org_Nick'
								],
								url: C_ORG_LIST
							});
							org_store.load({
								params: {
									Org_id: directionParams.Org_did,
									OrgServed_Type:11,
									OrgType: 'org',
									onlyFromDictionary:false
								},
								callback: function() {
									if ( org_store.getCount() > 0 ) {
										directionParams.Lpu_did = org_store.getAt(0).get('Lpu_id');
										getWnd('swDirectionMasterWindow').show({
											Filter_Lpu_Nick: org_store.getAt(0).get('Org_Nick'),
											LpuSectionProfile_id: directionParams.LpuSectionProfile_did || null,
											LpuUnitType_id: directionParams.LpuUnitType_did || null,
											userMedStaffFact: params.userMedStaffFact,
											personData: params,
											dirTypeCodeIncList: ['1','2','4','5','6'],
											directionData: directionParams,
											onHide: params.callback
										});
									} else {
										showSysMsg(langs('Не удалось получить данные организации с Org_id = ') + directionParams.Org_did);
										params.callback();
									}
								}
							});
						}
					} else {
						showSysMsg(langs('При получении данных для проверок произошла ошибка! Неправильный ответ сервера.'));
						params.callback();
					}
				},
				url: '/?c=EvnDirection&m=loadEvnDirectionList'
			});
		} else {
			params.callback();
		}
	},
	/**
	 *  Создание меню исходов госпитализации
	 */
	createListLeaveTypeMenu: function(config) {
		var menu = sw.Promed.Leave.getMenu({
			filterLeaveTypeList: function(store) {
				store.clearFilter();

				var
					LpuUnitType_SysNick = config.LpuUnitType_SysNick,
					begDate = config.begDate,
					endDate = config.endDate,
					topCode;

				store.filterBy(function (rec) {
					var flag = true;

					if (!Ext.isEmpty(rec.get('LeaveType_begDate')) && !Ext.isEmpty(endDate) && rec.get('LeaveType_begDate') > endDate) {
						return false;
					}
					if (!Ext.isEmpty(rec.get('LeaveType_endDate')) && !Ext.isEmpty(begDate) && rec.get('LeaveType_endDate') < begDate) {
						return false;
					}

					if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'krasnoyarsk', 'penza', 'pskov', 'vologda', 'krym', 'adygeya' ]) ) {
						if (!Ext.isEmpty(LpuUnitType_SysNick)) {
							if (LpuUnitType_SysNick == 'stac') {
								if ( getRegionNick() == 'buryatiya' ) {
									topCode = 106;
								}
								else if ( getRegionNick() == 'krym' ) {
									topCode = 110;
								}
								else {
									topCode = 199;
								}

								if (!(rec.get('LeaveType_Code') >= 101 && rec.get('LeaveType_Code') <= topCode)) {
									flag = false;
								}
							} else {
								if ( getRegionNick() == 'buryatiya' ) {
									topCode = 206;
								}
								else if ( getRegionNick() == 'krym' ) {
									topCode = 208;
								}
								else {
									topCode = 299;
								}

								if (!(rec.get('LeaveType_Code') >= 201 && rec.get('LeaveType_Code') <= topCode
									&& !(!getRegionNick().inlist([ 'adygeya' ]) && LpuUnitType_SysNick.inlist([ 'stac', 'hstac' ]) && rec.get('LeaveType_Code').toString().inlist([ '207', '208' ]))
								)) {
									flag = false;
								}
							}
						} else {
							if (!(rec.get('LeaveType_Code') >= 101 && rec.get('LeaveType_Code') <= 299)) {
								flag = false;
							}
						}
					} else if (getRegionNick().inlist([ 'kareliya', 'krasnoyarsk', 'msk', 'yaroslavl' ])) {
						if ( !Ext.isEmpty(LpuUnitType_SysNick) ) {
							if (LpuUnitType_SysNick == 'stac') {
								if (!(rec.get('LeaveType_Code') >= 101 && rec.get('LeaveType_Code') <= 199
									&& !(rec.get('LeaveType_Code').toString().inlist([ '111', '112', '113', '114', '115' ]))
								)) {
									flag = false;
								}
							} else {
								if (!(rec.get('LeaveType_Code') >= 201 && rec.get('LeaveType_Code') <= 299
									&& !(rec.get('LeaveType_Code').toString().inlist([ '210', '211', '212', '213', '215' ]))
								)) {
									flag = false;
								}
							}
						}
						else {
							if (!(rec.get('LeaveType_Code') >= 101 && rec.get('LeaveType_Code') <= 299
								&& !(rec.get('LeaveType_Code').toString().inlist([ '111', '112', '113', '114', '115', '210', '211', '212', '213', '215' ]))
							)) {
								flag = false;
							}
						}
					}
					return flag;
				});
			},
			ownerWindow: config.ownerWindow,
			id: config.id,
			getParams: config.getParams,
			onHideEditWindow: Ext.emptyFn,
			callbackEditWindow: config.callbackEditWindow,
			onCreate: config.onCreate
		});
	},
	filterFedResultDeseaseType:function(cfg){
		 log('filterFedResultDeseaseType', cfg);
		if (!cfg.fieldFedLeaveType||!cfg.fieldFedResultDeseaseType) {
			return true;
		}
		var Result_arr = [];
		
			
		var LeaveType_id =cfg.fieldFedLeaveType.getValue();
		if(LeaveType_id){
			swResultDeseaseLeaveTypeGlobalStore.each(function(rec){
				if(rec.get('LeaveType_id')==LeaveType_id){
					Result_arr.push(rec.get('ResultDeseaseType_id'))
				}
			})
		}
		
		if(Result_arr.length>0){
			log(cfg.fieldFedResultDeseaseType.getStore())
			cfg.fieldFedResultDeseaseType.getStore().filterBy(function(rec){return (rec.get('ResultDeseaseType_id').inlist(Result_arr))})
		
		}else{
			var LpuUnitType_SysNick = cfg.LpuUnitType_SysNick;
			var stac = 1;
			if(LpuUnitType_SysNick!='stac'){
				stac=2
			}
			var reg = new RegExp('^('+stac+')');
			cfg.fieldFedResultDeseaseType.getStore().filterBy(function(rec){return (reg.test(rec.get('ResultDeseaseType_Code')))})
		}
		var index = cfg.fieldFedResultDeseaseType.getStore().findBy(function(rec, id) {
			return (rec.get('ResultDeseaseType_id') == cfg.fieldFedResultDeseaseType.getValue());
		});

		if ( index < 0 ) {
			cfg.fieldFedResultDeseaseType.setValue('');
		}
	},
	filterFedLeaveType:function(cfg){
		 log('filterFedLeaveType', cfg);
		if (!cfg.fieldFedLeaveType||!cfg.fieldFedResultDeseaseType) {
			return true;
		}
		var Result_arr = [];
		
			
		var ResultDeseaseType_id =cfg.fieldFedResultDeseaseType.getValue();
		if(ResultDeseaseType_id){
			swResultDeseaseLeaveTypeGlobalStore.each(function(rec){
				if(rec.get('ResultDeseaseType_id')==ResultDeseaseType_id){
					Result_arr.push(rec.get('LeaveType_id'))
				}
			})
		}

		var fedIdList = new Array();
		if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'penza', 'pskov', 'vologda' ]) ) {
			// Получаем список доступных исходов из федерального справочника
			cfg.fieldLeaveType.getStore().each(function(rec) {
				if ( !Ext.isEmpty(rec.get('LeaveType_fedid')) && !rec.get('LeaveType_fedid').toString().inlist(fedIdList) ) {
					fedIdList.push(rec.get('LeaveType_fedid').toString());
				}
			});
		}
		
		if(Result_arr.length>0){
			cfg.fieldFedLeaveType.getStore().filterBy(function(rec){return (rec.get('LeaveType_id').inlist(Result_arr))})
			
		}else{
			var LpuUnitType_SysNick = cfg.LpuUnitType_SysNick;
			var stac = 1;
			if(LpuUnitType_SysNick!='stac'){
				stac=2
			}

			if ( getRegionNick().inlist([ 'buryatiya', 'penza', 'pskov', 'vologda' ]) ) {
				cfg.fieldFedLeaveType.getStore().filterBy(function (rec) {
					return ((stac == 1 && rec.get('LeaveType_USLOV') == stac && rec.get('LeaveType_Code') <= 110 && rec.get('LeaveType_id').toString().inlist(fedIdList)) || (stac == 2 && rec.get('LeaveType_USLOV') == stac && rec.get('LeaveType_Code') <= 208 && rec.get('LeaveType_id').toString().inlist(fedIdList)))
				});
			} else {
				cfg.fieldFedLeaveType.getStore().filterBy(function (rec) {
					return ((stac == 1 && rec.get('LeaveType_USLOV') == stac && rec.get('LeaveType_Code') <= 110) || (stac == 2 && rec.get('LeaveType_USLOV') == stac && rec.get('LeaveType_Code') <= 208))
				});
			}
		}
		var index = cfg.fieldFedLeaveType.getStore().findBy(function(rec, id) {
			return (rec.get('LeaveType_id') == cfg.fieldFedLeaveType.getValue());
		});
		

		if ( index < 0 ) {
			cfg.fieldFedLeaveType.setValue('');
		}
	},
	checkBeforeLeave: function(cmp, callback, EvnPS_id, EvnSection_id, LpuSection_id, MedPersonal_id, MedStaffFact_id, EvnSection_setDT, UslugaComplex_id, HTMedicalCareClass_id, childPS, EvnSection_IsZNO) {
		var self = this,
			loadMask = new Ext.LoadMask(cmp.getEl(), {msg: "Проверка при попытке добавить исход госпитализации..."});
		loadMask.show();

		var params = {
			isFromForm: LpuSection_id ? 1 : 0,
			EvnSection_pid: EvnPS_id,
			EvnSection_id: EvnSection_id || null,
			LpuSection_id: LpuSection_id || null,
			MedPersonal_id: MedPersonal_id || null,
			MedStaffFact_id: MedStaffFact_id || null,
			UslugaComplex_id: UslugaComplex_id || null,
			HTMedicalCareClass_id: HTMedicalCareClass_id || null,
			EvnSection_setDate: Ext.isDate(EvnSection_setDT) ? Ext.util.Format.date(EvnSection_setDT, 'd.m.Y') : null,
			EvnSection_setTime: Ext.isDate(EvnSection_setDT) ? Ext.util.Format.date(EvnSection_setDT, 'H:i') : null,
			childPS: childPS
		};
		if(EvnSection_IsZNO) {
			params.EvnSection_IsZNO = EvnSection_IsZNO;
		}

		Ext.Ajax.request({
			failure: function(response, options) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), langs('При проверке при попытке добавить исход госпитализации'));
				callback.call(cmp, false);
			},
			params: params,
			success: function(response, options) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( response_obj.success == false ) {
					sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При проверке при попытке добавить исход госпитализации'));
					callback.call(cmp, false);
				} else {
					callback.call(cmp, true);
				}
			},
			url: '/?c=EvnPS&m=checkBeforeLeave'
		});
	},
	
	//BOB - 14.06.2018	
	checkBeforeLeaveByReanimat: function(cmp, callback, EvnPS_id, EvnSection_id, Person_id, LeaveType_id) {
		var self = this,
			loadMask = new Ext.LoadMask(cmp.getEl(), {msg: "Проверка при попытке добавить исход госпитализации..."});
		loadMask.show();
		Ext.Ajax.request({
			failure: function(response, options) {
				loadMask.hide();
				sw.swMsg.alert(langs('Ошибка'), langs('При проверке при попытке добавить исход госпитализации'));
				callback.call(cmp, false);
			},
			params: {
				EvnPS_id: EvnPS_id,
				EvnSection_id: EvnSection_id,
				Person_id: Person_id,
				LeaveType_id: LeaveType_id
			},
			success: function(response, options) {
				loadMask.hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);
				console.log('BOB_response_obj=',response_obj);  //BOB - 14.06.2018
				if ( response_obj.success == false ) {
					sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('При проверке Реанимационного периода при попытке добавить исход госпитализации'));
					callback.call(cmp, false);
				} else {
					callback.call(cmp, true);
				}
			},
			url: '/?c=EvnReanimatPeriod&m=checkBeforeLeave'
		});
	},
	//BOB - 14.06.2018

    checkKardioPrivilegeConsent: function(cmp, callback, EvnPS_id, Person_id, LeaveType_id) {
        if (getRegionNick() == 'perm' && LeaveType_id.inlist([1,2]) && !Ext.isEmpty(Person_id) && !Ext.isEmpty(EvnPS_id)) { //окно получения согласия редназначено только для Перми
            Ext.Ajax.request({
                url: '/?c=Privilege&m=getKardioPrivilegeConsentData',
                params: {
                    EvnPS_id: EvnPS_id
                },
                callback:function (options, success, response) {
                    if (success) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (response_obj.need_consent && response_obj.need_consent == '1') {
                            getWnd('swPrivilegeConsentEditWindow').show({
                                Person_id: Person_id,
                                Evn_id: EvnPS_id,
								EvnPS_disDate: response_obj.EvnPS_disDate,
								is_stac: true,
								action: 'add',
								onHide: function() {
                                    callback.call(cmp, true);
								}
                            });
                        } else {
							callback.call(cmp, true);
						}
                    } else {
                        callback.call(cmp, true);
					}
                }
            });
        } else {
            callback.call(cmp, true);
		}
    },
	
	/**
	 * Расчет значения поля Фед. результат
	 * @param cfg
	 */
	calcFedLeaveType: function(cfg) {
		//log('calcFedLeaveType', cfg);
		if (!cfg.fieldFedLeaveType) {
			return true;
		}
		if (false == getRegionNick().inlist([ 'perm' ])
			|| !cfg.date
			|| Ext.util.Format.date(cfg.date, 'Y-m-d') < '2015-01-01'
			|| !cfg.LpuUnitType_SysNick
			|| !cfg.LeaveType_SysNick
		) {
			cfg.fieldFedLeaveType.clearValue();
			cfg.fieldFedLeaveType.hideContainer();
			cfg.fieldFedLeaveType.setAllowBlank(true);
			return true;
		}
		cfg.fieldFedLeaveType.showContainer();
		cfg.fieldFedLeaveType.setAllowBlank(false);
		if (cfg.noSetField) {
			return true;
		}
		var FedLeaveType_Code;
		if (!cfg.LeaveCause_Code) {
			cfg.LeaveCause_Code = '';
		}
		var LeaveCause_Code = cfg.LeaveCause_Code.toString();
		switch ( true ) {
			case ('leave' == cfg.LeaveType_SysNick && false == LeaveCause_Code.inlist(['5','6','7']) && 'stac' == cfg.LpuUnitType_SysNick)://LeaveType_Code=1
				FedLeaveType_Code = '101'; // Выписан
				break;
			case ('other' == cfg.LeaveType_SysNick && 'stac' == cfg.LpuUnitType_SysNick)://LeaveType_Code=2
				FedLeaveType_Code = '102'; // Переведён в др. ЛПУ
				break;
			case ('stac' == cfg.LeaveType_SysNick && 'stac' == cfg.LpuUnitType_SysNick)://LeaveType_Code=4
				FedLeaveType_Code = '103'; // Переведён в дневной стационар
				break;
			case ('section' == cfg.LeaveType_SysNick && 'stac' == cfg.LpuUnitType_SysNick)://LeaveType_Code=5
				FedLeaveType_Code = '104'; // Переведён на другой профиль коек
				break;
			case ('die' == cfg.LeaveType_SysNick && 'stac' == cfg.LpuUnitType_SysNick)://LeaveType_Code=3
				FedLeaveType_Code = '105'; // Умер
				break;
			case ('leave' == cfg.LeaveType_SysNick && LeaveCause_Code.inlist(['6']) && 'stac' == cfg.LpuUnitType_SysNick)://LeaveType_Code=1
				FedLeaveType_Code = '107'; // Лечение прервано по инициативе пациента
				break;
			case ('leave' == cfg.LeaveType_SysNick && LeaveCause_Code.inlist(['7']) && 'stac' == cfg.LpuUnitType_SysNick)://LeaveType_Code=1
				FedLeaveType_Code = '108'; // Лечение прервано по инициативе ЛПУ
				break;
			case ('leave' == cfg.LeaveType_SysNick && LeaveCause_Code.inlist(['5']) && 'stac' == cfg.LpuUnitType_SysNick)://LeaveType_Code=1
				FedLeaveType_Code = '110'; // Самовольно прерванное лечение
				break;
			case ('leave' == cfg.LeaveType_SysNick && !LeaveCause_Code.inlist(['5','6','7']) && 'stac' != cfg.LpuUnitType_SysNick)://LeaveType_Code=1
				FedLeaveType_Code = '201'; // Выписан
				break;
			case ('other' == cfg.LeaveType_SysNick && 'stac' != cfg.LpuUnitType_SysNick)://LeaveType_Code=2
				FedLeaveType_Code = '202'; // Переведён в др. ЛПУ
				break;
			case ('stac' == cfg.LeaveType_SysNick && 'stac' != cfg.LpuUnitType_SysNick)://LeaveType_Code=4
				FedLeaveType_Code = '203'; // Переведён в стационар
				break;
			case ('section' == cfg.LeaveType_SysNick && 'stac' != cfg.LpuUnitType_SysNick)://LeaveType_Code=5
				FedLeaveType_Code = '204'; // Переведён на другой профиль коек
				break;
			case ('die' == cfg.LeaveType_SysNick && 'stac' != cfg.LpuUnitType_SysNick)://LeaveType_Code=3
				FedLeaveType_Code = '205'; // Умер
				break;
			case ('leave' == cfg.LeaveType_SysNick && LeaveCause_Code.inlist(['5','6']) && 'stac' != cfg.LpuUnitType_SysNick)://LeaveType_Code=1
				FedLeaveType_Code = '207'; // Лечение прервано по инициативе пациента
				break;
			case ('leave' == cfg.LeaveType_SysNick && LeaveCause_Code.inlist(['7']) && 'stac' != cfg.LpuUnitType_SysNick)://LeaveType_Code=1
				FedLeaveType_Code = '208'; // Лечение прервано по инициативе ЛПУ
				break;
			default:
				FedLeaveType_Code = null;
				break;
		}
		var index = cfg.fieldFedLeaveType.getStore().findBy(function(rec){return (rec.get('LeaveType_Code')==FedLeaveType_Code)})
		if(index<0){
			var FedResultDeseaseType_Code = (cfg.LpuUnitType_SysNick=='stac')?'103':'203';
			if(FedLeaveType_Code=='105'){
				FedResultDeseaseType_Code = (cfg.LpuUnitType_SysNick=='stac')?'104':'204';
			}
			cfg.fieldFedResultDeseaseType.setFieldValue('ResultDeseaseType_Code', FedResultDeseaseType_Code);
		}
		if (!FedLeaveType_Code) {
			return false;
		}
		cfg.fieldFedLeaveType.setFieldValue('LeaveType_Code', FedLeaveType_Code);
		return true;
	},
	/**
	 * Расчет значения поля Фед. исход
	 * @param cfg
	 */
	calcFedResultDeseaseType: function(cfg) {
		if (!cfg.fieldFedResultDeseaseType) {
			return true;
		}
		if (false == getRegionNick().inlist([ 'perm' ])
			|| !cfg.date
			|| Ext.util.Format.date(cfg.date, 'Y-m-d') < '2015-01-01'
			|| !cfg.LpuUnitType_SysNick
			|| !cfg.LeaveType_SysNick
		) {
			cfg.fieldFedResultDeseaseType.clearValue();
			cfg.fieldFedResultDeseaseType.hideContainer();
			cfg.fieldFedResultDeseaseType.setAllowBlank(true);
			return true;
		}
		cfg.fieldFedResultDeseaseType.showContainer();
		cfg.fieldFedResultDeseaseType.setAllowBlank(false);

		if (cfg.noSetField) {
			// если устанавливать значение не нужно
			return true;
		}

		if (!cfg.ResultDesease_Code) {
			cfg.ResultDesease_Code = '0';
		}
		var FedResultDeseaseType_Code;
		var ResultDesease_Code = cfg.ResultDesease_Code.toString();
		switch ( true ) {
			case (ResultDesease_Code.inlist(['1','2','3','4']) && 'stac' == cfg.LpuUnitType_SysNick):
				FedResultDeseaseType_Code = '101';
				break;
			case (ResultDesease_Code.inlist(['5','6','7','8']) && 'stac' == cfg.LpuUnitType_SysNick):
				FedResultDeseaseType_Code = '102';
				break;
			case ('die' == cfg.LeaveType_SysNick && 'stac' == cfg.LpuUnitType_SysNick):
			case (ResultDesease_Code.inlist(['9','10','12','13']) && 'stac' == cfg.LpuUnitType_SysNick):
				FedResultDeseaseType_Code = '104';
				break;
			case (ResultDesease_Code.inlist(['11']) && 'stac' == cfg.LpuUnitType_SysNick):
				FedResultDeseaseType_Code = '103';
				break;
			case (ResultDesease_Code.inlist(['1','2','3','4']) && 'stac' != cfg.LpuUnitType_SysNick):
				FedResultDeseaseType_Code = '201';
				break;
			case (ResultDesease_Code.inlist(['5','6','7','8']) && 'stac' != cfg.LpuUnitType_SysNick):
				FedResultDeseaseType_Code = '202';
				break;
			case ('die' == cfg.LeaveType_SysNick && 'stac' != cfg.LpuUnitType_SysNick):
			case (ResultDesease_Code.inlist(['9','10','12','13']) && 'stac' != cfg.LpuUnitType_SysNick):
				FedResultDeseaseType_Code = '204';
				break;
			case (ResultDesease_Code.inlist(['11']) && 'stac' != cfg.LpuUnitType_SysNick):
				FedResultDeseaseType_Code = '203';
				break;
			default:
				FedResultDeseaseType_Code = (cfg.LpuUnitType_SysNick=='stac')?'103':'203';
				break;
		}
		var index = cfg.fieldFedResultDeseaseType.getStore().findBy(function(rec){return (rec.get('ResultDeseaseType_Code')==FedResultDeseaseType_Code)})
		if(index<0){
			FedResultDeseaseType_Code = (cfg.LpuUnitType_SysNick=='stac')?'103':'203';
		}
		if (!FedResultDeseaseType_Code) {
			return false;
		}
		cfg.fieldFedResultDeseaseType.setFieldValue('ResultDeseaseType_Code', FedResultDeseaseType_Code);
		return true;
	},
	isUseLpuSectionBedProfile: function() {
		return getRegionNick().inlist([ 'kareliya', 'astra' ]);
	}
};
/**
 * Методы для работы с порталом РПН (KZ)
 */
sw.Promed.serviceKZRPN = {
	// Получаем данные по человеку и выполняем сохранение в БД если необходимо
	getPersonCardList: function (wnd, params) {
		wnd.getLoadMask(langs('Получение данных из сервиса РПН. Пожалуйста, подождите...')).show();
		Ext.Ajax.request({
			url: '/?c=ServiceRPN&m=getPersonCardList',
			params: params,
			callback: function(options, success, response) {
				wnd.getLoadMask().hide();
				var answer = false;
				if (success) {
					answer = Ext.util.JSON.decode(response.responseText);
					if(answer.success) {
						showSysMsg((answer.Message)?answer.Message:langs('Данные о прикреплении успешно получены'),langs('Сообщение'));
					} else {
						showSysMsg((answer.Error_Msg)?answer.Error_Msg:langs('Ошибка получения данных с портала РПН'),langs('Ошибка'), 'error');
					}
				} else {
					showSysMsg(langs('Ошибка получения данных с портала РПН'),langs('Ошибка'), 'error');
				}
				if ( typeof params.callback == 'function' ) {
					params.callback(answer);
				}
			}
		});
	}, 
	// Получаем данные по участку и выполняем сохранение в БД если необходимо
	getLpuRegionList: function (wnd, params) {
		wnd.getLoadMask(langs('Получение данных из сервиса РПН. Пожалуйста, подождите...')).show();
		Ext.Ajax.request({
			url: '/?c=ServiceRPN&m=getLpuRegionList',
			params: params,
			callback: function(options, success, response) {
				wnd.getLoadMask().hide();
				var answer = false;
				if (success) {
					answer = Ext.util.JSON.decode(response.responseText);
					if(answer.success) {
						showSysMsg((answer.Message)?answer.Message:langs('Данные об участках успешно получены'),langs('Сообщение'));
					} else {
						showSysMsg((answer.Error_Msg)?answer.Error_Msg:langs('Ошибка получения данных с портала РПН'),langs('Ошибка'), 'error');
					}
				} else {
					showSysMsg(langs('Ошибка получения данных с портала РПН'),langs('Ошибка'), 'error');
				}
				if ( typeof params.callback == 'function' ) {
					params.callback(answer);
				}
			}
		});
	}
}

// объект для хранения выбранных двойников, для переноса их между формами
sw.Promed.personDoublesCache = {
	records: {},
	cacheEnabled: false,
	getAllRecords: function (type) {
		var answer = {};
		for (var key in this.records) {
			if (this.records[key].type == type || type === undefined) {
				answer[key] = this.records[key].record;
			}
		}
		return answer;
	},
	getPersonIds: function(type) {
		var Person_ids = [];
		for (var key in this.records) {
			if (this.records[key].type == type || type === undefined) {
				Person_ids.push(key);
			}
		}
		return Person_ids;
	},
	getRecord: function(Person_id) {
		return this.records[Person_id].record;
	},
	setCacheEnable: function(val) {
		if (val !== false) { 
			this.cacheEnabled = true;
		} else {
			this.cacheEnabled = false;
		}
	},
	setMainRecord: function (Person_id) {
		if (this.records[Person_id]) {
			for (var key in this.records) {
				this.records[key].record.IsMainRec = false;
			}
			this.records[Person_id].record.IsMainRec = true;
		}
	},
	// получаем Person_id моделей отличных от заданной
	getIdsOtherModels: function (type) {
		var answer = [];
		for (var key in this.records) {
			if (this.records[key].type != type) {
				answer.push(key);
			}
		}
		return answer;
	},
	addRecord: function(record, type) {
		if (this.cacheEnabled) {
			// если добавляем главную запись, то очищаем признак главной записи у всех остальных записей
			if (record.get('IsMainRec')) {
				for (var key in this.records) {
					this.records[key].IsMainRec = false;
				}
			}
			this.records[record.get('Person_id')] = {record: record, type: type};
		}
	},
	removeRecord: function(record) {
		if (this.cacheEnabled) {
			delete this.records[record.get('Person_id')];
		}
	},
	resetCache: function() {
		this.records = {};
		this.cacheEnabled = false;
	}
};
