/**
 * swWorkPlacePolkaRegWindow - АРМ регистратора поликлиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.PolkaRegWP.swWorkPlacePolkaRegWindow', {
	requires: [
		'common.PolkaRegWP.EvnDirectionPanel',
		'common.PolkaRegWP.PersonAmbulatCardPanel'
	],
	noCloseOnTaskBar: true, // без кнопки закрытия на таксбаре
	extend: 'base.BaseForm',
	alias: 'widget.swWorkPlacePolkaRegWindow',
	autoShow: false,
	maximized: true,
	width: 1000,
	refId: 'polkawp',
	findWindow: false,
	closable: false,
	frame: false,
	cls: 'arm-window-new PolkaWP',
	title: 'АРМ регистратора поликлиники',
	header: true,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	onRecordSelect: function() {
		var me = this;

		var cnt = this.mainGrid.getSelectionModel().getSelection().length;
		if (cnt > 0) {
			me.selectedLabel.setText('Выбран: ' + cnt);
		} else {
			me.selectedLabel.setText('');
		}

		me.personLabel.setHtml('');
		me.mainGrid.down('#action_edit').disable();
		me.mainGrid.down('#action_doubles').disable();
		me.mainGrid.down('#action_personcard').disable();
		me.mainGrid.down('#action_history').disable();

		me.EvnDirectionPanel.clearParams();
		me.PersonAmbulatCardPanel.clearParams();

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (record.get('Person_id')) {
				me.EvnDirectionPanel.setParams({
					Person_id: record.get('Person_id'),
					Server_id: record.get('Server_id'),
					Person_Surname: record.get('Person_Surname'),
					Person_Firname: record.get('Person_Firname'),
					Person_Secname: record.get('Person_Secname'),
					Person_IsDead: record.get('Person_IsDead')
				});
				me.PersonAmbulatCardPanel.setParams({
					Person_id: record.get('Person_id'),
					Server_id: record.get('Server_id'),
					Person_Surname: record.get('Person_Surname'),
					Person_Firname: record.get('Person_Firname'),
					Person_Secname: record.get('Person_Secname'),
					Person_IsDead: record.get('Person_IsDead')
				});

				var sex = 'man';
				if (record.get('Sex_id') == 2) {
					sex = 'woman';
				}
				me.personLabel.setHtml('<img src="/img/icons/2017/' + sex + '.png" width="16" height="16" /> ' + record.get('Person_FIO') + ' ' + me.getPersonBirthString(record.get('Person_Birthday')));
				me.mainGrid.down('#action_edit').enable();
				me.mainGrid.down('#action_doubles').enable();
				me.mainGrid.down('#action_personcard').enable();
				me.mainGrid.down('#action_history').enable();
			}
		}
	},
	getGrid: function ()
	{
		return this.mainGrid;
	},
	getSelectedRecord: function() {
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];
			if (record && record.get('Person_id')) {
				return record;
			}
		}
		return false;
	},
	_printMedCard: function(personCard, personId, personAmbulatCard_id, personAmbulatCard_num) {// #137782
		if (!personCard) personCard = 0;
		if (!personId) personId = 0;
		if (!personAmbulatCard_id) personAmbulatCard_id = 0;
		if (!personAmbulatCard_num) personAmbulatCard_num = 0;
		var lpu = getLpuIdForPrint();
		if (getRegionNick().inlist(['kz'])) {
			var params = {
				PersonCard_id: personCard,
				Person_id: personId
			};
			if (personAmbulatCard_num) {
				params.PersonAmbulatCard_Num = personAmbulatCard_num;
			}
			Ext6.Ajax.request({
				callback: function(options, success, response) {
					if (success) {
						var response_obj = Ext6.JSON.decode(response.responseText);
						openNewWindow(response_obj.result);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При получении данных для печати мед. карты произошла ошибка');
					}
				}.createDelegate(this),
				params: params,
				url: '/?c=PersonCard&m=printMedCard'
			});
		}
		else if (getRegionNick() == 'ufa') {
			//printMedCard4Ufa(gridSelected.get('PersonCard_id'));// функцию не трогаю, может вызываться откуда-то ещё
			printBirt({
				'Report_FileName': 'f025u_oborot.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
			printBirt({
				'Report_FileName': 'f025u.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
		}
		else {
			printBirt({
				'Report_FileName': 'pan_PersonCard_f025u.rptdesign',
				'Report_Params': '&paramPerson=' + personId + '&paramPersonAmbulatCard=' + personAmbulatCard_id + '&paramPersonCard=' + personCard + '&paramLpu=' + lpu,
				'Report_Format': 'pdf'
			});
		}
	},
	printPregnancyBlank: function() {
		printBirt({
			'Report_FileName': 'han_ParturientCard_f111_u.rptdesign',
			'Report_Params': '&paramPersonRegister_id=0',
			'Report_Format': 'pdf'
		});
	},
	printFreeTemplate: function() {
		var personData = this.getParamsIfHasPersonData();

		if(!personData){
			return false;
		}

		var params = {
			Person_id: personData.Person_id
		};

		getWnd('swPrintTemplateSelectWindow').show(params);
	},
	printDestinationRouteCard: function(Evn_id){
		printBirt({
			'Report_FileName': 'DestinationRouteCard.rptdesign',
			'Report_Params': '&paramEvnPL=' + Evn_id,
			//'Report_Params': '&paramEvnPL=' + this.getView().ownerPanel.EvnPL_id,
			'Report_Format': 'pdf'
		});
	},
	printEvnPLPrescr: function(Evn_id) {
		if(Evn_id)
			this.printDestinationRouteCard(Evn_id);
		else{
			var personData = this.getParamsIfHasPersonData();
			if(!personData){
				return false;
			}
			var params = {
				Person_id: personData.Person_id
			};
			getWnd('swSelectDestinationRouteListWindow').show(params);
		}
	},
	showDoublesUnionBar: function() {
		this.customBar.show();
		this.mainGrid.getSelectionModel().column.setWidth(45);
		this.mainGrid.getSelectionModel().column.show();
		this.mainGrid.queryById('action_doubles').addCls('x6-btn-pressed');
		this.mainGrid.setColumnHidden('IsMainRec', false);
	},
	show: function() {
		this.callParent(arguments);
		var me = this;
		this.personDoublesCache = sw.Promed.personDoublesCache;

		if ( me.personDoublesCache.cacheEnabled ) {
			me.showDoublesUnionBar();
		} else {
			me.cancelDoubles();
		}
		

		if (!arguments[0] || !arguments[0].MedService_id) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		} else {
			this.MedService_id = arguments[0].MedService_id;
			this.userMedStaffFact = arguments[0];
		}

		sw.Promed.MedStaffFactByUser.setMenuTitle(me, arguments[0]);

		me.doReset();
	},
	doSearch: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var me = this;

		if ( this.filterPanel.isEmpty() ) {
			this.doReset(); // ничего не задали - ничего не нашли
			return false;
		}

		var base_form = this.filterPanel.getForm();
		var extraParams = base_form.getValues();

		if (
			Ext6.isEmpty(extraParams.Person_Surname) && Ext6.isEmpty(extraParams.Person_Firname) && Ext6.isEmpty(extraParams.Person_Secname)
			&& (!Ext6.isEmpty(extraParams.Address_Street) || !Ext6.isEmpty(extraParams.Address_House))
		) {
			sw.swMsg.alert('Ошибка', 'Для поиска по адресу требуется заполнить хотя бы одно поле из ФИО');
			return false;
		}

		extraParams.allowOverLimit = 1;
		extraParams.Org_id = getGlobalOptions().org_id;

		if (extraParams.showAll) {
			extraParams.showAll = 2;
		} else {
			extraParams.showAll = 1;
		}

		var params = {
			start: 0,
			limit: 100
		}

		params.Double_ids = Ext6.util.JSON.encode(me.personDoublesCache.getIdsOtherModels('reg'));

		extraParams.dontShowUnknowns = 1;// #158923 не показывать неизвестных

		me.mainGrid.getStore().proxy.extraParams = extraParams;

		me.mainGrid.getStore().load({
			params: params,
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
				var i, rowIndex;
				var selectedRecords = [];
				var gridStore = me.mainGrid.getStore();


				if ( me.personDoublesCache.cacheEnabled ) {
					me.showDoublesUnionBar();
					var Person_ids = me.personDoublesCache.getPersonIds().reverse();
					for ( var index = 0; Person_ids.length > index; index++ ) {
						i = gridStore.findBy(function(rec) {
							return rec.get('Person_id') == Person_ids[index];
						});
						if (i == -1) {
							gridStore.insert(0, me.personDoublesCache.getRecord(Person_ids[index]));

							rowIndex = 0;
							selectedRecords.push(gridStore.getAt(rowIndex));
						} else {
							row = gridStore.getAt(i);
							gridStore.removeAt(i);
							gridStore.insert(0, row);
							if (me.personDoublesCache.getRecord(Person_ids[index]).get('IsMainRec')) {
								row.set('IsMainRec', true);
								row.commit();
							}
							selectedRecords.push(row);
						}
					}
					me.mainGrid.getSelectionModel().select(selectedRecords);
				} else {
					me.cancelDoubles();
				}
			}
		});
	},
	doReset: function () {
		var base_form = this.filterPanel.getForm();
		base_form.reset();
		this.mainGrid.getStore().removeAll();
		this.onRecordSelect();
		base_form.findField('Person_Surname').focus(true, 100);
	},
	getParamsIfHasPersonData: function() {
		var selected_record = this.getSelectedRecord();

		// Собираем информацию о человеке в случае, если в гриде есть поля по человеку
		if (selected_record && selected_record.get('Person_id')) {
			var params = new Object();
			params.Person_IsDead = selected_record.get('Person_IsDead');
			params.Person_id = selected_record.get('Person_id');
			params.Server_id = selected_record.get('Server_id');
			params.PersonEvn_id = selected_record.get('PersonEvn_id');
			params.Person_Birthday = selected_record.get('Person_Birthday');
			params.Person_Surname = selected_record.get('Person_Surname');
			params.Person_Firname = selected_record.get('Person_Firname');
			params.Person_Secname = selected_record.get('Person_Secname');
			return params;
		}
		return false;
	},
	openEvnDirectionEditWindow: function(action, mode) {
		if (!action || !action.inlist(['add', 'edit', 'view'])) {
			return false;
		}

		var wnd = this;
		var userMedStaffFact = this.userMedStaffFact;
		switch (action) {
			case 'add':
				var persData = wnd.getParamsIfHasPersonData();
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
					, EvnDirection_IsReceive: (mode && mode == 3) ? 2 : 1
					, fromBj: true
				};
				if (persData) {
					var isDead = (persData && persData.Person_IsDead && persData.Person_IsDead == 'true');

					if (isDead) {
						sw.swMsg.alert(langs('Ошибка'), langs('Запись невозможна, т.к. у пациента стоит дата смерти.'));
						return false;
					}

					var params = {
						userMedStaffFact: userMedStaffFact,
						isDead: isDead,
						type: 'LpuReg',
						personData: persData,
						directionData: directionData,
						onDirection: function(data) {
							// обновляем грид и позиционируемся на добавленное направление
							wnd.mainGrid.getStore().reload({
								callback: function() {
									if (data.EvnDirection_id) {
										var index = wnd.mainGrid.getStore().findBy(function(record) {
											if (record.get('EvnDirection_id') == data.EvnDirection_id) {
												return true;
											}
										});

										if (index > -1) {
											wnd.mainGrid.getView().focusRow(index);
											wnd.mainGrid.getSelectionModel().select(index);
										}
									}
								}
							});
						}
					};

					checkPersonPhoneVerification({
						Person_id: persData.Person_id,
						MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
						callback: function() {
							getWnd('swDirectionMasterWindow').show(params)
						}
					});
				} else {
					// открываем окно поиска чела, на него выписываем направление
					getWnd('swPersonSearchWindowExt6').show({
						onClose: Ext6.emptyFn,
						onSelect: function(person_data) {
							getWnd('swPersonSearchWindowExt6').hide();

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
								onDirection: function(data) {
									// обновляем грид и позиционируемся на добавленное направление
									wnd.mainGrid.getStore().reload({
										callback: function() {
											if (data.EvnDirection_id) {
												var index = wnd.mainGrid.getStore().findBy(function(record) {
													if (record.get('EvnDirection_id') == data.EvnDirection_id) {
														return true;
													}
												});

												if (index > -1) {
													wnd.mainGrid.getView().focusRow(index);
													wnd.mainGrid.getSelectionModel().select(index);
												}
											}
										}
									});
								}
							};

							checkPersonPhoneVerification({
								Person_id: person_data.Person_id,
								MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
								callback: function() {
									getWnd('swDirectionMasterWindow').show(params)
								}
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

				getWnd('swEvnDirectionEditWindow').show(params);
				break;
		}
		return true;
	},
	recordUnScheduled: function() {
		var me = this;
		var personData = this.getParamsIfHasPersonData();
		if (personData && personData.Person_IsDead == "true") {
			log(personData);
			Ext6.Msg.alert('Ошибка', 'Запись на незапланированный прием невозможна в связи со смертью пациента!');
			return false;
		}
		//log(personData);return false;
		if (!personData || !personData.Person_id) {
			personData = null;
		}
		getWnd('swMedStaffFactSelectWindow').show({
			medStaffFactGlobalStoreFilters: {
				onDate: getGlobalOptions().date,
				isPolkaAndStom: true
			},
			onSelect: function(selectedParams) {
				if (!selectedParams || !selectedParams.medStaffFactRecord || !selectedParams.lpuSectionRecord) {
					sw.swMsg.alert('Ошибка', 'Ошибка получения параметров выбранного врача');
					return false;
				}
				// осуществлять незапланированную запись к выбранному врачу с текущим временем
				var directionData = {
					LpuUnitType_SysNick: 'polka'//selectedParams.lpuSectionRecord.get('LpuSectionProfile_Code')
					, EvnQueue_id: null
					, QueueFailCause_id: null
					, Lpu_did: selectedParams.medStaffFactRecord.get('Lpu_id') // ЛПУ куда направляем
					, LpuUnit_did: selectedParams.lpuSectionRecord.get('LpuUnit_id')
					, LpuSection_did: selectedParams.medStaffFactRecord.get('LpuSection_id')
					, EvnUsluga_id: null
					, LpuSection_id: null
					, EvnDirection_pid: null
					, EvnPrescr_id: null
					, PrescriptionType_Code: null
					, DirType_id: null
					, LpuSectionProfile_id: selectedParams.lpuSectionRecord.get('LpuSectionProfile_id')
					, Diag_id: null
					, ARMType_id: this.userMedStaffFact.ARMType_id
					, MedStaffFact_id: selectedParams.medStaffFactRecord.get('MedStaffFact_id')
					, MedPersonal_id: this.userMedStaffFact.MedPersonal_id
					, MedPersonal_did: selectedParams.medStaffFactRecord.get('MedPersonal_id')
					, time: getGlobalOptions().date + ' 00:00'
				};
				var params = {
					Timetable_id: 0
					, direction: directionData
					, person: personData
					, loadMask: true
					, win: me
					, windowId: 'swWorkPlacePolkaRegWindow'
					, callback: Ext6.emptyFn
					, onSaveRecord: function(conf) {
						if (conf && conf.Timetable_id) {
							// После успешного осуществления записи обновлять грид Записи пациента
							if (personData) {
								this.EvnDirectionPanel.load();
							}
							/*
							убрал автоматическое открытие печатной формы талона #17462
							var pr_params = new Object();
							pr_params.type = 'EvnPL';
							pr_params.personId = personData.Person_id;
							pr_params.TimetableGraf_id = conf.Timetable_id;
							if ( getGlobalOptions().region ) {
								switch ( getGlobalOptions().region.nick ) {
									case 'ufa':
										getWnd('swEvnPLBlankSettingsWindow').show(pr_params);
									break;

									default:
										printEvnPLBlank(pr_params);
									break;
								}
							}
							else {
								printEvnPLBlank(pr_params);
							}
							*/
						}
					}.createDelegate(this)
					, onHide: Ext6.emptyFn
					, needDirection: null
					, fromEmk: false
					, mode: 'nosave'
					, Unscheduled: true
					, date: getGlobalOptions().date
				};
				sw.Promed.Direction.recordPerson(params);
			}.createDelegate(this),
			onHide: function() {
				//
			}.createDelegate(this)
		});
	},
	openPersonCardHistoryWindow: function() {
		var me = this;
		var params = this.getParamsIfHasPersonData();
		if (params && params.Person_id) {
			params.onHide = function(){
				me.mainGrid.getStore().reload();
			};
			getWnd('swPersonCardHistoryWindow').show(params);
		}
	},
	setDoubles: function() {
		this.customBar.show();
		this.mainGrid.getSelectionModel().column.setWidth(45);
		this.mainGrid.getSelectionModel().column.show();
		this.mainGrid.queryById('action_doubles').addCls('x6-btn-pressed');
		this.mainGrid.setColumnHidden('IsMainRec', false);

		this.personDoublesCache.setCacheEnable();
		var record = this.getSelectedRecord();
		if (record) {
			this.setIsMainRec(record);
			this.personDoublesCache.addRecord(record, 'reg');
		}
	},
	setIsMainRec: function(record) {
		this.mainGrid.getStore().each(function(rec) {
			if (rec != record && rec.get('IsMainRec') == true) {
				rec.set('IsMainRec', false);
				rec.commit();
			}
		});

		record.set('IsMainRec', true);
		record.commit();
		this.personDoublesCache.setMainRecord(record.get('Person_id'));
	},
	cancelDoubles: function() {
		this.customBar.hide();
		this.mainGrid.getSelectionModel().column.hide();
		this.mainGrid.queryById('action_doubles').removeCls('x6-btn-pressed');
		this.mainGrid.setColumnHidden('IsMainRec', true);
	},
	doPersonUnion: function () {
		var me = this;
		var mainGrid = this.mainGrid;
		var hasMainRec = false;
		var records = [];
		if (mainGrid.getSelectionModel().hasSelection()) {
			mainGrid.getSelectionModel().getSelection().forEach(function(record) {
				if (record.get('Person_id')) {
					if (record.get('IsMainRec')) {
						hasMainRec = true;
					}

					records.push({
						Person_id: record.get('Person_id'),
						IsMainRec: record.get('IsMainRec') ? 1 : 0
					});
				}
			});
		}
		if (records.length < 2) {
			Ext6.Msg.alert(langs('Внимание'),langs('Для объединения должны быть хотя бы 2 записи!'));
			return false;
		}
		if (!hasMainRec){
			Ext6.Msg.alert(langs('Внимание'),langs('Должна быть выбрана главная запись для объединения!'));
			return false;
		}

		me.mask('Пожалуйста, подождите, идет сохранение данных...');
		Ext6.Ajax.request({
			url: C_PERSON_UNION,
			success: function(result){
				me.unmask();
				if ( result.responseText.length > 0 ) {
					var resp_obj = Ext6.JSON.decode(result.responseText);
					if (resp_obj.success == true) {
						me.cancelDoubles();
						me.personDoublesCache.resetCache();
						mainGrid.getStore().reload();
						if (resp_obj.Info_Msg) {
							sw4.showInfoMsg({
								type: 'info',
								text: langs('Выбранные записи успешно отправлены на модерацию') + '<br />' + resp_obj.Info_Msg
							});
							mainGrid.queryById('action_doubles').removeCls('x6-btn-pressed');
						} else if (resp_obj.Success_Msg) {
							sw4.showInfoMsg({
								type: 'info',
								text: langs('Выбранные записи успешно отправлены на модерацию')
							});
							mainGrid.queryById('action_doubles').removeCls('x6-btn-pressed');
						}
					}
				}
			},
			params: {
				'Records': Ext6.JSON.encode(records)
			},
			failure: function(result){
				me.unmask();
			},
			method: 'POST',
			timeout: 120000
		});
	},
	openPersonEditWindow: function(action) {
		var me = this;
		if (action == 'edit') {
			var params = this.getParamsIfHasPersonData();
			if (params && params.Person_id) {
				params.PersonEvn_id = null;
				params.onClose = function() {
					me.mainGrid.getStore().reload();
				};
				getWnd('swPersonEditWindow').show(params);
			}
		} else {
			var filterForm = this.filterPanel.getForm();

			getWnd('swPersonEditWindow').show({
				action: 'add',
				fields: {
					Person_SurName: filterForm.findField('Person_Surname').getValue(),
					Person_FirName: filterForm.findField('Person_Firname').getValue(),
					Person_SecName: filterForm.findField('Person_Secname').getValue(),
					Person_BirthDay: filterForm.findField('Person_Birthday').getValue()
				},
				callback: function (saved_person){
					// обновить грид и выбрать добавленного человека
					var params = {
						Person_id: saved_person.Person_id,
						limit: 50,
						start: 0
					};

					me.mainGrid.getStore().removeAll();
					me.mainGrid.getStore().load({
						params: params,
						callback: function() {
							me.mainGrid.getStore().each(function(record) {
								if (record.get('Person_id') == saved_person.Person_id) {
									var index = me.mainGrid.getStore().indexOf(record);
									me.mainGrid.getView().focusRow(index);
									me.mainGrid.getSelectionModel().select(index);
									return;
								}
							});
						}
					});
				}
			});
		}
	},
	openPersonCureHistoryWindow: function() {
		var me = this;
		var params = this.getParamsIfHasPersonData();
		if (params && params.Person_id) {
			getWnd('swPersonCureHistoryWindow').show(params);
		}
	},
	getPersonBirthString: function(date) {
		if (!date) {
			return '';
		}

		var s = Ext6.util.Format.date(date, 'd.m.Y');
		s = s + ' (';
		var years = swGetPersonAge(date);
		if (years > 0) {
			s = s + swGetPersonAge(date);
		}
		if (years <= 3) { // до 3 лет вместе с месяцами
			if (years > 0) {
				s = s + 'г ';
			}
			s = s + swGetPersonAgeMonth(date) + 'м';
		}
		s = s + ')';

		return s;
	},
	checkDestRouteList: function(cbFn){
		var personData = this.getParamsIfHasPersonData();
		if(!personData){
			cbFn(true);
			return false;
		}
		var params = {
			Person_id: personData.Person_id,
			top: 2
		};
		Ext6.Ajax.request({
			url: '/?c=EvnPrescr&m=loadEvnVizitWithPrescrList',
			success: function(response) {
				var response_obj = Ext6.JSON.decode(response.responseText);
				if(response_obj && !Ext6.isEmpty(response_obj.countRouteList)) {
					switch(response_obj.countRouteList){
						case 0:
							cbFn(true);
							break;
						case 1:
							cbFn(false,response_obj.Evn_id);
							break;
						case 2:
							cbFn(false);
							break;
						default:
							cbFn(true);
					}
				} else {
					cbFn(true);
					return false;
				}
			}.createDelegate(this),
			failure: function(){
				cbFn(true);
			},
			params: params
		});
	},
	initComponent: function() {
		var me = this;

		me.selectedLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			cls: 'person-double-text-select',
			text: ''
		});

		me.customBar = Ext6.create('Ext6.toolbar.Toolbar', {
			padding: '4 20 3 20',
			xtype: 'toolbar',
			dock: 'bottom',
			style: {
				'backgroundColor': '#f5f5f5'
			},
			ui: 'footer',
			items: [{
				handler: function () {
					me.doPersonUnion();
				},
				cls: 'button-primary',
				text: 'Объединить'
			}, {
				handler: function () {
					me.cancelDoubles();
					me.personDoublesCache.resetCache();
				},
				cls: 'button-secondary',
				text: 'Отмена',
				margin: '0 0 0 3'
			}, {
				xtype: 'checkbox',
				boxLabel: 'Перенести случаи',
				fieldLabel: 'Перенести случаи',
				margin: '0 0 0 20',
				hideLabel: true,
				name: 'transferEvn'
			}, '->', me.selectedLabel ]
		});

		me.mainGrid = Ext6.create('Ext6.grid.Panel', {
			flex: 600,
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			plugins: {
				rowexpander: {
					width: 100,
					rowBodyTpl: new Ext6.XTemplate(
						'<table class="person-info-table">',
						'<tr>',
						'<td class="label">МО прикрепл:</td>',
						'<td>{AttachLpu_Name}</td>',
						'<td class="label">Усл. прикрепл.:</td>',
						'<td>{PersonCard_IsAttachCondit:this.formatCheckbox}</td>',
						'<td class="label">Зарегистрирован:</td>',
						'<td>{Person_UAddress}</td>',
						'</tr>',
						'<tr>',
						'<td class="label">Дата прикрепл.:</td>',
						'<td>{PersonCard_begDate}</td>',
						'<td class="label">Согласие:</td>',
						'<td>{PersonLpuInfo_IsAgree:this.formatCheckbox}</td>',
						'<td class="label">Проживает:</td>',
						'<td>{Person_PAddress}</td>',
						'</tr>',
						'<tr>',
						'<td class="label">Основной участок:</td>',
						'<td>{LpuRegion_Name}</td>',
						'<td class="label">СМС/e-mail уведомл.:</td>',
						'<td>{NewslatterAccept}</td>',
						'<td class="label">Телефон:</td>',
						'<td>{Person_Phone}</td>',
						'</tr>',
						'<tr>',
						'<td class="label">ФАП участок:</td>',
						'<td>{LpuRegion_FapName}</td>',
						'<td class="label">7 нозологий:</td>',
						'<td>{Person_Is7Noz:this.formatCheckbox}</td>',
						'<td class="label"></td>',
						'<td></td>',
						'</tr>',
						'</table>',
						{
							formatCheckbox: function (v) {
								if (v === true || v === 'true' || v === 'V') {
									return 'Да';
								} else {
									return 'Нет';
								}
							}
						}
					)
				}
			},
			dockedItems: [{
				xtype: 'toolbar',
				defaults: {
					margin: '0 4 0 0',
					padding: '4 10'
				},
				dock: 'top',
				height: 40,
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					margin: '0 0 0 6',
					text: 'Записать',
					itemId: 'action_record',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_record',
					handler: function() {
						me.openEvnDirectionEditWindow('add', 1);
					}
				}, {
					text: 'Записать с направлением',
					itemId: 'action_direction',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_direction',
					handler: function() {
						me.openEvnDirectionEditWindow('add', 2);
					}
				}, {
					text: 'Записать с внешним направлением',
					itemId: 'action_incomingdir',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_incomingdir',
					handler: function() {
						me.openEvnDirectionEditWindow('add', 3);
					}
				}, {
					text: 'Экстренный прием',
					itemId: 'action_emergencydir',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_emergencydir',
					handler: function() {
						me.recordUnScheduled();
					}
				}, {
					text: 'Прикрепления',
					itemId: 'action_personcard',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_personcard',
					handler: function() {
						me.openPersonCardHistoryWindow();
					}
				}, {
					text: 'Вызвать врача на дом',
					itemId: 'action_homevisit',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_homevisit',
					handler: function() {
						getWnd('swHomeVisitListWindow').show({type:'regpol'});
					}
				}, {
					text: 'Печать',
					itemId: 'action_print',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					listeners: {
						menushow: function (btn, menu, eOpts) {
							menu.mask('Проверка маршрутных карт');
							var cbFn = function(disable,Evn_id){
								menu.unmask();
								var menuItem = menu.down('#print_EvnPLPrescr');
								if(menuItem){
									delete menuItem.Evn_id;
									menuItem.setDisabled(disable);
									if(!Ext6.isEmpty(Evn_id)){
										menuItem.Evn_id = Evn_id;
									}
								}
							};
							me.checkDestRouteList(cbFn);
						}
					},
					menu: new Ext6.menu.Menu({
						userCls: 'menuWithoutIcons',
						items: [{
							text: 'Печать списка',
							handler: function() {
								Ext6.ux.GridPrinter.print(me.mainGrid);
							}
						}, {
							text: 'Список записанных по всем врачам',
							handler: function() {
								getWnd('swPrintLpuUnitScheduleWindow').show();
							}
						}, {
							disabled: false,
							handler: function() {
								var record = me.getSelectedRecord();
								if (record) {
									var params = new Object();
									params.type = 'EvnPL';
									params.personId = record.get('Person_id');

									printEvnPLBlank(params);
								}
							}.createDelegate(this),
							name: 'print_evnpl_blank',
							text: 'Печать бланка ТАП',
							tooltip: 'Печать бланка талона амбулаторного пациента'
						}, {
							disabled: false,
							hidden: getRegionNick() == 'kareliya',
							handler: function() {
								var record = me.getSelectedRecord();
								var url = "";
								url = '/?c=EvnPL&m=printEvnPLBlank';
								if (record && record.get('Person_id')) {
									url = url + '&Person_id=' + record.get('Person_id');
								}
								window.open(url, '_blank');
							}.createDelegate(this),
							name: 'print_evnpl_blank_old',
							text: 'Печать бланка ТАП (до 2015г)',
							tooltip: 'Печать бланка талона амбулаторного пациента (до 2015г)'
						}, {
							disabled: false,
							hidden: getRegionNick() != 'ekb',
							handler: function(btn) {
								var record = me.getSelectedRecord();
								if (record) {
									var Person_id = record.get('Person_id');
									printBirt({
										'Report_FileName': 'tap_66_blank.rptdesign',
										'Report_Params': '&paramPerson=' + Person_id,
										'Report_Format': 'pdf'
									});
								}
							},
							name: 'print_tap_66_blank',
							text: 'ТАП Свердловской области',
							tooltip: 'Печать ТАП Свердловской области'
						}, {
							disabled: false,
							handler: function() {
								var record = me.getSelectedRecord();
								if (record) {
									var Person_id = record.get('Person_id');

									var PersonAmbulatCard_id = 0;
									if (!Ext6.isEmpty(record.get('PersonAmbulatCard_id'))) {// создать амбулаторную карту можно в этом же окне, но главный грид не обновляется
										PersonAmbulatCard_id = parseInt(record.get('PersonAmbulatCard_id'));
									}

									var PersonCard = 0,
										PersonId = 0;
									if (!Ext6.isEmpty(record.get('PersonCard_id')) && record.get('AttachLpu_id') == getGlobalOptions().lpu_id) {
										PersonCard = parseInt(record.get('PersonCard_id'));
									}

									if (!Ext6.isEmpty(record.get('Person_id'))) {
										PersonId = parseInt(record.get('Person_id'));
									}

									if (!PersonAmbulatCard_id) {
										Ext6.Msg.show({
											title: langs('Внимание'),
											msg: langs('У пациента нет амбулаторной карты в данной МО. Будет распечатан шаблон документа. Для печати карты предварительно создайте ее для пациента.'),
											buttons: {
												'yes': langs('Печать шаблона АК'),
												'cancel': langs('Отмена')
											},
											modal: true,
											fn: function(btn) {
												if (btn === 'yes') {
													me._printMedCard();
													return true;
												}
											}
										});
									}
									else {// есть амбулаторная карта
										this._printMedCard(PersonCard, PersonId, PersonAmbulatCard_id);
									}
								}
							}.createDelegate(this),
							name: 'print_personcard',
							text: 'Печать амбулаторной карты',
							tooltip: 'Печать амбулатороной карты пациента'
						}, {
							disabled: false,
							name: 'print_personstomcard',
							text: 'Печать стом. карты',
							tooltip: 'Печать стоматологической карты пациента',
							menu: sw.Promed.StomHelper.Report.getPrintMenu(function(callback) {
								var record = me.getSelectedRecord();
								if (record) {
									var Person_id = record.get('Person_id');
									var comp = this;
									if (!comp.Person_id || comp.Person_id != Person_id) {
										comp.Person_id = Person_id;
										comp.lastEvnPLStomData = null;
									}
									if (!comp.lastEvnPLStomData && Person_id) {
										sw.Promed.StomHelper.loadLastEvnPLStomData(Person_id, function(data) {
											comp.lastEvnPLStomData = data;
											callback(Person_id, data);
										});
									} else {
										callback(Person_id, comp.lastEvnPLStomData);
									}
								}
							}, {
								hideCostPrint: true,
								isExt6: true
							})
						}, {
							disabled: false,
							hidden: !(getRegionNick() == 'pskov'),
							handler: function(btn) {
								var record = me.getSelectedRecord();
								if (record) {
									var Person_id = record.get('Person_id');
									printBirt({
										'Report_FileName': 'Person_soglasie.rptdesign',
										'Report_Params': '&paramPerson=' + Person_id,
										'Report_Format': 'pdf'
									});
								}
							},
							name: 'print_personsogl',
							text: 'Согласие на мед. вмешательство (A4)',
							tooltip: 'Печать согласия на мед. вмешательство в формате A4'
						}, {
							disabled: false,
							hidden: !(getRegionNick() == 'pskov'),
							handler: function(btn) {
								var record = me.getSelectedRecord();
								if (record) {
									PersonCard_id = record.get('PersonCard_id');

									if (Ext6.isEmpty(PersonCard_id)) {
										sw.swMsg.alert('Ошибка', 'Невозможно напечатать документ. Проверьте прикрепление пациента.');
										return false;
									}

									printBirt({
										'Report_FileName': 'PersonCardMedicalIntervent.rptdesign',
										'Report_Params': '&paramPersonCard=' + PersonCard_id + '&paramMedPersonal=' + me.userMedStaffFact.MedPersonal_id,
										'Report_Format': 'pdf'
									});
								}
							},
							name: 'print_personotkaz',
							text: 'Отказ от мед. вмешательства',
							tooltip: 'Печать отказа от мед. вмешательства'
						}, {
							disabled: false,
							hidden: !(getRegionNick() == 'pskov'),
							handler: function(btn) {
								var record = me.getSelectedRecord();
								if (record) {
									var Person_id = record.get('Person_id');
									printBirt({
										'Report_FileName': 'PersonInfoSoglasie_Vac.rptdesign',
										'Report_Params': '&paramPerson=' + Person_id,
										'Report_Format': 'pdf'
									});
								}
							},
							name: 'print_personsoglvac',
							text: 'Согласие на вакцинацию',
							tooltip: 'Печать согласия на на вакцинацию'
						}, {
							disabled: false,
							handler: function(btn) {
								var record = me.getSelectedRecord();
								if (record) {
									var Person_id = record.get('Person_id');
									Ext6.Ajax.request({
										url: '/?c=Person&m=savePersonLpuInfo',
										success: function(response) {
											var response_obj = Ext6.JSON.decode(response.responseText);
											if (response_obj && response_obj.Error_Msg) {
												sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласие на обработку перс. данных');
												return false;
											} else if (response_obj && !Ext6.isEmpty(response_obj.PersonLpuInfo_id)) {
												me.mainGrid.getStore().reload();

												if (getRegionNick() == 'kz') {
													var lan = (getAppearanceOptions().language == 'ru' ? 1 : 2);
													printBirt({
														'Report_FileName': 'PersonSoglasie_PersData.rptdesign',
														'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id + '&paramLang=' + lan,
														'Report_Format': 'pdf'
													});
												} else {
													printBirt({
														'Report_FileName': 'PersonSoglasie_PersData.rptdesign',
														'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
														'Report_Format': 'pdf'
													});
												}
											}
										}.createDelegate(this),
										params: {
											Person_id: Person_id,
											PersonLpuInfo_IsAgree: 2
										}
									});
								}
							},
							name: 'print_personsogl_persdata',
							text: 'Согласие на обработку перс. данных (A4)',
							tooltip: 'Печать согласия на обработку персональных данных данных в формате A4'
						}, {
							disabled: false,
							handler: function(btn) {
								var record = me.getSelectedRecord();
								if (record) {
									var Person_id = record.get('Person_id');
									Ext6.Ajax.request({
										url: '/?c=Person&m=savePersonLpuInfo',
										success: function(response) {
											var response_obj = Ext6.JSON.decode(response.responseText);
											if (response_obj && response_obj.Error_Msg) {
												sw.swMsg.alert('Ошибка', 'Ошибка при сохранении Отзыва согласия на обработку перс. данных');
												return false;
											} else if (response_obj && !Ext6.isEmpty(response_obj.PersonLpuInfo_id)) {
												me.mainGrid.getStore().reload();

												printBirt({
													'Report_FileName': 'PersonOtkaz_PersData.rptdesign',
													'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
													'Report_Format': 'pdf'
												});
											}
										}.createDelegate(this),
										params: {
											Person_id: Person_id,
											PersonLpuInfo_IsAgree: 1
										}
									});
								}
							},
							name: 'print_personotzyvsogl_persdata',
							text: 'Отзыв согласия на обработку перс. данных',
							tooltip: 'Печать отзыва согласия на обработку перс. данных'
						}, {
							hidden: getRegionNick().inlist(['kz', 'ekb']),
							name: 'print_personsogl_persdata_a5',
							text: 'Согласие на обработку ПД (A5)',
							tooltip: 'Печать согласия на обработку персональных данных в формате A5',
							handler: function(btn) {
								if (getRegionNick().inlist(['kz', 'ekb'])) {
									return false;
								}

								var record = me.getSelectedRecord();
								if (record) {
									var Person_id = record.get('Person_id');
									Ext6.Ajax.request({
										url: '/?c=Person&m=savePersonLpuInfo',
										success: function(response) {
											var response_obj = Ext6.JSON.decode(response.responseText);
											if (response_obj && response_obj.Error_Msg) {
												sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласие на обработку перс. данных');
												return false;

											} else if (response_obj && !Ext6.isEmpty(response_obj.PersonLpuInfo_id)) {
												me.mainGrid.getStore().reload();

												printBirt({
													'Report_FileName': 'PersonSoglasie_PersData_A5oborot.rptdesign',
													'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
													'Report_Format': 'pdf'
												});

												printBirt({
													'Report_FileName': 'PersonSoglasie_PersData_A5.rptdesign',
													'Report_Params': '&paramPersonLpuInfo_id=' + response_obj.PersonLpuInfo_id,
													'Report_Format': 'pdf'
												});
											}

											return true;

										}.createDelegate(this),
										params: {
											Person_id: Person_id,
											PersonLpuInfo_IsAgree: 2
										}
									});
								}
							}
						}, {
							hidden: getRegionNick().inlist(['kz', 'ekb']),
							name: 'print_personscard_infoconsent_a5',
							text: 'Согласие на вмешательство (А5)',
							tooltip: 'Печать согласия на вмешательство в формате A5',
							handler: function(btn) {
								if (getRegionNick().inlist(['kz', 'ekb'])) {
									return false;
								}

								var record = me.getSelectedRecord();
								if (record) {
									PersonCard_id = record.get('PersonCard_id'),
									MedPersonal_id = me.userMedStaffFact.MedPersonal_id;


									if (Ext6.isEmpty(PersonCard_id)) {
										sw.swMsg.alert('Ошибка', 'Невозможно напечатать документ. Проверьте прикрепление пациента.');
										return false;
									}

									printBirt({
										'Report_FileName': 'PersonCardInfoConsent_A5oborot.rptdesign',
										'Report_Params': '&paramPersonCard=' + PersonCard_id + '&paramMedPersonal=' + MedPersonal_id,
										'Report_Format': 'pdf'
									});

									printBirt({
										'Report_FileName': 'PersonCardInfoConsent_A5.rptdesign',
										'Report_Params': '&paramPersonCard=' + PersonCard_id + '&paramMedPersonal=' + MedPersonal_id,
										'Report_Format': 'pdf'
									});
								}
							}
						}, {
							disabled: false,
							hidden: !getRegionNick().inlist(['perm']),
							handler: function(btn) {
								var record = me.getSelectedRecord();
								if (record) {
									var params = {
										Person_id: record.get('Person_id'),
										type: ''
									};

									getWnd('swCostPrintWindow').show(params);
								}
							},
							name: 'print_cost',
							text: 'Справка о стоимости лечения',
							tooltip: 'Справка о стоимости лечения'
						}, {
							disabled: false,
							hidden: (getRegionNick().inlist(['kz'])),
							handler: function() {
								me.printPregnancyBlank();
							},
							name: 'print_pregnancy',
							text: 'Бланк Индивидуальной карты беременной',
							tooltip: 'Бланк Индивидуальной карты беременной'
						}, {
							disabled: false,
							handler: function() {
								me.printFreeTemplate();
							},
							name: 'print_freedoc',
							text: 'Печать шаблона документа',
							tooltip: 'Печать шаблона документа'
						}, {
							disabled: false,
							handler: function(item) {
								me.printEvnPLPrescr(item.Evn_id);
							},
							name: 'print_EvnPLPrescr',
							itemId: 'print_EvnPLPrescr',
							text: 'Печать маршрутной карты',
							tooltip: 'Печать маршрутной карты'
						}]
					})
				}, {
					text: 'Это двойник',
					itemId: 'action_doubles',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_doubles',
					handler: function() {
						me.setDoubles();
					}
				}, {
					text: 'Добавить человека',
					itemId: 'action_add',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_add',
					handler: function() {
						me.openPersonEditWindow('add');
					}
				}, {
					text: 'Редактировать данные',
					itemId: 'action_edit',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_edit',
					handler: function() {
						me.openPersonEditWindow('edit');
					}
				}, {
					text: 'История лечения',
					itemId: 'action_history',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_history',
					handler: function() {
						me.openPersonCureHistoryWindow();
					}
				}]
			}, me.customBar],
			selModel: {
				selType: 'checkboxmodel',
				width: 65,
				listeners: {
					select: function(model, record, index) {
						me.onRecordSelect();
						me.personDoublesCache.addRecord(record, 'reg');
					},
					deselect: function(model, record, index) {
						me.onRecordSelect();
						me.personDoublesCache.removeRecord(record);
					}
				}
			},
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store) {
					var cls = '';
					if (record.get('Person_deadDT')) {
						cls = cls + 'x-grid-rowgray ';
					}
					if (record.get('PersonQuarantine_IsOn')) {
						cls = cls + 'x-grid-rowbackred ';
					}
					return cls;
				}
			},
			listeners: {
				itemdblclick: function() {
					me.openEvnDirectionEditWindow('add', 1);
				}
			},
			store: {
				fields: [
					{name: 'Person_id', type: 'int'},
					{name: 'Server_id', type: 'int'},
					{name: 'PersonEvn_id', type: 'int'},
					{name: 'PersonCard_id', type: 'int'},
					{name: 'Person_IsDead'},
					{name: 'AttachLpu_id', type: 'int'},
					{name: 'PersonAmbulatCard_id', type: 'int'},
					{name: 'PersonCard_Code'},
					{
						name: 'Person_FIO', convert: function(val, row) {
							var s = '';
							var PersonQuarantine = "";
							if(row.get('PersonQuarantine_IsOn')){
								var PQ_tooltip = "Пациент на карантине.";
								if(row.get('PersonQuarantine_begDT')){
									PQ_tooltip = "Карантин с "+ Ext6.util.Format.date(row.get('PersonQuarantine_begDT'), "d.m.Y");
								}
								PersonQuarantine = "<span style='float: left;' class='quarantined-patient' data-qtip='"+PQ_tooltip+"'></span>";
							}
							if (row.get('Person_Surname')) {
								s += Ext6.util.Format.capitalize(row.get('Person_Surname').toLowerCase());
							}

							if (row.get('Person_Firname')) {
								s += ' ' + Ext6.util.Format.capitalize(row.get('Person_Firname').toLowerCase());
							}

							if (row.get('Person_Secname')) {
								s += ' ' + Ext6.util.Format.capitalize(row.get('Person_Secname').toLowerCase());
							}

							return PersonQuarantine+s;
						}
					},
					{name: 'Person_Surname'},
					{name: 'Person_Firname'},
					{name: 'Person_Secname'},
					{name: 'Sex_id', type: 'int'},
					{name: 'Person_Birthday', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Person_deadDT', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'Person_IsFedLgot'},
					{name: 'Person_IsRegLgot'},
					{name: 'Person_IsRefuse'},
					{name: 'AttachLpu_Name'},
					{name: 'LpuRegion_Name'},
					{name: 'Person_IsBDZ'},
					{name: 'Polis_Num'},
					{name: 'PersonQuarantine_IsOn', type: 'boolean'},
					{name: 'PersonQuarantine_begDT', type: 'date', dateFormat: 'd.m.Y'}
				],
				pageSize: 100,
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Person6E&m=getPersonGrid',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'login'
				],
				listeners: {
					load: function() {
						me.onRecordSelect();
					}
				}
			},
			columns: [
				{text: 'Главная запись', width: 100, hidden: true, xtype: 'checkcolumn', listeners: {
					'checkchange': function (column, rowIndex, checked, rec, e, eOpts) {
						if (checked) {
							me.setIsMainRec(rec);
						} else {
							rec.commit();
						}
						// все остальные чекбосы надо снять
					}
				}, dataIndex: 'IsMainRec'},
				{text: 'Ам. карта', tdCls: 'padLeft', width: 150, dataIndex: 'PersonCard_Code'},
				{text: 'ФИО', width: 280, minWidth: 280, maxWidth: 380, dataIndex: 'Person_FIO', flex: 1},
				{text: 'Д/Р (Возраст)', width: 140, dataIndex: 'Person_Birthday', renderer: function(val) {
					return me.getPersonBirthString(val);
				}},
				{text: 'Дата смерти', width: 120, dataIndex: 'Person_deadDT', renderer: function(val) {
					return Ext6.util.Format.date(val, 'd.m.Y')
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
				{text: 'Прикрепление', width: 120, dataIndex: 'AttachLpu_Name'},
				{text: 'Участок', width: 80, dataIndex: 'LpuRegion_Name'},
				{text: 'РЗ', width: 100, dataIndex: 'Person_Bdz', renderer: function(val, metaData, record) {
					var s = '';
					if (record.get('Person_IsBDZ') && record.get('Person_IsBDZ') == 'true') {
						s += "<span class='lgot_rz' data-qtip='Регистр застрахованных'>РЗ</span>";
					}
					return s;
				}},
				{text: 'Полис', width: 140, dataIndex: 'Polis_Num'},
				{text: '', flex: 1, dataIndex: 'empty'}
			]
		});

		me.leftMenu = new Ext6.menu.Menu({
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
				if (!me.leftMenu.activeChild || me.leftMenu.activeChild.hidden) {
					clearInterval(me.leftMenu.collapseInterval); // сбрасывем
					me.leftMenu.getEl().setWidth(me.leftMenu.collapsedWidth); // сужаем
					me.leftMenu.body.setWidth(me.leftMenu.collapsedWidth - 1); // сужаем
					me.leftMenu.deactivateActiveItem();
				}
			},
			listeners: {
				mouseover: function() {
					clearInterval(me.leftMenu.collapseInterval); // сбрасывем
					me.leftMenu.getEl().setWidth(me.leftMenu.items.items[0].getWidth());
					me.leftMenu.body.setWidth(me.leftMenu.items.items[0].getWidth() - 1);
				},
				afterrender : function(scope) {
					me.leftMenu.setWidth(me.leftMenu.collapsedWidth); // сразу сужаем
					me.leftMenu.setZIndex(10); // fix zIndex чтобы панель не уезжала под грид

					this.el.on('mouseout', function() {
						// сужаем, если нет подменю
						clearInterval(me.leftMenu.collapseInterval); // сбрасывем
						me.leftMenu.collapseInterval = setInterval(me.leftMenu.collapseMenu, 100);
					});
				}
			},
			items: [{
				iconCls: 'find16-2017',
				menu: [{
					text: langs('ЛВН'),
					handler: function() {
						getWnd('swEvnStickViewWindow').show();
					}
				}, {
					text: langs('Человек'),
					handler: function() {
						getWnd('swPersonSearchWindowExt6').show({
							onSelect: function(person_data) {
								getWnd('swPersonEditWindow').show({
									onHide: function () {
										if ( person_data.onHide && typeof person_data.onHide == 'function' ) {
											person_data.onHide();
										}
									},
									Person_id: person_data.Person_id,
									Server_id: person_data.Server_id
								});
							},
							searchMode: 'all'
						});
					}
				}, {
					text: langs('Льготники'),
					handler: function() {
						getWnd('swPrivilegeSearchWindow').show();
					}
				}, {
					text: langs('Участки и врачи по адресу'),
					handler: function() {
						getWnd('swFindRegionsWindow').show();
					}
				}],
				text: langs('Поиск')
			}, {
				iconCls: 'pcard16-2017',
				menu: [{
					text: langs('РПН: Поиск'),
					handler: function() {
						getWnd('swPersonCardSearchWindow').show();
					}
				}, {
					text: langs('РПН: Прикрепление'),
					handler: function() {
						getWnd('swPersonCardViewAllWindow').show();
					}
				}, {
					text: langs('РПН: Журнал движения'),
					handler: function() {
						getWnd('swPersonCardStateViewWindow').show();
					}
				}, {
					text: langs('РПН: Заявления о выборе МО'),
					hidden: getRegionNick().inlist(['by']),
					handler: function() {
						getWnd('swPersonCardAttachListWindow').show();
					}
				}],
				text: langs('РПН')
			}, {
				iconCls: 'timetable16-2017',
				handler: function() {
					if (getRegionNick().inlist(['krym'])) {
						//новый интерфейс
						getWnd('swTimetableScheduleViewWindow').show();
					} else {
						getWnd('swScheduleEditMasterWindow').show();
					}
				},
				text: langs('Ведение расписания')
			}, {
				iconCls: 'action_addDate',
				handler: function() {
					var userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
					var params = new Object({
						userMedStaffFact: userMedStaffFact,
						type: 'LpuReg'
					});
					params.directionData = {
						LpuUnitType_SysNick: null
						,EvnQueue_id: null
						,QueueFailCause_id: null
						,Lpu_did: null // ЛПУ куда направляем
						,LpuUnit_did: null
						,LpuSection_did: null
						,EvnUsluga_id: null
						,LpuSection_id: null
						,EvnDirection_pid: null
						,EvnPrescr_id: null
						,PrescriptionType_Code: null
						,DirType_id: null
						,LpuSectionProfile_id: null
						,Diag_id: null
						,MedStaffFact_id: null
						,MedPersonal_id: (userMedStaffFact ) ? userMedStaffFact.MedPersonal_id : getGlobalOptions().medpersonal_id
						,MedPersonal_did: null
						,type: 'LpuReg'
					};
					getWnd('swDirectionMasterWindow').show(params);
				},
				text: langs('Запись к врачу')
			}, {
				iconCls: 'homevisit16-2017',
				handler: function() {
					getWnd('swHomeVisitListWindow').show({type:'regpol'});
				},
				text: langs('Журнал вызовов на дом')
			}, {
				iconCls: 'moderrec16-2017',
				handler: function() {
					getWnd('swTimetableGrafModerationWindow').show();
				},
				text: langs('Модерация записей с портала')
			}, {
				iconCls: 'moderuser16-2017',
				handler: function() {
					getWnd('swInetPersonModerationWindow').show();
				},
				text: langs('Модерация пользователей портала')
			}, {
				iconCls: 'quote16-2017',
				handler: function() {
					getWnd('swTimetableQuoteEditorWindow').show();
				},
				text: langs('Редактирование квот приема')
			}, {
				iconCls: 'direction16-2017',
				handler: function() {
					getWnd('swMPQueueWindow').show({
						ARMType: 'regpol',
						callback: function(data) {
							// this.createTtgAndOpenPersonEPHForm(data);
							// this.scheduleRefresh();
						}.createDelegate(this),
						mode: 'view',
						userMedStaffFact: this.userMedStaffFact,
						onSelect: function(data) { // на тот случай если из режима просмотра очереди будет сделана запись
							getWnd('swMPQueueWindow').hide();
							getWnd('swMPRecordWindow').hide();
							// Ext6.getCmp('swMPWorkPlaceWindow').scheduleSave(data);
						}
					});
				}.createDelegate(this),
				text: langs('Журнал направлений и записей')
			}, {
				iconCls: 'emk-view',
				handler: function () {
					getWnd('swTFOMSQueryWindow').show({ARMType: 'mstat'});
				},
				text: langs('Запросы на просмотр ЭМК')
			}, {
				iconCls: 'cardstorage16-2017',
				handler: function() {
					getWnd('swCardStorageWindow').show();
				},
				text: langs('Картохранилище')
			}, {
				iconCls: 'registration-complaints',
				text: langs('Регистрация обращений'),
				menu: [{
					text: langs('Регистрация обращений: Поиск'),
					tooltip: langs('Регистрация обращений: Поиск'),
					handler: function() {
						getWnd('swTreatmentSearchWindow').show();
					},
					hidden: !isUserGroup(['TreatmentSpecialist'])
				}, {
					handler: function() {
						getWnd('swTreatmentReportWindow').show();
					},
					text: langs('Регистрация обращений: Отчетность'),
					tooltip: langs('Регистрация обращений: Отчетность'),
					hidden: !isUserGroup(['TreatmentSpecialist'])
				}]
			}, {
				iconCls: 'spr16-2017',
				menu: [{
					text: getRLSTitle(),
					handler: function() {
						getWnd('swRlsViewForm').show();
					},
					hidden: false
				}, {
					text: 'Справочник ' + getMESAlias(),
					handler: function() {
						if (!getWnd('swMesOldSearchWindow').isVisible())
							getWnd('swMesOldSearchWindow').show();
					}.createDelegate(this)
				}, {
					text: 'Справочник услуг',
					handler: function() {
						//getWnd('swUslugaTreeWindow').show({action: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))?'view':''});
						getWnd('swUslugaTreeWindow').show({action: 'view'});
					}
				}, {
					text: 'Справочники системы учета медикаментов',
					handler: function() {
						getWnd('swDrugDocumentSprWindow').show();
					}
				}],
				text: langs('Справочники')
			}, {
				iconCls: 'structure16-2017',
				hidden: !isAdmin && !isLpuAdmin() && !isCadrUserView(),
				menu: [{
					text: langs('Структура МО'),
					handler: function() {
						getWnd('swLpuStructureViewForm').show();
					}
				}, {
					text: langs('Паспорт МО'),
					handler: function() {
						getWnd('swLpuPassportEditWindow').show({
							action: 'edit',
							Lpu_id: getGlobalOptions().lpu_id
						});
					}
				}],
				text: langs('Структура')
			}, {
				iconCls: 'replacement16-2017',
				handler: function() {
					getWnd('swMedStaffFactReplaceViewWindow').show();
				},
				text: langs('График замещений')
			}, {
				iconCls: 'cabinet16-2017',
				menu: [{
					text: langs('Справочник кабинетов'),
					handler: function() {
						getWnd('swLpuBuildingOfficeListWindow').show();
					}
				}, {
					text: langs('Расписание работы врачей'),
					handler: function() {
						getWnd('swLpuBuildingScheduleWorkDoctorWindow').show();
					}
				}],
				text: langs('Справочник кабинетов')
			}, {
				iconCls: 'notice16-2017',
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				},
				text: langs('Журнал уведомлений')
			}, {
				iconCls: 'personcard16-2017',
				handler: function() {
					if (!Ext6.isEmpty(getGlobalOptions().check_attach_allow) && getGlobalOptions().check_attach_allow == 1 && !(isUserGroup('LpuAdmin') || isUserGroup('RegAdmin') || isUserGroup('SuperAdmin'))) {
						sw.swMsg.alert('Сообщение', 'У вас нет прав для редактирования прикрепления.');
						return false;
					}

					getWnd('swPersonSearchPersonCardAutoWindow').show();
				},
				text: langs('Групповое прикрепление')
			}, {
				iconCls: 'messages16-2017',
				handler: function() {
					getWnd('swNewslatterListWindow').show();
				},
				hidden: !isUserGroup('Newslatter'),
				text: langs('Управление рассылками')
			}, {
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
				text: langs('Отчеты')
			}, {
				iconCls: 'templates16-2017',
				handler: function() {
					var params = {
						allowedEvnClassList: [27],

						allowedXmlTypeEvnClassLink: {
							2:  [27],
							18:  [27],
							19:  [27],
							20: [27]
						},

						EvnClass_id: 27,
						XmlType_id: 3,
						LpuSection_id: me.userMedStaffFact.LpuSection_id,
						MedPersonal_id: me.userMedStaffFact.MedPersonal_id,
						MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id
					};
					getWnd('swXmlTemplateEditorWindow').show(params);
				},
				text: 'Шаблоны документов'
			}]
		});

		me.filterPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 20px 30px 0px 0px;',
			cls: 'person-search-input-panel',
			region: 'north',
			items: [{
				border: false,
				layout: 'column',
				padding: '0 0 0 28',
				items: [{
					border: false,
					layout: 'anchor',
					defaults: {
						anchor: '100%',
						labelWidth: 95,
						width: 250,
						listeners: {
							specialkey: function (field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						plugins: [ new Ext6.ux.Translit(true, false) ],
						fieldLabel: 'Фамилия',
						name: 'Person_Surname'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 27',
					defaults: {
						anchor: '100%',
						labelWidth: 95,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						plugins: [ new Ext6.ux.Translit(true, false) ],
						fieldLabel: 'Имя',
						name: 'Person_Firname'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 15',
					defaults: {
						anchor: '100%',
						labelWidth: 75,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						plugins: [ new Ext6.ux.Translit(true, false) ],
						fieldLabel: 'Отчество',
						name: 'Person_Secname'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%',
						labelWidth: 45,
						width: 180,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						allowBlank: true,
						xtype: 'swDateField',
						fieldLabel: 'Д/Р',
						name: 'Person_Birthday'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%'
					},
					items: [{
						border: false,
						style: 'margin-top: 3px;',
						items: [{
							width: 180,
							cls: 'button-secondary',
							text: 'Считать с карты',
							xtype: 'button',
							handler: function() {
								me.readFromCard();
							}
						}]
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				padding: '0 0 0 28',
				items: [{
					border: false,
					layout: 'anchor',
					defaults: {
						anchor: '100%',
						labelWidth: 95,
						width: 250,
						listeners: {
							specialkey: function (field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Улица',
						name: 'Address_Street'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 27',
					defaults: {
						anchor: '100%',
						labelWidth: 95,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Дом',
						name: 'Address_House'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 15',
					defaults: {
						anchor: '100%',
						labelWidth: 75,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Амб. карта',
						name: 'PersonCard_Code'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%',
						labelWidth: 45,
						width: 180,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'checkbox',
						hideLabel: true,
						boxLabel: 'Учитывать истории карт',
						name: 'checkHistoryCard'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%'
					},
					items: [{
						border: false,
						style: 'margin-top: 8px;',
						items: [{
							width: 180,
							cls: 'button-secondary',
							text: 'Очистить',
							xtype: 'button',
							handler: function() {
								me.doReset();
							}
						}]
					}]
				}]
			}, {
				border: false,
				layout: 'column',
				padding: '0 0 0 28',
				items: [{
					border: false,
					layout: 'anchor',
					defaults: {
						anchor: '100%',
						labelWidth: 95,
						width: 250,
						listeners: {
							specialkey: function (field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Серия полиса',
						name: 'Polis_Ser'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 27',
					defaults: {
						anchor: '100%',
						labelWidth: 95,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 15',
					defaults: {
						anchor: '100%',
						labelWidth: 75,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Ед. номер',
						name: 'Person_Code'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%',
						labelWidth: 45,
						width: 180,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									me.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'checkbox',
						hideLabel: true,
						boxLabel: 'Учитывать умерших',
						name: 'showAll'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%'
					},
					items: [{
						border: false,
						style: 'margin-top: 8px;',
						items: [{
							width: 180,
							cls: 'button-primary',
							text: 'Найти',
							xtype: 'button',
							handler: function() {
								me.doSearch();
							}
						}]
					}]
				}]
			}]
		});

		this.EvnDirectionHeader = Ext6.create('swPanel', {
			title: 'НАПРАВЛЕНИЯ И ЗАПИСИ',
			itemId: 'EvnDirectionPanel'
		});
		this.PersonAmbulatCardHeader = Ext6.create('swPanel', {
			title: 'АМБУЛАТОРНЫЕ КАРТЫ',
			itemId: 'PersonAmbulatCardPanel'
		});

		this.EvnDirectionPanel = Ext6.create('common.PolkaRegWP.EvnDirectionPanel', {
			header: false,
			headerPanel: this.EvnDirectionHeader,
			itemId: 'EvnDirectionPanel',
			ownerWin: me,
			ARMType: 'regpol'
		});
		this.PersonAmbulatCardPanel = Ext6.create('common.PolkaRegWP.PersonAmbulatCardPanel', {
			header: false,
			headerPanel: this.PersonAmbulatCardHeader,
			itemId: 'PersonAmbulatCardPanel',
			ownerWin: me
		});

		this.bottomTabPanel = Ext6.create('Ext6.TabPanel', {
			border: false,
			region: 'center',
			activeTab: 0,
			width: 400,
			defaults: {
				tabConfig: {
					margin: 0,
					cls: 'evn-pl-tab-panel-items bottom-tab-panel-items'
				}
			},
			items: [
				me.EvnDirectionHeader,
				me.PersonAmbulatCardHeader
			],
			beforeSetActiveTab: function(card) {
				if (this.getActiveTab() == card) {
					me.bottomPanel.collapse();
					return false;
				}

				return true;
			},
			listeners: {
				'render': function() {
					me.bottomPanel.setSplitterHidden(false);
				},
				'tabchange': function(tabPanel, newCard) {
					me.bottomPanel.expand();
					me.bottomPanel.setActiveItem(newCard.itemId);
					me.bottomPanel.layout.getActiveItem().load();

					me.bottomPanel.setSplitterHidden(false);
				}
			}
		});

		this.personLabel = new Ext6.form.Label({
			style: 'padding-left: 20px; color: #FFFFFF;',
			html: ''
		});

		this.bottomPanel = new Ext6.Panel({
			collapseMode: 'header',
			region: 'south',
			layout: 'card',
			flex: 400,
			collapsed: false,
			split: true,
			hideCollapseTool: true,
			collapsible: true,
			scrollable: true,
			border: false,
			bodyStyle: 'border: 0;',
			listeners: {
				'collapse': function() {
					me.bottomPanel.setSplitterHidden(true);
					me.bottomTabPanel.activeTab = null; // пусть думает что таб не выбран
					me.bottomTabPanel.tabBar.activeTab.deactivate(); // деактивируем активный таб
					me.bottomTabPanel.tabBar.activeTab = null; // пусть думает что таб не выбран
				}
			},
			setSplitterHidden: function(hidden) {
				if (hidden) {
					me.bottomPanel.splitter.setHeight(0);
					me.bottomPanel.splitter.getEl().hide();
				} else {
					me.bottomPanel.splitter.setHeight(10);
					me.bottomPanel.splitter.getEl().show();
				}
			},
			header: {
				xtype: 'header',
				padding: 0,
				itemPosition: 0,
				border: false,
				items: [me.personLabel, {
					xtype: 'tbspacer',
					flex: 100
				}, me.bottomTabPanel]
			},
			items: [
				me.EvnDirectionPanel,
				me.PersonAmbulatCardPanel
			]
		});

		me.mainPanel = new Ext6.Panel({
			animCollapse: false,
			floatable: false,
			collapsible: false,
			flex: 100,
			region: 'center',
			layout: 'border',
			activeItem: 0,
			border: false,
			items: [
				me.filterPanel, {
					dockedItems: [me.leftMenu],
					border: false,
					region: 'center',
					layout: 'border',
					items: [
						me.mainGrid,
						me.bottomPanel
					]
				}
			]
		});

		Ext6.apply(me, {
			items: [
				me.mainPanel
			]
		});

		this.callParent(arguments);
	}
});