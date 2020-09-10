<?php

class Search_model_selectBody
{
	public static function selectBody_CmpCallCard(Search_model $callObject)
	{
		return "
			v_CmpCallCard CCC
			left join v_CmpCallCardCostPrint ccp on ccp.CmpCallCard_id = ccc.CmpCallCard_id
			left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
			left join v_CmpReason CSecondR on CSecondR.CmpReason_id = CCC.CmpSecondReason_id
			left join v_CmpResult CRES on CRES.CmpResult_id = CCC.CmpResult_id
			left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
			left join EmergencyTeam ET on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
			left join {$callObject->schema}.v_CmpCloseCard CLC on CLC.CmpCallCard_id = CCC.CmpCallCard_id
			left join v_Lpu L on L.Lpu_id = CL.Lpu_id
			left join v_Lpu Lpu on CCC.CmpLpu_id = Lpu.Lpu_id
			left join v_Lpu LpuHid on CCC.CmpLpu_id = LpuHid.Lpu_id
			left join v_Diag CLD on CLD.Diag_id = CLC.Diag_id
			left join v_CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
			left join v_Diag D on D.Diag_id = CCC.Diag_sid
			left join v_Diag UD on UD.Diag_id = CCC.Diag_uid
			left join v_PersonState PS on CCC.Person_id = PS.Person_id
			left join v_Polis pls on pls.Polis_id = ps.Polis_id
			left join Address PAddr on PAddr.Address_id = PS.PAddress_id					
			left join LpuBuilding LB on LB.LpuBuilding_id = CCC.LpuBuilding_id
			left join CmpCallCardInputType CCCInput on CCCInput.CmpCallCardInputType_id = CCC.CmpCallCardInputType_id
			left join lateral(
				select CCCE.CmpCallCardEvent_id
				from
					v_CmpCallCardEvent CCCE
					left join v_CmpCallCardEventType CCCET on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id
				where CCCE.CmpCallCard_id = CCC.CmpCallCard_id
				  and CCCET.CmpCallCardEventType_Code = '6'
				limit 1
			) as acceptNmpFlag on true
		";
	}

	public static function selectBody_CmpCloseCard(Search_model $callObject, $data)
	{
		$reasonCase = (!(in_array($data["session"]["region"]["nick"], ["kz"])))
			? "left join CmpReason CR on CR.CmpReason_id = CLC.CallPovod_id"
			: "";
		return "
			v_CmpCallCard CCC
			left join {$callObject->schema}.v_CmpCloseCard CLC on CLC.CmpCallCard_id = CCC.CmpCallCard_id
			left join v_CmpCallCardCostPrint ccp on ccp.CmpCallCard_id = ccc.CmpCallCard_id
			{$reasonCase}
			left join CmpReason CRTalon on CRTalon.CmpReason_id = CCC.CmpReason_id
			left join CmpResult CRES on CRES.CmpResult_id = CCC.CmpResult_id
			left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
			left join EmergencyTeam ET on ET.EmergencyTeam_id = CCC.EmergencyTeam_id
			left join v_Lpu L on L.Lpu_id = CL.Lpu_id
			left join v_Lpu Lpu on CCC.CmpLpu_id = Lpu.Lpu_id
			left join v_Lpu LpuHid on CCC.CmpLpu_id = LpuHid.Lpu_id
			left join v_Diag CLD on CLD.Diag_id = CLC.Diag_id					
			left join v_Diag D on D.Diag_id = CCC.Diag_sid
			left join v_CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
			left join v_Diag UD on UD.Diag_id = CCC.Diag_uid
			left join v_PersonState PS on CCC.Person_id = PS.Person_id
			left join v_PersonState PSCLC on CLC.Person_id = PSCLC.Person_id
			left join v_Polis pls on pls.Polis_id = ps.Polis_id
			left join Address PAddr on PAddr.Address_id = PS.PAddress_id
			left join LpuBuilding LB on LB.LpuBuilding_id = CLC.LpuBuilding_id
			left join v_LpuBuilding ETLB on ETLB.LpuBuilding_id = ET.LpuBuilding_id
			left join CmpCallCardInputType CCCInput on CCCInput.CmpCallCardInputType_id = CCC.CmpCallCardInputType_id
			left join lateral(
				select
					CCLCR.CmpCloseCardCombo_id,
					CCLCR.Localize
				from
					{$callObject->schema}.v_CmpCloseCardRel CCLCR
					left join {$callObject->comboSchema}.v_CmpCloseCardCombo CCLCB on CCLCB.CmpCloseCardCombo_id = CCLCR.CmpCloseCardCombo_id
				where CCLCR.CmpCloseCard_id = CLC.CmpCloseCard_id
				  and CCLCB.CmpCloseCardCombo_Code = 693
				order by CCLCR.CmpCloseCardRel_id desc
				limit 1
			) isActiveCombo on true
			left join lateral(
				select
					CCLCR.CmpCloseCardCombo_id,
					CCLCR.Localize
				from
					{$callObject->schema}.v_CmpCloseCardRel CCLCR
					left join {$callObject->comboSchema}.v_CmpCloseCardCombo CCLCB on CCLCB.CmpCloseCardCombo_id = CCLCR.CmpCloseCardCombo_id
					left join {$callObject->comboSchema}.v_CmpCloseCardCombo parentCCLCB on parentCCLCB.CmpCloseCardCombo_id = CCLCB.Parent_id
				where CCLCR.CmpCloseCard_id = CLC.CmpCloseCard_id
				  and parentCCLCB.CmpCloseCardCombo_Code = 142
				order by CCLCR.CmpCloseCardRel_id desc
				limit 1
			) socialCombo on true
			left join lateral(
				select
					CCLCR.CmpCloseCardCombo_id,
					CCLCR.Localize
				from
					{$callObject->schema}.v_CmpCloseCardRel CCLCR
					left join {$callObject->comboSchema}.v_CmpCloseCardCombo CCLCB on CCLCB.CmpCloseCardCombo_id = CCLCR.CmpCloseCardCombo_id
					left join {$callObject->comboSchema}.v_CmpCloseCardCombo parentCCLCB on parentCCLCB.CmpCloseCardCombo_id = CCLCB.Parent_id
				where CCLCR.CmpCloseCard_id = CLC.CmpCloseCard_id
				  and parentCCLCB.CmpCloseCardCombo_Code = 223
				order by CCLCR.CmpCloseCardRel_id desc
				limit 1
			) resultCombo on true
			left join lateral(
				select CCCE.CmpCallCardEvent_id
				from
					v_CmpCallCardEvent CCCE
					left join v_CmpCallCardEventType CCCET on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id
				where CCCE.CmpCallCard_id = CCC.CmpCallCard_id
				  and CCCET.CmpCallCardEventType_Code = '6'
				limit 1
			) as acceptNmpFlag on true
		";
	}

	public static function selectBody_PersonPrivilegeWOW()
	{
		return "
			inner join PersonPrivilegeWOW PPW on PPW.Person_id = PS.Person_id
			left join PrivilegeTypeWOW PTW on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id
			left join Sex on Sex.Sex_id = PS.Sex_id
			left join v_Address UAdd on UAdd.Address_id = ps.UAddress_id
			left join v_Address PAdd on PAdd.Address_id = ps.PAddress_id
		";
	}

	public static function selectBody_RegisterSixtyPlus()
	{
		return "
			left join dbo.v_PersonState PS on PS.Person_id = RPlus.Person_id
			left join dbo.v_PersonCardState card  on RPlus.Person_id = card.Person_id and card.LpuAttachType_id = 1
			left join dbo.v_LpuRegion rg on card.LpuRegion_id = rg.LpuRegion_id
			left join dbo.LpuRegion as LpuRegionFap on card.LpuRegion_fapid = LpuRegionFap.LpuRegion_id
			left join dbo.v_InvalidGroupType IGT on IGT.InvalidGroupType_id = RPlus.InvalidGroupType_id
		";
	}

	public static function selectBody_EvnPLWOW(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			?" inner join v_EvnPLWOW EPW on EPW.Server_id = PS.Server_id and EPW.PersonEvn_id = PS.PersonEvn_id and EPW.Lpu_id {$getLpuIdFilterString}"
			:" inner join v_EvnPLWOW EPW on EPW.Person_id = PS.Person_id and EPW.Lpu_id {$getLpuIdFilterString}";
		$query .= "
			left join lateral(
				select PrivilegeTypeWow_id
				from PersonPrivilegeWOW
				where Person_id = PS.Person_id
				limit 1
			) as PPW on true
			left join PrivilegeTypeWOW PTW on PTW.PrivilegeTypeWow_id = PPW.PrivilegeTypeWow_id
			left join YesNo on YesNo.YesNo_id = EPW.EvnPLWOW_IsFinish
			left join Polis PLS on PLS.Polis_id = PS.Polis_id
		";
		return $query;
	}

	public static function selectBody_EvnDtpDeath(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			?" inner join v_EvnDtpDeath EDD on EDD.Server_id = PS.Server_id and EDD.PersonEvn_id = PS.PersonEvn_id and EDD.Lpu_id {$getLpuIdFilterString}"
			:" inner join v_EvnDtpDeath EDD on EDD.Person_id = PS.Person_id and EDD.Lpu_id {$getLpuIdFilterString}";
		$query .= "
			inner join v_Sex sex on sex.Sex_id = PS.Sex_id
			inner join v_Diag diag on diag.Diag_id = EDD.Diag_iid
		";
		return $query;
	}

	public static function selectBody_PersonDopDisp(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		return "
			inner join PersonDopDisp DD on DD.Person_id = PS.Person_id and DD.Lpu_id {$getLpuIdFilterString}
			left join Sex on PS.Sex_id = Sex.Sex_id
			left join v_Job as job1 ON PS.Job_id = job1.Job_id
			left join v_Org as org1 ON job1.Org_id = org1.Org_id
			left join v_Okved as okved1 ON okved1.Okved_id = org1.Okved_id
			left join v_Address as addr1 ON PS.UAddress_id = addr1.Address_id
			left join lateral(
				select KLArea_Name
				from v_KLAreaStat
				where (KLCountry_id = addr1.KLCountry_id or KLCountry_id is null)
				  and (KLRGN_id = addr1.KLRGN_id or KLRGN_id is null)
				  and (KLSubRGN_id = addr1.KLSubRGN_id or KLSubRGN_id is null)
				  and (KLCity_id = addr1.KLCity_id or KLCity_id is null)
				  and (KLTown_id = addr1.KLTown_id or KLTown_id is null)
				order by
					KLCountry_id desc,
					KLRGN_id desc,
					KLSubRGN_id desc,
					KLCity_id desc,
					KLTown_id desc
				limit 1
			) as astat1 on true
			left join v_Address as addr2 ON org1.UAddress_id = addr2.Address_id
			left join lateral(
				select KLArea_Name
				from v_KLAreaStat
				where (KLCountry_id = addr2.KLCountry_id or KLCountry_id is null)
				  and (KLRGN_id = addr2.KLRGN_id or KLRGN_id is null)
				  and (KLSubRGN_id = addr2.KLSubRGN_id or KLSubRGN_id is null)
				  and (KLCity_id = addr2.KLCity_id or KLCity_id is null)
				  and (KLTown_id = addr2.KLTown_id or KLTown_id is null)
				order by
					KLCountry_id desc,
					KLRGN_id desc,
					KLSubRGN_id desc,
					KLCity_id desc,
					KLTown_id desc
				limit 1
			) as astat2 on true
			left join lateral(
				select Lpu_Nick 
				from
					PersonDopDisp pdd
					inner join v_Lpu vlp on vlp.Lpu_id = pdd.Lpu_id
				where pdd.Person_id = PS.Person_id
				  and pdd.Lpu_id <> :Lpu_id
				  and pdd.PersonDopDisp_Year = :PersonDopDisp_Year
				limit 1
			) as otherddlpu on true
			left join v_EvnPLDispDop epldd on epldd.Person_id = PS.Person_id and epldd.Lpu_id
				{$getLpuIdFilterString}
				and epldd.EvnPLDispDop_setDate between cast(:PersonDopDisp_Year_Start as date) and cast(:PersonDopDisp_Year_End as date)
		";
	}

