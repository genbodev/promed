/**
* swMzDrugRequestMoViewWindow - окно просмотра заявок МО
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov Rustam
* @version      10.2012
* @comment      
*/
sw.Promed.swMzDrugRequestMoViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['zayavochnaya_kampaniya_spisok_zayavok_mo'],
	layout: 'border',
	id: 'MzDrugRequestMoViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
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
	doCalculate:  function() { //подсчет и вывод информации на инф панель
		var wnd = this;
		var panel = wnd.InformationPanel;

		/*var request_data = new Object({
			FedDrugRequestQuota_Person: 0,
			FedDrugRequestQuota_Total: 0,
			RegDrugRequestQuota_Person: 0,
			RegDrugRequestQuota_Total: 0
		});*/

		//panel.setData('overflow_reg'+i, Math.round(overflow_reg*100)/100);

		panel.showData();
	},
	doSearch: function(clear) {
		var wnd = this;
        var filterForm = wnd.SearchPanel.getForm();

		if (clear) {
            filterForm.reset();
		}
			
		var params = new Object();
		params.DrugRequestPeriod_id = wnd.DrugRequestPeriod_id;
		params.PersonRegisterType_id = wnd.PersonRegisterType_id;
		params.DrugRequestKind_id = wnd.DrugRequestKind_id;
		params.DrugGroup_id = wnd.DrugGroup_id;
		params.DrugRequest_Version = wnd.DrugRequest_Version;
		params.limit = 100;
		params.start =  0;
		params.DrugRequestStatus_id = filterForm.findField('DrugRequestStatus_id').getValue();
		params.KLAreaStat_id = filterForm.findField('KLAreaStat_id').getValue();
		params.OrgServiceTerr_Org_id = this.OrgServiceTerr_Org_id;

		this.SearchGrid.removeAll();
		this.SearchGrid.loadData({
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
		params.start = 0;
		params.limit = 100;

		this.DrugGrid.removeAll();
		this.DrugGrid.loadData({
			globalFilters: params
		});
	},
    setRegionDrugRequestStatus: function(data) {
        var wnd = this;

        if (data != undefined) {
            wnd.RegionDrugRequestStatus_Code = data.DrugRequestStatus_Code;
            wnd.RegionDrugRequestStatus_Name = data.DrugRequestStatus_Name;
            wnd.InformationPanel.setData('request_status_name', wnd.RegionDrugRequestStatus_Name);
            wnd.InformationPanel.showData();
            wnd.SearchGrid.getAction('action_add').setDisabled(wnd.RegionDrugRequestStatus_Code != 1 && wnd.RegionDrugRequestStatus_Code != 4); //1 - Начальная; 4 - Нулевая
            wnd.setDisabledAction(wnd.SearchGrid, 'action_mdrmv_actions', 'action_mdrmv_set_kolvo', wnd.RegionDrugRequestStatus_Code.inlist([3,7])); //3 - Утвержденная; 7 - Утвержденная МЗ
            wnd.setDisabledAction(wnd.SearchGrid, 'action_mdrmv_actions', 'action_mdrmv_set_limit', wnd.RegionDrugRequestStatus_Code.inlist([3,7])); //3 - Утвержденная; 7 - Утвержденная МЗ
        } else {
            Ext.Ajax.request({
                params:{
                    DrugRequest_id: wnd.DrugRequest_id
                },
                success: function (response) {
                    var result = Ext.util.JSON.decode(response.responseText);

                    if (result && !Ext.isEmpty(result.DrugRequestStatus_Code)) {
                        wnd.RegionDrugRequestStatus_Code = result.DrugRequestStatus_Code;
                        wnd.RegionDrugRequestStatus_Name = result.DrugRequestStatus_Name;
                        wnd.InformationPanel.setData('request_status_name', wnd.RegionDrugRequestStatus_Name);
                        wnd.InformationPanel.showData();
                        wnd.SearchGrid.getAction('action_add').setDisabled(wnd.RegionDrugRequestStatus_Code != 1 && wnd.RegionDrugRequestStatus_Code != 4); //1 - Начальная; 4 - Нулевая
                        //wnd.setDisabledAction(wnd.SearchGrid, 'action_mdrmv_actions', 'action_mdrmv_set_kolvo', wnd.RegionDrugRequestStatus_Code.inlist([3,7])); //3 - Утвержденная; 7 - Утвержденная МЗ
                        //wnd.setDisabledAction(wnd.SearchGrid, 'action_mdrmv_actions', 'action_mdrmv_set_limit', wnd.RegionDrugRequestStatus_Code.inlist([3,7])); //3 - Утвержденная; 7 - Утвержденная МЗ
                        wnd.setDisabledAction(wnd.SearchGrid, 'action_mdrmv_actions', 'action_mdrmv_set_kolvo', wnd.RegionDrugRequestStatus_Code != 3); //3 - Утвержденная
                        wnd.setDisabledAction(wnd.SearchGrid, 'action_mdrmv_actions', 'action_mdrmv_set_limit', wnd.RegionDrugRequestStatus_Code != 3); //3 - Утвержденная
                    }
                },
                url:'/?c=MzDrugRequest&m=getDrugRequestStatus'
            });
        }
    },
    actionOpenDrugRequest: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (record.get('DrugRequest_id') > 0) {
            getWnd('swMzDrugRequestViewWindow').show({
                MoRequest_Data: {
                    FedDrugRequestQuota_Person: wnd.DrugRequestData.FedDrugRequestQuota_Person ? wnd.DrugRequestData.FedDrugRequestQuota_Person : null,
                    RegDrugRequestQuota_Person: wnd.DrugRequestData.RegDrugRequestQuota_Person ? wnd.DrugRequestData.RegDrugRequestQuota_Person : null,
                    FedDrugRequestQuota_Reserve: wnd.DrugRequestData.FedDrugRequestQuota_Reserve ? wnd.DrugRequestData.FedDrugRequestQuota_Reserve : null,
                    RegDrugRequestQuota_Reserve: wnd.DrugRequestData.RegDrugRequestQuota_Reserve ? wnd.DrugRequestData.RegDrugRequestQuota_Reserve : null,
                    FedRequestLimit: record.get('DrugRequestPlan_FedSumma'),
                    RegRequestLimit: record.get('DrugRequestPlan_RegSumma')
                },
                RegionDrugRequest_Name: wnd.RegionDrugRequest_Name,
                FirstCopy_Inf: wnd.FirstCopy_Inf,
                action: record.get('DrugRequestStatus_Code') == '3' ? 'view' : 'edit', //3 - Утвержденная
                DrugRequestPeriod_id: wnd.DrugRequestPeriod_id,
                PersonRegisterType_id: wnd.PersonRegisterType_id,
                DrugRequestKind_id: wnd.DrugRequestKind_id,
                DrugGroup_id: wnd.DrugGroup_id,
                DrugRequest_Version: wnd.DrugRequest_Version,
                Lpu_id: record.get('Lpu_id'),
                Lpu_Name: record.get('Lpu_Name'),
                DrugRequestKind_Name: record.get('DrugRequestKind_Name'),
                DrugRequestPeriod_Name: record.get('DrugRequestPeriod_Name'),
                PersonRegisterType_Name: record.get('PersonRegisterType_Name'),
                onHide: function() {
                    wnd.SearchGrid.refreshRecords(null, 0);
                },
                ARMType: wnd.ARMType
            });
        }
    },
    actionSetEditable: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') != 1&& record.get('DrugRequestStatus_Code') != 4) {
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_smenit_status_zayavki']);
                    loadMask.hide();
                    wnd.hide();
                },
                params:{
                    DrugRequest_id: record.get('DrugRequest_id'),
                    DrugRequestStatus_Code: getRegionNick() == 'ufa' ? 2 : 1 //2 - Сформированная; 1 - Начальная;
                },
                success: function (response) {
                    wnd.SearchGrid.refreshRecords(null,0);
                    var res = Ext.util.JSON.decode(response.responseText);
                    if (!res || !res.Error_Msg || res.Error_Msg == '' || res.Error_Msg == null) {
                        wnd.setRegionDrugRequestStatus();
                        Ext.Ajax.request({
                            params:{
                                DrugRequest_id: record.get('DrugRequest_id'),
                                event: 'mo_request_return_edit'
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
                    }
                },
                url:'/?c=MzDrugRequest&m=changeDrugRequestStatus'
            });
        }
    },
    actionSetApprovedMz: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') != 1 && record.get('DrugRequestStatus_Code') != 4) {
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_smenit_status_zayavki']);
                    loadMask.hide();
                    wnd.hide();
                },
                params:{
                    DrugRequest_id: record.get('DrugRequest_id'),
                    DrugRequestStatus_Code: 7 //Утвержденная МЗ
                },
                success: function (response) {
                    wnd.SearchGrid.refreshRecords(null,0);
                },
                url:'/?c=MzDrugRequest&m=changeDrugRequestStatus'
            });
        }
    },
    actionSetConformed: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') != 1 && record.get('DrugRequestStatus_Code') != 4) {
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_smenit_status_zayavki']);
                    loadMask.hide();
                    wnd.hide();
                },
                params:{
                    DrugRequest_id: record.get('DrugRequest_id'),
                    DrugRequestStatus_Code: 6 //Согласована
                },
                success: function (response) {
                    wnd.SearchGrid.refreshRecords(null,0);
                },
                url:'/?c=MzDrugRequest&m=changeDrugRequestStatus'
            });
        }
    },
    actionSetApproved: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') == 2) { //2 - Сформированная
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_smenit_status_zayavki']);
                    loadMask.hide();
                    wnd.hide();
                },
                params:{
                    DrugRequest_id: record.get('DrugRequest_id'),
                    DrugRequestStatus_Code: 3 //Утвержденная
                },
                success: function (response) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (result.success) {
                        wnd.SearchGrid.refreshRecords(null,0);
                        wnd.setRegionDrugRequestStatus();
                        Ext.Ajax.request({
                            params:{
                                DrugRequest_id: record.get('DrugRequest_id'),
                                event: 'mo_request_set_confirmed'
                            },
                            success: function (response) {
                                /*var result = Ext.util.JSON.decode(response.responseText);
                                 if (result[0] && result[0].Message_id > 0) {
                                 getWnd('swMessagesViewWindow').show({
                                 mode: 'openMessage',
                                 message_data: result[0]
                                 });
                                 }*/
                            },
                            url:'/?c=MzDrugRequest&m=getNotice'
                        });
                    } else if (result.Error_Type && result.Error_Data) {
                        getWnd('swMzDrugRequestExtendedErrorViewWindow').show(result);
                    }
                },
                url:'/?c=MzDrugRequest&m=changeDrugRequestStatus'
            });
        }
    },
    actionRecalculateByFin: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (wnd.DrugRequest_id > 0 && record.get('Lpu_id') > 0 && record.get('DrugRequestStatus_Code') == 1) { //1 - Начальная
            sw.swMsg.show({
                icon: Ext.MessageBox.QUESTION,
                msg: 'Будет выполнен перерасчет количества ЛП в заявке. После перерасчета количество ЛП в заявках будет равно количеству ЛП, указанному в заявках с реальной потребностью, уменьшенному пропорционально доле объема финансирования заявки  к сумме реальной потребности. Вы действительно желаете выполнить такой расчет? ',
                title: 'Вопрос',
                buttons: Ext.Msg.YESNO,
                fn: function (buttonId, text, obj) {
                    if ('yes' == buttonId) {
                        Ext.Ajax.request({
                            failure: function () {
                                sw.swMsg.alert(langs('Ошибка'), langs('Не удалось выполнить расчет'));
                                loadMask.hide();
                                wnd.hide();
                            },
                            params: {
                                RegionDrugRequest_id: wnd.DrugRequest_id,
                                Lpu_List: record.get('Lpu_id'),
                                status_change_disabled: 1
                            },
                            success: function (response) {
                                var result = Ext.util.JSON.decode(response.responseText);
                                if (result.success) {
                                    sw.swMsg.alert(langs('Сообщение'), langs('Расчет успешно завершен'));
                                }
                            },
                            url: '/?c=MzDrugRequest&m=recalculateDrugRequestByFin'
                        });
                    }
                }
            });
        }
    },
    actionRemoveApproved: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (record.get('DrugRequest_id') > 0 && record.get('DrugRequestStatus_Code') == 3) { //3 - Утвержденная
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_smenit_status_zayavki']);
                    loadMask.hide();
                    wnd.hide();
                },
                params:{
                    DrugRequest_id: record.get('DrugRequest_id'),
                    DrugRequestStatus_Code: 2 //Сформированная
                },
                success: function (response) {
                    wnd.SearchGrid.refreshRecords(null,0);
                    wnd.setRegionDrugRequestStatus();
                    Ext.Ajax.request({
                        params:{
                            DrugRequest_id: record.get('DrugRequest_id'),
                            event: 'mo_request_set_formed'
                        },
                        success: function (response) {
                            /*var result = Ext.util.JSON.decode(response.responseText);
                             if (result[0] && result[0].Message_id > 0) {
                             getWnd('swMessagesViewWindow').show({
                             mode: 'openMessage',
                             message_data: result[0]
                             });
                             }*/
                        },
                        url:'/?c=MzDrugRequest&m=getNotice'
                    });
                },
                url:'/?c=MzDrugRequest&m=changeDrugRequestStatus'
            });
        }
    },
    actionSetApprovedAll: function(check_status) {
        var wnd = this;
        Ext.Ajax.request({
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if (result) {
                    if (!Ext.isEmpty(result.Status_Msg)) {
                        sw.swMsg.show({
                            buttons: Ext.Msg.YESNO,
                            fn:function (buttonId, text, obj) {
                                if (buttonId == 'yes') {
                                    //повторный вызов функции, но уже без проверки статуса
                                    wnd.actionSetApprovedAll(false);
                                }
                            },
                            icon: Ext.MessageBox.QUESTION,
                            msg: result.Status_Msg+lang['prodoljit'],
                            title:lang['podtverjdenie']
                        });
                    } else {
                        wnd.SearchGrid.refreshRecords(null,0);
                    }
                }
            },
            params: {
                RegionDrugRequest_id: wnd.DrugRequest_id,
                check_status: check_status ? 1 : 0
            },
            url:'/?c=MzDrugRequest&m=approveAllDrugRequestMo'
        });
    },
    actionCompare: function() {
        var wnd = this;
        var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
        if (record.get('DrugRequest_id') > 0) {
            DrugRequest_id = record.get('DrugRequest_id');
            printBirt({
                'Report_FileName': 'dlo_kontrol_kolvoLS_ArchiveCopy.rptdesign',
                'Report_Params': '&paramDrugRequest='+DrugRequest_id,
                'Report_Format': 'pdf'
            });
        }
    },
    calculatePlanRegionParams: function(mode) { //функция для количества льготников или обьемов финансирования для заявок участков и МО
        var wnd = this;
        Ext.Ajax.request({
            params:{
                RegionDrugRequest_id: wnd.DrugRequest_id,
                mode: mode
            },
            success: function (response) {
                wnd.doSearch();
            },
            failure:function () {
                sw.swMsg.alert(lang['oshibka'], lang['sohranenie_ne_udalos']);
            },
            url:'/?c=MzDrugRequest&m=calculateDrugRequestPlanRegionParams'
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
		var wnd = this;
        var region = getGlobalOptions().region && !Ext.isEmpty(getGlobalOptions().region.nick) ? getGlobalOptions().region.nick : null;

		sw.Promed.swMzDrugRequestMoViewWindow.superclass.show.apply(this, arguments);

        this.ARMType = !Ext.isEmpty(arguments[0].ARMType) ? arguments[0].ARMType : null;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.DrugRequest_id = arguments[0].DrugRequest_id ? arguments[0].DrugRequest_id : null;
		this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id ? arguments[0].DrugRequestPeriod_id : null;
		this.PersonRegisterType_id = arguments[0].PersonRegisterType_id ? arguments[0].PersonRegisterType_id : null;
		this.DrugRequestKind_id = arguments[0].DrugRequestKind_id ? arguments[0].DrugRequestKind_id : null;
		this.DrugGroup_id = arguments[0].DrugGroup_id ? arguments[0].DrugGroup_id : null;
		this.DrugRequest_Version = arguments[0].DrugRequest_Version ? arguments[0].DrugRequest_Version : null;
		this.OrgServiceTerr_Org_id = arguments[0].OrgServiceTerr_Org_id ? arguments[0].OrgServiceTerr_Org_id : null;
		this.RegionRequestData = null;
		this.RegionDrugRequest_Name = arguments[0].DrugRequest_Name ? arguments[0].DrugRequest_Name : null;
		this.RegionDrugRequestStatus_Name = null;
		this.RegionDrugRequestStatus_Code = null;
		this.SvodDrugRequest_Name = arguments[0].SvodDrugRequest_Name ? arguments[0].SvodDrugRequest_Name : null;
		this.FirstCopy_Inf = arguments[0].FirstCopy_Inf ? arguments[0].FirstCopy_Inf : null;

		this.InformationPanel.clearData();
		this.InformationPanel.setData('request_name', this.RegionDrugRequest_Name+(!Ext.isEmpty(this.FirstCopy_Inf) ? ' '+this.FirstCopy_Inf : ''));
		this.InformationPanel.setData('svod_request_name', !Ext.isEmpty(this.SvodDrugRequest_Name) ? langs('Сводная заявка') + this.SvodDrugRequest_Name : '');
		this.InformationPanel.showData();
        this.DrugGrid.removeAll();

		this.form.reset();

		wnd.DrugRequestListTabs.setActiveTab(1);
		wnd.DrugRequestListTabs.setActiveTab(0);

        wnd.DrugGrid.setReadOnly(this.DrugRequest_Version > 0);
        wnd.SearchGrid.setReadOnly(this.DrugRequest_Version > 0);

		wnd.DrugGrid.getAction('action_refresh').setDisabled(true);
		wnd.SearchGrid.getAction('action_add').setDisabled(true);
        wnd.SearchGrid.setParam('RegionDrugRequest_id', wnd.DrugRequest_id, false);
        wnd.SearchGrid.setParam('onSave', function() {
            wnd.SearchGrid.refreshRecords(null,0);
        }, false);

		wnd.SearchGrid.addActions({
			name:'action_mdrmv_actions',
			text:lang['deystviya'],
			menu: [{
				name: 'action_mdrmv_mp_request',
				text: lang['otkryit_zayavki_vrachey'],
				tooltip: lang['otkryit_zayavki_vrachey'],
				handler: function() {
					wnd.actionOpenDrugRequest();
				},
				iconCls: 'view16'
			}, {
                name: 'action_mdrmv_set_kolvo',
                text: 'Подсчитать количество человек в регистре',
                tooltip: 'Подсчитать количество человек в регистре',
                handler: function() {
                    wnd.calculatePlanRegionParams('all');
                },
                iconCls: 'edit16'
            }, {
                name: 'action_mdrmv_set_limit',
                text: 'Рассчитать объемы финансирования',
                tooltip: 'Рассчитать объемы финансирования',
                handler: function() {
                    wnd.calculatePlanRegionParams('sum');
                },
                iconCls: 'edit16'
            }, {
                name: 'action_mdrmv_compare',
                text: lang['sravnit_zayavku_s_predyiduschey_versiey'],
                tooltip: lang['sravnit_zayavku_s_predyiduschey_versiey'],
                handler: function() {
                    wnd.actionCompare();
                },
                iconCls: 'view16'
            }, {
				name: 'action_mdrmv_set_editable',
				text: lang['vernut_zayavku_mo_na_redaktirovanie'],
				tooltip: lang['vernut_zayavku_mo_na_redaktirovanie'],
				handler: function() {
					wnd.actionSetEditable();
				},
				iconCls: 'edit16'
			}, {
				name: 'action_mdrmv_set_conformed',
				text: lang['soglasovat_zayavku'],
				tooltip: lang['soglasovat_zayavku'],
				handler: function() {
					wnd.actionSetConformed();
				},
				iconCls: 'edit16'
			}, {
                name: 'action_mdrmv_set_approved_mz',
                text: lang['utverdit_zayavku_mz'],
                tooltip: lang['utverdit_zayavku_mz'],
                handler: function() {
                    wnd.actionSetApprovedMz();
                },
                iconCls: 'edit16'
            }, {
				name: 'action_mdrmv_set_approved',
				text: lang['utverdit_zayavku_mo'],
				tooltip: lang['utverdit_zayavku_mo'],
				handler: function() {
                    wnd.actionSetApproved();
				},
				iconCls: 'edit16'
			}, {
				name: 'action_mdrmv_remove_approved',
				text: lang['otmenit_status_utverjdennaya'],
				tooltip: lang['otmenit_status_utverjdennaya'],
				handler: function() {
                    wnd.actionRemoveApproved();
				},
				iconCls: 'edit16'
			}, {
                name: 'action_mdrmv_set_approved_all',
                text: lang['utverdit_rezultatyi_zayavochnoy_kampanii'],
                tooltip: lang['utverdit_rezultatyi_zayavochnoy_kampanii'],
                handler: function() {
                    wnd.actionSetApprovedAll(true);
                },
                iconCls: 'edit16'
            }, {
                name: 'action_mdrmv_recalculate_by_fin',
                text: langs('Выполнить расчет лимитированной заявки'),
                tooltip: langs('Выполнить расчет лимитированной заявки'),
                handler: function () {
                    wnd.actionRecalculateByFin();
                },
                iconCls: 'edit16'
            }],
			iconCls: 'actions16'
		});

        if (wnd.DrugRequest_id > 0) {
			var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
			loadMask.show();
			Ext.Ajax.request({
				params:{
					DrugRequest_id: wnd.DrugRequest_id
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0]) {
                        wnd.DrugRequestData = result[0];
                        wnd.setRegionDrugRequestStatus({
                            DrugRequestStatus_Code: result[0].DrugRequestStatus_Code,
                            DrugRequestStatus_Name: result[0].DrugRequestStatus_Name
                        });
                    }
					wnd.doSearch(true, true);
					loadMask.hide();
				},
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_o_regionalnoy_zayavke']);
					loadMask.hide();
				},
				url:'/?c=MzDrugRequest&m=load'
			});	
		} else {
			wnd.doSearch(true, true);
		}

        //настройка видимости пунктов меню
        //minzdravdlo - АРМ специалиста ЛЛО ОУЗ
        //mzchieffreelancer - АРМ главного внештатного специалиста МЗ
        //touz - АРМ специалиста ТОУЗ

        var hideable_actions_array = [{
            name: 'action_mdrmv_set_limit',
            hidden: !(this.ARMType == 'minzdravdlo')
        }, {
            name: 'action_mdrmv_compare',
            hidden: !(this.ARMType == 'minzdravdlo')
        }, {
            name: 'action_mdrmv_set_editable',
            hidden: !(this.ARMType == 'minzdravdlo' || this.ARMType == 'mzchieffreelancer' || this.ARMType == 'touz')
        }, {
            name: 'action_mdrmv_set_conformed',
            hidden: !(this.ARMType == 'mzchieffreelancer' && (region == 'saratov' || region == 'ufa'))
        }, {
            name: 'action_mdrmv_set_approved_mz',
            hidden: !((this.ARMType == 'minzdravdlo' || this.ARMType == 'touz') && region == 'saratov')
        }, {
            name: 'action_mdrmv_set_approved',
            hidden: !(this.ARMType == 'minzdravdlo' || this.ARMType == 'touz')
        }, {
            name: 'action_mdrmv_remove_approved',
            hidden: !(this.ARMType == 'minzdravdlo' || this.ARMType == 'mzchieffreelancer')
        }, {
            name: 'action_mdrmv_set_approved_all',
            hidden: !(this.ARMType == 'minzdravdlo' && region != 'saratov' && region != 'ufa')
        }];

        for(var i = 0; i < hideable_actions_array.length; i++) {
            this.setDisabledAction(this.SearchGrid, 'action_mdrmv_actions', hideable_actions_array[i].name, hideable_actions_array[i].hidden);
            this.hideAction(this.SearchGrid, 'action_mdrmv_actions', hideable_actions_array[i].name, hideable_actions_array[i].hidden);
        }

        this.SearchGrid.setActionHidden('action_add', !(this.ARMType == 'minzdravdlo'));
        this.SearchGrid.setActionHidden('action_edit', !(this.ARMType == 'minzdravdlo' && region != 'ufa'));
        this.SearchGrid.setActionHidden('action_delete', !(this.ARMType == 'minzdravdlo'));

        this.setDisabledAction(this.SearchGrid, 'action_mdrmv_actions', 'action_mdrmv_set_kolvo', true);
        this.setDisabledAction(this.SearchGrid, 'action_mdrmv_actions', 'action_mdrmv_set_limit', true);

        if(this.isCommonPersonRegisterType()){
            this.findById('mdrmv_DrugFinance_id').showContainer();
            this.findById('mdrmv_DrugFinance_id').getStore().baseParams.where = "where DrugFinance_Code in ('2','20')";
            this.findById('mdrmv_DrugFinance_id').getStore().load();
        } else {
            this.findById('mdrmv_DrugFinance_id').hideContainer();
        }
        this.SearchGrid.setColumnHidden('DrugRequestRow_Summa', wnd.isCommonPersonRegisterType());
        this.SearchGrid.setColumnHidden('DrugRequestPlan_Summa', wnd.isCommonPersonRegisterType());
        this.SearchGrid.setColumnHidden('DrugRequestPlan_Kolvo', wnd.isCommonPersonRegisterType());
        this.SearchGrid.setColumnHidden('DrugRequestRow_SummaFed', !wnd.isCommonPersonRegisterType());
        this.SearchGrid.setColumnHidden('DrugRequestPlan_FedSumma', !wnd.isCommonPersonRegisterType());
        this.SearchGrid.setColumnHidden('DrugRequestPlan_FedKolvo', !wnd.isCommonPersonRegisterType());
        this.SearchGrid.setColumnHidden('DrugRequestRow_SummaReg', !wnd.isCommonPersonRegisterType());
        this.SearchGrid.setColumnHidden('DrugRequestPlan_RegSumma', !wnd.isCommonPersonRegisterType());
        this.SearchGrid.setColumnHidden('DrugRequestPlan_RegKolvo', !wnd.isCommonPersonRegisterType());
	},
	initComponent: function() {
		var wnd = this;
		
		wnd.SearchPanel = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px',
			border: false,
			region: 'north',
			autoHeight: true,
			frame: true,
			labelWidth: 105,
			labelAlign: 'right',
			title: lang['filtr'],
			collapsible: true,
			items: [{
				layout: 'column',
				labelWidth: 110,
				items: [{
					layout: 'form',
					items: [{
						codeField: 'KLAreaStat_Code',
						disabled: false,
						displayField: 'KLArea_Name',
						editable: true,
						enableKeyEvents: true,
						fieldLabel: lang['territoriya'],
						hiddenName: 'KLAreaStat_id',
						store: new Ext.db.AdapterStore({
							autoLoad: true,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'KLAreaStat_id', type: 'int' },
								{ name: 'KLAreaStat_Code', type: 'int' },
								{ name: 'KLArea_Name', type: 'string' },
								{ name: 'KLCountry_id', type: 'int' },
								{ name: 'KLRGN_id', type: 'int' },
								{ name: 'KLSubRGN_id', type: 'int' },
								{ name: 'KLCity_id', type: 'int' },
								{ name: 'KLTown_id', type: 'int' }
							],
							key: 'KLAreaStat_id',
							sortInfo: {
								field: 'KLAreaStat_Code',
								direction: 'ASC'
							},
							tableName: 'KLAreaStat'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
							'</div></tpl>'
						),
						valueField: 'KLAreaStat_id',
						width: 300,
						xtype: 'swbaselocalcombo'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'swdrugrequeststatuscombo',
						hiddenName: 'DrugRequestStatus_id',
						fieldLabel: lang['status_zayavki'],
						width: 300,
						listWidth: 400,
                        listeners: {
                            expand: function() {
                                //скрываем лишние статусы
                                this.getStore().filterBy(function(record) {
                                    return (record.get('DrugRequestStatus_Code') != 2 && record.get('DrugRequestStatus_Code') != 5);
                                });
                            }
                        }
					}]
				}, {
					layout: 'form',
					bodyStyle:'background:#DFE8F6;padding-left:15px;padding-right:5px;',
					items: [{
						xtype: 'button',
						text: lang['poisk'],
						minWidth: 80,
						handler: function () {
							wnd.doSearch();
						}
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'button',
						text: lang['sbros'],
						minWidth: 80,
						handler: function () {
							wnd.doSearch(true);
						}
					}]
				}]
			}],
			setFilter: function() {
				var store = wnd.SearchGrid.getGrid().getStore();
				store.filterBy(function(record){
					return (record.get('Filtered') == 1);
				});
			},
			doSearch: function(clear) {
				var form = this.getForm();
				var store = wnd.SearchGrid.getGrid().getStore();
				if (clear) {
					form.reset();
					store.clearFilter();
				} else {
					this.setFilter();
				}
				wnd.doCalculate();
			}
		});
		
		wnd.InformationPanel = new sw.Promed.HtmlTemplatePanel({
			region: 'north',
			win: wnd
		});
		
		var tpl = "";
		tpl += "<table style='margin: 5px;'>";
		tpl += "<tr><td>{request_name}</td></tr>";
		tpl += "<tr><td>Статус заявочной кампании: {request_status_name}</td></tr>";
		tpl += "<tr><td>{svod_request_name}</td></tr>";
		tpl += "</table>";
		wnd.InformationPanel.setTemplate(tpl);

		this.DrugFilterFormPanel = new sw.Promed.Panel({
			region: 'center',
			layout: 'form',
			autoScroll: true,
			bodyStyle: 'padding: 0.5em;',
			border: false,
			frame: true,
			id: 'mdrmv_DrugFilterForm',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfield',
						name: 'DrugComplexMnnName_Name',
						fieldLabel: lang['mnn']
					}, {
						xtype: 'textfield',
						name: 'Tradenames_Name',
						fieldLabel: lang['torgovoe_naim']
					}, {
                        xtype: 'swdrugfinancecombo',
                        name: 'DrugFinance_id',
                        id: 'mdrmv_DrugFinance_id',
                        fieldLabel: lang['finansirovanie']
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
						id: 'mdrmv_BtnDrugGridSearch',
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
						id: 'mdrmv_BtnDrugGridReset',
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

        var editor_field_css = 'background-color: #dfe8f6;';
        var editor_field_config = {
            allowNegative: false
        };
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			id: this.id + 'ViewFrame',
			actions: [
                {name: 'action_add'},
				{
					name: 'action_edit',
					text: lang['kopirovat'],
                    icon: 'img/icons/add16.png',
					handler: function() {
						var record = wnd.SearchGrid.getGrid().getSelectionModel().getSelected();
						if (record.get('DrugRequest_id') > 0) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId) {
									if (buttonId == 'yes') {
										Ext.Ajax.request({
											params:{
												DrugRequest_id: record.get('DrugRequest_id')
											},
											success: function (response) {
												var result = Ext.util.JSON.decode(response.responseText);
												if (result.Error_Msg == '')
													sw.swMsg.alert(lang['soobschenie'], lang['sozdana_arhivnaya_kopiya_zayavki']);
											},								
											failure:function () {
												sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_sozdat_arhivnuyu_kopiyu_zayavki']);
											},
											url:'/?c=MzDrugRequest&m=createDrugRequestArchiveCopy'
										});
									}
								},
								icon: Ext.Msg.QUESTION,
								msg: lang['budet_sozdana_arhivnaya_kopiya_zayavki_prodolzhit'],
								title: lang['vnimanie']
							});
						}
					}
				},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', url: '/?c=MzDrugRequest&m=delete'},
				{name: 'action_print'},
				{name: 'action_save', url: '/?c=MzDrugRequest&m=saveDrugRequestPlanParams'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=MzDrugRequest&m=loadMoList',
			height: 180,
			object: 'DrugRequest',
			editformclassname: 'swMzDrugRequestMoAddWindow',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [				
				{name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequest_Version', header: lang['№_versii'], type: 'string', width: 120, hidden: true},
				{name: 'DrugRequestKind_Name', hidden: true},
				{name: 'DrugRequestPeriod_Name', hidden: true},
				{name: 'PersonRegisterType_Name', hidden: true},
				{name: 'Lpu_id', type: 'string', hidden: true},				
				{name: 'Lpu_Name', type: 'string', header: lang['mo'], width: 120, id: 'autoexpand'},				
				{name: 'Filtered', type: 'int', hidden: true},
				{name: 'DrugRequestStatus_id', type: 'int', hidden: true},
				{name: 'DrugRequestStatus_Code', type: 'int', hidden: true},
				{name: 'DrugRequestStatus_Code', type: 'int', hidden: true},
				{name: 'DrugRequestStatus_Name', type: 'string', header: lang['status'], width: 120},
				{name: 'DrugRequestPlan_Kolvo', type: 'string', header: 'Кол-во чел.', editor: new Ext.form.NumberField(editor_field_config), css: editor_field_css, width: 120},
				{name: 'DrugRequestRow_Summa', header: langs('Сумма'), width: 140,
                    renderer: function(v, p, r){
                        var limit = r.get('DrugRequestPlan_Summa');
                        var val = null;

                        if (v > 0 && limit > 0 && v > limit) {
                            val = '<span style="color: red">' + sw.Promed.Format.rurMoney(v) + '</span>';
                        } else {
                            val = sw.Promed.Format.rurMoney(v);
                        }

                        return val;
                    }
                },
                {name: 'DrugRequestPlan_Summa', type: 'money', header: 'Объем фин.', width: 120},
                {name: 'DrugRequestPlan_FedKolvo', type: 'string', header: 'Кол-во чел. (фед.)', editor: new Ext.form.NumberField(editor_field_config), css: editor_field_css, width: 120},
                {name: 'DrugRequestRow_SummaFed', header: langs('Сумма (фед.)'), width: 140,
                    renderer: function(v, p, r){
                        var limit = r.get('DrugRequestPlan_FedSumma');
                        var val = null;

                        if (v > 0 && limit > 0 && v > limit) {
                            val = '<span style="color: red">' + sw.Promed.Format.rurMoney(v) + '</span>';
                        } else {
                            val = sw.Promed.Format.rurMoney(v);
                        }

                        return val;
                    }
                },
                {name: 'DrugRequestPlan_FedSumma', type: 'money', header: 'Объем фин. (фед.)', width: 120},
                {name: 'DrugRequestPlan_RegKolvo', type: 'string', header: 'Кол-во чел. (рег.)', editor: new Ext.form.NumberField(editor_field_config), css: editor_field_css, width: 120},
                {name: 'DrugRequestRow_SummaReg', header: langs('Сумма (рег.)'), width: 140,
                    renderer: function(v, p, r){
                        var limit = r.get('DrugRequestPlan_RegSumma');
                        var val = null;

                        if (v > 0 && limit > 0 && v > limit) {
                            val = '<span style="color: red">' + sw.Promed.Format.rurMoney(v) + '</span>';
                        } else {
                            val = sw.Promed.Format.rurMoney(v);
                        }

                        return val;
                    }
                },
                {name: 'DrugRequestPlan_RegSumma', type: 'money', header: 'Объем фин. (рег.)', width: 120}
			],
			title: null,
			toolbar: true,
			editing: true,
			onRowSelect: function(sm,rowIdx,record) {
                var status_code = record.get('DrugRequestStatus_Code');

				if (record.get('DrugRequest_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);

					if(status_code == 4) { //4 - Нулевая
						this.ViewActions.action_mdrmv_actions.setDisabled(true);
					} else {
						this.ViewActions.action_mdrmv_actions.setDisabled(false);
					}

                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_mp_request', false);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_compare', false);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_editable', (wnd.ARMType == 'touz' && status_code == 6)); //6 - Согласована
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_conformed', status_code != 3); //3 - Утвержденная
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_approved_mz', status_code != 6); //6 - Согласована
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_approved', status_code != 2); //2 - Сформированная
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_remove_approved', false);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_approved_all', false)
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
					this.ViewActions.action_mdrmv_actions.setDisabled(Ext.isEmpty(record.get('DrugRequest_id')) || status_code == 4);

                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_mp_request', Ext.isEmpty(record.get('DrugRequest_id')));
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_compare', true);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_editable', true);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_conformed', true);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_approved_mz', true);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_approved', true);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_remove_approved', true);
                    wnd.setDisabledAction(this, 'action_mdrmv_actions', 'action_mdrmv_set_approved_all', true);
				}
			},
			onDblClick: function() {
				if (!this.ViewActions.action_mdrmv_actions.initialConfig.disabled)
					this.ViewActions.action_mdrmv_actions.initialConfig.menu[0].handler();
			},
			onLoadData: function() {
				this.getGrid().getStore().each(function(item){
					var summ_fed = item.get('FedSumm_Person')*1 + item.get('FedSumm_Reserv')*1;
					var summ_reg = item.get('RegSumm_Person')*1 + item.get('RegSumm_Reserv')*1;
					if (summ_fed > 0) item.set('FedSumm_Total', summ_fed.toFixed(2));
					if (summ_reg > 0) item.set('RegSumm_Total', summ_reg.toFixed(2));
				});
				
				wnd.SearchPanel.doSearch();
			},
			onAfterEditSelf: function(obj) {
                var request_id = obj.record.get('DrugRequest_id');

                if (request_id > 0) {
                    Ext.Ajax.request({
                        params:{
                            DrugRequest_id: request_id,
                            field: obj.field,
                            value: obj.value
                        },
                        success: function () {
                            obj.record.commit();
                        },
                        failure: function () {
                            wnd.SearchPanel.doSearch();
                            sw.swMsg.alert(lang['oshibka'], lang['sohranenie_ne_udalos']);
                        },
                        url:'/?c=MzDrugRequest&m=saveDrugRequestPlanParams'
                    });
                }
			},
			onBeforeEdit: function(o) {
                var status_code = o.record.get('DrugRequestStatus_Code');

				if (this.ViewActions.action_mdrmv_actions.initialConfig.disabled || status_code.inlist([3,7])) { //3 - Утвержденная; 7 - Утвержденная МЗ
					return false;
				}
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
			id: 'mdrmv_DrugGrid',
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
                {name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 150},
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
				id: 'mdrmv_request_list',
				title: lang['spisok_zayavok'],
				layout: 'border',
				border: false,
				items: [
					wnd.SearchPanel,
					wnd.SearchGrid
				]
			}, {
				id: 'mdrmv_drug_list',
				title: lang['medikamentyi_zayavki_v_razreze_mo'],
				layout: 'border',
				border: false,
				items: [
					this.DrugFilterPanel,
					this.DrugGrid
				]
			}],
			listeners: {
				//tabchange: wnd.onDrugRequestTabsChange.createDelegate(wnd)
			}
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function()  {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.InformationPanel,
				wnd.DrugRequestListTabs
			]
		});
		sw.Promed.swMzDrugRequestMoViewWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.SearchPanel.getForm();
	}	
});