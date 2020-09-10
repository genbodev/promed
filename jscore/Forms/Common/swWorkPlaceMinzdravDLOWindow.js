/**
* АРМ специалиста по ЛЛО МЗ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      май.2012
*/
sw.Promed.swWorkPlaceMinzdravDLOWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, { //getWnd('swWorkPlaceMinzdravDLOWindow').show({ARMType: 'minzdravdlo'});
	id: 'swWorkPlaceMinzdravDLOWindow',
	show: function() {
		sw.Promed.swWorkPlaceMinzdravDLOWindow.superclass.show.apply(this, arguments);
        this.userMedStaffFact = arguments[0];
		
		//пока основной грид не содержит никакой информации, заблокируем его редактирование
		this.GridPanel.setReadOnly(true);
		if(this.LeftPanel.actions.action_RLS){
			this.LeftPanel.actions.action_RLS.hide();
		}
		if(this.LeftPanel.actions.action_Mes){
			this.LeftPanel.actions.action_Mes.hide();
		}
	},
	enableDefaultActions: false,
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
		this.buttonPanelActions= {
			action_OpenTTMSO: {
				iconCls: 'schedule32',
				nn: 'action_EditSchedule',
				text: lang['vedenie_raspisaniya'],
				tooltip: lang['vedenie_raspisaniya'],
				handler: function()
				{
					var MedService_id = form.userMedStaffFact.MedService_id;
					var MedService_Name = form.userMedStaffFact.MedService_Name;
					getWnd('swTTMSOScheduleEditWindow').show({
						MedService_id : MedService_id,
						MedService_Name : MedService_Name,
						readOnly: false
					});
				}.createDelegate(this)
			},
			action_RecordLpu: {
				handler: function() {
					getWnd('swOrgDirectionMasterWindow').show({TimetableData:form.userMedStaffFact});
				},
				iconCls : 'record-new32',
				nn: 'action_RecordLpu',
				text: lang['zapis_na_zaschitu_v_mz'],
				tooltip: lang['zapis_na_zaschitu_v_mz']
			},
			action_OpenEmkAction: {
				text: lang['otkryit_emk'],
				tooltip: lang['nayti_cheloveka_otkryit_ego_emk'],
				iconCls: 'patient-search32',
				hidden: (getRegionNick() != 'perm'),
				handler: function()
				{
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
			action_MedOstat: {
				nn: 'action_MedOstat',
				tooltip: lang['ostatki_medikamentov'],
				text: lang['ostatki_medikamentov'],
				iconCls : 'rls-torg32',
				disabled: false,
				hidden: getRegionNick() != 'perm', //(!getGlobalOptions().superadmin),
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
			action_Action2: {
				nn: 'action_Action2',
				tooltip: lang['zayavki_llo'],
				text: lang['zayavki_llo'],
				iconCls : 'mp-drugrequest32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: lang['rabochie_periodyi'],
						text: lang['rabochie_periodyi'],
						iconCls : 'datepicker-day16',
						handler: function(){
							getWnd('swDrugRequestPeriodViewWindow').show();
						}.createDelegate(this)
					}, {
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
					}, {
						text: lang['zayavochnyie_kampanii'],
						tooltip: lang['zayavochnyie_kampanii'],
						iconCls : 'pers-curehist16',
						handler: function() {
							getWnd('swMzDrugRequestRegionViewWindow').show({ARMType: form.userMedStaffFact.ARMType});
						}
					}, {
						text: lang['svodnaya_zayavka'],
						tooltip: lang['svodnaya_zayavka_prosmotr_utverjdenie'],
						iconCls : 'otd-profile16',
						handler: function() {
							getWnd('swConsolidatedDrugRequestViewWindow').show();
						}
					}, {
						tooltip: lang['formirovanie_lotov'],
						text: lang['formirovanie_lotov'],
						iconCls : 'settings16',					
						handler: function(){
							getWnd('swUnitOfTradingViewWindow').show({
	                            disableAdd: false,
	                            disableEdit:true,
	                            enableDelete: true,
	                            enableCopy: true,
	                            enableForm: true,
	                            enableReform: true,
	                            enableMerge: true,
	                            enableAddDrug: true,
	                            enableDelDrug: true,
	                            enableDnD: true
	                        });
						}.createDelegate(this)
					},
					{
						tooltip: lang['medikamentyi_zakuplennyie_po_zayavke'],
						text: lang['medikamentyi_zakuplennyie_po_zayavke'],
						iconCls : 'mp-drugrequest16',
						hidden: getRegionNick() != 'perm',
						handler: function(){
							getWnd('swDrugStateSearchWindow').show();
						}.createDelegate(this)
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
			action_DrugRequestRecept: {
				nn: 'action_DrugRequestRecept',
				tooltip: lang['svodnaya_zayavka_i_raznaryadka'],
				text: lang['svodnaya_zayavka_i_raznaryadka'],
				iconCls : 'mp-drugrequest32',
				disabled: false,
				menuAlign: 'tr',
				hidden: getRegionNick() != 'perm',
				handler: function(){
					getWnd('swDrugRequestReceptConsolidatedViewWindow').show();
				}.createDelegate(this)
			},
			/*action_Action3: {
				nn: 'action_Action3',
				tooltip: lang['analiz_potrebnosti_v_ls_na_period'],
				text: lang['analiz_potrebnosti_v_ls_na_period3'],
				iconCls : 'mp-drugrequest32',
				disabled: false, 
				handler: function(){
				}.createDelegate(this)
			},*/
			/*action_Action4: {
				nn: 'action_Action4',
				tooltip: lang['prognoz_rashoda_ls'],
				text: lang['prognoz_rashoda_ls'],
				iconCls : 'mp-drugrequest32',
				disabled: false, 
				handler: function(){
				}.createDelegate(this)
			},*/
			action_SwhDocumentSupply: {
				nn: 'action_SwhDocumentSupply',
				tooltip: lang['goskontraktyi'],
				text: lang['goskontraktyi'],
				iconCls : 'card-state32',
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: lang['goskontraktyi_na_postavku'],
						text: lang['goskontraktyi_na_postavku'],
						iconCls : 'document16',
						handler: function(){
							getWnd('swWhsDocumentSupplyViewWindow').show({ARMType: this.userMedStaffFact.ARMType});
						}.createDelegate(this)
					}, {
						tooltip: lang['dopolnitelnyie_soglasheniya'],
						text: lang['dopolnitelnyie_soglasheniya'],
						iconCls : 'document16',
						handler: function(){
							getWnd('swWhsDocumentSupplyAdditionalViewWindow').show({ARMType: this.userMedStaffFact.ARMType});
						}.createDelegate(this)
					}]
				})
			},
			action_MainSpecialist: {
				nn: 'action_HeadMedSpec',
				tooltip: 'Регистр главных внештатных врачей-специалистов при МЗ',
				text: 'Регистр главных внештатных врачей-специалистов при МЗ',
				iconCls : 'registry32',
				disabled: false,
				handler: function(){
					getWnd('swHeadMedSpecRegisterWindow').show();
				}
			},
			action_DrugOstatRegistryList: {
				nn: 'action_DrugOstatRegistryList',
				tooltip: lang['prosmotr_registra_ostatkov'],
				text: lang['prosmotr_registra_ostatkov'],
				iconCls : 'pers-cards32',
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [{
						text: lang['prosmotr_ostatkov_po_gk'],
						tooltip: lang['prosmotr_ostatkov_po_gk'],
						iconCls: 'pill16',
						handler: function() {
							getWnd('swDrugOstatRegistryListWindow').show();
						}
					}, {
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
			/*action_Action7: {
				nn: 'action_Action7',
				tooltip: lang['vyigruzka_na_sayt_roszdravnadzora'],
				text: lang['vyigruzka_na_sayt_roszdravnadzora'],
				iconCls : 'mp-drugrequest32',
				disabled: false, 
				handler: function(){
				}.createDelegate(this)
			},*/
			action_Action8: {			
				nn: 'action_Action8',
				tooltip: lang['spravochnik_kontragentyi'],
				text: lang['spravochnik_kontragentyi'],
				iconCls : 'org32',
				disabled: false, 			
				handler: function() {
					getWnd('swContragentViewWindow').show({
						ARMType: this.userMedStaffFact.ARMType
					});
				}.createDelegate(this)
			},
			action_Action11: {
				nn: 'action_Action11',
				tooltip: lang['dokumentyi'],
				text: lang['dokumentyi'],
				iconCls : 'document32',
				disabled: false, 
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: lang['po_rezervu'],
						text: lang['po_rezervu'],
						iconCls : 'datepicker-day16',
						handler: function(){
							getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 12});
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
									getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 7});
								}.createDelegate(this)
							}, {
								tooltip: 'Распоряжение на ввод остатков по разнарядке',
								text: 'Распоряжение на ввод остатков по разнарядке',
								iconCls : 'pill16',
								handler: function(){
									getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 23});
								}.createDelegate(this)
							}/*, {
								text: lang['rasporyajeniya_na_otzyiv_ls_iz_raznaryadok'],
								tooltip: lang['rasporyajeniya_na_otzyiv_ls_iz_raznaryadok'],
								iconCls : 'pill16',
								handler: function() {
									getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 8});
								}
							}*/, {
								text: lang['prosmotr_raznaryadki'],
								tooltip: lang['prosmotr_raznaryadki_na_vyipisku_retsptov'],
								iconCls : 'pill16',
								handler: function() {
									getWnd('swWhsDocumentOrderAllocationSearchWindow').show();
								}
							}]
						})
					}, {
						text: lang['planyi_postavok'],
						tooltip: lang['planyi_postavok'],
						iconCls : 'plan16',
						handler: function() {
							getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 15});
						}
					}, {
						text: lang['raspredelenie_ls_po_aptekam'],
						tooltip: lang['raspredelenie_ls_po_aptekam'],
						iconCls : 'pill16',
						handler: function() {
							getWnd('swMinzdravDLODocumentsWindow').show({WhsDocumentType_id: 17});
						}
					}, {
						text: lang['pravoustanavlivayuschie_dokumentyi'],
						tooltip: lang['pravoustanavlivayuschie_dokumentyi'],
						iconCls : 'document16',
						handler: function() {
							getWnd('swWhsDocumentTitleViewWindow').show();
						}
					}, {
						text: lang['kommercheskie_predlojeniya'],
						tooltip: lang['kommercheskie_predlojeniya'],
						iconCls : 'document16',
						handler: function() {
							getWnd('swCommercialOfferViewWindow').show();
						}
					}]
				})
			},
			/*action_Action9: {
				nn: 'action_Action9',
				tooltip: lang['formulyaryi'],
				text: lang['formulyaryi'],
				iconCls : 'mp-drugrequest32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						text: lang['jnvls'],
						tooltip: lang['jnvls_federalnyiy'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['lgotnyiy_perechen_regionalnyiy'],
						tooltip: lang['lgotnyiy_perechen_regionalnyiy_dlya_sistemyi_llo'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['federalnyiy'],
						tooltip: lang['federalnyiy_dlya_sistemyi_llo'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['onkologiya'],
						tooltip: lang['onkologiya_dlya_sistemyi_llo'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['psihiatriya'],
						tooltip: lang['psihiatriya_dlya_sistemyi_llo'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['7_nozologiy'],
						tooltip: lang['7_nozologiy_dlya_sistemyi_llo'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['regionalnyiy'],
						tooltip: lang['regionalnyiy_dlya_sistemyi_llo'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['formulyar_mu'],
						tooltip: lang['formulyar_mu'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['rls'],
						tooltip: lang['rls'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}]
				})
			},*/
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
					}, {
						text: lang['svodnyie_reestryi_retseptov'],
						tooltip: lang['svodnyie_reestryi_retseptov'],
						iconCls : 'receipt-search16',
						hidden: getRegionNick() != 'saratov',
						handler: function()	{
							getWnd('swSvodRegistryViewWindow').show();
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
					if (getRegionNick().inlist(['perm', 'ufa'])) {
                        getWnd('swOrgFarmacyByLpuViewWindow').show();
					} else {
                        getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: form.userMedStaffFact.ARMType});
					}
				}
			},
			action_Notify: {
                nn: 'action_Notify',
                tooltip: 'Извещения/Направления',
                text: 'Извещения/Направления',
                iconCls : 'doc-notify32',
                disabled: false,
                menuAlign: 'tr?',
                menu: new Ext.menu.Menu({
                    items: [
                    {
                        tooltip: lang['jurnal_izvescheniy_napravleniy_po_vzn'],
                        text: lang['jurnal_izvescheniy_napravleniy_po_vzn'],
                        iconCls : 'journal16',
                        handler: function() {
                            if ( getWnd('swEvnNotifyRegisterNolosListWindow').isVisible() ) {
                                getWnd('swEvnNotifyRegisterNolosListWindow').hide();
                            }
                            getWnd('swEvnNotifyRegisterNolosListWindow').show({userMedStaffFact: new Object(), fromARM: 'minzdravdlo'});
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
                            getWnd('swEvnNotifyRegisterOrphanListWindow').show({userMedStaffFact: new Object(), fromARM: 'minzdravdlo'});
                        }
                    }
                    ]
                })
            },
			action_Register: {
				nn: 'action_Register',
				tooltip: lang['registryi_patsientov'],
				text: lang['registryi_patsientov'],
				iconCls : 'registry32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [/*{
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
					},*/ 
					{
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
					}, 
					{
						tooltip: lang['registr_po_onkologii'],
						text: lang['registr_po_onkologii'],
						iconCls : 'doc-reg16',
						disabled: false,
						hidden:(getRegionNick() == 'perm'),
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
					}, 
					{
						tooltip: lang['registr_bolnyih_venericheskim_zabolevaniem'],
						text: lang['registr_bolnyih_venericheskim_zabolevaniem'],
						iconCls : 'doc-reg16',
						disabled: false,
						hidden:(getRegionNick() == 'perm'),
						handler: function() {
							getWnd('swVenerRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						}.createDelegate(this)
					}, 
					{
							tooltip: lang['registr_vich-infitsirovannyih'],
							text: lang['registr_vich-infitsirovannyih'],
							iconCls : 'doc-reg16',
							disabled: false,
							handler: function()
							{
								getWnd('swHIVRegistryWindow').show({userMedStaffFact: this.userMedStaffFact,fromARM: 'minzdravdlo'});
							}.createDelegate(this)
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
							getWnd('swPersonRegisterOrphanListWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister', fromARM: 'minzdravdlo'});
						}.createDelegate(this)
					},
					{
						tooltip: lang['registr_po_vzn'],
						text: lang['registr_po_vzn'],
						iconCls : 'doc-reg16',
						handler: function() {
							if ( getWnd('swPersonRegisterNolosListWindow').isVisible() ) {
								getWnd('swPersonRegisterNolosListWindow').hide();
							}
							getWnd('swPersonRegisterNolosListWindow').show({userMedStaffFact: this.userMedStaffFact, editType: 'onlyRegister', fromARM: 'minzdravdlo'});
						}.createDelegate(this)
					},
					{
						tooltip: lang['registr_po_psihiatrii'],
						text: lang['registr_po_psihiatrii'],
						iconCls : 'doc-reg16',
						handler: function()
						{
							getWnd('swCrazyRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister',fromARM: 'minzdravdlo'});
						}.createDelegate(this)
					},
					{
						tooltip: lang['registr_po_narkologii'],
						text: lang['registr_po_narkologii'],
						iconCls : 'doc-reg16',
						handler: function()
						{
							getWnd('swNarkoRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister',fromARM: 'minzdravdlo'});
						}.createDelegate(this)
					},
					{
						tooltip: lang['registr_bolnyih_tuberkulezom'],
						text: lang['registr_po_tuberkuleznyim_zabolevaniyam'],
						iconCls : 'doc-reg16',
						handler: function()
						{
							getWnd('swTubRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister',fromARM: 'minzdravdlo'});
						}.createDelegate(this)
					},
					{
						tooltip: lang['registr_po_profzabolevaniyam'],
		                text: lang['registr_po_profzabolevaniyam'],
		                iconCls : 'doc-reg16',
		                handler: function(){
							getWnd('swProfRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister',fromARM: 'minzdravdlo'});
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
		                    getWnd('swNephroRegistryWindow').show({editType: 'onlyRegister',fromARM: 'minzdravdlo'});
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
						hidden: !getRegionNick().inlist([ 'pskov', 'saratov']),
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
	                }*/					
					]
				})
			},		
			/*action_Action10: {
				nn: 'action_Action10',
				tooltip: lang['registryi_dlya_sistemyi_llo'],
				text: lang['registryi_dlya_sistemyi_llo'],
				iconCls : 'mp-drugrequest32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						text: lang['lekarstvennyie_sredstva'],
						tooltip: lang['lekarstvennyie_sredstva_jnvls_rls_lgotnyie_perechni'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['registr_lgotnikov'],
						tooltip: lang['registr_lgotnikov_fed_reg_7_nozologiy'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}, {
						text: lang['poisk_po_dannyim_pf'],
						tooltip: lang['poisk_po_dannyim_pf'],
						iconCls : 'receipt-incorrect16',
						handler: function() {
							//getWnd('swWindow').show();
						}
					}]
				})
			},*/
			action_References: {
				nn: 'action_References',
				tooltip: lang['spravochniki'],
				text: lang['spravochniki'],
				iconCls : 'book32',
				disabled: false, 
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [/*{
						tooltip: lang['falsifikatyi'],
						text: lang['falsifikatyi'],
						iconCls: 'staff16',
						handler: function() {}
					}, */{
						tooltip: getRLSTitle(),
						text: getRLSTitle(),
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
						handler: function()
						{
							getWnd('swDrugMnnCodeViewWindow').show({readOnly: false});
						}
					}, {
						name: 'action_DrugTorgCodeSpr',
						text: lang['spravochnik_torgovyih_naimenovaniy'],
						iconCls : '',
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
						name: 'action_DrugRMZ',
						text: lang['spravochnik_rzn'],
						iconCls : 'view16',
						handler: function() {
							getWnd('swDrugRMZViewWindow').show();
						}
					}, {
						name: 'action_UslugaComplexTariffLlo',
						text: lang['tarifyi_llo'],
						iconCls : 'lpu-finans16',
						handler: function() {
							getWnd('swUslugaComplexTariffLloViewWindow').show();
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
			},
			action_Export: {
				nn: 'action_Export',
				tooltip: lang['vyigruzki'],
				text: lang['vyigruzki'],
				iconCls : 'database-export32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						tooltip: lang['dlya_mintruda'],
						text: lang['dlya_mintruda'],
						//iconCls : 'drug-farm16',
						handler: function() {
							getWnd('swExportForLaborDepWindow').show();
						}
					}]
				})

			}/*,
			action_Reports: {
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
			}*/
			/*action_DrugRequest: {
				nn: 'action_DrugRequest',
				tooltip: lang['zayavka_na_lekarstvennyie_sredstva'],
				text: lang['zayavka_na_lekarstvennyie_sredstva'],
				iconCls : 'mp-drugrequest32',
				disabled: false, 
				handler: function(){
					getWnd('swWorkPlaceMinzdravDLOWindow').showDrugRequestEditForm();
				}.createDelegate(this)
			},
			action_PrivilegeSearch: {
				nn: 'action_PrivilegeSearch',
				tooltip: lang['poisk_lgotnikov'],
				text: lang['lgotniki'],
				iconCls : 'mse-journal32',
				disabled: false, 
				handler: function(){
					getWnd('swPrivilegeSearchWindow').show();
				}
			},
			action_DLO: {
				nn: 'action_DLO',
				tooltip: lang['llo'],
				text: lang['llo'],
				iconCls : 'dlo32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						//sw.Promed.Actions.PrivilegeSearch,
						sw.Promed.Actions.EvnReceptInCorrectFindAction,
						sw.Promed.Actions.OstAptekaViewAction,
						sw.Promed.Actions.OstDrugViewAction
					]
				})
			},
			action_EvnReceptSearch: {
				nn: 'action_EvnReceptSearch',
				tooltip: lang['lgotnyie_retseptyi_poisk'],
				text: lang['lgotnyie_retseptyi'],
				iconCls : 'priv-new32',
				disabled: false, 
				handler: function(){
					getWnd('swEvnReceptSearchWindow').show();
				}
			},
			action_Contragents: {
				nn: 'action_Contragents',
				tooltip: lang['spravochnik_kontragentyi'],
				text: lang['kontragentyi'],
				iconCls : 'org32',
				disabled: false, 
				handler: function(){
					getWnd('swContragentViewWindow').show();
				}
			},
			action_MedOstat: {
				nn: 'action_MedOstat',
				tooltip: lang['ostatki_medikamentov'],
				text: lang['ostatki_medikamentov'],
				iconCls : 'rls-torg32',
				disabled: false, 
				handler: function(){
					getWnd('swMedOstatViewWindow').show();
				}
			},
			action_Recipe: {
				nn: 'action_Recipe',
				tooltip: lang['retseptyi'],
				text: lang['retseptyi'],
				iconCls : 'receipt-new32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [					
						sw.Promed.Actions.EvnReceptProcessAction,
						sw.Promed.Actions.EvnReceptTrafficBookViewAction,
						sw.Promed.Actions.EvnReceptInCorrectFindAction
					]
				})
			},
			action_DokNak: {
				nn: 'action_DokNak',
				tooltip: lang['prihodnyie_nakladnyie'],
				text: lang['prihodnyie_nakladnyie'],
				iconCls : 'pl-stream32',
				disabled: false, 
				handler: function(){
					getWnd('swDokNakViewWindow').show();
				}
			}*/		
		};
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
							id: 'wpmwContragent_sid',
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
			id: 'wpmwWorkPlaceGridPanel',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})',
			groupingView: {showGroupName: false, showGroupsText: true},
			actions: [
				{name:'action_add', handler: function() { /*this.SelectCreateTypeWindow.show();*/ }.createDelegate(this)},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'},
				{name:'action_refresh', handler: function() {}.createDelegate(this)},
				{name:'action_print'}
			],
			autoLoadData: false,
			paging: true,
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
		sw.Promed.swWorkPlaceMinzdravDLOWindow.superclass.initComponent.apply(this, arguments);
	}
});
