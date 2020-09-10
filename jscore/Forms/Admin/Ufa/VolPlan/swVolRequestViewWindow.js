sw.Promed.swVolRequestViewWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Заявки МО',
    layout: 'border',
    id: 'swVolRequestViewWindow',
    modal: false,
    onHide: Ext.emptyFn,
    onSelect:  Ext.emptyFn,
    shim: false,
    width: 550,
    height: 130,
    resizable: false,
    maximizable: false,
    maximized: true,
    region: 'center',
    isStat: false,
    isBoss: false,
    isMZ: false,
    params: {},
    listeners:{
        hide:function ()
        {
            this.onHide();
        }
    },
    show: function() 
    {		
        sw.Promed.swVolRequestViewWindow.superclass.show.apply(this, arguments);
        var wnd = this;
        
        
        switch (arguments[0].functionality) {
            case 'stat':
                wnd.isStat = true;
                break;
            case 'boss':
                wnd.isBoss = true;
                break;
            case 'mz':
                wnd.isMZ = true;
                break;
        }
        
        
        
        this.Grid.getGrid().getColumnModel().setHidden(3, !wnd.isMZ);
        this.Grid.getGrid().getColumnModel().setHidden(4, !wnd.isMZ);
        this.Grid.getGrid().getColumnModel().setHidden(5, !wnd.isMZ);
        this.Grid.getGrid().getColumnModel().setColumnHeader(4,'Заявка ' + (new Date().getFullYear() + 1) );
        
        
        if (wnd.isMZ == true)
        {
            this.Grid.addActions(
                {
                    name: 'action_print_form',
                    iconCls: 'print16',
                    text: 'Печать',
                    menu: [
                        {
                            name: 'print_analit',
                            iconCls: 'print16',
                            text: 'Печать аналитической формы',
                            menu: [
                                {
                                    name: 'print_disp',
                                    iconCls: 'print16',
                                    text: 'Диспансеризация',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'analit', 'disp');
                                    }
                                },
                                {
                                    name: 'print_cons',
                                    iconCls: 'print16',
                                    text: 'Консультативные посещения',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'analit', 'cons');
                                    }
                                },
                                {
                                    name: 'print_notAttach',
                                    iconCls: 'print16',
                                    text: 'Посещения МО, не имеющих прикрепленное населения',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'analit', 'notAttach');
                                    }
                                },
                                {
                                    name: 'print_podush',
                                    iconCls: 'print16',
                                    text: 'Посещения МО, имеющих прикрепленное население',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'analit', 'podush');
                                    }
                                },
                                {
                                    name: 'print_medos',
                                    iconCls: 'print16',
                                    text: 'Медосмотры',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'analit', 'medos');
                                    }
                                },
                                {
                                    name: 'print_cz',
                                    iconCls: 'print16',
                                    text: 'ЦЗ и Гериатрия',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'analit', 'cz');
                                    }
                                }
                            ],
                            handler: function() 
                            {
                                if (wnd.params.idVidMp !== 18)
                                {
                                    wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'analit');
                                }
                            }
                        }, 
                        {
                            name: 'print_federal',
                            iconCls: 'print16',
                            text: 'Печать федеральной формы',
                            menu: [
                                {
                                    name: 'print_fed_1',
                                    iconCls: 'print16',
                                    text: '1',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'federal', 1);
                                    }
                                },
                                {
                                    name: 'print_fed_2',
                                    iconCls: 'print16',
                                    text: '2',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'federal', 2);
                                    }
                                },
                                {
                                    name: 'print_fed_3',
                                    iconCls: 'print16',
                                    text: '3',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'federal', 3);
                                    }
                                },
                                {
                                    name: 'print_fed_4',
                                    iconCls: 'print16',
                                    text: '4',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'federal', 4);
                                    }
                                },
                                {
                                    name: 'print_fed_5',
                                    iconCls: 'print16',
                                    text: '5',
                                    handler: function() 
                                    {
                                            wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'federal', 5);
                                    }
                                }
                            ],
                            handler: function() 
                            {
                                wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'federal');
                            }
                        },
                        {
                            name: 'print_pgg',
                            id: 'idPrintPgg',
                            iconCls: 'print16',
                            text: 'ПГГ',
                            handler: function() 
                            {
                                wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'pgg');
                            }
                        },
                        {
                            name: 'print_event',
                            id: 'idPrintEvent',
                            iconCls: 'print16',
                            text: 'По мероприятиям',
                            handler: function() 
                            {
                                wnd.printRequest(wnd.params.Request_id, wnd.params.idVidMp, 'event');
                            }
                        }
                    ]
                }              
            );
    
            this.Grid.addActions(
                {
                    name: 'action_drpe_form',
                    iconCls: 'rpt-repo16',
                    text: 'Действия',
                    menu: [
                        {
                            name: 'share',
                            iconCls: 'group-global16',
                            text: 'Предоставить доступ к заявке МО',
                            handler: function() 
                            {
                                if (wnd.Grid.getGrid().getSelectionModel().getSelected().data.SprRequestStatus_Name == 'Новая')
                                {
                                    wnd.setRequestStatus(
                                        wnd.Grid.getGrid().getSelectionModel().getSelected().data.Request_id,
                                        2
                                    );
                                }
                                else
                                {
                                    sw.swMsg.show({
                                        title: 'Информация',
                                        msg: 'Действие доступно только для заявок со статусом "Новая"',
                                        icon: Ext.Msg.INFO,
                                        buttons: Ext.Msg.OK
                                    });
                                }


                            }
                        }, 
                        {
                            name: 'return',
                            iconCls: 'undo16',
                            text: 'Передать для корректировки КП',
                            handler: function() {
                                if (wnd.Grid.getGrid().getSelectionModel().getSelected().data.SprRequestStatus_Name == 'Согласована')
                                {
                                    wnd.setRequestStatus(
                                        wnd.Grid.getGrid().getSelectionModel().getSelected().data.Request_id,
                                        7
                                    );
                                }
                                else
                                {
                                    sw.swMsg.show({
                                        title: 'Информация',
                                        msg: 'Действие доступно только для заявок со статусом "Согласована"',
                                        icon: Ext.Msg.INFO,
                                        buttons: Ext.Msg.OK
                                    });
                                }
                            }
                        }
                    ]
                }
            );
            
            if(!this.Grid.getAction('make'))
            {
                var make_request_action = 
                {
                    id: 'act_make',
                    name: 'make',
                    text: 'Добавить',
                    iconCls : 'add16',
                    handler: function() 
                    {
                        getWnd('swVolRequestMakeWindow').show();
                    }
                }
                this.Grid.ViewActions[make_request_action.name] = new Ext.Action(make_request_action);
                this.Grid.ViewToolbar.insertButton(0, make_request_action);
                this.Grid.ViewContextMenu.add(wnd.Grid.ViewActions[make_request_action.name]);
            };
        }
        else
        {
            var grid = Ext.getCmp('idVolRequestsGrid');
            
            //grid.ViewActions.action_add.setHidden(true);
            grid.ViewActions.action_add.setDisabled(true);
            grid.ViewActions.action_delete.setHidden(true);
        }
        
        
        this.doSearch();
        wnd.onHide = Ext.emptyFn;
    },
    stepDay: function(day)
    {
        var frm = this;
        var new_value = 0;
        new_value = parseInt(frm.yearMenu.getValue()) + day;
        frm.yearMenu.setValue(new_value);
        this.Grid.getGrid().getColumnModel().setColumnHeader(4,'Заявка ' + new_value );
    },
    nextYear: function ()
    {
        this.stepDay(1);
    },
    prevYear: function ()
    {
        this.stepDay(-1);
    },
    createFormActions: function() 
    {
        var parent_object = this;
        this.yearMenu = new Ext.form.TextField({
            width: 50,
            fieldLabel: lang['period'],
            id: 'periodField',
            plugins: 
            [
                new Ext.ux.InputTextMask('9999', false)
            ],
            value: (new Date().getFullYear() + 1)
        });
        this.formActions = new Array();
        this.formActions.selectDate = new Ext.Action({
            text: ''
        });
        this.formActions.prev = new Ext.Action({
            text: lang['predyiduschiy'],
            xtype: 'button',
            iconCls: 'arrow-previous16',
            handler: function()
            {
                // на один день назад
                this.prevYear();
                this.doSearch();
            }.createDelegate(this)
        });
        this.formActions.next = new Ext.Action({
            text: lang['sleduyuschiy'],
            xtype: 'button',
            iconCls: 'arrow-next16',
            handler: function()
            {
                // на один день вперед
                this.nextYear();
                this.doSearch();
            }.createDelegate(this)
        });
    },
    doSearch: function()
    {
        var params = {};
        params.year = this.yearMenu.getValue();
        this.Grid.removeAll();
        this.Grid.loadData({globalFilters: params});
    },
    deleteRequest: function() 
    {
        if (this.isMZ == true)
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
                            url: '/?c=VolPeriods&m=deleteVolRequest',
                            params: {
                                Request_id: record.get('Request_id')
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
                                Ext.getCmp('idVolRequestsGrid').getGrid().getStore().reload();
                            }
                        });

                    }
                }
            });
        }
    },
    setRequestStatus: function(Request_id, Status_id)
    {
        var wnd = this;
        Ext.Ajax.request(
            {
                url: '/?c=VolPeriods&m=setRequestStatusAll',
                params: {
                    Request_id: Request_id,
                    Status_id: Status_id
                },
                callback: function(o, s, r) {
                    if(s) 
                    {
                        wnd.doSearch();
                    }
                }
            }
        );
    },
    printRequest: function(Request_id, VidMp_id, formType, subform)
    {
        var wnd = this;
        var form = null;
        var docNum = 1;
        var docCount = 0;
        if (subform === undefined)
        {
            subform = '-';
        }
        
        switch (formType)
        {
            case 'analit':
                switch (VidMp_id)
                {
                    case 1:
                        form = 'KS_analit';
                    break;
                    
                    case 2:
                        form = 'KSG_analit';
                        docCount = Math.ceil(wnd.params.LpuCount / 40);
                    break;
                    
                    case 3:
                        form = 'MZ_anal_form_SVOD_DS';
                    break;
                    
                    case 4:
                        form = 'MZ_anal_form_DS_SVOD_KSG';
                    break;
                    
                    case 5:
                        form = 'VMP_analit';
                    break;
                    
                    case 6:
                        form = 'MZ_analit_APU_neotl';
                    break;
                    
                    case 7:
                        form = 'MZ_analit_APU_LDU';
                    break;
                    
                    case 8:
                        form = 'MZ_analit_dializ';
                    break;
                    
                    case 9:
                        form = 'MZ_anal_form_ECO';
                    break;
                    
                    case 10:
                        form = 'SMP_analit';
                    break;
                    
                    case 11:
                        form = 'MZ_analit_cons';
                    break;
                    
                    case 12:
                        form = 'MZ_analit_notAttach';
                    break;
                    
                    case 13:
                        form = 'MZ_analit_APU_obrasch';
                    break;
                    
                    case 14:
                        form = 'MZ_analit_medos';
                    break;
                    
                    case 15:
                        form = 'MZ_analit_disp';
                    break;
                    
                    case 16:
                        form = 'MZ_analit_podush';
                    break;
                    
                    case 17:
                        form = 'MZ_analit_cz';
                    break;
					
                    case 18:
                        switch (subform)
                        {
                            case '-':
                                form = 'MZ_analit_prof';
                            break;
                            
                            case 'disp':
                                form = 'MZ_analit_disp';
                            break;
                            
                            case 'cons':
                                form = 'MZ_analit_cons';
                            break;
                            
                            case 'notAttach':
                                form = 'MZ_analit_notAttach';
                            break;
                            
                            case 'podush':
                                form = 'MZ_analit_podush';
                            break;
                            
                            case 'medos':
                                form = 'MZ_analit_medos';
                            break;
                            
                            case 'cz':
                                form = 'MZ_analit_cz';
                            break;
                        }
                            
                    break;
                    
                }
            break;
            
            case 'federal':
                switch (VidMp_id)
                {
                    case 1: //КС
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_KS_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_KS_0110';
                            break;
                            case 3:
                                form = '';
                            break;
                            case 4:
                                form = '';
                            break;
                            case 5:
                                form = '';
                            break;
                        }
                        //form = 'MZ_fed_form_KS';
                    break;
                    
                    case 2: //КС КСГ
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_KSG_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_KSG_0110';
                            break;
                            case 3:
                                form = '';
                            break;
                            case 4:
                                form = '';
                            break;
                            case 5:
                                form = '';
                            break;
                        }
                        //form = 'MZ_fed_form_KSG';
                    break;
                    
                    case 3: //ДС
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_DS_2017_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_DS_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_DS_2019_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_DS_2019_0110';
                            break;
                            case 5:
                                form = '';
                            break;
                        }
                        //form = '';
                    break;
                    
                    case 4: //ДС КСГ
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_DS_KSG_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_DS_KSG_0110';
                            break;
                            case 3:
                                form = '';
                            break;
                            case 4:
                                form = '';
                            break;
                            case 5:
                                form = '';
                            break;
                        }
                        //form = '';
                    break;
                    
                    case 5: //ВМП
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_VMP_2017_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_VMP_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_VMP_2019_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_VMP_2019_0110';
                            break;
                            case 5:
                                form = '';
                            break;
                            default:
                                form = 'M3_fed_form_SVOD_VMP_20190108';
                            break;
                        }
                        //form = 'MZ_fed_form_SVOD_VMP';
                    break;
                    
                    case 6: //АПУ Неотложка
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                    
                    case 7: //ЛДУ
                        switch(subform)
                        {
                            case 1:
                                form = '';
                            break;
                            case 2:
                                form = '';
                            break;
                            case 3:
                                form = '';
                            break;
                            case 4:
                                form = '';
                            break;
                            case 5:
                                form = '';
                            break;
                        }
                        form = '';
                    break;
                    
                    case 8: //Диализ
                        switch(subform)
                        {
                            case 1:
                                form = '';
                            break;
                            case 2:
                                form = '';
                            break;
                            case 3:
                                form = '';
                            break;
                            case 4:
                                form = '';
                            break;
                            case 5:
                                form = '';
                            break;
                        }
                        form = '';
                    break;
                    
                    case 9: //ЭКО
                        switch(subform)
                        {
                            case 1:
                                form = '';
                            break;
                            case 2:
                                form = '';
                            break;
                            case 3:
                                form = '';
                            break;
                            case 4:
                                form = '';
                            break;
                            case 5:
                                form = '';
                            break;
                        }
                        form = '';
                    break;
                    
                    case 10: //СМП
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_SMP_2017_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_SMP_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_SMP_2019_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_SMP_2019_0110';
                            break;
                            case 5:
                                form = '';
                            break;
                        }
                    break;
                    
                    case 11: //АПУ консультативные посещения
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                    
                    case 12: //АПУ неприкреп
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                    
                    case 13: //АПУ обр. заб.
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                    
                    case 14: //АПУ медосмотры
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                    
                    case 15: //АПУ Дисп
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                    
                    case 16: //АПУ прикреп
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                    
                    case 17: //АПУ ЦЗ и гериатрия
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                    
                    case 18: //АПУ Проф
                        switch(subform)
                        {
                            case 1:
                                form = 'MZ_FED_APU_2016_0108';
                            break;
                            case 2:
                                form = 'MZ_FED_APU_2018_0108';
                            break;
                            case 3:
                                form = 'MZ_FED_APU_2017_0108';
                            break;
                            case 4:
                                form = 'MZ_FED_APU_2019_0108';
                            break;
                            case 5:
                                form = 'MZ_FED_APU_2019_0110';
                            break;
                        }
                    break;
                }
            break;
            
            case 'pgg':
                switch (VidMp_id)
                {
                    case 18:
                        form = 'MZ_PGG_PROF_0108';
                    break;
                    
                    default:
                        form = '';
                    break;
                }
            break;

            case 'event':
                switch (VidMp_id)
                {
                    case 18:
                        form = 'MZ_PGG_PROF_0110';
                    break;
                    
                    default:
                        form = '';
                    break;
                }
            break;
        }
        
        if (form == 'KSG_analit')
        {
            for (docNum; docNum <= docCount; docNum++)
            {
                var url = '/?c=VolPeriods&m=ExportXls&form=' + form + '&Request_id=' + Request_id + '&part=' + docNum;
                window.open(url, '_blank');
            }
        }
        else
        {
            if (form == '')
            {
                sw.swMsg.show({
                    title: 'Внимание',
                    msg: 'Этот вид выгрузки еще не реализован',
                    icon: Ext.Msg.INFO,
                    buttons: Ext.Msg.OK
                });
            }
            else
            {
                var url = '/?c=VolPeriods&m=ExportXls&form=' + form + '&Request_id=' + Request_id;
                window.open(url, '_blank');
            }
        }
    },
    
    initComponent: function() {
        var wnd = this; 

        this.createFormActions();

        this.WindowToolbar = new Ext.Toolbar({
            items: [
                    this.formActions.prev, 
                {
                    xtype : "tbseparator"
                },
                this.yearMenu,
                //this.dateText,
                {
                    xtype : "tbseparator"
                },
                this.formActions.next, 
                {
                    xtype : "tbseparator"
                },
                {
                    xtype: 'tbfill'
                }
            ],
            id: 'idToolbar',
            region: 'north'
        });

        this.Grid = new sw.Promed.ViewFrame({
            actions: [
                {name: 'action_add', hidden: true, handler: Ext.emptyFn()},
                {name: 'action_edit', hidden: true},
                {name: 'action_view', handler: function() {
                    var wnd2 = getWnd('swVolRequestEditWindow')
                        if (wnd.mode == 'select') 
                        {
                            wnd.onSelect();
                        }
                        else 
                        {
                            if (wnd.isStat == true)
                            {
                                wnd.params.functionality = 'stat';
                            }
                            if (wnd.isBoss == true)
                            {
                                wnd.params.functionality = 'boss';
                            }
                            if (wnd.isMZ == true)
                            {
                                wnd.params.functionality = 'mz';
                            }
                            
                            wnd.params.openMode = 'view';
                            wnd2.show(wnd.params);
                        }

                }},
                {name: 'action_delete', handler: wnd.deleteRequest.createDelegate(this)},
                {name: 'action_print', disabled: true,  hidden: true}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: true,
            border: true,
            dataUrl: '/?c=VolPeriods&m=loadVolRequest',
            height: 180,
            region: 'center',
            object: 'VolRequest',
            editformclassname: 'swVolRequestEditWindow',
            id: 'idVolRequestsGrid',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: 
            [
                {name: 'Request_id', type: 'int', header: 'ID', key: true},
                {name: 'VolumeType_Name', type: 'string', header: 'Вид объёма', width: 500},
                {name: 'Request_LpuCount', header: 'Количество МО', width: 175},
                {name: 'SprRequestStatus_Name', type: 'string', header: 'Статус', width: 250},
                {name: 'Request_VolCount', type: 'int', hidden: false, header: 'Объем, ед.(План)', width: 100},
                {name: 'Request_DeviationPrct', type: 'int', hidden: false, header: 'Процент откл.', width: 100},
                {name: 'Request_Year', type: 'int', hidden: true, header: 'Год планирования', width: 100},
                {name: 'SprVidMp_id', type: 'int', hidden: true, header: 'Ид вида МП', width: 100}
            ],
            title: null,
            toolbar: true,
            onDblClick: function() 
            {
                var wnd2 = getWnd('swVolRequestEditWindow')
                    if (wnd.mode == 'select') {
                        wnd.onSelect();
                    }
                    else {
                        if (wnd.isStat == true)
                            {
                                wnd.params.functionality = 'stat';
                            }
                            if (wnd.isBoss == true)
                            {
                                wnd.params.functionality = 'boss';
                            }
                            if (wnd.isMZ == true)
                            {
                                wnd.params.functionality = 'mz';
                            }
                        wnd.params.openMode = 'edit';
                        wnd2.show(wnd.params);
                    }

            },
            onRowSelect: function(sm, index, record) {
                wnd.params.idVidMp = record.data.SprVidMp_id;
                wnd.params.NameVidMp = record.data.VolumeType_Name;
                wnd.params.RequestStatusName = record.data.SprRequestStatus_Name;
                wnd.params.Request_id = record.data.Request_id;
                wnd.params.PlanYear = wnd.yearMenu.getValue();
                wnd.params.LpuCount = record.data.Request_LpuCount;
                
                if (wnd.isMZ == true)
                {
                    var act_disp = wnd.Grid.ViewActions.action_print_form.items[0].menu.items.items[0].menu.items.items[0];
                    var act_cons = wnd.Grid.ViewActions.action_print_form.items[0].menu.items.items[0].menu.items.items[1];
                    var act_notAttach = wnd.Grid.ViewActions.action_print_form.items[0].menu.items.items[0].menu.items.items[2];
                    var act_podush = wnd.Grid.ViewActions.action_print_form.items[0].menu.items.items[0].menu.items.items[3];
                    var act_medos = wnd.Grid.ViewActions.action_print_form.items[0].menu.items.items[0].menu.items.items[4];
                    var act_cz = wnd.Grid.ViewActions.action_print_form.items[0].menu.items.items[0].menu.items.items[5];
                    var PrintPGG = wnd.Grid.ViewActions.action_print_form.items[0].menu.items.items[2];
                    if (wnd.params.idVidMp !== 18)
                    {
                        act_disp.setDisabled(true);
                        act_cons.setDisabled(true);
                        act_notAttach.setDisabled(true);
                        act_podush.setDisabled(true);
                        act_medos.setDisabled(true);
                        act_cz.setDisabled(true);
                        PrintPGG.setVisible(false);
                        PrintPGG.setDisabled(true);
                    }
                    else
                    {
                        act_disp.setDisabled(false);
                        act_cons.setDisabled(false);
                        act_notAttach.setDisabled(false);
                        act_podush.setDisabled(false);
                        act_medos.setDisabled(false);
                        act_cz.setDisabled(false);
                        PrintPGG.setVisible(true);
                        PrintPGG.setDisabled(false);
                    }
                }
                wnd.doLayout();
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
                    text: BTN_FRMCLOSE
                }],
            items:[{
                border: false,
                xtype: 'panel',
                region: 'center',
                layout: 'border',
                id: 'idMainPanel',
                items: [wnd.WindowToolbar,
                        wnd.Grid
                ]
            }]
        });
        sw.Promed.swVolRequestViewWindow.superclass.initComponent.apply(this, arguments);
    }	
});