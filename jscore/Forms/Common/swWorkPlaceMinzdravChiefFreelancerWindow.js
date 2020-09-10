/**
* АРМ главного внештатного специалиста при МЗ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Salakhov R.
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      01.2016
*/
sw.Promed.swWorkPlaceMinzdravChiefFreelancerWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	id: 'swWorkPlaceMinzdravChiefFreelancerWindow',
    showToolbar: false,
    changeYear: function(value) {
        var val = this.YearField.getValue();
        if (!val || value == 0) {
            val = (new Date()).getFullYear();
        }
        this.YearField.setValue(val+value);
    },
    doSearch: function() {
        var wnd = this;

        var params = new Object();
        params.Year = this.YearField.getValue();
        params.limit = 100;
        params.start = 0;
        params.DrugRequestProperty_Org_id = getGlobalOptions().minzdrav_org_id;

        this.GridPanel.removeAll();
        this.GridPanel.loadData({
            globalFilters: params
        });
    },
    show: function() {
        var wnd = this;

        wnd.changeYear(0);

        sw.Promed.swWorkPlaceMinzdravChiefFreelancerWindow.superclass.show.apply(this, arguments);

        this.userMedStaffFact = arguments[0];

        this.FilterPanel.hide();
        this.doLayout();

        //this.doSearch(true, true);
    },
	initComponent: function() {
		var wnd = this;
		
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

        this.YearField = new Ext.form.NumberField({
            allowDecimal: false,
            allowNegtiv: false,
            width: 35,
            enableKeyEvents: true,
            listeners: {
                'keydown': function (inp, e) {
                    if (e.getKey() == Ext.EventObject.ENTER) {
                        e.stopEvent();
                        wnd.doSearch();
                    }
                }
            }
        });

        this.YearWindowToolbar = new Ext.Toolbar({
            items: [{
                xtype: 'button',
                disabled: true,
                text: 'Год'
            }, {
                text: null,
                xtype: 'button',
                iconCls: 'arrow-previous16',
                handler: function() {
                    wnd.changeYear(-1);
                    wnd.doSearch();
                }.createDelegate(this)
            }, {
                xtype : "tbseparator"
            },
            wnd.YearField,
            {
                xtype : "tbseparator"
            }, {
                text: null,
                xtype: 'button',
                iconCls: 'arrow-next16',
                handler: function() {
                    wnd.changeYear(1);
                    wnd.doSearch();
                }.createDelegate(this)
            }, {
                xtype: 'tbfill'
            }]
        });

        Ext.apply(this,	{
            tbar: this.YearWindowToolbar
        });

        this.buttonPanelActions = {
            action_Recept: {
                nn: 'action_References',
                tooltip: 'Рецепты',
                text: 'Рецепты',
                iconCls : 'recept-search32',
                disabled: false,
                menuAlign: 'tr?',
                menu: new Ext.menu.Menu({
                    items: [{
                        tooltip: 'Поиск рецептов',
                        text: 'Поиск рецептов',
                        iconCls: 'receipt-search16',
                        handler: function() {
                            getWnd('swEvnReceptSearchWindow').show();
                        }
                    }, {
                        tooltip: 'Журнал отсрочки',
                        text: 'Журнал отсрочки',
                        iconCls : 'receipt-incorrect16',
                        handler: function()	{
                            getWnd('swReceptInCorrectSearchWindow').show();
                        }
                    }]
                })
            },
            action_Notify: {
                nn: 'action_Notify',
                tooltip: 'Извещения/Направления',
                text: 'Извещения/Направления',
                iconCls : 'doc-notify32',
                disabled: false,
                menuAlign: 'tr?',
                menu: new Ext.menu.Menu({
                    items: [/*{
                        tooltip: 'Журнал Извещений форма №058/У',
                        text: 'Журнал Извещений форма №058/У',
                        iconCls : 'journal16',
                        disabled: false,
                        handler: function() {
                            if (getWnd('swEvnInfectNotifyListWindow').isVisible()) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: 'Окно уже открыто',
                                    title: ERR_WND_TIT
                                });
                                return false;
                            }
                            getWnd('swEvnInfectNotifyListWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    }, {
                        tooltip: 'Журнал Извещений по Вирусному гепатиту',
                        text: 'Журнал Извещений по Вирусному гепатиту',
                        iconCls: 'journal16',
                        handler: function() {
                            if (getWnd('swEvnNotifyHepatitisListWindow').isVisible()) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: 'Окно уже открыто',
                                    title: ERR_WND_TIT
                                });
                            } else {
                                getWnd('swEvnNotifyHepatitisListWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                            }
                        }
                    }, {
                        tooltip: 'Журнал Извещений об онкобольных ',
                        text: 'Журнал Извещений об онкобольных ',
                        iconCls : 'journal16',
                        handler: function() {
                            if (getWnd('swEvnOnkoNotifyListWindow').isVisible()) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: 'Окно уже открыто',
                                    title: ERR_WND_TIT
                                });
                            } else {
                                getWnd('swEvnOnkoNotifyListWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                            }
                        }
                    },
                    {
                        tooltip: 'Журнал Извещений по психиатрии',
                        text: 'Журнал Извещений по психиатрии',
                        iconCls : 'journal16',
                        disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
                        handler: function() {
                            getWnd('swEvnNotifyCrazyListWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    }, {
                        tooltip: 'Журнал Извещений по наркологии',
                        text: 'Журнал Извещений по наркологии',
                        iconCls : 'journal16',
                        disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
                        handler: function() {
                            getWnd('swEvnNotifyNarkoListWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    }, {
                        tooltip: 'Журнал Извещений о больных туберкулезом',
                        text: 'Журнал Извещений по туберкулезным заболеваниям',
                        iconCls : 'journal16',
                        disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
                        handler: function() {
                            getWnd('swEvnNotifyTubListWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    }, {
                        tooltip: 'Журнал Извещений о больных венерическим заболеванием',
                        text: 'Журнал Извещений о больных венерическим заболеванием',
                        iconCls : 'journal16',
                        disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
                        handler: function() {
                            getWnd('swEvnNotifyVenerListWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    }, {
                        tooltip: 'Журнал Извещений о ВИЧ-инфицированных',
                        text: 'Журнал Извещений о ВИЧ-инфицированных',
                        iconCls : 'journal16',
                        disabled: (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0),
                        handler: function() {
                            getWnd('swEvnNotifyHIVListWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    },*/
                    {
                        tooltip: lang['jurnal_izvescheniy_napravleniy_po_vzn'],
                        text: lang['jurnal_izvescheniy_napravleniy_po_vzn'],
                        iconCls : 'journal16',
                        handler: function() {
                            if ( getWnd('swEvnNotifyRegisterNolosListWindow').isVisible() ) {
                                getWnd('swEvnNotifyRegisterNolosListWindow').hide();
                            }
                            getWnd('swEvnNotifyRegisterNolosListWindow').show({userMedStaffFact: new Object(), fromARM: 'mzchieffreelancer'});
                        }
                    },
                    {
                        tooltip: lang['jurnal_izvescheniy_napravleniy_ob_orfannyih_zabolevaniyah'],
                        text: lang['jurnal_izvescheniy_napravleniy_ob_orfannyih_zabolevaniyah'],
                        iconCls : 'journal16',
                        handler: function() {
                            if ( getWnd('swEvnNotifyRegisterOrphanListWindow').isVisible() ) {
                                getWnd('swEvnNotifyRegisterOrphanListWindow').hide();
                            }
                            getWnd('swEvnNotifyRegisterOrphanListWindow').show({userMedStaffFact: new Object(), fromARM: 'mzchieffreelancer'});
                        }
                    }
                    ]
                })
            },
            action_Register: {
                nn: 'action_Register',
                    tooltip: 'Регистры',
                    text: 'Регистры',
                    iconCls : 'registry32',
                    disabled: false,
                    menuAlign: 'tr?',
                    menu: new Ext.menu.Menu({
                    items: [/*{
                        tooltip: 'Регистр беременных',
                        text: 'Регистр беременных',
                        iconCls : 'doc-reg16',
						disabled: !isPregnancyRegisterAccess(),
                        hidden: false,
                        handler: function() {
                            getWnd('swPersonPregnancyWindow').show();
                        }
                    }, {
                        tooltip: 'Регистр детей-сирот',
                        text: 'Регистр детей-сирот',
                        iconCls : 'doc-reg16',
                        handler: function() {
                            getWnd('swPersonDispOrpSearchWindow').show();
                        }
                    }, {
                        tooltip: 'Регистр ВОВ',
                        text: 'Регистр ВОВ',
                        iconCls : 'doc-reg16',
                        handler: function() {
                            getWnd('swPersonPrivilegeWOWSearchWindow').show();
                        }
                    }, {
                        tooltip: 'Регистр ДД',
                        text: 'Регистр ДД',
                        iconCls : 'doc-reg16',
                        handler: function() {
                            getWnd('swPersonDopDispSearchWindow').show();
                        }
                    }, {
                        tooltip: 'Регистр декретированных возрастов',
                        text: 'Регистр декретированных возрастов',
                        iconCls : 'doc-reg16',
                        handler: function() {
                            getWnd('swEvnPLDispTeen14SearchWindow').show();
                        }
                    },*/ {
                        tooltip: 'Регистр по Вирусному гепатиту',
                        text: 'Регистр по Вирусному гепатиту',
                        iconCls : 'doc-reg16',
                        handler: function() {
                            if (getWnd('swHepatitisRegistryWindow').isVisible()) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: 'Окно уже открыто',
                                    title: ERR_WND_TIT
                                });
                            } else {
                                getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                            }
                        }
                    }, {
                        tooltip: 'Регистр по онкологии',
                        text: 'Регистр по онкологии',
                        iconCls : 'doc-reg16',
                        disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0),
                        handler: function() {
                            if ( getWnd('swOnkoRegistryWindow').isVisible() ) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: 'Окно уже открыто',
                                    title: ERR_WND_TIT
                                });
                            } else {
                                getWnd('swOnkoRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                            }
                        }
                    },
                    {
                        tooltip: lang['registr_po_orfannyim_zabolevaniyam'],
                        text: lang['registr_po_orfannyim_zabolevaniyam'],
                        iconCls : 'doc-reg16',
                        handler: function()
                        {
                            if ( getWnd('swPersonRegisterOrphanListWindow').isVisible() ) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: lang['okno_uje_otkryito'],
                                    title: ERR_WND_TIT
                                });
                                return false;
                            }
                            getWnd('swPersonRegisterOrphanListWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister', fromARM: 'mzchieffreelancer'});
                        }.createDelegate(this)
                    },
                    {
                        tooltip: 'Регистр по психиатрии',
                        text: 'Регистр по психиатрии',
                        iconCls : 'doc-reg16',
                        disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
                        handler: function() {
                            getWnd('swCrazyRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact,fromARM: 'mzchieffreelancer'});
                        }
                    }, {
                        tooltip: 'Регистр по наркологии',
                        text: 'Регистр по наркологии',
                        iconCls : 'doc-reg16',
                        disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
                        handler: function() {
                            getWnd('swNarkoRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact,fromARM: 'mzchieffreelancer'});
                        }
                    }, /*{
                        tooltip: 'Регистр по сахарному диабету',
                        text: 'Регистр по сахарному диабету',
                        iconCls : 'doc-reg16',
                        hidden: !getRegionNick().inlist([ 'pskov','khak','saratov','buryatiya' ]),
                        handler: function() {
                            if (getWnd('swDiabetesRegistryWindow').isVisible()) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: 'Окно уже открыто',
                                    title: ERR_WND_TIT
                                });
                                return false;
                            }
                            getWnd('swDiabetesRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    },*/ {
                        tooltip: 'Регистр больных туберкулезом',
                        text: 'Регистр по туберкулезным заболеваниям',
                        iconCls : 'doc-reg16',
                        disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
                        handler: function() {
                            getWnd('swTubRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact,fromARM: 'mzchieffreelancer'});
                        }
                    }, {
                        tooltip: 'Регистр больных венерическим заболеванием',
                        text: 'Регистр больных венерическим заболеванием',
                        iconCls : 'doc-reg16',
                        disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
                        handler: function() {
                            getWnd('swVenerRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    }, {
                        tooltip: 'Регистр ВИЧ-инфицированных',
                        text: 'Регистр ВИЧ-инфицированных',
                        iconCls : 'doc-reg16',
                        disabled: !allowHIVRegistry(),
                        handler: function() {
                            getWnd('swHIVRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact,fromARM: 'mzchieffreelancer'});
                        }
                    },
                    {
                        tooltip: lang['registr_po_vzn'],
                        text: lang['registr_po_vzn'],
                        iconCls : 'doc-reg16',
                        handler: function() {
                            if ( getWnd('swPersonRegisterNolosListWindow').isVisible() ) {
                                getWnd('swPersonRegisterNolosListWindow').hide();
                            }
                            getWnd('swPersonRegisterNolosListWindow').show({userMedStaffFact: wnd.userMedStaffFact, editType: 'onlyRegister', fromARM: 'mzchieffreelancer'});
                        }
                    },
                    {
                        tooltip: lang['registr_po_profzabolevaniyam'],
                        text: lang['registr_po_profzabolevaniyam'],
                        iconCls : 'doc-reg16',
                        handler: function(){
                            getWnd('swProfRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister',fromARM: 'mzchieffreelancer'});
                        }.createDelegate(this)
                    },
                    {
                        tooltip: lang['registr_po_nefrologii'],
                        text: lang['registr_po_nefrologii'],
                        iconCls : 'doc-reg16',
                        handler: function()
                        {
                            if ( getWnd('swNephroRegistryWindow').isVisible() ) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: lang['okno_uje_otkryito'],
                                    title: ERR_WND_TIT
                                });
                                return false;
                            }
                            getWnd('swNephroRegistryWindow').show({editType: 'onlyRegister',fromARM: 'mzchieffreelancer'});
                        }.createDelegate(this)
                    },
                    {
                        tooltip: lang['registr_ibs'],
                        text: lang['registr_ibs'],
                        iconCls : 'doc-reg16',
                        hidden: ('perm' != getRegionNick()),
                        handler: function()
                        {
                            if ( getWnd('swIBSRegistryWindow').isVisible() ) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: lang['okno_uje_otkryito'],
                                    title: ERR_WND_TIT
                                });
                                return false;
                            }
                            getWnd('swIBSRegistryWindow').show({editType: 'onlyRegister'});
                        }.createDelegate(this)
                    }
                    /*{
                        tooltip: 'Регистр по эндопротезированию',
                        text: 'Регистр по эндопротезированию',
                        iconCls : 'doc-reg16',
                        disabled: !isUserGroup('EndoRegistry'),
                        handler: function() {
                            if (getWnd('swEndoRegistryWindow').isVisible()) {
                                sw.swMsg.show({
                                    buttons: Ext.Msg.OK,
                                    fn: Ext.emptyFn,
                                    icon: Ext.Msg.WARNING,
                                    msg: 'Окно уже открыто',
                                    title: ERR_WND_TIT
                                });
                                return false;
                            }
                            getWnd('swEndoRegistryWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    }, {
                        disabled: !(isUserGroup('OperBirth')||isUserGroup('OperRegBirth')),
                        text: getRegionNick().inlist(['ufa']) ? 'Мониторинг детей первого года жизни' : 'Мониторинг новорожденных',
                        tooltip: 'Мониторинг новорожденных',
                        iconCls : 'doc-reg16',
                        handler: function() {
                            getWnd('swMonitorBirthSpecWindow').show({userMedStaffFact: wnd.userMedStaffFact});
                        }
                    }*/]
                })
            },
            action_OpenEmkAction: {
                text: 'Открыть ЭМК',
                tooltip: 'Найти человека, открыть его ЭМК',
                iconCls: 'patient-search32',
                handler: function() {
                    getWnd('swPersonSearchWindow').show({
                        onSelect: function(person_data) {
                            getWnd('swPersonSearchWindow').hide();
                            person_data.ARMType = 'common';
                            person_data.userMedStaffFact = {ARMType: 'OuzSpec'};
                            person_data.readOnly = true;
                            getWnd('swPersonEmkWindow').show(person_data);
                        },
                        searchMode: 'all'
                    });
                }
            },
            action_DrugOstatRegistryList: {
                nn: 'action_DrugOstatRegistryList',
                tooltip: 'Просмотр остатков',
                text: 'Просмотр остатков',
                iconCls : 'dlo32',
                handler: function() {
                    getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
                }
            },
            action_References: {
                nn: 'action_References',
                tooltip: 'Справочники',
                text: 'Справочники',
                iconCls : 'book32',
                disabled: false,
                menuAlign: 'tr?',
                menu: new Ext.menu.Menu({
                    items: [{
                        tooltip: 'МКБ-10',
                        text: 'Справочник МКБ-10',
                        iconCls: 'spr-mkb16',
                        handler: function() {
                            if (!getWnd('swMkb10SearchWindow').isVisible()) {
                                getWnd('swMkb10SearchWindow').show({onlyView: true});
                            }
                        }
                    }, {
                        name: 'action_DrugNomenSpr',
                        text: 'Номенклатурный справочник',
                        iconCls : '',
                        handler: function() {
                            getWnd('swDrugNomenSprWindow').show({onlyView: true});
                        }
                    },
					sw.Promed.Actions.swDrugDocumentSprAction,
					{
                        name: 'action_PriceJNVLP',
                        text: 'Цены на ЖНВЛП',
                        iconCls : 'dlo16',
                        handler: function() {
                            getWnd('swJNVLPPriceViewWindow').show();
                        }
                    }, {
                        name: 'action_DrugMarkup',
                        text: 'Предельные надбавки на ЖНВЛП',
                        iconCls : 'lpu-finans16',
                        handler: function() {
                            getWnd('swDrugMarkupViewWindow').show();
                        }
                    },
                    sw.Promed.Actions.swPrepBlockSprAction,
                    {
                        tooltip: 'Просмотр РЛС',
                        text: 'Просмотр РЛС',
                        iconCls: 'rls16',
                        handler: function() {
                            if (!getWnd('swRlsViewForm').isVisible()) {
                                getWnd('swRlsViewForm').show({onlyView: true});
                            }
                        }
                    }]
                })
            },
            action_JourNotice: {
                nn: 'action_JourNotice',
                tooltip: 'Журнал уведомлений',
                text: 'Журнал уведомлений',
                iconCls: 'notice32',
                handler: function() {
                    getWnd('swMessagesViewWindow').show();
                }
            }
        };

        this.GridPanel = new sw.Promed.ViewFrame({
			id: 'wpmcfWorkPlaceGridPanel',
			region: 'center',
            actions: [
                {name: 'action_add', disabled: true, hidden: true},
                {name: 'action_edit', disabled: true, hidden: true},
                {
                    name: 'action_view',
                    text: 'Просмотр заявок МО',
                    tooltip: 'Просмотр заявок МО',
                    handler: function() {
                        var record = wnd.GridPanel.getGrid().getSelectionModel().getSelected();
                        if (record.get('DrugRequest_id') > 0) {
                            getWnd('swMzDrugRequestMoViewWindow').show({
                                ARMType: wnd.userMedStaffFact.ARMType,
                                DrugRequest_id: record.get('DrugRequest_id'),
                                DrugRequest_Name: record.get('DrugRequest_Name'),
                                DrugRequestPeriod_id: record.get('DrugRequestPeriod_id'),
                                PersonRegisterType_id: record.get('PersonRegisterType_id'),
                                DrugRequestKind_id: record.get('DrugRequestKind_id'),
                                DrugGroup_id: record.get('DrugGroup_id')
                            });
                        }
                    }
                },
                {name: 'action_delete', disabled: true, hidden: true},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MzDrugRequest&m=loadRegionList',
            height: 180,
            object: 'DrugRequest',
            paging: false,
            style: 'margin-bottom: 10px',
            stringfields: [
                {name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
                {name: 'DrugRequestPeriod_id', type: 'int', hidden: true},
                {name: 'PersonRegisterType_id', type: 'int', hidden: true},
                {name: 'DrugRequestKind_id', type: 'int', hidden: true},
                {name: 'DrugGroup_id', type: 'int', hidden: true},
                {name: 'DrugRequestProperty_OrgName', type: 'string', header: 'Координатор', width: 190},
                {name: 'PersonRegisterType_Name', type: 'string', header: 'Тип', width: 90},
                {name: 'DrugRequestKind_Name', type: 'string', header: 'Вид', width: 90},
                {name: 'DrugRequest_Name', type: 'string', header: 'Наименование', width: 120, id: 'autoexpand'},
                {name: 'MoDrugRequest_Count', type: 'string', header: 'Кол-во МО', width: 90},
                {name: 'DrugRequestStatus_id', type: 'int', hidden: true},
                {name: 'DrugRequestStatus_Code', type: 'int', hidden: true},
                {name: 'DrugRequestStatus_Name', type: 'string', header: 'Статус', width: 120},
                {name: 'DrugRequest_Summa', header: 'Сумма', width: 120,
                    renderer: function(value,p,rec){
                        var total = rec.get('DrugRequestQuota_Total');
                        if (value > 0 && total > 0 && value*1 > total*1 ) {
                            return '<span style="color: red">' + value + '</span>';
                        }
                        return value;
                    }
                },
                {name: 'DrugRequestQuota_Total', type: 'string', header: 'Лимит фед.заявки', width: 120, hidden: true},
                {name: 'SvodDrugRequest_Name', type: 'string', header: 'Сводная заявка', width: 120}
            ],
            title: 'Журнал рабочего места',
            toolbar: true,
            onRowSelect: function(sm, rowIdx, record) {
                if (record.get('DrugRequest_id') > 0 && !this.readOnly) {
                    this.ViewActions.action_view.setDisabled(false);
                } else {
                    this.ViewActions.action_view.setDisabled(true);
                }
            },
            onDblClick: function(grid, number, object) {
                var viewframe = grid.ownerCt.ownerCt;
                if (!viewframe.getAction('action_view').isDisabled()) {
                    viewframe.runAction('action_view');
                }
            }
		});

		sw.Promed.swWorkPlaceMinzdravChiefFreelancerWindow.superclass.initComponent.apply(this, arguments);
	}
});