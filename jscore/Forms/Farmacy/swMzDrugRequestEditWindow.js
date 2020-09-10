/**
* swMzDrugRequestEditWindow - окно редактирования заявки врача
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Rustam Salakhov
* @version      07.2015
* @comment      
*/
sw.Promed.swMzDrugRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['zayavka_redaktirovanie'],
	layout: 'border',
	id: 'MzDrugRequestEditWindow',
	modal: true,
	shim: false,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
    isCommonPersonRegisterType: function(type_id) { //функция для определеия, является ли текущий регистр заявки общетерапевтическим
        var common_type_array = [1]; //для определения используются фиксированные идентификаторы, желательно в будущем переделать на ники
        if (type_id === undefined) {
            type_id = this.PersonRegisterType_id;
        }
        /*if(getRegionNick() == 'ufa') {
            common_type_array = [1,6,21,22,49,52,53,55,56];
        }*/
        return !Ext.isEmpty(type_id) && type_id.inlist(common_type_array);
    },
    isMainSpecRequest: function() { // возвращает ответ на вопрос "является ли заявка, заявкой главного внештатного специалиста"
        var user_mp_id = !Ext.isEmpty(getGlobalOptions().medpersonal_id) ? getGlobalOptions().medpersonal_id : null;
        var is_main_spec_request = (this.ARMType == 'mzchieffreelancer' &&  this.MedPersonal_id == user_mp_id && this.MedPersonal_isMainSpec); //признак заявки главного специалиста; mzchieffreelancer - АРМ главного внештатного специалиста.
        return is_main_spec_request
    },
	doDrugGridSearch: function(reset) {
		var wnd = this;
		var form = wnd.DrugFilterPanel.getForm();

		if (reset) {
			form.reset();
		}

		var params = form.getValues();
		params.DrugRequest_id = wnd.DrugRequest_id;
		params.PersonRegisterType_id = wnd.PersonRegisterType_id;
		params.start = 0;
		params.limit = 100;
		params.ShowDeleted = form.findField('ShowDeletedDrugRow').checked ? 1 : 0;
		params.ShowWithoutPerson = form.findField('ShowDrugRowWithoutPerson').checked ? 1 : 0;

		this.DrugGrid.removeAll();
		this.DrugGrid.loadData({
			globalFilters: params
		});
	},
	doDrugListGridSearch: function(reset) {
		var wnd = this;
		var form = wnd.DrugListFilterPanel.getForm();

		if (reset) {
			form.reset();
		}

		var params = form.getValues();
		params.DrugRequest_id = wnd.DrugRequest_id;
		params.start = 0;
		params.limit = 100;

		this.DrugListGrid.removeAll();
		this.DrugListGrid.loadData({
			globalFilters: params
		});
	},
	doPersonGridSearch: function(reset) {
		var wnd = this;
		var form = wnd.PersonFilterPanel.getForm();

		if (reset) {
			form.reset();
		}

		var params = form.getValues();
		params.DrugRequest_id = wnd.DrugRequest_id;
		params.start = 0;
		params.limit = 100;
        params.ShowPersonOnlyWthoutDrug = form.findField('ShowPersonOnlyWthoutDrug').checked ? 1 : 0;

		this.PersonGrid.removeAll();
		this.PersonGrid.loadData({
			globalFilters: params
		});
	},
	doFirstCopyGridSearch: function(reset) {
		var wnd = this;
		var form = wnd.FirstCopyFilterPanel.getForm();

		if (reset) {
			form.reset();
		}

		var params = form.getValues();
		params.DrugRequest_id = wnd.DrugRequest_id;
		params.DrugRequestFirstCopy_id = wnd.DrugRequestFirstCopy_id;
		params.start = 0;
		params.limit = 100;
        params.ShowPersonOnlyWthoutDrug = form.findField('ShowPersonOnlyWthoutDrug').checked ? 1 : 0;

        if (!Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) {
            this.FirstCopyGrid.removeAll();
            this.FirstCopyGrid.loadData({
                globalFilters: params
            });
        }
	},
	setDrugRequestStatus: function(status_code) {
		var wnd = this;
        if (status_code !== undefined) {
            wnd.DrugRequestStatus_Code = status_code;
        }

        //передаем статус заявки на формы редактирования гридов
        this.DrugPersonGrid.setParam('DrugRequestStatus_Code', this.DrugRequestStatus_Code, false);
        this.PersonDrugGrid.setParam('DrugRequestStatus_Code', this.DrugRequestStatus_Code, false);

        //настраиваем видимость кнопки на информационной панели
        this.DrugRequestInformationPanel.showToolbar(status_code == 1 && wnd.LpuRegion_id > 0  && wnd.Lpu_id == getGlobalOptions().lpu_id); //1 - Начальная

		switch(wnd.DrugRequestStatus_Code) {
			case 1: //Начальная
                if (wnd.isMainSpecRequest()) {
                    wnd.buttons[0].setText(lang['utverdit']);
                } else {
				wnd.buttons[0].setText(lang['sformirovat']);
                }
				break;
			case 2: //Сформированная
				wnd.buttons[0].setText(lang['redaktirovat']);
				break;
			case 3: //Утвержденная
                if (wnd.isMainSpecRequest()) {
                    wnd.buttons[0].setText(lang['redaktirovat']);
                } else {
                    wnd.buttons[0].setText(lang['sformirovat']);
                    wnd.buttons[0].disable();
                }
				break;
			default:
				wnd.buttons[0].setText(lang['sformirovat']);
				wnd.buttons[0].disable();
				break;
		}
	},
	changeDrugRequestStatus: function() {
		var wnd = this;
		var new_status_code = null;
		var new_status_code_name = null;
        var question_msg = '';

		switch(wnd.DrugRequestStatus_Code) {
			case 1: //Начальная
				new_status_code = wnd.isMainSpecRequest() ? 3 : 2;

                if (new_status_code == 3) {
                    new_status_code_name = lang['utverzhdennaia'];
                    question_msg = lang['zayavka_so_statusom_utverzhdennaia_dostupna_tolko_dlya_prosmotra_izmenit_status_zayavki_na_utverzhdennaia'];
                } else {
				new_status_code_name = lang['sformirovannaya'];
                    question_msg = lang['zayavka_so_statusom_sformirovannaya_dostupna_tolko_dlya_prosmotra_izmenit_status_zayavki_na_sformirovannaya'];
                }
				break;
			case 2: //Сформированная
			case 3: //Утвержденная
                if (wnd.DrugRequestStatus_Code == 2 || wnd.isMainSpecRequest()) {
				new_status_code = 1;
				new_status_code_name = lang['nachalnaya'];
                }
				break;
			default:
				break;
		}

		if (new_status_code > 0) {
			if (!Ext.isEmpty(question_msg)) { //если есть вопрос для пользователя - задаем его
				sw.swMsg.show({
					icon: Ext.MessageBox.QUESTION,
					msg: question_msg,
					title: lang['vopros'],
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							wnd.saveDrugRequestStatus(new_status_code, function() {
								wnd.setDrugRequestStatus(new_status_code);
								if (!Ext.isEmpty(new_status_code_name)) {
									wnd.DrugRequestInformationPanel.setData('request_status' , new_status_code_name);
									wnd.DrugRequestInformationPanel.showData();
								}
								wnd.setDisabled();
							});
						}
					}
				});
			} else {
				wnd.saveDrugRequestStatus(new_status_code, function() {
					wnd.setDrugRequestStatus(new_status_code);
					if (!Ext.isEmpty(new_status_code_name)) {
						wnd.DrugRequestInformationPanel.setData('request_status' , new_status_code_name);
						wnd.DrugRequestInformationPanel.showData();
					}
					wnd.setDisabled();
				});
			}
		} else {
            sw.swMsg.alert(lang['oshibka'], lang['smena_statusa_zayavki_nevozmojna']);
		}
	},
	saveDrugRequestStatus: function(status_code, callback) {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg:lang['zagruzka']});

		//loadMask.show();
		Ext.Ajax.request({
			params: {
				DrugRequest_id: wnd.DrugRequest_id,
				DrugRequestStatus_Code: status_code
			},
			callback: function (options, success, response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if (result && result.success) {
                    if (callback && typeof callback == 'function') {
                        callback();
                    }
                    if (wnd.owner && 'refreshRecords' in wnd.owner) {
                        wnd.owner.refreshRecords(null,0);
                    }
                    //loadMask.hide();
                } else if (result.Error_Type && result.Error_Data) {
                	getWnd('swMzDrugRequestExtendedErrorViewWindow').show(result);
				}
				if (!Ext.isEmpty(result.CheckWarning_Msg)) {
                    sw.swMsg.show({
                        title : langs('Предупреждение'),
                        msg : result.CheckWarning_Msg,
                        buttons: sw.swMsg.OK,
                        icon: sw.swMsg.WARNING
                    });
                }
			},
			url:'/?c=MzDrugRequest&m=saveDrugRequestStatus'
		});
	},
	onDrugRequestTabsChange: function(panel, tab) {
        var wnd = this;

		switch(tab.id) {
			case 'mdre_drug':
				if (this.DrugGrid.DataState == 'empty') {
					this.doDrugGridSearch();
				}
				if (this.DrugGrid.DataState == 'outdated') {
					this.DrugGrid.refreshRecords(null,0);
				}
				if (this.DrugPersonGrid.DataState == 'outdated' && !this.DrugPersonPanel.hidden) {
					this.DrugPersonGrid.refreshRecords(null,0);
					this.refreshDrugPersonInformationPanelData();
				}
				break;
			case 'mdre_drug_list':
				if (this.DrugListGrid.DataState == 'empty') {
					this.doDrugListGridSearch();
				}
				if (this.DrugListGrid.DataState == 'outdated') {
					this.DrugListGrid.refreshRecords(null,0);
				}
				break;
			case 'mdre_person':
				if (this.PersonGrid.DataState == 'empty') {
					this.doPersonGridSearch();
				}
				if (this.PersonGrid.DataState == 'outdated') {
					this.PersonGrid.refreshRecords(null,0);
				}
				if (this.PersonDrugGrid.DataState == 'outdated') {
					this.PersonDrugGrid.refreshRecords(null,0);
				}
				break;
			case 'mdre_first_copy':
				if (this.FirstCopyGrid.DataState == 'empty') {
					this.doFirstCopyGridSearch();
		}
				if (this.FirstCopyGrid.DataState == 'outdated' && !Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) {
					this.FirstCopyGrid.refreshRecords(null,0);
				}
				if (this.FirstCopyDrugGrid.DataState == 'outdated' && !Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) {
					this.FirstCopyDrugGrid.refreshRecords(null,0);
				}
				break;
		}
	},
	setDrugPersonInformationPanelData: function(record) {
		var wnd = this;

		this.DrugPersonInformationPanel.clearData();
		if (record) {
			Ext.Ajax.request({
				params: {
					DrugRequestRow_id: record.get('DrugRequestRow_id')
				},
				callback: function (options, success, response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && result.DrugRequestRow_Kolvo) {
						var total_count = result.DrugRequestRow_Kolvo*1 > 0 ? result.DrugRequestRow_Kolvo*1 : 0;
						var order_count = result.DrugRequestPersonOrder_SumOrdKolvo*1 > 0 ? result.DrugRequestPersonOrder_SumOrdKolvo*1 : 0;
						var reserve_count = total_count - order_count;
						var error_message = reserve_count < 0 ? langs('Резерв по заявке исчерпан. Откорректируйте персональную разнарядку.') : null;

						if (reserve_count < 0) {
							reserve_count = "<font color=#ff0000>" + reserve_count + "</font>";
						}

						wnd.DrugPersonInformationPanel.setData('total_count', total_count);
						wnd.DrugPersonInformationPanel.setData('order_count', order_count);
						wnd.DrugPersonInformationPanel.setData('reserve_count', reserve_count);
						wnd.DrugPersonInformationPanel.setData('error_message', error_message);
						wnd.DrugPersonInformationPanel.showData();
					}
				},
				url:'/?c=MzDrugRequest&m=getDrugRequestRowKolvoData'
			});
		} else {
			this.DrugPersonInformationPanel.showData();
		}
	},
	refreshDrugPersonInformationPanelData: function() {
		var selected_record = this.DrugGrid.getGrid().getSelectionModel().getSelected();
		if (selected_record && !Ext.isEmpty(selected_record.get('DrugRequestRow_id'))) {
			this.setDrugPersonInformationPanelData(selected_record);
		}
	},
	setGridDefaultParams: function() {
		var wnd = this;

		this.DrugGrid.removeAll({addEmptyRecord: false});
		this.DrugPersonGrid.removeAll({addEmptyRecord: false});
		this.DrugListGrid.removeAll({addEmptyRecord: false});
		this.PersonGrid.removeAll({addEmptyRecord: false});
		this.PersonDrugGrid.removeAll({addEmptyRecord: false})
		this.FirstCopyGrid.removeAll({addEmptyRecord: false});
		this.FirstCopyDrugGrid.removeAll({addEmptyRecord: false});

		this.DrugGrid.DataState = null;
		this.DrugPersonGrid.DataState = null;
		this.DrugListGrid.DataState = null;
		this.PersonGrid.DataState = 'empty';
		this.PersonDrugGrid.DataState = null;
		this.FirstCopyGrid.DataState = 'empty';
		this.FirstCopyDrugGrid.DataState = null;

		this.MedPersonal_id = null;

		this.DrugGrid.setParam('DrugRequest_id', this.DrugRequest_id, true);
		this.DrugGrid.setParam('start', 0, true);
		this.DrugGrid.setParam('limit', 100, true);
		this.DrugGrid.setParam('DrugRequest_id', this.DrugRequest_id, false);
		this.DrugGrid.setParam('DrugFinance_id', this.DrugFinance_id, false);
		this.DrugGrid.setParam('PersonRegisterType_id', this.PersonRegisterType_id, false);
		this.DrugGrid.setParam('onSave', function() {
            if (!Ext.isEmpty(wnd.DrugListGrid.DataState)) {
                wnd.DrugListGrid.DataState = 'outdated';
            }
		}, false);

		this.DrugPersonGrid.setParam('DrugRequest_id', this.DrugRequest_id, true);
		this.DrugPersonGrid.setParam('DrugRequest_id', this.DrugRequest_id, false);
		this.DrugPersonGrid.setParam('DrugRequestFirstCopy_id', this.DrugRequestFirstCopy_id, false);
		this.DrugPersonGrid.setParam('MedPersonal_id', this.MedPersonal_id, false);
		this.DrugPersonGrid.setParam('DrugComplexMnn_id', null, false);
		this.DrugPersonGrid.setParam('Tradenames_id', null, false);
		this.DrugPersonGrid.setParam('EvnVK_id', null, false);
        this.DrugPersonGrid.setParam('DrugRequestPeriod_id', this.DrugRequestPeriod_id, false);
		this.DrugPersonGrid.setParam('PersonRegisterType_id', this.PersonRegisterType_id, false);
        this.DrugPersonGrid.setParam('LpuRegion_id', this.LpuRegion_id, false);
        this.DrugPersonGrid.setParam('DrugFinance_id', this.DrugFinance_id, false);
		this.DrugPersonGrid.setParam('onSave', function() {
			wnd.PersonGrid.DataState = 'outdated';
			wnd.FirstCopyGrid.DataState = 'outdated';
			wnd.refreshDrugPersonInformationPanelData();
            if (wnd.DrugRequestStatus_Code == '1' && Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) { //1 - Начальная
                wnd.DrugGrid.refreshRecords(null,0);
                wnd.DrugListGrid.DataState = 'outdated';
            }
		}, false);

		this.DrugListGrid.setParam('DrugRequest_id', this.DrugRequest_id, true);
		this.DrugListGrid.setParam('start', 0, true);
		this.DrugListGrid.setParam('limit', 100, true);

		this.PersonGrid.setParam('DrugRequest_id', this.DrugRequest_id, true);
		this.PersonGrid.setParam('start', 0, true);
		this.PersonGrid.setParam('limit', 100, true);
		this.PersonGrid.setParam('DrugRequest_id', this.DrugRequest_id, false);
		this.PersonGrid.setParam('searchMode', null, false);
		this.PersonGrid.setParam('DrugRequestPeriod_id', this.DrugRequestPeriod_id, false);
		this.PersonGrid.setParam('PersonRegisterType_id', this.PersonRegisterType_id > 0 ? this.PersonRegisterType_id : null, false);
		this.PersonGrid.setParam('searchMode', this.DrugRequestCategory_SysNick == 'vrach' && !wnd.isMainSpecRequest() ?  'att_1_4' : 'all', false); //att_1_4 - только прикрепленные (основное или служебное прикрепление)
		this.PersonGrid.setParam('searchList', getRegionNick() == 'ufa' ? 1: 0, false); 
        this.PersonGrid.setParam('LpuRegion_id', this.LpuRegion_id, false);
        this.PersonGrid.setParam('PersonRefuse_IsRefuse', 1, false); //отказ от льготы: нет
		this.PersonGrid.setParam('onSelect', function(person_data) {
            if (!Ext.isEmpty(person_data.Person_id)) {
                if (!Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) {
                    wnd.checkExistPersonInFirstCopy(person_data.Person_id, function(check_data) {
                        if (check_data && check_data.drpo_cnt != undefined) {
                            if (check_data.drpo_cnt == 0) {
                                wnd.askConfirm('Пациент был не учтен при сборе данных о реальной потребности. Включить пациента в данные о реальной потребности без возможности удаления?', function() {wnd.addPersonGridRecord(person_data);})
                            } else {
			wnd.addPersonGridRecord(person_data);
                            }
                        } else {
                            sw.swMsg.alert(langs('Ошибка'), langs('При проверке данных о реальной потребности произошла ошибка'));
                        }
                    });
                } else {
                    wnd.addPersonGridRecord(person_data);
                }
            }
		}, false);
		this.PersonGrid.setParam('onSelectList', function(persons) {
            if (!Ext.isEmpty(persons)) {
				wnd.addPersonGridList(persons);
            }
		}, false);
        this.FirstCopyGrid.setParam('DrugRequest_id', this.DrugRequest_id, true);
        this.FirstCopyGrid.setParam('DrugRequestFirstCopy_id', this.DrugRequestFirstCopy_id, true);

		this.PersonDrugGrid.setParam('DrugRequest_id', this.DrugRequest_id, true);
		this.PersonDrugGrid.setParam('DrugRequest_id', this.DrugRequest_id, false);
        this.PersonDrugGrid.setParam('DrugRequestFirstCopy_id', this.DrugRequestFirstCopy_id, false);
		this.PersonDrugGrid.setParam('MedPersonal_id', this.MedPersonal_id, false);
		this.PersonDrugGrid.setParam('PersonRegisterType_id', this.PersonRegisterType_id, false);
		this.PersonDrugGrid.setParam('DrugFinance_id', this.DrugFinance_id, false);
		this.PersonDrugGrid.setParam('Person_id', null, false);
		this.PersonDrugGrid.setParam('onSave', function() {
			wnd.DrugPersonGrid.DataState = 'outdated';
			wnd.FirstCopyDrugGrid.DataState = 'outdated';
            if (wnd.DrugRequestStatus_Code == '1' && Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) { //1 - Начальная
                wnd.DrugGrid.DataState = 'outdated';
                wnd.DrugListGrid.DataState = 'outdated';
            }
		}, false);

        this.FirstCopyDrugGrid.setParam('DrugRequest_id', this.DrugRequest_id, true);
        this.FirstCopyDrugGrid.setParam('DrugRequestFirstCopy_id', this.DrugRequestFirstCopy_id, true);
	},
	addDrugPersonGridRecord: function() {
		var wnd = this;
		var viewframe = this.DrugPersonGrid;

		getWnd(getRegionNick() != 'ufa' ? 'swPersonSearchWindow' : 'swPersonSearchWindow4DrugRequest').show({
			searchMode: null,
			DrugRequestPeriod_id: wnd.DrugRequestPeriod_id,
			PersonRegisterType_id: wnd.PersonRegisterType_id > 0 ? wnd.PersonRegisterType_id : null,
            LpuRegion_id: wnd.LpuRegion_id,
            searchMode: wnd.DrugRequestCategory_SysNick == 'vrach' ?  'att_1_4' : 'all', //att_1_4 - только прикрепленные (основное или служебное прикрепление)
			PersonRefuse_IsRefuse: 1, //отказ от льготы: нет
			onSelect: function(person_data) {
				viewframe.setParam('Person_id', person_data.Person_id, false);
				viewframe.editRecord('add');
				delete viewframe.params.Person_id;
				this.hide();
			}
		});
	},
	addPersonGridRecord: function(person_data) {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg:lang['zagruzka']});
        var params = new Object();

        params.DrugRequest_id = wnd.DrugRequest_id;
        params.Person_id = person_data.Person_id;
        params.MedPersonal_id = getGlobalOptions().medpersonal_id > 0 ? getGlobalOptions().medpersonal_id : wnd.MedPersonal_id;
        if (!Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) {
            params.DrugRequestFirstCopy_id = wnd.DrugRequestFirstCopy_id;
        }

		//loadMask.show();
		Ext.Ajax.request({
			params: params,
			callback: function (options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result.success) {
					wnd.doPersonGridSearch();
                    wnd.FirstCopyGrid.DataState = 'outdated';
				}
				//loadMask.hide();
			},
			url:'/?c=MzDrugRequest&m=saveDrugRequestPersonOrder'
		});
	},
	addPersonGridList: function(persons) {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg:langs('Загрузка...')});
        var params = new Object();

        params.DrugRequest_id = wnd.DrugRequest_id;
        params.Persons = persons;
        params.MedPersonal_id = getGlobalOptions().medpersonal_id > 0 ? getGlobalOptions().medpersonal_id : wnd.MedPersonal_id;

		loadMask.show();
		Ext.Ajax.request({
			params: params,
			callback: function (options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result.success) {
					//log('kol = ', result.kol);
					var $str = 'Успешно добавлено записей ' + result.Kol + ' из ' + result.KolAll;
					 sw.swMsg.alert('Добавление записей завершено', $str);
					wnd.doPersonGridSearch();
                    wnd.FirstCopyGrid.DataState = 'outdated';
				}
				loadMask.hide();
			},
			url:'/?c=MzDrugRequest&m=saveDrugRequestPersonOrderList'
		});
	},
    checkExistPersonInFirstCopy: function(person_id, callback) {
        Ext.Ajax.request({
            url: '/?c=MzDrugRequest&m=checkExistPersonInFirstCopy',
            params: {
                DrugRequestFirstCopy_id: this.DrugRequestFirstCopy_id,
                Person_id: person_id
            },
            callback: function(options, success, response) {
                if (success) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    callback(result);
                }
            }
        });
    },
	setDisabled: function(disable) {
		var wnd = this;
		var st_code = wnd.DrugRequestStatus_Code;

		if (disable == undefined) {
			disable = (wnd.action != 'edit');
		}

		wnd.DrugGrid.setReadOnly(disable || st_code != 1);
		wnd.DrugPersonGrid.setReadOnly(disable);
		wnd.DrugListGrid.setReadOnly(disable || st_code != 1);
		wnd.PersonGrid.setReadOnly(disable);
		wnd.PersonDrugGrid.setReadOnly(disable);

        wnd.DrugGrid.setActionDisabled('action_mdre_dg_actions', wnd.DrugGrid.readOnly);
        wnd.PersonGrid.setActionDisabled('action_mdre_pg_actions', wnd.PersonGrid.readOnly);

		if (disable) {
			wnd.buttons[0].disable();
		} else {
			wnd.buttons[0].enable();
		}
	},
    setRequestLimit: function(data) {
        var wnd = this;

        wnd.RequestLimitData = new Object({
            limit: 0,
            limit_fed: 0,
            limit_reg: 0,
            kolvo: 0,
            kolvo_fed: 0,
            kolvo_reg: 0,
            r_kolvo: 0,
            r_kolvo_fed: 0,
            r_kolvo_reg: 0,
            count_req: 0,
            count_fed: 0,
            count_reg: 0,
            sum: 0,
            sum_fed: 0,
            sum_reg: 0,
            lrsum: 0,
            lrsum_fed: 0,
            lrsum_reg: 0,
            lrsum_str: '',
            lrsum_fed_str: '',
            lrsum_reg_str: ''
        });

        //расчет лимитов
        if (!Ext.isEmpty(data.DrugRequestPlan_id) || true) {
            //объем финансирования
            wnd.RequestLimitData.limit = data.DrugRequestPlan_Summa > 0 ? data.DrugRequestPlan_Summa*1 : 0;
            wnd.RequestLimitData.limit_fed = data.DrugRequestPlan_FedSumma > 0 ? data.DrugRequestPlan_FedSumma*1 : 0;
            wnd.RequestLimitData.limit_reg = data.DrugRequestPlan_RegSumma > 0 ? data.DrugRequestPlan_RegSumma*1 : 0;

            //количество людей на участке
            wnd.RequestLimitData.kolvo = data.DrugRequestPlan_Kolvo > 0 ? data.DrugRequestPlan_Kolvo*1 : 0;
            wnd.RequestLimitData.kolvo_fed = data.DrugRequestPlan_FedKolvo > 0 ? data.DrugRequestPlan_FedKolvo*1 : 0;
            wnd.RequestLimitData.kolvo_reg = data.DrugRequestPlan_RegKolvo > 0 ? data.DrugRequestPlan_RegKolvo*1 : 0;

            //количество людей в персональной разнарядке (с медикаментами)
            wnd.RequestLimitData.r_kolvo = data.DrugRequestPersonOrder_Kolvo > 0 ? data.DrugRequestPersonOrder_Kolvo*1 : 0;
            wnd.RequestLimitData.r_kolvo_fed = data.DrugRequestPersonOrder_FedKolvo > 0 ? data.DrugRequestPersonOrder_FedKolvo*1 : 0;
            wnd.RequestLimitData.r_kolvo_reg = data.DrugRequestPersonOrder_RegKolvo > 0 ? data.DrugRequestPersonOrder_RegKolvo*1 : 0;

            //обьем участковой заявки
            wnd.RequestLimitData.count_req = data.DrugRequestPlan_CountReq > 0 ? data.DrugRequestPlan_CountReq*1 : 0;
            wnd.RequestLimitData.count_fed = data.DrugRequestPlan_CountFed > 0 ? data.DrugRequestPlan_CountFed*1 : 0;
            wnd.RequestLimitData.count_reg = data.DrugRequestPlan_CountReg > 0 ? data.DrugRequestPlan_CountReg*1 : 0;

            //сумма заявки
            wnd.RequestLimitData.sum = data.DrugRequest_RowSumma > 0 ? data.DrugRequest_RowSumma*1 : 0;
            wnd.RequestLimitData.sum_fed = data.DrugRequest_FedRowSumma > 0 ? data.DrugRequest_FedRowSumma*1 : 0;
            wnd.RequestLimitData.sum_reg = data.DrugRequest_RegRowSumma > 0 ? data.DrugRequest_RegRowSumma*1 : 0;

            //сумма персональной разнарядки
            wnd.RequestLimitData.drpo_sum = data.DrugRequestPersonOrder_Summa > 0 ? data.DrugRequestPersonOrder_Summa*1 : 0;
            wnd.RequestLimitData.drpo_sum_fed = data.DrugRequestPersonOrder_FedSumma > 0 ? data.DrugRequestPersonOrder_FedSumma*1 : 0;
            wnd.RequestLimitData.drpo_sum_reg = data.DrugRequestPersonOrder_RegSumma > 0 ? data.DrugRequestPersonOrder_RegSumma*1 : 0;

            //резерв (сумма заявки - сумма персональной разнарядки)
            wnd.RequestLimitData.reserve = wnd.RequestLimitData.sum > wnd.RequestLimitData.drpo_sum ? wnd.RequestLimitData.sum - wnd.RequestLimitData.drpo_sum : 0;
            wnd.RequestLimitData.reserve_fed = wnd.RequestLimitData.sum_fed > wnd.RequestLimitData.drpo_sum_fed ? wnd.RequestLimitData.sum_fed - wnd.RequestLimitData.drpo_sum_fed : 0;
            wnd.RequestLimitData.reserve_reg = wnd.RequestLimitData.sum_reg > wnd.RequestLimitData.drpo_sum_reg ? wnd.RequestLimitData.sum_reg - wnd.RequestLimitData.drpo_sum_reg : 0;

            //по участку (объем заявки - резерв)
            wnd.RequestLimitData.lrsum = wnd.RequestLimitData.count_req > wnd.RequestLimitData.reserve ? wnd.RequestLimitData.count_req - wnd.RequestLimitData.reserve : 0;
            wnd.RequestLimitData.lrsum_fed = wnd.RequestLimitData.count_fed > wnd.RequestLimitData.reserve_fed ? wnd.RequestLimitData.count_fed - wnd.RequestLimitData.reserve_fed : 0;
            wnd.RequestLimitData.lrsum_reg = wnd.RequestLimitData.count_reg > wnd.RequestLimitData.reserve_reg ? wnd.RequestLimitData.count_reg - wnd.RequestLimitData.reserve_reg : 0;

            //проверка превышения объема финансирования
            wnd.RequestLimitData.count_req_str = wnd.RequestLimitData.count_req > wnd.RequestLimitData.limit ? '<span style="color:red;">' + sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_req) + '</span>' : sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_req);
            wnd.RequestLimitData.count_fed_str = wnd.RequestLimitData.count_fed > wnd.RequestLimitData.limit_fed ? '<span style="color:red;">' + sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_fed) + '</span>' : sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_fed);
            wnd.RequestLimitData.count_reg_str = wnd.RequestLimitData.count_reg > wnd.RequestLimitData.limit_reg ? '<span style="color:red;">' + sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_reg) + '</span>' : sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_reg);
        }

        //обновление информации о лимитах в информационой панели
        wnd.DrugRequestInformationPanel.setData('currency_name', getCurrencyName());
        if (wnd.isCommonPersonRegisterType()){
            wnd.DrugRequestInformationPanel.setData('request_kolvo', '');
            wnd.DrugRequestInformationPanel.setData('request_kolvo_fed', 'Фед.: '+wnd.RequestLimitData.kolvo_fed);
            wnd.DrugRequestInformationPanel.setData('request_kolvo_reg', '&nbsp;&nbsp;Рег.: '+wnd.RequestLimitData.kolvo_reg);
            wnd.DrugRequestInformationPanel.setData('request_limit', '');
            wnd.DrugRequestInformationPanel.setData('request_limit_fed', 'Фед.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.limit_fed));
            wnd.DrugRequestInformationPanel.setData('request_limit_reg', '&nbsp;&nbsp;Рег.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.limit_reg));
            wnd.DrugRequestInformationPanel.setData('request_count_req', '');
            wnd.DrugRequestInformationPanel.setData('request_count_fed', 'Фед.: '+wnd.RequestLimitData.count_fed_str+(wnd.RequestLimitData.kolvo_fed > 0 ? ' / '+wnd.RequestLimitData.kolvo_fed : ''));
            wnd.DrugRequestInformationPanel.setData('request_count_reg', '&nbsp;&nbsp;Рег.: '+wnd.RequestLimitData.count_reg_str+(wnd.RequestLimitData.kolvo_reg > 0 ? ' / '+wnd.RequestLimitData.kolvo_reg : ''));
            wnd.DrugRequestInformationPanel.setData('request_lrsum', '');
            wnd.DrugRequestInformationPanel.setData('request_lrsum_req', 'Фед.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.lrsum_fed)+(wnd.RequestLimitData.r_kolvo_fed > 0 ? ' / '+wnd.RequestLimitData.r_kolvo_fed : ''));
            wnd.DrugRequestInformationPanel.setData('request_lrsum_reg', '&nbsp;&nbsp;Рег.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.lrsum_reg)+(wnd.RequestLimitData.r_kolvo_reg > 0 ? ' / '+wnd.RequestLimitData.r_kolvo_reg : ''));
            wnd.DrugRequestInformationPanel.setData('request_sum', '');
            wnd.DrugRequestInformationPanel.setData('request_sum_fed', 'Фед.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.sum_fed));
            wnd.DrugRequestInformationPanel.setData('request_sum_reg', '&nbsp;&nbsp;Рег.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.sum_reg));
        } else {
            wnd.DrugRequestInformationPanel.setData('request_kolvo', wnd.RequestLimitData.kolvo);
            wnd.DrugRequestInformationPanel.setData('request_kolvo_fed', '');
            wnd.DrugRequestInformationPanel.setData('request_kolvo_reg', '');
            wnd.DrugRequestInformationPanel.setData('request_limit', sw.Promed.Format.rurMoney(wnd.RequestLimitData.limit));
            wnd.DrugRequestInformationPanel.setData('request_limit_fed', '');
            wnd.DrugRequestInformationPanel.setData('request_limit_reg', '');
            wnd.DrugRequestInformationPanel.setData('request_count_req', wnd.RequestLimitData.count_req_str+(wnd.RequestLimitData.kolvo > 0 ? ' / '+wnd.RequestLimitData.kolvo : ''));
            wnd.DrugRequestInformationPanel.setData('request_count_fed', '');
            wnd.DrugRequestInformationPanel.setData('request_count_reg', '');
            wnd.DrugRequestInformationPanel.setData('request_lrsum', sw.Promed.Format.rurMoney(wnd.RequestLimitData.lrsum)+(wnd.RequestLimitData.r_kolvo > 0 ? ' / '+wnd.RequestLimitData.r_kolvo : ''));
            wnd.DrugRequestInformationPanel.setData('request_lrsum_fed', '');
            wnd.DrugRequestInformationPanel.setData('request_lrsum_reg', '');
            wnd.DrugRequestInformationPanel.setData('request_sum', sw.Promed.Format.rurMoney(wnd.RequestLimitData.sum));
            wnd.DrugRequestInformationPanel.setData('request_sum_fed', '');
            wnd.DrugRequestInformationPanel.setData('request_sum_reg', '');
        }

        if (!Ext.isEmpty(wnd.LpuRegion_id)) {
            //wnd.DrugRequestInformationPanel.setData('request_lrsum_display', 'inline');
            wnd.DrugRequestInformationPanel.setData('request_kolvo_display', 'inline');
            wnd.DrugRequestInformationPanel.setData('request_limit_display', 'inline');
            wnd.DrugRequestInformationPanel.setData('request_count_display', 'inline');
        } else {
            //wnd.DrugRequestInformationPanel.setData('request_lrsum_display', 'none');
            wnd.DrugRequestInformationPanel.setData('request_kolvo_display', 'none');
            wnd.DrugRequestInformationPanel.setData('request_limit_display', 'none');
            wnd.DrugRequestInformationPanel.setData('request_count_display', 'none');
        }

        wnd.DrugRequestInformationPanel.showData();
    },
    generateRequestData: function(action) {
        var wnd = this;

        wnd.grdSetOptions(action, function(params) { //получение от пользователя входящих данных
            wnd.grdCheckExistsData(action, function(data_exists) { //проверка текущей заявки на наличие данных
                wnd.grdConfirm(data_exists, function() { //получение подтверждения пользователя на изменение данных
                    wnd.grdExecute(action, params, function() { //изменение данных
                        wnd.grdCallback(action); //отображение изменения данных на форме
                    });
                });
            });
        });
    },
    grdSetOptions: function(action, callback) {
        var params = {
            DrugRequest_id: this.DrugRequest_id
        };
        if (action == 'action_drug_copy') {
            getWnd('swMzDrugRequestCopyOptionsWindow').show({
                DrugRequest_id: this.DrugRequest_id,
                onSelect: function(prm) {
                    if (prm.DrugRequest_id > 0) {
                        params.SourceDrugRequest_id = prm.DrugRequest_id;
                        callback(params);
                    }
                }
            });
        } else {
            callback(params);
        }
    },
    grdCheckExistsData: function(action, callback) {
        if (this.DrugRequest_id <= 0) {
            return false;
        }

        if (action == 'action_drug_copy') {
            Ext.Ajax.request({
                url: '/?c=MzDrugRequest&m=getDrugRequestRowCount',
                params: {
                    DrugRequest_id: this.DrugRequest_id
                },
                callback: function(options, success, response) {
                    if (success) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        callback(result.cnt > 0);
                    }
                }
            });
        } else {
            callback(false);
        }
    },
    grdConfirm: function(data_exists, callback) {
        if (data_exists) {
            sw.swMsg.show({
                icon: Ext.MessageBox.QUESTION,
                msg: 'Текущая заявка врача содержит данные, которые могут быть изменены. Продолжить операцию копирования данных?',
                title: 'Вопрос',
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId, text, obj) {
                    if ('yes' == buttonId) {
                        callback();
                    }
                }
            });
        } else {
            callback();
        }
    },
    grdExecute: function(action, params, callback) {
        var method = null;

        switch(action) {
            case 'action_create_person_list':
                method = 'createMzDrugRequestPersonList';
                break;
            case 'action_drug_copy':
                method = 'createMzDrugRequestDrugCopy';
                break;
        }

        if (method) {
            Ext.Ajax.request({
                url: '/?c=MzDrugRequest&m='+method,
                params: params,
                callback: function(options, success, response) {
                    if (success) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result.Error_Msg) {
                            sw.swMsg.alert('Ошибка', result.Error_Msg);
                        } else {
                            callback();
                        }
                    } else {
                        sw.swMsg.alert('Ошибка', 'При обработке данных произошла ошибка.');
                        return false;
                    }
                }
            });
        }
    },
    grdCallback: function(action) {
        switch(action) {
            case 'action_create_person_list':
                sw.swMsg.alert('Формирование завершено', 'Список пациентов сформирован: в него включены льготники, прикрепленные к участку врача, и обращавшиеся за рецептами в последние 365 дней.');
                this.doPersonGridSearch();
                break;
            case 'action_drug_copy':
                sw.swMsg.alert('Копирование завершено', 'Копирование медикаментов завершено. Внимание! Необходимо отредактировать количество медикаментов в текущей заявке.');
                this.doDrugGridSearch();
                if (!Ext.isEmpty(this.DrugListGrid.DataState)) {
                    this.DrugListGrid.DataState = 'outdated';
                }
                break;
        }
    },
    askConfirm: function(msg, callback) {
        sw.swMsg.show({
            icon: Ext.MessageBox.QUESTION,
            msg: msg,
            title: 'Вопрос',
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ('yes' == buttonId) {
                    callback();
                }
            }
        });
    },
    openEmk: function(view_frame) {
        var selected_record = view_frame.getGrid().getSelectionModel().getSelected();

        if (selected_record && !Ext.isEmpty(selected_record.get('Person_id'))) {
            Ext.Ajax.request({
                params: {
                    searchMode: 'all',
                    Person_id: selected_record.get('Person_id')
                },
                callback: function (options, success, response) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (result.data && result.data[0]) {
                        var person_data = result.data[0];
                        person_data.ARMType = 'common';
                        person_data.userMedStaffFact = {ARMType: 'OuzSpec'};
                        person_data.readOnly = true;
                        getWnd('swPersonEmkWindow').show(person_data);
                    } else {
                        sw.swMsg.alert(langs('Ошибка'), langs('Пациент не найден'));
                    }
                },
                url:'/?c=Person&m=getPersonSearchGrid'
            });
        }
    },
    recalculateRequestLpuRegionPlanParams: function(callback) {
        var wnd = this;
        if (!Ext.isEmpty(wnd.LpuRegion_id)) {
            Ext.Ajax.request({
                params: {
                    LpuRegionDrugRequest_id: wnd.DrugRequest_id
                },
                success: function (response) {
                    var data = Ext.util.JSON.decode(response.responseText);
                    if (!Ext.isEmpty(data.Error_Msg)) {
                        sw.swMsg.alert(langs('Ошибка'), data.Error_Msg);
                    } else {
                        if (typeof callback == 'function') {
                            callback(data);
                        }
                    }
                },
                failure:function () {
                    var error_msg = langs('При пересчете данных произошла ошибка')
                    var data = Ext.util.JSON.decode(response.responseText);
                    if (data && !Ext.isEmpty(data.Error_Msg)) {
                        error_msg = data.Error_Msg;
                    }
                    sw.swMsg.alert(langs('Ошибка'), error_msg);
                },
                url:'/?c=MzDrugRequest&m=calculateDrugRequestPlanLpuRegionParams'
            });
        }
    },
	openClinExWorkEditWindow : function(EvnVK_id) {
		var args = {};
		args.showtype = 'view';
		args.EvnVK_id = EvnVK_id;
		getWnd('swClinExWorkEditWindow').show(args);											
	},
	reshow: function(options) {
        this.show_arguments.reshow_callback = options.callback ? options.callback : Ext.emptyFn;
		this.show(this.show_arguments);
	},
	show: function() {
        var wnd = this;
		sw.Promed.swMzDrugRequestEditWindow.superclass.show.apply(this, arguments);

        this.ARMType = !Ext.isEmpty(arguments[0].ARMType) ? arguments[0].ARMType : null;
		this.action = null;
		this.owner = null;
		this.onHide = Ext.emptyFn;
		this.DrugRequest_id = null;
		this.DrugRequestFirstCopy_id = null;
		this.DrugRequestStatus_Code = null;
		this.DrugFinance_id = null;
		this.DrugRequestCategory_SysNick = null;
		this.DrugRequestPeriod_id = null;
		this.PersonRegisterType_id = null;
		this.Lpu_id = null;
		this.LpuRegion_id = null;
        this.MedPersonal_isMainSpec = false; //признак того, что врач из заявки является главным специалистом на момент начала рабочего периода заявки
        this.show_arguments = new Object();
        this.reshow_callback = Ext.emptyFn;

        if (!arguments[0]) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        } else {
            this.show_arguments = arguments[0];
        }

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].owner) {
			this.owner = arguments[0].owner;
		}
		if (arguments[0].onHide && typeof arguments[0].onHide == 'function') {
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].DrugRequest_id) {
			this.DrugRequest_id = arguments[0].DrugRequest_id;
		}
        if (arguments[0].DrugRequestPeriod_id) {
            this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id;
        }
		if (arguments[0].PersonRegisterType_id) {
			this.PersonRegisterType_id = arguments[0].PersonRegisterType_id;
		}
		if (arguments[0].reshow_callback) {
			this.reshow_callback = arguments[0].reshow_callback;
		}

        var loadMask = new Ext.LoadMask(this.getEl(), {msg:lang['zagruzka']});
        //loadMask.show();

		wnd.setTitle(lang['zayavka'] + (wnd.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));

		wnd.DrugRequestTabs.setActiveTab(3);
		wnd.DrugRequestTabs.setActiveTab(2);
		wnd.DrugRequestTabs.setActiveTab(1);
		wnd.DrugRequestTabs.setActiveTab(0);

        this.DrugGrid.addActions({
            name:'action_mdre_dg_actions',
            text:'Действия',
            iconCls: 'actions16',
            menu: [{
                name: 'action_drug_copy',
                text: 'Копировать медикаменты из предыдущей заявки',
                tooltip: 'Копировать медикаменты из предыдущей заявки',
                handler: function() {
                    wnd.generateRequestData(this.name);
                },
                iconCls: 'view16'
            }]
        });

        this.PersonGrid.addActions({
            name:'action_mdre_pg_open_emk',
            text:'Открыть ЭМК',
            iconCls: 'open16',
            handler: function() {
                wnd.openEmk(wnd.PersonGrid);
            }
        });

        this.PersonGrid.addActions({
            name:'action_mdre_pg_actions',
            text:'Действия',
            iconCls: 'actions16',
            menu: [{
                name: 'action_create_person_list',
                text: 'Создать список пациентов разнарядки',
                tooltip: 'Создать список пациентов разнарядки',
                handler: function() {
                    wnd.generateRequestData(this.name);
                },
                iconCls: 'view16'
            }]
        });

        this.DrugPersonGrid.addActions({
            name:'action_mdre_dpg_open_emk',
            text:'Открыть ЭМК',
            iconCls: 'open16',
            handler: function() {
                wnd.openEmk(wnd.DrugPersonGrid);
            }
        });

        if (this.ARMType == 'mzchieffreelancer' || this.ARMType == 'leadermo') { //mzchieffreelancer - АРМ главного внештатного врача специалиста; leadermo - АРМ руководителя МО
            this.PersonGrid.getAction('action_mdre_pg_open_emk').show();
            this.DrugPersonGrid.getAction('action_mdre_dpg_open_emk').show();
        } else {
            this.PersonGrid.getAction('action_mdre_pg_open_emk').hide();
            this.DrugPersonGrid.getAction('action_mdre_dpg_open_emk').hide();
        }

        this.setDisabled();

		this.DrugRequestInformationPanel.showToolbar(false);
		this.DrugRequestInformationPanel.showData();
		this.DrugPersonInformationPanel.showData();

		Ext.Ajax.request({
			params: {
				DrugRequest_id: wnd.DrugRequest_id
			},
			callback: function (options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result[0]) {
					var request_status = result[0].DrugRequestStatus_Name;
					var request_name = "";
					var request_place = "";
					var request_owner = "";
					var request_protection = "";

					if (!Ext.isEmpty(result[0].Lpu_Nick)) {
						request_place += " " + result[0].Lpu_Nick;
					}

					if (!Ext.isEmpty(result[0].LpuSection_Name)) {
						request_place += ", " + result[0].LpuSection_Name;
					}

					if (result[0].MedPersonal_id > 0) {
						wnd.MedPersonal_id = result[0].MedPersonal_id;
						wnd.MedPersonal_isMainSpec = (!Ext.isEmpty(result[0].MedPersonal_isMainSpec) && result[0].MedPersonal_isMainSpec > 0);
						if (!Ext.isEmpty(result[0].MedPersonal_Fio)) {
							request_owner += result[0].MedPersonal_Fio;
						}
                        if (!Ext.isEmpty(result[0].LpuRegion_Name)) {
                            if (!Ext.isEmpty(request_owner)) {
                                request_owner += ', ';
                            }
                            request_owner += result[0].LpuRegion_Name;
                        } else if (!Ext.isEmpty(result[0].Dolgnost_Name)) {
                            if (!Ext.isEmpty(request_owner)) {
                                request_owner += ', ';
                        }
                            request_owner += result[0].Dolgnost_Name;
                        }
					} else {
                        wnd.MedPersonal_isMainSpec = false;
						if (!Ext.isEmpty(result[0].LpuRegion_Name)) {
							request_owner += result[0].LpuRegion_Name;
						}
					}

                    if (!Ext.isEmpty(result[0].Lpu_id)) {
                        wnd.Lpu_id = result[0].Lpu_id;
                    }

                    if (!Ext.isEmpty(result[0].LpuRegion_id)) {
                        wnd.LpuRegion_id = result[0].LpuRegion_id;
                    }

					if (!Ext.isEmpty(result[0].DrugRequestStatus_Code)) {
						wnd.setDrugRequestStatus(result[0].DrugRequestStatus_Code);
					}

					if (!Ext.isEmpty(result[0].DrugFinance_id) && result[0].DrugFinance_id > 0) {
						wnd.DrugFinance_id = result[0].DrugFinance_id;
					}

					if (!Ext.isEmpty(result[0].DrugRequestCategory_SysNick)) {
						wnd.DrugRequestCategory_SysNick = result[0].DrugRequestCategory_SysNick;
					}

					if (!Ext.isEmpty(result[0].DrugRequestPeriod_Name)) {
						request_name += lang['na'] + ' ' + result[0].DrugRequestPeriod_Name;
					}
					if (!Ext.isEmpty(result[0].PersonRegisterType_Name)) {
						request_name += (!Ext.isEmpty(request_name) ? ", " : "") + result[0].PersonRegisterType_Name;
					}
					if (!Ext.isEmpty(result[0].FirstCopy_Inf)) {
						request_name += (!Ext.isEmpty(request_name) ? " " : "") + result[0].FirstCopy_Inf;
					}
					if (!Ext.isEmpty(request_name)) {
						request_name = lang['zayavka'] + ' ' + request_name;
					}

					if (!Ext.isEmpty(result[0].PersonRegisterType_id)) {
                        wnd.PersonRegisterType_id = result[0].PersonRegisterType_id;
						wnd.DrugPersonPanel.show();
						wnd.DrugRequestTabs.unhideTabStripItem('mdre_person');
					} else {
                        wnd.PersonRegisterType_id = null;
						wnd.DrugPersonPanel.hide();
						wnd.DrugRequestTabs.hideTabStripItem('mdre_person');
					}
                    if (!Ext.isEmpty(result[0].DrugRequestPeriod_id)) {
                        wnd.DrugRequestPeriod_id = result[0].DrugRequestPeriod_id;
                    }
                    if (!Ext.isEmpty(result[0].DrugRequestFirstCopy_id)) {
                        wnd.DrugRequestFirstCopy_id = result[0].DrugRequestFirstCopy_id;
                    } else {
                        wnd.DrugRequestFirstCopy_id = null;
                    }

                    if (wnd.PersonRegisterType_id > 0 && wnd.DrugRequestFirstCopy_id) {
                        wnd.DrugRequestTabs.unhideTabStripItem('mdre_first_copy');
                    } else {
                        wnd.DrugRequestTabs.hideTabStripItem('mdre_first_copy');
                    }

                    if (!Ext.isEmpty(result[0].Protection_Date) && result[0].Protection_RemainedDays >= 0) {
						request_protection = result[0].Protection_Date;
						if (result[0].Protection_RemainedDays <= 7) {
							request_protection = "<span style='color: #ff0000;'>" + request_protection + "</span>";
						}
					} else {
						request_protection = "не определены";
					}

					wnd.DrugRequestInformationPanel.setData('request_name', request_name);
					wnd.DrugRequestInformationPanel.setData('request_place', request_place);
					wnd.DrugRequestInformationPanel.setData('request_owner', request_owner);
					wnd.DrugRequestInformationPanel.setData('request_protection', request_protection);
					wnd.DrugRequestInformationPanel.setData('request_status', request_status);

					if (wnd.isCommonPersonRegisterType()) {
                        wnd.DrugFilterPanel.getForm().findField('DrugFinance_id').showContainer();
                        wnd.DrugFilterPanel.getForm().findField('DrugFinance_id').getStore().load({
                        	params: {where:' where DrugFinance_id = 3 or DrugFinance_id = 27'}
                        });
                        wnd.DrugListFilterPanel.getForm().findField('DrugFinance_id').showContainer();
                        wnd.DrugListFilterPanel.getForm().findField('DrugFinance_id').getStore().load({
                        	params: {where:' where DrugFinance_id = 3 or DrugFinance_id = 27'}
                        });
					} else {
						wnd.DrugFilterPanel.getForm().findField('DrugFinance_id').hideContainer();
						wnd.DrugFilterPanel.getForm().findField('DrugFinance_id').getStore().removeAll();
						wnd.DrugListFilterPanel.getForm().findField('DrugFinance_id').hideContainer();
						wnd.DrugListFilterPanel.getForm().findField('DrugFinance_id').getStore().removeAll();
					}
					wnd.setDisabled();
				}
				wnd.setRequestLimit(result[0]); //метод включает  себя обновление данных информационной панели DrugRequestInformationPanel
				wnd.setGridDefaultParams();
				wnd.doDrugGridSearch();
				//loadMask.hide();

                if (wnd.reshow_callback && typeof wnd.reshow_callback == 'function') {
                    wnd.reshow_callback();
                }
			},
			url:'/?c=MzDrugRequest&m=getDrugRequestData'
		});
	},
	initComponent: function() {
		var wnd = this;

		this.DrugRequestInformationPanel = new sw.Promed.HtmlTemplatePanel({
            bbar: new Ext.Toolbar({
                style: 'background: transparent; border: 0px solid #C0C0C0;',
                width: 100,
                items: [{
                    xtype: 'button',
                    text: langs('Обновить'),
                    id: 'mdre_PlanParamsRefreshButton',
                    iconCls: 'refresh16',
                    handler: function (){
                        wnd.recalculateRequestLpuRegionPlanParams(function(data) {
                            wnd.setRequestLimit(data);
                        });
                    }
                }]
            }),
			region: 'north',
			win: wnd,
            showToolbar: function(show) {
                if (show) {
                    this.getBottomToolbar().show();
                } else {
                    this.getBottomToolbar().hide();
                }
                wnd.doLayout();
            }
		});

		/*var tpl = "";
		tpl += "<table style='margin: 5px; float: left;'>";
		tpl += "<tr><td>{request_name}</td><td style='width: 20px;'>&nbsp;</td><td>Объем фин. ({currency_name}/чел.):  <b>{request_limit}</b> <b>{request_limit_fed}</b> <b>{request_limit_reg}</b></td></tr>";
		tpl += "<tr><td>Дата и время сдачи заявки: {request_protection}</td><td>&nbsp;</td><td><div style=\"display:{request_count_display}\">Объем заявки ({currency_name}/чел.): <b>{request_count_req}</b> <b>{request_count_fed}</b> <b>{request_count_reg}</b></div></td></tr>";
		tpl += "<tr><td>{request_place}</td><td>&nbsp;</td><td><div style=\"display:{request_lrsum_display}\">по участку ({currency_name}/чел.): <b>{request_lrsum}</b> <b>{request_lrsum_fed}</b> <b>{request_lrsum_reg}</b></div></td></tr>";
		tpl += "<tr><td>{request_owner}</td><td>&nbsp;</td><td>Сумма заявки ({currency_name}): <b>{request_sum}</b> <b>{request_sum_fed}</b> <b>{request_sum_reg}</b></td></tr>";
		tpl += "<tr><td>Статус заявки: <b>{request_status}</b></td><td>&nbsp;</td><td>&nbsp;</td></tr>";
		tpl += "</table>";*/

		var tpl = "";
		tpl += "<table style='margin: 5px; float: left;'>";
		tpl += "<tr><td>{request_name}</td><td style='width: 20px;'>&nbsp;</td><td><div style=\"display:{request_kolvo_display}\">Количество льготников (чел.): <b>{request_kolvo}</b> <b>{request_kolvo_fed}</b> <b>{request_kolvo_reg}</b></div></td></tr>";
		tpl += "<tr><td>Дата и время сдачи заявки: {request_protection}</td><td>&nbsp;</td><td><div style=\"display:{request_limit_display}\">Объём финансирования ({currency_name}): <b>{request_limit}</b> <b>{request_limit_fed}</b> <b>{request_limit_reg}</b></div></td></tr>";
		tpl += "<tr><td>{request_place}</td><td>&nbsp;</td><td><div style=\"display:{request_count_display}\">Объем заявки ({currency_name}/чел.): <b>{request_count_req}</b> <b>{request_count_fed}</b> <b>{request_count_reg}</b></div></td></tr>";
		tpl += "<tr><td>{request_owner}</td><td>&nbsp;</td><td>Предельная сумма заявки ({currency_name}): <b>{request_sum}</b> <b>{request_sum_fed}</b> <b>{request_sum_reg}</b></td></tr>";
		tpl += "<tr><td>Статус заявки: <b>{request_status}</b></td><td>&nbsp;</td><td>&nbsp;</td></tr>";
		tpl += "</table>";
		this.DrugRequestInformationPanel.setTemplate(tpl);

		this.DrugPersonInformationPanel = new sw.Promed.HtmlTemplatePanel({
			region: 'south',
			win: wnd
		});

		tpl = "";
		tpl += "<table style='margin: 5px; float: left;'>";
		tpl += "<tr><td style='width: 150px;'>Количество всего:  {total_count}</td><td style='width: 150px;'>В разнарядке: {order_count}</td><td style='width: 150px;'> В резерве: {reserve_count}</td><td style='color: #ff0000;'>{error_message}</td></tr>";
		tpl += "</table>";
		this.DrugPersonInformationPanel.setTemplate(tpl);

		this.DrugFilterFormPanel = new sw.Promed.Panel({
			region: 'center',
			layout: 'form',
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'mdre_DrugFilterForm',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'daterangefield',
						name: 'DrugRequestRow_updDateRange',
						fieldLabel: lang['data_izmeneniya'],
						plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170
					}]
				}, {
					layout: 'form',
					labelWidth: 79,
					items: [{
						xtype: 'textfield',
						name: 'DrugComplexMnnName_Name',
						fieldLabel: lang['mnn']
					}]
				}, {
					layout: 'form',
					labelWidth: 120,
					items: [{
						fieldLabel: lang['finansirovanie'],
						xtype: 'swcommonsprcombo',
						comboSubject: 'DrugFinance'
					}]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Tradenames_Name',
						fieldLabel: lang['torgovoe_naim']
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'ClsDrugForms_Name',
						fieldLabel: lang['lek_forma']
					}]
				}, {
                    layout: 'form',
                    labelWidth: 240,
                    items: [{
                        xtype: 'checkbox',
                        name: 'ShowDrugRowWithoutPerson',
                        fieldLabel: langs('Не распределены между пациентами')
                    }]
                }]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'DrugComplexMnnDose_Name',
						fieldLabel: lang['dozirovka']
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'DrugComplexMnnFas_Name',
						fieldLabel: lang['fasovka']
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'checkbox',
						name: 'ShowDeletedDrugRow',
						fieldLabel: lang['v_t_ch_udalennyie']
					}]
				}]
			}]
		});

		this.DrugListFilterFormPanel = new sw.Promed.Panel({
			region: 'center',
			layout: 'form',
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'mdre_DrugListFilterForm',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'DrugComplexMnnName_Name',
						fieldLabel: lang['mnn']
					}]
				}, {
					layout: 'form',
					labelWidth: 120,
					items: [{
						fieldLabel: lang['finansirovanie'],
						xtype: 'swcommonsprcombo',
						comboSubject: 'DrugFinance'
					}]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Tradenames_Name',
						fieldLabel: lang['torgovoe_naim']
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'ClsDrugForms_Name',
						fieldLabel: lang['lek_forma']
					}]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'DrugComplexMnnDose_Name',
						fieldLabel: lang['dozirovka']
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'DrugComplexMnnFas_Name',
						fieldLabel: lang['fasovka']
					}]
				}]
			}]
		});

		this.PersonFilterFormPanel = new sw.Promed.Panel({
			region: 'center',
			layout: 'form',
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'mdre_PersonFilterForm',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SurName',
						fieldLabel: lang['familiya']
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_FirName',
						fieldLabel: lang['imya']
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SecName',
						fieldLabel: lang['otchestvo']
					}]
				}, {
                    layout: 'form',
                    items: [{
                        xtype: 'checkbox',
                        name: 'ShowPersonOnlyWthoutDrug',
                        fieldLabel: langs('Без медикаментов')
                    }]
                }]
			}]
		});

		this.FirstCopyFilterFormPanel = new sw.Promed.Panel({
			region: 'center',
			layout: 'form',
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'mdre_FirstCopyFilterForm',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SurName',
						fieldLabel: langs('Фамилия')
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_FirName',
						fieldLabel: langs('Имя')
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'Person_SecName',
						fieldLabel: langs('Отчество')
					}]
				}, {
                    layout: 'form',
                    items: [{
                        xtype: 'checkbox',
                        name: 'ShowPersonOnlyWthoutDrug',
                        fieldLabel: langs('Без медикаментов')
                    }]
                }]
			}]
		});

		this.DrugFilterButtonsPanel = new sw.Promed.Panel({
			region: 'north',
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdre_BtnDrugGridSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doDrugGridSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdre_BtnDrugGridReset',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doDrugGridSearch(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.DrugListFilterButtonsPanel = new sw.Promed.Panel({
			region: 'north',
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdre_BtnDrugListGridSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doDrugListGridSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdre_BtnDrugListGridReset',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doDrugListGridSearch(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.PersonFilterButtonsPanel = new sw.Promed.Panel({
			region: 'north',
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdre_BtnPersonGridSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doPersonGridSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdre_BtnPersonGridReset',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doPersonGridSearch(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.FirstCopyFilterButtonsPanel = new sw.Promed.Panel({
			region: 'north',
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdre_BtnFirstCopyGridSearch',
						text: langs('Найти'),
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doFirstCopyGridSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdre_BtnFirstCopyGridReset',
						text: langs('Сброс'),
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doFirstCopyGridSearch(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.DrugFilterPanel = getBaseFiltersFrame({
			region: 'north',
			height: 100,
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: wnd.WindowToolbar,
			items: [
				wnd.DrugFilterFormPanel,
				wnd.DrugFilterButtonsPanel
			],
			doSearch: wnd.doDrugGridSearch.createDelegate(wnd)
		});

		this.DrugListFilterPanel = getBaseFiltersFrame({
			region: 'north',
			height: 100,
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: wnd.WindowToolbar,
			items: [
				wnd.DrugListFilterFormPanel,
				wnd.DrugListFilterButtonsPanel
			],
			doSearch: wnd.doDrugListGridSearch.createDelegate(wnd)
		});

		this.PersonFilterPanel = getBaseFiltersFrame({
			region: 'north',
			height: 100,
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: wnd.WindowToolbar,
			items: [
				wnd.PersonFilterFormPanel,
				wnd.PersonFilterButtonsPanel
			],
			doSearch: wnd.doPersonGridSearch.createDelegate(wnd)
		});

		this.FirstCopyFilterPanel = getBaseFiltersFrame({
			region: 'north',
			height: 100,
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: wnd.WindowToolbar,
			items: [
				wnd.FirstCopyFilterFormPanel,
				wnd.FirstCopyFilterButtonsPanel
			],
			doSearch: wnd.doFirstCopyGridSearch.createDelegate(wnd)
		});

		this.DrugGrid = new sw.Promed.ViewFrame({
			title: lang['medikamentyi'],
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=MzDrugRequest&m=deleteDrugRequestRow'},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadMzDrugRequestDrugGrid',
			region: 'center',
			editformclassname: 'swMzDrugRequestRowEditWindow',
			id: 'mdre_DrugGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			saveAtOnce: false,
			style: 'margin: 0px',
			stringfields: [
				{name: 'DrugRequestRow_id', type: 'int', header: 'ID', key: true},
				{name: 'NTFR_Name', type: 'string', header: lang['klass_ntfr'], width: 100},
				{name: 'ATX_Code', type: 'string', header: lang['ath'], width: 100},
				{name: 'DrugComplexMnn_id', hidden: true},
				{id: 'autoexpand', name: 'DrugComplexMnnName_Name', header: lang['mnn'], renderer: function(v, p, record) { return record.get('isProblem') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'TRADENAMES_id', hidden: true},
				{name: 'Tradenames_Name', header: lang['torg_naim'], renderer: function(v, p, record) { return record.get('isProblemTorg') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'ClsDrugForms_Name', type: 'string', header: lang['lekarstvennaya_forma'], width: 160},
				{name: 'DrugComplexMnnDose_Name', type: 'string', header: lang['dozirovka'], width: 100},
				{name: 'DrugComplexMnnFas_Name', type: 'string', header: lang['fasovka'], width: 100},
				{name: 'DrugListRequest_Comment', type: 'string', header: lang['primechanie'], width: 140},
				{name: 'DrugFinance_id', type: 'int', hidden: true},
				{name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 120},
				{name: 'DrugRequestRow_Kolvo', type: 'float', header: lang['kol-vo'], width: 80},
				{name: 'DrugRequestRow_Price', type: 'money', header: lang['tsena'], width: 80, align: 'right'},
				{name: 'DrugRequestRow_Summa', type: 'money', header: lang['summa'], width: 80, align: 'right'},
				{name: 'DrugRequestRow_insDT', type: 'date', header: lang['vnesen'], width: 80, align: 'right'},
				{name: 'DrugRequestRow_updDT', type: 'date', header: lang['izmenen'], width: 80, align: 'right'},
				{name: 'DrugRequestRow_delDT', type: 'date', header: lang['udalen'], width: 80, align: 'right'},
				{name: 'isProblem', hidden: true},
				{name: 'isProblemTorg', hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm, rowIdx, record) {
				if (record.get('DrugRequestRow_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}

				if (!wnd.DrugPersonPanel.hidden) {
					wnd.DrugPersonGrid.setParam('DrugComplexMnn_id', null, false);
					wnd.DrugPersonGrid.setParam('Tradenames_id', null, false);
					wnd.DrugPersonGrid.setParam('DrugFinance_id', null, false);

					if (record.get('DrugComplexMnn_id') > 0 || record.get('TRADENAMES_id') > 0) {
						wnd.DrugPersonGrid.setParam('DrugComplexMnn_id', record.get('DrugComplexMnn_id'), false);
						wnd.DrugPersonGrid.setParam('Tradenames_id', record.get('TRADENAMES_id'), false);
						wnd.DrugPersonGrid.setParam('DrugFinance_id', record.get('DrugFinance_id'), false);
						wnd.DrugPersonGrid.loadData({
							globalFilters: {
								DrugRequest_id: wnd.DrugRequest_id,
								DrugComplexMnn_id: record.get('DrugComplexMnn_id'),
								Tradenames_id: record.get('TRADENAMES_id'),
								DrugFinance_id: record.get('DrugFinance_id')
							}
						});
						wnd.setDrugPersonInformationPanelData(record);
					} else {
						wnd.DrugPersonGrid.removeAll();
						wnd.setDrugPersonInformationPanelData(null);
					}
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			},
			afterDeleteRecord: function() {
				wnd.DrugListGrid.DataState = 'outdated';
			}
		});

		this.DrugListGrid = new sw.Promed.ViewFrame({
			title: lang['medikamentyi'],
			editing: true,
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh'},
				{name: 'action_print'},
				{name: 'action_save', hidden: true, disabled: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadMzDrugRequestDrugListGrid',
			region: 'center',
			id: 'mdre_DrugListGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			saveAtOnce: false,
			style: 'margin: 0px',
			stringfields: [
				{name: 'DrugListRequest_id', type: 'int', header: 'ID', key: true},
				{name: 'NTFR_Name', type: 'string', header: lang['klass_ntfr'], width: 100},
				{name: 'ATX_Code', type: 'string', header: lang['ath'], width: 100},
				{name: 'DrugComplexMnn_id', type: 'int', hidden: true},
				{id: 'autoexpand', name: 'DrugComplexMnnName_Name', header: lang['mnn'], renderer: function(v, p, record) { return record.get('isProblem') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'TRADENAMES_id', type: 'int', hidden: true},
				{name: 'Tradenames_Name', header: lang['torg_naim'], renderer: function(v, p, record) { return record.get('isProblemTorg') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'ClsDrugForms_Name', type: 'string', header: lang['lekarstvennaya_forma'], width: 160},
				{name: 'DrugComplexMnnDose_Name', type: 'string', header: lang['dozirovka'], width: 100},
				{name: 'DrugComplexMnnFas_Name', type: 'string', header: lang['fasovka'], width: 100},
                {name: 'DrugListRequest_Comment', type: 'string', header: lang['primechanie'], width: 140},
                {name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 120},
                {name: 'DrugFinance_id', type: 'int', hidden: true},
				{name: 'DrugRequestRow_Kolvo', type: 'float', header: langs('Кол-во'), width: 80, editor: new Ext.form.NumberField(), css: 'background-color: #dfe8f6;', css: 'background-color: #dfe8f6;'},
				/*
				 renderer: function(v, p, record) {v = !v && !record.get('Tradenames_Name')  ? 0: v; return !record.get('Tradenames_Name')  ? '<div style="background-color: #dfe8f6">'+ v + '</div>' : v; }},
				 result = !record.get('Tradenames_Name')  ? '<div style="background-color: #dfe8f6">'+ v + '</div>' : v; }},
				 */
				{name: 'DrugRequestRow_Price', type: 'money', header: langs('Цена'), width: 80, align: 'right'},
				{name: 'DrugRequestRow_Summa', header: langs('Сумма'), width: 80, align: 'right', renderer: function(v, p, record) { var sum = record.get('DrugRequestRow_Kolvo')*record.get('DrugRequestRow_Price'); return !Ext.isEmpty(sum) ? sum.toFixed(2) : null; }},
				{name: 'isProblem', hidden: true},
				{name: 'isProblemTorg', hidden: true}
			],
			toolbar: true,
			onLoadData: function() {
				this.DataState = 'loaded';
			},
			onAfterEdit: function(o) {
				var req_params = {
					DrugRequest_id: wnd.DrugRequest_id,
					DrugComplexMnn_id: o.record.get('DrugComplexMnn_id'),
					TRADENAMES_id: o.record.get('TRADENAMES_id'),
					DrugRequestRow_Kolvo: o.record.get('DrugRequestRow_Kolvo'),
					DrugRequestRow_Price: o.record.get('DrugRequestRow_Price'),
					DrugFinance_id: wnd.DrugFinance_id
				};
				if(wnd.isCommonPersonRegisterType()){
					req_params.DrugFinance_id = o.record.get('DrugFinance_id');
				}
				Ext.Ajax.request({
					params: req_params,
					callback: function (options, success, response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result && result.DrugRequestRow_Kolvo != undefined) {
							o.record.set('DrugRequestRow_Kolvo', result.DrugRequestRow_Kolvo);
							o.record.commit();
                            wnd.DrugGrid.DataState = 'outdated';
						} else {
                            o.record.reject();
                        }
					},
					url:'/?c=MzDrugRequest&m=saveDrugRequestRowKolvo'
				});
			},
			onBeforeEdit: function(e){
				//  Если заявка по торг. наименованию - запрещаем редактировать количество
			if (e.field = 'DrugRequestRow_Kolvo' && e.record.data.Tradenames_Name)
				return false;
				return true;
			}
		});

		this.DrugPersonGrid = new sw.Promed.ViewFrame({
			title: lang['patsientyi'],
			actions: [
				{name: 'action_add', handler: wnd.addDrugPersonGridRecord.createDelegate(wnd)},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=MzDrugRequest&m=deleteDrugRequestPersonOrder'},
				{name: 'action_refresh'},
				{name: 'action_print'},
				{name: 'action_save', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadMzDrugRequestDrugPersonGrid',
			region: 'center',
			editformclassname: 'swMzDrugRequestPersonOrderEditWindow',
			id: 'mdre_DrugPersonGrid',
			paging: false,
			saveAtOnce: false,
			style: 'margin: 0px',
			stringfields: [
				{name: 'DrugRequestPersonOrder_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', hidden: true},
				{id: 'autoexpand', name: 'Person_Fio', width: 100, header: langs('ФИО')},
				{name: 'Lpu_Information', header: langs('Прикрепление'), width: 200},
				{name: 'DrugRequestPersonOrder_OrdKolvo', header: langs('Кол-во'), width: 80, editor: new Ext.form.NumberField()},
				{name: 'MedPersonal_Name', header: langs('Врач'), width: 200},
				{name: 'DrugRequestPersonOrder_Period', header: langs('Период'), width: 200}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugRequestPersonOrder_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
				if (record.get('Person_id') > 0 && !this.readOnly) {
					this.ViewActions.action_mdre_dpg_open_emk.setDisabled(false);
				} else {
					this.ViewActions.action_mdre_dpg_open_emk.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			},
			onAfterEdit: function(o) {
				Ext.Ajax.request({
					params: {
						DrugRequestPersonOrder_id: o.record.get('DrugRequestPersonOrder_id'),
						MedPersonal_id: getGlobalOptions().medpersonal_id > 0 ? getGlobalOptions().medpersonal_id : wnd.MedPersonal_id,
						DrugRequestPersonOrder_OrdKolvo: o.record.get('DrugRequestPersonOrder_OrdKolvo'),
                        DrugRequestFirstCopy_id: wnd.DrugRequestFirstCopy_id
					},
					callback: function (options, success, response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result && result.DrugRequestPersonOrder_OrdKolvo != undefined) {
							o.record.set('DrugRequestPersonOrder_OrdKolvo', result.DrugRequestPersonOrder_OrdKolvo);
							o.record.commit();
                            wnd.PersonDrugGrid.DataState = 'outdated';
                            wnd.FirstCopyDrugGrid.DataState = 'outdated';
                            wnd.refreshDrugPersonInformationPanelData();
                            if (wnd.DrugRequestStatus_Code == '1' && Ext.isEmpty(wnd.DrugRequestFirstCopy_id)) { //1 - Начальная
                                wnd.DrugGrid.refreshRecords(null,0);
                                wnd.DrugListGrid.DataState = 'outdated';
                            }
						} else {
                            o.record.reject();
                        }
					},
					url:'/?c=MzDrugRequest&m=saveDrugRequestPersonOrderOrdKolvo'
				});
			},
			afterDeleteRecord: function() {
                if (wnd.DrugRequestStatus_Code == '1') { //1 - Начальная
                    wnd.DrugGrid.refreshRecords(null,0);
                    wnd.DrugListGrid.DataState = 'outdated';
                }
				wnd.PersonGrid.DataState = 'outdated';
				wnd.FirstCopyGrid.DataState = 'outdated';
				wnd.refreshDrugPersonInformationPanelData();
			}
		});

		this.PersonGrid = new sw.Promed.ViewFrame({
			region: 'center',
			title: lang['patsientyi'],
			actions: [
				{name: 'action_add'},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', url: '/?c=MzDrugRequest&m=deleteDrugRequestPersonOrder'},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadMzDrugRequestPersonGrid',
			editformclassname: getRegionNick() != 'ufa' ? 'swPersonSearchWindow' : 'swPersonSearchWindow4DrugRequest',
			id: 'mdre_PersonGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			saveAtOnce: false,
			style: 'margin: 0px',
			stringfields: [
				{name: 'DrugRequestPersonOrder_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', hidden: true},
				{name: 'DrugRequestPersonOrder_Count', hidden: true},
				{name: 'Person_SurName', width: 100, header: lang['familiya']},
				{name: 'Person_FirName', width: 100, header: lang['imya']},
				{name: 'Person_SecName', width: 100, header: lang['otchestvo']},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', header: lang['lpu_prikrepleniya'], width: 150},
				{name: 'LpuRegion_Name', header: lang['uchastok'], width: 80},
				{name: 'Person_IsBDZ', type:'checkbox', header: lang['bdz'], width: 35},
				{name: 'Person_IsFedLgot', type:'checkbox', header: lang['fed_lg'], width: 50},
				{name: 'Person_IsFedLgotCurr', type:'checkbox', header: lang['fed_zayavka'], width: 70},
				{name: 'Person_IsRefuse', type:'checkbox', header: lang['otkaz'], width: 50},
				{name: 'Person_IsRefuseNext', type:'checkbox', header: lang['otk_na_sl_god'], width: 80},
				{name: 'Person_IsRefuseCurr', type:'checkbox', header: lang['otk_zayavka'], width: 70},
				{name: 'Person_IsRegLgot', type:'checkbox', header: lang['reg_lg'], width: 50},
				{name: 'Person_IsRegLgotCurr', type:'checkbox', header: lang['reg_zayavka'], width: 70},
				{name: 'Person_Is7Noz', type:'checkbox', header: lang['7_noz'], width: 50},
				{name: 'Person_IsDead', type:'checkbox', header: lang['umer'], width: 50},
				{name: 'DrugRequestPersonOrder_insDT', type:'date', header: lang['vnesen'], width: 70},
				{name: 'DrugRequestPersonOrder_updDT', type:'date', header: lang['izmenen'], width: 70}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugRequestPersonOrder_id') > 0 && !this.readOnly) {
					if (record.get('DrugRequestPersonOrder_Count') > 0) {
						this.ViewActions.action_delete.setDisabled(true);
					} else {
						this.ViewActions.action_delete.setDisabled(false);
					}
				} else {
					this.ViewActions.action_delete.setDisabled(true);
				}

				wnd.PersonDrugGrid.setParam('Person_id', null, false);

				if (record.get('Person_id') > 0) {
					wnd.PersonDrugGrid.setParam('Person_id', record.get('Person_id'), false);
					wnd.PersonDrugGrid.loadData({
						globalFilters: {
							DrugRequest_id: wnd.DrugRequest_id,
							Person_id: record.get('Person_id')
						}
					});
                    this.ViewActions.action_mdre_pg_open_emk.setDisabled(false);
				} else {
					wnd.PersonDrugGrid.removeAll();
                    this.ViewActions.action_mdre_pg_open_emk.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			},
            onDblClick: function() {
                if (!this.ViewActions.action_add.isDisabled()) {
                    this.ViewActions.action_add.execute();
                }
            },
            afterDeleteRecord: function() {
                wnd.FirstCopyGrid.DataState = 'outdated';
			}
		});

		this.PersonDrugGrid = new sw.Promed.ViewFrame({
			region: 'south',
			title: lang['medikamentyi'],
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=MzDrugRequest&m=deleteDrugRequestPersonOrder'},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadMzDrugRequestPersonDrugGrid',
			editformclassname: 'swMzDrugRequestPersonOrderEditWindow',
			id: 'mdre_PersonDrugGrid',
			paging: false,
			saveAtOnce: false,
			style: 'margin: 0px',
			stringfields: [
				{name: 'DrugRequestPersonOrder_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequest_id', hidden: true},
				{name: 'NTFR_Name', type: 'string', header: langs('Класс НТФР'), width: 100},
				{name: 'ATX_Code', type: 'string', header: langs('АТХ'), width: 100},
				{id: 'autoexpand', name: 'DrugComplexMnnName_Name', header: langs('МНН'), renderer: function(v, p, record) { return record.get('isProblem') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'Tradenames_Name', header: langs('Торг. наим.'), renderer: function(v, p, record) { return record.get('isProblemTorg') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'EvnVK_id', type: 'int', header: 'EvnVK_id', hidden: true},
				{name: 'protokolVK_name', type: 'string', header: langs('Протокол ВК'), width: 120, 
					renderer: function(value, cellEl, rec){
						if (!rec.get('EvnVK_id')) {
							return value;
						}
						var result = '';
						result = '<a href="#" title="Открыть форму просмотра протокола ВК" "javascript://" onClick="Ext.getCmp(\'MzDrugRequestEditWindow\').openClinExWorkEditWindow(' + rec.get('EvnVK_id') + ');">' +rec.get('protokolVK_name') + '</a>';
						return result;
					}},
                {name: 'DrugListRequest_Comment', type: 'string', header: langs('Примечание'), width: 140},
				{name: 'ClsDrugForms_Name', type: 'string', header: langs('Лекарственная форма'), width: 160},
				{name: 'DrugComplexMnnDose_Name', type: 'string', header: langs('Дозировка'), width: 100},
				{name: 'DrugComplexMnnFas_Name', type: 'string', header: langs('Фасовка'), width: 100},
				{name: 'DrugFinance_Name', type: 'string', header: langs('Финансирование'), width: 120},
				{name: 'DrugRequestPersonOrder_OrdKolvo', type: 'float', header: langs('Кол-во'), width: 80},
				{name: 'DrugRequestPersonOrder_Kolvo', type: 'float', header: langs('Назначено'), width: 100},
                {name: 'MedPersonal_FullInf', type: 'string', header: langs('Врач'), width: 100},
				{name: 'DrugRequestPersonOrder_begDate', type: 'date', header: langs('Включен'), width: 80, align: 'right'},
				{name: 'DrugRequestPersonOrder_endDate', type: 'date', header: langs('Исключен'), width: 80, align: 'right'},
				{name: 'DrugRequestExceptionType_Name', type: 'string', header: langs('Причина исключения'), width: 100},
				{name: 'isProblem', hidden: true},
				{name: 'isProblemTorg', hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugRequestPersonOrder_id') > 0 && !this.readOnly && record.get('DrugRequest_id') == wnd.DrugRequest_id) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			},
			afterDeleteRecord: function() {
                if (wnd.DrugRequestStatus_Code == '1') { //1 - Начальная
				    wnd.DrugGrid.DataState = 'outdated';
                    wnd.DrugListGrid.DataState = 'outdated';
                }
				wnd.PersonGrid.DataState = 'outdated';
				wnd.FirstCopyGrid.DataState = 'outdated';
			}
		});

		this.FirstCopyGrid = new sw.Promed.ViewFrame({
			region: 'center',
			title: langs('Пациенты'),
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadMzDrugRequestFirstCopyGrid',
			id: 'mdre_FirstCopyGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			saveAtOnce: false,
			style: 'margin: 0px',
			stringfields: [
				{name: 'DrugRequestPersonOrder_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', hidden: true},
				{name: 'DrugRequestPersonOrder_Count', hidden: true},
				{name: 'RP', width: 100, type: 'checkbox', header: langs('РП'), width: 35},
				{name: 'LP', width: 100, type: 'checkbox', header: langs('ЛП'), width: 35},
				{name: 'Person_SurName', width: 100, header: langs('Фамилия')},
				{name: 'Person_FirName', width: 100, header: langs('Имя')},
				{name: 'Person_SecName', width: 100, header: langs('Отчество')},
				{name: 'Person_BirthDay', type: 'date', header: langs('Дата рождения'), width: 90},
				{name: 'Lpu_Nick', header: langs('ЛПУ прикрепления'), width: 150},
				{name: 'LpuRegion_Name', header: langs('Участок'), width: 80},
				{name: 'Person_IsBDZ', type:'checkbox', header: langs('БДЗ'), width: 35},
				{name: 'Person_IsFedLgot', type:'checkbox', header: langs('Фед. льг'), width: 50},
				{name: 'Person_IsFedLgotCurr', type:'checkbox', header: langs('Фед.(заявка)'), width: 70},
				{name: 'Person_IsRefuse', type:'checkbox', header: langs('Отказ'), width: 50},
				{name: 'Person_IsRefuseNext', type:'checkbox', header: langs('Отк. на сл.год'), width: 80},
				{name: 'Person_IsRefuseCurr', type:'checkbox', header: langs('Отк.(заявка)'), width: 70},
				{name: 'Person_IsRegLgot', type:'checkbox', header: langs('Рег. льг'), width: 50},
				{name: 'Person_IsRegLgotCurr', type:'checkbox', header: langs('Рег.(заявка)'), width: 70},
				{name: 'Person_Is7Noz', type:'checkbox', header: langs('7 ноз.'), width: 50},
				{name: 'Person_IsDead', type:'checkbox', header: langs('Умер'), width: 50},
				{name: 'DrugRequestPersonOrder_insDT', type:'date', header: langs('Внесен'), width: 70},
				{name: 'DrugRequestPersonOrder_updDT', type:'date', header: langs('Изменен'), width: 70}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugRequestPersonOrder_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					if (record.get('DrugRequestPersonOrder_Count') > 0) {
						this.ViewActions.action_delete.setDisabled(true);
					} else {
						this.ViewActions.action_delete.setDisabled(false);
					}
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}

				wnd.FirstCopyDrugGrid.setParam('Person_id', null, false);

				if (record.get('Person_id') > 0) {
					wnd.FirstCopyDrugGrid.setParam('Person_id', record.get('Person_id'), false);
					wnd.FirstCopyDrugGrid.loadData({
						globalFilters: {
							DrugRequest_id: wnd.DrugRequest_id,
							DrugRequestFirstCopy_id: wnd.DrugRequestFirstCopy_id,
							Person_id: record.get('Person_id')
						}
					});
				} else {
					wnd.FirstCopyDrugGrid.removeAll();
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			}
		});

		this.FirstCopyDrugGrid = new sw.Promed.ViewFrame({
			region: 'south',
			title: langs('Медикаменты'),
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadMzDrugRequestFirstCopyDrugGrid',
			id: 'mdre_FirstCopyDrugGrid',
			paging: false,
			saveAtOnce: false,
			style: 'margin: 0px',
			stringfields: [
				{name: 'DrugRequestPersonOrder_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequest_id', hidden: true},
				{name: 'ATX_Code', type: 'string', header: langs('АТХ'), width: 100},
				{id: 'autoexpand', name: 'Drug_Str', header: langs('Медикамент'), renderer: function(v, p, record) {
                    var dcm = record.get('DrugComplexMnnName_Name');
                    var tn = record.get('Tradenames_Name');
                    var br = !Ext.isEmpty(dcm) && !Ext.isEmpty(dcm) ? '<br/>' : '';

                    if (record.get('isProblem') == '1') {
                        dcm = '<div style="color: #ff0000">'+dcm+'</div>';
                    }
                    if (record.get('isProblemTorg') == '1') {
                        tn = '<div style="color: #ff0000">'+tn+'</div>';
                    }

                    return record.get('DrugRequestPersonOrder_id') > 0 ? dcm+br+tn : ''
                }},
                {name: 'LP_Kolvo', type: 'float', header: langs('Кол-во (ЛП)'), width: 80},
                {name: 'RP_Kolvo', type: 'float', header: langs('Кол-во (РП)'), width: 80},
                {name: 'Diff_Kolvo', header: langs('Дефицит'), width: 80, renderer: function(v, p, record) {
                    var lp_kol = record.get('LP_Kolvo')*1;
                    var rp_kol = record.get('RP_Kolvo')*1;
                    return rp_kol > lp_kol ? rp_kol-lp_kol : '';
                }},
				{name: 'DrugComplexMnnName_Name', hidden: true},
				{name: 'Tradenames_Name', hidden: true},
                {name: 'DrugFinance_Name', type: 'string', header: langs('Финансирование'), width: 120},
				{name: 'MedPersonal_FIO', type: 'string', header: langs('Врач'), width: 100},
                {name: 'DrugListRequest_Comment', type: 'string', header: langs('Примечание'), width: 140},
                {name: 'NTFR_Name', type: 'string', header: langs('Класс НТФР'), width: 100},
				{name: 'isProblem', hidden: true},
				{name: 'isProblemTorg', hidden: true}
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('DrugRequestPersonOrder_id') > 0 && !this.readOnly && record.get('DrugRequest_id') == wnd.DrugRequest_id) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			onLoadData: function() {
				this.DataState = 'loaded';
			},
			afterDeleteRecord: function() {
				wnd.FirstCopyGrid.DataState = 'outdated';
			}
		});

		this.DrugPersonPanel = new sw.Promed.Panel({
			layout: 'border',
			region: 'south',
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0px !important; margin: 0px;',
			style: 'padding: 0px; margin: 0px;',
			height: 170,
			border: false,
			frame: false,
			items: [
				wnd.DrugPersonGrid,
				wnd.DrugPersonInformationPanel
			]
		});

		this.DrugPanel = new sw.Promed.Panel({
			layout: 'border',
			autoScroll: true,
			bodyBorder: false,
			style: 'padding: 0px; margin: 0px;',
			border: false,
			frame: false,
			items: [
				wnd.DrugFilterPanel,
				wnd.DrugGrid,
				wnd.DrugPersonPanel
			]
		});

		this.DrugListPanel = new sw.Promed.Panel({
			layout: 'border',
			autoScroll: true,
			bodyBorder: false,
			style: 'padding: 0px; margin: 0px;',
			border: false,
			frame: false,
			items: [
				wnd.DrugListFilterPanel,
				wnd.DrugListGrid
			]
		});

		this.PersonPanel = new sw.Promed.Panel({
			layout: 'border',
			autoScroll: true,
			bodyBorder: false,
			style: 'padding: 0px; margin: 0px;',
			border: false,
			frame: false,
			items: [
				wnd.PersonFilterPanel,
				wnd.PersonGrid,
				wnd.PersonDrugGrid
			]
		});

		this.FirstCopyPanel = new sw.Promed.Panel({
			layout: 'border',
			autoScroll: true,
			bodyBorder: false,
			style: 'padding: 0px; margin: 0px;',
			border: false,
			frame: false,
			items: [
				wnd.FirstCopyFilterPanel,
				wnd.FirstCopyGrid,
				wnd.FirstCopyDrugGrid
			]
		});

		this.DrugRequestTabs = new Ext.TabPanel({
			id: 'mdre_DrugRequestTabsPanel',
			autoScroll: true,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'center',
			enableTabScroll: true,
			height: 170,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[{
				id: 'mdre_drug',
				title: lang['medikamentyi_zayavki'],
				layout: 'fit',
				border: false,
				items: [
					wnd.DrugPanel
				]
			}, {
				id: 'mdre_drug_list',
				title: lang['shablon_zayavki'],
				layout: 'fit',
				border: false,
				items: [
					wnd.DrugListPanel
				]
			}, {
				id: 'mdre_person',
				title: lang['personalnaya_raznaryadka'],
				layout: 'fit',
				border: false,
				items: [
					wnd.PersonPanel
				]
			}, {
				id: 'mdre_first_copy',
				title: getRegionNick() == 'ufa' ? langs('Прогноз регионального лекарственного обеспечения') : langs('Аналитика персональной потребности'),
				layout: 'fit',
				border: false,
				items: [
					wnd.FirstCopyPanel
				]
			}],
			listeners: {
				tabchange: wnd.onDrugRequestTabsChange.createDelegate(wnd)
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					this.ownerCt.changeDrugRequestStatus();
				},
				iconCls: 'actions16',
				text: lang['sformirovat']
			}, {
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items:[
				this.DrugRequestInformationPanel,
				this.DrugRequestTabs
			]
		});
		sw.Promed.swMzDrugRequestEditWindow.superclass.initComponent.apply(this, arguments);
	}
});