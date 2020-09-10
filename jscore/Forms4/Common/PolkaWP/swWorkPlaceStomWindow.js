/**
 * swWorkPlaceStomWindow - АРМ стоматолога
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.PolkaWP.swWorkPlaceStomWindow', {
	noCloseOnTaskBar: true, // без кнопки закрытия на таксбаре
	extend: 'base.BaseForm',
	alias: 'widget.swWorkPlaceStomWindow',
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

		if (record.get('Registry_id') > 0) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: Ext6.emptyFn,
				icon: Ext6.Msg.WARNING,
				msg: 'Освобождение бирки невозможно, поскольку прием уже осуществлен и посещение подано в реестр на оплату',
				title: ERR_WND_TIT
			});
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
	openPersonEmkWindow: function() {
		var form = this;

		var grid = this.mainGrid;

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

			var openEvn = null;
			if (record.get('Evn_id')) {
				openEvn = {
					object: 'EvnPLStom',
					object_value: record.get('Evn_id')
				};
			}

			getWnd('swPersonEmkWindowExt6').show({
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				TimetableGraf_id: record.get('TimetableGraf_id'),
				userMedStaffFact: form.userMedStaffFact,
				MedStaffFact_id: form.userMedStaffFact.MedStaffFact_id,
				LpuSection_id: form.userMedStaffFact.LpuSection_id,
				readOnly: true,
				ARMType: form.ARMType,
				openEvn: openEvn,
				electronicQueueData: electronicQueueData,
				callback: function(retParams) {
					form.scheduleRefresh();

					// выполняем кэллбэк (нужен для ЭО в некоторых случаях)
					if (retParams && retParams.callback && typeof retParams.callback == 'function') retParams.callback();
				}
			});

			return true;
		}
	},
	/*
	*	Открывает ЭМК при нажатии без записи
	*/
	createTtgAndOpenPersonEPHForm: function(pdata)
	{
		getWnd('swPersonEmkWindowExt6').show({
			Person_id: pdata.Person_id,
			Server_id: pdata.Server_id,
			PersonEvn_id: pdata.PersonEvn_id,
			userMedStaffFact: this.userMedStaffFact,
			TimetableGraf_id: pdata.TimetableGraf_id || null,
			EvnDirectionData: pdata.EvnDirectionData || null,
			mode: 'workplace',
			ARMType: this.ARMType,
			callback: function () {
				this.scheduleRefresh();
			}.createDelegate(this)
		});
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
							useCase: 'create_evnplstom_without_recording',
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
										useCase: 'create_evnplstom_without_recording',
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

		if (!arguments[0] || !arguments[0].userMedStaffFact || !arguments[0].userMedStaffFact.ARMType) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
		} else {
			this.ARMType = arguments[0].userMedStaffFact.ARMType;
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		log('userMedStaffFact', this.userMedStaffFact);

		sw.Promed.MedStaffFactByUser.setMenuTitle(win, win.userMedStaffFact);

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
	},
	currentWeek: function () {
		var win = this;
		var date1 = (Date.parseDate(win.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		win.dateMenu.setDates([date1, date2]);
	},
	currentMonth: function () {
		var win = this;
		var date1 = (Date.parseDate(win.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		win.dateMenu.setDates([date1, date2]);
	},
	scheduleRefresh: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;

		if (!win.dateMenu.getDateFrom()) {
			return false;
		}

		win.mainGrid.loadParams = {
			begDate: win.dateMenu.getDateFrom().format('d.m.Y'),
			endDate: (win.dateMenu.getDateTo() || win.dateMenu.getDateFrom()).format('d.m.Y'),
			MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
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
	initComponent: function() {
		var win = this;

		win.gridColumnConfig = [{text: 'Запись', tdCls: 'padLeft', width: 80, dataIndex: 'TimetableGraf_begTime'},
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
		}, renderer: function(val, metaData, record) {
			if (!val) {
				return '';
			}

			var tooltip = '';
			var spanClass = '';

			if (record.get('TimetableType_id') == 13) {
				tooltip = 'Видеосвязь';
				spanClass = 'videoVisitIcon';
			} else {
				switch (record.get('VizitType')) {
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
			}
			return "<span style='float: left;'>" + toUpperCaseFirstLetter(val) + "</span><span class='" + spanClass + "' data-qtip='"+tooltip+"'></span>";
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
		{text: 'Участок', width: 80, dataIndex: 'LpuRegion_Name'},
		{text: 'Записан', width: 150, dataIndex: 'TimetableGraf_updDT', renderer: function (val, metaData, record) {
				return val.toString('dd.MM.yyyy HH:mm');
		}},
		{text: 'Оператор', width: 150, dataIndex: 'pmUser_Name'},
		{text: '№ Ам. карты', width: 120, dataIndex: 'PersonCard_Code'},
		{text: 'РЗ', width: 100, dataIndex: 'Person_Bdz', renderer: function(val, metaData, record) {
			var s = '';
			if (record.get('Person_IsBDZ') && record.get('Person_IsBDZ') == 'true') {
				s += "<span class='lgot_rz' data-qtip='Регистр застрахованных'>РЗ</span>";
			}
			return s;
		}},
		{text: '', flex: 1, dataIndex: 'empty'}];

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
					{name: 'EvnDirection_id', type: 'int'},
					{name: 'EvnQueue_id', type: 'int'},
					{name: 'EvnStatus_id', type: 'int'},
					{name: 'MSF_Person_Fin'},
					{name: 'EvnDirection_setDate'},
					{name: 'EvnDirection_Num'},
					{name: 'LpuSectionProfile_Name'},
					{name: 'IsEvnDirection'},
					{name: 'PersonEncrypHIV_Encryp'},
					{name: 'Registry_id', type: 'int'},
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
			layoutPanelId: 'swWorkPlaceStomWindowLayout_' + win.id, // todo лэйаут для перерисовки в которой находится панель
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
					getWnd('swTTGScheduleEditWindow').show();
				},
				text: 'Расписание'
			}, {
				iconCls: 'evnpl16-2017',
				handler: function() {
					getWnd('swEvnPLStomSearchWindow').show();
				},
				text: 'Поиск ТАП по стоматологии'
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
				iconCls: 'access16-2017',
				handler: function() {
					getWnd('swMedStaffFactLinkViewWindow').show({
						MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id,
						onHide: Ext6.emptyFn
					});
				},
				text: 'Доступ ср. мед. персонала к ЭМК'
			}, {
				iconCls: 'spr16-2017',
				menu: [{
					text: langs('Справочник услуг'),
					handler: function() {
						getWnd('swUslugaTreeWindow').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser')) ? 'view' : ''});
					}
				},{
					text: langs('Справочник МКБ-10'),
					handler: function() {
						if ( !getWnd('swMkb10SearchWindow').isVisible() )
							getWnd('swMkb10SearchWindow').show();
					}
				}, {
					text: langs('Справочник') + getMESAlias(),
					handler: function()
					{
						if ( !getWnd('swMesOldSearchWindow').isVisible() )
							getWnd('swMesOldSearchWindow').show();
					}
				}, {
					text: langs('МНН: Ввод латинских наименований'),
					handler: function() {
						getWnd('swDrugMnnViewWindow').show({
							privilegeType: 'all',
							action: (isUserGroup('LpuUser') || isUserGroup('OrgUser')) ? 'view' : ''
						});
					}
				}, {
					text: langs('Торг.наим.: Ввод латинских наименований'),
					handler: function() {
						getWnd('swDrugTorgViewWindow').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser')) ? 'view' : ''});
					}
				}, {
					text: getRLSTitle(),
					handler: function()
					{
						getWnd('swRlsViewForm').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser')) ? 'view' : ''});
					}
				}, {
					text: langs('Глоссарий'),
					handler: function()
					{
						getWnd('swGlossarySearchWindow').show();
					}
				}],
				text: 'Справочники'
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
				}],
				text: 'Патоморфология'
			}, {
				iconCls: 'personcard16-2017',
				handler: function() {
					win.showPersonCardSearchWindow();
				},
				text: 'Поиск прикрепления'
			}, {
				iconCls: 'regions16-2017',
				handler: function() {
					getWnd('swFindRegionsWindow').show();
				},
				text: 'Поиск участков и врачей по адресу'
			}, {
				iconCls: 'templates16-2017',
				handler: function() {
					var params = {
						allowedEvnClassList: [13, 29],

						allowedXmlTypeEvnClassLink: {
							2:  [13],
							3:  [13],
							4:  [13, 29],
							10: [13]
						},

						EvnClass_id: 13,
						XmlType_id: 3,
						LpuSection_id: win.userMedStaffFact.LpuSection_id,
						MedPersonal_id: win.userMedStaffFact.MedPersonal_id,
						MedStaffFact_id: win.userMedStaffFact.MedStaffFact_id
					};
					getWnd('swXmlTemplateEditorWindow').show(params);
				},
				text: 'Шаблоны документов'
			}]
		});

		win.cardPanel = new Ext6.Panel({
			dockedItems: [ win.leftMenu ],
			animCollapse: false,
			floatable: false,
			collapsible: false,
			flex: 100,
			region: 'center',
			layout: 'border',
			activeItem: 0,
			border: false,
			items: [
				win.mainGrid,
				win.ElectronicQueuePanel
			]
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
				}]
			},
			items: [ win.cardPanel ]
		});

		Ext6.apply(win, {
			referenceHolder: true, // чтобы ЛУКап заработал по референсу
			reference: 'swWorkPlaceStomWindowLayout_' + win.id,
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
	}
});