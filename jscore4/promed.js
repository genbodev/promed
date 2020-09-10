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
sw.codeInfo = {};
sw.notices = [];
	//var $isFarmacy = false;

// объект для хранения прав
sw.roles = {};
// для хранения состояний гридов/форм с stateful: true
Ext.state.Manager.setProvider(new Ext.state.CookieProvider({
    expires: new Date(new Date().getTime()+(1000*60*60*24*7)) // на 7 дней
}));
Ext.override(Ext.Component,{ stateful:false }); // по умолчанию состояния не сохраняются

var swLpuBuildingGlobalStore = null;
var swLpuSectionGlobalStore = null;
var swLpuSectionWardGlobalStore = null;
var swLpuUnitGlobalStore = null;
var swMedServiceGlobalStore = null;
var swMedStaffFactGlobalStore = null;
var swProMedUserGlobalStore = null;

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

// TODO: Функции getPromedUserInfo и getCookie надо подписать (phpDoc) и возможно вынести в отдельный модуль (или в swFunctions).

function getPromedUserInfo()
{
	var result = new Array();
	result.lpu_nick = String(getGlobalOptions().lpu_nick);
	result.lpu_id = String(getGlobalOptions().lpu_id);
	result.sessionId = getCookie('PHPSESSID');
	result.groups = getGlobalOptions().groups;
	return result;
}

function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset)
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}

