<?php

require_once(APPPATH.'models/Common_model.php');

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
				,ED.EvnDirection_id
				,ED.EvnDirection_Num
				,ED.EvnDirection_setDT
			";
			$extendFrom .= "
				OUTER apply
				(SELECT top 1
					ED.EvnDirection_id,
					ED.EvnDirection_Num,
					isnull(convert(varchar(10), ED.EvnDirection_setDT, 104), '') as EvnDirection_setDT
					
				FROM
					v_EvnDirection_all ED WITH (NOLOCK)
				WHERE
					ED.EvnDirection_id = :EvnDirection_id
				) as ED
				";
		}
		
		
		$query = "
			SELECT TOP 1
				ps.Person_id,
				[dbo].[getPersonPhones](ps.Person_id, ',') as Person_Phone,
				ps.Server_id as Server_id,
				ps.Server_pid as Server_pid,
				CASE WHEN (pcard.PersonCard_endDate IS NOT NULL) THEN isnull(RTRIM(lpu.Lpu_Nick), '') + ' (Прикрепление неактуально. Дата открепления: '+isnull(convert(varchar(10), pcard.PersonCard_endDate, 104), '')+')' ELSE isnull(RTRIM(lpu.Lpu_Nick), '') end as Lpu_Nick,
				PersonState.Lpu_id as Lpu_id,
				pcard.PersonCard_id,
				isnull(RTRIM(PS.Person_SurName), '') as Person_Surname,
				isnull(RTRIM(PS.Person_FirName), '') as Person_Firname,
				isnull(RTRIM(PS.Person_SecName), '') as Person_Secname,
				isnull(RTRIM(PS.PersonEvn_id), '') as PersonEvn_id,
				isnull(convert(varchar(10), PS.Person_BirthDay, 104), '') as Person_Birthday,
				(datediff(year, PS.Person_Birthday, dbo.tzGetDate())
					+ case when month(PS.Person_Birthday) > month(dbo.tzGetDate())
					or (month(PS.Person_Birthday) = month(dbo.tzGetDate()) and day(PS.Person_Birthday) > day(dbo.tzGetDate()))
					then -1 else 0 end) as Person_Age,
				case when (KLArea.KLSocr_id = 68) or (KLArea.KLSocr_id = 56) then '1' else '0' end as KLAreaType_id,
				isnull(RTRIM(PS.Person_Snils), '') as Person_Snils,
				isnull(RTRIM(Sex.Sex_Name), '') as Sex_Name,
				isnull(RTRIM(Sex.Sex_Code), '') as Sex_Code,
				isnull(RTRIM(Sex.Sex_id), '') as Sex_id,
				isnull(RTRIM(SocStatus.SocStatus_Name), '') as SocStatus_Name,
				ps.SocStatus_id,
				isnull(RTRIM(UAddress.Address_Address), '') as Person_RAddress,
				isnull(RTRIM(PAddress.Address_Address), '') as Person_PAddress,
				isnull(RTRIM(Document.Document_Num), '') as Document_Num,
				isnull(RTRIM(Document.Document_Ser), '') as Document_Ser,
				isnull(convert(varchar(10), Document.Document_begDate, 104), '') as Document_begDate,
				isnull(RTRIM(DO.Org_Name), '') as OrgDep_Name,
				isnull(OmsSprTerr.OmsSprTerr_id, 0) as OmsSprTerr_id,
				isnull(OmsSprTerr.OmsSprTerr_Code, 0) as OmsSprTerr_Code,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE isnull(RTRIM(Polis.Polis_Ser), '') END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(PersonState.PersonPolisEdNum_EdNum), '') ELSE isnull(RTRIM(Polis.Polis_Num), '') END AS Polis_Num,
				isnull(convert(varchar(10), pcard.PersonCard_begDate, 104), '') as PersonCard_begDate,
				isnull(convert(varchar(10), pcard.PersonCard_endDate, 104), '') as PersonCard_endDate,
				isnull(convert(varchar(10), pcard.LpuRegion_Name, 104), '') as LpuRegion_Name,
				isnull(convert(varchar(10), Polis.Polis_begDate, 104), '') as Polis_begDate,
				isnull(convert(varchar(10), Polis.Polis_endDate, 104), '') as Polis_endDate,
				isnull(RTRIM(PO.Org_Name), '') as OrgSmo_Name,
				isnull(RTRIM(PJ.Org_id), '') as JobOrg_id,
				isnull(RTRIM(PJ.Org_Name), '') as Person_Job,
				isnull(RTRIM(PP.Post_Name), '') as Person_Post,
				'' as Ident_Lpu,
				CASE WHEN PR.PersonRefuse_IsRefuse = 2
					THEN 'true' ELSE 'false' END as Person_IsRefuse,
				/* -- в v_Person_all (reg) нет этих полей, надо Тарасу сказать чтобы добавил 
				isnull(convert(varchar(10), ps.Person_deadDT, 104), '') as Person_deadDT,
				isnull(convert(varchar(10), ps.Person_closeDT, 104), '') as Person_closeDT,
				ps.Person_IsDead,
				ps.PersonCloseCause_id
				*/
				CASE WHEN PS.Server_pid = 0 THEN 1 ELSE 0 END AS Person_IsBDZ,
				CASE WHEN PersonPrivilegeFed.Person_id IS NOT NULL THEN 1 ELSE 0 END AS Person_IsFedLgot,
				isnull(convert(varchar(10), Person.Person_deadDT, 104), '') as Person_deadDT,
				isnull(convert(varchar(10), Person.Person_closeDT, 104), '') as Person_closeDT,
				Person.Person_IsDead,
				Person.PersonCloseCause_id,
				0 as Children_Count
				,PersonPrivilegeFed.PrivilegeType_id
				,PersonPrivilegeFed.PrivilegeType_Name
				{$extendSelect}
			FROM [{$object}] [PS] WITH (NOLOCK)
				left join [Sex] WITH (NOLOCK) on [Sex].[Sex_id] = [PS].[Sex_id]
				left join [SocStatus] WITH (NOLOCK) on [SocStatus].[SocStatus_id] = [PS].[SocStatus_id]
				left join [Address] [UAddress] WITH (NOLOCK) on [UAddress].[Address_id] = [PS].[UAddress_id]
				left join [v_KLArea] [KLArea] WITH (NOLOCK) on [KLArea].[KLArea_id] = [UAddress].[KLTown_id]
				left join [Address] [PAddress] WITH (NOLOCK) on [PAddress].[Address_id] = [PS].[PAddress_id]
				left join [v_Job] [Job] WITH (NOLOCK) on [Job].[Job_id] = [PS].[Job_id]
				left join [Org] [PJ] WITH (NOLOCK) on [PJ].[Org_id] = [Job].[Org_id]
				left join [Post] [PP] WITH (NOLOCK) on [PP].[Post_id] = [Job].[Post_id]
				left join [Document] WITH (NOLOCK) on [Document].[Document_id] = [PS].[Document_id]
				left join [OrgDep] WITH (NOLOCK) on [OrgDep].[OrgDep_id] = [Document].[OrgDep_id]
				left join [Org] [DO] WITH (NOLOCK) on [DO].[Org_id] = [OrgDep].[Org_id]
				left join [Polis] WITH (NOLOCK) on [Polis].[Polis_id] = [PS].[Polis_id]
				left join [PolisType] WITH (NOLOCK) on [PolisType].[PolisType_id] = [Polis].[PolisType_id]
				left join [OmsSprTerr] WITH (NOLOCK) on [OmsSprTerr].[OmsSprTerr_id] = [Polis].[OmsSprTerr_id]
				left join [OrgSmo] WITH (NOLOCK) on [OrgSmo].[OrgSmo_id] = [Polis].[OrgSmo_id]
				left join [Org] [PO] WITH (NOLOCK) on [PO].[Org_id] = [OrgSmo].[Org_id]
				left join [Person] WITH (NOLOCK) on [Person].[Person_id] = [PS].[Person_id]
				left join [PersonState] with (nolock) on [PS].[Person_id] = [PersonState].[Person_id]
				OUTER apply
				(SELECT top 1
					PP.Person_id
					,PP.PrivilegeType_id
					,PT.PrivilegeType_Name
				FROM
					v_PersonPrivilege PP WITH (NOLOCK)
					inner join v_PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
				WHERE
					PP.PrivilegeType_id <= 150 AND
					PP.PersonPrivilege_begDate <= dbo.tzGetDate() AND
					(PP.PersonPrivilege_endDate IS NULL OR
					PP.PersonPrivilege_endDate >= cast(dbo.tzGetDate() AS date)) AND
					PP.Person_id = PS.Person_id
				) PersonPrivilegeFed
				outer apply (select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc WITH (NOLOCK)
					where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					) as pcard 
				left join v_Lpu lpu WITH (NOLOCK) on lpu.Lpu_id=PersonState.Lpu_id
				LEFT JOIN v_PersonRefuse PR WITH (NOLOCK) ON PR.Person_id = ps.Person_id and PR.PersonRefuse_IsRefuse = 2 and PR.PersonRefuse_Year = YEAR(dbo.tzGetDate())
				{$extendFrom}
			WHERE {$filter}
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

