/**
 * swJournalLeaveWindow - окно журнала выбывших из профильного отделения стационара
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Alexander Permyakov (alexpm)
 * @version      7.2013
 * @comment
 **/
/*NO PARSE JSON*/
sw.Promed.swJournalLeaveWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh: true,
    objectName: 'swJournalLeaveWindow',
    objectSrc: '/jscore/Forms/Hospital/swJournalLeaveWindow.js',

    title: 'Журнал выбывших',
    buttonAlign: 'left',
    closable: true,
    closeAction: 'hide',
    layout: 'border',
    maximized: true,
    minHeight: 400,
    minWidth: 700,
    modal: true,
    plain: true,
    id: 'swJournalLeaveWindow',

    //объект с параметрами рабочего места, с которыми была открыта форма
    userMedStaffFact: null,

    show: function () {
        sw.Promed.swJournalLeaveWindow.superclass.show.apply(this, arguments);

        if ((!arguments[0]) || (!arguments[0].userMedStaffFact)) {
            this.hide();
            Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "' + this.title + '".<br/>Не указаны параметры АРМа врача.');
        } else {
            this.userMedStaffFact = arguments[0].userMedStaffFact;

            var medstafffact_combo = this.findById('mpwpsSearch_MedStaffFact_id');// Oplachko
            medstafffact_combo.getStore().removeAll();
            setMedStaffFactGlobalStoreFilter({
                isStac: true,
                LpuSection_id: this.userMedStaffFact.LpuSection_id
            });
            medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
            medstafffact_combo.getStore().autoLoad = true;
            medstafffact_combo.setValue(this.userMedStaffFact.MedStaffFact_id);
            var record = medstafffact_combo.getStore().getById(this.userMedStaffFact.MedStaffFact_id);
            medstafffact_combo.fireEvent('select', medstafffact_combo, [record]);
        }
        this.doReset();
    },

    doSearch: function () {
        this.loadGridWithFilter(false);
    },
    doReset: function () {
        var form = this.filterPanel.getForm(),
            grid = this.EvnSectionGrid.getGrid();
        form.reset();
        form.findField('EvnSection_disDate_Range').setValue(getGlobalOptions().date + ' - ' + getGlobalOptions().date);
        form.findField('EvnSection_disDate_Range').focus(true, 250);
        var medstafffact_combo = this.findById('mpwpsSearch_MedStaffFact_id');// Oplachko
        medstafffact_combo.setValue(this.userMedStaffFact.MedStaffFact_id);
        var record = medstafffact_combo.getStore().getById(this.userMedStaffFact.MedStaffFact_id);
        medstafffact_combo.fireEvent('select', medstafffact_combo, [record]);
        grid.getStore().baseParams = {};
        this.EvnSectionGrid.removeAll(true);
        grid.getStore().removeAll();
        //this.loadGridWithFilter(true);
    },
    loadGridWithFilter: function (clear) {
        var viewFrame = this.EvnSectionGrid;
        viewFrame.removeAll();
        var params = getAllFormFieldValues(this.filterPanel);
        params.limit = 100;
        params.start = 0;
        params.LpuSection_cid = this.userMedStaffFact.LpuSection_id;
        var medstafffact_combo = this.findById('mpwpsSearch_MedStaffFact_id');// Oplachko
        params.MedStaffFact_id = medstafffact_combo.getValue();
        if (clear) {
            //default filter
        }
        else {
            //doSearch
        }
        viewFrame.loadData({
            globalFilters: params
        });
    },
    getSelectedRecord: function () {
        var record = this.EvnSectionGrid.getGrid().getSelectionModel().getSelected();
        if (!record || !record.data.EvnSection_pid) {
            Ext.Msg.alert('Ошибка', 'Ошибка выбора записи!');
            return false;
        }
        return record;
    },
    openEmk: function () {
        var record = this.getSelectedRecord();
        if (record == false) return false;
        if (getWnd('swPersonEmkWindow').isVisible()) {
            getWnd('swPersonEmkWindow').hide();
        }
        // чтобы при открытии ЭМК загрузилась форма просмотра КВС
        var searchNodeObj = false;
        if (record.data.EvnSection_pid) {
            searchNodeObj = {
                parentNodeId: 'root',
                last_child: false,
                disableLoadViewForm: false,
                EvnClass_SysNick: 'EvnPS',
                Evn_id: record.data.EvnSection_pid
            };
        }

        var emk_params = {
            Person_id: record.get('Person_id'),
            Server_id: record.get('Server_id'),
            PersonEvn_id: record.get('PersonEvn_id'),
            userMedStaffFact: this.userMedStaffFact,
            MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
            LpuSection_id: this.userMedStaffFact.LpuSection_id,
            ARMType: 'common',
            addStacActions: ['action_New_EvnPS', 'action_StacSvid'],
            searchNodeObj: searchNodeObj,
            callback: function () {
                //
            }.createDelegate(this)
        };

        Ext.Ajax.request({
            url: '/?c=EvnPS&m=beforeOpenEmk',
            params: {
                Person_id: record.get('Person_id')
            },
            failure: function (response, options) {
                showSysMsg('При получении данных для проверок произошла ошибка!');
            },
            success: function (response, action) {
                if (response.responseText) {
                    var answer = Ext.util.JSON.decode(response.responseText);
                    if (!Ext.isArray(answer) || !answer[0]) {
                        showSysMsg('При получении данных для проверок произошла ошибка! Неправильный ответ сервера.');
                        return false;
                    }
                    if (answer[0].countOpenEvnPS > 0) {
                        //showSysMsg('Создание новых КВС недоступно','У пациента имеются открытые КВС в даннном ЛПУ! Количество открытых КВС: '+ answer[0].countOpenEvnPS);
                        emk_params.addStacActions = ['action_StacSvid']; //лочить кнопку создания случая лечения, если есть незакрытые КВС в данном ЛПУ #13272
                    }
                    getWnd('swPersonEmkWindow').show(emk_params);
                }
                else {
                    showSysMsg('При получении данных для проверок произошла ошибка! Отсутствует ответ сервера.');
                }
            }
        });

        return true;
    },
    cancelLeave: function () {
        var record = this.getSelectedRecord();
        if (record == false) return false;
        var win = this;
        this.getLoadMask(LOAD_WAIT).show();
        var params = {
            EvnPS_id: record.data.EvnSection_pid
        };
        Ext.Ajax.request({
            url: '/?c=EvnLeave&m=getEvnLeaveBaseId',
            params: params,
            callback: function (options, success, response) {
                if (success && response.responseText) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (!response_obj || !response_obj[0]) {
                        win.getLoadMask().hide();
                        return false;
                    }
                    Ext.Ajax.request({
                        url: '/?c=Evn&m=deleteEvn',
                        params: {Evn_id: response_obj[0].EvnLeaveBase_id},
                        callback: function (options, success, response) {
                            if (success && response.responseText) {
                                win.getLoadMask().hide();
                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                if (response_obj.success) {
                                    win.EvnSectionGrid.refreshRecords(win.EvnSectionGrid, 0);
                                }
                            }
                        }
                    });
                    return true;
                }
                return false;
            }
        });
        return true;
    },

    initComponent: function () {
        var win = this;
        this.filterPanel = new Ext.form.FormPanel({
            autoHeight: true,
            buttonAlign: 'left',
            frame: true,
            labelAlign: 'left',
            labelWidth: 100,
            region: 'north',
            layout: 'column',
            items: [
                {
                    layout: 'form',// Oplachko
                    items: [
                        {
                            //displayField: 'MedPersonal_FIO',
                            id: 'mpwpsSearch_MedStaffFact_id',
                            parentElementId: 'mpwpsSearch_LpuSection_id',
                            name: 'Search_MedStaffFact_id',
                            emptyText: 'Врач...',
                            hideLabel: true,
                            hiddenName: 'MedStaffFact_id',
                            valueField: 'MedStaffFact_id',
                            lastQuery: '',
                            listWidth: 350,
                            //tabIndex: ,
                            width: 300,
                            xtype: 'swmedstafffactglobalcombo',
                            tpl: new Ext.XTemplate(
                                '<tpl for="."><div class="x-combo-list-item">',
                                '<table style="border: 0;">',
                                '<td style="width: 45px;"><font color="red">{MedPersonal_TabCode}&nbsp;</font></td>',
                                '<td><span style="font-weight: bold;">{MedPersonal_Fio}</span></td>',
                                '</tr></table>',
                                '</div></tpl>'
                            ),
                            listeners: {
                                'keydown': function (inp, e) {
                                    if (e.getKey() == Ext.EventObject.ENTER) {
                                        e.stopEvent();
                                        thas.doSearch();
                                    }
                                }
                            }
                        }
                    ]
                },
                {
                    layout: 'form',
                    style: 'padding-left: 20px',// Oplachko
                    items: [
                        {
                            fieldLabel: 'Дата выписки',
                            id: 'JLW_EvnSection_disDate_Range',
                            name: 'EvnSection_disDate_Range',
                            plugins: [
                                new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
                            ],
                            tabIndex: TABINDEX_EPSSW + 88,
                            labelWidth: 60,
                            width: 200,
                            xtype: 'daterangefield'
                        },
                        {
                            name: 'LpuSection_cid',
                            xtype: 'hidden'
                        },
                        {
                            name: 'PersonCardStateType_id',
                            xtype: 'hidden',
                            value: 1
                        },
                        {
                            name: 'PersonPeriodicType_id',
                            xtype: 'hidden',
                            value: 1
                        },
                        {
                            name: 'PrivilegeStateType_id',
                            xtype: 'hidden',
                            value: 1
                        },
                        {
                            name: 'SearchFormType',
                            xtype: 'hidden',
                            value: 'EvnSection'
                        }
                    ]
                },
                {
                    layout: 'form',
                    items: [
                        {
                            style: "padding-left: 20px",
                            xtype: 'button',
                            text: 'Найти',
                            iconCls: 'search16',
                            handler: function () {
                                win.doSearch();
                            }
                        }
                    ]
                },
                {
                    layout: 'form',
                    items: [
                        {
                            style: "padding-left: 20px",
                            xtype: 'button',
                            text: 'Сброс',
                            iconCls: 'resetsearch16',
                            handler: function () {
                                win.doReset();
                            }
                        }
                    ]
                }
            ],
            keys: [
                {
                    fn: function () {
                        win.doSearch();
                    },
                    key: Ext.EventObject.ENTER,
                    stopEvent: true
                }
            ]
        });

        this.EvnSectionGrid = new sw.Promed.ViewFrame({
            actions: [
                { name: 'action_add', hidden: true, disabled: true },
                { name: 'action_view', text: 'Открыть ЭМК', tooltip: 'Открыть ЭМК', handler: function () {
                    win.openEmk();
                } },
                { name: 'action_edit', text: 'Отменить выписку', tooltip: 'Отменить выписку', handler: function () {
                    win.cancelLeave();
                } },
                { name: 'action_delete', hidden: true, disabled: true },
                { name: 'action_cancel', hidden: true, disabled: true},
                { name: 'action_refresh' },
                { name: 'action_print' }
            ],
            stringfields: [
                {name: 'EvnSection_id', type: 'int', header: 'ID', key: true},
                {name: 'EvnSection_pid', type: 'int', hidden: true},
                {name: 'Person_id', type: 'int', hidden: true},
                {name: 'PersonEvn_id', type: 'int', hidden: true},
                {name: 'Server_id', type: 'int', hidden: true},
                {name: 'EvnSection_isLast', type: 'int', hidden: true},
                {name: 'MedStaffFact_id', type: 'string', hidden: true },
                {name: 'EvnPS_NumCard', type: 'string', header: '№ карты', width: 70},
                {name: 'MedPersonal_Fio', type: 'string', header: 'Врач', width: 250 },
                {name: 'Person_Surname', type: 'string', header: 'Фамилия', id: 'autoexpand', width: 250 },
                {name: 'Person_Firname', type: 'string', header: 'Имя', width: 200 },
                {name: 'Person_Secname', type: 'string', header: 'Отчество', width: 150},
                {name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Д/р', width: 90},
                {name: 'EvnSection_setDate', type: 'date', format: 'd.m.Y', header: 'Поступление', width: 90},
                {name: 'EvnSection_disDate', type: 'date', format: 'd.m.Y', header: 'Выписка', width: 90},
                {name: 'LeaveType_Name', type: 'string', header: 'Исход', width: 100 },
                //{name: 'LpuSection_Name', type: 'string', header: 'Отделение', width: 150 },
                {name: 'Diag_Name', type: 'string', header: 'Диагноз', width: 150 },
                {name: 'EvnSection_KoikoDni', type: 'int', header: 'К/дни', width: 90},
                {name: 'Person_IsBDZ', header: 'БДЗ', type: 'checkbox', width: 50},
                {name: 'PayType_Name', type: 'string', header: 'Вид оплаты', width: 100 }
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            dataUrl: C_SEARCH,
            object: 'EvnSection',
            pageSize: 100,
            paging: true,
            region: 'center',
            root: 'data',
            toolbar: true,
            totalProperty: 'totalCount',
            border: false,
            onLoadData: function () {
                //this.setActionDisabled('action_view',!(this.getCount()>0));
            },
            onRowSelect: function (sm, rowIdx, record) {
                //нельзя отменить исход из отделения
                //если движение не последнее
                //или движение закончено не сегодня
                this.setActionDisabled('action_edit', (/*!record.data.EvnSection_isLast ||*/ !record.data.EvnSection_disDate || record.data.EvnSection_disDate.format('d.m.Y') != getGlobalOptions().date));
            },
            onDblClick: function () {
                win.openEmk();
            },
            onEnter: function () {
                this.onDblClick();
            }
        });

        Ext.apply(this, {
            buttons: [
                {
                    text: '-'
                },
                HelpButton(this),
                {
                    handler: function () {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    text: BTN_FRMCLOSE
                }
            ],
            layout: 'border',
            items: [
                this.filterPanel,
                this.EvnSectionGrid
            ]
        });
        sw.Promed.swJournalLeaveWindow.superclass.initComponent.apply(this, arguments);
        //var date_range_cmp = this.filterPanel.getForm().findField('EvnSection_disDate_Range');
        var date_range_cmp = this.findById('JLW_EvnSection_disDate_Range');
        date_range_cmp.on('select', function () {
            win.doSearch();
        });
        var medstafffact_combo_cmp = this.findById('mpwpsSearch_MedStaffFact_id');// Oplachko
        medstafffact_combo_cmp.on('select', function () {
            win.doSearch();
        });
    }
});
