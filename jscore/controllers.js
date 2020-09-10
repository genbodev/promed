/**
* controllers.js - Пути к контроллерам на сервере
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       SWAN developers
* @version      12.07.2009
    */
// Работа с обслуживающими организациями (в структуре МО)
C_GET_CLOS              = '/?c=LpuOrgServed&m=getCurrentLpuOrgServed';
C_GET_LOS               = '/?c=LpuOrgServed&m=getLpuOrgServed';
C_SAVE_LOS              = '/?c=LpuOrgServed&m=saveLpuOrgServed';
C_DEL_LOS               = '/?c=LpuOrgServed&m=deleteLpuOrgServed';

//Конвертация полей (смена кодировок)
C_LOAD_SCHEMES          = '/?c=ConvertTables&m=LoadSchemes';
C_LOAD_TABLES           = '/?c=ConvertTables&m=LoadTables';
C_LOAD_FIELDS           = '/?c=ConvertTables&m=LoadFields';
C_CONVERT_FIELDS        ='/?c=ConvertTables&m=ConvertFields';

// Настройки пользователя
C_OPTIONS_SAVE_FORM     = '/?c=Options&m=saveOptionsForm';
C_OPTIONS_LOAD          = '/?c=Options&m=getGlobalOptions';
C_OPTIONS_LOAD_FORM     = '/?c=Options&m=getOptionsForm';
C_OPTIONS_LOAD_TREE     = '/?c=Options&m=getOptionsTree';

C_GOPTIONS_SAVE_FORM    = '/?c=Options&m=saveGlobalOptionsForm';
C_GOPTIONS_LOAD_FORM    = '/?c=Options&m=getGlobalOptionsForm';
C_GOPTIONS_LOAD_TREE    = '/?c=Options&m=getGlobalOptionsTree';


// Утилиты
C_RECORD_UNION          = '/?c=Utils&m=doRecordUnion';
C_PERSON_UNION          = '/?c=Utils&m=doPersonUnion';
C_PERSON_TRANSFER       = '/?c=Utils&m=doPersonEvnTransfer';
C_PLAN_PERSON_UNION     = '/?c=Utils&m=doPlanPersonUnion';
C_PERSON_DCHANGE        = '/?c=PersonDoubles&m=changePersonDoubles';
C_PERSON_DCANCEL        = '/?c=PersonDoubles&m=cancelPersonDoubles';
C_GETOBJECTLIST         = '/?c=Utils&m=GetObjectList';
C_LOAD_PROMED_SPR       = '/?c=SprLoader&m=getPromedSprSyncTable';
C_LOAD_FARMACY_SPR      = '/?c=SprLoader&m=getFarmacySprSyncTable';
C_LOAD_CURTIME          = '/?c=Common&m=getCurrentTimeAndUser';
C_GET_CURDATETIME		= '/?c=Common&m=getCurrentDateTime';
C_LOGOUT                = '/?c=main&m=Logout';
C_LOGOUT_ERROR          = '/?c=main&m=LogoutWithError';
C_RECORD_DEL            = '/?c=Utils&m=ObjectRecordDelete';
C_SEARCH                = '/?c=Search&m=searchData';
C_SEARCH_RECCNT         = '/?c=Search&m=getRecordsCount';
C_SEARCH_RECINCCNT      = '/?c=EvnRecept&m=getIncRecordsCount';
C_GET_AUDIT             = '/?c=Utils&m=getAudit';
C_UNIONHISTORY          = '/?c=Utils&m=getUnionHistory';
C_CHANGEPERSONFORDOC	= '/?c=Common&m=setAnotherPersonForDocument'
C_SETEVNISTRANSIT		= '/?c=Evn&m=setEvnIsTransit'

// Карта вызова
C_AMB_SAVE              = '/?c=AmbulanceCard&m=saveAmbulanceCard';
C_AMB_GET               = '/?c=AmbulanceCard&m=getAmbulanceCard';

// Мед. персонал
C_MP_GRID               = '/?c=MedPersonal&m=getMedPersonalGrid';
C_MP_GRIDDETAIL         = '/?c=MedPersonal&m=getMedPersonalGridDetail';
C_MSF_EDIT              = '/?c=MedPersonal&m=getMedStaffFactEditWindow';
C_MSF_DROP              = '/?c=MedPersonal&m=dropMedStaffFact';
C_MP_COMBO              = '/?c=MedPersonal&m=getMedPersonalCombo';
C_MP_LOADLIST           = '/?c=MedPersonal&m=loadMedPersonalCombo';
C_MP_DLO_LOADLIST       = '/?c=MedPersonal&m=loadDloMedPersonalList';
C_MP_REGION_LOADLIST    = '/?c=MedPersonal&m=getMedPersonalWithLpuRegionCombo';

// Бригады СМП
C_ET_LOADLIST			= '/?c=EmergencyTeam&m=loadEmergencyTeamCombo';

//Единицы измреения присутствующие с OkeiLink
C_OKEILINK				= '/?c=LpuPassport&m=loadOkeiLinkCombo';
C_OKATO_LIST			= '/?c=LpuPassport&m=loadOKATOList';

// Льготы
C_LGOT_TREE             = '/?c=Privilege&m=getLgotTree';
C_LGOT_LIST             = '/?c=Privilege&m=getLgotList';
C_PERS_PRIV_DEL         = '/?c=Privilege&m=deletePersonPrivilege';
C_PRIV_LOAD_LIST        = '/?c=Privilege&m=loadPersonPrivilegeList';
C_PRIV_LOAD_EDIT        = '/?c=Privilege&m=loadPrivilegeEditForm';
C_PRIV_SAVE             = '/?c=Privilege&m=savePrivilege';
C_PRIV_SEARCH           = '/?c=Privilege&m=searchPersonPrivilege';
C_PRIVCAT_LOAD_LIST     = '/?c=Privilege&m=loadPersonCategoryList';

