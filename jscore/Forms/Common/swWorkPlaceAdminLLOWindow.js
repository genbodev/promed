/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 18.09.14
 * Time: 17:02
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swWorkPlaceAdminLLOWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
    id: 'swWorkPlaceAdminLLOWindow',
    show: function() {
        this.doReset();

        sw.Promed.swWorkPlaceAdminLLOWindow.superclass.show.apply(this, arguments);
        this.userMedStaffFact = arguments[0];
        var wnd = this;
        var region_nick = getRegionNick();

        if (region_nick != 'msk') {
            if (!this.GridPanel.getAction('send_act_about_import')) {
                this.GridPanel.addActions({
                    text: langs('Передать акт о приеме данных'),
                    name: 'send_act_about_import',
                    handler: function () {
                        this.execAction('sendActAboutImport', langs('Выполняется формирование акта...'));
                    },
                    scope: this
                });
            }
            if (!this.GridPanel.getAction('import_and_exp')) {
                this.GridPanel.addActions({
                    text: langs('Провести импорт и экспертизу данных'),
                    name: 'import_and_exp',
                    handler: function () {
                        this.execAction('importAndExpertise', langs('Выполняется импорт и экспертиза данных...'));
                    },
                    scope: this
                });
            }

            var b_f = this.FilterPanel.getForm();
            if( !b_f.findField('Contragent_id').getStore().getCount() ) {
                b_f.findField('Contragent_id').getStore().load({
                    params: { ContragentType_id: 1 } // только организации
                });
            }
        } else {
            if(!this.GridPanel.getAction('action_expertise')){
                this.GridPanel.addActions({
                    handler: function() {
                        wnd.doExpertise();
                    },
                    name: 'action_expertise',
                    text: 'Экспертиза запроса',
                    iconCls: 'actions16'
                });
            }
            this.GridPanel.initEnabledActions();
        }

        if(this.LeftPanel.actions.action_RLS){
            this.LeftPanel.actions.action_RLS.hide();
        }
        if(this.LeftPanel.actions.action_Mes){
            this.LeftPanel.actions.action_Mes.hide();
        }
    },
    enableDefaultActions: false,
    buttonPanelActions: {
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
                        getWnd('swLgotTreeViewWindow').show({onlyView: true, ARMType: 'adminllo'});
                    }
                }, {
                    text: MM_DLO_LGOTSEARCH,
                    tooltip: langs('Поиск льготников'),
                    iconCls : 'lgot-search16',
                    handler: function() {
                        getWnd('swPrivilegeSearchWindow').show({onlyView: true, ARMType: 'adminllo'});
                    }
                },
                    '-',
                    {
                        text: MM_DLO_UDOSTLIST,
                        tooltip: langs('Удостоверения льготников: Поиск'),
                        iconCls : 'udost-list16',
                        handler: function() {
                            getWnd('swUdostViewWindow').show({onlyView: true, ARMType: 'adminllo'});
                        }
                    }]
            })
        },
        action_Recept: {
            nn: 'action_Register',
            tooltip: langs('Рецепты'),
            text: langs('Рецепты'),
            iconCls : 'recept-search32',
            disabled: false,
            menuAlign: 'tr?',
            menu: new Ext.menu.Menu({
                items: [{
                    text: langs('Поиск рецептов'),
                    tooltip: langs('Поиск рецептов'),
                    iconCls: 'receipt-search16',
                    handler: function() {
                        getWnd('swEvnReceptSearchWindow').show({onlyView: true});
                    }
                }, {
                    text: langs('Журнал отсрочки'),
                    tooltip: langs('Журнал отсрочки'),
                    iconCls : 'receipt-incorrect16',
                    handler: function()	{
                        getWnd('swReceptInCorrectSearchWindow').show({onlyView: true});
                    }
                }]
            })
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
                    iconCls : 'doc-reg16',
                    disabled: false,
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
                        getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, onlyView: true, ARMType: 'adminllo'});
                    }.createDelegate(this)
                }, {
                    tooltip: langs('Регистр по онкологии'),
                    text: langs('Регистр по онкологии'),
                    iconCls : 'doc-reg16',
                    disabled: false,
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
                        getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType: 'adminllo'});
                    }.createDelegate(this)
                }, {
                    tooltip: langs('Регистр больных венерическим заболеванием'),
                    text: langs('Регистр больных венерическим заболеванием'),
                    iconCls : 'doc-reg16',
                    disabled: false,
                    handler: function() {
                        getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType: 'adminllo'});
                    }.createDelegate(this)
                },
                    sw.Promed.personRegister.getOrphanBtnConfig('swWorkPlaceAdminLLOWindow', null),
                    sw.Promed.personRegister.getVznBtnConfig('swWorkPlaceAdminLLOWindow', null),
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
                            getWnd('swDiabetesRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType: 'adminllo'});
                        }.createDelegate(this)
                    }, {
                        tooltip: langs('Регистр по детям из многодетных семей'),
                        text: langs('Регистр по детям из многодетных семей'),
                        iconCls : 'doc-reg16',
                        hidden: !getRegionNick().inlist([ 'pskov', 'saratov' ]),
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
                            getWnd('swLargeFamilyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType: 'adminllo'});
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
        action_LLO: {
            nn: 'action_LLO',
            tooltip: langs('Заявки ЛЛО'),
            text: langs('Заявки ЛЛО'),
            iconCls : 'mp-drugrequest32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [{
                    text: langs('Нормативные списки'),
                    tooltip: langs('Нормативные списки'),
                    iconCls : 'drug-name16',
                    handler: function() {
                        getWnd('swDrugNormativeListViewWindow').show({onlyView: true});
                    }
                }, {
                    text: langs('Списки медикаментов для заявки'),
                    tooltip: langs('Списки медикаментов для заявки'),
                    iconCls : 'pill16',
                    handler: function() {
                        getWnd('swDrugRequestPropertyViewWindow').show({onlyView: true, ARMType: 'adminllo'});
                    }
                }, {
                    text: langs('Сводная заявка'),
                    tooltip: langs('Сводная заявка (просмотр, утверждение)'),
                    iconCls : 'otd-profile16',
                    handler: function() {
                        getWnd('swConsolidatedDrugRequestViewWindow').show({onlyView: true});
                    }
                }, {
                    tooltip: langs('Лоты'),
                    text: langs('Лоты'),
                    iconCls : 'settings16',
                    handler: function(){
                        getWnd('swUnitOfTradingViewWindow').show({
                            disableAdd: true,
                            disableEdit:true,
                            actionsCancel: true
                        });
                    }.createDelegate(this)
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
        action_WhsDocument: {
            nn: 'action_WhsDocument',
            tooltip: langs('Госконтракты'),
            text: langs('Госконтракты'),
            iconCls : 'card-state32',
            disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: langs('Госконтракты на поставку'),
					text: langs('Госконтракты на поставку'),
					iconCls : 'document16',
					handler: function(){
						getWnd('swWhsDocumentSupplyViewWindow').show({onlyView: false, ARMType: 'adminllo'});
					}
				}, {
					tooltip: langs('Дополнительные соглашения'),
					text: langs('Дополнительные соглашения'),
					iconCls : 'document16',
					handler: function(){
						getWnd('swWhsDocumentSupplyAdditionalViewWindow').show({ARMType: 'adminllo'});
					}
				}]
			})
        },
        action_Documents: {
            nn: 'action_Documents',
            tooltip: langs('Документы'),
            text: langs('Документы'),
            iconCls : 'document32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [{
                    text: langs('Правоустанавливающие документы'),
                    tooltip: langs('Правоустанавливающие документы'),
                    iconCls : 'document16',
                    handler: function() {
                        getWnd('swWhsDocumentTitleViewWindow').show({onlyView: false});
                    }.createDelegate(this)
                },{
                    tooltip: langs('По резерву'),
                    text: langs('По резерву'),
                    iconCls : 'datepicker-day16',
                    hidden: getGlobalOptions().region.nick == 'msk',
                    handler: function(){
                        getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 12, onlyView: false});
                    }.createDelegate(this)
                },{
                    text: langs('Разнарядки на выписку рецептов'),
                    tooltip: langs('Разнарядки на выписку рецептов'),
                    iconCls : 'drug-name16',
                    hidden: getGlobalOptions().region.nick == 'msk',
                    menu: new Ext.menu.Menu({
                        items: [{
                            tooltip: langs('Распоряжения на выдачу разнарядок'),
                            text: langs('Распоряжения на выдачу разнарядок'),
                            iconCls : 'pill16',
                            handler: function(){
                                getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 7, onlyView: false});
                            }.createDelegate(this)
                        }, {
                            text: langs('Просмотр разнарядки'),
                            tooltip: langs('Просмотр разнарядки на выписку рецптов'),
                            iconCls : 'pill16',
                            handler: function() {
                                getWnd('swWhsDocumentOrderAllocationSearchWindow').show({onlyView: false});
                            }
                        }]
                    })
                },{
                    text: langs('Планы поставок'),
                    tooltip: langs('Планы поставок'),
                    iconCls : 'plan16',
                    hidden: getGlobalOptions().region.nick == 'msk',
                    handler: function() {
                        getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 15, onlyView: false});
                    }
                },{
                    text: langs('Распределение ЛС по аптекам'),
                    tooltip: langs('Распределение ЛС по аптекам'),
                    iconCls : 'pill16',
                    hidden: getGlobalOptions().region.nick == 'msk',
                    handler: function() {
                        getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 17, onlyView: false});
                    }
                },{
                    text: langs('Коммерческие предложения'),
                    tooltip: langs('Коммерческие предложения'),
                    iconCls : 'document16',
                    handler: function() {
                        getWnd('swCommercialOfferViewWindow').show({onlyView: false});
                    }
                }
                ]
            })
        },
        action_Contragents: {
            nn: 'action_Contragents',
            tooltip: langs('Справочник: Контрагенты'),
            text: langs('Справочник: Контрагенты'),
            iconCls : 'org32',
            disabled: false,
            handler: function() {
                getWnd('swContragentViewWindow').show({
                    ARMType: 'adminllo',
                    onlyView: true
                });
            }
        },
        action_References: {
            nn: 'action_References',
            tooltip: langs('Справочники'),
            text: langs('Справочники'),
            iconCls : 'book32',
            disabled: false,
            menuAlign: 'tr?',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: langs('Просмотр РЛС'),
                    text: langs('Просмотр РЛС'),
                    iconCls: 'rls16',
                    handler: function() {
                        if ( !getWnd('swRlsViewForm').isVisible() )
                            getWnd('swRlsViewForm').show({onlyView: true});
                    }
                }, {
                    tooltip: langs('МКБ-10'),
                    text: langs('Справочник МКБ-10'),
                    iconCls: 'spr-mkb16',
                    handler: function() {
                        if ( !getWnd('swMkb10SearchWindow').isVisible() )
                            getWnd('swMkb10SearchWindow').show({onlyView: true});
                    }
                }, {
                    tooltip: langs('Просмотр') + getMESAlias(),
                    text: langs('Просмотр') + getMESAlias(),
                    iconCls: 'spr-mes16',
                    handler: function() {
                        if ( !getWnd('swMesOldSearchWindow').isVisible() )
                            getWnd('swMesOldSearchWindow').show({onlyView: true, ARMType: 'adminllo'});
                    }
                },
				sw.Promed.Actions.swDrugDocumentSprAction,
				{
                    name: 'action_DrugNomenSpr',
                    text: langs('Номенклатурный справочник'),
                    iconCls : '',
                    handler: function()
                    {
                        getWnd('swDrugNomenSprWindow').show({onlyView: true});
                    }
                }, {
					name: 'action_DrugMnnCodeSpr',
					text: langs('Справочник МНН'),
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugMnnCodeViewWindow').show({readOnly: false});
					}
				}, {
					name: 'action_DrugTorgCodeSpr',
					text: langs('Справочник Торговых наименований'),
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugTorgCodeViewWindow').show({readOnly: false});
					}
				}, {
                    name: 'action_PriceJNVLP',
                    text: langs('Цены на ЖНВЛП'),
                    iconCls : 'dlo16',
                    handler: function() {
                        getWnd('swJNVLPPriceViewWindow').show();
                    }
                }, {
                    name: 'action_DrugMarkup',
                    text: langs('Предельные надбавки на ЖНВЛП'),
                    iconCls : 'lpu-finans16',
                    handler: function() {
                        getWnd('swDrugMarkupViewWindow').show();
                    }
                }, {
                    name: 'action_DrugRMZ',
                    text: langs('Справочник РЗН'),
                    iconCls : 'view16',
                    handler: function() {
                        getWnd('swDrugRMZViewWindow').show();
                    }
                },
				sw.Promed.Actions.swPrepBlockSprAction,
				{
					text: 'Единицы измерения товара',
					tooltip: 'Единицы измерения товара',
					handler: function() {
						getWnd('swGoodsUnitViewWindow').show({allowImportFromRls: true});
					}
				}
				]
            })
        },
        action_PersonCardSearch: {
            handler: function() {
                getWnd('swPersonCardSearchWindow').show({onlyView: true});
            },
            iconCls : 'card-search32',
            nn: 'action_PersonCardSearch',
            text: WND_POL_PERSCARDSEARCH,
            tooltip: langs('РПН: Поиск')
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
                            userMedStaffFact: getWnd('swWorkPlaceAdminLLOWindow').userMedStaffFact
                        });
                    }.createDelegate(this),
		    hidden: getGlobalOptions().region.nick == 'ufa'
				}, {
					    text: 'Оборотная ведомость',
					    tooltip: 'Оборотная ведомость',
					    iconCls: 'pill16',
					    handler: function() {
						    getWnd('swDrugTurnoverListWindow').show();//{mode: 'suppliers'});
					    },
					    hidden: getGlobalOptions().region.nick != 'ufa'
				},
				{
					text: langs('Просмотр остатков по складам Аптек и РАС'),
					tooltip: langs('Просмотр остатков по складам Аптек и РАС'),
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
					}
				}]
			})
		},
		action_WhsDocumentUcInvent: {
			nn: 'action_WhsDocumentUcInvent',
			tooltip: langs('Инвентаризация'),
			text: langs('Инвентаризация'),
			iconCls : 'invent32',
			disabled: false,
            hidden: getGlobalOptions().region.nick == 'msk',
			menuAlign: 'tr?',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: langs('Приказы на проведение инвентаризации'),
					text: langs('Приказы на проведение инвентаризации'),
					iconCls : 'document16',
					handler: function() {
						getWnd('swWhsDocumentUcInventOrderViewWindow').show({
							ARMType: 'adminllo'
						});
					}
				}, {
					tooltip: langs('Инвентаризационные ведомости'),
					text: langs('Инвентаризационные ведомости'),
					iconCls : 'document16',
					disabled: false,
					handler: function() {
                        var wnd = getWnd('swWorkPlaceAdminLLOWindow');
						getWnd('swWhsDocumentUcInventViewWindow').show({
							ARMType: 'adminllo',
                            MedService_id: wnd.userMedStaffFact.MedService_id,
                            Lpu_id: wnd.userMedStaffFact.Lpu_id,
                            LpuSection_id: wnd.userMedStaffFact.LpuSection_id,
                            LpuBuilding_id: wnd.userMedStaffFact.LpuBuilding_id
						});
					}
				}, {
					text: 'Закрытие периода',
					tooltip: 'Закрытие периода',
					iconCls: 'document16',
					// Если это Уфа и ели Башфармация
					hidden: !(getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().org_id == '68320120724'),
					handler: function() {
						getWnd('swDrugPeriodCloseViewWindow').show();//{mode: 'suppliers'});
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
                    getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: 'adminllo'});
                }
            }
        },
        action_Export2dbf: {
            nn: 'action_Export2dbf',
            tooltip: langs('Выгрузка справочников в dbase (*.DBF)'),
            text: langs('Выгрузка справочников'),
            iconCls : 'report32',
            handler: function() {
                getWnd('swQueryToDbfExporterWindow').show();
            }
        },
	    action_ExportPL2dbf: {
            nn: 'action_Export2dbf',
            tooltip: 'Выгрузка файлов P и L в dbf',
            text: 'Выгрузка файлов P и L в dbf',
	    hidden:  !(getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().org_id == '68320120724'),
            iconCls : 'report32',
            handler: function() {
		    getWnd('swPLToDbfExporterWindow').show();
            }
        },
		action_Import: 
			{
				nn: 'action_Import',
				tooltip: 'Экстренный запуск функции импорта данных из БФ',
				text: 'Экстренный запуск функции импорта данных из БФ',
				hidden:  !(getGlobalOptions().region.nick == 'ufa' && getGlobalOptions().org_id == '68320120724'),
				iconCls : 'settings32',
				handler: function() {
					getWnd('swExtraImportDataBFWindow').show();
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
        }
    },
    doSearch: function(mode) {
        var params = this.FilterPanel.getForm().getValues(),
            btn = this.getPeriodToggle(mode);
        if (btn) {
            if (mode != 'range') {
                if (this.mode == mode) {
                    btn.toggle(true);
                    if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
                        return false;
                } else {
                    this.mode = mode;
                }
            } else {
                btn.toggle(true);
                this.mode = mode;
            }
        }
        params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
        params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
        params.limit = 50;
        params.start = 0;
        params.exclude_new_requests = 1; //флаг, установка которго запрещает отображение запросов со статусом "Новый"

        if (this.FilterPanel.getForm().findField('PersonPrivilegeReqStatus_id')) {
            params.PersonPrivilegeReqStatus_id = this.FilterPanel.getForm().findField('PersonPrivilegeReqStatus_id').getValue();
        }

        this.GridPanel.removeAll();
        this.GridPanel.loadData({globalFilters: params});
    },
    doReset: function() {
        var base_form = this.FilterPanel.getForm();
        base_form.reset();

        //установка значений по умолчанию
        if (base_form.findField('PersonPrivilegeReqStatus_id')) {
            base_form.findField('PersonPrivilegeReqStatus_id').setValue(2); //2 - На рассмотрении
        }
    },
    doExpertise: function() {
        var wnd = this;
        var selected_record = this.GridPanel.getGrid().getSelectionModel().getSelected();
        if (selected_record && selected_record.get('PersonPrivilegeReq_id')) {
            getWnd('swPersonPrivilegeReqExpertiseWindow').show({
                PersonPrivilegeReq_id: selected_record.get('PersonPrivilegeReq_id'),
                callback: function() {
                    wnd.GridPanel.refreshRecords(null, 0);
                }
            });
        }
    },
    showRegistryReceptListWindow: function() {
        var grid = this.GridPanel;
        var record = grid.getGrid().getSelectionModel().getSelected();
        if( !record ) return false;

        getWnd('swRegistryReceptListWindow').show({
            ReceptUploadLog_id: record.get('ReceptUploadLog_id')
        });
    },
    showImportReceptUploadWindow: function(action) {
        var grid = this.GridPanel;
        if( action != 'add' ) {
            var record = grid.getGrid().getSelectionModel().getSelected();
            if( !record ) return false;

            if (record.get('ReceptUploadType_id') == 2 || record.get('ReceptUploadType_id') == 3) {
                this.showRegistryReceptListWindow();
                return true;
            }
        }

        getWnd('swImportReceptUploadWindow').show({
            action: action,
            record: record || null,
            fromAdminLLO: true,
            callback: function(upload_result) {
                grid.ViewActions.action_refresh.execute();
                if(upload_result && upload_result.farmacy_import_msg && upload_result.farmacy_import_msg != '') {
                    sw.swMsg.alert(langs('Результат импорта'), upload_result.farmacy_import_msg);
                }
            }
        });
    },
    deleteReceptUploadLog: function() {
        var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
        if( !record ) return false;

        Ext.Msg.show({
            title: langs('Внимание'),
            scope: this,
            msg: langs('Вы действительно хотите удалить выбранную запись?'),
            buttons: Ext.Msg.YESNO,
            fn: function(btn) {
                if (btn === 'yes') {
                    this.getLoadMask(langs('Удаление записи')).show();
                    Ext.Ajax.request({
                        scope: this,
                        url: '/?c=ReceptUpload&m=deleteReceptUploadLog',
                        params: { ReceptUploadLog_id: record.get('ReceptUploadLog_id') },
                        callback: function(o, s, r) {
                            this.getLoadMask().hide();
                            this.GridPanel.ViewActions.action_refresh.execute();
                        }
                    });
                }
            },
            icon: Ext.MessageBox.QUESTION
        });
    },
    execAction: function(action, msg, cb, scope) {
        if( !action ) return false;

        var wnd = this,
            record = wnd.GridPanel.getGrid().getSelectionModel().getSelected();
        if( !record ) return false;

        wnd.getLoadMask(msg || '').show();
        Ext.Ajax.request({
            url: '/?c=ReceptUpload&m=' + action,
            params: record.data,
            scope: scope || this,
            callback: function(o, s, r) {
                wnd.getLoadMask().hide();
                if( s ) {
                    wnd.GridPanel.ViewActions.action_refresh.execute();
                    if( cb && Ext.isFunction(cb) ) {
                        cb.apply(this, arguments);
                    }
                }
            }
        });
    },
    showLink: function (resp_obj){
        sw.swMsg.alert('Завершено', 'Экспорт успешно завершен. <a href="'+resp_obj.filename+'" target="blank" title="Щелкните, чтобы сохранить результаты на локальный диск">Скачать</a>');
    },
    initComponent: function() {
        var form = this;
        var region_nick = getRegionNick();

        this.onKeyDown = function (inp, e) {
            if (e.getKey() == Ext.EventObject.ENTER) {
                e.stopEvent();
                this.doSearch();
            }
        }.createDelegate(this);

        if (region_nick != 'msk') {
            this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
                owner: form,
                filter: {
                    title: langs('Фильтр'),
                    layout: 'form',
                    items: [{
                        layout: 'column',
                        items: [{
                            layout: 'form',
                            labelWidth: 80,
                            items: [{
                                xtype: 'swcontragentcombo',
                                hiddenName:'Contragent_id',
                                listWidth: 300,
                                fieldLabel: langs('Поставщик')
                            }]
                        }, {
                            layout: 'form',
                            items: [{
                                fieldLabel: langs('Тип данных'),
                                xtype: 'swcommonsprcombo',
                                comboSubject: 'ReceptUploadType'
                            }]
                        }, {
                            layout: 'form',
                            style: 'margin-left: 10px;',
                            items: [{
                                xtype: 'button',
                                handler: this.doSearch,
                                scope: this,
                                iconCls: 'search16',
                                text: BTN_FRMSEARCH
                            }]
                        }, {
                            layout: 'form',
                            style: 'margin-left: 10px;',
                            items: [{
                                xtype: 'button',
                                iconCls: 'reset16',
                                handler: function() {
                                    this.FilterPanel.getForm().reset();
                                    this.doSearch();
                                },
                                scope: this,
                                text: BTN_FRMRESET
                            }]
                        }]
                    }]
                }
            });

            this.GridPanel = new sw.Promed.ViewFrame({
                id: this.id + '_Grid',
                region: 'center',
                autoExpandColumn: 'autoexpand',
                actions: [
                    { name:'action_add', /*text: langs('Импорт'), icon: 'img/icons/petition-report16.png',*/ handler: this.showImportReceptUploadWindow.createDelegate(this, ['add']) },
                    { name:'action_edit', disabled: true /*text: langs('Случаи'), icon: 'img/icons/doc-uch16.png'*/ },
                    { name:'action_view', handler: this.showImportReceptUploadWindow.createDelegate(this, ['view']) },
                    { name:'action_delete', handler: this.deleteReceptUploadLog.createDelegate(this) },
                    { name:'action_refresh' },
                    { name:'action_print', hidden: true }
                ],
                autoLoadData: false,
                paging: true,
                pageSize: 50,
                stringfields: [
                    // Поля для отображение в гриде
                    { name: 'ReceptUploadLog_id', type: 'int', header: langs('№'), width: 60, hidden: false, key: true },
                    { name: 'ReceptUploadLog_setDT', header: langs('Дата загрузки') },
                    { name: 'Contragent_id', hidden: true },
                    { name: 'ReceptUploadType_id', hidden: true },
                    { name: 'ReceptUploadStatus_id', hidden: true },
                    { name: 'ReceptUploadStatus_Code', hidden: true },
                    { name: 'Contragent_Name', header: langs('Поставщик'), id: 'autoexpand' },
                    { name: 'ReceptUploadType_Name', header: langs('Тип данных'), width: 120 },
                    { name: 'file_name', header: langs('Имя загруженного файла'), width: 180 },
                    { name: 'file_size', header: langs('Размер') },
                    { name: 'ReceptUploadStatus_Name', header: langs('Статус данных'), width: 200 },
                    { name: 'ReceptUploadLog_Act', header: langs('Ссылка на акт'), width: 120, renderer: function(v, p, r) {
                            return !Ext.isEmpty(v) && +r.get('isHisRecord') ? '<a href="'+v+'" target="_blank">скачать</a>' : '';
                        } },
                    { name: 'ReceptUploadLog_InFail', header: langs('Ссылка на файлы'), width: 120, renderer: function(v, p, r) {
                            return !Ext.isEmpty(v) && +r.get('isHisRecord') ? '<a href="'+v+'" target="_blank">скачать</a>' : '';
                        } },
                    { name: 'isHisRecord', hidden: true }
                ],
                editformclassname: '',
                dataUrl: '/?c=ReceptUpload&m=loadReceptUploadLogList',
                root: 'data',
                totalProperty: 'totalCount',
                //title: 'Журнал рабочего места',
                onRowSelect: function(sm, index, record) {
                    this.getAction('action_delete').setDisabled( !+record.get('isHisRecord') || !record.get('ReceptUploadStatus_Code').inlist([1]) );
                    this.getAction('import_and_exp').setDisabled( !+record.get('isHisRecord') );
                    this.getAction('send_act_about_import').setDisabled( !+record.get('isHisRecord') );
                },
                onLoadData: function() {
                    var view = this.getGrid().getView(),
                        store = this.getGrid().getStore(),
                        rows = view.getRows();
                    Ext.each(rows, function(row, idx) {
                        var record = store.getAt(idx);
                        if( !+record.get('isHisRecord') && !Ext.isEmpty(record.get('ReceptUploadLog_id')) ) {
                            new Ext.ToolTip({
                                html: langs('Данные были загружены на другой веб-сервер и не могут быть изменены или удалены!'),
                                target: Ext.get(row).id
                            });
                        }
                    });
                }
            });

            this.GridPanel.getGrid().view = new Ext.grid.GridView({
                getRowClass: function (row, index) {
                    var cls = '';
                    if ( !+row.get('isHisRecord') )
                        cls = cls+'x-grid-rowgray ';
                    return cls;
                }.createDelegate(this)
            });
        } else {
            this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
                labelWidth: 140,
                owner: form,
                filter: {
                    title: langs('Фильтр'),
                    layout: 'form',
                    items: [{
                        layout: 'column',
                        items: [{
                            layout: 'column',
                            items: [{
                                layout: 'form',
                                items: [{
                                    xtype: 'swlpucombo',
                                    fieldLabel: langs('МО'),
                                    name: 'Lpu_id',
                                    width: 200
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: langs('Отчество'),
                                    name: 'Person_SecName',
                                    width: 200
                                }, {
                                    xtype: 'swcommonsprcombo',
                                    comboSubject: 'PersonPrivilegeReqStatus',
                                    fieldLabel: 'Статус запроса',
                                    hiddenName: 'PersonPrivilegeReqStatus_id',
                                    width: 200,
                                    onLoadStore: function(store) {
                                        //исключаем лишний статус
                                        store.each(function(record) {
                                            if (record.get('PersonPrivilegeReqStatus_id') == 1) { //1 - Новый
                                                store.remove(record);
                                            }
                                        });
                                    }
                                }]
                            }, {
                                layout: 'form',
                                items: [{
                                    xtype: 'textfield',
                                    fieldLabel: langs('Фамилия'),
                                    name: 'Person_SurName',
                                    width: 200
                                }, {
                                    xtype: 'daterangefield',
                                    fieldLabel: langs('Дата рождения'),
                                    name: 'Person_BirthDay_Range',
                                    plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                                    width: 200
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: langs('Результат'),
                                    hiddenName: 'Result_Type',
                                    width: 200,
                                    displayField: 'name',
                                    valueField: 'code',
                                    editable: false,
                                    mode: 'local',
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    store: new Ext.data.SimpleStore({
                                        id: 0,
                                        fields: [
                                            'code',
                                            'name'
                                        ],
                                        data: [
                                            ['insert', langs('Включен')],
                                            ['reject', langs('Отказано')]
                                        ]
                                    }),
                                    tpl: new Ext.XTemplate(
                                        '<tpl for="."><div class="x-combo-list-item">',
                                        '{name}&nbsp;',
                                        '</div></tpl>'
                                    )
                                }]
                            }, {
                                layout: 'form',
                                items: [{
                                    xtype: 'textfield',
                                    fieldLabel: langs('Имя'),
                                    name: 'Person_FirName',
                                    width: 200
                                }, {
                                    xtype: 'swprivilegetypecombo',
                                    fieldLabel: langs('Льготная категория'),
                                    hiddenName: 'PrivilegeType_id',
                                    width: 200
                                }, {
                                    layout: 'column',
                                    items: [{
                                    layout: 'form',
                                        style: 'margin-left: 145px;',
                                        items: [{
                                            xtype: 'button',
                                            handler: this.doSearch,
                                            scope: this,
                                            iconCls: 'search16',
                                            text: BTN_FRMSEARCH
                                        }]
                                    }, {
                                        layout: 'form',
                                        style: 'margin-left: 10px;',
                                        items: [{
                                            xtype: 'button',
                                            iconCls: 'reset16',
                                            handler: function() {
                                                this.doReset();
                                                this.doSearch();
                                            },
                                            scope: this,
                                            text: BTN_FRMRESET
                                        }]
                                    }]
                                }]
                            }]
                        }]
                    }]
                }
            });

            this.GridPanel = new sw.Promed.ViewFrame({
                id: this.id + '_Grid',
                region: 'center',
				tbActions: true,
                actions: [
                    {name: 'action_add', handler: function() { form.GridPanel.addRecord(); } },
                    {name: 'action_edit'},
                    {name: 'action_view'},
                    {name: 'action_delete', url: '/?c=Privilege&m=deletePersonPrivilegeReq'},
                    {name: 'action_print'},
                    {name: 'sign_actions', key: 'sign_actions', text:langs('Подписать'), menu: [
                        new Ext.Action({
                            name: 'action_signPersonPrivilegeReq',
                            text: langs('Подписать запрос'),
                            tooltip: langs('Подписать запрос'),
                            handler: function() {
                                var me = this;
                                var rec = form.GridPanel.getGrid().getSelectionModel().getSelected();
                                if (rec && rec.get('PersonPrivilegeReq_id')) {
                                    getWnd('swEMDSignWindow').show({
                                        EMDRegistry_ObjectName: 'PersonPrivilegeReq',
                                        EMDRegistry_ObjectID: rec.get('PersonPrivilegeReq_id'),
                                        callback: function(data) {
                                            if (data.preloader) {
                                                me.disable();
                                            }

                                            if (data.success || data.error) {
                                                me.enable();
                                            }

                                            if (data.success) {
                                                win.getGrid().getStore().reload();
                                            }
                                        }
                                    });
                                }
                            }
                        }),
                        new Ext.Action({
                            name: 'action_showPersonPrivilegeReqVersionList',
                            text: langs('Версии документа «Запрос на включение в РРЛ»'),
                            tooltip: langs('Версии документа «Запрос на включение в РРЛ»'),
                            handler: function() {
                                var rec = form.GridPanel.getGrid().getSelectionModel().getSelected();
                                if (rec && rec.get('PersonPrivilegeReq_id')) {
                                    getWnd('swEMDVersionViewWindow').show({
                                        EMDRegistry_ObjectName: 'PersonPrivilegeReq',
                                        EMDRegistry_ObjectID: rec.get('PersonPrivilegeReq_id')
                                    });
                                }
                            }
                        }),
                        new Ext.Action({
                            name: 'action_signPersonPrivilegeReqAns',
                            text: langs('Подписать ответ'),
                            tooltip: langs('Подписать ответ'),
                            handler: function() {
                                var me = this;
                                var rec = form.GridPanel.getGrid().getSelectionModel().getSelected();
                                if (rec && rec.get('PersonPrivilegeReqAns_id')) {
                                    getWnd('swEMDSignWindow').show({
                                        EMDRegistry_ObjectName: 'PersonPrivilegeReqAns',
                                        EMDRegistry_ObjectID: rec.get('PersonPrivilegeReqAns_id'),
                                        callback: function(data) {
                                            if (data.preloader) {
                                                me.disable();
                                            }

                                            if (data.success || data.error) {
                                                me.enable();
                                            }

                                            if (data.success) {
                                                win.getGrid().getStore().reload();
                                            }
                                        }
                                    });
                                }
                            }
                        }),
                        new Ext.Action({
                            name: 'action_showPersonPrivilegeReqAnsVersionList',
                            text: langs('Версии документа «Ответ на запрос о включении в РРЛ»'),
                            tooltip: langs('Версии документа «Ответ на запрос о включении в РРЛ»'),
                            handler: function() {
                                var rec = form.GridPanel.getGrid().getSelectionModel().getSelected();
                                if (rec && rec.get('PersonPrivilegeReqAns_id')) {
                                    getWnd('swEMDVersionViewWindow').show({
                                        EMDRegistry_ObjectName: 'PersonPrivilegeReqAns',
                                        EMDRegistry_ObjectID: rec.get('PersonPrivilegeReqAns_id')
                                    });
                                }
                            }
                        })
                    ], tooltip: langs('Подписать'), iconCls : 'x-btn-text', icon: 'img/icons/digital-sign16.png', handler: function() {}}
                ],
                autoExpandColumn: 'autoexpand',
                autoExpandMin: 125,
                autoLoadData: false,
                border: true,
                dataUrl: '/?c=Privilege&m=loadPersonPrivilegeReqList',
                height: 180,
                object: 'PersonPrivilegeReq',
                editformclassname: 'swPersonPrivilegeReqEditWindow',
                paging: true,
                pageSize: 50,
                root: 'data',
                style: 'margin-bottom: 10px',
                stringfields: [
                    { name: 'PersonPrivilegeReq_id', type: 'int', header: 'ID', key: true },
                    { name: 'PersonPrivilegeReqAns_id', type: 'int', hidden: true },
                    { name: 'PersonPrivilegeReqStatus_id', type: 'int', hidden: true },
                    { name: 'PersonPrivilegeReq_setDT', type: 'string', header: langs('Дата и время'), width: 150 },
                    { name: 'Person_FullName', type: 'string', header: langs('ФИО, ДР'), width: 300  },
                    { name: 'PrivilegeType_Name', type: 'string', header: langs('Льготная категория'), width: 200 },
                    { name: 'MedStaffFact_FullName', type: 'string', header: langs('Заявитель'), id: 'autoexpand', width: 200 },
                    { name: 'PersonPrivilegeReqStatus_Name', type: 'string', header: langs('Статус'), width: 150 },
                    { name: 'Result_Data', type: 'string', header: langs('Результат'), width: 300 },
                    { name: 'Check_Snils', header: langs('Проверка СНИЛС'), width: 130, renderer: function (v, p, record) { return record.get('PersonPrivilegeReq_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } },
                    { name: 'Check_Registration', header: langs('Проверка регистрации'), width: 130, renderer: function (v, p, record) { return record.get('PersonPrivilegeReq_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } },
                    { name: 'Check_Polis', header: langs('Проверка полиса'), width: 130, renderer: function (v, p, record) { return record.get('PersonPrivilegeReq_id') > 0 ? sw.Promed.Format.checkColumn('true', p, record) : ''; } }
                ],
                title: null,
                toolbar: true,
                enableAudit: true,
                addRecord: function() {
                    var viewframe = this;
                    var params = new Object();

                    params.action = 'add';
                    params.callback = function() {
                        viewframe.refreshRecords(null, 0);
                    };
                    params.userMedStaffFact = form.userMedStaffFact;

                    getWnd('swPersonSearchWindow').show({
                        onHide: function() {
                            viewframe.focus(false);
                        },
                        onSelect: function(person_data) {
                            params.Person_id = person_data.Person_id;
                            getWnd(viewframe.editformclassname).show(params);
                            getWnd('swPersonSearchWindow').hide();
                        }
                    });
                },
                initEnabledActions: function() { //настройка доступности и видимости элементов панели управления списком
                    this.add_enabled = isUserGroup(['OperLLO', 'ChiefLLO', 'LpuUser', 'LpuAdmin']); //Оператор ЛЛО, Руководитель ЛЛО МО, Пользователь МО, Администратор МО

                    if (isUserGroup(['OperLLO', 'ChiefLLO', 'LpuUser', 'LpuAdmin'])) { //Оператор ЛЛО, Руководитель ЛЛО МО, Пользователь МО, Администратор МО
                        this.getAction('action_add').show();
                        this.getAction('action_edit').show();
                        this.getAction('action_delete').show();
                    } else {
                        this.getAction('action_add').hide();
                        this.getAction('action_edit').hide();
                        this.getAction('action_delete').hide();
                    }

                    if (haveArmType('adminllo')) { //АРМ Администратора ЛЛО (АРМ ситуационного центра ЛЛО)
                        this.getAction('action_expertise').show();
                    } else {
                        this.getAction('action_expertise').hide();
                    }
                },
                onRowSelect: function(sm, rowIdx, record) {
                    var status_id = record.get('PersonPrivilegeReqStatus_id');

                    if (record.get('PersonPrivilegeReq_id') > 0 && !this.readOnly) {
                        this.getAction('action_edit').setDisabled(status_id != 1); //1 - Новый
                        this.getAction('action_delete').setDisabled(status_id != 1); //1 - Новый
                        this.getAction('action_expertise').setDisabled(status_id != 2); //2 - На рассмотрнии
                    } else {
                        this.getAction('action_edit').setDisabled(true);
                        this.getAction('action_delete').setDisabled(true);
                        this.getAction('action_expertise').setDisabled(true);
                    }
                    this.getAction('action_add').setDisabled(!this.add_enabled || this.readOnly);
                    this.getAction('action_view').setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReq_id')));

                    this.getAction('sign_actions').items[0].menu.items.items[0].setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReq_id')));
                    this.getAction('sign_actions').items[0].menu.items.items[1].setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReq_id')));
                    this.getAction('sign_actions').items[0].menu.items.items[2].setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReqAns_id')) || record.get('PersonPrivilegeReqStatus_id') != 3);
                    this.getAction('sign_actions').items[0].menu.items.items[3].setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReqAns_id')) || record.get('PersonPrivilegeReqStatus_id') != 3);

                    this.getAction('sign_actions').items[1].menu.items.items[0].setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReq_id')));
                    this.getAction('sign_actions').items[1].menu.items.items[1].setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReq_id')));
                    this.getAction('sign_actions').items[1].menu.items.items[2].setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReqAns_id')) || record.get('PersonPrivilegeReqStatus_id') != 3);
                    this.getAction('sign_actions').items[1].menu.items.items[3].setDisabled(Ext.isEmpty(record.get('PersonPrivilegeReqAns_id')) || record.get('PersonPrivilegeReqStatus_id') != 3);
                },
                onDblClick: function() {
                    if (!this.getAction('action_expertise').isDisabled()) {
                        this.getAction('action_expertise').execute();
                    }
                }
            });
        }

        this.GridPanel.getGrid().getView().on('refresh', function(v) {
            //log('refresh');
        });

        sw.Promed.swWorkPlaceAdminLLOWindow.superclass.initComponent.apply(this, arguments);
    }
});

