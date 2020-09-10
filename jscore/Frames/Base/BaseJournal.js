
/**
 * sw.Promed.BaseFrame. Класс базового фрейма
 *
 *
 * @project  PromedWeb
 * @copyright  (c) Swan Ltd, 2009
 * @package frames
 * @author  Марков Андрей
 * @class sw.Promed.BaseFrame
 * @extends Ext.form.FormPanel
 * @version 20.02.2009
 */

sw.Promed.BaseJournal = function(config)
{
	Ext.apply(this, config);
	sw.Promed.BaseJournal.superclass.constructor.call(this);
};

Ext.extend(sw.Promed.BaseJournal, Ext.Panel,
{
	//region: 'center',
	labelWidth: 120,
	ownerWindow:null,
	border: true,
	persData:null,
	winType:null,
	ARMType:null,
	useCase:null,
	loadAddFields:null,
	resetRecordDate: true,
	getOwnerWindow: function () {
		return this.ownerWindow
	},
	
	noFocusOnLoad: false,
	noAutoSearch: false,// #142955 поиск не автоматический, по-умолчанию - автоматический

	openEvnDirectionEditWindow: function (action, mode) {
		if (!action || !action.inlist(['add', 'edit', 'view'])) {
			return false;
		}

		var view_frame = this.mainGrid;
		var wnd = this;
		var userMedStaffFact = this.ownerWindow.userMedStaffFact;
		switch (action) {
			case 'add':
				var directionData = {
					LpuUnitType_SysNick: null
					, EvnQueue_id: null
					, QueueFailCause_id: null
					, Lpu_did: null // ЛПУ куда направляем
					, LpuUnit_did: null
					, LpuSection_did: null
					, EvnUsluga_id: null
					, LpuSection_id: null
					, EvnDirection_pid: null
					, EvnPrescr_id: null
					, PrescriptionType_Code: null
					, DirType_id: null
					, LpuSectionProfile_id: null
					, LpuUnitType_id: null
					, Diag_id: null
					, MedStaffFact_id: null
					, MedPersonal_id: userMedStaffFact.MedPersonal_id
					, MedPersonal_did: null
					, withDirection: (mode && (mode == 2 || mode == 3))
					, EvnDirection_IsReceive: (mode && mode==3)?2:1
					, fromBj: true
				};
				if (this.persData != null) {
					var isDead = (this.persData && this.persData.Person_IsDead && this.persData.Person_IsDead == 'true');

					if (isDead) {
						sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна, т.к. у пациента стоит дата смерти.'));
						return false;
					}

					var params = {
						userMedStaffFact: userMedStaffFact,
						isDead: isDead,
						type: 'LpuReg',
						personData: this.persData,
						directionData: directionData,
						onDirection: function (data) {
							// обновляем грид и позиционируемся на добавленное направление
							
							/* если поиск не осуществлялся, то параметры baseParams отсутствуют и при загрузке будет ошибка 
							 отсутствия обязательных параметров.
							Коли параметров нет, то и перезагружать ее не будем*/
							var view_frameStore = view_frame.getGrid().getStore();
							if(view_frameStore.getCount() == 0 || (view_frameStore.getCount()==1 && !view_frameStore.getAt(0).get('EvnDirection_id'))){
								return;
							}
							view_frame.getGrid().getStore().reload({
								callback: function () {
									if (data.EvnDirection_id) {
										var index = view_frame.getGrid().getStore().findBy( function(record) {
											if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
												return true;
											}
										});

										if (index > -1) {
											view_frame.getGrid().getView().focusRow(index);
											view_frame.getGrid().getSelectionModel().selectRow(index);
										}
									}
								}
							});
						}
					};

					checkPersonPhoneVerification({
						Person_id: this.persData.Person_id,
						MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
						callback: function(){getWnd('swDirectionMasterWindow').show(params)}
					});
				} else {
					// открываем окно поиска чела, на него выписываем направление
					getWnd('swPersonSearchWindow').show({
						onClose: Ext.emptyFn,
						onSelect: function (person_data) {
							getWnd('swPersonSearchWindow').hide();

							var isDead = (person_data && person_data.Person_IsDead && person_data.Person_IsDead == 'true');
							if (isDead) {
								sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна, т.к. у пациента стоит дата смерти.'));
								return false;
							}

							var params = {
								userMedStaffFact: userMedStaffFact,
								isDead: isDead,
								type: 'LpuReg',
								personData: person_data,
								directionData: directionData,
								onDirection: function (data) {
									// обновляем грид и позиционируемся на добавленное направление
									
									/* если поиск не осуществлялся, то параметры baseParams отсутствуют и при загрузке будет ошибка 
									 отсутствия обязательных параметров.
									Коли параметров нет, то и перезагружать ее не будем*/
									var view_frameStore = view_frame.getGrid().getStore();
									if(view_frameStore.getCount()== 0 || (view_frameStore.getCount()==1 && !view_frameStore.getAt(0).get('EvnDirection_id')) ){
										return;
									}
									view_frame.getGrid().getStore().reload({
										callback: function () {
											if (data.EvnDirection_id) {
												var index = view_frame.getGrid().getStore().findBy( function(record) {
													if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
														return true;
													}
												});

												if (index > -1) {
													view_frame.getGrid().getView().focusRow(index);
													view_frame.getGrid().getSelectionModel().selectRow(index);
												}
											}
										}
									});
								}
							};

							checkPersonPhoneVerification({
								Person_id: person_data.Person_id,
								MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
								callback: function(){getWnd('swDirectionMasterWindow').show(params)}
							});
						},
						searchMode: 'all'
					});
				}
				break;

			case 'edit':
			case 'view':
				var record = this.getSelectedRecord();

				if (record == false) {
					return false;
				}

				var params = {
					action: 'view',
					formParams: {},
					Person_id: record.get('Person_id'),
					EvnDirection_id: record.get('EvnDirection_id')
				};

				this.openForm('swEvnDirectionEditWindow', langs('Просмотр электронного направления'), params);
				break;
		}
		return true;
	},
	onKeyDown: function (inp, e) {
		var _this = this;

		if ( e.getKey() == Ext.EventObject.ENTER ) {
			e.stopEvent();
			_this.doSearch();
		}

	},
	setUserListFilter: function() {
		var base_form = this.mainForm.getForm();
		var
			onlyCallCenterUsers = base_form.findField('onlyCallCenterUsers').getValue(),
			pmUserField = base_form.findField('pmUser_id'),
			pmUser = pmUserField.getValue(),
			UserLpu_id = base_form.findField('UserLpu_id').getValue();

		pmUserField.getStore().clearFilter();

		if ( onlyCallCenterUsers || !Ext.isEmpty(UserLpu_id) ) {
			pmUserField.getStore('pmUser_id').filterBy(function(rec) {
				return (
					(
						!onlyCallCenterUsers
						|| (
							rec.get('groups')
							&& (rec.get('groups').indexOf('OperatorCallCenter') !== -1 || rec.get('groups').indexOf('CallCenterAdmin') !== -1)
						)
					)
					&& (
						Ext.isEmpty(UserLpu_id)
						|| rec.get('Lpu_id') == UserLpu_id
					)
				);
			});
		}

		checkValueInStore(base_form, 'pmUser_id', 'pmUser_id', pmUser);
	},
	recInQueue: function(mode) {
		var record = this.getSelectedRecord();
		if (!record) {
			return false;
		}
		if ('record_from_queue' == this.useCase && record.data['EvnDirection_id']) {
			this.ownerWindow.onSelect(record.data);
			return true;
		}
		var win = this;
		var view_frame = this.mainGrid;
		var userMedStaffFact = this.ownerWindow.userMedStaffFact;
		win.getLoadMask(langs('Пожалуйста подождите...')).show();
		Ext.Ajax.request({
			url: '/?c=EvnDirection&m=getDataEvnDirection',
			callback: function(options, success, response)  {
				win.getLoadMask().hide();
				if (success) {
					var result  = Ext.util.JSON.decode(response.responseText);
					var data = result[0];
					var params =
					{
						useCase: 'record_from_queue',
						Diag_id: data.Diag_id,
						personData: {
							Person_id: data.Person_id,
							Server_id: data.Server_id,
							PersonEvn_id: data.PersonEvn_id,
							Person_Birthday: data.Person_BirthDay,
							Person_Surname: data.Person_SurName,
							Person_Firname: data.Person_FirName,
							Person_Secname: data.Person_SecName
						},
						EvnQueue_id: data.EvnQueue_id,
						EvnDirection_pid: data.EvnDirection_pid || null,
						LpuSectionProfile_id: data.LpuSectionProfile_did,
						Filter_Lpu_Nick: data.Lpu_Nick,
						userMedStaffFact: userMedStaffFact,
						onDirection: function (data) {
							// обновляем грид и позиционируемся на направление
							view_frame.getGrid().getStore().reload({
								callback: function () {
									if (data.EvnDirection_id) {
										var index = view_frame.getGrid().getStore().findBy( function(record) {
											if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
												return true;
											}
										});

										if (index > -1) {
											view_frame.getGrid().getView().focusRow(index);
											view_frame.getGrid().getSelectionModel().selectRow(index);
										}
									}
								}
							});
						}.createDelegate(this),
						ARMType: (this.ARMType)?this.ARMType:'regpol'
					}

					if (data.type == 'EvnQueue' && data.Resource_id) {
						params.TimetableData = {
							type: 'TimetableResource',
							EvnQueue_id: record.get('EvnQueue_id'),
							EvnDirection_id: data.EvnDirection_id,
							EvnDirection_pid: data.EvnDirection_pid || null,
							EvnDirection_Num: data.EvnDirection_Num,
							EvnDirection_setDate: data.EvnDirection_setDate,
							EvnDirection_IsAuto: data.EvnDirection_IsAuto,
							EvnDirection_IsReceive: data.EvnDirection_IsReceive,
							MedStaffFact_id: data.MedStaffFact_id,
							From_MedStaffFact_id: data.From_MedStaffFact_id,
							LpuUnit_did: data.LpuUnit_did,
							Lpu_did: data.Lpu_did,
							MedPersonal_did: data.MedPersonal_did,
							LpuSection_did: data.LpuSection_did,
							LpuSectionProfile_id: data.LpuSectionProfile_id,
							DirType_id: data.DirType_id,
							DirType_Code: data.DirType_Code,
							ARMType_id: data.ARMType_id,
							MedServiceType_SysNick: data.MedServiceType_SysNick,
							MedService_id: data.MedService_id,
							isAllowRecToUslugaComplexMedService: false,
							UslugaComplexMedService_id: data.UslugaComplexMedService_id,
							MedService_Nick: data.MedService_Nick,
							Resource_id: data.Resource_id,
							Resource_Name: data.Resource_Name
						};
					} else if (data.type == 'EvnQueue' && data.MedService_id) {
						// для службы открываем сразу расписание
						params.TimetableData = {
							type: 'TimetableMedService',
							EvnQueue_id: record.get('EvnQueue_id'),
							EvnDirection_id: data.EvnDirection_id,
							EvnDirection_pid: data.EvnDirection_pid || null,
							EvnDirection_Num: data.EvnDirection_Num,
							EvnDirection_setDate: data.EvnDirection_setDate,
							EvnDirection_IsAuto: data.EvnDirection_IsAuto,
							EvnDirection_IsReceive: data.EvnDirection_IsReceive,
							MedStaffFact_id: data.MedStaffFact_id,
							From_MedStaffFact_id: data.From_MedStaffFact_id,
							LpuUnit_did: data.LpuUnit_did,
							Lpu_did: data.Lpu_did,
							MedPersonal_did: data.MedPersonal_did,
							LpuSection_did: data.LpuSection_did,
							LpuSectionProfile_id: data.LpuSectionProfile_id,
							DirType_id: data.DirType_id,
							DirType_Code: data.DirType_Code,
							ARMType_id: data.ARMType_id,
							MedServiceType_SysNick: data.MedServiceType_SysNick,
							MedService_id: data.MedService_id,
							isAllowRecToUslugaComplexMedService: data.isAllowRecToUslugaComplexMedService,
							UslugaComplexMedService_id: (data.isAllowRecToUslugaComplexMedService && data.UslugaComplexMedService_id) ? data.UslugaComplexMedService_id : null,
							MedService_Nick: data.MedService_Nick
						};
					} else {
						params.dirTypeData = {
							DirType_id: data.DirType_id,
							DirType_Code: data.DirType_Code,
							DirType_Name: data.DirType_Name
						};
						params.directionData = data;
						params.directionData['redirectEvnDirection'] = 600; // признак записи из очереди
					}

					getWnd('swDirectionMasterWindow').show(params);
				} else
					sw.swMsg.alert(langs('Ошибка'), langs('Произошла ошибка.'));
			},
			params: {
				EvnDirection_id: record.get('EvnDirection_id'),
				EvnQueue_id:record.get('EvnQueue_id'),
				TimetableMedService_id:record.get('TimetableMedService_id'),
				TimetableResource_id_id:record.get('TimeResource_id'),
				TimetableGraf_id:record.get('TimetableGraf_id'),
				TimetableStac_id:record.get('TimetableStac_id')
			}
		});
	},
	rewriteEvnDirection: function () {
		var record = this.getSelectedRecord();
		var grid = this.mainGrid.getGrid();
		if (record == false) {
			return false;
		}
		sw.Promed.Direction.rewrite({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			userMedStaffFact: this.ownerWindow.userMedStaffFact,
			EvnDirection_id: record.get('EvnDirection_id'),
			callback: function (data) {
				// обновляем грид и позиционируемся на добавленное направление
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy( function(record) {
								if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
									return true;
								}
							});
							if (index > -1) {
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
						}
					}
				});
			}
		});
	},
	redirEvnDirection: function () {
		var record = this.getSelectedRecord();
		var grid = this.mainGrid.getGrid();
		if (record == false) {
			return false;
		}
		sw.Promed.Direction.redirect({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			userMedStaffFact: this.ownerWindow.userMedStaffFact,
			EvnDirection_id: record.get('EvnDirection_id'),
			callback: function (data) {
				// обновляем грид и позиционируемся на добавленное направление
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy( function(record) {
								if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
									return true;
								}
							});
							if (index > -1) {
								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
						}
					}
				});
			}
		});
	},
	/**
	 * cancelType - тип отмены направления (отменено/отклонено)
	 */
	cancelEvnDirection: function(cancelType) {
		if (!cancelType) {
			cancelType = 'cancel';
		}

		var win = this;
		var rec = this.getSelectedRecord();
		var grid = this.mainGrid.getGrid();
		log(['rec',rec]);
		return sw.Promed.Direction.cancel({
			cancelType: cancelType,
			ownerWindow: win.ownerWindow,
			formType: 'reg',
			allowRedirect: true,
			userMedStaffFact: win.ownerWindow.userMedStaffFact,
			EvnDirection_id: rec.get('EvnDirection_id')||null,
			DirType_Code: rec.get('DirType_id')||null,
			TimetableGraf_id: rec.get('TimetableGraf_id')||null,
			TimetableMedService_id: rec.get('TimetableMedService_id')||null,
			TimetableResource_id: rec.get('TimetableResource_id')||null,
			TimetableStac_id: rec.get('TimetableStac_id')||null,
			EvnQueue_id: rec.get('EvnQueue_id')||null,
			personData: win.persData||null,
			callback: function(cfg) {
				grid.getStore().reload();
			}
		});
	},
	emkOpen: function()
	{
		var record = this.getSelectedRecord();

		if ( !record.get('Person_id') )
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var userMedStaffFact = this.ownerWindow.userMedStaffFact;
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: userMedStaffFact,
			MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
			LpuSection_id: userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	confirmEvnDirection: function() {//потдвердить
        var thas = this;
		var record = this.getSelectedRecord();
		if ( Ext.isEmpty(record.get('DirType_id')) || !record.get('DirType_id').toString().inlist([ '1', '5' ])) {
			return false;
		}

		var params = {
			record: record,
			onConfirm: function(params){
				record.set('IsConfirmed', true);
				if (params && params['Hospitalisation_setDT'])
					record.set('Hospitalisation_setDT', params['Hospitalisation_setDT']);
				record.commit();
                thas.mainGrid.setActionDisabled('action_confirm', true);
			}
		};

		this.openForm('swHospDirectionConfirmWindow', langs('Подтверждение госпитализации'), params);
        return true;
	},
	getSelectedRecord: function() {
		var record = this.mainGrid.getGrid().getSelectionModel().getSelected();
		if ( !record || !record.get('EvnDirection_id') ) {
			log('bad',record)
			return false;
		}

		return record;
	},
	getLoadMask: function(msg) {
		if (this.ownerWindow && typeof this.ownerWindow.getLoadMask == 'function') {
			return this.ownerWindow.getLoadMask(msg);
		}

		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: msg });
		}
		return this.loadMask;
	},
	getCurrentDateTime: function() {
		var win = this;
		log('sw.Promed.BaseJournal.getCurrentDateTime');
		win.getLoadMask(langs('Получение текущей даты')).show();
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				win.getLoadMask().hide();
				if ( success && response.responseText != '' ) {
					var result  = Ext.util.JSON.decode(response.responseText);
					log('sw.Promed.BaseJournal.getCurrentDateTime', result);
					this.curDate = result.begDate;
					this.curTime = result.begTime;
					this.userName = result.pmUser_Name;
					this.currentDay();
					this.doReset(true);
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	stepDay: function(day)
	{
		var frm = this;
		var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	prevDay: function ()
	{
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	currentDay: function ()
	{
		var frm = this;
		var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
		var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.dateMenu.mode = 'oneday';
		frm.dateMenu.setAllowBlank(!frm.winType.inlist(['reg','call']));
		frm.setVisibleDateMenu(true);
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentWeek: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.dateMenu.mode = 'twodays';
		frm.dateMenu.setAllowBlank(!frm.winType.inlist(['reg','call']));
		frm.setVisibleDateMenu(true);
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	currentMonth: function ()
	{
		var frm = this;
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		frm.dateMenu.mode = 'twodays';
		frm.dateMenu.setAllowBlank(!frm.winType.inlist(['reg','call']));
		frm.setVisibleDateMenu(true);
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	range: function() {
		var frm = this;
		if (Ext.isEmpty(frm.dateMenu.getRawValue())) {
			var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
			var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
			frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
		}
		frm.dateMenu.mode = 'twodays';
		frm.dateMenu.setAllowBlank(!frm.winType.inlist(['reg','call']));
		frm.setVisibleDateMenu(true);
	},
	allTime: function ()
	{
		var frm = this;
		frm.dateMenu.setValue(null);
		frm.dateMenu.setAllowBlank(true);
		frm.setVisibleDateMenu(false);
		// frm.dateMenu.fireEvent("select", frm.dateMenu);
	},
	setVisibleDateMenu: function(visible) {
		if (this.WindowToolbar.items.get('prevArrowList')) {
			this.WindowToolbar.items.get('prevArrowList').setVisible(visible);
		}
		if (this.WindowToolbar.items.get('nextArrowList')) {
			this.WindowToolbar.items.get('nextArrowList').setVisible(visible);
		}
		this.dateMenu.setVisible(visible);
	},
	redirToQueue:function(){
		var record = this.getSelectedRecord();
		if (!record) {
			return false;
		}
		var win = this;
		var view_frame = this.mainGrid;
		sw.Promed.Direction.returnToQueue({
			loadMask: win.getLoadMask(langs('Пожалуйста подождите...')),
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableGraf_id: record.get('TimetableGraf_id'),
			TimetableMedService_id: record.get('TimetableMedService_id'),
			TimetableResource_id: record.get('TimetableResource_id'),
			TimetableStac_id: record.get('TimetableStac_id'),
			EvnQueue_id: record.get('EvnQueue_id'),
			callback: function (data) {
				// обновляем грид и позиционируемся на добавленное направление
				view_frame.getGrid().getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = view_frame.getGrid().getStore().findBy( function(record) {
								if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
									return true;
								}
							});

							if (index > -1) {
								view_frame.getGrid().getView().focusRow(index);
								view_frame.getGrid().getSelectionModel().selectRow(index);
							}
						}
					}
				});
			}
		});
	},
	openForm: function(name, title, params) {
		if ( getWnd(name).isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Форма ') + title + langs(' в данный момент открыта.'));
			return false;
		}
		else {
			getWnd(name).show(params);
		}
	},
	doReset: function(firstTime){
		var win = this;

		var form = this.mainForm.getForm();
		// Очистка полей фильтра И перезагрузка
		form.items.each(function(f){
			if(!f.disabled)
				f.reset();
		});

		if(Ext.getCmp('Referral_id'))
			Ext.getCmp('Referral_id').clearValue();

		form.findField('LpuSection_did').clearFilter();
		form.findField('LpuSection_did').clearValue();
		form.findField('LpuSection_did').getStore().removeAll();
		form.findField('LpuSection_did').disable();

		form.findField('MedService_did').clearFilter();
		form.findField('MedService_did').clearValue();
		form.findField('MedService_did').getStore().removeAll();
		form.findField('MedService_did').disable();

		if (firstTime) {
			if (this.ARMType && this.ARMType.inlist(['labdiag', 'funcdiag'])) {
				// для диагностики и лаборанта дату записи устанавливать не нужно
			} else {
				var curDate = new Date();
				curDate = Ext.util.Format.date(curDate, 'd.m.Y');
				if (this.resetRecordDate) {
					form.findField('RecordDate_from').setValue(curDate);
					form.findField('RecordDate_to').setValue(curDate);
				}
			}
			if (this.winType = 'queue') {
				form.findField('EvnStatus_id').setFieldValue('EvnStatus_SysNick', 'Queued');

				var base_form = this.mainForm.getForm();

				if ( isCallCenterAdmin() /*|| isSuperAdmin()*/ ) {
					var params = {
						withoutPaging: true
					};

					base_form.findField('UserLpu_id').setValue(getGlobalOptions().lpu_id);

					this.getLoadMask(langs('Загрузка списка пользователей...')).show();

					base_form.findField('pmUser_id').getStore().load({
						params: params,
						callback: function() {
							this.getLoadMask().hide();
							this.setUserListFilter();
						}.createDelegate(this)
					});
				}


			}
			if (win.TabPanel.getActiveTab() && win.TabPanel.getActiveTab().id == 'tab_incoming') {
				win.onTabChange();
			} else {
				win.TabPanel.setActiveTab(0);
			}

			base_form.findField('MedStaffFact_did').getStore().removeAll();
			base_form.findField('MedStaffFact_did').loadList({
				callback: function() {
					base_form.findField('MedStaffFact_did').medStaffFactFilter();
					if (win.ARMType != 'regpol' && win.ownerWindow.userMedStaffFact) {
						base_form.findField('MedStaffFact_did').setValue(win.ownerWindow.userMedStaffFact.MedStaffFact_id);
					}
				}
			});
		} else {
			win.onTabChange();
		}
	},
	getPeriodToggle: function (mode)
	{	
		switch(mode)
		{
		case 'day':
			return this.DTMenuActions.day.items[0];
			break;
		case 'week':
			return this.DTMenuActions.week.items[0];
			break;
		case 'month':
			return this.DTMenuActions.month.items[0];
			break;
		case 'range':
			return this.DTMenuActions.range.items[0];
			break;
		case 'allTime':
			return this.DTMenuActions.allTime.items[0];
			break;
		default:
			return null;
			break;
		}
	},
	useSearchType: false,
	doSearch: function(params) {
		var base_form = this.mainForm.getForm();

		var grid = this.mainGrid;
		var win = this;
		grid.removeAll();
		this.persData=null;
		
		var beg_date = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		var end_date = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		var Lpu_did = base_form.findField('Lpu_did').getValue() || null;
		var Lpu_sid = base_form.findField('Lpu_sid').getValue() || null;

		log('sw.Promed.BaseJournal.doSearch', params);

		if (typeof params=='object') {
			if(params.person_data&&params.person_data.Person_id){
				this.persData=params.person_data;
			}
			if (Lpu_sid) params.Lpu_sid = Lpu_sid;
			if (Lpu_did) params.Lpu_did = Lpu_did;
			params.useCase=win.useCase||'';
			params.winType=win.winType;
			params.loadAddFields=win.loadAddFields?win.loadAddFields:null;
			params.start = 0;
			params.limit = 100;
			if (false == win.winType.inlist(['reg','call'])
				&& (!params.beg_date||!params.end_date)
			) {
				sw.swMsg.alert(langs('Ошибка'), langs('Не указаны параметры периода дат.'));
				return false;
			}
			if (win.winType.inlist(['reg','call'])
				&& !params.Person_id
			) {
				sw.swMsg.alert(langs('Ошибка'), langs('Параметр Идентификатор человека обязателен для заполнения.'));
				return false;
			}
			grid.loadData({
				globalFilters: params,
				noFocusOnLoad: this.noFocusOnLoad
			});
		}
		else {
			var btn = this.getPeriodToggle(params);
			if (btn) {
				if (params != 'range') {
					if (this.mode == params) {
						btn.toggle(true);
						/*if (params != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
							return false;*/
					} else {
						this.mode = params;
					}
				}
				else {
					btn.toggle(true);
					this.mode = params;
				}
			}
			var Person_Birthday = base_form.findField('Person_Birthday').getValue() || null;
			var Person_SurName = base_form.findField('Person_SurName').getValue() || null;
			var Person_FirName = base_form.findField('Person_FirName').getValue() || null;
			var Person_SecName = base_form.findField('Person_SecName').getValue() || null;
			var MedService_did= base_form.findField('MedService_did').getValue() || null;
			var LpuSection_did= base_form.findField('LpuSection_did').getValue() || null;
			var LpuSectionProfile_did= base_form.findField('LpuSectionProfile_did').getValue() || null;
			var IsHospitalized= base_form.findField('IsHospitalized').getValue() || null;
			var IsConfirmed= base_form.findField('IsConfirmed').getValue() || null;
			var KlDistrict_sid= base_form.findField('KlDistrict_sid').getValue() || null;
			var EvnStatus_id= base_form.findField('EvnStatus_id').getValue() || null;
			var DirType_id= base_form.findField('DirType_id').getValue() || null;
			var EvnDirection_IsAuto = base_form.getValues().EvnDirection_IsAuto || null;
			var EvnDirection_Num = base_form.findField('EvnDirection_Num').getValue() || null;
			var RecordDate_from = Ext.util.Format.date(base_form.findField('RecordDate_from').getValue(), 'd.m.Y') || null;
			var RecordDate_to = Ext.util.Format.date(base_form.findField('RecordDate_to').getValue(), 'd.m.Y') || null;
			var VizitDate_from = Ext.util.Format.date(base_form.findField('VizitDate_from').getValue(), 'd.m.Y') || null;
			var VizitDate_to = Ext.util.Format.date(base_form.findField('VizitDate_to').getValue(), 'd.m.Y') || null;
			var Referral_id = null;
			var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue() || null;
			var onlyWaitingList = base_form.findField('onlyWaitingList').getValue() || null;

			if (isCallCenterAdmin()) {
				var UserLpu_id = base_form.findField('UserLpu_id').getValue() || null;
				var pmUser_id = base_form.findField('pmUser_id').getValue() || null;
			}
            if (Person_Birthday) {
                Person_Birthday = Ext.util.Format.date(Person_Birthday, 'd.m.Y');
            }
            if (isSuperAdmin()) {
				var onlySQL = base_form.findField('onlySQL').getValue() || null;
			}
			if (getRegionNick() == 'vologda'){
				var eQueueOnly = base_form.findField('eQueueOnly').getValue() || null;
			}

			if (win.winType === 'queue' && getRegionNick() === 'kz')
			{
				Referral_id = Ext.getCmp('Referral_id').getValue();
			}

			var SearchType = '';

			if (win.useSearchType) {
				if (win.TabPanel.getActiveTab() && win.TabPanel.getActiveTab().id == 'tab_outcoming') {
					SearchType = 'outcoming';
				} else {
					SearchType = 'incoming';
				}
			}

			if (false == win.winType.inlist(['reg','call'])
				&& this.mode != 'allTime'
				&& (!beg_date||!end_date)
			) {
				sw.swMsg.alert(langs('Ошибка'), langs('Не заполнен период дат.'));
				return false;
			}
			if (win.winType.inlist(['reg','call'])) {
				sw.swMsg.alert(langs('Ошибка'), langs('Параметр Идентификатор человека обязателен для заполнения.'));
				return false;
			}

			grid.loadData({
				globalFilters: {
					beg_date: beg_date,
					end_date: end_date,
					RecordDate_from: RecordDate_from,
					RecordDate_to: RecordDate_to,
					VizitDate_from: VizitDate_from,
					VizitDate_to: VizitDate_to,
					limit: 100,
					start: 0,
					Person_Birthday:Person_Birthday,
					Person_SecName:Person_SecName,
					Person_FirName:Person_FirName,
					Person_SurName:Person_SurName,
					MedService_did:MedService_did,
					LpuSection_did:LpuSection_did,
					LpuSectionProfile_did:LpuSectionProfile_did,
					Lpu_did:Lpu_did,
					Lpu_sid:Lpu_sid,
					pmUser_id:pmUser_id,
					IsHospitalized:IsHospitalized,
					EvnDirection_Num:EvnDirection_Num,
					KlDistrict_sid:KlDistrict_sid,
					IsConfirmed:IsConfirmed,
					EvnStatus_id:EvnStatus_id,
					DirType_id:DirType_id,
					EvnDirection_IsAuto:EvnDirection_IsAuto,
					loadAddFields:win.loadAddFields,
					useCase:win.useCase||'',
					winType:win.winType,
					SearchType:SearchType,
					dateRangeMode: this.mode,
					onlySQL:onlySQL,
					eQueueOnly:eQueueOnly,
					Referral_id: Referral_id,
					UslugaComplex_id: UslugaComplex_id,
					onlyWaitingList: onlyWaitingList
				},
				noFocusOnLoad: this.noFocusOnLoad
			});
		}
	},
	printTalonKvrachy: function(printOnCheck) {
		var grid = this.mainGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();
		var template;
		switch (getRegionNick())
		{
			case 'kz':
				template = 'pan_Talon_kvrachy.rptdesign';
				break;
			case 'ekb':
				template = ((printOnCheck === true) ? 'Print_form_of_ticket.rptdesign' : 'f025_4u_88.rptdesign');
				break;
			default:
				template = 'f025_4u_88.rptdesign';
		}
		if (rec && rec.get('TimetableGraf_id')) {
			printBirt({ // https://redmine.swan.perm.ru/issues/54910
				'Report_FileName': template,
				'Report_Params': (getRegionNick() == 'kz'?'&paramtimetablegraf=':'&paramTimeTableGraf=') + rec.get('TimetableGraf_id'),
				'Report_Format': 'pdf'
			});
		}
	},
	printTalonKvrachyForThermalPrinter: function() {
		if(getRegionNick() != 'kz') return false;
		var grid = this.mainGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();
		var template = 'print_tickettodoctor.rptdesign';
		if (rec && rec.get('TimetableGraf_id')) {
			printBirt({ 
				'Report_FileName': template,
				'Report_Params': '&ParamTimeTableGraf_id=' + rec.get('TimetableGraf_id'),
				'Report_Format': 'pdf'
			});
		}
	},
	printTicketDirection: function() {
		if(getRegionNick() != 'kz') return false;
		var grid = this.mainGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();
		var template = 'PrintTicketDirection.rptdesign';
		if (rec && rec.id) {
			printBirt({ 
				'Report_FileName': template,
				'Report_Params': '&paramEvnDirection=' + rec.id,
				'Report_Format': 'pdf'
			});
		}
	},
	printRouteList: function() {
		var grid = this.mainGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();
		if (rec && rec.id) {
			printBirt({ // https://redmine.swan.perm.ru/issues/54910
				'Report_FileName': 'EvnQueue.rptdesign',
				'Report_Params': '&paramEvnDirection=' + rec.id,
				'Report_Format': 'pdf'
			});
		}
	},
	printPredRecord: function() {
		var grid = this.mainGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();
		if (rec && rec.id) {
			printBirt({ // https://redmine.swan.perm.ru/issues/54910
				'Report_FileName': 'EvnDirection.rptdesign',
				'Report_Params': '&paramEvnDirection=' + rec.id,
				'Report_Format': 'pdf'
			});
		}
	},
	// 192492
	printForm: function(sReportName) {
		var grid = this.mainGrid.getGrid();
		var rec = grid.getSelectionModel().getSelected();
		if (rec && rec.id) {
			printBirt({ // https://redmine.swan.perm.ru/issues/54910
				'Report_FileName': sReportName,
				'Report_Params': '&paramEvnDirection=' + rec.id,
				'Report_Format': 'pdf'
			});
		}
	},
	setPrintItemVisible:function(oMenu, sUslugaId) {
		
		var aMenuItems = Object.keys(oMenu).map(function(key) {
			return oMenu[key];	
		});
		
		if (!! sUslugaId) {
			Ext.Ajax.request({
				url: '/?c=UslugaComplex&m=getUslugaComplexAttributes',
				params: {
					'UslugaComplex_id': sUslugaId
				},
				callback: function(opt, success, response) {
					var aAttributes = Ext.util.JSON.decode(response.responseText);
					aMenuItems.forEach(function(oItem){
						if (!!oItem.initialConfig && !!oItem.initialConfig.tag  && oItem.initialConfig.tag != '') {
							var oAttr = aAttributes.find(function(oAttr) {
								return oAttr.UslugaComplexAttributeType_SysNick == oItem.initialConfig.tag;
							}); 
							
							if (!! oAttr) {
								oItem.setHidden(getRegionNick() != 'vologda');
							} else {
								oItem.setHidden(true);
							}
						} 
					});
				}
			});
		} else {
			aMenuItems.forEach(function(oItem) {
				if (!!oItem.initialConfig && !!oItem.initialConfig.tag  && oItem.initialConfig.tag != '') {
					oItem.setHidden(true);
				}
			});
		}
	},
	// --- 192492
	printEvnDirection: function() {
		var grid = this.mainGrid.getGrid(),
			rec = grid.getSelectionModel().getSelected();

		if (!rec) {
			return false;
		}

		sw.Promed.Direction.print({
			EvnDirection_id: rec.get('EvnDirection_id')
		});
	},
	printEvnDirectionFree: function() {
		var grid = this.mainGrid.getGrid(),
			rec = grid.getSelectionModel().getSelected()
			
		if (!rec || !rec.get('EvnDirection_id')) {
			return false;
		}
		
		var params = {
			Evn_id: rec.get('EvnDirection_id'),
			fromBj: true,
			DirType_Code: rec.get('DirType_Code'),
			EvnClass_id: 27
		};
		
		getWnd('swPrintTemplateSelectWindow').show(params);
	},
	printLabDirections: function() {
		if(!this.persData.Person_id) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не выбран пациент.'));
			return;
		}
		var Lpu_id = getGlobalOptions().lpu_id;
		var Person_id = this.persData.Person_id;
		sw.Promed.EvnPrescr.openPrintDoc('/?c=EvnPrescr&m=printLabDirections&Lpu_id='+Lpu_id + '&Person_id=' + Person_id);
	},
	showHist:function(show){
		if(show&&isAdmin){
			this.hist = true;
			this.mainGrid.setColumnHidden('pmUser_Name',false);
			this.mainGrid.setColumnHidden('EvnDirection_insDT',false);
		}else{
			this.hist = false;
			this.mainGrid.setColumnHidden('pmUser_Name',true);
			this.mainGrid.setColumnHidden('EvnDirection_insDT',true);	
		}
	},
	checkBeforeLoadData: function(store, options) {
		return true;
	},
	onTabChange: function() {
		var win = this;
		//log('sw.Promed.BaseJournal.onTabChange', win.TabPanel.getActiveTab());
		var base_form = win.mainForm.getForm();
		if (!this.LpuSectionProfile_id) {
			this.LpuSectionProfile_id = base_form.findField('LpuSectionProfile_did').getValue();
		}
		if (win.TabPanel.getActiveTab() && win.TabPanel.getActiveTab().id == 'tab_outcoming') {
			var Lpu_sid = this.Lpu_sid || getGlobalOptions().lpu_id;
			base_form.findField('LpuSectionProfile_did').clearValue();
			base_form.findField('LpuSectionProfile_did').enable();
			if (!Ext.isEmpty(Lpu_sid)) {
				base_form.findField('Lpu_sid').setValue(Lpu_sid);
				base_form.findField('Lpu_sid').fireEvent('change', base_form.findField('Lpu_sid'), base_form.findField('Lpu_sid').getValue());
				base_form.findField('Lpu_sid').disable();
				base_form.findField('Lpu_did').clearValue();
				base_form.findField('Lpu_did').enable();
			}

			base_form.findField('IsConfirmed').clearValue();
			base_form.findField('IsHospitalized').clearValue();
			base_form.findField('IsConfirmed').hideContainer();
			base_form.findField('IsHospitalized').hideContainer();

			if ('record_from_queue' != this.useCase) {
				this.mainGrid.getAction('action_cancel_incoming').setHidden(true);
				this.mainGrid.getAction('action_cancel_outcoming').setHidden(false);
				this.mainGrid.getAction('action_add_incoming').setHidden(true);
			}
			if ('open_from_polka' == this.useCase) {
				this.mainGrid.getAction('action_adddirection').setHidden(false);
			}
		} else {
			var Lpu_did = this.Lpu_did || getGlobalOptions().lpu_id;
			if (win.ARMType == 'callcenter' || win.ARMType == 'regpol') {
				// если из АРМ регистратора или кол-центра, то поле профиль не заполнено и доступно для выбора
				//base_form.findField('LpuSectionProfile_did').clearValue();
				base_form.findField('LpuSectionProfile_did').setValue(this.LpuSectionProfile_id);
				base_form.findField('LpuSectionProfile_did').enable();
			} else {
				base_form.findField('LpuSectionProfile_did').setValue(this.LpuSectionProfile_id);
				base_form.findField('LpuSectionProfile_did').disable();
			}
			if (!Ext.isEmpty(Lpu_did)) {
				base_form.findField('Lpu_did').setValue(Lpu_did);
				base_form.findField('Lpu_did').fireEvent('change', base_form.findField('Lpu_did'), base_form.findField('Lpu_did').getValue());
				base_form.findField('Lpu_did').setDisabled(win.ARMType != 'callcenter');
				base_form.findField('Lpu_sid').clearValue();
				base_form.findField('Lpu_sid').enable();

				if (win.MedService_did) {
					base_form.findField('MedService_did').setValue(win.MedService_did);
				}
			}

			base_form.findField('IsConfirmed').showContainer();
			base_form.findField('IsHospitalized').showContainer();

			if ('record_from_queue' != this.useCase) {
				this.mainGrid.getAction('action_cancel_incoming').setHidden(false);
				this.mainGrid.getAction('action_cancel_outcoming').setHidden(true);
				this.mainGrid.getAction('action_add_incoming').setHidden(false);
			}
			if ('open_from_polka' == this.useCase) {
				this.mainGrid.getAction('action_adddirection').setHidden(true);
			}
		}

		base_form.findField('KlDistrict_sid').setContainerVisible(getRegionNick() == 'ekb');
		if (win.dateRangeMode) {
			var btn = win.getPeriodToggle(win.dateRangeMode);
			if (btn) {
				btn.toggle(true);
				btn.handler();
			}
		} else {
			if(! this.noAutoSearch)// #142955 поиск не автоматический
				this.doSearch(); // запускаем поиск, т.к. вкладка сменилась
		}
	},
	initComponent: function() {
		var win = this;
		this.hist = false;
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: langs('Период'),
			allowBlank: false,
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});

		this.dateMenu.addListener('keydown',function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				if(! this.noAutoSearch)// #142955 поиск не автоматический
					this.doSearch('period');// скорей всего имеется ввиду 'range'
				else
					this.mode = 'range';
			}
		}.createDelegate(this));
		this.dateMenu.addListener('select',function () {
			
			// Читаем расписание за период
			if(! this.noAutoSearch)// #142955 поиск не автоматический
				this.doSearch('range');
			else
				this.mode = 'range';
		}.createDelegate(this));
		this.dateMenu.addListener('blur',function () 
		{
			
		}.createDelegate(this));
		this.DTMenuActions = Array();
		this.DTMenuActions.day = new Ext.Action(
				{
					text: langs('День'),
					id:'dayLis',
					xtype: 'button',
					toggleGroup: 'periodToggle',
					pressed: true,
					handler: function()
					{
						this.currentDay();
						if(! this.noAutoSearch)// #142955 поиск не автоматический
							this.doSearch('day');
						else
							this.mode = 'day';
					}.createDelegate(this)
				});
		 this.DTMenuActions.week = new Ext.Action(
				{
					text: langs('Неделя'),
					id:'weekLis',
					xtype: 'button',
					toggleGroup: 'periodToggle',
					handler: function()
					{
						this.currentWeek();
						if(! this.noAutoSearch)// #142955 поиск не автоматический
							this.doSearch('week');
						else
							this.mode = 'week';
					}.createDelegate(this)
				});
		this.DTMenuActions.month = new Ext.Action(
				{
					id:'monthLis',
					text: langs('Месяц'),
					xtype: 'button',
					toggleGroup: 'periodToggle',
					handler: function()
					{
						this.currentMonth();
						if(! this.noAutoSearch)// #142955 поиск не автоматический
							this.doSearch('month');
						else
							this.mode = 'month';
					}.createDelegate(this)
				});
		this.DTMenuActions.range = new Ext.Action(
				{
					text: langs('Период'),
					xtype: 'button',
					toggleGroup: 'periodToggle',
					handler: function()
					{
						this.range();
						if(! this.noAutoSearch)// #142955 поиск не автоматический
							this.doSearch('range');
						else
							this.mode = 'range';
					}.createDelegate(this)
				});
		this.DTMenuActions.allTime = new Ext.Action(
				{
					text: langs('За все время'),
					xtype: 'button',
					toggleGroup: 'periodToggle',
					handler: function()
					{
						this.allTime();
						if(! this.noAutoSearch)// #142955 поиск не автоматический
							this.doSearch('allTime');
						else
							this.mode = 'allTime';
					}.createDelegate(this)
				});
		this.WindowToolbar = new Ext.Toolbar({
			id:'WindowToolbarLis',
			items: [
				new Ext.Action(
				{
					text: '',
					id:'prevArrowList',
					xtype: 'button',
					iconCls: 'arrow-previous16',
					handler: function()
					{
						// на один день назад
						this.prevDay();
						if(! this.noAutoSearch)// #142955 поиск не автоматический
							this.doSearch('range');
						else
							this.mode = 'range';
					}.createDelegate(this)
				}), 
				win.dateMenu,
				
				new Ext.Action(
				{
					text: '',
					id:'nextArrowList',
					xtype: 'button',
					iconCls: 'arrow-next16',
					handler: function()
					{
						// на один день вперед
						this.nextDay();
						if(! this.noAutoSearch)// #142955 поиск не автоматический
							this.doSearch('range');
						else
							this.mode = 'range';
					}.createDelegate(this)
				}), 
				{
					
					xtype: 'tbfill'
				},
				this.DTMenuActions.day, 
				this.DTMenuActions.week, 
				this.DTMenuActions.month,
				this.DTMenuActions.range,
				this.DTMenuActions.allTime
			]
		});

		this.RecordMenu = new Ext.menu.Menu({
			items: [{
				text:langs('Записать'),
				handler:function() {
					win.openEvnDirectionEditWindow('add', 1);
				}
			}, {
				text:langs('Записать с электронным направлением'),
				handler:function() {
					win.openEvnDirectionEditWindow('add', 2);
				}
			}]
		});

		this.mainGrid = new sw.Promed.ViewFrame(
		{
			id: 'EvnDirectionGrid_'+win.ownerWindow.id,
			region: 'center',
			//frame:true,
			title: null,
			object: 'EvnDirection',
			border: false,
			dataUrl: '/?c=EvnDirection&m=loadBaseJournal',
			toolbar: true,
			tbActions:true,
			autoLoadData: false,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			//Ufa, gaf #116422, для ГАУЗ РВФД
			onLoadData: function(sm, index, records) {
				if (this.onLoadData)
				{
					this.onLoadData(true);
				}
			}.createDelegate(this),
			checkBeforeLoadData: function(store, options) {
				return win.checkBeforeLoadData(store, options);
			},
			openEvnXmlEditWindow: function(key) {
				var view_frame = win.mainGrid;
				var store =  win.mainGrid.getGrid().getStore();
				var index = store.findBy(function(record){
					return record.get('EvnDirection_id') == key;
				});
				var record = store.getAt(index);
				var params = {
					title: langs('Бланк направления'),
					action: record.get('EvnXmlDir_id') ? 'edit' : 'add',
					userMedStaffFact: win.ownerWindow.userMedStaffFact,
					EvnClass_id: 27,
					XmlType_id: record.get('EvnXmlDirType_id')||null,
					UslugaComplex_id: null,
					EvnXml_id: record.get('EvnXmlDir_id')||null,
					Evn_id: record.get('EvnDirection_id'),
					onHide: function() {
						// обновляем грид и позиционируемся на направление
						view_frame.getGrid().getStore().reload({
							callback: function () {
								var index = view_frame.getGrid().getStore().findBy( function(rec) {
									if( rec.get('EvnDirection_id') == record.get('EvnDirection_id') ) {
										return true;
									}
								});

								if (index > -1) {
									view_frame.getGrid().getView().focusRow(index);
									view_frame.getGrid().getSelectionModel().selectRow(index);
								}
							}
						});
					}
				};
				getWnd('swEvnXmlEditWindow').show(params);
			},
			stringfields:
			[
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnDirection_IsAuto', type: 'int', hidden: true},
				{name: 'EvnDirection_TalonCode', header: "№ брони", width: 60},
				{name: 'EvnDirection_RecDate', header: langs("Запись")},
				{name: 'EvnXmlDir_link', header: "Бланк направления", width: 150, hidden: !(
					win.winType.toString().inlist(['reg','queue'])
					//&& (String(getGlobalOptions().groups).indexOf('RegUser', 0) >= 0 || String(getGlobalOptions().groups).indexOf('RegAdmin', 0) >= 0 )
					&& String(getGlobalOptions().groups).indexOf('BlankDirection', 0) >= 0
				), renderer: function(value, cellEl, rec){
					if (!rec.get('EvnXmlDirType_id')) {
						return '';
					}
					var link_title = langs('Открыть форму Бланк направления: Добавление'),
						link_label = langs('Заполнить бланк');
					if (rec.get('EvnXmlDir_id') > 0) {
						link_title = langs('Открыть форму Бланк направления: Просмотр/редактирование'),
						link_label = langs('Просмотр/редактирование бланка');
					}
					return '<a href="#" title="'+link_title+'" ' +
						'onclick="'+"Ext.getCmp('EvnDirectionGrid_"+win.ownerWindow.id+"').openEvnXmlEditWindow('"+ rec.get('EvnDirection_id') +"')"+'">'+
						link_label+'</a>';
				}},
				{name: 'accessType', type: 'int', hidden:true},
				{name: 'EvnXmlDir_id', width: 80, hidden:true},
				{name: 'EvnXmlDirType_id', width: 80, hidden:true},
				{name: 'TimetableGraf_id', width: 80, hidden:true},
				{name: 'TimetableMedService_id', width: 80, hidden:true},
				{name: 'TimetableResource_id', width: 80, hidden:true},
				{name: 'TimetableStac_id', width: 80, hidden:true},
				{name: 'EvnQueue_id', width: 80, hidden:true},
				{name: 'EvnQueue_Days', type: 'int', hidden:true},
				{
					name: 'EvnDirection_setDate',
					width: 80,
					header: langs('Дата направления'),
					renderer: function (value, cell, record) {
						var days = false;
						if (record.get('DirType_id') && record.get('DirType_id').inlist(['16', '3'])) { // На поликлинический прием и На консультацию
							days = parseInt(getGlobalOptions().promed_waiting_period_polka);
						} else if (record.get('DirType_id') && record.get('DirType_id').inlist(['1', '5'])) { // На госпитализацию плановую и На госпитализацию экстренную
							days = parseInt(getGlobalOptions().promed_waiting_period_stac);
						}
						if (days && !isNaN(days) && record.get('EvnQueue_Days') && record.get('EvnQueue_Days') > days) {
							var daysText = days + ' ' + ru_word_case('день', 'дня', 'дней', days);
							return value + " <img src='/img/icons/warn_red_round12.png' ext:qtip='Направление с периодом ожидания более " + daysText + "!' />";
						}
						return value;
					}
				},
				//{name: 'FreeRec', width: 100, header: 'Ближайшая'},
				{name: 'DirType_Code', hidden: true},
				{name: 'DirType_Name', width: 150, header: langs('Тип')},
				{name: 'EvnStatus_Name', width: 130, header: langs('Статус')},
				{name: 'DirType_id', width: 80, hidden:true},
				{name: 'EvnDirection_Num', width: 80, header: langs('Номер'), renderer: function(value, cellEl, rec){
					if (!rec.get('EvnDirection_id') || (rec.get('EvnDirection_IsAuto') && rec.get('EvnDirection_IsAuto') == 2)) {
						return value;
					}

					return '<a href="#" title="Открыть форму просмотра направления" onclick="'+"openEvnDirectionEditWindow('"+ rec.get('EvnDirection_id') +"', '"+ rec.get('Person_id') +"')"+'">'+value+'</a>';
				}},
				{name: 'EvnDirection_From', width: 200, header: langs('Кем направлен')},
				{name: 'EvnDirection_To', width: 300, header: langs('Куда направлен')},
				{name: 'Org_sid', width: 80,hidden:true},
				{name: 'Lpu_sid', width: 80, hidden:true},
				{name: 'Lpu_did', width: 80, hidden:true},
				{name: 'MedPersonal_id', hidden:true},
				{name: 'MedPersonal_did', hidden:true},
				{name: 'EvnStatus_SysNick', hidden:true},
				{name: 'LpuSection_sid', hidden:true},
				{name: 'loadAddFiles', hidden:true, hideable:false},
				{name: 'pmUser_Fio', width: 200, hidden:true, header: langs('Сотрудник, добавивший запись')},
				{name: 'LpuSectionProfile_Name', width: 100, header: langs('Профиль')},
				{name: 'MedPersonalDid', width: 200, header: langs('Врач')},
				{name: 'UslugaComplex_Name', width: 80, header: langs('Услуга')},
				{name: 'UslugaComplex_id',hidden:true},
				{name: 'Person_id',hidden:true},
				{name: 'IsConfirmed',hidden:true},
				{name: 'ARMType_id',hidden:true},
				{name: 'pmUser_updId',hidden:true},
				{name: 'Person_Fio', width: 180, header: langs('Пациент')},
				{name: 'Person_BirthDay', width: 80, header: langs('Д/р')},
				{name: 'Address_Address', width: 80, header: langs('Адрес')},
				{name: 'Person_Phone', width: 80, header: langs('Телефон')},
				{name: 'Diag_Name', width: 80, header: langs('Диагноз')},
				{name: 'inQueueCounter', width: 80, hidden: !getGlobalOptions().grant_individual_add_to_wait_list, header: langs('Позиция в очереди')},
				{name: 'EvnQueueStatus_Name', width: 80, hidden: !getGlobalOptions().grant_individual_add_to_wait_list, header: langs('Статус в очереди')},
				{name: 'QueueFailCause_Name', width: 80, hidden: !getGlobalOptions().grant_individual_add_to_wait_list, header: langs('Статус отмены очереди')},
				{name: 'EvnQueue_DeclineCount', width: 40, hidden: !getGlobalOptions().grant_individual_add_to_wait_list, header: langs('Отказов от бирки')},
				{name: 'RecMethodType_Name', width: 80, header: langs('Источник записи')},
				{name: 'pmUser_Name',hidden:true, type: 'string', header: langs('Создал'), width: 150},
				{name: 'EvnDirection_insDT',hidden:true, type: 'date',renderer: Ext.util.Format.dateRenderer('d.m.Y'), header: "Дата записи", width: 150},
				{name: 'Referral_id', header: langs('Передано в БГ'), width: 100, hidden: true, renderer: function (value, p, record) {

						if (getRegionNick() !== 'kz' || Ext.isEmpty(value))
						{
							return '';
						}

						var params = {scenario: "EvnDirection",id: record.get('Referral_id')};

						var ref = '<a href="#" onclick=\'getWnd("swTransfreredToBgInfoWindow").show(' + Ext.util.JSON.encode(params) + ')\'>Да</a>';

						return ref;
					}
				},
				{name: 'Referral_Code', type: 'string', hidden: true},
				{name: 'EvnDirectionLink_insDT', hidden: true}

			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
				[
					{ field: 'EvnDirection_From', tpl: '{EvnDirection_From}' },
					{ field: 'EvnDirection_To', tpl: '{EvnDirection_To}' }
				])
			],
			actions:
			[
				{
					name: 'action_adddirection',
					text: langs('Записать'),
					position: 0,
					hidden: ('action_add'.inlist(win.actions))?false:true,
					handler: function() {},
					icon: 'img/icons/add16.png',
					menu: win.RecordMenu
				}, {
					name:'action_add_incoming',
					text: langs('Внешнее направление'),
					position: 1,
					hidden:('action_add_incoming'.inlist(win.actions))?false:true,
					icon: 'img/icons/add16.png',
					handler: function (){
						win.openEvnDirectionEditWindow('add', 3);
					}
				}, {
					name:'action_cancel_outcoming',
					text: langs('Отменить'),
					position: 5,
					hidden:('action_delete'.inlist(win.actions))?false:true,
					icon: 'img/icons/delete16.png',
					disabled: true,
					handler: function (){
						win.cancelEvnDirection('cancel');
					}
				}, {
					name:'action_cancel_incoming',
					text: langs('Отклонить'),
					position: 5,
					hidden:('action_delete'.inlist(win.actions))?false:true,
					icon: 'img/icons/delete16.png',
					disabled: true,
					handler: function (){
						win.cancelEvnDirection('decline');
					}
				}, {
					name:'action_leave_queue',
					text: langs('Записать из очереди'),
					hidden:('action_leave_queue'.inlist(win.actions))?false:true,
					disabled: true,
					handler: function () {
						win.recInQueue('rec');
					}
				},
				{
					name:'action_in_queue',
					text: langs('Убрать в очередь'),
					hidden:('action_in_queue'.inlist(win.actions))?false:true,
					disabled:true,
					handler: function (){
						win.redirToQueue()
					}
				},
				{
					name:'action_redirect',
					text: langs('Перенаправить'),
					tooltip: langs('Выбрать другой объект для направления'),
					hidden:true,
					//hidden:('action_redirect'.inlist(win.actions))?false:true,
					disabled:true,
					handler: function (){
						win.redirEvnDirection()
					}
				},
				{
					name:'action_rewrite',
					text: langs('Перезаписать'),
					tooltip: langs('Выбрать другое время записи'),
					hidden:('action_rewrite'.inlist(win.actions))?false:true,
					disabled:true,
					handler: function (){
						win.rewriteEvnDirection()
					}
				},
				{
					name:'action_served',
					disabled:true,
					hidden:('action_served'.inlist(win.actions))?false:true,
					text: langs('Отметить как обслуженое'),
					handler: function (){

					}
				},
				{
					name:'action_emk',
					text: langs('Открыть ЭМК'),
					hidden:('action_emk'.inlist(win.actions))?false:true,
					disabled:true,
					handler: function (){
						win.emkOpen();
					}
				},
				{
					name:'action_confirm',
					text: langs('Подтвердить'),
					hidden:('action_confirm'.inlist(win.actions))?false:true,
					disabled:true,
					handler: function (){
						win.confirmEvnDirection();
					}
				},
				{name:'action_add',hidden:true,text:langs('Записать'), handler:function(){win.openEvnDirectionEditWindow('add')}},
				{name:'action_edit',hidden:('action_edit'.inlist(win.actions))?false:true,disabled:true},
				{name:'action_view',hidden:('action_view'.inlist(win.actions))?false:true, handler:function(){win.openEvnDirectionEditWindow('view')}},
				{name:'action_delete',hidden:true,text:"Отклонить/Отменить", handler:function(){win.cancelEvnDirection()}},
				{name:'action_refresh',hidden:('action_refresh'.inlist(win.actions))?false:true,hidden:true,disabled:true},
				{
					name: 'action_print',
					text: BTN_GRIDPRINT,
					tooltip: BTN_GRIDPRINT_TIP,
					icon: 'img/icons/print16.png', /*handler: function() {viewframe.printObjectList()}*/
					menuConfig: {
						printObject: {
							name: 'printObject', text: langs('Печать текущей строки'), handler: function () {
								win.mainGrid.printObject()
							}
						},
						printObjectList: {
							name: 'printObjectList',
							text: langs('Печать текущей страницы'),
							handler: function () {
								win.mainGrid.printObjectList()
							}
						},
						printObjectListFull: {
							name: 'printObjectListFull',
							text: langs('Печать всего списка'),
							handler: function () {
								win.mainGrid.printObjectListFull()
							}
						},
						printObjectListSelected: {
							name: 'printObjectListSelected',
							text: langs('Печать списка выбранных'),
							handler: function () {
								win.mainGrid.printObjectListSelected()
							}
						},
						printTalonKvrachy: {
							name: 'print_talon_kvrachy',
							icon: 'img/icons/print16.png',
							hidden: false,//(getRegionNick() != 'kz'),
							tooltip: langs('Талон на прием к врачу'),
							text: langs('Талон на прием к врачу'),
							disabled: true,
							handler: function () {
								win.printTalonKvrachy();
							}
						},
						printTalonKvrachyForThermalPrinter: {
							name: 'print_talon_kvrachy_for_thermal_printer',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'kz'),
							tooltip: langs('Талон на прием к врачу для термопринтера'),
							text: langs('Талон на прием к врачу для термопринтера'),
							disabled: true,
							handler: function () {
								win.printTalonKvrachyForThermalPrinter();
							}
						},
						printTicketDirection: {
							name: 'print_ticket_direction',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'kz'),
							tooltip: langs('Талон направления на исследование'),
							text: langs('Талон направления на исследование'),
							disabled: true,
							handler: function () {
								win.printTicketDirection();
							}
						},
						printTalonKvrachyCheck: {
							name: 'print_talon_kvrachy_check',
								icon: 'img/icons/print16.png',
								hidden: ! getRegionNick().inlist(['ekb']),
								tooltip: langs('Талон на прием к врачу (Чековая бумага)'),
								text: langs('Талон на прием к врачу (Чековая бумага)'),
								disabled: true,
								handler: function () {
								win.printTalonKvrachy(true);
							}
						},
						printRouteList: {
							name: 'printRouteList',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() == 'kz'),
							tooltip: langs('Печать маршрутного листа'),
							text: langs('Печать маршрутного листа'),
							//disabled: true,
							handler: function () {
								win.printRouteList();
							}
						},
						printPredRecord: {
							name: 'printPredRecord',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() == 'kz'),
							tooltip: langs('Печать листа предварительной записи'),
							text: langs('Печать листа предварительной записи'),
							//disabled: true,
							handler: function () {
								win.printPredRecord();
							}
						},
						printDirection: {
							name: 'print_direction',
							icon: 'img/icons/print16.png',
							text: langs('Печать направления'),
							tooltip: langs('Печать направления'),
							//disabled: true,
							handler: function () {
								win.printEvnDirection();
							}
						},
						printDirectionFree: {
							name: 'print_direction_freedoc',
							icon: 'img/icons/print16.png',
							text: 'Печать шаблона документа',
							tooltip: 'Печать шаблона документа',
							disabled: false,
							handler: function() {
								win.printEvnDirectionFree();
							}
						},
						printLabDirections: {
							name: 'print_lab_direction',
							icon: 'img/icons/print16.png',
							text: 'Печать единого направления на лабораторные исследования',
							hidden: getRegionNick() != 'ufa',
							tooltip: 'Печать единого направления на лабораторные исследования',
							disabled: false,
							handler: function() {
								win.printLabDirections();
							}
						},
						// 192492
						printDirection200u: {
							name: 'print_direction_200u',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 200/у»',
							tooltip: 'Печать «Форма 200/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF200u.rptdesign');
							}														
						},
						printDirection201u: {
							name: 'print_direction_201u',
							tag: 'AnalysisHematological',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 201/у»',
							tooltip: 'Печать «Форма 201/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF201u.rptdesign');
							}
						},
						printDirection202u: {
							name: 'print_direction_202u',
							tag: 'AnalysisBlood2',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 202/у»',
							tooltip: 'Печать «Форма 202/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF202u.rptdesign');
							}
						},
						printDirection210u: {
							name: 'print_direction_210u',
							tag: 'AnalysisUrine',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 210/у»',
							tooltip: 'Печать «Форма 210/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF210u.rptdesign');
							}
						},
						printDirection212u: {
							name: 'print_direction_212u',
							tag: 'AnalysisUrine2',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 212/у»',
							tooltip: 'Печать «Форма 212/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF212u.rptdesign');
							}
						},
						printDirection213u: {
							name: 'print_direction_213u',
							tag: 'GlucosuricProfile',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 213/у»',
							tooltip: 'Печать «Форма 213/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF213u.rptdesign');
							}
						},
						printDirection215u: {
							name: 'print_direction_215u',
							tag: 'AnalysisUrine3',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 215/у»',
							tooltip: 'Печать «Форма 215/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF215u.rptdesign');
							}
						},
						printDirection224u: {
							name: 'print_direction_224u',
							tag: 'AnalysisBlood',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 224/у»',
							tooltip: 'Печать «Форма 224/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF224u.rptdesign');
							}
						},
						printDirection225u: {
							name: 'print_direction_225u',
							tag: 'AnalysisBlood3',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 225/у»',
							tooltip: 'Печать «Форма 225/у»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF225u.rptdesign');
							}
						},
						printDirection452u06: {
							name: 'print_direction_452u06',
							tag: 'Chemical',
							icon: 'img/icons/print16.png',
							hidden: (getRegionNick() != 'vologda'),
							text: 'Печать «Форма 452/у-06»',
							tooltip: 'Печать «Форма 452/у-06»',
							disabled: false,
							handler: function() {
								win.printForm('printEvnDirectionF452u06.rptdesign');
							}
						},
						// --- 192492
					}
				}],
			onRowSelect: function (sm,index,record)
			{
				var rec = win.getSelectedRecord();
				if(!rec)return false;
				//alert(rec.get('TimeTableGraf_id'));
				var current_date = Date.parseDate(win.curDate + ' ' + win.curTime, 'd.m.Y H:i:s');
				var our = ((win.ARMType == 'callcenter' && rec.get('DirType_id').inlist(['3','16'])) || (win.ARMType != 'callcenter' && getGlobalOptions().lpu_id.inlist([rec.get('Lpu_did'),rec.get('Lpu_sid')])));
				var isConfirm = (rec.get('IsConfirmed')!=2&&rec.get('DirType_id').inlist(['1','5']))
				var isLabVologda = (getRegionNick()==='vologda' && getCurArm()==='lab'); //#PROMEDWEB-14156
				this.getAction('action_view').setDisabled(rec.get('EvnDirection_IsAuto') == 2);
				this.getAction('action_confirm').setDisabled(!isConfirm||!our);
				this.getAction('action_emk').setDisabled(!rec.get('Person_id')||!our);
				this.getAction('action_leave_queue').setDisabled(rec.get('EvnStatus_SysNick') != 'Queued'||!rec.get('EvnQueue_id')||!our||isLabVologda);
				this.getAction('action_print').setDisabled(rec.get('accessType') == 0||!rec.get('EvnDirection_id'));
				this.getAction('action_in_queue').setDisabled(rec.get('EvnStatus_SysNick') != 'DirZap'||!our||isLabVologda);
				this.getAction('action_redirect').setDisabled(rec.get('EvnStatus_SysNick') == 'Serviced'||!rec.get('EvnDirection_id')||!our);
				this.getAction('action_rewrite').setDisabled(rec.get('EvnStatus_SysNick') != 'DirZap'||!our||isLabVologda);
				win.mainGrid.getAction('action_print').menu.printTalonKvrachy.setDisabled(rec.get('accessType') == 0||!rec.get('TimetableGraf_id')||!our);
				win.mainGrid.getAction('action_print').menu.printTalonKvrachyForThermalPrinter.setDisabled(rec.get('accessType') == 0||!rec.get('TimetableGraf_id')||!our);
				win.mainGrid.getAction('action_print').menu.printTalonKvrachyCheck.setDisabled(rec.get('accessType') == 0||!rec.get('TimetableGraf_id')||!our||!getRegionNick().inlist(['ekb']));
				win.mainGrid.getAction('action_print').menu.printTicketDirection.setDisabled(rec.get('accessType') == 0||rec.get('DirType_id') != 10);
				win.mainGrid.getAction('action_print').menu.printRouteList.setDisabled(rec.get('accessType') == 0||rec.get('EvnQueue_id')||rec.get('TimetableStac_id'));
				win.mainGrid.getAction('action_print').menu.printPredRecord.setDisabled(rec.get('accessType') == 0||rec.get('EvnQueue_id')||rec.get('TimetableStac_id'));
				win.mainGrid.getAction('action_print').menu.printDirection.setDisabled(rec.get('EvnDirection_IsAuto') == 2);
				//win.mainGrid.getAction('action_print').menu.printDirectionFree.setDisabled(rec.get('EvnDirection_IsAuto') == 2);
				//this.getAction('print_talon_kvrachy').setDisabled(!rec.get('TimetableGraf_id')||!our);
				
				// 192492
				if (getRegionNick() == 'vologda') {
					win.setPrintItemVisible(win.mainGrid.getAction('action_print').menu, rec.get('UslugaComplex_id'));	
				}
				// -- 192492

				
				var is_decline = true;
				
				if (win.TabPanel.getActiveTab() && win.TabPanel.getActiveTab().id == 'tab_outcoming') {
					if (!(win.ARMType == 'callcenter' && rec.get('ARMType_id') == 24)) {
						is_decline = false;
					}
				}
				else {
					if (getGlobalOptions().lpu_id && rec.get('Lpu_sid') == getGlobalOptions().lpu_id) {
						is_decline = false;
					}
				}

				var disable_cancel = win.allowCancelDirectionOrRecord(rec, our);
					
				this.getAction('action_cancel_outcoming').setHidden(is_decline);
				this.getAction('action_cancel_incoming').setHidden(!is_decline);
					
				this.getAction('action_cancel_outcoming').setDisabled(disable_cancel);
				this.getAction('action_cancel_incoming').setDisabled(disable_cancel);
			}
		});

		this.TabPanel = new Ext.TabPanel({
			border: false,
			bodyStyle: 'margin-bottom: 5px',
			listeners:
			{
				'tabchange': function(tab, panel)
				{
					win.onTabChange();
				}
			},
			items: [{
				title: langs('Входящие'),
				id: 'tab_incoming',
				border: false,
				items: []
			},{
				title: langs('Исходящие'),
				id: 'tab_outcoming',
				border: false,
				items: []
			}]
		});

		this.mainForm = new Ext.form.FormPanel({
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.doSearch();
				},
				scope: this,
				stopEvent: true
			}],
			region: 'north',
			autoHeight: true,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'BaseJournalFilter' + win.ownerWindow.id,
			labelAlign: 'right',
			labelWidth: 150,
			bbar: [{
				tabIndex: TABINDEX_TRVVW + 5,
				xtype: 'button',
				text: langs('Найти'),
				iconCls: 'search16',
				handler: function () {
					win.doSearch();
				}
			}, {
				tabIndex: TABINDEX_TRVVW + 6,
				xtype: 'button',
				text: langs('Сброс'),
				iconCls: 'resetsearch16',
				handler: function () {
					win.doReset();
				}
			}],
			items: [win.TabPanel, {
				border: false,
				layout: 'column',
				bodyStyle: 'background:#DFE8F6; padding-left: 150px;',
				defaults: { bodyStyle: 'background:#DFE8F6; margin-left: 8px;' },
				items: [{
					border: false,
					layout: 'form',
					items: [{
						xtype: 'radio',
						hideLabel: true,
						boxLabel: langs('Все'),
						inputValue: 0,
						name: 'EvnDirection_IsAuto',
						checked: true
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'radio',
						hideLabel: true,
						boxLabel: langs('Направления'),
						inputValue: 1,
						name: 'EvnDirection_IsAuto'
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						xtype: 'radio',
						hideLabel: true,
						boxLabel: langs('Записи'),
						inputValue: 2,
						name: 'EvnDirection_IsAuto'
					}]
				},{
					border: false,
					layout: 'form',
					autoheight: true,
					items: [{
						name: 'onlyWaitingList',
						width: 180,
						xtype: 'checkbox',
						labelSeparator: '',
						hidden: !getGlobalOptions().grant_individual_add_to_wait_list,
						boxLabel: langs('Только листы ожидания'),
						listeners: {
							change: function(el, newVal, oldVal) {

                                this.mainGrid.setColumnHidden('Diag_Name', newVal);
                                this.mainGrid.setColumnHidden('EvnDirection_TalonCode', newVal);
                                this.mainGrid.setColumnHidden('pmUser_Fio', newVal);
                                this.mainGrid.setColumnHidden('UslugaComplex_Name', newVal);

								// #142955 поиск не автоматический
								if(!this.noAutoSearch) { this.doSearch(); }
							}.createDelegate(this)
						}
					}]
				},{
					border: false,
					layout: 'form',
					autoheight: true,
					hidden: getRegionNick() != 'vologda',
					items: [{
						boxLabel: 'Только с электронным направлением',
						name: 'eQueueOnly',
						labelSeparator: '',
						width: 180,
						xtype: 'checkbox'
					}]
				}]
			}, {
				layout: 'column',
				border: false,
				items: [{
					layout: 'form',
					width: 350,
					items: [new Ext.ux.Andrie.Select({
						fieldLabel: langs('Тип направления'),
						multiSelect: true,
						mode: 'local',
						listWidth: 400,
						anchor: '100%',
						store: new Ext.db.AdapterStore({
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
						}),
						displayField: 'DirType_Name',
						valueField: 'DirType_id',
						hiddenName: 'DirType_id'
					}), new Ext.ux.Andrie.Select({
						fieldLabel: langs('Статус'),
						multiSelect: true,
						mode: 'local',
						listWidth: 400,
						anchor: '100%',
						store: new Ext.db.AdapterStore({
							autoLoad: false,
							dbFile: 'Promed.db',
							fields: [
								{name: 'EvnStatus_Name', mapping: 'EvnStatus_Name'},
								{name: 'EvnStatus_SortCode', mapping: 'EvnStatus_SortCode'},
								{name: 'EvnStatus_SysNick', mapping: 'EvnStatus_SysNick'},
								{name: 'EvnStatus_id', mapping: 'EvnStatus_id'}
							],
							key: 'EvnStatus_id',
							sortInfo: {field: 'EvnStatus_SortCode'},
							tableName: 'EvnStatus'
						}),
						codeField: 'EvnStatus_SortCode',
						displayField: 'EvnStatus_Name',
						valueField: 'EvnStatus_id',
						hiddenName: 'EvnStatus_id'
					})]
				}, {
					layout: 'form',
					id: 'BaseJournalFilter_SecondColumn',
					width: 400,
					labelWidth: 200,
					items: [{
						fieldLabel: langs('Госпитализация одобрена'),
						anchor: '100%',
						xtype: 'swyesnocombo',
						hiddenName: 'IsConfirmed',
						name: 'IsConfirmed'
					},
					{
						fieldLabel: langs('Направившая МО'),
						listeners: {
							'blur': function(combo) {
								if (!combo.getValue()) {
									combo.setRawValue('');
								}
							}
						},
						listWidth: 300,
						allowTextInput: true,
						ctxSerach:true,
						anchor: '100%',
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_sid',
						name: 'Lpu_sid'
					}]
				}, {
					layout: 'form',
					width: 400,
					labelWidth: 200,
					items: [{
						fieldLabel: langs('Госпитализирован'),
						anchor: '100%',
						xtype: 'swyesnocombo',
						hiddenName: 'IsHospitalized',
						name: 'IsHospitalized'
					}, {
						xtype: 'textfieldpmw',
						name:'EvnDirection_Num',
						hiddenName: 'EvnDirection_Num',
						fieldLabel: langs('Номер направления'),
						anchor: '100%',
						listeners:
						{
							'keypress': function (inp, e)
							{
								if (e.getKey() == Ext.EventObject.ENTER)
								{
									e.stopEvent();
									if(! this.noAutoSearch)// #142955 поиск не автоматический
										this.doSearch();
								}
							}.createDelegate(this)
						}
					}, {
						border: false,	
						layout: 'form',
						autoheight: true,
						hidden: !isSuperAdmin(),
						items: [{
							fieldLabel: langs('SQL-запрос'),
							name: 'onlySQL',
							width: 180,
							xtype: 'checkbox'
						}]
					}
					]
				}, {
					layout: 'form',
					width: 400,
					labelWidth: 200,
					items: [{
						fieldLabel: langs('Округ'),
						listWidth: 300,
						//hidden: getRegionNick() !='ekb',
						anchor: '100%',
						comboSubject: 'KlDistrict',
						hiddenName: 'KlDistrict_sid',
						xtype: 'swcommonsprcombo'
					}]
				}]
			}, { // Дата записи и дата визита
				layout: 'column',
				items:
					[
						{
							layout: 'form',
							items:
								[{
									xtype: 'swdatefield',
									tabIndex: TABINDEX_EPSRSW + 8,
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									width: 100,
									listeners: {
										render: function (e) {
											Ext.QuickTips.register({
												target: e.getEl(),
												text: langs('Период дат записи')
											});
										},
										'keydown': this.onKeyDown.createDelegate(this)
									},
									name: 'RecordDate_from',
									hiddenName: 'RecordDate_from',
									fieldLabel: langs('Дата записи с')
								}]
						},
						{
							layout: 'form',
							labelWidth: 30,
							items:
								[{
									xtype: 'swdatefield',
									tabIndex: TABINDEX_EPSRSW + 9,
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									width: 100,
									listeners: {
										render: function (e) {
											Ext.QuickTips.register({
												target: e.getEl(),
												text: langs('Период дат записи')
											});
										},
										'keydown': this.onKeyDown.createDelegate(this)
									},
									name: 'RecordDate_to',
									hiddenName: 'RecordDate_to',
									fieldLabel: langs('по')
								}]
						},
						{
							layout: 'form',
							labelWidth: 130,
							items:
								[{
									xtype: 'swdatefield',
									tabIndex: TABINDEX_EPSRSW + 10,
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									width: 100,
									listeners: {
										render: function (e) {
											Ext.QuickTips.register({
												target: e.getEl(),
												text: langs('Период дат посещений')
											});
										},
										'keydown': this.onKeyDown.createDelegate(this)
									},
									name: 'VizitDate_from',
									hiddenName: 'VizitDate_from',
									fieldLabel: langs('Дата посещения с')
								}]
						},
						{
							layout: 'form',
							labelWidth: 30,
							items:
								[{
									xtype: 'swdatefield',
									tabIndex: TABINDEX_EPSRSW + 11,
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									width: 100,
									listeners: {
										render: function (e) {
											Ext.QuickTips.register({
												target: e.getEl(),
												text: langs('Период дат посещений')
											});
										},
										'keydown': this.onKeyDown.createDelegate(this)
									},
									name: 'VizitDate_to',
									hiddenName: 'VizitDate_to',
									fieldLabel: langs('по')
								}]
						},
						{
							layout: 'form',
							labelWidth: 65,
							items:
								[{
									xtype: 'swuslugacomplexmedservicecomdo',
									width: 450,
									baseParams: {UslugaGost_Code: 'FU', level:0},
									name: 'Search_Usluga',
									fieldLabel: 'Услуга',
									allowBlank: true,
									listeners: {
										'keydown': this.onKeyDown.createDelegate(this)
									}
								}]
						}
					]
			}, {
				style: 'padding: 0; padding-top: 5px; margin: 0',
				title: langs('Куда направлен'),
				xtype: 'fieldset',
				autoHeight: true,
				width: 985,
				items: [{
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						width: 350,
						items: [{
							fieldLabel: langs('МО'),
							listeners: {
								'blur': function(combo) {
								if (!combo.getValue()) {
									combo.setRawValue('');
								}
							},
							'change': function(combo, newValue, oldValue) {
								var base_form = win.mainForm.getForm();
								if (newValue) {
									base_form.findField('LpuSection_did').enable();
									base_form.findField('LpuSection_did').getStore().load({
										params: { Lpu_id: newValue },
										callback: function() {
											var base_form = win.mainForm.getForm();
											base_form.findField('LpuSection_did').lpuSectionFilter();	
										}
									});

									if (win.ARMType && win.ARMType.inlist(['labdiag', 'funcdiag'])) {
										// для этих АРМов служба устанавливается при запуске формы и недоступна для редактирования
									} else {
										base_form.findField('MedService_did').enable();
									}
									base_form.findField('MedService_did').getStore().load({
										params: {Lpu_id: newValue}, callback: function () {
											if (win.MedService_did) {
												var index = base_form.findField('MedService_did').getStore().findBy(function (rec) {
													return rec.get('MedService_id') == win.MedService_did;
												});

												if (index >= 0) {
													base_form.findField('MedService_did').setValue(win.MedService_did);
												} else {
													base_form.findField('MedService_did').clearValue();
												}
											}
										}
									});
								} else {
									base_form.findField('LpuSection_did').clearValue();
									base_form.findField('LpuSection_did').getStore().removeAll();
									base_form.findField('LpuSection_did').disable();

									if (win.ARMType && win.ARMType.inlist(['labdiag', 'funcdiag'])) {
										// для этих АРМов служба устанавливается при запуске формы и недоступна для редактирования
									} else {
										base_form.findField('MedService_did').clearValue();
										base_form.findField('MedService_did').getStore().removeAll();
										base_form.findField('MedService_did').disable();
									}
								}
							}
						},
							listWidth: 400,
							anchor: '100%',
							allowTextInput: true,
							ctxSerach:true,
							xtype: 'swlpucombo',
							hiddenName: 'Lpu_did',
							name: 'Lpu_did'
						}, {
							fieldLabel: langs('Служба'),
							hiddenName: 'MedService_did',
							allowBlank: true,
							listWidth: 400,
							anchor: '100%',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.mainForm.getForm();
									var params = {
										MedService_id: newValue
									};
									base_form.findField('MedStaffFact_did').reloadList({
										params: params,
										callback: function() {
											base_form.findField('MedStaffFact_did').medStaffFactFilter();
										}
									});
									

								}
							},
							xtype:'swmedservicecombo'
						}]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 200,
						items: [{
							fieldLabel: langs('Профиль'),
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.mainForm.getForm();
									base_form.findField('LpuSection_did').lpuSectionFilter();
									base_form.findField('MedStaffFact_did').medStaffFactFilter();
								}
							},
							listWidth: 300,
							anchor: '100%',
							xtype: 'swlpusectionprofilecombo',
							hiddenName: 'LpuSectionProfile_did',
							name: 'LpuSectionProfile_did'
						}, {
							fieldLabel: langs('Отделение'),
							disabled: true,
							listWidth: 300,
							anchor: '100%',
							xtype: 'swlpusectioncombo',
							hiddenName: 'LpuSection_did',
							name: 'LpuSection_did',
							lpuSectionFilter: function() {
								var base_form = win.mainForm.getForm();
								var LpuSectionProfile_did = base_form.findField('LpuSectionProfile_did').getValue();
								this.clearValue();
								this.getStore().clearFilter();
								this.getStore().filterBy(function(rec) {
									return rec.get('LpuSectionProfile_id') == LpuSectionProfile_did;
								});
								this.lastQuery = '';
							},
							listeners: {
								'change' : function(combo, newValue, oldValue) {
									var base_form = win.mainForm.getForm();
									base_form.findField('MedStaffFact_did').medStaffFactFilter();
								}
							}
						}, {
							allowBlank: true,
							listWidth: 600,
							fieldLabel: langs('Врач'),
							medStaffFactFilter: function() {

								var base_form = win.mainForm.getForm();
								var LpuSection_did = base_form.findField('LpuSection_did').getValue();
								var LpuSectionProfile_did = base_form.findField('LpuSectionProfile_did').getValue();

								base_form.findField('MedStaffFact_did').clearFilter();
								base_form.findField('MedStaffFact_did').clearValue();
									
								base_form.findField('MedStaffFact_did').getStore().filterBy(function(rec) {
									return (
										(!LpuSection_did || rec.get('LpuSection_id') == LpuSection_did) 
										&& (!LpuSectionProfile_did || rec.get('LpuSectionProfile_msfid') == LpuSectionProfile_did)
									);
								});
								
								base_form.findField('MedStaffFact_did').lastQuery = '';
							},
							anchor: '100%',
							id: 'MedStaffFact_did',
							name: 'MedStaffFact_did',
							xtype: 'swmedstafffactpostcombo'
						}]
					}]
				}]
			}, {
				style: 'padding: 0; padding-top: 5px; margin: 0',
				title: langs('Пациент'),
				xtype: 'fieldset',
				autoHeight: true,
				width: 985,
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						width: 350,
						items: [{
							fieldLabel: langs('Фамилия'),
							xtype: 'textfield',
							hiddenName: 'Person_SurName',
							name: 'Person_SurName'
						}, {
							fieldLabel: langs('Д/р'),
							xtype: 'swdatefield',
							name: 'Person_Birthday'
						}]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 200,
						items: [{
							fieldLabel: langs('Имя'),
							xtype: 'textfield',
							hiddenName: 'Person_FirName',
							name: 'Person_FirName'
						}, {
							fieldLabel: langs('Отчество'),
							xtype: 'textfield',
							hiddenName: 'Person_SecName',
							name: 'Person_SecName'
						}]
					}]
				}]
			}, {
				style: 'padding: 0; padding-top: 5px; margin: 0',
				title: langs('Администратор call-центра'),
				xtype: 'fieldset',
				autoHeight: true,
				hiddenName: 'call_admin',
				name: 'call_admin',
				width: 985,
				hidden: (isCallCenterAdmin())?false:true,
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						width: 350,
						items: [{
							xtype: 'swpmusercombo',
							width: 170,
							allowBlank: true,
							tabIndex: TABINDEX_EPSRSW + 6,
							hiddenName:'pmUser_id',
							fieldLabel: langs('Пользователь'),
							listWidth: 300,
							listeners: {
								'keypress': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										if(! this.noAutoSearch)// #142955 поиск не автоматический
											this.doSearch();
									}
								}.createDelegate(this)
							}
						}, {
							editable : true,
							forceSelection: true,
							hiddenName: 'UserLpu_id',
							fieldLabel: langs('МО пользователя'),
							allowBlank: true,
							lastQuery : '',
							listeners: {
								'keypress': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										if(! this.noAutoSearch)// #142955 поиск не автоматический
											this.doSearch();
									}
								}.createDelegate(this),
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('Lpu_id') == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								},
								'select': function(combo, record, index) {
									win.setUserListFilter();
								}
							},
							listWidth : 400,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{[(values.Lpu_EndDate && values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыто "+ values.Lpu_EndDate /* Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate.slice(0,10), "Y-m-d"), "d.m.Y")"*/ + ")" : values.Lpu_Nick ]}&nbsp;',
								'</div></tpl>'
							),
							typeAhead: true,
							anchor: '100%',
							xtype : 'swlpulocalcombo'
						}]
					}, {
						layout: 'form',
						width: 400,
						labelWidth: 200,
						items: [{
							xtype:'checkbox',
							checked: false,
							fieldLabel:langs('Поиск по пользователям call-центра'),
							handler: function(value) {
								win.setUserListFilter();
							},
							name: 'onlyCallCenterUsers'
						}]
					}]
				}]
			}]
		});

		Ext.apply(this, 
		{
			layout: 'border',
			items: [
				win.mainForm,
				win.mainGrid
			],
			tbar: win.WindowToolbar
		});
		sw.Promed.BaseJournal.superclass.initComponent.apply(this, arguments);

		this.ownerWindow.on('show', function() {
			var base_form = win.mainForm.getForm();
			var grid = win.mainGrid.getGrid();
			win.dateMenu.allowBlank = false;
			switch(win.winType){
				case'call':
				case'reg':
					win.dateMenu.allowBlank = true;
					win.mainForm.setVisible(false);
					win.WindowToolbar.setVisible(false);
					break;
				case'queue':
					win.getCurrentDateTime();

					if (getRegionNick() === 'kz')
					{
						var transferedToBg = {
							border: false,
							labelWidth: 170,
							layout: 'form',
							hidden: ! getRegionNick().inlist(['kz']),
							items: [{
								id: 'Referral_id',
								fieldLabel: 'Передано в БГ',
								name: 'Referral_id',
								width: 100,
								xtype: 'swyesnocombo'
							}]
						};

						Ext.getCmp('BaseJournalFilter_SecondColumn').add(transferedToBg);
						//Ext.getCmp('BaseJournalFilter_SecondColumn').doLayout();

						var clmnidx = grid.getColumnModel().findColumnIndex('Referral_id');
						grid.getColumnModel().setHidden(clmnidx, false);
					}

					// тип 24-Регистратура, только для ЭО
					win.mainForm.getForm().findField('DirType_id').getStore().baseParams.where = " where DirType_id != 24";
					win.mainForm.getForm().findField('DirType_id').getStore().load({
						params: {},
						callback: function() {
							this.filterBy(function(rec) {
								return !rec.get('DirType_Code').inlist([7]); //исключаем типы направлений
							});
						}
					});

					win.mainForm.getForm().findField('EvnStatus_id').getStore().baseParams.where = " where EvnClass_id = 27 and EvnStatus_SysNick not in ('DirNew')";
					win.mainForm.getForm().findField('EvnStatus_id').getStore().load({
						params: {}
					});

					break;
			}

			win.mainGrid.getAction('action_cancel_incoming').setHidden(true);
			win.mainGrid.getAction('action_cancel_outcoming').setHidden(false);

			win.doLayout();
		});
	},
	allowCancelDirectionOrRecord: function (rec, our, is_decline)
	{
		var win = this,
			data = rec? rec.data : {},
			current_date = Date.parseDate(win.curDate + ' ' + win.curTime, 'd.m.Y H:i:s'),
			disable_cancel;

		// Если статус записи/направления = «Обслужено», либо «Отклонено», либо «Отменено»
		var disable_cancel = ! our || rec.get('EvnStatus_SysNick').inlist(['Serviced', 'Canceled', 'Declined']);

		disable_cancel = disable_cancel || (
			getGlobalOptions().disallow_canceling_el_dir_for_elapsed_time == true &&
			Date.parseDate(rec.get('EvnDirection_RecDate'), 'd.m.Y H:i') <= current_date &&
			rec.get('EvnDirection_IsAuto') == 2
		);

		disable_cancel = disable_cancel || (
			getGlobalOptions().allow_canceling_without_el_dir_for_past_days != true &&
			Date.parseDate(rec.get('EvnDirection_RecDate'), 'd.m.Y H:i') <= current_date &&
			rec.get('EvnDirection_IsAuto') != 2
		);

		if ( ! win.ARMType || ! win.ARMType.inlist(['regpol', 'callcenter']))
		{
			disable_cancel = disable_cancel || (
				! Ext.isEmpty(rec.get('EvnDirection_RecDate')) &&
				Date.parseDate(rec.get('EvnDirection_RecDate'), 'd.m.Y H:i') >= Date.parseDate(getGlobalOptions().date, 'd.m.Y') &&
				(rec.get('ARMType_id') == 24 || (rec.get('pmUser_updId') >= 1000000 && rec.get('pmUser_updId') <= 5000000))
			);
		}

		if (win.ARMType == 'callcenter')
		{
			disable_cancel = disable_cancel || (
				! Ext.isEmpty(rec.get('EvnDirection_RecDate')) && Date.parseDate(rec.get('EvnDirection_RecDate'), 'd.m.Y H:i') < Date.parseDate(getGlobalOptions().date, 'd.m.Y')
			);
		} else
		{
			disable_cancel = disable_cancel || {
				incoming: this.getDisallowIncomingConditions(data.MedPersonal_did),
				outcoming: this.getDisallowOutcomingConditions(data.MedPersonal_id),
				both: this.getDisallowIncomingConditions(data.MedPersonal_did) && this.getDisallowOutcomingConditions(data.MedPersonal_id)
			}[this.getDirType(data)];

		}

		if (disable_cancel && IS_DEBUG)
		{
			console.log(data);
			console.log('Нельзя отменить');
		}

		return disable_cancel;
	},

	getDisallowIncomingConditions: function (MedPersonal_id)
	{
		return ! (
			getGlobalOptions().evn_direction_cancel_right_mo_where_adressed == 2 ||
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
			getGlobalOptions().evn_direction_cancel_right_mo_where_created == 2 ||
			'currMoDirCancel'.inlist(getGlobalOptions().groups.split('|')) ||
			(
				(MedPersonal_id || getGlobalOptions().CurMedPersonal_id) &&
				MedPersonal_id == getGlobalOptions().CurMedPersonal_id
			)
		);
	},

	getDirType: function (data)
	{
		var Lpu_id = getGlobalOptions().lpu_id;

		return Lpu_id == data.Lpu_sid ? (Lpu_id == data.Lpu_did ? 'both' : 'outcoming') : 'incoming';
	}
});