// Адрес
C_LOAD_ADDRCOMBO        = '/?c=Address&m=getAddressCombo';
C_LOAD_COUNTRYCOMBO     = '/?c=Address&m=getCountries';
C_LOAD_REGIONCOMBO      = '/?c=Address&m=getRegions';
C_LOAD_SUBREGIONCOMBO   = '/?c=Address&m=getSubRegions';
C_LOAD_CITYCOMBO        = '/?c=Address&m=getCities';
C_LOAD_TOWNCOMBO        = '/?c=Address&m=getTowns';
C_LOAD_STREETCOMBO      = '/?c=Address&m=getStreets';
C_LOAD_CHILDS           = '/?c=Address&m=loadChildLists';

// Талоны по ДД
C_EPLDD_VIZIT_LIST      = '/?c=EvnPLDispDop&m=loadEvnVizitDispDopGrid';
C_EPLDD_USLUGA_LIST     = '/?c=EvnPLDispDop&m=loadEvnUslugaDispDopGrid';
C_EPLDD_SAVE            = '/?c=EvnPLDispDop&m=saveEvnPLDispDop';
C_EPLDD_LOAD            = '/?c=EvnPLDispDop&m=loadEvnPLDispDopEditForm';
C_EPLDD_LOAD_YEARS      = '/?c=EvnPLDispDop&m=getEvnPLDispDopYears';
C_EPLDD_PRINT           = '/?c=EvnPLDispDop&m=printEvnPLDispDop';

// Диспансеризация, профосмотры и прочее
C_EPLD_LOAD_YEARS       = '/?c=EvnPLDisp&m=getEvnPLDispYears';
C_PDO_LOAD_YEARS        = '/?c=PersonDispOrp13&m=getPersonDispOrpYearsCombo';

// Талоны по диспансеризации взрослого населения
C_EPLDD13_VIZIT_LIST	= '/?c=EvnPLDispDop13&m=loadEvnVizitDispDop13Grid';
C_EPLDD13_USLUGA_LIST	= '/?c=EvnPLDispDop13&m=loadEvnUslugaDispDop13Grid';
C_EPLDD13_SAVE			= '/?c=EvnPLDispDop13&m=saveEvnPLDispDop13';
C_EPLDD13_LOAD			= '/?c=EvnPLDispDop13&m=loadEvnPLDispDop13EditForm';
C_EPLDD13_PRINT			= '/?c=EvnPLDispDop13&m=printEvnPLDispDop13';

// Талоны по профосмотрам
C_EPLDP_VIZIT_LIST      = '/?c=EvnPLDispProf&m=loadEvnVizitDispProfGrid';
C_EPLDP_USLUGA_LIST     = '/?c=EvnPLDispProf&m=loadEvnUslugaDispProfGrid';
C_EPLDP_SAVE            = '/?c=EvnPLDispProf&m=saveEvnPLDispProf';
C_EPLDP_LOAD            = '/?c=EvnPLDispProf&m=loadEvnPLDispProfEditForm';
C_EPLDP_LOAD_YEARS      = '/?c=EvnPLDispProf&m=getEvnPLDispProfYears';
C_EPLDP_PRINT           = '/?c=EvnPLDispProf&m=printEvnPLDispProf';

// Талоны по ДД 14
C_EPLDT14_VIZIT_LIST	= '/?c=EvnPLDispTeen14&m=loadEvnVizitDispTeen14Grid';
C_EPLDT14_USLUGA_LIST	= '/?c=EvnPLDispTeen14&m=loadEvnUslugaDispTeen14Grid';
C_EPLDT14_SAVE			= '/?c=EvnPLDispTeen14&m=saveEvnPLDispTeen14';
C_EPLDT14_LOAD			= '/?c=EvnPLDispTeen14&m=loadEvnPLDispTeen14EditForm';
C_EPLDT14_LOAD_YEARS	= '/?c=EvnPLDispTeen14&m=getEvnPLDispTeen14Years';
C_EPLDT14_PRINT			= '/?c=EvnPLDispTeen14&m=printEvnPLDispTeen14';

// Талоны по диспасеризации детей сирот
C_EPLDO_VIZIT_LIST      = '/?c=EvnPLDispOrp&m=loadEvnVizitDispOrpGrid';
C_EPLDO_USLUGA_LIST     = '/?c=EvnPLDispOrp&m=loadEvnUslugaDispOrpGrid';
C_EPLDO_SAVE            = '/?c=EvnPLDispOrp&m=saveEvnPLDispOrp';
C_EPLDO_LOAD            = '/?c=EvnPLDispOrp&m=loadEvnPLDispOrpEditForm';
C_EPLDO_LOAD_YEARS      = '/?c=EvnPLDispOrp&m=getEvnPLDispOrpYears';
C_EPLDO_PRINT           = '/?c=EvnPLDispOrp&m=printEvnPLDispOrp';

// Талоны по диспасеризации детей сирот с 2013
C_EPLDO13_VIZIT_LIST    = '/?c=EvnPLDispOrp13&m=loadEvnVizitDispOrpGrid';
C_EPLDO13_VIZITRECOMEND_LIST = '/?c=EvnPLDispOrp13&m=loadEvnDiagAndRecomendationGrid';
C_EPLDO13_USLUGA_LIST   = '/?c=EvnPLDispOrp13&m=loadEvnUslugaDispOrpGrid';
C_EPLDO13_SAVE          = '/?c=EvnPLDispOrp13&m=saveEvnPLDispOrp';
C_EPLDO13_LOAD          = '/?c=EvnPLDispOrp13&m=loadEvnPLDispOrpEditForm';
C_EPLDO13_LOAD_YEARS    = '/?c=EvnPLDispOrp13&m=getEvnPLDispOrpYears';
C_EPLDO13_PRINT         = '/?c=EvnPLDispOrp13&m=printEvnPLDispOrp';

// Талоны по диспасеризации детей сирот с 2013
C_EPLDTI_VIZIT_LIST     = '/?c=EvnPLDispTeenInspection&m=loadEvnVizitDispOrpGrid';
C_EPLDTI_VIZITRECOMEND_LIST      = '/?c=EvnPLDispTeenInspection&m=loadEvnDiagAndRecomendationGrid';
C_EPLDTI_USLUGA_LIST    = '/?c=EvnPLDispTeenInspection&m=loadEvnUslugaDispOrpGrid';
C_EPLDTI_SAVE           = '/?c=EvnPLDispTeenInspection&m=saveEvnPLDispTeenInspection';
C_EPLDTI_LOAD           = '/?c=EvnPLDispTeenInspection&m=loadEvnPLDispTeenInspectionEditForm';
C_EPLDTI_LOAD_YEARS     = '/?c=EvnPLDispTeenInspection&m=getEvnPLDispTeenInspectionYears';
C_EPLDTI_PRINT          = '/?c=EvnPLDispTeenInspection&m=printEvnPLDispTeenInspection';

