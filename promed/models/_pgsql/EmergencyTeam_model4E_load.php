<?php

class EmergencyTeam_model4E_load
{
	/**
	 * Возвращает данные по оперативной обстановке бригад СМП для арма ЦМК
	 * Для списка подчиненных подстанций СМП
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadCmpCallCardsARMCenterDisaster(EmergencyTeam_model4E $callObject, $data)
	{
		$params = [];
		$where = [];
		$lpuTable = "LB";
		//вызовы нмп
		if (!empty($data["isNmp"])) {
			$select = "
				nmpLpu.Lpu_Nick as \"NmpLpu_Nick\",
				nmpLpu.Lpu_id as \"NmpLpu_id\",
				rtrim(ToDT.PMUser_Name)||' '||to_char(ToDT.ToDT, '{$callObject->dateTimeForm104}')||' '||to_char(ToDT.ToDT, '{$callObject->dateTimeForm108}') as \"PPDUser_Name\",
				case when (CCCST.CmpCallCardStatusType_Code = 2) then
					case when EPL.EvnPL_setDT is null
						then to_char(CCC.CmpCallCard_updDT, '{$callObject->dateTimeForm120}')
						else to_char(EPL.EvnPL_setDT, '{$callObject->dateTimeForm120}')
					end
				end as \"CallAcceptanceDT\"
			";
			//принятые и переданные. первичные и повторы и консультация старшего врача
			$join = "
				left join v_Lpu nmpLpu on nmpLpu.Lpu_id = CCC.Lpu_ppdid
				left join lateral (
					select *
					from v_EvnPL
					where CmpCallCard_id = CCC.CmpCallCard_id
					limit 1
				) as EPL on true
				left join lateral (
					select
						CmpCallCardStatus_insDT as ToDT,
						PU.PMUser_Name
					from
						v_CmpCallCardStatus
						left join v_PmUser PU on PU.PMUser_id = pmUser_insID
					where CmpCallCardStatusType_id = 2
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ToDT on true
			";
			$where[] = "CCT.CmpCallType_Code in (1, 2)";
			$where[] = "CCCST.CmpCallCardStatusType_Code in (1, 2, 10)";
			$where[] = "nmpLpu.Lpu_id is not null";
			$lpuTable = "CCC";
		} else {
			//вызовы СМП только первичные вызовы
			$select = "
				lpuHid.Lpu_Nick as \"LpuHid_Nick\",
				CCCD.Duplicate_Count as \"Duplicate_Count\"
			";
			$join = "
				left join v_Lpu lpuHid on lpuHid.Lpu_id = CCC.Lpu_hid
				left join lateral (
					select count(CCCDouble.CmpCallCard_id) as Duplicate_Count
					from
						v_CmpCallCard CCCDouble
						left join v_CmpCallCardStatusType CCCSTDouble on CCCSTDouble.CmpCallCardStatusType_id = CCCDouble.CmpCallCardStatusType_id
					where CCCDouble.CmpCallCard_rid = CCC.CmpCallCard_id
					  and CCCSTDouble.CmpCallCardStatusType_Code = 9
					  and coalesce(CCCDouble.CmpCallCard_IsActiveCall, 1) != 2
				) as CCCD on true
			";
			$where[] = "CCT.CmpCallType_Code in (1, 4, 9)";
			$where[] = "CCCST.CmpCallCardStatusType_Code in (1, 2, 7, 8, 10)";
		}
		if (!empty ($data["Lpu_ids"])) {
			$where[] = $lpuTable . ".Lpu_id in ({$data["Lpu_ids"]})";
		} else {
			$Lpu_ids = $callObject->getSelectedLpuId();
			if (!$Lpu_ids) {
				return false;
			}
			$Lpu_idsString = implode(",", $Lpu_ids);
			$where[] = $lpuTable . ".Lpu_id in ({$Lpu_idsString})";
		}
		//Скрываем вызовы принятые в ППД
		$where[] = "coalesce(CCC.CmpCallCard_IsReceivedInPPD, 1) != 2";
		//Временно только открытые карты
		$where[] = "coalesce(CCC.CmpCallCard_IsOpen, 1) = 2";
		if (!empty($data["begDate"]) && !empty($data["endDate"])) {
			$where[] = "CCC.CmpCallCard_prmDT BETWEEN :begDate AND :endDate";
			$begDate = date_create($data["begDate"]);
			$endDate = date_create($data["endDate"]);
			$params["begDate"] = $begDate->format("Y-m-d") . " " . ((!empty($data["begTime"]) ? $data["begTime"] : " 00:00"));
			$params["endDate"] = $endDate->format("Y-m-d") . " " . ((!empty($data["endTime"]) ? $data["endTime"] : " 23:59"));
		} else {
			$where[] = "datediff('hh', CCC.CmpCallCard_prmDT, tzgetdate()) <= 24";
		}
		$whereString = (count($where) != 0)? "where ".implode(" and ", $where) : "";
		$sql = "
			select distinct
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDT\",
				rtrim(case when CR.CmpReason_id is not null then (CR.CmpReason_Code||' ') else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				CCC.Person_Age as \"Person_Age\",
				CCCST.CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\",
				CCCST.CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\",
				to_char((getdate() - CCCS.CmpCallCardStatus_insDT), '{$callObject->dateTimeForm120}') as \"statusTime\",
				to_char(CCCS.CmpCallCardStatus_insDT, '{$callObject->dateTimeForm120}') as \"CmpCallCardStatus_insDT\",
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				(case when ET.EmergencyTeam_Num is not null then ET.EmergencyTeam_Num||' ' else '' end)||
				(case when MP.Person_Fin is not null then MP.Person_Fin else '' end)
				as \"EmergencyTeam_Name\",
				ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				CCC.LpuBuilding_id as \"LpuBuilding_id\",
				CCC.CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
                CCC.CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
				case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as \"LpuBuildingName\",
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
					case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end||
					coalesce(lower(Town.KLSocr_Nick)||'. ', '')||Town.KLTown_Name else '' end||
					case when Street.KLStreet_FullName is not null then ', '||lower(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else '' end||
					case when SecondStreet.KLStreet_FullName is not null then ', '||lower(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else '' end||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
					case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end||
					case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end
				as \"Adress_Name\",
				coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||
				coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||
				coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null'  then '' else CCC.Person_SecName end, '')
				as \"Person_FIO\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				coalesce(CCC.MedService_id, 0) as \"MedService_id\",
				coalesce(MS.MedService_Nick, '-') as \"MedService_Nick\",
				case when coalesce(CCC112.CmpCallCard112_id, CCC112rid.CmpCallCard112_id, 1) = 1 then 1 else 2 end as \"is112\",
				CCC.CmpCallCard_isExtra as \"CmpCallCard_isExtra\",
				case when (coalesce(CCC.Person_Age, 0) = 0 and coalesce(CCC.Person_BirthDay, PS.Person_BirthDay , 0) !=0 ) then
					case when datediff('m', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), tzgetdate() ) > 12 then
						datediff('yy', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), tzgetdate())::varchar||' лет'
					else
						case when datediff('d', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), tzgetdate()) <= 30 then
							datediff('d', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), tzgetdate())::varchar||' дн. '
						else
							datediff('m', coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), tzgetdate())::varchar||' мес.'
						end
				   	end
				else
				 	case when coalesce(CCC.Person_Age, 0) = 0
				   	    then ''
						else CCC.Person_Age::varchar||' лет'
					end
				end as \"personAgeText\",
				CCC.Diag_uid as \"Diag_uid\",
				D.Diag_Code||'.'||D.Diag_Name as \"Diag_Name\",
				D.Diag_Code as \"Diag_Code\",
				CCC.Lpu_hid as \"Lpu_hid\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCT.CmpCallType_Name as \"CmpCallType_Name\",
				{$select}
			from
				v_CmpCallCard CCC
				left join v_CmpCallCard CCCrid on CCC.CmpCallCard_rid = CCCrid.CmpCallCard_id
				left join v_CmpCallCardStatusType CCCST on CCC.CmpCallCardStatusType_id = CCCST.CmpCallCardStatusType_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
				left join v_KLStreet SecondStreet on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				LEFT JOIN v_KLStreet SecondStreet on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_EmergencyTeam ET on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_MedPersonal as MP on MP.MedPersonal_id=ET.EmergencyTeam_HeadShift
				left join v_EmergencyTeamStatus AS ETS  on ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
				left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = CCC.LpuBuilding_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join v_Lpu L on L.Lpu_id = CCC.Lpu_id
				left join v_MedService MS on MS.MedService_id = CCC.MedService_id
				left join v_CmpCallCard112 CCC112 on CCC.CmpCallCard_id = CCC112.CmpCallCard_id
				left join v_CmpCallCard112 CCC112rid on CCCrid.CmpCallCard_id = CCC112rid.CmpCallCard_id
				left join v_Diag D on D.Diag_id = CCC.Diag_gid
				left join lateral (
				     select *
				     from v_CmpCallCardStatus
				     where CmpCallCard_id = CCC.CmpCallCard_id
				     order by CmpCallCardStatus_insDT desc
				     limit 1
				) as CCCS on true
				{$join}
			{$whereString}
		";
		if (isset($_GET["dbg"]) && $_GET["dbg"] == "1") {
			var_dump(getDebugSQL($sql, $params));
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadOutfitsARMCenterDisaster(EmergencyTeam_model4E $callObject, $data)
	{
		$queryParams = [];
		$filterArray = [];
		if (!empty($data["dateStart"]) && !empty($data["dateFinish"])) {
			$date_start = DateTime::createFromFormat("d.m.Y", $data["dateStart"]);
			$date_finish = DateTime::createFromFormat("d.m.Y", $data["dateFinish"]);
			$queryParams["dateStart"] = $date_start->format("Y-m-d");
			$queryParams["dateFinish"] = $date_finish->format("Y-m-d");
			$filterArray[] = "(ETD.EmergencyTeamDuty_DTStart::date >= :dateStart and ETD.EmergencyTeamDuty_DTStart::date <= :dateFinish)";
		} else {
			$filterArray[] = "(ETD.EmergencyTeamDuty_DTStart < tzgetdate() and ETD.EmergencyTeamDuty_DTFinish > tzgetdate())";
		}
		if (!empty($data["Lpu_ids"])) {
			$filterArray[] = "ET.Lpu_id in ({$data["Lpu_ids"]})";
		} else {
			$Lpu_ids = $callObject->getSelectedLpuId();
			if (!$Lpu_ids) {
				return false;
			}
			$Lpu_idsString = implode(",", $Lpu_ids);
			$filterArray[] = "ET.Lpu_id in ({$Lpu_idsString})";
		}
		if (!empty($data["dateFactStart"])) {
			$begDate = date_create($data["dateFactStart"]);
			$queryParams["dateFactStart"] = $begDate->format("Y-m-d") . " " . ((!empty($data["timeFactStart"]) ? $data["timeFactStart"] : "00:00"));
			$filterArray[] = "ETD.EmergencyTeamDuty_factToWorkDT >= :dateFactStart";
			$queryParams["is1900"] = "1900-01-01 00:00:00";
			if (!empty($data ["dateFactFinish"])) {
				$endDate = date_create($data["dateFactFinish"]);
				$queryParams["dateFactFinish"] = $endDate->format("Y-m-d") . " " . ((!empty($data["timeFactFinish"]) ? $data["timeFactFinish"] : " 23:59"));
				$filterArray[] = "ETD.EmergencyTeamDuty_factEndWorkDT <= :dateFactFinish";
			}
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				LB.LpuBuilding_Nick as \"LpuBuilding_Nick\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
				ET.Lpu_id as \"Lpu_id\",
				ET.CMPTabletPC_id as \"CMPTabletPC_id\",
				ET.EmergencyTeam_Phone as \"EmergencyTeam_Phone\",
				ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				coalesce(MPC.MedProductCard_BoardNumber, '')||' '||coalesce(MPCl.MedProductClass_Model, '')||' '||coalesce(AD.AccountingData_RegNumber, '') as \"MedProduct_Name\",
				ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
				to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
				to_char(ETD.EmergencyTeamDuty_factToWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factToWorkDT\",
				to_char(ETD.EmergencyTeamDuty_factEndWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factEndWorkDT\",
				L.Lpu_Nick as \"Lpu_Nick\",
				L.Lpu_id as \"Lpu_id\"
			from
				v_EmergencyTeam ET
				left join v_LpuBuilding LB on ET.LpuBuilding_id = LB.LpuBuilding_id
				left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				left join passport.v_MedProductCard MPC on MPC.MedProductCard_id = ET.MedProductCard_id
				left join passport.v_MedProductClass MPCl on MPCl.MedProductClass_id = MPC.MedProductClass_id
				left join passport.v_AccountingData AD on MPC.MedProductCard_id = AD.MedProductCard_id
				left join v_Lpu L on L.Lpu_id = ET.Lpu_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @return mixed|bool
	 */
	public static function loadIdSelectSmp()
	{
		$user = pmAuthUser::find($_SESSION["login"]);
		$settings = unserialize($user->settings);
		return (isset($settings["lpuBuildingsWorkAccess"]) && is_array($settings["lpuBuildingsWorkAccess"]) && !empty($settings["lpuBuildingsWorkAccess"][0]))
			? $settings["lpuBuildingsWorkAccess"]
			: false;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDispatchOperEnv(EmergencyTeam_model4E $callObject, $data)
	{
		$query = "
			select
				pmUser_id as \"pmUser_id\",
				pmUser_name as \"pmUser_name\",
				Lpu_Name as \"Lpu_Name\",
				'false' as \"online\"
			from
				v_pmUserCache u
				inner join v_Lpu as l on l.Lpu_id=u.Lpu_id
			where exists(
				select pucgl.pmUserCacheGroupLink_id
				from v_pmUserCacheGroupLink pucgl
				where pucgl.pmUserCacheGroup_id = (
						select pmUserCacheGroup_id
						from pmUserCacheGroup
						where pmUserCacheGroup_SysNick = 'SMPCallDispath'
						limit 1
					)
				  and pucgl.pmUserCache_id = u.PMUser_id
			  )
			  and exists(
			    select pucgl.pmUserCacheGroupLink_id
			    from v_pmUserCacheGroupLink pucgl
			    where pucgl.pmUserCacheGroup_id = (
			        	select pmUserCacheGroup_id
			        	from pmUserCacheGroup
			        	where pmUserCacheGroup_SysNick = 'SMPDispatchDirections'
			        )
			      and pucgl.pmUserCache_id = u.PMUser_id
			    )
			  and l.Lpu_id = :Lpu_id
			  and u.pmUser_deleted != 2				
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamDrugsPack(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			throw new Exception("Отсутствует обязательный параметр: идентификатор бригады");
		}
		$query = "
			select 				
				case when coalesce(Drug.Drug_Fas, 0) = 0
				    then RTRIM(coalesce(Drug.DrugTorg_Name, ''))||' '||coalesce(Drug.DrugForm_Name, '')||' '||coalesce(Drug.Drug_Dose,'')
					else RTRIM(coalesce(Drug.DrugTorg_Name, ''))||', '||coalesce(Drug.DrugForm_Name, '')||', '||coalesce(Drug.Drug_Dose, '')||', №'||Drug.Drug_Fas::varchar
				end as \"DrugTorg_Name\",
				Drug.Drug_id as \"Drug_id\",
				Drug.DrugPrepFas_id as \"DrugPrepFas_id\",
				Drug.Drug_Nomen as \"Drug_Nomen\",
				rtrim(coalesce(Drug.Drug_Nomen, '')) as \"Drug_Name\",
				rtrim(coalesce(Drug.DrugForm_Name, '')) as \"DrugForm_Name\",
				Drug.Drug_Dose as \"Drug_Dose\",
				Drug.Drug_Fas as \"Drug_Fas\",
				Drug.Drug_PackName as \"Drug_PackName\",
				Drug.Drug_Firm as \"Drug_Firm\",
				Drug.Drug_Ean as \"Drug_Ean\",
				Drug.Drug_RegNum as \"Drug_RegNum\",
				dMnn.DrugComplexMnn_RusName as \"DrugMnn\",
				EDP.EmergencyTeamDrugPack_Total as \"EmergencyTeamDrugPack_Total\"
			from 				
				rls.v_Drug Drug
				left join rls.v_DrugComplexMnn dMnn on dMnn.DrugComplexMnn_id=Drug.DrugComplexMnn_id 
				inner join EmergencyTeamDrugPack EDP on EDP.Drug_id = Drug.Drug_id and EDP.EmergencyTeam_id = :EmergencyTeam_id
		";
		/**@var CI_DB_result $result */
		$queryParams = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
		$result = $callObject->db->query($query, $queryParams);
		$arr = $result->result("array");
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $arr;
		$response["totalCount"] = count($arr);
		return $response;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeam(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			throw new Exception("Отсутствует обязательный параметр: идентификатор бригады");
		}
		$query = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
				ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
				ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
				ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
				ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				ET.EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\",
				ET.EmergencyTeam_HeadShiftWorkPlace as \"EmergencyTeam_HeadShiftWorkPlace\",
				ET.EmergencyTeam_HeadShift2 as \"EmergencyTeam_HeadShift2\",
				ET.EmergencyTeam_HeadShift2WorkPlace as \"EmergencyTeam_HeadShift2WorkPlace\",
				ET.EmergencyTeam_Driver as \"EmergencyTeam_Driver\",
				ET.EmergencyTeam_DriverWorkPlace as \"EmergencyTeam_DriverWorkPlace\",
				ET.EmergencyTeam_Driver2 as \"EmergencyTeam_Driver2\",
				ET.EmergencyTeam_Assistant1 as \"EmergencyTeam_Assistant1\",
				ET.EmergencyTeam_Assistant1WorkPlace as \"EmergencyTeam_Assistant1WorkPlace\",
				ET.EmergencyTeam_Assistant2 as \"EmergencyTeam_Assistant2\",
				ET.CMPTabletPC_id as \"CMPTabletPC_id\",
				TPC.CMPTabletPC_SIM as \"CMPTabletPC_SIM\",
				ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				ETD.EmergencyTeamDuty_ChangeComm as \"EmergencyTeamDuty_ChangeComm\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
				to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm104}') as \"EmergencyTeamDuty_DT\",
				to_char(ETD.EmergencyTeam_Head1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head1StartTime\",
				to_char(ETD.EmergencyTeam_Head1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head1FinishTime\",
				to_char(ETD.EmergencyTeam_Head2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head2StartTime\",
				to_char(ETD.EmergencyTeam_Head2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head2FinishTime\",
				to_char(ETD.EmergencyTeam_Assistant1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant1StartTime\",
				to_char(ETD.EmergencyTeam_Assistant1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant1FinishTime\",
				to_char(ETD.EmergencyTeam_Assistant2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant2StartTime\",
				to_char(ETD.EmergencyTeam_Assistant2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant2FinishTime\",
				to_char(ETD.EmergencyTeam_Driver1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver1StartTime\",
				to_char(ETD.EmergencyTeam_Driver1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver1FinishTime\",
				to_char(ETD.EmergencyTeam_Driver2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver2StartTime\",
				to_char(ETD.EmergencyTeam_Driver2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver2FinishTime\",
				ET.EmergencyTeam_DutyTime as \"EmergencyTeam_DutyTime\",
				ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				ET.EmergencyTeam_IsOnline as \"EmergencyTeam_IsOnline\",
				ET.EmergencyTeam_Phone as \"EmergencyTeam_Phone\",
				ET.Lpu_id as \"Lpu_id\",
				ET.EmergencyTeam_TemplateName as \"EmergencyTeam_TemplateName\",
				MPC.MedProductCard_Glonass as \"GeoserviceTransport_id\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				CCC.CmpReason_id as \"CmpReason_id\",
				coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||
					coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||
					coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null'  then '' else CCC.Person_SecName end, '')
				as \"Person_FIO\",
				coalesce(CCC.Person_Age, 0) as \"Person_Age\",
				case when SRGN.KLSubRgn_FullName is not null then ''||SRGN.KLSubRgn_FullName else 'г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end||
					case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end
				as \"Adress_Name\",
				CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
				MPC.MedProductCard_BoardNumber as \"MedProductCard_BoardNumber\",
				MPC.MedProductCard_id as \"MedProductCard_id\",
				coalesce(MPCl.MedProductClass_Model, '') as \"MedProductClass_Model\",
				coalesce(AD.AccountingData_RegNumber, '') as \"AccountingData_RegNumber\"
			from
				v_EmergencyTeam ET
				left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				left join v_EmergencyTeamStatus as ETS on ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
				left join v_CmpCallCard as CCC on CCC.CmpCallCard_id = (
				    select C2.CmpCallCard_id
				    from v_CmpCallCard as C2
				    where C2.EmergencyTeam_id = ET.EmergencyTeam_id
				    order by C2.CmpCallCard_updDT desc
				    limit 1
				)
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CMPTabletPC TPC on TPC.CMPTabletPC_id = ET.CMPTabletPC_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join passport.v_MedProductCard MPC on MPC.MedProductCard_id = ET.MedProductCard_id
				left join passport.v_MedProductClass MPCl on MPCl.MedProductClass_id = MPC.MedProductClass_id
				left join passport.v_AccountingData AD on MPC.MedProductCard_id = AD.MedProductCard_id
			where ET.EmergencyTeam_id = :EmergencyTeam_id
			limit 1
		";
		$queryParams = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool|CI_DB_result
	 * @throws Exception
	 */
	public static function loadEmergencyTeamList(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$lpuFilter = "";
		$filterArray = [];
		// соберем ИД базовых подстанций выбранных пользователем при входе в АРМ и подставим в фильтр по ним
		$user = pmAuthUser::find($_SESSION["login"]);
		$settings = unserialize($user->settings);
		$CurArmType = $data["session"]["CurArmType"];
		if (empty($data["viewAllMO"])) {
			if (isset($settings["lpuBuildingsWorkAccess"]) && is_array($settings["lpuBuildingsWorkAccess"])) {
				$lpuBuildingsWorkAccess = $settings["lpuBuildingsWorkAccess"];
				if (!empty($lpuBuildingsWorkAccess)) {
					$lpuBuildingsWorkAccessString = implode(",", $lpuBuildingsWorkAccess);
					$lpuFilter = "LB.LpuBuilding_id in ({$lpuBuildingsWorkAccessString})";
				} else {
					$lpuFilter = "1 = 0";
				}
			}
			if (!empty($data['LpuBuilding_id'])) {
				if (empty($data['filterLpuBuilding'])) {
					$filterArray[] = ($lpuFilter) ? $lpuFilter : "LB.LpuBuilding_id = :LpuBuilding_id";
				} else {
					$filterArray[] = "LB.LpuBuilding_id = :filterLpuBuilding";
				}
			}
		}
		if (!in_array($CurArmType, ["dispdirnmp"])) {
			$filterArray[] = "LB.Lpu_id = :Lpu_id";
		}
		if (in_array($CurArmType, ["dispnmp", "dispdirnmp"])) {
			$filterArray[] = "MPT.MedProductType_Code in (6) and LB.LpuBuildingType_id = 28";
		} else {
			$filterArray[] = "MPT.MedProductType_Code in (7, 8, 9, 10)";
		}
		if (!empty($data["display"])) {
			switch ($data["display"]) {
				case "all":
				{
					break;
				}
				case "opened":
				{
					$filterArray[] = "
						(
							(AD.AccountingData_setDate is null OR AD.AccountingData_setDate < dbo.tzGetDate()) and
							(AD.AccountingData_endDate is null OR AD.AccountingData_endDate > dbo.tzGetDate())
						)
					";
					break;
				}
				case "closed":
				{
					$filterArray[] = "
						not (
								(AD.AccountingData_setDate is null OR  AD.AccountingData_setDate < dbo.tzGetDate()) and
								(AD.AccountingData_endDate is null OR  AD.AccountingData_endDate > dbo.tzGetDate())
							)
					";
					break;
				}
			}
		}
		//фильтр авто, открытых в выбранный промежуток времени
		if (!empty($data["dStart"]) && !empty($data["dFinish"])) {
			$filterArray[] = "
				(
					(AD.AccountingData_setDate is null or AD.AccountingData_setDate <= :dStart) and
					(AD.AccountingData_endDate is null or AD.AccountingData_endDate >= :dFinish)
				)
			";
		}
		$params = [
			"Lpu_id" => $data["Lpu_id"],
			"LpuBuilding_id" => $data["LpuBuilding_id"],
			"filterLpuBuilding" => !empty($data["filterLpuBuilding"]) ? $data["filterLpuBuilding"] : null,
			"dStart" => !empty($data["dStart"]) ? $data["dStart"] : null,
			"dFinish" => !empty($data["dFinish"]) ? $data["dFinish"] : null
		];
		$apply = "";
		$select = "";
		if (!empty($data["dtStart"]) && !empty($data["dtFinish"])) {
			$apply = "
	            left join (
	                select 
						ET.EmergencyTeam_id,
						EmergencyTeam_Num,
						to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') AS EmergencyTeamDuty_DTStart,
						to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') AS EmergencyTeamDuty_DTFinish
					from
						v_EmergencyTeam ET
	                    left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
					where MPC.MedProductCard_id = ET.MedProductCard_id
	                  and coalesce(ET.EmergencyTeam_isTemplate, 1) = 1
	                  and
	                    (
	                        (ETD.EmergencyTeamDuty_DTStart >= :dtStart and ETD.EmergencyTeamDuty_DTStart <= :dtFinish) or
	                        (ETD.EmergencyTeamDuty_DTFinish >= :dtStart and ETD.EmergencyTeamDuty_DTFinish <= :dtFinish)
	                    )
	                limit 1
				) as team on true
			";
			$params["dtStart"] = $data["dtStart"];
			$params["dtFinish"] = $data["dtFinish"];
			$select = "
				team.EmergencyTeam_id as \"EmergencyTeam_id\",
				team.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				team.EmergencyTeamDuty_DTStart as \"EmergencyTeamDuty_DTStart\",
				team.EmergencyTeamDuty_DTFinish as \"EmergencyTeamDuty_DTFinish\"
			";
		}
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$selectString = "
			MPC.MedProductCard_id as \"MedProductCard_id\",
			LB.LpuBuilding_id as \"LpuBuilding_id\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			MPC.MedProductCard_BoardNumber as \"MedProductCard_BoardNumber\",
			AD.AccountingData_RegNumber as \"AccountingData_RegNumber\",
			MPCl.MedProductClass_Name as \"MedProductClass_Name\",
			MPCl.MedProductClass_Model as \"MedProductClass_Model\",
			MPT.MedProductType_Code as \"MedProductType_Code\",
			MPC.MedProductCard_Glonass as \"GeoserviceTransport_id\",
			to_char(AD.AccountingData_setDate, '{$callObject->dateTimeForm120}') as \"AccountingData_setDate\",
			to_char(AD.AccountingData_endDate, '{$callObject->dateTimeForm120}') as \"AccountingData_endDate\",
			{$select}
		";
		$fromString = "
			passport.v_MedProductCard MPC				
			left join passport.v_MedProductClass MPCl on MPCl.MedProductClass_id = MPC.MedProductClass_id
			left join passport.v_AccountingData AD on MPC.MedProductCard_id = AD.MedProductCard_id
			left join passport.v_MedProductType MPT on MPT.MedProductType_id = MPCl.MedProductType_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = MPC.LpuBuilding_id
			{$apply}
		";
		$query = "
			select {$selectString}
			from {$fromString}
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		$result = $result->result_array();
		if (!is_object($query) || count($result) == 0) {
			return false;
		}
		return $result;
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadEmergencyTeamShiftList(EmergencyTeam_model4E $callObject, $data)
	{
		$queryParams = [];
		if (!empty($data["session"]["CurMedService_id"])) {
			$lpuBuildingQuery = "
				select coalesce(MS.LpuBuilding_id, 0) as \"LpuBuilding_id\"
				from v_MedService MS
				where MS.MedService_id = :MedService_id
			";
			$lpuBuildingParams = ["MedService_id" => $data["session"]["CurMedService_id"]];
			$lpuBuildingResult = $callObject->db->query($lpuBuildingQuery, $lpuBuildingParams);
			if (!is_object($lpuBuildingResult)) {
				return false;
			}
			$lpuFilter = "";
			// Усли нужно загрузить список нарядов тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ
			if (isset($data["loadSelectSmp"])) {
				// возьмем ИД выбранных пользователем подразделений СМП
				$arrayIdSelectSmp = $callObject->loadIdSelectSmp();
				if (!empty($arrayIdSelectSmp)) {
					$arrayIdSelectSmpString = implode(",", $arrayIdSelectSmp);
					$lpuFilter = "ET.LpuBuilding_id in ({$arrayIdSelectSmpString})";
				} else {
					$lpuFilter = "1 = 0";
				}
			}
			$lpuBuildingResult = $lpuBuildingResult->result("array");
			if (!isset($lpuBuildingResult[0]) || empty($lpuBuildingResult[0]["LpuBuilding_id"])) {
				return false;
			}
			$queryParams["LpuBuilding_id"] = $lpuBuildingResult[0]["LpuBuilding_id"];
		}
		//если указан интервал то выводим бригады в интервале
		//иначе только те, у которых активная смена
		$filterArray = [];
		if (!empty($data["dateStart"]) && !empty($data["dateFinish"])) {
			$date_start = DateTime::createFromFormat("d.m.Y", $data["dateStart"]);
			$date_finish = DateTime::createFromFormat("d.m.Y", $data["dateFinish"]);
			$queryParams["dateStart"] = $date_start->format("Y-m-d");
			$queryParams["dateFinish"] = $date_finish->format("Y-m-d");
			$filterArray[] = "ETD.EmergencyTeamDuty_DTStart >= :dateStart and ETD.EmergencyTeamDuty_DTStart <= :dateFinish";
		} else {
			$filterArray[] = "ETD.EmergencyTeamDuty_DTStart < dbo.tzGetDate() and ETD.EmergencyTeamDuty_DTFinish > dbo.tzGetDate()";
		}
		$filterArray[] = "coalesce(ET.EmergencyTeam_isTemplate, 1) = 1 ";
		if ($lpuFilter) {
			$filterArray[] = $lpuFilter;
		} else {
			$filterArray[] = "ET.LpuBuilding_id = :LpuBuilding_id";
		}
		if (!empty($data["EmergencyTeamSpec_id"])) {
			$filterArray[] = "ET.EmergencyTeamSpec_id = :EmergencyTeamSpec_id";
			$queryParams["EmergencyTeamSpec_id"] = $data["EmergencyTeamSpec_id"];
		}
		//если указано выводить бригады по фактичекой дате начала и конца смены
		if (!empty($data["dateFactFinish"]) && !empty($data["dateFactStart"])) {
			/*
			 * по заданию #88109
			 * Бригада считается находящейся на смене, если текущая дата и время входят в фактический период работы бригады
			 * (поля «Фактическое начало работы», «Фактическое окончание работы» формы «Отметка о выходе бригад СМП»).
			 * Если Фактическое начало работы НЕ задано, то считается, что бригада НЕ вышла на смену.
			 * Если Фактическое начало работы заполнено, а Фактическое окончание работы НЕ заполнено, то считается, что бригада находится на смене.
			 */
			$date_start = DateTime::createFromFormat("d.m.Y H:i:s", $data["dateFactStart"]);
			$date_finish = DateTime::createFromFormat("d.m.Y H:i:s", $data["dateFactFinish"]);
			$queryParams["dateFactStart"] = $date_start->format("Y-m-d H:i:s");
			$queryParams["dateFactFinish"] = $date_finish->format("Y-m-d H:i:s");
			// некоторое пустые занчения сохранены как "1900-01-01 00:00:00", значит пока будем считать их как пустые
			$queryParams["is1900"] = "1900-01-01 00:00:00";
			$filterArray[] = "ETD.EmergencyTeamDuty_factToWorkDT < :dateFactStart";
			$filterArray[] = "ETD.EmergencyTeamDuty_factToWorkDT <> :is1900";
			$filterArray[] = "(ETD.EmergencyTeamDuty_factEndWorkDT is null or ETD.EmergencyTeamDuty_factEndWorkDT = :is1900 or ETD.EmergencyTeamDuty_factEndWorkDT > :dateFactFinish)";
		}
		if (!empty($data["showCurrentTeamsByFact"]) && $data["showCurrentTeamsByFact"] == "true") {
			$queryParams["is1900"] = "1900-01-01 00:00:00";
			$filterArray[] = "(ETD.EmergencyTeamDuty_factToWorkDT is not null and ETD.EmergencyTeamDuty_factToWorkDT < dbo.tzGetDate())";
			$filterArray[] = "(ETD.EmergencyTeamDuty_factEndWorkDT is null or ETD.EmergencyTeamDuty_factEndWorkDT = :is1900 or ETD.EmergencyTeamDuty_factEndWorkDT > dbo.tzGetDate())";
		}
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $callObject->_defineGeoserviceTransportRelQueryParams($data);

		$select = "GTR.{$GTR["GeoserviceTransport_id_field"]} as \"GeoserviceTransport_id\"";
		$join = "left join {$GTR["GeoserviceTransportRel_object"]} as GTR on GTR.{$GTR["EmergencyTeam_id_field"]} = ET.EmergencyTeam_id";
		$whereString = (count($filterArray) != 0)?" and ".implode(" and ", $filterArray) : "";
		$query = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
				ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
				ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
				LB.LpuBuilding_Nick as \"LpuBuilding_Nick\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
				ET.Lpu_id as \"Lpu_id\",
				ET.CMPTabletPC_id as \"CMPTabletPC_id\",
				ET.EmergencyTeam_Phone as \"EmergencyTeam_Phone\",
				ET.EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\",
				ET.EmergencyTeam_HeadShiftWorkPlace as \"EmergencyTeam_HeadShiftWorkPlace\",
				ET.EmergencyTeam_HeadShift2 as \"EmergencyTeam_HeadShift2\",
				ET.EmergencyTeam_HeadShift2WorkPlace as \"EmergencyTeam_HeadShift2WorkPlace\",
				ET.EmergencyTeam_Assistant1 as \"EmergencyTeam_Assistant1\",
				ET.EmergencyTeam_Assistant1WorkPlace as \"EmergencyTeam_Assistant1WorkPlace\",
				ET.EmergencyTeam_Assistant2 as \"EmergencyTeam_Assistant2\",
				ET.EmergencyTeam_Driver as \"EmergencyTeam_Driver\",
				ET.EmergencyTeam_DriverWorkPlace as \"EmergencyTeam_DriverWorkPlace\",
				ET.EmergencyTeam_Driver2 as \"EmergencyTeam_Driver2\",
				ET.EmergencyTeam_DutyTime as \"EmergencyTeam_DutyTime\",
				ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				ETSpec.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				MPh1.Person_Fin as \"EmergencyTeam_HeadShiftFIO\",
				MPh2.Person_Fin as \"EmergencyTeam_HeadShift2FIO\",
				MPd1.Person_Fin as \"EmergencyTeam_DriverFIO\",
				MPd2.Person_Fin as \"EmergencyTeam_Driver2FIO\",
				MPa1.Person_Fin as \"EmergencyTeam_Assistant1FIO\",
				MPa2.Person_Fin as \"EmergencyTeam_Assistant2FIO\",
				ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",			
				to_char(EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
				to_char(EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
				to_char(EmergencyTeamDuty_DTStart, '{$callObject->dateTimeFormUnixDate}') as \"EmergencyTeamDuty_DStart\",					
				to_char(EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeFormUnixDate}') as \"EmergencyTeamDuty_DFinish\",					
				to_char(EmergencyTeam_Head1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head1StartTime\",
				to_char(EmergencyTeam_Head1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head1FinishTime\",
				to_char(EmergencyTeam_Head2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head2StartTime\",
				to_char(EmergencyTeam_Head2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head2FinishTime\",
				to_char(EmergencyTeam_Assistant1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant1StartTime\",
				to_char(EmergencyTeam_Assistant1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant1FinishTime\",
				to_char(EmergencyTeam_Assistant2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant2StartTime\",
				to_char(EmergencyTeam_Assistant2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant2FinishTime\",
				to_char(EmergencyTeam_Driver1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver1StartTime\",
				to_char(EmergencyTeam_Driver1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver1FinishTime\",
				to_char(EmergencyTeam_Driver2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver2StartTime\",
				to_char(EmergencyTeam_Driver2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver2FinishTime\",
				ETD.EmergencyTeamDuty_ChangeComm as \"EmergencyTeamDuty_ChangeComm\",
				ETD.EmergencyTeamDuty_IsCancelledStart as \"EmergencyTeamDuty_IsCancelledStart\",
				ETD.EmergencyTeamDuty_IsCancelledClose as \"EmergencyTeamDuty_IsCancelledClose\",
				case when coalesce(ETD.EmergencyTeamDuty_isComesToWork, 1) = 1 then 'false' else 'true' end as \"locked\",
				case when coalesce(ETD.EmergencyTeamDuty_isClose, 1) = 1 then 'false' else 'true' end as \"closed\", 
				{$select}
			FROM
				v_EmergencyTeam ET
				left join v_MedPersonal MPh1 on MPh1.MedPersonal_id = ET.EmergencyTeam_HeadShift
				left join v_MedPersonal MPh2 on MPh2.MedPersonal_id = ET.EmergencyTeam_HeadShift2
				left join v_MedPersonal MPd1 on MPd1.MedPersonal_id = ET.EmergencyTeam_Driver
				left join v_MedPersonal MPd2 on MPd2.MedPersonal_id = ET.EmergencyTeam_Driver2
				left join v_MedPersonal MPa1 on MPa1.MedPersonal_id = ET.EmergencyTeam_Assistant1
				left join v_MedPersonal MPa2 on MPa2.MedPersonal_id = ET.EmergencyTeam_Assistant2
				left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				left join v_LpuBuilding LB on ET.LpuBuilding_id = LB.LpuBuilding_id
				left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				{$join}
			where ET.LpuBuilding_id = :LpuBuilding_id
			  and coalesce(ET.EmergencyTeam_isTemplate, 1) = 1
			  {$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if(!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamCombo(EmergencyTeam_model4E $callObject, $data)
	{
		// Выводим только бригады состоящих в ЛПУ пользователя
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		$query = "
        	select distinct
				et.EmergencyTeam_id as \"EmergencyTeam_id\",
				et.EmergencyTeam_Num as \"EmergencyTeam_Code\",
				trim(mp.Person_FIO) as \"EmergencyTeam_Name\"
			from
				v_EmergencyTeam et
				inner join v_MedPersonal mp on mp.MedPersonal_id=et.EmergencyTeam_HeadShift
			where et.Lpu_id = :Lpu_id
			order by et.EmergencyTeam_Num
    	";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function loadEmergencyTeamComboWithWialonID(EmergencyTeam_model4E $callObject, $data)
	{
		$filterArray = ["ET.Lpu_id = :Lpu_id"];
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		if (!empty($data["dateStart"])) {
			$queryParams["dateStart"] = $data["dateStart"];
			$filterArray[] = "ETD.EmergencyTeamDuty_DTStart = :dateStart";
		}
		if (!empty($data["workComing"]) && $data["workComing"]) {
			$filterArray[] = "coalesce(ETD.EmergencyTeamDuty_isClose, 1) = 1 and ETD.EmergencyTeamDuty_isComesToWork = 2";
		}
		$whereString = (count($filterArray) != 0)? "where ".implode(" and ", $filterArray) : "";
		$query = "
        	select distinct
				ETD.EmergencyTeamDuty_DTFinish as \"EmergencyTeamDuty_DTFinish\",
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Code\",
				trim(MP.Person_FIO) as \"EmergencyTeam_Name\",
				ETW.WialonEmergencyTeamId as \"WialonID\",
				ETD.EmergencyTeamDuty_isClose as \"EmergencyTeamDuty_isClose\"
			from
				v_EmergencyTeam et
				inner join v_MedPersonal MP on MP.MedPersonal_id=ET.EmergencyTeam_HeadShift
				left join v_EmergencyTeamWialonRel ETW on ETW.EmergencyTeam_id=ET.EmergencyTeam_id
				left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			{$whereString}
			order by ET.EmergencyTeam_Num
    	";
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadEmergencyTeamDutyTimeGrid(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			return false;
		}
		if (!array_key_exists("dateStart", $data) || empty($data["dateStart"]) || !array_key_exists("dateFinish", $data) || empty($data["dateFinish"])) {
			$sqlArr['dateStart'] = new DateTime( $data[ 'dateStart' ]);
			$sqlArr['dateFinish'] = new DateTime( $data[ 'dateFinish' ].' 23:59:59');
		}
		$queryParams = [
			"EmergencyTeam_id" => $data["EmergencyTeam_id"],
			"dateStart" => $data["dateStart"],
			"dateFinish" => $data["dateFinish"]
		];
		$query = "
			select
				etd.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				etd.EmergencyTeam_id as \"EmergencyTeam_id\",
				to_char(etd.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
                to_char(etd.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
				case
					when etd.EmergencyTeamDuty_isComesToWork = 2 THEN 'Да'
					when etd.EmergencyTeamDuty_isComesToWork = 1 THEN 'Нет'
					else ''
				end as \"ComesToWork\"
			from v_EmergencyTeamDuty etd
			WHERE etd.EmergencyTeam_id = :EmergencyTeam_id
			  and etd.EmergencyTeamDuty_DTStart::date >= :dateStart::date
			  and etd.EmergencyTeamDuty_DTStart::date <= :dateFinish::date
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$arr = $result->result("array");
		return [
			"data" => $arr,
			"totalCount" => sizeof($arr)
		];
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamDutyTimeListGrid(EmergencyTeam_model4E $callObject, $data)
	{
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		$where = ["ET.Lpu_id = :Lpu_id"];
		if ((array_key_exists("dateStart", $data) && $data["dateStart"] != null) && (array_key_exists("dateFinish", $data) && $data["dateFinish"] != null)) {
			$queryParams["dateStart"] = new DateTime($data["dateStart"]);
			$queryParams["dateFinish"] = new DateTime($data["dateFinish"] . " 23:59:59");
			$where[] = "etd.EmergencyTeamDuty_DTStart::date >= :dateStart::date";
			$where[] = "etd.EmergencyTeamDuty_DTStart::date <= :dateFinish::date";
		}
		if (!empty($data["session"]["CurMedService_id"])) {
			$lpuBuildingQuery = "
					select coalesce(MS.LpuBuilding_id, 0) as \"LpuBuilding_id\"
					from v_MedService MS
					where MS.MedService_id = :MedService_id
			";
			$lpuBuildingQueryParams = ["MedService_id" => $data["session"]["CurMedService_id"]];
			$lpuBuildingResult = $callObject->db->query($lpuBuildingQuery, $lpuBuildingQueryParams);
			if (!is_object($lpuBuildingResult)) {
				return false;
			}
			$lpuFilter = "";
			// Усли нужно загрузить список нарядов тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ
			if (isset($data["loadSelectSmp"])) {
				// возьмем ИД выбранных пользователем подразделений СМП
				$arrayIdSelectSmp = $callObject->loadIdSelectSmp();
				if (!empty($arrayIdSelectSmp)) {
					$arrayIdSelectSmpString = implode(",", $arrayIdSelectSmp);
					$lpuFilter .= "ET.LpuBuilding_id in ({$arrayIdSelectSmpString})";
				} else {
					$lpuFilter .= "1 = 0";
				}
			}
			$lpuBuildingResult = $lpuBuildingResult->result("array");
			if ($lpuFilter) {
				$where[] = $lpuFilter;
			} elseif (isset($lpuBuildingResult[0]) && (!empty($lpuBuildingResult[0]["LpuBuilding_id"]))) {
				$where[] = "et.LpuBuilding_id = " . (int)$lpuBuildingResult[0]["LpuBuilding_id"];
			} else {
				return false;
			}
		}
		$whereString = (count($where) != 0)?"where ".implode(" and ", $where):"";
		$query = "
				SELECT DISTINCT
					etd.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",					
					MPh1.Person_Fin as \"EmergencyTeam_HeadShiftFIO\",
					MPh2.Person_Fin as \"EmergencyTeam_HeadShift2FIO\",
					MPd1.Person_Fin as \"EmergencyTeam_DriverFIO\",
					MPd2.Person_Fin as \"EmergencyTeam_Driver2FIO\",
					MPa1.Person_Fin as \"EmergencyTeam_Assistant1FIO\",
					MPa2.Person_Fin as \"EmergencyTeam_Assistant2FIO\",
					to_char(etd.EmergencyTeam_Head1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head1StartTime\",
					to_char(etd.EmergencyTeam_Head1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head1FinishTime\",
					to_char(etd.EmergencyTeam_Head2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head2StartTime\",
					to_char(etd.EmergencyTeam_Head2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Head2FinishTime\",
					to_char(etd.EmergencyTeam_Assistant1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant1StartTime\",
					to_char(etd.EmergencyTeam_Assistant1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant1FinishTime\",
					to_char(etd.EmergencyTeam_Assistant2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant2StartTime\",
					to_char(etd.EmergencyTeam_Assistant2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Assistant2FinishTime\",
					to_char(etd.EmergencyTeam_Driver1StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver1StartTime\",
					to_char(etd.EmergencyTeam_Driver1FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver1FinishTime\",
					to_char(etd.EmergencyTeam_Driver2StartTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver2StartTime\",
					to_char(etd.EmergencyTeam_Driver2FinishTime, '{$callObject->dateTimeForm120}') as \"EmergencyTeam_Driver2FinishTime\",
					et.EmergencyTeam_id as \"EmergencyTeam_id\",
					lpub.LpuBuilding_Name as \"LpuBuilding_Name\",
					et.EmergencyTeam_Num as \"EmergencyTeam_Num\",
					to_char(etd.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
					to_char(etd.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeFormUnixDate}') as \"EmergencyTeamDuty_DStart\",
					to_char(etd.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm108}') as \"EmergencyTeamDuty_TStart\",
					to_char(etd.EmergencyTeamDuty_factToWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factToWorkDT\",
					to_char(etd.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
					to_char(etd.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeFormUnixDate}') as \"EmergencyTeamDuty_DFinish\",
					to_char(etd.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm108}') as \"EmergencyTeamDuty_TFinish\",
					case when coalesce(etd.EmergencyTeamDuty_isComesToWork, 1) = 2 then 'true' else 'false' end as \"ComesToWork\",
					case when coalesce(etd.EmergencyTeamDuty_isClose, 1) = 2 then 'true' else 'false' end as \"closed\",
					etd.EmergencyTeamDuty_Comm as \"EmergencyTeamDuty_Comm\",
					case when coalesce(etd.EmergencyTeamDuty_IsCancelledStart, 1) = 2 then 'true' else 'false' end as \"EmergencyTeamDuty_IsCancelledStart\",
					case when coalesce(etd.EmergencyTeamDuty_IsCancelledClose, 1) = 2 then 'true' else 'false' end as \"EmergencyTeamDuty_IsCancelledClose\",
					etd.EmergencyTeamDuty_ChangeComm as \"EmergencyTeamDuty_ChangeComm\"
			from
				v_EmergencyTeamDuty etd
				left join v_EmergencyTeam et on et.EmergencyTeam_id = etd.EmergencyTeam_id
				left join v_MedPersonal MPh1 on MPh1.MedPersonal_id = et.EmergencyTeam_HeadShift
				left join v_MedPersonal MPh2 on MPh2.MedPersonal_id = et.EmergencyTeam_HeadShift2
				left join v_MedPersonal MPd1 on MPd1.MedPersonal_id = et.EmergencyTeam_Driver
				left join v_MedPersonal MPd2 on MPd2.MedPersonal_id = et.EmergencyTeam_Driver2
				left join v_MedPersonal MPa1 on MPa1.MedPersonal_id = et.EmergencyTeam_Assistant1
				left join v_MedPersonal MPa2 on MPa2.MedPersonal_id = et.EmergencyTeam_Assistant2
				left join v_LpuBuilding lpub on lpub.LpuBuilding_id = et.LpuBuilding_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$result = $result->result_array();
		return [
			"data" => $result,
			"totalCount" => sizeof($result)
		];
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamOperEnv(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("Lpu_id", $data) || !$data["Lpu_id"]) {
			throw new Exception("Не указан идентификатор ЛПУ");
		}
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $callObject->_defineGeoserviceTransportRelQueryParams($data);
		$select = "GTR.{$GTR["GeoserviceTransport_id_field"]} as \"GeoserviceTransport_id\"";
		$join = "left join {$GTR["GeoserviceTransportRel_object"]} as GTR on GTR.{$GTR["EmergencyTeam_id_field"]} = ET.EmergencyTeam_id";
		$where = "GTR.{$GTR["GeoserviceTransport_id_field"]} > 0";
		$query = "
			select distinct
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
				ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
				ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
				ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
				ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				case when coalesce(ET.EmergencyTeam_IsOnline, 1) = 2 THEN 'online' ELSE 'offline' END as \"EmergencyTeam_isOnline\",
				(case when ET.EmergencyTeam_HeadShift2 is not null then 1 else 0 end)+
					(case when ET.EmergencyTeam_Assistant1 is not null then 1 else 0 end)+
					(case when ET.EmergencyTeam_Assistant2 is not null then 1 else 0 end)
				as \"medPersonCount\",
				LB.LpuBuilding_Nick as \"EmergencyTeamBuildingName\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
				to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
				ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				case
					when ETS.EmergencyTeamStatus_Code = 14 THEN 'red'
					when ETS.EmergencyTeamStatus_Code = 3 THEN 'red'
					when ETS.EmergencyTeamStatus_Code = 21 THEN 'blue'
					when ETS.EmergencyTeamStatus_Code = 13 THEN 'blue'
					when ETS.EmergencyTeamStatus_Code = 23 THEN 'green'
					when ETS.EmergencyTeamStatus_Code = 8 THEN 'green'
					else 'black'
				end as \"EmergencyTeamStatus_Color\",
				MP.Person_Fin as \"Person_Fin\",
		        {$select}
			from
				v_EmergencyTeam as ET
				left join v_EmergencyTeamStatus as ETS on ETS.EmergencyTeamStatus_id = ET.EmergencyTeamStatus_id
				left join v_MedPersonal as MP on MP.MedPersonal_id = ET.EmergencyTeam_HeadShift
				left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				left join v_EmergencyTeamDuty as ETD on ETD.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_LpuBuilding as LB on LB.LpuBuilding_id = ET.LpuBuilding_id
				{$join}
			where ET.Lpu_id = :Lpu_id
			  and {$where}
			order by EmergencyTeam_Num
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamOperEnvForSmpUnit(EmergencyTeam_model4E $callObject, $data)
	{
		$where = [];
		$params = [];
		$lpuBuildingsWorkAccess = null;
		$regionNick = getRegionNick();
		$CurArmType = (!empty($data["CurArmType"]) ? $data["CurArmType"] : "");
		// здесь мы получаем список доступных подстанций для работы из лдапа
		$user = pmAuthUser::find($_SESSION["login"]);
		$settings = @unserialize($user->settings);
		if (isset($settings["lpuBuildingsWorkAccess"]) && is_array($settings["lpuBuildingsWorkAccess"])) {
			$lpuBuildingsWorkAccess = $settings["lpuBuildingsWorkAccess"];
		}
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $callObject->_defineGeoserviceTransportRelQueryParams($data);
		if (!in_array($data["CurArmType"], ["dispcallnmp", "dispdirnmp"]) and getRegionNick() != "astra") {
			$where[] = "ET.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		if (empty($lpuBuildingsWorkAccess)) {
			throw new Exception("Не настроен список доступных для работы подстанций");
		}
		if ($lpuBuildingsWorkAccess[0] == "") {
			throw new Exception("Не настроен список доступных для работы подстанций");
		}
		if (empty($data["LpuBuilding_id"])) {
			$data["LpuBuilding_id"] = $lpuBuildingsWorkAccess[0];
		}
		$where[] = "coalesce(ET.EmergencyTeam_isTemplate, 1) = 1";
		$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
		$callObject->load->model("LpuStructure_model", "LpuStructure");
		$operDpt = $callObject->CmpCallCard_model4E->getOperDepartament($data);
		$operDptParams = $callObject->LpuStructure->getLpuBuildingData(["LpuBuilding_id" => $operDpt["LpuBuilding_pid"]]);
		$operDptParams = $operDptParams[0];
		//здесь реализовано оповещение и автоматический выход на смену / снятие со смены бригады смп
		$autoStartDutyTeamsIds = null;
		$autoFinishDutyTeamsIds = null;
		$cur_date = new DateTime();
		$cur_date = $cur_date->format("Y-m-d H:i:s");
		if (!empty($operDptParams["SmpUnitParam_IsAutoEmergDuty"]) && $operDptParams["SmpUnitParam_IsAutoEmergDuty"] == "true") {
			//получаем список просроченных по плановому выходу на смену бригад
			//Добавим фильтр по выбранным подстанциям, чтоб не выводились чужие бригады
			$lpuBuildingsWorkAccessString = implode(",", $lpuBuildingsWorkAccess);
			$autoStartDutyTeams = $callObject->getAutoStartVigil($data, array_merge($where, ["LB.LpuBuilding_id in ({$lpuBuildingsWorkAccessString})"]), $params);
			if ($autoStartDutyTeams) {
				$autoStartDutyTeamsIds = [];
				foreach ($autoStartDutyTeams as &$value) {
					$value["ComesToWork"] = 2;
					$value["EmergencyTeamDuty_factToWorkDT"] = $cur_date;
					$autoStartDutyTeamsIds[] = $value["EmergencyTeam_id"];
				}
				$teamsAutoStartArray = ["pmUser_id" => $data["pmUser_id"]];
				$teamsAutoStartArray["EmergencyTeamsDutyTimesAndComing"] = json_encode($autoStartDutyTeams);
				//проставляем время фактического выхода на смену и признак
				$callObject->setEmergencyTeamsWorkComingList($teamsAutoStartArray);
			}
		}
		if (!empty($operDptParams["SmpUnitParam_IsAutoEmergDutyClose"]) && $operDptParams["SmpUnitParam_IsAutoEmergDutyClose"] == "true") {
			//получаем список просроченных по плановому завершению смены бригад
			//Добавим фильтр по выбранным подстанциям, чтоб не закрывались чужие бригады
			$lpuBuildingsWorkAccessString = implode(",", $lpuBuildingsWorkAccess);
			$autoFinishDutyTeams = $callObject->getAutoFinishVigil($data, array_merge($where, ["LB.LpuBuilding_id in ({$lpuBuildingsWorkAccessString})"]), $params);
			if ($autoFinishDutyTeams) {
				$autoFinishDutyTeamsIds = [];
				foreach ($autoFinishDutyTeams as &$value) {
					$value["ComesToWork"] = 2;
					$value["closed"] = 2;
					$value["EmergencyTeamDuty_factEndWorkDT"] = $cur_date;
					$autoFinishDutyTeamsIds[] = $value["EmergencyTeam_id"];
				}
				$teamsAutoFinishArray = ["pmUser_id" => $data["pmUser_id"]];
				$teamsAutoFinishArray["EmergencyTeamsDutyTimesAndComing"] = json_encode($autoFinishDutyTeams);
				if ($autoFinishDutyTeamsIds) {
					//проставляем время фактического завершения смены и признак
					$callObject->setEmergencyTeamsWorkComingList($teamsAutoFinishArray);
				}
			}
		}
		//В зависимости от настроек загружаем вызовы
		if ($operDptParams["SmpUnitParam_IsViewOther"] == "true") {
			//всех подстанция опер отдела
			$params["LpuBuilding_pid"] = $operDpt["LpuBuilding_pid"];
			$where[] = "SUP.LpuBuilding_pid = :LpuBuilding_pid";
			//с флагом доступа к бригаде
			$lpuBuildingsWorkAccessString = implode(",", $lpuBuildingsWorkAccess);
			$WorkAccess = "
				,case when (coalesce(ETSpec.EmergencyTeamSpec_isTeamAvailable, 1) = 2 or LB.LpuBuilding_id in ({$lpuBuildingsWorkAccessString})) then 'true' else 'false' end as \"WorkAccess\"
			";
		} else {
			//либо только выбранные пользователем
			//в этом месте запрос подвисает, тк подразделений на рабочем много, нужен доп. фильтр по мо
			$lpuBuildingsWorkAccessString = implode(",", $lpuBuildingsWorkAccess);
			$where[] = "LB.LpuBuilding_id in ({$lpuBuildingsWorkAccessString})";
			$WorkAccess = ",'true' as \"WorkAccess\"";
		}
		$lastCallOnTeamApply = "";
		$checkInAddress = "'' as \"lastCheckinAddress\",";
		if ( in_array($regionNick, array('perm', 'kareliya')) && !in_array($CurArmType, array('dispcallnmp', 'dispnmp', 'dispdirnmp')) ) {
			//из за СМП Перми не будем тормозить другие регионы
			$checkInAddress = "
				case when (ETS.EmergencyTeamStatus_Code in (21) or lastCallOnTeam.CmpCallCard_id is null)
				then
					case when LBSRGNCity.KLSubRgn_Name is not null
						then LBSRGNCity.KLSocr_Nick||' '||LBSRGNCity.KLSubRgn_Name||', '
						else
							case when LBSRGNTown.KLSubRgn_Name is not null
								then LBSRGNTown.KLSocr_Nick||' '||LBSRGNTown.KLSubRgn_Name||', '
								else
									case when LBSRGN.KLSubRgn_Name is not null
										then LBSRGN.KLSocr_Nick||' '||LBSRGN.KLSubRgn_Name||', '
										else ''
									end
							end
					end||
					case when LBCity.KLCity_Name is not null then 'г. '||LBCity.KLCity_Name else '' end||
					case when LBTown.KLTown_FullName is not null then
						case when LBCity.KLCity_Name is not null then ', ' else '' end||
							coalesce(LOWER(LBTown.KLSocr_Nick)||'. ', '')||LBTown.KLTown_Name else ''
						end||
						case when LBStreet.KLStreet_FullName is not null
							then
								case when LBStreet.KLSocr_Nick is not null
									then ', '||lower(LBStreet.KLSocr_Nick)||'. '||LBStreet.KLStreet_Name
									else ', '||LBStreet.KLStreet_FullName
								end
							else ''
						end||
						case when LBAddress.Address_House is not null then ', д.'||LBAddress.Address_House else '' end||
						case when LBAddress.Address_Corpus is not null then ', к.'||LBAddress.Address_Corpus else '' end||
						case when LBAddress.Address_Flat is not null then ', кв.'||LBAddress.Address_Flat else '' end
				else
					lastCallOnTeam.Adress_Name
				end as \"lastCheckinAddress\",
			";
			$lastCallOnTeamApply = "
				left join lateral (
					select
						c.CmpCallCard_id as \"CmpCallCard_id\",
						case when SRGNCity.KLSubRgn_Name is not null
							then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
							else
								case when SRGNTown.KLSubRgn_Name is not null
									then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
									else
										case when SRGN.KLSubRgn_Name is not null
											then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', '
											else ''
										end
								end
						end||
						case when City.KLCity_Name is not null
							then 'г. '||City.KLCity_Name
							else ''
						end||
						case when Town.KLTown_FullName is not null
							then
								case when City.KLCity_Name is not null
									then ', '
									else ''
								end||coalesce(lower(Town.KLSocr_Nick)||'. ', '')||Town.KLTown_Name
							else ''
						end||
						case when Street.KLStreet_FullName is not null
							then
								case when Street.KLSocr_Nick is not null
									then ', '||lower(Street.KLSocr_Nick)||'. '||Street.KLStreet_Name
									else ', '||Street.KLStreet_FullName
								end
							else
								case when c.CmpCallCard_Ulic is not null
									then ', '||c.CmpCallCard_Ulic
									else ''
								end
						end||
						case when SecondStreet.KLStreet_FullName is not null
							then
								case when SecondStreet.KLSocr_Nick is not null
									then ', '||lower(SecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name
									else ', '||SecondStreet.KLStreet_FullName
								end
							else ''
						end||
						case when c.CmpCallCard_Dom is not null then ', д.'||c.CmpCallCard_Dom else '' end||
						case when c.CmpCallCard_Korp is not null then ', к.'||c.CmpCallCard_Korp else '' end||
						case when c.CmpCallCard_Kvar is not null then ', кв.'||c.CmpCallCard_Kvar else '' end||
						case when c.CmpCallCard_Room is not null then ', ком. '||c.CmpCallCard_Room else '' end||
						case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end
					as \"Adress_Name\"
					from
						v_CmpCallCardTeamsAssignmentHistory cctah
						left join v_CmpCallCard c on cctah.CmpCallCard_id = c.CmpCallCard_id
						left join lateral (
							select *
							from v_EmergencyTeamStatusHistory
							where EmergencyTeam_id = c.EmergencyTeam_id
							  and CmpCallCard_id = c.CmpCallCard_id
							  order by EmergencyTeamStatusHistory_id desc
							limit 1
						) as lastETSH on true
						left join v_EmergencyTeamStatus lastCallETS on lastETSH.EmergencyTeamStatus_id = lastCallETS.EmergencyTeamStatus_id
						left join v_KLRgn RGN on RGN.KLRgn_id = c.KLRgn_id
						left join v_KLRgn RGNCity on RGNCity.KLRgn_id = c.KLCity_id
						left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = c.KLSubRgn_id
						left join v_KLCity City on City.KLCity_id = c.KLCity_id
						left join v_KLTown Town on Town.KLTown_id = c.KLTown_id
						left join v_KLSubRgn SRGNTown on SRGNTown.KLSubRgn_id = c.KLTown_id
						left join v_KLSubRgn SRGNCity on SRGNCity.KLSubRgn_id = c.KLCity_id
						left join v_KLStreet Street on Street.KLStreet_id = c.KLStreet_id
						left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = c.UnformalizedAddressDirectory_id
						left join v_KLStreet SecondStreet on SecondStreet.KLStreet_id = c.CmpCallCard_UlicSecond
					where c.EmergencyTeam_id = ET.EmergencyTeam_id
					  and lastCallETS.EmergencyTeamStatus_Code != 36
					order by CmpCallCardTeamsAssignmentHistory_id desc
					limit 1
				) as lastCallOnTeam on true
				left join v_Address LBAddress on LB.Address_id = LBAddress.Address_id
				left join v_KLRgn LBRGN on LBRGN.KLRgn_id = LBAddress.KLRgn_id
				left join v_KLRgn LBRGNCity on LBRGNCity.KLRgn_id = LBAddress.KLCity_id
				left join v_KLSubRgn LBSRGN on LBSRGN.KLSubRgn_id = LBAddress.KLSubRgn_id
				left join v_KLCity LBCity on LBCity.KLCity_id = LBAddress.KLCity_id
				left join v_KLTown LBTown on LBTown.KLTown_id = LBAddress.KLTown_id
				left join v_KLSubRgn LBSRGNTown on LBSRGNTown.KLSubRgn_id = LBAddress.KLTown_id
				left join v_KLSubRgn LBSRGNCity on LBSRGNCity.KLSubRgn_id = LBAddress.KLCity_id
				left join v_KLStreet LBStreet on LBStreet.KLStreet_id = LBAddress.KLStreet_id
			";
		}
		$soundSetting = (!empty($operDptParams["SmpUnitParam_IsSignalEnd"]) && $operDptParams["SmpUnitParam_IsSignalEnd"] == "true")
			?",2 as \"IsSignalEnd\""
			:",'1' as \"IsSignalEnd\"";
		$countCallsOnTeam = ",null as \"countcallsOnTeam\"";
		if ($operDptParams["SmpUnitParam_IsShowCallCount"] == "true") {
			$countCallsOnTeam = ",callsOnTeam.countCalls as \"countcallsOnTeam\"";
		}
		// Вышел на смену
		$where[] = "ETD.EmergencyTeamDuty_isComesToWork = 2";
		//Временной интервал
		$where[] = "(coalesce(ETD.EmergencyTeamDuty_factToWorkDT, tzgetdate()) <= tzgetdate() and coalesce(ETD.EmergencyTeamDuty_factEndWorkDT, tzgetdate()) >= tzgetdate())";
		#141727 Выводим кол-во обслуженых вызовов для Уфы (статусы Обслужено и Закрыто)
		$countCloseCalls = "'' as \"countCloseCalls\"";
		$CloseCalls = "";
		if (in_array($regionNick, ["ufa"])) {
			$countCloseCalls = "countCloseCalls.countCloseCalls";
			$CloseCalls = "
				left join lateral (
					select count(1) as \"countCloseCalls\"
					from v_CmpCallCard c
					where c.EmergencyTeam_id = ET.EmergencyTeam_id
					  and c.CmpCallCardStatusType_id in (4, 6)
				) as countCloseCalls on true
			";
		}
		$whereString = (count($where) != 0)? "where ".implode(" and ", $where) : "";
		$query = "
			select distinct
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
				ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
				ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
				ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
				ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
				ET.LpuBuilding_id as \"LpuBuilding_id\",
				ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				case when tzgetdate() between ETD.EmergencyTeamDuty_factToWorkDT and ETD.EmergencyTeamDuty_DTFinish
				    then 0
				    else 1
				end as \"EmergencyTeamDuty_isNotFact\",
				datediff('mi', ETES.EmergencyTeamStatusHistory_insDT, tzgetdate()) as \"EmergencyTeamStatusHistory_insDT\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
				to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
				to_char(ETD.EmergencyTeamDuty_factToWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factToWorkDT\",
				to_char(ETD.EmergencyTeamDuty_factEndWorkDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_factEndWorkDT\",
				ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				CC.CmpCallCard_id as \"CmpCallCard_id\",
				CC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				case when ETS.EmergencyTeamStatus_Code in (3, 53) then hL.Lpu_Nick else '' end as \"HLpu_Nick\",
				case when coalesce(ET.EmergencyTeam_isOnline, 1)=2 then 'online' else 'offline' end as \"EmergencyTeam_isOnline\",
				case when coalesce(ET.EmergencyTeam_HeadShift2, 0)!=0 then 1 else 0 end +
				case when coalesce(ET.EmergencyTeam_Assistant1, 0)!=0 then 1 else 0 end +
				case when coalesce(ET.EmergencyTeam_Assistant2, 0)!=0 then 1 else 0 end as \"medPersonCount\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
				case
					when ETS.EmergencyTeamStatus_Code in (3, 14) then 'red'
					when ETS.EmergencyTeamStatus_Code in (13, 21) then 'blue'
					when ETS.EmergencyTeamStatus_Code in (8, 23) then 'green'
					else 'black'
				end as \"EmergencyTeamStatus_Color\",
				case when ETS.EmergencyTeamStatus_Code in (13, 4, 5, 20, 21, 47)
				    then 'true'
					else 'false'
				end as \"EmergencyTeamStatus_FREE\",
				{$checkInAddress}
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				L.Lpu_Nick as \"Lpu_Nick\",
				upper(substring(MP.Person_SurName, 1, 1))||substring(trim(MP.Person_SurName), 2, length(rtrim(lower(MP.Person_SurName))))||' '||substring(MP.Person_FirName, 1, 1)||case when MP.Person_SecName is null then '' else ' '||substring(MP.Person_SecName, 1, 1) end as \"Person_Fin\",
				MP.MedPersonal_id as \"MedPersonal_id\",
				coalesce(GTR.{$GTR["GeoserviceTransport_id_field"]}, MPC.MedProductCard_Glonass) as \"GeoserviceTransport_id\",
				SUP.LpuBuilding_pid as \"LpuBuilding_pid\",
				alertToStartVigil.CmpEmTeamDuty_id as \"alertToStartVigil\",
				alertToEndVigil.CmpEmTeamDuty_id as \"alertToEndVigil\",
				{$countCloseCalls}
				{$soundSetting}
				{$WorkAccess}
				{$countCallsOnTeam}
			from
				v_EmergencyTeam as ET
				left join v_EmergencyTeamStatus AS ETS on ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
				left join v_EmergencyTeamDuty AS ETD on ETD.EmergencyTeam_id=ET.EmergencyTeam_id
				left join v_MedPersonal as MP on MP.MedPersonal_id=ET.EmergencyTeam_HeadShift
				left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				left join lateral (
					select
						c.CmpCallCard_id,
						c.CmpCallCard_Numv,
						c.CmpCallCard_Ngod,
						c.Lpu_hid,
						c.CmpCallCard_updDT
					from
						v_CmpCallCardTeamsAssignmentHistory cctah
						left join v_CmpCallCard c on cctah.CmpCallCard_id = c.CmpCallCard_id
						left join v_CmpCallCardStatus CCCS on CCCS.CmpCallCardStatus_id = c.CmpCallCardStatus_id
					where c.EmergencyTeam_id = ET.EmergencyTeam_id
					  and CCCS.CmpCallCardStatusType_id = 2
					order by CmpCallCardTeamsAssignmentHistory_id
					limit 1
				) as CC on true
				left join lateral (
					select count(1) as countCalls
					from v_CmpCallCard c
					where c.EmergencyTeam_id = ET.EmergencyTeam_id
					  and c.CmpCallCardStatusType_id = 2
				) as callsOnTeam on true
				{$CloseCalls}
				left join v_Lpu hL on CC.Lpu_hid = hL.Lpu_id
				left join passport.v_MedProductCard MPC on MPC.MedProductCard_id = ET.MedProductCard_id
				left join {$GTR["GeoserviceTransportRel_object"]} as GTR on GTR.{$GTR["EmergencyTeam_id_field"]} = ET.EmergencyTeam_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = ET.LpuBuilding_id
				left join v_Lpu L on LB.Lpu_id = L.Lpu_id
				left join lateral (
					select EmergencyTeamStatusHistory_insDT
					from v_EmergencyTeamStatusHistory
					where EmergencyTeam_id = ET.EmergencyTeam_id
					order by EmergencyTeamStatusHistory_insDT desc
					limit 1
				) as ETES on true
				left join v_CmpEmTeamDuty as alertToStartVigil on alertToStartVigil.EmergencyTeam_id = ET.EmergencyTeam_id and tzgetdate() > alertToStartVigil.CmpEmTeamDuty_PlanBegDT and tzgetdate() < alertToStartVigil.CmpEmTeamDuty_PlanEndDT and ETS.EmergencyTeamStatus_Code in (4, 5, 13, 47)
				left join v_CmpEmTeamDuty as alertToEndVigil on alertToEndVigil.EmergencyTeam_id = ET.EmergencyTeam_id and tzgetdate() > alertToEndVigil.CmpEmTeamDuty_PlanEndDT and ETS.EmergencyTeamStatus_Code in (50)
				left join lateral (
					select *
					from v_SmpUnitParam
					where LpuBuilding_id = ET.LpuBuilding_id
					order by SmpUnitParam_id desc
					limit 1
				) as SUP on true
				{$lastCallOnTeamApply}
			{$whereString}
			order by
				\"EmergencyTeamStatus_FREE\" desc,
				\"EmergencyTeam_Num\"
		";
		if (isset($_GET["dbg"]) && $_GET["dbg"] == "1") {
			var_dump(getDebugSQL($query, $params));
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamOperEnvForSmpUnitsNested(EmergencyTeam_model4E $callObject, $data)
	{
		$where = [];
		$params = [];
		$regionNick = getRegionNick();
		$CurArmType = (!empty($data["session"]["CurArmType"]) ? $data["session"]["CurArmType"] : "");
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $callObject->_defineGeoserviceTransportRelQueryParams($data);
		$where[] = "coalesce(ET.EmergencyTeam_isTemplate, 1) = 1";
		// Вышел на смену
		$where[] = "ETD.EmergencyTeamDuty_isComesToWork = 2";
		$where[] = "ETD.EmergencyTeamDuty_isClose = 1";
		//$where[] = "dbo.tzGetDate() BETWEEN ETD.EmergencyTeamDuty_factToWorkDT AND ETD.EmergencyTeamDuty_DTFinish ";
		$where[] = "(coalesce(ETD.EmergencyTeamDuty_factToWorkDT, tzgetdate()) <= tzgetdate() and coalesce(ETD.EmergencyTeamDuty_factEndWorkDT, tzgetdate()) >= tzgetdate())";
		// здесь мы получаем список доступных подстанций для работы
		$where[] = $callObject->getNestedLpuBuildingsForRequests($data);
		$lastCallOnTeamApply = "";
		$checkInAddress = "'' as \"lastCheckinAddress\",";
		if (in_array($regionNick, ["perm"]) && !in_array($CurArmType, ["dispnmp", "dispdirnmp"])) {
			//из за СМП Перми не будем тормозить другие регионы
			$checkInAddress = "
				case when ETS.EmergencyTeamStatus_Code in (21) or lastCallOnTeam.CmpCallCard_id is null
					then
						case when LBSRGNCity.KLSubRgn_Name is not null
							then LBSRGNCity.KLSocr_Nick||' '||LBSRGNCity.KLSubRgn_Name||', '
							else
								case when LBSRGNTown.KLSubRgn_Name is not null
									then LBSRGNTown.KLSocr_Nick||' '||LBSRGNTown.KLSubRgn_Name||', '
									else
										case when LBSRGN.KLSubRgn_Name is not null
											then LBSRGN.KLSocr_Nick||' '||LBSRGN.KLSubRgn_Name||', '
											else ''
										end
								end
						end||
						case when LBCity.KLCity_Name is not null then 'г. '||LBCity.KLCity_Name else '' end||
						case when LBTown.KLTown_FullName is not null
							then
								case when LBCity.KLCity_Name is not null
									then ', '
									else ''
								end||coalesce(lower(LBTown.KLSocr_Nick)||'. ', '')||LBTown.KLTown_Name
							else ''
						end||
						case when LBStreet.KLStreet_FullName is not null
							then
								case when LBStreet.KLSocr_Nick is not null
									then ', '||lower(LBStreet.KLSocr_Nick)||'. '||LBStreet.KLStreet_Name
									else ', '||LBStreet.KLStreet_FullName
								end
							else ''
						end||
						case when LBAddress.Address_House is not null then ', д.'||LBAddress.Address_House else '' end||
						case when LBAddress.Address_Corpus is not null then ', к.'||LBAddress.Address_Corpus else '' end||
						case when LBAddress.Address_Flat is not null then ', кв.'||LBAddress.Address_Flat else '' end
					else lastCallOnTeam.Adress_Name
				end as \"lastCheckinAddress\",
			";
			$lastCallOnTeamApply = "
				left join lateral (
					select
						c.CmpCallCard_id,
						case when SRGNCity.KLSubRgn_Name is not null
							then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
							else
								case when SRGNTown.KLSubRgn_Name is not null
									then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
									else
										case when SRGN.KLSubRgn_Name is not null
											then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', '
											else ''
										end
								end
						end||
						case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
						case when Town.KLTown_FullName is not null
							then
								case when City.KLCity_Name is not null
									then ', '
									else ''
								end||coalesce(lower(Town.KLSocr_Nick)||'. ', '')||Town.KLTown_Name
							else ''
						end||
						case when Street.KLStreet_FullName is not null
							then
								case when Street.KLSocr_Nick is not null
									then ', '||lower(Street.KLSocr_Nick)||'. '||Street.KLStreet_Name
									else ', '||Street.KLStreet_FullName
								end
							else
								case when c.CmpCallCard_Ulic is not null
									then ', '||c.CmpCallCard_Ulic
									else ''
								end
						end||
						case when SecondStreet.KLStreet_FullName is not null
							then
								case when SecondStreet.KLSocr_Nick is not null
									then ', '||lower(SecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name
									else ', '||SecondStreet.KLStreet_FullName
								end
							else ''
						end||
						case when c.CmpCallCard_Dom is not null then ', д.'||c.CmpCallCard_Dom else '' end||
						case when c.CmpCallCard_Korp is not null then ', к.'||c.CmpCallCard_Korp else '' end||
						case when c.CmpCallCard_Kvar is not null then ', кв.'||c.CmpCallCard_Kvar else '' end||
						case when c.CmpCallCard_Room is not null then ', ком. '||c.CmpCallCard_Room else '' end||
						case when UAD.UnformalizedAddressDirectory_Name is not null
							then ', Место: '||UAD.UnformalizedAddressDirectory_Name
							else ''
						end
						as \"Adress_Name\"
					from
						v_CmpCallCardTeamsAssignmentHistory cctah
						left join v_CmpCallCard c on cctah.CmpCallCard_id = c.CmpCallCard_id
						left join lateral (
							select *
							from v_EmergencyTeamStatusHistory
							where EmergencyTeam_id = c.EmergencyTeam_id
							  and CmpCallCard_id = c.CmpCallCard_id
							order by EmergencyTeamStatusHistory_id desc
							limit 1
						) as lastETSH on true
						left join v_EmergencyTeamStatus lastCallETS on lastETSH.EmergencyTeamStatus_id = lastCallETS.EmergencyTeamStatus_id
						left join v_KLRgn RGN on RGN.KLRgn_id = c.KLRgn_id
						left join v_KLRgn RGNCity on RGNCity.KLRgn_id = c.KLCity_id
						left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = c.KLSubRgn_id
						left join v_KLCity City on City.KLCity_id = c.KLCity_id
						left join v_KLTown Town on Town.KLTown_id = c.KLTown_id
						left join v_KLSubRgn SRGNTown on SRGNTown.KLSubRgn_id = c.KLTown_id
						left join v_KLSubRgn SRGNCity on SRGNCity.KLSubRgn_id = c.KLCity_id
						left join v_KLStreet Street on Street.KLStreet_id = c.KLStreet_id
						left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = c.UnformalizedAddressDirectory_id
						left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
						left join v_KLStreet SecondStreet on SecondStreet.KLStreet_id = c.CmpCallCard_UlicSecond
						left join v_KLSocr socrSecondStreet on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
					where c.EmergencyTeam_id = ET.EmergencyTeam_id
					  and lastCallETS.EmergencyTeamStatus_Code != 36
					order by CmpCallCardTeamsAssignmentHistory_id desc
					limit 1
				) as lastCallOnTeam on true
				left join v_Address LBAddress on LB.Address_id = LBAddress.Address_id
				left join v_KLRgn LBRGN on LBRGN.KLRgn_id = LBAddress.KLRgn_id
				left join v_KLRgn LBRGNCity on LBRGNCity.KLRgn_id = LBAddress.KLCity_id
				left join v_KLSubRgn LBSRGN on LBSRGN.KLSubRgn_id = LBAddress.KLSubRgn_id
				left join v_KLCity LBCity on LBCity.KLCity_id = LBAddress.KLCity_id
				left join v_KLTown LBTown on LBTown.KLTown_id = LBAddress.KLTown_id
				left join v_KLSubRgn LBSRGNTown on LBSRGNTown.KLSubRgn_id = LBAddress.KLTown_id
				left join v_KLSubRgn LBSRGNCity on LBSRGNCity.KLSubRgn_id = LBAddress.KLCity_id
				left join v_KLStreet LBStreet on LBStreet.KLStreet_id = LBAddress.KLStreet_id
				left join v_KLSocr LBsocrStreet on LBStreet.KLSocr_id = LBsocrStreet.KLSocr_id
			";
		}
		$selectString = "
			ET.EmergencyTeam_id as \"EmergencyTeam_id\",
			ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
			ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
			ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
			ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
			ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
			ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
			ET.LpuBuilding_id as \"LpuBuilding_id\",
			ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
			ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
			ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
			to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
			to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
			ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
			datediff('mi', ETLastStatusDateTime.EmergencyTeamStatusHistory_insDT, tzgetdate()) as \"lastChangedStatusTime\",
			case when datediff('mi', tzgetdate(), ETD.EmergencyTeamDuty_DTFinish ) < 0 and ETS.EmergencyTeamStatus_Code in (1, 2, 3, 17, 48)
				then 2
				else 1
			end
			as \"isOverTime\",
			case when tzgetdate() BETWEEN ETD.EmergencyTeamDuty_factToWorkDT and ETD.EmergencyTeamDuty_DTFinish
			    then 0
			    else 1
			end as \"EmergencyTeamDuty_isNotFact\",
			ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
			ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
			CC.CmpCallCard_id as \"CmpCallCard_id\",
			CC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			coalesce(CC.Lpu_hid,lsL.Lpu_id) as \"Lpu_hid\",
			coalesce(lpuHid.Lpu_Nick,lsL.Lpu_Nick) as \"LpuHid_Nick\",
			CC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
			case when coalesce(ET.EmergencyTeam_isOnline, 1) = 2 then 'online' else 'offline' end as \"EmergencyTeam_isOnline\",
			case when coalesce(ET.EmergencyTeam_HeadShift2, 0) != 0 then 1 else 0 end +
			case when coalesce(ET.EmergencyTeam_Assistant1, 0) != 0 then 1 else 0 end +
			case when coalesce(ET.EmergencyTeam_Assistant2, 0) != 0 then 1 else 0 end
			as \"medPersonCount\",
			ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
			case
				when ETS.EmergencyTeamStatus_Code in (3, 14) then 'red'
				when ETS.EmergencyTeamStatus_Code in (5, 13, 21, 47) then 'blue'
				when ETS.EmergencyTeamStatus_Code in (8, 23) then 'green'
				else 'black'
			end as \"EmergencyTeamStatus_Color\",
			MP.Person_Fin as \"Person_Fin\",
			{$checkInAddress}
			case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as \"EmergencyTeamBuildingName\",
			coalesce(GTR.{$GTR["GeoserviceTransport_id_field"]}, MPC.MedProductCard_Glonass) as \"GeoserviceTransport_id\"
		";
		$fromString = "
			v_EmergencyTeam as ET
			left join v_EmergencyTeamStatus AS ETS  on ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
			left join v_EmergencyTeamDuty AS ETD on ETD.EmergencyTeam_id=ET.EmergencyTeam_id
			left join v_MedPersonal as MP on MP.MedPersonal_id=ET.EmergencyTeam_HeadShift
			left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
			left join lateral (
				select C2.* 
				from v_CmpCallCard as C2
				where C2.EmergencyTeam_id = ET.EmergencyTeam_id 
				  and C2.CmpCallCardStatusType_id = 2
				order by C2.CmpCallCard_updDT desc
				limit 1
			) as CC on true
			left join lateral (
				select *
				from v_EmergencyTeamStatusHistory as EmergTeamStatHistory
				where EmergTeamStatHistory.EmergencyTeam_id = ET.EmergencyTeam_id					
				order by EmergTeamStatHistory.EmergencyTeamStatusHistory_insDT desc
			    limit 1
			) as ETLastStatusDateTime on true
			left join {$GTR["GeoserviceTransportRel_object"]} as GTR on GTR.{$GTR["EmergencyTeam_id_field"]}=ET.EmergencyTeam_id
			left join passport.v_MedProductCard MPC on MPC.MedProductCard_id = ET.MedProductCard_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = ET.LpuBuilding_id
			left join v_Lpu lpuHid on CC.Lpu_hid = lpuHid.Lpu_id
			left join v_Lpu lsL on lsL.Lpu_id = LB.Lpu_id
			{$lastCallOnTeamApply}
		";
		$whereString = (count($where) != 0)? "where ".implode(" and ", $where) : "";
		$orderByString = "EmergencyTeam_Num";
		$query = "
			select distinct
				{$selectString}
			from {$fromString}
			{$whereString}
			order by {$orderByString}
		";
		if (isset($_GET["dbg"]) && $_GET["dbg"] == "1") {
			var_dump(getDebugSQL($query, $params));
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamOperEnvForInteractiveMap(EmergencyTeam_model4E $callObject, $data)
	{
		$where = [];
		$params = [];
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $callObject->_defineGeoserviceTransportRelQueryParams($data);
		// Вышел на смену
		$where[] = "ETD.EmergencyTeamDuty_isComesToWork = 2";
		$where[] = "(coalesce(ETD.EmergencyTeamDuty_factToWorkDT, tzgetdate()) <= tzgetdate() and coalesce(ETD.EmergencyTeamDuty_factEndWorkDT, tzgetdate()) >= tzgetdate())";
		if (!empty($data["EmergencyTeamStatus_id"])) {
			$params["EmergencyTeamStatus_id"] = $data["EmergencyTeamStatus_id"];
			$where[] = "ET.EmergencyTeamStatus_id = :EmergencyTeamStatus_id";
		}
		// здесь мы получаем список доступных подстанций для работы
		$where[] = $callObject->getNestedLpuBuildingsForRequests($data);
		$whereString = (count($where) != 0)? "where ".implode(" and ", $where) : "";
		$select = "GTR.{$GTR["GeoserviceTransport_id_field"]} as \"GeoserviceTransport_id\"";
		$join = "left join {$GTR["GeoserviceTransportRel_object"]} as GTR on GTR.{$GTR["EmergencyTeam_id_field"]} = ET.EmergencyTeam_id";

		$selectString = "
			ET.EmergencyTeam_id as \"EmergencyTeam_id\",
			ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
			ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
			ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
			ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
			to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
			to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
			ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
			datediff('mi', ETLastStatusDateTime.EmergencyTeamStatusHistory_insDT, tzgetdate()) as \"lastChangedStatusTime\",
			case when datediff('mi', tzgetdate(), ETD.EmergencyTeamDuty_DTFinish ) < 0 and ETS.EmergencyTeamStatus_Code in (1, 2, 3, 17, 48)
				then 2
				else 1
			and
			as \"isOverTime\", 
			ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
			ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
			CC.CmpCallCard_id as \"CmpCallCard_id\",
			CC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			coalesce(CC.Lpu_hid, lsL.Lpu_id) as \"Lpu_hid\",
			coalesce(lpuHid.Lpu_Nick,lsL.Lpu_Nick) as \"LpuHid_Nick\",
			lpuHid.PAddress_Address as \"LpuHid_PAddress\",
			CC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
			case when coalesce(ET.EmergencyTeam_isOnline, 1) = 2 then 'online' else 'offline' end as \"EmergencyTeam_isOnline\",
			case when coalesce(ET.EmergencyTeam_HeadShift2, 0) != 0 then 1 else 0 end +
			case when coalesce(ET.EmergencyTeam_Assistant1, 0) != 0 then 1 else 0 end +
			case when coalesce(ET.EmergencyTeam_Assistant2, 0) != 0 then 1 else 0 end
			as \"medPersonCount\",
			ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
			case
				when ETS.EmergencyTeamStatus_Code in (3, 14) then 'red'
				when ETS.EmergencyTeamStatus_Code in (5, 13, 21, 47) then 'blue'
				when ETS.EmergencyTeamStatus_Code in (8, 23) then 'green'
				else 'black'
			end as \"EmergencyTeamStatus_Color\",
			MP.Person_Fin as \"Person_Fin\",
			case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as \"EmergencyTeamBuildingName\",
			{$select}
		";
		$fromString = "
			v_EmergencyTeam as ET
			left join v_EmergencyTeamStatus AS ETS on ETS.EmergencyTeamStatus_id = ET.EmergencyTeamStatus_id
			left join v_EmergencyTeamDuty AS ETD on ETD.EmergencyTeam_id = ET.EmergencyTeam_id
			left join v_MedPersonal as MP on MP.MedPersonal_id = ET.EmergencyTeam_HeadShift
			left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
			left join lateral (
				select C2.* 
				from v_CmpCallCard as C2
				where C2.EmergencyTeam_id = ET.EmergencyTeam_id 
				  and C2.CmpCallCardStatusType_id = 2
				order by C2.CmpCallCard_updDT desc
				limit 1
			) as CC on true
			left join lateral (
				select *
				from v_EmergencyTeamStatusHistory as EmergTeamStatHistory
				where EmergTeamStatHistory.EmergencyTeam_id = ET.EmergencyTeam_id					
				order by EmergTeamStatHistory.EmergencyTeamStatusHistory_insDT desc
			    limit 1
			) as ETLastStatusDateTime on true
			left join v_LpuBuilding LB on LB.LpuBuilding_id = ET.LpuBuilding_id
			left join v_Lpu lpuHid on CC.Lpu_hid = lpuHid.Lpu_id
			left join v_Lpu lsL on lsL.Lpu_id = LB.Lpu_id
			{$join}
		";
		$orderByString = "EmergencyTeam_Num";
		$query = "
			select distinct
				{$selectString}
			from {$fromString}
			{$whereString}
			order by {$orderByString}
		";
		if (isset($_GET["dbg"]) && $_GET["dbg"] == "1") {
			print_r(getDebugSQL($query, $params));
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadEmergencyTeamsARMCenterDisaster(EmergencyTeam_model4E $callObject, $data)
	{
		$params = [];
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $callObject->_defineGeoserviceTransportRelQueryParams($data);
		$where = [
			"coalesce(ET.EmergencyTeam_isTemplate, 1) = 1",
			"ETD.EmergencyTeamDuty_isComesToWork = 2",
			"tzgetdate() between ETD.EmergencyTeamDuty_DTStart and ETD.EmergencyTeamDuty_DTFinish",
			"ETD.EmergencyTeamDuty_factToWorkDT is not null",
			"LB.LpuBuildingType_id = 27"
		];
		if (!empty ($data["Lpu_ids"])) {
			$where[] = "L.Lpu_id in ({$data["Lpu_ids"]})";
		} else {
			$Lpu_ids = $callObject->getSelectedLpuId();
			if (!$Lpu_ids) {
				return false;
			}
			$Lpu_idsString = implode(",", $Lpu_ids);
			$where[] = "L.Lpu_id in ({$Lpu_idsString})";
		}
		$whereString = (count($where) != 0)? "where ".implode(" and ", $where) : "";
		$select = "COALESCE(GTR.{$GTR["GeoserviceTransport_id_field"]}, MPC.MedProductCard_Glonass) as \"GeoserviceTransport_id\",";
		$join = "left join {$GTR["GeoserviceTransportRel_object"]} as GTR on GTR.{$GTR["EmergencyTeam_id_field"]} = ET.EmergencyTeam_id";
		
		$selectString = "
			ET.EmergencyTeam_id as \"EmergencyTeam_id\",
			ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
			ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
			ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
			ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
			ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
			ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
			ET.LpuBuilding_id as \"LpuBuilding_id\",
			L.Lpu_Nick as \"Lpu_Nick\",
			ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
			ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
			ET.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
			to_char(GETDATE() - LAstStatus.EmergencyTeamStatusHistory_insDT, '{$callObject->dateTimeForm120}') as \"statusTime\",
			to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
			to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
			ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
			ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
			ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
			CC.CmpCallCard_id as \"CmpCallCard_id\",
			CC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			CC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
               CC.Person_Age as \"Person_Age\",
               coalesce(PS.Person_Surname, CC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CC.Person_FirName) = 'null' then '' else CC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CC.Person_SecName) = 'null' then '' else CC.Person_SecName end, '') as \"Person_FIO\",
               rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
			case when coalesce(ET.EmergencyTeam_isOnline, 1) = 2 then 'online' else 'offline' end as \"EmergencyTeam_isOnline\",
			{$select}
			case when coalesce(ET.EmergencyTeam_HeadShift2, 0) != 0 then 1 else 0 end +
			case when coalesce(ET.EmergencyTeam_Assistant1, 0) != 0 then 1 else 0 end +
			case when coalesce(ET.EmergencyTeam_Assistant2, 0) != 0 then 1 else 0 end
			as \"medPersonCount\",
			L.Lpu_Nick||' / '||coalesce(LB.LpuBuilding_Nick, LB.LpuBuilding_Name)||' / '||ET.EmergencyTeam_Num as \"EmergencyTeamNum\",
			ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
			case
				when ETS.EmergencyTeamStatus_Code in (13, 21, 36) then 'green'
				when ETS.EmergencyTeamStatus_Code in (8, 9, 23) then 'gray'
				else 'black'
			end as \"EmergencyTeamStatus_Color\",
			case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as \"EmergencyTeamBuildingName\",
			(case when (ET.EmergencyTeam_HeadShift is not null and ET.EmergencyTeam_HeadShift != 0) then 1 else 0 end) + (case when (ET.EmergencyTeam_HeadShift2 is not null and ET.EmergencyTeam_HeadShift2 != 0) then 1 else 0 end) as \"EmergencyTeam_HeadShiftCount\",
               (case when (ET.EmergencyTeam_Assistant1 is not null and ET.EmergencyTeam_Assistant1 != 0) then 1 else 0 end) + (case when (ET.EmergencyTeam_Assistant2 is not null and ET.EmergencyTeam_Assistant2 != 0) then 1 else 0 end) as \"EmergencyTeam_AssistantCount\",
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
				case when Town.KLTown_FullName is not null then
				case when City.KLCity_Name is not null then ', ' else '' end||
				coalesce(lower(Town.KLSocr_Nick)||'. ', '')||Town.KLTown_Name else '' end||
				case when Street.KLStreet_FullName is not null then ', '||lower(Street.KLSocr_Nick)||'. '||Street.KLStreet_Name else '' end||
				case when CC.CmpCallCard_Dom is not null then ', д.'||CC.CmpCallCard_Dom else '' end||
				case when CC.CmpCallCard_Korp is not null then ', к.'||CC.CmpCallCard_Korp else '' end||
				case when CC.CmpCallCard_Kvar is not null then ', кв.'||CC.CmpCallCard_Kvar else '' end||
				case when CC.CmpCallCard_Room is not null then ', ком. '||CC.CmpCallCard_Room else '' end||
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else ''
			end as \"Address_Name\",
			case when coalesce(CC.Person_Age, 0) = 0 and coalesce(CC.Person_BirthDay, PS.Person_BirthDay, 0) != 0 then
				case when datediff('m', coalesce(CC.Person_BirthDay, PS.Person_BirthDay), getdate()) > 12
				    then datediff('yy', coalesce(CC.Person_BirthDay, PS.Person_BirthDay), getdate())::varchar||' лет'
				else
					case when datediff('d', coalesce(CC.Person_BirthDay, PS.Person_BirthDay), tzgetdate()) <= 30
					    then datediff('d', coalesce(CC.Person_BirthDay, PS.Person_BirthDay), getdate())::varchar||' дн. '
						else datediff('m', coalesce(CC.Person_BirthDay, PS.Person_BirthDay), getdate())::varchar||' мес.'
					end
				end
			else
			 	case when coalesce(CC.Person_Age,0) = 0
			 	    then ''
					else CC.Person_Age::varchar||' лет'
				end
			end as \"personAgeText\",
			CC.CmpCallCard_isExtra as \"CmpCallCard_isExtra\",
			case when EmergencyTeamStatus_Code = 3
				then lpuHid.Lpu_Nick
				else ''
			end as \"CCCLpu_Nick\"
		";
		$fromString = "
			v_EmergencyTeam as ET
			inner join v_LpuBuilding LB on LB.LpuBuilding_id = ET.LpuBuilding_id
			inner join v_Lpu L on L.Lpu_id = LB.Lpu_id
			left join v_EmergencyTeamStatus as ETS on ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id
			left join lateral (
				select *
				from v_EmergencyTeamStatusHistory
				where EmergencyTeam_id = ET.EmergencyTeam_id					
				order by EmergencyTeamStatusHistory_insDT desc
			    limit 1
			) as LastStatus on true
			left join v_EmergencyTeamDuty as ETD on ETD.EmergencyTeam_id=ET.EmergencyTeam_id
			left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
			left join lateral (
				select C2.* 
				from v_CmpCallCard as C2
				where C2.EmergencyTeam_id = ET.EmergencyTeam_id 
				  and C2.CmpCallCardStatusType_id = 2
				order by C2.CmpCallCard_updDT desc
				limit 1
			) as CC on true
			left join v_Lpu CCLpu on CCLpu.Lpu_id = CC.Lpu_id
			left join v_Lpu lpuHid on lpuHid.Lpu_id = CC.Lpu_hid
			left join v_CmpReason CR on CR.CmpReason_id = CC.CmpReason_id
			left join v_PersonState PS on PS.Person_id = CC.Person_id
			left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CC.UnformalizedAddressDirectory_id
			left join v_KLCity City on City.KLCity_id = CC.KLCity_id
			left join v_KLTown Town on Town.KLTown_id = CC.KLTown_id
			left join v_KLStreet Street on Street.KLStreet_id = CC.KLStreet_id
			left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
			left join passport.v_MedProductCard MPC on MPC.MedProductCard_id = ET.MedProductCard_id
			{$join}
		";
		$orderByString = "EmergencyTeam_Num";
		$query = "
			select {$selectString}
			from {$fromString}
			{$whereString}
			order by {$orderByString}
		";
		if (isset($_GET["dbg"]) && $_GET["dbg"] == "1") {
			var_dump(getDebugSQL($query, $params));
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadEmergencyTeamStatuses(EmergencyTeam_model4E $callObject, $data)
	{
		$queryParams = [];
		$where = [
			"(ETS.EmergencyTeamStatus_begDT is null or ETS.EmergencyTeamStatus_begDT <= tzgetdate())",
			"(ETS.EmergencyTeamStatus_endDT is null or ETS.EmergencyTeamStatus_endDT >= tzgetdate())"
		];
		if (getRegionNick() != "perm") {
			$whereString = "where " . implode(" and ", $where);
			$query = "
				select
					ETS.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
					ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
					ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
					to_char(ETS.EmergencyTeamStatus_begDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamStatus_begDT\",
					to_char(ETS.EmergencyTeamStatus_endDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamStatus_endDT\"
				from v_EmergencyTeamStatus as ETS
				{$whereString}
			";
			/**@var CI_DB_result $result */
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result("array");
		}
		if (!empty($data["EmergencyTeamStatus_pid"])) {
			$where[] = "ETSM.EmergencyTeamStatus_pid = :EmergencyTeamStatus_pid";
			$queryParams["EmergencyTeamStatus_pid"] = $data["EmergencyTeamStatus_pid"];
		}
		$whereString = "where " . implode(" and ", $where);
		$query = "
			SELECT
				ETS.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
                ETSM.EmergencyTeamStatus_pid as \"ppp\"
			from
            	v_EmergencyTeamStatusModel as ETSM
                left join v_EmergencyTeamStatus as ETS on ETS.EmergencyTeamStatus_id = ETSM.EmergencyTeamStatus_id
			{$whereString}
		";
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadEmergencyTeamStatusesHistory(EmergencyTeam_model4E $callObject, $data)
	{
		$queryParams = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
		$query = "
			select
				ETSH.EmergencyTeamStatusHistory_id as \"EmergencyTeamStatusHistory_id\",
				ETSH.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
				to_char(ETSH.EmergencyTeamStatusHistory_insDT, '{$callObject->dateTimeForm120}') as \"EmergencyTeamStatusHistory_insDT\",
				ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\",
				ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\",
				ССС.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
			from
				v_EmergencyTeamStatusHistory as ETSH
				left join v_EmergencyTeamStatus ETS on ETS.EmergencyTeamStatus_id=ETSH.EmergencyTeamStatus_id
				left join v_CmpCallCard ССС on ETSH.CmpCallCard_id=ССС.CmpCallCard_id
			where ETSH.EmergencyTeam_id = :EmergencyTeam_id
			order by ETSH.EmergencyTeamStatusHistory_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamTemplateList(EmergencyTeam_model4E $callObject, $data)
	{
		$GTR = $callObject->_defineGeoserviceTransportRelQueryParams($data);
		$callObject->load->model('CmpCallCard_model4E', 'cardModel');
		$where = [
			$callObject->getNestedLpuBuildingsForRequests($data),
			"coalesce(ET.EmergencyTeam_isTemplate, 1) = 2"
		];
		$select = "GTR.{$GTR["GeoserviceTransport_id_field"]} as \"GeoserviceTransport_id\",";
		$join = "left join {$GTR["GeoserviceTransportRel_object"]} GTR   GTR.{$GTR["EmergencyTeam_id_field"]} = ET.EmergencyTeam_id";
		
		$selectString = "
			ET.EmergencyTeam_id as \"EmergencyTeam_id\",
			ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
			ET.EmergencyTeam_PortRadioNum as \"EmergencyTeam_PortRadioNum\",
			ET.EmergencyTeam_GpsNum as \"EmergencyTeam_GpsNum\",
			ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
			ET.EmergencyTeam_CarBrand as \"EmergencyTeam_CarBrand\",
			ET.EmergencyTeam_CarModel as \"EmergencyTeam_CarModel\",
			LB.LpuBuilding_Nick as \"LpuBuilding_Nick\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			ET.LpuBuilding_id as \"LpuBuilding_id\",
			ET.Lpu_id as \"Lpu_id\",
			ET.CMPTabletPC_id as \"CMPTabletPC_id\",
			ET.EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\",
			ET.EmergencyTeam_HeadShiftWorkPlace as \"EmergencyTeam_HeadShiftWorkPlace\",
			ET.EmergencyTeam_HeadShift2 as \"EmergencyTeam_HeadShift2\",
			ET.EmergencyTeam_HeadShift2WorkPlace as \"EmergencyTeam_HeadShift2WorkPlace\",
			ET.EmergencyTeam_Assistant1 as \"EmergencyTeam_Assistant1\",
			ET.EmergencyTeam_Assistant1WorkPlace as \"EmergencyTeam_Assistant1WorkPlace\",
			ET.EmergencyTeam_Assistant2 as \"EmergencyTeam_Assistant2\",
			ET.EmergencyTeam_Driver as \"EmergencyTeam_Driver\",
			ET.EmergencyTeam_DriverWorkPlace as \"EmergencyTeam_DriverWorkPlace\",
			ET.EmergencyTeam_Driver2 as \"EmergencyTeam_Driver2\",
			ET.EmergencyTeam_DutyTime as \"EmergencyTeam_DutyTime\",
			ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\",
			ET.MedProductCard_id as \"MedProductCard_id\",
			MPCl.MedProductClass_Name as \"MedProductClass_Name\",
			ETSpec.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
			ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
			ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
			ET.EmergencyTeam_TemplateName as \"EmergencyTeam_TemplateName\",
			to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
			to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
			MPh1.Person_Fin as \"EmergencyTeam_HeadShiftFIO\",
			MPh2.Person_Fin as \"EmergencyTeam_HeadShift2FIO\",
			MPd1.Person_Fin as \"EmergencyTeam_DriverFIO\",
			MPd2.Person_Fin as \"EmergencyTeam_Driver2FIO\",
			MPa1.Person_Fin as \"EmergencyTeam_Assistant1FIO\",
			MPa2.Person_Fin as \"EmergencyTeam_Assistant2FIO\",
			{$select}
			ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\"
		";
		$fromString = "
			v_EmergencyTeam ET
			left join v_MedPersonal MPh1 on MPh1.MedPersonal_id = ET.EmergencyTeam_HeadShift
			left join v_MedPersonal MPh2 on MPh2.MedPersonal_id = ET.EmergencyTeam_HeadShift2
			left join v_MedPersonal MPd1 on MPd1.MedPersonal_id = ET.EmergencyTeam_Driver
			left join v_MedPersonal MPd2 on MPd2.MedPersonal_id = ET.EmergencyTeam_Driver2
			left join v_MedPersonal MPa1 on MPa1.MedPersonal_id = ET.EmergencyTeam_Assistant1
			left join v_MedPersonal MPa2 on MPa2.MedPersonal_id = ET.EmergencyTeam_Assistant2				
			left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
			left join v_LpuBuilding LB on ET.LpuBuilding_id = LB.LpuBuilding_id
			left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			left join passport.v_MedProductCard MPC  on MPC.MedProductCard_id = ET.MedProductCard_id
			left join passport.v_MedProductClass MPCl  on MPCl.MedProductClass_id = MPC.MedProductClass_id
			{$join}
		";
		$whereString = (count($where) != 0)? "where ".implode(" and ", $where) : "";
		$query = "
			select distinct
				{$selectString}
			from {$fromString}
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool|false
	 */
	public static function loadUnfinishedEmergencyTeamList(EmergencyTeam_model4E $callObject, $data)
	{
		//Получаем идентификатор отделения
		$callObject->load->model("CmpCallCard_model4E", "cccmodel");
		$LpuBuilding_result = $callObject->cccmodel->getLpuBuildingBySessionData($data);
		if (!$callObject->isSuccessful($LpuBuilding_result) || !isset($LpuBuilding_result[0])) {
			return $LpuBuilding_result;
		}
		$data = array_merge($data, $LpuBuilding_result[0]);
		//Пока параметры пустые и загружаем все все все бригады с незавершёнными сменами
		$rules = [
			["field" => "LpuBuilding_id", "label" => "Идентификатор подстанции", "rules" => "required", "type" => "int"],
			["field" => "EmergencyTeam_id", "label" => "Идентификатор подстанции", "rules" => "", "type" => "int", "default" => null]
		];
		$queryParams = $callObject->checkInputData($rules, $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$additionalWhereClause = "";
		if (!empty($queryParams["EmergencyTeam_id"])) {
			$additionalWhereClause = "OR ET.EmergencyTeam_id = :EmergencyTeam_id";
		}
		$query = "
			select distinct
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				ET.EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\",
				ETSpec.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\",
				ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				MPh1.Person_Fin as \"EmergencyTeam_HeadShiftFIO\",
				ETD.EmergencyTeamDuty_id as \"EmergencyTeamDuty_id\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTStart\",
				to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"EmergencyTeamDuty_DTFinish\",
				case when coalesce(ETD.EmergencyTeamDuty_isComesToWork, 1) = 1 then 'false' else 'true' end as \"locked\",
				case when coalesce(ETD.EmergencyTeamDuty_isClose, 1) = 1 then 'false' else 'true' end as \"closed\",
				ET.EmergencyTeam_Num||' '||ETSpec.EmergencyTeamSpec_Code||' '||coalesce(MPh1.Person_Fin, '') as \"EmergencyTeam_Name\"
			from
				v_EmergencyTeam ET
				left join v_MedPersonal MPh1 on MPh1.MedPersonal_id = ET.EmergencyTeam_HeadShift
				left join v_EmergencyTeamSpec as ETSpec on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			where (ET.LpuBuilding_id = :LpuBuilding_id and coalesce(ET.EmergencyTeam_isTemplate, 1) = 1 and ETD.EmergencyTeamDuty_DTFinish > tzgetdate())
				{$additionalWhereClause}
		";
		return $callObject->queryResult($query, $queryParams);
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadEmergencyTeamVigils(EmergencyTeam_model4E $callObject, $data)
	{
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			throw new Exception("Не указан идентификатор наряда");
		}
		$query = "
			select distinct
				CETD.CmpEmTeamDuty_id as \"CmpEmTeamDuty_id\",
				CETD.EmergencyTeam_id as \"EmergencyTeam_id\",
				to_char(CETD.CmpEmTeamDuty_PlanBegDT, '{$callObject->dateTimeForm120}') as \"CmpEmTeamDuty_PlanBegDT\",
				to_char(CETD.CmpEmTeamDuty_PlanEndDT, '{$callObject->dateTimeForm120}') as \"CmpEmTeamDuty_PlanEndDT\",
				to_char(CETD.CmpEmTeamDuty_FactBegDT, '{$callObject->dateTimeForm120}') as \"CmpEmTeamDuty_FactBegDT\",
				to_char(CETD.CmpEmTeamDuty_FactEndDT, '{$callObject->dateTimeForm120}') as \"CmpEmTeamDuty_FactEndDT\",
				case when SRGN.KLSubRgn_FullName is not null then ''||SRGN.KLSubRgn_FullName else 'г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CETD.CmpEmTeamDuty_House is not null then ', д.'||CETD.CmpEmTeamDuty_House else '' end||
					case when CETD.CmpEmTeamDuty_Flat is not null then ', кв.'||CETD.CmpEmTeamDuty_Flat else '' end||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' 
				end as \"address_AddressText\",
				CETD.CmpEmTeamDuty_Description as \"CmpEmTeamDuty_Description\"
			from
				v_CmpEmTeamDuty as CETD
				left join v_KLRgn RGN on RGN.KLRgn_id = CETD.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CETD.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CETD.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CETD.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CETD.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CETD.UnformalizedAddressDirectory_id
			where CETD.EmergencyTeam_id = :EmergencyTeam_id
		";
		$queryParams = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param EmergencyTeam_model4E $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function loadSingleEmergencyTeamVigil(EmergencyTeam_model4E $callObject, $data)
	{
		/**@var CI_DB_result $result */
		if (!array_key_exists("EmergencyTeam_id", $data) || !$data["EmergencyTeam_id"]) {
			throw new Exception("Не указан идентификатор наряда");
		}
		$queryParams = ["EmergencyTeam_id" => $data["EmergencyTeam_id"]];
		$where[] = "ET.EmergencyTeam_id = :EmergencyTeam_id";
		if (!empty($data["CmpEmTeamDuty_id"])) {
			$where[] = "CmpEmTeamDuty_id = :CmpEmTeamDuty_id";
			$queryParams["CmpEmTeamDuty_id"] = $data["CmpEmTeamDuty_id"];
		} else {
			//если дежурство на добавление, то возвращаем данные бригады
			$whereString = "where " . implode(" and ", $where);
			$selectString = "
				ET.EmergencyTeam_id as \"EmergencyTeam_id\", 
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				to_char(ETD.EmergencyTeamDuty_DTStart, '{$callObject->dateTimeForm120}') as \"CmpEmTeamDuty_PlanBegDT\",
				to_char(ETD.EmergencyTeamDuty_DTFinish, '{$callObject->dateTimeForm120}') as \"CmpEmTeamDuty_PlanEndDT\",
				to_char(ETD.EmergencyTeamDuty_factToWorkDT, '{$callObject->dateTimeForm120}') as \"CmpEmTeamDuty_FactBegDT\",
				to_char(ETD.EmergencyTeamDuty_factEndWorkDT, '{$callObject->dateTimeForm120}') as \"CmpEmTeamDuty_FactEndDT\"				
			";
			$fromString = "
				v_EmergencyTeam as ET
				left join v_EmergencyTeamDuty ETD on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			";
			$query = "
				select {$selectString}
				from {$fromString}
				{$whereString}
			";
			$result = $callObject->db->query($query, $queryParams);
			if (!is_object($result)) {
				return false;
			}
			return $result->result_array();
		}
		$whereString = "where " . implode(" and ", $where);
		$query = "
			select
				ET.EmergencyTeam_id as \"EmergencyTeam_id\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				CETD.CmpEmTeamDuty_id as \"CmpEmTeamDuty_id\",
				to_char(CETD.CmpEmTeamDuty_PlanBegDT, {$callObject->dateTimeForm120}) as \"CmpEmTeamDuty_PlanBegDT\",
				to_char(CETD.CmpEmTeamDuty_PlanEndDT, {$callObject->dateTimeForm120}) as \"CmpEmTeamDuty_PlanEndDT\",
				to_char(CETD.CmpEmTeamDuty_FactBegDT, {$callObject->dateTimeForm120}) as \"CmpEmTeamDuty_FactBegDT\",
				to_char(CETD.CmpEmTeamDuty_FactEndDT, {$callObject->dateTimeForm120}) as \"CmpEmTeamDuty_FactEndDT\",
				CETD.CmpEmTeamDuty_Description as \"CmpEmTeamDuty_Description\",
				CETD.KLRgn_id as \"KLRgn_id\",
				CETD.KLSubRgn_id as \"KLSubRgn_id\",
				CETD.KLCity_id as \"KLCity_id\",
				CETD.KLTown_id as \"KLTown_id\",
				CETD.KLStreet_id as \"KLStreet_id\",
				CETD.CmpEmTeamDuty_House as \"CmpEmTeamDuty_House\",
				CETD.CmpEmTeamDuty_Corpus as \"CmpEmTeamDuty_Corpus\",
				CETD.CmpEmTeamDuty_Flat as \"CmpEmTeamDuty_Flat\",
				CETD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
				case when SRGN.KLSubRgn_FullName is not null then ''||SRGN.KLSubRgn_FullName else 'г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CETD.CmpEmTeamDuty_House is not null then ', д.'||CETD.CmpEmTeamDuty_House else '' end||
					case when CETD.CmpEmTeamDuty_Flat is not null then ', кв.'||CETD.CmpEmTeamDuty_Flat else '' end||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' 
				end as \"address_AddressText\"
			from
				v_EmergencyTeam as ET 
            	left join v_CmpEmTeamDuty CETD on CETD.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CETD.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CETD.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CETD.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CETD.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CETD.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CETD.UnformalizedAddressDirectory_id
			{$whereString}
		";
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}
}