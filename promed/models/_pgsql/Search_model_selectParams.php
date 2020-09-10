<?php

class Search_model_selectParams
{
	public static function selectParams_CmpCallCard(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		if (array_key_exists("linkedLpuIdList", $data["session"])) {
			$filter .= " and CCC.Lpu_id in (" . implode(",", $data["session"]["linkedLpuIdList"]) . ")";
		} else if (!empty($data["Lpu_id"])) {
			$filter .= " and CCC.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["CmpCloseCard_id"])) {
			$filter .= " and CLC.CmpCloseCard_id = :CmpCloseCard_id";
			$queryParams["CmpCloseCard_id"] = $data["CmpCloseCard_id"];
		}
		if (!empty($data["CmpArea_gid"])) {
			$filter .= " and CCC.CmpArea_gid = :CmpArea_gid";
			$queryParams["CmpArea_gid"] = $data["CmpArea_gid"];
		}
		if (isset($data["CmpCallCard_Expo"])) {
			$filter .= " and CCC.CmpCallCard_Expo = :CmpCallCard_Expo";
			$queryParams["CmpCallCard_Expo"] = $data["CmpCallCard_Expo"];
		}
		if (!empty($data["CmpCallCard_IsAlco"])) {
			$filter .= " and CCC.CmpCallCard_IsAlco = :CmpCallCard_IsAlco";
			$queryParams["CmpCallCard_IsAlco"] = $data["CmpCallCard_IsAlco"];
		}
		if (!empty($data["CmpCallCard_IsPoli"])) {
			$filter .= " and CCC.CmpCallCard_IsPoli = :CmpCallCard_IsPoli";
			$queryParams["CmpCallCard_IsPoli"] = $data["CmpCallCard_IsPoli"];
		}
		if (!empty($data["CmpCallCard_isPaid"])) {
			$filter .= " and coalesce(CCC.CmpCallCard_isPaid, 1) = :CmpCallCard_isPaid";
			$queryParams["CmpCallCard_isPaid"] = $data["CmpCallCard_isPaid"];
		}
		if (!empty($data["CmpDiag_aid"])) {
			$filter .= " and CCC.CmpDiag_aid = :CmpDiag_aid";
			$queryParams["CmpDiag_aid"] = $data["CmpDiag_aid"];
		}
		if (!empty($data["CmpDiag_oid"])) {
			$filter .= " and CCC.CmpDiag_oid = :CmpDiag_oid";
			$queryParams["CmpDiag_oid"] = $data["CmpDiag_oid"];
		}
		if (!empty($data["CmpTalon_id"])) {
			$filter .= " and CCC.CmpTalon_id = :CmpTalon_id";
			$queryParams["CmpTalon_id"] = $data["CmpTalon_id"];
		}
		if (!empty($data["CmpTrauma_id"])) {
			$filter .= " and CCC.CmpTrauma_id = :CmpTrauma_id";
			$queryParams["CmpTrauma_id"] = $data["CmpTrauma_id"];
		}
		if (!empty($data["Diag_sid"])) {
			$filter .= " and CCC.Diag_sid = :Diag_sid";
			$queryParams["Diag_sid"] = $data["Diag_sid"];
		}
		if (!empty($data["Diag_uCode_From"])) {
			$filter .= " and UD.Diag_Code >= :Diag_uCode_From";
			$queryParams["Diag_uCode_From"] = $data["Diag_uCode_From"];
		}
		if (!empty($data["Diag_uCode_To"])) {
			$filter .= " and UD.Diag_Code <= :Diag_uCode_To";
			$queryParams["Diag_uCode_To"] = $data["Diag_uCode_To"];
		}
		if (!empty($data["Lpu_oid"])) {
			$filter .= " and CL.Lpu_id = :Lpu_oid";
			$queryParams["Lpu_oid"] = $data["Lpu_oid"];
		}
		if (!empty($data["CmpArea_id"])) {
			$filter .= " and CCC.CmpArea_id = :CmpArea_id";
			$queryParams["CmpArea_id"] = $data["CmpArea_id"];
		}
		if (!empty($data["CmpCallCard_City"])) {
			$filter .= " and CCC.CmpCallCard_City ilike :CmpCallCard_City||'%'";
			$queryParams["CmpCallCard_City"] = $data["CmpCallCard_City"];
		}
		if (!empty($data["CmpCallCard_Dom"])) {
			$filter .= " and CCC.CmpCallCard_Dom = :CmpCallCard_Dom";
			$queryParams["CmpCallCard_Dom"] = $data["CmpCallCard_Dom"];
		}
		if (!empty($data["CmpCallCard_Ktov"])) {
			$filter .= " and CCC.CmpCallerType_id = :CmpCallCard_Ktov";
			$queryParams["CmpCallCard_Ktov"] = $data["CmpCallCard_Ktov"];
		}
		if (!empty($data["CmpResult_Code_From"]) || !empty($data["CmpResult_Code_To"])) {
			$filter .= " and trim(CRES.CmpResult_Code) ~ '^[0-9\.]+$'";
			if (!empty($data["CmpResult_Code_From"])) {
				$filter .= " and trim(CRES.CmpResult_Code) >= :CmpResult_Code_From";
				$queryParams["CmpResult_Code_From"] = $data["CmpResult_Code_From"];
			}
			if (!empty($data["CmpResult_Code_To"])) {
				$filter .= " and trim(CRES.CmpResult_Code) <= :CmpResult_Code_To";
				$queryParams["CmpResult_Code_To"] = $data["CmpResult_Code_To"];
			}
		}
		if (!empty($data["CmpCallCardInputType_id"])) {
			$filter .= " and CCC.CmpCallCardInputType_id = :CmpCallCardInputType_id";
			$queryParams["CmpCallCardInputType_id"] = $data["CmpCallCardInputType_id"];
		}
		if (!empty($data["ResultDeseaseType_id"])) {
			$filter .= " and CCC.ResultDeseaseType_id = :ResultDeseaseType_id";
			$queryParams["ResultDeseaseType_id"] = $data["ResultDeseaseType_id"];
		}
		if (!empty($data["CmpCallCard_Kvar"])) {
			$filter .= " and CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
			$queryParams["CmpCallCard_Kvar"] = $data["CmpCallCard_Kvar"];
		}
		if (!empty($data["CmpCallCard_Line"])) {
			$filter .= " and CCC.CmpCallCard_Line = :CmpCallCard_Line";
			$queryParams["CmpCallCard_Line"] = $data["CmpCallCard_Line"];
		}
		if (!empty($data["CmpCallCard_Ngod"])) {
			$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
		}
		if ($callObject->regionNick == "penza") {
			if (!empty($data["CmpCallCard_NumvPr"])) {
				switch ($data["CmpCallCard_NumvPr"]) {
					case "А":
					case "П":
					case "И":
					case "К":
					case "Н":
						$filter .= " and CCC.CmpCallCard_NumvPr = :CmpCallCard_NumvPr";
						$queryParams["CmpCallCard_NumvPr"] = $data["CmpCallCard_NumvPr"];
						break;
					case "Все":
						$filter .= " and CCC.CmpCallCard_NumvPr is not null";
						break;
					case "Без признака":
						$filter .= " and CCC.CmpCallCard_NumvPr is null";
						break;
				}
			}
			if (!empty($data["CmpCallCard_NgodPr"])) {
				switch ($data["CmpCallCard_NgodPr"]) {
					case "А":
					case "П":
					case "И":
					case "К":
					case "Н":
						$filter .= " and CCC.CmpCallCard_NgodPr = :CmpCallCard_NgodPr";
						$queryParams["CmpCallCard_NgodPr"] = $data["CmpCallCard_NgodPr"];
						break;
					case "Все":
						$filter .= " and CCC.CmpCallCard_NgodPr is not null";
						break;
					case "Без признака":
						$filter .= " and CCC.CmpCallCard_NgodPr is null";
						break;
				}
			}
		}
		if (isset($data["CmpCallCard_Prty"])) {
			$filter .= " and CCC.CmpCallCard_Prty = :CmpCallCard_Prty";
			$queryParams["CmpCallCard_Prty"] = $data["CmpCallCard_Prty"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		if (!empty($data["LpuBuilding_id"])) {
			$filter .= " and CCC.LpuBuilding_id = :LpuBuilding_id";
			$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		if (!empty($data["Diag_Code_From"])) {
			$filter .= " and CLD.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (!empty($data["Diag_Code_To"])) {
			$filter .= " and CLD.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (!empty($data["Diag_id"])) {
			$filter .= " and CLC.Diag_id = :Diag_id";
			$queryParams["Diag_id"] = $data["Diag_id"];
		}
		if (!empty($data["EmergencyTeamSpec_id"])) {
			$filter .= " and ET.EmergencyTeamSpec_id = :EmergencyTeamSpec_id";
			$queryParams["EmergencyTeamSpec_id"] = $data["EmergencyTeamSpec_id"];
		}
		if (!empty($data["EmergencyTeamNum"])) {
			$filter .= " and CLC.EmergencyTeamNum = :EmergencyTeamNum";
			$queryParams["EmergencyTeamNum"] = $data["EmergencyTeamNum"];
		}
		if (!empty($data["CmpNumber_From"]) || !empty($data["CmpNumber_To"])) {
			if (!empty($data["CmpNumber_From"])) {
				$filter .= " and (trim(cast(CCC.CmpCallCard_Numv as varchar)) >= :CmpNumber_From)";
				$queryParams["CmpNumber_From"] = $data["CmpNumber_From"];
			}
			if (!empty($data["CmpNumber_To"])) {
				$filter .= " and (trim(cast(CCC.CmpCallCard_Numv as varchar)) <= :CmpNumber_To)";
				$queryParams["CmpNumber_To"] = $data["CmpNumber_To"];
			}
		}
		if (!empty($data["CmpNumberGod_From"]) || !empty($data["CmpNumberGod_To"])) {
			if (!empty($data["CmpNumberGod_From"])) {
				$filter .= " and trim(cast(CCC.CmpCallCard_Ngod as varchar)) >= :CmpNumberGod_From";
				$queryParams["CmpNumberGod_From"] = $data["CmpNumberGod_From"];
			}
			if (!empty($data["CmpNumberGod_To"])) {
				$filter .= " and trim(cast(CCC.CmpCallCard_Ngod as varchar)) <= :CmpNumberGod_To";
				$queryParams["CmpNumberGod_To"] = $data["CmpNumberGod_To"];
			}
		}
		if (!empty($data["PayType_id"])) {
			$filter .= " and CLC.PayType_id = :PayType_id";
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
		if (isset($data["CmpCallCard_prmDate_Range"][0])) {
			if (isset($data["CmpCallCard_begTime"])) {
				$filter .= " and CCC.CmpCallCard_prmDT >= :CmpCallCard_prmDate_Range_0::timestamp";
				$data["CmpCallCard_prmDate_Range"][0] .= " " . $data["CmpCallCard_begTime"] . ":00";
			} else {
				$filter .= " and CCC.CmpCallCard_prmDT >= :CmpCallCard_prmDate_Range_0::timestamp";
			}
			$queryParams["CmpCallCard_prmDate_Range_0"] = $data["CmpCallCard_prmDate_Range"][0];
		}
		if (isset($data["CmpCallCard_prmDate_Range"][1])) {
			if (isset($data["CmpCallCard_endTime"])) {
				$filter .= " and CCC.CmpCallCard_prmDT <= :CmpCallCard_prmDate_Range_1::timestamp";
				$data["CmpCallCard_prmDate_Range"][1] .= " " . $data["CmpCallCard_endTime"];
			} else {
				$filter .= " and CCC.CmpCallCard_prmDT <= :CmpCallCard_prmDate_Range_1::timestamp";
			}
			$queryParams["CmpCallCard_prmDate_Range_1"] = $data["CmpCallCard_prmDate_Range"][1];
		}
		if (isset($data["CmpCallCard_begTime"]) && !isset($data["CmpCallCard_prmDate_Range"][0])) {
			$filter .= " and CCC.CmpCallCard_prmDT::time >= :CmpCallCard_begTime::time";
			$queryParams["CmpCallCard_begTime"] = $data["CmpCallCard_begTime"];
		}
		if (isset($data["CmpCallCard_endTime"]) && !isset($data["CmpCallCard_prmDate_Range"][1])) {
			$filter .= " and CCC.CmpCallCard_prmDT::time <= :CmpCallCard_endTime::time";
			$queryParams["CmpCallCard_endTime"] = $data["CmpCallCard_endTime"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		if (isset($data["CmpCallCard_Sect"])) {
			$filter .= " and CCC.CmpCallCard_Sect = :CmpCallCard_Sect";
			$queryParams["CmpCallCard_Sect"] = $data["CmpCallCard_Sect"];
		}
		if (isset($data["CmpCallCard_Stan"])) {
			$filter .= " and CCC.CmpCallCard_Stan = :CmpCallCard_Stan";
			$queryParams["CmpCallCard_Stan"] = $data["CmpCallCard_Stan"];
		}
		if (in_array($callObject->regionNick, ["ekb", "perm"]) && isset($data["CmpCallCard_InRegistry"])) {
			$CmpCallCard_InRegistry = $data["CmpCallCard_InRegistry"];
			if ($CmpCallCard_InRegistry == 1) {
				$filter .= " and coalesce(CCC.CmpCallCard_IsInReg, 1) = 1";
			}
			if ($CmpCallCard_InRegistry == 2) {
				$filter .= " and coalesce(CCC.CmpCallCard_IsInReg, 1) = 2";
			}
		}
		if (!empty($data["CmpCallCard_Ulic"])) {
			$filter .= " and CCC.CmpCallCard_Ulic ilike :CmpCallCard_Ulic||'%'";
			$queryParams["CmpCallCard_Ulic"] = $data["CmpCallCard_Ulic"];
		}
		if (!empty($data["CmpCallType_id"])) {
			$filter .= " and CCC.CmpCallType_id = :CmpCallType_id";
			$queryParams["CmpCallType_id"] = $data["CmpCallType_id"];
		}
		if (!empty($data["CmpPlace_id"])) {
			$filter .= " and CCC.CmpPlace_id = :CmpPlace_id";
			$queryParams["CmpPlace_id"] = $data["CmpPlace_id"];
		}
		if (!empty($data["CmpProfile_cid"])) {
			$filter .= " and CCC.CmpProfile_cid = :CmpProfile_cid";
			$queryParams["CmpProfile_cid"] = $data["CmpProfile_cid"];
		}
		if (!empty($data["CmpReason_id"])) {
			$filter .= " and CCC.CmpReason_id = :CmpReason_id";
			$queryParams["CmpReason_id"] = $data["CmpReason_id"];
		}
		if (!empty($data["CmpResult_id"])) {
			$filter .= " and CCC.CmpResult_id = :CmpResult_id";
			$queryParams["CmpResult_id"] = $data["CmpResult_id"];
		}
		if (!empty($data["IsExtra"])) {
			$filter .= " and CCC.CmpCallCard_IsExtra = :CmpCallCard_IsExtra";
			$queryParams["CmpCallCard_IsExtra"] = $data["IsExtra"];
		}
		if (!empty($data["Lpu_ppdid"])) {
			$filter .= " and CCC.Lpu_ppdid = :Lpu_ppdid";
			$queryParams["Lpu_ppdid"] = $data["Lpu_ppdid"];
		}
		if (!empty($data["Lpu_hid"])) {
			$filter .= " and CCC.Lpu_hid = :Lpu_hid";
			$queryParams["Lpu_hid"] = $data["Lpu_hid"];
		}
		if (!empty($data["acceptPPD"])) {
			$filter .= " and CCC.Lpu_ppdid is not null";
			if ($data["acceptPPD"] == 1) {
				$filter .= " and acceptNmpFlag.CmpCallCardEvent_id is null";
			} else {
				$filter .= " and acceptNmpFlag.CmpCallCardEvent_id is not null";
			}
		}
		if ($data["CardKLSubRgn_id"] > 0) {
			$filter .= " and CCC.KLSubRgn_id = :CardKLSubRgn_id";
			$queryParams["CardKLSubRgn_id"] = $data["CardKLSubRgn_id"];
		}
		if ($data["CardKLCity_id"] > 0) {
			$filter .= " and CCC.KLCity_id = :CardKLCity_id";
			$queryParams["CardKLCity_id"] = $data["CardKLCity_id"];
		}
		if ($data["CardKLTown_id"] > 0) {
			$filter .= " and CCC.KLTown_id = :CardKLTown_id";
			$queryParams["CardKLTown_id"] = $data["CardKLTown_id"];
		}
		if ($data["CardKLStreet_id"] > 0) {
			$filter .= " and CCC.KLStreet_id = :CardKLStreet_id";
			$queryParams["CardKLStreet_id"] = $data["CardKLStreet_id"];
		}
		if (strlen($data["CardAddress_House"]) > 0) {
			$filter .= " and CCC.CmpCallCard_Dom = :CardAddress_House";
			$queryParams["CardAddress_House"] = $data["CardAddress_House"];
		}
		if (strlen($data["CardAddress_Corpus"]) > 0) {
			$filter .= " and CCC.CmpCallCard_Korp = :CardAddress_Corpus";
			$queryParams["CardAddress_Corpus"] = $data["CardAddress_Corpus"];
		}
		if (strlen($data["CardAddress_Office"]) > 0) {
			$filter .= " and CCC.CmpCallCard_Kvar = :CardAddress_Office";
			$queryParams["CardAddress_Office"] = $data["CardAddress_Office"];
		}
		if (!empty($data["CmpCallCard_Dokt"])) {
			$filter .= " and CCC.CmpCallCard_Dokt ilike :CmpCallCard_Dokt||'%'";
			$queryParams["CmpCallCard_Dokt"] = $data["CmpCallCard_Dokt"];
		}
		if (isset($data["CmpCallCard_Kakp"])) {
			$filter .= " and CCC.CmpCallCard_Kakp = :CmpCallCard_Kakp";
			$queryParams["CmpCallCard_Kakp"] = $data["CmpCallCard_Kakp"];
		}
		if (!empty($data["CmpCallCard_Kilo"])) {
			$filter .= " and CCC.CmpCallCard_Kilo = :CmpCallCard_Kilo";
			$queryParams["CmpCallCard_Kilo"] = $data["CmpCallCard_Kilo"];
		}
		if (!empty($data["CmpCallCard_Ncar"])) {
			$filter .= " and CCC.CmpCallCard_Ncar = :CmpCallCard_Ncar";
			$queryParams["CmpCallCard_Ncar"] = $data["CmpCallCard_Ncar"];
		}
		if (isset($data["CmpCallCard_Numb"])) {
			$filter .= " and (CCC.CmpCallCard_Numb = :CmpCallCard_Numb OR ET.EmergencyTeam_Num = :CmpCallCard_Numb OR CLC.EmergencyTeamNum = :CmpCallCard_Numb)";
			$queryParams["CmpCallCard_Numb"] = $data["CmpCallCard_Numb"];
		}
		if (!empty($data["CmpCallCard_Smpb"])) {
			$filter .= " and CCC.CmpCallCard_Smpb = :CmpCallCard_Smpb";
			$queryParams["CmpCallCard_Smpb"] = $data["CmpCallCard_Smpb"];
		}
		if (isset($data["CmpCallCard_Stbb"])) {
			$filter .= " and CCC.CmpCallCard_Stbb = :CmpCallCard_Stbb";
			$queryParams["CmpCallCard_Stbb"] = $data["CmpCallCard_Stbb"];
		}
		if (!empty($data["CmpCallCard_Stbr"])) {
			$filter .= " and CCC.CmpCallCard_Stbr = :CmpCallCard_Stbr";
			$queryParams["CmpCallCard_Stbr"] = $data["CmpCallCard_Stbr"];
		}
		if (!empty($data["CmpCallCard_Tab2"])) {
			$filter .= " and CCC.CmpCallCard_Tab2 = :CmpCallCard_Tab2";
			$queryParams["CmpCallCard_Tab2"] = $data["CmpCallCard_Tab2"];
		}
		if (!empty($data["CmpCallCard_Tab3"])) {
			$filter .= " and CCC.CmpCallCard_Tab3 = :CmpCallCard_Tab3";
			$queryParams["CmpCallCard_Tab3"] = $data["CmpCallCard_Tab3"];
		}
		if (!empty($data["CmpCallCard_Tab4"])) {
			$filter .= " and CCC.CmpCallCard_Tab4 = :CmpCallCard_Tab4";
			$queryParams["CmpCallCard_Tab4"] = $data["CmpCallCard_Tab4"];
		}
		if (!empty($data["CmpCallCard_Tabn"])) {
			$filter .= " and CCC.CmpCallCard_Tabn = :CmpCallCard_Tabn";
			$queryParams["CmpCallCard_Tabn"] = $data["CmpCallCard_Tabn"];
		}
		if (!empty($data["CmpProfile_bid"])) {
			$filter .= " and (CCC.CmpProfile_bid = :CmpProfile_bid OR ET.EmergencyTeamSpec_id = :CmpProfile_bid OR CLC.EmergencyTeamSpec_id = :CmpProfile_bid)";
			$queryParams["CmpProfile_bid"] = $data["CmpProfile_bid"];
		}
		if (!empty($data["CLLpuBuilding_id"])) {
			$filter .= " and CCC.LpuBuilding_id = :CLLpuBuilding_id";
			$queryParams["CLLpuBuilding_id"] = $data["CLLpuBuilding_id"];
		}
		if (!empty($data["ETMedStaffFact_id"])) {
			$filter .= " and ET.EmergencyTeam_HeadShiftWorkPlace = :ETMedStaffFact_id";
			$queryParams["ETMedStaffFact_id"] = $data["ETMedStaffFact_id"];
		}
		if (!empty($data["CmpCallCard_D201"])) {
			$filter .= " and substring(CCC.CmpCallCard_D201 from 1 for position(' ' in CCC.CmpCallCard_D201)) = :CmpCallCard_D201";
			$queryParams["CmpCallCard_D201"] = $data["CmpCallCard_D201"];
		}
		if (!empty($data["CmpCallCard_Dlit"])) {
			$filter .= " and CCC.CmpCallCard_Dlit = :CmpCallCard_Dlit";
			$queryParams["CmpCallCard_Dlit"] = $data["CmpCallCard_Dlit"];
		}
		if (!empty($data["CmpCallCard_Dsp1"])) {
			$filter .= " and substring(CCC.CmpCallCard_Dsp1 from 1 for position(' ' in CCC.CmpCallCard_Dsp1)) = :CmpCallCard_Dsp1";
			$queryParams["CmpCallCard_Dsp1"] = $data["CmpCallCard_Dsp1"];
		}
		if (!empty($data["CmpCallCard_Dsp2"])) {
			$filter .= " and substring(CCC.CmpCallCard_Dsp2 from 1 for position(' ' in CCC.CmpCallCard_Dsp2)) = :CmpCallCard_Dsp2";
			$queryParams["CmpCallCard_Dsp2"] = $data["CmpCallCard_Dsp2"];
		}
		if (!empty($data["CmpCallCard_Dsp3"])) {
			$filter .= " and substring(CCC.CmpCallCard_Dsp3 from 1 for position(' ' in CCC.CmpCallCard_Dsp3)) = :CmpCallCard_Dsp3";
			$queryParams["CmpCallCard_Dsp3"] = $data["CmpCallCard_Dsp3"];
		}
		if (!empty($data["CmpCallCard_Dspp"])) {
			$filter .= " and substring(CCC.CmpCallCard_Dspp from 1 for position(' ' in CCC.CmpCallCard_Dspp)) = :CmpCallCard_Dspp";
			$queryParams["CmpCallCard_Dspp"] = $data["CmpCallCard_Dspp"];
		}
		if (!empty($data["CmpCallCard_Prdl"])) {
			$filter .= " and CCC.CmpCallCard_Prdl = :CmpCallCard_Prdl";
			$queryParams["CmpCallCard_Prdl"] = $data["CmpCallCard_Prdl"];
		}
		if (!empty($data["CmpCallCard_Smpp"])) {
			$filter .= " and CCC.CmpCallCard_Smpp = :CmpCallCard_Smpp";
			$queryParams["CmpCallCard_Smpp"] = $data["CmpCallCard_Smpp"];
		}
		if (!empty($data["CmpCallCard_Vr51"])) {
			$filter .= " and substring(CCC.CmpCallCard_Vr51 from 1 for position(' ' in CCC.CmpCallCard_Vr51)) = :CmpCallCard_Vr51";
			$queryParams["CmpCallCard_Vr51"] = $data["CmpCallCard_Vr51"];
		}
	}

	public static function selectParams_CmpCloseCard(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		if (array_key_exists("linkedLpuIdList", $data["session"])) {
			$filter .= " and CCC.Lpu_id in (" . implode(",", $data["session"]["linkedLpuIdList"]) . ")";
		} else if (!empty($data["Lpu_id"])) {
			$filter .= " and CCC.Lpu_id = :Lpu_id";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["CmpCallCard_isPaid"])) {
			$filter .= " and coalesce(CCC.CmpCallCard_isPaid, 1) = :CmpCallCard_isPaid";
			$queryParams["CmpCallCard_isPaid"] = $data["CmpCallCard_isPaid"];
		}
		if (!empty($data["CmpCloseCard_id"])) {
			$filter .= " and CLC.CmpCloseCard_id = :CmpCloseCard_id";
			$queryParams["CmpCloseCard_id"] = $data["CmpCloseCard_id"];
		}
		if (!empty($data["CmpCallCard_IsAlco"])) {
			$filter .= " and CLC.isAlco = :CmpCallCard_IsAlco";
			$queryParams["CmpCallCard_IsAlco"] = $data["CmpCallCard_IsAlco"];
		}
		if (!empty($data["Diag_uCode_From"])) {
			$filter .= " and UD.Diag_Code >= :Diag_uCode_From";
			$queryParams["Diag_uCode_From"] = $data["Diag_uCode_From"];
		}
		if (!empty($data["Diag_uCode_To"])) {
			$filter .= " and UD.Diag_Code <= :Diag_uCode_To";
			$queryParams["Diag_uCode_To"] = $data["Diag_uCode_To"];
		}
		if (!empty($data["Lpu_oid"])) {
			$filter .= " and CL.Lpu_id = :Lpu_oid";
			$queryParams["Lpu_oid"] = $data["Lpu_oid"];
		}
		if (!empty($data["CmpCallCard_City"])) {
			$filter .= " and CCC.CmpCallCard_City ilike :CmpCallCard_City||'%'";
			$queryParams["CmpCallCard_City"] = $data["CmpCallCard_City"];
		}
		if (!empty($data["CmpCallCard_Dom"])) {
			$filter .= " and CCC.CmpCallCard_Dom = :CmpCallCard_Dom";
			$queryParams["CmpCallCard_Dom"] = $data["CmpCallCard_Dom"];
		}
		if (!empty($data["CmpCallCard_Ktov"])) {
			$filter .= " and CCC.CmpCallerType_id = :CmpCallCard_Ktov";
			$queryParams["CmpCallCard_Ktov"] = $data["CmpCallCard_Ktov"];
		}
		if (!empty($data["Diag_Code_From"])) {
			$filter .= " and CLD.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (!empty($data["Diag_Code_To"])) {
			$filter .= " and CLD.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (!empty($data["CmpResult_Code_From"]) || !empty($data["CmpResult_Code_To"])) {
			$filter .= " and trim(CRES.CmpResult_Code) ~ '^[0-9\.]+$'";
			if (!empty($data["CmpResult_Code_From"])) {
				$filter .= " and trim(CRES.CmpResult_Code) >= :CmpResult_Code_From";
				$queryParams["CmpResult_Code_From"] = $data["CmpResult_Code_From"];
			}
			if (!empty($data["CmpResult_Code_To"])) {
				$filter .= " and trim(CRES.CmpResult_Code) <= :CmpResult_Code_To";
				$queryParams["CmpResult_Code_To"] = $data["CmpResult_Code_To"];
			}
		}
		if (!empty($data["CmpCallCardInputType_id"])) {
			$filter .= " and CCC.CmpCallCardInputType_id = :CmpCallCardInputType_id";
			$queryParams["CmpCallCardInputType_id"] = $data["CmpCallCardInputType_id"];
		}
		if (!empty($data["ResultDeseaseType_id"])) {
			$filter .= " and CCC.ResultDeseaseType_id = :ResultDeseaseType_id";
			$queryParams["ResultDeseaseType_id"] = $data["ResultDeseaseType_id"];
		}
		if (!empty($data["CmpCallCard_Kvar"])) {
			$filter .= " and CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
			$queryParams["CmpCallCard_Kvar"] = $data["CmpCallCard_Kvar"];
		}
		if (!empty($data["CmpCallCard_Line"])) {
			$filter .= " and CCC.CmpCallCard_Line = :CmpCallCard_Line";
			$queryParams["CmpCallCard_Line"] = $data["CmpCallCard_Line"];
		}
		if (!empty($data["CmpCallCard_Ngod"])) {
			$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
		}
		if (isset($data["CmpCallCard_Prty"])) {
			$filter .= " and CCC.CmpCallCard_Prty = :CmpCallCard_Prty";
			$queryParams["CmpCallCard_Prty"] = $data["CmpCallCard_Prty"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		if (!empty($data["IsExtra"])) {
			$filter .= " and CCC.CmpCallCard_IsExtra = :CmpCallCard_IsExtra";
			$queryParams["CmpCallCard_IsExtra"] = $data["IsExtra"];
		}
		if (!empty($data["Lpu_ppdid"])) {
			$filter .= " and CCC.Lpu_ppdid = :Lpu_ppdid";
			$queryParams["Lpu_ppdid"] = $data["Lpu_ppdid"];
		}
		if (!empty($data["Lpu_hid"])) {
			$filter .= " and CCC.Lpu_hid = :Lpu_hid";
			$queryParams["Lpu_hid"] = $data["Lpu_hid"];
		}
		if (!empty($data["acceptPPD"])) {
			$filter .= " and CCC.Lpu_ppdid is not null";
			$filter .= ($data["acceptPPD"] == 1)
				? " and acceptNmpFlag.CmpCallCardEvent_id is null"
				: " and acceptNmpFlag.CmpCallCardEvent_id is not null";
		}
		if (!empty($data["isActive"])) {
			$filter .= ($data["isActive"] == 1)
				? " and isActiveCombo.CmpCloseCardCombo_id is null"
				: " and isActiveCombo.CmpCloseCardCombo_id is not null";
		}
		if (!empty($data["PersonSocial_id"])) {
			$filter .= " and socialCombo.CmpCloseCardCombo_id = :PersonSocial_id";
			$queryParams["PersonSocial_id"] = $data["PersonSocial_id"];
		}
		if (!empty($data["Diag_id"])) {
			$filter .= " and CLC.Diag_id = :Diag_id";
			$queryParams["Diag_id"] = $data["Diag_id"];
		}
		if (!empty($data["EmergencyTeamSpec_id"])) {
			$filter .= " and (CLC.EmergencyTeamSpec_id = :EmergencyTeamSpec_id_CLC OR ET.EmergencyTeamSpec_id = :EmergencyTeamSpec_id_ET)";
			$queryParams["EmergencyTeamSpec_id_CLC"] = $data["EmergencyTeamSpec_id"];
			$queryParams["EmergencyTeamSpec_id_ET"] = $data["EmergencyTeamSpec_id"];
		}
		if (!empty($data["EmergencyTeamNum"])) {
			$filter .= " and CLC.EmergencyTeamNum = :EmergencyTeamNum";
			$queryParams["EmergencyTeamNum"] = $data["EmergencyTeamNum"];
		}
		if (!empty($data["CmpNumber_From"]) || !empty($data["CmpNumber_To"])) {
			if (!empty($data["CmpNumber_From"])) {
				$filter .= " and trim(cast(CCC.CmpCallCard_Numv as varchar)) >= :CmpNumber_From";
				$queryParams["CmpNumber_From"] = $data["CmpNumber_From"];
			}
			if (!empty($data["CmpNumber_To"])) {
				$filter .= " and trim(cast(CCC.CmpCallCard_Numv as varchar)) <= :CmpNumber_To";
				$queryParams["CmpNumber_To"] = $data["CmpNumber_To"];
			}
		}
		if (!empty($data["CmpNumberGod_From"]) || !empty($data["CmpNumberGod_To"])) {
			if (!empty($data["CmpNumberGod_From"])) {
				$filter .= " and trim(cast(CCC.CmpCallCard_Ngod as varchar)) >= :CmpNumberGod_From";
				$queryParams["CmpNumberGod_From"] = $data["CmpNumberGod_From"];
			}
			if (!empty($data["CmpNumberGod_To"])) {
				$filter .= " and trim(cast(CCC.CmpCallCard_Ngod as varchar)) <= :CmpNumberGod_To";
				$queryParams["CmpNumberGod_To"] = $data["CmpNumberGod_To"];
			}
		}
		if (!empty($data["PayType_id"])) {
			$filter .= " and CLC.PayType_id = :PayType_id";
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
		if (isset($data["CmpCallCard_prmDate_Range"][0])) {
			if (isset($data["CmpCallCard_begTime"])) {
				$filter .= " and CCC.CmpCallCard_prmDT >= :CmpCallCard_prmDate_Range_0::timestamp";
				$data["CmpCallCard_prmDate_Range"][0] .= " " . $data["CmpCallCard_begTime"];
			} else {
				$filter .= " and CCC.CmpCallCard_prmDT >= :CmpCallCard_prmDate_Range_0::timestamp";
			}
			$queryParams["CmpCallCard_prmDate_Range_0"] = $data["CmpCallCard_prmDate_Range"][0];
		}
		if (isset($data["CmpCallCard_prmDate_Range"][1])) {
			if (isset($data["CmpCallCard_endTime"])) {
				$filter .= " and CCC.CmpCallCard_prmDT <= :CmpCallCard_prmDate_Range_1::timestamp";
				$data["CmpCallCard_prmDate_Range"][1] .= " " . $data["CmpCallCard_endTime"];
			} else {
				$filter .= " and CCC.CmpCallCard_prmDT <= :CmpCallCard_prmDate_Range_1::timestamp";
			}
			$queryParams["CmpCallCard_prmDate_Range_1"] = $data["CmpCallCard_prmDate_Range"][1];
		}
		if (isset($data["CmpCallCard_begTime"]) && !isset($data["CmpCallCard_prmDate_Range"][0])) {
			$filter .= " and CCC.CmpCallCard_prmDT::time >= :CmpCallCard_begTime::time";
			$queryParams["CmpCallCard_begTime"] = $data["CmpCallCard_begTime"];
		}
		if (isset($data["CmpCallCard_endTime"]) && !isset($data["CmpCallCard_prmDate_Range"][1])) {
			$filter .= " and CCC.CmpCallCard_prmDT::time <= :CmpCallCard_endTime::time";
			$queryParams["CmpCallCard_endTime"] = $data["CmpCallCard_endTime"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		if (isset($data["CmpCallCard_Sect"])) {
			$filter .= " and CCC.CmpCallCard_Sect = :CmpCallCard_Sect";
			$queryParams["CmpCallCard_Sect"] = $data["CmpCallCard_Sect"];
		}
		if (isset($data["CmpCallCard_Stan"])) {
			$filter .= " and CCC.CmpCallCard_Stan = :CmpCallCard_Stan";
			$queryParams["CmpCallCard_Stan"] = $data["CmpCallCard_Stan"];
		}
		if (isset($data["CmpCallCard_InRegistry"])) {
			$CmpCallCard_InRegistry = $data["CmpCallCard_InRegistry"];
			$IsInRegField = (in_array($callObject->regionNick, ["ekb", "perm"]) ? "CCC.CmpCallCard_IsInReg" : "CLC.CmpCloseCard_IsInReg");
			if ($CmpCallCard_InRegistry == 1) {
				$filter .= " and coalesce({$IsInRegField},1) = 1";
			}
			if ($CmpCallCard_InRegistry == 2) {
				$filter .= " and coalesce({$IsInRegField},1) = 2";
			}
		}
		if (!empty($data["CmpCallCard_Ulic"])) {
			$filter .= " and CCC.CmpCallCard_Ulic ilike :CmpCallCard_Ulic||'%'";
			$queryParams["CmpCallCard_Ulic"] = $data["CmpCallCard_Ulic"];
		}
		if (!empty($data["CmpCallType_id"])) {
			$filter .= " and CCC.CmpCallType_id = :CmpCallType_id";
			$queryParams["CmpCallType_id"] = $data["CmpCallType_id"];
		}
		if (!empty($data["CmpPlace_id"])) {
			$filter .= " and CCC.CmpPlace_id = :CmpPlace_id";
			$queryParams["CmpPlace_id"] = $data["CmpPlace_id"];
		}
		if (!empty($data["CmpProfile_cid"])) {
			$filter .= " and CCC.CmpProfile_cid = :CmpProfile_cid";
			$queryParams["CmpProfile_cid"] = $data["CmpProfile_cid"];
		}
		if (!empty($data["CmpReason_id"])) {
			$filter .= " and CCC.CmpReason_id = :CmpReason_id";
			$queryParams["CmpReason_id"] = $data["CmpReason_id"];
		}
		if (!empty($data["ResultUfa_id"])) {
			$filter .= " and resultCombo.CmpCloseCardCombo_id = :ResultUfa_id";
			$queryParams["ResultUfa_id"] = $data["ResultUfa_id"];
		}
		if (!empty($data["CmpResult_id"])) {
			$filter .= " and coalesce(CLC.CmpResult_id, CCC.CmpResult_id) = :CmpResult_id";
			$queryParams["CmpResult_id"] = $data["CmpResult_id"];
		}
		if (!empty($data["LpuBuilding_id"])) {
			$filter .= " and CLC.LpuBuilding_id = :LpuBuilding_id";
			$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		$filter .= ($data["session"]["region"]["nick"] == "perm" || $data["session"]["region"]["nick"] == "kareliya")
			?" and (CLC.CmpCloseCard_id > 0 or CCC.CmpCallCardInputType_id is not null)"
			:" and (CLC.CmpCloseCard_id > 0)";
		if (!empty($data["CmpCallCard_Dokt"])) {
			$filter .= " and CCC.CmpCallCard_Dokt ilike :CmpCallCard_Dokt||'%'";
			$queryParams["CmpCallCard_Dokt"] = $data["CmpCallCard_Dokt"];
		}
		if (isset($data["CmpCallCard_Kakp"])) {
			$filter .= " and CCC.CmpCallCard_Kakp = :CmpCallCard_Kakp";
			$queryParams["CmpCallCard_Kakp"] = $data["CmpCallCard_Kakp"];
		}
		if (!empty($data["CmpCallCard_Kilo"])) {
			$filter .= " and CCC.CmpCallCard_Kilo = :CmpCallCard_Kilo";
			$queryParams["CmpCallCard_Kilo"] = $data["CmpCallCard_Kilo"];
		}
		if (!empty($data["CmpCallCard_Ncar"])) {
			$filter .= " and CCC.CmpCallCard_Ncar = :CmpCallCard_Ncar";
			$queryParams["CmpCallCard_Ncar"] = $data["CmpCallCard_Ncar"];
		}
		if (isset($data["CmpCallCard_Numb"])) {
			$filter .= " and (CCC.CmpCallCard_Numb = :CmpCallCard_Numb OR ET.EmergencyTeam_Num = :CmpCallCard_Numb OR CLC.EmergencyTeamNum = :CmpCallCard_Numb)";
			$queryParams["CmpCallCard_Numb"] = $data["CmpCallCard_Numb"];
		}
		if (!empty($data["CmpCallCard_Smpb"])) {
			$filter .= " and CCC.CmpCallCard_Smpb = :CmpCallCard_Smpb";
			$queryParams["CmpCallCard_Smpb"] = $data["CmpCallCard_Smpb"];
		}
		if (isset($data["CmpCallCard_Stbb"])) {
			$filter .= " and CCC.CmpCallCard_Stbb = :CmpCallCard_Stbb";
			$queryParams["CmpCallCard_Stbb"] = $data["CmpCallCard_Stbb"];
		}
		if (!empty($data["CmpCallCard_Stbr"])) {
			$filter .= " and CCC.CmpCallCard_Stbr = :CmpCallCard_Stbr";
			$queryParams["CmpCallCard_Stbr"] = $data["CmpCallCard_Stbr"];
		}
		if (!empty($data["CmpCallCard_Tab2"])) {
			$filter .= " and CCC.CmpCallCard_Tab2 = :CmpCallCard_Tab2";
			$queryParams["CmpCallCard_Tab2"] = $data["CmpCallCard_Tab2"];
		}
		if (!empty($data["CmpCallCard_Tab3"])) {
			$filter .= " and CCC.CmpCallCard_Tab3 = :CmpCallCard_Tab3";
			$queryParams["CmpCallCard_Tab3"] = $data["CmpCallCard_Tab3"];
		}
		if (!empty($data["CmpCallCard_Tab4"])) {
			$filter .= " and CCC.CmpCallCard_Tab4 = :CmpCallCard_Tab4";
			$queryParams["CmpCallCard_Tab4"] = $data["CmpCallCard_Tab4"];
		}
		if (!empty($data["CmpCallCard_Tabn"])) {
			$filter .= " and CCC.CmpCallCard_Tabn = :CmpCallCard_Tabn";
			$queryParams["CmpCallCard_Tabn"] = $data["CmpCallCard_Tabn"];
		}
		if (!empty($data["CmpProfile_bid"])) {
			$filter .= " and (CCC.CmpProfile_bid = :CmpProfile_bid OR ET.EmergencyTeamSpec_id = :CmpProfile_bid OR CLC.EmergencyTeamSpec_id = :CmpProfile_bid)";
			$queryParams["CmpProfile_bid"] = $data["CmpProfile_bid"];
		}
		if (!empty($data["CLLpuBuilding_id"])) {
			$filter .= " and CLC.LpuBuilding_id = :CLLpuBuilding_id";
			$queryParams["CLLpuBuilding_id"] = $data["CLLpuBuilding_id"];
		}
		if (!empty($data["ETMedStaffFact_id"])) {
			$filter .= " and ET.EmergencyTeam_HeadShiftWorkPlace = :ETMedStaffFact_id";
			$queryParams["ETMedStaffFact_id"] = $data["ETMedStaffFact_id"];
		}
		if ($data["CardKLSubRgn_id"] > 0) {
			$filter .= " and CLC.Area_id = :CardKLSubRgn_id";
			$queryParams["CardKLSubRgn_id"] = $data["CardKLSubRgn_id"];
		}
		if ($data["CardKLCity_id"] > 0) {
			$filter .= " and CLC.City_id = :CardKLCity_id";
			$queryParams["CardKLCity_id"] = $data["CardKLCity_id"];
		}
		if ($data["CardKLTown_id"] > 0) {
			$filter .= " and CLC.Town_id = :CardKLTown_id";
			$queryParams["CardKLTown_id"] = $data["CardKLTown_id"];
		}
		if ($data["CardKLStreet_id"] > 0) {
			$filter .= " and CLC.Street_id = :CardKLStreet_id";
			$queryParams["CardKLStreet_id"] = $data["CardKLStreet_id"];
		}
		if (strlen($data["CardAddress_House"]) > 0) {
			$filter .= " and CLC.House = :CardAddress_House";
			$queryParams["CardAddress_House"] = $data["CardAddress_House"];
		}
		if (strlen($data["CardAddress_Corpus"]) > 0) {
			$filter .= " and CLC.Korpus = :CardAddress_Corpus";
			$queryParams["CardAddress_Corpus"] = $data["CardAddress_Corpus"];
		}
		if (strlen($data["CardAddress_Office"]) > 0) {
			$filter .= " and CLC.Office = :CardAddress_Office";
			$queryParams["CardAddress_Office"] = $data["CardAddress_Office"];
		}
		$fields = ["CmpCallCard_D201", "CmpCallCard_Dsp1", "CmpCallCard_Dsp2", "CmpCallCard_Dsp3", "CmpCallCard_Dspp", "CmpCallCard_Vr51"];
		foreach ($fields as $field) {
			if (!empty($data[$field])) {
				$filter .= " and substring(CCC.{$field} from 1 for position(' ' in CCC.{$field})) = :{$field}";
				$queryParams[$field] = $data[$field];
			}
		}
		$fields = [
			"CmpCallCard_Prdl", "CmpCallCard_Smpp", "CmpCallCard_Dlit", "CmpDiag_aid", "CmpDiag_oid",
			"CmpTalon_id", "CmpTrauma_id", "Diag_sid", "CmpArea_gid", "CmpCallCard_Expo",
			"CmpCallCard_IsPoli", "CmpArea_id"];
		foreach ($fields as $field) {
			if (!empty($data[$field])) {
				$filter .= " and CCC.{$field} = :{$field}";
				$queryParams[$field] = $data[$field];
			}
		}
	}

	public static function selectParams_RegisterSixtyPlus(&$data, &$filter, &$queryParams)
	{
		if (isset($data["YesNo_id"]) && $data["YesNo_id"] != "") {
			$filter .= ($data["YesNo_id"] == 2)
				? " and RPlus.RegisterSixtyPlus_isSetPersonDisp = 1"
				: " and RPlus.RegisterSixtyPlus_isSetPersonDisp = 0";
		}
		switch ($data["ProfileData"]) {
			case 1:
				$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileZNO = 1";
				break;
			case 2:
				$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileONMK = 1";
				break;
			case 3:
				$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileOKS = 1";
				break;
			case 4:
				$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileBSK = 1";
				break;
			case 5:
				$filter .= " and RPlus.RegisterSixtyPlus_isSetProfileDiabetes = 1";
				break;
			default :
				$filter .= "";
				break;
		}
		if (isset($data["DisabilityData_id"]) && $data["DisabilityData_id"] != "") {
			$filter .= " and RPlus.InvalidGroupType_id =:DisabilityData_id";
			$queryParams["DisabilityData_id"] = $data["DisabilityData_id"];
		}
		if (isset($data["OnkoCtrComment_id"]) && $data["OnkoCtrComment_id"] != "") {
			if ($data["OnkoCtrComment_id"] == 1) {
				$filter .= " and RPlus.RegisterSixtyPlus_OnkoControlIsNeeded = 1";
			} else if ($data["OnkoCtrComment_id"] == 2) {
				$filter .= " and RPlus.RegisterSixtyPlus_OnkoControlIsNeeded = 0";
			} else {
				$filter .= " and RPlus.RegisterSixtyPlus_OnkoProfileDtBeg is not null";
			}
		}
		if (isset($data["Cholesterol_id"]) && $data["Cholesterol_id"] != "") {
			if ($data["Cholesterol_id"] == 3) {
				$filter .= "
					and ( 
						(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 > 3.6 and RPlus.RegisterSixtyPlus_isSetProfileBSK = 1) or
						(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 > 5.2 and RPlus.RegisterSixtyPlus_isSetProfileBSK = 0)
					)
				";
			} else {
				$filter .= "
					and ( 
						(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 <= 3.6 and RPlus.RegisterSixtyPlus_isSetProfileBSK = 1) or
						(replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 <= 5.2 and RPlus.RegisterSixtyPlus_isSetProfileBSK = 0)
					)
				";
			}
		}
		if (isset($data["Sugar_id"]) && $data["Sugar_id"] != "") {
			$filter .= ($data["Sugar_id"] == 3)
				?" and replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 > 6.1"
				:" and replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 4 and replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 <= 6.1";
		}
		if (isset($data["IMT_id"]) && $data["IMT_id"] != "") {
			$filter .= ($data["IMT_id"] == 3)
				?" and RPlus.RegisterSixtyPlus_IMTMeasure::float8 > 24.9"
				:" and RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 18.5 and RPlus.RegisterSixtyPlus_IMTMeasure::float8 <= 24.9";
		}
		if (isset($data["RiskType_id"]) && $data["RiskType_id"] != "") {
			if ($data["RiskType_id"] == 1) {
				$filter .= "
					and (
						case
							when (RPlus.RegisterSixtyPlus_IMTMeasure is null or RPlus.RegisterSixtyPlus_IMTMeasure::float8 < 25.0) then 0
							when (RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 25.0 and RPlus.RegisterSixtyPlus_IMTMeasure::float8 < 30.0) then 1
							when RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 30.0 then 2
						end +
						case
							when (RPlus.RegisterSixtyPlus_CholesterolMeasure is null or replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float < 5.1) then 0
							when (replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 >= 5.1 and replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 < 7.1) then 1 
							when replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 >= 7.1 then 2
						end +
						case
							when (RPlus.RegisterSixtyPlus_GlucoseMeasure is null or replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 < 6.2) then 0
							when (replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 6.2 and replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 < 7.0) then 1
							when replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 7.0 then 2
						end
					) >= 6 
				";
			} else if ($data["RiskType_id"] == 2) {
				$filter .= "
					and (
						case
							when (RPlus.RegisterSixtyPlus_IMTMeasure is null or RPlus.RegisterSixtyPlus_IMTMeasure::float8 < 25.0) then 0
							when (RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 25.0 and RPlus.RegisterSixtyPlus_IMTMeasure::float8 < 30.0) then 1
							when RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 30.0 then 2
						end +
						case
							when (RPlus.RegisterSixtyPlus_CholesterolMeasure is null or replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 < 5.1) then 0
							when (replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 >= 5.1 and replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 < 7.1) then 1 
							when replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 >= 7.1 then 2
						end +
						case
							when (RPlus.RegisterSixtyPlus_GlucoseMeasure is null or replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 < 6.2) then 0
							when (replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 6.2 and replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 < 7.0) then 1
							when replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 7.0 then 2
						end
					) between 1 and 5
				";
			} else {
				$filter .= "
					and (
						case
							when (RPlus.RegisterSixtyPlus_IMTMeasure is null or RPlus.RegisterSixtyPlus_IMTMeasure::float8 < 25.0 ) then 0
							when (RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 25.0 and RPlus.RegisterSixtyPlus_IMTMeasure::float8 < 30.0) then 1
							when RPlus.RegisterSixtyPlus_IMTMeasure::float8 >= 30.0 then 2
						end +
						case
							when (RPlus.RegisterSixtyPlus_CholesterolMeasure is null or replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 < 5.1) then 0
							when (replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 >= 5.1 and replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 < 7.1) then 1 
							when replace(RPlus.RegisterSixtyPlus_CholesterolMeasure, ',','.')::float8 >= 7.1 then 2
						end +
						case
							when (RPlus.RegisterSixtyPlus_GlucoseMeasure is null or replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 < 6.2) then 0
							when (replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 6.2 and replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 < 7.0) then 1
							when replace(RPlus.RegisterSixtyPlus_GlucoseMeasure, ',','.')::float8 >= 7.0 then 2
						end
					) = 0
				";
			}
		}
	}

	public static function selectParams_EvnDtpDeath(&$data, &$filter, &$queryParams)
	{
		if (isset($data["EvnDtpDeath_setDate_Range"][0])) {
			$filter .= " and EDW.EvnDtpDeath_setDate >= :EvnDtpDeath_setDate_Range_0";
			$queryParams["EvnDtpDeath_setDate_Range_0"] = $data["EvnDtpDeath_setDate_Range"][0];
		}
		if (isset($data["EvnDtpDeath_setDate_Range"][1])) {
			$filter .= " and EDW.EvnDtpDeath_setDate <= :EvnDtpDeath_setDate_Range_1";
			$queryParams["EvnDtpDeath_setDate_Range_1"] = $data["EvnDtpDeath_setDate_Range"][1];
		}
		if (isset($data["EvnDtpDeath_DeathDate_Range"][0])) {
			$filter .= " and EDW.EvnDtpDeath_DeathDate >= :EvnDtpDeath_DeathDate_Range_0";
			$queryParams["EvnDtpDeath_DeathDate_Range_0"] = $data["EvnDtpDeath_DeathDate_Range"][0];
		}
		if (isset($data["EvnDtpDeath_DeathDate_Range"][1])) {
			$filter .= " and EDW.EvnDtpDeath_DeathDate <= :EvnDtpDeath_DeathDate_Range_1";
			$queryParams["EvnDtpDeath_DeathDate_Range_1"] = $data["EvnDtpDeath_DeathDate_Range"][1];
		}
	}

	public static function selectParams_PersonDopDisp(&$data, &$filter, &$queryParams)
	{
		if (isset($data["dop_disp_reg_beg_date"]) && isset($data["dop_disp_reg_beg_time"])) {
			$filter .= " and DD.PersonDopDisp_updDT >= :DDR_BegDate ";
			$queryParams["DDR_BegDate"] = $data["dop_disp_reg_beg_date"] . " " . $data["dop_disp_reg_beg_time"];
		}
		$filter .= " and DD.PersonDopDisp_Year = :PersonDopDisp_Year ";
		if (isset($data["PersonDopDisp_Year"]) && ($data["PersonDopDisp_Year"] > 2000)) {
			$queryParams["PersonDopDisp_Year_Start"] = "{$data["PersonDopDisp_Year"]}-01-01";
			$queryParams["PersonDopDisp_Year_End"] = "{$data["PersonDopDisp_Year"]}-12-31";
			$queryParams["PersonDopDisp_Year"] = $data["PersonDopDisp_Year"];
		} else {
			$queryParams["PersonDopDisp_Year_Start"] = date("Y") . "-01-01";
			$queryParams["PersonDopDisp_Year_End"] = date("Y") . "-12-31";
			$queryParams["PersonDopDisp_Year"] = date("Y");
		}
	}

	public static function selectParams_PersonDispOrpPeriod(&$data, &$filter, &$queryParams)
	{
		if (isset($data["reg_beg_date"]) && isset($data["reg_beg_time"])) {
			$filter .= " and DOr.PersonDispOrp_updDT >= :DDR_BegDate ";
			$queryParams["DDR_BegDate"] = "{$data["reg_beg_date"]} {$data["reg_beg_time"]}";
		}
		$filter .= " and DOr.PersonDispOrp_Year = :PersonDispOrp_Year ";
		$queryParams["PersonDispOrp_Year"] = (isset($data["PersonDispOrp_Year"]))
			?$data["PersonDispOrp_Year"]
			:date("Y");
		if (!empty($data["EducationInstitutionType_id"])) {
			$filter .= " and DOr.EducationInstitutionType_id = :EducationInstitutionType_id ";
			$queryParams["EducationInstitutionType_id"] = $data["EducationInstitutionType_id"];
		}
		$filter .= " and DOr.CategoryChildType_id IN (8)";
	}

	public static function selectParams_PersonDispOrpPred(&$data, &$filter, &$queryParams)
	{
		if (isset($data["reg_beg_date"]) && isset($data["reg_beg_time"])) {
			$filter .= " and DOr.PersonDispOrp_updDT >= :DDR_BegDate ";
			$queryParams["DDR_BegDate"] = "{$data["reg_beg_date"]} {$data["reg_beg_time"]}";
		}
		$filter .= " and DOr.PersonDispOrp_Year = :PersonDispOrp_Year ";
		$queryParams["PersonDispOrp_Year"] = (isset($data["PersonDispOrp_Year"]))
			?$data["PersonDispOrp_Year"]
			:date("Y");
		if (!empty($data["EducationInstitutionType_id"])) {
			$filter .= " and DOr.EducationInstitutionType_id = :EducationInstitutionType_id ";
			$queryParams["EducationInstitutionType_id"] = $data["EducationInstitutionType_id"];
		}
		$filter .= " and DOr.CategoryChildType_id IN (9)";
	}

	public static function selectParams_PersonDispOrpProf(&$data, &$filter, &$queryParams)
	{
		if (isset($data["reg_beg_date"]) && isset($data["reg_beg_time"])) {
			$filter .= " and DOr.PersonDispOrp_updDT >= :DDR_BegDate ";
			$queryParams["DDR_BegDate"] = $data["reg_beg_date"] . " " . $data["reg_beg_time"];
		}
		$filter .= " and DOr.PersonDispOrp_Year = :PersonDispOrp_Year ";
		$queryParams["PersonDispOrp_Year"] = (isset($data["PersonDispOrp_Year"]))
			?$data["PersonDispOrp_Year"]
			:date("Y");
		if (!empty($data["AgeGroupDisp_id"])) {
			$filter .= " and DOr.AgeGroupDisp_id = :AgeGroupDisp_id ";
			$queryParams["AgeGroupDisp_id"] = $data["AgeGroupDisp_id"];
		}
		if (!empty($data["OrgExist"])) {
			$filter .= ($data["OrgExist"] == 2)
				? " and DOr.Org_id IS NOT NULL"
				: " and DOr.Org_id IS NULL";
		}
		$filter .= " and DOr.CategoryChildType_id IN (10)";
	}

	public static function selectParams_PersonDispOrp(&$data, &$filter, &$queryParams)
	{
		if (isset($data["reg_beg_date"]) && isset($data["reg_beg_time"])) {
			$filter .= " and DOr.PersonDispOrp_updDT >= :DDR_BegDate ";
			$queryParams["DDR_BegDate"] = "{$data["reg_beg_date"]} {$data["reg_beg_time"]}";
		}
		$filter .= " and DOr.PersonDispOrp_Year = :PersonDispOrp_Year ";
		$queryParams["PersonDispOrp_Year"] = (isset($data["PersonDispOrp_Year"]))
			? $data["PersonDispOrp_Year"]
			: date("Y");
		if (empty($data["CategoryChildType"])) {
			$data["CategoryChildType"] = "orp";
		}
		if (!empty($data["EducationInstitutionType_id"])) {
			$filter .= " and DOr.EducationInstitutionType_id = :EducationInstitutionType_id ";
			$queryParams["EducationInstitutionType_id"] = $data["EducationInstitutionType_id"];
		}
		switch ($data["CategoryChildType"]) {
			case "orp":
				$filter .= " and DOr.CategoryChildType_id IN (1,2,3,4)";
				break;
			case "orpadopted":
				$filter .= " and DOr.CategoryChildType_id IN (5,6,7)";
				break;
		}
	}

	public static function selectParams_PersonDispOrpOld(&$data, &$filter, &$queryParams)
	{
		if (isset($data["reg_beg_date"]) && isset($data["reg_beg_time"])) {
			$filter .= " and DOr.PersonDispOrp_updDT >= :DDR_BegDate ";
			$queryParams["DDR_BegDate"] = "{$data["reg_beg_date"]} {$data["reg_beg_time"]}";
		}
		$filter .= "
					and DOr.PersonDispOrp_Year = :PersonDispOrp_Year
					and DOr.PersonDispOrp_Year <= 2012
				";
		$queryParams["PersonDispOrp_Year"] = (isset($data["PersonDispOrp_Year"]))
			? $data["PersonDispOrp_Year"]
			: date("Y");
	}

	public static function selectParams_EvnPLDispDopStream(&$data, &$filter, &$queryParams)
	{
		$filter .= " and EPLDD.EvnPLDispDop_updDT >= :EvnPLDispDop_date_time and EPLDD.pmUser_updID = :pmUser_id ";
		$queryParams["EvnPLDispDop_date_time"] = "{$data["EvnPLDispDopStream_begDate"]} {$data["EvnPLDispDopStream_begTime"]}";
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		$queryParams["Lpu_id"] = $data["Lpu_id"];
	}

	public static function selectParams_EvnPLDispTeen14Stream(&$data, &$filter, &$queryParams)
	{
		$filter .= " and EPLDT14.EvnPLDispTeen14_updDT >= :EvnPLDispTeen14_date_time and EPLDT14.pmUser_updID = :pmUser_id ";
		$queryParams["EvnPLDispTeen14_date_time"] = "{$data["EvnPLDispTeen14Stream_begDate"]} {$data["EvnPLDispTeen14Stream_begTime"]}";
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		$queryParams["Lpu_id"] = $data["Lpu_id"];
	}

	public static function selectParams_EvnPLDispOrpStream(&$data, &$filter, &$queryParams)
	{
		$filter .= " and EPLDO.EvnPLDispOrp_updDT >= :EvnPLDispOrp_date_time and EPLDO.pmUser_updID = :pmUser_id ";
		$queryParams["EvnPLDispOrp_date_time"] = "{$data["EvnPLDispOrpStream_begDate"]} {$data["EvnPLDispOrpStream_begTime"]}";
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		$queryParams["Lpu_id"] = $data["Lpu_id"];
	}

	public static function selectParams_EvnPLDispMigrant(&$data, &$filter, &$queryParams)
	{
		if (isset($data["ResultDispMigrant_id"])) {
			$filter .= " and EPLDM.ResultDispMigrant_id = :ResultDispMigrant_id ";
			$queryParams["ResultDispMigrant_id"] = $data["ResultDispMigrant_id"];
		}
		if (isset($data["EvnPLDispMigran_SertHIVNumber"])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertHIVNumber ilike :EvnPLDispMigran_SertHIVNumber||'%' ";
			$queryParams["EvnPLDispMigran_SertHIVNumber"] = $data["EvnPLDispMigran_SertHIVNumber"];
		}
		if (isset($data["EvnPLDispMigran_SertHIVDate"])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertHIVDate = :EvnPLDispMigran_SertHIVDate::timestamp ";
			$queryParams["EvnPLDispMigran_SertHIVDate"] = $data["EvnPLDispMigran_SertHIVDate"];
		}
		if (isset($data["EvnPLDispMigran_SertHIVDateRange"][0])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertHIVDate >= :EvnPLDispMigran_SertHIVDateRange_0::timestamp ";
			$queryParams["EvnPLDispMigran_SertHIVDateRange_0"] = $data["EvnPLDispMigran_SertHIVDateRange"][0];
		}
		if (isset($data["EvnPLDispMigran_SertHIVDateRange"][1])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertHIVDate <= :EvnPLDispMigran_SertHIVDateRange_1::timestamp ";
			$queryParams["EvnPLDispMigran_SertHIVDateRange_1"] = $data["EvnPLDispMigran_SertHIVDateRange"][1];
		}
		if (isset($data["EvnPLDispMigran_SertInfectNumber"])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertInfectNumber ilike :EvnPLDispMigran_SertInfectNumber||'%' ";
			$queryParams["EvnPLDispMigran_SertInfectNumber"] = $data["EvnPLDispMigran_SertInfectNumber"];
		}
		if (isset($data["EvnPLDispMigran_SertInfectDate"])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertInfectDate = :EvnPLDispMigran_SertInfectDate::timestamp ";
			$queryParams["EvnPLDispMigran_SertInfectDate"] = $data["EvnPLDispMigran_SertInfectDate"];
		}
		if (isset($data["EvnPLDispMigran_SertInfectDateRange"][0])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertInfectDate >= :EvnPLDispMigran_SertInfectDateRange_0::timestamp ";
			$queryParams["EvnPLDispMigran_SertInfectDateRange_0"] = $data["EvnPLDispMigran_SertInfectDateRange"][0];
		}
		if (isset($data["EvnPLDispMigran_SertInfectDateRange"][1])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertInfectDate <= :EvnPLDispMigran_SertInfectDateRange_1::timestamp ";
			$queryParams["EvnPLDispMigran_SertInfectDateRange_1"] = $data["EvnPLDispMigran_SertInfectDateRange"][1];
		}
		if (isset($data["EvnPLDispMigran_SertNarcoNumber"])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertNarcoNumber ilike :EvnPLDispMigran_SertNarcoNumber||'%' ";
			$queryParams["EvnPLDispMigran_SertNarcoNumber"] = $data["EvnPLDispMigran_SertNarcoNumber"];
		}
		if (isset($data["EvnPLDispMigran_SertNarcoDate"])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertNarcoDate = :EvnPLDispMigran_SertNarcoDate::timestamp ";
			$queryParams["EvnPLDispMigran_SertNarcoDate"] = $data["EvnPLDispMigran_SertNarcoDate"];
		}
		if (isset($data["EvnPLDispMigran_SertNarcoDateRange"][0])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertNarcoDate >= :EvnPLDispMigran_SertNarcoDateRange_0::timestamp ";
			$queryParams["EvnPLDispMigran_SertNarcoDateRange_0"] = $data["EvnPLDispMigran_SertNarcoDateRange"][0];
		}
		if (isset($data["EvnPLDispMigran_SertNarcoDateRange"][1])) {
			$filter .= " and EPLDM.EvnPLDispMigran_SertNarcoDate <= :EvnPLDispMigran_SertNarcoDateRange_1::timestamp ";
			$queryParams["EvnPLDispMigran_SertNarcoDateRange_1"] = $data["EvnPLDispMigran_SertNarcoDateRange"][1];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
	}

	public static function selectParams_EvnPLDispDriver(&$data, &$filter, &$queryParams)
	{
		if (isset($data["ResultDispDriver_id"])) {
			$filter .= " and EPLDD.ResultDispDriver_id = :ResultDispDriver_id ";
			$queryParams["ResultDispDriver_id"] = $data["ResultDispDriver_id"];
		}
		$queryParams["Lpu_id"] = $data["Lpu_id"];
	}

	public static function selectParams_GibtRegistry(&$data, &$filter, &$queryParams)
	{
		$filter .= " and PR.PersonRegisterType_id = 70 ";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			} else if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0])) {
			$filter .= " and PR.PersonRegister_setDate >= :PersonRegister_setDate_Range_0::timestamp ";
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
		}
		if (isset($data["PersonRegister_setDate_Range"][1])) {
			$filter .= " and PR.PersonRegister_setDate <= :PersonRegister_setDate_Range_1::timestamp ";
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
		}
		if (isset($data["PersonRegister_disDate_Range"][0])) {
			$filter .= " and PR.PersonRegister_disDate >= :PersonRegister_disDate_Range_0::timestamp ";
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
		}
		if (isset($data["PersonRegister_disDate_Range"][1])) {
			$filter .= " and PR.PersonRegister_disDate <= :PersonRegister_disDate_Range_1::timestamp ";
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and D.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and D.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (!empty($data["MorbusGEBT_setDiagDT_Range"][0])) {
			$filter .= " and MG.Morbus_setDT >= :MorbusGEBT_setDiagDT_Range_0::timestamp ";
			$queryParams["MorbusGEBT_setDiagDT_Range_0"] = $data["MorbusGEBT_setDiagDT_Range"][0];
		}
		if (!empty($data["MorbusGEBT_setDiagDT_Range"][1])) {
			$filter .= " and MG.Morbus_setDT <= :MorbusGEBT_setDiagDT_Range_1::timestamp ";
			$queryParams["MorbusGEBT_setDiagDT_Range_1"] = $data["MorbusGEBT_setDiagDT_Range"][1];
		}
	}

	public static function selectParams_EvnERSBirthCertificate(&$data, &$filter, &$queryParams)
	{
		$filter .= " and ERS.Lpu_id = :Lpu_id ";
		$queryParams["Lpu_id"] = $data["Lpu_id"];

		if (!empty($data["ERSRequest_ERSNumber"])) {
			$filter .= " and er.ERSRequest_ERSNumber = :ERSRequest_ERSNumber ";
			$queryParams["ERSRequest_ERSNumber"] = $data["ERSRequest_ERSNumber"];
		}
		if (count($data["EvnERSBirthCertificate_CreateDate_Range"]) == 2 && !empty($data["EvnERSBirthCertificate_CreateDate_Range"][0])) {
			$filter .= " and ers.EvnERSBirthCertificate_CreateDate between :EvnERSBirthCertificate_CreateDate_RangeStart and :EvnERSBirthCertificate_CreateDate_RangeEnd ";
			$queryParams["EvnERSBirthCertificate_CreateDate_RangeStart"] = $data["EvnERSBirthCertificate_CreateDate_Range"][0];
			$queryParams["EvnERSBirthCertificate_CreateDate_RangeEnd"] = $data["EvnERSBirthCertificate_CreateDate_Range"][1];
		}
		if (!empty($data["ERSStatus_id"])) {
			$filter .= " and ers.ERSStatus_id = :ERSStatus_id ";
			$queryParams["ERSStatus_id"] = $data["ERSStatus_id"];
		}
		if (!empty($data["ERSRequestType_id"])) {
			$filter .= " and er.ERSRequestType_id = :ERSRequestType_id ";
			$queryParams["ERSRequestType_id"] = $data["ERSRequestType_id"];
		}
		if (!empty($data["ERSRequestStatus_id"])) {
			$filter .= " and er.ERSRequestStatus_id = :ERSRequestStatus_id ";
			$queryParams["ERSRequestStatus_id"] = $data["ERSRequestStatus_id"];
		}
	}

	public static function selectParams_HTMRegister(&$data, &$filter, &$queryParams)
	{
		if (!empty($data["RegisterType_id"])) {
			$queryParams["RegisterType_id"] = $data["RegisterType_id"];
			switch ($data["RegisterType_id"]) {
				case 1:
					break;
				case 2:
					$filter .= " and coalesce(PS.Person_isDead,1) = 1";
					break;
				case 3:
					$filter .= " and coalesce(PS.Person_isDead,1) = 2";
					break;
			}
		}
		if (!empty($data["Register_setDate_Range"][0]) && !empty($data["Register_setDate_Range"][1])) {
			$queryParams["Register_setDate_Range_0"] = $data["Register_setDate_Range"][0];
			$queryParams["Register_setDate_Range_1"] = $data["Register_setDate_Range"][1];
			$filter .= " and R.Register_setDate between :Register_setDate_Range_0 and :Register_setDate_Range_1";
		}
		if (!empty($data["Register_disDate_Range"][0]) && !empty($data["Register_disDate_Range"][1])) {
			$queryParams["Register_disDate_Range_0"] = $data["Register_disDate_Range"][0];
			$queryParams["Register_disDate_Range_1"] = $data["Register_disDate_Range"][1];
			$filter .= " and PS.Person_DeadDT between :Register_disDate_Range_0 and :Register_disDate_Range_1";
		}
		if (!empty($data["HTMLpu_id"])) {
			$queryParams["Lpu_id"] = $data["HTMLpu_id"];
			$filter .= " and EDH.Lpu_id = :Lpu_id";
		}
		if (!empty($data["HTMRegister_ApplicationDate_Range"][0]) && !empty($data["HTMRegister_ApplicationDate_Range"][1])) {
			$queryParams["HTMRegister_ApplicationDate_Range_0"] = $data["HTMRegister_ApplicationDate_Range"][0];
			$queryParams["HTMRegister_ApplicationDate_Range_1"] = $data["HTMRegister_ApplicationDate_Range"][1];
			$filter .= " and HR.HTMRegister_ApplicationDate between :HTMRegister_ApplicationDate_Range_0 and :HTMRegister_ApplicationDate_Range_1";
		}
		if (!empty($data["HTMRegister_DisDate_Range"][0]) && !empty($data["HTMRegister_DisDate_Range"][1])) {
			$queryParams["HTMRegister_DisDate_Range_0"] = $data["HTMRegister_DisDate_Range"][0];
			$queryParams["HTMRegister_DisDate_Range_1"] = $data["HTMRegister_DisDate_Range"][1];
			$filter .= " and HR.HTMRegister_DisDate between :HTMRegister_DisDate_Range_0 and :HTMRegister_DisDate_Range_1";
		}
		if (!empty($data["HTMedicalCareClass_id"])) {
			$queryParams["HTMedicalCareClass_id"] = $data["HTMedicalCareClass_id"];
			$filter .= " and HR.HTMedicalCareClass_id = :HTMedicalCareClass_id";
		}
		if (!empty($data["HTMRegister_Stage"])) {
			$queryParams["HTMRegister_Stage"] = $data["HTMRegister_Stage"];
			$filter .= " and HR.HTMRegister_Stage = :HTMRegister_Stage";
		}
		if (!empty($data["Diag_id1"])) {
			$queryParams["Diag_id1"] = $data["Diag_id1"];
			$filter .= " and HR.Diag_FirstId = :Diag_id1";
		}
		if (!empty($data["HTMQueueType_id"])) {
			$queryParams["HTMQueueType_id"] = $data["HTMQueueType_id"];
			$filter .= " and HR.HTMQueueType_id = :HTMQueueType_id";
		}
		if (!empty($data["isSetPlannedHospDate"])) {
			switch ($data["isSetPlannedHospDate"]) {
				case 2:
					$filter .= " and HTMRegister_PlannedHospDate is not null";
					break;
				case 1:
					$filter .= " and HTMRegister_PlannedHospDate is null";
					break;
			}
		}
		if (!empty($data["LpuSectionProfile_id"])) {
			$queryParams["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
			$filter .= " and EDH.LpuSectionProfile_id = :LpuSectionProfile_id";
		}
		if (!empty($data["HTMRegister_OperDate_Range"][0]) && !empty($data["HTMRegister_OperDate_Range"][1])) {
			$queryParams["HTMRegister_OperDate_Range_0"] = $data["HTMRegister_OperDate_Range"][0];
			$queryParams["HTMRegister_OperDate_Range_1"] = $data["HTMRegister_OperDate_Range"][1];
			$filter .= " and HR.HTMRegister_OperDate between :HTMRegister_OperDate_Range_0 and :HTMRegister_OperDate_Range_1";
		}
		if (!empty($data["HTMResult_id"])) {
			$queryParams["HTMResult_id"] = $data["HTMResult_id"];
			$filter .= " and HR.HTMResult_id = :HTMResult_id";
		}
		if (!empty($data["HTMRegister_IsSigned"])) {
			$queryParams["HTMRegister_IsSigned"] = $data["HTMRegister_IsSigned"];
			$filter .= " and HR.HTMRegister_IsSigned = :HTMRegister_IsSigned";
		}
	}

	public static function selectParams_SportRegistry(&$data, &$filter, &$queryParams)
	{
		if (!empty($data["SportRegisterType_id"])) {
			if ($data["SportRegisterType_id"] == 3)
				$filter .= " and SR.SportRegister_delDT is not null";
			else if ($data["SportRegisterType_id"] == 2)
				$filter .= " and SR.SportRegister_delDT is null ";
		}
		if (!empty($data["SportType_id"])) {
			$queryParams["SportType_id"] = $data["SportType_id"];
			$filter .= " and SRUMO.SportType_id = :SportType_id ";
		}
		if (!empty($data["SportStage_id"])) {
			$queryParams["SportStage_id"] = $data["SportStage_id"];
			$filter .= " and SRUMO.SportStage_id = :SportStage_id ";
		}
		if (!empty($data["SportCategory_id"])) {
			$queryParams["SportCategory_id"] = $data["SportCategory_id"];
			$filter .= " and SRUMO.SportCategory_id = :SportCategory_id ";
		}
		if (!empty($data["SportOrg_id"])) {
			$queryParams["SportOrg_id"] = $data["SportOrg_id"];
			$filter .= " and SRUMO.SportOrg_id = :SportOrg_id ";
		}
		if (!empty($data["UMOResult_id"])) {
			$queryParams["UMOResult_id"] = $data["UMOResult_id"];
			$filter .= " and SRUMO.UMOResult_id = :UMOResult_id ";
		}
		if (!empty($data["MedPersonal_pid"])) {
			$queryParams["MedPersonal_pid"] = $data["MedPersonal_pid"];
			$filter .= " and SRUMO.MedPersonal_pid = :MedPersonal_pid ";
		}
		if (!empty($data["SportTrainer_id"])) {
			$queryParams["SportTrainer_id"] = $data["SportTrainer_id"];
			$filter .= " and SRUMO.SportTrainer_id = :SportTrainer_id ";
		}
		if (!empty($data["IsTeamMember_id"])) {
			$queryParams["IsTeamMember_id"] = $data["IsTeamMember_id"];
			$filter .= " and SRUMO.SportRegisterUMO_IsTeamMember = :IsTeamMember_id";
		}
		if (!empty($data["InvalidGroupType_id"])) {
			$queryParams["InvalidGroupType_id"] = $data["InvalidGroupType_id"];
			$filter .= " and SRUMO.InvalidGroupType_id = :InvalidGroupType_id ";
		}
		if (!empty($data["SportParaGroup_id"])) {
			$queryParams["SportParaGroup_id"] = $data["SportParaGroup_id"];
			$filter .= " and SRUMO.SportParaGroup_id = :SportParaGroup_id ";
		}
		if (!empty($data["SportStage_id"])) {
			$queryParams["SportStage_id"] = $data["SportStage_id"];
			$filter .= " and SRUMO.SportStage_id = :SportStage_id ";
		}
		if (!empty($data["SportRegisterUMO_UMODate"][0]) && !empty($data["SportRegisterUMO_UMODate"][1])) {
			$queryParams["SportRegisterUMO_UMODate_0"] = $data["SportRegisterUMO_UMODate"][0];
			$queryParams["SportRegisterUMO_UMODate_1"] = $data["SportRegisterUMO_UMODate"][1];
			$filter .= " and SRUMO.SportRegisterUMO_UMODate::date between :SportRegisterUMO_UMODate_0 and :SportRegisterUMO_UMODate_1 ";
		}
		if (!empty($data["SportRegisterUMO_AdmissionDtBeg"][0]) && !empty($data["SportRegisterUMO_AdmissionDtBeg"][1])) {
			$queryParams["SportRegisterUMO_AdmissionDtBeg_0"] = $data["SportRegisterUMO_AdmissionDtBeg"][0];
			$queryParams["SportRegisterUMO_AdmissionDtBeg_1"] = $data["SportRegisterUMO_AdmissionDtBeg"][1];
			$filter .= " and SRUMO.SportRegisterUMO_AdmissionDtBeg::date between :SportRegisterUMO_AdmissionDtBeg_0 and :SportRegisterUMO_AdmissionDtBeg_1 ";
		}
		if (!empty($data["SportRegisterUMO_AdmissionDtEnd"][0]) && !empty($data["SportRegisterUMO_AdmissionDtEnd"][1])) {
			$queryParams["SportRegisterUMO_AdmissionDtEnd_0"] = $data["SportRegisterUMO_AdmissionDtEnd"][0];
			$queryParams["SportRegisterUMO_AdmissionDtEnd_1"] = $data["SportRegisterUMO_AdmissionDtEnd"][1];
			$filter .= " and SRUMO.SportRegisterUMO_AdmissionDtEnd::date between :SportRegisterUMO_AdmissionDtEnd_0 and :SportRegisterUMO_AdmissionDtEnd_1 ";
		}
		$filter .= " and SRUMO.SportRegisterUMO_delDT is null ";
	}

	public static function selectParams_ONMKRegistry(&$data, &$filter, &$queryParams)
	{
		$filter .= " and ONMKR.ONMKRegistry_deleted = 1 ";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			} elseif ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (!empty($data["ONMKRegistry_Status"])) {
			if ($data["ONMKRegistry_Status"] == 1) {
				$filter .= " and ONMKR.ONMKRegistry_IsMonitor = 1 ";
			} elseif ($data["ONMKRegistry_Status"] == 3) {
				$filter .= " and ONMKR.ONMKRegistry_IsNew = 1 ";
			} elseif ($data["ONMKRegistry_Status"] == 4) {
				$filter .= " and ONMKR.ONMKRegistry_IsNew = 2 ";
			} elseif ($data["ONMKRegistry_Status"] == 5) {
				$filter .= " and ONMKR.ONMKRegistry_IsConfirmed = 2 ";
			}
		} else {
			$filter .= " and ONMKR.ONMKRegistry_IsMonitor = 1 ";
		}
		if (isset($data["LPU_sid"]) && $data["LPU_sid"] != "") {
			$filter .= " and ONMKR.Lpu_id = :LPU_sid";
			$queryParams["LPU_sid"] = $data["LPU_sid"];
		}
		if (isset($data["LPU_id"]) && $data["LPU_id"] != "") {
			if ($data["LPU_id"] == "20000") {
				$filter .= " and not ONMKR.Lpu_id in (select mo.lpu_id from dbo.RoutingONMK mo)";
			} else {
				$filter .= "
					and ONMKR.Lpu_id in (
						select rcc.lpu_id
						from dbo.RoutingONMK rcc
						where rcc.lpu_id = :LPU_id
						union
						select pco.lpu_id
						from
							dbo.RoutingONMK rcc
							left join dbo.RoutingONMK pco on pco.lpu_pid=rcc.lpu_id
						where rcc.lpu_id = :LPU_id and pco.lpu_id is not null
						union
						select mo.lpu_id
						from
							dbo.RoutingONMK rcc
							left join dbo.RoutingONMK pco on pco.lpu_pid=rcc.lpu_id
							left join dbo.RoutingONMK mo on mo.lpu_pid=pco.lpu_id
						where rcc.lpu_id = :LPU_id and mo.lpu_id is not null
					)
				";
				$queryParams["LPU_id"] = $data["LPU_id"];
			}
		}
		if (isset($data["ONMKRegistry_TypeMO"]) && $data["ONMKRegistry_TypeMO"] != "") {
			if ($data["ONMKRegistry_TypeMO"] == 2) {
				$filter .= " and Lp.lpu_id in (select mo.lpu_id from dbo.RoutingONMK mo where mo.lpusectiondoptype_id in (1,2))";
			} else if ($data["ONMKRegistry_TypeMO"] == 3) {
				$filter .= " and Lp.lpu_id in (select mo.lpu_id from dbo.RoutingONMK mo where mo.lpusectiondoptype_id in (1))";
			} else if ($data["ONMKRegistry_TypeMO"] == 4) {
				$filter .= " and Lp.lpu_id in (select mo.lpu_id from dbo.RoutingONMK mo where mo.lpusectiondoptype_id in (2))";
			} else if ($data["ONMKRegistry_TypeMO"] == 5) {
				$filter .= " and dbo.GetONMKMO(Lp.lpu_id) = 0";
			}
		}
		if (isset($data["ONMKRegistry_Evn_DTDesease"][0]) && isset($data["ONMKRegistry_Evn_DTDesease"][1])) {
			$queryParams["ONMKRegistry_EvnDTDesease_0"] = $data["ONMKRegistry_Evn_DTDesease"][0];
			$queryParams["ONMKRegistry_EvnDTDesease_1"] = $data["ONMKRegistry_Evn_DTDesease"][1];
			$filter .= " and ONMKR.ONMKRegistry_EvnDT::date between :ONMKRegistry_EvnDTDesease_0 and :ONMKRegistry_EvnDTDesease_1 ";
		}
		if (!empty($data["Diag_Code_From"])) {
			$queryWithAdditionalWhere[] = "Dg.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (!empty($data["Diag_Code_To"])) {
			$queryWithAdditionalWhere[] = "Dg.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($queryWithAdditionalWhere)) {
			$filter .= " and " . implode(" and ", $queryWithAdditionalWhere);
		}
		if (isset($data["ONMKRegistry_ISTLT"])) {
			if ($data["ONMKRegistry_ISTLT"] == 1) {
				$filter .= " and ONMKRegistry_TLTDT is not null ";
			} else if ($data["ONMKRegistry_ISTLT"] == 2) {
				$filter .= " and ONMKRegistry_TLTDT is null ";
			}
		}
		if (!empty($data["ONMKRegistry_ResultDesease"])) {
			if ($data["ONMKRegistry_ResultDesease"] == 1) {
				$filter .= " and ONMKR.LeaveType_id = (select LT.LeaveType_id from dbo.LeaveType LT where LT.region_id=" . getRegionNumber() . " and LT.LeaveType_Code=1) ";
			} elseif ($data["ONMKRegistry_ResultDesease"] == 2) {
				$filter .= " and ONMKR.LeaveType_id = (select LT.LeaveType_id from dbo.LeaveType LT where LT.region_id=" . getRegionNumber() . " and LT.LeaveType_Code=3) ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and ONMKR.ONMKRegistry_insDT::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (strpos($data["session"]["groups"], "ONMKRegistryCenter") === false && !empty($data['session']['lpu_id'])) {
			$filter .= "
				and (
					exists(
						select 1
						from v_PersonCard_all VPC
						where VPC.Person_id = PS.Person_id
						  and VPC.lpuattachtype_id = 1
						  and VPC.PersonCard_endDate is null 
						  and ','||dbo.GetRoutingONMKMO({$data["session"]["lpu_id"]})||',' ilike '%,'||VPC.Lpu_id||',%'
					) or ','||dbo.GetRoutingONMKMO({$data["session"]["lpu_id"]})||',' ilike '%,'||ONMKR.lpu_id||',%'
				)
			";
		}
	}

	public static function selectParams_PersonDopDispPlan(&$data, &$filter, &$queryParams, $callObject)
	{
		// Дисп-ция детей-сирот стационарных 1-ый этап [3].
		if ($data["DispClass_id"] == 3) {
			$filter .= " and dbo.Age2(PS.Person_BirthDay, '{$data["PersonDopDisp_Year"]}-12-31') <= 18 ";
		}
		
		// Дисп-ция детей-сирот усыновленных 1-ый этап [7].
		if ($data["DispClass_id"] == 7) {
			$filter .= " and dbo.Age2(PS.Person_BirthDay, '{$data["PersonDopDisp_Year"]}-12-31') <= 18 ";
		}
		
		// Дисп-ция взр. населения 1-ый этап [1].
		if ($data["DispClass_id"] == 1) {
			// Подлежащие ежегодному прохождению ДВН (инвалиды ВОВ и блокадники)
			if ($data["Person_isYearlyDispDop"]) {
				$filter .= " and PP.PersonPrivilege_id is not null and PP.PrivilegeType_id in (10,11,50) ";
			} else {
				// Исключаем из запроса для "Не проходившие в установленные сроки".
				if (!$data['Person_isNotDispDopOnTime']) {
					$add_filter = "";
					$callObject->load->model('EvnPLDispDop13_model', 'EvnPLDispDop13_model');
					$dateX = $callObject->EvnPLDispDop13_model->getNewDVNDate();
					if (!empty($dateX) && $dateX <= date('Y-m-d')) {
						$add_filter .= "
							or
							(dbo.Age2(PS.Person_BirthDay, CAST('{$data['PersonDopDisp_Year']}-12-31' as timestamp)) >= 40)
						";
					} else {
						if (!in_array($data['session']['region']['nick'], array('kz')) && $data['PersonDopDisp_Year'] >= 2018) {
							$add_filter .= "
								or
								(PS.Sex_id = 1 and dbo.Age2(PS.Person_BirthDay, CAST('{$data['PersonDopDisp_Year']}-12-31' as timestamp)) between 49 and 73 and dbo.Age2(PS.Person_BirthDay, CAST('{$data['PersonDopDisp_Year']}-12-31' as timestamp)) % 2 = 1)
								or
								(PS.Sex_id = 2 and dbo.Age2(PS.Person_BirthDay, CAST('{$data['PersonDopDisp_Year']}-12-31' as timestamp)) between 48 and 73)
							";
						}
					}
					$filter .= "
					and PS.Person_BirthDay <= :PersonAge_18
					and (
							(PS.Person_BirthDay <= :PersonAge_21 and dbo.Age2(PS.Person_BirthDay, CAST('{$data["PersonDopDisp_Year"]}-12-31' as timestamp)) % 3 = 0) or
							(PP.PersonPrivilege_id is not null and PP.PrivilegeType_id in (10,11,50))
							{$add_filter}
					)";
				}
			}
			
			// Не проходившие в установленные сроки.
			// Примечание: Подлежащие ежегодному прохождению ДВН перекрывает запрос.
			if ($data['Person_isNotDispDopOnTime'] && !$data['Person_isYearlyDispDop']) {
				// Кроме регионов Казахстан, Карелия, Хакасия, Бурятия, Уфа.
				if (!in_array(getRegionNick(), ['kz', 'kareliya', 'khak', 'buryatiya', 'ufa'])) {
					// 1. подлежат прохождению ДВН.
					// 2. не проходили ДВН согласно возраста диспансеризации (кратно трем до 40 лет).
					// 3. нет карты диспансеризации за два предыдущих года.
					$filter .= "
						AND (
							(
								-- ДВН проводится раз в 3 года.
								-- от 18 до 39 (младше 40 лет).
								(dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') >= 18)
								AND (dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') <= 39)
								-- Возраст кратен 3-м.
								AND (dbo.Age2(PS.Person_BirthDay, '{$data['PersonDopDisp_Year']}-12-31') % 3 = 0)
								-- нет карты диспансеризации в указанному году и за два предыдущих года.
								AND (not exists (
									SELECT EvnPLDispProf_id 
									FROM v_EvnPLDispProf 
									WHERE 
										(date_part('year', EvnPLDispProf_disDT) BETWEEN {$data['PersonDopDisp_Year']} - 2 AND {$data['PersonDopDisp_Year']}) 
										AND Person_id = PS.Person_id 
									LIMIT 1
									))
							)
							-- Подлежащие ежегодному прохождению ДВН (инвалиды ВОВ и блокадники)
							OR PP.PersonPrivilege_id is not null AND PP.PrivilegeType_id in (10,11,50)
						)
					";
				}
			}
		}
		
		// Проф.осмотры взр. населения [5].
		if ($data["DispClass_id"] == 5) {
			$queryParams["PersonDopDisp_YearPrev"] = $data["PersonDopDisp_Year"] - 1;
			$filter .= "
				and PS.Person_BirthDay <= :PersonAge_18
				and dbo.Age2(PS.Person_BirthDay, '{$data["PersonDopDisp_Year"]}-12-31') % 3 != 0
				and EplDispProfLastYear.count = 0
			";
		}
		$filter .= " and IsPersonDopDispPlanned.PlanPersonList_id is null ";
		if (!$data["Person_isDopDispPassed"]) {
			$filter .= " and IsPersonDopDispPassed.EvnPLDisp_id is null ";
		} else {
			if (isset($data["EvnPLDisp_setDate_Range"][0])) {
				$filter .= " and IsPersonDopDispPassed.EvnPLDisp_setDate >= :EvnPLDisp_setDate_Range_0";
				$queryParams["EvnPLDisp_setDate_Range_0"] = $data["EvnPLDisp_setDate_Range"][0];
			}
			if (isset($data["EvnPLDisp_setDate_Range"][1])) {
				$filter .= " and IsPersonDopDispPassed.EvnPLDisp_setDate <= :EvnPLDisp_setDate_Range_1";
				$queryParams["EvnPLDisp_setDate_Range_1"] = $data["EvnPLDisp_setDate_Range"][1];
			}
			if (isset($data["EvnPLDisp_disDate_Range"][0])) {
				$filter .= " and IsPersonDopDispPassed.EvnPLDisp_disDate >= :EvnPLDisp_disDate_Range_0";
				$queryParams["EvnPLDisp_disDate_Range_0"] = $data["EvnPLDisp_disDate_Range"][0];
			}
			if (isset($data["EvnPLDisp_disDate_Range"][1])) {
				$filter .= " and IsPersonDopDispPassed.EvnPLDisp_disDate <= :EvnPLDisp_disDate_Range_1";
				$queryParams["EvnPLDisp_disDate_Range_1"] = $data["EvnPLDisp_disDate_Range"][1];
			}
		}
		$queryParams["PersonAge_18"] = date("Y-m-d", strtotime("-18 years", strtotime(date("Y-12-31"))));
		$queryParams["PersonAge_21"] = date("Y-m-d", strtotime("-21 years", strtotime(date("Y-12-31"))));
		$queryParams["PersonDopDisp_Year"] = $data["PersonDopDisp_Year"];
		$queryParams["DispClass_id"] = $data["DispClass_id"];
		if ($data["Person_isOftenApplying"] || $data["Person_isNotApplyingLastYear"]) {
			$queryParams["PersonDopDisp_YearPrev"] = $data["PersonDopDisp_Year"] - 1;
			if ($data["Person_isOftenApplying"]) {
				$filter .= " and EplLastYear.count >= 4 ";
			}
			if ($data["Person_isNotApplyingLastYear"]) {
				$filter .= " and EplLastYear.count = 0 ";
			}
		}
		if ($data["Person_isNotDispProf"]) {
			$queryParams["PersonDopDisp_YearPrev"] = $data["PersonDopDisp_Year"] - 1;
			$filter .= " and EplDispProfLastYear.count = 0 ";
		}
		if ($data["Person_isNotDispDop"]) {
			$queryParams["PersonDopDisp_YearPrev"] = $data["PersonDopDisp_Year"] - 1;
			$filter .= " and EplDispDopLastYear.count = 0 ";
		}
		$filter .= " and PS.Person_DeadDT is null";
	}
	
	public static function selectParams_RzhdRegistry(&$data, &$filter, &$queryParams)
	{
		$filter .= ' and RR.RzhdRegistry_delDT is null';

		if (!empty($data['RzhdRegistry_id'])) {
			$queryParams['RzhdRegistry_id'] = $data['RzhdRegistry_id'];
			$filter .= ' and RR.RzhdRegistry_id = :RzhdRegistry_id';
		}
		if (!empty($data['RzhdWorkerCategory_id'])) {
			$queryParams['RzhdWorkerCategory_id'] = $data['RzhdWorkerCategory_id'];
			$filter .= ' and RR.RzhdWorkerCategory_id = :RzhdWorkerCategory_id';
		}
		if (!empty($data['RzhdWorkerGroup_id'])) {
			$queryParams['RzhdWorkerGroup_id'] = $data['RzhdWorkerGroup_id'];
			$filter .= ' and RR.RzhdWorkerGroup_id = :RzhdWorkerGroup_id';
		}
		if (!empty($data['RzhdWorkerSubgroup_id'])) {
			$queryParams['RzhdWorkerSubgroup_id'] = $data['RzhdWorkerSubgroup_id'];
			$filter .= ' and RR.RzhdWorkerSubgroup_id = :RzhdWorkerSubgroup_id';
		}
		if (!empty($data['RzhdRegistry_PensionBegDate_Range'][0]) && !empty($data['RzhdRegistry_PensionBegDate_Range'][1])) {
			$queryParams['RzhdRegistry_PensionBegDate_Range_0'] = $data['RzhdRegistry_PensionBegDate_Range'][0];
			$queryParams['RzhdRegistry_PensionBegDate_Range_1'] = $data['RzhdRegistry_PensionBegDate_Range'][1];
			$filter .= ' and RR.RzhdRegistry_PensionBegDate between cast(:RzhdRegistry_PensionBegDate_Range_0 as date) and cast(:RzhdRegistry_PensionBegDate_Range_1 as date)';
		}
		if (!empty($data['Register_setDate_Range'][0]) && !empty($data['Register_setDate_Range'][1])) {
			$queryParams['Register_setDate_Range_0'] = $data['Register_setDate_Range'][0];
			$queryParams['Register_setDate_Range_1'] = $data['Register_setDate_Range'][1];
			$filter .= ' and R.Register_setDate between cast(:Register_setDate_Range_0 as date) and cast(:Register_setDate_Range_1 as date)';
		}
		if (!empty($data['Register_disDate_Range'][0]) && !empty($data['Register_disDate_Range'][1])) {
			$queryParams['Register_disDate_Range_0'] = $data['Register_disDate_Range'][0];
			$queryParams['Register_disDate_Range_1'] = $data['Register_disDate_Range'][1];
			$filter .= ' and R.Register_disDate between cast(:Register_disDate_Range_0 as date) and cast(:Register_disDate_Range_1 as date)';
		}
		if (!empty($data['RzhdOrg_id'])) {
			$queryParams['RzhdOrg_id'] = $data['RzhdOrg_id'];
			$filter .= ' and RR.Org_id =:RzhdOrg_id';
		}
		if (!empty($data['RegisterDisCause_id'])) {
			$queryParams['RegisterDisCause_id'] = $data['RegisterDisCause_id'];
			$filter .= ' and R.RegisterDisCause_id =:RegisterDisCause_id';
		}
	}

	public static function selectParams_VenerRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "vener";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
	}

	public static function selectParams_HIVRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "hiv";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["MorbusHIV_NumImmun"])) {
			$filter .= " and MH.MorbusHIV_NumImmun = :MorbusHIV_NumImmun";
			$queryParams["MorbusHIV_NumImmun"] = $data["MorbusHIV_NumImmun"];
		}
	}

	public static function selectParams_FmbaRegistry(&$data, &$filter, &$queryParams)
	{
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
	}

	public static function selectParams_LargeFamilyRegistry(&$data, &$filter, &$queryParams)
	{
		$registers = [
			"DiabetesRegistry" => "diabetes",
			"LargeFamilyRegistry" => "large family"
		];
		$queryParams["MorbusType_SysNick"] = $registers[$data["SearchFormType"]];
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
	}

	public static function selectParams_TubRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "tub";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["isNeglected"])) {
			if ($data["isNeglected"] == 1) {
				$filter .= "
					and not exists (
						select mdr.MorbusTubMDR_id
						from v_MorbusTubMDR mdr
						where mdr.Morbus_id = PR.Morbus_id
					)
				";
			} else if ($data["isNeglected"] == 2) {
				$filter .= "
					and exists (
						select mdr.MorbusTubMDR_id
						from v_MorbusTubMDR mdr
						where mdr.Morbus_id = PR.Morbus_id
					)
				";
			}
		}
		if (!empty($data["isGeneralForm"])) {
			if ($data["isGeneralForm"] == 1) {
				$filter .= "
					and not exists (
						select tdgf.TubDiagGeneralForm_id
						from v_TubDiagGeneralForm tdgf
						where tdgf.MorbusTub_id = MO.MorbusTub_id
					)
				";
			} else if ($data["isGeneralForm"] == 2) {
				$filter .= "
					and exists (
						select tdgf.TubDiagGeneralForm_id
						from v_TubDiagGeneralForm tdgf
						where tdgf.MorbusTub_id = MO.MorbusTub_id
					)
				";
			}
		}
	}

	public static function selectParams_ProfRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "prof";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["MorbusProfDiag_id"])) {
			$filter .= " and mo.MorbusProfDiag_id = :MorbusProfDiag_id";
			$queryParams["MorbusProfDiag_id"] = $data["MorbusProfDiag_id"];
		}
		if (isset($data["OrgWork_id"])) {
			$filter .= " and job.Org_id = :OrgWork_id";
			$queryParams["OrgWork_id"] = $data["OrgWork_id"];
		}
		if ($data["Person_IsDead"]) {
			$filter .= " and PS.Person_IsDead = 2";
		}
		if ($data["Person_DeRegister"]) {
			$filter .= " and pcs.CardCloseCause_id = 5";
		}
	}

	public static function selectParams_IBSRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "ibs";
		if (!empty($data["IBSType_id"])) {
			$queryParams["IBSType_id"] = $data["IBSType_id"];
			$filter .= " and MO.IBSType_id = :IBSType_id ";
		}
		if (!empty($data["MorbusIBS_IsKGIndication"])) {
			$queryParams["MorbusIBS_IsKGIndication"] = $data["MorbusIBS_IsKGIndication"];
			$filter .= " and MO.MorbusIBS_IsKGIndication = :MorbusIBS_IsKGIndication ";
		}
		if (!empty($data["MorbusIBS_IsKGFinished"])) {
			$queryParams["MorbusIBS_IsKGFinished"] = $data["MorbusIBS_IsKGFinished"];
			$filter .= " and MO.MorbusIBS_IsKGFinished = :MorbusIBS_IsKGFinished ";
		}
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
	}

	public static function selectParams_EndoRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "nephro";
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegisterEndo_hospDate_Range"][0]) && isset($data["PersonRegisterEndo_hospDate_Range"][1])) {
			$queryParams["PersonRegisterEndo_hospDate_Range_0"] = $data["PersonRegisterEndo_hospDate_Range"][0];
			$queryParams["PersonRegisterEndo_hospDate_Range_1"] = $data["PersonRegisterEndo_hospDate_Range"][1];
			$filter .= " and PRE.PersonRegisterEndo_hospDate::date between :PersonRegisterEndo_hospDate_Range_0 and :PersonRegisterEndo_hospDate_Range_1 ";
		}
		if (!empty($data["PersonRegister_Code"])) {
			$filter .= " and PR.PersonRegister_Code = :PersonRegister_Code";
			$queryParams["PersonRegister_Code"] = $data["PersonRegister_Code"];
		}
		if (!empty($data["Lpu_iid"])) {
			$filter .= " and PR.Lpu_iid = :Lpu_iid";
			$queryParams["Lpu_iid"] = $data["Lpu_iid"];
		}
		if (!empty($data["MedPersonal_iid"])) {
			$filter .= " and PR.MedPersonal_iid = :MedPersonal_iid";
			$queryParams["MedPersonal_iid"] = $data["MedPersonal_iid"];
		}
		if (!empty($data["ProsthesType_id"])) {
			$filter .= " and PRE.ProsthesType_id = :ProsthesType_id";
			$queryParams["ProsthesType_id"] = $data["ProsthesType_id"];
		}
	}

	public static function selectParams_NephroRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "nephro";
		$queryParams["Diab_Diag_Code_Start"] = "E10.0";
		$queryParams["Diab_Diag_Code_End"] = "E11.9";
		if (!empty($data["PersonRegisterType_id"])) {
			if (getRegionNick() == "ufa") {
				if (in_array($data["PersonRegisterType_id"], [2, 3, 4])) {
					$filter .= " and PR.PersonRegister_disDate is null ";
				}
				switch ($data["PersonRegisterType_id"]) {
					case 2:
						$filter .= " and NRT.NephroResultType_Code = 1 ";
						break;
					case 3:
						$filter .= " and NRT.NephroResultType_Code in (2,3,5)";
						break;
					case 4:
						$filter .= " and NRT.NephroResultType_Code = 4 ";
						break;
					case 5:
						$filter .= " and NRT.NephroResultType_Code in (6, 7, 8) or PR.PersonRegister_disDate is not null";
						break;
				}
			} else {
				if ($data["PersonRegisterType_id"] == 2) {
					$filter .= " and PR.PersonRegister_disDate is null ";
				}
				if ($data["PersonRegisterType_id"] == 3) {
					$filter .= " and PR.PersonRegister_disDate is not null ";
				}
			}
		}
		if (!empty($data["NephroPersonStatus_id"])) {
			$queryParams["NephroPersonStatus_id"] = $data["NephroPersonStatus_id"];
			$filter .= " and MO.NephroPersonStatus_id = :NephroPersonStatus_id";
		}
		if (!empty($data["PersonCountAtDate"])) {
			$queryParams["PersonCountAtDate"] = $data["PersonCountAtDate"];
			$filter .= " and :PersonCountAtDate >= PR.PersonRegister_setDate::date and :PersonCountAtDate < coalesce(PR.PersonRegister_disDate::date, '2999-01-01'::date)";
		}
		if (!empty($data["DialysisCenter_id"])) {
			$queryParams["DialysisCenter_id"] = $data["DialysisCenter_id"];
			$filter .= ($data["DialysisCenter_id"] == -1)
				? " and MO.Lpu_id is null"
				: " and MO.Lpu_id = :DialysisCenter_id";
		}
		if (!empty($data["NephroCRIType_id"])) {
			$filter .= " and MO.NephroCRIType_id = :NephroCRIType_id";
			$queryParams["NephroCRIType_id"] = $data["NephroCRIType_id"];
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["MorbusNephro_DialDate_Range"][0]) && isset($data["MorbusNephro_DialDate_Range"][1])) {
			$queryParams["MorbusNephro_DialDate_Range_0"] = $data["MorbusNephro_DialDate_Range"][0];
			$queryParams["MorbusNephro_DialDate_Range_1"] = $data["MorbusNephro_DialDate_Range"][1];
			$filter .= " and MO.MorbusNephro_dialDate::date between :MorbusNephro_DialDate_Range_0 and :MorbusNephro_DialDate_Range_1";
		}
		if (isset($data["MorbusNephro_DialEndDate_Range"][0]) && isset($data["MorbusNephro_DialEndDate_Range"][1])) {
			$queryParams["MorbusNephro_DialEndDate_Range_0"] = $data["MorbusNephro_DialEndDate_Range"][0];
			$queryParams["MorbusNephro_DialEndDate_Range_1"] = $data["MorbusNephro_DialEndDate_Range"][1];
			$filter .= " and MO.MorbusNephro_DialEndDate::date between :MorbusNephro_DialEndDate_Range_0 and :MorbusNephro_DialEndDate_Range_1";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["Diab_Diag_Code_From"])) {
			$filter .= "
				and (
					DiabEvnDiagSpec.Diag_Code >= :Diab_Diag_Code_From or
					DiabEvnSection.Diag_Code >= :Diab_Diag_Code_From or
					DiabEvnVizitPL.Diag_Code >= :Diab_Diag_Code_From or
					DiabEvnDiagPLSop.Diag_Code >= :Diab_Diag_Code_From or
					DiabEvnDiagPS.Diag_Code >= :Diab_Diag_Code_From or
					DiabEvnUslugaDispDop.Diag_Code >= :Diab_Diag_Code_From or
					DiabEvnDiagDopDisp.Diag_Code >= :Diab_Diag_Code_From
				)
			";
			$queryParams["Diab_Diag_Code_From"] = $data["Diab_Diag_Code_From"];
		}
		if (isset($data["Diab_Diag_Code_To"])) {
			$filter .= "
				and (
					DiabEvnDiagSpec.Diag_Code <= :Diab_Diag_Code_To or
					DiabEvnSection.Diag_Code <= :Diab_Diag_Code_To or
					DiabEvnVizitPL.Diag_Code <= :Diab_Diag_Code_To or
					DiabEvnDiagPLSop.Diag_Code <= :Diab_Diag_Code_To or
					DiabEvnDiagPS.Diag_Code <= :Diab_Diag_Code_To or
					DiabEvnUslugaDispDop.Diag_Code <= :Diab_Diag_Code_To or
					DiabEvnDiagDopDisp.Diag_Code <= :Diab_Diag_Code_To
				)
			";
			$queryParams["Diab_Diag_Code_To"] = $data["Diab_Diag_Code_To"];
		}
		if (isset($data["PersonVisit_Date_Range"][0]) && isset($data["PersonVisit_Date_Range"][1])) {
			$queryParams["PersonVisit_Date_Range_0"] = $data["PersonVisit_Date_Range"][0];
			$queryParams["PersonVisit_Date_Range_1"] = $data["PersonVisit_Date_Range"][1];
			$filter .= " and lastVizitDate.EvnVizitPL_setDate::date between :PersonVisit_Date_Range_0 and :PersonVisit_Date_Range_1 ";
		}
		if (!empty($data["MonthsWithoutNefroVisit"])) {
			switch ($data["MonthsWithoutNefroVisit"]) {
				case 1:
					$filter .= " and lastVizitDate.EvnVizitPL_setDate is null";
					break;
				case 2:
					$filter .= " and date_part('month', dbo.tzgetdate() - lastVizitDate.EvnVizitPL_setDate) > 1";
					break;
				case 3:
					$filter .= " and date_part('month', dbo.tzgetdate() - lastVizitDate.EvnVizitPL_setDate) > 3";
					break;
				case 4:
					$filter .= " and date_part('month', dbo.tzgetdate() - lastVizitDate.EvnVizitPL_setDate) > 12";
					break;
			}
		}
	}

	public static function selectParams_PalliatRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["PersonRegisterType_SysNick"] = $data["PersonRegisterType_SysNick"];
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (!empty($data['PersonRegisterOutCause_id'])) {
			$queryParams['PersonRegisterOutCause_id'] = $data['PersonRegisterOutCause_id'];
			$filter .= ' and PR.PersonRegisterOutCause_id = :PersonRegisterOutCause_id ';
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (false == empty($data["PersonRegister_Code"])) {
			$filter .= " and PR.PersonRegister_Code = :PersonRegister_Code";
			$queryParams["PersonRegister_Code"] = $data["PersonRegister_Code"];
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (!empty($data["MorbusPalliat_IsIVL"])) {
			$filter .= " and MO.MorbusPalliat_IsIVL = :MorbusPalliat_IsIVL";
			$queryParams["MorbusPalliat_IsIVL"] = $data["MorbusPalliat_IsIVL"];
		}
		if (!empty($data["AnesthesiaType_id"])) {
			$filter .= ($data["AnesthesiaType_id"] < 0)
				?" and MO.MorbusPalliat_IsAnesthesia = 1"
				:" and MO.AnesthesiaType_id = :AnesthesiaType_id";
			if ($data["AnesthesiaType_id"] >= 0) {
				$queryParams["AnesthesiaType_id"] = $data["AnesthesiaType_id"];
			}
		}
		if (!empty($data["MorbusPalliat_IsZond"])) {
			$filter .= " and MO.MorbusPalliat_IsZond = :MorbusPalliat_IsZond";
			$queryParams["MorbusPalliat_IsZond"] = $data["MorbusPalliat_IsZond"];
		}
		if (!empty($data["ViolationsDegreeType_id"])) {
			$filter .= " and MO.ViolationsDegreeType_id = :ViolationsDegreeType_id";
			$queryParams["ViolationsDegreeType_id"] = $data["ViolationsDegreeType_id"];
		}
		if (!empty($data["Lpu_sid"])) {
			$filter .= " and MO.Lpu_sid = :Lpu_sid";
			$queryParams["Lpu_sid"] = $data["Lpu_sid"];
		}
		if (!empty($data["Lpu_aid"])) {
			$filter .= " and MO.Lpu_aid = :Lpu_aid";
			$queryParams["Lpu_aid"] = $data["Lpu_aid"];
		}
		if (!isSuperAdmin() && !haveARMType("spec_mz") && !havingGroup("RegistryPalliatCareAll")) {
			$filter .= "
				and (
					PC.Lpu_id = :Lpu_id or
					pr.Lpu_iid = :Lpu_id or
					MO.Lpu_sid = :Lpu_id or
					MO.Lpu_aid = :Lpu_id
				)
			";
			$queryParams["Lpu_id"] = $data["Lpu_id"];
		}
	}

	public static function selectParams_PersonRegisterBase(&$data, &$filter, &$queryParams)
	{
		$queryParams["PersonRegisterType_SysNick"] = $data["PersonRegisterType_SysNick"];
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (false == empty($data["PersonRegister_Code"])) {
			$filter .= " and PR.PersonRegister_Code = :PersonRegister_Code";
			$queryParams["PersonRegister_Code"] = $data["PersonRegister_Code"];
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
	}

	public static function selectParams_NarkoRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "narc";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["RegLpu_id"]) && !empty($data["RegLpu_id"])) {
			$filter .= " and Lpu2.Lpu_id = :RegLpu_id";
			$queryParams["RegLpu_id"] = $data["RegLpu_id"];
		}
	}

	public static function selectParams_CrazyRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "crazy";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["RegLpu_id"]) && !empty($data["RegLpu_id"])) {
			$filter .= " and Lpu2.Lpu_id = :RegLpu_id";
			$queryParams["RegLpu_id"] = $data["RegLpu_id"];
		}
	}

	public static function selectParams_ACSRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "acs";
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (!empty($data["DiagACS_id"])) {
			$filter .= " and PR.Diag_id = :DiagACS_id";
			$queryParams["DiagACS_id"] = $data["DiagACS_id"];
		}
		if (!empty($data["Lpu_iid"])) {
			$filter .= " and PR.Lpu_iid = :Lpu_iid";
			$queryParams["Lpu_iid"] = $data["Lpu_iid"];
		}
		if (!empty($data["MorbusACS_IsST"])) {
			$filter .= " and MA.MorbusACS_IsST = :MorbusACS_IsST";
			$queryParams["MorbusACS_IsST"] = $data["MorbusACS_IsST"];
		}
		if (!empty($data["MorbusACS_IsCoronary"])) {
			$filter .= " and MA.MorbusACS_IsCoronary = :MorbusACS_IsCoronary";
			$queryParams["MorbusACS_IsCoronary"] = $data["MorbusACS_IsCoronary"];
		}
		if (!empty($data["MorbusACS_IsTransderm"])) {
			$filter .= " and MA.MorbusACS_IsTransderm = :MorbusACS_IsTransderm";
			$queryParams["MorbusACS_IsTransderm"] = $data["MorbusACS_IsTransderm"];
		}
	}

	public static function selectParams_OrphanRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "orphan";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
	}

	public static function selectParams_PalliatNotify(&$data, &$filter, &$queryParams)
	{
		if (isset($data["Lpu_sid"])) {
			$filter .= " and Lpu.Lpu_id = :Lpu_sid ";
			$queryParams["Lpu_sid"] = $data["Lpu_sid"];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= " and ENB.EvnNotifyBase_niDate is null and PR.PersonRegister_id is null ";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= " and (ENB.EvnNotifyBase_niDate is not null or PR.PersonRegister_id is not null) ";
			}
		}
	}

	public static function selectParams_EvnNotifyVener(&$data, &$filter, &$queryParams)
	{
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and ENC.EvnNotifyVener_setDT >= :EvnNotifyBase_setDT_Range_0::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and ENC.EvnNotifyVener_setDT <= :EvnNotifyBase_setDT_Range_1::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENC.EvnNotifyVener_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and (ENC.EvnNotifyVener_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
	}

	public static function selectParams_EvnNotifyHIV(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "hiv";
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and ENB.EvnNotifyBase_setDT >= :EvnNotifyBase_setDT_Range_0::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and ENB.EvnNotifyBase_setDT <= :EvnNotifyBase_setDT_Range_1::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["HIVNotifyType_id"])) {
			$filter .= " and HIVNotifyType.HIVNotifyType_id = :HIVNotifyType_id ";
			$queryParams["HIVNotifyType_id"] = $data["HIVNotifyType_id"];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENB.EvnNotifyBase_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and (ENB.EvnNotifyBase_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
	}

	public static function selectParams_EvnNotifyTub(&$data, &$filter, &$queryParams)
	{
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["TubDiagSop_id"])) {
			$filter .= "
				and exists(
					select 1
					from v_TubDiagSopLink tdsl
					where tdsl.EvnNotifyTub_id = ENC.EvnNotifyTub_id
					  and tdsl.TubDiagSop_id = :TubDiagSop_id
					limit 1
				)
			";
			$queryParams["TubDiagSop_id"] = $data["TubDiagSop_id"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and ENC.EvnNotifyTub_setDT >= :EvnNotifyBase_setDT_Range_0::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and ENC.EvnNotifyTub_setDT <= :EvnNotifyBase_setDT_Range_1::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["EvnNotifyTub_IsFirstDiag"])) {
			$filter .= " and ENC.EvnNotifyTub_IsFirstDiag = :EvnNotifyTub_IsFirstDiag";
			$queryParams["EvnNotifyTub_IsFirstDiag"] = $data["EvnNotifyTub_IsFirstDiag"];
		}
		if (isset($data["PersonCategoryType_id"])) {
			$filter .= " and ENC.PersonCategoryType_id = :PersonCategoryType_id";
			$queryParams["PersonCategoryType_id"] = $data["PersonCategoryType_id"];
		}
		if (isset($data["TubSurveyGroupType_id"])) {
			$filter .= ($data["TubSurveyGroupType_id"] == 2)
				? " and ENC.TubSurveyGroupType_id is not null"
				: " and ENC.TubSurveyGroupType_id is null";
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENC.EvnNotifyTub_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and (ENC.EvnNotifyTub_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
	}

	public static function selectParams_EvnNotifyProf(&$data, &$filter, &$queryParams)
	{
		if (isset($data["Lpu_did"])) {
			$filter .= " and ENC.Lpu_did = :Lpu_did ";
			$queryParams["Lpu_did"] = $data["Lpu_did"];
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and to_char(ENC.EvnNotifyProf_setDT, 'yyyy-mm-dd') >= :EvnNotifyBase_setDT_Range_0 ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and to_char(ENC.EvnNotifyProf_setDT, 'yyyy-mm-dd') <= :EvnNotifyBase_setDT_Range_1 ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENC.EvnNotifyProf_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and (ENC.EvnNotifyProf_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
		if (isset($data["OrgWork_id"])) {
			$filter .= " and ENC.Org_id = :OrgWork_id";
			$queryParams["OrgWork_id"] = $data["OrgWork_id"];
		}
	}

	public static function selectParams_EvnNotifyNephro(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and to_char(ENC.EvnNotifyNephro_setDT, '{$callObject->dateTimeForm120}') >= :EvnNotifyBase_setDT_Range_0 ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and to_char(ENC.EvnNotifyNephro_setDT, '{$callObject->dateTimeForm120}') <= :EvnNotifyBase_setDT_Range_1 ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENC.EvnNotifyNephro_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and (ENC.EvnNotifyNephro_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
		if (!empty($data["isNotVizitMonth"]) && $data["isNotVizitMonth"] == 2) {
			$filter .= "
				and not exists (
					select 1
					from
						v_EvnVizitPL vp
						left join v_MedStaffFactCache msfc on vp.MedStaffFact_id = msfc.MedStaffFact_id
					where  vp.Person_id = PS.Person_id 
					  and vp.EvnVizitPL_setDT >= ENC.EvnNotifyNephro_setDT
					  and msfc.Post_id = 39
					limit 1
				)
			";
		}
	}

	public static function selectParams_EvnNotifyNarko(&$data, &$filter, &$queryParams)
	{
		$filter .= " and Diag_pid in (705, 706, 707, 708, 709, 710, 711, 712, 713, 714) ";
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and ENC.EvnNotifyNarco_setDT >= :EvnNotifyBase_setDT_Range_0::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and ENC.EvnNotifyNarco_setDT <= :EvnNotifyBase_setDT_Range_1::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENC.EvnNotifyNarco_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and (ENC.EvnNotifyNarco_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
	}

	public static function selectParams_EvnNotifyCrazy(&$data, &$filter, &$queryParams)
	{
		$filter .= " and Diag_pid not in (705, 706, 707, 708, 709, 710, 711, 712, 713, 714) ";
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and ENC.EvnNotifyCrazy_setDT >= :EvnNotifyBase_setDT_Range_0::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and ENC.EvnNotifyCrazy_setDT <= :EvnNotifyBase_setDT_Range_1::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENC.EvnNotifyCrazy_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and (ENC.EvnNotifyCrazy_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
	}

	public static function selectParams_EvnNotifyOrphan(&$data, &$filter, &$queryParams)
	{
		switch ($data["EvnNotifyType_SysNick"]) {
			case "EvnNotifyOrphan":
				$filter .= " and ENO.EvnNotifyType_SysNick = 'EvnNotifyOrphan' ";
				break;
			case "EvnNotifyOrphanOut":
				$filter .= " and ENO.EvnNotifyType_SysNick = 'EvnNotifyOrphanOut' ";
				break;
			case "EvnDirectionOrphan":
				$filter .= " and ENO.EvnNotifyType_SysNick = 'EvnDirectionOrphan' ";
				break;
			default:
				break;
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and ENO.EvnNotifyOrphan_setDT >= :EvnNotifyBase_setDT_Range_0::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and ENO.EvnNotifyOrphan_setDT <= :EvnNotifyBase_setDT_Range_1::timestamp ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["Lpu_sid"])) {
			$filter .= " and ENO.Lpu_oid = :Lpu_oid ";
			$queryParams["Lpu_oid"] = $data["Lpu_sid"];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENO.EvnNotifyType_SysNick = EvnNotifyOrphan'
					and ENO.EvnNotifyOrphan_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and ENO.EvnNotifyType_SysNick = 'EvnNotifyOrphan'
					and (ENO.EvnNotifyOrphan_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
	}

	public static function selectParams_EvnNotifyRegister(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		$queryParams["PersonRegisterType_SysNick"] = $data["PersonRegisterType_SysNick"];
		if (!empty($data["NotifyType_id"])) {
			$filter .= " and EN.NotifyType_id = :NotifyType_id ";
			$queryParams["NotifyType_id"] = $data["NotifyType_id"];
		}
		$filter .= "
			and PRT.PersonRegisterType_SysNick = :PersonRegisterType_SysNick
			and coalesce(E.Evn_deleted, 1) = 1
			and E.EvnClass_id = 176
		";
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["Diag_Code_Group"])) {
			$filter .= " and (DiagGroup.Diag_id = :Diag_Code_Group or Diag.Diag_id = :Diag_Code_Group) ";
			$queryParams["Diag_Code_Group"] = $data["Diag_Code_Group"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and E.Evn_setDT::date >= :EvnNotifyBase_setDT_Range_0::date ";
			$queryParams["EvnNotifyBase_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and E.Evn_setDT::date <= :EvnNotifyBase_setDT_Range_1::date ";
			$queryParams["EvnNotifyBase_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and EN.NotifyType_id = 1
					and ENB.EvnNotifyBase_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and EN.NotifyType_id = 1
					and (ENB.EvnNotifyBase_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
		if (isset($data["Lpu_oid"])) {
			$filter .= " and EN.Lpu_oid = :Lpu_oid ";
			$queryParams["Lpu_oid"] = $data["Lpu_oid"];
		}
		if ($callObject->getRegionNick() == "khak" && $data["PersonRegisterType_SysNick"] == "orphan") {
			$filter .= " and Diag.Diag_Code in ('D59.3','D59.5','D61.9','D68.2','D69.3','D84.1','E22.8','E70.0','E70.1','E70.2','E71.0','E71.1','E71.3','E72.1','E72.3','E74.2','E75.2','E76.0','E76.1','E76.2','E80.2','E83.0','I27.0','M08.2','Q78.0') ";
		}
	}

	public static function selectParams_HepatitisRegistry(&$data, &$filter, &$queryParams)
	{
		$queryParams["MorbusType_SysNick"] = "hepa";
		if (empty($data["AttachLpu_id"]) && (empty($data["session"]["groups"]) || strpos($data["session"]["groups"], "HepatitisRegistry") < 0)) {
			$filter .= " and PC.Lpu_id  = :AttachLpu_id ";
			$queryParams["AttachLpu_id"] = $data["session"]["lpu_id"];
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["Diag_id"])) {
			$filter .= " and PR.Diag_id = :Diag_id ";
			$queryParams["Diag_id"] = $data["Diag_id"];
		}
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			}
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (!empty($data["MorbusHepatitisDiag_setDT_Range"][0])) {
			$filter .= " and MHD.MorbusHepatitisDiag_setDT >= :MorbusHepatitisDiag_setDT_Range_0::timestamp ";
			$queryParams["MorbusHepatitisDiag_setDT_Range_0"] = $data["MorbusHepatitisDiag_setDT_Range"][0];
		}
		if (!empty($data["MorbusHepatitisDiag_setDT_Range"][1])) {
			$filter .= " and MHD.MorbusHepatitisDiag_setDT <= :MorbusHepatitisDiag_setDT_Range_1::timestamp ";
			$queryParams["MorbusHepatitisDiag_setDT_Range_1"] = $data["MorbusHepatitisDiag_setDT_Range"][1];
		}
		if (!empty($data["HepatitisDiagType_id"])) {
			$filter .= " and MHD.HepatitisDiagType_id = :HepatitisDiagType_id ";
			$queryParams["HepatitisDiagType_id"] = $data["HepatitisDiagType_id"];
		}
		if (!empty($data["HepatitisDiagActiveType_id"])) {
			$filter .= " and MHD.HepatitisDiagActiveType_id = :HepatitisDiagActiveType_id ";
			$queryParams["HepatitisDiagActiveType_id"] = $data["HepatitisDiagActiveType_id"];
		}
		if (!empty($data["HepatitisFibrosisType_id"])) {
			$filter .= " and MHD.HepatitisFibrosisType_id = :HepatitisFibrosisType_id ";
			$queryParams["HepatitisFibrosisType_id"] = $data["HepatitisFibrosisType_id"];
		}
		if (!empty($data["HepatitisEpidemicMedHistoryType_id"])) {
			$filter .= " and MH.HepatitisEpidemicMedHistoryType_id = :HepatitisEpidemicMedHistoryType_id ";
			$queryParams["HepatitisEpidemicMedHistoryType_id"] = $data["HepatitisEpidemicMedHistoryType_id"];
		}
		if (!empty($data["MorbusHepatitis_EpidNum"])) {
			$filter .= " and MH.MorbusHepatitis_EpidNum = :MorbusHepatitis_EpidNum ";
			$queryParams["MorbusHepatitis_EpidNum"] = $data["MorbusHepatitis_EpidNum"];
		}
		if ((isset($data["MorbusHepatitisLabConfirm_setDT_Range"][0]) && isset($data["MorbusHepatitisLabConfirm_setDT_Range"][1])) || !empty($data["HepatitisLabConfirmType_id"]) || !empty($data["MorbusHepatitisLabConfirm_Result"])) {
			if (isset($data["MorbusHepatitisLabConfirm_setDT_Range"][0]) && isset($data["MorbusHepatitisLabConfirm_setDT_Range"][1])) {
				$queryParams["MorbusHepatitisLabConfirm_setDT_Range_0"] = $data["MorbusHepatitisLabConfirm_setDT_Range"][0];
				$queryParams["MorbusHepatitisLabConfirm_setDT_Range_1"] = $data["MorbusHepatitisLabConfirm_setDT_Range"][1];
			}
			if (!empty($data["HepatitisLabConfirmType_id"])) {
				$queryParams["HepatitisLabConfirmType_id"] = $data["HepatitisLabConfirmType_id"];
			}
			if (!empty($data["MorbusHepatitisLabConfirm_Result"])) {
				$queryParams["MorbusHepatitisLabConfirm_Result"] = $data["MorbusHepatitisLabConfirm_Result"];
			}
		}
		if ((isset($data["MorbusHepatitisFuncConfirm_setDT_Range"][0]) && isset($data["MorbusHepatitisFuncConfirm_setDT_Range"][1])) || !empty($data["HepatitisFuncConfirmType_id"]) || !empty($data["MorbusHepatitisFuncConfirm_Result"])) {
			if (isset($data["MorbusHepatitisFuncConfirm_setDT_Range"][0]) && isset($data["MorbusHepatitisFuncConfirm_setDT_Range"][1])) {
				$queryParams["MorbusHepatitisFuncConfirm_setDT_Range_0"] = $data["MorbusHepatitisFuncConfirm_setDT_Range"][0];
				$queryParams["MorbusHepatitisFuncConfirm_setDT_Range_1"] = $data["MorbusHepatitisFuncConfirm_setDT_Range"][1];
			}
			if (!empty($data["HepatitisFuncConfirmType_id"])) {
				$queryParams["HepatitisFuncConfirmType_id"] = $data["HepatitisFuncConfirmType_id"];
			}
			if (!empty($data["MorbusHepatitisFuncConfirm_Result"])) {
				$queryParams["MorbusHepatitisFuncConfirm_Result"] = $data["MorbusHepatitisFuncConfirm_Result"];
			}
		}
		if (isset($data["MorbusHepatitisCure_begDT"]) || isset($data["MorbusHepatitisCure_endDT"]) || !empty($data["HepatitisResultClass_id"]) || !empty($data["HepatitisSideEffectType_id"]) || !empty($data["MorbusHepatitisCure_Drug"])) {
			if (isset($data["MorbusHepatitisCure_begDT"])) {
				$queryParams["MorbusHepatitisCure_begDT"] = $data["MorbusHepatitisCure_begDT"];
			}
			if (isset($data["MorbusHepatitisCure_endDT"])) {
				$queryParams["MorbusHepatitisCure_endDT"] = $data["MorbusHepatitisCure_endDT"];
			}
			if (!empty($data["HepatitisResultClass_id"])) {
				$queryParams["HepatitisResultClass_id"] = $data["HepatitisResultClass_id"];
			}
			if (!empty($data["HepatitisSideEffectType_id"])) {
				$queryParams["HepatitisSideEffectType_id"] = $data["HepatitisSideEffectType_id"];
			}
			if (!empty($data["MorbusHepatitisCure_Drug"])) {
				$queryParams["MorbusHepatitisCure_Drug"] = $data["MorbusHepatitisCure_Drug"];
			}
		}
		if (!empty($data["HepatitisQueueType_id"])) {
			$filter .= " and MHQ.HepatitisQueueType_id = :HepatitisQueueType_id ";
			$queryParams["HepatitisQueueType_id"] = $data["HepatitisQueueType_id"];
		}
		if (!empty($data["MorbusHepatitisQueue_Num"])) {
			$filter .= " and MHQ.MorbusHepatitisQueue_Num = :MorbusHepatitisQueue_Num ";
			$queryParams["MorbusHepatitisQueue_Num"] = $data["MorbusHepatitisQueue_Num"];
		}
		if (!empty($data["MorbusHepatitisQueue_IsCure"])) {
			$filter .= " and coalesce(MHQ.MorbusHepatitisQueue_IsCure, 1) = :MorbusHepatitisQueue_IsCure ";
			$queryParams["MorbusHepatitisQueue_IsCure"] = $data["MorbusHepatitisQueue_IsCure"];
		}
	}

	public static function selectParams_ReanimatRegistry(&$data, &$filter, &$queryParams)
	{
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and RR.ReanimatRegister_disDate is null ";
			} elseif ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and RR.ReanimatRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["ReanimatRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["ReanimatRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and RR.ReanimatRegister_setDate::date between :ReanimatRegister_setDate_Range_0 and :ReanimatRegister_setDate_Range_1 ";
		}
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["ReanimatRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["ReanimatRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and RR.ReanimatRegister_disDate::date between :ReanimatRegister_disDate_Range_0 and :ReanimatRegister_disDate_Range_1 ";
		}
		if (!empty($data["MorbusType_id"])) {
			$filter .= " and RR.MorbusType_id = :MorbusType_id ";
			$queryParams["MorbusType_id"] = $data["MorbusType_id"];
		}
		$data["ReanimatRegister_IsPeriodNow"] = $data["ReanimatRegister_IsPeriodNow"] + 1;
		if ($data["ReanimatRegister_IsPeriodNow"] == 2) {
			$filter .= " and RR.ReanimatRegister_IsPeriodNow = :ReanimatRegister_IsPeriodNow ";
			$queryParams["ReanimatRegister_IsPeriodNow"] = $data["ReanimatRegister_IsPeriodNow"];
		}
		if ((!empty($data["RRW_BeginDate"])) && (!empty($data["RRW_EndDate"]))) {
			$filter .= "
				and exists(
					select 1
					from v_EvnReanimatPeriod ERP3 
					where ERP3.Person_id = PS.Person_id 
					  and ERP3.EvnReanimatPeriod_setDT <= :RRW_EndDate::date + interval '1 day'
					  and (ERP3.EvnReanimatPeriod_disDT > :RRW_BeginDate or ERP3.EvnReanimatPeriod_disDT is null)
				)
			";
			$queryParams["RRW_BeginDate"] = $data["RRW_BeginDate"];
			$queryParams["RRW_EndDate"] = $data["RRW_EndDate"];
		}
		if (!empty($data["EvnScaleType"])) {
			$filter .= "
				and exists (
					select 1
					from v_EvnScale ES 
					where ES.EvnScale_pid = ERP2.EvnReanimatPeriod_id
					  and ES.ScaleType_id = :EvnScaleType
			";
			$queryParams["EvnScaleType"] = $data["EvnScaleType"];
			if (!empty($data["EvnScaleFrom"])) {
				$filter .= " and ES.EvnScale_Result >= :EvnScaleFrom ";
				$queryParams["EvnScaleFrom"] = $data["EvnScaleFrom"];
			}
			if (!empty($data["EvnScaleTo"])) {
				$filter .= " and ES.EvnScale_Result <= :EvnScaleTo ";
				$queryParams["EvnScaleTo"] = $data["EvnScaleTo"];
			}

			$filter .= ") ";
		}
		if ((!empty($data["ReanimatActionType"])) || (!empty($data["RA_DrugNames"]))) {
			$filter .= "
				and exists (
					select 1
					from v_EvnReanimatAction ERA 
					where ERA.EvnReanimatAction_pid = ERP2.EvnReanimatPeriod_id
			";
			if (!empty($data["ReanimatActionType"])) {
				$filter .= " and ERA.ReanimatActionType_id = :ReanimatActionType ";
				$queryParams["ReanimatActionType"] = $data["ReanimatActionType"];
			}
			if (!empty($data["RA_DrugNames"])) {
				$filter .= " and ERA.ReanimDrugType_id = :RA_DrugNames ";
				$queryParams["RA_DrugNames"] = $data["RA_DrugNames"];
			}
			$filter .= ") ";
		}
		if (!empty($data["ReanimatLpu"])) {
			$filter .= " and Lpu2.Lpu_id = :ReanimatLpu ";
			$queryParams["ReanimatLpu"] = $data["ReanimatLpu"];
		}
	}

	public static function selectParams_ReabRegistry(&$data, &$filter, &$queryParams)
	{
		if ($data["LpuAttachType_id"] > 0) {
			$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
			$queryParams["LpuAttachType_id"] = $data["LpuAttachType_id"];
		}
		$insID = "";
		if ($data["pmUser_insID"] > 0) {
			$queryParams["pmUser_insID"] = $data["pmUser_insID"];
			$insID .= " and gg.pmUser_insID = :pmUser_insID ";
			$data["pmUser_insID"] = 0;
		}
		if (isset($data["InsDate"])) {
			$queryParams["InsDate"] = $data["InsDate"];
			$insID .= " and gg.ReabEvent_insDT::date = :InsDate::date";
			$data["InsDate"] = null;
		}
		if (isset($data["InsDate_Range"][0]) && isset($data["InsDate_Range"][1])) {
			$queryParams["InsDate_Range_0"] = $data["InsDate_Range"][0];
			$queryParams["InsDate_Range_1"] = $data["InsDate_Range"][1];
			$insID .= " and gg.ReabEvent_insDT::date between :InsDate_Range_0 and :InsDate_Range_1 ";
			$data["InsDate_Range"] = null;
		};
		if ($data["pmUser_updID"] > 0) {
			$queryParams["pmUser_updID"] = $data["pmUser_updID"];
			$insID .= " and gg.pmUser_updID = :pmUser_updID ";
			$data["pmUser_updID"] = 0;
		}
		if (isset($data["UpdDate"])) {
			$queryParams["UpdDate"] = $data["UpdDate"];
			$insID .= " and gg.ReabEvent_updDT::date = :UpdDate::date";
			$data["UpdDate"] = null;
		}
		if (isset($data["UpdDate_Range"][0]) && isset($data["UpdDate_Range"][1])) {
			$queryParams["UpdDate_Range_0"] = $data["UpdDate_Range"][0];
			$queryParams["UpdDate_Range_1"] = $data["UpdDate_Range"][1];
			$insID .= " and gg.ReabEvent_updDT::date between :UpdDate_Range_0 and :UpdDate_Range_1 ";
			$data["UpdDate_Range"] = null;
		};
		if (empty($data["DirectType_id"]) || $data["DirectType_id"] == "") {
			$filter .= "and exists (select 1 from r2.ReabEvent gg where gg.Person_id = PR.pPerson_id ";
			if (isset($data["PersonRegisterType_id"]) && $data["PersonRegisterType_id"] == 2) {
				$filter .= " and gg.ReabOutCause_id is null ";
			}
			if (isset($data["PersonRegisterType_id"]) && $data["PersonRegisterType_id"] == 3) {
				$filter .= " and gg.ReabOutCause_id is not null ";
			}
			if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
				$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
				$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
				$filter .= " and gg.ReabEvent_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
			};
			if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
				$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
				$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
				$filter .= " and gg.ReabEvent_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
			};
			$filter .= $insID . ")";
		} else {
			$reabfilter = "where gg.Person_id = PR.pPerson_id ";
			$filter .= "and exists (select 1 from r2.ReabEvent gg";
			if (!empty($data["Reabquest_yn"]) && $data["Reabquest_yn"] == 1) {
				$filter .= ", r2.ReabQuestion gg1 ";
				$reabfilter .= " and gg1.ReabEvent_id = gg.ReabEvent_id ";
			}
			if (!empty($data["ReabScale_yn"]) && $data["ReabScale_yn"] == 1) {
				$filter .= ", r2.ReabScaleCondit gg2 ";
				$reabfilter .= " and gg2.ReabEvent_id = gg.ReabEvent_id ";
			}
			$queryParams["DirectType_id"] = $data["DirectType_id"];
			$filter .= $reabfilter . " and gg.ReabDirectType_id = :DirectType_id ";
			if (!empty($data["StageType_id"]) && $data["StageType_id"] != "") {
				$queryParams["StageType_id"] = $data["StageType_id"];
				$filter .= " and gg.ReabStageType_id = :StageType_id ";
			}
			if (isset($data["PersonRegisterType_id"]) && $data["PersonRegisterType_id"] == 2) {
				$filter .= " and gg.ReabOutCause_id is null ";
			}
			if (isset($data["PersonRegisterType_id"]) && $data["PersonRegisterType_id"] == 3) {
				$filter .= " and gg.ReabOutCause_id is not null ";
			}
			if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
				$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
				$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
				$filter .= " and gg.ReabEvent_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
			};
			if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
				$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
				$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
				$filter .= " and gg.ReabEvent_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
			};
			$filter .= $insID . ")";
		}
	}

	public static function selectParams_AdminVIPPerson(&$data, &$filter, &$queryParams)
	{
		if ($data["LpuAttachType_id"] > 0) {
			$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
			$queryParams["LpuAttachType_id"] = $data["LpuAttachType_id"];
		}
		if ($data["AdminVIPPersonLpu_id"] >= 0) {
			$filter .= " and Lpu1.Lpu_id = :AdminVIPPersonLpu_id ";
			$queryParams["AdminVIPPersonLpu_id"] = $data["AdminVIPPersonLpu_id"];
		};
		if (isset($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and R.VIPPerson_disDate is null ";
			};
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and R.VIPPerson_disDate is not null ";
			}
		} else {
			$filter .= " and 1<>1 ";
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and R.VIPPerson_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		};
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and R.VIPPerson_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		};
	}

	public static function selectParams_ZNOSuspectRegistry(&$data, &$filter, &$queryParams)
	{
		if ($data["LpuAttachType_id"] > 0) {
			$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
			$queryParams["LpuAttachType_id"] = $data["LpuAttachType_id"];
		}
		$filter .= " and ZNOReg.ZNOSuspectRegistry_deleted = 1 and ZNORout.ZNOSuspectRout_deleted = 1 ";
		if ($data["ZnoViewLpu_id"] >= 0) {
			$filter .= "and Lpu.Lpu_id = :ZnoViewLpu_id ";
			$queryParams["ZnoViewLpu_id"] = $data["ZnoViewLpu_id"];
		};
		if (isset($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and ps.Person_deadDT is null ";
			};
			if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and ps.Person_deadDT is not null ";
			}
		} else {
			$filter .= " and 1<>1 ";
		}
		if (isset($data["ObservType_id"])) {
			if ($data["ObservType_id"] == 1) {
				$filter .= " and (dd.Diag_Code ilike 'D0%' or dd.Diag_Code ilike 'C%') ";
			};
			if ($data["ObservType_id"] == 2) {
				$filter .= " and dd.Diag_Code >= 'D10' and dd.Diag_Code < 'D49' ";
			};
			if ($data["ObservType_id"] == 3) {
				$filter .= " and dd.Diag_Code not ilike 'D0%' and dd.Diag_Code not ilike 'C%' and (dd.Diag_Code < 'D10' or dd.Diag_Code >='D49') ";
			};
			if ($data["ObservType_id"] == 4) {
				$filter .= " and dd.Diag_id is null  ";
			};
		};
		if (isset($data["DeadlineZNO_id"])) {
			if ($data["DeadlineZNO_id"] == 1) {
				$filter .= " and ZNORout.ZNOSuspectRout_IsTerms = 2 ";
			};
			if ($data["DeadlineZNO_id"] == 2) {
				$filter .= " and ZNORout.ZNOSuspectRout_IsTerms = 1 ";
			};
		};
		if (isset($data["BiopsyRefZNO_id"])) {
			if ($data["BiopsyRefZNO_id"] == 1) {
				$filter .= " and ZNORout.ZNOSuspectRout_IsBiopsy is not null ";
			};
			if ($data["BiopsyRefZNO_id"] == 2) {
				$filter .= " and ZNORout.ZNOSuspectRout_IsBiopsy is null ";
			};
		};
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and ZNOReg.ZNOSuspectRegistry_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		};
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and ps.Person_deadDT is not null and ps.Person_deadDT::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		};
	}

	public static function selectParams_BskRegistry(&$data, &$filter, &$queryParams)
	{
		if ($data["LpuAttachType_id"] > 0) {
			$filter .= " and PC.LpuAttachType_id = :LpuAttachType_id";
			$queryParams["LpuAttachType_id"] = $data["LpuAttachType_id"];
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		};
		if (!empty($data["PersonRegister_disDate_Range"][0]) && !empty($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["MorbusType_id"]) && $data["MorbusType_id"] != "") {
			$filter .= " and PR.MorbusType_id =:MorbusType_id";
			$queryParams["MorbusType_id"] = $data["MorbusType_id"];
			if (isset($data["quest_id"]) && $data["quest_id"] != "") {
				$filter .= ($data["quest_id"] == 1)
					? " and BSKRegistry_setDate is not null"
					: " and BSKRegistry_setDate is null";
			}
			if (isset($data["pmUser_docupdID"]) && $data["pmUser_docupdID"] != "") {
				$filter .= "
					and R.BSKRegistry_id in (
						select T.BSKRegistry_id
						from (
							select ROW_NUMBER() OVER(PARTITION BY R.Person_id ORDER BY coalesce(R.BSKRegistry_insDT, R.BSKRegistry_updDT) DESC) num, * 
                            from dbo.BSKRegistry R
						) as T
						where T.num = 1
					)
				";
				$filter .= " and coalesce(R.pmUser_updID, R.pmUser_insID) = :pmUser_docupdID";
				$queryParams["pmUser_docupdID"] = $data["pmUser_docupdID"];
			}
		}
		if (!empty($data["PersonRegisterType_id"])) {
			switch ($data["PersonRegisterType_id"]) {
				case "2":
					$filter .= " and PR.PersonRegister_disDate is not null ";
					break;
				case "3":
					$filter .= " and PS.Person_deadDT is not null ";
					break;
			}
		}
	}

	public static function selectParams_ECORegistry(&$data, &$filter, &$queryParams)
	{
		if (isset($data["isRegion"]) && $data["isRegion"] == 1) {
			if (isset($data["EcoRegistryData_lpu_id"]) && $data["EcoRegistryData_lpu_id"] != "") {
				$filter .= " and PR.Lpu_iid =:PersonRegister_Lpu_iid";
				$queryParams["PersonRegister_Lpu_iid"] = $data["EcoRegistryData_lpu_id"];
			};
		} else {
			$filter .= "
				and (
					pr.lpu_iid = :PersonRegister_Lpu_iid or exists(
						select 1
						from dbo.v_ECORegistry ee
						where ee.lpu_id = :PersonRegister_Lpu_iid and ee.PersonRegister_id = pr.PersonRegister_id
					)
				)
			";
			$queryParams["PersonRegister_Lpu_iid"] = $data["EcoRegistryData_lpu_id"];
		}
		if (isset($data["EcoRegistryData_dateRange"][0]) && isset($data["EcoRegistryData_dateRange"][1])) {
			$queryParams["EcoRegistryData_dateRange_0"] = $data["EcoRegistryData_dateRange"][0];
			$queryParams["EcoRegistryData_dateRange_1"] = $data["EcoRegistryData_dateRange"][1];
			$filter .= " and ER.PersonRegisterEco_AddDate::date between :EcoRegistryData_dateRange_0 and :EcoRegistryData_dateRange_1 ";
		};
		if (isset($data["EcoRegistryData_vidOplod"]) && $data["EcoRegistryData_vidOplod"] != "") {
			$filter .= " and ER.EcoOplodType_id = :PersonRegister_VidOplod";
			$queryParams["PersonRegister_VidOplod"] = $data["EcoRegistryData_vidOplod"];
		}
		if (isset($data["EcoRegistryData_countMoveEmbroin"]) && $data["EcoRegistryData_countMoveEmbroin"] != "") {
			$filter .= " and ER.EmbrionCount_id = :PersonRegister_EmbrionCount";
			$queryParams["PersonRegister_EmbrionCount"] = $data["EcoRegistryData_countMoveEmbroin"];
		}
		if (isset($data["EcoRegistryData_ds1_from"]) && $data["EcoRegistryData_ds1_from"] != "") {
			$filter .= " and ER.ds_osn_code >= :PersonRegister_ds1_from";
			$queryParams["PersonRegister_ds1_from"] = $data["EcoRegistryData_ds1_from"];
		}
		if (isset($data["EcoRegistryData_ds1_to"]) && $data["EcoRegistryData_ds1_to"] != "") {
			$filter .= " and ER.ds_osn_code <= :PersonRegister_ds1_to";
			$queryParams["PersonRegister_ds1_to"] = $data["EcoRegistryData_ds1_to"];
		}
		if (isset($data["EcoPregnancyType_id"]) && $data["EcoPregnancyType_id"] != "") {
			$filter .= " and ER.EcoPregnancyType_id = :EcoPregnancyType_id";
			$queryParams["EcoPregnancyType_id"] = $data["EcoPregnancyType_id"];
		}
		if (isset($data["PayType_id"]) && $data["PayType_id"] != "") {
			$filter .= " and ER.PayType_id = :PayType_id";
			$queryParams["PayType_id"] = $data["PayType_id"];
		}
		if (isset($data["EcoRegistryData_genDiag"]) && $data["EcoRegistryData_genDiag"] != "") {
			$filter .= " and ER.PersonRegisterEco_IsGeneting = :PersonRegister_GenetigDiag";
			$queryParams["PersonRegister_GenetigDiag"] = $data["EcoRegistryData_genDiag"];
		}
		if (isset($data["EcoRegistryData_resEco"]) && $data["EcoRegistryData_resEco"] != "") {
			$filter .= " and ER.res_code = :PersonRegister_Result";
			$queryParams["PersonRegister_Result"] = $data["EcoRegistryData_resEco"];
		}
		if (isset($data["EcoRegistryData_noRes"]) && $data["EcoRegistryData_noRes"] == 1) {
			$filter .= " and ER.Result is null";
		}
		if (isset($data["MedPersonal_iid"])) {
			$filter .= " and ER.MedPersonal_id = :MedPersonal_iid";
			$queryParams["MedPersonal_iid"] = $data["MedPersonal_iid"];
		}
	}

	public static function selectParams_IPRARegistry(Search_model $callObject, &$data, &$filter, &$queryParams)
	{
		if (isset($data["PersonRegister_disDate_Range"][0]) && isset($data["PersonRegister_disDate_Range"][1])) {
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
			$filter .= " and PR.PersonRegister_disDate::date between :PersonRegister_disDate_Range_0 and :PersonRegister_disDate_Range_1 ";
		}
		if (isset($data["PersonRegister_setDate_Range"][0]) && isset($data["PersonRegister_setDate_Range"][1])) {
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
			$filter .= " and PR.PersonRegister_setDate::date between :PersonRegister_setDate_Range_0 and :PersonRegister_setDate_Range_1 ";
		}
		if (isset($data["IPRARegistry_EndDate_Range"][0]) && isset($data["IPRARegistry_EndDate_Range"][1])) {
			$queryParams["IPRARegistry_EndDate_Range_0"] = $data["IPRARegistry_EndDate_Range"][0];
			$queryParams["IPRARegistry_EndDate_Range_1"] = $data["IPRARegistry_EndDate_Range"][1];
			$filter .= " and IR.IPRARegistry_EndDate between :IPRARegistry_EndDate_Range_0::date and :IPRARegistry_EndDate_Range_1::date ";
		}
		if (isset($data["IPRARegistry_issueDate_Range"][0]) && isset($data["IPRARegistry_issueDate_Range"][1])) {
			$queryParams["IPRARegistry_issueDate_Range_0"] = $data["IPRARegistry_issueDate_Range"][0];
			$queryParams["IPRARegistry_issueDate_Range_1"] = $data["IPRARegistry_issueDate_Range"][1];
			$filter .= " and IR.IPRARegistry_issueDate between :IPRARegistry_issueDate_Range_0::date and :IPRARegistry_issueDate_Range_1::date ";
		}
		if (isset($data["IPRARegistryData_MedRehab_yn"]) && $data["IPRARegistryData_MedRehab_yn"] != "") {
			$filter .= " and IRD.IPRARegistryData_MedRehab =:IPRARegistryData_MedRehab_yn";
			$queryParams["IPRARegistryData_MedRehab_yn"] = $data["IPRARegistryData_MedRehab_yn"];
		}
		if (isset($data["IPRARegistryData_ReconstructSurg_yn"]) && $data["IPRARegistryData_ReconstructSurg_yn"] != "") {
			$filter .= " and IRD.IPRARegistryData_ReconstructSurg =:IPRARegistryData_ReconstructSurg_yn";
			$queryParams["IPRARegistryData_ReconstructSurg_yn"] = $data["IPRARegistryData_ReconstructSurg_yn"];
		}
		if (isset($data["IPRARegistryData_Orthotics_yn"]) && $data["IPRARegistryData_Orthotics_yn"] != "") {
			$filter .= " and IRD.IPRARegistryData_Orthotics =:IPRARegistryData_Orthotics_yn";
			$queryParams["IPRARegistryData_Orthotics_yn"] = $data["IPRARegistryData_Orthotics_yn"];
		}
		$filter .= " and exists (select IPRARegistryData_id from dbo.v_IPRARegistryData where IPRARegistry_id = IR.IPRARegistry_id limit 1)";
		if (isset($data["PersonRegister_number_IPRA"]) && $data["PersonRegister_number_IPRA"] != "") {
			$filter .= " and IR.IPRARegistry_Number =:PersonRegister_number_IPRA";
			$queryParams["PersonRegister_number_IPRA"] = $data["PersonRegister_number_IPRA"];
		}
		if (isset($data["PersonRegister_buro_MCE"]) && $data["PersonRegister_buro_MCE"] != "") {
			$filter .= " and IR.IPRARegistry_FGUMCEnumber =:PersonRegister_buro_MCE";
			$queryParams["PersonRegister_buro_MCE"] = $data["PersonRegister_buro_MCE"];
		}
		if (isset($data["PersonRegister_confirm_IPRA"]) && $data["PersonRegister_confirm_IPRA"] != "") {
			$filter .= " and IR.IPRARegistry_confirm =:PersonRegister_confirm_IPRA";
			$queryParams["PersonRegister_confirm_IPRA"] = $data["PersonRegister_confirm_IPRA"];
		}
		if (isset($data["IPRARegistry_DirectionLPU_id"]) && $data["IPRARegistry_DirectionLPU_id"] != "") {
			$filter .= " and IR.IPRARegistry_DirectionLPU_id =:IPRARegistry_DirectionLPU_id";
			$queryParams["IPRARegistry_DirectionLPU_id"] = $data["IPRARegistry_DirectionLPU_id"];
		}
		if (isset($data["LPU_id"]) && $data["LPU_id"] != "" && $callObject->getRegionNick() == "ufa") {
			$filter .= " and IR.Lpu_id =:LPU_id";
			$queryParams["LPU_id"] = $data["LPU_id"];
		}
		if ($callObject->getRegionNick() != "ufa" && isset($data["PersonRegister_FilterBy"]) && $data["PersonRegister_FilterBy"] != "" && !isOuzSpec()) {
			if ($data["PersonRegister_FilterBy"] == "Attachment") {
				if(isset($data["AttachLpu_id"]) && $data["AttachLpu_id"] !== 0) {
					$filter .= " and PC.Lpu_id = :AttachLpu_id and pc.LpuAttachType_id = 1";
				} else {
					$filter .= " and PC.Lpu_id = :Lpu_id AND pc.LpuAttachType_id = 1";
				}
			} elseif ($data["PersonRegister_FilterBy"] == "DirectionToMse") {
				$filter .= " and IR.IPRARegistry_DirectionLPU_id = :Lpu_id and (pc.LpuAttachType_id != 1 or pc.LpuAttachType_id is null)";
			}
		}
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			} elseif ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		$filter .= " and PR.PersonRegister_insDT is not null ";
		if ($callObject->getRegionNick() != "ufa" && !isOuzSpec()) {
			if (empty($data["IPRARegistryEdit"])) {
				$filter .= "
					and (
						(PC.PersonCard_id is not null and PC.Lpu_id = :Lpu_id) or (PC.PersonCard_id is null and IR.IPRARegistry_DirectionLPU_id = :Lpu_id)
					)
                ";
			}
			if (!empty($data["IsMeasuresComplete"])) {
				$filter .= "and IsMeasuresComplete.Value = :IsMeasuresComplete";
				$queryParams["IsMeasuresComplete"] = $data["IsMeasuresComplete"];
			}
		}
		if (!empty($data["pmUser_confirmID"])) {
			$queryParams["pmUser_confirmID"] = $data["pmUser_confirmID"];
			$filter .= " and IR.pmUser_confirmID=:pmUser_confirmID ";
		}
	}

	public static function selectParams_GeriatricsRegistry(&$data, &$filter, &$queryParams)
	{
		$filter .= " and PR.PersonRegisterType_id = 67 ";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			} else if ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		if (isset($data["PersonRegister_setDate_Range"][0])) {
			$filter .= " and PR.PersonRegister_setDate >= :PersonRegister_setDate_Range_0::timestamp ";
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
		}
		if (isset($data["PersonRegister_setDate_Range"][1])) {
			$filter .= " and PR.PersonRegister_setDate <= :PersonRegister_setDate_Range_1::timestamp ";
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
		}
		if (isset($data["PersonRegister_disDate_Range"][0])) {
			$filter .= " and PR.PersonRegister_disDate >= :PersonRegister_disDate_Range_0::timestamp ";
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
		}
		if (isset($data["PersonRegister_disDate_Range"][1])) {
			$filter .= " and PR.PersonRegister_disDate <= :PersonRegister_disDate_Range_1::timestamp ";
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
		}
		if (!empty($data["AgeNotHindrance_id"])) {
			$filter .= " and MG.AgeNotHindrance_id = :AgeNotHindrance_id ";
			$queryParams["AgeNotHindrance_id"] = $data["AgeNotHindrance_id"];
		}
		if (!empty($data["MorbusGeriatrics_IsKGO"])) {
			$filter .= " and MG.MorbusGeriatrics_IsKGO = :MorbusGeriatrics_IsKGO ";
			$queryParams["MorbusGeriatrics_IsKGO"] = $data["MorbusGeriatrics_IsKGO"];
		}
		if (!empty($data["MorbusGeriatrics_IsWheelChair"])) {
			$filter .= " and MG.MorbusGeriatrics_IsWheelChair = :MorbusGeriatrics_IsWheelChair ";
			$queryParams["MorbusGeriatrics_IsWheelChair"] = $data["MorbusGeriatrics_IsWheelChair"];
		}
		if (!empty($data["MorbusGeriatrics_IsFallDown"])) {
			$filter .= " and MG.MorbusGeriatrics_IsFallDown = :MorbusGeriatrics_IsFallDown ";
			$queryParams["MorbusGeriatrics_IsFallDown"] = $data["MorbusGeriatrics_IsFallDown"];
		}
		if (!empty($data["MorbusGeriatrics_IsWeightDecrease"])) {
			$filter .= " and MG.MorbusGeriatrics_IsWeightDecrease = :MorbusGeriatrics_IsWeightDecrease ";
			$queryParams["MorbusGeriatrics_IsWeightDecrease"] = $data["MorbusGeriatrics_IsWeightDecrease"];
		}
		if (!empty($data["MorbusGeriatrics_IsCapacityDecrease"])) {
			$filter .= " and MG.MorbusGeriatrics_IsCapacityDecrease = :MorbusGeriatrics_IsCapacityDecrease ";
			$queryParams["MorbusGeriatrics_IsCapacityDecrease"] = $data["MorbusGeriatrics_IsCapacityDecrease"];
		}
		if (!empty($data["MorbusGeriatrics_IsCognitiveDefect"])) {
			$filter .= " and MG.MorbusGeriatrics_IsCognitiveDefect = :MorbusGeriatrics_IsCognitiveDefect ";
			$queryParams["MorbusGeriatrics_IsCognitiveDefect"] = $data["MorbusGeriatrics_IsCognitiveDefect"];
		}
		if (!empty($data["MorbusGeriatrics_IsMelancholia"])) {
			$filter .= " and MG.MorbusGeriatrics_IsMelancholia = :MorbusGeriatrics_IsMelancholia ";
			$queryParams["MorbusGeriatrics_IsMelancholia"] = $data["MorbusGeriatrics_IsMelancholia"];
		}
		if (!empty($data["MorbusGeriatrics_IsEnuresis"])) {
			$filter .= " and MG.MorbusGeriatrics_IsEnuresis = :MorbusGeriatrics_IsEnuresis ";
			$queryParams["MorbusGeriatrics_IsEnuresis"] = $data["MorbusGeriatrics_IsEnuresis"];
		}
		if (!empty($data["MorbusGeriatrics_IsPolyPragmasy"])) {
			$filter .= " and MG.MorbusGeriatrics_IsPolyPragmasy = :MorbusGeriatrics_IsPolyPragmasy ";
			$queryParams["MorbusGeriatrics_IsPolyPragmasy"] = $data["MorbusGeriatrics_IsPolyPragmasy"];
		}
	}

	public static function selectParams_OnkoRegistry(&$data, &$filter, &$queryParams)
	{
		$filter .= " and PR.PersonRegisterType_id = 3 ";
		if (!empty($data["PersonRegisterType_id"])) {
			if ($data["PersonRegisterType_id"] == 2) {
				$filter .= " and PR.PersonRegister_disDate is null ";
			} elseif ($data["PersonRegisterType_id"] == 3) {
				$filter .= " and PR.PersonRegister_disDate is not null ";
			}
		}
		switch ($data["PersonRegisterRecordType_id"]) {
			case 1: // все
				break;
			case 2: // все, состоящие на учете
				$filter .= " and MOB.OnkoRegType_id is not null and MOB.OnkoRegOutType_id is null ";
				break;
			case 3: // все выехавшие
				$filter .= " and MOB.OnkoRegOutType_id = 1 ";
				break;
			case 4: // все, у которых диагноз не подтвердился
				$filter .= " and MOB.OnkoRegOutType_id = 2 ";
				break;
			case 5: // все, «снятые по базалиоме»
				$filter .= " and MOB.OnkoRegOutType_id = 3 ";
				break;
			case 6: // все умершие
				$filter .= " and MOB.OnkoRegOutType_id in (4,5,6) ";
				break;
		}
		if (isset($data["PersonRegister_setDate_Range"][0])) {
			$filter .= " and PR.PersonRegister_setDate >= :PersonRegister_setDate_Range_0::timestamp ";
			$queryParams["PersonRegister_setDate_Range_0"] = $data["PersonRegister_setDate_Range"][0];
		}
		if (isset($data["PersonRegister_setDate_Range"][1])) {
			$filter .= " and PR.PersonRegister_setDate <= :PersonRegister_setDate_Range_1::timestamp ";
			$queryParams["PersonRegister_setDate_Range_1"] = $data["PersonRegister_setDate_Range"][1];
		}
		if (isset($data["PersonRegister_disDate_Range"][0])) {
			$filter .= " and PR.PersonRegister_disDate >= :PersonRegister_disDate_Range_0::timestamp ";
			$queryParams["PersonRegister_disDate_Range_0"] = $data["PersonRegister_disDate_Range"][0];
		}
		if (isset($data["PersonRegister_disDate_Range"][1])) {
			$filter .= " and PR.PersonRegister_disDate <= :PersonRegister_disDate_Range_1::timestamp ";
			$queryParams["PersonRegister_disDate_Range_1"] = $data["PersonRegister_disDate_Range"][1];
		}
		if (isset($data['PersonRegister_onkoDeathDate_Range'][0])){
			$filter .= " and MOB.MorbusOnkoBase_deadDT >= :PersonRegister_onkoDeathDate_Range_0::timestamp ";
			$queryParams['PersonRegister_onkoDeathDate_Range_0'] = $data['PersonRegister_onkoDeathDate_Range'][0];
		}
		if (isset($data['PersonRegister_onkoDeathDate_Range'][1])){
			$filter .= " and MOB.MorbusOnkoBase_deadDT <= :PersonRegister_onkoDeathDate_Range_1::timestamp ";
			$queryParams['PersonRegister_onkoDeathDate_Range_1'] = $data['PersonRegister_onkoDeathDate_Range'][1];
		}
		if(isset($data['PersonRegister_onkoDiagDeath'])){
			$filter.="and DiagDeath.diag_code=:PersonRegister_onkoDiagDeath";
			$queryParams['PersonRegister_onkoDiagDeath'] = $data['PersonRegister_onkoDiagDeath'];
		}
		if (isset($data["MorbusOnkoBase_NumCard"])) {
			$filter .= "and MOB.MorbusOnkoBase_NumCard ilike :MorbusOnkoBase_NumCard ";
			$data["MorbusOnkoBase_NumCard"] = str_replace("%", "[%]", $data["MorbusOnkoBase_NumCard"]);
			$data["MorbusOnkoBase_NumCard"] = str_replace("_", "[_]", $data["MorbusOnkoBase_NumCard"]);
			if (mb_strlen($data["MorbusOnkoBase_NumCard"] < 10)) {
				$data["MorbusOnkoBase_NumCard"] .= "%";
			}
			$queryParams["MorbusOnkoBase_NumCard"] = $data["MorbusOnkoBase_NumCard"];
		}
		if (!empty($data["PersonRegister_evnSection_Range"][0]) || !empty($data["PersonRegister_evnSection_Range"][1])) {
			if (!empty($data["PersonRegister_evnSection_Range"][0]) && !empty($data["PersonRegister_evnSection_Range"][1])) {
				$filter .= " and ES.EvnSection_setDate >= :PersonRegister_evnSection_setDate::timestamp and ES.EvnSection_setDate <= :PersonRegister_evnSection_disDate::timestamp";
				$queryParams["PersonRegister_evnSection_setDate"] = $data["PersonRegister_evnSection_Range"][0];
				$queryParams["PersonRegister_evnSection_disDate"] = $data["PersonRegister_evnSection_Range"][1];
			} else if (!empty($data["PersonRegister_evnSection_Range"][0])) {
				$filter .= " and (ES.EvnSection_setDate >= :PersonRegister_evnSection_setDate::timestamp or ES.EvnSection_disDate >= :PersonRegister_evnSection_setDate::timestamp or ES.EvnSection_disDate is null)";
				$queryParams["PersonRegister_evnSection_setDate"] = $data["PersonRegister_evnSection_Range"][0];
			} else if (!empty($data["PersonRegister_evnSection_Range"][1])) {
				$filter .= " and ES.EvnSection_setDate <= :PersonRegister_evnSection_disDate::timestamp";
				$queryParams["PersonRegister_evnSection_disDate"] = $data["PersonRegister_evnSection_Range"][1];
			}
			$filter .= " and ((ESD.Diag_Code >= 'C00' and ESD.Diag_Code <= 'C97') or (ESD.Diag_Code >= 'D00' and ESD.Diag_Code <= 'D09')) ";
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (!empty($data["MorbusOnko_setDiagDT_Range"][0])) {
			$filter .= " and MO.MorbusOnko_setDiagDT >= :MorbusOnko_setDiagDT_Range_0::timestamp ";
			$queryParams["MorbusOnko_setDiagDT_Range_0"] = $data["MorbusOnko_setDiagDT_Range"][0];
		}
		if (!empty($data["MorbusOnko_setDiagDT_Range"][1])) {
			$filter .= " and MO.MorbusOnko_setDiagDT <= :MorbusOnko_setDiagDT_Range_1::timestamp ";
			$queryParams["MorbusOnko_setDiagDT_Range_1"] = $data["MorbusOnko_setDiagDT_Range"][1];
		}
		if (!empty($data["MorbusOnko_IsMainTumor"])) {
			$filter .= " and coalesce(MO.MorbusOnko_IsMainTumor, 1) = :MorbusOnko_IsMainTumor ";
			$queryParams["MorbusOnko_IsMainTumor"] = $data["MorbusOnko_IsMainTumor"];
		}
		if (!empty($data["Diag_mid"])) {
			$filter .= " and MO.OnkoDiag_mid = :Diag_mid ";
			$queryParams["Diag_mid"] = $data["Diag_mid"];
		}
		if (!empty($data["TumorStage_id"])) {
			$filter .= " and MO.TumorStage_id = :TumorStage_id ";
			$queryParams["TumorStage_id"] = $data["TumorStage_id"];
		}
		if (!empty($data["TumorPrimaryTreatType_id"])) {
			$filter .= " and MO.TumorPrimaryTreatType_id = :TumorPrimaryTreatType_id ";
			$queryParams["TumorPrimaryTreatType_id"] = $data["TumorPrimaryTreatType_id"];
		}
		if (!empty($data["TumorRadicalTreatIncomplType_id"])) {
			$filter .= " and MO.TumorRadicalTreatIncomplType_id = :TumorRadicalTreatIncomplType_id ";
			$queryParams["TumorRadicalTreatIncomplType_id"] = $data["TumorRadicalTreatIncomplType_id"];
		}
		if (isset($data["MorbusOnkoSpecTreat_begDate_Range"][0]) && isset($data["MorbusOnkoSpecTreat_begDate_Range"][1])) {
			$filter .= " and MO.MorbusOnko_specSetDT between :MorbusOnkoSpecTreat_begDate_Range_0::timestamp and :MorbusOnkoSpecTreat_begDate_Range_1::timestamp ";
			$queryParams["MorbusOnkoSpecTreat_begDate_Range_0"] = $data["MorbusOnkoSpecTreat_begDate_Range"][0];
			$queryParams["MorbusOnkoSpecTreat_begDate_Range_1"] = $data["MorbusOnkoSpecTreat_begDate_Range"][1];
		}
		if (isset($data["MorbusOnkoSpecTreat_endDate_Range"][0]) && isset($data["MorbusOnkoSpecTreat_endDate_Range"][1])) {
			$filter .= " and MO.MorbusOnko_specDisDT between :MorbusOnkoSpecTreat_endDate_Range_0::timestamp and :MorbusOnkoSpecTreat_endDate_Range_1::timestamp ";
			$queryParams["MorbusOnkoSpecTreat_endDate_Range_0"] = $data["MorbusOnkoSpecTreat_endDate_Range"][0];
			$queryParams["MorbusOnkoSpecTreat_endDate_Range_1"] = $data["MorbusOnkoSpecTreat_endDate_Range"][1];
		}
		if (!empty($data["OnkoTumorStatusType_id"])) {
			$filter .= " and MO.OnkoTumorStatusType_id = :OnkoTumorStatusType_id ";
			$queryParams["OnkoTumorStatusType_id"] = $data["OnkoTumorStatusType_id"];
		}
		if (!empty($data["OnkoPersonStateType_id"])) {
			$filter .= " and MOBPS.OnkoPersonStateType_id = :OnkoPersonStateType_id ";
			$queryParams["OnkoPersonStateType_id"] = $data["OnkoPersonStateType_id"];
		}
		if (!empty($data["OnkoStatusYearEndType_id"])) {
			$filter .= " and MOB.OnkoStatusYearEndType_id = :OnkoStatusYearEndType_id ";
			$queryParams["OnkoStatusYearEndType_id"] = $data["OnkoStatusYearEndType_id"];
		}
	}

	public static function selectParams_EvnOnkoNotify(&$data, &$filter, &$queryParams)
	{
		if (empty($data["isOnlyTheir"]) && (empty($data["session"]["groups"]) || strpos($data["session"]["groups"], "OnkoRegistry") < 0)) {
			$filter .= " and (EON.pmUser_insID = :pmUser_id or EONN.pmUser_insID = :pmUser_id) ";
			$queryParams["pmUser_id"] = $data["pmUser_id"];
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["OnkoDiag_Code_From"])) {
			$filter .= " and OnkoDiag.OnkoDiag_id >= :OnkoDiag_Code_From ";
			$queryParams["OnkoDiag_Code_From"] = $data["OnkoDiag_Code_From"];
		}
		if (isset($data["OnkoDiag_Code_To"])) {
			$filter .= " and OnkoDiag.OnkoDiag_id <= :OnkoDiag_Code_To ";
			$queryParams["OnkoDiag_Code_To"] = $data["OnkoDiag_Code_To"];
		}
		if (isset($data["Lpu_sid"])) {
			$filter .= " and EON.Lpu_sid = :Lpu_sid ";
			$queryParams["Lpu_sid"] = $data["Lpu_sid"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and EON.EvnOnkoNotify_setDT >= :EvnOnkoNotify_setDT_Range_0::timestamp ";
			$queryParams["EvnOnkoNotify_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and EON.EvnOnkoNotify_setDT <= :EvnOnkoNotify_setDT_Range_1::timestamp ";
			$queryParams["EvnOnkoNotify_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["isNeglected"])) {
			if ($data["isNeglected"] == 1) {
				$filter .= " and EONN.EvnOnkoNotifyNeglected_id IS NULL ";
			} elseif ($data["isNeglected"] == 2) {
				$filter .= " and EONN.EvnOnkoNotifyNeglected_id IS NOT NULL ";
			}
		}
		if (isset($data["TumorStage_id"])) {
			$filter .= " and MO.TumorStage_id = :TumorStage_id ";
			$queryParams["TumorStage_id"] = $data["TumorStage_id"];
		}
		if (isset($data["TumorCircumIdentType_id"])) {
			$filter .= " and MO.TumorCircumIdentType_id = :TumorCircumIdentType_id ";
			$queryParams["TumorCircumIdentType_id"] = $data["TumorCircumIdentType_id"];
		}
		if (!empty($data["EvnNotifyStatus_id"])) {
			switch ($data["EvnNotifyStatus_id"]) {
				case 1:
					$filter .= "
						and EON.EvnOnkoNotify_niDate is null
						and PR.PersonRegister_id is null
					";
					break;
				case 2:
					$filter .= "
						and PR.PersonRegister_id is not null
					";
					break;
				case 3:
					$filter .= "
						and EON.EvnOnkoNotify_niDate is not null
						and EON.PersonRegisterFailIncludeCause_id = 1
					";
					break;
				case 4:
					$filter .= "
						and EON.EvnOnkoNotify_niDate is not null
						and EON.PersonRegisterFailIncludeCause_id = 2
					";
					break;
			}
		}
		if (!empty($data["IsIncluded"])) {
			$filter .= ($data["IsIncluded"] == 2)
				? " and EON.EvnOnkoNotify_niDate is null and PR.PersonRegister_setDate is not null "
				: " and EON.EvnOnkoNotify_niDate is not null ";
		}
	}

	public static function selectParams_EvnNotifyHepatitis(&$data, &$filter, &$queryParams)
	{
		if (empty($data["AttachLpu_id"]) && (empty($data["session"]["groups"]) || strpos($data["session"]["groups"], "HepatitisRegistry") < 0)) {
			$filter .= " and PC.Lpu_id  = :AttachLpu_id ";
			$queryParams["AttachLpu_id"] = $data["session"]["lpu_id"];
		}
		if (isset($data["Diag_Code_From"])) {
			$filter .= " and Diag.Diag_Code >= :Diag_Code_From ";
			$queryParams["Diag_Code_From"] = $data["Diag_Code_From"];
		}
		if (isset($data["Diag_Code_To"])) {
			$filter .= " and Diag.Diag_Code <= :Diag_Code_To ";
			$queryParams["Diag_Code_To"] = $data["Diag_Code_To"];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][0])) {
			$filter .= " and ENH.EvnNotifyHepatitis_setDT >= :EvnNotifyHepatitis_setDT_Range_0::timestamp ";
			$queryParams["EvnNotifyHepatitis_setDT_Range_0"] = $data["EvnNotifyBase_setDT_Range"][0];
		}
		if (isset($data["EvnNotifyBase_setDT_Range"][1])) {
			$filter .= " and ENH.EvnNotifyHepatitis_setDT <= :EvnNotifyHepatitis_setDT_Range_1::timestamp ";
			$queryParams["EvnNotifyHepatitis_setDT_Range_1"] = $data["EvnNotifyBase_setDT_Range"][1];
		}
		if (isset($data["isNotifyProcessed"])) {
			if ($data["isNotifyProcessed"] == 1) {
				$filter .= "
					and ENH.EvnNotifyHepatitis_niDate is null
					and PR.PersonRegister_id is null
				";
			} elseif ($data["isNotifyProcessed"] == 2) {
				$filter .= "
					and (ENH.EvnNotifyHepatitis_niDate is not null or PR.PersonRegister_id is not null)
				";
			}
		}
	}
	//selectParams_EvnVizitPL
}