// Функция загрузки модулей Промед
function loadModules()
{
	is_ready = true;

	// TODO: Проблема определения админа на клиенте в том, что эти данные всегда можно подделать для клиента
	// С одной стороны это хорошо, потому что позволяет набором инструкций получать доступ к определенному функционалу для тестирования в случае ошибки 
	isAdmin = (/SuperAdmin/.test(getGlobalOptions().groups));
	isFarmacyInterface = false; // хотя здесь можно просто написать фальш.

	// Конфиги акшенов
//	sw.Promed.Actions = {
//		swEvnPLEvnPSSearchAction: {
//			text: 'Поиск ТАП/КВС',
//			tooltip: 'Поиск ТАП/КВС',
//			iconCls : 'test16',
//			handler: function() {
//				getWnd('swEvnPLEvnPSSearchWindow').show();
//			},
//			hidden: false
//		},
//		swEvnPLEvnPSViewAction: {
//			text: 'Выбор ТАП/КВС',
//			tooltip: 'Выбор ТАП/КВС',
//			iconCls : 'test16',
//			handler: function() {
//				getWnd('swEvnPLEvnPSSearchWindow').show({
//					Person_id: 421380
//				});
//			},
//			hidden: false
//		},
//		EvnDirectionMorfoHistologicViewAction: {
//			text: 'Направления на патоморфогистологическое исследование',
//			tooltip: 'Журнал направлений на патоморфогистологическое исследование',
//			iconCls : 'pathomorph16',
//			handler: function() {
//				getWnd('swEvnDirectionMorfoHistologicViewWindow').show();
//			},
//			hidden: false
//		},
//		EvnStickViewAction: {
//			text: 'ЛВН: Поиск',
//			tooltip: 'Поиск листков временной нетрудоспособности',
//			iconCls : 'lvn-search16',
//			handler: function() {
//				getWnd('swEvnStickViewWindow').show();
//			},
//			hidden: false //(!isAdmin || IS_DEBUG != 1)
//		},
//		EvnMorfoHistologicProtoViewAction: {
//			text: 'Протоколы патоморфогистологических исследований',
//			tooltip: 'Журнал протоколов патоморфогистологических исследований',
//			iconCls : 'pathomorph16',
//			handler: function() {
//				getWnd('swEvnMorfoHistologicProtoViewWindow').show();
//			},
//			hidden: false
//		},
//		EvnHistologicProtoViewAction: {
//			text: 'Протоколы патологогистологических исследований',
//			tooltip: 'Журнал протоколов патологогистологических исследований',
//			iconCls : 'pathohistproto16',
//			handler: function() {
//				getWnd('swEvnHistologicProtoViewWindow').show();
//			},
//			hidden: false
//		},
//		EvnDirectionHistologicViewAction: {
//			text: 'Направления на патологогистологическое исследование',
//			tooltip: 'Журнал направлений на патологогистологическое исследование',
//			iconCls : 'pathohist16',
//			handler: function() {
//				getWnd('swEvnDirectionHistologicViewWindow').show();
//			},
//			hidden: false
//		},
//		PersonDoublesSearchAction: {
//			text: 'Работа с двойниками',
//			tooltip: 'Работа с двойниками',
//			iconCls: 'doubles16',
//			handler: function() {
//				getWnd('swPersonDoublesSearchWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		PersonDoublesModerationAction: {
//			text: 'Модерация двойников',
//			tooltip: 'Модерация двойников',
//			iconCls: 'doubles-mod16',
//			handler: function() {
//				getWnd('swPersonDoublesModerationWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		PersonUnionHistoryAction: {
//			text: 'История модерации двойников',
//			tooltip: 'История модерации двойников',
//			iconCls: 'doubles-history16',
//			handler: function() {
//				getWnd('swPersonUnionHistoryWindow').show();
//			},
//			hidden: true
//		},
//		UslugaComplexViewAction: {
//			text: 'Комплексные услуги',
//			tooltip: 'Комплексные услуги',
//			iconCls: 'services-complex16',
//			handler: function() {
//				getWnd('swUslugaComplexViewWindow').show();
//			},
//			hidden: true
//		},
//		UslugaComplexTreeAction: {
//			text: 'Комплексные услуги',
//			tooltip: 'Комплексные услуги',
//			iconCls: 'services-complex16',
//			handler: function()
//			{
//				getWnd('swUslugaComplexTreeWindow').show();
//			},
//			hidden: (!isAdmin || IS_DEBUG != 1)
//		},
//		RegistryViewAction: {
//			text: 'Счета и реестры',
//			tooltip: 'Счета и реестры',
//			iconCls : 'service-reestrs16',
//			handler: function() {
//				getWnd('swRegistryViewWindow').show();
//			},
//			hidden: false
//		},
//		MiacExportAction: {
//			text: 'Выгрузка для МИАЦ',
//			tooltip: 'Выгрузка данных для МИАЦ',
//			iconCls : 'service-reestrs16',
//			handler: function() {
//				getWnd('swMiacExportWindow').show();
//			},
//			hidden: (getGlobalOptions().region.nick != 'ufa')
//		},
//		MiacExportSheduleOptionsAction: {
//			text: 'Настройки автоматической выгрузки для МИАЦ',
//			tooltip: 'Настройки автоматической выгрузки для МИАЦ',
//			iconCls : 'settings16',
//			handler: function() {
//				getWnd('swMiacExportSheduleOptionsWindow').show();
//			},
//			hidden: (getGlobalOptions().region.nick != 'ufa')
//		},
//		/*RegistryEditAction: {
//			text: 'Редактирование реестра (счета)',
//			tooltip: 'Редактирование реестра (счета)',
//			iconCls : 'x-btn-text',
//			handler: function() {
//				getWnd('swRegistryEditWindow').show({
//					action: 'add',
//					callback: Ext.emptyFn,
//					onHide: Ext.emptyFn,
//					RegistryType_id: 2
//				});
//			}
//		},*/
///*
//		DrugRequestPrintAllAction: {
//			text: 'Печать заявки',
//			tooltip: 'Печать заявки по выбранной ЛПУ или по всем ЛПУ',
//			iconCls : 'x-btn-text',
//			handler: function() {
//				getWnd('swDrugRequestPrintAllWindow').show({
//					onHide: Ext.emptyFn
//				});
//			}
//		},
//*/
//		DrugTorgLatinNameEditAction: {
//			text: WND_DLO_DRUGTORGLATINEDIT,
//			tooltip: 'Редактирование латинского наименования медикамента',
//			iconCls : 'drug-viewtorg16',
//			handler: function()
//			{
//				getWnd('swDrugTorgViewWindow').show();
//			}
//		},
//
//		DrugMnnLatinNameEditAction: {
//			text: WND_DLO_DRUGMNNLATINEDIT,
//			tooltip: 'Редактирование латинского наименования МНН',
//			iconCls : 'drug-viewmnn16',
//			handler: function()
//			{
//				getWnd('swDrugMnnViewWindow').show({
//					privilegeType: 'all'
//				});
//			}
//		},
//
//		PersonCardSearchAction: {
//			text: WND_POL_PERSCARDSEARCH,
//			tooltip: 'Поиск карты пациента',
//			iconCls : 'card-search16',
//			handler: function()
//			{
//				getWnd('swPersonCardSearchWindow').show();
//			}
//		},
//
//		PersonCardViewAllAction: {
//			text: WND_POL_PERSCARDVIEWALL,
//			tooltip: 'Картотека: работа со всей картотекой',
//			iconCls : 'card-view16',
//			handler: function()
//			{
//				getWnd('swPersonCardViewAllWindow').show();
//			}
//		},
//
//		PersonCardStateViewAction: {
//			text: WND_POL_PERSCARDSTATEVIEW,
//			tooltip: 'Просмотр журнала движения по картотеке пациентов',
//			iconCls : 'card-state16',
//			handler: function()
//			{
//				getWnd('swPersonCardStateViewWindow').show();
//			}
//		},
//
//		AutoAttachViewAction: {
//			text: 'Автоматическое прикрепление',
//			tooltip: 'Автоматическое прикрепление',
//			iconCls : 'card-state16',
//			hidden: !isAdmin,
//			handler: function()
//			{
//				var id_salt = Math.random();
//				var win_id = 'report' + Math.floor(id_salt*10000);
//				// собственно открываем окно и пишем в него
//				var win = window.open('/?c=AutoAttach&m=doAutoAttach', win_id);
//			}
//		},
//
//		PersonDispSearchAction: {
//			text: WND_POL_PERSDISPSEARCH,
//			tooltip: 'Поиск диспансерной карты пациента',
//			iconCls : 'disp-search16',
//			handler: function()
//			{
//				getWnd('swPersonDispSearchWindow').show();
//			},
//			hidden: false//!(isAdmin || isTestLpu)
//		},
//		PersonDispViewAction: {
//			text: WND_POL_PERSDISPSEARCHVIEW,
//			tooltip: 'Просмотр диспансерной карты пациента',
//			iconCls : 'disp-view16',
//			handler: function()
//			{
//				getWnd('swPersonDispViewWindow').show({mode: 'view'});
//			},
//			hidden: false//!(isAdmin || isTestLpu)
//		},
//		EvnPLEditAction: {
//			text: 'Талон амбулаторного пациента: Поиск',
//			tooltip: 'Поиск талона амбулаторного пациента',
//			iconCls : 'pol-eplsearch16',
//			handler: function()
//			{
//				getWnd('swEvnPLSearchWindow').show();
//			}
//		},
//		EvnPLStreamInputAction: {
//			text: MM_POL_EPLSTREAM,
//			tooltip: 'Потоковый ввод талонов амбулаторного пациента',
//			iconCls : 'pol-eplstream16',
//			handler: function()
//			{
//				getWnd('swEvnPLStreamInputWindow').show();
//			}
//		},
//
//		LpuStructureViewAction: {
//			text: MM_LPUSTRUC,
//			tooltip: 'Структура ЛПУ',
//			iconCls : 'lpu-struc16',
//			hidden: !isAdmin && !isLpuAdmin() && !isCadrUserView(),
//			handler: function()
//			{
//				getWnd('swLpuStructureViewForm').show();
//			}
//		},
//		
//		OrgStructureViewAction: {
//			text: 'Структура организации',
//			tooltip: 'Структура организации',
//			iconCls : 'lpu-struc16',
//			hidden: (!isAdmin && !isOrgAdmin()) || !isDebug(),
//			handler: function()
//			{
//				getWnd('swOrgStructureWindow').show();
//			}
//		},
//		
//		FundHoldingViewAction: {
//			text: 'Фондодержание',
//			tooltip: 'Фондодержание',
//			iconCls : 'lpu-struc16',
//			hidden: !isAdmin ,//&& !getGlobalOptions()['mp_is_zav'] && !getGlobalOptions()['mp_is_uch'],
//			handler: function()
//			{
//				getWnd('swFundHoldingViewForm').show();
//			}
//		},
//		
//		LgotFindAction: {
//			text: MM_DLO_LGOTSEARCH,
//			tooltip: 'Поиск льготников',
//			iconCls : 'lgot-search16',
//			handler: function()
//			{
//				getWnd('swPrivilegeSearchWindow').show();
//			}
//		},
//		LgotAddAction: {
//			text: MM_DLO_LGOTADD,
//			tooltip: 'Добавление льготника',
//			iconCls : 'x-btn-text',
//			handler: function()
//			{
//				if (getWnd('swPersonSearchWindow').isVisible())
//				{
//					Ext.Msg.alert('Сообщение', 'Окно поиска человека уже открыто');
//					return false;
//				}
//
//				if (getWnd('swPrivilegeEditWindow').isVisible())
//				{
//					Ext.Msg.alert('Сообщение', 'Окно редактирования льготы уже открыто');
//					return false;
//				}
//
//				getWnd('swPersonSearchWindow').show({
//					onSelect: function(person_data) {
//						getWnd('swPrivilegeEditWindow').show({
//							params: {
//								action: 'add',
//								Person_id: person_data.Person_id,
//								Server_id: person_data.Server_id
//							}
//						});
//					},
//					searchMode: 'all'
//				});
//			}
//		},
//		EvnUdostViewAction: {
//			text: MM_DLO_UDOSTLIST,
//			tooltip: 'Просмотр удостоверений',
//			iconCls : 'udost-list16',
//			handler: function()
//			{
//				getWnd('swUdostViewWindow').show();
//			}
//		},
//		EvnUdostAddAction: {
//			text: MM_DLO_UDOSTADD,
//			tooltip: 'Добавление удостоверений',
//			iconCls : 'x-btn-text',
//			handler: function()
//			{
//				if (getWnd('swPersonSearchWindow').isVisible())
//				{
//					Ext.Msg.alert('Сообщение', 'Окно поиска человека уже открыто');
//					return false;
//				}
//
//				if (getWnd('swEvnUdostEditWindow').isVisible())
//				{
//					Ext.Msg.alert('Сообщение', 'Окно редактирования удостоверения уже открыто');
//					return false;
//				}
//
//				getWnd('swPersonSearchWindow').show({
//					onSelect: function(person_data) {
//						getWnd('swEvnUdostEditWindow').show({
//							action: 'add',
//							onHide: function() {
//								getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
//							},
//							Person_id: person_data.Person_id,
//							PersonEvn_id: person_data.PersonEvn_id,
//							Server_id: person_data.Server_id
//						});
//					},
//					searchMode: 'all'
//				});
//			}
//		},
//		EvnReceptAddStreamAction: {
//			text: MM_DLO_RECSTREAM,
//			tooltip: 'Ввод рецептов',
//			iconCls : 'receipt-stream16',
//			handler: function()
//			{
//				getWnd('swReceptStreamInputWindow').show();
//			}
//		},
//		EvnReceptAddAction: {
//			text: MM_DLO_RECADD,
//			tooltip: 'Добавление рецепта',
//			iconCls : 'x-btn-text',
//			handler: function()
//			{
//				if (getWnd('swPersonSearchWindow').isVisible())
//				{
//					Ext.Msg.alert('Сообщение', 'Окно поиска человека уже открыто');
//					return false;
//				}
//
//				if (getWnd('swEvnReceptEditWindow').isVisible())
//				{
//					Ext.Msg.alert('Сообщение', 'Окно редактирования рецепта уже открыто');
//					return false;
//				}
//
//				getWnd('swPersonSearchWindow').show({
//					onSelect: function(person_data) {
//						getWnd('swEvnReceptEditWindow').show({
//							action: 'add',
//							onHide: function() {
//								getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
//							},
//							Person_id: person_data.Person_id,
//							PersonEvn_id: person_data.PersonEvn_id,
//							Server_id: person_data.Server_id
//						});
//					},
//					searchMode: 'all'
//				});
//			}
//		},
//		EvnReceptFindAction: {
//			text: MM_DLO_RECSEARCH,
//			tooltip: 'Поиск рецептов',
//			iconCls : 'receipt-search16',
//			handler: function()
//			{
//				getWnd('swEvnReceptSearchWindow').show();
//			}
//		},
//		EvnReceptInCorrectFindAction: {
//			text: 'Журнал отсрочки',
//			tooltip: 'Журнал отсрочки',
//			iconCls : 'receipt-incorrect16',
//			handler: function()
//			{
//				getWnd('swReceptInCorrectSearchWindow').show();
//			}
//		},
//		PersonPrivilegeWOWSearchAction: {
//			text: 'Регистр ВОВ: Поиск',
//			tooltip: 'Регистр ВОВ: Поиск',
//			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
//			handler: function()
//			{
//				getWnd('PersonPrivilegeWOWSearchWindow').show();
//			}
//		},
//		PersonDispWOWStreamAction: {
//			text: 'Обследования ВОВ: Поточный ввод',
//			tooltip: 'Обследования ВОВ: Поточный ввод',
//			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
//			handler: function()
//			{
//				getWnd('EvnPLWOWStreamWindow').show();
//			}
//		},
//		PersonDispWOWSearchAction: {
//			text: 'Обследования ВОВ: Поиск',
//			tooltip: 'Обследования ВОВ: Поиск',
//			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
//			handler: function()
//			{
//				getWnd('EvnPLWOWSearchWindow').show();
//			}
//		},
//		PersonDopDispSearchAction: {
//			text: MM_POL_PERSDDSEARCH,
//			tooltip: 'Дополнительная диспансеризация: поиск',
//			iconCls : 'dopdisp-search16',
//			handler: function()
//			{
//				getWnd('swPersonDopDispSearchWindow').show();
//			}
//		},
//		PersonDopDispStreamInputAction: {
//			text: MM_POL_PERSDDSTREAMINPUT,
//			tooltip: 'Дополнительная диспансеризация: потоковый ввод',
//			iconCls : 'dopdisp-stream16',
//			handler: function()
//			{
//				getWnd('swPersonDopDispSearchWindow').show({mode: 'stream'});
//			}
//		},
//		EvnPLDopDispSearchAction: {
//			text: MM_POL_EPLDDSEARCH,
//			tooltip: 'Талон по доп. диспансеризации: поиск',
//			iconCls : 'dopdisp-epl-search16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispDopSearchWindow').show();
//			},
//			hidden: false //!isAdmin
//		},
//		EvnPLDopDispStreamInputAction: {
//			text: MM_POL_EPLDDSTREAM,
//			tooltip: 'Талон по доп. диспансеризации: потоковый ввод',
//			iconCls : 'dopdisp-epl-stream16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispDopSearchWindow').show({mode: 'stream'});
//			},
//			hidden: false //!isAdmin
//		},
//		EvnPLDispDop13SearchAction: {
//			text: MM_POL_EPLDD13SEARCH,
//			tooltip: MM_POL_EPLDD13SEARCH,
//			iconCls : 'dopdisp-epl-search16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispDop13SearchWindow').show();
//			},
//			hidden: false //!isAdmin
//		},
//		EvnPLDispDop13StreamInputAction: {
//			text: MM_POL_EPLDD13STREAM,
//			tooltip: MM_POL_EPLDD13STREAM,
//			iconCls : 'dopdisp-epl-stream16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispDop13SearchWindow').show({mode: 'stream'});
//			},
//			hidden: false //!isAdmin
//		},
//		EvnPLDispDop13SecondSearchAction: {
//			text: MM_POL_EPLDD13SECONDSEARCH,
//			tooltip: MM_POL_EPLDD13SECONDSEARCH,
//			iconCls : 'dopdisp-epl-search16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispDop13SecondSearchWindow').show();
//			},
//			hidden: false //!isAdmin
//		},
//		EvnPLDispDop13SecondStreamInputAction: {
//			text: MM_POL_EPLDD13SECONDSTREAM,
//			tooltip: MM_POL_EPLDD13SECONDSTREAM,
//			iconCls : 'dopdisp-epl-stream16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispDop13SecondSearchWindow').show({mode: 'stream'});
//			},
//			hidden: false //!isAdmin
//		},
//		EvnPLDispTeen14SearchAction: {
//			text: 'Диспансеризация 14-летних подростков: Поиск',
//			tooltip: 'Диспансеризация 14-летних подростков: Поиск',
//			iconCls : 'dopdisp-teens-search16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispTeen14SearchWindow').show();
//			},
//			hidden: false
//		},
//		EvnPLDispTeen14StreamInputAction: {
//			text: 'Диспансеризация 14-летних подростков: Поточный ввод',
//			tooltip: 'Диспансеризация 14-летних подростков: Поточный ввод',
//			iconCls : 'dopdisp-teens-stream16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispTeen14SearchWindow').show({mode: 'stream'});
//			},
//			hidden: false
//		},
//		EvnPLDispSomeAdultSearchAction: {
//			text: 'Диспансеризация отдельных групп взрослого населения: Поиск',
//			hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'),
//			tooltip: 'Диспансеризация отдельных групп взрослого населения: Поиск',
//			iconCls : 'dopdisp-teens-search16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispSomeAdultSearchWindow').show();
//			}
//		},
//		EvnPLDispSomeAdultStreamInputAction: {
//			text: 'Диспансеризация отдельных групп взрослого населения: Поточный ввод',
//			hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'),
//			tooltip: 'Диспансеризация отдельных групп взрослого населения: Поточный ввод',
//			iconCls : 'dopdisp-teens-stream16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispSomeAdultStreamInputWindow').show();
//			}
//		},
//		ReestrsViewAction: {
//			text: 'Счета и реестры',
//			tooltip: 'Счета и реестры',
//			iconCls : 'service-reestrs16',
//			handler: function()
//			{
//				Ext.Msg.alert('Сообщение', 'Данный модуль пока недоступен!');
//			}
//		},
//		DrugRequestEditAction: {
//			text: 'Заявка на лекарственные средства: Ввод',
//			tooltip: 'Работа со заявкой на лекарственные средства',
//			iconCls : 'x-btn-text',
//			handler: function()
//			{
//				getWnd('swDrugRequestEditForm').show({mode: 'edit'});
//			},
//			hidden:(IS_DEBUG!=1)
//		},
//		DrugRequestViewAction: {
//			text: 'Заявка на лекарственные средства: Просмотр',
//			tooltip: 'Просмотр заявок',
//			iconCls : 'drug-request16',
//			handler: function()
//			{
//				getWnd('swDrugRequestViewForm').show();
//			},
//			hidden: (getGlobalOptions().region.nick!='perm')
//		},
//		NewDrugRequestViewAction: {
//			text: 'Новая заявка на лекарственные средства: Просмотр',
//			tooltip: 'Просмотр заявок',
//			iconCls : 'drug-request16',
//			handler: function()
//			{
//				getWnd('swNewDrugRequestViewForm').show();
//			},
//			hidden: (getGlobalOptions().region.nick=='perm')
//		},
//		OrgFarmacyViewAction: {
//			text: MM_DLO_OFVIEW,
//			tooltip: 'Работа с просмотром и редактированием аптек',
//			iconCls : 'farmview16',
//			handler: function()
//			{
//				getWnd('swOrgFarmacyViewWindow').show();
//			},
//			hidden : !isAdmin
//		},
//		OstAptekaViewAction: {
//			text: MM_DLO_MEDAPT,
//			tooltip: 'Работа с остатками медикаментов по аптекам',
//			iconCls : 'drug-farm16',
//			handler: function()
//			{
//				getWnd('swDrugOstatByFarmacyViewWindow').show();
//			}
//		},
//		OstSkladViewAction: {
//			text: MM_DLO_MEDSKLAD,
//			tooltip: 'Работа с остатками медикаментов на аптечном складе',
//			iconCls : 'drug-sklad16',
//			handler: function()
//			{
//				getWnd('swDrugOstatBySkladViewWindow').show();
//			}
//		},
//		OstDrugViewAction: {
//			text: MM_DLO_MEDNAME,
//			tooltip: 'Работа с остатками медикаментов по наименованию',
//			iconCls : 'drug-name16',
//			handler: function()
//			{
//				getWnd('swDrugOstatViewWindow').show();
//			}
//		},
//		ReportStatViewAction: {
//			text: 'Статистическая отчетность',
//			tooltip: 'Статистическая отчетность',
//			iconCls : 'reports16',
//			hidden : false,
//			handler: function()
//			{
//				// Пример предварительной загрузки блока кода 
//				if (sw.codeInfo.loadEngineReports)
//				{
//					getWnd('swReportEndUserWindow').show();
//				}
//				else 
//				{
//					getWnd('reports').load(
//					{
//						callback: function(success) 
//						{
//							sw.codeInfo.loadEngineReports = success;
//							// здесь можно проверять только успешную загрузку 
//							getWnd('swReportEndUserWindow').show();
//						}
//					});
//				}
//			}
//		},
//		EventsWindowTestAction: {
//			text: 'Тест (только на тестовом)',
//			tooltip: 'Тест',
//			iconCls : 'test16',
//			hidden:((IS_DEBUG!=1) || (getGlobalOptions().region.nick == 'saratov')),
//			handler: function()
//			{
//				getWnd('swTestEventsWindow').show();
//			}
//		},
//		TemplatesWindowTestAction: {
//			text: 'Тест шаблонов',
//			tooltip: 'Тест шаблонов',
//			iconCls : 'test16',
//			//hidden:(IS_DEBUG!=1),
//			hidden: true,
//			handler: function()
//			{
//				getWnd('swTestTemplatesWindow').show();
//			}
//		},
//		TemplatesEditWindowAction: {
//			text: 'Редактор шаблонов',
//			tooltip: 'Редактор шаблонов',
//			iconCls : 'test16',
//			//hidden:(IS_DEBUG!=1),
//			hidden: ((getGlobalOptions().region.nick=='saratov') || (IS_DEBUG!=1)),
//			handler: function()
//			{
//				getWnd('swTemplatesEditWindow').show();
//			}
//		},
//		TemplateRefValuesOpenAction: {
//			text: 'База референтных значений',
//			tooltip: 'Редактор референтных значений',
//			iconCls : 'test16',
//			hidden: !isAdmin,
//			handler: function()
//			{
//				getWnd('swTemplateRefValuesViewWindow').show();
//			}
//		},
//		GlossarySearchAction: {
//			text: 'Глоссарий',
//			tooltip: 'Глоссарий',
//			iconCls : 'glossary16',
//			//hidden: false,
//			hidden: (getGlobalOptions().region.nick=='saratov'),
//			handler: function()
//			{
//				getWnd('swGlossarySearchWindow').show();
//			}
//		},
//		ReportDBStructureAction: {
//			text: 'Структура БД',
//			tooltip: 'Структура БД',
//			iconCls : 'test16',
//			hidden:(!isAdmin),
//			handler: function()
//			{
//				getWnd('swReportDBStructureOptionsWindow').show();
//			}
//		},
//		UserProfileAction: {
//			text: 'Мой профиль',
//			tooltip: 'Профиль пользователя',
//			iconCls : 'user16',
//			hidden: false,
//			handler: function()
//			{
//				args = {}
//				args.action = 'edit';
//				getWnd('swUserProfileEditWindow').show(args);
//			}
//		},
//		PromedHelp: {
//			text: 'Вызов справки',
//			tooltip: 'Помощь по программе',
//			iconCls : 'help16',
//			handler: function()
//			{
//				ShowHelp('Содержание');
//			}
//		},
//		PromedForum: {
//			text: 'Форум поддержки',
//			iconCls: 'support16',
//			xtype: 'tbbutton',
//			handler: function() {
//				window.open(ForumLink);
//			}
//		},		
//		swShowTestWindowAction: {
//			text: 'Тестовое окно',
//			tooltip: 'Открыть Тестовое окно',
//			iconCls : 'test16',
//			handler: function() {
//				//getWnd('swTestWindow').show();
//                getWnd('swWorkPlaceWindow').show();
//			},
//			hidden: !isAdmin || !isDebug()
//		},
//		PromedAbout: {
//			text: 'О программе',
//			tooltip: 'Информация о программе',
//			iconCls : 'promed16',
//			testId: 'mainmenu_help_about',
//			handler: function()
//			{
//				getWnd('swAboutWindow').show();
//			}
//		},
//		PromedExit: {
//			text:'Выход',
//			iconCls: 'exit16',
//			handler: function()
//			{
//					sw.swMsg.show({
//							title: 'Подтвердите выход',
//							msg: 'Вы действительно хотите выйти?',
//							buttons: Ext.Msg.YESNO,
//							fn: function ( buttonId ) {
//									if ( buttonId == 'yes' ) {
//				window.onbeforeunload = null;
//				window.location=C_LOGOUT;
//				}
//					}
//			});
//			}
//		},
//        ConvertAction:{
//            text: 'Конвертация полей',
//            tooltip: 'Конвертация',
//            iconCls : 'eph16',
//            handler: function()
//            {
//                getWnd('swConvertEditWindow').show();
//            },
//            hidden:((IS_DEBUG!=1) || (getGlobalOptions().region.nick == 'saratov'))
//        },
//		swLdapAttributeChangeAction:{
//            text: 'Замена атрибута в LDAP',
//            tooltip: 'Замена атрибута в LDAP',
//            iconCls : 'eph16',
//            handler: function()
//            {
//                getWnd('swLdapAttributeChangeWindow').show();
//            },
//            hidden: !isSuperAdmin()
//        },
//		swDicomViewerAction:{
//            text: 'Просмотрщик Dicom',
//            tooltip: 'Просмотрщик Dicom',
//            iconCls : 'eph16',
//            handler: function()
//            {
//                getWnd('swDicomViewerWindow').show();
//            },
//            hidden: ((!isAdmin) || (getGlobalOptions().region.nick == 'saratov'))
//        },
//		TestAction: {
//			text: 'Тест (только на тестовом)',
//			tooltip: 'Тест',
//			iconCls : 'eph16',
//			handler: function()
//			{
//				// Инициализация всех окон промед
//				/*
//				for(var key in sw.Promed)
//				{
//					//log(key);
//					if ((key.indexOf('Form') == -1) && (key.indexOf('Window') == -1))
//					{
//						// Не форма и не окно 100%
//					}
//					else 
//					{
//						try 
//						{
//							var win = swGetWindow(key);
//							if (win!=null)
//							{
//								log(key, ';', win.title);
//							}
//						}
//						catch (e)
//						{
//							//log('Это не форма: ', e);
//						}
//					}
//					//log(key);
//				};
//				*/
//				//getWnd('swPersonEPHForm').show({Person_id: 499527, Server_id: 10, PersonEvn_id: 104170589});
//				getWnd('swEvnUslugaOrderEditWindow').show({LpuSection_id:10});
//			},
//			hidden: ((IS_DEBUG!=1) || (getGlobalOptions().region.nick == 'saratov'))
//		},
//		Test2Action: {
//			text: 'Получить с анализатора (только на тестовом)',
//			tooltip: 'Тест',
//			iconCls : 'eph16',
//			handler: function()
//			{
//				//getWnd('swPersonEPHForm').show({Person_id: 499527, Server_id: 10, PersonEvn_id: 104170589});
//				getWnd('swTestLoadEditWindow').show();
//			},
//			hidden: ((IS_DEBUG!=1) || (getGlobalOptions().region.nick == 'saratov'))
//		},
//		MedPersonalPlaceAction: {
//			text: 'Медицинский персонал: места работы (старый ЕРМП)',
//			tooltip: 'Медицинский персонал: места работы (старый ЕРМП)',
//			iconCls : 'staff16',
//			hidden: ((!isAdmin && (getGlobalOptions().region.nick != 'ufa')) || (getGlobalOptions().region.nick == 'pskov')),
//			handler: function()
//			{
//				getWnd('swMedPersonalViewWindow').show();
//			}
//		},
//		MedWorkersAction: {
//			text: 'Медработники',
//			tooltip: 'Медработники',
//			iconCls : 'staff16',
//			hidden : ((getGlobalOptions().region.nick == 'ufa') || (!isAdmin && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ))),
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'MedWorker', main_center_panel);
//			}
//		},
//		MedPersonalSearchAction: {
//			text: 'Медицинский персонал: Просмотр (старый ЕРМП)',
//			tooltip: 'Медицинский персонал: Просмотр (старый ЕРМП)',
//			iconCls : 'staff16',
//			hidden : !MP_NOT_ERMP,
//			handler: function()
//			{
//				getWnd('swMedPersonalSearchWindow').show();
//			}
//		},
//		swLgotTreeViewAction: {
//			text: 'Регистр льготников: Список',
//			tooltip: 'Просмотр льгот по категориям',
//			iconCls : 'lgot-tree16',
//			handler: function()
//			{
//				getWnd('swLgotTreeViewWindow').show();
//			}
//		},
//		swAttachmentDemandAction: {
//			text: 'Заявления на прикрепление: ЛПУ',
//			tooltip: 'Просмотр и редактирование заявлений на прикрепление к ЛПУ',
//			iconCls : 'attach-demand16',
//			hidden : !isAdmin,
//			handler: function()
//			{
//				getWnd('swAttachmentDemandListWindow').show();
//			}
//		},
//		swChangeSmoDemandAction: {
//			text: 'Заявления на прикрепление: СМО',
//			tooltip: 'Просмотр и редактирование заявлений на прикрепление к СМО',
//			iconCls : 'attach-demand16',
//			hidden : !isAdmin,
//			handler: function()
//			{
//				getWnd('swChangeSmoDemandListWindow').show();
//			}
//		},
//		swUsersTreeViewAction: {
//			text: 'Пользователи',
//			tooltip: 'Просмотр и редактирование пользователей',
//			iconCls : 'users16',
//			hidden: !getGlobalOptions().superadmin && !isLpuAdmin(),
//			handler: function()
//			{
//				getWnd('swUsersTreeViewWindow').show();
//			}
//		},
//		swGroupsViewAction: {
//			text: 'Группы',
//			tooltip: 'Просмотр и редактирование групп',
//			iconCls : 'users16',
//			hidden: !isSuperAdmin(),
//			handler: function()
//			{
//				getWnd('swGroupViewWindow').show();
//			}
//		},
//		swOptionsViewAction: {
//			text: 'Настройки',
//			tooltip: 'Просмотр и редактирование настроек',
//			iconCls : 'settings16',
//			handler: function()
//			{
//				getWnd('swOptionsWindow').show();
//			}
//		},
//		swPersonSearchAction: {
//			text: 'Человек: поиск',
//			tooltip: 'Поиск людей',
//			iconCls: 'patient-search16',
//			handler: function()
//			{
//				getWnd('swPersonSearchWindow').show({
//					onSelect: function(person_data) {
//						getWnd('swPersonEditWindow').show({
//							onHide: function () {
//								if ( person_data.onHide && typeof person_data.onHide == 'function' ) {
//									person_data.onHide();
//								}
//							},
//							Person_id: person_data.Person_id,
//							Server_id: person_data.Server_id
//						});
//					},
//					searchMode: 'all'
//				});
//			}
//		},
//		swImportAction: {
//			text: 'Загрузка ФРЛ',
//			tooltip: 'Загрузка ФРЛ',
//			iconCls: 'patient-search16',
//			hidden: !getGlobalOptions().superadmin,
//			handler: function()
//			{
//				getWnd('swImportWindow').show();
//			}
//		},
//		swTemperedDrugs: {
//			text: 'Импорт отпущенных ЛС',
//			tooltip: 'Отпущенные ЛС',
//			iconCls: 'adddrugs-icon16',
//			handler: function()
//			{
//                getWnd('swTemperedDrugsWindow').show();
//			},
//            hidden: (getGlobalOptions().region.nick != 'ufa')
//		},
//		swPersonPeriodicViewAction: {
//			text: 'Тест периодик',
//			tooltip: 'Тест периодик',
//			iconCls: 'patient-search16',
//			handler: function()
//			{
//				getWnd('swPeriodicViewWindow').show({
//					Person_id: 	99560000173,
//					Server_id: 	10010833
//				});
//			}
//		},
//		/*swAssistantWorkPlaceAction: {
//			text: 'Рабочее место лаборанта',
//			tooltip: 'Рабочее место лаборанта',
//			iconCls: 'lab-assist16',
//			//iconCls: 'patient-search16',
//			hidden: !isAdmin,
//			handler: function()
//			{
//				getWnd('swAssistantWorkPlaceWindow').show();
//			}
//		},*/
//		swSelectWorkPlaceAction: {
//			text: 'Выбор АРМ по умолчанию',
//			tooltip: 'Выбор АРМ по умолчанию',
//			iconCls: 'lab-assist16',
//			//iconCls: 'patient-search16',
//			hidden: !isAdmin,
//			handler: function()
//			{
//				getWnd('swSelectWorkPlaceWindow').show();
//			}
//		},
//		
//		swRegistrationJournalSearchAction: {
//			text: 'Лабораторные исследования: поиск',
//			tooltip: 'Журнал лабораторных исследований',
//			//iconCls: 'patient-search16',
//			hidden: ((!isAdmin) || (getGlobalOptions().region.nick == 'saratov')),
//			handler: function()
//			{
//				getWnd('swRegistrationJournalSearchWindow').show();
//			}
//		},
//		swLpuSelectAction: {
//			text: 'Выбор ЛПУ',
//			tooltip: 'Выбор ЛПУ',
//			iconCls: 'lpu-select16',
//			handler: function()
//			{
//				Ext.WindowMgr.each(function(wnd){
//					if ( wnd.isVisible() )
//					{
//						wnd.hide();
//					}
//				});
//				getWnd('swSelectLpuWindow').show({});
//			},
//			hidden: !getGlobalOptions().superadmin && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ) // проверяем так же просмотр медперсонала
//		},
//
//		swDivCountAction: {
//			text: 'Количество html-элементов',
//			tooltip: 'Посчитать текущее количество html-элементов',
//			iconCls: 'tags16',
//			handler: function()
//			{
//				var arrdiv = Ext.DomQuery.select("div");
//				var arrtd = Ext.DomQuery.select("td");
//				var arra = Ext.DomQuery.select("a");
//				Ext.Msg.alert("Количество html-элементов", "Количество html-элементов:<br><b>div</b>:&nbsp;" + arrdiv.length+"<br><b>td</b>:&nbsp;&nbsp;" + arrtd.length+"<br><b>a</b>:&nbsp;&nbsp;&nbsp;" + arra.length);
//			},
//			hidden:(IS_DEBUG!=1)
//		},
//		swGlobalOptionAction: {
//			text: 'Общие настройки',
//			tooltip: 'Просмотр и изменение общих настроек',
//			iconCls: 'settings-global16',
//			handler: function()
//			{
//				getWnd('swGlobalOptionsWindow').show();
//			},
//			hidden: !getGlobalOptions().superadmin //((IS_DEBUG!=1) || !getGlobalOptions().superadmin)
//		},
//		// Все прочие акшены
//		swPregCardViewAction: {
//			text: 'Индивидуальная карта беременной: Просмотр',
//			tooltip: 'Индивидуальная карта беременной: Просмотр',
//			iconCls: 'pol-preg16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin && !isTestLpu
//		},
//		swPregCardFindAction: {
//			text: 'Индивидуальная карта беременной: Поиск',
//			tooltip: 'Индивидуальная карта беременной: Поиск',
//			iconCls: 'pol-pregsearch16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin && !isTestLpu
//		},
//		swRegChildOrphanDopDispStreamAction: {
//			text: 'Регистр детей-сирот: Поточный ввод',
//			tooltip: 'Регистр детей-сирот: Поточный ввод',
//			iconCls: 'orphdisp-stream16',
//			handler: function()
//			{
//				getWnd('swPersonDispOrpSearchWindow').show({mode: 'stream'});
//			},
//			hidden: false//!isAdmin
//		},
//		swRegChildOrphanDopDispFindAction: {
//			text: 'Регистр детей-сирот: Поиск',
//			tooltip: 'Регистр детей-сирот: Поиск',
//			iconCls: 'orphdisp-search16',
//			handler: function()
//			{
//				getWnd('swPersonDispOrpSearchWindow').show();
//			},
//			hidden: false//!isAdmin
//		},
//		swEvnPLChildOrphanDopDispStreamAction: {
//			text: 'Талон по диспансеризации детей-сирот: Поточный ввод',
//			tooltip: 'Талон по диспансеризации детей-сирот: Поточный ввод',
//			iconCls: 'orphdisp-epl-stream16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispOrpSearchWindow').show({mode: 'stream'});
//			},
//			hidden: false
//		},
//		swEvnPLChildOrphanDopDispFindAction: {
//			text: 'Талон по диспансеризации детей-сирот: Поиск',
//			tooltip: 'Талон по диспансеризации детей-сирот: Поиск',
//			iconCls: 'orphdisp-epl-search16',
//			handler: function()
//			{
//				getWnd('swEvnPLDispOrpSearchWindow').show();
//			},
//			hidden: false
//		},
//		swEvnDtpWoundViewAction: {
//			text: 'Извещения ДТП о раненом: Просмотр',
//			tooltip: 'Извещения ДТП о раненом: Просмотр',
//			iconCls: 'stac-accident-injured16',
//			handler: function()
//			{
//				getWnd('swEvnDtpWoundWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		swEvnDtpDeathViewAction: {
//			text: 'Извещения ДТП о скончавшемся: Просмотр',
//			tooltip: 'Извещения ДТП о скончавшемся: Просмотр',
//			iconCls: 'stac-accident-dead16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swMedPersonalWorkPlaceAction: {
//			text: '<b>Рабочее место</b>',
//			title: 'АРМ',
//			tooltip: 'Рабочее место врача',
//			iconCls: 'workplace-mp16',
//			handler: function()
//			{
//				sw.Promed.MedStaffFactByUser.selectARM({
//					ARMType: 'common',
//					onSelect: null
//				});
//			},
//			hidden: getGlobalOptions().medstafffact == undefined
//		},
//		/*swStacNurseWorkPlaceAction: {
//			text: 'Рабочее место постовой медсестры',
//			tooltip: 'Рабочее место постовой медсестры',
//			iconCls: 'workplace-mp16',
//			handler: function() {
//				sw.Promed.MedStaffFactByUser.selectARM({
//					ARMType: 'stacnurse',
//					onSelect: function(data) { getWnd('swEvnPrescrJournalWindow').show({userMedStaffFact: data}); }
//				});
//			},
//			hidden: getGlobalOptions().medstafffact == undefined
//		},*/
//		swEvnPrescrViewJournalAction: {
//			text: 'Журнал назначений',
//			tooltip: 'Журнал назначений',
//			iconCls: 'workplace-mp16',
//			handler: function() {
//				sw.Promed.MedStaffFactByUser.selectARM({
//					ARMType: 'prescr',
//					onSelect: function(data) {getWnd('swEvnPrescrJournalWindow').show({userMedStaffFact: data});}
//				});
//			},
//			hidden: getGlobalOptions().medstafffact == undefined
//		},
//		swEvnPrescrCompletedViewJournalAction: {
//			text: 'Журнал медицинских мероприятий',
//			tooltip: 'Журнал медицинских мероприятий',
//			iconCls: 'workplace-mp16',
//			handler: function() {
//				sw.Promed.MedStaffFactByUser.selectARM({
//					ARMType: 'prescr',
//					onSelect: function(data) {getWnd('swEvnPrescrCompletedJournalWindow').show({userMedStaffFact: data});}
//				});
//			},
//			hidden: getGlobalOptions().medstafffact == undefined
//		},
//		/*
//		swVKWorkPlaceAction: {
//			text: 'Рабочее место ВК',
//			tooltip: 'Рабочее место ВК',
//			iconCls: 'workplace-mp16',
//			handler: function()
//			{
//				var onSelect = function(data) {
//					getWnd('swVKWorkPlaceWindow').show(data);
//				}
//				openSelectServiceWindow({ ARMType: 'vk', onSelect: onSelect });
//			},
//			hidden: !IS_DEBUG // getGlobalOptions().medstafffact == undefined
//		},
//		swMseWorkPlaceAction: {
//			text: 'Рабочее место МСЭ',
//			tooltip: 'Рабочее место МСЭ',
//			iconCls: 'workplace-mp16',
//			handler: function()
//			{
//				var onSelect = function(data) {
//					getWnd('swMseWorkPlaceWindow').show(data);
//				}
//				openSelectServiceWindow({ ARMType: 'mse', onSelect: onSelect });
//			},
//			hidden: !IS_DEBUG // getGlobalOptions().medstafffact == undefined
//		},*/
//		swJournalDirectionsAction: {
//			text: 'Журнал регистрации направлений',
//			tooltip: 'Журнал регистрации направлений',
//			iconCls: 'pol-directions16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		
//		swPersonCardAttachListAction: {
//			text: 'РПН: Заявления о выборе МО',
//			tooltip: 'РПН: Заявления о выборе МО',
//			iconCls: '', // нужна иконка
//			handler: function() {
//				getWnd('swPersonCardAttachListWindow').show();
//			}
//		},
//		
//		swMSJobsAction: {
//			text: 'Управление задачами MSSQL',
//			tooltip: 'Управление задачами MSSQL',
//			iconCls: 'sql16',
//			handler: function()
//			{
//				getWnd('swMSJobsWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		loadLastObjectCode: {
//			text: 'Обновить последний JS-файл',
//			tooltip: 'Обновить последний JS-файл',
//			iconCls: 'test16',
//			handler: function() {
//				if (sw.codeInfo) {
//					loadJsCode({objectName: sw.codeInfo.lastObjectName, objectClass: sw.codeInfo.lastObjectClass});
//				}
//			},
//			hidden: true //!isAdmin && !IS_DEBUG
//		},
//		MessageAction: {
//			text: 'Сообщения',
//			iconCls: 'messages16',
//			hidden: false,
//			handler: function()
//			{
//				if(getWnd('swMessagesViewWindow').isVisible() == false)
//				{
//					getWnd('swMessagesViewWindow').show();
//				}
//			}
//		},
//		swTreatmentStreamInputAction: {
//			text: 'Регистрация обращений: Поточный ввод',
//			tooltip: 'Регистрация обращений: Поточный ввод',
//			iconCls: 'petition-stream16',
//			handler: function() {
//				getWnd('swTreatmentStreamInputWindow').show();
//			},
//			hidden: !isAccessTreatment()
//		},
//		swTreatmentSearchAction: {
//			text: 'Регистрация обращений: Поиск',
//			tooltip: 'Регистрация обращений: Поиск',
//			iconCls: 'petition-search16',
//			handler: function() {
//				getWnd('swTreatmentSearchWindow').show();
//			},
//			hidden: !isAccessTreatment()
//		},
//		swTreatmentReportAction: {
//			text: 'Регистрация обращений: Отчетность',
//			tooltip: 'Регистрация обращений: Отчетность',
//			iconCls: 'petition-report16',
//			handler: function() {
//				getWnd('swTreatmentReportWindow').show();
//			},
//			hidden: !isAccessTreatment()
//		},
//		swEvnPSStreamAction: {
//			text: 'Карта выбывшего из стационара: Поточный ввод',
//			tooltip: 'Карта выбывшего из стационара: Поточный ввод',
//			iconCls: 'stac-psstream16',
//			handler: function()
//			{
//				getWnd('swEvnPSStreamInputWindow').show();
//			},
//			hidden: false //!isAdmin && !isTestLpu && IS_DEBUG != 1
//		},
//		swEvnPSFindAction: {
//			text: 'Карта выбывшего из стационара: Поиск',
//			tooltip: 'Карта выбывшего из стационара: Поиск',
//			iconCls: 'stac-pssearch16',
//			handler: function()
//			{
//				getWnd('swEvnPSSearchWindow').show();
//			},
//			hidden: false //!isAdmin && !isTestLpu && IS_DEBUG != 1
//		},
//		swSuicideAttemptsEditAction: {
//			text: 'Суицидальные попытки: Ввод',
//			tooltip: 'Суицидальные попытки: Ввод или просмотр',
//			iconCls: 'suicide-edit16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin && !isTestLpu 
//		},
//		swSuicideAttemptsFindAction: {
//			text: 'Суицидальные попытки: Поиск',
//			tooltip: 'Суицидальные попытки: Поиск',
//			iconCls: 'suicide-search16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin && !isTestLpu 
//		},
//		
//		swExportToDBFBedFondAction: {
//			text: 'Выгрузка данных по коечному фонду',
//			tooltip: 'Выгрузка данных по коечному фонду',
//			iconCls: 'database-export16',
//			handler: function(){
//				getWnd('swExportToDBFBedFondWindow').show();
//			}
//		},
//		swExportToMiacAction: {
//			text: 'Экспорт статистики в МИАЦ',
//			tooltip: 'Экспорт статистики в МИАЦ',
//			iconCls: 'database-export16',
//			handler: function(){
//                var w = getWnd('swExportToMiacWindow');
//                if(!w.isVisible()) w.show();
//			},
//            hidden: (getGlobalOptions().region.nick != 'samara')
//		},
//
//		/*swMedPersonalWorkPlaceStacAction: {
//			text: 'Рабочее место врача',
//			tooltip: 'Рабочее место врача',
//			iconCls: 'workplace-mp16',
//			handler: function()
//			{
//				var onSelect = function(data) {
//					if (data.LpuSectionProfile_SysNick == 'priem') 
//					{
//						getWnd('swMPWorkPlacePriemWindow').show({userMedStaffFact: data});
//					}
//					else
//					{
//						getWnd('swMPWorkPlaceStacWindow').show({userMedStaffFact: data});
//					}
//				};
//				sw.Promed.MedStaffFactByUser.selectARM({
//					ARMType: 'stac',
//					onSelect: onSelect
//				});
//			},
//			hidden: false
//		},*/
//		swJourHospDirectionAction: {
//			text: 'Журнал направлений',
//			tooltip: 'Журнал направлений на госпитализацию',
//			iconCls: 'pol-directions16',
//			handler: function()
//			{
//				var onSelect = function(data) {getWnd('swJourHospDirectionWindow').show({userMedStaffFact: data});};
//				sw.Promed.MedStaffFactByUser.selectARM({
//					ARMType: 'stac',
//					onSelect: onSelect
//				});
//			},
//			hidden: false
//		},
//		swEvnUslugaParStreamAction: {
//			text: 'Выполнение параклинической услуги: Поточный ввод',
//			tooltip: 'Выполнение параклинической услуги: Поточный ввод',
//			iconCls: 'par-serv-stream16',
//			handler: function()
//			{
//				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//				getWnd('swEvnUslugaParStreamInputWindow').show();
//			},
//			hidden: false
//		},
//		swEvnUslugaParFindAction: {
//			text: 'Выполнение параклинической услуги: Поиск',
//			tooltip: 'Выполнение параклинической услуги: Поиск',
//			iconCls: 'par-serv-search16',
//			handler: function()
//			{
//				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//				getWnd('swEvnUslugaParSearchWindow').show();
//			},
//			hidden: false
//		},
//		/*swMedPersonalWorkPlaceParAction: {
//			text: 'Рабочее место врача',
//			tooltip: 'Рабочее место врача',
//			iconCls: 'workplace-mp16',
//			handler: function()
//			{
//				var onSelect = function(data) {getWnd('swMPWorkPlaceParWindow').show({userMedStaffFact: data});};
//				sw.Promed.MedStaffFactByUser.selectARM({
//					ARMType: 'par',
//					onSelect: onSelect
//				});
//			},
//			hidden: getGlobalOptions().medstafffact == undefined
//		},*/
//		swEvnPLStomStreamAction: {
//			text: 'Талон амбулаторного пациента: Поточный ввод',
//			tooltip: 'Талон амбулаторного пациента: Поточный ввод',
//			iconCls: 'stom-stream16',
//			handler: function()
//			{
//				getWnd('swEvnPLStomStreamInputWindow').show();
//			}
//		},
//		swEvnPLStomSearchAction: {
//			text: 'Талон амбулаторного пациента: Поиск',
//			tooltip: 'Талон амбулаторного пациента: Поиск',
//			iconCls : 'stom-search16',
//			handler: function()
//			{
//				getWnd('swEvnPLStomSearchWindow').show();
//			},
//			hidden: false
//		},
//		swUslugaPriceListAction: {
//			text: 'Стоматологические услуги ЛПУ (Справочник УЕТ)',
//			tooltip: 'Стоматологические услуги ЛПУ (Справочник УЕТ)',
//			iconCls: 'stom-uslugi16',
//			handler: function() {
//				getWnd('swUslugaPriceListViewWindow').show();
//			},
//			hidden: false
//		},
//		swMedSvidBirthAction: {
//			text: 'Свидетельства о рождении',
//			tooltip: 'Свидетельства о рождении',
//			iconCls: 'svid-birth16',
//			handler: function()
//			{
//				getWnd('swMedSvidBirthStreamWindow').show();
//			},
//			hidden: false
//		},
//		swMedSvidDeathAction: {
//			text: 'Свидетельства о смерти',
//			tooltip: 'Свидетельства о смерти',
//			iconCls: 'svid-death16',
//			handler: function()
//			{
//				getWnd('swMedSvidDeathStreamWindow').show();
//			},
//			hidden: false
//		},
//		swMedSvidPDeathAction: {
//			text: 'Свидетельства о перинатальной  смерти',
//			tooltip: 'Свидетельства о перинатальной  смерти',
//			iconCls: 'svid-pdeath16',
//			handler: function()
//			{
//				getWnd('swMedSvidPntDeathStreamWindow').show();
//			},
//			hidden: false
//		},
//		swMedSvidPrintAction: {
//			text: 'Печать бланков свидетельств',
//			tooltip: 'Печать бланков свидетельств',
//			iconCls: 'svid-blank16',
//			handler: function()
//			{
//				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//				getWnd('swMedSvidSelectSvidType').show();
//			},
//			hidden: false
//		},
//		swTestAction: {
//			text: 'Тест',
//			tooltip: 'Тест',
//			iconCls: '',
//			handler: function()
//			{
//				// проверка методов 
//				Ext.Ajax.request({
//					failure: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//
//						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
//							sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
//						}
//						else {
//							sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
//						}
//					},
//					params: {
//						Polis_Ser: 'КС',
//						Polis_Num: '431885'
//					},
//					success: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//						log('?c=AmbulanceService&m=getPersonByPolis');
//						log(response_obj);
//					},
//					url: '?c=AmbulanceService&m=getPersonByPolis'
//				});
//				
//				Ext.Ajax.request({
//					failure: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//
//						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
//							sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
//						}
//						else {
//							sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
//						}
//					},
//					params: {
//						Person_SurName: 'ПЕТУХОВ',
//						Person_FirName: 'ИВАН',
//						Person_SecName: 'СЕРГЕЕВИЧ',
//						Person_BirthDay: '1983-12-26'
//					},
//					success: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//						log('?c=AmbulanceService&m=getPersonByFIODR');
//						log(response_obj);
//					},
//					url: '?c=AmbulanceService&m=getPersonByFIODR'
//				});
//				Ext.Ajax.request({
//					failure: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//
//						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
//							sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
//						}
//						else {
//							sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
//						}
//					},
//					params: {
//						Person_SurName: 'КАТАЕВ',
//						Person_FirName: 'АНДРЕЙ',
//						Person_Age: 46,
//						KLStreet_Name: 'ШКОЛЬНАЯ',
//						Address_House: '6',
//						Address_Flat: null
//					},
//					success: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//						log('?c=AmbulanceService&m=getPersonByAddress');
//						log(response_obj);
//					},
//					url: '?c=AmbulanceService&m=getPersonByAddress'
//				});
//				
//				Ext.Ajax.request({
//					failure: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//
//						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
//							sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
//						}
//						else {
//							sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
//						}
//					},
//					params: {},
//					success: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//						log('?c=AmbulanceService&m=getProfileList');
//						log(response_obj);
//					},
//					url: '?c=AmbulanceService&m=getProfileList'
//				});
//				
//				
//				Ext.Ajax.request({
//					failure: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//
//						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
//							sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
//						}
//						else {
//							sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
//						}
//					},
//					params: {
//						LpuSectionProfile_Code: 1000
//					},
//					success: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//						log('?c=AmbulanceService&m=getStacList');
//						log(response_obj);
//					},
//					url: '?c=AmbulanceService&m=getStacList'
//				});
//				
//				Ext.Ajax.request({
//					failure: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//
//						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
//							sw.swMsg.alert('Ошибка', response_obj.Error_Msg);
//						}
//						else {
//							sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу');
//						}
//					},
//					params: {
//						LpuSection_id: 99560000944,
//						Lpu_id: 28,
//						Person_id: 220,
//						emergencyBedCount: 1, 
//						EmergencyData_BrigadeNum: 1, 
//						EmergencyData_CallNum: 111 
//					},
//					success: function(response, options) {
//						var response_obj = Ext.util.JSON.decode(response.responseText);
//						log('?c=AmbulanceService&m=bookEmergencyBed');
//						log(response_obj);
//					},
//					url: '?c=AmbulanceService&m=bookEmergencyBed'
//				});
//				
//				/*getWnd('swAddPropertyWindow').show({
//					onSelect: function(params) {						
//						var ds_model = Ext.data.Record.create([
//							'id',
//							'type',
//							'name',
//							'value'
//						]);
//						
//						var gr = Ext.getCmp('EUDDEW_PropertyGrid');
//						gr.getStore().insert(
//							0,
//							new ds_model({
//								id: params.id,
//								type: params.type,
//								name: params.pname,
//								value: params.value									
//							})
//						);
//						gr.startEditing(0,0);
//						getWnd('swAddPropertyWindow').hide();
//						swalert(params);
//					}
//				});*/
//				/*
//				getWnd('swPersonEditWindow').show({
//					action: 'edit',
//					Person_id: "1170750319",
//					Server_id: "10010833"
//				});
//				*/
//			},
//			hidden: ((!isAdmin) || (getGlobalOptions().region.nick == 'saratov'))
//		},
//		swRegDeceasedPeopleAction: {
//			text: 'Сведения об умерших гражданах',
//			tooltip: 'Сведения об умерших гражданах (регистр)',
//			iconCls: 'regdead16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swMedicationSprAction: {
//			text: 'Справочник: Медикаменты',
//			tooltip: 'Справочник: Медикаменты',
//			iconCls: 'farm-drugs16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: true
//		},
//		swContractorsSprAction: {
//			text: 'Справочник: Контрагенты',
//			tooltip: 'Справочник: Контрагенты',
//			iconCls: 'farm-partners16',
//			handler: function()
//			{
//				getWnd('swContragentViewWindow').show();
//			},
//			hidden: false
//		},
//		swDokNakAction: {
//			text: 'Приходные накладные',
//			tooltip: 'Приходные накладные',
//			iconCls: 'doc-nak16',
//			handler: function()
//			{
//				getWnd('swDokNakViewWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		swDokUchAction: {
//			text: 'Документы учета медикаментов',
//			tooltip: 'Документы учета медикаментов',
//			iconCls: 'doc-uch16',
//			handler: function()
//			{
//				getWnd('swDokUcLpuViewWindow').show();
//			},
//			hidden: false
//		},
//		swAktSpisAction: {
//			text: 'Акты списания медикаментов',
//			tooltip: 'Акты списания медикаментов',
//			iconCls: 'doc-spis16',
//			handler: function()
//			{
//				getWnd('swDokSpisViewWindow').show();
//				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: false
//		},
//		swDokOstAction: {
//			text: 'Документы ввода остатков',
//			tooltip: 'Документы ввода остатков',
//			iconCls: 'doc-ost16',
//			handler: function()
//			{
//				getWnd('swDokOstViewWindow').show();
//			},
//			hidden: false
//		},
//		swInvVedAction: {
//			text: 'Инвентаризационные ведомости',
//			tooltip: 'Инвентаризационные ведомости',
//			iconCls: 'farm-inv16',
//			handler: function()
//			{
//				getWnd('swDokInvViewWindow').show();
//				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swMedOstatAction: {
//			text: 'Остатки медикаментов',
//			tooltip: 'Остатки медикаментов',
//			iconCls: 'farm-ostat16',
//			handler: function()
//			{
//				getWnd('swMedOstatViewWindow').show();
//			},
//			hidden: false
//		},
//		EvnReceptProcessAction: {
//			text: 'Обработка рецептов',
//			tooltip: 'Обработка рецептов',
//			iconCls : 'receipt-process16',
//			handler: function() {
//				getWnd('swEvnReceptProcessWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		EvnRPStreamInputAction: {
//			text: 'Потоковое отоваривание рецептов',
//			tooltip: 'Потоковое отоваривание рецептов',
//			iconCls : 'receipt-streamps16',
//			handler: function() {
//				getWnd('swEvnRPStreamInputWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		EvnReceptTrafficBookViewAction: {
//			text: 'Журнал движения рецептов',
//			tooltip: 'Журнал движения рецептов',
//			iconCls : 'receipt-delay16',
//			handler: function() {
//				getWnd('swEvnReceptTrafficBookViewWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		KerRocordBookAction: {
//			text: 'Врачебная комиссия',
//			tooltip: 'Врачебная комиссия',
//			iconCls: 'med-commission16',
//			handler: function()
//			{
//				getWnd('swClinExWorkSearchWindow').show();
//			}, 
//			hidden: !isAdmin
//		},
//		swRegistrationCallAction: {
//			text: 'Регистрация вызова',
//			tooltip: 'Регистрация вызова',
//			iconCls: '',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: true
//		},
//		swCardCallViewAction: {
//			text: 'Карта вызова: Просмотр',
//			tooltip: 'Карта вызова: Просмотр',
//			iconCls: 'ambulance_add16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: true
//		},
//		swCardCallFindAction: {
//			text: 'Карты вызова СМП',
//			tooltip: 'Карты вызова СМП: Поиск',
//			iconCls: 'ambulance_search16',
//			handler: function()
//			{
//				getWnd('swCmpCallCardSearchWindow').show();
//			},
//			hidden: (getGlobalOptions().region.nick == 'samara')
//		},
////                 *
////                 *Закоментировал Тагир
////                 *
////		swInjectionStreamAction: {
////			text: 'Прививки: Поточный ввод',
////			tooltip: 'Прививки: Поточный ввод',
////			iconCls: 'inj-stream16',
////			handler: function()
////			{
////				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
////			},
////			hidden: !isAdmin
////		},
////		swInjectionFindAction: {
////			text: 'Прививки: Поиск',
////			tooltip: 'Прививки: Поиск',
////			iconCls: 'inj-search16',
////			handler: function()
////			{
////				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
////			},
////			hidden: !isAdmin
////		},
////		swMedicalTapStreamAction: {
////			text: 'Медотводы: Поточный ввод',
////			tooltip: 'Медотводы: Поточный ввод',
////			iconCls: 'mreject-stream16',
////			handler: function()
////			{
////				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
////			},
////			hidden: !isAdmin
////		},
////		swMedicalTapFindAction: {
////			text: 'Медотводы: Поиск',
////			tooltip: 'Медотводы: Поиск',
////			iconCls: 'mreject-search16',
////			handler: function()
////			{
////				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
////			},
////			hidden: !isAdmin
////		},
////		swSerologyStreamAction: {
////			text: 'Серология: Поточный ввод',
////			tooltip: 'Серология: Поточный ввод',
////			iconCls: 'imm-ser-stream16',
////			handler: function()
////			{
////				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
////			},
////			hidden: !isAdmin
////		},
////		swSerologyFindAction: {
////			text: 'Серология: Поиск',
////			tooltip: 'Серология: Поиск',
////			iconCls: 'imm-ser-search16',
////			handler: function()
////			{
////				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
////			},
////			hidden: !isAdmin
////		},
////		swAbsenceBakAction: {
////			text: 'Отсутствие бакпрепаратов',
////			tooltip: 'Отсутствие бакпрепаратов',
////			iconCls: 'imm-bakabs16',
////			handler: function()
////			{
////				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
////			},
////			hidden: !isAdmin
////		},
////		swCurrentPlanAction: {
////			text: 'Текущее планирование вакцинации',
////			tooltip: 'Текущее планирование вакцинации',
////			iconCls: 'vac-plan16',
////			handler: function()
////			{
////				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
////			},
////			hidden: !isAdmin
////		},
////                 *
////                 *Закоментировал Тагир
////                 *
//			// tagir start
//			amm_JournalsVac: {
//			text: 'Просмотр журналов вакцинации',
//			tooltip: 'Просмотр журналов вакцинации',
//			iconCls: 'vac-plan16',
//			handler: function()
//			{
//                            if (vacLpuContr())  // Если это 2-я детская
//                                    getWnd('amm_mainForm').show();
//                                else
//                                    sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//
////              getWnd('amm_mainForm').show();
//              //var loadMask = new Ext.LoadMask(Ext.getCmp('journalsVaccine'), { msg: LOAD_WAIT });
//              //loadMask.show();
//			},
//			hidden: false // !isAdmin
//		},
//		
//		ammStartVacFormPlan: {
//			text: 'Планирование вакцинации',
//			tooltip: 'Планирование вакцинации',
//			iconCls: 'vac-plan16',
//			hidden: !isAdmin&& !isLpuAdmin(),
//			handler: function()
//			{
//				if (vacLpuContr())  // Если это 2-я детская
//					getWnd('amm_StartVacPlanForm').show();
//				else
//					sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			}
//			//, hidden: false // !isAdmin
//		},
//
//		ammvacListTasks: {
//			text: 'Список заданий',
//			tooltip: 'Список заданий',
//			iconCls: 'vac-plan16',
//			hidden: !isAdmin&& !isLpuAdmin(),
//
//			handler: function()
//			{
//				if (vacLpuContr())  // Если это 2-я детская
//					getWnd('amm_ListTaskForm').show();
//				else
//					sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			}
//			//, hidden: false // !isAdmin
//		},
//		ammvacReport_5: {
//			text: 'Отчет ф. №5',
//			tooltip: 'Отчет ф. №5',
//			iconCls: 'vac-plan16',
//			handler: function()
//			{
//				if (vacLpuContr())  // Если это 2-я детская
//					getWnd('amm_vacReport_5').show();
//				else
//					sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: false // !isAdmin
//		},
//		ammSprVaccine: {
//			text: 'Справочник вакцин',
//			tooltip: 'Справочник вакцин',
//			iconCls: 'vac-plan16',
//			handler: function()
//			{
//				if (vacLpuContr())  // Если это 2-я детская
//					getWnd('amm_SprVaccineForm').show();
//				else
//					sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: false // !isAdmin
//		},
//		ammSprNacCal: {
//			text: 'Национальный календарь прививок',
//			tooltip: 'Национальный календарь прививок',
//			iconCls: 'vac-plan16',
//			handler: function()
//			{
//				if (vacLpuContr())  // Если это 2-я детская
//					getWnd('amm_SprNacCalForm').show();
//				else
//					sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: false // !isAdmin
//		},
//		ammVacPresence: {
//			text: 'Наличие вакцин',
//			tooltip: 'Наличие вакцин',
//			iconCls: 'vac-plan16',
//			handler: function()
//			{
//				if (vacLpuContr())  // Если это 2-я детская
//					getWnd('amm_PresenceVacForm').show();
//				else
//					sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: false // !isAdmin
//		},
//		// End  tagir
//		swLpuPassportAction: {
//			text: 'Паспорт ЛПУ',
//			tooltip: 'Паспорт ЛПУ',
//			iconCls: 'lpu-passport16',
//			handler: function()
//			{
//				getWnd('swLpuPassportEditWindow').show({
//						action: 'edit',
//						Lpu_id: getGlobalOptions().lpu_id
//				});
//			},
//			hidden: !isSuperAdmin() && !isLpuAdmin()
//		},
//		swOrgPassportAction: {
//			text: 'Паспорт организации',
//			tooltip: 'Паспорт организации',
//			iconCls: 'lpu-passport16',
//			handler: function()
//			{
//				getWnd('swOrgEditWindow').show({
//						action: 'edit',
//						mode: 'passport',
//						Org_id: getGlobalOptions().org_id
//				});
//			},
//			hidden: (!isAdmin && !isOrgAdmin()) || !isDebug()
//		},
//		swLpuUslugaAction: {
//			text: 'Услуги ЛПУ',
//			tooltip: 'Услуги ЛПУ',
//			iconCls: 'lpu-services-lpu16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swUslugaGostAction: {
//			text: 'Услуги ГОСТ',
//			tooltip: 'Услуги ГОСТ',
//			iconCls: 'lpu-services-gost16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swMKB10Action: {
//			text: 'МКБ-10',
//			tooltip: 'Справочник МКБ-10',
//			iconCls: 'spr-mkb16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swMESAction: {
//			text: 'Новые ' + getMESAlias(),
//			tooltip: 'Справочник новых ' + getMESAlias(),
//			iconCls: 'spr-mes16',
//			handler: function()
//			{
//				getWnd('swMesSearchWindow').show();
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swMESOldAction: {
//			text: getMESAlias(),
//			tooltip: 'Справочник ' + getMESAlias(),
//			iconCls: 'spr-mes16',
//			handler: function()
//			{
//				getWnd('swMesOldSearchWindow').show();
//			},
//			hidden: false // TODO: После тестирования доступ должен быть для всех
//		},
//		swOrgAllAction: {
//			text: 'Все организации',
//			tooltip: 'Все организации',
//			iconCls: 'spr-org16',
//			handler: function()
//			{
//				getWnd('swOrgViewForm').show();
//				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: false
//		},
//		swContragentsAction: {
//			text: 'Контрагенты',
//			tooltip: 'Справочник контрагентов для персонифицированного учета',
//			iconCls: 'farm-partners16',
//			handler: function()
//			{
//				getWnd('swContragentViewWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		swDocumentUcAction: {
//			text: 'Учет медикаментов',
//			tooltip: 'Документы учета медикаментов',
//			iconCls: 'drug-traffic16',
//			handler: function()
//			{
//				getWnd('swDocumentUcViewWindow').show();
//			},
//			hidden: !isAdmin
//		},
//		swOrgLpuAction: {
//			text: 'Лечебно-профилактические учреждения',
//			tooltip: 'Лечебно-профилактические учреждения',
//			iconCls: 'spr-org-lpu16',
//			handler: function()
//			{
//				getWnd('swOrgViewForm').show({mode: 'lpu'});
//			},
//			hidden: false
//		},
//		swOrgGosAction: {
//			text: 'Государственные учреждения',
//			tooltip: 'Государственные учреждения',
//			iconCls: 'spr-org-gos16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swOrgStrahAction: {
//			text: 'Страховые медицинские организации',
//			tooltip: 'Страховые медицинские организации',
//			iconCls: 'spr-org-strah16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swOrgBankAction: {
//			text: 'Банки',
//			tooltip: 'Банки',
//			iconCls: 'spr-org-bank16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swRlsFirmsAction: {
//			text: 'Производители лекарственных средств',
//			tooltip: 'Производители лекарственных средств',
//			iconCls: 'spr-org-manuf16',
//			handler: function(){
//				if(!getWnd('swRlsFirmsSearchWindow').isVisible()) getWnd('swRlsFirmsSearchWindow').show();
//			}
//		},
//		swOMSSprTerrAction: {
//			text: 'Территории субъекта РФ',
//			tooltip: 'Территории субъекта РФ',
//			iconCls: 'spr-terr-oms16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swClassAddrAction: {
//			text: 'Классификатор адресов',
//			tooltip: 'Классификатор адресов',
//			iconCls: 'spr-terr-addr16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		swSprPromedAction: {
//			text: 'Справочники Промед',
//			tooltip: 'Справочники Промед',
//			iconCls: 'spr-promed16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		SprLpuAction: {
//			text: 'Справочники ЛПУ',
//			tooltip: 'Справочники ЛПУ',
//			iconCls: 'spr-lpu16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		SprOmsAction: {
//			text: 'Справочники ОМС',
//			tooltip: 'Справочники ОМС',
//			iconCls: 'spr-oms16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		SprDloAction: {
//			text: 'Справочники ЛЛО',
//			tooltip: 'Справочники ЛЛО',
//			iconCls: 'spr-dlo16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		SprPropertiesProfileAction: {
//			text: 'Характеристики профилей отделений',
//			tooltip: 'Характеристики профилей отделений',
//			iconCls: 'otd-profile16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		SprUchetFactAction: {
//			text: 'Учет фактической выработки смен',
//			tooltip: 'Учет фактической выработки смен',
//			iconCls: 'uchet-fact16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara'))
//		},
//		SprRlsAction: {
//			text: 'Регистр лекарственных средств в России',
//			tooltip: 'Регистр лекарственных средств в России',
//			iconCls: 'rls16',
//			handler: function()
//			{
//				getWnd('swRlsViewForm').show();
//			},
//			hidden: false
//		},
//		SprPostAction: {
//			text: 'Должности',
//			tooltip: 'Должности',
//			iconCls: '',
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Post', main_center_panel);
//			}/*,
//			hidden: !isAdmin*/
//		},
//		SprSkipPaymentReasonAction: {
//			text: 'Причины невыплат',
//			tooltip: 'Причины невыплат',
//			iconCls: '',
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'SkipPaymentReason', main_center_panel);
//			}/*,
//			hidden: !isAdmin*/
//		},
//		SprWorkModeAction: {
//			text: 'Режимы работы',
//			tooltip: 'Режимы работы',
//			iconCls: '',
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkMode', main_center_panel);
//			}/*,
//			hidden: !isAdmin*/
//		},
//		SprSpecialityAction: {
//			text: 'Специальности',
//			tooltip: 'Специальности',
//			iconCls: '',
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Speciality', main_center_panel);
//			}/*,
//			hidden: !isAdmin*/
//		},
//		SprDiplomaSpecialityAction: {
//			text: 'Дипломные специальности',
//			tooltip: 'Дипломные специальности',
//			iconCls: '',
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'DiplomaSpeciality', main_center_panel);
//			}/*,
//			hidden: !isAdmin*/
//		},
//		SprLeaveRecordTypeAction: {
//			text: 'Тип записи окончания работы',
//			tooltip: 'Тип записи окончания работы',
//			iconCls: '',
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'LeaveRecordType', main_center_panel);
//			}/*,
//			hidden: !isAdmin*/
//		},
//		SprEducationTypeAction: {
//			text: 'Тип образования',
//			tooltip: 'Тип образования',
//			iconCls: '',
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationType', main_center_panel);
//			}/*,
//			hidden: !isAdmin*/
//		},
//		SprEducationInstitutionAction: {
//			text: 'Учебное учреждение',
//			tooltip: 'Учебное учреждение',
//			iconCls: '',
//			handler: function()
//			{
//				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationInstitution', main_center_panel);
//			}/*,
//			hidden: !isAdmin*/
//		},
//		swF14OMSPerAction: {
//			text: 'Форма Ф14 ОМС: Показатели',
//			tooltip: 'Показатели для формы Ф14 ОМС',
//			iconCls: 'rep-f14oms-per16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swF14OMSAction: {
//			text: 'Форма Ф14 ОМС',
//			tooltip: 'Форма Ф14 ОМС',
//			iconCls: 'rep-f14oms16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swF14OMSFinAction: {
//			text: 'Форма Ф14 ОМС: Приложение 1',
//			tooltip: 'Форма Ф14 ОМС: Приложение 1',
//			iconCls: 'rep-f14oms-fin16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},/*
//		swAdminWorkPlaceAction: {
//			text: 'Рабочее место администратора',
//			tooltip: 'Рабочее место администратора',
//			iconCls: 'admin16',
//			handler: function()
//			{
//				getWnd('swAdminWorkPlaceWindow').show({});
//			},
//			hidden: false
//		},
//		swLpuAdminWorkPlaceAction: {
//			text: 'Рабочее место администратора ЛПУ',
//			tooltip: 'Рабочее место администратора ЛПУ',
//			iconCls: 'admin16',
//			handler: function()
//			{
//				getWnd('swLpuAdminWorkPlaceWindow').show({});
//			},
//			hidden: false
//		},*/
//		swRegWorkPlaceAction: {
//			text: 'Рабочее место регистратора',
//			tooltip: 'Рабочее место регистратора',
//			iconCls: 'admin16',
//			handler: function()
//			{
//				getWnd('swRegWorkPlaceWindow').show({});
//			},
//			hidden: !isAdmin
//		},
//		swReportEngineAction: {
//			text: 'Репозиторий отчетов',
//			tooltip: 'Репозиторий отчетов',
//			iconCls: 'rpt-repo16',
//			handler: function()
//			{
//				// Пример предварительной загрузки блока кода 
//				if (sw.codeInfo.loadEngineReports)
//				{
//					getWnd('swReportEngineWindow').show();
//				}
//				else 
//				{
//					getWnd('reports').load(
//					{
//						callback: function(success) 
//						{
//							sw.codeInfo.loadEngineReports = success;
//							// здесь можно проверять только успешную загрузку 
//							getWnd('swReportEngineWindow').show();
//						}
//					});
//				}
//			},
//			hidden: !isAdmin
//		},
//		swAnalyzerWindowAction: {
//			text: 'Настройки ЛИС',
//			tooltip: 'Настройки пользователя ЛИС',
//			handler: function()
//			{
//				getWnd('swAnalyzerWindow').show({pmUser_id: getGlobalOptions().pmuser_id, pmUser_Login: UserLogin});
//			},
//			hidden: ((!isAdmin) || (getGlobalOptions().region.nick == 'saratov'))
//		},
//		swRrlExportWindowAction: {
//			text: 'Выгрузка РРЛ',
//			tooltip: 'Выгрузка регистра региональных льготников',
//			handler: function()
//			{
//				getWnd('swRrlExportWindow').show();
//			},
//			hidden: (getGlobalOptions().region.nick != 'ufa')
//		}
//	}
	
	// Проставляем ID-шники списку акшенов [и на всякий случай создаем их] (создавать кстати не обязательно)