	public static function selectBody_PersonDispOrpPeriod(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		return "
			inner join v_PersonDispOrp DOr on DOr.Person_id = PS.Person_id and DOr.Lpu_id {$getLpuIdFilterString}
			left join Sex on PS.Sex_id = Sex.Sex_id
			left join v_Address UAdd on UAdd.Address_id = ps.UAddress_id
			left join v_Address PAdd on PAdd.Address_id = ps.PAddress_id
			left join v_EvnPLDispTeenInspection EPLDTI on EPLDTI.PersonDispOrp_id = DOr.PersonDispOrp_id
			left join lateral(
				select
					pc.Person_id as PersonCard_Person_id,
					pc.Lpu_id
				from v_PersonCard pc
				where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
				order by PersonCard_begDate desc
				limit 1
			) as pcard on true
			left join v_Lpu LATT on pcard.Lpu_id = LATT.Lpu_id
			left join v_EducationInstitutionType EIT on EIT.EducationInstitutionType_id = DOr.EducationInstitutionType_id
		";
	}

	public static function selectBody_PersonDispOrpPred(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		return "
			inner join v_PersonDispOrp DOr on DOr.Person_id = PS.Person_id and DOr.Lpu_id {$getLpuIdFilterString}
			left join Sex on PS.Sex_id = Sex.Sex_id
			left join v_EvnPLDispTeenInspection EPLDTI on EPLDTI.PersonDispOrp_id = DOr.PersonDispOrp_id
			left join lateral(
				select
					pc.Person_id as PersonCard_Person_id,
					pc.Lpu_id
				from v_PersonCard pc
				where pc.Person_id = ps.Person_id and LpuAttachType_id = 1
				order by PersonCard_begDate desc
				limit 1
			) as pcard on true
			left join v_Lpu LATT on pcard.Lpu_id=LATT.Lpu_id
			left join v_EducationInstitutionType EIT on EIT.EducationInstitutionType_id = DOr.EducationInstitutionType_id
		";
	}

	public static function selectBody_PersonDispOrpProf(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		return "
			inner join v_PersonDispOrp DOr on DOr.Person_id = PS.Person_id and DOr.Lpu_id {$getLpuIdFilterString}
			left join Sex on PS.Sex_id = Sex.Sex_id
			left join v_EvnPLDispTeenInspection EPLDTI on EPLDTI.PersonDispOrp_id = DOr.PersonDispOrp_id
			left join v_AgeGroupDisp AGD on AGD.AgeGroupDisp_id = DOr.AgeGroupDisp_id
		";
	}

	public static function selectBody_PersonDispOrp(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$filterevnpl = "";
		switch ($data["CategoryChildType"]) {
			case "orp":
				$filterevnpl = " and epldd.DispClass_id = 3";
				break;
			case "orpadopted":
				$filterevnpl = " and epldd.DispClass_id = 7";
				break;
		}
		return "
			inner join v_PersonDispOrp DOr on DOr.Person_id = PS.Person_id and DOr.Lpu_id {$getLpuIdFilterString}
			left join Sex on PS.Sex_id = Sex.Sex_id
			left join v_Job as job1 ON PS.Job_id = job1.Job_id
			left join v_Org as org1 ON job1.Org_id = org1.Org_id
			left join v_Okved as okved1 ON okved1.Okved_id = org1.Okved_id
			left join v_Address UAdd on UAdd.Address_id = ps.UAddress_id
			left join v_Address PAdd on PAdd.Address_id = ps.PAddress_id
			left join v_Address as addr1 ON PS.UAddress_id = addr1.Address_id
			left join lateral(
				select KLArea_Name
				from v_KLAreaStat
				where (KLCountry_id = addr1.KLCountry_id or KLCountry_id is null)
				  and (KLRGN_id = addr1.KLRGN_id or KLRGN_id is null)
				  and (KLSubRGN_id = addr1.KLSubRGN_id or KLSubRGN_id is null)
				  and (KLCity_id = addr1.KLCity_id or KLCity_id is null)
				  and (KLTown_id = addr1.KLTown_id or KLTown_id is null)
				order by
					KLCountry_id desc,
					KLRGN_id desc,
					KLSubRGN_id desc,
					KLCity_id desc,
					KLTown_id desc
				limit 1
			) as astat1 on true
			left join v_Address as addr2 ON org1.UAddress_id=addr2.Address_id
			left join lateral(
				select KLArea_Name
				from v_KLAreaStat
				where (KLCountry_id = addr2.KLCountry_id or KLCountry_id is null)
				  and (KLRGN_id = addr2.KLRGN_id or KLRGN_id is null)
				  and (KLSubRGN_id = addr2.KLSubRGN_id or KLSubRGN_id is null)
				  and (KLCity_id = addr2.KLCity_id or KLCity_id is null)
				  and (KLTown_id = addr2.KLTown_id or KLTown_id is null)
				order by
					KLCountry_id desc,
					KLRGN_id desc,
					KLSubRGN_id desc,
					KLCity_id desc,
					KLTown_id desc
				limit 1
			) as astat2 on true
			left join lateral(
				select Lpu_Nick 
				from
					PersonDispOrp pdd
					inner join v_Lpu vlp on vlp.Lpu_id = pdd.Lpu_id
				where pdd.Person_id = PS.Person_id
				  and pdd.Lpu_id <> :Lpu_id
				  and pdd.PersonDispOrp_Year = :PersonDispOrp_Year
			) as ODL on true
			left join lateral(
				select EvnPLDispOrp_id
				from v_EvnPLDispOrp epldd
				where epldd.Person_id=PS.Person_id
				  and epldd.Lpu_id {$getLpuIdFilterString}
				  {$filterevnpl}
				  and date_part('year', epldd.EvnPLDispOrp_setDate) = :PersonDispOrp_Year
				limit 1
			) as EPLDO on true
		";
	}

	public static function selectBody_PersonDispOrpOld(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		return "
			inner join v_PersonDispOrp DOr on DOr.Person_id = PS.Person_id and DOr.Lpu_id {$getLpuIdFilterString}
			left join Sex on PS.Sex_id = Sex.Sex_id
			left join v_Job as job1 ON PS.Job_id = job1.Job_id
			left join v_Org as org1 ON job1.Org_id = org1.Org_id
			left join v_Okved as okved1 ON okved1.Okved_id = org1.Okved_id
			left join v_Address as addr1 ON PS.UAddress_id = addr1.Address_id
			left join lateral(
				select KLArea_Name
				from v_KLAreaStat
				where (KLCountry_id = addr1.KLCountry_id or KLCountry_id is null)
				  and (KLRGN_id = addr1.KLRGN_id or KLRGN_id is null)
				  and (KLSubRGN_id = addr1.KLSubRGN_id or KLSubRGN_id is null)
				  and (KLCity_id = addr1.KLCity_id or KLCity_id is null)
				  and (KLTown_id = addr1.KLTown_id or KLTown_id is null)
				order by
					KLCountry_id desc,
					KLRGN_id desc,
					KLSubRGN_id desc,
					KLCity_id desc,
					KLTown_id desc
				limit 1
			) as astat1 on true
			left join v_Address as addr2 on org1.UAddress_id=addr2.Address_id
			left join lateral(
				select KLArea_Name
				from v_KLAreaStat
				where (KLCountry_id = addr2.KLCountry_id or KLCountry_id is null)
				  and (KLRGN_id = addr2.KLRGN_id or KLRGN_id is null)
				  and (KLSubRGN_id = addr2.KLSubRGN_id or KLSubRGN_id is null)
				  and (KLCity_id = addr2.KLCity_id or KLCity_id is null)
				  and (KLTown_id = addr2.KLTown_id or KLTown_id is null)
				order by
					KLCountry_id desc,
					KLRGN_id desc,
					KLSubRGN_id desc,
					KLCity_id desc,
					KLTown_id desc
				limit 1
			) as astat2 on true
			left join lateral(
				select Lpu_Nick 
				from
					PersonDispOrp pdd
					inner join v_Lpu vlp on vlp.Lpu_id = pdd.Lpu_id
				where pdd.Person_id = PS.Person_id
				  and pdd.Lpu_id <> :Lpu_id
				  and pdd.PersonDispOrp_Year = :PersonDispOrp_Year
			) as ODL on true
			left join lateral(
				select EvnPLDispOrp_id
				from v_EvnPLDispOrp epldd
				where epldd.Person_id=PS.Person_id
				  and epldd.Lpu_id {$getLpuIdFilterString}
				  and date_part('year', epldd.EvnPLDispOrp_setDate) = :PersonDispOrp_Year
				limit 1
			) as EPLDO on true
		";
	}

	public static function selectBody_EvnPLDispDopStream(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			?" inner join v_EvnPLDispDop EPLDD on EPLDD.Server_id = PS.Server_id and EPLDD.PersonEvn_id = PS.PersonEvn_id and EPLDD.Lpu_id {$getLpuIdFilterString} "
			:" inner join v_EvnPLDispDop EPLDD on PS.Person_id = EPLDD.Person_id and EPLDD.Lpu_id {$getLpuIdFilterString} ";
		$query .= " 
			left join YesNo IsFinish on IsFinish.YesNo_id = EPLDD.EvnPLDispDop_IsFinish
		";
		return $query;
	}

	public static function selectBody_EvnPLDispTeen14Stream(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			?" inner join v_EvnPLDispTeen14 EPLDT14 on EPLDT14.Server_id = PS.Server_id and EPLDT14.PersonEvn_id = PS.PersonEvn_id and EPLDT14.Lpu_id {$getLpuIdFilterString} "
			:" inner join v_EvnPLDispTeen14 EPLDT14 on PS.Person_id = EPLDT14.Person_id and EPLDT14.Lpu_id {$getLpuIdFilterString} ";
		$query .= " 
			left join YesNo IsFinish on IsFinish.YesNo_id = EPLDT14.EvnPLDispTeen14_IsFinish
		";
		return $query;
	}