// Организации
C_ORG_LIST              = '/?c=Org&m=getOrgList';

//Медикаменты
C_ORGFARMACY_REPLACE    = '/?c=Drug&m=orgFarmacyReplace';
C_DRUG_MNN_VIEW         = '/?c=Drug&m=loadDrugMnnGrid';
C_DRUG_OSTAT            = '/?c=Drug&m=getDrugOstat';
C_DRUG_OSTAT_FARM_LIST  = '/?c=Drug&m=getDrugOstatByFarmacyGrid';
C_DRUG_OSTAT_LIST       = '/?c=Drug&m=getDrugOstatGrid';
C_DRUG_TORG_VIEW        = '/?c=Drug&m=loadDrugTorgGrid';
C_ORGFARMACY_LIST       = '/?c=Drug&m=getOrgFarmacyGrid';
C_ORGFARMACY_VKL        = '/?c=Drug&m=VklOrgFarmacy';
C_DRUG_LIST             = '/?c=Drug&m=getDrugGrid';
C_DRUG_UPD_TIME         = '/?c=Drug&m=getDrugOstatUpdateTime';
C_DRUG_RAS_UPD_TIME     = '/?c=Drug&m=getDrugOstatRASUpdateTime';
C_DRUG_MNN_LIST         = '/?c=Drug&m=loadDrugMnnList';
C_DRUG_COMPLEX_MNN_LIST = '/?c=Drug&m=loadDrugComplexMnnList';
C_DRUG_REC_LIST         = '/?c=Drug&m=loadDrugList';
C_DRUG_RLS_LIST         = '/?c=Drug&m=loadDrugRlsList';

//Пользователь
C_USER_SETCURLPU            = '/?c=User&m=setCurrentLpu';
C_USER_CHANGELPU			= '/?c=User&m=changeCurrentLpu';
C_USER_GETOWNLPU_LIST       = '/?c=User&m=getOwnedLpuList';
C_USER_GETOWNFARMACY_LIST   = '/?c=User&m=getOwnedFarmacyList';
C_USER_GETGROUP_LIST        = '/?c=User&m=getUsersGroupsList';
C_USER_GETTREE			    = '/?c=User&m=getOrgUsersTree';
C_USER_LIST				    = '/?c=User&m=getUsersList';
C_FARMACYUSER_LIST          = '/?c=User&m=getFarmacyUsersList';
C_USER_DROP				    = '/?c=User&m=dropUser';
C_USER_GETDATA			    = '/?c=User&m=getUserData';
C_FARMACYUSER_GETDATA	    = '/?c=User&m=getFarmacyUserData';
C_USER_SAVEDATA			    = '/?c=User&m=saveUserData';
C_USER_SETCURFARMACY        = '/?c=User&m=setCurrentFarmacy';
C_USER_SETCURMSF            = '/?c=User&m=setCurrentMSF';
C_USER_SETCURARM            = '/?c=User&m=setCurrentARM';

