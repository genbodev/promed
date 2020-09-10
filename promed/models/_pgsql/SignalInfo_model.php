<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SignalInfo_model extends SwPgModel {

  public $inputRules = array(
   'loadEvnPS' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_aid', 'label' => 'ЛПУ прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
		'loadEvnUsluga' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
		'loadRegisterPrivilege' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
		'loadEvnStick' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
		'loadDeathSvid' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
		'loadCmpCallCard' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
		'loadListByDayDisp' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
		'loadListByDayDisp2' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
	  'loadPersonNoVisit' => array(
		  array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
		  array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
		  array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ врача', 'rules' => '', 'type' => 'id'),
		  array('default' => '', 'field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
		  array('default' => '', 'field' => 'begDate', 'label' => 'Дата записи (0)', 'rules' => 'required', 'type' => 'date')
			//array('default' => '', 'field' => 'endDate', 'label' => 'Дата записи (1)', 'rules' => 'required', 'type' => 'date')
		),
	  'loadCdk' => array(
		  array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
		  array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
		  array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ врача', 'rules' => '', 'type' => 'id'),
		  array('default' => '', 'field' => 'MedStaffFact_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
		  array('default' => '', 'field' => 'EvnDirection_setDate', 'label' => 'Дата направления', 'rules' => 'required', 'type' => 'date')
	  ),
		'setIsBrowsed' => array(
			array('field' => 'EvnDirection_id', 'label' => '', 'rules' => '', 'type' => 'int')
		),
	  'loadFromStac' => array(
		  array('field' => 'Lpu_id', 'label' => 'МО госпитализации', 'rules' => '', 'type' => 'id'),
		  array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
		  array('field' => 'MedService_id', 'label' => 'Отделение госпитализации', 'rules' => '', 'type' => 'id'),
		  array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
		  array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
		  array('field' => 'DisDateFrom', 'label' => 'Начало дат выписки', 'rules' => '', 'type' => 'date'),
		  array('field' => 'DisDateTo', 'label' => 'Конец дат выписки', 'rules' => '', 'type' => 'date'),
		  array('field' => 'HospitDateFrom', 'label' => 'Начало дат госпитализации', 'rules' => '', 'type' => 'date'),
		  array('field' => 'HospitDateTo', 'label' => 'Конец дат госпитализации', 'rules' => '', 'type' => 'date'),
		  array('field' => 'EvnPS_NumCard', 'label' => 'Номер КВС', 'rules' => '', 'type' => 'int'),
	  ),
		'loadPregnancyRouteNotConsultation' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_iid', 'label' => 'ЛПУ учета', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_iid', 'label' => 'Врач', 'rules' => '', 'type' => 'string'),
			array('default' => '', 'field' => 'Trimester', 'label' => 'Триместр', 'rules' => '', 'type' => 'string')
		),
		'loadPregnancyRouteHospital' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_iid', 'label' => 'ЛПУ учета', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_iid', 'label' => 'Врач', 'rules' => '', 'type' => 'string'),
			array('default' => '', 'field' => 'Date_Range', 'label' => 'Дата записи (диапазон)', 'rules' => '', 'type' => 'daterange'),
			array('default' => '', 'field' => 'PregnancyRouteType', 'label' => 'Тип поиска по беременным', 'rules' => '', 'type' => 'string'),
			array('default' => '', 'field' => 'Trimester', 'label' => 'Триместр', 'rules' => '', 'type' => 'string')
		),
		'loadPregnancyRouteSMP' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_iid', 'label' => 'ЛПУ учета', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_iid', 'label' => 'Врач', 'rules' => '', 'type' => 'string'),
			array('default' => '', 'field' => 'CmpCallCard_prmDate_Range', 'label' => 'Дата приема (диапазон)', 'rules' => '', 'type' => 'daterange'),
			array('default' => '', 'field' => 'Yesterday', 'label' => 'За вчерашний день', 'rules' => '', 'type' => 'id')
		),
		'loadTrimesterListMO' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ учета', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'string'),
			array('default' => '', 'field' => 'Type', 'label' => 'Тип поиска','rules' => '', 'type' => 'string'),
		),
		'loadPregnancyNotIncludeList' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id', 'session_value' => 'medpersonal_id'),
		),
		'loadListRegistBSK' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'МО прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'BSKRegistry_setDateNext', 'label' => 'Дата приема (диапазон)', 'rules' => '', 'type' => 'daterange')
	 	),
		'loadCVI' => array(
			array('default' => 0, 'field' => 'Lpu_id', 'label' => 'ЛПУ учета', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id')
		),
		'loadPersonDispInfo' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => '', 'field' => 'Lpu_id', 'label' => 'ЛПУ', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'MedPersonal_id', 'label' => 'Врач оснойвной или прикрепления', 'rules' => '', 'type' => 'id'),
			array('default' => '', 'field' => 'PersonDispInfo_prmDate_Range', 'label' => 'Дата планового осмотра (диапазон)', 'rules' => '', 'type' => 'daterange')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка журнал выбывших
	 */
	function loadEvnPS($data) {
		$filters = "";
		$params = array();

		if (isset($data['Lpu_aid']) && $data['Lpu_aid'] > 0) {
			$params['Lpu_aid'] = $data['Lpu_aid'];
			$filters .= "and Lpu_id = :Lpu_aid ";
		}

		if (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters .= "and MedPersonal_id = :MedPersonal_id ";
		}

		$diagFilter = getAccessRightsDiagFilter('Diag_Code');
		if (!empty($diagFilter)) {
			$filters .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('Lpu_ideps');
		if (!empty($lpuFilter)) {
			$filters .= " and $lpuFilter";
		}

		$query = "
			SELECT
				-- select
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				Person_Fio as \"Person_Fio\",
				to_char(Person_Birthday, 'dd.mm.yyyy') AS \"Person_Birthday\",
				Person_Age as \"Person_Age\",
				LpuSections_Name as \"LpuSections_Name\",
				Lpu_Name as \"Lpu_Name\",
				Diag_Name as \"Diag_Name\",
				PrehospType_Name as \"PrehospType_Name\",
				to_char(EvnPS_disDateTime, 'dd.mm.yyyy') AS \"EvnPS_disDateTime\",
				Diag_Code as \"Diag_Code\",
				Lpu_ideps as \"Lpu_ideps\"
				-- end select
			FROM
				-- from
				SignalInfoEJHW
				-- end from
			WHERE
				-- where
				(1 = 1)
				{$filters}
				-- end where
			ORDER BY
				-- order by
				\"EvnPS_disDateTime\" DESC
				-- end order by
		";
		//return $this->queryResult($query, $params);
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	function loadFromStac($data) {
		$filters = "";
		$params = array();

		if(isset($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters .= "and EPS.Lpu_id = :Lpu_id ";
		}

		/*if(isset($data['LpuSection_id'])) {
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$filters .= "and ESEC.LpuSection_id = :LpuSection_id ";
		}*/

		if (isset($data['MedService_id']) && $data['MedService_id'] > 0) {
			$params['MedService_id'] = $data['MedService_id'];
			$filters .= "and MedService_id = :MedService_id ";
		}

		if (isset($data['EvnPS_NumCard']) && $data['EvnPS_NumCard'] > 0) {
			$params['EvnPS_NumCard'] = $data['EvnPS_NumCard'];
			$filters .= "and EPS.EvnPS_NumCard = :EvnPS_NumCard ";
		}

		if (isset($data['DisDateFrom'])) {
			$params['DisDateFrom'] = $data['DisDateFrom'];
			$filters .= "and ESEC.EvnSection_disDate >= :DisDateFrom ";
		}

		if (isset($data['DisDateTo'])) {
			$params['DisDateTo'] = $data['DisDateTo'];
			$filters .= "and ESEC.EvnSection_disDate <= :DisDateTo ";
		}

		if (isset($data['HospitDateFrom'])) {
			$params['HospitDateFrom'] = $data['HospitDateFrom'];
			$filters .= "and EPS.EvnPS_setDate >= :HospitDateFrom ";
		}

		if (isset($data['HospitDateTo'])) {
			$params['HospitDateTo'] = $data['HospitDateTo'];
			$filters .= "and EPS.EvnPS_setDate <= :HospitDateTo ";
		}

		$limiter = "";
		$offset = "";
		if(isset($data['start']) && $data['start'] > 0) {
			$offset .= " OFFSET " . $data['start'];
			if(isset($data['limit']) && $data['limit']>0) {
				$offset .= " LIMIT " . $data['limit'];
			}
		} else if (isset($data['limit']) && $data['limit']>0) {
			$limiter .= " LIMIT " . $data['limit'];
		}

		$query = "
				SELECT
						EPS.EvnPS_id as \"EvnPS_id\",
						EPS.Person_id as \"Person_id\",
						EPS.PersonEvn_id as \"PersonEvn_id\",
						EPS.Server_id as \"Server_id\",
						ESEC.EvnSection_id as \"EvnSection_id\",
						COALESCE(EPS.EvnPS_IsTransit, 1) as \"EvnPS_IsTransit\",
						RTRIM(EPS.EvnPS_NumCard) as \"EvnPS_NumCard\",
						to_char(EPS.EvnPS_setDate, 'dd.mm.yyyy') as \"EvnPS_setDate\",
						to_char(EPS.EvnPS_disDate, 'dd.mm.yyyy') as \"EvnPS_disDate\",
						COALESCE(LP.Lpu_Name, '') as \"Lpu_Name\",
						COALESCE(LStmp.LpuSection_Name, '') as \"LpuSection_Name\",
						COALESCE(Dtmp.Diag_FullName, DP.Diag_FullName) as \"Diag_Name\",
						COALESCE(Prehos.PrehospType_Name, '') as \"PrehospType_Name\",
						-- поскольку в одном КВС не может быть движений по круглосуточным и дневным стационарам вместе (поскольку это делается через перевод и создание новой карты)
						-- то подсчет количества койкодней реализуем так (с) Night, 2011-06-22
						
					case when LpuUnitType.LpuUnitType_SysNick = 'stac'
					then extract(DAY FROM (EPS.EvnPS_setDate - EPS.EvnPS_disDate)) + abs(sign(extract(day from (EPS.EvnPS_setDate - EPS.EvnPS_disDate))) - 1) -- круглосуточные
					else (extract(day from (EPS.EvnPS_setDate - EPS.EvnPS_disDate)) + 1) -- дневные
					end as \"EvnPS_KoikoDni\",
					
						--CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ,
						dbfpayt.PayType_Name as \"PayType_Name\", --Вид оплаты
						CASE
							WHEN LT.LeaveType_Name is not null THEN LT.LeaveType_Name
							WHEN EPS.PrehospWaifRefuseCause_id > 0 THEN pwrc.PrehospWaifRefuseCause_Name
							ELSE ''
						END as \"LeaveType_Name\",
						LT.LeaveType_Code as \"LeaveType_Code\",
						CASE WHEN DeathSvid.DeathSvid_id is null then 'false'
						else 'true'
						end as \"DeadSvid\",
						EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
						 COALESCE(ksgkpg.Mes_Code, '') || COALESCE(ksgkpg.Mes_Name, '') as \"EvnSection_KSG\",
						COALESCE(ksgkpg.Mes_Code, '') ||  COALESCE(ksgkpg.Mes_Name, '') as \"EvnSection_KSGKPG\",
						 kpg.Mes_Code as \"EvnSection_KPG\",
						to_char(ecp.EvnCostPrint_setDT, 'dd.mm.yyyy') as \"EvnCostPrint_setDT\",
						case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as \"EvnCostPrint_IsNoPrintText\"
							,RTRIM(PS.Person_SurName) as \"Person_Surname\"
							,RTRIM(PS.Person_FirName) as \"Person_Firname\"
							,RTRIM(PS.Person_SecName) as \"Person_Secname\"
							,COALESCE(ps.Person_SurName,'')||COALESCE(' '||ps.Person_FirName,'')||COALESCE(' '||ps.Person_SecName,'') as \"Person_Fio\"
							,to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
							,dbo.Age2(PS.Person_Birthday, dbo.tzGetDate()) as \"Person_Age\"
							,COALESCE(to_char(DeathSvid.DeathSvid_DeathDate,'dd.mm.yyyy'),to_char(EvnDie.EvnDie_setDate, 'dd.mm.yyyy'),to_char(PS.Person_DeadDT, 'dd.mm.yyyy'),'') as \"Person_deadDT\"
						,case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as \"fedservice_iemk\"
				, case when COALESCE(EPS.EvnPS_IsArchive, 1) = 1 then 0 else 1 end as \"archiveRecord\"
			
			
			FROM  
							v_PersonState PS
						 inner join v_EvnPS EPS on EPS.Person_id = PS.Person_id and EPS.Lpu_id in (10010833,101,150185)
						 inner join v_EvnSection as ESEC on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id in (10010833,101,150185)
								left join v_EvnCostPrint ecp  on ecp.Evn_id = EPS.EvnPS_id
								left join v_Lpu LP on EPS.Lpu_id = LP.Lpu_id
								left join v_PrehospType Prehos on EPS.PrehospType_id = Prehos.PrehospType_id
								left join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 and EPSLastES.Lpu_id in (10010833,101,150185)
								left join LpuSection LStmp on LStmp.LpuSection_id = EPSLastES.LpuSection_id
								left join v_Diag Dtmp on Dtmp.Diag_id = EPSLastES.Diag_id
								left join LeaveType LT on LT.LeaveType_id = COALESCE(EPSLastES.LeaveType_id, EPSLastES.LeaveType_prmid)
								left JOIN LATERAL(
									select *
									from v_MedPersonal 
									where MedPersonal_id = EPSLastES.MedPersonal_id
										and Lpu_id in (10010833,101,150185)
									order by
										case when Lpu_id = '10010833' then 1 else 2 end
										limit 1
								) MP on true
								left join v_Diag DP on DP.Diag_id = EPS.Diag_pid
								left join LpuSection LS on LS.LpuSection_id = EPS.LpuSection_id
								left join v_PrehospWaifRefuseCause pwrc on pwrc.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
								left join LpuUnit  on LpuUnit.LpuUnit_id = LStmp.LpuUnit_id 
								left join LpuUnitType  on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id 
								left join PayType dbfpayt  on dbfpayt.PayType_id = EPS.PayType_id
								left join v_Polis pls on pls.Polis_id = ps.Polis_id
								left JOIN LATERAL (
									select EvnDie_setDate
									from v_EvnDie
									where Person_id = PS.Person_id
									order by EvnDie_setDate
									limit 1
								) EvnDie on true
								left JOIN LATERAL(
									SELECT DeathSvid_id,DeathSvid_DeathDate 
									FROM dbo.v_DeathSvid
									WHERE Person_id = PS.Person_id and (DeathSvid_IsBad is null or DeathSvid_IsBad = 1)
									limit 1
								) DeathSvid on true
								left join v_MesTariff spmt on EPSLastES.MesTariff_id = spmt.MesTariff_id
								left join v_MesOld as sksg on sksg.Mes_id = EPSLastES.Mes_sid
								left join v_MesOld as ksg on ksg.Mes_id = case
									when spmt.Mes_id in (EPSLastES.Mes_sid, EPSLastES.Mes_tid) then spmt.Mes_id
									else COALESCE(EPSLastES.Mes_sid, EPSLastES.Mes_tid)
								end
								left join v_MesOld as ksgkpg on spmt.Mes_id = ksgkpg.Mes_id
								left join v_MesOld as kpg on kpg.Mes_id = EPSLastES.Mes_kid
								left join v_EvnReanimatPeriod ERP on ERP.EvnReanimatPeriod_rid = EPS.EvnPS_id
								left JOIN LATERAL (
									select ESS.EvnSection_id
									from v_EvnSection ESS
									inner join v_Diag ESSDiag on ESSDiag.Diag_id = ESS.Diag_id
									where ESS.EvnSection_pid = EPS.EvnPS_id
										and ESS.Lpu_id in (10010833,101,150185)
								 and ((coalesce(ESSDiag.Diag_Code, '') in('H00.0')) or (coalesce(ESSDiag.Diag_Code, '') between 'B00.0' and 'B00.9'))
								 limit 1) adf on true
					left JOIN LATERAL (
						select ServiceEvnStatus_id
						from v_ServiceEvnHist
						where Evn_id = EPS.EvnPS_id
							and ServiceEvnList_id = 1
						order by ServiceEvnHist_id desc
						limit 1
					) SEH1 on true
					left join v_ServiceEvnStatus SES1 on SES1.ServiceEvnStatus_id = SEH1.ServiceEvnStatus_id
				
					 WHERE 
					(1 = 1) 
					and COALESCE(EPS.EvnPS_IsArchive, 1) = 1 
					and adf.EvnSection_id is null 
					and coalesce(Dtmp.Diag_Code, '') not in('H00.0') 
					and coalesce(Dtmp.Diag_Code, '') not between 'B00.0' and 'B00.9'
					{$filters}
						ORDER BY 
							EPS.EvnPS_id
						{$limiter}
						{$offset}
		";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		}
	}

	/**
	 * Получение списка выполненных услуг (Параклинические услуги)
	 */
	function loadEvnUsluga($data) {
		$filters = "";

		$queryParams = array();

		if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filters .= "and Lpu_id = :Lpu_id ";
			$filters .= "and Lpu_iid = :Lpu_id ";
		}

		if (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters .= "and MedPersonal_id = :MedPersonal_id ";
		}

		$query = "
			SELECT
				-- select
				S.Person_id as \"Person_id\",
				S.PersonEvn_id as \"PersonEvn_id\",
				S.Server_id as \"Server_id\",
				S.EvnXml_id as \"EvnXml_id\",
				to_char(S.EvnUslugaPar_setDate, 'dd.mm.yyyy') AS \"EvnUslugaPar_setDate\",
				S.MedPersonal_Fio as \"S.MedPersonal_Fio\",
				S.Usluga_Code as \"Usluga_Code\",
				RTRIM(S.Usluga_Name) as \"Usluga_Name\",
				S.Person_Surname as \"Person_Surname\",
				S.Person_Firname as \"Person_Firname\",
				S.Person_Secname as \"Person_Secname\",
				to_char(S.Person_Birthday, 'dd.mm.yyyy') AS \"Person_Birthday\",
				S.Person_Age as \"Person_Age\",
				xth.XmlTemplateHtml_HtmlTemplate as \"XmlTemplate_HtmlTemplate\"
				-- end select
			FROM
				-- from
				SignalInfoEvnUsluga S
				left join v_EvnXml EvnXml on S.EvnXml_id = EvnXml.EvnXml_id
				left join XmlTemplateHtml xth  on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
				-- end from
			where
				-- where
				(1=1)
				{$filters}
				-- end where
			order by
				-- order by
				\"EvnUslugaPar_setDate\"  DESC
				-- end order by
		";
		//return $this->queryResult($query, $params);
		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
		//return $response;
	}

	/**
	 * Получение списка льготников
	 */
	function loadRegisterPrivilege($data) {
		$filters = "";
		$params = array();

		if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters .= "and Lpu_id = :Lpu_id ";
		}

		if (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters .= "and MedPersonal_id = :MedPersonal_id ";
		}

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filters .= " and $privilegeFilter";
		}

		$lpuFilter = getAccessRightsLpuFilter("Lpu_id");
		$lpuFilter = !empty($lpuFilter) ? " and {$lpuFilter}" : '';
		$filters .= " and exists (select  PP.PersonPrivilege_id from v_PersonPrivilege as PP  where PP.PrivilegeType_id = PrivilegeType_id and PP.Person_id = Person_id {$lpuFilter} limit 1)";


		$query = "
			SELECT
				-- select
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				to_char(Privilege_begDate, 'dd.mm.yyyy') AS \"Privilege_begDate\",
				to_char(Privilege_endDate, 'dd.mm.yyyy') AS \"Privilege_endDate\",
				Person_IsFedLgot as \"Person_IsFedLgot\",
				Person_IsRegLgot as \"Person_IsRegLgot\",
				Person_Is7Noz as \"Person_Is7Noz\",
				PrivilegeType_Code as \"PrivilegeType_Code\",
				PrivilegeType_Name as \"PrivilegeType_Name\",
				Person_Surname as \"Person_Surname\",
				Person_Firname as \"Person_Firname\",
				Person_Secname as \"Person_Secname\",
				to_char(Person_Birthday, 'dd.mm.yyyy') AS \"Person_Birthday\",
				Person_Age as \"Person_Age\",
				PrivilegeType_id as \"PrivilegeType_id\"
				-- end select
			FROM
				-- from
				SignalInfoRegisterPrivilege
				-- end from
			where
				-- where
				(1=1)
				{$filters}
				-- end where
			order by
				-- order by
				\"Privilege_endDate\"  DESC
				-- end order by
		";

		//return $this->queryResult($query, $params);
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/**
	 * Получение списка ЛВН
	 */
	function loadEvnStick($data) {

		$filters = "";
		$params = array('Org_id' => $data['session']['org_id']);
		$filters .= "and SI.Org_id = :Org_id ";


		if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters .= "and SI.Lpu_id = :Lpu_id ";
		}

		if (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters .= "and SI.MedPersonal_id = :MedPersonal_id ";
		}

		$query = "
			SELECT
				-- select
				SI.Person_id as \"Person_id\",
				SI.PersonEvn_id as \"PersonEvn_id\",
				SI.Server_id as \"Server_id\",
				SI.EvnStickClass_Name as \"EvnStickClass_Name\",
				SI.EvnStickBase_Ser as \"EvnStickBase_Ser\",
				SI.EvnStickBase_Num as \"EvnStickBase_Num\",
				l.Lpu_Name as \"Lpu_Name\",
				SI.Person_Surname as \"Person_Surname\",
				SI.Person_Firname as \"Person_Firname\",
				SI.Person_Secname as \"Person_Secname\",
				to_char(SI.Person_Birthday, 'dd.mm.yyyy') AS \"Person_Birthday\",
				SI.Person_Age as \"Person_Age\",
				SI.MedPersonalFirst_Fio as \"MedPersonalFirst_Fio\",
				to_char(SI.EvnStickWorkRelease_begDate, 'dd.mm.yyyy') AS \"EvnStickWorkRelease_begDate\",
				to_char(SI.EvnStickWorkRelease_endDate, 'dd.mm.yyyy') AS \"EvnStickWorkRelease_endDate\",
				SI.EvnStickWorkRelease_DaysCount as \"EvnStickWorkRelease_DaysCount\"
				-- end select
			FROM
				-- from
				SignalInfoEvnStick SI
				left join v_lpu l on SI.lpu_id = l.lpu_id
				-- end from
			where
				-- where
				(1=1)
				{$filters}
				-- end where
			order by
				-- order by
				\"EvnStickWorkRelease_endDate\"  DESC
				-- end order by
		";

		//return $this->queryResult($query, $params);
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/**
	 * Получение списка Мед свид смерти
	 */
	function loadDeathSvid($data) {

		$filters = "";
		$params = array();

		if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters .= "and Lpu_id = :Lpu_id ";
		}

		if (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters .= "and MedPersonal_id = :MedPersonal_id ";
		}

		$query = "
			SELECT
				-- select
				Person_id as \"Person_id\",
				Person_FIO as \"Person_FIO\",
				to_char(Person_Birthday, 'dd.mm.yyyy') AS \"Person_Birthday\",
				to_char(DeathSvid_DeathDate, 'dd.mm.yyyy') AS \"DeathSvid_DeathDate\",
				dbo.Age2(Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				Lpu_Nick as \"Lpu_Nick\",
				Person_id as \"Person_id\"
				-- end select
			FROM
				-- from
				SignalInfoDeathSvid
				-- end from
			where
				-- where
				(1=1)
				{$filters}
				-- end where
			order by
				-- order by
				\"DeathSvid_DeathDate\"  DESC
				-- end order by
		";

		//return $this->queryResult($query, $params);
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/**
	 * Получение списка СМП
	 */
	function loadCmpCallCard($data) {

		$filters = "";
		$params = array();


		if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters .= "and Lpu_id = :Lpu_id ";
			$filters .= "and CCCLpu_id = :Lpu_id ";
		}

		if (isset($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters .= "and MedPersonal_id = :MedPersonal_id ";
		}

		$diagFilter = getAccessRightsDiagFilter('Diag_Code');
		if (!empty($diagFilter)) {
			$filters .= " and $diagFilter";
		}

		$query = "
			SELECT
				-- select
				Person_id as \"Person_id\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				to_char(CmpCallCard_prmDate, 'dd.mm.yyyy') AS \"CmpCallCard_prmDate\",
				to_char(CmpCallCard_prmTime,'hh:mm:ss') AS \"CmpCallCard_prmTime\"
				,LpuBuilding_Name as \"LpuBuilding_Name\"
				,Person_Surname as \"Person_Surname\"
				,Person_Firname as \"Person_Firname\"
				,Person_Secname as \"Person_Secname\"
				,CmpReason_Name as \"CmpReason_Name\"
				,to_char(Person_Birthday, 'dd.mm.yyyy') AS \"Person_Birthday\"
				,Person_Age as \"Person_Age\"
				,CmpLpu_Name as \"CmpLpu_Name\"
				,CmpDiag_Name as \"CmpDiag_Name\"
				,Diag_Code as \"Diag_Code\"
				-- end select
			FROM
				-- from
				SignalInfoCmpCallCard
				-- end from
			where
				-- where
				(1=1)
				{$filters}
				-- end where
			order by
				-- order by
				CmpCallCard_prmDate  DESC
				-- end order by
		";

		//return $this->queryResult($query, $params);
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/**
	 * Список пациентов записанных к текущему врачу, которым необходимо пройти ДВН 1 этап
	 */
function loadListByDayDisp($data) {

		$filters = "";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		);

		$query = "
			-- addit with
            with cte as (
			   select dbo.tzGetDate() as curDT,
			   cast(dbo.tzGetDate() as date) as curDate,
			   cast(date_part('year' , dbo.tzGetDate())|| '-12-31' as date) as PersonDopDisp_YearEndDate
			),
			EvnPLDispDop13Top as (
				select
						EvnPLDispDop13.EvnPLDispDop13_id,
						EvnPLDispDop13.EvnPLDispDop13_consDT,
						PS.Person_id,
						RTRIM(PS.Person_Surname) as Person_Surname,
						RTRIM(PS.Person_Firname) as Person_Firname,
						RTRIM(PS.Person_Secname) as Person_Secname,
						PS.Person_Birthday

				from v_PersonState PS
				    left join v_EvnPLDispDop13 EvnPLDispDop13  on PS.Person_id = EvnPLDispDop13.Person_id
				    and EvnPLDispDop13.Lpu_id = :Lpu_id and
				    COALESCE(EvnPLDispDop13.DispClass_id,1) = 1
				    and date_part('year',EvnPLDispDop13.EvnPLDispDop13_consDT) = date_part('year',(select curDT from cte))

				where
					(1=1) and exists (
						select
							personcard_id
						from
							v_PersonCard PC
							left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
							left join v_LpuRegion LR_Fap  on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
							left join v_MedStaffRegion MedStaffRegion on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
						WHERE
							PC.Person_id = PS.Person_id and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as timestamp) > (select curDT from cte))
							and MedStaffRegion.MedPersonal_id = :MedPersonal_id
							and PC.Lpu_id = :Lpu_id and PC.LpuAttachType_id = '1'
						limit 1
					)
                    and (PS.Person_deadDT >= cast ( date_part('year',(select curDT from cte)) || '-01-01' as date)
                                        OR PS.Person_deadDT IS NULL)
					and EvnPLDispDop13.EvnPLDispDop13_disDate is null -- дата окончания не заполнено
					and (
                        EvnPLDispDop13.EvnPLDispDop13_id is not null OR
                            (
                                (
				                (dbo.Age2(PS.Person_BirthDay, (select PersonDopDisp_YearEndDate from cte)) - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, (select PersonDopDisp_YearEndDate from cte)) - 21) % 3 = 0)
				                 or (exists (select PersonPrivilegeWOW_id from v_PersonPrivilegeWOW where Person_id = PS.Person_id limit 1)
                                and dbo.Age2(PS.Person_BirthDay, (select PersonDopDisp_YearEndDate from cte)) >= 18)
				                or
				                (PS.Sex_id = 1 and dbo.Age2(PS.Person_BirthDay, (select PersonDopDisp_YearEndDate from cte)) between 49 and 73 and dbo.Age2(PS.Person_BirthDay, (select PersonDopDisp_YearEndDate from cte)) % 2 = 1)
				                or
				                (PS.Sex_id = 2 and dbo.Age2(PS.Person_BirthDay, (select PersonDopDisp_YearEndDate from cte)) between 48 and 73)

			                    )
			            and dbo.Age2(PS.Person_BirthDay, (select PersonDopDisp_YearEndDate from cte)) <= 999
		                    )
                        )
			    )
			-- end addit with

			SELECT
				-- select
				EPLDD13.Person_id as \"Person_id\",
				EPLDD13.Person_Surname as \"Person_Surname\",
				EPLDD13.Person_Firname as \"Person_Firname\",
				EPLDD13.Person_Secname as \"Person_Secname\",
				to_char(EPLDD13.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthD\",
				dbo.Age2(EPLDD13.Person_BirthDay, (select curDT from cte)) as \"Person_Age\",
				--LpuAttach.MedPersonal_id,
				to_char(EPLDD13.EvnPLDispDop13_consDT, 'dd.mm.yyyy') as \"EvnPLDispDop13_setDate\"
				-- end select
			FROM
				-- from
				EvnPLDispDop13Top EPLDD13
				left join v_TimetableGraf_lite ttg  on ttg.Person_id = EPLDD13.Person_id
				left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				-- end from
			where
				-- where
				(1=1)
				and MSF.MedPersonal_id = :MedPersonal_id
				and (cast(ttg.TimetableGraf_factTime as date) >= (select curDate from cte) and cast(ttg.TimetableGraf_factTime as date) <= (select curDate from cte)
					or cast(ttg.TimetableGraf_begTime as date) >= (select curDate from cte) and cast(ttg.TimetableGraf_begTime as date) <= (select curDate from cte)
					or cast(ttg.TimetableGraf_insDT as date) >= (select curDate from cte) and cast(ttg.TimetableGraf_insDT as date) <= (select curDate from cte))

				-- end where
			order by
				-- order by
				EPLDD13.Person_Surname,
				EPLDD13.Person_Firname,
				EPLDD13.Person_Secname
				-- end order by
		";

      
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/**
	 * Список пациентов записанных к текущему врачу, которым необходимо пройти ДВН 2 этап
	 */
function loadListByDayDisp2($data) {

		$filters = "";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		);

		$query = "
			-- addit with
            WITH CTE AS(
                SELECT dbo.tzGetDate() AS curDT,
                        dbo.tzGetDate() AS curDate,
                        date_part('year',dbo.tzGetDate()) || '-12-31' AS PersonDopDisp_YearEndDate
           )
           -- end addit with
           SELECT
				-- select
				PS.Person_id as \"Person_id\",
				PS.Person_Surname as \"Person_Surname\",
				PS.Person_Firname as \"Person_Firname\",
				PS.Person_Secname as \"Person_Secname\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthD\",
				dbo.Age2(PS.Person_BirthDay, (select curDT from cte)) as \"Person_Age\",
				MSF.MedPersonal_id as \"MedPersonal_id\",
				--LpuAttach.MedPersonal_id,
				to_char(DopDispSecond.EvnPLDispDop13_consDT, 'dd.mm.yyyy') as \"EvnPLDispDop13_setDate\"
				-- end select
			FROM
				-- from
				v_TimetableGraf_lite ttg
				left join v_PersonState PS  on PS.Person_id = ttg.Person_id
				inner join v_EvnPLDispDop13 EPLDD13  on ttg.Person_id = EPLDD13.Person_id and EPLDD13.Lpu_id = :Lpu_id
				and COALESCE(EPLDD13.DispClass_id,1) = 1
				and date_part('year',EPLDD13.EvnPLDispDop13_consDT) = date_part('year',(select curDT from cte))
				and COALESCE(EPLDD13.EvnPLDispDop13_IsTwoStage, 1) = '2'
				left join v_EvnPLDispDop13  DopDispSecond on (DopDispSecond.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id)
				left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				-- end from
			where
				-- where
				(1=1)
				and MSF.MedPersonal_id = :MedPersonal_id
				and (cast(ttg.TimetableGraf_factTime as date) >= (select curDate from cte) and cast(ttg.TimetableGraf_factTime as date) <= (select curDate from cte)
					or cast(ttg.TimetableGraf_begTime as date) >= (select curDate from cte) and cast(ttg.TimetableGraf_begTime as date) <= (select curDate from cte)
					or cast(ttg.TimetableGraf_insDT as date) >= (select curDate from cte) and cast(ttg.TimetableGraf_insDT as date) <= (select curDate from cte))
				and PS.Person_id is not null
				and DopDispSecond.EvnPLDispDop13_disDate is null
				and exists (select  personcard_id from v_PersonCard PC 
				left join v_LpuRegion LR  on LR.LpuRegion_id = PC.LpuRegion_id
				left join v_LpuRegion LR_Fap  on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
				left join v_MedStaffRegion MedStaffRegion  on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
				WHERE PC.Person_id = PS.Person_id
				and MedStaffRegion.MedPersonal_id = :MedPersonal_id
				and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as timestamp) > (select curDT from cte))
				and PC.Lpu_id = :Lpu_id and PC.LpuAttachType_id = '1'
                limit 1
                )
				-- end where
			order by
				-- order by
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName
				-- end order by";
      
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
}

	/**
	 * Список неявившихся
	 */
	function loadPersonNoVisit($data) {

		$filters = "";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'begDate' => $data['begDate']
		);

		$query = "
			-- addit with
				with mv as (
					select
						dbo.tzgetdate() as curDT
				)
			-- end addit with
			SELECT 
				-- select
				Person.Server_id as \"Server_id\",
				Person.Person_id as \"Person_id\",
				lpued.Lpu_Nick as \"LpuED_Nick\",
				to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\", --Дата направления
				TTG.TimetableGraf_updDT as \"TimetableGraf_updDT\", --Дата записи
				to_char(TTG.TimeTableGraf_begTime, 'dd.mm.yyyy hh24:mi:ss') as \"TimeTableGraf_begTime\",
				Person.Person_surName as \"Person_surName\",
				Person.Person_firName as \"Person_firName\",
				Person.Person_secName as \"Person_secName\",
				to_char(Person.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				dbo.Age2(Person.Person_BirthDay, (select curDTfrom mv)) as \"Person_Age\",
				--Lpu_MSF.Lpu_Nick, -- МО врача
				LS_MSF.LpuSection_Name as \"LpuSection_Name\",
				LS_MSF.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				MSF.Person_Fio vrach as \"Person_Fio vrach\",
				MSF.MedPersonal_id as \"MedPersonal_id\",
				case when ED.EvnDirection_id is null then 'НЕТ' else 'ДА' end as \"direction\",
				l.Lpu_Nick, -- Мо прикрепления
				adr.Address_Address as \"Address_Address\",
				Person.Person_Phone as \"Person_Phone\"
				-- end select
			FROM 
				-- from
				v_TimeTableGraf TTG
				join v_MedStaffFact MSF on MSF.MedStaffFact_id = TTG.MedStaffFact_id
				join v_Lpu Lpu_MSF on Lpu_MSF.Lpu_id = MSF.Lpu_id
				--join v_Lpu ED on Lpu_MSF.Lpu_id = MSF.Lpu_id
				join v_LpuSection LS_MSF on LS_MSF.LpuSection_id = MSF.LpuSection_id
				join v_LpuSectionProfile LSProfile on LSProfile.LpuSectionProfile_id = LS_MSF.LpuSectionProfile_id
				left join v_EvnDirection ED on ED.EvnDirection_id = TTG.EvnDirection_id
				left join lateral(
					select
						Vizit.EvnVizitPL_id,
						vizit.MedStaffFact_id
					from
						v_EvnVizitPL vizit
					where
						vizit.Person_id = TTG.Person_id
						and vizit.Lpu_id = MSF.Lpu_id
						and vizit.MedPErsonal_id = MSF.MedPersonal_id
						and vizit.LpuSection_id = MSF.LpuSection_id
						and cast(vizit.EvnVizitPL_setDate as date) = cast(:begDate as date)
					limit 1
				)Vizit on true
				left join v_PersonState Person on Person.Person_id = TTG.Person_id
				left join v_Lpu l on l.Lpu_id = Person.Lpu_id
				left join v_Lpu lpued on lpued.Lpu_id = ED.Lpu_id
				left join v_Address adr on adr.Address_id = Person.PAddress_id
				-- end from
			where 
				-- where
				(1=1)
				and MSF.MedStaffFact_id = :MedStaffFact_id
				and cast(TTG.TimeTableGraf_begTime as date) = cast(:begDate as date)
				and Lpu_MSF.Lpu_id = :Lpu_id
				and TTG.Person_id is not null
				and Vizit.EvnVizitPL_id is null
				--and ED.EvnDirection_id is not null
				-- end where
			order by 
				-- order by
				Person.Person_Surname,
				Person.Person_Firname,
				Person.Person_Secname
				-- end order by
				";
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/**
	 * Список направлений на удаленную консультацию
	 */
	function loadCdk($data) {

		$where = "";
		$params = [];

		if (!empty($data['Lpu_id'])) {
			$where .= ' and ed.Lpu_id = :Lpu_id';
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['MedStaffFact_id'])) {
			$where .= ' and ed.MedStaffFact_id = :MedStaffFact_id';
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if (!empty($data['EvnDirection_setDate'])) {
			$where .= ' and ed.EvnDirection_setDate = :EvnDirection_setDate';
			$params['EvnDirection_setDate'] = $data['EvnDirection_setDate'];
		}

		$query = "
			-- addit with
				WITH CTE AS( SELECT dbo.tzGetDate() AS curDT )
			-- end addit with
			select
			-- select
				ed.EvnDirection_id as \"EvnDirection_id\",
				ps.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Person_FirName as \"Person_FirName\",
				to_char(ps.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				to_char(evpl.EvnVizitPL_setDate, 'dd.mm.yyyy') || ' ' || to_char(evpl.EvnVizitPL_setTime, 'hh24:mi') as \"EvnVizitPL_setDT\",
				dbo.Age2(PS.Person_BirthDay, (select curDT from cte)) as \"Person_Age\",
				to_char(ed.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				ed.Lpu_did as \"Lpu_did\",
				lpuDid.Lpu_Name as \"LpuDid_Nick\",
				ed.RemoteConsultCause_id as \"RemoteConsultCause_id\",
				rcc.RemoteConsultCause_Name as \"RemoteConsultCause_Name\",
				ed.ConsultationForm_id as \"ConsultationForm_id\",
				cf.ConsultationForm_Name as \"ConsultationForm_Name\",
				ed.EvnDirection_IsCito as \"EvnDirection_IsCito\",
				ed.Diag_id as \"Diag_id\",
				EDdiag.Diag_Code || ' ' || EDdiag.Diag_Name as \"EDDiag_Name\",
				ed.MedPersonal_id as \"MedPersonal_id\",
				to_char(eut.EvnUslugaTelemed_setDate, 'dd.mm.yyyy') || ' ' || to_char(eut.EvnUslugaTelemed_setTime, 'hh24:mi') as \"EvnUslugaTelemed_setDT\",
				UTT.UslugaTelemedResultType_Name as \"UslugaTelemedResultType_Name\",
				EUTdiag.Diag_Code || ' ' || EUTdiag.Diag_Name as \"EUTDiag_Name\"
			-- end select
			from
			-- from
				dbo.v_EvnDirection ed 
				left join dbo.v_PersonState ps on ps.Person_id = ed.Person_id
				left join dbo.v_Lpu lpuDid on lpuDid.Lpu_id = ed.Lpu_did
				left join dbo.v_ConsultationForm cf on cf.ConsultationForm_id = ed.ConsultationForm_id
				left join dbo.v_Diag EDdiag on EDdiag.Diag_id = ED.Diag_id
				left join dbo.v_RemoteConsultCause RCC on RCC.RemoteConsultCause_id = ed.RemoteConsultCause_id
				left join dbo.v_EvnUslugaTelemed EUT on EUT.EvnDirection_id = ed.EvnDirection_id
				left join dbo.v_Diag EUTdiag on EUTdiag.Diag_id = eut.Diag_id
				left join dbo.v_UslugaTelemedResultType UTT on UTT.UslugaTelemedResultType_id = EUT.UslugaTelemedResultType_id
				inner join dbo.v_DirType DT on DT.DirType_id = ED.DirType_id and DT.Dirtype_Code = 13
				left join dbo.v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = ed.EvnDirection_pid 
			-- end from
			
			where
				-- where
				(1=1)
				{$where}
				-- end where
			order by 
				-- order by
				ps.Person_Surname,
				ps.Person_Firname,
				ps.Person_Secname
				-- end order by
				";
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}
	/**
	 * Проставление признака просмотра ЭМК для выполненных направлений
	 */
	function setIsBrowsed($data)
	{
		$result = $this->swUpdate('EvnDirection', array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnDirection_isBrowsed' => 1,
		), false);
		if (empty($result) || false == is_array($result)) {
			throw new Exception('Ошибка запроса к БД', 500);
		}
		if (false == empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg'], 500);
		}
		return $this->isSuccessful($result);
	}

	/**
	 * Список беременных у которых Не проведена консультация
	 */
	function loadPregnancyRouteNotConsultation($data)
	{
		$filters = "";
		$filtersMedPersonal = "";
		$join = "";
		$params = array(
			'Lpu_iid' => $data['Lpu_iid'],
			'MedPersonal_iid' => $data['MedPersonal_iid'],
			'Trimester' => $data['Trimester']
		);
		if (!empty($data['MedPersonal_iid'])) {
			$MedPersonal_iids = explode(',', $data['MedPersonal_iid']);
			$filtersMedPersonal .= " and PR.MedPersonal_iid in (" . implode(',', $MedPersonal_iids) . ")";
		}
		if ($data['Trimester'] == 'Trimester1') {
			$filters .= "and Period.Value between 1 and 12";
		} else if ($data['Trimester'] == 'Trimester2') {
			$filters .= "and Period.Value between 13 and 27";
		} else if ($data['Trimester'] == 'Trimester3') {
			$filters .= "and Period.Value between 28 and 43";
		} else {
			$filters .= "";
		}
		$query = "
				with PersonRegister (
					PersonRegister_id
				) as (
					select PR.PersonRegister_id
					from dbo.v_PersonRegister PR 
					inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
						and PRT.PersonRegisterType_SysNick like 'pregnancy'
					where (1=1)
					and PR.PersonRegisterOutCause_id is null -- не исключен из регистра
					and PR.PregnancyResult_id is null -- нет исхода беременности
					and PR.PersonRegister_setDate is not null
					{$filtersMedPersonal}
					and PR.Lpu_iid = :Lpu_iid
				)
				select
					-- select
					PR.PersonRegister_id as \"PersonRegister_id\",
					PR.PersonRegister_setDate as \"PersonRegister_setDate\",
					PR.Person_id as \"Person_id\",
					PR.Lpu_iid as \"Lpu_iid\",
					PR.MedPersonal_iid as \"MedPersonal_iid\",
					dbo.Age2(PS.Person_BirthDay, tzgetdate()::date) as \"Person_Age\",
					PR.PersonRegister_Code as \"PersonRegister_Code\",
					PP.PersonPregnancy_id as \"PersonPregnancy_id\",
					coalesce(rtrim(PS.Person_SurName),'')||coalesce(' '||rtrim(PS.Person_FirName),'')||coalesce(' '||rtrim(PS.Person_SecName),'') as \"Person_Fio\",
					to_char(PS.Person_BirthDay,  'DD.MM.YYYY') as \"Person_BirthDay\",
					coalesce(PP.PersonPregnancy_RiskDPP, 0) + coalesce(LastScreen.PregnancyScreen_RiskPerPat, 0) as \"PersonPregnancy_ObRisk\",
					Period.Value as \"PersonPregnancy_Period\",
					(case when Period.Value between 1 and 12  then '1 триместр'
					when Period.Value between 13 and 27 then '2 триместр'
					when Period.Value between 28 and 43 then '3 триместр' end) as \"Trimester\",
					dbo.GetPregnancyRoute(PR.PersonRegister_id, 1, 0) as \"lstfactorrisk\",
					RKT.RiskType_Name as \"RiskType_AName\",
					EPS_LPU.Lpu_Nick as \"NickHospital\", -- МО госпитализации
					to_char(EvnVizitPL_Date.EvnVizitPL_setDate,  'DD.MM.YYYY') as \"EvnVizitPL_setDate\",
					to_char(PP.PersonPregnancy_birthDate,  'DD.MM.YYYY') as \"PersonPregnancy_birthDate\",
					ML.MesLevel_Name as \"MesLevel_Name\"
					-- end select
				from
					-- from
					PersonRegister PReg
					left join v_PersonRegister PR on PReg.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonState PS on PS.Person_id = PR.Person_id
					left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
					left join lateral (
						select
							MedPersonal_id,
							Person_Fio as MedPersonal_Fio
						from v_MedPersonal
						where MedPersonal_id = PR.MedPersonal_iid and Lpu_id = coalesce(L.Lpu_id, Lpu_id)
					) MP on true
					left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
					left join lateral (
						select Screen.*
						from v_PregnancyScreen Screen
						where Screen.PersonRegister_id = PR.PersonRegister_id
						order by Screen.PregnancyScreen_setDT desc
						limit 1
					) LastScreen on true
					left join lateral (
						select PQ.PregnancyQuestion_AnswerInt as Value
						from v_PregnancyQuestion PQ
						inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
						where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
						and QT.QuestionType_Code in (358,359,362,363)
						order by QT.QuestionType_Code
						limit 1
					) LastScreenPeriod on true
					left join lateral (
						select coalesce(
							BSS.BirthSpecStac_OutcomPeriod,
							LastScreenPeriod.Value + datediff('week', LastScreen.PregnancyScreen_setDT, tzgetdate()::date),
							PP.PersonPregnancy_Period + datediff('week', PP.PersonPregnancy_setDT, tzgetdate()::date)
						) as Value
					) Period on true
					left join lateral (
						select EvnVizitPL_setDate
						from v_EvnVizitPL DT
						where DT.Person_id = PR.Person_id 
						and DT.MedPersonal_id = PR.MedPersonal_iid
						order by DT.EvnVizitPL_setDate DESC
						limit 1
					) EvnVizitPL_Date on true
					left join v_RiskType RKT on RKT.RiskType_id = PR.RiskType_aid
					left join v_MesLevel ML on ML.MesLevel_id = PR.MesLevel_id
					left join lateral (
						select EPS_LPU.Lpu_Nick
						from v_EvnPS EPS
						left join v_Lpu_all EPS_LPU on EPS_LPU.Lpu_id = EPS.Lpu_id
						where EPS.Person_id = PR.Person_id and EPS.EvnPS_disDate is null
						order by EPS.EvnPS_setDate desc
						limit 1
					) EPS_LPU on true
					{$join}
					-- end from
				where
					-- where
					(1=1)
					and dbo.GetPregnancyRouteNoConsultation(PR.PersonRegister_id, PR.Person_id) ilike '%0%'
					{$filters}
					-- end where
				order by
					-- order by
					PR.PersonRegister_setDate desc,
					PR.PersonRegister_disDate desc
					-- end order by
			";
		
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');

		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}
	/*
	* Список беременных которые на госпитализации/выписанные/Изменилась группа риска
	*/
	function loadPregnancyRouteHospital($data)
	{
		$filters = "";
		$filterMedPersonal = "";
		$filterHosp  = "";
		$join = "";
		$order = "";
		$params = array(
			'Lpu_iid' => $data['Lpu_iid'],
			'MedPersonal_iid' => $data['MedPersonal_iid'],
			'Date_Range_0' => isset($data['Date_Range'][0]) ? $data['Date_Range'][0] : null,
			'Date_Range_1' => isset($data['Date_Range'][1]) ? $data['Date_Range'][1] : null,
			'Trimester' => $data['Trimester'],
			'PregnancyRouteType'  => $data['PregnancyRouteType']
		);


		if(isset($data['PregnancyRouteType']) && $data['PregnancyRouteType'] == 'Hospital'){
			if ($data['Trimester'] == 'Trimester1') {
				$filters .= "and Period.Value between 1 and 12";
			} else if ($data['Trimester'] == 'Trimester2') {
				$filters .= "and Period.Value between 13 and 27";
			} else if ($data['Trimester'] == 'Trimester3') {
				$filters .= "and Period.Value between 28 and 43";
			} else {
				$filters .= "";
			}
			$filters .= " and PR.PersonRegister_setDate <= EPS.EvnPS_setDate 
						and EPS.EvnPS_setDate is not null 
						and EPS.EvnPS_disDate is null ";
			$order .= " EPS.EvnPS_setDate desc ";
		} else if (isset($data['PregnancyRouteType']) && $data['PregnancyRouteType'] == 'DisHospital') {
			$filters .= " and (cast(EPS.EvnPS_disDate as date) >= cast(:Date_Range_0 as date) and cast(EPS.EvnPS_disDate  as date) <= cast(:Date_Range_1 as date)) ";
			$order .= "
			PS.Person_SurName,
			PS.Person_FirName,
			PS.Person_SecName,
			EPS.EvnPS_disDate desc
			";
		} else {
			//Изменилась группа риска
			$filters .= " and PR.PersonRegister_HighRiskDT >= dateadd('DAY', -7, tzgetdate()::date) or PR.PersonRegister_ModerateRiskDT >= dateadd('DAY', -7, tzgetdate()::date)";
			$filterHosp .= " and EPS.EvnPS_disDate is null";
			$order .= " PR.PersonRegister_setDate desc ";
		}

		if (!empty($data['MedPersonal_iid'])) {
			$MedPersonal_iids = explode(',', $data['MedPersonal_iid']);

			$filterMedPersonal = " and PR.MedPersonal_iid in (" . implode(',', $MedPersonal_iids) . ")";
		}

		$query = "
			select
				-- select
				PR.PersonRegister_id as \"PersonRegister_id\",
				PR.Person_id as \"Person_id\",
				PR.Lpu_iid as \"Lpu_iid\",
				PR.MedPersonal_iid as \"MedPersonal_iid\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				PP.PersonPregnancy_id as \"PersonPregnancy_id\",
				coalesce(rtrim(PS.Person_SurName),'')||coalesce(' '||rtrim(PS.Person_FirName),'')||coalesce(' '||rtrim(PS.Person_SecName),'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_BirthDay, tzgetdate()::date) as \"Person_Age\",
				coalesce(PP.PersonPregnancy_RiskDPP, 0) + coalesce(LastScreen.PregnancyScreen_RiskPerPat, 0) as \"PersonPregnancy_ObRisk\",
				Period.Value as \"PersonPregnancy_Period\",
				(case when Period.Value between 1 and 12  then '1 триместр'
				when Period.Value between 13 and 27 then '2 триместр'
				when Period.Value between 28 and 43 then '3 триместр' end) as \"Trimester\",
				dbo.GetPregnancyRoute(PR.PersonRegister_id, 1, 0) as \"lstfactorrisk\",
				RKT.RiskType_Name as \"RiskType_AName\",
				EPS.Lpu_Nick as \"NickHospital\", -- МО госпитализации
				EPS.LpuSectionProfile_Name as \"ProfilHospital\", -- Профиль госп
				EPS.LpuUnitType_Name as \"LpuUnitType_Name\",
				EPS.diag_FullName as \"diag_FullName\",
				to_char(EPS.EvnPS_setDate, 'DD.MM.YYYY') as \"EvnPS_setDate\",
				to_char(EPS.EvnPS_disDate, 'DD.MM.YYYY') as \"EvnPS_disDate\",
				to_char(EvnVizitPL_Date.EvnVizitPL_setDate,  'DD.MM.YYYY') as \"EvnVizitPL_setDate\",
				to_char(PP.PersonPregnancy_birthDate,  'DD.MM.YYYY') as \"PersonPregnancy_birthDate\",
				ML.MesLevel_Name as \"MesLevel_Name\"
				-- end select
			from
				-- from
				v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick like 'pregnancy' {$filterMedPersonal} and PR.Lpu_iid = :Lpu_iid
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonState PS on PS.Person_id = PR.Person_id
				left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
				left join lateral (
					select
						MedPersonal_id,
						Person_Fio as MedPersonal_Fio
					from v_MedPersonal
					where MedPersonal_id = PR.MedPersonal_iid and Lpu_id = coalesce(L.Lpu_id, Lpu_id)
					limit 1
				) MP on true
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
				left join lateral (
					select Screen.*
					from v_PregnancyScreen Screen
					where Screen.PersonRegister_id = PR.PersonRegister_id
					order by Screen.PregnancyScreen_setDT desc
					limit 1
				) LastScreen on true
				left join lateral (
					select PQ.PregnancyQuestion_AnswerInt as Value
					from v_PregnancyQuestion PQ
					inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and QT.QuestionType_Code in (358,359,362,363)
					order by QT.QuestionType_Code
					limit 1
				) LastScreenPeriod on true
				left join lateral (
					select coalesce(
						BSS.BirthSpecStac_OutcomPeriod,
						LastScreenPeriod.Value + datediff('week', LastScreen.PregnancyScreen_setDT, tzgetdate()::date),
						PP.PersonPregnancy_Period + datediff('week', PP.PersonPregnancy_setDT, tzgetdate()::date)
					) as Value
				) Period on true
				left join lateral (
					select EvnVizitPL_setDate
					from v_EvnVizitPL DT
					where DT.Person_id = PR.Person_id 
					and DT.MedPersonal_id = PR.MedPersonal_iid
					order by DT.EvnVizitPL_setDate DESC
					limit 1
				) EvnVizitPL_Date on true
				left join v_RiskType RKT on RKT.RiskType_id = PR.RiskType_aid
				left join v_MesLevel ML on ML.MesLevel_id = PR.MesLevel_id
				left join lateral (
					select EPS.EvnPS_id,EPS_LPU.Lpu_Nick,EPS_PROFILE.LpuSectionProfile_Name,EPS.EvnPS_setDate,EPS.EvnPS_disDate,D.diag_FullName,LUT.LpuUnitType_Name
					from v_EvnPS EPS  
						left join v_Diag D on D.Diag_id = EPS.Diag_id
						left join v_Lpu_all EPS_LPU  on EPS_LPU.Lpu_id = EPS.Lpu_id
						left join v_EvnSection ESS on ESS.EvnSection_pid=EPS.EvnPS_id
						left join v_LpuSection LS on LS.LpuSection_id = ESS.LpuSection_id
						left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
						left join v_LpuSectionProfile EPS_PROFILE  on EPS_PROFILE.LpuSectionProfile_id = ESS.LpuSectionProfile_id
					where EPS.Person_id = PR.Person_id {$filterHosp}
					order by EPS.EvnPS_setDate desc
					limit 1
				) EPS on true
				{$join}
				-- end from
			where
				-- where
				(1=1)
				{$filters}
				and PR.PersonRegisterOutCause_id is null -- не исключен из регистра
				and PR.PregnancyResult_id is null -- нет исхода беременности
				and PR.PersonRegister_setDate is not null
				-- end where
			order by
				-- order by
				{$order}
				-- end order by
			";
			//echo getDebugSQL($query, $data);exit;
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/*
	* Список беременных которые вызывали СМП за вчерашний день или за период до 11 дней
	*/
	function loadPregnancyRouteSMP($data)
	{
		$filters = "";
		$join = "";
		$order = "";
		$params = array(
			'Lpu_iid' => $data['Lpu_iid'],
			'MedPersonal_iid' => $data['MedPersonal_iid'],
			'CmpCallCard_prmDate_Range_0' => isset($data['CmpCallCard_prmDate_Range'][0]) ? $data['CmpCallCard_prmDate_Range'][0] : null,
			'CmpCallCard_prmDate_Range_1' => isset($data['CmpCallCard_prmDate_Range'][1]) ? $data['CmpCallCard_prmDate_Range'][1] : null,
			'Yesterday' => $data['Yesterday'],
		);
		if (isset($data['Yesterday']) && $data['Yesterday'] == 1) {
			$query = "
			select
				-- select
				SI.CmpCallCard_id as \"CmpCallCard_id\",
				SI.Person_id as \"Person_id\",
				SI.LpuBuilding_Name as \"LpuBuilding_Name\",
				to_char(SI.CmpCallCard_prmDate, 'DD.MM.YYYY') as \"CmpCallCard_prmDate\",
				to_char(SI.CmpCallCard_prmTime, 'HH24:MI') as \"CmpCallCard_prmTime\",
				SI.Person_Surname as \"Person_Surname\",
				SI.Person_Firname as \"Person_Firname\",
				SI.Person_Secname as \"Person_Secname\",
				to_char(SI.Person_Birthday, 'HH24:MI') as \"Person_Birthday\",
				SI.Person_Age as \"Person_Age\",
				SI.CmpLpu_Name as \"CmpLpu_Name\",
				SI.CmpDiag_Name as \"CmpDiag_Name\",
				to_char(EvnVizitPL_Date.EvnVizitPL_setDate,  'DD.MM.YYYY') as \"EvnVizitPL_setDate\",
				to_char(PP.PersonPregnancy_birthDate,  'DD.MM.YYYY') as \"PersonPregnancy_birthDate\",
				ML.MesLevel_Name as \"MesLevel_Name\"
				-- end select
			from
				-- from
				dbo.SignalInfoCmpCallCard SI
				left join v_PersonRegister PR on PR.Person_id = SI.Person_id
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick like 'pregnancy'
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_MesLevel ML on ML.MesLevel_id = PR.MesLevel_id
				left join lateral (
					select EvnVizitPL_setDate
					from v_EvnVizitPL DT
					where DT.Person_id = PR.Person_id 
					and DT.MedPersonal_id = PR.MedPersonal_iid
					order by DT.EvnVizitPL_setDate DESC
					limit 1
				) EvnVizitPL_Date on true
				-- end from
			where
				-- where
				(1=1)
				and PR.Lpu_iid = :Lpu_iid
				and PR.MedPersonal_iid in (:MedPersonal_iid) 
				and PR.PersonRegisterOutCause_id is null -- не исключен из регистра
				and PR.PregnancyResult_id is null -- нет исхода беременности
				and PR.PersonRegister_setDate is not null
				-- end where
			order by
				-- order by
					SI.CmpCallCard_prmDate desc
				-- end order by
					";
		} else {
			$query = "
				select
					-- select
					CCC.CmpCallCard_id as \"CmpCallCard_id\",
					CCC.Person_id as \"Person_id\",
					LB.LpuBuilding_Name as \"LpuBuilding_Name\",
					to_char(CCC.CmpCallCard_prmDT, 'DD.MM.YYYY') as \"CmpCallCard_prmDate\",
					to_char(CCC.CmpCallCard_prmDT, 'HH24:MI') as \"CmpCallCard_prmTime\",
					RTRIM(COALESCE(CLC.Fam, PS.Person_Surname, CCC.Person_SurName)) as \"Person_Surname\",
					RTRIM(COALESCE(CLC.Name, PS.Person_Firname, CCC.Person_FirName)) as \"Person_Firname\",
					RTRIM(COALESCE(CLC.Middle, PS.Person_Secname, CCC.Person_SecName)) as \"Person_Secname\",
					to_char(COALESCE(PSCLC.Person_BirthDay,CCC.Person_BirthDay,PS.Person_BirthDay), 'DD.MM.YYYY') as \"Person_Birthday\",
					coalesce(dbo.Age2(PS.Person_BirthDay, tzgetdate()), CLC.Age) as \"Person_Age\",
					(case when CLC.CallPovod_id is not null then COALESCE(CR.CmpReason_Code || '. ', '') || CR.CmpReason_Name else COALESCE(CRTalon.CmpReason_Code || '. ', '') || CRTalon.CmpReason_Name end) as \"CmpReason_Name\",
					--RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\",
					case when (CCC.CmpCallCardInputType_id in (1,2)) then COALESCE(Lpu.Lpu_Nick, Lpu.Lpu_Name, '')
						else COALESCE(LpuHid.Lpu_Nick, LpuHid.Lpu_Name, '') end
					as \"CmpLpu_Name\",
					RTRIM(case when CLD.diag_FullName is not null then CLD.diag_FullName else CD.CmpDiag_Code end) as \"CmpDiag_Name\",
					to_char(EvnVizitPL_Date.EvnVizitPL_setDate,  'DD.MM.YYYY') as \"EvnVizitPL_setDate\",
					to_char(PP.PersonPregnancy_birthDate,  'DD.MM.YYYY') as \"PersonPregnancy_birthDate\",
					ML.MesLevel_Name as \"MesLevel_Name\"
					-- end select
				from
					-- from
					v_CmpCallCard CCC
					left join dbo.v_CmpCloseCard CLC on CLC.CmpCallCard_id = CCC.CmpCallCard_id
					left join CmpReason CR on CR.CmpReason_id = CLC.CallPovod_id
					left join CmpReason CRTalon on CRTalon.CmpReason_id = CCC.CmpReason_id
					left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
					left join v_Lpu L on L.Lpu_id = CL.Lpu_id
					left join v_Lpu Lpu on CCC.CmpLpu_id = Lpu.Lpu_id
					left join v_Lpu LpuHid on CCC.Lpu_hid = LpuHid.Lpu_id
					left join v_Diag CLD on CLD.Diag_id = CLC.Diag_id		
					left join v_CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
					left join v_PersonState PS on CCC.Person_id = PS.Person_id
					left join v_PersonState PSCLC on CLC.Person_id = PSCLC.Person_id
					left join LpuBuilding LB on LB.LpuBuilding_id = CLC.LpuBuilding_id
					left join v_PersonRegister PR on PR.Person_id = CCC.Person_id
					inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick like 'pregnancy'
					left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join lateral (
						select EvnVizitPL_setDate
						from v_EvnVizitPL DT
						where DT.Person_id = PR.Person_id 
						and DT.MedPersonal_id = PR.MedPersonal_iid
						order by DT.EvnVizitPL_setDate DESC
						limit 1
					) EvnVizitPL_Date on true
					left join v_MesLevel ML on ML.MesLevel_id = PR.MesLevel_id
					-- end from
				where
					-- where
					(1 = 1)
					and cast(CCC.CmpCallCard_prmDT as date) >= cast(:CmpCallCard_prmDate_Range_0 as date) 
					and cast(CCC.CmpCallCard_prmDT as date) <= cast(:CmpCallCard_prmDate_Range_1 as date)
					and PR.Lpu_iid = :Lpu_iid
					and PR.MedPersonal_iid in (:MedPersonal_iid) 
					and PR.PersonRegisterOutCause_id is null -- не исключен из регистра
					and PR.PregnancyResult_id is null -- нет исхода беременности
					and PR.PersonRegister_setDate is not null
					and (CLC.CmpCloseCard_id > 0)  
					and exists (select personcard_id from v_PersonCard PC
					WHERE PC.Person_id = PS.Person_id and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as timestamp) > tzgetdate()) limit 1)
					-- end where
				order by
					-- order by
					CCC.CmpCallCard_prmDT desc
					-- end order by";
		}
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/**
	 * Получение списка беременных для сигнальной информации в разбивке по МО и триместрам
	 */
	function loadTrimesterListMO($data) {
		$filters = "";
		$join = "";
		$params = array(
			'Type' => $data['Type']
		);
		if (isset($data['Type']) && $data['Type'] == 'notConsultationRegion') {
			$filters .= " and dbo.GetPregnancyRouteNoConsultation(PR.PersonRegister_id, PR.Person_id) like '%0%'";
		} else if (isset($data['Type']) && $data['Type'] == 'HospitalRegion') {
			$join .= "left join lateral (
						select EPS.EvnPS_id,EPS_LPU.Lpu_Nick,EPS_PROFILE.LpuSectionProfile_Name,EPS.EvnPS_setDate,EPS.EvnPS_disDate,D.diag_FullName,LUT.LpuUnitType_Name
						from v_EvnPS EPS  
							left join v_Diag D on D.Diag_id = EPS.Diag_id
							left join v_Lpu_all EPS_LPU  on EPS_LPU.Lpu_id = EPS.Lpu_id
							left join v_EvnSection ESS on ESS.EvnSection_pid=EPS.EvnPS_id
							left join v_LpuSection LS on LS.LpuSection_id = ESS.LpuSection_id
							left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
							left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
							left join v_LpuSectionProfile EPS_PROFILE  on EPS_PROFILE.LpuSectionProfile_id = ESS.LpuSectionProfile_id
						where EPS.Person_id = PR.Person_id --and EPS.EvnPS_disDate is null
						order by EPS.EvnPS_setDate desc
						limit 1
					) EPS on true";
			$filters .= " and EPS.EvnPS_disDate is null 
						and PR.PersonRegister_setDate <= EPS.EvnPS_setDate 
						and EPS.EvnPS_setDate is not null ";
		} else {
			$join .= "--дата последнего посещения
						left join lateral (
						select EvnVizitPL_setDate
						from v_EvnVizitPL DT
						where DT.Person_id = PR.Person_id 
						and DT.Lpu_id = PR.Lpu_iid -- смотрим посещения по лпу учета
						order by DT.EvnVizitPL_setDate DESC
						limit 1
						) EvnVizitPL_Date on true
						--разница между последней датой и сегодняшней
						left join lateral (
						SELECT DATEDIFF('day',EvnVizitPL_Date.EvnVizitPL_setDate,GETDATE()) AS DiffDate
						) DiffDate on true";
			$filters .= " and ((case when ((Period.Value between 1 and 12) and DiffDate.DiffDate>35) then 1 end=1) 
						or (case when ((Period.Value between 13 and 27) and DiffDate.DiffDate>35) then 1 end=1)
						or (case when ((Period.Value between 28 and 43) and DiffDate.DiffDate>22) then 1 end=1))";
		}
		$query = "
			with PersonRegister (
				PersonRegister_id
			) as (
				select PR.PersonRegister_id
					from dbo.v_PersonRegister PR
					inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
						and PRT.PersonRegisterType_SysNick like 'pregnancy'
					where (1=1)
					and PR.PersonRegisterOutCause_id is null -- не исключен из регистра
					and PR.PregnancyResult_id is null -- нет исхода беременности
					and PR.PersonRegister_setDate is not null
			)
			select
				-- select
				PR.Lpu_iid as \"Lpu_iid\",
				L.Lpu_Nick as \"Lpu_Nick\",
				count ( case when Period.Value between 1 and 12  then '1 триместр' end ) as \"Trimester1\",
				count ( case when Period.Value between 13 and 27 then '2 триместр' end ) as \"Trimester2\",
				count ( case when Period.Value between 28 and 43 then '3 триместр' end ) as \"Trimester3\"
				-- end select
			from
				-- from
				PersonRegister PReg 
				left join v_PersonRegister PR on PReg.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonPregnancy PP on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_Lpu_all L on L.Lpu_id = PR.Lpu_iid
				left join v_BirthSpecStac BSS on BSS.PersonRegister_id = PR.PersonRegister_id
				left join lateral (
					select Screen.*
					from v_PregnancyScreen Screen
					where Screen.PersonRegister_id = PR.PersonRegister_id
					order by Screen.PregnancyScreen_setDT desc
					limit 1
				) LastScreen on true
				left join lateral (
					select PQ.PregnancyQuestion_ValuesStr as Lpu_id
					from v_PregnancyQuestion PQ
					inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id and QT.QuestionType_Code = 406
					limit 1
				) LastScreenDir on true
				left join lateral (
					select PQ.PregnancyQuestion_AnswerInt as Value
					from v_PregnancyQuestion PQ
					inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and QT.QuestionType_Code in (358,359,362,363)
					order by QT.QuestionType_Code
					limit 1
				) LastScreenPeriod on true
				left join lateral (
					select coalesce(
						BSS.BirthSpecStac_OutcomPeriod,
						LastScreenPeriod.Value + datediff('week', LastScreen.PregnancyScreen_setDT, tzgetdate()::date),
						PP.PersonPregnancy_Period + datediff('week', PP.PersonPregnancy_setDT, tzgetdate()::date)
					) as Value
				) Period on true
				{$join}
				-- end from
			where
				-- where
				(1=1)
				{$filters}
			group by
				PR.Lpu_iid,
				L.Lpu_Nick
				-- end where
			ORDER BY
				--order by
				PR.Lpu_iid
				--end order by";

		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');

		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}
	/**
	 * Регистр БСК
	 */
	function loadListRegistBSK($data) {

		$filters = "";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'begDate' => date('Y-m-d', strtotime($data['BSKRegistry_setDateNext'][0])),
			'endDate' => date('Y-m-d', strtotime($data['BSKRegistry_setDateNext'][1]))
		);

		$query = "
			select
			--select
				PR.PersonRegister_id as \"PersonRegister_id\",
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				PR.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				coalesce(rtrim(PS.Person_SurName),'')||coalesce(' '||rtrim(PS.Person_FirName),'')||coalesce(' '||rtrim(PS.Person_SecName),'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay,  'DD.MM.YYYY') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_BirthDay, tzgetdate()::date) as \"Person_Age\",
				MT.MorbusType_id as \"MorbusType_id\",
				MT.MorbusType_Name as \"MorbusType_Name\",
				case 
						when MT.MorbusType_id = 84 then cast(BSKRegistry.BSKRegistry_riskGroup as varchar(10))
						when MT.MorbusType_id = 89 then 
								(select RD.BSKRegistryData_data from dbo.BSKRegistryData RD 
								where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 269)
						when MT.MorbusType_id = 88 then 
								(select RD.BSKRegistryData_data from dbo.BSKRegistryData RD 
								where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 151)
						else NULL end as \"BSKRegistry_riskGroup\",
				BSKRegistry.BSKRegistry_setDate as \"BSKRegistry_setDate\",
				coalesce(to_char(BSKRegistry.BSKRegistry_nextDate, 'DD.MM.YYYY'),case 
					when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 1 then to_char((dateadd('MONTH', 18, BSKRegistry.BSKRegistry_setDate)), 'DD.MM.YYYY')
					when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 2 then to_char((dateadd('MONTH', 12, BSKRegistry.BSKRegistry_setDate)), 'DD.MM.YYYY')
					when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 3 then to_char((dateadd('MONTH', 6,BSKRegistry.BSKRegistry_setDate)), 'DD.MM.YYYY')
					when MT.MorbusType_id = 50 then to_char((dateadd('MONTH', 6, BSKRegistry.BSKRegistry_setDate)), 'DD.MM.YYYY')
					when MT.MorbusType_id = 89 then to_char((dateadd('MONTH', 6, BSKRegistry.BSKRegistry_setDate)), 'DD.MM.YYYY')
					when MT.MorbusType_id = 88 then to_char((dateadd('MONTH', 6, BSKRegistry.BSKRegistry_setDate)), 'DD.MM.YYYY')
					end) as \"BSKRegistry_setDateNext\",
				BSKRegistry.BSKRegistry_id as \"BSKRegistry_id\"
				--end select
			from 
			-- from
			PersonRegister PR
			left join dbo.MorbusType MT  on MT.MorbusType_id = PR.MorbusType_id
			left join v_PersonState PS on PS.Person_id = PR.Person_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id  and PC.LpuAttachType_id = '1' 
			left join lateral (
				select
					to_char(R.BSKRegistry_setDate, 'YYYY-MM-DD') as BSKRegistry_setDate,
					R.BSKRegistry_riskGroup,
					R.BSKRegistry_id,
					R.BSKRegistry_nextDate
				from dbo.BSKRegistry R 
				where R.MorbusType_id = MT.MorbusType_id and R.Person_id = PR.Person_id 
				order by R.BSKRegistry_setDate DESC
				limit 1
			) as BSKRegistry on true
			-- end from
			where
			--where
			(1=1)
			and PR.MorbusType_id in (84,88,89,50)
			and PC.LpuAttachType_id = '1'
			and PC.Lpu_id = :Lpu_id
			and cast((coalesce(BSKRegistry.BSKRegistry_nextDate,case 
								when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 1 then dateadd('MONTH', 18, BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 2 then dateadd('MONTH', 12, BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 3 then dateadd('MONTH', 6,BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 50 then dateadd('MONTH', 6, BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 89 then dateadd('MONTH', 6, BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 88 then dateadd('MONTH', 6, BSKRegistry.BSKRegistry_setDate)
								end)) as date) between cast(:begDate as date) and cast(:endDate as date)
			--end where 
				ORDER BY
			--order by
				\"BSKRegistry_setDateNext\" desc
			--end order by"
				;
		//echo getDebugSQL($query, $data);exit;
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}
	/**
	 * @param $data
	 */
	function loadCVI($data){
		$filters = "";
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		);

		$query = "
			SELECT 
				distinct
				-- select
				PQ.PersonQuarantine_id as \"PersonQuarantine_id\",
				PQ.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				coalesce(rtrim(PS.Person_SurName),'')||coalesce(' '||rtrim(PS.Person_FirName),'')||coalesce(' '||rtrim(PS.Person_SecName),'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay,  'DD.MM.YYYY') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_BirthDay, tzgetdate()) as \"Person_Age\",
				PS.Person_Phone as \"Person_Phone\",
				to_char(PQ.PersonQuarantine_begDT,  'DD.MM.YYYY') as \"PersonQuarantine_begDate\",
				PQOR.PersonQuarantineOpenReason_Name as \"PersonQuarantineOpenReason_Name\",
				to_char(PQ.PersonQuarantine_approveDT,  'DD.MM.YYYY') as \"PersonQuarantine_approveDate\",
				EXTRACT( DAY FROM COALESCE(PQ.personquarantine_enddt, tzgetdate()) -
						(case
							 when COALESCE(PQ.PersonQuarantineOpenReason_id,0) = 1 then ROO.RepositoryObserv_arrivalDate
							 when COALESCE(PQ.PersonQuarantineOpenReason_id,0) = 2 then ROO.RepositoryObesrv_contactDate
							 when COALESCE(PQ.PersonQuarantineOpenReason_id,0) = 3 then PQ.PersonQuarantine_approveDT end)
				)+1 as \"QuarantineDays\",
				EPS.Lpu_Nick as \"NickHospital\",
				RO.psign as \"psign\"
				-- end select
			FROM 
				-- from
				dbo.v_PersonQuarantine PQ 
				left join v_PersonState PS  on PQ.Person_id = PS.Person_id
				left join lateral (
					Select EPS_LPU.Lpu_Nick
					from v_EvnPS EPS 
						left join v_Lpu_all EPS_LPU on EPS_LPU.Lpu_id = EPS.Lpu_id
					where EPS.Person_id = PQ.Person_id and EPS.EvnPS_disDate is null
					order by EPS.EvnPS_setDate desc
					limit 1
				) as EPS on true
				left join v_RepositoryObserv ROO on ROO.PersonQuarantine_id = PQ.PersonQuarantine_id and COALESCE(ROO.RepositoryObesrv_IsFirstRecord,1) = 2
				left join lateral (
					select
					MedPersonal_id, case when RO.RepositoryObserv_IsRunnyNose = 2 or RO.RepositoryObserv_IsSoreThroat = 2 or RO.RepositoryObserv_IsHighTemperature = 2 or RO.Dyspnea_id > 1 or RO.Cough_id > 1 then 1 else 0 end as \"psign\"
					from dbo.v_RepositoryObserv RO 
					where (1=1) and RO.PersonQuarantine_id = PQ.PersonQuarantine_id
					order by RO.repositoryobserv_id desc
					limit 1
				) as RO on true
				left join v_PersonQuarantineOpenReason PQOR  on PQOR.PersonQuarantineOpenReason_id = PQ.PersonQuarantineOpenReason_id
				left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = PQ.MedStaffFact_id
				-- end from
			where 
				-- where
				(1=1)
				and PQ.PersonQuarantine_endDT IS NULL
				and MSF.MedPersonal_id = :MedPersonal_id or
				exists (select personcard_id 
					from v_PersonCard PC   
						left join v_MedStaffRegion MedStaffRegion  on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
						left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
					WHERE PC.Person_id = PQ.Person_id and PQ.PersonQuarantine_endDT IS NULL
						and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as timestamp) > tzgetdate())
						and MedStaffRegion.MedStaffRegion_isMain = 2
						and msf.MedPersonal_id = :MedPersonal_id
						and PC.LpuAttachType_id = 1
					limit 1
				)
				-- end where
			order by 
				-- order by
				\"QuarantineDays\" desc
				-- end order by
				";
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');

		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}
	/**
	 * @param $data
	 */
	function loadPersonDispInfo($data){
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'begDate' => date('Y-m-d', strtotime($data['PersonDispInfo_prmDate_Range'][0])),
			'endDate' => date('Y-m-d', strtotime($data['PersonDispInfo_prmDate_Range'][1]))
		);

		$query = "
		-- with
		with SignalInfoPersonDisp(
			PersonDispVizit_id,
			Person_id,
			PersonDisp_id,
			Diag_id,
			PersonDispVizit_NextDate
		) as (
			select
				oapdv.PersonDispVizit_id,
				PD.Person_id,
				PD.PersonDisp_id,
				PD.Diag_id,
				oapdv.PersonDispVizit_NextDate
			from dbo.v_PersonDisp PD
				left join dbo.v_PersonDispVizit oapdv on oapdv.PersonDisp_id = PD.PersonDisp_id and oapdv.PersonDispVizit_NextFactDate is null
				left join lateral(
					select 
						MP_L.MedPersonal_id as MedPersonal_id_last,
						MP_L.Person_Fio as MedPersonal_FIO_last
					from dbo.v_PersonDispHist PDH_L
					left join dbo.v_MedPersonal MP_L on MP_L.MedPersonal_id = PDH_L.MedPersonal_id
					where PDH_L.PersonDisp_id = PD.PersonDisp_id
					order by PDH_L.PersonDispHist_begDate desc
					limit 1	
				) mph_last on true

				left join lateral (
					select msf.MedPersonal_id
					from dbo.v_PersonCard_all PC 
					left join dbo.v_MedStaffRegion MedStaffRegion on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
					left join dbo.v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
					where PC.Person_id = PD.Person_id 
					and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as timestamp) > tzgetdate())
					and MedStaffRegion.MedStaffRegion_isMain = 2
					and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) as MedPersonal on true

			where (1=1)
			and PD.Lpu_id = :Lpu_id
			and ( PD.PersonDisp_endDate is null or PD.PersonDisp_endDate > tzgetdate())
			and COALESCE(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate) is not null
			and (mph_last.MedPersonal_id_last = :MedPersonal_id or MedPersonal.MedPersonal_id = :MedPersonal_id)
			and (cast(COALESCE(oapdv.PersonDispVizit_NextDate,PD.PersonDisp_NextDate) as date) >= :begDate and cast(COALESCE(oapdv.PersonDispVizit_NextDate,PD.PersonDisp_NextDate) as date) <= :endDate)
			)
			-- end with
			SELECT 
				-- select
				SIPD.PersonDisp_id as \"PersonDisp_id\",
				SIPD.PersonDispVizit_id as \"PersonDispVizit_id\",
				coalesce(rtrim(PS.Person_SurName),'')||coalesce(' '||rtrim(PS.Person_FirName),'')||coalesce(' '||rtrim(PS.Person_SecName),'') as \"Person_Fio\",
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				to_char(PS.Person_BirthDay,  'DD.MM.YYYY') as \"Person_BirthDay\",
				dbo.Age2(PS.Person_BirthDay, tzgetdate()) as \"Person_Age\",
				to_char(PD.PersonDisp_begDate, 'DD.MM.YYYY') as \"PersonDisp_begDate\", -- взят
				to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY') as \"PersonDisp_endDate\", -- снят
				dg1.Diag_FullName as \"Diag_FullName\",
				to_char(coalesce(SIPD.PersonDispVizit_NextDate, PD.PersonDisp_NextDate), 'DD.MM.YYYY') as \"PersonDisp_NextDate\", -- дата след явки /дата планового осмотра
				PS.Person_Phone as \"Person_Phone\"
				-- end select
			FROM 
				-- from
				SignalInfoPersonDisp SIPD
				left join dbo.v_PersonDisp PD  on SIPD.PersonDisp_id = PD.PersonDisp_id
				left join dbo.v_PersonState PS on SIPD.Person_id = PS.Person_id
				left join v_Diag dg1 on PD.Diag_id = dg1.Diag_id
				left join lateral(
					select
						EVPL.EvnVizitPL_setDT as PersonDisp_LastDate
					from
						dbo.v_EvnVizitPL EVPL
						left join dbo.v_VizitType VT on VT.VizitType_id = EVPL.VizitType_id
					where
					VT.VizitType_SysNick='disp'
					and cast(PD.PersonDisp_begDate as date)<=cast(EVPL.EvnVizitPL_setDT as date)
					and EVPL.Person_id = SIPD.Person_id
					and EVPL.Diag_id = SIPD.Diag_id 
					order by
						EVPL.EvnVizitPL_setDT desc
					limit 1
				) LD on true
				left join lateral(
					select pdv.PersonDispVizit_NextFactDate 
					from dbo.v_PersonDispVizit pdv
					where pdv.PersonDisp_id = SIPD.PersonDisp_id 
					order by pdv.PersonDispVizit_NextFactDate desc
					limit 1
				) lapdv on true
				-- end from
			where 
				-- where
				(1=1)
				and case when LD.PersonDisp_LastDate is not null or lapdv.PersonDispVizit_NextFactDate is not null
						then case when COALESCE(SIPD.PersonDispVizit_NextDate,PD.PersonDisp_NextDate) > (case when LD.PersonDisp_LastDate > lapdv.PersonDispVizit_NextFactDate then LD.PersonDisp_LastDate else lapdv.PersonDispVizit_NextFactDate end) then 1 end 
						else 1 
					end = 1
				-- end where
			order by 
				-- order by
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				PersonDisp_NextDate
				-- end order by
				";
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');

		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}

	/**
	 * Получение списка записей не включенных в регистр
	 */
	function loadPregnancyNotIncludeList($data) {
		$query = "
			-- addit with
			with cte as (
				select 
					dbo.tzGetDate() as date, 
					dateadd('month', -3, dbo.tzGetDate()) as date3m
				limit 1
			),
            e as (
				SELECT 
					EPL.EvnPL_id as Evn_id,
					EPL.EvnPL_setDate as Evn_setDate,
					EPL.EvnPL_disDate as Evn_disDate,
					EPL.Diag_id,
					EPL.Person_id,
					EPL.Lpu_id,
					'Амбулаторный' as EvnType,
					EVPL.MedStaffFact_id,
					EPL.EvnPL_NumCard as Evn_NumCard,
					RC.ResultClass_Name as EvnResult
				FROM  
					v_EvnPL EPL 
					inner join v_MorbusDiag MD  on MD.MorbusType_id = 2 and MD.Diag_id = EPL.Diag_id
					left join v_PersonPregnancy PP  on PP.Evn_id = EPL.EvnPL_id
					left join lateral (select * from v_EvnVizitPL  where EvnVizitPL_pid = EPL.EvnPL_id and EvnVizitPL_Index = EvnVizitPL_Count - 1 limit 1) EVPL on true
					left join v_ResultClass RC  on RC.ResultClass_id = EPL.ResultClass_id 
				WHERE 
					EPL.EvnPL_setDate >= (select date3m from cte)
					and PP.PersonPregnancy_id is null
					and EPL.Diag_id not in (1944,1946,11071,11072,11073,11074,11075,11076,11077,11078,11079,11089,11090,11091)
				
				union all
			
				SELECT 
					EPS.EvnPS_id as Evn_id,
					EPS.EvnPS_setDate as Evn_setDate,
					EPS.EvnPS_disDate as Evn_disDate,
					EPS.Diag_id,
					EPS.Person_id,
					EPS.Lpu_id,
					'Стационарный' as EvnType,
					ES.MedStaffFact_id,
					EPS.EvnPS_NumCard as Evn_NumCard,
					LT.LeaveType_Name as EvnResult
				FROM  
					v_EvnPS EPS 
					inner join v_MorbusDiag MD  on MD.MorbusType_id = 2 and MD.Diag_id = EPS.Diag_id
					left join v_PersonPregnancy PP  on PP.Evn_id = EPS.EvnPS_id
					left join lateral (select * from v_EvnSection  where EvnSection_pid = EPS.EvnPS_id and EvnSection_Index = EvnSection_Count - 1 limit 1) ES on true
					left join v_LeaveType LT  on LT.LeaveType_id = EPS.LeaveType_id 
				WHERE 
					EPS.EvnPS_setDate >= (select date3m from cte)
					and PP.PersonPregnancy_id is null
					and EPS.Diag_id not in (1944,1946,11071,11072,11073,11074,11075,11076,11077,11078,11079,11089,11090,11091)
			)
			-- end addit with
			
			select
			-- select
				e.Evn_id as \"Evn_id\",
				e.EvnType as \"EvnType\",
				to_char(E.Evn_setDate, 'dd.mm.yyyy') as \"Evn_setDate\",
				to_char(E.Evn_disDate, 'dd.mm.yyyy') as \"Evn_disDate\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				D.Diag_id as \"Diag_id\",
				D.Diag_FullName as \"Diag_FullName\",
				PS.Person_id as \"Person_id\",
				PS.Person_SurName || ' ' || PS.Person_FirName || coalesce(' ' || PS.Person_SecName,'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				LpuAttach.Lpu_id as \"LpuAttach_id\",
				LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
				coalesce(PAddress.Address_Nick, PAddress.Address_Address) as \"Person_PAddress\",
				MSF.Person_Fio as \"MedPersonal\",
				case when exists(
					select * 
					from v_PersonQuarantine PQ 
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				e.Evn_NumCard as \"Evn_NumCard\", 
				e.EvnResult as \"EvnResult\"
			-- end select
			from
			-- from
				e 
				left join v_PersonState PS on PS.Person_id = E.Person_id
				left join v_Lpu L on L.Lpu_id = E.Lpu_id
				left join v_PersonCard PC on PC.Person_id = E.Person_id and PC.LpuAttachType_id = 1
				left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
				left join v_Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_Diag D  on D.Diag_id = e.Diag_id
				left join v_MedStaffFact MSF  on MSF.MedStaffFact_id = e.MedStaffFact_id
			-- end from
			where 
			-- where
				(1=1)
				and not exists (
					select 1 from v_PersonRegister PR
					where PR.Person_id = E.Person_id
						and PR.PersonRegisterType_id = 2 -- общерегиональный
						and coalesce(PR.PersonRegister_disDate, (select date from cte)) >= E.Evn_setDate
					limit 1
				)
				and exists (
					select 1 from v_PersonCard PCG
						inner join v_LpuRegion LR on LR.LpuRegion_id = PCG.LpuRegion_id
						inner join v_MedStaffRegion msr on msr.LpuRegion_id = lr.LpuRegion_id
					where
						PCG.LpuAttachType_id = 2 and
						PCG.Person_id = E.Person_id and 
						PCG.Lpu_id = :Lpu_id and 
						coalesce(msr.MedStaffRegion_endDate, (select date from cte)) >= (select date from cte) and 
						msr.MedPersonal_id = :MedPersonal_id
					limit 1
				)
			-- end where
			order by
			-- order by			
				E.Evn_disDate desc,
				E.Evn_setDate desc
			-- end order by
		";

		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}
}





