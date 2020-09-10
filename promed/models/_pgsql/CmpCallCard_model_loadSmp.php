<?php

class CmpCallCard_model_loadSmp
{
	/**
	 * @param CmpCallCard_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadSMPWorkPlace(CmpCallCard_model $callObject, $data)
	{
		$callObject->RefuseOnTimeout($data);
		$queryParams = [];
		$filterArray = [
			"coalesce(CCC.CmpCallCard_IsOpen, 1) = 2",
			"coalesce(CCC.CmpCallCard_IsReceivedInPPD, 1) != 2"
		];
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
		$filterArray[] = "CCC.Lpu_id = :Lpu_id";
		$filterArray[] = "CCC.CmpCallCardStatusType_id in (1,2,3,4,5)";
		$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
			    PS.Person_id as \"Person_id\",
			    PS.PersonEvn_id as \"PersonEvn_id\",
			    PS.Server_id as \"Server_id\",
			    coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
			    coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
			    coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
			    CCC.pmUser_insID as \"pmUser_insID\",
			    to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
			    CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			    CCC.CmpLpu_id as \"CmpLpu_id\",
			    CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
			    coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
			    to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			    rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
			    rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
			    coalesce(L.Lpu_Nick, '') as \"CmpLpu_Name\",
			    rtrim(coalesce(CD.Diag_Code, '')) as \"CmpDiag_Name\",
			    rtrim(coalesce(D.Diag_Code, '')) as \"StacDiag_Name\",
			    CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
			    case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
					then to_char(
					    (select coalesce((select DS.DataStorage_Value from DataStorage DS where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0 limit 1), 20)) - (DATEDIFF('mi', CCC.CmpCallCard_updDT, tzgetdate())||' minutes')::interval,
					    '{$callObject->dateTimeForm108}'
					)
					else '00'||':'||'00'
				end as \"PPD_WaitingTime\",
			    SLPU.Lpu_Nick as \"SendLpu_Nick\",
			    case when CCC.CmpCallCardStatusType_id in (5) then CCC.CmpCallCardStatus_Comment end as \"PPDDiag\",
			    case
					when RES.CmpPPDResult_Name is not null
						then 'Результат: '||RES.CmpPPDResult_Name
						else case when CCC.CmpCallCardStatusType_id = 3
							then coalesce(CMFNR.CmpMoveFromNmpReason_Name, CRTSR.CmpReturnToSmpReason_Name, CCC.CmpCallCardStatus_Comment, '')
							else '' end
				end as \"PPDResult\",
			    '' as \"ServedBy\",
			    case when CCC.CmpCallCardStatusType_id in (2, 3, 4) then PMC.PMUser_Name||to_char(ToDT.ToDT, '{$callObject->dateTimeForm108}') else '' end as \"PPDUser_Name\",
			    case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id in (1,2,3,4) then CCC.CmpCallCardStatusType_id + 1
							when CCC.CmpCallCardStatusType_id = 5 then CCC.CmpCallCardStatusType_id + 2
							else 1
						end
					else 6
				end as \"CmpGroup_id\",
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as \"PersonQuarantine_IsOn\",
				to_char(PQ.PersonQuarantine_begDT, 'DD.MM.YYYY') as \"PersonQuarantine_begDT\"
			from
				v_CmpCallCard CCC
				left join lateral (
					select CmpCallCardStatus_insDT as ServeDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 4
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as ServeDT on true
				left join lateral (
					select CmpCallCardStatus_insDT as ToDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 2
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as ToDT on true
				left join lateral (
					select
						CmpMoveFromNmpReason_id,
						CmpReturnToSmpReason_id
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 3
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as CmpNmpToSmpReason on true
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join v_Lpu L on L.Lpu_id = CCC.CmpLpu_id
				left join v_Diag CD on CD.Diag_id = CCC.Diag_gid
				left join Diag D on D.Diag_id = CCC.Diag_sid
				left join CmpPPDResult RES on RES.CmpPPDResult_id = CCC.CmpPPDResult_id
				left join v_Lpu SLPU on SLPU.Lpu_id = CCC.Lpu_ppdid
				left join v_CmpMoveFromNmpReason CMFNR on CMFNR.CmpMoveFromNmpReason_id = CmpNmpToSmpReason.CmpMoveFromNmpReason_id
				left join v_CmpReturnToSmpReason CRTSR on CRTSR.CmpReturnToSmpReason_id = CmpNmpToSmpReason.CmpReturnToSmpReason_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join lateral (
					select PQ.*
					from v_PersonQuarantine PQ
					where PQ.Person_id = CCC.Person_id 
					and PQ.PersonQuarantine_endDT is null
					limit 1
				) PQ on true
			{$whereString}
			order by
				(case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id in (1,2,3,4) then CCC.CmpCallCardStatusType_id + 1
							else 1
						end
					else 7
				end),
				CCC.CmpCallCard_prmDT desc
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$val = $result->result("array");
		// Добавляем ТАПы к вызовам
		// Собираем id cmpcallcard
		$CmpId = [];
		foreach ($val as $v) {
			$CmpId[] = $v["CmpCallCard_id"];
		}
		if (count($CmpId) > 0) {
			// Выполняем запрос, получая дополнительные поля
			$list_CmpId = implode(",", $CmpId);
			$query2 = "
				select
					EPL.evn_id as \"EvnPL_id\",
					EPL.CmpCallCard_id as \"CmpCallCard_id\",
					case
						when CCC.CmpCallCardStatusType_id in (5) then
							CCC.CmpCallCardStatus_Comment
						when CCC.CmpCallCardStatusType_id = 4 then
							case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end||
							case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
					end	as \"PPDDiag\",
					case when CCC.CmpCallCardStatusType_id in (4) then MP.Person_SurName||' '||MP.Person_FirName||' '||to_char(ServeDT.ServeDT, '{$callObject->dateTimeForm108}') else '' end as \"ServedBy\",
					case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
						then
							case
								when CCC.CmpCallCardStatusType_id = 4 and EPLD.diag_FullName is not null then CCC.CmpCallCardStatusType_id + 2
								when CCC.CmpCallCardStatusType_id in(1,2,3,4) then CCC.CmpCallCardStatusType_id + 1
								when CCC.CmpCallCardStatusType_id = 5 then CCC.CmpCallCardStatusType_id + 2
								else 1
							end
						else 6
					end as \"CmpGroup_id\"
				from
					EvnPL EPL 
					inner join Evn on Evn.Evn_id = EPL.evn_id and Evn.Evn_deleted = 1
					inner join v_CmpCallCard CCC on CCC.CmpCallCard_id = EPL.CmpCallCard_id and CCC.Lpu_ppdid is not null
					left join v_Diag EPLD on EPLD.Diag_id = EPL.Diag_id
					left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
					left join v_DirectType DT on DT.DirectType_id = EPL.DirectType_id
					left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_pid = EPL.evn_id
					left join v_MedPersonal MP on MP.MedPersonal_id = EVPL.MedPersonal_id and MP.Lpu_id = 1
					left join lateral (
						select CmpCallCardStatus_insDT as ServeDT
						from v_CmpCallCardStatus
						where CmpCallCardStatusType_id = 4
						  and CmpCallCard_id = EPL.CmpCallCard_id
						order by CmpCallCardStatus_insDT desc
						limit 1
					) as ServeDT on true
				where EPL.CmpCallCard_id in ({$list_CmpId})
				  and Evn.Lpu_id = CCC.Lpu_ppdid
				  and Evn.Evn_setDT >= CCC.CmpCallCard_prmDT::date
			";
			$result = $callObject->db->query($query2, []);
			if (is_object($result)) {
				// разбираем данные
				$ea = $result->result("array");
				$evnpls = [];
				// преобразуем массив для дальнейшего сведения данных
				foreach ($ea as $v) {
					$evnpls[$v["CmpCallCard_id"]] = $v;
				}
				// объединяем по строкам, если есть какие то данные
				foreach ($val as &$v) {
					if (!empty($evnpls[$v["CmpCallCard_id"]])) {
						$v["PPDDiag"] = $evnpls[$v["CmpCallCard_id"]]["PPDDiag"];
						$v["ServedBy"] = $evnpls[$v["CmpCallCard_id"]]["ServedBy"];
						$v["CmpGroup_id"] = $evnpls[$v["CmpCallCard_id"]]["CmpGroup_id"];
					}
				}
			}
		}
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
	public static function loadSMPDispatchCallWorkPlace(CmpCallCard_model $callObject, $data)
	{
		$queryParams = [];
		$filterArray = [
			"coalesce(CCC.CmpCallCard_IsOpen, 1) = 2",
			"coalesce(CCC.CmpCallCard_IsReceivedInPPD, 1) != 2"
		];
		$queryParams["pmUser_id"] = (!empty($data["pmUser_id"])) ? $data["pmUser_id"] : 0;
		if (!empty($data["Search_SurName"])) {
			$filterArray[] = "coalesce(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams["Person_SurName"] = $data["Search_SurName"] . "%";
		}
		if (!empty($data["Search_FirName"])) {
			$filterArray[] = "coalesce(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams["Person_FirName"] = $data["Search_FirName"] . "%";
		}
		if (!empty($data["Search_SecName"])) {
			$filterArray[] = "coalesce(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams["Person_SecName"] = $data["Search_SecName"] . "%";
		}
		if (!empty($data["Search_BirthDay"])) {
			$filterArray[] = "coalesce(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
			$queryParams["Person_BirthDay"] = $data["Search_BirthDay"];
		}
		if (!empty($data["CmpLpu_id"])) {
			$filterArray[] = "CCC.CmpLpu_id = :CmpLpu_id";
			$queryParams["CmpLpu_id"] = $data["CmpLpu_id"];
		}
		if (!empty($data["CmpCallCard_Ngod"])) {
			$filterArray[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filterArray[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		$isToday = strtotime($data["begDate"]) == mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		if (!empty($data["begDate"]) && !empty($data["endDate"]) && ($data["begDate"] == $data["endDate"]) && (!empty($data["hours"])) && $isToday) {
			$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate - (1||' days')::interval";
			$queryParams["begDate"] = $data["begDate"];
			$filterArray[] = "CCC.CmpCallCard_prmDT > tzgetdate() + (:hours::integer||' hours')::interval";
			switch ($data["hours"]) {
				case "1":
				case "2":
				case "3":
				case "6":
				case "12":
				case "24":
					$queryParams["hours"] = "-" . $data["hours"];
					break;
				default:
					$queryParams["hours"] = "-24";
					break;
			}
		} else {
			if (!empty($data["begDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate";
				$queryParams["begDate"] = $data["begDate"];
			}
			if (!empty($data["endDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date <= :endDate";
				$queryParams["endDate"] = $data["endDate"];
			}
		}
		if (!empty($data["CmpCallCard_id"])) {
			$filterArray[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
		}
		$filterArray[] = "CCC.Lpu_id = :Lpu_id";
		$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
			    PS.Person_id as \"Person_id\",
			    PS.PersonEvn_id as \"PersonEvn_id\",
			    PS.Server_id as \"Server_id\",
			    coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
			    coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
			    coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
			    coalesce(CCC.Person_Age,0) as \"Person_Age\",
			    CCC.pmUser_insID as \"pmUser_insID\",
			    to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
			    CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			    CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
			    case when coalesce(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as \"CmpCallCard_isLocked\",
			    case when coalesce(CCCLL.CmpCallCardLockList_id,0) = 0
			        then coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
					else '<img src=\"../img/grid/lock.png\">'||coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				end as \"Person_FIO\",
			    to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			    rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
			    rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
			    rtrim(coalesce(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\",
			    rtrim(case when CLD.diag_FullName is not null then CLD.diag_FullName else rtrim(coalesce(CLD.Diag_Code, '')||' '||coalesce(CLD.Diag_Name, '')) end) as \"CmpDiag_Name\",
			    rtrim(coalesce(D.Diag_Code, '')) as \"StacDiag_Name\",
			    CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
			    SLPU.Lpu_Nick as \"SendLpu_Nick\",
			    coalesce(RGN.KLRgn_FullName, '')||
					case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
					case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end||
					case when CCC.CmpCallCard_Comm is not null then '</br>'||CCC.CmpCallCard_Comm else '' end||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end
			    as \"Adress_Name\",
				case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id is null then 1
							when CCC.Lpu_ppdid is null
								then
									case
										when CCC.CmpCallCardStatusType_id in (1, 2) then CCC.CmpCallCardStatusType_id+1
										when CCC.CmpCallCardStatusType_id in (4) then CCC.CmpCallCardStatusType_id
										when CCC.CmpCallCardStatusType_id in (6) then 10
										when CCC.CmpCallCardStatusType_id in (5) then 9
										when CCC.CmpCallCardStatusType_id in (3) then 7
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4, 5, 6) then CCC.CmpCallCardStatusType_id+4
									end
						end
					else 10
				end as \"CmpGroup_id\",
				case when CCC.CmpCallCardStatusType_id in (2, 3, 4) then PMC.PMUser_Name||to_char(CCC.CmpCallCard_updDT, '{$callObject->dateTimeForm104}') else '' end as \"PPDUser_Name\",
				case when CCC.CmpCallCardStatusType_id = 3 then
					case
						when coalesce(CCCStatusHist.CmpMoveFromNmpReason_id, 0) = 0 then  CCC.CmpCallCardStatus_Comment
						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
					when CCC.CmpCallCardStatusType_id = 5 then
						CCC.CmpCallCardStatus_Comment
					when CCC.CmpCallCardStatusType_id = 4 then
						case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end||
						case when RC.ResultClass_Name is not null then '<br />Результат: '||RC.ResultClass_Name else '' end||
						case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
				end	as \"PPDResult\",
				to_char(ServeDT.ServeDT, '{$callObject->dateTimeForm104}') as \"ServeDT\",
				case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id is null then '01'
							when CCC.Lpu_ppdid is null
								then
									case
										when CCC.CmpCallCardStatusType_id in (1, 2) then '0'||(CCC.CmpCallCardStatusType_id + 1)::varchar
										when CCC.CmpCallCardStatusType_id in (4) then '0'||CCC.CmpCallCardStatusType_id::varchar
										when CCC.CmpCallCardStatusType_id in (6) then '10'
										when CCC.CmpCallCardStatusType_id in (5) then '09'
										when CCC.CmpCallCardStatusType_id in (3) then '07'
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4, 5) then '0'||(CCC.CmpCallCardStatusType_id + 4)::varchar
										when CCC.CmpCallCardStatusType_id in (6) then ('10')
									end
						end
					else '10'
				end as \"CmpGroupName_id\",
				case when CCC.pmUser_insID = :pmUser_id then 1 else 0 end as \"Owner\"
			from
				v_CmpCallCard CCC
				left join lateral (
					select CmpCallCardStatus_insDT as ServeDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 4
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as ServeDT on true
				left join lateral (
					select CmpCallCardStatus_insDT as ToDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 2
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as ToDT on true
				left join lateral (
					select
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus
						left join v_CmpMoveFromNmpReason on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where CmpCallCardStatusType_id = 3
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as CCCStatusHist on true
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L on L.Lpu_id = CCC.CmpLpu_id
				left join CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU on SLPU.Lpu_id = CCC.Lpu_ppdid
				left join lateral (
					select *
					from v_EvnPL AS t1
					where t1.CmpCallCard_id = CCC.CmpCallCard_id
					  and t1.Lpu_id = CCC.Lpu_ppdid
					  and t1.EvnPL_setDate >= CCC.CmpCallCard_prmDT::date
					  and CCC.Lpu_ppdid is not null
				    limit 1
				) as EPL on true
				left join v_Diag EPLD on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD on CLC.Diag_id = CLD.Diag_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_CmpCallCardlockList CCCLL on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id and (60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate())) > 0
			{$whereString}
			order by
				(case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4) then CCC.CmpCallCardStatusType_id + 2
							else
								case when CCC.Lpu_ppdid is not null
									then 1
									else 2
								end
						end
					else 7
				end),
				CCC.CmpCallCard_prmDT desc
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
	public static function loadSMPAdminWorkPlace(CmpCallCard_model $callObject, $data)
	{
		$queryParams = [];
		$filterArray = [
			"coalesce(CCC.CmpCallCard_IsReceivedInPPD, 1) != 2",
			"coalesce(CCC.CmpCallCard_IsOpen, 1) = 2"
		];
		$queryParams["pmUser_id"] = (!empty($data["pmUser_id"])) ? $data["pmUser_id"] : 0;
		if (!empty($data["Search_SurName"])) {
			$filterArray[] = "coalesce(PS.Person_Surname, CCC.Person_SurName,) ilike :Person_SurName";
			$queryParams["Person_SurName"] = $data["Search_SurName"] . "%";
		}
		if (!empty($data["Search_FirName"])) {
			$filterArray[] = "coalesce(PS.Person_Firname, CCC.Person_FirName) ilike :Person_FirName";
			$queryParams["Person_FirName"] = $data["Search_FirName"] . "%";
		}
		if (!empty($data["Search_SecName"])) {
			$filterArray[] = "coalesce(PS.Person_Secname, CCC.Person_SecName) ilike :Person_SecName";
			$queryParams["Person_SecName"] = $data["Search_SecName"] . "%";
		}
		if (!empty($data["Search_BirthDay"])) {
			$filterArray[] = "coalesce( PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
			$queryParams["Person_BirthDay"] = $data["Search_BirthDay"];
		}
		if (!empty($data["CmpLpu_id"])) {
			$filterArray[] = "CCC.CmpLpu_id = :CmpLpu_id";
			$queryParams["CmpLpu_id"] = $data["CmpLpu_id"];
		}
		if (!empty($data["CmpCallCard_Ngod"])) {
			$filterArray[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filterArray[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		if (!empty($data["dispatchCallPmUser_id"])) {
			$filterArray[] = "CCC.pmUser_insID = :dispatchCallPmUser_id";
			$queryParams["dispatchCallPmUser_id"] = $data["dispatchCallPmUser_id"];
		}
		if (!empty($data["EmergencyTeam_id"])) {
			$filterArray[] = "CCC.EmergencyTeam_id = :EmergencyTeam_id";
			$queryParams["EmergencyTeam_id"] = $data["EmergencyTeam_id"];
		}
		$deletedField = "null";
		if (!empty($data["displayDeletedCards"]) && $data["displayDeletedCards"] == "on") {
			$deletedField = "CCC.CmpCallCard_deleted";
			$filterArray[] = "CCC.CmpCallCard_firstVersion is null";
		}
		if (!empty($data["LpuBuilding_id"])) {
			$filterArray[] = "CCC.LpuBuilding_id = :LpuBuilding_id";
			$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
		} else {
			$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
			$lpuBuilding = $callObject->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			if (!empty($lpuBuilding[0]) && !empty($lpuBuilding[0]["LpuBuilding_id"])) {
				$data["LpuBuilding_id"] = $lpuBuilding[0]["LpuBuilding_id"];
				$operLpuBuilding = $callObject->CmpCallCard_model4E->getOperDepartament($data);
				if (!empty($operLpuBuilding["LpuBuilding_pid"]) && $operLpuBuilding["LpuBuilding_pid"] == $data["LpuBuilding_id"]) {
					//опер отдел
					$smpUnitsNested = $callObject->CmpCallCard_model4E->loadSmpUnitsNested($data, true);
					if (!empty($smpUnitsNested)) {
						$list = [];
						foreach ($smpUnitsNested as $value) {
							$list[] = $value["LpuBuilding_id"];
						}
						$list_str = implode(",", $list);
						$filterArray[] = "CCC.LpuBuilding_id is null or CCC.LpuBuilding_id in ($list_str)";
					}
				} else {
					//подчиненные подстанции
					$filterArray[] = "(CCC.LpuBuilding_id is null or CCC.LpuBuilding_id = :LpuBuilding_id)";
					$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
				}
			}

		}
		$isToday = strtotime($data["begDate"]) == mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		if (!empty($data["begDate"]) && !empty($data["endDate"]) && ($data["begDate"] == $data["endDate"]) && (!empty($data["hours"])) && $isToday) {
			$filterArray[] = "CCC.CmpCallCard_prmDT::date >= (:begDate - (1||' days')::interval)";
			$filterArray[] = "CCC.CmpCallCard_prmDT > (tzgetdate() + (:hours::int8||' hours')::interval)";
			$queryParams["begDate"] = $data["begDate"];
			switch ($data["hours"]) {
				case "1":
				case "2":
				case "3":
				case "6":
				case "12":
				case "24":
					$queryParams["hours"] = "-" . $data["hours"];
					break;
				default:
					$queryParams["hours"] = "-24";
					break;
			}
		} else {
			if (!empty($data["begDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate";
				$queryParams["begDate"] = $data["begDate"];
			}
			if (!empty($data["endDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date <= :endDate";
				$queryParams["endDate"] = $data["endDate"];
			}
		}
		//Для получения изменений одного талона вызова
		if (!empty($data["CmpCallCard_id"])) {
			$filterArray[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
		}

		if (getRegionNick() == 'kz') {
			$filterArray[] = "and CCC.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}

		//$filterArray[] = "CCC.Lpu_id = :Lpu_id";
		//$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
		$callObject->load->model("Options_model", "opmodel");
		$callObject->opmodel->getOptionsGlobals($data);

		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				CCC.CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
				CCC.CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
				CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
				coalesce(CCCL.Lpu_Nick, lsL.Lpu_Nick) as \"Lpu_Nick\",
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				coalesce(CCC.Person_Surname, PS.Person_SurName) as \"Person_Surname\",
				coalesce(CCC.Person_Firname, PS.Person_FirName) as \"Person_Firname\",
				coalesce(CCC.Person_Secname, PS.Person_SecName) as \"Person_Secname\",
				coalesce(CCC.Person_Age, 0) as \"Person_Age\",
				CCC.pmUser_insID as \"pmUser_insID\",
				CRR.CmpRejectionReason_Name as \"CmpRejectionReason_Name\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm120}') as \"CmpCallCard_prmDate\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCC.LpuBuilding_id as \"LpuBuilding_id\",
				case when coalesce(CCCLL.CmpCallCardLockList_id, 0) = 0 then 0 else 1 end as \"CmpCallCard_isLocked\",
				CCC.CmpReason_id as \"CmpReason_id\",
				coalesce(CR.CmpReason_Code||'. ', '')||CR.CmpReason_Name as \"CmpReason_Name\",
				CCC.CmpSecondReason_id as \"CmpSecondReason_id\",
				coalesce(CSecondR.CmpReason_Code||'. ', '')||CSecondR.CmpReason_Name as \"CmpSecondReason_Name\",
				rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code::varchar||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				CL.Lpu_Nick as \"CmpLpu_Name\",
				rtrim(case when CLD.diag_FullName is not null then CLD.diag_FullName else CD.Diag_Code end) as \"CmpDiag_Name\",
				case when (CLC.CmpCloseCard_id is not null) then DiagStacFromCombo.Diag_FullName else rtrim(coalesce(D.Diag_FullName, '')) end as \"StacDiag_Name\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
				SLPU.Lpu_Nick as \"SendLpu_Nick\",
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
								end||
								coalesce(lower(Town.KLSocr_Nick)||'. ', '')||
								Town.KLTown_Name else ''
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
						case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end
				as \"Adress_Name\",
				CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
				case
					when CCC.CmpCallCardStatusType_id = 3 then
						case
							when coalesce(CCCStatusHist.CmpMoveFromNmpReason_id, 0) = 0 then  CCC.CmpCallCardStatus_Comment
							else CCCStatusHist.CmpMoveFromNmpReason_Name
						end
					when CCC.CmpCallCardStatusType_id = 5 then
						CCC.CmpCallCardStatus_Comment
					when CCC.CmpCallCardStatusType_id = 4 then
						case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end||
						case when RC.ResultClass_Name is not null then '<br />Результат: '||RC.ResultClass_Name else '' end||
						case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
					end
				as \"PPDResult\",
				ServeDT.ServeDT as \"ServeDT\",
				case when CCC.CmpCallCardStatusType_id in (2, 3, 4)
					then PMC.PMUser_Name||to_char(CCC.CmpCallCard_updDT, '{$callObject->dateTimeForm104}')
					else ''
				end as \"PPDUser_Name\",
				CCT.CmpCallType_Code as \"CmpCallType_Code\",
				case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when GetRegion() = 2 and CCT.CmpCallType_Code = 14 then 10
							when CCC.CmpCallCardStatusType_id is null then 1
							when CCC.Lpu_ppdid is null
								then
									case
										when CCC.CmpCallCardStatusType_id in (1, 2) then CCC.CmpCallCardStatusType_id+1
										when CCC.CmpCallCardStatusType_id in (4) then CCC.CmpCallCardStatusType_id
										when CCC.CmpCallCardStatusType_id in (6) then 10
										when CCC.CmpCallCardStatusType_id in (5) then 9
										when CCC.CmpCallCardStatusType_id in (3) then 7
										when CCC.CmpCallCardStatusType_id in (7) then 3
										when CCC.CmpCallCardStatusType_id in (8) then 2
										when CCC.CmpCallCardStatusType_id > 8 then 1
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4, 5, 6) then CCC.CmpCallCardStatusType_id+4
										when CCC.CmpCallCardStatusType_id in (7) then 3
										when CCC.CmpCallCardStatusType_id in (8) then 2
										when CCC.CmpCallCardStatusType_id in (20) then 5
										when CCC.CmpCallCardStatusType_id > 8 then 1
									end
						end
					else 10
				end as \"CmpGroup_id\",
				case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when GetRegion() = 2 and CCT.CmpCallType_Code = 14 then '10'
							when CCC.CmpCallCardStatusType_id is NULL then '01'
							when CCC.Lpu_ppdid is null
								then
									case
										when CCC.CmpCallCardStatusType_id in (1, 2) then '0'||(CCC.CmpCallCardStatusType_id + 1)::varchar
										when CCC.CmpCallCardStatusType_id in (4) then '0'||CCC.CmpCallCardStatusType_id::varchar
										when CCC.CmpCallCardStatusType_id in (6) then '10'
										when CCC.CmpCallCardStatusType_id in (5) then '09'
										when CCC.CmpCallCardStatusType_id in (3) then '07'
										when CCC.CmpCallCardStatusType_id in (7) then ('03')
										when CCC.CmpCallCardStatusType_id in (8) then ('02')
										when CCC.CmpCallCardStatusType_id > 8 then ('01')
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4, 5) then ('0'||(CCC.CmpCallCardStatusType_id + 4)::varchar)
										when CCC.CmpCallCardStatusType_id in (6) then ('10')
										when CCC.CmpCallCardStatusType_id in (7) then ('03')
										when CCC.CmpCallCardStatusType_id in (8) then ('02')
										when CCC.CmpCallCardStatusType_id in (20) then ('05')
										when CCC.CmpCallCardStatusType_id > 8 then ('01')
									end
						end
					else '10'
				end as \"CmpGroupName_id\",
				case when CCC.pmUser_insID = :pmUser_id then 1 else 0 end as \"Owner\",
				CCC.Lpu_ppdid as \"Lpu_ppdid\",
				{$deletedField} as \"CmpCallCard_isDeleted\",
				case when coalesce(CCCLL.CmpCallCardLockList_id,0) = 0 then
					COALESCE(CCC.Person_SurName, PS.Person_Surname, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, PS.Person_Firname, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, PS.Person_Secname, '')
				else
					'<img src=\" ../img / grid / lock . png\">'||COALESCE(CCC.Person_SurName, PS.Person_Surname, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, PS.Person_Firname, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, PS.Person_Secname, '')
				end as \"Person_FIO\",
				to_char(cast(coalesce(CCC.Person_BirthDay, PS.Person_Birthday) as datetime), 'dd.mm.yyyy hh24:mi:ss') as \"Person_Birthday\",
			from
				v_CmpCallCard CCC
				left join lateral (
					select CmpCallCardStatus_insDT as ServeDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 4
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ServeDT on true
				left join lateral (
					select CmpCallCardStatus_insDT as ToDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 2
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ToDT on true
				left join lateral (
					select
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus
						left join v_CmpMoveFromNmpReason on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where CmpCallCardStatusType_id = 3
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as CCCStatusHist on true
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpReason CSecondR on CSecondR.CmpReason_id = CCC.CmpSecondReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join lateral (
					select
						*
					from v_CmpCallCardStatus CCCS
					where
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
					order by CCCS.CmpCallCardStatus_updDT desc
					limit 1
				) as lastStatus on true
				left join v_CmpRejectionReason CRR on CRR.CmpRejectionReason_id = lastStatus.CmpReason_id
				left join lateral (
					select
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id
					from v_PersonCard_all pc
					where pc.Person_id = CCC.Person_id
					  and LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) as pcard on true
				left join v_Lpu CL on pcard.Lpu_id=CL.Lpu_id
				left join v_Diag CD on CD.Diag_id = CCC.Diag_gid
				left join v_Diag D on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU on SLPU.Lpu_id = CCC.Lpu_ppdid
				left join v_EvnPL EPL on EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id = CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					and EvnPL_setDate >= CCC.CmpCallCard_prmDT::date
				left join v_Diag EPLD on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join v_EmergencyTeam ET on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join {$callObject->schema}.v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join {$callObject->comboSchema}.v_CmpCloseCardCombo CLCC on CLCC.CmpCloseCardCombo_Code = 243
				left join lateral (
					select rel.Localize
					from {$callObject->schema}.v_CmpCloseCardRel rel
					where rel.CmpCloseCard_id = CLC.CmpCloseCard_id
					  and CLCC.CmpCloseCardCombo_id = rel.CmpCloseCardCombo_id
					  and isnumeric(rel.Localize||'e0') = 1
					order by CmpCloseCardRel_insDT desc
					limit 1
				) as RL on true
				left join v_Diag as DiagStacFromCombo on DiagStacFromCombo.Diag_id = RL.Localize
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_Diag CLD on CLD.Diag_id = CLC.Diag_id
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
				left join v_CmpCallCardlockList CCCLL on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
					and (60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate())) > 0
				left join v_Lpu CCCL on CCCL.Lpu_id=CCC.Lpu_hid
				left join v_LpuSection LS on LS.LpuSection_id = CCC.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_Lpu lsL on lsL.Lpu_id = LB.Lpu_id
			where
			    --where
			    " . implode(' and ', $filterArray). "
			    -- end where
			order by
				(case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4)
							then CCC.CmpCallCardStatusType_id + 2
							else case when CCC.Lpu_ppdid is not null then 1 else 2 end
						end
					else 7
				end),
				CCC.CmpCallCard_prmDT desc
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$val = $result->result("array");
		for ($i = 0; $i < count($val); $i++) {
			if ($val[$i]["ServeDT"]) {
				$val[$i]["ServeDT"] = date_format($val[$i]["ServeDT"], "m.d.Y");
			}
		}
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
	public static function loadSMPDispatchDirectWorkPlace(CmpCallCard_model $callObject, $data)
	{
		$queryParams = [];
		$callObject->RefuseOnTimeout($data);
		$filterArray = [
			"coalesce(CCC.CmpCallCard_IsOpen, 1) = 2"
		];
		if (!empty($data["Search_SurName"])) {
			$filterArray[] = "coalesce(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams["Person_SurName"] = $data["Search_SurName"] . "%";
		}
		if (!empty($data["Search_FirName"])) {
			$filterArray[] = "coalesce(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams["Person_FirName"] = $data["Search_FirName"] . "%";
		}
		if (!empty($data["Search_SecName"])) {
			$filterArray[] = "coalesce(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams["Person_SecName"] = $data["Search_SecName"] . "%";
		}
		if (!empty($data["Search_BirthDay"])) {
			$filterArray[] = "coalesce(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
			$queryParams["Person_BirthDay"] = $data["Search_BirthDay"];
		}
		if (!empty($data["CmpLpu_id"])) {
			$filterArray[] = "CCC.CmpLpu_id = :CmpLpu_id";
			$queryParams["CmpLpu_id"] = $data["CmpLpu_id"];
		}
		if (!empty($data["CmpCallCard_Ngod"])) {
			$filterArray[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filterArray[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		$isToday = strtotime($data["begDate"]) == mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		if (!empty($data["begDate"]) && !empty($data["endDate"]) && ($data["begDate"] == $data["endDate"]) && (!empty($data["hours"])) && $isToday) {
			$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate - (1||' days')::interval";
			$filterArray[] = "CCC.CmpCallCard_prmDT > (tzgetdate() + (:hours::integer||' hours')::interval)";
			$queryParams["begDate"] = $data["begDate"];
			switch ($data["hours"]) {
				case "1":
				case "2":
				case "3":
				case "6":
				case "12":
				case "24":
					$queryParams["hours"] = "-" . $data["hours"];
					break;
				default:
					$queryParams["hours"] = "-24";
					break;
			}
		} else {
			if (!empty($data["begDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate";
				$queryParams["begDate"] = $data["begDate"];
			}
			if (!empty($data["endDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date <= :endDate";
				$queryParams["endDate"] = $data["endDate"];
			}
		}
		if (!empty($data["dispatchCallPmUser_id"])) {
			$filterArray[] = "CCC.pmUser_insID = :dispatchCallPmUser_id";
			$queryParams["dispatchCallPmUser_id"] = $data["dispatchCallPmUser_id"];
		}
		if (!empty($data["EmergencyTeam_id"])) {
			$filterArray[] = "CCC.EmergencyTeam_id = :EmergencyTeam_id";
			$queryParams["EmergencyTeam_id"] = $data["EmergencyTeam_id"];
		}
		//Для получения изменений одного талона вызова
		if (!empty($data["CmpCallCard_id"])) {
			$filterArray[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
		}
		$filterArray[] = "CCC.Lpu_id = :Lpu_id";
		$queryParams["Lpu_id"] = $data["session"]["lpu_id"];
		// Отображаем только вызовы переданные от диспетчера вызовов СМП
		$filterArray[] = "CCC.CmpCallCardStatusType_id is not null";

		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
				coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
				coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
				coalesce(CCC.Person_Age,0) as \"Person_Age\",
				CCC.pmUser_insID as \"pmUser_insID\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				case when coalesce(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as \"CmpCallCard_isLocked\",
				case when coalesce(CCCLL.CmpCallCardLockList_id,0) = 0 then
					coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				else
					'<img src=\"../img/grid/lock.png\">'||coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				end as \"Person_FIO\",
				to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
				rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				rtrim(coalesce(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\",
				rtrim(coalesce(CLD.Diag_Code, '')||' '||coalesce(CLD.Diag_Name, '')) as \"CmpDiag_Name\",
				rtrim(coalesce(D.Diag_Code, '')) as \"StacDiag_Name\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
			    case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
					then to_char(
					    (select coalesce((select DS.DataStorage_Value from DataStorage DS where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0 limit 1), 20)) - (DATEDIFF('mi', CCC.CmpCallCard_updDT, tzgetdate())||' minutes')::interval,
					    '{$callObject->dateTimeForm108}'
					)
					else '00'||':'||'00'
				end as \"PPD_WaitingTime\",
				SLPU.Lpu_Nick as \"SendLpu_Nick\",
				coalesce(RGN.KLRgn_FullName, '')||
					case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
					case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end||
					case when CCC.CmpCallCard_Comm is not null then '</br>'||CCC.CmpCallCard_Comm else '' end||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else ''
				end as \"Adress_Name\",
				case when CCC.CmpCallCardStatusType_id = 3 then
					case when coalesce(CCCStatusHist.CmpMoveFromNmpReason_id, 0) = 0
					    then  CCC.CmpCallCardStatus_Comment
						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
					when CCC.CmpCallCardStatusType_id = 5 then
						CCC.CmpCallCardStatus_Comment
					when CCC.CmpCallCardStatusType_id = 4 then
						case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end||
						case when RC.ResultClass_Name is not null then '<br />Результат: '||RC.ResultClass_Name else '' end||
						case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
				end	as \"PPDResult\",
				to_char(ServeDT.ServeDT, '{$callObject->dateTimeForm104}') as \"ServeDT\",
				case when CCC.CmpCallCardStatusType_id in (2, 3, 4) then PMC.PMUser_Name||to_char(CCC.CmpCallCard_updDT, '{$callObject->dateTimeForm104}') else '' end as \"PPDUser_Name\",
				case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.Lpu_ppdid is null then
								case
									when CCC.CmpCallCardStatusType_id = 4 then 3
									when CCC.CmpCallCardStatusType_id = 6 then 9
									when CCC.CmpCallCardStatusType_id = 3 then 7
									when CCC.CmpCallCardStatusType_id in (1, 2) then CCC.CmpCallCardStatusType_id
									else CCC.CmpCallCardStatusType_id + 3
								end
							else
								case
									when CmpCallCardStatusType_id = 4 then 6
									when CmpCallCardStatusType_id = 3 then 7
									else CCC.CmpCallCardStatusType_id + 3
								end
							end
					else 9
				end as \"CmpGroup_id\"
			from
				v_CmpCallCard CCC
				left join lateral (
					select CmpCallCardStatus_insDT as ServeDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 4
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ServeDT on true
				left join lateral (
					select CmpCallCardStatus_insDT as ToDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 2
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as ToDT on true
				left join lateral (
					select
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus
						left join v_CmpMoveFromNmpReason on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where CmpCallCardStatusType_id = 3
				      and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				    limit 1
				) as CCCStatusHist on true
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L on L.Lpu_id = CCC.CmpLpu_id
				left join CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU on SLPU.Lpu_id = CCC.Lpu_ppdid
				left join lateral (
					select *
					from v_EvnPL AS t1
					where t1.CmpCallCard_id = CCC.CmpCallCard_id
					  and t1.Lpu_id = CCC.Lpu_ppdid
					  and t1.EvnPL_setDate >= CCC.CmpCallCard_prmDT::date
					  and CCC.Lpu_ppdid is not null
					limit 1
				) as EPL on true
				left join v_Diag EPLD on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join v_EmergencyTeam ET on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD on CLC.Diag_id = CLD.Diag_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_CmpCallCardlockList CCCLL on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id and (60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate())) > 0
			{$whereString}
			order by
				(case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4) then CCC.CmpCallCardStatusType_id + 2
							else
								case when CCC.Lpu_ppdid is not null
									then 1
									else 2
								end
						end
					else 7
				end),
				CCC.CmpCallCard_prmDT desc
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
	public static function loadSmpFarmacyRegisterHistory(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CFBRH.CmpFarmacyBalanceRemoveHistory_id as \"CmpFarmacyBalanceRemoveHistory_id\",
				case when coalesce(D.Drug_Fas, 0) = 0
				    then rtrim(coalesce(D.DrugTorg_Name, '')||' '||coalesce(D.DrugForm_Name, '')||' '||coalesce(D.Drug_Dose, '')
					else rtrim(coalesce(D.DrugTorg_Name, '')||', '||coalesce(D.DrugForm_Name, '')||', '||coalesce(D.Drug_Dose, '')||', №'||D.Drug_Fas::varchar
				end as \"DrugTorg_Name\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				MP.Person_Fin as \"Person_Fin\",
				to_char(CFBRH.CmpFarmacyBalanceRemoveHistory_insDT, '{$callObject->dateTimeForm104}') as \"CmpCallCard_prmDate\",
				CFBRH.CmpFarmacyBalanceRemoveHistory_DoseCount as \"CmpFarmacyBalanceRemoveHistory_DoseCount\",
				CFBRH.CmpFarmacyBalanceRemoveHistory_PackCount as \"CmpFarmacyBalanceRemoveHistory_PackCount\"
			from
				CmpFarmacyBalanceRemoveHistory CFBRH
				left join v_EmergencyTeam ET on ET.EmergencyTeam_id = CFBRH.EmergencyTeam_id
				left join v_MedPersonal as MP on MP.MedPersonal_id = ET.EmergencyTeam_HeadShift
				left join v_CmpFarmacyBalance as CFB on CFB.CmpFarmacyBalance_id = CFBRH.CmpFarmacyBalance_id
				left join rls.v_Drug D on D.Drug_id = CFB.Drug_id
			where CFBRH.CmpFarmacyBalance_id = :CmpFarmacyBalance_id
			order by CFBRH.CmpFarmacyBalanceRemoveHistory_id desc
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
	public static function loadSmpFarmacyRegister(CmpCallCard_model $callObject, $data)
	{
		$query = "
			select
				CFB.CmpFarmacyBalance_id as \"CmpFarmacyBalance_id\",
				CFBAH_AD.AddDate as \"AddDate\",
				CFB.Drug_id as \"Drug_id\",
				D.DrugTorg_Name as \"DDFGT\",
				case when coalesce(D.Drug_Fas, 0) = 0
				    then rtrim(coalesce(D.DrugTorg_Name, '')||' '||coalesce(D.DrugForm_Name, '')||' '||coalesce(D.Drug_Dose,''))
					else rtrim(coalesce(D.DrugTorg_Name, '')||', '||coalesce(D.DrugForm_Name, '')||', '||coalesce(D.Drug_Dose,'')||', №'||D.Drug_Fas::varchar)
				end as \"DrugTorg_Name\",
				D.Drug_PackName as \"Drug_PackName\",
				D.Drug_Fas as \"Drug_Fas\",
				CFB.CmpFarmacyBalance_PackRest as \"CmpFarmacyBalance_PackRest\",
				CFB.CmpFarmacyBalance_DoseRest as \"CmpFarmacyBalance_DoseRest\"
			from
				v_CmpFarmacyBalance CFB
				left join lateral (
					select to_char(CFBAH.CmpFarmacyBalanceAddHistory_AddDate, '{$callObject->dateTimeForm104}') as AddDate
					from v_CmpFarmacyBalanceAddHistory CFBAH
					where CFB.CmpFarmacyBalance_id = CFBAH.CmpFarmacyBalance_id
					order by CFBAH.CmpFarmacyBalanceAddHistory_AddDate desc
					limit 1
				) as CFBAH_AD on true
				left join rls.v_Drug D on D.Drug_id = CFB.Drug_id
			where CFB.Lpu_id = :Lpu_id
			order by D.DrugTorg_Name
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
	public static function loadSmpStacDiffDiagJournal(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [];
		// Скрываем вызовы принятые в ППД
		if (!empty($data["begDate"])) {
			$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate";
			$queryParams["begDate"] = $data["begDate"];
		}
		if (!empty($data["endDate"])) {
			$filterArray[] = "CCC.CmpCallCard_prmDT::date <= :endDate";
			$queryParams["endDate"] = $data["endDate"];
		}
		if (!empty($data["diffDiagView"]) && $data["diffDiagView"] != "false") {
			$filterArray[] = "coalesce(EPS.Diag_pid, 0) != coalesce(CLC.Diag_id, 0)";
		}
		/*
		if (!empty($data["LpuBuilding_id"])) {
			$filterArray[] = "CCC.LpuBuilding_id = :LpuBuilding_id";
			$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
		} else {
			$callObject->load->model("CmpCallCard_model4E", "CmpCallCard_model4E");
			$lpuBuilding = $callObject->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			if (!empty($lpuBuilding[0]) && !empty($lpuBuilding[0]["LpuBuilding_id"])) {
				$data["LpuBuilding_id"] = $lpuBuilding[0]["LpuBuilding_id"];
				$operLpuBuilding = $callObject->CmpCallCard_model4E->getOperDepartament($data);
				if (!empty($operLpuBuilding["LpuBuilding_pid"]) && $operLpuBuilding["LpuBuilding_pid"] == $data["LpuBuilding_id"]) {
					//опер отдел
					$smpUnitsNested = $callObject->CmpCallCard_model4E->loadSmpUnitsNested($data, true);
					if (!empty($smpUnitsNested)) {
						$list = [];
						foreach ($smpUnitsNested as $value) {
							$list[] = $value["LpuBuilding_id"];
						}
						$list_str = implode(",", $list);
						$filterArray[] = "(CCC.LpuBuilding_id is null or CCC.LpuBuilding_id in ($list_str))";
					}
				} else {
					//подчиненные подстанции
					$filterArray[] = "(CCC.LpuBuilding_id is null or CCC.LpuBuilding_id = :LpuBuilding_id)";
					$queryParams["LpuBuilding_id"] = $data["LpuBuilding_id"];
				}
			}
		}
		*/
		$filterArray[] = "CCC.Lpu_id = :Lpu_id";
		
