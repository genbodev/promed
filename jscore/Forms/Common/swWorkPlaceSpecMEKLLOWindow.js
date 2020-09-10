/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 18.09.14
 * Time: 17:02
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swWorkPlaceSpecMEKLLOWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
    id: 'swWorkPlaceSpecMEKLLOWindow',
    show: function() {
        var wnd = this;

        var allowed_regions = ['pskov', 'khak', 'saratov', 'buryatiya', 'krym', 'ekb']; //в остальных регионах для использования АРМ-а нужно предварительно подготовить структуру таблиц, иначе полезу ошибки в запросах
        if (!getRegionNick().inlist(allowed_regions)) {
            sw.swMsg.alert(lang['oshibka'], 'АРМ не доступен в данном регионе');
            this.hide();
            return true;
        }

        sw.Promed.swWorkPlaceSpecMEKLLOWindow.superclass.show.apply(this, arguments);
        this.userMedStaffFact = arguments[0];

        if(this.LeftPanel.actions.action_RLS){
            this.LeftPanel.actions.action_RLS.hide();
        }
        if(this.LeftPanel.actions.action_Mes){
            this.LeftPanel.actions.action_Mes.hide();
        }

        if(!this.GridPanel.getAction('action_wpsml_actions')) {
            this.GridPanel.addActions({
                name:'action_wpsml_actions',
                text:lang['deystviya'],
                iconCls: 'actions16',
                menu: [{
                    name: 'action_wpsml_set_expertise_result',
                    text: lang['ruchnoy_vvod_rezultatov_ekspertizyi'],
                    hidden: !IS_DEBUG && !Ext.globalOptions.others.demo_server,
                    iconCls: 'actions16',
                    handler: function() {
                        wnd.setExpertiseResult();
                    }
                }]
            });
        }

        if (!this.GridPanel.getAction('action_rlv_expertise')) {
            this.GridPanel.addActions({
                name: 'action_wpsml_expertise',
                text: lang['ekspertiza'],
                iconCls: 'actions16',
                handler: function() {
                    wnd.doExpertise();
                }
            });
        }
    },
    enableDefaultActions: false,
    buttonPanelActions: {
        action_LLO: {
            nn: 'action_LLO',
            tooltip: lang['zayavki_llo'],
            text: lang['zayavki_llo'],
            iconCls : 'mp-drugrequest32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [{
                    text: lang['normativnyie_spiski'],
                    tooltip: lang['normativnyie_spiski'],
                    iconCls : 'drug-name16',
                    handler: function() {
                        getWnd('swDrugNormativeListViewWindow').show({onlyView: true});
                    }
                }, {
                    text: lang['spiski_medikamentov_dlya_zayavki'],
                    tooltip: lang['spiski_medikamentov_dlya_zayavki'],
                    iconCls : 'pill16',
                    handler: function() {
                        getWnd('swDrugRequestPropertyViewWindow').show({onlyView: true, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }
                }]
            })
        },
        action_WhsDocument: {
            nn: 'action_WhsDocument',
            tooltip: lang['goskontraktyi'],
            text: lang['goskontraktyi'],
            iconCls : 'card-state32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: lang['goskontraktyi_na_postavku'],
                    text: lang['goskontraktyi_na_postavku'],
                    iconCls : 'document16',
                    handler: function(){
                        getWnd('swWhsDocumentSupplyViewWindow').show({onlyView: true});
                    }
                }, {
                    tooltip: lang['dopolnitelnyie_soglasheniya'],
                    text: lang['dopolnitelnyie_soglasheniya'],
                    iconCls : 'document16',
                    handler: function(){
                        getWnd('swWhsDocumentSupplyAdditionalViewWindow').show({onlyView: true, ARMType: 'spesexpertllo'});
                    }
                }]
            })
        },
        action_Documents: {
            nn: 'action_Documents',
            tooltip: lang['dokumentyi'],
            text: lang['dokumentyi'],
            iconCls : 'document32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [{
                    text: lang['pravoustanavlivayuschie_dokumentyi'],
                    tooltip: lang['pravoustanavlivayuschie_dokumentyi'],
                    iconCls : 'document16',
                    handler: function() {
                        getWnd('swWhsDocumentTitleViewWindow').show({onlyView: true});
                    }.createDelegate(this)
                }, {
                    text: lang['raznaryadki_na_vyipisku_retseptov'],
                    tooltip: lang['raznaryadki_na_vyipisku_retseptov'],
                    iconCls : 'drug-name16',
                    menu: new Ext.menu.Menu({
                        items: [{
                            tooltip: lang['rasporyajeniya_na_vyidachu_raznaryadok'],
                            text: lang['rasporyajeniya_na_vyidachu_raznaryadok'],
                            iconCls : 'pill16',
                            handler: function(){
                                getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 7, onlyView: true});
                            }.createDelegate(this)
                        }, {
                            text: lang['prosmotr_raznaryadki'],
                            tooltip: lang['prosmotr_raznaryadki_na_vyipisku_retsptov'],
                            iconCls : 'pill16',
                            handler: function() {
                                getWnd('swWhsDocumentOrderAllocationSearchWindow').show({onlyView: true});
                            }
                        }]
                    })
                }]
            })
        },
        action_Contragents: {
            nn: 'action_Contragents',
            tooltip: lang['spravochnik_kontragentyi'],
            text: lang['spravochnik_kontragentyi'],
            iconCls : 'org32',
            disabled: false,
            handler: function() {
                getWnd('swContragentViewWindow').show({
                    ARMType: getGlobalOptions().CurMedServiceType_SysNick,
                    onlyView: true
                });
            }.createDelegate(this)
        },
        action_Recept: {
            nn: 'action_Register',
            tooltip: lang['retseptyi'],
            text: lang['retseptyi'],
            iconCls : 'recept-search32',
            disabled: false,
            menuAlign: 'tr?',
            menu: new Ext.menu.Menu({
                items: [{
                    text: lang['poisk_retseptov'],
                    tooltip: lang['poisk_retseptov'],
                    iconCls: 'receipt-search16',
                    handler: function() {
                        getWnd('swEvnReceptSearchWindow').show({onlyView: true});
                    }
                }, {
                    text: lang['jurnal_otsrochki'],
                    tooltip: lang['jurnal_otsrochki'],
                    iconCls : 'receipt-incorrect16',
                    handler: function()	{
                        getWnd('swReceptInCorrectSearchWindow').show({onlyView: true});
                    }
                }]
            })
        },
        action_References: {
            nn: 'action_References',
            tooltip: lang['spravochniki'],
            text: lang['spravochniki'],
            iconCls : 'book32',
            disabled: false,
            menuAlign: 'tr?',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: lang['prosmotr_rls'],
                    text: lang['prosmotr_rls'],
                    iconCls: 'rls16',
                    handler: function() {
                        if (!getWnd('swRlsViewForm').isVisible()) {
                            getWnd('swRlsViewForm').show({onlyView: true});
                        }
                    }
                }, {
                    tooltip: lang['mkb-10'],
                    text: lang['spravochnik_mkb-10'],
                    iconCls: 'spr-mkb16',
                    handler: function() {
                        if (!getWnd('swMkb10SearchWindow').isVisible()) {
                            getWnd('swMkb10SearchWindow').show({onlyView: true});
                        }
                    }
                },
				sw.Promed.Actions.swDrugDocumentSprAction,
				{
                    name: 'action_DrugNomenSpr',
                    text: lang['nomenklaturnyiy_spravochnik'],
                    iconCls : '',
                    handler: function() {
                        getWnd('swDrugNomenSprWindow').show({onlyView: true});
                    }
                }, {
                    name: 'action_PriceJNVLP',
                    text: lang['tsenyi_na_jnvlp'],
                    iconCls : 'dlo16',
                    handler: function() {
                        getWnd('swJNVLPPriceViewWindow').show();
                    }
                }, {
                    name: 'action_DrugMarkup',
                    text: lang['predelnyie_nadbavki_na_jnvlp'],
                    iconCls : 'lpu-finans16',
                    handler: function() {
                        getWnd('swDrugMarkupViewWindow').show();
                    }
                }, {
                    text: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
                    tooltip: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
                    handler: function() {
                        getWnd('swPrepBlockViewWindow').show();
                    }
                }]
            })
        },
        action_accessibility: {
            menuAlign: 'tr',
            text: lang['lgotniki'],
            tooltip: lang['lgotniki'],
            iconCls: 'lgot32',
            menu: new Ext.menu.Menu({
                items: [{
                    text: lang['registr_lgotnikov_spisok'],
                    tooltip: lang['prosmotr_lgot_po_kategoriyam'],
                    iconCls : 'lgot-tree16',
                    handler: function() {
                        getWnd('swLgotTreeViewWindow').show({onlyView: true, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }
                }, {
                    text: MM_DLO_LGOTSEARCH,
                    tooltip: lang['poisk_lgotnikov'],
                    iconCls : 'lgot-search16',
                    handler: function() {
                        getWnd('swPrivilegeSearchWindow').show({onlyView: true, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }
                },
                '-',
                {
                    text: MM_DLO_UDOSTLIST,
                    tooltip: lang['udostovereniya_lgotnikov_poisk'],
                    iconCls : 'udost-list16',
                    handler: function() {
                        getWnd('swUdostViewWindow').show({onlyView: true, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }
                }]
            })
        },
        action_Register: {
            nn: 'action_Register',
            tooltip: lang['registryi'],
            text: lang['registryi'],
            iconCls : 'registry32',
            disabled: false,
            menuAlign: 'tr?',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: lang['registr_po_virusnomu_gepatitu'],
                    text: lang['registr_po_virusnomu_gepatitu'],
                    iconCls : 'doc-reg16',
                    disabled: false,
                    handler: function() {
                        if ( getWnd('swHepatitisRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, onlyView: true, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }.createDelegate(this)
                }, {
                    tooltip: lang['registr_po_onkologii'],
                    text: lang['registr_po_onkologii'],
                    iconCls : 'doc-reg16',
                    disabled: false,
                    handler: function() {
                        if ( getWnd('swOnkoRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }.createDelegate(this)
                }, {
                    tooltip: lang['registr_bolnyih_venericheskim_zabolevaniem'],
                    text: lang['registr_bolnyih_venericheskim_zabolevaniem'],
                    iconCls : 'doc-reg16',
                    disabled: false,
                    handler: function() {
                        getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }.createDelegate(this)
				},
				sw.Promed.personRegister.getOrphanBtnConfig('swWorkPlaceSpecMEKLLOWindow', null),
				sw.Promed.personRegister.getVznBtnConfig('swWorkPlaceSpecMEKLLOWindow', null),
				{
                    tooltip: lang['registr_po_saharnomu_diabetu'],
                    text: lang['registr_po_saharnomu_diabetu'],
                    iconCls : 'doc-reg16',
                    hidden: !getRegionNick().inlist([ 'pskov','khak','saratov','buryatiya' ]),
                    handler: function()
                    {
                        if ( getWnd('swDiabetesRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swDiabetesRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }.createDelegate(this)
                }, {
                    tooltip: lang['registr_po_detyam_iz_mnogodetnyih_semey'],
                    text: lang['registr_po_detyam_iz_mnogodetnyih_semey'],
                    iconCls : 'doc-reg16',
                    hidden: !getRegionNick().inlist([ 'pskov', 'saratov' ]),
                    handler: function()
                    {
                        if ( getWnd('swLargeFamilyRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
                                title: ERR_WND_TIT
                            });
                            return false;
                        }
                        getWnd('swLargeFamilyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact, ARMType: getGlobalOptions().CurMedServiceType_SysNick});
                    }.createDelegate(this)
                }, {
                    tooltip: lang['registr_fmba'],
                    text: lang['registr_fmba'],
                    iconCls : 'doc-reg16',
                    hidden: (!getRegionNick().inlist([ 'saratov' ]) && !isFmbaUser()),
                    handler: function()
                    {
                        if ( getWnd('swFmbaRegistryWindow').isVisible() ) {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: Ext.emptyFn,
                                icon: Ext.Msg.WARNING,
                                msg: lang['okno_uje_otkryito'],
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
			tooltip: lang['prosmotr_ostatkov'],
			text: lang['prosmotr_ostatkov'],
			iconCls : 'pers-cards32',
            handler: function() {
                getWnd('swDrugOstatRegistryListWindow').show({
                    mode: 'simple',
                    disabledSearchOnReset: true
                });
            }
		},
        action_RegistryLLO: {
            handler: function() {
                getWnd('swRegistryLLOViewWindow').show({
                    ARMType: sw.Promed.MedStaffFactByUser.current.MedServiceType_SysNick
                });
            },
            hidden: ( !getRegionNick().inlist([/*'ufa',*/ 'khak', 'saratov', 'krym', 'ekb']) ),
            iconCls: 'service-reestrs16',
            nn: 'action_RegistryLLO',
            text: lang['oplata_reestrov_receptov'],
            tooltip: lang['oplata_reestrov_receptov']
        },
        action_JourNotice: {
            handler: function() {
                getWnd('swMessagesViewWindow').show();
            }.createDelegate(this),
            iconCls: 'notice32',
            nn: 'action_JourNotice',
            text: lang['jurnal_uvedomleniy'],
            tooltip: lang['jurnal_uvedomleniy']
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
        params.ReceptUploadLog_setDT_range = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y') + ' - ' + Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
        params.limit = 50;
        params.start = 0;
        params.RegistryStatus_Code = this.GridPanel.RegistryStatus_Code;

        this.GridPanel.removeAll();
        this.GridPanel.loadData({globalFilters: params});
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
            fromSpecMEKLLO: true,
            callback: function(upload_result) {
                grid.ViewActions.action_refresh.execute();
                if(upload_result && upload_result.farmacy_import_msg && upload_result.farmacy_import_msg != '') {
                    sw.swMsg.alert(lang['rezultat_importa'], upload_result.farmacy_import_msg);
                }
            }
        });
    },

    deleteReceptUploadLog: function() {
        var record = this.GridPanel.getGrid().getSelectionModel().getSelected();
        if( !record ) return false;

        Ext.Msg.show({
            title: lang['vnimanie'],
            scope: this,
            msg: lang['vyi_deystvitelno_hotite_udalit_vyibrannuyu_zapis'],
            buttons: Ext.Msg.YESNO,
            fn: function(btn) {
                if (btn === 'yes') {
                    this.getLoadMask(lang['udalenie_zapisi']).show();
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
    doExpertise: function(options) {
		options = options || {};

        var grid = this.GridPanel.getGrid();
        var record = grid.getSelectionModel().getSelected();

        if (!record || Ext.isEmpty(record.get('RegistryLLO_id'))) {
            return false;
        }

        this.getLoadMask('Выполнение экспертизы...').show();

        Ext.Ajax.request({
            url: '/?c=RegistryLLO&m=expertise',
            params: {RegistryLLO_id: record.get('RegistryLLO_id')},
            success: function() {
                this.getLoadMask().hide();
                this.GridPanel.getAction('action_refresh').execute();
				if (typeof options.callback == 'function') {
					options.callback();
				}
            }.createDelegate(this),
            failure: function() {
                this.getLoadMask().hide();
            }.createDelegate(this)
        });
    },
    setExpertiseResult: function() {
        var grid = this.GridPanel.getGrid();
        var record = grid.getSelectionModel().getSelected();

        if (!record || Ext.isEmpty(record.get('RegistryLLO_id'))) {
            return false;
        }

        var openForm = function() {
			var params = {};
			params.formParams = {
				RegistryLLO_id: record.get('RegistryLLO_id')
			};
			params.callback = function() {
				this.GridPanel.getAction('action_refresh').execute();
			}.createDelegate(this);

			getWnd('swRegistryLLOExpertiseWindow').show(params);
		}.createDelegate(this);

		if (record.get('ReceptUploadStatus_Code').inlist([3,4,5])) {
			openForm();
		} else {
			this.doExpertise({callback: openForm});
		}
    },
    createExpertiseAct: function() {
        return false;
    },
    initComponent: function() {
        var form = this;

        this.onKeyDown = function (inp, e) {
            if (e.getKey() == Ext.EventObject.ENTER) {
                e.stopEvent();
                this.doSearch();
            }
        }.createDelegate(this);

        this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
            owner: form,
            labelAlign: 'right',
            labelWidth: 180,
            filter: {
                title: lang['filtr'],
                layout: 'form',
                items: [{
                    layout: 'form',
                    items: [{
                        xtype: 'sworgcomboex',
                        fieldLabel : lang['organizatsiya'],
                        hiddenName: 'Org_id',
                        id: 'rlv_Org_id',
                        width: 300,
                        editable: true,
                        allowBlank: true,
                        tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
                        emptyText: lang['vvedite_chast_nazvaniya'],
                        onTriggerClick: function() {
                            if (this.disabled) {
                                return false;
                            }
                            var combo = this;

                            if (!this.formList) {
                                this.formList = new sw.Promed.swListSearchWindow({
                                    title: lang['poisk_organizatsii'],
                                    id: 'OrgSearch_' + this.id,
                                    object: 'Org',
                                    prefix: 'lsswdse1',
                                    editformclassname: 'swOrgEditWindow',
                                    stringfields: [
                                        {name: 'Org_id',    type:'int'},
                                        {name: 'Org_Name',  type:'string'}
                                    ],
                                    dataUrl: C_ORG_LIST
                                });
                            }
                            this.formList.show({
                                params: this.getStore().baseParams,
                                onSelect: function(data) {
                                    form.setOrgValueByData(combo, data);
                                }
                            });
                        }
                    }]
                }, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            xtype: 'numberfield',
                            fieldLabel: lang['summa_po_retseptam_rub_ot'],
                            name: 'MinSum',
                            allowNegative: false
                        }]
                    }, {
                        layout: 'form',
                        labelWidth: 37,
                        items: [{
                            xtype: 'numberfield',
                            fieldLabel: lang['do'],
                            name: 'MaxSum',
                            allowNegative: false
                        }]
                    }]
                }, {
                    xtype: 'swdrugfinancecombo',
                    fieldLabel: lang['finansirovanie'],
                    name: 'DrugFinance_id',
                    width: 300
                }, {
                    xtype: 'swwhsdocumentcostitemtypecombo',
                    fieldLabel: lang['programma_llo'],
                    name: 'WhsDocumentCostItemType_id',
                    width: 300
                }, {
                    xtype: 'swkatnaselcombo',
                    fieldLabel: lang['kategoriya_naseleniya'],
                    name: 'KatNasel_id',
                    width: 300
                }, {
                    fieldLabel: lang['status_reestra'],
                    xtype: 'swcommonsprcombo',
                    comboSubject: 'ReceptUploadStatus',
                    name: 'ReceptUploadStatus_id',
                    width: 300
                }, {
                    layout: 'column',
                    items: [{
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
            actions: [
                {name: 'action_add', disabled: true, hidden: true},
                {name: 'action_edit', disabled: true, hidden: true},
                {name: 'action_view'},
                {name: 'action_delete', disabled: true, hidden: true},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 125,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=RegistryLLO&m=loadList',
            height: 180,
            object: 'RegistryLLO',
            editformclassname: 'swRegistryDataReceptViewWindow',
            paging: true,
            pageSize: 50,
            root: 'data',
            totalProperty: 'totalCount',
            style: 'margin-bottom: 10px',
            title: null,
            toolbar: true,
            RegistryStatus_Code: 3, //3 - В работе
            stringfields: [
                { name: 'RegistryLLO_id', type: 'int', header: 'ID', key: true },
				{ name: 'ReceptUploadStatus_Code', type: 'int', hidden: true },
                { name: 'ReceptUploadLog_Data', type: 'string', header: lang['ekspertiza'], width: 140 },
                { name: 'ReceptUploadStatus_Name', type: 'string', header: lang['status_ekspertizyi'], width: 140 },
                { name: 'ReceptUploadLog_Act', header: lang['akt'], width: 140, renderer: function(v, p, r) {
                    return !Ext.isEmpty(v) ? '<a href="'+v+'" target="_blank">скачать</a>' : '';
                }},
                { name: 'Org_Name', type: 'string', header: lang['organizatsiya'], width: 140 },
                { name: 'RegistryLLO_Num', type: 'string', header: lang['nomer'], width: 100 },
                { name: 'RegistryLLO_accDate', type: 'date', header: lang['data'], width: 100 },
                { name: 'RegistryLLO_Period', type: 'string', header: lang['period'], width: 140, isparams: true },
                { name: 'KatNasel_Name', type: 'string', header: lang['kategoriya_naseleniya'], width: 140 },
                { name: 'DrugFinance_Name', type: 'string', header: lang['finansirovanie'], width: 140 },
                { name: 'WhsDocumentCostItemType_Name', type: 'string', header: lang['programma_llo'], width: 140 },
                { name: 'WhsDocumentUc_Num', type: 'string', header: lang['kontrakt'], width: 140, isparams: true },
                { name: 'Registry_Count', type: 'int', header: lang['kolichestvo_retseptov'], width: 140 },
                { name: 'Registry_ErrorCount', type: 'int', header: lang['kolichestvo_oshibok'], width: 140 },
                { name: 'RegistryLLO_Sum', type: 'money', align: 'right', header: lang['summa_po_lp_rub'], width: 140 },
                { name: 'Registry_Sum2', type: 'money', align: 'right', header: lang['summa_po_usluge_rub'], width: 140 },
                { name: 'FinDocument_id', hidden: true },
                { name: 'DrugFinance_id', hidden: true, isparams: true },
                { name: 'WhsDocumentCostItemType_id', hidden: true, isparams: true },
                { name: 'SupplierContragent_id', hidden: true, isparams: true },
                { name: 'Org_id', hidden: true, isparams: true }
            ],
            onRowSelect: function(sm,rowIdx,record) {
                if (record.get('RegistryLLO_id') > 0) {
                    this.ViewActions.action_edit.setDisabled(false);
                    this.ViewActions.action_view.setDisabled(false);
                    this.ViewActions.action_delete.setDisabled(false);
                    this.ViewActions.action_wpsml_expertise.setDisabled(false);
                } else {
                    this.ViewActions.action_edit.setDisabled(true);
                    this.ViewActions.action_view.setDisabled(true);
                    this.ViewActions.action_delete.setDisabled(true);
					this.ViewActions.action_wpsml_expertise.setDisabled(true);
                }
            },
            onLoadData: function() {
                this.DataState = 'loaded';
            }
        });

        sw.Promed.swWorkPlaceSpecMEKLLOWindow.superclass.initComponent.apply(this, arguments);
    }
});