//Структура
C_LPU_GET                   = '/?c=LpuStructure&m=GetLpuAllQuery';
C_LPUFILIAL_GET				= '/?c=LpuPassport&m=getLpuFilialRecord';
C_LPUSTRUCTURE_LOAD         = '/?c=LpuStructure&m=GetLpuStructure';
C_LPUBUILDING_GET           = '/?c=LpuStructure&m=GetLpuBuilding';
C_LPUBUILDING_SAVE          = '/?c=LpuStructure&m=SaveLpuBuilding';
C_LPUUNIT_GETEDIT           = '/?c=LpuStructure&m=GetLpuUnitEdit';
C_LPUUNIT_GET               = '/?c=LpuStructure&m=getLpuUnit';
C_LPUUNIT_SAVE		        = '/?c=LpuStructure&m=SaveLpuUnit';
C_LPUUNIT_COMBO             = '/?c=LpuStructure&m=getLpuUnitCombo';
C_LPUUNITSET_COMBO          = '/?c=LpuStructure&m=getLpuUnitSetCombo';
C_LPUSECTION_LIST		    = '/?c=Common&m=loadLpuSectionList';
C_LPUSECTION_GRID		    = '/?c=LpuStructure&m=getLpuSectionGrid';
C_LPUSECTION_PID            = '/?c=LpuStructure&m=getLpuSectionPid';
C_LPUSECTION_GET            = '/?c=LpuStructure&m=GetLpuSectionEdit';
C_LPUSECTION_SAVE           = '/?c=LpuStructure&m=SaveLpuSection';
C_LPUSECTIONWARD_LIST	    = '/?c=Common&m=loadLpuSectionWardList';
C_LPUSECTIONBEDPROFILE_LIST	= '/?c=Common&m=loadLpuSectionBedProfileList';
C_LPUWITHMEDSERV_LIST		= '/?c=MedService&m=getLpusWithMedService';
C_MEDPERSONAL_LIST		    = '/?c=MedPersonal&m=loadMedStaffFactList';
C_MPBYSTRUCTURE_LIST		= '/?c=MedPersonal&m=loadMedStaffFactListByLpuStructure';
C_MEDSERVICE_LIST		    = '/?c=MedService&m=loadMedServiceList';
C_MEDSERVICE_MP_LIST		= '/?c=MedService&m=loadMedServiceMedPersonalList';
C_LPUREGION_LIST		    = '/?c=Common&m=loadLpuRegionList';
C_PMUSER_LIST			    = '/?c=User&m=getCurrentOrgUsersList';
C_MSFREG_LIST			    = '/?c=LpuStructure&m=GetMedStaffRegion';
C_MSFREG_SAVE			    = '/?c=LpuStructure&m=SaveMedStaffRegion';
C_LPUREGION_SAVE		    = '/?c=LpuStructure&m=SaveLpuRegion';
C_LPUREGIONSTREET_GET       = '/?c=LpuStructure&m=GetLpuRegionStreet';
C_LPUREGIONSTREET_SAVE      = '/?c=LpuStructure&m=SaveLpuRegionStreet';
C_LPUBUILDINGSTREET_GET     = '/?c=LpuStructure&m=GetLpuBuildingStreet';
C_LPUBUILDINGSTREET_SAVE    = '/?c=LpuStructure&m=SaveLpuBuildingStreet';
C_LPUBUILDINGTERRITORYSERVICE_LOAD      = '/?c=TerritoryService&m=loadLpuBuildingTerritoryServiceList';
C_LPUBUILDINGTERRITORYSERVICE_GET4EDIT      = '/?c=TerritoryService&m=getLpuBuildingTerritoryServiceForEdit';
C_LPUBUILDINGTERRITORYSERVICE_GETGRID4EDIT      = '/?c=TerritoryService&m=getLpuBuildingTerritoryServiceHousesForEdit';
C_LPUBUILDINGTERRITORYSERVICE_SAVE     = '/?c=TerritoryService&m=saveLpuBuildingTerritoryService';
C_LPUSECTIONTARIFF_GET      = '/?c=LpuStructure&m=GetLpuSectionTariff';
C_LPUSECTIONTARIFF_SAVE     = '/?c=LpuStructure&m=SaveLpuSectionTariff';
C_LPUSECTIONTARIFF_CHECK    = '/?c=LpuStructure&m=CheckLpuSectionTariff';
C_USLUGASECTIONTARIFF_GET   = '/?c=LpuStructure&m=GetUslugaSectionTariff';
C_USLUGASECTIONTARIFF_SAVE  = '/?c=LpuStructure&m=SaveUslugaSectionTariff';
C_USLUGASECTION_SAVE        = '/?c=LpuStructure&m=SaveUslugaSection';
C_USLUGASECTION_COPY        = '/?c=LpuStructure&m=copyUslugaSectionList';
C_LPUSECTIONBEDSTATE_GET    = '/?c=LpuStructure&m=GetLpuSectionBedState';
C_LPUSECTIONBEDSTATE_CHECK  = '/?c=LpuStructure&m=CheckLpuSectionBedState';
C_LPUSECTIONBEDSTATE_SAVE   = '/?c=LpuStructure&m=SaveLpuSectionBedState';
C_LPUSECTIONWARD_GET	    = '/?c=LpuStructure&m=GetLpuSectionWard';
C_LPUSECTIONWARD_SAVE	    = '/?c=LpuStructure&m=SaveLpuSectionWard';
C_LPUSECTIONFINANS_GET      = '/?c=LpuStructure&m=GetLpuSectionFinans';
C_LPUSECTIONFINANS_CHECK    = '/?c=LpuStructure&m=CheckLpuSectionFinans';
C_LPUSECTIONFINANS_SAVE     = '/?c=LpuStructure&m=SaveLpuSectionFinans';
C_LPUSECTIONSHIFT_GET       = '/?c=LpuStructure&m=GetLpuSectionShift';
C_LPUSECTIONSHIFT_CHECK     = '/?c=LpuStructure&m=CheckLpuSectionShift';
C_LPUSECTIONSHIFT_SAVE      = '/?c=LpuStructure&m=SaveLpuSectionShift';
C_LPUSECTIONLICENSE_GET     = '/?c=LpuStructure&m=GetLpuSectionLicence';
C_LPUSECTIONLICENSE_CHECK   = '/?c=LpuStructure&m=CheckLpuSectionLicence';
C_LPUSECTIONLICENSE_SAVE    = '/?c=LpuStructure&m=SaveLpuSectionLicence';
C_LPUSECTIONTARIFFMES_GET   = '/?c=LpuStructure&m=GetLpuSectionTariffMes';
C_LPUSECTIONTARIFFMES_CHECK = '/?c=LpuStructure&m=CheckLpuSectionTariffMes';
C_LPUSECTIONTARIFFMES_SAVE  = '/?c=LpuStructure&m=SaveLpuSectionTariffMes';
C_LPUSECTIONPLAN_GET        = '/?c=LpuStructure&m=GetLpuSectionPlan';
C_LPUSECTIONPLAN_CHECK      = '/?c=LpuStructure&m=CheckLpuSectionPlan';
C_LPUSECTIONPLAN_SAVE       = '/?c=LpuStructure&m=SaveLpuSectionPlan';
C_LPUSECTIONQUOTE_SAVE      = '/?c=LpuStructure&m=SaveLpuSectionQuote';
C_LPUSECTIONQUOTE_GET       = '/?c=LpuStructure&m=GetLpuSectionQuote';
C_LPUUSLUGA_GET             = '/?c=LpuStructure&m=GetLpuUsluga';
C_PERSONDOPDISPPLAN_GET		= '/?c=LpuStructure&m=GetPersonDopDispPlan';
C_PERSONDOPDISPPLAN_SAVE	= '/?c=LpuStructure&m=SavePersonDopDispPlan';
C_FRMOSECTION_LIST			= '/?c=Common&m=loadFRMOSectionList';
C_FRMOUNIT_LIST				= '/?c=Common&m=loadFRMOUnitList';

//Регистр по заболеваниям
C_PERSONDISPREG_LOAD    = '/?c=PersonDispReg&m=getPersonDispReg';
C_PERSONDISPREG_SAVE    = '/?c=PersonDispReg&m=savePersonDispReg';
C_PERSONDISPREG_TREE    = '/?c=PersonDispReg&m=getSicknessTree';
C_PERSONDISPREG_LIST    = '/?c=PersonDispReg&m=getPersonDispRegListBySickness';
C_PERSONDISPREG_DROP    = '/?c=PersonDispReg&m=dropPersonDispReg';

