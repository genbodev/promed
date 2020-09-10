<?php

class CmpCallCard_model_load
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadUnformalizedAddressDirectory(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
				UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\",
				UAD.UnformalizedAddressDirectory_lat as \"UnformalizedAddressDirectory_lat\",
				UAD.UnformalizedAddressDirectory_lng as \"UnformalizedAddressDirectory_lng\",
				UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\",
				UAD.UnformalizedAddressDirectory_Corpus as \"UnformalizedAddressDirectory_Corpus\",
				UAD.KLRgn_id as \"KLRgn_id\",
				UAD.KLSubRgn_id as \"KLSubRgn_id\",
				UAD.KLCity_id as \"KLCity_id\",
				UAD.KLTown_id as \"KLTown_id\",
				UAD.KLStreet_id as \"KLStreet_id\",
			    coalesce(RGN.KLRgn_FullName, '')||
					case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when UAD.UnformalizedAddressDirectory_Corpus is not null then ', корп.'||UAD.UnformalizedAddressDirectory_Corpus else '' end||
					case when UAD.UnformalizedAddressDirectory_Dom is not null then ', д.'||UAD.UnformalizedAddressDirectory_Dom else ''
				end as \"UnformalizedAddressDirectory_Address\"
			from
				v_UnformalizedAddressDirectory UAD
				left join v_KLRgn RGN on RGN.KLRgn_id = UAD.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = UAD.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = UAD.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = UAD.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = UAD.KLStreet_id
			where UAD.Lpu_id = :Lpu_id
			order by UAD.UnformalizedAddressDirectory_Name
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $data);
		$result_count = $callObject->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		if (!is_object($result)) {
			return false;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadStreetsAndUnformalizedAddressDirectoryCombo(CmpCallCard_model $callObject, $data)
	{
		$UADFilter = "";
		$where = "";
		$params = ["Lpu_id" => $data["Lpu_id"]];
		if (!empty($data['town_id'])) {
			$params["town_id"] = $data["town_id"];
			$where = "and KLArea_id = :town_id";
		}
		if (!empty($data['StreetAndUnformalizedAddressDirectory_id'])) {
			$params["StreetAndUnformalizedAddressDirectory_id"] = $data["StreetAndUnformalizedAddressDirectory_id"];
			$UADFilter .= " and 'UA.'||UAD.UnformalizedAddressDirectory_id::varchar = :StreetAndUnformalizedAddressDirectory_id";
			$where .= " and 'ST.'||KLStreet.KLStreet_id::varchar = :StreetAndUnformalizedAddressDirectory_id";
		}
		$limit1 = empty($where) ? "limit 1" : "";
		$query = "
			select
				'UA.'||UAD.UnformalizedAddressDirectory_id::varchar as \"StreetAndUnformalizedAddressDirectory_id\",
				UAD.UnformalizedAddressDirectory_Name as \"StreetAndUnformalizedAddressDirectory_Name\",
				'СМП' as \"Socr_Nick\",
				UAD.UnformalizedAddressDirectory_lat::varchar as \"lat\",
				UAD.UnformalizedAddressDirectory_lng::varchar as \"lng\",
				UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
				to_number('') as \"KLStreet_id\"
			from v_UnformalizedAddressDirectory UAD
			where UAD.Lpu_id = :Lpu_id
			 {$UADFilter}
			union all
			select
				'ST.'||KLStreet.KLStreet_id::varchar as \"StreetAndUnformalizedAddressDirectory_id\",
            	rtrim(KLStreet.KLStreet_Name) as \"StreetAndUnformalizedAddressDirectory_Name\",
				KLSocr.KLSocr_Nick as \"Socr_Nick\",
				null as \"lat\",
				null as \"lng\",
				null as \"UnformalizedAddressDirectory_id\",
				KLStreet.KLStreet_id as \"KLStreet_id\"
			from
				KLStreet
				left join KLSocr on KLSocr.KLSocr_id = KLStreet.KLSocr_id
			where KLAdr_Actual = 0
			  {$where}
			{$limit1}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadPPDWorkPlace(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [
			"PPDL.Lpu_id = :Lpu_ppdid",
			"CCC.CmpCallCardStatusType_id <> 18",
			"CCC.CmpCallCardStatusType_id > 0"
		];
		$queryParams = ["Lpu_ppdid" => $data["Lpu_id"]];
		if (!empty($data["Search_SurName"])) {
			$filterArray[] = "coalesce(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams["Person_SurName"] = rtrim($data["Search_SurName"]) . "%";
		}
		if (!empty($data["Search_FirName"])) {
			$filterArray[] = "coalesce(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams["Person_FirName"] = rtrim($data["Search_FirName"]) . "%";
		}
		if (!empty($data["Search_SecName"])) {
			$filterArray[] = "coalesce(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams["Person_SecName"] = rtrim($data["Search_SecName"]) . "%";
		}
		if (!empty($data["Search_BirthDay"])) {
			$filterArray[] = "coalesce(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
			$queryParams["Person_BirthDay"] = $data["Search_BirthDay"];
		}
		if (!empty($data["CmpLpu_id"])) {
			$filterArray[] = "CCC.CmpLpu_id = :CmpLpu_id";
			$queryParams["CmpLpu_id"] = $data["CmpLpu_id"];
		}
		if (!empty($data["MedService_id"])) {
			$filterArray[] = "(MS.MedService_id = :MedService_id or MS.MedService_id is null)";
			$queryParams["MedService_id"] = $data["MedService_id"];
		}
		if (!empty($data["CmpCallCard_Ngod"])) {
			$filterArray[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filterArray[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		if (!empty($data["begDate"])) {
			$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate";
			$queryParams["begDate"] = $data["begDate"];
		}
		if (!empty($data["endDate"])) {
			$filterArray[] = "CCC.CmpCallCard_prmDT::date <= :endDate";
			$queryParams["endDate"] = $data["endDate"];
		}
		// Скрываем вызовы с поводом "Решение старшего врача"
		$query = "
			select CmpReason_id
			from v_CmpReason
			where CmpReason_Code in ('02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?','999')
		";
		$reason_array = $callObject->queryResult($query, []);
		if ($reason_array !== false && is_array($reason_array) && count($reason_array) > 0) {
			$reasons = [];
			foreach ($reason_array as $reason) {
				$reasons[] = $reason['CmpReason_id'];
			}
			if (count($reasons) > 0) {
				$reasonsString = implode(",", $reasons);
				$filterArray[] = "coalesce(CCC.CmpReason_id, 0) not in ({$reasonsString})";
			}
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			with cc as (
				select
					CCC.*,
					PPDL.Lpu_id as PPDL_Lpu_id
				from
					v_CmpCallCard CCC
					left join v_MedService MS on MS.MedService_id = CCC.MedService_id
					left join v_Lpu PPDL on PPDL.Lpu_id = coalesce(CCC.Lpu_ppdid, MS.Lpu_id)
					left join v_PersonState PS on PS.Person_id = CCC.Person_id
				{$whereString}
			)
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				P.Person_id as \"Person_id\",
				P.Person_IsUnknown as \"Person_IsUnknown\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
				coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
				coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
				CCC.pmUser_insID as \"pmUser_insID\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm120}') as \"CmpCallCard_prmDate\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				to_char(coalesce(CCC.Person_BirthDay, PS.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
				rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				rtrim(coalesce(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\",
				rtrim(coalesce(CD.CmpDiag_Name, '')) as \"CmpDiag_Name\",
				rtrim(coalesce(D.Diag_Name, '')) as \"StacDiag_Name\",
				CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
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
							case when socrStreet.KLSocr_Nick is not null
								then ', '||lower(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name
								else ', '||Street.KLStreet_FullName
							end
						else
							case when CCC.CmpCallCard_Ulic is not null
								then ', '||CmpCallCard_Ulic
								else ''
							end
					end||
					case when SecondStreet.KLStreet_FullName is not null
						then
							case when socrSecondStreet.KLSocr_Nick is not null
								then ', '||lower(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name
								else ', '||SecondStreet.KLStreet_FullName
							end
						else ''
					end||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
					case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end||
					case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else ''
				end as \"Adress_Name\",
				LR.LpuRegion_Name as \"LpuRegion_Name\",
				EPL.evn_id as \"EvnPL_id\",
				MedService_id as \"MedService_id\",
				to_char(EPL.EvnPL_setDT, '{$callObject->dateTimeForm113}') as \"EvnPL_setDT\",
				ToDT.PMUser_Name||to_char(ToDT.ToDT, '{$callObject->dateTimeForm104}')||' '||to_char(ToDT.ToDT, '{$callObject->dateTimeForm108}') as \"PPDUser_Name\",
				case when coalesce(CmpCallCard_IsOpen, 1) = 2 then
					case
						when CmpCallCard_IsReceivedInPPD = 2 then
							case
								when evn_id is not null then 6
								when CCC.CmpCallCardStatusType_id in (1, 2) then CCC.CmpCallCardStatusType_id + 3
								when CCC.CmpCallCardStatusType_id = 4 then 6
								else 7
							end
					else
						case
							when evn_id is not null then 3
							when CCC.CmpCallCardStatusType_id in (1, 2) then CCC.CmpCallCardStatusType_id
							when CCC.CmpCallCardStatusType_id = 3 then 8
							when CCC.CmpCallCardStatusType_id = 4 then 3
							when CCC.CmpCallCardStatusType_id in (16, 18) then 9
							when CCC.CmpCallCardStatusType_id = 20 then 1
							else 7
						end
					end
				else 7 end as \"CmpGroup_id\",
				case when (CCC.CmpCallCardStatusType_id in (3, 5, 6, 7, 8)) then
					case when CCC.CmpCallCardStatusType_id = 3 then
						case when CCCS.CmpMoveFromNmpReason_id > 0
							then 'Отклонено: '||CMFNR.CmpMoveFromNmpReason_Name
							else 'Отклонено: '||CCC.CmpCallCardStatus_Comment
						end
					else '' end||
					case when (coalesce(CmpCallCard_IsOpen, 1) = 1 OR (CCC.CmpCallCardStatusType_id in (5, 6, 7, 8))) then
						case when CCCS.CmpMoveFromNmpReason_id > 0 then 'Отказ: '||CMFNR.CmpMoveFromNmpReason_Name
							else 'Отказ: '||CCC.CmpCallCardStatus_Comment end
					else '' end
				else
					case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end||
					case when RES.CmpPPDResult_Name is not null then '<br />Результат: '||RES.CmpPPDResult_Name else '' end||
					case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
				end as \"PPDResult\",
				case when (CCC.CmpCallCardStatusType_id = 4) then
					case when coalesce(EPL.EvnPL_setDT, -1) = -1
						then to_char(CmpCallCard_updDT, '{$callObject->dateTimeForm120}')
						else to_char(EPL.EvnPL_setDT, '{$callObject->dateTimeForm120}')
					end
				end as \"ServeDT\",
				coalesce(MSF.Person_Fio,'') as \"MedStaffFact_FIO\"
			from
				cc CCC
				left join v_Person P on P.Person_id = CCC.Person_id
				left join v_PersonState PS on PS.Person_id = P.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L on L.Lpu_id = CL.Lpu_id
				left join CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D on D.Diag_id = CCC.Diag_sid
				left join CmpPPDResult RES on RES.CmpPPDResult_id = CCC.CmpPPDResult_id
				left join lateral (
					select
						e1.*,
						e2.Evn_setDT as EvnPL_setDT
                    from
                        EvnPL e1
                        inner join Evn e2 on e2.Evn_id = e1.evn_id and e2.Evn_deleted = 1
                    where e1.CmpCallCard_id = CCC.CmpCallCard_id
                    limit 1
				) as EPL on true
				left join v_Diag EPLD on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT on DT.DirectType_id = EPL.DirectType_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
				left join v_KLStreet SecondStreet on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				left join v_KLSocr socrSecondStreet on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				left join v_KLSubRgn SRGNTown on SRGNTown.KLSubRgn_id = CCC.KLTown_id
				left join v_KLSubRgn SRGNCity on SRGNCity.KLSubRgn_id = CCC.KLCity_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join CmpCallCardStatus CCCS on CCCS.CmpCallCardStatus_id = CCC.CmpCallCardStatus_id
				left join v_MedPersonal MSF on MSF.MedPersonal_id=EPL.MedPersonal_id and MSF.Lpu_id = CCC.Lpu_ppdid
				left join v_CmpMoveFromNmpReason CMFNR ON CCCS.CmpMoveFromNmpReason_id = CMFNR.CmpMoveFromNmpReason_id
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
				left join lateral (
					select LpuRegion_id
					from v_LpuRegionStreet
					where KLCountry_id = 643
					  and KLRGN_id = CCC.KLRgn_id
					  and coalesce(KLSubRGN_id, '') = coalesce(CCC.KLSubRgn_id, '')
					  and coalesce(KLCity_id, '') = coalesce(CCC.KLCity_id, '')
					  and coalesce(KLTown_id, '') = coalesce(CCC.KLTown_id, '')
					  and KLStreet_id = CCC.KLStreet_id
				) as LRS on true
				left join v_LpuRegion LR on LR.LpuRegion_id = LRS.LpuRegion_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$val = $result->result("array");
		return [
			"data" => $val,
			"totalCount" => count($val)
		];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadLpuOperEnv(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [
			"CCC.Lpu_ppdid = :Lpu_ppdid",
			"CCC.CmpCallCard_prmDT::date >= :begDate",
			"CCC.CmpCallCard_prmDT::date <= :endDate",
			"((CCC.CmpCallCard_IsReceivedInPPD = 2 and CCC.CmpCallCardStatusType_id in (1, 2, 4)) or (CCC.CmpCallCard_IsReceivedInPPD != 2 and CCC.CmpCallCardStatusType_id in (1, 2, 4)))"
		];
		$queryParams = [
			"Lpu_ppdid" => $data["Lpu_ppdid"],
			"begDate" => date("Y-m-d", time() - 86400),
			"endDate" => date("Y-m-d")
		];
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				CCC.pmUser_insID as \"pmUser_insID\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
				rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				rtrim(coalesce(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\",
				rtrim(coalesce(CD.CmpDiag_Name, '')) as \"CmpDiag_Name\",
				rtrim(coalesce(D.Diag_Name, '')) as \"StacDiag_Name\",
				CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
				LR.LpuRegion_Name as \"LpuRegion_Name\",
				EPL.EvnPL_id as \"EvnPL_id\",
				to_char(ServeDT.ServeDT, '{$callObject->dateTimeForm113}') as \"ServeDT\",
				to_char(EPL.EvnPL_setDT, '{$callObject->dateTimeForm113}') as \"EvnPL_setDT\",
				case when CCC.CmpCallCardStatusType_id in (2, 4) then ToDT.PMUser_Name||to_char(ToDT.ToDT, '{$callObject->dateTimeForm104}') else '' end as \"PPDUser_Name\",
				case when coalesce(CmpCallCard_IsOpen, 1) = 2 then
					case when CmpCallCard_IsReceivedInPPD = 2 then
						case when EvnPL_id is not null then 6
							when CmpCallCardStatusType_id in (1, 2) then CmpCallCardStatusType_id + 3
							when CmpCallCardStatusType_id = 4 then 6
							else 7 end
					else
						case when EvnPL_id is not null then 3
							when CmpCallCardStatusType_id in (1, 2) then CmpCallCardStatusType_id
							when CmpCallCardStatusType_id = 4 then 3
							else 7 end
					end
				else 7 end as \"CmpGroup_id\",
				coalesce(MSF.Person_Fio, '') as \"MedStaffFact_FIO\"
			from
				v_CmpCallCard CCC
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L on L.Lpu_id = CL.Lpu_id
				left join CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D on D.Diag_id = CCC.Diag_sid
				left join CmpPPDResult RES on RES.CmpPPDResult_id = CCC.CmpPPDResult_id
				left join lateral (
					select *
					from v_EvnPL AS t1
					where t1.CmpCallCard_id = CCC.CmpCallCard_id
					  and t1.Lpu_id = CCC.Lpu_ppdid
					  and CCC.Lpu_ppdid is not null
				    limit 1
				) as EPL on true
				left join v_Diag EPLD on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT on DT.DirectType_id = EPL.DirectType_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join v_MedPersonal MSF on MSF.MedPersonal_id=EPL.MedPersonal_id
				left join lateral (
					select CmpCallCardStatus_insDT as ServeDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 4
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ServeDT on true
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
				left join lateral (
					select LpuRegion_id
					from v_LpuRegionStreet
					where KLCountry_id = 643
					  and KLRGN_id = CCC.KLRgn_id
					  and coalesce(KLSubRGN_id, '') = coalesce(CCC.KLSubRgn_id, '')
					  and coalesce(KLCity_id, '') = coalesce(CCC.KLCity_id, '')
					  and coalesce(KLTown_id, '') = coalesce(CCC.KLTown_id, '')
					  and KLStreet_id = CCC.KLStreet_id
				    limit 1
				) as LRS on true
				left join v_LpuRegion LR on LR.LpuRegion_id = LRS.LpuRegion_id
			{$whereString}
			order by CCC.CmpCallCard_Ngod DESC
		";
		$countQuery = getCountSQLPH($query);
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $countResult
		 */
		$countResult = $callObject->db->query($countQuery, $queryParams);
		if (!is_object($countResult)) {
			return false;
		}
		$countResult->result("array");
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadIllegalActCmpCards(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [];
		if (empty($data["Person_id"]) && empty($data["CmpCallCard_id"]) && empty($data["KLCity_id"]) && empty($data["KLTown_id"])) {
			return false;
		}
		if (!empty($data["CmpCallCard_id"])) {
			$filterArray[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
		} else {
			if (!empty($data["KLCity_id"]) || !empty($data["KLTown_id"])) {
				if (!empty($data["KLSubRgn_id"])) {
					$filterArray[] = "CCC.KLSubRgn_id = :KLSubRgn_id";
					$queryParams["KLSubRgn_id"] = $data["KLSubRgn_id"];
				}
				if (!empty($data["KLCity_id"])) {
					$filterArray[] = "CCC.KLCity_id = :KLCity_id";
					$queryParams["KLCity_id"] = $data["KLCity_id"];
				}
				if (!empty($data["KLTown_id"])) {
					$filterArray[] = "CCC.KLTown_id = :KLTown_id";
					$queryParams["KLTown_id"] = $data["KLTown_id"];
				}
				if (!empty($data["KLStreet_id"])) {
					$filterArray[] = "CCC.KLStreet_id = :KLStreet_id";
					$queryParams["KLStreet_id"] = $data["KLStreet_id"];
				}
				if (!empty($data["CmpCallCard_Dom"])) {
					$filterArray[] = "(CCC.CmpCallCard_Dom = :CmpCallCard_Dom or CCC.CmpCallCard_Dom is null)";
					$queryParams["CmpCallCard_Dom"] = $data["CmpCallCard_Dom"];
				}
				if (!empty($data["CmpCallCard_Kvar"])) {
					$filterArray[] = "CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
					$queryParams["CmpCallCard_Kvar"] = $data["CmpCallCard_Kvar"];
				} else {
					$filterArray[] = "CCC.CmpCallCard_Kvar is null";
				}
			}
			if (!empty($data["Person_id"])) {
				$filterArray[] = "CCC.Person_id = :Person_id";
				$queryParams["Person_id"] = $data["Person_id"];
			}
			if (!empty($data["CmpCallCard_prmDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date = :CmpCallCard_prmDate";
				$queryParams["CmpCallCard_prmDate"] = $data["CmpCallCard_prmDate"];
			}
		}
		$whereString = (count($filterArray) != 0) ? "where " . implode(" and ", $filterArray) : "";
		$query = "
			select
				CCC.CmpCallCard_id as \"CallCard_id\",
				coalesce(CCC.Person_SurName, '')||' '||coalesce(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
				rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				case when RGN.KLRgn_FullName is not null then RGN.KLRgn_FullName||', ' else '' end||
					case when SRGN.KLSubRgn_FullName is not null then SRGN.KLSubRgn_FullName||', ' else ' г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
					case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else ''
				end as \"Adress_Name\"
			from
				v_CmpCallCard CCC
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return ["data" => $result->result("array")];
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadLpuCmpUnits(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LS.LpuSection_Code as \"LpuSection_Code\"
			from
				v_LpuSection LS
				join LpuUnit LU on LU.LpuUnitType_id = 13 and LU.LpuUnit_id = LS.LpuUnit_id
				join LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id and LB.Lpu_id = :Lpu_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadLpuHomeVisit(CmpCallCard_model $callObject, $data)
	{
		$where = [
			"ds.DataStorage_Name = :DataStorage_Name",
			"ds.DataStorage_Value = 1",
			"ds.DataStorageGroup_SysNick = :DataStorageGroup_SysNick"
		];
		$params = [
			"DataStorage_Name" => "homevizit_isallowed",
			"DataStorageGroup_SysNick" => "homevizit"
		];
		if (!empty($data) && !empty($data["Lpu_id"])) {
			$params["Lpu_id"] = $data["Lpu_id"];
			$where[] = "lpu.Lpu_id = :Lpu_id";
		}
		$selectString = "
			lpu.Lpu_id as \"Lpu_id\",
			lpu.lpu_Nick as \"Lpu_Nick\"
		";
		$fromString = "
			v_Lpu lpu
			left join v_DataStorage ds on ds.Lpu_id = lpu.Lpu_id
		";
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$sql = "
			select {$selectString}
			from {$fromString}
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadMedStaffFactCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$params = [];
		if (!empty($data["MedStaffFact_id"])) {
			$where[] = "msf.MedStaffFact_id = :MedStaffFact_id";
			$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		} else {
			if (!empty($data["Lpu_id"])) {
				$where[] = "mp.Lpu_id = :Lpu_id";
				$params["Lpu_id"] = $data["Lpu_id"];
			}
			if (!empty($data["EmergencyTeam_id"])) {
				$where[] = "et.EmergencyTeam_id = :EmergencyTeam_id";
				$params["EmergencyTeam_id"] = $data["EmergencyTeam_id"];
			}
			if (!empty($data["query"])) {
				$where[] = "msf.Person_Fio like :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
			$where[] = "mp.Person_Fio is not null";
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and  ", $where) : "";
		$query = "
		    select distinct
                msf.MedStaffFact_id as \"MedStaffFact_id\",
                msf.Person_Fio as \"MedStaffFact_Name\",
                mp.Lpu_id as \"Lpu_id\",
                coalesce(mp.MedPersonal_Code, '') as \"MedPersonal_DloCode\",
                coalesce(mp.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
                ls.LpuSection_Name as \"LpuSection_Name\",
                p.name as \"PostMed_Name\",
                msf.MedStaffFact_Stavka::varchar as \"MedStaffFact_Stavka\",
                to_char(msf.WorkData_begDate, '{$callObject->dateTimeForm104}') as \"WorkData_begDate\",
                to_char(msf.WorkData_endDate, '{$callObject->dateTimeForm104}') as \"WorkData_endDate\"
            from
                v_EmergencyTeam et
                inner join v_MedPersonal mp on
                    mp.MedPersonal_id = et.EmergencyTeam_HeadShift or
                    mp.MedPersonal_id = et.EmergencyTeam_Assistant1 or
                    mp.MedPersonal_id = et.EmergencyTeam_Assistant2
                left join v_MedStaffFact msf on msf.MedPersonal_id = mp.MedPersonal_id
                left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
                left join persis.Post p on p.id = msf.Post_id
		    {$whereString}
		    order by msf.Person_Fio
			limit 250
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadLpuBuildingCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$params = [];
		if (!empty($data["LpuBuilding_id"])) {
			$where[] = "lb.LpuBuilding_id = :LpuBuilding_id";
			$params["LpuBuilding_id"] = $data["LpuBuilding_id"];
		} else {
			if (!empty($data["Lpu_id"])) {
				$where[] = "lb.Lpu_id = :Lpu_id";
				$params["Lpu_id"] = $data["Lpu_id"];
			}
			if (!empty($data["LpuBuildingType_id"])) {
				$where[] = "lb.LpuBuildingType_id = :LpuBuildingType_id";
				$params["LpuBuildingType_id"] = $data["LpuBuildingType_id"];
			}
			if (!empty($data["query"])) {
				$where[] = "lb.LpuBuilding_Name like :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
		    select
		        lb.LpuBuilding_id as \"LpuBuilding_id\",
		        lb.LpuBuilding_Name as \"LpuBuilding_Name\"
		    from v_LpuBuilding lb
		    {$whereString}
		    limit 250
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadStorageCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$params = [];
		if (!empty($data["Storage_id"])) {
			$where[] = "s.Storage_id = :Storage_id";
			$params["Storage_id"] = $data["Storage_id"];
		} else {
			if (!empty($data["LpuBuilding_id"])) {
				$where[] = "s.Storage_id in (
                    select ssl.Storage_id
                    from
                        v_StorageStructLevel ssl
                        left join v_Storage s on s.Storage_id = ssl.Storage_id or s.Storage_pid = ssl.Storage_id 
                        left join lateral (
							select i_ms.MedService_id
							from
								v_StorageStructLevel i_ssl
								left join v_MedService i_ms on i_ms.MedService_id = i_ssl.MedService_id			
								left join v_MedServiceType i_mst on i_mst.MedServiceType_id = i_ms.MedServiceType_id
							where i_ssl.Storage_id = s.Storage_id
							  and i_mst.MedServiceType_SysNick = 'merch'
							limit 1
						) as ms on true
                    where ssl.LpuBuilding_id = :LpuBuilding_id
                      and (ms.MedService_id is null or s.Storage_id = ssl.Storage_id)
                )";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			}
			if (!empty($data["query"])) {
				$where[] = "s.Storage_Name like :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
		    select
		        s.Storage_id as \"Storage_id\",
		        s.Storage_Name as \"Storage_Name\",
		        sz_cnt.StorageZone_Count as \"StorageZone_Count\"
		    from
                v_Storage s
                left join lateral (
                    select count(sz.StorageZone_id) as StorageZone_Count
                    from v_StorageZone sz
                    where sz.Storage_id = s.Storage_id
                ) as sz_cnt on true
		    {$whereString}
		    limit 250
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadMolCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$params = [];
		if (!empty($data["Mol_id"])) {
			$where[] = "m.Mol_id = :Mol_id";
			$params["Mol_id"] = $data["Mol_id"];
		} else {
			if (empty($data["Storage_id"])) {
				return false;
			}
			$where[] = "m.Storage_id = :Storage_id";
			$params["Storage_id"] = $data["Storage_id"];
			if (!empty($data["query"])) {
				$where[] = "mn.Mol_Name like :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
		    select
		        m.Mol_id as \"Mol_id\",
		        mn.Mol_Name as \"Mol_Name\"
		    from
                v_Mol m
                left join lateral (
                    select *
                    from v_MedPersonal i_mp
                    where i_mp.MedPersonal_id = m.MedPersonal_id
		    		limit 1
                ) as mp on true
                left join lateral (
                    select
                        (
                            case
                                when m.Person_id is not null
                                then coalesce(m.Person_SurName||' ', '')||coalesce(m.Person_FirName||' ', '')||coalesce(m.Person_SecName||' ', '')
                                else coalesce(mp.Person_SurName||' ', '')||coalesce(mp.Person_FirName||' ', '')||coalesce(mp.Person_SecName||' ', '')
                            end
                        ) as Mol_Name
                ) as mn on true
		    {$whereString}
		    limit 250
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadStorageZoneCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$params = [];
		if (!empty($data["StorageZone_id"])) {
			$where[] = "sz.StorageZone_id = :StorageZone_id";
			$params["StorageZone_id"] = $data["StorageZone_id"];
		} else {
			if (empty($data["Storage_id"])) {
				return false;
			}
			$where[] = "sz.Storage_id = :Storage_id";
			$params["Storage_id"] = $data["Storage_id"];
			if (!empty($data["query"])) {
				$params["query"] = "%" . $data["query"] . "%";
			}
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
		    select
		        sz.StorageZone_id as \"StorageZone_id\",
		        case 
		        	when liable.EmergencyTeam_Num is null then sz.StorageZone_Code
		        	else rtrim(sz.StorageZone_Code||' / '||liable.EmergencyTeam_Num)
		        end as \"StorageZone_Name\"
		    from
                v_StorageZone sz
                left join lateral (
                	select et.EmergencyTeam_Num
                	from
                		v_StorageZoneLiable szl
                		left join v_EmergencyTeam et on et.EmergencyTeam_id = szl.StorageZoneLiable_ObjectId
                	where szl.StorageZone_id = sz.StorageZone_id
                	  and szl.StorageZoneLiable_ObjectName = 'Бригада СМП'
                	  and szl.StorageZoneLiable_endDate is null
                	limit 1
                ) as liable on true
		    {$whereString}
		    limit 250
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugPrepFasCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$with = [];
		$join = [];
		$params = [];
		if (!empty($data["DrugPrepFas_id"])) {
			$where[] = "dpf.DrugPrepFas_id = :DrugPrepFas_id";
			$params["DrugPrepFas_id"] = $data["DrugPrepFas_id"];
		} else {
			if (!empty($data["Storage_id"])) {
				// По дефолту берем медикаменты, которые есть на субсчете достуно
				$sz = " and sat.SubAccountType_Code = 1 ";
				if (!empty($data["StorageZone_id"])) {
					$sz = " and exists(
            			select dsz.DrugStorageZone_id
            			from
            			    v_DrugStorageZone dsz
            			    inner join v_StorageZone sz on sz.StorageZone_id = dsz.StorageZone_id
            			where dsz.StorageZone_id = :StorageZone_id
            			  and dsz.Drug_id = dor.Drug_id
            			  and coalesce(dsz.DrugShipment_id, 0) = coalesce(dor.DrugShipment_id, 0)
            			  and sz.Storage_id = dor.Storage_id
            			  and dsz.DrugStorageZone_Count > 0
            			limit 1
            		) ";
					// Если указано место хранения то берем медикаменты:
					// с субсчета доступно если место хранения не передано на подотчет
					// с субсчета зарезервировано если место хранения подотчетное - при передаче на подотчет все медикаменты резервируются
					$sz .= " 
						and (
								(
									sat.SubAccountType_Code = 2
									and exists(
				            		    select szl.StorageZoneLiable_id
				            			from v_StorageZoneLiable szl
				            			where szl.StorageZone_id = :StorageZone_id
				            			  and szl.StorageZoneLiable_endDate is null
				            			limit 1
				            		)
								) or
								(
									sat.SubAccountType_Code = 1
									and not exists(
				            			select szl.StorageZoneLiable_id
				            			from v_StorageZoneLiable szl
				            			where szl.StorageZone_id = :StorageZone_id
				            			  and szl.StorageZoneLiable_endDate is null
				            			limit 1
				            		)
								)
							)
					";
					$params["StorageZone_id"] = $data["StorageZone_id"];
				}
				$with[] = " ost as (
					select
						dor.Drug_id,
						d.DrugPrepFas_id,
						coalesce(sum(dor.DrugOstatRegistry_Kolvo), 0) as cnt
					from
						v_DrugOstatRegistry dor
						left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
						left join rls.v_Drug d on d.Drug_id = dor.Drug_id
						left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
					where dor.DrugOstatRegistry_Kolvo > 0
					  and dor.Storage_id = :Storage_id
					  and (ps.PrepSeries_GodnDate is null or ps.PrepSeries_GodnDate >= tzgetdate())
					  and coalesce(ps.PrepSeries_IsDefect, 1) = 1
                    {$sz}
					group by
						dor.Drug_id,
						d.DrugPrepFas_id
				)";
				$join[] = "left join ost on ost.DrugPrepFas_id = dpf.DrugPrepFas_id";
				$where[] = "ost.Drug_id is not null";
				$params["Storage_id"] = $data["Storage_id"];
			}
			if (!empty($data["query"])) {
				$where[] = "dpf.DrugPrep_Name like :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
		}
		$withString = (count($with) != 0) ? "with " . implode(", ", $with) : "";
		$joinString = implode(" ", $join);
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
            {$withString}
		    select
		        dpf.DrugPrepFas_id as \"DrugPrepFas_id\",
		        dpf.DrugPrep_Name as \"DrugPrepFas_Name\"
		    from
                rls.v_DrugPrep dpf
                {$joinString}
		    {$whereString}
		    limit 250
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDrugCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$with = [];
		$join = [];
		$params = [];
		if (!empty($data["Drug_id"])) {
			$where[] = "d.Drug_id = :Drug_id";
			$params["Drug_id"] = $data["Drug_id"];
		} else {
			if (!empty($data["Storage_id"])) {
				// По дефолту берем медикаменты, которые есть на субсчете достуно
				$sz = " and sat.SubAccountType_Code = 1 ";
				if (!empty($data["DrugShipment_setDT_max"])) {
					$sz .= " and ds.DrugShipment_setDT::date <= :DrugShipment_setDT_max ";
					$params["DrugShipment_setDT_max"] = $data["DrugShipment_setDT_max"];
				}
				if (!empty($data['StorageZone_id'])) {
					$sz = " and exists(
            			select dsz.DrugStorageZone_id
            			from
            			    v_DrugStorageZone dsz
            			    inner join v_StorageZone sz on sz.StorageZone_id = dsz.StorageZone_id
            			where dsz.StorageZone_id = :StorageZone_id
            			  and dsz.Drug_id = dor.Drug_id
            			  and coalesce(dsz.DrugShipment_id, 0) = coalesce(dor.DrugShipment_id, 0)
            			  and sz.Storage_id = dor.Storage_id
            			  and dsz.DrugStorageZone_Count > 0
            			limit 1
            		) ";
					// Если указано место хранения то берем медикаменты:
					// с субсчета доступно если место хранения не передано на подотчет
					// с субсчета зарезервировано если место хранения подотчетное - при передаче на подотчет все медикаменты резервируются
					$sz .= " 
						and (
								(
									sat.SubAccountType_Code = 2
									and exists(
				            			select szl.StorageZoneLiable_id
				            			from v_StorageZoneLiable szl
				            			where szl.StorageZone_id = :StorageZone_id
				            			  and szl.StorageZoneLiable_endDate is null
				            			limit 1
				            		)
								) or
								(
									sat.SubAccountType_Code = 1
									and not exists(
				            			select szl.StorageZoneLiable_id
				            			from v_StorageZoneLiable szl
				            			where szl.StorageZone_id = :StorageZone_id
				            			  and szl.StorageZoneLiable_endDate is null
				            			limit 1
				            		)
								)
							)
					";
					$params["StorageZone_id"] = $data["StorageZone_id"];
				}
				$with[] = " ost as (
					select
						dor.Drug_id,
						coalesce(sum(dor.DrugOstatRegistry_Kolvo), 0) as cnt
					from
						v_DrugOstatRegistry dor
						left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
						left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
						left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
					where dor.DrugOstatRegistry_Kolvo > 0
					  and dor.Storage_id = :Storage_id
					  and (ps.PrepSeries_GodnDate is null or ps.PrepSeries_GodnDate >= tzgetdate())
					  and coalesce(ps.PrepSeries_IsDefect, 1) = 1
                      {$sz}
					group by dor.Drug_id
				)";
				$join[] = "left join ost on ost.Drug_id = d.Drug_id";
				$where[] = "ost.Drug_id is not null";
				$params["Storage_id"] = $data["Storage_id"];
			}
			if (!empty($data["DrugPrepFas_id"])) {
				$where[] = "d.DrugPrepFas_id = :DrugPrepFas_id";
				$params["DrugPrepFas_id"] = $data["DrugPrepFas_id"];
			}
			if (!empty($data["query"])) {
				$where[] = "d.Drug_Nomen like :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
		}
		$withString = (count($with) != 0) ? "with " . implode(", ", $with) : "";
		$joinString = implode(" ", $join);
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
            {$withString}
		    select
		        d.Drug_id as \"Drug_id\",
		        d.Drug_Nomen as \"Drug_Nomen\",
		        d.Drug_Name as \"Drug_Name\",
		        dn.DrugNomen_Code as \"DrugNomen_Code\"
		    from
                rls.v_Drug d
                left join lateral (
                    select i_dn.DrugNomen_Code
                    from rls.v_DrugNomen i_dn
                    where i_dn.Drug_id = d.Drug_id
                    order by i_dn.DrugNomen_Code desc
                    limit 1
                ) as dn on true
                {$joinString}
		    {$whereString}
		    limit 250
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadDocumentUcStrOidCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$join = [];
		$params = [];
		$count = "ost.cnt";
		// По дефолту берем медикаменты, которые есть на субсчете достуно
		$sub = " sat.SubAccountType_Code = 1 and ";
		$callObject->load->model("DocumentUc_model", "DocumentUc_model");
		$params["DefaultGoodsUnit_id"] = $callObject->DocumentUc_model->getDefaultGoodsUnitId();

		if (!empty($data["DocumentUcStr_id"])) {
			$where[] = "dus.DocumentUcStr_id = :DocumentUcStr_id";
			$params["DocumentUcStr_id"] = $data["DocumentUcStr_id"];
			$params["Storage_id"] = null;
			$join[] = "
				left join lateral (
	                select 
	                    dsz.DrugStorageZone_id, 
	                    dsz.DrugStorageZone_Count,
	                    dsl.DocumentUcStr_id  
	                from
	                    v_DrugStorageZone dsz 
	                    left join v_DrugShipmentLink dsl on dsz.DrugShipment_id = dsl.DrugShipment_id
	                where dsz.Drug_id = ost.Drug_id
	                  and dsz.DrugShipment_id = ost.DrugShipment_id
	                  and dsz.StorageZone_id = du.StorageZone_sid
	                limit 1
	            ) as dsz_c on true
        	";
			$count = "coalesce(dsz_c.DrugStorageZone_Count, ost.cnt)";
		} else {
			$params["DocumentUcStr_id"] = null;
			//должны учитываться только партии из приходных документов учета
			$dd_type_where = "ddt.DrugDocumentType_Code in (3, 6)"; //3 - Документ ввода остатков; 6 - Приходная накладная.
			if (!empty($data["StorageZone_id"])) {
				// Если указано место хранения то берем медикаменты:
				// с субсчета доступно если место хранения не передано на подотчет
				// с субсчета зарезервировано если место хранения подотчетное - при передаче на подотчет все медикаменты резервируются
				$sub = " 
					(
						(
							sat.SubAccountType_Code = 2
							and exists(
		            			select szl.StorageZoneLiable_id
		            			from v_StorageZoneLiable szl
		            			where szl.StorageZone_id = :StorageZone_id
		            			  and szl.StorageZoneLiable_endDate is null
		            			limit 1
		            		)
						) or
						(
							sat.SubAccountType_Code = 1
							and not exists(
		            			select szl.StorageZoneLiable_id
		            			from v_StorageZoneLiable szl
		            			where szl.StorageZone_id = :StorageZone_id
		            			  and szl.StorageZoneLiable_endDate is null
		            			limit 1
		            		)
						)
					) and 
				";
				$join[] = "
					left join lateral(
	                    select 
	                        dsz.DrugStorageZone_id, 
	                        dsz.DrugStorageZone_Count,
	                        dsl.DocumentUcStr_id  
	                    from
	                        v_DrugStorageZone dsz 
	                        left join v_DrugShipmentLink dsl on dsz.DrugShipment_id = dsl.DrugShipment_id
	                    where dsz.Drug_id = ost.Drug_id
	                      and dsz.DrugShipment_id = ost.DrugShipment_id
	                      and dsz.StorageZone_id = :StorageZone_id
	                    limit 1
	                ) as dsz_c on true
            	";
				$count = "coalesce(dsz_c.DrugStorageZone_Count, 0)";
				$where[] = "dus.DocumentUcStr_id = coalesce(dsz_c.DocumentUcStr_id,0)";
				$params["StorageZone_id"] = $data["StorageZone_id"];
			}
			if (empty($data["Storage_id"]) || empty($data["Drug_id"])) {
				return false;
			}
			$where[] = "ost.Drug_id is not null";
			$dd_type_where = "({$dd_type_where} or (ddt.DrugDocumentType_Code = 15 and du.Storage_tid = :Storage_id))"; //15 - Накладная на внутреннее перемещение
			$params["Storage_id"] = $data["Storage_id"];
			$where[] = "dus.Drug_id = :Drug_id";
			$params["Drug_id"] = $data["Drug_id"];
			if (!empty($data["DrugShipment_setDT_max"])) {
				$sub .= " ds.DrugShipment_setDT::date <= :DrugShipment_setDT_max and ";
				$params["DrugShipment_setDT_max"] = $data["DrugShipment_setDT_max"];
			}
			if (!empty($data["query"])) {
				$where[] = "ps.PrepSeries_Ser like :query";
				$params["query"] = "%" . $data["query"] . "%";
			}
			if (!empty($dd_type_where)) {
				$where[] = $dd_type_where;
			}
		}
		$joinString = (count($join) != 0) ? implode(" ", $join) : "";
		$whereString = (count($where) != 0) ? "where " . implode(' and ', $where) : "";
		$storageString = ":Storage_id";
		if (empty($params["Storage_id"]) && !empty($params["DocumentUcStr_id"])) {
			$storageString = "
				(
					select i_du.Storage_tid
                    from
                        v_DocumentUcStr i_dus
                        left join v_DocumentUc i_du on i_du.DocumentUc_id = i_dus.DocumentUc_id
                    where i_dus.DocumentUcStr_id = :DocumentUcStr_id
                )
			";
		}
		$query = "
            with ost as (
                select
                    dor.Drug_id,
                    dor.PrepSeries_id,
                    dor.DrugShipment_id,
                    coalesce(max(dor.GoodsUnit_id), :DefaultGoodsUnit_id) as GoodsUnit_id,
                    coalesce(sum(dor.DrugOstatRegistry_Kolvo), 0) as cnt
                from
                    v_DrugOstatRegistry dor
                    left join v_SubAccountType sat on sat.SubAccountType_id = dor.SubAccountType_id
                    left join rls.v_PrepSeries ps on ps.PrepSeries_id = dor.PrepSeries_id
                    left join v_DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
                where dor.DrugOstatRegistry_Kolvo > 0
                  and {$sub} dor.Storage_id = {$storageString}
                  and (ps.PrepSeries_GodnDate is null or ps.PrepSeries_GodnDate >= tzgetdate())
                  and coalesce(ps.PrepSeries_IsDefect, 1) = 1
                group by
                    dor.Drug_id,
                    dor.PrepSeries_id,
                    dor.DrugShipment_id
            )
		    select
		        dus.DocumentUcStr_id as \"DocumentUcStr_id\",
		        dus.PrepSeries_id as \"PrepSeries_id\",
		        to_char(dus.DocumentUcStr_Price, '{$callObject->numericForm18_2}') as \"DocumentUcStr_Price\",
		        (
		            coalesce(ps.PrepSeries_Ser||' ', '')||
		            coalesce(to_char(ps.PrepSeries_GodnDate, '{$callObject->dateTimeForm104}')||' ', '')||
		            coalesce(dus.DocumentUcStr_Price::varchar||' ', '')||
		            coalesce({$count}::varchar||coalesce(' '||b_gu.GoodsUnit_Nick||' ', ''), '')||
		            coalesce(df.DrugFinance_Name||' ', '')||
		            coalesce(wdcit.WhsDocumentCostItemType_Name, '')
		        ) as \"DocumentUcStr_Name\",
		        {$count} as \"DrugOstatRegistry_Kolvo\",
		        du.DrugFinance_id as \"DrugFinance_id\",
		        df.DrugFinance_Name as \"DrugFinance_Name\",
                du.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
                wdcit.WhsDocumentCostItemType_Name as \"WhsDocumentCostItemType_Name\",
                ps.PrepSeries_Ser as \"PrepSeries_Ser\",
                to_char(ps.PrepSeries_GodnDate, '{$callObject->dateTimeForm104}') as \"PrepSeries_GodnDate\",
                coalesce(dus.GoodsUnit_bid, ost.GoodsUnit_id, :DefaultGoodsUnit_id) as \"GoodsUnit_bid\",
				coalesce(dus.GoodsUnit_id, p_dus.GoodsUnit_id, :DefaultGoodsUnit_id) as \"GoodsUnit_id\",
				b_gu.GoodsUnit_Nick as \"GoodsUnit_bNick\",
				coalesce(b_gpc.GoodsPackCount_Count, 1) as \"GoodsPackCount_bCount\"
		    from
                v_DocumentUcStr dus
                left join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
                left join v_DrugShipmentLink dsll on dsll.DocumentUcStr_id = dus.DocumentUcStr_id
                left join v_DrugShipment ds on ds.DrugShipment_id = dsll.DrugShipment_id
				left join v_DrugShipmentLink p_dsll on p_dsll.DrugShipment_id = ds.DrugShipment_pid -- получение партии прихода
				left join v_DocumentUcStr p_dus on p_dus.DocumentUcStr_id = p_dsll.DocumentUcStr_id
                left join v_DrugDocumentType ddt on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                left join rls.v_Drug d on d.Drug_id = dus.Drug_id
                left join rls.v_PrepSeries ps on ps.PrepSeries_id = dus.PrepSeries_id
                left join v_DrugFinance df on df.DrugFinance_id = du.DrugFinance_id
                left join v_WhsDocumentCostItemType wdcit on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
                left join ost on ost.Drug_id = dus.Drug_id and ost.PrepSeries_id = dus.PrepSeries_id and ost.DrugShipment_id = dsll.DrugShipment_id
                left join v_GoodsUnit b_gu on b_gu.GoodsUnit_id = coalesce(dus.GoodsUnit_bid, :DefaultGoodsUnit_id)
                left join lateral (
                    select i_gpc.GoodsPackCount_Count
                    from v_GoodsPackCount i_gpc
                    where i_gpc.GoodsUnit_id = coalesce(dus.GoodsUnit_bid, ost.GoodsUnit_id)
                      and i_gpc.DrugComplexMnn_id = d.DrugComplexMnn_id
                      and (d.DrugTorg_id is null or i_gpc.TRADENAMES_ID is null or i_gpc.TRADENAMES_ID = d.DrugTorg_id)
                    order by
                        i_gpc.TRADENAMES_ID desc,
                        i_gpc.Org_id
                    limit 1
                ) as b_gpc on true
		    	{$joinString}
		    {$whereString}
		    order by ps.PrepSeries_GodnDate
		    limit 250
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	public static function loadGoodsUnitCombo(CmpCallCard_model $callObject, $data)
	{
		$where = [];
		$params = $data;
		if (!empty($data["GoodsUnit_id"])) {
			$where[] = "gu.GoodsUnit_id = :GoodsUnit_id";
		} else {
			if (empty($data["Drug_id"])) {
				return false;
			}
			$where[] = "gpc.GoodsUnit_id is not null";
			if (!empty($data["query"])) {
				$where[] = "gu.GoodsUnit_Name like :query";
				$params["query"] = $data["query"] . "%";
			}
		}
		$whereString = (count($where) != 0) ? "where " . implode(" and ", $where) : "";
		$query = "
            select
                gu.GoodsUnit_id,
                gu.GoodsUnit_Name,
                (
                    case
                        when gu.GoodsUnit_Name = 'упаковка' then 1
                        else gpc.GoodsPackCount_Count
                    end
                ) as GoodsPackCount_Count
            from
                v_GoodsUnit gu
                left join lateral (
                    select
                        i_gpc.GoodsUnit_id,
                        i_gpc.GoodsPackCount_Count
                    from v_GoodsPackCount i_gpc
                    where i_gpc.GoodsUnit_id = gu.GoodsUnit_id
                      and i_gpc.DrugComplexMnn_id = coalesce(:Drug_id, (select DrugComplexMnn_id from rls.v_Drug d where Drug_id = :Drug_id))
                      and (
                            coalesce(:Drug_id, (select DrugTorg_id from rls.v_Drug d where Drug_id = :Drug_id)) is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = coalesce(:Drug_id, (select DrugTorg_id from rls.v_Drug d where Drug_id = :Drug_id))
                      )
                    order by i_gpc.TRADENAMES_ID desc
                    limit 1
                ) as gpc on true
            {$whereString}
            union
            select
                gu.GoodsUnit_id,
                gu.GoodsUnit_Name,
                1 as GoodsPackCount_Count
            from v_GoodsUnit gu
            where gu.GoodsUnit_Name = 'упаковка'
              and (:query is null or 'упаковка' like :query)
              and :GoodsUnit_id is null
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadRegionSmpUnits(CmpCallCard_model $callObject, $data)
	{
		$params = ["Region_id" => $data["session"]["region"]["number"]];
		$orderBy = "";
		if (isset($data["Lpu_id"])) {
			$orderBy = "order by case when LB.\"Lpu_id\" = :Lpu_id then 1 else 2 end, LB.\"Lpu_id\"";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		$query = "
			select * 
			from (
				select distinct
					LB.LpuBuilding_id as \"LpuBuilding_id\",
					LB.LpuBuilding_Name as \"LpuBuilding_Name\",
					LB.LpuBuilding_Code as \"LpuBuilding_Code\",
					LB.LpuBuilding_Nick as \"LpuBuilding_Nick\",
					L.Lpu_Nick as \"Lpu_Nick\",
					L.Lpu_id as \"Lpu_id\"
				from
					v_LpuBuilding LB
					left join v_Lpu L on L.Lpu_id = LB.Lpu_id
					left join v_SmpUnitParam sup on LB.LpuBuilding_id = sup.LpuBuilding_id
					left join v_SmpUnitType sut on sup.SmpUnitType_id = sut.SmpUnitType_id
				where L.Region_id = :Region_id
				  and LB.LpuBuildingType_id = 27
			      and (sut.SmpUnitType_Code <>  4 or sut.SmpUnitType_Code is null)
			) LB			
			{$orderBy}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}