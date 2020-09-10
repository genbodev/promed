<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * SearchEvnPLDispDop13Sec_model - модель для форм поиска
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
class SearchEvnPLDispDop13Sec_model extends SearchBase_model {
	protected $main_alias = 'EPLDD13';
	protected $main_table = 'EvnPLDispDop13';

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
			cast(ISNULL([EPLDD13].[EvnPLDispDop13_id], 0) as varchar) + '_' + cast(isnull([PS].[Person_id], 0) as varchar) as [id],
			[EPLDD13].[EvnPLDispDop13_id] as [EvnPLDispDop13_id],
			[PS].[Person_id] as [Person_id],
			[PS].[Server_id] as [Server_id],
			[EPLDD13].[PersonEvn_id] as [PersonEvn_id],
			[EPLDD13].[PayType_id],
			[IsFinish].[YesNo_Name] as [EvnPLDispDop13_IsEndStage],
			[IsMobile].[YesNo_Name] as [EvnPLDispDop13_IsMobile],
			[IsFinishSecond].[YesNo_Name] as [EvnPLDispDop13Second_IsEndStage],
			convert(varchar(10), [EPLDD13].[EvnPLDispDop13_consDT], 104) as [EvnPLDispDop13_setDate],
			convert(varchar(10), [EPLDD13].[EvnPLDispDop13_disDate], 104) as [EvnPLDispDop13_disDate],
			HK.HealthKind_Name as [EvnPLDispDop13_HealthKind_Name],
			case when [EPLDD13].EvnPLDispDop13_IsEndStage = 2 and EPLDD13.EvnPLDispDop13_IsTwoStage = 2 then convert(varchar(10), [EPLDD13].[EvnPLDispDop13_disDate], 104) else null end as EvnPLDispDop13Second_napDate,
			HK_SEC.HealthKind_Name as EvnPLDispDop13Second_HealthKind_Name,
			case when DDICData.DopDispInfoConsent_IsAgree = 1 then convert(varchar(10), [EPLDD13].[EvnPLDispDop13_consDT], 104) else null end as EvnPLDispDop13_rejDate,
			case
				when EPLDD13AL.EvnPLDispDop13_id is not null then 4
				when DopDispSecond.EvnPLDispDop13_id is null then 0
				when EPLDD13.Lpu_id = :Lpu_id then 0
				when DopDispSecond.Lpu_id = :Lpu_id then 0
				when DopDispSecond.Lpu_id " . getLpuIdFilter($data) . " and ISNULL(DopDispSecond.EvnPLDispDop13_IsTransit, 1) = 2 then 0
				else 4
			end as AccessType_Code,
			DopDispSecond.EvnPLDispDop13_id as EvnPLDispDop13Second_id,
			convert(varchar(10), DOCOSMDT.EvnUslugaDispDop_disDate, 104) as VopOsm_EvnUslugaDispDop_disDate, -- дата осмотра врача-терапевта на первом этапе
			ISNULL(DopDispSecond.EvnPLDispDop13_IsTransit, 1) as EvnPLDispDop13Second_IsTransit,
			convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) as EvnPLDispDop13Second_setDate,
			convert(varchar(10), DopDispSecond.EvnPLDispDop13_disDate, 104) as EvnPLDispDop13Second_disDate,
			case when DDICDataSecond.DopDispInfoConsent_IsAgree = 1 then convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) else null end as EvnPLDispDop13Second_rejDate,
			convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
			case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
		";
		if (in_array(getRegionNick(), array('ufa'))) {
			$this->select .= "
				,lpu.Lpu_Nick
			";
		}
		if (in_array(getRegionNick(), array('buryatiya', 'krym'))) {
			$this->select .= "
				,ISNULL(UC.UslugaComplex_Code + '. ','') + UC.UslugaComplex_Name as UslugaComplex_Name
			";
		}

		$this->select .= "
			,ISNULL(UAdd.Address_Nick, UAdd.Address_Address) as ua_name
			,ISNULL(PAdd.Address_Nick, PAdd.Address_Address) as pa_name
		";
		if (allowPersonEncrypHIV($data['session'])) {
			$this->select .= "
				,case when PEH.PersonEncrypHIV_id is null then rtrim(PS.Person_SurName) else rtrim(PEH.PersonEncrypHIV_Encryp) end as Person_Surname
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_FirName) else '' end as Person_Firname
				,case when PEH.PersonEncrypHIV_id is null then RTRIM(PS.Person_SecName) else '' end as Person_Secname
				,case when PEH.PersonEncrypHIV_id is null then convert(varchar(10), PS.Person_Birthday, 104) else null end as Person_Birthday
			";
		} else {
			$this->select .= "
				,RTRIM(PS.Person_SurName) as Person_Surname
				,RTRIM(PS.Person_FirName) as Person_Firname
				,RTRIM(PS.Person_SecName) as Person_Secname
				,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
			";
		}
	}

	/**
	 * Возвращает данные для фильтрации
	 */
	function getFromAndWhere($data) {
		$this->from = 'v_PersonState PS (nolock)';
		if (allowPersonEncrypHIV($data['session'])) {
			$this->from .= "
				left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
			";
		}

		$filterEPLDD13 = "";
		$filterDDICData = "";
		$filterDopDispSecond = "";

		// https://redmine.swan.perm.ru/issues/37296
		$this->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');

		if (isset($data['EvnPLDispDop13_setDate'])) {
			$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_setDate = cast(:EvnPLDispDop13_setDate as datetime) ";
			$this->queryParams['EvnPLDispDop13_setDate'] = $data['EvnPLDispDop13_setDate'];
		}
		if (isset($data['EvnPLDispDop13_setDate_Range'][0])) {
			$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13_setDate_Range_0 as datetime) ";
			$this->queryParams['EvnPLDispDop13_setDate_Range_0'] = $data['EvnPLDispDop13_setDate_Range'][0];
		}
		if (isset($data['EvnPLDispDop13_setDate_Range'][1])) {
			$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13_setDate_Range_1 as datetime) ";
			$this->queryParams['EvnPLDispDop13_setDate_Range_1'] = $data['EvnPLDispDop13_setDate_Range'][1];
		}
		if (isset($data['EvnPLDispDop13_disDate'])) {
			$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_disDate = cast(:EvnPLDispDop13_disDate as datetime) ";
			$this->queryParams['EvnPLDispDop13_disDate'] = $data['EvnPLDispDop13_disDate'];
		}
		if (isset($data['EvnPLDispDop13_disDate_Range'][0])) {
			$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13_disDate_Range_0 as datetime) ";
			$this->queryParams['EvnPLDispDop13_disDate_Range_0'] = $data['EvnPLDispDop13_disDate_Range'][0];
		}
		if (isset($data['EvnPLDispDop13_disDate_Range'][1])) {
			$filterEPLDD13 .= " and [EPLDD13].EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13_disDate_Range_1 as datetime) ";
			$this->queryParams['EvnPLDispDop13_disDate_Range_1'] = $data['EvnPLDispDop13_disDate_Range'][1];
		}
		if (isset($data['EvnPLDispDop13_IsFinish'])) {
			$filterEPLDD13 .= " and isnull([EPLDD13].EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13_IsFinish ";
			$this->queryParams['EvnPLDispDop13_IsFinish'] = $data['EvnPLDispDop13_IsFinish'];
		}
		if (isset($data['EvnPLDispDop13_Cancel'])) {
			if ($data['EvnPLDispDop13_Cancel'] == 2) {
				$data['DopDispInfoConsent_IsAgree'] = 1;
			} else {
				$data['DopDispInfoConsent_IsAgree'] = 2;
			}
			$filterDDICData .= " and ISNULL(DDIC.DopDispInfoConsent_IsAgree,2) = :DopDispInfoConsent_IsAgree ";
			$this->queryParams['DopDispInfoConsent_IsAgree'] = $data['DopDispInfoConsent_IsAgree'];
		}
		if (isset($data['EvnPLDispDop13_IsTwoStage'])) {
			$filterEPLDD13 .= " and isnull([EPLDD13].EvnPLDispDop13_IsTwoStage, 1) = :EvnPLDispDop13_IsTwoStage ";
			$this->queryParams['EvnPLDispDop13_IsTwoStage'] = $data['EvnPLDispDop13_IsTwoStage'];
		}

		if (isset($data['EvnPLDispDop13_HealthKind_id'])) {
			$filterEPLDD13 .= " and [EPLDD13].HealthKind_id = :EvnPLDispDop13_HealthKind_id ";
			$this->queryParams['EvnPLDispDop13_HealthKind_id'] = $data['EvnPLDispDop13_HealthKind_id'];
		}

		if (isset($data['EvnPLDispDop13_isPaid'])) {
			if ($data['session']['region']['nick'] == 'ufa') {
				// https://redmine.swan.perm.ru/issues/56232
				if ( $data['EvnPLDispDop13_isPaid'] == 2 ) {
					$this->where .= " and exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
				}
				else {
					$this->where .= " and not exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
				}
			}
			else {
				$filterEPLDD13 .= " and ISNULL([EPLDD13].EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13_isPaid ";
				$this->queryParams['EvnPLDispDop13_isPaid'] = $data['EvnPLDispDop13_isPaid'];
			}
		}

		if (isset($data['EvnPLDispDop13Second_isPaid'])) {
			if ($data['session']['region']['nick'] == 'ufa') {
				// https://redmine.swan.perm.ru/issues/77587
				if ( $data['EvnPLDispDop13Second_isPaid'] == 2 ) {
					$this->where .= " and exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = DopDispSecond.EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
				}
				else {
					$this->where .= " and not exists (select top 1 EvnVizitDispDop_id from v_EvnVizitDispDop t1 with (nolock) where t1.EvnVizitDispDop_pid = DopDispSecond.EvnPLDispDop13_id and ISNULL(t1.EvnVizitDispDop_IsPaid, 1) = 2)";
				}
			} else {
				$filterDopDispSecond .= " and ISNULL([DopDispSecond].EvnPLDispDop13_isPaid,1) = :EvnPLDispDop13Second_isPaid ";
				$this->queryParams['EvnPLDispDop13Second_isPaid'] = $data['EvnPLDispDop13Second_isPaid'];
			}
		}

		if (isset($data['EvnPLDispDop13_isMobile'])) {
			$filterEPLDD13 .= " and ISNULL([EPLDD13].EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13_isMobile ";
			$this->queryParams['EvnPLDispDop13_isMobile'] = $data['EvnPLDispDop13_isMobile'];
		}

		if (isset($data['EvnPLDispDop13Second_isMobile'])) {
			$filterDopDispSecond .= " and ISNULL([DopDispSecond].EvnPLDispDop13_isMobile,1) = :EvnPLDispDop13Second_isMobile ";
			$this->queryParams['EvnPLDispDop13Second_isMobile'] = $data['EvnPLDispDop13Second_isMobile'];
		}

		if (isset($data['EvnPLDispDop13Second_setDate'])) {
			$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_setDate = cast(:EvnPLDispDop13Second_setDate as datetime) ";
			$this->queryParams['EvnPLDispDop13Second_setDate'] = $data['EvnPLDispDop13Second_setDate'];
		}
		if (isset($data['EvnPLDispDop13Second_setDate_Range'][0])) {
			$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_setDate >= cast(:EvnPLDispDop13Second_setDate_Range_0 as datetime) ";
			$this->queryParams['EvnPLDispDop13Second_setDate_Range_0'] = $data['EvnPLDispDop13Second_setDate_Range'][0];
		}
		if (isset($data['EvnPLDispDop13Second_setDate_Range'][1])) {
			$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_setDate <= cast(:EvnPLDispDop13Second_setDate_Range_1 as datetime) ";
			$this->queryParams['EvnPLDispDop13Second_setDate_Range_1'] = $data['EvnPLDispDop13Second_setDate_Range'][1];
		}
		if (isset($data['EvnPLDispDop13Second_disDate'])) {
			$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_disDate = cast(:EvnPLDispDop13Second_disDate as datetime) ";
			$this->queryParams['EvnPLDispDop13Second_disDate'] = $data['EvnPLDispDop13Second_disDate'];
		}
		if (isset($data['EvnPLDispDop13Second_disDate_Range'][0])) {
			$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_disDate >= cast(:EvnPLDispDop13Second_disDate_Range_0 as datetime) ";
			$this->queryParams['EvnPLDispDop13Second_disDate_Range_0'] = $data['EvnPLDispDop13Second_disDate_Range'][0];
		}
		if (isset($data['EvnPLDispDop13Second_disDate_Range'][1])) {
			$filterDopDispSecond .= " and [DopDispSecond].EvnPLDispDop13_disDate <= cast(:EvnPLDispDop13Second_disDate_Range_1 as datetime) ";
			$this->queryParams['EvnPLDispDop13Second_disDate_Range_1'] = $data['EvnPLDispDop13Second_disDate_Range'][1];
		}

		if (isset($data['EvnPLDispDop13Second_IsFinish'])) {
			$filterDopDispSecond .= " and isnull([DopDispSecond].EvnPLDispDop13_IsEndStage, 1) = :EvnPLDispDop13Second_IsFinish ";
			$this->queryParams['EvnPLDispDop13Second_IsFinish'] = $data['EvnPLDispDop13Second_IsFinish'];
		}

		if (isset($data['EvnPLDispDop13Second_HealthKind_id'])) {
			$filterDopDispSecond .= " and [DopDispSecond].HealthKind_id = :EvnPLDispDop13Second_HealthKind_id ";
			$this->queryParams['EvnPLDispDop13Second_HealthKind_id'] = $data['EvnPLDispDop13Second_HealthKind_id'];
		}

		if (isset($data['PersonDopDisp_Year'])) {
			// $this->where .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
			$this->queryParams['PersonDopDisp_Year'] = $data['PersonDopDisp_Year'];
		} else {
			$this->queryParams['PersonDopDisp_Year'] = 2013;
		}

		$joinDopDisp = "inner";
		if (getRegionNick() == 'ekb') {
			// не обязательно есть карта первого этапа
			$joinDopDisp = "left";
		}

		$this->variables[] = "declare @PersonDopDisp_YearEndDate datetime = cast(:PersonDopDisp_Year as varchar) + '-12-31';";

		$this->queryParams['Lpu_id'] = $data['Lpu_id'];

		$joinDDICData = "outer";
		if (!empty($filterDDICData)) {
			$joinDDICData = "cross";
		}

		if (!empty($filterDopDispSecond)) {
			// если есть фильтр по 2 этапу, то от него и пляшем
			if ($data['PersonPeriodicType_id'] == 2) {
				$this->from .= "
					inner join [v_EvnPLDispDop13] [DopDispSecond] with (nolock) on DopDispSecond.Server_id = PS.Server_id and DopDispSecond.PersonEvn_id = PS.PersonEvn_id and [DopDispSecond].Lpu_id " . $this->getLpuIdFilter($data) . " and DopDispSecond.DispClass_id = 2 and YEAR(DopDispSecond.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterDopDispSecond}
				";
			} else {
				$this->from .= "
					inner join [v_EvnPLDispDop13] [DopDispSecond] with (nolock) on [PS].[Person_id] = [DopDispSecond].[Person_id] and [DopDispSecond].Lpu_id " . $this->getLpuIdFilter($data) . " and DopDispSecond.DispClass_id = 2 and YEAR(DopDispSecond.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterDopDispSecond}
				";
			}

			$this->from .= "
				{$joinDopDisp} join v_EvnPLDispDop13 (nolock) [EPLDD13] on [EPLDD13].EvnPLDispDop13_id = DopDispSecond.EvnPLDispDop13_fid
			";
		} else {
			// иначе лефт джойн к 1 этапу
			if ($data['PersonPeriodicType_id'] == 2) {
				$this->from .= "
				{$joinDopDisp} join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on EPLDD13.Server_id = PS.Server_id and EPLDD13.PersonEvn_id = PS.PersonEvn_id and [EPLDD13].Lpu_id " . $this->getLpuIdFilter($data) . " and ISNULL(EPLDD13.DispClass_id,1) = 1 and YEAR(EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
			";
			} else {
				$this->from .= "
				{$joinDopDisp} join [v_EvnPLDispDop13] [EPLDD13] with (nolock) on [PS].[Person_id] = [EPLDD13].[Person_id] and [EPLDD13].Lpu_id " . $this->getLpuIdFilter($data) . " and ISNULL(EPLDD13.DispClass_id,1) = 1 and YEAR(EPLDD13.EvnPLDispDop13_consDT) = :PersonDopDisp_Year {$filterEPLDD13}
			";
			}

			$mainFilterDopDispSecond = "(DopDispSecond.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id)";
			if (getRegionNick() == 'ekb') {
				$mainFilterDopDispSecond = "(
					DopDispSecond.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id
					OR (
						DopDispSecond.EvnPLDispDop13_id IS NOT NULL
						and DopDispSecond.Person_id = PS.Person_id
						and DopDispSecond.Lpu_id " . $this->getLpuIdFilter($data) . "
						and DopDispSecond.DispClass_id = 2
						and DopDispSecond.EvnPLDispDop13_fid IS NULL
						and YEAR(DopDispSecond.EvnPLDispDop13_consDT) = :PersonDopDisp_Year
					)
				)";
			}

			$this->from .= "
				left join v_EvnPLDispDop13 (nolock) DopDispSecond on {$mainFilterDopDispSecond} {$filterDopDispSecond}
			";
		}

		$this->from .= "
			left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = DopDispSecond.EvnPLDispDop13_id
			left join [YesNo] [IsFinish] with (nolock) on [IsFinish].[YesNo_id] = ISNULL([EPLDD13].[EvnPLDispDop13_IsEndStage], 1)
			left join [YesNo] [IsMobile] with (nolock) on [IsMobile].[YesNo_id] = ISNULL([EPLDD13].[EvnPLDispDop13_isMobile], 1)
			left join v_HealthKind HK with (nolock) on HK.HealthKind_id = EPLDD13.HealthKind_id
			left join v_Address UAdd (nolock) on UAdd.Address_id = PS.UAddress_id
			left join v_Address PAdd (nolock) on PAdd.Address_id = PS.PAddress_id
			{$joinDDICData} apply(
				select top 1
					DDIC.DopDispInfoConsent_IsAgree
				from
					v_DopDispInfoConsent DDIC (nolock)
					left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				where
					DDIC.EvnPLDisp_id = EPLDD13.EvnPLDispDop13_id
					and ST.SurveyType_Code = 1
					{$filterDDICData}
			) DDICData
			outer apply( -- дата осмотра врачём-терапевтом из первого этапа
				select top 1
					ISNULL(EUDD.EvnUslugaDispDop_disDate, EUDD.EvnUslugaDispDop_didDate) as EvnUslugaDispDop_disDate
				from v_EvnUslugaDispDop EUDD (nolock)
					left join v_EvnVizitDispDop EVDD (nolock) on EVDD.EvnVizitDispDop_id = EUDD.EvnUslugaDispDop_pid
					left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EUDD.UslugaComplex_id
					left join v_DopDispInfoConsent DDIC (nolock) on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				where
					EVDD.EvnVizitDispDop_pid = EPLDD13.EvnPLDispDop13_id
					and ST.SurveyType_Code = 19
					and ISNULL(EUDD.EvnUslugaDispDop_IsVizitCode, 1) = 1
			) DOCOSMDT
			left join v_HealthKind HK_SEC with (nolock) on HK_SEC.HealthKind_id = DopDispSecond.HealthKind_id
			left join [YesNo] [IsFinishSecond] with (nolock) on [IsFinishSecond].[YesNo_id] = [DopDispSecond].[EvnPLDispDop13_IsEndStage]
			outer apply(
				select top 1
					DDIC.DopDispInfoConsent_IsAgree
				from
					v_DopDispInfoConsent DDIC (nolock)
					left join v_SurveyTypeLink STL (nolock) on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					left join v_SurveyType ST (nolock) on ST.SurveyType_id = STL.SurveyType_id
				where
					DDIC.EvnPLDisp_id = DopDispSecond.EvnPLDispDop13_id
					and ST.SurveyType_Code = 48
			) DDICDataSecond
			outer apply(
				select top 1 EvnPLDispDop13_id, Lpu_id
				from v_EvnPLDispDop13 (nolock)
				where Person_id = PS.Person_id
					and YEAR(EvnPLDispDop13_setDate) = :PersonDopDisp_Year
					and Lpu_id " . getLpuIdFilter($data, true) . "
					and ISNULL(DispClass_id,1) = 1
			) EPLDD13AL
			left join v_Lpu lpu with (nolock) on lpu.Lpu_id = ISNULL(EPLDD13.Lpu_id, EPLDD13AL.Lpu_id)
		";
		if (in_array($data['session']['region']['nick'], array('buryatiya', 'krym'))) {
			if (!empty($data['UslugaComplex_id'])) {
				$this->where .= " and euddvizit.UslugaComplex_id = :UslugaComplex_id ";
				$this->queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
			}
			$this->from .= "
				outer apply(
					select top 1
						UslugaComplex_id
					from
						v_EvnUslugaDispDop (nolock)
					where
						EvnUslugaDispDop_IsVizitCode = 2
						and EvnUslugaDispDop_pid = DopDispSecond.EvnPLDispDop13_id
				) euddvizit
				left join v_UslugaComplex UC (nolock) on uc.UslugaComplex_id = euddvizit.UslugaComplex_id
			";
		}
	}
}
