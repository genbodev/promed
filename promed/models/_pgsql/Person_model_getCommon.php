<?php

class Person_model_getCommon
{
	/**
	 * Получение данных полиса. Метод для API
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getPolisForAPI(Person_model $callObject, $data)
	{
		$params = ["Polis_id" => $data["Polis_id"]];
		$query = "
			select
				PP.PersonPolis_id as \"PersonPolis_id\",
				PP.Server_id as \"Server_id\",
				PP.Polis_id as \"Polis_id\",
				PP.Person_id as \"Person_id\",
				PP.OMSSprTerr_id as \"OMSSprTerr_id\",
				PP.PolisType_id as \"PolisType_id\",
				PP.Polis_Ser as \"Polis_Ser\",
				PP.Polis_Num as \"Polis_Num\",
				PP.OrgSmo_id as \"OrgSmo_id\",
				to_char(PP.Polis_begDate, '{$callObject->dateTimeForm120}') as \"Polis_begDate\",
				to_char(PP.Polis_endDate, '{$callObject->dateTimeForm120}') as \"Polis_endDate\",
				PP.PolisFormType_id as \"PolisFormType_id\"
			from v_PersonPolis PP
			where PP.Polis_id = :Polis_id
			limit 1
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param Person_model $callObject
	 * @param $post_new
	 * @param array $data
	 * @return |null
	 */
	public static function getPostIdFromPostName(Person_model $callObject, $post_new, $data = [])
	{
		$Post_id = null;
		/**@var CI_DB_result $result */
		if (is_numeric($post_new)) {
			$numPostID = 1;
			$sql = "
				select Post_id as \"Post_id\"
				from v_Post 
				where Post_id = ?
			";
			$result = $callObject->db->query($sql, [$post_new]);
		} else {
			$sql = "
				select Post_id as \"Post_id\"
				from v_Post 
				where Post_Name iLIKE ? and Server_id = ?
			";
			$result = $callObject->db->query($sql, [$post_new, $data["Server_id"]]);
		}
		if (is_object($result)) {
			$sel = $result->result_array();
			if (isset($sel[0])) {
				if ($sel[0]["Post_id"] > 0) {
					$Post_id = $sel[0]["Post_id"];
				}
			} elseif (isset($numPostID)) {
				$Post_id = null;
			} else {
				$sql = "
					select Post_id as \"Post_id\"
					from dbo.p_Post_ins(
						Post_Name := ?,
						pmUser_id := ?,
						Server_id := ?
					);
				";
				$result = $callObject->db->query($sql, [$post_new, $data["pmUser_id"], $data["Server_id"]]);
				if (is_object($result)) {
					$sel = $result->result_array();
					if ($sel[0]["Post_id"] > 0) {
						$Post_id = $sel[0]["Post_id"];
					}
				}
			}
		}
		return $Post_id;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getAddressByPersonId(Person_model $callObject, $data)
	{
		$query = "
			select
				uaddr.Address_Zip as \"UAddress_Zip\",
				uaddr.KLCountry_id as \"UKLCountry_id\",
				uaddr.KLRGN_id as \"UKLRGN_id\",
				uaddr.KLSubRGN_id as \"UKLSubRGN_id\",
				uaddr.KLCity_id as \"UKLCity_id\",
				uaddr.KLTown_id as \"UKLTown_id\",
				uaddr.KLStreet_id as \"UKLStreet_id\",
				uaddr.Address_House as \"UAddress_House\",
				uaddr.Address_Corpus as \"UAddress_Corpus\",
				uaddr.Address_Flat as \"UAddress_Flat\",
				uaddr.Address_Address as \"UAddress_AddressText\",
				uaddr.Address_Address as \"UAddress_Address\",
				uaddr.PersonSprTerrDop_id as \"UPersonSprTerrDop_id\",
				paddr.Address_Zip as \"PAddress_Zip\",
				paddr.KLCountry_id as \"PKLCountry_id\",
				paddr.KLRGN_id as \"PKLRGN_id\",
				paddr.KLSubRGN_id as \"PKLSubRGN_id\",
				paddr.KLCity_id as \"PKLCity_id\",
				paddr.KLTown_id as \"PKLTown_id\",
				paddr.KLStreet_id as \"PKLStreet_id\",
				paddr.Address_House as \"PAddress_House\",
				paddr.Address_Corpus as \"PAddress_Corpus\",
				paddr.Address_Flat as \"PAddress_Flat\",
				paddr.Address_Address as \"PAddress_AddressText\",
				paddr.Address_Address as \"PAddress_Address\",
				paddr.PersonSprTerrDop_id as \"PPersonSprTerrDop_id\"
			from
				v_Personstate vper 
				left join v_Address uaddr on vper.UAddress_id = uaddr.Address_id
				left join v_Address paddr on vper.PAddress_id = paddr.Address_id
			where vper.Person_id =  :Person_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Запрос для получения списка истории изменения всех периодик человека
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getAllPeriodics(Person_model $callObject, $data)
	{
		$query = "";
		$filter = "";
		$queryParams = ["Person_id" => $data["Person_id"]];
		$classname = (getRegionNick() != "kz")
			? "pec.PersonEvnClass_Name as \"PersonEvnClass_Name\""
			: "case when pec.PersonEvnClass_id = 20 then 'ИИН' else pec.PersonEvnClass_Name end as \"PersonEvnClass_Name\"";
		$sql = "
			select
				pa.PersonEvn_id as \"PersonEvn_id\",
				pa.Server_id as \"Server_id\",
				pa.Server_pid as \"Server_pid\",
				pa.Person_id as \"Person_id\",
				pa.PersonEvnClass_id as \"PersonEvnClass_id\",
				{$classname},
				to_char(pa.PersonEvn_insDT, 'DD.MM.YYYY HH24:MI:SS') as \"PersonEvn_insDT\",
				case
					when pa.PersonEvnClass_id = 1 then coalesce(pa.Person_SurName, '')
					when pa.PersonEvnClass_id = 2 then coalesce(pa.Person_FirName, '')
					when pa.PersonEvnClass_id = 3 then coalesce(pa.Person_SecName, '')
					when pa.PersonEvnClass_id = 4 then coalesce(to_char(pa.Person_BirthDay, '{$callObject->dateTimeForm104}'), '')
					when pa.PersonEvnClass_id = 5 then coalesce(sx.Sex_Name, '')
					when pa.PersonEvnClass_id = 6 then coalesce(Person_Snils, '')
					when pa.PersonEvnClass_id = 7 then coalesce(ss.SocStatus_Name, '')
					when pa.PersonEvnClass_id = 8 then coalesce(rtrim(sprt.OMSSprTerr_Name), '')||' '||coalesce(rtrim(osmo.OrgSMO_Nick), '') || ' ' || coalesce(rtrim(pls.Polis_Ser), '') || ' ' || coalesce(rtrim(pls.Polis_Num), '') || coalesce( ' Открыт: ' || to_char(pls.Polis_begDate, '$callObject->dateTimeForm104'), '') || coalesce( ' Закрыт: ' || to_char(pls.Polis_endDate, '{$callObject->dateTimeForm104}'), '')
					when pa.PersonEvnClass_id = 9 then coalesce(rtrim(dt.DocumentType_Name), '')||' '||coalesce(rtrim(doc.Document_Ser), '') || ' ' || coalesce(RTRIM(doc.Document_Num), '') || ' ' || coalesce(to_char(doc.Document_begDate, '$callObject->dateTimeForm104'), '')
					when pa.PersonEvnClass_id = 10 then coalesce(rtrim(uaddr.Address_Address), '')
					when pa.PersonEvnClass_id = 11 then coalesce(rtrim(paddr.Address_Address), '')
					when pa.PersonEvnClass_id = 12 then coalesce(rtrim(jorg.Org_Name), '')
					when pa.PersonEvnClass_id = 15 then coalesce(ref.YesNo_Name, '')
					when pa.PersonEvnClass_id = 16 then coalesce(rtrim(pa.Person_EdNum), '')
					when pa.PersonEvnClass_id = 18 then coalesce(rtrim(pa.PersonPhone_Phone), '')
					when pa.PersonEvnClass_id = 20 then coalesce(rtrim(pa.PersonInn_Inn), '')
					when pa.PersonEvnClass_id = 21 then coalesce(rtrim(pa.PersonSocCardNum_SocCardNum), '')
					when pa.PersonEvnClass_id = 22 then coalesce(fs.FamilyStatus_Name, '')
					when pa.PersonEvnClass_id = 23 then coalesce(klc.KLCountry_Code||'. '||klc.KLCountry_Name, '')||coalesce(case when ns.NationalityStatus_IsTwoNation = 2 then ', Двойное гражданство (РФ и иностранное государство)' end, '')
					else ''
				end as \"PersonEvn_Value\",
				case when pa.PersonEvnClass_id = 8 and pls.BDZ_id is not null then 1 else 0 end as \"PersonEvn_readOnly\",
				case
					when pa.PersonEvnClass_id = 8 then pa.Polis_id
					when pa.PersonEvnClass_id = 9 then pa.Document_id
					when pa.PersonEvnClass_id = 10 then pa.UAddress_id
					when pa.PersonEvnClass_id = 11 then pa.PAddress_id
					when pa.PersonEvnClass_id = 12 then pa.Job_id
					when pa.PersonEvnClass_id = 23 then pa.NationalityStatus_id
					else null
				end as \"PersonEvnObject_id\"
				{$query}
			from
				v_Person_all as pa
				inner join PersonEvnClass pec on pa.PersonEvnClass_id = pec.PersonEvnClass_id
				inner join v_PersonEvn pe on pe.PersonEvn_id = pa.PersonEvn_id
				left join Sex sx on sx.Sex_id = pa.Sex_id
				left join SocStatus ss on ss.SocStatus_id = pa.SocStatus_id
				left join FamilyStatus fs on fs.FamilyStatus_id = pa.FamilyStatus_id
				left join v_Polis pls on pls.Polis_id = pa.Polis_id
				left join OMSSprTerr sprt on sprt.OMSSprTerr_id = pls.OmsSprTerr_id
				left join v_OrgSmo osmo on osmo.OrgSMO_id = pls.OrgSmo_id
				left join v_PersonRefuse PR on PR.Person_id = pa.Person_id and PR.PersonRefuse_Year = date_part('YEAR',dbo.tzGetDate())
				left join YesNo ref on ref.YesNo_id = PR.PersonRefuse_IsRefuse
				left join Document doc on doc.Document_id = pa.Document_id
				left join DocumentType dt on dt.DocumentType_id = doc.DocumentType_id
				left join NationalityStatus ns on ns.NationalityStatus_id = pa.NationalityStatus_id
				left join KLCountry klc on klc.KLCountry_id = ns.KLCountry_id
				left join Address uaddr on uaddr.Address_id = pa.UAddress_id
				left join Address paddr on paddr.Address_id = pa.PAddress_id
				left join Job jb on jb.Job_id = pa.Job_id
				left join Org jorg on jorg.Org_id = jb.Org_id
			where pa.Person_id = :Person_id
			  {$filter}
			order by
				pa.PersonEvn_insDT desc,
				pa.PersonEvn_TimeStamp desc
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $queryParams);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получение антропометрических данных человека
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getAnthropometryViewData(Person_model $callObject, $data)
	{
		$query = "
			select
				PH.PersonHeight_id as \"PersonHeight_id\",
				to_char(PH.PersonHeight_setDT, '{$callObject->dateTimeForm104}') as \"PersonHeight_setDate\",
				PH.PersonHeight_Height as \"PersonHeight_Height\",
				coalesce(IsAbnorm.YesNo_Name, '') as \"PersonHeight_IsAbnorm\",
				coalesce(HAT.HeightAbnormType_Name, '') as \"HeightAbnormType_Name\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\"
			from
				v_PersonHeight PH 
				left join v_YesNo IsAbnorm on IsAbnorm.YesNo_id = PH.PersonHeight_IsAbnorm
				left join v_HeightAbnormType HAT on HAT.HeightAbnormType_id = PH.HeightAbnormType_id
				left join v_HeightMeasureType HMT on HMT.HeightMeasureType_id = PH.HeightMeasureType_id
			where PH.Person_id = :Person_id
			order by PH.PersonHeight_setDT
		";
		$queryParams = ["Person_id" => $data["Person_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		return (is_object($result))?$result->result_array():false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return mixed|bool
	 */
	public static function getBDZPersonData(Person_model $callObject, $data)
	{
		$filter = "";
		$params = [];
		if (isset($data["Person_id"])) {
			$filter = " and vper.Person_id <> :Person_id";
			$params["Person_id"] = $data["Person_id"];
		}
		$sql = "
			select
				vper.Person_SurName as \"Person_SurName\",
				vper.Person_SecName as \"Person_SecName\",
				vper.Person_FirName as \"Person_FirName\",
				case when PersonPrivilegeFed.Person_id is not null then 1 else 0 end as \"Person_IsFedLgot\",
				vper.Server_pid as \"Server_pid\",
				vper.Person_id as \"Person_id\",
				to_char(cast(vper.Person_BirthDay as timestamp), 'DD.MM.YYYY') as \"Person_BirthDay\",
				vper.Sex_id as \"PersonSex_id\",
				case
					when length(vper.Person_Snils) = 11 then left(vper.Person_Snils, 3) || '-' || substring(vper.Person_Snils, 4, 3) || '-' || 
						substring(vper.Person_Snils, 7, 3) || '-' || right(vper.Person_Snils, 2)
					else vper.Person_Snils
				end as \"Person_SNILS\",
				vper.SocStatus_id as \"SocStatus_id\",
				vper.FamilyStatus_id as \"FamilyStatus_id\",
				vper.PersonFamilyStatus_IsMarried as \"PersonFamilyStatus_IsMarried\",
				vper.Person_edNum as \"Federal_Num\",
				vper.UAddress_id as \"UAddress_id\",
				uaddr.PersonSprTerrDop_id as \"UPersonSprTerrDop_id\",
				uaddr.Address_Zip as \"UAddress_Zip\",
				uaddr.KLCountry_id as \"UKLCountry_id\",
				uaddr.KLRGN_id as \"UKLRGN_id\",
				uaddr.KLSubRGN_id as \"UKLSubRGN_id\",
				uaddr.KLCity_id as \"UKLCity_id\",
				uaddr.KLTown_id as \"UKLTown_id\",
				uaddr.KLStreet_id as \"UKLStreet_id\",
				uaddr.Address_House as \"UAddress_House\",
				uaddr.Address_Corpus as \"UAddress_Corpus\",
				uaddr.Address_Flat as \"UAddress_Flat\",
				uaddrsp.AddressSpecObject_id as \"UAddressSpecObject_id\",
				uaddrsp.AddressSpecObject_Name as \"UAddressSpecObject_Value\",
				uaddr.Address_Address as \"UAddress_AddressText\",
				uaddr.Address_Address as \"UAddress_Address\",
				baddr.PersonSprTerrDop_id as \"BPersonSprTerrDop_id\",
				baddr.Address_id as \"Address_id\",
				baddr.KLCountry_id as \"BKLCountry_id\",
				baddr.KLRGN_id as \"BKLRGN_id\",
				baddr.KLSubRGN_id as \"BKLSubRGN_id\",
				baddr.KLCity_id as \"BKLCity_id\",
				baddr.KLTown_id as \"BKLTown_id\",
				baddr.KLStreet_id as \"BKLStreet_id\",
				baddr.Address_House as \"BAddress_House\",
				baddr.Address_Corpus as \"BAddress_Corpus\",
				baddr.Address_Flat as \"BAddress_Flat\",
				baddrsp.AddressSpecObject_id as \"BAddressSpecObject_id\",
				baddrsp.AddressSpecObject_Name as \"BAddressSpecObject_Value\",
				baddr.Address_Zip as \"BAddress_Zip\",
				baddr.Address_Address as \"BAddress_AddressText\",
				baddr.Address_Address as \"BAddress_Address\",
				pcc.PolisCloseCause_Code as \"polisCloseCause\",
				vper.PAddress_id as \"PAddress_id\",
				paddr.PersonSprTerrDop_id as \"PPersonSprTerrDop_id\",
				paddr.Address_Zip as \"PAddress_Zip\",
				paddr.KLCountry_id as \"PKLCountry_id\",
				paddr.KLRGN_id as \"PKLRGN_id\",
				paddr.KLSubRGN_id as \"PKLSubRGN_id\",
				paddr.KLCity_id as \"PKLCity_id\",
				paddr.KLTown_id as \"PKLTown_id\",
				paddr.KLStreet_id as \"PKLStreet_id\",
				paddr.Address_House as \"PAddress_House\",
				paddr.Address_Corpus as \"PAddress_Corpus\",
				paddr.Address_Flat as \"PAddress_Flat\",
				paddrsp.AddressSpecObject_id as \"PAddressSpecObject_id\",
				paddrsp.AddressSpecObject_Name as \"PAddressSpecObject_Value\",
				paddr.Address_Address as \"PAddress_AddressText\",
				paddr.Address_Address as \"PAddress_Address\",
				pi.Nationality_id as \"PersonNationality_id\",
				pol.OmsSprTerr_id as \"OMSSprTerr_id\",
				pol.PolisType_id as \"PolisType_id\",
				pol.Polis_Ser as \"Polis_Ser\",
				case when pol.PolisType_id = 4 then '' else pol.Polis_Num end as \"Polis_Num\",
				pol.OrgSmo_id as \"OrgSMO_id\",
				to_char(cast(pol.Polis_begDate as timestamp), '{$callObject->dateTimeForm104}') as \"Polis_begDate\",
				to_char(cast(pol.Polis_endDate as timestamp), '{$callObject->dateTimeForm104}') as \"Polis_endDate\",
				doc.DocumentType_id as \"DocumentType_id\",
				doc.Document_Ser as \"Document_Ser\",
				doc.Document_Num as \"Document_Num\",
				doc.OrgDep_id as \"OrgDep_id\",
				ns.KLCountry_id as \"KLCountry_id\",
				ns.LegalStatusVZN_id as \"LegalStatusVZN_id\",
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as \"NationalityStatus_IsTwoNation\",
				pjob.Org_id as \"Org_id\",
				pjob.OrgUnion_id as \"OrgUnion_id\",
				pjob.Post_id as \"Post_id\",
				to_char(cast(doc.Document_begDate as timestamp), '{$callObject->dateTimeForm104}') as \"Document_begDate\",
				PDEP.DeputyKind_id as \"DeputyKind_id\",
				PDEP.Person_pid as \"DeputyPerson_id\",
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName || ' ' || PDEPSTATE.Person_FirName || ' ' || COALESCE(PDEPSTATE.Person_SecName, '') ELSE '' END as \"DeputyPerson_Fio\",
				ResidPlace_id as \"ResidPlace_id\",
				PersonChild_id as \"PersonChild_id\",
				PersonChild_IsManyChild as \"PersonChild_IsManyChild\",
				PersonChild_IsBad as \"PersonChild_IsBad\",
				PersonChild_IsYoungMother as \"PersonChild_IsYoungMother\",
				PersonChild_IsIncomplete as \"PersonChild_IsIncomplete\",
				PersonChild_IsInvalid as \"PersonChild_IsInvalid\",
				PersonChild_IsTutor as \"PersonChild_IsTutor\",
				PersonChild_IsMigrant as \"PersonChild_IsMigrant\",
				HealthKind_id as \"HealthKind_id\",
				ph.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				ph.HeightAbnormType_id as \"HeightAbnormType_id\",
				pw.WeightAbnormType_id as \"WeightAbnormType_id\",
				pw.PersonWeight_IsAbnorm as \"PersonWeight_IsAbnorm\",
				PCh.PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
				FeedingType_id as \"FeedingType_id\",
				PersonChild_CountChild as \"PersonChild_CountChild\",
				InvalidKind_id as \"InvalidKind_id\",
				to_char(cast(PersonChild_invDate as timestamp), '{$callObject->dateTimeForm104}') as \"PersonChild_invDate\",
				HealthAbnorm_id as \"HealthAbnorm_id\",
				HealthAbnormVital_id as \"HealthAbnormVital_id\",
				Diag_id as \"Diag_id\",
				to_char(cast(vper.Person_deadDT as timestamp), '{$callObject->dateTimeForm104}') as \"Person_deadDT\",
				to_char(cast(vper.Person_closeDT as timestamp), '{$callObject->dateTimeForm104}') as \"Person_closeDT\",
				rtrim(vper.Person_Phone) as \"PersonPhone_Phone\",
				rtrim(pi.PersonInfo_InternetPhone) as \"PersonInfo_InternetPhone\",
				rtrim(vper.Person_Inn) as \"PersonInn_Inn\",
				rtrim(vper.Person_SocCardNum) as \"PersonSocCardNum_SocCardNum\",
				rtrim(Ref.PersonRefuse_IsRefuse::text) as \"PersonRefuse_IsRefuse\",
				rtrim(pce.PersonCarExist_IsCar::text) as \"PersonCarExist_IsCar\",
				rtrim(pche.PersonChildExist_IsChild::text) as \"PersonChildExist_IsChild\",
				ph.PersonHeight_Height as \"PersonHeight_Height\",
				COALESCE(pw.Okei_id, 37) as \"Okei_id\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				case when vper.Server_pid = 0 and pol.Polis_endDate is not null and pol.Polis_endDate < dbo.tzGetDate() then 1 else 0 end as \"Polis_CanAdded\",
				pi.Ethnos_id as \"Ethnos_id\",
				mop.OnkoOccupationClass_id as \"OnkoOccupationClass_id\",
				per.BDZ_Guid as \"BDZ_Guid\",
				pol.Polis_Guid as \"Polis_Guid\"
			from
				v_PersonState vper
				left join v_Person per  on per.Person_id=vper.Person_id
				left join v_Address uaddr  on vper.UAddress_id = uaddr.Address_id
				left join v_AddressSpecObject uaddrsp  on uaddr.AddressSpecObject_id = uaddrsp.AddressSpecObject_id
				left join v_Address paddr  on vper.PAddress_id = paddr.Address_id
				left join v_AddressSpecObject paddrsp  on paddr.AddressSpecObject_id = paddrsp.AddressSpecObject_id
				left join PersonBirthPlace pbp  on vper.Person_id = pbp.Person_id
				left join v_Address baddr  on pbp.Address_id = baddr.Address_id
				left join v_AddressSpecObject baddrsp  on baddr.AddressSpecObject_id = baddrsp.AddressSpecObject_id
				left join Polis pol  on pol.Polis_id=vper.Polis_id
				left join v_PolisCloseCause pcc  on pol.PolisCloseCause_id = pcc.PolisCloseCause_id
				left join Document doc  on doc.Document_id=vper.Document_id
				left join NationalityStatus ns  on ns.NationalityStatus_id = vper.NationalityStatus_id
				left join PersonInfo pi  on pi.Person_id = vper.Person_id
				left join Job pjob  on vper.Job_id = pjob.Job_id
				left join PersonDeputy PDEP  on PDEP.Person_id = vper.Person_id
				left join v_PersonState PDEPSTATE  on PDEPSTATE.Person_id = PDEP.Person_pid
				left join PersonChild PCh  on PCh.Person_id = vper.Person_id
				left join lateral (
					select pp.Person_id
					from
						v_PersonPrivilege pp 
						inner join v_PrivilegeType pt  on pt.PrivilegeType_id = pp.PrivilegeType_id
					where pt.ReceptFinance_id = 1
					  and pp.PersonPrivilege_begDate <= dbo.tzGetDate()
					  and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= dbo.tzGetDate()::date)
					  and pp.Person_id = vper.Person_id
	                limit 1
				) as PersonPrivilegeFed on true
				left join lateral (
					select OnkoOccupationClass_id
					from v_MorbusOnkoPerson 
					where Person_id = vper.Person_id
					order by MorbusOnkoPerson_insDT desc
	                limit 1
				) as mop ON true
				left join lateral (
					select PersonRefuse_IsRefuse
					from v_PersonRefuse
					where Person_id = vper.Person_id
					  and PersonRefuse_Year = date_part('year',dbo.tzGetDate())
					order by PersonRefuse_insDT desc
	                limit 1
				) as Ref ON true
				left join lateral (
					select PersonCarExist_IsCar
					from PersonCarExist 
					where Person_id = vper.Person_id
					order by PersonCarExist_setDT desc
	                limit 1
				) as pce ON true
				left join lateral (
					select PersonChildExist_IsChild
					from PersonChildExist 
					where Person_id = vper.Person_id
					order by PersonChildExist_setDT desc
	                limit 1
				) as pche ON true
				left join lateral (
					select
						PersonHeight_Height,
						PersonHeight_IsAbnorm,
						HeightAbnormType_id
					from PersonHeight
					where Person_id = vper.Person_id
					order by PersonHeight_setDT desc
	                limit 1
				) as ph ON true
				left join lateral (
					select
						PersonWeight_Weight,
						WeightAbnormType_id,
						PersonWeight_IsAbnorm,
						Okei_id
					from PersonWeight 
					where Person_id = vper.Person_id
					order by PersonWeight_setDT desc
	                limit 1
				) as pw ON true
			where per.BDZ_Guid = :BDZ_Guid
			  {$filter}
			limit 1
		";
		$params["BDZ_Guid"] = $data["BDZ_Guid"];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return (count($result) == 1) ? $result[0] : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $Person_id
	 * @return array
	 */
	public static function getCurrentPersonJob(Person_model $callObject, $Person_id)
	{
		$query = "
			select
				pjob.Org_id as \"Org_id\",
				pjob.OrgUnion_id as \"OrgUnion_id\",
				pjob.Post_id as \"Post_id\"
			from 
				v_PersonState vper 
				left join Job pjob  on vper.Job_id = pjob.Job_id
			where vper.Person_id = :Person_id
			limit 1
		";
		$queryParams = ["Person_id" => $Person_id];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return [
				"Org_id" => null,
				"Post_id" => null,
				"OrgUnion_id" => null,
			];
		}
		$dataPersonState = $result->result_array();
		return [
			"Org_id" => isset($dataPersonState[0]["Org_id"]) ? $dataPersonState[0]["Org_id"] : null,
			"Post_id" => isset($dataPersonState[0]["Post_id"]) ? $dataPersonState[0]["Post_id"] : null,
			"OrgUnion_id" => isset($dataPersonState[0]["OrgUnion_id"]) ? $dataPersonState[0]["OrgUnion_id"] : null,
		];
	}

	/**
	 * Получение диагнозов человека на диспансерном учете
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getDiagnosesPersonOnDisp(Person_model $callObject, $data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$filters = "";
		$diagFilters = getAccessRightsDiagFilter("Diag.Diag_Code");
		if (!empty($diagFilters)) {
			$filters .= " and $diagFilters";
		}
		if (!empty($data["actualForToday"]) && $data["actualForToday"]) {
			$filters .= " and (PD.PersonDisp_endDate > dbo.tzGetDate() or PD.PersonDisp_endDate is null)";
		}
		$query = "
			select 
				PD.Diag_id as \"Diag_id\",
				Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_Name as \"Diag_Name\"
			from
				v_PersonDisp PD
				left join v_PersonState_All PS on PD.Person_id = PS.Person_id 
				left join v_Diag Diag on Diag.Diag_id = PD.Diag_id
			where PS.Person_id = :Person_id 
			  {$filters}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * Получение данных документа. Метод для API
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getDocumentForAPI(Person_model $callObject, $data)
	{
		$params = ["Document_id" => $data["Document_id"]];
		$query = "
			select
				PD.Person_id as \"Person_id\",
				PD.Server_id as \"Server_id\",
				PD.PersonDocument_id as \"PersonDocument_id\",
				PD.Document_id as \"Document_id\",
				PD.DocumentType_id as \"DocumentType_id\",
				PD.Document_Ser as \"Document_Ser\",
				PD.Document_Num as \"Document_Num\",
				PD.OrgDep_id as \"OrgDep_id\",
				to_char(PD.Document_begDate, '{$callObject->dateTimeForm120}') as \"Document_begDate\",
				NS.KLCountry_id as \"KLCountry_id\"
			from
				v_PersonDocument PD
				inner join v_Person_all P on P.PersonEvn_id = PD.PersonDocument_id
				left join v_NationalityStatus NS on NS.NationalityStatus_id = P.NationalityStatus_id
			where PD.Document_id = :Document_id
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * Получить дату взятия биопсии из последнего случая с признаком ЗНО
	 * @param Person_model $callObject
	 * @param $data
	 * @return bool|float|int|string
	 */
	public static function getEvnBiopsyDate(Person_model $callObject, $data)
	{
		$sql = "
			with evnz as (
				select
					EVPL.EvnVizitPL_id as evn_id,
					to_char(EVPL.EvnVizitPL_setDate, '{$callObject->dateTimeForm104}') as evn_date,
					EVPL.EvnVizitPL_setDate as sortdate
				from v_EvnVizitPL EVPL
				where EVPL.Person_id = :Person_id
				  and EVPL.EvnVizitPL_IsZNO = 2
				union
				select
					EPS.EvnPS_id as evn_id,
					to_char(EPS.EvnPS_setDate, '{$callObject->dateTimeForm104}') as evn_date,
					EPS.EvnPS_setDate as sortdate
				from v_EvnPS EPS
				where EPS.Person_id = :Person_id
				  and EPS.EvnPS_IsZNO = 2
				union
				select
					STOM.EvnDiagPLStom_id as evn_id,
					to_char(STOM.EvnDiagPLStom_setDate, '{$callObject->dateTimeForm104}') as evn_date,
					STOM.EvnDiagPLStom_setDate as sortdate
				from v_EvnDiagPLStom STOM
				where STOM.Person_id = :Person_id
				  and STOM.EvnDiagPLStom_IsZNO = 2
				union
				select
					ES.EvnSection_id as evn_id,
					to_char(ES.EvnSection_setDate, '{$callObject->dateTimeForm104}') as evn_date,
					ES.EvnSection_setDate as sortdate
				from v_EvnSection ES
				where ES.Person_id = :Person_id
				  and ES.EvnSection_IsZNO = 2
			)
			select to_char(EDH.EvnDirectionHistologic_didDate, '{$callObject->dateTimeForm104}') as \"BiopsyDate\"
			from
				v_EvnDirectionHistologic EDH 
				inner join evnz on evnz.evn_id = EDH.EvnDirectionHistologic_pid
			order by evnz.sortdate desc
            limit 1
		";
		$sqlParams = ["Person_id" => $data["Person_id"]];
		return $callObject->getFirstResultFromQuery($sql, $sqlParams);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return mixed
	 */
	public static function getInfoForDouble(Person_model $callObject, $data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select 
				PS.Person_id as \"Person_id\",
				case when MP.MedPersonal_id is null then 0 else 1 end as \"IsMedPersonal\"
			from
				v_PersonState PS 
				left join lateral (
					select MedPersonal_id
					from v_MedPersonal 
					where Person_id = PS.Person_id
					limit 1
				) as MP on true
			where PS.Person_id = :Person_id
			limit 1
		";
		$response = $callObject->queryResult($query, $params);
		return $response[0];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getLastDocumentForAPI(Person_model $callObject, $data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select
				PD.Person_id as \"Person_id\",
				PD.Document_id as \"Document_id\",
				PD.DocumentType_id as \"DocumentType_id\",
				PD.Document_Ser as \"Document_Ser\",
				PD.Document_Num as \"Document_Num\",
				PD.OrgDep_id as \"OrgDep_id\",
				to_char(PD.Document_begDate, '{$callObject->dateTimeForm120}') as \"Document_begDate\",
				NS.KLCountry_id as \"KLCountry_id\"
			from
				v_PersonState PS 
				inner join v_PersonDocument PD on PD.Document_id = PS.Document_id
				left join v_NationalityStatus NS on NS.NationalityStatus_id = PS.NationalityStatus_id
			where PS.Person_id = :Person_id
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getLastPolisForAPI(Person_model $callObject, $data)
	{
		$params = [];
		if (!empty($data["Person_id"])) {
			$filters[] = "PS.Person_id = :Person_id";
			$params["Person_id"] = $data["Person_id"];
		}
		if (!empty($data["Polis_id"])) {
			$filters[] = "P.Polis_id = :Polis_id";
			$params["Polis_id"] = $data["Polis_id"];
		}
		if (!empty($data["Polis_Ser"])) {
			$filters[] = "P.Polis_Ser = :Polis_Ser";
			$params["Polis_Ser"] = $data["Polis_Ser"];
		}
		if (!empty($data["Polis_Num"])) {
			$filters[] = "(PS.Polis_Num = :Polis_Num or PS.Person_EdNum = :Polis_Num)";
			$filters[] = "coalesce(PS.Polis_Num, PS.Person_EdNum) = :Polis_Num"; // Person_EdNum проверяем если только Polis_Num пустой, но сам по себе такой фильтр работает медленно, поэтому дублируем через or
			$params["Polis_Num"] = $data["Polis_Num"];
		}
		if (empty($params)) {
			throw new Exception("Не передан ни один из параметров поиска", 6);
		}
		$whereString = implode(" and ", $filters);
		$query = "
			select
				PS.Person_id as \"Person_id\",
				P.Polis_id as \"Polis_id\",
				P.OmsSprTerr_id as \"OmsSprTerr_id\",
				P.PolisType_id as \"PolisType_id\",
				P.Polis_Ser as \"Polis_Ser\",
				P.Polis_Num as \"Polis_Num\",
				P.OrgSmo_id as \"OrgSmo_id\",
				to_char(P.Polis_begDate, '{$callObject->dateTimeForm120}') as \"Polis_begDate\",
				to_char(P.Polis_endDate, '{$callObject->dateTimeForm120}') as \"Polis_endDate\",
				P.PolisFormType_id as \"PolisFormType_id\"
			from
				v_PersonState PS 
				left join v_Polis P  on P.Polis_id = PS.Polis_id
			where {$whereString}
			order by P.Polis_begDate desc
			limit 1
		";
		$resp = $callObject->queryResult($query, $params);
		return ["error_code" => 0, "data" => $resp];
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getMainFields(Person_model $callObject, $data)
	{
		if (getRegionNick() != "penza") {
			return [];
		}
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select 
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				to_char(PS.Person_BirthDay, '{$callObject->dateTimeForm120}') as \"Person_BirthDay\",
				PS.Sex_id as \"PersonSex_id\",
				PS.Person_SNILS as \"Person_SNILS\",
				PS.PolisType_id as \"PolisType_id\",
				PS.Polis_Ser as \"Polis_Ser\",
				PS.Polis_Num as \"Polis_Num\",
				PS.Person_IsInErz as \"Person_IsInErz\"
			from v_PersonState PS 
			where PS.Person_id = :Person_id
			limit 1
		";
		return $callObject->getFirstRowFromQuery($query, $params);
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getOrgSMO(Person_model $callObject, $data)
	{
		$sql = "
			select
				os.OrgSMO_id as \"OrgSMO_id\",
				ost.OMSSprTerr_id as \"OMSSprTerr_id\"
			from
				v_OrgSMO os 
				inner join v_Org o  on o.Org_id = os.Org_id
				left join lateral (
					select OMSSprTerr_id
					from v_OMSSprTerr 
					where lpad(coalesce(OMSSprTerr_OKATO, ''), '0') = :Org_OKATO
                    limit 1
				) as ost on true
			where o.Org_OGRN = :Org_OGRN
				and left(o.Org_OKATO, 5) = :Org_OKATO
            limit 1
		";
		$params = [
			"Org_OGRN" => $data["Org_OGRN"],
			"Org_OKATO" => $data["Org_OKATO"]
		];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * @param Person_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getLastPeriodicalsByPolicy(Person_model $callObject, $data)
	{
		$resultArr = array();
		if(empty($data['Person_id'])) return $resultArr;

		$sql = "
			select
				PersonPolis_id as \"PersonPolis_id\",
				Server_id as \"Server_id\",
				Polis_id as \"Polis_id\",
				Polis_endDate as \"Polis_endDate\"
			from
				v_PersonPolis
			where
				Person_id = :Person_id
			order by
				PersonPolis_insDT desc,
				PersonPolis_TimeStamp desc
			limit 1
		";

		$res = $callObject->db->query($sql, array('Person_id' => $data['Person_id']));
		if (is_object($res)) {
			$sel = $res->result('array');
			if (count($sel) > 0) $resultArr = $sel[0];
		}
		return $resultArr;
	}
}