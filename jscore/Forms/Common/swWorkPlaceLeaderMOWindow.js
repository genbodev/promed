/**
* АРМ руководителя МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Salakhov R.
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      11.2012
*/
sw.Promed.swWorkPlaceLeaderMOWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, { //getWnd('swWorkPlaceLeaderMOWindow').show({ARMType: 'minzdravdlo'});
	id: 'swWorkPlaceLeaderMOWindow',
	show: function() {
		sw.Promed.swWorkPlaceLeaderMOWindow.superclass.show.apply(this, arguments);
        this.userMedStaffFact = arguments[0];

		//пока основной грид не содержит никакой информации, заблокируем его редактирование
		this.GridPanel.setReadOnly(true);
	},
	buttonPanelActions: {
                        action_VolPlan: {
				iconCls : 'monitoring32',
				nn: 'action_VolPlan',
				text: 'Планирование объёмов',
				tooltip: 'Планирование объёмов',
				hidden: (getRegionNick() != 'ufa'),
                                menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						{
							text: 'Периоды фактических объёмов',
							tooltip: 'Периоды фактических объёмов',
							iconCls: 'datepicker-day16',
							handler: function()
							{
                                                            getWnd('swVolPeriodViewWindow').show();
							},
							hidden: true
						},
						{
							text: 'Расчёт фактических объёмов',
							tooltip: 'Расчёт фактических объёмов',
							iconCls: 'farm-inv16',
							handler: function()
							{
                                                            getWnd('swVolPlanCalcWindow').show();
							},
							hidden: true
						},
                                                {
							text: 'Заявки МО',
							tooltip: 'Заявки МО',
							iconCls: 'pol-eplstream16',
							handler: function()
							{
                                                            var params = {};
                                                            params.functionality = 'boss';
                                                            getWnd('swVolRequestViewWindow').show(params);
							},
							hidden: false
						}
					]
				})
			},
		action_Action1: {
			nn: 'action_Action1',
			tooltip: lang['zayavki_llo'],
			text: lang['zayavki_llo'],
			iconCls : 'mp-drugrequest32',
			disabled: false,
			menuAlign: 'tr',
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: lang['zayavka_llo'],
					text: lang['zayavka_llo'],
					iconCls : 'view16',
					handler: function() {
						getWnd('swMzDrugRequestSelectWindow').show({
							ARMType: "leadermo"
						});
					}
				}, {
					text: lang['plan_potrebleniya_mo'],
					tooltip: lang['plan_potrebleniya_mo'],
					iconCls : 'pill16',
					handler: function() {
						getWnd('swDrugRequestPlanDeliveryViewWindow').show();
					}
				}]
			})
		},
		action_Action2: {
			nn: 'action_Action2',
			tooltip: lang['spravochniki'],
			text: lang['spravochniki'],
			iconCls : 'org32',
			disabled: false, 			
			menu: new Ext.menu.Menu({
				items: [{
					tooltip: lang['spravochnik_kontragentov'],
					text: lang['spravochnik_kontragentov'],
					iconCls : 'view16',
					handler: function(){
						getWnd('swContragentViewWindow').show();
					}.createDelegate(this)
				}, {
					tooltip: lang['spravochnik_rabochih_periodov'],
					text: lang['rabochie_periodyi'],
					iconCls : 'view16',
					handler: function(){
						getWnd('swDrugRequestPeriodViewWindow').show({onlyView: true});
					}.createDelegate(this)
				},
				sw.Promed.Actions.swDrugDocumentSprAction,
				{
					name: 'action_DrugNomenSpr',
					text: lang['nomenklaturnyiy_spravochnik'],
					iconCls : '',
					handler: function()
					{
						getWnd('swDrugNomenSprWindow').show();
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
				}]
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
		action_RPNPrikr: {
			nn: 'action_RPNPrikr',
			tooltip: lang['rpn_prikreplenie'],
			text: lang['rpn_prikreplenie'],
			iconCls : 'card-view32',
			handler: function() {
				getWnd('swPersonCardViewAllWindow').show();
			}
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
				items: [ {
					tooltip: lang['registr_po_virusnomu_gepatitu'],
					text: lang['registr_po_virusnomu_gepatitu'],
					hidden: ( getRegionNick() == 'saratov' ),
					iconCls: 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('HepatitisRegistry', 0) < 0),
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
					hidden: ( getRegionNick() == 'saratov' ),
					iconCls: 'doc-reg16',
					disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0),
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
					tooltip: lang['registr_po_psihiatrii'],
					text: lang['registr_po_psihiatrii'],
					iconCls: 'doc-reg16',
					hidden: ( getRegionNick() == 'saratov' ),
					disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
					handler: function() {
						getWnd('swCrazyRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					tooltip: lang['registr_po_narkologii'],
					text: lang['registr_po_narkologii'],
					iconCls: 'doc-reg16',
					hidden: ( getRegionNick() == 'saratov' ),
					disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
					handler: function() {
						getWnd('swNarkoRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					tooltip: lang['registr_bolnyih_tuberkulezom'],
					text: lang['registr_po_tuberkuleznyim_zabolevaniyam'],
					iconCls: 'doc-reg16',
					hidden: ( getRegionNick() == 'saratov' ),
					disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
					handler: function() {
						getWnd('swTubRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					tooltip: lang['registr_bolnyih_venericheskim_zabolevaniem'],
					text: lang['registr_bolnyih_venericheskim_zabolevaniem'],
					iconCls : 'doc-reg16',
					hidden: ( getRegionNick() == 'saratov' ),
					disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
					handler: function()
					{
						getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				}, {
					tooltip: lang['registr_vich-infitsirovannyih'],
					text: lang['registr_vich-infitsirovannyih'],
					iconCls: 'doc-reg16',
					hidden: ( getRegionNick() == 'saratov' ),
					disabled: !allowHIVRegistry(),
					handler: function() {
						getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
				sw.Promed.personRegister.getOrphanBtnConfig('swWorkPlaceLeaderMOWindow', null),
				sw.Promed.personRegister.getVznBtnConfig('swWorkPlaceLeaderMOWindow', null),
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
                            userMedStaffFact: getWnd('swWorkPlaceLeaderMOWindow').userMedStaffFact
                        });
                    }
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
		action_WhsDocumentOrderAllocationSearchWindow: {
			nn: 'action_WhsDocumentOrderAllocationSearchWindow',
			tooltip: lang['prosmotr_raznaryadki'],
			text: lang['prosmotr_raznaryadki'],
			iconCls : 'document32',
			disabled: false,
			handler: function(){
				getWnd('swWhsDocumentOrderAllocationSearchWindow').show({mode: 'limitedRights'});
			}.createDelegate(this)
		},
        action_WhsDocumentSupply: {
            nn: 'action_WhsDocumentSupply',
            tooltip: lang['dopolnitelnyie_soglasheniya'],
            text: lang['dopolnitelnyie_soglasheniya'],
            iconCls : 'card-state32',
            handler: function(){
                getWnd('swWhsDocumentSupplyAdditionalViewWindow').show({ARMType: 'leadermo'});
            }
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
                    tooltip: lang['prosmotr'] + getMESAlias(),
                    text: lang['prosmotr'] + getMESAlias(),
                    iconCls: 'spr-mes16',
                    handler: function() {
                        if ( !getWnd('swMesOldSearchWindow').isVisible() )
                            getWnd('swMesOldSearchWindow').show();
                    }
                },
				sw.Promed.Actions.swPrepBlockSprAction,
				sw.Promed.Actions.swDrugDocumentSprAction
				]
            })
        },
		action_OrgFarmacyByLpuView: {
			nn: 'action_OrgFarmacyByLpuView',
			tooltip: lang['prikreplenie_aptek_k_mo'],
			text: lang['prikreplenie_aptek_k_mo'],
			iconCls : 'therapy-plan32',
			disabled: false,
			handler: function(){
                if (getRegionNick().inlist(['perm', 'ufa'])) {
                    getWnd('swOrgFarmacyByLpuViewWindow').show();
                } else {
                    getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: 'leadermo'});
                }
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
		},
		action_EMD: {
			nn: 'action_EMD',
			hidden: getRegionNick() != 'msk',
			iconCls : 'remd32',
			text: 'Подписание медицинской документации',
			tooltip: 'Подписание медицинской документации',
			handler: function() {
				getWnd('swEMDSearchUnsignedWindow').show({
					armType: 'leadermo'
				});
			}
		}
	},
	doSearch: function(mode) {
		/*var params = this.FilterPanel.getForm().getValues();
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
			} else {
				btn.toggle(true);
				this.mode = mode;
			}
		}		
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 100;
		params.start = 0;
		this.GridPanel.removeAll();
		this.GridPanel.loadData({globalFilters: params});*/
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
					labelWidth: 129,
					items: [{
						layout: 'form',
						items: [{
							/*xtype: 'swcontragentcombo',
							disabled: false,
							width: 380,
							id: 'wplmContragent_sid',
							name: 'Contragent_sid',
							hiddenName:'Contragent_sid',
							fieldLabel: lang['postavschik'],
							listeners: {'keydown': form.onKeyDown}*/
						}]
					}]
				}]
			}
		});
		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'wplmWorkPlaceGridPanel',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})',
			groupingView: {showGroupName: false, showGroupsText: true},
			actions: [
				{name:'action_add', handler: function() { /*this.SelectCreateTypeWindow.show();*/ }.createDelegate(this)},
				{name:'action_edit'},
				{name:'action_view', disabled: true},
				{name:'action_delete'},
				{name:'action_refresh', disabled: true, handler: function() {}.createDelegate(this)},
				{name:'action_print'}
			],
			autoLoadData: false,
			paging: false,
			pageSize: 100,
			stringfields: [
				// Поля для отображение в гриде
				{name: 'DocumentUc_id', type: 'int', header: 'ID', key: true},
				{name: 'DocumentUc_Num', header: lang['data'], width: 100}/*,
				{name: 'DocumentUc_setDate', header: lang['data_podpisaniya'], type: 'date', width: 100},
				{name: 'DocumentUc_txtdidDate', header: lang['data_postavki'], type: 'date', width: 100},
				{name: 'Contragent_sName', header: lang['postavschik'], width: 260},
				{id: 'autoexpand', name: 'Contragent_tName', header: lang['potrebitel'], width: 120},
				{name: 'DocumentUc_SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'}*/
			],
			object: 'DocumentUc',
			params: {ARMType: getGlobalOptions().OrgFarmacy_id == 1 ? 'storehouse' : 'undefined'},
			editformclassname: 'swDocumentUcEditWindow',			
			dataUrl: '/?c=Farmacy&m=load&method=DokUcLpu',			
			root: 'data',
			totalProperty: 'totalCount',
			title: lang['jurnal_rabochego_mesta'],
			onRowSelect: function(sm, index, record) {
				//this.getAction('action_delete').setDisabled( !record.get('canEdit') );
			},
			onLoadData: function(sm, index, record) {
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
			}
		});
		sw.Promed.swWorkPlaceLeaderMOWindow.superclass.initComponent.apply(this, arguments);
	}
});