	public static function selectBody_EvnPLDispOrpStream(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$query = ($data["PersonPeriodicType_id"] == 2)
			? " inner join v_EvnPLDispOrp EPLDO on EPLDO.Server_id = PS.Server_id and EPLDO.PersonEvn_id = PS.PersonEvn_id and EPLDO.Lpu_id {$getLpuIdFilterString} "
			: " inner join v_EvnPLDispOrp EPLDO on PS.Person_id = EPLDO.Person_id and EPLDO.Lpu_id {$getLpuIdFilterString} ";
		$query .= " 
			left join YesNo IsFinish on IsFinish.YesNo_id = EPLDO.EvnPLDispOrp_IsFinish
			left join YesNo IsTwoStage on IsTwoStage.YesNo_id = EPLDO.EvnPLDispOrp_IsTwoStage
		";
		return $query;
	}

	public static function selectBody_EvnPLDispMigrant(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		return "
			inner join v_EvnPLDispMigrant EPLDM on PS.Person_id = EPLDM.Person_id and EPLDM.Lpu_id {$getLpuIdFilterString}
			left join v_Address UA on UA.Address_id = PS.UAddress_id
			left join v_Address PA on PA.Address_id = PS.PAddress_id
			left join v_ResultDispMigrant RDM on RDM.ResultDispMigrant_id = EPLDM.ResultDispMigrant_id
			left join lateral(
				select EVDD.EvnVizitDispDop_setDate
				from
					v_EvnVizitDispDop EVDD
					inner join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				where EVDD.EvnVizitDispDop_pid = EPLDM.EvnPLDispMigrant_id
				  and ST.SurveyType_Code = 152
				limit 1
			) as EVDD on true
		";
	}

	public static function selectBody_EvnPLDispDriver(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		return "
			inner join v_EvnPLDispDriver EPLDD on PS.Person_id = EPLDD.Person_id and EPLDD.Lpu_id {$getLpuIdFilterString}
			left join v_Address UA on UA.Address_id = PS.UAddress_id
			left join v_Address PA on PA.Address_id = PS.PAddress_id
			left join v_ResultDispDriver RDD on RDD.ResultDispDriver_id = EPLDD.ResultDispDriver_id
			left join lateral(
				select EVDD.EvnVizitDispDop_setDate
				from
					v_EvnVizitDispDop EVDD 
					inner join v_DopDispInfoConsent DDIC on EVDD.DopDispInfoConsent_id = DDIC.DopDispInfoConsent_id
					inner join v_SurveyTypeLink STL on STL.SurveyTypeLink_id = DDIC.SurveyTypeLink_id
					inner join v_SurveyType ST on ST.SurveyType_id = STL.SurveyType_id
				where EVDD.EvnVizitDispDop_pid = EPLDD.EvnPLDispDriver_id and ST.SurveyType_Code = 152
				limit 1
			) as EVDD on true
		";
	}

	public static function selectBody_GibtRegistry()
	{
		return "
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id
			inner join v_Diag D on D.Diag_id = PR.Diag_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_Morbus M on M.Morbus_id = PR.Morbus_id
			left join v_MorbusGEBT MG on MG.Morbus_id = M.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
		";
	}

	public static function selectBody_EvnERSBirthCertificate()
	{
		return "
			inner join v_EvnERSBirthCertificate ERS on ERS.Person_id = PS.Person_id
			left join v_ERSStatus ES on ES.ERSStatus_id = ERS.ERSStatus_id
			left join v_ERSCloseCauseType CCT on CCT.ERSCloseCauseType_id = ERS.ERSCloseCauseType_id
			left join lateral(
				select
					*
				from v_ErsRequest ER
				where ER.EvnERS_id = ERS.EvnERSBirthCertificate_id
				order by ER.ERSRequest_insDT desc
				limit 1
			) as ER on true
			left join v_ErsRequestType ERT on ERT.ErsRequestType_id = ER.ErsRequestType_id
			left join v_ErsRequestStatus ERSt on ERSt.ErsRequestStatus_id = ER.ErsRequestStatus_id
			left join lateral(
				select
					string_agg(ere.ERSRequestError_Descr, ', ') as ERSRequestError
				from ERSRequestError ere
				where ere.ERSRequest_id = ER.ErsRequest_id
			) as ERE on true
			left join lateral(
				select
					ES.ERSStatus_Name
				from v_EvnERSTicket ERT
				inner join v_ERSStatus ES on ES.ERSStatus_id = ERT.ERSStatus_id
				where ERT.ERSTicketType_id = 1 and ERT.EvnERSTicket_pid = ERS.EvnERSBirthCertificate_id
				limit 1
			) ticket1 on true
			left join lateral(
				select
					ES.ERSStatus_Name
				from v_EvnERSTicket ERT
				inner join v_ERSStatus ES on ES.ERSStatus_id = ERT.ERSStatus_id
				where ERT.ERSTicketType_id = 2 and ERT.EvnERSTicket_pid = ERS.EvnERSBirthCertificate_id
				limit 1
			) ticket2 on true
			left join lateral(
				select
					ES.ERSStatus_Name
				from v_EvnERSTicket ERT
				inner join v_ERSStatus ES on ES.ERSStatus_id = ERT.ERSStatus_id
				where ERT.ERSTicketType_id = 3 and ERT.EvnERSTicket_pid = ERS.EvnERSBirthCertificate_id
				limit 1
			) ticket31 on true
			left join lateral(
				select
					ES.ERSStatus_Name
				from v_EvnERSTicket ERT
				inner join v_ERSStatus ES on ES.ERSStatus_id = ERT.ERSStatus_id
				where ERT.ERSTicketType_id = 4 and ERT.EvnERSTicket_pid = ERS.EvnERSBirthCertificate_id
				limit 1
			) ticket32 on true
		";
	}

	public static function selectBody_HTMRegister()
	{
		return "
			r2.v_Register R
			inner join r2.v_HTMRegister HR on R.Register_id = HR.Register_id
			left join v_PersonState PS on PS.Person_id = R.Person_id
			left join v_PersonCardState PCS on PCS.Person_id = R.Person_id
			left join v_Lpu Lpu on Lpu.Lpu_id = PCS.Lpu_id
			left join v_EvnDirectionHTM EDH on EDH.EvnDirectionHTM_id = HR.EvnDirectionHTM_id
			left join v_Lpu LpuEDH on LpuEDH.Lpu_id = EDH.Lpu_sid
			left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = EDH.LpuSectionProfile_id
		";
	}

	public static function selectBody_SportRegistry()
	{
		return "
			left join dbo.SportRegister SR on PS.Person_id = SR.Person_id
			left join dbo.PersonRegisterOutCause OC on OC.PersonRegisterOutCause_id = SR.PersonRegisterOutCause_id
			inner join dbo.SportRegisterUMO SRUMO on SR.SportRegister_id = SRUMO.SportRegister_id
			left join dbo.InvalidGroupType IGT on IGT.InvalidGroupType_id = SRUMO.InvalidGroupType_id
			left join dbo.SportParaGroup SPG on SPG.SportParaGroup_id = SRUMO.SportParaGroup_id
			left join lateral(
				select *
				from dbo.MedPersonalCache
				where MedPersonal_id = SRUMO.MedPersonal_pid
				limit 1
			) as MPp on true
			left join dbo.PersonState MSFPSp on MSFPSp.Person_id = MPp.Person_id
			left join dbo.SportType ST on ST.SportType_id = SRUMO.SportType_id
			left join dbo.SportOrg SO on SO.SportOrg_id = SRUMO.SportOrg_id
			left join dbo.SportCategory SC on SC.SportCategory_id = SRUMO.SportCategory_id
			left join dbo.SportStage SS on SS.SportStage_id = SRUMO.SportStage_id
			left join dbo.SportTrainer STr on STr.SportTrainer_id = SRUMO.SportTrainer_id
			left join dbo.PersonState PSTr on PSTr.Person_id = STr.Person_id
			left join dbo.UMOResult UR on UR.UMOResult_id = SRUMO.UMOResult_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
		";
	}

	public static function selectBody_ONMKRegistry()
	{
		return "
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id in (select MorbusType_id from dbo.MorbusType where MorbusType_SysNick = 'onmk')
			inner join v_ONMKRegistry ONMKR on ONMKR.PersonRegister_id=PR.PersonRegister_id
			inner join v_Diag Dg on Dg.Diag_id=ONMKR.Diag_id
			inner join v_lpu Lp on Lp.Lpu_id=ONMKR.Lpu_id
			left join dbo.ConsciousType CT on CT.ConsciousType_id=ONMKR.ConsciousType_id
			left join v_RankinScale RS on RS.RankinScale_id=ONMKR.RankinScale_id
			left join v_LeaveType LT on LT.LeaveType_id=ONMKR.LeaveType_id
		";
	}

	public static function selectBody_PersonDopDispPlan(Search_model $callObject, $data)
	{
		$getLpuIdFilterString = $callObject->getLpuIdFilter($data);
		$query = "
			left join Sex on Sex.Sex_id = PS.Sex_id
			left join lateral(
				select
					epld.EvnPLDisp_id,
					epld.EvnPLDisp_setDate,
					epld.EvnPLDisp_disDate
				from v_EvnPLDisp epld 
				where epld.Person_id = PS.Person_id
				  and date_part('year', epld.EvnPLDisp_setDate) = :PersonDopDisp_Year
				  and epld.DispClass_id = :DispClass_id
				limit 1
			) as IsPersonDopDispPassed on true
		";
		if ($data["DispClass_id"] == 3) {
			$query .= " inner join v_PersonDispOrp DOr on DOr.Person_id = PS.Person_id and DOr.CategoryChildType_id IN (1,2,3,4) and DOr.Lpu_id {$getLpuIdFilterString}";
		}
		if ($data["DispClass_id"] == 7) {
			$query .= " inner join v_PersonDispOrp DOr on DOr.Person_id = PS.Person_id and DOr.CategoryChildType_id IN (5,6,7) and DOr.Lpu_id {$getLpuIdFilterString}";
		}
		if ($data["DispClass_id"] == 1) {
			$query .= " left join v_PersonPrivilege PP on PP.Person_id = PS.Person_id";
		}
		if ($data["DispClass_id"] == 5) {
			$query .= "
				left join lateral(
					select count(*) count
					from v_EvnPLDisp epld 
					where epld.Person_id = PS.Person_id
					  and date_part('year', epld.EvnPLDisp_setDate) = :PersonDopDisp_YearPrev
					  and epld.DispClass_id = 5
				) as EplDispProfLastYear on true
			";
		}
		$query .= "
			left join lateral(
				select ppl.PlanPersonList_id
				from
					v_PlanPersonList ppl 
					inner join v_PersonDopDispPlan pddp on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
					left join v_PlanPersonListStatus pddps on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
				where ppl.Person_id = PS.Person_id
				  and pddp.PersonDopDispPlan_Year = :PersonDopDisp_Year
				  and pddp.DispClass_id = :DispClass_id
				  and coalesce(PDDPS.PlanPersonListStatusType_id, 1) <> 4
				limit 1
			) as IsPersonDopDispPlanned on true
		";
		if ($data["Person_isOftenApplying"] || $data["Person_isNotApplyingLastYear"]) {
			$query .= "
				left join lateral(
					select count(*) count
					from v_EvnPL epl 
					where epl.Person_id = PS.Person_id and date_part('year', epl.EvnPL_setDate) = :PersonDopDisp_YearPrev
				) as EplLastYear on true
			";
		}
		if ($data["Person_isNotDispProf"]) {
			$query .= "
				left join lateral(
					select count(*) count
					from v_EvnPLDisp epld 
					where epld.Person_id = PS.Person_id and date_part('year', epld.EvnPLDisp_setDate) = :PersonDopDisp_YearPrev and epld.DispClass_id = 5
				) as EplDispProfLastYear on true
			";
		}
		if ($data["Person_isNotDispDop"]) {
			$query .= "
				left join lateral(
					select count(*) count
					from v_EvnPLDisp epld 
					where epld.Person_id = PS.Person_id and date_part('year', epld.EvnPLDisp_setDate) = :PersonDopDisp_YearPrev and epld.DispClass_id = 1
				) as EplDispDopLastYear on true
			";
		}
		return $query;
	}
	
