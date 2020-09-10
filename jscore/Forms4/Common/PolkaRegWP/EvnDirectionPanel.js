/**
 * Панель направлений
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
Ext6.define('common.PolkaRegWP.EvnDirectionPanel', {
	extend: 'swPanel',
	title: 'НАПРАВЛЕНИЯ И ЗАПИСИ',
	layout: 'border',
	clearParams: function() {
		var me = this;

		me.Person_id = null;
		me.Server_id = null;
		me.Person_Surname = '';
		me.Person_Firname = '';
		me.Person_Secname = '';
		me.Person_IsDead = null;

		me.EvnDirectionGrid.getStore().removeAll();
		me.onRecordSelect();
	},
	setParams: function(params) {
		var me = this;

		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.Person_Surname = params.Person_Surname;
		me.Person_Firname = params.Person_Firname;
		me.Person_Secname = params.Person_Secname;
		me.Person_IsDead = params.Person_IsDead;

		if (!me.ownerCt.collapsed && me.isVisible()) {
			me.load();
		}
	},
	load: function() {
		var me = this;
		if (!Ext6.isEmpty(me.Person_id)) {
			this.EvnDirectionGrid.getStore().load({
				params: {
					Person_id: me.Person_id,
					winType: 'reg',
					Lpu_id: getGlobalOptions().lpu_id
				}
			});
		}
	},
	onRecordSelect: function() {
		var me = this;

		me.EvnDirectionGrid.down('#action_direction').disable();
		me.EvnDirectionGrid.down('#action_delete').disable();
		me.EvnDirectionGrid.down('#action_recordfromqueue').disable();
		me.EvnDirectionGrid.down('#action_toqueue').disable();
		me.EvnDirectionGrid.down('#action_rewrite').disable();
		me.EvnDirectionGrid.down('#printTalonKvrachy').disable();
		me.EvnDirectionGrid.down('#printTalonKvrachyForThermalPrinter').disable();
		me.EvnDirectionGrid.down('#printTalonKvrachyCheck').disable();
		me.EvnDirectionGrid.down('#printTalonKvrachy').disable();
		me.EvnDirectionGrid.down('#printRouteList').disable();
		me.EvnDirectionGrid.down('#printPredRecord').disable();
		me.EvnDirectionGrid.down('#printDirection').disable();

		if (this.EvnDirectionGrid.getSelectionModel().hasSelection()) {
			var rec = this.EvnDirectionGrid.getSelectionModel().getSelection()[0];

			if (rec.get('EvnDirection_id')) {
				var our = ((me.ARMType == 'callcenter' && rec.get('DirType_id').inlist(['3', '16'])) || (me.ARMType != 'callcenter' && getGlobalOptions().lpu_id.inlist([rec.get('Lpu_did'), rec.get('Lpu_sid')])));

				me.EvnDirectionGrid.down('#action_direction').enable();
				me.EvnDirectionGrid.down('#action_delete').setDisabled(me.allowCancelDirectionOrRecord(rec, our));
				me.EvnDirectionGrid.down('#action_recordfromqueue').setDisabled(rec.get('EvnStatus_SysNick') != 'Queued' || !rec.get('EvnQueue_id') || !our);
				me.EvnDirectionGrid.down('#action_toqueue').setDisabled(rec.get('EvnStatus_SysNick') != 'DirZap' || !our);
				me.EvnDirectionGrid.down('#action_rewrite').setDisabled(rec.get('EvnStatus_SysNick') != 'DirZap' || !our);

				me.EvnDirectionGrid.down('#printTalonKvrachy').setDisabled(rec.get('accessType') == 0 || !rec.get('TimetableGraf_id') || !our);
				me.EvnDirectionGrid.down('#printTalonKvrachyForThermalPrinter').setDisabled(rec.get('accessType') == 0 || !rec.get('TimetableGraf_id') || !our);
				me.EvnDirectionGrid.down('#printTalonKvrachyCheck').setDisabled(rec.get('accessType') == 0 || !rec.get('TimetableGraf_id') || !our || !getRegionNick().inlist(['ekb']));
				me.EvnDirectionGrid.down('#printRouteList').setDisabled(rec.get('accessType') == 0 || rec.get('EvnQueue_id') || rec.get('TimetableStac_id'));
				me.EvnDirectionGrid.down('#printPredRecord').setDisabled(rec.get('accessType') == 0 || rec.get('EvnQueue_id') || rec.get('TimetableStac_id'));
				me.EvnDirectionGrid.down('#printDirection').setDisabled(rec.get('EvnDirection_IsAuto') == 2);
			}
		}
	},
	allowCancelDirectionOrRecord: function(rec, our, is_decline) {
		var win = this,
			data = rec ? rec.data : {},
			current_date = Date.parseDate(win.curDate + ' ' + win.curTime, 'd.m.Y H:i:s'),
			disable_cancel;

		// Если статус записи/направления = «Обслужено», либо «Отклонено», либо «Отменено»
		var disable_cancel = !our || rec.get('EvnStatus_SysNick').inlist(['Serviced', 'Canceled', 'Declined']);

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

		if (!win.ARMType || !win.ARMType.inlist(['regpol', 'callcenter'])) {
			disable_cancel = disable_cancel || (
				!Ext6.isEmpty(rec.get('EvnDirection_RecDate')) &&
				Date.parseDate(rec.get('EvnDirection_RecDate'), 'd.m.Y H:i') >= Date.parseDate(getGlobalOptions().date, 'd.m.Y') &&
				(rec.get('ARMType_id') == 24 || (rec.get('pmUser_updId') >= 1000000 && rec.get('pmUser_updId') <= 5000000))
			);
		}

		if (win.ARMType == 'callcenter') {
			disable_cancel = disable_cancel || (
				!Ext6.isEmpty(rec.get('EvnDirection_RecDate')) && Date.parseDate(rec.get('EvnDirection_RecDate'), 'd.m.Y H:i') < Date.parseDate(getGlobalOptions().date, 'd.m.Y')
			);
		} else {
			disable_cancel = disable_cancel || {
				incoming: this.getDisallowIncomingConditions(data.MedPersonal_did),
				outcoming: this.getDisallowOutcomingConditions(data.MedPersonal_id),
				both: this.getDisallowIncomingConditions(data.MedPersonal_did) && this.getDisallowOutcomingConditions(data.MedPersonal_id)
			}[this.getDirType(data)];

		}

		if (disable_cancel && IS_DEBUG) {
			console.log(data);
			console.log('Нельзя отменить');
		}

		return disable_cancel;
	},
	getDisallowIncomingConditions: function(MedPersonal_id) {
		return !(
			getGlobalOptions().evn_direction_cancel_right_mo_where_adressed == 2 ||
			'toCurrMoDirCancel'.inlist(getGlobalOptions().groups.split('|')) ||
			(
				(MedPersonal_id || getGlobalOptions().CurMedPersonal_id) &&
				MedPersonal_id == getGlobalOptions().CurMedPersonal_id
			)
		);
	},
	getDisallowOutcomingConditions: function(MedPersonal_id) {
		return !(
			getGlobalOptions().evn_direction_cancel_right_mo_where_created == 2 ||
			'currMoDirCancel'.inlist(getGlobalOptions().groups.split('|')) ||
			(
				(MedPersonal_id || getGlobalOptions().CurMedPersonal_id) &&
				MedPersonal_id == getGlobalOptions().CurMedPersonal_id
			)
		);
	},
	getDirType: function(data) {
		var Lpu_id = getGlobalOptions().lpu_id;

		return Lpu_id == data.Lpu_sid ? (Lpu_id == data.Lpu_did ? 'both' : 'outcoming') : 'incoming';
	},
	openEvnDirectionEditWindow: function(action) {
		if (!action || !action.inlist(['add', 'edit', 'view'])) {
			return false;
		}

		var me = this;
		var userMedStaffFact = this.ownerWin.userMedStaffFact;
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
							me.EvnDirectionGrid.getStore().reload({
								callback: function () {
									if (data.EvnDirection_id) {
										var index = me.EvnDirectionGrid.getStore().findBy( function(record) {
											if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
												return true;
											}
										});

										if (index > -1) {
											me.EvnDirectionGrid.getView().focusRow(index);
											me.EvnDirectionGrid.getSelectionModel().select(index);
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
						onClose: Ext6.emptyFn,
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
									me.EvnDirectionGrid.getStore().reload({
										callback: function () {
											if (data.EvnDirection_id) {
												var index = me.EvnDirectionGrid.getStore().findBy( function(record) {
													if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
														return true;
													}
												});

												if (index > -1) {
													me.EvnDirectionGrid.getView().focusRow(index);
													me.EvnDirectionGrid.getSelectionModel().select(index);
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
				var record = me.EvnDirectionGrid.getSelectionModel().getSelectedRecord();

				if (record == false) {
					return false;
				}

				var params = {
					action: 'view',
					formParams: {},
					Person_id: record.get('Person_id'),
					EvnDirection_id: record.get('EvnDirection_id')
				};

				getWnd('swEvnDirectionEditWindow').show(params);
				break;
		}
		return true;
	},
	cancelEvnDirection: function(cancelType) {
		if (!cancelType) {
			cancelType = 'cancel';
		}

		var me = this;
		var rec = me.EvnDirectionGrid.getSelectionModel().getSelectedRecord();
		log(['rec',rec]);
		return sw.Promed.Direction.cancel({
			cancelType: cancelType,
			ownerWindow: me.ownerWin,
			formType: 'reg',
			allowRedirect: true,
			userMedStaffFact: me.ownerWin.userMedStaffFact,
			EvnDirection_id: rec.get('EvnDirection_id') || null,
			DirType_Code: rec.get('DirType_id') || null,
			TimetableGraf_id: rec.get('TimetableGraf_id') || null,
			TimetableMedService_id: rec.get('TimetableMedService_id') || null,
			TimetableResource_id: rec.get('TimetableResource_id') || null,
			TimetableStac_id: rec.get('TimetableStac_id') || null,
			EvnQueue_id: rec.get('EvnQueue_id') || null,
			personData: me.persData || null,
			callback: function(cfg) {
				me.EvnDirectionGrid.getStore().reload();
			}
		});
	},
	printTalonKvrachy: function(printOnCheck) {
		var grid = this.EvnDirectionGrid;
		var rec = grid.getSelectionModel().getSelectedRecord();
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
		if (getRegionNick() != 'kz') return false;
		var grid = this.EvnDirectionGrid;
		var rec = grid.getSelectionModel().getSelectedRecord();
		var template = 'print_tickettodoctor.rptdesign';
		if (rec && rec.get('TimetableGraf_id')) {
			printBirt({
				'Report_FileName': template,
				'Report_Params': '&ParamTimeTableGraf_id=' + rec.get('TimetableGraf_id'),
				'Report_Format': 'pdf'
			});
		}
	},
	printRouteList: function() {
		var grid = this.EvnDirectionGrid;
		var rec = grid.getSelectionModel().getSelectedRecord();
		if (rec && rec.get('EvnDirection_id')) {
			printBirt({ // https://redmine.swan.perm.ru/issues/54910
				'Report_FileName': 'EvnQueue.rptdesign',
				'Report_Params': '&paramEvnDirection=' + rec.get('EvnDirection_id'),
				'Report_Format': 'pdf'
			});
		}
	},
	printPredRecord: function() {
		var grid = this.EvnDirectionGrid;
		var rec = grid.getSelectionModel().getSelectedRecord();
		if (rec && rec.get('EvnDirection_id')) {
			printBirt({ // https://redmine.swan.perm.ru/issues/54910
				'Report_FileName': 'EvnDirection.rptdesign',
				'Report_Params': '&paramEvnDirection=' + rec.get('EvnDirection_id'),
				'Report_Format': 'pdf'
			});
		}
	},
	printEvnDirection: function() {
		var grid = this.EvnDirectionGrid,
			rec = grid.getSelectionModel().getSelectedRecord();

		if (!rec) {
			return false;
		}

		sw.Promed.Direction.print({
			EvnDirection_id: rec.get('EvnDirection_id')
		});
	},
	printEvnDirectionFree: function() {
		var grid = this.EvnDirectionGrid,
			rec = grid.getSelectionModel().getSelectedRecord()

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
		if(!this.Person_id) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не выбран пациент.'));
			return;
		}
		var Lpu_id = getGlobalOptions().lpu_id;
		var Person_id = this.Person_id;
		sw.Promed.EvnPrescr.openPrintDoc('/?c=EvnPrescr&m=printLabDirections&Lpu_id='+Lpu_id + '&Person_id=' + Person_id);
	},
	recInQueue: function(mode) {
		var record = this.EvnDirectionGrid.getSelectionModel().getSelectedRecord();
		if (!record) {
			return false;
		}
		var me = this;
		var userMedStaffFact = this.ownerWin.userMedStaffFact;
		me.getLoadMask(langs('Пожалуйста подождите...')).show();
		Ext6.Ajax.request({
			url: '/?c=EvnDirection&m=getDataEvnDirection',
			callback: function(options, success, response)  {
				me.getLoadMask().hide();
				if (success) {
					var result  = Ext6.JSON.decode(response.responseText);
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
								me.EvnDirectionGrid.getStore().reload({
									callback: function () {
										if (data.EvnDirection_id) {
											var index = me.EvnDirectionGrid.getStore().findBy( function(record) {
												if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
													return true;
												}
											});

											if (index > -1) {
												me.EvnDirectionGrid.getView().focusRow(index);
												me.EvnDirectionGrid.getSelectionModel().select(index);
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
		var record = this.EvnDirectionGrid.getSelectionModel().getSelectedRecord();
		var grid = this.EvnDirectionGrid;
		if (record == false) {
			return false;
		}
		sw.Promed.Direction.rewrite({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			userMedStaffFact: this.ownerWin.userMedStaffFact,
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
								grid.getSelectionModel().select(index);
							}
						}
					}
				});
			}
		});
	},
	redirEvnDirection: function () {
		var record = this.EvnDirectionGrid.getSelectionModel().getSelectedRecord();
		var grid = this.EvnDirectionGrid;
		if (record == false) {
			return false;
		}
		sw.Promed.Direction.redirect({
			loadMask: this.getLoadMask(langs('Пожалуйста подождите...')),
			userMedStaffFact: this.ownerWin.userMedStaffFact,
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
								grid.getSelectionModel().select(index);
							}
						}
					}
				});
			}
		});
	},
	redirToQueue:function(){
		var record = this.EvnDirectionGrid.getSelectionModel().getSelectedRecord();
		if (!record) {
			return false;
		}
		var me = this;
		sw.Promed.Direction.returnToQueue({
			loadMask: me.getLoadMask(langs('Пожалуйста подождите...')),
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableGraf_id: record.get('TimetableGraf_id'),
			TimetableMedService_id: record.get('TimetableMedService_id'),
			TimetableResource_id: record.get('TimetableResource_id'),
			TimetableStac_id: record.get('TimetableStac_id'),
			EvnQueue_id: record.get('EvnQueue_id'),
			callback: function (data) {
				// обновляем грид и позиционируемся на добавленное направление
				me.EvnDirectionGrid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = me.EvnDirectionGrid.getStore().findBy( function(record) {
								if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
									return true;
								}
							});

							if (index > -1) {
								me.EvnDirectionGrid.getView().focusRow(index);
								me.EvnDirectionGrid.getSelectionModel().select(index);
							}
						}
					}
				});
			}
		});
	},
	initComponent: function() {
		var me = this;

		this.EvnDirectionGrid = Ext6.create('Ext6.grid.Panel', {
			itemId: 'EvnDirectionGrid',
			border: false,
			cls: 'grid-common',
			region: 'center',
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
					text: 'Открыть',
					itemId: 'action_direction',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_direction',
					handler: function() {
						me.openEvnDirectionEditWindow('edit');
					}
				}, {
					text: 'Отменить',
					itemId: 'action_delete',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_delete',
					handler: function() {
						me.cancelEvnDirection('cancel');
					}
				}, {
					text: 'Печать',
					itemId: 'action_print',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					menu: Ext6.create('Ext6.menu.Menu', {
						userCls: 'menuWithoutIcons',
						items: [{
							text: 'Печать списка',
							handler: function() {
								Ext6.ux.GridPrinter.print(me.EvnDirectionGrid);
							}
						}, {
							text: 'Талон на прием к врачу',
							itemId: 'printTalonKvrachy',
							handler: function() {
								me.printTalonKvrachy();
							}
						}, {
							text: 'Талон на прием к врачу для термопринтера',
							hidden: getRegionNick() != 'kz',
							itemId: 'printTalonKvrachyForThermalPrinter',
							handler: function() {
								me.printTalonKvrachyForThermalPrinter();
							}
						}, {
							text: 'Талон на прием к врачу (Чековая бумага)',
							hidden: getRegionNick() != 'ekb',
							itemId: 'printTalonKvrachyCheck',
							handler: function() {
								me.printTalonKvrachy(true);
							}
						}, {
							text: 'Печать маршрутного листа',
							itemId: 'printRouteList',
							handler: function() {
								me.printRouteList();
							}
						}, {
							text: 'Печать листа предварительной записи',
							itemId: 'printPredRecord',
							handler: function() {
								me.printPredRecord();
							}
						}, {
							text: 'Печать направления',
							itemId: 'printDirection',
							handler: function() {
								me.printEvnDirection();
							}
						}, {
							text: 'Печать шаблона документа',
							itemId: 'printEvnDirectionFree',
							handler: function() {
								me.printEvnDirectionFree();
							}
						}, {
							text: 'Печать единого направления на лабораторные исследования',
							itemId: 'printLabDirections',
							hidden: getRegionNick() != 'ufa',
							handler: function() {
								me.printLabDirections();
							}
						}]
					})
				}, {
					text: 'Записать из очереди',
					itemId: 'action_recordfromqueue',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_recordfromqueue',
					handler: function() {
						me.recInQueue('rec');
					}
				}, {
					text: 'В очередь',
					itemId: 'action_toqueue',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_toqueue',
					handler: function() {
						me.redirToQueue();
					}
				}, {
					text: 'Перезаписать',
					itemId: 'action_rewrite',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_rewrite',
					handler: function() {
						me.rewriteEvnDirection();
					}
				}]
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						me.onRecordSelect();
					}
				}
			},
			columns: [
				{text: '№брони/талона ЭО', tdCls: 'padLeft', width: 150, dataIndex: 'EvnDirection_TalonCode'},
				{
					text: 'Запись',
					width: 140,
					dataIndex: 'EvnDirection_RecDate',
					renderer: Ext6.util.Format.dateRenderer('d.m.Y H:i')
				},
				{
					text: 'Дата напр.',
					width: 100,
					dataIndex: 'EvnDirection_setDate',
					renderer: Ext6.util.Format.dateRenderer('d.m.Y')
				},
				{text: 'Тип', width: 150, dataIndex: 'DirType_Name'},
				{text: 'Статус', width: 150, dataIndex: 'EvnStatus_Name'},
				{text: 'Номер', width: 150, dataIndex: 'EvnDirection_Num'},
				{text: 'Кем направлен', width: 150, flex: 1, dataIndex: 'EvnDirection_From'},
				{text: 'Куда направлен', width: 150, flex: 1, dataIndex: 'EvnDirection_To'}
			],
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{name: 'EvnDirection_id', type: 'int'},
					{name: 'EvnDirection_IsAuto', type: 'int'},
					{name: 'EvnDirection_TalonCode'},
					{name: 'EvnDirection_RecDate', type: 'date', dateFormat: 'd.m.Y H:i'},
					{name: 'accessType', type: 'int'},
					{name: 'EvnXmlDir_id', type: 'int'},
					{name: 'EvnXmlDirType_id', type: 'int'},
					{name: 'TimetableGraf_id', type: 'int'},
					{name: 'TimetableMedService_id', type: 'int'},
					{name: 'TimetableResource_id', type: 'int'},
					{name: 'TimetableStac_id', type: 'int'},
					{name: 'EvnQueue_id', type: 'int'},
					{name: 'EvnQueue_Days', type: 'int'},
					{name: 'EvnDirection_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'DirType_Code'},
					{name: 'DirType_Name'},
					{name: 'DirType_id', type: 'int'},
					{name: 'EvnDirection_Num'},
					{name: 'EvnDirection_From'},
					{name: 'EvnDirection_To'},
					{name: 'Org_sid', type: 'int'},
					{name: 'Lpu_sid', type: 'int'},
					{name: 'Lpu_did', type: 'int'},
					{name: 'Lpu_did', type: 'int'},
					{name: 'MedPersonal_id', type: 'int'},
					{name: 'MedPersonal_did', type: 'int'},
					{name: 'EvnStatus_SysNick'},
					{name: 'LpuSection_sid', type: 'int'},
					{name: 'pmUser_Fio'},
					{name: 'LpuSectionProfile_Name'},
					{name: 'UslugaComplex_Name'},
					{name: 'Person_id', type: 'int'},
					{name: 'IsConfirmed', type: 'int'},
					{name: 'ARMType_id', type: 'int'},
					{name: 'pmUser_updId', type: 'int'},
					{name: 'Person_Fio', type: 'string'},
					{name: 'Person_BirthDay', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Address_Address', type: 'string'},
					{name: 'Person_Phone', type: 'string'},
					{name: 'Diag_Name', type: 'string'},
					{name: 'pmUser_Name', type: 'string'},
					{name: 'EvnDirection_insDT', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Referral_id', type: 'int'},
					{name: 'Referral_Code', type: 'string'},
					{name: 'EvnDirectionLink_insDT', type: 'date', dateFormat: 'd.m.Y'}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnDirection&m=loadBaseJournal',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					},
					pageParam: "",
					startParam: "",
					limitParam: "",
				},
				sorters: {
					property: 'EvnDirection_Num',
					direction: 'ASC'
				},
				listeners: {
					load: function() {
						me.onRecordSelect();
					}
				}
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnDirectionGrid
			]
		});

		this.callParent(arguments);
	}
});