// Рецепты
C_EVNREC_CHECK          = '/?c=EvnRecept&m=checkEvnRecept';
C_EVNDIAGPRIV_CHECK     = '/?c=EvnRecept&m=checkPrivDiag';
C_EVNREC_MATTER_CHECK   = '/?c=EvnRecept&m=checkEvnMatterRecept';
C_EVNREC_SAVE           = '/?c=EvnRecept&m=saveEvnRecept';
C_EVNREC_SAVE_RLS       = '/?c=EvnRecept&m=saveEvnReceptRls';
C_EVNRECGEN_SAVE		= '/?c=EvnRecept&m=saveEvnReceptGeneral';
C_EVNREC_PRINT          = '/?c=EvnRecept&m=printRecept';
C_EVNREC_PRINT_DS       = '/?c=EvnRecept&m=printReceptDarkSide';
C_RECEPT_NUM            = '/?c=EvnRecept&m=getReceptNumber';
C_SIGNA_LIST            = '/?c=EvnRecept&m=getSignaList';
C_SIGNA_SAVE            = '/?c=EvnRecept&m=saveSigna';
C_SIGNA_DEL             = '/?c=EvnRecept&m=deleteSigna';
C_EVNREC_LOAD           = '/?c=EvnRecept&m=loadEvnReceptEditForm';
C_EVNRECGEN_LOAD		= '/?c=EvnRecept&m=loadEvnReceptGeneralEditForm';
C_EVNREC_DEL            = '/?c=EvnRecept&m=deleteEvnRecept';
C_EVNREC_LIST           = '/?c=EvnRecept&m=loadEvnReceptList';
C_EVNREC_PRINTSEARCH    = '/?c=EvnRecept&m=printSearchEvnRecept';
C_EVNRECINC_SEARCH      = '/?c=EvnRecept&m=searchEvnReceptInCorrect';
C_EVNREC_STREAM         = '/?c=EvnRecept&m=loadStreamReceptList';
C_EVNRECINC_PRINTSEARCH = '/?c=EvnRecept&m=printSearchEvnReceptInCorrect';
C_RECEPTFORM_GET_LIST   = '/?c=EvnRecept&m=getReceptFormList';
C_RECEPTGENFORM_GET_LIST   = '/?c=EvnRecept&m=getReceptGenFormList';
C_RECEPTURG_GET_LIST	= '/?c=EvnRecept&m=getReceptUrgencyList';

//Отоваренные рецепты
C_RECOTOV_VIEW          = '/?c=ReceptOtov&m=GetReceptOtovViewForm';

// Удостоверения
C_EVNUDOST_DEL          = '/?c=EvnUdost&m=deleteEvnUdost';
C_EVNUDOST_LOADLIST     = '/?c=EvnUdost&m=loadEvnUdostList';
C_EVNUDOST_PRINT        = '/?c=EvnUdost&m=printUdost';


//ТАП
C_EVNPL_PRINT           = '/?c=EvnPL&m=printEvnPL';

// Стомат. ТАП
C_EVNPLSTOM_PRINT       = '/?c=EvnPLStom&m=printEvnPLStom';

//КВС
C_EVNPS_PRINT           = '/?c=EvnPS&m=printEvnPS';

// Анамнез
C_ANAMNEZ_DEL           = '/?c=EvnPL&m=deleteAnamnez';
C_ANAMNEZ_LIST          = '/?c=EvnPL&m=loadAnamnezList';

//Диспансеризация
C_PERSDISP_HIST         = '/?c=PersonDisp&m=getPersonDispHistoryList';
C_PERSDISP_DEL          = '/?c=PersonDisp&m=deletePersonDisp';

//Выдача медикаментов в диспансеризации
C_PERSONDISPMED_SAVE    = '/?c=PersonDisp&m=savePersonDispMedicament';
C_PERSONDISPMED_DRUGLIST= '/?c=Drug&m=loadSicknessDrugList';

//Картотека
C_PERSONCARD_DEL        = '/?c=PersonCard&m=deletePersonCard';
C_PERSONCARD_HIST       = '/?c=PersonCard&m=getPersonCardHistoryList';
C_PERSONCARD_GRID       = '/?c=Person&m=getPersonCardGrid';
C_PERSONCARD_PRINTBLANK = '/?c=PersonCard&m=printStatement';
C_PERSONCARD_LIST       = '/?c=PersonCard&m=GetList';
C_PERSONCARD_SAVE       = '/?c=PersonCard&m=savePersonCard';
C_PERSONCARD_COUNT      = '/?c=PersonCard&m=getPersonCardCount';
C_PERSONCARD_LOG_DETAILS= '/?c=PersonCard&m=GetDetailList';

// Заявка на медикаменты
C_DRUGREQUEST_PRINT     = '/?c=DrugRequest&m=index&method=printDrugRequest';

// Пациент
C_PERSON_SEARCH         = '/?c=Person&m=getPersonSearchGrid';
C_PERSON_SAVE           = '/?c=Person&m=savePersonEditWindow';
C_PERSON_EDIT           = '/?c=Person&m=getPersonEditWindow';
C_DIAG_LIST             = '/?c=Utils&m=loadDiagList';
C_USLUGA_LIST           = '/?c=Utils&m=loadUslugaList';
C_PERSON_DEVIATION_HEIGHT      = '/?c=Person&m=getDeviationHeight';
C_PERSON_DEVIATION_WEIGHT      = '/?c=Person&m=getDeviationWeight';

// ЭО
C_EQ_TALONLIST          = '/?c=ElectronicQueueInfo&m=getElectronicQueueGrid';

//Расписание
C_TTG_LISTDAY			= '/?c=TimetableGraf&m=GetListByDay';
C_TTG_APPLY				= '/?c=TimetableGraf&m=Apply';
C_TTG_CLEAR				= '/?c=TimetableGraf&m=Clear';
C_TTG_CLEARDAY			= '/?c=TimetableGraf&m=ClearDay';
C_TTG_DELETE			= '/?c=TimetableGraf&m=Delete';

C_TTG_LISTFOREDIT		= '/?c=TimetableGraf&m=getTimetableGrafForEdit';
C_TTG_LISTFORREC		= '/?c=TimetableGraf&m=getTimetableGraf';
C_TTG_LISTONEDAYFORREC	= '/?c=TimetableGraf&m=getTimetableGrafOneDay';
C_TTG_LISTFOREDITPRINT	= '/?c=TimetableGraf&m=printTimetableGrafForEdit';
C_TTG_LISTONEDAYFORRECPRINT = '/?c=TimetableGraf&m=printTimetableGrafOneDay';
C_TTG_EDITTTG			= '/?c=TimetableGraf&m=editTTG';

