<?php

class Polka_PersonCard_model_get
{
	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getOldPersonCard(Polka_PersonCard_model $callObject, $data)
	{
		$query = "						
			select 
				Lpu_id as \"Lpu_id\",
				rtrim(coalesce(Person_SurName, ''))||' '||rtrim(coalesce(Person_FirName, ''))||' '||rtrim(coalesce(Person_SecName, '')) as \"Person_FIO\"
			from v_PersonCard 
			where Person_id = :Person_id
			order by PersonCard_begDate desc
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result("array") : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getCountAttachPersonInLpu(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
			select COUNT(distinct PersonCard_id) as \"countAttachment\"
			from v_PersonCard 
			where Lpu_id = :Lpu_id
			  and PersonCard_begDate < tzgetdate()
			  and PersonCard_begDate is not null
			  and PersonCard_endDate is null
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return $result[0]["countAttachment"];
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getCountDetachPersonInLpu(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
			select COUNT(distinct PersonCard_id) as \"countDetachment\"
			from v_PersonCard 
			where Lpu_id = :Lpu_id
			  and PersonCard_endDate < tzgetdate()
			  and PersonCard_endDate is not null
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return $result[0]["countDetachment"];
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonCardHistoryList(Polka_PersonCard_model $callObject, $data)
	{
		$queryParams = [
			"Lpu_id" => $data["Lpu_id"],
			"Person_id" => $data["Person_id"],
			"gdate" => $callObject->tzGetDate()
		];
		$attach_type_filter = "";
		switch ($data["AttachType"]) {
			case "common_region":
				$attach_type_filter = " and pc.LpuAttachType_id = 1 ";
				break;
			case "ginecol_region":
				$attach_type_filter = " and pc.LpuAttachType_id = 2 ";
				break;
			case "stomat_region":
				$attach_type_filter = " and pc.LpuAttachType_id = 3 ";
				break;
			case "service_region":
				$attach_type_filter = " and pc.LpuAttachType_id = 4 ";
				if (!isSuperadmin())
					$attach_type_filter .= " and pc.Lpu_id = :Lpu_id ";
				break;
			case "dms_region":
				$attach_type_filter = " and pc.LpuAttachType_id = 5 ";
				break;
		}
		$sql = "			
			select
				pc.Person_id as \"Person_id\",
				pc.PersonCard_id as \"PersonCard_id\",
				pc.PersonCard_Code as \"PersonCard_Code\",
				pc.Server_id as \"Server_id\",
				to_char(pc.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
				pc.PersonCard_begDate as \"sort\",
				to_char(pc.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
				rtrim(pc.LpuRegionType_Name) as \"LpuRegionType_Name\",
				ccc.CardCloseCause_id as \"CardCloseCause_id\",
				ccc.CardCloseCause_SysNick as \"CardCloseCause_SysNick\",
				rtrim(ccc.CardCloseCause_Name) as \"CardCloseCause_Name\",
				rtrim(pc.LpuRegion_Name) as \"LpuRegion_Name\",
				rtrim(pc.LpuRegion_FapName) as \"LpuRegion_FapName\",
				lpu.Lpu_id as \"Lpu_id\",
				rtrim(lpu.Lpu_Nick) as \"Lpu_Nick\",
				PACLT.AmbulatCardLocatType_Name as \"AmbulatCardLocatType_Name\",
				PACLT.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				case when coalesce(pc.PersonCard_IsAttachCondit, 1) = 1 then 'false' else 'true' end as \"PersonCard_IsAttachCondit\",
				case when PCard.PersonCardAttach_id is null then 'false' else 'true' end as \"PersonCardAttach\",
				case when lpu.Lpu_id = :Lpu_id then 'true' else 'false' end as \"Is_OurLpu\",
				case when ps.Server_pid = 0	then 'true' else 'false' end as \"Person_IsBDZ\",
				case when fedl.Person_id is not null then 'true' else 'false' end as \"Person_IsFedLgot\", 
				case when exists (
					select PersonCard_id
					from v_PersonCard 
					where Person_id = ps.Person_id
					  and LpuAttachType_id = 5
					  and PersonCard_endDate >= :gdate
					  and CardCloseCause_id is null
				) then 'true' else 'false' end as \"PersonCard_IsDmsForCheck\",
				case when exists (
					select PersonCard_id
					from v_PersonCard 
					where Person_id = ps.Person_id
					  and LpuAttachType_id = 5
					  and pc.LpuAttachType_id = 1						
					  and to_char(pc.PersonCard_begDate, '{$callObject->dateTimeFormUnixDate}') = to_char(PersonCard_begDate, '{$callObject->dateTimeFormUnixDate}')
				) or pc.LpuAttachType_id = 5 then 'true' else 'false' end as \"PersonCard_IsDms\",
				case when (
					select PersonCardAttach_id
					from PersonCard 
					where PersonCard_id = pc.PersonCard_id
					  and Person_id = pc.Person_id
					  and Lpu_id = pc.Lpu_id
					  and LpuAttachType_id = pc.LpuAttachType_id
					  and (PersonCard_IsAttachCondit = 1 or PersonCard_IsAttachCondit is null)
				) is not null THEN 'true' ELSE 'false' END as \"isPersonCardAttach\",
				case when pl.Polis_begDate <= :gdate and (coalesce(pl.Polis_endDate, :gdate) > :gdate) then 'true' else 'false' end as \"Person_HasPolis\",
				case when exists (
					select PersonCard_id
					from v_PersonCard 
					where Person_id = ps.Person_id
					  and LpuAttachType_id = 5
					  and PersonCard_endDate >= :gdate
					  and CardCloseCause_id is null
					  and Lpu_id != :Lpu_id
				) then 'true' else 'false' end as \"Person_HasDmsOtherLpu\"
				,to_char(PCA.PersonCardAttach_insDT, '{$callObject->dateTimeForm104}') as \"PersonCardAttach_insDT\"
			from
				v_PersonCard_all pc 
				inner join v_PersonState ps on ps.Person_id = pc.Person_id
				left join PersonCard PCard on PCard.PersonCard_id = pc.PersonCard_id
				left join v_Lpu lpu on pc.Lpu_id=lpu.Lpu_id
				left join v_Polis pl on pl.Polis_id = ps.Polis_id
				left join v_CardCloseCause ccc on ccc.CardCloseCause_id=pc.CardCloseCause_id	
				left join v_PersonCardAttach PCA on PCA.PersonCardAttach_id=pc.PersonCardAttach_id
				left join lateral (
					select
						ACLT.AmbulatCardLocatType_Name,
					    PACL.PersonAmbulatCard_id
					from
						v_PersonAmbulatCardLocat PACL 
						left join AmbulatCardLocatType ACLT on ACLT.AmbulatCardLocatType_id = PACL.AmbulatCardLocatType_id
						left join v_PersonAmbulatCardLink PACLink on PACLink.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
					where pc.PersonCard_id=PACLink.PersonCard_id
					order by PACL.PersonAmbulatCardLocat_begDate desc
					limit 1
				) as PACLT on true
				left join lateral (
						select Person_id
						from
							v_personprivilege pp 
							inner join v_PrivilegeType t2 on t2.PrivilegeType_id = pp.PrivilegeType_id
						where pp.person_id = ps.person_id
						  and t2.ReceptFinance_id = 1
						  and pp.personprivilege_begdate <= :gdate
						  and coalesce(pp.personprivilege_enddate, :gdate) >= :gdate
						limit 1
				) as fedl on true
			where pc.Person_id = :Person_id {$attach_type_filter}
			order by sort
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getLpuAgeType(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
			select coalesce(MesAgeLpuType_id, 3) as \"MesAgeLpuType_id\"
			from v_Lpu 
			where Lpu_id = :Lpu_id
			limit 1
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (count($result) > 0) ? $result[0]["MesAgeLpuType_id"] : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getLpuRegionType(Polka_PersonCard_model $callObject, $data)
	{
		if (empty($data["LpuRegion_id"])) {
			return false;
		}
		$query = "
			select LpuRegionType_id as \"LpuRegionType_id\"
			from v_LpuRegion 
			where LpuRegion_id = :LpuRegion_id
			limit 1
		";
		$queryParams = ["LpuRegion_id" => $data["LpuRegion_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result("array");
		return (count($result) > 0) ? $result[0]["LpuRegionType_id"] : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMedCard(Polka_PersonCard_model $callObject, $data)
	{
		$filterList = [];
		$queryParams = ["Lpu_id" => $data['Lpu_id']];
		if (!empty($data["Person_id"])) {
			$filterList[] = "PS.Person_id = :Person_id";
			$queryParams["Person_id"] = $data["Person_id"];
		}
		if (!empty($data["PersonCard_id"])) {
			$filterList[] = "PC.PersonCard_id = :PersonCard_id";
			$queryParams["PersonCard_id"] = $data["PersonCard_id"];
		}
		$whereString = (count($filterList) != 0) ? "where " . implode(" and ", $filterList) : "";
		$sql = "
			select 
				PS.Person_id as \"Person_id\", 
				PC.PersonCard_id as \"PersonCard_id\", 
				PC.Lpu_id as \"Lpu_id\", 
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				rtrim(COALESCE(PC.LpuRegion_Name, '')) as \"LpuRegion_Name\",
				LpuAttachType.LpuAttachType_Name as \"LpuAttachType_Name\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				COALESCE(E.Ethnos_Name,'') as \"Ethnos_Name\",
				PS.Person_SurName as \"Person_SurName\", 
				PS.Person_FirName as \"Person_FirName\", 
				PS.Person_SecName as \"Person_SecName\",
				coalesce(PS.Person_Inn, '') as \"Person_Inn\",
				Sex.Sex_Name as \"Sex_Name\",
				case when Sex.Sex_Code = 3 then 1 else Sex.Sex_Code end as \"Sex_Code\",
				case when PC.Lpu_id = :Lpu_id then PC.PersonCard_Code else null end as \"PersonCard_Code\",
                LpuCur.Lpu_Name as \"Lpu_Name\",
                LpuCur.Lpu_OGRN as \"Lpu_OGRN\",
                OrgCur.Org_OKPO as \"Org_OKPO\",
                AdrCur.Address_Address as \"Address_Address\",
				PS.Person_Snils as \"Person_Snils\", 
				OrgSmo.OrgSMO_Nick as \"OrgSMO_Nick\",
				case when Polis.PolisType_id = 4 then '' else PS.Polis_Ser end as \"Polis_Ser\",
				case when Polis.PolisType_id = 4 then PS.Person_EdNum else PS.Polis_Num end as \"Polis_Num\",
				PAdr.Address_Address as \"PAddress_Address\",
				UAdr.Address_Address as \"UAddress_Address\",
				KLAType.KLAreaType_Code::varchar || ': ' || KLAType.KLAreaType_Name as \"KLAreaType_Name\",
				DocumentType.DocumentType_Name as \"DocumentType_Name\", 
				PS.Document_Num as \"Document_Num\", 
				PS.Document_Ser as \"Document_Ser\",
				PS.Person_Phone as \"Person_Phone\",
				job.Org_Name as \"Job_Name\",
				OrgUnion.OrgUnion_Name as \"OrgUnion_Name\",
				Post.Post_Name as \"Post_Name\",
				EU.EvnUdost_Ser as \"EvnUdost_Ser\",
				EU.EvnUdost_Num as \"EvnUdost_Num\",
				to_char(EU.EvnUdost_setDate, '{$callObject->dateTimeForm104}') as \"EvnUdost_Date\",
				SSt.SocStatus_Name as \"SocStatus_Name\",
				InvalidGroupType.InvalidGroupType_Name as \"InvalidGroupType_Name\"
			from
				v_PersonState PS 
				left join v_PersonCard_all PC on PS.Person_id = PC.Person_id
				left join v_PersonInfo PI on PI.Person_id = PS.Person_id
				left join v_Ethnos E on E.Ethnos_id = PI.Ethnos_id
				left join v_PersonJob pjob on PS.Job_id = pjob.Job_id
				left join v_Org job on job.Org_id = pjob.Org_id
				left join v_OrgUnion OrgUnion on OrgUnion.OrgUnion_id = pjob.OrgUnion_id
				left join v_Post Post on Post.Post_id = pjob.Post_id
				left join v_EvnUdost EU on EU.Person_id = PC.Person_id
				left join v_LpuAttachType LpuAttachType on LpuAttachType.LpuAttachType_id = PC.LpuAttachType_id
				left join v_Polis Polis on PS.Polis_id = Polis.Polis_id
				left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
				left join v_Document Document on PS.Document_id = Document.Document_id
				left join v_DocumentType DocumentType on Document.DocumentType_id = DocumentType.DocumentType_id
				left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
				left join v_Org Org on Org.Org_id = Lpu.Org_id
				left join v_Lpu LpuCur on LpuCur.Lpu_id = :Lpu_id
				left join v_Org OrgCur on OrgCur.Org_id = LpuCur.Org_id
				left join v_Address Adr on Adr.Address_id = COALESCE(Org.UAddress_id, Org.PAddress_id)
				left join v_Address AdrCur on AdrCur.Address_id = COALESCE(OrgCur.UAddress_id, OrgCur.PAddress_id)
				left join v_Address PAdr on PAdr.Address_id = PS.PAddress_id
				left join v_Address UAdr on UAdr.Address_id = PS.UAddress_id
				left join v_KLAreaType KLAType on KLAType.KLAreaType_id = PAdr.KLAreaType_id
				left join v_SocStatus SSt on SSt.SocStatus_id = PS.SocStatus_id
				LEFT JOIN LATERAL(
					select IGT.InvalidGroupType_Code, IGT.InvalidGroupType_Name
					from v_EvnMse EM 
						inner join v_InvalidGroupType IGT on IGT.InvalidGroupType_id = EM.InvalidGroupType_id
					where EM.PersonEvn_id = PS.PersonEvn_id
					order by EM.EvnMse_setDT
					limit 1
				) as InvalidGroupType on true
			{$whereString}
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$res = $result->result_array();
		if (is_array($res) && count($res) > 0) {
			$sql = "
				select coalesce(PT.PrivilegeType_VCode, PT.PrivilegeType_Code::varchar) as \"PrivilegeType_Code\"
				from
					v_PersonPrivilege PP 
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
				where PP.Person_id = :Person_id
				  and PT.ReceptFinance_id = 1
				order by PP.PrivilegeType_Code
			";
			$sqlParams = ["Person_id" => $res[0]["Person_id"]];
			$result = $callObject->db->query($sql, $sqlParams);
			if (is_object($result)) {
				$code = $result->result_array();
				$codes = [];
				foreach ($code as $c) {
					$codes[] = $c["PrivilegeType_Code"];
				}
				$res = array_merge($res, ["1" => $codes]);
			}
		}
		return $res;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool|false
	 */
	public static function getPersonCard(Polka_PersonCard_model $callObject, $data)
	{
		$filter = isSuperadmin() ? "(1 = 1)" : "(1 = 0)";
		$sql = "
			select
				case when {$filter} or pall.Lpu_id = :Lpu_id then 'edit' else 'view' end as \"accessType\",
				pall.PersonCard_id as \"PersonCard_id\",
				rtrim(rtrim(pall.PersonCard_Code)) as \"PersonCard_Code\",
				pall.Person_id as \"Person_id\",
				pall.LpuAttachType_id as \"LpuAttachType_id\",
				pall.PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
				pall.LpuRegionType_id as \"LpuRegionType_id\",
   				to_char(pall.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
   				to_char(pall.PersonCardBeg_insDT, '{$callObject->dateTimeForm104}') as \"PersonCardBeg_insDT\",
   				to_char(pall.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
   				to_char(pall.PersonCardEnd_insDT, '{$callObject->dateTimeForm104}') as \"PersonCardEnd_insDT\",
				pall.CardCloseCause_id as \"CardCloseCause_id\",
				pall.Lpu_id as \"Lpu_id\",
				pall.LpuRegion_id as \"LpuRegion_id\",
				pc.MedStaffFact_id as \"MedStaffFact_id\",
				pall.LpuRegion_fapid as \"LpuRegion_Fapid\",
				pall.PersonCard_DmsPolisNum as \"PersonCard_DmsPolisNum\",
				to_char(pall.PersonCard_DmsBegDate, '{$callObject->dateTimeForm104}') as \"PersonCard_DmsBegDate\",
				to_char(pall.PersonCard_DmsEndDate, '{$callObject->dateTimeForm104}') as \"PersonCard_DmsEndDate\",
				pall.OrgSMO_id as \"OrgSMO_id\",
				pc.PersonCardAttach_id as \"PersonCardAttach_id\",
				PACLink.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				PCA.PersonCardAttach_IsSMS-1 as \"PersonCardAttach_IsSMS\",
				case when PCA.PersonCardAttach_SMS is null
				    then null
				    else '+7 '||substring(PCA.PersonCardAttach_SMS, 1, 3)||' '||substring(PCA.PersonCardAttach_SMS, 4, 10)
				end as \"PersonCardAttach_SMS\",
				PCA.PersonCardAttach_IsEmail-1 as \"PersonCardAttach_IsEmail\",
				PCA.PersonCardAttach_Email as \"PersonCardAttach_Email\",
				coalesce(pc.PersonCard_IsAttachAuto::varchar, '') as \"PersonCard_IsAttachAuto\"
			from
				v_PersonCard_all pall 
				left join lateral (
					select 
						PersonCardAttach_id,
						MedStaffFact_id,
						PersonCard_IsAttachAuto
					from PersonCard 
					where PersonCard_id = pall.PersonCard_id
					  and Person_id = pall.Person_id
					  and Lpu_id = pall.Lpu_id
					  and LpuAttachType_id = pall.LpuAttachType_id
					limit 1
				) as pc on true
				left join v_PersonCardAttach PCA on PCA.PersonCardAttach_id = pc.PersonCardAttach_id
				left join lateral (
					select pac.PersonAmbulatCard_id
					from
						v_PersonAmbulatCard pac 
						left join v_PersonAmbulatCardLink PACLink on PACLink.PersonAmbulatCard_id = pac.PersonAmbulatCard_id
					where PACLink.PersonCard_id = pall.PersonCard_id
					limit 1
				) as PACLink on true
			where pall.PersonCard_id = :PersonCard_id
		";
		$params = ["PersonCard_id" => $data["PersonCard_id"], "Lpu_id" => $data["Lpu_id"]];
		$result = $callObject->queryResult($sql, $params);
		if (!is_array($result)) {
			return false;
		}
		if (count($result) > 0) {
			$data["PersonCardAttach_id"] = 0;
			if (isset($result[0]["PersonCardAttach_id"]) && !empty($result[0]["PersonCardAttach_id"])) {
				$data["PersonCardAttach_id"] = $result[0]["PersonCardAttach_id"];
			}
			$result[0]["files"] = $callObject->getFilesOnPersonCardAttach($data);
		}
		return $result;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 */
	public static function getLpuRegion(Polka_PersonCard_model $callObject, $data)
	{
		$sql = "
			select
        		LpuRegion_id as \"LpuRegion_id\",
        		Lpu_id as \"Lpu_id\"
        	from xp_PersonAttach(
                	Person_id := :Person_id,
					LpuAttachType_id := :LpuAttachType_id
        		)
        ";
		$sqlParams = [
			"Person_id" => $data["Person_id"],
			"LpuAttachType_id" => $data["LpuAttachType_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return $result;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLpuRegionByAddress(Polka_PersonCard_model $callObject, $data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"Lpu_id" => $data["Lpu_id"],
			"LpuRegionType_id" => $data["LpuRegionType_id"]
		];
		$queryAddress = "
			select LR.LpuRegion_id as \"LpuRegion_id\" 
			from
				v_LpuRegion LR
				inner join LpuRegionStreet LRS on LRS.LpuRegion_id = LR.LpuRegion_id
				LEFT JOIN LATERAL(
					select
						A.KLStreet_id,
					    A.Address_House,
					    A.Address_Corpus
					from
						v_PersonState PS
						inner join Address A on A.Address_id = PS.PAddress_id
					where PS.Person_id = :Person_id
				) as PersonAddress on true
			where LR.Lpu_id = :Lpu_id
			  and LR.LpuRegionType_id = :LpuRegionType_id
			  and LRS.KLStreet_id = PersonAddress.KLStreet_id
			  and dbo.GetHouse(
			      coalesce(LRS.LpuRegionStreet_HouseSet, '  '),
			      trim(PersonAddress.Address_House)||(case when PersonAddress.Address_Corpus is not null then '/'||trim(PersonAddress.Address_Corpus) else '' end)
			  ) = 1
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($queryAddress, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getFilesOnPersonCardAttach(Polka_PersonCard_model $callObject, $data)
	{
		$query = "			
			select
				pmMediaData_id as \"pmMediaData_id\",
				pmMediaData_ObjectID as \"PersonCardAttach_id\",
				pmMediaData_FileName as \"name\",
				pmMediaData_FilePath as \"url\",
				pmMediaData_FilePath as \"tmp_name\",
				pmMediaData_Comment as \"sizeinfo\"
			from pmMediaData 
			where pmMediaData_ObjectID = :pmMediaData_ObjectID
			union
			select
				pmMediaData_id as \"pmMediaData_id\",
				pmMediaData_ObjectID as \"PersonCardAttach_id\",
				pmMediaData_FileName as \"name\",
				pmMediaData_FilePath as \"url\",
				pmMediaData_FilePath as \"tmp_name\",
				pmMediaData_Comment as \"sizeinfo\"
			from pmMediaData
			where pmMediaData_ObjectID = :pmMediaData_ObjectIDPCard
		";
		$queryParams = [
			"pmMediaData_ObjectID" => $data["PersonCardAttach_id"],
			"pmMediaData_ObjectIDPCard" => $data["PersonCard_id"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getPersonCardCode(Polka_PersonCard_model $callObject, $data)
	{
		$regionsNotFond = ["ufa", "kareliya", "astra"];
		$regionNick = $callObject->getRegionNick();
		if ((!$regionNick) || (!in_array($regionNick, $regionsNotFond))) {
			// проверяем, можем ли мы прикрепить человека к этому ЛПУ по признаку фондододержания
			if (isset($data['CheckFond'])) {
				$sql = "
					select Person_Age as \"Person_Age\"
					from
						v_PersonState_all Person 
						inner join v_Polis Polis on Person.Polis_id = Polis.Polis_id
							and (Polis.Polis_begDate < dbo.tzGetDate() and (Polis.Polis_endDate is null or Polis.Polis_endDate > dbo.tzGetDate()))
					where Person_id = :Person_id
					  and Person_IsBDZ = 1
				";
				$sqlParams = ["Person_id" => $data["Person_id"]];
				/**@var CI_DB_result $res */
				$res = $callObject->db->query($sql, $sqlParams);
				if (is_object($res)) {
					$sel = $res->result("array");
					if (count($sel) > 0) {
						$age = $sel[0]["Person_Age"];
						$sql = "
							select LpuRegionType_id as \"LpuRegionType_id\"
							from v_LpuPeriodFondHolder
							where Lpu_id = :Lpu_id
							  and coalesce(LpuRegionType_id, 1) in (1, 2)
							  and LpuPeriodFondHolder_begDate <= tzgetdate()
							  and (LpuPeriodFondHolder_endDate is null or LpuPeriodFondHolder_endDate > tzgetdate())
						";
						$sqlParams = ["Lpu_id" => $data["Lpu_id"]];
						$res = $callObject->db->query($sql, $sqlParams);
						if (!is_object($res)) {
							throw new Exception("Ошибка при выполнении запроса к базе данных (проверка фондодержания).");
						}
						$sel = $res->result("array");
						if (!is_array($sel) || count($sel) == 0) {
							throw new Exception("Человек не может быть прикреплен к данному ЛПУ, так как ЛПУ не является фондодержателем.");
						}
						$all = false;
						$child = false;
						$old = false;
						foreach ($sel as $val) {
							if (empty($val["LpuRegionType_id"])) {
								$all = true;
							} else if ($val["LpuRegionType_id"] == 1) {
								$old = true;
							} else if ($val["LpuRegionType_id"] == 2) {
								$child = true;
							}
						}
						if ($all === false) {
							if ($age >= 18 && !$old && $child) {
								throw new Exception("Взрослый человек не может быть прикреплен к данному ЛПУ, так как ЛПУ является фондодержателем детского населения.");
							}
							if ($age < 18 && !$child && $old) {
								throw new Exception("Человек до 18 лет не может быть прикреплен к данному ЛПУ, так как ЛПУ является фондодержателем взрослого населения.");
							}
							if (!($age >= 18 && $old) && !($age < 18 && $child)) {
								throw new Exception("Человек не может быть прикреплен к данному ЛПУ, так как ЛПУ не является фондодержателем.");
							}
						}
					}
				}
			}
		}
		$sql = "
	        select objectid as \"PersonCard_Code\"
	        from xp_GenpmID(
	          ObjectName => 'PersonCard', 
			  Lpu_id => :Lpu_id
	        )
        ";
		$sqlParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		$personcard_result = $result->result_array();
		$personcard_result[0]["success"] = true;
		return $personcard_result;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonCardStateGrid(Polka_PersonCard_model $callObject, $data)
	{
		$filters = ["pc.Lpu_id = :Lpu_id"];
		$queryParams = [
			"begDate" => $data["Period"][0],
			"endDate" => $data["Period"][1],
			"FromLpu_id" => (!empty($data["FromLpu_id"]) && $data["FromLpu_id"] > 0 ? $data["FromLpu_id"] : null),
			"Lpu_id" => $data["Lpu_id"],
			"LpuAttachType_id" => (!empty($data["LpuAttachType_id"]) ? $data["LpuAttachType_id"] : null),
			"LpuRegion_id" => (!empty($data["LpuRegion_id"]) && $data["LpuRegion_id"] > 0 ? $data["LpuRegion_id"] : null),
			"LpuRegionType_id" => (!empty($data["LpuRegionType_id"]) ? $data["LpuRegionType_id"] : null),
			"ToLpu_id" => (!empty($data["ToLpu_id"]) && $data["ToLpu_id"] > 0 ? $data["ToLpu_id"] : null)
		];
		if (!empty($data['LpuRegionType_id']) && is_numeric($data['LpuRegionType_id'])) {
			$filters[] = "lr.LpuRegionType_id = :LpuRegionType_id";
		}
		if (!empty($data["LpuRegion_id"])) {
			$filters[] = ($data["LpuRegion_id"] == -1) ? "lr.LpuRegion_id is null" : "lr.LpuRegion_id = :LpuRegion_id";
		}
		if (!empty($data["LpuAttachType_id"])) {
			$filters[] = "pc.LpuAttachType_id = :LpuAttachType_id";
		}
		if (!empty($queryParams["begDate"])) {
			$filters[] = "(pc.PersonCard_begDate is null or pc.PersonCard_begDate::date <= :endDate)";
		}
		if (!empty($queryParams["endDate"])) {
			$filters[] = "(pc.PersonCard_endDate is null or pc.PersonCard_endDate::date >= :begDate)";
		}
		$select_part = "
			case when lr.LpuRegion_Name is null then 'Без участка' else lr.LpuRegion_Name end as \"LpuRegion_Name\",
            case when lr.LpuRegionType_Name is null then 'Без участка' else lr.LpuRegionType_Name end as \"LpuRegionType_Name\",
			lr.LpuRegion_id as \"LpuRegion_id\",
			to_char(:begDate::date, '{$callObject->dateTimeForm104}') as \"StartDate\",
			to_char(:endDate::date, '{$callObject->dateTimeForm104}') as \"EndDate\",
			count(distinct case when pc.PersonCard_begDate::date < :begDate
				and (spc.PersonCard_endDate is null or spc.PersonCard_endDate::date >= :begDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null) or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				) THEN pc.PersonCard_id END
			) as \"BegCount\",
			count(distinct case when pc.PersonCard_begDate::date < :begDate
				and (spc.PersonCard_endDate is null or spc.PersonCard_endDate::date >= :begDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null) or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				)
				and PolisBeg.Polis_id is not null THEN pc.PersonCard_id END
			) as \"BegCountBDZ\",			
			count(distinct case when pc.PersonCard_begDate::date < :begDate
				and (spc.PersonCard_endDate is null or spc.PersonCard_endDate::date >= :begDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null) or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				)
				and PolisBeg.Polis_id is null THEN pc.PersonCard_id END
			) as \"BegCountNotInBDZ\",
			count(distinct case when pc.PersonCard_begDate::date <= :endDate
				and (spc.PersonCard_endDate is null or spc.PersonCard_endDate::date > :endDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null) or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				) THEN pc.PersonCard_id END
			) as \"EndCount\",
			count(distinct case when pc.PersonCard_begDate::date <= :endDate
				and (spc.PersonCard_endDate is null or spc.PersonCard_endDate::date > :endDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null) or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				)
				and PolisEnd.Polis_id is not null THEN pc.PersonCard_id END
			) as \"EndCountBDZ\",
			count(distinct case when pc.PersonCard_begDate::date <= :endDate
				and (spc.PersonCard_endDate is null or spc.PersonCard_endDate::date > :endDate)
				and ((pc.LpuRegion_id is null and lr.LpuRegion_id is null) or (pc.LpuRegion_id is not null and lr.LpuRegion_id is not null)
				)
				and PolisEnd.Polis_id is null THEN pc.PersonCard_id END
			) as \"EndCountNotInBDZ\"
		";
		$addit_filter = "";
		if (isset($data['FromLpu_id']) && (int)$data['FromLpu_id'] > 0) {
			$addit_filter .= " and LastCard.Lpu_id is not null and LastCard.Lpu_id = :FromLpu_id ";
		}
		if (isset($data['ToLpu_id']) && (int)$data['ToLpu_id'] > 0) {
			$addit_filter .= " and NextCard.Lpu_id is not null and NextCard.Lpu_id = :ToLpu_id ";
		}
		if ($data['LpuMotion_id'] == 2) {
			$addit_filter .= " and LastCard.Lpu_id is not null and LastCard.Lpu_id != pc.Lpu_id ";
		} else if ($data['LpuMotion_id'] == 3) {
			$addit_filter .= " and LastCard.Lpu_id is not null and LastCard.Lpu_id = pc.Lpu_id ";
		}
		$whereString = (count($filters) != 0) ? "where " . implode(" and ", $filters) : "";
		$sql = "
			with SelLpuRegions(LpuRegion_id, LpuRegionType_id, LpuRegion_Name, LpuRegionType_Name) as (
				select
					lr.LpuRegion_id,
					lr.LpuRegionType_id,
					lr.LpuRegion_Name as LpuRegion_Name,
					lrt.LpuRegionType_Name as LpuRegionType_Name
				from
					LpuRegion lr
					left join LpuRegionType lrt on lr.LpuRegionType_id = lrt.LpuRegionType_id
				where Lpu_id = :Lpu_id
			),
			SelPersonCards(person_id, Lpu_id, Server_id, LpuAttachType_id, LpuRegion_id, Personcard_id, PersonCard_begDate, PersonCard_endDate) as (
				select
					pc.Person_id,
					pc.Lpu_id,
					pc.Server_id,
					pc.LpuAttachType_id,
					pc.LpuRegion_id,
					pc.PersonCard_id,
					pc.PersonCard_begDate,
					pc.PersonCard_endDate	
				from
					v_PersonCard_all pc 
					left join v_LpuRegion lr on lr.LpuRegion_id = pc.LpuRegion_id							
				{$whereString}
			)
			select
				{$select_part}
			from
				PersonCard pc 
				inner join v_PersonState ps on ps.Person_id = pc.Person_id
				inner join SelPersonCards spc on spc.PersonCard_id = pc.PersonCard_id
				left join SelLpuRegions lr on pc.LpuRegion_id = lr.LpuRegion_id
				left join lateral (
					select Polis.Polis_id
					from
						PersonStateall person
						join Polis Polis on Person.Polis_id = Polis.Polis_id
					where Person.Person_id = pc.Person_id
					  and Polis.Polis_begDate::date < :begDate
					  and (Polis.Polis_endDate is null or (Polis.Polis_endDate::date >= :begDate))
					limit 1
				) as PolisBeg on true
				left join lateral (
					select Polis.Polis_id
					from
						PersonStateall Person
						join Polis Polis on Person.Polis_id = Polis.Polis_id
					where Person.Person_id = pc.Person_id
					  and Polis.Polis_begDate::date <= :endDate
					  and (Polis.Polis_endDate is null or (Polis.Polis_endDate::date > :endDate))
					limit 1
				) as PolisEnd on true
				left join lateral (
					select
						pclast.PersonCard_id,
						pclast.Lpu_id
					from PersonCard pclast
					where pc.Person_id = pclast.Person_id
					  and pclast.PersonCard_id < pc.PersonCard_id
					  and pclast.LpuAttachType_id = pc.LpuAttachType_id
					order by pclast.PersonCard_id desc
					limit 1
				) as LastCard on true
				left join lateral (
					select
						pclast.PersonCard_id,
						pclast.Lpu_id,
						pclast.PersonCard_begDate
					from PersonCard pclast
					where pc.Person_id = pclast.Person_id
					  and pclast.PersonCard_id >= pc.PersonCard_id
					  and pclast.LpuAttachType_id = pc.LpuAttachType_id
					order by pclast.PersonCard_id asc
					limit 1
				) as NextCard on true
			where (1=1) {$addit_filter}
			group by
				lr.LpuRegion_id,
				lr.LpuRegion_Name,
				lr.LpuRegionType_Name
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$ret = $result->result_array();
		$itog = [
			"LpuRegion_Name" => "<b>Итог:</b>",
			"LpuRegionType_Name" => "",
			"LpuRegion_id" => "",
			"StartDate" => "",
			"EndDate" => "",
			"BegCount" => 0,
			"BegCountBDZ" => 0,
			"BegCountNotInBDZ" => 0,
			"EndCount" => 0,
			"EndCountBDZ" => 0,
			"EndCountNotInBDZ" => 0
		];
		foreach ($ret as $sel_row) {
			$itog["BegCount"] += $sel_row["BegCount"];
			$itog["BegCountBDZ"] += $sel_row["BegCountBDZ"];
			$itog["BegCountNotInBDZ"] += $sel_row["BegCountNotInBDZ"];
			$itog["EndCount"] += $sel_row["EndCount"];
			$itog["EndCountBDZ"] += $sel_row["EndCountBDZ"];
			$itog["EndCountNotInBDZ"] += $sel_row["EndCountNotInBDZ"];
		}
		$itog["BegCount"] = "<b>{$itog["BegCount"]}</b>";
		$itog["BegCountBDZ"] = "<b>{$itog["BegCountBDZ"]}</b>";
		$itog["BegCountNotInBDZ"] = "<b>{$itog["BegCountNotInBDZ"]}</b>";
		$itog["EndCount"] = "<b>{$itog["EndCount"]}</b>";
		$itog["EndCountBDZ"] = "<b>{$itog["EndCountBDZ"]}</b>";
		$itog["EndCountNotInBDZ"] = "<b>{$itog["EndCountNotInBDZ"]}</b>";
		if (count($ret) > 0) {
			$ret[] = $itog;
		}
		return $ret;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonCardDetailList(Polka_PersonCard_model $callObject, $data)
	{
		$queryParams = [
			"begDate" => ArrayVal($data, "StartDate"),
			"endDate" => ArrayVal($data, "EndDate"),
			"Lpu_id" => $data["session"]["lpu_id"],
		];
		if (ArrayVal($data, "LpuRegion_id") != "") {
			$queryParams["LpuRegion_id"] = ArrayVal($data, "LpuRegion_id");
			$lpu_region_filter = " and pc.LpuRegion_id = :LpuRegion_id ";
		} else {
			$lpu_region_filter = " and pc.LpuRegion_id is null and pc.Lpu_id = :Lpu_id ";
		}
		$dates_filter = "";
		if ($data["mode"] == "BegCount") {
			$dates_filter .= "
				pc.PersonCard_begDate < :begDate and (pc.PersonCard_endDate is null or pc.PersonCard_endDate >= :begDate)
			";
		} elseif ($data["mode"] == "BegCountBDZ") {
			$dates_filter .= "
				pc.PersonCard_begDate < :begDate and (pc.PersonCard_endDate is null or pc.PersonCard_endDate >= :begDate)
			";
		} elseif ($data["mode"] == "EndCount") {
			$dates_filter .= "
				pc.PersonCard_begDate <= :endDate and (pc.PersonCard_endDate is null or pc.PersonCard_endDate > :endDate)
			";
		} elseif ($data["mode"] == "AttachCount") {
			$dates_filter .= "
				pc.PersonCard_begDate between :begDate and :endDate
			";
		} elseif ($data["mode"] == "DettachCount") {
			$dates_filter .= "
				pc.PersonCard_endDate between :begDate and :endDate
			";
		}
		$sql = "
			select
				pc.PersonCard_Code as \"PersonCard_Code\",
				pc.PersonCard_id as \"PersonCard_id\",
				pc.Person_id as \"Person_id\",
				pc.Server_id as \"Server_id\",
				rtrim(pc.Person_SurName) as \"Person_Surname\",
				rtrim(pc.Person_FirName) as \"Person_Firname\",
				rtrim(pc.Person_SecName) as \"Person_Secname\",
				to_char(pc.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				to_char(pc.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
				to_char(pc.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
				pc.LpuRegionType_Name as \"LpuRegionType_Name\",
				pc.LpuRegion_Name as \"LpuRegion_Name\",
				coalesce(ccc.CardCloseCause_Name, '') as \"CardCloseCause_Name\",
				case when coalesce(pc.PersonCard_IsAttachCondit, 1) = 1 then 'false' else 'true' end as \"PersonCard_IsAttachCondit\",
				coalesce(pc1.LpuRegion_Name, '') as \"ActiveLpuRegion_Name\",
				coalesce(rtrim(lp.Lpu_Nick), '') as \"ActiveLpu_Nick\",
				coalesce(rtrim(Address.Address_Address), '') as \"PAddress_Address\"
			from
				v_PersonCard_All pc 
				left join CardCloseCause ccc on ccc.CardCloseCause_id = pc.CardCloseCause_id
				left join v_PersonCard pc1 on pc.Person_id=pc1.Person_id and pc.LpuAttachType_id=pc1.LpuAttachType_id
				left join v_Lpu lp on pc1.Lpu_id=lp.Lpu_id
				left join PersonState ps on ps.Person_id = pc.Person_id
				left join Address on ps.PAddress_id = Address.Address_id
			where
				{$dates_filter}
				{$lpu_region_filter}
			order by
				pc.Person_SurName, pc.Person_FirName, pc.Person_SecName
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonCardCount(Polka_PersonCard_model $callObject, $data)
	{
		$sql = "
			select count(PC.PersonCard_id) as \"PersonCard_Count\"
			from
				v_PersonCard PC 
				inner join v_PersonState PS  on PS.Person_id = PC.Person_id
			where PC.Lpu_id = :Lpu_id
			  and (PersonCard_endDate is null or PersonCard_endDate >= tzgetdate())
			  and LpuAttachType_id = 1
		";
		$sqlParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $Person_id
	 * @param $Lpu_id
	 * @param $KLStreet_id
	 * @param $Address_House
	 * @return array
	 */
	public static function getPersonRegionList(Polka_PersonCard_model $callObject, $Person_id, $Lpu_id, $KLStreet_id, $Address_House)
	{
		$Region = [];
		$arRegions = $callObject->FindAddressRegionsIDByPersonCard($Person_id, $Lpu_id);
		if (count($arRegions) == 0) {
			$arRegions = $callObject->FindAddressRegionsIDByAddress($KLStreet_id, $Address_House, $Lpu_id);
		}
		if (count($arRegions) > 0) {
			$arRegionsString = implode(", ", $arRegions);
			$sql = "
				select LR.LpuRegion_Name as \"LpuRegion_Name\"
				from
					v_LpuRegion LR 
					left join v_LpuRegionType LRT on LR.LpuRegionType_id=LRT.LpuRegionType_id
				where LR.LpuRegion_Id in({$arRegionsString})".(getRegionNick() != 'vologda' ?
				  "and LRT.LpuRegionType_sysNick in ('ter','ped','gin')" : "");
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($sql);
			if (is_object($result)) {
				$res = $result->result_array();
				foreach ($res as $row) {
					$Region[] = trim($row["LpuRegion_Name"]);
				}
			}
		}
		return $Region;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonData(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
			select 
				PS.Polis_id as \"Polis_id\",
				PS.PAddress_id as \"PAddress_id\",
				PS.UAddress_id as \"UAddress_id\",
				PC.Lpu_id as \"Lpu_id\",
				PS.Person_id as \"Person_id\",
				rtrim(PS.Person_SurName)||' '||rtrim(PS.Person_FirName)||' '||coalesce(rtrim(PS.Person_SecName), '') as \"Person_FIO\",
				rtrim(PS.Person_SurName) as \"Person_SurName\",
				rtrim(PS.Person_FirName) as \"Person_FirName\",
				coalesce(rtrim(PS.Person_SecName), '') as \"Person_SecName\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"Person_BirthDay\",
				coalesce(PS.Person_Snils, '') as \"Person_Snils\",
				ORG.Org_Email as \"OrgSmo_Email\",
				to_char(PC.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\"
			from
				v_PersonState PS 
				left join lateral (
					select
						Lpu_id,
					    PersonCard_begDate
					from v_PersonCard 
					where Person_id = PS.Person_id
					  and LpuAttachType_id = :LpuAttachType_id
					order by PersonCard_begDate desc
					limit 1
				) as PC on true
				left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
				left join v_OrgSmo SMO on SMO.OrgSmo_id = Polis.OrgSmo_id
				left join v_Org Org on Org.Org_id = SMO.Org_id
			where PS.Person_id = :Person_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonCardAttachOnPersonCard(Polka_PersonCard_model $callObject, $data)
	{
		$queryParams = ["PersonCard_id" => $data["PersonCard_id"]];
		$query = "
			select 
				PC.PersonCardAttach_id as \"PersonCardAttach_id\",
				PC.Person_id as \"Person_id\",
				PC.LpuAttachType_id as \"LpuAttachType_id\",
				PC.PersonCard_endDate as \"PersonCard_endDate\",
				PCA.PersonCardAttach_IsEmail as \"PersonCardAttach_IsEmail\",
				PCA.PersonCardAttach_Email as \"PersonCardAttach_Email\",
				PCA.PersonCardAttach_IsSms as \"PersonCardAttach_IsSms\",
				PCA.PersonCardAttach_Sms as \"PersonCardAttach_Sms\"
			from
				PersonCard PC 
				left join v_PersonCardAttach PCA on PCA.PersonCardAttach_id = PC.PersonCardAttach_id
			where PC.PersonCard_id = :PersonCard_id
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return bool
	 */
	public static function getPersonInfoKVRACHU(Polka_PersonCard_model $callObject, $data)
	{
		return false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonCardAttachStatus(Polka_PersonCard_model $callObject, $data)
	{
		$sql = "
			select 
				PCAST.PersonCardAttachStatusType_id as \"PersonCardAttachStatusType_id\",
				PCAST.PersonCardAttachStatusType_Code as \"PersonCardAttachStatusType_Code\",
				PCAST.PersonCardAttachStatusType_Name as \"PersonCardAttachStatusType_Name\"
			from
				v_PersonCardAttachStatus PCAS 
				left join lateral (select * from v_PersonCardAttachStatusType PCAST where PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id limit 1) PCAST on true
			where PCAS.PersonCardAttach_id = :PersonCardAttach_id
			order by
				PCAS.PersonCardAttachStatus_setDate desc,
				PCAS.PersonCardAttachStatus_id desc
			limit 1
		";
		$params = ["PersonCardAttach_id" => $data["PersonCardAttach_id"]];
		return $callObject->getFirstRowFromQuery($sql, $params);
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonCardAttachStatusesHistory(Polka_PersonCard_model $callObject, $data)
	{
		$query = "
			select PersonCardAttachStatus_id as \"PersonCardAttachStatus_id\"
			from v_PersonCardAttachStatus 
			where PersonCardAttach_id = :PersonCardAttach_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getInfoForAttachesFile(Polka_PersonCard_model $callObject, $data)
	{
		$params = [];
		if (isset($data["AttachesLpu_id"])) {
			$params["AttachesLpu_id"] = $data["AttachesLpu_id"];
		}
		$query = "
			select
				coalesce(Lpu_f003mcod, '') as \"Lpu_f003mcod\",
				replace(to_char(dbo.tzGetDate(), 'YYYY-MM-DD\"T\"HH24:MI:SS'), '-', '') as file_date
			from v_Lpu 
			where Lpu_id = :AttachesLpu_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		return $result->result_array();
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $person_id
	 * @param $old_lpu_id
	 * @param $new_lpu_id
	 * @return array
	 */
	public static function getDataForMessages(Polka_PersonCard_model $callObject, $person_id, $old_lpu_id, $new_lpu_id)
	{
		$result = [];
		$lpu_data_query = "
			select
				L.Org_Nick as \"Org_Nick\",
				coalesce(L.Lpu_f003mcod, '') as \"Lpu_f003mcod\",
				rtrim(O.Org_Email) as \"Org_Email\"
			from
				v_Lpu L
				left join v_Org O  on O.Org_id = L.Org_id
			where L.Lpu_id = :Lpu_id
		";
		$person_data_query = "
			select 
				PS.Polis_id as \"Polis_id\",
				PS.PAddress_id as \"PAddress_id\",
				PS.UAddress_id as \"UAddress_id\",
				PC.Lpu_id as \"Lpu_id\",
				rtrim(PS.Person_SurName)||' '||rtrim(PS.Person_FirName)||' '||coalesce(RTRIM(PS.Person_SecName), '') as \"Person_FIO\",
				rtrim(PS.Person_SurName) as \"Person_SurName\",
				rtrim(PS.Person_FirName) as \"Person_FirName\",
				coalesce(rtrim(PS.Person_SecName),'') as \"Person_SecName\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"Person_BirthDay\",
				coalesce(PS.Person_Snils,'') as \"Person_Snils\",
				ORG.Org_Email as \"OrgSmo_Email\",
				to_char(PC.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
				coalesce(PC.PersonCard_Code,'') as \"PersonCard_Code\"
			from
				v_PersonState PS 
				left join lateral (
					select 
						PC.Lpu_id,
						PC.PersonCard_begDate,
						coalesce(PAC.PersonAmbulatCard_Num, PC.PersonCard_Code) as PersonCard_Code
					from
						v_PersonCard PC 
						left join v_PersonAmbulatCardLink PCAL on PCAL.PersonCard_id = PC.PersonCard_id
						left join v_PersonAmbulatCard PAC on PAC.PersonAmbulatCard_id = PCAL.PersonAmbulatCard_id
					where PC.Person_id = PS.Person_id
					  and PC.LpuAttachType_id = 1
					order by PC.PersonCard_begDate desc
					limit 1
				) as PC on true
				left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
				left join v_OrgSmo SMO on SMO.OrgSmo_id = Polis.OrgSmo_id
				left join v_Org Org on Org.Org_id = SMO.Org_id
			where PS.Person_id = :Person_id
			limit 1
		";
		if ($old_lpu_id != 0) {
			$old_lpu_data = $callObject->db->query($lpu_data_query, ["Lpu_id" => $old_lpu_id]);
			if (is_object($old_lpu_data)) {
				$result["old_lpu_data"] = $old_lpu_data->result("array");
			}
		}
		$new_lpu_data = $callObject->db->query($lpu_data_query, ["Lpu_id" => $new_lpu_id]);
		if (is_object($new_lpu_data)) {
			$result["new_lpu_data"] = $new_lpu_data->result("array");
		}
		$person_data = $callObject->db->query($person_data_query, ["Person_id" => $person_id]);
		if (is_object($person_data)) {
			$result["person_data"] = $person_data->result("array");
		}
		return $result;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getPersonAttach(Polka_PersonCard_model $callObject, $data)
	{
		// Данные по пациенту
		$query = "
			select 
				ps.Person_Snils as \"PersonSnils_Snils\",
				j.Org_id as \"Org_id\",
				j.Post_id as \"Post_id\"
			from
				v_PersonState ps 
				left join v_Job j on j.Job_id = ps.Job_id
			where ps.Person_id = :Person_id
			limit 1
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($query, $queryParams);
		if (!is_object($res)) {
			return false;
		}
		$response = $res->result_array();
		if (!is_array($response) || count($response) == 0) {
			return [];
		}
		// Список прикреплений пациента
		$filterList = [];
		$queryParams = ["Person_id" => $data["Person_id"]];
		$filterList[] = "pca.Person_id = :Person_id";
		if (!empty($data["Lpu_id"])) {
			$queryParams["Lpu_id"] = $data["Lpu_id"];
			$filterList[] = "pca.Lpu_id = :Lpu_id";
		}
		if (!empty($data["LpuRegion_id"])) {
			$queryParams["LpuRegion_id"] = $data["LpuRegion_id"];
			$filterList[] = "pca.LpuRegion_id = :LpuRegion_id";
		}
		if (!empty($data["LpuAttachType_id"])) {
			$queryParams["LpuAttachType_id"] = $data["LpuAttachType_id"];
			$filterList[] = "pca.LpuAttachType_id = :LpuAttachType_id";
		}
		$whereString = (count($filterList) != 0) ? "where " . implode(" and ", $filterList) : "";
		$query = "
			select
				pca.PersonCard_id as \"PersonCard_id\",
				to_char(pca.PersonCard_begDate, '{$callObject->dateTimeForm120}') as \"PersonCard_begDate\",
				to_char(pca.PersonCard_endDate, '{$callObject->dateTimeForm120}') as \"PersonCard_endDate\",
				pca.PersonCard_Code as \"PersonCard_Code\",
				pca.Lpu_id as \"Lpu_id\",
				pca.LpuRegion_id as \"LpuRegion_id\",
				pca.LpuAttachType_id as \"LpuAttachType_id\",
				pc.PersonCard_isAttachAuto as \"PersonCard_isAttachAuto\",
				pca.CardCloseCause_id as \"CardCloseCause_id\",
				to_char(pc.PersonCard_AttachAutoDT, '{$callObject->dateTimeForm120}') as \"PersonCard_AttachAutoDT\",
				pca.PersonCard_isAttachCondit as \"PersonCard_isAttachCondit\"
			from
				v_PersonCard_All pca 
				left join PersonCard pc on pc.PersonCard_id = pca.PersonCard_id
			{$whereString}
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($query, $queryParams);
		if (!is_object($res)) {
			return false;
		}
		$response[0]["attach_data"] = $res->result_array();
		return $response;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getPersonAttachList(Polka_PersonCard_model $callObject, $data)
	{
		$begDate = DateTime::createFromFormat("Y-m-d", $data["begDate"]);
		$endDate = DateTime::createFromFormat("Y-m-d", $data["endDate"]);
		if ($begDate > $endDate) {
			return false;
		}
		$currentHour = intval(date("H"));
		$dateDiff = $endDate->diff($begDate);
		$filterList = [];
		if ($currentHour >= 7 && $currentHour <= 20) {
			// Дневной режим, ограничение периода - 1 день
			if ($dateDiff->days > 1) {
				$endDate = DateTime::createFromFormat("Y-m-d", $data["begDate"]);
				$endDate->add(new DateInterval("P1D"));
			}
		} else {
			// Ночной режим, ограничение периода - 32 дня
			if ($dateDiff->days > 32) {
				$endDate = DateTime::createFromFormat("Y-m-d", $data["begDate"]);
				$endDate->add(new DateInterval("P32D"));
			}
		}
		$data["begDate"] = $begDate->format("Y-m-d");
		$data["endDate"] = $endDate->format("Y-m-d");
		if (!empty($data["LpuAttachType_id"])) {
			$filterList[] = "t1.LpuAttachType_id = :LpuAttachType_id";
		}
		if (isset($data["PersonCardAttach"]) && in_array($data["PersonCardAttach"], [0, 1])) {
			switch ($data["PersonCardAttach"]) {
				case 0:
					$filterList[] = "t1.PersonCardAttach_id is null";
					break;

				case 1:
					$filterList[] = "t1.PersonCardAttach_id is not null";
					break;
			}
		}
		$tmpTableName = "#tmp" . time();
		$query = "
			select AttributeSign_id as \"AttributeSign_id\"
			from v_AttributeSign
			where AttributeSign_Code = 1
			  and AttributeSign_TableName = 'dbo.LpuSection'
			limit 1
		";
		$res = $callObject->getFirstRowFromQuery($query);
		$AttributeSign_id = !empty($res["AttributeSign_id"]) ? $res["AttributeSign_id"] : null;

		$query = "drop table if exists {$tmpTableName}";
		$callObject->db->query($query);
		$query = "
			create table {$tmpTableName} (
				PersonCard_id bigint,
				PersonCard_begDate timestamp,
				PersonCard_endDate timestamp,
				CardCloseCause_id bigint,
				PersonCard_isAttachCondit bigint,
				PersonCardAttach_id bigint,
				Person_id bigint,
				Lpu_id bigint,
				LpuRegion_id bigint,
				LpuRegion_fapid bigint,
				PersonCard_updDate timestamp
			)
		";
		$callObject->db->query($query);

		$filter1 = (count($filterList) > 0) ? "and " . implode(" and ", $filterList) : "";
		$filter2 = (count($filterList) > 0) ? "where " . implode(" and ", $filterList) : "";
		$query = "
			with LpuRegionChanges as (
				select
					LpuRegion_id,
					LpuRegion_updDT::date as PersonCard_updDate
				from v_LpuRegion 
				where LpuRegion_updDT::date between :begDate and :endDate
			)
			insert into {$tmpTableName} (
				PersonCard_id,
				PersonCard_begDate,
				PersonCard_endDate,
				CardCloseCause_id,
				PersonCard_isAttachCondit,
				PersonCardAttach_id,
				Person_id,
				Lpu_id,
				LpuRegion_id,
				LpuRegion_fapid,
				PersonCard_updDate
			)
			select
				t1.PersonCard_id,
				t1.PersonCard_begDate,
				t1.PersonCard_endDate,
				t1.CardCloseCause_id,
				t1.PersonCard_isAttachCondit,
				t1.PersonCardAttach_id,
				t1.Person_id,
				t1.Lpu_id,
				t1.LpuRegion_id,
				t1.LpuRegion_fapid,
				t1.PersonCardBeg_updDT::date as PersonCard_updDate
			from v_PersonCard_all t1 
			where t1.PersonCardBeg_updDT::date between :begDate and :endDate
				{$filter1}
			union
			select
				t1.PersonCard_id,
				t1.PersonCard_begDate,
				t1.PersonCard_endDate,
				t1.CardCloseCause_id,
				t1.PersonCard_isAttachCondit,
				t1.PersonCardAttach_id,
				t1.Person_id,
				t1.Lpu_id,
				t1.LpuRegion_id,
				t1.LpuRegion_fapid,
				t1.PersonCardEnd_updDT::date as PersonCard_updDate
			from v_PersonCard_all t1 
			where t1.PersonCardEnd_updDT::date between :begDate and :endDate
				{$filter1}
			union
			select
				t1.PersonCard_id,
				t1.PersonCard_begDate,
				t1.PersonCard_endDate,
				t1.CardCloseCause_id,
				t1.PersonCard_isAttachCondit,
				t1.PersonCardAttach_id,
				t1.Person_id,
				t1.Lpu_id,
				t1.LpuRegion_id,
				t1.LpuRegion_fapid,
				t2.PersonCard_updDate
			from
				v_PersonCard t1
				inner join LpuRegionChanges t2 on t2.LpuRegion_id = t1.LpuRegion_id
			{$filter2}
			union
			select
				t1.PersonCard_id,
				t1.PersonCard_begDate,
				t1.PersonCard_endDate,
				t1.CardCloseCause_id,
				t1.PersonCard_isAttachCondit,
				t1.PersonCardAttach_id,
				t1.Person_id,
				t1.Lpu_id,
				t1.LpuRegion_id,
				t1.LpuRegion_fapid,
				t2.PersonCard_updDate
			from
				v_PersonCard t1
				inner join LpuRegionChanges t2 on t2.LpuRegion_id = t1.LpuRegion_fapid
			{$filter2};
			select
			-- select
				p.BDZ_id as \"BDZ_id\",
				ps.Person_id as \"Person_id\",
				ps.Person_SurName as \"PersonSurName_SurName\",
				ps.Person_FirName as \"PersonFirName_FirName\",
				ps.Person_SecName as \"PersonSecName_SecName\",
				to_char(ps.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"PersonBirthDay_BirthDay\",
				ps.Sex_id as \"Person_Sex_id\",
				ps.Person_Snils as \"PersonSnils_Snils\",
				d.Document_id as \"Document_id\",
				d.DocumentType_id as \"DocumentType_id\",
				d.Document_Ser as \"Document_Ser\",
				d.Document_Num as \"Document_Num\",
				pls.Polis_id as \"Polis_id\",
				pls.PolisType_id as \"PolisType_id\",
				pls.Polis_Ser as \"Polis_Ser\",
				pls.Polis_Num as \"Polis_Num\",
				os.OrgSMO_f002smocod as \"OrgSmoCode\",
				to_char(pls.Polis_begDate, '{$callObject->dateTimeForm120}') as \"Polis_BegDate\",
				to_char(pls.Polis_endDate, '{$callObject->dateTimeForm120}') as \"Polis_EndDate\",
				ps.Person_EdNum as \"ENP\",
				pcc.PolisCloseCause_id as \"PolisCloseCause_id\",
				pcc.PolisCloseCause_Name as \"PolisCloseCause_Name\",
				pc.PersonCard_id as \"PersonCard_id\",
				to_char(pc.PersonCard_begDate, '{$callObject->dateTimeForm120}') as \"PersonCard_begDate\",
				to_char(pc.PersonCard_endDate, '{$callObject->dateTimeForm120}') as \"PersonCard_endDate\",
				ccc.CardCloseCause_id as \"CardCloseCause_id\",
				ccc.CardCloseCause_Name as \"CardCloseCause_Name\",
				case when pc.PersonCardAttach_id is not null then 1 else 2 end as \"PersonCard_isAttachCondit\",
				l.Lpu_id as \"Lpu_id\",
				l.Lpu_f003mcod as \"Lpu_Code\",
				lr.LpuRegion_id as \"LpuRegion_id\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
				lr.LpuRegion_tfoms as \"LpuRegion_tfoms\",
				ls.LpuSection_id as \"LpuSection_id\",
				lb.LpuBuilding_id as \"LpuBuilding_id\",
				lstc.LpuSection_CodeTFOMS as \"LpuSection_CodeTFOMS\",
				lbtc.LpuBuilding_CodeTFOMS as \"LpuBuilding_CodeTFOMS\",
				coalesce(lrmsf.Person_Snils, lrmsf2.Person_Snils) as \"Doc_Snils\",
				case
					when lrmsf.PostKind_id = 1 then 1
					when lrmsf.PostKind_id = 6 then 2
					else null
				end as \"Doc_Type\",
				lrf.LpuRegion_id as \"LpuRegionF_id\",
				lrf.LpuRegion_Name as \"LpuRegionF_Name\",
				lrf.LpuRegion_tfoms as \"LpuRegionF_tfoms\",
				lsf.LpuSection_id as \"LpuSectionF_id\",
				lbf.LpuBuilding_id as \"LpuBuildingF_id\",
				lsftc.LpuSectionF_CodeTFOMS as \"LpuSectionF_CodeTFOMS\",
				lbftc.LpuBuildingF_CodeTFOMS as \"LpuBuildingF_CodeTFOMS\",
				lrmsff.Person_Snils as \"DocF_Snils\",
				case
					when lrmsff.PostKind_id = 1 then 1
					when lrmsff.PostKind_id = 6 then 2
					else null
				end as \"DocF_Type\"
			-- end select		
			from
			-- from	
				{$tmpTableName} pc 
				inner join v_Lpu l on l.Lpu_id = pc.Lpu_id
				left join v_CardCloseCause ccc on ccc.CardCloseCause_id = pc.CardCloseCause_id
				inner join lateral (
					select *
					from v_Person_all 
					where Person_id = pc.Person_id
					  and PersonEvn_insDT <= pc.PersonCard_updDate
					order by PersonEvn_insDT desc
					limit 1
				) as ps on true
				inner join v_Person p on p.Person_id = ps.Person_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_Polis pls on pls.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSMO_id = pls.OrgSMO_id
				left join v_PolisCloseCause pcc on pcc.PolisCloseCause_id = pls.PolisCloseCause_id
				left join v_LpuRegion lr on lr.LpuRegion_id = pc.LpuRegion_id
				left join v_LpuSection ls on ls.LpuSection_id = lr.LpuSection_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
				left join lateral (
					select AV.AttributeValue_ValueString as LpuSection_CodeTFOMS
					from
						v_AttributeValue AV 
						inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where ASV.AttributeSign_id = {$AttributeSign_id}
					  and ASV.AttributeSignValue_TablePKey = ls.LpuSection_id
					  and A.Attribute_SysNick = 'Section_Code'
					limit 1
				) as lstc on true
				left join lateral (
					select AV.AttributeValue_ValueString as LpuBuilding_CodeTFOMS
					from
						v_AttributeValue AV 
						inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where ASV.AttributeSign_id = {$AttributeSign_id}
					  and ASV.AttributeSignValue_TablePKey = ls.LpuSection_id
					  and A.Attribute_SysNick = 'Building_Code'
					limit 1
				) as lbtc on true
				left join lateral (
					select
						t2.Person_Snils,
						t3.PostKind_id
					from
						v_MedStaffRegion t1 
						inner join v_MedStaffFact t3 on t3.MedStaffFact_id = t1.MedStaffFact_id
						inner join v_MedPersonal t2 on t2.MedPersonal_id = t3.MedPersonal_id
					where t1.LpuRegion_id = pc.LpuRegion_id
					  and t2.Person_Snils is not null
					  and t3.Lpu_id = pc.Lpu_id
					  and (t3.WorkData_begDate is null or t3.WorkData_begDate <= dbo.tzGetDate())
					  and (t3.WorkData_endDate is null or t3.WorkData_endDate >= dbo.tzGetDate())
					  and (t1.MedStaffRegion_begDate is null or t1.MedStaffRegion_begDate <= dbo.tzGetDate())
					  and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= dbo.tzGetDate())
					order by t3.PostKind_id
					limit 1
				) as lrmsf on true
				left join lateral (
					select MP.Person_Snils
					from
						v_MedPersonal MP 
						inner join v_MedStaffRegion MSR on MSR.LpuRegion_id = pc.LpuRegion_id and MSR.MedPersonal_id = MP.MedPersonal_id
					where MSR.MedStaffRegion_IsMain = 2
					  and MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate >= dbo.tzGetDate()
					  and MSR.MedStaffRegion_begDate is null or MSR.MedStaffRegion_begDate <= dbo.tzGetDate()
					order by MSR.MedStaffRegion_endDate
					limit 1
				) as lrmsf2 on true
				left join v_LpuRegion lrf on lrf.LpuRegion_id = pc.LpuRegion_fapid
				left join v_LpuSection lsf on lsf.LpuSection_id = lrf.LpuSection_id
				left join v_LpuBuilding lbf on lbf.LpuBuilding_id = lsf.LpuBuilding_id
				left join lateral (
					select AV.AttributeValue_ValueString as LpuSectionF_CodeTFOMS
					from
						v_AttributeValue AV 
						inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where ASV.AttributeSign_id = {$AttributeSign_id}
					  and ASV.AttributeSignValue_TablePKey = lsf.LpuSection_id
					  and A.Attribute_SysNick = 'Section_Code'
					limit 1
				) as lsftc on true
				left join lateral (
					select AV.AttributeValue_ValueString as LpuBuildingF_CodeTFOMS
					from
						v_AttributeValue AV 
						inner join v_AttributeSignValue ASV on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
						inner join v_Attribute A on A.Attribute_id = AV.Attribute_id
					where ASV.AttributeSign_id = {$AttributeSign_id}
					  and ASV.AttributeSignValue_TablePKey = lsf.LpuSection_id
					  and A.Attribute_SysNick = 'Building_Code'
					limit 1
				) as lbftc on true
				left join lateral (
					select
						t2.Person_Snils,
						t3.PostKind_id
					from
						v_MedStaffRegion t1 
						inner join v_MedStaffFact t3  on t3.MedStaffFact_id = t1.MedStaffFact_id
						inner join v_MedPersonal t2  on t2.MedPersonal_id = t3.MedPersonal_id
					where t1.LpuRegion_id = pc.LpuRegion_fapid
					  and t2.Person_Snils is not null
					  and t3.Lpu_id = pc.Lpu_id
					  and (t3.WorkData_begDate is null or t3.WorkData_begDate <= dbo.tzGetDate())
					  and (t3.WorkData_endDate is null or t3.WorkData_endDate >= dbo.tzGetDate())
					  and (t1.MedStaffRegion_begDate is null or t1.MedStaffRegion_begDate <= dbo.tzGetDate())
					  and (t1.MedStaffRegion_endDate is null or t1.MedStaffRegion_endDate >= dbo.tzGetDate())
					order by t3.PostKind_id
					limit 1
				) lrmsff on true
			-- end from
			order by
			-- order by
				p.BDZ_id,
				ps.Person_id
			-- end order by
		";
		$limit = 10000;
		$start = ($data["PageNum"] - 1) * $limit;
		$result = $callObject->queryResult(getLimitSQLPH($query, $start, $limit), $data, true);
		$count = count($result);

		$query = "DROP TABLE {$tmpTableName}";
		$callObject->db->query($query);
		return [
			"data" => $result,
			"ZAP" => $count
		];
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getPersonLpuInfoIsAgree(Polka_PersonCard_model $callObject, $data)
	{
		$filterList = ["pli.Person_id = :Person_id"];
		$queryParams = ["Person_id" => $data["Person_id"]];
		if (!empty($data["Lpu_id"])) {
			$queryParams["Lpu_id"] = $data["Lpu_id"];
			$filterList[] = "pli.Lpu_id = :Lpu_id";
		}
		$whereString = (count($filterList) != 0) ? "where " . implode(" and ", $filterList) : "";
		$query = "
			select case when pli.PersonLpuInfo_IsAgree = 2 then 1 else 0 end as \"PersonLpuInfo_IsAgree\"
			from v_PersonLpuInfo pli 
			{$whereString}
			order by pli.PersonLpuInfo_setDT desc
			limit 1
		";
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * Получение списка прикреплений.  метод для API
	 */
	public static function getPersonCardAPI(Polka_PersonCard_model $callObject, $data)
	{
		$filters = ["pca.LpuAttachType_id = :LpuAttachType_id"];
		if (!empty($data["Person_id"])) {
			$filters[] = "pca.Person_id = :Person_id";
		}
		if (!empty($data["Lpu_id"])) {
			$filters[] = "pca.Lpu_id = :Lpu_id";
		}
		if (!empty($data["LpuRegion_id"])) {
			$filters[] = "pca.LpuRegion_id = :LpuRegion_id";
		}
		if (!empty($data["LpuRegion_fapid"])) {
			$filters[] = "pca.LpuRegion_fapid = :LpuRegion_fapid";
		}
		if (!empty($data["Date_DT"])) {
			$filters[] = "
				pc.PersonCard_begDate < :Date_DT and (pc.PersonCard_endDate is null or pc.PersonCard_endDate >= :Date_DT)
			";
		}
		// если включен режим оффлайна
		if (!empty($data["isOffline"])) {
			$select = "
				pca.Person_id as \"Person_id\"
			";
			$filters[] = "pca.PersonCard_endDate is null";
		} else {
			$select = "
				pca.Person_id as \"Person_id\",
				pca.PersonCardAttach_id as \"PersonCardAttach_id\",
				pca.PersonCard_id as \"PersonCard_id\",
				pca.Lpu_id as \"Lpu_id\",
				pca.LpuRegion_id as \"LpuRegion_id\",
				pca.LpuAttachType_id as \"LpuAttachType_id\",
				pca.PersonCard_Code as \"PersonCard_Code\",
				to_char(pca.PersonCard_begDate, '{$callObject->dateTimeForm104}') as \"PersonCard_begDate\",
				to_char(pca.PersonCard_endDate, '{$callObject->dateTimeForm104}') as \"PersonCard_endDate\",
				pca.CardCloseCause_id as \"CardCloseCause_id\",
				pca.PersonCard_IsAttachCondit as \"PersonCard_IsAttachCondit\",
				pc.PersonCard_IsAttachAuto as \"PersonCard_IsAttachAuto\",
				pc.PersonCard_AttachAutoDT as \"PersonCard_AttachAutoDT\",
				pca.PersonCard_DmsPolisNum as \"PersonCard_DmsPolisNum\",
				pca.PersonCard_DmsBegDate as \"PersonCard_DmsBegDate\",
				pca.PersonCard_DmsEndDate as \"PersonCard_DmsEndDate\",
				pca.OrgSMO_id as \"OrgSMO_id\",
				pca.LpuRegion_fapid as \"LpuRegion_fapid\",
				pca.LpuRegionType_id as \"LpuRegionType_id\",
				pca.MedStaffFact_id as \"MedStaffFact_id\"
			";
		}
		$whereString = implode(" and ", $filters);
		$sql = "
			select {$select}
			from
				v_PersonCard_All pca
				left join PersonCard pc on pc.PersonCard_id = pca.PersonCard_id
			where {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Polka_PersonCard_model $callObject
	 * @return array|false
	 */
	public static function getRecMethodTypeCombo(Polka_PersonCard_model $callObject)
	{
		$query = "
			select
				RecMethodType_id as \"RecMethodType_id\",
			    RecMethodType_Code as \"RecMethodType_Code\",
			    RecMethodType_Name as \"RecMethodType_Name\"
			from RecMethodType
			where RecMethodType_id in (1, 3, 14, 15, 16)
		";
		return $callObject->queryResult($query);
	}
}