	public static function selectBody_RzhdRegistry($callObject, $data)
	{
		return  "
			r2.v_Register R
			inner join r2.RegisterType RT on RT.RegisterType_id = R.RegisterType_id and RT.RegisterType_Code = 'RZHD'
			left join r2.v_RzhdRegistry RR on RR.Register_id = R.Register_id
			left join r2.RzhdWorkerSubgroup RWS on RWS.RzhdWorkerSubgroup_id = RR.RzhdWorkerSubgroup_id
			left join r2.RzhdWorkerGroup RWG on RWG.RzhdWorkerGroup_id = RWS.RzhdWorkerGroup_id
			left join r2.RzhdWorkerCategory RWC on RWC.RzhdWorkerCategory_id = RWG.RzhdWorkerCategory_id
			left join r2.RegisterDisCause RDC on RDC.RegisterDisCause_id = R.RegisterDisCause_id
			left join dbo.v_PersonState PS on PS.Person_id = R.Person_id
			left join dbo.v_PersonCardState PCS on PCS.Person_id = R.Person_id and PCS.LpuAttachType_id = 1
			left join dbo.v_Lpu Lpu on Lpu.Lpu_id = PCS.Lpu_id
			left join dbo.v_Org Org on Org.Org_id = Lpu.Org_id
		";
	}