C_TTS_APPLY				= '/?c=TimetableStac&m=Apply';
C_TTS_CLEAR				= '/?c=TimetableStac&m=Clear';
C_TTS_LISTFOREDIT		= '/?c=TimetableStac&m=getTimetableStacForEdit';
C_TTS_LISTFORREC		= '/?c=TimetableStac&m=getTimetableStac';
C_TTS_LISTONEDAYFORREC	= '/?c=TimetableStac&m=getTimetableStacOneDay';
C_TTS_LISTFOREDITPRINT	= '/?c=TimetableStac&m=printTimetableStacForEdit';
C_TTS_LISTONEDAYFORRECPRINT	= '/?c=TimetableStac&m=printTimetableStacOneDay';
C_TTS_CREATESCHED		= '/?c=TimetableStac&m=createTTSSchedule';
C_TTS_SETTYPE			= '/?c=TimetableStac&m=setTTSType';
C_TTS_HISTORY			= '/?c=TimetableStac&m=getTTSHistory';
C_TTS_DAYCOMMENT_GET	= '/?c=TimetableStac&m=getTTSDayComment';
C_TTS_DAYCOMMENT_SAVE	= '/?c=TimetableStac&m=saveTTSDayComment';
C_TTS_CLEARDAY			= '/?c=TimetableStac&m=ClearDay';
C_TTS_EDITTTS			= '/?c=TimetableStac&m=editTTS';
C_TTS_DELETE			= '/?c=TimetableStac&m=Delete';


C_TTMS_LISTFOREDIT		= '/?c=TimetableMedService&m=getTimetableMedServiceForEdit';
C_TTMS_CLEARDAY			= '/?c=TimetableMedService&m=ClearDay';
C_TTMS_LISTFORREC		= '/?c=TimetableMedService&m=getTimetableMedService';
C_TTMS_LISTONEDAYFORREC	= '/?c=TimetableMedService&m=getTimetableMedServiceOneDay';
C_TTMS_LISTFOREDITPRINT	= '/?c=TimetableMedService&m=printTimetableMedServiceForEdit';
C_TTMS_LISTONEDAYFORRECPRINT = '/?c=TimetableMedService&m=printTimetableMedServiceOneDay';
C_TTMSUC_LISTONEDAYFORRECPRINT = '/?c=TimetableMedService&m=printTimetableUslugaComplexOneDay';
C_TTMS_CLEAR			= '/?c=TimetableMedService&m=Clear';
C_TTMS_DELETE			= '/?c=TimetableMedService&m=Delete';
C_TTMS_SETTYPE			= '/?c=TimetableMedService&m=setTTMSType';
C_TTMS_CREATESCHED		= '/?c=TimetableMedService&m=createTTMSSchedule';
C_TTMS_DAYCOMMENT_GET	= '/?c=TimetableMedService&m=getTTMSDayComment';
C_TTMS_DAYCOMMENT_SAVE	= '/?c=TimetableMedService&m=saveTTMSDayComment';
C_TTMS_ADDDOP			= '/?c=TimetableMedService&m=addTTMSDop';
C_TTMS_APPLY			= '/?c=TimetableMedService&m=Apply';
C_TTMS_HISTORY			= '/?c=TimetableMedService&m=getTTMSHistory';
C_TTMS_EDITTTMS			= '/?c=TimetableMedService&m=editTTMS';

C_TTMSO_LISTFOREDIT		= '/?c=TimetableMedServiceOrg&m=getTimetableMedServiceOrgForEdit';
C_TTMSO_CLEARDAY			= '/?c=TimetableMedServiceOrg&m=ClearDay';
C_TTMSO_LISTFORREC		= '/?c=TimetableMedServiceOrg&m=getTimetableMedServiceOrg';
C_TTMSO_LISTONEDAYFORREC	= '/?c=TimetableMedServiceOrg&m=getTimetableMedServiceOrgOneDay';
C_TTMSO_LISTFOREDITPRINT	= '/?c=TimetableMedServiceOrg&m=printTimetableMedServiceOrgForEdit';
C_TTMSO_LISTONEDAYFORRECPRINT = '/?c=TimetableMedServiceOrg&m=printTimetableMedServiceOrgOneDay';
C_TTMSOUC_LISTONEDAYFORRECPRINT = '/?c=TimetableMedServiceOrg&m=printTimetableUslugaComplexOneDay';
C_TTMSO_CLEAR			= '/?c=TimetableMedServiceOrg&m=Clear';
C_TTMSO_DELETE			= '/?c=TimetableMedServiceOrg&m=Delete';
C_TTMSO_SETTYPE			= '/?c=TimetableMedServiceOrg&m=setTTMSOType';
C_TTMSO_CREATESCHED		= '/?c=TimetableMedServiceOrg&m=createTTMSOSchedule';
C_TTMSO_DAYCOMMENT_GET	= '/?c=TimetableMedServiceOrg&m=getTTMSODayComment';
C_TTMSO_DAYCOMMENT_SAVE	= '/?c=TimetableMedServiceOrg&m=saveTTMSODayComment';
C_TTMSO_ADDDOP			= '/?c=TimetableMedServiceOrg&m=addTTMSODop';
C_TTMSO_APPLY			= '/?c=TimetableMedServiceOrg&m=Apply';
C_TTMSO_HISTORY			= '/?c=TimetableMedServiceOrg&m=getTTMSOHistory';
C_TTMSO_EDITTTMS			= '/?c=TimetableMedServiceOrg&m=editTTMSO';

C_TTUC_LISTFOREDIT		= '/?c=TimetableMedService&m=getTimetableUslugaComplexForEdit';
C_TTUC_LISTFORREC		= '/?c=TimetableMedService&m=getTimetableUslugaComplex';
C_TTUC_LISTONEDAYFORREC	= '/?c=TimetableMedService&m=getTimetableUslugaComplexOneDay';
C_TTUC_LISTFOREDITPRINT		= '/?c=TimetableMedService&m=printTimetableMedServiceForEditUslugaComplex';
C_TTUC_LISTONEDAYFORRECPRINT = '/?c=TimetableMedService&m=printTimetableUslugaComplexOneDay';

