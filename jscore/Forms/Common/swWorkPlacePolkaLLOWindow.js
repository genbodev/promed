/**
* АРМ «ЛЛО Поликлиника» - такое неадекватное название придумал автор задачи 12010
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      23.10.2012
*/

sw.Promed.swWorkPlacePolkaLLOWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	title: '',
	maximized: true,
	shim: false,
	plain: true,
	id: 'swWorkPlacePolkaLLOWindow',
	listeners: {
		'hide': function() {
			clearInterval(this.interval);
		}
	},
	checkPersonAddressAndDocData: function(options) { //проверка наличия  у пациента информации об адресе и документе
		var wnd = this;
		var person_id = null;
		var server_id = null;
		var callback = Ext.emptyFn;

		if (options) {
			if (Ext.isEmpty(options.Person_id) || Ext.isEmpty(options.Server_id)) {
				var vizit_grid = this.GridPanel.getGrid();
				var vizit = vizit_grid.getSelectionModel().getSelected();

				if (vizit) {
					person_id = vizit.get('Person_id');
					server_id = vizit.get('Server_id');
				}
			} else {
				person_id = options.Person_id;
				server_id = options.Server_id;
			}

			if (options.callback) {
				callback = options.callback;
			}
		}

		if (!Ext.isEmpty(person_id) && !Ext.isEmpty(server_id)) {
			//проверка
			wnd.getPersonAddressAndDocData(person_id, function(check_result) {
 				if (check_result.data_is_exists) {
					//если проверка пройдена вызываем callback
					callback();
				} else {
 					//готовим сообщение об ошибке
					var empty_data_string = "";
					if (check_result && check_result.empty_data_list && check_result.empty_data_list.length > 0) {
						empty_data_string = " ("+check_result.empty_data_list.join(", ")+")";
					}
					var error_message = langs("Выписка рецепта невозможна - не заполнены данные адреса и/или документа, удостоверяющего личность пациента")+empty_data_string+". "+langs("Перейти в форму редактирования пациента и продолжить выписку рецепта?");

					//если проверка не пройдена отображаем предупреждение и открываем окно для редактирования данных пациента
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if (buttonId == 'yes') {
								getWnd('swPersonEditWindow').show({
									Person_id: person_id,
									Server_id: server_id,
									checkAdress: true,
									callback: function(callback_data) {
										//после сохранения данных пациента переходим к повтороной проверке
										wnd.checkPersonAddressAndDocData({
											Person_id: person_id,
											Server_id: server_id,
											callback: callback
										});
									}
								});
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: error_message,
						title: langs('Вопрос')
					});
				}
			});
		} else {
			sw.swMsg.alert(langs('Ошибка'), langs('Не удалось определить пациента'));
		}
	},
	getPersonAddressAndDocData: function(person_id, callback) {
		Ext.Ajax.request({
			params: {
				Person_id: person_id
			},
			url: '/?c=EvnRecept&m=getPersonAddressAndDocData',
			callback: function(options, success, response) {
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var data_is_exists = false;
					var empty_data_list = new Array();

					if (
						!Ext.isEmpty(response_obj.Address_Address) &&
						!Ext.isEmpty(response_obj.Document_Num) &&
						!Ext.isEmpty(response_obj.OrgDep_id) &&
						!Ext.isEmpty(response_obj.Document_begDate) &&
						(
							!Ext.isEmpty(response_obj.KLCity_id) ||
							!Ext.isEmpty(response_obj.KLSubRgn_id)
						)
					) {
						data_is_exists = true;
					} else {
						if (Ext.isEmpty(response_obj.Address_Address)) {
							empty_data_list.push(langs('адрес регистрации или адрес проживания'));
						} else if (Ext.isEmpty(response_obj.KLCity_id) && Ext.isEmpty(response_obj.KLSubRgn_id)) {
							empty_data_list.push(langs('район или город регистрации или проживания'));
						}
						if (Ext.isEmpty(response_obj.Document_Num)) {
							empty_data_list.push(langs('номер документа'));
						}
						if (Ext.isEmpty(response_obj.OrgDep_id)) {
							empty_data_list.push(langs('кем выдан документ'));
						}
						if (Ext.isEmpty(response_obj.Document_begDate)) {
							empty_data_list.push(langs('дата выдачи документа'));
						}
					}

					if (typeof callback == 'function') {
						callback({
							data_is_exists: data_is_exists,
							empty_data_list: empty_data_list
						});
					}
				} else {
					sw.swMsg.alert(langs('Ошибка'), langs('При проверке данных пациента произошла ошибка'));
				}
			}
		});
	},
	getReceptGridPanel: function() {
		if (getRegionNick() == 'msk') {
			return this.ReceptGridPanel;
		}
		else {
			return this.GridPanel;
		}
	},
    doSearch: function(mode){
        if (mode == "period"){
            var dateFrom = new Date(this.dateMenu.getValue1()),
                dateTo = new Date(this.dateMenu.getValue2());

            if (((dateTo - dateFrom) / 86400000) >= 31){
                Ext.Msg.alert('Ошибка', 'заданный период не может превышать 31 день');
                return false;
            }
        }

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
        params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
        params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');

        if (this.FilterPanel.getForm().findField('pmUser_insID')) {
            params.pmUser_insID = this.FilterPanel.getForm().findField('pmUser_insID').getValue();
        }

        this.GridPanel.removeAll({clearAll:true});
        this.GridPanel.loadData({globalFilters: params});
    },
	doReset: function() {
		this.FilterPanel.getForm().reset();

		if (!Ext.isEmpty(this.FilterPanel.getForm().findField('pmUser_insID'))) {
			this.FilterPanel.getForm().findField('pmUser_insID').setValue(getGlobalOptions().pmuser_id);

			if ( isDLOUser() ) {
				this.FilterPanel.getForm().findField('pmUser_insID').disable();
			}
			else {
				this.FilterPanel.getForm().findField('pmUser_insID').enable();
			}
		}
	},
	show: function() {
        this.doReset();

		sw.Promed.swWorkPlacePolkaLLOWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.userMedStaffFact = arguments[0];

		if(getRegionNick() == 'msk' && !this.GridPanel.getAction('action_request')){
			this.GridPanel.addActions({
				disabled: true,
				name: 'action_request',
				iconCls: 'actions16',
				text: 'Создать запрос',
				tooltip: 'Создать запрос',
				handler: function() {
					this.addPersonPrivilegeReq();
				}.createDelegate(this)
			});
		}

		if(!this.getReceptGridPanel().getAction('action_undo_delete') && getRegionNick() != 'msk'){
			this.getReceptGridPanel().addActions({
				disabled: true,
				handler: function() {
					this.UndoDeleteEvnRecept();
				}.createDelegate(this),
				name: 'action_undo_delete',
				text: 'Удалить пометку к удалению '
			},3);
		}

		this.searchParams = { start: 0, limit: this.getReceptGridPanel().pageSize };
		this.doSearch();

		if (sw.openLLOFromEMIASData) {
			var params = sw.openLLOFromEMIASData;
			sw.openLLOFromEMIASData = null;
			win.getLoadMask('Идентификация пациента и создание посещения').show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					win.getLoadMask().hide();
					win.doSearch();
				},
				params: params,
				url: '/?c=EvnVizit&m=createEvnVizitPLForLLO'
			});
		}

		if ( this.interval ) {
			clearInterval(this.interval);
		}

		this.interval = setInterval(function(){
			this.searchParams = { start: 0, limit: this.getReceptGridPanel().pageSize };
			this.doSearch();
		}.bind(this), 3600000);
	},
	enableDefaultActions: true,

	addPersonPrivilegeReq: function() {
		var viewframe = this.GridPanel;
		var record = viewframe.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Person_id')) ) {
			return false;
		}

		getWnd('swPersonPrivilegeReqEditWindow').show({
			action: 'add',
			callback: function() {
				viewframe.refreshRecords(null, 0);
			},
			userMedStaffFact: this.userMedStaffFact,
			Person_id: record.get('Person_id')
		});
	},
	openEvnReceptEditWindow: function(action, MorbusType_SysNick) {
		var win = this;

		if ( getRegionNick() == 'msk' ) {
			if ( action == 'add' ) {
				this.checkPersonAddressAndDocData({
					callback: function() {
						win._openEvnReceptEditWindowMsk(action);
					}
				});
			} else {
				win._openEvnReceptEditWindowMsk(action);
			}
			return true;
		}

		if ( action != 'add' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
			return false;
		}

		var that = this;
		var grid = that.getReceptGridPanel().ViewGridPanel;
		var params = {
			EvnRecept_setDate: getGlobalOptions().curDate,
			LpuSection_id: getGlobalOptions().CurLpuSection_id || null,
			MedPersonal_id: getGlobalOptions().CurMedStaffFact_id || null
		};
		var wnd;

		params.action = action;
		params.callback = function(data) {
			if ( data && data.EvnReceptData ) {
				that.getReceptGridPanel().getAction('action_refresh').execute();
				setGridRecord(grid, data.EvnReceptData);
			}
		};

		if ( action == 'add' ) {
			if(getGlobalOptions().drug_spr_using == 'dbo')
				wnd = 'swEvnReceptEditWindow';
			else
				wnd = 'swEvnReceptRlsEditWindow';

			if ( getWnd(wnd).isVisible() ) {
				sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования рецепта уже открыто'));
				return false;
			}

			getWnd('swPersonSearchWindow').show({
				onClose: function () {
					that.getReceptGridPanel().getAction('action_refresh').execute();
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.onHide = function() {
						// TODO: Продумать использование getWnd в таких случаях
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
						that.getReceptGridPanel().getAction('action_refresh').execute();
					};
					params.Person_id =  person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;

					getWnd(wnd).show( params );
				},
				searchMode: 'all'
			});
		} else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			if (!Ext.isEmpty(selected_record.get('Drug_id'))) {
				wnd = 'swEvnReceptEditWindow'; // для Перми
			} else if (!Ext.isEmpty(selected_record.get('Drug_rlsid')) || !Ext.isEmpty(selected_record.get('DrugComplexMnn_id'))) {
				wnd = 'swEvnReceptRlsEditWindow'; // для Уфы
			} else {
				sw.swMsg.alert("Ошибка", "Не выбран медикамент в рецепте"); // так не может быть
				return false;
			}

			if ( getWnd(wnd).isVisible() ) {
				sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования рецепта уже открыто'));
				return false;
			}

			var evn_recept_id = selected_record.get('EvnRecept_id');
			var person_id = selected_record.get('Person_id');
			var person_evn_id = selected_record.get('PersonEvn_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_recept_id && person_id && person_evn_id && server_id >= 0 ) {
				params.EvnRecept_id = evn_recept_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.Server_id = server_id;

				getWnd(wnd).show( params );
			}
		}
	},
	_openEvnReceptEditWindowMsk: function(action) {
		if ( action != 'add' && action != 'view' ) {
			return false;
		}

		var
			params = {},
			that = this,
			receptGrid = that.ReceptGridPanel.getGrid(),
			vizitGrid = that.GridPanel.getGrid(),
			wnd;

		if (
			(action == 'add' && !vizitGrid.getSelectionModel().getSelected())
			|| (action == 'view' && !receptGrid.getSelectionModel().getSelected())
		) {
			return false;
		}

		var vizit = vizitGrid.getSelectionModel().getSelected();

		params.action = action;
		params.callback = function(data) {
			if ( data && data.EvnReceptData ) {
				that.ReceptGridPanel.getAction('action_refresh').execute();
				setGridRecord(receptGrid, data.EvnReceptData);
			}
		};

		if ( action == 'add' ) {
			if (vizit.get('Person_IsFedLgot') === 'false' && vizit.get('Person_IsRegLgot') === 'false') {
				this.addPersonPrivilegeReq();
				return false;
			}
			
			if(getGlobalOptions().drug_spr_using == 'dbo')
				wnd = 'swEvnReceptEditWindow';
			else
				wnd = 'swEvnReceptRlsEditWindow';

			if ( getWnd(wnd).isVisible() ) {
				sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования рецепта уже открыто'));
				return false;
			}

			params.onHide = function() {
				that.ReceptGridPanel.getAction('action_refresh').execute();
			};
			params.Person_id = vizit.get('Person_id');
			params.PersonEvn_id = vizit.get('PersonEvn_id');
			params.Server_id = vizit.get('Server_id');
			params.LpuSection_id = vizit.get('LpuSection_id');
			params.MedPersonal_id = vizit.get('MedStaffFact_id');
			params.Diag_id = vizit.get('Diag_id');
			params.EvnRecept_pid = vizit.get('EvnVizitPL_id');
			params.EvnRecept_setDate = vizit.get('EvnVizitPL_setDate');

			getWnd(wnd).show(params);
		}
		else {
			if ( !receptGrid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = receptGrid.getSelectionModel().getSelected();

			if (!Ext.isEmpty(selected_record.get('Drug_id'))) {
				wnd = 'swEvnReceptEditWindow'; // для Перми
			}
			else if (!Ext.isEmpty(selected_record.get('Drug_rlsid')) || !Ext.isEmpty(selected_record.get('DrugComplexMnn_id'))) {
				wnd = 'swEvnReceptRlsEditWindow'; // для Уфы
			}
			else {
				sw.swMsg.alert("Ошибка", "Не выбран медикамент в рецепте"); // так не может быть
				return false;
			}

			if ( getWnd(wnd).isVisible() ) {
				sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования рецепта уже открыто'));
				return false;
			}

			var evn_recept_id = selected_record.get('EvnRecept_id');
			var person_id = selected_record.get('Person_id');
			var person_evn_id = selected_record.get('PersonEvn_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_recept_id && person_id && person_evn_id && server_id >= 0 ) {
				params.EvnRecept_id = evn_recept_id;
				params.onHide = function() {
					that.ReceptGridPanel.getAction('action_refresh').execute();
				};
				params.Person_id = person_id;
				params.PersonEvn_id = person_evn_id;
				params.Server_id = server_id;

				getWnd(wnd).show(params);
			}
		}
	},

	printEvnRecept: function() {
		var grid = this.getReceptGridPanel().ViewGridPanel;

		var record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('EvnRecept_id') ) {
			return false;
		}
		if(record.get('ReceptType_Code') == 1){
			sw.swMsg.alert(langs('Ошибка'), 'Для рецептов, выписанных на бланке, печатная форма не предусмотрена');
			return false;
		}
		if(record.get('Recept_MarkDeleted')=='true' && getRegionNick() != 'msk'){
			sw.swMsg.alert(langs('Ошибка'), 'Рецепт удален и не может быть распечатан');
			return false;
		}
		if (getRegionNick() != 'kz' && record.get('ReceptType_Code') == 3 && !record.get('EvnRecept_IsSigned')) {
			sw.swMsg.alert(langs('Ошибка'), 'Рецепт в форме электронного документа можно распечатать после подписания рецепта ЭП. Подпишите рецепт и повторите печать.');
			return false;
		}
		if (!Ext.isEmpty(record.get('Drug_id'))) {
			wnd = 'swEvnReceptEditWindow'; // для Перми
		}
		else if (!Ext.isEmpty(record.get('Drug_rlsid')) || !Ext.isEmpty(record.get('DrugComplexMnn_id'))) {
			wnd = 'swEvnReceptRlsEditWindow'; // для Уфы
		}
		var ReceptForm_id = record.get('ReceptForm_id')*1;
		var ReceptForm_Code = record.get('ReceptForm_Code');
		var evn_recept_set_date = record.get('EvnRecept_setDate').format('Y-m-d');
		var evn_recept_id = record.get('EvnRecept_id');
		var that = this;
		saveEvnReceptIsPrinted({
			allowQuestion: false
			,callback: function(success) {
				if ( success == true ) {
					record.set('EvnRecept_IsPrinted', 'true');
					record.commit();
					if (Ext.globalOptions.recepts.print_extension == 3) {
						if(ReceptForm_Code != langs('1-МИ'))
							window.open(C_EVNREC_PRINT_DS, '_blank');
						window.open(C_EVNREC_PRINT + '&EvnRecept_id=' + evn_recept_id, '_blank');
					} else {
						Ext.Ajax.request({
							url: '/?c=EvnRecept&m=getPrintType',
							callback: function(options, success, response) {
								if (success) {
									var result = Ext.util.JSON.decode(response.responseText);
									var PrintType = '';
									switch(result.PrintType) {
										case '1':
											PrintType = 2;
											break;
										case '2':
											PrintType = 3;
											break;
										case '3':
											PrintType = '';
											break;
									}

									//в зависимости от окна выполняем печать
									if (wnd === 'swEvnReceptEditWindow') {
										switch (ReceptForm_id) {
											case 2: //1-МИ
												if (result.CopiesCount == 1) {
													printBirt({
														'Report_FileName': 'EvnReceptPrint4_1MI.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id,
														'Report_Format': 'pdf'
													});
												} else {
													if (PrintType == '') {
														printBirt({
															'Report_FileName': 'EvnReceptPrint1_1MI.rptdesign',
															'Report_Params': '&paramEvnRecept=' + evn_recept_id,
															'Report_Format': 'pdf'
														});
													} else {
														printBirt({
															'Report_FileName': 'EvnReceptPrint' + PrintType + '_1MI.rptdesign',
															'Report_Params': '&paramEvnRecept=' + evn_recept_id,
															'Report_Format': 'pdf'
														});
													}
												}
												break;
											case 9: //148-1/у-04(л)
                                                if (getRegionNick() == 'msk') {
                                                    printBirt({
                                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2020.rptdesign',
                                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                        'Report_Format': 'pdf'
                                                    });
                                                } else {
                                                    //игнорируем настройки и печатаем сразу обе стороны
                                                    printBirt({
                                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019.rptdesign',
                                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                        'Report_Format': 'pdf'
                                                    });
                                                }
                                                printBirt({
                                                    'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                                    'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                    'Report_Format': 'pdf'
                                                });
												break;
											default:
												var ReportName = 'EvnReceptPrint' + PrintType;
												var ReportNameOb = 'EvnReceptPrintOb' + PrintType;
												if (result.CopiesCount == 1) {
													if (evn_recept_set_date >= '2016-07-30') {
														ReportName = 'EvnReceptPrint4_2016_new';
													} else if (evn_recept_set_date >= '2016-01-01') {
														ReportName = 'EvnReceptPrint4_2016';
													} else {
														ReportName = 'EvnReceptPrint2_2015';
													}
													ReportNameOb = 'EvnReceptPrintOb2_2015';
												} else {
													if (evn_recept_set_date >= '2016-07-30') {
														ReportName = ReportName + '_2016_new';
													} else if (evn_recept_set_date >= '2016-01-01') {
														ReportName = ReportName + '_2016';
													}
												}
												if (Ext.globalOptions.recepts.print_extension == 1) {
													printBirt({
														'Report_FileName': ReportNameOb + '.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
														'Report_Format': 'pdf'
													});
												}
												if (result.server_port != null) {
													printBirt({
														'Report_FileName': ReportName + '.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
														'Report_Format': 'pdf'
													});
												} else {
													printBirt({
														'Report_FileName': ReportName + '.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedProto=' + result.server_http,
														'Report_Format': 'pdf'
													});
												}
												break;
										}
									}
									else {
										switch (ReceptForm_id*1) {
											case 2: //1-МИ
												if(PrintType=='') {
													printBirt({
														'Report_FileName': 'EvnReceptPrint1_1MI.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id,
														'Report_Format': 'pdf'
													});
												} else {
													printBirt({
														'Report_FileName': 'EvnReceptPrint' + PrintType + '_1MI.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id,
														'Report_Format': 'pdf'
													});
												}
												break;
											case 9: //148-1/у-04(л)
												if (getRegionNick() == 'msk') {
													printBirt({
														'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2020.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id,
														'Report_Format': 'pdf'
													});
												} else {
                                                    //игнорируем настройки и печатаем сразу обе стороны
                                                    printBirt({
                                                        'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019.rptdesign',
                                                        'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                        'Report_Format': 'pdf'
                                                    });
                                                }
                                                printBirt({
                                                    'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
                                                    'Report_Params': '&paramEvnRecept=' + evn_recept_id,
                                                    'Report_Format': 'pdf'
                                                });
												break;
											case 10: //148-1/у-04 (к)
												//игнорируем настройки и печатаем сразу обе стороны
												printBirt({
													'Report_FileName': 'EvnReceptPrint_148_1u04k_2InA4_2019.rptdesign',
													'Report_Params': '&paramEvnRecept=' + evn_recept_id,
													'Report_Format': 'pdf'
												});
												printBirt({
													'Report_FileName': 'EvnReceptPrint_148_1u04_2InA4_2019Oborot.rptdesign',
													'Report_Params': '&paramEvnRecept=' + evn_recept_id,
													'Report_Format': 'pdf'
												});
												break;
											case 1: //148-1/у-04(л), 148-1/у-06(л)
												if (getRegionNick() == 'msk') {
													printBirt({
														'Report_FileName': 'EvnReceptPrint_148_1u04_4InA4_2019.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id,
														'Report_Format': 'pdf'
													});
													break; //в пределах условия для того, чтобы в других регионах выполнение проваливалось в дефолтную секцию
												}
											default:
												var ReportName = 'EvnReceptPrint' + PrintType;
												var ReportNameOb = 'EvnReceptPrintOb' + PrintType;
												if (result.CopiesCount == 1) {
													if (evn_recept_set_date >= '2016-07-30') {
														ReportName = 'EvnReceptPrint4_2016_new';
													} else if(evn_recept_set_date >= '2016-01-01') {
														ReportName = 'EvnReceptPrint4_2016';
													} else {
														ReportName = 'EvnReceptPrint2_2015';
													}
													ReportNameOb = 'EvnReceptPrintOb2_2015';
												} else {
													if (evn_recept_set_date >= '2016-07-30') {
														ReportName = ReportName + '_2016_new';
													} else if (evn_recept_set_date >= '2016-01-01') {
														ReportName = ReportName + '_2016';
													}
												}
												if (Ext.globalOptions.recepts.print_extension == 1) {
													printBirt({
														'Report_FileName': ReportNameOb + '.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
														'Report_Format': 'pdf'
													});
												}
												if (result.server_port != null) {
													printBirt({
														'Report_FileName': ReportName + '.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedPort=' + result.server_port + '&paramProMedProto=' + result.server_http,
														'Report_Format': 'pdf'
													});
												} else {
													printBirt({
														'Report_FileName': ReportName + '.rptdesign',
														'Report_Params': '&paramEvnRecept=' + evn_recept_id + '&paramProMedProto=' + result.server_http,
														'Report_Format': 'pdf'
													});
												}
												break;
										}
									}
									this.getReceptGridPanel().getAction('action_refresh').execute();
								}
							}.createDelegate(that)
						});
					}

				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при сохранении признака распечатывания рецепта');
				}
			}.createDelegate(this)
			,Evn_id: evn_recept_id
		});
	},
	//получить данные рецепта выбранной записи для копирования
	getReceptDataForCopy: function(record, callback) {
		var evnRecept_id = record.get('EvnRecept_id');

		if (!(evnRecept_id && evnRecept_id>0)) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		Ext.Ajax.request({
			params: {
				EvnRecept_id: evnRecept_id
			},
			url: C_EVNREC_LOAD,
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!Ext.isEmpty(response_obj.success) && !response_obj.success) {
						sw.swMsg.alert('Ошибка', 'При получении данных рецепта');
						return false;
					}
					var recept_data = response_obj[0];

					//меняем параметры
					recept_data['EvnRecept_id'] = 0;
					recept_data['EvnRecept_IsSigned'] = 1;
					recept_data['EvnRecept_IsPrinted'] = 1;
					recept_data['isRLS'] = 1;
					recept_data['PersonEvn_id'] = record.get('PersonEvn_id');
					recept_data['Server_id'] = record.get('Server_id');

					if (typeof(callback) == 'function') {
						callback(recept_data);
					}
					return recept_data;
				}
				else {
					sw.swMsg.alert('Ошибка', 'При получении данных рецепта');
					return false;
				}
			}.createDelegate(this)
		});
	},
	//заменить серию и номер в переданном массиве данных рецепта
	setNewReceptNum: function (recept_data, callback) {
		var recept_new_data = recept_data;

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		Ext.Ajax.request({
			url: C_RECEPT_NUM,
			params: recept_new_data,
			callback: function(options, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!Ext.isEmpty(response_obj.success) && !response_obj.success) {
						sw.swMsg.alert('Ошибка', 'При получении серии и номера для нового рецепта');
						return false;
					}

					recept_new_data['EvnRecept_Num'] = response_obj.EvnRecept_Num;
					if (response_obj.EvnRecept_Ser) {
						recept_new_data['EvnRecept_Ser'] = response_obj.EvnRecept_Ser;
					}

					if (typeof(callback) == 'function') {
						callback(recept_new_data);
					}
					return recept_new_data;
				}
				else {
					sw.swMsg.alert('Ошибка', 'При получении серии и номера для нового рецепта');
					return false;
				}
			}.createDelegate(this)
		});
	},
	//сохранить рецепт из переданного массива данных рецепта
	saveNewEvnRecept: function(recept_new_data, callback) {
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		Ext.Ajax.request({
			params: recept_new_data,
			url: C_EVNREC_SAVE_RLS,
			callback: function(options, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!Ext.isEmpty(response_obj.success) && !response_obj.success) {
						sw.swMsg.alert('Ошибка', 'При сохранении нового рецепта');
						return false;
					}
					//получаем номер сохраненного рецепта
					var evn_recept_id_new = response_obj.EvnRecept_id;

					if (typeof(callback) == 'function') {
						callback(evn_recept_id_new);
					}
					return evn_recept_id_new;
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении нового рецепта');
					return false;
				}
			}.createDelegate(this)
		});
	},
	//удалить рецепта по ID после копирования с причной "перепечать бланка"
	deleteEvnReceptAfterCopy: function(evnRecept_id, callback) {
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		Ext.Ajax.request({
			params: {
				EvnRecept_id: evnRecept_id
				, ReceptRemoveCauseType_id: 10
				, DeleteType: 1
			},
			url: C_EVNREC_DEL,
			callback: function (options, success, response) {
				loadMask.hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (!Ext.isEmpty(response_obj.success) && !response_obj.success) {
						sw.swMsg.alert('Ошибка', 'При '+(getRegionNick()=='msk'?'аннулировании':'удалении')+' старого рецепта произошла ошибка');
						return false;
					}

					if (typeof(callback) == 'function') {
						callback();
					}
					return true;
				}
				else {
					sw.swMsg.alert('Ошибка', 'При '+(getRegionNick()=='msk'?'аннулировании':'удалении')+' старого рецепта произошла ошибка');
					return false;
				}
				return success;
			}.createDelegate(this)
		});
	},
	rePrintEvnRecept: function () {
		var that = this,
			grid = that.getReceptGridPanel().ViewGridPanel,
            current_date = new Date().format('Y-m-d'),
			record = grid.getSelectionModel().getSelected();

		if ( !record || !record.get('EvnRecept_id') ) {
			return false;
		}
		if(record.get('ReceptType_Code') == 1){
			sw.swMsg.alert(langs('Ошибка'), 'Для рецептов, выписанных на бланке, печатная форма не предусмотрена');
			return false;
		}
		if (getRegionNick() != 'kz' && record.get('ReceptType_Code') == 3 && !record.get('EvnRecept_IsSigned')) {
			sw.swMsg.alert(langs('Ошибка'), 'Рецепт в форме электронного документа можно распечатать после подписания рецепта ЭП. Подпишите рецепт и повторите печать.');
			return false;
		}
		var currentEvnRecept_id = record.get('EvnRecept_id');
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		that.getReceptDataForCopy(record, function(recept_data) {
			that.setNewReceptNum(recept_data, function(recept_new) {
                if ((current_date >= '2020-01-01') && (recept_data['ReceptForm_id'] == 1)) {
                    recept_new['ReceptForm_id'] = 9;
                }
				that.saveNewEvnRecept(recept_new, function(evn_recept_id_new) {
					that.deleteEvnReceptAfterCopy(currentEvnRecept_id, function() {
						loadMask.show();
						//затем обновляем грид
						grid.getStore().reload({
							callback: function() {
								loadMask.hide();
								//ищем в гриде новую запись и селектим ее
								var index = grid.getStore().findBy(function(record) { return record.get('EvnRecept_id') == evn_recept_id_new; } );
								grid.getSelectionModel().selectRow(index);
								//печатаем
								that.printEvnRecept();
							}
						});
					});
				});
			});
		});
	},

	deleteEvnRecept: function() {
    	var that = this;
		var grid = that.getReceptGridPanel().ViewGridPanel;

		if ( !grid || !grid.getSelectionModel().getSelected()) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		if ( !selected_record.get('EvnRecept_id') ) {
			return false;
		}

		var DeleteType = 0; //Пометка к удалению
		if (isSuperAdmin() || isLpuAdmin() || isUserGroup('ChiefLLO') || getRegionNick() == 'msk') {
			DeleteType = 1;
		}
		else
		{
			if (selected_record.get('ReceptType_Code') == 2 && selected_record.get('EvnRecept_IsSigned') == 'false' && selected_record.get('EvnRecept_IsPrinted') == 'false') { //Если тип рецепта - "На листе" и рецепт не подписан
				DeleteType = 1; //Удаление
			}
		}
		if(DeleteType == 0 && selected_record.get('Recept_MarkDeleted')=='true'){ //Не даем дважды помечать к удалению
			sw.swMsg.alert(langs('Ошибка'), 'Рецепт уже помечен к удалению');
			return false;
		}
		if(selected_record.get('EvnRecept_deleted') == 2){
			sw.swMsg.alert(langs('Ошибка'), 'Рецепт уже ' + (getRegionNick() == 'msk' ? 'аннулирован' : 'удален'));
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					getWnd('swEvnReceptDeleteWindow').show({
						callback: function() {
							that.getReceptGridPanel().getAction('action_refresh').execute();
						},
						EvnRecept_id: selected_record.get('EvnRecept_id'),
						DeleteType: DeleteType,
						onHide: function() {

						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs((getRegionNick() == 'msk' ? 'Аннулировать' : 'Удалить') + ' рецепт?'),
			title: langs('Вопрос')
		});
	},
	UndoDeleteEvnRecept: function(){
		if ( getRegionNick() == 'msk' ) {
			return false;
		}

		var grid = this.getReceptGridPanel().ViewGridPanel;

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		if ( !record.get('EvnRecept_id') ) {
			return false;
		}
		var that = this;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							that.getReceptGridPanel().getAction('action_refresh').execute();
						}.createDelegate(that),
						params: {
							EvnRecept_id: record.get('EvnRecept_id')
						},
						url: '/?c=EvnRecept&m=UndoDeleteEvnRecept'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Вы действительно желаете восстановить рецепт?',
			title: langs('Вопрос')
		});
	},
	initComponent: function()
	{
		var form = this;

		this.buttonPanelActions = {
			action_accessibility: {
				menuAlign: 'tr',
					text: langs('Льготники'),
					tooltip: langs('Льготники'),
					iconCls: 'lgot32',
					menu: new Ext.menu.Menu({
					items: [{
						text: langs('Регистр льготников: Список'),
						tooltip: langs('Просмотр льгот по категориям'),
						iconCls : 'lgot-tree16',
						handler: function() {
							getWnd('swLgotTreeViewWindow').show();
						}
					}, {
						text: MM_DLO_LGOTSEARCH,
						tooltip: langs('Поиск льготников'),
						iconCls : 'lgot-search16',
						handler: function() {
							getWnd('swPrivilegeSearchWindow').show();
						}
					},
						'-',
						{
							text: MM_DLO_UDOSTLIST,
							tooltip: langs('Просмотр удостоверений'),
							iconCls : 'udost-list16',
							handler: function() {
								getWnd('swUdostViewWindow').show();
							}
						}, {
                            text: langs('Запросы на включение в льготные регистры'),
                            tooltip: langs('Запросы на включение в льготные регистры'),
                            iconCls : 'lgot-tree16',
                            hidden: getGlobalOptions().person_privilege_add_source != 1, //1 - Включение в регистр выполняется по запросу в ситуационный центр
                            handler: function() {
                                getWnd('swPersonPrivilegeReqViewWindow').show({userMedStaffFact: getWnd('swWorkPlacePolkaLLOWindow').userMedStaffFact});
                            }
                        }]
				})
			},
			action_findrecepr: {
				text: langs('Рецепты'),
					tooltip: langs('Рецепты'),
					iconCls: 'recept-search32',
					menuAlign: 'tr',
					menu: new Ext.menu.Menu({
					items: [{
						text: langs('Поиск рецептов'),
						tooltip: langs('Поиск рецептов'),
						iconCls: 'receipt-search16',
						handler: function() {
							getWnd('swEvnReceptSearchWindow').show();
						}
					}, {
						tooltip: 'Печать бланков рецептов',
						text: 'Печать бланков рецептов',
						iconCls: 'receipt-new16',
						hidden: getRegionNick() == 'msk',
						handler: function() {
							getWnd('swReceiptBlankPrintWindow').show();
						}
					}]
				})
			},
			action_evnReceptInCorrectFind: {
				text: langs('Журнал отсрочки'),
					tooltip: langs('Журнал отсрочки'),
					iconCls : 'receipt-incorrect32',
					handler: function()	{
					getWnd('swReceptInCorrectSearchWindow').show();
				}
			},
			action_drugs: {
				text: "Остатки медикаментов",
					tooltip: "Остатки медикаментов",
					menuAlign: 'tr',
					iconCls: 'rls-torg32',
					hidden: !getRegionNick().inlist(['perm','ufa']),
					menu: new Ext.menu.Menu({
					items: [{
						text: MM_DLO_MEDAPT,
						tooltip: langs('Работа с остатками медикаментов по аптекам'),
						iconCls : 'drug-farm16',
						handler: function() {
							getWnd('swDrugOstatByFarmacyViewWindow').show();
						}
					}, {
						text: MM_DLO_MEDNAME,
						tooltip: langs('Работа с остатками медикаментов по наименованию'),
						iconCls : 'drug-name16',
						handler: function() {
							getWnd('swDrugOstatViewWindow').show();
						}
					}, {
						text: MM_DLO_MEDSKLAD,
						tooltip: langs('Работа с остатками медикаментов на аптечном складе'),
						iconCls : 'drug-sklad16',
						handler: function() {
							getWnd('swDrugOstatBySkladViewWindow').show();
						}
					}
					]
				})
			},
			action_drugrequestview: {
				text: langs('Заявки ЛЛО'),
					tooltip: langs('Заявки ЛЛО'),
					iconCls: 'mp-drugrequest32',
					menuAlign: 'tr',
					menu: new Ext.menu.Menu({
					items: [{
						tooltip: langs('Заявка ЛЛО'),
						text: langs('Заявка ЛЛО'),
						iconCls : 'view16',
						handler: function() {
							getWnd('swMzDrugRequestSelectWindow').show();
						}
					}, {
						text: langs('План потребления МО'),
						tooltip: langs('План потребления МО'),
						iconCls : 'pill16',
						handler: function() {
							getWnd('swDrugRequestPlanDeliveryViewWindow').show();
						}
					}]
				})
			},
			action_directories: {
				text: langs('Справочники'),
					tooltip: langs('Справочники'),
					iconCls: 'book32',
					menuAlign: 'tr',
					menu: new Ext.menu.Menu({
					items: [
						{
							text: WND_DLO_DRUGMNNLATINEDIT,
							tooltip: langs('Редактирование латинского наименования МНН'),
							iconCls : 'drug-viewmnn16',
							hidden: !getRegionNick().inlist(['perm','ufa']),
							handler: function() {
								getWnd('swDrugMnnViewWindow').show({
									privilegeType: 'all'
								});
							}
						}, {
							text: WND_DLO_DRUGTORGLATINEDIT,
							tooltip: langs('Редактирование латинского наименования медикамента'),
							iconCls : 'drug-viewtorg16',
							hidden: !getRegionNick().inlist(['perm','ufa']),
							handler: function() {
								getWnd('swDrugTorgViewWindow').show();
							}
						},
						'-',
						{
							tooltip: langs('МКБ-10'),
							text: langs('Справочник МКБ-10'),
							iconCls: 'spr-mkb16',
							handler: function() {
								if ( !getWnd('swMkb10SearchWindow').isVisible() )
									getWnd('swMkb10SearchWindow').show();
							}
						},
						{
							tooltip: langs('Просмотр') + getMESAlias(),
							text: langs('Просмотр') + getMESAlias(),
							iconCls: 'spr-mes16',
							handler: function() {
								if ( !getWnd('swMesOldSearchWindow').isVisible() )
									getWnd('swMesOldSearchWindow').show();
							}
						},
						{
							text: MM_DLO_OFVIEW,
							tooltip: langs('Работа с просмотром и редактированием аптек'),
							iconCls : 'farmview16',
							hidden: !getRegionNick().inlist(['perm','ufa']),
							handler: function() {
								getWnd('swOrgFarmacyViewWindow').show();
							}
						},
						sw.Promed.Actions.swDrugDocumentSprAction,
						{
							name: 'action_DrugNomenSpr',
							text: langs('Номенклатурный справочник'),
							iconCls : '',
							handler: function()
							{
								getWnd('swDrugNomenSprWindow').show();
							}
						},
						{
							name: 'action_PriceJNVLP',
							text: langs('Цены на ЖНВЛП'),
							iconCls : 'dlo16',
							handler: function() {
								getWnd('swJNVLPPriceViewWindow').show();
							}
						},
						{
							name: 'action_DrugMarkup',
							text: langs('Предельные надбавки на ЖНВЛП'),
							iconCls : 'lpu-finans16',
							handler: function() {
								getWnd('swDrugMarkupViewWindow').show();
							}
						}
						,'-',
						{
							text: getRLSTitle(),
							tooltip: getRLSTitle(),
							iconCls: 'rls16',
							handler: function() {
								getWnd('swRlsViewForm').show();
							},
							hidden: false
						}
					]
				})
			},
			action_PersonCardSearch: {
				handler: function() {
					getWnd('swPersonCardSearchWindow').show();
				},
				iconCls : 'card-search32',
					nn: 'action_PersonCardSearch',
					text: WND_POL_PERSCARDSEARCH,
					tooltip: langs('РПН: Поиск')
			},
			action_RPNPrikr: {
				nn: 'action_RPNPrikr',
					tooltip: langs('РПН: Прикрепление'),
					text: langs('РПН: Прикрепление'),
					iconCls : 'card-view32',
					handler: function() {
					getWnd('swPersonCardViewAllWindow').show();
				}
			},
			action_PersonCardState: {
				handler: function() {
					getWnd('swPersonCardStateViewWindow').show();
				},
				iconCls : 'card-state32',
					nn: 'action_PersonCardState',
					text: WND_POL_PERSCARDSTATEVIEW,
					tooltip: langs('РПН: Журнал движения')
			},
			action_Register: {
				nn: 'action_Register',
					tooltip: langs('Регистры'),
					text: langs('Регистры'),
					iconCls : 'registry32',
					disabled: false,
					menuAlign: 'tr?',
					menu: new Ext.menu.Menu({
					items: [{
						tooltip: langs('Регистр по Вирусному гепатиту'),
						text: langs('Регистр по Вирусному гепатиту'),
						iconCls: 'doc-reg16',
						disabled: (String(getGlobalOptions().groups).indexOf('HepatitisRegistry', 0) < 0),
						handler: function() {
							if ( getWnd('swHepatitisRegistryWindow').isVisible() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: langs('Окно уже открыто'),
									title: ERR_WND_TIT
								});
								return false;
							}
							getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: langs('Регистр по онкологии'),
						text: langs('Регистр по онкологии'),
						iconCls: 'doc-reg16',
						disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0),
						handler: function() {
							if ( getWnd('swOnkoRegistryWindow').isVisible() ) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: Ext.emptyFn,
									icon: Ext.Msg.WARNING,
									msg: langs('Окно уже открыто'),
									title: ERR_WND_TIT
								});
								return false;
							}
							getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: langs('Регистр по Психиатрии'),
						text: langs('Регистр по Психиатрии'),
						iconCls: 'doc-reg16',
						disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
						handler: function() {
							getWnd('swCrazyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: langs('Регистр по Наркологии'),
						text: langs('Регистр по Наркологии'),
						iconCls: 'doc-reg16',
						disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
						handler: function() {
							getWnd('swNarkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: langs('Регистр больных туберкулезом'),
						text: langs('Регистр по туберкулезным заболеваниям'),
						iconCls: 'doc-reg16',
						disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
						handler: function() {
							getWnd('swTubRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: langs('Регистр больных венерическим заболеванием'),
						text: langs('Регистр больных венерическим заболеванием'),
						iconCls : 'doc-reg16',
						disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
						handler: function()
						{
							getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, {
						tooltip: langs('Регистр ВИЧ-инфицированных'),
						text: langs('Регистр ВИЧ-инфицированных'),
						iconCls: 'doc-reg16',
						disabled: !allowHIVRegistry(),
						handler: function() {
							getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					},
						sw.Promed.personRegister.getOrphanBtnConfig('swWorkPlacePolkaLLOWindow', null),
						sw.Promed.personRegister.getVznBtnConfig('swWorkPlacePolkaLLOWindow', null),
						{
							tooltip: 'Регистр ИПРА',
							text: 'Регистр ИПРА',
							iconCls : 'doc-reg16',
							hidden: !(isUserGroup('IPRARegistry') || isUserGroup('IPRARegistryEdit')),
							handler: function() {
								if ( getWnd('swIPRARegistryViewWindow').isVisible() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: Ext.emptyFn,
										icon: Ext.Msg.WARNING,
										msg: 'Окно уже открыто',
										title: ERR_WND_TIT
									});
									return false;
								}
								getWnd('swIPRARegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
							}.createDelegate(this)
						},
						{
							tooltip: langs('Регистр по сахарному диабету'),
							text: langs('Регистр по сахарному диабету'),
							iconCls : 'doc-reg16',
							hidden: !getRegionNick().inlist([ 'pskov','khak','saratov','buryatiya' ]),
							handler: function()
							{
								if ( getWnd('swDiabetesRegistryWindow').isVisible() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: Ext.emptyFn,
										icon: Ext.Msg.WARNING,
										msg: langs('Окно уже открыто'),
										title: ERR_WND_TIT
									});
									return false;
								}
								getWnd('swDiabetesRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
							}.createDelegate(this)
						}, {
							tooltip: langs('Регистр по детям из многодетных семей'),
							text: langs('Регистр по детям из многодетных семей'),
							iconCls : 'doc-reg16',
							hidden: !getRegionNick().inlist([ 'pskov', 'saratov']),
							handler: function()
							{
								if ( getWnd('swLargeFamilyRegistryWindow').isVisible() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: Ext.emptyFn,
										icon: Ext.Msg.WARNING,
										msg: langs('Окно уже открыто'),
										title: ERR_WND_TIT
									});
									return false;
								}
								getWnd('swLargeFamilyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
							}.createDelegate(this)
						}, {
							tooltip: langs('Регистр ФМБА'),
							text: langs('Регистр ФМБА'),
							iconCls : 'doc-reg16',
							hidden: (!getRegionNick().inlist([ 'saratov' ]) && !isFmbaUser()),
							handler: function()
							{
								if ( getWnd('swFmbaRegistryWindow').isVisible() ) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: Ext.emptyFn,
										icon: Ext.Msg.WARNING,
										msg: langs('Окно уже открыто'),
										title: ERR_WND_TIT
									});
									return false;
								}
								getWnd('swFmbaRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
							}.createDelegate(this)
						}]
				})
			},
			action_DrugOstatRegistryList: {
				nn: 'action_DrugOstatRegistryList',
					tooltip: langs('Просмотр регистра остатков'),
					text: langs('Просмотр регистра остатков'),
					iconCls : 'pers-cards32',
					menuAlign: 'tr?',
					menu: new Ext.menu.Menu({
					items: [{
						text: langs('Просмотр остатков организации пользователя'),
						tooltip: langs('Просмотр остатков организации пользователя'),
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show({
								mode: 'suppliers',
								userMedStaffFact: getWnd('swWorkPlacePolkaLLOWindow').userMedStaffFact
							});
						}.createDelegate(this)
					}, {
						text: langs('Просмотр остатков по складам Аптек и РАС'),
						tooltip: langs('Просмотр остатков по складам Аптек и РАС'),
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
						}
					}]
				})
			},
			action_OrgFarmacyByLpuView: {
				nn: 'action_OrgFarmacyByLpuView',
					tooltip: langs('Прикрепление аптек к МО'),
					text: langs('Прикрепление аптек к МО'),
					iconCls : 'therapy-plan32',
					disabled: false,
					handler: function(){
					if (getRegionNick().inlist(['perm', 'ufa'])) {
						getWnd('swOrgFarmacyByLpuViewWindow').show();
					} else {
						getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: 'polkallo'});
					}
				}
			},
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
					iconCls: 'notice32',
					nn: 'action_JourNotice',
					text: langs('Журнал уведомлений'),
					tooltip: langs('Журнал уведомлений')
			},
			action_Report: { //http://redmine.swan.perm.ru/issues/18509
				nn: 'action_Report',
				tooltip: langs('Просмотр отчетов'),
				text: langs('Просмотр отчетов'),
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
			}
		}

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

		if (getRegionNick() == 'msk') {
			this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
				owner: form,
				filter: {
					title: langs('Фильтр'),
					layout: 'form',
					items: [{
						layout: 'column',
						items: [{
							layout: 'form',
							labelWidth: 65,
							items: [{
								xtype: 'textfieldpmw',
								width: 150,
								name: 'Person_SurName',
								fieldLabel: langs('Фамилия'),
								listeners: {
									'keydown': form.onKeyDown
								}
							}]
						}, {
							layout: 'form',
							labelWidth: 45,
							items: [{
								xtype: 'textfieldpmw',
								width: 150,
								name: 'Person_FirName',
								fieldLabel: langs('Имя'),
								listeners: {
									'keydown': form.onKeyDown
								}
							}]
						}, {
							layout: 'form',
							labelWidth: 75,
							items: [{
								xtype: 'textfieldpmw',
								width: 150,
								name: 'Person_SecName',
								fieldLabel: langs('Отчество'),
								listeners: {
									'keydown': form.onKeyDown
								}
							}]
						}, {
							layout: 'form',
							labelWidth: 35,
							items: [{
								xtype:'swdatefield',
								format:'d.m.Y',
								plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
								name: 'Person_BirthDay',
								fieldLabel: langs('ДР'),
								listeners: {
									'keydown': form.onKeyDown
								}
							}]
						}]
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							labelWidth: 65,
							items: [{
								width: 150,
								xtype: 'textfield',
								name: 'Person_Snils',
								fieldLabel: langs('СНИЛС'),
								listeners: {
									'keydown': form.onKeyDown
								}
							}]
						}, {
							layout: 'form',
							labelWidth: 100,
							items: [{
								hiddenName: 'pmUser_insID',
								listeners: {
									'keydown': form.onKeyDown,
									'render': function() {
										this.getStore().load({
											callback: function() {
												if ( this.getValue() ) {
													this.setValue(this.getValue());
												}
											}.createDelegate(this)
										});
									}
								},
								width: 300,
								xtype: 'swpromedusercombo'
							}]
						}]
					}, {
						layout: 'column',
						items: [{
							layout: 'form',
							items: [{
								xtype: 'button',
								id: form.id + 'BtnSearch',
								text: langs('Найти'),
								iconCls: 'search16',
								handler: function() {
									form.doSearch();
								}
							}]
						}, {
							layout: 'form',
							items: [{
								style: "padding-left: 10px",
								xtype: 'button',
								id: form.id + 'BtnClear',
								text: langs('Сброс'),
								iconCls: 'reset16',
								handler: function() {
									form.doReset();
									form.doSearch('day');
								}
							}]
						}]
					}]
				}
			});

			this.GridPanel = new sw.Promed.ViewFrame({
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 100,
				id: this.id + '_Grid',
				pageSize: 50,
				paging: true,
				autoScroll: true,
				listeners: {
					resize: function() {
						if( this.layout.layout ) this.doLayout();
					}
				},
				autoLoadData: false,
				root: 'data',
				actions: [
					{ name: 'action_add', hidden: true, disabled: true },
					{ name: 'action_edit', hidden: true, disabled: true },
					{ name: 'action_view', hidden: true, disabled: true },
					{ name: 'action_delete', hidden: true, disabled: true },
					{ name: 'action_refresh' },
					{ name: 'action_print' }
				],
				stringfields: [
					{ name: 'EvnVizitPL_id', type: 'int', hidden: true, key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonEvn_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'LpuSection_id', type: 'int', hidden: true },
					{ name: 'MedStaffFact_id', type: 'int', hidden: true },
					{ name: 'Diag_id', type: 'int', hidden: true },
					{ name: 'EvnVizitPL_setDate', type: 'date', header: langs('Дата'), width: 100 },
					{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 180, id: 'autoexpand' },
					{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
					{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 180 },
					{ name: 'Person_Birthday', type: 'date', header: langs('Дата рождения'), width: 100 },
					{ name: 'Person_IsFedLgot', type: 'checkbox', header: 'ФЛ', width: 30},
					{ name: 'Person_IsRefuse', type: 'checkbox', header: 'Отказ', width: 30},
					{ name: 'Person_IsRegLgot', type: 'checkbox', header: 'РЛ', width: 30},
					{ name: 'PersonPrivilegeReq_Data', header: langs('Категории'), width: 150, renderer: function(v, p, r) {
						var req_str = r.get('PersonPrivilegeReq_Data');
						if (!Ext.isEmpty(req_str)) {
							if (!Ext.isEmpty(req_str)) {
								if (req_str.length > 0 && req_str.slice(-1) == ';') {
									req_str = req_str.substr(0, req_str.length-1);
								}
								req_str = req_str.split(';').join('<br/>');
							}
						}
						return req_str;
					}},
					{ name: 'MedPersonal_Data', type: 'string', header: langs('Врач'), width: 350 },
					{ name: 'pmUser_Name', type: 'string', header: langs('Пользователь'), width: 150 },
                    { name: 'Check_Snils', header: langs('Проверка СНИЛС'), width: 130, renderer: function (v, p, record) { return record.get('EvnVizitPL_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } },
                    { name: 'Check_Registration', header: langs('Проверка регистрации'), width: 130, renderer: function (v, p, record) { return record.get('EvnVizitPL_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } },
                    { name: 'Check_Polis', header: langs('Проверка полиса'), width: 130, renderer: function (v, p, record) { return record.get('EvnVizitPL_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } }
				],
				onRowSelect: function(sm, index, record) {
					if ( typeof record == 'object' && !Ext.isEmpty(record.get('EvnVizitPL_id')) ) {
						form.ReceptGridPanel.getAction('action_print').menu.printObject.setHidden(false); //для рендера меню
						form.ReceptGridPanel.loadData({
							globalFilters: {
								EvnRecept_pid: record.get('EvnVizitPL_id')
							},
							callback: function() {
								if (isDLOUser() && Ext.util.Format.date(record.get('EvnVizitPL_setDate'), 'd.m.Y') == getGlobalOptions().date) {
									form.ReceptGridPanel.getAction('action_add').enable();
								}
								else {
									form.ReceptGridPanel.getAction('action_add').disable();
								}

                                form.ReceptGridPanel.getAction('action_refresh').enable();
							}
						});
						this.setActionDisabled('action_request', false);
					}
					else {
						form.ReceptGridPanel.removeAll();
                        form.ReceptGridPanel.getAction('action_add').disable();
                        form.ReceptGridPanel.getAction('action_refresh').disable();
						this.setActionDisabled('action_request', true);
					}
				},
				dataUrl: '/?c=EvnVizit&m=loadEvnVizitPLListForLLO',
				totalProperty: 'totalCount'
			});

			this.ReceptGridPanel = new sw.Promed.ViewFrame({
				tbActions: true,
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 100,
				id: this.id + '_ReceptGrid',
				pageSize: 50,
				paging: false,
				height: 250,
				region: 'south',
				autoScroll: true,
				listeners: {
					resize: function() {
						if( this.layout.layout ) this.doLayout();
					}
				},
				autoLoadData: false,
				root: 'data',
				actions: [
					{
						name: 'action_add',
						handler: function() { //Рецепт по особым группам заболеваний
							this.openEvnReceptEditWindow('add'/*, 'pregnancy'*/);
						}.createDelegate(this)
					},
					{ name: 'action_edit', hidden: true, handler: this.openEvnReceptEditWindow.createDelegate(this, ['view']) },
					{ name: 'action_view', handler: this.openEvnReceptEditWindow.createDelegate(this, ['view']) },
					{ name: 'action_delete', handler: this.deleteEvnRecept.createDelegate(this), text: langs('Аннулировать') },
					{ name: 'action_refresh' },
					{
						name:'action_signEvnRecept', key: 'sign_actions', hidden: getRegionNick() == 'kz', text:langs('Подписать'),
						tooltip: langs('Подписать'), iconCls : 'x-btn-text', icon: 'img/icons/digital-sign16.png',
						position: 6,
						handler: function() {
							var me = this,
							g = form.ReceptGridPanel.getGrid(),
							rec = g.getSelectionModel().getSelected();
							if (rec && rec.get('EvnRecept_id')) {
								getWnd('swEMDSignWindow').show({
									EMDRegistry_ObjectName: 'EvnRecept',
									EMDRegistry_ObjectID: rec.get('EvnRecept_id'),
									callback: function(data) {
										if (data.preloader) {
											me.disable();
										}

										if (data.success || data.error) {
											me.enable();
										}

										if (data.success) {
											me.getReceptGridPanel().getAction('action_refresh').execute();
										}
									}
								});
							}
						}
					},
					{
						name: 'action_print',
						menuConfig: {
							printObject: { handler: function(){ this.printEvnRecept(); }.createDelegate(this) }
						}
					}
				],
				stringfields: [
					{ name: 'EvnRecept_id', type: 'int', hidden: true, key: true },
					{ name: 'EvnRecept_pid', type: 'int', hidden: true },
					{ name: 'EvnRecept_deleted', type: 'int', hidden: true },
					{ name: 'ReceptRemoveCauseType_id', type: 'int', hidden: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonEvn_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'EvnRecept_Status', type: 'string', header: 'Статус', width: 150 },
					{ name: 'ReceptForm_Name', type: 'string', header: 'Форма', width: 150 },
					{ name: 'EvnRecept_Ser', type: 'string', header: langs('Серия') },
					{ name: 'EvnRecept_Num', type: 'string', header: langs('Номер') },
					{ name: 'Drug_Name', type: 'string', header: langs('Медикамент'), id: 'autoexpand' },
					{ name: 'EvnRecept_Kolvo', type: 'float', header: langs('Количество') },
					{ name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 200 },
					{ name: 'ReceptForm_id', hidden: true },
					{ name: 'ReceptType_Code', hidden: true },
					{ name: 'ReceptType_Name', header: langs('Тип рецепта'), width: 120, renderer: function(v, p, r) {
						return r.get('ReceptType_Code') == '3' ? langs('ЭД') : r.get('ReceptType_Name'); //ЭД - Электронный документ
					}},
					{ name: 'Recept_MarkDeleted', type: 'checkbox', header: 'Помечен к удалению', width: 120},
					{ name: 'EvnRecept_IsSigned', type: 'checkbox', header: langs('Подписан'), width: 80},
					{ name: 'EvnRecept_IsPrinted', type: 'checkbox', header: 'Распечатан', width: 80},
                    { name: 'Drug_id', type: 'int', hidden: true },
                    { name: 'Drug_rlsid', type: 'int', hidden: true },
                    { name: 'DrugComplexMnn_id', type: 'int', hidden: true },
                    { name: 'EvnRecept_setDate', type: 'date', hidden: true }
				],
				onRefresh: function () {
					this.getAction('action_print').menu.printObject.setHidden(false);
				},
				onLoadData: function(sm, index, record) {
					let vizitRecord = form.GridPanel.getGrid().getSelectionModel().getSelected();

					if ( typeof vizitRecord == 'object' && !Ext.isEmpty(vizitRecord.get('EvnVizitPL_id')) ) {
						if (isDLOUser() && Ext.util.Format.date(vizitRecord.get('EvnVizitPL_setDate'), 'd.m.Y') == getGlobalOptions().date) {
							form.ReceptGridPanel.getAction('action_add').enable();
						}
						else {
							form.ReceptGridPanel.getAction('action_add').disable();
						}
					}
					else {
						form.ReceptGridPanel.getAction('action_add').disable();
					}
				},
				onRowSelect: function(sm, index, record) {
					var dateNow = Ext.util.Format.date(new Date(), 'd.m.Y'),
						dateRecept = Ext.util.Format.date(record.get('EvnRecept_setDate'), 'd.m.Y');

					if(record.get('EvnRecept_deleted') != 2){
						this.getAction('action_delete').setDisabled(false);
					}
					else {
						this.getAction('action_delete').setDisabled(true);
					}

					var printButton = this.getAction('action_print').menu.printObject;
					printButton.setHidden(true);
					if (record.get('Recept_MarkDeleted') == 'false' && record.get('EvnRecept_Status') != 'Аннулирован') { //проверяем, что рецепт действующий
						printButton.setHidden(false);
						if(record.get('EvnRecept_IsPrinted') == 'true' && dateNow == dateRecept ){//если рецепт уже распечатан и дата рецепта равна текущей дате, то можно печатать повторно
							printButton.setText('Распечатать рецепт повторно');
							printButton.setHandler(function() {form.rePrintEvnRecept();}, form);
						}
						else { //если рецепт еще не распечатан или дата рецепта не равна текущей дате, то отображаем просто печать
							printButton.setText('Печать рецепта');
							printButton.setHandler(function() {form.printEvnRecept();}, form);
						}
					}

					var printButton = this.getAction('action_print').menu.printObject;
					printButton.setHidden(true);
					if (record.get('Recept_MarkDeleted') == 'false' && record.get('EvnRecept_Status') != 'Аннулирован') { //проверяем, что рецепт действующий
						printButton.setHidden(false);
						if(record.get('EvnRecept_IsPrinted') == 'true' && dateNow == dateRecept ){//если рецепт уже распечатан и дата рецепта равна текущей дате, то можно печатать повторно
							printButton.setText('Распечатать рецепт повторно');
							printButton.setHandler(function() {form.rePrintEvnRecept();}, form);
						}
						else { //если рецепт еще не распечатан или дата рецепта не равна текущей дате, то отображаем просто печать
							printButton.setText('Печать рецепта');
							printButton.setHandler(function() {form.printEvnRecept();}, form);
						}
					}
				},
				dataUrl: '/?c=EvnRecept&m=loadReceptList',
				totalProperty: 'totalCount'
			});

			this.ReceptGridPanel.ViewGridPanel.view = new Ext.grid.GridView({
				getRowClass: function (row, index) {
					var cls = '';

					if ( row.get('EvnRecept_deleted') == 2 || !Ext.isEmpty(row.get('ReceptRemoveCauseType_id')) ) {
						cls = cls + 'x-grid-rowbackdarkgray ';
					}

					if ( cls.length == 0 ) {
						cls = 'x-grid-panel';
					}

					return cls;
				}
			});

			this.MainPanel = new Ext.Panel({
				region: 'center',
				border: true,
				layout: 'border',
				title: langs('Журнал рабочего места'),
				items: [
					this.GridPanel,
					this.ReceptGridPanel
				]
			});
		}
		else {
			this.GridPanel = new sw.Promed.ViewFrame({
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 100,
				id: this.id + '_Grid',
				pageSize: 50,
				paging: true,
				autoScroll: true,
				listeners: {
					resize: function() {
						if( this.layout.layout ) this.doLayout();
					}
				},
				autoLoadData: false,
				root: 'data',
				actions: [
					{
						name: 'action_add',
						handler: function() { //Рецепт по особым группам заболеваний
							this.openEvnReceptEditWindow('add'/*, 'pregnancy'*/);
						}.createDelegate(this)
					},
					{ name: 'action_edit', hidden: true, handler: this.openEvnReceptEditWindow.createDelegate(this, ['view']) },
					{ name: 'action_view', handler: this.openEvnReceptEditWindow.createDelegate(this, ['view']) },
					{ name: 'action_delete', handler: this.deleteEvnRecept.createDelegate(this) },
					{ name: 'action_refresh' },
					//{ name: 'action_print' }
					{
						name: 'action_print',
						menuConfig: {
							printObject: { handler: function(){ this.printEvnRecept(); }.createDelegate(this) }
						}
					}
				],
				stringfields: [
					{ name: 'EvnRecept_id', type: 'int', hidden: true, key: true },
					{ name: 'EvnRecept_pid', type: 'int', hidden: true },
					{ name: 'ReceptRemoveCauseType_id', type: 'int', hidden: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonEvn_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'MorbusType_SysNick', type: 'string', header: 'MorbusType_SysNick',  hidden: true },
					{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 180 },
					{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
					{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 180 },
					{ name: 'EvnRecept_setDate', type: 'date', header: langs('Дата'), width: 100 },
					{ name: 'Drug_Name', type: 'string', header: langs('Медикамент'), id: 'autoexpand' },
					{ name: 'EvnRecept_Ser', type: 'string', header: langs('Серия') },
					{ name: 'EvnRecept_Num', type: 'string', header: langs('Номер') },
					{ name: 'ReceptForm_id', type: 'string', hidden: true },
					{ name: 'ReceptForm_Code', type: 'string', hidden: true },
					{ name: 'ReceptType_Code', type: 'string', hidden: true },
					{ name: 'ReceptType_Name', header: langs('Тип рецепта'), width: 120, renderer: function(v, p, r) {
							return r.get('ReceptType_Code') == '3' ? langs('ЭД') : r.get('ReceptType_Name'); //3 - Электронный документ
						}},
					{ name: 'Recept_MarkDeleted', type: 'checkbox', header: 'Помечен к удалению', width: 120},
					{ name: 'EvnRecept_IsSigned', type: 'checkbox', header: langs('Подписан'), width: 80},
					{ name: 'EvnRecept_IsPrinted', type: 'checkbox', header: 'Распечатан', width: 80},
					{ name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 200 },
					{ name: 'Drug_id', type: 'int', hidden:true },
					{ name: 'Drug_rlsid', type: 'int', hidden:true },
					{ name: 'DrugComplexMnn_id', type: 'int', hidden:true }
				],
				onRowSelect: function(sm, index, record) {
					if (getRegionNick() != 'msk') {
						if(record.get('Recept_MarkDeleted')=='true'){
							this.getAction('action_undo_delete').setDisabled(false);
						} else {
							this.getAction('action_undo_delete').setDisabled(true);
						}
					}
				},
				dataUrl: '/?c=EvnRecept&m=loadReceptList',
				totalProperty: 'totalCount'
			});

			this.MainPanel = new Ext.Panel({
				region: 'center',
				border: true,
				layout: 'border',
				title: langs('Журнал рабочего места'),
				items: [
					this.GridPanel
				]
			});
		}

		this.LeftPanel = new sw.Promed.BaseWorkPlaceButtonsPanel({
			animCollapse: false,
			width: 60,
			minSize: 60,
			maxSize: 120,
			region: 'west',
			floatable: false,
			collapsible: true,
			id: form.id + '_buttPanel',
			layoutConfig: {
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners: {
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

		Ext.apply(this, {
			buttons: [
				'-',
				HelpButton(this, TABINDEX_MPSCHED + 98),
				{
					text: langs('Закрыть'),
					tabIndex: -1,
					tooltip: langs('Закрыть'),
					iconCls: 'cancel16',
					handler: this.hide.createDelegate(this, [])
				}
			]
		});

		sw.Promed.swWorkPlacePolkaLLOWindow.superclass.initComponent.apply(this, arguments);
	}
});