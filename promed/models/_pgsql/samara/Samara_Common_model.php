<?php

require_once(APPPATH.'models/_pgsql/Common_model.php');

class Samara_Common_model extends Common_model {
	/**
	 * description
	 */
    function __construct() {
		parent::__construct();
    }
	/**
	 * description
	 */
	function loadPersonData($data) {
		// Если передали PersonEvn_id, значит определенная периодика нужна и читать будем из периодики
		$object = "v_PersonState";
		$filter = " (1=1)";
		$params =  array('Person_id' => $data['Person_id']);
		if ((isset($data['PersonEvn_id'])) && ($data['PersonEvn_id']>0))
		{
			$object = "v_Person_bdz";
			$params['Server_id'] = $data['Server_id'];
			$filter .= " and PS.Server_id = :Server_id";
			$params['PersonEvn_id'] = $data['PersonEvn_id'];
			$filter .= " and PS.PersonEvn_id = :PersonEvn_id";
		}
		else 
		{
			$params['Person_id'] = $data['Person_id'];
			$filter .= " and PS.Person_id = :Person_id";
		}
		$extendFrom = "";
		$extendSelect = "";
		if ((isset($data['EvnDirection_id'])) && (!empty($data['EvnDirection_id']))) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$extendSelect = "
				,ED.EvnDirection_id as \"EvnDirection_id\"
				,ED.EvnDirection_Num as \"EvnDirection_Num\"
				,ED.EvnDirection_setDT as \"EvnDirection_setDT\"
			";
			$extendFrom .= "
				left join lateral(
					SELECT
						ED.EvnDirection_id,
						ED.EvnDirection_Num,
						coalesce(to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy'), '') as EvnDirection_setDT
						
					FROM
						v_EvnDirection_all ED
					WHERE
						ED.EvnDirection_id = :EvnDirection_id
					limit 1
				) ED on true
				";
		}
		
		
		$query = "
			SELECT
				ps.Person_id as \"Person_id\",
				dbo.getPersonPhones(ps.Person_id, ',') as \"Person_Phone\",
				ps.Server_id as \"Server_id\",
				ps.Server_pid as \"Server_pid\",
				CASE WHEN (pcard.PersonCard_endDate IS NOT NULL)
					THEN coalesce(RTRIM(lpu.Lpu_Nick), '') || ' (Прикрепление неактуально. Дата открепления: '||coalesce(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '')||')'
					ELSE coalesce(RTRIM(lpu.Lpu_Nick), '')
				end as \"Lpu_Nick\",
				PersonState.Lpu_id as \"Lpu_id\",
				pcard.PersonCard_id as \"PersonCard_id\",
				coalesce(RTRIM(PS.Person_SurName), '') as \"Person_Surname\",
				coalesce(RTRIM(PS.Person_FirName), '') as \"Person_Firname\",
				coalesce(RTRIM(PS.Person_SecName), '') as \"Person_Secname\",
				coalesce(RTRIM(PS.PersonEvn_id), '') as \"PersonEvn_id\",
				coalesce(to_char(PS.Person_BirthDay, 'dd.mm.yyyy'), '') as \"Person_Birthday\",
				(datediff('year', PS.Person_Birthday, dbo.tzGetDate())
					+ case when month(PS.Person_Birthday) > month(dbo.tzGetDate())
					or (month(PS.Person_Birthday) = month(dbo.tzGetDate()) and day(PS.Person_Birthday) > day(dbo.tzGetDate()))
					then -1 else 0 end) as \"Person_Age\",
				case when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56) then '1' else '0' end as \"KLAreaType_id\",
				coalesce(RTRIM(PS.Person_Snils), '') as \"Person_Snils\",
				coalesce(RTRIM(Sex.Sex_Name), '') as \"Sex_Name\",
				coalesce(RTRIM(Sex.Sex_Code), '') as \"Sex_Code\",
				coalesce(RTRIM(Sex.Sex_id), '') as \"Sex_id\",
				coalesce(RTRIM(SocStatus.SocStatus_Name), '') as \"SocStatus_Name\",
				ps.SocStatus_id as \"SocStatus_id\",
				coalesce(RTRIM(UAddress.Address_Address), '') as \"Person_RAddress\",
				coalesce(RTRIM(PAddress.Address_Address), '') as \"Person_PAddress\",
				coalesce(RTRIM(Document.Document_Num), '') as \"Document_Num\",
				coalesce(RTRIM(Document.Document_Ser), '') as \"Document_Ser\",
				coalesce(to_char(Document.Document_begDate, 'dd.mm.yyyy'), '') as \"Document_begDate\",
				coalesce(RTRIM(DO.Org_Name), '') as \"OrgDep_Name\",
				coalesce(OmsSprTerr.OmsSprTerr_id, 0) as \"OmsSprTerr_id\",
				coalesce(OmsSprTerr.OmsSprTerr_Code, 0) as \"OmsSprTerr_Code\",
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE coalesce(RTRIM(Polis.Polis_Ser), '') END as \"Polis_Ser\",
				CASE WHEN PolisType.PolisType_Code = 4 then coalesce(RTRIM(PersonState.PersonPolisEdNum_EdNum), '') ELSE coalesce(RTRIM(Polis.Polis_Num), '') END as \"Polis_Num\",
				coalesce(to_char(pcard.PersonCard_begDate, 'dd.mm.yyyy'), '') as \"PersonCard_begDate\",
				coalesce(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '') as \"PersonCard_endDate\",
				coalesce(to_char(pcard.LpuRegion_Name, 'dd.mm.yyyy'), '') as \"LpuRegion_Name\",
				coalesce(to_char(Polis.Polis_begDate, 'dd.mm.yyyy'), '') as \"Polis_begDate\",
				coalesce(to_char(Polis.Polis_endDate, 'dd.mm.yyyy'), '') as \"Polis_endDate\",
				coalesce(RTRIM(PO.Org_Name), '') as \"OrgSmo_Name\",
				coalesce(RTRIM(PJ.Org_id), '') as \"JobOrg_id\",
				coalesce(RTRIM(PJ.Org_Name), '') as \"Person_Job\",
				coalesce(RTRIM(PP.Post_Name), '') as \"Person_Post\",
				'' as \"Ident_Lpu\",
				CASE WHEN PR.PersonRefuse_IsRefuse = 2
					THEN 'true' ELSE 'false' END as \"Person_IsRefuse\",
				CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END as \"Person_IsBDZ\",
				CASE WHEN PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0 END as \"Person_IsFedLgot\",
				coalesce(to_char(Person.Person_deadDT, 'dd.mm.yyyy'), '') as \"Person_deadDT\",
				coalesce(to_char(Person.Person_closeDT, 'dd.mm.yyyy'), '') as \"Person_closeDT\",
				Person.Person_IsDead as \"Person_IsDead\",
				Person.PersonCloseCause_id as \"PersonCloseCause_id\",
				0 as \"Children_Count\"
				,PersonPrivilegeFed.PrivilegeType_id as \"PrivilegeType_id\"
				,PersonPrivilegeFed.PrivilegeType_Name as \"PrivilegeType_Name\"
				{$extendSelect}
			FROM {$object} PS
				left join Sex on Sex.Sex_id = PS.Sex_id
				left join SocStatus on SocStatus.SocStatus_id = PS.SocStatus_id
				left join Address UAddress on UAddress.Address_id = PS.UAddress_id
				left join v_KLArea KLArea on KLArea.KLArea_id = UAddress.KLTown_id
				left join Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join v_Job Job on Job.Job_id = PS.Job_id
				left join Org PJ on PJ.Org_id = Job.Org_id
				left join Post PP on PP.Post_id = Job.Post_id
				left join Document on Document.Document_id = PS.Document_id
				left join OrgDep on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org DO on DO.Org_id = OrgDep.Org_id
				left join Polis on Polis.Polis_id = PS.Polis_id
				left join PolisType on PolisType.PolisType_id = Polis.PolisType_id
				left join OmsSprTerr on OmsSprTerr.OmsSprTerr_id = Polis.OmsSprTerr_id
				left join OrgSmo on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org PO on PO.Org_id = OrgSmo.Org_id
				left join Person on Person.Person_id = PS.Person_id
				left join PersonState on PS.Person_id = PersonState.Person_id
				left join lateral(
					SELECT
						PP.Person_id
						,PP.PrivilegeType_id
						,PT.PrivilegeType_Name
					FROM
						v_PersonPrivilege PP
						inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
					WHERE
						PP.PrivilegeType_id <= 150 AND
						PP.PersonPrivilege_begDate <= dbo.tzGetDate() AND
						(PP.PersonPrivilege_endDate IS NULL OR
						PP.PersonPrivilege_endDate >= cast(dbo.tzGetDate() AS date)) AND
						PP.Person_id = PS.Person_id
					limit 1
				) PersonPrivilegeFed on true
				left join lateral(
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc
					where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) pcard on true
				left join v_Lpu lpu on lpu.Lpu_id=PersonState.Lpu_id
				LEFT JOIN v_PersonRefuse PR ON PR.Person_id = ps.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate())
				{$extendFrom}
			WHERE {$filter}
			limit 1
		";
        //echo getDebugSQL($query, $params); exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
}