C_TTR_APPLY				= '/?c=TimetableResource&m=Apply';
C_TTR_CLEAR				= '/?c=TimetableResource&m=Clear';
C_TTR_CLEARDAY			= '/?c=TimetableResource&m=ClearDay';
C_TTR_DELETE			= '/?c=TimetableResource&m=Delete';
C_TTR_SETTYPE			= '/?c=TimetableResource&m=setTTRType';
C_TTR_CREATESCHED		= '/?c=TimetableResource&m=createTTRSchedule';
C_TTR_DAYCOMMENT_GET	= '/?c=TimetableResource&m=getTTRDayComment';
C_TTR_DAYCOMMENT_SAVE	= '/?c=TimetableResource&m=saveTTRDayComment';
C_TTR_HISTORY			= '/?c=TimetableResource&m=getTTRHistory';
C_TTR_EDITTTR			= '/?c=TimetableResource&m=editTTR';
C_TTR_LISTFORREC		= '/?c=TimetableResource&m=getTimetableResource';
C_TTR_LISTONEDAYFORREC	= '/?c=TimetableResource&m=getTimetableResourceOneDay'
C_TTR_LISTFOREDIT		= '/?c=TimetableResource&m=getTimetableResourceForEdit';
C_TTR_LISTFOREDITPRINT	= '/?c=TimetableResource&m=printTimetableResourceForEdit';
C_TTR_DAYCOMMENT_SAVE	= '/?c=TimetableResource&m=saveTTRDayComment';
C_TTR_DAYCOMMENT_GET	= '/?c=TimetableResource&m=getTTRDayComment';
C_TTR_ADDDOP			= '/?c=TimetableResource&m=addTTRDop';
C_TTR_LISTONEDAYFORRECPRINT = '/?c=TimetableResource&m=printTimetableResourceOneDay';

C_TTG_CREATESCHED		= '/?c=TimetableGraf&m=createTTGSchedule';
C_MSF_COMMENT_GET		= '/?c=MedPersonal&m=getMedStaffFactComment';
C_MSF_COMMENT_SAVE		= '/?c=MedPersonal&m=saveMedStaffFactComment';
C_TTG_ADDDOP			= '/?c=TimetableGraf&m=addTTGDop';
C_MSF_DURATION_GET		= '/?c=MedPersonal&m=getMedStaffFactDuration';
C_TTG_SETTYPE			= '/?c=TimetableGraf&m=setTTGType';
C_TTG_HISTORY			= '/?c=TimetableGraf&m=getTTGHistory';

C_QUEUE_APPLY		    = '/?c=Queue&m=ApplyFromQueue';
C_QUEUE_ADD 		    = '/?c=Queue&m=addQueue';

C_LS_COMMENT_GET		= '/?c=LpuStructure&m=getLpuSectionComment';
C_LS_COMMENT_SAVE		= '/?c=LpuStructure&m=saveLpuSectionComment';
C_LS_COMMENT_GET		= '/?c=LpuStructure&m=getLpuSectionComment';
C_LS_COMMENT_SAVE		= '/?c=LpuStructure&m=saveLpuSectionComment';

// История изменений примечаний по бирке
C_TT_DESCR_HISTORY		= '/?c=TimetableGraf&m=getTTDescrHistory';

//Справочник РЛС
C_RLS_SEARCH			= '/?c=Rls&m=searchData';
C_RLS_GETPACKCODE		= '/?c=Rls&m=GetRlsPackCode';
C_RLS_GETTORGVIEW		= '/?c=Rls&m=GetRlsTorgView';
C_RLS_GETACTMATTERSVIEW = '/?c=Rls&m=GetRlsActmattersView';
C_RLS_GETFIRMSVIEW		= '/?c=Rls&m=GetRlsFirmsView';
C_RLS_GETPHARMASTRUCT	= '/?c=Rls&m=GetRlsPharmacologyStructure';
C_RLS_GETPHARMAVIEW		= '/?c=Rls&m=GetRlsPharmacologyView';
C_RLS_GETNOZOLSTRUCT	= '/?c=Rls&m=GetRlsNozologyStructure';
C_RLS_GETNOZOLVIEW		= '/?c=Rls&m=GetRlsNozologyView';
C_RLS_GETATXSTRUCT		= '/?c=Rls&m=GetRlsAtxStructure';
C_RLS_GETATXVIEW		= '/?c=Rls&m=GetRlsAtxView';

//Админка. Управление джобами на сервере
C_MSJOBS_GETJOBSLIST    = '/?c=Kladr&m=getJobsList';
C_MSJOBS_GETJOBSRUNNING = '/?c=Kladr&m=getJobsRunning';
C_MSJOBS_GETHISTORY     = '/?c=Kladr&m=getHistoryByInterval';
C_MSJOBS_GETSTEPSLIST   = '/?c=Kladr&m=getStepsList';
C_MSJOBS_ISJOBRUNNING   = '/?c=Kladr&m=isJobRunning';
C_MSJOBS_STARTJOB       = '/?c=Kladr&m=startJob';
C_MSJOBS_STOPJOB        = '/?c=Kladr&m=stopJob';


C_REFVALUES_LIST        = '/?c=Template&m=loadRefValuesList';
C_USLUGACOMPLEX_LIST    = '/?c=Usluga&m=loadUslugaComplexList';
C_USLUGAGOST_LIST       = '/?c=Usluga&m=loadUslugaGostList';