	public static function selectBody_VenerRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyVener EN on EN.EvnNotifyVener_id = PR.EvnNotifyBase_id
			left join v_MorbusVener MO on MO.Morbus_id = coalesce(EN.Morbus_id, PR.Morbus_id)
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id  
		";
	}

	public static function selectBody_HIVRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyHIV EN on EN.EvnNotifyHIV_id = PR.EvnNotifyBase_id
			inner join v_Morbus M on M.Morbus_id = coalesce(EN.Morbus_id, PR.Morbus_id)
			inner join v_MorbusHIV MH on MH.Morbus_id = M.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id  
		";
	}

	public static function selectBody_FmbaRegistry()
	{
		return "
			inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_SysNick = 'fmba'
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.PersonRegisterType_id = PRT.PersonRegisterType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
			left join lateral(
				select count(*) Request
				from DrugRequestRow 
				where Person_id = PC.Person_id
			) as Drug on true
		";
	}

	public static function selectBody_LargeFamilyRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
			left join lateral(
				select count(*) Request
				from DrugRequestRow 
				where Person_id = PC.Person_id
			) as Drug on true
		";
	}

	public static function selectBody_TubRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyTub EN on EN.EvnNotifyTub_id = PR.EvnNotifyBase_id
			left join v_MorbusTub MO on MO.Morbus_id = coalesce(EN.Morbus_id,PR.Morbus_id)
			left join lateral(
				select Lpu_id
				from v_PersonCard
				where Person_id = PS.Person_id and LpuAttachType_id = 1
				limit 1
			) as PC on true
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id  
		";
	}

	public static function selectBody_ProfRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyProf EN on EN.EvnNotifyProf_id = PR.EvnNotifyBase_id
			left join v_MorbusProf MO on MO.Morbus_id = coalesce(EN.Morbus_id, PR.Morbus_id)
			left join v_MorbusProfDiag mpd on mpd.MorbusProfDiag_id = MO.MorbusProfDiag_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join lateral(
				select v_evndiag.Diag_id
				from v_evndiag
				where v_evndiag.Morbus_id = PR.Morbus_id
				  and v_evndiag.evndiag_pid is null
				order by v_evndiag.evndiag_setdate desc
				limit 1
			) EvnDiagProf on true
			left join v_Diag Diag on Diag.Diag_id = coalesce(EvnDiagProf.Diag_id, PR.Diag_id)
			left join v_Job job ON ps.Job_id = job.Job_id
			left join v_Org o on o.Org_id = job.Org_id
			left join v_PersonCardState pcs on pcs.Person_id = ps.Person_id and pcs.LpuAttachType_id = 1
		";
	}

	public static function selectBody_IBSRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyBase EN on EN.EvnNotifyBase_id = PR.EvnNotifyBase_id
			left join v_MorbusIBS MO on MO.Morbus_id = coalesce(EN.Morbus_id,PR.Morbus_id)
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(MO.Diag_nid,PR.Diag_id)
			left join v_IBSType IBSType on IBSType.IBSType_id = MO.IBSType_id
		";
	}

	public static function selectBody_EndoRegistry()
	{
		return "
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id
			inner join v_PersonRegisterEndo PRE on PRE.PersonRegister_id = PR.PersonRegister_id
			left join v_Lpu Lpu on Lpu.Lpu_id = PR.Lpu_iid
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
			left join v_CategoryLifeDegreeType CLDT on CLDT.CategoryLifeDegreeType_id = PRE.CategoryLifeDegreeType_id
			left join v_ProsthesType PT on PT.ProsthesType_id = PRE.ProsthesType_id
			left join v_MedPersonal MP on MP.MedPersonal_id = PR.MedPersonal_iid
		";
	}

	public static function selectBody_NephroRegistry($data)
	{
		$query = "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyNephro EN on EN.EvnNotifyNephro_id = PR.EvnNotifyBase_id
			left join v_MorbusNephro MO on MO.Morbus_id = coalesce(EN.Morbus_id,PR.Morbus_id)
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join lateral(
				select v_evndiag.Diag_id
				from v_evndiag
				where v_evndiag.Morbus_id = PR.Morbus_id
				  and v_evndiag.evndiag_pid is null
				order by v_evndiag.evndiag_setdate desc
				limit 1
			) EvnDiagNephro on true
			left join v_Diag Diag on Diag.Diag_id = coalesce(PR.Diag_id,EvnDiagNephro.Diag_id)
			left join lateral(
				select D.Diag_Code
				from
					v_Diag D
					inner join v_EvnDiagSpec EvnDiagSpec on EvnDiagSpec.Person_id = PS.Person_id and D.Diag_id = EvnDiagSpec.Diag_id
				where D.Diag_Code >= :Diab_Diag_Code_Start
				  and D.Diag_Code <= :Diab_Diag_Code_End
				  and D.DiagLevel_id = 4
				limit 1
			) DiabEvnDiagSpec on true
			left join lateral(
				select D.Diag_Code
				from
					v_Diag D
					inner join v_EvnSection EvnSection on EvnSection.Person_id = PS.Person_id and D.Diag_id = EvnSection.Diag_id
				where D.Diag_Code >= :Diab_Diag_Code_Start
				  and D.Diag_Code <= :Diab_Diag_Code_End
				  and D.DiagLevel_id = 4
				limit 1
			) DiabEvnSection on true
			left join lateral(
				select D.Diag_Code
				from
					v_Diag D
					inner join v_EvnVizitPL EvnVizitPL on EvnVizitPL.Person_id = PS.Person_id and D.Diag_id = EvnVizitPL.Diag_id
				where D.Diag_Code >= :Diab_Diag_Code_Start
				  and D.Diag_Code <= :Diab_Diag_Code_End
				  and D.DiagLevel_id = 4
				limit 1
			) DiabEvnVizitPL on true
			left join lateral(
				select D.Diag_Code
				from
					v_Diag D
					inner join v_EvnDiagPLSop EvnDiagPLSop on EvnDiagPLSop.Person_id = PS.Person_id and D.Diag_id = EvnDiagPLSop.Diag_id
				where D.Diag_Code >= :Diab_Diag_Code_Start
				  and D.Diag_Code <= :Diab_Diag_Code_End
				  and D.DiagLevel_id = 4
				limit 1
			) DiabEvnDiagPLSop on true
			left join lateral(
				select D.Diag_Code
				from
					v_Diag D
					inner join v_EvnDiagPS EvnDiagPS on EvnDiagPS.Person_id = PS.Person_id and D.Diag_id = EvnDiagPS.Diag_id
				where D.Diag_Code >= :Diab_Diag_Code_Start
				  and D.Diag_Code <= :Diab_Diag_Code_End
				  and D.DiagLevel_id = 4
				limit 1
			) DiabEvnDiagPS on true
			left join lateral(
				select D.Diag_Code
				from
					v_Diag D
					inner join v_EvnUslugaDispDop EvnUslugaDispDop on EvnUslugaDispDop.Person_id = PS.Person_id and D.Diag_id = EvnUslugaDispDop.Diag_id
				where D.Diag_Code >= :Diab_Diag_Code_Start
				  and D.Diag_Code <= :Diab_Diag_Code_End
				  and D.DiagLevel_id = 4
				limit 1
			) DiabEvnUslugaDispDop on true
			left join lateral(
				select D.Diag_Code
				from
					v_Diag D
					inner join v_EvnDiagDopDisp EvnDiagDopDisp on EvnDiagDopDisp.Person_id = PS.Person_id and D.Diag_id = EvnDiagDopDisp.Diag_id
				where D.Diag_Code >= :Diab_Diag_Code_Start
				  and D.Diag_Code <= :Diab_Diag_Code_End
				  and D.DiagLevel_id = 4
				limit 1
			) DiabEvnDiagDopDisp on true
			left join lateral(
				select EVPL.EvnVizitPL_setDate
				from
					v_EvnVizitPL EVPL
					left join v_MedStaffFact MSF on MSF.MedStaffFact_id = EVPL.MedStaffFact_id
					left join v_PostMed PM on PM.PostMed_id = MSF.Post_id
				where EVPL.Person_id = PS.Person_id and PM.PostMed_Code = 39
				order by EvnVizitPL_setDate desc
				limit 1
			) lastVizitDate on true
		";
		if (!empty($data["PersonRegisterType_id"])) {
			if (getRegionNick() == "ufa") {
				$query .= "
					left join v_NephroResultType NRT on NRT.NephroResultType_id = MO.NephroResultType_id
				";
			}
		}
		return $query;
	}

	public static function selectBody_PalliatRegistry()
	{
		return "
			inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_SysNick ilike :PersonRegisterType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.PersonRegisterType_id = PRT.PersonRegisterType_id
			left join v_MorbusPalliat MO on MO.Morbus_id = PR.Morbus_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Lpu LpuIns on LpuIns.Lpu_id = pr.Lpu_iid
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
			left join v_MorbusType MT on MT.MorbusType_id = PR.MorbusType_id
		";
	}

	public static function selectBody_PersonRegisterBase()
	{
		return "
			inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_SysNick ilike :PersonRegisterType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.PersonRegisterType_id = PRT.PersonRegisterType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Lpu LpuIns on LpuIns.Lpu_id = pr.Lpu_iid
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
			left join v_MorbusType MT on MT.MorbusType_id = PR.MorbusType_id
		";
	}

	public static function selectBody_NarkoRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyCrazy EC on EC.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
			left join v_EvnNotifyNarco EN on EN.EvnNotifyNarco_id = PR.EvnNotifyBase_id
			left join v_MorbusCrazy MO on MO.Morbus_id = coalesce(EN.Morbus_id,PR.Morbus_id)
			left join v_CrazyCauseEndSurveyType CCEST on CCEST.CrazyCauseEndSurveyType_id = MO.CrazyCauseEndSurveyType_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu2 on Lpu2.Lpu_id = PR.Lpu_iid
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join lateral(
				select CD.Diag_id
				from
					v_MorbusCrazyDiag MCD
					left join v_CrazyDiag CD on CD.CrazyDiag_id=MCD.CrazyDiag_id 
				where MCD.MorbusCrazy_id = MO.MorbusCrazy_id
				order by MCD.MorbusCrazyDiag_setDT desc
				limit 1
			) as CDiag on true
			left join v_Diag Diag on Diag.Diag_id = CDiag.Diag_id
			left join v_Diag PRDiag on PRDiag.Diag_id = PR.Diag_id
		";
	}

	public static function selectBody_CrazyRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyCrazy EN on EN.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
			left join v_MorbusCrazy MO on MO.Morbus_id = coalesce(EN.Morbus_id,PR.Morbus_id)
			left join v_CrazyCauseEndSurveyType CCEST on CCEST.CrazyCauseEndSurveyType_id = MO.CrazyCauseEndSurveyType_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Lpu Lpu2 on Lpu2.Lpu_id = PR.Lpu_iid
			left join lateral(
				select CD.Diag_id
				from
					v_MorbusCrazyDiag MCD
					left join v_CrazyDiag CD on CD.CrazyDiag_id=MCD.CrazyDiag_id 
				where MCD.MorbusCrazy_id=MO.MorbusCrazy_id
				order by MCD.MorbusCrazyDiag_setDT desc
				limit 1
			) as CDiag on true
			left join v_Diag Diag on Diag.Diag_id = CDiag.Diag_id
			left join v_Diag PRDiag on PRDiag.Diag_id = PR.Diag_id
		";
	}

	public static function selectBody_ACSRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			inner join v_Morbus M on M.Morbus_id = PR.Morbus_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyBase EN on EN.EvnNotifyBase_id = PR.EvnNotifyBase_id
			left join v_MorbusACS MA on MA.Morbus_id = M.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Lpu LpuAdd on LpuAdd.Lpu_id = PR.Lpu_iid
			left join v_Diag Diag on Diag.Diag_id = M.Diag_id
		";
	}

	public static function selectBody_OrphanRegistry()
	{
		return "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			inner join v_Morbus M on M.Morbus_id = PR.Morbus_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyOrphan EN on EN.EvnNotifyOrphan_id = PR.EvnNotifyBase_id
			left join v_MorbusOrphan MO on MO.Morbus_id = M.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = M.Diag_id
		";
	}

	public static function selectBody_PalliatNotify()
	{
		return "
			inner join v_EvnNotifyBase ENB on ENB.Person_id = PS.Person_id
			inner join v_PalliatNotify PN on PN.EvnNotifyBase_id = ENB.EvnNotifyBase_id
			inner join v_Diag Diag on Diag.Diag_id = PN.Diag_id
			inner join v_Lpu Lpu on Lpu.Lpu_id = ENB.Lpu_id
			left join v_Morbus M on M.Morbus_id = ENB.Morbus_id
			left join v_PersonRegister PR on PR.EvnNotifyBase_id = ENB.EvnNotifyBase_id
			left join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
			left join lateral(
				select PC.*
				from v_PersonCard PC
				where PC.Person_id = PS.Person_id
				  and (select dt from mv) between PC.PersonCard_begDate and coalesce(PC.PersonCard_endDate, (select dt from mv))
				  and PC.LpuAttachType_id = 1
				order by PC.PersonCard_id desc
				limit 1
			) PC on true
			left join v_Lpu AttachLpu on AttachLpu.Lpu_id = PC.Lpu_id
		";
	}

	public static function selectBody_EvnNotifyVener($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id"
			: "ENC.Person_id = PS.Person_id";
		return "
			inner join v_EvnNotifyVener ENC on {$code}
			inner join v_Morbus MO on MO.Morbus_id = ENC.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_PersonRegister PR on ENC.EvnNotifyVener_id = PR.EvnNotifyBase_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(MO.Diag_id, PR.Diag_id)
		";
	}
	
	public static function selectBody_EvnNotifyHIV($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "ENB.PersonEvn_id = PS.PersonEvn_id and ENB.Server_id = PS.Server_id"
			: "ENB.Person_id = PS.Person_id";
		$query = "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_EvnNotifyBase ENB on ENB.MorbusType_id = v_MorbusType.MorbusType_id and {$code}
			inner join v_Morbus MO on MO.Morbus_id = ENB.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_PersonRegister PR on ENB.EvnNotifyBase_id = PR.EvnNotifyBase_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(MO.Diag_id, PR.Diag_id)
			left join EvnClass on ENB.EvnClass_id = EvnClass.EvnClass_id
		";
		if (isset($data["HIVNotifyType_id"])) {
			$query .= "
				inner join v_HIVNotifyType HIVNotifyType on EvnClass.EvnClass_SysNick = HIVNotifyType.HIVNotifyType_SysNick
			";
		}
		return $query;
	}

	public static function selectBody_EvnNotifyTub($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id"
			: "ENC.Person_id = PS.Person_id";
		return "
			inner join v_EvnNotifyTub ENC on {$code}
			inner join v_Morbus MO on MO.Morbus_id = ENC.Morbus_id
			left join lateral(
				select Lpu_id
				from v_PersonCard
				where Person_id = PS.Person_id and LpuAttachType_id = 1
				limit 1
			) as PC on true
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_PersonRegister PR on ENC.EvnNotifyTub_id = PR.EvnNotifyBase_id and PR.PersonRegister_disDate is null
			left join v_Diag DiagENC on DiagENC.Diag_id = ENC.Diag_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(PR.Diag_id, MO.Diag_id)
		";
	}

	public static function selectBody_EvnNotifyProf($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id"
			: "ENC.Person_id = PS.Person_id";
		return "
			inner join v_EvnNotifyProf ENC on {$code}
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_PersonRegister PR on ENC.EvnNotifyProf_id = PR.EvnNotifyBase_id and PR.PersonRegister_disDate is null
			left join v_Diag Diag on Diag.Diag_id = coalesce(PR.Diag_id, ENC.Diag_id)
		";
	}

	public static function selectBody_EvnNotifyNephro($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id"
			: "ENC.Person_id = PS.Person_id";
		return "
			inner join v_EvnNotifyNephro ENC on {$code}
			inner join v_Morbus MO on MO.Morbus_id = ENC.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_PersonRegister PR on ENC.EvnNotifyNephro_id = PR.EvnNotifyBase_id and PR.PersonRegister_disDate is null
			left join v_Diag Diag on Diag.Diag_id = ENC.Diag_id
		";
	}

	public static function selectBody_EvnNotifyNarko($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id"
			: "ENC.Person_id = PS.Person_id";
		return "
			inner join v_EvnNotifyNarco ENC on {$code}
			inner join v_Morbus MO on MO.Morbus_id = ENC.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_PersonRegister PR on ENC.EvnNotifyNarco_id = PR.EvnNotifyBase_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(MO.Diag_id,PR.Diag_id)
		";
	}

	public static function selectBody_EvnNotifyCrazy($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "ENC.PersonEvn_id = PS.PersonEvn_id and ENC.Server_id = PS.Server_id"
			: "ENC.Person_id = PS.Person_id";
		return "
			inner join v_EvnNotifyCrazy ENC on {$code}
			inner join v_Morbus MO on MO.Morbus_id = ENC.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_PersonRegister PR on ENC.EvnNotifyCrazy_id = PR.EvnNotifyBase_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(MO.Diag_id, PR.Diag_id)
		";
	}

	public static function selectBody_EvnNotifyOrphan($data)
	{
		$code1 = ($data["PersonPeriodicType_id"] == 2)
			? "EvnNotifyOrphan.PersonEvn_id = PS.PersonEvn_id and EvnNotifyOrphan.Server_id = PS.Server_id"
			: "EvnNotifyOrphan.Person_id = PS.Person_id";
		$code2 = ($data["PersonPeriodicType_id"] == 2)
			? "EvnNotifyOrphanOut.PersonEvn_id = PS.PersonEvn_id and EvnNotifyOrphanOut.Server_id = PS.Server_id"
			: "EvnNotifyOrphanOut.Person_id = PS.Person_id";
		$code3 = ($data["PersonPeriodicType_id"] == 2)
			? "EvnDirectionOrphan.PersonEvn_id = PS.PersonEvn_id and EvnDirectionOrphan.Server_id = PS.Server_id"
			: "EvnDirectionOrphan.Person_id = PS.Person_id";
		return "
			left join lateral(
				select
					EvnNotifyOrphan.EvnNotifyOrphan_id,
					EvnNotifyOrphan.EvnNotifyOrphan_pid,
					EvnNotifyOrphan.EvnNotifyOrphan_setDT,
					'Направление на включение в регистр' as EvnNotifyType_Name,
					'EvnNotifyOrphan' as EvnNotifyType_SysNick,
					EvnNotifyOrphan.Morbus_id,
					EvnNotifyOrphan.EvnNotifyOrphan_niDate,
					EvnNotifyOrphan.Lpu_oid,
					EvnNotifyOrphan.Lpu_id,
					EvnNotifyOrphan.MedPersonal_id,
					EvnNotifyOrphan.pmUser_updId,
					PersonRegister.PersonRegister_id
				from
					v_EvnNotifyOrphan EvnNotifyOrphan 
					left join PersonRegister on EvnNotifyOrphan.EvnNotifyOrphan_id = PersonRegister.EvnNotifyBase_id
				where {$code1}
				union all
				select
					EvnNotifyOrphanOut.EvnNotifyOrphanOut_id as EvnNotifyOrphan_id,
					EvnNotifyOrphanOut.EvnNotifyOrphanOut_pid as EvnNotifyOrphan_pid,
					EvnNotifyOrphanOut.EvnNotifyOrphanOut_setDT as EvnNotifyOrphan_setDT,
					' Извещение на исключение из регистра' as EvnNotifyType_Name,
					'EvnNotifyOrphanOut' as EvnNotifyType_SysNick,
					EvnNotifyOrphanOut.Morbus_id,
					EvnNotifyOrphanOut.EvnNotifyOrphanOut_niDate as EvnNotifyOrphan_niDate,
					null as Lpu_oid,
					EvnNotifyOrphanOut.Lpu_id,
					EvnNotifyOrphanOut.MedPersonal_id,
					EvnNotifyOrphanOut.pmUser_updId,
					PersonRegister.PersonRegister_id
				from
					v_EvnNotifyOrphanOut EvnNotifyOrphanOut
					inner join PersonRegister on EvnNotifyOrphanOut.Morbus_id = PersonRegister.Morbus_id
				where {$code2}
				union all
				select
					EvnDirectionOrphan.EvnDirectionOrphan_id as EvnNotifyOrphan_id,
					EvnDirectionOrphan.EvnDirectionOrphan_pid as EvnNotifyOrphan_pid,
					EvnDirectionOrphan.EvnDirectionOrphan_setDT as EvnNotifyOrphan_setDT,
					'Направление на внесение изменений в регистр' as EvnNotifyType_Name,
					'EvnDirectionOrphan' as EvnNotifyType_SysNick,
					EvnDirectionOrphan.Morbus_id,
					null as EvnNotifyOrphan_niDate,
					null as Lpu_oid,
					EvnDirectionOrphan.Lpu_id,
					EvnDirectionOrphan.MedPersonal_id,
					EvnDirectionOrphan.pmUser_updId,
					PersonRegister.PersonRegister_id
				from
					v_EvnDirectionOrphan EvnDirectionOrphan
					inner join PersonRegister on EvnDirectionOrphan.Morbus_id = PersonRegister.Morbus_id
				where {$code3}
			) as ENO on true
			inner join v_Morbus MO on MO.Morbus_id = ENO.Morbus_id 
			left join v_PersonRegister PR on ENO.PersonRegister_id = PR.PersonRegister_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(MO.Diag_id, PR.Diag_id)
			left join v_MedPersonal MP on MP.MedPersonal_id = ENO.MedPersonal_id and MP.Lpu_id = ENO.Lpu_id
		";
	}

	public static function selectBody_EvnNotifyRegister($data)
	{
		$joinEvnNotifyRegisterOn = ($data["PersonPeriodicType_id"] == 2)
			?"E.PersonEvn_id = PS.PersonEvn_id and E.Server_id = PS.Server_id"
			:"E.Person_id = PS.Person_id";
		return "
			inner join Evn E on {$joinEvnNotifyRegisterOn}
			inner join v_EvnNotifyRegister EN on EN.EvnNotifyRegister_id = E.Evn_id
			inner join v_EvnNotifyBase ENB on ENB.EvnNotifyBase_id = E.Evn_id
			inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = EN.PersonRegisterType_id
			inner join v_NotifyType NT on NT.NotifyType_id = EN.NotifyType_id
			left join v_PersonRegister PR on (EN.NotifyType_id in (2, 3) and PR.PersonRegister_id = EN.PersonRegister_id) or (1 = EN.NotifyType_id and PR.EvnNotifyBase_id = EN.EvnNotifyRegister_id)
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu AttachLpu on AttachLpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(EN.Diag_id,PR.Diag_id)
			left join v_Diag DiagGroup on DiagGroup.Diag_id = Diag.Diag_pid
			left join v_MedPersonal MP on MP.MedPersonal_id = ENB.MedPersonal_id and MP.Lpu_id = E.Lpu_id
			left join v_Lpu Lpu on Lpu.Lpu_id = E.Lpu_id
			left join v_MorbusType MT on MT.MorbusType_id = ENB.MorbusType_id
		";
	}

	public static function selectBody_HepatitisRegistry($data)
	{
		$query = "
			inner join v_MorbusType on v_MorbusType.MorbusType_SysNick = :MorbusType_SysNick
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = v_MorbusType.MorbusType_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyHepatitis ENH on ENH.EvnNotifyHepatitis_id = PR.EvnNotifyBase_id
			left join v_MorbusHepatitis MH on MH.Morbus_id = coalesce(ENH.Morbus_id, PR.Morbus_id)
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
			left join lateral(
				select
					HepatitisDiagType_id,
					MorbusHepatitisDiag_setDT,
					HepatitisDiagActiveType_id,
					HepatitisFibrosisType_id
				from v_MorbusHepatitisDiag MHD
				where MH.MorbusHepatitis_id = MHD.MorbusHepatitis_id
				order by MorbusHepatitisDiag_setDT desc
				limit 1
			) as MHD on true
			left join v_HepatitisDiagType HDT on HDT.HepatitisDiagType_id = MHD.HepatitisDiagType_id  
			left join lateral(
				select
					MorbusHepatitisQueue_Num,
					HepatitisQueueType_id,
					MorbusHepatitisQueue_IsCure
				from v_MorbusHepatitisQueue MHQ
				where MH.MorbusHepatitis_id = MHQ.MorbusHepatitis_id
				order by MorbusHepatitisQueue_IsCure
				limit 1
			) as MHQ on true
			left join v_HepatitisQueueType HQT on HQT.HepatitisQueueType_id = MHQ.HepatitisQueueType_id  
			left join v_YesNo IsCure on IsCure.YesNo_id = coalesce(MHQ.MorbusHepatitisQueue_IsCure, 1) 
		";
		if ((isset($data["MorbusHepatitisLabConfirm_setDT_Range"][0]) && isset($data["MorbusHepatitisLabConfirm_setDT_Range"][1])) || !empty($data["HepatitisLabConfirmType_id"]) || !empty($data["MorbusHepatitisLabConfirm_Result"])) {
			$filterDop = "";
			if (isset($data["MorbusHepatitisLabConfirm_setDT_Range"][0]) && isset($data["MorbusHepatitisLabConfirm_setDT_Range"][1])) {
				$filterDop .= " and cast(MHLC.MorbusHepatitisLabConfirm_setDT as date) between cast(:MorbusHepatitisLabConfirm_setDT_Range_0 as date) and cast(:MorbusHepatitisLabConfirm_setDT_Range_1 as date) ";
			}
			if (!empty($data["HepatitisLabConfirmType_id"])) {
				$filterDop .= " and MHLC.HepatitisLabConfirmType_id = :HepatitisLabConfirmType_id ";
			}
			if (!empty($data["MorbusHepatitisLabConfirm_Result"])) {
				$filterDop .= " and MHLC.MorbusHepatitisLabConfirm_Result ilike '%'||:MorbusHepatitisLabConfirm_Result||'%' ";
			}
			$query .= " inner join v_MorbusHepatitisLabConfirm MHLC on MH.MorbusHepatitis_id = MHLC.MorbusHepatitis_id {$filterDop}";
		}
		if ((isset($data["MorbusHepatitisFuncConfirm_setDT_Range"][0]) && isset($data["MorbusHepatitisFuncConfirm_setDT_Range"][1])) || !empty($data["HepatitisFuncConfirmType_id"]) || !empty($data["MorbusHepatitisFuncConfirm_Result"])) {
			$filterDop = "";
			if (isset($data["MorbusHepatitisFuncConfirm_setDT_Range"][0]) && isset($data["MorbusHepatitisFuncConfirm_setDT_Range"][1])) {
				$filterDop .= " and cast(MHFC.MorbusHepatitisLabConfirm_setDT as date) between cast(:MorbusHepatitisFuncConfirm_setDT_Range_0 as date) and cast(:MorbusHepatitisFuncConfirm_setDT_Range_1 as date) ";
			}
			if (!empty($data["HepatitisFuncConfirmType_id"])) {
				$filterDop .= " and MHFC.HepatitisFuncConfirmType_id = :HepatitisFuncConfirmType_id ";
			}
			if (!empty($data["MorbusHepatitisFuncConfirm_Result"])) {
				$filterDop .= " and MHFC.MorbusHepatitisFuncConfirm_Result ilike '%'||:MorbusHepatitisFuncConfirm_Result||'%' ";
			}
			$query .= " inner join v_MorbusHepatitisFuncConfirm MHFC on MH.MorbusHepatitis_id = MHFC.MorbusHepatitis_id {$filterDop}";
		}
		if (isset($data["MorbusHepatitisCure_begDT"]) || isset($data["MorbusHepatitisCure_endDT"]) || !empty($data["HepatitisResultClass_id"]) || !empty($data["HepatitisSideEffectType_id"]) || !empty($data["MorbusHepatitisCure_Drug"])) {
			$filterDop = "";
			if (isset($data["MorbusHepatitisCure_begDT"])) {
				$filterDop .= " and cast(MHC.MorbusHepatitisCure_begDT as date) >= cast(:MorbusHepatitisCure_begDT as date) and cast(MHC.MorbusHepatitisCure_begDT as date) is not null ";
			}
			if (isset($data["MorbusHepatitisCure_endDT"])) {
				$filterDop .= " and cast(MHC.MorbusHepatitisCure_endDT as date) <= cast(:MorbusHepatitisCure_endDT as date) and cast(MHC.MorbusHepatitisCure_endDT as date) is not null ";
			}
			if (!empty($data["HepatitisResultClass_id"])) {
				$filterDop .= " and MHC.HepatitisResultClass_id = :HepatitisResultClass_id ";
			}
			if (!empty($data["HepatitisSideEffectType_id"])) {
				$filterDop .= " and MHC.HepatitisSideEffectType_id = :HepatitisSideEffectType_id ";
			}
			if (!empty($data["MorbusHepatitisCure_Drug"])) {
				$filterDop .= " and MHC.MorbusHepatitisCure_Drug ilike '%'||:MorbusHepatitisCure_Drug||'%' ";
			}
			$query .= " inner join v_MorbusHepatitisCure MHC on MH.MorbusHepatitis_id = MHC.MorbusHepatitis_id {$filterDop}";
		}
		return $query;
	}

	public static function selectBody_ReanimatRegistry($data)
	{
		$HowJoin = $data["HardOnly"] == 1 ? "inner" : "left";
		return "
			inner join v_ReanimatRegister RR on RR.Person_id = PS.Person_id 
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = RR.PersonRegisterOutCause_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			left join dbo.v_pmUserCache MP on MP.PMUser_id = RR.pmUser_insID  --  PR.pmUser_insID 
			left join v_EvnReanimatPeriod ERP2 on ERP2.EvnReanimatPeriod_id = RR.EvnReanimatPeriod_id
			left join v_EvnSection ES on ES.EvnSection_id = ERP2.EvnReanimatPeriod_pid
			left join v_EvnPS EPS on EPS.EvnPS_id = ES.EvnSection_pid 	
			left join v_Diag D on  D.Diag_id	= COALESCE(ES.Diag_id, EPS.Diag_pid)
			left join v_Lpu Lpu2 on Lpu2.Lpu_id = ES.Lpu_id					 				
			{$HowJoin} join (
				select
					1 as selrow,
					EvnReanimatPeriod_id
				from v_EvnReanimatPeriod ERP0 	
				where ERP0.EvnReanimatPeriod_disDT is null
				  and (
				    exists ( 
						select 1
						from v_EvnReanimatAction
						where EvnReanimatAction_pid = ERP0.EvnReanimatPeriod_id
						  and ReanimatActionType_SysNick = 'lung_ventilation'
						  and (EvnReanimatAction_disDT is null or EvnReanimatAction_disDT >= GetDate())
					) or exists (
						select 1
						from v_EvnScale
						where EvnScale_pid = ERP0.EvnReanimatPeriod_id
						  and ScaleType_id = (select ScaleType_id from dbo.ScaleType where ScaleType_SysNick = 'sofa' limit 1)
						  and EvnScale_setDT >= dbo.tzgetdate() + interval '3 days'
						  and EvnScale_Result > 2
					)
				  )
				) as ERP on ERP.EvnReanimatPeriod_id = RR.EvnReanimatPeriod_id					
		";
	}

	public static function selectBody_ReabRegistry($data)
	{
		$query = " left join v_PersonCard PC on PC.Person_id = PS.Person_id ";
		$query .= ($data["LpuAttachType_id"] > 0)
			?" and PC.LpuAttachType_id = :LpuAttachType_id "
			:" and PC.LpuAttachType_id = 1 ";
		$query .= "
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			inner join lateral(
				select distinct
					dd.Person_id as pPerson_id
				from r2.ReabEvent dd
				where dd.Person_id = PS.Person_id and dd.ReabEvent_Deleted = 1
			) as PR on true
		";
		return $query;
	}

	public static function selectBody_AdminVIPPerson($data)
	{
		$query = " left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1";
		$query .= ($data["LpuAttachType_id"] > 0)
			?" and PC.LpuAttachType_id = :LpuAttachType_id "
			:" and PC.LpuAttachType_id = 1 ";
		$query .= "    
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			inner join dbo.VIPPerson R on R.Person_id = Ps.Person_id
			inner join v_Lpu Lpu1 on Lpu1.Lpu_id = r.Lpu_id
			left join dbo.pmUserCache pmUser on pmUser.PMUser_id = R.pmUser_updID
		";
		return $query;
	}

	public static function selectBody_ZNOSuspectRegistry($data)
	{
		$query = " left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1 ";
		$query .= ($data["LpuAttachType_id"] > 0)
			?" and PC.LpuAttachType_id = :LpuAttachType_id "
			:" and PC.LpuAttachType_id = 1 ";
		$query .= "    
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			inner join dbo.ZNOSuspectRegistry ZNOReg on ZNOReg.Person_id = ps.Person_id
			inner join dbo.ZNOSuspectRout ZNORout on ZNORout.ZNOSuspectRegistry_id= ZNOReg.ZNOSuspectRegistry_id    
			inner join v_Diag d on d.Diag_id = ZNORout.Diag_id
			left join v_Diag dd on dd.Diag_id = ZNORout.Diag_Fid
		";
		return $query;
	}

	public static function selectBody_BskRegistry($data)
	{
		$query = "
			left join v_PersonCard PC on PC.Person_id = PS.Person_id
		";
		$query .= ($data["LpuAttachType_id"] > 0)
			?" and PC.LpuAttachType_id = :LpuAttachType_id "
			:" and PC.LpuAttachType_id = 1 ";
		$query .= "
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			left join lateral(
				select *
				from dbo.BSKRegistry r
				where r.Person_id = PS.Person_id
				  and r.MorbusType_id in (19,88,84,89,50,110,111,112,113)
				order by r.BSKRegistry_setDate desc
				limit 1
			) as R on true
			inner join lateral (
				select *
				from v_PersonRegister PR
				where 
					PR.Person_id = PS.Person_id and 
					PR.MorbusType_id in (19,88,84,89,50,110,111,112,113) and 
					coalesce(R.MorbusType_id, PR.MorbusType_id) = PR.MorbusType_id
				order by PR.PersonRegister_setDate desc
				limit 1
			) PR on true
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join lateral(
				select coalesce(rd.BSKRegistryData_AnswerText,rd.BSKRegistryData_data) as BSKLpuGospital_data
				from
					dbo.BSKRegistry r
					inner join dbo.BSKRegistryData rd on rd.BSKRegistry_id = r.BSKRegistry_id and rd.BSKObservElement_id = 304
				where r.Person_id = PR.Person_id
				  and r.MorbusType_id = 19
				order by r.BSKRegistry_setDate desc
				limit 1
			) as BSKLpuGospital on true
			left join lateral(
				select coalesce(to_char(rd.BSKRegistryData_AnswerDT,'yyyy-mm-dd hh24:mi:ss.mmm'),rd.BSKRegistryData_data) CHKV_data
				from
					dbo.BSKRegistry r
					inner join dbo.BSKRegistryData rd on rd.BSKRegistry_id = r.BSKRegistry_id and rd.BSKObservElement_id = 302 and coalesce(to_char(rd.BSKRegistryData_AnswerDT,'yyyy-mm-dd hh24:mi:ss.mmm'),rd.BSKRegistryData_data) != 'нет'
				where r.Person_id = PR.Person_id
				  and r.MorbusType_id = 19
				order by r.BSKRegistry_setDate desc
				limit 1
			) as BSKCKV on true
			left join lateral(
				select coalesce(to_char(rd.BSKRegistryData_AnswerDT,'yyyy-mm-dd hh24:mi:ss.mmm'),rd.BSKRegistryData_data) TLT_data
				from
					dbo.BSKRegistry r
					inner join dbo.BSKRegistryData rd on rd.BSKRegistry_id = r.BSKRegistry_id and rd.BSKObservElement_id = 274 
					inner join lateral(
						select LastR.BSKRegistry_setDate
						from  dbo.BSKRegistry LastR
						where LastR.Person_id = r.Person_id
						  and LastR.MorbusType_id = r.MorbusType_id
						order by LastR.BSKRegistry_setDate desc
						limit 1
					) LastR on true
				where r.Person_id = PR.Person_id
				  and r.MorbusType_id = 19
				  and r.BSKRegistry_setDate >= DATEADD('DAY', -3, LastR.BSKRegistry_setDate)
				  and coalesce(to_char(rd.BSKRegistryData_AnswerDT,'yyyy-mm-dd hh24:mi:ss.mmm'),rd.BSKRegistryData_data) != ''
				  and coalesce(to_char(rd.BSKRegistryData_AnswerDT,'yyyy-mm-dd hh24:mi:ss.mmm'),rd.BSKRegistryData_data) != 'нет'
				order by r.BSKRegistry_setDate desc
				limit 1
			) as BSKTLT on true
			left join lateral(
				select coalesce(to_char(rd.BSKRegistryData_AnswerDT,'yyyy-mm-dd hh24:mi:ss.mmm'),rd.BSKRegistryData_data) KAG_data
				from
					dbo.BSKRegistry r
					inner join dbo.BSKRegistryData rd on rd.BSKRegistry_id = r.BSKRegistry_id and rd.BSKObservElement_id = 413 and coalesce(to_char(rd.BSKRegistryData_AnswerDT,'yyyy-mm-dd hh24:mi:ss.mmm'),rd.BSKRegistryData_data) != 'нет'
				where r.Person_id = PR.Person_id
				  and r.MorbusType_id = 19
				order by r.BSKRegistry_setDate desc
				limit 1
			) as BSKKAG on true
			left join v_Diag DP on PR.Diag_id = DP.Diag_id
			left join lateral(
				select *
				from v_EvnPS
				where Person_id = PS.Person_id
				  and PR.diag_id =
				        case
				            when PR.diag_id = Diag_id then Diag_id
							when PR.diag_id = Diag_pid then Diag_pid
							when PR.diag_id = Diag_did then Diag_did
							else null
						end
				order by EvnPS_setDT desc
				limit 1
			) as EvnPSDD on true
			left join dbo.v_pmUserCache MP on MP.PMUser_id = R.pmUser_insID
		";
		return $query;
	}

	public static function selectBody_ECORegistry($data)
	{
		if (isset($data["isRegion"]) && $data["isRegion"] == 1) {
			$query = "
				inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id in (
					select MorbusType_id
					from dbo.MorbusType
					where MorbusType_SysNick = 'eco'
				)
				inner join (
					select
						vER.*,
						EmC.EmbrionCount_Name,
						ChildCount.EcoChildCountType_Name
					FROM dbo.v_ECORegistry vER
						left join v_EmbrionCount EmC on EmC.EmbrionCount_id = vER.EmbrionCount_id 
						left join v_EcoChildCountType ChildCount on ChildCount.EcoChildCountType_id = vER.EcoChildCountType_id 
                	where vER.PersonRegisterEco_AddDate in (
                		select 
                			max(vER1.PersonRegisterEco_AddDate)
                		from dbo.v_ECORegistry vER1
                		where vER.PersonRegister_id=vER1.PersonRegister_id
                	)
                ) ER on ER.Person_id = PS.Person_id
				left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_PersonPregnancy PP ON PP.PersonRegisterEco_id = ER.PersonRegisterEco_id 
				left join dbo.BirthSpecStac BSS on BSS.PersonRegister_id=PP.PersonRegister_id
				left join dbo.PregnancyResult PPR on PPR.PregnancyResult_id=BSS.PregnancyResult_id
				left join dbo.BirthSpecStac BSS1 on BSS1.BirthSpecStac_id=ER.BirthSpecStac_id
				left join dbo.PregnancyResult PPR1 on PPR1.PregnancyResult_id=BSS1.PregnancyResult_id						
				left join v_Lpu LpuUch ON LpuUch.Lpu_id = PR.Lpu_iid
			";
		} else {
			$query = "
				inner join v_PersonRegister PR ON PR.Person_id = PS.Person_id
					and PR.MorbusType_id in (
						select
							MorbusType_id
                        from dbo.MorbusType
						where MorbusType_SysNick = 'eco'
					)
				left join (
					select
						vER.*,
						EmC.EmbrionCount_Name,
						ChildCount.EcoChildCountType_Name
					from dbo.v_ECORegistry vER
						left join v_EmbrionCount EmC on EmC.EmbrionCount_id = vER.EmbrionCount_id 
						left join v_EcoChildCountType ChildCount on ChildCount.EcoChildCountType_id = vER.EcoChildCountType_id 
					where vER.PersonRegisterEco_AddDate in (
						select 
                			max(vER1.PersonRegisterEco_AddDate)
                		from dbo.v_ECORegistry vER1
                		where vER.PersonRegister_id=vER1.PersonRegister_id
                	)
				) ER on ER.Person_id = PS.Person_id
				left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_PersonPregnancy PP ON PP.PersonRegisterEco_id = ER.PersonRegisterEco_id 
				left join dbo.BirthSpecStac BSS on BSS.PersonRegister_id=PP.PersonRegister_id
				left join dbo.PregnancyResult PPR on PPR.PregnancyResult_id=BSS.PregnancyResult_id
				left join dbo.BirthSpecStac BSS1 on BSS1.BirthSpecStac_id=ER.BirthSpecStac_id
				left join dbo.PregnancyResult PPR1 on PPR1.PregnancyResult_id=BSS1.PregnancyResult_id						
				left join v_Lpu LpuUch ON LpuUch.Lpu_id = :PersonRegister_Lpu_iid
			";
		}
		$query .= " 
			left join lateral(
				select
					PC.PersonCard_id,
					PC.Lpu_id,
					PC.LpuRegion_id,
					PC.LpuAttachType_id,
					PC.PersonCard_begDate,
					PC.PersonCard_endDate,
					PC.PersonCard_IsAttachCondit,
					PC.LpuRegion_fapid
				from
					dbo.v_PersonCard_all PC
					inner join dbo.Lpu on Lpu.Lpu_id = PC.Lpu_id 
					inner join dbo.Org on Org.Org_id = Lpu.Org_id
				where PC.Person_id = PS.Person_id 
				  and PC.LpuAttachType_id = 1
				order by pc.PersonCard_begDate desc
				limit 1
			) as PC on true
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
			left join v_Address PA on PA.Address_id = PS.UAddress_id
		";
		return $query;
	}

	public static function selectBody_IPRARegistry(Search_model $callObject)
	{
		$query = " 
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id and PR.MorbusType_id = 90
			left join lateral(
				select *
				from dbo.IPRARegistry 
				where Person_id = PS.Person_id
				order by IPRARegistry_issueDate desc
				limit 1
			) as IR on true
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_LpuRegion LR on LR.LpuRegion_id = PC.LpuRegion_id
			left join v_LpuRegion LR_Fap on LR_Fap.LpuRegion_id = PC.LpuRegion_fapid
			inner join v_IPRARegistryData ird on ird.IPRARegistry_id = IR.IPRARegistry_id
			left join v_Lpu DirLpu on DirLpu.Lpu_id = IR.IPRARegistry_DirectionLPU_id
			left join v_Lpu ConfLpu on ConfLpu.Lpu_id = IR.Lpu_id
			left join v_pmUserCache pmUser on IR.pmUser_confirmID = pmUser.pmUser_id
		";
		if ($callObject->getRegionNick() != "ufa") {
			$query .= "
				left join lateral(
					select case when (
						coalesce(ird.IPRARegistryData_MedRehab, 1) = 1 or exists (select * from v_MeasuresRehab where IPRARegistry_id = IR.IPRARegistry_id and MeasuresRehabType_id = 1)) and
						(coalesce(ird.IPRARegistryData_ReconstructSurg, 1) = 1 or exists (select * from v_MeasuresRehab where IPRARegistry_id = IR.IPRARegistry_id and MeasuresRehabType_id = 2)) and
						(coalesce(ird.IPRARegistryData_Orthotics, 1) = 1 or exists(select * from v_MeasuresRehab where IPRARegistry_id = IR.IPRARegistry_id and MeasuresRehabType_id = 3)
					) then 2 else 1 end as Value
				) as IsMeasuresComplete on true
			";
		}
		return $query;
	}

	public static function selectBody_GeriatricsRegistry()
	{
		return "
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id
			inner join v_Diag D on D.Diag_id = PR.Diag_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnNotifyGeriatrics ENG on ENG.EvnNotifyGeriatrics_id = PR.EvnNotifyBase_id
			left join v_Morbus M on M.Morbus_id = PR.Morbus_id
			left join v_MorbusGeriatrics MG on MG.Morbus_id = M.Morbus_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_SocStatus SS on SS.SocStatus_id = PS.SocStatus_id
			left join v_AgeNotHindrance ANH on ANH.AgeNotHindrance_id = MG.AgeNotHindrance_id
			left join v_YesNo IsKGO on IsKGO.YesNo_id = MG.MorbusGeriatrics_IsKGO
			left join v_YesNo IsWheelChair on IsWheelChair.YesNo_id = MG.MorbusGeriatrics_IsWheelChair
			left join v_YesNo IsFallDown on IsFallDown.YesNo_id = MG.MorbusGeriatrics_IsFallDown
			left join v_YesNo IsWeightDecrease on IsWeightDecrease.YesNo_id = MG.MorbusGeriatrics_IsWeightDecrease
			left join v_YesNo IsCapacityDecrease on IsCapacityDecrease.YesNo_id = MG.MorbusGeriatrics_IsCapacityDecrease
			left join v_YesNo IsCognitiveDefect on IsCognitiveDefect.YesNo_id = MG.MorbusGeriatrics_IsCognitiveDefect
			left join v_YesNo IsMelancholia on IsMelancholia.YesNo_id = MG.MorbusGeriatrics_IsMelancholia
			left join v_YesNo IsEnuresis on IsEnuresis.YesNo_id = MG.MorbusGeriatrics_IsEnuresis
			left join v_YesNo IsPolyPragmasy on IsPolyPragmasy.YesNo_id = MG.MorbusGeriatrics_IsPolyPragmasy
		";
	}

	public static function selectBody_OnkoRegistry($data)
	{
		$query = "
			inner join v_PersonRegister PR on PR.Person_id = PS.Person_id
			left join v_PersonRegisterOutCause PROUT on PROUT.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
			left join v_EvnOnkoNotify EON on EON.EvnOnkoNotify_id = PR.EvnNotifyBase_id
			left join v_EvnOnkoNotifyNeglected EONN on EONN.EvnOnkoNotify_id = PR.EvnNotifyBase_id
			left join v_Morbus M on M.Morbus_id = coalesce(EON.Morbus_id, PR.Morbus_id)
			left join v_MorbusOnko MO on MO.Morbus_id = M.Morbus_id
			left join v_MorbusOnkoBase MOB on MOB.MorbusBase_id = M.MorbusBase_id
			left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
			left join v_OnkoDiag OnkoDiag on OnkoDiag.OnkoDiag_id = MO.OnkoDiag_mid
			left join v_TumorStage TumorStage on TumorStage.TumorStage_id = MO.TumorStage_id
			left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join lateral(
				select MOL2.MorbusOnkoLeave_id
				from
					v_EvnSection ES
					inner join v_MorbusOnkoLeave MOL2 on ES.EvnSection_id = MOL2.EvnSection_id
				where MOL2.Diag_id = PR.Diag_id
				  and ES.Person_id = PS.Person_id
				limit 1
			) as MOL on true
			left join lateral(
				select MOLd2.MorbusOnkoVizitPLDop_id
				from
					v_MorbusOnkoVizitPLDop MOLd2 
					inner join v_EvnVizitPL EVP on EVP.EvnVizitPL_id = MOLd2.EvnVizit_id
				where MOLd2.Diag_id = PR.Diag_id
				  and EVP.Person_id = PS.Person_id
				limit 1
			) MOV on true
		";
		if (!empty($data["PersonRegister_evnSection_Range"][0]) || !empty($data["PersonRegister_evnSection_Range"][1])) {
			$query .= "
				left join v_EvnSection ES on ES.Person_id = PS.Person_id
				left join v_Diag ESD on ES.Diag_id = ESD.Diag_id
			";
		}
		if (!empty($data["OnkoPersonStateType_id"])) {
			$query .= "
				left join v_MorbusOnkoBasePersonState MOBPS on MOBPS.MorbusOnkoBase_id = MOB.MorbusOnkoBase_id
			";
		}
		if(isset($data['PersonRegister_onkoDiagDeath'])){
			$query.="
					left join v_Diag DiagDeath on DiagDeath.Diag_id = mob.Diag_did
					";
		}
		return $query;
	}

	public static function selectBody_EvnOnkoNotify($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "EON.PersonEvn_id = PS.PersonEvn_id and EON.Server_id = PS.Server_id"
			: "EON.Person_id = PS.Person_id";
		$query = "
			inner join v_EvnOnkoNotify EON on {$code}
			inner join v_Morbus M on M.Morbus_id = EON.Morbus_id 
			inner join v_MorbusOnko MO on MO.Morbus_id = M.Morbus_id 
			left join lateral(
				select Lpu_id
				from v_PersonCard pc
				where pc.Person_id = PS.Person_id
				limit 1
			) as PC on true
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id 
			left join v_Lpu Lpu1 on Lpu1.Lpu_id = EON.Lpu_sid 
			left join v_Diag Diag on Diag.Diag_id = M.Diag_id 
			left join v_OnkoDiag OnkoDiag on OnkoDiag.OnkoDiag_id = MO.OnkoDiag_mid 
			left join v_TumorStage TumorStage on TumorStage.TumorStage_id = MO.TumorStage_id 
			left join v_EvnOnkoNotifyNeglected EONN on EONN.EvnOnkoNotify_id = EON.EvnOnkoNotify_id 
			left join v_PersonRegister PR on EON.EvnOnkoNotify_id = PR.EvnNotifyBase_id
		";
		return $query;
	}

	public static function selectBody_EvnNotifyHepatitis($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "ENH.PersonEvn_id = PS.PersonEvn_id and ENH.Server_id = PS.Server_id"
			: "ENH.Person_id = PS.Person_id";
		$query = "
			inner join v_EvnNotifyHepatitis ENH on {$code}
			inner join v_MorbusHepatitis MH on MH.Morbus_id = ENH.Morbus_id 
			left join lateral(
				select Lpu_id
				from v_PersonCard pc
				where pc.Person_id = PS.Person_id
				limit 1
			) as PC on true
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id 
			left join v_PersonRegister PR on ENH.EvnNotifyHepatitis_id = PR.EvnNotifyBase_id
			left join v_Diag Diag on Diag.Diag_id = coalesce(MH.Diag_id,PR.Diag_id)
		";
		return $query;
	}

	public static function selectBody_EvnInfectNotify($data)
	{
		$code = ($data["PersonPeriodicType_id"] == 2)
			? "EIN.PersonEvn_id = PS.PersonEvn_id and EIN.Server_id = PS.Server_id"
			: "EIN.Person_id = PS.Person_id";
		return "
			inner join v_EvnInfectNotify EIN on {$code}
			left join v_EvnVizitPL EVPL on EIN.EvnInfectNotify_pid = EVPL.EvnVizitPL_id
			left join v_EvnSection ES on EIN.EvnInfectNotify_pid = ES.EvnSection_id 
			left join lateral(
				select Lpu_id
				from v_PersonCard pc
				where pc.Person_id = PS.Person_id
				limit 1
			) as PC on true
			left join v_Lpu Lpu on Lpu.Lpu_id = PC.Lpu_id
			left join v_Diag Diag on Diag.Diag_id = EVPL.Diag_id
			left join v_Diag Diag1 on Diag1.Diag_id = ES.Diag_id
			left join v_MorbusDiag MD on MD.Diag_id = coalesce(Diag.Diag_id, Diag1.Diag_id)
			left join v_MorbusType MT on MT.MorbusType_id = MD.MorbusType_id
		";
	}

	//selectBody_EvnAggStom
}
