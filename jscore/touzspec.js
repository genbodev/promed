/**
* Загрузчик модуля с обработчиком onReady
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Init
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
*               Bykov Stas aka Savage (savage1981@gmail.com)
* @version      10.04.2009
*/

// TODO: Требует тщательного причесывания и продумывания логики скрытия/отображения меню (+ потому что меню надо будет выводить согласно правам).

var is_ready = false;

Ext.ns('sw.codeInfo');
Ext.ns('sw.notices');
Ext.ns('sw.adminnotices');
sw.codeInfo = {};
sw.notices = [];
sw.adminnotices = [];
sw.firstadminnotice = 1;
sw.readOnly = true;
var taskbar = null;
	//var $isFarmacy = false;

// для хранения состояний гридов/форм с stateful: true
Ext.state.Manager.setProvider(new Ext.state.CookieProvider({
    expires: new Date(new Date().getTime()+(1000*60*60*24*7)) // на 7 дней
}));
Ext.override(Ext.Component,{ stateful:false }); // по умолчанию состояния не сохраняются

var swLpuFilialGlobalStore = null;
var swLpuBuildingGlobalStore = null;
var swLpuSectionGlobalStore = null;
var swLpuSectionWardGlobalStore = null;
var swLpuUnitGlobalStore = null;
var swMedServiceGlobalStore = null;
var swMedStaffFactGlobalStore = null;
var swProMedUserGlobalStore = null;
var swFederalKladrGlobalStore = null;

//uidesigner
/*	var phpSupport; // Flag is set in index.php to indicate whe support PHP
	var cookies = new Ext.state.CookieProvider();
	var options = cookies.get('Designer.Options') ||
	{
	dock : 1,
	compressed : 0,
	codepress : 1,
	autoresize : 1,
	floatheight : 480,
	floatwidth : 580,
	cmpfiles : "{0}ThirdParty.Components.json"
	};

	//Url based actions
	//var windowMode = Ext.ux.UTIL.getUrlAction('window',options.float)==1; //Change this flag to true if you want designer to be a window
	var docked = Ext.ux.UTIL.getUrlAction('docked',options.dock)==1;
	var nocache = Ext.ux.UTIL.getUrlAction('nocache',options.nocache)==1;
	var autoResize = Ext.ux.UTIL.getUrlAction('autoresize',options.autoresize)==1;
	var cmpfiles = (options.cmpfiles || "").replace('\r').split("\n");
	var designer; //Variable used to save the designer plugin
	
*/


var isTestLpu = UserLogin.inlist( ['testpol', 'permmsc1', 'permmsc9', 'permgkb2', 'permgkb7', 'permgdkb15','pr9trav1', 'pr9ter1', 'pr9gin1', 'pr9nevr1', 'pr9rodd1', 'Pech9s2', 'Pech9s4', 'permgdkb1', 'PermGDKB1_POR', 'PermGDKB1_BIL', 'PermGDKB1_BEV', 'PermGDKB1_LLV', 'PermGDKB1_AOV','pgp2test', 'msv', 'msv2' ] );

// Функция загрузки модулей Промед
function loadModules()
{
	is_ready = true;
	
	/**
	* Запрос к серверу для получения списка доступных врачу служб
	* поскольку функция используется еще и на форме ЭМК, сделаю ее пока что глобальной (var не использовать!!!)
	*/
	openSelectServiceWindow = function(params) {
		if(!params) return false;
		Ext.Ajax.request({
			url: '/?c=MedService&m=defineMedServiceListOnMedPersonal',
			params: {MedPersonal_id: getGlobalOptions().medpersonal_id},
			callback: function(o, s, r) {
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if(obj.MedPersonal_FIO) {
						getGlobalOptions().CurMedPersonal_FIO = obj.MedPersonal_FIO;
					}
					getGlobalOptions().medservices = obj.medservices;
					getWnd('swSelectMedServiceWindow').show({ARMType: params.ARMType, onSelect: params.onSelect});
				}
			}
		});
	};

	// TODO: Проблема определения админа на клиенте в том, что эти данные всегда можно подделать для клиента
	// С одной стороны это хорошо, потому что позволяет набором инструкций получать доступ к определенному функционалу для тестирования в случае ошибки 
	isAdmin = (/SuperAdmin/.test(getGlobalOptions().groups));
	isFarmacyInterface = false; // хотя здесь можно просто написать фальш.

	// Конфиги акшенов
	sw.Promed.Actions = {
		swEvnPLEvnPSSearchAction: {
			text: lang['poisk_tap_kvs'],
			tooltip: lang['poisk_tap_kvs'],
			iconCls : 'test16',
			handler: function() {
				getWnd('swEvnPLEvnPSSearchWindow').show();
			},
			hidden: false
		},
		swEvnPLEvnPSViewAction: {
			text: lang['vyibor_tap_kvs'],
			tooltip: lang['vyibor_tap_kvs'],
			iconCls : 'test16',
			handler: function() {
				getWnd('swEvnPLEvnPSSearchWindow').show({
					Person_id: 421380
				});
			},
			hidden: false
		},
		EvnDirectionMorfoHistologicViewAction: {
			text: lang['napravleniya_na_patomorfogistologicheskoe_issledovanie'],
			tooltip: lang['jurnal_napravleniy_na_patomorfogistologicheskoe_issledovanie'],
			iconCls : 'pathomorph16',
			handler: function() {
				getWnd('swEvnDirectionMorfoHistologicViewWindow').show();
			},
			hidden: false
		},
		EvnStickViewAction: {
			text: lang['lvn_poisk'],
			tooltip: lang['poisk_listkov_vremennoy_netrudosposobnosti'],
			iconCls : 'lvn-search16',
			handler: function() {
				getWnd('swEvnStickViewWindow').show();
			},
			hidden: false //(!isAdmin || IS_DEBUG != 1)
		},
		EvnMorfoHistologicProtoViewAction: {
			text: lang['protokolyi_patomorfogistologicheskih_issledovaniy'],
			tooltip: lang['jurnal_protokolov_patomorfogistologicheskih_issledovaniy'],
			iconCls : 'pathomorph16',
			handler: function() {
				getWnd('swEvnMorfoHistologicProtoViewWindow').show();
			},
			hidden: false
		},
		EvnHistologicProtoViewAction: {
			text: lang['protokolyi_patologogistologicheskih_issledovaniy'],
			tooltip: lang['jurnal_protokolov_patologogistologicheskih_issledovaniy'],
			iconCls : 'pathohistproto16',
			handler: function() {
				getWnd('swEvnHistologicProtoViewWindow').show();
			},
			hidden: false
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
		EvnDirectionHistologicViewAction: {
			text: lang['napravleniya_na_patologogistologicheskoe_issledovanie'],
			tooltip: lang['jurnal_napravleniy_na_patologogistologicheskoe_issledovanie'],
			iconCls : 'pathohist16',
			handler: function() {
				getWnd('swEvnDirectionHistologicViewWindow').show();
			},
			hidden: false
		},
		PersonDoublesSearchAction: {
			text: lang['rabota_s_dvoynikami'],
			tooltip: lang['rabota_s_dvoynikami'],
			iconCls: 'doubles16',
			handler: function() {
				getWnd('swPersonDoublesSearchWindow').show();
			},
			hidden: !isAdmin
		},
		PersonDoublesModerationAction: {
			text: lang['moderatsiya_dvoynikov'],
			tooltip: lang['moderatsiya_dvoynikov'],
			iconCls: 'doubles-mod16',
			handler: function() {
				var params = {};
				if(!isAdmin && (isLpuAdmin() && isUserGroup('106'))){
					params.LpuOnly = true;
				}
				getWnd('swPersonDoublesModerationWindow').show(params);
			},
			hidden: !(isAdmin || (isLpuAdmin() && isUserGroup('106')))
		},
		PersonUnionHistoryAction: {
			text: lang['istoriya_moderatsii_dvoynikov'],
			tooltip: lang['istoriya_moderatsii_dvoynikov'],
			iconCls: 'doubles-history16',
			handler: function() {
				getWnd('swPersonUnionHistoryWindow').show();
			},
			hidden: true
		},
		UslugaComplexViewAction: {
			text: lang['kompleksnyie_uslugi'],
			tooltip: lang['kompleksnyie_uslugi'],
			iconCls: 'services-complex16',
			handler: function() {
				getWnd('swUslugaComplexViewWindow').show();
			},
			hidden: true
		},
		UslugaComplexTreeAction: {
			text: lang['kompleksnyie_uslugi'],
			tooltip: lang['kompleksnyie_uslugi'],
			iconCls: 'services-complex16',
			handler: function()
			{
			},
			hidden: true
		},
		RegistryViewAction: {
			text: lang['reestryi_schetov'],
			tooltip: lang['reestryi_schetov'],
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swRegistryViewWindow').show({Registry_IsNew: 1});
			},
			hidden: !(isUserGroup([ 'RegistryUser', 'RegistryUserReadOnly' ]) || isSuperAdmin()||getGlobalOptions().region.nick == 'ufa')
		},
		RegistryNewViewAction: {
			text: lang['reestryi_schetov_novyie'],
			tooltip: lang['reestryi_schetov'],
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swRegistryViewWindow').show({Registry_IsNew: 2});
			},
			hidden: !(isUserGroup([ 'LpuPowerUser', 'SuperAdmin' ]) && getRegionNick().inlist(['ufa']))
		},
		MiacExportAction: {
			text: lang['vyigruzka_dlya_miats'],
			tooltip: lang['vyigruzka_dannyih_dlya_miats'],
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swMiacExportWindow').show();
			},
			hidden: (getGlobalOptions().region.nick != 'ufa')
		},
		MiacExportSheduleOptionsAction: {
			text: lang['nastroyki_avtomaticheskoy_vyigruzki_dlya_miats'],
			tooltip: lang['nastroyki_avtomaticheskoy_vyigruzki_dlya_miats'],
			iconCls : 'settings16',
			handler: function() {
				getWnd('swMiacExportSheduleOptionsWindow').show();
			},
			hidden: (getGlobalOptions().region.nick != 'ufa')
		},
		/*RegistryEditAction: {
			text: lang['redaktirovanie_reestra_scheta'],
			tooltip: lang['redaktirovanie_reestra_scheta'],
			iconCls : 'x-btn-text',
			handler: function() {
				getWnd('swRegistryEditWindow').show({
					action: 'add',
					callback: Ext.emptyFn,
					onHide: Ext.emptyFn,
					RegistryType_id: 2
				});
			}
		},*/
