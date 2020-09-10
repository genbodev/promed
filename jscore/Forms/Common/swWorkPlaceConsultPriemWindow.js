/**
* АРМ врача службы консультативного приема
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      июль.2013
*/
sw.Promed.swWorkPlaceConsultPriemWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
    //объект с параметрами АРМа, с которыми была открыта форма
    userMedStaffFact: null,
	gridPanelAutoLoad: false,
	id: 'swWorkPlaceConsultPriemWindow',
    /**
     * Отмена направления с указанием причины
     */
	cancelEvnDirection: function() {
		var win = this;
        var grid = this.GridPanel.getGrid();
        var record = grid.getSelectionModel().getSelected();
        if ( !record || !record.get('EvnUslugaPar_id') ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zayavka']);
            return false;
        }		
		getWnd('swSelectEvnStatusCauseWindow').show({
			EvnClass_id: 27,
			formType: 'uslugapar',
			callback: function(EvnStatusCauseData) {
				if (!Ext.isEmpty(EvnStatusCauseData.EvnStatusCause_id)) {
					win.getLoadMask("Отмена направлений на консультацию...").show();
					Ext.Ajax.request({
						params: {
							EvnDirection_id: record.get('EvnDirection_id'),
							EvnStatusCause_id: EvnStatusCauseData.EvnStatusCause_id,
							EvnStatusHistory_Cause: EvnStatusCauseData.EvnStatusHistory_Cause
						},
						url: '/?c=EvnUslugaPar&m=cancelDirection',
						callback: function (options, success, response) {
							win.getLoadMask().hide();
							if (success) {
								win.GridPanel.loadData();
							}
						}
					});
				}
			}
		});
        return true;
    },
    /**
     * Пытаемся определить MedStaffFact_id врача в отделении, где создана служба
     * Если врач не имеет мест работы в отделении, где создана служба,
     * то сообщаем об этом
     */
    _defineMedStaffFactId: function(callback) {
        var thas = this;
        setMedStaffFactGlobalStoreFilter({
            onDate: getGlobalOptions().date,
            LpuSection_id: this.userMedStaffFact.LpuSection_id
        });
        var records = [];
        swMedStaffFactGlobalStore.each(function(record) {
            if ( record.get('MedPersonal_id') == thas.userMedStaffFact.MedPersonal_id ) {
                records.push(record);
            }
        });
        if (records.length > 0) {
            thas.userMedStaffFact.MedStaffFact_id = records[0].get('MedStaffFact_id');
            if (typeof callback == 'function') {
                callback();
            }
        } else {
            sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_imeete_mest_rabotyi_v_otdelenii_gde_sozdana_slujba']);
            thas.hide();
        }
        /*
        this.getLoadMask("Выполнение запроса к серверу...").show();
        Ext.Ajax.request({
            failure: function() {
                thas.getLoadMask().hide();
            },
            params: {
                MedService_id: this.userMedStaffFact.MedService_id
            },
            success: function(response) {
                thas.getLoadMask().hide();
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if ( response_obj.success ) {
                    if (response_obj.MedStaffFact_id) {
                        thas.userMedStaffFact.MedStaffFact_id = response_obj.MedStaffFact_id;
                        if (typeof callback == 'function') {
                            callback();
                            return true;
                        }
                    } else {
                        sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_imeete_mest_rabotyi_v_otdelenii_gde_sozdana_slujba']);
                    }
                }
                thas.hide();
                return false;
            },
            url: '/?c=null&m=defineMedStaffFactId'
        });
        */
    },
    /**
     * Установка статуса "Выполнено" для связанного назначения
     */
    _execEvnPrescr: function(record, data) {
        var thas = this;
        var conf = {
            ownerWindow: this
            ,PrescriptionType_id: record.get('PrescriptionType_id')
            ,EvnPrescr_id: record.get('EvnPrescr_id')
        };
        conf.onExecSuccess = function(){
            record.set('EvnPrescr_IsExec', 2);
            record.set('MedPersonal_FIO', thas.userMedStaffFact.MedPersonal_FIO);
            record.commit();
            thas._setIsHasReception(record, data);
        };
        sw.Promed.EvnPrescr.execRequest(conf);
        return true;
    },
    /**
     * Установка статуса "Выполнено" для заказа услуги
     */
    _setIsHasReception: function(record, data) {
        var grid = this.GridPanel.getGrid();
        var thas = this;
        if (data) {
            //делаю так, пока не решен вопрос нужно ли записывать состояние "выполнено" заказу
            //и как это правильно сделать для заказа конс.услуги,
            //чтобы она не становилась видимой после этого в ЭМК
            record.set('Usluga_IsHasReception', 'true');
            record.set('MedPersonal_FIO', data.evnUslugaData ? data.evnUslugaData.MedPersonal_Fin : thas.userMedStaffFact.MedPersonal_FIO);
            record.commit();
            grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
            return true;
        }
        this.getLoadMask("Выполнение запроса к серверу...").show();
        Ext.Ajax.request({
            failure: function() {
                thas.getLoadMask().hide();
            },
            params: {
                EvnUslugaPar_id: record.get('EvnUslugaPar_id'),
                MedPersonal_uid: thas.userMedStaffFact.MedPersonal_id,
                LpuSection_uid: thas.userMedStaffFact.LpuSection_id,
                Lpu_uid: thas.userMedStaffFact.Lpu_id,
                EvnPL_id: data.EvnPL_id || null,
                EvnVizitPL_id: data.EvnVizitPL_id || null
            },
            success: function(response) {
                thas.getLoadMask().hide();
                var response_obj = Ext.util.JSON.decode(response.responseText);
                if ( response_obj.success ) {
                    record.set('Usluga_IsHasReception', 'true');
                    record.commit();
                    grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
                }
            },
            url: '/?c=EvnUslugaOrder&m=exec'
        });
        return true;
    },
    /**
     * Принять пациента / Открыть ЭМК
     *
     * Нужно учитывать нюансы:
     * был ли пациент принят
     * есть ли связанное назначение (record.get('EvnPrescr_id'), record.get('EvnPrescr_IsExec'))
     * из какого документа создано назначение (record.get('EvnPrescrParentEvn_id'), record.get('EvnPrescrParentEvn_rid'), record.get('EvnPrescrParentEvnClass_SysNick'))
     * где находится служба (this.userMedStaffFact.LpuUnitType_SysNick)
     */
    _openEmk: function(record, params) {
        var thas = this;
        if (!this.userMedStaffFact.MedStaffFact_id) {
            // для правильной работы ЭМК нужно определить MedStaffFact_id врача в отделении, где создана служба
            this._defineMedStaffFactId(function() {
                thas._openEmk(record, params);
            });
            return false;
        }

        params.userMedStaffFact = this.userMedStaffFact;
        params.mode = 'workplace';
        params.ARMType = 'common';
        params.accessViewFormDelegate = {};
		params.EvnDirectionData = record ? record.data : {};
		params.EvnDirectionData.useCase = 'load_data_for_create_tap_consult';
		params.EvnDirectionData.VizitType_SysNick = ('kz' == getRegionNick()) ? 'other' : 'consul';
		params.EvnDirectionData.VizitType_id = 3;
		
		if (!record && params && params.EvnDirection_id) {
			params.EvnDirectionData.EvnDirection_id = params.EvnDirection_id;		
		}
		
        if ( record ) {
			params.onDeleteEvent = function(success, data) {			
				record.set('Usluga_IsHasReception', 'false');
				record.set('MedPersonal_FIO', null);
				record.commit();
			}
			if (record.get('EvnPrescr_id')) {
				if ( 2 != record.get('EvnPrescr_IsExec') ) {
					params.onExecEvnPrescr = function(success, data) {
						thas._setIsHasReception(record, data);
					};
					params.onSaveEvnDocument = function(success, data) {
						if (success && (data.EvnPL_id>0 || (data.evnUslugaData && data.evnUslugaData.EvnUsluga_id))) {
							thas._execEvnPrescr(record, data);
						}
					};
				}
				//'polka' == this.userMedStaffFact.LpuUnitType_SysNick
				if (!Ext.isEmpty(record.get('EvnPrescrParentEvnClass_SysNick')) && record.get('EvnPrescrParentEvnClass_SysNick').inlist([ 'EvnPS', 'EvnSection' ]) && record.get('EvnPrescrParentEvn_rid')) {
					// если пациент лежит в стационаре
					params.searchNodeObj = {
						parentNodeId: 'root',
						last_child: false,
						disableLoadViewForm: false,
						EvnClass_SysNick: 'EvnPS',
						Evn_id: record.get('EvnPrescrParentEvn_rid'),
						//позиционируем на движении, в котором было создано назначение
						scroll_value: ('EvnSection_data_'+ record.get('EvnPrescrParentEvn_id'))
					};
					params.addStacActions = ['action_StacSvid'];
					// доступный функционал
					// выполнить назначение (только свое) с занесением оказания услуги
					params.EvnPrescr_id = record.get('EvnPrescr_id');
					// добавить файлы
					params.accessViewFormDelegate['EvnMediaDataList_'+ record.data.EvnPrescrParentEvn_rid +'_add'] = true;
					// добавить событие оказания услуги
					params.accessViewFormDelegate['EvnUslugaStacList_'+ record.data.EvnPrescrParentEvn_id +'_add'] = true;
					// добавить документы в разделы осмотры и документы
					params.accessViewFormDelegate['EvnXmlProtokolList_'+ record.data.EvnPrescrParentEvn_id +'_adddoc'] = true;
					//params.accessViewFormDelegate['EvnXmlRecordList_'+ record.data.EvnPrescrParentEvn_id +'_adddoc'] = true;
					//params.accessViewFormDelegate['EvnXmlEpikrizList_'+ record.data.EvnPrescrParentEvn_id +'_adddoc'] = true;
					params.accessViewFormDelegate['EvnXmlOtherList_'+ record.data.EvnPrescrParentEvn_id +'_adddoc'] = true;
				} else {
					// если пациент НЕ лежит в стационаре
					if ( 2 != record.get('EvnPrescr_IsExec') ) {
						//создаем ТАП и посещение с целью "Консультация" и параметрами направления
						//при сохранении посещения назначение должно быть отмечено как выполненное
						params.onShow = function(form) {
							/*form.addNewEvnPLAndEvnVizitPL({
								useCase: 'load_data_for_create_tap_consult',
								EvnPrescr_id: record.get('EvnPrescr_id'),
								VizitType_SysNick: 'consul',
								EvnDirection_id: record.get('EvnDirection_id'),
								Diag_id: record.get('Diag_id')
							});*/
						};
					}
				}
			} else {
				// направление создано без назначения из АРМа регистратора
				//if ( record.get('EvnStatus_id') && 15 != record.get('EvnStatus_id') ) {
					//создаем ТАП и посещение с целью "Консультация" и параметрами направления
					//при сохранении посещения назначение должно быть отмечено как выполненное
					params.onShow = function(form) {
						/*form.addNewEvnPLAndEvnVizitPL({
							useCase: 'load_data_for_create_tap_consult',
							EvnPrescr_id: null,
							VizitType_SysNick: 'consul',
							EvnDirection_id: record.get('EvnDirection_id'),
							Diag_id: record.get('Diag_id')
						});*/
					};
				//}
			}
			params.onSaveEvnDocument = function(success, data) {
				if (success && ((data.EvnPL_id > 0 && data.EvnVizitPL_id > 0) || (data.evnUslugaData && data.evnUslugaData.EvnUsluga_id))) {
					thas._setIsHasReception(record, data);
				}
			};
        } else {		
			params.callback = function() {			
				thas.GridPanel.loadData();
			}
		}
        getWnd('swPersonEmkWindow').show(params);
        return true;
    },	
	openEvnDirectionEditWindow: function() { // Просмотр направления
        var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

        getWnd('swEvnDirectionEditWindow').show({
			Person_id: record.get('Person_id'),
            EvnDirection_id: record.get('EvnDirection_id'),
            action: 'view',
            formParams: {}
        });
    },
	doRecordPerson: function(params) { // запись
		log(params);
		var win = this;
		if (params.TimetableMedService_id > 0) {
			getWnd('swTTMSScheduleRecordWindow').hide();
			win.getLoadMask(lang['zapis_patsienta']).show();
			// нужно записать человека на эту бирку
			Ext.Ajax.request({
				url: '/?c=EvnUslugaPar&m=recordPerson',
				params: params,
				callback: function(options, success, response) {
					win.getLoadMask().hide();
					win.GridPanel.refreshRecords(null, 0);
				},
				failure: function() {
					win.getLoadMask().hide();
				}
			});
		}
	},
	recordPerson: function() { // запись
		var win = this;
		var rec = this.GridPanel.getGrid().getSelectionModel().getSelected();
        var swSelectUslugaComplexWindow = getWnd('swSelectUslugaComplexWindow');
        if ( swSelectUslugaComplexWindow.isVisible() ) {
            swSelectUslugaComplexWindow.hide();
        }
		var usluga_grid = this.UslugaPanel.getGrid();
        var record = usluga_grid.getSelectionModel().getSelected();
        if ( !record || !record.get('UslugaComplex_id') ) {
            return false;
        }
		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				getWnd('swPersonSearchWindow').hide();
				var params = {
					Person_id: person_data.Person_id,
					MedService_id: win.msData.MedService_id,
					LpuUnitType_SysNick: win.msData.LpuUnitType_SysNick
				};

				Ext.Ajax.request({
					url: '/?c=EvnUslugaPar&m=checkOpenEvnSection',
					params: params,
					callback: function (options, success, response) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success) {
							var params = {
								MedService_id: win.msData.MedService_id,
								MedServiceType_id: win.msData.MedServiceType_id,
								MedService_Nick: win.msData.MedService_Nick,
								MedService_Name: win.msData.MedService_Name,
								MedServiceType_SysNick: win.msData.MedServiceType_SysNick,
								Lpu_did: win.msData.Lpu_id,
								LpuUnit_did: win.msData.LpuUnit_id,
								LpuUnitType_SysNick: win.msData.LpuUnitType_SysNick,
								LpuSection_uid: win.msData.LpuSection_id,
								LpuSection_id: win.msData.LpuSection_id,
								LpuSection_did: win.msData.LpuSection_id,
								MedPersonal_id: win.msData.MedPersonal_id,
								MedStaffFact_id: win.userMedStaffFact,
								LpuSection_Name: win.msData.LpuSection_Name,
								LpuSectionProfile_id: win.msData.LpuSectionProfile_id,
								userMedStaffFact: win.userMedStaffFact,
								TimetableMedService_id: rec.get('TimetableMedService_id'),
								EvnDirection_Num: 0,
								DirType_id: 11,
								EvnDirection_IsAuto: 2,
								EvnDirection_setDate: getGlobalOptions().date,
								EvnUslugaPar_setDate: getGlobalOptions().date,
								UslugaComplex_id: record.get('UslugaComplex_id'),
								Person_id: person_data.Person_id,
								PersonEvn_id: person_data.PersonEvn_id,
								Server_id: person_data.Server_id,
								MedService_id: win.msData.MedService_id,
								LpuUnitType_SysNick: win.msData.LpuUnitType_SysNick
							};
							win.doRecordPerson(params);
						}else if(response_obj.error){
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
					},
					failure: function () {
						win.getLoadMask().hide();
					}
				});
			},
			searchMode: 'all'
		});
	},
	recordPersonFromQueue: function() { // запись из очереди
		var win = this;
		var rec = this.GridPanel.getGrid().getSelectionModel().getSelected();		
		if (rec && rec.get('EvnQueue_id') && rec.get('Person_id')) {
			getWnd('swTTMSScheduleRecordWindow').show({
				disableRecord: true,
				MedService_id: win.msData.MedService_id,
				UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id'),
				MedServiceType_id: win.msData.MedServiceType_id,
				MedService_Nick: win.msData.MedService_Nick,
				MedService_Name: win.msData.MedService_Name,
				MedServiceType_SysNick: win.msData.MedServiceType_SysNick,
				Lpu_did: win.msData.Lpu_id,
				LpuUnit_did: win.msData.LpuUnit_id,
				LpuUnitType_SysNick: win.msData.LpuUnitType_SysNick,
				LpuSection_uid: win.msData.LpuSection_id,
				LpuSection_Name: win.msData.LpuSection_Name,
				LpuSectionProfile_id: win.msData.LpuSectionProfile_id,
				userMedStaffFact: win.userMedStaffFact,
				callback: function(ttms){
					if (ttms.TimetableMedService_id > 0) {
						getWnd('swTTMSScheduleRecordWindow').hide();
						win.getLoadMask(lang['zapis_patsienta']).show();
						// нужно записать человека на эту бирку
						Ext.Ajax.request({
							url: '/?c=Queue&m=ApplyFromQueue',
							params: {
								TimetableMedService_id: ttms.TimetableMedService_id,
								EvnDirection_id: rec.get('EvnDirection_id'),
								EvnQueue_id: rec.get('EvnQueue_id'),
								Person_id: rec.get('Person_id')
							},
							callback: function(options, success, response) {
								win.getLoadMask().hide();
								win.GridPanel.refreshRecords(null, 0);
							},
							failure: function() {
								win.getLoadMask().hide();
							}
						});
					}
				}
			});		
		}
	},
	acceptWithoutRecord: function() { // приём без записи
		var win = this;
		var rec = this.GridPanel.getGrid().getSelectionModel().getSelected();
        var swSelectUslugaComplexWindow = getWnd('swSelectUslugaComplexWindow');
        if ( swSelectUslugaComplexWindow.isVisible() ) {
            swSelectUslugaComplexWindow.hide();
        }
			getWnd('swPersonSearchWindow').show({
				onSelect: function (person_data) {
					getWnd('swPersonSearchWindow').hide();
					//После выбора пациента - выбор услуги.
					var params = {
						Person_id: person_data.Person_id,
						MedService_id: win.msData.MedService_id,
						LpuUnitType_SysNick: win.msData.LpuUnitType_SysNick
					};
					Ext.Ajax.request({
						url: '/?c=EvnUslugaPar&m=checkOpenEvnSection',
						params: params,
						callback: function (options, success, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success) {
								swSelectUslugaComplexWindow.show({
									onSelect: function (record) {
										swSelectUslugaComplexWindow.hide();
										win.getLoadMask('Приём пациента без записи...').show();
										var params = {
											Person_id: person_data.Person_id,
											PersonEvn_id: person_data.PersonEvn_id,
											Server_id: person_data.Server_id,
											MedService_id: win.msData.MedService_id,
											Lpu_id: win.msData.Lpu_id,
											Lpu_did: win.msData.Lpu_id,
											//LpuUnit_did: win.msData.LpuUnit_did,
											LpuSection_id: win.msData.LpuSection_id,
											LpuSection_did: win.msData.LpuSection_id,
											MedPersonal_id: win.msData.MedPersonal_id,
											MedStaffFact_id: win.msData.userMedStaffFact,
											LpuSectionProfile_id: win.msData.LpuSectionProfile_id,
											EvnDirection_Num: 0,
											DirType_id: 11,
											EvnDirection_IsAuto: 2,
											EvnDirection_setDate: getGlobalOptions().date,
											UslugaComplex_id: record.get('UslugaComplex_id'),
											EvnUslugaPar_setDate: getGlobalOptions().date,
											LpuUnitType_SysNick: win.msData.LpuUnitType_SysNick
										};
										Ext.Ajax.request({
											url: '/?c=EvnUslugaPar&m=acceptWithoutRecord',
											params: params,
											callback: function (options, success, response) {
												win.getLoadMask().hide();
												win.GridPanel.refreshRecords(null, 0);
												var response_obj = Ext.util.JSON.decode(response.responseText);
												if (response_obj.EvnDirection_id) {
													params.EvnDirection_id = response_obj.EvnDirection_id;
													win._openEmk(false, params);
												}
											},
											failure: function () {
												win.getLoadMask().hide();
											}
										});
									},
									mode: 'MedService',
									baseParams: {
										Lpu_uid: win.msData.Lpu_id,
										MedService_id: win.msData.MedService_id,
										UslugaComplex_Date: getGlobalOptions().date,
										level: 0
									}
								});
							}else if(response_obj.error){
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
							}
						},
						failure: function () {
							win.getLoadMask().hide();
						}
					});
				},
				searchMode: 'all'
			});
	},
	returnToQueue: function() { // Возврат в очередь
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
		var grid = this.GridPanel.getGrid();
		if (record == false || !record.get('TimetableMedService_id')) {
			return false;
		}
		return sw.Promed.Direction.returnToQueue({
			loadMask: this.getLoadMask(lang['pojaluysta_podojdite']),
			EvnDirection_id: record.get('EvnDirection_id'),
			TimetableMedService_id: record.get('TimetableMedService_id'),
			EvnQueue_id: record.get('EvnQueue_id'),
			callback: function (data) {
				grid.getStore().reload({
					callback: function () {
						if (data.EvnDirection_id) {
							var index = grid.getStore().findBy( function(rec) {
								if ( rec.get('TimetableMedService_id') == record.get('TimetableMedService_id') ) {
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
     * Принять пациента - открыть ЭМК
     */
    reception: function() {
        var grid = this.GridPanel.getGrid();
        var record = grid.getSelectionModel().getSelected();
        if ( !record || !record.get('EvnUslugaPar_id') ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zayavka']);
            return false;
        }
        var params = {
            Person_id: record.get('Person_id'),
            Server_id: record.get('Server_id'),
            PersonEvn_id: record.get('PersonEvn_id')
        };
        this._openEmk(record, params);
        return true;
    },
    doReset: function()
    {
        this.FilterPanel.getForm().reset();
        this.searchParams = { MedService_id: this.userMedStaffFact.MedService_id, 'wnd_id': this.id }; // для фильтрации направлений по службе
    },
	doSearch: function(mode){
		
		var params = Ext.apply(this.FilterPanel.getForm().getValues(), this.searchParams || {});
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			}
			else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		var usluga_grid = this.UslugaPanel.getGrid();
        var record = usluga_grid.getSelectionModel().getSelected();
        if ( !record || !record.get('UslugaComplex_id') ) {
            return false;
        }	
		params.UslugaComplex_id = record.get('UslugaComplex_id');
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.hours = this.comboHourSelect.getValue();
		params.diffDiagView = this.diffDiagView.getValue();
		params.dispatchCallPmUser_id = (this.dispatchCallSelect.getValue()==0)?0:this.dispatchCallSelect.getValue();
		params.EmergencyTeam_id = (this.emergencyTeamCombo.getValue()==0)?0:this.emergencyTeamCombo.getValue();
		params.LpuBuilding_id = (this.LpuBuildingSelect.getValue()==0)?0:this.LpuBuildingSelect.getValue();
		this.GridPanel.removeAll({clearAll:true});
		this.GridPanel.loadData({globalFilters: params});
		this.emergencyTeamCombo.store.load({
			params: {
				begDate: params.begDate,
				endDate: params.endDate,
				LpuBuilding_id: params.LpuBuilding_id
			}
		})
	},

    show: function() {
		sw.Promed.swWorkPlaceConsultPriemWindow.superclass.show.apply(this, arguments);

		if ( arguments[0] && arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType) {
			arguments[0] = arguments[0].userMedStaffFact;
		}
		
        if ( !arguments[0] || !arguments[0].MedService_id ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_identifikator_slujbyi'], function() {
                this.hide();
            }.createDelegate(this));
            return false;
        }

        if ( !arguments[0].LpuUnitType_SysNick ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_tip_otdeleniya_slujbyi'], function() {
                this.hide();
            }.createDelegate(this));
            return false;
        }

        this.userMedStaffFact = arguments[0];
        this.isPolka = (this.userMedStaffFact.LpuUnitType_SysNick.inlist(['polka', 'ccenter', 'traumcenter', 'fap','stac']));

		//Подгружаем данные службы, нужные для записи
		var ms_combo = this.FilterPanel.getForm().findField('MedService_id');
		this.msData = {};
		ms_combo.getStore().load({
			params: { MedService_id: this.MedService_id },
			callback: function(r,o,s) {
				if(r.length > 0) {
					this.msData = r[0].data;
				}
			}.createDelegate(this)
		});
		
		var params = {
			MedService_id: this.userMedStaffFact.MedService_id,
			level: 0,
			begDate: Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y'),
			endDate: Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y')
		};
		var usluga_grid = this.UslugaPanel.getGrid();
		usluga_grid.getStore().removeAll();
		usluga_grid.getStore().load({
			params: params
		});
		usluga_grid.getStore().baseParams = params;

		//this.GridPanel.setActionDisabled('action_add', !this.isPolka);
		//this.GridPanel.setActionHidden('action_add', !this.isPolka);
		this.GridPanel.setActionDisabled('action_add', true);
		this.GridPanel.setActionHidden('action_add', true);
		this.GridPanel.setActionDisabled('action_dir', !this.isPolka);
		this.GridPanel.setActionDisabled('action_withoutdir', !this.isPolka);

        this.doReset();
        //this.doSearch('day');
        return true;
    },
    initComponent: function() {
        var form = this;

        this.buttonPanelActions = {
			action_Report: { //http://redmine.swan.perm.ru/issues/18509
				nn: 'action_Report',
					tooltip: lang['prosmotr_otchetov'],
					text: lang['prosmotr_otchetov'],
					iconCls: 'report32',
					//hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
					handler: function() {
						var ARMType = '';
						if(Ext.isEmpty(form.ARMType))
						{
							if(form.userMedStaffFact && form.userMedStaffFact.ARMType)
								ARMType = form.userMedStaffFact.ARMType;
						}
						else
							ARMType = form.ARMType;
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show({ARMType:ARMType});
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show({ARMType:ARMType});
								}
							});
					}
				}
			},
            action_Timetable: {
                nn: 'action_Timetable',
                tooltip: lang['rabota_s_raspisaniem'],
                text: lang['raspisanie'],
                iconCls: 'mp-timetable32',
                handler: function() {
                    getWnd('swTTMSScheduleEditWindow').show({
                        MedService_id: form.userMedStaffFact.MedService_id,
                        MedService_Name: form.userMedStaffFact.MedService_Name,
                        userClearTimeMS: null
                    });
                }
            },
            action_JourNotice: {
                handler: function() {
                    getWnd('swMessagesViewWindow').show();
                },
                iconCls: 'notice32',
                nn: 'action_JourNotice',
                text: lang['jurnal_uvedomleniy'],
                tooltip: lang['jurnal_uvedomleniy']
            }
        };

        this.onKeyDown = function (inp, e) {
            if (e.getKey() == Ext.EventObject.ENTER) {
                e.stopEvent();
                this.doSearch();
            }
        }.createDelegate(this);

        this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
            owner: form,
            filter: {
                title: lang['filtr'],
                layout: 'form',
                items: [{
                    layout: 'column',
                    items: [{
						layout: 'form',
						labelWidth: 65,
						items:
						[{
							xtype: 'swmedserviceglobalcombo',
							hidden: true,
							disabled: true,
							hideLabel: true
						}]
					},{
                        layout: 'form',
                        labelWidth: 65,
                        items:
                            [{
                                xtype: 'textfieldpmw',
                                width: 150,
                                name: 'Person_SurName',
                                fieldLabel: lang['familiya'],
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
                        layout: 'form',
                        labelWidth: 45,
                        items:
                            [{
                                xtype: 'textfieldpmw',
                                width: 150,
                                name: 'Person_FirName',
                                fieldLabel: lang['imya'],
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
                        layout: 'form',
                        labelWidth: 75,
                        items:
                            [{
                                xtype: 'textfieldpmw',
                                width: 150,
                                name: 'Person_SecName',
                                fieldLabel: lang['otchestvo'],
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
                        layout: 'form',
                        labelWidth: 35,
                        items:
                            [{
                                xtype:'swdatefield',
                                format:'d.m.Y',
                                plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                name: 'Person_BirthDay',
                                fieldLabel: lang['dr'],
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }, {
                        layout: 'form',
                        labelWidth: 145,
                        items:
                            [{
                                xtype: 'textfield',
                                width: 100,
                                name: 'EvnDirection_Num',
                                fieldLabel: lang['nomer_napravleniya'],
                                listeners: {
                                    'keydown': form.onKeyDown
                                }
                            }]
                    }]
                }, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items:
                            [{
                                xtype: 'button',
                                id: form.id+'BtnSearch',
                                text: lang['nayti'],
                                iconCls: 'search16',
                                handler: function()
                                {
                                    form.doSearch();
                                }
                            }]
                    }, {
                        layout: 'form',
                        items:
                            [{
                                style: "padding-left: 10px",
                                xtype: 'button',
                                id: form.id+'BtnClear',
                                text: lang['sbros'],
                                iconCls: 'reset16',
                                handler: function()
                                {
                                    form.doReset();
                                    form.doSearch('day');
                                }
                            }]
                    }]
                }]
            }
        });

        this.UslugaPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', disabled: true },
				{ name: 'action_view', disabled: true },
				{ name: 'action_delete', disabled: true },
				{ name: 'action_refresh', disabled: true }
			],
			autoLoadData: false,
            autoExpandColumn: 'autoexpand',
			dataUrl: '/?c=Usluga&m=loadUslugaComplexMedServiceList',
			width: 400,
			minSize: 150,
			id: form.id + 'UslugaPanel',
			object: 'UslugaComplex',
			split: true,
            onRowSelect: function(sm, idx, record){
				form.doSearch('day');
			},
			region: 'west',
			toolbar: false,
			stringfields: [
				{ name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplex_Name', header: lang['uslugi'], width: 150, id: 'autoexpand' }
			]
		});

        this.GridPanel = new sw.Promed.ViewFrame({
            id: 'WorkPlaceConsultGridPanel',
            object: 'EvnUslugaPar',
            region: 'center',
            autoExpandColumn: 'autoexpand',
            grouping: true,
            groupTextTpl: '{[values.text == "(Пусто)" ? "Очередь" : values.text]} {[sw.Promed.swWorkPlaceConsultPriemWindow.cntOrders(values) == 0 ? "" : "(" + sw.Promed.swWorkPlaceConsultPriemWindow.cntOrders(values) + " " + (parseInt(sw.Promed.swWorkPlaceConsultPriemWindow.cntOrders(values).toString().charAt(sw.Promed.swWorkPlaceConsultPriemWindow.cntOrders(values).toString().length-1)).inlist([1]) ?"заявка" :(parseInt(sw.Promed.swWorkPlaceConsultPriemWindow.cntOrders(values).toString().charAt(sw.Promed.swWorkPlaceConsultPriemWindow.cntOrders(values).toString().length-1)).inlist([2,3,4]) ? "заявки" : "заявок")) + ")" ]}',
            groupingView: {
                showGroupName: false,
                showGroupsText: true
            },
            actions: [
                { name: 'action_view', hidden: true, disabled: true },
                { name: 'action_edit', text:lang['otkryit_emk'], tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'], iconCls: 'open16', handler: this.reception.createDelegate(this) },
                { name: 'action_add', hidden: true, disabled: true, text: lang['prinyat_bez_zapisi'], tooltip: lang['prinyat_patsienta_bez_zapisi'] },
                { name: 'action_delete', text:lang['otklonit'], tooltip: lang['otklonit'], handler: this.cancelEvnDirection.createDelegate(this) },
                { name: 'action_refresh' },
                { name: 'action_print' }
            ],
            autoLoadData: false,
            pageSize: 20,
            stringfields: [
                { name: 'item_key', type: 'string', header: 'ID', key: true },
                { name: 'EvnUslugaPar_id', type: 'int', hidden: true }, // заказ услуги
                { name: 'EvnDirection_id', type: 'int', hidden: true },
                { name: 'Diag_id', type: 'int', hidden: true },
                { name: 'EvnStatus_id', type: 'int', hidden: true },
                { name: 'EvnQueue_id', type: 'int', hidden: true },
                { name: 'TimetableMedService_id', type: 'int', hidden: true },
				{ name: 'UslugaComplexRecord_id', type: 'int', hidden: true },
                { name: 'TimetableMedService_begDate', type: 'date', hidden: false, group: true, sort: true, direction: [
					{field: 'TimetableMedService_begDate', direction:'DESC'},
					{field: 'TimetableMedService_begTime', direction:'ASC'}
				]},
                { name: 'Person_id', type: 'int', hidden: true },
                { name: 'PersonEvn_id', type: 'int', hidden: true },
                { name: 'Server_id', type: 'int', hidden: true },
                { name: 'UslugaComplex_id', type: 'int', hidden: true },
                { name: 'EvnPrescr_id', type: 'int', hidden: true },
                { name: 'EvnPrescr_IsExec', type: 'int', hidden: true },
                { name: 'PrescriptionStatusType_id', type: 'int', hidden: true },
                { name: 'PrescriptionType_id', type: 'int', hidden: true },
                { name: 'EvnPrescrParentEvn_id', type: 'int', hidden: true },
                { name: 'EvnPrescrParentEvn_rid', type: 'int', hidden: true },
                { name: 'EvnPrescrParentEvnClass_SysNick', type: 'string', hidden: true },
                { name: 'Lpu_Name', type: 'string', hidden: true }, // ЛПУ откуда направлен
                { name: 'LpuSection_Name', type: 'string', hidden: true }, // Отделение откуда направлен
                { name: 'EvnUslugaPar_isCito', header: 'Cito!', type: 'checkbox', width: 40}, // срочность заказа услуги
                { name: 'Usluga_IsHasReception', header: 'Приём', type: 'checkbox', width: 60 }, // признак, что заказ услуги выполнен, назначение исполнено
                { name: 'EvnDirection_setDT', dateFormat: 'd.m.Y', type: 'date', header: lang['data_napravleniya'], width: 120 },
                { name: 'TimetableMedService_begTime', type: 'string', header: lang['zapis'], width: 120/*, sort: true, direction: 'ASC'*/},
				{ name: 'TimetableMedServiceType', type: 'string', header: lang['raspisanie'], width: 120 },
                { name: 'EvnDirection_Num', header: lang['napravlenie'], width: 120 },
                { name: 'EvnDirection_From', header: 'Кем направлен', width: 180 },
                { name: 'Person_FIO', header: lang['fio_patsienta'], type: 'string', width: 320 },
                { name: 'UslugaComplex_Name', header: lang['usluga'], type: 'string', width: 420, id: 'autoexpand' },
                { name: 'MedPersonal_FIO', header: lang['vrach'], type: 'string', width: 200 },
                { name: 'Diag_Name', header: lang['diagnoz'], type: 'string', width: 150 },
				{ name: 'UslugaComplexMedService_id', type: 'int', hidden: true },
				{ name: 'PersonQuarantine_IsOn', type: 'boolean'}
            ],
            dataUrl: '/?c=EvnUslugaOrder&m=loadList',
            totalProperty: 'totalCount',
			queueIsLoaded: false,
            interceptMouse: function(e){
				var gv = this,
					grid = form.GridPanel.getGrid(),
					store = grid.getStore(),
					hd = e.getTarget('.x-grid-group-hd', this.mainBody),
					params = Ext.apply({loadQueue: 1}, store.lastOptions.params);
                if(hd) {
                    e.stopEvent();
					if (hd.innerHTML.indexOf('Очередь') != -1 && grid.queueIsLoaded == false) {
						form.getLoadMask("Загрузка...").show();
						Ext.Ajax.request({
							url: '/?c=EvnUslugaOrder&m=loadList',
							params: params,
							callback: function(opt, success, response) {
								form.getLoadMask().hide();
								if (success && response.responseText != '') {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									//чтобы правильно отображалось кол-во записей в гриде:
									var i = store.findBy(function(rec) {return rec.get('EvnQueue_id')==-1});
									if(i>=0) store.removeAt(i);
									if(response_obj[response_obj.length-1]['EvnQueue_id']==-1) {
										response_obj.pop();
									}
									store.loadData(response_obj, true);
									grid.queueIsLoaded = true;
									if( Ext.get(grid.getView().getGroupId(lang['pusto'])) != null ) {
										grid.getView().toggleGroup(grid.getView().getGroupId(lang['pusto']), true);
									}
								}
							}
						});
					} else {
						gv.toggleGroup(hd.parentNode);
					}
                }
            },
            onLoadData: function(sm, index, records) {
				var grid = this.GridPanel.getGrid(),
					store = grid.getStore();
				if (!store.totalLength) {
					store.removeAll();
				}
				if( Ext.get(grid.getView().getGroupId(lang['pusto'])) != null ) {
					grid.getView().toggleGroup(grid.getView().getGroupId(lang['pusto']), false);
				}
				grid.queueIsLoaded = false;
				var opts = getGlobalOptions();
				var params = {};
				store.each(function(rec,idx,count) {
					var dirHref;
					if (dirHref = rec.get('EvnDirection_Num')) {
						if (!Ext.isEmpty(rec.get('EvnDirection_id'))) {
							dirHref = '<a href="javascript://" onClick="Ext.getCmp(\''+this.id+'\').openEvnDirectionEditWindow()">'+rec.get('EvnDirection_Num')+'</a>';								
						}
						rec.set('EvnDirection_Num',dirHref);
						rec.commit();
					}
				}.createDelegate(this));
            }.createDelegate(this),
            onRowSelect: function(sm, idx, record){
				this.getAction('action_dir').setDisabled( !record || record.get('EvnUslugaPar_id') || Ext.isEmpty(record.get('TimetableMedService_id') ) || !form.isPolka );
				this.getAction('action_dir_from_queue').setDisabled( !record || !record.get('EvnQueue_id') || "true"==record.get('Usluga_IsHasReception') || !Ext.isEmpty(record.get('TimetableMedService_id')) );				
				this.getAction('action_to_queue').setDisabled( !record || Ext.isEmpty(record.get('TimetableMedService_id')) || !record.get('EvnUslugaPar_id') || record.get('EvnStatus_id') == 15 );				
                this.setActionDisabled('action_edit', (!record || !record.get('EvnUslugaPar_id') || !record.get('Person_id')));
                this.setActionDisabled('action_delete', (!record || !record.get('EvnDirection_id') || "true"==record.get('Usluga_IsHasReception')));
            },/*
            onDblClick: function(grid, number, event){
                log(['onDblClick', event]);
                this.onEnter();
            },*/
            onEnter: function() {
                //
            }
        });

        this.GridPanel.getGrid().getView().getRowClass = function (row, index) {
            var cls = '';
            if (row.get('EvnQueue_id') == -1) {
                cls = 'x-hide-display';
            }
			if (row.get('PersonQuarantine_IsOn')) {
				cls = cls + 'x-grid-rowbackred ';
			}
            return cls;
        };
		
        this.MainPanel = new Ext.Panel({
            region: 'center',
			border: true,
			layout: 'border',
            title: lang['jurnal_rabochego_mesta'],
			items: [
				this.UslugaPanel,
				this.GridPanel
			]
		});
		
		this.LeftPanel = new sw.Promed.BaseWorkPlaceButtonsPanel({
			animCollapse: false,
			width: 60,
			minSize: 60,
			maxSize: 120,
			region: 'west',
			floatable: false,
			collapsible: true,
			id: form.id + '_buttPanel',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					var el = null;
					el = form.findById(form.id + '_buttPanel_slid');
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					
					return;
				}
				
			},
			border: false,
			title: ' ',
			titleCollapse: true,
			hidden: !form.showLeftMenu,
			enableDefaultActions: (typeof form.enableDefaultActions == 'boolean')?form.enableDefaultActions:true,
			panelActions: form.buttonPanelActions
		});

		this.CenterPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [
				this.LeftPanel, 
				this.MainPanel
			]
		});
		
        sw.Promed.swWorkPlaceConsultPriemWindow.superclass.initComponent.apply(this, arguments);
		
		this.GridPanel.ViewToolbar.on('render', function(vt){
			this.ViewActions['action_dir'] = new Ext.Action({
                name:'action_dir',
                id: 'WorkPlaceGridPanel_action_dir',
                handler: function() {
                    form.recordPerson();
                },
                text:lang['zapisat'],
                tooltip: lang['zapisat'],
                iconCls : 'add16'
            });
			this.ViewActions['action_dir_from_queue'] = new Ext.Action({
                name:'action_dir_from_queue',
                id: 'WorkPlaceGridPanel_action_dir_from_queue',
                handler: function() {
                    form.recordPersonFromQueue();
                },
                text:lang['zapisat_iz_ocheredi'],
                tooltip: lang['zapisat_iz_ocheredi'],
                iconCls : 'add16'
            });
			this.ViewActions['action_withoutdir'] = new Ext.Action({
                name:'action_withoutdir',
                id: 'WorkPlaceGridPanel_action_withoutdir',
                handler: function() {
                    form.acceptWithoutRecord();
                },
                text:lang['prinyat_bez_zapisi'],
                tooltip: lang['prinyat_bez_zapisi'],
                iconCls : 'copy16'
            });
			this.ViewActions['action_to_queue'] = new Ext.Action({
                name:'action_to_queue',
                id: 'WorkPlaceGridPanel_action_to_queue',
                handler: function() {
                    form.returnToQueue();
                },
                text:lang['ubrat_v_ochered'],
                tooltip: lang['ubrat_v_ochered'],
                iconCls : 'delete16'
            });
            vt.insertButton(1,this.ViewActions['action_dir']);
            vt.insertButton(2,this.ViewActions['action_dir_from_queue']);
            vt.insertButton(3,this.ViewActions['action_withoutdir']);
            vt.insertButton(4,this.ViewActions['action_to_queue']);
            return true;
        }, this.GridPanel);
	}
});

sw.Promed.swWorkPlaceConsultPriemWindow.cntOrders = function(values) {
    var cnt = 0;
    for (var i=0; i < values.rs.length; i++) {
        if (values.rs[i].data.EvnUslugaPar_id > 0) {
            cnt++;
        }
    }
    return cnt;
};