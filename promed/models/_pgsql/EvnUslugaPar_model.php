<?php
class EvnUslugaPar_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных о паракл. услуге
	 */
	function getEvnUslugaParViewData($data) {
		$accessType = 'EUP.Lpu_id = :Lpu_id';
		$join_msf = '';
		$params =  array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		if (isset($data['session']['CurMedStaffFact_id']))
		{
			$accessType .= ' and (EUP.MedPersonal_id is null or EUP.MedPersonal_id = MSF.MedPersonal_id) and EUP.LpuSection_uid = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}

		$selectPersonData = "PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || coalesce(PS.Person_SecName,'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = PS.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null 
					then ps.Person_SurName || ' ' || ps.Person_FirName || ' ' || coalesce(ps.Person_SecName,'') 
					else peh.PersonEncrypHIV_Encryp 
				end as \"Person_Fio\",
				null as \"Person_Birthday\",";
		}

		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.EvnPrescr_id as \"EvnPrescr_id\",
				EUP.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EUP.Person_id as \"Person_id\",
				EUP.PersonEvn_id as \"PersonEvn_id\",
				EUP.Server_id as \"Server_id\",
				EUP.Usluga_id as \"Usluga_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				EUP.EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				EUP.PayType_id as \"PayType_id\",
				EUP.PrehospDirect_id as \"PrehospDirect_id\",
				EUP.TimetablePar_id as \"TimetablePar_id\",
				EUP.EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				EUP.Lpu_id as \"Lpu_id\",
				EUP.LpuSection_uid as \"LpuSection_uid\",
				EUP.MedPersonal_id as \"MedStaffFact_uid\",
				DLpuSection.Lpu_id as \"Lpu_did\",
				EUP.LpuSection_did as \"LpuSection_did\",
				EUP.Org_did as \"Org_did\",
				EUP.MedPersonal_did as \"MedStaffFact_did\",
				EUP.MedPersonal_sid as \"MedStaffFact_sid\",
				{$selectPersonData}
				D.Diag_id as \"Diag_id\",
				coalesce(D.Diag_Code,'') as \"Diag_Code\",
				coalesce(D.Diag_Name,'') as \"Diag_Name\",
				coalesce(ucms.UslugaComplex_Name, UC.UslugaComplex_Name) as \"Usluga_Name\",
				EUP.EvnUslugaPar_id as \"Usluga_Number\",
				ULpu.Lpu_Nick as \"Lpu_Nick\",
				ULpu.Lpu_Name as \"Lpu_Name\",
				ULpu.UAddress_Address as \"Lpu_Address\",
				ULpuSection.LpuSection_Code as \"LpuSection_Code\",
				ULpuSection.LpuSection_Name as \"LpuSection_Name\",
				to_char(EUP.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
			    to_char(EUP.EvnUslugaPar_updDT, 'hh24:mi') as \"EvnUslugaPar_setTime\",
				MP.Person_SurName || ' ' || LEFT(MP.Person_FirName, 1)  || '. ' || coalesce(LEFT(MP.Person_SecName, 1) || '.', '') as \"MedPersonal_Fin\",
				DLpuSection.LpuSection_Code as \"DirectSubject_Code\",
                DLpuSection.LpuSection_Name as \"DirectSubject_Name\",
                DOrg.Org_Code as \"OrgDirectSubject_Code\",
                DOrg.Org_Nick as \"OrgDirectSubject_Name\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				DMedPersonal.Person_SurName || ' ' || LEFT(DMedPersonal.Person_FirName, 1)  || '. ' || coalesce(LEFT(DMedPersonal.Person_SecName, 1) || '.', '') as \"MedPersonalDirect_Fin\",
				case when EvnLabRequest.EvnLabRequest_id is null then 0 else 1 end as \"isLab\",
				EUP.Study_uid as \"Study_uid\",
				to_char(ecp.EvnCostPrint_setDT, 'dd.mm.yyyy') as \"EvnCostPrint_setDT\",
				ecp.EvnCostPrint_IsNoPrint as \"EvnCostPrint_IsNoPrint\",
				TRIM(to_Char(evncostprint_cost, '99999999999999999D99')) as \"CostPrint\"
			FROM v_EvnUslugaPar EUP
				left join v_EvnCostPrint ecp on ecp.Evn_id = EUP.EvnUslugaPar_id
				left join lateral (select * from v_Person_all PS where EUP.Person_id = PS.Person_id AND EUP.PersonEvn_id = PS.PersonEvn_id AND EUP.Server_id = PS.Server_id limit 1) as PS on true
				left join v_EvnDirection_all ED on EUP.EvnDirection_id = ED.EvnDirection_id and ED.DirFailType_id is null
				left join v_EvnLabRequest EvnLabRequest on EvnLabRequest.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = ed.EvnDirection_id
				left join v_EvnlabSample els on els.EvnLabSample_id = eup.EvnLabSample_id
				left join v_MedService MS on els.MedService_id = MS.MedService_id
				left join v_Lpu ULpu on coalesce(MS.Lpu_id,EUP.Lpu_id) = ULpu.Lpu_id
				left join v_LpuSection ULpuSection on coalesce(MS.LpuSection_id,EUP.LpuSection_uid) = ULpuSection.LpuSection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = coalesce(els.MedPersonal_aid,EUP.MedPersonal_id) AND MP.Lpu_id = coalesce(MS.Lpu_id,EUP.Lpu_id)
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EUP.UslugaComplex_id
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = els.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join v_Diag D on coalesce(eup.Diag_id, efr.Diag_id, ED.Diag_id) = D.Diag_id
				left join v_LpuSection DLpuSection on coalesce(EUP.LpuSection_did,ED.LpuSection_id) = DLpuSection.LpuSection_id
				left join v_Lpu DLpu on DLpu.Lpu_id = ED.Lpu_sid
				left join v_Org DOrg on coalesce(EUP.Org_did,DLpu.Org_id) = DOrg.Org_id
				left join v_MedPersonal DMedPersonal on coalesce(EUP.MedPersonal_did,ED.MedPersonal_id) = DMedPersonal.MedPersonal_id AND coalesce(DLpuSection.Lpu_id,ED.Lpu_id) = DMedPersonal.Lpu_id
				{$join_msf}
				{$joinPersonEncrypHIV}
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
			limit 1
		";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * @param array $data
	 * @return array|false
	 */
	function getEvnUslugaParViewDataLis($data) {
		$this->load->swapi('lis');
		$resp = $this->lis->GET('EvnUsluga/ParViewData', array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		), 'list');
		if (!$this->isSuccessful($resp) || empty($resp)) {
			return false;
		}

		$rows = [];
		foreach($resp as $item) {
			$fields = [];
			foreach($item as $key => $value) {
				switch(true) {
					case ($value === null):
						$fields[] = "CAST(null as varchar) as \"{$key}\"";
						break;
					case is_string($value):
						$fields[] = "CAST('{$value}' as varchar) as \"{$key}\"";
						break;
					default:
						$fields[] = "CAST({$value} as varchar) as \"{$key}\"";
						break;
				}
			}
			$rows[] = "select ".implode(",", $fields);
		}
		$rows = implode("\nunion\n", $rows);

		$params =  array(
			'Lpu_id' => $data['Lpu_id']
		);

		$accessType = 'row_list."Lpu_id" = :Lpu_id';
		$join_msf = '';
		if (isset($data['session']['CurMedStaffFact_id'])) {
			$accessType .= ' and (row_list."MedPersonal_id" is null or CAST(row_list."MedPersonal_id" as bigint) = MSF.MedPersonal_id) and CAST(row_list."LpuSection_uid" as bigint) = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}

		$selectPersonData = "PS.Person_SurName||' '||PS.Person_FirName||' '||COALESCE(PS.Person_SecName,'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = PS.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null 
					then ps.Person_SurName||' '||ps.Person_FirName||' '||COALESCE(ps.Person_SecName,'') 
					else peh.PersonEncrypHIV_Encryp 
				end as \"Person_Fio\",
				null as \"Person_Birthday\",";
		}

		$query = "
			with row_list as (
				{$rows}
			)
			select
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				{$selectPersonData}
				row_list.*
			from row_list
			left join v_Person_all PS on PS.PersonEvn_id = CAST(row_list.\"PersonEvn_id\" as bigint)
			{$join_msf}
			{$joinPersonEncrypHIV}
			limit 1
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка услуг
	 */
	function loadEvnUslugaParWorkPlace($data)
	{
		if (empty($data['date_range'][0]) || empty($data['date_range'][1]))
		{
			return false;
		}

		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['LpuSection_id'] = $data['LpuSection_id'];
		$params['begDate'] = $data['date_range'][0];
		$params['endDate'] = $data['date_range'][1];
		/*
		Услуги по записи фильтруем по TimetablePar_begTime
		Услуги оказанные из очереди (EQ.EvnQueue_id is not null AND EQ.EvnQueue_recDT is not null AND TTP.TimetablePar_begTime is null) фильтруем по EvnQueue_recDT
		Услуги без записи (EQ.EvnQueue_id is null AND TTP.TimetablePar_begTime is null) фильтруем по EvnUslugaPar_insDT
		Услуги находящиеся в очереди (EQ.EvnQueue_id is not null AND EQ.EvnQueue_recDT is null AND TTP.TimetablePar_begTime is null)выводим все
		*/
		$filter = " AND cast(COALESCE(TTP.TimetablePar_begTime, EQ.EvnQueue_recDT, (case when EQ.EvnQueue_id is null then EUP.EvnUslugaPar_insDT else dbo.tzGetDate() end)) as date) >= cast(:begDate as date)
				AND cast(COALESCE(TTP.TimetablePar_begTime, EQ.EvnQueue_recDT, (case when EQ.EvnQueue_id is null then EUP.EvnUslugaPar_insDT else dbo.tzGetDate() end)) as date) <= cast(:endDate as date)";
		// для сокрытия услуг из очереди
		if ($data['Usluga_isFromQueue'] == 1 )
		{
			$filter .= " AND EQ.EvnQueue_id is null";
		}
		if (!empty($data['Person_SurName']))
		{
			$filter .= " AND PS.Person_Surname ILIKE :Person_SurName";
			$params['Person_SurName'] = $data['Person_SurName'].'%';
		}
		if (!empty($data['Person_FirName']))
		{
			$filter .= " AND PS.Person_Firname ILIKE :Person_FirName";
			$params['Person_FirName'] = $data['Person_FirName'].'%';
		}
		if (!empty($data['Person_SecName']))
		{
			$filter .= " AND PS.Person_Secname ILIKE :Person_SecName";
			$params['Person_SecName'] = $data['Person_SecName'].'%';
		}
		if (!empty($data['Person_BirthDay']))
		{
			$filter .= " AND cast(PS.Person_BirthDay as date) = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if (!empty($data['UslugaComplex_id']))
		{
			$filter .= " AND EUP.UslugaComplex_id = :UslugaComplex_id";
			$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		$sql = "
			SELECT
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EUP.TimetablePar_id as \"TimetablePar_id\",
				to_char(TTP.TimetablePar_begTime, 'dd.mm.yyyy') as \"TimetablePar_Date\",
				to_char(TTP.TimetablePar_begTime, 'HH24:MI') as \"TimetablePar_begTime\",
				EUP.EvnUslugaPar_setTime as \"TimetablePar_factTime\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				EUP.Person_id as \"Person_id\",
				EUP.PersonEvn_id as \"PersonEvn_id\",
				EUP.Server_id as \"Server_id\",
				EUP.Usluga_id as \"Usluga_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.Lpu_id as \"Lpu_id\",
				EUP.Lpu_did as \"Lpu_did\",
				EUP.LpuSection_uid as \"LpuSection_uid\",
				EUP.LpuSection_did as \"LpuSection_did\",
				EUP.MedPersonal_id as \"MedPersonal_id\",
				EUP.MedPersonal_did as \"MedPersonal_did\",
				EUP.MedPersonal_sid as \"MedPersonal_sid\",
				to_char(EUP.EvnUslugaPar_insDT, 'dd.mm.yyyy') as \"EvnUslugaPar_insDT\",
				case when EUP.EvnUslugaPar_setDT is not null then 'true' else 'false' end as \"Service_isProvided\",
				case when (TTP.TimetablePar_begTime is not null)
					then to_char(TTP.TimetablePar_begTime, 'dd.mm.yyyy HH24:MI')
					else case when EQ.EvnQueue_recDT is not null then to_char(EQ.EvnQueue_recDT, 'dd.mm.yyyy') || ' принят из очереди' 
					else case when EQ.EvnQueue_id is not null then 'очередь' else 'б/з' end end
				end as \"Record_date\",
				case when TTP.TimetablePar_begTime is not null 
					then to_char(TTP.TimetablePar_begTime, 'dd.mm.yyyy')
					else case when EQ.EvnQueue_recDT is not null then to_char(EQ.EvnQueue_recDT, 'dd.mm.yyyy')
					else case when EQ.EvnQueue_id is not null then 'очередь' else to_char(EUP.EvnUslugaPar_insDT, 'dd.mm.yyyy') end end
				end as \"Group_date\",
				PS.Person_SurName as \"Person_Surname\",
				PS.Person_FirName as \"Person_Firname\",
				PS.Person_SecName as \"Person_Secname\",
				PS.Person_Fio as \"Person_FIO\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				case when Person.Person_deadDT is not null then 'true' else 'false' end as \"Person_IsDead\",
				case when ED.EvnDirection_id is not null then 'true' else 'false' end as \"Direction_exists\",
				case when Person.Server_id = 0 then 'true' else 'false' end as \"Person_IsBDZ\",
				-- ЛПУ прикрепления
				Lpu.Lpu_Nick as \"Person_Lpu\",
				case when EUP.UslugaComplex_id is not null then UC.UslugaComplex_Name else U.Usluga_Name end as \"Usluga_Name\",
				case when EUP.EvnUslugaPar_isCito = 2 then 'true' else 'false' end as \"Service_isCito\",
				to_char(EUP.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUslugaPar_setDT\",
				MP.Person_Fin as \"MedPersonal_Fin\",
				EUP.pmUser_insID as \"pmUser_insID\"
			FROM v_EvnUslugaPar EUP 
				left join lateral (select * from v_Person_all PS where EUP.Person_id = PS.Person_id AND EUP.PersonEvn_id = PS.PersonEvn_id AND EUP.Server_id = PS.Server_id limit 1) as PS on true 
				left join Person on PS.Person_id = Person.Person_id 
				left join PersonCardState PersonCard on PS.Person_id = PersonCard.Person_id AND PersonCard.LpuAttachType_id = 1 AND cast(EUP.EvnUslugaPar_updDT as date) >= cast(PersonCard.PersonCardState_begDate as DATE) AND (PersonCard.PersonCardState_endDate is null OR cast(EUP.EvnUslugaPar_updDT as DATE) <= cast(PersonCard.PersonCardState_endDate as DATE))
				left join v_Lpu Lpu on PersonCard.Lpu_id = Lpu.Lpu_id
				left join v_TimetablePar TTP on EUP.TimetablePar_id = TTP.TimetablePar_id
				left join v_EvnDirection_all ED on TTP.TimetablePar_id = ED.TimetablePar_id and ED.DirFailType_id is null
				left join v_EvnQueue EQ on EUP.EvnUslugaPar_id = EQ.EvnUslugaPar_id AND EQ.TimetablePar_id is null
				left join v_MedPersonal MP on EUP.MedPersonal_id = MP.MedPersonal_id AND MP.Lpu_id = EUP.Lpu_id
				left join v_Usluga U on EUP.Usluga_id = U.Usluga_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EUP.UslugaComplex_id
			WHERE
				EUP.Lpu_id = :Lpu_id
				AND EUP.LpuSection_uid = :LpuSection_id
				{$filter}
			ORDER BY
				ps.Person_Surname ASC,ps.Person_Firname ASC,ps.Person_Secname ASC
			limit 100
		";

		$res = $this->db->query($sql,$params);

		if ( is_object($res) ) {

			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * Удаление услуги
	 */
	function deleteEvnUslugaPar($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnUslugaPar_del(
				EvnUslugaPar_id := :EvnUslugaPar_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление параклинической услуги)'));
		}
	}

	/**
	 * Получение списка направлений
	 */
	function loadEvnDirectionList($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		if ( isset($data['EvnDirection_setDate_From']) ) {
			$filter .= " and ED.EvnDirection_setDate >= cast(:EvnDirection_setDate_From as timestamp)";
			$queryParams['EvnDirection_setDate_From'] = $data['EvnDirection_setDate_From'];
		}

		if ( isset($data['EvnDirection_setDate_To']) ) {
			$filter .= " and ED.EvnDirection_setDate <= cast(:EvnDirection_setDate_To as timestamp)";
			$queryParams['EvnDirection_setDate_To'] = $data['EvnDirection_setDate_To'];
		}

		$query = "
			select
				ED.EvnDirection_id as \"EvnDirection_id\",
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				ED.Lpu_did as \"Lpu_did\",
				to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ED.EvnDirection_setTime as \"EvnDirection_setTime\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				RTRIM(PS.Person_SurName) as \"Person_Surname\",
				RTRIM(PS.Person_FirName) as \"Person_Firname\",
				RTRIM(PS.Person_SecName) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				RTRIM(Lpu.Lpu_Nick) as \"Lpu_Name\",
				MP.Person_Fio as \"MedPersonal_Fio\"
			from
				v_EvnDirection_all ED
				inner join v_PersonState PS on PS.Person_id = ED.Person_id
				inner join v_Lpu Lpu on Lpu.Lpu_id = ED.Lpu_id
				inner join v_MedPersonal MP on MP.MedPersonal_id = ED.MedPersonal_id
					and MP.Lpu_id = ED.Lpu_id
			where " . $filter . "
				and ED.Lpu_did = :Lpu_did
				and ED.LpuSection_did = :LpuSection_did
		";

		$queryParams['Lpu_did'] = $data['Lpu_id'];
		$queryParams['LpuSection_did'] = $data['LpuSection_id'];

		// echo getDebugSQL($query, $queryParams); exit();

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка формы редактирования пар услуги
	 */
	function loadEvnUslugaParEditForm($data) {
		$accessType = 'EUP.Lpu_id = :Lpu_id';
		//$accessType .= ' and (ED.EvnDirection_IsReceive = 2 OR (ED.MedService_id IS NULL and not exists(select EvnFuncRequest_id from v_EvnFuncRequest where EvnFuncRequest_pid = EUP.EvnUslugaPar_id limit 1)))'; // не даём редактировать услуги связанные с направлением в лабораторию и с заявкой ФД
		$join_msf = '';
		$params =  array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		// правильнее доступ определять по рабочему месту
		if ((!isSuperAdmin() || !isLpuAdmin() || !isLpuUser()) && empty($data['session']['isMedStatUser']) && !empty($data['session']['medpersonal_id']))
		{
			$accessType .= ' and coalesce(EUP.MedPersonal_id,:user_MedPersonal_id) = :user_MedPersonal_id';
			$params['user_MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		if ( $data['session']['region']['nick'] == 'pskov' ) {
			$accessType .= " and coalesce(EUP.EvnUslugaPar_IsPaid, 1) = 1
				and not exists(
					select RD.Registry_id
					from r60.v_RegistryData RD
						inner join v_Registry R on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EUP.EvnUslugaPar_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
					limit 1
				)
			";
		}

		$query = "
			SELECT
				case when {$accessType} then 'edit' else 'view' end as \"accessType\",
				case when ED.MedService_id IS NULL and not exists(select EvnFuncRequest_id from v_EvnFuncRequest where EvnFuncRequest_pid = EUP.EvnUslugaPar_id limit 1) then '0' else '1' end as \"fromMedService\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EUP.EvnDirection_id as \"EvnDirection_id\",
				EUP.MedStaffFact_id as \"MedStaffFact_uid\",
				coalesce(EDH.EvnDirectionHistologic_id,EDH2.EvnDirectionHistologic_id) as \"EvnDirectionHistologic_id\",
				case when ED.EvnDirection_id IS NOT NULL then ED.EvnDirection_Num else EUP.EvnDirection_Num end as \"EvnDirection_Num\",
				case when ed.EvnDirection_id is null then 1 else 2 end as \"EvnUslugaPar_IsWithoutDirection\",
				to_char(case when ED.EvnDirection_id IS NOT NULL then ED.EvnDirection_setDT else EUP.EvnDirection_setDT end, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				EUP.EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				EUP.TimetablePar_id as \"TimetablePar_id\",
				EUP.Person_id as \"Person_id\",
				EUP.PersonEvn_id as \"PersonEvn_id\",
				EUP.UslugaPlace_id as \"UslugaPlace_id\",
				EUP.EvnUslugaPar_MedPersonalCode as \"EvnUslugaPar_MedPersonalCode\",
				coalesce(EUP.Lpu_uid, l3.Lpu_id, case when EUP.Lpu_id = :Lpu_id then null else EUP.Lpu_id end) as \"Lpu_uid\",
				EUP.Org_uid as \"Org_uid\",
				coalesce(EUP.LpuSectionProfile_id, LS.LpuSectionProfile_id) as \"LpuSectionProfile_id\",
				EUP.MedSpecOms_id as \"MedSpecOms_id\",
				EUP.Server_id as \"Server_id\",
				case when ED.PrehospDirect_id IS NOT NULL then ED.PrehospDirect_id else EUP.PrehospDirect_id end as \"PrehospDirect_id\",
				case when ED.LpuSection_id IS NOT NULL then ED.LpuSection_id else EUP.LpuSection_did end as \"LpuSection_did\",
				case when ED.Lpu_sid IS NOT NULL then ED.Lpu_sid else EUP.Lpu_did end as \"Lpu_did\",
				coalesce(Org.Org_id, l.Org_id, EUP.Org_did, l2.Org_id) as \"Org_did\",
				case when ED.MedPersonal_id IS NOT NULL then ED.MedPersonal_id else EUP.MedPersonal_did end as \"MedPersonal_did\",
				to_char(EUP.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				to_char(EUP.EvnUslugaPar_setTime, 'HH24:MI') as \"EvnUslugaPar_setTime\",
				to_char(EUP.EvnUslugaPar_disDT, 'dd.mm.yyyy') as \"EvnUslugaPar_disDate\",
				to_char(EUP.EvnUslugaPar_disTime, 'HH24:MI') as \"EvnUslugaPar_disTime\",
				EUP.LpuSection_uid as \"LpuSection_uid\",
				EUP.MedPersonal_id as \"MedPersonal_uid\",
				EUP.MedPersonal_sid as \"MedPersonal_sid\",
				EUP.Usluga_id as \"Usluga_id\",
				EUP.EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				EUP.PayType_id as \"PayType_id\",
				EC.XmlTemplate_id as \"XmlTemplate_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.FSIDI_id as \"FSIDI_id\",
				EUP.Diag_id as \"Diag_id\",
				EUP.DeseaseType_id as \"DeseaseType_id\",
				EUP.TumorStage_id as \"TumorStage_id\",
				EUP.Mes_id as \"Mes_id\",
				EC.UslugaCategory_id as \"UslugaCategory_id\",
				ucat.UslugaCategory_Name as \"UslugaCategory_Name\",
				coalesce(EUP.EvnUslugaPar_IsPaid, 1) as \"EvnUslugaPar_IsPaid\",
				coalesce(EUP.EvnUslugaPar_IndexRep, 0) as \"EvnUslugaPar_IndexRep\",
				coalesce(EUP.EvnUslugaPar_IndexRepInReg, 1) as \"EvnUslugaPar_IndexRepInReg\",
				EUP.MedProductCard_id as \"MedProductCard_id\",
				EUP.UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				ED.Diag_id as \"DirectionDiag_id\"
			FROM
				v_EvnUslugaPar EUP
				left join v_EvnDirection_all ED on EUP.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnDirectionHistologic EDH on EDH.EvnDirectionHistologic_id = ED.EvnDirection_id
				left join v_EvnDirectionHistologic EDH2 on EDH2.EvnDirectionHistologic_id = EUP.EvnDirection_id
				left join Org on Org.Org_id = ED.Org_sid
				left join v_Lpu l on l.Lpu_id = ED.Lpu_sid
				left join v_Lpu l2 on l2.Lpu_id = EUP.Lpu_did
				left join v_Lpu l3 on l3.Org_id = EUP.Org_uid
				left join v_LpuSection LS on LS.LpuSection_id = EUP.LpuSection_uid
				left join v_UslugaComplex EC on EUP.UslugaComplex_id = EC.UslugaComplex_id
				left join v_UslugaCategory ucat on ucat.UslugaCategory_id = ec.UslugaCategory_id
				{$join_msf}
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
			limit 1
		";

		// это условие наверно лишнее, если EUP.Lpu_id != :Lpu_id то будет на просмотр
		//and (EUP.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		//echo getDebugSQL($query, $params); exit();

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение части полей направления
	 */
	function updateEvnDirectionFields($data) {
		// обновляем поля в услуге
		$resp = $this->queryResult("
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				to_char(EvnUslugaPar_setDT, 'dd-mm-yyyy') as \"EvnUslugaPar_setDT\",
				to_char(EvnUslugaPar_disDT, 'dd-mm-yyyy') as \"EvnUslugaPar_disDT\",
				to_char(EvnUslugaPar_didDT, 'dd-mm-yyyy') as \"EvnUslugaPar_didDT\",
				Morbus_id as \"Morbus_id\",
				EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				to_char(EvnUslugaPar_signDT, 'dd-mm-yyyy') as \"EvnUslugaPar_signDT\",
				PayType_id as \"PayType_id\",
				Usluga_id as \"Usluga_id\",
				MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				LpuSection_uid as \"LpuSection_uid\",
				EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				Org_uid as \"Org_uid\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				MedPersonal_sid as \"MedPersonal_sid\",
				EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				EvnDirection_id as \"EvnDirection_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				MesOperType_id as \"MesOperType_id\",
				EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnPrescrTimetable_id as \"EvnPrescrTimetable_id\",
				EvnCourse_id as \"EvnCourse_id\",
				Lpu_oid as \"Lpu_oid\",
				PrehospDirect_id as \"PrehospDirect_id\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				MedPersonal_did as \"MedPersonal_did\",
				TimetablePar_id as \"TimetablePar_id\",
				EvnLabSample_id as \"EvnLabSample_id\",
				Study_uid as \"Study_uid\",
				EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				to_char(EvnUslugaPar_ResultAppDate, 'dd-mm-yyyy') as \"EvnUslugaPar_ResultAppDate\",
				EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\"
			from
				v_EvnUslugaPar
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		", array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));

		$query = "
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnUslugaPar_upd(
				EvnUslugaPar_id := :EvnUslugaPar_id,
				EvnUslugaPar_pid := :EvnUslugaPar_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaPar_setDT := :EvnUslugaPar_setDT,
				EvnUslugaPar_disDT := :EvnUslugaPar_disDT,
				EvnUslugaPar_didDT := :EvnUslugaPar_didDT,
				Morbus_id := :Morbus_id,
				EvnUslugaPar_IsSigned := :EvnUslugaPar_IsSigned,
				pmUser_signID := :pmUser_signID,
				EvnUslugaPar_signDT := :EvnUslugaPar_signDT,
				PayType_id := :PayType_id,
				Usluga_id := :Usluga_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				LpuSection_uid := :LpuSection_uid,
				EvnUslugaPar_Kolvo := :EvnUslugaPar_Kolvo,
				Org_uid := :Org_uid,
				UslugaComplex_id := :UslugaComplex_id,
				EvnUslugaPar_isCito := :EvnUslugaPar_isCito,
				MedPersonal_sid := :MedPersonal_sid,
				EvnUslugaPar_Result := :EvnUslugaPar_Result,
				EvnDirection_id := :EvnDirection_id,
				UslugaComplexTariff_id := :UslugaComplexTariff_id,
				EvnUslugaPar_CoeffTariff := :EvnUslugaPar_CoeffTariff,
				MesOperType_id := :MesOperType_id,
				EvnUslugaPar_IsModern := :EvnUslugaPar_IsModern,
				EvnUslugaPar_Price := :EvnUslugaPar_Price,
				EvnUslugaPar_Summa := :EvnUslugaPar_Summa,
				EvnPrescr_id := :EvnPrescr_id,
				EvnPrescrTimetable_id := :EvnPrescrTimetable_id,
				EvnCourse_id := :EvnCourse_id,
				Lpu_oid := :Lpu_oid,
				PrehospDirect_id := :PrehospDirect_id,
				LpuSection_did := :LpuSection_did,
				Lpu_did := :Lpu_did,
				Org_did := :Org_did,
				MedPersonal_did := :MedPersonal_did,
				TimetablePar_id := :TimetablePar_id,
				EvnLabSample_id := :EvnLabSample_id,
				Study_uid := :Study_uid,
				EvnUslugaPar_ResultValue := :EvnUslugaPar_ResultValue,
				EvnUslugaPar_ResultLower := :EvnUslugaPar_ResultLower,
				EvnUslugaPar_ResultUpper := :EvnUslugaPar_ResultUpper,
				EvnUslugaPar_ResultUnit := :EvnUslugaPar_ResultUnit,
				EvnUslugaPar_ResultApproved := :EvnUslugaPar_ResultApproved,
				EvnUslugaPar_ResultAppDate := :EvnUslugaPar_ResultAppDate, 
				EvnUslugaPar_ResultCancelReason := :EvnUslugaPar_ResultCancelReason,
				EvnUslugaPar_Comment := :EvnUslugaPar_Comment,
				EvnUslugaPar_ResultLowerCrit := :EvnUslugaPar_ResultLowerCrit, 
				EvnUslugaPar_ResultUpperCrit := :EvnUslugaPar_ResultUpperCrit,
				EvnUslugaPar_ResultQualitativeNorms := :EvnUslugaPar_ResultQualitativeNorms,
				EvnUslugaPar_ResultQualitativeText := :EvnUslugaPar_ResultQualitativeText,
				RefValues_id := :RefValues_id,
				Unit_id := :Unit_id,
				EvnUslugaPar_Regime := :EvnUslugaPar_Regime,
				EvnUslugaPar_IndexRep := :EvnUslugaPar_IndexRep,
				EvnUslugaPar_IndexRepInReg := :EvnUslugaPar_IndexRepInReg,
				pmUser_id := :pmUser_id
			)
		";

		$resp[0]['PrehospDirect_id'] = $data['PrehospDirect_id'];
		$resp[0]['Org_did'] = $data['Org_did'];
		$resp[0]['LpuSection_did'] = $data['LpuSection_did'];
		$resp[0]['MedPersonal_did'] = $data['MedPersonal_did'];
		$resp[0]['pmUser_id'] = $data['pmUser_id'];
		$resp[0]['EvnUslugaPar_IndexRep'] = (!empty($data['EvnUslugaPar_IndexRep']) ? $data['EvnUslugaPar_IndexRep'] : 0);
		$resp[0]['EvnUslugaPar_IndexRepInReg'] = (!empty($data['EvnUslugaPar_IndexRepInReg']) ? $data['EvnUslugaPar_IndexRepInReg'] : 0);
		$result = $this->queryResult($query, $resp[0]);

		// обновляем поля в направлении
		if (!empty($resp[0]['EvnDirection_id'])) {
			// меняем только часть полей, остальное начитываем.
			$this->load->model('EvnDirectionAll_model');
			$this->EvnDirectionAll_model->setAttributes(array('EvnDirection_id' => $resp[0]['EvnDirection_id']));

			$this->EvnDirectionAll_model->setAttribute('prehospdirect_id', $data['PrehospDirect_id']);
			if (!empty($data['PrehospDirect_id']) && $data['PrehospDirect_id'] != 1) {
				// получаем МО из организации
				$this->load->model("Org_model");
				$data['Lpu_sid'] = $this->Org_model->getLpuOnOrg(array('Org_id' => $data['Org_did']));
				$this->EvnDirectionAll_model->setAttribute('lpu_sid', $data['Lpu_sid']);
			} else {
				$this->EvnDirectionAll_model->setAttribute('lpu_sid', null);
			}
			$this->EvnDirectionAll_model->setAttribute('num', $data['EvnDirection_Num']);
			$this->EvnDirectionAll_model->setAttribute('setdt', $data['EvnDirection_setDate']);
			$this->EvnDirectionAll_model->setAttribute('lpusection_id', $data['LpuSection_did']);
			$this->EvnDirectionAll_model->setAttribute('medpersonal_id', $data['MedPersonal_did']);

			return $this->EvnDirectionAll_model->_save();
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Загрузка формы просмотра пар услуг
	 */
	function loadEvnUslugaParViewForm($data) {
		// получаем шаблон просмотра
		$query = "
			SELECT
				XmlTemplate_HtmlTemplate as \"XmlTemplate_HtmlTemplate\",
				EvnXml_Data as \"EvnXml_Data\"
			FROM
				XmlTemplate
				inner join EvnXml on Evn_id = :Evn_id
					and EvnXml.XmlTemplate_id = XmlTemplate.XmlTemplate_id
			limit 1
		";
		//$data['EvnUslugaPar_id'] = 17667;
		$result = $this->db->query($query, [
			'Evn_id' => $data['EvnUslugaPar_id']
		]);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( is_array($res) && count($res) == 1 && !empty($res[0]['XmlTemplate_HtmlTemplate']) )
			{
				$html_template = $res[0]['XmlTemplate_HtmlTemplate'];
			}
			else
				$html_template = false;
			// данные шаблона
			if ( is_array($res) && count($res) == 1 )
				$evn_xml_data = $res[0]['EvnXml_Data'];
			else
				$evn_xml_data = false;
		}
		else {
			return false;
		}
		// прочая информация
		$query = "
			SELECT
				lp.Lpu_Nick as \"Lpu_Nick\",
				lp.UAddress_Address as \"UAddress_Address\",
				'000000001' as \"EvnUslugaPar_Num\",
				to_char(EUP.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				mp.Person_Fin as \"MedPersonal_FIO\",
				mp.MedPersonal_TabCode as \"MedPersonal_TabCode\",
				PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || coalesce(PS.Person_SecName, '') as \"Person_FIO\",
				(date_part('year', dbo.tzgetdate() - ps.Person_BirthDay) + 
					case when date_part('month', ps.Person_BirthDay) > date_part('month', dbo.tzgetdate())
						or (date_part('month', ps.Person_BirthDay) = date_part('month', dbo.tzgetdate())
							and date_part('day', ps.Person_BirthDay) > date_part('day', dbo.tzgetdate()))
						then -1
						else 0
					end
				) as \"Person_Age\",
				to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				sx.Sex_Name as \"Sex_Name\",
				pd.PrehospDirect_Name as \"PrehospDirect_Name\",
				u.Usluga_Name as \"Usluga_Name\",
				0 as \"Cabinet_Num\",
				'' as \"Diag_Code\",
				'' as \"Diag_Name\",
				UslugaComplex_id as \"UslugaComplex_id\"
			FROM
				v_EvnUslugaPar EUP
				inner join v_Lpu lp on EUP.Lpu_id = :Lpu_id and lp.Lpu_id = EUP.Lpu_id
				left join v_LpuSection ls on ls.LpuSection_id = EUP.LpuSection_uid
				left join v_MedPersonal mp on mp.MedPersonal_id = EUP.MedPersonal_id
				left join v_PersonState PS on PS.Person_id = EUP.Person_id
				left join Sex sx on sx.Sex_id = ps.Sex_id
				left join v_PrehospDirect pd on pd.PrehospDirect_id = EUP.PrehospDirect_id
				left join v_Usluga u on u.Usluga_id = EUP.Usluga_id
			WHERE (1 = 1)
				and EUP.EvnUslugaPar_id = :EvnUslugaPar_id
				and EUP.Lpu_id = :Lpu_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			$res = $result->result('array');
		}
		else {
			return false;
		}
		$ret = array('html_template' => $html_template, 'evnxml_data' => $evn_xml_data, 'usluga_data' => isset($res[0]) ? $res[0]: "");
		return $ret;
	}

	/**
	 * Загрузка поточного списка пар услуг
	 */
	function loadEvnUslugaParStreamList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and EUP.pmUser_insID = :pmUser_id";
		$queryParams['pmUser_id'] = $data['pmUser_id'];

		if ( (isset($data['begDate'])) && (isset($data['begTime'])) ) {
			$filter .= " and EUP.EvnUslugaPar_insDT >= cast(:EvnUslugaPar_insDT as timestamp)";
			$queryParams['EvnUslugaPar_insDT'] = $data['begDate'] . " " . $data['begTime'];
		}

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and EUP.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			SELECT
				case when EUP.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EUP.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as \"accessType\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.Person_id as \"Person_id\",
				EUP.PersonEvn_id as \"PersonEvn_id\",
				EUP.Server_id as \"Server_id\",
				RTRIM(PS.Person_SurName) as \"Person_Surname\",
				RTRIM(PS.Person_FirName) as \"Person_Firname\",
				RTRIM(PS.Person_SecName) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				to_char(EUP.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				PT.PayType_Name as \"PayType_Name\"
			FROM v_EvnUslugaPar EUP
				inner join v_PersonState PS on PS.Person_id = EUP.Person_id
				inner join v_LpuSection LS on LS.LpuSection_id = EUP.LpuSection_uid
				inner join lateral(
					select Person_Fio
					from v_MedPersonal
					where MedPersonal_id = EUP.MedPersonal_id
						and Lpu_id = EUP.Lpu_id
					limit 1
				) MP on true
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EUP.UslugaComplex_id
				inner join v_PayType PT on PT.PayType_id = EUP.PayType_id
			WHERE " . $filter . "
			ORDER BY
				EUP.EvnUslugaPar_id desc
		";

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function doSaveEvnUslugaPar($data) {
		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'LpuSection_uid' => $data['LpuSection_uid']
			//,'TimetablePar_Day' => $data['TimetablePar_Day']
		);

		$query = "
			SELECT
				case when EQ.EvnQueue_id is not null AND EQ.EvnQueue_recDT is null AND EQ.pmUser_recID is null AND EUP.EvnUslugaPar_setDT is null then 2 else 1 end as \"allowApplyFromQueue\", -- да при оказании услуги из очереди 
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.Person_id as \"Person_id\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				EUP.EvnDirection_id as \"UslugaEvnDirection_id\"
			FROM v_EvnUslugaPar EUP 
				left join v_EvnQueue EQ on EUP.EvnUslugaPar_id = EQ.EvnUslugaPar_id
				left join v_EvnDirection_all ED on EQ.EvnDirection_id = ED.EvnDirection_id AND ED.DirFailType_id is null
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
		";
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Сохранение пар услуги
     * @param $data
     * @return array
     * @throws Exception
     */
	function saveEvnUslugaPar($data) {
		// Стартуем транзакцию
		$this->db->trans_begin();

		$procedure = '';

		if ( (!isset($data['EvnUslugaPar_id'])) || ($data['EvnUslugaPar_id'] <= 0) ) {
			$procedure = 'p_EvnUslugaPar_ins';
		}
		else {
			$procedure = 'p_EvnUslugaPar_upd';
		}

		if ( isset($data['EvnUslugaPar_setTime']) ) {
			$data['EvnUslugaPar_setDate'] .= ' ' . $data['EvnUslugaPar_setTime'] . ':00';
		}

		if ( !empty($data['EvnUslugaPar_disDate']) && !empty($data['EvnUslugaPar_disTime']) ) {
			$data['EvnUslugaPar_disDate'] .= ' ' . $data['EvnUslugaPar_disTime'] . ':00';
		}
		if ( empty($data['EvnUslugaPar_disDate']) ) {
			$data['EvnUslugaPar_disDate'] = $data['EvnUslugaPar_setDate'];
		}

		if (!empty($data['UslugaPlace_id']) && $data['UslugaPlace_id'] == 1) {
			if (empty($data['MedPersonal_uid'])) {
				return array(array('Error_Msg' => 'Поле "Врач" обязательно для заполнения'));
			}
			if (empty($data['LpuSection_uid'])) {
				return array(array('Error_Msg' => 'Поле "Отделение" обязательно для заполнения'));
			}
		}

		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['EvnUslugaPar_id']) && !empty($data['EvnCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveEvnCostPrint(array(
				'Evn_id' => $data['EvnUslugaPar_id'],
				'CostPrint_IsNoPrint' => $data['EvnCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['EvnCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$query = "
			with mv1 as (
				select
					EvnPrescr_id
				from v_EvnPrescrDirection
				where EvnDirection_id = :EvnDirection_id
				limit 1
			), mv2 as (
				select
					e_child.Evn_id
				from
					v_EvnPrescr ep
					inner join v_Evn e on e.Evn_id = EvnPrescr_pid -- посещние/движение
					inner join v_Evn e_child on e_child.Evn_pid = e.Evn_pid -- посещения/движения той же КВС/ТАП
				where
					e_child.EvnClass_SysNick IN ('EvnSection', 'EvnVizitPL', 'EvnVizitPLStom')
					and EvnPrescr_id = (select EvnPrescr_id from mv1)
					and e_child.Evn_setDT <= :EvnUslugaPar_setDT
					and (e_child.Evn_disDT >= :EvnUslugaPar_setDT
						OR e_child.Evn_disDT IS NULL
					) -- актуальное
				limit 1
			)
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from " . $procedure . "(
				EvnUslugaPar_id := :EvnUslugaPar_id,
				EvnUslugaPar_pid := (select Evn_id from mv2),
				EvnDirection_id := :EvnDirection_id,
				EvnDirection_setDT := :EvnDirection_setDT,
				EvnDirection_Num := :EvnDirection_Num,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaPar_setDT := :EvnUslugaPar_setDT,
				EvnUslugaPar_disDT := :EvnUslugaPar_disDT,
				PayType_id := :PayType_id,
				EvnUslugaPar_isCito := :EvnUslugaPar_isCito,
				TimetablePar_id := :TimetablePar_id,
				Usluga_id := :Usluga_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				UslugaPlace_id := :UslugaPlace_id,
				LpuSection_uid := :LpuSection_uid,
				Lpu_uid := :Lpu_uid,
				MedSpecOms_id := :MedSpecOms_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				EvnUslugaPar_MedPersonalCode := :EvnUslugaPar_MedPersonalCode,
				EvnUslugaPar_Kolvo := :EvnUslugaPar_Kolvo,
				PrehospDirect_id := :PrehospDirect_id,
				LpuSection_did := :LpuSection_did,
				Lpu_did := :Lpu_did,
				Org_did := :Org_did,
				Org_uid := :Org_uid,
				MedPersonal_did := :MedPersonal_did,
				MedPersonal_sid := :MedPersonal_sid,
				UslugaComplex_id := :UslugaComplex_id,
				FSIDI_id := :FSIDI_id,
				Diag_id := :Diag_id,
				DeseaseType_id := :DeseaseType_id,
				TumorStage_id := :TumorStage_id,
				Mes_id := :Mes_id,
				UslugaComplexTariff_id := :UslugaComplexTariff_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := (select EvnPrescr_id from mv1),
				EvnUslugaPar_IndexRep := :EvnUslugaPar_IndexRep,
				EvnUslugaPar_IndexRepInReg := :EvnUslugaPar_IndexRepInReg,
				MedProductCard_id := :MedProductCard_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = [
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnDirection_setDT' => $data['EvnDirection_setDate'],
			'Lpu_uid' => $data['Lpu_uid'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'MedSpecOms_id' => $data['MedSpecOms_id'],
			'EvnUslugaPar_MedPersonalCode' => $data['EvnUslugaPar_MedPersonalCode'],
			'EvnDirection_Num' => $data['EvnDirection_Num'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDate'],
			'EvnUslugaPar_disDT' => $data['EvnUslugaPar_disDate'],
			'PayType_id' => $data['PayType_id'],
			'EvnUslugaPar_isCito' => $data['EvnUslugaPar_isCito'],
			'TimetablePar_id' => $data['TimetablePar_id'],
			'Usluga_id' => $data['Usluga_id'],
			'MedPersonal_id' => $data['MedPersonal_uid'],
			'MedStaffFact_id' => $data['MedStaffFact_uid'],
			'UslugaPlace_id' => $data['UslugaPlace_id'],
			'LpuSection_uid' => $data['LpuSection_uid'],
			'EvnUslugaPar_Kolvo' => $data['EvnUslugaPar_Kolvo'],
			'PrehospDirect_id' => $data['PrehospDirect_id'],
			'LpuSection_did' => $data['LpuSection_did'],
			'Lpu_did' => $data['Lpu_did'],
			'Org_did' => $data['Org_did'],
			'Org_uid' => $data['Org_uid'],
			'MedPersonal_did' => $data['MedPersonal_did'],
			'MedPersonal_sid' => $data['MedPersonal_sid'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'FSIDI_id' => $data['FSIDI_id'] ?? null,
			'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']))?$data['UslugaComplexTariff_id']: null,
			'Diag_id' => (!empty($data['Diag_id']))?$data['Diag_id']: null,
			'DeseaseType_id' => (!empty($data['DeseaseType_id']))?$data['DeseaseType_id']: null,
			'TumorStage_id'	=> (!empty($data['TumorStage_id']))?$data['TumorStage_id']: null,
			'Mes_id' => (!empty($data['Mes_id']))?$data['Mes_id']: null,
			'EvnUslugaPar_IndexRep' =>  (!empty($data['EvnUslugaPar_IndexRep']))?$data['EvnUslugaPar_IndexRep']: 0,
			'EvnUslugaPar_IndexRepInReg' =>  (!empty($data['EvnUslugaPar_IndexRepInReg']))?$data['EvnUslugaPar_IndexRepInReg']: 1,
			'MedProductCard_id' => $data['MedProductCard_id'],
			'pmUser_id' => $data['pmUser_id']
		];

		$this->load->helper('PersonNotice');
		//Инициализация хелпера рассылки сообщений о смене статуса
		$PersonNotice = new PersonNoticeEvn($data['Person_id']);
		$PersonNotice->loadPersonInfo(/*$data['PersonEvn_id'], $data['Server_id']*/);

		//Начинаем отслеживать статусы события EvnUslugaPar
		$PersonNotice->setEvnClassSysNick('EvnUslugaPar');
		$PersonNotice->setEvnId($data['EvnUslugaPar_id']);
		$PersonNotice->doStatusSnapshotFirst();

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при сохранении параклинической услуги'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение параклинической услуги)'));
		}
		else if ( strlen($response[0]['Error_Msg']) > 0  ) {
			$this->db->trans_rollback();
			return array(array('Error_Msg' => $response[0]['Error_Msg']));
		}

		$PersonNotice->setEvnId($response[0]['EvnUslugaPar_id']);
		$PersonNotice->doStatusSnapshotSecond();
		$PersonNotice->processStatusChange();

		// сохраняем выполнение по свяанному назначению и меняем статус направления
		if (!empty($data['EvnDirection_id'])) {
			$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
			$this->EvnPrescr_model->saveEvnPrescrIsExec([
				'pmUser_id' => $data['pmUser_id'],
				'EvnDirection_id' => $data['EvnDirection_id'],
				'EvnPrescr_IsExec' => 2
			]);
			if (!isset($data['is_consul']) || !$data['is_consul']) {
				$this->load->model('Evn_model', 'Evn_model');
				$this->Evn_model->updateEvnStatus([
					'Evn_id' => $data['EvnDirection_id'],
					'EvnStatus_SysNick' => 'Serviced',
					'EvnClass_SysNick' => 'EvnDirection',
					'pmUser_id' => $data['pmUser_id']
				]);
			}
		}

		$this->db->trans_commit();

		return $response;
	}

	/**
	 * Получение данных услуги для уведомления
	 * (больше не используется)
	 */
	function getUslugaParDataForNotice($data)
	{
		$query = "						
			select
				PA.Person_Fio as \"Person_Fio\",
				PA.Person_id as \"Person_id\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				v_EvnUslugaPar EUP
				left join v_Person_all PA on PA.Person_id = EUP.Person_id
					and PA.PersonEvn_id = EUP.PersonEvn_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EUP.UslugaComplex_id
			where
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));
		if ( is_object($result) ) {
			$result = $result->result('array');
			if( isset($result[0]) )
				return $result[0];
			else
				return false;
		} else {
			return false;
		}
	}

	/**
	 * Отмена направления
	 */
	function cancelDirection($data)
	{
		$directionData = array();

		// 1. получение данных направления
		$query = "
			select
				d.pmUser_insID as \"pmUser_insID\",
				tmms.TimetableMedService_id as \"TimetableMedService_id\",
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				d.EvnDirection_Num as \"EvnDirection_Num\",
				ms.MedService_Name as \"MedService_Name\",
				(select EvnStatusCause_Name from v_EvnStatusCause where EvnStatusCause_id = :EvnStatusCause_id limit 1) as \"EvnStatusCause_Name\",
				coalesce(PS.Person_SurName, '') || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName, '') as \"Person_Fio\",
				l.Lpu_Nick as \"Lpu_Nick\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				eq.EvnQueue_id as \"EvnQueue_id\"
			from
				v_EvnDirection_all d
				inner join v_EvnUslugaPar eup on eup.EvnDirection_id = d.EvnDirection_id
				left join v_PersonState ps on ps.Person_id = d.Person_id
				left join v_MedService ms on ms.MedService_id = d.MedService_id
				left join v_Lpu l on l.Lpu_id = ms.Lpu_id
				left join v_LpuSection ls on ls.LpuSection_id = ms.LpuSection_id
				left join v_TimeTableMedService_lite tmms on tmms.EvnDirection_id = d.EvnDirection_id
				left join v_EvnQueue eq on eq.EvnDirection_id = d.EvnDirection_id
			where
				d.EvnDirection_id = :EvnDirection_id
			limit 1
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$directionData = $result->result('array');
		}

		if (count($directionData) == 0) {
			return array(array('Error_Msg' => 'Ошибка получения данных по направлению'));
		}

		// 2. удаляем заявку
		$this->deleteEvnUslugaPar(array(
			'EvnUslugaPar_id' => $directionData[0]['EvnUslugaPar_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// 3. отмена направления

		$data['EvnComment_Comment'] = $data['EvnStatusHistory_Cause'];
		if ( strlen($data['EvnComment_Comment']) > 2048 ) {
			$data['EvnComment_Comment'] = substr($data['EvnComment_Comment'], 0, 2048);
		}

		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'TimetableMedService_id' => $directionData[0]['TimetableMedService_id'],
			'EvnQueue_id' => $directionData[0]['EvnQueue_id'],
			'EvnStatusCause_id' => $data['EvnStatusCause_id'],
			'EvnComment_Comment' => $data['EvnStatusHistory_Cause'],
			'EvnDirection_id' => $data['EvnDirection_id']
		);

		try {
			$this->beginTransaction();

			$res = $this->queryResult("
				update EvnDirection
				set EvnDirection_failDT = dbo.tzGetDate(),
					pmUser_failID = :pmUser_id,
					TimetableGraf_id = null,
					TimetableStac_id = null,
					TimetableMedService_id = null,
					TimetablePar_id = null
				where Evn_id = :EvnDirection_id
			", $params);
			if (!$this->isSuccessful($res)) {
				throw new Exception($res[0]['Error_Code']);
			}

			$comment = $this->getFirstResultFromQuery("
				select
					EvnComment_id
				from v_EvnComment
				where Evn_id = :EvnDirection_id
			", $params);

			if (!empty($comment)) {
				$params['EvnComment_id'] = $comment;
				$res = $this->queryResult("
					update dbo.EvnComment
					set EvnComment_Comment = :EvnComment_Comment,
						pmUser_updID = :pmUser_id,
						EvnComment_updDT = dbo.tzgetdate()
					where Evn_id = :EvnComment_id
				", $params);

				if (!$this->isSuccessful($res)) {
					throw new Exception($res[0]['Error_Code']);
				}
			} else {
				$res = $this->queryResult("
					insert into dbo.EvnComment
						(Evn_id, EvnComment_Comment, pmUser_insID, pmUser_updID, EvnComment_insDT, EvnComment_updDT)
					values (:EvnDirection_id, :EvnComment_Comment, :pmUser_id, :pmUser_id, dbo.tzgetdate(), dbo.tzgetdate())
				", $params);

				if (!$this->isSuccessful($res)) {
					throw new Exception($res[0]['Error_Code']);
				}
			}

			if (!empty($params['TimetableMedService_id'])) {
				$res = $this->queryResult("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_TimetableMedService_cancel(
						TimetableMedService_id := :TimetableMedService_id,
						pmUser_id := :pmUser_id
					)
				", $params);

				if (!$this->isSuccessful($res)) {
					throw new Exception($res[0]['Error_Code']);
				}
			}

			if (!empty($params['EvnQueue_id'])) {
				$res = $this->queryResult("
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnQueue_del(
						EvnQueue_id := :EvnQueue_id,
						pmUser_id := :pmUser_id
					)
				", $params);

				if (!$this->isSuccessful($res)) {
					throw new Exception($res[0]['Error_Code']);
				}
			}

			$resp = $this->queryResult("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_Evn_setStatus(
					Evn_id := :EvnDirection_id,
					EvnStatus_SysNick := 'Declined',
					EvnClass_id := 27,
					EvnStatusCause_id := :EvnStatusCause_id,
					EvnStatusHistory_Cause := :EvnComment_Comment,
					pmUser_id := :pmUser_id
				)
			", $params);

			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Code']);
			}

			$this->commitTransaction();
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return false;
		}


		if (empty($resp[0]['Error_Msg'])) {
			$noticeData = array(
				'autotype' => 1
			,'User_rid' => $directionData[0]['pmUser_insID']
			,'pmUser_id' => $data['pmUser_id']
			,'type' => 1
			,'title' => 'Отмена направления'
			,'text' => 'Направление №' .$directionData[0]['EvnDirection_Num']. ' (' .$directionData[0]['Person_Fio']. ') на консультацию ' .$directionData[0]['MedService_Name']. ' ('.$directionData[0]['Lpu_Nick'].', '.$directionData[0]['LpuSection_Name'].') отменено по причине '. $directionData[0]['EvnStatusCause_Name'] . '. ' . $data['EvnComment_Comment']
			);
			$this->load->model('Messages_model', 'Messages_model');
			$noticeResponse = $this->Messages_model->autoMessage($noticeData);
		}

		return $resp;


	}

	/**
	 * Изменение привязки услуги
	 */
	function editEvnUslugaPar($data) {
		$query = "
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				to_char(EvnUslugaPar_setDT, 'dd-mm-yyyy') as \"EvnUslugaPar_setDT\",
				to_char(EvnUslugaPar_disDT, 'dd-mm-yyyy') as \"EvnUslugaPar_disDT\",
				to_char(EvnUslugaPar_didDT, 'dd-mm-yyyy') as \"EvnUslugaPar_didDT\",
				Morbus_id as \"Morbus_id\",
				EvnUslugaPar_IsSigned as \"EvnUslugaPar_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				to_char(EvnUslugaPar_signDT, 'dd-mm-yyyy') as \"EvnUslugaPar_signDT\",
				PayType_id as \"PayType_id\",
				Usluga_id as \"Usluga_id\",
				MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				LpuSection_uid as \"LpuSection_uid\",
				EvnUslugaPar_Kolvo as \"EvnUslugaPar_Kolvo\",
				Org_uid as \"Org_uid\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaPar_isCito as \"EvnUslugaPar_isCito\",
				MedPersonal_sid as \"MedPersonal_sid\",
				EvnUslugaPar_Result as \"EvnUslugaPar_Result\",
				EvnDirection_id as \"EvnDirection_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EvnUslugaPar_CoeffTariff as \"EvnUslugaPar_CoeffTariff\",
				MesOperType_id as \"MesOperType_id\",
				EvnUslugaPar_IsModern as \"EvnUslugaPar_IsModern\",
				EvnUslugaPar_Price as \"EvnUslugaPar_Price\",
				EvnUslugaPar_Summa as \"EvnUslugaPar_Summa\",
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnPrescrTimetable_id as \"EvnPrescrTimetable_id\",
				EvnCourse_id as \"EvnCourse_id\",
				Lpu_oid as \"Lpu_oid\",
				PrehospDirect_id as \"PrehospDirect_id\",
				LpuSection_did as \"LpuSection_did\",
				Lpu_did as \"Lpu_did\",
				Org_did as \"Org_did\",
				MedPersonal_did as \"MedPersonal_did\",
				TimetablePar_id as \"TimetablePar_id\",
				EvnLabSample_id as \"EvnLabSample_id\",
				Study_uid as \"Study_uid\",
				EvnUslugaPar_ResultValue as \"EvnUslugaPar_ResultValue\",
				EvnUslugaPar_ResultLower as \"EvnUslugaPar_ResultLower\",
				EvnUslugaPar_ResultUpper as \"EvnUslugaPar_ResultUpper\",
				EvnUslugaPar_ResultUnit as \"EvnUslugaPar_ResultUnit\",
				EvnUslugaPar_ResultApproved as \"EvnUslugaPar_ResultApproved\",
				to_char(EvnUslugaPar_ResultAppDate, 'dd-mm-yyyy') as \"EvnUslugaPar_ResultAppDate\",
				EvnUslugaPar_ResultCancelReason as \"EvnUslugaPar_ResultCancelReason\",
				EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				EvnUslugaPar_ResultLowerCrit as \"EvnUslugaPar_ResultLowerCrit\",
				EvnUslugaPar_ResultUpperCrit as \"EvnUslugaPar_ResultUpperCrit\",
				EvnUslugaPar_ResultQualitativeNorms as \"EvnUslugaPar_ResultQualitativeNorms\",
				EvnUslugaPar_ResultQualitativeText as \"EvnUslugaPar_ResultQualitativeText\",
				RefValues_id as \"RefValues_id\",
				Unit_id as \"Unit_id\",
				EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				EvnUslugaPar_IsManual as \"EvnUslugaPar_IsManual\"
			from
				v_EvnUslugaPar
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
		}

		if (empty($resp[0])) {
			return false;
		}

		// Отвязать услуги от случая, попавшего в реестр нельзя
		$resp_check = $this->queryResult("
			select
				coalesce(epl.EvnPL_IsInReg, eps.EvnPS_IsInReg, ev.EvnVizit_IsInReg, es.EvnSection_IsInReg) as \"Evn_IsInReg\"
			from
				Evn e
				left join EvnPLBase eplb on eplb.Evn_id = e.Evn_id 
				left join EvnPL epl on epl.Evn_id = eplb.Evn_id 
				left join EvnPS eps on eps.Evn_id = e.Evn_id 
				left join EvnVizit ev on ev.Evn_id = e.Evn_id 
				left join EvnSection es on es.Evn_id = e.Evn_id 
			where
				e.Evn_id = :Evn_id
		", array(
			'Evn_id' => $resp[0]['EvnUslugaPar_pid']
		));
		if (!empty($resp_check[0]['Evn_IsInReg']) && $resp_check[0]['Evn_IsInReg'] == 2) {
			return array('Error_Msg' => 'Нельзя отредактировать услугу, т.к. случай в реестре');
		}

		// Привязать услугу к случаю, попавшему в рееестр нельзя
		$resp_check = $this->queryResult("
			select
				coalesce(epl.EvnPL_IsInReg, eps.EvnPS_IsInReg, ev.EvnVizit_IsInReg, es.EvnSection_IsInReg) as \"Evn_IsInReg\"
			from
				Evn e
				left join EvnPLBase eplb on eplb.Evn_id = e.Evn_id 
				left join EvnPL epl on epl.Evn_id = eplb.Evn_id 
				left join EvnPS eps on eps.Evn_id = e.Evn_id 
				left join EvnVizit ev on ev.Evn_id = e.Evn_id 
				left join EvnSection es on es.Evn_id = e.Evn_id 
			where
				e.Evn_id = :Evn_id
		", array(
			'Evn_id' => $data['EvnUslugaPar_pid']
		));
		if (!empty($resp_check[0]['Evn_IsInReg']) && $resp_check[0]['Evn_IsInReg'] == 2) {
			return array('Error_Msg' => 'Нельзя привязать услугу к случаю, т.к. случай в реестре');
		}

		// получаем информацию по услуге, используется в дальнейших проверках.
		$resp_usl = $this->queryResult("
			select
				UslugaComplex_Code as \"UslugaComplex_Code\",
				UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				v_UslugaComplex uc
			where
				uc.UslugaComplex_id = :UslugaComplex_id
		", array(
			'UslugaComplex_id' => $resp[0]['UslugaComplex_id']
		));

		$UslugaComplex_Name = '';
		if (!empty($resp_usl[0]['UslugaComplex_Name'])) {
			$UslugaComplex_Name = $resp_usl[0]['UslugaComplex_Name'];
		}

		// меняем pid
		$prevPid = $resp[0]['EvnUslugaPar_pid'];
		$newPid = $data['EvnUslugaPar_pid'];
		$resp[0]['EvnUslugaPar_pid'] = $newPid;

		// меняем дату
		if (isset($data['EvnUslugaPar_setDate'])) {
			if ( isset($data['EvnUslugaPar_setTime']) ) {
				$data['EvnUslugaPar_setDate'] .= ' ' . $data['EvnUslugaPar_setTime'] . ':00:000';
			}

			$resp[0]['EvnUslugaPar_setDT'] = $data['EvnUslugaPar_setDate'];

			// если дату поменяли, то проверяем, чтобы рабочее место врача на эту дату было открыто
			if (!empty($resp[0]['MedStaffFact_id']) && !empty($resp[0]['EvnUslugaPar_setDT'])) {
				// проверяем что рабочее место врача на дату выполнения услуги открыто.
				$MedStaffFact_id = $this->getFirstResultFromQuery("
				select
					MedStaffFact_id as \"MedStaffFact_id\"
				from
					v_MedStaffFact
				where
					MedStaffFact_id = :MedStaffFact_id
					and WorkData_begDate <= :EvnUslugaPar_setDT
					and (WorkData_endDate >= :EvnUslugaPar_setDT OR WorkData_endDate IS NULL)
				limit 1
			", array(
					'MedStaffFact_id' => $resp[0]['MedStaffFact_id'],
					'EvnUslugaPar_setDT' => $resp[0]['EvnUslugaPar_setDT']
				));
				if (empty($MedStaffFact_id)) {
					return array('Error_Msg' => 'Период работы врача не соответствует дате выполнения услуги');
				}
			}
		}

		// меняем пользователя
		$resp[0]['pmUser_id'] = $data['pmUser_id'];
		
		$resp_evn_prev = array();
		$resp_evn_new = array();

		$resp_evn_prev = array();
		$resp_evn_new = array();


		// Дополнительные проверки
		if (!empty($prevPid)) {
			// получаем инфрмацию по предудщему случаю
			$resp_evn_prev = $this->queryResult("
				select
					Evn_id as \"Evn_id\",
					to_char(Evn_setDT, 'dd.mm.yyyy') as \"Evn_setDT\",
					EvnClass_SysNick as \"EvnClass_SysNick\"
				from
					v_Evn
				where
					Evn_id = :Evn_id
			", array(
				'Evn_id' => $prevPid
			));

			if (!empty($resp_evn_prev[0]['Evn_id'])) {
				// Регион: Карелия
				if (getRegionNick() == 'kareliya') {
					if ($resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection') {
						// В КВС, в которой только одно движение с длительностью 24 часа и менее, проверяется наличие услуги ГОСТ-2011. Если услуги нет, то отвязка услуги от движения отменяется, за исключением случая, если в движении определилась КСГ 69, 87, 90, 146, 300, 302, 306, 91+услуга A25.30.035.003, 149+A07.30.011, 293+диагноз E22.0. При этом выводится сообщение: «Попытка отвязать услугу <Наименование услуги> от движения <дата движения> не была завершена по причине обязательности наличия услуги ГОСТ-2011 в КВС».
						// Если в движении определилась КСГ 69, 87, 90, 146, 300, 302, 306, 293+диагноз E22.0, то ошибка выдаваться не должна.
						$resp_check = $this->queryResult("
							select
								es_count.cnt as \"cnt\",
								to_char(coalesce(es.EvnSection_disDT, es.EvnSection_setDT), 'dd-mm-yyyy') as \"EvnSection_disDate\",
								date_part('hour', es.EvnSection_disDT - es.EvnSection_setDT) as \"diff_hours\",
								mo.Mes_Code as \"Mes_Code\",
								d.Diag_Code as \"Diag_Code\"
							from
								v_EvnSection es
								left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
								left join v_MesOld mo on mo.Mes_id = mt.Mes_id
								left join v_Diag d on d.Diag_id = es.Diag_id
								left join lateral(
									select
										count(es2.EvnSection_id) as cnt
									from
										v_EvnSection es2
									where
										es2.EvnSection_pid = es.EvnSection_pid
										and coalesce(es2.EvnSection_IsPriem, 1) = 1
								) es_count on true
							where
								es.EvnSection_id = :EvnSection_id
						", array(
							'EvnSection_id' => $resp_evn_prev[0]['Evn_id']
						));

						if (!empty($resp_check[0])) {
							$disableCheck = false;
							if (
								$resp_check[0]['EvnSection_disDate'] >= '2017-01-01' && $resp_check[0]['EvnSection_disDate'] <= '2017-12-31'
								&& (
									in_array($resp_check[0]['Mes_Code'], array('69','87','90','146','300','302','306'))
									|| (
										in_array($resp_check[0]['Mes_Code'], array('293'))
										&& $resp_check[0]['Diag_Code'] == 'E22.0'
									)
								)
							) {
								$disableCheck = true;
							}
							else if (
								$resp_check[0]['EvnSection_disDate'] >= '2018-01-01'
								&& (
									in_array($resp_check[0]['Mes_Code'], array('71', '86', '92', '157', '314', '316', '320'))
									|| (
										in_array($resp_check[0]['Mes_Code'], array('307'))
										&& $resp_check[0]['Diag_Code'] == 'E22.0'
									)
								)
							) {
								$disableCheck = true;
							}

							if (!$disableCheck && $resp_check[0]['cnt'] == 1 && $resp_check[0]['diff_hours'] <= 24) {
								$query = "
									select
										EU.EvnUsluga_id as \"EvnUsluga_id\"
									from
										v_EvnUsluga EU
										inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
										inner join v_UslugaCategory UCat on UCat.UslugaCategory_id = UC.UslugaCategory_id
									where
										EU.EvnUsluga_pid = :EvnSection_id
										and UCat.UslugaCategory_SysNick = 'gost2011'
										and EU.EvnUsluga_id <> :EvnUslugaPar_id
									limit 1
								";
								$resp_uslcheck = $this->queryResult($query, array(
									'EvnSection_id' => $resp_evn_prev[0]['Evn_id'],
									'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
								));
								if (empty($resp_uslcheck[0]['EvnUsluga_id'])) {
									throw new Exception('Попытка отвязать услугу '.$UslugaComplex_Name.' от движения '.$resp_evn_prev[0]['Evn_setDT'].' не была завершена по причине обязательности наличия услуги ГОСТ-2011 в КВС');
								}
							}
						}
					}
				}

				// Регион: Свердловская область.
				if (getRegionNick() == 'ekb') {
					if ($resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection') {
						// получаем информацию по движению
						$resp_es = $this->queryResult("
							select
								pt.PayType_SysNick as \"PayType_SysNick\",
								es.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
								lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
								es.Mes_sid as \"Mes_sid\"
							from
								v_EvnSection es
								left join v_PayType pt on pt.PayType_id = es.PayType_id
								left join v_LpuSection LS on LS.LpuSection_id = ES.LpuSection_id
								left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
								left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
							where
								es.EvnSection_id = :EvnSection_id
						", array(
							'EvnSection_id' => $resp_evn_prev[0]['Evn_id']
						));

						// При сохранении движения, если в поле “Вид оплаты” выбрано “Местный бюджет” проверяется, что в движении есть услуга, связанная с группой 152 или 156 для круглосуточного стационара
						// и 252 для дневного стационара или указан метод ВМП. Если проверка не срабатывают, то отвязка услуги от движения отменяется и выводится сообщение: «Попытка отвязать услугу <Наименование услуги> от движения <дата движения> не была завершена по причине обязательности наличия услуги, связанной с группой 152 или 156 для круглосуточного стационара и 252 для дневного стационара».
						if (!empty($resp_es[0]['PayType_SysNick']) && $resp_es[0]['PayType_SysNick'] == 'bud' && empty($resp_es[0]['HTMedicalCareClass_id'])) {
							if (in_array($resp_es[0]['LpuUnitType_SysNick'], array('dstac', 'hstac', 'pstac'))) {
								// для дневного в движении обязательно должна быть услуга с группой 252
								$UslugaComplexPartition_Code_filter = "ucp.UslugaComplexPartition_Code = 252";
							} else {
								// для круглосуточного в движении обязательно должна быть услуга с группой 152 или 156
								$UslugaComplexPartition_Code_filter = "ucp.UslugaComplexPartition_Code IN (152,156)";
							}
							$resp_uslcheck = $this->queryResult("
								select
									eu.EvnUsluga_id as \"EvnUsluga_id\"
								from
									v_EvnUsluga eu
								where
									eu.EvnUsluga_pid = :EvnSection_id
									and exists(
										select
											ucp.UslugaComplexPartition_id
										from
											r66.v_UslugaComplexPartitionLink ucpl
											inner join r66.v_UslugaComplexPartition ucp on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
										where
											ucpl.UslugaComplex_id = eu.UslugaComplex_id
											and '.$UslugaComplexPartition_Code_filter.'
										limit 1
									)
									and EU.EvnUsluga_id <> :EvnUslugaPar_id
								limit 1
							", array(
								'EvnSection_id' => $resp_evn_prev[0]['Evn_id'],
								'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
							));
							if (empty($resp_uslcheck[0]['EvnUsluga_id'])) {
								throw new Exception('Попытка отвязать услугу ' . $UslugaComplex_Name . ' от движения ' . $resp_evn_prev[0]['Evn_setDT'] . ' не была завершена по причине обязательности наличия услуги, связанной с группой 152 или 156 для круглосуточного стационара и 252 для дневного стационара');
							}
						}

						// При указании хирургической КСГ,  проверяется наличие для неё услуги. Если проверка не срабатывают, то отвязка услуги от движения отменяется и выводится сообщение: «Попытка отвязать услугу <Наименование услуги> от движения <дата движения> не была завершена по причине обязательности наличия услуги для хирургической КСГ».
						if (!empty($resp_es[0]['Mes_sid'])) {
							$resp_uslcheck = $this->queryResult("
								select
									mu.MesUsluga_id as \"MesUsluga_id\",
									INES.EvnUsluga_id as \"EvnUsluga_id\"
								from
									v_MesUsluga mu
									inner join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = mu.UslugaComplex_id
									left join lateral(
										select
											euc.EvnUsluga_id
										from
											v_EvnUsluga euc
										where
											UslugaComplex_id in (
												select
													mouc2.UslugaComplex_id
												from
													v_MesOldUslugaComplex mouc2
												where
													mouc2.Mes_id = mu.Mes_id
											)
											and euc.EvnUsluga_pid = :EvnSection_id
											and euc.EvnUsluga_id <> :EvnUslugaPar_id
										limit 1
									) INES on true
								where
									mu.Mes_id = :Mes_sid
									and ucpl.UslugaComplexPartitionLink_IsNeedOper = 2
								limit 1
							", array(
								'EvnSection_id' => $resp_evn_prev[0]['Evn_id'],
								'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
								'Mes_sid' => $resp_es[0]['Mes_sid']
							));

							if (!empty($resp_uslcheck[0]['MesUsluga_id']) && empty($resp_uslcheck[0]['EvnUsluga_id'])) {
								throw new Exception('Попытка отвязать услугу ' . $UslugaComplex_Name . ' от движения ' . $resp_evn_prev[0]['Evn_setDT'] . ' не была завершена по причине обязательности наличия услуги для хирургической КСГ');
							}
						}
					}
				}
			}
		}

		if (!empty($newPid)) {
			// получаем инфрмацию по новом случае
			$resp_evn_new = $this->queryResult("
				select
					Evn_id as \"Evn_id\",
					to_char(Evn_setDT, 'dd.mm.yyyy') as \"Evn_setDT\",
					EvnClass_SysNick as \"EvnClass_SysNick\"
				from
					v_Evn
				where
					Evn_id = :Evn_id
			", array(
				'Evn_id' => $newPid
			));

			if (!empty($resp_evn_new[0]['Evn_id'])) {
				// Регион: Пермь.
				if (getRegionNick() == 'perm') {
					if ($resp_evn_new[0]['EvnClass_SysNick'] == 'EvnVizitPL') {
						// Контроль добавления услуги к посещению с таким же кодом. Если привязывается услуга к посещению с кодом таким же, как и код посещения, то привязка к посещению отменяется. При этом выводится сообщение: «Попытка привязать услугу <наименование услуги> к посещению <дата посещения> не была завершена, т.к. код услуги не должен быть идентичен коду посещения».
						// Контроль ввода услуги к посещению «на дому». Если к посещению при указании в поле «Место» значений «2. На дому» либо «4. На дому: НМП» привязывается услуга «B04.069.333 Оказание помощи вне медицинской организации (в том числе на дому)», то привязка к посещению отменяется.
						// При этом выводится сообщение: «Попытка привязать услугу «B04.069.333 Оказание помощи вне медицинской организации (в том числе на дому)»,  к посещению <дата посещения> не была завершена, т.к. данную услугу нельзя привязывать к посещению, в котором указано место:  «2. На дому» либо «4. На дому: НМП»
						$resp_check = $this->queryResult("
							select
								evpl.UslugaComplex_id as \"UslugaComplex_id\",
								st.ServiceType_SysNick as \"ServiceType_SysNick\"
							from
								v_EvnVizitPL evpl
								left join v_ServiceType st on st.ServiceType_id = evpl.ServiceType_id
							where
								evpl.EvnVizitPL_id = :EvnVizitPL_id
						", array(
							'EvnVizitPL_id' => $resp_evn_new[0]['Evn_id']
						));

						if (!empty($resp_check[0]['UslugaComplex_id']) && $resp_check[0]['UslugaComplex_id'] == $resp[0]['UslugaComplex_id']) {
							throw new Exception('Попытка привязать услугу '.$UslugaComplex_Name.' к посещению '.$resp_evn_new[0]['Evn_setDT'].' не была завершена, т.к. код услуги не должен быть идентичен коду посещения');
						}

						if (!empty($resp_check[0]['ServiceType_SysNick']) && in_array($resp_check[0]['ServiceType_SysNick'], array('home', 'neotl')) && !empty($resp_usl[0]['UslugaComplex_Code']) && $resp_usl[0]['UslugaComplex_Code'] == 'B04.069.333') {
							throw new Exception('Попытка привязать услугу '.$UslugaComplex_Name.' к посещению '.$resp_evn_new[0]['Evn_setDT'].' не была завершена, т.к. данную услугу нельзя привязывать к посещению, в котором указано место: "2. На дому" либо "4. На дому: НМП"');
						}
					}
				}
			}
		}

		$query = "
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnUslugaPar_upd(
				EvnUslugaPar_id := :EvnUslugaPar_id,
				EvnUslugaPar_pid := :EvnUslugaPar_pid,
				EvnUslugaPar_rid := :EvnUslugaPar_rid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaPar_setDT := :EvnUslugaPar_setDT,
				EvnUslugaPar_disDT := :EvnUslugaPar_disDT,
				EvnUslugaPar_didDT := :EvnUslugaPar_didDT,
				Morbus_id := :Morbus_id,
				EvnUslugaPar_IsSigned := :EvnUslugaPar_IsSigned,
				pmUser_signID := :pmUser_signID,
				EvnUslugaPar_signDT := :EvnUslugaPar_signDT,
				PayType_id := :PayType_id,
				Usluga_id := :Usluga_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				LpuSection_uid := :LpuSection_uid,
				EvnUslugaPar_Kolvo := :EvnUslugaPar_Kolvo,
				Org_uid := :Org_uid,
				UslugaComplex_id := :UslugaComplex_id,
				EvnUslugaPar_isCito := :EvnUslugaPar_isCito,
				MedPersonal_sid := :MedPersonal_sid,
				EvnUslugaPar_Result := :EvnUslugaPar_Result,
				EvnDirection_id := :EvnDirection_id,
				UslugaComplexTariff_id := :UslugaComplexTariff_id,
				EvnUslugaPar_CoeffTariff := :EvnUslugaPar_CoeffTariff,
				MesOperType_id := :MesOperType_id,
				EvnUslugaPar_IsModern := :EvnUslugaPar_IsModern,
				EvnUslugaPar_Price := :EvnUslugaPar_Price,
				EvnUslugaPar_Summa := :EvnUslugaPar_Summa,
				EvnPrescr_id := :EvnPrescr_id,
				EvnPrescrTimetable_id := :EvnPrescrTimetable_id,
				EvnCourse_id := :EvnCourse_id,
				Lpu_oid := :Lpu_oid,
				PrehospDirect_id := :PrehospDirect_id,
				LpuSection_did := :LpuSection_did,
				Lpu_did := :Lpu_did,
				Org_did := :Org_did,
				MedPersonal_did := :MedPersonal_did,
				TimetablePar_id := :TimetablePar_id,
				EvnLabSample_id := :EvnLabSample_id,
				Study_uid := :Study_uid,
				EvnUslugaPar_ResultValue := :EvnUslugaPar_ResultValue,
				EvnUslugaPar_ResultLower := :EvnUslugaPar_ResultLower,
				EvnUslugaPar_ResultUpper := :EvnUslugaPar_ResultUpper,
				EvnUslugaPar_ResultUnit := :EvnUslugaPar_ResultUnit,
				EvnUslugaPar_ResultApproved := :EvnUslugaPar_ResultApproved,
				EvnUslugaPar_ResultAppDate := :EvnUslugaPar_ResultAppDate, 
				EvnUslugaPar_ResultCancelReason := :EvnUslugaPar_ResultCancelReason,
				EvnUslugaPar_Comment := :EvnUslugaPar_Comment,
				EvnUslugaPar_ResultLowerCrit := :EvnUslugaPar_ResultLowerCrit, 
				EvnUslugaPar_ResultUpperCrit := :EvnUslugaPar_ResultUpperCrit,
				EvnUslugaPar_ResultQualitativeNorms := :EvnUslugaPar_ResultQualitativeNorms,
				EvnUslugaPar_ResultQualitativeText := :EvnUslugaPar_ResultQualitativeText,
				RefValues_id := :RefValues_id,
				Unit_id := :Unit_id,
				EvnUslugaPar_Regime := :EvnUslugaPar_Regime,
				EvnUslugaPar_IsManual := :EvnUslugaPar_IsManual,
				pmUser_id := :pmUser_id
			)
		";

		if (!empty($data['EvnUslugaPar_IsManual'])) {
			$resp[0]['EvnUslugaPar_IsManual'] = $data['EvnUslugaPar_IsManual'];
		}

		$this->load->helper('PersonNotice');
		//Инициализация хелпера рассылки сообщений о смене статуса
		if (empty($data['Person_id'])) {
			$data['Person_id'] = $this->getFirstResultFromQuery("
				select
					Person_id as \"Person_id\"
				from
					v_PersonEvn
				where
					PersonEvn_id = :PersonEvn_id
				limit 1
			", $resp[0]);
			if (empty($data['Person_id'])) {
				$data['Person_id'] = null;
			}
		}
		$PersonNotice = new PersonNoticeEvn($data['Person_id']);
		$PersonNotice->loadPersonInfo(/*$resp[0]['PersonEvn_id'], $data['Server_id']*/);

		//Начинаем отслеживать статусы события EvnUslugaPar
		$PersonNotice->setEvnClassSysNick('EvnUslugaPar');
		$PersonNotice->setEvnId($resp[0]['EvnUslugaPar_id']);
		$PersonNotice->doStatusSnapshotFirst();

		$result = $this->db->query($query, $resp[0]);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if (!empty($response[0]['EvnUslugaPar_id'])) {
				if (in_array(getRegionNick(), array('perm', 'kaluga', 'ufa', 'astra')) && !empty($resp_evn_prev[0]['Evn_id']) && $resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection') {
					// При отвязке услуги от движения происходит:
					// Автоматический перерасчет полей «КСГ», «Коэффициент КСГ», «КСЛП»
					if (empty($resp_evn_new[0]['Evn_id']) || $resp_evn_prev[0]['Evn_id'] != $resp_evn_new[0]['Evn_id']) {
						$this->load->model('EvnSection_model');
						$this->EvnSection_model->recalcKSGKPGKOEF($resp_evn_prev[0]['Evn_id'], $data['session']);
					}
				}

				if (in_array(getRegionNick(), array('perm', 'kaluga', 'ufa', 'astra')) && !empty($resp_evn_new[0]['Evn_id']) && $resp_evn_new[0]['EvnClass_SysNick'] == 'EvnSection') {
					// При привязке услуги к движению происходит:
					// Автоматический перерасчет полей «КСГ», «Коэффициент КСГ», «КСЛП»
					if (empty($resp_evn_prev[0]['Evn_id']) || $resp_evn_prev[0]['Evn_id'] != $resp_evn_new[0]['Evn_id']) {
						$this->load->model('EvnSection_model');
						$this->EvnSection_model->recalcKSGKPGKOEF($resp_evn_new[0]['Evn_id'], $data['session']);
					}
				}

				$PersonNotice->setEvnId($response[0]['EvnUslugaPar_id']);
				$PersonNotice->doStatusSnapshotSecond();
				$PersonNotice->processStatusChange();

				// Регион: Свердловская область
				if (getRegionNick() == 'ekb') {
					$warning = '';
					if (!empty($resp_evn_prev[0]['EvnClass_SysNick']) && $resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection' && !empty($resp_evn_new[0]['EvnClass_SysNick']) && $resp_evn_new[0]['EvnClass_SysNick'] == 'EvnSection') {
						if ($resp_evn_prev[0]['Evn_id'] != $resp_evn_new[0]['Evn_id']) {
							$warning = 'Услуга ' . $UslugaComplex_Name . ' отвязана от движения ' . $resp_evn_prev[0]['Evn_setDT'] . ' и привязана к движению ' . $resp_evn_new[0]['Evn_setDT'] . '. Проверьте значение КСГ в движениях';
						}
					} else if (!empty($resp_evn_prev[0]['EvnClass_SysNick']) && $resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection') {
						$warning = 'От движения ' . $resp_evn_prev[0]['Evn_setDT'] . ' отвязана услуга ' . $UslugaComplex_Name . '. Проверьте значение КСГ в движении';
					} else if (!empty($resp_evn_new[0]['EvnClass_SysNick']) && $resp_evn_new[0]['EvnClass_SysNick'] == 'EvnSection') {
						$warning = 'К движению ' . $resp_evn_new[0]['Evn_setDT'] . ' привязана услуга ' . $UslugaComplex_Name . '. Проверьте значение КСГ в движении';
					}

					if (!empty($warning)) {
						$response[0]['Alert_Msg'] = $warning;
					}
				}
			}

			return $response;
		}

		return false;
	}
	
	function beforeEditEvnUslugaPar($data) {
		if (is_string($data['savedData'])) {
			$savedData = json_decode($data['savedData'], true);
		} else {
			$savedData = $data['savedData'];
		}

		// Отвязать услуги от случая, попавшего в реестр нельзя
		$resp_check = $this->queryResult("
			select
				coalesce(epl.EvnPL_IsInReg, eps.EvnPS_IsInReg, ev.EvnVizit_IsInReg, es.EvnSection_IsInReg) as \"Evn_IsInReg\"
			from
				Evn e
				left join EvnPLBase eplb on eplb.Evn_id = e.Evn_id 
				left join EvnPL epl on epl.EvnPLBase_id = eplb.EvnPLBase_id 
				left join EvnPS eps on eps.Evn_id = e.Evn_id 
				left join EvnVizit ev on ev.Evn_id = e.Evn_id 
				left join EvnSection es on es.Evn_id = e.Evn_id 
			where
				e.Evn_id = :Evn_id
		", array(
			'Evn_id' => $savedData['EvnUslugaPar_pid']
		));
		if (!empty($resp_check[0]['Evn_IsInReg']) && $resp_check[0]['Evn_IsInReg'] == 2) {
			return array('Error_Msg' => 'Нельзя отредактировать услугу, т.к. случай в реестре');
		}

		// Привязать услугу к случаю, попавшему в рееестр нельзя
		$resp_check = $this->queryResult("
			select
				coalesce(epl.EvnPL_IsInReg, eps.EvnPS_IsInReg, ev.EvnVizit_IsInReg, es.EvnSection_IsInReg) as \"Evn_IsInReg\"
			from
				Evn e
				left join EvnPLBase eplb on eplb.Evn_id = e.Evn_id 
				left join EvnPL epl on epl.EvnPLBase_id = eplb.EvnPLBase_id 
				left join EvnPS eps on eps.Evn_id = e.Evn_id 
				left join EvnVizit ev on ev.Evn_id = e.Evn_id 
				left join EvnSection es on es.Evn_id = e.Evn_id 
			where
				e.Evn_id = :Evn_id
		", array(
			'Evn_id' => $data['EvnUslugaPar_pid']
		));
		if (!empty($resp_check[0]['Evn_IsInReg']) && $resp_check[0]['Evn_IsInReg'] == 2) {
			return array('Error_Msg' => 'Нельзя привязать услугу к случаю, т.к. случай в реестре');
		}

		$resp_usl = $this->queryResult("
			select
				UslugaComplex_Code as \"UslugaComplex_Code\",
				UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				v_UslugaComplex uc
			where
				uc.UslugaComplex_id = :UslugaComplex_id
		", array(
			'UslugaComplex_id' => $savedData['UslugaComplex_id']
		));

		$UslugaComplex_Name = '';
		if (!empty($resp_usl[0]['UslugaComplex_Name'])) {
			$UslugaComplex_Name = $resp_usl[0]['UslugaComplex_Name'];
		}

		$prevPid = $savedData['EvnUslugaPar_pid'];
		$newPid = $data['EvnUslugaPar_pid'];

		// меняем дату
		if (!empty($data['EvnUslugaPar_setDT']) && !empty($savedData['MedStaffFact_id'])) {
			// проверяем что рабочее место врача на дату выполнения услуги открыто.
			$MedStaffFact_id = $this->getFirstResultFromQuery("
				select
					MedStaffFact_id as \"MedStaffFact_id\"
				from
					v_MedStaffFact
				where
					MedStaffFact_id = :MedStaffFact_id
					and WorkData_begDate <= :EvnUslugaPar_setDT
					and (WorkData_endDate >= :EvnUslugaPar_setDT OR WorkData_endDate IS NULL)
				limit 1
			", array(
				'MedStaffFact_id' => $savedData['MedStaffFact_id'],
				'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDT']
			));
			if (empty($MedStaffFact_id)) {
				return array('Error_Msg' => 'Период работы врача не соответствует дате выполнения услуги');
			}
		}

		// Дополнительные проверки
		if (!empty($prevPid)) {
			// получаем инфрмацию по предудщему случаю
			$resp_evn_prev = $this->queryResult("
				select
					Evn_id as \"Evn_id\",
					to_char(Evn_setDT, 'DD.MM.YYYY') as \"Evn_setDT\",
					EvnClass_SysNick as \"EvnClass_SysNick\"
				from
					v_Evn
				where
					Evn_id = :Evn_id
			", array(
				'Evn_id' => $prevPid
			));

			if (!empty($resp_evn_prev[0]['Evn_id'])) {
				// Регион: Карелия
				if (getRegionNick() == 'kareliya') {
					if ($resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection') {
						// В КВС, в которой только одно движение с длительностью 24 часа и менее, проверяется наличие услуги ГОСТ-2011. Если услуги нет, то отвязка услуги от движения отменяется, за исключением случая, если в движении определилась КСГ 69, 87, 90, 146, 300, 302, 306, 91+услуга A25.30.035.003, 149+A07.30.011, 293+диагноз E22.0. При этом выводится сообщение: «Попытка отвязать услугу <Наименование услуги> от движения <дата движения> не была завершена по причине обязательности наличия услуги ГОСТ-2011 в КВС».
						// Если в движении определилась КСГ 69, 87, 90, 146, 300, 302, 306, 293+диагноз E22.0, то ошибка выдаваться не должна.
						$resp_check = $this->queryResult("
							select
								es_count.cnt as \"cnt\",
								to_char(coalesce(es.EvnSection_disDT, es.EvnSection_setDT), 'YYYY-MM-DD') as \"EvnSection_disDate\",
								datediff('hour', es.EvnSection_setDT, es.EvnSection_disDT) as \"diff_hours\",
								mo.Mes_Code as \"Mes_Code\",
								d.Diag_Code as \"Diag_Code\"
							from
								v_EvnSection es
								left join v_MesTariff mt on mt.MesTariff_id = es.MesTariff_id
								left join v_MesOld mo on mo.Mes_id = mt.Mes_id
								left join v_Diag d on d.Diag_id = es.Diag_id
								left join lateral (
									select
										count(es2.EvnSection_id) as cnt
									from
										v_EvnSection es2
									where
										es2.EvnSection_pid = es.EvnSection_pid
										and COALESCE(es2.EvnSection_IsPriem, 1) = 1
								) es_count on true
							where
								es.EvnSection_id = :EvnSection_id
						", array(
							'EvnSection_id' => $resp_evn_prev[0]['Evn_id']
						));

						if (!empty($resp_check[0])) {
							$disableCheck = false;
							if (
								$resp_check[0]['EvnSection_disDate'] >= '2017-01-01' && $resp_check[0]['EvnSection_disDate'] <= '2017-12-31'
								&& (
									in_array($resp_check[0]['Mes_Code'], array('69','87','90','146','300','302','306'))
									|| (
										in_array($resp_check[0]['Mes_Code'], array('293'))
										&& $resp_check[0]['Diag_Code'] == 'E22.0'
									)
								)
							) {
								$disableCheck = true;
							}
							else if (
								$resp_check[0]['EvnSection_disDate'] >= '2018-01-01'
								&& (
									in_array($resp_check[0]['Mes_Code'], array('71', '86', '92', '157', '314', '316', '320'))
									|| (
										in_array($resp_check[0]['Mes_Code'], array('307'))
										&& $resp_check[0]['Diag_Code'] == 'E22.0'
									)
								)
							) {
								$disableCheck = true;
							}

							if (!$disableCheck && $resp_check[0]['cnt'] == 1 && $resp_check[0]['diff_hours'] <= 24) {
								$query = "
									select
										EU.EvnUsluga_id as \"EvnUsluga_id\"
									from
										v_EvnUsluga EU
										inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
										inner join v_UslugaCategory UCat on UCat.UslugaCategory_id = UC.UslugaCategory_id
									where
										EU.EvnUsluga_pid = :EvnSection_id
										and UCat.UslugaCategory_SysNick = 'gost2011'
										and EU.EvnUsluga_id <> :EvnUslugaPar_id
									limit 1
								";
								$resp_uslcheck = $this->queryResult($query, array(
									'EvnSection_id' => $resp_evn_prev[0]['Evn_id'],
									'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
								));
								if (empty($resp_uslcheck[0]['EvnUsluga_id'])) {
									throw new Exception('Попытка отвязать услугу '.$UslugaComplex_Name.' от движения '.$resp_evn_prev[0]['Evn_setDT'].' не была завершена по причине обязательности наличия услуги ГОСТ-2011 в КВС');
								}
							}
						}
					}
				}

				// Регион: Свердловская область.
				if (getRegionNick() == 'ekb') {
					if ($resp_evn_prev[0]['EvnClass_SysNick'] == 'EvnSection') {
						// получаем информацию по движению
						$resp_es = $this->queryResult("
							select
								pt.PayType_SysNick as \"PayType_SysNick\",
								es.HTMedicalCareClass_id as \"HTMedicalCareClass_id\",
								lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
								es.Mes_sid as \"Mes_sid\"
							from
								v_EvnSection es
								left join v_PayType pt on pt.PayType_id = es.PayType_id
								left join v_LpuSection LS on LS.LpuSection_id = ES.LpuSection_id
								left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
								left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
							where
								es.EvnSection_id = :EvnSection_id
						", array(
							'EvnSection_id' => $resp_evn_prev[0]['Evn_id']
						));

						// При сохранении движения, если в поле “Вид оплаты” выбрано “Местный бюджет” проверяется, что в движении есть услуга, связанная с группой 152 или 156 для круглосуточного стационара
						// и 252 для дневного стационара или указан метод ВМП. Если проверка не срабатывают, то отвязка услуги от движения отменяется и выводится сообщение: «Попытка отвязать услугу <Наименование услуги> от движения <дата движения> не была завершена по причине обязательности наличия услуги, связанной с группой 152 или 156 для круглосуточного стационара и 252 для дневного стационара».
						if (!empty($resp_es[0]['PayType_SysNick']) && $resp_es[0]['PayType_SysNick'] == 'bud' && empty($resp_es[0]['HTMedicalCareClass_id'])) {
							if (in_array($resp_es[0]['LpuUnitType_SysNick'], array('dstac', 'hstac', 'pstac'))) {
								// для дневного в движении обязательно должна быть услуга с группой 252
								$UslugaComplexPartition_Code_filter = "ucp.UslugaComplexPartition_Code = 252";
							} else {
								// для круглосуточного в движении обязательно должна быть услуга с группой 152 или 156
								$UslugaComplexPartition_Code_filter = "ucp.UslugaComplexPartition_Code IN (152,156)";
							}
							$resp_uslcheck = $this->queryResult('
								select
									eu.EvnUsluga_id as "EvnUsluga_id"
								from
									v_EvnUsluga eu
								where
									eu.EvnUsluga_pid = :EvnSection_id
									and exists(
										select
											ucp.UslugaComplexPartition_id
										from
											r66.v_UslugaComplexPartitionLink ucpl
											inner join r66.v_UslugaComplexPartition ucp on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
										where
											ucpl.UslugaComplex_id = eu.UslugaComplex_id
											and '.$UslugaComplexPartition_Code_filter.'
										limit 1
									)
									and EU.EvnUsluga_id <> :EvnUslugaPar_id
								limit 1
							', array(
								'EvnSection_id' => $resp_evn_prev[0]['Evn_id'],
								'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
							));
							if (empty($resp_uslcheck[0]['EvnUsluga_id'])) {
								throw new Exception('Попытка отвязать услугу ' . $UslugaComplex_Name . ' от движения ' . $resp_evn_prev[0]['Evn_setDT'] . ' не была завершена по причине обязательности наличия услуги, связанной с группой 152 или 156 для круглосуточного стационара и 252 для дневного стационара');
							}
						}

						// При указании хирургической КСГ,  проверяется наличие для неё услуги. Если проверка не срабатывают, то отвязка услуги от движения отменяется и выводится сообщение: «Попытка отвязать услугу <Наименование услуги> от движения <дата движения> не была завершена по причине обязательности наличия услуги для хирургической КСГ».
						if (!empty($resp_es[0]['Mes_sid'])) {
							$resp_uslcheck = $this->queryResult('
								select
									mu.MesUsluga_id as "MesUsluga_id",
									INES.EvnUsluga_id as "EvnUsluga_id"
								from
									v_MesUsluga mu
									inner join r66.v_UslugaComplexPartitionLink ucpl on ucpl.UslugaComplex_id = mu.UslugaComplex_id
									left join lateral (
										select
											euc.EvnUsluga_id
										from
											v_EvnUsluga euc
										where
											UslugaComplex_id in (
												select
													mouc2.UslugaComplex_id
												from
													v_MesOldUslugaComplex mouc2
												where
													mouc2.Mes_id = mu.Mes_id
											)
											and euc.EvnUsluga_pid = :EvnSection_id
											and euc.EvnUsluga_id <> :EvnUslugaPar_id
										limit 1
									) INES on true
								where
									mu.Mes_id = :Mes_sid
									and ucpl.UslugaComplexPartitionLink_IsNeedOper = 2
								limit 1
							', array(
								'EvnSection_id' => $resp_evn_prev[0]['Evn_id'],
								'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
								'Mes_sid' => $resp_es[0]['Mes_sid']
							));

							if (!empty($resp_uslcheck[0]['MesUsluga_id']) && empty($resp_uslcheck[0]['EvnUsluga_id'])) {
								throw new Exception('Попытка отвязать услугу ' . $UslugaComplex_Name . ' от движения ' . $resp_evn_prev[0]['Evn_setDT'] . ' не была завершена по причине обязательности наличия услуги для хирургической КСГ');
							}
						}
					}
				}
			}
		}

		if (!empty($newPid)) {
			// получаем инфрмацию по новом случае
			$resp_evn_new = $this->queryResult("
				select
					Evn_id as \"Evn_id\",
					to_char(Evn_setDT, 'DD.MM.YYYY') as \"Evn_setDT\",
					EvnClass_SysNick as \"EvnClass_SysNick\"
				from
					v_Evn
				where
					Evn_id = :Evn_id
			", array(
				'Evn_id' => $newPid
			));

			if (!empty($resp_evn_new[0]['Evn_id'])) {
				// Регион: Пермь.
				if (getRegionNick() == 'perm') {
					if ($resp_evn_new[0]['EvnClass_SysNick'] == 'EvnVizitPL') {
						// Контроль добавления услуги к посещению с таким же кодом. Если привязывается услуга к посещению с кодом таким же, как и код посещения, то привязка к посещению отменяется. При этом выводится сообщение: «Попытка привязать услугу <наименование услуги> к посещению <дата посещения> не была завершена, т.к. код услуги не должен быть идентичен коду посещения».
						// Контроль ввода услуги к посещению «на дому». Если к посещению при указании в поле «Место» значений «2. На дому» либо «4. На дому: НМП» привязывается услуга «B04.069.333 Оказание помощи вне медицинской организации (в том числе на дому)», то привязка к посещению отменяется.
						// При этом выводится сообщение: «Попытка привязать услугу «B04.069.333 Оказание помощи вне медицинской организации (в том числе на дому)»,  к посещению <дата посещения> не была завершена, т.к. данную услугу нельзя привязывать к посещению, в котором указано место:  «2. На дому» либо «4. На дому: НМП»
						$resp_check = $this->queryResult("
							select
								evpl.UslugaComplex_id as \"UslugaComplex_id\",
								st.ServiceType_SysNick as \"ServiceType_SysNick\"
							from
								v_EvnVizitPL evpl
								left join v_ServiceType st on st.ServiceType_id = evpl.ServiceType_id
							where
								evpl.EvnVizitPL_id = :EvnVizitPL_id
						", array(
							'EvnVizitPL_id' => $resp_evn_new[0]['Evn_id']
						));

						if (!empty($resp_check[0]['UslugaComplex_id']) && $resp_check[0]['UslugaComplex_id'] == $savedData['UslugaComplex_id']) {
							throw new Exception('Попытка привязать услугу '.$UslugaComplex_Name.' к посещению '.$resp_evn_new[0]['Evn_setDT'].' не была завершена, т.к. код услуги не должен быть идентичен коду посещения');
						}

						if (!empty($resp_check[0]['ServiceType_SysNick']) && in_array($resp_check[0]['ServiceType_SysNick'], array('home', 'neotl')) && !empty($resp_usl[0]['UslugaComplex_Code']) && $resp_usl[0]['UslugaComplex_Code'] == 'B04.069.333') {
							throw new Exception('Попытка привязать услугу '.$UslugaComplex_Name.' к посещению '.$resp_evn_new[0]['Evn_setDT'].' не была завершена, т.к. данную услугу нельзя привязывать к посещению, в котором указано место: "2. На дому" либо "4. На дому: НМП"');
						}
					}
				}
			}
		}

		return array(
			'success' => true,
			'resp_evn_prev' => $resp_evn_prev,
			'resp_evn_new' => $resp_evn_new
		);
	}

	/**
	 * Прием без записи. Выношу отдельно, ибо нужно сразу несколько действий выполнить
	 */
	function acceptWithoutRecord ($data) {

		$this->db->trans_begin();

		$this->load->model('Evn_model', 'Evn_model');
		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->load->model('TimetableMedService_model','TimetableMedService');

		$data['PayType_id'] = $this->getFirstResultFromQuery("
			select
				PayType_id as \"PayType_id\"
			from
				v_PayType
			where
				PayType_SysNick = :PayType_SysNick
			limit 1
		", array(
				'PayType_SysNick' => getPayTypeSysNickOMS())
		);

		if (empty($data['PayType_id'])) {
			$data['PayType_id'] = null;
		}

		// 1 этап. Сохраняем направление
		$EvnDirection = $this->EvnDirection->saveEvnDirection($data);
		if ($EvnDirection) {
			$data['EvnDirection_id'] = $EvnDirection[0]['EvnDirection_id'];
		} else {
			$this->db->trans_rollback();
			return false;
		}

		$EvnStatus_id = (isset($data['LpuUnitType_SysNick']) && $data['LpuUnitType_SysNick'] == 'stac') ? 17 : 1;
		$EvnStatus_SysNick = (isset($data['LpuUnitType_SysNick']) && $data['LpuUnitType_SysNick'] == 'stac') ? 'DirZap' : 'New';

		// 2 этап. Сохраняем услугу
		$EvnUslugaPar = $this->saveEvnUslugaPar($data);
		if ($EvnUslugaPar) {

		} else {
			$this->db->trans_rollback();
			return false;
		}

		// 3 этап. Сохраняем бирку
		$ttms = $this->TimetableMedService->acceptWithoutRecord($data);
		if ($ttms) {
			$ttms[0]['EvnDirection_id'] = $data['EvnDirection_id'];
			$res = $this->Evn_model->updateEvnStatus(array(
				'Evn_id' => $data['EvnDirection_id'],
				'EvnStatus_id' => $EvnStatus_id,
				'EvnStatus_SysNick' => $EvnStatus_SysNick,
				'EvnClass_SysNick' => 'EvnDirection',
				'pmUser_id' => $data['pmUser_id']
			));
			$this->db->trans_commit();
			return $ttms;
		} else {
			$this->db->trans_rollback();
			return false;
		}

	}

	/**
	 * Запись на бирку. Выношу отдельно, ибо нужно сразу несколько действий выполнить
	 */
	function recordPerson ($data) {

		$this->db->trans_begin();

		$this->load->model('EvnDirection_model', 'EvnDirection');

		$data['PayType_id'] = $this->getFirstResultFromQuery("
			select
				PayType_id as \"PayType_id\"
			from
				v_PayType
			where
				PayType_SysNick = :PayType_SysNick
			limit 1
		", array(
				'PayType_SysNick' => getPayTypeSysNickOMS())
		);

		if (empty($data['PayType_id'])) {
			$data['PayType_id'] = null;
		}

		// 1 этап. Сохраняем направление
		$EvnDirection = $this->EvnDirection->saveEvnDirection($data);
		if ($EvnDirection) {
			$data['EvnDirection_id'] = $EvnDirection[0]['EvnDirection_id'];
		} else {
			$this->db->trans_rollback();
			return false;
		}

		$EvnStatus_id = (isset($data['LpuUnitType_SysNick']) && $data['LpuUnitType_SysNick'] == 'stac') ? 17 : 1;
		$EvnStatus_SysNick = (isset($data['LpuUnitType_SysNick']) && $data['LpuUnitType_SysNick'] == 'stac') ? 'DirZap' : 'New';

		// 2 этап. Сохраняем услугу
		$data['is_consul'] = true;
		$EvnUslugaPar = $this->saveEvnUslugaPar($data);
		if ($EvnUslugaPar) {
			$this->db->trans_commit();
			return $EvnUslugaPar;
		} else {
			$this->db->trans_rollback();
			return false;
		}

	}

	/**
	 * Загрузка пар услуг по направлению (используется в патологогистологии)
	 */
	function loadEvnUslugaParListByDirection($data) {
		$query = "
			SELECT
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.Person_id as \"Person_id\",
				EUP.Server_id as \"Server_id\",
				to_char(EUP.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\"
			FROM v_EvnUslugaPar EUP
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EUP.UslugaComplex_id
			WHERE 
				EUP.EvnDirection_id = :EvnDirection_id
			ORDER BY
				EUP.EvnUslugaPar_id desc
		";

		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Список связанных документов по исследованию
	 */
	function getLabStydyResultDoc($data) {
		$query = "
			SELECT
				EvnXml_id as \"EvnXml_id\"
			FROM v_EvnXml
			WHERE 
				Evn_id = :EvnUslugaPar_id
				and XmlTemplateType_id = 9
				and XmlTemplate_id is not null
		";

		return $this->queryResult($query, $data);
	}

    /*
     * Получение списка микроорганизмов
     */
	function getBactMicroList($params) {
		$query = "select
				row_number() over(order by bm.BactMicro_Name) as \"RowNumber\",
      			bm.BactMicro_id as \"BactMicro_id\",
				bm.BactMicro_Name as \"BactMicro_Name\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\"
			from dbo.v_EvnUslugaPar  eup
				inner join dbo.v_EvnLabSample  els on els.EvnLabSample_id = eup.EvnLabSample_id
				inner join dbo.v_UslugaTest  ut on ut.EvnLabSample_id = els.EvnLabSample_id
				inner join dbo.v_BactMicroProbe  bmp on bmp.UslugaTest_id = ut.UslugaTest_id
				inner join dbo.v_BactMicro  bm on bm.BactMicro_id = bmp.BactMicro_id
			where ut.UslugaTest_ResultApproved = 2 and EvnUslugaPar_id = :EvnUslugaPar_id and bmp.BactMicroProbe_IsNotShown != 2
			order by bm.BactMicro_Name";

		return $this->queryResult($query, $params);
	}

	/*
	 * Получение списка микроорганизмов
	 */
	function getBactMicroIsNotFind($params) {
		$query = "select count(EvnUslugaPar_id) as count
			from dbo.v_EvnUslugaPar eup
				inner join dbo.v_EvnLabSample els on els.EvnLabSample_id = eup.EvnLabSample_id
				inner join dbo.v_UslugaTest ut on ut.EvnLabSample_id = els.EvnLabSample_id
				inner join dbo.v_BactMicroProbe bmp on bmp.UslugaTest_id = ut.UslugaTest_id
			where ut.UslugaTest_ResultApproved = 2 and EvnUslugaPar_id = :EvnUslugaPar_id and bmp.BactMicroProbe_IsNotShown = 2";

		return $this->queryResult($query, $params);
	}

	/*
     * Получение списка антибиотиков
     */
	function getBactAntibioticList($params) {
		$query = "select
				bmp.BactMicro_id as \"BactMicro_id\",
				concat(coalesce(ba.BactAntibiotic_Name, ''), ' ', coalesce(bg.BactGuideline_Name, ''), ' ', coalesce(method.BactMethod_Name, '')) as \"BactAntibiotic_Name\",
				coalesce(sens.BactMicroABPSens_ShortName, '-') as \"BactMicroABPSens_ShortName\"
			from dbo.v_EvnUslugaPar  eup
			inner join dbo.v_EvnLabSample  els on els.EvnLabSample_id = eup.EvnLabSample_id
			inner join dbo.v_UslugaTest  ut on ut.EvnLabSample_id = els.EvnLabSample_id
			inner join dbo.v_BactMicroProbe  bmp on bmp.UslugaTest_id = ut.UslugaTest_id
			inner join dbo.v_BactMicroProbeAntibiotic  bmpa on bmpa.BactMicroProbe_id = bmp.BactMicroProbe_id
			inner join dbo.v_UslugaTest  uta on uta.UslugaTest_id = bmpa.UslugaTest_id
			inner join dbo.v_BactMethod  method on method.BactMethod_id = bmpa.BactMethod_id
			left join dbo.v_BactAntibiotic  ba on ba.BactAntibiotic_id = bmpa.BactAntibiotic_id
			inner join dbo.v_BactGuideline  bg on bg.BactGuideline_id = ba.BactGuideline_id
			left join dbo.v_BactMicroABPSens  sens on sens.BactMicroABPSens_id = bmpa.BactMicroABPSens_id

			where EvnUslugaPar_id = :EvnUslugaPar_id and uta.UslugaTest_ResultApproved = 2
			order by concat(coalesce(ba.BactAntibiotic_Name, ''), ' ', coalesce(bg.BactGuideline_Name, ''), ' ', coalesce(method.BactMethod_Name, ''))";
		return $this->queryResult($query, $params);
	}


	/**
	 *  Получение информации по параклинической услуге. Метод для API.
	 */
	function getEvnUslugaParForApi($data) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['session']['lpu_id']
		);
		if (!empty($data['EvnUsluga_id'])) {
			$filter .= " and EUP.EvnUslugaPar_id = :EvnUsluga_id";
			$queryParams['EvnUsluga_id'] = $data['EvnUsluga_id'];
		}
		if (!empty($data['Person_id'])) {
			$filter .= " and EUP.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['Evn_setDT'])) {
			$filter .= " and EUP.EvnUslugaPar_setDT = :Evn_setDT";
			$queryParams['Evn_setDT'] = $data['Evn_setDT'];
		}
		if (!empty($data['UslugaComplex_id'])) {
			$filter .= " and EUP.UslugaComplex_id = :UslugaComplex_id";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		if (empty($filter)) {
			return array();
		}

		$query = "
			select
				EUP.EvnUslugaPar_id as \"EvnUsluga_id\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				EUP.EvnUslugaPar_id as \"Evn_id\",
				to_char( EUP.EvnUslugaPar_setDT, 'dd-mm-yyyy') as \"Evn_setDT\",
				to_char( EUP.EvnUslugaPar_disDT, 'dd-mm-yyyy') as \"Evn_disDT\",
				EUP.Person_id as \"Person_id\",
				EUP.LpuSection_uid as \"LpuSection_id\",
				EUP.Lpu_uid as \"Lpu_id\",
				EUP.Org_uid as \"Org_id\",
				EUP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EUP.MedSpecOms_id as \"MedSpecOms_id\",
				EUP.MedStaffFact_id as \"MedStaffFact_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.PayType_id as \"PayType_id\",
				EUP.EvnUslugaPar_Kolvo as \"EvnUsluga_Kolvo\",
				EUP.UslugaPlace_id as \"UslugaPlace_id\",
				EUP.EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\"
			from
				v_EvnUslugaPar EUP
			where
				Lpu_id = :Lpu_id
				{$filter}
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 *  Сохранение параклинической услуги. Метод для API.
	 */
	function saveEvnUslugaParFromAPI($data) {
		$data['MedPersonal_id'] = null;
		if (!empty($data['MedStaffFact_id'])) {
			$info = $this->getFirstRowFromQuery("
				select
					MedStaffFact_id as \"MedStaffFact_id\",
					MedPersonal_id as \"MedPersonal_id\"
				from v_MedStaffFact
				where MedStaffFact_id = :MedStaffFact_id
			", $data);
			if (!empty($info['MedStaffFact_id'])) {
				$data['MedPersonal_id'] = $info['MedPersonal_id'];
			} else {
				return array(array('Error_Msg' => 'Ошибка получения данных по рабочему месту врача'));
			}
		}
		$info = $this->getFirstRowFromQuery("
			select
				PersonEvn_id as \"PersonEvn_id\",
				Person_id as \"Person_id\",
				Server_id as \"Server_id\"
			from v_PersonState
			where Person_id = :Person_id
		", $data);
		if (!empty($info['Person_id'])) {
			$data['Server_id'] = $info['Server_id'];
			$data['PersonEvn_id'] = $info['PersonEvn_id'];
		} else {
			return array(array('Error_Msg' => 'Ошибка получения данных по человеку'));
		}

		return $this->saveEvnUslugaPar(array(
			'EvnUslugaPar_setDate' => $data['Evn_setDT'],
			'EvnUslugaPar_id' => $data['EvnUsluga_id'],
			'EvnDirection_id' => null,
			'EvnDirection_setDate' => null,
			'Lpu_uid' => $data['Lpu_id'],
			'Org_uid' => $data['Org_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'MedSpecOms_id' => $data['MedSpecOms_id'],
			'EvnUslugaPar_MedPersonalCode' => null,
			'EvnDirection_Num' => null,
			'Lpu_id' => $data['session']['lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Person_id' => $data['Person_id'],
			'PayType_id' => $data['PayType_id'],
			'EvnUslugaPar_isCito' => null,
			'TimetablePar_id' => null,
			'Usluga_id' => null,
			'MedPersonal_uid' => $data['MedPersonal_id'],
			'MedStaffFact_uid' => $data['MedStaffFact_id'],
			'LpuSection_uid' => $data['LpuSection_id'],
			'UslugaPlace_id' => $data['UslugaPlace_id'],
			'EvnUslugaPar_Kolvo' => $data['EvnUsluga_Kolvo'],
			'PrehospDirect_id' => null,
			'LpuSection_did' => null,
			'Lpu_did' => null,
			'Org_did' => null,
			'MedPersonal_did' => null,
			'MedPersonal_sid' => null,
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'MedProductCard_id' => null,
			'pmUser_id' => $data['session']['pmuser_id']
		));
	}

	/**
	 *  Получение списка исследований в ЭМК
	 */
	function loadEvnUslugaParPanel($data)
	{
		$filter = " and eup.Person_id = :Person_id ";

		if (!empty($data['EvnUslugaPar_rid']) && empty($data['Person_id'])) {
			$filter = " and eup.EvnUslugaPar_rid = :EvnUslugaPar_rid ";
		}

		$sql = "
			select
				-- select
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				eup.EvnUslugaPar_rid as \"EvnUslugaPar_rid\",
				to_char(eup.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				l.Lpu_Name as \"Lpu_Name\",
				ms.MedService_Name as \"MedService_Name\",
				ex.EvnXml_id as \"EvnXml_id\"
				-- end select
			from
				-- from
				v_EvnUslugaPar eup
				left join v_Evn EvnUP on EvnUP.Evn_id = eup.EvnUslugaPar_pid
				left join v_EvnDirection_all ed on ed.EvnDirection_id = eup.EvnDirection_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = eup.UslugaComplex_id
				left join v_Lpu l on l.Lpu_id = eup.Lpu_id
				left join v_MedService ms on ms.MedService_id = ed.MedService_id
				left join v_EvnXml ex on ex.Evn_id = eup.EvnUslugaPar_id
				-- end from
			where
				-- where
				eup.EvnUslugaPar_setDT is not null
				and coalesce(EvnUP.EvnClass_SysNick, '') != 'EvnUslugaPar'
				{$filter}
				-- end where
			order by
				-- order by
				eup.EvnUslugaPar_setDate DESC
				-- end order by
		";
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $data);
		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = count($response['data']) + intval($data['start']);
			return $response;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка назначенных, но не выполненных исследований в ЭМК
	 *  //yl данные объединяются с запросом из PG
	 */
	function loadEvnUslugaParPanelWithoutPG($data)
	{
		$response = array();
		$response['data'] = array();
		$response['totalCount'] = 0;
		return $response;
	}

	/**
	 *  Получение списка исследований в ЭМК
	 */
	function loadEvnUslugaParResults($data)
	{
		$arrValText = array(' ');
		$arrUsl =  $this->queryResult("
			SELECT 
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				to_char(eup_issl.EvnUslugaPar_setDT, 'dd-mm-yyyy') as \"Evn_setDT\",
				eup_issl.EvnUslugaPar_setDT as \"EvnUslugaPar_setDT\",
				null as \"textValue\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValueText\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				--2 as \"UslugaTest_ResultLower\",
				--4 as \"UslugaTest_ResultUpper\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\"
			FROM 
				v_EvnUslugaPar eup_issl
				inner join v_UslugaTest ut on ut.UslugaTest_pid = eup_issl.EvnUslugaPar_id
				left join v_UslugaComplex uc on ut.UslugaComplex_id = uc.UslugaComplex_id
			WHERE 
				eup_issl.Person_id = :Person_id
				and eup_issl.EvnUslugaPar_setDT IS NOT null 
				and ut.UslugaTest_ResultApproved = 2
				and (eup_issl.UslugaComplex_id = :UslugaComplex_id OR ut.UslugaComplex_id = :UslugaComplex_id)
			order by eup_issl.EvnUslugaPar_setDT
		", $data);

		foreach($arrUsl as $key => $usl){
			if (!is_numeric($usl['UslugaTest_ResultValue'])) {
				$index = array_search($usl['UslugaTest_ResultValue'], $arrValText);
				if ($index !== false)
					$arrUsl[$key]['UslugaTest_ResultValue'] = $index;
				else {
					$arrUsl[$key]['UslugaTest_ResultValue'] = count($arrValText);
					$arrValText[] = $usl['UslugaTest_ResultValue'];
				}
				$arrUsl[$key]['textValue'] = true;
			}
		}
		return $arrUsl;
	}
	/**
	 *  Получение списка тесто в исследовании
	 */
	function checkForComplexUslugaList($data)
	{
		return $this->queryResult("
			SELECT 
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				uc.UslugaComplex_Code as \"UslugaComplex_Code\"
			FROM 
				v_EvnUslugaPar eup_issl
				inner join v_UslugaTest ut on ut.UslugaTest_pid = eup_issl.EvnUslugaPar_id
				left join v_UslugaComplex uc on ut.UslugaComplex_id = uc.UslugaComplex_id
			WHERE 
				eup_issl.Person_id = :Person_id
				and eup_issl.EvnUslugaPar_setDT IS NOT null 
				and ut.UslugaTest_ResultApproved = 2
				and (eup_issl.UslugaComplex_id = :UslugaComplex_id OR ut.UslugaComplex_id = :UslugaComplex_id)
			group by ut.UslugaComplex_id,uc.UslugaComplex_Name,uc.UslugaComplex_Code
		", $data);
	}
	
	function EvnUslugaParExustsInMainDb($id)
	{
		return $this->getFirstResultFromQuery("
			select
				count(*)
			from v_EvnUslugaPar
			where EvnUslugaPar_id = :id
		", ['id' => $id]);
	}

	 /* Печать лабораторных исследований в формате HL7
	 * $data[MedStaffFact_id]  - автор подписи
	 * $data[EvnUslugaPar_id] - ID услуги (из v_EvnUslugaPar)
	 * возвращает массив с XML
	 * если ошибка - исключение (в АРМ-е выводится окно с текстом сообщения)
 	 */
	function printEvnUslugaParHL7($data)
	{
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		if (!empty($isEMDEnabled)) {
			$this->emddb = $this->load->database('emd', true); // своя БД на PostgreSQL
		} else {
			throw new Exception('Не настроена база данных для хранения электронных медицинских документов');
		}

		if (!$this->usePostgreLis){
			$resp = $this->queryResult("
				select 
					eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",										/*ID документа*/
					LpuOID.PassportToken_tid as \"PassportToken_tid\",								/*OID организации*/
					--msf.MedPersonal_id,															/**/
					--msf.Person_SurName as MedPersonal_SurName,									/*Фамилия, Имя, Отчество автор документа*/
					--msf.Person_FirName as MedPersonal_FirName,
					--msf.Person_SecName as MedPersonal_SecName,
					L.Lpu_Nick as \"Lpu_Nick\",														/*Наименование медицинской организации*/
					L.Lpu_Phone as \"Lpu_Phone\",													/*тел медорганизации*/
					L.Org_Name as \"Org_Name\",														/*Наименование медицинской организации полное*/
					OL.Org_Www as \"Lpu_Www\",															/*сайт медорганизации*/
					lua.Address_Address as \"LAddress_Address\",										/*Адрес медицинской организаци*/
					lua.KLRgn_id as  \"LKLRgn_id\",														/*код региона*/
					psr.Person_Snils as \"Person_Snils\",																/*СНИЛС пациента*/
					psr.Person_id as \"Person_id\",																	/*идентификатор пациента*/
					psr.Person_SurName as \"Person_SurName\",																/*фамилия пациента*/
					psr.Person_FirName as \"Person_FirName\",																/*имя пациента*/
					psr.Person_SecName as \"Person_SecName\",																/*отчество пациента*/
					to_char(psr.Person_BirthDay, 'yyyy-mm-dd') as \"Person_BirthDay\",					/*ФОРМАТ YYYYMMDD !!!!*/
					s.Sex_code as \"Sex_code\",																		/*код пола*/		
					s.Sex_Name as \"Sex_Name\",																		/*тект пола*/
					--mp.MedPost_Code,																/*Код должности автора*/
					--mp.MedPost_Name,																/* имя должности автора*/
					to_char(eup.EvnUslugaPar_setDate, 'yyyy-mm-dd HH24:MI:SS') as \"Document_DateCreate\", 		/*Дата создания документа*/

					eup.EvnUslugaPar_rid as \"EvnUslugaPar_rid\",															/*Уникальный идентификатор случая оказания медицинской помощи*/

					to_char(eup.EvnUslugaPar_signDT, 'yyyy-mm-dd HH24:MI:SS') as \"EvnUslugaPar_signDT\",		/*Дата подписи документа автором*/
					msf_sign.MedPersonal_id as \"Sig_MedPersonal_id\", 									/*ID автора подписи*/
					msf_sign.Person_SurName as \"Sig_MedPersonal_SurName\",								/*Фамилия, Имя, Отчество подписавшего документа*/
					msf_sign.Person_FirName as \"Sig_MedPersonal_FirName\",
					msf_sign.Person_SecName as \"Sig_MedPersonal_SecName\",
					mp_sign.MedPost_Code as \"Sig_MedPost_Code\",										/*Код должности автора*/
					mp_sign.MedPost_Name as \"Sig_MedPost_Name\",										/* имя должности автора*/

					to_char(els.EvnLabSample_DelivDT, 'yyyy-mm-dd HH24:MI:SS') as \"EvnLabSample_DelivDT\", 	/*Дата начала исследования (доставка материала в лабораторию)*/
					to_char(els.EvnLabSample_StudyDT, 'yyyy-mm-dd HH24:MI:SS') as \"EvnLabSample_StudyDT\", 	/*Дата окончания исследования*/

					msfs.MedPersonal_id as \"EvnLabSample_Doctor_id\",									/*исполнитель исследования (врач)*/
					msfs.Person_SurName as \"EvnLabSample_Doctor_SurName\",								/*Фамилия, Имя, Отчество автор документа*/
					msfs.Person_FirName as \"EvnLabSample_Doctor_FirName\",
					msfs.Person_SecName as \"EvnLabSample_Doctor_SecName\",
					mps.MedPost_Code as \"EvnLabSampleMedPost_Code\",									/*Код должности автора*/
					mps.MedPost_Name as \"EvnLabSampleMedPost_Name\",									/* имя должности автора*/

					EvnLabSampleLpuOID.PassportToken_tid as \"EvnLabSample_PassportToken_tid\",			/*OID организации делающий анализ*/
					EvnLabSampleL.Lpu_Phone as \"EvnLabSampleLpu_Phone\",								/*тел медорганизации*/
					EvnLabSampleL.Org_Name as \"EvnLabSampleOrg_Name\",									/*Наименование медицинской организации полное*/
					EvnLabSampleLua.Address_Address as \"EvnLabSampleLAddress_Address\",				/*Адрес медицинской организаци*/
					EvnLabSampleLua.KLRgn_id as \"EvnLabSampleLKLRgn_id\",								/*код региона*/


					--eup.EvnUslugaPar_pid,																/*ссылка на движение EvnSection или посещение EvnVizitPL */
					usc.UslugaComplex_Code as \"UslugaComplex_Code\",									/*Код услуги*/
					usc.UslugaComplex_Name as \"UslugaComplex_Name\",									/*Имя услуги*/
					usc.UslugaComplex_id as \"UslugaComplex_id\",
					elr.EvnLabRequest_BarCodes as \"EvnLabRequest_BarCodes\",							/*штрихкод в виде номер_проб:штрих-код*/
					elr.EvnLabRequest_id as \"EvnLabRequest_id\",										/*ID заявки лаб исследования*/
					els.EvnLabSample_id as \"EvnLabSample_id\",											/*ID пробы исследования*/
					ltm.LabTestMaterial_Code as \"LabTestMaterial_Code\",								/*лаб материал код*/
					ltm.LabTestMaterial_Name as \"LabTestMaterial_Name\",								/*лаб материал имя*/
					a.Analyzer_Name as \"Analyzer_Name\",												/*имя анализатора*/

					/*врачебн помощь*/
					coalesce(eps.EvnPS_NumCard,epl.EvnPL_NumCard) as \"MedHelpId\",						/*это елемент extension для стационара или поликлмнники*/
					eu.EvnUsluga_id as \"EvnUsluga_id\",												/*ID паракл,услуги*/
					to_char(e.Evn_setDT, 'yyyy-mm-dd HH24:MI:SS') as \"MedHelpStart\",					/*даты начала и конца случая*/
					to_char(e.Evn_disDT, 'yyyy-mm-dd HH24:MI:SS') as \"MedHelpEnd\",
					LpuOIDMedHelp.PassportToken_tid as \"MedHelpOID\"								/*OID стационара/поликлинники*/
						from 
						v_EvnUslugaPar eup
						--left join v_MedStaffFact msf on msf.MedStaffFact_id = eup.MedStaffFact_id

						left join fed.v_PassportToken LpuOID on LpuOID.Lpu_id = eup.Lpu_id
						left join v_Lpu l on l.Lpu_id = eup.Lpu_id
						left join v_Org ol on ol.Org_id = l.Org_id
						left join v_Address_all lua on lua.Address_id = l.UAddress_id
						left join v_PersonState psr on psr.Person_id = eup.Person_id
						left join v_Sex s on s.Sex_id = psr.Sex_id

						--left join persis.Post p on p.id = msf.Post_id
						--left join nsi.v_MedPost mp on mp.MedPost_id = p.MedPost_id

						left join v_MedStaffFact msf_sign on (msf_sign.MedStaffFact_id = :MedStaffFact_id and eup.Lpu_id=msf_sign.Lpu_id )
						left join persis.Post p_sign  on p_sign.id = msf_sign.Post_id
						left join nsi.v_MedPost mp_sign on mp_sign.MedPost_id = p_sign.MedPost_id	

						left join v_EvnLabSample els  on els.EvnLabSample_id=eup.EvnLabSample_id


						left join persis.v_MedWorker mw  on mw.MedWorker_Id = coalesce(els.MedPersonal_aid,els.MedPersonal_did)
						left join v_MedStaffFact msfs  on (msfs.Person_id = mw.Person_id and eup.Lpu_id=msfs.Lpu_id and (els.LpuSection_did=msfs.LpuSection_id or els.LpuSection_aid=msfs.LpuSection_id))
						left join persis.Post ps on ps.id = msfs.Post_id
						left join nsi.v_MedPost mps on mps.MedPost_id = ps.MedPost_id

						/*материал*/
						left join v_RefSample RefSample_id_ref  on RefSample_id_ref.RefSample_id = els.RefSample_id
						left join v_RefMaterial rm on rm.RefMaterial_id = RefSample_id_ref.RefMaterial_id
						left join nsi.LabTestMaterial ltm on ltm.LabTestMaterial_id = rm.LabTestMaterial_id

						/*имя организация лаборатории*/
						/*inner join v_MedService ms on ms.MedService_id = els.MedService_id Это пункт забора служба внутри*/

						left join fed.v_PassportToken EvnLabSampleLpuOID coalesce on EvnLabSampleLpuOID.Lpu_id = coalesce(els.Lpu_aid,els.Lpu_did)
						left join v_Lpu EvnLabSampleL on EvnLabSampleL.Lpu_id = coalesce(els.Lpu_aid,els.Lpu_did)
						left join v_Address_all EvnLabSampleLua on EvnLabSampleLua.Address_id = EvnLabSampleL.UAddress_id

						/*услуги*/
						left join v_EvnUsluga eu on eu.EvnUsluga_id=eup.EvnUslugaPar_id
						left join v_UslugaComplex usc on usc.UslugaComplex_id=eu.UslugaComplex_id

						/*заявка*/
						left join v_EvnLabRequest elr on elr.EvnLabRequest_id=els.EvnLabRequest_id

						/*анализатор*/
						left join lis.Analyzer a on a.Analyzer_id=els.Analyzer_id

						/*врачебная помощь*/
						left JOIN v_Evn e ON (eup.EvnUslugaPar_rid = e.Evn_id and e.EvnClass_id<>47)
						left join dbo.EvnPS eps on (eps.Evn_id = e.Evn_id)
						left join dbo.EvnPLBase eplb on (eplb.Evn_id = e.Evn_id)
						left join dbo.EvnPL epl on (epl.EvnPLBase_id = eplb.EvnPLBase_id)
						left join fed.v_PassportToken LpuOIDMedHelp on LpuOIDMedHelp.Lpu_id = e.Lpu_id

						where
							eup.EvnUslugaPar_id = :EvnUslugaPar_id 
							limit 1
				", [
					'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],	//ID самой услуги
					'MedStaffFact_id' => $data['MedStaffFact_id'],	//ID автора подписи
				]);

			if (empty($resp[0]['EvnUslugaPar_id'])) {
				throw new Exception('Запись '.$data['EvnUslugaPar_id'].' не найдена', 500);
			}

			//ID подписывающего
			$resp[0]['SignMedStaffFact_id']=$data['MedStaffFact_id'];

			//костыльная проверка на материалы: если нет тогда 138 
			if (empty($resp[0]["LabTestMaterial_Code"])) {
				$resp[0]["LabTestMaterial_Code"]=138;
				$resp[0]["LabTestMaterial_Name"]="Любой образец, кроме уточненных отдельными показателями";
			}

			/** 
			* делаем выборку услуг внутри пробы
			*/
			$uslugi=$this->queryResult("
				select 
					lt.LabTest_Code as \"LabTest_Code\",													/*код услуги по справочнику*/
					lt.LabTest_Name as \"LabTest_Name\",
					concat(uc.UslugaComplex_Code + ' ' + uc.UslugaComplex_Name) as \"Analize_Name\",  /*код + имя услуги*/
					to_char(ut.UslugaTest_setDT, 'yyyy-mm-dd HH24:MI:SS') as \"UslugaTest_setDT\",
					ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
					ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
					concat(ut.UslugaTest_ResultLower + ' - ' + ut.UslugaTest_ResultUpper) as \"RefLimits\",
					ut.UslugaTest_Comment as \"UslugaTest_Comment\",
					a.Analyzer_Name as \"Analyzer_Name\",
					uc.UslugaComplex_id as \"UslugaComplex_id\",
					els.EvnLabSample_id as \"EvnLabSample_id\"

						from 
							v_UslugaTest ut
							left join v_UslugaComplex uc on ut.UslugaComplex_id = uc.UslugaComplex_id
							left join UslugaComplex ucg on uc.UslugaComplex_2011id = ucg.UslugaComplex_id
							left join nsi.NomenMedUsluga nmu on nmu.NomenMedUsluga_Code = coalesce(uc.UslugaComplex_oid, ucg.UslugaComplex_oid)

							left join nsi.LabTestLink ltl on ltl.NomenMedUsluga_id = nmu.NomenMedUsluga_id
							left join nsi.LabTest lt on lt.LabTest_id = ltl.LabTest_id

							left join v_EvnLabSample els on els.EvnLabSample_id=ut.EvnLabSample_id
							left join lis.Analyzer a on a.Analyzer_id=els.Analyzer_id

							/*left join v_RefValues as rf on rf.RefValues_id=ut.RefValues_id*/

							 where ut.UslugaTest_ResultApproved=2
								and ut.UslugaTest_pid=:EvnUslugaPar_id

						",["EvnUslugaPar_id"=>$resp[0]["EvnUslugaPar_id"]]);

			$resp[0]["uslugi"]=$uslugi;

			/*рассмотрим вариант, когда врачебная помощь не найдена, берем данные из паракл. услуги*/
			if (empty($resp[0]["MedHelpId"]) || empty($resp[0]["MedHelpOID"])) {
				$resp[0]["MedHelpId"]=$resp[0]["EvnUsluga_id"];
				$resp[0]["MedHelpOID"]=$resp[0]["EvnLabSample_PassportToken_tid"];
			}

			/*дата подписи; если пусто ставим дату текущую, по логике в текущий момент могут подписать*/
			if (empty($resp[0]['EvnUslugaPar_signDT'])){
				$resp[0]['EvnUslugaPar_signDT']=date("Y-m-d H:i:s");
			}
			$resp[0]['EvnUslugaPar_signDT']=date("YmdHisO", strtotime($resp[0]['EvnUslugaPar_signDT']));
			$resp[0]['EvnLabSample_StudyDT']=date("YmdHisO", strtotime($resp[0]['EvnLabSample_StudyDT']));
			$resp[0]['EvnLabSample_DelivDT']=date("YmdHisO", strtotime($resp[0]['EvnLabSample_DelivDT']));
			$resp[0]['MedHelpStart']=date("YmdHisO", strtotime($resp[0]['MedHelpStart']));
			$resp[0]['MedHelpEnd']=date("YmdHisO", strtotime($resp[0]['MedHelpEnd']));

			/*костыли для верной выборки штрих-кода (нужно удалить последнюю запятую)*/
			$resp[0]['EvnLabRequest_BarCodes']=explode(',',trim($resp[0]['EvnLabRequest_BarCodes']," \t\n\r\0\x0B,"));
			$bar_codes=[];
			foreach ($resp[0]['EvnLabRequest_BarCodes'] as $item){
				$item=explode(":",$item);
				$bar_codes[$item[0]]=$item[1];
			}
			//ключ это EvnLabSample_id
			$resp[0]['EvnLabRequest_BarCodes']=$bar_codes;
			
			$emd = $this->queryResult('
			select
				COALESCE(MAX("EMDVersion_VersionNum") + 1, 1) as "Version",
				COALESCE((select MAX("EMDVersion_id") from "EMD"."EMDVersion" ) + 1, 1) as "EMDVersion_id"
					from
						"EMD"."EMDVersion" e2
						inner join "EMD"."EMDRegistry" emdr on e2."EMDRegistry_id" = emdr."EMDRegistry_id"
							where
								emdr."EMDRegistry_ObjectName" = :EMDRegistry_ObjectName
								and emdr."EMDRegistry_ObjectID" = :EMDRegistry_ObjectID
			', ["EMDRegistry_ObjectName"=>'EvnUslugaPar',"EMDRegistry_ObjectID"=>$resp[0]["EvnUslugaPar_id"]]
			, $this->emddb);
			//добавим номер версии документа
			$resp[0]["DocVersion"]=$emd[0]["Version"];
			$resp[0]["EMDVersion_id"]=$emd[0]["EMDVersion_id"];
		}
		

		if ($this->usePostgreLis){
			$this->load->swapi("lis");
			$this->load->swapi("common");
			/*Собственно услуга*/
			$EvnUslugaPar=$this->lis->GET("EvnUsluga/ParViewData",["EvnUslugaPar_id"=>$data['EvnUslugaPar_id']])["data"][0];
			/*промежуточная*/
			$EvnLabSampleAndRequest=$this->lis->GET("EvnDirection/EvnLabSampleAndRequest",["EvnDirection_id"=>$EvnUslugaPar['EvnDirection_id']])["data"];
			
			$EvnLabRequest=$this->lis->GET("EvnLabRequest",["EvnLabRequest_id"=>$EvnLabSampleAndRequest["EvnLabRequest_id"]])["data"][0];
			$EvnLabSample=$this->lis->GET("EvnLabSample",["EvnLabSample_id"=>$EvnLabSampleAndRequest["EvnLabSample_id"]])["data"][0];

			$resp=[];
			$resp[0]=[];
			$resp[0]["EvnUslugaPar_id"]=$EvnUslugaPar["EvnUslugaPar_id"];
			$resp[0]["Lpu_Nick"]=$EvnUslugaPar["DirectLpu_Nick"];
			$resp[0]["Lpu_Name"]=$EvnUslugaPar["DirectLpu_Name"];
			$resp[0]["Org_Name"]=$EvnUslugaPar["DirectLpu_Name"];
			$resp[0]["LAddress_Address"]=$EvnUslugaPar["DirectLpu_Address"];
			//не достающий код региона
			$r=$this->queryResult("
					select lua.KLRGN_id from 
						v_Address_all lua, v_Lpu l
							where 
								 lua.Address_id = l.UAddress_id and 
								 l.Lpu_id=:Lpu_id
								 ",["Lpu_id"=>$EvnUslugaPar["Lpu_id"]]);
			//будем считать что коды равны и для от кого направление и кто денлает анализы
			$resp[0]["LKLRgn_id"]=$resp[0]["EvnLabSampleLKLRgn_id"]= $r[0]["KLRGN_id"];
			
			//код организации из фед-справочника
			$LpuOID=$this->queryResult("select PassportToken_tid from fed.v_PassportToken   where Lpu_id =:Lpu_id",["Lpu_id"=>$EvnUslugaPar["Lpu_id"]]);
			$resp[0]["PassportToken_tid"]=$LpuOID[0]["PassportToken_tid"];
			$resp[0]["Lpu_Www"]="";
			$resp[0]["Document_DateCreate"]=$EvnUslugaPar["EvnUslugaPar_setDate"]." ".$EvnUslugaPar["EvnUslugaPar_setTime"];
			$resp[0]['EvnUslugaPar_signDT']=date("YmdHisO");
			
			//пациент
			$Person=$this->lis->GET("Person",["Person_id"=>$EvnUslugaPar['Person_id']])["data"][0];
			$resp[0]["Person_Snils"]=$Person["Person_Snils"];
			$resp[0]["Person_id"]=$Person['Person_id'];
			$resp[0]["Person_SurName"]=$Person["Person_Surname"];
			$resp[0]["Person_FirName"]=$Person["Person_Firname"];
			$resp[0]["Person_SecName"]=$Person["Person_Secname"];
			$resp[0]["Person_BirthDay"]=$Person["Person_Birthday_ISO"];
			$resp[0]["Sex_code"]=$Person["Sex_Code"];
			$resp[0]["Sex_Name"]=$Person["Sex_Name"];
				
			//кто подписывает
			$Person=$this->common->GET("MedStaffFact",["MedStaffFact_id"=>$data['MedStaffFact_id']])["data"][0];
			$mp=$this->queryResult("
						select 
							mp_sign.MedPost_Code as \"Sig_MedPost_Code\",
							mp_sign.MedPost_Name as \"Sig_MedPost_Name\"
							  from 
								 v_MedStaffFact msf_sign 
								left join persis.Post p_sign   on p_sign.id = msf_sign.Post_id
								left join nsi.v_MedPost mp_sign  on mp_sign.MedPost_id = p_sign.MedPost_id
									where 
										msf_sign.MedStaffFact_id = :MedStaffFact_id",["MedStaffFact_id"=>$data['MedStaffFact_id']]);

			$resp[0]["SignMedStaffFact_id"]=$Person["MedStaffFact_id"];
			$resp[0]["Sig_MedPersonal_id"]=$Person["Person_id"];
			$resp[0]["Sig_MedPersonal_SurName"]=$Person["Person_SurName"];
			$resp[0]["Sig_MedPersonal_FirName"]=$Person["Person_FirName"];
			$resp[0]["Sig_MedPersonal_SecName"]=$Person["Person_SecName"];
			$resp[0]=array_merge($resp[0],$mp[0]);
			
			//OID и другие данные кто выполнял анализ
			$LpuOID=$this->queryResult("select PassportToken_tid from fed.v_PassportToken   where Lpu_id =:Lpu_id",["Lpu_id"=>$EvnUslugaPar["Lpu_did"]]);
			$resp[0]["Lpu_Nick"]=$EvnUslugaPar["Lpu_Nick"];
			$resp[0]["EvnLabSampleOrg_Name"]=$EvnUslugaPar["Lpu_Name"];
			$resp[0]["EvnLabSampleOrg_Name"]=$EvnUslugaPar["Lpu_Name"];
			$resp[0]["EvnLabSampleLAddress_Address"]=$EvnUslugaPar["Lpu_Address"];
			$resp[0]["EvnLabSampleLpu_Phone"]=$EvnUslugaPar["DirectLpu_Phone"];
			
			
			/*Дата начала окончания исследования*/
			$resp[0]["EvnLabSample_DelivDT"]=$EvnLabSample["EvnLabSample_DelivDT_ISO"];
			$resp[0]["EvnLabSample_StudyDT"]=$EvnLabSample["EvnLabSample_StudyDT_ISO"];

			//исполнитель 
			$Person=$this->common->GET("MedPersonal/Fio",["MedPersonal_id"=>$EvnLabSample['MedPersonal_did']])["data"];
			//недостающие данные для получения из фед справочников
			$mp=$this->queryResult("
						select 
							mp.MedPost_Code as \"EvnLabSampleMedPost_Code\",
							mp.MedPost_Name as \"EvnLabSampleMedPost_Name\",
							msf.MedStaffFact_id as \"EvnLabSample_Doctor_id\"
							  from 
								 v_MedStaffFact msf 
								left join persis.Post p   on p.id = msf.Post_id
								left join nsi.v_MedPost mp  on mp.MedPost_id = p.MedPost_id
								left join persis.v_MedWorker mw   on mw.MedWorker_Id = :MedPersonal_did and :Lpu_did=msf.Lpu_id and (msf.LpuSection_id=:LpuSection_did or :LpuSection_aid=msf.LpuSection_id)
									where 
										msf.Person_id=mw.Person_id
										",[
											"MedPersonal_did"=>$EvnLabSample['MedPersonal_did'],
											"LpuSection_did"=>$EvnLabSample['LpuSection_did'],
											"LpuSection_aid"=>$EvnLabSample['LpuSection_aid'],
											"Lpu_did"=>$EvnLabSample['Lpu_did']
										]);
			$resp[0]=array_merge($resp[0],$mp[0]);
			$resp[0]["EvnLabSample_Doctor_SurName"]=$Person["Person_SurName"];
			$resp[0]["EvnLabSample_Doctor_FirName"]=$Person["Person_FirName"];
			$resp[0]["EvnLabSample_Doctor_SecName"]=$Person["Person_SecName"];

			//исполнитель - OID
			//код организации из фед-справочника
			$LpuOID=$this->queryResult("select PassportToken_tid from fed.v_PassportToken   where Lpu_id =:Lpu_id",["Lpu_id"=>($EvnLabSample["Lpu_aid"]) ? $EvnLabSample["Lpu_aid"]: $EvnLabSample["Lpu_did"]]);
			$resp[0]["EvnLabSample_PassportToken_tid"]=$LpuOID[0]["PassportToken_tid"];
			
			//коды услуг
			$LabRequestUslugaComplexData=$this->lis->GET("EvnLabRequest/LabRequestUslugaComplexData",["EvnUslugaPar_id"=>$data['EvnUslugaPar_id']])["data"][0];
			//читаем из справочника
			$Usluga_Complex=$this->queryResult("
					select *
						from v_UslugaComplex 
							where UslugaComplex_id = :UslugaComplex_id",["UslugaComplex_id"=>$LabRequestUslugaComplexData["UslugaComplex_id"]]);
			
			$resp[0]["UslugaComplex_Code"]=$Usluga_Complex[0]["UslugaComplex_Code"];
			$resp[0]["UslugaComplex_Name"]=$Usluga_Complex[0]["UslugaComplex_Name"];
			$resp[0]["EvnLabRequest_BarCodes"][$EvnLabSampleAndRequest["EvnLabSample_id"]]=$EvnLabSample["EvnLabSample_Barcode"];
		
			//анализатор
			$Analyzer=$this->lis->GET("Analyzer",["Analyzer_id"=>$EvnLabSample['Analyzer_id']])["data"][0];
			$resp[0]["Analyzer_Name"]=$Analyzer["Analyzer_Name"];

			//параметры врачебной помощи, идентификаторы, даты
			$p=$this->queryResult("					
					select 
						coalesce(eps.EvnPS_NumCard,epl.EvnPL_NumCard) as \"MedHelpId\",					/*это елемент extension для стационара или поликлмнники*/
						--eu.EvnUsluga_id,															/*ID паракл,услуги*/
						convert(varchar(20),e.Evn_setDT, 120) as \"MedHelpStart\",						/*даты начала и конца случая*/
						convert(varchar(20),e.Evn_disDT, 120) as \"MedHelpEnd\",
						LpuOIDMedHelp.PassportToken_tid as \"MedHelpOID\"					/*OID стационара/поликлинники*/

						from v_Evn e  
						left join dbo.EvnPS eps  on (eps.Evn_id = e.Evn_id)
						left join dbo.EvnPLBase eplb  on (eplb.Evn_id = e.Evn_id)
						left join dbo.EvnPL epl  on (epl.EvnPLBase_id = eplb.EvnPLBase_id)
						left join fed.v_PassportToken LpuOIDMedHelp  on LpuOIDMedHelp.Lpu_id = e.Lpu_id
					
						where
							e.Evn_id=:EvnUslugaPar_rid and e.EvnClass_id<>47",["EvnUslugaPar_rid"=>$EvnUslugaPar["EvnUslugaPar_rid"]]);
			if (empty($p[0])){
				//не найдена услуга мед помощи, берем саму парак. услугу
				$p[0]["MedHelpId"]=$resp[0]["EvnUslugaPar_id"];
				$p[0]["MedHelpStart"]=$resp[0]["EvnLabSample_DelivDT"];
				$p[0]["MedHelpEnd"]=$resp[0]["EvnLabSample_StudyDT"];
				$p[0]["MedHelpOID"]=$resp[0]["EvnLabSample_PassportToken_tid"];
			}

			$resp[0]=array_merge($resp[0],$p[0]);

			$uslugi=$this->lis->GET("EvnLabSample/ResultGrid",["EvnDirection_id"=>$EvnUslugaPar['EvnDirection_id'],"EvnLabSample_id"=>$EvnLabSample["EvnLabSample_id"]])["data"];
			$u=[];
			foreach ($uslugi as $k=>$item){
				if ($item["UslugaTest_ResultApproved"]!=2 /*|| $item["EvnLabSample_id"]!=$EvnUslugaPar["EvnUslugaPar_id"]*/ || $item["UslugaTest_pid"]!=$EvnUslugaPar["EvnUslugaPar_id"]){
					continue;
				}
				$u[$k]["Analize_Name"]=$item["UslugaComplex_Code"]." ".$item["UslugaComplex_Name"];
				$u[$k]["UslugaTest_setDT"]=$item["UslugaTest_setDT"];
				$u[$k]["UslugaTest_ResultValue"]=$item["UslugaTest_ResultValue"];
				$u[$k]["UslugaTest_ResultUnit"]=$item["UslugaTest_ResultUnit"];
				$u[$k]["RefLimits"]=$item["UslugaTest_ResultLower"]." - ".$item["UslugaTest_ResultUpper"];
				$u[$k]["UslugaComplex_id"]=$item["UslugaComplex_id"];
				$u[$k]["UslugaTest_Comment"]=$item["UslugaTest_Comment"];
				$u[$k]["EvnLabSample_id"]=$item["EvnLabSample_id"];
				//анализатор
				$Analyzer=$this->lis->GET("Analyzer",["Analyzer_id"=>$item['Analyzer_id']])["data"][0];
				$u[$k]["Analyzer_Name"]=$Analyzer["Analyzer_Name"];

				//федеральные справочники, пока не используются
				/*$p=$this->queryResult("
							select lt.LabTest_Code, lt.LabTest_Name
								from nsi.LabTest lt,v_UslugaComplex uc,UslugaComplex ucg,nsi.NomenMedUsluga nmu,nsi.LabTestLink ltl
									where
										lt.LabTest_id = ltl.LabTest_id and
										uc.UslugaComplex_id = :UslugaComplex_id and
										uc.UslugaComplex_2011id = ucg.UslugaComplex_id and
										nmu.NomenMedUsluga_Code = coalesce(uc.UslugaComplex_oid, ucg.UslugaComplex_oid) and
										ltl.NomenMedUsluga_id = nmu.NomenMedUsluga_id

					",["UslugaComplex_id"=>$item["UslugaComplex_id"]]);
				if (!empty($p)){
					$u[$k]["LabTest_Name"]=$p[0]["LabTest_Name"];
					$u[$k]["LabTest_Code"]=$p[0]["LabTest_Code"];
				}*/
			}
			$resp[0]["uslugi"]=$u;
			//костыльная проверка на материалы: если нет тогда 138 
			if (empty($resp[0]["LabTestMaterial_Code"])) {
				$resp[0]["LabTestMaterial_Code"]=138;
				$resp[0]["LabTestMaterial_Name"]="Любой образец, кроме уточненных отдельными показателями";
			}

			//проеобразуем дату в объект по формату в объект DateTime
			foreach ([
				"Document_DateCreate",
				"EvnLabSample_DelivDT",
				"EvnLabSample_StudyDT",
				"MedHelpStart",
				"MedHelpEnd",
				] as $name) {
				$resp[0][$name]=date("YmdHisO",strtotime($resp[0][$name]));
			}
			//дата рождения пациента
			$resp[0]["Person_BirthDay"]=date("Ymd",strtotime($resp[0]["Person_BirthDay"]));
			/*читаем базу EMD и получаем номер версии документа, тупо увеличиваем на 1
			* за данное мгновение маловероятно что будет много запросов, тем более этот номер нужен лишь для затирания пред, документа*/
			$v=$this->lis->GET("EMD/EMDSignDocNextVersion",["EMDRegistry_ObjectName"=>'EvnUslugaPar',"EMDRegistry_ObjectID"=>$resp[0]["EvnUslugaPar_id"]]);
			//добавим номер версии документа ID версии
			$resp[0]["DocVersion"]=$v["data"]["Version"];
			$resp[0]["EMDVersion_id"]=$v["data"]["EMDVersion_id"];
		}

		/**
		* проверка на обязательность
		*/
		$errors=[];
		if (empty($resp[0]["Person_Snils"])){
			$errors[]="В карточке пациента не указан СНИЛС";
		}
		
		if (!empty($errors)) {
			throw new Exception('<b>Обнаружены ошибки:<br></b>'.implode("<br>",$errors), 500);
		}
		$this->load->library('parser');

		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<?xml-stylesheet type="text/xsl" href="/documents/xsl/LAB.xsl"?>';
		$xml .= '<?valbuddy_schematron Schematron.sch?>';
		$xml .= $this->parser->parse('print_EvnUslugaPar_hl7', $resp[0], true); //дополнительный вьювер-генератор XML путем конкатенации
		
		// проверяем xml по xsd схеме
		$xsd = realpath('documents/xsd/Par/CDA.xsd');
		$domDoc = new DOMDocument();
		$domDoc->loadXML($xml);
		libxml_use_internal_errors(true);
		if (!$domDoc->schemaValidate($xsd)) {
			$errors = array_map(function ($error) {
				return trim($error->message) . ' on line ' . $error->line;
			}, libxml_get_errors());
			libxml_clear_errors();
			if (!empty($_REQUEST['getDebug'])) {
				echo "<textarea cols=150 rows=20>" . $xml . "</textarea>";
			}
			throw new Exception('Ошибка при проверке документа в формате HL7 по XSD схеме: <br>' . implode("<br>", $errors) . '<br><br>Сформированный HL7:<br><textarea cols="50" rows="10">'.$xml.'</textarea>');
		}

		return array('xml' => $xml);
	}


}
