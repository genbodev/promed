/**
 * swMzDrugRequestViewWindow - форма просмотра заявок врачей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Salakhov R.
 * @version      07.2015
 * @comment
 *
 */

sw.Promed.swMzDrugRequestViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['zayavki_vrachey'],
	layout: 'border',
	id: 'MzDrugRequestViewWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	buttons: [
		{
			text: '-'
		}, {
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],
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
	loadFilterCombo: function() {
		var wnd = this;

		['LpuUnit_id', 'LpuSection_id', 'MedPersonal_id'].forEach(function(field_name){
			var f_combo = wnd.form.findField(field_name);

			if (!Ext.isEmpty(wnd[field_name]) && wnd[field_name] > 0) {
				f_combo.setValueById(wnd[field_name]);
				f_combo.disable();
			} else {
				f_combo.getStore().baseParams.Lpu_id = wnd.Lpu_id;
				f_combo.loadData();
				f_combo.enable();
			}
		});
		['mdrv_DrugFinance_id','mdrv_DrugFinance_id2'].forEach(function(field_name){
			var f_combo = wnd.findById(field_name);
			if(wnd.isCommonPersonRegisterType()){
				f_combo.getStore().load({params:{where:' where DrugFinance_id = 3 or DrugFinance_id = 27'}});
			} else {
				f_combo.getStore().removeAll();
			}
		});
	},
	setPermissions: function() {
		// Для МинЗдрава скрываем панель фильтров по врачу
		this.findById('mdrv_DrugRequestMedPersonalFilter').setVisible(!getGlobalOptions().isMinZdrav);

		// закрытие возможности добавлять заявку для пользователей и админов ЛПУ взависимости от настройки
		this.DrugRequestGrid.setActionDisabled('action_add', this.action != 'edit' || (!isSuperAdmin() && (getGlobalOptions().is_create_drugrequest != 1)));
		this.DrugRequestGrid.setActionDisabled('action_edit', this.action != 'edit' || (!isSuperAdmin() && (getGlobalOptions().is_create_drugrequest != 1)));
		this.DrugRequestGrid.setActionDisabled('action_delete', this.action != 'edit' || (!isSuperAdmin() && (getGlobalOptions().is_create_drugrequest != 1)));

		if ( !isAdmin ) {
			this.buttons[0].hide();
		}

		if ((Ext.isEmpty(this.MedPersonal_id) || (this.MedPersonal_id > 0 && this.MedPersonal_id == getGlobalOptions().medpersonal_id)) && this.action != 'view') {
			this.DrugRequestGrid.setReadOnly(false);
			this.DrugRequestGrid.getAction('action_add').show();
			this.DrugRequestGrid.getAction('action_edit').show();
			this.DrugRequestGrid.getAction('action_delete').show();
		} else {
			this.DrugRequestGrid.setReadOnly(true);
			this.DrugRequestGrid.getAction('action_add').hide();
			this.DrugRequestGrid.getAction('action_edit').hide();
			this.DrugRequestGrid.getAction('action_delete').hide();
		}

        if (this.DrugRequest_Version > 0) {
            this.DrugRequestGrid.setReadOnly(true);
            this.DrugGrid.setReadOnly(true);
        } else {
            this.DrugGrid.setReadOnly(false);
        }
	},
	setVisibleElements: function() {
		var wnd = this;
		var form = this.DrugRequestFilterMainPanel.getForm();
		var mp_combo = form.findField('MedPersonal_id');

		mp_combo.ownerCt.show();

		['mdrv_DrugFinance_id','mdrv_DrugFinance_id2'].forEach(function(field_name){
			var f_combo = wnd.findById(field_name);
			if(wnd.isCommonPersonRegisterType()){
				f_combo.ownerCt.show();
			} else {
				f_combo.ownerCt.hide();
			}
		});

		/*if (this.MedPersonal_id > 0) {
			mp_combo.ownerCt.hide();
		}*/
	},
	setRequestEditable: function() {
		var wnd = this;
		var record = wnd.DrugRequestGrid.getGrid().getSelectionModel().getSelected();
		var isLeaderMo = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'leadermo'; }) > -1);

		if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') == 2 && (isLeaderMo || isSuperAdmin())) {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_smenit_status_zayavki']);
					loadMask.hide();
					wnd.hide();
				},
				params:{
					DrugRequest_id: record.get('DrugRequest_id'),
					DrugRequestStatus_Code: 1 //Начальная
				},
				success: function (response) {
					wnd.DrugRequestGrid.refreshRecords(null,0);

					//отправка сообщения о возврате заявки врача на редактирование
					Ext.Ajax.request({
						params:{
							DrugRequest_id: record.get('DrugRequest_id'),
							event: 'mp_request_return_edit'
						},
						success: function (response) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result[0] && result[0].Message_id > 0) {
								getWnd('swMessagesViewWindow').show({
									mode: 'openMessage',
									message_data: result[0]
								});
							}
						},
						url:'/?c=MzDrugRequest&m=getNotice'
					});
				},
				url:'/?c=MzDrugRequest&m=saveDrugRequestStatus'
			});
		}
	},
	setRequestMoApproved: function(approved) {
		var wnd = this;
		var record = wnd.DrugRequestGrid.getGrid().getSelectionModel().getSelected();
		var RequiredStatus_Code = approved ? 2 : 3; //2 - Сформированная; 3 - Утвержденная;
		var NewStatus_Code = approved ? 3 : 2;
		var NoticeEvent = approved ? 'mo_request_set_confirmed' : 'mo_request_set_formed';
		var MoDrugRequest_id = 0;
		var MoDrugRequestStatus_Code = 0;

		Ext.Ajax.request({
			params:{
				DrugRequest_id: record.get('DrugRequest_id')
			},
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result.DrugRequest_id) {
					MoDrugRequest_id = result.DrugRequest_id;
					MoDrugRequestStatus_Code = result.DrugRequestStatus_Code;
				}
				if (MoDrugRequest_id > 0) {
					if (MoDrugRequestStatus_Code == RequiredStatus_Code) {
						Ext.Ajax.request({
							failure:function () {
								sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_smenit_status_zayavki']);
							},
							params:{
								DrugRequest_id: MoDrugRequest_id,
								DrugRequestStatus_Code: NewStatus_Code
							},
							success: function (response) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.success) {
									wnd.DrugRequestGrid.refreshRecords(null,0);
									wnd.refreshRequestMoStatus();
									Ext.Ajax.request({
										params:{
											DrugRequest_id: MoDrugRequest_id,
											event: NoticeEvent
										},
										success: function (response) {},
										url:'/?c=MzDrugRequest&m=getNotice'
									});
								} else if (result.Error_Type && result.Error_Data) {
                                    getWnd('swMzDrugRequestExtendedErrorViewWindow').show(result);
                                }
							},
							url:'/?c=MzDrugRequest&m=changeDrugRequestStatus'
						});
					} else {
						sw.swMsg.alert(lang['oshibka'], approved ? lang['utverdit_mojno_tolko_zayavku_mo_so_statusom_sformirovannaya'] : lang['zayavka_mo_doljna_imet_status_utverjdennaya']);
					}
				}
			},
			url:'/?c=MzDrugRequest&m=getMoRequestByMpRequest'
		});
	},
	editLpuRegion: function() {
		var wnd = this;
		var selected_record = wnd.DrugRequestGrid.getGrid().getSelectionModel().getSelected();

		if (selected_record && selected_record.get('DrugRequest_id') > 0) {
            var params = new Object();
            params.DrugRequest_id = selected_record.get('DrugRequest_id');
            params.onSave = function () {
                wnd.DrugRequestGrid.refreshRecords(null,0);
            };
            getWnd('swMzDrugRequestLpuRegionEditWindow').show(params);
		}
	},
	refreshRequestMoStatus: function() {
		var wnd = this;
		var params =  new Object();

		params.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id > 0 ? wnd.DrugRequestPeriod_id : null;
		params.PersonRegisterType_id = wnd.PersonRegisterType_id > 0 ? wnd.PersonRegisterType_id : null;
		params.DrugRequestKind_id = wnd.DrugRequestKind_id > 0 ? wnd.DrugRequestKind_id : null;
		params.DrugGroup_id = wnd.DrugGroup_id > 0 ? wnd.DrugGroup_id : null;
		params.DrugRequest_Version = wnd.DrugRequest_Version > 0 ? wnd.DrugRequest_Version : null;
		params.Lpu_id = wnd.Lpu_id > 0 ? wnd.Lpu_id : getGlobalOptions().lpu_id;

		Ext.Ajax.request({
			params: params,
			success: function (response) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result && result[0] && !Ext.isEmpty(result[0].DrugRequest_id)) {
					wnd.InformationPanel.setData('mo_request_status', result[0].DrugRequestStatus_Name);
					wnd.InformationPanel.showData();

					if (result[0].DrugRequestStatus_Code == 3) {
						wnd.action = 'view';
					} else {
						wnd.action = 'edit';
					}

                    wnd.InformationPanel.showToolbar(result[0].DrugRequestStatus_Code == 1 && wnd.Lpu_id == getGlobalOptions().lpu_id); //1 - Начальная

					wnd.setPermissions();
				}
			},
			url:'/?c=MzDrugRequest&m=getMoRequestStatusByParams'
		});
	},
    getRequestMoPlanParams: function(callback) {
		var wnd = this;
		var params =  new Object();

		params.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id > 0 ? wnd.DrugRequestPeriod_id : null;
		params.PersonRegisterType_id = wnd.PersonRegisterType_id > 0 ? wnd.PersonRegisterType_id : null;
		params.DrugRequestKind_id = wnd.DrugRequestKind_id > 0 ? wnd.DrugRequestKind_id : null;
		params.DrugGroup_id = wnd.DrugGroup_id > 0 ? wnd.DrugGroup_id : null;
		params.DrugRequest_Version = wnd.DrugRequest_Version > 0 ? wnd.DrugRequest_Version : null;
		params.Lpu_id = wnd.Lpu_id > 0 ? wnd.Lpu_id : getGlobalOptions().lpu_id;

		Ext.Ajax.request({
			params: params,
			success: function (response) {
				var data = Ext.util.JSON.decode(response.responseText);
                if (typeof callback == 'function') {
                    callback(data);
                }
			},
            failure:function () {
                if (typeof callback == 'function') {
                    callback(null);
                }
            },
			url:'/?c=MzDrugRequest&m=getMoRequestPlanParamsByParams'
		});
	},
    recalculateRequestMoPlanParams: function(callback) {
		var wnd = this;
		var params =  new Object();

		params.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id > 0 ? wnd.DrugRequestPeriod_id : null;
		params.PersonRegisterType_id = wnd.PersonRegisterType_id > 0 ? wnd.PersonRegisterType_id : null;
		params.DrugRequestKind_id = wnd.DrugRequestKind_id > 0 ? wnd.DrugRequestKind_id : null;
		params.DrugGroup_id = wnd.DrugGroup_id > 0 ? wnd.DrugGroup_id : null;
		params.DrugRequest_Version = wnd.DrugRequest_Version > 0 ? wnd.DrugRequest_Version : null;
        params.Lpu_id = wnd.Lpu_id > 0 ? wnd.Lpu_id : getGlobalOptions().lpu_id;

		Ext.Ajax.request({
			params: params,
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
			url:'/?c=MzDrugRequest&m=calculateDrugRequestPlanLpuParams'
		});
	},
	setRequestSum: function() {
		var sum = 0;
		var sum_fed = 0;
		var sum_reg = 0;

		if(!this.isCommonPersonRegisterType()){
			this.DrugRequestGrid.getGrid().getStore().each(function(record) {
				if (!Ext.isEmpty(record.get('DrugRequest_Summa')) && record.get('DrugRequest_Summa')*1 > 0) {
					sum += record.get('DrugRequest_Summa')*1;
				}
			});

            //проверка превышения лимита
            if (this.RequestLimitData && !Ext.isEmpty(this.RequestLimitData.limit) && this.RequestLimitData.limit > 0 && sum > this.RequestLimitData.limit) {
                sum = '<span style="color:red;">' + sw.Promed.Format.rurMoney(sum) + '</span>';
            } else {
                sum = sw.Promed.Format.rurMoney(sum);
            }
            
			this.InformationPanel.setData('mo_request_sum', sum);
			this.InformationPanel.setData('mo_request_sum_fed', '');
			this.InformationPanel.setData('mo_request_sum_reg', '');
		} else {
			this.DrugRequestGrid.getGrid().getStore().each(function(record) {
				if (!Ext.isEmpty(record.get('DrugRequest_Summa_Fed')) && record.get('DrugRequest_Summa_Fed')*1 > 0) {
					sum_fed += record.get('DrugRequest_Summa_Fed')*1;
				}
			});
			this.DrugRequestGrid.getGrid().getStore().each(function(record) {
				if (!Ext.isEmpty(record.get('DrugRequest_Summa_Reg')) && record.get('DrugRequest_Summa_Reg')*1 > 0) {
					sum_reg += record.get('DrugRequest_Summa_Reg')*1;
				}
			});

            //проверка превышения лимита
            if (this.RequestLimitData && !Ext.isEmpty(this.RequestLimitData.limit_fed) && this.RequestLimitData.limit_fed > 0 && sum_fed > this.RequestLimitData.limit_fed) {
                sum_fed = '<span style="color:red;">' + sw.Promed.Format.rurMoney(sum_fed) + '</span>';
            } else {
                sum_fed = sw.Promed.Format.rurMoney(sum_fed);
            }
            sum_fed = 'Фед.: '+sum_fed;

            //проверка превышения лимита
            if (this.RequestLimitData && !Ext.isEmpty(this.RequestLimitData.limit_reg) && this.RequestLimitData.limit_reg > 0 && sum_reg > this.RequestLimitData.limit_reg) {
                sum_reg = '<span style="color:red;">' + sw.Promed.Format.rurMoney(sum_reg) + '</span>';
            } else {
                sum_reg = sw.Promed.Format.rurMoney(sum_reg);
            }
            sum_reg = '&nbsp;&nbsp;Рег.: '+sum_reg;
            
			this.InformationPanel.setData('mo_request_sum', '');
			this.InformationPanel.setData('mo_request_sum_fed', sum_fed);
			this.InformationPanel.setData('mo_request_sum_reg', sum_reg);
		}
		
		this.InformationPanel.showData();
	},
	setRequestLimit: function(data) {
        var wnd = this;
        
        wnd.RequestLimitData = new Object({
            limit: 0,
            limit_fed: 0,
            limit_reg: 0,
            cnt: 0,
            cnt_fed: 0,
            cnt_reg: 0,
            count_req: 0,
            count_fed: 0,
            count_reg: 0
        });

        //расчет лимитов
        if (!Ext.isEmpty(data.DrugRequestPlan_id)) {
            wnd.RequestLimitData.limit = data.DrugRequestPlan_Summa > 0 ? data.DrugRequestPlan_Summa*1 : 0;
            wnd.RequestLimitData.limit_fed = data.DrugRequestPlan_FedSumma > 0 ? data.DrugRequestPlan_FedSumma*1 : 0;
            wnd.RequestLimitData.limit_reg = data.DrugRequestPlan_RegSumma > 0 ? data.DrugRequestPlan_RegSumma*1 : 0;
            wnd.RequestLimitData.kolvo = data.DrugRequestPlan_Kolvo > 0 ? data.DrugRequestPlan_Kolvo*1 : 0;
            wnd.RequestLimitData.kolvo_fed = data.DrugRequestPlan_FedKolvo > 0 ? data.DrugRequestPlan_FedKolvo*1 : 0;
            wnd.RequestLimitData.kolvo_reg = data.DrugRequestPlan_RegKolvo > 0 ? data.DrugRequestPlan_RegKolvo*1 : 0;
            wnd.RequestLimitData.count_req = data.DrugRequestPlan_CountReq > 0 ? data.DrugRequestPlan_CountReq*1 : 0;
            wnd.RequestLimitData.count_fed = data.DrugRequestPlan_CountFed > 0 ? data.DrugRequestPlan_CountFed*1 : 0;
            wnd.RequestLimitData.count_reg = data.DrugRequestPlan_CountReg > 0 ? data.DrugRequestPlan_CountReg*1 : 0;
        }

        //обновление информации о лимитах в информационой панели
        wnd.InformationPanel.setData('currency_name', getCurrencyName());
        if(!wnd.isCommonPersonRegisterType()){
            wnd.InformationPanel.setData('mo_request_limit', sw.Promed.Format.rurMoney(wnd.RequestLimitData.limit)+(wnd.RequestLimitData.kolvo > 0 ? ' / '+wnd.RequestLimitData.kolvo : ''));
            wnd.InformationPanel.setData('mo_request_limit_fed', '');
            wnd.InformationPanel.setData('mo_request_limit_reg', '');
            wnd.InformationPanel.setData('mo_request_count_req', sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_req)+(wnd.RequestLimitData.kolvo > 0 ? ' / '+wnd.RequestLimitData.kolvo : ''));
            wnd.InformationPanel.setData('mo_request_count_fed', '');
            wnd.InformationPanel.setData('mo_request_count_reg', '');
        } else {
            wnd.InformationPanel.setData('mo_request_limit', '');
            wnd.InformationPanel.setData('mo_request_limit_fed', 'Фед.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.limit_fed)+(wnd.RequestLimitData.kolvo_fed > 0 ? ' / '+wnd.RequestLimitData.kolvo_fed : ''));
            wnd.InformationPanel.setData('mo_request_limit_reg', '&nbsp;&nbsp;Рег.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.limit_reg)+(wnd.RequestLimitData.kolvo_reg > 0 ? ' / '+wnd.RequestLimitData.kolvo_reg : ''));
            wnd.InformationPanel.setData('mo_request_count_req', '');
            wnd.InformationPanel.setData('mo_request_count_req', 'Фед.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_fed)+(wnd.RequestLimitData.kolvo_fed > 0 ? ' / '+wnd.RequestLimitData.kolvo_fed : ''));
            wnd.InformationPanel.setData('mo_request_count_reg', '&nbsp;&nbsp;Рег.: '+sw.Promed.Format.rurMoney(wnd.RequestLimitData.count_reg)+(wnd.RequestLimitData.kolvo_reg > 0 ? ' / '+wnd.RequestLimitData.kolvo_reg : ''));
        }
        wnd.setRequestSum(); //данный метод включает в себя showData для информационой панели, поэтому дополнительного вызова showData не требуется
	},
	doSearch: function(clear) {
		var wnd = this;
		var dr_form = wnd.DrugRequestFilterMainPanel.getForm();

		if (clear) {
			['LpuUnit_id', 'LpuSection_id', 'DrugRequestStatus_id', 'MedPersonal_id'].forEach(function(field_name) {
				var field = wnd.form.findField(field_name);
				if (!field.disabled) {
					field.reset();
				}
			});
		}

		var params = new Object();
		params.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id > 0 ? wnd.DrugRequestPeriod_id : null;
		params.PersonRegisterType_id = wnd.PersonRegisterType_id > 0 ? wnd.PersonRegisterType_id : null;
		params.DrugRequestKind_id = wnd.DrugRequestKind_id > 0 ? wnd.DrugRequestKind_id : null;
		params.DrugGroup_id = wnd.DrugGroup_id > 0 ? wnd.DrugGroup_id : null;
		params.DrugRequest_Version = wnd.DrugRequest_Version > 0 ? wnd.DrugRequest_Version : null;
		params.Lpu_id = wnd.Lpu_id > 0 ? wnd.Lpu_id : getGlobalOptions().lpu_id;
		params.LpuSection_id = wnd.lpusection_combo.getValue() > 0 ? wnd.lpusection_combo.getValue() : (wnd.LpuSection_id > 0 ? wnd.LpuSection_id : null);
		params.LpuUnit_id = wnd.lpuunit_combo.getValue() > 0 ? wnd.lpuunit_combo.getValue() : (wnd.LpuUnit_id > 0 ? wnd.LpuUnit_id : null);
		params.MedPersonal_id = wnd.MedPersonal_id > 0 ? wnd.MedPersonal_id : (dr_form.findField('MedPersonal_id').getValue() > 0 ? dr_form.findField('MedPersonal_id').getValue() : null);
		params.DrugRequestStatus_id = dr_form.findField('DrugRequestStatus_id').getValue() > 0 ? dr_form.findField('DrugRequestStatus_id').getValue() : null;
		params.DrugFinance_id = dr_form.findField('DrugFinance_id').getValue() > 0 ? dr_form.findField('DrugFinance_id').getValue() : null;
		params.mode = 'with_lgot_count';

		this.DrugRequestGrid.removeAll();
		this.DrugRequestGrid.loadData({
			globalFilters: params
		});
	},
	doDrugGridSearch: function(reset) {
		var wnd = this;
		var form = wnd.DrugFilterPanel.getForm();

		if (reset) {
			form.reset();
		}

		var params = form.getValues();
		params.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id;
		params.PersonRegisterType_id = wnd.PersonRegisterType_id;
		params.DrugRequestKind_id = wnd.DrugRequestKind_id;
		params.DrugGroup_id = wnd.DrugGroup_id;
		params.DrugRequest_Version = wnd.DrugRequest_Version;
		params.Lpu_id = wnd.Lpu_id;
		params.DrugFinance_id = form.findField('DrugFinance_id').getValue() > 0 ? form.findField('DrugFinance_id').getValue() : null;
		params.start = 0;
		params.limit = 100;

		this.DrugGrid.removeAll();
		this.DrugGrid.loadData({
			globalFilters: params
		});
	},
	setDisabledAction: function(grid, actions_id, action, disable) {
        var actions = grid.getAction(actions_id).items[0].menu.items;
        var menu_actions = grid.ViewContextMenu.items.get('id_'+actions_id).menu.items;
        actions.each(function(a) {
            if (a.name == action) {
                if (disable) {
                    a.disable();
                } else {
                    a.enable();
                }
            }
        });
        menu_actions.each(function(a) {
            if (a.name == action) {
                if (disable) {
                    a.disable();
                } else {
                    a.enable();
                }
            }
        });
    },
    hideAction: function(grid, actions_id, action, hide) {
        var actions = grid.getAction(actions_id).items[0].menu.items;
        var menu_actions = grid.ViewContextMenu.items.get('id_'+actions_id).menu.items;
        actions.each(function(a) {
            if (a.name == action) {
                if (hide) {
                    a.hide();
                } else {
                    a.show();
                }
            }
        });
        menu_actions.each(function(a) {
            if (a.name == action) {
                if (hide) {
                    a.hide();
                } else {
                    a.show();
                }
            }
        });
    },
	show: function() {
		sw.Promed.swMzDrugRequestViewWindow.superclass.show.apply(this, arguments);
		var wnd = this;

		this.ARMType = !Ext.isEmpty(arguments[0].ARMType) ? arguments[0].ARMType : null;
		this.action = arguments[0].action ? arguments[0].action : 'edit';
		this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id ? arguments[0].DrugRequestPeriod_id : null;
		this.PersonRegisterType_id = arguments[0].PersonRegisterType_id ? arguments[0].PersonRegisterType_id : null;
		this.DrugRequestKind_id = arguments[0].DrugRequestKind_id ? arguments[0].DrugRequestKind_id : null;
		this.DrugGroup_id = arguments[0].DrugGroup_id ? arguments[0].DrugGroup_id : null;
		this.DrugRequest_Version = arguments[0].DrugRequest_Version ? arguments[0].DrugRequest_Version : null;
		this.Lpu_id = arguments[0].Lpu_id ? arguments[0].Lpu_id : null;
		this.LpuUnit_id = arguments[0].LpuUnit_id ? arguments[0].LpuUnit_id : null;
		this.LpuSection_id = arguments[0].LpuSection_id ? arguments[0].LpuSection_id : null;
		this.MedPersonal_id = arguments[0].MedPersonal_id ? arguments[0].MedPersonal_id : null;
		this.MoRequest_Data = arguments[0].MoRequest_Data ? arguments[0].MoRequest_Data : null;
        this.FirstCopy_Inf = arguments[0].FirstCopy_Inf ? arguments[0].FirstCopy_Inf : null;
		this.onHide = arguments[0].onHide ? arguments[0].onHide : Ext.emptyFn;

		if (this.isCommonPersonRegisterType()) {
			this.DrugRequestGrid.setColumnHidden('DrugRequest_Summa', true);
			this.DrugRequestGrid.setColumnHidden('DrugRequest_Summa_Fed', false);
			this.DrugRequestGrid.setColumnHidden('DrugRequest_Summa_Reg', false);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_CountReq', true);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_CountFed', false);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_CountReg', false);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_Summa', true);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_FedSumma', false);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_RegSumma', false);
		} else {
			this.DrugRequestGrid.setColumnHidden('DrugRequest_Summa', false);
			this.DrugRequestGrid.setColumnHidden('DrugRequest_Summa_Fed', true);
			this.DrugRequestGrid.setColumnHidden('DrugRequest_Summa_Reg', true);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_CountReq', false);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_CountFed', true);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_CountReg', true);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_Summa', false);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_FedSumma', true);
			this.DrugRequestGrid.setColumnHidden('DrugRequestPlan_RegSumma', true);
		}

		this.DrugRequestGrid.params = new Object();
		if (this.DrugRequestPeriod_id > 0) {
			this.DrugRequestGrid.params.DrugRequestPeriod_id = this.DrugRequestPeriod_id;
            this.medpersonal_combo.getStore().baseParams.DrugRequestPeriod_id = this.DrugRequestPeriod_id;
		} else {
            this.medpersonal_combo.getStore().baseParams.DrugRequestPeriod_id = null;
        }
		if (this.PersonRegisterType_id > 0) {
			this.DrugRequestGrid.params.PersonRegisterType_id = this.PersonRegisterType_id;
		}
		if (this.Lpu_id > 0) {
			this.DrugRequestGrid.params.Lpu_id = this.Lpu_id;
		}
		if (this.LpuUnit_id > 0) {
			this.DrugRequestGrid.params.LpuUnit_id = this.LpuUnit_id;
		}
		if (this.LpuSection_id > 0) {
			this.DrugRequestGrid.params.LpuSection_id = this.LpuSection_id;
		}
		if (this.MedPersonal_id) {
			this.DrugRequestGrid.params.MedPersonal_id = this.MedPersonal_id;
		}
		if (this.MoRequest_Data) {
			this.DrugRequestGrid.params.MoRequest_Data = this.MoRequest_Data;
		}

        this.RequestLimitData = new Object();

		/*this.DrugRequestGrid.loadData({
			params:{
				IsDlo: (!getGlobalOptions().isOnko && !getGlobalOptions().isRA)?1:0,
				checkDloDate: true
			}
		});*/

		this.loadFilterCombo();
		this.setPermissions();
		this.setVisibleElements();
		this.doSearch(true);
        this.InformationPanel.showToolbar(false);

		this.DrugRequestGrid.setParam('PersonRegisterType_id', this.PersonRegisterType_id, false);
		this.DrugRequestGrid.setParam('ARMType', this.ARMType, false);

		this.DrugGrid.getAction('action_refresh').setDisabled(true);

		this.DrugRequestGrid.addActions({
			name:'action_mdrv_actions',
			text:lang['deystviya'],
			menu: [{
				name: 'action_mdrv_set_editable',
				text: lang['vernut_zayavku_na_redaktirovanie'],
				tooltip: lang['vernut_zayavku_na_redaktirovanie'],
				handler: function() {
					wnd.setRequestEditable();
				},
				iconCls: 'edit16'
			},  {
				name: 'action_mdrv_set_approved',
				text: lang['utverdit_zayavku_mo'],
				tooltip: lang['utverdit_zayavku_mo'],
				handler: function() {
					wnd.setRequestMoApproved(true);
				},
				iconCls: 'edit16'
			}, {
				name: 'action_mdrv_remove_approved',
				text: 'Отменить статус «Утвержденная» для заявки МО',
				tooltip: 'Отменить статус «Утвержденная» для заявки МО',
				handler: function() {
					wnd.setRequestMoApproved(false);
				},
				iconCls: 'edit16'
			}, {
				name: 'action_mdrv_edit_luregion',
				text: 'Изменить участок в заявке врача',
				tooltip: 'Изменить участок в заявке врача',
				handler: function() {
					wnd.editLpuRegion();
				},
				iconCls: 'edit16'
			}],
			iconCls: 'actions16'
		});

		this.DrugRequestListTabs.setActiveTab(1);
		this.DrugRequestListTabs.setActiveTab(0);

		this.InformationPanel.clearData();
		this.InformationPanel.setData('region_request_name', (!Ext.isEmpty(arguments[0].RegionDrugRequest_Name) ? arguments[0].RegionDrugRequest_Name+' ' : '')+(!Ext.isEmpty(this.FirstCopy_Inf) ? this.FirstCopy_Inf : ''));
		this.InformationPanel.showData();

        this.getRequestMoPlanParams(function(data) {
            wnd.setRequestLimit(data);
        });

		//настройка видимости пунктов меню
        //leadermo - АРМ руководителя МО

		var hideable_actions_array = [{
            name: 'action_mdrv_set_approved',
            hidden: !(this.ARMType == 'leadermo')
        }, {
            name: 'action_mdrv_remove_approved',
            hidden: !(this.ARMType == 'leadermo')
        }];

        for(var i = 0; i < hideable_actions_array.length; i++) {
            this.setDisabledAction(this.DrugRequestGrid, 'action_mdrv_actions', hideable_actions_array[i].name, hideable_actions_array[i].hidden);
            this.hideAction(this.DrugRequestGrid, 'action_mdrv_actions', hideable_actions_array[i].name, hideable_actions_array[i].hidden);
        }
	},
	initComponent: function() {
		var wnd = this;

		this.InformationPanel = new sw.Promed.HtmlTemplatePanel({
            bbar: new Ext.Toolbar({
                style: 'background: transparent; border: 0px solid #C0C0C0;',
                width: 100,
                items: [{
                    xtype: 'button',
                    text: langs('Обновить'),
                    id: 'mdrv_PlanParamsRefreshButton',
                    iconCls: 'refresh16',
                    handler: function (){
                        wnd.recalculateRequestMoPlanParams(function(data) {
                            wnd.setRequestLimit(data);
                        })
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

		var tpl = "";
		tpl += "<table style='margin: 5px;'>";
		tpl += "<tr><td>{region_request_name}</td></tr>";
		tpl += "<tr><td>Статус заявки МО: <b>{mo_request_status}</b></td></tr>";
		tpl += "<tr><td>Сумма заявок врачей ({currency_name}): <b>{mo_request_sum}</b> <b>{mo_request_sum_fed}</b> <b>{mo_request_sum_reg}</b></td></tr>";
		tpl += "<tr><td>Объем финансирования ({currency_name}/чел.): <b>{mo_request_limit}</b> <b>{mo_request_limit_fed}</b> <b>{mo_request_limit_reg}</b></td></tr>";
		tpl += "<tr><td>Объем  заявок врачей ({currency_name}/чел.): <b>{mo_request_count_req}</b> <b>{mo_request_count_fed}</b> <b>{mo_request_count_reg}</b></td></tr>";
		tpl += "</table>";
		this.InformationPanel.setTemplate(tpl);

		this.lpuunit_combo = new sw.Promed.SwLpuUnitCombo ({
			name: 'LpuUnit_id',
			fieldLabel: lang['gruppa_otdeleniy'],
			width: 250,
			tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">'+
				'<span style="color:#4fff51;">{LpuUnit_Code}</span> {LpuUnit_Name}&nbsp;'+
				'</div></tpl>'),
			childrenList: ['LpuSection_id', 'MedPersonal_id'],
			listeners: {
				select: function(combo) {
					combo.childrenList.forEach(function(field_name){
						var f_combo = wnd.form.findField(field_name);
						if (!f_combo.disabled) {
							f_combo.getStore().baseParams[combo.name] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
							f_combo.loadData();
						}
					});
				}
			},
			setValueById: function(id) {
				var combo = this;
				combo.store.baseParams.LpuUnit_id = id;
				combo.store.load({
					callback: function(){
						combo.setValue(id);
						combo.store.baseParams.LpuUnit_id = null;
					}
				});
			},
			loadData: function() {
				var combo = this;
				combo.store.load({
					callback: function(){
						combo.setValue(null);
					}
				});
			}
		});

		this.lpusection_combo = new sw.Promed.SwBaseLocalCombo ({
			fieldLabel: lang['otdelenie'],
			hiddenName: 'LpuSection_id',
			displayField: 'LpuSection_Name',
			valueField: 'LpuSection_id',
			editable: true,
			width: 250,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{LpuSection_Name}&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
					{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' }
				],
				key: 'LpuSection_id',
				sortInfo: { field: 'LpuSection_Name' },
				url:'/?c=MzDrugRequest&m=loadLpuSectionCombo'
			}),
			childrenList: ['MedPersonal_id'],
			listeners: {
				'change': function(combo, newValue) {
					combo.childrenList.forEach(function(field_name){
						var f_combo = wnd.form.findField(field_name);
						if (!f_combo.disabled) {
							f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
							f_combo.loadData();
						}
					});
				}
			},
			setValueById: function(id) {
				var combo = this;
				combo.store.baseParams.LpuSection_id = id;
				combo.store.load({
					callback: function(){
						combo.setValue(id);
						combo.store.baseParams.LpuSection_id = null;
					}
				});
			},
			loadData: function() {
				var combo = this;
				combo.store.load({
					callback: function(){
						combo.setValue(null);
					}
				});
			}
		});

		this.medpersonal_combo = new sw.Promed.SwMedPersonalCombo ({
			hiddenName: 'MedPersonal_id',
			fieldLabel: lang['vrach'],
			width: 250,
			allowBlank: true,
			listWidth: 400,
			setValueById: function(id) {
				var combo = this;
				combo.store.baseParams.MedPersonal_id = id;
				combo.store.load({
					callback: function(){
						combo.setValue(id);
						combo.store.baseParams.MedPersonal_id = null;
					}
				});
			},
			loadData: function() {
				var combo = this;
				combo.store.load({
					callback: function(){
						combo.setValue(null);
					}
				});
			}
		});
        this.medpersonal_combo.getStore().baseParams.displayHmsSpec = 1; //флаг отображения специальности для главного внештатного специалиста

		this.DrugRequestFilterPanel = new sw.Promed.Panel({
			bodyStyle:'width:100%;background:#DFE8F6;padding:10px;',
			border: false,
			title: lang['filtryi'],
			collapsible: true,
			height: 90,//65,
			region: 'north',
			labelWidth: 110,
			layout: 'form',
			frame: true,
			id: 'DrugRequestFiltersPanel',
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					labelAlign: 'right',
					labelWidth: 120,
					items: [wnd.lpuunit_combo]
				}, {
					layout: 'form',
					labelAlign: 'right',
					labelWidth: 100,
					items: [wnd.lpusection_combo]
				}, {
					layout: 'form',
					labelAlign: 'right',
					labelWidth: 120,
					items: [{
						disabled: false,
						fieldLabel: lang['finansirovanie'],
						id: 'mdrv_DrugFinance_id',
						xtype: 'swcommonsprcombo',
						comboSubject: 'DrugFinance'
					}]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					labelAlign: 'right',
					labelWidth: 120,
					items: [{
						disabled: false,
						width: 250,
						id: 'mdrv_DrugRequestStatus_id',
						xtype: 'swdrugrequeststatuscombo',
						tabIndex:4211
					}]
				}, {
					layout: 'form',
					border: false,
					id: 'mdrv_DrugRequestMedPersonalFilter',
					labelAlign: 'right',
					labelWidth: 100,
					items: [wnd.medpersonal_combo]
				}]
			}]
		});

		this.DrugRequestFilterButtonsPanel = new sw.Promed.Panel({
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
						id: 'mdrv_ButtonSetFilter',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch();
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'mdrv_ButtonUnSetFilter',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function() {
							wnd.doSearch(true);
						}.createDelegate(this)
					}]
				}]
			}]
		});

		this.DrugRequestFilterMainPanel = getBaseFiltersFrame({
			region: 'north',
			height: 100,
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: wnd.WindowToolbar,
			items: [
				wnd.DrugRequestFilterPanel,
				wnd.DrugRequestFilterButtonsPanel
			],
			doSearch: wnd.doSearch.createDelegate(wnd)
		});

		this.DrugFilterFormPanel = new sw.Promed.Panel({
			region: 'center',
			layout: 'form',
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'mdrv_DrugFilterForm',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					width:350,
					items: [{
						xtype: 'textfield',
						name: 'DrugComplexMnnName_Name',
						fieldLabel: lang['mnn']
					}, {
						xtype: 'textfield',
						name: 'Tradenames_Name',
						fieldLabel: lang['torgovoe_naim']
					}, {
						layout: 'form',
						items:[{
							fieldLabel: lang['finansirovanie'],
							disabled: false,
							id: 'mdrv_DrugFinance_id2',
							xtype: 'swcommonsprcombo',
							comboSubject: 'DrugFinance'
						}]
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'ClsDrugForms_Name',
						fieldLabel: lang['lek_forma']
					}, {
						xtype: 'textfield',
						name: 'DrugComplexMnnDose_Name',
						fieldLabel: lang['dozirovka']
					}, {
						xtype: 'textfield',
						name: 'DrugComplexMnnFas_Name',
						fieldLabel: lang['fasovka']
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
						id: 'mdrv_BtnDrugGridSearch',
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
						id: 'mdrv_BtnDrugGridReset',
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
		
		this.DrugRequestGrid = new sw.Promed.ViewFrame({
			id: this.id + 'GridPanel',
			region: 'center',
			height: 303,
			minSize: 200,
			maxSize: 400,
			object: 'DrugRequest',
			editformclassname: 'swMzDrugRequestEditWindow',
			dataUrl: '/?c=MzDrugRequest&m=loadMPList',
			toolbar: true,
			autoLoadData: false,
			stringfields: [
				{name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', isparams: true, hidden: true},
				{name: 'LpuRegion_id', hidden: true},
				{name: 'LpuSection_id', hidden: true, isparams: true},
				{name: 'MedPersonal_id', isparams: true, hidden: true},
				{name: 'Lpu_Name', type: 'string', header: langs('МО'), width: 140},
				{name: 'LpuUnit_Name', header: langs('Группа отделений'), width: 200},
				{name: 'LpuSection_Name', header: langs('Отделение'), width: 200},
				{name: 'LpuRegion_Name', header: langs('Участок'), width: 200},
				{name: 'MedPersonal_FIO', header: langs('Врач'), width: 200},
				{name: 'DrugRequestPeriod_Name', header: langs('Период'), width: 140, hidden: true},
				{name: 'DrugRequestPeriod_id', hidden: true, isparams: true},
				{name: 'DrugRequestStatus_Name', header: lang['status'], width: 100},
				{name: 'DrugRequestStatus_id', hidden: true, hideable: false},
				{name: 'DrugRequestStatus_Code', hidden: true, hideable: false},
                {name: 'DrugRequestPlan_CountReq', align: 'right', header: langs('Объем')+' ('+getCurrencyName()+')', renderer: function(v, p, r){return r.get('LpuRegion_id') > 0 ? sw.Promed.Format.rurMoney(v) : '';}},
                {name: 'DrugRequestPlan_Summa', align: 'right', header: langs('Объем фин.')+' ('+getCurrencyName()+')', renderer: function(v, p, r){return r.get('LpuRegion_id') > 0 ? sw.Promed.Format.rurMoney(v) : '';}},
                {name: 'DrugRequestPlan_CountFed', align: 'right', header: langs('Объем фед.')+' ('+getCurrencyName()+')', renderer: function(v, p, r){return r.get('LpuRegion_id') > 0 ? sw.Promed.Format.rurMoney(v) : '';}},
                {name: 'DrugRequestPlan_FedSumma', align: 'right', header: langs('Объем фин. фед.')+' ('+getCurrencyName()+')', renderer: function(v, p, r){return r.get('LpuRegion_id') > 0 ? sw.Promed.Format.rurMoney(v) : '';}},
                {name: 'DrugRequestPlan_CountReg', align: 'right', header: langs('Объем рег.')+' ('+getCurrencyName()+')', renderer: function(v, p, r){return r.get('LpuRegion_id') > 0 ? sw.Promed.Format.rurMoney(v) : '';}},
                {name: 'DrugRequestPlan_RegSumma', align: 'right', header: langs('Объем фин. рег.')+' ('+getCurrencyName()+')', renderer: function(v, p, r){return r.get('LpuRegion_id') > 0 ? sw.Promed.Format.rurMoney(v) : '';}},
                {name: 'DrugRequest_insDT', type: 'date', header: langs('Внесен'), width: 80},
                {name: 'DrugRequest_updDT', type: 'date', header: langs('Изменен'), width: 80},
				{name: 'FedPrivilegePerson_Count', type: 'string', header: langs('ФЛ всего'), width: 140, hidden: true},
				{name: 'RegPrivilegePerson_Count', type: 'string', header: langs('РЛ всего'), width: 140, hidden: true},
				{name: 'DrugRequest_Summa', type: 'money', align: 'right', header: langs('Сумма'), hidden: false},
				{name: 'DrugRequest_Summa_Fed', type: 'money', align: 'right', header: langs('Сумма (фед.)'), hidden: false},
				{name: 'DrugRequest_Summa_Reg', type: 'money', align: 'right', header: langs('Сумма (рег.)'), hidden: false}
			],
			actions: [
				{name:'action_add', handler: function() { wnd.DrugRequestGrid.addRecord(); }},
				{name:'action_delete', url: '/?c=MzDrugRequest&m=delete'}
			],
			onDblClick: function() {
				if (!this.getAction('action_edit').isDisabled()) {
					this.getAction('action_edit').execute();
				} else {
					this.getAction('action_view').execute();
				}
			},
			onRowSelect: function(sm,rowIdx,record) {
				var isLeaderMo = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'leadermo'; }) > -1);
				var isThisLpuUser = (wnd.Lpu_id == getGlobalOptions().lpu_id);

				this.getAction('action_mdrv_actions').setDisabled(record.get('DrugRequest_id') <= 0);
				this.setActionDisable('action_mdrv_set_editable', record.get('DrugRequest_id') <= 0 || record.get('DrugRequestStatus_Code') != 2);
				this.setActionDisable('action_mdrv_set_approved', !isLeaderMo);
				this.setActionDisable('action_mdrv_remove_approved', !isLeaderMo);
				this.setActionDisable('action_mdrv_edit_lpuregion', !isThisLpuUser || record.get('DrugRequestStatus_Code') != 1); //1 - Начальная

				if (record.get('DrugRequest_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}

				this.ViewActions.action_view.setDisabled(record.get('DrugRequest_id') <= 0);
			},
			setActionDisable: function(action, disable) {
				var actions = this.getAction('action_mdrv_actions').items[0].menu.items;
				var menu_actions = this.ViewContextMenu.items.get(11).menu.items;
				actions.each(function(a) {
					if (a.name == action) {
						if (disable) {
							a.disable();
						} else {
							a.enable();
						}
					}
				});
				menu_actions.each(function(a) {
					if (a.name == action) {
						if (disable) {
							a.disable();
						} else {
							a.enable();
						}
					}
				});
			},
			addRecord: function() {
				var viewframe = this;

				var form_params = new Object();
				form_params.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id;
				form_params.PersonRegisterType_id = wnd.PersonRegisterType_id;
				form_params.DrugRequestKind_id = wnd.DrugRequestKind_id;
				form_params.DrugGroup_id = wnd.DrugGroup_id;
				form_params.Lpu_id = wnd.Lpu_id;

				if (wnd.LpuUnit_id > 0) {
					form_params.LpuUnit_id = wnd.LpuUnit_id;
				}
				if (wnd.LpuSection_id > 0) {
					form_params.LpuSection_id = wnd.LpuSection_id;
				}
				if (wnd.LpuRegion_id > 0) {
					form_params.LpuRegion_id = wnd.LpuRegion_id;
				}
				if (wnd.MedPersonal_id > 0) {
					form_params.MedPersonal_id = wnd.MedPersonal_id;
				}

				getWnd('swMzDrugRequestAddWindow').show({
					FormParams: form_params,
					onSave: function(owner, DrugRequest_id) {
						viewframe.setParam('DrugRequest_id', DrugRequest_id, false);
						viewframe.editRecord('edit');
						viewframe.refreshRecords(null, 0);
						delete viewframe.params.DrugRequest_id;
						wnd.setRequestSum();
					}
				});
			},
			onLoadData: function() {
				this.DataState = 'loaded';
				this.getAction('action_refresh').setDisabled(false);
				wnd.setRequestSum();
				wnd.refreshRequestMoStatus();
			},
			afterDeleteRecord: function() {
				wnd.setRequestSum();
				wnd.refreshRequestMoStatus();
			}
		});

		this.DrugGrid = new sw.Promed.ViewFrame({
			title: lang['medikamentyi'],
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
			dataUrl: '/?c=MzDrugRequest&m=loadMzDrugRequestMoDrugGrid',
			region: 'center',
			id: 'mdrv_DrugGrid',
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
				{name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 120},
				{name: 'DrugRequestRow_Kolvo', type: 'float', header: lang['kol-vo'], width: 80},
				{name: 'DrugRequestRow_Price', type: 'money', header: lang['tsena'], width: 80, align: 'right'},
				{name: 'DrugRequestRow_Summa', type: 'money', header: lang['summa'], width: 80, align: 'right'},
                {name: 'DrugListRequest_Comment', type: 'string', header: lang['primechanie'], width: 140},
				{name: 'isProblem', hidden: true},
				{name: 'isProblemTorg', hidden: true}
			],
			toolbar: true,
			onLoadData: function() {
				this.DataState = 'loaded';
				this.getAction('action_refresh').setDisabled(false);
			}
		});

		this.DrugRequestListTabs = new Ext.TabPanel({
			region: 'center',
			autoScroll: true,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			enableTabScroll: true,
			height: 170,
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[{
				id: 'mdrv_request_list',
				title: lang['spisok_zayavok'],
				layout: 'border',
				border: false,
				items: [
					wnd.DrugRequestFilterMainPanel,
					wnd.DrugRequestGrid
				]
			}, {
				id: 'mdrv_drug_list',
				title: langs('Медикаменты заявки врача'),
				layout: 'border',
				border: false,
				items: [
					wnd.DrugFilterPanel,
					wnd.DrugGrid
				]
			}],
			listeners: {
				//tabchange: wnd.onDrugRequestTabsChange.createDelegate(wnd)
			}
		});

		Ext.apply(this, {
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items: [
				wnd.InformationPanel,
				wnd.DrugRequestListTabs
			]
		});
		sw.Promed.swMzDrugRequestViewWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.DrugRequestFilterMainPanel.getForm();
	}
});
