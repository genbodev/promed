/**
* АРМ МЭК ЛЛО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Alexander Chebukin
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      декабрь.2012
*/
sw.Promed.swWorkPlaceMEKLLOWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, { //getWnd('swWorkPlaceMEKLLOWindow').show({ARMType: 'mekllo'});
	id: 'swWorkPlaceMEKLLOWindow',
	show: function() {
		sw.Promed.swWorkPlaceMEKLLOWindow.superclass.show.apply(this, arguments);
        this.userMedStaffFact = arguments[0];
		
		if( !this.GridPanel.getAction('send_act_about_import') ) {
			this.GridPanel.addActions({
				text: lang['peredat_akt_o_prieme_dannyih'],
				name: 'send_act_about_import',
				handler: function() {
					this.execAction('sendActAboutImport', lang['vyipolnyaetsya_formirovanie_akta']);
				},
				scope: this
			});
		}
		
		if( !this.GridPanel.getAction('import_and_exp') ) {
			this.GridPanel.addActions({
				text: lang['provesti_import_i_ekspertizu_dannyih'],
				name: 'import_and_exp',
				handler: function() {
					this.execAction('importAndExpertise', lang['vyipolnyaetsya_import_i_ekspertiza_dannyih']);
				},
				scope: this
			});
		}

		if(this.LeftPanel.actions.action_RLS){
		this.LeftPanel.actions.action_RLS.hide();
		}
		if(this.LeftPanel.actions.action_Mes){
		this.LeftPanel.actions.action_Mes.hide();
		}
		var b_f = this.FilterPanel.getForm();
		if( !b_f.findField('Contragent_id').getStore().getCount() ) {
			b_f.findField('Contragent_id').getStore().load({
				params: { ContragentType_id: 1 } // только организации
			});
		}
	},
	enableDefaultActions: false,
	buttonPanelActions: {
        action_Docs: {
            nn: 'action_Docs',
            tooltip: lang['dokumentyi'],
            text: lang['dokumentyi'],
            iconCls : 'card-state32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [{
                    tooltip: lang['lotyi'],
                    text: lang['lotyi'],
                    iconCls : 'settings16',
                    handler: function(){
                        getWnd('swUnitOfTradingViewWindow').show({
                            disableAdd: true,
                            disableEdit:true
                        });
                    }.createDelegate(this)
                }, {
                    // nn: 'action_SwhDocumentSupply',
                    tooltip: lang['goskontraktyi'],
                    text: lang['goskontraktyi'],
                    iconCls : 'card-state32',
                    disabled: false,
                    handler: function(){
                        getWnd('swWhsDocumentSupplyViewWindow').show();
                    }.createDelegate(this)
                }, {
                    text: lang['pravoustanavlivayuschie_dokumentyi'],
                    tooltip: lang['pravoustanavlivayuschie_dokumentyi'],
                    iconCls : 'document16',
                    handler: function() {
                        getWnd('swWhsDocumentTitleViewWindow').show();
                    }.createDelegate(this)
                }
                ]
            })
        },
        action_Action8: {
            nn: 'action_Action8',
            tooltip: lang['spravochnik_kontragentyi'],
            text: lang['spravochnik_kontragentyi'],
            iconCls : 'org32',
            disabled: false,
            handler: function() {
                getWnd('swContragentViewWindow').show({
					ARMType: getGlobalOptions().CurMedServiceType_SysNick
				});
            }.createDelegate(this)
        },
		action_Action2: {
			nn: 'action_Action2',
			tooltip: lang['zayavki_llo'],
			text: lang['zayavki_llo'],
			iconCls : 'mp-drugrequest32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [{
					text: lang['normativnyie_perechni_lekarstvennyih_sredstv'],
					tooltip: lang['spravochnik_normativnyih_perechney_lekarstvennyih_sredstv'],
					iconCls : 'drug-name16',
					handler: function() {
						getWnd('swDrugNormativeListViewWindow').show();
					}
				}, {
					text: lang['spiski_medikamentov_dlya_zayavki'],
					tooltip: lang['spiski_medikamentov_dlya_zayavki'],
					iconCls : 'pill16',
					handler: function() {
						getWnd('swDrugRequestPropertyViewWindow').show();
					}
				}]
			})
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
						getWnd('swEvnReceptSearchWindow').show();
					}
				}, {
					text: lang['jurnal_otsrochki'],
					tooltip: lang['jurnal_otsrochki'],
					iconCls : 'receipt-incorrect16',
					handler: function()	{
						getWnd('swReceptInCorrectSearchWindow').show();
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
						if ( !getWnd('swRlsViewForm').isVisible() )
							getWnd('swRlsViewForm').show();
					}
				}, {
					tooltip: lang['mkb-10'],
					text: lang['spravochnik_mkb-10'],
					iconCls: 'spr-mkb16',
					handler: function() {
						if ( !getWnd('swMkb10SearchWindow').isVisible() )
							getWnd('swMkb10SearchWindow').show();
					}
				}, {
					tooltip: lang['prosmotr'] + ' ' + getMESAlias(),
					text: lang['prosmotr'] + ' ' + getMESAlias(),
					iconCls: 'spr-mes16',
					handler: function() {
						if ( !getWnd('swMesOldSearchWindow').isVisible() )
							getWnd('swMesOldSearchWindow').show();
					}
				}, {
					tooltip: lang['spravochnik_kriteriev_ekspertizyi'],
					text: lang['spravochnik_kriteriev_ekspertizyi'],
					iconCls: '',
					handler: function() {
						if ( !getWnd('swRegistryReceptExpertiseTypeViewWindow').isVisible() )
							getWnd('swRegistryReceptExpertiseTypeViewWindow').show();
					}
				},
				sw.Promed.Actions.swDrugDocumentSprAction,
				{
					name: 'action_DrugNomenSpr',
					text: lang['nomenklaturnyiy_spravochnik'],
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugNomenSprWindow').show({readOnly: false});
					}
				}, {
					name: 'action_DrugMnnCodeSpr',
					text: lang['spravochnik_mnn'],
					iconCls : '',
					hidden: getRegionNick() != 'saratov',
					handler: function()
					{
						getWnd('swDrugMnnCodeViewWindow').show({readOnly: false});
					}
				}, {
					name: 'action_DrugTorgCodeSpr',
					text: lang['spravochnik_torgovyih_naimenovaniy'],
					iconCls : '',
					hidden: getRegionNick() != 'saratov',
					handler: function()
					{
						getWnd('swDrugTorgCodeViewWindow').show({readOnly: false});
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
					name: 'action_UslugaComplexTariffLlo',
					text: lang['tarifyi_llo'],
					iconCls : 'lpu-finans16',
					handler: function() {
						getWnd('swUslugaComplexTariffLloViewWindow').show();
					}
				},
				sw.Promed.Actions.swPrepBlockSprAction
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
			tooltip: lang['rpn_poisk']
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
						getWnd('swLgotTreeViewWindow').show();
					}
				}, {
					text: MM_DLO_LGOTSEARCH,
					tooltip: lang['poisk_lgotnikov'],
					iconCls : 'lgot-search16',
					handler: function() {
						getWnd('swPrivilegeSearchWindow').show();
					}
				},
				'-',
				{
					text: MM_DLO_UDOSTLIST,
					tooltip: lang['prosmotr_udostovereniy'],
					iconCls : 'udost-list16',
					handler: function() {
						getWnd('swUdostViewWindow').show();
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
					tooltip: lang['lgotnyie_registryi_poisk'],
					text: lang['lgotnyie_registryi_poisk'],
					iconCls: 'doc-reg16',
					disabled: false,
					hidden:(getRegionNick() == 'perm'),
					handler: function() {
						if ( getWnd('swPrivilegeSearchWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swPrivilegeSearchWindow').show();
					}
				}, {
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
						getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
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
						getWnd('swOnkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					tooltip: lang['registr_bolnyih_venericheskim_zabolevaniem'],
					text: lang['registr_bolnyih_venericheskim_zabolevaniem'],
					iconCls : 'doc-reg16',
					disabled: false,
					handler: function() {
						getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				sw.Promed.personRegister.getOrphanBtnConfig('swWorkPlaceMEKLLOWindow', null),
				sw.Promed.personRegister.getVznBtnConfig('swWorkPlaceMEKLLOWindow', null),
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
						getWnd('swDiabetesRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
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
						getWnd('swLargeFamilyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
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
		action_MedOstat: {
			nn: 'action_MedOstat',
			tooltip: lang['ostatki_medikamentov'],
			text: lang['ostatki_medikamentov'],
			iconCls : 'rls-torg32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: lang['po_aptekam'],
					text: lang['po_aptekam'],
					iconCls : 'drug-farm16',
					handler: function() {
						getWnd('swDrugOstatByFarmacyViewWindow').show();
					}
				}, {
					tooltip: lang['po_naimenovaniyu'],
					text: lang['po_naimenovaniyu'],
					iconCls : 'drug-name16',
					handler: function() {
						getWnd('swDrugOstatViewWindow').show();
					}
				}, {
					tooltip: lang['po_kontragentam'],
					text: lang['po_kontragentam'],
					iconCls : 'drug-sklad16',
					handler: function(){
						//(в разрезах ЛС; программ ЛЛО и источников финансирования; по системе в целом/территориям/выбранному складу системы)
						getWnd('swMedOstatSearchWindow').show();
					}.createDelegate(this)
				}]
			})

		},
		action_SvodRegistry: {
			nn: 'action_SvodRegistry',
			tooltip: lang['oplata_reestrov_receptov'],
			text: lang['oplata_reestrov_receptov'],
			iconCls : 'recept-search32',
			disabled: false,
			hidden: true, //getRegionNick() != 'saratov',
			handler: function(){
				getWnd('swSvodRegistryViewWindow').show();
			}.createDelegate(this)
		},
		action_DrugOstatRegistryList: {
			nn: 'action_DrugOstatRegistryList',
			tooltip: lang['prosmotr_registra_ostatkov'],
			text: lang['prosmotr_registra_ostatkov'],
			iconCls : 'pers-cards32',
			menuAlign: 'tr?',
			menu: new Ext.menu.Menu({
				items: [{
					text: lang['prosmotr_ostatkov_organizatsii_polzovatelya'],
					tooltip: lang['prosmotr_ostatkov_organizatsii_polzovatelya'],
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({
                            mode: 'suppliers',
                            userMedStaffFact: this.userMedStaffFact
                        });
					}.createDelegate(this)
				}, {
					text: lang['prosmotr_ostatkov_po_skladam_aptek_i_ras'],
					tooltip: lang['prosmotr_ostatkov_po_skladam_aptek_i_ras'],
					iconCls: 'pill16',
					handler: function() {
						getWnd('swDrugOstatRegistryListWindow').show({mode: 'farmacy_and_store'});
					}
				}]
			})
		},
		action_OrgFarmacyByLpuView: {
			nn: 'action_OrgFarmacyByLpuView',
			tooltip: lang['prikreplenie_aptek_k_mo'],
			text: lang['prikreplenie_aptek_k_mo'],
			iconCls : 'therapy-plan32',
			disabled: false,
			handler: function(){
				getWnd('swOrgFarmacyByLpuViewWindow').show();
			}
		},
		/*action_Reports: {
			nn: 'action_Reports',
			tooltip: lang['otchetyi'],
			text: lang['otchetyi'],
			iconCls : 'report32',
			disabled: false, 
			menuAlign: 'tr?',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: lang['formirovanie'],
					text: lang['formirovanie'],
					iconCls : 'settings16',
					disabled: true,
					handler: function() {}
						/*,
						menu: new Ext.menu.Menu({
							items: [{
								text: lang['lichnaya_kartochka_lgotnika_patsienta'],
								tooltip: lang['lichnaya_kartochka_lgotnika_patsienta'],
								iconCls : 'receipt-incorrect16',
								handler: function() {
									//getWnd('swWindow').show();
								}
							}]
						})
				}, {
					tooltip: lang['prosmotr'],
					text: lang['prosmotr'],
					iconCls: 'search16',
					handler: function() {
						if (sw.codeInfo.loadEngineReports) {
							getWnd('swReportEndUserWindow').show();
						} else {
							getWnd('reports').load({
								callback: function(success) {
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку 
									getWnd('swReportEndUserWindow').show();
								}
							});
						}
					}
				}]
			})
		},*/
        action_RegistryLLO: {
            handler: function() {
                getWnd('swRegistryLLOViewWindow').show({
                    ARMType: sw.Promed.MedStaffFactByUser.current.MedServiceType_SysNick
                });
            },
            hidden: ( !getRegionNick().inlist(['saratov']) ),
            iconCls: 'service-reestrs16',
            nn: 'action_RegistryLLO',
            text: lang['oplata_reestrov_receptov'],
            tooltip: lang['oplata_reestrov_receptov']
        },
		action_Export2dbf: {
			nn: 'action_Export2dbf',
			tooltip: lang['vyigruzka_spravochnikov_v_dbase_*_dbf'],
			text: lang['vyigruzka_spravochnikov'],
			iconCls : 'report32',
            handler: function() {
                getWnd('swQueryToDbfExporterWindow').show();
            }
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
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 50;
		params.start = 0;
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
		var record = grid.getGrid().getSelectionModel().getSelected();

		if (!Ext.isEmpty(record.get('RegistryLLO_id'))) {
			return true;
		}

		if( action != 'add' ) {
			if( !record ) {
				return false;
			}
			
			if (record.get('ReceptUploadType_id') == 2 || record.get('ReceptUploadType_id') == 3) {
				this.showRegistryReceptListWindow();
				return true;
			}
		}

		getWnd('swImportReceptUploadWindow').show({
			action: action,
			record: record || null,
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

		var url = '/?c=ReceptUpload&m=' + action;
		var params = new Object();

		params = Ext.apply(params, record.data);

		if (!Ext.isEmpty(record.get('RegistryLLO_id')) && action == 'importAndExpertise') {
			url = '/?c=RegistryLLO&m=setRegistryStatus';
			params.RegistryStatus_Code = 2; //2 - К оплате
		}
		
		wnd.getLoadMask(msg || '').show();
		Ext.Ajax.request({
			url: url,
			params: params,
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
			filter: {
				title: lang['filtr'],
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
							fieldLabel: lang['postavschik']
						}]
					}, {
						layout: 'form',
						items: [{
							fieldLabel: lang['tip_dannyih'],
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
				{ name:'action_add', /*text: lang['import'], icon: 'img/icons/petition-report16.png',*/ handler: this.showImportReceptUploadWindow.createDelegate(this, ['add']) },
				{ name:'action_edit', disabled: true /*text: lang['sluchai'], icon: 'img/icons/doc-uch16.png'*/ },
				{ name:'action_view', handler: this.showImportReceptUploadWindow.createDelegate(this, ['view']) },
				{ name:'action_delete', handler: this.deleteReceptUploadLog.createDelegate(this) },
				{ name:'action_refresh' },
				{ name:'action_print' }
			],
			autoLoadData: false,
			paging: true,
			pageSize: 50,
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'ReceptUploadLog_id', type: 'int', header: lang['№'], width: 60, hidden: false, key: true },
				{ name: 'ReceptUploadLog_setDT', header: lang['data_zagruzki'] },
				{ name: 'Contragent_id', hidden: true },
				{ name: 'ReceptUploadType_id', hidden: true },
				{ name: 'ReceptUploadStatus_id', hidden: true },
				{ name: 'ReceptUploadStatus_Code', hidden: true },
				{ name: 'Contragent_Name', header: lang['postavschik'], id: 'autoexpand' },
				{ name: 'ReceptUploadType_Name', header: lang['tip_dannyih'], width: 120 },
				{ name: 'file_name', header: lang['imya_zagrujennogo_fayla'], width: 180 },
				{ name: 'file_size', header: lang['razmer'] },
				{ name: 'ReceptUploadStatus_Name', header: lang['status_dannyih'], width: 200 },
				{ name: 'ReceptUploadLog_Act', header: lang['ssyilka_na_akt'], width: 120, renderer: function(v, p, r) {
					return !Ext.isEmpty(v) && +r.get('isHisRecord') ? '<a href="'+v+'" target="_blank">скачать</a>' : '';
				} },
				{ name: 'ReceptUploadLog_InFail', header: lang['ssyilka_na_faylyi'], width: 120, renderer: function(v, p, r) {
					return !Ext.isEmpty(v) && +r.get('isHisRecord') ? '<a href="'+v+'" target="_blank">скачать</a>' : '';
				} },
				{ name: 'isHisRecord', hidden: true },
				{ name: 'RegistryLLO_id', hidden: true }
			],
			// params: {ARMType: getGlobalOptions().OrgFarmacy_id == 1 ? 'storehouse' : 'undefined'},
			editformclassname: '',
			dataUrl: '/?c=ReceptUpload&m=loadReceptUploadLogList',
			root: 'data',
			totalProperty: 'totalCount',
			//title: 'Журнал рабочего места',
			onRowSelect: function(sm, index, record) {
				this.getAction('action_delete').setDisabled( !+record.get('isHisRecord') || !record.get('ReceptUploadStatus_Code').inlist([1]) || Ext.isEmpty(record.get('RegistryLLO_id')) );
				this.getAction('import_and_exp').setDisabled( !+record.get('isHisRecord') && (Ext.isEmpty(record.get('RegistryLLO_id')) || record.get('ReceptUploadStatus_Code') == 4) );
				this.getAction('send_act_about_import').setDisabled( !+record.get('isHisRecord') );
			},
			onLoadData: function() {
				var view = this.getGrid().getView(),
					store = this.getGrid().getStore(),
					rows = view.getRows();
				Ext.each(rows, function(row, idx) {
					var record = store.getAt(idx);
					if( !+record.get('isHisRecord') && !Ext.isEmpty(record.get('ReceptUploadLog_id')) && Ext.isEmpty(record.get('RegistryLLO_id')) ) {
						new Ext.ToolTip({
							html: lang['dannyie_byili_zagrujenyi_na_drugoy_veb-server_i_ne_mogut_byit_izmenenyi_ili_udalenyi'],
							target: Ext.get(row).id
						});
					}
				});
			}
		});
		
		this.GridPanel.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if ( !+row.get('isHisRecord') && Ext.isEmpty(row.get('RegistryLLO_id')) )
					cls = cls+'x-grid-rowgray ';
				return cls;
			}.createDelegate(this)
		});
		
		this.GridPanel.getGrid().getView().on('refresh', function(v) {
			//log('refresh');
		});
		
		sw.Promed.swWorkPlaceMEKLLOWindow.superclass.initComponent.apply(this, arguments);
	}
});