/**
 * Загрузчик модуля Аптеки
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Init
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Swan Coders
 * @version      23.01.2010
 */

Ext.ns('sw.codeInfo');
Ext.ns('sw.notices');
Ext.ns('sw.adminnotices');
sw.codeInfo = {};
sw.notices = [];
sw.adminnotices = [];
sw.firstadminnotice = 1;
sw.isExt6Menu = true;
var taskbar = null;

var is_ready = false;
var swSelectFarmacyWindow = null;
var swUsersTreeViewWindow = null;
var isFarmacyInterface = true;
var isTestLpu = (UserLogin=='testpol');

function loadWindowModule()
{
    is_ready = true;

	isAdmin = (/SuperAdmin/.test(getGlobalOptions().groups));
	isFarmacyInterface = false;
	var win = this;
	// Конфиги акшенов
	sw.Promed.Actions = {
		swEvnPLEvnPSSearchAction: {
			text: langs('Поиск ТАП/КВС'),
			iconCls: 'test16',
			handler: function () {
				getWnd('swEvnPLEvnPSSearchWindow').show();
			},
			hidden: false
		},
		swEvnPLEvnPSViewAction: {
			text: langs('Выбор ТАП/КВС'),
			iconCls: 'test16',
			handler: function () {
				getWnd('swEvnPLEvnPSSearchWindow').show({
					Person_id: 421380
				});
			},
			hidden: false
		},
		EvnDirectionMorfoHistologicViewAction: {
			text: langs('Направления на патоморфогистологическое исследование'),
			iconCls: 'pathomorph16',
			handler: function () {
				getWnd('swEvnDirectionMorfoHistologicViewWindow').show();
			},
			hidden: false
		},
		EvnStickViewAction: {
			text: langs('ЛВН: Поиск'),
			iconCls: 'lvn-search16',
			handler: function () {
				getWnd('swEvnStickViewWindow').show();
			},
			hidden: false //(!isSuperAdmin() || IS_DEBUG != 1)
		},
		EvnMorfoHistologicProtoViewAction: {
			text: langs('Протоколы патоморфогистологических исследований'),
			iconCls: 'pathomorph16',
			handler: function () {
				getWnd('swEvnMorfoHistologicProtoViewWindow').show();
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
		EvnHistologicProtoViewAction: {
			text: langs('Протоколы патологогистологических исследований'),
			iconCls: 'pathohistproto16',
			handler: function () {
				getWnd('swEvnHistologicProtoViewWindow').show();
			},
			hidden: false
		},
		EvnDirectionHistologicViewAction: {
			text: langs('Направления на патологогистологическое исследование'),
			iconCls: 'pathohist16',
			handler: function () {
				getWnd('swEvnDirectionHistologicViewWindow').show();
			},
			hidden: false
		},
		PersonDoublesSearchAction: {
			text: langs('Работа с двойниками'),
			iconCls: 'doubles16',
			handler: function () {
				getWnd('swPersonDoublesSearchWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		PersonDoublesModerationAction: {
			text: langs('Модерация двойников'),
			iconCls: 'doubles-mod16',
			handler: function () {
				var params = {};
				if (!isSuperAdmin() && (isLpuAdmin() && isUserGroup('106'))) {
					params.LpuOnly = true;
				}
				getWnd('swPersonDoublesModerationWindow').show(params);
			},
			hidden: !(isSuperAdmin() || (isLpuAdmin() && isUserGroup('106')))
		},
		PersonUnionHistoryAction: {
			text: langs('История модерации двойников'),
			iconCls: 'doubles-history16',
			handler: function () {
				getWnd('swPersonUnionHistoryWindow').show();
			},
			hidden: true
		},
		UslugaComplexViewAction: {
			text: langs('Комплексные услуги'),
			iconCls: 'services-complex16',
			handler: function () {
				getWnd('swUslugaComplexViewWindow').show();
			},
			hidden: true
		},
		UslugaComplexTreeAction: {
			text: langs('Комплексные услуги'),
			iconCls: 'services-complex16',
			handler: function () {
			},
			hidden: true
		},
		RegistryViewAction: {
			text: langs('Реестры счетов'),
			iconCls: 'service-reestrs16',
			handler: function () {
				getWnd('swRegistryViewWindow').show({Registry_IsNew: 1});
			},
			hidden: (
				getRegionNick().inlist(['by'])
				|| (
					!getRegionNick().inlist(['krasnoyarsk','ufa'])
					&& !isUserGroup(['RegistryUser', 'RegistryUserReadOnly'])
					&& !isSuperAdmin()
				)
				|| (
					getRegionNick().inlist(['ufa'])
					&& !isUserGroup(['LpuPowerUser'])
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
			},
			hidden: !(getRegionNick() == 'penza' && (isUserGroup('RegistryUser') || isUserGroup('RegistryUserReadOnly')))
		},
		RegistryEUViewAction: {
			text: 'Реестры внешних услуг',
			iconCls: 'service-reestrs16',
			handler: function () {
				getWnd('swRegistryEUSearchWindow').show();
			},
			hidden: true // !((getRegionNick() == 'perm') && (isSuperAdmin() || isUserGroup(['RegistryUser', 'RegistryUserReadOnly'])))
		},
		TariffVolumeViewAction: {
			text: langs('Тарифы и объемы'),
			// iconCls : 'service-reestrs16',
			handler: function () {
				getWnd('swTariffVolumesViewWindow').show();
			},
			hidden: (getRegionNick() != 'perm')
		},
		RegistryNewViewAction: {
			text: langs('Реестры счетов (новые)'),
			iconCls: 'service-reestrs16',
			handler: function () {
				if ( getRegionNick() == 'vologda' ) {
					getWnd('swRegistryNewViewWindow').show();
				}
				else {
					getWnd('swRegistryViewWindow').show({Registry_IsNew: 2});
				}
			},
			hidden: (
				!getRegionNick().inlist([ 'ufa', 'vologda' ])
				|| (
					!getRegionNick().inlist(['ufa'])
					&& !isUserGroup(['RegistryUser', 'RegistryUserReadOnly'])
					&& !isSuperAdmin()
				)
				|| (
					getRegionNick().inlist(['ufa'])
					&& !isUserGroup(['LpuPowerUser'])
					&& !isSuperAdmin()
				)
			)
		},
		MiacExportAction: {
			text: langs('Выгрузка для МИАЦ'),
			iconCls: 'service-reestrs16',
			handler: function () {
				getWnd('swMiacExportWindow').show();
			},
			hidden: (getRegionNick() != 'ufa')
		},
		MiacExportSheduleOptionsAction: {
			text: langs('Настройки автоматической выгрузки для МИАЦ'),
			iconCls: 'settings16',
			handler: function () {
				getWnd('swMiacExportSheduleOptionsWindow').show();
			},
			hidden: (getRegionNick() != 'ufa')
		},
		/*RegistryEditAction: {
			text: langs('Редактирование реестра (счета)'),
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
			iconCls: 'drug-viewtorg16',
			handler: function () {
				getWnd('swDrugTorgViewWindow').show();
			},
			hidden: (getRegionNick() == 'saratov' || getRegionNick() == 'pskov')
		},

		DrugMnnLatinNameEditAction: {
			text: WND_DLO_DRUGMNNLATINEDIT,
			iconCls: 'drug-viewmnn16',
			handler: function () {
				getWnd('swDrugMnnViewWindow').show({
					privilegeType: 'all'
				});
			},
			hidden: (getRegionNick() == 'saratov' || getRegionNick() == 'pskov')
		},

		PersonCardSearchAction: {
			text: WND_POL_PERSCARDSEARCH,
			iconCls: 'card-search16',
			handler: function () {
				getWnd('swPersonCardSearchWindow').show();
			}
		},

		PersonCardViewAllAction: {
			text: WND_POL_PERSCARDVIEWALL,
			iconCls: 'card-view16',
			handler: function () {
				getWnd('swPersonCardViewAllWindow').show();
			}
		},

		PersonCardStateViewAction: {
			text: WND_POL_PERSCARDSTATEVIEW,
			iconCls: 'card-state16',
			handler: function () {
				getWnd('swPersonCardStateViewWindow').show();
			}
		},

		AutoAttachViewAction: {
			text: 'Групповое прикрепление',
			iconCls: 'card-state16',
			hidden: true,
			handler: function () {
				var id_salt = Math.random();
				var win_id = 'report' + Math.floor(id_salt * 10000);
				// собственно открываем окно и пишем в него
				var win = window.open('/?c=AutoAttach&m=doAutoAttach', win_id);
			}
		},

		PersonDispSearchAction: {
			text: WND_POL_PERSDISPSEARCH,
			iconCls: 'disp-search16',
			handler: function () {
				getWnd('swPersonDispSearchWindow').show();
			},
			hidden: false//!(isSuperAdmin() || isTestLpu)
		},
		PersonDispViewAction: {
			text: WND_POL_PERSDISPSEARCHVIEW,
			iconCls: 'disp-view16',
			handler: function () {
				getWnd('swPersonDispViewWindow').show({mode: 'view'});
			},
			hidden: false//!(isSuperAdmin() || isTestLpu)
		},
		EvnPLEditAction: {
			text: langs('Талон амбулаторного пациента: Поиск'),
			iconCls: 'pol-eplsearch16',
			handler: function () {
				getWnd('swEvnPLSearchWindow').show();
			}
		},
		EvnPLStreamInputAction: {
			text: MM_POL_EPLSTREAM,
			iconCls: 'pol-eplstream16',
			handler: function () {
				getWnd('swEvnPLStreamInputWindow').show();
			}
		},

		LpuStructureViewAction: {
			text: langs('Структура МО'),
			iconCls: 'lpu-struc16',
			hidden: !isSuperAdmin() && !isLpuAdmin() && !isCadrUserView() && !isRegAdmin() && !isUserGroup('OuzSpec'),
			handler: function () {
				getWnd('swLpuStructureViewForm').show();
			}
		},

		OrgStructureViewAction: {
			text: langs('Структура организации'),
			iconCls: 'lpu-struc16',
			hidden: (!isSuperAdmin() && !isOrgAdmin()) || !isDebug(),
			handler: function () {
				getWnd('swOrgStructureWindow').show();
			}
		},

		FundHoldingViewAction: {
			text: langs('Фондодержание'),
			iconCls: 'lpu-struc16',
			hidden: (!isSuperAdmin() || getRegionNick().inlist(['by'])),//&& !getGlobalOptions()['mp_is_zav'] && !getGlobalOptions()['mp_is_uch'],
			handler: function () {
				getWnd('swFundHoldingViewForm').show();
			}
		},

		LgotFindAction: {
			text: MM_DLO_LGOTSEARCH,
			iconCls: 'lgot-search16',
			handler: function () {
				getWnd('swPrivilegeSearchWindow').show();
			}
		},
		LgotAddAction: {
			text: MM_DLO_LGOTADD,
			iconCls: 'x-btn-text',
			handler: function () {
				if (getWnd('swPersonSearchWindow').isVisible()) {
					Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
					return false;
				}

				if (getWnd('swPrivilegeEditWindow').isVisible()) {
					Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования льготы уже открыто'));
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function (person_data) {
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
			iconCls: 'udost-list16',
			handler: function () {
				getWnd('swUdostViewWindow').show();
			}
		},
		EvnUdostAddAction: {
			text: MM_DLO_UDOSTADD,
			iconCls: 'x-btn-text',
			handler: function () {
				if (getWnd('swPersonSearchWindow').isVisible()) {
					Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
					return false;
				}

				if (getWnd('swEvnUdostEditWindow').isVisible()) {
					Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования удостоверения уже открыто'));
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function (person_data) {
						getWnd('swEvnUdostEditWindow').show({
							action: 'add',
							onHide: function () {
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
			iconCls: 'receipt-stream16',
			hidden: (
				getGlobalOptions().region && getRegionNick() == 'saratov'
				&& !(isSuperAdmin() || isLpuAdmin() || isUserGroup('LpuUser'))
			),
			handler: function () {
				getWnd('swReceptStreamInputWindow').show();
			}
		},
		EvnReceptAddAction: {
			text: MM_DLO_RECADD,
			iconCls: 'x-btn-text',
			handler: function () {
				if (getWnd('swPersonSearchWindow').isVisible()) {
					Ext.Msg.alert(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
					return false;
				}

				if (getWnd('swEvnReceptEditWindow').isVisible()) {
					Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования рецепта уже открыто'));
					return false;
				}

				getWnd('swPersonSearchWindow').show({
					onSelect: function (person_data) {
						getWnd('swEvnReceptEditWindow').show({
							action: 'add',
							onHide: function () {
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
			iconCls: 'receipt-search16',
			handler: function () {
				getWnd('swEvnReceptSearchWindow').show();
			}
		},
		EvnReceptInCorrectFindAction: {
			text: langs('Журнал отсрочки'),
			iconCls: 'receipt-incorrect16',
			handler: function () {
				getWnd('swReceptInCorrectSearchWindow').show();
			}
		},
		PersonPrivilegeWOWSearchAction: {
			text: (getRegionNick().inlist(['ufa', 'ekb', 'penza'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поиск') : langs('Регистр ВОВ: Поиск'),
			iconCls: 'dopdisp-search16', // to-do: Поменять иконку
			handler: function () {
				getWnd('swPersonPrivilegeWOWSearchWindow').show();
			}
		},
		PersonPrivilegeWOWStreamInputAction: {
			text: (getRegionNick().inlist(['ufa', 'ekb', 'penza'])) ? langs('Регистр инвалидов, подлежащих ДВН: Поточный ввод') : langs('Регистр ВОВ: Поточный ввод'),
			iconCls: 'dopdisp-search16', // to-do: Поменять иконку
			handler: function () {
				getWnd('swPersonPrivilegeWOWStreamInputWindow').show();
			}
		},
		PersonDispWOWStreamAction: {
			text: langs('Обследования ВОВ: Поточный ввод'),
			iconCls: 'dopdisp-search16', // to-do: Поменять иконку
			handler: function () {
				getWnd('EvnPLWOWStreamWindow').show();
			}
		},
		PersonDispWOWSearchAction: {
			text: langs('Обследования ВОВ: Поиск'),
			iconCls: 'dopdisp-search16', // to-do: Поменять иконку
			handler: function () {
				getWnd('EvnPLWOWSearchWindow').show();
			}
		},
		PersonDopDispSearchAction: {
			text: MM_POL_PERSDDSEARCH,
			iconCls: 'dopdisp-search16',
			hidden: getRegionNick().inlist(['adygeya']),
			handler: function () {
				getWnd('swPersonDopDispSearchWindow').show();
			}
		},
		PersonDopDispStreamInputAction: {
			text: MM_POL_PERSDDSTREAMINPUT,
			iconCls: 'dopdisp-stream16',
			handler: function () {
				getWnd('swPersonDopDispSearchWindow').show({mode: 'stream'});
			}
		},
		EvnPLDopDispSearchAction: {
			text: langs('Талон по дополнительной диспансеризации взрослых (до 2013г.): поиск'),
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispDopSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['adygeya'])
		},
		EvnPLDopDispStreamInputAction: {
			text: MM_POL_EPLDDSTREAM,
			iconCls: 'dopdisp-epl-stream16',
			handler: function () {
				getWnd('swEvnPLDispDopSearchWindow').show({mode: 'stream'});
			},
			hidden: false //!isSuperAdmin()
		},
		PersonDispOrpSearchAction: {
			text: langs('Регистр детей-сирот (стационарных): Поиск'),
			iconCls: 'dopdisp-search16',
			handler: function () {
				getWnd('swPersonDispOrp13SearchWindow').show({
					CategoryChildType: 'orp'
				});
			},
			hidden: false //!isSuperAdmin()
		},
		PersonDispOrpAdoptedSearchAction: {
			text: langs('Регистр детей-сирот (усыновленных/опекаемых): Поиск'),
			iconCls: 'dopdisp-search16',
			handler: function () {
				getWnd('swPersonDispOrp13SearchWindow').show({
					CategoryChildType: 'orpadopted'
				});
			},
			hidden: false //!isSuperAdmin()
		},
		PersonDispOrpPeriodSearchAction: {
			text: langs('Регистр периодических осмотров несовершеннолетних: Поиск'),
			iconCls: 'dopdisp-search16',
			handler: function () {
				getWnd('swPersonDispOrpPeriodSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['adygeya', 'yakutiya'])
		},
		EvnPLDispTeenInspectionSearchAction: {
			text: langs('Периодические осмотры несовершеннолетних: Поиск'),
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispTeenInspectionSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['adygeya', 'yakutiya'])
		},
		PersonDispOrpProfSearchAction: {
			text: langs('Направления на профилактические осмотры несовершеннолетних: Поиск'),
			iconCls: 'dopdisp-search16',
			handler: function () {
				getWnd('swPersonDispOrpProfSearchWindow').show();
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispTeenInspectionProfSearchAction: {
			text: langs('Профилактические осмотры несовершеннолетних - 1 этап: Поиск'),
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispTeenInspectionProfSearchWindow').show();
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispTeenInspectionProfSecSearchAction: {
			text: langs('Профилактические осмотры несовершеннолетних - 2 этап: Поиск'),
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispTeenInspectionProfSecSearchWindow').show();
			},
			hidden: false //!isSuperAdmin()
		},
		PersonDispOrpPredSearchAction: {
			text: langs('Направления на предварительные осмотры несовершеннолетних: Поиск'),
			iconCls: 'dopdisp-search16',
			handler: function () {
				getWnd('swPersonDispOrpPredSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['adygeya', 'yakutiya'])
		},
		EvnPLDispTeenInspectionPredSearchAction: {
			text: langs('Предварительные осмотры несовершеннолетних - 1 этап: Поиск'),
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispTeenInspectionPredSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['adygeya', 'yakutiya'])
		},
		EvnPLDispTeenInspectionPredSecSearchAction: {
			text: langs('Предварительные осмотры несовершеннолетних - 2 этап: Поиск'),
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispTeenInspectionPredSecSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['adygeya', 'yakutiya'])
		},
		EvnPLDispTeenExportAction: {
			text: langs('Экспорт карт по диспансеризации несовершеннолетних'),
			iconCls: 'database-export16',
			handler: function () {
				getWnd('swEvnPLDispTeenExportWindow').show();
			},
			hidden: false
		},
		EvnPLDispOrpSearchAction: {
			text: langs('Карта диспансеризации несовершеннолетнего - 1 этап: Поиск'),
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispOrp13SearchWindow').show({
					stage: 1
				});
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispOrpSecSearchAction: {
			text: langs('Карта диспансеризации несовершеннолетнего - 2 этап: Поиск'),
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispOrp13SearchWindow').show({
					stage: 2
				});
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispDop13SearchAction: {
			text: MM_POL_EPLDD13SEARCH,
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispDop13SearchWindow').show();
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispProfSearchAction: {
			text: MM_POL_EPLDPSEARCH,
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispProfSearchWindow').show();
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispScreenSearchAction: {
			text: MM_POL_EPLDSSEARCH,
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispScreenSearchWindow').show();
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispScreenChildSearchAction: {
			text: MM_POL_EPLDSCSEARCH,
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispScreenChildSearchWindow').show();
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispDop13SecondSearchAction: {
			text: MM_POL_EPLDD13SECONDSEARCH,
			iconCls: 'dopdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispDop13SecondSearchWindow').show();
			},
			hidden: false //!isSuperAdmin()
		},
		EvnPLDispTeen14SearchAction: {
			text: langs('Диспансеризация 14-летних подростков: Поиск'),
			iconCls: 'dopdisp-teens-search16',
			handler: function () {
				getWnd('swEvnPLDispTeen14SearchWindow').show();
			},
			hidden: false
		},
		EvnPLDispTeen14StreamInputAction: {
			text: langs('Диспансеризация 14-летних подростков: Поточный ввод'),
			iconCls: 'dopdisp-teens-stream16',
			handler: function () {
				getWnd('swEvnPLDispTeen14SearchWindow').show({mode: 'stream'});
			},
			hidden: false
		},
		/*EvnPLDispSomeAdultSearchAction: {
			text: langs('Диспансеризация отдельных групп взрослого населения: Поиск'),
			hidden: !(getGlobalOptions().region && getRegionNick() == 'ufa'),
			iconCls : 'dopdisp-teens-search16',
			handler: function()
			{
				getWnd('swEvnPLDispSomeAdultSearchWindow').show();
			}
		},
		EvnPLDispSomeAdultStreamInputAction: {
			text: langs('Диспансеризация отдельных групп взрослого населения: Поточный ввод'),
			hidden: !(getGlobalOptions().region && getRegionNick() == 'ufa'),
			iconCls : 'dopdisp-teens-stream16',
			handler: function()
			{
				getWnd('swEvnPLDispSomeAdultStreamInputWindow').show();
			}
		},*/
		ReestrsViewAction: {
			text: langs('Реестры счетов'),
			iconCls: 'service-reestrs16',
			handler: function () {
				Ext.Msg.alert(langs('Сообщение'), langs('Данный модуль пока недоступен!'));
			}
		},
		DrugRequestEditAction: {
			text: langs('Заявка на лекарственные средства: Ввод'),
			iconCls: 'x-btn-text',
			handler: function () {
				getWnd('swDrugRequestEditForm').show({mode: 'edit'});
			},
			hidden: (IS_DEBUG != 1)
		},
		DrugRequestViewAction: {
			text: langs('Заявка на ЛС по общетерапевтической группе заболеваний'),
			iconCls: 'drug-request16',
			handler: function () {
				getWnd('swDrugRequestViewForm').show();
			},
			hidden: (getRegionNick() != 'perm')
		},
		/*NewDrugRequestViewAction: {
			text: (getGlobalOptions().region && getRegionNick()=='saratov')?langs('Заявка на лекарственные средства'):langs('Заявка на ЛС по особым группам заболеваний'),
			iconCls : 'drug-request16',
			handler: function()
			{
				getWnd('swNewDrugRequestViewForm').show();
			},
			hidden: (getRegionNick() == 'saratov'||getRegionNick() == 'pskov')
		},*/
		OrgFarmacyViewAction: {
			text: MM_DLO_OFVIEW,
			iconCls: 'farmview16',
			handler: function () {
				getWnd('swOrgFarmacyViewWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		OstAptekaViewAction: {
			text: MM_DLO_MEDAPT,
			iconCls: 'drug-farm16',
			handler: function () {
				getWnd('swDrugOstatByFarmacyViewWindow').show();
			}
		},
		OstSkladViewAction: {
			text: MM_DLO_MEDSKLAD,
			iconCls: 'drug-sklad16',
			handler: function () {
				getWnd('swDrugOstatBySkladViewWindow').show();
			}
		},
		OstDrugViewAction: {
			text: MM_DLO_MEDNAME,
			iconCls: 'drug-name16',
			handler: function () {
				getWnd('swDrugOstatViewWindow').show();
			}
		},
		ReportStatViewAction: {
			text: langs('Статистическая отчетность'),
			iconCls: 'reports16',
			hidden: false,
			handler: function () {
				// Пример предварительной загрузки блока кода
				if (sw.codeInfo.loadEngineReports) {
					getWnd('swReportEndUserWindow').show();
				}
				else {
					Ext.Ajax.request({ //Если список АРМов для пользователя не подгрузился, то отображаются все отчеты, независимо от прав пользователя.
						// Поэтому сделал принудительный вызов метода перед открытием отчетов http://redmine.swan.perm.ru/issues/35151
						success: function (response, options) {
							getWnd('reports').load(
								{
									callback: function (success) {
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
			iconCls: 'test16',
			hidden: ((IS_DEBUG != 1) || (getRegionNick() == 'saratov')),
			handler: function () {
				getWnd('swTestEventsWindow').show();
			}
		},
		TemplatesWindowTestAction: {
			text: langs('Тест шаблонов'),
			iconCls: 'test16',
			hidden: true,
			handler: function () {
			}
		},
		TemplatesEditWindowAction: {
			text: langs('Редактор шаблонов'),
			iconCls: 'test16',
			hidden: true,
			handler: function () {
			}
		},
		TemplateRefValuesOpenAction: {
			text: langs('База референтных значений'),
			iconCls: 'test16',
			hidden: !isSuperAdmin(),
			handler: function () {
				getWnd('swTemplateRefValuesViewWindow').show();
			}
		},
		GlossarySearchAction: {
			text: langs('Глоссарий'),
			iconCls: 'glossary16',
			//hidden: false,
			hidden: getRegionNick().inlist(['saratov', 'by']),
			handler: function () {
				getWnd('swGlossarySearchWindow').show();
			}
		},
		ReportDBStructureAction: {
			text: langs('Структура БД'),
			iconCls: 'test16',
			hidden: ((!isSuperAdmin()) && (!IS_DEBUG)),
			handler: function () {
				getWnd('swReportDBStructureOptionsWindow').show();
			}
		},
		UserProfileAction: {
			text: langs('Мой профиль'),
			iconCls: 'user16',
			hidden: false,
			handler: function () {
				args = {}
				args.action = 'edit';
				getWnd('swUserProfileEditWindow').show(args);
			}
		},
		PromedHelp: {
			text: langs('Вызов справки'),
			iconCls: 'help16',
			handler: function () {
				ShowHelp(langs('Содержание'));
			}
		},
		PromedForum: {
			text: langs('Форум поддержки'),
			iconCls: 'support16',
			xtype: 'tbbutton',
			handler: function () {
				window.open(ForumLink);
			}
		},
		swShowTestWindowAction: {
			text: langs('Тестовое окно'),
			iconCls: 'test16',
			handler: function () {
				//getWnd('swTestWindow').show();
				getWnd('swWorkPlaceWindow').show();
			},
			hidden: !isSuperAdmin() || !isDebug()
		},
		PromedAbout: {
			text: langs('О программе'),
			iconCls: 'promed16',
			testId: 'mainmenu_help_about',
			handler: function () {
				getWnd('swAboutWindow').show();
			}
		},
		PromedExit: {
			text: langs('Выход'),
			iconCls: 'exit16',
			handler: function () {
				Ext6.Msg.show({
					title: langs('Подтвердите выход'),
					msg: langs('Вы действительно хотите выйти?'),
					buttons: Ext6.Msg.YESNO,
					fn: function (buttonId) {
						if (buttonId == 'yes') {
							window.onbeforeunload = null;
							window.location = C_LOGOUT;
						}
					}
				});
			}
		},
		ConvertAction: {
			text: langs('Конвертация полей'),
			iconCls: 'eph16',
			handler: function () {
				getWnd('swConvertEditWindow').show();
			},
			hidden: ((IS_DEBUG != 1) || (getRegionNick().inlist(['saratov', 'by'])))
		},
		swLdapAttributeChangeAction: {
			text: langs('Замена атрибута в LDAP'),
			iconCls: 'eph16',
			handler: function () {
				getWnd('swLdapAttributeChangeWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		swImportSMPCardsTest: {
			text: langs('Тест импорта карт СМП'),
			iconCls: 'eph16',
			handler: function () {
				getWnd('swImportSMPCardsTestWindow').show();
			},
			hidden: (!isSuperAdmin() || getRegionNick().inlist(['by']))
		},
		swDicomViewerAction: {
			text: langs('Просмотрщик Dicom'),
			iconCls: 'eph16',
			handler: function () {
				getWnd('swDicomViewerWindow').show();
			},
			hidden: (IS_DEBUG != 1 || !isSuperAdmin())
		},
		TestAction: {
			text: langs('Тест (только на тестовом)'),
			iconCls: 'eph16',
			handler: function () {
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
				getWnd('swEvnUslugaOrderEditWindow').show({LpuSection_id: 10});
			},
			hidden: ((IS_DEBUG != 1) || (getRegionNick() == 'saratov'))
		},
		Test2Action: {
			text: langs('Получить с анализатора (только на тестовом)'),
			iconCls: 'eph16',
			handler: function () {
				//getWnd('swPersonEPHForm').show({Person_id: 499527, Server_id: 10, PersonEvn_id: 104170589});
				getWnd('swTestLoadEditWindow').show();
			},
			hidden: ((IS_DEBUG != 1) || (getRegionNick() == 'saratov'))
		},
		/*MedPersonalPlaceAction: {
			text: langs('Медицинский персонал: места работы (старый ЕРМП)'),
			iconCls : 'staff16',
			hidden: ((!isSuperAdmin() && (getRegionNick() != 'ufa')) || (getRegionNick() == 'pskov')),
			handler: function()
			{
				getWnd('swMedPersonalViewWindow').show();
			}
		},*/
		MedWorkersAction: {
			text: langs('Медработники'),
			iconCls: 'staff16',
			hidden: ((getRegionNick() == 'ufa') || (!isSuperAdmin() && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ))),
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'MedWorker', main_center_panel);
			}
		},
		/*MedPersonalSearchAction: {
			text: langs('Медицинский персонал: Просмотр (старый ЕРМП)'),
			iconCls : 'staff16',
			hidden : !MP_NOT_ERMP,
			handler: function()
			{
				getWnd('swMedPersonalSearchWindow').show();
			}
		},*/
		swLgotTreeViewAction: {
			text: langs('Регистр льготников: Список'),
			iconCls: 'lgot-tree16',
			handler: function () {
				getWnd('swLgotTreeViewWindow').show();
			}
		},
		swAttachmentDemandAction: {
			text: langs('Заявления на прикрепление МО'),
			iconCls: 'attach-demand16',
			hidden: !isSuperAdmin(),
			handler: function () {
				getWnd('swAttachmentDemandListWindow').show();
			}
		},
		swChangeSmoDemandAction: {
			text: langs('Заявления на прикрепление: СМО'),
			iconCls: 'attach-demand16',
			hidden: !isSuperAdmin(),
			handler: function () {
				getWnd('swChangeSmoDemandListWindow').show();
			}
		},
		swUsersTreeViewAction: {
			text: langs('Пользователи'),
			iconCls: 'users16',
			hidden: !getGlobalOptions().superadmin && !isLpuAdmin(),
			handler: function () {
				getWnd('swUsersTreeViewWindow').show();
			}
		},
		swGroupsViewAction: {
			text: langs('Группы'),
			iconCls: 'users16',
			hidden: !isSuperAdmin(),
			handler: function () {
				getWnd('swGroupViewWindow').show();
			}
		},
		swOptionsViewAction: {
			text: langs('Настройки'),
			iconCls: 'settings16',
			handler: function () {
				getWnd('swOptionsWindow').show();
			}
		},
		swNumeratorAction: {
			text: langs('Нумераторы'),
			iconCls: 'create-schedule16',
			handler: function () {
				getWnd('swNumeratorListWindow').show();
			}
		},
		swPersonSearchAction: {
			text: langs('Человек: поиск'),
			iconCls: 'patient-search16',
			handler: function () {
				getWnd('swPersonSearchWindowExt6').show({
					searchMode: 'all'
				});
			}
		},
		swImportAction: {
			text: langs('Обновление регистров'),
			iconCls: 'patient-search16',
			hidden: (!getGlobalOptions().superadmin || getRegionNick().inlist(['by'])),
			handler: function () {
				getWnd('swImportWindow').show();
			}
		},
		swTemperedDrugs: {
			text: langs('Импорт отпущенных ЛС'),
			iconCls: 'adddrugs-icon16',
			handler: function () {
				getWnd('swTemperedDrugsWindow').show();
			},
			//hidden: (getRegionNick() != 'ufa')
			hidden: !(getRegionNick() == 'ufa' && isSuperAdmin())
		},
		swPersonPeriodicViewAction: {
			text: langs('Тест периодик'),
			iconCls: 'patient-search16',
			handler: function () {
				getWnd('swPeriodicViewWindow').show({
					Person_id: 99560000173,
					Server_id: 10010833
				});
			}
		},
		/*swAssistantWorkPlaceAction: {
			text: langs('Рабочее место лаборанта'),
			iconCls: 'lab-assist16',
			//iconCls: 'patient-search16',
			hidden: !isSuperAdmin(),
			handler: function()
			{
				getWnd('swAssistantWorkPlaceWindow').show();
			}
		},*/
		swSelectWorkPlaceAction: {
			text: langs('Выбор АРМ по умолчанию'),
			iconCls: 'lab-assist16',
			//iconCls: 'patient-search16',
			hidden: false,//!isSuperAdmin(),
			handler: function () {
				getWnd('swSelectWorkPlaceWindowExt6').show();
			}
		},

		swRegistrationJournalSearchAction: {
			text: langs('Лабораторные исследования: поиск'),
			//iconCls: 'patient-search16',
			hidden: (IS_DEBUG != 1 || !isSuperAdmin()),
			handler: function () {
				getWnd('swRegistrationJournalSearchWindow').show();
			}
		},
		swLpuSelectAction: {
			text: langs('Выбор МО'),
			iconCls: 'lpu-select16',
			handler: function () {
				sw.WindowMgr.each(function (wnd) {
					if (wnd.isVisible()) {
						wnd.hide();
					}
				});
				getWnd('swSelectLpuWindowExt6').show({});
			},
			hidden: !getGlobalOptions().superadmin && !isUserGroup(['medpersview', 'ouzuser', 'ouzadmin', 'ouzchief']) && !(getGlobalOptions().lpu && getGlobalOptions().lpu.length > 1) // проверяем так же просмотр медперсонала и количество МО у пользователя
		},

		swDivCountAction: {
			text: langs('Количество html-элементов'),
			iconCls: 'tags16',
			handler: function () {
				var arrdiv = Ext.DomQuery.select("div");
				var arrtd = Ext.DomQuery.select("td");
				var arra = Ext.DomQuery.select("a");
				Ext.Msg.alert("Количество html-элементов", "Количество html-элементов:<br><b>div</b>:&nbsp;" + arrdiv.length + "<br><b>td</b>:&nbsp;&nbsp;" + arrtd.length + "<br><b>a</b>:&nbsp;&nbsp;&nbsp;" + arra.length);
			},
			hidden: (IS_DEBUG != 1)
		},
		swGlobalOptionAction: {
			text: langs('Параметры системы'),
			iconCls: 'settings-global16',
			handler: function () {
				getWnd('swGlobalOptionsWindow').show();
			},
			hidden: !getGlobalOptions().superadmin //((IS_DEBUG!=1) || !getGlobalOptions().superadmin)
		},
		// Все прочие акшены
		swPregCardViewAction: {
			text: langs('Индивидуальная карта беременной: Просмотр'),
			iconCls: 'pol-preg16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isSuperAdmin() && !isTestLpu
		},
		swPregCardFindAction: {
			text: langs('Индивидуальная карта беременной: Поиск'),
			iconCls: 'pol-pregsearch16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isSuperAdmin() && !isTestLpu
		},
		swRegChildOrphanDopDispStreamAction: {
			text: langs('Регистр детей-сирот: Поточный ввод'),
			iconCls: 'orphdisp-stream16',
			handler: function () {
				getWnd('swPersonDispOrpSearchWindow').show({mode: 'stream'});
			},
			hidden: false//!isSuperAdmin()
		},
		swRegChildOrphanDopDispFindAction: {
			text: langs('Регистр детей-сирот (до 2013г.): Поиск'),
			iconCls: 'orphdisp-search16',
			handler: function () {
				getWnd('swPersonDispOrpSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['adygeya', 'yakutiya'])
		},
		swEvnPLChildOrphanDopDispStreamAction: {
			text: langs('Талон по диспансеризации детей-сирот: Поточный ввод'),
			iconCls: 'orphdisp-epl-stream16',
			handler: function () {
				getWnd('swEvnPLDispOrpSearchWindow').show({mode: 'stream'});
			},
			hidden: false
		},
		swEvnPLChildOrphanDopDispFindAction: {
			text: langs('Талон по диспансеризации детей-сирот (до 2013г.): Поиск'),
			iconCls: 'orphdisp-epl-search16',
			handler: function () {
				getWnd('swEvnPLDispOrpSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['adygeya'])
		},
		swEvnDtpWoundViewAction: {
			text: langs('Извещения ДТП о раненом: Просмотр'),
			iconCls: 'stac-accident-injured16',
			handler: function () {
				getWnd('swEvnDtpWoundWindow').show();
			},
			hidden: false
		},
		swEvnDtpDeathViewAction: {
			text: langs('Извещения ДТП о скончавшемся: Просмотр'),
			iconCls: 'stac-accident-dead16',
			handler: function () {
				getWnd('swEvnDtpDeathWindow').show();
			},
			hidden: false
		},
		swMedPersonalWorkPlaceAction: {
			text: langs('<b>Рабочее место</b>'),
			title: langs('АРМ'),
			iconCls: 'workplace-mp16',
			handler: function () {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'common',
					onSelect: null
				});
			},
			hidden: ((getGlobalOptions().medstafffact == undefined) && (getGlobalOptions().lpu_id > 0))
		},
		/*swStacNurseWorkPlaceAction: {
			text: langs('Рабочее место постовой медсестры'),
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
			iconCls: 'workplace-mp16',
			handler: function () {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'prescr',
					onSelect: function (data) {
						getWnd('swEvnPrescrJournalWindow').show({userMedStaffFact: data});
					}
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},
		swEvnPrescrCompletedViewJournalAction: {
			text: langs('Журнал медицинских мероприятий'),
			iconCls: 'workplace-mp16',
			handler: function () {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'prescr',
					onSelect: function (data) {
						getWnd('swEvnPrescrCompletedJournalWindow').show({userMedStaffFact: data});
					}
				});
			},
			hidden: getGlobalOptions().medstafffact == undefined
		},
		/*
		swVKWorkPlaceAction: {
			text: langs('Рабочее место ВК'),
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
			iconCls: 'pol-directions16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//(!isSuperAdmin() || getRegionNick().inlist(['by']))
		},

		swPersonCardAttachListAction: {
			text: langs('РПН: Заявления о выборе МО'),
			hidden: getRegionNick().inlist(['by']),
			iconCls: '', // нужна иконка
			handler: function () {
				getWnd('swPersonCardAttachListWindow').show();
			}
		},

		swMSJobsAction: {
			text: langs('Управление задачами MSSQL'),
			iconCls: 'sql16',
			handler: function () {
				getWnd('swMSJobsWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		swXmlTemplateDebug: {
			text: langs('Конвертация Xml-документов'),
			iconCls: 'test16',
			handler: function () {
				window.open('/?c=EvnXmlConvert&m=index');
			},
			hidden: !isSuperAdmin()
		},
		loadLastObjectCode: {
			text: langs('Обновить последний JS-файл'),
			iconCls: 'test16',
			handler: function () {
				if (sw.codeInfo) {
					loadJsCode({objectName: sw.codeInfo.lastObjectName, objectClass: sw.codeInfo.lastObjectClass});
				}
			},
			hidden: !isSuperAdmin()//true //!isSuperAdmin() && !IS_DEBUG
		},
		MessageAction: {
			text: langs('Сообщения'),
			iconCls: 'messages16',
			hidden: false,
			handler: function () {
				if (getWnd('swMessagesViewWindow').isVisible() == false) {
					getWnd('swMessagesViewWindow').show();
				}
			}
		},
		swTreatmentStreamInputAction: {
			text: langs('Регистрация обращений: Поточный ввод'),
			iconCls: 'petition-stream16',
			handler: function () {
				getWnd('swTreatmentStreamInputWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swTreatmentSearchAction: {
			text: langs('Регистрация обращений: Поиск'),
			iconCls: 'petition-search16',
			handler: function () {
				getWnd('swTreatmentSearchWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swTreatmentReportAction: {
			text: langs('Регистрация обращений: Отчетность'),
			iconCls: 'petition-report16',
			handler: function () {
				getWnd('swTreatmentReportWindow').show();
			},
			hidden: !isAccessTreatment()
		},
		swEvnPSStreamAction: {
			text: langs('Карта выбывшего из стационара: Поточный ввод'),
			iconCls: 'stac-psstream16',
			handler: function () {
				getWnd('swEvnPSStreamInputWindow').show();
			},
			hidden: false //!isSuperAdmin() && !isTestLpu && IS_DEBUG != 1
		},
		swEvnPSFindAction: {
			text: langs('Карта выбывшего из стационара: Поиск'),
			iconCls: 'stac-pssearch16',
			handler: function () {
				getWnd('swEvnPSSearchWindow').show();
			},
			hidden: false //!isSuperAdmin() && !isTestLpu && IS_DEBUG != 1
		},
		swSuicideAttemptsEditAction: {
			text: langs('Суицидальные попытки: Ввод'),
			iconCls: 'suicide-edit16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isSuperAdmin() && !isTestLpu
		},
		swSuicideAttemptsFindAction: {
			text: langs('Суицидальные попытки: Поиск'),
			iconCls: 'suicide-search16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isSuperAdmin() && !isTestLpu
		},

		/*swMedPersonalWorkPlaceStacAction: {
			text: langs('Рабочее место врача'),
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
			iconCls: 'pol-directions16',
			handler: function () {
				getWnd('swEvnDirectionJournalWindow', {params: { userMedStaffFact: null}}).show();
			},
			hidden: false
		},
		swEvnUslugaParStreamAction: {
			text: langs('Выполнение параклинической услуги: Поточный ввод'),
			iconCls: 'par-serv-stream16',
			handler: function () {
				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swEvnUslugaParStreamInputWindow').show();
			},
			hidden: !getRegionNick().inlist(['perm', 'ekb', 'krym', 'kareliya', 'buryatiya', 'vologda', 'adygeya', 'yakutiya', 'yaroslavl'])
		},
		swEvnUslugaParFindAction: {
			text: langs('Выполнение параклинической услуги: Поиск'),
			iconCls: 'par-serv-search16',
			handler: function () {
				// sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swEvnUslugaParSearchWindow').show();
			},
			hidden: false
		},
		swEvnLabSampleDefectViewAction: {
			text: langs('Журнал отбраковки'),
			iconCls: 'lab-assist16',
			handler: function () {
				getWnd('swEvnLabSampleDefectViewWindow').show();
			},
			hidden: false
		},
		swEvnPLStomStreamAction: {
			text: langs('Талон амбулаторного пациента: Поточный ввод'),
			iconCls: 'stom-stream16',
			handler: function () {
				getWnd('swEvnPLStomStreamInputWindow').show();
			}
		},
		swEvnPLStomSearchAction: {
			text: langs('Талон амбулаторного пациента: Поиск'),
			iconCls: 'stom-search16',
			handler: function () {
				getWnd('swEvnPLStomSearchWindow').show();
			},
			hidden: false
		},
		swUslugaPriceListAction: {
			text: langs('Стоматологические услуги МО (Справочник УЕТ)'),
			iconCls: 'stom-uslugi16',
			handler: function () {
				getWnd('swUslugaPriceListViewWindow').show();
			},
			hidden: false
		},
		swMedSvidBirthAction: {
			text: langs('Свидетельства о рождении'),
			iconCls: 'svid-birth16',
			handler: function () {
				getWnd('swMedSvidBirthStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidDeathAction: {
			text: langs('Свидетельства о смерти'),
			iconCls: 'svid-death16',
			handler: function () {
				getWnd('swMedSvidDeathStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidPDeathAction: {
			text: langs('Свидетельства о перинатальной смерти'),
			iconCls: 'svid-pdeath16',
			handler: function () {
				getWnd('swMedSvidPntDeathStreamWindow').show();
			},
			hidden: false
		},
		swMedSvidPrintAction: {
			text: langs('Печать бланков свидетельств'),
			iconCls: 'svid-blank16',
			handler: function () {
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				getWnd('swMedSvidSelectSvidType').show();
			},
			hidden: false
		},
		swTestAction: {
			text: langs('Тест'),
			iconCls: '',
			handler: function () {
				//
				Ext.Ajax.request({
					failure: function (response, options) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if (response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0) {
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
					success: function (response, options) {
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
			hidden: ((!isSuperAdmin()) || (getRegionNick() == 'saratov'))
		},
		swRegDeceasedPeopleAction: {
			text: langs('Сведения об умерших гражданах'),
			iconCls: 'regdead16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isSuperAdmin()
		},
		swMedicationSprAction: {
			text: langs('Справочник: Медикаменты'),
			iconCls: 'farm-drugs16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swContractorsSprAction: {
			text: langs('Справочник: Контрагенты'),
			iconCls: 'farm-partners16',
			handler: function () {
				getWnd('swContragentViewWindow').show();
			},
			hidden: false
		},
		swDokNakAction: {
			text: langs('Приходные накладные'),
			iconCls: 'doc-nak16',
			handler: function () {
				getWnd('swDokNakViewWindow').show();
			},
			hidden: false
		},
		swDokUchAction: {
			text: langs('Документы учета медикаментов'),
			iconCls: 'doc-uch16',
			handler: function () {
				getWnd('swDokUcLpuViewWindow').show();
			},
			hidden: false
		},
		swAktSpisAction: {
			text: langs('Акты списания медикаментов'),
			iconCls: 'doc-spis16',
			handler: function () {
				getWnd('swDokSpisViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swDokOstAction: {
			text: langs('Документы ввода остатков'),
			iconCls: 'doc-ost16',
			handler: function () {
				getWnd('swDokOstViewWindow').show();
			},
			hidden: false
		},
		swInvVedAction: {
			text: langs('Инвентаризационные ведомости'),
			iconCls: 'farm-inv16',
			handler: function () {
				getWnd('swDokInvViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swMedOstatAction: {
			text: langs('Остатки медикаментов'),
			iconCls: 'farm-ostat16',
			handler: function () {
				getWnd('swMedOstatViewWindow').show();
			},
			hidden: false
		},
        DloEgissoCreateDataAction: {
			text: langs('Сформировать данные'),
			hidden: !isUserGroup('EGISSOAdmin'),
			handler: function()
			{
                getWnd('swEgissoDataImportWindow').show();
			}
		},
        DloEgissoOpenModuleAction: {
			text: langs('Открыть модуль'),
			hidden: !isUserGroup('EGISSOAdmin') && !isUserGroup('EGISSOUser'),
			handler: function()
			{
				var url = '/ext03_6/directions_spa_treatment.html?PHPSESSID='+getCookie('PHPSESSID');
				window.open(url);
			}
		},
		EvnReceptProcessAction: {
			text: langs('Обработка рецептов'),
			iconCls: 'receipt-process16',
			handler: function () {
				getWnd('swEvnReceptProcessWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		EvnRPStreamInputAction: {
			text: langs('Потоковое отоваривание рецептов'),
			iconCls: 'receipt-streamps16',
			handler: function () {
				getWnd('swEvnRPStreamInputWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		EvnReceptTrafficBookViewAction: {
			text: langs('Журнал движения рецептов'),
			iconCls: 'receipt-delay16',
			handler: function () {
				getWnd('swEvnReceptTrafficBookViewWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		KerRocordBookAction: {
			text: langs('Врачебная комиссия'),
			iconCls: 'med-commission16',
			handler: function () {
				getWnd('swClinExWorkSearchWindow').show();
			},
			hidden: (!isSuperAdmin() || getRegionNick().inlist(['by']))
		},
		swRegistrationCallAction: {
			text: langs('Регистрация вызова'),
			iconCls: '',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swCardCallViewAction: {
			text: langs('Карта вызова: Просмотр'),
			iconCls: 'ambulance_add16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		},
		swCardCallFindAction: {
			text: langs('Карты СМП: Поиск'),
			iconCls: 'ambulance_search16',
			handler: function () {
				getWnd('swCmpCallCardSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['by'])
		},
		swCardCallStreamAction: {
			text: langs('Карты СМП: Поточный ввод'),
			iconCls: 'ambulance_search16',
			handler: function () {
				//пытаемся запустить новую поточную карту
				getWnd('swCmpCallCardNewCloseCardWindow').show({
					action: 'stream',
					formParams: {
						ARMType: 'smpadmin'
					},
					callback: function (data) {
						if (!data || !data.CmpCloseCard_id) {
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
//			iconCls: 'inj-stream16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isSuperAdmin()
//		},
//		swInjectionFindAction: {
//			text: 'Прививки: Поиск',
//			iconCls: 'inj-search16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isSuperAdmin()
//		},
//		swMedicalTapStreamAction: {
//			text: 'Медотводы: Поточный ввод',
//			iconCls: 'mreject-stream16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isSuperAdmin()
//		},
//		swMedicalTapFindAction: {
//			text: 'Медотводы: Поиск',
//			iconCls: 'mreject-search16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isSuperAdmin()
//		},
//		swSerologyStreamAction: {
//			text: 'Серология: Поточный ввод',
//			iconCls: 'imm-ser-stream16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isSuperAdmin()
//		},
//		swSerologyFindAction: {
//			text: 'Серология: Поиск',
//			iconCls: 'imm-ser-search16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isSuperAdmin()
//		},
//		swAbsenceBakAction: {
//			text: 'Отсутствие бакпрепаратов',
//			iconCls: 'imm-bakabs16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isSuperAdmin()
//		},
//		swCurrentPlanAction: {
//			text: 'Текущее планирование вакцинации',
//			iconCls: 'vac-plan16',
//			handler: function()
//			{
//				sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
//			},
//			hidden: !isSuperAdmin()
//		},
//                 *
//                 *Закоментировал Тагир
//                 *
		// tagir start
		amm_JournalsVac: {
			text: langs('Просмотр журналов вакцинации'),
			iconCls: 'vac-plan16',
			handler: function () {
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_mainForm').show();
				else
					sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));

//              getWnd('amm_mainForm').show();
				//var loadMask = new Ext.LoadMask(Ext.getCmp('journalsVaccine'), { msg: LOAD_WAIT });
				//loadMask.show();
			},
			hidden: false // !isSuperAdmin()
		},

		ammOnkoCtrl_ProfileJurnal: {
			text: langs('Онкоконтроль'),
			iconCls: 'stac-psstream16',
			hidden: (getRegionNick() != 'ufa'),
			handler: function () {
				var record = {'Lpu_id': getGlobalOptions().lpu_id};
				getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
			}
		},

		ammOnkoCtrl_ReportSetZNO: {
			text: langs('Отчет "Анализ выявленных ЗНО"'),
			iconCls: 'stac-psstream16',
			hidden: (getRegionNick() != 'ufa'),
			//hidden: !isSuperAdmin(),
			handler: function () {
				var record = {'Lpu_id': getGlobalOptions().lpu_id}; //amm_vacReport_5
				getWnd('amm_OnkoCtrl_ReportSetZNO').show(record);
//                           getWnd('amm_vacReport_5').show();
			}
		},

		ammOnkoCtrl_ReportMonutoring: {
			text: langs('Отчет "Мониторинг реализации системы "Онкоконтроль"'),
			iconCls: 'stac-psstream16',
			hidden: (getRegionNick() != 'ufa'),
			//hidden: !isSuperAdmin(),
			handler: function () {
				var record = {'Lpu_id': getGlobalOptions().lpu_id};
				getWnd('amm_OnkoCtrl_ReportMonitoring').show(record);
				//getWnd('amm_OnkoCtrl_ReportSetZNO').show(record);
			}
		},


		ammStartVacFormPlan: {
			text: langs('Планирование вакцинации'),
			iconCls: 'vac-plan16',
			hidden: !isSuperAdmin() && !isLpuAdmin(),
			handler: function () {
				if (vacLpuContr())  // Если это 2-я детская  .js
					getWnd('amm_StartVacPlanForm').show();
				//getWnd('amm_SprOtherVacSchemeEditFotm').show();
				else
					sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			}
			//, hidden: false // !isSuperAdmin()
		},

		ammvacListTasks: {
			text: langs('Список заданий'),
			iconCls: 'vac-plan16',
			hidden: !isSuperAdmin() && !isLpuAdmin(),

			handler: function () {
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_ListTaskForm').show();
				else
					sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			}
			//, hidden: false // !isSuperAdmin()
		},
		ammvacReport_5: {
			text: langs('Отчет ф. №5'),
			iconCls: 'vac-plan16',
			handler: function () {
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_vacReport_5').show();
				else
					sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: getRegionNick() == 'kz'
		},
		ammSprVaccineTypeForm: {
			text: langs('Справочник прививок'),
			iconCls: 'vac-plan16',
			handler: function () {
				getWnd('amm_SprVaccineTypeForm').show();
			}
		},
		ammSprVaccine: {
			text: langs('Справочник вакцин'),
			iconCls: 'vac-plan16',
			handler: function () {
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_SprVaccineForm').show();
				else
					sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: false // !isSuperAdmin()
		},
		ammSprNacCal: {
			text: langs('Национальный календарь прививок'),
			iconCls: 'vac-plan16',
			handler: function () {
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_SprNacCalForm').show();
				else
					sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: false // !isSuperAdmin()
		},
		ammVacPresence: {
			text: langs('Наличие вакцин'),
			iconCls: 'vac-plan16',
			handler: function () {
				if (vacLpuContr())  // Если это 2-я детская
					getWnd('amm_PresenceVacForm').show();
				else
					sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: false // !isSuperAdmin()
		},
		// End  tagir
		swLpuPassportAction: {
			text: langs('Паспорт МО'),
			iconCls: 'lpu-passport16',
			handler: function () {
				getWnd('swLpuPassportEditWindow').show({
					action: 'edit',
					Lpu_id: getGlobalOptions().lpu_id
				});
			},
			hidden: !isSuperAdmin() && !isLpuAdmin() && getGlobalOptions().groups.toString().indexOf('MPCModer') == -1 && getGlobalOptions().groups.toString().indexOf('PMUspec') == -1
		},
		swOrgPassportAction: {
			text: langs('Паспорт организации'),
			iconCls: 'lpu-passport16',
			handler: function () {
				getWnd('swOrgEditWindow').show({
					action: 'edit',
					mode: 'passport',
					Org_id: getGlobalOptions().org_id
				});
			},
			hidden: (!isSuperAdmin() && !isOrgAdmin()) || !isDebug()
		},
		swLpuUslugaAction: {
			text: langs('Услуги МО'),
			iconCls: 'lpu-services-lpu16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swUslugaGostAction: {
			text: langs('Услуги ГОСТ'),
			iconCls: 'lpu-services-gost16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swMKB10Action: {
			text: langs('МКБ-10'),
			iconCls: 'spr-mkb16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swMESAction: {
			text: langs('Новые ') + getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function () {
				getWnd('swMesSearchWindow').show();
			},
			hidden: (!isSuperAdmin() || (getRegionNick().inlist(['by'])))
		},
		swMESOldAction: {
			text: getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function () {
				getWnd('swMesOldSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['by']) // TODO: После тестирования доступ должен быть для всех
		},
		swOrgAllAction: {
			text: langs('Все организации'),
			iconCls: 'spr-org16',
			handler: function () {
				getWnd('swOrgViewForm').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			},
			hidden: false
		},
		swContragentsAction: {
			text: langs('Контрагенты'),
			iconCls: 'farm-partners16',
			handler: function () {
				getWnd('swContragentViewWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		swDrugDocumentSprAction: {
			text: 'Справочники системы учета медикаментов',
			iconCls: '',
			handler: function () {
				getWnd('swDrugDocumentSprWindow').show();
			}
		},
		swDocumentUcAction: {
			text: langs('Учет медикаментов'),
			iconCls: 'drug-traffic16',
			handler: function () {
				getWnd('swDocumentUcViewWindow').show();
			},
			hidden: !isSuperAdmin()
		},
		swOrgLpuAction: {
			text: langs('Лечебно-профилактические учреждения'),
			iconCls: 'spr-org-lpu16',
			handler: function () {
				getWnd('swOrgViewForm').show({mode: 'lpu'});
			},
			hidden: false
		},
		swOrgGosAction: {
			text: langs('Государственные учреждения'),
			iconCls: 'spr-org-gos16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swOrgStrahAction: {
			text: langs('Страховые медицинские организации'),
			iconCls: 'spr-org-strah16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swOrgBankAction: {
			text: langs('Банки'),
			iconCls: 'spr-org-bank16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swRlsFirmsAction: {
			text: langs('Производители лекарственных средств'),
			iconCls: 'spr-org-manuf16',
			handler: function () {
				if (!getWnd('swRlsFirmsSearchWindow').isVisible()) getWnd('swRlsFirmsSearchWindow').show();
			}
		},
		swOMSSprTerrAction: {
			text: langs('Территории субъекта РФ'),
			iconCls: 'spr-terr-oms16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swClassAddrAction: {
			text: langs('Классификатор адресов'),
			iconCls: 'spr-terr-addr16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swSprPromedAction: {
			text: langs('Справочники Промед'),
			iconCls: 'spr-promed16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprLpuAction: {
			text: langs('Справочники МО'),
			iconCls: 'spr-lpu16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprOmsAction: {
			text: langs('Справочники ОМС'),
			iconCls: 'spr-oms16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprDloAction: {
			text: langs('Справочники ЛЛО'),
			iconCls: 'spr-dlo16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprPropertiesProfileAction: {
			text: langs('Характеристики профилей отделений'),
			iconCls: 'otd-profile16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprUchetFactAction: {
			text: langs('Учет фактической выработки смен'),
			iconCls: 'uchet-fact16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		SprRlsAction: {
			text: getRLSTitle(),
			iconCls: 'rls16',
			handler: function () {
				getWnd('swRlsViewForm').show();
			},
			hidden: false
		},

		SprMedPerson4Rec: {
			text: 'Врачи ЛЛО',
			iconCls: 'rls16',
			handler: function () {
				getWnd('swMedPerson4ReceptListWindow').show();
			},
			hidden: getRegionNick() != 'ufa' ? true : ( !isSuperAdmin() && !isSuperAdmin() && !isUserGroup(['ChiefLlo', 'DLOAccess']))
		},
		SprPostAction: {
			text: langs('Должности'),
			iconCls: '',
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Post', main_center_panel);
			}/*,
			hidden: !isSuperAdmin()*/
		},
		SprSkipPaymentReasonAction: {
			text: langs('Причины невыплат'),
			iconCls: '',
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'SkipPaymentReason', main_center_panel);
			}/*,
			hidden: !isSuperAdmin()*/
		},
		SprWorkModeAction: {
			text: langs('Режимы работы'),
			iconCls: '',
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkMode', main_center_panel);
			}/*,
			hidden: !isSuperAdmin()*/
		},
		SprSpecialityAction: {
			text: langs('Специальности'),
			iconCls: '',
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'Speciality', main_center_panel);
			}/*,
			hidden: !isSuperAdmin()*/
		},
		SprDiplomaSpecialityAction: {
			text: langs('Дипломные специальности'),
			iconCls: '',
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'DiplomaSpeciality', main_center_panel);
			}/*,
			hidden: !isSuperAdmin()*/
		},
		SprLeaveRecordTypeAction: {
			text: langs('Тип записи окончания работы'),
			iconCls: '',
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'LeaveRecordType', main_center_panel);
			}/*,
			hidden: !isSuperAdmin()*/
		},
		SprEducationTypeAction: {
			text: langs('Тип образования'),
			iconCls: '',
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationType', main_center_panel);
			}/*,
			hidden: !isSuperAdmin()*/
		},
		SprEducationInstitutionAction: {
			text: langs('Учебное учреждение'),
			iconCls: '',
			handler: function () {
				window.gwtBridge.runDictionary(getPromedUserInfo(), 'EducationInstitution', main_center_panel);
			}/*,
			hidden: !isSuperAdmin()*/
		},
		swF14OMSPerAction: {
			text: langs('Форма Ф14 ОМС: Показатели'),
			iconCls: 'rep-f14oms-per16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isSuperAdmin()
		},
		swF14OMSAction: {
			text: langs('Форма Ф14 ОМС'),
			iconCls: 'rep-f14oms16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isSuperAdmin()
		},
		swF14OMSFinAction: {
			text: langs('Форма Ф14 ОМС: Приложение 1'),
			iconCls: 'rep-f14oms-fin16',
			handler: function () {
				sw.swMsg.alert(langs('Внимание'), langs('Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.'));
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//!isSuperAdmin()
		}, /*
		swAdminWorkPlaceAction: {
			text: langs('Рабочее место администратора'),
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swAdminWorkPlaceWindow').show({});
			},
			hidden: false
		},
		swLpuAdminWorkPlaceAction: {
			text: langs('Рабочее место администратора МО'),
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
			iconCls: 'admin16',
			handler: function()
			{
				getWnd('swRegWorkPlaceWindow').show({});
			},
			hidden: !isSuperAdmin()
		},
		*/
		swReportEngineAction: {
			text: langs('Репозиторий отчетов'),
			iconCls: 'rpt-repo16',
			handler: function () {
				// Пример предварительной загрузки блока кода
				if (sw.codeInfo.loadEngineReports) {
					getWnd('swReportEngineWindow').show();
				}
				else {
					getWnd('reports').load(
						{
							callback: function (success) {
								sw.codeInfo.loadEngineReports = success;
								// здесь можно проверять только успешную загрузку
								getWnd('swReportEngineWindow').show();
							}
						});
				}
			},
			hidden: !isSuperAdmin()
		},
		swAnalyzerWindowAction: {
			text: langs('Настройки ЛИС'),
			handler: function () {
				getWnd('swAnalyzerWindow').show({pmUser_id: getGlobalOptions().pmuser_id, pmUser_Login: UserLogin});
			},
			hidden: true //((!isSuperAdmin()) || (getRegionNick() == 'saratov'))
		},
		swRrlExportWindowAction: {
			text: langs('Выгрузка РРЛ'),
			handler: function () {
				getWnd('swRrlExportWindow').show();
			},
			hidden: (getRegionNick() != 'ufa')
		},
		swPrepBlockSprAction: {
			text: langs('Справочник фальсификатов и забракованных серий ЛС'),
			handler: function () {
				getWnd('swPrepBlockViewWindow').show();
			}
		},
		PrepBlockCauseViewAction: {
			text: langs('Причины блокировки ЛС'),
			iconCls: '',
			hidden: !isSuperAdmin(),
			handler: function () {
				getWnd('swPrepBlockCauseViewWindow').show();
			}
		}
	}

	// Проставляем ID-шники списку акшенов [и на всякий случай создаем их] (создавать кстати не обязательно)
	for (var key in sw.Promed.Actions) {
		sw.Promed.Actions[key].id = key;
		sw.Promed.Actions[key] = new Ext.Action(sw.Promed.Actions[key]);
	}

    // панель меню
    main_menu_panel = new Ext.Panel({
		layout: 'fit',
		listeners: {
			'resize': function() {
				if (typeof main_toolbar == 'object') {
					main_toolbar.updateLayout();
				}
			}
		},
		region: 'north',
		height: 30,
		width: '100%',
		border: false,
		html: '<div class="mainTopToolBar" id="mainTopToolBar"></div><div class="x-clear"></div>'
	});

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

	main_taskbar_panel = new Ext.Panel({
		id: 'ux-taskbar',
		layout: 'fit',
		region: 'south',
		hidden: true,
		autoWidth: true,
		border: false,
		html: '<div id="ux-taskbuttons-panel"></div><a href="https://goo.gl/forms/MmeeNwUacwKE6lO82" target="_blank" class="feedback-button"></a><div class="x-clear"></div>'
	});

	//if(Ext.isEmpty(getGlobalOptions().showTop) && getGlobalOptions().showTop == 1)
	main_top_panel = new Ext.Panel({
		id: 'main_top_panel',
		layout: 'fit',
		tbar: main_menu_panel,
		border: false,
		items: [
			main_taskbar_panel
		],
		region: 'center'
	});

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
    // центральная панель
    main_center_panel = new Ext.Panel({
		id: 'main-center-panel',
		cls: 'mainCenterPanel',
        region: 'center',
        bodyStyle:'width:100%;height:100%;background:#16334a;padding:0;',
		border: false,
		tbar: main_top_panel
    });
	main_center_panel.add(main_messages_panel);
    main_frame = new Ext.Viewport({
        layout:'border',
        items: [
            main_menu_panel,
            main_center_panel/*,
             left_panel
             new Ext.Panel({
             region: 'south',
             title: '_',
             height: 1,
             id: 'ajax_state'
             })*/
        ],
		listeners:
		{
			resize: function(){
				main_messages_panel.hideOver(main_messages_panel.mLeft);
			}
		}
    });

	multiColPanel = Ext6.create('Ext6.panel.Panel', {
		width: 1200,
		autoHeight: true,
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		border: false,
		defaults: {
			xtype: 'menu',
			floating: false,
			border: false
		},
		items: [{
			items: [
				'<b class="menu-title">Паспорт МО</b>',
				sw.Promed.Actions.LpuStructureViewAction,
				sw.Promed.Actions.swLpuPassportAction,
				{
					text: 'Организации',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.swOrgAllAction,
							sw.Promed.Actions.swOrgLpuAction
						]
					}
				},
				'-',
				'<b class="menu-title">Лекарственные средства</b>',
				sw.Promed.Actions.swLgotTreeViewAction,
				sw.Promed.Actions.LgotFindAction,
				sw.Promed.Actions.EvnUdostViewAction,
				sw.Promed.Actions.EvnReceptFindAction,
				sw.Promed.Actions.EvnReceptAddStreamAction,
				sw.Promed.Actions.OstAptekaViewAction,
				sw.Promed.Actions.OstDrugViewAction,
				sw.Promed.Actions.OstSkladViewAction,
				sw.Promed.Actions.DrugRequestViewAction,
				sw.Promed.Actions.EvnReceptInCorrectFindAction,
				sw.Promed.Actions.DrugMnnLatinNameEditAction,
				sw.Promed.Actions.DrugTorgLatinNameEditAction,
				sw.Promed.Actions.SprRlsAction,
                {
                    text: 'ЕГИССО',
                    menuAlign: 'tl-bl?',
					hidden: getRegionNick() == 'kz' || (!isUserGroup('EGISSOAdmin') && !isUserGroup('EGISSOUser')),
                    menu: {
                        cls: 'mainToolbarMenu',
                        items: [
                            sw.Promed.Actions.DloEgissoCreateDataAction,
                            sw.Promed.Actions.DloEgissoOpenModuleAction
                        ]
                    }
                }
			], flex: 1, padding: 10
		}, {
			items: [
				'<b class="menu-title">Поликлиника</b>',
				sw.Promed.Actions.EvnPLStreamInputAction,
				sw.Promed.Actions.EvnPLEditAction,
				sw.Promed.Actions.PersonCardSearchAction,
				sw.Promed.Actions.PersonCardViewAllAction,
				sw.Promed.Actions.PersonCardStateViewAction,
				sw.Promed.Actions.swPersonCardAttachListAction,
				{
					text: 'Диспансеризация взрослого населения',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.PersonDispWOWSearchAction,
							sw.Promed.Actions.PersonPrivilegeWOWSearchAction,
							sw.Promed.Actions.PersonPrivilegeWOWStreamInputAction,
							sw.Promed.Actions.PersonDopDispSearchAction,
							sw.Promed.Actions.EvnPLDopDispSearchAction,
							sw.Promed.Actions.EvnPLDispDop13SearchAction,
							sw.Promed.Actions.EvnPLDispDop13SecondSearchAction
						]
					}
				},
				{
					text: 'Профилактические осмотры взрослых',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.EvnPLDispProfSearchAction
						]
					}
				},
				{
					text: 'Диспансеризация детей-сирот',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.swRegChildOrphanDopDispFindAction,
							sw.Promed.Actions.swEvnPLChildOrphanDopDispFindAction,
							sw.Promed.Actions.PersonDispOrpSearchAction,
							sw.Promed.Actions.PersonDispOrpAdoptedSearchAction,
							sw.Promed.Actions.EvnPLDispOrpSearchAction,
							sw.Promed.Actions.EvnPLDispOrpSecSearchAction,
							sw.Promed.Actions.EvnPLDispTeenExportAction
						]
					}
				},
				{
					text: 'Медицинские осмотры несовершеннолетних',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.PersonDispOrpPeriodSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionSearchAction,
							sw.Promed.Actions.PersonDispOrpProfSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionProfSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionProfSecSearchAction,
							sw.Promed.Actions.PersonDispOrpPredSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionPredSearchAction,
							sw.Promed.Actions.EvnPLDispTeenInspectionPredSecSearchAction
						]
					}
				},
				{
					text: 'Диспансеризация (подростки 14ти лет)',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.EvnPLDispTeen14SearchAction
						]
					}
				},
				{
					text: 'Диспансерное наблюдение',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.PersonDispSearchAction,
							sw.Promed.Actions.PersonDispViewAction
						]
					}
				},
				{
					text: 'Индивидуальная карта беременной',
					hidden: !isAdmin || getRegionNick().inlist(['by']),
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.swPregCardViewAction,
							sw.Promed.Actions.swPregCardFindAction
						]
					}
				},
				{
					text: langs('Анкетирование'),
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [{
							text: langs('Онкоконтроль'),
							hidden: getRegionNick() == 'ufa',
							handler: function() {
								var record = {'Lpu_id': getGlobalOptions().lpu_id};
								getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
							}
						},
						sw.Promed.Actions.ammOnkoCtrl_ProfileJurnal,
						sw.Promed.Actions.ammOnkoCtrl_ReportMonutoring,
						sw.Promed.Actions.ammOnkoCtrl_ReportSetZNO,
						{
							text: langs('Паллиативная помощь'),
							hidden: getRegionNick() == 'kz',
							handler: function() {
								var record = {'Lpu_id' : getGlobalOptions().lpu_id};
								record.ReportType = 'palliat';
								getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
							}
						}, {
							text: langs('Возраст не помеха'),
							hidden: getRegionNick() == 'kz',
							handler: function() {
								var record = {'Lpu_id': getGlobalOptions().lpu_id};
								record.ReportType = 'geriatrics';
								getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
							}
						}, {
							text: langs('Оценка BI-RADS'),
							hidden: getRegionNick() == 'kz',
							handler: function() {
								var record = {'Lpu_id': getGlobalOptions().lpu_id};
								record.ReportType = 'birads';
								getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
							}
						}, {
							text: langs('Предварительное анкетирование'),
							handler: function() {
								var record = {'Lpu_id': getGlobalOptions().lpu_id};
								record.ReportType = 'previzit';
								getWnd('amm_OnkoCtrl_ProfileJurnal').show(record);
							}
						}]
					}
				},
				{
					text: 'Иммунопрофилактика',
					hidden: getRegionNick().inlist(['by']),
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.ammStartVacFormPlan,
							sw.Promed.Actions.ammvacListTasks,
							sw.Promed.Actions.amm_JournalsVac,
							sw.Promed.Actions.ammvacReport_5,
							sw.Promed.Actions.ammSprVaccineTypeForm,
							sw.Promed.Actions.ammSprVaccine,
							sw.Promed.Actions.ammSprNacCal,
							sw.Promed.Actions.ammVacPresence
						]
					}
				},
				'-',
				'<b class="menu-title">Стационар</b>',
				sw.Promed.Actions.swEvnPSStreamAction,
				sw.Promed.Actions.swEvnPSFindAction,
				sw.Promed.Actions.swJourHospDirectionAction
			], flex: 1, padding: 10
		}, {
			items: [
				'<b class="menu-title">Параклиника</b>',
				sw.Promed.Actions.swEvnUslugaParStreamAction,
				sw.Promed.Actions.swEvnUslugaParFindAction,
				sw.Promed.Actions.swEvnLabSampleDefectViewAction,
				'-',
				'<b class="menu-title">Стоматология</b>',
				sw.Promed.Actions.swEvnPLStomStreamAction,
				sw.Promed.Actions.swEvnPLStomSearchAction,
				'-',
				'<b class="menu-title">Аптека</b>',
				sw.Promed.Actions.swContractorsSprAction,
				sw.Promed.Actions.swDokNakAction,
				sw.Promed.Actions.swDokUchAction,
				sw.Promed.Actions.swAktSpisAction,
				sw.Promed.Actions.swDokOstAction,
				sw.Promed.Actions.swInvVedAction,
				sw.Promed.Actions.swMedOstatAction,
				'-',
				'<b class="menu-title">Отчеты</b>',
				sw.Promed.Actions.ReportStatViewAction
			], flex: 1, padding: 10
		}, {
			items: [
				'<b class="menu-title">Документы</b>',
				sw.Promed.Actions.RegistryViewAction,
				sw.Promed.Actions.RegistryNewViewAction,
				sw.Promed.Actions.RegistryExportInTFOMS,
				{
					text: 'Патоморфология',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.EvnDirectionHistologicViewAction,
							sw.Promed.Actions.EvnHistologicProtoViewAction,
							sw.Promed.Actions.EvnDirectionMorfoHistologicViewAction,
							sw.Promed.Actions.EvnMorfoHistologicProtoViewAction,
							sw.Promed.Actions.DirectionsForCytologicalDiagnosticExaminationViewAction,
							sw.Promed.Actions.CytologicalDiagnosticTestProtocolsViewAction
						]
					}
				},
				{
					text: 'Свидетельства',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.swMedSvidBirthAction,
							sw.Promed.Actions.swMedSvidDeathAction,
							sw.Promed.Actions.swMedSvidPDeathAction,
							sw.Promed.Actions.swMedSvidPrintAction
						]
					}
				},
				{
					text: 'Извещения о ДТП',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.swEvnDtpWoundViewAction,
							sw.Promed.Actions.swEvnDtpDeathViewAction
						]
					}
				},
				sw.Promed.Actions.swCardCallFindAction,
				sw.Promed.Actions.EvnStickViewAction,
				'-',
				'<b class="menu-title">Сервис</b>',
				sw.Promed.Actions.swUsersTreeViewAction,
				sw.Promed.Actions.swGroupsViewAction,
				{
					text: 'Двойники',
					menuAlign: 'tl-bl?',
					menu: {
						cls: 'mainToolbarMenu',
						items: [
							sw.Promed.Actions.PersonDoublesSearchAction,
							sw.Promed.Actions.PersonDoublesModerationAction,
							sw.Promed.Actions.PersonUnionHistoryAction
						]
					}
				},
				sw.Promed.Actions.swPersonSearchAction,
				sw.Promed.Actions.swOptionsViewAction,
				sw.Promed.Actions.UserProfileAction,
				sw.Promed.Actions.MessageAction,
				sw.Promed.Actions.swNumeratorAction,
				sw.Promed.Actions.swLpuSelectAction,
				sw.Promed.Actions.swSelectWorkPlaceAction,
				'-',
				'<b class="menu-title">Помощь</b>',
				sw.Promed.Actions.PromedHelp,
				sw.Promed.Actions.PromedForum,
				sw.Promed.Actions.PromedAbout
			], flex: 1, padding: 10
		}]
	});

	this.user_menu = Ext6.create('Ext6.menu.Menu', {
		//plain: true,
		id: 'user_menu',
		cls: 'user-menu-list',
		items: [{
			iconCls: 'user16',
			margin: "11px 40px 10px 19px",
			html: '<p><b>' + 'Имя: </b> ' + UserName + '<br/><b>' + 'E-mail: </b> ' + UserEmail + '<br/><b>' + 'Описание: </b> ' + UserDescr + '<br/><b>' + 'МО:</b> ' + Ext.globalOptions.globals.lpu_nick + '</p>',
			xtype: 'label'
		}, {
			cls: 'buttonAccept-super-flat-min button-exit',
			margin: '0 0 0 5',
			xtype: 'button',
			text: langs('Выход'),
			handler: function () {
				Ext6.Msg.show({
					title: langs('Подтвердите выход'),
					msg: langs('Вы действительно хотите выйти?'),
					buttons: Ext6.Msg.YESNO,
					fn: function (buttonId) {
						if (buttonId == 'yes') {
							window.onbeforeunload = null;
							window.location = C_LOGOUT;
						}
					}
				});
			}
		}]
	});
	//виджет уведомлений (колокольчик)
	main_notice_widget = Ext6.create('common.NoticeWidget', {
		defaultAlign: 'tr-br'
	});

	main_toolbar_menu = Ext6.create('Ext6.menu.Menu', {
		cls: 'mainToolbarMenu bigPadding',
		border: false,
		items: multiColPanel
	});

    taskbar = new Ext6.ux.TaskBar();
	main_toolbar = Ext6.create('Ext6.Toolbar', {
		padding: 0,
		defaults: {
			margin: 0
		},
		items: [{
			text: 'Выбрать рабочее место',
			title: langs('АРМ'),
			tooltip: langs('Выбрать рабочее место'),
			id: 'change_workplace_menu',
			//flex: 1,
			handler: function () {
				sw.Promed.MedStaffFactByUser._showMenu(this.id);
			}
		},'->',
		{
			id: '_user_menu',
			text: UserName,
			tabIndex: -1,
			hidden: false,
			handler: function() {
				win.user_menu.showBy(this);
			}
		}, {
			id: '_shared_templates',
			width: 40,
			hidden: true,
			iconCls: 'panicon-envelop-white',
			tooltip: langs('Входящие сообщения'),
			handler: function() {
				var userMedStaffFact = sw.Promed.MedStaffFactByUser.current;

				var params = {
					openShared: true,
					XmlType_id: null,
					EvnClass_id: null,
					LpuSection_id: userMedStaffFact.LpuSection_id,
					MedPersonal_id: userMedStaffFact.MedPersonal_id,
					MedStaffFact_id: userMedStaffFact.MedStaffFact_id,
					MedService_id: userMedStaffFact.MedService_id
				};

				switch(userMedStaffFact.ARMType) {
					case 'polka':
						params.XmlType_id = 3;
						params.EvnClass_id = 11;
						break;
				}

				getWnd('swXmlTemplateEditorWindow').show(params);
			}
		},
		{
			//refs #179845
			text: '',
			id: 'version_for_visually_impaired',
			tooltip: Ext.globalOptions.emk.version_for_visually_impaired ? langs('Выключить версию для слабовидящих') : langs('Включить версию для слабовидящих'),
			iconCls: Ext.globalOptions.emk.version_for_visually_impaired ? 'menu_vision_on' : 'menu_vision_off',
			width: 40,
			hidden: true,
			handler: function () {
				var data = {
					node: 'emk'
				};

				var main_Panel_class_list = document.getElementById('main-center-panel').classList;

				if (main_Panel_class_list) {
					if (this.btnIconEl.dom.className.indexOf('menu_vision_off') == -1) {
						delete data.version_for_visually_impaired;
						main_Panel_class_list.remove('increased-size');
						this.getEl().set({
							'data-qtip': langs('Включить версию для слабовидящих')
						});
					} else {
						data.version_for_visually_impaired = 'on';
						main_Panel_class_list.add('increased-size');
						this.getEl().set({
							'data-qtip': langs('Выключить версию для слабовидящих')
						});

					}
					this.btnIconEl.toggleCls('menu_vision_off');
					this.btnIconEl.toggleCls('menu_vision_on');

					var emks = Ext6.ComponentQuery.query('window[refId=common]');
					if (emks){
						for (i=0; i<emks.length; i++) {
							if(emks[i].isVisible()) {
								emks[i].updateLayout();
							}
						}
					}

					Ext.Ajax.request({
						url: C_OPTIONS_SAVE_FORM,
						params: data,
						success: function (response) {
							if(response && response.responseText){
								var resp = Ext.util.JSON.decode(response.responseText);
								if(resp.success){
									sw4.showInfoMsg({
										type: 'success',
										text: 'Настройки сохранены.'
									});
									Ext.loadOptions();
								} else {
									sw4.showInfoMsg({
										type: 'error',
										text: 'Ошибка при сохранении настроек.'
									});
								}
							}
						}
					});
				}
			}
		}, main_notice_widget.NoticeWidgetButton = new Ext6.create('Ext6.Button', {
			id: '_notice_widget',
			width: 40,
			iconCls: 'panicon-pers-evn-book-white',
			tooltip: langs('Уведомления'),
			handler: function() {
				main_notice_widget.showBy(this);
			}
		}), {
			id: '_video_chat',
			text: '',
			iconCls: 'VideoChatWindowIcon',	//todo: iconCls
			tooltip: 'Видеосвязь',
			disabled: true,
			handler: function() {
				getWnd('swVideoChatWindow').show();
			}
		}, {
			text: '',
			tooltip: langs('Помощь'),
			iconCls: 'menu_help16',
			handler: function () {
				ShowHelp(langs('Содержание'));
			}
		}, {
			text: '',
			tooltip: langs('Меню'),
			id: '_main_toolbar_menu_button',
			iconCls: 'menu_3dot16',
				margin: '0 15 0 0',
			handler: function() {
				// показываем панельку с менюшкой
				main_toolbar_menu.showBy(this);
			}
		}],
		renderTo: 'mainTopToolBar'
	});
}


