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

function connectSocket()
{
	var opts = getGlobalOptions();

	if (!opts.NodeJSControl || !opts.NodeJSControl.host) {
		return false;
	}

	var socket = io(opts.NodeJSControl.host);

	socket.on('connect', function () {
		log('socket connect');

		socket.on('promedMessage', function (options) {
			Ext.Msg.alert(options.title, options.message);
		});

		socket.on('registration', function (callback) {
			callback(document.cookie, opts.pmuser_id, navigator.userAgent);
		});

		socket.on('logout', function() {
			window.onbeforeunload = null;
			location.replace(C_LOGOUT);
		});
	});

	socket.connect();

	return socket;
}

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
			text: langs('Поиск ТАП/КВС'),
			tooltip: langs('Поиск ТАП/КВС'),
			iconCls : 'test16',
			handler: function() {
				getWnd('swEvnPLEvnPSSearchWindow').show();
			},
			hidden: false
		},
		swEvnPLEvnPSViewAction: {
			text: langs('Выбор ТАП/КВС'),
			tooltip: langs('Выбор ТАП/КВС'),
			iconCls : 'test16',
			handler: function() {
				getWnd('swEvnPLEvnPSSearchWindow').show({
					Person_id: 421380
				});
			},
			hidden: false
		},
		EvnDirectionMorfoHistologicViewAction: {
			text: langs('Направления на патоморфогистологическое исследование'),
			tooltip: langs('Журнал направлений на патоморфогистологическое исследование'),
			iconCls : 'pathomorph16',
			handler: function() {
				getWnd('swEvnDirectionMorfoHistologicViewWindow').show();
			},
			hidden: false
		},
		EvnStickViewAction: {
			text: langs('ЛВН: Поиск'),
			tooltip: langs('Поиск листков временной нетрудоспособности'),
			iconCls : 'lvn-search16',
			handler: function() {
				getWnd('swEvnStickViewWindow').show();
			},
			hidden: false //(!isAdmin || IS_DEBUG != 1)
		},
		EvnMorfoHistologicProtoViewAction: {
			text: langs('Протоколы патоморфогистологических исследований'),
			tooltip: langs('Журнал протоколов патоморфогистологических исследований'),
			iconCls : 'pathomorph16',
			handler: function() {
				getWnd('swEvnMorfoHistologicProtoViewWindow').show();
			},
			hidden: false
		},
		EvnHistologicProtoViewAction: {
			text: langs('Протоколы патологогистологических исследований'),
			tooltip: langs('Журнал протоколов патологогистологических исследований'),
			iconCls : 'pathohistproto16',
			handler: function() {
				getWnd('swEvnHistologicProtoViewWindow').show();
			},
			hidden: false
		},
		EvnDirectionHistologicViewAction: {
			text: langs('Направления на патологогистологическое исследование'),
			tooltip: langs('Журнал направлений на патологогистологическое исследование'),
			iconCls : 'pathohist16',
			handler: function() {
				getWnd('swEvnDirectionHistologicViewWindow').show();
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
		PersonDoublesSearchAction: {
			text: langs('Работа с двойниками'),
			tooltip: langs('Работа с двойниками'),
			iconCls: 'doubles16',
			handler: function() {
				getWnd('swPersonDoublesSearchWindow').show();
			},
			hidden: !isAdmin
		},
		PersonDoublesModerationAction: {
			text: langs('Модерация двойников'),
			tooltip: langs('Модерация двойников'),
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
			text: langs('История модерации двойников'),
			tooltip: langs('История модерации двойников'),
			iconCls: 'doubles-history16',
			handler: function() {
				getWnd('swPersonUnionHistoryWindow').show();
			},
			hidden: true
		},
		UslugaComplexViewAction: {
			text: langs('Комплексные услуги'),
			tooltip: langs('Комплексные услуги'),
			iconCls: 'services-complex16',
			handler: function() {
				getWnd('swUslugaComplexViewWindow').show();
			},
			hidden: true
		},
		UslugaComplexTreeAction: {
			text: langs('Комплексные услуги'),
			tooltip: langs('Комплексные услуги'),
			iconCls: 'services-complex16',
			handler: function()
			{
			},
			hidden: true
		},
		RegistryViewAction: {
			text: langs('Реестры счетов'),
			tooltip: langs('Реестры счетов'),
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swRegistryViewWindow').show({Registry_IsNew: 1});
			},
			hidden: (
				getRegionNick().inlist([ 'by' ])
				|| (
					!getRegionNick().inlist([ 'krasnoyarsk', 'ufa' ])
					&& !isUserGroup([ 'RegistryUser', 'RegistryUserReadOnly' ])
					&& !isSuperAdmin()
				)
				|| (
					getRegionNick().inlist(['ufa'])
					&& !isUserGroup([ 'LpuPowerUser' ])
					&& !isSuperAdmin()
				)
				|| (
					getRegionNick().inlist(['krasnoyarsk'])
					&& !isUserGroup(['LpuAdmin', 'RegistryUser', 'RegistryUserReadOnly', 'SuperAdmin'])
				)
			)
		},
		RegistryExportInTFOMS: {
			text: 'Выгрузка реестров в ТФОМС ПО',
			tooltip: 'Выгрузка реестров в ТФОМС ПО',
			handler: function () {
				var PHPSESSID = document.cookie.match("(?:^|; )PHPSESSID=([^;]*)")[1];
				window.open(REGISTRY_EXPORT_IN_TFOMS_ADDR + '?PHPSESSID=' + PHPSESSID);
			}
		},
		RegistryEUViewAction: {
			text: 'Реестры внешних услуг',
			tooltip: 'Реестры внешних услуг',
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swRegistryEUSearchWindow').show();
			},
			hidden: true // !((getRegionNick() == 'perm') && (isSuperAdmin() || isUserGroup([ 'RegistryUser', 'RegistryUserReadOnly' ])))
		},
		TariffVolumeViewAction: {
			text: langs('Тарифы и объемы'),
			tooltip: langs('Тарифы и объемы'),
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swTariffVolumesViewWindow').show();
			}
			//hidden: (getRegionNick() != 'perm')
		},
		RegistryNewViewAction: {
			text: langs('Реестры счетов (новые)'),
			tooltip: langs('Реестры счетов'),
			iconCls : 'service-reestrs16',
			handler: function() {
				if ( getRegionNick() == 'vologda' ) {
					getWnd('swRegistryNewViewWindow').show();
				}
				else {
					getWnd('swRegistryViewWindow').show({Registry_IsNew: 2});
				}
			},
			hidden: !getRegionNick().inlist([ 'ufa', 'vologda' ])
				|| (
					getRegionNick().inlist([ 'vologda' ])
					&& !isUserGroup([ 'RegistryUser', 'RegistryUserReadOnly' ])
					&& !isSuperAdmin()
				)
				|| (
					getRegionNick().inlist(['ufa'])
					&& !isUserGroup([ 'LpuPowerUser' ])
					&& !isSuperAdmin()
				)
		},
		MiacExportAction: {
			text: langs('Выгрузка для МИАЦ'),
			tooltip: langs('Выгрузка данных для МИАЦ'),
			iconCls : 'service-reestrs16',
			handler: function() {
				getWnd('swMiacExportWindow').show();
			},
			hidden: (getRegionNick() != 'ufa')
		},
		MiacExportSheduleOptionsAction: {
			text: langs('Настройки автоматической выгрузки для МИАЦ'),
			tooltip: langs('Настройки автоматической выгрузки для МИАЦ'),
			iconCls : 'settings16',
			handler: function() {
				getWnd('swMiacExportSheduleOptionsWindow').show();
			},
			hidden: (getRegionNick() != 'ufa')
		},
		/*RegistryEditAction: {
			text: langs('Редактирование реестра (счета)'),
			tooltip: langs('Редактирование реестра (счета)'),
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
			text: langs('Печать заявки'),
			tooltip: langs('Печать заявки по выбранной МО или по всем МО'),
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
			tooltip: langs('Редактирование латинского наименования медикамента'),
			iconCls : 'drug-viewtorg16',
			handler: function()
			{
				getWnd('swDrugTorgViewWindow').show();
			},
			hidden:(getRegionNick() == 'saratov'||getRegionNick() == 'pskov')
		},

		DrugMnnLatinNameEditAction: {
			text: WND_DLO_DRUGMNNLATINEDIT,
			tooltip: langs('Редактирование латинского наименования МНН'),
			iconCls : 'drug-viewmnn16',
			handler: function()
			{
				getWnd('swDrugMnnViewWindow').show({
					privilegeType: 'all'
				});
			},
			hidden:(getRegionNick() == 'saratov'||getRegionNick() == 'pskov')
		},

		PersonCardSearchAction: {
			text: WND_POL_PERSCARDSEARCH,
			tooltip: langs('Поиск карты пациента'),
			iconCls : 'card-search16',
			handler: function()
			{
				getWnd('swPersonCardSearchWindow').show();
			}
		},

		PersonCardViewAllAction: {
			text: WND_POL_PERSCARDVIEWALL,
			tooltip: langs('Картотека: работа со всей картотекой'),
			iconCls : 'card-view16',
			handler: function()
			{
				getWnd('swPersonCardViewAllWindow').show();
			}
		},

		PersonCardStateViewAction: {
			text: WND_POL_PERSCARDSTATEVIEW,
			tooltip: langs('Просмотр журнала движения по картотеке пациентов'),
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
			hidden: true,
			handler: function()
			{
				var id_salt = Math.random();
				var win_id = 'report' + Math.floor(id_salt*10000);
				// собственно открываем окно и пишем в него
				var win = window.open('/?c=AutoAttach&m=doAutoAttach', win_id);
			}
		},
		worksheetList: {
			text: 'Конструктор анкет',
			tooltip: 'Конструктор анкет',
			handler: function() {
				getWnd('worksheetListWindow').show();
			},
			hidden: !isAdmin
		},
		PersonDispSearchAction: {
			text: WND_POL_PERSDISPSEARCH,
			tooltip: langs('Поиск диспансерной карты пациента'),
			iconCls : 'disp-search16',
			handler: function()
			{
				getWnd('swPersonDispSearchWindow').show();
			},
			hidden: false//!(isAdmin || isTestLpu)
		},
		PersonDispViewAction: {
			text: WND_POL_PERSDISPSEARCHVIEW,
			tooltip: langs('Просмотр диспансерной карты пациента'),
			iconCls : 'disp-view16',
			handler: function()
			{
				getWnd('swPersonDispViewWindow').show({mode: 'view'});
			},
			hidden: false//!(isAdmin || isTestLpu)
		},
		EvnPLEditAction: {
			text: langs('Талон амбулаторного пациента: Поиск'),
			tooltip: langs('Поиск талона амбулаторного пациента'),
			iconCls : 'pol-eplsearch16',
			handler: function()
			{
				getWnd('swEvnPLSearchWindow').show();
			}
		},
		EvnPLStreamInputAction: {
			text: MM_POL_EPLSTREAM,
			tooltip: langs('Потоковый ввод талонов амбулаторного пациента'),
			iconCls : 'pol-eplstream16',
			handler: function()
			{
				getWnd('swEvnPLStreamInputWindow').show();
			}
		},

		LpuStructureViewAction: {
			text: langs('Структура МО'),
			tooltip: langs('Структура МО'),
			iconCls : 'lpu-struc16',
			hidden: !isAdmin && !isLpuAdmin() && !isCadrUserView() && !isRegAdmin() && !isUserGroup('OuzSpec'),
			handler: function()
			{
				getWnd('swLpuStructureViewForm').show();
			}
		},
		
		OrgStructureViewAction: {
			text: langs('Структура организации'),
			tooltip: langs('Структура организации'),
			iconCls : 'lpu-struc16',
			hidden: (!isAdmin && !isOrgAdmin()) || !isDebug(),
			handler: function()
			{
				getWnd('swOrgStructureWindow').show();
			}
		},

		OnkoControlAction: {
			text: langs('Онкоконтроль'),
			tooltip: langs('Онкоконтроль'),
			hidden: getRegionNick() == 'ufa',
			iconCls: 'stac-psstream16',
			handler: function() {
				var record = {'Lpu_id': getGlobalOptions().lpu_id};
				getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
			}
		},

		PalliativeAction: {
			text: langs('Паллиативная помощь'),
			tooltip: langs('Паллиативная помощь'),
			iconCls : 'stac-psstream16',
			hidden: getRegionNick() == 'kz',
			handler: function(){
				var record = {'Lpu_id' : getGlobalOptions().lpu_id};
				record.ReportType = 'palliat';
				getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
			}
		},

		GeriatricAction: {
			text: langs('Возраст не помеха'),
			tooltip: langs('Возраст не помеха'),
			iconCls: 'stac-psstream16',
			hidden: getRegionNick() == 'kz',
			handler: function(){
				var record = {'Lpu_id': getGlobalOptions().lpu_id};
				record.ReportType = 'geriatrics';
				getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
			}
		},

		BIRADSAction: {
			text: langs('Оценка BI-RADS'),
			tooltip: langs('Оценка BI-RADS'),
			iconCls: 'stac-psstream16',
			hidden: getRegionNick() == 'kz',
			handler: function(){
				var record = {'Lpu_id': getGlobalOptions().lpu_id};
				record.ReportType = 'birads';
				getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
			}
		},

		RECISTAction: {
			text: langs('Оценка RECIST'),
			tooltip: langs('Оценка RECIST'),
			iconCls: 'stac-psstream16',
			hidden: getRegionNick() == 'kz',
			handler: function(){
				var record = {'Lpu_id': getGlobalOptions().lpu_id};
				record.ReportType = 'recist';
				getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
			}
		},

		PreliminarySurveyAction: {
			text: langs('Предварительное анкетирование'),
			tooltip: langs('Предварительное анкетирование'),
			hidden: (!getRegionNick().inlist(['kz'])),
			iconCls: 'stac-psstream16',
			handler: function() {
				var record = {'Lpu_id': getGlobalOptions().lpu_id};
				record.ReportType = 'previzit';
				getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
			}
		},

		FundHoldingViewAction: {
			text: langs('Фондодержание'),
			tooltip: langs('Фондодержание'),
			iconCls : 'lpu-struc16',
			hidden: (!isAdmin || getRegionNick().inlist(['by'])) ,//&& !getGlobalOptions()['mp_is_zav'] && !getGlobalOptions()['mp_is_uch'],
			handler: function()
			{
				getWnd('swFundHoldingViewForm').show();
			}
		},
		
		LgotFindAction: {
			text: MM_DLO_LGOTSEARCH,
			tooltip: langs('Поиск льготников'),
			iconCls : 'lgot-search16',
			handler: function()
			{
				getWnd('swPrivilegeSearchWindow').show();
			}
		},
		LgotAddAction: {
			text: MM_DLO_LGOTADD,
			tooltip: langs('Добавление льготника'),
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
					return false;
				}

				if (getWnd('swPrivilegeEditWindow').isVisible())
				{
					Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования льготы уже открыто'));
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
			tooltip: langs('Просмотр удостоверений'),
			iconCls : 'udost-list16',
			handler: function()
			{
				getWnd('swUdostViewWindow').show();
			}
		},
		EvnUdostAddAction: {
			text: MM_DLO_UDOSTADD,
			tooltip: langs('Добавление удостоверений'),
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
					return false;
				}

				if (getWnd('swEvnUdostEditWindow').isVisible())
				{
					Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования удостоверения уже открыто'));
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
		EvnReceptAddStreamAction: {
			text: MM_DLO_RECSTREAM,
			tooltip: langs('Ввод рецептов'),
			iconCls : 'receipt-stream16',
			hidden: (
				getGlobalOptions().region && getRegionNick() == 'saratov'
				&& !(isSuperAdmin() || isLpuAdmin() || isUserGroup('LpuUser'))
			),
			handler: function()
			{
				getWnd('swReceptStreamInputWindow').show();
			}
		},
		EvnReceptAddAction: {
			text: MM_DLO_RECADD,
			tooltip: langs('Добавление рецепта'),
			iconCls : 'x-btn-text',
			handler: function()
			{
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
					return false;
				}

				if (getWnd('swEvnReceptEditWindow').isVisible())
				{
					Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования рецепта уже открыто'));
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
			tooltip: langs('Поиск рецептов'),
			iconCls : 'receipt-search16',
			handler: function()
			{
				getWnd('swEvnReceptSearchWindow').show();
			}
		},
		EvnReceptInCorrectFindAction: {
			text: langs('Журнал отсрочки'),
			tooltip: langs('Журнал отсрочки'),
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
		PersonPrivilegeWOWStreamInputAction: {
			text: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поточный ввод') : langs('Регистр ВОВ: Поточный ввод'),
			tooltip: (getRegionNick().inlist(['ufa','ekb','penza','astra'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поточный ввод') : langs('Регистр ВОВ: Поточный ввод'),
			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
			handler: function()
			{
				getWnd('swPersonPrivilegeWOWStreamInputWindow').show();
			}
		},
		PersonDispWOWStreamAction: {
			text: langs('Обследования ВОВ: Поточный ввод'),
			tooltip: langs('Обследования ВОВ: Поточный ввод'),
			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
			handler: function()
			{
				getWnd('EvnPLWOWStreamWindow').show();
			}
		},
		PersonDispWOWSearchAction: {
			text: langs('Обследования ВОВ: Поиск'),
			tooltip: langs('Обследования ВОВ: Поиск'),
			iconCls : 'dopdisp-search16', // to-do: Поменять иконку
			handler: function()
			{
				getWnd('EvnPLWOWSearchWindow').show();
			}
		},
		PersonDopDispSearchAction: {
			text: MM_POL_PERSDDSEARCH,
			tooltip: langs('Дополнительная диспансеризация: поиск'),
			iconCls : 'dopdisp-search16',
			hidden: getRegionNick().inlist(['adygeya']),
			handler: function()
			{
				getWnd('swPersonDopDispSearchWindow').show();
			}
		},
		PersonDopDispStreamInputAction: {
			text: MM_POL_PERSDDSTREAMINPUT,
			tooltip: langs('Дополнительная диспансеризация: потоковый ввод'),
			iconCls : 'dopdisp-stream16',
			handler: function()
			{
				getWnd('swPersonDopDispSearchWindow').show({mode: 'stream'});
			}
		},
		EvnPLDopDispSearchAction: {
			text: langs('Талон по дополнительной диспансеризации взрослых (до 2013г.): поиск'),
			tooltip: langs('Талон по дополнительной диспансеризации взрослых (до 2013г.): поиск'),
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispDopSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDopDispStreamInputAction: {
			text: MM_POL_EPLDDSTREAM,
			tooltip: langs('Талон по доп. диспансеризации: потоковый ввод'),
			iconCls : 'dopdisp-epl-stream16',
			handler: function()
			{
				getWnd('swEvnPLDispDopSearchWindow').show({mode: 'stream'});
			},
			hidden: false //!isAdmin
		},
		PersonDispOrpSearchAction: {
			text: langs('Регистр детей-сирот (стационарных): Поиск'),
			tooltip: langs('Регистр детей-сирот (стационарных): Поиск'),
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
			text: langs('Регистр детей-сирот (усыновленных/опекаемых): Поиск'),
			tooltip: langs('Регистр детей-сирот (усыновленных/опекаемых): Поиск'),
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
			text: langs('Регистр периодических осмотров несовершеннолетних: Поиск'),
			tooltip: langs('Регистр периодических осмотров несовершеннолетних: Поиск'),
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpPeriodSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionSearchAction: {
			text: langs('Периодические осмотры несовершеннолетних: Поиск'),
			tooltip: langs('Периодические осмотры несовершеннолетних: Поиск'),
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		PersonDispOrpProfSearchAction: {
			text: langs('Направления на профилактические осмотры несовершеннолетних: Поиск'),
			tooltip: langs('Направления на профилактические осмотры несовершеннолетних: Поиск'),
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpProfSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionProfSearchAction: {
			text: langs('Профилактические осмотры несовершеннолетних - 1 этап: Поиск'),
			tooltip: langs('Профилактические осмотры несовершеннолетних - 1 этап: Поиск'),
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionProfSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionProfSecSearchAction: {
			text: langs('Профилактические осмотры несовершеннолетних - 2 этап: Поиск'),
			tooltip: langs('Профилактические осмотры несовершеннолетних - 2 этап: Поиск'),
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionProfSecSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		PersonDispOrpPredSearchAction: {
			text: langs('Направления на предварительные осмотры несовершеннолетних: Поиск'),
			tooltip: langs('Направления на предварительные осмотры несовершеннолетних: Поиск'),
			iconCls : 'dopdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpPredSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionPredSearchAction: {
			text: langs('Предварительные осмотры несовершеннолетних - 1 этап: Поиск'),
			tooltip: langs('Предварительные осмотры несовершеннолетних - 1 этап: Поиск'),
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionPredSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenInspectionPredSecSearchAction: {
			text: langs('Предварительные осмотры несовершеннолетних - 2 этап: Поиск'),
			tooltip: langs('Предварительные осмотры несовершеннолетних - 2 этап: Поиск'),
			iconCls : 'dopdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenInspectionPredSecSearchWindow').show();
			},
			hidden: false //!isAdmin
		},
		EvnPLDispTeenExportAction: {
			text: langs('Экспорт карт по диспансеризации несовершеннолетних'),
			tooltip: langs('Экспорт карт по диспансеризации несовершеннолетних'),
			iconCls : 'database-export16',
			handler: function()
			{
				getWnd('swEvnPLDispTeenExportWindow').show();
			},
			hidden: false
		},
		EvnPLDispOrpSearchAction: {
			text: langs('Карта диспансеризации несовершеннолетнего - 1 этап: Поиск'),
			tooltip: langs('Карта диспансеризации несовершеннолетнего - 1 этап: Поиск'),
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
			text: langs('Карта диспансеризации несовершеннолетнего - 2 этап: Поиск'),
			tooltip: langs('Карта диспансеризации несовершеннолетнего - 2 этап: Поиск'),
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
			text: langs('Диспансеризация 14-летних подростков: Поиск'),
			tooltip: langs('Диспансеризация 14-летних подростков: Поиск'),
			iconCls : 'dopdisp-teens-search16',
			handler: function()
			{
				getWnd('swEvnPLDispTeen14SearchWindow').show();
			},
			hidden: false
		},
		EvnPLDispTeen14StreamInputAction: {
			text: langs('Диспансеризация 14-летних подростков: Поточный ввод'),
			tooltip: langs('Диспансеризация 14-летних подростков: Поточный ввод'),
			iconCls : 'dopdisp-teens-stream16',
			handler: function()
			{
				getWnd('swEvnPLDispTeen14SearchWindow').show({mode: 'stream'});
			},
			hidden: false
		},
		/*EvnPLDispSomeAdultSearchAction: {
			text: langs('Диспансеризация отдельных групп взрослого населения: Поиск'),
			hidden: !(getGlobalOptions().region && getRegionNick() == 'ufa'),
			tooltip: langs('Диспансеризация отдельных групп взрослого населения: Поиск'),
			iconCls : 'dopdisp-teens-search16',
			handler: function()
			{
				getWnd('swEvnPLDispSomeAdultSearchWindow').show();
			}
		},
		EvnPLDispSomeAdultStreamInputAction: {
			text: langs('Диспансеризация отдельных групп взрослого населения: Поточный ввод'),
			hidden: !(getGlobalOptions().region && getRegionNick() == 'ufa'),
			tooltip: langs('Диспансеризация отдельных групп взрослого населения: Поточный ввод'),
			iconCls : 'dopdisp-teens-stream16',
			handler: function()
			{
				getWnd('swEvnPLDispSomeAdultStreamInputWindow').show();
			}
		},*/
		ReestrsViewAction: {
			text: langs('Реестры счетов'),
			tooltip: langs('Реестры счетов'),
			iconCls : 'service-reestrs16',
			handler: function()
			{
				Ext.Msg.alert(langs('Сообщение'), langs('Данный модуль пока недоступен!'));
			}
		},
		DrugRequestEditAction: {
			text: langs('Заявка на лекарственные средства: Ввод'),
			tooltip: langs('Работа со заявкой на лекарственные средства'),
			iconCls : 'x-btn-text',
			handler: function()
			{
				getWnd('swDrugRequestEditForm').show({mode: 'edit'});
			},
			hidden:(IS_DEBUG!=1)
		},
		DrugRequestViewAction: {
			text: langs('Заявка на ЛС по общетерапевтической группе заболеваний'),
			tooltip: langs('Заявки на ЛС по общетерапевтической группе заболеваний'),
			iconCls : 'drug-request16',
			handler: function()
			{
				getWnd('swDrugRequestViewForm').show();
			},
			hidden: (getRegionNick()!='perm')
		},
		/*NewDrugRequestViewAction: {
			text: (getGlobalOptions().region && getRegionNick()=='saratov')?langs('Заявка на лекарственные средства'):langs('Заявка на ЛС по особым группам заболеваний'),
			tooltip: (getGlobalOptions().region && getRegionNick()=='saratov')?langs('Заявка на лекарственные средства'):langs('Заявка на ЛС по особым группам заболеваний'),
			iconCls : 'drug-request16',
			handler: function()
			{
				getWnd('swNewDrugRequestViewForm').show();
			},
			hidden: (getRegionNick() == 'saratov'||getRegionNick() == 'pskov')
		},*/
		OrgFarmacyViewAction: {
			text: MM_DLO_OFVIEW,
			tooltip: langs('Работа с просмотром и редактированием аптек'),
			iconCls : 'farmview16',
			handler: function()
			{
				getWnd('swOrgFarmacyViewWindow').show();
			},
			hidden : !isAdmin
		},
		OstAptekaViewAction: {
			text: MM_DLO_MEDAPT,
			tooltip: langs('Работа с остатками медикаментов по аптекам'),
			iconCls : 'drug-farm16',
			handler: function()
			{
				getWnd('swDrugOstatByFarmacyViewWindow').show();
			}
		},
		OstSkladViewAction: {
			text: MM_DLO_MEDSKLAD,
			tooltip: langs('Работа с остатками медикаментов на аптечном складе'),
			iconCls : 'drug-sklad16',
			handler: function()
			{
				getWnd('swDrugOstatBySkladViewWindow').show();
			}
		},
		OstDrugViewAction: {
			text: MM_DLO_MEDNAME,
			tooltip: langs('Работа с остатками медикаментов по наименованию'),
			iconCls : 'drug-name16',
			handler: function()
			{
				getWnd('swDrugOstatViewWindow').show();
			}
		},
		ReportStatViewAction: {
			text: langs('Статистическая отчетность'),
			tooltip: langs('Статистическая отчетность'),
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
			text: langs('Тест (только на тестовом)'),
			tooltip: langs('Тест'),
			iconCls : 'test16',
			hidden:((IS_DEBUG!=1) || (getRegionNick() == 'saratov')),
			handler: function()
			{
				getWnd('swTestEventsWindow').show();
			}
		},
		TemplatesWindowTestAction: {
			text: langs('Тест шаблонов'),
			tooltip: langs('Тест шаблонов'),
			iconCls : 'test16',
			hidden: true,
			handler: function()
			{
			}
		},
		TemplatesEditWindowAction: {
			text: langs('Редактор шаблонов'),
			tooltip: langs('Редактор шаблонов'),
			iconCls : 'test16',
			hidden: true,
			handler: function()
			{
			}
		},
		TemplateRefValuesOpenAction: {
			text: langs('База референтных значений'),
			tooltip: langs('Редактор референтных значений'),
			iconCls : 'test16',
			hidden: !isAdmin,
			handler: function()
			{
				getWnd('swTemplateRefValuesViewWindow').show();
			}
		},
		GlossarySearchAction: {
			text: langs('Глоссарий'),
			tooltip: langs('Глоссарий'),
			iconCls : 'glossary16',
			//hidden: false,
			hidden: getRegionNick().inlist(['saratov','by']),
			handler: function()
			{
				getWnd('swGlossarySearchWindow').show();
			}
		},
		ReportDBStructureAction: {
			text: langs('Структура БД'),
			tooltip: langs('Структура БД'),
			iconCls : 'test16',
			hidden:((!isAdmin) && (!IS_DEBUG)),
			handler: function()
			{
				getWnd('swReportDBStructureOptionsWindow').show();
			}
		},
		UserProfileAction: {
			text: langs('Мой профиль'),
			tooltip: langs('Профиль пользователя'),
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
			text: langs('Вызов справки'),
			tooltip: langs('Помощь по программе'),
			iconCls : 'help16',
			handler: function()
			{
				ShowHelp(langs('Содержание'));
			}
		},
		PromedForum: {
			text: langs('Форум поддержки'),
			iconCls: 'support16',
			xtype: 'tbbutton',
			handler: function() {
				window.open(ForumLink);
			}
		},		
		swShowTestWindowAction: {
			text: langs('Тестовое окно'),
			tooltip: langs('Открыть Тестовое окно'),
			iconCls : 'test16',
			handler: function() {
				//getWnd('swTestWindow').show();
				getWnd('swWorkPlaceWindow').show();
			},
			hidden: !isAdmin || !isDebug()
		},
		PromedAbout: {
			text: langs('О программе'),
			tooltip: langs('Информация о программе'),
			iconCls : 'promed16',
			testId: 'mainmenu_help_about',
			handler: function()
			{
				getWnd('swAboutWindow').show();
			}
		},
		PromedExit: {
			text:langs('Выход'),
			iconCls: 'exit16',
			handler: function()
			{
					sw.swMsg.show({
							title: langs('Подтвердите выход'),
							msg: langs('Вы действительно хотите выйти?'),
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
			text: langs('Конвертация полей'),
			tooltip: langs('Конвертация'),
			iconCls : 'eph16',
			handler: function()
			{
				getWnd('swConvertEditWindow').show();
			},
			hidden:((IS_DEBUG!=1) || (getRegionNick().inlist(['saratov', 'by'])))
		},
		swLdapAttributeChangeAction:{
			text: langs('Замена атрибута в LDAP'),
			tooltip: langs('Замена атрибута в LDAP'),
			iconCls : 'eph16',
			handler: function()
			{
				getWnd('swLdapAttributeChangeWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		swImportSMPCardsTest:{
			text: langs('Тест импорта карт СМП'),
			tooltip: langs('Тест импорта карт СМП'),
			iconCls : 'eph16',
			handler: function()
			{
				getWnd('swImportSMPCardsTestWindow').show();
			},
			hidden: (!isSuperAdmin() || getRegionNick().inlist(['by']))
		},
		swDicomViewerAction:{
			text: langs('Просмотрщик Dicom'),
			tooltip: langs('Просмотрщик Dicom'),
			iconCls : 'eph16',
			handler: function()
			{
				getWnd('swDicomViewerWindow').show();
			},
			hidden: (IS_DEBUG!=1 || !isSuperAdmin())
		},
		TestAction: {
			text: langs('Тест (только на тестовом)'),
			tooltip: langs('Тест'),
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
			hidden: ((IS_DEBUG!=1) || (getRegionNick() == 'saratov'))
		},
		Test2Action: {
			text: langs('Получить с анализатора (только на тестовом)'),
			tooltip: langs('Тест'),
			iconCls : 'eph16',
			handler: function()
			{
				//getWnd('swPersonEPHForm').show({Person_id: 499527, Server_id: 10, PersonEvn_id: 104170589});
				getWnd('swTestLoadEditWindow').show();
			},
			hidden: ((IS_DEBUG!=1) || (getRegionNick() == 'saratov'))
		},
		/*MedPersonalPlaceAction: {
			text: langs('Медицинский персонал: места работы (старый ЕРМП)'),
			tooltip: langs('Медицинский персонал: места работы (старый ЕРМП)'),
			iconCls : 'staff16',
			hidden: ((!isAdmin && (getRegionNick() != 'ufa')) || (getRegionNick() == 'pskov')),
			handler: function()
			{
				getWnd('swMedPersonalViewWindow').show();
			}
		},*/
		MedWorkersAction: {
			text: langs('Медработники'),
			tooltip: langs('Медработники'),
			iconCls : 'staff16',
			hidden : ((getRegionNick() == 'ufa') || (!isAdmin && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ))),
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'MedWorker', main_center_panel);
			}
		},
		/*MedPersonalSearchAction: {
			text: langs('Медицинский персонал: Просмотр (старый ЕРМП)'),
			tooltip: langs('Медицинский персонал: Просмотр (старый ЕРМП)'),
			iconCls : 'staff16',
			hidden : !MP_NOT_ERMP,
			handler: function()
			{
				getWnd('swMedPersonalSearchWindow').show();
			}
		},*/
		swLgotTreeViewAction: {
			text: langs('Регистр льготников: Список'),
			tooltip: langs('Просмотр льгот по категориям'),
			iconCls : 'lgot-tree16',
			handler: function()
			{
				getWnd('swLgotTreeViewWindow').show();
			}
		},
		swAttachmentDemandAction: {
			text: langs('Заявления на прикрепление МО'),
			tooltip: langs('Просмотр и редактирование заявлений на прикрепление к МО'),
			iconCls : 'attach-demand16',
			hidden : !isAdmin,
			handler: function()
			{
				getWnd('swAttachmentDemandListWindow').show();
			}
		},
		swChangeSmoDemandAction: {
			text: langs('Заявления на прикрепление: СМО'),
			tooltip: langs('Просмотр и редактирование заявлений на прикрепление к СМО'),
			iconCls : 'attach-demand16',
			hidden : !isAdmin,
			handler: function()
			{
				getWnd('swChangeSmoDemandListWindow').show();
			}
		},
		swUsersTreeViewAction: {
			text: langs('Пользователи'),
			tooltip: langs('Просмотр и редактирование пользователей'),
			iconCls : 'users16',
			hidden: !getGlobalOptions().superadmin && !isLpuAdmin(),
			handler: function()
			{
				getWnd('swUsersTreeViewWindow').show();
			}
		},
		swGroupsViewAction: {
			text: langs('Группы'),
			tooltip: langs('Просмотр и редактирование групп'),
			iconCls : 'users16',
			hidden: !isSuperAdmin(),
			handler: function()
			{
				getWnd('swGroupViewWindow').show();
			}
		},
		swOptionsViewAction: {
			text: langs('Настройки'),
			tooltip: langs('Просмотр и редактирование настроек'),
			iconCls : 'settings16',
			handler: function()
			{
				getWnd('swOptionsWindow').show();
			}
		},
		swNumeratorAction: {
			text: langs('Нумераторы'),
			tooltip: langs('Просмотр и редактирование нумераторов'),
			iconCls : 'create-schedule16',
			handler: function()
			{
				getWnd('swNumeratorListWindow').show();
			}
		},
		swPersonSearchAction: {
			text: langs('Человек: поиск'),
			tooltip: langs('Поиск людей'),
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
											if (callback_data.PersonData.Lpu_Nick !== undefined) {
												record.set('Lpu_Nick', callback_data.PersonData.Lpu_Nick);
											}
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
			text: langs('Обновление регистров'),
			tooltip: langs('Обновление регистров'),
			iconCls: 'patient-search16',
			hidden: (!getGlobalOptions().superadmin || getRegionNick().inlist(['by'])),
			handler: function()
			{
				getWnd('swImportWindow').show();
			}
		},
		swTemperedDrugs: {
			text: langs('Импорт отпущенных ЛС'),
			tooltip: langs('Отпущенные ЛС'),
			iconCls: 'adddrugs-icon16',
			handler: function()
			{
				getWnd('swTemperedDrugsWindow').show();
			},
			//hidden: (getRegionNick() != 'ufa')
			hidden: !(getRegionNick() == 'ufa' && isSuperAdmin())
		},
		swPersonPeriodicViewAction: {
			text: langs('Тест периодик'),
			tooltip: langs('Тест периодик'),
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
			text: langs('Рабочее место лаборанта'),
			tooltip: langs('Рабочее место лаборанта'),
			iconCls: 'lab-assist16',
			//iconCls: 'patient-search16',
			hidden: !isAdmin,
			handler: function()
			{
				getWnd('swAssistantWorkPlaceWindow').show();
			}
		},*/
		swSelectWorkPlaceAction: {
			text: langs('Выбор АРМ по умолчанию'),
			tooltip: langs('Выбор АРМ по умолчанию'),
			iconCls: 'lab-assist16',
			//iconCls: 'patient-search16',
			hidden: !isAdmin,
			handler: function()
			{
				getWnd('swSelectWorkPlaceWindow').show();
			}
		},
		
		swRegistrationJournalSearchAction: {
			text: langs('Лабораторные исследования: поиск'),
			tooltip: langs('Журнал лабораторных исследований'),
			//iconCls: 'patient-search16',
			hidden: (IS_DEBUG!=1 || !isSuperAdmin()),
			handler: function()
			{
				getWnd('swRegistrationJournalSearchWindow').show();
			}
		},
		swLpuSelectAction: {
			text: langs('Выбор МО'),
			tooltip: langs('Выбор МО'),
			iconCls: 'lpu-select16',
			handler: function()
			{
				sw.WindowMgr.each(function(wnd){
					if ( wnd.isVisible() )
					{
						wnd.hide();
					}
				});
				getWnd('swSelectLpuWindow').show({});
			},
			hidden: !getGlobalOptions().superadmin && !isUserGroup(['medpersview', 'ouzuser', 'ouzadmin', 'ouzchief']) && !(getGlobalOptions().lpu && getGlobalOptions().lpu.length>1) // проверяем так же просмотр медперсонала и количество МО у пользователя
		},

		swDivCountAction: {
			text: langs('Количество html-элементов'),
			tooltip: langs('Посчитать текущее количество html-элементов'),
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
			text: langs('Параметры системы'),
			tooltip: langs('Просмотр и изменение общих настроек'),
			iconCls: 'settings-global16',
			handler: function()
			{
				getWnd('swGlobalOptionsWindow').show();
			},
			hidden: !getGlobalOptions().superadmin //((IS_DEBUG!=1) || !getGlobalOptions().superadmin)
		},
		// Все прочие акшены
		swPregCardViewAction: {
			text: langs('Индивидуальная карта беременной: Просмотр'),
			tooltip: langs('Индивидуальная карта беременной: Просмотр'),
			iconCls: 'pol-preg16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin && !isTestLpu
		},
		swPregCardFindAction: {
			text: langs('Индивидуальная карта беременной: Поиск'),
			tooltip: langs('Индивидуальная карта беременной: Поиск'),
			iconCls: 'pol-pregsearch16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin && !isTestLpu
		},
		swRegChildOrphanDopDispStreamAction: {
			text: langs('Регистр детей-сирот: Поточный ввод'),
			tooltip: langs('Регистр детей-сирот: Поточный ввод'),
			iconCls: 'orphdisp-stream16',
			handler: function()
			{
				getWnd('swPersonDispOrpSearchWindow').show({mode: 'stream'});
			},
			hidden: false//!isAdmin
		},
		swRegChildOrphanDopDispFindAction: {
			text: langs('Регистр детей-сирот (до 2013г.): Поиск'),
			tooltip: langs('Регистр детей-сирот: Поиск'),
			iconCls: 'orphdisp-search16',
			handler: function()
			{
				getWnd('swPersonDispOrpSearchWindow').show();
			},
			hidden: false//!isAdmin
		},
		swEvnPLChildOrphanDopDispStreamAction: {
			text: langs('Талон по диспансеризации детей-сирот: Поточный ввод'),
			tooltip: langs('Талон по диспансеризации детей-сирот: Поточный ввод'),
			iconCls: 'orphdisp-epl-stream16',
			handler: function()
			{
				getWnd('swEvnPLDispOrpSearchWindow').show({mode: 'stream'});
			},
			hidden: false
		},
		swEvnPLChildOrphanDopDispFindAction: {
			text: langs('Талон по диспансеризации детей-сирот (до 2013г.): Поиск'),
			tooltip: langs('Талон по диспансеризации детей-сирот: Поиск'),
			iconCls: 'orphdisp-epl-search16',
			handler: function()
			{
				getWnd('swEvnPLDispOrpSearchWindow').show();
			},
			hidden: false
		},
		swEvnDtpWoundViewAction: {
			text: langs('Извещения ДТП о раненом: Просмотр'),
			tooltip: langs('Извещения ДТП о раненом: Просмотр'),
			iconCls: 'stac-accident-injured16',
			handler: function()
			{
				getWnd('swEvnDtpWoundWindow').show();
			},
			hidden: false
		},
		swEvnDtpDeathViewAction: {
			text: langs('Извещения ДТП о скончавшемся: Просмотр'),
			tooltip: langs('Извещения ДТП о скончавшемся: Просмотр'),
			iconCls: 'stac-accident-dead16',
			handler: function()
			{
				getWnd('swEvnDtpDeathWindow').show();
			},
			hidden: false
		},
		swMedPersonalWorkPlaceAction: {
			text: langs('<b>Рабочее место</b>'),
			title: langs('АРМ'),
			tooltip: langs('Рабочее место врача'),
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
			text: langs('Рабочее место постовой медсестры'),
			tooltip: langs('Рабочее место постовой медсестры'),
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
			text: langs('Журнал назначений'),
			tooltip: langs('Журнал назначений'),
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
			text: langs('Журнал медицинских мероприятий'),
			tooltip: langs('Журнал медицинских мероприятий'),
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
			text: langs('Рабочее место ВК'),
			tooltip: langs('Рабочее место ВК'),
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
			text: langs('Рабочее место МСЭ'),
			tooltip: langs('Рабочее место МСЭ'),
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
			text: langs('Журнал регистрации направлений'),
			tooltip: langs('Журнал регистрации направлений'),
			iconCls: 'pol-directions16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//(!isAdmin || getRegionNick().inlist(['by']))
		},
		
		swPersonCardAttachListAction: {
			text: langs('РПН: Заявления о выборе МО'),
			tooltip: langs('РПН: Заявления о выборе МО'),
			hidden: getRegionNick().inlist(['by']),
			iconCls: '', // нужна иконка
			handler: function() {
				getWnd('swPersonCardAttachListWindow').show();
			}
		},
		
		swMSJobsAction: {
			text: langs('Управление задачами MSSQL'),
			tooltip: langs('Управление задачами MSSQL'),
			iconCls: 'sql16',
			handler: function()
			{
				getWnd('swMSJobsWindow').show();
			},
			hidden: !isAdmin
		},
		swXmlTemplateDebug: {
			text: langs('Конвертация Xml-документов'),
			tooltip: langs('Проверка и правки данных шаблонов и документов'),
			iconCls: 'test16',
			handler: function()
			{
				window.open('/?c=EvnXmlConvert&m=index');
			},
			hidden: !isAdmin
		},
		loadLastObjectCode: {
			text: langs('Обновить последний JS-файл'),
			tooltip: langs('Обновить последний JS-файл'),
			iconCls: 'test16',
			handler: function() {
				if (sw.codeInfo) {
					loadJsCode({objectName: sw.codeInfo.lastObjectName, objectClass: sw.codeInfo.lastObjectClass});
				}
			},
			hidden: !isAdmin//true //!isAdmin && !IS_DEBUG
		},
		MessageAction: {
			text: langs('Сообщения'),
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
		swTreatmentStreamInputAction: {
			text: langs('Регистрация обращений: Поточный ввод'),
			tooltip: langs('Регистрация обращений: Поточный ввод'),
			iconCls: 'petition-stream16',
			handler: function() {
				getWnd('swTreatmentStreamInputWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swTreatmentSearchAction: {
			text: langs('Регистрация обращений: Поиск'),
			tooltip: langs('Регистрация обращений: Поиск'),
			iconCls: 'petition-search16',
			handler: function() {
				getWnd('swTreatmentSearchWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swTreatmentReportAction: {
			text: langs('Регистрация обращений: Отчетность'),
			tooltip: langs('Регистрация обращений: Отчетность'),
			iconCls: 'petition-report16',
			handler: function() {
				getWnd('swTreatmentReportWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swEvnPSStreamAction: {
			text: langs('Карта выбывшего из стационара: Поточный ввод'),
			tooltip: langs('Карта выбывшего из стационара: Поточный ввод'),
			iconCls: 'stac-psstream16',
			handler: function()
			{
				getWnd('swEvnPSStreamInputWindow').show();
			},
			hidden: false //!isAdmin && !isTestLpu && IS_DEBUG != 1
		},
		swEvnPSFindAction: {
			text: langs('Карта выбывшего из стационара: Поиск'),
			tooltip: langs('Карта выбывшего из стационара: Поиск'),
			iconCls: 'stac-pssearch16',
			handler: function()
			{
				getWnd('swEvnPSSearchWindow').show();
			},
			hidden: false //!isAdmin && !isTestLpu && IS_DEBUG != 1
		},
		swSuicideAttemptsEditAction: {
			text: langs('Суицидальные попытки: Ввод'),
			tooltip: langs('Суицидальные попытки: Ввод или просмотр'),
			iconCls: 'suicide-edit16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin && !isTestLpu
		},
		swSuicideAttemptsFindAction: {
			text: langs('Суицидальные попытки: Поиск'),
			tooltip: langs('Суицидальные попытки: Поиск'),
			iconCls: 'suicide-search16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin && !isTestLpu
		},
		
		/*swMedPersonalWorkPlaceStacAction: {
			text: langs('Рабочее место врача'),
			tooltip: langs('Рабочее место врача'),
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
			text: langs('Журнал направлений'),
			tooltip: langs('Журнал направлений на госпитализацию'),
			iconCls: 'pol-directions16',
			handler: function()
			{
				getWnd('swEvnDirectionJournalWindow', {params: { userMedStaffFact: null}}).show();
			},
			hidden: false
		},
		swEvnUslugaParStreamAction: {
			text: langs('Выполнение параклинической услуги: Поточный ввод'),
			tooltip: langs('Выполнение параклинической услуги: Поточный ввод'),
			iconCls: 'par-serv-stream16',
			handler: function()
			{
				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swEvnUslugaParStreamInputWindow').show();
			},
			hidden: !getRegionNick().inlist(['perm', 'ekb', 'krym', 'kareliya', 'buryatiya', 'vologda', 'adygeya', 'yaroslavl', 'yakutiya'])
		},
		swEvnUslugaParFindAction: {
			text: langs('Выполнение параклинической услуги: Поиск'),
			tooltip: langs('Выполнение параклинической услуги: Поиск'),
			iconCls: 'par-serv-search16',
			handler: function()
			{
				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swEvnUslugaParSearchWindow').show();
			},
			hidden: false
		},
		swEvnLabSampleDefectViewAction: {
			text: langs('Журнал отбраковки'),
			tooltip: langs('Журнал отбраковки'),
			iconCls: 'lab-assist16',
			handler: function()
			{
				getWnd('swEvnLabSampleDefectViewWindow').show();
			},
			hidden: false
		},
		swEvnPLStomStreamAction: {
			text: langs('Талон амбулаторного пациента: Поточный ввод'),
			tooltip: langs('Талон амбулаторного пациента: Поточный ввод'),
			iconCls: 'stom-stream16',
			handler: function()
			{
				getWnd('swEvnPLStomStreamInputWindow').show();
			}
		},
		swEvnPLStomSearchAction: {
			text: langs('Талон амбулаторного пациента: Поиск'),
			tooltip: langs('Талон амбулаторного пациента: Поиск'),
			iconCls : 'stom-search16',
			handler: function()
			{
				getWnd('swEvnPLStomSearchWindow').show();
			},
			hidden: false
		},
		swUslugaPriceListAction: {
			text: langs('Стоматологические услуги МО (Справочник УЕТ)'),
			tooltip: langs('Стоматологические услуги МО (Справочник УЕТ)'),
			iconCls: 'stom-uslugi16',
			handler: function() {
				getWnd('swUslugaPriceListViewWindow').show();
			},
			hidden: false
		},
		swMedSvidBirthAction: {
			text: langs('Свидетельства о рождении'),
			tooltip: langs('Свидетельства о рождении'),
			iconCls: 'svid-birth16',
			handler: function()
			{
				getWnd('swMedSvidBirthStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidDeathAction: {
			text: langs('Свидетельства о смерти'),
			tooltip: langs('Свидетельства о смерти'),
			iconCls: 'svid-death16',
			handler: function()
			{
				getWnd('swMedSvidDeathStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidPDeathAction: {
			text: langs('Свидетельства о перинатальной смерти'),
			tooltip: langs('Свидетельства о перинатальной смерти'),
			iconCls: 'svid-pdeath16',
			handler: function()
			{
				getWnd('swMedSvidPntDeathStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidPrintAction: {
			text: langs('Печать бланков свидетельств'),
			tooltip: langs('Печать бланков свидетельств'),
			iconCls: 'svid-blank16',
			handler: function()
			{
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swMedSvidSelectSvidType').show();
			},
			hidden: false
		},
		swTestAction: {
			text: langs('Тест'),
			tooltip: langs('Тест'),
			iconCls: '',
			handler: function()
			{
				// 
				Ext.Ajax.request({
					failure: function(response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
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
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
						}
					},
					params: {
						Polis_Ser: langs('КС'),
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
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
						}
					},
					params: {
						Person_SurName: langs('ПЕТУХОВ'),
						Person_FirName: langs('ИВАН'),
						Person_SecName: langs('СЕРГЕЕВИЧ'),
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
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
						}
					},
					params: {
						Person_SurName: langs('КАТАЕВ'),
						Person_FirName: langs('АНДРЕЙ'),
						Person_Age: 46,
						KLStreet_Name: langs('ШКОЛЬНАЯ'),
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
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
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
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
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
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса к серверу'));
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
			hidden: ((!isAdmin) || (getRegionNick() == 'saratov'))
		},
		swBarcodeByEvnReceptIdViewAction: {
			text: langs('Получение бинарного кода для рецепта'),
			tooltip: langs('Получение бинарного кода по идентификатору рецепта'),
			iconCls: '',
			handler: function() {
				getWnd('swBarcodeByEvnReceptIdViewWindow').show();
			}
		},
		swRegDeceasedPeopleAction: {
			text: langs('Сведения об умерших гражданах'),
			tooltip: langs('Сведения об умерших гражданах (регистр)'),
			iconCls: 'regdead16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},
		swMedicationSprAction: {
			text: langs('Справочник: Медикаменты'),
			tooltip: langs('Справочник: Медикаменты'),
			iconCls: 'farm-drugs16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swContractorsSprAction: {
			text: langs('Справочник: Контрагенты'),
			tooltip: langs('Справочник: Контрагенты'),
			iconCls: 'farm-partners16',
			handler: function()
			{
				getWnd('swContragentViewWindow').show();
			},
			hidden: false
		},
		swDokNakAction: {
			text: langs('Приходные накладные'),
			tooltip: langs('Приходные накладные'),
			iconCls: 'doc-nak16',
			handler: function()
			{
				getWnd('swDokNakViewWindow').show();
			},
			hidden: false
		},
		swDokUchAction: {
			text: langs('Документы учета медикаментов'),
			tooltip: langs('Документы учета медикаментов'),
			iconCls: 'doc-uch16',
			handler: function()
			{
				getWnd('swDokUcLpuViewWindow').show();
			},
			hidden: false
		},
		swAktSpisAction: {
			text: langs('Акты списания медикаментов'),
			tooltip: langs('Акты списания медикаментов'),
			iconCls: 'doc-spis16',
			handler: function()
			{
				getWnd('swDokSpisViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swDokOstAction: {
			text: langs('Документы ввода остатков'),
			tooltip: langs('Документы ввода остатков'),
			iconCls: 'doc-ost16',
			handler: function()
			{
				getWnd('swDokOstViewWindow').show();
			},
			hidden: false
		},
		swInvVedAction: {
			text: langs('Инвентаризационные ведомости'),
			tooltip: langs('Инвентаризационные ведомости'),
			iconCls: 'farm-inv16',
			handler: function()
			{
				getWnd('swDokInvViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swMedOstatAction: {
			text: langs('Остатки медикаментов'),
			tooltip: langs('Остатки медикаментов'),
			iconCls: 'farm-ostat16',
			handler: function()
			{
				getWnd('swMedOstatViewWindow').show();
			},
			hidden: false
		},
		EvnReceptProcessAction: {
			text: langs('Обработка рецептов'),
			tooltip: langs('Обработка рецептов'),
			iconCls : 'receipt-process16',
			handler: function() {
				getWnd('swEvnReceptProcessWindow').show();
			},
			hidden: !isAdmin
		},
		EvnRPStreamInputAction: {
			text: langs('Потоковое отоваривание рецептов'),
			tooltip: langs('Потоковое отоваривание рецептов'),
			iconCls : 'receipt-streamps16',
			handler: function() {
				getWnd('swEvnRPStreamInputWindow').show();
			},
			hidden: !isAdmin
		},
		EvnReceptTrafficBookViewAction: {
			text: langs('Журнал движения рецептов'),
			tooltip: langs('Журнал движения рецептов'),
			iconCls : 'receipt-delay16',
			handler: function() {
				getWnd('swEvnReceptTrafficBookViewWindow').show();
			},
			hidden: !isAdmin
		},
		KerRocordBookAction: {
			text: langs('Врачебная комиссия'),
			tooltip: langs('Врачебная комиссия'),
			iconCls: 'med-commission16',
			handler: function()
			{
				getWnd('swClinExWorkSearchWindow').show();
			}, 
			hidden: (!isAdmin || getRegionNick().inlist(['by']))
		},
		swRegistrationCallAction: {
			text: langs('Регистрация вызова'),
			tooltip: langs('Регистрация вызова'),
			iconCls: '',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swCardCallViewAction: {
			text: langs('Карта вызова: Просмотр'),
			tooltip: langs('Карта вызова: Просмотр'),
			iconCls: 'ambulance_add16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swCardCallFindAction: {
			text: langs('Карты СМП: Поиск'),
			tooltip: langs('Карты вызова СМП: Поиск'),
			iconCls: 'ambulance_search16',
			handler: function()
			{
				getWnd('swCmpCallCardSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['by'])
		},
		swCardCallStreamAction: {
			text: langs('Карты СМП: Поточный ввод'),
			tooltip: langs('Карты вызова СМП: Поточный ввод'),
			iconCls: 'ambulance_search16',
			handler: function()
			{
				//пытаемся запустить новую поточную карту
				getWnd('swCmpCallCardNewCloseCardWindow').show({
					action: 'stream',
					formParams: {
						ARMType: 'smpadmin'			   
					},
					callback: function(data) {
						if ( !data || !data.CmpCloseCard_id ) {
							return false;
						}
					}
				});
				
				//временно закомментил, вдруг что-то пойдет не так
				//getWnd('swCmpCallCardCloseStreamWindow').show();
			},
			hidden: (!getRegionNick().inlist(['pskov', 'astra', 'kareliya']))
		},
//                 *
//                 *Закоментировал Тагир
//                 *
//		swInjectionStreamAction: {
//			text: 'Прививки: Поточный ввод',
//			tooltip: 'Прививки: Поточный ввод',
//			iconCls: 'inj-stream16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swInjectionFindAction: {
//			text: 'Прививки: Поиск',
//			tooltip: 'Прививки: Поиск',
//			iconCls: 'inj-search16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swMedicalTapStreamAction: {
//			text: 'Медотводы: Поточный ввод',
//			tooltip: 'Медотводы: Поточный ввод',
//			iconCls: 'mreject-stream16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swMedicalTapFindAction: {
//			text: 'Медотводы: Поиск',
//			tooltip: 'Медотводы: Поиск',
//			iconCls: 'mreject-search16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swSerologyStreamAction: {
//			text: 'Серология: Поточный ввод',
//			tooltip: 'Серология: Поточный ввод',
//			iconCls: 'imm-ser-stream16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swSerologyFindAction: {
//			text: 'Серология: Поиск',
//			tooltip: 'Серология: Поиск',
//			iconCls: 'imm-ser-search16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swAbsenceBakAction: {
//			text: 'Отсутствие бакпрепаратов',
//			tooltip: 'Отсутствие бакпрепаратов',
//			iconCls: 'imm-bakabs16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//		swCurrentPlanAction: {
//			text: 'Текущее планирование вакцинации',
//			tooltip: 'Текущее планирование вакцинации',
//			iconCls: 'vac-plan16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isAdmin
//		},
//                 *
//                 *Закоментировал Тагир
//                 *
			// tagir start
			amm_JournalsVac: {
			text: langs('Просмотр журналов вакцинации'),
			tooltip: langs('Просмотр журналов вакцинации'),
			iconCls: 'vac-plan16',
			handler: function()
			{
							if (vacLpuContr())  // Если это 2-я детская
									getWnd('amm_mainForm').show();
								else
									sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));

//              getWnd('amm_mainForm').show();
			  //var loadMask = new Ext.LoadMask(Ext.getCmp('journalsVaccine'), { msg: LOAD_WAIT });
			  //loadMask.show();
			},
			hidden: false // !isAdmin
		},
						
				   ammOnkoCtrl_ProfileJurnal: {
			text: langs('Журнал анкетирования'),
			tooltip: langs('Журнал анкетирования'),
			iconCls: 'stac-psstream16',
			hidden: (!getRegionNick().inlist(['ufa', 'perm', 'kz'])),//Улучшение #156968
			handler: function()
			 {
							var record = {'Lpu_id' : getGlobalOptions().lpu_id};
							getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
						 }   
					},
							
					 ammOnkoCtrl_ReportSetZNO: {
			text: langs('Отчет "Анализ выявленных ЗНО"'),
			tooltip: langs('Отчет "Анализ выявленных ЗНО"'),
			iconCls: 'stac-psstream16',
			hidden: (getRegionNick() != 'ufa'),
			//hidden: !isAdmin,
			handler: function()
			 {
							var record = {'Lpu_id' : getGlobalOptions().lpu_id}; //amm_vacReport_5
						   getWnd('amm_OnkoCtrl_ReportSetZNO').show(record);
//                           getWnd('amm_vacReport_5').show();
						}
				   },
						   
				ammOnkoCtrl_ReportMonutoring: {
			text: langs('Отчет "Мониторинг реализации системы "Онкоконтроль"'),
			tooltip: langs('Отчет "Мониторинг реализации системы "Онкоконтроль"'),
			iconCls: 'stac-psstream16',
						hidden: (getRegionNick() != 'ufa'),
									//hidden: !isAdmin,
									handler: function()
									 {
										var record = {'Lpu_id' : getGlobalOptions().lpu_id}; 
									   getWnd('amm_OnkoCtrl_ReportMonitoring').show(record);
									   //getWnd('amm_OnkoCtrl_ReportSetZNO').show(record);
								   }
							   },           

		
		ammStartVacFormPlan: {
			text: langs('Планирование вакцинации'),
			tooltip: langs('Планирование вакцинации'),
			iconCls: 'vac-plan16',
			hidden: !isAdmin&& !isLpuAdmin(),
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская  .js
									getWnd('amm_StartVacPlanForm').show();
									//getWnd('amm_SprOtherVacSchemeEditFotm').show();
				else
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			}
			//, hidden: false // !isAdmin
		},

		ammvacListTasks: {
			text: langs('Список заданий'),
			tooltip: langs('Список заданий'),
			iconCls: 'vac-plan16',
			hidden: !isAdmin&& !isLpuAdmin(),

			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_ListTaskForm').show();
				else
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			}
			//, hidden: false // !isAdmin
		},
		ammvacReport_5: {
			text: langs('Отчет ф. №5'),
			tooltip: langs('Отчет ф. №5'),
			iconCls: 'vac-plan16',
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_vacReport_5').show();
				else
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: getRegionNick() == 'kz'
		},
		ammSprVaccineTypeForm: {
			text: langs('Справочник прививок'),
			tooltip: langs('Справочник прививок'),
			iconCls: 'vac-plan16',
			handler: function()
			{
				getWnd('amm_SprVaccineTypeForm').show();
			}
		},
		ammSprVaccine: {
			text: langs('Справочник вакцин'),
			tooltip: langs('Справочник вакцин'),
			iconCls: 'vac-plan16',
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_SprVaccineForm').show();
				else
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: false // !isAdmin
		},
		ammSprNacCal: {
			text: langs('Национальный календарь прививок'),
			tooltip: langs('Национальный календарь прививок'),
			iconCls: 'vac-plan16',
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_SprNacCalForm').show();
				else
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: false // !isAdmin
		},
		ammVacPresence: {
			text: langs('Наличие вакцин'),
			tooltip: langs('Наличие вакцин'),
			iconCls: 'vac-plan16',
			handler: function()
			{
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_PresenceVacForm').show();
				else
					sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: false // !isAdmin
		},
		// End  tagir
		swLpuPassportAction: {
			text: langs('Паспорт МО'),
			tooltip: langs('Паспорт МО'),
			iconCls: 'lpu-passport16',
			handler: function()
			{
				getWnd('swLpuPassportEditWindow').show({
						action: 'edit',
						Lpu_id: getGlobalOptions().lpu_id
				});
			},
			hidden: !isSuperAdmin() && !isLpuAdmin() && getGlobalOptions().groups.toString().indexOf('MPCModer') == -1 && getGlobalOptions().groups.toString().indexOf('PMUspec') == -1
		},
		swOrgPassportAction: {
			text: langs('Паспорт организации'),
			tooltip: langs('Паспорт организации'),
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
			text: langs('Услуги МО'),
			tooltip: langs('Услуги МО'),
			iconCls: 'lpu-services-lpu16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swUslugaGostAction: {
			text: langs('Услуги ГОСТ'),
			tooltip: langs('Услуги ГОСТ'),
			iconCls: 'lpu-services-gost16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swMKB10Action: {
			text: langs('МКБ-10'),
			tooltip: langs('Справочник МКБ-10'),
			iconCls: 'spr-mkb16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swMESAction: {
			text: langs('Новые ') + getMESAlias(),
			tooltip: langs('Справочник новых ') + getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function()
			{
				getWnd('swMesSearchWindow').show();
			},
			hidden: (!isAdmin || (getRegionNick().inlist(['by'])))
		},
		swMESOldAction: {
			text: getMESAlias(),
			tooltip: langs('Справочник') + getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function()
			{
				getWnd('swMesOldSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['by']) // TODO: После тестирования доступ должен быть для всех
		},
		swOrgAllAction: {
			text: langs('Все организации'),
			tooltip: langs('Все организации'),
			iconCls: 'spr-org16',
			handler: function()
			{
				getWnd('swOrgViewForm').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swContragentsAction: {
			text: langs('Контрагенты'),
			tooltip: langs('Справочник контрагентов для персонифицированного учета'),
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
			text: langs('Учет медикаментов'),
			tooltip: langs('Документы учета медикаментов'),
			iconCls: 'drug-traffic16',
			handler: function()
			{
				getWnd('swDocumentUcViewWindow').show();
			},
			hidden: !isAdmin
		},
		swOrgLpuAction: {
			text: langs('Лечебно-профилактические учреждения'),
			tooltip: langs('Лечебно-профилактические учреждения'),
			iconCls: 'spr-org-lpu16',
			handler: function()
			{
				getWnd('swOrgViewForm').show({mode: 'lpu'});
			},
			hidden: false
		},
		swOrgGosAction: {
			text: langs('Государственные учреждения'),
			tooltip: langs('Государственные учреждения'),
			iconCls: 'spr-org-gos16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swOrgStrahAction: {
			text: langs('Страховые медицинские организации'),
			tooltip: langs('Страховые медицинские организации'),
			iconCls: 'spr-org-strah16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swOrgBankAction: {
			text: langs('Банки'),
			tooltip: langs('Банки'),
			iconCls: 'spr-org-bank16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swRlsFirmsAction: {
			text: langs('Производители лекарственных средств'),
			tooltip: langs('Производители лекарственных средств'),
			iconCls: 'spr-org-manuf16',
			handler: function(){
				if(!getWnd('swRlsFirmsSearchWindow').isVisible()) getWnd('swRlsFirmsSearchWindow').show();
			}
		},
		swOMSSprTerrAction: {
			text: langs('Территории субъекта РФ'),
			tooltip: langs('Территории субъекта РФ'),
			iconCls: 'spr-terr-oms16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swClassAddrAction: {
			text: langs('Классификатор адресов'),
			tooltip: langs('Классификатор адресов'),
			iconCls: 'spr-terr-addr16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swSprPromedAction: {
			text: langs('Справочники Промед'),
			tooltip: langs('Справочники Промед'),
			iconCls: 'spr-promed16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprLpuAction: {
			text: langs('Справочники МО'),
			tooltip: langs('Справочники МО'),
			iconCls: 'spr-lpu16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprOmsAction: {
			text: langs('Справочники ОМС'),
			tooltip: langs('Справочники ОМС'),
			iconCls: 'spr-oms16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprDloAction: {
			text: langs('Справочники ЛЛО'),
			tooltip: langs('Справочники ЛЛО'),
			iconCls: 'spr-dlo16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprPropertiesProfileAction: {
			text: langs('Характеристики профилей отделений'),
			tooltip: langs('Характеристики профилей отделений'),
			iconCls: 'otd-profile16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprUchetFactAction: {
			text: langs('Учет фактической выработки смен'),
			tooltip: langs('Учет фактической выработки смен'),
			iconCls: 'uchet-fact16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
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
		DloEgissoCreateDataAction: {
			text: langs('Сформировать данные'),
			tooltip: langs('Сформировать данные'),
			iconCls: '',
			hidden: !isUserGroup('EGISSOAdmin'),
			handler: function() {
                getWnd('swEgissoDataImportWindow').show();
			}
		},
		DloEgissoOpenModuleAction: {
			text: langs('Открыть модуль'),
			tooltip: langs('Открыть модуль'),
			iconCls: '',
			hidden: !isUserGroup('EGISSOAdmin') && !isUserGroup('EGISSOUser'),
			handler: function()
			{
				var url = '/ext03_6/directions_spa_treatment.html?PHPSESSID='+getCookie('PHPSESSID');
				window.open(url);
			}
		},
		DloEgissoReceptExportAction: {
			text: langs('Журнал ручного экспорта МСЗ'),
			tooltip: langs('Журнал ручного экспорта МСЗ'),
			iconCls: '',
			hidden: !isUserGroup('EGISSOAdmin') && !isUserGroup('EGISSOUser'),
			handler: function()
			{
				getWnd('swEgissoReceptExportListWindow').show();
			}
		},
	
		SprMedPerson4Rec: {
			text: 'Врачи ЛЛО',
			tooltip: getRLSTitle(),
			iconCls: 'rls16',
			handler: function()
			{
				getWnd('swMedPerson4ReceptListWindow').show();
			},
			hidden:  getRegionNick() != 'ufa'?true:( !isSuperAdmin() && !isSuperAdmin() && !isUserGroup(['ChiefLlo', 'DLOAccess'])) 
		},
		SprPostAction: {
			text: langs('Должности'),
			tooltip: langs('Должности'),
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Post', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprSkipPaymentReasonAction: {
			text: langs('Причины невыплат'),
			tooltip: langs('Причины невыплат'),
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'SkipPaymentReason', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprWorkModeAction: {
			text: langs('Режимы работы'),
			tooltip: langs('Режимы работы'),
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkMode', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprSpecialityAction: {
			text: langs('Специальности'),
			tooltip: langs('Специальности'),
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Speciality', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprDiplomaSpecialityAction: {
			text: langs('Дипломные специальности'),
			tooltip: langs('Дипломные специальности'),
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'DiplomaSpeciality', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprLeaveRecordTypeAction: {
			text: langs('Тип записи окончания работы'),
			tooltip: langs('Тип записи окончания работы'),
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'LeaveRecordType', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprEducationTypeAction: {
			text: langs('Тип образования'),
			tooltip: langs('Тип образования'),
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationType', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		SprEducationInstitutionAction: {
			text: langs('Учебное учреждение'),
			tooltip: langs('Учебное учреждение'),
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationInstitution', main_center_panel);
			}/*,
			hidden: !isAdmin*/
		},
		swF14OMSPerAction: {
			text: langs('Форма Ф14 ОМС: Показатели'),
			tooltip: langs('Показатели для формы Ф14 ОМС'),
			iconCls: 'rep-f14oms-per16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},
		swF14OMSAction: {
			text: langs('Форма Ф14 ОМС'),
			tooltip: langs('Форма Ф14 ОМС'),
			iconCls: 'rep-f14oms16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},
		swF14OMSFinAction: {
			text: langs('Форма Ф14 ОМС: Приложение 1'),
			tooltip: langs('Форма Ф14 ОМС: Приложение 1'),
			iconCls: 'rep-f14oms-fin16',
			handler: function()
			{
				sw.swMsg.alert(langs('Внимание'),langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isAdmin
		},/*
		swAdminWorkPlaceAction: {
			text: langs('Рабочее место администратора'),
			tooltip: langs('Рабочее место администратора'),
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swAdminWorkPlaceWindow').show({});
			},
			hidden: false
		},
		swLpuAdminWorkPlaceAction: {
			text: langs('Рабочее место администратора МО'),
			tooltip: langs('Рабочее место администратора МО'),
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swLpuAdminWorkPlaceWindow').show({});
			},
			hidden: false
		},*/
		/*
		swRegWorkPlaceAction: {
			text: langs('Рабочее место регистратора'),
			tooltip: langs('Рабочее место регистратора'),
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swRegWorkPlaceWindow').show({});
			},
			hidden: !isAdmin
		},
		*/
		swReportEngineAction: {
			text: langs('Репозиторий отчетов'),
			tooltip: langs('Репозиторий отчетов'),
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
			text: langs('Настройки ЛИС'),
			tooltip: langs('Настройки пользователя ЛИС'),
			handler: function()
			{
				getWnd('swAnalyzerWindow').show({pmUser_id: getGlobalOptions().pmuser_id, pmUser_Login: UserLogin});
			},
			hidden: true //((!isAdmin) || (getRegionNick() == 'saratov'))
		},
		swRrlExportWindowAction: {
			text: langs('Выгрузка РРЛ'),
			tooltip: langs('Выгрузка регистра региональных льготников'),
			handler: function()
			{
				getWnd('swRrlExportWindow').show();
			},
			hidden: (getRegionNick() != 'ufa')
		},
		swPrepBlockSprAction: {
			text: langs('Справочник фальсификатов и забракованных серий ЛС'),
			tooltip: langs('Справочник фальсификатов и забракованных серий ЛС'),
			handler: function()
			{
				getWnd('swPrepBlockViewWindow').show();
			}
		},
		PrepBlockCauseViewAction: {
			text: langs('Причины блокировки ЛС'),
			tooltip: langs('Причины блокировки ЛС'),
			iconCls : '',
			hidden: !isSuperAdmin(),
			handler: function()
			{
				getWnd('swPrepBlockCauseViewWindow').show();
			}
		},
		SelectMoToControl: {
				width: 100,
				text:langs('Управление МО'),
				hidden: true,
				id: 'select_mo_win',
				iconCls: 'settings16',
				handler: function()
				{
					var callback = Ext.emptyFn;
					if(getRegionNick() == 'ufa') {
						lpuBuildingCombo = Ext.getCmp('CmkLpuBuilding_combo');
						if(!lpuBuildingCombo.hidden)
							callback = function() {
								lpuBuildingCombo.getStore().reload()
							};
					}
					getWnd('swSelectMOToControlWindow').show({callback: callback});
				}

		},
		VideoChatBtn: {
				iconCls: 'VideoChatWindowIcon',
				hidden: true,
				style: 'background-color: #1976d2;',
				tooltip: 'Видеочат',
				handler: function() {
					getWnd('swVideoChatWindow').show();
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
			/*{
				text: langs('Медицинский персонал'),
				hidden: (!isAdmin && !isLpuAdmin()) || (getRegionNick() == 'pskov'),
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
			},*/
			//'-', //TODO: Показывать взависимости от условий
			sw.Promed.Actions.swLpuUslugaAction,
			sw.Promed.Actions.swUslugaGostAction,
			sw.Promed.Actions.swMKB10Action,
			sw.Promed.Actions.swMESAction,
			sw.Promed.Actions.swMESOldAction,
			sw.Promed.Actions.UslugaComplexTreeAction,
			'-',
			{
				text:langs('Организации'),
				iconCls:'spr-org16',
				hidden: !isAdmin && !isLpuAdmin() && (getRegionNick() != 'ufa'),
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
				text:langs('Классификатор территорий'),
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
			'-',
			sw.Promed.Actions.EvnUdostViewAction,
			'-',
			sw.Promed.Actions.EvnReceptFindAction,
			sw.Promed.Actions.EvnReceptAddStreamAction,
			'-',
			sw.Promed.Actions.OstAptekaViewAction,
			sw.Promed.Actions.OstDrugViewAction,
			sw.Promed.Actions.OstSkladViewAction,
			'-',
			sw.Promed.Actions.DrugRequestViewAction,
			sw.Promed.Actions.EvnReceptInCorrectFindAction,
			sw.Promed.Actions.swTemperedDrugs,
			
			sw.Promed.Actions.DrugMnnLatinNameEditAction,
			sw.Promed.Actions.DrugTorgLatinNameEditAction,
			'-',
			sw.Promed.Actions.SprRlsAction,
            {
                text: langs('ЕГИССО'),
                tooltip: langs('ЕГИССО'),
                iconCls: '',
                hidden: getRegionNick() == 'kz' || (!isUserGroup('EGISSOAdmin') && !isUserGroup('EGISSOUser')),
                menu: new Ext.menu.Menu({
					id: 'menu_egisso',
					items:[
						sw.Promed.Actions.DloEgissoCreateDataAction,
						sw.Promed.Actions.DloEgissoOpenModuleAction,
						sw.Promed.Actions.DloEgissoReceptExportAction
					]
				})
            },
			'-',
			sw.Promed.Actions.SprMedPerson4Rec
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

	this.menu_polka_main.addMenuItem(sw.Promed.Actions.EvnPLStreamInputAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.EvnPLEditAction);
	this.menu_polka_main.addSeparator();

	this.menu_polka_main.addMenuItem(sw.Promed.Actions.PersonCardSearchAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.PersonCardViewAllAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.PersonCardStateViewAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.swPersonCardAttachListAction);
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.AutoAttachViewAction);
	//Добавил что б потом убрать
	this.menu_polka_main.addMenuItem(sw.Promed.Actions.worksheetList);

	//this.menu_polka_main.addSeparator();
	//this.menu_polka_main.addMenuItem(sw.Promed.Actions.swMedPersonalWorkPlaceAction);
	//this.menu_polka_main.addSeparator();
	//this.menu_polka_main.addMenuItem(sw.Promed.Actions.swVKWorkPlaceAction);
	//this.menu_polka_main.addSeparator();
	//this.menu_polka_main.addMenuItem(sw.Promed.Actions.swMseWorkPlaceAction);
	
	if (isAdmin) {
		this.menu_polka_main.addSeparator();
		this.menu_polka_main.addMenuItem(sw.Promed.Actions.swJournalDirectionsAction);
		this.menu_polka_main.addSeparator();
	}
		// Углубленное диспансерное обследование ВОВ
		/*this.menu_polka_main.addMenuItem({ //http://redmine.swan.perm.ru/issues/22108#note-6
			text:langs('Углубленное диспансерное обследование ВОВ'),
			iconCls: 'pol-dopdisp16', // to-do: Поменять иконки
			hidden: false, // !isAdmin
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_wow',
				items:
				[
					sw.Promed.Actions.PersonDispWOWStreamAction,
					sw.Promed.Actions.PersonDispWOWSearchAction
				]
			})
		});*/
		/*this.menu_polka_main.addMenuItem({ // старый функционал по дд
			text:langs('Дополнительная диспансеризация'),
			iconCls: 'pol-dopdisp16',
			hidden: false,//!isAdmin,
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dd',
				items:
				[
					sw.Promed.Actions.PersonDopDispSearchAction,
					sw.Promed.Actions.PersonDopDispStreamInputAction,
					sw.Promed.Actions.EvnPLDopDispSearchAction,
					sw.Promed.Actions.EvnPLDopDispStreamInputAction
				]
			})
		});*/
		this.menu_polka_main.addMenuItem({
			text: langs('Скрининговые исследования'),
			hidden: getRegionNick() != 'kz',
			iconCls: 'disp-view16',
			menu: new Ext.menu.Menu({
				items: [
					sw.Promed.Actions.EvnPLDispScreenSearchAction,
					sw.Promed.Actions.EvnPLDispScreenChildSearchAction
				]
			})
		});
	var dopDispItems = 	[
		sw.Promed.Actions.PersonDispWOWSearchAction,
		'-',
		sw.Promed.Actions.PersonPrivilegeWOWSearchAction,
		sw.Promed.Actions.PersonPrivilegeWOWStreamInputAction,
		'-',
		sw.Promed.Actions.PersonDopDispSearchAction,
		'-',
		sw.Promed.Actions.EvnPLDopDispSearchAction,
		'-',
		sw.Promed.Actions.EvnPLDispDop13SearchAction,
		sw.Promed.Actions.EvnPLDispDop13SecondSearchAction
	];
	if (getRegionNick().inlist(['adygeya'])) {
		dopDispItems.splice(4, 4);
	}
	this.menu_polka_main.addMenuItem({
			text:langs('Диспансеризация взрослого населения'),
			iconCls: 'pol-dopdisp16',
			hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dd13',
				items: dopDispItems
			})
		});
	this.menu_polka_main.addMenuItem({
		text:langs('Профилактические осмотры взрослых'),
		iconCls: 'pol-dopdisp16',
		hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
		menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dd13',
				items:
					[
						sw.Promed.Actions.EvnPLDispProfSearchAction
					]
			})
	});
	var polkaDD13Items = [
		sw.Promed.Actions.swRegChildOrphanDopDispFindAction,
		sw.Promed.Actions.swEvnPLChildOrphanDopDispFindAction,
		'-',
		sw.Promed.Actions.PersonDispOrpSearchAction,
		sw.Promed.Actions.PersonDispOrpAdoptedSearchAction,
		sw.Promed.Actions.EvnPLDispOrpSearchAction,
		sw.Promed.Actions.EvnPLDispOrpSecSearchAction,
		'-',
		sw.Promed.Actions.EvnPLDispTeenExportAction
	];
	if (getRegionNick().inlist(['adygeya'])) {
		polkaDD13Items.splice(0, 3);
	}
		this.menu_polka_main.addMenuItem({
			text:langs('Диспансеризация детей-сирот'),
			iconCls: 'pol-dopdisp16',
			hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dd13',
				items: polkaDD13Items
			})
		});

		var polkaDD16Items = [
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
		];
		if (getRegionNick().inlist(['adygeya', 'yakutiya'])) {
			polkaDD16Items.splice(6, 4);
			polkaDD16Items.splice(0, 3);
		}
		this.menu_polka_main.addMenuItem({
			text:langs('Медицинские осмотры несовершеннолетних'),
			iconCls: 'pol-dopdisp16',
			hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
			menu: new Ext.menu.Menu(
				{
					id: 'menu_polka_dd13',
					items: polkaDD16Items
				})
		});

		this.menu_polka_main.addMenuItem({
			text:langs('Диспансеризация (подростки 14ти лет)'),
			iconCls: 'dopdisp-teens16',
			hidden: getRegionNick().inlist(['by','kz', 'adygeya']),//!isAdmin,
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dt14',
				items:
				[
					sw.Promed.Actions.EvnPLDispTeen14SearchAction
				]
			})
		});
/*
		this.menu_polka_main.addMenuItem({
			text:langs('Диспансеризация отдельных групп взрослого населения'),
			iconCls: 'dopdisp-teens16',
			hidden: !(getGlobalOptions().region && getRegionNick() == 'ufa'),
			menu: new Ext.menu.Menu(
			{
				id: 'menu_polka_dt14',
				items:
				[
					sw.Promed.Actions.EvnPLDispSomeAdultSearchAction,
					sw.Promed.Actions.EvnPLDispSomeAdultStreamInputAction
				]
			})
		});
*/
		this.menu_polka_main.addSeparator();
		this.menu_polka_main.addMenuItem({
			text:langs('Диспансерное наблюдение'),
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
				text:langs('Индивидуальная карта беременной'),
				iconCls: 'pol-preg16',
				hidden: !isAdmin || getRegionNick().inlist(['by']),
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
				text:langs('Иммунопрофилактика'),
				iconCls: 'pol-immuno16',
				hidden: getRegionNick().inlist(['by']),
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
						sw.Promed.Actions.ammSprVaccineTypeForm,
						sw.Promed.Actions.ammSprVaccine,
						sw.Promed.Actions.ammSprNacCal,
						'-',
						sw.Promed.Actions.ammVacPresence
					]
				})
			});

			if ( getRegionNick() == 'ufa' ) {
				this.menu_polka_main.addMenuItem({
					text: langs('Онкоконтроль'),
					iconCls: 'patient16',
					menu: new Ext.menu.Menu({
						id: 'menu_polka_OnkoCtrl',
						items: [
							sw.Promed.Actions.ammOnkoCtrl_ProfileJurnal,
							'-',
							sw.Promed.Actions.ammOnkoCtrl_ReportMonutoring,
							sw.Promed.Actions.ammOnkoCtrl_ReportSetZNO                    
						]
					})
				});
			}

			this.menu_polka_main.addMenuItem({
				text:langs('Анкетирование'),
				iconCls: 'patient16',
				menu: new Ext.menu.Menu({
					id: 'menu_polka_OnkoCtrl',
					items: [
						sw.Promed.Actions.OnkoControlAction,// онкоконтроль, в этом меню в Уфе скрыто
						sw.Promed.Actions.PalliativeAction,// паллиативная помощь
						sw.Promed.Actions.GeriatricAction,// возраст не помеха
						sw.Promed.Actions.BIRADSAction,// оценка BI-RADS
						sw.Promed.Actions.RECISTAction,// оценка RECIST
						sw.Promed.Actions.PreliminarySurveyAction // предварительное анкетирование, пока только Казахстан
					]
				})
			});
	//	}

		this.menu_polka_main.addMenuItem(sw.Promed.Actions.FundHoldingViewAction);

		
	this.menu_dlo_service = new Ext.menu.Menu(
	{
		//plain: true,
		id: 'menu_dlo_service',
		items: [
			//sw.Promed.Actions.swLpuAdminWorkPlaceAction,
			//sw.Promed.Actions.swAdminWorkPlaceAction,
			//sw.Promed.Actions.swRegWorkPlaceAction,
			/*{
				text: langs('Рабочее место врача функциональной диагностики '),
				tooltip: langs('Рабочее место врача функциональной диагностики '),
				iconCls: 'test16',
				handler: function()
				{
					getWnd('swWorkPlaceFuncDiagWindow').show({ARMType: "common"});
				},
				hidden: !isAdmin || !isDebug()
			},*/
            {
                text: langs('Пользователи'),
                tooltip: langs('Пользователи'),
                iconCls: 'users16',
                menu: new Ext.menu.Menu({
                    items: [
                        sw.Promed.Actions.swUsersTreeViewAction,
                        sw.Promed.Actions.swGroupsViewAction
                    ]
                })
            },
            {
                text: langs('Двойники'),
                tooltip: langs('Двойники'),
                iconCls: 'doubles16',
                menu: new Ext.menu.Menu({
                    items: [
                        sw.Promed.Actions.PersonDoublesSearchAction,
                        sw.Promed.Actions.PersonDoublesModerationAction,
                        sw.Promed.Actions.PersonUnionHistoryAction
                    ]
                })
            },
            {
                text: langs('Система'),
                tooltip: langs('Система'),
                iconCls: '',
				hidden: !isAdmin && !isTestLpu,
                menu: new Ext.menu.Menu({
                    items:[
                        sw.Promed.Actions.swGlobalOptionAction,
                        //sw.Promed.Actions.swDivCountAction,//=============
                        //sw.Promed.Actions.loadLastObjectCode,//=============
                        //sw.Promed.Actions.swMSJobsAction,//=============
                        //sw.Promed.Actions.TemplateRefValuesOpenAction,//=============
                        //sw.Promed.Actions.ConvertAction,//=============
                        //sw.Promed.Actions.swImportSMPCardsTest,//=============
                        //sw.Promed.Actions.swLdapAttributeChangeAction,//=============
                        //sw.Promed.Actions.Test2Action,//=============
                        //sw.Promed.Actions.TestAction,//=============
                        //sw.Promed.Actions.swDicomViewerAction,//=============
                        sw.Promed.Actions.swRegistrationJournalSearchAction,
                        sw.Promed.Actions.swAnalyzerWindowAction,
                        //sw.Promed.Actions.ReportDBStructureAction,//=============
                        //sw.Promed.Actions.swXmlTemplateDebug,//=============
                        sw.Promed.Actions.swSelectWorkPlaceAction
                    ]
                })
            },
            {
                text: langs('Тестирование и отладка'),
                tooltip: langs('Тестирование и отладка'),
                iconCls: '',
                hidden: !(getGlobalOptions().superadmin && IS_DEBUG),
                menu: new Ext.menu.Menu({
                    items:[
                        sw.Promed.Actions.swDivCountAction,
                        sw.Promed.Actions.loadLastObjectCode,
                        sw.Promed.Actions.swMSJobsAction,
                        sw.Promed.Actions.TemplateRefValuesOpenAction,
                        sw.Promed.Actions.ConvertAction,
                        sw.Promed.Actions.swImportSMPCardsTest,
                        sw.Promed.Actions.swLdapAttributeChangeAction,
                        sw.Promed.Actions.Test2Action,
                        sw.Promed.Actions.TestAction,
                        sw.Promed.Actions.swDicomViewerAction,
                        sw.Promed.Actions.ReportDBStructureAction,
                        sw.Promed.Actions.swXmlTemplateDebug,
                        sw.Promed.Actions.swPersonPeriodicViewAction,
                        sw.Promed.Actions.swTestAction,
						sw.Promed.Actions.swBarcodeByEvnReceptIdViewAction
                    ]
                })
            }
		]
	});
	this.menu_dlo_service.addMenuItem({
		text:langs('МИАЦ'),
		iconCls: 'miac16',
		hidden: (getRegionNick() != 'ufa'),
		menu: new Ext.menu.Menu(
		{
			id: 'menu_miac',
			items: [
				sw.Promed.Actions.MiacExportAction,
				sw.Promed.Actions.MiacExportSheduleOptionsAction
			]
		})
	});
	if (isAdmin || isUserGroup(['medpersview', 'ouzuser', 'ouzadmin', 'ouzchief'])) // проверяем так же просмотр медперсонала )
	{
		this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swLpuSelectAction);
		this.menu_dlo_service.addSeparator();
	}

    this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swPersonSearchAction);

	this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swImportAction);

    this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swOptionsViewAction);
    this.menu_dlo_service.addMenuItem(sw.Promed.Actions.UserProfileAction);
    this.menu_dlo_service.addMenuItem(sw.Promed.Actions.MessageAction);
    this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swNumeratorAction);
    this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swTemperedDrugs);


	this.menu_dlo_service.addSeparator();
	//this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swRegistrationJournalSearchAction);
	//this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swAnalyzerWindowAction);
	this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swRrlExportWindowAction);
	if (IS_DEBUG) {
		//this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swPersonPeriodicViewAction);
		this.menu_dlo_service.addMenuItem(sw.Promed.Actions.TemplatesWindowTestAction);
		//this.menu_dlo_service.addMenuItem(sw.Promed.Actions.TemplatesEditWindowAction);
		//this.menu_dlo_service.addMenuItem(sw.Promed.Actions.ReportDBStructureAction);
		//this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swAssistantWorkPlaceAction);
		//this.menu_dlo_service.addMenuItem(sw.Promed.Actions.swSelectWorkPlaceAction);
		
	}

	this.menu_dlo_service_oper_llo = new Ext.menu.Menu({
		//plain: true,
		id: 'menu_dlo_service',
		items: [
			sw.Promed.Actions.swOptionsViewAction,
			sw.Promed.Actions.UserProfileAction,
			sw.Promed.Actions.MessageAction
		]
	});

	this.menu_stac_main = new Ext.menu.Menu(
	{
		id: 'menu_stac_main',
		items: [
			sw.Promed.Actions.swEvnPSStreamAction,
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
	}*/
	/*
	if (isAdmin || isTestLpu)
	{
		this.menu_stac_main.addSeparator();
		this.menu_stac_main.addMenuItem(sw.Promed.Actions.swSuicideAttemptsEditAction);
		this.menu_stac_main.addMenuItem(sw.Promed.Actions.swSuicideAttemptsFindAction);
	}*/
	this.menu_stac_main.addSeparator();
	this.menu_stac_main.addMenuItem(sw.Promed.Actions.swJourHospDirectionAction);

	/*
	this.menu_stac_main.addSeparator();
	this.menu_stac_main.addMenuItem({
		text: langs('Патоморфология'),
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
			sw.Promed.Actions.swEvnUslugaParStreamAction,
			sw.Promed.Actions.swEvnUslugaParFindAction,
			sw.Promed.Actions.swEvnLabSampleDefectViewAction
			//'-',
			//sw.Promed.Actions.swMedPersonalWorkPlaceParAction
		]
	});
	this.menu_stomat_main = new Ext.menu.Menu(
	{
		id: 'menu_stomat_main',
		items: [
			sw.Promed.Actions.swEvnPLStomStreamAction,
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
		this.menu_farmacy_main.addMenuItem(sw.Promed.Actions.PrepBlockCauseViewAction);
	}
	
	/*
	this.menu_immunoprof_main = new Ext.menu.Menu(
	{
		id: 'menu_immunoprof_main',
		items: [
			sw.Promed.Actions.swInjectionStreamAction,
			sw.Promed.Actions.swInjectionFindAction,
			'-',
			sw.Promed.Actions.swMedicalTapStreamAction,
			sw.Promed.Actions.swMedicalTapFindAction,
			'-',
			sw.Promed.Actions.swSerologyStreamAction,
			sw.Promed.Actions.swSerologyFindAction,
			'-',
			sw.Promed.Actions.swAbsenceBakAction,
			sw.Promed.Actions.swCurrentPlanAction
		]
	});
	*/
		// Документы 
		this.menu_documents = new Ext.menu.Menu({
			id: 'menu_documents',
			items: []
		});
		
		this.menu_documents.addMenuItem(sw.Promed.Actions.RegistryViewAction);
		this.menu_documents.addMenuItem(sw.Promed.Actions.RegistryNewViewAction);
		this.menu_documents.addMenuItem(sw.Promed.Actions.RegistryEUViewAction);
		if(getRegionNick() == 'penza' && (isUserGroup('RegistryUser') || isUserGroup('RegistryUserReadOnly'))) {
			this.menu_documents.addMenuItem(sw.Promed.Actions.RegistryExportInTFOMS);
		}
		//this.menu_documents.addMenuItem(sw.Promed.Actions.TariffVolumeViewAction);
		this.menu_documents.addSeparator();
		
		this.menu_documents.addMenuItem({
			text:langs('Патоморфология'),
			hidden: getRegionNick().inlist(['by']),
			iconCls: 'pathomorph-16',
			menu: new Ext.menu.Menu(
			{
				id: 'menu_pathomorph',
				items: [
					sw.Promed.Actions.EvnDirectionHistologicViewAction,
					sw.Promed.Actions.EvnHistologicProtoViewAction,
					'-',
					sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
					sw.Promed.Actions.EvnMorfoHistologicProtoViewAction,
					'-',
					sw.Promed.Actions.DirectionsForCytologicalDiagnosticExaminationViewAction,
					sw.Promed.Actions.CytologicalDiagnosticTestProtocolsViewAction
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
			text: langs('Обращения'),
			iconCls: 'petition-stream16',
			menu: new Ext.menu.Menu(
			{
				id: 'menu_treatment',
				items:
				[
					sw.Promed.Actions.swTreatmentStreamInputAction,
					sw.Promed.Actions.swTreatmentSearchAction,
					sw.Promed.Actions.swTreatmentReportAction
				]
			}),
			hidden: !isAccessTreatment()
		});
		
		this.menu_documents.addMenuItem({
			text:langs('Свидетельства'),
			hidden: !isMedSvidAccess() && !isSuperAdmin() && !isLpuAdmin(),
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
			text: langs('Извещения о ДТП'),
			iconCls: 'pol-dtp16',
			// https://redmine.swan.perm.ru/issues/96252
			hidden: ((!isSuperAdmin() && !isLpuAdmin()) || getRegionNick().inlist(['by,kz'])),
			menu: new Ext.menu.Menu(
				{
					id: 'menu_dtp',
					items: [
						sw.Promed.Actions.swEvnDtpWoundViewAction,
						sw.Promed.Actions.swEvnDtpDeathViewAction
					]
				})
		});


		this.menu_documents.addSeparator();
		this.menu_documents.addMenuItem(sw.Promed.Actions.swCardCallFindAction);
		this.menu_documents.addMenuItem(sw.Promed.Actions.swCardCallStreamAction);
		
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
						text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+((getGlobalOptions().lpu_id>0)?'МО: ':'Организация : ')+Ext.globalOptions.globals.org_nick,
						xtype: 'tbtext'
					}
				]
			});
		}		

	// Удалим стрелку выпадающего меню для экономии места
	var menu_listener = {
		'render': function(b) {
			b.el.child(b.menuClassTarget).removeClass('x-btn-with-menu');
		}
	};
	// панель меню
	main_menu_panel = new sw.Promed.Toolbar({
		autoHeight: true,
		region: 'north',
		items:
		[
			{
				text: langs('АРМ'),
				title: langs('АРМ'),
				tooltip: langs('Рабочее место врача'),
				handler: function()
				{
					sw.Promed.MedStaffFactByUser.selectARM({
						ARMType: 'common',
						onSelect: null
					});
				},
				hidden: ((getGlobalOptions().medstafffact == undefined) && (getGlobalOptions().lpu_id>0))
			},
			'-',
			{
				text:langs('Паспорт МО'),
				id: '_menu_lpu',
				menu: this.menu_passport_lpu,
				hidden: false, //!isSuperAdmin() && !isLpuAdmin(),
				tabIndex: -1,
				listeners :menu_listener

			},
			/*{
				text:langs('Регистратура'),
				iconCls: 'reg16',
				menu: this.menu_reg_main,
				tabIndex: -1
			},*/
			{
				text:langs('ЛЛО'),
				id: '_menu_dlo',
				menu: this.menu_dlo_main,
				tabIndex: -1,
				listeners :menu_listener
			},
			{
				text: langs('Поликлиника'),
				id: '_menu_polka',
				hidden: (getRegionNick() == 'saratov'),
				menu: this.menu_polka_main,
				tabIndex: -1,
				listeners :menu_listener
			},
			{
				text: langs('Стационар'),
				id: '_menu_stac',
				menu: this.menu_stac_main,
				tabIndex: -1,
				hidden: (getRegionNick() == 'saratov'),
				listeners :menu_listener
			},
			{
				text: langs('Параклиника'),
				id: '_menu_parka',
				menu: this.menu_parka_main,
				tabIndex: -1,
				hidden: getRegionNick().inlist(['saratov', 'by']),
				listeners :menu_listener
			},
			{
				text: langs('Стоматология'),
				id: '_stomatka',
				hidden: (getRegionNick() == 'saratov'),
				menu: this.menu_stomat_main,
				tabIndex: -1,
				listeners :menu_listener
			},
			{
				text: langs('Аптека'),
				id: '_menu_farmacy',
				menu: this.menu_farmacy_main,
				tabIndex: -1,
				hidden: (getRegionNick() == 'saratov'),
				listeners :menu_listener
			},
			{
				text: langs('Документы'),
				id: '_menu_documents',
				menu: this.menu_documents,
				tabIndex: -1,
				hidden: (getRegionNick() == 'saratov'),
				listeners :menu_listener
			},
			{
				text:langs('Сервис'),
				id: '_menu_service',
				menu: this.menu_dlo_service,
				tabIndex: -1,
				listeners :menu_listener
			},
			{
				text:langs('Отчеты'),
				id: '_menu_reports',
				menu: this.menu_reports,
				tabIndex: -1,
				listeners: menu_listener
			},
			{
				text: langs('Окна'),
				id: '_menu_windows',
				listeners: {
					'render': menu_listener['render'],
					'click': function(e) {
						var menu = Ext.menu.MenuMgr.get('menu_windows');
						menu.removeAll();
						var number = 1;
						sw.WindowMgr.each(function(wnd) {
							if ( wnd.isVisible() )
							{
								if ( sw.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												if (Ext6.getCmp(wnd.id)) {
													Ext6.getCmp(wnd.id).toFront();
												} else {
													Ext.getCmp(wnd.id).toFront();
												}
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
												if (Ext6.getCmp(wnd.id)) {
													Ext6.getCmp(wnd.id).toFront();
												} else {
													Ext.getCmp(wnd.id).toFront();
												}
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: langs('Открытых окон нет'),
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
									text: langs('Закрыть все окна'),
									iconCls : 'close16',
									handler: function()
									{
										sw.WindowMgr.each(function(wnd){
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
						sw.WindowMgr.each(function(wnd){
							if ( wnd.isVisible() )
							{
								if ( sw.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												if (Ext6.getCmp(wnd.id)) {
													Ext6.getCmp(wnd.id).toFront();
												} else {
													Ext.getCmp(wnd.id).toFront();
												}
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
												if (Ext6.getCmp(wnd.id)) {
													Ext6.getCmp(wnd.id).toFront();
												} else {
													Ext.getCmp(wnd.id).toFront();
												}
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: langs('Открытых окон нет'),
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
									text: langs('Закрыть все окна'),
									iconCls : 'close16',
									handler: function()
									{
										sw.WindowMgr.each(function(wnd){
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
				text:langs('Помощь'),
				id: '_menu_help',
				menu: this.menu_help,
				tabIndex: -1,
				listeners :menu_listener
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
				id: 'CmkLpuBuilding_label',
				xtype: 'label',
				text: 'Оперативный отдел МО',
				style: 'margin-right: 10px',
				hidden: true
			},
			{
				id: 'CmkLpuBuilding_combo',
				name: 'LpuBuilding_id',
				baseParams: { SmpUnitType_Code: 4, form: 'cmk' },
				xtype: 'swsmpunitscombo',
				tpl:new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{values.LpuBuilding_Name}&nbsp{[(!Ext.isEmpty(values.Lpu_Nick)) ? "(" + values.Lpu_Nick +")" : ""]}',
					'</div></tpl>'
				),
				LpuBuildingType_id: 27,
				autoLoad: true,
				width: 200,
				listWidth: 300,
				hidden: true,
				store: new Ext.data.JsonStore({
					autoLoad: true,
					baseParams: this.baseParams,
					fields: [
						{name: 'LpuBuilding_id', type: 'int'},
						{name: 'LpuBuilding_Code', type: 'int'},
						{name: 'LpuBuilding_Name', type: 'string'},
						{name: 'Lpu_id', type: 'int'},
						{name: 'Lpu_Nick', type: 'string'}
					],
					key: 'LpuBuilding_id',
					sortInfo: {
						field: 'LpuBuilding_Name'
					},
					url: '/?c=CmpCallCard&m=loadSmpUnits',
					listeners: {
						beforeload: function() {
							if (Ext.isEmpty(getGlobalOptions().lpu_id)) {
								return false;
							}
						}
					}
				}),
				listeners: {
					change: function(combo,newValue) {
						if( !newValue ) return;

						Ext.Ajax.request({
							url: '/?c=Options&m=saveLpuBuildingForTimingCmk',
							params: { LpuBuilding_id: newValue }
						})


						Ext.Ajax.request({
							url: '/?c=LpuStructure&m=getLpuBuildingData',
							params: { LpuBuilding_id: newValue },
							callback: function(options, success, response) {
								var win = swWorkPlaceCenterDisasterMedicineWindow;
								if( !success || !win ) return;

								var result = Ext.util.JSON.decode(response.responseText);

								if ( !result && !result[0]) return;

								for( prop in win.SmpTiming ) {
									win.SmpTiming[prop] = parseInt(result[0][prop]);
								}
							}
						})
					}
				}
			},
			sw.Promed.Actions.SelectMoToControl,

			// #175117. Флаг "Я на смене":
			{
				id: 'cbWorkShift',
				boxLabel: langs('Я на смене'),
				xtype: 'checkbox',

				hidden: true,

				listeners:
					{
						render: _onRender_cbWorkShift,
						change: _onChange_cbWorkShift
					}
			},
			{
				id: '_user_menu',
				text: UserLogin,
				menu: this.user_menu,
				tabIndex: -1,
				listeners :menu_listener
			},
			sw.Promed.Actions.VideoChatBtn,
			'-',
			{
				text:langs('Выход'),
				handler: function()
				{
					sw.swMsg.show({
						title: langs('Подтвердите выход'),
						msg: langs('Вы действительно хотите выйти?'),
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								window.onbeforeunload = null;
								window.location=C_LOGOUT;
							}
						}
					});
				}
			}
		]
	});

	//#183310
	if (!isAdmin && getRegionNick() == 'msk' && isOperLLOUser()) {
		main_menu_panel = new sw.Promed.Toolbar({
			autoHeight: true,
			region: 'north',
			items:
				[
					{
						text: langs('АРМ'),
						title: langs('АРМ'),
						tooltip: langs('Рабочее место врача'),
						handler: function()
						{
							sw.Promed.MedStaffFactByUser.selectARM({
								ARMType: 'common',
								onSelect: null
							});
						},
						hidden: ((getGlobalOptions().medstafffact == undefined) && (getGlobalOptions().lpu_id>0))
					},
					'-',
					{
						text:langs('Сервис'),
						id: '_menu_service',
						menu: this.menu_dlo_service_oper_llo,
						tabIndex: -1,
						listeners :menu_listener
					},
					{
						text:langs('Отчеты'),
						id: '_menu_reports',
						menu: this.menu_reports,
						tabIndex: -1,
						listeners: menu_listener
					},
					{
						text: langs('Окна'),
						id: '_menu_windows',
						listeners: {
							'render': menu_listener['render'],
							'click': function(e) {
								var menu = Ext.menu.MenuMgr.get('menu_windows');
								menu.removeAll();
								var number = 1;
								sw.WindowMgr.each(function(wnd) {
									if ( wnd.isVisible() )
									{
										if ( sw.WindowMgr.getActive().id == wnd.id )
										{
											menu.add(new Ext.menu.Item(
												{
													text: number + ". " + wnd.title,
													iconCls : 'checked16',
													checked: true,
													handler: function()
													{
														if (Ext6.getCmp(wnd.id)) {
															Ext6.getCmp(wnd.id).toFront();
														} else {
															Ext.getCmp(wnd.id).toFront();
														}
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
														if (Ext6.getCmp(wnd.id)) {
															Ext6.getCmp(wnd.id).toFront();
														} else {
															Ext.getCmp(wnd.id).toFront();
														}
													}
												})
											);
											number++;
										}
									}
								});
								if ( menu.items.getCount() == 0 )
									menu.add({
										text: langs('Открытых окон нет'),
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
											text: langs('Закрыть все окна'),
											iconCls : 'close16',
											handler: function()
											{
												sw.WindowMgr.each(function(wnd){
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
								sw.WindowMgr.each(function(wnd){
									if ( wnd.isVisible() )
									{
										if ( sw.WindowMgr.getActive().id == wnd.id )
										{
											menu.add(new Ext.menu.Item(
												{
													text: number + ". " + wnd.title,
													iconCls : 'checked16',
													checked: true,
													handler: function()
													{
														if (Ext6.getCmp(wnd.id)) {
															Ext6.getCmp(wnd.id).toFront();
														} else {
															Ext.getCmp(wnd.id).toFront();
														}
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
														if (Ext6.getCmp(wnd.id)) {
															Ext6.getCmp(wnd.id).toFront();
														} else {
															Ext.getCmp(wnd.id).toFront();
														}
													}
												})
											);
											number++;
										}
									}
								});
								if ( menu.items.getCount() == 0 )
									menu.add({
										text: langs('Открытых окон нет'),
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
											text: langs('Закрыть все окна'),
											iconCls : 'close16',
											handler: function()
											{
												sw.WindowMgr.each(function(wnd){
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
						text:langs('Помощь'),
						id: '_menu_help',
						menu: this.menu_help,
						tabIndex: -1,
						listeners :menu_listener
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
						id: 'CmkLpuBuilding_label',
						xtype: 'label',
						text: 'Оперативный отдел МО',
						style: 'margin-right: 10px',
						hidden: true
					},
					{
						id: 'CmkLpuBuilding_combo',
						name: 'LpuBuilding_id',
						baseParams: { SmpUnitType_Code: 4, form: 'cmk' },
						xtype: 'swsmpunitscombo',
						tpl:new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{values.LpuBuilding_Name}&nbsp{[(!Ext.isEmpty(values.Lpu_Nick)) ? "(" + values.Lpu_Nick +")" : ""]}',
							'</div></tpl>'
						),
						LpuBuildingType_id: 27,
						autoLoad: true,
						width: 200,
						listWidth: 300,
						hidden: true,
						store: new Ext.data.JsonStore({
							autoLoad: true,
							baseParams: this.baseParams,
							fields: [
								{name: 'LpuBuilding_id', type: 'int'},
								{name: 'LpuBuilding_Code', type: 'int'},
								{name: 'LpuBuilding_Name', type: 'string'},
								{name: 'Lpu_id', type: 'int'},
								{name: 'Lpu_Nick', type: 'string'}
							],
							key: 'LpuBuilding_id',
							sortInfo: {
								field: 'LpuBuilding_Name'
							},
							url: '/?c=CmpCallCard&m=loadSmpUnits',
							listeners: {
								beforeload: function() {
									if (Ext.isEmpty(getGlobalOptions().lpu_id)) {
										return false;
									}
								}
							}
						}),
						listeners: {
							change: function(combo,newValue) {
								if( !newValue ) return;

								Ext.Ajax.request({
									url: '/?c=Options&m=saveLpuBuildingForTimingCmk',
									params: { LpuBuilding_id: newValue }
								})


								Ext.Ajax.request({
									url: '/?c=LpuStructure&m=getLpuBuildingData',
									params: { LpuBuilding_id: newValue },
									callback: function(options, success, response) {
										var win = swWorkPlaceCenterDisasterMedicineWindow;
										if( !success || !win ) return;

										var result = Ext.util.JSON.decode(response.responseText);

										if ( !result && !result[0]) return;

										for( prop in win.SmpTiming ) {
											win.SmpTiming[prop] = parseInt(result[0][prop]);
										}
									}
								})
							}
						}
					},
					sw.Promed.Actions.SelectMoToControl,
					{
						id: '_user_menu',
						text: UserLogin,
						menu: this.user_menu,
						tabIndex: -1,
						listeners :menu_listener
					},
					'-',
					{
						text:langs('Выход'),
						handler: function()
						{
							sw.swMsg.show({
								title: langs('Подтвердите выход'),
								msg: langs('Вы действительно хотите выйти?'),
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' ) {
										window.onbeforeunload = null;
										window.location=C_LOGOUT;
									}
								}
							});
						}
					}
				]
		});
	}

	// Экшен для меню "Окна"
	sw.Promed.Actions.WindowsAction = new Ext.Action( {
		text: langs('Окна'),
		iconCls: 'windows16',
		listeners: {
			'click': function(obj, e) {
				//log(e);
				if ( IS_DEBUG == 1 && e.altKey && e.shiftKey && e.ctrlKey )
				{
					new Ext.Window({
						title: langs('C первым Апреля!'),
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
				sw.WindowMgr.each(function(wnd){
					if ( wnd.isVisible() )
					{
						if ( sw.WindowMgr.getActive().id == wnd.id )
						{
							menu.add(new Ext.menu.Item(
							{
								text: number + ". " + wnd.title,
								iconCls : 'checked16',
								checked: true,
								handler: function()
								{
									if (Ext6.getCmp(wnd.id)) {
										Ext6.getCmp(wnd.id).toFront();
									} else {
										Ext.getCmp(wnd.id).toFront();
									}
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
									if (Ext6.getCmp(wnd.id)) {
										Ext6.getCmp(wnd.id).toFront();
									} else {
										Ext.getCmp(wnd.id).toFront();
									}
								}
							})
							);
							number++;
						}
					}
				});
				if ( menu.items.getCount() == 0 )
					menu.add({
						text: langs('Открытых окон нет'),
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
						text: langs('Закрыть все окна'),
						iconCls : 'close16',
						handler: function()
						{
							sw.WindowMgr.each(function(wnd){
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
				sw.WindowMgr.each(function(wnd){
					if ( wnd.isVisible() )
					{
						if ( sw.WindowMgr.getActive().id == wnd.id )
						{
							menu.add(new Ext.menu.Item(
							{
								text: number + ". " + wnd.title,
								iconCls : 'checked16',
								checked: true,
								handler: function()
								{
									if (Ext6.getCmp(wnd.id)) {
										Ext6.getCmp(wnd.id).toFront();
									} else {
										Ext.getCmp(wnd.id).toFront();
									}
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
									if (Ext6.getCmp(wnd.id)) {
										Ext6.getCmp(wnd.id).toFront();
									} else {
										Ext.getCmp(wnd.id).toFront();
									}
								}
							})
							);
							number++;
						}
					}
				});
				if ( menu.items.getCount() == 0 )
					menu.add({
						text: langs('Открытых окон нет'),
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
						text: langs('Закрыть все окна'),
						iconCls : 'close16',
						handler: function()
						{
							sw.WindowMgr.each(function(wnd){
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

	
	// Меню в виде "ленты". Включается опцией в настройках
	var main_menu_ribbon_items = [];
	
	main_menu_ribbon_items = main_menu_ribbon_items.concat([{
		title: langs('АРМ'),
		id: '_menu_arm',
		iconCls: 'workplace-mp16',
		hidden: getGlobalOptions().medstafffact == undefined,
		items: [
			sw.Promed.Actions.swMedPersonalWorkPlaceAction,
			'-',
			sw.Promed.Actions.WindowsAction,
			'-',
			{
				text: langs('Помощь'),
				iconCls: 'help16',
				testId: 'mainmenu_help',
				menu: this.menu_help
			},
			'-',
			{
				iconCls: 'user16',
				text: UserLogin,
				menu: this.user_menu
			},
			sw.Promed.Actions.PromedExit
		]
	}]);
	
	
	main_menu_ribbon_items = main_menu_ribbon_items.concat([{
		title: langs('МО'),
		id: '_menu_lpu',
		iconCls: 'lpu16',
		items: [{
			text: langs('Паспорт МО'),
			//hidden: !isSuperAdmin() && !isLpuAdmin(),
			iconCls: 'lpu16',
			menu: new Ext.menu.Menu({
				items: [
				sw.Promed.Actions.OrgStructureViewAction,
				sw.Promed.Actions.LpuStructureViewAction,
				sw.Promed.Actions.swOrgPassportAction,
				sw.Promed.Actions.swLpuPassportAction,
				sw.Promed.Actions.GlossarySearchAction,
				sw.Promed.Actions.swMESAction,
				sw.Promed.Actions.swMESOldAction,
				sw.Promed.Actions.UslugaComplexTreeAction,
				'-',
				{
					text:langs('Организации'),
					iconCls:'spr-org16',
					hidden: !isAdmin && !isLpuAdmin() && (getRegionNick() != 'ufa'),
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
				}
				/*,
				'-',
				sw.Promed.Actions.SprPropertiesProfileAction,
				'-',
				sw.Promed.Actions.SprUchetFactAction*/
				]
			})
		}, {
			text: langs('Персонал'),
			iconCls: 'staff16',
			hidden: !isAdmin && !isLpuAdmin(),
			menu: new Ext.menu.Menu({
				items: [
					//sw.Promed.Actions.MedPersonalPlaceAction,
					sw.Promed.Actions.MedWorkersAction
					//sw.Promed.Actions.MedPersonalSearchAction
				]
			})
		},/* {
			text: langs('Справочники'),
			iconCls: 'staff16',
			hidden: !isAdmin && !isLpuAdmin(),
			menu: new Ext.menu.Menu({
				items: [
					sw.Promed.Actions.swMESOldAction,
					
				]
			})
		}, */
		/*{
			text: langs('Организации'),
			iconCls: 'spr-org16',
			hidden: !isAdmin && !isLpuAdmin(),
			menu: new Ext.menu.Menu({
			items: [
				sw.Promed.Actions.swOrgAllAction,
				'-',
				sw.Promed.Actions.swOrgLpuAction,
				sw.Promed.Actions.swOrgGosAction,
				sw.Promed.Actions.swOrgStrahAction,
				sw.Promed.Actions.swOrgBankAction,
				sw.Promed.Actions.swRlsFirmsAction
			]})
		},*/
		'-',
	sw.Promed.Actions.WindowsAction,
		'-',
		{
			text: langs('Помощь'),
			iconCls: 'help16',
			testId: 'mainmenu_help',
			menu: this.menu_help
		},
		'-',
		{
			iconCls: 'user16',
			text: UserLogin,
			menu: this.user_menu
		},
	sw.Promed.Actions.PromedExit
		]
	}]);

	main_menu_ribbon_items = main_menu_ribbon_items.concat([
	{
		title: langs('ЛЛО'),
		id: '_menu_dlo',
		iconCls: 'dlo16',
		items: [{
			text: langs('Льготники'),
			iconCls: 'accessibility16',
			menu: new Ext.menu.Menu({
				items: [
				sw.Promed.Actions.swLgotTreeViewAction,
				sw.Promed.Actions.LgotFindAction,
					'-',
				sw.Promed.Actions.EvnUdostViewAction
				]
			})
		}, {
			text: "Рецепты",
			iconCls: 'receipt-search16',
			menu: new Ext.menu.Menu({
				items: [
				sw.Promed.Actions.EvnReceptFindAction,
				sw.Promed.Actions.EvnReceptAddStreamAction,
					'-',
				sw.Promed.Actions.EvnReceptInCorrectFindAction,
				sw.Promed.Actions.swTemperedDrugs
				]
			})
		}, {
			text: "Медикаменты",
			iconCls: 'dlo16',
			menu: new Ext.menu.Menu({
				items: [
				sw.Promed.Actions.OstAptekaViewAction,
				sw.Promed.Actions.OstDrugViewAction,
				sw.Promed.Actions.OstSkladViewAction,
					'-',
				sw.Promed.Actions.DrugRequestViewAction
				//sw.Promed.Actions.NewDrugRequestViewAction
				]
			})
		}, {
			text: "Справочники",
			iconCls: 'spr-dlo16',
			menu: new Ext.menu.Menu({
				items: [
				sw.Promed.Actions.DrugMnnLatinNameEditAction,
				sw.Promed.Actions.DrugTorgLatinNameEditAction,
				
					'-',
				sw.Promed.Actions.OrgFarmacyViewAction // isAdmin
				,'-',
				sw.Promed.Actions.SprRlsAction
				]
			})
		},
		'-',
		sw.Promed.Actions.WindowsAction,
		'-',
		{
			text: langs('Помощь'),
			iconCls: 'help16',
			menu: this.menu_help
		},
		'-',
		{
			iconCls: 'user16',
			text: UserLogin,
			menu: this.user_menu
		},
		sw.Promed.Actions.PromedExit
		]
	}]);

	if ( getRegionNick() != 'saratov') {
		main_menu_ribbon_items = main_menu_ribbon_items.concat([
		{
			title: langs('Поликлиника'),
			iconCls: 'polyclinic16',
			items: [{
				text: langs('Пациенты'),
				iconCls: 'patient16',
				menu: new Ext.menu.Menu({
					items: [
				sw.Promed.Actions.EvnPLStreamInputAction,
				sw.Promed.Actions.EvnPLEditAction,
					'-',
				sw.Promed.Actions.PersonCardSearchAction,
				sw.Promed.Actions.PersonCardViewAllAction,
				sw.Promed.Actions.PersonCardStateViewAction,
				sw.Promed.Actions.swPersonCardAttachListAction,
					'-',
				sw.Promed.Actions.AutoAttachViewAction
					]
				})
			},
		//sw.Promed.Actions.swMedPersonalWorkPlaceAction,
		
		//sw.Promed.Actions.swVKWorkPlaceAction,
		
		//sw.Promed.Actions.swMseWorkPlaceAction,
			{
				text: "Диспансеризация",
				iconCls: 'disp-view16',
				menu: new Ext.menu.Menu({
					items: [
					{
						text:langs('Диспансеризация взрослого населения'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
						menu: new Ext.menu.Menu(
							{
								id: 'menu_polka_dd13',
								items:
									[
										sw.Promed.Actions.PersonDispWOWSearchAction,
										sw.Promed.Actions.PersonPrivilegeWOWSearchAction,
										sw.Promed.Actions.PersonPrivilegeWOWStreamInputAction,
										'-',
										sw.Promed.Actions.PersonDopDispSearchAction,
										//sw.Promed.Actions.PersonDopDispStreamInputAction,
										'-',
										sw.Promed.Actions.EvnPLDopDispSearchAction,
										'-',
										sw.Promed.Actions.EvnPLDispDop13SearchAction,
										sw.Promed.Actions.EvnPLDispDop13SecondSearchAction
									]
							})
					},
					{
						text:langs('Профилактические осмотры взрослых'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
						menu: new Ext.menu.Menu(
							{
								id: 'menu_polka_dd13',
								items:
									[
										sw.Promed.Actions.EvnPLDispProfSearchAction
									]
							})
					},
					{
						text:langs('Диспансеризация детей-сирот'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
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
					},
					{
						text:langs('Медицинские осмотры несовершеннолетних'),
						iconCls: 'pol-dopdisp16',
						hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
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
					},
					{
						text:langs('Диспансеризация (подростки 14ти лет)'),
						iconCls: 'dopdisp-teens16',
						hidden: getRegionNick().inlist(['by','kz']),//!isAdmin,
						menu: new Ext.menu.Menu(
							{
								id: 'menu_polka_dt14',
								items:
									[
										sw.Promed.Actions.EvnPLDispTeen14SearchAction
										//sw.Promed.Actions.EvnPLDispTeen14StreamInputAction
									]
							})
					},
/*					{
						text:langs('Диспансеризация отдельных групп взрослого населения'),
						iconCls: 'dopdisp-teens16',
						hidden: !(getGlobalOptions().region && getRegionNick() == 'ufa'),
						menu: new Ext.menu.Menu(
							{
								id: 'menu_polka_dt14',
								items:
									[
										sw.Promed.Actions.EvnPLDispSomeAdultSearchAction,
										sw.Promed.Actions.EvnPLDispSomeAdultStreamInputAction
									]
							})
					},
*/
					{
						text:langs('Диспансерное наблюдение'),
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
					}/*,

					sw.Promed.Actions.EvnPLDispSomeAdultSearchAction,
					sw.Promed.Actions.EvnPLDispSomeAdultStreamInputAction
					*/
					]
				})
			}, {
				text: "Скрининговые исследования",
				hidden: getRegionNick() != 'kz',
				iconCls: 'disp-view16',
				menu: new Ext.menu.Menu({
					items: [
						sw.Promed.Actions.EvnPLDispScreenSearchAction,
						sw.Promed.Actions.EvnPLDispScreenChildSearchAction
					]
				})
			}, {
				text: "Беременные",
				iconCls: 'pol-preg16',
				hidden: ((!isAdmin && !isTestLpu) || getRegionNick().inlist(['by'])),
				menu: new Ext.menu.Menu({
					items: [
					sw.Promed.Actions.swPregCardViewAction,
					sw.Promed.Actions.swPregCardFindAction
					]
				})
			}, {
				text: "Иммунопрофилактика",
				iconCls: 'pol-immuno16',
				hidden: getRegionNick().inlist(['by','perm']),
				menu: new Ext.menu.Menu({
					items: [
					/*
					 * Закоментировал Тагир
					 *
					sw.Promed.Actions.swInjectionStreamAction,
					sw.Promed.Actions.swInjectionFindAction,
						'-',
					sw.Promed.Actions.swMedicalTapStreamAction,
					sw.Promed.Actions.swMedicalTapFindAction,
						'-',
					sw.Promed.Actions.swSerologyStreamAction,
					sw.Promed.Actions.swSerologyFindAction,
						'-',
					sw.Promed.Actions.swAbsenceBakAction,
						'-',
					sw.Promed.Actions.swCurrentPlanAction,
						'-',
					*/
				   //sw.Promed.Actions.ammSprOtherVacSchemeEditFotm,
					sw.Promed.Actions.ammStartVacFormPlan,
					sw.Promed.Actions.ammvacListTasks,
					'-',
					sw.Promed.Actions.amm_JournalsVac,
					getRegionNick() == 'kz' ? '' : '-',
					sw.Promed.Actions.ammvacReport_5,
					'-',    
					sw.Promed.Actions.ammSprVaccineTypeForm,					
					sw.Promed.Actions.ammSprVaccine,
					sw.Promed.Actions.ammSprNacCal,
					'-',
					sw.Promed.Actions.ammVacPresence
					]
				})
			},
			{
				text:langs('Онкоконтроль'),
				iconCls: 'patient16',
				hidden: (getRegionNick() != 'ufa'),
				menu: new Ext.menu.Menu ({
				items:
					[
					   sw.Promed.Actions.ammOnkoCtrl_ProfileJurnal
					   ,'-',
					   sw.Promed.Actions.ammOnkoCtrl_ReportMonutoring,
					   sw.Promed.Actions.ammOnkoCtrl_ReportSetZNO
					]
				})
			 
				   },        
					  
		sw.Promed.Actions.FundHoldingViewAction,
			'-',
		sw.Promed.Actions.WindowsAction,
			'-',
			{
					text: langs('Помощь'),
					iconCls: 'help16',
					menu: this.menu_help
			},
			'-',
			{
					iconCls: 'user16',
					text: UserLogin,
					menu: this.user_menu
			},
		sw.Promed.Actions.PromedExit
			]
		}]);
	}

	if ( getRegionNick() != 'saratov') {
		main_menu_ribbon_items = main_menu_ribbon_items.concat([
		{
			title: langs('Стационар'),
			id: '_menu_stac',
			iconCls: 'stac16',
			hidden: false,
			items: [{
				text: langs('Выбывшие'),
				iconCls: 'patient16',
				menu: new Ext.menu.Menu({
					items: [
				sw.Promed.Actions.swEvnPSStreamAction,
				sw.Promed.Actions.swEvnPSFindAction
					]
				})
			},
			sw.Promed.Actions.swJourHospDirectionAction,
			//sw.Promed.Actions.swMedPersonalWorkPlaceStacAction,
			//sw.Promed.Actions.swStacNurseWorkPlaceAction,
			//sw.Promed.Actions.swEvnPrescrViewJournalAction,
			//sw.Promed.Actions.swEvnPrescrCompletedViewJournalAction,
			{
				text: langs('Суициды'),
				iconCls: 'suicide-edit16',
				hidden: true,
				menu: new Ext.menu.Menu({
					items: [
				sw.Promed.Actions.swSuicideAttemptsEditAction,
				sw.Promed.Actions.swSuicideAttemptsFindAction
					]
				})
			}, /* {
				text: langs('Патоморфология'),
				iconCls: 'pathomorph-16',
				menu: new Ext.menu.Menu({
					items: [
					sw.Promed.Actions.EvnDirectionHistologicViewAction,
					sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
					sw.Promed.Actions.EvnHistologicProtoViewAction
					]
				})
			}, */
			//'-',
			sw.Promed.Actions.WindowsAction,
			'-',
			{
				text: langs('Помощь'),
				iconCls: 'help16',
				menu: this.menu_help
			},
			'-',
			{
				iconCls: 'user16',
				text: UserLogin,
				menu: this.user_menu
			},
		sw.Promed.Actions.PromedExit
			]
		}
		]);
	}

	if ( getRegionNick() != 'saratov') {
		if (!getRegionNick().inlist(['by'])) {
			main_menu_ribbon_items = main_menu_ribbon_items.concat([{
				title: langs('Параклиника'),
				id: '_menu_parka',
				iconCls: 'parka16',
				//hidden: !isAdmin && !isTestLpu,
				hidden: false,
				items:[{
					text: langs('Параклинические услуги'),
					iconCls: 'parka16',
					menu: new Ext.menu.Menu({
						items: [
						sw.Promed.Actions.swEvnUslugaParStreamAction,
						sw.Promed.Actions.swEvnUslugaParFindAction,
						sw.Promed.Actions.swEvnLabSampleDefectViewAction
						]
					})
				},
				//sw.Promed.Actions.swMedPersonalWorkPlaceParAction,
				'-',
				sw.Promed.Actions.WindowsAction,
				'-',
				{
					text: langs('Помощь'),
					iconCls: 'help16',
					menu: this.menu_help
				},
				'-',
				{
					iconCls: 'user16',
					text: UserLogin,
					menu: this.user_menu
				},
				sw.Promed.Actions.PromedExit
				]
			}]);
		}

		main_menu_ribbon_items = main_menu_ribbon_items.concat([{
				title: langs('Стоматология'),
				iconCls: 'stomat16',
				items:[{
						text: langs('Пациенты'),
						iconCls: 'patient16',
						menu: new Ext.menu.Menu({
								items: [
							sw.Promed.Actions.swEvnPLStomStreamAction,
							sw.Promed.Actions.swEvnPLStomSearchAction
								]
						})
				}, /*{
						text: langs('Справочники'),
						iconCls: 'sprav16',
						menu: new Ext.menu.Menu({
								items: [
							sw.Promed.Actions.swUslugaPriceListAction
								]
						})
				},*/
				'-',
			sw.Promed.Actions.WindowsAction,
				'-',
				{
						text: langs('Помощь'),
						iconCls: 'help16',
						menu: this.menu_help
				},
				'-',
				{
						iconCls: 'user16',
						text: UserLogin,
						menu: this.user_menu
				},
			sw.Promed.Actions.PromedExit
				]
		}]);
	}

	if ( getRegionNick() != 'saratov' ) {
		main_menu_ribbon_items = main_menu_ribbon_items.concat([
		{
			title: langs('Аптека'),
			iconCls: 'farmacy16',
			hidden: false,
			items:[
				sw.Promed.Actions.swContractorsSprAction,
				'-',
				//sw.Promed.Actions.swDokNakAction,
				//sw.Promed.Actions.EvnReceptProcessAction,
				sw.Promed.Actions.swDokUchAction,
				sw.Promed.Actions.swAktSpisAction,
				sw.Promed.Actions.swDokOstAction,
				sw.Promed.Actions.swInvVedAction,
				sw.Promed.Actions.swMedOstatAction,
				'-',
				//sw.Promed.Actions.swDokOstAction,
				//sw.Promed.Actions.swInvVedAction,
				//sw.Promed.Actions.swMedOstatAction,
				{
					text: langs('Рецепты'),
					hidden: !isAdmin,
					iconCls: 'receipt-process16',
					menu: new Ext.menu.Menu({
						items: [
						sw.Promed.Actions.swDokNakAction,
						sw.Promed.Actions.EvnReceptProcessAction,
						sw.Promed.Actions.EvnRPStreamInputAction,
						sw.Promed.Actions.EvnReceptTrafficBookViewAction
							]
					})
				},
				sw.Promed.Actions.PrepBlockCauseViewAction
				]
		}]);
	}

	if ( getRegionNick() != 'saratov' ) {
		main_menu_ribbon_items = main_menu_ribbon_items.concat([
		{
			title: langs('Документы'),
			id: '_menu_documents',
			iconCls: 'documents16',
			hidden: false,
			items: [
			sw.Promed.Actions.RegistryViewAction,
			sw.Promed.Actions.RegistryNewViewAction,
			sw.Promed.Actions.RegistryEUViewAction,
				'-',
				{
					text: langs('Патоморфология'),
					iconCls: 'pathomorph-16',
					//hidden: false,
					menu: new Ext.menu.Menu({
						items: [
						sw.Promed.Actions.EvnDirectionHistologicViewAction,
						sw.Promed.Actions.EvnHistologicProtoViewAction, 
							'-',
						sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
						sw.Promed.Actions.EvnMorfoHistologicProtoViewAction
						]
					})
				},
				{
					text: langs('Обращения'),
					hidden: !isAccessTreatment(),
					iconCls: 'petition-stream16',
					menu: new Ext.menu.Menu({
						items: [
						sw.Promed.Actions.swAttachmentDemandAction,
							'-',
						sw.Promed.Actions.swTreatmentStreamInputAction,
						sw.Promed.Actions.swTreatmentSearchAction,
						sw.Promed.Actions.swTreatmentReportAction
						]
					})
				},
				{
					text: langs('Свидетельства'),
					iconCls: 'medsvid16',
					hidden: !isMedSvidAccess() && !isSuperAdmin() && !isLpuAdmin(),
					menu: new Ext.menu.Menu({
						items: [
						sw.Promed.Actions.swMedSvidBirthAction,
						sw.Promed.Actions.swMedSvidDeathAction,
						sw.Promed.Actions.swMedSvidPDeathAction,
							'-',
						sw.Promed.Actions.swMedSvidPrintAction
						]
					})
				},

				{
					text: langs('Извещения о ДТП'),
					iconCls: 'stac-accident-injured16',
					hidden: ((!isSuperAdmin() && !isLpuAdmin()) || getRegionNick().inlist(['by,kz'])),
					menu: new Ext.menu.Menu({
						items: [
						sw.Promed.Actions.swEvnDtpWoundViewAction,
						sw.Promed.Actions.swEvnDtpDeathViewAction
						]
					})
				},
				'-',
			sw.Promed.Actions.swCardCallFindAction,
			sw.Promed.Actions.swCardCallStreamAction,			
				//'-',
			sw.Promed.Actions.KerRocordBookAction, 
				//'-',
				/*
				{
					text: langs('Карты вызова СМП'),
					iconCls: 'ambulance16',
					hidden: !isAdmin,
					menu: new Ext.menu.Menu({
						items: [
						sw.Promed.Actions.swCardCallViewAction,
						sw.Promed.Actions.swCardCallFindAction
						]
					})
				},*/
				
			sw.Promed.Actions.swJournalDirectionsAction,
				
			sw.Promed.Actions.EvnStickViewAction,
				'-',
			sw.Promed.Actions.WindowsAction,
				'-',
				{
					text: langs('Помощь'),
					iconCls: 'help16',
					menu: this.menu_help
				},
				'-',
				{
					iconCls: 'user16',
					text: UserLogin,
					menu: this.user_menu
				},
			sw.Promed.Actions.PromedExit
			]
		}]);
	}
	
	main_menu_ribbon_items = main_menu_ribbon_items.concat([
	{
		title: langs('Отчеты'),
		id: '_menu_reports',
		iconCls: 'reports16',
		items:[
	sw.Promed.Actions.ReportStatViewAction,
		{
			text: langs('Форма Ф14 ОМС'),
			hidden: getRegionNick().inlist(['by']),
			iconCls: 'rep-f14oms16',
			menu: new Ext.menu.Menu({
				items: [
				sw.Promed.Actions.swF14OMSPerAction,
				sw.Promed.Actions.swF14OMSAction,
				sw.Promed.Actions.swF14OMSFinAction
				]
			})
		},
	sw.Promed.Actions.swReportEngineAction,
		'-',
	sw.Promed.Actions.WindowsAction,
		'-',
		{
			text: langs('Помощь'),
			iconCls: 'help16',
			menu: this.menu_help
		},
		'-',
		{
			iconCls: 'user16',
			text: UserLogin,
			menu: this.user_menu
		},
	sw.Promed.Actions.PromedExit
		]
	}]);

	main_menu_ribbon_items = main_menu_ribbon_items.concat([
	{
		title: langs('Сервис'),
		id: '_menu_service',
		iconCls: 'service16',
		items: [
			sw.Promed.Actions.swUsersTreeViewAction,
			sw.Promed.Actions.swGroupsViewAction,
			{
				text: langs('МИАЦ'),
				hidden: (getRegionNick() != 'ufa'),
				iconCls: 'miac16',
				menu: new Ext.menu.Menu({
					items: [
					sw.Promed.Actions.MiacExportAction,
					sw.Promed.Actions.MiacExportSheduleOptionsAction
					]
				})
			},
			sw.Promed.Actions.swOptionsViewAction,
			sw.Promed.Actions.swNumeratorAction,
		   // sw.Promed.Actions.ConvertAction,
			/*{
				text: langs('Настройки'),
				iconCls: 'service16',
				menu: new Ext.menu.Menu({
					items: [
					sw.Promed.Actions.swOptionsViewAction,
					sw.Promed.Actions.swGlobalOptionAction
					]
				})
			}, {
					text: langs('Двойники'),
					iconCls: 'doubles16',
					hidden: !isAdmin,
					menu: new Ext.menu.Menu({
							items: [
						sw.Promed.Actions.PersonDoublesSearchAction,
						sw.Promed.Actions.PersonDoublesModerationAction,
						sw.Promed.Actions.PersonUnionHistoryAction
							]
					})
			},*/
		sw.Promed.Actions.swPersonSearchAction,
		sw.Promed.Actions.swImportAction,
		sw.Promed.Actions.swLpuSelectAction,
		//sw.Promed.Actions.swAdminWorkPlaceAction,
		//sw.Promed.Actions.swRegWorkPlaceAction,
		sw.Promed.Actions.swRrlExportWindowAction,
			{
				text: langs('Система'),
				iconCls: 'swan16',
				hidden: !isAdmin && !isTestLpu,
				menu: new Ext.menu.Menu({
					items: [
					sw.Promed.Actions.swDivCountAction,
					sw.Promed.Actions.loadLastObjectCode,
					'-',
					sw.Promed.Actions.swMSJobsAction,
					'-',
					sw.Promed.Actions.swRegistrationJournalSearchAction,
					sw.Promed.Actions.swAnalyzerWindowAction,
					'-',
					sw.Promed.Actions.ConvertAction,
					sw.Promed.Actions.swImportSMPCardsTest,
					sw.Promed.Actions.swLdapAttributeChangeAction,
					sw.Promed.Actions.swDicomViewerAction,
					'-',
					sw.Promed.Actions.TemplateRefValuesOpenAction,
					sw.Promed.Actions.TemplatesWindowTestAction,
					//sw.Promed.Actions.TemplatesEditWindowAction,
					sw.Promed.Actions.swXmlTemplateDebug,
					//sw.Promed.Actions.ReportDBStructureAction,
					//sw.Promed.Actions.swAssistantWorkPlaceAction,
					sw.Promed.Actions.swSelectWorkPlaceAction,
					sw.Promed.Actions.swTestAction
					]
				})
			},
			'-',
		sw.Promed.Actions.WindowsAction,
			'-',
			{
					text: langs('Помощь'),
					iconCls: 'help16',
					menu: this.menu_help
			},
			'-',
		sw.Promed.Actions.UserProfileAction,
		sw.Promed.Actions.MessageAction,
			'-',
			{
					iconCls: 'user16',
					text: UserLogin,
					menu: this.user_menu
			},
			'-',
		sw.Promed.Actions.PromedExit
		]
	}]);

	main_menu_ribbon = new sw.Promed.swTabToolbar (main_menu_ribbon_items);
	// End of main_menu_ribbon

	this.leftMenu = new Ext.Panel(
	{
		region: 'center',
		border: false,
		layout:'accordion',
		defaults:
		{
			bodyStyle: 'padding:5px'
		},
		layoutConfig:
		{
			titleCollapse: true,
			animate: true,
			activeOnTop: false
		},
		items:
		[{
			title: langs('Скорая помощь'),
			xtype: 'panel',
			border: false,
			items: [this.main_menu_ribbon],
			hidden: !isAdmin
		},
		{
			title: 'Panel 2',
			border: false,
			html: '<p>Panel content!</p>'
		},
		{
			title: 'Panel 3',
			border: false,
			html: '<p>Panel content!</p>'
		}]
	});

	var left_panel =
	{
		animCollapse: false,
		margins: '5 0 0 0',
		cmargins: '5 5 0 0',
		width: 200,
		minSize: 100,
		maxSize: 250,
		layout: 'border',
		id: 'left_panel',
		region: 'west',
		floatable: false,
		collapsible: true,
		listeners:
		{
			collapse: function()
			{
				main_center_panel.doLayout();
			},
			resize: function (p,nW, nH, oW, oH)
			{
				//main_center_panel.doLayout();
			}
		},
		border: true,
		title: langs('Меню'),
		/*iconCls: 'promed16',*/
		split: true,
		items: [/*{
			region: 'north',
			border: false
		}, */this.leftMenu] /*info*/
	};
	
	var main_menu_easy = new sw.Promed.Toolbar({
		autoHeight: true,
		region: 'north',
		items: [
			sw.Promed.Actions.swMedPersonalWorkPlaceAction,
			'-',
			sw.Promed.Actions.UserProfileAction,
			sw.Promed.Actions.MessageAction, // пока продублируем в меню тоже
			sw.Promed.Actions.swOptionsViewAction,
			'-',
			sw.Promed.Actions.swLpuSelectAction,
			sw.Promed.Actions.WindowsAction,
			'-',
			{
				text: langs('Помощь'),
				iconCls: 'help16',
				testId: 'mainmenu_help',
				menu: this.menu_help
			},
			{
				id: '_menu_easy_tbfill',
				xtype : "tbfill"
			},
			{
				iconCls: 'progress16',
				id: '_menu_easy_progress',
				text: '',
				hidden: true,
				tabIndex: -1
			},
			{
				iconCls: 'user16',
				text: UserLogin,
				menu: this.user_menu
			},
			'-',
			sw.Promed.Actions.PromedExit
		]
	});

	var main_menu_smo = new sw.Promed.Toolbar({
		autoHeight: true,
		region: 'north',
		items: [
			sw.Promed.Actions.swMedPersonalWorkPlaceAction,
			'-',
			{
				text:langs('Отчеты'),
				id: '_menu_reports',
				iconCls: 'reports16',
				menu: this.menu_reports,
				tabIndex: -1
			},
			'-',
			{
				text:langs('Помощь'),
				id: '_menu_help',
				iconCls: 'help16',
				menu: this.menu_help,
				tabIndex: -1
			},
			'-',
			{
				iconCls: 'user16',
				id: '_user_menu',
				text: UserLogin,
				menu: this.user_menu,
				tabIndex: -1
			},
			sw.Promed.Actions.PromedExit
		]
	});
	
	// Установка типа меню в зависимости от настройки пользователя
	if (Ext.globalOptions.appearance.menu_type == 'ribbon') {
		main_promed_toolbar = main_menu_ribbon;
	} else {
		main_promed_toolbar = main_menu_panel;
	}
	
	
	main_card_toolbar_active = 0;
	
	// Временно
	if( isLpuCadrAdmin() ) {
		main_card_toolbar_active = 1;
	}
	else if( isSmoTfomsUser() ) {
		main_card_toolbar_active = 2;
	}
	
	main_card_toolbar = new Ext.Panel({
		layout: 'card',
		region: 'north',
		cls: 'x-panel-mc',
		style: 'padding: 0px;',
		border: false,
		activeItem: main_card_toolbar_active,
		items: [
			main_promed_toolbar,
			main_menu_easy,
			main_menu_smo
		]
	});

	main_taskbar_panel = new Ext.Panel({
		id: 'ux-taskbar',
		layout: 'fit',
		region: 'south',
		hidden: true,
		autoWidth: true,
		html: '<div id="ux-taskbuttons-panel"></div><div class="x-clear"></div>'
	});

	main_top_panel = new Ext.Panel({
		id: 'main_top_panel',
		layout: 'fit',
		tbar: main_card_toolbar,
		items: [
			main_taskbar_panel
		],
		region: 'center'
	});
	
	//main_promed_toolbar.setVisible( String(getGlobalOptions().groups).indexOf('LpuCadrAdmin') == 1 );
	if (!Ext.isEmpty(HIDE_MENU_ON_ARMS) && HIDE_MENU_ON_ARMS == 1 && !isSuperAdmin() && !isLpuAdmin() && !Ext.isEmpty(main_card_toolbar))
	{
		main_card_toolbar.setVisible(false);
	}
	// центральная панель
	main_center_panel = new Ext.Panel({
		id: 'main-center-panel',
		layout: 'fit',
		tbar: main_top_panel,
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
				
	log(langs('Подключаем плагин КриптоПро'));
	sw.Applets.CryptoPro.initCryptoPro();

	if(Ext.globalOptions.others.enable_uecreader) {
		log(langs('Подключаем апплет УЭК'));
		sw.Applets.uec.initUec();
	}
	if(Ext.globalOptions.others.enable_bdzreader) {
		log(langs('Подключаем апплет BDZ'));
		sw.Applets.bdz.initBdz();
	}

	if ( Ext.globalOptions.others.enable_barcodereader ) {
		log(langs('Подключаем апплет для сканера штрих-кодов'));
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
				main_menu_easy.setWidth('auto');
				main_menu_smo.setWidth('auto');
				main_messages_panel.hideOver(main_messages_panel.mLeft);
			}
		}
	});

	if (Ext.globalOptions.appearance.menu_type == 'ribbon' && typeof main_promed_toolbar.delegateUpdates == 'function') {
		main_promed_toolbar.delegateUpdates();
	}

	if (getAppearanceOptions().taskbar_enabled) {
		taskbar = new Ext.ux.TaskBar();
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
	mask.setStyle('z-index', sw.WindowMgr.zseed + 10000);
	// log(sw.WindowMgr.zseed);
	// log(sw.WindowMgr);
	sw.Promed.mask = new Ext.LoadMask(Ext.getBody(), {msg: LOAD_WAIT});
	sw.Promed.mask.hide();
	
	Ext.Ajax.timeout = 1800000;
	
	// Значения по умолчанию
	loadPromed( function() {
		// в качестве теста nodejs повесим emit на промед
		testSocketNodejs();
		// Инициализация всплывыющих подсказок
		Ext.QuickTips.init();
		
		// http://172.19.61.24:85/issues/show/2264
		function unload_page(event) {
		sw.Applets.BarcodeScaner.stopBarcodeScaner();
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

		// TAG: Старт тасков
		if (getNoticeOptions().is_popup_message || getNoticeOptions().is_infopanel_message) {
			this.taskTimer = function() {
				return {run: taskRun, interval: ((getGlobalOptions().message_time_limit)?getGlobalOptions().message_time_limit:5)*2*1000};
			}
			sw.Promed.tasks.start(this.taskTimer());
		}
		if(getNoticeOptions().is_extra_message) {
			this.extraTaskTimer = function() {
				return {run: extraTaskRun, interval: 300*1000};
			}
			sw.Promed.tasks.start(this.extraTaskTimer());
		}

		//Подключению к сокету на NodeJS
		if (getGlobalOptions().NodeJSControl && getGlobalOptions().NodeJSControl.enable) {
			sw.Promed.socket = connectSocket();
		}
		
		warnNeedChangePassword({
			callback: function() {
				if (isSuperAdmin() && Ext.isRemoteDB && getGlobalOptions().mongoDBVersion == 0) {
					getWnd('swDBLocalVersionWindow').show();
					showSysMsg(langs('Необходимо собрать новую версию локальных справочников!'));
					return;
				}

				if ( getGlobalOptions().lpu ) {
					if ( getGlobalOptions().lpu.length>1 ) { // Выбор МО в случае, если их несколько у пользователя
						getWnd('swSelectLpuWindow').show( {params : getGlobalOptions().lpu} );
					} else {
						if ( getGlobalOptions().lpu.length==1 ) {
							// Если у пользователя только 1 МО, то загрузким данные по этой МО
							loadGlobalStores({
								callback: function () {
									// Открытие АРМа по умолчанию
									sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
								}
							});
							getCountNewDemand();
						}
					}
				} else {
					// У пользователя нет ни одной МО, загружать нечего не нужно :)
					// Открытие АРМа по умолчанию для пользователя организации
					sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
				}

				if ( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ) {
					getWnd('swSelectLpuWindow').show({});
				}
			}
		});


		//Явный сброс сессии с клиента ( после 2х часов бездействия )
		ConnectionDestroyer = Ext.extend(Ext.util.Observable, {
			constructor: function(config){

				this.disconnectTimer = 0;
				this.disconnectTime  = DisconnectOnInactivityTime * 60 * 1000;

				var disconnectTime  = this.disconnectTime;
				var disconnectTimer = this.disconnectTimer;

				Ext.select('body').on({

					"click": function () {
						sw.Promed.GlobalVariables.statusInactivity = false;
						disconnectTimer = 0;
					},

					"keydown": function () {
						sw.Promed.GlobalVariables.statusInactivity = false;
						disconnectTimer = 0;
					}
				});

				this.interval = setInterval(function() {
					disconnectTimer += 60000;

					if(disconnectTimer >= disconnectTime) {

						if(sw.Promed.GlobalVariables.statusInactivity) {
							window.onbeforeunload = false;
							window.location = "?c=main&m=Logout";
						}
					}
					sw.Promed.GlobalVariables.statusInactivity = true;
				}, 60000);//5 с. было слишком мало чтоб считать пользователя
					// неактивным. В результате могла получиться ситуация когда
					// в других таймерах система всегда оказывается неактивной.

				ConnectionDestroyer.superclass.constructor.call(this, config)
			}
		});

		ConnectionDestroy = new ConnectionDestroyer();

		if (getRegionNick() != 'kz') {
			// Инициализация видеосвязи
			Ext6.require('videoChat.lib.Engine');
		}

		if (!Ext.isEmpty(getGlobalOptions().confluenceauthpath)) {
			var confWindow = window.open(getGlobalOptions().confluenceauthpath, '_blank', "menubar=no,toolbar=no,width=1,height=1");
			setTimeout(function() {
				confWindow.close();
			}, 3000);
		}
	});
});

function testSocketNodejs(){
    var region = getRegionNick();
    var regionName = ['perm', 'ufa2'];
    if(regionName.indexOf(region) < 0) return;

    var opts = getGlobalOptions();

	if (!opts.smp || !opts.smp.NodeJSSocketConnectHost) {
		return false;
	}

	var socket = io(opts.smp.NodeJSSocketConnectHost);
    socket.on('connect', function () {
		console.log('SOCKET.IO NodeJS');
		socket.on('authentification', function (callback) {
			callback(document.cookie, opts.pmuser_id, navigator.userAgent, opts.region.nick);
		});
		socket.emit('region', region);
    });
    socket.on('connect_error',function(){
		console.log('отсутствует соединение с NodeJS');
    });
    socket.on('disconnect', function () {
		console.log('обрыв соединения с NodeJS');
    });
};

/******* _onChange_cbWorkShift *************************************************
 * #175117
 * Обработчик щелчка по флагу "Я на смене".
 * Открывает форму ввода данных о текущей смене.
 ******************************************************************************/
function _onChange_cbWorkShift()
{
	var g = getGlobalOptions(),
		wnd = getWnd('swTimeJournalEditWindow');

	wnd.show(
		{
			pmUser_id: g.pmuser_id,
			lpu_id: g.lpu_id
		});
};

/******* _onRender_cbWorkShift *************************************************
 * #175117
 * Обработчик отрисовки флага "Я на смене".
 * Текст должен быть синего цвета (slateblue) и должен быть расположен на одном
 * уровне с пунктами главного меню.
 ******************************************************************************/
function _onRender_cbWorkShift(cbWorkShift)
{
	cbWorkShift.labelEl.applyStyles('color: slateblue; vertical-align: top;');
};