// Регистратура
C_REG_RECORDLULIST 		= '/?c=Reg&m=getRecordLpuUnitList'
C_REG_RECORDMSFLIST 	= '/?c=Reg&m=getRecordMedPersonalList';
C_REG_RECORDLSLIST 		= '/?c=Reg&m=getRecordLpuSectionList';
C_REG_RECORDMSLIST 		= '/?c=Reg&m=getRecordMedServiceList';
C_REG_GETAPPRLU			= '/?c=Reg&m=getAppropriateLpuUnit'
C_REG_DIRTYPELIST		= '/?c=Reg&m=getDirTypeList';
C_REG_REGIONSLIST		= '/?c=Reg&m=getRegionsList'
C_REG_GETCURLPUDATA		= '/?c=Reg&m=getCurrentLpuData'
C_REG_DIRLULIST 		= '/?c=Reg&m=getDirectionLpuUnitList'
C_REG_DIRMSFLIST 		= '/?c=Reg&m=getDirectionMedPersonalList';
C_REG_DIRLSLIST 		= '/?c=Reg&m=getDirectionLpuSectionList';
C_REG_DIRMSLIST 		= '/?c=Reg&m=getDirectionMedServiceList';
C_REG_GETRECBYPERSON	= '/?c=Reg&m=getListByPerson';
C_REG_GETMSBYUSLUGA		= '/?c=Reg&m=getListMedServiceByUsluga';

//PACS

C_GET_PACSSET           = '/?c=LpuPacsSettings&m=getCurrentPacsSettings';
C_SAVE_PACSSET          = '/?c=LpuPacsSettings&m=saveLpuPacsData';
C_DEL_PACSSET           = '/?c=LpuPacsSettings&m=deleteLpuPacsData';

//СМП
C_LOAD_STREETSANDUNFORMALIZEDADDRESSCOMBO = '/?c=CmpCallCard&m=loadStreetsAndUnformalizedAddressDirectoryCombo';
C_ACTIVECMPCALLRULES_LOAD = '/?c=CmpCallCard4E&m=loadActiveCallRules';
C_ACTIVECMPCALLRULES_SAVE     = '/?c=CmpCallCard4E&m=saveActiveCallRules';
C_ACTIVECMPCALLRULES_EDIT     = '/?c=CmpCallCard4E&m=getActiveCallRuleEdit';

// Удаление Evn
C_EVN_DEL            	= '/?c=Evn&m=deleteEvn';

// Удаление посещения
C_EVN_VIZIT_DEL         = '/?c=Evn&m=deleteEvn';

//Группы пользователей
C_LOAD_SMPDISPATCHCALLCOMBO = '/?c=CmpCallCard&m=getDispatchCallUsers';

//Вызовы на дом
C_HOMEVISIT_LIST 		= '/?c=HomeVisit&m=getHomeVisitList';
C_HOMEVISIT_CONFIRM 	= '/?c=HomeVisit&m=confirmHomeVisit';
C_HOMEVISIT_DENY 		= '/?c=HomeVisit&m=denyHomeVisit';
C_HOMEVISIT_ADD_GET     = '/?c=HomeVisit&m=getHomeVisitAddData';
C_HOMEVISIT_SYMPTOMS_TREE = '/?c=HomeVisit&m=getSymptomsHTML';
C_HOMEVISIT_ADD         = '/?c=HomeVisit&m=addHomeVisit';

//Справочник типов единиц времени
C_TIMETYPES_COMBO 		= '/?c=LpuPassport&m=getTimeTypes';

//Справочник типов компрессии PACS
C_PACSCOMPRESSIONTYPES_COMBO = '/?c=LpuPassport&m=getPacsCompressionTypes';

//Работа с квотами приема
C_TTQUOTES_LIST 		    = '/?c=TimetableQuote&m=getQuotesList';
C_TTQUOTE_LOAD 			    = '/?c=TimetableQuote&m=getQuoteRule';
C_TTQUOTE_SAVE 			    = '/?c=TimetableQuote&m=saveQuoteRule';
C_TTQUOTE_DELETE 			= '/?c=TimetableQuote&m=deleteQuoteRule';
C_TTQUOTE_SUBJECTS_LIST     = '/?c=TimetableQuote&m=getQuoteRuleSubjects';

C_INETUSERINFO = '/?c=Reg&m=getInetUserInfo';

C_STRUCTPARAMS_TREE         = '/?c=StructuredParams&m=getStructuredParamsTree';
C_STRUCTPARAMSEDIT_TREE     = '/?c=StructuredParams&m=getStructuredParamsTreeBranch';
C_STRUCTPARAMSEDIT_GRID     = '/?c=StructuredParams&m=getStructuredParamsGridBranch';
C_STRUCTPARAMSEDIT_EXTJS6   = '/?c=StructuredParams&m=getStructuredParamsExtJS6';
C_STRUCTPARAMSSEND_DATA     = '/?c=StructuredParams&m=sendStructuredParamData';
C_STRUCTPARAMSEDIT_SAVE     = '/?c=StructuredParams&m=saveStructuredParams';
C_STRUCTPARAMSINEDIT_SAVE   = '/?c=StructuredParams&m=saveStructuredParamsInline';
C_STRUCTPARAMEDIT_GET       = '/?c=StructuredParams&m=getStructuredParam';
C_STRUCTPARAMEDIT_DEL       = '/?c=StructuredParams&m=deleteStructuredParam';
C_STRUCTPARAMEDIT_MOVE      = '/?c=StructuredParams&m=moveStructuredParam';

//Коды КСГ

C_KSG_CODE                  = '/?c=MesOld&m=loadKsgCombo';
C_KPG_CODE                  = '/?c=MesOld&m=loadKpgCombo';


//Список городов федерального значения

//Ведение графиков дежурств
C_KLADR_LIST				= '/?c=Common&m=loadFederalKladrList';
C_WORKGRAPH_SAVE			='/?c=Common&m=saveWorkGraph';
C_WORKGRAPH_LPUSEC_SAVE		='/?c=Common&m=saveWorkGraphLpuSection';
C_WORKGRAPH_DEL				='/?c=Common&m=deleteWorkGraph';
C_WORKGRAPHLPUSECTION_DEL	='/?c=Common&m=deleteWorkGraphLpuSection';
C_WORKGRAPHLPUSECTIONARR_DEL ='/?c=Common&m=deleteWorkGraphLpuSectionArray';


C_LBOMSL_LOAD_SWD = '/?c=LpuBuildingOfficeMedStaffLink&m=loadScheduleWorkDoctor';
C_LBO_LOAD_LBOC = '/?c=LpuBuildingOffice&m=loadLpuBuildingOfficeCombo';