/*
		DrugRequestPrintAllAction: {
			text: lang['pechat_zayavki'],
			tooltip: lang['pechat_zayavki_po_vyibrannoy_mo_ili_po_vsem_mo'],
			iconCls : 'x-btn-text',
			handler: function() {
				getWnd('swDrugRequestPrintAllWindow').show({
					onHide: Ext.emptyFn
				});
			}
		},
*/
		DrugTorgLatinNameEditAction: {
			text: WND_DLO_DRUGTORGLATINEDIT,
			tooltip: lang['redaktirovanie_latinskogo_naimenovaniya_medikamenta'],
			iconCls : 'drug-viewtorg16',
			handler: function()
			{
				getWnd('swDrugTorgViewWindow').show();
			},
			hidden:(getGlobalOptions().region.nick == 'saratov'||getGlobalOptions().region.nick == 'pskov')
		},

		DrugMnnLatinNameEditAction: {
			text: WND_DLO_DRUGMNNLATINEDIT,
			tooltip: lang['redaktirovanie_latinskogo_naimenovaniya_mnn'],
			iconCls : 'drug-viewmnn16',
			handler: function()
			{
				getWnd('swDrugMnnViewWindow').show({
					privilegeType: 'all'
				});
			},
			hidden:(getGlobalOptions().region.nick == 'saratov'||getGlobalOptions().region.nick == 'pskov')
		},

		PersonCardSearchAction: {
			text: WND_POL_PERSCARDSEARCH,
			tooltip: lang['poisk_kartyi_patsienta'],
			iconCls : 'card-search16',
			handler: function()
			{
				getWnd('swPersonCardSearchWindow').show();
			}
		},

		PersonCardViewAllAction: {
			text: WND_POL_PERSCARDVIEWALL,
			tooltip: lang['kartoteka_rabota_so_vsey_kartotekoy'],
			iconCls : 'card-view16',
			handler: function()
			{
				getWnd('swPersonCardViewAllWindow').show();
			}
		},

		PersonCardStateViewAction: {
			text: WND_POL_PERSCARDSTATEVIEW,
			tooltip: lang['prosmotr_jurnala_dvijeniya_po_kartoteke_patsientov'],
			iconCls : 'card-state16',
			handler: function()
			{
				getWnd('swPersonCardStateViewWindow').show();
			}
		},

		AutoAttachViewAction: {
			text: 'Групповое прикрепление',
			tooltip: 'Групповое прикрепление',
			iconCls : 'card-state16',
			hidden: !isAdmin,
			handler: function()
			{
				var id_salt = Math.random();
				var win_id = 'report' + Math.floor(id_salt*10000);
				// собственно открываем окно и пишем в него
				var win = window.open('/?c=AutoAttach&m=doAutoAttach', win_id);
			}
		},

		PersonDispSearchAction: {
			text: WND_POL_PERSDISPSEARCH,
			tooltip: lang['poisk_dispansernoy_kartyi_patsienta'],
			iconCls : 'disp-search16',
			handler: function()
			{
				getWnd('swPersonDispSearchWindow').show();
			},
			hidden: false//!(isAdmin || isTestLpu)
		},
		PersonDispViewAction: {
			text: WND_POL_PERSDISPSEARCHVIEW,
			tooltip: lang['prosmotr_dispansernoy_kartyi_patsienta'],
			iconCls : 'disp-view16',
			handler: function()
			{
				getWnd('swPersonDispViewWindow').show({mode: 'view'});
			},
			hidden: false//!(isAdmin || isTestLpu)
		},
		EvnPLEditAction: {
			text: lang['talon_ambulatornogo_patsienta_poisk'],
			tooltip: lang['poisk_talona_ambulatornogo_patsienta'],
			iconCls : 'pol-eplsearch16',
			handler: function()
			{
				getWnd('swEvnPLSearchWindow').show();
			}
		},
		LpuStructureViewAction: {
			text: lang['struktura_mo'],
			tooltip: lang['struktura_mo'],
			iconCls : 'lpu-struc16',
			hidden: !isAdmin && !isLpuAdmin() && !isCadrUserView() && !isUserGroup('OuzSpec'),
			handler: function()
			{
				getWnd('swLpuStructureViewForm').show();
			}
		},
		
		OrgStructureViewAction: {
			text: lang['struktura_organizatsii'],
			tooltip: lang['struktura_organizatsii'],
			iconCls : 'lpu-struc16',
			hidden: (!isAdmin && !isOrgAdmin()) || !isDebug(),
			handler: function()
			{
				getWnd('swOrgStructureWindow').show();
			}
		},
		
		FundHoldingViewAction: {
			text: lang['fondoderjanie'],
			tooltip: lang['fondoderjanie'],
			iconCls : 'lpu-struc16',
			hidden: !isAdmin ,//&& !getGlobalOptions()['mp_is_zav'] && !getGlobalOptions()['mp_is_uch'],
			handler: function()
			{
				getWnd('swFundHoldingViewForm').show();
			}
		},
		
		LgotFindAction: {
			text: MM_DLO_LGOTSEARCH,
			tooltip: lang['poisk_lgotnikov'],
			iconCls : 'lgot-search16',
			handler: function()
			{
				getWnd('swPrivilegeSearchWindow').show();
			}
		},
		LgotAddAction: {
			text: MM_DLO_LGOTADD,
			tooltip: lang['dobavlenie_lgotnika'],
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
					return false;
				}

				if (getWnd('swPrivilegeEditWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_lgotyi_uje_otkryito']);
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd('swPrivilegeEditWindow').show({
							params: {
								action: 'add',
								Person_id: person_data.Person_id,
								Server_id: person_data.Server_id
							}
						});
					},
					searchMode: 'all'
				});
			}
		},
		EvnUdostViewAction: {
			text: MM_DLO_UDOSTLIST,
			tooltip: lang['prosmotr_udostovereniy'],
			iconCls : 'udost-list16',
			handler: function()
			{
				getWnd('swUdostViewWindow').show();
			}
		},
		EvnUdostAddAction: {
			text: MM_DLO_UDOSTADD,
			tooltip: lang['dobavlenie_udostovereniy'],
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
					return false;
				}

				if (getWnd('swEvnUdostEditWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_udostovereniya_uje_otkryito']);
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd('swEvnUdostEditWindow').show({
							action: 'add',
							onHide: function() {
								getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
							},
							Person_id: person_data.Person_id,
							PersonEvn_id: person_data.PersonEvn_id,
							Server_id: person_data.Server_id
						});
					},
					searchMode: 'all'
				});
			}
		},
		EvnReceptAddAction: {
			text: MM_DLO_RECADD,
			tooltip: lang['dobavlenie_retsepta'],
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
					return false;
				}

				if (getWnd('swEvnReceptEditWindow').isVisible())
				{
					Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_uje_otkryito']);
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						getWnd('swEvnReceptEditWindow').show({
							action: 'add',
							onHide: function() {
								getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
							},
							Person_id: person_data.Person_id,
							PersonEvn_id: person_data.PersonEvn_id,
							Server_id: person_data.Server_id
						});
					},
					searchMode: 'all'
				});
			}
		},
		EvnReceptFindAction: {
			text: MM_DLO_RECSEARCH,
			tooltip: lang['poisk_retseptov'],
			iconCls : 'receipt-search16',
			handler: function()
			{
				getWnd('swEvnReceptSearchWindow').show();
			}
		},
		EvnReceptInCorrectFindAction: {
			text: lang['jurnal_otsrochki'],
			tooltip: lang['jurnal_otsrochki'],
			iconCls : 'receipt-incorrect16',
			handler: function()
			{
				getWnd('swReceptInCorrectSearchWindow').show();
			}
		},
		PersonPrivilegeWOWSearchAction: {
			text: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
			tooltip: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
			handler: function()
			{
				getWnd('swPersonPrivilegeWOWSearchWindow').show();
			}
		},
		PersonDispWOWSearchAction: {
			text: lang['obsledovaniya_vov_poisk'],
			tooltip: lang['obsledovaniya_vov_poisk'],
			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
			handler: function()
			{
				getWnd('EvnPLWOWSearchWindow').show();
			}
		},
		PersonDopDispSearchAction: {
			text: MM_POL_PERSDDSEARCH,
			tooltip: lang['dopolnitelnaya_dispanserizatsiya_poisk'],
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDopDispSearchWindow').show();
			}
		},
		EvnPLDopDispSearchAction: {
			text: lang['talon_po_dopolnitelnoy_dispanserizatsii_vzroslyih_do_2013g_poisk'],
			tooltip: lang['talon_po_dopolnitelnoy_dispanserizatsii_vzroslyih_do_2013g_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispDopSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		PersonDispOrpSearchAction: {
			text: lang['registr_detey-sirot_s_2013g_poisk'],
			tooltip: lang['registr_detey-sirot_s_2013g_poisk'],
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrp13SearchWindow').show({
					CategoryChildType: 'orp'
				});
			},
			hidden: false //!isAdmin
		},
		PersonDispOrpAdoptedSearchAction: {
			text: lang['registr_detey-sirot_usyinovlennyih_poisk'],
			tooltip: lang['registr_detey-sirot_usyinovlennyih_poisk'],
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrp13SearchWindow').show({
					CategoryChildType: 'orpadopted'
				});
			},
			hidden: false //!isAdmin
		},
		PersonDispOrpPeriodSearchAction: {
			text: lang['registr_periodicheskih_osmotrov_nesovershennoletnih_poisk'],
			tooltip: lang['registr_periodicheskih_osmotrov_nesovershennoletnih_poisk'],
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpPeriodSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionSearchAction: {
			text: lang['periodicheskie_osmotryi_nesovershennoletnih_poisk'],
			tooltip: lang['periodicheskie_osmotryi_nesovershennoletnih_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		PersonDispOrpProfSearchAction: {
			text: lang['napravleniya_na_profilakticheskie_osmotryi_nesovershennoletnih_poisk'],
			tooltip: lang['napravleniya_na_profilakticheskie_osmotryi_nesovershennoletnih_poisk'],
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpProfSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionProfSearchAction: {
			text: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
			tooltip: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionProfSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionProfSecSearchAction: {
			text: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
			tooltip: lang['profilakticheskie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionProfSecSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		PersonDispOrpPredSearchAction: {
			text: lang['napravleniya_na_predvaritelnyie_osmotryi_nesovershennoletnih_poisk'],
			tooltip: lang['napravleniya_na_predvaritelnyie_osmotryi_nesovershennoletnih_poisk'],
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpPredSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionPredSearchAction: {
			text: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
			tooltip: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_1_etap_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionPredSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionPredSecSearchAction: {
			text: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
			tooltip: lang['predvaritelnyie_osmotryi_nesovershennoletnih_-_2_etap_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionPredSecSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenExportAction: {
			text: lang['eksport_kart_po_dispanserizatsii_nesovershennoletnih'],
			tooltip: lang['eksport_kart_po_dispanserizatsii_nesovershennoletnih'],
			iconCls : 'database-export16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenExportWindow').show();
			},
			hidden: false
		},
		EvnPLDispOrpSearchAction: {
			text: lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_poisk'],
			tooltip: lang['karta_dispanserizatsii_nesovershennoletnego_-_1_etap_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispOrp13SearchWindow').show({
					stage: 1
				});
			},
			hidden: false //!isAdmin
		},
		EvnPLDispOrpSecSearchAction: {
			text: lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_poisk'],
			tooltip: lang['karta_dispanserizatsii_nesovershennoletnego_-_2_etap_poisk'],
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispOrp13SearchWindow').show({
					stage: 2
				});
			},
			hidden: false //!isAdmin
		},
		EvnPLDispDop13SearchAction: {
			text: MM_POL_EPLDD13SEARCH,
			tooltip: MM_POL_EPLDD13SEARCH,
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispDop13SearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispProfSearchAction: {
			text: MM_POL_EPLDPSEARCH,
			tooltip: MM_POL_EPLDPSEARCH,
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispProfSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispScreenSearchAction: {
			text: MM_POL_EPLDSSEARCH,
			tooltip: MM_POL_EPLDSSEARCH,
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispScreenSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispScreenChildSearchAction: {
			text: MM_POL_EPLDSCSEARCH,
			tooltip: MM_POL_EPLDSCSEARCH,
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispScreenChildSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispDop13SecondSearchAction: {
			text: MM_POL_EPLDD13SECONDSEARCH,
			tooltip: MM_POL_EPLDD13SECONDSEARCH,
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispDop13SecondSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeen14SearchAction: {
			text: lang['dispanserizatsiya_14-letnih_podrostkov_poisk'],
			tooltip: lang['dispanserizatsiya_14-letnih_podrostkov_poisk'],
			iconCls : 'dopdisp-teens-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeen14SearchWindow').show();
			},
			hidden: false
		},
		ReestrsViewAction: {
			text: lang['reestryi_schetov'],
			tooltip: lang['reestryi_schetov'],
			iconCls : 'service-reestrs16',
			handler: function()
			{
				Ext.Msg.alert(lang['soobschenie'], lang['dannyiy_modul_poka_nedostupen']);
			}
		},
		DrugRequestViewAction: {
			text: lang['zayavka_na_ls_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
			tooltip: lang['zayavki_na_ls_po_obscheterapevticheskoy_gruppe_zabolevaniy'],
			iconCls : 'drug-request16',
			handler: function()
			{
				getWnd('swDrugRequestViewForm').show();
			},
			hidden: (getGlobalOptions().region.nick!='perm')
		},
		/*NewDrugRequestViewAction: {
			text: (getGlobalOptions().region && getGlobalOptions().region.nick=='saratov')?lang['zayavka_na_lekarstvennyie_sredstva']:lang['zayavka_na_ls_po_osobyim_gruppam_zabolevaniy'],
			tooltip: (getGlobalOptions().region && getGlobalOptions().region.nick=='saratov')?lang['zayavka_na_lekarstvennyie_sredstva']:lang['zayavka_na_ls_po_osobyim_gruppam_zabolevaniy'],
			iconCls : 'drug-request16',
			handler: function()
			{
				getWnd('swNewDrugRequestViewForm').show();
			},
			hidden: (getGlobalOptions().region.nick == 'saratov'||getGlobalOptions().region.nick == 'pskov')
		},*/
		OrgFarmacyViewAction: {
			text: MM_DLO_OFVIEW,
			tooltip: lang['rabota_s_prosmotrom_i_redaktirovaniem_aptek'],
			iconCls : 'farmview16',
			handler: function()
			{
				getWnd('swOrgFarmacyViewWindow').show();
			},
			hidden : !isAdmin
		},
		OstAptekaViewAction: {
			text: MM_DLO_MEDAPT,
			tooltip: lang['rabota_s_ostatkami_medikamentov_po_aptekam'],
			iconCls : 'drug-farm16',
			handler: function()
			{
				getWnd('swDrugOstatByFarmacyViewWindow').show();
			}
		},
		OstSkladViewAction: {
			text: MM_DLO_MEDSKLAD,
			tooltip: lang['rabota_s_ostatkami_medikamentov_na_aptechnom_sklade'],
			iconCls : 'drug-sklad16',
			handler: function()
			{
				getWnd('swDrugOstatBySkladViewWindow').show();
			}
		},
		OstDrugViewAction: {
			text: MM_DLO_MEDNAME,
			tooltip: lang['rabota_s_ostatkami_medikamentov_po_naimenovaniyu'],
			iconCls : 'drug-name16',
			handler: function()
			{
				getWnd('swDrugOstatViewWindow').show();
			}
		},
		ReportStatViewAction: {
			text: lang['statisticheskaya_otchetnost'],
			tooltip: lang['statisticheskaya_otchetnost'],
			iconCls : 'reports16',
			hidden : false,
			handler: function()
			{
				// Пример предварительной загрузки блока кода 
				if (sw.codeInfo.loadEngineReports)
				{
					getWnd('swReportEndUserWindow').show();
				}
				else 
				{
                    Ext.Ajax.request({ //Если список АРМов для пользователя не подгрузился, то отображаются все отчеты, независимо от прав пользователя.
                                        // Поэтому сделал принудительный вызов метода перед открытием отчетов http://redmine.swan.perm.ru/issues/35151
                        success: function(response, options) {
                            getWnd('reports').load(
                                {
                                    callback: function(success)
                                    {
                                        sw.codeInfo.loadEngineReports = success;
                                        // здесь можно проверять только успешную загрузку
                                        getWnd('swReportEndUserWindow').show();
                                    }
                                });
                        },
                        url: '?c=User&m=getMSFList'
                    });

					/*getWnd('reports').load(
					{
						callback: function(success) 
						{
							sw.codeInfo.loadEngineReports = success;
							// здесь можно проверять только успешную загрузку 
							getWnd('swReportEndUserWindow').show();
						}
					});*/
				}
			}
		},
		EventsWindowTestAction: {
			text: lang['test_tolko_na_testovom'],
			tooltip: lang['test'],
			iconCls : 'test16',
			hidden:((IS_DEBUG!=1) || (getGlobalOptions().region.nick == 'saratov')),
			handler: function()
			{
				getWnd('swTestEventsWindow').show();
			}
		},
		TemplatesWindowTestAction: {
			text: lang['test_shablonov'],
			tooltip: lang['test_shablonov'],
			iconCls : 'test16',
			hidden: true,
			handler: function()
			{
			}
		},
		TemplatesEditWindowAction: {
			text: lang['redaktor_shablonov'],
			tooltip: lang['redaktor_shablonov'],
			iconCls : 'test16',
			hidden: true,
			handler: function()
			{
			}
		},
		TemplateRefValuesOpenAction: {
			text: lang['baza_referentnyih_znacheniy'],
			tooltip: lang['redaktor_referentnyih_znacheniy'],
			iconCls : 'test16',
			hidden: !isAdmin,
			handler: function()
			{
				getWnd('swTemplateRefValuesViewWindow').show();
			}
		},
		GlossarySearchAction: {
			text: lang['glossariy'],
			tooltip: lang['glossariy'],
			iconCls : 'glossary16',
			//hidden: false,
			hidden: (getGlobalOptions().region.nick=='saratov'),
			handler: function()
			{
				getWnd('swGlossarySearchWindow').show();
			}
		},
		ReportDBStructureAction: {
			text: lang['struktura_bd'],
			tooltip: lang['struktura_bd'],
			iconCls : 'test16',
			hidden:(!isAdmin),
			handler: function()
			{
				getWnd('swReportDBStructureOptionsWindow').show();
			}
		},
		UserProfileAction: {
			text: lang['moy_profil'],
			tooltip: lang['profil_polzovatelya'],
			iconCls : 'user16',
			hidden: false,
			handler: function()
			{
				args = {}
				args.action = 'edit';
				getWnd('swUserProfileEditWindow').show(args);
			}
		},
		PromedHelp: {
			text: lang['vyizov_spravki'],
			tooltip: lang['pomosch_po_programme'],
			iconCls : 'help16',
			handler: function()
			{
				ShowHelp(lang['soderjanie']);
			}
		},
		PromedForum: {
			text: lang['forum_podderjki'],
			iconCls: 'support16',
			xtype: 'tbbutton',
			handler: function() {
				window.open(ForumLink);
			}
		},		
		swShowTestWindowAction: {
			text: lang['testovoe_okno'],
			tooltip: lang['otkryit_testovoe_okno'],
			iconCls : 'test16',
			handler: function() {
				//getWnd('swTestWindow').show();
                getWnd('swWorkPlaceWindow').show();
			},
			hidden: !isAdmin || !isDebug()
		},
		PromedAbout: {
			text: lang['o_programme'],
			tooltip: lang['informatsiya_o_programme'],
			iconCls : 'promed16',
			testId: 'mainmenu_help_about',
			handler: function()
			{
				getWnd('swAboutWindow').show();
			}
		},
		PromedExit: {
			text:lang['vyihod'],
			iconCls: 'exit16',
			handler: function()
			{
					sw.swMsg.show({
							title: lang['podtverdite_vyihod'],
							msg: lang['vyi_deystvitelno_hotite_vyiyti'],
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
									if ( buttonId == 'yes' ) {
				window.onbeforeunload = null;
				window.location=C_LOGOUT;
				}
					}
			});
			}
		},
        ConvertAction:{
            text: lang['konvertatsiya_poley'],
            tooltip: lang['konvertatsiya'],
            iconCls : 'eph16',
            handler: function()
            {
                getWnd('swConvertEditWindow').show();
            },
            hidden:((IS_DEBUG!=1) || (getGlobalOptions().region.nick == 'saratov'))
        },
		swLdapAttributeChangeAction:{
            text: lang['zamena_atributa_v_ldap'],
            tooltip: lang['zamena_atributa_v_ldap'],
            iconCls : 'eph16',
            handler: function()
            {
                getWnd('swLdapAttributeChangeWindow').show();
            },
            hidden: !isSuperAdmin()
        },
		swImportSMPCardsTest:{
            text: lang['test_importa_kart_smp'],
            tooltip: lang['test_importa_kart_smp'],
            iconCls : 'eph16',
            handler: function()
            {
                getWnd('swImportSMPCardsTestWindow').show();
            },
            hidden: !isSuperAdmin()
        },
		swDicomViewerAction:{
            text: lang['prosmotrschik_dicom'],
            tooltip: lang['prosmotrschik_dicom'],
            iconCls : 'eph16',
            handler: function()
            {
                getWnd('swDicomViewerWindow').show();
            },
            hidden: (IS_DEBUG!=1 || !isSuperAdmin())
        },
		TestAction: {
			text: lang['test_tolko_na_testovom'],
			tooltip: lang['test'],
			iconCls : 'eph16',
			handler: function()
			{
				// Инициализация всех окон промед
				/*
				for(var key in sw.Promed)
				{
					//log(key);
					if ((key.indexOf('Form') == -1) && (key.indexOf('Window') == -1))
					{
						// Не форма и не окно 100%
					}
					else 
					{
						try 
						{
							var win = swGetWindow(key);
							if (win!=null)
							{
								log(key, ';', win.title);
							}
						}
						catch (e)
						{
							//log('Это не форма: ', e);
						}
					}
					//log(key);
				};
				*/
				//getWnd('swPersonEPHForm').show({Person_id: 499527, Server_id: 10, PersonEvn_id: 104170589});
				getWnd('swEvnUslugaOrderEditWindow').show({LpuSection_id:10});
			},
			hidden: ((IS_DEBUG!=1) || (getGlobalOptions().region.nick == 'saratov'))
		},
		Test2Action: {
			text: lang['poluchit_s_analizatora_tolko_na_testovom'],
			tooltip: lang['test'],
			iconCls : 'eph16',
			handler: function()
			{
				//getWnd('swPersonEPHForm').show({Person_id: 499527, Server_id: 10, PersonEvn_id: 104170589});
				getWnd('swTestLoadEditWindow').show();
			},
			hidden: ((IS_DEBUG!=1) || (getGlobalOptions().region.nick == 'saratov'))
		},
		MedPersonalPlaceAction: {
			text: lang['meditsinskiy_personal_mesta_rabotyi_staryiy_ermp'],
			tooltip: lang['meditsinskiy_personal_mesta_rabotyi_staryiy_ermp'],
			iconCls : 'staff16',
			hidden: ((!isAdmin && (getGlobalOptions().region.nick != 'ufa')) || (getGlobalOptions().region.nick == 'pskov')),
			handler: function()
			{
				getWnd('swMedPersonalViewWindow').show();
			}
		},
		MedWorkersAction: {
			text: lang['medrabotniki'],
			tooltip: lang['medrabotniki'],
			iconCls : 'staff16',
			hidden : ((getGlobalOptions().region.nick == 'ufa') || (!isAdmin && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ))),
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'MedWorker', main_center_panel);
			}
		},
		MedPersonalSearchAction: {
			text: lang['meditsinskiy_personal_prosmotr_staryiy_ermp'],
			tooltip: lang['meditsinskiy_personal_prosmotr_staryiy_ermp'],
			iconCls : 'staff16',
			hidden : !MP_NOT_ERMP,
			handler: function()
			{
				getWnd('swMedPersonalSearchWindow').show();
			}
		},
		swLgotTreeViewAction: {
			text: lang['registr_lgotnikov_spisok'],
			tooltip: lang['prosmotr_lgot_po_kategoriyam'],
			iconCls : 'lgot-tree16',
			handler: function()
			{
				getWnd('swLgotTreeViewWindow').show();
			}
		},
		swAttachmentDemandAction: {
			text: lang['zayavleniya_na_prikreplenie_mo'],
			tooltip: lang['prosmotr_i_redaktirovanie_zayavleniy_na_prikreplenie_k_mo'],
			iconCls : 'attach-demand16',
			hidden : !isAdmin,
			handler: function()
			{
				getWnd('swAttachmentDemandListWindow').show();
			}
		},
		swChangeSmoDemandAction: {
			text: lang['zayavleniya_na_prikreplenie_smo'],
			tooltip: lang['prosmotr_i_redaktirovanie_zayavleniy_na_prikreplenie_k_smo'],
			iconCls : 'attach-demand16',
			hidden : !isAdmin,
			handler: function()
			{
				getWnd('swChangeSmoDemandListWindow').show();
			}
		},
		swUsersTreeViewAction: {
			text: lang['polzovateli'],
			tooltip: lang['prosmotr_i_redaktirovanie_polzovateley'],
			iconCls : 'users16',
			hidden: !getGlobalOptions().superadmin && !isLpuAdmin(),
			handler: function()
			{
				getWnd('swUsersTreeViewWindow').show();
			}
		},
		swGroupsViewAction: {
			text: lang['gruppyi'],
			tooltip: lang['prosmotr_i_redaktirovanie_grupp'],
			iconCls : 'users16',
			hidden: !isSuperAdmin(),
			handler: function()
			{
				getWnd('swGroupViewWindow').show();
			}
		},
		swOptionsViewAction: {
			text: lang['nastroyki'],
			tooltip: lang['prosmotr_i_redaktirovanie_nastroek'],
			iconCls : 'settings16',
			handler: function()
			{
				getWnd('swOptionsWindow').show();
			}
		},
		swTimeTableAction: {
			text: lang['raspisanie'],
			tooltip: lang['raspisanie'],
			iconCls: 'eph-timetable-top16',
			handler: function()
			{
				getWnd('swRecordMasterWindow').show();
			}
		},
		swOpenEmkAction: {
			text: lang['otkryit_emk'],
			tooltip: lang['nayti_cheloveka_otkryit_ego_emk'],
			iconCls: 'patient-search16',
			hidden: (getRegionNick() != 'perm' || !getGlobalOptions().groups || getGlobalOptions().groups.toString().indexOf('OuzSpec') == -1),
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
		swPersonSearchAction: {
			text: lang['chelovek_poisk'],
			tooltip: lang['poisk_lyudey'],
			iconCls: 'patient-search16',
			handler: function()
			{
				getWnd('swPersonSearchWindow').show({
					onSelect: function(person_data) {
						if (!person_data.afterAdd && person_data.accessType != 'list') {
							getWnd('swPersonEditWindow').show({
								onHide: function () {
									if ( person_data.onHide && typeof person_data.onHide == 'function' ) {
										person_data.onHide();
									}
								},
								callback: function(callback_data) {
									if ( typeof callback_data != 'object' ) {
										return false;
									}

									var grid = getWnd('swPersonSearchWindow').PersonSearchViewFrame.getGrid();

									if ( typeof grid != 'object' ) {
										return false;
									}

									grid.getStore().each(function(record) {
										if ( record.get('Person_id') == callback_data.Person_id ) {
											record.set('Server_id', callback_data.Server_id);
											record.set('PersonEvn_id', callback_data.PersonEvn_id);
											record.set('PersonSurName_SurName', callback_data.PersonData.Person_SurName);
											record.set('PersonFirName_FirName', callback_data.PersonData.Person_FirName);
											record.set('PersonSecName_SecName', callback_data.PersonData.Person_SecName);
											record.set('PersonBirthDay_BirthDay', callback_data.PersonData.Person_BirthDay);
											record.set('Lpu_Nick', callback_data.PersonData.Lpu_Nick);
											record.commit();
										}
									});

									grid.getView().focusRow(0);
								},
								Person_id: person_data.Person_id,
								Server_id: person_data.Server_id
							});
						}
					},
					searchMode: 'all'
				});
			}
		},
		swImportAction: {
			text: lang['obnovlenie_registrov'],
			tooltip: lang['obnovlenie_registrov'],
			iconCls: 'patient-search16',
			hidden: !getGlobalOptions().superadmin,
			handler: function()
			{
				getWnd('swImportWindow').show();
			}
		},
		swTemperedDrugs: {
			text: lang['import_otpuschennyih_ls'],
			tooltip: lang['otpuschennyie_ls'],
			iconCls: 'adddrugs-icon16',
			handler: function()
			{
                getWnd('swTemperedDrugsWindow').show();
			},
			//hidden: (getGlobalOptions().region.nick != 'ufa')
			hidden: !(getRegionNick() == 'ufa' && isSuperAdmin())
		},
		swPersonPeriodicViewAction: {
			text: lang['test_periodik'],
			tooltip: lang['test_periodik'],
			iconCls: 'patient-search16',
			handler: function()
			{
				getWnd('swPeriodicViewWindow').show({
					Person_id: 	99560000173,
					Server_id: 	10010833
				});
			}
		},
		/*swAssistantWorkPlaceAction: {
			text: lang['rabochee_mesto_laboranta'],
			tooltip: lang['rabochee_mesto_laboranta'],
			iconCls: 'lab-assist16',
			//iconCls: 'patient-search16',
			hidden: !isAdmin,
			handler: function()
			{
				getWnd('swAssistantWorkPlaceWindow').show();
			}
		},*/
		swSelectWorkPlaceAction: {
			text: lang['vyibor_arm_po_umolchaniyu'],
			tooltip: lang['vyibor_arm_po_umolchaniyu'],
			iconCls: 'lab-assist16',
			//iconCls: 'patient-search16',
			hidden: !isAdmin,
			handler: function()
			{
				getWnd('swSelectWorkPlaceWindow').show();
			}
		},
		
		swRegistrationJournalSearchAction: {
			text: lang['laboratornyie_issledovaniya_poisk'],
			tooltip: lang['jurnal_laboratornyih_issledovaniy'],
			//iconCls: 'patient-search16',
			hidden: (IS_DEBUG!=1 || !isSuperAdmin()),
			handler: function()
			{
				getWnd('swRegistrationJournalSearchWindow').show();
			}
		},
		swLpuSelectAction: {
			text: lang['vyibor_mo'],
			tooltip: lang['vyibor_mo'],
			iconCls: 'lpu-select16',
			handler: function()
			{
				Ext.WindowMgr.each(function(wnd){
					if ( wnd.isVisible() )
					{
						wnd.hide();
					}
				});
				getWnd('swSelectLpuWindow').show();
			},
			hidden: false
		},

		swDivCountAction: {
			text: lang['kolichestvo_html-elementov'],
			tooltip: lang['poschitat_tekuschee_kolichestvo_html-elementov'],
			iconCls: 'tags16',
			handler: function()
			{
				var arrdiv = Ext.DomQuery.select("div");
				var arrtd = Ext.DomQuery.select("td");
				var arra = Ext.DomQuery.select("a");
				Ext.Msg.alert("Количество html-элементов", "Количество html-элементов:<br><b>div</b>:&nbsp;" + arrdiv.length+"<br><b>td</b>:&nbsp;&nbsp;" + arrtd.length+"<br><b>a</b>:&nbsp;&nbsp;&nbsp;" + arra.length);
			},
			hidden:(IS_DEBUG!=1)
		},
		swGlobalOptionAction: {
			text: lang['parametryi_sistemyi'],
			tooltip: lang['prosmotr_i_izmenenie_obschih_nastroek'],
			iconCls: 'settings-global16',
			handler: function()
			{
				getWnd('swGlobalOptionsWindow').show();
			},
			hidden: !getGlobalOptions().superadmin //((IS_DEBUG!=1) || !getGlobalOptions().superadmin)
		},
		// Все прочие акшены
		swPregCardViewAction: {
			text: lang['individualnaya_karta_beremennoy_prosmotr'],
			tooltip: lang['individualnaya_karta_beremennoy_prosmotr'],
			iconCls: 'pol-preg16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin && !isTestLpu
		},
		swPregCardFindAction: {
			text: lang['individualnaya_karta_beremennoy_poisk'],
			tooltip: lang['individualnaya_karta_beremennoy_poisk'],
			iconCls: 'pol-pregsearch16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin && !isTestLpu
		},
		swRegChildOrphanDopDispFindAction: {
			text: lang['registr_detey-sirot_do_2013g_poisk'],
			tooltip: lang['registr_detey-sirot_poisk'],
			iconCls: 'orphdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpSearchWindow').show();
			},
			hidden: false//!isAdmin
		},
		swEvnPLChildOrphanDopDispFindAction: {
			text: lang['talon_po_dispanserizatsii_detey-sirot_do_2013g_poisk'],
			tooltip: lang['talon_po_dispanserizatsii_detey-sirot_poisk'],
			iconCls: 'orphdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispOrpSearchWindow').show();
			},
			hidden: false
		},
		swEvnDtpWoundViewAction: {
			text: lang['izvescheniya_dtp_o_ranenom_prosmotr'],
			tooltip: lang['izvescheniya_dtp_o_ranenom_prosmotr'],
			iconCls: 'stac-accident-injured16',
			handler: function()
			{
				getWnd('swEvnDtpWoundWindow').show();
			},
			hidden: !isAdmin
		},
		swEvnDtpDeathViewAction: {
			text: lang['izvescheniya_dtp_o_skonchavshemsya_prosmotr'],
			tooltip: lang['izvescheniya_dtp_o_skonchavshemsya_prosmotr'],
			iconCls: 'stac-accident-dead16',
			handler: function()
			{
				getWnd('swEvnDtpDeathWindow').show();
			},
			hidden: !isAdmin
		},
		swMedPersonalWorkPlaceAction: {
			text: lang['rabochee_mesto'],
			title: lang['arm'],
			tooltip: lang['rabochee_mesto_vracha'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'common',
					onSelect: null
				});
			},
			hidden: ((getGlobalOptions().medstafffact == undefined) && (getGlobalOptions().lpu_id>0))
		},
		/*swStacNurseWorkPlaceAction: {
			text: lang['rabochee_mesto_postovoy_medsestryi'],
			tooltip: lang['rabochee_mesto_postovoy_medsestryi'],
			iconCls: 'workplace-mp16',
			handler: function() {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'stacnurse',
					onSelect: function(data) { getWnd('swEvnPrescrJournalWindow').show({userMedStaffFact: data}); }
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},*/
		swEvnPrescrViewJournalAction: {
			text: lang['jurnal_naznacheniy'],
			tooltip: lang['jurnal_naznacheniy'],
			iconCls: 'workplace-mp16',
			handler: function() {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'prescr',
					onSelect: function(data) {getWnd('swEvnPrescrJournalWindow').show({userMedStaffFact: data});}
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},
		swEvnPrescrCompletedViewJournalAction: {
			text: lang['jurnal_meditsinskih_meropriyatiy'],
			tooltip: lang['jurnal_meditsinskih_meropriyatiy'],
			iconCls: 'workplace-mp16',
			handler: function() {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'prescr',
					onSelect: function(data) {getWnd('swEvnPrescrCompletedJournalWindow').show({userMedStaffFact: data});}
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},
		/*
		swVKWorkPlaceAction: {
			text: lang['rabochee_mesto_vk'],
			tooltip: lang['rabochee_mesto_vk'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				var onSelect = function(data) {
					getWnd('swVKWorkPlaceWindow').show(data);
				}
				openSelectServiceWindow({ ARMType: 'vk', onSelect: onSelect });
			},
			hidden: !IS_DEBUG // getGlobalOptions().medstafffact == undefined
		},
		swMseWorkPlaceAction: {
			text: lang['rabochee_mesto_mse'],
			tooltip: lang['rabochee_mesto_mse'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				var onSelect = function(data) {
					getWnd('swMseWorkPlaceWindow').show(data);
				}
				openSelectServiceWindow({ ARMType: 'mse', onSelect: onSelect });
			},
			hidden: !IS_DEBUG // getGlobalOptions().medstafffact == undefined
		},*/
		swJournalDirectionsAction: {
			text: lang['jurnal_registratsii_napravleniy'],
			tooltip: lang['jurnal_registratsii_napravleniy'],
			iconCls: 'pol-directions16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},
		
		swPersonCardAttachListAction: {
			text: lang['rpn_zayavleniya_o_vyibore_mo'],
			tooltip: lang['rpn_zayavleniya_o_vyibore_mo'],
			iconCls: '', // нужна иконка
			handler: function() {
				getWnd('swPersonCardAttachListWindow').show();
			}
		},
		
		swMSJobsAction: {
			text: lang['upravlenie_zadachami_mssql'],
			tooltip: lang['upravlenie_zadachami_mssql'],
			iconCls: 'sql16',
			handler: function()
			{
				getWnd('swMSJobsWindow').show();
			},
			hidden: !isAdmin
		},
		swXmlTemplateDebug: {
			text: lang['konvertatsiya_xml-dokumentov'],
			tooltip: lang['proverka_i_pravki_dannyih_shablonov_i_dokumentov'],
			iconCls: 'test16',
			handler: function()
			{
				window.open('/?c=EvnXmlConvert&m=index');
			},
			hidden: !isAdmin
		},
		loadLastObjectCode: {
			text: lang['obnovit_posledniy_js-fayl'],
			tooltip: lang['obnovit_posledniy_js-fayl'],
			iconCls: 'test16',
			handler: function() {
				if (sw.codeInfo) {
					loadJsCode({objectName: sw.codeInfo.lastObjectName, objectClass: sw.codeInfo.lastObjectClass});
				}
			},
			hidden: true //!isAdmin && !IS_DEBUG
		},
		MessageAction: {
			text: lang['soobscheniya'],
			iconCls: 'messages16',
			hidden: false,
			handler: function()
			{
				if(getWnd('swMessagesViewWindow').isVisible() == false)
				{
					getWnd('swMessagesViewWindow').show();
				}
			}
		},
		swTreatmentSearchAction: {
			text: lang['registratsiya_obrascheniy_poisk'],
			tooltip: lang['registratsiya_obrascheniy_poisk'],
			iconCls: 'petition-search16',
			handler: function() {
				getWnd('swTreatmentSearchWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swTreatmentReportAction: {
			text: lang['registratsiya_obrascheniy_otchetnost'],
			tooltip: lang['registratsiya_obrascheniy_otchetnost'],
			iconCls: 'petition-report16',
			handler: function() {
				getWnd('swTreatmentReportWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swEvnPSFindAction: {
			text: lang['karta_vyibyivshego_iz_statsionara_poisk'],
			tooltip: lang['karta_vyibyivshego_iz_statsionara_poisk'],
			iconCls: 'stac-pssearch16',
			handler: function()
			{
				getWnd('swEvnPSSearchWindow').show();
			},
			hidden: false //!isAdmin && !isTestLpu && IS_DEBUG != 1
		},
		swSuicideAttemptsFindAction: {
			text: lang['suitsidalnyie_popyitki_poisk'],
			tooltip: lang['suitsidalnyie_popyitki_poisk'],
			iconCls: 'suicide-search16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin && !isTestLpu
		},
		
		/*swMedPersonalWorkPlaceStacAction: {
			text: lang['rabochee_mesto_vracha'],
			tooltip: lang['rabochee_mesto_vracha'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				var onSelect = function(data) {
					if (data.LpuSectionProfile_SysNick == 'priem') 
					{
						getWnd('swMPWorkPlacePriemWindow').show({userMedStaffFact: data});
					}
					else
					{
						getWnd('swMPWorkPlaceStacWindow').show({userMedStaffFact: data});
					}
				};
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'stac',
					onSelect: onSelect
				});
			},
			hidden: false
		},*/
		swJourHospDirectionAction: {
			text: lang['jurnal_napravleniy'],
			tooltip: lang['jurnal_napravleniy_na_gospitalizatsiyu'],
			iconCls: 'pol-directions16',
			handler: function()
			{
				getWnd('swEvnDirectionJournalWindow').show({userMedStaffFact: null});
			},
			hidden: false
		},
		swEvnUslugaParFindAction: {
			text: lang['vyipolnenie_paraklinicheskoy_uslugi_poisk'],
			tooltip: lang['vyipolnenie_paraklinicheskoy_uslugi_poisk'],
			iconCls: 'par-serv-search16',
			handler: function()
			{
				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swEvnUslugaParSearchWindow').show();
			},
			hidden: false
		},
		swEvnLabSampleDefectViewAction: {
			text: lang['jurnal_otbrakovki'],
			tooltip: lang['jurnal_otbrakovki'],
			iconCls: 'lab-assist16',
			handler: function()
			{
				getWnd('swEvnLabSampleDefectViewWindow').show();
			},
			hidden: false
		},
		swEvnPLStomSearchAction: {
			text: lang['talon_ambulatornogo_patsienta_poisk'],
			tooltip: lang['talon_ambulatornogo_patsienta_poisk'],
			iconCls : 'stom-search16',
			handler: function()
			{
				getWnd('swEvnPLStomSearchWindow').show();
			},
			hidden: false
		},
		swUslugaPriceListAction: {
			text: lang['stomatologicheskie_uslugi_mo_spravochnik_uet'],
			tooltip: lang['stomatologicheskie_uslugi_mo_spravochnik_uet'],
			iconCls: 'stom-uslugi16',
			handler: function() {
				getWnd('swUslugaPriceListViewWindow').show();
			},
			hidden: false
		},
		swMedSvidBirthAction: {
			text: lang['svidetelstva_o_rojdenii'],
			tooltip: lang['svidetelstva_o_rojdenii'],
			iconCls: 'svid-birth16',
			handler: function()
			{
				getWnd('swMedSvidBirthStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidDeathAction: {
			text: lang['svidetelstva_o_smerti'],
			tooltip: lang['svidetelstva_o_smerti'],
			iconCls: 'svid-death16',
			handler: function()
			{
				getWnd('swMedSvidDeathStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidPDeathAction: {
			text: lang['svidetelstva_o_perinatalnoy_smerti'],
			tooltip: lang['svidetelstva_o_perinatalnoy_smerti'],
			iconCls: 'svid-pdeath16',
			handler: function()
			{
				getWnd('swMedSvidPntDeathStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidPrintAction: {
			text: lang['pechat_blankov_svidetelstv'],
			tooltip: lang['pechat_blankov_svidetelstv'],
			iconCls: 'svid-blank16',
			handler: function()
			{
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swMedSvidSelectSvidType').show();
			},
			hidden: false
		},
		swTestAction: {
			text: lang['test'],
			tooltip: lang['test'],
			iconCls: '',
			handler: function()
			{
				// 
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						token: '18e6e09a6cd57caea50f8d33c789bf00',
						doc_id: '431885',
						user_id: 1
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log(response_obj);
					},
					url: 'http://192.168.36.62:8080/sign_service/'
				});
				return false;
				/*
				// проверка методов 
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						Polis_Ser: lang['ks'],
						Polis_Num: '431885'
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getPersonByPolis');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getPersonByPolis'
				});
				
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						Person_SurName: lang['petuhov'],
						Person_FirName: lang['ivan'],
						Person_SecName: lang['sergeevich'],
						Person_BirthDay: '1983-12-26'
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getPersonByFIODR');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getPersonByFIODR'
				});
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						Person_SurName: lang['kataev'],
						Person_FirName: lang['andrey'],
						Person_Age: 46,
						KLStreet_Name: lang['shkolnaya'],
						Address_House: '6',
						Address_Flat: null
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getPersonByAddress');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getPersonByAddress'
				});
				
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getProfileList');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getProfileList'
				});
				
				
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						LpuSectionProfile_Code: 1000
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=getStacList');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=getStacList'
				});
				
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
						}
					},
					params: {
						LpuSection_id: 99560000944,
						Lpu_id: 28,
						Person_id: 220,
						emergencyBedCount: 1, 
						EmergencyData_BrigadeNum: 1, 
						EmergencyData_CallNum: 111 
					},
					success: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						log('?c=AmbulanceService&m=bookEmergencyBed');
						log(response_obj);
					},
					url: '?c=AmbulanceService&m=bookEmergencyBed'
				});
				*/
				
				/*getWnd('swAddPropertyWindow').show({
					onSelect: function(params) {						
						var ds_model = Ext.data.Record.create([
							'id',
							'type',
							'name',
							'value'
						]);
						
						var gr = Ext.getCmp('EUDDEW_PropertyGrid');
						gr.getStore().insert(
							0,
							new ds_model({
								id: params.id,
								type: params.type,
								name: params.pname,
								value: params.value									
							})
						);
						gr.startEditing(0,0);
						getWnd('swAddPropertyWindow').hide();
						swalert(params);
					}
				});*/
				/*
				getWnd('swPersonEditWindow').show({
					action: 'edit',
					Person_id: "1170750319",
					Server_id: "10010833"
				});
				*/
			},
			hidden: ((!isAdmin) || (getGlobalOptions().region.nick == 'saratov'))
		},
		swRegDeceasedPeopleAction: {
			text: lang['svedeniya_ob_umershih_grajdanah'],
			tooltip: lang['svedeniya_ob_umershih_grajdanah_registr'],
			iconCls: 'regdead16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},
		swMedicationSprAction: {
			text: lang['spravochnik_medikamentyi'],
			tooltip: lang['spravochnik_medikamentyi'],
			iconCls: 'farm-drugs16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swContractorsSprAction: {
			text: lang['spravochnik_kontragentyi'],
			tooltip: lang['spravochnik_kontragentyi'],
			iconCls: 'farm-partners16',
			handler: function()
			{
				getWnd('swContragentViewWindow').show();
			},
			hidden: false
		},
		swDokNakAction: {
			text: lang['prihodnyie_nakladnyie'],
			tooltip: lang['prihodnyie_nakladnyie'],
			iconCls: 'doc-nak16',
			handler: function()
			{
				getWnd('swDokNakViewWindow').show();
			},
			hidden: false
		},
		swDokUchAction: {
			text: lang['dokumentyi_ucheta_medikamentov'],
			tooltip: lang['dokumentyi_ucheta_medikamentov'],
			iconCls: 'doc-uch16',
			handler: function()
			{
				getWnd('swDokUcLpuViewWindow').show();
			},
			hidden: false
		},
		swAktSpisAction: {
			text: lang['aktyi_spisaniya_medikamentov'],
			tooltip: lang['aktyi_spisaniya_medikamentov'],
			iconCls: 'doc-spis16',
			handler: function()
			{
				getWnd('swDokSpisViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swDokOstAction: {
			text: lang['dokumentyi_vvoda_ostatkov'],
			tooltip: lang['dokumentyi_vvoda_ostatkov'],
			iconCls: 'doc-ost16',
			handler: function()
			{
				getWnd('swDokOstViewWindow').show();
			},
			hidden: false
		},
		swInvVedAction: {
			text: lang['inventarizatsionnyie_vedomosti'],
			tooltip: lang['inventarizatsionnyie_vedomosti'],
			iconCls: 'farm-inv16',
			handler: function()
			{
				getWnd('swDokInvViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swMedOstatAction: {
			text: lang['ostatki_medikamentov'],
			tooltip: lang['ostatki_medikamentov'],
			iconCls: 'farm-ostat16',
			handler: function()
			{
				getWnd('swMedOstatViewWindow').show();
			},
			hidden: false
		},
		EvnReceptProcessAction: {
			text: lang['obrabotka_retseptov'],
			tooltip: lang['obrabotka_retseptov'],
			iconCls : 'receipt-process16',
			handler: function() {
				getWnd('swEvnReceptProcessWindow').show();
			},
			hidden: !isAdmin
		},
		EvnRPStreamInputAction: {
			text: lang['potokovoe_otovarivanie_retseptov'],
			tooltip: lang['potokovoe_otovarivanie_retseptov'],
			iconCls : 'receipt-streamps16',
			handler: function() {
				getWnd('swEvnRPStreamInputWindow').show();
			},
			hidden: !isAdmin
		},
		EvnReceptTrafficBookViewAction: {
			text: lang['jurnal_dvijeniya_retseptov'],
			tooltip: lang['jurnal_dvijeniya_retseptov'],
			iconCls : 'receipt-delay16',
			handler: function() {
				getWnd('swEvnReceptTrafficBookViewWindow').show();
			},
			hidden: !isAdmin
		},
		KerRocordBookAction: {
			text: lang['vrachebnaya_komissiya'],
			tooltip: lang['vrachebnaya_komissiya'],
			iconCls: 'med-commission16',
			handler: function()
			{
				getWnd('swClinExWorkSearchWindow').show();
			}, 
			hidden: !isAdmin
		},
		swRegistrationCallAction: {
			text: lang['registratsiya_vyizova'],
			tooltip: lang['registratsiya_vyizova'],
			iconCls: '',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swCardCallViewAction: {
			text: lang['karta_vyizova_prosmotr'],
			tooltip: lang['karta_vyizova_prosmotr'],
			iconCls: 'ambulance_add16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swCardCallFindAction: {
			text: lang['kartyi_smp_poisk'],
			tooltip: lang['kartyi_vyizova_smp_poisk'],
			iconCls: 'ambulance_search16',
			handler: function()
			{
				getWnd('swCmpCallCardSearchWindow').show();
			}
			},
			amm_JournalsVac: {
			text: lang['prosmotr_jurnalov_vaktsinatsii'],
			tooltip: lang['prosmotr_jurnalov_vaktsinatsii'],
			iconCls: 'vac-plan16',
			handler: function()
			{
                            if (vacLpuContr())  // Если это 2-я детская
                                    getWnd('amm_mainForm').show();
                                else
                                    sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);

//              getWnd('amm_mainForm').show();
              //var loadMask = new Ext.LoadMask(Ext.getCmp('journalsVaccine'), { msg: LOAD_WAIT });
              //loadMask.show();
			},
			hidden: false // !isAdmin
		},
		
		ammStartVacFormPlan: {
			text: lang['planirovanie_vaktsinatsii'],
			tooltip: lang['planirovanie_vaktsinatsii'],
			iconCls: 'vac-plan16',
			hidden: !isAdmin&& !isLpuAdmin(),
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_StartVacPlanForm').show();
				else
					sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			}
			//, hidden: false // !isAdmin
		},

		ammvacListTasks: {
			text: lang['spisok_zadaniy'],
			tooltip: lang['spisok_zadaniy'],
			iconCls: 'vac-plan16',
			hidden: !isAdmin&& !isLpuAdmin(),

			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_ListTaskForm').show();
				else
					sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			}
			//, hidden: false // !isAdmin
		},
		ammvacReport_5: {
			text: lang['otchet_f_№5'],
			tooltip: lang['otchet_f_№5'],
			iconCls: 'vac-plan16',
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_vacReport_5').show();
				else
					sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: getRegionNick() == 'kz'
		},
		ammSprVaccine: {
			text: lang['spravochnik_vaktsin'],
			tooltip: lang['spravochnik_vaktsin'],
			iconCls: 'vac-plan16',
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_SprVaccineForm').show();
				else
					sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: false // !isAdmin
		},
		ammSprNacCal: {
			text: lang['natsionalnyiy_kalendar_privivok'],
			tooltip: lang['natsionalnyiy_kalendar_privivok'],
			iconCls: 'vac-plan16',
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_SprNacCalForm').show();
				else
					sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: false // !isAdmin
		},
		ammVacPresence: {
			text: lang['nalichie_vaktsin'],
			tooltip: lang['nalichie_vaktsin'],
			iconCls: 'vac-plan16',
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_PresenceVacForm').show();
				else
					sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: false // !isAdmin
		},
		// End  tagir
		swLpuPassportAction: {
			text: lang['pasport_mo'],
			tooltip: lang['pasport_mo'],
			iconCls: 'lpu-passport16',
			handler: function()
			{
				getWnd('swLpuPassportEditWindow').show({
					action: 'view',
					Lpu_id: getGlobalOptions().lpu_id
				});
			},
			hidden: false
		},
		swOrgPassportAction: {
			text: lang['pasport_organizatsii'],
			tooltip: lang['pasport_organizatsii'],
			iconCls: 'lpu-passport16',
			handler: function()
			{
				getWnd('swOrgEditWindow').show({
						action: 'edit',
						mode: 'passport',
						Org_id: getGlobalOptions().org_id
				});
			},
			hidden: (!isAdmin && !isOrgAdmin()) || !isDebug()
		},
		swLpuUslugaAction: {
			text: lang['uslugi_mo'],
			tooltip: lang['uslugi_mo'],
			iconCls: 'lpu-services-lpu16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swUslugaGostAction: {
			text: lang['uslugi_gost'],
			tooltip: lang['uslugi_gost'],
			iconCls: 'lpu-services-gost16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swMKB10Action: {
			text: lang['mkb-10'],
			tooltip: lang['spravochnik_mkb-10'],
			iconCls: 'spr-mkb16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swMESAction: {
			text: lang['novyie'] + getMESAlias(),
			tooltip: lang['spravochnik_novyih'] + getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function()
			{
				getWnd('swMesSearchWindow').show();
			},
			hidden: (!isAdmin)
		},
		swMESOldAction: {
			text: getMESAlias(),
			tooltip: lang['spravochnik'] + getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function()
			{
				getWnd('swMesOldSearchWindow').show();
			},
			hidden: false // TODO: После тестирования доступ должен быть для всех
		},
		swOrgAllAction: {
			text: lang['vse_organizatsii'],
			tooltip: lang['vse_organizatsii'],
			iconCls: 'spr-org16',
			handler: function()
			{
				getWnd('swOrgViewForm').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swContragentsAction: {
			text: lang['kontragentyi'],
			tooltip: lang['spravochnik_kontragentov_dlya_personifitsirovannogo_ucheta'],
			iconCls: 'farm-partners16',
			handler: function()
			{
				getWnd('swContragentViewWindow').show();
			},
			hidden: !isAdmin
		},
		swDrugDocumentSprAction: {
			text: 'Справочники системы учета медикаментов',
			tooltip: 'Справочники системы учета медикаментов',
			iconCls: '',
			handler: function()
			{
				getWnd('swDrugDocumentSprWindow').show();
			}
		},
		swDocumentUcAction: {
			text: lang['uchet_medikamentov'],
			tooltip: lang['dokumentyi_ucheta_medikamentov'],
			iconCls: 'drug-traffic16',
			handler: function()
			{
				getWnd('swDocumentUcViewWindow').show();
			},
			hidden: !isAdmin
		},
		swOrgLpuAction: {
			text: lang['lechebno-profilakticheskie_uchrejdeniya'],
			tooltip: lang['lechebno-profilakticheskie_uchrejdeniya'],
			iconCls: 'spr-org-lpu16',
			handler: function()
			{
				getWnd('swOrgViewForm').show({mode: 'lpu'});
			},
			hidden: false
		},
		swOrgGosAction: {
			text: lang['gosudarstvennyie_uchrejdeniya'],
			tooltip: lang['gosudarstvennyie_uchrejdeniya'],
			iconCls: 'spr-org-gos16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swOrgStrahAction: {
			text: lang['strahovyie_meditsinskie_organizatsii'],
			tooltip: lang['strahovyie_meditsinskie_organizatsii'],
			iconCls: 'spr-org-strah16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swOrgBankAction: {
			text: lang['banki'],
			tooltip: lang['banki'],
			iconCls: 'spr-org-bank16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swRlsFirmsAction: {
			text: lang['proizvoditeli_lekarstvennyih_sredstv'],
			tooltip: lang['proizvoditeli_lekarstvennyih_sredstv'],
			iconCls: 'spr-org-manuf16',
			handler: function(){
				if(!getWnd('swRlsFirmsSearchWindow').isVisible()) getWnd('swRlsFirmsSearchWindow').show();
			}
		},
		swOMSSprTerrAction: {
			text: lang['territorii_subyekta_rf'],
			tooltip: lang['territorii_subyekta_rf'],
			iconCls: 'spr-terr-oms16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swClassAddrAction: {
			text: lang['klassifikator_adresov'],
			tooltip: lang['klassifikator_adresov'],
			iconCls: 'spr-terr-addr16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swSprPromedAction: {
			text: lang['spravochniki_promed'],
			tooltip: lang['spravochniki_promed'],
			iconCls: 'spr-promed16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprLpuAction: {
			text: lang['spravochniki_mo'],
			tooltip: lang['spravochniki_mo'],
			iconCls: 'spr-lpu16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprOmsAction: {
			text: lang['spravochniki_oms'],
			tooltip: lang['spravochniki_oms'],
			iconCls: 'spr-oms16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprDloAction: {
			text: lang['spravochniki_llo'],
			tooltip: lang['spravochniki_llo'],
			iconCls: 'spr-dlo16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprPropertiesProfileAction: {
			text: lang['harakteristiki_profiley_otdeleniy'],
			tooltip: lang['harakteristiki_profiley_otdeleniy'],
			iconCls: 'otd-profile16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprUchetFactAction: {
			text: lang['uchet_fakticheskoy_vyirabotki_smen'],
			tooltip: lang['uchet_fakticheskoy_vyirabotki_smen'],
			iconCls: 'uchet-fact16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprRlsAction: {
			text: getRLSTitle(),
			tooltip: getRLSTitle(),
			iconCls: 'rls16',
			handler: function()
			{
				getWnd('swRlsViewForm').show();
			},
			hidden: false
		},
		SprPostAction: {
			text: lang['doljnosti'],
			tooltip: lang['doljnosti'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Post', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprSkipPaymentReasonAction: {
			text: lang['prichinyi_nevyiplat'],
			tooltip: lang['prichinyi_nevyiplat'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'SkipPaymentReason', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprWorkModeAction: {
			text: lang['rejimyi_rabotyi'],
			tooltip: lang['rejimyi_rabotyi'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkMode', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprSpecialityAction: {
			text: lang['spetsialnosti'],
			tooltip: lang['spetsialnosti'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Speciality', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprDiplomaSpecialityAction: {
			text: lang['diplomnyie_spetsialnosti'],
			tooltip: lang['diplomnyie_spetsialnosti'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'DiplomaSpeciality', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprLeaveRecordTypeAction: {
			text: lang['tip_zapisi_okonchaniya_rabotyi'],
			tooltip: lang['tip_zapisi_okonchaniya_rabotyi'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'LeaveRecordType', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprEducationTypeAction: {
			text: lang['tip_obrazovaniya'],
			tooltip: lang['tip_obrazovaniya'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationType', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprEducationInstitutionAction: {
			text: lang['uchebnoe_uchrejdenie'],
			tooltip: lang['uchebnoe_uchrejdenie'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationInstitution', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		swF14OMSPerAction: {
			text: lang['forma_f14_oms_pokazateli'],
			tooltip: lang['pokazateli_dlya_formyi_f14_oms'],
			iconCls: 'rep-f14oms-per16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},
		swF14OMSAction: {
			text: lang['forma_f14_oms'],
			tooltip: lang['forma_f14_oms'],
			iconCls: 'rep-f14oms16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},
		swF14OMSFinAction: {
			text: lang['forma_f14_oms_prilojenie_1'],
			tooltip: lang['forma_f14_oms_prilojenie_1'],
			iconCls: 'rep-f14oms-fin16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},/*
		swAdminWorkPlaceAction: {
			text: lang['rabochee_mesto_administratora'],
			tooltip: lang['rabochee_mesto_administratora'],
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swAdminWorkPlaceWindow').show({});
			},
			hidden: false
		},
		swLpuAdminWorkPlaceAction: {
			text: lang['rabochee_mesto_administratora_mo'],
			tooltip: lang['rabochee_mesto_administratora_mo'],
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swLpuAdminWorkPlaceWindow').show({});
			},
			hidden: false
		},*/
		/*
		swRegWorkPlaceAction: {
			text: lang['rabochee_mesto_registratora'],
			tooltip: lang['rabochee_mesto_registratora'],
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swRegWorkPlaceWindow').show({});
			},
			hidden: !isAdmin
		},
		*/
		swReportEngineAction: {
			text: lang['repozitoriy_otchetov'],
			tooltip: lang['repozitoriy_otchetov'],
			iconCls: 'rpt-repo16',
			handler: function()
			{
				// Пример предварительной загрузки блока кода 
				if (sw.codeInfo.loadEngineReports)
				{
					getWnd('swReportEngineWindow').show();
				}
				else 
				{
					getWnd('reports').load(
					{
						callback: function(success) 
						{
							sw.codeInfo.loadEngineReports = success;
							// здесь можно проверять только успешную загрузку 
							getWnd('swReportEngineWindow').show();
						}
					});
				}
			},
			hidden: !isAdmin
		},
		swAnalyzerWindowAction: {
			text: lang['nastroyki_lis'],
			tooltip: lang['nastroyki_polzovatelya_lis'],
			handler: function()
			{
				getWnd('swAnalyzerWindow').show({pmUser_id: getGlobalOptions().pmuser_id, pmUser_Login: UserLogin});
			},
			hidden: true //((!isAdmin) || (getGlobalOptions().region.nick == 'saratov'))
		},
		swRrlExportWindowAction: {
			text: lang['vyigruzka_rrl'],
			tooltip: lang['vyigruzka_registra_regionalnyih_lgotnikov'],
			handler: function()
			{
				getWnd('swRrlExportWindow').show();
			},
			hidden: (getGlobalOptions().region.nick != 'ufa')
		},
		swPrepBlockSprAction: {
			text: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
			tooltip: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
			handler: function()
			{
				getWnd('swPrepBlockViewWindow').show();
			}
		}
	}
	
	// Проставляем ID-шники списку акшенов [и на всякий случай создаем их] (создавать кстати не обязательно)
	for(var key in sw.Promed.Actions) {
		sw.Promed.Actions[key].id = key;
		sw.Promed.Actions[key] = new Ext.Action(sw.Promed.Actions[key]);
	}
	
	// Формирование обычного меню 
	this.menu_passport_lpu = new Ext.menu.Menu(
	{
		id: 'menu_passport_lpu',
		items:
		[
			sw.Promed.Actions.OrgStructureViewAction,
			sw.Promed.Actions.LpuStructureViewAction,
			sw.Promed.Actions.swOrgPassportAction,
			sw.Promed.Actions.swLpuPassportAction,
			{
				text: lang['meditsinskiy_personal'],
				hidden: (!isAdmin && !isLpuAdmin()) || (getGlobalOptions().region.nick == 'pskov'),
				iconCls : 'staff16',
				menu: new Ext.menu.Menu(
				{
					id: 'menu_spr_mp',
					items:
					[
						sw.Promed.Actions.MedPersonalPlaceAction,
						sw.Promed.Actions.MedPersonalSearchAction
					]
				})
			},
			//'-', //TODO: Показывать взависимости от условий
			sw.Promed.Actions.swLpuUslugaAction,
			sw.Promed.Actions.swUslugaGostAction,
			sw.Promed.Actions.swMKB10Action,
			sw.Promed.Actions.swMESAction,
			sw.Promed.Actions.swMESOldAction,
			sw.Promed.Actions.UslugaComplexTreeAction,
			'-',
			{
				text:lang['organizatsii'],
				iconCls:'spr-org16',
				hidden: !isAdmin && !isLpuAdmin() && (getGlobalOptions().region.nick != 'ufa'),
				menu: new Ext.menu.Menu(
				{
					id: 'menu_spr_org',
					items:
					[
						sw.Promed.Actions.swOrgAllAction,
						'-',
						sw.Promed.Actions.swOrgLpuAction,
						sw.Promed.Actions.swOrgGosAction,
						sw.Promed.Actions.swOrgStrahAction,
						sw.Promed.Actions.swOrgBankAction,
						sw.Promed.Actions.swRlsFirmsAction
					]
				})
			},
			{
				text:lang['klassifikator_territoriy'],
				iconCls:'spr-terr16',
				hidden: (!isAdmin),
				menu: new Ext.menu.Menu(
				{
					id: 'menu_spr_org',
					items:
					[
						sw.Promed.Actions.swOMSSprTerrAction,
						sw.Promed.Actions.swClassAddrAction
					]
				})
			}
		]
	});
	
	if (isAdmin)
	{
		this.menu_passport_lpu.addSeparator();
		this.menu_passport_lpu.addMenuItem(sw.Promed.Actions.swSprPromedAction);
		this.menu_passport_lpu.addMenuItem(sw.Promed.Actions.SprLpuAction);
		this.menu_passport_lpu.addMenuItem(sw.Promed.Actions.SprOmsAction);
		this.menu_passport_lpu.addMenuItem(sw.Promed.Actions.SprDloAction);
		this.menu_passport_lpu.addMenuItem(sw.Promed.Actions.SprPropertiesProfileAction);
		this.menu_passport_lpu.addMenuItem(sw.Promed.Actions.SprUchetFactAction);
	}
	this.menu_passport_lpu.addMenuItem(sw.Promed.Actions.GlossarySearchAction);
	//this.menu_passport_lpu.addSeparator();
	//this.menu_passport_lpu.addMenuItem(sw.Promed.Actions.SprRlsAction);
	
	/*
	this.menu_reg_main = new Ext.menu.Menu(
	{
		//plain: true,
		id: 'menu_reg_main',
		items:
		[
			sw.Promed.Actions.PersonCardSearchAction,
			sw.Promed.Actions.PersonCardViewAllAction,
			sw.Promed.Actions.PersonCardStateViewAction
		]
	});
	*/
	this.menu_dlo_main = new Ext.menu.Menu(
	{
		//plain: true,
		id: 'menu_dlo_main2',
		items:
		[
			sw.Promed.Actions.swLgotTreeViewAction,
			sw.Promed.Actions.LgotFindAction,
	//		sw.Promed.Actions.LgotAddAction,
			'-',
			sw.Promed.Actions.EvnUdostViewAction,
	//		sw.Promed.Actions.EvnUdostAddAction,
			'-',
			sw.Promed.Actions.EvnReceptFindAction,
	//		sw.Promed.Actions.EvnReceptAddAction,
			'-',
			sw.Promed.Actions.OstAptekaViewAction,
			sw.Promed.Actions.OstDrugViewAction,
			sw.Promed.Actions.OstSkladViewAction,
			'-',/******/
			sw.Promed.Actions.DrugRequestViewAction,
			//sw.Promed.Actions.NewDrugRequestViewAction,
			sw.Promed.Actions.EvnReceptInCorrectFindAction,
            sw.Promed.Actions.swTemperedDrugs,
			
			sw.Promed.Actions.DrugMnnLatinNameEditAction,
			sw.Promed.Actions.DrugTorgLatinNameEditAction,
            '-',
            sw.Promed.Actions.SprRlsAction
		]
	});
	if (isAdmin)
	{
		this.menu_dlo_main.addSeparator();
		this.menu_dlo_main.addMenuItem(sw.Promed.Actions.OrgFarmacyViewAction);
	}
	
	
	this.menu_polka_main = new Ext.menu.Menu(
	{
		id: 'menu_polka_main',
		items: []
	});

	this.menu_polka_main.addMenuItem(sw.Promed.Actions.EvnPLEditAction);
	this.menu_polka_main.addSeparator();

	this.menu_polka_main.addMenuItem(sw.Promed.Actions.PersonCardSearchAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.PersonCardViewAllAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.PersonCardStateViewAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.swPersonCardAttachListAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.AutoAttachViewAction);

	if (isAdmin) {
		this.menu_polka_main.addSeparator();
		this.menu_polka_main.addMenuItem(sw.Promed.Actions.swJournalDirectionsAction);
		this.menu_polka_main.addSeparator();
	}
		// Углубленное диспансерное обследование ВОВ
		/*this.menu_polka_main.addMenuItem({ //http://redmine.swan.perm.ru/issues/22108#note-6
			text:lang['uglublennoe_dispansernoe_obsledovanie_vov'],
			iconCls: 'pol-dopdisp16', // to-do: Поменять иконки
			hidden: false, // !isAdmin
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_wow',
				items:
				[
					sw.Promed.Actions.PersonDispWOWSearchAction
				]
			})
		});*/
		/*this.menu_polka_main.addMenuItem({ // старый функционал по дд
			text:lang['dopolnitelnaya_dispanserizatsiya'],
			iconCls: 'pol-dopdisp16',
			hidden: false,//!isAdmin,
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dd',
				items:
				[
					sw.Promed.Actions.PersonDopDispSearchAction,
					sw.Promed.Actions.EvnPLDopDispSearchAction
				]
			})
		});*/
		this.menu_polka_main.addMenuItem({
			text:lang['dispanserizatsiya_vzroslogo_naseleniya'],
			iconCls: 'pol-dopdisp16',
			hidden: false,//!isAdmin,
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dd13',
				items:
				[
					sw.Promed.Actions.PersonDispWOWSearchAction,
					'-',
					sw.Promed.Actions.PersonPrivilegeWOWSearchAction,
					'-',
					sw.Promed.Actions.PersonDopDispSearchAction,
					'-',
					sw.Promed.Actions.EvnPLDopDispSearchAction,
					'-',
					sw.Promed.Actions.EvnPLDispDop13SearchAction,
					sw.Promed.Actions.EvnPLDispDop13SecondSearchAction
				]
			})
		});
	this.menu_polka_main.addMenuItem({
		text:lang['profilakticheskie_osmotryi_vzroslyih'],
		iconCls: 'pol-dopdisp16',
		hidden: false,//!isAdmin,
		menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dd13',
				items:
					[
						sw.Promed.Actions.EvnPLDispProfSearchAction
					]
			})
	});
		this.menu_polka_main.addMenuItem({
			text:lang['dispanserizatsiya_detey-sirot'],
			iconCls: 'pol-dopdisp16',
			hidden: false,//!isAdmin,
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dd13',
				items:
				[
					sw.Promed.Actions.swRegChildOrphanDopDispFindAction,
					sw.Promed.Actions.swEvnPLChildOrphanDopDispFindAction,
					'-',
					sw.Promed.Actions.PersonDispOrpSearchAction,
					sw.Promed.Actions.PersonDispOrpAdoptedSearchAction,
					sw.Promed.Actions.EvnPLDispOrpSearchAction,
					sw.Promed.Actions.EvnPLDispOrpSecSearchAction,
					'-',
					sw.Promed.Actions.EvnPLDispTeenExportAction
				]
			})
		});

		this.menu_polka_main.addMenuItem({
			text:lang['meditsinskie_osmotryi_nesovershennoletnih'],
			iconCls: 'pol-dopdisp16',
			hidden: false,//!isAdmin,
			menu: new Ext.menu.Menu(
				{
					id: 'menu_polka_dd13',
					items:
						[
							sw.Promed.Actions.PersonDispOrpPeriodSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionSearchAction,
							'-',
							sw.Promed.Actions.PersonDispOrpProfSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionProfSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionProfSecSearchAction,
							'-',
							sw.Promed.Actions.PersonDispOrpPredSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionPredSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionPredSecSearchAction
						]
				})
		});

		this.menu_polka_main.addMenuItem({
			text:lang['dispanserizatsiya_podrostki_14ti_let'],
			iconCls: 'dopdisp-teens16',
			hidden: false,//!isAdmin,
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dt14',
				items:
				[
					sw.Promed.Actions.EvnPLDispTeen14SearchAction
				]
			})
		});
		this.menu_polka_main.addSeparator();
		this.menu_polka_main.addMenuItem({
			text:lang['dispansernyiy_uchet'],
			iconCls: 'pol-disp16',
			hidden: false,//!(isAdmin || isTestLpu),
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_disp',
				items:
				[
					sw.Promed.Actions.PersonDispSearchAction,
					sw.Promed.Actions.PersonDispViewAction
				]
			})
		});
//		if (isAdmin) {
			this.menu_polka_main.addMenuItem({
				text:lang['individualnaya_karta_beremennoy'],
				iconCls: 'pol-preg16',
				hidden: !isAdmin,
				menu: new Ext.menu.Menu(
				{
					id: 'menu_polka_preg',
					items:
					[
						sw.Promed.Actions.swPregCardViewAction,
						sw.Promed.Actions.swPregCardFindAction
					]
				})
			});
			this.menu_polka_main.addSeparator();
            this.menu_polka_main.addMenuItem({
                text:lang['immunoprofilaktika'],
                hidden: (getRegionNick() == 'perm'),
                iconCls: 'pol-immuno16',
                // hidden: ((getGlobalOptions().region.nick != 'ufa') && (!isAdmin)),
                menu: new Ext.menu.Menu(
                {
                    id: 'menu_polka_immuno',
                    items:
                    [
                        sw.Promed.Actions.ammStartVacFormPlan,
                        sw.Promed.Actions.ammvacListTasks,
                        '-',
                        sw.Promed.Actions.amm_JournalsVac,
                        getRegionNick() == 'kz' ? '' : '-',
                        sw.Promed.Actions.ammvacReport_5,
                        '-',
                        sw.Promed.Actions.ammSprVaccine,
                        sw.Promed.Actions.ammSprNacCal,
                        '-',
                        sw.Promed.Actions.ammVacPresence
                    ]
                })
            });
    //		}
		
		this.menu_polka_main.addMenuItem(sw.Promed.Actions.FundHoldingViewAction);

		
	this.menu_dlo_service = new Ext.menu.Menu(
	{
		//plain: true,
		id: 'menu_dlo_service',
		items: [
			sw.Promed.Actions.swOptionsViewAction,
			sw.Promed.Actions.UserProfileAction,
			sw.Promed.Actions.MessageAction,
			sw.Promed.Actions.swLpuSelectAction,
			sw.Promed.Actions.swTimeTableAction,
			sw.Promed.Actions.swOpenEmkAction,
			sw.Promed.Actions.swPersonSearchAction
		]
	});
	
	this.menu_stac_main = new Ext.menu.Menu(
	{
		id: 'menu_stac_main',
		items: [
			sw.Promed.Actions.swEvnPSFindAction
		]
	});
	
	
	//this.menu_stac_main.addMenuItem(sw.Promed.Actions.swMedPersonalWorkPlaceStacAction);
	/*if (isAdmin || isTestLpu || IS_DEBUG == 1)
	{
		this.menu_stac_main.addSeparator();
		//this.menu_stac_main.addMenuItem(sw.Promed.Actions.swStacNurseWorkPlaceAction);
		this.menu_stac_main.addMenuItem(sw.Promed.Actions.swEvnPrescrViewJournalAction);
		this.menu_stac_main.addMenuItem(sw.Promed.Actions.swEvnPrescrCompletedViewJournalAction);
		//this.menu_stac_main.addMenuItem(sw.Promed.Actions.swJourHospDirectionAction);
	}*/
	/*
	if (isAdmin || isTestLpu)
	{
		this.menu_stac_main.addSeparator();
		this.menu_stac_main.addMenuItem(sw.Promed.Actions.swSuicideAttemptsFindAction);
	}*/
	/*
	this.menu_stac_main.addSeparator();
	this.menu_stac_main.addMenuItem({
		text: lang['patomorfologiya'],
		iconCls: 'pathomorph-16',
		hidden: false, // !isAdmin
		menu: new Ext.menu.Menu({
			id: 'menu_stac_sudmedexp',
			items: [
				sw.Promed.Actions.EvnDirectionHistologicViewAction,
				sw.Promed.Actions.EvnHistologicProtoViewAction,
				sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
				sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
			]
		})
	});
	*/

	
	
	this.menu_parka_main = new Ext.menu.Menu(
	{
		id: 'menu_parka_main',
		items: [
			sw.Promed.Actions.swEvnUslugaParFindAction,
			sw.Promed.Actions.swEvnLabSampleDefectViewAction
		]
	});
	this.menu_stomat_main = new Ext.menu.Menu(
	{
		id: 'menu_stomat_main',
		items: [
			sw.Promed.Actions.swEvnPLStomSearchAction,
			'-'//,
			//sw.Promed.Actions.swUslugaPriceListAction
		]
	});
	
	this.menu_farmacy_main = new Ext.menu.Menu(
	{
		id: 'menu_farmacy_main',
		items: [
			//sw.Promed.Actions.swMedicationSprAction,
			sw.Promed.Actions.swContractorsSprAction,
			'-',
			sw.Promed.Actions.swDokNakAction,
			sw.Promed.Actions.swDokUchAction,
			sw.Promed.Actions.swAktSpisAction,
			sw.Promed.Actions.swDokOstAction,
			sw.Promed.Actions.swInvVedAction,
			sw.Promed.Actions.swMedOstatAction
		]
	});
	if (isAdmin || isTestLpu)
	{
		this.menu_farmacy_main.addSeparator();
		this.menu_farmacy_main.addMenuItem(sw.Promed.Actions.EvnReceptProcessAction);
		this.menu_farmacy_main.addMenuItem(sw.Promed.Actions.EvnRPStreamInputAction);
		this.menu_farmacy_main.addMenuItem(sw.Promed.Actions.swInvVedAction);
		this.menu_farmacy_main.addMenuItem(sw.Promed.Actions.swMedOstatAction);
		this.menu_farmacy_main.addMenuItem(sw.Promed.Actions.EvnReceptTrafficBookViewAction);
	}
		// Документы 
		this.menu_documents = new Ext.menu.Menu({
			id: 'menu_documents',
			items: []
		});
		
		this.menu_documents.addMenuItem(sw.Promed.Actions.RegistryViewAction);
		this.menu_documents.addMenuItem(sw.Promed.Actions.RegistryNewViewAction);
		this.menu_documents.addSeparator();
		
		this.menu_documents.addMenuItem({
			text:lang['patomorfologiya'],
			iconCls: 'pathomorph-16',
			menu: new Ext.menu.Menu(
			{
				id: 'menu_pathomorph',
				items: [
					sw.Promed.Actions.EvnDirectionHistologicViewAction,
					sw.Promed.Actions.EvnHistologicProtoViewAction,
					'-',
					sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
					sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
				]
			})
		});
		
		if (isAdmin || isTestLpu)
		{
			//this.menu_documents.addSeparator();
			this.menu_documents.addMenuItem(sw.Promed.Actions.swAttachmentDemandAction);
		}
		
		this.menu_documents.addMenuItem(
		{
			text: lang['obrascheniya'],
			iconCls: 'petition-stream16',
			menu: new Ext.menu.Menu(
			{
				id: 'menu_treatment',
				items:
				[
					sw.Promed.Actions.swTreatmentSearchAction,
					sw.Promed.Actions.swTreatmentReportAction
				]
			}),
			hidden: !isAccessTreatment()
		});
		
		this.menu_documents.addMenuItem({
			text:lang['svidetelstva'],
			//hidden: (!getGlobalOptions().medstafffact) || (getGlobalOptions().medstafffact.length==0) && !isAdmin,
			hidden: !isMedSvidAccess(),
			iconCls: 'medsvid16',
			menu: new Ext.menu.Menu(
			{
				id: 'menu_medsvid_main',
				items: [
					sw.Promed.Actions.swMedSvidBirthAction,
					sw.Promed.Actions.swMedSvidDeathAction,
					sw.Promed.Actions.swMedSvidPDeathAction,
					'-',
					sw.Promed.Actions.swMedSvidPrintAction
				]
			})
		});
		
		this.menu_documents.addMenuItem({
			text:lang['izvescheniya_o_dtp'],
			iconCls: 'pol-dtp16',
			hidden: (!isAdmin || !isLpuAdmin || getRegionNick().inlist(['kz'])),
			menu: new Ext.menu.Menu(
			{
				id: 'menu_dtp',
				items:
				[
					sw.Promed.Actions.swEvnDtpWoundViewAction,
					sw.Promed.Actions.swEvnDtpDeathViewAction
				]
			})
		});

		this.menu_documents.addSeparator();
		this.menu_documents.addMenuItem(sw.Promed.Actions.swCardCallFindAction);
		
		// убрать условие после открытия KerRocordBookAction
		if (isAdmin)
		{
			this.menu_documents.addSeparator();
		}
		this.menu_documents.addMenuItem(sw.Promed.Actions.KerRocordBookAction);
		
		this.menu_documents.addMenuItem(sw.Promed.Actions.EvnStickViewAction);
		
		this.menu_reports = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_reports',
			items: [
				sw.Promed.Actions.ReportStatViewAction,
				'-',
				sw.Promed.Actions.swF14OMSPerAction,
				sw.Promed.Actions.swF14OMSAction,
				sw.Promed.Actions.swF14OMSFinAction
			]
		});
		if(isAdmin){
			this.menu_reports.addSeparator();
			this.menu_reports.addMenuItem(sw.Promed.Actions.swReportEngineAction);
		}

		this.menu_windows = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_windows',
			items: [
				'-'
			]
		});

		this.menu_help = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_help',
			items:
			[
				sw.Promed.Actions.PromedHelp,
				sw.Promed.Actions.PromedForum,
				'-',
				sw.Promed.Actions.PromedAbout
			]
		});

		this.menu_exit = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_help',
			items:
			[
				sw.Promed.Actions.PromedHelp, sw.Promed.Actions.PromedAbout
			]
		});
		if ( isFarmacyUser() ) {
			this.user_menu = new Ext.menu.Menu(
			{
				//plain: true,
				id: 'user_menu',
				items:
				[
					{
						disabled: true,
						iconCls: 'user16',
						text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'Аптека : '+Ext.globalOptions.globals.OrgFarmacy_Nick,
						xtype: 'tbtext'
					}
				]
			});
		} else if ( isSmoTfomsUser() ) {
			this.user_menu = new Ext.menu.Menu(
			{
				//plain: true,
				id: 'user_menu',
				items:
				[
					{
						disabled: true,
						iconCls: 'user16',
						text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr,
						xtype: 'tbtext'
					}
				]
			});
		} else {
			this.user_menu = new Ext.menu.Menu(
			{
				//plain: true,
				id: 'user_menu',
				items:
				[
					{
						disabled: true,
						iconCls: 'user16',
						text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
						xtype: 'tbtext'
					}
				]
			});
		}		

	// панель меню
	main_menu_panel = new sw.Promed.Toolbar({
		autoHeight: true,
		region: 'north',
		items:
		[
			{
				text:lang['pasport_mo'],
				id: '_menu_lpu',
				iconCls: 'lpu16',
				menu: this.menu_passport_lpu,
				hidden: false, //!isSuperAdmin() && !isLpuAdmin(),
				tabIndex: -1
			},
			/*{
				text:lang['registratura'],
				iconCls: 'reg16',
				menu: this.menu_reg_main,
				tabIndex: -1
			},*/
			{
				text:lang['llo'],
				id: '_menu_dlo',
				iconCls: 'dlo16',
				menu: this.menu_dlo_main,
				tabIndex: -1
			},
			{
				text: lang['poliklinika'],
				id: '_menu_polka',
				iconCls: 'polyclinic16',
				hidden: (getGlobalOptions().region.nick == 'saratov'),
				menu: this.menu_polka_main,
				tabIndex: -1
			},
			{
				text: lang['statsionar'],
				id: '_menu_stac',
				iconCls: 'stac16',
				menu: this.menu_stac_main,
				tabIndex: -1,
				hidden: (getGlobalOptions().region.nick == 'saratov')
			},
			{
				text: lang['paraklinika'],
				id: '_menu_parka',
				iconCls: 'parka16',
				menu: this.menu_parka_main,
				tabIndex: -1,
				hidden: (getGlobalOptions().region.nick == 'saratov')
			},
			{
				text: lang['stomatologiya'],
				id: '_stomatka',
				iconCls: 'stomat16',
				hidden: (getGlobalOptions().region.nick == 'saratov'),
				menu: this.menu_stomat_main,
				tabIndex: -1
			},
			{
				text: lang['apteka'],
				id: '_menu_farmacy',
				iconCls: 'farmacy16',
				menu: this.menu_farmacy_main,
				tabIndex: -1,
				hidden: (getGlobalOptions().region.nick == 'saratov')
			},
			{
				text: lang['dokumentyi'],
				id: '_menu_documents',
				iconCls: 'documents16',
				menu: this.menu_documents,
				tabIndex: -1,
				hidden: (getGlobalOptions().region.nick == 'saratov')
			},
			{
				text:lang['servis'],
				id: '_menu_service',
				iconCls: 'service16',
				menu: this.menu_dlo_service,
				tabIndex: -1
			},
			{
				text:lang['otchetyi'],
				id: '_menu_reports',
				iconCls: 'reports16',
				menu: this.menu_reports,
				tabIndex: -1
			},
			{
				text: lang['okna'],
				id: '_menu_windows',
				iconCls: 'windows16',
				listeners: {
					'click': function(e) {
						var menu = Ext.menu.MenuMgr.get('menu_windows');
						menu.removeAll();
						var number = 1;
						Ext.WindowMgr.each(function(wnd){
							if ( wnd.isVisible() )
							{
                        		if ( Ext.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
								else
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'x-btn-text',
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: lang['otkryityih_okon_net'],
								iconCls : 'x-btn-text',
								handler: function()
								{
								}
							});
						else
						{
							menu.add(new Ext.menu.Separator());
       						menu.add(new Ext.menu.Item(
								{
									text: lang['zakryit_vse_okna'],
									iconCls : 'close16',
									handler: function()
									{
										Ext.WindowMgr.each(function(wnd){
											if ( wnd.isVisible() )
											{
				        						wnd.hide();
											}
										});
									}
								})
							);
						}
					},
					'mouseover': function() {
						var menu = Ext.menu.MenuMgr.get('menu_windows');
						menu.removeAll();
						var number = 1;
						Ext.WindowMgr.each(function(wnd){
							if ( wnd.isVisible() )
							{
        						if ( Ext.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
								else
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'x-btn-text',
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: lang['otkryityih_okon_net'],
								iconCls : 'x-btn-text',
								handler: function()
								{
								}
							});
						else
						{
    						menu.add(new Ext.menu.Separator());
							menu.add(new Ext.menu.Item(
								{
									text: lang['zakryit_vse_okna'],
									iconCls : 'close16',
									handler: function()
									{
										Ext.WindowMgr.each(function(wnd){
											if ( wnd.isVisible() )
											{
				        						wnd.hide();
											}
										});
									}
								})
							);
						}
					}
				},
				menu: this.menu_windows,
				tabIndex: -1
			},
			{
				text:lang['pomosch'],
				id: '_menu_help',
				iconCls: 'help16',
				menu: this.menu_help,
				tabIndex: -1
			},
			{
				id: '_menu_tbfill',
				xtype : "tbfill"
			},
			{
				iconCls: 'progress16',
				id: '_menu_progress',
				text: '',
				hidden: true,
				tabIndex: -1
			},
			{
				iconCls: 'user16',
				id: '_user_menu',
				text: UserLogin,
				menu: this.user_menu,
				tabIndex: -1
			},
			'-',
			sw.Promed.Actions.PromedExit
		]
	});

    // Экшен для меню "Окна"
    sw.Promed.Actions.WindowsAction = new Ext.Action( {
        text: lang['okna'],
        iconCls: 'windows16',
        listeners: {
            'click': function(obj, e) {
				//log(e);
				if ( IS_DEBUG == 1 && e.altKey && e.shiftKey && e.ctrlKey )
				{
					new Ext.Window({
						title: lang['c_pervyim_aprelya'],
						width: 615,
						height: 595,
						items: [],
						html: '<object width="615" height="595" id="nordnet" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">'+
								'<param value="/img/ololo/ololo.swf" name="movie">'+
								'<embed width="615" height="595" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" bgcolor="white" quality="high" menu="false" src="/img/ololo/ololo.swf">'+
							'</object>'
					}).show();
				}
                var menu = Ext.menu.MenuMgr.get('menu_windows');
                menu.removeAll();
                var number = 1;
                Ext.WindowMgr.each(function(wnd){
                    if ( wnd.isVisible() )
                    {
                        if ( Ext.WindowMgr.getActive().id == wnd.id )
                        {
                            menu.add(new Ext.menu.Item(
                            {
                                text: number + ". " + wnd.title,
                                iconCls : 'checked16',
                                checked: true,
                                handler: function()
                                {
                                    Ext.getCmp(wnd.id).toFront();
                                }
                            })
                            );
                            number++;
                        }
                        else
                        {
                            menu.add(new Ext.menu.Item(
                            {
                                text: number + ". " + wnd.title,
                                iconCls : 'x-btn-text',
                                handler: function()
                                {
                                    Ext.getCmp(wnd.id).toFront();
                                }
                            })
                            );
                            number++;
                        }
                    }
                });
                if ( menu.items.getCount() == 0 )
                    menu.add({
                        text: lang['otkryityih_okon_net'],
                        iconCls : 'x-btn-text',
                        handler: function()
                        {
                        }
                    });
                else
                {
                    menu.add(new Ext.menu.Separator());
                    menu.add(new Ext.menu.Item(
                    {
                        text: lang['zakryit_vse_okna'],
                        iconCls : 'close16',
                        handler: function()
                        {
                            Ext.WindowMgr.each(function(wnd){
                                if ( wnd.isVisible() )
                                {
                                    wnd.hide();
                                }
                            });
                        }
                    })
                    );
                }
            },
            'mouseover': function() {
                var menu = Ext.menu.MenuMgr.get('menu_windows');
                menu.removeAll();
                var number = 1;
                Ext.WindowMgr.each(function(wnd){
                    if ( wnd.isVisible() )
                    {
                        if ( Ext.WindowMgr.getActive().id == wnd.id )
                        {
                            menu.add(new Ext.menu.Item(
                            {
                                text: number + ". " + wnd.title,
                                iconCls : 'checked16',
                                checked: true,
                                handler: function()
                                {
                                    Ext.getCmp(wnd.id).toFront();
                                }
                            })
                            );
                            number++;
                        }
                        else
                        {
                            menu.add(new Ext.menu.Item(
                            {
                                text: number + ". " + wnd.title,
                                iconCls : 'x-btn-text',
                                handler: function()
                                {
                                    Ext.getCmp(wnd.id).toFront();
                                }
                            })
                            );
                            number++;
                        }
                    }
                });
                if ( menu.items.getCount() == 0 )
                    menu.add({
                        text: lang['otkryityih_okon_net'],
                        iconCls : 'x-btn-text',
                        handler: function()
                        {
                        }
                    });
                else
                {
                    menu.add(new Ext.menu.Separator());
                    menu.add(new Ext.menu.Item(
                    {
                        text: lang['zakryit_vse_okna'],
                        iconCls : 'close16',
                        handler: function()
                        {
                            Ext.WindowMgr.each(function(wnd){
                                if ( wnd.isVisible() )
                                {
                                    wnd.hide();
                                }
                            });
                        }
                    })
                    );
                }
            }
        },
        menu: this.menu_windows,
        tabIndex: -1
    });
	
	main_promed_toolbar = main_menu_panel;
	
	main_card_toolbar_active = 0;
	
	main_card_toolbar = new Ext.Panel({
		layout: 'card',
		region: 'north',
		cls: 'x-panel-mc',
		style: 'padding: 0px;',
		border: false,
		activeItem: main_card_toolbar_active,
		items: [
			main_promed_toolbar
		]
	});
	
	// центральная панель
	main_center_panel = new Ext.Panel({
		id: 'main-center-panel',
		layout: 'fit',
        tbar: main_card_toolbar,
		region: 'center',
		bodyStyle:'width:100%;height:100%;background:#aaa;padding:0;'
	});

	// проверяем настройку на сервере дя работы с Картридером
	
	/*$cardreader_is_enable = getGlobalOptions()['card_reader_is_enable'] ? getGlobalOptions()['card_reader_is_enable'] : false;
	if ( $cardreader_is_enable === true )
	{
		if (navigator.javaEnabled()) {
			var appl = Ext.getBody().createChild({
				name: 'apl',
				tag: 'applet',
				archive:'applets/swSmartCard_2ids.jar',
				code:'swan/SmartCard/swSmartCard',
				width: 0,
				height: 0,
				id: 'card_appl',
				style:'width:1px,height:1px'
			});
		} else {
			setPromedInfo('Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>', 'javamashine-info');
		}
	}*/

	log(lang['podklyuchaem_plagin_kriptopro']);
	sw.Applets.CryptoPro.initCryptoPro();

	if(Ext.globalOptions.others.enable_uecreader) {
		log(lang['podklyuchaem_applet_uek']);
		sw.Applets.uec.initUec();
	}
	if(Ext.globalOptions.others.enable_bdzreader) {
		log(lang['podklyuchaem_applet_bdz']);
		sw.Applets.bdz.initBdz();
	}

	if ( Ext.globalOptions.others.enable_barcodereader ) {
		log(lang['podklyuchaem_applet_dlya_skanera_shtrih-kodov']);
		sw.Applets.BarcodeScaner.initBarcodeScaner();
	}
	
	/*main_center_panel2 = new Ext.Panel({
		autoHeight: true,
		region: 'north',
		bodyStyle:'width:100%;background:#aaa;padding:0;',
		html: 'lalala'
	});*/

	main_tabs_panel = new Ext.TabPanel({
		id: 'main-tabs-panel',
		bodyStyle:'background-color:#16334a;',
		//bodyStyle:'background-color:#aaa;',
		resizeTabs: true,
		minTabWidth: 115,
		tabWidth: 135,
		enableTabScroll: true,
		defaults: {autoScroll: true},
		html: '<div></div>'
	});

	main_center_panel.add(main_tabs_panel);
	
	
	main_messages_tpl = new Ext.XTemplate(
	 '<div onMouseOver="if(isMouseLeaveOrEnter(event, this)){this.style.display=&quot;block&quot;; Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(220);}" ',
		'onMouseOut="if(isMouseLeaveOrEnter(event, this)){this.style.display=&quot;block&quot;; Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(50);}" ',
		'onClick="getWnd(&quot;swMessagesViewWindow&quot;).show({mode: &quot;newMessages&quot;});" ',
		'style="background: silver; padding: 3px 0px 3px 5px; height: 48px; border: 1px solid gray; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -webkit-box-shadow: 1px 1px 1px #888888; box-shadow: 1px 1px 1px #888888;cursor:pointer;cursor:hand;">',
		'<div style="float: left; width: 48px; height: 48px;" class="mail48unread">',
		'<div style="float: left; font: bold 12px Tahoma; color:#444; margin-top:20px; opacity:0.8;filter: alpha(opacity=80); padding:1px; width:100%;text-align:center;">{count}</div>',
		'</div>',
		'<div style="margin-left: 56px;margin-top:6px;"><a style="font: normal 13px Tahoma; color: black;text-shadow: 1px 1px #cccccc;" href="#">У Вас <b>{count}</b> непрочитанн{okch} сообщен{ok}</a></div>',
		'</div>'
	);
	
	main_messages_panel = new Ext.Panel({
		id: 'main-messages-panel',
		hidden: true,
		style: 'position: absolute; z-index: 20000;',
		bodyStyle: 'background: none;',
		height: 56,
		mLeft: 50, // Сколько px панели видно слева =)
		mTop: 220, // Отступ сверху
		hideOver: function(shift) {
			this.setPosition(Ext.getBody().getWidth()-shift, this.mTop);
		},
		border: false,
		width: 225,
		html: '<div onMouseOver="Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(220);" '+
			'onMouseOut="Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(50);" '+
			'onClick="getWnd(&quot;swMessagesViewWindow&quot;).show({mode: &quot;newMessages&quot;});" '+
			'style="background: silver; padding: 3px 0px 3px 5px; height: 48px; border: 1px solid gray; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -webkit-box-shadow: 1px 1px 1px #888888; box-shadow: 1px 1px 1px #888888;">'+
			'<div style="float: left; width: 48px; height: 48px;" class="mail48">'+
			'<div style="float: left; font: bold 12px Tahoma; color:#444; margin-top:20px; opacity:0.8;filter: alpha(opacity=80); padding:1px; width:100%;text-align:center;"></div></div>'+
			'<div style="margin-left: 56px;margin-top:6px;"><a style="font: normal 13px Tahoma; color: black;text-shadow: 1px 1px #cccccc;" href="#">У Вас <b>нет</b> непрочитанных сообщений</a></div>'+
			'</div>'
	});
	main_center_panel.add(main_messages_panel);
	
	main_frame = new Ext.Viewport({
		layout:'border',
		items: [
			main_center_panel/*,
			left_panel
			new Ext.Panel({
				region: 'south',
				title: '_',
				height: 1,
				id: 'ajax_state'
			})*/
		],
		listeners: {
			resize: function()
			{
				main_promed_toolbar.setWidth('auto');
				main_messages_panel.hideOver(main_messages_panel.mLeft);
			}
		}
	});

    if (Ext.globalOptions.appearance.menu_type == 'ribbon' && typeof main_promed_toolbar.delegateUpdates == 'function') {
        main_promed_toolbar.delegateUpdates();
    }

	main_frame.doLayout();
	main_card_toolbar.doLayout();
}

Ext.onReady(function (){
	if ( is_ready )
	{
		return;
	}
	
	// Запускалка
	sw.Promed.tasks = new Ext.util.TaskRunner();
	// Маска поверх всех окон
	var mask = Ext.getBody().mask();
	//Ext.Element.setZIndex
	mask.setStyle('z-index', Ext.WindowMgr.zseed + 10000);
	// log(Ext.WindowMgr.zseed);
	// log(Ext.WindowMgr);
	sw.Promed.mask = new Ext.LoadMask(Ext.getBody(), {msg: LOAD_WAIT});
	sw.Promed.mask.hide();
	
	Ext.Ajax.timeout = 1800000;
	
	// Значения по умолчанию
	loadPromed( function() {
		// Инициализация всплывыющих подсказок
		Ext.QuickTips.init();
		
		// http://172.19.61.24:85/issues/show/2264
		function unload_page(event) {
			// еще выше надо будет добавить признак изменения документов, который должен лежать в глобальной области
			/*if (event)
				event.returnValue = "Вы хотите завершить работу с системой или перезагрузить страницу.";
			else */
				return "Вы хотите завершить работу с системой или перезагрузить страницу."
		};
		if (Ext.isIE)
		{
			var root = window.addEventListener || window.attachEvent ? window : document.addEventListener ? document : null;
			if (typeof(root.onbeforeunload) != "undefined") root.onbeforeunload = unload_page;
		}
		else 
		{
			window.onbeforeunload = unload_page;
		}
		// Подменяем данные с локального хранилища при первом входе
		Ext.setLocalOptions();
		
		// собственно загрузка меню и прочего 
		// TODO: Надо оптимизировать и разделить функционал этой функции для более быстрой загрузки 
		loadModules();
		// указываем регион
		setPromedInfo(' / '+getGlobalOptions().region.name,'promed-region');
		// Старт тасков
		if (getNoticeOptions().is_popup_message) {
			this.taskTimer = function() {
				return {run: taskRun, interval: ((getGlobalOptions().message_time_limit)?getGlobalOptions().message_time_limit:5)*60*1000};
			}
			sw.Promed.tasks.start(this.taskTimer());
		}
		if(getNoticeOptions().is_extra_message) {
			this.extraTaskTimer = function() {
				return {run: extraTaskRun, interval: 300*1000};
			}
			sw.Promed.tasks.start(this.extraTaskTimer());
		}

		if (getGlobalOptions().TOUZLpuArr) {
			getWnd('swSelectLpuWindow').show();
		}
		
		//main_messages_panel.setPosition(Ext.getBody().getWidth()-main_messages_panel.mLeft, main_messages_panel.mTop);
	});
});