//	for(var key in sw.Promed.Actions) {
//		sw.Promed.Actions[key].id = key;
//		sw.Promed.Actions[key] = new Ext.Action(sw.Promed.Actions[key]);
//	}
//	
//	// Формирование обычного меню 
//	this.menu_passport_lpu = new Ext.menu.Menu(
//	{
//		id: 'menu_passport_lpu',
//		items:
//		[
//			sw.Promed.Actions.OrgStructureViewAction,
//			sw.Promed.Actions.LpuStructureViewAction,
//			sw.Promed.Actions.swOrgPassportAction,
//			sw.Promed.Actions.swLpuPassportAction,
//			{
//				text: 'Медицинский персонал',
//				hidden: (!isAdmin && !isLpuAdmin()) || (getGlobalOptions().region.nick == 'samara') || (getGlobalOptions().region.nick == 'pskov'),
//				iconCls : 'staff16',
//				menu: new Ext.menu.Menu(
//				{
//					id: 'menu_spr_mp',
//					items:
//					[
//						sw.Promed.Actions.MedPersonalPlaceAction,
//						sw.Promed.Actions.MedPersonalSearchAction
//					]
//				})
//			},
//			//'-', //TODO: Показывать взависимости от условий
//			sw.Promed.Actions.swLpuUslugaAction,
//			sw.Promed.Actions.swUslugaGostAction,
//			sw.Promed.Actions.swMKB10Action,
//			sw.Promed.Actions.swMESAction,
//			sw.Promed.Actions.swMESOldAction,
//			sw.Promed.Actions.UslugaComplexTreeAction,
//			'-',
//			{
//				text:'Организации',
//				iconCls:'spr-org16',
//				hidden: !isAdmin && !isLpuAdmin() && (getGlobalOptions().region.nick != 'ufa'),
//				menu: new Ext.menu.Menu(
//				{
//					id: 'menu_spr_org',
//					items:
//					[
//						sw.Promed.Actions.swOrgAllAction,
//						'-',
//						sw.Promed.Actions.swOrgLpuAction,
//						sw.Promed.Actions.swOrgGosAction,
//						sw.Promed.Actions.swOrgStrahAction,
//						sw.Promed.Actions.swOrgBankAction,
//						sw.Promed.Actions.swRlsFirmsAction
//					]
//				})
//			},
//			{
//				text:'Классификатор территорий',
//				iconCls:'spr-terr16',
//				hidden: (!isAdmin || (getGlobalOptions().region.nick == 'samara')),
//				menu: new Ext.menu.Menu(
//				{
//					id: 'menu_spr_org',
//					items:
//					[
//						sw.Promed.Actions.swOMSSprTerrAction,
//						sw.Promed.Actions.swClassAddrAction
//					]
//				})
//			}
//		]
//	});
//	
//	if (isAdmin)
//	{
//		this.menu_passport_lpu.add("-");
//		this.menu_passport_lpu.add(sw.Promed.Actions.swSprPromedAction);
//		this.menu_passport_lpu.add(sw.Promed.Actions.SprLpuAction);
//		this.menu_passport_lpu.add(sw.Promed.Actions.SprOmsAction);
//		this.menu_passport_lpu.add(sw.Promed.Actions.SprDloAction);
//		this.menu_passport_lpu.add(sw.Promed.Actions.SprPropertiesProfileAction);
//		this.menu_passport_lpu.add(sw.Promed.Actions.SprUchetFactAction);
//	}
//	this.menu_passport_lpu.add(sw.Promed.Actions.GlossarySearchAction);
//	this.menu_passport_lpu.add("-");
//	this.menu_passport_lpu.add(sw.Promed.Actions.SprRlsAction);
//	
//	/*
//	this.menu_reg_main = new Ext.menu.Menu(
//	{
//		//plain: true,
//		id: 'menu_reg_main',
//		items:
//		[
//			sw.Promed.Actions.PersonCardSearchAction,
//			sw.Promed.Actions.PersonCardViewAllAction,
//			sw.Promed.Actions.PersonCardStateViewAction
//		]
//	});
//	*/
//	this.menu_dlo_main = new Ext.menu.Menu(
//	{
//		//plain: true,
//		id: 'menu_dlo_main2',
//		items:
//		[
//			sw.Promed.Actions.swLgotTreeViewAction,
//			sw.Promed.Actions.LgotFindAction,
//	//		sw.Promed.Actions.LgotAddAction,
//			'-',
//			sw.Promed.Actions.EvnUdostViewAction,
//	//		sw.Promed.Actions.EvnUdostAddAction,
//			'-',
//			sw.Promed.Actions.EvnReceptFindAction,
//			sw.Promed.Actions.EvnReceptAddStreamAction,
//	//		sw.Promed.Actions.EvnReceptAddAction,
//			'-',
//			sw.Promed.Actions.OstAptekaViewAction,
//			sw.Promed.Actions.OstDrugViewAction,
//			sw.Promed.Actions.OstSkladViewAction,
//			'-',
//			sw.Promed.Actions.DrugRequestViewAction,
//			sw.Promed.Actions.NewDrugRequestViewAction,
//			//sw.Promed.Actions.DrugRequestEditAction,
//			sw.Promed.Actions.EvnReceptInCorrectFindAction,
//            sw.Promed.Actions.swTemperedDrugs,
//			'-',
//			sw.Promed.Actions.DrugMnnLatinNameEditAction,
//			sw.Promed.Actions.DrugTorgLatinNameEditAction
//		]
//	});
//	if (isAdmin)
//	{
//		this.menu_dlo_main.add("-");
//		this.menu_dlo_main.add(sw.Promed.Actions.OrgFarmacyViewAction);
//	}
//	
//	
//	this.menu_polka_main = new Ext.menu.Menu(
//	{
//		id: 'menu_polka_main',
//		items: []
//	});
//
//	this.menu_polka_main.add(sw.Promed.Actions.EvnPLStreamInputAction);
//	this.menu_polka_main.add(sw.Promed.Actions.EvnPLEditAction);
//	this.menu_polka_main.add("-");
//
//	this.menu_polka_main.add(sw.Promed.Actions.PersonCardSearchAction);
//	this.menu_polka_main.add(sw.Promed.Actions.PersonCardViewAllAction);
//	this.menu_polka_main.add(sw.Promed.Actions.PersonCardStateViewAction);
//	this.menu_polka_main.add(sw.Promed.Actions.swPersonCardAttachListAction);
//	this.menu_polka_main.add(sw.Promed.Actions.AutoAttachViewAction);
//
//	//this.menu_polka_main.add("-");
//	//this.menu_polka_main.add(sw.Promed.Actions.swMedPersonalWorkPlaceAction);
//	//this.menu_polka_main.add("-");
//	//this.menu_polka_main.add(sw.Promed.Actions.swVKWorkPlaceAction);
//	//this.menu_polka_main.add("-");
//	//this.menu_polka_main.add(sw.Promed.Actions.swMseWorkPlaceAction);
//	
//	if (isAdmin) {
//		this.menu_polka_main.add("-");
//		this.menu_polka_main.add(sw.Promed.Actions.swJournalDirectionsAction);
//		this.menu_polka_main.add("-");
//	}
//		// Углубленное диспансерное обследование ВОВ
//		this.menu_polka_main.add({
//			text:'Углубленное диспансерное обследование ВОВ',
//			iconCls: 'pol-dopdisp16', // to-do: Поменять иконки
//			hidden: false, // !isAdmin
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_polka_wow',
//				items:
//				[
//					sw.Promed.Actions.PersonPrivilegeWOWSearchAction,
//					sw.Promed.Actions.PersonDispWOWStreamAction,
//					sw.Promed.Actions.PersonDispWOWSearchAction
//				]
//			})
//		});
//		this.menu_polka_main.add({
//			text:'Дополнительная диспансеризация',
//			iconCls: 'pol-dopdisp16',
//			hidden: false,//!isAdmin,
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_polka_dd',
//				items:
//				[
//					sw.Promed.Actions.PersonDopDispSearchAction,
//					sw.Promed.Actions.PersonDopDispStreamInputAction,
//					sw.Promed.Actions.EvnPLDopDispSearchAction,
//					sw.Promed.Actions.EvnPLDopDispStreamInputAction
//				]
//			})
//		});
//		this.menu_polka_main.add({
//			text:'Диспансеризация взрослого населения',
//			iconCls: 'pol-dopdisp16',
//			hidden: false,//!isAdmin,
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_polka_dd13',
//				items:
//				[
//					sw.Promed.Actions.EvnPLDispDop13SearchAction,
//					// sw.Promed.Actions.EvnPLDispDop13StreamInputAction,
//					sw.Promed.Actions.EvnPLDispDop13SecondSearchAction
//					// sw.Promed.Actions.EvnPLDispDop13SecondStreamInputAction
//				]
//			})
//		});
//		this.menu_polka_main.add({
//			text:'Диспансеризация (подростки 14ти лет)',
//			iconCls: 'dopdisp-teens16',
//			hidden: false,//!isAdmin,
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_polka_dt14',
//				items:
//				[
//					sw.Promed.Actions.EvnPLDispTeen14SearchAction,
//					sw.Promed.Actions.EvnPLDispTeen14StreamInputAction
//				]
//			})
//		});
//		this.menu_polka_main.add({
//			text:'Диспансеризация отдельных групп взрослого населения',
//			iconCls: 'dopdisp-teens16',
//			hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa'),
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_polka_dt14',
//				items:
//				[
//					sw.Promed.Actions.EvnPLDispSomeAdultSearchAction,
//					sw.Promed.Actions.EvnPLDispSomeAdultStreamInputAction
//				]
//			})
//		});
//		this.menu_polka_main.add({
//			text:'Диспансеризация (дети-сироты)',
//			iconCls: 'pol-orphandisp16',
//			hidden: false,
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_polka_ddchild',
//				items:
//				[
//					sw.Promed.Actions.swRegChildOrphanDopDispFindAction,
//					sw.Promed.Actions.swRegChildOrphanDopDispStreamAction,
//					sw.Promed.Actions.swEvnPLChildOrphanDopDispFindAction,
//					sw.Promed.Actions.swEvnPLChildOrphanDopDispStreamAction
//
//				]
//			})
//		});
//		this.menu_polka_main.add("-");
//		this.menu_polka_main.add({
//			text:'Диспансерный учет',
//			iconCls: 'pol-disp16',
//			hidden: false,//!(isAdmin || isTestLpu),
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_polka_disp',
//				items:
//				[
//					sw.Promed.Actions.PersonDispSearchAction,
//					sw.Promed.Actions.PersonDispViewAction
//				]
//			})
//		});
////		if (isAdmin) {
//			this.menu_polka_main.add({
//				text:'Индивидуальная карта беременной',
//				iconCls: 'pol-preg16',
//				hidden: !isAdmin,
//				menu: new Ext.menu.Menu(
//				{
//					id: 'menu_polka_preg',
//					items:
//					[
//						sw.Promed.Actions.swPregCardViewAction,
//						sw.Promed.Actions.swPregCardFindAction
//					]
//				})
//			});
//			this.menu_polka_main.add("-");
//            this.menu_polka_main.add({
//                text:'Иммунопрофилактика',
//                iconCls: 'pol-immuno16',
//                // hidden: ((getGlobalOptions().region.nick != 'ufa') && (!isAdmin)),
//                menu: new Ext.menu.Menu(
//                {
//                    id: 'menu_polka_immuno',
//                    items:
//                    [
//                    /*
//                     * Закоментировал Тагир
//                     *
//                        sw.Promed.Actions.swInjectionStreamAction,
//                        sw.Promed.Actions.swInjectionFindAction,
//                        '-',
//                        sw.Promed.Actions.swMedicalTapStreamAction,
//                        sw.Promed.Actions.swMedicalTapFindAction,
//                        '-',
//                        sw.Promed.Actions.swSerologyStreamAction,
//                        sw.Promed.Actions.swSerologyFindAction,
//                        '-',
//                        sw.Promed.Actions.swAbsenceBakAction,
//                        sw.Promed.Actions.swCurrentPlanAction,
//                        '-',
//                     */
//                        sw.Promed.Actions.ammStartVacFormPlan,
//                        sw.Promed.Actions.ammvacListTasks,
//                        '-',
//                        sw.Promed.Actions.amm_JournalsVac,
//                        '-',
//                        sw.Promed.Actions.ammvacReport_5,
//                        '-',
//                        sw.Promed.Actions.ammSprVaccine,
//                        sw.Promed.Actions.ammSprNacCal,
//                        '-',
//                        sw.Promed.Actions.ammVacPresence
//                    ]
//                })
//            });
//    //		}
//		
//		this.menu_polka_main.add(sw.Promed.Actions.FundHoldingViewAction);
//
//		
//	this.menu_dlo_service = new Ext.menu.Menu(
//	{
//		//plain: true,
//		id: 'menu_dlo_service',
//		items: [
//			//sw.Promed.Actions.swLpuAdminWorkPlaceAction,
//			//sw.Promed.Actions.swAdminWorkPlaceAction,
//			//sw.Promed.Actions.swRegWorkPlaceAction,
//			/*{
//				text: 'Рабочее место врача функциональной диагностики ',
//				tooltip: 'Рабочее место врача функциональной диагностики ',
//				iconCls: 'test16',
//				handler: function()
//				{
//					getWnd('swWorkPlaceFuncDiagWindow').show({ARMType: "common"});
//				},
//				hidden: !isAdmin || !isDebug()
//			},*/
//			sw.Promed.Actions.swUsersTreeViewAction,
//			sw.Promed.Actions.swGroupsViewAction,
//			sw.Promed.Actions.PersonDoublesSearchAction,
//			sw.Promed.Actions.PersonDoublesModerationAction,
//			sw.Promed.Actions.PersonUnionHistoryAction,
//			sw.Promed.Actions.swOptionsViewAction,
//			/*
//			sw.Promed.Actions.UslugaComplexViewAction,
//			sw.Promed.Actions.UslugaComplexTreeAction,
//			*/
//			sw.Promed.Actions.UserProfileAction,
//			sw.Promed.Actions.MessageAction,
//			
//			sw.Promed.Actions.TestAction,
//            sw.Promed.Actions.ConvertAction,
//			sw.Promed.Actions.swLdapAttributeChangeAction,
//			sw.Promed.Actions.swDicomViewerAction,
//			sw.Promed.Actions.Test2Action,
//			sw.Promed.Actions.swTemperedDrugs
//		]
//	});
//	this.menu_dlo_service.add({
//		text:'МИАЦ',
//		iconCls: 'miac16',
//		hidden: (getGlobalOptions().region.nick != 'ufa'),
//		menu: new Ext.menu.Menu(
//		{
//			id: 'menu_miac',
//			items: [
//				sw.Promed.Actions.MiacExportAction,
//				sw.Promed.Actions.MiacExportSheduleOptionsAction
//			]
//		})
//	});
//	if (isAdmin)
//	{
//		this.menu_dlo_service.add(sw.Promed.Actions.swDivCountAction);
//		this.menu_dlo_service.add(sw.Promed.Actions.loadLastObjectCode);
//	}
//	this.menu_dlo_service.add("-");
//	if (isAdmin)
//	{
//		this.menu_dlo_service.add(sw.Promed.Actions.swMSJobsAction);
//		this.menu_dlo_service.add("-");
//	}
//	if (isAdmin)
//	{
//		this.menu_dlo_service.add(sw.Promed.Actions.TemplateRefValuesOpenAction);
//		this.menu_dlo_service.add(sw.Promed.Actions.swGlobalOptionAction);
//	}
//	
//	if (isAdmin || ( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 )) // проверяем так же просмотр медперсонала )
//	{
//		this.menu_dlo_service.add(sw.Promed.Actions.swLpuSelectAction);
//		this.menu_dlo_service.add("-");
//	}
//	
//	this.menu_dlo_service.add(sw.Promed.Actions.swPersonSearchAction);
//	this.menu_dlo_service.add(sw.Promed.Actions.swImportAction);
//	this.menu_dlo_service.add("-");
//	this.menu_dlo_service.add(sw.Promed.Actions.swRegistrationJournalSearchAction);
//	this.menu_dlo_service.add(sw.Promed.Actions.swAnalyzerWindowAction);
//	this.menu_dlo_service.add(sw.Promed.Actions.swRrlExportWindowAction);
//	
//	if (IS_DEBUG) {
//		this.menu_dlo_service.add(sw.Promed.Actions.swPersonPeriodicViewAction);
//		this.menu_dlo_service.add(sw.Promed.Actions.TemplatesWindowTestAction);
//		this.menu_dlo_service.add(sw.Promed.Actions.TemplatesEditWindowAction);
//		this.menu_dlo_service.add(sw.Promed.Actions.ReportDBStructureAction);
//		//this.menu_dlo_service.add(sw.Promed.Actions.swAssistantWorkPlaceAction);
//		this.menu_dlo_service.add(sw.Promed.Actions.swSelectWorkPlaceAction);
//		
//	} 
//	if (isAdmin) {
//		this.menu_dlo_service.add(sw.Promed.Actions.swTestAction);
//	}
//	
//	this.menu_stac_main = new Ext.menu.Menu(
//	{
//		id: 'menu_stac_main',
//		items: [
//			sw.Promed.Actions.swEvnPSStreamAction,
//			sw.Promed.Actions.swEvnPSFindAction
//		]
//	});
//	
//	
//	//this.menu_stac_main.add(sw.Promed.Actions.swMedPersonalWorkPlaceStacAction);
//	if (isAdmin || isTestLpu || IS_DEBUG == 1)
//	{
//		this.menu_stac_main.add("-");
//		//this.menu_stac_main.add(sw.Promed.Actions.swStacNurseWorkPlaceAction);
//		this.menu_stac_main.add(sw.Promed.Actions.swEvnPrescrViewJournalAction);
//		this.menu_stac_main.add(sw.Promed.Actions.swEvnPrescrCompletedViewJournalAction);
//		this.menu_stac_main.add(sw.Promed.Actions.swJourHospDirectionAction);
//	}
//	/*
//	if (isAdmin || isTestLpu)
//	{
//		this.menu_stac_main.add("-");
//		this.menu_stac_main.add(sw.Promed.Actions.swSuicideAttemptsEditAction);
//		this.menu_stac_main.add(sw.Promed.Actions.swSuicideAttemptsFindAction);
//	}*/
//	this.menu_stac_main.add("-");
//	this.menu_stac_main.add(sw.Promed.Actions.swExportToDBFBedFondAction);
//	this.menu_stac_main.add(sw.Promed.Actions.swExportToMiacAction);
//
//	/*
//	this.menu_stac_main.add("-");
//	this.menu_stac_main.add({
//		text: 'Патоморфология',
//		iconCls: 'pathomorph16',
//		hidden: false, // !isAdmin
//		menu: new Ext.menu.Menu({
//			id: 'menu_stac_sudmedexp',
//			items: [
//				sw.Promed.Actions.EvnDirectionHistologicViewAction,
//				sw.Promed.Actions.EvnHistologicProtoViewAction,
//				sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
//				sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
//			]
//		})
//	});
//	*/
//
//
//	
//	this.menu_parka_main = new Ext.menu.Menu(
//	{
//		id: 'menu_parka_main',
//		items: [
//			sw.Promed.Actions.swEvnUslugaParStreamAction,
//			sw.Promed.Actions.swEvnUslugaParFindAction
//			//'-',
//			//sw.Promed.Actions.swMedPersonalWorkPlaceParAction
//		]
//	});
//	this.menu_stomat_main = new Ext.menu.Menu(
//	{
//		id: 'menu_stomat_main',
//		items: [
//			sw.Promed.Actions.swEvnPLStomStreamAction,
//			sw.Promed.Actions.swEvnPLStomSearchAction,
//			'-'//,
//			//sw.Promed.Actions.swUslugaPriceListAction
//		]
//	});
//	
//	this.menu_farmacy_main = new Ext.menu.Menu(
//	{
//		id: 'menu_farmacy_main',
//		items: [
//			//sw.Promed.Actions.swMedicationSprAction,
//			sw.Promed.Actions.swContractorsSprAction,
//			'-',
//			sw.Promed.Actions.swDokNakAction,
//			sw.Promed.Actions.swDokUchAction,
//			sw.Promed.Actions.swAktSpisAction,
//			sw.Promed.Actions.swDokOstAction,
//			sw.Promed.Actions.swInvVedAction,
//			sw.Promed.Actions.swMedOstatAction
//		]
//	});
//	if (isAdmin || isTestLpu)
//	{
//		this.menu_farmacy_main.add("-");
//		this.menu_farmacy_main.add(sw.Promed.Actions.EvnReceptProcessAction);
//		this.menu_farmacy_main.add(sw.Promed.Actions.EvnRPStreamInputAction);
//		this.menu_farmacy_main.add(sw.Promed.Actions.swInvVedAction);
//		this.menu_farmacy_main.add(sw.Promed.Actions.swMedOstatAction);
//		this.menu_farmacy_main.add(sw.Promed.Actions.EvnReceptTrafficBookViewAction);
//	}
//	
//	/*
//	this.menu_immunoprof_main = new Ext.menu.Menu(
//	{
//		id: 'menu_immunoprof_main',
//		items: [
//			sw.Promed.Actions.swInjectionStreamAction,
//			sw.Promed.Actions.swInjectionFindAction,
//			'-',
//			sw.Promed.Actions.swMedicalTapStreamAction,
//			sw.Promed.Actions.swMedicalTapFindAction,
//			'-',
//			sw.Promed.Actions.swSerologyStreamAction,
//			sw.Promed.Actions.swSerologyFindAction,
//			'-',
//			sw.Promed.Actions.swAbsenceBakAction,
//			sw.Promed.Actions.swCurrentPlanAction
//		]
//	});
//	*/
//		// Документы 
//		this.menu_documents = new Ext.menu.Menu({
//			id: 'menu_documents',
//			items: []
//		});
//		
//		this.menu_documents.add(sw.Promed.Actions.RegistryViewAction);
//		this.menu_documents.add("-");
//		
//		this.menu_documents.add({
//			text:'Патоморфология',
//			iconCls: 'pathomorph16',
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_pathomorph',
//				items: [
//					sw.Promed.Actions.EvnDirectionHistologicViewAction,
//					sw.Promed.Actions.EvnHistologicProtoViewAction,
//					'-',
//					sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
//					sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
//				]
//			})
//		});
//		
//		if (isAdmin || isTestLpu)
//		{
//			//this.menu_documents.add("-");
//			this.menu_documents.add(sw.Promed.Actions.swAttachmentDemandAction);
//		}
//		
//		this.menu_documents.add(
//		{
//			text: 'Обращения',
//			iconCls: 'petition-stream16',
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_treatment',
//				items:
//				[
//					sw.Promed.Actions.swTreatmentStreamInputAction,
//					sw.Promed.Actions.swTreatmentSearchAction,
//					sw.Promed.Actions.swTreatmentReportAction
//				]
//			}),
//			hidden: !isAccessTreatment()
//		});
//		
//		this.menu_documents.add({
//			text:'Свидетельства',
//			//hidden: (!getGlobalOptions().medstafffact) || (getGlobalOptions().medstafffact.length==0) && !isAdmin,
//			hidden: !(isAdmin || (getGlobalOptions().medsvidgrant_add && getGlobalOptions().medsvidgrant_add == 1)),
//			iconCls: 'medsvid16',
//			menu: new Ext.menu.Menu(
//			{
//				id: 'menu_medsvid_main',
//				items: [
//					sw.Promed.Actions.swMedSvidBirthAction,
//					sw.Promed.Actions.swMedSvidDeathAction,
//					sw.Promed.Actions.swMedSvidPDeathAction,
//					'-',
//					sw.Promed.Actions.swMedSvidPrintAction
//				]
//			})
//		});
//		
//		if (isAdmin) {
//			this.menu_documents.add({
//				text:'Извещения о ДТП',
//				iconCls: 'pol-dtp16',
//				hidden: !isAdmin,
//				menu: new Ext.menu.Menu(
//				{
//					id: 'menu_dtp',
//					items:
//					[
//						sw.Promed.Actions.swEvnDtpWoundViewAction,
//						sw.Promed.Actions.swEvnDtpDeathViewAction
//					]
//				})
//			});
//		}
//		this.menu_documents.add("-");
//		this.menu_documents.add(sw.Promed.Actions.swCardCallFindAction);
//		
//		// убрать условие после открытия KerRocordBookAction
//		if (isAdmin)
//		{
//			this.menu_documents.add("-");
//		}
//		this.menu_documents.add(sw.Promed.Actions.KerRocordBookAction);
//		
//		this.menu_documents.add(sw.Promed.Actions.EvnStickViewAction);
//		
//		this.menu_reports = new Ext.menu.Menu(
//		{
//			//plain: true,
//			id: 'menu_reports',
//			items: [
//				sw.Promed.Actions.ReportStatViewAction,
//				'-',
//				sw.Promed.Actions.swF14OMSPerAction,
//				sw.Promed.Actions.swF14OMSAction,
//				sw.Promed.Actions.swF14OMSFinAction
//			]
//		});
//		if(isAdmin){
//			this.menu_reports.add("-");
//			this.menu_reports.add(sw.Promed.Actions.swReportEngineAction);
//		}
//
//		this.menu_windows = new Ext.menu.Menu(
//		{
//			//plain: true,
//			id: 'menu_windows',
//			items: [
//				'-'
//			]
//		});
//
//		this.menu_help = new Ext.menu.Menu(
//		{
//			//plain: true,
//			id: 'menu_help',
//			items:
//			[
//				sw.Promed.Actions.PromedHelp,
//				sw.Promed.Actions.PromedForum,
//				'-',
//				sw.Promed.Actions.PromedAbout
//			]
//		});
//
//		this.menu_exit = new Ext.menu.Menu(
//		{
//			//plain: true,
//			id: 'menu_help',
//			items:
//			[
//				sw.Promed.Actions.PromedHelp, sw.Promed.Actions.PromedAbout
//			]
//		});
//		if ( isFarmacyUser() ) {
//			this.user_menu = new Ext.menu.Menu(
//			{
//				//plain: true,
//				id: 'user_menu',
//				items:
//				[
//					{
//						disabled: true,
//						iconCls: 'user16',
//						text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'Аптека : '+Ext.globalOptions.globals.OrgFarmacy_Nick,
//						xtype: 'tbtext'
//					}
//				]
//			});
//		} else {
//			this.user_menu = new Ext.menu.Menu(
//			{
//				//plain: true,
//				id: 'user_menu',
//				items:
//				[
//					{
//						disabled: true,
//						iconCls: 'user16',
//						text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'ЛПУ : '+Ext.globalOptions.globals.lpu_nick,
//						xtype: 'tbtext'
//					}
//				]
//			});
//		}		
//
//	// панель меню
////	main_menu_panel = new sw.Promed.Toolbar({
////		autoHeight: true,
////		region: 'north',
////		items:
////		[
////			sw.Promed.Actions.swMedPersonalWorkPlaceAction,
////			'-',
////			{
////				text:'Паспорт ЛПУ',
////				id: '_menu_lpu',
////				iconCls: 'lpu16',
////				menu: this.menu_passport_lpu,
////				hidden: false, //!isSuperAdmin() && !isLpuAdmin(),
////				tabIndex: -1
////			},
////			/*{
////				text:'Регистратура',
////				iconCls: 'reg16',
////				menu: this.menu_reg_main,
////				tabIndex: -1
////			},*/
////			{
////				text:'ЛЛО',
////				id: '_menu_dlo',
////				iconCls: 'dlo16',
////				hidden: (getGlobalOptions().region.nick == 'samara'),
////				menu: this.menu_dlo_main,
////				tabIndex: -1
////			},
////			{
////				text: 'Поликлиника',
////				id: '_menu_polka',
////				iconCls: 'polyclinic16',
////				hidden: (getGlobalOptions().region.nick == 'samara' || getGlobalOptions().region.nick == 'saratov'),
////				menu: this.menu_polka_main,
////				tabIndex: -1
////			},
////			{
////				text: 'Стационар',
////				id: '_menu_stac',
////				iconCls: 'stac16',
////				menu: this.menu_stac_main,
////				tabIndex: -1,
////				hidden: (getGlobalOptions().region.nick == 'saratov')
////			},
////			{
////				text: 'Параклиника',
////				id: '_menu_parka',
////				iconCls: 'parka16',
////				menu: this.menu_parka_main,
////				tabIndex: -1,
////				hidden: (getGlobalOptions().region.nick == 'samara' || getGlobalOptions().region.nick == 'saratov')
////			},
////			{
////				text: 'Стоматология',
////				id: '_stomatka',
////				iconCls: 'stomat16',
////				hidden: (getGlobalOptions().region.nick == 'samara' || getGlobalOptions().region.nick == 'saratov'),
////				menu: this.menu_stomat_main,
////				tabIndex: -1
////			},
////			{
////				text: 'Аптека',
////				id: '_menu_farmacy',
////				iconCls: 'farmacy16',
////				menu: this.menu_farmacy_main,
////				tabIndex: -1,
////				hidden: (getGlobalOptions().region.nick == 'saratov')
////			},
////			{
////				text: 'Документы',
////				id: '_menu_documents',
////				iconCls: 'documents16',
////				menu: this.menu_documents,
////				tabIndex: -1,
////				hidden: (getGlobalOptions().region.nick == 'saratov')
////			},
////			{
////				text:'Сервис',
////				id: '_menu_service',
////				iconCls: 'service16',
////				menu: this.menu_dlo_service,
////				tabIndex: -1
////			},
////			{
////				text:'Отчеты',
////				id: '_menu_reports',
////				iconCls: 'reports16',
////				menu: this.menu_reports,
////				tabIndex: -1
////			},
////			{
////				text: 'Окна',
////				id: '_menu_windows',
////				iconCls: 'windows16',
////				listeners: {
////					'click': function(e) {
////						var menu = Ext.menu.MenuMgr.get('menu_windows');
////						menu.removeAll();
////						var number = 1;
////						Ext.WindowMgr.each(function(wnd){
////							if ( wnd.isVisible() )
////							{
////                        		if ( Ext.WindowMgr.getActive().id == wnd.id )
////								{
////									menu.add(new Ext.menu.Item(
////										{
////											text: number + ". " + wnd.title,
////											iconCls : 'checked16',
////											checked: true,
////											handler: function()
////											{
////												Ext.getCmp(wnd.id).toFront();
////											}
////										})
////									);
////									number++;
////								}
////								else
////								{
////									menu.add(new Ext.menu.Item(
////										{
////											text: number + ". " + wnd.title,
////											iconCls : 'x-btn-text',
////											handler: function()
////											{
////												Ext.getCmp(wnd.id).toFront();
////											}
////										})
////									);
////									number++;
////								}
////							}
////						});
////						if ( menu.items.getCount() == 0 )
////							menu.add({
////								text: 'Открытых окон нет',
////								iconCls : 'x-btn-text',
////								handler: function()
////								{
////								}
////							});
////						else
////						{
////							menu.add(new Ext.menu.Separator());
////       						menu.add(new Ext.menu.Item(
////								{
////									text: 'Закрыть все окна',
////									iconCls : 'close16',
////									handler: function()
////									{
////										Ext.WindowMgr.each(function(wnd){
////											if ( wnd.isVisible() )
////											{
////				        						wnd.hide();
////											}
////										});
////									}
////								})
////							);
////						}
////					},
////					'mouseover': function() {
////						var menu = Ext.menu.MenuMgr.get('menu_windows');
////						menu.removeAll();
////						var number = 1;
////						Ext.WindowMgr.each(function(wnd){
////							if ( wnd.isVisible() )
////							{
////        						if ( Ext.WindowMgr.getActive().id == wnd.id )
////								{
////									menu.add(new Ext.menu.Item(
////										{
////											text: number + ". " + wnd.title,
////											iconCls : 'checked16',
////											checked: true,
////											handler: function()
////											{
////												Ext.getCmp(wnd.id).toFront();
////											}
////										})
////									);
////									number++;
////								}
////								else
////								{
////									menu.add(new Ext.menu.Item(
////										{
////											text: number + ". " + wnd.title,
////											iconCls : 'x-btn-text',
////											handler: function()
////											{
////												Ext.getCmp(wnd.id).toFront();
////											}
////										})
////									);
////									number++;
////								}
////							}
////						});
////						if ( menu.items.getCount() == 0 )
////							menu.add({
////								text: 'Открытых окон нет',
////								iconCls : 'x-btn-text',
////								handler: function()
////								{
////								}
////							});
////						else
////						{
////    						menu.add(new Ext.menu.Separator());
////							menu.add(new Ext.menu.Item(
////								{
////									text: 'Закрыть все окна',
////									iconCls : 'close16',
////									handler: function()
////									{
////										Ext.WindowMgr.each(function(wnd){
////											if ( wnd.isVisible() )
////											{
////				        						wnd.hide();
////											}
////										});
////									}
////								})
////							);
////						}
////					}
////				},
////				menu: this.menu_windows,
////				tabIndex: -1
////			},
////			{
////				text:'Помощь',
////				id: '_menu_help',
////				iconCls: 'help16',
////				menu: this.menu_help,
////				tabIndex: -1
////			},
////			{
////				id: '_menu_tbfill',
////				xtype : "tbfill"
////			},
////			{
////				iconCls: 'progress16',
////				id: '_menu_progress',
////				text: '',
////				hidden: true,
////				tabIndex: -1
////			},
////			{
////				iconCls: 'user16',
////				id: '_user_menu',
////				text: UserLogin,
////				menu: this.user_menu,
////				tabIndex: -1
////			},
////			'-',
////			sw.Promed.Actions.PromedExit
////		]
////	});
//	
//	if ( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 )
//	{
//		main_menu_panel = new sw.Promed.Toolbar({
//			autoHeight: true,
//			region: 'north',
//			items: [
//				sw.Promed.Actions.MedWorkersAction,
//				{
//					iconCls: 'progress16',
//					text: '',
//					hidden: true,
//					id: '_menu_progress',
//					tabIndex: -1
//				},
//				new Ext.Action(
//				{
//					text: 'Выбор ЛПУ',
//					tooltip: 'Выбор ЛПУ',
//					iconCls: 'lpu-select16',
//					handler: function()
//					{
//						Ext.WindowMgr.each(function(wnd){
//							if ( wnd.isVisible() )
//							{
//								wnd.hide();
//							}
//						});
//						getWnd('swSelectLpuWindow').show({});
//					},
//					hidden: !getGlobalOptions().superadmin && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ) // проверяем так же просмотр медперсонала
//				}),
//				{
//					iconCls: 'user16',
//					text: UserLogin,
//					menu: this.user_menu,
//					tabIndex: -1
//				},
//				new Ext.Action( {
//					text:'Выход',
//					iconCls: 'exit16',
//					handler: function()
//					{
//						sw.swMsg.show({
//							title: 'Подтвердите выход',
//							msg: 'Вы действительно хотите выйти?',
//							buttons: Ext.Msg.YESNO,
//							fn: function ( buttonId ) {
//								if ( buttonId == 'yes' ) {
//									window.onbeforeunload = null;
//									window.location=C_LOGOUT;
//								}
//							}
//						});
//					}
//				})
//			]
//		});
//	}
//
//    // Экшен для меню "Окна"
//    sw.Promed.Actions.WindowsAction = new Ext.Action( {
//        text: 'Окна',
//        iconCls: 'windows16',
//        listeners: {
//            'click': function(obj, e) {
//				//log(e);
//				if ( IS_DEBUG == 1 && e.altKey && e.shiftKey && e.ctrlKey )
//				{
//					new Ext.Window({
//						title: 'C первым Апреля!',
//						width: 615,
//						height: 595,
//						items: [],
//						html: '<object width="615" height="595" id="nordnet" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">'+
//								'<param value="/img/ololo/ololo.swf" name="movie">'+
//								'<embed width="615" height="595" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" bgcolor="white" quality="high" menu="false" src="/img/ololo/ololo.swf">'+
//							'</object>'
//					}).show();
//				}
//                var menu = Ext.menu.MenuMgr.get('menu_windows');
//                menu.removeAll();
//                var number = 1;
//                Ext.WindowMgr.each(function(wnd){
//                    if ( wnd.isVisible() )
//                    {
//                        if ( Ext.WindowMgr.getActive().id == wnd.id )
//                        {
//                            menu.add(new Ext.menu.Item(
//                            {
//                                text: number + ". " + wnd.title,
//                                iconCls : 'checked16',
//                                checked: true,
//                                handler: function()
//                                {
//                                    Ext.getCmp(wnd.id).toFront();
//                                }
//                            })
//                            );
//                            number++;
//                        }
//                        else
//                        {
//                            menu.add(new Ext.menu.Item(
//                            {
//                                text: number + ". " + wnd.title,
//                                iconCls : 'x-btn-text',
//                                handler: function()
//                                {
//                                    Ext.getCmp(wnd.id).toFront();
//                                }
//                            })
//                            );
//                            number++;
//                        }
//                    }
//                });
//                if ( menu.items.getCount() == 0 )
//                    menu.add({
//                        text: 'Открытых окон нет',
//                        iconCls : 'x-btn-text',
//                        handler: function()
//                        {
//                        }
//                    });
//                else
//                {
//                    menu.add(new Ext.menu.Separator());
//                    menu.add(new Ext.menu.Item(
//                    {
//                        text: 'Закрыть все окна',
//                        iconCls : 'close16',
//                        handler: function()
//                        {
//                            Ext.WindowMgr.each(function(wnd){
//                                if ( wnd.isVisible() )
//                                {
//                                    wnd.hide();
//                                }
//                            });
//                        }
//                    })
//                    );
//                }
//            },
//            'mouseover': function() {
//                var menu = Ext.menu.MenuMgr.get('menu_windows');
//                menu.removeAll();
//                var number = 1;
//                Ext.WindowMgr.each(function(wnd){
//                    if ( wnd.isVisible() )
//                    {
//                        if ( Ext.WindowMgr.getActive().id == wnd.id )
//                        {
//                            menu.add(new Ext.menu.Item(
//                            {
//                                text: number + ". " + wnd.title,
//                                iconCls : 'checked16',
//                                checked: true,
//                                handler: function()
//                                {
//                                    Ext.getCmp(wnd.id).toFront();
//                                }
//                            })
//                            );
//                            number++;
//                        }
//                        else
//                        {
//                            menu.add(new Ext.menu.Item(
//                            {
//                                text: number + ". " + wnd.title,
//                                iconCls : 'x-btn-text',
//                                handler: function()
//                                {
//                                    Ext.getCmp(wnd.id).toFront();
//                                }
//                            })
//                            );
//                            number++;
//                        }
//                    }
//                });
//                if ( menu.items.getCount() == 0 )
//                    menu.add({
//                        text: 'Открытых окон нет',
//                        iconCls : 'x-btn-text',
//                        handler: function()
//                        {
//                        }
//                    });
//                else
//                {
//                    menu.add(new Ext.menu.Separator());
//                    menu.add(new Ext.menu.Item(
//                    {
//                        text: 'Закрыть все окна',
//                        iconCls : 'close16',
//                        handler: function()
//                        {
//                            Ext.WindowMgr.each(function(wnd){
//                                if ( wnd.isVisible() )
//                                {
//                                    wnd.hide();
//                                }
//                            });
//                        }
//                    })
//                    );
//                }
//            }
//        },
//        menu: this.menu_windows,
//        tabIndex: -1
//    });
//
//	
//	// Меню в виде "ленты". Включается опцией в настройках
//	var main_menu_ribbon_items = [];
//	
//	main_menu_ribbon_items = main_menu_ribbon_items.concat([{
//		title: 'АРМ',
//		id: '_menu_arm',
//		iconCls: 'workplace-mp16',
//		hidden: getGlobalOptions().medstafffact == undefined,
//		items: [
//			sw.Promed.Actions.swMedPersonalWorkPlaceAction,
//			'-',
//			sw.Promed.Actions.WindowsAction,
//			'-',
//			{
//				text: 'Помощь',
//				iconCls: 'help16',
//				testId: 'mainmenu_help',
//				menu: this.menu_help
//			},
//			'-',
//			{
//				iconCls: 'user16',
//				text: UserLogin,
//				menu: this.user_menu
//			},
//			sw.Promed.Actions.PromedExit
//		]
//	}]);
//	
//	
//	main_menu_ribbon_items = main_menu_ribbon_items.concat([{
//		title: 'ЛПУ',
//		id: '_menu_lpu',
//		iconCls: 'lpu16',
//		items: [{
//			text: 'Паспорт ЛПУ',
//			//hidden: !isSuperAdmin() && !isLpuAdmin(),
//			iconCls: 'lpu16',
//			menu: new Ext.menu.Menu({
//				items: [
//				sw.Promed.Actions.OrgStructureViewAction,
//				sw.Promed.Actions.LpuStructureViewAction,
//				sw.Promed.Actions.swOrgPassportAction,
//				sw.Promed.Actions.swLpuPassportAction,
//				sw.Promed.Actions.GlossarySearchAction,
//				sw.Promed.Actions.swMESOldAction/*,
//				'-',
//				sw.Promed.Actions.SprPropertiesProfileAction,
//				'-',
//				sw.Promed.Actions.SprUchetFactAction*/
//				]
//			})
//		}, {
//			text: 'Персонал',
//			iconCls: 'staff16',
//			hidden: !isAdmin && !isLpuAdmin(),
//			menu: new Ext.menu.Menu({
//				items: [
//			sw.Promed.Actions.MedPersonalPlaceAction,
//			sw.Promed.Actions.MedWorkersAction,
//			sw.Promed.Actions.MedPersonalSearchAction
//				]
//			})
//		},/* {
//			text: 'Справочники',
//			iconCls: 'staff16',
//			hidden: !isAdmin && !isLpuAdmin(),
//			menu: new Ext.menu.Menu({
//				items: [
//					sw.Promed.Actions.swMESOldAction,
//					
//				]
//			})
//		}, */
//		/*{
//			text: 'Организации',
//			iconCls: 'spr-org16',
//			hidden: !isAdmin && !isLpuAdmin(),
//			menu: new Ext.menu.Menu({
//			items: [
//				sw.Promed.Actions.swOrgAllAction,
//				'-',
//				sw.Promed.Actions.swOrgLpuAction,
//				sw.Promed.Actions.swOrgGosAction,
//				sw.Promed.Actions.swOrgStrahAction,
//				sw.Promed.Actions.swOrgBankAction,
//				sw.Promed.Actions.swRlsFirmsAction
//			]})
//		},*/
//		'-',
//	sw.Promed.Actions.WindowsAction,
//		'-',
//		{
//			text: 'Помощь',
//			iconCls: 'help16',
//			testId: 'mainmenu_help',
//			menu: this.menu_help
//		},
//		'-',
//		{
//			iconCls: 'user16',
//			text: UserLogin,
//			menu: this.user_menu
//		},
//	sw.Promed.Actions.PromedExit
//		]
//	}]);
//
//	if (getGlobalOptions().region.nick != 'samara') {
//		main_menu_ribbon_items = main_menu_ribbon_items.concat([
//		{
//			title: 'ЛЛО',
//			id: '_menu_dlo',
//			iconCls: 'dlo16',
//			items: [{
//				text: 'Льготники',
//				iconCls: 'accessibility16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.swLgotTreeViewAction,
//					sw.Promed.Actions.LgotFindAction,
//						'-',
//					sw.Promed.Actions.EvnUdostViewAction
//					]
//				})
//			}, {
//				text: "Рецепты",
//				iconCls: 'receipt-search16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.EvnReceptFindAction,
//					sw.Promed.Actions.EvnReceptAddStreamAction,
//						'-',
//					sw.Promed.Actions.EvnReceptInCorrectFindAction,
//					sw.Promed.Actions.swTemperedDrugs
//					]
//				})
//			}, {
//				text: "Медикаменты",
//				iconCls: 'dlo16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.OstAptekaViewAction,
//					sw.Promed.Actions.OstDrugViewAction,
//					sw.Promed.Actions.OstSkladViewAction,
//						'-',
//					sw.Promed.Actions.DrugRequestViewAction,
//					sw.Promed.Actions.NewDrugRequestViewAction
//					]
//				})
//			}, {
//				text: "Справочники",
//				iconCls: 'spr-dlo16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.DrugMnnLatinNameEditAction,
//					sw.Promed.Actions.DrugTorgLatinNameEditAction,
//					
//						'-',
//					sw.Promed.Actions.OrgFarmacyViewAction // isAdmin
//					,'-',
//					sw.Promed.Actions.SprRlsAction
//					]
//				})
//			},
//			'-',
//		sw.Promed.Actions.WindowsAction,
//			'-',
//			{
//				text: 'Помощь',
//				iconCls: 'help16',
//				menu: this.menu_help
//			},
//			'-',
//			{
//				iconCls: 'user16',
//				text: UserLogin,
//				menu: this.user_menu
//			},
//			sw.Promed.Actions.PromedExit
//			]
//		}]);
//	}
//
//	if ( getGlobalOptions().region.nick != 'samara' && getGlobalOptions().region.nick != 'saratov') {
//		main_menu_ribbon_items = main_menu_ribbon_items.concat([
//		{
//			title: 'Поликлиника',
//			iconCls: 'polyclinic16',
//			items: [{
//				text: 'Пациенты',
//				iconCls: 'patient16',
//				menu: new Ext.menu.Menu({
//					items: [
//				sw.Promed.Actions.EvnPLStreamInputAction,
//				sw.Promed.Actions.EvnPLEditAction,
//					'-',
//				sw.Promed.Actions.PersonCardSearchAction,
//				sw.Promed.Actions.PersonCardViewAllAction,
//				sw.Promed.Actions.PersonCardStateViewAction,
//				sw.Promed.Actions.swPersonCardAttachListAction,
//					'-',
//				sw.Promed.Actions.AutoAttachViewAction
//					]
//				})
//			},
//		//sw.Promed.Actions.swMedPersonalWorkPlaceAction,
//		
//		//sw.Promed.Actions.swVKWorkPlaceAction,
//		
//		//sw.Promed.Actions.swMseWorkPlaceAction,
//			{
//				text: "Диспансеризация",
//				iconCls: 'disp-view16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.PersonPrivilegeWOWSearchAction,
//					sw.Promed.Actions.PersonDispWOWStreamAction,
//					sw.Promed.Actions.PersonDispWOWSearchAction,
//						'-',
//					sw.Promed.Actions.PersonDopDispSearchAction,
//					sw.Promed.Actions.PersonDopDispStreamInputAction,
//					sw.Promed.Actions.EvnPLDopDispSearchAction,
//					sw.Promed.Actions.EvnPLDopDispStreamInputAction,
//						'-',
//					sw.Promed.Actions.EvnPLDispDop13SearchAction,
//					// sw.Promed.Actions.EvnPLDispDop13StreamInputAction,
//					sw.Promed.Actions.EvnPLDispDop13SecondSearchAction,
//					// sw.Promed.Actions.EvnPLDispDop13SecondStreamInputAction,
//						'-',
//					sw.Promed.Actions.EvnPLDispTeen14SearchAction,
//					sw.Promed.Actions.EvnPLDispTeen14StreamInputAction,
//					sw.Promed.Actions.EvnPLDispSomeAdultSearchAction,
//					sw.Promed.Actions.EvnPLDispSomeAdultStreamInputAction,
//						'-',
//					sw.Promed.Actions.swRegChildOrphanDopDispFindAction,
//					sw.Promed.Actions.swRegChildOrphanDopDispStreamAction,
//					sw.Promed.Actions.swEvnPLChildOrphanDopDispFindAction,
//					sw.Promed.Actions.swEvnPLChildOrphanDopDispStreamAction,
//						'-',
//					sw.Promed.Actions.PersonDispSearchAction,
//					sw.Promed.Actions.PersonDispViewAction
//					]
//				})
//			}, {
//				text: "Беременные",
//				iconCls: 'pol-preg16',
//				hidden: !isAdmin && !isTestLpu,
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.swPregCardViewAction,
//					sw.Promed.Actions.swPregCardFindAction
//					]
//				})
//			}, {
//                text: "Иммунопрофилактика",
//                iconCls: 'pol-immuno16',
//                // hidden: ((getGlobalOptions().region.nick != 'ufa') && (!isAdmin)),
//                menu: new Ext.menu.Menu({
//                    items: [
//                    /*
//                     * Закоментировал Тагир
//                     *
//                    sw.Promed.Actions.swInjectionStreamAction,
//                    sw.Promed.Actions.swInjectionFindAction,
//                        '-',
//                    sw.Promed.Actions.swMedicalTapStreamAction,
//                    sw.Promed.Actions.swMedicalTapFindAction,
//                        '-',
//                    sw.Promed.Actions.swSerologyStreamAction,
//                    sw.Promed.Actions.swSerologyFindAction,
//                        '-',
//                    sw.Promed.Actions.swAbsenceBakAction,
//                        '-',
//                    sw.Promed.Actions.swCurrentPlanAction,
//                        '-',
//                    */
//                    sw.Promed.Actions.ammStartVacFormPlan,
//                    sw.Promed.Actions.ammvacListTasks,
//                    '-',
//                    sw.Promed.Actions.amm_JournalsVac,
//                    '-',
//                    sw.Promed.Actions.ammvacReport_5,
//                    '-',         
//                    sw.Promed.Actions.ammSprVaccine,
//                    sw.Promed.Actions.ammSprNacCal,
//                    '-',
//                    sw.Promed.Actions.ammVacPresence
//                    ]
//                })
//            },
//                      
//		sw.Promed.Actions.FundHoldingViewAction,
//			'-',
//		sw.Promed.Actions.WindowsAction,
//			'-',
//			{
//					text: 'Помощь',
//					iconCls: 'help16',
//					menu: this.menu_help
//			},
//			'-',
//			{
//					iconCls: 'user16',
//					text: UserLogin,
//					menu: this.user_menu
//			},
//		sw.Promed.Actions.PromedExit
//			]
//		}]);
//	}
//
//	if ( getGlobalOptions().region.nick != 'saratov') {
//		main_menu_ribbon_items = main_menu_ribbon_items.concat([
//		{
//			title: 'Стационар',
//			id: '_menu_stac',
//			iconCls: 'stac16',
//			hidden: false,
//			items: [{
//				text: 'Выбывшие',
//				iconCls: 'patient16',
//				menu: new Ext.menu.Menu({
//					items: [
//				sw.Promed.Actions.swEvnPSStreamAction,
//				sw.Promed.Actions.swEvnPSFindAction
//					]
//				})
//			},
//			sw.Promed.Actions.swJourHospDirectionAction,
//			//sw.Promed.Actions.swMedPersonalWorkPlaceStacAction,
//			//sw.Promed.Actions.swStacNurseWorkPlaceAction,
//			sw.Promed.Actions.swEvnPrescrViewJournalAction,
//			sw.Promed.Actions.swEvnPrescrCompletedViewJournalAction,
//			{
//				text: 'Суициды',
//				iconCls: 'suicide-edit16',
//				hidden: true,
//				menu: new Ext.menu.Menu({
//					items: [
//				sw.Promed.Actions.swSuicideAttemptsEditAction,
//				sw.Promed.Actions.swSuicideAttemptsFindAction
//					]
//				})
//			}, /* {
//				text: 'Патоморфология',
//				iconCls: 'pathomorph16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.EvnDirectionHistologicViewAction,
//					sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
//					sw.Promed.Actions.EvnHistologicProtoViewAction
//					]
//				})
//			}, */
//			'-',
//			sw.Promed.Actions.swExportToDBFBedFondAction,
//			'-',
//			
//			sw.Promed.Actions.WindowsAction,
//			'-',
//			{
//				text: 'Помощь',
//				iconCls: 'help16',
//				menu: this.menu_help
//			},
//			'-',
//			{
//				iconCls: 'user16',
//				text: UserLogin,
//				menu: this.user_menu
//			},
//		sw.Promed.Actions.PromedExit
//			]
//		}
//		]);
//	}
//
//	if (getGlobalOptions().region.nick != 'samara' && getGlobalOptions().region.nick != 'saratov') {
//		main_menu_ribbon_items = main_menu_ribbon_items.concat([
//		{
//			title: 'Параклиника',
//			id: '_menu_parka',
//			iconCls: 'parka16',
//			//hidden: !isAdmin && !isTestLpu,
//			hidden: false,
//			items:[{
//				text: 'Параклинические услуги',
//				iconCls: 'parka16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.swEvnUslugaParStreamAction,
//					sw.Promed.Actions.swEvnUslugaParFindAction
//					]
//				})
//			},
//			//sw.Promed.Actions.swMedPersonalWorkPlaceParAction,
//			'-',
//			sw.Promed.Actions.WindowsAction,
//			'-',
//			{
//				text: 'Помощь',
//				iconCls: 'help16',
//				menu: this.menu_help
//			},
//			'-',
//			{
//				iconCls: 'user16',
//				text: UserLogin,
//				menu: this.user_menu
//			},
//			sw.Promed.Actions.PromedExit
//			]
//		}, {
//				title: 'Стоматология',
//				hidden: (getGlobalOptions().region.nick == 'samara'),
//				iconCls: 'stomat16',
//				items:[{
//						text: 'Пациенты',
//						iconCls: 'patient16',
//						menu: new Ext.menu.Menu({
//								items: [
//							sw.Promed.Actions.swEvnPLStomStreamAction,
//							sw.Promed.Actions.swEvnPLStomSearchAction
//								]
//						})
//				}, /*{
//						text: 'Справочники',
//						iconCls: 'sprav16',
//						menu: new Ext.menu.Menu({
//								items: [
//							sw.Promed.Actions.swUslugaPriceListAction
//								]
//						})
//				},*/
//				'-',
//			sw.Promed.Actions.WindowsAction,
//				'-',
//				{
//						text: 'Помощь',
//						iconCls: 'help16',
//						menu: this.menu_help
//				},
//				'-',
//				{
//						iconCls: 'user16',
//						text: UserLogin,
//						menu: this.user_menu
//				},
//			sw.Promed.Actions.PromedExit
//				]
//		}]);
//	}
//
//	if ( getGlobalOptions().region.nick != 'saratov' ) {
//		main_menu_ribbon_items = main_menu_ribbon_items.concat([
//		{
//			title: 'Аптека',
//			iconCls: 'farmacy16',
//			hidden: false,
//			items:[
//				sw.Promed.Actions.swContractorsSprAction,
//				'-',
//				sw.Promed.Actions.swDokUchAction,
//				sw.Promed.Actions.swAktSpisAction,
//				sw.Promed.Actions.swDokOstAction,
//				sw.Promed.Actions.swInvVedAction,
//				sw.Promed.Actions.swMedOstatAction/*,
//				'-',
//				//sw.Promed.Actions.swDokOstAction,
//				//sw.Promed.Actions.swInvVedAction,
//				//sw.Promed.Actions.swMedOstatAction,
//				{
//					text: 'Рецепты',
//					hidden: (IS_DEBUG!=1),
//					iconCls: 'receipt-process16',
//					menu: new Ext.menu.Menu({
//						items: [
//						sw.Promed.Actions.EvnReceptProcessAction,
//						sw.Promed.Actions.EvnRPStreamInputAction,
//						sw.Promed.Actions.EvnReceptTrafficBookViewAction
//							]
//					})
//				}*/
//				]
//		}]);
//	}
//
//	if ( getGlobalOptions().region.nick != 'saratov' ) {
//		main_menu_ribbon_items = main_menu_ribbon_items.concat([
//		{
//			title: 'Документы',
//			id: '_menu_documents',
//			iconCls: 'documents16',
//			hidden: false,
//			items: [
//			sw.Promed.Actions.RegistryViewAction,
//				'-',
//				{
//					text: 'Патоморфология',
//					iconCls: 'pathomorph16',
//					//hidden: false,
//					hidden: (getGlobalOptions().region.nick == 'samara'),
//					menu: new Ext.menu.Menu({
//						items: [
//						sw.Promed.Actions.EvnDirectionHistologicViewAction,
//						sw.Promed.Actions.EvnHistologicProtoViewAction, 
//							'-',
//						sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
//						sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
//						]
//					})
//				},
//				{
//					text: 'Обращения',
//					hidden: !isAccessTreatment(),
//					iconCls: 'petition-stream16',
//					menu: new Ext.menu.Menu({
//						items: [
//						sw.Promed.Actions.swAttachmentDemandAction,
//							'-',
//						sw.Promed.Actions.swTreatmentStreamInputAction,
//						sw.Promed.Actions.swTreatmentSearchAction,
//						sw.Promed.Actions.swTreatmentReportAction
//						]
//					})
//				},
//				{
//					text: 'Свидетельства',
//					iconCls: 'medsvid16',
//					hidden: !(isAdmin || (getGlobalOptions().medsvidgrant_add && getGlobalOptions().medsvidgrant_add == 1)),
//					menu: new Ext.menu.Menu({
//						items: [
//						sw.Promed.Actions.swMedSvidBirthAction,
//						sw.Promed.Actions.swMedSvidDeathAction,
//						sw.Promed.Actions.swMedSvidPDeathAction,
//							'-',
//						sw.Promed.Actions.swMedSvidPrintAction
//						]
//					})
//				},
//				{
//					text: 'Извещения о ДТП',
//					iconCls: 'stac-accident-injured16',
//					hidden: !isAdmin,
//					menu: new Ext.menu.Menu({
//						items: [
//						sw.Promed.Actions.swEvnDtpWoundViewAction,
//						sw.Promed.Actions.swEvnDtpDeathViewAction
//						]
//					})
//				},
//				'-',
//			sw.Promed.Actions.swCardCallFindAction,
//				//'-',
//			sw.Promed.Actions.KerRocordBookAction, 
//				//'-',
//				/*
//				{
//					text: 'Карты вызова СМП',
//					iconCls: 'ambulance16',
//					hidden: !isAdmin,
//					menu: new Ext.menu.Menu({
//						items: [
//						sw.Promed.Actions.swCardCallViewAction,
//						sw.Promed.Actions.swCardCallFindAction
//						]
//					})
//				},*/
//				
//			sw.Promed.Actions.swJournalDirectionsAction,
//				
//			sw.Promed.Actions.EvnStickViewAction,
//				'-',
//			sw.Promed.Actions.WindowsAction,
//				'-',
//				{
//					text: 'Помощь',
//					iconCls: 'help16',
//					menu: this.menu_help
//				},
//				'-',
//				{
//					iconCls: 'user16',
//					text: UserLogin,
//					menu: this.user_menu
//				},
//			sw.Promed.Actions.PromedExit
//			]
//		}]);
//	}
//	
//	main_menu_ribbon_items = main_menu_ribbon_items.concat([
//	{
//		title: 'Отчеты',
//		id: '_menu_reports',
//		iconCls: 'reports16',
//		items:[
//	sw.Promed.Actions.ReportStatViewAction,
//		{
//			text: 'Форма Ф14 ОМС',
//			iconCls: 'rep-f14oms16',
//			menu: new Ext.menu.Menu({
//				items: [
//				sw.Promed.Actions.swF14OMSPerAction,
//				sw.Promed.Actions.swF14OMSAction,
//				sw.Promed.Actions.swF14OMSFinAction
//				]
//			})
//		},
//	sw.Promed.Actions.swReportEngineAction,
//		'-',
//	sw.Promed.Actions.WindowsAction,
//		'-',
//		{
//			text: 'Помощь',
//			iconCls: 'help16',
//			menu: this.menu_help
//		},
//		'-',
//		{
//			iconCls: 'user16',
//			text: UserLogin,
//			menu: this.user_menu
//		},
//	sw.Promed.Actions.PromedExit
//		]
//	}]);
//
//	main_menu_ribbon_items = main_menu_ribbon_items.concat([
//	{
//		title: 'Сервис',
//		id: '_menu_service',
//		iconCls: 'service16',
//		items: [
//			sw.Promed.Actions.swUsersTreeViewAction,
//			sw.Promed.Actions.swGroupsViewAction,
//			{
//				text: 'МИАЦ',
//				hidden: (getGlobalOptions().region.nick != 'ufa'),
//				iconCls: 'miac16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.MiacExportAction,
//					sw.Promed.Actions.MiacExportSheduleOptionsAction
//					]
//				})
//			},
//			sw.Promed.Actions.swOptionsViewAction,
//		   // sw.Promed.Actions.ConvertAction,
//			/*{
//				text: 'Настройки',
//				iconCls: 'service16',
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.swOptionsViewAction,
//					sw.Promed.Actions.swGlobalOptionAction
//					]
//				})
//			}, {
//					text: 'Двойники',
//					iconCls: 'doubles16',
//					hidden: !isAdmin,
//					menu: new Ext.menu.Menu({
//							items: [
//						sw.Promed.Actions.PersonDoublesSearchAction,
//						sw.Promed.Actions.PersonDoublesModerationAction,
//						sw.Promed.Actions.PersonUnionHistoryAction
//							]
//					})
//			},*/
//		sw.Promed.Actions.swPersonSearchAction,
//		sw.Promed.Actions.swImportAction,
//		sw.Promed.Actions.swLpuSelectAction,
//		//sw.Promed.Actions.swAdminWorkPlaceAction,
//		//sw.Promed.Actions.swRegWorkPlaceAction,
//			{
//				text: 'Система',
//				iconCls: 'swan16',
//				hidden: !isAdmin && !isTestLpu,
//				menu: new Ext.menu.Menu({
//					items: [
//					sw.Promed.Actions.swDivCountAction,
//					sw.Promed.Actions.loadLastObjectCode,
//					'-',
//					sw.Promed.Actions.swMSJobsAction,
//					'-',
//					sw.Promed.Actions.swRegistrationJournalSearchAction,
//					sw.Promed.Actions.swAnalyzerWindowAction,
//					'-',
//					sw.Promed.Actions.swRrlExportWindowAction,
//					sw.Promed.Actions.ConvertAction,
//					sw.Promed.Actions.swLdapAttributeChangeAction,
//					sw.Promed.Actions.swDicomViewerAction,
//					'-',
//					sw.Promed.Actions.TemplateRefValuesOpenAction,
//					sw.Promed.Actions.TemplatesWindowTestAction,
//					sw.Promed.Actions.TemplatesEditWindowAction,
//					//sw.Promed.Actions.ReportDBStructureAction,
//					//sw.Promed.Actions.swAssistantWorkPlaceAction,
//					sw.Promed.Actions.swSelectWorkPlaceAction,
//					sw.Promed.Actions.swTestAction
//					]
//				})
//			},
//			'-',
//		sw.Promed.Actions.WindowsAction,
//			'-',
//			{
//					text: 'Помощь',
//					iconCls: 'help16',
//					menu: this.menu_help
//			},
//			'-',
//		sw.Promed.Actions.UserProfileAction,
//		sw.Promed.Actions.MessageAction,
//			'-',
//			{
//					iconCls: 'user16',
//					text: UserLogin,
//					menu: this.user_menu
//			},
//			'-',
//		sw.Promed.Actions.PromedExit
//		]
//	}]);
//
////	main_menu_ribbon = new sw.Promed.swTabToolbar (main_menu_ribbon_items);
//    // End of main_menu_ribbon
//
//	this.leftMenu = new Ext.Panel(
//	{
//		region: 'center',
//		border: false,
//		layout:'accordion',
//		defaults:
//		{
//			bodyStyle: 'padding:5px'
//		},
//		layoutConfig:
//		{
//			titleCollapse: true,
//			animate: true,
//			activeOnTop: false
//		},
//		items:
//		[{
//			title: 'Скорая помощь',
//			xtype: 'panel',
//			border: false,
//			items: [this.main_menu_ribbon],
//			hidden: !isAdmin
//		},
//		{
//			title: 'Panel 2',
//			border: false,
//			html: '<p>Panel content!</p>'
//		},
//		{
//			title: 'Panel 3',
//			border: false,
//			html: '<p>Panel content!</p>'
//		}]
//	});
//
//	var left_panel =
//	{
//		animCollapse: false,
//		margins: '5 0 0 0',
//		cmargins: '5 5 0 0',
//		width: 200,
//		minSize: 100,
//		maxSize: 250,
//		layout: 'border',
//		id: 'left_panel',
//		region: 'west',
//		floatable: false,
//        collapsible: true,
//		listeners:
//		{
//			collapse: function()
//			{
//				main_center_panel.doLayout();
//			},
//			resize: function (p,nW, nH, oW, oH)
//			{
//				//main_center_panel.doLayout();
//			}
//		},
//		border: true,
//		title: 'Меню',
//		/*iconCls: 'promed16',*/
//		split: true,
//		items: [/*{
//			region: 'north',
//			border: false
//		}, */this.leftMenu] /*info*/
//	};
//
//    // Установка типа меню в зависимости от настройки пользователя
////    if (Ext.globalOptions.appearance.menu_type == 'ribbon') {
////        main_promed_toolbar = main_menu_ribbon;
////    } else {
////        main_promed_toolbar = main_menu_panel;
////    }
//	
//	
//	// Временно
////	if( /LpuCadrAdmin/g.test(getGlobalOptions().groups) ) {
////		main_promed_toolbar = new sw.Promed.Toolbar({
////			autoHeight: true,
////			region: 'north',
////			items: [
////				sw.Promed.Actions.swMedPersonalWorkPlaceAction,
////				{
////					iconCls: 'progress16',
////					text: '',
////					hidden: true,
////					id: '_menu_progress',
////					tabIndex: -1
////				},
////				'-',
////				sw.Promed.Actions.PromedExit
////			]
////		});
////	}
//	//main_promed_toolbar.setVisible( String(getGlobalOptions().groups).indexOf('LpuCadrAdmin') == 1 );
//
//	// центральная панель
////	main_center_panel = new Ext.Panel({
////		id: 'main-center-panel',
////		layout: 'fit',
////        tbar: main_promed_toolbar,
////		region: 'center',
////		bodyStyle:'width:100%;height:100%;background:#aaa;padding:0;'
////	});
////
////	// проверяем настройку на сервере дя работы с Картридером
////	
////	$cardreader_is_enable = getGlobalOptions()['card_reader_is_enable'] ? getGlobalOptions()['card_reader_is_enable'] : false;
////	if ( $cardreader_is_enable === true )
////	{
////		if (navigator.javaEnabled()) {
////			var appl = Ext.getBody().createChild({
////				name: 'apl',
////				tag: 'applet',
////				archive:'applets/swSmartCard_2ids.jar',
////				code:'swan/SmartCard/swSmartCard',
////				width: 0,
////				height: 0,
////				id: 'card_appl',
////				style:'width:1px,height:1px'
////			});
////		} else {
////			setPromedInfo('Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>', 'javamashine-info');
////		}
////	}
////	
////	if(Ext.globalOptions.others.enable_uecreader) {
////		initCardReaders();
////		log('Подключаем апплет УЭК');
////		sw.Applets.uec.initUec();
////	}
////
////	if ( Ext.globalOptions.others.enable_barcodereader ) {
////		log('Подключаем апплет для сканера штрих-кодов');
////		sw.Applets.BarcodeScaner.initBarcodeScaner();
////	}
//	
//	/*main_center_panel2 = new Ext.Panel({
//		autoHeight: true,
//		region: 'north',
//		bodyStyle:'width:100%;background:#aaa;padding:0;',
//		html: 'lalala'
//	});*/
//
////	main_tabs_panel = new Ext.TabPanel({
////		id: 'main-tabs-panel',
////		bodyStyle:'background-color:#16334a;',
////		//bodyStyle:'background-color:#aaa;',
////		resizeTabs: true,
////		minTabWidth: 115,
////		tabWidth: 135,
////		enableTabScroll: true,
////		defaults: {autoScroll: true},
////		html: '<div></div>'
////	});
////
////	main_center_panel.add(main_tabs_panel);
////	
////	
////	main_messages_tpl = new Ext.XTemplate(
////	 '<div onMouseOver="if(isMouseLeaveOrEnter(event, this)){this.style.display=&quot;block&quot;; Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(220);}" ',
////		'onMouseOut="if(isMouseLeaveOrEnter(event, this)){this.style.display=&quot;block&quot;; Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(50);}" ',
////		'onClick="getWnd(&quot;swMessagesViewWindow&quot;).show({mode: &quot;newMessages&quot;});" ',
////		'style="background: silver; padding: 3px 0px 3px 5px; height: 48px; border: 1px solid gray; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -webkit-box-shadow: 1px 1px 1px #888888; box-shadow: 1px 1px 1px #888888;cursor:pointer;cursor:hand;">',
////		'<div style="float: left; width: 48px; height: 48px;" class="mail48unread">',
////		'<div style="float: left; font: bold 12px Tahoma; color:#444; margin-top:20px; opacity:0.8;filter: alpha(opacity=80); padding:1px; width:100%;text-align:center;">{count}</div>',
////		'</div>',
////		'<div style="margin-left: 56px;margin-top:6px;"><a style="font: normal 13px Tahoma; color: black;text-shadow: 1px 1px #cccccc;" href="#">У Вас <b>{count}</b> непрочитанн{okch} сообщен{ok}</a></div>',
////		'</div>'
////	);
////	
////	main_messages_panel = new Ext.Panel({
////		id: 'main-messages-panel',
////		hidden: true,
////		style: 'position: absolute; z-index: 20000;',
////		bodyStyle: 'background: none;',
////		height: 56,
////		mLeft: 50, // Сколько px панели видно слева =)
////		mTop: 220, // Отступ сверху
////		hideOver: function(shift) {
////			this.setPosition(Ext.getBody().getWidth()-shift, this.mTop);
////		},
////		border: false,
////		width: 225,
////		html: '<div onMouseOver="Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(220);" '+
////			'onMouseOut="Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(50);" '+
////			'onClick="getWnd(&quot;swMessagesViewWindow&quot;).show({mode: &quot;newMessages&quot;});" '+
////			'style="background: silver; padding: 3px 0px 3px 5px; height: 48px; border: 1px solid gray; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -webkit-box-shadow: 1px 1px 1px #888888; box-shadow: 1px 1px 1px #888888;">'+
////			'<div style="float: left; width: 48px; height: 48px;" class="mail48">'+
////			'<div style="float: left; font: bold 12px Tahoma; color:#444; margin-top:20px; opacity:0.8;filter: alpha(opacity=80); padding:1px; width:100%;text-align:center;"></div></div>'+
////			'<div style="margin-left: 56px;margin-top:6px;"><a style="font: normal 13px Tahoma; color: black;text-shadow: 1px 1px #cccccc;" href="#">У Вас <b>нет</b> непрочитанных сообщений</a></div>'+
////			'</div>'
////	});
////	main_center_panel.add(main_messages_panel);
////	
////	main_frame = new Ext.Viewport({
////		layout:'border',
////		items: [
////			main_center_panel/*,
////			left_panel
////			new Ext.Panel({
////				region: 'south',
////				title: '_',
////				height: 1,
////				id: 'ajax_state'
////			})*/
////		],
////		listeners: {
////			resize: function()
////			{
////				main_messages_panel.hideOver(main_messages_panel.mLeft);
////			}
////		}
////	});
////
////    if (Ext.globalOptions.appearance.menu_type == 'ribbon' && typeof main_promed_toolbar.delegateUpdates == 'function') {
////        main_promed_toolbar.delegateUpdates();
////    }
//
////   main_frame.doLayout();
}

