/**
 * swWorkPlacePolkaWindow - АРМ врача поликлиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.PolkaWP.swWorkPlacePolkaWindow', {
	noCloseOnTaskBar: true, // без кнопки закрытия на таксбаре
	extend: 'base.BaseForm',
	alias: 'widget.swWorkPlacePolkaWindow',
	autoShow: false,
	maximized: true,
	width: 1000,
	refId: 'polkawp',
	findWindow: false,
	closable: false,
	frame: false,
	cls: 'arm-window-new PolkaWP',
	title: 'АРМ врача поликлиники',
	header: true,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	onDblClick: function() {
		var win = this;
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (record.get('Person_id')) {
				if (!win.mainGrid.down('#action_openemk').isDisabled()) {
					win.openPersonEmkWindow();
				}
			}
			else {
				if (!win.mainGrid.down('#action_record').isDisabled()) {
					win.recordPatient();
				}
			}
		}
	},
	onHomeVisitDblClick: function() {
		var win = this;
		if (this.homeVisitGrid.getSelectionModel().hasSelection()) {
			var record = this.homeVisitGrid.getSelectionModel().getSelection()[0];

			if (record.get('Person_id')) {
				if (!win.homeVisitGrid.down('#action_openemk').isDisabled()) {
					win.openPersonEmkWindow();
				}
			}
		}
	},
	onRecordSelect: function() {
		var win = this;

		win.mainGrid.down('#action_openemk').disable();
		win.mainGrid.down('#action_record').disable();
		win.mainGrid.down('#action_recordfromqueue').disable();
		win.mainGrid.down('#action_cancel').disable();
		win.mainGrid.down('#action_toqueue').disable();
		win.mainGrid.down('#action_rewrite').disable();

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			var TimetableGraf_Date = null;
			if (record.get('TimetableGraf_Date')) {
				TimetableGraf_Date = Date.parseDate(record.get('TimetableGraf_Date').dateFormat('d.m.Y') + ' ' + record.get('TimetableGraf_begTime'), 'd.m.Y H:i');
			}
			var current_date = Date.parseDate(win.curDate + ' ' + win.curTime, 'd.m.Y H:i:s');

			if (record.get('Person_id')) {
				win.mainGrid.down('#action_openemk').enable();

				if (!(
					record.get('ARMType_id') == 24 ||
					(record.get('EvnStatus_id') && record.get('EvnStatus_id').inlist([12,13,15])) ||
					(getGlobalOptions().disallow_canceling_el_dir_for_elapsed_time == true &&
						record.get('EvnDirection_id') &&
						current_date > TimetableGraf_Date)
					||
					(getGlobalOptions().allow_canceling_without_el_dir_for_past_days != true &&
						!record.get('EvnDirection_id') &&
						current_date > TimetableGraf_Date)
				)) {
					win.mainGrid.down('#action_cancel').enable();
				}

				if (!(
					(getGlobalOptions().disallow_canceling_el_dir_for_elapsed_time == true &&
						record.get('EvnDirection_id') &&
						current_date > TimetableGraf_Date)
					||
					(getGlobalOptions().allow_canceling_without_el_dir_for_past_days != true &&
						!record.get('EvnDirection_id') &&
						current_date > TimetableGraf_Date)
					||
					(record.get('pmUser_updId') >= 1000000 && record.get('pmUser_updId') <= 5000000 && getRegionNick() == 'kareliya')
				)) {
					win.mainGrid.down('#action_toqueue').enable();
				}

				win.mainGrid.down('#action_rewrite').enable();
			} else {
				if (!(
					Ext6.isEmpty(win.userMedStaffFact.ElectronicService_id) &&
					getGlobalOptions().disallow_recording_for_elapsed_time == true &&
					current_date > TimetableGraf_Date
				)) {
					win.mainGrid.down('#action_record').enable();
					win.mainGrid.down('#action_recordfromqueue').enable();
				}
			}
		}
	},
	onHomeVisitRecordSelect: function() {
		var win = this;

		win.homeVisitGrid.down('#action_openemk').disable();
		win.homeVisitGrid.down('#action_status').disable();

		if (this.homeVisitGrid.getSelectionModel().hasSelection()) {
			var record = this.homeVisitGrid.getSelectionModel().getSelection()[0];

			if (record.get('Person_id')) {
				win.homeVisitGrid.down('#action_openemk').enable();
			}

			if (record.get('HomeVisitStatus_id') == 3 || record.get('HomeVisitStatus_id') == 6) {
				win.homeVisitGrid.down('#action_status').enable();

				win.homeVisitGrid.down('#action_status').menu.items.items[1].setDisabled(record.get('HomeVisitStatus_id')==3);
				win.homeVisitGrid.down('#action_status').menu.items.items[2].setDisabled(record.get('HomeVisitStatus_id')==3);
				win.homeVisitGrid.down('#action_status').menu.items.items[3].setDisabled(record.get('HomeVisitStatus_id')!=3);
			}
		}
	},
	checkMedStaffFactReplace: function() {
		var win = this,
			combo = win.mainPanel.down('#mpwp_MedStaffFactFilterType');
		if(!win.userMedStaffFact && !getGlobalOptions().CurMedStaffFact_id)
			return;
		combo.hide();
		Ext.Ajax.request({
			url: '/?c=MedStaffFactReplace&m=checkExist',
			params: {
				MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id || getGlobalOptions().CurMedStaffFact_id,
				begDate: win.dateMenu.getDateFrom().format('d.m.Y'),
				endDate: (win.dateMenu.getDateTo() || win.dateMenu.getDateFrom()).format('d.m.Y')
			},
			callback: function (opt, success, response) {
				if(success && response && response.responseText){
					var resp = Ext6.JSON.decode(response.responseText);
					if (resp.success && resp.exist) {
						// есть замещаемые врачи
						combo.show();
					}
				}
			}
		});
	},
	onEvnLabRequestRecordSelectionChange: function() {
		var win = this;

		var sm = win.labRequestGrid.getSelectionModel();

		var disableddel = true;
		var disabledprint = true; //е дизаблить печать протоколов если хотя бы одна запись выполненная
		var disableAll = true;
		var disableApprove = true;
		var disablePrintBarCodes = true;
		var disableTake = true;
		var disableCancel = true;
		var current_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');


		if (sm.getCount() >= 1){
			sm.getSelection().forEach(function (el) {
				// печать только для "С результатами" и "Одобренные"
				if (disabledprint){
					disabledprint = (el.get('EvnStatus_id') != 3 && el.get('EvnStatus_id') != 4);
				}
			});
		}

		if (sm.getCount() > 0) {
			disableAll = false;
			disableddel = false;
			// идём по выделенным и смотрим можно ли их удалять
			var records = sm.getSelection();
			for (var i = 0; i < records.length; i++) {
				disableApprove = disableApprove && (records[i].get('EvnLabSample_IsOutNorm') == 2);

				if (!Ext6.isEmpty(records[i].get('EvnLabSample_ids'))) {
					disablePrintBarCodes  = false;
				}

				disableddel =
					disableddel ||
					current_date > Date.parseDate(Ext6.util.Format.date(records[i].get('TimetableMedService_begTime')),'d.m.Y') ||
					(!(records[i].get('canEdit') == 1) || Ext6.isEmpty(records[i].get('EvnStatus_id')) || !records[i].get('EvnStatus_id').inlist([1]));

				if (records[i].get('ProbaStatus') && records[i].get('ProbaStatus').inlist(['needmore', 'needone', 'notall'])) {
					disableTake = false;
				}
				if (records[i].get('EvnStatus_id') && records[i].get('EvnStatus_id') == 2) {
					disableCancel = false;
				}
			}
		}

		win.labRequestGrid.down('#printBarcodes').setDisabled(disablePrintBarCodes);
		win.labRequestGrid.down('#printSampleList').setDisabled(sm.getCount() < 1);
		win.labRequestGrid.down('#printProtocol').setDisabled(disabledprint);

		win.labRequestGrid.down('#action_cancel').setDisabled(disableddel);
		win.labRequestGrid.down('#action_take_sample').setDisabled(disableTake);
		win.labRequestGrid.down('#action_cancel_sample').setDisabled(disableCancel);
		win.labRequestGrid.down('#action_outsourcing_create').setDisabled(sm.getCount() < 1);
	},
	recordPatient: function() {
		var win = this;

		getWnd('swPersonSearchWindowExt6').show({
			notHideOnSelect: true,
			onClose: function () {
				// do nothing
			},
			onSelect: function (pdata) {
				checkPersonDead({
					Person_id: pdata.Person_id,
					onIsLiving: function () {
						getWnd('swPersonSearchWindowExt6').hide();
						win.scheduleSave(pdata);
					},
					onIsDead: function (res) {
						Ext6.Msg.alert(langs('Ошибка'), langs('Запись невозможна в связи со смертью пациента'));
					}
				});
			},
			searchMode: 'all'
		});
	},
	getGrid: function ()
	{
		return this.mainGrid;
	},
	getSelectedRecord: function() {
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];
			if (record && record.get('TimetableGraf_id')) {
				return record;
			}
		}
		return false;
	},
	getHomeVisitSelectedRecord: function() {
		if (this.homeVisitGrid.getSelectionModel().hasSelection()) {
			var record = this.homeVisitGrid.getSelectionModel().getSelection()[0];
			if (record && record.get('HomeVisit_id')) {
				return record;
			}
		}
		return false;
	},
	rewrite: function () {
		var record = this.getSelectedRecord();
		var grid = this.getGrid();
		if (record == false) {
			return false;
		}
		return sw.Promed.Direction.rewrite({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			userMedStaffFact: this.userMedStaffFact,
			EvnDirection_id: record.get('EvnDirection_id'),
			callback: function (data) {
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy(function (record) {
								if (record.get('EvnDirection_id') == data.EvnDirection_id) {
									return true;
								}
							});
							if (index > -1) {
								grid.getView().focusRow(index);
								grid.getSelectionModel().select(index);
							}
						}
					}
				});
			}
		});
	},
	returnToQueue: function (options) {
		var win = this;
		if (!options) options = {};
		var record = this.getSelectedRecord();
		var grid = this.getGrid();
		if (record == false || !record.get('TimetableGraf_id')) {
			return false;
		}

		if (!Ext6.isEmpty(record.get('TimetableGraf_factTime')) && !options.ignorePriemCheck) {
			Ext6.Msg.show({
				icon: Ext6.MessageBox.QUESTION,
				msg: 'По данному направлению был осуществлен прием. В случае возвращения направления в очередь, связь с добавленным случаем лечения будет утеряна. Продолжить?',
				title: langs('Внимание'),
				buttons: Ext6.Msg.YESNO,
				fn: function (buttonId, text, obj) {
					if ('yes' == buttonId) {
						win.returnToQueue({ignorePriemCheck: 1});
					}
				}
			});
			return false;
		}

		return sw.Promed.Direction.returnToQueue({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableGraf_id: record.get('TimetableGraf_id'),
			EvnQueue_id: record.get('EvnQueue_id'),
			noask: !Ext6.isEmpty(record.get('TimetableGraf_factTime')),
			callback: function (data) {
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy(function (record) {
								if (data.EvnDirection_id == record.get('EvnDirection_id')) {
									return true;
								}
							});
							if (index > -1) {
								grid.getView().focusRow(index);
								grid.getSelectionModel().select(index);
							}
						}
					}
				});
			}
		});
	},
	reject: function () {
		var record = this.getSelectedRecord();
		var grid = this.getGrid();
		if (record == false || !record.get('TimetableGraf_id') || !sw.Promed.Direction) {
			return false;
		}
		return sw.Promed.Direction.cancel({
			cancelType: 'decline',
			ownerWindow: this,
			userMedStaffFact: this.userMedStaffFact,
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableGraf_id: record.get('TimetableGraf_id'),
			personData: {
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				Person_IsDead: record.get('Person_IsDead'),
				Person_Firname: record.get('Person_Firname'),
				Person_Secname: record.get('Person_Secname'),
				Person_Surname: record.get('Person_Surname'),
				Person_Birthday: record.get('Person_Birthday')
			},
			callback: function (cfg) {
				grid.getStore().reload({
					callback: function () {
						var index = grid.getStore().findBy(function (rec) {
							if (rec.get('TimetableGraf_id') == cfg.TimetableGraf_id) {
								return true;
							}
						});
						if (index > -1) {
							grid.getView().focusRow(index);
							grid.getSelectionModel().select(index);
						}
					}
				});
			}
		});
	},
	recordPatientFromQueue: function() {
		var win = this;

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];
			getWnd('swMPQueueWindow').show({
				useCase: 'record_from_queue',
				LpuSectionProfile_id: this.userMedStaffFact.LpuSectionProfile_id,
				Lpu_id: this.userMedStaffFact.Lpu_id,
				TimetableGraf_id: record.get('TimetableGraf_id'),
				params: record.data,
				userMedStaffFact: this.userMedStaffFact,
				onSelect: function (data) {
					// Действия после выбора ЭН при записи из очереди
					getWnd('swMPQueueWindow').hide();
					sw.Promed.Direction.recordFromQueue({
						queue: {
							EvnDirection_id: data['EvnDirection_id'],
							EvnQueue_id: data['EvnQueue_id']
						},
						params: {
							EvnDirection_id: data['EvnDirection_id'],
							TimetableGraf_id: record.get('TimetableGraf_id')
						},
						url: C_TTG_APPLY,
						loadMask: true,
						win: win,
						windowId: win.id,
						Timetable_id: record.get('TimetableGraf_id'),
						callback: function (success, response) {
							if (success) {
								var result = Ext6.JSON.decode(response.responseText);
								success = result.success;
							}
							if (success) {
								// обновляем грид и позиционируемся на добавленное направление
								win.mainGrid.getStore().reload({
									callback: function () {
										var index = win.mainGrid.getStore().findBy(function (rec) {
											return rec.get('TimetableGraf_id') == record.data['TimetableGraf_id'];
										});
										win.mainGrid.focus();
										win.mainGrid.getView().focusRow(index);
										win.mainGrid.getSelectionModel().select(index);
									}
								});
							}
						}
					});
				}
			});
		}
	},
	scheduleSave: function(data)
	{
		var win = this;

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (!data.TimetableGraf_id) { // если среди данных нет id бирки, извлекаем id из грида
				if (
					(record.get('TimetableGraf_id') == null) ||
					(record.get('Person_id') != null)
				) {
					return false;
				}

				data.TimetableGraf_id = record.get('TimetableGraf_id');
			}

			if (data.TimetableGraf_id) {
				sw.Promed.Direction.recordHimSelf({
					userMedStaffFact: this.userMedStaffFact
					, TimetableGraf_id: data.TimetableGraf_id
					, personData: data
					, windowId: this.getId()
					, onSaveRecord: function (conf) {
						win.mainGrid.getStore().reload();
					}
				});
			} else {
				data.LpuUnitType_SysNick = 'polka';
				data.addDirection = 1;
				data.DirType_id = 16;
				data.EvnDirection_Num = 0;
				data.MedPersonal_zid = 0;
				data.EvnDirection_setDate = this.curDate;
				data.From_MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
				data.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
				if (getGlobalOptions().lpu_id > 0) {
					data.Lpu_did = getGlobalOptions().lpu_id;
				}
				if (getGlobalOptions().CurMedPersonal_id > 0) {
					data.MedPersonal_id = getGlobalOptions().CurMedPersonal_id;
					data.MedPersonal_did = getGlobalOptions().CurMedPersonal_id;
				}
				if (getGlobalOptions().CurLpuSection_id > 0) {
					data.LpuSection_did = getGlobalOptions().CurLpuSection_id;
					data.LpuUnit_did = getGlobalOptions().CurLpuSection_id;
				}
				if (getGlobalOptions().CurMedService_id > 0) {
					data.MedService_id = getGlobalOptions().CurMedService_id;
				}
				if (getGlobalOptions().CurLpuSectionProfile_id > 0) {
					data.LpuSectionProfile_id = getGlobalOptions().CurLpuSectionProfile_id;
				}

				this.mask(langs('Подождите, сохраняется запись...'));
				Ext6.Ajax.request({
					url: C_QUEUE_APPLY,
					params: data,
					callback: function (options, success, response) {
						if (response) {
							var response_text = Ext6.JSON.decode(response.responseText);
							if (response_text.warning) {
								Ext6.Msg.show({
									icon: Ext6.MessageBox.QUESTION,
									msg: response_text.warning,
									title: langs('Внимание'),
									buttons: Ext6.Msg.YESNO,
									fn: function (buttonId, text, obj) {
										if ('yes' == buttonId) {
											var data = {
												Person_id: response_text.Person_id,
												Server_id: response_text.Server_id,
												PersonEvn_id: response_text.PersonEvn_id,
												OverrideWarning: 1
											}

											win.scheduleSave(data);
										}
									}
								});
							}
						}
						this.unmask();
						win.mainGrid.getStore().reload();
					}.createDelegate(this)
				});
			}
		}
	},
	readFromCard: function() {
		var win = this;
		// 1. пробуем считать с эл. полиса
		sw.Applets.AuthApi.getEPoliceData({callback: function(bdzData, person_data) {
			if (bdzData) {
				win.getDataFromBdz(bdzData, person_data);
			} else {
				// 2. пробуем считать с УЭК
				var successRead = false;
				if (sw.Applets.uec.checkPlugin()) {
					successRead = sw.Applets.uec.getUecData({callback: this.getDataFromUec.createDelegate(this), onErrorRead: function() {
						Ext6.Msg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
						return false;
					}});
				}
				// 3. если не считалось, то "Не найден плагин для чтения данных картридера либо не возможно прочитать данные с карты"
				if (!successRead) {
					Ext6.Msg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
					return false;
				}
			}
		}});
	},
	getDataFromBdz: function(bdzData, person_data) {
		this.getDataFromUec(bdzData, person_data);
	},
	getDataFromUec: function(uec_data, person_data) {
		var form = this;
		var grid = this.getGrid();
		var f = false;
		grid.getStore().each(function(record) {
			if (record.get('Person_id') == person_data.Person_id) {
				log(langs('Найден в гриде'));

				var index = grid.getStore().indexOf(record);
				grid.getView().focusRow(index);
				grid.getSelectionModel().select(index);
				form.openPersonEmkWindow();
				f = true;
				return;
			}
		});
		if (!f) { // Если не нашли в гриде
			// todo: Еще надо проверку в принципе на наличие такого человека в БД, и если нет - предлагать добавлять
			// Открываем на добавление
			log(langs('Не найден в гриде'));
			var params = {};
			params.action = 'add';
			params.Person_id = person_data.Person_id;
			params.PersonEvn_id = (person_data.PersonEvn_id)?person_data.PersonEvn_id:null;
			params.Server_id = (person_data.Server_id)?person_data.Server_id:null;
			params.swPersonSearchWindow = getWnd('swPersonSearchWindowExt6');
			Ext6.Ajax.request({
				params: {LpuUnitType_SysNick: 'polka',Person_id: params.Person_id, LpuSection_id: form.userMedStaffFact.LpuSection_id, MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id},
				callback: function(opt, success, response) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if ( response_obj.success && response_obj.result )
					{
						// да, записан
						Ext6.Msg.alert(langs('Сообщение'), langs('Пациент')+ response_obj.result['Person_FIO'] +' ('
							+ response_obj.result['Person_BirthDay'] +' '+langs(' г.р., возраст: ')+
							response_obj.result['Person_Age'] +') записан сегодня на '+ response_obj.result['TimetableGraf_begTime']);
						return false;
					}
					// ? pdata.EvnDirectionData = null;
					this.createTtgAndOpenPersonEPHForm(params);
				}.createDelegate(form),
				url: '/?c=TimetableGraf&m=checkPersonByToday'
			});
			//form.scheduleNew();
		}
	},
	addHomeVisit: function() {
		var win = this;

		getWnd('swPersonSearchWindowExt6').show({
			onSelect: function(personData) {
				if ( personData.Person_id > 0 ) {
					getWnd('swHomeVisitAddWindow').show({
						Person_id: personData.Person_id,
						Server_id: personData.Server_id,
						action:'add',
						Lpu_id: getGlobalOptions().lpu_id,
						callback : function() {
							win.scheduleRefresh();
						}
					});
				}
			}
		});
	},
	updateHomeVisitStatus: function (status) {
		var win = this;
		var grid = win.homeVisitGrid;
		var record = win.getHomeVisitSelectedRecord();

		if (status == 2) {
			win.openHomeVisitDenyWindow();
			return;
		}
		if (status == 5) {
			win.openHomeVisitCancelWindow();
			return;
		}

		var url = '/?c=HomeVisit&m=confirmHomeVisit';
		if (status == 1) {
			var url = '/?c=HomeVisit&m=setStatusNew';
		}
		if (record && record.get('HomeVisit_id')) {
			Ext6.Ajax.request({
				failure: function (response, options) {
					showSysMsg(langs('При загрузке сигнальный информации о диспансерном учете возникли ошибки'));
				},
				params: {
					HomeVisit_id: record.get('HomeVisit_id')
				},
				success: function (response, options) {
					win.scheduleRefresh();

				},
				url: url
			});
		}
	},
	openHomeVisitDenyWindow: function () {
		var wnd = this;
		var grid = wnd.homeVisitGrid;
		var record = wnd.getHomeVisitSelectedRecord();

		if (!record || Ext6.isEmpty(record.get('HomeVisit_id')) || record.get('HomeVisitStatus_id') != 3) {
			return;
		}

		var params = {
			HomeVisit_id: record.get('HomeVisit_id'),
			callback: function () {
				wnd.scheduleRefresh()
			}
		};

		getWnd('swHomeVisitDenyWindow').show(params);
	},
	openHomeVisitCancelWindow: function () {
		var wnd = this;
		var grid = wnd.homeVisitGrid;
		var record = wnd.getHomeVisitSelectedRecord();

		if (!record || Ext6.isEmpty(record.get('HomeVisit_id')) || record.get('HomeVisitStatus_id') == 5) {
			return;
		}

		var params = {
			HomeVisit_id: record.get('HomeVisit_id'),
			Person_Surname: record.get('Person_Surname'),
			Person_Firname: record.get('Person_Firname'),
			Person_Secname: record.get('Person_Secname'),
			Address_Address: record.get('Address_Address'),
			callback: function () {
				wnd.scheduleRefresh()
			},
			needLpuComment: true
		};

		getWnd('swHomeVisitCancelWindow').show(params);
	},
	openPersonEmkWindow: function(openEvn) {
		var form = this;

		var grid = this.mainGrid;
		if (form.cardPanel.items.indexOf(form.cardPanel.getLayout().getActiveItem()) == 1) {
			grid = this.homeVisitGrid;
		}

		if (grid.getSelectionModel().hasSelection()) {

			var record = grid.getSelectionModel().getSelection()[0];
			if (typeof record != 'object' || Ext6.isEmpty(record.get('Person_id'))) return false;

			var searchNodeObj = {};

			// если неизвестный не показываем ЭМКу
			if (!form.ElectronicQueuePanel.checkIsUnknown({record: record})) {log('scheduleOpen() is_unknown'); return false; }

			var electronicQueueData = (form.ElectronicQueuePanel.electronicQueueData
					? form.ElectronicQueuePanel.electronicQueueData
					: form.ElectronicQueuePanel.getElectronicQueueData()
			);

			if (form.ElectronicQueuePanel.electronicQueueData) form.ElectronicQueuePanel.electronicQueueData = null;


			if(Ext6.isEmpty(openEvn))
			if (record.get('Evn_id')) {
				openEvn = {
					object: 'EvnPL',
					object_value: record.get('Evn_id')
				};
			}

			var params = {
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				TimetableGraf_id: record.get('TimetableGraf_id'),
				EvnDirection_id: record.get('EvnDirection_id'),
				userMedStaffFact: form.userMedStaffFact,
				MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id,
				LpuSection_id: form.userMedStaffFact.LpuSection_id,
				readOnly: true,
				ARMType: 'common',
				openEvn: openEvn,
				electronicQueueData: electronicQueueData,
				callback: function(retParams) {
					form.scheduleRefresh();
					// выполняем кэллбэк (нужен для ЭО в некоторых случаях)
					if (retParams && retParams.callback && typeof retParams.callback == 'function') retParams.callback();
				}
			};

			//yl:если текущий грид - это вызовы
			if (this.cardPanel.getLayout() && (itm=this.cardPanel.getLayout().getActiveItem()) && itm.itemId && itm.itemId=="homeVisitTab") {//idx==1
				if (!Ext.isEmpty(record.get("EvnVizitPL_pid"))) {//если случай уже создан - сразу откроем его
					params.openEvn = {
						object: "EvnPL",
						object_value: record.get("EvnVizitPL_pid")
					}
				}else if (record.get("HomeVisitStatus_id") != 4) {//при открытии ЭМК авто-создание случая с привязкой к вызову на дом
					params.HomeVisit_id=record.get("HomeVisit_id");
					params.allowHomeVisit = true;//разрешить привязку Вызова к Случаю
					if (record.get("HomeVisitStatus_id") != 3) {
						this.updateHomeVisitStatus(3);//Одобрен врачом - промежуточный статус, зачем?
					}
				}
			}

			getWnd('swPersonEmkWindowExt6').show(params);

			return true;
		}
	},
	/*
	*	Открывает ЭМК при нажатии без записи
	*/
	createTtgAndOpenPersonEPHForm: function(pdata)
	{
		var me = this;
		var openEMK = function () {
			getWnd('swPersonEmkWindowExt6').show({
				Person_id: pdata.Person_id,
				Server_id: pdata.Server_id,
				PersonEvn_id: pdata.PersonEvn_id,
				userMedStaffFact: me.userMedStaffFact,
				TimetableGraf_id: pdata.TimetableGraf_id || null,
				EvnDirectionData: pdata.EvnDirectionData || null,
				mode: 'workplace',
				ARMType: me.ARMType,
				callback: function () {
					this.scheduleRefresh();
				}.createDelegate(me)
			});
		};

		if (pdata.Person_IsDead == 'true') {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function(){openEMK()},
				icon: Ext6.Msg.WARNING,
				msg: langs('Установлена дата смерти пациента. ЭМК доступна только для просмотра'),
				title: langs('Внимание')
			});
		} else {
			openEMK()
		}
	},
	withoutRecord: function(params) {
		var win = this;

		var personParams = {
			notHideOnSelect: true,
			onSelect: function(pdata)
			{
				var onIsLiving = function(){
					getWnd('swPersonSearchWindowExt6').hide();
					// проверка - стоит ли этот пациент в очереди по профилю этого отделения в данное МО или записан к этому врачу (на это рабочее место врача)
					Ext6.Ajax.request({
						params: {
							useCase: 'create_evnpl_without_recording',
							Person_id: pdata.Person_id,
							MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
						},
						callback: function(opt, success, response) {
							var response_obj = Ext6.JSON.decode(response.responseText);
							if ( Ext6.isArray(response_obj) ) {
								if ( response_obj.length == 1 ) {
									var begTime = response_obj[0]['Timetable_begTime'] || null;
									if (begTime) {
										begTime = Date.parseDate(begTime, 'd.m.Y H:i');
									}
									if (begTime && Ext6.util.Format.date(begTime, 'd.m.Y') == getGlobalOptions().date && response_obj[0]['MedStaffFact_did'] == win.userMedStaffFact.MedStaffFact_id) {
										Ext6.Msg.alert(langs('Сообщение'), langs('Пациент записан на текущий день. Воспользуйтесь функцией приема по записи'));
										return false;
									}
									var msg, buttonText;
									msg = 'Пациент <b>' + pdata.Person_Surname + ' ' + pdata.Person_Firname + ' ' + pdata.Person_Secname + '</b> ';
									if (17 == response_obj[0]['EvnStatus_id'] || response_obj[0]['Timetable_begTime']) {
										if (response_obj[0]['EvnDirection_IsAuto'] != 2) {
											msg += 'записан на ' + response_obj[0]['Timetable_begTime'] + ' по направлению ' + response_obj[0]['EvnDirection_Num'] + ' по профилю ' + response_obj[0]['LpuSectionProfile_Name'] + ', врач ' + response_obj[0]['MSF_Person_Fin'] + '.';
										} else {
											msg += 'записан на ' + response_obj[0]['Timetable_begTime'] + ', врач ' + response_obj[0]['MSF_Person_Fin'] + '.';
										}

										yesObject = {
											text: 'Принять по этому направлению',
											descr: 'Пациент будет обслужен по записи'
										};

										noObject = {
											text: 'Принять без направления',
											descr: 'Пациент останется записан'
										};
									} else if (10 == response_obj[0]['EvnStatus_id'] || !response_obj[0]['Timetable_begTime']) {
										if (response_obj[0]['EvnDirection_IsAuto'] != 2) {
											msg += 'стоит в очереди по направлению ' + response_obj[0]['EvnDirection_Num'] + ' по профилю ' + response_obj[0]['LpuSectionProfile_Name'] + '.';
										} else {
											msg += 'стоит в очереди по профилю ' + response_obj[0]['LpuSectionProfile_Name'] + '.';
										}

										yesObject = {
											text: 'Принять по этому направлению',
											descr: 'Пациент будет убран из очереди'
										};

										noObject = {
											text: 'Принять без направления',
											descr: 'Пациент останется в очереди'
										};
									} else {
										// Данное направление не имеет статуса "Поставлено в очередь" или "Записано". Создать случай без связи с направлением
										pdata.EvnDirectionData=null;
										win.createTtgAndOpenPersonEPHForm(pdata);
										return false;
									}

									msg = msg + '<br>Выберите дальнейшее действие:';

									getWnd('swYesNoWindow').show({
										title: 'Приём пациента',
										msg: msg,
										yesObject: yesObject,
										noObject: noObject,
										callback: function(answer) {
											if (answer == 2) {
												// создавать случай со связью с направлением
												pdata.EvnDirectionData=response_obj[0];
												pdata.EvnDirectionData.Diag_did = pdata.EvnDirectionData.Diag_id;
												delete pdata.EvnDirectionData.Diag_id;
												pdata.EvnDirectionData.Org_did = pdata.EvnDirectionData.Org_id;
												delete pdata.EvnDirectionData.Org_id;
												win.createTtgAndOpenPersonEPHForm(pdata);
											} else {
												// создать случай без связи с направлением
												pdata.EvnDirectionData=null;
												win.createTtgAndOpenPersonEPHForm(pdata);
											}
										}
									});
								} else if ( response_obj.length > 1 ) {
									// выводим список этих направлений с возможностью выбрать одно из них
									getWnd('swEvnDirectionSelectWindow').show({
										useCase: 'create_evnpl_without_recording',
										storeData: response_obj,
										Person_Birthday: pdata.Person_Birthday,
										Person_Firname: pdata.Person_Firname,
										Person_Secname: pdata.Person_Secname,
										Person_Surname: pdata.Person_Surname,
										Person_id:pdata.Person_id,
										callback: function(evnDirectionData){
											if (evnDirectionData && evnDirectionData.EvnDirection_id){
												// создавать случай со связью с направлением
												pdata.EvnDirectionData=evnDirectionData;
											} else {
												// создать случай без связи с направлением
												pdata.EvnDirectionData=null;
											}
										},
										onHide: function(){
											// если направление не выбрано, то создавать случай без связи с направлением
											win.createTtgAndOpenPersonEPHForm(pdata);
										}
									});
								} else {
									// создать случай без связи с направлением
									pdata.EvnDirectionData=null;
									win.createTtgAndOpenPersonEPHForm(pdata);
								}
							}
						},
						url: '/?c=EvnDirection&m=loadEvnDirectionList'
					});
				};
				if (isAdmin){
					onIsLiving();
				} else {
					checkPersonDead({
						Person_id: pdata.Person_id,
						onIsLiving: onIsLiving,
						onIsDead: function(res) {
							Ext6.Msg.alert(langs('Ошибка'), langs('Запись невозможна в связи со смертью пациента'));
						}
					});
				}
			},
			needUecIdentification: true,
			searchMode: 'all'
		};

		if (!Ext6.isEmpty(params)){
			if (!Ext6.isEmpty(params.Person_Firname)){
				personParams.personFirname = params.Person_Firname;
			}
			if (!Ext6.isEmpty(params.Person_Secname)){
				personParams.personSecname = params.Person_Secname;
			}
			if (!Ext6.isEmpty(params.Person_Surname)){
				personParams.personSurname = params.Person_Surname;
			}
			if (!Ext6.isEmpty(params.Person_Birthday)){
				personParams.PersonBirthDay_BirthDay = params.Person_Birthday;
			}
			if (!Ext6.isEmpty(params.Polis_Num)){
				personParams.Polis_EdNum = params.Polis_Num;
			}
		}

		getWnd('swPersonSearchWindowExt6').show(personParams);
	},
	show: function() {
		this.callParent(arguments);
		var win = this;

		this.mainGrid.getStore().clearFilter(); //зануляем фильтр, иначе если переключиться с АРМа с ЭО на АРМ без ЭО, фильтр остается #178130
		if (!arguments[0] || !arguments[0].userMedStaffFact || !arguments[0].userMedStaffFact.ARMType) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
		} else {
			this.ARMType = arguments[0].userMedStaffFact.ARMType;
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		log('userMedStaffFact', this.userMedStaffFact);

		sw.Promed.MedStaffFactByUser.setMenuTitle(win, win.userMedStaffFact);

		// todo надо уточнить, что если пунктов забора несколько?
		win.pzm_MedService_id = null;
		win.MedService_IsExternal = null;
		sw.Promed.MedStaffFactByUser.store.each(function(rec) {
			if (rec && rec.get('ARMType') == 'pzm' && rec.get('LpuSection_id') == win.userMedStaffFact.LpuSection_id) {
				win.pzm_MedService_id = rec.get('MedService_id');
				win.MedService_IsExternal = rec.get('MedService_IsExternal');
			}
		});

		if (win.pzm_MedService_id) {
			win.mainPanel.down('[refId=labButton]').show();
			win.leftMenu.down('#medServiceLink').show();
			win.leftMenu.down('#evnUslugaParSearch').show();
		} else {
			win.mainPanel.down('[refId=labButton]').hide();
			win.leftMenu.down('#medServiceLink').hide();
			win.leftMenu.down('#evnUslugaParSearch').hide();
		}

		// win.labRequestGrid.down('#action_outsourcing_create').setHidden('action_outsourcing_create', !win.MedService_IsExternal);

		// грузим текущую дату
		win.mask(LOAD_WAIT);
		Ext6.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				win.unmask();
				if (success && response.responseText != '') {
					var result  = Ext6.JSON.decode(response.responseText);
					win.curDate = result.begDate;
					win.curTime = result.begTime;
					win.dateMenu.setDates(Date.parseDate(result.begDate, 'd.m.Y'));
					win.scheduleRefresh();
					win.checkMedStaffFactReplace();
				}
			}
		});

		win.onRecordSelect();

		// запустим ЭО
		this.ElectronicQueuePanel.initElectronicQueue();
	},
	showEvnUslugaOperEditWindow: function(data) {
		var win = this;
		
		var params = {
			action: 'edit',
			parentClass: 'EvnSection',
			useCase: 'OperBlock',
			formParams: {
				EvnUslugaOper_id: data.EvnUslugaOper_id,
				EvnUslugaOper_setDate: data.EvnUslugaOper_setDate,
				LpuSection_id: win.userMedStaffFact.LpuSection_id,
				OperBrig: data.OperBrig
			},
			callback: function() {
				// обновить гриды
				win.scheduleRefresh();
			}
		};

		getWnd('swEvnUslugaOperEditWindow').show(params);
	},
	sendRequestsToLis: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;

		// Проверяем есть ли выбранные записи
		var selections = win.labRequestGrid.getSelectionModel().getSelection();
		var ArrayId = [];

		for (var key in selections) {
			if (selections[key].data) {
				ArrayId.push(selections[key].data['EvnLabRequest_id'].toString());
			}
		}

		var params = {}
		params.EvnLabRequests = Ext6.JSON.encode(ArrayId);
		if (options.onlyNew) {
			params.onlyNew = options.onlyNew;
		}
		if (options.changeNumber) {
			params.changeNumber = options.changeNumber;
		}
		if (win.labRequestGrid.getSelectionModel().getCount() > 0) {
			win.getLoadMask(langs('Создание ')+((ArrayId.length>1)?langs('заявок'):langs('заявки'))+langs(' для анализатора')).show();
			// получаем выделенную запись
			Ext6.Ajax.request({
				url: '/?c='+getLabController()+'&m=createRequestSelectionsLabRequest',
				params: params,
				callback: function(opt, success, response) {
					win.getLoadMask(LOAD_WAIT).hide();
					if (success && response.responseText != '') {
						var result = Ext6.JSON.decode(response.responseText);
						if (result.success) {
							if (result.sysMsg) {
								showSysMsg(result.sysMsg);
							}
							if (result.Alert_Code) {
								switch(result.Alert_Code) {
									case 100:
										Ext6.Msg.show({
											buttons: Ext6.Msg.YESNOCANCEL,
											buttonText: {
												yes: langs('Только новые'),
												no: langs('Все'),
												cancel: langs('Отмена')
											},
											fn: function(buttonId, text, obj) {
												if ( buttonId == 'yes' ) {
													options.onlyNew = 2;
													win.sendRequestsToLis(options);
												} else if (buttonId == 'no') {
													options.onlyNew = 1;
													win.sendRequestsToLis(options);
												}
											}.createDelegate(this),
											icon: Ext6.MessageBox.QUESTION,
											msg: result.Alert_Msg,
											title: langs('Вопрос')
										});
										break;
									case 101:
										Ext6.Msg.show({
											buttons: Ext6.Msg.YESNOCANCEL,
											fn: function(buttonId, text, obj) {
												if ( buttonId == 'yes' ) {
													options.changeNumber = 2;
													win.sendRequestsToLis(options);
												} else if (buttonId == 'no') {
													options.changeNumber = 1;
													win.sendRequestsToLis(options);
												}
											}.createDelegate(this),
											icon: Ext6.MessageBox.QUESTION,
											msg: result.Alert_Msg,
											title: langs('Вопрос')
										});
										break;
								}
							} else {
								win.labRequestGrid.getGrid().getStore().reload();
								showSysMsg(langs('Заявка для анализатора успешно создана'), langs('Заявка для анализатора'));
							}
						} else {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: function() {
								},
								icon: Ext6.Msg.WARNING,
								msg: result.Error_Msg,
								title: langs('Заявка для анализатора')
							});
						}
					}
				}
			});
		} else {
			sw.swMsg.alert(langs('Заявка не выбрана'), langs('Для создания заявки необходимо выбрать хотя бы одну заявку'));
		}
	},
	stepDay: function(day)
	{
		var win = this;
		var date1 = (win.dateMenu.getDateFrom() || Date.parseDate(win.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (win.dateMenu.getDateTo() || win.dateMenu.getDateFrom() || Date.parseDate(win.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		if( date1.toJSONString() === date2.toJSONString() ) {
			win.dateMenu.setDates(date1);
		} else {
			win.dateMenu.setDates([date1, date2]);
		}
		win.checkMedStaffFactReplace();
	},
	prevDay: function() {
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	currentDay: function () {
		var win = this;
		var date1 = Date.parseDate(win.curDate, 'd.m.Y');
		win.dateMenu.setDates(date1);
		win.checkMedStaffFactReplace();
	},
	currentWeek: function () {
		var win = this;
		var date1 = (Date.parseDate(win.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		win.dateMenu.setDates([date1, date2]);
		win.checkMedStaffFactReplace();
	},
	currentMonth: function () {
		var win = this;
		var date1 = (Date.parseDate(win.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		win.dateMenu.setDates([date1, date2]);
		win.checkMedStaffFactReplace();
	},
	onTabChange: function() {
		this.scheduleRefresh();
	},
	scheduleRefresh: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this,
			MedStaffFactFilterType = 3;
		if (!win.dateMenu.getDateFrom()) {
			return false;
		}

		if (win.mainPanel.down('#mpwp_MedStaffFactFilterType').isVisible()) {
			MedStaffFactFilterType = win.mainPanel.down('#mpwp_MedStaffFactFilterType').getValue();
		}
		
		if (win.cardPanel.items.indexOf(win.cardPanel.getLayout().getActiveItem()) == 2) {
			var params = {
				begDate: win.dateMenu.getDateFrom().format('d.m.Y'),
				endDate: (win.dateMenu.getDateTo() || win.dateMenu.getDateFrom()).format('d.m.Y'),
				MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
				MedService_id: win.pzm_MedService_id,
				MedServiceType_SysNick: 'pzm'
			};

			win.labRequestGrid.getStore().load({
				params: params,
				callback: function () {
					if (options.callback && typeof options.callback == 'function') {
						options.callback();
					}
				}
			});
		} else if (win.cardPanel.items.indexOf(win.cardPanel.getLayout().getActiveItem()) == 1) {
			var params = {
				begDate: win.dateMenu.getDateFrom().format('d.m.Y'),
				endDate: (win.dateMenu.getDateTo() || win.dateMenu.getDateFrom()).format('d.m.Y'),
				MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
			};

			win.homeVisitGrid.getStore().load({
				params: params,
				callback: function () {
					if (options.callback && typeof options.callback == 'function') {
						options.callback();
					}
				}
			});
		} else {
			win.mainGrid.loadParams = {
				begDate: win.dateMenu.getDateFrom().format('d.m.Y'),
				endDate: (win.dateMenu.getDateTo() || win.dateMenu.getDateFrom()).format('d.m.Y'),
				MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
				MedStaffFactFilterType_id: MedStaffFactFilterType 
			};

			// добавляем загрузочные параметры ЭО в loadParams
			win.ElectronicQueuePanel.setElectronicQueueLoadStoreParams();

			win.mainGrid.getStore().load({
				params: win.mainGrid.loadParams,
				callback: function () {
					if (options.callback && typeof options.callback == 'function') {
						options.callback();
					}
				}
			});
		}
	},
	checkEvnPLDispDop: function() {
		var id_salt = Math.random();
		var win_id = 'print_pac_list' + Math.floor(id_salt * 10000);

		var personIds = [];
		this.mainGrid.getStore().each(function(rec) {
			if (!Ext6.isEmpty(rec.get('Person_id'))) {
				personIds.push(rec.get('Person_id'));
			}
		});

		if (personIds.length > 0) {
			window.open('/?c=EvnPLDispDop13&m=checkPersons&personIds='+Ext6.JSON.encode(personIds), win_id);
		}
	},
	printPacList: function() {
		var record = this.getSelectedRecord();
		if (record == false) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не выбрана запись'));
			return false;
		}

		var id_salt = Math.random();
		var win_id = 'print_pac_list' + Math.floor(id_salt * 10000);
		window.open('/?c=TimetableGraf&m=printPacList&Day=' + record.get('TimetableGraf_Date').dateFormat('d.m.Y') + '&MedStaffFact_id=' + record.get('MedStaffFact_id'), win_id);
	},
	/**
	 * Открывает форму редактирования заявки на лабораторное исследование
	 * @param action
	 */
	openLabRequestEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) return false;

		if (getWnd('swEvnLabRequestEditWindow').isVisible()) {

			Ext6.Msg.alert(
				langs('Сообщение'),
				langs('Окно редактирования заявки уже открыто. Для продолжения необходимо закрыть окно редактирования заявки.')
			);

			return false;
		}

		var win = this,
			params = new Object();

		params.action = action;
		params.ARMType = 'pzm';
		params.MedService_id = this.pzm_MedService_id;

		params.callback = function(retParams) {
			win.scheduleRefresh();
		};

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindowExt6').show({
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					params.Person_Firname = person_data.Person_Firname;//Параметры для печати на принтере Zebra
					params.Person_Secname = person_data.Person_Secname;//Параметры для печати на принтере Zebra
					params.Person_Surname = person_data.Person_Surname;//Параметры для печати на принтере Zebra

					// При попытке добавить пациента без записи по кнопке «Добавить», перед отображением формы создания заявки, выполняется поиск заявок данного пациента в статусе "Новая" , созданных 3 месяца назад от текущей даты и позднее.
					getWnd('swEvnLabRequestSelectWindow').show({
						Person_id: params.Person_id,
						MedService_id: params.MedService_id,
						ARMType: params.ARMType,
						onNewEvnLabRequest: function() {
							getWnd('swEvnLabRequestEditWindow').show(params);
						}
					});

				}, searchMode: 'all'
			});
		} else {
			var record = win.labRequestGrid.getSelectionModel().getSelectedRecord();

			if ( !record || !record.get('EvnDirection_id') ) {
				Ext6.Msg.alert(langs('Ошибка'), langs('Не выбрана заявка из списка'));
				return false;
			}

			//для печати на принтере Zebra
			params.Person_ShortFio = record.get('Person_ShortFio');
			params.EvnDirection_id = record.get('EvnDirection_id');
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');

			getWnd('swEvnLabRequestEditWindow').show(params);
		}
	},
	/**
	 * Отклонение лаб. заявки
	 */
	rejectEvnLabRequest: function() {
		var win = this;
		var records = win.labRequestGrid.getSelectionModel().getSelection();
		var EvnDirection_ids = [];

		for (var i = 0; i < records.length; i++) {
			if (
				!Ext6.isEmpty(records[i].get('EvnDirection_id'))
				&& records[i].get('EvnDirection_id') > 0
			) {
				EvnDirection_ids = EvnDirection_ids.concat(records[i].get('EvnDirection_id').toString());
			}
		}

		if (!Ext6.isEmpty(EvnDirection_ids) && EvnDirection_ids.length > 0) {
			getWnd('swSelectEvnStatusCauseWindow').show({
				EvnClass_id: 27,
				formType: 'labdiag',
				callback: function(EvnStatusCauseData) {
					if (!Ext6.isEmpty(EvnStatusCauseData.EvnStatusCause_id)) {
						win.getLoadMask("Отмена направлений на лабораторное обследование...").show();
						Ext6.Ajax.request({
							url: '/?c=EvnLabRequest&m=cancelDirection',
							params: {
								EvnDirection_ids: Ext6.JSON.encode(EvnDirection_ids),
								EvnStatusCause_id: EvnStatusCauseData.EvnStatusCause_id,
								EvnStatusHistory_Cause: EvnStatusCauseData.EvnStatusHistory_Cause
							},
							callback: function(options, success, response) {
								win.getLoadMask().hide();
								if (success) {
									win.scheduleRefresh();
								}
							}
						});
					}
				}
			});
		}
	},
	/**
	 * Взятие проб
	 */
	takeSample: function() {
		var win = this;

		var selections = win.labRequestGrid.getSelectionModel().getSelection();
		var ArrayId = [];

		for (var key in selections) {
			if (selections[key].data) {
				ArrayId.push(selections[key].data['EvnLabRequest_id'].toString());
			}
		}
		var params = {
			MedServiceType_SysNick: 'pzm'
		};
		params.EvnLabRequests = Ext6.JSON.encode(ArrayId);
		params.MedService_did = win.MedService_id;

		if (win.labRequestGrid.getSelectionModel().getCount() > 0) {
			win.getLoadMask(langs('Взятие проб')).show();
			// получаем выделенную запись
			Ext6.Ajax.request({
				url: '/?c=EvnLabRequest&m=takeLabSample',
				params: params,
				callback: function(opt, success, response) {
					win.getLoadMask().hide();
					if (success && response.responseText != '') {
						var result = Ext6.JSON.decode(response.responseText);
						if (result.success) {
							win.scheduleRefresh();
						} else {
							Ext6.Msg.alert(langs('Взятие проб'), result.Error_Msg);
						}
					}
				}
			});
		} else {
			Ext6.Msg.alert(langs('Заявка не выбрана'), langs('Выберите заявку, для которой нужно взять пробы'));
		}
	},
	/**
	 * Отмена взятия проб
	 */
	cancelSample: function() {
		var win = this;

		var selections = win.labRequestGrid.getSelectionModel().getSelection();
		var ArrayId = [];

		for (var key in selections) {
			if (selections[key].data) {
				ArrayId.push(selections[key].data['EvnLabRequest_id'].toString());
			}
		}
		var params = {
			MedServiceType_SysNick: 'pzm'
		};
		params.EvnLabRequests = Ext6.JSON.encode(ArrayId);
		params.MedService_did = win.MedService_id;

		if (win.labRequestGrid.getSelectionModel().getCount() > 0) {
			Ext6.Msg.show({
				title: langs('Отмена взятия проб'),
				msg: langs('Вы действительно хотите отменить взятие проб?'),
				buttons: Ext6.Msg.YESNO,
				fn: function(btn) {
					if (btn === 'yes') {
						win.getLoadMask(langs('Отмена взятия проб')).show();
						// получаем выделенную запись
						Ext6.Ajax.request({
							url: '/?c=EvnLabRequest&m=cancelLabSample',
							params: params,
							callback: function(opt, success, response) {
								win.getLoadMask().hide();
								if (success && response.responseText != '') {
									var result = Ext6.JSON.decode(response.responseText);
									if (result.success) {
										win.scheduleRefresh();
									} else {
										Ext6.Msg.alert(langs('Взятие проб'), result.Error_Msg);
									}
								}
							}
						});
					}
				},
				icon: Ext6.MessageBox.QUESTION
			});
		} else {
			Ext6.Msg.alert(langs('Заявка не выбрана'), langs('Выберите заявку, для которой нужно отменить взятие проб'));
		}
	},
	barCodeIsFocused: false,
	/**
	 * Выводим поле для ввода штрих-кода
	 */
	showInputBarCodeField: function(inputPlace, EvnLabSample_id, element) {
		var win = this;
		var oldBarCode = element.innerHTML;
		Ext6.get(inputPlace).setDisplayed('none');
		Ext6.get(inputPlace + '_inp').setDisplayed('block');

		var cmp = new Ext.form.TextField({ // todo перевести на Ext6
			hideLabel: true
			,renderTo: inputPlace + '_inp'
			,width: 100
			,listeners:
				{
					blur: function(f) {
						Ext6.get(inputPlace).setDisplayed('block');
						Ext6.get(inputPlace + '_inp').setDisplayed('none');
						f.destroy();
						win.barCodeIsFocused = false;
					},
					render: function(f) {
						f.setValue(oldBarCode);
						f.focus(true);
						win.barCodeIsFocused = true;
					},
					change: function(f,n,o) {
						if (!Ext6.isEmpty(n) && n != oldBarCode) {
							// проверить на уникальность и обновить в БД
							win.getLoadMask(langs('Сохранение штрих-кода')).show();
							Ext6.Ajax.request({
								url: '/?c=EvnLabSample&m=saveNewEvnLabSampleBarCode',
								params: {
									EvnLabSample_id: EvnLabSample_id,
									EvnLabSample_BarCode: n
								},
								callback: function(opt, success, response) {
									win.getLoadMask().hide();
									if (success && response.responseText != '') {
										var result = Ext6.JSON.decode(response.responseText);
										if (result.success) {
											element.innerHTML = n;
											var num = n.substr(-4);
											// если сохранился штрих-код, предлагаем менять номер пробы
											Ext6.Msg.show({
												title: 'Внимание',
												msg: 'Штрих код изменен на №'+ n +'. Изменить номер пробы на №'+num+'?',
												buttons: Ext6.Msg.YESNO,
												fn: function(btn) {
													if (btn === 'yes') {
														win.getLoadMask("Сохранение номера пробы...").show();
														Ext6.Ajax.request({
															params: {
																EvnLabSample_id: EvnLabSample_id,
																EvnLabSample_ShortNum: num
															},
															url: '/?c=EvnLabSample&m=saveNewEvnLabSampleNum',
															callback: function(options, success, response) {
																win.getLoadMask().hide();
																if(success) {
																	// do nothing
																}
															}
														});
													}
												},
												icon: Ext6.MessageBox.QUESTION
											});
										}
									}
								}
							});
						}
					}
				}
		});

		// cmp.focus(true, 500);
	},
	/**
	 * Печать штрих-кодов
	 */
	printBarcodes: function() {
		var s = "";
		this.labRequestGrid.getSelectionModel().getSelection().forEach(function (el) {
			if (!Ext6.isEmpty(el.data.EvnLabSample_ids)) {
				if (!Ext6.isEmpty(s)) {
					s = s + ",";
				}
				s = s + el.data.EvnLabSample_ids.replace(/\s+/g,'');
			}
		});

		if (!Ext6.isEmpty(s)) {
			var Report_Params = '&s=' + s;

			if ( getLisOptions() ) {
				var ZebraDateOfBirth = (getLisOptions().ZebraDateOfBirth) ? 1 : 0;
				var ZebraUsluga_Name = (getLisOptions().ZebraUsluga_Name) ? 1: 0;
				var ZebraDirect_Name = (Ext.globalOptions.lis.ZebraDirect_Name) ? 1 : 0;
				var ZebraFIO = (Ext.globalOptions.lis.ZebraFIO) ? 1 : 0;
				Report_Params = Report_Params + '&paramPrintType=1';
				Report_Params = Report_Params + '&marginTop=' + getLisOptions().labsample_barcode_margin_top;
				Report_Params = Report_Params + '&marginBottom=' + getLisOptions().labsample_barcode_margin_bottom;
				Report_Params = Report_Params + '&marginLeft=' + getLisOptions().labsample_barcode_margin_left;
				Report_Params = Report_Params + '&marginRight=' + getLisOptions().labsample_barcode_margin_right;
				Report_Params = Report_Params + '&width=' + getLisOptions().labsample_barcode_width;
				Report_Params = Report_Params + '&height=' + getLisOptions().labsample_barcode_height;
				Report_Params = Report_Params + '&barcodeFormat=' + getLisOptions().barcode_format;
				Report_Params = Report_Params + '&ZebraDateOfBirth=' + ZebraDateOfBirth;
				Report_Params = Report_Params + '&ZebraUsluga_Name=' + ZebraUsluga_Name;
				Report_Params = Report_Params + '&paramFrom=' + ZebraDirect_Name;
           		Report_Params = Report_Params + '&paramFIO=' + ZebraFIO;
			}

			Report_Params = Report_Params + '&paramLpu=' + getGlobalOptions().lpu_id

			printBirt({
				'Report_FileName': (Ext.globalOptions.lis.use_postgresql_lis ? 'barcodesprint_resize_pg' : 'barcodesprint_resize') + '.rptdesign',
				'Report_Params': Report_Params,
				'Report_Format': 'pdf'
			});
		}

		return false;
	},
	/**
	 * Печать списка проб
	 */
	printLabSmplList: function() {
		var s = "";
		this.labRequestGrid.getSelectionModel().getSelection().forEach(function (el) {
			if (!Ext6.isEmpty(el.data.EvnLabRequest_id)) {
				if (!Ext6.isEmpty(s)) {
					s = s + ",";
				}
				s = s + el.data.EvnLabRequest_id;
			}
		});

		if (!Ext6.isEmpty(s)) {
			var Report_Params = '&s=' + s + '&paramLpu=' + getGlobalOptions().lpu_id;

			printBirt({
				'Report_FileName': 'EvnLabSmpl_List_pg.rptdesign',
				'Report_Params': Report_Params,
				'Report_Format': 'xls'
			});
		}

		return false;
	},
	/**
	 * Просмотр списка записанных на групповую бирку
	 */
	showRecList: function(TimetableGraf_id){
		if(!TimetableGraf_id)
			return false;
		getWnd('swTimeTableGrafRecListWindow').show({
			TimetableGraf_id: TimetableGraf_id,
			callback: function() {
			}
		});
	},
	showEvnPLDispMenu: function(el, rowIndex) {
		var win = this,
			record = win.mainGrid.getStore().getAt(rowIndex);
		win.EvnPLDispMenu.selectedRecord = record;

		if(record.get('dispneed')) {
			win.EvnPLDispMenu.queryBy(function(item) {
				item.hide();
			});

			var DispClass_id = 1, id = 0, IsEndStage = false;

			if(!Ext6.isEmpty(record.get('EvnPLDispDop13sec_id'))) {
				DispClass_id = 2;
				id = record.get('EvnPLDispDop13sec_id');
				IsEndStage = record.get('EvnPLDispDop13sec_IsEndStage') == 2;
			} else if(!Ext6.isEmpty(record.get('EvnPLDispDop13_id'))) {
				IsEndStage = record.get('EvnPLDispDop13_IsEndStage') == 2;
				id = record.get('EvnPLDispDop13_id');
			}

			var m = win.EvnPLDispMenu.queryBy(function(item) {
				return item.DispClass_id == DispClass_id;
			});

			m.forEach(function(item) {
				item.show();
			});

			var m = win.EvnPLDispMenu.queryBy(function(item) {
				return item.DispClass_id == -DispClass_id;
			});
			m.forEach(function(item) {
				item.setVisible(!IsEndStage);
			});

			win.EvnPLDispMenu.showBy(el);
		}
	},
	initComponent: function() {
		var win = this;
		var EvnPLDispMenuHandler = function() {
			var record = win.EvnPLDispMenu.selectedRecord;
			if(this.DispClass_id > 0) {
				var openEvn = {
					object: 'EvnPLDispDop13',
					object_value: record.get('EvnPLDispDop13_id')
				};
				win.openPersonEmkWindow(openEvn);
			} else {
				//отказ от диспансеризации, профосмотров и т.п. (EvnPLDisp...)
				EvnPLDispRefuse(record.get('Person_id'), -this.DispClass_id, getGlobalOptions().CurMedStaffFact_id, null);
			}
		};
		win.EvnPLDispMenu = new Ext6.menu.Menu({
			selectedRecord: null,
			items: [{
				text: 'Пройти диспансеризацию',//Диспансеризация взрослого населения - 1 этап
				itemId: 'EvnPLDispMenu_Disp1',
				object: 'EvnPLDispDop13',
				DispClass_id: 1,
				EvnClass_id: 101,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пациент отказался от диспансеризации',//Отказ 1 этап
				itemId: 'EvnPLDispMenu_Disp1refuse',
				DispClass_id: -1,
				EvnClass_id: 101,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пройти диспансеризацию',//Диспансеризация взрослого населения - 2 этап
				itemId: 'EvnPLDispMenu_Disp2',
				DispClass_id: 2,
				EvnClass_id: 101,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пациент отказался от диспансеризации',//Отказ 2 этап
				itemId: 'EvnPLDispMenu_Disp2refuse',
				DispClass_id: -2,
				EvnClass_id: 101,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пройти профосмотр',
				itemId: 'EvnPLDispMenu_Prof',
				object: 'EvnPLDispProf',
				DispClass_id: 5,
				EvnClass_id: 101,
				disabled: true //временно
			}, {
				text: 'Пациент отказался от профосмотра',
				itemId: 'EvnPLDispMenu_ProfRefuse',
				DispClass_id: -5,
				EvnClass_id: 101,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}]
		});

		win.gridColumnConfig = [
			{text: 'Запись', tdCls: 'padLeft', width: 150, dataIndex: 'TimetableGraf_begTime',
				renderer: function(val, metaData, record) {
				var resStr = val;
					if (record && record.get('TimetableType_id') == 14){
						var group = "<span class='timetable-type-group-ext6' data-qtip='Групповой приём'></span>";
						resStr = "<span style='float:left;padding-right:4px;'>"+resStr+"</span>"+group;
					}

				return resStr;
				}
			},
			{text: 'Приём', width: 80, dataIndex: 'TimetableGraf_factTime', renderer: function(val, metaData, record) {
				if (!val) {
					var current_date = Date.parseDate(win.curDate + ' ' + win.curTime, 'd.m.Y H:i:s');
					var TimetableGraf_Date = null;
					if (record.get('TimetableGraf_Date')) {
						TimetableGraf_Date = Date.parseDate(record.get('TimetableGraf_Date').dateFormat('d.m.Y') + ' ' + record.get('TimetableGraf_begTime'), 'd.m.Y H:i');
					}
					if (record.get('Person_id') && TimetableGraf_Date < current_date) {
						// если дата прошла. то не явился
						if (metaData) {
							metaData.tdAttr = 'data-qtip="Не явился"';
						}
						return '<span style="color:red;">н</span>';
					} else {
						return '';
					}
				}
				return Ext6.util.Format.date(val, 'H:i');
			}},
			{text: '', width: 280, minWidth: 280, maxWidth: 380, dataIndex: 'Person_FIO', flex: 1, filter: {
				type: 'string',
				xtype: 'textfield',
				triggers: {
					search: {
						cls: 'x6-form-search-trigger',
						handler: function() {
							// ?
						}
					}
				},
				anchor: '-30',
				emptyText: 'ФИО'
			}, renderer: function(val, metaData, rec) {
				//Для группового приема публикуем надпись + иконку
				if(rec && rec.get('TimetableType_id') == 14
					&& !Ext6.isEmpty(rec.get('TimeTableGraf_countRec')) && parseInt(rec.get('TimeTableGraf_countRec'))>0)
					val = 'Групповой приём';
				if (!val) {
					return '';
				}

				var tooltip = '',
					spanClass = '',
					spanText = '';
				switch (rec.get('TimetableType_id')) {
					case 13:
						tooltip = 'Видеосвязь';
						spanClass = 'videoVisitIcon';
						break;
					case 14:
						tooltip = 'Просмотр записавшихся';
						spanClass = 'group-visit';
						if(rec.get('TimeTableGraf_countRec') && rec.get('TimeTableGraf_PersRecLim')){
							spanText = '<a href="#" ' +
								'onclick="Ext6.getCmp(\'' + win.id + '\').showRecList(' +
								"'" + rec.get('TimetableGraf_id') + "'" +
								')">'+'('+rec.get('TimeTableGraf_countRec')+' из '+rec.get('TimeTableGraf_PersRecLim')+')'+'</a>';
						}
						break;
					default:
						switch (rec.get('VizitType')) {
							case 2:
								tooltip = 'Повторный прием';
								spanClass = 'secondVisitIcon';
								break;
							case 3:
								tooltip = 'Диспансерный учет';
								spanClass = 'dispVisitIcon';
								break;
							default:
								tooltip = 'Первичный прием';
								spanClass = 'firstVisitIcon';
								break;
						}
						break;
				}
				var PersonQuarantine = "";
				if(rec.get('PersonQuarantine_IsOn')){
					var PQ_tooltip = "Пациент на карантине.";
					if(rec.get('PersonQuarantine_begDT')){
						PQ_tooltip = "Карантин с "+ Ext6.util.Format.date(rec.get('PersonQuarantine_begDT'), "d.m.Y");
					}
					PersonQuarantine = "<span style='float: left;' class='quarantined-patient' data-qtip='"+PQ_tooltip+"'></span>";
				}
				return PersonQuarantine+"<span style='float: left;'>" + toUpperCaseFirstLetter(val) + "</span><span class='" + spanClass + "' data-qtip='"+tooltip+"'>"+spanText+"</span>";
			}},
			{text: 'Д/Р (Возраст)', width: 120, dataIndex: 'Person_BirthDay', renderer: function(val) {
				if (!val) {
					return '';
				}

				var s = Ext6.util.Format.date(val, 'd.m.Y');
				s = s + ' (';
				var years = swGetPersonAge(val);
				if (years > 0) {
					s = s + swGetPersonAge(val);
				}
				if (years <= 3) { // до 3 лет вместе с месяцами
					if (years > 0) {
						s = s + 'г ';
					}
					s = s + swGetPersonAgeMonth(val) + 'м';
				}
				s = s + ')';

				return s;
			}},
			{text: 'Телефон', width: 150, dataIndex: 'Person_Phone', renderer: function(val, metaData, record) {
				if (val && val.length == 10) {
					return '+7' + val;
				}

				return val;
			}},
			{text: 'Напр.', width: 120, dataIndex: 'EvnDirection_Num', renderer: function(val, metaData, record) {
				if (record.get('IsEvnDirection') && record.get('IsEvnDirection') == 'true') {				var tooltip = 'Направление №' + val + ' ' + record.get('EvnDirection_setDate');
					return "<a data-qtip='" + tooltip + "' href='javascript:openEvnDirectionEditWindow(" + record.get('EvnDirection_id') + ", " + record.get('Person_id') + ");' class='dirInfoLink'></a>";
				}

				return '';
			}},
			{text: 'Льготы', width: 80, dataIndex: 'Person_Lgots', renderer: function(val, metaData, record) {
				var s = '';
				var addClass = "";
				var isRefuse = false;
				if (record.get('Person_IsRefuse') && record.get('Person_IsRefuse') == 'true') {
					addClass += " lgot_refuse";
					isRefuse = true;
				}
				if (record.get('Person_IsFedLgot') && record.get('Person_IsFedLgot') == 'true') {
					s += "<span class='lgot_fl" + addClass + "' data-qtip='" + (isRefuse ? "Пациент отказался от федеральной льготы" : "Федеральная льгота") + "'>ФЛ</span>";
				}
				if (record.get('Person_IsRegLgot') && record.get('Person_IsRegLgot') == 'true') {
					s += "<span class='lgot_rl" + addClass + "' data-qtip='" + (isRefuse ? "Пациент отказался от региональной льготы" : "Региональная льгота") + "'>РЛ</span>";
				}
				return s;
			}},
			{text: 'ДВН', width: 80, dataIndex: 'DVN',
				//hidden: (getRegionNick() != 'krym'),
				renderer: function(val, metaData, record) {
					var s = '',
					icon = '',
					tip = '';
				if(!record.get('MedStaffRegion_id')) return '';//не наш пациент
				
				if(record.get('Person_AgeEndYear')>17) {
					if( record.get('Person_AgeEndYear')>39 || record.get('Person_AgeEndYear') % 3 == 0 ) {//требуется диспансеризация
						icon = 'disp-icon-need'; tip = 'Подлежит диспансеризации';
						record.set('dispneed',true);
					}
				}
				
				if( !Ext6.isEmpty(record.get('EvnPLDispDop13_id')) && record.get('EvnPLDispDop13_id')>0 ) {//состоит на диспансеризации
					if(record.get('EvnPLDispDop13_IsEndStage')==2) {
						icon = 'disp-icon-complete';
						tip = 'Диспансеризация пройдена '+record.get('EvnPLDispDop13_Date');
					} else {
						icon = 'disp-icon-process';
						tip = 'Диспансеризация не закончена';
					}
				}
				else
				if( !Ext6.isEmpty(record.get('DispRefuse_Date')) ) {//есть отказ
					icon = 'disp-icon-need'; tip = 'Отказ от диспансеризации '+record.get('DispRefuse_Date');
				}
				
				if(icon!='') {
					s = "<span class='"+icon+"' data-qtip='"+tip+"' onClick='Ext6.getCmp(\""+win.id+"\").showEvnPLDispMenu(this,"+metaData.rowIndex+")'></span>";
				}
				return s;
				}
			},
			{text: 'Участок', width: 80, dataIndex: 'LpuRegion_Name'},
			{text: '№ Ам. карты', width: 120, dataIndex: 'PersonCard_Code'},
			{text: 'РЗ', width: 100, dataIndex: 'Person_Bdz', renderer: function(val, metaData, record) {
				var s = '';
				if (record.get('Person_IsBDZ') && record.get('Person_IsBDZ') == 'true') {
					s += "<span class='lgot_rz' data-qtip='Регистр застрахованных'>РЗ</span>";
				}
				return s;
			}},
			{text: 'Оператор', width: 160, dataIndex: 'pmUser_Name'},
			{text: 'Записан к', width: 160, dataIndex: 'MSF_Person_Fin'},
			{text: '', flex: 1, dataIndex: 'empty'}
		];

		// не переживайте, этот плагин инициализируется, но после инициализации ЭО
		// на это пришлось пойти из-за того что плагин старый и не поддерживает grid.reconfigure();
		var gridPlugins = [Ext6.create('Ext6.ux.GridHeaderFilters', {
			enableTooltip: false,
			reloadOnChange: false,
			pluginId:'gridHeaderPlugin'
		})];

		win.mainGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			requires: [
				'Ext6.ux.GridHeaderFilters'
			],
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				})
			],
			features: [
				Ext6.create('Ext6.grid.feature.Grouping', {
					enableGroupingMenu: false,
					groupHeaderTpl: new Ext6.XTemplate(
						'{[this.formatName(values.rows)]} {[this.countTotal(values.rows)]}',
						{
							formatName: function(rows) {
								var s = '';
								if (rows[0] && rows[0].get('TimetableGraf_Date')) {
									var date = rows[0].get('TimetableGraf_Date');
									s = s + '<span class="day_title">' + Ext6.util.Format.date(date, 'd.m.Y');
									s = s + ' ' + sw4.getRussianDayOfWeek(Ext6.util.Format.date(date, 'w')) + '</span>';
								}
								return s;
							},
							countTotal: function(rows) {
								var s = '';
								var current_date = Date.parseDate(win.curDate + ' ' + win.curTime, 'd.m.Y H:i:s');
								// надо посчитать каунты и вывести их в заголовке
								var birCount = 0;
								var zapCount = 0;
								var prinCount = 0;
								var bzCount = 0;
								var neyavCount = 0;
								for (var key in rows) {
									if (typeof rows[key] == 'object') {
										if (rows[key].get('TimetableGraf_id')) {
											if (rows[key].get('TimetableGraf_begTime') != 'б/з') {
												birCount++;
												if (rows[key].get('Person_id')) {
													zapCount++;
												}
											} else {
												bzCount++;
											}
										}
										if (rows[key].get('TimetableGraf_factTime')) {
											prinCount++;
										} else if (rows[key].get('TimetableGraf_Date')) {
											var TimetableGraf_Date = Date.parseDate(rows[key].get('TimetableGraf_Date').dateFormat('d.m.Y') + ' ' + rows[key].get('TimetableGraf_begTime'), 'd.m.Y H:i');
											if (rows[key].get('Person_id') && TimetableGraf_Date < current_date) {
												neyavCount++;
											}
										}
									}
								}
								s = s + '<span class="headCount">Бирок: <span class="headCountNum">' + birCount + '</span></span>';
								s = s + '<span class="headCount">Записано: <span class="headCountNum">' + zapCount + '</span></span>';
								s = s + '<span class="headCount">Принято: <span class="headCountNum">' + prinCount + '</span></span>';
								s = s + '<span class="headCount">Без записи: <span class="headCountNum">' + bzCount + '</span></span>';
								s = s + '<span class="headCount">Не явились: <span class="headCountNum">' + neyavCount + '</span></span>';
								return s;
							}
						}
					)
				})
			],
			tbar: {
				xtype: 'toolbar',
				defaults: {
					margin: '0 4 0 0',
					padding: '4 10'
				},
				height: 40,
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					margin: '0 0 0 6',
					text: 'Обновить',
					itemId: 'action_refresh',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_refresh',
					handler: function() {
						win.scheduleRefresh();
					}
				}, {
					text: 'Открыть ЭМК',
					itemId: 'action_openemk',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_openemk',
					handler: function() {
						win.openPersonEmkWindow();
					}
				}, {
					text: 'Считать с карты',
					itemId: 'action_readcard',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_readcard',
					handler: function() {
						win.readFromCard();
					}
				}, {
					text: 'Принять без записи',
					itemId: 'action_withoutrecord',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_withoutrecord',
					handler: function() {
						win.withoutRecord();
					}
				}, {
					text: 'Вызвать СМП',
					itemId: 'action_smp',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'smp16-2017',
					handler: function() {
						inDevelopmentAlert();
					}
				}, {
					text: 'Записать',
					tooltip: 'Записать пациента к себе на выбранную бирку.',
					itemId: 'action_record',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_record',
					handler: function() {
						win.recordPatient();
					}
				}, {
					text: 'Записать из очереди',
					tooltip: 'Поиск направленных пациентов и запись в свое расписание. Недоступно, если прием ведется по электронной очереди.',
					itemId: 'action_recordfromqueue',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_recordfromqueue',
					handler: function() {
						win.recordPatientFromQueue();
					}
				}, {
					text: 'Отменить',
					tooltip: 'Освободить бирку и отменить прием.',
					itemId: 'action_cancel',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_cancel',
					handler: function() {
						win.reject();
					}
				}, {
					text: 'В очередь',
					tooltip: 'Вернуть направление в очередь и освободить бирку.',
					itemId: 'action_toqueue',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_toqueue',
					handler: function() {
						win.returnToQueue();
					}
				}, {
					text: 'Перезаписать',
					tooltip: 'Записать пациента на другое время.',
					itemId: 'action_rewrite',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_rewrite',
					handler: function() {
						win.rewrite();
					}
				}, {
					text: 'Печать',
					itemId: 'action_print',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					menu: new Ext6.menu.Menu({
						userCls: 'menuWithoutIcons',
						items: [{
							text: 'Печать списка',
							handler: function() {
								Ext6.ux.GridPrinter.print(win.mainGrid);
							}
						}, {
							text: 'Проф. осмотры',
							handler: function() {
								win.checkEvnPLDispDop();
							}
						}, {
							text: 'Печать списка пациентов',
							hidden: (getRegionNick() != 'ufa' && getRegionNick() != 'kz'),
							handler: function() {
								win.printPacList();
							}
						}]
					})
				}]
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onRecordSelect();
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					win.onDblClick();
				}
			},
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store) {
					var cls = '';
					if (record.get('PersonQuarantine_IsOn')) {
						cls = cls + 'x-grid-rowbackred ';
					}
					return cls;
				}
			},
			store: {
				groupField: 'TimetableGraf_Date',
				fields: [
					{name: 'TimetableGraf_id', type: 'int'},
					{name: 'TimeTableGraf_countRec', type: 'int'},
					{name: 'TimeTableGraf_PersRecLim', type: 'int'},
					{name: 'MedStaffFact_id', type: 'int'},
					{name: 'LpuSection_id', type: 'int'},
					{name: 'Person_id', type: 'int', allowNull: true},
					{name: 'Person_IsUnknown', type: 'int'},
					{name: 'Server_id', type: 'int'},
					{name: 'PersonEvn_id', type: 'int'},
					{name: 'Evn_id', type: 'int'},
					{name: 'Person_IsEvents'},
					{name: 'pmUser_updId', type: 'int'},
					{name: 'TimetableGraf_Date', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'TimetableGraf_begTime'},
					{name: 'TimetableGraf_factTime', type: 'date', dateFormat: 'H:i'},
					{name: 'Person_FIO'},
					{name: 'Person_Surname'},
					{name: 'Person_Firname'},
					{name: 'Person_Secname'},
					{name: 'Person_Age', type: 'int'},
					{name: 'Person_AgeEndYear', type: 'int'},
					{name: 'EvnPLDispDop13_id', type: 'int'},
					{name: 'EvnPLDispProf_id', type: 'int'},
					{name: 'PersonPrivilegeWOW_id', type: 'int'},
					{name: 'MedStaffRegion_id', type: 'int'},
					{name: 'VizitType', type: 'int'},
					{name: 'TimetableType_id', type: 'int'},
					{name: 'Person_IsBDZ'},
					{name: 'Person_Phone'},
					{name: 'Person_IsFedLgot'},
					{name: 'Person_IsRegLgot'},
					{name: 'Person_IsRefuse'},
					{name: 'PersonQuarantine_IsOn', type: 'boolean'},
					{name: 'PersonQuarantine_begDT', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Lpu_id', type: 'int'},
					{name: 'PersonCard_Code'},
					{name: 'Lpu_Nick'},
					{name: 'LpuRegion_Name'},
					{name: 'Person_BirthDay', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'TimetableGraf_updDT', type: 'date', dateFormat: 'd.m.Y H:i'},
					{name: 'pmUser_Name'},
					{name: 'MSF_Person_Fin'},
					{name: 'EvnDirection_id', type: 'int'},
					{name: 'EvnQueue_id', type: 'int'},
					{name: 'EvnStatus_id', type: 'int'},
					{name: 'MSF_Person_Fin'},
					{name: 'EvnDirection_setDate'},
					{name: 'EvnDirection_Num'},
					{name: 'LpuSectionProfile_Name'},
					{name: 'IsEvnDirection'},
					{name: 'PersonEncrypHIV_Encryp'},
					{name: 'ARMType_id', type: 'int'},
					{name: 'ElectronicTalon_Num'},
					{name: 'ElectronicTalonStatus_Name'},
					{name: 'ElectronicService_id', type: 'int'},
					{name: 'ElectronicTalonStatus_id', type: 'int'},
					{name: 'ElectronicTalon_id', type: 'int'},
					{name: 'EvnDirection_uid', type: 'int'},
					{name: 'toElectronicService_id', type: 'int'},
					{name: 'fromElectronicService_id', type: 'int'},
					{name: 'ElectronicTreatment_id', type: 'int'},
					{name: 'ElectronicTreatment_Name'}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=TimetableGraf6E&m=loadPolkaWorkPlaceList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'TimetableGraf_begTime'
				],
				listeners: {
					load: function() {
						win.onRecordSelect();
					}
				}
			},
			columns: win.gridColumnConfig
		});

		win.homeVisitGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			requires: [
				'Ext6.ux.GridHeaderFilters'
			],
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				}),
				Ext6.create('Ext6.ux.GridHeaderFilters', {
					enableTooltip: false,
					reloadOnChange: false
				})
			],
			features: [
				Ext6.create('Ext6.grid.feature.Grouping', {
					enableGroupingMenu: false,
					groupHeaderTpl: new Ext6.XTemplate(
						'{[this.formatName(values.rows)]} {[this.countTotal(values.rows)]}',
						{
							formatName: function(rows) {
								var s = '';
								if (rows[0] && rows[0].get('HomeVisit_setDate')) {
									var date = rows[0].get('HomeVisit_setDate');
									s = s + '<span class="day_title">' + Ext6.util.Format.date(date, 'd.m.Y');
									s = s + ' ' + sw4.getRussianDayOfWeek(Ext6.util.Format.date(date, 'w')) + '</span>';
								}
								return s;
							},
							countTotal: function(rows) {
								var s = '';
								// надо посчитать каунты и вывести их в заголовке
								var count = 0;
								var obslCount = 0;
								var cancelCount = 0;
								for (var key in rows) {
									if (typeof rows[key] == 'object') {
										if (rows[key].get('HomeVisit_id')) {
											count++;
											if (rows[key].get('HomeVisitStatus_id') == 4) {
												obslCount++;
											} else if (rows[key].get('HomeVisitStatus_id') == 5) {
												cancelCount++;
											}
										}
									}
								}
								s = s + '<span class="headCount">Вызовов: <span class="headCountNum">' + count + '</span></span>';
								s = s + '<span class="headCount">Обслужено: <span class="headCountNum">' + obslCount + '</span></span>';
								s = s + '<span class="headCount">Отменено: <span class="headCountNum">' + cancelCount + '</span></span>';
								return s;
							}
						}
					)
				})
			],
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store){
					var cls = '';
					if (record.get('HomeVisitStatus_id') == 4 || record.get('HomeVisitStatus_id') == 5) {
						cls = cls + 'x-grid-rowgray ';
					}
					return cls;
				}
			},
			tbar: {
				xtype: 'toolbar',
				defaults: {
					margin: 0
				},
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					text: 'Обновить',
					itemId: 'action_refresh',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_refresh',
					handler: function() {
						win.scheduleRefresh();
					}
				}, {
					text: 'Открыть ЭМК',
					itemId: 'action_openemk',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_openemk',
					handler: function() {
						win.openPersonEmkWindow();
					}
				}, {
					text: 'Добавить',
					itemId: 'action_add',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_record',
					handler: function() {
						win.addHomeVisit();
					}
				}, {
					text: 'Сменить статус',
					itemId: 'action_status',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_rewrite',
					menu: [{
						name: 'action_new',
						text: langs('Новый'),
						handler: function () {
							win.updateHomeVisitStatus(1)
						}
					}, {
						name: 'action_confirm',
						text: langs('Одобрен'),
						handler: function () {
							win.updateHomeVisitStatus(3)
						}
					}, {
						name: 'action_cancel',
						text: 'Отменен',
						handler: function () {
							win.updateHomeVisitStatus(5)
						}
					}, {
						name: 'action_deny',
						text: 'Отказ',
						handler: function () {
							win.updateHomeVisitStatus(2)
						}
					}]
				}, {
					text: 'Печать',
					itemId: 'action_print',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					menu: [{
						text: 'Печать списка',
						handler: function() {
							Ext6.ux.GridPrinter.print(win.homeVisitGrid);
						}
					}]
				}]
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onHomeVisitRecordSelect();
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					win.onHomeVisitDblClick();
				}
			},
			store: {
				groupField: 'HomeVisit_setDate',
				fields: [
					{name: 'HomeVisit_id', type: 'int'},
					{name: 'Person_id', type: 'int'},
					{name: 'Server_id', type: 'int'},
					{name: 'PersonEvn_id', type: 'int'},
					{name: 'Person_FIO'},
					{name: 'Person_Surname'},
					{name: 'Person_Firname'},
					{name: 'Person_Secname'},
					{name: 'HomeVisit_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Person_BirthDay', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Address_Address'},
					{name: 'LpuRegion_Name'},
					{name: 'HomeVisit_Symptoms'},
					{name: 'HomeVisitCallType_Name'},
					{name: 'HomeVisit_Phone'},
					{name: 'HomeVisitStatus_id', type: 'int'}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=HomeVisit6E&m=getHomeVisitList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'HomeVisit_setDate',
					'HomeVisitStatus_Name'
				],
				listeners: {
					load: function() {
						win.onHomeVisitRecordSelect();
					}
				}
			},
			columns: [
				{text: 'Статус', tdCls: 'padLeft', width: 120, dataIndex: 'HomeVisitStatus_Name'},
				{text: '', width: 280, minWidth: 280, maxWidth: 380, dataIndex: 'Person_FIO', flex: 1, filter: {
					type: 'string',
					xtype: 'textfield',
					triggers: {
						search: {
							cls: 'x6-form-search-trigger',
							handler: function() {
								// ?
							}
						}
					},
					anchor: '-30',
					emptyText: 'ФИО'
				}},
				{text: 'Д/Р (Возраст)', width: 120, dataIndex: 'Person_BirthDay', renderer: function(val) {
					if (!val) {
						return '';
					}

					var s = Ext6.util.Format.date(val, 'd.m.Y');
					s = s + ' (';
					var years = swGetPersonAge(val);
					if (years > 0) {
						s = s + swGetPersonAge(val);
					}
					if (years <= 3) { // до 3 лет вместе с месяцами
						if (years > 0) {
							s = s + 'г ';
						}
						s = s + swGetPersonAgeMonth(val) + 'м';
					}
					s = s + ')';

					return s;
				}},
				{text: 'Адрес места вызова', width: 300, dataIndex: 'Address_Address'},
				{text: 'Уч.', width: 80, dataIndex: 'LpuRegion_Name'},
				{text: 'Повод', width: 200, dataIndex: 'HomeVisit_Symptoms'},
				{text: 'Тип вызова', width: 120, dataIndex: 'HomeVisitCallType_Name'},
				{text: 'Телефон', width: 150, dataIndex: 'HomeVisit_Phone', renderer: function(val, metaData, record) {
					if (val && val.length == 10) {
						return '+7' + val;
					}

					return val;
				}},
				{text: '', flex: 1, dataIndex: 'empty'}
			]
		});

		win.labRequestGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			requires: [
				'Ext6.ux.GridHeaderFilters'
			],
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				}),
				Ext6.create('Ext6.ux.GridHeaderFilters', {
					enableTooltip: false,
					reloadOnChange: false
				})
			],
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store){
					var cls = 'x-grid-state-' + record.get('EvnStatus_id');
					return cls;
				}
			},
			tbar: {
				xtype: 'toolbar',
				defaults: {
					margin: 0
				},
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					text: 'Добавить',
					itemId: 'action_add',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_add',
					handler: function() {
						win.openLabRequestEditWindow('add');
					}
				}, {
					text: 'Изменить',
					itemId: 'action_edit',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_edit',
					handler: function() {
						win.openLabRequestEditWindow('edit');
					}
				}, {
					text: 'Просмотреть',
					itemId: 'action_view',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_view',
					handler: function() {
						win.openLabRequestEditWindow('view');
					}
				}, {
					text: 'Отклонить',
					itemId: 'action_cancel',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_cancel',
					handler: function() {
						win.rejectEvnLabRequest();
					}
				}, {
					text: 'Обновить',
					itemId: 'action_refresh',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_refresh',
					handler: function() {
						win.scheduleRefresh();
					}
				}, {
					text: 'Печать',
					itemId: 'action_print',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					menu: [{
						text: 'Печать протоколов исследования',
						itemId: 'printProtocol',
						handler: function() {
							var records = win.labRequestGrid.getSelectionModel().getSelection();
							var EvnDirection_ids = [];
							for (var i = 0; i < records.length; i++) {
								if (!Ext6.isEmpty(records[i].get('EvnDirection_id')) && records[i].get('EvnDirection_id') > 0) {
									EvnDirection_ids = EvnDirection_ids.concat(records[i].get('EvnDirection_id').toString());
								}
							}

							if (!Ext6.isEmpty(EvnDirection_ids) && EvnDirection_ids.length > 0) {
								// получаем EvnUslugaPar_id's для заявок, по ним печатаем с использованием нового шаблона
								win.getLoadMask('Получение данных заявок').show();
								// обновить на стороне сервера
								Ext6.Ajax.request({
									url: '/?c=EvnLabRequest&m=getEvnUslugaParForPrint',
									params: {
										EvnDirections: Ext6.JSON.encode(EvnDirection_ids)
									},
									callback: function (options, success, response) {
										win.getLoadMask().hide();
										if (success && response.responseText != '') {
											var result  = Ext6.JSON.decode(response.responseText);
											var Report_Params = '&paramEvnUslugaPar=';
											var ids = [];
											for (var i = 0; i < result.length; i++) {
												if (!Ext6.isEmpty(result[i].EvnUslugaPar_id))
													ids.push(result[i].EvnUslugaPar_id);
											}
											ids = ids.join(',');
											Report_Params += ids;
											printBirt({
												'Report_FileName': 'EvnParCard_list_pg.rptdesign',
												'Report_Params': Report_Params,
												'Report_Format': 'pdf'
											});
										}
									}
								});
							}
						}
					}, {
						text: 'Печать всего списка',
						itemId: 'printAll',
						handler: function() {
							Ext6.ux.GridPrinter.print(win.labRequestGrid);
						}
					}, {
						text: 'Печать списка выбранных',
						itemId: 'printSelected',
						handler: function() {
							var selections = win.labRequestGrid.getSelectionModel().getSelection();
							Ext6.ux.GridPrinter.print(win.labRequestGrid, {
								selections: selections, addNumberColumn: true
							});
						}
					}, {
						text: 'Печать штрих-кодов',
						itemId: 'printBarcodes',
						handler: function() {
							win.printBarcodes();
						}
					}, {
						text: 'Печать списка проб',
						itemId: 'printSampleList',
						handler: function() {
							win.printLabSmplList();
						}
					}]
				}, {
					text: 'Взять пробы',
					itemId: 'action_take_sample',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_take_sample',
					handler: function() {
						win.takeSample();
					}
				}, {
					text: 'Отмена взятия проб',
					itemId: 'action_cancel_sample',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_cancel_sample',
					handler: function() {
						win.cancelSample();
					}
				}, {
					text: 'Аутсорсинг',
					itemId: 'action_outsourcing_create',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_outsourcing_create',
					handler: function() {
						win.sendRequestsToLis();
					}
				}]
			},
			selType: 'checkboxmodel',
			selModel: {
				mode: 'MULTI',
				listeners: {
					select: function(model, record, index) {
						win.onEvnLabRequestRecordSelectionChange();
						var row = win.labRequestGrid.view.getRow(index);
						Ext6.fly(row).replaceCls("x-grid-state-" + record.get('EvnStatus_id'), "x-grid-state-" + record.get('EvnStatus_id') + "-selected");
					},
					deselect: function(model, record, index) {
						win.onEvnLabRequestRecordSelectionChange();
						var row = win.labRequestGrid.view.getRow(index);
						Ext6.fly(row).replaceCls("x-grid-state-" + record.get('EvnStatus_id') + "-selected", "x-grid-state-" + record.get('EvnStatus_id'));
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					win.openLabRequestEditWindow('edit');
				}
			},
			store: {
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnLabRequest&m=loadEvnLabRequestList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'EvnDirection_setDate'
				],
				listeners: {
					load: function() {
						win.onEvnLabRequestRecordSelectionChange();
					}
				}
			},
			columns: [
				{text: '', width: 280, minWidth: 280, maxWidth: 380, dataIndex: 'Person_ShortFio', flex: 1, filter: {
					type: 'string',
					xtype: 'textfield',
					triggers: {
						search: {
							cls: 'x6-form-search-trigger',
							handler: function() {
								// ?
							}
						}
					},
					anchor: '-30',
					emptyText: 'ФИО'
				}},
				{text: 'Запись', width: 130, dataIndex: 'TimetableMedService_begTime'},
				{text: 'Лаборатория', width: 200, dataIndex: 'MedService_Nick'},
				{text: 'Cito!', width: 60, dataIndex: 'EvnDirection_IsCito'},
				{text: 'Услуга (исследование)', width: 280, dataIndex: 'EvnLabRequest_UslugaName', renderer: function(value, cellEl, rec) {
					var result = '';
					if (!Ext6.isEmpty(value) && value[0] == "[" && value[value.length-1] == "]") {
						// разджейсониваем
						var uslugas = Ext6.JSON.decode(value);
						for(var k in uslugas) {
							if (uslugas[k].UslugaComplex_Name) {
								if (!Ext6.isEmpty(result)) {
									result += '<br />';
								}
								result += uslugas[k].UslugaComplex_Name;
							}
						}

						return result;
					} else {
						return value;
					}
				}},
				{text: 'Статус', width: 60, dataIndex: 'ProbaStatus', renderer: function(val, metaData, rec) {
					var n = rec.get('ProbaStatus');
					var qtip = '';

					switch(rec.get('ProbaStatus')) {
						case 'needmore':
							qtip = langs('Нужно взять две или более проб');
							break;
						case 'needone':
							qtip = langs('Нужно взять одну пробу');
							break;
						case 'notall':
							qtip = langs('Взяты не все пробы');
							break;
						case 'new':
							qtip = langs('Новая проба взята, но не отправлена на анализатор');
							break;
						case 'toanaliz':
							qtip = langs('Проба отправлена на анализатор (результатов нет)');
							break;
						case 'exec':
							qtip = langs('Выполнено. Есть результаты');
							break;
						case 'someOk':
							qtip = langs('Частично одобрено');
							break;
						case 'Ok':
							qtip = langs('Полностью одобрено');
							break;
						case 'bad':
							qtip = langs('Брак пробы');
							break;
					}

					return "<img data-qtip='"+qtip+"' src='../img/icons/lis-prob-"+n+".png'/>"
				}},
				{text: 'Номер пробы', width: 120, dataIndex: 'EvnLabRequest_SampleNum'},
				{text: 'Тесты', width: 55, dataIndex: 'EvnLabRequest_Tests'},
				{text: 'Отклонение', width: 100, dataIndex: 'EvnLabSample_IsOutNorm', renderer: function(val, metaData, rec) {
					if (rec.get('EvnLabSample_IsOutNorm') == 2) {
						return "<img src='../img/icons/warning16.png'/>"
					} else {
						return "";
					}
				}},
				{text: 'Штрих-код', width: 130, dataIndex: 'EvnLabRequest_FullBarCode', renderer: function(value, cellEl, rec) {
					var result = "";
					// разделить value по ,
					if (!Ext6.isEmpty(value)) {
						var val_array = value.split(',');
						for(var k in val_array) {
							if (!Ext6.isEmpty(val_array[k]) && typeof val_array[k] == 'string') {
								var valone_array = val_array[k].split(':');
								if (!Ext6.isEmpty(valone_array[1])) {
									result = result + "<div id='lrbarcode" + rec.get('EvnDirection_id') + "_" + valone_array[0].trim() + "_inp'></div><div id='lrbarcode" + rec.get('EvnDirection_id') + "_" + valone_array[0].trim() + "'>" + "<a href='javascript://' onClick='Ext6.getCmp(\"" + win.id + "\").showInputBarCodeField(\"lrbarcode" + rec.get('EvnDirection_id') + "_" + valone_array[0].trim() + "\", " + valone_array[0].trim() + ",this);'>" + valone_array[1].trim() + "</a></div>";
								}
							}
						}
					}

					return result;
				}},
				{text: '№ напр.', width: 80, dataIndex: 'EvnDirection_Num'},
				{text: 'Дата напр.', width: 100, dataIndex: 'EvnDirection_setDate'},
				{text: 'Кем направлен', width: 110, dataIndex: 'PrehospDirect_Name'},
				{text: '', flex: 1, dataIndex: 'empty'}
			]
		});

		win.dateMenu = Ext6.create('Ext6.date.RangeField', {
			hideLabel: true,
			autoWidth: true,
			itemId: 'datefilter',
			margin: 0,
			width: 210
		});

		win.dateMenu.addListener('set', function () {
			win.scheduleRefresh();
		});

		win.ElectronicQueuePanel = new sw4.Promed.ElectronicQueuePanel({
			ownerWindow: win,
			ownerGrid: win.mainGrid, // передаем грид для работы с ЭО
			gridColumnConfig: win.gridColumnConfig, // передаем параметры для реконфигуратора столбцов
			gridRefreshFn: function(options){win.scheduleRefresh(options)},
			applyCallActionFn: function(){ win.openPersonEmkWindow() }, // связываем метод открытия эмки
			layoutPanelId: 'swWorkPlacePolkaWindowLayout_' + win.id, // todo лэйаут для перерисовки в которой находится панель
			region: 'south',
			additionalGridPlugins: gridPlugins
		});

		win.leftMenu = new Ext6.menu.Menu({
			xtype: 'menu',
			floating: false,
			dock: 'left',
			cls: 'leftPanelWP',
			border: false,
			padding: 0,
			defaults: {
				margin: 0
			},
			mouseLeaveDelay: 100,
			collapsedWidth: 30,
			collapseMenu: function() {
				if (!win.leftMenu.activeChild || win.leftMenu.activeChild.hidden) {
					clearInterval(win.leftMenu.collapseInterval); // сбрасывем
					win.leftMenu.getEl().setWidth(win.leftMenu.collapsedWidth); // сужаем
					win.leftMenu.body.setWidth(win.leftMenu.collapsedWidth - 1); // сужаем
					win.leftMenu.deactivateActiveItem();
				}
			},
			listeners: {
				mouseover: function() {
					clearInterval(win.leftMenu.collapseInterval); // сбрасывем
					win.leftMenu.getEl().setWidth(win.leftMenu.items.items[0].getWidth());
					win.leftMenu.body.setWidth(win.leftMenu.items.items[0].getWidth() - 1);
				},
				afterrender : function(scope) {
					win.leftMenu.setWidth(win.leftMenu.collapsedWidth); // сразу сужаем
					win.leftMenu.setZIndex(10); // fix zIndex чтобы панель не уезжала под грид

					this.el.on('mouseout', function() {
						// сужаем, если нет подменю
						clearInterval(win.leftMenu.collapseInterval); // сбрасывем
						win.leftMenu.collapseInterval = setInterval(win.leftMenu.collapseMenu, 100);
					});
				}
			},
			items: [{
				iconCls: 'reports16-2017',
				handler: function() {
					if (sw.codeInfo.loadEngineReports) {
						getWnd('swReportEndUserWindow').show();
					} else {
						getWnd('reports').load({
							callback: function (success) {
								sw.codeInfo.loadEngineReports = success;
								getWnd('swReportEndUserWindow').show();
							}
						});
					}
				},
				text: 'Отчеты'
			}, {
				iconCls: 'timetable16-2017',
				handler: function() {
					if (getRegionNick().inlist(['krym'])) {
						//новый интерфейс
						getWnd('swTimetableScheduleViewWindow').show({
							userMedStaffFact: win.userMedStaffFact,
							ARMType: win.ARMType
						});
					} else {
						getWnd('swTTGScheduleEditWindow').show();
					}
				},
				text: 'Работа с расписанием'
			}, {
				iconCls: 'signalinfo16-2017',
				handler: function () {
					getWnd('swSignalInfoForDoctorForm').show({
						userMedStaffFact: win.userMedStaffFact
					});
				},
				text: langs('Сигнальная информация для врача'),
				tooltip: langs('Сигнальная информация для врача'),
				hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa')
			}, {
				iconCls: 'evnpl16-2017',
				handler: function() {
					getWnd('swEvnPLSearchWindow').show();
				},
				text: 'Поиск ТАП'
			}, {
				iconCls: 'distance-monitoring',
				handler: function() {
					getWnd('swRemoteMonitoringWindow').show({
						userMedStaffFact: win.userMedStaffFact,
						ARMType: win.ARMType
					});
				},
				text: 'Дистанционный мониторинг'
			}, {
				iconCls: 'prof16-2017',
				menu: [{
					text:langs('Диспансеризация взрослого населения'),
					hidden: getRegionNick().inlist(['by','kz']),
					menu: [{
						text: langs('Обследования ВОВ: Поиск'),
						handler: function() {
							getWnd('EvnPLWOWSearchWindow').show();
						}
					}, '-', {
						text: (getRegionNick().inlist(['ufa', 'ekb', 'penza'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
						handler: function () {
							getWnd('swPersonPrivilegeWOWSearchWindow').show();
						}
					}, {
						text: (getRegionNick().inlist(['ufa', 'ekb', 'penza'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поточный ввод') : langs('Регистр ВОВ: Поточный ввод'),
						handler: function () {
							getWnd('swPersonPrivilegeWOWStreamInputWindow').show();
						}
					}, '-', {
						text: MM_POL_PERSDDSEARCH,
						handler: function () {
							getWnd('swPersonDopDispSearchWindow').show();
						}
					}, '-', {
						text: langs('Талон по дополнительной диспансеризации взрослых (до 2013г.): поиск'),
						handler: function () {
							getWnd('swEvnPLDispDopSearchWindow').show();
						}
					}, '-', {
						text: MM_POL_EPLDD13SEARCH,
						handler: function () {
							getWnd('swEvnPLDispDop13SearchWindow').show();
						}
					}, {
						text: MM_POL_EPLDD13SECONDSEARCH,
						handler: function () {
							getWnd('swEvnPLDispDop13SecondSearchWindow').show();
						}
					}]
				}, {
					text:langs('Профилактические осмотры взрослых'),
					hidden: getRegionNick().inlist(['by','kz']),
					menu: [{
						text: MM_POL_EPLDPSEARCH,
						handler: function() {
							getWnd('swEvnPLDispProfSearchWindow').show();
						}
					}]
				}, {
					text:langs('Диспансеризация детей-сирот'),
					hidden: getRegionNick().inlist(['by','kz']),
					menu: [{
						text: langs('Регистр детей-сирот (до 2013г.): Поиск'),
						handler: function() {
							getWnd('swPersonDispOrpSearchWindow').show();
						}
					}, {
						text: langs('Талон по диспансеризации детей-сирот (до 2013г.): Поиск'),
						handler: function() {
							getWnd('swEvnPLDispOrpSearchWindow').show();
						}
					}, '-', {
						text: langs('Регистр детей-сирот (с 2013г.): Поиск'),
						handler: function() {
							getWnd('swPersonDispOrp13SearchWindow').show({
								CategoryChildType: 'orp'
							});
						}
					}, {
						text: langs('Регистр детей-сирот усыновленных: Поиск'),
						handler: function() {
							getWnd('swPersonDispOrp13SearchWindow').show({
								CategoryChildType: 'orpadopted'
							});
						}
					}, {
						text: langs('Карта диспансеризации несовершеннолетнего - 1 этап: Поиск'),
						handler: function() {
							getWnd('swEvnPLDispOrp13SearchWindow').show({
								stage: 1
							});
						}
					}, {
						text: langs('Карта диспансеризации несовершеннолетнего - 2 этап: Поиск'),
						handler: function() {
							getWnd('swEvnPLDispOrp13SearchWindow').show({
								stage: 2
							});
						}
					}, '-', {
						text: langs('Экспорт карт по диспансеризации несовершеннолетних'),
						handler: function() {
							getWnd('swEvnPLDispTeenExportWindow').show();
						}
					}]
				}, {
					text:langs('Медицинские осмотры несовершеннолетних'),
					hidden: getRegionNick().inlist(['by','kz']),
					menu: [{
						text: langs('Регистр периодических осмотров несовершеннолетних: Поиск'),
						hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
						handler: function() {
							getWnd('swPersonDispOrpPeriodSearchWindow').show();
						}
					}, {
						text: langs('Периодические осмотры несовершеннолетних: Поиск'),
						hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
						handler: function() {
							getWnd('swEvnPLDispTeenInspectionSearchWindow').show();
						}
					}, '-', {
						text: langs('Направления на профилактические осмотры несовершеннолетних: Поиск'),
						handler: function() {
							getWnd('swPersonDispOrpProfSearchWindow').show();
						}
					}, {
						text: langs('Профилактические осмотры несовершеннолетних - 1 этап: Поиск'),
						handler: function() {
							getWnd('swEvnPLDispTeenInspectionProfSearchWindow').show();
						}
					}, {
						text: langs('Профилактические осмотры несовершеннолетних - 2 этап: Поиск'),
						handler: function() {
							getWnd('swEvnPLDispTeenInspectionProfSecSearchWindow').show();
						}
					}, '-', {
						text: langs('Направления на предварительные осмотры несовершеннолетних: Поиск'),
						hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
						handler: function() {
							getWnd('swPersonDispOrpPredSearchWindow').show();
						}
					}, {
						text: langs('Предварительные осмотры несовершеннолетних - 1 этап: Поиск'),
						hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
						handler: function() {
							getWnd('swEvnPLDispTeenInspectionPredSearchWindow').show();
						}
					}, {
						text: langs('Предварительные осмотры несовершеннолетних - 2 этап: Поиск'),
						hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
						handler: function() {
							getWnd('swEvnPLDispTeenInspectionPredSecSearchWindow').show();
						}
					}]
				}, {
					text:langs('Диспансеризация (подростки 14ти лет)'),
					hidden: getRegionNick().inlist(['by','kz']),
					menu: [{
						text: langs('Диспансеризация 14-летних подростков: Поиск'),
						handler: function() {
							getWnd('swEvnPLDispTeen14SearchWindow').show();
						}
					}]
				}, {
					text:'Медицинское освидетельствование мигрантов',
					hidden: !isUserGroup('MedOsvMigr'),
					handler: function() {
						getWnd('swEvnPLDispMigrSearchWindow').show();
					}
				}, {
					text:'Медицинское освидетельствование водителей',
					hidden: !isDrivingCommission(),
					handler: function() {
						getWnd('swEvnPLDispDriverSearchWindow').show();
					}
				}],
				text: 'Профосмотры'
			}, {
				iconCls: 'access16-2017',
				handler: function() {
					getWnd('swMedStaffFactLinkViewWindow').show({
						MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
						onHide: Ext6.emptyFn
					});
				},
				text: 'Доступ ср. мед. персонала к ЭМК'
			}, {
				iconCls: 'mse16-2017',
				handler: function() {
					getWnd('swEvnPrescrMseJournalWindow').show({
						userMedStaffFact: win.userMedStaffFact.MedStaffFact_id
					});
				},
				text: 'Журнал направлений на МСЭ'
			}, {
				iconCls: 'direction16-2017',
				handler: function() {
					getWnd('swMPQueueWindow').show({
						useCase: 'open_from_polka',
						LpuSectionProfile_id: win.userMedStaffFact.LpuSectionProfile_id,
						Lpu_id: win.userMedStaffFact.Lpu_id,
						callback: function() {
							win.scheduleRefresh();
						},
						userMedStaffFact: win.userMedStaffFact
					});
				},
				text: 'Журнал направлений и записей'
			}, {
				tooltip: 'Открыть карту профилактических прививок',
				text: 'Иммунопрофилактика',
				iconCls : 'immunoprophylaxis',
				handler: function()
				{
					var selected_record = win.mainGrid.getSelectionModel().getSelectedRecord();
					if (!selected_record || !selected_record.get('Person_id')) {
						Ext6.Msg.show({
							buttons: Ext6.Msg.OK,
							fn: Ext6.emptyFn,
							icon: Ext6.Msg.WARNING,
							msg: langs('Выберите человека'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('amm_Kard063').show({person_id: selected_record.get('Person_id')});
				}
			}, {
				disabled: false,
				handler: function()
				{
					getWnd('swQueryEvnListWindow').show({ARMType: this.ARMType});
				},
				iconCls: 'query-log',
				nn: 'action_QueryEvn',
				text: 'Журнал запросов',
				tooltip: 'Журнал запросов'
			}, {
				iconCls: 'persondisp16-2017',
				hidden: !getRegionNick().inlist(['kz']),
				menu: [{
					text: WND_POL_PERSDISPSEARCH,
					handler: function()
					{
						getWnd('swPersonDispSearchWindow').show();
					},
					hidden: false//!(isAdmin || isTestLpu)
				}, {
					text: WND_POL_PERSDISPSEARCHVIEW,
					handler: function()
					{
						getWnd('swPersonDispViewWindow').show({mode: 'view'});
					},
					hidden: false//!(isAdmin || isTestLpu)
				}],
				text: 'Диспансерное наблюдение'
			}, {
				iconCls: 'personcard16-2017',
				handler: function() {
					win.showPersonCardSearchWindow();
				},
				text: 'Поиск прикрепления'
			}, {
				iconCls: 'hosp16-2017',
				handler: function() {
					getWnd('swJournalHospitWindow').show({userMedStaffFact: win.userMedStaffFact});
				},
				text: 'Журнал госпитализаций'
			}, {
				iconCls: 'smpjournal16-2017',
				handler: function() {
					getWnd('swCmpCallCardJournalWindow').show({userMedStaffFact: win.userMedStaffFact});
				},
				text: 'Журнал вызовов СМП'
			}, {
				iconCls: 'notify16-2017',
				handler: function() {
					if(!getWnd('swNotificationLogAdverseReactions').isVisible()){
						getWnd('swNotificationLogAdverseReactions').show({userMedStaffFact: win.userMedStaffFact});
					}
				},
				text: 'Журнал извещений о неблагоприятных реакциях'
			}, {
				iconCls: 'spr16-2017',
				menu: [{
					text: langs('Справочник услуг'),
					handler: function() {
						getWnd('swUslugaTreeWindow').show({action: 'view'});
					}
				},{
					text: langs('Справочник МКБ-10'),
					handler: function() {
						if ( !getWnd('swMkb10SearchWindow').isVisible() )
							getWnd('swMkb10SearchWindow').show();
					}
				}, {
					name: 'action_DrugNomenSpr',
					text: langs('Номенклатурный справочник'),
					handler: function()
					{
						getWnd('swDrugNomenSprWindow').show();
					}
				}, {
					name: 'action_PriceJNVLP',
					hidden: getRegionNick().inlist(['by']),
					text: langs('Цены на ЖНВЛП'),
					handler: function() {
						getWnd('swJNVLPPriceViewWindow').show();
					}
				}, {
					name: 'action_DrugMarkup',
					hidden: getRegionNick().inlist(['by']),
					text: langs('Предельные надбавки на ЖНВЛП'),
					handler: function() {
						getWnd('swDrugMarkupViewWindow').show({readOnly: true});
					}
				}, {
					text: langs('Справочник фальсификатов и забракованных серий ЛС'),
					handler: function()
					{
						getWnd('swPrepBlockViewWindow').show();
					}
				}, {
					text: 'Справочники системы учета медикаментов',
					handler: function()
					{
						getWnd('swDrugDocumentSprWindow').show();
					}
				}],
				text: 'Справочники'
			}, {
				iconCls: 'llo16-2017',
				menu: [{
					text: langs('Поиск льготников'),
					hidden: getRegionNick().inlist(['pskov']),
					handler: function()
					{
						getWnd('swPrivilegeSearchWindow').show();
					}
				},{
					text: langs('Журнал отсрочки'),
					handler: function()
					{
						getWnd('swReceptInCorrectSearchWindow').show();
					}
				}, {
					text: MM_DLO_MEDAPT,
					handler: function()
					{
						getWnd('swDrugOstatByFarmacyViewWindow').show();
					},
					hidden: !(getRegionNick() == 'perm')
				}, {
					text: MM_DLO_MEDNAME,
					handler: function()
					{
						getWnd('swDrugOstatViewWindow').show();
					},
					hidden: !(getRegionNick() == 'perm')
				}, {
					text: langs('Заявки ЛЛО'),
					hidden: getRegionNick().inlist(['by']),
					handler: function() {
						if (getRegionNick().inlist(['perm'])) {
							getWnd('swDrugRequestViewForm').show();
						} else {
							getWnd('swMzDrugRequestSelectWindow').show();
						}
					}
				}, {
					text: langs('Просмотр остатков'),
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({
							mode: 'suppliers',
							userMedStaffFact: this.userMedStaffFact
						});
					}.createDelegate(this),
					hidden: getRegionNick().inlist(['perm','ufa'])
				}, {
					text: langs('Просмотр остатков по складам Аптек и РАС'),
					tooltip: langs('Просмотр остатков по складам Аптек и РАС'),
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
					}.createDelegate(this),
					hidden: getRegionNick().inlist(['perm','ufa'])
				}, {
					text: langs('Прикрепление аптек к МО'),
					tooltip: langs('Прикрепление аптек к МО'),
					disabled: false,
					handler: function() {
						if (getRegionNick().inlist(['perm', 'ufa'])) {
							getWnd('swOrgFarmacyByLpuViewWindow').show();
						} else {
							getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: win.ARMType});
						}
					}
				}, {
					text: langs('План потребления МО'),
					tooltip: langs('План потребления МО'),
					handler: function() {
						getWnd('swDrugRequestPlanDeliveryViewWindow').show();
					}
				}],
				text: 'ЛЛО'
			}, {
				iconCls: 'notify16-2017',
				menu: [{
					text: langs('Журнал Извещений форма №058/У'),
					disabled: false,
					handler: function()
					{
						if ( getWnd('swEvnInfectNotifyListWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnInfectNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений по Вирусному гепатиту'),
					handler: function()
					{
						if ( getWnd('swEvnNotifyHepatitisListWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnNotifyHepatitisListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений об онкобольных'),
					disabled: !isUserGroup('OnkoRegistry') && !isUserGroup('OnkoRegistryFullAccess'),
					handler: function()
					{
						if ( getWnd('swEvnOnkoNotifyListWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnOnkoNotifyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, sw.Promed.personRegister.getEvnNotifyOrphanBtnConfig(win.id, win, true), {
					text: langs('Журнал Извещений по Психиатрии'),
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyCrazyListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений по Наркологии'),
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyNarkoListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений по туберкулезным заболеваниям'),
					disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyTubListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений о больных венерическим заболеванием'),
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyVenerListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений о ВИЧ-инфицированных'),
					disabled: (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0),
					handler: function()
					{
						getWnd('swEvnNotifyHIVListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений по нефрологии'),
					hidden: !getRegionNick().inlist([ 'perm', 'ufa' ,'buryatiya']),
					handler: function()
					{
						if ( getWnd('swEvnNotifyNephroListWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnNotifyNephroListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений по профзаболеваниям'),
					hidden: ('perm' != getRegionNick()),
					handler: function()
					{
						if ( getWnd('swEvnNotifyProfListWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnNotifyProfListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Журнал Извещений по ИБС'),
					hidden: true,//('perm' != getRegionNick()),
					handler: function()
					{
						if ( getWnd('swEvnNotifyIBSListWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnNotifyIBSListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, sw.Promed.personRegister.getEvnNotifyVznBtnConfig(win.id, win, true)
					, sw.Promed.personRegister.getEvnNotifyPalliatBtnConfig(win.id, win, true)
				],
				text: 'Извещения/Направления'
			}, {
				iconCls: 'registers16-2017',
				menu: [{
					text: langs('Регистр беременных'),
					disabled: !isPregnancyRegisterAccess(),
					hidden: false,
					handler: function()
					{
						getWnd('swPersonPregnancyWindow').show();
					}
				}, {
					text: langs('Регистр КВИ'),
					hidden: false,
					handler: function() {
						win.getAttachDataShowWindow('swCVIRegistryWindow');
					}
				}, {
					text:langs('Регистр детей-сирот'),
					hidden: getRegionNick().inlist(['by','kz']),
					menu: [{
						text: langs('Регистр детей-сирот (стационарных)'),
						handler: function() {
							getWnd('swPersonDispOrp13SearchWindow').show({
								CategoryChildType: 'orp'
							});
						}
					}, {
						text: langs('Регистр детей-сирот (усыновленных/опекаемых)'),
						handler: function() {
							getWnd('swPersonDispOrp13SearchWindow').show({
								CategoryChildType: 'orpadopted'
							});
						}
					}]
				}, {
					text: langs('Регистр ВОВ'),
					handler: function()
					{
						getWnd('swPersonPrivilegeWOWSearchWindow').show();
					}
				}, {
					text: langs('Регистр ДД'),
					handler: function()
					{
						getWnd('swPersonDopDispSearchWindow').show();
					}
				}, {
					text: langs('Регистр декретированных возрастов'),
					handler: function()
					{
						getWnd('swEvnPLDispTeen14SearchWindow').show();
					}
				}, {
					text: langs('Регистр по Вирусному гепатиту'),
					handler: function()
					{
						if ( getWnd('swHepatitisRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр по онкологии'),
					disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0),
					handler: function()
					{
						if ( getWnd('swOnkoRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType:this.ARMType});
					}.createDelegate(this)
				}, sw.Promed.personRegister.getOrphanBtnConfig(win.id, win, true), {
					text: langs('Регистр по Психиатрии'),
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function()
					{
						getWnd('swCrazyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр по Наркологии'),
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function()
					{
						getWnd('swNarkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр по гериатрии'),
					hidden: getRegionNick() == 'kz',
					disabled: !isUserGroup('GeriatryRegistry') && !isUserGroup('GeriatryRegistryFullAccess') && !isSuperAdmin(),
					handler: function() {
						if ( getWnd('swGeriatricsRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swGeriatricsRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр "Скрининг населения 60+"'),
					hidden: !getRegionNick().inlist(['perm', 'ufa']),
					handler: function()
					{

						if ( getWnd('swRegisterSixtyPlusViewWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swRegisterSixtyPlusViewWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр по сахарному диабету'),
					hidden: !getRegionNick().inlist([ 'pskov','khak','saratov','buryatiya' ]),
					handler: function()
					{
						if ( getWnd('swDiabetesRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swDiabetesRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр больных туберкулезом'),
					disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
					menu: [{
						text: langs('Регистр по туберкулезным заболеваниям'),
						handler: function() {
							getWnd('swTubRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						text: langs('Экспорт сведений в ФРБТ'),
						handler: function() {
							getWnd('swExportTubToXLSWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}]
				}, {
					text: langs('Регистр больных венерическими заболеваниями'),
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр ВИЧ-инфицированных'),
					disabled: !allowHIVRegistry(),
					menu: [{
						text: langs('Регистр ВИЧ'),
						handler: function() {
							getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						text: langs('Экспорт регистра ВИЧ в ФРВИЧ'),
						handler: function() {
							getWnd('swExportHivToXLSWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}]
				}, {
					text: langs('Регистр по нефрологии'),
					hidden: !getRegionNick().inlist([ 'perm', 'ufa' ,'buryatiya']),
					disabled: (String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) < 0),
					handler: function()
					{
						if ( getWnd('swNephroRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swNephroRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр по профзаболеваниям'),
					hidden: ('perm' != getRegionNick()),
					disabled: (String(getGlobalOptions().groups).indexOf('ProfRegistry', 0) < 0),
					handler: function()
					{
						if ( getWnd('swProfRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swProfRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр ИБС'),
					hidden: ('perm' != getRegionNick()),
					disabled: (String(getGlobalOptions().groups).indexOf('IBSRegistry', 0) < 0),
					handler: function()
					{
						if ( getWnd('swIBSRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swIBSRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, sw.Promed.personRegister.getVznBtnConfig(win.id, win, true), {
					text: langs('Регистр по БСК'),
					/*hidden: (!getRegionNick().inlist([ 'ufa' ])),*/
					disabled: (!isUserGroup("BSKRegistry")),
					handler: function()
					{

						if ( getWnd('swBskRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swBskRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр Реабилитации'),
					hidden: (!getRegionNick().inlist([ 'ufa' ])),
					handler: function()
					{

						if ( getWnd('swReabRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swReabRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					tooltip: langs("Регистр пациентов с подозрением на ЗНО"),
					text: langs("Регистр пациентов с подозрением на ЗНО"),
					hidden: getRegionNick() != "ufa",//yl:196433
					menu: [{
						tooltip: "Регистр подозрений на ЗНО",
						text: "Регистр подозрений на ЗНО",
						handler: function() {
							if ( getWnd("swZNOSuspectRegistryWindow").isVisible() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: langs("Окно уже открыто"),
									title: ERR_WND_TIT
								});
								return false;
							}
							getWnd("swZNOSuspectRegistryWindow").show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: "Периодические задания Регистра",
						text: "Периодические задания Регистра",
						hidden: (!isSuperAdmin()),
						handler: function() {
							if ( getWnd("swZNOSuspectAdminWindow").isVisible() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: langs("Окно уже открыто"),
									title: ERR_WND_TIT
								});
								return false;
							}
							getWnd("swZNOSuspectAdminWindow").show();
						}.createDelegate(this)
					}]
				}, {
					text: 'Регистр ИПРА',
					hidden: !(isUserGroup('IPRARegistry') || isUserGroup('IPRARegistryEdit')),
					handler: function()
					{

						if ( getWnd('swIPRARegistryViewWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swIPRARegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: "Регистр по ВРТ",
					hidden: getRegionNick() != "ufa",//yl:196433
					disabled: !(isUserGroup("EcoRegistry") || isUserGroup("EcoRegistryRegion")),
					handler: function () {
						if (getWnd("swECORegistryViewWindow").isVisible()) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: "Окно уже открыто",
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd("swECORegistryViewWindow").show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр по ОКС'),
					hidden:!getRegionNick().inlist(['astra', 'buryatiya']),
					disabled: (!isUserGroup("OKSRegistry")),
					handler: function()
					{
						if ( getWnd('swOKSRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swACSRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: langs('Регистр по эндопротезированию'),
					disabled: !isUserGroup('EndoRegistry'),
					handler: function()
					{

						if ( getWnd('swEndoRegistryWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEndoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					disabled: !(isUserGroup('OperBirth')||isUserGroup('OperRegBirth')),
					text: getRegionNick().inlist(['ufa']) ? 'Мониторинг детей первого года жизни' : 'Мониторинг новорожденных',
					handler: function() {
						getWnd('swMonitorBirthSpecWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: 'Регистр по суицидам',
					disabled: !isUserGroup('SuicideRegistry'),
					handler: function()
					{

						if ( getWnd('swPersonRegisterSuicideListWindow').isVisible() ) {
							Ext6.Msg.show({
								buttons: Ext6.Msg.OK,
								fn: Ext6.emptyFn,
								icon: Ext6.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swPersonRegisterSuicideListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					text: 'Регистр по паллиативной помощи',
					disabled: !(isUserGroup('RegistryPalliatCare')||isUserGroup('RegistryPalliatCareAll')),
					hidden: (getRegionNick() == 'kz'),
					handler: function() {
						if ( getWnd('swPersonRegisterPalliatListWindow').isVisible() ) {
							sw.swMsg.alert(ERR_WND_TIT, langs('Окно уже открыто'));
							return false;
						}
						getWnd('swPersonRegisterPalliatListWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},

				{
					text: lang['HTM_registry'],
					tooltip: lang['HTM_registry'],
					hidden: getRegionNick().inlist(['ufa', 'kz']) || !isUserGroup('HTMRegistry'),

					handler: function()
					{
						getWnd('swEvnDirectionHTMRegistryWindow')
							.show({
									ARMType: 'common',
									userMedStaffFact: win.userMedStaffFact
								});
					}
				}
				],
				text: 'Регистры по заболеваниям'
			}, {
				iconCls: 'persondisp16-2017',
				hidden: getRegionNick().inlist(['kz']),
				handler: function() {
					var selected_record = win.mainGrid.getSelectionModel().getSelectedRecord(),
						Person_id = (selected_record && selected_record.get('Person_id')) || null,
						MedPersonal_id = win.userMedStaffFact.MedPersonal_id;
					getWnd('swPersonDispViewWindow').show({
						mode: 'view', Person_id: Person_id, MedPersonal_id: MedPersonal_id, view_one_doctor: true, ARMType: "common"
					});
				},
				text: 'Диспансерное наблюдение'
			}, {
				iconCls: 'regions16-2017',
				handler: function() {
					getWnd('swFindRegionsWindow').show();
				},
				text: 'Поиск участков и врачей по адресу'
			}, {
				iconCls: 'pathomorph16-2017',
				menu: [{
					text: 'Направления на патологогистологическое исследование',
					handler: function() {
						getWnd('swEvnDirectionHistologicViewWindow').show();
					},
					hidden: false
				}, {
					text: 'Протоколы патологогистологических исследований',
					handler: function() {
						getWnd('swEvnHistologicProtoViewWindow').show();
					},
					hidden: false
				}, '-', {
					text: 'Направления на патоморфогистологическое исследование',
					handler: function() {
						getWnd('swEvnDirectionMorfoHistologicViewWindow').show();
					},
					hidden: false
				}, {
					text: 'Протоколы патоморфогистологических исследований',
					handler: function() {
						getWnd('swEvnMorfoHistologicProtoViewWindow').show();
					},
					hidden: false
				}, '-', {
					text: 'Направления на цитологическое исследование',
					//iconCls : 'cytologica16',
					handler: function() {
						getWnd('swEvnDirectionCytologicViewWindows').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
					},
					hidden: false
				}, {
					text: 'Протоколы цитологических исследований',
					//iconCls : 'cytologica16',
					handler: function() {
						getWnd('swEvnCytologicProtoViewWindow').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
					},
					hidden: false
				}],
				text: 'Патоморфология'
			}, {
				iconCls: 'medsvid16-2017',
				handler: function() {
					getWnd('swMedSvidDeathStreamWindow').show();
				},
				hidden: !isUserGroup('MedSvidDeath'),
				text: 'Мед. свидетельства о смерти'
			}, {
				iconCls: 'templates16-2017',
				handler: function() {
					var params = {
						allowedEvnClassList: [11, 22, 43],

						allowedXmlTypeEvnClassLink: {
							2:  [11],
							3:  [11],
							4:  [11, 22],
							10: [11],
							17: [43]
						},

						allowedXmlTypeKind: {'11.10': 5},  // Для ВК

						EvnClass_id: 11,  // Посещение поликлиники
						XmlType_id: 3,
						LpuSection_id: win.userMedStaffFact.LpuSection_id,
						MedPersonal_id: win.userMedStaffFact.MedPersonal_id,
						MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
					};
					getWnd('swXmlTemplateEditorWindow').show(params);
				},
				text: 'Шаблоны документов'
			},{
				iconCls: 'registers16-2017',
				menu: [{
					text: 'Региональный РЭМД',
					handler: function() {

						var params = {
							LpuBuilding_id: win.userMedStaffFact.LpuBuilding_id,
							LpuSection_id: win.userMedStaffFact.LpuSection_id,
							MedPersonal_id: win.userMedStaffFact.MedPersonal_id,
							MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
							ArmType: 'common'
						};

						getWnd('swEMDSearchWindow').show(params);
					},
					hidden: false
				},{
					text: 'Поиск и подписание документов',
					handler: function() {

						var params = {
							LpuBuilding_id: win.userMedStaffFact.LpuBuilding_id,
							LpuSection_id: win.userMedStaffFact.LpuSection_id,
							MedPersonal_id: win.userMedStaffFact.MedPersonal_id,
							MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
						};

						getWnd('swEMDSearchUnsignedWindow').show(params);
					},
					hidden: false
				}],
				text: 'Региональный РЭМД'
			}, {
				iconCls: 'dtp16-2017',
				menu: [{
					text: langs('Извещения ДТП о раненом: Просмотр'),
					handler: function()
					{
						getWnd('swEvnDtpWoundWindow').show();
					},
					hidden: false
				}, {
					text: langs('Извещения ДТП о скончавшемся: Просмотр'),
					handler: function()
					{
						getWnd('swEvnDtpDeathWindow').show();
					},
					hidden: false
				}],
				text: 'Извещения о ДТП'
			}, {
				iconCls: 'action_take_sample',
				itemId: 'medServiceLink',
				handler: function() {
					getWnd('swMedServiceLinkManageWindow').show({
						MedService_id: win.pzm_MedService_id
					});
				},
				text: 'Лаборатории'
			}, {
				iconCls: 'action_take_sample',
				itemId: 'evnUslugaParSearch',
				handler: function() {
					getWnd('swEvnUslugaParSearchWindow').show({
						LpuSection_id: win.userMedStaffFact.LpuSection_id
					});
				},
				text: 'Параклинические услуги: Поиск'
			}, {
				iconCls: 'vkqueryjournal16-2017',
				itemId: 'VKJournal',
				hidden: !getRegionNick().inlist(['perm', 'vologda']) || !isUserGroup('DepHead'),
				handler: function() {
					getWnd('swVKJournalWindow').show({
						userMedStaffFact: win.userMedStaffFact
					});
				},
				text: 'Журнал запросов ВК'
			}, {
				iconCls: 'pregnancy-icon',
				/*Кнопка доступна для пользователей, включенных в любую из групп доступа:
				•	ЭРС. Оформление документов
				•	ЭРС. Руководитель МО
				•	ЭРС. Бухгалтер*/
				hidden: !(isUserGroup(['ERSDoc', 'ERSHead', 'ERSAccountant'])),
				menu: [{
					text: 'Журнал Родовых сертификатов',
					handler: function () {
						getWnd('swEvnErsJournalWindow').show();
					}
				}, {
					text: 'Журнал Талонов',
					handler: function () {
						getWnd('swEvnErsTicketJournalWindow').show();
					}
				}, {
					text: 'Журнал учета детей',
					handler: function () {
						getWnd('swEvnErsChildJournalWindow').show();
					}
				}, {
					text: 'Реестры талонов и счета на оплату',
					handler: function () {
						getWnd('swErsRegistryJournalWindow').show();
					}
				}],
				text: 'Родовые сертификаты'
			}]
		});

		win.cardPanel = new Ext6.Panel({
			dockedItems: [ win.leftMenu ],
			animCollapse: false,
			floatable: false,
			collapsible: false,
			flex: 100,
			region: 'center',
			layout: 'card',
			activeItem: 0,
			border: false,
			items: [{
				border: false,
				layout: 'border',
				itemId: "mainTab",
				items: [
					win.mainGrid, win.ElectronicQueuePanel
				]
			}, {
				border: false,
				layout: 'border',
				itemId: "homeVisitTab",
				items: [
					win.homeVisitGrid
				]
			}, {
				border: false,
				layout: 'border',
				itemId: "labRequestTab",
				items: [
					win.labRequestGrid
				]
			}]
		});
		
		win.MedStaffFactFilter = Ext6.create('swBaseCombobox', {
			fieldLabel: 'Пациенты:',
			displayField: 'name',
			itemId: 'mpwp_MedStaffFactFilterType',
			valueField: 'value',
			queryMode: 'local',
			labelWidth: '50px',
			style: 'margin-left: 15px',
			value: 3,
			store: {
				fields: [
					{name: 'value'},
					{name: 'name'}
				],
				data: [
					[1, 'Cвои'],
					[2, 'Пациенты врача по замещению'],
					[3, 'Все']
				]
			},
			getText: function() {
				return win.MedStaffFactFilter.getFieldValue('name');
			},
			listeners: {
				'change': function (combo, newValue, oldValue) {
					win.scheduleRefresh();
				},
			}
		});

		win.mainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			border: false,
			tbar: {
				xtype: 'toolbar',
				cls: 'toptoolbar',
				items: [{
					text: 'Период:',
					padding: "10px 0px 10px 10px",
					margin: 0,
					xtype: 'label'
				}, {
					xtype: 'button',
					cls: 'bgTrans',
					border: false,
					margin: 3,
					iconCls: 'arrow-previous16-2017',
					handler: function()
					{
						win.prevDay();
						win.scheduleRefresh();
					}
				}, win.dateMenu, {
					xtype: 'button',
					cls: 'bgTrans',
					border: false,
					margin: 3,
					iconCls: 'arrow-next16-2017',
					handler: function()
					{
						win.nextDay();
						win.scheduleRefresh();
					}
				}, win.MedStaffFactFilter, '->', {
					xtype: 'segmentedbutton',
					userCls: 'segmentedButtonGroup',//для применение стилей ставить этот класс
					items: [{
						text: 'Прием',
						refId: 'priemButton',
						pressed: true,
						handler: function() {
							win.cardPanel.getLayout().setActiveItem(0);
							win.onTabChange();
						}
					}, {
						text: 'Вызовы',
						refId: 'callButton',
						handler: function() {
							win.cardPanel.getLayout().setActiveItem(1);
							win.onTabChange();
						}
					}, {
						text: 'Заявки',
						refId: 'labButton',
						handler: function() {
							win.cardPanel.getLayout().setActiveItem(2);
							win.onTabChange();
						}
					}]
				}]
			},
			items: [ win.cardPanel ]
		});

		Ext6.apply(win, {
			referenceHolder: true, // чтобы ЛУКап заработал по референсу
			reference: 'swWorkPlacePolkaWindowLayout_' + win.id,
			items: [win.mainPanel, win.FormPanel],
			/*buttons: [
				{
					handler: function() {ShowHelp(this.up('window').title)},
					iconCls: 'help16',
					text: BTN_FRMHELP
				},
				{
					handler: function() {win.hide()},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			]*/
		});

		this.callParent(arguments);
	},
	showPersonCardSearchWindow: function() {
		var win = this;
		win.mask('Получение данных участка...');
		Ext6.Ajax.request({
			url: '/?c=LpuRegion&m=getAttachData',
			callback: function (options, success, response) {
				win.unmask();
				if (success) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if (response_obj[0] && response_obj[0].LpuRegion_id) {
						getWnd('swPersonCardSearchWindow').show({
							LpuAttachType_id: response_obj[0].LpuAttachType_id,
							LpuRegionType_id: response_obj[0].LpuRegionType_id,
							LpuRegion_id: response_obj[0].LpuRegion_id
						});
					}
					else {
						getWnd('swPersonCardSearchWindow').show();
					}
				}
			},
			params: {
				MedPersonal_id: getGlobalOptions().medpersonal_id,
				Lpu_id: getGlobalOptions().lpu_id
			}
		});
	},
	getAttachDataShowWindow: function(wnd) {
		var global_options = getGlobalOptions();
		var scope = this;
		Ext.Ajax.request({
			url: '/?c=LpuRegion&m=getAttachData',
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj[0] && response_obj[0].LpuRegion_id) {
						getWnd(wnd).show({
							LpuAttachType_id: response_obj[0].LpuAttachType_id,
							LpuRegionType_id: response_obj[0].LpuRegionType_id,
							LpuRegion_id: response_obj[0].LpuRegion_id,
							userMedStaffFact: scope.userMedStaffFact
						});
					}
					else {
						getWnd(wnd).show();
					}
				}
			},
			params: { MedPersonal_id: global_options.medpersonal_id, Lpu_id: global_options.lpu_id }
		});
	}
});
