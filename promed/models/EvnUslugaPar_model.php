<?php
class EvnUslugaPar_model extends swModel {
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
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}

		$selectPersonData = "PS.Person_SurName+' '+PS.Person_FirName+' '+isnull(PS.Person_SecName,'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null 
					then ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'') 
					else peh.PersonEncrypHIV_Encryp 
				end as Person_Fio,
				null as Person_Birthday,";
		}

		$query = "
			SELECT top 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				EUP.EvnUslugaPar_id,
				EUP.EvnPrescr_id,
				EUP.EvnUslugaPar_pid,
				ED.EvnDirection_id,
				EUP.Person_id,
				EUP.PersonEvn_id,
				EUP.Server_id,
				EUP.Usluga_id,
				EUP.UslugaComplex_id,
				EUP.EvnUslugaPar_isCito,
				EUP.EvnUslugaPar_Kolvo,
				EUP.PayType_id,
				EUP.PrehospDirect_id,
				EUP.TimetablePar_id,
				EUP.EvnUslugaPar_IsSigned,
				EUP.Lpu_id,
				EUP.LpuSection_uid,
				EUP.MedPersonal_id as MedStaffFact_uid,
				DLpuSection.Lpu_id as Lpu_did,
				EUP.LpuSection_did,
				EUP.Org_did,
				EUP.MedPersonal_did as MedStaffFact_did,
				EUP.MedPersonal_sid as MedStaffFact_sid,
				{$selectPersonData}
				D.Diag_id,
				isnull(D.Diag_Code,'') as Diag_Code,
				isnull(D.Diag_Name,'') as Diag_Name,
				ISNULL(ucms.UslugaComplex_Name, UC.UslugaComplex_Name) as Usluga_Name,
				EUP.EvnUslugaPar_id as Usluga_Number,
				ULpu.Lpu_Nick,
				ULpu.Lpu_Name,
				ULpu.UAddress_Address as Lpu_Address,
				ULpuSection.LpuSection_Code,
				ULpuSection.LpuSection_Name,
				convert(varchar(10), EUP.EvnUslugaPar_setDT, 104) as EvnUslugaPar_setDate,
				convert(varchar(10), EUP.EvnUslugaPar_updDT, 108) as EvnUslugaPar_setTime,
				MP.Person_SurName + ' ' + LEFT(MP.Person_FirName, 1)  + '. ' + ISNULL(LEFT(MP.Person_SecName, 1) + '.', '') as MedPersonal_Fin,
				--case when DLpuSection.LpuSection_Code is not null then DLpuSection.LpuSection_Code else ISNULL(DOrg.Org_Code,'') end as DirectSubject_Code,
				--case when DLpuSection.LpuSection_Name is not null then DLpuSection.LpuSection_Name else ISNULL(DOrg.Org_Nick,'') end as DirectSubject_Name,
				DLpuSection.LpuSection_Code as DirectSubject_Code,
                DLpuSection.LpuSection_Name as DirectSubject_Name,
                DOrg.Org_Code as OrgDirectSubject_Code,
                DOrg.Org_Nick as OrgDirectSubject_Name,
				ED.EvnDirection_Num as EvnDirection_Num,
				convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
				DMedPersonal.Person_SurName + ' ' + LEFT(DMedPersonal.Person_FirName, 1)  + '. ' + ISNULL(LEFT(DMedPersonal.Person_SecName, 1) + '.', '') as MedPersonalDirect_Fin,
				case when EvnLabRequest.EvnLabRequest_id is null then 0 else 1 end as isLab,
				EUP.Study_uid,
				convert(varchar(10), ecp.EvnCostPrint_setDT, 104) as EvnCostPrint_setDT,
				ecp.EvnCostPrint_IsNoPrint,
				STR(ecp.EvnCostPrint_Cost, 19, 2) as CostPrint
			FROM v_EvnUslugaPar EUP with (nolock)
				left join v_EvnCostPrint ecp (nolock) on ecp.Evn_id = EUP.EvnUslugaPar_id
				left join v_Person_all PS with (nolock) on EUP.Person_id = PS.Person_id AND EUP.PersonEvn_id = PS.PersonEvn_id AND EUP.Server_id = PS.Server_id
				left join v_EvnDirection_all ED with (nolock) on EUP.EvnDirection_id = ED.EvnDirection_id and ED.DirFailType_id is null
				left join v_EvnLabRequest EvnLabRequest with (nolock) on EvnLabRequest.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnFuncRequest efr (nolock) on efr.EvnFuncRequest_pid = ed.EvnDirection_id
				left join v_EvnlabSample els (nolock) on els.EvnLabSample_id = eup.EvnLabSample_id
				left join v_MedService MS with (nolock) on els.MedService_id = MS.MedService_id
				left join v_Lpu ULpu with (nolock) on isnull(MS.Lpu_id,EUP.Lpu_id) = ULpu.Lpu_id
				left join v_LpuSection ULpuSection with (nolock) on isnull(MS.LpuSection_id,EUP.LpuSection_uid) = ULpuSection.LpuSection_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = isNull(els.MedPersonal_aid,EUP.MedPersonal_id) AND MP.Lpu_id = isnull(MS.Lpu_id,EUP.Lpu_id)
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUP.UslugaComplex_id
				left join v_UslugaComplexMedService UCMS (nolock) on UCMS.MedService_id = els.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				left join v_Diag D with (nolock) on coalesce(eup.Diag_id, efr.Diag_id, ED.Diag_id) = D.Diag_id
				left join v_LpuSection DLpuSection with (nolock) on isnull(EUP.LpuSection_did,ED.LpuSection_id) = DLpuSection.LpuSection_id
				left join v_Lpu DLpu (nolock) on DLpu.Lpu_id = ED.Lpu_sid
				left join v_Org DOrg with (nolock) on isnull(EUP.Org_did,DLpu.Org_id) = DOrg.Org_id
				left join v_MedPersonal DMedPersonal with (nolock) on isnull(EUP.MedPersonal_did,ED.MedPersonal_id) = DMedPersonal.MedPersonal_id AND isnull(DLpuSection.Lpu_id,ED.Lpu_id) = DMedPersonal.Lpu_id
				{$join_msf}
				{$joinPersonEncrypHIV}
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
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
						$fields[] = "null as {$key}";
						break;
					case is_string($value):
						$fields[] = "'{$value}' as {$key}";
						break;
					default:
						$fields[] = "{$value} as {$key}";
						break;
				}
			}
			$rows[] = "select ".implode(",", $fields);
		}
		$rows = implode("\nunion\n", $rows);

		$params =  array(
			'Lpu_id' => $data['Lpu_id']
		);

		$accessType = 'row_list.Lpu_id = :Lpu_id';
		$join_msf = '';
		if (isset($data['session']['CurMedStaffFact_id'])) {
			$accessType .= ' and (row_list.MedPersonal_id is null or row_list.MedPersonal_id = MSF.MedPersonal_id) and row_list.LpuSection_uid = MSF.LpuSection_id';
			$join_msf = 'left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :user_MedStaffFact_id';
			$params['user_MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
		}

		$selectPersonData = "PS.Person_SurName+' '+PS.Person_FirName+' '+isnull(PS.Person_SecName,'') as Person_Fio,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null 
					then ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'') 
					else peh.PersonEncrypHIV_Encryp 
				end as Person_Fio,
				null as Person_Birthday,";
		}

		$query = "
			with row_list as (
				{$rows}
			)
			select top 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				{$selectPersonData}
				row_list.*
			from row_list
			left join v_Person_all PS with(nolock) on PS.PersonEvn_id = row_list.PersonEvn_id
			{$join_msf}
			{$joinPersonEncrypHIV}
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
			$filter .= " AND PS.Person_Surname LIKE :Person_SurName";
			$params['Person_SurName'] = $data['Person_SurName'].'%';
		}
		if (!empty($data['Person_FirName']))
		{
			$filter .= " AND PS.Person_Firname LIKE :Person_FirName";
			$params['Person_FirName'] = $data['Person_FirName'].'%';
		}
		if (!empty($data['Person_SecName']))
		{
			$filter .= " AND PS.Person_Secname LIKE :Person_SecName";
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
			SELECT top 100 
				EUP.EvnUslugaPar_id,
				ED.EvnDirection_id,
				EUP.TimetablePar_id,
				convert(varchar(10), TTP.TimetablePar_begTime, 104) as TimetablePar_Date,
				convert(varchar(5), TTP.TimetablePar_begTime, 108) as TimetablePar_begTime,
				EUP.EvnUslugaPar_setTime as TimetablePar_factTime,
				EQ.EvnQueue_id,
				EUP.Person_id,
				EUP.PersonEvn_id,
				EUP.Server_id,
				EUP.Usluga_id,
				EUP.UslugaComplex_id,
				EUP.Lpu_id,
				EUP.Lpu_did,
				EUP.LpuSection_uid,
				EUP.LpuSection_did,
				EUP.MedPersonal_id,
				EUP.MedPersonal_did,
				EUP.MedPersonal_sid,
				convert(varchar(10), EUP.EvnUslugaPar_insDT, 104) as EvnUslugaPar_insDT,
				case when EUP.EvnUslugaPar_setDT is not null then 'true' else 'false' end as Service_isProvided,
				case when (TTP.TimetablePar_begTime is not null)
					then convert(varchar(10), TTP.TimetablePar_begTime, 104) +' '+ convert(varchar(5), TTP.TimetablePar_begTime, 108)
					else case when EQ.EvnQueue_recDT is not null then convert(varchar(10), EQ.EvnQueue_recDT, 104) +' принят из очереди' 
					else case when EQ.EvnQueue_id is not null then 'очередь' else 'б/з' end end
				end as Record_date,
				case when TTP.TimetablePar_begTime is not null 
					then convert(varchar(10), TTP.TimetablePar_begTime, 104)
					else case when EQ.EvnQueue_recDT is not null then convert(varchar(10), EQ.EvnQueue_recDT, 104)
					else case when EQ.EvnQueue_id is not null then 'очередь' else convert(varchar(10), EUP.EvnUslugaPar_insDT, 104) end end
				end as Group_date,
				PS.Person_SurName as Person_Surname,
				PS.Person_FirName as Person_Firname,
				PS.Person_SecName as Person_Secname,
				PS.Person_Fio as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when Person.Person_deadDT is not null then 'true' else 'false' end as Person_IsDead,
				case when ED.EvnDirection_id is not null then 'true' else 'false' end as Direction_exists,
				case when Person.Server_id = 0 then 'true' else 'false' end as Person_IsBDZ,
				-- ЛПУ прикрепления
				Lpu.Lpu_Nick as Person_Lpu,
				case when EUP.UslugaComplex_id is not null then UC.UslugaComplex_Name else U.Usluga_Name end as Usluga_Name,
				case when EUP.EvnUslugaPar_isCito = 2 then 'true' else 'false' end as Service_isCito,
				convert(varchar(10), EUP.EvnUslugaPar_setDT, 104) as EvnUslugaPar_setDT,
				MP.Person_Fin as MedPersonal_Fin,
				EUP.pmUser_insID
			FROM v_EvnUslugaPar EUP with (nolock) 
				left join v_Person_all PS with (nolock) on EUP.Person_id = PS.Person_id AND EUP.PersonEvn_id = PS.PersonEvn_id AND EUP.Server_id = PS.Server_id 
				left join Person with (nolock) on PS.Person_id = Person.Person_id 
				left join PersonCardState PersonCard with (nolock) on PS.Person_id = PersonCard.Person_id AND PersonCard.LpuAttachType_id = 1 AND cast(EUP.EvnUslugaPar_updDT as date) >= cast(PersonCard.PersonCardState_begDate as DATE) AND (PersonCard.PersonCardState_endDate is null OR cast(EUP.EvnUslugaPar_updDT as DATE) <= cast(PersonCard.PersonCardState_endDate as DATE))
				left join v_Lpu Lpu with (nolock) on PersonCard.Lpu_id = Lpu.Lpu_id
				left join v_TimetablePar TTP with (nolock) on EUP.TimetablePar_id = TTP.TimetablePar_id
				left join v_EvnDirection_all ED with (nolock) on TTP.TimetablePar_id = ED.TimetablePar_id and ED.DirFailType_id is null
				left join v_EvnQueue EQ with (nolock) on EUP.EvnUslugaPar_id = EQ.EvnUslugaPar_id AND EQ.TimetablePar_id is null
				left join v_MedPersonal MP with (nolock) on EUP.MedPersonal_id = MP.MedPersonal_id AND MP.Lpu_id = EUP.Lpu_id
				left join v_Usluga U with (nolock) on EUP.Usluga_id = U.Usluga_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUP.UslugaComplex_id
			WHERE
				EUP.Lpu_id = :Lpu_id
				AND EUP.LpuSection_uid = :LpuSection_id
				{$filter}
			ORDER BY
				Person_Surname ASC,Person_Firname ASC,Person_Secname ASC
		";
		/*
		echo getDebugSql($sql, $params);
		exit;
				CASE WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime) THEN 'yellow' ELSE CASE WHEN ps.PersonCloseCause_id = 2 and Person_closeDT is not null THEN 'red' ELSE CASE WHEN ps.Server_pid = 0 THEN 'true' ELSE 'false' END END END as [Person_IsBDZ]

				left join Polis pls with (nolock) on pls.Polis_id = ps.Polis_id
		*/
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnUslugaPar_del
				@EvnUslugaPar_id = :EvnUslugaPar_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			$filter .= " and ED.EvnDirection_setDate >= cast(:EvnDirection_setDate_From as datetime)";
			$queryParams['EvnDirection_setDate_From'] = $data['EvnDirection_setDate_From'];
		}

		if ( isset($data['EvnDirection_setDate_To']) ) {
			$filter .= " and ED.EvnDirection_setDate <= cast(:EvnDirection_setDate_To as datetime)";
			$queryParams['EvnDirection_setDate_To'] = $data['EvnDirection_setDate_To'];
		}

		$query = "
			select
				ED.EvnDirection_id,
				PS.Person_id,
				PS.PersonEvn_id,
				PS.Server_id,
				ED.Lpu_did,
				convert(varchar(10), ED.EvnDirection_setDate, 104) as EvnDirection_setDate,
				ED.EvnDirection_setTime,
				ED.EvnDirection_Num,
				RTRIM(PS.Person_SurName) as Person_Surname,
				RTRIM(PS.Person_FirName) as Person_Firname,
				RTRIM(PS.Person_SecName) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Name,
				MP.Person_Fio as MedPersonal_Fio
			from
				v_EvnDirection_all ED with (NOLOCK)
				inner join v_PersonState PS with(nolock) on PS.Person_id = ED.Person_id
				inner join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = ED.Lpu_id
				inner join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = ED.MedPersonal_id
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
		//$accessType .= ' and (ED.EvnDirection_IsReceive = 2 OR (ED.MedService_id IS NULL and not exists(select top 1 EvnFuncRequest_id from v_EvnFuncRequest (nolock) where EvnFuncRequest_pid = EUP.EvnUslugaPar_id)))'; // не даём редактировать услуги связанные с направлением в лабораторию и с заявкой ФД
		$join_msf = '';
		$params =  array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		// правильнее доступ определять по рабочему месту
		if ((!isSuperAdmin() || !isLpuAdmin() || !isLpuUser()) && empty($data['session']['isMedStatUser']) && !empty($data['session']['medpersonal_id']))
		{
			$accessType .= ' and ISNULL(EUP.MedPersonal_id,:user_MedPersonal_id) = :user_MedPersonal_id';
			$params['user_MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		if ( $data['session']['region']['nick'] == 'pskov' ) {
			$accessType .= " and ISNULL(EUP.EvnUslugaPar_IsPaid, 1) = 1
				and not exists(
					select top 1 RD.Registry_id
					from r60.v_RegistryData RD with(nolock)
						inner join v_Registry R with(nolock) on R.Registry_id = RD.Registry_id
						inner join v_RegistryStatus RS with(nolock) on RS.RegistryStatus_id = R.RegistryStatus_id
					where
						RD.Evn_id = EUP.EvnUslugaPar_id
						and RS.RegistryStatus_SysNick not in ('work','paid')
				)
			";
		}

		$query = "
			SELECT TOP 1
				case when {$accessType} then 'edit' else 'view' end as accessType,
				case when ED.MedService_id IS NULL and not exists(select top 1 EvnFuncRequest_id from v_EvnFuncRequest (nolock) where EvnFuncRequest_pid = EUP.EvnUslugaPar_id) then '0' else '1' end as fromMedService,
				EUP.EvnUslugaPar_id,
				EUP.EvnUslugaPar_pid,
				EUP.EvnDirection_id,
				EUP.MedStaffFact_id as MedStaffFact_uid,
				isnull(EDH.EvnDirectionHistologic_id,EDH2.EvnDirectionHistologic_id) as EvnDirectionHistologic_id,
				case when ED.EvnDirection_id IS NOT NULL then ED.EvnDirection_Num else EUP.EvnDirection_Num end as EvnDirection_Num,
				case when ed.EvnDirection_id is null then 1 else 2 end as EvnUslugaPar_IsWithoutDirection,
				convert(varchar(10), case when ED.EvnDirection_id IS NOT NULL then ED.EvnDirection_setDT else EUP.EvnDirection_setDT end, 104) as EvnDirection_setDate,
				EUP.EvnUslugaPar_isCito,
				EUP.TimetablePar_id,
				EUP.Person_id,
				EUP.PersonEvn_id,
				EUP.UslugaPlace_id,
				EUP.EvnUslugaPar_MedPersonalCode,
				coalesce(EUP.Lpu_uid, l3.Lpu_id, case when EUP.Lpu_id = :Lpu_id then null else EUP.Lpu_id end) as Lpu_uid,
				EUP.Org_uid,
				coalesce(EUP.LpuSectionProfile_id, LS.LpuSectionProfile_id) as LpuSectionProfile_id,
				EUP.MedSpecOms_id,
				EUP.Server_id,
				case when ED.PrehospDirect_id IS NOT NULL then ED.PrehospDirect_id else EUP.PrehospDirect_id end as PrehospDirect_id,
				case when ED.LpuSection_id IS NOT NULL then ED.LpuSection_id else EUP.LpuSection_did end as LpuSection_did,
				case when ED.Lpu_sid IS NOT NULL then ED.Lpu_sid else EUP.Lpu_did end as Lpu_did,
				coalesce(Org.Org_id, l.Org_id, EUP.Org_did, l2.Org_id) as Org_did,
				case when ED.MedPersonal_id IS NOT NULL then ED.MedPersonal_id else EUP.MedPersonal_did end as MedPersonal_did,
				convert(varchar(10), EUP.EvnUslugaPar_setDT, 104) as EvnUslugaPar_setDate,
				EUP.EvnUslugaPar_setTime,
				convert(varchar(10), EUP.EvnUslugaPar_disDT, 104) as EvnUslugaPar_disDate,
				EUP.EvnUslugaPar_disTime,
				EUP.LpuSection_uid,
				EUP.MedPersonal_id as MedPersonal_uid,
				EUP.MedPersonal_sid as MedPersonal_sid,
				EUP.Usluga_id,
				EUP.EvnUslugaPar_Kolvo,
				EUP.PayType_id,
				EC.XmlTemplate_id,
				EUP.UslugaComplex_id,
				EUP.FSIDI_id,
				EUP.Diag_id,
				EUP.DeseaseType_id,
				EUP.TumorStage_id,
				EUP.Mes_id,
				EC.UslugaCategory_id,
				ucat.UslugaCategory_Name,
				ISNULL(EUP.EvnUslugaPar_IsPaid, 1) as EvnUslugaPar_IsPaid,
				ISNULL(EUP.EvnUslugaPar_IndexRep, 0) as EvnUslugaPar_IndexRep,
				ISNULL(EUP.EvnUslugaPar_IndexRepInReg, 1) as EvnUslugaPar_IndexRepInReg,
				EUP.MedProductCard_id,
				EUP.UslugaComplexTariff_id,
				ED.Diag_id as DirectionDiag_id
			FROM
				v_EvnUslugaPar EUP with (NOLOCK)
				left join v_EvnDirection_all ED with (NOLOCK) on EUP.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnDirectionHistologic EDH (nolock) on EDH.EvnDirectionHistologic_id = ED.EvnDirection_id
				left join v_EvnDirectionHistologic EDH2 (nolock) on EDH2.EvnDirectionHistologic_id = EUP.EvnDirection_id
                left join Org (nolock) on Org.Org_id = ED.Org_sid
				left join v_Lpu l (nolock) on l.Lpu_id = ED.Lpu_sid
				left join v_Lpu l2 (nolock) on l2.Lpu_id = EUP.Lpu_did
				left join v_Lpu l3 (nolock) on l3.Org_id = EUP.Org_uid
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = EUP.LpuSection_uid
				left join v_UslugaComplex EC with (NOLOCK) on EUP.UslugaComplex_id = EC.UslugaComplex_id
				left join v_UslugaCategory ucat (nolock) on ucat.UslugaCategory_id = ec.UslugaCategory_id
				{$join_msf}
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
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
				EvnUslugaPar_id,
				EvnUslugaPar_pid,
				EvnUslugaPar_rid,
				Lpu_id,
				Server_id,
				PersonEvn_id,
				convert(varchar(20), EvnUslugaPar_setDT, 120) as EvnUslugaPar_setDT,
				convert(varchar(20), EvnUslugaPar_disDT, 120) as EvnUslugaPar_disDT,
				convert(varchar(20), EvnUslugaPar_didDT, 120) as EvnUslugaPar_didDT,
				Morbus_id,
				EvnUslugaPar_IsSigned,
				pmUser_signID,
				convert(varchar(20), EvnUslugaPar_signDT, 120) as EvnUslugaPar_signDT,
				PayType_id,
				Usluga_id,
				MedPersonal_id,
				MedStaffFact_id,
				UslugaPlace_id,
				Lpu_uid,
				LpuSection_uid,
				EvnUslugaPar_Kolvo,
				Org_uid,
				UslugaComplex_id,
				EvnUslugaPar_isCito,
				MedPersonal_sid,
				EvnUslugaPar_Result,
				EvnDirection_id,
				UslugaComplexTariff_id,
				EvnUslugaPar_CoeffTariff,
				MesOperType_id,
				EvnUslugaPar_IsModern,
				EvnUslugaPar_Price,
				EvnUslugaPar_Summa,
				EvnPrescr_id,
				EvnPrescrTimetable_id,
				EvnCourse_id,
				Lpu_oid,
				PrehospDirect_id,
				LpuSection_did,
				Lpu_did,
				Org_did,
				MedPersonal_did,
				TimetablePar_id,
				EvnLabSample_id,
				Study_uid,
				EvnUslugaPar_ResultValue,
				EvnUslugaPar_ResultLower,
				EvnUslugaPar_ResultUpper,
				EvnUslugaPar_ResultUnit,
				EvnUslugaPar_ResultApproved,
				convert(varchar(20), EvnUslugaPar_ResultAppDate, 120) as EvnUslugaPar_ResultAppDate,
				EvnUslugaPar_ResultCancelReason,
				EvnUslugaPar_Comment,
				EvnUslugaPar_ResultLowerCrit,
				EvnUslugaPar_ResultUpperCrit,
				EvnUslugaPar_ResultQualitativeNorms,
				EvnUslugaPar_ResultQualitativeText,
				RefValues_id,
				Unit_id,
				EvnUslugaPar_Regime
			from
				v_EvnUslugaPar (nolock)
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		", array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnUslugaPar_id;
			exec p_EvnUslugaPar_upd
				@EvnUslugaPar_id = @Res output,
				@EvnUslugaPar_pid = :EvnUslugaPar_pid,
				@EvnUslugaPar_rid = :EvnUslugaPar_rid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaPar_setDT = :EvnUslugaPar_setDT,
				@EvnUslugaPar_disDT = :EvnUslugaPar_disDT,
				@EvnUslugaPar_didDT = :EvnUslugaPar_didDT,
				@Morbus_id = :Morbus_id,
				@EvnUslugaPar_IsSigned = :EvnUslugaPar_IsSigned,
				@pmUser_signID = :pmUser_signID,
				@EvnUslugaPar_signDT = :EvnUslugaPar_signDT,
				@PayType_id = :PayType_id,
				@Usluga_id = :Usluga_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@UslugaPlace_id = :UslugaPlace_id,
				@Lpu_uid = :Lpu_uid,
				@LpuSection_uid = :LpuSection_uid,
				@EvnUslugaPar_Kolvo = :EvnUslugaPar_Kolvo,
				@Org_uid = :Org_uid,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnUslugaPar_isCito = :EvnUslugaPar_isCito,
				@MedPersonal_sid = :MedPersonal_sid,
				@EvnUslugaPar_Result = :EvnUslugaPar_Result,
				@EvnDirection_id = :EvnDirection_id,
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@EvnUslugaPar_CoeffTariff = :EvnUslugaPar_CoeffTariff,
				@MesOperType_id = :MesOperType_id,
				@EvnUslugaPar_IsModern = :EvnUslugaPar_IsModern,
				@EvnUslugaPar_Price = :EvnUslugaPar_Price,
				@EvnUslugaPar_Summa = :EvnUslugaPar_Summa,
				@EvnPrescr_id = :EvnPrescr_id,
				@EvnPrescrTimetable_id = :EvnPrescrTimetable_id,
				@EvnCourse_id = :EvnCourse_id,
				@Lpu_oid = :Lpu_oid,
				@PrehospDirect_id = :PrehospDirect_id,
				@LpuSection_did = :LpuSection_did,
				@Lpu_did = :Lpu_did,
				@Org_did = :Org_did,
				@MedPersonal_did = :MedPersonal_did,
				@TimetablePar_id = :TimetablePar_id,
				@EvnLabSample_id = :EvnLabSample_id,
				@Study_uid = :Study_uid,
				@EvnUslugaPar_ResultValue = :EvnUslugaPar_ResultValue,
				@EvnUslugaPar_ResultLower = :EvnUslugaPar_ResultLower,
				@EvnUslugaPar_ResultUpper = :EvnUslugaPar_ResultUpper,
				@EvnUslugaPar_ResultUnit = :EvnUslugaPar_ResultUnit,
				@EvnUslugaPar_ResultApproved = :EvnUslugaPar_ResultApproved,
				@EvnUslugaPar_ResultAppDate = :EvnUslugaPar_ResultAppDate, 
				@EvnUslugaPar_ResultCancelReason = :EvnUslugaPar_ResultCancelReason,
				@EvnUslugaPar_Comment = :EvnUslugaPar_Comment,
				@EvnUslugaPar_ResultLowerCrit = :EvnUslugaPar_ResultLowerCrit, 
				@EvnUslugaPar_ResultUpperCrit = :EvnUslugaPar_ResultUpperCrit,
				@EvnUslugaPar_ResultQualitativeNorms = :EvnUslugaPar_ResultQualitativeNorms,
				@EvnUslugaPar_ResultQualitativeText = :EvnUslugaPar_ResultQualitativeText,
				@RefValues_id = :RefValues_id,
				@Unit_id = :Unit_id,
				@EvnUslugaPar_Regime = :EvnUslugaPar_Regime,
				@EvnUslugaPar_IndexRep = :EvnUslugaPar_IndexRep,
				@EvnUslugaPar_IndexRepInReg = :EvnUslugaPar_IndexRepInReg,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output
			select @Res as EvnUslugaPar_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				top 1 XmlTemplate_HtmlTemplate,
				EvnXml_Data
			FROM
				XmlTemplate with(nolock)
				inner join EvnXml with(nolock) on Evn_id = ? and EvnXml.XmlTemplate_id = XmlTemplate.XmlTemplate_id
		";
		//$data['EvnUslugaPar_id'] = 17667;
		$result = $this->db->query($query, array($data['EvnUslugaPar_id']));

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
			SELECT TOP 1
				lp.Lpu_Nick,
				lp.UAddress_Address,
				'000000001' as EvnUslugaPar_Num,
				convert(varchar(10), EUP.EvnUslugaPar_setDT, 104) as EvnUslugaPar_setDate,
				ls.LpuSection_Name,
				mp.Person_Fin as MedPersonal_FIO,
				mp.MedPersonal_TabCode,
				PS.Person_SurName + ' ' + PS.Person_FirName + ' ' + isnull(PS.Person_SecName, '') as Person_FIO,
				(datediff(year,ps.Person_BirthDay,dbo.tzGetDate())
				+ case when month(ps.Person_BirthDay)>month(dbo.tzGetDate())
				or (month(ps.Person_BirthDay)=month(dbo.tzGetDate()) and day(ps.Person_BirthDay)>day(dbo.tzGetDate()))
				then -1 else 0 end) as Person_Age,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				sx.Sex_Name,
				pd.PrehospDirect_Name,
				u.Usluga_Name,
				0 as Cabinet_Num,
				'' as Diag_Code,
				'' as Diag_Name,
				UslugaComplex_id
			FROM
				v_EvnUslugaPar EUP with(nolock)
				inner join v_Lpu lp with(nolock) on EUP.Lpu_id = :Lpu_id and lp.Lpu_id = EUP.Lpu_id
				left join v_LpuSection ls with(nolock) on ls.LpuSection_id = EUP.LpuSection_uid
				left join v_MedPersonal mp with(nolock) on mp.MedPersonal_id = EUP.MedPersonal_id
				left join v_PersonState PS with(nolock) on PS.Person_id = EUP.Person_id
				left join Sex sx with(nolock) on sx.Sex_id = ps.Sex_id
				left join v_PrehospDirect pd with(nolock) on pd.PrehospDirect_id = EUP.PrehospDirect_id
				left join v_Usluga u with(nolock) on u.Usluga_id = EUP.Usluga_id
			WHERE (1 = 1)
				and EUP.EvnUslugaPar_id = :EvnUslugaPar_id
				and EUP.Lpu_id = :Lpu_id
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
			$filter .= " and EUP.EvnUslugaPar_insDT >= cast(:EvnUslugaPar_insDT as datetime)";
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
				case when EUP.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EUP.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
				EUP.EvnUslugaPar_id,
				EUP.Person_id,
				EUP.PersonEvn_id,
				EUP.Server_id,
				RTRIM(PS.Person_SurName) as Person_Surname,
				RTRIM(PS.Person_FirName) as Person_Firname,
				RTRIM(PS.Person_SecName) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate,
				LS.LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				UC.UslugaComplex_Code,
				UC.UslugaComplex_Name,
				PT.PayType_Name
			FROM v_EvnUslugaPar EUP with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EUP.Person_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = EUP.LpuSection_uid
				cross apply (
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = EUP.MedPersonal_id
						and Lpu_id = EUP.Lpu_id
				) MP
				inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUP.UslugaComplex_id
				inner join v_PayType PT with (nolock) on PT.PayType_id = EUP.PayType_id
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
				case when EQ.EvnQueue_id is not null AND EQ.EvnQueue_recDT is null AND EQ.pmUser_recID is null AND EUP.EvnUslugaPar_setDT is null then 2 else 1 end as allowApplyFromQueue, -- да при оказании услуги из очереди 
				EUP.EvnUslugaPar_id,
				EUP.Person_id,
				EQ.EvnQueue_id,
				ED.EvnDirection_id,
				ED.EvnDirection_Num,
				EUP.EvnDirection_id as UslugaEvnDirection_id
			FROM v_EvnUslugaPar EUP with (nolock) 
				left join v_EvnQueue EQ with (nolock) on EUP.EvnUslugaPar_id = EQ.EvnUslugaPar_id
				left join v_EvnDirection_all ED with (nolock) on EQ.EvnDirection_id = ED.EvnDirection_id AND ED.DirFailType_id is null
			WHERE
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
		";
		/*
				--case when EUP.TimetablePar_id is not null then 2 else 1 end as fromTimetable,
				--case when EQ.EvnQueue_id is not null then 2 else 1 end as fromQueue,
				--case when EUP.EvnUslugaPar_setDT is not null then 2 else 1 end as isMade,
				--case when EUP.TimetablePar_id is null OR (EQ.EvnQueue_id is not null AND EQ.TimetablePar_id is null) then 2 else 1 end as allowApply,-- да при сохранении без записи и из очереди
				--,A.TimetablePar_id
				-- поиск свободной бирки на указанный день

				outer apply (
					select top 1
						TimetablePar_id
					from
						v_TimetablePar A with (nolock)
					where
						(EUP.TimetablePar_id is null OR (EQ.EvnQueue_id is not null AND EQ.TimetablePar_id is null)) AND A.TimetablePar_Day = :TimetablePar_Day AND A.LpuSection_id = :LpuSection_uid AND A.Person_id is null and A.TimetablePar_IsReserv is null and A.TimetablePar_IsPay is null

					order by TimetablePar_begTime
				) A
		*/
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
			$data['EvnUslugaPar_setDate'] .= ' ' . $data['EvnUslugaPar_setTime'] . ':00.000';
		}

		if ( !empty($data['EvnUslugaPar_disDate']) && !empty($data['EvnUslugaPar_disTime']) ) {
			$data['EvnUslugaPar_disDate'] .= ' ' . $data['EvnUslugaPar_disTime'] . ':00:000';
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
			declare
				@Res bigint,
				@EvnPrescr_id bigint,
				@EvnUslugaPar_pid bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnUslugaPar_id;
			set @EvnPrescr_id = (select top 1 EvnPrescr_id from v_EvnPrescrDirection (nolock) where EvnDirection_id = :EvnDirection_id);
			set @EvnUslugaPar_pid = (
				select top 1
					e_child.Evn_id
				from
					v_EvnPrescr ep (nolock)
					inner join v_Evn e (nolock) on e.Evn_id = EvnPrescr_pid -- посещние/движение
					inner join v_Evn e_child (nolock) on e_child.Evn_pid = e.Evn_pid -- посещения/движения той же КВС/ТАП
				where
					e_child.EvnClass_SysNick IN ('EvnSection', 'EvnVizitPL', 'EvnVizitPLStom')
					and EvnPrescr_id = @EvnPrescr_id and e_child.Evn_setDT <= :EvnUslugaPar_setDT and (e_child.Evn_disDT >= :EvnUslugaPar_setDT OR e_child.Evn_disDT IS NULL) -- актуальное
			);
			exec " . $procedure . "
				@EvnUslugaPar_id = @Res output,
				@EvnUslugaPar_pid = @EvnUslugaPar_pid,
				@EvnDirection_id = :EvnDirection_id,
				@EvnDirection_setDT = :EvnDirection_setDT,
				@EvnDirection_Num = :EvnDirection_Num,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaPar_setDT = :EvnUslugaPar_setDT,
				@EvnUslugaPar_disDT = :EvnUslugaPar_disDT,
				@PayType_id = :PayType_id,
				@EvnUslugaPar_isCito = :EvnUslugaPar_isCito,
				@TimetablePar_id = :TimetablePar_id,
				@Usluga_id = :Usluga_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@UslugaPlace_id = :UslugaPlace_id,
				@LpuSection_uid = :LpuSection_uid,
				@Lpu_uid = :Lpu_uid,
				@MedSpecOms_id = :MedSpecOms_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@EvnUslugaPar_MedPersonalCode = :EvnUslugaPar_MedPersonalCode,
				@EvnUslugaPar_Kolvo = :EvnUslugaPar_Kolvo,
				@PrehospDirect_id = :PrehospDirect_id,
				@LpuSection_did = :LpuSection_did,
				@Lpu_did = :Lpu_did,
				@Org_did = :Org_did,
				@Org_uid = :Org_uid,
				@MedPersonal_did = :MedPersonal_did,
				@MedPersonal_sid = :MedPersonal_sid,
				@UslugaComplex_id = :UslugaComplex_id,
				@FSIDI_id = :FSIDI_id,
				@Diag_id = :Diag_id,
				@DeseaseType_id = :DeseaseType_id,
				@TumorStage_id = :TumorStage_id,
				@Mes_id = :Mes_id,
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = @EvnPrescr_id,
				@EvnUslugaPar_IndexRep = :EvnUslugaPar_IndexRep,
				@EvnUslugaPar_IndexRepInReg = :EvnUslugaPar_IndexRepInReg,
				@MedProductCard_id = :MedProductCard_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnUslugaPar_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
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
		);
		/*
		echo getDebugSql($query, $queryParams);
		exit;
		*/

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
			$this->EvnPrescr_model->saveEvnPrescrIsExec(array(
				'pmUser_id' => $data['pmUser_id'],
				'EvnDirection_id' => $data['EvnDirection_id'],
				'EvnPrescr_IsExec' => 2
			));
			if (!isset($data['is_consul']) || !$data['is_consul']) {
				$this->load->model('Evn_model', 'Evn_model');
				$this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $data['EvnDirection_id'],
					'EvnStatus_SysNick' => 'Serviced',
					'EvnClass_SysNick' => 'EvnDirection',
					'pmUser_id' => $data['pmUser_id']
				));
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
			select top 1
				PA.Person_Fio,
				PA.Person_id,
				UC.UslugaComplex_Name
			from
				v_EvnUslugaPar EUP with (nolock)
				left join v_Person_all PA with(nolock) on PA.Person_id = EUP.Person_id
					and PA.PersonEvn_id = EUP.PersonEvn_id
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EUP.UslugaComplex_id
			where
				EUP.EvnUslugaPar_id = :EvnUslugaPar_id
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
			select top 1
				d.pmUser_insID,
				tmms.TimetableMedService_id,
				eup.EvnUslugaPar_id,
				d.EvnDirection_Num,
				ms.MedService_Name,
				(select top 1 EvnStatusCause_Name from v_EvnStatusCause (nolock) where EvnStatusCause_id = :EvnStatusCause_id) as EvnStatusCause_Name,
				ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, '') as Person_Fio,
				l.Lpu_Nick,
				ls.LpuSection_Name,
				eq.EvnQueue_id
			from
				v_EvnDirection_all d (nolock)
				inner join v_EvnUslugaPar eup (nolock) on eup.EvnDirection_id = d.EvnDirection_id
				left join v_PersonState ps (nolock) on ps.Person_id = d.Person_id
				left join v_MedService ms (nolock) on ms.MedService_id = d.MedService_id
				left join v_Lpu l (nolock) on l.Lpu_id = ms.Lpu_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = ms.LpuSection_id
				left join v_TimeTableMedService_lite tmms (nolock) on tmms.EvnDirection_id = d.EvnDirection_id
				left join v_EvnQueue eq (nolock) on eq.EvnDirection_id = d.EvnDirection_id
			where
				d.EvnDirection_id = :EvnDirection_id
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
		$query = "
			declare
				@datetime datetime = dbo.tzGetDate(),
				@ErrCode int,
				@Err_Msg varchar(400),
				@EvnComment_id bigint;

			set nocount on;

			begin try
				begin tran

				update EvnDirection
				set
					EvnDirection_failDT = dbo.tzGetDate(),
					pmUser_failID = :pmUser_id,
					TimetableGraf_id = null,
					TimetableStac_id = null,
					TimetableMedService_id = null,
					TimetablePar_id = null
				where EvnDirection_id = :EvnDirection_id

				set @EvnComment_id = (select top 1 EvnComment_id from v_EvnComment with (nolock) where Evn_id = :EvnDirection_id);

				if ( @EvnComment_id is not null )
					begin
						update dbo.EvnComment
						set EvnComment_Comment = :EvnComment_Comment,
							pmUser_updID = :pmUser_id,
							EvnComment_updDT = @datetime
						where EvnComment_id = @EvnComment_id
					end
				else
					begin
						insert into dbo.EvnComment with (ROWLOCK) (Evn_id, EvnComment_Comment, pmUser_insID, pmUser_updID, EvnComment_insDT, EvnComment_updDT)
						values (:EvnDirection_id, :EvnComment_Comment, :pmUser_id, :pmUser_id, @datetime, @datetime)
					end

				if ( :TimetableMedService_id is not null )
					begin
						exec p_TimetableMedService_cancel
							@TimetableMedService_id = :TimetableMedService_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @Err_Msg output;
					end

				if ( :EvnQueue_id is not null )
					begin
						exec p_EvnQueue_del
							@EvnQueue_id = :EvnQueue_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @Err_Msg output;
					end
					
				exec p_Evn_setStatus
					@Evn_id = :EvnDirection_id,
					@EvnStatus_SysNick = 'Declined',
					@EvnClass_id = 27,
					@EvnStatusCause_id = :EvnStatusCause_id,
					@EvnStatusHistory_Cause = :EvnComment_Comment,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @Err_Msg output;
				select @ErrCode as Error_Code, @Err_Msg as Error_Msg;

				commit tran
			end try

			begin catch
				set @Err_Msg = error_message();
				rollback tran
			end catch

			set nocount off;

			select @Err_Msg as Error_Msg;
		";

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

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$resp = $result->result('array');
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

		return false;
	}

	/**
	 * Изменение привязки услуги
	 */
	function editEvnUslugaPar($data) {
		$query = "
			select
				EvnUslugaPar_id,
				EvnUslugaPar_pid,
				EvnUslugaPar_rid,
				Lpu_id,
				Server_id,
				PersonEvn_id,
				convert(varchar(20), EvnUslugaPar_setDT, 120) as EvnUslugaPar_setDT,
				convert(varchar(20), EvnUslugaPar_disDT, 120) as EvnUslugaPar_disDT,
				convert(varchar(20), EvnUslugaPar_didDT, 120) as EvnUslugaPar_didDT,
				Morbus_id,
				EvnUslugaPar_IsSigned,
				pmUser_signID,
				convert(varchar(20), EvnUslugaPar_signDT, 120) as EvnUslugaPar_signDT,
				PayType_id,
				Usluga_id,
				MedPersonal_id,
				MedStaffFact_id,
				UslugaPlace_id,
				Lpu_uid,
				LpuSection_uid,
				EvnUslugaPar_Kolvo,
				Org_uid,
				UslugaComplex_id,
				EvnUslugaPar_isCito,
				MedPersonal_sid,
				EvnUslugaPar_Result,
				EvnDirection_id,
				UslugaComplexTariff_id,
				EvnUslugaPar_CoeffTariff,
				MesOperType_id,
				EvnUslugaPar_IsModern,
				EvnUslugaPar_Price,
				EvnUslugaPar_Summa,
				EvnPrescr_id,
				EvnPrescrTimetable_id,
				EvnCourse_id,
				Lpu_oid,
				PrehospDirect_id,
				LpuSection_did,
				Lpu_did,
				Org_did,
				MedPersonal_did,
				TimetablePar_id,
				EvnLabSample_id,
				Study_uid,
				EvnUslugaPar_ResultValue,
				EvnUslugaPar_ResultLower,
				EvnUslugaPar_ResultUpper,
				EvnUslugaPar_ResultUnit,
				EvnUslugaPar_ResultApproved,
				convert(varchar(20), EvnUslugaPar_ResultAppDate, 120) as EvnUslugaPar_ResultAppDate,
				EvnUslugaPar_ResultCancelReason,
				EvnUslugaPar_Comment,
				EvnUslugaPar_ResultLowerCrit,
				EvnUslugaPar_ResultUpperCrit,
				EvnUslugaPar_ResultQualitativeNorms,
				EvnUslugaPar_ResultQualitativeText,
				RefValues_id,
				Unit_id,
				EvnUslugaPar_Regime,
				EvnUslugaPar_IsManual
			from
				v_EvnUslugaPar (nolock)
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
				coalesce(epl.EvnPL_IsInReg, eps.EvnPS_IsInReg, ev.EvnVizit_IsInReg, es.EvnSection_IsInReg) as Evn_IsInReg
			from
				Evn e (nolock)
				left join EvnPLBase eplb (nolock) on eplb.Evn_id = e.Evn_id 
				left join EvnPL epl (nolock) on epl.EvnPLBase_id = eplb.EvnPLBase_id 
				left join EvnPS eps (nolock) on eps.Evn_id = e.Evn_id 
				left join EvnVizit ev (nolock) on ev.Evn_id = e.Evn_id 
				left join EvnSection es (nolock) on es.Evn_id = e.Evn_id 
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
				coalesce(epl.EvnPL_IsInReg, eps.EvnPS_IsInReg, ev.EvnVizit_IsInReg, es.EvnSection_IsInReg) as Evn_IsInReg
			from
				Evn e (nolock)
				left join EvnPLBase eplb (nolock) on eplb.Evn_id = e.Evn_id 
				left join EvnPL epl (nolock) on epl.EvnPLBase_id = eplb.EvnPLBase_id 
				left join EvnPS eps (nolock) on eps.Evn_id = e.Evn_id 
				left join EvnVizit ev (nolock) on ev.Evn_id = e.Evn_id 
				left join EvnSection es (nolock) on es.Evn_id = e.Evn_id 
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
				UslugaComplex_Code,
				UslugaComplex_Name
			from
				v_UslugaComplex uc (nolock)
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
				select top 1
					MedStaffFact_id
				from
					v_MedStaffFact (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					and WorkData_begDate <= :EvnUslugaPar_setDT
					and (WorkData_endDate >= :EvnUslugaPar_setDT OR WorkData_endDate IS NULL)
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

		// Дополнительные проверки
		if (!empty($prevPid)) {
			// получаем инфрмацию по предудщему случаю
			$resp_evn_prev = $this->queryResult("
				select
					Evn_id,
					convert(varchar(10), Evn_setDT, 104) as Evn_setDT,
					EvnClass_SysNick
				from
					v_Evn (nolock)
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
								es_count.cnt,
								convert(varchar(10), ISNULL(es.EvnSection_disDT, es.EvnSection_setDT), 120) as EvnSection_disDate,
								datediff(hour, es.EvnSection_setDT, es.EvnSection_disDT) as diff_hours,
								mo.Mes_Code,
								d.Diag_Code
							from
								v_EvnSection es (nolock)
								left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
								left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
								left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
								outer apply (
									select
										count(es2.EvnSection_id) as cnt
									from
										v_EvnSection es2 (nolock)
									where
										es2.EvnSection_pid = es.EvnSection_pid
										and ISNULL(es2.EvnSection_IsPriem, 1) = 1
								) es_count
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
									select top 1
										EU.EvnUsluga_id
									from
										v_EvnUsluga EU (nolock)
										inner join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
										inner join v_UslugaCategory UCat (nolock) on UCat.UslugaCategory_id = UC.UslugaCategory_id
									where
										EU.EvnUsluga_pid = :EvnSection_id
										and UCat.UslugaCategory_SysNick = 'gost2011'
										and EU.EvnUsluga_id <> :EvnUslugaPar_id
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
								pt.PayType_SysNick,
								es.HTMedicalCareClass_id,
								lut.LpuUnitType_SysNick,
								es.Mes_sid
							from
								v_EvnSection es (nolock)
								left join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
								left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
								left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
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
								select top 1
									eu.EvnUsluga_id
								from
									v_EvnUsluga eu (nolock)
								where
									eu.EvnUsluga_pid = :EvnSection_id
									and exists(
										select top 1
											ucp.UslugaComplexPartition_id
										from
											r66.v_UslugaComplexPartitionLink ucpl (nolock)
											inner join r66.v_UslugaComplexPartition ucp with(nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
										where
											ucpl.UslugaComplex_id = eu.UslugaComplex_id
											and '.$UslugaComplexPartition_Code_filter.'
									)
									and EU.EvnUsluga_id <> :EvnUslugaPar_id
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
								select top 1
									mu.MesUsluga_id,
									INES.EvnUsluga_id
								from
									v_MesUsluga mu (nolock)
									inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = mu.UslugaComplex_id
									outer apply (
										select top 1
											euc.EvnUsluga_id
										from
											v_EvnUsluga euc (nolock)
										where
											UslugaComplex_id in (
												select
													mouc2.UslugaComplex_id
												from
													v_MesOldUslugaComplex mouc2 (nolock)
												where
													mouc2.Mes_id = mu.Mes_id
											)
											and euc.EvnUsluga_pid = :EvnSection_id
											and euc.EvnUsluga_id <> :EvnUslugaPar_id
									) INES
								where
									mu.Mes_id = :Mes_sid
									and ucpl.UslugaComplexPartitionLink_IsNeedOper = 2
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
					Evn_id,
					convert(varchar(10), Evn_setDT, 104) as Evn_setDT,
					EvnClass_SysNick
				from
					v_Evn (nolock)
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
								evpl.UslugaComplex_id,
								st.ServiceType_SysNick
							from
								v_EvnVizitPL evpl (nolock)
								left join v_ServiceType st (nolock) on st.ServiceType_id = evpl.ServiceType_id
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnUslugaPar_id;
			exec p_EvnUslugaPar_upd
				@EvnUslugaPar_id = @Res output,
				@EvnUslugaPar_pid = :EvnUslugaPar_pid,
				@EvnUslugaPar_rid = :EvnUslugaPar_rid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUslugaPar_setDT = :EvnUslugaPar_setDT,
				@EvnUslugaPar_disDT = :EvnUslugaPar_disDT,
				@EvnUslugaPar_didDT = :EvnUslugaPar_didDT,
				@Morbus_id = :Morbus_id,
				@EvnUslugaPar_IsSigned = :EvnUslugaPar_IsSigned,
				@pmUser_signID = :pmUser_signID,
				@EvnUslugaPar_signDT = :EvnUslugaPar_signDT,
				@PayType_id = :PayType_id,
				@Usluga_id = :Usluga_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@UslugaPlace_id = :UslugaPlace_id,
				@Lpu_uid = :Lpu_uid,
				@LpuSection_uid = :LpuSection_uid,
				@EvnUslugaPar_Kolvo = :EvnUslugaPar_Kolvo,
				@Org_uid = :Org_uid,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnUslugaPar_isCito = :EvnUslugaPar_isCito,
				@MedPersonal_sid = :MedPersonal_sid,
				@EvnUslugaPar_Result = :EvnUslugaPar_Result,
				@EvnDirection_id = :EvnDirection_id,
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@EvnUslugaPar_CoeffTariff = :EvnUslugaPar_CoeffTariff,
				@MesOperType_id = :MesOperType_id,
				@EvnUslugaPar_IsModern = :EvnUslugaPar_IsModern,
				@EvnUslugaPar_Price = :EvnUslugaPar_Price,
				@EvnUslugaPar_Summa = :EvnUslugaPar_Summa,
				@EvnPrescr_id = :EvnPrescr_id,
				@EvnPrescrTimetable_id = :EvnPrescrTimetable_id,
				@EvnCourse_id = :EvnCourse_id,
				@Lpu_oid = :Lpu_oid,
				@PrehospDirect_id = :PrehospDirect_id,
				@LpuSection_did = :LpuSection_did,
				@Lpu_did = :Lpu_did,
				@Org_did = :Org_did,
				@MedPersonal_did = :MedPersonal_did,
				@TimetablePar_id = :TimetablePar_id,
				@EvnLabSample_id = :EvnLabSample_id,
				@Study_uid = :Study_uid,
				@EvnUslugaPar_ResultValue = :EvnUslugaPar_ResultValue,
				@EvnUslugaPar_ResultLower = :EvnUslugaPar_ResultLower,
				@EvnUslugaPar_ResultUpper = :EvnUslugaPar_ResultUpper,
				@EvnUslugaPar_ResultUnit = :EvnUslugaPar_ResultUnit,
				@EvnUslugaPar_ResultApproved = :EvnUslugaPar_ResultApproved,
				@EvnUslugaPar_ResultAppDate = :EvnUslugaPar_ResultAppDate, 
				@EvnUslugaPar_ResultCancelReason = :EvnUslugaPar_ResultCancelReason,
				@EvnUslugaPar_Comment = :EvnUslugaPar_Comment,
				@EvnUslugaPar_ResultLowerCrit = :EvnUslugaPar_ResultLowerCrit, 
				@EvnUslugaPar_ResultUpperCrit = :EvnUslugaPar_ResultUpperCrit,
				@EvnUslugaPar_ResultQualitativeNorms = :EvnUslugaPar_ResultQualitativeNorms,
				@EvnUslugaPar_ResultQualitativeText = :EvnUslugaPar_ResultQualitativeText,
				@RefValues_id = :RefValues_id,
				@Unit_id = :Unit_id,
				@EvnUslugaPar_Regime = :EvnUslugaPar_Regime,
				@EvnUslugaPar_IsManual = :EvnUslugaPar_IsManual,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output
			select @Res as EvnUslugaPar_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		if (!empty($data['EvnUslugaPar_IsManual'])) {
			$resp[0]['EvnUslugaPar_IsManual'] = $data['EvnUslugaPar_IsManual'];
		}

		$this->load->helper('PersonNotice');
		//Инициализация хелпера рассылки сообщений о смене статуса
		if (empty($data['Person_id'])) {
			$data['Person_id'] = $this->getFirstResultFromQuery("
				select top 1
					Person_id
				from
					v_PersonEvn (nolock)
				where
					PersonEvn_id = :PersonEvn_id
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
				coalesce(epl.EvnPL_IsInReg, eps.EvnPS_IsInReg, ev.EvnVizit_IsInReg, es.EvnSection_IsInReg) as Evn_IsInReg
			from
				Evn e (nolock)
				left join EvnPLBase eplb (nolock) on eplb.Evn_id = e.Evn_id 
				left join EvnPL epl (nolock) on epl.EvnPLBase_id = eplb.EvnPLBase_id 
				left join EvnPS eps (nolock) on eps.Evn_id = e.Evn_id 
				left join EvnVizit ev (nolock) on ev.Evn_id = e.Evn_id 
				left join EvnSection es (nolock) on es.Evn_id = e.Evn_id 
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
				coalesce(epl.EvnPL_IsInReg, eps.EvnPS_IsInReg, ev.EvnVizit_IsInReg, es.EvnSection_IsInReg) as Evn_IsInReg
			from
				Evn e (nolock)
				left join EvnPLBase eplb (nolock) on eplb.Evn_id = e.Evn_id 
				left join EvnPL epl (nolock) on epl.EvnPLBase_id = eplb.EvnPLBase_id 
				left join EvnPS eps (nolock) on eps.Evn_id = e.Evn_id 
				left join EvnVizit ev (nolock) on ev.Evn_id = e.Evn_id 
				left join EvnSection es (nolock) on es.Evn_id = e.Evn_id 
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
				UslugaComplex_Code,
				UslugaComplex_Name
			from
				v_UslugaComplex uc (nolock)
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
				select top 1
					MedStaffFact_id
				from
					v_MedStaffFact (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					and WorkData_begDate <= :EvnUslugaPar_setDT
					and (WorkData_endDate >= :EvnUslugaPar_setDT OR WorkData_endDate IS NULL)
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
					Evn_id,
					convert(varchar(10), Evn_setDT, 104) as Evn_setDT,
					EvnClass_SysNick
				from
					v_Evn (nolock)
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
								es_count.cnt,
								convert(varchar(10), ISNULL(es.EvnSection_disDT, es.EvnSection_setDT), 120) as EvnSection_disDate,
								datediff(hour, es.EvnSection_setDT, es.EvnSection_disDT) as diff_hours,
								mo.Mes_Code,
								d.Diag_Code
							from
								v_EvnSection es (nolock)
								left join v_MesTariff mt (nolock) on mt.MesTariff_id = es.MesTariff_id
								left join v_MesOld mo (nolock) on mo.Mes_id = mt.Mes_id
								left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
								outer apply (
									select
										count(es2.EvnSection_id) as cnt
									from
										v_EvnSection es2 (nolock)
									where
										es2.EvnSection_pid = es.EvnSection_pid
										and ISNULL(es2.EvnSection_IsPriem, 1) = 1
								) es_count
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
									select top 1
										EU.EvnUsluga_id
									from
										v_EvnUsluga EU (nolock)
										inner join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
										inner join v_UslugaCategory UCat (nolock) on UCat.UslugaCategory_id = UC.UslugaCategory_id
									where
										EU.EvnUsluga_pid = :EvnSection_id
										and UCat.UslugaCategory_SysNick = 'gost2011'
										and EU.EvnUsluga_id <> :EvnUslugaPar_id
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
								pt.PayType_SysNick,
								es.HTMedicalCareClass_id,
								lut.LpuUnitType_SysNick,
								es.Mes_sid
							from
								v_EvnSection es (nolock)
								left join v_PayType pt (nolock) on pt.PayType_id = es.PayType_id
								left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
								left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
								left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
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
								select top 1
									eu.EvnUsluga_id
								from
									v_EvnUsluga eu (nolock)
								where
									eu.EvnUsluga_pid = :EvnSection_id
									and exists(
										select top 1
											ucp.UslugaComplexPartition_id
										from
											r66.v_UslugaComplexPartitionLink ucpl (nolock)
											inner join r66.v_UslugaComplexPartition ucp with(nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
										where
											ucpl.UslugaComplex_id = eu.UslugaComplex_id
											and '.$UslugaComplexPartition_Code_filter.'
									)
									and EU.EvnUsluga_id <> :EvnUslugaPar_id
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
								select top 1
									mu.MesUsluga_id,
									INES.EvnUsluga_id
								from
									v_MesUsluga mu (nolock)
									inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = mu.UslugaComplex_id
									outer apply (
										select top 1
											euc.EvnUsluga_id
										from
											v_EvnUsluga euc (nolock)
										where
											UslugaComplex_id in (
												select
													mouc2.UslugaComplex_id
												from
													v_MesOldUslugaComplex mouc2 (nolock)
												where
													mouc2.Mes_id = mu.Mes_id
											)
											and euc.EvnUsluga_pid = :EvnSection_id
											and euc.EvnUsluga_id <> :EvnUslugaPar_id
									) INES
								where
									mu.Mes_id = :Mes_sid
									and ucpl.UslugaComplexPartitionLink_IsNeedOper = 2
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
					Evn_id,
					convert(varchar(10), Evn_setDT, 104) as Evn_setDT,
					EvnClass_SysNick
				from
					v_Evn (nolock)
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
								evpl.UslugaComplex_id,
								st.ServiceType_SysNick
							from
								v_EvnVizitPL evpl (nolock)
								left join v_ServiceType st (nolock) on st.ServiceType_id = evpl.ServiceType_id
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
			select top 1
				PayType_id
			from
				v_PayType (nolock)
			where
				PayType_SysNick = :PayType_SysNick
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
			select top 1
				PayType_id
			from
				v_PayType (nolock)
			where
				PayType_SysNick = :PayType_SysNick
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
				EUP.EvnUslugaPar_id,
				EUP.Person_id,
				EUP.Server_id,
				convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate,
				UC.UslugaComplex_Code,
				UC.UslugaComplex_Name
			FROM v_EvnUslugaPar EUP with (nolock)
				inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EUP.UslugaComplex_id
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
				EvnXml_id
			FROM v_EvnXml with (nolock)
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
				row_number() over(order by bm.BactMicro_Name) as RowNumber,
      			bm.BactMicro_id,
				bm.BactMicro_Name,
				ut.UslugaTest_ResultValue
			from dbo.v_EvnUslugaPar (nolock) eup
				inner join dbo.v_EvnLabSample (nolock) els on els.EvnLabSample_id = eup.EvnLabSample_id
				inner join dbo.v_UslugaTest (nolock) ut on ut.EvnLabSample_id = els.EvnLabSample_id
				inner join dbo.v_BactMicroProbe (nolock) bmp on bmp.UslugaTest_id = ut.UslugaTest_id
				inner join dbo.v_BactMicro (nolock) bm on bm.BactMicro_id = bmp.BactMicro_id
			where ut.UslugaTest_ResultApproved = 2 and EvnUslugaPar_id = :EvnUslugaPar_id and bmp.BactMicroProbe_IsNotShown != 2
			order by bm.BactMicro_Name";
		
		return $this->queryResult($query, $params);
	}
	
	/*
	 * Получение списка микроорганизмов
	 */
	function getBactMicroIsNotFind($params) {
		$query = "select count(EvnUslugaPar_id) as count
			from dbo.v_EvnUslugaPar (nolock) eup
				inner join dbo.v_EvnLabSample (nolock) els on els.EvnLabSample_id = eup.EvnLabSample_id
				inner join dbo.v_UslugaTest (nolock) ut on ut.EvnLabSample_id = els.EvnLabSample_id
				inner join dbo.v_BactMicroProbe (nolock) bmp on bmp.UslugaTest_id = ut.UslugaTest_id
			where ut.UslugaTest_ResultApproved = 2 and EvnUslugaPar_id = :EvnUslugaPar_id and bmp.BactMicroProbe_IsNotShown = 2";
		
		return $this->db->query($query, $params)->first_row()->count;
	}
	
	/*
	 * Получение списка антибиотиков
	 */
	function getBactAntibioticList($params) {
		$query = "select
				bmp.BactMicro_id,
				concat(isnull(ba.BactAntibiotic_Name, ''), ' ', isnull(bg.BactGuideline_Name, ''), ' ', isnull(method.BactMethod_Name, '')) as BactAntibiotic_Name,
				isnull(sens.BactMicroABPSens_ShortName, '-') as BactMicroABPSens_ShortName
			from dbo.v_EvnUslugaPar (nolock) eup
			inner join dbo.v_EvnLabSample (nolock) els on els.EvnLabSample_id = eup.EvnLabSample_id
			inner join dbo.v_UslugaTest (nolock) ut on ut.EvnLabSample_id = els.EvnLabSample_id
			inner join dbo.v_BactMicroProbe (nolock) bmp on bmp.UslugaTest_id = ut.UslugaTest_id
			inner join dbo.v_BactMicroProbeAntibiotic (nolock) bmpa on bmpa.BactMicroProbe_id = bmp.BactMicroProbe_id
			inner join dbo.v_UslugaTest (nolock) uta on uta.UslugaTest_id = bmpa.UslugaTest_id
			inner join dbo.v_BactMethod (nolock) method on method.BactMethod_id = bmpa.BactMethod_id
			left join dbo.v_BactAntibiotic (nolock) ba on ba.BactAntibiotic_id = bmpa.BactAntibiotic_id
			inner join dbo.v_BactGuideline (nolock) bg on bg.BactGuideline_id = ba.BactGuideline_id
			left join dbo.v_BactMicroABPSens (nolock) sens on sens.BactMicroABPSens_id = bmpa.BactMicroABPSens_id
			
			where EvnUslugaPar_id = :EvnUslugaPar_id and uta.UslugaTest_ResultApproved = 2
			order by concat(isnull(ba.BactAntibiotic_Name, ''), ' ', isnull(bg.BactGuideline_Name, ''), ' ', isnull(method.BactMethod_Name, ''))";
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
				EUP.EvnUslugaPar_id as EvnUsluga_id,
				EUP.EvnUslugaPar_id,
				EUP.EvnUslugaPar_id as Evn_id,
				convert(varchar(19), EUP.EvnUslugaPar_setDT, 120) as Evn_setDT,
				convert(varchar(19), EUP.EvnUslugaPar_disDT, 120) as Evn_disDT,
				EUP.Person_id,
				EUP.LpuSection_uid as LpuSection_id,
				EUP.Lpu_uid as Lpu_id,
				EUP.Org_uid as Org_id,
				EUP.LpuSectionProfile_id,
				EUP.MedSpecOms_id,
				EUP.MedStaffFact_id,
				EUP.UslugaComplex_id,
				EUP.PayType_id,
				EUP.EvnUslugaPar_Kolvo as EvnUsluga_Kolvo,
				EUP.UslugaPlace_id,
				EUP.EvnUslugaPar_Comment
			from
				v_EvnUslugaPar EUP (nolock)
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
			$info = $this->getFirstRowFromQuery("select MedStaffFact_id, MedPersonal_id from v_MedStaffFact (nolock) where MedStaffFact_id = :MedStaffFact_id", $data);
			if (!empty($info['MedStaffFact_id'])) {
				$data['MedPersonal_id'] = $info['MedPersonal_id'];
			} else {
				return array(array('Error_Msg' => 'Ошибка получения данных по рабочему месту врача'));
			}
		}
		$info = $this->getFirstRowFromQuery("select PersonEvn_id, Person_id, Server_id from v_PersonState (nolock) where Person_id = :Person_id", $data);
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
	 *  //yl:176439 добавлены не выполненные назначенные лабораторные и функциональные исследования
	 *  //yl: поменял выполнена: eup.EvnUslugaPar_setDT на назаначена:ed.EvnDirection_setDate
	 *  //yl: отключил EvnXml_id т.к. только дублирует и не используется
	 */
	function loadEvnUslugaParPanel($data)
	{
		$filter = " and eup.Person_id = :Person_id ";
		$filter2 = " and EP.Person_id = :Person_id ";

		if (!empty($data['EvnUslugaPar_rid']) && empty($data['Person_id'])) {
			$filter = " and eup.EvnUslugaPar_rid = :EvnUslugaPar_rid ";
		}

		$sql = "
		select
			-- select
			*
			-- end select
		from
			-- from
			(

			select
				eup.EvnUslugaPar_id,
				eup.EvnUslugaPar_rid,
				convert(varchar(10), eup.EvnUslugaPar_setDT, 104) as EvnUslugaPar_setDate,
				uc.UslugaComplex_Name,
				l.Lpu_Name,
				ms.MedService_Name,
				EX.EvnXml_id --используется в мобильном арм
			from
				v_EvnUslugaPar eup (nolock)
				left join v_Evn EvnUP with (nolock) on EvnUP.Evn_id = eup.EvnUslugaPar_pid
				left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eup.EvnDirection_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = eup.UslugaComplex_id
				left join v_Lpu l (nolock) on l.Lpu_id = eup.Lpu_id
				left join v_MedService ms (nolock) on ms.MedService_id = ed.MedService_id
				left join v_EvnXml ex (nolock) on ex.Evn_id = eup.EvnUslugaPar_id --используется в мобильном арм
			where
				eup.EvnUslugaPar_setDT is not null
				and ISNULL(EvnUP.EvnClass_SysNick, '') != 'EvnUslugaPar'
				{$filter}
				
			UNION 
			
			SELECT
				DISTINCT
				NULL AS EvnUslugaPar_id,
				NULL AS EvnUslugaPar_rid,
				coalesce(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),convert(varchar(10), TTR.TimeTableResource_begTime, 104)) as EvnUslugaPar_setDate,
				coalesce(UC1.UslugaComplex_Name,UC2.UslugaComplex_Name) as UslugaComplex_Name,
				coalesce(LS1.Lpu_Name,LS2.Lpu_Name,LS4.Lpu_Name,LS6.Lpu_Name) as Lpu_Name,
				coalesce(MS4.MedService_Name,MS2.MedService_Name,MS1.MedService_Name,MS6.MedService_Name) as MedService_Name,
				EX.EvnXml_id --используется в мобильном арм
			FROM
				v_EvnPrescr EP with (nolock) --назначение

				left join v_EvnPrescrDirection EPD with (nolock) ON EPD.EvnPrescr_id=EP.EvnPrescr_id --связь назначений и направлений
				left join v_TimetableMedService_lite TTMS with (nolock) ON TTMS.EvnDirection_id = EPD.EvnDirection_id --дата бирки
			
				left join v_MedService MS1 WITH (nolock) on MS1.MedService_id = TTMS.MedService_id --служба_1
				left join v_LpuSection LS1 WITH (nolock) on LS1.LpuSection_id = MS1.LpuSection_id --отделение_1

				left join v_MedService MS2 WITH (nolock) on MS2.MedService_id = EP.MedService_id --служба_2
				left join v_LpuSection LS2 WITH (nolock) on LS2.LpuSection_id = MS2.LpuSection_id --отделение_2

				left join v_EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id --Лабораторная диагностика
				left join v_UslugaComplex UC1 with (nolock) on UC1.UslugaComplex_id = EPLD.UslugaComplex_id --услуга_1 лаб

				left join v_EvnQueue EQ WITH (nolock) on EQ.EvnDirection_id = EPD.EvnDirection_id --постановка в очередь
				left join v_MedService MS4 WITH (nolock) on MS4.MedService_id = EQ.MedService_did --служба_4
				left join v_LpuSection LS4 WITH (nolock) on LS4.LpuSection_id = MS4.LpuSection_id --отделение_4

				left join EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescr_id = EP.EvnPrescr_id --Инструментальная диагностика (нет поля EPFD.EvnPrescr_id)
				left join v_EvnPrescrFuncDiagUsluga EPFDU WITH (nolock) on EPFDU.EvnPrescrFuncDiag_id=EPFD.EvnPrescrFuncDiag_id --связка
				left join v_UslugaComplex UC2 with (nolock) on UC2.UslugaComplex_id = EPFDU.UslugaComplex_id --услуга_2 функ

				--EPD даёт EvnDirection_id - он есть в TimeTableResource (Расписание ресурсов)
				left join v_TimeTableResource_lite TTR WITH (nolock) on TTR.EvnDirection_id = EPD.EvnDirection_id --дата функ_2
				left join v_Resource R with (nolock) on R.Resource_id = TTR.Resource_id
				
				left join v_MedService MS6 WITH (nolock) on MS6.MedService_id = R.MedService_id --служба_6
				left join v_LpuSection LS6 WITH (nolock) on LS6.LpuSection_id = MS6.LpuSection_id --отделение_6

				left join v_EvnXml EX with (nolock) on EX.Evn_id = EP.EvnPrescr_id --используется в мобильном арм
			WHERE
				(EP.EvnPrescr_IsExec IS NULL OR EP.EvnPrescr_IsExec=1)
				AND
				EP.PrescriptionType_id IN (11,12) --Лабораторная диагностика, Инструментальная диагностика
				{$filter2}
		) as t
		-- end from
		order BY
			-- order by
			t.EvnUslugaPar_setDate DESC, t.UslugaComplex_Name
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
        $filter = " and EP.Person_id = :Person_id ";

        $sql = "
            SELECT
				-- select
				--push
				NULL AS EvnUslugaPar_id,
				NULL AS EvnUslugaPar_rid,
				coalesce(convert(varchar(10), TTMS.TimetableMedService_begTime, 104),convert(varchar(10), TTR.TimeTableResource_begTime, 104)) as EvnUslugaPar_setDate,
				coalesce(UC1.UslugaComplex_Name,UC2.UslugaComplex_Name) as UslugaComplex_Name,
				coalesce(LS1.Lpu_Name,LS2.Lpu_Name,LS4.Lpu_Name,LS6.Lpu_Name) as Lpu_Name,
				coalesce(MS4.MedService_Name,MS2.MedService_Name,MS1.MedService_Name,MS6.MedService_Name) as MedService_Name,
				null as EvnXml_id --не используется
				-- end select
			FROM
				-- from
				v_EvnPrescr EP with (nolock) --назначение

				left join v_EvnPrescrDirection EPD with (nolock) ON EPD.EvnPrescr_id=EP.EvnPrescr_id --связь назначений и направлений
				left join v_TimetableMedService_lite TTMS with (nolock) ON TTMS.EvnDirection_id = EPD.EvnDirection_id --дата бирки
			
				left join v_MedService MS1 WITH (nolock) on MS1.MedService_id = TTMS.MedService_id --служба_1
				left join v_LpuSection LS1 WITH (nolock) on LS1.LpuSection_id = MS1.LpuSection_id --отделение_1

				left join v_MedService MS2 WITH (nolock) on MS2.MedService_id = EP.MedService_id --служба_2
				left join v_LpuSection LS2 WITH (nolock) on LS2.LpuSection_id = MS2.LpuSection_id --отделение_2

				left join v_EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id --Лабораторная диагностика
				left join v_UslugaComplex UC1 with (nolock) on UC1.UslugaComplex_id = EPLD.UslugaComplex_id --услуга_1 лаб

				left join v_EvnQueue EQ WITH (nolock) on EQ.EvnDirection_id = EPD.EvnDirection_id --постановка в очередь
				left join v_MedService MS4 WITH (nolock) on MS4.MedService_id = EQ.MedService_did --служба_4
				left join v_LpuSection LS4 WITH (nolock) on LS4.LpuSection_id = MS4.LpuSection_id --отделение_4

				left join EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescr_id = EP.EvnPrescr_id --Инструментальная диагностика (нет поля EPFD.EvnPrescr_id)
				left join v_EvnPrescrFuncDiagUsluga EPFDU WITH (nolock) on EPFDU.EvnPrescrFuncDiag_id=EPFD.EvnPrescrFuncDiag_id --связка
				left join v_UslugaComplex UC2 with (nolock) on UC2.UslugaComplex_id = EPFDU.UslugaComplex_id --услуга_2 функ

				--EPD даёт EvnDirection_id - он есть в TimeTableResource (Расписание ресурсов)
				left join v_TimeTableResource_lite TTR WITH (nolock) on TTR.EvnDirection_id = EPD.EvnDirection_id --дата функ_2
				left join v_Resource R with (nolock) on R.Resource_id = TTR.Resource_id
				
				left join v_MedService MS6 WITH (nolock) on MS6.MedService_id = R.MedService_id --служба_6
				left join v_LpuSection LS6 WITH (nolock) on LS6.LpuSection_id = MS6.LpuSection_id --отделение_6

				--left join v_EvnXml EX with (nolock) on EX.Evn_id = EP.EvnPrescr_id --не используется
				-- end from
			WHERE
				-- where
				(EP.EvnPrescr_IsExec IS NULL OR EP.EvnPrescr_IsExec=1)
				AND
				EP.PrescriptionType_id IN (11,12) --Лабораторная диагностика, Инструментальная диагностика
				{$filter}
				-- end where
			order BY
				-- order by
				EvnUslugaPar_setDate DESC, UslugaComplex_Name
				-- end order by
		";
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit'],"distinct"), $data);
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
	 *  Получение списка исследований в ЭМК
	 */
	function loadEvnUslugaParResults($data)
	{
		$arrValText = array(' ');
		$arrUsl =  $this->queryResult("
			SELECT 
				ut.UslugaComplex_id,
				convert(varchar(10), eup_issl.EvnUslugaPar_setDT, 20) as Evn_setDT,
				eup_issl.EvnUslugaPar_setDT,
				null as textValue,
				ut.UslugaTest_ResultValue,
				ut.UslugaTest_ResultValue as UslugaTest_ResultValueText,
				ut.UslugaTest_ResultLower,
				ut.UslugaTest_ResultUpper,
				--2 as UslugaTest_ResultLower,
				--4 as UslugaTest_ResultUpper,
				uc.UslugaComplex_Name,
				uc.UslugaComplex_Code
			FROM 
				v_EvnUslugaPar eup_issl (nolock)
				inner join v_UslugaTest ut (nolock) on ut.UslugaTest_pid = eup_issl.EvnUslugaPar_id
				left join v_UslugaComplex uc (nolock) on ut.UslugaComplex_id = uc.UslugaComplex_id
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
				ut.UslugaComplex_id,
				uc.UslugaComplex_Name,
				uc.UslugaComplex_Code
			FROM 
				v_EvnUslugaPar eup_issl with (nolock)
				inner join v_UslugaTest ut with (nolock) on ut.UslugaTest_pid = eup_issl.EvnUslugaPar_id
				left join v_UslugaComplex uc with (nolock) on ut.UslugaComplex_id = uc.UslugaComplex_id
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
			from v_EvnUslugaPar with(nolock)
			where EvnUslugaPar_id = :id
		", ['id' => $id]);
	}

	/**
	 * Печать лабораторных исследований в формате HL7
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
				select top 1
					eup.EvnUslugaPar_id,															/*ID документа*/
					LpuOID.PassportToken_tid,														/*OID организации*/
					--msf.MedPersonal_id,																/**/
					--msf.Person_SurName as MedPersonal_SurName,										/*Фамилия, Имя, Отчество автор документа*/
					--msf.Person_FirName as MedPersonal_FirName,
					--msf.Person_SecName as MedPersonal_SecName,
					L.Lpu_Nick,																		/*Наименование медицинской организации*/
					L.Lpu_Phone,																	/*тел медорганизации*/
					L.Org_Name,																		/*Наименование медицинской организации полное*/
					OL.Org_Www as Lpu_Www,															/*сайт медорганизации*/
					lua.Address_Address as LAddress_Address,										/*Адрес медицинской организаци*/
					lua.KLRgn_id as LKLRgn_id,														/*код региона*/
					psr.Person_Snils,																/*СНИЛС пациента*/
					psr.Person_id,																	/*идентификатор пациента*/
					psr.Person_SurName,																/*фамилия пациента*/
					psr.Person_FirName,																/*имя пациента*/
					psr.Person_SecName,																/*отчество пациента*/
					convert(varchar(8), psr.Person_BirthDay, 112) as Person_BirthDay,	/*ФОРМАТ YYYYMMDD !!!!*/
					s.Sex_code,																		/*код пола*/		
					s.Sex_Name,																		/*тект пола*/
					--mp.MedPost_Code,																/*Код должности автора*/
					--mp.MedPost_Name,																/* имя должности автора*/
					convert(varchar(8), eup.EvnUslugaPar_setDate, 112) as Document_DateCreate, 		/*Дата создания документа*/

					eup.EvnUslugaPar_rid,															/*Уникальный идентификатор случая оказания медицинской помощи*/

					convert(varchar(20), eup.EvnUslugaPar_signDT, 120) as EvnUslugaPar_signDT,		/*Дата подписи документа автором*/
					msf_sign.MedPersonal_id as Sig_MedPersonal_id, 									/*ID автора подписи*/
					msf_sign.Person_SurName as Sig_MedPersonal_SurName,								/*Фамилия, Имя, Отчество подписавшего документа*/
					msf_sign.Person_FirName as Sig_MedPersonal_FirName,
					msf_sign.Person_SecName as Sig_MedPersonal_SecName,
					mp_sign.MedPost_Code as Sig_MedPost_Code,										/*Код должности автора*/
					mp_sign.MedPost_Name as Sig_MedPost_Name,										/* имя должности автора*/

					convert(varchar(20), els.EvnLabSample_DelivDT, 120) as EvnLabSample_DelivDT, 	/*Дата начала исследования (доставка материала в лабораторию)*/
					convert(varchar(20), els.EvnLabSample_StudyDT, 120) as EvnLabSample_StudyDT, 	/*Дата окончания исследования*/

					msfs.MedPersonal_id as EvnLabSample_Doctor_id,									/*исполнитель исследования (врач)*/
					msfs.Person_SurName as EvnLabSample_Doctor_SurName,								/*Фамилия, Имя, Отчество автор документа*/
					msfs.Person_FirName as EvnLabSample_Doctor_FirName,
					msfs.Person_SecName as EvnLabSample_Doctor_SecName,
					mps.MedPost_Code as EvnLabSampleMedPost_Code,									/*Код должности автора*/
					mps.MedPost_Name as EvnLabSampleMedPost_Name,									/* имя должности автора*/

					EvnLabSampleLpuOID.PassportToken_tid as EvnLabSample_PassportToken_tid,			/*OID организации делающий анализ*/
					EvnLabSampleL.Lpu_Phone as EvnLabSampleLpu_Phone,								/*тел медорганизации*/
					EvnLabSampleL.Org_Name as EvnLabSampleOrg_Name,									/*Наименование медицинской организации полное*/
					EvnLabSampleLua.Address_Address as EvnLabSampleLAddress_Address,				/*Адрес медицинской организаци*/
					EvnLabSampleLua.KLRgn_id as EvnLabSampleLKLRgn_id,								/*код региона*/


					--eup.EvnUslugaPar_pid,														/*ссылка на движение EvnSection или посещение EvnVizitPL */
					usc.UslugaComplex_Code,														/*Код услуги*/
					usc.UslugaComplex_Name,														/*Имя услуги*/
					usc.UslugaComplex_id,
					elr.EvnLabRequest_BarCodes,													/*штрихкод в виде номер_проб:штрих-код*/
					elr.EvnLabRequest_id,														/*ID заявки лаб исследования*/
					els.EvnLabSample_id,														/*ID пробы исследования*/
					ltm.LabTestMaterial_Code,													/*лаб материал код*/
					ltm.LabTestMaterial_Name,													/*лаб материал имя*/
					a.Analyzer_Name,																/*имя анализатора*/

					/*врачебн помощь*/
					isNull(eps.EvnPS_NumCard,epl.EvnPL_NumCard) as MedHelpId,					/*это елемент extension для стационара или поликлмнники*/
					eu.EvnUsluga_id,															/*ID паракл,услуги*/
					convert(varchar(20),e.Evn_setDT, 120) as MedHelpStart,						/*даты начала и конца случая*/
					convert(varchar(20),e.Evn_disDT, 120) as MedHelpEnd,
					LpuOIDMedHelp.PassportToken_tid as MedHelpOID					/*OID стационара/поликлинники*/
						from 
						v_EvnUslugaPar eup with (nolock)
						--left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = eup.MedStaffFact_id

						left join fed.v_PassportToken LpuOID with (nolock) on LpuOID.Lpu_id = eup.Lpu_id
						left join v_Lpu l with (nolock) on l.Lpu_id = eup.Lpu_id
						left join v_Org ol with (nolock) on ol.Org_id = l.Org_id
						left join v_Address_all lua with (nolock) on lua.Address_id = l.UAddress_id
						left join v_PersonState psr with (nolock) on psr.Person_id = eup.Person_id
						left join v_Sex s with (nolock) on s.Sex_id = psr.Sex_id

						--left join persis.Post p on p.id = msf.Post_id
						--left join nsi.v_MedPost mp on mp.MedPost_id = p.MedPost_id

						left join v_MedStaffFact msf_sign with (nolock) on (msf_sign.MedStaffFact_id = :MedStaffFact_id and eup.Lpu_id=msf_sign.Lpu_id )
						left join persis.Post p_sign with (nolock)  on p_sign.id = msf_sign.Post_id
						left join nsi.v_MedPost mp_sign with (nolock) on mp_sign.MedPost_id = p_sign.MedPost_id	

						left join v_EvnLabSample els with (nolock)  on els.EvnLabSample_id=eup.EvnLabSample_id


						left join persis.v_MedWorker mw with (nolock)  on mw.MedWorker_Id = IsNull(els.MedPersonal_aid,els.MedPersonal_did)
						left join v_MedStaffFact msfs with (nolock)  on (msfs.Person_id = mw.Person_id and eup.Lpu_id=msfs.Lpu_id and (els.LpuSection_did=msfs.LpuSection_id or els.LpuSection_aid=msfs.LpuSection_id))
						left join persis.Post ps on ps.id = msfs.Post_id
						left join nsi.v_MedPost mps on mps.MedPost_id = ps.MedPost_id

						/*материал*/
						left join v_RefSample RefSample_id_ref with (nolock)  on RefSample_id_ref.RefSample_id = els.RefSample_id
						left join v_RefMaterial rm with (nolock) on rm.RefMaterial_id = RefSample_id_ref.RefMaterial_id
						left join nsi.LabTestMaterial ltm with (nolock) on ltm.LabTestMaterial_id = rm.LabTestMaterial_id

						/*имя организация лаборатории*/
						/*inner join v_MedService ms (nolock) on ms.MedService_id = els.MedService_id Это пункт забора служба внутри*/

						left join fed.v_PassportToken EvnLabSampleLpuOID with(nolock) on EvnLabSampleLpuOID.Lpu_id = IsNull(els.Lpu_aid,els.Lpu_did)
						left join v_Lpu EvnLabSampleL with (nolock) on EvnLabSampleL.Lpu_id = IsNull(els.Lpu_aid,els.Lpu_did)
						left join v_Address_all EvnLabSampleLua with (nolock) on EvnLabSampleLua.Address_id = EvnLabSampleL.UAddress_id

						/*услуги*/
						left join v_EvnUsluga eu with (nolock) on eu.EvnUsluga_id=eup.EvnUslugaPar_id
						left join v_UslugaComplex usc with (nolock) on usc.UslugaComplex_id=eu.UslugaComplex_id

						/*заявка*/
						left join v_EvnLabRequest elr with (nolock) on elr.EvnLabRequest_id=els.EvnLabRequest_id

						/*анализатор*/
						left join lis.Analyzer a with (nolock) on a.Analyzer_id=els.Analyzer_id

						/*врачебная помощь*/
						left JOIN v_Evn e (NOLOCK) ON (eup.EvnUslugaPar_rid = e.Evn_id and e.EvnClass_id<>47)
						left join dbo.EvnPS eps (nolock) on (eps.Evn_id = e.Evn_id)
						left join dbo.EvnPLBase eplb (nolock) on (eplb.Evn_id = e.Evn_id)
						left join dbo.EvnPL epl (nolock) on (epl.EvnPLBase_id = eplb.EvnPLBase_id)
						left join fed.v_PassportToken LpuOIDMedHelp with (nolock) on LpuOIDMedHelp.Lpu_id = e.Lpu_id

						where
							eup.EvnUslugaPar_id = :EvnUslugaPar_id 


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
					lt.LabTest_Code,													/*код услуги по справочнику*/
					lt.LabTest_Name,
					uc.UslugaComplex_Code + ' ' + uc.UslugaComplex_Name as Analize_Name,  /*код + имя услуги*/
					convert(varchar(20),ut.UslugaTest_setDT, 120) as UslugaTest_setDT,
					ut.UslugaTest_ResultValue,
					ut.UslugaTest_ResultUnit,
					ut.UslugaTest_ResultLower + ' - ' + ut.UslugaTest_ResultUpper as RefLimits,
					ut.UslugaTest_Comment,
					a.Analyzer_Name,
					uc.UslugaComplex_id,
					els.EvnLabSample_id

						from 
							v_UslugaTest ut with (nolock)
							left join v_UslugaComplex uc with (nolock) on ut.UslugaComplex_id = uc.UslugaComplex_id
							left join UslugaComplex ucg with (nolock) on uc.UslugaComplex_2011id = ucg.UslugaComplex_id
							left join nsi.NomenMedUsluga nmu with (nolock) on nmu.NomenMedUsluga_Code = IsNull(uc.UslugaComplex_oid, ucg.UslugaComplex_oid)

							left join nsi.LabTestLink ltl with (nolock) on ltl.NomenMedUsluga_id = nmu.NomenMedUsluga_id
							left join nsi.LabTest lt with (nolock) on lt.LabTest_id = ltl.LabTest_id

							left join v_EvnLabSample els (nolock) on els.EvnLabSample_id=ut.EvnLabSample_id
							left join lis.Analyzer a with (nolock) on a.Analyzer_id=els.Analyzer_id

							/*left join v_RefValues as rf with (nolock) on rf.RefValues_id=ut.RefValues_id*/

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
				$bar_codes[trim($item[0])]=trim($item[1]);
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
			$LpuOID=$this->queryResult("select PassportToken_tid from fed.v_PassportToken  with (nolock) where Lpu_id =:Lpu_id",["Lpu_id"=>$EvnUslugaPar["Lpu_id"]]);
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
							mp_sign.MedPost_Code as Sig_MedPost_Code,
							mp_sign.MedPost_Name as Sig_MedPost_Name
							  from 
								 v_MedStaffFact msf_sign with (nolock)
								left join persis.Post p_sign with (nolock)  on p_sign.id = msf_sign.Post_id
								left join nsi.v_MedPost mp_sign with (nolock) on mp_sign.MedPost_id = p_sign.MedPost_id
									where 
										msf_sign.MedStaffFact_id = :MedStaffFact_id",["MedStaffFact_id"=>$data['MedStaffFact_id']]);

			$resp[0]["SignMedStaffFact_id"]=$Person["MedStaffFact_id"];
			$resp[0]["Sig_MedPersonal_id"]=$Person["Person_id"];
			$resp[0]["Sig_MedPersonal_SurName"]=$Person["Person_SurName"];
			$resp[0]["Sig_MedPersonal_FirName"]=$Person["Person_FirName"];
			$resp[0]["Sig_MedPersonal_SecName"]=$Person["Person_SecName"];
			$resp[0]=array_merge($resp[0],$mp[0]);
			
			//OID и другие данные кто выполнял анализ
			$LpuOID=$this->queryResult("select PassportToken_tid from fed.v_PassportToken  with (nolock) where Lpu_id =:Lpu_id",["Lpu_id"=>$EvnUslugaPar["Lpu_did"]]);
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
							mp.MedPost_Code as EvnLabSampleMedPost_Code,
							mp.MedPost_Name as EvnLabSampleMedPost_Name,
							msf.MedStaffFact_id as EvnLabSample_Doctor_id
							  from 
								 v_MedStaffFact msf with (nolock)
								left join persis.Post p with (nolock)  on p.id = msf.Post_id
								left join nsi.v_MedPost mp with (nolock) on mp.MedPost_id = p.MedPost_id
								left join persis.v_MedWorker mw with (nolock)  on mw.MedWorker_Id = :MedPersonal_did and :Lpu_did=msf.Lpu_id and (msf.LpuSection_id=:LpuSection_did or :LpuSection_aid=msf.LpuSection_id)
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
			$LpuOID=$this->queryResult("select PassportToken_tid from fed.v_PassportToken  with (nolock) where Lpu_id =:Lpu_id",["Lpu_id"=>($EvnLabSample["Lpu_aid"]) ? $EvnLabSample["Lpu_aid"]: $EvnLabSample["Lpu_did"]]);
			$resp[0]["EvnLabSample_PassportToken_tid"]=$LpuOID[0]["PassportToken_tid"];
			
			//коды услуг
			$LabRequestUslugaComplexData=$this->lis->GET("EvnLabRequest/LabRequestUslugaComplexData",["EvnUslugaPar_id"=>$data['EvnUslugaPar_id']])["data"][0];
			//читаем из справочника
			$Usluga_Complex=$this->queryResult("
					select top 1 *
						from v_UslugaComplex with (nolock)
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
						isNull(eps.EvnPS_NumCard,epl.EvnPL_NumCard) as MedHelpId,					/*это елемент extension для стационара или поликлмнники*/
						--eu.EvnUsluga_id,															/*ID паракл,услуги*/
						convert(varchar(20),e.Evn_setDT, 120) as MedHelpStart,						/*даты начала и конца случая*/
						convert(varchar(20),e.Evn_disDT, 120) as MedHelpEnd,
						LpuOIDMedHelp.PassportToken_tid as MedHelpOID					/*OID стационара/поликлинники*/

						from v_Evn e (NOLOCK) 
						left join dbo.EvnPS eps (nolock) on (eps.Evn_id = e.Evn_id)
						left join dbo.EvnPLBase eplb (nolock) on (eplb.Evn_id = e.Evn_id)
						left join dbo.EvnPL epl (nolock) on (epl.EvnPLBase_id = eplb.EvnPLBase_id)
						left join fed.v_PassportToken LpuOIDMedHelp with (nolock) on LpuOIDMedHelp.Lpu_id = e.Lpu_id
					
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
										nmu.NomenMedUsluga_Code = IsNull(uc.UslugaComplex_oid, ucg.UslugaComplex_oid) and
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
