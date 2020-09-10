<?php
class PersonDoubles_model extends CI_Model {
    /**
     * Doc
     */
	function __construct() {
		parent::__construct();
	}

    /**
     * Doc
     */
	function checkPersonDoublesGroup($data) {
		$query = "
			select
				count(TruePersonDoubles_id) as cnt
			from
				TruePersonDoubles (nolock)
			where
				TruePersonDoubles_id <> :PersonDoubles_id
				and (Person_id = :Person_id or Person_did = :Person_id)
				and (Person_id = :Person_did or Person_did = :Person_did)
		";

		$queryParams = array(
			'Person_did' => $data['Person_did'],
			'Person_id' => $data['Person_id'],
			'PersonDoubles_id' => $data['PersonDoubles_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return -1;
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) != 1 || !isset($response[0]['cnt']) ) {
			return -1;
		}

		return $response[0]['cnt'];
	}

    /**
     * Doc
     */
	function deletePersonDoublesGroup($data) {
		$query = "delete from TruePersonDoubles where TruePersonDoubles_id = :PersonDoubles_id";
		$result = $this->db->query($query, array('PersonDoubles_id' => $data['PersonDoubles_id']));

		return array(array('Error_Msg' => ''));
	}

    /**
     * Doc
     */
	function loadPersonDoublesData($data) {
		$query = "
			select
				PS.Server_pid,
				PS.Person_SurName as Person_Surname,
				PS.Person_FirName as Person_Firname,
				PS.Person_SecName as Person_Secname,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
				case when PS.Server_pid = 0 then 'Да' else 'Нет' end as Person_IsBDZ,
				case when FedLgot.IsFedLgot = 1 then 'Да' else 'Нет' end as Person_IsFedLgot,
				case
					when FedLgot.IsFedLgot = 1 and Refuse.Person_IsRefuse = 1 then 'Да'
					when FedLgot.IsFedLgot = 1 and Refuse.Person_IsRefuse is null then 'Нет'
					else '' end
				as Person_IsRefuse,
				ISNULL(AttachLpu.Lpu_Name, '') as Lpu_Name,
				LTRIM(RTRIM(ISNULL(PS.Polis_Ser, '') + ' ' + ISNULL(PS.Polis_Num, ''))) as Polis_SerNum,
				convert(varchar(10), Polis.Polis_begDate, 104) + ', ' + RTRIM(PolisOrg.Org_Name) as Polis_setInfo,
				PS.Person_Snils as Person_Snils,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				ISNULL(Sex.Sex_Name, '') as Sex_Name,
				ISNULL(SocStatus.SocStatus_Name, '') as SocStatus_Name,
				ISNULL(Document.Document_Ser, '') + ' ' + ISNULL(Document.Document_Num, '') + ', ' + RTRIM(OrgDoc.Org_Name) as Document_Info,
				RTRIM(OrgJob.Org_Name) as OrgJob_Name
			from
				TruePersonDoubles TPD with (nolock)
				inner join v_PersonState PS (nolock) on PS.Person_id = TPD.Person_id
					and PS.Server_id = TPD.Server_id
				left join Polis (nolock) on Polis.Polis_id = PS.Polis_id
				left join OrgSmo (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org PolisOrg (nolock) on PolisOrg.Org_id = OrgSmo.Org_id
				left join Job (nolock) on Job.Job_id = PS.Job_id
				left join Org OrgJob (nolock) on OrgJob.Org_id = Job.Org_id
				left join Sex (nolock) on Sex.Sex_id = PS.Sex_id
				left join SocStatus (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				left join [Address] PAddr (nolock) on PAddr.Address_id = PS.PAddress_id
				left join [Address] UAddr (nolock) on UAddr.Address_id = PS.UAddress_id
				left join Document (nolock) on Document.Document_id = PS.Document_id
				left join OrgDep (nolock) on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org OrgDoc (nolock) on OrgDoc.Org_id = OrgDep.Org_id
				outer apply (
					select top 1 1 as IsFedLgot
					from v_PersonPrivilege PP (nolock)
						inner join PrivilegeType PT (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1
					where PP.Person_id = PS.Person_id
						and PP.PersonPrivilege_begDate <= dbo.tzGetDate()
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) FedLgot
				outer apply (
					select top 1
						RTRIM(Lpu.Lpu_Name) as Lpu_Name
					from v_PersonCard PC (nolock)
						inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = PC.Lpu_id
					where (1 = 1)
						and PC.Person_id = PS.Person_id
						and PC.PersonCard_begDate <= dbo.tzGetDate()
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) AttachLpu
				outer apply (
					select top 1
						1 as Person_IsRefuse
					from
						v_PersonRefuse PR (nolock)
					where PR.Person_id = PS.Person_id
						and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate())
						and PR.PersonRefuse_IsRefuse = 2 
				) Refuse
			where
				TPD.TruePersonDoubles_id = :PersonDoubles_id
			union all
			select
				PS.Server_pid,
				PS.Person_SurName as Person_Surname,
				PS.Person_FirName as Person_Firname,
				PS.Person_SecName as Person_Secname,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday,
				case when PS.Server_pid = 0 then 'Да' else 'Нет' end as Person_IsBDZ,
				case when FedLgot.IsFedLgot = 1 then 'Да' else 'Нет' end as Person_IsFedLgot,
				case
					when FedLgot.IsFedLgot = 1 and Refuse.Person_IsRefuse = 1 then 'Да'
					when FedLgot.IsFedLgot = 1 and Refuse.Person_IsRefuse is null then 'Нет'
					else '' end
				as Person_IsRefuse,
				ISNULL(AttachLpu.Lpu_Name, '') as Lpu_Name,
				LTRIM(RTRIM(ISNULL(PS.Polis_Ser, '') + ' ' + ISNULL(PS.Polis_Num, ''))) as Polis_SerNum,
				convert(varchar(10), Polis.Polis_begDate, 104) + ', ' + RTRIM(PolisOrg.Org_Name) as Polis_setInfo,
				PS.Person_Snils as Person_Snils,
				RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name,
				RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name,
				ISNULL(Sex.Sex_Name, '') as Sex_Name,
				ISNULL(SocStatus.SocStatus_Name, '') as SocStatus_Name,
				ISNULL(Document.Document_Ser, '') + ' ' + ISNULL(Document.Document_Num, '') + ', ' + RTRIM(OrgDoc.Org_Name) as Document_Info,
				RTRIM(OrgJob.Org_Name) as OrgJob_Name
			from
				TruePersonDoubles TPD with (nolock)
				inner join v_PersonState PS (nolock) on PS.Person_id = TPD.Person_did
					and PS.Server_id = TPD.Server_did
				left join Polis (nolock) on Polis.Polis_id = PS.Polis_id
				left join OrgSmo (nolock) on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org PolisOrg (nolock) on PolisOrg.Org_id = OrgSmo.Org_id
				left join Job (nolock) on Job.Job_id = PS.Job_id
				left join Org OrgJob (nolock) on OrgJob.Org_id = Job.Org_id
				left join Sex (nolock) on Sex.Sex_id = PS.Sex_id
				left join SocStatus (nolock) on SocStatus.SocStatus_id = PS.SocStatus_id
				left join [Address] PAddr (nolock) on PAddr.Address_id = PS.PAddress_id
				left join [Address] UAddr (nolock) on UAddr.Address_id = PS.UAddress_id
				left join Document (nolock) on Document.Document_id = PS.Document_id
				left join OrgDep (nolock) on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org OrgDoc (nolock) on OrgDoc.Org_id = OrgDep.Org_id
				outer apply (
					select top 1 1 as IsFedLgot
					from v_PersonPrivilege PP (nolock)
						inner join PrivilegeType PT (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 1 and isnumeric(PT.PrivilegeType_Code) = 1
					where PP.Person_id = PS.Person_id
						and PP.PersonPrivilege_begDate <= dbo.tzGetDate()
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) FedLgot
				outer apply (
					select top 1
						Lpu.Lpu_Name
					from v_PersonCard PC (nolock)
						inner join v_Lpu Lpu (nolock) on Lpu.Lpu_id = PC.Lpu_id
					where (1 = 1)
						and PC.Person_id = PS.Person_id
						and PC.PersonCard_begDate <= dbo.tzGetDate()
						and (PC.PersonCard_endDate is null or PC.PersonCard_endDate >= cast(convert(char(10), dbo.tzGetDate(), 112) as datetime))
				) AttachLpu
				outer apply (
					select top 1
						1 as Person_IsRefuse
					from
						v_PersonRefuse PR (nolock)
					where PR.Person_id = PS.Person_id
						and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate())
						and PR.PersonRefuse_IsRefuse = 2 
				) Refuse
			where
				TPD.TruePersonDoubles_id = :PersonDoubles_id
		";

		$queryParams = array(
			'PersonDoubles_id' => $data['PersonDoubles_id']
		);

		//echo getDebugSQL($query, $queryParams); exit();
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Получение данных двойников для модерации
	 */
	function getPersonDoublesForModeration($data) {
		$filterList = array('PersonDoublesStatus_id is null AND ps1.Person_id is not null AND ps2.Person_id is not null');
		$queryParams = array();

		if ( !empty($data['PersonSurName']) ) {
			$filterList[] = "ps1.Person_Surname like :PersonSurName";
			$queryParams['PersonSurName'] = $data['PersonSurName'] . "%";
		}

		if ( !empty($data['PersonFirName']) ) {
			$filterList[] = "ps1.Person_Firname like :PersonFirName";
			$queryParams['PersonFirName'] = $data['PersonFirName'] . "%";
		}

		if ( !empty($data['PersonSecName']) ) {
			$filterList[] = "ps1.Person_Secname like :PersonSecName";
			$queryParams['PersonSecName'] = $data['PersonSecName'] . "%";
		}

		if ( !empty($data['PersonBirthDay']) ) {
			$filterList[] = "ps1.Person_Birthday = :PersonBirthDay";
			$queryParams['PersonBirthDay'] = $data['PersonBirthDay'];
		}

		if ( !empty($data['Lpu_did']) ) {
			$queryParams['Lpu_did'] = $data['Lpu_did'];

			if ( $data['exceptSelectedLpu'] ) {
				$filterList[] = "l.Lpu_id <> :Lpu_did";
			}
			else {
				$filterList[] = "l.Lpu_id = :Lpu_did";
			}
		}

		$sql = "
			select
				-- select
				PersonDoubles_id,
				ps1.Person_id as Person_id1,
				ps2.Person_id as Person_id2,
				'<b>' + isnull(rtrim(ps1.Person_Surname), '') + '</b><br/>' + rtrim(ps2.Person_Surname) as Person_Surname,
				'<b>' + isnull(rtrim(ps1.Person_Firname), '') + '</b><br/>' + isnull(rtrim(ps2.Person_Firname),'') as Person_Firname,
				'<b>' + isnull(rtrim(ps1.Person_Secname), '') + '</b><br/>' + isnull(rtrim(ps2.Person_Secname), '') as Person_Secname,
				'<b>' + isnull(convert(varchar, ps1.Person_Birthday, 104), '') + '</b><br/>' + convert(varchar, ps2.Person_Birthday, 104) as Person_BirthDay,
				ps1.Server_pid as Server_id1,
				ps2.Server_pid as Server_id2,
				'<b>' + isnull(cast(ps1.Server_pid as varchar), '') + '</b><br/>' + cast(ps2.Server_pid as varchar) as Server_id,
				PersonDoublesStatus_id as PersonDoubles_Status,
				l.Lpu_Nick,
				case when ps1.Server_pid=0 then 1 else 0 end as Person_IsBDZ1,
				case when ps2.Server_pid=0 then 1 else 0 end as Person_IsBDZ2,
				'<b>' + case when ps1.Server_pid=0 then 'Да' else 'Нет' end + '</b><br/>' + case when ps2.Server_pid=0 then 'Да' else 'Нет' end as Person_IsBDZ,
				PersonDoubles_insDT
				-- end select
			from
				-- from
				pd.PersonDoubles pd (nolock)
				left join v_PersonState ps1 (nolock) on ps1.Person_id = pd.Person_id
				left join v_PersonState ps2 (nolock) on ps2.Person_id = pd.Person_did
				left join v_pmUser pu (nolock) on pu.pmUser_id = pd.pmUser_insID
				outer apply (
					select top 1
						l.Lpu_id
					from
						v_pmUserCacheOrg puco (nolock)
						inner join v_Lpu l (nolock) on l.Org_id = puco.Org_id
					where
						puco.pmUserCache_id = pu.PMUser_id
				) puco
				left join v_Lpu l (nolock) on l.Lpu_id = ISNULL(pu.Lpu_id, puco.Lpu_id)
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				pd.PersonDoubles_insDT
				-- end order by
		";

		$res = null;

		// замена функции преобразования запроса в запрос для получения количества записей
		//echo getDebugSQL($sql, array());die;
		$count_sql = getCountSQLPH($sql, 'PersonDoubles_id', 'distinct');

		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 ) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit'], 'distinct');
		}

		$res = $this->db->query($sql, $queryParams);

		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);

		if ( is_object($count_res) ) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];

		}
		else {
			return false;
		}

		if ( is_object($res) ) {
			$response = $res->result('array');
			$response[] = array('__countOfAllRows' => $count);
			return $response;
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Смена главной записи и двойника
	 */
	function changePersonDoubles($data)
	{
		$query = "
			update pd.PersonDoubles
			set
				Person_id = Person_did,
				Person_did = Person_id
			where
				Person_id = :Person_id
				and Person_did = :Person_did
		";
		$res = $this->db->query($query, $data);
		if ( $res > 0 ) {
			return array('Error_Msg' => '');
		} else {
			return array('Error_Msg' => 'Ошибка!');
		}
	}
	
	
	/**
	 * Отказ в модерации
	 */
	function cancelPersonDoubles($data)
	{

		$query = "
			update pd.PersonDoubles
			set
				PersonDoublesStatus_id = :PersonDoublesStatus_id,
				PersonDoubles_updDT = dbo.tzGetDate(),
				pmUser_updId = :pmUser_id
			where
				Person_id = :Person_id
				and Person_did = :Person_did
		";

        //echo getDebugSQL($query, $data);die();
		$res = $this->db->query($query, $data);
		if ( $res > 0 ) {
			return array('Error_Msg' => '');
		} else {
			return array('Error_Msg' => 'Ошибка!');
		}
	}

    /**
     * Doc
     */
	function loadPersonDoublesGroupsList($data) {
		$query = "
			select
				TruePersonDoubles_id as PersonDoubles_id,
				Person_id,
				Person_did,
				Server_id,
				Server_did,
				TruePersonDoubles_Surname as PersonDoubles_Surname,
				TruePersonDoubles_Firname as PersonDoubles_Firname,
				TruePersonDoubles_Secname as PersonDoubles_Secname,
				convert(varchar(10), TruePersonDoubles_Birthday, 104) as PersonDoubles_Birthday
			from
				TruePersonDoubles with (nolock)
			where
				pmUser_updID = :pmUser_id
		";

		$queryParams = array(
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Doc
     */
	function searchPersonDoubles($data) {
		// Удаление записей текущего пользователя из таблицы TruePersonDoubles
		$query = "
			delete from TruePersonDoubles where pmUser_insID = :pmUser_id
		";

		$queryParams = array(
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $queryParams);
        /*
		if ( !is_object($res) ) {
			return array('Error_Msg' => 'Ошибка при удалении старых записей из таблицы');
		}
        */
		// Формирование запроса с учетом фильтров
		$filter = "(1 = 1)";
		$join_filter = "";
		$join_str = "";

		$queryParams = array(
			'pmUser_id' => $data['pmUser_id']
		);

		if ( isset($data['Document_SerNum']) ) {
			$filter .= " and ISNULL(ps1.Document_Ser, '') + ' ' + ISNULL(ps1.Document_Num, '') = :Document_SerNum";
			$join_filter .= " and ISNULL(ps1.Document_Ser, '') + ' ' + ISNULL(ps1.Document_Num, '') = ISNULL(ps2.Document_Ser, '') + ' ' + ISNULL(ps2.Document_Num, '')";
			$queryParams['Document_SerNum'] = $data['Document_SerNum'];
		}

		if ( isset($data['Person_Birthday']) ) {
			$filter .= " and ps1.Person_BirthDay = cast(:Person_Birthday as datetime)";
			$join_filter .= " and ps1.Person_BirthDay = ps2.Person_BirthDay";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}

		if ( isset($data['Person_BirthYear']) ) {
			$filter .= " and YEAR(ps1.Person_BirthDay) = :Person_BirthYear";
			$join_filter .= " and YEAR(ps1.Person_BirthDay) = YEAR(ps2.Person_BirthDay)";
			$queryParams['Person_BirthYear'] = $data['Person_BirthYear'];
		}

		if ( isset($data['Person_EdNum']) ) {
			$filter .= " and ps1.Person_EdNum = :Person_EdNum";
			$join_filter .= " and ps1.Person_EdNum = ps2.Person_EdNum";
			$queryParams['Person_EdNum'] = $data['Person_EdNum'];
		}

		if ( isset($data['Person_Firname']) ) {
			$filter .= " and replace(ps1.Person_FirName, 'Ё', 'Е') = replace(:Person_Firname, 'Ё', 'Е')";
			$join_filter .= " and replace(ps1.Person_FirName, 'Ё', 'Е') = replace(ps2.Person_FirName, 'Ё', 'Е')";
			$queryParams['Person_Firname'] = $data['Person_Firname'];
		}

		if ( isset($data['Person_Secname']) ) {
			$filter .= " and replace(ps1.Person_SecName, 'Ё', 'Е') = replace(:Person_Secname, 'Ё', 'Е')";
			$join_filter .= " and (replace(ps1.Person_SecName, 'Ё', 'Е') = replace(ps2.Person_SecName, 'Ё', 'Е') or (nullif(replace(ps1.Person_SecName, ' ', ''), '---') is null and  nullif(replace(ps2.Person_SecName, ' ', ''), '---') is null))";
			$queryParams['Person_Secname'] = $data['Person_Secname'];
		}

		if ( isset($data['Person_Snils']) ) {
			$filter .= " and ps1.Person_Snils = :Person_Snils";
			$join_filter .= " and ps1.Person_Snils = ps2.Person_Snils";
			$queryParams['Person_Snils'] = $data['Person_Snils'];
		}

		if ( isset($data['Person_Surname']) ) {
			$filter .= " and replace(ps1.Person_SurName, 'Ё', 'Е') = replace(:Person_Surname, 'Ё', 'Е')";
			$join_filter .= " and replace(ps1.Person_SurName, 'Ё', 'Е') = replace(ps2.Person_SurName, 'Ё', 'Е')";
			$queryParams['Person_Surname'] = $data['Person_Surname'];
		}

		if ( isset($data['Polis_SerNum']) ) {
			$filter .= " and ISNULL(ps1.Polis_Ser, '') + ' ' + ISNULL(ps1.Polis_Num, '') = :Polis_SerNum";
			$join_filter .= " and ISNULL(ps1.Polis_Ser, '') + ' ' + ISNULL(ps1.Polis_Num, '') = ISNULL(ps2.Polis_Ser, '') + ' ' + ISNULL(ps2.Polis_Num, '')";
			$queryParams['Polis_SerNum'] = $data['Polis_SerNum'];
		}

		if ( isset($data['Sex_id']) ) {
			$filter .= " and ps1.Sex_id = :Sex_id";
			$join_filter .= " and ps1.Sex_id = ps2.Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		}

		if ( isset($data['SocStatus_id']) ) {
			$filter .= " and ps1.SocStatus_id = :SocStatus_id";
			$join_filter .= " and ps1.SocStatus_id = ps2.SocStatus_id";
			$queryParams['SocStatus_id'] = $data['SocStatus_id'];
		}

		$query = "
			insert into TruePersonDoubles (Person_id, Person_did, Server_id, Server_did, TruePersonDoubles_Surname, TruePersonDoubles_Firname, TruePersonDoubles_Secname, TruePersonDoubles_Birthday, pmUser_insID, pmUser_updID, TruePersonDoubles_insDT, TruePersonDoubles_updDT)
			select top 500
				ps1.Person_id as Person_id,
				ps2.Person_id as Person_did,
				ps1.Server_id as Server_id,
				ps2.Server_id as Server_did,
				ps1.Person_SurName as TruePersonDoubles_Surname,
				ps1.Person_FirName as TruePersonDoubles_Firname,
				ps1.Person_SecName as TruePersonDoubles_Secname,
				ps1.Person_BirthDay  as TruePersonDoubles_Birthday,
				:pmUser_id as pmUser_insID,
				:pmUser_id as pmUser_updID,
				dbo.tzGetDate() as TruePersonDoubles_insDT,
				dbo.tzGetDate() as TruePersonDoubles_updDT
			from
				v_PersonState ps1 with (nolock)
				inner join v_PersonState ps2 with (nolock) on (1 = 1)
					and ps2.Person_id <> ps1.Person_id
					and (ps2.Server_pid <> ps1.Server_pid or (ps2.Server_pid <> 0 and ps1.Server_pid <> 0))
					" . $join_filter . "
				" . $join_str . "
			where " . $filter . "
		";

		// echo getDebugSQL($query, $queryParams);
		// exit();

		// Выполнение запроса и сохранение записей в таблице TruePersonDoubles
		$res = $this->db->query($query, $queryParams);
        /*
		if ( !is_object($res) ) {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (поиск двойников)');
		}
        */
		return array('Error_Msg' => '');
	}
}