Ext.onReady(function (){
    if ( is_ready )
    {
        return;
    }

    // Запускалка
    sw.Promed.tasks = new Ext.util.TaskRunner();
    // Маска поверх всех окон
    sw.Promed.mask = {
    	show: function() {
			var mask = Ext6.getBody().mask(LOAD_WAIT);
			mask.setStyle('z-index', sw.WindowMgr.zseed + 20000);
		},
		hide: function() {
			Ext6.getBody().unmask();
		}
	};
    sw.Promed.mask.hide();

    Ext.Ajax.timeout = 600000;

    // Значения по умолчанию
    loadPromed( function() {

        // Инициализация всплывыющих подсказок
        Ext.QuickTips.init();

		function unload_page(event) {
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
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

        // собственно загрузка модуля
        loadWindowModule();
        setPromedInfo(' / '+getGlobalOptions().region.name,'promed-region');

		// Старт тасков
		if (getNoticeOptions().is_popup_message) {
			this.taskTimer = function() {
				return {run: taskRunExt6, interval: ((getGlobalOptions().message_time_limit)?getGlobalOptions().message_time_limit:5)*3*1000};
			}
			sw.Promed.tasks.start(this.taskTimer());
		}
        if(getNoticeOptions().is_extra_message) {
            this.extraTaskTimer = function() {
                return {run: extraTaskRun, interval: 300*1000};
            }
            sw.Promed.tasks.start(this.extraTaskTimer());
        }
		/*{// Старт виджета уведомлений (колокольчик) - получение сообщений перенесено в taskRunExt6
			this.NoticeWidgetTaskTimer = function() {
				return {run: function() {
					main_notice_widget.EvnJournalList.getUnreadCount();
				}, interval: 30*1000};
			}
			sw.Promed.tasks.start(this.NoticeWidgetTaskTimer());
		}*/
		this.XmlTemplateSharedTaskTimer = function() {
			return {run: getXmlTemplateSharedUnreadCount, interval: 30*1000};
		}
		sw.Promed.tasks.start(this.XmlTemplateSharedTaskTimer());

		var globals_lpu = getGlobalOptions().lpu;
		var index = globals_lpu.indexOf('');
		if(index > -1)
			globals_lpu.splice(index, 1);
		if ( globals_lpu ) {
			if ( globals_lpu.length>1 ) { // Выбор МО в случае, если их несколько у пользователя
				getWnd('swSelectLpuWindowExt6').show( {params : globals_lpu} );
			} else {
				if ( globals_lpu.length==1 ) {
					// Если у пользователя только 1 МО, то загрузким данные по этой МО
					loadGlobalStores({
						callback: function () {
							// Открытие АРМа по умолчанию
							if (getGlobalOptions().se_techinfo) {
								openWindowsByTechInfo();
							} else {
								sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
							}
						}
					});
					getCountNewDemand();
				}
                else
                {
                    if(isUserGroup('OuzSpec') || isUserGroup('Communic')){
					
                        sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();        
                    }
                }
			}
		} else {
			// У пользователя нет ни одной МО, загружать нечего не нужно :) 
			// Открытие АРМа по умолчанию для пользователя организации
			if (getGlobalOptions().se_techinfo) {
				openWindowsByTechInfo();
			} else {
				sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
			}
		}

		//Инициализация видеосвязи
		Ext6.require('videoChat.lib.Engine', function() {
			videoChat.lib.Engine.addEvent('connect', function() {
				Ext6.getCmp('_video_chat').enable();
			});
			videoChat.lib.Engine.addEvent('disconnect', function() {
				Ext6.getCmp('_video_chat').disable();
			});
		});
    } );

	//Явный сброс сессии с клиента ( после 2х часов бездействия )
	ConnectionDestroyer = Ext6.extend(Ext6.util.Observable, {
		constructor: function(config){

			this.disconnectTimer = 0;
			this.disconnectTime  = DisconnectOnInactivityTime * 60 * 1000;

			var disconnectTime  = this.disconnectTime;
			var disconnectTimer = this.disconnectTimer;
						
			Ext6.GlobalEvents.on({
				'mousedown': function() {
					sw.Promed.GlobalVariables.statusInactivity = false;
					disconnectTimer = 0;
				},
				'keydown': function () { //TODO: обработчик глобально не применился. Надо думать как сделать.
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
			}, 60000);

			ConnectionDestroyer.superclass.constructor.call(this, config)
		}
	});

	ConnectionDestroy = new ConnectionDestroyer();
});

