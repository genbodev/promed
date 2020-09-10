/**
* АРМ администратора платных услуг
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/
sw.Promed.swPaidServiceWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	id: 'swPaidServiceWorkPlaceWindow',
	fixPersonUnknown: function(Person_id, Server_id) {
		var win = this;

		// открывается форма «Человек: Поиск» для поиска, выбора или создания нового человека
		getWnd('swPersonSearchWindow').show({
			onClose: function() {},
			onSelect: function(person_data) {
				getWnd('swPersonSearchWindow').hide();

				if (person_data.Person_id) {
					// Обновляем данные в талоне и удаляем неизвестного
					win.getLoadMask('Обновление данных человека в талоне...').show();
					Ext.Ajax.request({
						url: '/?c=PaidService&m=fixPersonUnknown',
						params: {
							Person_oldId: Person_id,
							Person_newId: person_data.Person_id
						},
						callback: function() {
							win.getLoadMask().hide();

							var params = {
								noLostFocus: true
							};

							win.doSearch(params);
						}
					});
				}
			}
		});
	},
	checkElectronicQueueInfoEnabled: function() {
		// проверка включена ли электронная очередь
		var win = this;
		win.ElectronicQueueInfo_IsOff = false;
		win.getLoadMask('Проверка активности электронной очереди...').show();
		Ext.Ajax.request({
			url: '/?c=PaidService&m=checkElectronicQueueInfoEnabled',
			params: {
				ElectronicService_id: win.ElectronicService_id
			},
			callback: function (options, success, response) {
				win.getLoadMask().hide();

				if (success) {
					if ( response.responseText.length > 0 ) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success && result.ElectronicQueueInfo_IsOff) {
							win.ElectronicQueueInfo_id = parseFloat(result.ElectronicQueueInfo_id);
							win.ElectronicQueueInfo_IsOff = parseInt(result.ElectronicQueueInfo_IsOff);
							win.doSearch();
						}
					}
				}
			}
		});
	},
	filterRecords: function() {
		var win = this;

		var TimetableMedService_id = null;
		var selectedVisible = false;
		var count = 0;
		var grid = win.WorkPlaceGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (record && record.get('TimetableMedService_id')) {
			TimetableMedService_id = record.get('TimetableMedService_id');
		}

		if (win.WorkPlaceGrid.showOnlyActive) {
			grid.getStore().filterBy(function(record) {
				if (win.checkRecordActive(record)) {
					count++;

					if (record.get('TimetableMedService_id') && record.get('TimetableMedService_id') == TimetableMedService_id) {
						selectedVisible = true;
					}
					return true;
				} else {
					return false;
				}
			});
		} else {
			grid.getStore().filterBy(function(record) {
				count++;

				if (record.get('TimetableMedService_id') && record.get('TimetableMedService_id') == TimetableMedService_id) {
					selectedVisible = true;
				}
				return true;
			});
		}

		if (count > 0) {
			// если фокус оказался на скрытой записи, то ставим на первую
			if (!selectedVisible) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		} else {
			grid.getSelectionModel().clearSelections();
			win.WorkPlaceGrid.clearTopTextCounter();
		}

		win.onRowSelect();
	},
	show: function()
	{
		sw.Promed.swPaidServiceWorkPlaceWindow.superclass.show.apply(this, arguments);

		var win = this;

		if(arguments[0].userMedStaffFact){ this.userMedStaffFact = arguments[0].userMedStaffFact; }
		else { this.userMedStaffFact = arguments[0]; }

		this.ElectronicService_id = null;
		if (this.userMedStaffFact.ElectronicService_id) {
			this.ElectronicService_id = this.userMedStaffFact.ElectronicService_id
		}

		if ( arguments[0].MedService_id && arguments[0].UslugaComplexMedService_id ) {
			this.MedService_id = arguments[0].MedService_id;
			this.UslugaComplexMedService_id = arguments[0].UslugaComplexMedService_id;

			if (arguments[0].MedServiceType_SysNick)
				this.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick;

		} else {
			// Не понятно, что за АРМ открывается
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function () {
				this.hide();
			}.createDelegate(this));
			return false;
		}

		// Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера
		sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);

		this.WorkPlaceGrid.addActions({
			handler: function() {
				win.doCall();
			},
			iconCls: 'ic_ivite16',
			name: 'do_call',
			text: 'Вызвать',
			tooltip: 'Вызвать'
		}, 0);

		this.WorkPlaceGrid.addActions({
			handler: function() {
				win.applyCall();
			},
			iconCls: 'ic_take16',
			name: 'apply_call',
			text: 'Принять',
			tooltip: 'Принять'
		}, 1);

		this.WorkPlaceGrid.addActions({
			handler: function() {
				win.cancelCall();
			},
			iconCls: 'ic_ivite_cancel16',
			name: 'cancel_call',
			text: 'Отменить вызов',
			tooltip: 'Отменить вызов'
		}, 2);

		this.WorkPlaceGrid.addActions({
			handler: function() {
				win.noPatient();
			},
			iconCls: 'ic_ivite_stop16',
			name: 'no_patient',
			text: 'Пациент не явился',
			tooltip: 'Пациент не явился'
		}, 3);

		this.WorkPlaceGrid.addActions({
			handler: function() {
				win.doCancel();
			},
			iconCls: 'delete16',
			name: 'do_cancel',
			text: 'Отменить услугу',
			tooltip: 'Отменить услугу',
			hidden: !isUserGroup('DrivingCommissionReg')
		}, 4);

		this.WorkPlaceGrid.addActions({
			handler: function() {
				win.openEmk();
			},
			iconCls: 'open16',
			name: 'open_emk',
			text: 'Открыть ЭМК',
			tooltip: 'Открыть ЭМК'
		}, 5);

		this.WorkPlaceGrid.getGrid().getTopToolbar().addSeparator();
		this.WorkPlaceGrid.getGrid().getTopToolbar().add({
			boxLabel: 'Показать только доступные записи',
			checked: true,
			listeners: {
				'check': function(field, value) {
					if (value) {
						win.WorkPlaceGrid.showOnlyActive = true;
					} else {
						win.WorkPlaceGrid.showOnlyActive = false;
					}

					win.filterRecords();
				}
			},
			xtype: 'checkbox'
		});

		win.dateFilter.setValue(getGlobalOptions().date);

		win.ElectronicQueueInfo_id = null;
		win.checkElectronicQueueInfoEnabled();
		win.initNodeListeners();

		// Автоматическое обновление грида
		if(!this.refreshInterval){

			this.refreshInterval = setInterval(function(){

				var activeWin = getActiveWin();

				if (win.id == activeWin.id) {

					if (win.socket){

						if (!win.socket.connected){
							win.doSearch({
								disablePreloader: true,
								noLostFocus: true
							});
						}
					}
					else{
						win.doSearch({
							disablePreloader: true,
							noLostFocus: true
						});
					}
				}
			}.bind(this),15000);
		}
	},
	initNodeListeners: function () {

		var win = this;
		var opts = getGlobalOptions();

		if (!opts || !opts.nodePortalConnectionHost ) {
			log('No socket connection host');
			return false;
		}

		if (!win.socket) {

			var socketData = {
				Lpu_id: this.userMedStaffFact.Lpu_id,
				MedService_id: this.userMedStaffFact.MedService_id,
				MedPersonal_id: this.userMedStaffFact.MedPersonal_id,
				MedPersonal_FIO: this.userMedStaffFact.MedPersonal_FIO,
				ElectronicService_id: this.userMedStaffFact.ElectronicService_id,
				ElectronicQueueInfo_id: this.userMedStaffFact.ElectronicQueueInfo_id,
			};

			win.socket = io(opts.nodePortalConnectionHost, { query: socketData});
			win.socket.on('connect', function () {

				log('connect');

				win.socket.on('error', function (nodeResponse) {
					log('node-error', nodeResponse);
				});

				win.socket.on('message', function (nodeResponse) {

					log('message', nodeResponse);

					if (nodeResponse.message) {
						switch(nodeResponse.message) {

							case 'electronicQueueDisabled':

								if (nodeResponse.ElectronicQueueInfo_id && parseFloat(nodeResponse.ElectronicQueueInfo_id) == win.ElectronicQueueInfo_id) {
									win.checkElectronicQueueInfoEnabled();
								}

								break;

							// создан новый талон
							case 'electronicTalonCreated':

								var params = {
									noLostFocus: true
								};

								win.doSearch(params);
								break;

							// статус талона изменен
							case 'electronicTalonStatusHasChanged':

								// просто ищем в гриде и обновляем статус
								var index = win.WorkPlaceGrid.getGrid().getStore().findBy(function(rec) {
									return (rec.get('ElectronicTalon_id') == parseFloat(nodeResponse.ElectronicTalon_id));
								});

								if (index >= 0) {
									var record = win.WorkPlaceGrid.getGrid().getStore().getAt(index);
									record.set('ElectronicTalonStatus_id', parseFloat(nodeResponse.ElectronicTalonStatus_id));
									record.set('ElectronicTalonStatus_Name', nodeResponse.ElectronicTalonStatus_Name);
									record.commit();

									win.onRowSelect();
								}
								break;
						}
					}
				});
			});
		}
	},
	// АЯКС запрос через ПРАМИС, ибо так мы можем получить результат и выполнить потом что-то
	sendAjaxRequestPromise: function(url, ajax_params) {

		return new Promise(function(resolve, reject) {

			Ext.Ajax.request({

				params: ajax_params,
				url: url,
				success: function(response) {resolve(JSON.parse(response.responseText))},
				failure: function(response) {reject(response)}
			})
		})
	},
	checkIsDigitalServiceBusy: function(record, dsAction, callbackFn) {

		var win = this;
		win.getLoadMask('Проверка на текущее обслуживание').show();

		// проверяем кабинет на доступность
		Ext.Ajax.request({
			url: '/?c=PaidService&m=checkIsDigitalServiceBusy',
			params: {
				ElectronicService_id: win.ElectronicService_id,
				DigitalServiceAction: dsAction
			},
			callback: function (opt, success, response) {

				win.getLoadMask().hide();
				var serviceChecking = JSON.parse(response.responseText);

				if (serviceChecking.success) {

					// если есть сообщение проверки
					if (serviceChecking.Check_Msg) {

						var buttons = {
							yes: "Завершить обслуживание",
							cancel: "Отмена"
						};

						if (isUserGroup('DrivingCommissionReg')) {
							buttons.no = 'Отменить услугу';
						}

						sw.swMsg.show({
							icon: Ext.MessageBox.WARNING,
							buttons: buttons,
							title: lang['preduprejdenie'],
							msg: serviceChecking.Check_Msg,

							fn: function (buttonId) {

								if (buttonId == 'yes') {

									if (serviceChecking.data.ElectronicTalon_id) {

										var openEmkParams = {
											completeDigitalTicket: true,
											//что мы сделаем по закрытии ЭМК
											callbackFn: callbackFn
										};

										win.openEmk(serviceChecking.data.ElectronicTalon_id, openEmkParams);

									} else {
										showPopupWarningMsg('Невозможно завершить вызов, нет данных талона ЭО');
									}

								} else if (buttonId == 'no') {

									if (serviceChecking.data.ElectronicTalon_id) {
										// отменяем услугу пациента с талоном который сейчас на обслуживании
										win.doCancelTalon(serviceChecking.data.ElectronicTalon_id, callbackFn);
									} else {
										showPopupWarningMsg('Невозможно отменить услугу, нет данных талона ЭО');
									}
								} else if (buttonId == 'cancel') {
									// ничего
								}
							}
						});

					// проверка прошла, продолжаем
					} else {
						callbackFn.call();
					}
				}

			}
		});
	},
	doCall: function() {

		var win = this,
			grid = win.WorkPlaceGrid.getGrid(),
			record = grid.getSelectionModel().getSelected(),
			digitalServiceAction = 'call',

			// кэллбэк по завершении проверки
			callbackFn = function(){

				var url = '/?c=PaidService&m=checkIsDigitalServiceBusy',
					ajaxParams = {
						ElectronicService_id: win.ElectronicService_id,
					};

				// здесь лучше прамисом, чем кэлбэком
				// если мы закрыли карту ЭМК и нихрена не сделали с талоном
				win.sendAjaxRequestPromise(url, ajaxParams).then(function(ret) {

					if (ret.success && !ret.Check_Msg) {
						// если проверка повторная прошла, вызываем того на кого нажали
						win.doCallRequest(record.get('ElectronicTalon_id'))
					} else {
						log('Никого не вызываем, талон не переведен в другой кабинет');
						// иначе никого не вызываем
						return false;
					}
				});

			};

		if (!record || !record.get('ElectronicTalon_id')) {
			return false;
		}

		// проверяем наш кабинет статус на "на обслуживании"
		win.checkIsDigitalServiceBusy(record, digitalServiceAction, callbackFn);
	},
	doCallRequest: function(ElectronicTalon_id) {

		var win = this;
		win.getLoadMask('Вызов пациента').show();

		Ext.Ajax.request({
			url: '/?c=PaidService&m=setElectronicTalonStatus',
			params: {
				ElectronicTalon_id: ElectronicTalon_id,
				ElectronicTalonStatus_id: 2, // Изменяется текущий статус на «Вызван»
				ElectronicService_id: win.ElectronicService_id
			},
			callback: function (options, success, response) {
				win.getLoadMask().hide();

				var params = {
					noLostFocus: true,
					disableStoreRefresh : (win.socket && win.socket.connected)
				};

				win.doSearch(params);
			}
		});
	},
	cancelCall: function() {
		var win = this;
		var grid = this.WorkPlaceGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('ElectronicTalon_id') ) return false;

		win.getLoadMask('Отмена вызова').show();
		Ext.Ajax.request({
			url: '/?c=PaidService&m=setElectronicTalonStatus',
			params: {
				ElectronicTalon_id: record.get('ElectronicTalon_id'),
				ElectronicTalonStatus_id: 1, // Изменяется текущий статус на «Ожидает»
				ElectronicService_id: win.ElectronicService_id
			},
			callback: function (options, success, response) {
				win.getLoadMask().hide();

				var params = {
					noLostFocus: true,
					disableStoreRefresh : (win.socket && win.socket.connected)
				};

				win.doSearch(params);
			}
		});
	},
	noPatient: function() {
		var win = this;
		var grid = this.WorkPlaceGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('ElectronicTalon_id') ) return false;

		win.getLoadMask('Пациент не явился').show();
		Ext.Ajax.request({
			url: '/?c=PaidService&m=setNoPatientTalonStatus',
			params: {
				ElectronicTalon_id: record.get('ElectronicTalon_id')
			},
			callback: function (options, success, response) {
				win.getLoadMask().hide();

				var params = {
					noLostFocus: true,
				};

				win.doSearch(params);
			}
		});
	},
	checkIsUnknown: function (record) {
		var win = this;
		if (record.get('Person_IsUnknown') == 2) {
			showPopupWarningMsg('Человек неизвестный! Найдите человека в системе или создайте нового.');
			win.fixPersonUnknown(record.get('Person_id'), record.get('Server_id'));
			return false;
		}

		return true;
	},
	getEmkParams: function(dataParams, nodeObjectParams) {

		var wnd = this,
			searchNodeObj = {};

		if (nodeObjectParams.EvnPLDispDriver_id) {
			searchNodeObj = {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: 'EvnPLDispDriver',
				Evn_id: nodeObjectParams.EvnPLDispDriver_id
			}
		}

		if (nodeObjectParams.EvnPLDispTeenInspection_id) {
			searchNodeObj = {
				parentNodeId: 'root',
				last_child: false,
				disableLoadViewForm: false,
				EvnClass_SysNick: 'EvnPLDispTeenInspection',
				Evn_id: nodeObjectParams.EvnPLDispTeenInspection_id
			}
		}

		var emk_params = {
			Person_id: dataParams.CalledPersonData.Person_id,
			Server_id: dataParams.CalledPersonData.Server_id,
			PersonEvn_id: dataParams.CalledPersonData.PersonEvn_id,
			userMedStaffFact: wnd.userMedStaffFact,
			mode: 'workplace',
			callback: dataParams.callbackFn,
			searchNodeObj: searchNodeObj,
			ARMType: 'common'
		};

		if (dataParams.isDigitalTicketCompleteSelected) {
			emk_params.isDigitalTicketCompleteSelected = true;
		}

		console.warn('emk_params',emk_params);

		return emk_params;
	},
	applyCallRequest: function(options, params) {

		var win = this;
		if (win.MedServiceType_SysNick) {
			params.MedServiceType_SysNick = win.MedServiceType_SysNick;
		}

		//console.warn('options', options);
		win.getLoadMask('Прием пациента').show();

		Ext.Ajax.request({
			url: '/?c=PaidService&m=applyCall',
			params: params,
			callback: function (opt, success, response) {

				win.getLoadMask().hide();

				if (success) {
					if ( response.responseText.length > 0 ) {

						var result = Ext.util.JSON.decode(response.responseText);

						if (result.success && (result.EvnPLDispDriver_id || result.EvnPLDispTeenInspection_id)) {

							var dataParams = options,
								nodeObjectParams = result;

							var refreshParams = {
								noLostFocus: true,
								disableStoreRefresh : (win.socket && win.socket.connected)
							};

							dataParams.callbackFn = function() {
								win.doSearch(refreshParams);
							};

							getWnd('swPersonEmkWindow').show(win.getEmkParams(dataParams, nodeObjectParams));

						} else if (result.Error_Msg && result.Error_Msg == 'CheckRegister') {

							sw.swMsg.show({

								buttons: {
									yes: 'Все равно принять',
									no: 'Отменить услугу'
								},
								fn: function(buttonId, text, obj) {

									if ( buttonId == 'yes' ) {

										options.ignoreCheckRegister = 1;
										win.applyCall(options);

									} else if ( buttonId == 'no' ) {
										win.doCancelTalon(options.CalledPersonData.ElectronicTalon_id);
									}

								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: result.Alert_Msg,
								title: lang['preduprejdenie']
							});

						} else if (result.Error_Msg && result.Error_Msg == 'CheckAnotherElectronicTalon') {

							sw.swMsg.show({
								buttons: {yes: 'Принять другого пациента', no: 'Принять вызванного пациента', cancel: 'Отмена'},
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										options.ignoreCheckAnotherElectronicTalon = 1;
										win.applyCall(options);
									} else if ( buttonId == 'no' ) {
										options.ignoreCheckAnotherElectronicTalon = 1;
										options.CalledPersonData = result.CalledPersonData;
										win.applyCall(options);
									} else if ( buttonId == 'cancel' ) {
										// do nothing
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: result.Alert_Msg,
								title: lang['preduprejdenie']
							});

						}
					}
				}
			}
		});

	},
	applyCall: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var grid = this.WorkPlaceGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('ElectronicTalon_id')) return false;

		if (!win.checkIsUnknown(record)) {
			return false;
		}

		if (!options.CalledPersonData) {
			options.CalledPersonData = {
				ElectronicTalon_id: record.get('ElectronicTalon_id'),
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id')
			};
		}
		var applyParams = {
			ElectronicTalon_id: options.CalledPersonData.ElectronicTalon_id
		};

		if (options.ignoreCheckRegister) { applyParams.ignoreCheckRegister = options.ignoreCheckRegister; }

		if (options.ignoreCheckAnotherElectronicTalon) {
			applyParams.ignoreCheckAnotherElectronicTalon = options.ignoreCheckAnotherElectronicTalon;
		}

		if (options.withoutElectronicQueue) {
			applyParams.withoutElectronicQueue = options.withoutElectronicQueue;
		}

		var digitalServiceAction = 'call',

			// кэллбэк по завершении проверки
			callbackFn = function(){

				var url = '/?c=PaidService&m=checkIsDigitalServiceBusy',
					ajaxParams = {
						ElectronicService_id: win.ElectronicService_id
					};

				// здесь лучше прамисом, чем кэлбэком
				// если мы закрыли карту ЭМК и нихрена не сделали с талоном
				win.sendAjaxRequestPromise(url, ajaxParams).then(function(ret) {

					if (ret.success && !ret.Check_Msg) {
						// если проверка повторная прошла, вызываем того на кого нажали
						win.applyCallRequest(options, applyParams);
					} else {
						log('Никого не вызываем, талон не переведен в другой кабинет');
						// иначе никого не вызываем
						return false;
					}
				});
			};

		// проверяем наш кабинет статус на "на обслуживании"
		win.checkIsDigitalServiceBusy(record, digitalServiceAction, callbackFn);
	},
	openEmk: function(ElectronicTalon_id, openEmkParams) {

		var wnd = this,
			grid = wnd.WorkPlaceGrid.getGrid(),
			record;

		if (ElectronicTalon_id) {

			var index = grid.getStore().findBy( function(r) {
				if (r.get('ElectronicTalon_id') == ElectronicTalon_id) {
					return true;
				}
			});

			record = grid.getStore().getAt(index);
		} else {
			record = grid.getSelectionModel().getSelected();
		}

		if (
			!record || !record.get('Person_id')
			|| !wnd.checkIsUnknown(record)
		) { return false; }

		if (
			wnd.ElectronicQueueInfo_IsOff == 2 // Эл. очередь отключена
			&& record.get('IsCurrentDate') == 1 // Проверить в регистрах возможно только, если дата записи равна текущей дате
			&& isUserGroup('DrivingCommissionReg') // Для Пользователей с группой доступа «Регистратор платных услуг (электронная очередь) Водительская комиссия А,B»
		) {
			// вместо открытия ЭМК выполнять процедуру проверки в регистрах по наркологии и психиатрии
			wnd.applyCall({
				withoutElectronicQueue: 1
			});

		} else {

			var dataParams = {
					CalledPersonData: {
						Person_id: record.get('Person_id'),
						Server_id: record.get('Server_id'),
						PersonEvn_id: record.get('PersonEvn_id'),
					}
				},
				nodeObjectParams = {};

			if (record.get('EvnPLDispDriver_id')) {
				nodeObjectParams.EvnPLDispDriver_id = record.get('EvnPLDispDriver_id')
			}

			if (openEmkParams && openEmkParams.completeDigitalTicket) {
				dataParams.isDigitalTicketCompleteSelected = true;

				if (openEmkParams.callbackFn) {

					dataParams.callbackFn = openEmkParams.callbackFn;

				} else {

					dataParams.callbackFn = function() {
						var params = {
							noLostFocus: true,
						};

						wnd.doSearch(params);
					}
				}

			} else {

				// здесь поставим пустую функцию вдруг, кэллбэк не обнулится
				//dataParams.callbackFn = Ext.emptyFn;
				// или лучше поставим обновление грида, вдруг врач завершит прием
				dataParams.callbackFn = function() {

					var params = {
						noLostFocus: true,
					};

					wnd.doSearch(params);
				}
			}

			getWnd('swPersonEmkWindow').show(wnd.getEmkParams(dataParams, nodeObjectParams));
		}
	},
	doCancel: function() {
		var win = this;
		var grid = this.WorkPlaceGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('ElectronicTalon_id') ) return false;

		win.doCancelTalon(record.get('ElectronicTalon_id'));
	},
	doCancelTalon: function(ElectronicTalon_id, callBackFn) {
		var win = this;
		win.getLoadMask('Отмена услуги').show();
		Ext.Ajax.request({
			url: '/?c=PaidService&m=setElectronicTalonStatus',
			params: {
				ElectronicTalon_id: ElectronicTalon_id,
				ElectronicTalonStatus_id: 5, // статус «Отменен»
				ElectronicService_id: win.ElectronicService_id
			},
			callback: function (options, success, response) {
				win.getLoadMask().hide();

				if (callBackFn) {
					callBackFn.call();
				} else {
					var params = {
						noLostFocus: true,
						disableStoreRefresh : (win.socket && win.socket.connected)
					};

					win.doSearch(params);
				}
			}
		});
	},
    doSearch: function(options) {

		var win = this;

		if (typeof options != 'object') {
			options = new Object();
		}

		// если опция дизаблинга обновления стора включена
		if (options.disableStoreRefresh) {

			// выполняем только кэллбэк
			if (options.callback && typeof options.callback == 'function') {
				options.callback();
			}

			// дальше не идем
			return false;
		}

		this.WorkPlaceGrid.removeAll({
			clearAll: true
		});

		var params = {
			globalFilters: {
				UslugaComplexMedService_id: win.UslugaComplexMedService_id,
				onDate: win.dateFilter.getValue().format('d.m.Y'),
			}
		};

		if (options.disablePreloader) { params.disablePreloader = true; }

		var record = win.WorkPlaceGrid.getGrid().getSelectionModel().getSelected();
		if (options.noLostFocus && record && record.get('TimetableMedService_id')) {
			params.valueOnFocus = {
				TimetableMedService_id: record.get('TimetableMedService_id')
			};
		}

		this.WorkPlaceGrid.loadData(params);
    },
	layout: 'border',
	maximized: true,
	stepDay: function(day)
	{
		var win = this;
		var date1 = (win.dateFilter.getValue() || Date.parseDate(getGlobalOptions().date, 'd.m.Y')).add(Date.DAY, day).clearTime();
		win.dateFilter.setValue(Ext.util.Format.date(date1, 'd.m.Y'));
	},
	prevDay: function() {
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	onRowSelect: function() {
		var win = this;

		var grid = this.WorkPlaceGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();

		this.WorkPlaceGrid.setActionDisabled('do_call', true);
		this.WorkPlaceGrid.setActionDisabled('cancel_call', true);
		this.WorkPlaceGrid.setActionDisabled('no_patient', true);
		this.WorkPlaceGrid.setActionDisabled('apply_call', true);
		this.WorkPlaceGrid.setActionDisabled('open_emk', true);
		this.WorkPlaceGrid.setActionDisabled('do_cancel', true);

		if (record && record.get('Person_id')) {
			if (
				record.get('ElectronicTalonStatus_id') == 1 && record.get('ElectronicService_id') == win.ElectronicService_id // Вызов пациента доступен только для записей Талон ЭО с текущим статусом «Ожидает» и пунктом обслуживания равным пункту обслуживания Пользователя
				&& record.get('IsCurrentDate') == 1 // Вызвать пациента возможно, только если дата записи равна текущей дате
				&& win.ElectronicQueueInfo_IsOff == 1 // Вызвать пациента возможно, если у текущей ЭО признак «Очередь выключена» принимает значение false.
			) {
				this.WorkPlaceGrid.setActionDisabled('do_call', false);
			}
		
			// Вызов пациента доступен, если для текущего пункта обслуживания нет ни одного пациента с текущим статусом талона ЭО «Вызван»
			// Пермякова Мария (16:59:31 7/07/2017) просто кнопка должна бтыь недоступной
			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('ElectronicTalonStatus_id') == 2 && rec.get('ElectronicService_id') == win.ElectronicService_id);
			});
			if (index >= 0) {
				this.WorkPlaceGrid.setActionDisabled('do_call', true);
			}

			if (
				record.get('ElectronicTalonStatus_id') == 2 && record.get('ElectronicService_id') == win.ElectronicService_id // Действия применяются для записи Талона ЭО с текущим статусом «Вызван» и пунктом обслуживания равным пункту обслуживания Пользователя
				&& win.ElectronicQueueInfo_IsOff == 1 // Отменить вызов возможно, если у текущей ЭО признак «Очередь выключена» принимает значение false.
			) {
				this.WorkPlaceGrid.setActionDisabled('cancel_call', false);
			}

			if (
				record.get('ElectronicTalonStatus_id') == 2 && record.get('ElectronicService_id') == win.ElectronicService_id // Действие применяется для записей Талонов ЭО с текущим статусом «Вызван» и пунктом обслуживания равным пункту обслуживания Пользователя
				&& win.ElectronicQueueInfo_IsOff == 1 // Действие возможно, если у текущей ЭО признак «Очередь выключена» принимает значение false.
			) {
				this.WorkPlaceGrid.setActionDisabled('no_patient', false);
			}

			if (
				record.get('ElectronicTalonStatus_id') && record.get('ElectronicTalonStatus_id').inlist([1,2]) // Доступно выбрать запись со статусом «Вызван» или «Ожидает»
				&& record.get('ElectronicService_id') == win.ElectronicService_id // Пункт обслуживания равен пункту обслуживания Пользователя
				&& record.get('IsCurrentDate') == 1 // Принять пациента возможно, только если дата записи равна текущей дате
				&& win.ElectronicQueueInfo_IsOff == 1 // Принять пациента возможно, если у текущей ЭО признак «Очередь выключена» принимает значение false.
			) {
				this.WorkPlaceGrid.setActionDisabled('apply_call', false);
			}

			this.WorkPlaceGrid.setActionDisabled('open_emk', false);

			if (
				record.get('ElectronicTalonStatus_id') == 3 && record.get('ElectronicService_id') == win.ElectronicService_id // Действия применяется для записи Талона ЭО с текущим статусом «На обслуживании» и пунктом обслуживания равным пункту обслуживания Пользователя
				&& win.ElectronicQueueInfo_IsOff == 1
				&& isUserGroup('DrivingCommissionReg') // Отменить услугу возможно, если у текущей ЭО признак «Очередь выключена» принимает значение false.
			) {
				console.log(record);
				this.WorkPlaceGrid.setActionDisabled('do_cancel', false);
			}
		}
	},
	checkRecordActive: function(row) {
		var win = this;

		return win.ElectronicQueueInfo_IsOff == 2  || (row.get('ElectronicService_id') == win.ElectronicService_id && row.get('ElectronicTalonStatus_id') && row.get('ElectronicTalonStatus_id').inlist([1,2,3]));
	},
	initComponent: function() {
		var win = this;

		win.dateFilter = new sw.Promed.SwDateField({
			format : 'd.m.Y',
			plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
			listeners: {
				'select': function() {
					win.doSearch();
				},
				'keydown': function(inp , e) {
					if (e.getKey() == Ext.EventObject.ENTER) {
						e.stopEvent();
						win.doSearch();
					}
				}
			}
		});

		win.toolbarDate = new Ext.Toolbar({
			items: [{
				xtype: 'tbfill'
			}, {
				xtype: 'button',
				iconCls: 'arrow-previous16',
				handler: function()
				{
					win.prevDay();
					win.doSearch();
				}
			}, win.dateFilter, {
				xtype: 'button',
				iconCls: 'arrow-next16',
				handler: function()
				{
					win.nextDay();
					win.doSearch();
				}
			}]
		});

		this.FilterPanel = new Ext.Panel({
			hidden: true
		});

		win.WorkPlaceGrid = new sw.Promed.ViewFrame({
			showOnlyActive: true,
			uniqueId: true,
			title: '',
			region: 'center',
			dataUrl: '/?c=PaidService&m=loadWorkPlaceGrid',
			paging: false,
			toolbar: true,
			root: '',
			totalProperty: 'totalCount',
			autoLoadData: false,
			sortInfo: {field: 'DrugRequest_insDT'},
			stringfields:
			[
				{name: 'TimetableMedService_id', type: 'int', header: 'ID', key: true},
				{name: 'ElectronicTalonStatus', renderer: function(v, p, r) {
					var icon = '';
					if (r.get('ElectronicService_id') == win.ElectronicService_id) {
						switch(r.get('ElectronicTalonStatus_id')) {
							case 2: // Вызван
								icon = "<img src='/img/icons/ic_ivite.png' />";
								break;
							case 3: // На обслуживании
								icon = "<img src='/img/icons/ic_take.png' />";
								break;
						}
					}
					return icon;
				}, header: '', width: 40},
				{name: 'ElectronicTalon_Num', type: 'string', header: 'Талон', width: 120},
				{name: 'TimetableMedService_begTime', type: 'string', header: 'Записан', width: 120},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'ElectronicService_id', type: 'int', hidden: true},
				{name: 'ElectronicTalonStatus_id', type: 'int', hidden: true},
				{name: 'EvnPLDispDriver_id', type: 'int', hidden: true},
				{name: 'IsCurrentDate', type: 'int', hidden: true},
				{name: 'ElectronicTalon_id', type: 'int', hidden: true},
				{name: 'Person_IsUnknown', type: 'int', hidden: true},
				{name: 'RecMethodType_Name', type: 'string', header: 'Способ записи', width: 120},
				{name: 'Person_Fio', renderer: function(v, p, r) {
					if (r.get('Person_IsUnknown') == 2) {
						v = "<img src='/img/icons/warn_red_round12.png' />&nbsp;&nbsp;<a href='javascript:getWnd(\"swPaidServiceWorkPlaceWindow\").fixPersonUnknown(" + r.get('Person_id') + "," + r.get('Server_id') + ");' style='color: #0000FF;'>" + v + "</a>";
					}
					return v;
				}, header: 'ФИО', width: 300, id: 'autoexpand'},
				{name: 'Person_BirthDay', type: 'string', header: 'Дата рождения', width: 120},
				{name: 'EvnPLDispDriver_Num', type: 'string', header: 'Карта №', width: 120},
				{name: 'ElectronicTalonStatus_Name', type: 'string', header: 'Статус в ЭО', width: 200},
				{name: 'ElectronicService_Name', type: 'string', header: 'Кабинет', width: 200}
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onLoadData: function() {
				win.filterRecords();
				win.onRowSelect();
            },
			onRowSelect: function() {
				win.onRowSelect();
			}
		});

		win.WorkPlaceGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (
					win.checkRecordActive(row)
				) {
					// ok
				} else {
					// запись не активна
					cls = cls+'x-grid-rowgray ';
				}

				return cls;
			}
		});

		Ext.apply(this,	{
			tbar: win.toolbarDate,
			items: [
				win.WorkPlaceGrid
			],
			buttons: []
		});
		sw.Promed.swPaidServiceWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});