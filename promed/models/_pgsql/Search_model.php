<?php
defined("BASEPATH") or die("No direct script access allowed");
require_once("Search_model_get.php");
require_once("Search_model_selectFunc.php");
require_once("Search_model_selectBody.php");
require_once("Search_model_selectParams.php");
require_once("Search_model_selectParamsCommon.php");
require_once("Search_model_selectBodyCommon.php");
/**
 * Search - модель для форм поиска
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author			Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			?
 *
 * @property CI_DB_driver $db
 * @property EvnPLDispDop13_model $EvnPLDispDop13_model
 * @property EvnPS_model $EvnPS_model
 */
class Search_model extends SwPgModel
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI:SS";
	public $dateTimeForm120 = "YYYY-MM-DD HH24:MI:SS";

	public $schema = "dbo";  //региональная схема
	public $comboSchema = "dbo";
	public $lpuList = [];

	function __construct()
	{
		parent::__construct();
		//установка региональной схемы
		$config = get_config();
		if ($this->regionNick == "kz") {
			$this->schema = $config["regions"][getRegionNumber()]["schema"];
		}
		if ($this->regionNick == "kz") {
			$this->comboSchema = $config["regions"][getRegionNumber()]["schema"];
		}
	}

	/**
	 * @return array
	 */
	function getDbf1Array()
	{
		return Search_model_get::getDbf1Array();
	}

	/**
	 * @return array
	 */
	function getDbf2Array()
	{
		return Search_model_get::getDbf2Array();
	}

	/**
	 * @return array
	 */
	function getDbf3Array()
	{
		return Search_model_get::getDbf3Array();
	}

	/**
	 * Определяет вид фильтра по Lpu_id
	 * @param $data
	 * @return string
	 */
	function getLpuIdFilter($data)
	{
		return Search_model_get::getLpuIdFilter($this, $data);
	}

	/**
	 * Фильтры по льготам
	 * @param $data
	 * @param $filter
	 * @param $queryParams
	 */
	function getPrivilegeFilters($data, &$filter, &$queryParams)
	{
		Search_model_get::getPrivilegeFilters($this, $data, $filter, $queryParams);
	}

	/**
	 * Фильтр по правам доступа ко льготам
	 * @param $data
	 * @param $filter
	 * @param $queryParams
	 */
	function getPrivilegeAccessRightsFilters($data, &$filter, &$queryParams)
	{
		Search_model_get::getPrivilegeAccessRightsFilters($this, $data, $filter, $queryParams);
	}

	/**
	 * Фильтры по прикреплению
	 * @param $data
	 * @param $filter
	 * @param $queryParams
	 * @param $orderby
	 * @param $pac_filter
	 * @throws Exception
	 */
	function getPersonCardFilters($data, &$filter, &$queryParams, &$orderby, &$pac_filter)
	{
		Search_model_get::getPersonCardFilters($this, $data, $filter, $queryParams, $orderby, $pac_filter);
	}

	/**
	 * Фильтры по периодикам
	 * @param $data
	 * @param $filter
	 * @param $queryParams
	 * @param $main_alias
	 * @param string $alias
	 */
	protected function getPersonPeriodicFilters($data, &$filter, &$queryParams, $main_alias, $alias = 'PS')
	{
		Search_model_get::getPersonPeriodicFilters($data, $filter, $queryParams, $main_alias, $alias);
	}

	/**
	 * Формирование и выполнение поискового запроса
	 * @param $data
	 * @param bool $getCount
	 * @param bool $print
	 * @param bool $dbf
	 * @return array|bool
	 * @throws Exception
	 */
	function searchData($data, $getCount = false, $print = false, $dbf = false)
	{
		$filter = "(1 = 1)";
		$pac_filter = "";
		$main_alias = "";
		$queryParams = [];
		$variablesArray = ["dbo.tzGetDate() as dt"];
		$orderby = "";
		$archiveTable = null;
		$archiveTables = ["EvnPS", "EvnSection", "EvnRecept", "EvnPL", "EvnPLStom", "EvnVizitPL", "EvnVizitPLStom", "EvnPLDispDop13", "EvnPLDispDop13Sec", "EvnPLDispProf", "EvnPLDispScreen", "EvnPLDispScreenChild", "EvnPLDispOrp", "EvnPLDispTeenInspectionPeriod", "EvnPLDispTeenInspectionProf", "EvnPLDispTeenInspectionPred", "EvnUslugaPar", "CmpCallCard", "CmpCloseCard", "EvnPLDispTeen14"];
		if (in_array($data["SearchFormType"], $archiveTables)) {
			$archiveTable = $data["SearchFormType"];
			switch ($data["SearchFormType"]) {
				case "EvnPLDispTeenInspectionPeriod":
				case "EvnPLDispTeenInspectionProf":
				case "EvnPLDispTeenInspectionPred":
					$archiveTable = "EvnPLDispTeenInspection";
					break;
				case "EvnPLDispDop13Sec":
					$archiveTable = "EvnPLDispDop13";
					break;
				case "EvnPL":
					$archiveTable = "Evn";
					break;
			}
		}
		$archive_database_enable = $this->config->item("archive_database_enable");
		$query = "
			select
				-- select
		";
		$queryWithArray = [];
		$select = [];
		if (($data["SearchFormType"] == "EvnDiag" ||
			$data["SearchFormType"] == "EPLPerson" ||
			$data["SearchFormType"] == "EvnPL" ||
			$data["SearchFormType"] == "EvnVizitPL" ||
			$data["SearchFormType"] == "EvnUsluga" ||
			$data["SearchFormType"] == "EvnAgg" ||
			$data["SearchFormType"] == "EPLStomPerson" ||
			$data["SearchFormType"] == "EvnPLStom" ||
			$data["SearchFormType"] == "EvnVizitPLStom`" ||
			$data["SearchFormType"] == "EvnUslugaStom" ||
			$data["SearchFormType"] == "EvnAggStom" ||
			substr($data["SearchFormType"], 0, 3) == "Kvs") && $dbf === true
		) {
			$query .= "
				distinct
			";
		}
		$isFarmacy = (isset($data["session"]["OrgFarmacy_id"]));
		$queryParams["Lpu_id"] = $data["Lpu_id"];
		$PS_prefix = "PS"; //таблица для выборки данных по застрахованным
		if ((in_array($data["PersonPeriodicType_id"], [2, 3]) && @$data["kvs_date_type"] == 1) || ($data["PersonPeriodicType_id"] == 1 && @$data["kvs_date_type"] == 2)) {
			$PS_prefix = "PS2";
		}
		if (isset($data["and_kvsperson"]) && $data["and_kvsperson"] && $dbf === true) {
			$select[] = Search_model_selectFunc::searchData_1($this, $PS_prefix);
		}
		$PL_prefix = "PS";
		if ((in_array($data["PersonPeriodicType_id"], [2, 3]) && @$data["epl_date_type"] == 1) || ($data["PersonPeriodicType_id"] == 1 && @$data["epl_date_type"] == 2)) {
			$PL_prefix = "PS2";
		}
		if (isset($data["and_eplperson"]) && $data["and_eplperson"] && $dbf === true) {
			$select[] = Search_model_selectFunc::searchData_1($this, $PL_prefix);
		}
		$PLS_prefix = 'PS';
		if ((in_array($data["PersonPeriodicType_id"], [2, 3]) && @$data["eplstom_date_type"] == 1) || ($data["PersonPeriodicType_id"] == 1 && @$data["eplstom_date_type"] == 2)) {
			$PLS_prefix = "PS2";
		}
		if (isset($data["and_eplstomperson"]) && $data["and_eplstomperson"] && $dbf === true) {
			$select[] = Search_model_selectFunc::searchData_1($this, $PLS_prefix);
		}
		$isPerm = $data["session"]["region"]["nick"] == "perm";
		$isBDZ = Search_model_selectFunc::searchData_isBDZ($isPerm);
		switch ($data["SearchFormType"]) {
			case "CmpCallCard":
				$main_alias = "CCC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_CmpCallCard($this, $isBDZ);
				break;
			case "CmpCloseCard":
				$main_alias = "CCC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_CmpCloseCard($this, $isBDZ, $data);
				break;
			case "PersonDopDisp":
				$main_alias = "DD";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonDopDisp($this, $data);
				break;
			case "PersonDispOrp":
				$main_alias = "DOr";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonDispOrp($this, $data);
				break;
			case "PersonDispOrpPeriod":
				$main_alias = "DOr";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonDispOrpPeriod($this, $data);
				break;
			case "PersonDispOrpPred":
				$main_alias = "DOr";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonDispOrpPred($this, $data);
				break;
			case "PersonDispOrpProf":
				$main_alias = "DOr";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonDispOrpProf($this, $data);
				break;
			case "PersonDispOrpOld":
				$main_alias = "DOr";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonDispOrpOld($this, $data);
				break;
			case "EvnPLDispDop13":
				$main_alias = "EvnPLDispDop13";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispDop13($this, $data);
				break;
			case "EvnPLDispDop13Sec":
				$main_alias = "EPLDD13";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispDop13Sec($this, $data);
				break;
			case "EvnPLDispProf":
				$main_alias = "EPLDP";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispProf($this, $data);
				break;
			case "EvnPLDispScreen":
				$main_alias = "EPLDS";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispScreen($this, $data);
				break;
			case "EvnPLDispScreenChild":
				$main_alias = "EPLDS";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispScreenChild($this, $data);
				break;
			case "EvnPLDispDop":
				$main_alias = "EPLDD";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispDop($this, $data);
				break;
			case "EvnPLDispTeen14":
				$main_alias = "EPLDT14";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispTeen14($this, $data);
				break;
			case "EvnPLDispOrp":
				$main_alias = "EPLDO";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispOrp($this, $data);
				break;
			case "EvnPLDispOrpOld":
				$main_alias = "EPLDO";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispOrpOld($this, $data);
				break;
			case "EvnPLDispOrpSec":
				$main_alias = "EPLDO";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispOrpSec($this, $data);
				break;
			case "EvnPLDispTeenInspectionPeriod":
			case "EvnPLDispTeenInspectionProf":
			case "EvnPLDispTeenInspectionPred":
				$main_alias = "EPLDTI";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispTeenInspectionPred($this, $data);
				break;
			case "EvnPLDispDopStream":
				$main_alias = "EPLDD";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispDopStream($this);
				break;
			case "EvnPLDispTeen14Stream":
				$main_alias = "EPLDT14";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispTeen14Stream($this);
				break;
			case "EvnPLDispOrpStream":
				$main_alias = "EPLDO";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispOrpStream($this);
				break;
			case "EvnPLDispMigrant":
				$main_alias = "EPLDM";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispMigrant($this, $data);
				break;
			case "EvnPLDispDriver":
				$main_alias = "EPLDD";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLDispDriver($this, $data);
				break;
			case "EvnUsluga":
				$main_alias = "EvnUsluga";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnUsluga($this, $data);
				}
				break;
			case "EvnUslugaStom":
				$main_alias = "EvnUsluga";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnUslugaStom($this, $data);
				}
				break;
			case "EvnSection":
				$main_alias = "ESEC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnSection($this, $data, $dbf, $isBDZ, $filter, $queryParams);
				break;
			case "EvnDiag":
				$main_alias = "EPSD";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnDiag($this);
				}
				break;
			case "EvnLeave":
				$main_alias = "ELV";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnLeave();
				}
				break;
			case "EvnStick":
				$main_alias = "EST";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnStick($this);
				}
				break;
			case "KvsPerson":
				$main_alias = "PS";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsPerson($this, $PS_prefix);
				}
				break;
			case "EPLPerson":
				$main_alias = "PS";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EPLPerson($this, $PL_prefix);
				}
				break;
			case "EPLStomPerson":
				$main_alias = "PS";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EPLStomPerson($this, $PLS_prefix);
				}
				break;
			case "KvsPersonCard":
				$main_alias = "PC";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsPersonCard($this, $data);
				}
				break;
			case "KvsEvnDiag":
				$main_alias = "EDPS";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnDiag($this, $data);
				}
				break;
			case "KvsEvnPS":
				$main_alias = "EPS";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnPS($this, $data);
				}
				break;
			case "KvsEvnSection":
				$main_alias = "ESEC";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnSection($this, $data);
				}
				break;
			case "KvsNarrowBed":
				$main_alias = "ESNB";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsNarrowBed($this, $data);
				}
				break;
			case "KvsEvnUsluga":
				$main_alias = "EU";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnUsluga($this, $data);
				}
				break;
			case "KvsEvnUslugaOB":
				$main_alias = "EU";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnUslugaOB($this, $data);
				}
				break;
			case "KvsEvnUslugaAn":
				$main_alias = "EU";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnUslugaAn($data);
				}
				break;
			case "KvsEvnUslugaOsl":
				$main_alias = "EU";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnUslugaOsl($this, $data);
				}
				break;
			case "KvsEvnDrug":
				$main_alias = "ED";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnDrug($this, $data);
				}
				break;
			case "KvsEvnLeave":
				$main_alias = "ELV";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnLeave($this, $data);
				}
				break;
			case "KvsEvnStick":
				$main_alias = "EST";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_KvsEvnStick($this, $data);
				}
				break;
			case "EvnAgg":
				$main_alias = "EvnAgg";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnAgg($this, $data);
				}
				break;
			case "EvnAggStom":
				$main_alias = "EvnAgg";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnAggStom($this, $data);
				}
				break;
			case "EvnVizitPL":
				$main_alias = "EVizitPL";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnVizitPL($this, $data, $isBDZ, $dbf, $filter, $queryParams);
				break;
			case "EvnVizitPLStom":
				$main_alias = "EVPLS";
				if ($dbf === true) {
					$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnVizitPLStom($this, $data, $isBDZ, $dbf, $filter, $queryParams);
				}
				break;
			case "EvnPL": // Талон амбулаторного пациента: Поиск
				$main_alias = "EPL";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPL($this, $data, $isBDZ, $dbf, $filter, $queryParams);
				break;
			case "EvnPLStom":
				$main_alias = "EPLS";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLStom($this, $data, $isBDZ, $dbf, $filter, $queryParams);
				break;
			case "EvnPS":
				$main_alias = "EPS";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPS($this, $data, $isBDZ, $dbf, $filter, $queryParams);
				break;
			case "EvnRecept":
				$main_alias = "ER";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnRecept($this, $data, $isBDZ);
				break;
			case "EvnReceptGeneral":
				$main_alias = "ERG";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnReceptGeneral($this, $data, $isBDZ);
				break;
			case "EvnUslugaPar":
				$main_alias = "EUP";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnUslugaPar($this, $data, $queryParams);
				break;
			case "WorkPlacePolkaReg":
				$main_alias = "PC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_WorkPlacePolkaReg($this, $data, $isBDZ);
				break;
			case "PersonCard":
				$main_alias = "PC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonCard($this, $data, $isBDZ, $filter);
				break;
			case "PersonCallCenter":
				$main_alias = "PS";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonCallCenter($this, $data, $isBDZ, $filter);
				break;
			case "PersonCardStateDetail":
				$main_alias = "PCSD";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonCardStateDetail($this);
				break;
			case "PersonDisp":
				$main_alias = "PD";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonDisp($this, $data);
				break;
			case "PersonPrivilege":
				$main_alias = "PP";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonPrivilege($this, $data, $isBDZ);
				break;
			case "PersonPrivilegeWOW":
				$main_alias = "PPW";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonPrivilegeWOW($this);
				break;
			case "RegisterSixtyPlus":
				$select[] = Search_model_selectFunc::searchData_SearchFormType_RegisterSixtyPlus($this);
				break;
			case "EvnPLWOW":
				$main_alias = "EPW";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnPLWOW($this);
				break;
			case "EvnDtpWound":
				$main_alias = "EDW";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnDtpWound($this);
				break;
			case "EvnDtpDeath":
				$main_alias = "EDD";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnDtpDeath($this);
				break;
			case "OrphanRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_OrphanRegistry($this);
				break;
			case "ACSRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_ACSRegistry($this);
				break;
			case "CrazyRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_CrazyRegistry($this);
				break;
			case "NarkoRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_NarkoRegistry($this);
				break;
			case "PersonRegisterBase":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonRegisterBase($this);
				break;
			case "PalliatRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PalliatRegistry($this);
				break;
			case "NephroRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_NephroRegistry($this);
				break;
			case "EndoRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EndoRegistry($this);
				break;
			case "IBSRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_IBSRegistry($this);
				break;
			case "ProfRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_ProfRegistry($this);
				break;
			case "TubRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_TubRegistry($this);
				break;
			case "DiabetesRegistry":
			case "LargeFamilyRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_LargeFamilyRegistry($this);
				break;
			case "FmbaRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_FmbaRegistry($this);
				break;
			case "HIVRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_HIVRegistry($this);
				break;
			case "VenerRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_VenerRegistry($this);
				break;
			case "HepatitisRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_HepatitisRegistry($this);
				break;
			case "OnkoRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_OnkoRegistry($this);
				break;
			case "GeriatricsRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_GeriatricsRegistry($this);
				break;
			case "IPRARegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_IPRARegistry($this);
				break;
			case "ECORegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_ECORegistry($this, $data, $queryParams);
				break;
			case "BskRegistry":
				$main_alias = "R";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_BskRegistry($this);
				break;
			case "ReabRegistry":
				$main_alias = "R";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_ReabRegistry($this);
				break;
			case "AdminVIPPerson":
				$main_alias = "R";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_AdminVIPPerson($this);
				break;
			case "ZNOSuspectRegistry":
				$main_alias = "R";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_ZNOSuspectRegistry($this);
				break;
			case "ReanimatRegistry":
				$main_alias = "RR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_ReanimatRegistry($this);
				break;
			case "EvnInfectNotify":
				$main_alias = "EIN";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnInfectNotify($this);
				break;
			case "EvnNotifyHepatitis":
				$main_alias = "ENH";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyHepatitis($this);
				break;
			case "EvnOnkoNotify":
				$main_alias = "EON";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnOnkoNotify($this);
				break;
			case "EvnNotifyRegister":
				$main_alias = "EN";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyRegister($this);
				break;
			case "EvnNotifyOrphan":
				$main_alias = "ENO";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyOrphan($this);
				break;
			case "EvnNotifyCrazy": // Психиатрия
				$main_alias = "ENC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyCrazy($this);
				break;
			case "EvnNotifyNarko": // Психиатрия
				$main_alias = "ENC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyNarko($this);
				break;
			case "EvnNotifyTub": // Туберкулез
				$main_alias = "ENC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyTub($this);
				break;
			case "EvnNotifyNephro": // Нефрология
				$main_alias = "ENC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyNephro($this);
				break;
			case "EvnNotifyProf": // Профзаболевания
				$main_alias = "ENC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyProf($this);
				break;
			case "EvnNotifyHIV": // ВИЧ
				$main_alias = "ENB";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyHIV($this);
				break;
			case "EvnNotifyVener": // Туберкулез
				$main_alias = "ENC";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnNotifyVener($this);
				break;
			case "PalliatNotify":
				$main_alias = "PN";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PalliatNotify($this);
				break;
			case "PersonDopDispPlan": // План профилактических мероприятий
				$main_alias = "PDDP";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_PersonDopDispPlan($this);
				break;
			case "RzhdRegistry":
				$main_alias = 'RR';
				$select[] = Search_model_selectFunc::searchData_SearchFormType_RzhdRegistry($this);
				break;
			case "ONMKRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_ONMKRegistry($this);
				break;
			case "SportRegistry":
				$main_alias = "SR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_SportRegistry($this);
				break;
			case "HTMRegister":
				$main_alias = "HR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_HTMRegister($this);
				break;
			case "GibtRegistry":
				$main_alias = "PR";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_GibtRegistry($this);
				break;
			case "EvnERSBirthCertificate":
				$main_alias = "ERS";
				$select[] = Search_model_selectFunc::searchData_SearchFormType_EvnERSBirthCertificate($this);
				break;
			default:
				throw new Exception("Необходимо задать цель поиска.");
				break;
		}
		if (!empty($archive_database_enable) && !empty($main_alias) && !empty($archiveTable)) {
			$archive_main_alias = $main_alias;
			if ($archiveTable == 'CmpCloseCard') {
				$archive_main_alias = 'CLC';
			} else if ($archiveTable == 'Evn') {
				$archive_main_alias = 'Evn';
			}
			$select_main_alias = $archive_main_alias;
			if ($archive_main_alias == 'EvnPLDispDop13') {
				$select_main_alias = 'EPLDD13';
			} else if ($archive_main_alias == 'Evn') {
				$select_main_alias = 'Evn';
			}

			$archive_main_alias = ($select_main_alias == 'Evn') ? 'EPL' : $select_main_alias;
			$archiveTable = ($archiveTable == 'Evn') ? 'EvnPL' : $archiveTable;

			if ($select_main_alias == 'Evn') {
				$select[] = (count($select) ? ',' : '') . " case when coalesce({$archive_main_alias}.{$archiveTable}_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"";
			} else {
				$select[] = (count($select) ? ',' : '') . " case when coalesce({$select_main_alias}.{$archiveTable}_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"";
			}
			
			if (empty($_REQUEST["useArchive"])) {
				$filter .= " and coalesce({$archive_main_alias}.{$archiveTable}_IsArchive, 1) = 1";
			} elseif (!empty($_REQUEST["useArchive"]) && $_REQUEST["useArchive"] == 1) {
				$data["start"] = $data["start"] - $data["archiveStart"];
				$filter .= " and coalesce({$archive_main_alias}.{$archiveTable}_IsArchive, 1) = 2";
			} else {
				$filter .= "";
			}
			
		}
		// Фильтр "Врач"
		// Примечание: "MedPersonal_id" зарезервирован. Используем "MedPersonal_iid".
		if (isset($data['MedPersonal_iid'])) {
			$filter.= " AND {$main_alias}.MedPersonal_id = :MedPersonal_iid ";
			$queryParams['MedPersonal_iid'] = $data['MedPersonal_iid'];
		}

		$query .= implode(" \n\t", $select). "
			-- end select
			from
				-- from
		";
		$joinDopDispSecond = "";
		$filterDopDispSecond = "";
		if ($data["SearchFormType"] == "PersonPrivilege"){
			set_time_limit(100);		//добавил вермя на выполнение скрипта, иначе он не успевает завершиться и сервер возвращает код ошибки 500
		}

		switch ($data["SearchFormType"]) {
			case "PersonPrivilegeWOW":
                $query .= (isset($data["Refuse_id"]))?" v_PersonState_All PS ":" v_PersonState PS ";
				break;
			case "RegisterSixtyPlus":
				$query .= " RegisterSixtyPlus RPlus ";
				break;
			case "EvnPLDispDop13":
				$query .= " v_EvnPLDispDop13Top EPLDD13 ";
				break;
			case "EvnPLDispDop":
			case "EvnPLDispDop13Sec":
			case "EvnPLDispProf":
			case "EvnPLDispScreen":
			case "EvnPLDispScreenChild":
			case "EvnPLDispTeen14":
			case "EvnPLDispDopStream":
			case "EvnPLDispTeen14Stream":
			case "EvnPLDispOrp":
			case "EvnPLDispOrpOld":
			case "EvnPLDispOrpSec":
			case "EvnPLDispTeenInspectionPeriod":
			case "EvnPLDispTeenInspectionProf":
			case "EvnPLDispTeenInspectionPred":
			case "EvnPLDispOrpStream":
			case "EvnPLDispMigrant":
			case "EvnPLDispDriver":
			case "EPLStomPerson":
			case "EvnPLStom":
			case "EvnVizitPLStom":
			case "EvnUslugaStom":
			case "EvnAggStom":
			case "EvnPS":
			case "EvnSection":
			case "EvnDiag":
			case "EvnLeave":
			case "EvnStick":
			case "KvsPerson":
			case "KvsPersonCard":
			case "KvsEvnDiag":
			case "KvsEvnPS":
			case "KvsEvnSection":
			case "KvsNarrowBed":
			case "KvsEvnUsluga":
			case "KvsEvnUslugaOB":
			case "KvsEvnUslugaAn":
			case "KvsEvnUslugaOsl":
			case "KvsEvnDrug":
			case "KvsEvnLeave":
			case "KvsEvnStick":
			case "EvnRecept":
			case "EvnReceptGeneral":
			case "EvnPLWOW":
			case "EvnDtpWound":
			case "EvnDtpDeath":
			case "EvnUslugaPar":
			case "EvnInfectNotify":
			case "EvnNotifyHepatitis":
			case "EvnOnkoNotify":
			case "EvnNotifyOrphan":
			case "EvnNotifyRegister":
			case "PersonRegisterBase":
			case "PalliatRegistry":
			case "EvnNotifyCrazy":
			case "EvnNotifyNarko":
			case "EvnNotifyTub":
			case "EvnNotifyNephro":
			case "EvnNotifyProf":
			case "NephroRegistry":
			case "EndoRegistry":
			case "IBSRegistry":
			case "ProfRegistry":
			case "EvnNotifyHIV":
			case "EvnNotifyVener":
			case "PalliatNotify":
			case "HepatitisRegistry":
			case "OnkoRegistry":
			case "GeriatricsRegistry":
			case "BskRegistry":
			case "ReabRegistry":
			case "AdminVIPPerson":
			case "ZNOSuspectRegistry":
			case "ReanimatRegistry":
			case "IPRARegistry":
			case "ECORegistry":
			case "OrphanRegistry":
			case "ACSRegistry":
			case "CrazyRegistry":
			case "NarkoRegistry":
			case "TubRegistry":
			case "DiabetesRegistry":
			case "FmbaRegistry":
			case "LargeFamilyRegistry":
			case "HIVRegistry":
			case "VenerRegistry":
			case "ONMKRegistry":
			case "SportRegistry":
			case "GibtRegistry":
			case "EvnERSBirthCertificate":
			    if($data["PersonPeriodicType_id"] == 2) {
                    $query .= " v_Person_all PS ";
                } else {
			        $query .= (!isset($data["Refuse_id"])) ? " v_PersonState PS " : " v_PersonState_all PS ";
                }
				if (allowPersonEncrypHIV($data["session"])) {
					$query .= " left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id ";
				}
				break;
			case "PersonDopDisp":
				$query .= (isset($data["Refuse_id"]))?" v_PersonState_All PS ":" v_PersonState PS ";
				if (allowPersonEncrypHIV($data["session"])) {
					$query .= " left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id ";
				}
				break;
			case "WorkPlacePolkaReg":
			case "PersonCallCenter":
			case "PersonCard":
			case "PersonDispOrp":
			case "PersonDispOrpPeriod":
			case "PersonDispOrpPred":
			case "PersonDispOrpProf":
			case "PersonDispOrpOld":
			case "PersonDisp":
			case "PersonCardStateDetail":
			case "PersonPrivilege":
			case "PersonDopDispPlan":

				if ($data["SearchFormType"] == "PersonDispOrpPeriod") {
					$query .= "
                	    v_PersonState_all PS
					";
				} else {
					$queryWithArray[] = "
                    PCSD1 as (
                        select
                            max(Personcard_id) as Personcard_id,
                            Person_id
                        from
                            v_PersonCard_all pc
                        where
                            pc.Lpu_id = :Lpu_id
                        group by Person_id
                	)";

					$query .= "
                	    PCSD1 
                	    inner join v_PersonState_all PS on PCSD1.person_id=PS.person_id
					";
				}

                if (allowPersonEncrypHIV($data["session"])) {
					$query .= " left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id ";
				}
				if ($this->getRegionNick() == "ufa" and $data["SearchFormType"] == "PersonCard" and $data["hasObrTalonMse"] == "on") {
					$mseDiagFilter = "(1=1)";
					if (!empty($data["Diag_Code_From"])) {
						$mseDiagFilter .= " and D1oa.Diag_Code >= :Diag_Code_From";
						$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
					}
					if (!empty($data["Diag_Code_To"])) {
						$mseDiagFilter .= " and D1oa.Diag_Code <= :Diag_Code_To";
						$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
					}
					$query .= "
						inner join lateral(
							select
								D1oa.Diag_Code, 
								IGT.InvalidGroupType_Name
							from
								v_EvnMse mse
								inner join InvalidGroupType IGT on IGT.InvalidGroupType_id = mse.InvalidGroupType_id
								left join v_Diag D1oa on D1oa.Diag_id = mse.Diag_id
							where {$mseDiagFilter}
							order by mse.EvnMse_SendStickDate desc
							limit 1
						) as OBTMSE on true
					";
				}
				break;
		}
		if (isset($data["soc_card_id"]) && strlen($data["soc_card_id"]) >= 25) {
			$filter .= " and left(ps.Person_SocCardNum, 19) = :SocCardNum ";
			$queryParams["SocCardNum"] = substr($data["soc_card_id"], 0, 19);
		}
		switch ($data["SearchFormType"]) {
			case "CmpCallCard":
				$query .= Search_model_selectBody::selectBody_CmpCallCard($this);
				Search_model_selectParams::selectParams_CmpCallCard($this, $data, $filter, $queryParams);
				break;
			case "CmpCloseCard":
				$query .= Search_model_selectBody::selectBody_CmpCloseCard($this, $data);
				Search_model_selectParams::selectParams_CmpCloseCard($this, $data, $filter, $queryParams);
				break;
			case "PersonPrivilegeWOW":
				$query .= Search_model_selectBody::selectBody_PersonPrivilegeWOW();
				if (isset($data["PrivilegeTypeWow_id"])) {
					$filter .= " and PTW.PrivilegeTypeWow_id = :PrivilegeTypeWow_id ";
					$queryParams["PrivilegeTypeWow_id"] = $data["PrivilegeTypeWow_id"];
				}
				break;
			case "RegisterSixtyPlus":
				$query .= Search_model_selectBody::selectBody_RegisterSixtyPlus();
				Search_model_selectParams::selectParams_RegisterSixtyPlus($data, $filter, $queryParams);
				break;
			case "EvnPLWOW":
				$query .= Search_model_selectBody::selectBody_EvnPLWOW($this, $data);
				if (isset($data["PrivilegeTypeWow_id"])) {
					$filter .= " and PTW.PrivilegeTypeWow_id = :PrivilegeTypeWow_id ";
					$queryParams["PrivilegeTypeWow_id"] = $data["PrivilegeTypeWow_id"];
				}
				break;
			case "EvnDtpWound":
				$getLpuIdFilterString = $this->getLpuIdFilter($data);
				$query .= ($data["PersonPeriodicType_id"] == 2)
					?" inner join v_EvnDtpWound EDW on EDW.Server_id = PS.Server_id and EDW.PersonEvn_id = PS.PersonEvn_id and EDW.Lpu_id {$getLpuIdFilterString}"
					:" inner join v_EvnDtpWound EDW on EDW.Person_id = PS.Person_id and EDW.Lpu_id {$getLpuIdFilterString}";
				if (isset($data["EvnDtpWound_setDate_Range"][0])) {
					$filter .= " and EDW.EvnDtpWound_setDate >= :EvnDtpWound_setDate_Range_0";
					$queryParams["EvnDtpWound_setDate_Range_0"] = $data["EvnDtpWound_setDate_Range"][0];
				}
				if (isset($data["EvnDtpWound_setDate_Range"][1])) {
					$filter .= " and EDW.EvnDtpWound_setDate <= :EvnDtpWound_setDate_Range_1";
					$queryParams["EvnDtpWound_setDate_Range_1"] = $data["EvnDtpWound_setDate_Range"][1];
				}
				break;
			case "EvnDtpDeath":
				$query .= Search_model_selectBody::selectBody_EvnDtpDeath($this, $data);
				Search_model_selectParams::selectParams_EvnDtpDeath($data, $filter, $queryParams);
				break;
			case "PersonDopDisp":
				$query .= Search_model_selectBody::selectBody_PersonDopDisp($this, $data);
				Search_model_selectParams::selectParams_PersonDopDisp($data, $filter, $queryParams);
				break;
			case "PersonDispOrpPeriod":
				$query .= Search_model_selectBody::selectBody_PersonDispOrpPeriod($this, $data);
				Search_model_selectParams::selectParams_PersonDispOrpPeriod($data, $filter, $queryParams);
				break;
			case "PersonDispOrpPred":
				$query .= Search_model_selectBody::selectBody_PersonDispOrpPred($this, $data);
				Search_model_selectParams::selectParams_PersonDispOrpPred($data, $filter, $queryParams);
				break;
			case "PersonDispOrpProf":
				$query .= Search_model_selectBody::selectBody_PersonDispOrpProf($this, $data);
				Search_model_selectParams::selectParams_PersonDispOrpProf($data, $filter, $queryParams);
				break;
			case "PersonDispOrp":
				$query .= Search_model_selectBody::selectBody_PersonDispOrp($this, $data);
				Search_model_selectParams::selectParams_PersonDispOrp($data, $filter, $queryParams);
				break;
			case "PersonDispOrpOld":
				$query .= Search_model_selectBody::selectBody_PersonDispOrpOld($this, $data);
				Search_model_selectParams::selectParams_PersonDispOrpOld($data, $filter, $queryParams);
				break;
			case "EvnPLDispDop13":
				$this->load->model("EvnPLDispDop13_model", "EvnPLDispDop13_model");
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispDop13($joinDopDispSecond, $filterDopDispSecond, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispDop13($this, $data, $filter, $queryParams);
				$variablesArray[] = "to_char(:PersonDopDisp_Year, 'yyyy') || '-12-31' as PPD_YearEndDate";
				break;
			case "EvnPLDispDop13Sec":
				$this->load->model("EvnPLDispDop13_model", "EvnPLDispDop13_model");
				Search_model_selectBodyCommon::selectBody_EvnPLDispDop13Sec($query, $joinDopDispSecond, $filterDopDispSecond, $this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispDop13Sec($this, $data, $filter, $queryParams);
				$variablesArray[] = "to_char(:PersonDopDisp_Year, 'yyyy') || '-12-31' as PPD_YearEndDate";
				break;
			case "EvnPLDispProf":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispProf($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispProf($data, $filter, $queryParams);
				break;
			case "EvnPLDispScreen":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispScreen($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispScreen($data, $filter, $queryParams);
				break;
			case "EvnPLDispScreenChild":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispScreenChild($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispScreenChild($data, $filter, $queryParams);
				break;
			case "EvnPLDispDop":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispDop($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispDop($data, $filter, $queryParams);
				break;
			case "EvnPLDispTeen14":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispTeen14($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispTeen14($data, $filter, $queryParams);
				break;
			case "EvnPLDispOrp":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispOrp($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispOrp($data, $filter, $queryParams);
				break;
			case "EvnPLDispOrpOld":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispOrpOld($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispOrpOld($data, $filter, $queryParams);
				break;
			case "EvnPLDispOrpSec":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispOrpSec($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispOrpSec($data, $filter, $queryParams);
				break;
			case "EvnPLDispTeenInspectionPeriod":
			case "EvnPLDispTeenInspectionProf":
			case "EvnPLDispTeenInspectionPred":
				$query .= Search_model_selectBodyCommon::selectBody_EvnPLDispTeenInspectionPred($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnPLDispTeenInspectionPred($data, $filter, $queryParams);
				break;
			case "EvnPLDispDopStream":
				$query .= Search_model_selectBody::selectBody_EvnPLDispDopStream($this, $data);
				Search_model_selectParams::selectParams_EvnPLDispDopStream($data, $filter, $queryParams);
				break;
			case "EvnPLDispTeen14Stream":
				$query .= Search_model_selectBody::selectBody_EvnPLDispTeen14Stream($this, $data);
				Search_model_selectParams::selectParams_EvnPLDispTeen14Stream($data, $filter, $queryParams);
				break;
			case "EvnPLDispOrpStream":
				$query .= Search_model_selectBody::selectBody_EvnPLDispOrpStream($this, $data);
				Search_model_selectParams::selectParams_EvnPLDispOrpStream($data, $filter, $queryParams);
				break;
			case "EvnPLDispMigrant":
				$query .= Search_model_selectBody::selectBody_EvnPLDispMigrant($this, $data);
				Search_model_selectParams::selectParams_EvnPLDispMigrant($data, $filter, $queryParams);
				break;
			case "EvnPLDispDriver":
				$query .= Search_model_selectBody::selectBody_EvnPLDispDriver($this, $data);
				Search_model_selectParams::selectParams_EvnPLDispDriver($data, $filter, $queryParams);
				break;
			case "EPLPerson":
			case "EvnAgg":
			case "EvnPL":
			case "EvnUsluga":
			case "EvnVizitPL":
				$getLpuIdFilter = $this->getLpuIdFilter($data);
				$query .= Search_model_selectBodyCommon::selectBody_EvnVizitPL($this, $dbf, $PL_prefix, $data, $getLpuIdFilter);
				Search_model_selectParamsCommon::selectParams_EvnVizitPL($this, $dbf, $getLpuIdFilter, $data, $filter, $queryParams, $queryWithArray);
				break;
			case "EPLStomPerson":
			case "EvnPLStom":
			case "EvnVizitPLStom":
			case "EvnUslugaStom":
			case "EvnAggStom":
				$getLpuIdFilter = $this->getLpuIdFilter($data);
				$query .= Search_model_selectBodyCommon::selectBody_EvnAggStom($this, $dbf, $PLS_prefix, $data, $getLpuIdFilter);
				Search_model_selectParamsCommon::selectParams_EvnAggStom($this, $dbf, $getLpuIdFilter, $data, $filter, $queryParams);
				break;
			case "EvnPS":
			case "EvnSection":
			case "EvnDiag":
			case "EvnLeave":
			case "EvnStick":
			case "KvsPerson":
			case "KvsPersonCard":
			case "KvsEvnDiag":
			case "KvsEvnPS":
			case "KvsEvnSection":
			case "KvsNarrowBed":
			case "KvsEvnUsluga":
			case "KvsEvnUslugaOB":
			case "KvsEvnUslugaAn":
			case "KvsEvnUslugaOsl":
			case "KvsEvnDrug":
			case "KvsEvnLeave":
			case "KvsEvnStick":
				$getLpuIdFilterString = $this->getLpuIdFilter($data);
				$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick($data, $getLpuIdFilterString);
				if ($dbf === true) {
					switch ($data["SearchFormType"]) {
						case "EvnPS":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_EvnPS($getLpuIdFilterString);
							break;
						case "EvnSection":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_EvnSection($getLpuIdFilterString);
							break;
						case "EvnDiag":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_EvnDiag($getLpuIdFilterString);
							break;
						case "EvnLeave":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_EvnLeave($getLpuIdFilterString);
							break;
						case "EvnStick":
							$query .= " inner join v_EvnStick EST on EST.EvnStick_pid = EPS.EvnPS_id and EST.Lpu_id {$getLpuIdFilterString} ";
							break;
						case "KvsPerson":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsPerson($PS_prefix, $data, $getLpuIdFilterString);
							break;
						case "KvsPersonCard":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsPersonCard();
							break;
						case "KvsEvnDiag":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnDiag($getLpuIdFilterString);
							break;
						case "KvsEvnPS":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnPS($getLpuIdFilterString);
							break;
						case "KvsEvnSection":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnSection($getLpuIdFilterString);
							break;
						case "KvsNarrowBed":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsNarrowBed($getLpuIdFilterString);
							break;
						case "KvsEvnUsluga":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnUsluga($getLpuIdFilterString);
							break;
						case "KvsEvnUslugaOB":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnUslugaOB($getLpuIdFilterString);
							break;
						case "KvsEvnUslugaAn":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnUslugaAn($getLpuIdFilterString);
							break;
						case "KvsEvnUslugaOsl":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnUslugaOsl($getLpuIdFilterString);
							break;
						case "KvsEvnDrug":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnDrug($getLpuIdFilterString);
							break;
						case "KvsEvnLeave":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnLeave($getLpuIdFilterString);
							break;
						case "KvsEvnStick":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_KvsEvnStick($getLpuIdFilterString);
							break;
					}
					$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_NoSearchFormTypeSwitch($PS_prefix, $data);
				} else {
					switch ($data["SearchFormType"]) {
						case "EvnPS":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_EvnPSNoDbf($this, $data, $getLpuIdFilterString);
							break;
						case "EvnSection":
							$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_EvnSectionNoDbf($getLpuIdFilterString);
							break;
					}
					if ($data["PersonPeriodicType_id"] == 3) {
						$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_NoDbfNoSwitch();
					}
				}
				Search_model_selectParamsCommon::selectParams_KvsEvnStick($this, $dbf, $data, $filter, $queryParams);
				if (isset($data["LpuUnitType_did"])) {
					$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_LpuUnitType_did($getLpuIdFilterString);
				}
				$this->load->model("EvnPS_model", "EvnPS_model");
				$query .= Search_model_selectBodyCommon::selectBody_KvsEvnStick_common($this, $data, $getLpuIdFilterString);
				break;
			case "EvnRecept":
				$query .= Search_model_selectBodyCommon::selectBody_EvnRecept($this, $data, $isFarmacy);
				Search_model_selectParamsCommon::selectParams_EvnRecept($this, $isFarmacy, $data, $filter, $queryParams);
				break;
			case "EvnReceptGeneral":
				$query .= Search_model_selectBodyCommon::selectBody_EvnReceptGeneral($this, $data, $isFarmacy);
				Search_model_selectParamsCommon::selectParams_EvnReceptGeneral($data, $filter, $queryParams);
				break;
			case "EvnUslugaPar":
				$query .= Search_model_selectBodyCommon::selectBody_EvnUslugaPar($this, $data);
				Search_model_selectParamsCommon::selectParams_EvnUslugaPar($this, $data, $filter, $queryParams);
				$evnUC_filter = " and (EvnParent.EvnClass_SysNick <> 'EvnUslugaPar' or EvnParent.EvnClass_SysNick is null)";
				if (isset($data["UslugaComplex_id"])) {
					$evnUC_filter .= " and EUP.UslugaComplex_id = :UslugaComplex_id";
				}
				if (!empty($data["Part_of_the_study"]) && $data["Part_of_the_study"] && !empty($data["UslugaComplex_id"])) {
					$uc_filter = "
						UNION ALL
						{$query}
                       where (1=1)
							-- where
						    and EUP.EvnUslugaPar_id in (
							    select eup2.EvnUslugaPar_rid 
							    from
							        v_EvnUslugaPar eup2 
							        left join v_UslugaTest ut on ut.UslugaTest_pid = eup2.evnuslugapar_id
							    where ut.UslugaComplex_id = :UslugaComplex_id
						    )
						    and
					";
				}
				break;
			case "PersonCardStateDetail":
				$query .= Search_model_selectBodyCommon::selectBody_PersonCardStateDetail();
				Search_model_selectParamsCommon::selectParams_PersonCardStateDetail($data, $filter, $queryParams);
				break;
			case "PersonCardStateDetail_old":
				$query .= Search_model_selectBodyCommon::selectBody_PersonCardStateDetail_old();
				Search_model_selectParamsCommon::selectParams_PersonCardStateDetail_old($data, $filter, $queryParams);
				break;
			case "PersonCardStateDetail_old1":
				//TODO Дубляж ключа PersonCardStateDetail_old нужно разобраться почему и для чего
				$query .= Search_model_selectBodyCommon::selectBody_PersonCardStateDetail_old1();
				Search_model_selectParamsCommon::selectParams_PersonCardStateDetail_old1($data, $filter, $queryParams);
				break;
			case "PersonDisp":
				$query .= Search_model_selectBodyCommon::selectBody_PersonDisp($data);
				Search_model_selectParamsCommon::selectParams_PersonDisp($data, $filter, $queryParams);
				break;
			case "EvnInfectNotify":
				$query .= Search_model_selectBody::selectBody_EvnInfectNotify($data);
				Search_model_selectParamsCommon::selectParams_EvnInfectNotify($this, $data, $filter, $queryParams);
				break;
			case "EvnNotifyHepatitis":
				$query .= Search_model_selectBody::selectBody_EvnNotifyHepatitis($data);
				Search_model_selectParams::selectParams_EvnNotifyHepatitis($data, $filter, $queryParams);
				break;
			case "EvnOnkoNotify":
				$query .= Search_model_selectBody::selectBody_EvnOnkoNotify($data);
				Search_model_selectParams::selectParams_EvnOnkoNotify($data, $filter, $queryParams);
				break;
			case "OnkoRegistry":
				$query .= Search_model_selectBody::selectBody_OnkoRegistry($data);
				Search_model_selectParams::selectParams_OnkoRegistry($data, $filter, $queryParams);
				break;
			case "GeriatricsRegistry":
				$query .= Search_model_selectBody::selectBody_GeriatricsRegistry();
				Search_model_selectParams::selectParams_GeriatricsRegistry($data, $filter, $queryParams);
				break;
			case "IPRARegistry":
				$query .= Search_model_selectBody::selectBody_IPRARegistry($this);
				Search_model_selectParams::selectParams_IPRARegistry($this, $data, $filter, $queryParams);
				break;
			case "ECORegistry":
				$query .= Search_model_selectBody::selectBody_ECORegistry($data);
				Search_model_selectParams::selectParams_ECORegistry($data, $filter, $queryParams);
				break;
			case "BskRegistry":
				$query .= Search_model_selectBody::selectBody_BskRegistry($data);
				Search_model_selectParams::selectParams_BskRegistry($data, $filter, $queryParams);
				break;
			case "ZNOSuspectRegistry":
				$query .= Search_model_selectBody::selectBody_ZNOSuspectRegistry($data);
				Search_model_selectParams::selectParams_ZNOSuspectRegistry($data, $filter, $queryParams);
				break;
			case "AdminVIPPerson":
				$query .= Search_model_selectBody::selectBody_AdminVIPPerson($data);
				Search_model_selectParams::selectParams_AdminVIPPerson($data, $filter, $queryParams);
				break;
			case "ReabRegistry":
				$query .= Search_model_selectBody::selectBody_ReabRegistry($data);
				Search_model_selectParams::selectParams_ReabRegistry($data, $filter, $queryParams);
				break;
			case "ReanimatRegistry":
				$query .= Search_model_selectBody::selectBody_ReanimatRegistry($data);
				Search_model_selectParams::selectParams_ReanimatRegistry($data, $filter, $queryParams);
				break;
			case "HepatitisRegistry":
				$query .= Search_model_selectBody::selectBody_HepatitisRegistry($data);
				Search_model_selectParams::selectParams_HepatitisRegistry($data, $filter, $queryParams);
				break;
			case "EvnNotifyRegister":
				$this->load->library("swPersonRegister");
				if (empty($data["PersonRegisterType_SysNick"]) || false == swPersonRegister::isAllow($data["PersonRegisterType_SysNick"])) {
					return false;
				}
				$query .= Search_model_selectBody::selectBody_EvnNotifyRegister($data);
				Search_model_selectParams::selectParams_EvnNotifyRegister($this, $data, $filter, $queryParams);
				break;
			case "EvnNotifyOrphan":
				$query .= Search_model_selectBody::selectBody_EvnNotifyOrphan($data);
				Search_model_selectParams::selectParams_EvnNotifyOrphan($data, $filter, $queryParams);
				break;
			case "EvnNotifyCrazy":
				$query .= Search_model_selectBody::selectBody_EvnNotifyCrazy($data);
				Search_model_selectParams::selectParams_EvnNotifyCrazy($data, $filter, $queryParams);
				break;
			case "EvnNotifyNarko":
				$query .= Search_model_selectBody::selectBody_EvnNotifyNarko($data);
				Search_model_selectParams::selectParams_EvnNotifyNarko($data, $filter, $queryParams);
				break;
			case "EvnNotifyNephro":
				$query .= Search_model_selectBody::selectBody_EvnNotifyNephro($data);
				Search_model_selectParams::selectParams_EvnNotifyNephro($this, $data, $filter, $queryParams);
				break;
			case "EvnNotifyProf":
				$query .= Search_model_selectBody::selectBody_EvnNotifyProf($data);
				Search_model_selectParams::selectParams_EvnNotifyProf($data, $filter, $queryParams);
				break;
			case "EvnNotifyTub":
				$query .= Search_model_selectBody::selectBody_EvnNotifyTub($data);
				Search_model_selectParams::selectParams_EvnNotifyTub($data, $filter, $queryParams);
				break;
			case "EvnNotifyHIV":
				$query .= Search_model_selectBody::selectBody_EvnNotifyHIV($data);
				Search_model_selectParams::selectParams_EvnNotifyHIV($data, $filter, $queryParams);
				break;
			case "EvnNotifyVener":
				$query .= Search_model_selectBody::selectBody_EvnNotifyVener($data);
				Search_model_selectParams::selectParams_EvnNotifyVener($data, $filter, $queryParams);
				break;
			case "PalliatNotify":
				$query .= Search_model_selectBody::selectBody_PalliatNotify();
				Search_model_selectParams::selectParams_PalliatNotify($data, $filter, $queryParams);
				break;
			case "OrphanRegistry":
				$query .= Search_model_selectBody::selectBody_OrphanRegistry();
				Search_model_selectParams::selectParams_OrphanRegistry($data, $filter, $queryParams);
				break;
			case "ACSRegistry":
				$query .= Search_model_selectBody::selectBody_ACSRegistry();
				Search_model_selectParams::selectParams_ACSRegistry($data, $filter, $queryParams);
				break;
			case "CrazyRegistry":
				$query .= Search_model_selectBody::selectBody_CrazyRegistry();
				Search_model_selectParams::selectParams_CrazyRegistry($data, $filter, $queryParams);
				break;
			case "NarkoRegistry":
				$query .= Search_model_selectBody::selectBody_NarkoRegistry();
				Search_model_selectParams::selectParams_NarkoRegistry($data, $filter, $queryParams);
				break;
			case "PersonRegisterBase":
				$this->load->library("swPersonRegister");
				if (empty($data["PersonRegisterType_SysNick"]) || false == swPersonRegister::isAllow($data["PersonRegisterType_SysNick"])) {
					return false;
				}
				$query .= Search_model_selectBody::selectBody_PersonRegisterBase();
				Search_model_selectParams::selectParams_PersonRegisterBase($data, $filter, $queryParams);
				break;
			case "PalliatRegistry":
				$this->load->library("swPersonRegister");
				if (empty($data["PersonRegisterType_SysNick"]) || false == swPersonRegister::isAllow($data["PersonRegisterType_SysNick"])) {
					return false;
				}
				$query .= Search_model_selectBody::selectBody_PalliatRegistry();
				Search_model_selectParams::selectParams_PalliatRegistry($data, $filter, $queryParams);
				break;
			case "NephroRegistry":
				$query .= Search_model_selectBody::selectBody_NephroRegistry($data);
				Search_model_selectParams::selectParams_NephroRegistry($data, $filter, $queryParams);
				break;
			case "EndoRegistry":
				$query .= Search_model_selectBody::selectBody_EndoRegistry();
				Search_model_selectParams::selectParams_EndoRegistry($data, $filter, $queryParams);
				break;
			case "IBSRegistry":
				$query .= Search_model_selectBody::selectBody_IBSRegistry();
				Search_model_selectParams::selectParams_IBSRegistry($data, $filter, $queryParams);
				break;
			case "ProfRegistry":
				$query .= Search_model_selectBody::selectBody_ProfRegistry();
				Search_model_selectParams::selectParams_ProfRegistry($data, $filter, $queryParams);
				break;
			case "TubRegistry":
				$query .= Search_model_selectBody::selectBody_TubRegistry();
				Search_model_selectParams::selectParams_TubRegistry($data, $filter, $queryParams);
				break;
			case "DiabetesRegistry":
			case "LargeFamilyRegistry":
				$query .= Search_model_selectBody::selectBody_LargeFamilyRegistry();
				Search_model_selectParams::selectParams_LargeFamilyRegistry($data, $filter, $queryParams);
				break;
			case "FmbaRegistry":
				$query .= Search_model_selectBody::selectBody_FmbaRegistry();
				Search_model_selectParams::selectParams_FmbaRegistry($data, $filter, $queryParams);
				break;
			case "HIVRegistry":
				$query .= Search_model_selectBody::selectBody_HIVRegistry();
				Search_model_selectParams::selectParams_HIVRegistry($data, $filter, $queryParams);
				break;
			case "VenerRegistry":
				$query .= Search_model_selectBody::selectBody_VenerRegistry();
				Search_model_selectParams::selectParams_VenerRegistry($data, $filter, $queryParams);
				break;
			case "PersonDopDispPlan":
				$query .= Search_model_selectBody::selectBody_PersonDopDispPlan($this, $data);
				Search_model_selectParams::selectParams_PersonDopDispPlan($data, $filter, $queryParams, $this);
				break;
			case "RzhdRegistry":
				$query .= Search_model_selectBody::selectBody_RzhdRegistry($this, $data);
				Search_model_selectParams::selectParams_RzhdRegistry($data, $filter, $queryParams);
				break;
			case "ONMKRegistry":
				$query .= Search_model_selectBody::selectBody_ONMKRegistry();
				Search_model_selectParams::selectParams_ONMKRegistry($data, $filter, $queryParams);
				break;
			case "SportRegistry":
				$query .= Search_model_selectBody::selectBody_SportRegistry();
				Search_model_selectParams::selectParams_SportRegistry($data, $filter, $queryParams);
				break;
			case "HTMRegister":
				$query .= Search_model_selectBody::selectBody_HTMRegister();
				Search_model_selectParams::selectParams_HTMRegister($data, $filter, $queryParams);
				break;
			case "GibtRegistry":
				$query .= Search_model_selectBody::selectBody_GibtRegistry();
				Search_model_selectParams::selectParams_GibtRegistry($data, $filter, $queryParams);
				break;
			case "EvnERSBirthCertificate":
				$query .= Search_model_selectBody::selectBody_EvnERSBirthCertificate();
				Search_model_selectParams::selectParams_EvnERSBirthCertificate($data, $filter, $queryParams);
				break;
		}
		if ($data["PersonPeriodicType_id"] == 3) {
			$filter .= "
				and exists(
					select 1
					from v_Person_all PStmp
					where PStmp.Person_id = PS.Person_id
			";
			$this->getPersonPeriodicFilters($data, $filter, $queryParams, $main_alias, "PStmp");
			$filter .= ") ";
		} else {
			$this->getPersonPeriodicFilters($data, $filter, $queryParams, $main_alias);
		}
		if (($data["SearchFormType"] == "PersonPrivilege")) {
			$query .= "
                inner join PersonPrivilege PP on PP.Person_id = PS.Person_id
                inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
                left join LATERAL  (select * from v_Polis pls where  pls.Polis_id = ps.Polis_id limit 1) pls on true
                left join LATERAL  (select * from v_WhsDocumentCostItemType PT_WDCIT where PT_WDCIT.WhsDocumentCostItemType_id = PT.WhsDocumentCostItemType_id limit 1) PT_WDCIT on true
                left join  LATERAL  (select * from v_ReceptFinance RF where RF.ReceptFinance_id = PT.ReceptFinance_id limit 1) RF on true
                left join  LATERAL  (select * from v_Address PUAdd where PUAdd.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id) limit 1) PUAdd on true
                left join  LATERAL  (select * from v_PMUserCache UserDel where UserDel.pmUser_id = PP.pmUser_delID limit 1) UserDel on true
                left join  LATERAL  (select * from v_PrivilegeCloseType PrivCT where PrivCT.PrivilegeCloseType_id = PP.PrivilegeCloseType_id limit 1) PrivCT on true
				left join lateral(
					select
						(
							i_DocPriv.DocumentPrivilege_Ser || ' ' ||
							i_DocPriv.DocumentPrivilege_Num || ' ' ||
							to_char(i_DocPriv.DocumentPrivilege_begDate, '{$this->dateTimeForm104}') || ' ' ||
							coalesce(i_Org.Org_Nick, i_DocPriv.DocumentPrivilege_Org, '')	
						) as DocumentPrivilege_Data
					from
					 	v_DocumentPrivilege i_DocPriv
						left join v_Org i_Org on i_Org.Org_id = i_DocPriv.Org_id
					where
						i_DocPriv.PersonPrivilege_id = PP.PersonPrivilege_id
					order by
						i_DocPriv.DocumentPrivilege_id
					limit 1
				) as DocPriv on true
			";
			if ($data["session"]["region"]["nick"] == "krym") {
				$code = (!empty($data["session"]["lpu_id"])) ? $data["session"]["lpu_id"] : "null";
				$query .= "
					left join lateral(
						select count(PC.PersonCard_id) as cntPC
						from
							v_PersonCard PC
							inner join v_LpuAttachType LAT on LAT.LpuAttachType_id = PC.LpuAttachType_id
						where PC.Person_id = PS.Person_id
						  and PC.Lpu_id = {$code}
						  and LAT.LpuAttachType_SysNick in ('main', 'slug')
					) as PCardChecks on true
				";
			}
			if ($this->regionNick == "kz") {
				$query .= "
					left join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT on PPSCPT.PersonPrivilege_id = PP.PersonPrivilege_id
					left join r101.SubCategoryPrivType SCPT on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
				";
			} else {
				$query .= " left join v_Diag Diag on Diag.Diag_id = PP.Diag_id";
			}
			$this->getPrivilegeFilters($data, $filter, $queryParams);
			$this->getPrivilegeAccessRightsFilters($data, $filter, $queryParams);
		} elseif (
			isset($data["RegisterSelector_id"]) ||
			isset($data["PrivilegeType_id"]) ||
			isset($data["Privilege_begDate"]) ||
			isset($data["Privilege_begDate_Range"][0]) ||
			isset($data["Privilege_begDate_Range"][1]) ||
			isset($data["Privilege_endDate"]) ||
			isset($data["Privilege_endDate_Range"][0]) ||
			isset($data["Privilege_endDate_Range"][1]) ||
			isset($data["Refuse_id"]) ||
			isset($data["RefuseNextYear_id"])
		) {
			$filter .= "
				and exists(
					select personprivilege_id
					from
						v_PersonPrivilege PP
			            inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
			        where PP.Person_id = PS.Person_id
			";
			$this->getPrivilegeFilters($data, $filter, $queryParams);
			$this->getPrivilegeAccessRightsFilters($data, $filter, $queryParams);
			$filter .= " limit 1) ";
		}
		if (in_array($data["SearchFormType"], ["EvnPS", "EvnSection", "EvnPL", "EvnPLStom", "EvnVizitPL", "EvnVizitPLStom", "PersonDisp", "EvnRecept", "EvnReceptGeneral", "CmpCallCard", "CmpCloseCard"])) {
			switch ($data["SearchFormType"]) {
				case "PersonDisp":
					$diag_field_code = "dg1.Diag_Code";
					break;
				case "EvnPL":
					$diag_field_code = ($dbf === true) ? "dbfdiag.Diag_Code" : "EVPLD.Diag_Code";
					break;
				case "EvnVizitPL":
				case "EvnVizitPLStom":
					$diag_field_code = ($dbf === true) ? "dbfdiag.Diag_Code" : "evpldiag.Diag_Code";
					break;
				case "EvnPLStom":
					$diag_field_code = ($dbf === true) ? "dbfdiag.Diag_Code" : "EVPLSD.Diag_Code";
					break;
				case "EvnPS":
					$diag_field_code = ["Dtmp.Diag_Code"];
					break;
				case "EvnSection":
					$diag_field_code = "Dtmp.Diag_Code";
					break;
				case "EvnReceptGeneral":
				case "EvnRecept":
					$diag_field_code = (!$isFarmacy) ? "ERDiag.Diag_Code" : null;
					break;
				case "CmpCallCard":
					$diag_field_code = (in_array($data["session"]["region"]["nick"], ["kareliya", "ufa"])) ? "CLD.Diag_Code" : "CD.CmpDiag_Code";
					break;
				case "CmpCloseCard":
					$diag_field_code = "CLD.Diag_Code";
					break;
				default:
					$diag_field_code = "D.Diag_Code";
			}
			$diagFilter = getAccessRightsDiagFilter($diag_field_code);
			if (!empty($diagFilter)) {
				$filter .= " and {$diagFilter}";
			}
		}
		if ((isset($data["Refuse_id"])) || (isset($data["RefuseNextYear_id"]))) {
			if (isset($data["Refuse_id"])) {
				$filter .= "
					and " . ($data['Refuse_id'] == 1 ? "not " : "") . "exists (
						select 1
						from
							v_PersonPrivilege PP
							inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
							left join v_WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = PT.WhsDocumentCostItemType_id
						where
							PP.Person_id = PS.Person_id
							and WDCIT.WhsDocumentCostItemType_Nick = 'fl'
							and coalesce(PS.Person_IsRefuse2, 1) = 2
							and PP.PersonPrivilege_begDate <= (select dt from mv)
							and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= (select dt from mv)::date)
						limit 1
					)
				";
			}
			if (isset($data["RefuseNextYear_id"])) {
				$filter .= "
					and " . ($data["RefuseNextYear_id"] == 1 ? "not " : "") . "exists (
						select 1
						from
							v_PersonPrivilege PPN
							inner join v_PrivilegeType PTN on PTN.PrivilegeType_id = PPN.PrivilegeType_id and PTN.ReceptFinance_id = 1
							left join lateral(
								select coalesce(PRN.PersonRefuse_IsRefuse, 1) as PersonRefuse_IsRefuse
								from v_PersonRefuse PRN
								where PRN.Person_id = PPN.Person_id
								  and coalesce(PRN.PersonRefuse_Year, date_part('year', (select dt from mv))) = date_part('year', (select dt from mv)) + 1
								limit 1
							) as PRefN on true
						where PPN.Person_id = PS.Person_id
						  and coalesce(PRefN.PersonRefuse_IsRefuse, 1) = 2
						  and PPN.PersonPrivilege_begDate <= (select dt from mv)
						  and (PPN.PersonPrivilege_endDate is null or PPN.PersonPrivilege_endDate >= (select dt from mv)::date)
					) 
				";
			}
		}
		if ($data["SearchFormType"] == "PersonCallCenter") {
			$personCardFilter = "";
			$this->getPersonCardFilters($data, $personCardFilter, $queryParams, $orderby, $pac_filter);
			if (!empty($personCardFilter)) {
				$filter .= " and PersonCard.PersonCard_id is not null";
			}
			$code = (!empty($personCardFilter)) ? $personCardFilter : "";
			$query .= "
				left join lateral(
					select
						PersonCard_id,
						PersonCard_Code,
						PersonCard_begDate,
						PersonCard_endDate,
						LpuAttachType_Name,
						LpuRegionType_Name,
						PersonCard_IsAttachCondit,
						PersonCardAttach_id,
						LpuRegion_id,
						Lpu_id,
						LpuRegion_fapid
					from v_PersonCard PC
					where PC.Person_id = PS.Person_id {$code}
					order by LpuAttachType_id
					limit 1
				) as PersonCard on true
				left join v_LpuRegion LR on LR.LpuRegion_id = PersonCard.LpuRegion_id
				left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PersonCard.LpuRegion_fapid
				left join v_Lpu AttachLpu on AttachLpu.Lpu_id = PersonCard.Lpu_id
				left join v_Address uaddr on uaddr.Address_id = ps.UAddress_id
				left join v_Address paddr on paddr.Address_id = ps.PAddress_id
				left join v_PersonRefuse PRef on PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year = date_part('year', (select dt from mv)) + 1
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join v_NewslatterAccept NA on NA.Person_id = PS.Person_id and NA.Lpu_id = :Lpu_id and NA.NewslatterAccept_endDate is null
				left join lateral(
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from v_PersonDisp
					where Person_id = ps.Person_id
					  and (PersonDisp_endDate is null or PersonDisp_endDate > (select dt from mv))
					  and Sickness_id in (1, 3, 4, 5, 6, 7, 8)
				) as disp on true
			";
		} elseif ($data["SearchFormType"] == "PersonCard") {
			if ($data["PersonCard_IsDms"] > 0) {
				$exists = ($data["PersonCard_IsDms"] == 1) ? " not " : "";
				$filter .= "
					and {$exists} exists(
						select PersonCard_id
						from v_PersonCard
						where Person_id = PC.Person_id
						  and LpuAttachType_id = 5
						  and PersonCard_endDate >= (select dt from mv)
						  and CardCloseCause_id is null
					)
				";
				$exists = ($data["PersonCard_IsDms"] == 1) ? " != " : " = ";
				$filter .= " and pc.LpuAttachType_id {$exists} 5 ";
			}
			if ($data["session"]["region"]["nick"] != "kz") {
				if (!empty($data["IsBDZ"]) && ($data["IsBDZ"] <> 0)) {
					$filter .= ($data["IsBDZ"] == 2) ? " and PS.Server_pid = 0" : " and PS.Server_pid <> 0";
				}
				if (in_array($data["session"]["region"]["nick"], ["perm", "ufa"])) {
					if (!empty($data["TFOMSIdent"]) && ($data["TFOMSIdent"] <> 0)) {
						$query .= " left join v_Person Pers on Pers.Person_id = PS.Person_id";
						$filter .= ($data["TFOMSIdent"] == 2) ? " and Pers.BDZ_Guid is not null" : " and Pers.BDZ_Guid is null";
					}
				}
				if (!empty($data["HasPolis_Code"]) && ($data["HasPolis_Code"] <> 0)) {
					$filter .= ($data["HasPolis_Code"] == 2) ? " and PS.Polis_id is not null" : " and PS.Polis_id is null";
				}
				if (!empty($data["PolisClosed"]) && $data["PolisClosed"] <> 0) {
					$query .= " left join v_Polis Pol on Pol.Polis_id = PS.Polis_id";
					if ($data["PolisClosed"] == 2) {
						$filter .= " and (PS.Polis_id is not null and Pol.Polis_endDate is not null)";
						if (isset($data["PolisClosed_Date_Range"][0])) {
							$filter .= " and Pol.Polis_endDate >= :PolisClosed_Date_Range_0::timestamp";
							$queryParams["PolisClosed_Date_Range_0"] = $data["PolisClosed_Date_Range"][0];
						}
						if (isset($data["PolisClosed_Date_Range"][1])) {
							$filter .= " and Pol.Polis_endDate <= :PolisClosed_Date_Range_1::timestamp";
							$queryParams["PolisClosed_Date_Range_1"] = $data["PolisClosed_Date_Range"][1];
						}
					} else {
						$filter .= " and (PS.Polis_id is not null and Pol.Polis_endDate is null)";
					}
				}
			}
			if ($data["PersonCardStateType_id"] == 1) {
				$query .= ($data["session"]["region"]["nick"] == "khak")
					?" left join lateral (select * from  v_PersonCard PC where PC.Person_id = PS.Person_id limit 1000) PC on true"
					:" inner join lateral (select * from  v_PersonCard PC where PC.Person_id = PS.Person_id limit 1000) PC on true";
				$filter .= " and (PC.PersonCard_endDate is null or PC.PersonCard_endDate::timestamp > (select dt from mv))";
			} else {
				$query .= " inner join v_PersonCard_all PC on PC.Person_id = PS.Person_id";
			}
			$query .= "
				left join lateral(
					select ACLT.AmbulatCardLocatType_Name,PACL.PersonAmbulatCard_id
					from
						v_PersonAmbulatCardLocat PACL
						left join AmbulatCardLocatType ACLT on ACLT.AmbulatCardLocatType_id = PACL.AmbulatCardLocatType_id
						left join v_PersonAmbulatCardLink PACLink on PACLink.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
					where pc.PersonCard_id = PACLink.PersonCard_id
					order by PACL.PersonAmbulatCardLocat_begDate desc
					limit 1
				) as PACLT on true
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
				left join v_Lpu AttachLpu on AttachLpu.Lpu_id = PC.Lpu_id
				left join Address uaddr on uaddr.Address_id = ps.UAddress_id
				left join Address paddr on paddr.Address_id = ps.PAddress_id
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join PersonRefuse PRef on PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year = date_part('year', (select dt from mv)) + 1
				left join lateral(
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from PersonDisp
					where Person_id = ps.Person_id
					  and (PersonDisp_endDate is null or PersonDisp_endDate > (select dt from mv))
					  and Sickness_id in (1, 3, 4, 5, 6, 7, 8)
				) as disp on true
			";
			$this->getPersonCardFilters($data, $filter, $queryParams, $orderby, $pac_filter);
		} else if ($data["SearchFormType"] == "WorkPlacePolkaReg") {
			if ($data["PersonCardStateType_id"] == 1) {
				$query .= "
					left join lateral(
						select coalesce(PC_ALT.PersonCard_id, PC_SERV.PersonCard_id, PC_MAIN.PersonCard_id) as PersonCard_id
						from
							Person
							left join lateral(
								select PersonCard_id, Lpu_id
								from v_PersonCard
								where Person_id = PS.Person_id and LpuAttachType_id = 1 and PersonCard_endDate is null
								order by PersonCard_begDate
								limit 1
							) as PC_MAIN on true
							left join v_PersonCard PC_ALT on PC_ALT.Lpu_id = :Lpu_id and PC_ALT.Person_id = PS.Person_id and PC_ALT.LpuAttachType_id in (2, 3)
							left join v_PersonCard PC_SERV on PC_SERV.Person_id = PS.Person_id and PC_SERV.LpuAttachType_id = 4 and PC_SERV.Lpu_id = :Lpu_id and (coalesce(PC_MAIN.Lpu_id, PC_ALT.Lpu_id, 0) != PC_SERV.Lpu_id)
						where Person.Person_id = PS.Person_id
						limit 1
					) as PersonCard on true
					left join v_PersonCard PC on PC.PersonCard_id = PersonCard.PersonCard_id
				";
			} else {
				$query .= "
					left join lateral(
						select coalesce(PC_SERV.PersonCard_id, PC_MAIN.PersonCard_id, PC_ALT.PersonCard_id) as PersonCard_id
						from
							Person
							left join lateral(
								select PersonCard_id, Lpu_id
								from v_PersonCard
								where Person_id = PS.Person_id and LpuAttachType_id = 1 and PersonCard_endDate is null
								order by PersonCard_begDate
								limit 1
							) as PC_MAIN on true
							left join v_PersonCard_all PC_ALT on PC_MAIN.PersonCard_id is null and PC_ALT.Person_id = PS.Person_id and PC_ALT.LpuAttachType_id in (2,3)
							left join v_PersonCard_all PC_SERV on PC_SERV.Person_id = PS.Person_id and PC_SERV.LpuAttachType_id = 4 and PC_SERV.Lpu_id = :Lpu_id and (coalesce(PC_MAIN.Lpu_id, PC_ALT.Lpu_id, 0) != PC_SERV.Lpu_id)
						where Person.Person_id = PS.Person_id
						limit 1
					) as PersonCard on true
					left join v_PersonCard_all PC on PC.PersonCard_id = PersonCard.PersonCard_id
				";
			}
			$query .= "
				left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_Lpu AttachLpu on AttachLpu.Lpu_id = PC.Lpu_id
				left join v_PersonState Inn on Inn.Person_id = ps.Person_id
				left join Address uaddr on uaddr.Address_id = ps.UAddress_id
				left join Address paddr on paddr.Address_id = ps.PAddress_id
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
			    left join PersonRefuse PRef on PRef.Person_id = ps.Person_id and PRef.PersonRefuse_Year = date_part('year', (select dt from mv)) + 1
				left join lateral(
					select max(case when Lpu_id = :Lpu_id then 1 else 0 end) as OwnLpu
					from PersonDisp
					where Person_id = ps.Person_id
					  and (PersonDisp_endDate is null or PersonDisp_endDate > (select dt from mv))
					  and Sickness_id in (1, 3, 4, 5, 6, 7, 8)
				) as disp on true
			";
			$this->getPersonCardFilters($data, $filter, $queryParams, $orderby, $pac_filter);
		} else {
			if ($data["AttachLpu_id"] > 0 ||
				(strlen($data["PersonCard_Code"]) > 0 && !in_array($data["SearchFormType"], ["EvnPL", "EvnVizitPL"])) ||
				isset($data["PersonCard_begDate"]) ||
				isset($data["PersonCard_begDate_Range"][0]) ||
				isset($data["PersonCard_begDate_Range"][1]) ||
				isset($data["PersonCard_endDate"]) ||
				isset($data["PersonCard_endDate_Range"][0]) ||
				isset($data["PersonCard_endDate_Range"][1]) ||
				$data["LpuAttachType_id"] > 0 ||
				isset($data["LpuRegion_id"]) ||
				$data["LpuRegionType_id"] > 0 ||
				$data["MedPersonal_id"] > 0 ||
				isset($data["LpuRegion_Fapid"]) ||
				$data["PersonCard_IsDms"] > 0 ||
				$data["PersonCard_IsDms"] > 0 ||
				!empty($data["PersonCard_IsAttachCondit"])
			) {
				$needWithoutAttach = false;
				if (getRegionNick() == "ekb" && in_array($data["SearchFormType"], ["EvnPLDispDop13", "EvnPLDispDop13Sec"])) {
					// проверяем наличие объёма "Без прикрепления"
					$resp_vol = $this->queryResult("
						select av.AttributeValue_id as \"AttributeValue_id\"
						from
							v_AttributeVision avis
							inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
							inner join v_Attribute a on a.Attribute_id = av.Attribute_id
						where avis.AttributeVision_TableName = 'dbo.VolumeType'
						  and av.AttributeValue_ValueIdent = :Lpu_id
						  and avis.AttributeVision_TablePKey = (
								select VolumeType_id
								from v_VolumeType
								where VolumeType_Code = 'ДВН_Б_ПРИК'
								limit 1
						  )
						  and avis.AttributeVision_IsKeyValue = 2
						  and coalesce(av.AttributeValue_begDate, dbo.tzgetdate()) <= dbo.tzgetdate()
						  and coalesce(av.AttributeValue_endDate, dbo.tzgetdate()) >= dbo.tzgetdate()
						limit 1
					", ["Lpu_id" => $data["Lpu_id"]]);
					if (!empty($resp_vol[0]["AttributeValue_id"]) && $data["AttachLpu_id"] == 666666) {
						$needWithoutAttach = true;
					}
				}
				if (!in_array($data["SearchFormType"], ["BskRegistry", "IPRARegistry", "AdminVIPPerson", "ReabRegistry", "ReanimatRegistry", "ZNOSuspectRegistry", "SportRegistry", "HTMRegister"])) {
					$pc_fitler = $needWithoutAttach ? " not" : "";
					if (in_array($data["SearchFormType"], ["EvnPL", "EvnVizitPL"])) {
						if ($data['PersonCardStateType_id'] == 1) {
							$pc_fitler .= "
								exists (
									select PC.personcard_id
									from
										v_PersonCard PC
										left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
										left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
									where PC.Person_id = PS.Person_id
									  and (PC.PersonCard_endDate is null or PC.PersonCard_endDate::timestamp > (select dt from mv))
							";
						} else {
							$pc_fitler .= "
								exists (
									select PC.personcard_id
									from
										v_PersonCard_all PC
										left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
										left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
										left join v_PersonAmbulatCardLink PACL on PACL.PersonCard_id = PC.PersonCard_id
										left join v_PersonAmbulatCard PAC on PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
									where PC.Person_id = PS.Person_id
							";
						}
					} else {
						if ($needWithoutAttach === true) {
							$pc_fitler .= "
								exists (
									select personcard_id
									from v_PersonCard PC
							        where PC.Person_id = PS.Person_id
							";
						} elseif ($data["PersonCardStateType_id"] == 1) {
							if ($data["SearchFormType"] != "PersonPrivilege") {
								$pc_fitler .= "
								exists (
								    select personcard_id
                                    from
                                        PersonCard PC
                                        left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
                                        left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
                                        left join v_MedStaffRegion MedStaffRegion on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
										left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
										left join persis.Post p on p.id = msf.Post_id
                                    where
                                        PC.Person_id = PS.Person_id
                                    	and (PC.PersonCard_endDate is null
                                    		or PC.PersonCard_endDate::timestamp > (select dt from mv)
                                    	)
								";
								if (isset($data["SignalInfo"]) && $data["SignalInfo"] == 1) {
									$pc_fitler .= "
										and MedStaffRegion.MedStaffRegion_isMain = 2
										and p.code in (74, 47, 40, 117, 111)
									";
								}
							} else {
								// перенес для формы "Регистр льготников: Поиск" поиск personcard_id из $pc_fitler в $query, иначе запрос требовал на много порядков больше времени на исполнение,
								// чем приемлемо при работе в веб. Причина длительного исполнения в том, что таблица PCSD1 содержит много данных (на момент разработки из тестовой БД Перми возвращалось больше 150 тыс. записей)
								// и проверка существования personcard_id для каждой записи оператором exists в блоке where (соответствует переменной $filter) приводит к "зависанию" запроса
								$query .= "
								left join lateral (
									select personcard_id
									from
										PersonCard PC
										left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
										left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
										left join v_MedStaffRegion MedStaffRegion on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
										left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
										left join persis.Post p on p.id = msf.Post_id
									where
										PC.Person_id = PS.Person_id
										and (PC.PersonCard_endDate is null
										or PC.PersonCard_endDate::timestamp > (select dt from mv)
										)
									 ";
								$pc_fitler .= '(1=1)';
							}
						} else {
							$pc_fitler .= "
								exists (
									select personcard_id
									from
										v_PersonCard_all PC
										left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
										left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
							        where PC.Person_id = PS.Person_id
							 ";
						}
					}
					$this->getPersonCardFilters($data, $pc_fitler, $queryParams, $orderby, $pac_filter);
					if ($data["SearchFormType"] != "PersonPrivilege") {
						$filter .= " and {$pc_fitler} limit 1) ";
					} else {
						$query .= " and {$pc_fitler} limit 1) as PPPersonCard on true";
						$filter .= "and PPPersonCard.personcard_id is not null";
					}
				} else if (in_array($data["SearchFormType"], ["BskRegistry", "IPRARegistry", "AdminVIPPerson", "ReabRegistry", "ReanimatRegistry", "ZNOSuspectRegistry", "SportRegistry"])) {
					$this->getPersonCardFilters($data, $filter, $queryParams, $orderby, $pac_filter);
				}
			} else {
				$this->getPersonCardFilters($data, $filter, $queryParams, $orderby, $pac_filter);
			}
		}
		if ($data["pmUser_insID"] > 0) {
			if ($data["SearchFormType"] == "PersonCard" && $data["PersonCardStateType_id"] != 1) {
				$filter .= " and {$main_alias}.pmUserBeg_insID = :pmUser_insID";
			} elseif ($data["SearchFormType"] == "EvnPLDispDop13Sec") {
				$filter .= " and case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.pmUser_insID else {$main_alias}.pmUser_insID end = :pmUser_insID";
			} elseif ($data["SearchFormType"] == "EvnPL") {
				$filter .= " and Evn.pmUser_insID = :pmUser_insID";
			} elseif ($data["SearchFormType"] == "EvnNotifyRegister") {
				$filter .= " and E.pmUser_insID = :pmUser_insID";
			} else {
				$filter .= " and {$main_alias}.pmUser_insID = :pmUser_insID";
			}
			$queryParams["pmUser_insID"] = $data["pmUser_insID"];
		}
		if ($data["pmUser_updID"] > 0) {
			if ($data["SearchFormType"] == "PersonCard" && $data["PersonCardStateType_id"] != 1) {
				$filter .= " and {$main_alias}.pmUserEnd_insID = :pmUser_updID";
			} else if ($data["SearchFormType"] == "EvnPLDispDop13Sec") {
				$filter .= " and case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.pmUser_updID else {$main_alias}.pmUser_updID end = :pmUser_updID";
			} else if ($data["SearchFormType"] == "EvnPL") {
				$filter .= " and Evn.pmUser_updID = :pmUser_updID";
			} else if ($data["SearchFormType"] == "EvnNotifyRegister") {
				$filter .= " and E.pmUser_updID = :pmUser_updID";
			} else {
				$filter .= " and {$main_alias}.pmUser_updID = :pmUser_updID";
			}
			$queryParams["pmUser_updID"] = $data["pmUser_updID"];
		}
		if (substr($data["SearchFormType"], 0, 3) == "Kvs") {
			$fld_name = substr($data["SearchFormType"], 3);
			switch ($data["SearchFormType"]) {
				case "KvsPerson":
					$fld_name = "EvnPS";
					break;
				case "KvsEvnDiag":
					$fld_name = "EvnDiagPS";
					break;
				case "KvsNarrowBed":
					$fld_name = "EvnSectionNarrowBed";
					break;
			}
			$fld_name = ($data["SearchFormType"] != "KvsPerson") ? "{$main_alias}.{$fld_name}" : "EPS.{$fld_name}";
			$is_pc = ($data["SearchFormType"] == "KvsPersonCard" && $data["PersonCardStateType_id"] != 1);
			$is_pcIns = $is_pc ? "Beg_insDT" : "_insDT";
			$is_pcUpd = $is_pc ? "Beg_insDT" : "_updDT";
			if (isset($data["InsDate"])) {
				$filter .= ($data["SearchFormType"] == "KvsEvnLeave")
					? " and (
						(EPS.LeaveType_id = 1 and ELV.EvnLeave_insDT = :InsDate::timestamp) or
						(EPS.LeaveType_id = 2 and EOLpu.EvnOtherLpu_insDT = :InsDate::timestamp) or
						(EPS.LeaveType_id = 3 and EDie.EvnDie_insDT = :InsDate::timestamp) or
						(EPS.LeaveType_id = 4 and EOStac.EvnOtherStac_insDT = :InsDate::timestamp) or
						(EPS.LeaveType_id = 5 and EOSect.EvnOtherSection_insDT = :InsDate::timestamp) or
						(EOSectBP.EvnOtherSectionBedProfile_insDT = :InsDate::timestamp)
					)"
					: " and {$fld_name}{$is_pcIns} = :InsDate::timestamp";
				$queryParams["InsDate"] = $data["InsDate"];
			}
			if (isset($data["InsDate_Range"][0])) {
				$filter .= ($data["SearchFormType"] == "KvsEvnLeave")
					? " and (
						(EPS.LeaveType_id = 1 and ELV.EvnLeave_insDT >= :InsDate_Range_0::timestamp) or
						(EPS.LeaveType_id = 2 and EOLpu.EvnOtherLpu_insDT >= :InsDate_Range_0::timestamp) or
						(EPS.LeaveType_id = 3 and EDie.EvnDie_insDT >= :InsDate_Range_0::timestamp) or
						(EPS.LeaveType_id = 4 and EOStac.EvnOtherStac_insDT >= :InsDate_Range_0::timestamp) or
						(EPS.LeaveType_id = 5 and EOSect.EvnOtherSection_insDT >= :InsDate_Range_0::timestamp) or
						(EOSectBP.EvnOtherSectionBedProfile_insDT >= :InsDate_Range_0::timestamp)
					)"
					: " and {$fld_name}{$is_pcIns} >= :InsDate_Range_0::timestamp";
				$queryParams["InsDate_Range_0"] = $data["InsDate_Range"][0];
			}
			if (isset($data["InsDate_Range"][1])) {
				$filter .= ($data["SearchFormType"] == "KvsEvnLeave")
					? " and (
						(EPS.LeaveType_id = 1 and ELV.EvnLeave_insDT <= :InsDate_Range_1::timestamp) or
						(EPS.LeaveType_id = 2 and EOLpu.EvnOtherLpu_insDT <= :InsDate_Range_1::timestamp) or
						(EPS.LeaveType_id = 3 and EDie.EvnDie_insDT <= :InsDate_Range_1::timestamp) or
						(EPS.LeaveType_id = 4 and EOStac.EvnOtherStac_insDT <= :InsDate_Range_1::timestamp) or
						(EPS.LeaveType_id = 5 and EOSect.EvnOtherSection_insDT <= :InsDate_Range_1::timestamp) or
						(EOSectBP.EvnOtherSectionBedProfile_insDT <= :InsDate_Range_1::timestamp)
					)"
					: " and {$fld_name}{$is_pcIns} <= :InsDate_Range_1::timestamp";
				$queryParams["InsDate_Range_1"] = $data["InsDate_Range"][1];
			}
			if (isset($data["UpdDate"])) {
				$filter .= ($data["SearchFormType"] == "KvsEvnLeave")
					? " and (
						(EPS.LeaveType_id = 1 and ELV.EvnLeave_updDT = :UpdDate::timestamp) or
						(EPS.LeaveType_id = 2 and EOLpu.EvnOtherLpu_updDT = :UpdDate::timestamp) or
						(EPS.LeaveType_id = 3 and EDie.EvnDie_updDT = :UpdDate::timestamp) or
						(EPS.LeaveType_id = 4 and EOStac.EvnOtherStac_updDT = :UpdDate::timestamp) or
						(EPS.LeaveType_id = 5 and EOSect.EvnOtherSection_updDT = :UpdDate::timestamp) or
						(EOSectBP.EvnOtherSectionBedProfile_updDT = :UpdDate::timestamp)
					)"
					: " and {$fld_name}{$is_pcUpd} = :UpdDate::timestamp";
				$queryParams["UpdDate"] = $data["UpdDate"];
			}
			if (isset($data["UpdDate_Range"][0])) {
				$filter .= ($data["SearchFormType"] == "KvsEvnLeave")
					? " and (
						(EPS.LeaveType_id = 1 and ELV.EvnLeave_updDT >= :UpdDate_Range_0::timestamp) or
						(EPS.LeaveType_id = 2 and EOLpu.EvnOtherLpu_updDT >= :UpdDate_Range_0::timestamp) or
						(EPS.LeaveType_id = 3 and EDie.EvnDie_updDT >= :UpdDate_Range_0::timestamp) or
						(EPS.LeaveType_id = 4 and EOStac.EvnOtherStac_updDT >= :UpdDate_Range_0::timestamp) or
						(EPS.LeaveType_id = 5 and EOSect.EvnOtherSection_updDT >= :UpdDate_Range_0::timestamp) or
						(EOSectBP.EvnOtherSectionBedProfile_updDT >= :UpdDate_Range_0::timestamp)
					)"
					: " and {$fld_name}{$is_pcUpd} >= :UpdDate_Range_0::timestamp";
				$queryParams["UpdDate_Range_0"] = $data["UpdDate_Range"][0];
			}
			if (isset($data["UpdDate_Range"][1])) {
				$filter .= ($data["SearchFormType"] == "KvsEvnLeave")
					? " and (
						(EPS.LeaveType_id = 1 and ELV.EvnLeave_updDT <= :UpdDate_Range_1::timestamp) or
						(EPS.LeaveType_id = 2 and EOLpu.EvnOtherLpu_updDT <= :UpdDate_Range_1::timestamp) or
						(EPS.LeaveType_id = 3 and EDie.EvnDie_updDT <= :UpdDate_Range_1::timestamp) or
						(EPS.LeaveType_id = 4 and EOStac.EvnOtherStac_updDT <= :UpdDate_Range_1::timestamp) or
						(EPS.LeaveType_id = 5 and EOSect.EvnOtherSection_updDT <= :UpdDate_Range_1::timestamp) or
						(EOSectBP.EvnOtherSectionBedProfile_updDT <= :UpdDate_Range_1::timestamp)
					)"
					: " and {$fld_name}{$is_pcUpd} <= :UpdDate_Range_1::timestamp";
				$queryParams["UpdDate_Range_1"] = $data["UpdDate_Range"][1];
			}
		} else {
			$fld_name = $data["SearchFormType"];
			switch ($data["SearchFormType"]) {
				case "EvnPLDispTeenInspectionPeriod":
				case "EvnPLDispTeenInspectionPred":
				case "EvnPLDispTeenInspectionProf":
					$fld_name = "EvnPLDispTeenInspection";
					break;
				case "PersonDispOrpPeriod":
				case "PersonDispOrpPred":
				case "PersonDispOrpProf":
					$fld_name = "PersonDispOrp";
					break;
				case "EvnPLDispDop13Sec":
					$fld_name = "EvnPLDispDop13";
					break;
				case "EvnPLDispOrpSec":
					$fld_name = "EvnPLDispOrp";
					break;
				case "CmpCloseCard":
					$fld_name = "CmpCallCard";
					break;
				case "EvnNotifyHIV":
					$fld_name = "EvnNotifyBase";
					break;
				case "EvnNotifyNarko":
					$fld_name = "EvnNotifyNarco";
					break;
				case "CrazyRegistry":
				case "NarkoRegistry":
				case "NephroRegistry":
				case "OnkoRegistry":
				case "PalliatRegistry":
				case "TubRegistry":
				case "IPRARegistry":
				case "HepatitisRegistry":
					$fld_name = "PersonRegister";
					break;
				case 'AdminVIPPerson': // PROMEDWEB-12920, PROMEDWEB-12980: Форма AdminVIPPerson запускает поиск по таблице VIPPerson (PgSQL only), а в этой таблице все (поисковые) поля начинаются с vipperson, а не с AdminVIPPerson
					$fld_name = 'VIPPerson'; // NOTE(vmunt): $fld_name -- это префикс поля, а не имя поля. К этому префиксу будут добавлятся _insDT, _delDT, _setDate, и так далее.
					break;
			}
			if (isset($data["InsDate"])) {
				if ($data["SearchFormType"] == "PersonCard" && $data["PersonCardStateType_id"] != 1) {
					$filter .= " and {$main_alias}.{$data["SearchFormType"]}Beg_insDT::date = :InsDate::date";
				} else if ($data["SearchFormType"] == "EvnPLDispDop13Sec") {
					$filter .= " and (case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.{$fld_name}_insDT else {$main_alias}.{$fld_name}_insDT end)::date = :InsDate::date";
				} else if ($data["SearchFormType"] == "EvnPL") {
					$filter .= " and Evn.Evn_insDT::date = :InsDate::date";
				} else if ($data["SearchFormType"] == "EvnNotifyRegister") {
					$filter .= " and E.Evn_insDT::date = :InsDate::date";
				} else {
					$filter .= " and {$main_alias}.{$fld_name}_insDT::date = :InsDate::date";
				}
				$queryParams["InsDate"] = $data["InsDate"];
			}
			if (isset($data["InsDate_Range"][0])) {
				if ($data["SearchFormType"] == "PersonCard" && $data["PersonCardStateType_id"] != 1) {
					$filter .= " and {$main_alias}.{$data["SearchFormType"]}Beg_insDT::date >= :InsDate_Range_0::date";
				} else if ($data["SearchFormType"] == "EvnPLDispDop13Sec") {
					$filter .= " and (case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.{$fld_name}_insDT else {$main_alias}.{$fld_name}_insDT end)::date >= :InsDate_Range_0::date";
				} else if ($data["SearchFormType"] == "EvnPL") {
					$filter .= " and Evn.Evn_insDT::date >= :InsDate_Range_0::date";
				} else if ($data["SearchFormType"] == "EvnNotifyRegister") {
					$filter .= " and E.Evn_insDT::date >= :InsDate_Range_0::date";
				} else {
					$filter .= " and {$main_alias}.{$fld_name}_insDT::date >= :InsDate_Range_0::date";
				}
				$queryParams["InsDate_Range_0"] = $data["InsDate_Range"][0];
			}
			if (isset($data["InsDate_Range"][1])) {
				if ($data["SearchFormType"] == "PersonCard" && $data["PersonCardStateType_id"] != 1) {
					$filter .= " and {$main_alias}.{$data["SearchFormType"]}Beg_insDT::date <= :InsDate_Range_1::date";
				} else if ($data["SearchFormType"] == "EvnPLDispDop13Sec") {
					$filter .= " and (case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.{$fld_name}_insDT else {$main_alias}.{$fld_name}_insDT end)::date <= :InsDate_Range_1::date";
				} else if ($data["SearchFormType"] == "EvnPL") {
					$filter .= " and Evn.Evn_insDT::date <= :InsDate_Range_1::date";
				} else if ($data["SearchFormType"] == "EvnNotifyRegister") {
					$filter .= " and E.Evn_insDT::date <= :InsDate_Range_1::date";
				} else {
					$filter .= " and {$main_alias}.{$fld_name}_insDT::date <= :InsDate_Range_1::date";
				}
				$queryParams["InsDate_Range_1"] = $data["InsDate_Range"][1];
			}
			if (isset($data["UpdDate"])) {
				if ($data["SearchFormType"] == "PersonCard" && $data["PersonCardStateType_id"] != 1) {
					$filter .= " and {$main_alias}.{$data["SearchFormType"]}Beg_insDT::date = :UpdDate::date";
				} else if ($data["SearchFormType"] == "EvnPLDispDop13Sec") {
					$filter .= " and (case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.{$fld_name}_updDT else {$main_alias}.{$fld_name}_updDT end)::date = :UpdDate::date";
				} else if ($data["SearchFormType"] == "EvnPL") {
					$filter .= " and Evn.Evn_updDT::date = :UpdDate::date";
				} else if ($data["SearchFormType"] == "EvnNotifyRegister") {
					$filter .= " and E.Evn_updDT::date = :UpdDate::date";
				} else {
					$filter .= " and {$main_alias}.{$fld_name}_updDT::date = :UpdDate::date";
				}
				$queryParams["UpdDate"] = $data["UpdDate"];
			}
			if (isset($data["UpdDate_Range"][0])) {
				if ($data["SearchFormType"] == "PersonCard" && $data["PersonCardStateType_id"] != 1) {
					$filter .= " and {$main_alias}.{$data["SearchFormType"]}Beg_insDT::date >= :UpdDate_Range_0::date";
				} else if ($data["SearchFormType"] == "EvnPLDispDop13Sec") {
					$filter .= " and (case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.{$fld_name}_updDT else {$main_alias}.{$fld_name}_updDT end)::date >= :UpdDate_Range_0::date";
				} else if ($data["SearchFormType"] == "EvnPL") {
					$filter .= " and Evn.Evn_updDT::date >= :UpdDate_Range_0::date";
				} else if ($data["SearchFormType"] == "EvnNotifyRegister") {
					$filter .= " and E.Evn_updDT::date >= :UpdDate_Range_0::date";
				} else {
					$filter .= " and {$main_alias}.{$fld_name}_updDT::date >= :UpdDate_Range_0::date";
				}
				$queryParams["UpdDate_Range_0"] = $data["UpdDate_Range"][0];
			}
			if (isset($data["UpdDate_Range"][1])) {
				if ($data["SearchFormType"] == "PersonCard" && $data["PersonCardStateType_id"] != 1) {
					$filter .= " and {$main_alias}.{$data["SearchFormType"]}Beg_insDT::date <= :UpdDate_Range_1::date";
				} else if ($data["SearchFormType"] == "EvnPLDispDop13Sec") {
					$filter .= " and (case when DopDispSecond.EvnPLDispDop13_id is not null then DopDispSecond.{$fld_name}_updDT else {$main_alias}.{$fld_name}_updDT end)::date <= :UpdDate_Range_1::date";
				} else if ($data["SearchFormType"] == "EvnPL") {
					$filter .= " and Evn.Evn_updDT::date <= :UpdDate_Range_1::date";
				} else if ($data["SearchFormType"] == "EvnNotifyRegister") {
					$filter .= " and E.Evn_updDT::date <= :UpdDate_Range_1::date";
				} else {
					$filter .= " and {$main_alias}.{$fld_name}_updDT::date <= :UpdDate_Range_1::date";
				}
				$queryParams["UpdDate_Range_1"] = $data["UpdDate_Range"][1];
			}
		}
		if ($data["SearchFormType"] == "EvnPLDispDop13") {
			$joinEPLDD13 = "left";
			if (!empty($filterEPLDD13)) {
				$joinEPLDD13 = "inner";
			}
			$ddjoin = ($data["PersonPeriodicType_id"] == 2)
				?" {$joinEPLDD13} join v_EvnPLDispDop13 EvnPLDispDop13 on EvnPLDispDop13.Server_id = PS.Server_id and EvnPLDispDop13.PersonEvn_id = PS.PersonEvn_id and EvnPLDispDop13.Lpu_id " . $this->getLpuIdFilter($data) . " and coalesce(EvnPLDispDop13.DispClass_id,1) = 1 and date_part('year', EvnPLDispDop13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13} "
				:" {$joinEPLDD13} join v_EvnPLDispDop13 EvnPLDispDop13 on PS.Person_id = EvnPLDispDop13.Person_id and EvnPLDispDop13.Lpu_id " . $this->getLpuIdFilter($data) . " and coalesce(EvnPLDispDop13.DispClass_id,1) = 1 and date_part('year', EvnPLDispDop13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13} ";
			if (allowPersonEncrypHIV($data["session"])) {
				$PersonFields = "
					case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname,
					case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname,
					case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname,
					case when PEH.PersonEncrypHIV_id is null then PS.Person_Birthday else null end as Person_Birthday
				";
				$ddjoin .= " left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id ";
			} else {
				$PersonFields = "
					RTRIM(PS.Person_Surname) as Person_Surname,
					RTRIM(PS.Person_Firname) as Person_Firname,
					RTRIM(PS.Person_Secname) as Person_Secname,
					PS.Person_Birthday
				";
			}
			$wherewith = [];
			$wherewith[] = $filter;
			$queryWithAdditionalJoin = [];
			$queryWithAdditionalJoin[] = "
				{$joinDopDispSecond}
				join lateral(
					select
						EPLDD13_SEC.EvnPLDispDop13_id,
						EPLDD13_SEC.EvnPLDispDop13_isPaid,
						EPLDD13_SEC.EvnPLDispDop13_IsTransit,
						EPLDD13_SEC.EvnPLDispDop13_setDate,
						EPLDD13_SEC.EvnPLDispDop13_disDate,
						EPLDD13_SEC.EvnPLDispDop13_consDT,
						EPLDD13_SEC.HealthKind_id,
						coalesce(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
						HK_SEC.HealthKind_Name,
						EPLDD13_SEC.EvnPLDispDop13_insDT,
						EPLDD13_SEC.EvnPLDispDop13_updDT,
						EPLDD13_SEC.pmUser_insID,
						EPLDD13_SEC.pmUser_updID
					from
						v_EvnPLDispDop13 EPLDD13_SEC
						left join v_HealthKind HK_SEC on HK_SEC.HealthKind_id = EPLDD13_SEC.HealthKind_id
					where
						EPLDD13_SEC.EvnPLDispDop13_fid = EvnPLDispDop13.EvnPLDispDop13_id
						{$filterDopDispSecond}
					limit 1
				) as DopDispSecond on true
			";
			$queryWithAdditionalJoinString = implode(" ", $queryWithAdditionalJoin);
			$wherewithString = implode(" and ", $wherewith);
			$queryWithArray[] = "
				EvnPLDispDop13Top as (
					select
							EvnPLDispDop13.EvnPLDispDop13_id,
							EvnPLDispDop13.EvnPLDispDop13_IsEndStage,
							EvnPLDispDop13.EvnPLDispDop13_isMobile,
							EvnPLDispDop13.EvnPLDispDop13_IsTransit,
							EvnPLDispDop13.HealthKind_id,
							EvnPLDispDop13.EvnPLDispDop13_IsArchive,
							PS.Person_id,
							PS.Server_id,
							EvnPLDispDop13.PersonEvn_id,
							EvnPLDispDop13.Lpu_id,
							EvnPLDispDop13.EvnPLDispDop13_consDT,
							EvnPLDispDop13.EvnPLDispDop13_disDate,
							EvnPLDispDop13.EvnPLDispDop13_IsRefusal,
							EvnPLDispDop13.EvnPLDispDop13_IsTwoStage,
							DopDispSecond.EvnPLDispDop13_id as DopDispSecond_EvnPLDispDop13_id,
							DopDispSecond.HealthKind_id as DopDispSecond_HealthKind_id,
							DopDispSecond.EvnPLDispDop13_IsEndStage as DopDispSecond_EvnPLDispDop13_IsEndStage,
							DopDispSecond.EvnPLDispDop13_consDT as DopDispSecond_EvnPLDispDop13_consDT,
							DopDispSecond.EvnPLDispDop13_disDate as DopDispSecond_EvnPLDispDop13_disDate,
							PS.UAddress_id,
							PS.PAddress_id,
							{$PersonFields}
					from v_PersonState PS
						{$ddjoin}
						{$queryWithAdditionalJoinString}
					where
						{$wherewithString}
				)
			";
			$query .= "
					-- end from
				where
					-- where
						(1=1)
					-- end where
			";
		} else {
			if (!empty($data["Part_of_the_study"]) && $data["Part_of_the_study"] && !empty($data["UslugaComplex_id"])) {
				$query .= "
						-- end from
					where
						-- where
						{$filter}{$evnUC_filter}{$pac_filter}{$uc_filter}
						-- end where
				";
			} else {
				$set_evnUC_filter = $data["SearchFormType"] == "EvnUslugaPar" ? $evnUC_filter : "";
				$query .= "
						-- end from
					where
						-- where
						{$filter}{$set_evnUC_filter}{$pac_filter}
						-- end where
				";
			}
		}
		$query .= Search_model_get::functionRefactorOrder($data, $orderby, $dbf, $print);
		$response = [];
		$with = "";
		if (count($queryWithArray) > 0) {
			$queryWithArrayString = implode(", ", $queryWithArray);
			$with = "
				-- addit with
			    {$queryWithArrayString}
				-- end addit with
			";
		}
		$variablesArrayString = implode(",", $variablesArray);
		$variables = "
			--variables
			with mv as (
				select
				{$variablesArrayString}
			)". (empty($with) ? '' : ',')."
			--end variables
		";
		if (strtolower($data["onlySQL"]) == "on" && isSuperAdmin()) {
			$query = $variables . $with . $query;
			echo getDebugSQL($query, $queryParams);
			return false;
		}
		/**
		 * @var CI_DB_result $get_count_result
		 * @var CI_DB_result $result
		 */
		if ($getCount == true) {
			$get_count_query = getCountSQLPH($query);
			// приходится так делать из-за группировки
			if (in_array($data["SearchFormType"], ["EvnInfectNotify", "PersonDopDisp"])) {
				$get_count_query = getCountSQLPH($query, "*", "", "", true);
			}
			// Цепляем переменные
			$get_count_query = $variables . $with . $get_count_query;
			$get_count_result = $this->db->query($get_count_query, $queryParams);
			$res = $get_count_result->result("array");
			$cnt = (count($res) == 1) ? $res[0]["cnt"] : count($res);
			if (!is_object($get_count_result)) {
				return false;
			}
			$response["totalCount"] = $cnt;
			$response["Error_Msg"] = "";
		} else {
            // Цепляем переменные
			$query = $variables . $with. $query;
			if ($print === true) {
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$response["data"] = $result->result("array");
			} elseif ($dbf === true) {
				if (
					$data["SearchFormType"] == "EvnDiag" ||
					$data["SearchFormType"] == "EPLPerson" ||
					$data["SearchFormType"] == "EvnPL" ||
					$data["SearchFormType"] == "EvnVizitPL" ||
					$data["SearchFormType"] == "EvnUsluga" ||
					$data["SearchFormType"] == "EvnAgg" ||
					$data["SearchFormType"] == "EPLStomPerson" ||
					$data["SearchFormType"] == "EvnPLStom" ||
					$data["SearchFormType"] == "EvnVizitPLStom" ||
					$data["SearchFormType"] == "EvnUslugaStom" ||
					$data["SearchFormType"] == "EvnAggStom" ||
					substr($data["SearchFormType"], 0, 3) == "Kvs"
				) {
					$fld_name = Search_model_selectFunc::searchData_SearchFormTypeDbf($PS_prefix, $PL_prefix, $PLS_prefix, $data);
					$get_count_query = getCountSQLPH($query, "", "distinct " . $fld_name);
				} else {
					$get_count_query = getCountSQLPH($query);
					// приходится так делать из-за группировки
					if (in_array($data["SearchFormType"], ["EvnInfectNotify", "PersonDopDisp"])) {
						$get_count_query = getCountSQLPH($query, "*", "", "", true);
					}
				}
				$get_count_result = $this->db->query($get_count_query, $queryParams);
				if (!is_object($get_count_result)) {
					return false;
				}
				$records_count = $get_count_result->result("array");
				$cnt = $records_count[0]["cnt"];
				$result = $this->db->query($query, $queryParams);
				if (!is_object($result)) {
					return false;
				}
				$response = ["data" => $result, "totalCount" => $cnt];
			} else {
				if ($data["start"] >= 0 && $data["limit"] >= 0) {
					$limit_query = getLimitSQLPH($query, $data["start"], $data["limit"]);
					if (!empty($data["Part_of_the_study"]) && $data["Part_of_the_study"] && !empty($data["UslugaComplex_id"])) {
						$start = stripos($limit_query, "UNION ALL");
						$end = strrpos($limit_query, "UNION ALL");
						$str_result = substr_replace($limit_query, "", $start, $end - $start);
						$limit_query = $str_result;
					}
					
					if ($this->isDebug) {
						$debug_sql = getDebugSql($limit_query, $queryParams);
					}
					$result = $this->db->query($limit_query, $queryParams);
				} else {
					$result = $this->db->query($query, $queryParams);
				}
				if (!is_object($result)) {
					return false;
				}
				$res = $result->result("array");
				if (!is_array($res)) {
					return false;
				}
				$response['data'] = $res;
				$response['totalCount'] = $data['start'] + count($res);
				if (count($res) >= $data["limit"]) {
					if (!empty($_REQUEST["useArchive"]) && $_REQUEST["useArchive"] == 1) {
						$get_count_query = getCountSQLPH($query);
						if (in_array($data["SearchFormType"], ["EvnInfectNotify", "PersonDopDisp"])) {
							$get_count_query = getCountSQLPH($query, "*", "", "", true);
						}
						$get_count_result = $this->db->query($get_count_query, $queryParams);
						if (is_object($get_count_result)) {
							$response["totalCount"] = $get_count_result->result("array");
							$response["totalCount"] = $response["totalCount"][0]["cnt"];
						}
					} else {
						$response["overLimit"] = true;
					}
				}
			}
		}
		if (!empty($_REQUEST["useArchive"]) && $_REQUEST["useArchive"] == 1) {
			if (!empty($response["totalCount"])) {
				$response["totalCount"] = $response["totalCount"] + $data["archiveStart"];
			}
		}
		sql_log_message("error", "Search_model exec query: ", getDebugSql($query, $queryParams));
		return $response;
	}
}
