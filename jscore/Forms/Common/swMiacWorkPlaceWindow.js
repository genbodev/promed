/**
 * swPMWorkPlaceWindow - окно рабочего места сотрудника МИАЦ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009, Swan.
 * @author       Dmitry Vlasenko
 * @version      11.2019
 */
/*NO PARSE JSON*/

sw.Promed.swMiacWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	objectName: 'swMiacWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Common/swMiacWorkPlaceWindow.js',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	title: 'Рабочее место сотрудника МИАЦ',
	iconCls: 'admin16',
	id: 'swMiacWorkPlaceWindow',
	show: function()
	{
		sw.Promed.swMiacWorkPlaceWindow.superclass.show.apply(this, arguments);

		var loadMask = new Ext.LoadMask(Ext.get('swMiacWorkPlaceWindow'), {msg: LOAD_WAIT});
		loadMask.show();
		var form = this;
		
		form.loadGridWithFilter(true);
		if (arguments[0]) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
			this.ARMType = arguments[0].ARMType;
		}
		
		//Очистим $_SESSION['TOUZLpuArr'] - https://redmine.swan.perm.ru/issues/104824
		 /*Ext.Ajax.request({
			callback: function(options, success, response) {
			},			
			url: '/?c=Common&m=clearTOUZLpuArr'
		});*/

		loadMask.hide();

	},
	clearFilters: function ()
	{
		this.findById('wpmzOrg_Nick').setValue('');
		this.findById('wpmzOrg_Name').setValue('');
	},
	loadGridWithFilter: function(clear)
	{
		var form = this;
		if (clear)
			form.clearFilters();
		var OrgNick = this.findById('wpmzOrg_Nick').getValue();
		var OrgName = this.findById('wpmzOrg_Name').getValue();
		//TOUZLpuArr
		var filters = {Nick: OrgNick, Name: OrgName, start: 0, limit: 100, mode: 'lpu'};
		if(getGlobalOptions().TOUZLpuArr && getGlobalOptions().TOUZLpuArr.length > 0)
		{
			filters.LpuArr = getGlobalOptions().TOUZLpuArr.join(',').replace('\'','');
		}
		form.LpuGrid.loadData({globalFilters: filters});
	},
	initComponent: function()
	{
		Ext.apply(sw.Promed.Actions, {
			ActionUslugaParFind: {
				text: lang['vyipolnenie_paraklinicheskoy_uslugi_poisk'],
				tooltip: lang['vyipolnenie_paraklinicheskoy_uslugi_poisk'],
				iconCls: 'par-serv-search16',
				handler: function()
				{
					getWnd('swEvnUslugaParSearchWindow').show({
						viewOnly: true
					});
				},
				hidden: false
			},
			DefectAction: 
			{
				nn: 'DefectAction',
				tooltip: lang['jurnal_otbrakovki'],
				text: lang['jurnal_otbrakovki'],
				iconCls : 'lab32',
				disabled: false, 
				handler: function() 
				{
					/*getWnd('swEvnLabSampleDefectViewWindow').show({
						MedService_id: this.MedService_id
					});*/
					getWnd('swEvnLabSampleDefectViewWindow').show({
						viewOnly: true
					});
				}.createDelegate(this)
			},
			ActionEvnPLSearch: {
				nn: 'ActionEvnPLSearch',
				text: lang['talon_ambulatornogo_patsienta_poisk'],
				tooltip: lang['talon_ambulatornogo_patsienta_poisk'],
				iconCls: 'pol-eplsearch16',
				handler: function()
				{
					getWnd('swEvnPLSearchWindow').show({
						viewOnly: true
					});
				}
			},
			ActionPersonCardSearch: {
				nn: 'ActionPersonCardSearch',
				text: lang['rpn_poisk'],
				tooltip: lang['rpn_poisk'],
				iconCls: 'card-search16',
				handler: function()
				{
					getWnd('swPersonCardSearchWindow').show({
						viewOnly: true
					});
				}
			},
			ActionPersonCardViewAll: {
				nn: 'ActionPersonCardViewAll',
				text: lang['rpn_prikreplenie'],
				tooltip: lang['rpn_prikreplenie'],
				iconCls:'card-view16',
				handler: function()
				{
					getWnd('swPersonCardViewAllWindow').show({
						viewOnly: true
					});
				}
			},
			ActionPersonCardState: {
				nn: 'ActionPersonCardState',
				text: lang['rpn_jurnal_dvijeniya'],
				tooltip: lang['rpn_jurnal_dvijeniya'],
				iconCls: 'card-state16',
				handler: function()
				{
					getWnd('swPersonCardStateViewWindow').show({
						viewOnly: true
					});
				}
			},
			ActionPersonCardSAttach:{
				nn: 'ActionPersonCardSAttach',
				text: lang['rpn_zayavleniya_o_vyibore_mo'],
				tooltip: lang['rpn_zayavleniya_o_vyibore_mo'],
				handler: function()
				{
					getWnd('swPersonCardAttachListWindow').show({
						viewOnly: true
					});
				}
			},
			PersonDispWOWSearchAction: {
				nn: 'PersonDispWOWSearchAction',
				text: lang['obsledovaniya_vov_poisk'],
				tooltip: lang['obsledovaniya_vov_poisk'],
				iconCls : 'dopdisp-search16', // to-do: Поменять иконку
				handler: function()
				{
					getWnd('EvnPLWOWSearchWindow').show({
							viewOnly: true
					});
				}
			},
			PersonPrivilegeWOWSearchAction: {
				nn: 'PersonPrivilegeWOWSearchAction',
				text: lang['registr_vov_poisk'],
				tooltip: lang['registr_vov_poisk'],
				iconCls : 'dopdisp-search16', // to-do: Поменять иконку
				handler: function()
				{
					getWnd('swPersonPrivilegeWOWSearchWindow').show({
							viewOnly: true
					});
				}
			},
			PersonDopDispSearchAction: {
				nn: 'PersonDopDispSearchAction',
				text: lang['dopolnitelnaya_dispanserizatsiya_poisk'],
				tooltip: lang['dopolnitelnaya_dispanserizatsiya_poisk'],
				iconCls : 'dopdisp-search16',
				handler: function()
				{
					getWnd('swPersonDopDispSearchWindow').show({
							viewOnly: true
					});
				}
			},
			EvnPLDopDispSearchAction: {
				nn: 'EvnPLDopDispSearchAction',
				text: lang['talon_po_dopolnitelnoy_dispanserizatsii_vzroslyih_do_2013g_poisk'],
				tooltip: lang['talon_po_dopolnitelnoy_dispanserizatsii_vzroslyih_do_2013g_poisk'],
				iconCls : 'dopdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispDopSearchWindow').show({
							viewOnly: true
					});
				}
			},
			EvnPLDispDop13SearchAction: {
				nn: 'EvnPLDispDop13SearchAction',
				text: MM_POL_EPLDD13SEARCH,
				tooltip: MM_POL_EPLDD13SEARCH,
				iconCls : 'dopdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispDop13SearchWindow').show({
							viewOnly: true
					});
				}
			},
			EvnPLDispDop13SecondSearchAction: {
				nn: 'EvnPLDispDop13SecondSearchAction',
				text: MM_POL_EPLDD13SECONDSEARCH,
				tooltip: MM_POL_EPLDD13SECONDSEARCH,
				iconCls : 'dopdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispDop13SecondSearchWindow').show({
							viewOnly: true
					});
				}
			},
			EvnPLDispProfSearchAction: {
				nn: 'EvnPLDispProfSearchAction',
				text: lang['profilakticheskie_osmotryi_vzroslyih'],
				tooltip: lang['profilakticheskie_osmotryi_vzroslyih'],
				iconCls : 'dopdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispProfSearchWindow').show({
							viewOnly: true
					});
				}	
			},
			swRegChildOrphanDopDispFindAction: {
				nn: 'swRegChildOrphanDopDispFindAction',
				text: lang['registr_detey-sirot_do_2013g_poisk'],
				tooltip: lang['registr_detey-sirot_poisk'],
				iconCls: 'orphdisp-search16',
				handler: function()
				{
					getWnd('swPersonDispOrpSearchWindow').show({
							viewOnly: true
					});
				}
			},
			swEvnPLChildOrphanDopDispFindAction: {
				nn: 'swEvnPLChildOrphanDopDispFindAction',
				text: lang['talon_po_dispanserizatsii_detey-sirot_do_2013g_poisk'],
				tooltip: lang['talon_po_dispanserizatsii_detey-sirot_poisk'],
				iconCls: 'orphdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispOrpSearchWindow').show({
							viewOnly: true
					});
				}
			},
			PersonDispOrpSearchAction: {
				nn: 'PersonDispOrpSearchAction',
				text: lang['registr_detey-sirot_statsionarnyih_poisk'],
				tooltip: lang['registr_detey-sirot_statsionarnyih_poisk'],
				iconCls : 'dopdisp-search16',
				handler: function()
				{
					getWnd('swPersonDispOrp13SearchWindow').show({
						CategoryChildType: 'orp',
						viewOnly: true
					});
				}
			},
			PersonDispOrpAdoptedSearchAction: {
				nn: 'PersonDispOrpAdoptedSearchAction',
				text: lang['registr_detey-sirot_usyinovlennyih_opekaemyih_poisk'],
				tooltip: lang['registr_detey-sirot_usyinovlennyih_opekaemyih_poisk'],
				iconCls : 'dopdisp-search16',
				handler: function()
				{
					getWnd('swPersonDispOrp13SearchWindow').show({
						CategoryChildType: 'orpadopted',
						viewOnly: true
					});
				}
			},
			EvnPLDispOrpSearchAction: {
				nn: 'EvnPLDispOrpSearchAction',
				text: lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_poisk'],
				tooltip: lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_poisk'],
				iconCls : 'dopdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispOrp13SearchWindow').show({
						stage: 1,
						viewOnly: true
					});
				}
			},
			EvnPLDispOrpSecSearchAction: {
				nn: 'EvnPLDispOrpSecSearchAction',
				text: lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_poisk'],
				tooltip: lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_poisk'],
				iconCls : 'dopdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispOrp13SearchWindow').show({
						stage: 2,
						viewOnly: true
					});
				}
			},
			PersonDispOrpPeriodSearchAction:
			{
				nn: 'PersonDispOrpPeriodSearchAction',
				text: lang['registr_periodicheskih_osmotrov_nesovershennoletnih_poisk'],
				tooltip: lang['registr_periodicheskih_osmotrov_nesovershennoletnih_poisk'],
				iconCls : 'dopdisp-search16',
				hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
				handler: function()
				{
					getWnd('swPersonDispOrpPeriodSearchWindow').show({
						viewOnly: true
					});
				}
			},
			EvnPLDispTeenInspectionSearchAction: {
				nn: 'EvnPLDispTeenInspectionSearchAction',
				text: lang['periodicheskie_osmotryi_nesovershennoletnih_poisk'],
				tooltip: lang['periodicheskie_osmotryi_nesovershennoletnih_poisk'],
				iconCls : 'dopdisp-epl-search16',
				hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
				handler: function()
				{
					getWnd('swEvnPLDispTeenInspectionSearchWindow').show({
						viewOnly: true
					});
				}
			},
			PersonDispOrpProfSearchAction: {
				nn: 'PersonDispOrpProfSearchAction',
				text: lang['napravleniya_na_profilakticheskie_osmotryi_nesovershennoletnih_poisk'],
				tooltip: lang['napravleniya_na_profilakticheskie_osmotryi_nesovershennoletnih_poisk'],
				iconCls : 'dopdisp-search16',
				handler: function()
				{
					getWnd('swPersonDispOrpProfSearchWindow').show({
						viewOnly: true
					});
				},
			},
			EvnPLDispTeenInspectionProfSearchAction: {
				nn: 'EvnPLDispTeenInspectionProfSearchAction',
				text: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
				tooltip: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
				iconCls : 'dopdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispTeenInspectionProfSearchWindow').show({
						viewOnly: true
					});
				}
			},
			EvnPLDispTeenInspectionProfSecSearchAction: {
				nn: 'EvnPLDispTeenInspectionProfSecSearchAction',
				text: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
				tooltip: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
				iconCls : 'dopdisp-epl-search16',
				handler: function()
				{
					getWnd('swEvnPLDispTeenInspectionProfSecSearchWindow').show({
						viewOnly: true
					});
				}
			},
			PersonDispOrpPredSearchAction: {
				nn: 'PersonDispOrpPredSearchAction',
				text: lang['napravleniya_na_predvaritelnyie_osmotryi_nesovershennoletnih_poisk'],
				tooltip: lang['napravleniya_na_predvaritelnyie_osmotryi_nesovershennoletnih_poisk'],
				iconCls : 'dopdisp-search16',
				hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
				handler: function()
				{
					getWnd('swPersonDispOrpPredSearchWindow').show({
						viewOnly: true
					});
				}
			},
			EvnPLDispTeenInspectionPredSearchAction: {
				nn: 'EvnPLDispTeenInspectionPredSearchAction',
				text: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
				tooltip: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
				iconCls : 'dopdisp-epl-search16',
				hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
				handler: function()
				{
					getWnd('swEvnPLDispTeenInspectionPredSearchWindow').show({
						viewOnly: true
					});
				}
			},
			EvnPLDispTeenInspectionPredSecSearchAction: {
				nn: 'EvnPLDispTeenInspectionPredSecSearchAction',
				text: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
				tooltip: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
				iconCls : 'dopdisp-epl-search16',
				hidden: getRegionNick().inlist(['adygeya', 'yakutiya']),
				handler: function()
				{
					getWnd('swEvnPLDispTeenInspectionPredSecSearchWindow').show({
						viewOnly: true
					});
				}
			},
			EvnPLDispTeen14SearchAction: {
				nn: 'EvnPLDispTeen14SearchAction',
				text: lang['dispanserizatsiya_14-letnih_podrostkov_poisk'],
				tooltip: lang['dispanserizatsiya_14-letnih_podrostkov_poisk'],
				iconCls : 'dopdisp-teens-search16',
				handler: function()
				{
					getWnd('swEvnPLDispTeen14SearchWindow').show({
						viewOnly: true
					});
				}
			},
			PersonDispSearchAction: {
				nn: 'PersonDispSearchAction',
				text: lang['dispansernyie_kartyi_patsientov_poisk'],
                tooltip: lang['dispansernyie_kartyi_patsientov_poisk'],
                iconCls: 'disp-search16',
                handler: function()
                {
                    getWnd('swPersonDispSearchWindow').show({
						viewOnly: true
					});
                }
			},
			PersonDispViewAction: {
				nn: 'PersonDispViewAction',
				text: lang['dispansernyie_kartyi_patsientov_spisok'],
                tooltip: lang['dispansernyie_kartyi_patsientov_spisok'],
                iconCls: 'disp-view16',
                handler: function()
                {
                    getWnd('swPersonDispViewWindow').show({mode: 'view', Person_id: null, view_one_doctor: true, viewOnly: true});
                }
			},
			amm_JournalsVac: {
				nn: 'amm_JournalsVac',
				text: lang['prosmotr_jurnalov_vaktsinatsii'],
				tooltip: lang['prosmotr_jurnalov_vaktsinatsii'],
				iconCls: 'vac-plan16',
				handler: function()
				{
					getWnd('amm_mainForm').show({
						viewOnly: true
					});
				}
			},
			ammvacReport_5: {
				hidden: getRegionNick() == 'kz',
				nn: 'ammvacReport_5',
				text: lang['otchet_f_№5'],
				tooltip: lang['otchet_f_№5'],
				iconCls: 'vac-plan16',
				handler: function()
				{
					getWnd('amm_vacReport_5').show({
						viewOnly: true
					});
				}
			},
			ammSprVaccine: {
				nn: 'ammSprVaccine',
				text: lang['spravochnik_vaktsin'],
				tooltip: lang['spravochnik_vaktsin'],
				iconCls: 'vac-plan16',
				handler: function()
				{
					getWnd('amm_SprVaccineForm').show({
						viewOnly: true
					});
				}
			},
			ammSprNacCal: {
				nn: 'ammSprNacCal',
				text: lang['natsionalnyiy_kalendar_privivok'],
				tooltip: lang['natsionalnyiy_kalendar_privivok'],
				iconCls: 'vac-plan16',
				handler: function()
				{
					getWnd('amm_SprNacCalForm').show({
						viewOnly: true
					});
				}
			},
			ammVacPresence: {
				nn: 'ammVacPresence',
				text: lang['nalichie_vaktsin'],
				tooltip: lang['nalichie_vaktsin'],
				iconCls: 'vac-plan16',
				handler: function()
				{
					getWnd('amm_PresenceVacForm').show({
						viewOnly: true
					});
				}
			},
			LgotTreeViewAction: {
				nn: 'LgotTreeViewAction',
				text: lang['registr_lgotnikov_spisok'],
				tooltip: lang['prosmotr_lgot_po_kategoriyam'],
				iconCls : 'lgot-tree16',
				handler: function()
				{
					getWnd('swLgotTreeViewWindow').show();
				}
			},
			LgotFindAction: {
				nn: 'LgotFindAction',
				text: MM_DLO_LGOTSEARCH,
				tooltip: lang['poisk_lgotnikov'],
				iconCls : 'lgot-search16',
				handler: function()
				{
					getWnd('swPrivilegeSearchWindow').show({viewOnly: true});
				}
			},
			EvnUdostViewAction: {
				nn: 'EvnUdostViewAction',
				text: MM_DLO_UDOSTLIST,
				tooltip: lang['prosmotr_udostovereniy'],
				iconCls : 'udost-list16',
				handler: function()
				{
					getWnd('swUdostViewWindow').show({viewOnly: true});
				}
			},
			EvnReceptFindAction: {
				nn: 'EvnReceptFindAction',
				text: MM_DLO_RECSEARCH,
				tooltip: lang['poisk_retseptov'],
				iconCls : 'receipt-search16',
				handler: function()
				{
					getWnd('swEvnReceptSearchWindow').show({onlyView: true});
				}
			},
			OstAptekaViewAction: {
				nn: 'OstAptekaViewAction',
				text: MM_DLO_MEDAPT,
				tooltip: lang['rabota_s_ostatkami_medikamentov_po_aptekam'],
				iconCls : 'drug-farm16',
				handler: function()
				{
					getWnd('swDrugOstatByFarmacyViewWindow').show();
				}
			},
			OstDrugViewAction: {
				nn: 'OstDrugViewAction',
				text: MM_DLO_MEDNAME,
				tooltip: lang['rabota_s_ostatkami_medikamentov_po_naimenovaniyu'],
				iconCls : 'drug-name16',
				handler: function()
				{
					getWnd('swDrugOstatViewWindow').show();
				}
			},
			OstSkladViewAction: {
				nn: 'OstSkladViewAction',
				text: MM_DLO_MEDSKLAD,
				tooltip: lang['rabota_s_ostatkami_medikamentov_na_aptechnom_sklade'],
				iconCls : 'drug-sklad16',
				handler: function()
				{
					getWnd('swDrugOstatBySkladViewWindow').show();
				}
			},
			DrugRequestViewAction: {
				nn: 'DrugRequestViewAction',
				text: lang['zayavka_na_ls_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
				tooltip: lang['zayavki_na_ls_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
				iconCls : 'drug-request16',
				handler: function()
				{
					getWnd('swDrugRequestViewForm').show({viewOnly: true});
				}
			},
			EvnReceptInCorrectFindAction: {
				nn: 'EvnReceptInCorrectFindAction',
				text: lang['jurnal_otsrochki'],
				tooltip: lang['jurnal_otsrochki'],
				iconCls : 'receipt-incorrect16',
				handler: function()
				{
					getWnd('swReceptInCorrectSearchWindow').show({viewOnly: true});
				}
			},
			DrugMnnLatinNameEditAction: {
				nn: 'DrugMnnLatinNameEditAction',
				text: WND_DLO_DRUGMNNLATINEDIT,
				tooltip: lang['redaktirovanie_latinskogo_naimenovaniya_mnn'],
				iconCls : 'drug-viewmnn16',
				handler: function()
				{
					getWnd('swDrugMnnViewWindow').show({
						privilegeType: 'all'
					});
				},
				hidden:(getRegionNick() == 'saratov'||getRegionNick() == 'pskov')
			},
			DrugTorgLatinNameEditAction: {
				nn: 'DrugTorgLatinNameEditAction',
				text: WND_DLO_DRUGTORGLATINEDIT,
				tooltip: lang['redaktirovanie_latinskogo_naimenovaniya_medikamenta'],
				iconCls : 'drug-viewtorg16',
				handler: function()
				{
					getWnd('swDrugTorgViewWindow').show();
				},
				hidden:(getRegionNick() == 'saratov'||getRegionNick() == 'pskov')
			},
			SprRlsAction: {
				nn: 'SprRlsAction',
				text: getRLSTitle(),
				tooltip: getRLSTitle(),
				iconCls: 'rls16',
				handler: function()
				{
					getWnd('swRlsViewForm').show();
				}
			},
			EvnDirectionHistologicViewAction: {
				nn:'EvnDirectionHistologicViewAction',
				text: lang['napravleniya_na_patologogistologicheskoe_issledovanie'],
				tooltip: lang['jurnal_napravleniy_na_patologogistologicheskoe_issledovanie'],
				iconCls : 'pathohist16',
				handler: function() {
					getWnd('swEvnDirectionHistologicViewWindow').show({
						viewOnly: true
					});
				}
			},
			EvnHistologicProtoViewAction: {
				nn: 'EvnHistologicProtoViewAction',
				text: lang['protokolyi_patologogistologicheskih_issledovaniy'],
				tooltip: lang['jurnal_protokolov_patologogistologicheskih_issledovaniy'],
				iconCls : 'pathohistproto16',
				handler: function() {
					getWnd('swEvnHistologicProtoViewWindow').show({
						viewOnly: true
					});
				}
			},
			EvnDirectionMorfoHistologicViewAction: {
				nn: 'EvnDirectionMorfoHistologicViewAction',
				text: lang['napravleniya_na_patomorfogistologicheskoe_issledovanie'],
				tooltip: lang['jurnal_napravleniy_na_patomorfogistologicheskoe_issledovanie'],
				iconCls : 'pathomorph16',
				handler: function() {
					getWnd('swEvnDirectionMorfoHistologicViewWindow').show({
						viewOnly: true
					});
				}
			},
			EvnMorfoHistologicProtoViewAction: {
				nn: 'EvnMorfoHistologicProtoViewAction',
				text: lang['protokolyi_patomorfogistologicheskih_issledovaniy'],
				tooltip: lang['jurnal_protokolov_patomorfogistologicheskih_issledovaniy'],
				iconCls : 'pathomorph16',
				handler: function() {
					getWnd('swEvnMorfoHistologicProtoViewWindow').show({
						viewOnly: true
					});
				}
			},
			DirectionsForCytologicalDiagnosticExaminationViewAction: {
				text: langs('Направления на цитологическое диагностическое исследование'),
				tooltip: langs('Направления на цитологическое диагностическое исследование'),
				iconCls : 'cytologica16',
				handler: function() {
					getWnd('swEvnDirectionCytologicViewWindows').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
				},
				hidden: (getRegionNick() == 'kz')
			},
			CytologicalDiagnosticTestProtocolsViewAction: {
				text: langs('Протоколы цитологических диагностических исследований'),
				tooltip: langs('Протоколы цитологических диагностических исследований'),
				iconCls : 'cytologica16',
				handler: function() {
					getWnd('swEvnCytologicProtoViewWindow').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
				},
				hidden: (getRegionNick() == 'kz')
			},
			EndoRegistry: {
				nn: 'EndoRegistry',
				tooltip: lang['registr_po_endoprotezirovaniyu'],
					text: lang['registr_po_endoprotezirovaniyu'],
					iconCls : 'doc-reg16',
					//disabled: !isUserGroup('EndoRegistry'),
					handler: function()
					{

						if ( getWnd('swEndoRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: lang['okno_uje_otkryito'],
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEndoRegistryWindow').show({editType: 'onlyRegister'});
					}.createDelegate(this)
			},
		 	IBSRegistry:
            {
            	nn: 'IBSRegistry',
                tooltip: lang['registr_ibs'],
                text: lang['registr_ibs'],
                iconCls : 'doc-reg16',
                hidden: ('perm' != getRegionNick()),
                //disabled: (String(getGlobalOptions().groups).indexOf('IBSRegistry', 0) < 0),
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
            },
            SuicideRegistry: {
            	nn: 'SuicideRegistry',
				tooltip: 'Регистр по суицидам',
				text: 'Регистр по суицидам',
				iconCls : 'doc-reg16',
				//disabled: !isUserGroup('SuicideRegistry'),
				handler: function()
				{
					if ( getWnd('swPersonRegisterSuicideListWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swPersonRegisterSuicideListWindow').show({editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			PalliatRegistry: {
				tooltip: 'Регистр по паллиативной помощи',
				text: 'Регистр по паллиативной помощи',
				iconCls : 'doc-reg16',
				//disabled: !isUserGroup('RegistryPalliatCare'),
                hidden: (getRegionNick() == 'kz'),
				handler: function()
				{
					if ( getWnd('swPersonRegisterPalliatListWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swPersonRegisterPalliatListWindow').show({editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			GeriatricsRegistry: {
				tooltip: langs('Регистр по гериатрии'),
				text: langs('Регистр по гериатрии'),
				hidden: getRegionNick() == 'kz',
				iconCls : 'doc-reg16',
				handler: function() {
					if ( getWnd('swGeriatricsRegistryWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swGeriatricsRegistryWindow').show({editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			GibtRegistry: {
				tooltip: langs('Регистр нуждающихся в ГИБТ'),
				text: langs('Регистр нуждающихся в ГИБТ'),
				hidden: getRegionNick() != 'perm',
				disabled: !isUserGroup('GEBTRegistry'),
				iconCls : 'doc-reg16',
				handler: function() {
					if ( getWnd('swGibtRegistryWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: langs('Окно уже открыто'),
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swGibtRegistryWindow').show({editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			RegisterSixtyPlus: {
					tooltip: 'Регистр "Скрининг населения 60+"',
					text: 'Регистр "Скрининг населения 60+"',
					iconCls : 'doc-reg16',
					hidden: !getRegionNick().inlist(['perm', 'ufa']),
					handler: function()
					{   

							if ( getWnd('swRegisterSixtyPlusViewWindow').isVisible() ) {
									sw.swMsg.show({
											buttons: Ext.Msg.OK,
											fn: Ext.emptyFn,
											icon: Ext.Msg.WARNING,
											msg: 'Окно уже открыто',
											title: ERR_WND_TIT
									});
									return false;
							}
							getWnd('swRegisterSixtyPlusViewWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
			VZNRegistry: {
				nn: 'VZNRegistry',
				tooltip: lang['registr_po_vzn'],
				text: lang['registr_po_vzn'],
				iconCls : 'doc-reg16',
				//disabled: (String(getGlobalOptions().groups).indexOf('VznRegistry', 0) < 0),
				handler: function() {
					getWnd('swPersonRegisterNolosListWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister', fromARM: 'spec_mz'});
				}
			},
			EvnNotifyNolos: {
				tooltip: lang['jurnal_izvescheniy_napravleniy_po_vzn'],
				text: lang['jurnal_izvescheniy_napravleniy_po_vzn'],
				iconCls : 'journal16',
				handler: function() {
					if ( getWnd('swEvnNotifyRegisterNolosListWindow').isVisible() ) {
						getWnd('swEvnNotifyRegisterNolosListWindow').hide();
					}
					getWnd('swEvnNotifyRegisterNolosListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}
			},
			EvnNotifyOrphan: {
				tooltip: lang['jurnal_izvescheniy_napravleniy_ob_orfannyih_zabolevaniyah'],
				text: lang['jurnal_izvescheniy_napravleniy_ob_orfannyih_zabolevaniyah'],
				iconCls : 'journal16',
				handler: function() {
					if ( getWnd('swEvnNotifyRegisterOrphanListWindow').isVisible() ) {
						getWnd('swEvnNotifyRegisterOrphanListWindow').hide();
					}
					getWnd('swEvnNotifyRegisterOrphanListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}
			},
			EvnNotifyHepatitis: {
				tooltip: 'Журнал Извещений по Вирусному гепатиту',
					text: 'Журнал Извещений по Вирусному гепатиту',
					iconCls : 'journal16',
					handler: function()
					{
						if ( getWnd('swEvnNotifyHepatitisListWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: 'Окно уже открыто',
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swEvnNotifyHepatitisListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
					}.createDelegate(this)
			},
			EvnOnkoNotify: 
			{
				tooltip: 'Журнал Извещений об онкобольных ',
				text: 'Журнал Извещений об онкобольных ',
				iconCls : 'journal16',
				handler: function()
				{
					if ( getWnd('swEvnOnkoNotifyListWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swEvnOnkoNotifyListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}.createDelegate(this)
			},
			EvnNotifyCrazy:
			{
				tooltip: 'Журнал Извещений по психиатрии',
				text: 'Журнал Извещений по психиатрии',
				iconCls : 'journal16',
				//disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyCrazyListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}.createDelegate(this)
			},
			EvnNotifyNarko:
			{
				tooltip: 'Журнал Извещений по наркологии',
				text: 'Журнал Извещений по наркологии',
				iconCls : 'journal16',
				//disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyNarkoListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}.createDelegate(this)
			},
			EvnNotifyTub:
			{
				tooltip: 'Журнал Извещений о больных туберкулезом',
				text: 'Журнал Извещений по туберкулезным заболеваниям',
				iconCls : 'journal16',
				//disabled: (String(getGlobalOptions().groups).indexOf('Tub', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyTubListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}.createDelegate(this)
			},
			EvnNotifyVener:
			{
				tooltip: 'Журнал Извещений о больных венерическим заболеванием',
				text: 'Журнал Извещений о больных венерическим заболеванием',
				iconCls : 'journal16',
				//disabled: (String(getGlobalOptions().groups).indexOf('Vener', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyVenerListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}.createDelegate(this)
			},
			EvnNotifyHIV:
			{
				tooltip: 'Журнал Извещений о ВИЧ-инфицированных',
				text: 'Журнал Извещений о ВИЧ-инфицированных',
				iconCls : 'journal16',
				//disabled: (String(getGlobalOptions().groups).indexOf('HIV', 0) < 0),
				handler: function()
				{
					getWnd('swEvnNotifyHIVListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}.createDelegate(this)
			},
			EvnInfectNotify: 
			{
				tooltip: lang['jurnal_izvescheniy_forma_№058_u'],
				text: lang['jurnal_izvescheniy_forma_№058_u'],
				iconCls : 'journal16',
				disabled: false, 
				handler: function()
				{
					if ( getWnd('swEvnInfectNotifyListWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: lang['okno_uje_otkryito'],
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swEvnInfectNotifyListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
				}.createDelegate(this)
			},
			EvnNotifyProf:
            {
                tooltip: lang['jurnal_izvescheniy_po_profzabolevaniyam'],
                text: lang['jurnal_izvescheniy_po_profzabolevaniyam'],
                iconCls : 'journal16',
                hidden: ('perm' != getRegionNick()),
                handler: function()
                {
                    if ( getWnd('swEvnNotifyProfListWindow').isVisible() ) {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: Ext.emptyFn,
                            icon: Ext.Msg.WARNING,
                            msg: lang['okno_uje_otkryito'],
                            title: ERR_WND_TIT
                        });
                        return false;
                    }
                    getWnd('swEvnNotifyProfListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
                }.createDelegate(this)
            },
            EvnNotifyNephro:
            {
                tooltip: lang['jurnal_izvescheniy_po_nefrologii'],
                text: lang['jurnal_izvescheniy_po_nefrologii'],
                iconCls : 'journal16',
                hidden: !getRegionNick().inlist([ 'perm', 'ufa' ]),
                handler: function()
                {
                    if ( getWnd('swEvnNotifyNephroListWindow').isVisible() ) {
                        sw.swMsg.show({
                            buttons: Ext.Msg.OK,
                            fn: Ext.emptyFn,
                            icon: Ext.Msg.WARNING,
                            msg: lang['okno_uje_otkryito'],
                            title: ERR_WND_TIT
                        });
                        return false;
                    }
                    getWnd('swEvnNotifyNephroListWindow').show({userMedStaffFact: new Object(), fromARM: 'spec_mz'});
                }.createDelegate(this)
            },

			NephroRegistry: {
				nn: 'NephroRegistry',
				tooltip: lang['registr_po_nefrologii'],
                text: lang['registr_po_nefrologii'],
                iconCls : 'doc-reg16',
                hidden: !getRegionNick().inlist([ 'perm', 'ufa' ]),
                //disabled: (String(getGlobalOptions().groups).indexOf('NephroRegistry', 0) < 0),
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
                    getWnd('swNephroRegistryWindow').show({editType: 'onlyRegister'});
                }.createDelegate(this)
			},
			OnkoRegistry: {
				nn:'OnkoRegistry',
				tooltip: lang['registr_po_onkologii'],
				text: lang['registr_po_onkologii'],
				iconCls : 'doc-reg16',
				//disabled: (String(getGlobalOptions().groups).indexOf('OnkoRegistry', 0) < 0), 
				handler: function()
				{
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
					getWnd('swOnkoRegistryWindow').show({editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			OrphanRegistry: {
				nn: 'OrphanRegistry',
				tooltip: lang['registr_po_orfannyim_zabolevaniyam'],
				text: lang['registr_po_orfannyim_zabolevaniyam'],
				iconCls : 'doc-reg16',
				//disabled: (String(getGlobalOptions().groups).indexOf('Orphan', 0) < 0),
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
					getWnd('swPersonRegisterOrphanListWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister', fromARM: 'spec_mz'});
				}.createDelegate(this)
			},
			PregnancyRegistry: {
				nn: 'PregnancyRegistry',
				tooltip: lang['registr_beremennyih'],
				text: lang['registr_beremennyih'],
				iconCls : 'doc-reg16',
				handler: function(){
					getWnd('swPersonPregnancyWindow').show({editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			ProfRegystry: {
				nn: 'ProfRegystry',
				tooltip: lang['registr_po_profzabolevaniyam'],
                text: lang['registr_po_profzabolevaniyam'],
                iconCls : 'doc-reg16',
                handler: function(){
					getWnd('swProfRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			IPRARegistry: {
				nn: 'IPRARegistry',
				tooltip: 'Регистр ИПРА',
				text: 'Регистр ИПРА',
				iconCls : 'doc-reg16',
				handler: function(){
					getWnd('swIPRARegistryViewWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			 ZNOSuspectRegistry: {
					nn: 'ZNOSuspectRegistry',
					tooltip: langs('Регистр пациентов с подозрением на ЗНО'),
					text: langs('Регистр пациентов с подозрением на ЗНО'),
					iconCls : 'doc-reg16',
					hidden: (getGlobalOptions().region.nick != 'ufa'),
					handler: function()
					{ 
						if ( getWnd('swZNOSuspectRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swZNOSuspectRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
						//alert("dfdfdfd");
					}.createDelegate(this)
				},
			ReabRegistry: {
					nn: 'ReabRegistry',
					tooltip: langs('Регистр Реабилитации'),
					text: langs('Регистр Реабилитации'),
					iconCls : 'doc-reg16',
					hidden: (getGlobalOptions().region.nick != 'ufa'),
					handler: function()
					{ 
						if ( getWnd('swReabRegistryWindow').isVisible() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: Ext.emptyFn,
								icon: Ext.Msg.WARNING,
								msg: langs('Окно уже открыто'),
								title: ERR_WND_TIT
							});
							return false;
						}
						getWnd('swReabRegistryWindow').show({userMedStaffFact: this.userMedStaffFact});
					}.createDelegate(this)
				},
			ECORegistry: {
				nn: 'ECORegistry',
				tooltip: 'Регистр по ВРТ',
				text: 'Регистр по ВРТ',
				iconCls : 'doc-reg16',
				//hidden: (String(getGlobalOptions().groups).indexOf('EcoRegistry', 0) < 0 && String(getGlobalOptions().groups).indexOf('EcoRegistryRegion', 0) < 0),
				handler: function()
				{   

					if ( getWnd('swECORegistryViewWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swECORegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},
			ONMKRegistry: {
				tooltip: 'Регистр ОНМК',
				text: 'Регистр ОНМК',
				iconCls : 'doc-reg16',					
				handler: function()
				{   
					if ( getWnd('swONMKRegistryViewWindow').isVisible() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Окно уже открыто',
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('swONMKRegistryViewWindow').show({userMedStaffFact: this.userMedStaffFact});
				}.createDelegate(this)
			},			
			MonitorBirthSpec:{
				nn: 'MonitorBirthSpec',
				tooltip: lang['monitoring_novorojdennyih'],
				text: lang['monitoring_novorojdennyih'],
				iconCls : 'doc-reg16',
				handler: function(){
					getWnd('swMonitorBirthSpecWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			HepatitisRegistry:{
				nn: 'HepatitisRegistry',
				tooltip: lang['registr_po_virusnomu_gepatitu'],
				text: lang['registr_po_virusnomu_gepatitu'],
				iconCls : 'doc-reg16',
				handler: function(){
					getWnd('swHepatitisRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			CrazyRegistry: {
				nn: 'CrazyRegistry',
				tooltip: lang['registr_po_psihiatrii'],
				text: lang['registr_po_psihiatrii'],
				iconCls : 'doc-reg16',
				//disabled: (String(getGlobalOptions().groups).indexOf('Crazy', 0) < 0),
				handler: function()
				{
					getWnd('swCrazyRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			NarkoRegistry:
			{
				nn: 'NarkoRegistry',
				tooltip: lang['registr_po_narkologii'],
				text: lang['registr_po_narkologii'],
				iconCls : 'doc-reg16',
				//disabled: (String(getGlobalOptions().groups).indexOf('Narko', 0) < 0),
				handler: function()
				{
					getWnd('swNarkoRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			TubRegistry:
			{
				nn: 'TubRegistry',
				tooltip: lang['registr_bolnyih_tuberkulezom'],
				text: lang['registr_po_tuberkuleznyim_zabolevaniyam'],
				iconCls : 'doc-reg16',
				handler: function()
				{
					getWnd('swTubRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			VenerRegistry:
			{
				nn: 'VenerRegistry',
				tooltip: lang['registr_bolnyih_venericheskim_zabolevaniem'],
				text: lang['registr_bolnyih_venericheskim_zabolevaniem'],
				iconCls : 'doc-reg16',
				handler: function()
				{
					getWnd('swVenerRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			HIVRegistry:
			{
				nn: 'HIVRegistry',
				tooltip: lang['registr_vich-infitsirovannyih'],
				text: lang['registr_vich-infitsirovannyih'],
				iconCls : 'doc-reg16',
				handler: function()
				{
					getWnd('swHIVRegistryWindow').show({userMedStaffFact: new Object(), editType: 'onlyRegister'});
				}.createDelegate(this)
			},
			TreatmentSearchAction: {
				nn: 'TreatmentSearchAction',
				text: lang['registratsiya_obrascheniy_poisk'],
				tooltip: lang['registratsiya_obrascheniy_poisk'],
				iconCls: 'petition-search16',
				handler: function() {
					getWnd('swTreatmentSearchWindow').show({action: 'view'});
				}
			},
			TreatmentReportAction: {
				nn: 'TreatmentReportAction',
				text: lang['registratsiya_obrascheniy_otchetnost'],
				tooltip: lang['registratsiya_obrascheniy_otchetnost'],
				iconCls: 'petition-report16',
				handler: function() {
					getWnd('swTreatmentReportWindow').show();
				}
			},
			ContragentsAction:{
				nn: 'ContragentsAction',
				tooltip: lang['spravochnik_kontragentyi'],
	            text: lang['spravochnik_kontragentyi'],
	            iconCls : 'org16',
	            disabled: false,
	            handler: function() {
	                getWnd('swContragentViewWindow').show({
	                    onlyView: true
	                });
	            }
			},
			DrugOstatRegistryAction:{
				nn: 'DrugOstatRegistryAction',
				tooltip: langs('Остатки медикаментов'),
				text: langs('Остатки медикаментов'),
				iconCls : 'rls-torg32',
				disabled: false,
				handler: function() {
					getWnd('swDrugOstatRegistryListWindow').show({
						mode: 'suppliers'
					});
				}
			},
			DokNaklAction: {
				nn: 'DokNaklAction',
				text: lang['prihodnyie_nakladnyie'],
				tooltip: lang['prihodnyie_nakladnyie'],
				iconCls: 'doc-nak16',
				handler: function()
				{
					getWnd('swDokNakViewWindow').show({viewOnly: true});
				}
			},
			DocUcAction: {
				nn: 'DocUcAction',
				tooltip: lang['dokumentyi_ucheta_medikamentov'],
				text: lang['dokumentyi_ucheta_medikamentov'],
				iconCls : 'document16',
				disabled: false,
				handler: function(){
					getWnd('swDokUcLpuViewWindow').show({viewOnly: true});
				}
			},
			ActSpisAction: {
				nn: 'ActSpisAction',
				text: lang['aktyi_spisaniya_medikamentov'],
				tooltip: lang['aktyi_spisaniya_medikamentov'],
				iconCls: 'doc-spis16',
				handler: function()
				{
					getWnd('swDokSpisViewWindow').show({viewOnly: true});
				}
			},
			DocOstAction: {
				nn: 'DocOstAction',
				text: lang['dokumentyi_vvoda_ostatkov'],
				tooltip: lang['dokumentyi_vvoda_ostatkov'],
				iconCls: 'doc-ost16',
				handler: function()
				{
					getWnd('swDokOstViewWindow').show({viewOnly: true});
				}	
			},
			InvVedAction: {
				nn: 'InvVedAction',
				text: lang['inventarizatsionnyie_vedomosti'],
				tooltip: lang['inventarizatsionnyie_vedomosti'],
				iconCls: 'farm-inv16',
				handler: function()
				{
					getWnd('swDokInvViewWindow').show({viewOnly: true});
					//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				}
			},
			MKB10Action: {
				nn: 'MKB10Action',
				text: lang['spravochnik_mkb-10'],
				tooltip: lang['spravochnik_mkb-10'],
				iconCls: 'spr-mkb16',
				handler: function()
				{
					getWnd('swMkb10SearchWindow').show();
				}
			},
			ClinicRecommendAction: {
				nn: 'ClinicRecommendAction',
				text: langs('Клинические рекомендации'),
				tooltip: langs('Клинические рекомендации'),
				iconCls: '',
				handler: function()
				{
					getWnd('swCureStandartListWindow').show({ARMType: this.ARMType});
				}
			},
			DrugDocumentSprAction: {
				nn: 'DrugDocumentSprAction',
				text: lang['spravochniki_sistemyi_ucheta_medikamentov'],
				tooltip: lang['spravochniki_sistemyi_ucheta_medikamentov'],
				iconCls: '',
				handler: function()
				{
					getWnd('swDrugDocumentSprWindow').show({ARMType: this.ARMType});
				}
			},
			DrugListAction: {
				name: 'DrugListAction',
				text: 'Перечни медикаментов',
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugListSprWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			DrugNomenAction: {
				nn: 'DrugNomenAction',
				text: lang['nomenklaturnyiy_spravochnik'],
				tooltip: lang['nomenklaturnyiy_spravochnik'],
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugNomenSprWindow').show({readOnly: true});
				}
			},
			DrugMNNAction: {
				nn: 'DrugMNNAction',
				text: lang['spravochnik_mnn'],
				tooltip: lang['spravochnik_mnn'],
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugMnnCodeViewWindow').show({action: 'view'});
				}
			},
			DrugTorgAction: {
				nn: 'DrugTorgAction',
				text: lang['spravochnik_torgovyih_naimenovaniy'],
				tooltip: lang['spravochnik_torgovyih_naimenovaniy'],
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugTorgCodeViewWindow').show({action: 'view'});
				}
			},
			PriceJNVLPAction: {
				nn: 'PriceJNVLPAction',
				text: lang['tsenyi_na_jnvlp'],
				tooltip: lang['tsenyi_na_jnvlp'],
				iconCls : 'dlo16',
				handler: function() {
					getWnd('swJNVLPPriceViewWindow').show();
				}
			},
			DrugMarkupAction: {
				nn: 'DrugMarkupAction',
				text: lang['predelnyie_nadbavki_na_jnvlp'],
				tooltip: lang['predelnyie_nadbavki_na_jnvlp'],
				iconCls : 'lpu-finans16',
				handler: function() {
					getWnd('swDrugMarkupViewWindow').show({readOnly: true});
				}
			},
			DrugRMZAction: {
				nn: 'DrugRMZAction',
				text: lang['spravochnik_rzn'],
				tooltip: lang['spravochnik_rzn'],
                iconCls : 'view16',
                handler: function() {
                    getWnd('swDrugRMZViewWindow').show({action:'view'});
                }
			},
			TariffAction: {
				nn: 'TariffAction',
				text: lang['tarifyi_llo'],
				tooltip: lang['tarifyi_llo'],
				iconCls : 'lpu-finans16',
				handler: function() {
					getWnd('swUslugaComplexTariffLloViewWindow').show({viewOnly: true, allowEdit: true});
				}
			},
			PrepBlockSprAction: {
				nn: 'PrepBlockSprAction',
				text: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
				tooltip: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
				handler: function()
				{
					getWnd('swPrepBlockViewWindow').show();
				}
			},
			GoodsUnitAction: {
				nn: 'GoodsUnitAction',
				text: lang['edinitsyi_izmereniya_tovara'],
				tooltip: lang['edinitsyi_izmereniya_tovara'],
				handler: function()
				{
					getWnd('swGoodsUnitViewWindow').show({allowImportFromRls: true, viewOnly: true});
				}
			}
		});

		var form = this;
		var configActions = 
		{
			action_selectLpu:
			{
				nn: 'action_selectLpu',
				tooltip: 'Выбрать МО просмотра',
				text: 'Выбрать МО просмотра',
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'lpu-select16',
				disabled: false, 
				handler: function() 
				{
					getWnd('swChangeLpuWindow').show();
				}
			},
			action_openRecordMaster: {
				nn: 'action_openRecordMaster',
				text: lang['raspisanie'],
				tooltip: lang['raspisanie'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'eph-timetable-top32',
				handler: function()
				{
					getWnd('swDirectionMasterWindow').show({
						userMedStaffFact: {ARMType: this.ARMType}
					});
				}.createDelegate(this)
			},
			action_openEMK: {
				nn: 'action_openEMK',
				text: lang['otkryit_emk'],
				tooltip: lang['otkryit_emk'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'patient-search32',
				handler: function()
				{
					getWnd('swPersonSearchWindow').show({
						viewOnly: true,
						onSelect: function(person_data) {
							getWnd('swPersonSearchWindow').hide();
							person_data.ARMType = 'common';
							person_data.readOnly = true;
							getWnd('swPersonEmkWindow').show(person_data);
						},
						searchMode: 'all'
					});
				}
			},
			action_searchPerson: {
				nn: 'action_searchPerson',
				text: langs('Человек'),
				tooltip: lang['poisk_cheloveka'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'mp-queue32',
				handler: function()
				{
					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {
							getWnd('swPersonEditWindow').show({
								readOnly: true,
								onHide: function () {
									if ( person_data.onHide && typeof person_data.onHide == 'function' ) {
										person_data.onHide();
									}
								},
								Person_id: person_data.Person_id,
								Server_id: person_data.Server_id
							});
						},
						searchMode: 'all',
						viewOnly: true
					});
				}
			},
			action_reports:
			{
				nn: 'action_Report',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				hidden: !isUserGroup(['MIACStat', 'MIACMonitoring', 'MIACSuperAdmin']),
				iconCls: 'report32',
				handler: function() {
					if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show();
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show();
								}
							});
					}
				}
			},
			action_stomSearh: {
				nn: 'action_stomSearh',
				text: lang['stomatologiya'],
				tooltip: lang['stomatologiya'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls : 'stom-search16',
				handler: function()
				{
					getWnd('swEvnPLStomSearchWindow').show({
						viewOnly: true
					});
				}
			},
			action_Par: 
			{
				nn: 'action_Par',
				tooltip: lang['paraklinika'],
				text: lang['paraklinika'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls : 'parka16',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.ActionUslugaParFind,
						sw.Promed.Actions.DefectAction
					]
				})
			},
			action_EvnPSSearh: {
				nn: 'action_EvnPSSearh',
				text: lang['statsionar'],
				tooltip: lang['statsionar'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'stac-pssearch16',
				handler: function()
				{
					getWnd('swEvnPSSearchWindow').show({
						viewOnly: true
					});
				}
			},
			action_Polka:
			{
				nn:'action_Polka',
				tooltip: lang['poliklinika'],
				text: lang['poliklinika'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'polyclinic16',
				menu: new Ext.menu.Menu({
					items:[
						sw.Promed.Actions.ActionEvnPLSearch, //ТАП Поиск
						sw.Promed.Actions.ActionPersonCardSearch, //РПН Поиск
						sw.Promed.Actions.ActionPersonCardViewAll, //РПН Прикрепления
						sw.Promed.Actions.ActionPersonCardState, //РПН Журнал движений
						sw.Promed.Actions.ActionPersonCardSAttach, //Заявления о выборе МО
						{
							nn: 'action_Disp',
							tooltip: lang['dispanserizatsiya_vzroslogo_naseleniya'],
							text: lang['dispanserizatsiya_vzroslogo_naseleniya'],
							iconCls: 'pol-dopdisp16',
							menu: new Ext.menu.Menu({
								items:[
									sw.Promed.Actions.PersonDispWOWSearchAction, //Обследования ВОВ поиск
									'-',
									sw.Promed.Actions.PersonPrivilegeWOWSearchAction, //Регистр ВОВ поиск
									'-',
									sw.Promed.Actions.PersonDopDispSearchAction, // Регистр по дополнительной диспансеризации поиск
									'-',
									sw.Promed.Actions.EvnPLDopDispSearchAction, //Талон по дополнительной диспансеризации поиск
									'-',
									sw.Promed.Actions.EvnPLDispDop13SearchAction, //Диспансеризация взр населениея I этап поиск
									sw.Promed.Actions.EvnPLDispDop13SecondSearchAction //Диспансеризация взр населениея II этап поиск
								]
							})
						},
						sw.Promed.Actions.EvnPLDispProfSearchAction,
						{
							nn: 'action_DispOrp',
							tooltip: lang['dispanserizatsiya_detey-sirot'],
							text: lang['dispanserizatsiya_detey-sirot'],
							iconCls: 'pol-dopdisp16',
							menu: new Ext.menu.Menu({
								items:[
									sw.Promed.Actions.swRegChildOrphanDopDispFindAction, //Регистр детей-сирот до 2013 года поиск
									sw.Promed.Actions.swEvnPLChildOrphanDopDispFindAction, //Талон по диспансеризации детей-сирот до 2013 года поиск
									'-',
									sw.Promed.Actions.PersonDispOrpSearchAction, //Регистр детей-сирот стационарных
									sw.Promed.Actions.PersonDispOrpAdoptedSearchAction, //Регистр детей-сирот усыновленных
									sw.Promed.Actions.EvnPLDispOrpSearchAction, //Карта диспансеризации несовершеннолетнего 1 I этап поиск
									sw.Promed.Actions.EvnPLDispOrpSecSearchAction //Карта диспансеризации несовершеннолетнего 1 II этап поиск
								]
							})
						},
						{
							nn: 'action_DDYoung',
							tooltip: lang['meditsinskie_osmotryi_nesovershennoletnih'],
							text: lang['meditsinskie_osmotryi_nesovershennoletnih'],
							iconCls: 'pol-dopdisp16',
							menu: new Ext.menu.Menu({
								items:[
									sw.Promed.Actions.PersonDispOrpPeriodSearchAction, //Регистр периодических осмотров неосвершеннолетних
									sw.Promed.Actions.EvnPLDispTeenInspectionSearchAction, //Периодические осмотры несовершеннолетних поиск
									'-',
									sw.Promed.Actions.PersonDispOrpProfSearchAction, //Направления на профилактические осмотры несовершеннолетних поиск
									sw.Promed.Actions.EvnPLDispTeenInspectionProfSearchAction, //Проф осмотры несовершеннолетних I этап поиск
									sw.Promed.Actions.EvnPLDispTeenInspectionProfSecSearchAction, //Проф осмотры несовершеннолетних II этап поиск
									'-',
									sw.Promed.Actions.PersonDispOrpPredSearchAction, //Направления не предварительные осмотры несовершеннолетних поиск
									sw.Promed.Actions.EvnPLDispTeenInspectionPredSearchAction, //Предварительные осмотры несовершеннолетних I этап поиск
									sw.Promed.Actions.EvnPLDispTeenInspectionPredSecSearchAction //Предварительные осмотры несовершеннолетних II этап поиск
								]
							})
						},
						sw.Promed.Actions.EvnPLDispTeen14SearchAction, //Диспансеризация подростки 14 лет поиск
						{
							nn: 'action_DispSearchView',
							tooltip: lang['dispansernoe_nablyudenie'],
							text: lang['dispansernoe_nablyudenie'],
							iconCls : 'epl-ddisp-new16',
							menu: new Ext.menu.Menu({
								items:[
									sw.Promed.Actions.PersonDispSearchAction, //Контрольные карты дисп наблюдения поиск
									sw.Promed.Actions.PersonDispViewAction, //Контрольные карты дисп наблюдения список
								]
							})
						}
					]
				})
			},
			action_LLO:
			{
				nn: 'action_LLO',
				tooltip: lang['llo'],
				text: lang['llo'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls : 'dlo32',
				menu: new Ext.menu.Menu({
					items:[
						sw.Promed.Actions.LgotTreeViewAction, //Регистр льготников список
						sw.Promed.Actions.LgotFindAction, //Регистр льготников поиск
						'-',
						sw.Promed.Actions.EvnUdostViewAction, //Удостоверения льготников поиск
						sw.Promed.Actions.EvnReceptFindAction, //Льготные рецепты поиск
						'-',
						sw.Promed.Actions.OstAptekaViewAction, //Остатки медикаментов по аптекам
						sw.Promed.Actions.OstDrugViewAction, //Остатки медикаментов по наименованию
						sw.Promed.Actions.OstSkladViewAction, //Остатки медикаментов на аптечном складе
						'-',
						sw.Promed.Actions.DrugRequestViewAction, //Заявка на ЛС по общетерапевтической группе заболеваний
						sw.Promed.Actions.EvnReceptInCorrectFindAction, //Журнал отсрочки
						sw.Promed.Actions.DrugMnnLatinNameEditAction, // МНН ввод латинских наименований
						sw.Promed.Actions.DrugTorgLatinNameEditAction, //Торг. наим.: ввод латинских наименований
						'-',
						sw.Promed.Actions.SprRlsAction //Регистр лекарственных средство России
					]
				})
			},
			action_Morf:
			{
				nn: 'action_Morf',
				text:lang['patomorfologiya'],
				tooltip: lang['patomorfologiya'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'pathomorph-16',
				menu: new Ext.menu.Menu(
				{
					items: [
						sw.Promed.Actions.EvnDirectionHistologicViewAction, //Направление на патологистическое исследование
						sw.Promed.Actions.EvnHistologicProtoViewAction, //Протоколы патологистических исследований
						'-',
						sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction, //Направления на патоморфологическое исследование
						sw.Promed.Actions.EvnMorfoHistologicProtoViewAction //Протоколы патоморфологических исследований
					]
				})
			},
			action_Register:
			{
				nn: 'action_Register',
				tooltip: lang['registryi_patsientov'],
				text: lang['registryi_patsientov'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls : 'registry32',
				menu: new Ext.menu.Menu(
				{
					items: [
						sw.Promed.Actions.VZNRegistry,//Регистр по ВЗН
						sw.Promed.Actions.OnkoRegistry,//Регистр по онкологии
						sw.Promed.Actions.OrphanRegistry,//Регистр по орфанным заболеваниям
						sw.Promed.Actions.PregnancyRegistry,//Регистр беременных
						sw.Promed.Actions.CrazyRegistry, //Регистр по психиатрии
						sw.Promed.Actions.NarkoRegistry, //Регистр по наркологии
						sw.Promed.Actions.TubRegistry //Регистр по туберкулезу
					]
				})
			},
			action_Svid: {
				nn: 'action_Svid',
				tooltip: lang['svidetelstva'],
				text: lang['svidetelstva'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls : 'medsvid32',
				disabled: false,
				menuAlign: 'tr?',
				menu: new Ext.menu.Menu({
					items: [
						{
							text: lang['svidetelstva_o_rojdenii'],
							tooltip: lang['svidetelstva_o_rojdenii'],
							iconCls: 'svid-birth16',
							handler: function()
							{
								getWnd('swMedSvidBirthStreamWindow').show({action: 'view', viewOnly: true});
							},
							hidden: false
						},
						{
							text: lang['svidetelstva_o_smerti'],
							tooltip: lang['svidetelstva_o_smerti'],
							iconCls: 'svid-death16',
							handler: function()
							{
								getWnd('swMedSvidDeathStreamWindow').show({action: 'view', viewOnly: true});
							}
						},
						{
							text: lang['svidetelstva_o_perinatalnoy_smerti'],
							tooltip: lang['svidetelstva_o_perinatalnoy_smerti'],
							iconCls: 'svid-pdeath16',
							handler: function()
							{
								getWnd('swMedSvidPntDeathStreamWindow').show({action: 'view', viewOnly: true});
							}
						}
					]
				})
			},
			action_Smp:{
				nn: 'action_Smp',
				text:lang['kartyi_smp_poisk'],
				tooltip: lang['kartyi_smp_poisk'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'ambulance32',
				handler: function()
				{
					getWnd('swCmpCallCardSearchWindow').show({
						viewOnly: true
					});
				}
			},
			action_Lvn: {
				nn: 'action_Lvn',
				text: lang['lvn_poisk'],
				tooltip: lang['poisk_listkov_vremennoy_netrudosposobnosti'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls : 'lvn-search16',
				handler: function() {
					getWnd('swEvnStickViewWindow').show({viewOnly: true});
				}
			},
			action_Farm: {
				nn: 'action_Farm',
				tooltip: lang['apteka'],
				text: lang['apteka'],
				hidden: !isUserGroup(['MIACSuperAdmin']),
				iconCls: 'plan32',
				menu: new Ext.menu.Menu(
				{
					items:[
						sw.Promed.Actions.ContragentsAction, //Справочник контрагенты
						sw.Promed.Actions.DrugOstatRegistryAction,
						sw.Promed.Actions.DokNaklAction, //Приходные накладные
						sw.Promed.Actions.DocUcAction, //Документы учета медикаментов
						sw.Promed.Actions.ActSpisAction, //Акты списания медикаментов
						sw.Promed.Actions.DocOstAction, //Документы ввода остатков
						sw.Promed.Actions.InvVedAction //Инвентаризационные ведомости
					]
				})
			},
			action_Spr: {
				nn: 'action_Spr',
				tooltip: lang['spravochniki'],
				text: lang['spravochniki'],
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu(
				{
					items:
					[
						sw.Promed.Actions.MKB10Action, // Справочник МКБ-10
						sw.Promed.Actions.ClinicRecommendAction, // Клинические рекомендации
						sw.Promed.Actions.DrugDocumentSprAction, // Справочники системы учета медикаментов
						sw.Promed.Actions.DrugListAction, //Номенклатурный справочник
						sw.Promed.Actions.DrugNomenAction, //Номенклатурный справочник
						sw.Promed.Actions.DrugMNNAction, //Номенклатурный справочник
						sw.Promed.Actions.DrugTorgAction, //Справочник торговых наименований
						sw.Promed.Actions.PriceJNVLPAction, //Цены на ЖНВЛП
						sw.Promed.Actions.DrugMarkupAction, //Предельные надбавки на ЖНВЛП
						sw.Promed.Actions.DrugRMZAction, //Справочник РЗН
						sw.Promed.Actions.TariffAction, //Тарифы ЛЛО
						sw.Promed.Actions.PrepBlockSprAction, //Справочник фальсификатов и забракованных серий ЛС
						sw.Promed.Actions.GoodsUnitAction //Единицы измерения товара
					]
				})
			},
			action_staff_actions: {
				nn: 'action_staff_actions',
				disabled: isMedPersView(),
				text:langs('Действия'),
				menuAlign: 'tr',
				hidden: !isUserGroup(['MIACSuperAdmin', 'MIACAdminFRMR']),
				iconCls : 'database-export32',
				tooltip: langs('Действия'),
				menu: [{
					name: langs('Выгрузка регистра медработников для ФРМР новый'),
					text: langs('Выгрузка регистра медработников для ФРМР новый'),
					hidden: false,
					handler: function()
					{
						getWnd('swExportMedPersonalToXMLFRMPWindow').show();
					}.createDelegate(this)
				},{
					name: langs('Выгрузка штатного расписания для ФРМР новый'),
					text: langs('Выгрузка штатного расписания для ФРМР новый'),
					hidden: false,
					handler: function()
					{
						getWnd('swExportMedPersonalToXMLFRMPStaffWindow').show();
					}.createDelegate(this)
				}, {
					name: 'export_data_to_frmr',
					text: 'Передача данных в сервис ФРМР',
					handler: function() {
						getWnd('swExportToFRMRWindow').show();
					}.createDelegate(this)
				}, {
					name: 'export_data_to_frmo',
					text: 'Передача данных в сервис ФРМО',
					handler: function() {
						getWnd('swExportToFRMOWindow').show();
					}.createDelegate(this)
				}]
			},
			action_System:
			{
				nn: 'action_System',
				tooltip: langs('Система'),
				text: langs('Система'),
				hidden: !isUserGroup(['MIACSuperAdmin', 'MIACSysAdmin']),
				iconCls : 'settings32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						hidden: !isUserGroup(['MIACSuperAdmin']),
						text: langs('Структура БД'),
						handler: function() {
							getWnd('swReportDBStructureOptionsWindow').show();
						}
					}, {
						text: langs('Мониторинг системы'),
						handler: function () {
							getWnd('swSystemMonitorWindow').show();
						}
					}, {
						text: langs('Журнал авторизаций и событий безопасности'),
						hidden: (getRegionNick() !== 'vologda'),
						handler: function () {
							getWnd('swPhpLogAndUserSessionViewWindow').show();
						}
					}, {
						text: langs('Журнал событий системы'),
						handler: function () {
							getWnd('swPhpLogViewWindow').show();
						}
					}, {
						text: langs('Пользователи'),
						handler: function() {
							getWnd('swUsersTreeViewWindow').show();
						}
					}]
				})
			}
		};

		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls;
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = [
			'action_selectLpu',
			'action_openRecordMaster',
			'action_openEMK',
			'action_searchPerson',
			'action_reports',
			'action_stomSearh',
			'action_Par',
			'action_EvnPSSearh',
			'action_Polka',
			'action_Disp',
			'action_DispOrp',
			'action_DDYoung',
			'action_DispSearchView',
			'action_LLO',
			'action_Morf',
			'action_Register',
			'action_Svid',
			'action_Smp',
			'action_Lvn',
			'action_Farm',
			'action_MedOstat',
			'action_Spr',
			'action_staff_actions',
			'action_System'
		];

		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}

		this.leftMenu = new Ext.Panel(
		{
			region: 'center',
			id: form.id + '_mz',
			border: false,
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});
		
		this.leftPanel =
		{
			animCollapse: false,
			bodyStyle: 'padding-left: 5px',
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'awpwLeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					var el = null;
					el = form.findById(form.id + '_slid'); //_slid
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					return;
				}
			},
			border: true,
			title: ' ',
			split: true,
			items: [
				new Ext.Button(
				{	
					
					cls:'upbuttonArr',
					iconCls:'uparrow',
					disabled: false,
					handler: function() 
					{
						var el = form.findById(form.id + '_mz');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					border: false,
					layout:'border',
					id: form.id + '_slid', //_slid
					height:100,
					items:[this.leftMenu]
				},			
				new Ext.Button(
				{
				cls:'upbuttonArr',
				iconCls:'downarrow',
				style:{width:'48px'},
				disabled: false, 
				handler: function() 
				{
					var el = form.findById(form.id + '_mz');
					var d = el.body.dom;
					d.scrollTop +=38;
					
					
				}
				})]
		};


		this.LpuFilterPanel = new Ext.form.FieldSet(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			autoHeight: true,
			region: 'north',
			layout: 'column',
			title: lang['filtryi'],
			id: 'OrgLpuFilterPanel',
			items: 
			[{
				// Левая часть фильтров
				labelAlign: 'top',
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px;',
				columnWidth: .44,
				items: 
				[{
					name: 'Org_Name',
					anchor: '100%',
					disabled: false,
					fieldLabel: lang['naimenovanie_organizatsii'],
					tabIndex: 0,
					xtype: 'textfield',
					id: 'wpmzOrg_Name'
				},
				{
					xtype: 'hidden',
					anchor: '100%'
				}]
			},
			{
				// Средняя часть фильтров
				labelAlign: 'top',
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .44,
				items:
				[{
					name: 'Org_Nick',
					anchor: '100%',
					disabled: false,
					fieldLabel: lang['kratkoe_naimenovanie'],
					tabIndex: 0,
					xtype: 'textfield',
					id: 'wpmzOrg_Nick'
				},
				{
					xtype: 'hidden',
					anchor: '100%'
				}]
			},
			{
				// Правая часть фильтров (кнопка)
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .12,
				items:
				[{
					xtype: 'button',
					text: lang['nayti'],
					tabIndex: 4217,
					minWidth: 110,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'wpmzButtonSetFilter',
					handler: function ()
					{
						Ext.getCmp('swMiacWorkPlaceWindow').loadGridWithFilter();
					}
				},
				{
					xtype: 'button',
					text: lang['sbros'],
					tabIndex: 4218,
					minWidth: 110,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'wmpzButtonUnSetFilter',
					handler: function ()
					{
						Ext.getCmp('swMiacWorkPlaceWindow').loadGridWithFilter(true);
					}
				}]
			}],
			keys: [{
				key: [
					Ext.EventObject.ENTER
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					
					Ext.getCmp('swMiacWorkPlaceWindow').loadGridWithFilter();
				},
				stopEvent: true
			}]
		});

		// Организации
		this.LpuGrid = new sw.Promed.ViewFrame(
		{
			id: 'wpmzLpuGridPanel',
			tbar: this.gridToolbar,
			region: 'center',
			layout: 'fit',
			paging: true,
			object: 'Org',
			dataUrl: '/?c=Org&m=getOrgView',
			keys: [{
				key: [
					Ext.EventObject.F6
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					var grid = Ext.getCmp('wpmzLpuGridPanel');
					if (!grid.getAction('action_new').isDisabled()) {
						if (e.altKey) {
							AddRecordToUnion(
								grid.getGrid().getSelectionModel().getSelected(),
								'Org',
								lang['organizatsii'],
								function () {
									grid.loadData();
								}
							)
						}
					}
				},
				stopEvent: true
			}],
			//toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'Org_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', type: 'int', header: lang['id_lpu'], key: true},
				{name: 'Org_IsAccess', type:'checkbox', header: lang['dostup_v_sistemu'], width: 60},
				{name: 'DLO', type:'checkbox', header: lang['llo'], width: 40},
				{name: 'OMS', type:'checkbox', header: lang['oms'], width: 40},
				{id: 'Lpu_Ouz', name: 'Lpu_Ouz', header: lang['kod_ouz'], width: 80},
				{name: 'Org_Name', id: 'autoexpand', header: lang['polnoe_naimenovanie']},
				{name: 'Org_Nick', header: lang['kratkoe_naimenovanie'], width: 240},
				{name: 'KLArea_Name', header: lang['territoriya'], width: 160},
				{name: 'Org_OGRN', header: lang['ogrn'], width: 120},
				{name: 'Lpu_begDate', header: lang['data_nachala_deyatelnosti'], width: 80},
				{name: 'Lpu_endDate', header: lang['data_zakryitiya'], width: 80},
				// Поля для отображения в дополнительной панели
				{name: 'UAddress_Address', hidden: true},
				{name: 'PAddress_Address', hidden: true}
			],
			actions:
			[
				{name:'action_add', hidden: true},
				{name:'action_edit', iconCls : 'x-btn-text', icon: 'img/icons/lpu16.png', text: lang['pasport_mo'], handler: function()
					{
						this.Lpu_id = Ext.getCmp('wpmzLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
						getWnd('swLpuPassportEditWindow').show({
							action: 'view',
							Lpu_id: this.Lpu_id
						});
					}
				},		
				{name:'action_view', iconCls : 'x-btn-text', icon: 'img/icons/lpu-struc16.png', text: lang['struktura_mo'], handler: function()
					{
						this.Lpu_id = Ext.getCmp('wpmzLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
						getWnd('swLpuStructureViewForm').show({
							action: 'view',
							Lpu_id: this.Lpu_id
						});
					}
				},
				{name:'action_delete', hidden: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('swMiacWorkPlaceWindow');
				var form = Ext.getCmp('wpmzLpuGridPanel');
				if ( win.mode && win.mode == 'lpu')
				/*{
					var Lpu_id = form.ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
					form.getAction('action_edit').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
					form.getAction('action_view').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
				}*/
				var UAddress_Address = record.get('UAddress_Address');
				var PAddress_Address = record.get('PAddress_Address');
				win.LpuDetailTpl.overwrite(win.LpuDetailPanel.body, {UAddress_Address:UAddress_Address, PAddress_Address:PAddress_Address}); 
			}
		});


		this.LpuGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('Lpu_endDate')!=null && row.get('Lpu_endDate').length > 0)
					cls = cls+'x-grid-rowgray ';
				return cls;
			}
		});		
		var LpuDetailTplMark = 
		[
			'<div style="height:44px;">'+
				'<div>Юридический адрес: <b>{UAddress_Address}</b></div>'+
				'<div>Фактический адрес: <b>{PAddress_Address}</b></div>'+
			'</div>'
		];
		this.LpuDetailTpl = new Ext.Template(LpuDetailTplMark);
		this.LpuDetailPanel = new Ext.Panel(
		{
			id: 'LpuDetailPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			region: 'south',
			border: true,
			frame: true,
			height: 44,
			maxSize: 44,
			html: ''
		});

		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.LpuFilterPanel,
				this.leftPanel,
				{
					layout: 'fit',
					region: 'center',
					border: false,
					items:
					[
						this.LpuGrid
					]
				},
				this.LpuDetailPanel				
			],
			buttons: 
			[{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_MPSCHED + 98), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {this.hide();}.createDelegate(this)
			}]
		});

		sw.Promed.swMiacWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});