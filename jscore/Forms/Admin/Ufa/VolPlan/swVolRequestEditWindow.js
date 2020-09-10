sw.Promed.swVolRequestEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Заявки МО: добавление',
	layout: 'border',
	id: 'swVolRequestEditWindow',
	modal: false,
	onHide: Ext.emptyFn,
	onSelect:  Ext.emptyFn,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: false,
	maximized: true,
    region: 'center',
    VolRequest_id: null,
    SprVidMp_id: null,
    NameVidMp: null,
    PlanYear: null,
    RequestStatusName: null,
    isStat: false,
    isBoss: false,
    isMZ: false,
    arr:[],
    infoData: {},
	listeners:{
        hide:function ()
        {
            this.FilterPanel.getForm().reset();
            this.onHide();
        }
	},
        editRequest: function()
        {
            var wnd = this;
            if (wnd.mode == 'select') 
                        {
                                //wnd.onSelect();
                        } 
                        else if (wnd.mode == 'view') 
                        {
                            var grid = Ext.getCmp('idVolRequestsGrid2').getGrid();
                            var row = grid.getSelectionModel().getSelected();
                            var params = {};
                            params.VolRequest_id = row.data.VolRequest_id;
                            params.VolRequestList_id = row.data.RequestList_id;
                            params.SprVidMp_id = row.data.SprVidMp_id;
                            params.Lpu_id = row.data.Lpu_id;
                            params.mo = row.data.mo;
                            params.Status_id = row.data.status_id;
                            params.StatusName = row.data.status_name;
                            params.NameVidMp = wnd.NameVidMp;
                            params.kp = row.data.kp;
                            params.PlanYear = wnd.PlanYear;
                            if (wnd.isStat == true)
                            {
                                params.functionality = 'stat';
                            }
                            if (wnd.isBoss == true)
                            {
                                params.functionality = 'boss';
                            }
                            if (wnd.isMZ == true)
                            {
                                params.functionality = 'mz';
                            }

//                                    wnd.infoData.idVidMp = record.data.SprVidMp_id;
//                                    wnd.infoData.NameVidMp = wnd.NameVidMp;
//                                    wnd.infoData.StatusName = record.data.Status_Name;
//                                    wnd.infoData.mo = record.data.mo;

                            //console.log('params', params);
                            getWnd('swVolRequestWindow').show(params);
                            //this.ViewActions.action_edit.execute();
                        }
        },
        deleteRequest: function() 
        {
            if (this.isMZ)
            {
                var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет удаление заявки..."});

                var grid = this.Grid.ViewGridPanel,
                    record = grid.getSelectionModel().getSelected();
                if( !record ) return false;

                sw.swMsg.show({
                    title: lang['podtverjdenie_udaleniya'],
                    msg: 'Вы действительно хотите удалить выбранную заявку?',
                    buttons: Ext.Msg.YESNO,
                    fn: function(buttonId) {
                        if (buttonId == 'yes') {
                            loadMask.show();
                            Ext.Ajax.request({
                                url: '/?c=VolPeriods&m=deleteVolRequestList',
                                params: {
                                    RequestList_id: record.get('RequestList_id')
                                },
                                callback: function(o, s, r) {
                                    if(s) {
                                        grid.getStore().remove(record);
                                        loadMask.hide();
                                    }
                                },
                                success: function() {
                                    sw.swMsg.show({
                                        title: 'Сообщение',
                                        msg: 'Заявка удалена',
                                        icon: Ext.Msg.INFO,
                                        buttons: Ext.Msg.OK
                                    });
                                    Ext.getCmp('idVolRequestsGrid2').getGrid().getStore().reload();
                                }
                            });

                        }
                    }
                });
            }
        },
        setRequestStatus: function (status_id, RequestList_id) 
        {
            var msg = '';
            switch (status_id)
            {
                case 3:
                    msg = 'Заявка сформирована';
                break;
                case 4:
                    msg = 'Заявка утверждена';
                break;
            }
            
            var params = {}
            params.SprRequestStatus_id = status_id;
            params.RequestList_id = RequestList_id;
            Ext.Ajax.request(
            {
                failure: function(response, options) 
                {
                    sw.swMsg.alert(lang['oshibka'], 'Ошибка');
                },
                params: params,
                success: function(response, options) 
                {
                    if (msg != '')
                    {
                        sw.swMsg.show(
                        {
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.INFO,
                            msg: msg,
                            title: 'Информация'
                        });
                    }
                },
                url: '/?c=VolPeriods&m=setRequestStatus'
            });
            this.doSearch();
        },
        updateLic: function (RequestList_id) 
        {
            var params = {}
            params.RequestList_id = RequestList_id;
            Ext.Ajax.request(
            {
                failure: function(response, options) 
                {
                    sw.swMsg.alert(lang['oshibka'], 'Ошибка');
                },
                params: params,
                success: function(response, options) 
                {
                    sw.swMsg.show({
                        title: 'Сообщение',
                        msg: 'Флаг "Разрешить планирование" установлен',
                        icon: Ext.Msg.INFO,
                        buttons: Ext.Msg.OK
                    });
                },
                url: '/?c=VolPeriods&m=updateLic'
            });
            this.doSearch();
        },
        printRequest: function(RequestList_id, form)
        { 
            var fileName = '';
            switch (form)
            {
                case 'analit' :
                    switch (this.SprVidMp_id)
                    {
                        // Круглосуточный стационар
                        case 1:
                            fileName = 'vol_MO_analit_KS_2018.rptdesign';
                            break;
                        // Круглосуточный стационар (КСГ/КПГ)
                        case 2:
                            fileName = 'vol_MO_analit_KS_KSG_2018.rptdesign';
                            break;
                        // Дневной стационар
                        case 3:
                            fileName = 'vol_MO_analit_DS_2_1_2018.rptdesign';
                            break;
                        // Дневной стационар (КСГ/КПГ)
                        case 4:
                            fileName = 'vol_MO_analit_DS_KSG_2018.rptdesign';
                            break;
                        // Высокотехнологичная медицинская помощь
                        case 5:
                            fileName = 'vol_MO_analit_VMP_2018.rptdesign';
                            break;
                        // АПУ. Неотложная медицинская помощь
                        case 6:
                            fileName = 'vol_MO_analit_Neotl_3_1_9_2018.rptdesign';
                            break;
                        // Лечебно-диагностические исследования
                        case 7:
                            fileName = 'vol_MO_analit_APU_LDU_3_2_2018.rptdesign';
                            break;
                        // Заместительная почечная терапия
                        case 8:
                            fileName = 'vol_MO_analit_Dializ_5_2018.rptdesign';
                            break;
                        // Экстракорпоральное оплодотворение
                        case 9:
                            fileName = 'vol_MO_analit_EKO_2018.rptdesign';
                            break;
                        // Скорая медицинская помощь
                        case 10:
                            fileName = 'vol_MO_analit_SMP_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения. Консультативные посещения
                        case 11:
                            fileName = 'vol_MO_analit_Prof_Kons_3_1_1_2018.rptdesign';
                            break;
                        // АПУ. Посещения МО, не имеющих прикрепленного населения
                        case 12:
                            fileName = 'vol_MO_analit_Prof_notAttach_3_1_2_2018.rptdesign';
                            break;
                        // АПУ. Обращения по заболеваниям
                        case 13:
                            fileName = 'vol_MO_analit_APU_obrasch_3_1_10_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения. Медосмотры
                        case 14:
                            fileName = 'vol_MO_analit_Prof_Medos_3_1_5_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения. Диспансеризация
                        case 15:
                            fileName = 'vol_MO_analit_Disp_3_1_4_2018.rptdesign';
                            break;
                        // АПУ. Посещения МО, имеющих прикрепленное население
                        case 16:
                            fileName = 'vol_MO_analit_Prof_Podush_3_1_3_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения. ЦЗ и Гериатрия
                        case 17:
                            fileName = 'vol_MO_analit_Prof_CZiG_3_1_6_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения
                        case 18:
                            fileName = 'vol_MO_analit_Prof_all_2018.rptdesign';
                            break;
                        }
                    break;
                case 'federal':
                    switch (this.SprVidMp_id)
                        {
                        // Круглосуточный стационар
                        case 1:
                            fileName = 'vol_MO_fed_KS_1_1_2018.rptdesign';
                            break;
                        // Круглосуточный стационар (КСГ/КПГ)
                        case 2:
                            fileName = 'vol_MO_fed_KS_KSG_1_2_2018.rptdesign';
                            break;
                        // Дневной стационар
                        case 3:
                            fileName = 'vol_MO_fed_DS_2_1_2018.rptdesign';
                            break;
                        // Дневной стационар (КСГ/КПГ)
                        case 4:
                            fileName = 'vol_MO_fed_DS_KSG_2_2_2018.rptdesign';
                            break;
                        // Высокотехнологичная медицинская помощь
                        case 5:
                            fileName = 'vol_MO_fed_VMP_1_4_2018.rptdesign';
                            break;
                        // АПУ. Неотложная медицинская помощь
                        case 6:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        // Лечебно-диагностические исследования
                        case 7:
                            fileName = '';
                            break;
                        // Заместительная почечная терапия
                        case 8:
                            fileName = '';
                            break;
                        // Экстракорпоральное оплодотворение
                        case 9:
                            fileName = '';
                            break;
                        // Скорая медицинская помощь
                        case 10:
                            fileName = 'vol_MO_fed_SMP_4_2_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения. Консультативные посещения
                        case 11:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        // АПУ. Посещения МО, не имеющих прикрепленного населения
                        case 12:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        // АПУ. Обращения по заболеваниям
                        case 13:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения. Медосмотры
                        case 14:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения. Диспансеризация
                        case 15:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        // АПУ. Посещения МО, имеющих прикрепленное население
                        case 16:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения. ЦЗ и Гериатрия
                        case 17:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        // АПУ. Профилактические посещения
                        case 18:
                            fileName = 'vol_MO_fed_APU_2018.rptdesign';
                            break;
                        }
                    break;
                case 'pgg':
                    switch (this.SprVidMp_id)
                    {
                        // АПУ. Профилактические посещения
                        case 18:
                            fileName = 'vol_MO_analit_APU_Profilaktika_PGG_2019.rptdesign';
                            break;
                    }
                    break;
                case 'events':
                    switch (this.SprVidMp_id)
                    {
                        // АПУ. Профилактические посещения
                        case 18:
                            fileName = 'vol_MO_PGG_Prof_3_1_8_2019.rptdesign';
                            break;
                    }
                    break;
            }
            if (fileName === '')
            {
                sw.swMsg.show(
                    {
                        title: 'Внимание',
                        msg: 'Этот вид выгрузки еще не реализован',
                        icon: Ext.Msg.INFO,
                        buttons: Ext.Msg.OK
                    }
                );
            }
            else
            {
                var url = '/?c=ReportRun&m=RunByFileName&Report_FileName=' + fileName + '&Report_Params=%26paramRequestList%3D' + RequestList_id + '&Report_Format=pdf'
                window.open(url, '_blank');
            }
        },
	show: function() 
        {
            Ext.getCmp('filtMO').items.items[0].expand();
           
            var wnd = this;
            wnd.onHide = Ext.emptyFn;
            
            
            
            switch (arguments[0].functionality) 
            {
                case 'stat':
                    wnd.isStat = true;
                    this.Grid.getGrid().getColumnModel().setHidden(this.Grid.getGrid().getColumnModel().findColumnIndex('doControl'), true);
                    this.Grid.getGrid().getColumnModel().setEditable(this.Grid.getGrid().getColumnModel().findColumnIndex('KpAdults'), false);
                    this.Grid.getGrid().getColumnModel().setEditable(this.Grid.getGrid().getColumnModel().findColumnIndex('KpKids'), false);
                    this.Grid.getGrid().getColumnModel().setEditable(this.Grid.getGrid().getColumnModel().findColumnIndex('DevPrc'), false);
                    this.Grid.getGrid().getColumnModel().setEditable(this.Grid.getGrid().getColumnModel().findColumnIndex('Comment'), false);
                    break;
                case 'boss':
                    wnd.isBoss = true;
                    this.Grid.getGrid().getColumnModel().setHidden(this.Grid.getGrid().getColumnModel().findColumnIndex('doControl'), true);
                    this.Grid.getGrid().getColumnModel().setEditable(this.Grid.getGrid().getColumnModel().findColumnIndex('KpAdults'), false);
                    this.Grid.getGrid().getColumnModel().setEditable(this.Grid.getGrid().getColumnModel().findColumnIndex('KpKids'), false);
                    this.Grid.getGrid().getColumnModel().setEditable(this.Grid.getGrid().getColumnModel().findColumnIndex('DevPrc'), false);
                    this.Grid.getGrid().getColumnModel().setEditable(this.Grid.getGrid().getColumnModel().findColumnIndex('Comment'), false);
                    break;
                case 'mz':
                    wnd.isMZ = true;
                    this.Grid.getGrid().getColumnModel().setHidden(this.Grid.getGrid().getColumnModel().findColumnIndex('doControl'), false);
                    break;
            }
            
            
            this.Grid.addActions(
                {
                    name: 'action_drpe_form',
                    iconCls: 'rpt-repo16',
                    text: 'Действия',
                    menu: [
                        {
                            name: 'share',
                            id: 'idShare',
                            iconCls: 'group-global16',
                            text: 'Предоставить доступ к заявке МО',
                            //hidden: !wnd.isMZ,
                            handler: function() 
                                {
                                    wnd.setRequestStatus(2, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                                }
                        }, 
                        {
                            name: 'return',
                            id: 'idReturn',
                            iconCls: 'undo16',
                            text: 'Вернуть заявку на редактирование',
                            //hidden: wnd.isStat,
                            handler: function() 
                            {
                                if (wnd.Grid.getGrid().getSelectionModel().getSelected().data.status_name == 'Сформирована по КП')
                                {
                                    wnd.setRequestStatus(7, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id); 
                                }
                                else
                                {
                                    wnd.setRequestStatus(2, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                                }
                            }
                        },
                        {
                            name: 'confirm',
                            id: 'idConfirm',
                            iconCls: 'ok16',
                            text: 'Утвердить заявку',
                            //hidden: !wnd.isBoss,
                            handler: function() 
                            {
                                wnd.doControl(wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id, 4);
                                //wnd.setRequestStatus(4, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                            }
                        },
                        {
                            name: 'cancel_status',
                            id: 'idCancel_status',
                            iconCls: 'delete16',
                            text: 'Отменить статус «Утверждена»',
                            //hidden: !wnd.isBoss,
                            handler: function() 
                            {
                                wnd.setRequestStatus(3, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                            }
                        },
                        {
                            name: 'check',
                            id: 'idCheck',
                            iconCls: 'checklist_blue16',
                            text: 'Проверить',
                            //hidden: false,
                            handler: function() 
                            {
                                wnd.doControl(wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id, 0);
                            }
                        },
                        {
                            name: 'agree',
                            id: 'idAgree',
                            iconCls: 'doubles-mod16',
                            text: 'Согласовать заявку МО',
                            //hidden: !wnd.isMZ,
                            handler: function() 
                            {
                                wnd.setRequestStatus(5, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                            }
                        },
                        {
                            name: 'decline',
                            id: 'idDecline',
                            iconCls: 'reset16',
                            text: 'Отклонить заявку МО',
                            //hidden: !wnd.isMZ,
                            handler: function() 
                            {
                                wnd.setRequestStatus(6, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                            }
                        },
                        {
                            name: 'need_correction',
                            id: 'idNeed_correction',
                            iconCls: 'glossary16',
                            text: 'Передать для корректировки по КП',
                            //hidden: !wnd.isMZ,
                            handler: function() 
                            {
                                wnd.setRequestStatus(7, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                            }
                        },
                        {
                            name: 'return_for_kp',
                            id: 'idReturn_for_kp',
                            iconCls: 'glossary16',
                            text: 'Вернуть заявку для корректировки КП',
                            //hidden: !wnd.isMZ,
                            handler: function() 
                            {
                                wnd.setRequestStatus(7, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                            }
                        },
                        {
                            name: 'approve_distribution',
                            id: 'idApprove_distribution',
                            iconCls: 'glossary16',
                            text: 'Утвердить распределение по КП',
                            //hidden: !wnd.isMZ,
                            handler: function() 
                            {
                                wnd.setRequestStatus(9, wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                            }
                        },
                        {
                            name: 'update_lic',
                            iconCls: 'action_refresh',
                            text: 'Обновить по лицензиям',
                            hidden: !wnd.isMZ,
                            handler: function() 
                            {
                                wnd.updateLic(wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id);
                            }
                        }, 
                        {
                            name: 'print',
                            iconCls: 'print16',
                            text: 'Печать заявки',
                            menu: [
                                {
                                    name: 'print_fed',
                                    iconCls: 'print16',
                                    text: 'Федеральная форма',
                                    handler: function()
                                    {
                                        wnd.printRequest(wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id, 'federal');
                                    }
                                }, 
                                {
                                    name: 'print_analit',
                                    iconCls: 'print16',
                                    text: 'Аналитическая форма',
                                    handler: function()
                                    {
                                        wnd.printRequest(wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id, 'analit');
                                    }
                                },
                                {
                                    name: 'print_pgg',
                                    iconCls: 'print16',
                                    text: 'По ПГГ',
                                    handler: function()
                                    {
                                        wnd.printRequest(wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id, 'pgg');
                                    }
                                },
                                {
                                    name: 'print_events',
                                    iconCls: 'print16',
                                    text: 'По мероприятиям',
                                    handler: function()
                                    {
                                        wnd.printRequest(wnd.Grid.getGrid().getSelectionModel().getSelected().data.RequestList_id, 'events');
                                    }
                                }
                            ]
                        }]
                });
                
            if (!wnd.isStat)
            {
                //this.Grid.getGrid().getColumnModel().setEditable(8, true);
                if (wnd.isMZ)
                {
                    Ext.getCmp('idVolRequestsGrid2').ViewActions.action_delete.setHidden(false);
                }
            }
            else
            {
                Ext.getCmp('idFilterMO').setValue(getGlobalOptions().lpu_id);
                Ext.getCmp('idFilterMO').setDisabled(true);
                this.Grid.getGrid().getColumnModel().setEditable(8, false);
                Ext.getCmp('idVolRequestsGrid2').ViewActions.action_add.setHidden(true);
                Ext.getCmp('idVolRequestsGrid2').ViewActions.action_delete.setHidden(true);
                this.doLayout();
            }
            
            if (!wnd.isMZ)
            {
                Ext.getCmp('idVolRequestsGrid2').ViewActions.action_add.setHidden(true);
            }
                
            if (arguments[0] && arguments[0].onHide) {
                    wnd.onHide = arguments[0].onHide;
            }
            if (arguments[0] && arguments[0].onSelect) {
                    wnd.onSelect = arguments[0].onSelect;
                    wnd.buttons[0].show();
                    wnd.mode = 'select';			
            } else 
            {
                wnd.onSelect = Ext.emptyFn;
                wnd.buttons[0].hide();
                wnd.mode = 'view';
            }
            if ( arguments[0].Request_id ) {
                    this.VolRequest_id = arguments[0].Request_id;

            }
            if ( arguments[0].idVidMp ) 
            {
                var gridCm = Ext.getCmp('idVolRequestsGrid2').getGrid().getColumnModel();
                this.SprVidMp_id = arguments[0].idVidMp;
                
                if (!(this.SprVidMp_id == 2) && !(this.SprVidMp_id == 4))
                {
                    gridCm.setHidden(gridCm.findColumnIndex('KfLimit'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('KfPlan'), true);
                }
                
                /////////////////////////////////////////////////////////////////////
                ///////// https://redmine.swan.perm.ru/issues/144483 ////////////////
                /////////////////////////////////////////////////////////////////////
                if (   this.SprVidMp_id == 3 
                    || this.SprVidMp_id == 4 
                    || this.SprVidMp_id == 18) // https://redmine.swan.perm.ru/issues/146080
                {
                    gridCm.setHidden(gridCm.findColumnIndex('Kp'), true);
                    gridCm.setHidden(gridCm.findColumnIndex('KpKids'), true);
                    gridCm.setColumnHeader(gridCm.findColumnIndex('KpAdults'), 'КП');
                }
                /////////////////////////////////////////////////////////////////////

                var fields = [
                    'DispNabKP',
                    'RazObrCountKP',
                    'MidMedStaffKP',
                    'OtherPurpKP'
                ];

                var hidden = this.SprVidMp_id != 18;

                fields.forEach(
                    function (item, i, fields)
                    {
                        var idx = gridCm.findColumnIndex(item);

                        if (idx >= 0) {
                            gridCm.setHidden(idx, hidden);
                        }
                    }
                );
            }

            if ( arguments[0].PlanYear )
            {
                this.PlanYear = arguments[0].PlanYear;
            }
            if ( arguments[0].NameVidMp ) {
                this.NameVidMp = arguments[0].NameVidMp;
            }
            if ( arguments[0].RequestStatusName ) {
                this.RequestStatusName = arguments[0].RequestStatusName;
            }
            
            Ext.Ajax.request({ 
                    url: '/?c=VolPeriods&m=getLpuList', 
                    params: { 
                        Request_id: wnd.VolRequest_id
                    },

                    success: function(result){
                        var resp_obj = Ext.util.JSON.decode(result.responseText); 

                        var ind = 0;
                        var indEnd = resp_obj.length;
                        //var arr=[];
                        while (ind<indEnd) {                    
                            wnd.arr.push(resp_obj[ind].Lpu_id)

                            ind++;
                        };
                    }
                });
            
            
            wnd.setTitle(arguments[0].openMode == 'view' ? 'Заявки МО: Просмотр' : 'Заявки МО'/*+ this.NameVidMp*/ +': Редактирование');

            Ext.getCmp('idVolRequestsGrid2').getGrid().getColumnModel().setColumnHeader(11,('Заявка ' + this.PlanYear) );
            
            Ext.getCmp('labelInfoVidMp').setText( 'Вид помощи:   ' + arguments[0].NameVidMp );
            Ext.getCmp('labelInfoRequestStatus').setText( 'Статус заявки:   ' + arguments[0].RequestStatusName);
            Ext.getCmp('swVolRequestEditWindow').doSearch();
            sw.Promed.swVolRequestEditWindow.superclass.show.apply(this, arguments);    
	},
        doControl: function(RequestList_id, param)
        {
            var wnd = this;
            Ext.Ajax.request({
                failure:function () {
                    sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                },
                params:{
                    RequestList_id: RequestList_id
                },
                success: function (response) {
                    var result = Ext.util.JSON.decode(response.responseText);
                    
                    if (result[0].rslt != '')
                    {
                        var params = {};
                        params.text = result[0].rslt;
                        getWnd('swVolWarningWnd').show(params);
                    }
                    else
                    {
                        if (param !=0)
                        {
                            wnd.setRequestStatus(param, RequestList_id);
                        }
                        
                        sw.swMsg.show(
                        {
                            buttons: Ext.Msg.OK,
                            icon: Ext.Msg.INFO,
                            msg: 'Заявка проверена. <br> Ошибки не выявлены.',
                            title: 'Информация'
                        });
                    }
                },
                url:'/?c=VolPeriods&m=doControl'
            });
        },
        doSearch: function(){
                var mo_name = Ext.getCmp('idFilterMO').getValue();
                var mo_lvl = Ext.getCmp('idFilterLevelMO').getValue();
                var request_status = Ext.getCmp('idFilterRequestStatus').getValue();
		var params_ = {};
		//params.year = Ext.getCmp('idYear').getEl().getValue();
                params_.VolRequest_id = this.VolRequest_id;
                params_.start= 0;
                params_.limit= 100;
                
                if (mo_name.length > 0 || mo_name > 0) {
                    params_.mo_name = mo_name;
                }
                else
                {
                    delete params_.mo_name;
                }
                if (request_status.length > 0) {
                    params_.request_status = request_status;
                }
                else
                {
                    delete params_.request_status;
                }
                if (!this.isMZ)
                {
                    params_.mo_name = getGlobalOptions().lpu_id;
                    params_.request_status = '_0';
                    params_.mz = 0;
                }
                else
                {
                    params_.mz = 1;
                }
                
                if (mo_lvl.length > 0) {
                    params_.mo_lvl = mo_lvl;
                }
                else
                {
                    delete params_.mo_lvl;
                }
                
		this.Grid.removeAll();
                this.Grid.getGrid().getStore().baseParams = {};
		this.Grid.loadData({
                        globalFilters: params_/*,
                        callback: function() {console.log('idVolRequestsGrid2',Ext.getCmp('idVolRequestsGrid2').ViewGridPanel.getStore())*/
                    
            });
                
                //console.log(this.Grid.getGrid().getStore());
                params_ = null;
            },
        deleteVolRequestList: function() {
//                var grid = Ext.getCmp('idVolRequestsGrid2').getGrid();
//                var row = grid.getSelectionModel().getSelected();
//                var requestlist_id = row.get('RequestList_id');
//                var request_id = row.get('VolRequest_id');
//                sw.swMsg.show({
//                    title: lang['podtverjdenie_udaleniya'],
//                    msg: 'Вы действительно хотите удалить выбранную заявку?',
//                    buttons: Ext.Msg.YESNO,
//                    fn: function(buttonId) {
//                        if (buttonId == 'yes') {
//                            Ext.Ajax.request({
//                                url: '/?c=VolPeriods&m=deleteVolRequestList',
//                                params: {
//                                    Request_id: request_id,
//                                    RequestList_id: requestlist_id
//                                },
//                                callback: function(o, s, r) {
//                                    if(s) {
//                                            // Поскольку релоадить негут, так как данные из лдапа читаются, то просто удаляем запись
//                                            grid.getStore().remove(row);
//                                    }
//                                }
//                            });
//                        }
//                    }
//		});
        },
        doReset: function(){
                this.FilterPanel.getForm().reset();
                this.doSearch();
            },
            
		
	
        buildRequest: function() {
            var plan_year = Ext.getCmp('idYear').getEl().getValue();
            var vid_mp_id = Ext.getCmp('idVidMP2').getValue();
            var vol_period_id = Ext.getCmp('idUseFacts').getValue();
            var max_prc = Ext.getCmp('max_prc').getEl().getValue();
            
            Ext.Ajax.request({
                    url: '/?c=VolPeriods&m=buildRequest',
                    params: {
                            VolPeriod_id: vol_period_id,
                            Year: plan_year,
                            VidMp: vid_mp_id,
                            Prc: max_prc
                    },
                    success: function (response) {
                            //this.doSearch();
                            Ext.getCmp('swVolRequestEditWindow').doSearch();
                            sw.swMsg.show( {
                                    buttons: Ext.Msg.OK,
                                    icon: Ext.Msg.INFO,
                                    msg: 'Заявка сформирована',
                                    title: 'Сообщение'
                            });
                    }
            });

            //this.submit();
            return true;		
        },
        reloadGrid: function() {
            this.Grid.loadData();
        },
	initComponent: function() {
            var wnd = this;

            this.InfoPanel = new Ext.Panel({
                autoScroll: true,
                bodyBorder: false,
                id: 'idInfoPanel',
                bodyStyle: 'padding: 0',
                border: true,
                frame: true,
                region: 'north',
                layout: 'form',
                autoHeight: true,
                labelAlign: 'right',
                items: [
                    { 
                        xtype: 'label', 
                        text: '[eq1',
                        name: 'vidmp', 
                        id: 'labelInfoVidMp', 
                        style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;',
                    },
                    {
                        fieldLabel: 'Год планирования',
                        id: 'idKostil',
                        name: 'plan_year',
                        width: 40,
                        xtype: 'textfield',
                        plugins: [new Ext.ux.InputTextMask('9999',false)],
                        value: (new Date().getFullYear() + 1),
                        hidden: true,
                        hideLabel: true
                    },
                    { 
                        xtype: 'label', 
                        text: '[eq2', 
                        name: 'status', 
                        id: 'labelInfoRequestStatus', 
                        style: 'font-size: 12px;font-weight:bolder;margin: 2px 2px 2px 2px;',
                    },

                ]
            });

            this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
                owner: wnd,
                labelWidth: 120,
                frame: false,
                collapsible: true,
                id: 'filtMO',
                //bodyStyle:'background:#ff5555;',
                filter: {
                    title: 'Фильтры',
                    layout: 'column',
                    items: [
                    {
                        layout: 'form',
                        items: [
                        {
                            layout: 'form',
                            labelWidth: 100,
                            //bodyStyle:'background:#ffffff;',
                            items: [
                                {
                                                    anchor : "95%",
                                                    editable : true,
                                                    //editable: !(getGlobalOptions().TOUZLpuArr && getGlobalOptions().TOUZLpuArr.length > 0 && !isSuperAdmin() && getGlobalOptions().isMinZdrav),
                                                    ctxSerach: true,
                                                    allowBlank: true,
                                                    forceSelection: true,
                                                    hiddenName : 'Lpu_id',
                                                    displayField: 'Lpu_Nick',
                                                    valueField: 'Lpu_id',
                                                    fieldLabel: lang['mo'],
                                                    id : 'idFilterMO',
                                                    lastQuery : '',
                                                    listeners: {
                                                            'blur': function(combo) {
                                                                    if ( combo.getStore().findBy(
                                                                        function(rec) 
                                                                        { 
                                                                            return (rec.get(combo.displayField) == combo.getRawValue()) 
                                                                        }
                                                                                ) < 0  ) 
                                                                    {
                                                                        combo.clearValue();
                                                                        var p = {text:'&nbsp;'};
//                                                                        wnd.TextTpl.overwrite(wnd.TextPanel.body, p);
//                                                                        wnd.TextPanel.render();
                                                                    }
                                                            },
                                                            'select': function(combo, record, index) {
                                                                    var p = {text:'&nbsp;'};
                                                                    if(record) {
                                                                            if ( record.get('Lpu_EndDate') && record.get('Lpu_EndDate') != '' ) {
                                                                                    p.text = '<span style="color: red;">МО закрыто '+record.get('Lpu_EndDate')+'</span>';
                                                                            } else {
                                                                                    p.text = record.get('Lpu_Name');
                                                                            }
                                                                            /*if (isSuperAdmin()) {
                                                                                    p.text = p.text + ' [ id: '+record.get('Lpu_id')+' ]';
                                                                            }*/
                                                                    }
//                                                                    wnd.TextTpl.overwrite(wnd.TextPanel.body, p);
//                                                                    wnd.TextPanel.render();

                                                            },
                                                            'keydown': function (inp, e) {
                                                                    inp.getStore().filterBy(function(rec) {
                                                                            return (rec.get('Lpu_id').toString().inlist(wnd.arr));
                                                                    });
                                                                    if (e.shiftKey == false && e.getKey() == Ext.EventObject.ENTER)
                                                                    {
                                                                            inp.fireEvent("blur", inp);
                                                                            e.stopEvent();
                                                                            //this.submit();
                                                                    }
                                                            }.createDelegate(this),
                                                            'keypress': function (inp, e) {
                                                                    inp.getStore().filterBy(function(rec) {
                                                                            return (rec.get('Lpu_id').toString().inlist(wnd.arr));
                                                                    });
                                                                    
                                                            }.createDelegate(this),
                                                            'keyup': function (inp, e) {
                                                                    inp.getStore().filterBy(function(rec) {
                                                                            return (rec.get('Lpu_id').toString().inlist(form.arr));
                                                                    });
                                                                    
                                                            }.createDelegate(this)
                                                    },
                                                    listWidth : 500,
                                                    tpl: new Ext.XTemplate(
                                                            '<tpl for="."><div class="x-combo-list-item">',
                                                            '{[(values.Lpu_EndDate && values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыто "+ values.Lpu_EndDate /* Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate.slice(0,10), "Y-m-d"), "d.m.Y")"*/ + ")" : values.Lpu_Nick ]}&nbsp;',
                                                            '</div></tpl>'
                                                    ),
                                                    width : 420,
                                                    xtype : 'swlpulocalcombo'
                                            }
                                /*
                            {
                                xtype : 'swcombo',
                                id : 'idFilterMO',
                                mode: 'local',
                                typeCode: 'string',
                                orderBy: 'Lpu_Nick',
                                resizable: false,
                                editable: true,
                                allowBlank: true,
                                displayField: 'Lpu_Nick',
                                valueField: 'Lpu_id',
                                triggerAction: 'all', 
                                store: new Ext.data.Store({
                                    autoLoad: true,
                                    reader: new Ext.data.JsonReader({
                                        id: 'VolPeriod_id'
                                        },[
                                            { name: 'Lpu_id', mapping: 'Lpu_id' },
                                            { name: 'Lpu_Nick', mapping: 'Lpu_Nick' }
                                          ]),
                                        url:'/?c=VolPeriods&m=loadLpuList'
                                    }),
                                width : 450,
                                fieldLabel: 'МО'
                            }*/]
                        },
                        {
                            layout: 'form',
                            labelWidth: 100,
                            items: [
                            {
                                xtype : 'swcombo',
                                id : 'idFilterLevelMO',
                                mode: 'local',
                                typeCode: 'string',
                                orderBy: 'LpuLevel_id',
                                resizable: false,
                                editable: false,
                                allowBlank: true,
                                displayField: 'LevelType_Name',
                                valueField: 'LevelType_id',
                                triggerAction: 'all', 
                                store: new Ext.data.Store({
                                autoLoad: true,
                                reader: new Ext.data.JsonReader({
                                        id: 'VolPeriod_id'
                                        },[
                                            { name: 'LevelType_id', mapping: 'LevelType_id' },
                                            { name: 'LevelType_Name', mapping: 'LevelType_Name' }
                                          ]),
                                        url:'/?c=VolPeriods&m=loadLpuLevelList'
                                        }),
                                width : 250,
                                fieldLabel: 'Уровень МО'
                            }]
                        },
                        {
                            layout: 'form',
                            labelWidth: 100,
                            items: [
                            {
                                xtype : 'swcombo',
                                id : 'idFilterRequestStatus',
                                mode: 'local',
                                typeCode: 'string',
                                orderBy: 'SprRequestStatus_id',
                                resizable: false,
                                editable: false,
                                allowBlank: true,
                                displayField: 'SprRequestStatus_Name',
                                valueField: 'SprRequestStatus_id',
                                triggerAction: 'all', 
                                store: new Ext.data.Store({
                                autoLoad: true,
                                reader: new Ext.data.JsonReader({
                                    id: 'VolPeriod_id'
                                    },[
                                        { name: 'SprRequestStatus_id', mapping: 'SprRequestStatus_id' },
                                        { name: 'SprRequestStatus_Name', mapping: 'SprRequestStatus_Name' }
                                      ]),
                                    url:'/?c=VolPeriods&m=loadRequestStatusList'
                                    }),
                                width : 250,
                                fieldLabel: 'Статус заявки'
                            }]
                        },
                        {
                            layout: 'column',
                            items: [
                            {
                                border: false,
                                layout: 'form',
                                items: [
                                {
                                    style: "padding-left: 20px",
                                    xtype: 'button',
                                    id: wnd.id + 'BtnSearch',
                                    text: lang['nayti'],
                                    iconCls: 'search16',
                                    handler: function() 
                                    {
                                        wnd.doSearch();
                                    }.createDelegate(wnd)
                                }]
                            },
                            {
                                border: false,
                                layout: 'form',
                                items: [
                                {
                                    style: "padding-left: 10px",
                                    xtype: 'button',
                                    id: wnd.id + 'BtnClear',
                                    text: lang['sbros'],
                                    iconCls: 'reset16',
                                    handler: function() 
                                    {
                                        wnd.doReset();
                                    }.createDelegate(wnd)
                                }]
                            }]}
                        ]
                    }]
                }
            });

            this.Grid = new sw.Promed.ViewFrame(
            {
                layout: 'fit',
                actions: [
                    {name: 'action_add', handler: function() 
                        {
                            var params = {};
                            params.VolRequest_id = Ext.getCmp('swVolRequestEditWindow').VolRequest_id;
                            //console.log('111',params.VolRequest_id);
                            getWnd('swAddLpuWindow').show(params);
                        }
                    },
                    {name: 'action_edit', hidden: true, handler: function()
                        {
                            wnd.editRequest();
                        }
                    },
                    {name: 'action_view', handler: function()
                        {
                            wnd.editRequest();
                        }
                    },
                    {name: 'action_delete', handler: function()
                        {
                            wnd.deleteRequest();
                        }
                    },
                    {name: 'action_print', disabled: false,  hidden: false},
                    {name: 'action_save', hidden: true}
                ],
                autoExpandColumn: 'autoexpand',
                autoExpandMin: 150,
                autoLoadData: false,                        
                //autoHeight: true,
                border: true,
                dataUrl: '/?c=VolPeriods&m=loadVolRequestList',
                height: 500,
                region: 'center',
                object: 'requestlist',
                //editformclassname: 'swVolRequestWindow',
                id: 'idVolRequestsGrid2',
                paging: false,
                pageSize: 2,
                style: 'margin-bottom: 10px',
                //root: 'data',
                stringfields: [
                    {name: 'SprVidMp_id', type: 'int', header: 'Ид вида МП', width: 250, hidden: true},
                    {name: 'VolRequest_id', type: 'int', header: 'ид заявки'},
                    {name: 'RequestList_id', type: 'int', header: 'ид записи лпу в заявке', width: 250, hidden: true, key: true},
                    {name: 'Lpu_id', type: 'int', header: 'Ид ЛПУ', width: 250, hidden: true},
                    {name: 'mo', type: 'string', header: 'МО', width: 250, renderer: function(v, p, record) {return record.get('mo');}},
                    {name: 'mo_lvl', header: 'Уровень МО', type: 'string', width: 80},
                    {name: 'doControl', type: 'checkcolumnedit', header: 'Проводить <br>контроль', width: 75},
                    //{name: 'status_id', type: 'int', header: 'Ид статуса заявки', width: 250, hidden: true},
                    {name: 'status_name', type: 'string', header: 'Статус', width: 130},
                    {name: 'KfLimit', type: 'float', header: 'Предельный коэффициент', width: 160, editor: new Ext.form.NumberField()},
                    {name: 'KfPlan', type: 'float', header: 'Планируемый коэффициент', width: 160},
                    {name: 'DevPrc', type: 'float', header: 'Процент отклонения', width: 160, editor: new Ext.form.NumberField(), hidden: false},
                    {name: 'VolCount', type: 'int', header: 'Объем, ед.(План)', width: 100},
                    {name: 'Kp', type: 'float', header: 'КП', width: 100},
                    {name: 'KpAdults', type: 'float', header: 'в т.ч взрослые', width: 100, editor: new Ext.form.NumberField()},
                    {name: 'KpKids', type: 'float', header: 'в.т.ч дети', width: 100, editor: new Ext.form.NumberField() },
                    { name: 'DispNabKP', type: 'int', header: 'Для проведения<br>диспансерного<br>наблюдения', width: 100, editor: new Ext.form.NumberField() },
                    { name: 'RazObrCountKP', type: 'int', header: 'Для разовых<br>посещений<br>в связи с<br>заболеваниями', width: 100, editor: new Ext.form.NumberField() },
                    { name: 'MidMedStaffKP', type: 'int', header: 'Для посещений<br>среднего МП', width: 100, editor: new Ext.form.NumberField() },
                    { name: 'OtherPurpKP', type: 'int', header: 'Для посещений<br>с<br>другими целями', width: 100, editor: new Ext.form.NumberField() },
                    {name: 'Comment', type: 'string', header: 'Комментарий', width: 250, editor: new Ext.form.TextField(), hidden: false},
                    {name: 'PacCount', type: 'int', header: 'Численность населения на 01.04', width: 200 }
                ],
                title: 'Список заявок',
                toolbar: true,
                saveAtOnce: false,
                onAfterEdit: function(o)
                {
                    var params = {};
                    params.RequestList_id = o.record.data.RequestList_id;
                    params[o.field] = o.value;
                    params[o.field + '_o'] = o.originalValue;
                    Ext.Ajax.request(
                    {
                        failure: function(response, options) 
                        {
                            sw.swMsg.alert(lang['oshibka'], 'Возникли проблемы при сохранении записи');
                        },
                        params: params,
                        success: function(response, options)
                        {
                            Ext.getCmp('idVolRequestsGrid2').getGrid().getStore().reload();
                        },
                        url: '/?c=VolPeriods&m=saveRequestList'
                    });
                },
                onRowSelect: function(sm, index, record) 
                {
                    var grid = Ext.getCmp('idVolRequestsGrid2');
                    
                    wnd.infoData.idVidMp = record.data.SprVidMp_id;
                    wnd.infoData.NameVidMp = wnd.NameVidMp;
                    wnd.infoData.Status_id = record.data.status_id;
                    wnd.infoData.StatusName = record.data.status_name;
                    wnd.infoData.mo = record.data.mo;
                    wnd.infoData.Comment = record.data.Comment;
                    
                    //if (wnd.isMZ)
                    //{
                        if ((record.data.status_name == 'Новая' || record.data.status_name == 'В работе') && record.data.VolCount == 0)
                        {
                            wnd.Grid.ViewActions.action_delete.setDisabled(false);
                        }
                        else
                        {
                            wnd.Grid.ViewActions.action_delete.setDisabled(true);
                        }
                        
                        var act_share = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[0];
                        var act_return = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[1];
                        var act_confirm = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[2];
                        var act_unconfirm = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[3];
                        var act_check = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[4];
                        var act_approve = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[5];
                        var act_decline = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[6];
                        var act_sendforkp = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[7];
                        var act_returnforkp = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[8];
                        var act_confirmkp = wnd.Grid.ViewActions.action_drpe_form.items[0].menu.items.items[9];
                        
                        var act_share_ctx = Ext.getCmp('idShare');
                        var act_return_ctx = Ext.getCmp('idReturn');
                        var act_confirm_ctx = Ext.getCmp('idConfirm');
                        var act_unconfirm_ctx = Ext.getCmp('idCancel_status');
                        var act_approve_ctx = Ext.getCmp('idAgree');
                        var act_decline_ctx = Ext.getCmp('idDecline');
                        var act_sendforkp_ctx = Ext.getCmp('idNeed_correction');
                        var act_returnforkp_ctx = Ext.getCmp('idReturn_for_kp');
                        var act_confirmkp_ctx = Ext.getCmp('idApprove_distribution');
                        
                        var act_share_visible = false;
                        var act_return_visible = false;
                        var act_confirm_visible = false;
                        var act_unconfirm_visible = false;
                        var act_approve_visible = false;
                        var act_decline_visible = false;
                        var act_sendforkp_visible = false;
                        var act_returnforkp_visible = false;
                        var act_confirmkp_visible = false;
                        
                        switch (record.data.status_name)
                        {
                            case 'Новая' :
                                act_share_visible = wnd.isMZ;
                            break;
                            case 'Сформирована' :
                                act_return_visible = wnd.isMZ || wnd.isBoss;
                            break;
                            case 'Утверждена' :
                                act_return_visible = wnd.isMZ || wnd.isBoss;
                                act_approve_visible = wnd.isMZ;
                                act_decline_visible = wnd.isMZ;
                            break;
                            case 'Согласована' :
                                act_sendforkp_visible = wnd.isMZ;
                            break;
                            case 'Сформирована по КП' :
                                act_return_visible = wnd.isMZ || wnd.isBoss;
                                act_returnforkp_visible = wnd.isMZ;
                                act_confirmkp_visible = wnd.isMZ;
                            break;
                            case 'Утверждена по КП' :
                                act_returnforkp_visible = wnd.isMZ;
                            break;
                        }
                        act_share.setVisible(act_share_visible);
                        act_return.setVisible(act_return_visible);
                        act_confirm.setVisible(act_confirm_visible);
                        act_unconfirm.setVisible(act_unconfirm_visible);
                        act_approve.setVisible(act_approve_visible);
                        act_decline.setVisible(act_decline_visible);
                        act_sendforkp.setVisible(act_sendforkp_visible);
                        act_returnforkp.setVisible(act_returnforkp_visible);
                        act_confirmkp.setVisible(act_confirmkp_visible);
                        
                        act_share_ctx.setVisible(act_share_visible);
                        act_return_ctx.setVisible(act_return_visible);
                        act_confirm_ctx.setVisible(act_confirm_visible);
                        act_unconfirm_ctx.setVisible(act_unconfirm_visible);
                        act_approve_ctx.setVisible(act_approve_visible);
                        act_decline_ctx.setVisible(act_decline_visible);
                        act_sendforkp_ctx.setVisible(act_sendforkp_visible);
                        act_returnforkp_ctx.setVisible(act_returnforkp_visible);
                        act_confirmkp_ctx.setVisible(act_confirmkp_visible);
                    //}
                    wnd.doLayout();
                },
                onDblClick: function() 
                {
                    wnd.editRequest();
                    /*
                    if (wnd.mode == 'select') 
                    {
                            //wnd.onSelect();
                    } 
                    else if (wnd.mode == 'view') 
                    {
                        var grid = Ext.getCmp('idVolRequestsGrid2').getGrid();
                        var row = grid.getSelectionModel().getSelected();
                        var params = {};
                        params.VolRequest_id = row.data.VolRequest_id;
                        params.VolRequestList_id = row.data.RequestList_id;
                        params.SprVidMp_id = row.data.SprVidMp_id;
                        params.Lpu_id = row.data.Lpu_id;
                        params.mo = row.data.mo;
                        params.StatusName = row.data.status_name;
                        params.NameVidMp = wnd.NameVidMp;
                        params.kp = row.data.kp;
                        params.PlanYear = wnd.PlanYear;
                        if (wnd.isStat == true)
                        {
                            params.functionality = 'stat';
                        }
                        if (wnd.isBoss == true)
                        {
                            params.functionality = 'boss';
                        }
                        if (wnd.isMZ == true)
                        {
                            params.functionality = 'mz';
                        }

//                                    wnd.infoData.idVidMp = record.data.SprVidMp_id;
//                                    wnd.infoData.NameVidMp = wnd.NameVidMp;
//                                    wnd.infoData.StatusName = record.data.Status_Name;
//                                    wnd.infoData.mo = record.data.mo;

                        //console.log('params', params);
                        getWnd('swVolRequestWindow').show(params);
                        //this.ViewActions.action_edit.execute();
                    }
                    */
                }
            });

            this.Inputs = new Ext.Panel({
                    autoScroll: true,
                    bodyBorder: false,
                    id: 'idPanel',
                    bodyStyle: 'padding: 0',
                    border: true,
                    frame: true,
                    region: 'north',
                    //height: 100,
                    autoHeight: true,
                    labelAlign: 'right',
                    items: [
                            wnd.FilterPanel,
                            {
                            xtype: 'form',
                            id: 'drpeVolRequestEditForm',
                            style: '',
                            bodyStyle:'background:#DFE8F6;padding:4px;',
                            border: true,
                            labelWidth: 170,
                            collapsible: true,
                            url:'/?c=VolPeriods&m=saveVolPeriod',
                            items:[{
                                    name: 'VolPeriod_id',
                                    xtype: 'hidden',
                                    value: 0
                                    }, 
                                    {
                                        fieldLabel: 'Год планирования',
                                        id: 'idYear',
                                        name: 'plan_year',
                                        width: 40,
                                        xtype: 'textfield',
                                        plugins: [new Ext.ux.InputTextMask('9999',false)],
                                        value: (new Date().getFullYear() + 1)
                                },
                                {
                                    xtype : 'swcombo',
                                    id : 'idUseFacts',
                                    mode: 'local',
                                    typeCode: 'string',
                                    orderBy: 'VolPeriod_id',
                                    resizable: true,
                                    editable: false,
                                    allowBlank: false,
                                    displayField: 'VolPeriod_Name',
                                    valueField: 'VolPeriod_id',
                                    triggerAction: 'all', 
                                    store: new Ext.data.Store({
                                    autoLoad: true,
                                    reader: new Ext.data.JsonReader({
                                            id: 'VolPeriod_id'
                                        },[
                                        { name: 'VolPeriod_id', mapping: 'VolPeriod_id' },
                                        { name: 'VolPeriod_Name', mapping: 'VolPeriod_Name' }
                                    ]),
                                url:'/?c=VolPeriods&m=loadVolPeriodList'
                                    }),
                                    width : 470,
                                    fieldLabel: 'Использовать фактически выполненные объемы за периоды'
                                },
                                {
                                    xtype : 'swcombo',
                                    id : 'idVidMP2',
                                    mode: 'local',
                                    typeCode: 'string',
                                    orderBy: 'SprVidMp_id',
                                    resizable: true,
                                    editable: false,
                                    allowBlank: false,
                                    displayField: 'SprVidMp_Name',
                                    valueField: 'SprVidMp_id',
                                    triggerAction: 'all', 
                                    store: new Ext.data.Store({
                                        autoLoad: true,
                                        reader: new Ext.data.JsonReader({
                                                id: 'SprVidMp_id'
                                            },[
                                                { name: 'SprVidMp_id', mapping: 'SprVidMp_id' },
                                                { name: 'SprVidMp_Name', mapping: 'SprVidMp_Name' }
                                            ]),
                                        url:'/?c=VolPeriods&m=loadVidMPList'
                                    }),
                                    width : 470,
                                    fieldLabel: 'Вид МП'
                                },
                                {
                                        fieldLabel: 'Максимальный % отклонения',
                                        id: 'max_prc',
                                        name: 'percent',
                                        width: 40,
                                        xtype: 'textfield',
                                        //plugins: [new Ext.ux.InputTextMask('9999',false)],
                                        value: 0
                                }
                            ]
                    }//,
                //wnd.Grid

            ]
            });


            this.BottomPanel = new Ext.Panel ({
                bodyStyle: 'padding:2px',
                    layout: 'fit',
                    border: true,
                    frame: false,                      
                    resizeable: true,
                    //autoHeight: true,
                    region: 'center',
                    //maxSize: 30,
                    items: [this.Grid
                            ]
                });

            Ext.apply(this, {
                    //layout: 'border',
                    buttons: [
//                               {
//                                handler: function() {
//                                    
//                                    }.createDelegate(this),
//                                iconCls: 'ok16',
//                                text: 'Сформировать'
//                                },
//                                {
//                                handler: function() {
//                                    this.ownerCt.buildRequest();
//                                    },
//                                iconCls: 'ok16',
//                                text: 'Сформировать'
//                                },
				{
					text: '-'
				},
                            HelpButton(this, 1),
                            {
                                    handler: function () {
                                        var win = Ext.getCmp('swVolRequestEditWindow');
                                        win.hide();
                                    }.createDelegate(this),
                                    iconCls: 'close16',
                                    text: BTN_FRMCLOSE
                            }],
                        items:[//wnd.Inputs,
                                new Ext.Panel({
                                    autoScroll: true,
                                    bodyBorder: false,
                                    id: 'idPanel',
                                    bodyStyle: 'padding: 0',
                                    border: true,
                                    frame: true,
                                    region: 'north',
                                    //height: 200,
                                    autoHeight: true,
                                    labelAlign: 'right',
                                    items: [wnd.InfoPanel,
                                            wnd.FilterPanel]}),
                                //wnd.FilterPanel,
                               wnd.Grid
                    ]
            });
            sw.Promed.swVolRequestEditWindow.superclass.initComponent.apply(this, arguments);
	}	
});