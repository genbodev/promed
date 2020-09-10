<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * SearchEvnPLDispDop13_model - модель для форм поиска
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @version			10.04.2018
 */
require_once('SearchBase_model.php');
class SearchEvnPLDispDop13_model extends SearchBase_model {
	protected $main_alias = 'EvnPLDispDop13';
	protected $main_table = 'EvnPLDispDop13';
	protected $archiveAlias = 'EPLDD13';

	/**
	 * 	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает данные для select'а в запросе
	 */
	function getSelect($data)
	{
		$this->select = "
			cast(COALESCE(EPLDD13.EvnPLDispDop13_id, 0) as varchar) || '_' || cast(COALESCE(EPLDD13.Person_id, 0) as varchar) as \"id\",
			EPLDD13.EvnPLDispDop13_id as \"EvnPLDispDop13_id\",
			COALESCE(EPLDD13.EvnPLDispDop13_IsTransit, 1) as \"EvnPLDispDop13_IsTransit\",
			EPLDD13.Person_id as \"Person_id\",
			EPLDD13.Server_id as \"Server_id\",
			EPLDD13.PersonEvn_id as \"PersonEvn_id\",
			RTRIM(EPLDD13.Person_Surname) as \"Person_Surname\",
			RTRIM(EPLDD13.Person_Firname) as \"Person_Firname\",
			RTRIM(EPLDD13.Person_Secname) as \"Person_Secname\",
			to_char(EPLDD13.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
			IsFinish.YesNo_Name as \"EvnPLDispDop13_IsEndStage\",
			IsMobile.YesNo_Name as \"EvnPLDispDop13_IsMobile\",
			IsFinishSecond.YesNo_Name as \"EvnPLDispDop13Second_IsEndStage\",
			to_char(EPLDD13.EvnPLDispDop13_consDT, 'DD.MM.YYYY') as \"EvnPLDispDop13_setDate\",
			to_char(EPLDD13.EvnPLDispDop13_disDate, 'DD.MM.YYYY') as \"EvnPLDispDop13_disDate\",
			HK.HealthKind_Name as \"EvnPLDispDop13_HealthKind_Name\",
			case when EPLDD13.EvnPLDispDop13_IsEndStage = 2 and EPLDD13.EvnPLDispDop13_IsTwoStage = 2 then to_char(EPLDD13.EvnPLDispDop13_disDate, 'DD.MM.YYYY') else null end as \"EvnPLDispDop13Second_napDate\",
			DopDispSecond.HealthKind_Name as \"EvnPLDispDop13Second_HealthKind_Name\",
			case when EPLDD13.EvnPLDispDop13_IsRefusal = 2 then to_char(EPLDD13.EvnPLDispDop13_consDT, 'DD.MM.YYYY') else null end as \"EvnPLDispDop13_rejDate\",
			case
				when EPLDD13AL.EvnPLDispDop13_id is not null then 4
				when EPLDD13.EvnPLDispDop13_id is null then 0
				when EPLDD13.Lpu_id = :Lpu_id then 0
				when EPLDD13.Lpu_id " . getLpuIdFilter($data) . " and COALESCE(EPLDD13.EvnPLDispDop13_IsTransit, 1) = 2 then 0
				else 4
			end as \"AccessType_Code\",
			DopDispSecond.EvnPLDispDop13_id as \"EvnPLDispDop13Second_id\",
			to_char(DopDispSecond.EvnPLDispDop13_consDT, 'DD.MM.YYYY') as \"EvnPLDispDop13Second_setDate\",
			to_char(DopDispSecond.EvnPLDispDop13_disDate, 'DD.MM.YYYY') as \"EvnPLDispDop13Second_disDate\",
			case when DDICDataSecond.DopDispInfoConsent_IsAgree = 1 then to_char(DopDispSecond.EvnPLDispDop13_consDT, 'DD.MM.YYYY') else null end as \"EvnPLDispDop13Second_rejDate\",
			to_char(ecp.EvnCostPrint_setDT, 'DD.MM.YYYY') as \"EvnCostPrint_setDT\",
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
		";
		if (in_array(getRegionNick(), array('ufa'))) {
			$this->select .= "
				,lpu.Lpu_Nick as \"Lpu_Nick\"
			";
		}
		if (in_array(getRegionNick(), array('buryatiya', 'krym'))) {
			$this->select .= "
				,COALESCE(UC.UslugaComplex_Code || '. ','') || UC.UslugaComplex_Name as \"UslugaComplex_Name\"
			";
		}

		$this->select .= "
			,COALESCE(UAdd.Address_Nick, UAdd.Address_Address) as \"ua_name\"
			,COALESCE(PAdd.Address_Nick, PAdd.Address_Address) as \"pa_name\"
		";
	}

	/**
	 * Возвращает данные для фильтрации
	 */
	function getFromAndWhere($data) {
		$this->from = 'EvnPLDispDop13Top EPLDD13';

		$this->withFilters = $this->where;
		$this->where = "(1=1)";

		$this->filterEPLDD13 = "";
		$this->filterDopDispSecond = "";

		// https://redmine.swan.perm.ru/issues/37296
		$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

		if (isset($data['EvnPLDispDop13_setDate'])) {
			$this->filterEPLDD13 .= " and EvnPLDispDop13.EvnPLDispDop13_setDate = cast(:EvnPLDispDop13_setDate as timestamp) ";
			$this->queryParams['EvnPLDispDop13_setDate'] = $data['EvnPLDispDop13_setDate'];
		}
		if (isset($data['EvnPLDispDop13_setDate_Range'][0])) {
			$this->filterEPLDD13 .= " and EvnPLDispDop13.EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13_setDate_Range_0 as timestamp) ";
			$this->queryParams['EvnPLDispDop13_setDate_Range_0'] = $data['EvnPLDispDop13_setDate_Range'][0];
		}
		if (isset($data['EvnPLDispDop13_setDate_Range'][1])) {
			$this->filterEPLDD13 .= " and EvnPLDispDop13.EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13_setDate_Range_1 as timestamp) ";
			$this->queryParams['EvnPLDispDop13_setDate_Range_1'] = $data['EvnPLDispDop13_setDate_Range'][1];
		}
		if (isset($data['EvnPLDispDop13_disDate'])) {
			$this->filterEPLDD13 .= " and EvnPLDispDop13.EvnPLDispDop13_disDate = cast(:EvnPLDispDop13_disDate as timestamp) ";
			$this->queryParams['EvnPLDispDop13_disDate'] = $data['EvnPLDispDop13_disDate'];
		}
		if (isset($data['EvnPLDispDop13_disDate_Range'][0])) {
			$this->filterEPLDD13 .= " and EvnPLDispDop13.EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13_disDate_Range_0 as timestamp) ";
			$this->queryParams['EvnPLDispDop13_disDate_Range_0'] = $data['EvnPLDispDop13_disDate_Range'][0];
		}
		if (isset($data['EvnPLDispDop13_disDate_Range'][1])) {
			$this->filterEPLDD13 .= " and EvnPLDispDop13.EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13_disDate_Range_1 as timestamp) ";
			$this->queryParams['EvnPLDispDop13_disDate_Range_1'] = $data['EvnPLDispDop13_disDate_Range'][1];
		}
		if (isset($data['EvnPLDispDop13_IsFinish'])) {
			$this->filterEPLDD13 .= " and COALESCE(EvnPLDispDop13.EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13_IsFinish ";
			$this->queryParams['EvnPLDispDop13_IsFinish'] = $data['EvnPLDispDop13_IsFinish'];
		}
		if (isset($data['EvnPLDispDop13_IsRefusal'])) {
			$this->filterEPLDD13 .= " and EvnPLDispDop13.EvnPLDispDop13_IsRefusal = :EvnPLDispDop13_IsRefusal ";
			$this->queryParams['EvnPLDispDop13_IsRefusal'] = $data['EvnPLDispDop13_IsRefusal'];
		}
		if (isset($data['EvnPLDispDop13_IsTwoStage'])) {
			$this->filterEPLDD13 .= " and COALESCE(EvnPLDispDop13.EvnPLDispDop13_IsTwoStage, 1) = :EvnPLDispDop13_IsTwoStage ";
			$this->queryParams['EvnPLDispDop13_IsTwoStage'] = $data['EvnPLDispDop13_IsTwoStage'];
		}

		if (isset($data['EvnPLDispDop13_HealthKind_id'])) {
			$this->filterEPLDD13 .= " and EvnPLDispDop13.HealthKind_id = :EvnPLDispDop13_HealthKind_id ";
			$this->queryParams['EvnPLDispDop13_HealthKind_id'] = $data['EvnPLDispDop13_HealthKind_id'];
		}

		if (isset($data['EvnPLDispDop13_isPaid'])) {
			if ($this->getRegionNick() == 'ufa') {
				// https://redmine.swan.perm.ru/issues/56232
				if ( $data['EvnPLDispDop13_isPaid'] == 2 ) {
					$this->withFilters .= " and exists (select EvnVizitDispDop_id from v_EvnVizitDispDop t1 where t1.EvnVizitDispDop_pid = EvnPLDispDop13.EvnPLDispDop13_id and COALESCE(t1.EvnVizitDispDop_IsPaid, 1) = 2 limit 1)";
				}
				else {
					$this->withFilters .= " and not exists (select EvnVizitDispDop_id from v_EvnVizitDispDop t1 where t1.EvnVizitDispDop_pid = EvnPLDispDop13.EvnPLDispDop13_id and COALESCE(t1.EvnVizitDispDop_IsPaid, 1) = 2 limit 1)";
				}
			}
			else {
				$this->filterEPLDD13 .= " and COALESCE(EvnPLDispDop13.EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13_isPaid ";
				$this->queryParams['EvnPLDispDop13_isPaid'] = $data['EvnPLDispDop13_isPaid'];
			}
		}

		if (isset($data['EvnPLDispDop13Second_isPaid'])) {
			if ($this->getRegionNick() == 'ufa') {
				if ( $data['EvnPLDispDop13Second_isPaid'] == 2 ) {
					$this->withFilters .= " and exists (select EvnVizitDispDop_id from v_EvnVizitDispDop t1 where t1.EvnVizitDispDop_pid = EPLDD13_SEC.EvnPLDispDop13_id and COALESCE(t1.EvnVizitDispDop_IsPaid, 1) = 2 limit 1)";
				}
				else {
					$this->withFilters .= " and not exists (select EvnVizitDispDop_id from v_EvnVizitDispDop t1 where t1.EvnVizitDispDop_pid = EPLDD13_SEC.EvnPLDispDop13_id and COALESCE(t1.EvnVizitDispDop_IsPaid, 1) = 2 limit 1)";
				}
			} else {
				$this->filterDopDispSecond .= " and COALESCE(EPLDD13_SEC.EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13Second_isPaid ";
				$this->queryParams['EvnPLDispDop13Second_isPaid'] = $data['EvnPLDispDop13Second_isPaid'];
			}
		}

		if (isset($data['EvnPLDispDop13_isMobile'])) {
			$this->filterEPLDD13 .= " and COALESCE(EvnPLDispDop13.EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13_isMobile ";
			$this->queryParams['EvnPLDispDop13_isMobile'] = $data['EvnPLDispDop13_isMobile'];
		}

		if (isset($data['EvnPLDispDop13Second_isMobile'])) {
			$this->filterDopDispSecond .= " and COALESCE(EPLDD13_SEC.EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13Second_isMobile ";
			$this->queryParams['EvnPLDispDop13Second_isMobile'] = $data['EvnPLDispDop13Second_isMobile'];
		}

		if (isset($data['EvnPLDispDop13Second_setDate'])) {
			$this->filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_setDate = cast(:EvnPLDispDop13Second_setDate as timestamp) ";
			$this->queryParams['EvnPLDispDop13Second_setDate'] = $data['EvnPLDispDop13Second_setDate'];
		}
		if (isset($data['EvnPLDispDop13Second_setDate_Range'][0])) {
			$this->filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13Second_setDate_Range_0 as timestamp) ";
			$this->queryParams['EvnPLDispDop13Second_setDate_Range_0'] = $data['EvnPLDispDop13Second_setDate_Range'][0];
		}
		if (isset($data['EvnPLDispDop13Second_setDate_Range'][1])) {
			$this->filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13Second_setDate_Range_1 as timestamp) ";
			$this->queryParams['EvnPLDispDop13Second_setDate_Range_1'] = $data['EvnPLDispDop13Second_setDate_Range'][1];
		}
		if (isset($data['EvnPLDispDop13Second_disDate'])) {
			$this->filterDopDispSecond .= " and DopDispSecond.EvnPLDispDop13_disDate = cast(:EvnPLDispDop13Second_disDate as timestamp) ";
			$this->queryParams['EvnPLDispDop13Second_disDate'] = $data['EvnPLDispDop13Second_disDate'];
		}
		if (isset($data['EvnPLDispDop13Second_disDate_Range'][0])) {
			$this->filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13Second_disDate_Range_0 as timestamp) ";
			$this->queryParams['EvnPLDispDop13Second_disDate_Range_0'] = $data['EvnPLDispDop13Second_disDate_Range'][0];
		}
		if (isset($data['EvnPLDispDop13Second_disDate_Range'][1])) {
			$this->filterDopDispSecond .= " and EPLDD13_SEC.EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13Second_disDate_Range_1 as timestamp) ";
			$this->queryParams['EvnPLDispDop13Second_disDate_Range_1'] = $data['EvnPLDispDop13Second_disDate_Range'][1];
		}

		if (isset($data['EvnPLDispDop13Second_IsFinish'])) {
			$this->filterDopDispSecond .= " and COALESCE(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13Second_IsFinish ";
			$this->queryParams['EvnPLDispDop13Second_IsFinish'] = $data['EvnPLDispDop13Second_IsFinish'];
		}

		if (isset($data['EvnPLDispDop13Second_HealthKind_id'])) {
			$this->filterDopDispSecond .= " and EPLDD13_SEC.HealthKind_id = :EvnPLDispDop13Second_HealthKind_id ";
			$this->queryParams['EvnPLDispDop13Second_HealthKind_id'] = $data['EvnPLDispDop13Second_HealthKind_id'];
		}

		if (isset($data['PersonDopDisp_Year'])) {
			// $this->withFilters .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
			$this->queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
		} else {
			$this->queryParams['PersonDopDisp_Year'] = 2013;
		}

		// Выборка ДВН по годам.
		// Формула для каждого года разная.
		// Год указывается в поле "Год" ($data['PersonDopDisp_Year']).

		// не отображать пациентов, которые умерли ранее выбранного года
		$this->withFilters .= " and (PS.Person_deadDT >= cast(cast(:PersonDopDisp_Year as varchar) || '-01-01' as timestamp) OR PS.Person_deadDT IS NULL)";

		$this->queryParams['Lpu_id'] = $data['Lpu_id'];

		$dateX = $this->EvnPLDispDop13_model->getNewDVNDate();
		$maxage = 999;
		$personPrivilegeCodeList = $this->EvnPLDispDop13_model->getPersonPrivilegeCodeList($this->queryParams['PersonDopDisp_Year'] . '-01-01');
		$this->queryParams['PersonDopDisp_YearEndDate'] = $this->queryParams['PersonDopDisp_Year'] . '-12-31';

		if (!empty($dateX) && $dateX <= date('Y-m-d')) {
			/**
			 * ДВН с 2019 года.
			 * @see EvnPLDispDop13_model::getNewDVNDate()
			 */
			$add_filter = "
				dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) >= 40
				or (
					dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) >= 18
					and (dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) - 18) % 3 = 0
				)
			";
		}
		else {
			// от 21 года
			$add_filter = "
				(dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) - 21) % 3 = 0)
			";

			// @task https://redmine.swan.perm.ru/issues/124302
			if ($this->getRegionNick() != 'kz' && $data['PersonDopDisp_Year'] >= 2018) {
				// ДВН с 2018 года.
				$add_filter .= "
					or
					(PS.Sex_id = 1 and dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) between 49 and 73 and dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) % 2 = 1)
					or
					(PS.Sex_id = 2 and dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) between 48 and 73)
				";
			}
		}

		// ДВН с 2020 года.
		if ($data['PersonDopDisp_Year'] >= 2020) {
			// Кроме регионов Казахстан, Карелия, Хакасия, Бурятия, Уфа.
			if (!in_array($this->getRegionNick(), ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'])) {
				$add_filter = '
					(
						-- Проверка по возрасту.
						(
							-- ДВН проводится раз в 3 года.
							-- От 18 до 39 (младше 40 лет).
							(dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) >= 18)
							AND (dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) <= 39)
							-- Возраст кратен 3-м.
							AND (dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) % 3 = 0)
							-- нет карты диспансеризации в указанному году и за два предыдущих года.
							AND not exists (SELECT EvnPLDispProf_id FROM v_EvnPLDispProf WHERE (date_part(\'year\', EvnPLDispProf_disDT) BETWEEN :PersonDopDisp_Year - 2 AND :PersonDopDisp_Year) AND Person_id = PS.Person_id LIMIT 1)
							-- нет карты профосмотра в указанному году.
							AND not exists (SELECT EvnPLDispProf_id FROM v_EvnPLDispProf WHERE date_part(\'year\', EvnPLDispProf_consDT) = :PersonDopDisp_Year AND Person_id = PS.Person_id LIMIT 1)
						)
						OR (
							-- ДВН проводится раз в 3 года (со сдвигом).
							-- От 18 до 39 (младше 40 лет).
							(dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) >= 18)
							AND (dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) <= 39)
							-- Возраст не кратен 3-м.
							AND (dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) % 3 <> 0)
							-- нет карты диспансеризации в указанному году и за два предыдущих года.
							AND not exists (SELECT EvnPLDispProf_id FROM v_EvnPLDispProf WHERE (date_part(\'year\', EvnPLDispProf_disDT) BETWEEN :PersonDopDisp_Year - 2 AND :PersonDopDisp_Year) AND Person_id = PS.Person_id LIMIT 1)
							-- нет карты профосмотра в указанному году.
							AND not exists (SELECT EvnPLDispProf_id FROM v_EvnPLDispProf WHERE date_part(\'year\', EvnPLDispProf_consDT) = :PersonDopDisp_Year AND Person_id = PS.Person_id LIMIT 1)
						)
						OR (
							-- ДВН проводится ежегодно.
							-- Старше 40 лет включительно.
							dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) >= 40
						)
					)
				';
			}
		}

		if (in_array($this->getRegionNick(), ['ufa', 'ekb', 'kareliya', 'penza', 'astra'])) {
			$add_filter .= " or (exists (select PersonPrivilegeWOW_id from v_PersonPrivilegeWOW where Person_id = PS.Person_id limit 1) and dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) >= 18)";
		}

		if ( count($personPrivilegeCodeList) > 0 ) {
			$add_filter .= "
				or (
					(dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) BETWEEN 18 AND {$maxage})
					and exists (
						select pp.PersonPrivilege_id
						from v_PersonPrivilege pp
							inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
						where pt.PrivilegeType_Code in ('" . implode("','", $personPrivilegeCodeList) . "')
							and pp.Person_id = PS.Person_id
							and pp.PersonPrivilege_begDate <= :PersonDopDisp_YearEndDate
							and (pp.PersonPrivilege_endDate > :PersonDopDisp_YearEndDate or pp.PersonPrivilege_endDate is null)
						limit 1
					)
				) -- refs #23044
			";
		}

		// https://redmine.swan.perm.ru/issues/19835
		// Если пациент, состоит в регистре ВОВ, то на данной форме его отображать независимо от возраста http://redmine.swan.perm.ru/issues/22014
		$DDfilter = "
			(
				{$add_filter}
			)
			and dbo.Age2(PS.Person_BirthDay, :PersonDopDisp_YearEndDate) <= {$maxage}
		";

		if ($this->getRegionNick() == 'perm') {
			// нет карты профосмотра в указанному году
			$DDfilter .= "
				and not exists (select EvnPLDispProf_id from v_EvnPLDispProf where date_part('year', EvnPLDispProf_consDT) = :PersonDopDisp_Year and Person_id = PS.Person_id limit 1)
			";
		}
		if (!empty($data['Disp_MedStaffFact_id']) || !empty($data['Disp_LpuSection_id']) || !empty($data['Disp_LpuBuilding_id'])) {
			$disp_msf = "";
			$disp_msf2 = "";
			$disp_ls = "";
			$disp_ls2 = "";
			$disp_b = "";
			$disp_b2 = "";
			$join1 = "";
			$join2 = "";
			if(!empty($data['Disp_MedStaffFact_id'])){
				$disp_msf = " and msf1.MedStaffFact_id = :MedStaffFact_id";
				$disp_msf2 = " and msf2.MedStaffFact_id = :MedStaffFact_id";
				$this->queryParams['MedStaffFact_id'] = $data['Disp_MedStaffFact_id'];
			} else {
				if (!empty($data['Disp_LpuSection_id'])) {
					$disp_ls = " and msf1.LpuSection_id = :LpuSection_id";
					$disp_ls2 = " and msf2.LpuSection_uid = :LpuSection_id";
					$this->queryParams['LpuSection_id'] = $data['Disp_LpuSection_id'];
				}
				if (!empty($data['Disp_LpuBuilding_id'])) {
					$join1 = "
						left join v_LpuSection ls1 on ls1.LpuSection_id = msf1.LpuSection_id
					";
					$disp_b = " and ls1.LpuBuilding_id = :LpuBuilding_id";
					$join2 = "
						left join v_LpuSection ls3 on ls3.LpuSection_id = msf2.LpuSection_uid
					";
					$disp_b2 = " and ls3.LpuBuilding_id = :LpuBuilding_id";
					$this->queryParams['LpuBuilding_id'] = $data['Disp_LpuBuilding_id'];
				}
			}
			$this->withFilters .= " 
				and (exists (
					select msf1.EvnVizitDispDop_id 
					from v_EvnVizitDispDop msf1 
					{$join1}
					where msf1.EvnVizitDispDop_pid = EvnPLDispDop13.EvnPLDispDop13_id 
					{$disp_b}
					{$disp_msf}
					{$disp_ls}
					limit 1
				) or exists (
					select msf2.EvnUslugaDispDop_id 
					from v_EvnUslugaDispDop msf2
					{$join2} 
					where msf2.EvnUslugaDispDop_pid = EvnPLDispDop13.EvnPLDispDop13_id 
					{$disp_b2}
					{$disp_msf2}
					{$disp_ls2}
					limit 1	
				))
			";
		}

		// ДВН с 2020 года.
		// Кроме регионов Казахстан, Карелия, Хакасия, Бурятия, Уфа.
		// Удаляем "EvnPLDispDop13.EvnPLDispDop13_id is not null" для правильной проверки даты рождения.
		if ($data['PersonDopDisp_Year'] >= 2020 && !in_array($this->getRegionNick(), ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'])) {
			$this->withFilters .= " and ({$DDfilter})";
		} else {
			$this->withFilters .= " and (EvnPLDispDop13.EvnPLDispDop13_id is not null OR ({$DDfilter}))";
		}

		$joinDopDispSecond = "LEFT JOIN";
		if (!empty($this->filterDopDispSecond)) {
			$joinDopDispSecond = "INNER JOIN";
		}

		if(isset($data['EvnPLDisp_UslugaComplex']) && $data['EvnPLDisp_UslugaComplex'] > 0)
		{
			$this->where .= "
				and exists (
					select UslugaComplex_id
					from v_EvnUslugaDispDop
					where EvnUslugaDispDop_didDate is not null
						and UslugaComplex_id = :EvnPLDisp_UslugaComplex
						and EvnUslugaDispDop_rid = EPLDD13.EvnPLDispDop13_id
					limit 1
				)
			";
			$this->queryParams['EvnPLDisp_UslugaComplex'] = $data['EvnPLDisp_UslugaComplex'];
		}

		$this->from .= "
			left join v_EvnCostPrint ecp on ecp.Evn_id = EPLDD13.EvnPLDispDop13_id
			left join YesNo IsFinish on IsFinish.YesNo_id = COALESCE(EPLDD13.EvnPLDispDop13_IsEndStage, 1)
			left join YesNo IsMobile on IsMobile.YesNo_id = COALESCE(EPLDD13.EvnPLDispDop13_isMobile, 1)
			left join v_HealthKind HK on HK.HealthKind_id = EPLDD13.HealthKind_id
			left join v_Address UAdd on UAdd.Address_id = EPLDD13.UAddress_id
			left join v_Address PAdd on PAdd.Address_id = EPLDD13.PAddress_id
			{$joinDopDispSecond} LATERAL (
				select
					EPLDD13_SEC.EvnPLDispDop13_id,
					EPLDD13_SEC.EvnPLDispDop13_isPaid,
					EPLDD13_SEC.EvnPLDispDop13_IsTransit,
					EPLDD13_SEC.EvnPLDispDop13_setDate,
					EPLDD13_SEC.EvnPLDispDop13_disDate,
					EPLDD13_SEC.EvnPLDispDop13_consDT,
					EPLDD13_SEC.HealthKind_id,
					COALESCE(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
					HK_SEC.HealthKind_Name,
					EPLDD13_SEC.EvnPLDispDop13_insDT,
					EPLDD13_SEC.EvnPLDispDop13_updDT,
					EPLDD13_SEC.pmUser_insID,
					EPLDD13_SEC.pmUser_updID
				from
					v_EvnPLDispDop13 EPLDD13_SEC
					left join v_HealthKind HK_SEC on HK_SEC.HealthKind_id = EPLDD13_SEC.HealthKind_id
				where
					EPLDD13_SEC.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
					{$this->filterDopDispSecond}
				limit 1
			) DopDispSecond on true -- данные по 2 этапу
			left join YesNo IsFinishSecond on IsFinishSecond.YesNo_id = DopDispSecond.EvnPLDispDop13_IsEndStage
			LEFT JOIN LATERAL (
				select
					DDIC.DopDispInfoConsent_IsAgree
				from
					v_DopDispInfoConsent DDIC 
					left join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				where
					DDIC.EvnPLDisp_id = DopDispSecond.EvnPLDispDop13_id
					and ST.SurveyType_Code = 48
				limit 1
			) DDICDataSecond on true
			LEFT JOIN LATERAL (
				select EvnPLDispDop13_id, Lpu_id
				from v_EvnPLDispDop13
				where Person_id = EPLDD13.Person_id
					and date_part('year', EvnPLDispDop13_setDate) = :PersonDopDisp_Year
					and Lpu_id " . getLpuIdFilter($data, true) . "
					and COALESCE(DispClass_id,1) = 1
				limit 1
			) EPLDD13AL on true
			left join v_Lpu lpu on lpu.Lpu_id = COALESCE(EPLDD13.Lpu_id, EPLDD13AL.Lpu_id)
		";
		if (in_array($this->getRegionNick(), ['buryatiya', 'krym'])) {
			if (!empty($data['UslugaComplex_id'])) {
				$this->withFilters .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
				$this->queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
			}
			$this->from .= "
				LEFT JOIN LATERAL (
					select
						UslugaComplex_id
					from
						v_EvnUslugaDispDop
					where
						EvnUslugaDispDop_IsVizitCode = 2
						and EvnUslugaDispDop_pid = EPLDD13.EvnPLDispDop13_id
					limit 1
				) euddvizit on true
				left join v_UslugaComplex UC on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
			";
		}
	}

	/**
	 * Возвращает данные для блока with
	 */
	function getAdditWith($data) {
		$joinEPLDD13 = "left";
		if (!empty($this->filterEPLDD13)) {
			$joinEPLDD13 = "inner";
		}
		if ($data['PersonPeriodicType_id'] == 2) {
			$ddjoin = "
				{$joinEPLDD13} join v_EvnPLDispDop13 EvnPLDispDop13 on EvnPLDispDop13.Server_id = PS.Server_id and EvnPLDispDop13.PersonEvn_id = PS.PersonEvn_id and EvnPLDispDop13.Lpu_id " . $this->getLpuIdFilter($data) . " and COALESCE(EvnPLDispDop13.DispClass_id,1) = 1 and date_part('year', EvnPLDispDop13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$this->filterEPLDD13}
			";
		}
		else {
			$ddjoin = "
				{$joinEPLDD13} join v_EvnPLDispDop13 EvnPLDispDop13 on PS.Person_id = EvnPLDispDop13.Person_id and EvnPLDispDop13.Lpu_id " . $this->getLpuIdFilter($data) . " and COALESCE(EvnPLDispDop13.DispClass_id,1) = 1 and date_part('year', EvnPLDispDop13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$this->filterEPLDD13}
			";
		}
		if (!empty($data['FarRegistered'])) {
			$ddjoin .= "
				left join TimetableGraf TTG on TTG.Person_id = PS.Person_id
			";
		} 

		if (allowPersonEncrypHIV($data['session'])) {
			$PersonFields = "
				case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname,
				case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname,
				case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname,
				case when PEH.PersonEncrypHIV_id is null then PS.Person_Birthday else null end as Person_Birthday
			";
			$ddjoin .= "
				left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id
			";
		} else {
			$PersonFields = "
				RTRIM(PS.Person_Surname) as Person_Surname,
				RTRIM(PS.Person_Firname) as Person_Firname,
				RTRIM(PS.Person_Secname) as Person_Secname,
				PS.Person_Birthday
			";
		}

		$joinDopDispSecond = "LEFT JOIN";
		if (!empty($this->filterDopDispSecond)) {
			$joinDopDispSecond = "INNER JOIN";
		}

		$this->additWith[] = "
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
					".$ddjoin."
					{$joinDopDispSecond} LATERAL (
						select
							EPLDD13_SEC.EvnPLDispDop13_id,
							EPLDD13_SEC.EvnPLDispDop13_isPaid,
							EPLDD13_SEC.EvnPLDispDop13_IsTransit,
							EPLDD13_SEC.EvnPLDispDop13_setDate,
							EPLDD13_SEC.EvnPLDispDop13_disDate,
							EPLDD13_SEC.EvnPLDispDop13_consDT,
							EPLDD13_SEC.HealthKind_id,
							COALESCE(EPLDD13_SEC.EvnPLDispDop13_IsEndStage, 1) as EvnPLDispDop13_IsEndStage,
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
							{$this->filterDopDispSecond}
						limit 1
					) DopDispSecond on true -- данные по 2 этапу
				where
					{$this->withFilters}
			)
		";
	}

	/**
	 * Возвращает данные для сортировки
	 */
	function getOrderBy($data) {
		$this->orderBy = "
			EPLDD13.Person_SurName,
			EPLDD13.Person_FirName,
			EPLDD13.Person_SecName
		";
	}
}