		if ( !empty($data['Lpu_id']) ) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}else{
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}
		
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$query = "
			select
				CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
			    CCC.CmpCallCard_id as \"CmpCallCard_id\",
			    CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			    to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
			    to_char(CCC.CmpCallCard_HospitalizedTime, '{$callObject->dateTimeForm113}') as \"CmpCallCard_HospitalizedTime\",
			    CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
			    coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
			    rtrim(coalesce(CLD.Diag_Code, '')||' '||coalesce(CLD.Diag_Name, '')) as \"CmpDiag_Name\",
			    rtrim(coalesce(EPSD.Diag_Code, '')||' '||coalesce(EPSD.Diag_Name, '')) as \"StacDiag_Name\",
			    rtrim(coalesce(L.Lpu_Nick, '')||' '||coalesce(LS.LpuSection_Name,'')) as \"Stac_Name\"
			from
				v_CmpCallCard CCC
				left join v_EvnPS EPS  on EPS.CmpCallCard_id = CCC.CmpCallCard_id
				left join dbo.v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD on CLC.Diag_id = CLD.Diag_id
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_Diag EPSD on EPS.Diag_pid = EPSD.Diag_id
				left join v_LpuSection LS on LS.LpuSection_id = EPS.LpuSection_id
				left join v_Lpu L on L.Lpu_id = EPS.Lpu_id
			{$whereString}
			order by CCC.cmpcallcard_prmdt
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
	public static function loadSMPHeadDutyWorkPlace(CmpCallCard_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [];
		if (!empty($data["Search_SurName"])) {
			$filterArray[] = "coalesce(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams["Person_SurName"] = $data["Search_SurName"] . "%";
		}
		if (!empty($data["Search_FirName"])) {
			$filterArray[] = "coalesce(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams["Person_FirName"] = $data["Search_FirName"] . "%";
		}
		if (!empty($data["Search_SecName"])) {
			$filterArray[] = "coalesce(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams["Person_SecName"] = $data["Search_SecName"] . "%";
		}
		if (!empty($data["Search_BirthDay"])) {
			$filterArray[] = "coalesce(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
			$queryParams["Person_BirthDay"] = $data["Search_BirthDay"];
		}
		if (!empty($data["CmpLpu_id"])) {
			$filterArray[] = "CCC.CmpLpu_id = :CmpLpu_id";
			$queryParams["CmpLpu_id"] = $data["CmpLpu_id"];
		}
		if (!empty($data["CmpCallCard_Ngod"])) {
			$filterArray[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filterArray[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		$isToday = strtotime($data["begDate"]) == mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		if (!empty($data["begDate"]) && !empty($data["endDate"]) && ($data["begDate"] == $data["endDate"]) && (!empty($data["hours"])) && $isToday) {
			$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate - (1||' days')::interval";
			$filterArray[] = "CCC.CmpCallCard_prmDT > tzgetdate() + (:hours::integer||' hours')::interval";
			$queryParams["begDate"] = $data["begDate"];
			switch ($data["hours"]) {
				case "1":
				case "2":
				case "3":
				case "6":
				case "12":
				case "24":
					$queryParams["hours"] = "-" . $data["hours"];
					break;
				default:
					$queryParams["hours"] = "-24";
					break;
			}
		} else {
			if (!empty($data["begDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date >= :begDate";
				$queryParams["begDate"] = $data["begDate"];
			}

			if (!empty($data["endDate"])) {
				$filterArray[] = "CCC.CmpCallCard_prmDT::date <= :endDate";
				$queryParams["endDate"] = $data["endDate"];
			}
		}
		if (!empty($data["dispatchCallPmUser_id"])) {
			$filterArray[] = "CCC.pmUser_insID = :dispatchCallPmUser_id";
			$queryParams["dispatchCallPmUser_id"] = $data["dispatchCallPmUser_id"];
		}
		if (!empty($data["EmergencyTeam_id"])) {
			$filterArray[] = "CCC.EmergencyTeam_id = :EmergencyTeam_id";
			$queryParams["EmergencyTeam_id"] = $data["EmergencyTeam_id"];
		}
		//Для получения изменений одного талона вызова
		if (!empty($data["CmpCallCard_id"])) {
			$filterArray[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
		}
		$filterArray[] = "CCC.Lpu_id = :Lpu_id";
		$queryParams["Lpu_id"] = $data["session"]["lpu_id"];

		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				coalesce(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",
				coalesce(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",
				coalesce(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",
				CCC.pmUser_insID as \"pmUser_insID\",
				to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				case when coalesce(CCCLL.CmpCallCardLockList_id, 0) = 0 then 0 else 1 end as \"CmpCallCard_isLocked\",
				case when coalesce(CCCLL.CmpCallCardLockList_id, 0) = 0
					then coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
					else '<img src=\"../img/grid/lock.png\">'||coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				end as \"Person_FIO\",
				to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
				rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
				rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
				rtrim(coalesce(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\",
				rtrim(coalesce(CLD.Diag_Code, '')||' '||coalesce(CLD.Diag_Name, '')) as \"CmpDiag_Name\",
				rtrim(coalesce(D.Diag_Code, '')) as \"StacDiag_Name\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
			    case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
					then to_char(
					    (select coalesce((select DS.DataStorage_Value from DataStorage DS where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0 limit 1), 20)) - (DATEDIFF('mi', CCC.CmpCallCard_updDT, tzgetdate())||' minutes')::interval,
					    '{$callObject->dateTimeForm108}'
					)
					else '00'||':'||'00'
				end as \"PPD_WaitingTime\",
				SLPU.Lpu_Nick as \"SendLpu_Nick\",
				coalesce(RGN.KLRgn_FullName, '')||
					case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end||
					case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end||
					case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
					case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end||
					case when CCC.CmpCallCard_Comm is not null then '</br>'||CCC.CmpCallCard_Comm else '' end||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else ''
				end as \"Adress_Name\",
				case when CCC.CmpCallCardStatusType_id = 3
					then
						case when coalesce(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0
							then CCC.CmpCallCardStatus_Comment
							else CCCStatusHist.CmpMoveFromNmpReason_Name
						end
					when CCC.CmpCallCardStatusType_id = 5 then
						CCC.CmpCallCardStatus_Comment
					when CCC.CmpCallCardStatusType_id = 4 then
						case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end||
						case when RC.ResultClass_Name is not null then '<br />Результат: '||RC.ResultClass_Name else '' end||
						case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
				end	as \"PPDResult\",
				to_char(ServeDT.ServeDT, '{$callObject->dateTimeForm104}') as \"ServeDT\",
				case when CCC.CmpCallCardStatusType_id in (2, 3, 4) then PMC.PMUser_Name||to_char(CCC.CmpCallCard_updDT, '{$callObject->dateTimeForm104}') else '' end as \"PPDUser_Name\",
				case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id is null then 1
							when CCC.Lpu_ppdid is null then
								case
									when CCC.CmpCallCardStatusType_id in (1, 2) then CCC.CmpCallCardStatusType_id + 1
									when CCC.CmpCallCardStatusType_id = 4 then 4
									when CCC.CmpCallCardStatusType_id = 6 then 10
									when CCC.CmpCallCardStatusType_id = 3 then 8
									when CCC.CmpCallCardStatusType_id = 7 then 11
									when CCC.CmpCallCardStatusType_id = 8 then 12
									when CCC.CmpCallCardStatusType_id = 16 then 13
									when CCC.CmpCallCardStatusType_id = 18 then 14
									when CCC.CmpCallCardStatusType_id = 19 then 15
									when CCC.CmpCallCardStatusType_id = 20 then 16
									else CCC.CmpCallCardStatusType_id + 4
								end
							else
								case
									when CmpCallCardStatusType_id = 4 then 7
									when CmpCallCardStatusType_id = 3 then 8
									when CCC.CmpCallCardStatusType_id = 7 then 11
									when CCC.CmpCallCardStatusType_id = 8 then 12
									when CCC.CmpCallCardStatusType_id = 16 then 13
									when CCC.CmpCallCardStatusType_id = 18 then 14
									when CCC.CmpCallCardStatusType_id = 19 then 15
									when CCC.CmpCallCardStatusType_id = 20 then 16
									else CCC.CmpCallCardStatusType_id + 4
								end
							end
					else 9
				end as \"CmpGroup_id\",
				case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id is null then '01'
							when CCC.Lpu_ppdid is null then
								case
									when CCC.CmpCallCardStatusType_id in (1, 2) then '0'||(CCC.CmpCallCardStatusType_id + 1)::varchar
									when CCC.CmpCallCardStatusType_id = 3 then '08'
									when CCC.CmpCallCardStatusType_id = 4 then '04'
									when CCC.CmpCallCardStatusType_id = 6 then '10'
									when CCC.CmpCallCardStatusType_id = 7 then '11'
									when CCC.CmpCallCardStatusType_id = 8 then '12'
									when CCC.CmpCallCardStatusType_id = 16 then '13'
									when CCC.CmpCallCardStatusType_id = 18 then '14'
									when CCC.CmpCallCardStatusType_id = 19 then '15'
									when CCC.CmpCallCardStatusType_id = 20 then '16'
									else right('0'||(CCC.CmpCallCardStatusType_id + 4)::varchar, 2)
								end
							else
								case
									when CCC.CmpCallCardStatusType_id = 4 then '07'
									when CCC.CmpCallCardStatusType_id = 3 then '08'
									when CCC.CmpCallCardStatusType_id = 6 then '10'
									when CCC.CmpCallCardStatusType_id = 7 then '11'
									when CCC.CmpCallCardStatusType_id = 8 then '12'
									when CCC.CmpCallCardStatusType_id = 16 then '13'
									when CCC.CmpCallCardStatusType_id = 18 then '14'
									when CCC.CmpCallCardStatusType_id = 19 then '15'
									when CCC.CmpCallCardStatusType_id = 20 then '16'
									else right('0'||(CCC.CmpCallCardStatusType_id + 4)::varchar, 2)
								end
							end
					else '09'
				end as \"CmpGroupName_id\",
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as \"PersonQuarantine_IsOn\",
				to_char(PQ.PersonQuarantine_begDT, 'DD.MM.YYYY') as \"PersonQuarantine_begDT\"
			from
				v_CmpCallCard CCC
				left join lateral (
					select CmpCallCardStatus_insDT as ServeDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 4
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ServeDT on true
				left join lateral (
					select CmpCallCardStatus_insDT as ToDT
					from v_CmpCallCardStatus
					where CmpCallCardStatusType_id = 2
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ToDT on true
				left join lateral (
					select
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus
						left join v_CmpMoveFromNmpReason on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where CmpCallCardStatusType_id = 3
					  and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as CCCStatusHist on true
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L on L.Lpu_id = CCC.CmpLpu_id
				left join CmpDiag CD on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU on SLPU.Lpu_id = CCC.Lpu_ppdid
				left join lateral (
					select *
					from v_EvnPL AS t1
					where t1.CmpCallCard_id = CCC.CmpCallCard_id
					  and t1.Lpu_id = CCC.Lpu_ppdid
					  and t1.EvnPL_setDate >= CCC.CmpCallCard_prmDT::date
					  and CCC.Lpu_ppdid is not null
					limit 1
				) EPL on true
				left join v_Diag EPLD on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
				left join v_EmergencyTeam ET on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join {$callObject->schema}.v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD on CLC.Diag_id = CLD.Diag_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_CmpCallCardlockList CCCLL on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id and (60 - datediff('ss', CCCLL.CmpCallCardLockList_updDT, tzgetdate())) > 0
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join lateral (
					select PQ.*
					from v_PersonQuarantine PQ
					where PQ.Person_id = CCC.Person_id 
					and PQ.PersonQuarantine_endDT is null
					limit 1
				) PQ on true
			{$whereString}
			order by
				(case when coalesce(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case when CCC.CmpCallCardStatusType_id in (1, 2, 3, 4)
							then CCC.CmpCallCardStatusType_id + 2
							else case when CCC.Lpu_ppdid is not null then 1 else 2 end
						end
					else 7
				end),
				CCC.CmpCallCard_prmDT desc
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
	public static function loadSMPHeadBrigWorkPlace(CmpCallCard_model $callObject, $data)
	{
		if (empty($data["session"]["medpersonal_id"])) {
			return false;
		}
		$filterAray = [
			"ET.EmergencyTeam_HeadShift = :MedPersonal_id",
			"CCC.CmpCallCardStatusType_id > 0"
		];
		$queryParams = ["MedPersonal_id" => $data["session"]["medpersonal_id"]];
		if (!empty($data["Search_SurName"])) {
			$filterAray[] = "coalesce(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams["Person_SurName"] = $data["Search_SurName"] . "%";
		}
		if (!empty($data["Search_FirName"])) {
			$filterAray[] = "coalesce(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams["Person_FirName"] = $data["Search_FirName"] . "%";
		}
		if (!empty($data["Search_SecName"])) {
			$filterAray[] = "coalesce(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams["Person_SecName"] = $data["Search_SecName"] . "%";
		}
		if (!empty($data["Search_BirthDay"])) {
			$filterAray[] = "coalesce(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
			$queryParams["Person_BirthDay"] = $data["Search_BirthDay"];
		}
		if (!empty($data["CmpLpu_id"])) {
			$filterAray[] = "CCC.Lpu_hid = :CmpLpu_id";
			$queryParams["CmpLpu_id"] = $data["CmpLpu_id"];
		}
		if (!empty($data["CmpCallCard_Ngod"])) {
			$filterAray[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams["CmpCallCard_Ngod"] = $data["CmpCallCard_Ngod"];
		}
		if (!empty($data["CmpCallCard_Numv"])) {
			$filterAray[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams["CmpCallCard_Numv"] = $data["CmpCallCard_Numv"];
		}
		if (!empty($data["session"]["CurrentEmergencyTeam_id"])) {
			$filterAray[] = "ET.EmergencyTeam_id = :EmergencyTeam_id";
			$queryParams["EmergencyTeam_id"] = $data["session"]["CurrentEmergencyTeam_id"];
		}
		if (!empty($data["begDate"])) {
			$filterAray[] = "CCC.CmpCallCard_prmDT::date >= :begDate";
			$queryParams["begDate"] = $data["begDate"];
		}
		if (!empty($data["endDate"])) {
			$filterAray[] = "CCC.CmpCallCard_prmDT::date <= :endDate";
			$queryParams["endDate"] = $data["endDate"];
		}
		//Для получения изменений одного талона вызова
		if (!empty($data["CmpCallCard_id"])) {
			$filterAray[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams["CmpCallCard_id"] = $data["CmpCallCard_id"];
		}
		$joinString = (!empty($data["session"]["medpersonal_id"])) ? "left join v_EmergencyTeam ET on (CCC.EmergencyTeam_id = ET.EmergencyTeam_id)" : "";
		
		$selectString = "
			CCC.CmpCallCard_id as \"CmpCallCard_id\",
			CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
			PS.Person_id as \"Person_id\",
			PS.PersonEvn_id as \"PersonEvn_id\",
			PS.Server_id as \"Server_id\",
			coalesce(PS.Person_SurName, CCC.Person_SurName) as \"Person_Surname\",
			coalesce(PS.Person_FirName, CCC.Person_FirName) as \"Person_Firname\",
			coalesce(PS.Person_SecName, CCC.Person_SecName) as \"Person_Secname\",
			CCC.pmUser_insID as \"pmUser_insID\",
			to_char(CCC.CmpCallCard_prmDT, '{$callObject->dateTimeForm113}') as \"CmpCallCard_prmDate\",
			CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
			coalesce(PS.Person_Surname, CCC.Person_SurName, '')||' '||coalesce(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '')||' '||coalesce(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
			to_char(coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), '{$callObject->dateTimeForm104}') as \"Person_Birthday\",
			rtrim(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end||coalesce(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
			rtrim(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code::varchar||'. ' else '' end||coalesce(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
			rtrim(coalesce(CLD.Diag_Code, '')||' '||coalesce(CLD.Diag_Name, '')) as \"CmpDiag_Name\",
			rtrim(coalesce(D.Diag_Name, '')) as \"StacDiag_Name\",
			CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
			case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end||
				case when RES.CmpPPDResult_Name is not null then '<br />Результат: '||RES.CmpPPDResult_Name else '' end||
				case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else ''
			end as \"PPDResult\",
			case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
				case when Town.KLTown_FullName is not null then
					case when (City.KLCity_Name is not null) then ', '||lower(Town.KLSocr_Nick)||'. '||Town.KLTown_Name else lower(Town.KLSocr_Nick)||'. '||Town.KLTown_Name end
				else '' end||
				case when Street.KLStreet_FullName is not null then ', '||lower(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else '' end||
				case when CCC.CmpCallCard_Ulic is not null then ', '||CCC.CmpCallCard_Ulic+'. ' else '' end||
				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end||
				case when CCC.CmpCallCard_Comm is not null then '</br>'||CCC.CmpCallCard_Comm else '' end
			as \"Adress_Name\",
			LR.LpuRegion_Name as \"LpuRegion_Name\",
			EPL.EvnPL_id as \"EvnPL_id\",
			to_char(EPL.EvnPL_setDT, '{$callObject->dateTimeForm113}') as \"EvnPL_setDT\",
			case when CCC.CmpCallCardStatusType_id in (2, 4) then ToDT.PMUser_Name||to_char(ToDT.ToDT, '{$callObject->dateTimeForm104}') else '' end as \"PPDUser_Name\",
			case when CmpCallCard_IsOpen = 2 and CCC.CmpCallCardStatusType_id in (6) then 2 else 1 end as \"CmpGroup_id\",
			case when (CmpCallCardStatusType_id=4) then
				case when coalesce(ServeDT.ServeDT, -1) = -1
				    then to_char(CmpCallCard_updDT, '{$callObject->dateTimeForm113}')
					else to_char(ServeDT.ServeDT, '{$callObject->dateTimeForm113}')
				end
			end as \"ServeDT\",
			coalesce(MSF.Person_Fio, '') as \"MedStaffFact_FIO\"
		";
		$fromString = "
			v_CmpCallCard CCC
			left join v_PersonState PS on PS.Person_id = CCC.Person_id
			left join v_CmpReason CR on CR.CmpReason_id = CCC.CmpReason_id
			left join v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
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
			left join v_CmpCloseCard CLC on CCC.CmpCallCard_id = CLC.CmpCallCard_id
			left join v_Diag CLD on CLC.Diag_id = CLD.Diag_id
			left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
			left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
			left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
			left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
			left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
			left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
			left join v_KLSocr socrStreet on Street.KLSocr_id = socrStreet.KLSocr_id
			left join v_pmUserCache PMC on PMC.PMUser_id = CCC.pmUser_updID
			left join v_MedPersonal MSF ON( MSF.MedPersonal_id=EPL.MedPersonal_id )
			left join lateral (
				select EvnPL_setDT as ServeDT
				from v_EvnPL
				where CmpCallCard_id = CCC.CmpCallCard_id
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
			{$joinString}
		";
		$whereString = (count($filterAray) != 0)?"where ".implode(" and ", $filterAray) : "";
		$orderByString = "
			(case when CCC.CmpCallCardStatusType_id = 4 then 3 else CCC.CmpCallCardStatusType_id end),
			LR.LpuRegion_Name,
			CCC.CmpCallCard_prmDT desc
		";
		$query = "
			select {$selectString}
			from {$fromString}
			{$whereString}
			order by {$orderByString}
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
	 * @throws Exception
	 */
	public static function loadSmpUnits(CmpCallCard_model $callObject, $data)
	{
		if (!isset($data["Lpu_id"])) {
			throw new Exception("Не задан обязательный параметр: идентификатор ЛПУ");
		}
		$filterArray = ["LB.LpuBuildingType_id = 27"];

		if (!empty($data["form"]) && $data["form"] == "cmk") {
			$Lpu_ids = $callObject->getSelectedLpuId();
			if (!$Lpu_ids) {
				return false;
			}
			$Lpu_idsString = implode(",", $Lpu_ids);
			$filterArray[] = "LB.Lpu_id in ({$Lpu_idsString})";
		} else {
			$filterArray[] = "LB.Lpu_id = " . $data["Lpu_id"];
		}
		if (!empty($data["SmpUnitType_Code"])) {
			$filterArray[] = "sut.SmpUnitType_Code = " . $data["SmpUnitType_Code"];
		}
		if (isset($data["showOperDpt"]) && $data["showOperDpt"] == 1) {
			$filterArray[] = "sut.SmpUnitType_Code != 4";
		};
		$whereString = (count($filterArray) != 0)?"where ".implode(" and ", $filterArray) : "";
		$query = "
			select distinct
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				LB.LpuBuilding_Nick as \"LpuBuilding_Nick\",
				L.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\"
			from
				v_LpuBuilding LB
				inner join v_SmpUnitParam sup ON LB.LpuBuilding_id = sup.LpuBuilding_id
				left join v_SmpUnitType sut on sut.SmpUnitType_id = sup.SmpUnitType_id
				left join v_Lpu L on L.Lpu_id = LB.Lpu_id
			{$whereString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}
}