Ext.onReady(function (){
	if ( is_ready )
	{
		return;
	}
	
	// Запускалка
//	sw.Promed.tasks = new Ext.util.TaskRunner();
//	// Маска поверх всех окон
//	var mask = Ext.getBody().mask();
//	//Ext.Element.setZIndex
//	mask.setStyle('z-index', Ext.WindowMgr.zseed + 10000);
//	// log(Ext.WindowMgr.zseed);
//	// log(Ext.WindowMgr);
	sw.Promed.mask = new Ext.LoadMask(Ext.getBody(), {msg: LOAD_WAIT});
	sw.Promed.mask.hide();
//	
//	Ext.Ajax.timeout = 600000;
	
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
//		getWnd('comboboxes').load({callback: function() {
//			// Глоссарий загружается после выбора драйвера для работы с локальным хранилищем, поскольку использует локальное хранилище
//			// getWnd('glossary').load();
//			
//			// Старт тасков
//			if (getAppearanceOptions().is_popup_message) {
//				this.taskTimer = function() {
//					return {run: taskRun, interval: ((getGlobalOptions().message_time_limit)?getGlobalOptions().message_time_limit:5)*60*1000};
//				}
//				sw.Promed.tasks.start(this.taskTimer());
//			}
//			
		// Выбор ЛПУ в случае если их несколько у человека
		Ext.Ajax.request({
			failure: function(response, options) {
				Ext.Msg.alert('Ошибка', 'Произошла ошибка при входе в систему, повторите попытку через некоторое время.');
			},
			success: function(resp, options) {
				var response_obj = Ext.JSON.decode(resp.responseText);
				if (response_obj.length>1) { // || getGlobalOptions().superadmin // больше одного ЛПУ у человека
					var lpuarr = [];
					for (i=0; i < response_obj.length; i++) {
						if (response_obj[i]['lpu_id']!=undefined) {
							lpuarr.push(response_obj[i]['lpu_id']);
						}
					}
					//swSelectLpuWindow = new sw.Promed.swSelectLpuWindow();
					//main_frame.add(swSelectLpuWindow);
					//main_frame.doLayout();
					//getWnd('swSelectLpuWindow').show( {params : lpuarr} );
					getWnd('swGlobalControl').show({})
				}
				else if(response_obj.length>0) { // В случае если ЛПУ одно, то прогружаем список медперсонала и отделения сразу
//					loadLpuBuildingGlobalStore();
//					loadLpuSectionGlobalStore();
//					loadLpuSectionWardGlobalStore();
//					loadLpuUnitGlobalStore();
//					loadMedServiceGlobalStore();
//					loadMedStaffFactGlobalStore();
//					getCountNewDemand();
					// Открытие АРМа по умолчанию
					// Будет теперь в globalApplication
					getWnd('swGlobalControl').show({})
				}
			},
			url: C_USER_GETOWNLPU_LIST
		});

		// Выбор АРМа
		//console.log(Ext.globalOptions);
//		switch (Ext.globalOptions.defaultARM.ARMType)
//		{
//			case 'smpdispatchdirect' : {getWnd('swWorkPlaceSMPDispatcherDWE4').show({}); break;}
//			case 'smpdispatchcall' : {getWnd('swWorkPlaceSMPDispatcherCallE4').show({}); break;}
//			case 'smpheadduty' : {getWnd('swWorkPlaceSMPHeadDutyWindowE4').show({}); break;}
//		}
			
			
//			if ( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 )
//				getWnd('swSelectLpuWindow').show({});
//			
//			//main_messages_panel.setPosition(Ext.getBody().getWidth()-main_messages_panel.mLeft, main_messages_panel.mTop);
//			
//		}.bind(this)});
	});
});

sw.lostConnection = false;
// Раз в несколько секунд опрашиваем связь с сервером, а если её нет переключаемся на локальный веб.
setInterval(function () {
	checkConnection();
}, 10000); // 10 секунд