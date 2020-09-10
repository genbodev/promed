<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SignalInfo_model extends swModel {

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
		),
		'loadDistObservList' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => '', 'field' => 'DistObserv_Range', 'label' => 'Дата', 'rules' => '', 'type' => 'string'))
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
				Person_id,
				PersonEvn_id,
				Server_id,
				Person_Fio,
				CONVERT(varchar(10), Person_Birthday, 104) AS Person_Birthday,
				Person_Age,
				LpuSections_Name,
				Lpu_Name,
				Diag_Name,
				PrehospType_Name,
				CONVERT(varchar(10), EvnPS_disDateTime, 104) AS EvnPS_disDateTime,
				Diag_Code,
				Lpu_ideps
				-- end select
			FROM
				-- from
				SignalInfoEJHW with (nolock)
				-- end from
			WHERE
				-- where
				(1 = 1)
				{$filters}
				-- end where
			ORDER BY
				-- order by
				EvnPS_disDateTime DESC
				-- end order by";
		//return $this->queryResult($query, $params);
		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
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
				S.Person_id,
				S.PersonEvn_id,
				S.Server_id,
				S.EvnXml_id,
				CONVERT(VARCHAR(10), S.EvnUslugaPar_setDate, 104) AS EvnUslugaPar_setDate
				,S.MedPersonal_Fio
				,S.Usluga_Code
				,RTRIM(S.Usluga_Name) as Usluga_Name
				,S.Person_Surname
				,S.Person_Firname
				,S.Person_Secname
				,CONVERT(VARCHAR(10), S.Person_Birthday, 104) AS Person_Birthday
				,S.Person_Age
				,xth.XmlTemplateHtml_HtmlTemplate as XmlTemplate_HtmlTemplate
				-- end select
			FROM 
				-- from
				SignalInfoEvnUsluga S with (NOLOCK)
				left join v_EvnXml EvnXml with (NOLOCK) on S.EvnXml_id = EvnXml.EvnXml_id
				left join XmlTemplateHtml xth with (NOLOCK) on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
				
				-- end from
			where 
				-- where
				(1=1)
				{$filters} 
				-- end where
			order by 
				-- order by
				EvnUslugaPar_setDate  DESC
				-- end order by";
		//return $this->queryResult($query, $params);
		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
		//return $response;
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
			$offset .= " OFFSET " . $data['start'] . " ROWS ";
			if(isset($data['limit']) && $data['limit']>0) {
				$offset .= " FETCH NEXT ".$data['limit']." ROWS ONLY ";
			}
		} else if (isset($data['limit']) && $data['limit']>0) {
			$limiter .= " TOP " . $data['limit'];
		}
		
		$query = "
				SELECT {$limiter}
						EPS.EvnPS_id as EvnPS_id,
						EPS.Person_id as Person_id,
						EPS.PersonEvn_id as PersonEvn_id,
						EPS.Server_id as Server_id,
						ESEC.EvnSection_id,
						ISNULL(EPS.EvnPS_IsTransit, 1) as EvnPS_IsTransit,
						RTRIM(EPS.EvnPS_NumCard) as EvnPS_NumCard,
						convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate,
						convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate,
						ISNULL(LP.Lpu_Name, '') as Lpu_Name,
						ISNULL(LStmp.LpuSection_Name, '') as LpuSection_Name,
						ISNULL(Dtmp.Diag_FullName, DP.Diag_FullName) as Diag_Name,
						ISNULL(Prehos.PrehospType_Name, '') as PrehospType_Name,
						-- поскольку в одном КВС не может быть движений по круглосуточным и дневным стационарам вместе (поскольку это делается через перевод и создание новой карты)
						-- то подсчет количества койкодней реализуем так (с) Night, 2011-06-22
						
					case when LpuUnitType.LpuUnitType_SysNick = 'stac'
					then datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + abs(sign(datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - 1) -- круглосуточные
					else (datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + 1) -- дневные
					end as EvnPS_KoikoDni,
					
						--CASE WHEN PS.Server_pid = 0 THEN 'true' ELSE 'false' END as Person_IsBDZ,
						dbfpayt.PayType_Name as PayType_Name, --Вид оплаты
						CASE
							WHEN LT.LeaveType_Name is not null THEN LT.LeaveType_Name
							WHEN EPS.PrehospWaifRefuseCause_id > 0 THEN pwrc.PrehospWaifRefuseCause_Name
							ELSE ''
						END as LeaveType_Name,
						LT.LeaveType_Code,
						CASE WHEN DeathSvid.DeathSvid_id is null then 'false'
						else 'true'
						end as DeadSvid,
						EPS.PrehospWaifRefuseCause_id,
						 ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') as EvnSection_KSG,
						ISNULL(ksgkpg.Mes_Code, '') + ' ' +  ISNULL(ksgkpg.Mes_Name, '') as EvnSection_KSGKPG,
						 kpg.Mes_Code as EvnSection_KPG,
						convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
						case when ecp.EvnCostPrint_IsNoPrint = 2 then 'Отказ от справки' when ecp.EvnCostPrint_IsNoPrint = 1 then 'Справка выдана' else '' end as EvnCostPrint_IsNoPrintText
							,RTRIM(PS.Person_SurName) as Person_Surname
							,RTRIM(PS.Person_FirName) as Person_Firname
							,RTRIM(PS.Person_SecName) as Person_Secname
							,ps.Person_SurName+ISNULL(' '+ps.Person_FirName,'')+ISNULL(' '+ps.Person_SecName,'') as Person_Fio
							,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
							,dbo.Age2(PS.Person_Birthday, dbo.tzGetDate()) as Person_Age
							,COALESCE(convert(varchar(10), DeathSvid.DeathSvid_DeathDate,104),convert(varchar(10), EvnDie.EvnDie_setDate, 104),convert(varchar(10), PS.Person_DeadDT, 104),'') as Person_deadDT
						,case when SES1.ServiceEvnStatus_SysNick in ('sendegis','loadegis') then 'true' else 'false' end as fedservice_iemk
				, case when ISNULL(EPS.EvnPS_IsArchive, 1) = 1 then 0 else 1 end as archiveRecord
			
			
			FROM  
							v_PersonState PS with (nolock)
						 inner join v_EvnPS EPS with (nolock) on EPS.Person_id = PS.Person_id and EPS.Lpu_id in (10010833,101,150185)
						 inner join v_EvnSection as ESEC with (nolock) on ESEC.EvnSection_pid = EPS.EvnPS_id and ESEC.Lpu_id in (10010833,101,150185)
								left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EPS.EvnPS_id
								left join v_Lpu LP (nolock) on EPS.Lpu_id = LP.Lpu_id
								left join v_PrehospType Prehos (nolock) on EPS.PrehospType_id = Prehos.PrehospType_id
								left join v_EvnSection EPSLastES with (nolock) on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1 and EPSLastES.Lpu_id in (10010833,101,150185)
								left join LpuSection LStmp with (nolock) on LStmp.LpuSection_id = EPSLastES.LpuSection_id
								left join v_Diag Dtmp with (nolock) on Dtmp.Diag_id = EPSLastES.Diag_id
								left join LeaveType LT with (nolock) on LT.LeaveType_id = ISNULL(EPSLastES.LeaveType_id, EPSLastES.LeaveType_prmid)
								outer apply (
									select top 1 *
									from v_MedPersonal with (nolock)
									where MedPersonal_id = EPSLastES.MedPersonal_id
										and Lpu_id in (10010833,101,150185)
									order by
										case when Lpu_id = '10010833' then 1 else 2 end
								) MP
								left join v_Diag DP with (nolock) on DP.Diag_id = EPS.Diag_pid
								left join LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_id
								left join v_PrehospWaifRefuseCause pwrc with(nolock) on pwrc.PrehospWaifRefuseCause_id = EPS.PrehospWaifRefuseCause_id
								left join LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LStmp.LpuUnit_id 
								left join LpuUnitType with (nolock) on LpuUnitType.LpuUnitType_id = LpuUnit.LpuUnitType_id 
								left join PayType dbfpayt (nolock) on dbfpayt.PayType_id = EPS.PayType_id
								left join v_Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
								outer apply (
									select top 1 EvnDie_setDate
									from v_EvnDie with (nolock)
									where Person_id = PS.Person_id
									order by EvnDie_setDate
								) as EvnDie
								outer apply(
									SELECT TOP 1 DeathSvid_id,DeathSvid_DeathDate 
									FROM dbo.v_DeathSvid WITH (NOLOCK) 
									WHERE Person_id = PS.Person_id and (DeathSvid_IsBad is null or DeathSvid_IsBad = 1)
								) as DeathSvid
								left join v_MesTariff spmt (nolock) on EPSLastES.MesTariff_id = spmt.MesTariff_id
								left join v_MesOld as sksg with (nolock) on sksg.Mes_id = EPSLastES.Mes_sid
								left join v_MesOld as ksg with (nolock) on ksg.Mes_id = case
									when spmt.Mes_id in (EPSLastES.Mes_sid, EPSLastES.Mes_tid) then spmt.Mes_id
									else isnull(EPSLastES.Mes_sid, EPSLastES.Mes_tid)
								end
								left join v_MesOld as ksgkpg with (nolock) on spmt.Mes_id = ksgkpg.Mes_id
								left join v_MesOld as kpg with (nolock) on kpg.Mes_id = EPSLastES.Mes_kid
								left join v_EvnReanimatPeriod ERP with (nolock) on ERP.EvnReanimatPeriod_rid = EPS.EvnPS_id
								 outer apply (
									select top 1 ESS.EvnSection_id
									from v_EvnSection ESS with (nolock)
									inner join v_Diag ESSDiag with (nolock) on ESSDiag.Diag_id = ESS.Diag_id
									where ESS.EvnSection_pid = EPS.EvnPS_id
										and ESS.Lpu_id in (10010833,101,150185)
								 and ((coalesce(ESSDiag.Diag_Code, '') in('H00.0')) or (coalesce(ESSDiag.Diag_Code, '') between 'B00.0' and 'B00.9'))) adf
					outer apply (
						select top 1 ServiceEvnStatus_id
						from v_ServiceEvnHist with (nolock)
						where Evn_id = EPS.EvnPS_id
							and ServiceEvnList_id = 1
						order by ServiceEvnHist_id desc
					) SEH1
					left join v_ServiceEvnStatus SES1 with(nolock) on SES1.ServiceEvnStatus_id = SEH1.ServiceEvnStatus_id
				
					 WHERE 
					(1 = 1) 
					and ISNULL(EPS.EvnPS_IsArchive, 1) = 1 
					and adf.EvnSection_id is null 
					and coalesce(Dtmp.Diag_Code, '') not in('H00.0') 
					and coalesce(Dtmp.Diag_Code, '') not between 'B00.0' and 'B00.9'
					{$filters}
						ORDER BY 
							EPS.EvnPS_id
						{$offset}
		";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		}
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
		$filters .= " and exists (select top 1 PP.PersonPrivilege_id from v_PersonPrivilege as PP with (nolock) where PP.PrivilegeType_id = PrivilegeType_id and PP.Person_id = Person_id {$lpuFilter})";


		$query = "
			SELECT 
				-- select
				Person_id,
				PersonEvn_id,
				Server_id,
				CONVERT(VARCHAR(10), Privilege_begDate, 104) AS Privilege_begDate
				,CONVERT(VARCHAR(10), Privilege_endDate, 104) AS Privilege_endDate
				,Person_IsFedLgot
				,Person_IsRegLgot
				,Person_Is7Noz
				,PrivilegeType_Code
				,PrivilegeType_Name
				,Person_Surname
				,Person_Firname
				,Person_Secname
				,CONVERT(VARCHAR(10), Person_Birthday, 104) AS Person_Birthday
				,Person_Age
				,PrivilegeType_id
				-- end select
			FROM 
				-- from
				SignalInfoRegisterPrivilege with (nolock)
				-- end from
			where 
				-- where
				(1=1) 
				{$filters}
				-- end where
			order by 
				-- order by
				Privilege_endDate  DESC
				-- end order by";

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
				SI.Person_id,
				SI.PersonEvn_id,
				SI.Server_id,
				SI.EvnStickClass_Name
				,SI.EvnStickBase_Ser
				,SI.EvnStickBase_Num
				,l.Lpu_Name
				,SI.Person_Surname
				,SI.Person_Firname
				,SI.Person_Secname
				,CONVERT(VARCHAR(10), SI.Person_Birthday, 104) AS Person_Birthday
				,SI.Person_Age
				,SI.MedPersonalFirst_Fio
				,CONVERT(VARCHAR(10), SI.EvnStickWorkRelease_begDate, 104) AS EvnStickWorkRelease_begDate
				,CONVERT(VARCHAR(10), SI.EvnStickWorkRelease_endDate, 104) AS EvnStickWorkRelease_endDate
				,SI.EvnStickWorkRelease_DaysCount
				-- end select
			FROM 
				-- from
				SignalInfoEvnStick SI with (nolock)
				left join v_lpu l with (nolock) on SI.lpu_id = l.lpu_id
				-- end from
			where 
				-- where
				(1=1) 
				{$filters}
				-- end where
			order by 
				-- order by
				EvnStickWorkRelease_endDate  DESC
				-- end order by";
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
				Person_id,
				Person_FIO
				,CONVERT(VARCHAR(10), Person_Birthday, 104) AS Person_Birthday
				,CONVERT(VARCHAR(10), DeathSvid_DeathDate, 104) AS DeathSvid_DeathDate
				,dbo.Age2(Person_BirthDay, dbo.tzGetDate()) as Person_Age
				,Lpu_Nick
				,Person_id
				-- end select
			FROM 
				-- from
				SignalInfoDeathSvid with (nolock)
				-- end from
			where 
				-- where
				(1=1) 
				{$filters}
				-- end where
			order by
				-- order by
				DeathSvid_DeathDate  DESC
				-- end order by";
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
				Person_id,
				PersonEvn_id,
				Server_id,
				CONVERT(VARCHAR(10), CmpCallCard_prmDate, 104) AS CmpCallCard_prmDate
				,CONVERT(VARCHAR(5), CmpCallCard_prmTime, 108) AS CmpCallCard_prmTime
				,LpuBuilding_Name
				,Person_Surname
				,Person_Firname
				,Person_Secname
				,CmpReason_Name
				,CONVERT(VARCHAR(10), Person_Birthday, 104) AS Person_Birthday
				,Person_Age
				,CmpLpu_Name
				,CmpDiag_Name
				,Diag_Code
				-- end select
			FROM 
				-- from
				SignalInfoCmpCallCard with (nolock)
				-- end from
			where 
				-- where
				(1=1) 
				{$filters}
				-- end where
			order by 
				-- order by
				CmpCallCard_prmDate  DESC
				-- end order by";
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
			-- variables
			declare @curDT datetime = dbo.tzGetDate();
			declare @curDate date = dbo.tzGetDate();
			declare @PersonDopDisp_YearEndDate datetime = cast(YEAR(@curDT) as varchar) + '-12-31';
			-- end variables
			-- addit with
			with 
			EvnPLDispDop13Top as (
				select
						EvnPLDispDop13.EvnPLDispDop13_id,
						EvnPLDispDop13.EvnPLDispDop13_consDT,
						PS.Person_id,
						RTRIM(PS.Person_Surname) as Person_Surname,
						RTRIM(PS.Person_Firname) as Person_Firname,
						RTRIM(PS.Person_Secname) as Person_Secname,
						PS.Person_Birthday
			
				from v_PersonState PS with(nolock)
				left join [v_EvnPLDispDop13] [EvnPLDispDop13] with (nolock) on [PS].[Person_id] = [EvnPLDispDop13].[Person_id] 
				and [EvnPLDispDop13].Lpu_id = :Lpu_id and 
				ISNULL(EvnPLDispDop13.DispClass_id,1) = 1 
				and YEAR(EvnPLDispDop13.EvnPLDispDop13_consDT) = YEAR(@curDT) 
				
				where
					(1=1) and exists (select top 1 personcard_id from v_PersonCard PC with (nolock)  
					left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id 
					left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid 
					left join v_MedStaffRegion MedStaffRegion with (NOLOCK) on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id 
					WHERE PC.Person_id = PS.Person_id and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @curDT) 
					and MedStaffRegion.MedPersonal_id = :MedPersonal_id
					and PC.Lpu_id = :Lpu_id and PC.LpuAttachType_id = '1')  
					and (PS.Person_deadDT >= cast(YEAR(@curDT) as varchar) + '-01-01' OR PS.Person_deadDT IS NULL) 
					and EvnPLDispDop13.EvnPLDispDop13_disDate is null -- дата окончания не заполнено
					and (EvnPLDispDop13.EvnPLDispDop13_id is not null OR (
			(
				(dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) - 21 >= 0 and (dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) - 21) % 3 = 0)
				 or (exists (select top 1 PersonPrivilegeWOW_id from v_PersonPrivilegeWOW (nolock) where Person_id = PS.Person_id) and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) >= 18)
				or
				(PS.Sex_id = 1 and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) between 49 and 73 and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) % 2 = 1)
				or
				(PS.Sex_id = 2 and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) between 48 and 73)
			
			)
			and dbo.Age2(PS.Person_BirthDay, @PersonDopDisp_YearEndDate) <= 999
		))
			)
			-- end addit with

			SELECT 
				-- select
				EPLDD13.Person_id,
				EPLDD13.Person_Surname,
				EPLDD13.Person_Firname,
				EPLDD13.Person_Secname,
				CONVERT(VARCHAR(10), EPLDD13.Person_BirthDay, 104) as Person_BirthD,
				dbo.Age2(EPLDD13.Person_BirthDay, @curDT) as Person_Age,
				--LpuAttach.MedPersonal_id,
				convert(varchar(10), EPLDD13.[EvnPLDispDop13_consDT], 104) as [EvnPLDispDop13_setDate]
				-- end select
			FROM 
				-- from
				EvnPLDispDop13Top EPLDD13 with (nolock)
				left join v_TimetableGraf_lite ttg with (nolock) on ttg.Person_id = EPLDD13.Person_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				-- end from
			where 
				-- where
				(1=1) 
				and MSF.MedPersonal_id = :MedPersonal_id
				and (cast(ttg.TimetableGraf_factTime as date) >= @curDate and cast(ttg.TimetableGraf_factTime as date) <= @curDate
					or cast(ttg.TimetableGraf_begTime as date) >= @curDate and cast(ttg.TimetableGraf_begTime as date) <= @curDate
					or cast(ttg.TimetableGraf_insDT as date) >= @curDate and cast(ttg.TimetableGraf_insDT as date) <= @curDate)
				
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
			-- variables
			declare @curDT datetime = dbo.tzGetDate();
			declare @curDate date = dbo.tzGetDate();
			declare @PersonDopDisp_YearEndDate datetime = cast(YEAR(@curDT) as varchar) + '-12-31';
			-- end variables
			SELECT 
				-- select
				PS.Person_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				CONVERT(VARCHAR(10), PS.Person_BirthDay, 104) as Person_BirthD,
				dbo.Age2(PS.Person_BirthDay, @curDT) as Person_Age,
				MSF.MedPersonal_id,
				--LpuAttach.MedPersonal_id,
				convert(varchar(10), DopDispSecond.EvnPLDispDop13_consDT, 104) as EvnPLDispDop13_setDate
				-- end select
			FROM 
				-- from
				v_TimetableGraf_lite ttg with (nolock)
				left join v_PersonState PS with (nolock) on PS.Person_id = ttg.Person_id
				inner join v_EvnPLDispDop13 EPLDD13 with (nolock) on ttg.Person_id = EPLDD13.Person_id and EPLDD13.Lpu_id = :Lpu_id 
				and ISNULL(EPLDD13.DispClass_id,1) = 1 
				and YEAR(EPLDD13.EvnPLDispDop13_consDT) = YEAR(@curDT)  
				and isnull(EPLDD13.EvnPLDispDop13_IsTwoStage, 1) = '2' 
				left join v_EvnPLDispDop13 (nolock) DopDispSecond on (DopDispSecond.EvnPLDispDop13_fid = EPLDD13.EvnPLDispDop13_id)
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				-- end from
			where 
				-- where
				(1=1) 
				and MSF.MedPersonal_id = :MedPersonal_id
				and (cast(ttg.TimetableGraf_factTime as date) >= @curDate and cast(ttg.TimetableGraf_factTime as date) <= @curDate
					or cast(ttg.TimetableGraf_begTime as date) >= @curDate and cast(ttg.TimetableGraf_begTime as date) <= @curDate
					or cast(ttg.TimetableGraf_insDT as date) >= @curDate and cast(ttg.TimetableGraf_insDT as date) <= @curDate)
				and PS.Person_id is not null
				and DopDispSecond.EvnPLDispDop13_disDate is null
				and exists (select top 1 personcard_id from v_PersonCard PC with (nolock)  
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = PC.LpuRegion_id 
				left join v_LpuRegion LR_Fap with (nolock) on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
				left join v_MedStaffRegion MedStaffRegion with (NOLOCK) on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id 
				WHERE PC.Person_id = PS.Person_id
				and MedStaffRegion.MedPersonal_id = :MedPersonal_id
				and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @curDT) 
				and PC.Lpu_id = :Lpu_id and PC.LpuAttachType_id = '1')
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
			-- variables
				declare @curDT datetime = dbo.tzGetDate();
			-- end variables
			SELECT 
				-- select
				Person.Server_id,
				Person.Person_id,
				lpued.Lpu_Nick as LpuED_Nick,
				CONVERT(VARCHAR(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate, --Дата направления
				TTG.TimetableGraf_updDT, --Дата записи
				convert(varchar(10), cast(TTG.TimeTableGraf_begTime as datetime), 104) + ' ' + convert(varchar(8), cast(TTG.TimeTableGraf_begTime as datetime), 108) as TimeTableGraf_begTime,
				Person.Person_surName,
				Person.Person_firName,
				Person.Person_secName,
				CONVERT(VARCHAR(10), Person.Person_Birthday, 104) as Person_Birthday,
				dbo.Age2(Person.Person_BirthDay, @curDT) as Person_Age,
				--Lpu_MSF.Lpu_Nick, -- МО врача
				LS_MSF.LpuSection_Name,
				LS_MSF.LpuSectionProfile_Name,
				MSF.Person_Fio vrach,
				MSF.MedPersonal_id,
				case when ED.EvnDirection_id is null then 'НЕТ' else 'ДА' end direction,
				l.Lpu_Nick, -- Мо прикрепления
				adr.Address_Address,
				Person.Person_Phone
				-- end select
			FROM 
				-- from
				v_TimeTableGraf TTG (nolock)
				join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = TTG.MedStaffFact_id
				join v_Lpu Lpu_MSF (nolock) on Lpu_MSF.Lpu_id = MSF.Lpu_id
				--join v_Lpu ED (nolock) on Lpu_MSF.Lpu_id = MSF.Lpu_id
				join v_LpuSection LS_MSF (nolock) on LS_MSF.LpuSection_id = MSF.LpuSection_id
				join v_LpuSectionProfile LSProfile (nolock) on LSProfile.LpuSectionProfile_id = LS_MSF.LpuSectionProfile_id
				left join v_EvnDirection ED (nolock) on ED.EvnDirection_id = TTG.EvnDirection_id
				outer apply (
					select  top 1 Vizit.EvnVizitPL_id, vizit.MedStaffFact_id from v_EvnVizitPL vizit (nolock)
					where
					vizit.Person_id = TTG.Person_id
					and vizit.Lpu_id = MSF.Lpu_id
					and vizit.MedPErsonal_id = MSF.MedPersonal_id
					and vizit.LpuSection_id = MSF.LpuSection_id
					and cast(vizit.EvnVizitPL_setDate as date) = cast(:begDate as date)
				)Vizit
				left join v_PersonState Person (nolock) on Person.Person_id = TTG.Person_id
				left join v_Lpu l with (nolock) on l.Lpu_id = Person.Lpu_id
				left join v_Lpu lpued with (nolock) on lpued.Lpu_id = ED.Lpu_id
				left join v_Address adr (nolock) on adr.Address_id = Person.PAddress_id
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
			-- variables
			declare @curDT datetime = dbo.tzGetDate();
			-- end variables
			select
			-- select
				ed.EvnDirection_id,
				ps.Person_id,
				ps.Server_id,
				ps.Person_SurName,
				ps.Person_SecName,
				ps.Person_FirName,
				CONVERT(VARCHAR(10), Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), evpl.EvnVizitPL_setDate, 104) + ' ' + convert(varchar(5), evpl.EvnVizitPL_setTime, 108) as EvnVizitPL_setDT,
				dbo.Age2(PS.Person_BirthDay, @curDT) as Person_Age,
				CONVERT(VARCHAR(10), EvnDirection_setDate, 104) as EvnDirection_setDate,
				ed.EvnDirection_Num,
				ed.Lpu_did,
				lpuDid.Lpu_Name as LpuDid_Nick,
				ed.RemoteConsultCause_id,
				rcc.RemoteConsultCause_Name,
				ed.ConsultationForm_id,
				cf.ConsultationForm_Name,
				ed.EvnDirection_IsCito,
				ed.Diag_id,
				EDdiag.Diag_Code + ' ' + EDdiag.Diag_Name as EDDiag_Name,
				ed.MedPersonal_id,
				convert(varchar(10), eut.EvnUslugaTelemed_setDate, 104) + ' ' + convert(varchar(5), eut.EvnUslugaTelemed_setTime, 108) as EvnUslugaTelemed_setDT,
				UTT.UslugaTelemedResultType_Name,
				EUTdiag.Diag_Code + ' ' + EUTdiag.Diag_Name as EUTDiag_Name
			-- end select
			from
			-- from
				dbo.v_EvnDirection ed with (nolock)
				left join dbo.v_PersonState ps with(nolock) on ps.Person_id = ed.Person_id
				left join dbo.v_Lpu lpuDid with(nolock) on lpuDid.Lpu_id = ed.Lpu_did
				left join dbo.v_ConsultationForm cf with(nolock) on cf.ConsultationForm_id = ed.ConsultationForm_id
				left join dbo.v_Diag EDdiag with(nolock) on EDdiag.Diag_id = ED.Diag_id
				left join dbo.v_RemoteConsultCause RCC with(nolock) on RCC.RemoteConsultCause_id = ed.RemoteConsultCause_id
				left join dbo.v_EvnUslugaTelemed EUT with(nolock) on EUT.EvnDirection_id = ed.EvnDirection_id
				left join dbo.v_Diag EUTdiag with(nolock) on EUTdiag.Diag_id = eut.Diag_id
				left join dbo.v_UslugaTelemedResultType UTT with(nolock) on UTT.UslugaTelemedResultType_id = EUT.UslugaTelemedResultType_id
				inner join dbo.v_DirType DT with(nolock) on DT.DirType_id = ED.DirType_id and DT.Dirtype_Code = 13
				left join dbo.v_EvnVizitPL EVPL with(nolock) on EVPL.EvnVizitPL_id = ed.EvnDirection_pid 
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
					-- variables
				declare @date date = dbo.tzGetDate();
					-- end variables
				with PersonRegister (
					PersonRegister_id
				) as (
					select PR.PersonRegister_id
					from dbo.v_PersonRegister PR with(nolock)
					inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
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
					PR.PersonRegister_id,
					PR.PersonRegister_setDate,
					PR.Person_id,
					PR.Lpu_iid,
					PR.MedPersonal_iid,
					dbo.Age2(PS.Person_BirthDay, @date) as Person_Age,
					PR.PersonRegister_Code,
					PP.PersonPregnancy_id,
					isnull(rtrim(PS.Person_SurName),'')+isnull(' '+rtrim(PS.Person_FirName),'')+isnull(' '+rtrim(PS.Person_SecName),'') as Person_Fio,
					convert(varchar(10), PS.Person_BirthDay,  104) as Person_BirthDay,
					ISNULL(PP.PersonPregnancy_RiskDPP, 0) + ISNULL(LastScreen.PregnancyScreen_RiskPerPat, 0) as PersonPregnancy_ObRisk,
					Period.Value as PersonPregnancy_Period,
					(case when Period.Value between 1 and 12  then '1 триместр'
					when Period.Value between 13 and 27 then '2 триместр'
					when Period.Value between 28 and 43 then '3 триместр' end) as Trimester,
					[dbo].[GetPregnancyRoute](PR.PersonRegister_id, 1, 0) as lstfactorrisk,
					RKT.RiskType_Name as RiskType_AName,
					EPS_LPU.Lpu_Nick as NickHospital, -- МО госпитализации
					convert(varchar(10), EvnVizitPL_Date.EvnVizitPL_setDate,  104) as EvnVizitPL_setDate,
					convert(varchar(10), PP.PersonPregnancy_birthDate,  104) as PersonPregnancy_birthDate,
					ML.MesLevel_Name
					-- end select
				from
					-- from
					PersonRegister PReg with(nolock)
					left join v_PersonRegister PR with(nolock) on PReg.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonPregnancy PP with(nolock) on PP.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonState PS with(nolock) on PS.Person_id = PR.Person_id
					left join v_Lpu_all L with(nolock) on L.Lpu_id = PR.Lpu_iid
					outer apply(
						select top 1
							MedPersonal_id,
							Person_Fio as MedPersonal_Fio
						from v_MedPersonal with(nolock)
						where MedPersonal_id = PR.MedPersonal_iid and Lpu_id = isnull(L.Lpu_id, Lpu_id)
					) MP
					left join v_BirthSpecStac BSS with(nolock) on BSS.PersonRegister_id = PR.PersonRegister_id
					outer apply (
						select top 1 Screen.*
						from v_PregnancyScreen Screen with(nolock)
						where Screen.PersonRegister_id = PR.PersonRegister_id
						order by Screen.PregnancyScreen_setDT desc
					) LastScreen
					outer apply(
						select top 1 PQ.PregnancyQuestion_AnswerInt as Value
						from v_PregnancyQuestion PQ with(nolock)
						inner join v_QuestionType QT with(nolock) on QT.QuestionType_id = PQ.QuestionType_id
						where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
						and QT.QuestionType_Code in (358,359,362,363)
						order by QT.QuestionType_Code
					) LastScreenPeriod
					outer apply(
						select top 1 coalesce(
							BSS.BirthSpecStac_OutcomPeriod,
							LastScreenPeriod.Value + datediff(week, LastScreen.PregnancyScreen_setDT, @date),
							PP.PersonPregnancy_Period + datediff(week, PP.PersonPregnancy_setDT, @date)
						) as Value
					) Period
					outer apply(
						select top 1 EvnVizitPL_setDate
						from v_EvnVizitPL DT with(nolock)
						where DT.Person_id = PR.Person_id 
						and DT.MedPersonal_id = PR.MedPersonal_iid
						order by DT.EvnVizitPL_setDate DESC
					) EvnVizitPL_Date
					left join v_RiskType RKT with(nolock) on RKT.RiskType_id = PR.RiskType_aid
					left join v_MesLevel ML with(nolock) on ML.MesLevel_id = PR.MesLevel_id
					outer apply (
						select top 1 EPS_LPU.Lpu_Nick
						from v_EvnPS EPS with(nolock)
						left join v_Lpu_all EPS_LPU with(nolock) on EPS_LPU.Lpu_id = EPS.Lpu_id
						where EPS.Person_id = PR.Person_id and EPS.EvnPS_disDate is null
						order by EPS.EvnPS_setDate desc
					) EPS_LPU
					{$join}
					-- end from
				where
					-- where
					(1=1)
					and [dbo].[GetPregnancyRouteNoConsultation](PR.PersonRegister_id, PR.Person_id) like '%0%'
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
			$filters .= " and PR.PersonRegister_HighRiskDT >= dateadd(DAY, -7, @date) or PR.PersonRegister_ModerateRiskDT >= dateadd(DAY, -7, @date)";
			$filterHosp .= " and EPS.EvnPS_disDate is null";
			$order .= " PR.PersonRegister_setDate desc ";
		}

		if (!empty($data['MedPersonal_iid'])) {
			$MedPersonal_iids = explode(',', $data['MedPersonal_iid']);

			$filterMedPersonal = " and PR.MedPersonal_iid in (" . implode(',', $MedPersonal_iids) . ")";
		}

		$query = "
				-- variables
			declare @date date = dbo.tzGetDate();
				-- end variables
			select
				-- select
				PR.PersonRegister_id,
				PR.Person_id,
				PR.Lpu_iid,
				PR.MedPersonal_iid,
				PR.PersonRegister_Code,
				PP.PersonPregnancy_id,
				isnull(rtrim(PS.Person_SurName),'')+isnull(' '+rtrim(PS.Person_FirName),'')+isnull(' '+rtrim(PS.Person_SecName),'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_BirthDay, @date) as Person_Age,
				ISNULL(PP.PersonPregnancy_RiskDPP, 0) + ISNULL(LastScreen.PregnancyScreen_RiskPerPat, 0) as PersonPregnancy_ObRisk,
				Period.Value as PersonPregnancy_Period,
				(case when Period.Value between 1 and 12  then '1 триместр'
				when Period.Value between 13 and 27 then '2 триместр'
				when Period.Value between 28 and 43 then '3 триместр' end) as Trimester,
				[dbo].[GetPregnancyRoute](PR.PersonRegister_id, 1, 0) as lstfactorrisk,
				RKT.RiskType_Name as RiskType_AName,
				EPS.Lpu_Nick as NickHospital, -- МО госпитализации
				EPS.LpuSectionProfile_Name as ProfilHospital, -- Профиль госп
				EPS.LpuUnitType_Name,
				EPS.diag_FullName,
				convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate,
				convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate,
				convert(varchar(10), EvnVizitPL_Date.EvnVizitPL_setDate,  104) as EvnVizitPL_setDate,
				convert(varchar(10), PP.PersonPregnancy_birthDate,  104) as PersonPregnancy_birthDate,
				ML.MesLevel_Name
				-- end select
			from
				-- from
				v_PersonRegister PR with(nolock)
				inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick like 'pregnancy' {$filterMedPersonal} and PR.Lpu_iid = :Lpu_iid 
				left join v_PersonPregnancy PP with(nolock) on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonState PS with(nolock) on PS.Person_id = PR.Person_id
				left join v_Lpu_all L with(nolock) on L.Lpu_id = PR.Lpu_iid
				outer apply(
					select top 1
						MedPersonal_id,
						Person_Fio as MedPersonal_Fio
					from v_MedPersonal with(nolock)
					where MedPersonal_id = PR.MedPersonal_iid and Lpu_id = isnull(L.Lpu_id, Lpu_id)
				) MP
				left join v_BirthSpecStac BSS with(nolock) on BSS.PersonRegister_id = PR.PersonRegister_id
				outer apply (
					select top 1 Screen.*
					from v_PregnancyScreen Screen with(nolock)
					where Screen.PersonRegister_id = PR.PersonRegister_id
					order by Screen.PregnancyScreen_setDT desc
				) LastScreen
				outer apply(
					select top 1 PQ.PregnancyQuestion_AnswerInt as Value
					from v_PregnancyQuestion PQ with(nolock)
					inner join v_QuestionType QT with(nolock) on QT.QuestionType_id = PQ.QuestionType_id
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and QT.QuestionType_Code in (358,359,362,363)
					order by QT.QuestionType_Code
				) LastScreenPeriod
				outer apply(
					select top 1 coalesce(
						BSS.BirthSpecStac_OutcomPeriod,
						LastScreenPeriod.Value + datediff(week, LastScreen.PregnancyScreen_setDT, @date),
						PP.PersonPregnancy_Period + datediff(week, PP.PersonPregnancy_setDT, @date)
					) as Value
				) Period
				outer apply(
					select top 1 EvnVizitPL_setDate
					from v_EvnVizitPL DT with(nolock)
					where DT.Person_id = PR.Person_id 
					and DT.MedPersonal_id = PR.MedPersonal_iid
					order by DT.EvnVizitPL_setDate DESC
				) EvnVizitPL_Date
				left join v_RiskType RKT with(nolock) on RKT.RiskType_id = PR.RiskType_aid
				left join v_MesLevel ML with(nolock) on ML.MesLevel_id = PR.MesLevel_id
				outer apply (
					select top 1 EPS.EvnPS_id,EPS_LPU.Lpu_Nick,EPS_PROFILE.LpuSectionProfile_Name,EPS.EvnPS_setDate,EPS.EvnPS_disDate,D.diag_FullName,LUT.LpuUnitType_Name
					from v_EvnPS EPS with(nolock) 
						left join v_Diag D with (nolock) on D.Diag_id = EPS.Diag_id
						left join v_Lpu_all EPS_LPU with(nolock) on EPS_LPU.Lpu_id = EPS.Lpu_id
						left join v_EvnSection ESS with (nolock) on ESS.EvnSection_pid=EPS.EvnPS_id
						left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ESS.LpuSection_id
						left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						left join v_LpuUnitType LUT (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
						left join v_LpuSectionProfile EPS_PROFILE with(nolock) on EPS_PROFILE.LpuSectionProfile_id = ESS.LpuSectionProfile_id
					where EPS.Person_id = PR.Person_id {$filterHosp}
					order by EPS.EvnPS_setDate desc
				) EPS
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
				-- variables
			declare @date date = dbo.tzGetDate();
				-- end variables
			select
				-- select
				SI.CmpCallCard_id,
				SI.Person_id,
				SI.LpuBuilding_Name,
				convert(varchar(10), SI.CmpCallCard_prmDate, 104) as CmpCallCard_prmDate,
				convert(varchar(5), SI.CmpCallCard_prmTime, 108) as CmpCallCard_prmTime,
				SI.Person_Surname,
				SI.Person_Firname,
				SI.Person_Secname,
				convert(varchar(10), SI.Person_Birthday, 104) as Person_Birthday,
				SI.Person_Age,
				SI.CmpLpu_Name,
				SI.CmpDiag_Name,
				convert(varchar(10), EvnVizitPL_Date.EvnVizitPL_setDate,  104) as EvnVizitPL_setDate,
				convert(varchar(10), PP.PersonPregnancy_birthDate,  104) as PersonPregnancy_birthDate,
				ML.MesLevel_Name
				-- end select
			from
				-- from
				dbo.SignalInfoCmpCallCard SI with(nolock)
				left join v_PersonRegister PR with(nolock) on PR.Person_id = SI.Person_id
				inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick like 'pregnancy'
				left join v_PersonPregnancy PP with(nolock) on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonRegisterOutCause OutCause with(nolock) on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_MesLevel ML with(nolock) on ML.MesLevel_id = PR.MesLevel_id
				outer apply(
					select top 1 EvnVizitPL_setDate
					from v_EvnVizitPL DT with(nolock)
					where DT.Person_id = PR.Person_id 
					and DT.MedPersonal_id = PR.MedPersonal_iid
					order by DT.EvnVizitPL_setDate DESC
				) EvnVizitPL_Date
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
					--variables
				declare @getDT datetime = dbo.tzGetDate();
					--end variables
				select
					-- select
					CCC.CmpCallCard_id,
					CCC.Person_id,
					LB.LpuBuilding_Name,
					convert(varchar(10), CCC.CmpCallCard_prmDT, 104) as CmpCallCard_prmDate,
					convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmTime,
					RTRIM(COALESCE(CLC.Fam, PS.Person_Surname, CCC.Person_SurName)) as Person_Surname,
					RTRIM(COALESCE(CLC.Name, PS.Person_Firname, CCC.Person_FirName)) as Person_Firname,
					RTRIM(COALESCE(CLC.Middle, PS.Person_Secname, CCC.Person_SecName)) as Person_Secname,
					convert(varchar(10), COALESCE(PSCLC.Person_BirthDay,CCC.Person_BirthDay,PS.Person_BirthDay), 104) as Person_Birthday,
					ISNULL(dbo.Age2(PS.Person_BirthDay, @getDT), CLC.Age) as Person_Age,
					(case when CLC.CallPovod_id is not null then COALESCE(CR.CmpReason_Code + '. ', '') + CR.CmpReason_Name else COALESCE(CRTalon.CmpReason_Code + '. ', '') + CRTalon.CmpReason_Name end) as CmpReason_Name,
					--RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name,
					case when (CCC.CmpCallCardInputType_id in (1,2)) then COALESCE(Lpu.Lpu_Nick, Lpu.Lpu_Name, '')
						else COALESCE(LpuHid.Lpu_Nick, LpuHid.Lpu_Name, '') end
					as CmpLpu_Name,
					RTRIM(case when CLD.diag_FullName is not null then CLD.diag_FullName else CD.CmpDiag_Code end) as CmpDiag_Name,
					convert(varchar(10), EvnVizitPL_Date.EvnVizitPL_setDate,  104) as EvnVizitPL_setDate,
					convert(varchar(10), PP.PersonPregnancy_birthDate,  104) as PersonPregnancy_birthDate,
					ML.MesLevel_Name
					-- end select
				from
					-- from
					dbo.v_CmpCallCard CCC with (nolock)
					left join dbo.v_CmpCloseCard CLC with (nolock) on CLC.CmpCallCard_id = CCC.CmpCallCard_id
					left join CmpReason CR with (nolock) on CR.CmpReason_id = CLC.CallPovod_id
					left join CmpReason CRTalon with (nolock) on CRTalon.CmpReason_id = CCC.CmpReason_id
					left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
					left join v_Lpu L with (nolock) on L.Lpu_id = CL.Lpu_id
					left join v_Lpu Lpu with (nolock) on CCC.CmpLpu_id = Lpu.Lpu_id
					left join v_Lpu LpuHid with (nolock) on CCC.Lpu_hid = LpuHid.Lpu_id
					left join v_Diag CLD with (nolock) on CLD.Diag_id = CLC.Diag_id		
					left join v_CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
					left join v_PersonState PS with (nolock) on CCC.Person_id = PS.Person_id
					left join v_PersonState PSCLC with (nolock) on CLC.Person_id = PSCLC.Person_id
					left join LpuBuilding LB with (nolock) on LB.LpuBuilding_id = CLC.LpuBuilding_id
					left join v_PersonRegister PR with(nolock) on PR.Person_id = CCC.Person_id
					inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					and PRT.PersonRegisterType_SysNick like 'pregnancy'
					left join v_PersonPregnancy PP with(nolock) on PP.PersonRegister_id = PR.PersonRegister_id
					left join v_PersonRegisterOutCause OutCause with(nolock) on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					left join v_MesLevel ML with(nolock) on ML.MesLevel_id = PR.MesLevel_id
					outer apply(
						select top 1 EvnVizitPL_setDate
						from v_EvnVizitPL DT with(nolock)
						where DT.Person_id = PR.Person_id 
						and DT.MedPersonal_id = PR.MedPersonal_iid
						order by DT.EvnVizitPL_setDate DESC
					) EvnVizitPL_Date
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
					and exists (select top 1 personcard_id from v_PersonCard PC with (nolock)
					WHERE PC.Person_id = PS.Person_id and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @getDT))
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
			$filters .= " and [dbo].[GetPregnancyRouteNoConsultation](PR.PersonRegister_id, PR.Person_id) like '%0%'";
		} else if (isset($data['Type']) && $data['Type'] == 'HospitalRegion') {
			$join .= "outer apply (
						select top 1 EPS.EvnPS_id,EPS_LPU.Lpu_Nick,EPS_PROFILE.LpuSectionProfile_Name,EPS.EvnPS_setDate,EPS.EvnPS_disDate,D.diag_FullName,LUT.LpuUnitType_Name
						from v_EvnPS EPS with(nolock) 
							left join v_Diag D with (nolock) on D.Diag_id = EPS.Diag_id
							left join v_Lpu_all EPS_LPU with(nolock) on EPS_LPU.Lpu_id = EPS.Lpu_id
							left join v_EvnSection ESS with (nolock) on ESS.EvnSection_pid=EPS.EvnPS_id
							left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ESS.LpuSection_id
							left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
							left join v_LpuUnitType LUT (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
							left join v_LpuSectionProfile EPS_PROFILE with(nolock) on EPS_PROFILE.LpuSectionProfile_id = ESS.LpuSectionProfile_id
						where EPS.Person_id = PR.Person_id --and EPS.EvnPS_disDate is null
						order by EPS.EvnPS_setDate desc
					) EPS";
			$filters .= " and EPS.EvnPS_disDate is null 
						and PR.PersonRegister_setDate <= EPS.EvnPS_setDate 
						and EPS.EvnPS_setDate is not null ";
		} else {
			$join .= "--дата последнего посещения
						outer apply(
						select top 1 EvnVizitPL_setDate
						from v_EvnVizitPL DT with(nolock)
						where DT.Person_id = PR.Person_id 
						and DT.Lpu_id = PR.Lpu_iid -- смотрим посещения по лпу учета
						order by DT.EvnVizitPL_setDate DESC
						) EvnVizitPL_Date
						--разница между последней датой и сегодняшней
						outer apply(
						SELECT DATEDIFF(day,EvnVizitPL_Date.EvnVizitPL_setDate,GETDATE()) AS DiffDate
						) DiffDate";
			$filters .= " and ((case when ((Period.Value between 1 and 12) and DiffDate.DiffDate>35) then 1 end=1) 
						or (case when ((Period.Value between 13 and 27) and DiffDate.DiffDate>35) then 1 end=1)
						or (case when ((Period.Value between 28 and 43) and DiffDate.DiffDate>22) then 1 end=1))";
		}
		$query = "
			-- variables
			declare @date date = dbo.tzGetDate();
			-- end variables
			with PersonRegister (
				PersonRegister_id
			) as (
				select PR.PersonRegister_id
					from dbo.v_PersonRegister PR with(nolock)
					inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
						and PRT.PersonRegisterType_SysNick like 'pregnancy'
					where (1=1)
					and PR.PersonRegisterOutCause_id is null -- не исключен из регистра
					and PR.PregnancyResult_id is null -- нет исхода беременности
					and PR.PersonRegister_setDate is not null
			)
			select
				-- select
				PR.Lpu_iid,
				L.Lpu_Nick,
				count ( case when Period.Value between 1 and 12  then '1 триместр' end ) as Trimester1,
				count ( case when Period.Value between 13 and 27 then '2 триместр' end ) as Trimester2,
				count ( case when Period.Value between 28 and 43 then '3 триместр' end ) as Trimester3
				-- end select
			from
				-- from
				PersonRegister PReg with(nolock)
				left join v_PersonRegister PR with(nolock) on PReg.PersonRegister_id = PR.PersonRegister_id
				left join v_PersonPregnancy PP with(nolock) on PP.PersonRegister_id = PR.PersonRegister_id
				left join v_Lpu_all L with(nolock) on L.Lpu_id = PR.Lpu_iid
				left join v_BirthSpecStac BSS with(nolock) on BSS.PersonRegister_id = PR.PersonRegister_id
				outer apply (
					select top 1 Screen.*
					from v_PregnancyScreen Screen with(nolock)
					where Screen.PersonRegister_id = PR.PersonRegister_id
					order by Screen.PregnancyScreen_setDT desc
				) LastScreen
				outer apply(
					select top 1 PQ.PregnancyQuestion_AnswerInt as Value
					from v_PregnancyQuestion PQ with(nolock)
					inner join v_QuestionType QT with(nolock) on QT.QuestionType_id = PQ.QuestionType_id
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and QT.QuestionType_Code in (358,359,362,363)
					order by QT.QuestionType_Code
				) LastScreenPeriod
				outer apply(
					select top 1 coalesce(
						BSS.BirthSpecStac_OutcomPeriod,
						LastScreenPeriod.Value + datediff(week, LastScreen.PregnancyScreen_setDT, @date),
						PP.PersonPregnancy_Period + datediff(week, PP.PersonPregnancy_setDT, @date)
					) as Value
				) Period
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
			-- variables
				declare @date date = dbo.tzGetDate();
			-- end variables
			select
			--select
				PR.PersonRegister_id,
				PC.LpuAttachType_id,
				PR.Person_id,
				PS.Server_id,
				PS.PersonEvn_id,
				isnull(rtrim(PS.Person_SurName),'')+isnull(' '+rtrim(PS.Person_FirName),'')+isnull(' '+rtrim(PS.Person_SecName),'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay,  104) as Person_BirthDay,
				dbo.Age2(PS.Person_BirthDay, @date) as Person_Age,
				MT.MorbusType_id,
				MT.MorbusType_Name,
				case 
						when MT.MorbusType_id = 84 then cast(BSKRegistry.BSKRegistry_riskGroup as varchar(10))
						when MT.MorbusType_id = 89 then 
								(select RD.BSKRegistryData_data from dbo.BSKRegistryData RD 
								where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 269)
						when MT.MorbusType_id = 88 then 
								(select RD.BSKRegistryData_data from dbo.BSKRegistryData RD 
								where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 151)
						else NULL end as BSKRegistry_riskGroup,
				BSKRegistry.BSKRegistry_setDate,
				isnull(convert(varchar(10),BSKRegistry.BSKRegistry_nextDate,104),case 
					when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 1 then convert(varchar(10), (dateadd(MONTH, 18, BSKRegistry.BSKRegistry_setDate)), 104)
					when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 2 then convert(varchar(10), (dateadd(MONTH, 12, BSKRegistry.BSKRegistry_setDate)), 104)
					when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 3 then convert(varchar(10), (dateadd(MONTH, 6,BSKRegistry.BSKRegistry_setDate)), 104)
					when MT.MorbusType_id = 50 then convert(varchar(10), (dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)), 104)
					when MT.MorbusType_id = 89 then convert(varchar(10), (dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)), 104)
					when MT.MorbusType_id = 88 then convert(varchar(10), (dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)), 104)
					end) as BSKRegistry_setDateNext,
				BSKRegistry.BSKRegistry_id
				--end select
			from 
			-- from
			PersonRegister PR with (nolock)
			left join dbo.MorbusType MT  with (nolock) on MT.MorbusType_id = PR.MorbusType_id
			left join v_PersonState PS with(nolock) on PS.Person_id = PR.Person_id
			left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id  and PC.LpuAttachType_id = '1' 
			outer apply(
			select top 1 
					convert(varchar(10), R.BSKRegistry_setDate, 120) as BSKRegistry_setDate,
					R.BSKRegistry_riskGroup ,
					R.BSKRegistry_id,
					R.BSKRegistry_nextDate
			from dbo.BSKRegistry R with (nolock) 
			where R.MorbusType_id = MT.MorbusType_id and R.Person_id = PR.Person_id 
			order by R.BSKRegistry_setDate DESC
			) as BSKRegistry
			-- end from
			where
			--where
			(1=1)
			and PR.MorbusType_id in (84,88,89,50)
			and PC.LpuAttachType_id = '1'
			and PC.Lpu_id = :Lpu_id
			and cast((isnull(BSKRegistry.BSKRegistry_nextDate,case 
								when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 1 then dateadd(MONTH, 18, BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 2 then dateadd(MONTH, 12, BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 84 and BSKRegistry_riskGroup = 3 then dateadd(MONTH, 6,BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 50 then dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 89 then dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)
								when MT.MorbusType_id = 88 then dateadd(MONTH, 6, BSKRegistry.BSKRegistry_setDate)
								end)) as date) between cast(:begDate as date) and cast(:endDate as date)
			--end where 
				ORDER BY
			--order by
				BSKRegistry_setDateNext desc
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
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		);

		$query = "
			-- variables
				declare @curDT datetime = dbo.tzGetDate();
			-- end variables
			SELECT 
				distinct
				-- select
				PQ.PersonQuarantine_id,
				PQ.Person_id,
				PS.Server_id,
				isnull(rtrim(PS.Person_SurName),'')+isnull(' '+rtrim(PS.Person_FirName),'')+isnull(' '+rtrim(PS.Person_SecName),'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay,  104) as Person_BirthDay,
				dbo.Age2(PS.Person_BirthDay, @curDT) as Person_Age,
				PS.Person_Phone,
				convert(varchar(10),PQ.PersonQuarantine_begDT,104) as PersonQuarantine_begDate, --дата открытия
				PQOR.PersonQuarantineOpenReason_Name,
				convert(varchar(10),PQ.PersonQuarantine_approveDT, 104) as PersonQuarantine_approveDate,
				DATEDIFF(day, 
					cast( (case when isnull(PQ.PersonQuarantineOpenReason_id,0) = 1 then ROO.RepositoryObserv_arrivalDate
						when isnull(PQ.PersonQuarantineOpenReason_id,0) = 2 then ROO.RepositoryObesrv_contactDate
						when isnull(PQ.PersonQuarantineOpenReason_id,0) = 3 then PQ.PersonQuarantine_approveDT end)
						as date),
					cast( isnull(PQ.PersonQuarantine_endDT,GETDATE()) as date)
				)+1 as QuarantineDays,
				EPS.Lpu_Nick as NickHospital,
				RO.psign
				-- end select
			FROM 
				-- from
				dbo.v_PersonQuarantine PQ with(nolock)
				left join v_PersonState PS with(nolock) on PQ.Person_id = PS.Person_id
				outer apply (
					Select top 1 EPS_LPU.Lpu_Nick
					from v_EvnPS EPS with(nolock) 
						left join v_Lpu_all EPS_LPU with(nolock) on EPS_LPU.Lpu_id = EPS.Lpu_id
					where EPS.Person_id = PQ.Person_id and EPS.EvnPS_disDate is null
					order by EPS.EvnPS_setDate desc
				) EPS
				left join v_RepositoryObserv ROO with(nolock) on ROO.PersonQuarantine_id = PQ.PersonQuarantine_id and isnull(ROO.RepositoryObesrv_IsFirstRecord,1) = 2 
				outer apply (
					select top 1 MedPersonal_id, case when RO.RepositoryObserv_IsRunnyNose = 2 or RO.RepositoryObserv_IsSoreThroat = 2 or RO.RepositoryObserv_IsHighTemperature = 2 or RO.Dyspnea_id > 1 or RO.Cough_id > 1 then 1 else 0 end as psign 
					from v_RepositoryObserv RO with(nolock)
					where (1=1) and RO.PersonQuarantine_id = PQ.PersonQuarantine_id
					order by RO.repositoryobserv_id desc
				) RO
				left join v_PersonQuarantineOpenReason PQOR with(nolock) on PQOR.PersonQuarantineOpenReason_id = PQ.PersonQuarantineOpenReason_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = PQ.MedStaffFact_id
				-- end from
			where 
				-- where
				(1=1)
				and PQ.PersonQuarantine_endDT IS NULL
				and MSF.MedPersonal_id = :MedPersonal_id or
				exists (select top 1 personcard_id 
					from v_PersonCard PC with (nolock)  
						left join v_MedStaffRegion MedStaffRegion with (NOLOCK) on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
						left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
					WHERE PC.Person_id = PQ.Person_id 
						and PQ.PersonQuarantine_endDT IS NULL
						and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @curDT)
						and MedStaffRegion.MedStaffRegion_isMain = 2
						and msf.MedPersonal_id = :MedPersonal_id
						and PC.LpuAttachType_id = 1
				)
				-- end where
			order by 
				-- order by
				QuarantineDays desc
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
			-- variables
				declare @begDate date = cast(:begDate as date);
				declare @endDate date = cast(:endDate as date);
				declare @getDT datetime = dbo.tzGetDate();
			-- end variables
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
				from dbo.v_PersonDisp PD with (nolock)
					left join dbo.v_PersonDispVizit oapdv with (nolock) on oapdv.PersonDisp_id = PD.PersonDisp_id and oapdv.PersonDispVizit_NextFactDate is null
					outer apply(
						select top 1 
							MP_L.MedPersonal_id as MedPersonal_id_last,
							MP_L.Person_Fio as MedPersonal_FIO_last
						from dbo.v_PersonDispHist PDH_L (nolock)
						left join dbo.v_MedPersonal MP_L (nolock) on MP_L.MedPersonal_id = PDH_L.MedPersonal_id
						where PDH_L.PersonDisp_id = PD.PersonDisp_id
						order by PDH_L.PersonDispHist_begDate desc					
					) mph_last

					outer apply (
						select top 1 msf.MedPersonal_id
						from v_PersonCard_all PC (nolock)
						left join v_MedStaffRegion MedStaffRegion with (NOLOCK) on MedStaffRegion.LpuRegion_id = PC.LpuRegion_id
						left join v_MedStaffFact msf on msf.MedStaffFact_id = MedStaffRegion.MedStaffFact_id and MedStaffRegion.Lpu_id = msf.Lpu_id
						where PC.Person_id = PD.Person_id 
						and (PC.PersonCard_endDate is null or cast(PC.PersonCard_endDate as datetime) > @getDT)
						and MedStaffRegion.MedStaffRegion_isMain = 2
						and LpuAttachType_id = 1
						order by PersonCard_begDate desc
					) as MedPersonal

				where (1=1)
				and PD.Lpu_id = :Lpu_id
				and ( PD.PersonDisp_endDate is null or PD.PersonDisp_endDate > @getDT)
				and isnull(oapdv.PersonDispVizit_NextDate, PD.PersonDisp_NextDate) is not null
				and (mph_last.MedPersonal_id_last = :MedPersonal_id or MedPersonal.MedPersonal_id = :MedPersonal_id)
				and (cast(isnull(oapdv.PersonDispVizit_NextDate,PD.PersonDisp_NextDate) as date) >= @begDate and cast(isnull(oapdv.PersonDispVizit_NextDate,PD.PersonDisp_NextDate) as date) <= @endDate)

			)
			-- end with
			SELECT 
				-- select
				SIPD.PersonDisp_id,
				SIPD.PersonDispVizit_id,
				isnull(rtrim(PS.Person_SurName),'')+isnull(' '+rtrim(PS.Person_FirName),'')+isnull(' '+rtrim(PS.Person_SecName),'') as Person_Fio,
				PS.Person_id,
				PS.Server_id,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(PS.Person_BirthDay, @getDT) as Person_Age,
				convert(varchar(10), PD.PersonDisp_begDate, 104) as PersonDisp_begDate, -- взят
				convert(varchar(10), PD.PersonDisp_endDate, 104) as PersonDisp_endDate, -- снят
				dg1.Diag_FullName,
				convert(varchar(10), isnull(SIPD.PersonDispVizit_NextDate, PD.PersonDisp_NextDate), 104) as PersonDisp_NextDate, -- дата след явки /дата планового осмотра
				PS.Person_Phone
				-- end select
			FROM 
				-- from
				SignalInfoPersonDisp SIPD with (nolock)
				left join dbo.v_PersonDisp PD with (nolock) on SIPD.PersonDisp_id = PD.PersonDisp_id
				left join dbo.v_PersonState PS with (nolock) on SIPD.Person_id = PS.Person_id
				left join v_Diag dg1 with (nolock) on PD.Diag_id = dg1.Diag_id
				outer apply(
					select top 1
						EVPL.EvnVizitPL_setDT as PersonDisp_LastDate
					from
						v_EvnVizitPL EVPL with (nolock)
						left join v_VizitType VT with(nolock) on VT.VizitType_id = EVPL.VizitType_id
					where (1=1)
						and VT.VizitType_SysNick='disp'
						and cast(PD.PersonDisp_begDate as date)<=cast(EVPL.EvnVizitPL_setDT as date)
						and EVPL.Person_id = SIPD.Person_id
						and EVPL.Diag_id = SIPD.Diag_id 
					order by
						EVPL.EvnVizitPL_setDT desc
				) LD
				outer apply(
					select top 1 pdv.PersonDispVizit_NextFactDate 
					from dbo.v_PersonDispVizit pdv with (nolock) 
					where pdv.PersonDisp_id = SIPD.PersonDisp_id 
					order by pdv.PersonDispVizit_NextFactDate desc
				) lapdv
				-- end from
			where 
				-- where
				(1=1)
				and case when LD.PersonDisp_LastDate is not null or lapdv.PersonDispVizit_NextFactDate is not null
						then case when isnull(SIPD.PersonDispVizit_NextDate,PD.PersonDisp_NextDate) > (case when LD.PersonDisp_LastDate > lapdv.PersonDispVizit_NextFactDate then LD.PersonDisp_LastDate else lapdv.PersonDispVizit_NextFactDate end) then 1 end 
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
	 * @param $data
	 */
	function loadDistObservList($data){
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'begDate' => date('Y-m-d', strtotime($data['DistObserv_Range']))
			// 'endDate' => date('Y-m-d', strtotime($data['PersonDispInfo_prmDate_Range'][1]))
		);
		$query = "
			select DISTINCT
				vp.Person_id,
				vp.Server_id,
				vp.Person_SurName as Person_Surname,
				vp.Person_FirName as Person_Firname,
				vp.Person_SecName as Person_Secname,
				CONVERT(varchar(10), vp.Person_Birthday, 104) AS Person_Birthday,
				dbo.Age2(vp.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				vhrg.HypertensionRiskGroup_Name as HypertensionRiskGroup,
				SP.Value+'/'+DP.Value as AD,
				vlosi.LabelObserveSignalInfo_MonthCount as Day_Count,
				adr.Address_Address,
				vp.Person_Phone			
			from 
				v_LabelObserveSignalInfo vlosi with (nolock)			    
				INNER JOIN v_PersonState vp with(nolock) on vlosi.Person_id = vp.Person_id
				inner join v_PersonDisp v (nolock) on v.Person_id = vp.Person_id
				inner join v_LabelObserveChart vlc (nolock) on vlc.PersonDisp_id = v.PersonDisp_id
				inner join v_LabelObserveChartMeasure vm (nolock) on vm.LabelObserveChartMeasure_id = vlosi.LabelObserveChartMeasure_id
				inner join v_HypertensionRiskGroup vhrg with (nolock) on vlc.HypertensionRiskGroup_id = vhrg.HypertensionRiskGroup_id
				outer apply(
					select top 1 
						vm2.LabelObserveChartMeasure_Value as Value
					from 
						v_LabelObserveChartMeasure vm2 (nolock)
						inner join LabelObserveChartRate CR (nolock) on CR.LabelObserveChartRate_id = vm2.LabelObserveChartRate_id
					where 
						convert(varchar(16),vm2.LabelObserveChartMeasure_insDT,120)=convert(varchar(16),vm.LabelObserveChartMeasure_insDT,120)
				   		and CR.RateType_id=53
				) as SP
				outer apply(
					select top 1 
						vm2.LabelObserveChartMeasure_Value as Value
					from 
						v_LabelObserveChartMeasure vm2 (nolock)
						inner join LabelObserveChartRate CR (nolock) on CR.LabelObserveChartRate_id = vm2.LabelObserveChartRate_id
					where 
						convert(varchar(16),vm2.LabelObserveChartMeasure_insDT,120)=convert(varchar(16),vm.LabelObserveChartMeasure_insDT,120)
			   			and CR.RateType_id=54
				) as DP
				left join v_Address adr (nolock) on adr.Address_id = vp.PAddress_id
			where 
				vlc.LabelObserveChart_endDate is null
				and vp.Lpu_id = :Lpu_id
				and vlosi.LabelObserveSignalInfo_MonthCount > 6
			order by
				vp.Person_SurName,
				vp.Person_FirName,
				vp.Person_SecName
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
			-- variables
			declare @date date = dbo.tzGetDate();
			declare @date3m date = dateadd(month, -3, dbo.tzGetDate());
			-- end variables
		
			-- addit with
			with e as (
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
					v_EvnPL EPL (nolock)
					inner join v_MorbusDiag MD (nolock) on MD.MorbusType_id = 2 and MD.Diag_id = EPL.Diag_id
					left join v_PersonPregnancy PP (nolock) on PP.Evn_id = EPL.EvnPL_id
					outer apply (select top 1 MedStaffFact_id from v_EvnVizitPL  (nolock) where EvnVizitPL_pid = EPL.EvnPL_id and EvnVizitPL_Index = EvnVizitPL_Count - 1) EVPL 
					left join v_ResultClass RC (nolock) on RC.ResultClass_id = EPL.ResultClass_id 
				WHERE 
					EPL.EvnPL_setDate >= @date3m
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
					v_EvnPS EPS (nolock)
					inner join v_MorbusDiag MD (nolock) on MD.MorbusType_id = 2 and MD.Diag_id = EPS.Diag_id
					left join v_PersonPregnancy PP (nolock) on PP.Evn_id = EPS.EvnPS_id
					outer apply (select top 1 MedStaffFact_id from v_EvnSection  (nolock) where EvnSection_pid = EPS.EvnPS_id and EvnSection_Index = EvnSection_Count - 1) ES 
					left join v_LeaveType LT (nolock) on LT.LeaveType_id = EPS.LeaveType_id 
				WHERE 
					EPS.EvnPS_setDate >= @date3m
					and PP.PersonPregnancy_id is null
					and EPS.Diag_id not in (1944,1946,11071,11072,11073,11074,11075,11076,11077,11078,11079,11089,11090,11091)
			)
			-- end addit with
			
			select
			-- select
				e.Evn_id,
				e.EvnType,
				convert(varchar(10), E.Evn_setDate, 104) as Evn_setDate,
				convert(varchar(10), E.Evn_disDate, 104) as Evn_disDate,
				L.Lpu_id,
				L.Lpu_Nick,
				D.Diag_id,
				D.Diag_FullName,
				PS.Person_id,
				PS.Person_SurName + ' ' + PS.Person_FirName + isnull(' '+PS.Person_SecName,'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				LpuAttach.Lpu_id as LpuAttach_id,
				LpuAttach.Lpu_Nick as LpuAttach_Nick,
				isnull(PAddress.Address_Nick, PAddress.Address_Address) as Person_PAddress,
				MSF.Person_Fio as MedPersonal,
				case when exists(
					select * 
					from v_PersonQuarantine PQ (nolock)
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as PersonQuarantine_IsOn,
				e.Evn_NumCard, 
				e.EvnResult
			-- end select
			from
			-- from
				e 
				left join v_PersonState PS (nolock) on PS.Person_id = E.Person_id
				left join v_Lpu L (nolock) on L.Lpu_id = E.Lpu_id
				left join v_PersonCard PC (nolock) on PC.Person_id = E.Person_id and PC.LpuAttachType_id = 1
				left join v_Lpu_all LpuAttach (nolock) on LpuAttach.Lpu_id = PC.Lpu_id
				left join v_Address PAddress (nolock) on PAddress.Address_id = PS.PAddress_id
				left join v_Diag D (nolock) on D.Diag_id = e.Diag_id
				left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = e.MedStaffFact_id
			-- end from
			where 
			-- where
				(1=1)
				and not exists (
					select top 1 1 from v_PersonRegister PR (nolock)
					where PR.Person_id = E.Person_id
						and PR.PersonRegisterType_id = 2 -- общерегиональный
						and isnull(PR.PersonRegister_disDate, @date) >= E.Evn_setDate
				)
				and exists (
					select top 1 1 from v_PersonCard PCG (nolock)
						inner join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PCG.LpuRegion_id
						inner join v_MedStaffRegion msr (nolock) on msr.LpuRegion_id = lr.LpuRegion_id
					where
						PCG.LpuAttachType_id = 2 and
						PCG.Person_id = E.Person_id and 
						PCG.Lpu_id = :Lpu_id and 
						ISNULL(msr.MedStaffRegion_endDate, @date) >= @date and 
						msr.MedPersonal_id = :MedPersonal_id
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
