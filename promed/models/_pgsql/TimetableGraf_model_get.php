<?php

class TimetableGraf_model_get
{
	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDataMedStafFact(TimetableGraf_model $callObject, $data)
	{
		// За текущую дату на этого человека у этого врача / отделения
		$filter = "MedStaffFact_id = :MedStaffFact_id ";
		$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		$sql = "
			select
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			    ls.LpuUnit_id as \"LpuUnit_id\"
			from v_MedStaffFact msf
			     left join v_LpuSection ls on msf.LpuSection_id = ls.LpuSection_id
			where {$filter}
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (is_object($result)) {
			$result = $result->result("array");
		}
		if (count($result) == 0) {
			return false;
		}
		return [
			"LpuSectionProfile_id" => $result[0]["LpuSectionProfile_id"],
			"LpuUnit_id" => $result[0]["LpuUnit_id"]
		];
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @param bool $OnlyPlan
	 * @return array|bool
	 */
	public static function GetDataPolka(TimetableGraf_model $callObject, $data, $OnlyPlan = false)
	{
		if (empty($data["begDate"])) {
			$begDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d"), date("Y")));
			$endDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d") + 15, date("Y")));
		} else {
			$begDay_id = TimeToDay(strtotime($data["begDate"]));
			$endDay_id = TimeToDay(strtotime($data["endDate"]));
		}
		$filter = "(1 = 1)";
		$params = [];

		$filter .= " and TimetableGraf_Day between :begDay_id and :endDay_id";
		$params["begDay_id"] = $begDay_id;
		$params["endDay_id"] = $endDay_id;
		$params["Lpu_id"] = $data["Lpu_id"];
		if (empty($data["MedPersonal_id"])) {
			$data["MedPersonal_id"] = $data["session"]["medpersonal_id"];
		}
		$params["MedPersonal_id"] = $data["MedPersonal_id"];
		if (empty($data["MedStaffFact_id"])) {
			$params["MedStaffFact_id"] = isset($data["session"]["CurMedStaffFact_id"]) ? $data["session"]["CurMedStaffFact_id"] : $data["session"]["MedStaffFact"][0];
		} else {
			$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		}
		if ((!isset($data["session"]["medpersonal_id"])) || (empty($data["session"]["medpersonal_id"]))) {
			return false; // Только пользовател врач или админ
		}

		$isSearchByEncryp = false;
		$selectPersonData = "
			rtrim(rtrim(p.Person_Surname)||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), '')) as \"Person_FIO\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
			getpersonphones(p.Person_id, '<br />') as \"Person_Phone_all\",
			p.Lpu_id as \"Lpu_id\",
			rtrim(pcard.PersonCard_Code) as \"PersonCard_Code\",				
			rtrim(l.Lpu_Nick) as \"Lpu_Nick\",
			rtrim(pcard.LpuRegion_Name) as \"LpuRegion_Name\",
			to_char(p.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
			age2(p.Person_BirthDay, tzgetdate()) as \"Person_Age\",
			null as \"PersonEncrypHIV_Encryp\",
		";
		if (allowPersonEncrypHIV($data["session"])) {
			$isSearchByEncryp = isSearchByPersonEncrypHIV($data["Person_SurName"]);
			$selectPersonData = "
				case when PEH.PersonEncrypHIV_id is not null
					then coalesce(rtrim(PEH.PersonEncrypHIV_Encryp), '')
					else rtrim(rtrim(p.Person_Surname)||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), ''))
				end as \"Person_FIO\",
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Surname) else rtrim(PEH.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
				case when PEH.PersonEncrypHIV_id is null then getpersonphones(p.Person_id, '<br />') else '' end as \"Person_Phone_all\",
				case when PEH.PersonEncrypHIV_id is null then rtrim(p.Lpu_id) else null end as \"Lpu_id\",
				case when PEH.PersonEncrypHIV_id is null then rtrim(pcard.PersonCard_Code) else '' end as \"PersonCard_Code\",
				case when PEH.PersonEncrypHIV_id is null then rtrim(l.Lpu_Nick) else '' end as \"Lpu_Nick\",
				case when PEH.PersonEncrypHIV_id is null then rtrim(pcard.LpuRegion_Name) else '' end as \"LpuRegion_Name\",
				case when PEH.PersonEncrypHIV_id is null then to_char(p.Person_BirthDay, '{$callObject->dateTimeForm104}') else null end as \"Person_BirthDay\",
				case when PEH.PersonEncrypHIV_id is null then age2(p.Person_BirthDay, tzgetdate()) else null end as \"Person_Age\",
				rtrim(PEH.PersonEncrypHIV_Encryp) as \"PersonEncrypHIV_Encryp\",
			";
		}
		$join = [];
		if (!empty($data["Person_SurName"])) {
			if (allowPersonEncrypHIV($data["session"]) && $isSearchByEncryp) {
				$filter .= " and PEH.PersonEncrypHIV_Encryp ilike (:Person_SurName||'%')";
				$join["PEH"] = " inner join v_PersonEncrypHIV PEH on PEH.Person_id = ttg.Person_id";
			} else {
				$filter .= " and p.Person_SurName ilike (:Person_SurName||'%')";
				$join["P"] = " inner join v_PersonState P on P.Person_id = ttg.Person_id";
			}
			$params["Person_SurName"] = rtrim($data["Person_SurName"]);
		}
		if (!empty($data["Person_FirName"])) {
			$filter .= " and p.Person_FirName ilike (:Person_FirName||'%')";
			$params["Person_FirName"] = rtrim($data["Person_FirName"]);
			$join["P"] = " inner join v_PersonState P on P.Person_id = ttg.Person_id";
		}
		if (!empty($data["Person_SecName"])) {
			$filter .= " and p.Person_SecName ilike (:Person_SecName||'%')";
			$params["Person_SecName"] = rtrim($data["Person_SecName"]);
			$join["P"] = " inner join v_PersonState P on P.Person_id = ttg.Person_id";
		}
		if (!empty($data["Person_BirthDay"])) {
			$filter .= " and p.Person_BirthDay = :Person_BirthDay";
			$params["Person_BirthDay"] = $data["Person_BirthDay"];
			$join["P"] = " inner join v_PersonState P on P.Person_id = ttg.Person_id";
		}
		//В зависимости от профиля врача будем показывать соответствующее прикрепление
		$callObject->load->model("LpuRegion_model", "LpuRegion_model");
		$data["MedStaffFact_id"] = $params["MedStaffFact_id"];
		$params["LpuAttachType_id"] = $callObject->LpuRegion_model->defineLpuAttachTypeId($data);
		if ($OnlyPlan) {
			$filter .= " and TimetableGraf_factTime is null";
		}
		$isPerm = $data["session"]["region"]["nick"] == "perm";
		$isBDZ = "
			CASE WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= tzgetdate()
				THEN 'orange'
				ELSE CASE
					WHEN p.PersonCloseCause_id = 2 and p.Person_closeDT is not null THEN 'red'
					ELSE CASE
						WHEN p.Server_pid = 0 THEN 'true'
						ELSE 'false'
					END
				END
			END as \"Person_IsBDZ\",
		";
		if ($isPerm) {
			$isBDZ = "
				case when p.Server_pid = 0 then 
					case when p.Person_IsInErz = 1
						then 'blue' 
						else
							case when pls.Polis_endDate is not null and pls.Polis_endDate <= tzgetdate()
								then 
									case when p.Person_deadDT is not null
										then 'red'
										else 'yellow'
									end
								else 'true'
							end
						end 
					else 'false'
				end as \"Person_IsBDZ\",
			";
		}
		if ($callObject->getRegionNick() == 'kz') {
			$isBDZ ="
				case
					when pers.Person_IsInFOMS = 1 then 'orange'
					when pers.Person_IsInFOMS = 2 then 'true'
					else 'false'
				end as \"Person_IsBDZ\",
			";
		}

		$needUnion = false;
		if (!empty($data["showLiveQueue"]) && !empty($data["ElectronicService_id"])) {
			$needUnion = true;
		}
		if ($needUnion) {
			$params["ElectronicService_id"] = $data["ElectronicService_id"];
		}
		if (empty($data["MedStaffFactFilterType_id"])) {
			$data["MedStaffFactFilterType_id"] = 3; // Все
		}
		// получаем врачей по замещению
		$msfArray = [];
		// формируем фильтр по дате в зависимости от данных // для задачи #133626
		$filterdate = "";
		$filterdate .= (!empty($data["begDate"])) ? " and MedStaffFactReplace_BegDate <= :begDate " : " and MedStaffFactReplace_BegDate <= tzgetdate() ";
		$filterdate .= (!empty($data["endDate"])) ? " and MedStaffFactReplace_EndDate >= :endDate " : " and MedStaffFactReplace_EndDate <= tzgetdate() ";
		$sql = "
			select distinct MedStaffFact_id as \"MedStaffFact_id\"
			from v_MedStaffFactReplace
			where MedStaffFact_rid = :MedStaffFact_id
				{$filterdate}
		";
		$sqlParams = [
			"MedStaffFact_id" => $params["MedStaffFact_id"],
			"begDate" => !empty($data["begDate"]) ? $data["begDate"] : null,
			"endDate" => !empty($data["endDate"]) ? $data["endDate"] : null
		];
		$resp_msfr = $callObject->queryResult($sql, $sqlParams);
		if (!empty($resp_msfr)) {
			foreach ($resp_msfr as $one_msfr) {
				$msfArray[] = $one_msfr["MedStaffFact_id"];
			}
		}
		switch ($data["MedStaffFactFilterType_id"]) {
			case 1:
				$filterMSF = " and MSF.MedStaffFact_id = :MedStaffFact_id";
				break;
			case 2:
				if (!empty($msfArray)) {
					// врачи по замещению
					$filterMSF = " and MSF.MedStaffFact_id IN ('" . implode("','", $msfArray) . "')";
				} else {
					// нет врачей по замещению
					$filterMSF = " and 1=0";
				}
				break;
			default:
				// свой + врачи по замещению
				$msfArray[] = $params["MedStaffFact_id"];
				$filterMSF = " and MSF.MedStaffFact_id IN ('" . implode("','", $msfArray) . "')";
				break;
		}
		$join_sql = "";
		if (!empty($join)) {
			$join_sql = implode(" ", $join);
		}
		$presql = "";
		if ($needUnion) {
			$presql = "
				select
				    distinct
				    mseq.MedStaffFact_id
				from
				    v_MedServiceElectronicQueue mseq
				    inner join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
				    inner join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
				    inner join v_ElectronicTreatmentLink etl on etl.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
				where etl.ElectronicTreatment_id in (
				        select etlIn.ElectronicTreatment_id
				        from
				        	v_ElectronicTreatmentLink etlIn
				        	inner join v_ElectronicQueueInfo eqiIn on eqiIn.ElectronicQueueInfo_id = etlIn.ElectronicQueueInfo_id
				        	inner join v_ElectronicService esIn2 on esIn2.ElectronicQueueInfo_id = eqiIn.ElectronicQueueInfo_id
				        where esIn2.ElectronicService_id = :ElectronicService_id
				    )
			";
		}
		$sql = "
			SELECT
				ttg.TimetableGraf_id as \"TimetableGraf_id\",
				ttg.MedStaffFact_id as \"MedStaffFact_id\",
				1 as \"liveQueueSort\",
				ttg.TimetableGraf_Day as \"TimetableGraf_Day\",
				ttg.TimetableType_id as \"TimetableType_id\",
				ttg.TimeTableGraf_countRec as \"TimeTableGraf_countRec\",
				ttg.TimeTableGraf_PersRecLim as \"TimeTableGraf_PersRecLim\",
				case when ttg.Person_id is not null
					then coalesce(ttt.TimetableType_SysNick, 'busy')
					else coalesce(ttt.TimetableType_SysNick, 'free')
				end as \"TimetableType_SysNick\",
				coalesce(ttt.TimetableType_Name, '') as \"TimetableType_Name\",
				MSF.LpuSection_id as \"LpuSection_id\",
				ttg.Person_id as \"Person_id\",
				case when exists(
					select * 
					from v_PersonQuarantine PQ
					where PQ.Person_id = ttg.Person_id
					and PQ.PersonQuarantine_endDT is null
				) then 'true' else 'false' end as \"PersonQuarantine_IsOn\",
				case
					when TimetableGraf_begTime is not null then to_char(TimetableGraf_begTime, '{$callObject->dateTimeForm104}')
					when TimetableGraf_factTime is not null then to_char(TimetableGraf_factTime, '{$callObject->dateTimeForm104}')
					else to_char(TimetableGraf_insDT, '{$callObject->dateTimeForm104}')
				end as \"TimetableGraf_Date\",
				coalesce(to_char(TimetableGraf_begTime, 'HH24:MI'), 'б/з') as \"TimetableGraf_begTime\",
				to_char(ttg.TimetableGraf_factTime, '{$callObject->dateTimeForm104}') as \"TimetableGraf_factTime\",
				case when ttg.Person_id is not null then to_char(TimetableGraf_updDT, '{$callObject->dateTimeForm104} HH24:MI') end as \"TimetableGraf_updDT\",
				case when ttg.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else 'Запись через интернет'
					end
				end as \"pmUser_Name\",
				ttg.pmUser_updId as \"pmUser_updId\",
				ttg.pmUser_insId as \"pmUser_insId\",
				case when ed.EvnDirection_isAuto != 2 then 'true' else 'false' end as \"IsEvnDirection\",
				ed.MedPersonal_id as \"MedPersonal_id\",
				ed.MedPersonal_did as \"MedPersonal_did\",
				ed.EvnQueue_id as \"EvnQueue_id\",
				ed.EvnStatus_id as \"EvnStatus_id\",
				MSF.Person_Fin as \"MSF_Person_Fin\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				rtrim(LSP.LpuSectionProfile_Name) as \"LpuSectionProfile_Name\",
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.ARMType_id as \"ARMType_id\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				et.ElectronicService_id as \"ElectronicService_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.EvnDirection_uid as \"EvnDirection_uid\",
				etr.ElectronicService_id as \"toElectronicService_id\",
				etr.ElectronicService_uid as \"fromElectronicService_id\",
				et.ElectronicTreatment_id as \"ElectronicTreatment_id\",
				etre.ElectronicTreatment_Name as \"ElectronicTreatment_Name\",
			    extract(epoch from (et.ElectronicTalon_insDT::timestamp - getdate()::timestamp)) as \"ElectronicTalon_TimeHasPassed\",
				PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
				ACR.AmbulatCardRequest_id as \"AmbulatCardRequest_id\",
				ACR.AmbulatCardRequestStatus_id as \"AmbulatCardRequestStatus_id\",
				ambulatCard.MedStaffFact_id as \"locationMedStaffFact_id\", --у кого находится карта
				visitPerson.TimetableGrafRecList_id as \"TimetableGrafRecList_id\"
			FROM
				v_TimetableGraf_lite ttg
				{$join_sql}
				left join v_ElectronicTalon et on (coalesce(et.EvnDirection_uid, et.EvnDirection_id) = ttg.EvnDirection_id)
				left join v_MedServiceElectronicQueue mseq on mseq.ElectronicService_id = et.ElectronicService_id
				left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicTalonRedirect etr on (etr.ElectronicTalon_id = et.ElectronicTalon_id and (etr.EvnDirection_uid = et.EvnDirection_uid or etr.EvnDirection_uid is null))
				left join v_ElectronicTreatment etre on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				left join v_pmUser pu on pu.pmUser_id = ttg.pmUser_updId
				left join v_TimetableType ttt on ttt.TimetableType_id = ttg.TimetableType_id
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_MedStaffFact ETMSF on ETMSF.MedStaffFact_id = mseq.MedStaffFact_id
				left join v_EvnDirection_all ed on ed.EvnDirection_id = ttg.EvnDirection_id and ed.DirFailType_id is null and ED.EvnStatus_id not in (12,13)
				left join LpuSectionProfile LSP on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
				left join v_PersonAmbulatCard PAC on PAC.PersonAmbulatCard_id = ttg.PersonAmbulatCard_id AND ttg.Person_id = PAC.Person_id
				left join v_AmbulatCardRequest ACR on ACR.TimeTableGraf_id = ttg.TimeTableGraf_id AND ACR.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				left join lateral (
					select
						vPACL.PersonAmbulatCardLocat_id,
					    vPACL.MedStaffFact_id
					from v_PersonAmbulatCardLocat vPACL
					where PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
					order by PersonAmbulatCardLocat_begDate desc
				    limit 1
				) as ambulatCard on true
				left join lateral (
					select ttgrl.TimetableGrafRecList_id
					from TimetableGrafRecList ttgrl
					where ttgrl.TimetableGraf_id = ttg.TimetableGraf_id
					  and ttgrl.TimetableGrafRecList_isGroupFact = 2
				    limit 1
				) as visitPerson on true
			WHERE
				{$filter}
				{$filterMSF}
				and (ttg.TimetableType_id != 12 or ttg.Person_id is not null)
			";
		if ($needUnion) {
			$TimetableGraf_DateString = "
				case
					when TimetableGraf_begTime is not null then to_char(TimetableGraf_begTime, '{$callObject->dateTimeForm104}')
					when TimetableGraf_factTime is not null then to_char(TimetableGraf_factTime, '{$callObject->dateTimeForm104}')
					else to_char(TimetableGraf_insDT, '{$callObject->dateTimeForm104}')
				end
			";
			$sql .= "
					UNION ALL
					SELECT
						ttg.TimetableGraf_id as \"TimetableGraf_id\",
						ttg.MedStaffFact_id as \"MedStaffFact_id\",
						2 as \"liveQueueSort\",
						ttg.TimetableGraf_Day as \"TimetableGraf_Day\",
						ttg.TimetableType_id as \"TimetableType_id\",
						ttg.TimeTableGraf_countRec as \"TimeTableGraf_countRec\",
						ttg.TimeTableGraf_PersRecLim as \"TimeTableGraf_PersRecLim\",
						case
							when ttg.Person_id is not null then coalesce(ttt.TimetableType_SysNick, 'busy')
							else coalesce(ttt.TimetableType_SysNick, 'free') end as \"TimetableType_SysNick\",
						coalesce(ttt.TimetableType_Name, '') as \"TimetableType_Name\",
						MSF.LpuSection_id as \"LpuSection_id\",
						ttg.Person_id as \"Person_id\",
						case when exists(
							select * 
							from v_PersonQuarantine PQ
							where PQ.Person_id = ttg.Person_id
							and PQ.PersonQuarantine_endDT is null
						) then 'true' else 'false' end as \"PersonQuarantine_IsOn\",
						{$TimetableGraf_DateString} as \"TimetableGraf_Date\",
						coalesce(to_char(TimetableGraf_begTime, 'HH24:MI'), 'б/з') as \"TimetableGraf_begTime\",
						to_char(ttg.TimetableGraf_factTime, '{$callObject->dateTimeForm108}') as \"TimetableGraf_factTime\",
						case when ttg.Person_id is not null then to_char(TimetableGraf_updDT, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') end as \"TimetableGraf_updDT\",
						case when ttg.Person_id is not null then
							case
								when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
								else 'Запись через интернет'
							end
						end as \"pmUser_Name\",
						ttg.pmUser_updId as \"pmUser_updId\",
						ttg.pmUser_insId as \"pmUser_insId\",
						case when ed.EvnDirection_isAuto != 2 then 'true' else 'false' end as \"IsEvnDirection\",
						ed.MedPersonal_id as \"MedPersonal_id\",
						ed.MedPersonal_did as \"MedPersonal_did\",
						ed.EvnQueue_id as \"EvnQueue_id\",
						ed.EvnStatus_id as \"EvnStatus_id\",
						MSF.Person_Fin as \"MSF_Person_Fin\",
						ed.EvnDirection_Num as \"EvnDirection_Num\",
						rtrim(LSP.LpuSectionProfile_Name) as \"LpuSectionProfile_Name\",
						ed.EvnDirection_id as \"EvnDirection_id\",
						ed.ARMType_id as \"ARMType_id\",
						et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
						ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
						et.ElectronicService_id as \"ElectronicService_id\",
						et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
						et.ElectronicTalon_id as \"ElectronicTalon_id\",
						et.EvnDirection_uid as \"EvnDirection_uid\",
						etr.ElectronicService_id as \"toElectronicService_id\",
						etr.ElectronicService_uid as \"fromElectronicService_id\",
						et.ElectronicTreatment_id as \"ElectronicTreatment_id\",
						etre.ElectronicTreatment_Name as \"ElectronicTreatment_Name\",
						datediff('ss', et.ElectronicTalon_insDT, getdate()) as \"ElectronicTalon_TimeHasPassed\",
						PAC.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
						PAC.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
						ACR.AmbulatCardRequest_id as \"AmbulatCardRequest_id\",
						ACR.AmbulatCardRequestStatus_id as \"AmbulatCardRequestStatus_id\",
						ambulatCard.MedStaffFact_id as \"locationMedStaffFact_id\", --у кого находится карта
						visitPerson.TimetableGrafRecList_id as \"TimetableGrafRecList_id\"
					FROM
						({$presql}) as preMsf
						inner join v_TimetableGraf_lite ttg on preMsf.MedStaffFact_id = ttg.MedStaffFact_id
						{$join_sql}
						left join v_ElectronicTalon et on et.EvnDirection_id = ttg.EvnDirection_id
						left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
						left join v_ElectronicTalonRedirect etr on (etr.ElectronicTalon_id = et.ElectronicTalon_id and (etr.EvnDirection_uid = et.EvnDirection_uid or etr.EvnDirection_uid is null))
						left join v_ElectronicTreatment etre on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
						left join v_pmUser pu on pu.pmUser_id = ttg.pmUser_updId
						left join v_TimetableType ttt on ttt.TimetableType_id = ttg.TimetableType_id
						left join v_MedStaffFact MSF on MSF.MedStaffFact_id = ttg.MedStaffFact_id
						left join v_EvnDirection_all ed on ed.EvnDirection_id = ttg.EvnDirection_id and ed.DirFailType_id is null and ED.EvnStatus_id not in (12,13)
						left join LpuSectionProfile LSP on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_id
						left join v_PersonAmbulatCard PAC on PAC.PersonAmbulatCard_id = ttg.PersonAmbulatCard_id AND ttg.Person_id = PAC.Person_id
						left join v_AmbulatCardRequest ACR on ACR.TimeTableGraf_id = ttg.TimeTableGraf_id AND ACR.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
						left join lateral (
							select vPACL.PersonAmbulatCardLocat_id, vPACL.MedStaffFact_id
							from v_PersonAmbulatCardLocat vPACL
							where PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
							order by PersonAmbulatCardLocat_begDate desc
							limit 1
						) as ambulatCard on true
						left join lateral (
							select ttgrl.TimetableGrafRecList_id
							from TimetableGrafRecList ttgrl
							where ttgrl.TimetableGraf_id = ttg.TimetableGraf_id
							  and ttgrl.TimetableGrafRecList_isGroupFact = 2
							limit 1
						) as visitPerson on true
					WHERE
						{$filter}
						and MSF.MedStaffFact_id != :MedStaffFact_id
						and ttg.TimetableType_id = 12
						and ttg.Person_id is not null
					ORDER BY
						 \"TimetableGraf_Day\"
						,\"liveQueueSort\"
						,\"TimetableGraf_Date\"
				";
		} else {
			$sql .= "
					ORDER BY
						 \"TimetableGraf_Day\"
						,(
							case
								when TimetableGraf_begTime is not null then to_char(TimetableGraf_begTime, '{$callObject->dateTimeForm104}')
								when TimetableGraf_factTime is not null then to_char(TimetableGraf_factTime, '{$callObject->dateTimeForm104}')
								else to_char(TimetableGraf_insDT, '{$callObject->dateTimeForm104}')
							end
						)
				";
		}
		/**
		 * @var CI_DB_result $res
		 * @var CI_DB_result $result_ps
		 * @var CI_DB_result $result_fer
		 */
		$res = $callObject->db->query($sql, $params, true);
		$FER_PERSON_ID = $callObject->config->item("FER_PERSON_ID");
		if (!is_object($res)) {
			return false;
		}
		$resp = $res->result("array");
		$arrayFromPersonState = [];
		foreach ($resp as &$respone) {
			if (!empty($respone["Person_id"])) {
				$arrayFromPersonState[] = $respone["Person_id"];
			}
		}
		$psData = [];
		if (!empty($arrayFromPersonState)) {
			// делаем запрос в PersonState
			$joinPEH = "";
			if (allowPersonEncrypHIV($data["session"])) {
				$joinPEH = ($isSearchByEncryp ? "inner" : "left") . " join v_PersonEncrypHIV PEH on PEH.Person_id = p.Person_id";
			}
			$arrayFromPersonStateString = implode("','", $arrayFromPersonState);
			$selectString = "
				    p.Person_id as \"Person_id\",
				    p.PersonEvn_id as \"PersonEvn_id\",
				    p.Server_id as \"Server_id\",
				    {$selectPersonData}
				    {$isBDZ}
				    ambulatCard.PersonAmbulatCard_id as \"PersonAmbulatCard_id\",
				    ambulatCard.PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\",
				    ambulatCard.MedStaffFact_id as \"locationMedStaffFact_id\",
				    pers.Person_IsUnknown as \"Person_IsUnknown\",
				    case when p.Person_IsFedLgot = 1 or p.Person_IsRegLgot = 1 then 'true' else 'false' end as \"Person_IsLgot\",
				    CASE WHEN p.Person_IsFedLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsFedLgot\",
				    CASE WHEN p.Person_IsRegLgot = 1 THEN 'true' ELSE 'false' END as \"Person_IsRegLgot\"
			";
			$query = "
				select {$selectString}
				from v_PersonState_all p
				     left join v_Polis pls on pls.Polis_id = p.Polis_id
				     left join v_Person pers on pers.Person_id = p.Person_id
				     left join lateral (
				         select
				                pc.Person_id as PersonCard_Person_id,
				                pc.Lpu_id,
				                pc.LpuRegion_id,
				                pc.LpuRegion_Name,
				                case when pc.LpuAttachType_id = 1 then pc.PersonCard_Code else null end as PersonCard_Code
				         from v_PersonCard pc
				         where pc.Person_id = p.Person_id
				           and LpuAttachType_id = :LpuAttachType_id
				         order by PersonCard_begDate desc
				         limit 1
				     ) as pcard on true
				     left join lateral (
				         select
				                PAC.PersonAmbulatCard_id,
				                PAC.PersonAmbulatCard_Num,
				                ACLB.LpuBuilding_id,
				                PACL.MedStaffFact_id
				         from v_PersonAmbulatCard PAC
				              left join v_PersonAmbulatCardLocat PACL on PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
				              left join v_AmbulatCardLpuBuilding ACLB on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				         where PAC.Person_id = p.Person_id
				           and PAC.Lpu_id = :Lpu_id
				           and tzgetdate() between coalesce(ACLB.AmbulatCardLpuBuilding_begDate, tzgetdate()) and coalesce(ACLB.AmbulatCardLpuBuilding_endDate, tzgetdate())
				         order by
				                  PACL.PersonAmbulatCardLocat_begDate desc,
				                  PAC.PersonAmbulatCard_id desc
				         limit 1
				     ) as ambulatCard on true
				    left join v_LpuRegion LpuRegion on LpuRegion.LpuRegion_id = pcard.LpuRegion_id
				    left outer join v_Lpu l on l.Lpu_id = pcard.Lpu_id
				    {$joinPEH}
				where p.Person_id in ('{$arrayFromPersonStateString}')
			";
			$queryParams = [
				"LpuAttachType_id" => $params["LpuAttachType_id"],
				"Lpu_id" => $data["Lpu_id"]
			];
			$result_ps = $callObject->db->query($query, $queryParams);
			if (is_object($result_ps)) {
				$resp_ps = $result_ps->result("array");
				foreach ($resp_ps as $one_ps) {
					$psData[$one_ps["Person_id"]] = $one_ps;
				}
			}
		}
		$arrayFromSlot = [];
		foreach ($resp as &$respone) {
			if (!empty($psData[$respone["Person_id"]])) {
				$one_ps = $psData[$respone["Person_id"]];
				$respone["PersonEvn_id"] = $one_ps["PersonEvn_id"];
				$respone["Server_id"] = $one_ps["Server_id"];
				$respone["Person_IsUnknown"] = $one_ps["Person_IsUnknown"];
				$respone["Person_Surname"] = $one_ps["Person_Surname"];
				$respone["Person_Firname"] = $one_ps["Person_Firname"];
				$respone["Person_Secname"] = $one_ps["Person_Secname"];
				$respone["Person_FIO"] = ($respone["TimetableType_id"] == 14) ? "ГРУППОВОЙ ПРИЁМ" : $one_ps["Person_FIO"];
				$respone["Person_BirthDay"] = $one_ps["Person_BirthDay"];
				$respone["Person_Age"] = $one_ps["Person_Age"];
				$respone["Person_Phone_all"] = $one_ps["Person_Phone_all"];
				$respone["PersonCard_Code"] = $one_ps["PersonCard_Code"];
				$respone["PersonAmbulatCard_id"] = (!empty($respone["PersonAmbulatCard_id"])) ? $respone["PersonAmbulatCard_id"] : $one_ps["PersonAmbulatCard_id"];
				$respone["PersonAmbulatCard_Num"] = (!empty($respone["PersonAmbulatCard_Num"])) ? $respone["PersonAmbulatCard_Num"] : $one_ps["PersonAmbulatCard_Num"];
				$respone["locationMedStaffFact_id"] = (!empty($respone["PersonAmbulatCard_id"])) ? $respone["locationMedStaffFact_id"] : $one_ps["locationMedStaffFact_id"];
				$respone["Lpu_id"] = $one_ps["Lpu_id"];
				$respone["Lpu_Nick"] = $one_ps["Lpu_Nick"];
				$respone["LpuRegion_Name"] = $one_ps["LpuRegion_Name"];
				$respone["Person_IsBDZ"] = $one_ps["Person_IsBDZ"];
				$respone["Person_IsFedLgot"] = $one_ps["Person_IsFedLgot"];
				$respone["Person_IsRegLgot"] = $one_ps["Person_IsRegLgot"];
				$respone["PersonEncrypHIV_Encryp"] = $one_ps["PersonEncrypHIV_Encryp"];
			}
			if (empty($respone["PersonEncrypHIV_Encryp"]) && !empty($respone["TimetableGraf_id"]) && !empty($respone["Person_id"]) && !empty($FER_PERSON_ID) && $FER_PERSON_ID == $respone["Person_id"]) {
				$arrayFromSlot[] = $respone["TimetableGraf_id"];
			}
		}
		$slotData = [];
		if (!empty($arrayFromSlot)) {
			// делаем запрос в fer.slot
			$selectSlotArray = [
				"Slot_id as \"Slot_id\"",
				"Slot_SurName as \"Person_Surname\"",
				"Slot_FirName as \"Person_Firname\"",
				"Slot_SecName as \"Person_Secname\"",
				"Slot_SurName||' '||coalesce(Slot_FirName, '')||' '||coalesce(Slot_SecName, '') as \"Person_FIO\"",
				"TimetableGraf_id as \"TimetableGraf_id\""
			];
			$arrayFromSlotString = implode("','", $arrayFromSlot);
			$selectSlotString = implode(", ", $selectSlotArray);
			$fromSlotString = "fer.v_Slot";
			$whereString = "TimetableGraf_id in ('{$arrayFromSlotString}')";
			$query = "
				select {$selectSlotString}
				from {$fromSlotString}
				where {$whereString}
			";
			$result_fer = $callObject->db->query($query);
			if (is_object($result_fer)) {
				$resp_fer = $result_fer->result("array");
				foreach ($resp_fer as $one_fer) {
					$slotData[$one_fer["TimetableGraf_id"]] = $one_fer;
				}
			}
		}
		foreach ($resp as &$respone) {
			if (!empty($slotData[$respone["TimetableGraf_id"]])) {
				$one_fer = $slotData[$respone["TimetableGraf_id"]];
				$respone["Person_Surname"] = $one_fer["Person_Surname"];
				$respone["Person_Firname"] = $one_fer["Person_Firname"];
				$respone["Person_Secname"] = $one_fer["Person_Secname"];
				$respone["Person_FIO"] = ($respone["TimetableType_id"] == 14) ? "ГРУППОВОЙ ПРИЁМ" : $one_fer["Person_FIO"];
				$respone["Person_BirthDay"] = "";
				$respone["Person_Age"] = "";
				$respone["Person_Phone_all"] = "";
				$respone["PersonCard_Code"] = "";
				$respone["Lpu_Nick"] = "";
				$respone["LpuRegion_Name"] = "";
				$respone["IsEvnDirection"] = "false";
				$respone["Person_IsBDZ"] = "false";
				$respone["Person_IsFedLgot"] = "false";
				$respone["Person_IsRegLgot"] = "false";
			}
		}
		return $resp;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetDataStac(TimetableGraf_model $callObject, $data)
	{
		if (empty($data["begDate"])) {
			$begDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d"), date("Y")));
			$endDay_id = TimeToDay(mktime(0, 0, 0, date("m"), date("d") + 15, date("Y")));
		} else {
			$begDay_id = TimeToDay(strtotime($data["begDate"]));
			$endDay_id = TimeToDay(strtotime($data["endDate"]));
		}
		$params = [];
		$params["begDay_id"] = $begDay_id;
		$params["endDay_id"] = $endDay_id;
		$params["Lpu_id"] = $data["Lpu_id"];

		$selectArray = [];
		$fromArray = [];
		$whereArray = [];
		$orderArray = [
			"\"TimetableStac_Day\"",
			"\"LpuSectionBedType_id\""
		];
		$whereArray[] = "t.TimetableType_id != 6";
		if (empty($data["LpuSection_id"])) {
			return false;
		}
		$params["LpuSection_id"] = $data["LpuSection_id"];
		$whereArray[] = "t.LpuSection_id = :LpuSection_id";
		$whereArray[] = "t.TimetableStac_Day between :begDay_id and :endDay_id";

		$selectArray[] = "t.pmUser_updId as \"pmUser_updId\"";
		$selectArray[] = "t.pmUser_insId as \"pmUser_insId\"";
		$selectArray[] = "t.TimetableStac_id as \"TimetableStac_id\"";
		$selectArray[] = "t.TimetableStac_Day as \"TimetableStac_Day\"";
		$selectArray[] = "t.LpuSectionBedType_id as \"LpuSectionBedType_id\"";
		$selectArray[] = "t.Person_id as \"Person_id\"";

		$fromArray[] = "v_TimetableStac_lite t";
		$fromArray[] = "left outer join LpuSection ls on ls.LpuSection_id = t.LpuSection_id";
		$fromArray[] = "left join v_PersonState_all p on p.Person_id = t.Person_id";
		$fromArray[] = "left join v_pmUser pu on pu.pmUser_id = t.pmUser_updId";
		$fromArray[] = "
			left join lateral (
			    select pc.Person_id as PersonCard_Person_id
			          ,pc.Lpu_id
			          ,pc.LpuRegion_id
			          ,pc.LpuRegion_Name
			    from v_PersonCard pc
			    where pc.Person_id = p.Person_id
			      and LpuAttachType_id = 1
			    order by PersonCard_begDate desc
			    limit 1
			) as pcard on true
		";
		$fromArray[] = "left join v_LpuRegion LpuRegion on LpuRegion.LpuRegion_id = pcard.LpuRegion_id";
		$fromArray[] = "left join v_TimetableType ttt on ttt.TimetableType_id = t.TimetableType_id";
		$fromArray[] = "left join v_EvnQueue q on t.TimetableStac_id = q.TimetableStac_id and t.Person_id = q.Person_id";
		$fromArray[] = "left join LpuSectionBedType lsbt on lsbt.LpuSectionBedType_id = t.LpuSectionBedType_id";
		if (allowPersonEncrypHIV($data["session"])) {
			$fromArray[] = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";

			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null then p.Person_Phone end as \"Person_Phone\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(rtrim(p.Person_Surname)||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), '')) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_FIO\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null and t.Person_id is not null then to_char(p.Person_BirthDay, '{$callObject->dateTimeForm104}') end as \"Person_BirthDay\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null and t.Person_id is not null then Age2(p.Person_BirthDay, TimetableStac_updDT) end as \"Person_Age\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null then p.Lpu_id end as \"Lpu_id\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Lpu_Nick) else '' end as \"Lpu_Nick\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(pcard.LpuRegion_Name) else '' end as \"LpuRegion_Name\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\"";
			$selectArray[] = "case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\"";
		} else {
			$selectArray[] = "p.Person_Phone as \"Person_Phone\"";
			$selectArray[] = "rtrim(rtrim(p.Person_Surname)||' '||coalesce(rtrim(p.Person_Firname), '')||' '||coalesce(rtrim(p.Person_Secname), '')) as \"Person_FIO\"";
			$selectArray[] = "case when t.Person_id is not null then to_char(p.Person_BirthDay, '{$callObject->dateTimeForm104}') end as \"Person_BirthDay\"";
			$selectArray[] = "case when t.Person_id is not null then Age2(p.Person_BirthDay, TimetableStac_updDT) end as \"Person_Age\"";
			$selectArray[] = "p.Lpu_id as \"Lpu_id\"";
			$selectArray[] = "rtrim(p.Lpu_Nick) as \"Lpu_Nick\"";
			$selectArray[] = "rtrim(pcard.LpuRegion_Name) as \"LpuRegion_Name\"";
			$selectArray[] = "rtrim(p.Person_Firname) as \"Person_Firname\"";
			$selectArray[] = "rtrim(p.Person_Surname) as \"Person_Surname\"";
			$selectArray[] = "rtrim(p.Person_Secname) as \"Person_Secname\"";
		}
		$selectArray[] = "t.TimetableType_id as \"TimetableType_id\"";
		$selectArray[] = "
			case when t.Person_id is not null
				then coalesce(ttt.TimetableType_SysNick, 'busy')
				else coalesce(ttt.TimetableType_SysNick, 'free')
			end as \"TimetableType_SysNick\"		
		";
		$selectArray[] = "coalesce(ttt.TimetableType_Name, '') as \"TimetableType_Name\"";
		$selectArray[] = "lsbt.LpuSectionBedType_Name as \"LpuSectionBedType_Name\"";
		$selectArray[] = "to_char(t.TimetableStac_setDate, '{$callObject->dateTimeForm104}') as \"TimetableStac_Date\"";
		$selectArray[] = "
			case when p.Person_IsFedLgot = 1 or p.Person_IsRegLgot = 1
				then 'true'
				else 'false'
			end as \"Person_IsLgot\"
		";
		$selectArray[] = "
			case when p.Person_IsBDZ = 1
				then 'true'
				else 'false'
			end as \"Person_IsBDZ\"
		";
		$selectArray[] = "
			case when t.Person_id is not null
				then to_char(TimetableStac_updDT, '{$callObject->dateTimeForm104}')||' '||to_char(TimetableStac_updDT, '{$callObject->dateTimeForm108}')
			end as \"TimetableStac_updDT\"
		";
		$selectArray[] = "
			case when t.Person_id is not null then
				case when pu.pmUser_id is not null
					then rtrim(pu.pmUser_Name)
					else 'Запись через интернет'
				end
			end as \"pmUser_Name\"
		";
		$selectArray[] = "ls.LpuSectionHospType_id as \"LpuSectionHospType_id\"";
		$selectArray[] = "q.EvnQueue_insDT as \"EvnQueue_insDT\"";

		$selectString = implode(", ", $selectArray);
		$fromString = implode(" ", $fromArray);
		$whereString = implode(" and ", $whereArray);
		$orderString = implode(", ", $orderArray);
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
			order by {$orderString}
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @param bool $OnlyPlan
	 * @return array|bool
	 */
	public static function getListByDay(TimetableGraf_model $callObject, $data, $OnlyPlan = false)
	{
		switch ($data["LpuUnitType_SysNick"]) {
			case "polka":
				return $callObject->GetDataPolka($data, $OnlyPlan);
				break;
			case "stac":
			case "dstac":
			case "hstac":
			case "pstac":
				return $callObject->GetDataStac($data);
				break;
		}
		return false;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return bool|mixed
	 */
	public static function getDoctorRoom(TimetableGraf_model $callObject, $data)
	{
		if (empty($data["MedStaffFact_id"])) {
			return false;
		}
		$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
		$datetime = (!empty($data["datetime"])) ? ":recordDate" : "tzgetdate()";
		if (!empty($data["datetime"])) {
			$params["recordDate"] = $data["datetime"];
		}
		$query = "
			select lbo.LpuBuildingOffice_Number as \"LpuBuildingOffice_Number\"
			from v_LpuBuildingOfficeMedStaffLink lboml
			     left join v_LpuBuildingOffice lbo on lbo.LpuBuildingOffice_id = lboml.LpuBuildingOffice_id
			     -- вариант когда указано время на дне
			     left join lateral (
			         select lbovtoa.LpuBuildingOfficeMedStaffLink_id
			         from v_LpuBuildingOfficeVizitTime lbovtoa
			         where lbovtoa.LpuBuildingOfficeMedStaffLink_id in(
			                select LpuBuildingOfficeMedStaffLink_id
			                from v_LpuBuildingOfficeMedStaffLink lboml2
			                where lboml2.LpuBuildingOfficeMedStaffLink_begDate <= {$datetime}
			                  and coalesce(lboml2.LpuBuildingOfficeMedStaffLink_endDate, '2030-01-01') >= {$datetime}
			                  and lboml2.MedStaffFact_id = :MedStaffFact_id
			             )
			           and lbovtoa.CalendarWeek_id = date_part('dow', {$datetime})
			           and lbovtoa.LpuBuildingOfficeVizitTime_begDate::time <= {$datetime}::time
			           and lbovtoa.LpuBuildingOfficeVizitTime_endDate::time >= {$datetime}::time
			         limit 1
			    ) as mainRoom on true
			    -- вариант когда на дне время не указано, но связь кабинета и врача есть (первый попавшийся)
			    left join lateral (
			         select lbomloa.LpuBuildingOfficeMedStaffLink_id
			         from v_LpuBuildingOfficeMedStaffLink lbomloa
			         where lbomloa.LpuBuildingOfficeMedStaffLink_begDate <= {$datetime}
			           and coalesce(lbomloa.LpuBuildingOfficeMedStaffLink_endDate, '2030-01-01') >= {$datetime}
			           and lbomloa.MedStaffFact_id = :MedStaffFact_id
			         limit 1
			    ) as reserveRoom on true
			where coalesce(mainRoom.LpuBuildingOfficeMedStaffLink_id, reserveRoom.LpuBuildingOfficeMedStaffLink_id) = lboml.LpuBuildingOfficeMedStaffLink_id
			limit 1
		";
		/**@var array $result */
		$result = $callObject->queryResult($query, $params);
		if (empty($result[0]["LpuBuildingOffice_Number"])) {
			return false;
		}
		return $result[0]["LpuBuildingOffice_Number"];
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getTimeTableGrafStatus(TimetableGraf_model $callObject, $data)
	{
		$query = "
			select
				EvnStatus_id as \"EvnStatus_id\",
				ttg.MedStaffFact_id as \"MedStaffFact_id\",
				ttg.TimeTableGraf_begTime as \"TimeTableGraf_begTime\"
			from v_TimeTableGraf_lite ttg
				 inner join v_EvnDirection_all ed on ed.EvnDirection_id = ttg.EvnDirection_id
			where ttg.Person_id = :Person_id
			  and ttg.TimetableGraf_id = :TimeTableGraf_id
		";
		$queryParams = [
			"Person_id" => $data["Person_id"],
			"TimeTableGraf_id" => $data["TimeTableGraf_id"]
		];
		$resp = $callObject->queryResult($query, $queryParams);
		if (!empty($resp[0]) && !empty($resp[0]["EvnStatus_id"])) {
			$responseArray = ["EvnStatus_id" => $resp[0]["EvnStatus_id"]];
			if (!empty($data["extended"]) && !empty($resp[0]["MedStaffFact_id"])) {
				// вынес получение номера кабинета в отдельный метод, т.к. метод будем исползовать еще кое где
				$funcParams = [
					"MedStaffFact_id" => $resp[0]["MedStaffFact_id"],
					"datetime" => (!empty($resp[0]["TimeTableGraf_begTime"]) ? $resp[0]["TimeTableGraf_begTime"] : null)
				];
				$room = $callObject->getDoctorRoom($funcParams);
				$responseArray["Room"] = (!empty($room) ? $room : null);
			}
			return $responseArray;
		}
		return false;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return int
	 * @throws Exception
	 */
	public static function getFreeTimetable(TimetableGraf_model $callObject, $data)
	{
		if (in_array($data["object"], ["TimetableGraf", "TimetablePar"])) {
			$query = "
					select null
				";
		} elseif ($data["object"] == "TimetableStac") {
			// Экстренные бирки
			$query = "
				select TimetableStac_id as \"TimetableStac_id\"
				from v_TimetableStac_lite
				where TimetableStac_setDate = tzgetdate()
				  and TimetableType_id = 6
				  and Person_id is null
				  and LpuSection_id = :LpuSection_id
				limit 1
			";
		} else {
			throw new Exception("Указанный тип расписания не существует.");
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $data);
		if (!is_object($result)) {
			return 0;
		}
		$r = $result->result("array");
		if (count($r) == 0) {
			return 0;
		}
		return $r[0]["TimetableStac_id"];
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getTimetableGrafForEdit(TimetableGraf_model $callObject, $data)
	{
		$outdata = [];
		if (!isset($data["MedStaffFact_id"])) {
			throw new Exception("Не указан врач, для которого показывать расписание");
		}
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;

		$param["StartDay"] = TimeToDay($StartDay);
		$param["EndDay"] = TimeToDay(strtotime("+14 days", $StartDay));
		$param["MedStaffFact_id"] = $data["MedStaffFact_id"];
		$param["StartDate"] = date("Y-m-d", $StartDay);
		$param["EndDate"] = date("Y-m-d", strtotime("+14 days", $StartDay));

		if ($data["PanelID"] == "TTGRecordPanel" || $data["PanelID"] == "TTGDirectionPanel") {
			$msflpu = $callObject->getFirstRowFromQuery("select Lpu_id as \"Lpu_id\" from v_MedStaffFact where MedStaffFact_id = ?", [$data["MedStaffFact_id"]]);
			if (empty($_SESSION["setting"]) || empty($_SESSION["setting"]["server"])) {
				// Вынес отдельно, чтобы не повторять
				$maxDays = null;

			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "regpol" && $_SESSION["lpu_id"] == $msflpu["Lpu_id"]) {
				// Для регистратора запись в свою МО
				$callObject->load->model("LpuIndividualPeriod_model", "lipmodel");
				$individualPeriod = $callObject->lipmodel->getObjectIndividualPeriod(["Lpu_id" => $_SESSION["lpu_id"]], "MedStaffFact");
				if (!empty($data["MedStaffFact_id"]) && !empty($individualPeriod[$data["MedStaffFact_id"]])) {
					$maxDays = $individualPeriod[$data["MedStaffFact_id"]];
				} else {
					$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count"]) ? $_SESSION["setting"]["server"]["pol_record_day_count"] : null;
				}
			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "regpol") {
				// Для регистратора запись в чужую МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_reg"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_reg"] : null;
			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "callcenter") {
				// Для оператора call-центра
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_cc"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_cc"] : null;
			} elseif ($_SESSION["lpu_id"] == $msflpu["Lpu_id"]) {
				// Для остальных пользовалелей запись в свою МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_own"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_own"] : null;
			} else {
				// Для остальных пользовалелей запись в чужую МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_other"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_other"] : null;
			}
			if (date("H:i") >= getShowNewDayTime() && $maxDays) {
				$maxDays++;
			}
			$param["EndDate"] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param["EndDate"];
		}
		$param["nulltime"] = "00:00:00";
		$nTime = $StartDay;

		$outdata["header"] = [];
		$outdata["descr"] = [];
		$outdata["data"] = [];
		$outdata["occupied"] = [];
		for ($nCol = 0; $nCol < 14; $nCol++) {
			$nWeekDay = date("w", $nTime);
			$sClass = "work";
			if (($nWeekDay == 6) || ($nWeekDay == 0)) {
				$sClass = "relax";
			}
			$outdata["header"][TimeToDay($nTime)] = "<td class='$sClass'>" . "<b>" . $callObject->arShortWeekDayName[$nWeekDay] . "</b>" . date(" d", $nTime) . "</td>";
			$outdata["descr"][TimeToDay($nTime)] = [];
			$outdata["data"][TimeToDay($nTime)] = [];
			$outdata["occupied"][TimeToDay($nTime)] = false;

			$nTime = strtotime("+1 day", $nTime);
		}
		$param["StartDayA"] = TimeToDay(strtotime("-1 day", $StartDay));
		$param["EndDayA"] = TimeToDay(strtotime("+13 days", $StartDay));
		$param["Lpu_id"] = $data["Lpu_id"];
		$param["pmUser_id"] = $data["pmUser_id"];

		$sql = "
			select
				D.Day_id as \"Day_id\",
				rtrim(A.Annotation_Comment) as \"Annotation_Comment\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				to_char(A.Annotation_updDT, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') as \"Annotation_updDT\"
			from
				v_Day D
				left join v_Annotation A on
					A.Annotation_begDate <= D.day_date AND
					(A.Annotation_endDate >= D.day_date OR A.Annotation_endDate is null) AND
					(A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) AND
					(A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
				left join v_pmUser u on u.pmUser_id = A.pmUser_updID
				left join v_MedStaffFact msf on msf.MedStaffFact_id = A.MedStaffFact_id
			where A.MedStaffFact_id = :MedStaffFact_id
			  and D.Day_id >= :StartDayA
			  and D.Day_id < :EndDayA
			  and (A.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id)
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($sql, $param);
		$daydescrdata = $res->result("array");
		foreach ($daydescrdata as $day) {
			/**@var DateTime $Annotation_updDT */
			$Annotation_updDT = $day["Annotation_updDT"];
			$outdata["descr"][++$day["Day_id"]][] = [
				"Annotation_Comment" => $day["Annotation_Comment"],
				"pmUser_Name" => $day["pmUser_Name"],
				"Annotation_updDT" => isset($Annotation_updDT) ? DateTime::createFromFormat("d.m.Y H:i", $Annotation_updDT) : ""
			];
		}

		// Получаем примечания к биркам за период
		// @task https://redmine.swan.perm.ru/issues/128771
		$param["CurrentLpu_id"] = $data["session"]["lpu_id"];

		$query = "
			select
				to_char(A.Annotation_begDate, '{$callObject->dateForm120}') as \"Annotation_begDate\",
				to_char(A.Annotation_endDate, '{$callObject->dateForm120}') as \"Annotation_endDate\",
				to_char(A.Annotation_begTime, 'HH24:MI') as \"Annotation_begTime\",
				to_char(A.Annotation_endTime, 'HH24:MI') as \"Annotation_endTime\",
				rtrim(A.Annotation_Comment) as \"Annotation_Comment\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				A.Annotation_updDT as \"Annotation_updDT\"
			from v_Annotation A
				left join v_pmUser u on u.pmUser_id = A.pmUser_updID
				left join v_MedStaffFact msf on msf.MedStaffFact_id = A.MedStaffFact_id
			where A.MedStaffFact_id = :MedStaffFact_id
			  and (A.Annotation_begDate IS NULL or A.Annotation_begDate <= :EndDate)
			  and (A.Annotation_endDate IS NULL or :StartDate <= A.Annotation_endDate)
			  and (A.Annotation_begTime IS NOT NULL or A.Annotation_endTime IS NOT NULL)
			  and (A.AnnotationVison_id != 3 or msf.Lpu_id = :CurrentLpu_id)
		";
		$annotationdata = $callObject->queryResult($query, $param);
		if ($annotationdata === false) {
			$annotationdata = [];
		}
		$lpuFilter = getAccessRightsLpuFilter("laf.Lpu_id");
		$joinAccessFilter = (!empty($lpuFilter))
			? " left join v_Lpu laf on laf.Lpu_id = msf.Lpu_id and ($lpuFilter or t.pmUser_updID = :pmUser_id)"
			: " left join v_Lpu laf on laf.Lpu_id = msf.Lpu_id";
		$selectPersonData = "
			case when laf.Lpu_id is null then null else p.Person_BirthDay end as \"Person_BirthDay\",
			case when laf.Lpu_id is null then '' else p.Person_Phone end as \"Person_Phone\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Firname) end as \"Person_Firname\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Surname) end as \"Person_Surname\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Secname) end as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_BirthDay else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_Phone else null end as \"Person_Phone\",
				case when laf.Lpu_id is null then ''
					when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname)
					else rtrim(peh.PersonEncrypHIV_Encryp)
				end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
		}
		$filters = "";
		if (!havingGroup(["CallCenterAdmin", "OperatorCallCenter"])) {
			// если не оператор Call-центра, то фильтруем по МО.
			if (isset($data["filterByLpu"]) && $data["filterByLpu"] != "false") {
				$filters .= " and (coalesce(msf.MedStaffFact_IsDirRec, 2) = 2 or msf.Lpu_id = :Lpu_id)";
			}
		}
		$selectString1 = "
			t.pmUser_updID as \"pmUser_updID\",
			to_char(t.TimetableGraf_updDT, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') as \"TimetableGraf_updDT\",
			t.TimetableGraf_id as \"TimetableGraf_id\",
			t.Person_id as \"Person_id\",
			t.TimetableGraf_Day as \"TimetableGraf_Day\",
			to_char(t.TimetableGraf_begTime, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') as \"TimetableGraf_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableGraf_IsDop as \"TimetableGraf_IsDop\",
			t.TimeTableGraf_countRec as \"TimeTableGraf_countRec\",
			t.TimeTableGraf_PersRecLim as \"TimeTableGraf_PersRecLim\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
		";
		$selectString2 = "
			t.PMUser_UpdID as \"PMUser_UpdID\",
			case
				when t.pmUser_updid=999000 then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000 then 'Запись через интернет'
				else u.PMUser_Name
			end as \"PMUser_Name\",
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			d.EvnDirection_Num as \"Direction_Num\",
			coalesce(et.ElectronicTalon_Num::varchar, ED.EvnDirection_TalonCode) as \"Direction_TalonCode\",
			to_char(d.EvnDirection_setDT, '{$callObject->dateTimeForm104}') as \"Direction_Date\",
			d.EvnDirection_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			to_char(q.EvnQueue_insDT, 'dd.mm.yyyy HH24:MI:SS') as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			msf.MedStaffFact_id as \"MedStaffFact_id\",
			msf.MedPersonal_id as \"MedPersonal_id\",
			msf.LpuUnit_id as \"LpuUnit_id\"
		";
		$fromString = "
			v_TimetableGraf_lite t
			left outer join v_MedStaffFact_ER msf on msf.MedStaffFact_id = t.MedStaffFact_id
			left join lateral (
				select *
				from v_Person_ER2 p
				where t.Person_id = p.Person_id
				limit 1
			) p on true
			left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			left join v_EvnDirection d on t.EvnDirection_id = d.EvnDirection_id and d.DirFailType_id is null
			left join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
			left join v_EvnQueue q on t.TimetableGraf_id = q.TimetableGraf_id and t.Person_id = q.Person_id
			left join v_pmUser qp on q.pmUser_updId = qp.pmUser_id
			left join Diag dg on dg.Diag_id = d.Diag_id
			left join v_EvnDirection_all ed on ed.EvnDirection_id = t.EvnDirection_id
			left join v_ElectronicTalon et on et.EvnDirection_id = t.EvnDirection_id
		";
		$whereString = implode(" and ", [
			"t.TimetableGraf_Day >= :StartDay",
			"t.TimetableGraf_Day < :EndDay",
			"t.MedStaffFact_Id = :MedStaffFact_id",
			"cast(TimetableGraf_begTime as date) between :StartDate and :EndDate",
		]);
		$orderByString = "t.TimetableGraf_begTime";
		$sql = "
			select
				{$selectString1}
				{$selectPersonData}
				{$selectString2}
			from
				{$fromString}
				{$joinPersonEncrypHIV}
				{$joinAccessFilter}
			where
				{$whereString}
				{$filters}
			order by {$orderByString}
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($sql, $param);
		$ttgdata = $res->result("array");
		foreach ($ttgdata as $ttg) {
			$ttgannotation = [];
			foreach ($annotationdata as $annotation) {
				/**@var DateTime $TimetableGraf_begTime */
				$TimetableGraf_begTime = new DateTime($ttg["TimetableGraf_begTime"]);
				if (
					(empty($annotation["Annotation_begDate"]) || $annotation["Annotation_begDate"] <= $TimetableGraf_begTime->format("Y-m-d")) &&
					(empty($annotation["Annotation_endDate"]) || $annotation["Annotation_endDate"] >= $TimetableGraf_begTime->format("Y-m-d")) &&
					(empty($annotation["Annotation_begTime"]) || $annotation["Annotation_begTime"] <= $TimetableGraf_begTime->format("H:i")) &&
					(empty($annotation["Annotation_endTime"]) || $annotation["Annotation_endTime"] >= $TimetableGraf_begTime->format("H:i"))
				) {
					$ttgannotation[] = $annotation;
				}
			}
			$ferAnnotation = $callObject->_getTimetableGrafFERAnnotation($ttg);
			if (is_array($ferAnnotation)) {
				$ttgannotation = array_merge($ttgannotation, $ferAnnotation);
			}
			$ttg["annotation"] = $ttgannotation;
			$outdata["data"][$ttg["TimetableGraf_Day"]][] = $ttg;
			if (isset($ttg["Person_id"])) {
				$outdata["occupied"][$ttg["TimetableGraf_Day"]] = true;
			}
			if (!empty($data["timetable_blocked"]) && !isset($ttg["Person_id"]) && in_array($ttg["TimetableType_id"], [1, 11])) {
				$outdata["occupied"][$ttg["TimetableGraf_Day"]] = true;
			}
		}
		$sql = "
			select TimetableGraf_id as \"TimetableGraf_id\"
			from TimetableLock
			where TimetableGraf_id is not null
		";
		$res = $callObject->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $res->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableGraf_id"];
		}
		return $outdata;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getListTimetableLpu(TimetableGraf_model $callObject, $data)
	{
		$params = [];
		$join = "";
		$filter = "(1 = 1)";
		$filter_in = "(1 = 1)";
		if (!empty($data["LpuSectionProfile_id"])) {
			$filter_in .= " and lss.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
		}
		if (!empty($data["MedPersonal_id"])) {
			$filter_in .= " and msf.MedPersonal_id = :MedPersonal_id";
			$params["MedPersonal_id"] = $data["MedPersonal_id"];
		}
		if ((!empty($data["LpuSectionProfile_id"])) || (!empty($data["MedPersonal_id"]))) {
			$filter .= " and ms.LpuUnit_id = lu.LpuUnit_id";
			$join = "
				left join lateral (
					select lss.LpuUnit_id
					from v_MedStaffFact_ER msf
					     left join LpuSection lss on lss.LpuSection_id = msf.LpuSection_id
					     and {$filter_in}
					) ms on true
			";
		}
		if (!empty($data["LpuUnitType_id"])) {
			$filter .= " and lu.LpuUnitType_id = :LpuUnitType_id";
			$params["LpuUnitType_id"] = $data["LpuUnitType_id"];
		}
		if (!empty($data["Lpu_id"])) {
			$filter .= " and lu.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		$query = "
			select
			    -- select
				lu.Lpu_id as \"Lpu_id\",
			    Lpu.Lpu_Nick as \"Lpu_Nick\",
			    lu.LpuUnit_id as \"LpuUnit_id\",
			    lu.LpuUnit_Name as \"LpuUnit_Name\",
			    lu.LpuUnit_Descr as \"LpuUnit_Descr\",
			    rtrim(luas.KLStreet_Name)||' '||lua.Address_House as \"LpuUnit_Address\",
			    lu.LpuUnit_Phone as \"LpuUnit_Phone\",
			    lu.LpuUnit_Enabled as \"LpuUnit_Enabled\",
			    lu.LpuUnit_ExtMedCnt as \"ExtMed\",
			    lu.LpuUnitType_id as \"LpuUnitType_id\",
			    LpuUnit_updDT as \"LpuUnit_updDT\",
			    null as \"FreeTime\"
		        -- end select
			from 
			    -- from
			    v_LpuUnit_ER lu
				 left outer join Address lua on lu.Address_id = lua.Address_id
			     left outer join KLStreet luas on lua.KLStreet_id = luas.KLStreet_id
			     left join v_Lpu Lpu on Lpu.Lpu_id = lu.Lpu_id
			     left join v_pmUser pu on lu.pmUser_updId = pu.pmUser_id
			     {$join}
			    -- end from
			where
			    -- where 
			    lu.LpuUnitType_id !=5
			  and (Lpu.Lpu_endDate is null or Lpu.Lpu_endDate > tzgetdate())
			  and {$filter}
			    -- end where
			order by
			    -- order by
				lu.LpuUnit_Enabled desc,
			    Lpu.Lpu_Nick,
			    lu.LpuUnit_Name
			    -- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
		$result_count = $callObject->db->query(getCountSQLPH($query), $params);
		if (!is_object($result)) {
			return false;
		}
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getListTimetableLpuUnit(TimetableGraf_model $callObject, $data)
	{
		$params = [];
		$join = "";
		$filter = "(1 = 1)";
		$filter_in = "(1 = 1)";
		$filter_tt = "";
		$filter_ttp = "";
		if (!empty($data["LpuSectionProfile_id"])) {
			$filter_in .= " and lss.LpuSectionProfile_id = :LpuSectionProfile_id";
			$filter_tt .= " AND msf.LpuSectionProfile_id = :LpuSectionProfile_id";
			$filter_ttp = " AND ls.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
		}
		if (!empty($data["MedPersonal_id"])) {
			$filter_in .= " and msf.MedPersonal_id = :MedPersonal_id";
			$filter_tt .= " AND msf.MedPersonal_id = :MedPersonal_id";
			$params["MedPersonal_id"] = $data["MedPersonal_id"];
		}
		if ((!empty($data["LpuSectionProfile_id"])) || (!empty($data["MedPersonal_id"]))) {
			$filter .= " and ms.LpuUnit_id = lu.LpuUnit_id";
			$join = "
				left join lateral (
					select lss.LpuUnit_id
					from v_MedStaffFact_ER msf
					     left join LpuSection lss on lss.LpuSection_id = msf.LpuSection_id
					 and {$filter_in}
				) ms on true
			";
		}
		if (!empty($data["LpuUnitType_id"])) {
			$filter .= " and lu.LpuUnitType_id = :LpuUnitType_id";
			$params["LpuUnitType_id"] = $data["LpuUnitType_id"];
		}
		if (empty($data["Lpu_id"])) {
			return false;
		}
		$filter .= " and lu.Lpu_id = :Lpu_id";
		$params["Lpu_id"] = $data["Lpu_id"];

		$query = "
			select
				lu.Lpu_id as \"Lpu_id\",
			    Lpu.Lpu_Nick as \"Lpu_Nick\",
			    lu.LpuUnit_id as \"LpuUnit_id\",
			    lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
			    lu.LpuUnit_Name as \"LpuUnit_Name\",
			    lu.LpuUnit_Descr as \"LpuUnit_Descr\",
			    rtrim(luas.KLStreet_Name)||' '||lua.Address_House as \"LpuUnit_Address\",
			    lu.LpuUnit_Phone as \"LpuUnit_Phone\",
			    lu.LpuUnit_Enabled as \"LpuUnit_Enabled\",
			    lu.LpuUnit_ExtMedCnt as \"ExtMed\",
			    lu.LpuUnitType_id as \"LpuUnitType_id\",
			    LpuUnit_updDT as \"LpuUnit_updDT\",
			    coalesce(to_char(coalesce(TT.min_time,TTP.min_time), '{$callObject->dateTimeForm4}'), 'нет')||' '||coalesce(to_char(coalesce(TT.min_time,TTP.min_time), '{$callObject->dateTimeForm108}'), '') as \"FreeTime\"
			from v_LpuUnit_ER lu
			     left outer join Address lua on lu.Address_id = lua.Address_id
			     left outer join KLStreet luas on lua.KLStreet_id = luas.KLStreet_id
			     left join v_Lpu Lpu on Lpu.Lpu_id = lu.Lpu_id
			     left join v_pmUser pu on lu.pmUser_updId = pu.pmUser_id
			     left join LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
			     left join lateral (
			         select min(ttg.TimetableGraf_begTime) as min_time
			         from v_TimetableGraf_lite ttg
			         where ttg.MedStaffFact_id in (select msf.MedStaffFact_id from v_MedStaffFact_ER msf where msf.LpuUnit_id = lu.LpuUnit_id {$filter_tt})
			           and ttg.TimetableType_id not in (2,3,4)
			           and ttg.Person_id is null
			           and ttg.TimetableGraf_begTime >= tzgetdate()
			     ) as TT on true
			     left join lateral (
			         select ttg.TimetablePar_begTime as min_time
			         from v_TimetablePar ttg
			         where ttg.LpuSection_id in (select LpuSection_id from v_LpuSection_ER ls where ls.Lpu_id = lu.Lpu_id and ls.LpuUnit_id = lu.LpuUnit_id {$filter_ttp})
			           and ttg.TimetablePar_IsReserv is null
			           and ttg.Person_id is null
			           and ttg.TimetablePar_isPay is null
			           and ttg.TimetablePar_IsDop is null
			           and ttg.TimetablePar_begTime >= tzgetdate()
			         order by ttg.TimetablePar_begTime
			         limit 1
			     ) as TTP on true
			     {$join}
			where lu.LpuUnitType_id !=5
			  and (Lpu.Lpu_endDate is null or Lpu.Lpu_endDate > dbo.tzGetDate())
			  and {$filter}
			order by
				lu.LpuUnit_Enabled DESC,
			    Lpu.Lpu_Nick,
			    lu.LpuUnit_Name
		";
		/**
		 * @var CI_DB_result $result
		 */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getListTimetableMedPersonal(TimetableGraf_model $callObject, $data)
	{
		$params = [];
		$filter = "(1 = 1)";
		if (!empty($data["LpuSectionProfile_id"])) {
			$filter .= " and msf.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
		}
		if (!empty($data["MedPersonal_id"])) {
			$filter .= " and msf.MedPersonal_id = :MedPersonal_id";
			$params["MedPersonal_id"] = $data["MedPersonal_id"];
		}
		if (!empty($data["LpuUnit_id"])) {
			$filter .= " and msf.LpuUnit_id = :LpuUnit_id";
			$params["LpuUnit_id"] = $data["LpuUnit_id"];
		}
		if (!empty($data["Lpu_id"])) {
			$filter .= " and msf.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		$query = "
			select
				MedPersonal_FIO as \"MedPersonal_FIO\",
			    msf.MedStaffFact_id as \"MedStaffFact_id\",
			    msf.MedPersonal_id as \"MedPersonal_id\",
			    msf.LpuSection_id as \"LpuSection_id\",
			    msf.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			    msf.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
			    null as \"MedStaffFact_Descr\",
			    u.pmuser_name as \"pmuser_name\",
			    msf.Lpu_id as \"Lpu_id\",
			    msf.LpuUnit_id as \"LpuUnit_id\",
			    msf.MedStaffFact_updDT as \"MedStaffFact_updDT\",
			    msf.RecType_id as \"RecType_id\",
			    msf.MedStatus_id as \"MedStatus_id\",
			    sign(LR.LpuRegion_Count) as \"isRegion\",
			    rtrim(LpuSection_Name) as \"LpuSection_Name\",
			    rtrim(LR.LpuRegion_Name) as \"LpuRegion_Names\",
			    coalesce(to_char(TT.min_time, '{$callObject->dateTimeForm4}'), 'нет')||' '||coalesce(to_char(TT.min_time, '{$callObject->dateTimeForm108}'), '') as \"FreeTime\",
			    (
			    	select count(*)
			        from v_EvnQueue
			        where LpuSectionProfile_did = msf.LpuSectionProfile_id
			          and Lpu_id = msf.Lpu_id
			          and EvnDirection_id is null
			          and EvnQueue_recDT is null
			          and pmUser_recID is null
			          and TimetableGraf_id is null
			          and TimetableStac_id is null
			          and TimetablePar_id is null
				) as \"EvnQueue_Names\"
			from v_MedStaffFact_ER msf
			     left join v_pmUser u on u.pmUser_id=msf.pmUser_updId
			     left join lateral (
			         select MIN(ttg.TimetableGraf_begTime) as min_time
			         from v_TimetableGraf_lite ttg
			         where msf.MedStaffFact_id=ttg.MedStaffFact_id
			           and ttg.TimetableType_id not in (2,3,4)
			           and ttg.Person_id is null
			           and ttg.TimetableGraf_begTime >= tzgetdate()
			     ) as TT on true
			     left join lateral (
			         select count(*) as LpuRegion_Count
			               ,lr.LpuRegion_Name
			         from v_MedstaffRegion msr
			              left join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
			         where msf.MedPersonal_id = msr.MedPersonal_id
			           and msr.Lpu_id = msf.Lpu_id
			         group by lr.LpuRegion_Name
			     ) as LR on true
			where {$filter}
			  and (msf.Medstafffact_disDate is null or msf.Medstafffact_disDate > tzgetdate())
			  and (msf.RecType_id != 6 or msf.RecType_id is null)
			order by
				sign(LR.LpuRegion_Count) DESC,
				MedPersonal_FIO
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
		$result_count = $callObject->db->query(getCountSQLPH($query), $params);
		if (!is_object($result)) {
			return false;
		}
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getListTimetableMedService(TimetableGraf_model $callObject, $data)
	{
		$params = [];
		$filter = "(1 = 1)";
		$uc_filter = "";
		if (!empty($data["Lpu_id"])) {
			$filter .= " and ms.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		if (!empty($data["LpuUnitType_id"])) {
			$filter .= " and ms.LpuUnitType_id = :LpuUnitType_id";
			$params["LpuUnitType_id"] = $data["LpuUnitType_id"];
		}
		if (!empty($data["LpuUnit_id"])) {
			$filter .= " and ms.LpuUnit_id = :LpuUnit_id";
			$params["LpuUnit_id"] = $data["LpuUnit_id"];
		} else {
			$filter .= " and ms.LpuUnit_id is null and ms.LpuSection_id is null";
		}
		if (!empty($data["MedService_id"])) {
			$filter = " ms.MedService_id = :MedService_id";
			$params["MedService_id"] = $data["MedService_id"];
		}
		if (!empty($data["UslugaComplex_id"])) {
			$uc_filter = " and UCMS.UslugaComplex_id = :UslugaComplex_id";
			$params["UslugaComplex_id"] = $data["UslugaComplex_id"];
		}
		if (!empty($data["uslugaList"])) {
			$uslugaList = explode(",", $data["uslugaList"]);
			foreach ($uslugaList as &$UslugaComplex_id) {
				$UslugaComplex_id = trim($UslugaComplex_id);
				if (!(is_numeric($UslugaComplex_id) && $UslugaComplex_id > 0)) {
					return false;
				}
			}
			$uslugaList = implode(",", $uslugaList);
			$filter = "(1 = 1)";
			$uc_filter = " and UCMS.UslugaComplex_id in({$uslugaList})";
		}
		$query = "
			select
			    -- select
				case when ms.Lpu_id = :Lpu_id then 1 else 0 end as \"isUserLpu\",
				ms.MedService_Nick as \"MedService_Nick\",
				ms.MedService_id as \"MedService_id\",
				ms.Lpu_id as \"Lpu_id\",
				ms.LpuBuilding_id as \"LpuBuilding_id\",
				ms.LpuUnitType_id as \"LpuUnitType_id\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				ms.LpuUnit_id as \"LpuUnit_id\",
				ms.LpuSection_id as \"LpuSection_id\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MT.MedServiceType_id as \"MedServiceType_id\",
				MT.MedServiceType_SysNick as \"MedServiceType_SysNick\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				UCMS.UslugaComplex_id as \"UslugaComplex_id\",
				'нет' as \"FreeTime\",
				0 as \"EvnQueue_Names\"
				-- end select
			from
			    -- from
				v_MedService ms
				inner join v_UslugaComplexMedService UCMS on ms.MedService_id = UCMS.MedService_id {$uc_filter}
				inner join v_UslugaComplex UC on UCMS.UslugaComplex_id = UC.UslugaComplex_id
				left join v_MedServiceType MT on ms.MedServiceType_id = MT.MedServiceType_id
				left join v_LpuUnitType LUT on ms.LpuUnitType_id = LUT.LpuUnitType_id
				left join v_LpuSection LS on ms.LpuSection_id = LS.LpuSection_id
				-- end from
			where
			    -- where 
			    {$filter}
			  AND tzgetdate() between UCMS.UslugaComplexMedService_begDT AND coalesce(UCMS.UslugaComplexMedService_endDT, tzgetdate())
			    -- end where
			order by
			    -- order by
				\"isUserLpu\" desc,
			    UC.UslugaComplex_Name
			    -- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
		$result_count = $callObject->db->query(getCountSQLPH($query), $params);
		if (!is_object($result)) {
			return false;
		}
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getListTimetableLpuSection(TimetableGraf_model $callObject, $data)
	{
		$params = [];
		$filter = "(1 = 1)";
		$filter_in = "(1 = 1)";
		if (!empty($data["LpuSectionProfile_id"])) {
			$filter .= " and ls.LpuSectionProfile_id = :LpuSectionProfile_id";
			$params["LpuSectionProfile_id"] = $data["LpuSectionProfile_id"];
		}
		if (!empty($data["LpuUnit_id"])) {
			$filter .= " and ls.LpuUnit_id = :LpuUnit_id";
			$filter_in .= " and LSWC.LpuUnit_id = :LpuUnit_id";
			$params["LpuUnit_id"] = $data["LpuUnit_id"];
		}
		if (!empty($data["Lpu_id"])) {
			$filter .= " and lu.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		$for_free_time = "
			left join lateral (
					select null as min_time
				) as TT on true
		";
		if ($data["LpuUnitType_SysNick"] == "parka") {
			$usluga = "
				join v_UslugaComplex UC on UC.LpuSection_id = ls.LpuSection_id
			";
			$usluga_select = "
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
			";
			$for_free_time = "
				left join lateral (
					select ttg.TimetablePar_begTime as min_time
					from v_TimetablePar ttg
					where
						ttg.LpuSection_id = ls.LpuSection_id
						and ttg.TimetablePar_IsReserv is null
						and ttg.Person_id is null
						and ttg.TimetablePar_isPay is null
						and ttg.TimetablePar_IsDop is null
						and ttg.TimetablePar_begTime >= tzgetdate()
					order by ttg.TimetablePar_begTime
					limit 1
				) TT on true
			";
		} else {
			$usluga = "";
			$usluga_select = "";
		}
		if (in_array($data["LpuUnitType_SysNick"], ["stac", "dstac", "hstac", "pstac"])) {
			// не показываем подотделения отделений стационарного типа
			$filter .= " and ls.LpuSection_pid is null";
		} else {
			//непонятно зачем было это условие, но для стаца оно точно не нужно, т.к. отсеивает отделения, имеющие подотделеления
			$filter .= " and ls.LpuSection_id not in (select LpuSection_pid from v_LpuSectionWithCabs LSWC where LpuSection_pid is not null and {$filter_in})";
		}
		$selectString1 = "
			LpuUnitType_id as \"LpuUnitType_id\",
			ls.LpuSection_id as \"LpuSection_id\",
			ls.LpuSection_Name as \"LpuSection_Name\",
			ls.LpuSection_Descr as \"LpuSection_Descr\",
			ls.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
			rtrim(pmUser_Name) as \"pmuser_name\",
			ls.LpuSection_updDT as \"LpuSection_updDT\",
			ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
			lu.LpuUnit_id as \"LpuUnit_id\",
		";
		$selectString2 = "
			coalesce(to_char(TT.min_time::date, '{$callObject->dateTimeForm4}'), 'нет')||' '||coalesce(to_char(TT.min_time::date, '{$callObject->dateTimeForm108}'), '') as \"FreeTime\",
			(
				select count(*)
				from v_EvnQueue
				where LpuSectionProfile_did = ls.LpuSectionProfile_id
				  and Lpu_id = lu.Lpu_id
				  and EvnDirection_id is null
				  and EvnQueue_recDT is null
				  and pmUser_recID is null
				  and TimetableGraf_id is null
				  and TimetableStac_id is null
				  and TimetablePar_id is null
			) as \"EvnQueue_Names\"
		";
		$fromString = "
			v_LpuSection_ER ls
			left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_pmUser pu on ls.pmUser_updId = pu.pmUser_id
		";
		$whereString = " and coalesce(LpuSectionHospType_id, 1) != 5";
		$orderByString = "\"LpuSection_Name\"";
		$query = "
			select
			    -- select
				{$selectString1}
			    {$usluga_select}
			    {$selectString2}
			    -- end select
			from
			    -- from
				{$fromString}
			    {$usluga}
			    {$for_free_time}
			    -- end from
			where
			    -- where 
			    {$filter}
			    {$whereString}
			    -- end where
			order by
			    -- order by 
			    {$orderByString}
			    -- end order by
		";
		/**
		 * @var CI_DB_result $result
		 * @var CI_DB_result $result_count
		 */
		$result = $callObject->db->query(getLimitSQLPH($query, $data["start"], $data["limit"]), $params);
		$result_count = $callObject->db->query(getCountSQLPH($query), $params);
		if (!is_object($result)) {
			return false;
		}
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result("array");
			$count = $cnt_arr[0]["cnt"];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		$response = [];
		$response["data"] = $result->result("array");
		$response["totalCount"] = $count;
		return $response;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getTimeTableGrafById(TimetableGraf_model $callObject, $data)
	{
		$sql = "
			select
				ttg.MedStaffFact_id as \"MedStaffFact_id\",
				to_char(ttg.TimetableGraf_begTime, '{$callObject->dateTimeForm120}') as \"TimeTableGraf_begTime\",
				ttg.TimetableGraf_Time as \"TimetableGraf_Time\",
				ttg.TimeTableType_id as \"TimeTableType_id\",
				yn.YesNo_Code as \"TimeTableGraf_IsDop\"
			from
				v_TimeTableGraf_lite ttg
				left join YesNo yn on yn.YesNo_id = coalesce(ttg.TimeTableGraf_IsDop, 1)
			where ttg.TimeTableGraf_id = :TimeTableGraf_id
			limit 1
		";
		$sqlParams = ["TimeTableGraf_id" => $data["TimeTableGraf_id"]];
		$resp = $callObject->queryResult($sql, $sqlParams);
		return $resp;
	}

    /**
     * @param TimetableGraf_model $callObject
     * @param $data
     * @return array|false
     */
    public static function getRecord(TimetableGraf_model $callObject, $data)
    {
        $sql = "
			select
				TimetableGraf_id as \"TimetableGraf_id\",
				rtrim(p.Person_Surname) || ' ' || rtrim(p.Person_Firname) || coalesce(' ' || rtrim(p.Person_Secname), '' ) as \"Person_FIO\",
				rtrim(Person_FIO) as \"MedPersonal_FIO\",
				rtrim(lsp.ProfileSpec_Name) as \"ProfileSpec_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				rtrim(KLStreet_Name)||' '||rtrim(Address_House) as \"LpuUnit_Address\",
				ttg.TimetableGraf_begTime as \"TimetableGraf_begTime\",
				mpd.MedPersonalDay_Descr as \"MedPersonalDay_Descr\",
				p.Person_id as \"Person_id\"
			from v_TimetableGraf_lite ttg
				left join v_Person_ER p on ttg.Person_id = p.Person_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_LpuSection ls on msf.LpuSection_id = ls.LpuSection_id
				left join v_LpuSectionProfile lsp  on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_Lpu l on l.Lpu_id = lu.Lpu_id
				left join Address  on Address.Address_id = lu.Address_id
				left join KLStreet  on KLStreet.KLStreet_id = Address.KLStreet_id
				left join MedpersonalDay mpd  on
					mpd.MedStaffFact_id = ttg.MedStaffFact_id
					and mpd.Day_id = ttg.TimetableGraf_Day
			where TimeTableGraf_id = :TimeTableGraf_id
                and coalesce(l.Lpu_IsTest, 1) = 1
			union
			select 
				ttg.TimetableGraf_id as \"TimetableGraf_id\",
				rtrim(p.Person_Surname) || ' ' || rtrim(p.Person_Firname) || coalesce(' ' || rtrim(p.Person_Secname), '' ) as \"Person_FIO\",
			    rtrim(Person_FIO) as \"MedPersonal_FIO\",
				rtrim(lsp.ProfileSpec_Name) as \"ProfileSpec_Name\",
				l.Lpu_Nick as \"Lpu_Nick\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				rtrim(KLStreet_Name)||' '||rtrim(Address_House) as \"LpuUnit_Address\",
				ttg.TimetableGraf_begTime as \"TimetableGraf_begTime\",
				mpd.MedPersonalDay_Descr as \"MedPersonalDay_Descr\",
				p.Person_id as \"Person_id\"
			from v_TimetableGraf_lite ttg
				left join v_Person_ER p on ttg.Person_id = p.Person_id
				left join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_LpuSection ls on msf.LpuSection_id = ls.LpuSection_id
				left join v_LpuSectionProfile lsp  on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
				left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_Lpu l on l.Lpu_id = lu.Lpu_id
				left join Address  on Address.Address_id = lu.Address_id
				left join KLStreet  on KLStreet.KLStreet_id = Address.KLStreet_id
				left join v_EvnQueue evn on evn.TimeTableGraf_id = ttg.TimeTableGraf_id
				left join MedpersonalDay mpd  on
					mpd.MedStaffFact_id = ttg.MedStaffFact_id
					and mpd.Day_id = ttg.TimetableGraf_Day
				where ttg.TimeTableGraf_id = :TimeTableGraf_id
                limit 1
		";
        $sqlParams = [
            "TimeTableGraf_id" => $data["TimeTableGraf_id"]
        ];
        $resp = $callObject->getFirstRowFromQuery($sql, $sqlParams);
        return $resp;
    }

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getTimeTableGrafbyMO(TimetableGraf_model $callObject, $data)
	{
		$sql = "
			select
				ttg.TimetableGraf_id as \"TimeTableGraf_id\",
				ttg.Person_id as \"Person_id\"
			from
				v_TimeTableGraf_lite ttg
				inner join v_MedStaffFact msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
			where msf.Lpu_id = :Lpu_id
			  and ttg.TimetableGraf_begTime >= :TimeTableGraf_beg
			  and ttg.TimetableGraf_begTime <= :TimeTableGraf_end
			  and ttg.Person_id is not null
		";
		$sqlParams = [
			"Lpu_id" => $data["Lpu_id"],
			"TimeTableGraf_beg" => $data["TimeTableGraf_beg"],
			"TimeTableGraf_end" => $data["TimeTableGraf_end"]
		];
		$resp = $callObject->queryResult($sql, $sqlParams);
		return $resp;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getTimeTableGrafByUpdPeriod(TimetableGraf_model $callObject, $data)
	{
		$params = [
			"TimeTableGraf_updbeg" => $data["TimeTableGraf_updbeg"],
			"TimeTableGraf_updend" => $data["TimeTableGraf_updend"]
		];
		$filters = ["TTGHM.TimeTableGraf_updDT between :TimeTableGraf_updbeg and :TimeTableGraf_updend"];
		if (!empty($data["Lpu_id"])) {
			$filters[] = "TTGHM.Lpu_id = :Lpu_id";
			$params["Lpu_id"] = $data["Lpu_id"];
		}
		$filtersString = implode(" and ", $filters);
		$query = "
			select
				TTGHM.Lpu_id as \"Lpu_id\",
				TTGHM.MedStaffFact_id as \"MedStaffFact_id\",
				TTGHM.TimeTableGraf_id as \"TimeTableGraf_id\",
				to_char(TTGHM.TimeTableGraf_begTime, '{$callObject->dateTimeForm120}') as \"TimeTableGraf_begTime\",
				TTGHM.TimeTableType_id as \"TimeTableType_id\",
				TTGHM.Person_id as \"Person_id\",
				TTGHM.TimeTableGrafAction_id as \"TimeTableGrafAction_id\",
				to_char(TTGHM.TimeTableGraf_insDT, '{$callObject->dateTimeForm120}') as \"TimeTableGraf_insDT\",
				to_char(TTGHM.TimeTableGraf_updDT, '{$callObject->dateTimeForm120}') as \"TimeTableGraf_updDT\"
			from TimeTableGrafHistMIS TTGHM
			where {$filtersString}
		";
		return $callObject->queryResult($query, $params);
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getTimeTableGrafFreeDate(TimetableGraf_model $callObject, $data)
	{
		$filter = "";
		if (!empty($data["freeforinternetrecord"])) {
			$filter = "
				and TimeTableType_id in (1, 9, 11)
				and TimeTableGraf_begTime >= tzgetdate()
			";
		}
		$sql = "
			select distinct
				to_char(TimetableGraf_begTime, '{$callObject->dateForm120}') as \"TimeTableGraf_begTime\"
			from v_TimeTableGraf_lite
			where MedStaffFact_id = :MedStaffFact_id
			  and TimetableGraf_begTime::date >= :TimeTableGraf_beg
			  and TimetableGraf_begTime::date <= :TimeTableGraf_end
			  and Person_id is null
			  {$filter}
		";
		$sqlParams = [
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"TimeTableGraf_beg" => $data["TimeTableGraf_beg"],
			"TimeTableGraf_end" => $data["TimeTableGraf_end"]
		];
		$resp = $callObject->queryResult($sql, $sqlParams);
		return $resp;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|false
	 */
	public static function getTimeTableGrafFreeTime(TimetableGraf_model $callObject, $data)
	{
		$sql = "
			select
				TimeTableGraf_id as \"TimeTableGraf_id\",
				to_char(TimetableGraf_begTime, '{$callObject->dateTimeForm120}') as \"TimeTableGraf_begTime\",
				TimeTableGraf_Time as \"TimeTableGraf_Time\",
				TimeTableType_id as \"TimeTableType_id\"
			from v_TimeTableGraf_lite
			where MedStaffFact_id = :MedStaffFact_id
			  and TimetableGraf_begTime = :TimeTableGraf_begTime
			  and Person_id is null
			order by \"TimetableGraf_begTime\"
		";
		$sqlParams = [
			"MedStaffFact_id" => $data["MedStaffFact_id"],
			"TimeTableGraf_begTime" => $data["TimeTableGraf_begTime"]
		];
		$resp = $callObject->queryResult($sql, $sqlParams);
		return $resp;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getTimetableGrafGroup(TimetableGraf_model $callObject, $data)
	{
		$outdata = [];
		if (!isset($data['MedStaffFact_id'])) {
			throw new Exception("Не указан врач, для которого показывать расписание");
		}
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;

		$param["pmUser_id"] = $data["pmUser_id"];
		$param["StartDay"] = TimeToDay($StartDay);
		$param["StartDayA"] = $param["StartDay"] - 1;
		$param["MedStaffFact_id"] = $data["MedStaffFact_id"];
		$param["Lpu_id"] = $data["Lpu_id"];
		$param["nulltime"] = "00:00:00";
		$param["TimeTableGraf_id"] = $data["TimeTableGraf_id"];


		$param["EndDate"] = date("Y-m-d", $StartDay);
		if ($data["PanelID"] == "TTGRecordInGroupPanel") {
			$msflpu = $callObject->getFirstRowFromQuery("select Lpu_id  as \"Lpu_id\" from v_MedStaffFact where MedStaffFact_id = ?", array($data["MedStaffFact_id"]));
			if (empty($_SESSION["setting"]) || empty($_SESSION["setting"]["server"])) {
				// Вынес отдельно, чтобы не повторять
				$maxDays = null;
			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "regpol" && $_SESSION["lpu_id"] == $msflpu["Lpu_id"]) {
				// Для регистратора запись в свою МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count"]) ? $_SESSION["setting"]["server"]["pol_record_day_count"] : null;
			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "regpol") {
				// Для регистратора запись в чужую МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_reg"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_reg"] : null;
			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "callcenter") {
				// Для оператора call-центра
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_cc"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_cc"] : null;
			} elseif ($_SESSION["lpu_id"] == $msflpu["Lpu_id"]) {
				// Для остальных пользовалелей запись в свою МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_own"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_own"] : null;
			} else {
				// Для остальных пользовалелей запись в чужую МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_other"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_other"] : null;
			}

			if ($maxDays) $maxDays--; // лишний день
			if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
			$param["EndDate"] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param["EndDate"];
		}
		$ttGrafQuery = "
			select 
				ttg.TimeTableGraf_id as \"TimeTableGraf_id\",
				ttg.TimeTableGraf_IsMultiRec as \"TimeTableGraf_IsMultiRec\",
				ttg.TimeTableGraf_PersRecLim as \"TimeTableGraf_PersRecLim\",
				ttg.TimeTableType_id as \"TimeTableType_id\"
			from v_TimeTableGraf ttg
			where ttg.TimeTableGraf_id = :TimeTableGraf_id
		";
		$ttGrafQueryParams = [
			"TimeTableGraf_id" => $data["TimeTableGraf_id"]
		];
		$TimeTableGraf = $callObject->getFirstRowFromQuery($ttGrafQuery, $ttGrafQueryParams);
		$outdata["day_comment"] = null;
		$outdata["data"] = [];

		$sql = "
			select
				D.Day_id as \"Day_id\",
				rtrim(A.Annotation_Comment) as \"Annotation_Comment\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				A.Annotation_updDT as \"Annotation_updDT\"
			from v_Day D
				 left join v_Annotation A on
					(A.Annotation_begDate <= D.day_date) AND
					((A.Annotation_endDate >= D.day_date) OR A.Annotation_endDate is null) AND
					(A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) AND
					(A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
				 left join v_pmUser u on u.pmUser_id = A.pmUser_updID
				 left join v_MedStaffFact msf on msf.MedStaffFact_id = A.MedStaffFact_id
			where A.MedStaffFact_id = :MedStaffFact_id
			  and D.Day_id = :StartDayA
			  and (A.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id)
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($sql, $param);
		$daydescrdata = $res->result("array");
		foreach ($daydescrdata as $day) {
			/**@var DateTime $Annotation_updDT */
			$Annotation_updDT = $day["Annotation_updDT"];
			$outdata["day_comment"][] = [
				"Annotation_Comment" => $day["Annotation_Comment"],
				"pmUser_Name" => $day["pmUser_Name"],
				"Annotation_updDT" => isset($day["Annotation_updDT"]) ? DateTime::createFromFormat("d.m.Y H:i", $Annotation_updDT): ""
			];
		}
		$lpuFilter = getAccessRightsLpuFilter("laf.Lpu_id");
		$joinAccessFilter = (!empty($lpuFilter))
			? " left join v_Lpu laf on laf.Lpu_id = msf.Lpu_id and ($lpuFilter or t.pmUser_updID = :pmUser_id)"
			: " left join v_Lpu laf on laf.Lpu_id = msf.Lpu_id";
		$selectPersonData = "
			case when laf.Lpu_id is null then null else p.Person_BirthDay end as \"Person_BirthDay\",
			case when laf.Lpu_id is null then '' else p.Person_Phone end as \"Person_Phone\",
			pcs.PersonCardState_Code as \"PersonCard_Code\",
			pcs.PersonCard_id as \"PersonCard_id\",
			case when laf.Lpu_id is null then null else p.PersonInfo_InternetPhone end as \"Person_InetPhone\",
			case when laf.Lpu_id is null then ''
				when a1.Address_id is not null
				then a1.Address_Address
				else a.Address_Address
			end	as \"Address_Address\",
			case when a1.Address_id is not null
				then a1.KLTown_id
				else a.KLTown_id
			end as \"KLTown_id\",
			case when laf.Lpu_id is null then ''
				when a1.Address_id is not null
				then a1.KLStreet_id::varchar
				else a.KLStreet_id::varchar
			end as \"KLStreet_id\",
			case when a1.Address_id is not null
				then a1.Address_House
				else a.Address_House
			end as \"Address_House\",
		";
		//Ufa, gaf #116387, для ГАУЗ РВФД
		if ((isSuperadmin() || $param["Lpu_id"] == 81) && $callObject->getRegionNick() == "ufa") {
			$selectPersonData .= "
				(
					select pp.post_name
					from v_PersonState vps
					    ,job jj
					    ,post pp
					where vps.person_id = t.person_id
					  and vps.job_id = jj.job_id
					  and jj.post_id = pp.Post_id
				) as \"Job_Name\",
			";
		} else {
			$selectPersonData .= "
				case when laf.Lpu_id is null then ''
					else j.Job_Name
				end as \"Job_Name\",
			";
		}
		$selectPersonData .= "
			case when laf.Lpu_id is null then '' else lpu.Lpu_Nick end as \"Lpu_Nick\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Firname) end as \"Person_Firname\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Surname) end as \"Person_Surname\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Secname) end as \"Person_Secname\",
			case when laf.Lpu_id is null then '1' else '0' end as \"Person_Filter\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_BirthDay else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then pcs.PersonCardState_Code else null end as \"PersonCard_Code\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then pcs.PersonCard_id else null end as \"PersonCard_id\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.PersonInfo_InternetPhone else null end as \"Person_InetPhone\",
				case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.Address_Address else a.Address_Address
				end as \"Address_Address\",
				case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.KLTown_id else a.KLTown_id
				end as \"KLTown_id\",
				case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.KLStreet_id else a.KLStreet_id
				end as \"KLStreet_id\",
				case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.Address_House else a.Address_House
				end as \"Address_House\",
			";
			//ГАУЗ РВФД, #116387, gilmiyarov_25092017
			if (((isSuperadmin() || $param["Lpu_id"] == 81) && $callObject->getRegionNick() == "ufa")) {
				$selectPersonData .= "
					(
						select pp.post_name
						from v_PersonState vps
						    ,job jj
						    ,post pp
						where vps.person_id = t.person_id
						  and vps.job_id = jj.job_id
						  and jj.post_id = pp.Post_id
					) as \"Job_Name\",
				";
			} else {
				$selectPersonData .= "
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null
						then j.Job_Name
						else null
					end as \"Job_Name\",
				";
			}
			$selectPersonData .= "
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null
					then lpu.Lpu_Nick
					else null
				end as \"Lpu_Nick\",
				case when laf.Lpu_id is null then ''
					when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Surname)
					else rtrim(peh.PersonEncrypHIV_Encryp)
				end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
				case when laf.Lpu_id is null then '1' else '0' end as \"Person_Filter\",
			";
		}
		// Для Астрахани выводится информация о полисе
		$selectPolis = "";
		$joinPolis = "";
		if ($callObject->getRegionNick() == "astra") {
			$selectPolis = "
				case when pol.PolisType_id = 4 then '' else pol.Polis_Ser end as \"Polis_Ser\",
				case when pol.PolisType_id = 4 then p.Person_EdNum else pol.Polis_Num end as \"Polis_Num\",
			";
			$joinPolis = " left join v_polis pol on pol.polis_id = p.polis_id";
		}
		$selectString = "
			t.pmUser_updID as \"pmUser_updID\",
			t.TimetableGraf_updDT as \"TimetableGraf_updDT\",
			t.TimetableGraf_id as \"TimetableGraf_id\",
			trl.Person_id as \"Person_id\",
			trl.TimetableGrafRecList_id as \"TimetableGrafRecList_id\",
			t.TimetableGraf_Day as \"TimetableGraf_Day\",
			t.TimetableGraf_begTime as \"TimetableGraf_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableGraf_IsDop as \"TimetableGraf_IsDop\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			{$selectPersonData}
			t.PMUser_UpdID as \"PMUser_UpdID\",
			case
				when t.pmUser_updid=999000
				then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000
				then 'Запись через интернет'
				else u.PMUser_Name
			end as \"PMUser_Name\",
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			ed.EvnDirection_Num as \"Direction_Num\",
			coalesce(et.ElectronicTalon_Num::varchar, ED.EvnDirection_TalonCode) as \"Direction_TalonCode\",
			t.EvnDirection_id as \"EvnDirection_tid\",
			to_char(ed.EvnDirection_setDate, '{$callObject->dateTimeForm104}') as \"Direction_Date\",
			ed.EvnDirection_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			q.EvnQueue_insDT as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			t.TimetableGraf_IsModerated as \"TimetableGraf_IsModerated\",
			msf.Lpu_id as \"Lpu_id\",
			t.TimetableExtend_Descr as \"TimetableExtend_Descr\",
			t.TimetableExtend_updDT as \"TimetableExtend_updDT\",
			ud.pmUser_Name as \"TimetableExtend_pmUser_Name\",
			msf.MedStaffFact_id as \"MedStaffFact_id\",
			msf.MedPersonal_id as \"MedPersonal_id\",
			msf.LpuUnit_id as \"LpuUnit_id\",
			{$selectPolis}
			case when t.TimetableGraf_factTime is not null then 2 else 1 end as \"Person_IsPriem\"
		";
		$fromString = "
				v_TimetableGraf t
				left join v_TimeTableGrafRecList trl on trl.TimeTableGraf_id = t.TimeTableGraf_id
				left outer join v_MedStaffFact_ER msf on msf.MedStaffFact_id = t.MedStaffFact_id
				left outer join v_Person_ER p on trl.Person_id = p.Person_id
				left join lateral (
					select PersonCardState_Code
						  ,PersonCard_id
					from PersonCardState pcs
					where pcs.Person_id = p.Person_id
					  and pcs.Lpu_id = msf.Lpu_id
					order by LpuAttachType_id
					limit 1
				) as pcs on true
				left join lateral (
					select Lpu_id
					from v_PersonCardState
					where Person_id = p.Person_id
					order by LpuAttachType_id
					limit 1
				) as pcs_l on true
				left outer join Address a on p.UAddress_id = a.Address_id
				left outer join Address a1 on p.PAddress_id = a1.Address_id
				left outer join KLStreet pas on a.KLStreet_id = pas.KLStreet_id
				left outer join KLStreet pas1 on a1.KLStreet_id = pas1.KLStreet_id
				left outer join v_Job_ER j on p.Job_id=j.Job_id
				left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
				left outer join v_pmUser ud on t.TimetableExtend_pmUser_updid = ud.PMUser_id
				left outer join v_Lpu lpu on lpu.Lpu_id = pcs_l.Lpu_id
				left outer join v_EvnDirection d on trl.EvnDirection_id=d.EvnDirection_id and d.DirFailType_id is null and d.Person_id = trl.Person_id
				left join v_EvnDirection_all ed on ed.EvnDirection_id = trl.EvnDirection_id
				left outer join v_Lpu lpud on lpud.Lpu_id = ed.Lpu_id
				left join v_EvnQueue q on t.TimetableGraf_id = q.TimetableGraf_id and trl.Person_id = q.Person_id
				left join v_pmUser qp on q.pmUser_updId=qp.pmUser_id
				left join Diag dg on dg.Diag_id=ed.Diag_id
				left join v_ElectronicTalon et on et.EvnDirection_id = trl.EvnDirection_id
				{$joinPolis}
				{$joinPersonEncrypHIV}
				{$joinAccessFilter}		
		";
		$whereString = "
				t.TimeTableGraf_id = :TimeTableGraf_id
			and t.MedStaffFact_Id = :MedStaffFact_id
			and t.TimetableGraf_begTime is not null
		";
		$sql = "
			select {$selectString}
			from {$fromString}
			where {$whereString}
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($sql, $param);
		$ttgdata = $res->result("array");
		foreach ($ttgdata as $ttg) {
			$outdata["data"][] = $ttg;
		}
		$defaultTimeTableGraf = $ttgdata[0];
		$arrAttrNotNull = [
			"pmUser_updID",
			"TimetableGraf_updDT",
			"TimetableGraf_id",
			"TimetableGraf_Day",
			"TimetableGraf_begTime",
			"TimetableType_id",
			"TimetableGraf_IsDop",
			"Lpu_Nick",
			"Person_Filter",
			"PMUser_UpdID",
			"QpmUser_Name",
			"EvnQueue_insDT",
			"Diag_Code",
			"pmUser_Lpu_id",
			"TimetableGraf_IsModerated",
			"Lpu_id",
			"TimetableExtend_Descr",
			"TimetableExtend_updDT",
			"TimetableExtend_pmUser_Name",
			"MedStaffFact_id",
			"MedPersonal_id",
			"LpuUnit_id",
			"Person_IsPriem"
		];
		foreach ($defaultTimeTableGraf as $key => $attr) {
			$defaultTimeTableGraf[$key] = null;
			if (in_array($key, $arrAttrNotNull))
				$defaultTimeTableGraf[$key] = $attr;
		}
		for ($i = 0; $i < (intval($TimeTableGraf["TimeTableGraf_PersRecLim"]) - count($ttgdata)); $i++) {
			$outdata["data"][] = $defaultTimeTableGraf;
		}
		$sql = "select TimetableGraf_id  as \"TimetableGraf_id\" from TimetableLock where TimetableGraf_id is not null";
		$res = $callObject->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $res->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableGraf_id"];
		}
		return $outdata;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getTimetableGrafOneDay(TimetableGraf_model $callObject, $data)
	{
		$outdata = [];
		if (!isset($data["MedStaffFact_id"])) {
			throw new Exception("Не указан врач, для которого показывать расписание");
		}
		$StartDay = isset($data["StartDay"]) ? strtotime($data["StartDay"]) : time();
		$outdata["StartDay"] = $StartDay;

		$param["pmUser_id"] = $data["pmUser_id"];
		$param["StartDay"] = TimeToDay($StartDay);
		$param["StartDayA"] = $param["StartDay"] - 1;
		$param["MedStaffFact_id"] = $data["MedStaffFact_id"];
		$param["Lpu_id"] = $data["Lpu_id"];
		$param["nulltime"] = "00:00:00";
		$param["EndDate"] = date("Y-m-d", $StartDay);
		if ($data["PanelID"] == "TTGRecordOneDayPanel") {
			$msflpu = $callObject->getFirstRowFromQuery("select Lpu_id  as \"Lpu_id\" from v_MedStaffFact where MedStaffFact_id = ?", [$data["MedStaffFact_id"]]);
			if (empty($_SESSION["setting"]) || empty($_SESSION["setting"]["server"])) {
				// Вынес отдельно, чтобы не повторять
				$maxDays = null;
			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "regpol" && $_SESSION["lpu_id"] == $msflpu["Lpu_id"]) {
				// Для регистратора запись в свою МО
				$callObject->load->model("LpuIndividualPeriod_model", "lipmodel");
				$individualPeriod = $callObject->lipmodel->getObjectIndividualPeriod(["Lpu_id" => $_SESSION["lpu_id"]], "MedStaffFact");
				if (!empty($data["MedStaffFact_id"]) && !empty($individualPeriod[$data["MedStaffFact_id"]])) {
					$maxDays = $individualPeriod[$data["MedStaffFact_id"]];
				} else {
					$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count"]) ? $_SESSION["setting"]["server"]["pol_record_day_count"] : null;
				}
			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "regpol") {
				// Для регистратора запись в чужую МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_reg"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_reg"] : null;
			} elseif (!empty($_SESSION["CurArmType"]) && $_SESSION["CurArmType"] == "callcenter") {
				// Для оператора call-центра
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_cc"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_cc"] : null;
			} elseif ($_SESSION["lpu_id"] == $msflpu["Lpu_id"]) {
				// Для остальных пользовалелей запись в свою МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_own"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_own"] : null;
			} else {
				// Для остальных пользовалелей запись в чужую МО
				$maxDays = !empty($_SESSION["setting"]["server"]["pol_record_day_count_other"]) ? $_SESSION["setting"]["server"]["pol_record_day_count_other"] : null;
			}
			if ($maxDays) $maxDays--; // лишний день
			if (date("H:i") >= getShowNewDayTime() && $maxDays) $maxDays++;
			$param["EndDate"] = !empty($maxDays) ? date("Y-m-d", strtotime("+" . $maxDays . " days", time())) : $param["EndDate"];
		}
		$outdata["day_comment"] = null;
		$outdata["data"] = [];
		$sql = "
			select
				D.Day_id as \"Day_id\",
				rtrim(A.Annotation_Comment) as \"Annotation_Comment\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				to_char(A.Annotation_updDT, 'dd.mm.yyyy HH24:MI') as \"Annotation_updDT\"
			from
				v_Day D
				left join v_Annotation A on
					(A.Annotation_begDate <= D.day_date) AND
					(A.Annotation_endDate >= D.day_date OR A.Annotation_endDate is null) AND
					(A.Annotation_begTime is null or A.Annotation_begTime = :nulltime) AND
					(A.Annotation_endTime is null or A.Annotation_endTime = :nulltime)
				left join v_pmUser u on u.pmUser_id = A.pmUser_updID
				left join v_MedStaffFact msf on msf.MedStaffFact_id = A.MedStaffFact_id
			where A.MedStaffFact_id = :MedStaffFact_id
			  and D.Day_id = :StartDayA
			  and (A.AnnotationVison_id != 3 or msf.Lpu_id = :Lpu_id)
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($sql, $param);
		$daydescrdata = $res->result("array");
		foreach ($daydescrdata as $day) {
			/**@var DateTime $Annotation_updDT */
			$Annotation_updDT = $day["Annotation_updDT"];
			$outdata["day_comment"][] = [
				"Annotation_Comment" => $day["Annotation_Comment"],
				"pmUser_Name" => $day["pmUser_Name"],
				"Annotation_updDT" => isset($day["Annotation_updDT"]) ? DateTime::createFromFormat("d.m.Y H:i", $Annotation_updDT)->format( "d.m.Y H:i" ) : ""
			];
		}
		$lpuFilter = getAccessRightsLpuFilter("laf.Lpu_id");
		$joinAccessFilter = (!empty($lpuFilter))
			? " left join v_Lpu laf on laf.Lpu_id = msf.Lpu_id and ($lpuFilter or t.pmUser_updID = :pmUser_id)"
			: " left join v_Lpu laf on laf.Lpu_id = msf.Lpu_id";
		$selectPersonData = "
			case when laf.Lpu_id is null then null else to_char(p.Person_BirthDay, '{$callObject->dateTimeForm104}') end as \"Person_BirthDay\",
			case when laf.Lpu_id is null then '' else p.Person_Phone end as \"Person_Phone\",
			pcs.PersonCardState_Code as \"PersonCard_Code\",
			pcs.PersonCard_id as \"PersonCard_id\",
			case when laf.Lpu_id is null then null else p.PersonInfo_InternetPhone end as \"Person_InetPhone\",
			case when laf.Lpu_id is null then ''
				when a1.Address_id is not null then a1.Address_Address
				else a.Address_Address
			end	as \"Address_Address\",
			case when a1.Address_id is not null
				then a1.KLTown_id
				else a.KLTown_id
			end as \"KLTown_id\",
			case when laf.Lpu_id is null then ''
				when a1.Address_id is not null
					then a1.KLStreet_id::varchar
					else a.KLStreet_id::varchar
			end as \"KLStreet_id\",
			case when a1.Address_id is not null
				then a1.Address_House
				else a.Address_House
			end as \"Address_House\",
		";
		//Ufa, gaf #116387, для ГАУЗ РВФД
		if ((isSuperadmin() || $param["Lpu_id"] == 81) && $callObject->getRegionNick() == "ufa") {
			$selectPersonData .= "
				(
					select pp.post_name
					from v_PersonState vps
					    ,job jj
					    ,post pp
					where vps.person_id = t.person_id
					  and vps.job_id = jj.job_id
					  and jj.post_id = pp.Post_id
				) as \"Job_Name\",			
			";
		} else {
			$selectPersonData .= "
				case when laf.Lpu_id is null then '' else j.Job_Name end as \"Job_Name\",
			";
		}
		$selectPersonData .= "
			case when laf.Lpu_id is null then '' else lpu.Lpu_Nick end as \"Lpu_Nick\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Firname) end as \"Person_Firname\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Surname) end as \"Person_Surname\",
			case when laf.Lpu_id is null then '' else rtrim(p.Person_Secname) end as \"Person_Secname\",
			case when laf.Lpu_id is null then '1' else '0' end as \"Person_Filter\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then to_char(p.Person_BirthDay, 'dd.mm.yyyy') else null end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.Person_Phone else null end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then pcs.PersonCardState_Code else null end as \"PersonCard_Code\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then pcs.PersonCard_id else null end as \"PersonCard_id\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then p.PersonInfo_InternetPhone else null end as \"Person_InetPhone\",
				case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.Address_Address
					else a.Address_Address
				end as \"Address_Address\",
				case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.KLTown_id
					else a.KLTown_id
				end as \"KLTown_id\",
				case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.KLStreet_id
					else a.KLStreet_id
				end as \"KLStreet_id\",
				case when peh.PersonEncrypHIV_Encryp is not null or laf.Lpu_id is null then null
					when a1.Address_id is not null then a1.Address_House
					else a.Address_House
				end as \"Address_House\",
			";
			//ГАУЗ РВФД, #116387, gilmiyarov_25092017
			if ((isSuperadmin() || $param['Lpu_id'] == 81) && $callObject->getRegionNick() == "ufa") {
				$selectPersonData .= "
					(
						select pp.post_name
						from v_PersonState vps
						    ,job jj
						    ,post pp
						where vps.person_id = t.person_id
						  and vps.job_id = jj.job_id
						  and jj.post_id = pp.Post_id
					) as \"Job_Name\",
				";
			} else {
				$selectPersonData .= "
					case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then j.Job_Name else null end as \"Job_Name\",
				";
			}
			$selectPersonData .= "
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then lpu.Lpu_Nick else null end as \"Lpu_Nick\",
				case when laf.Lpu_id is null then ''
					when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Surname)
					else rtrim(peh.PersonEncrypHIV_Encryp)
				end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null and laf.Lpu_id is not null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
				case when laf.Lpu_id is null then '1' else '0' end as \"Person_Filter\",
			";
		}
		// Для Астрахани выводится информация о полисе
		$selectPolis = "";
		$joinPolis = "";
		if ($callObject->getRegionNick() == "astra") {
			$selectPolis = "
				case when pol.PolisType_id = 4 then '' else pol.Polis_Ser end as \"Polis_Ser\",
				case when pol.PolisType_id = 4 then p.Person_EdNum else pol.Polis_Num end as \"Polis_Num\",
			";
			$joinPolis = " left join v_polis pol on pol.polis_id = p.polis_id";
		}

		$selectString = "
			t.pmUser_updID as \"pmUser_updID\",
			to_char(t.TimetableGraf_updDT, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableGraf_updDT\",
			t.TimetableGraf_id as \"TimetableGraf_id\",
			t.Person_id as \"Person_id\",
			t.TimetableGraf_Day as \"TimetableGraf_Day\",
			to_char(t.TimetableGraf_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"TimetableGraf_begTime\",
			t.TimetableType_id as \"TimetableType_id\",
			t.TimetableGraf_IsDop as \"TimetableGraf_IsDop\",
			t.TimeTableGraf_countRec as \"TimeTableGraf_countRec\",
			t.TimeTableGraf_PersRecLim as \"TimeTableGraf_PersRecLim\",
			p.PrivilegeType_id as \"PrivilegeType_id\",
			{$selectPersonData}
			t.PMUser_UpdID as \"PMUser_UpdID\",
			case
				when t.pmUser_updid=999000 then 'Запись через КМИС'
				when t.pmUser_updid between 1000000 and 5000000 then 'Запись через интернет'
				else u.PMUser_Name
			end as \"PMUser_Name\",
			lpud.Lpu_Nick as \"DirLpu_Nick\",
			d.EvnDirection_Num as \"Direction_Num\",
			coalesce(et.ElectronicTalon_Num::varchar, ED.EvnDirection_TalonCode) as \"Direction_TalonCode\",
			t.EvnDirection_id as \"EvnDirection_tid\",
			to_char(d.EvnDirection_setDate, '{$callObject->dateTimeForm104}') as \"Direction_Date\",
			d.EvnDirection_id as \"EvnDirection_id\",
			qp.pmUser_Name as \"QpmUser_Name\",
			q.EvnQueue_insDT as \"EvnQueue_insDT\",
			dg.Diag_Code as \"Diag_Code\",
			u.Lpu_id as \"pmUser_Lpu_id\",
			t.TimetableGraf_IsModerated as \"TimetableGraf_IsModerated\",
			msf.Lpu_id as \"Lpu_id\",
			TimetableExtend_Descr as \"TimetableExtend_Descr\",
			t.TimetableExtend_updDT as \"TimetableExtend_updDT\",
			ud.pmUser_Name as \"TimetableExtend_pmUser_Name\",
			msf.MedStaffFact_id as \"MedStaffFact_id\",
			msf.MedPersonal_id as \"MedPersonal_id\",
			msf.LpuUnit_id as \"LpuUnit_id\",
			{$selectPolis}
			case when t.TimetableGraf_factTime is not null then 2 else 1 end as \"Person_IsPriem\"
		";
		$fromString = "
			v_TimetableGraf t
			left outer join v_MedStaffFact_ER msf on msf.MedStaffFact_id = t.MedStaffFact_id
			left outer join v_Person_ER p on t.Person_id = p.Person_id
			left join lateral (
				select PersonCardState_Code, PersonCard_id
				from PersonCardState pcs
				where pcs.Person_id = p.Person_id and pcs.Lpu_id = msf.Lpu_id
				order by LpuAttachType_id
				limit 1
			) as pcs on true
			left join lateral (
				select Lpu_id
				from v_PersonCardState
				where Person_id = p.Person_id
				order by LpuAttachType_id
				limit 1
			) as pcs_l on true
			left outer join Address a on p.UAddress_id = a.Address_id
			left outer join Address a1 on p.PAddress_id = a1.Address_id
			left outer join KLStreet pas on a.KLStreet_id = pas.KLStreet_id
			left outer join KLStreet pas1 on a1.KLStreet_id = pas1.KLStreet_id
			left outer join v_Job_ER j on p.Job_id=j.Job_id
			left outer join v_pmUser u on t.PMUser_UpdID = u.PMUser_id
			left outer join v_pmUser ud on t.TimetableExtend_pmUser_updid = ud.PMUser_id
			left outer join v_Lpu lpu on lpu.Lpu_id = pcs_l.Lpu_id
			left outer join v_EvnDirection d on t.TimetableGraf_id=d.TimetableGraf_id and d.DirFailType_id is null and d.Person_id = t.Person_id
			left outer join v_Lpu lpud ON lpud.Lpu_id = d.Lpu_id
			left join v_EvnQueue q on t.TimetableGraf_id = q.TimetableGraf_id and t.Person_id = q.Person_id
			left join v_pmUser qp on q.pmUser_updId=qp.pmUser_id
			left join Diag dg on dg.Diag_id=d.Diag_id
			left join v_EvnDirection_all ed on ed.EvnDirection_id = t.EvnDirection_id
			left join v_ElectronicTalon et on et.EvnDirection_id = t.EvnDirection_id
		";
		$whereString = "
				t.TimetableGraf_Day = :StartDay
			and t.TimetableGraf_begTime <= :EndDate
			and t.MedStaffFact_Id = :MedStaffFact_id
			and t.TimetableGraf_begTime is not null
		";
		$orderByString = "t.TimetableGraf_begTime";
		$sql = "
			select
				{$selectString}
			from
				{$fromString}
				{$joinPolis}
				{$joinPersonEncrypHIV}
				{$joinAccessFilter}
			where {$whereString}
			order by {$orderByString}
		";
		/**@var CI_DB_result $res */
		$res = $callObject->db->query($sql, $param);
		$ttgdata = $res->result("array");
		foreach ($ttgdata as $ttg) {
			$outdata["data"][] = $ttg;
		}
		$sql = "
			select TimetableGraf_id as \"TimetableGraf_id\"
			from TimetableLock
			where TimetableGraf_id is not null
		";
		$res = $callObject->db->query($sql);
		$outdata["reserved"] = [];
		$reserved = $res->result("array");
		foreach ($reserved as $lock) {
			$outdata["reserved"][] = $lock["TimetableGraf_id"];
		}
		return $outdata;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getTopTimetable(TimetableGraf_model $callObject, $data)
	{
		$sql = "
			select
				ttc.TimetableCount_id as \"TimetableCount_id\",
			    ttc.TimetableCount_Count as \"TimetableCount_Count\",
			    ttc.TimetableObject_id as \"TimetableObject_id\",
			    ttc.MedPersonal_id as \"MedPersonal_id\",
			    ttc.MedStaffFact_id as \"MedStaffFact_id\",
			    ttc.LpuSection_id as \"LpuSection_id\",
			    case when ttc.TimetableObject_id > 1
					then lc.LpuSection_Name||' / '||lcl.Lpu_Nick
					else mp.Person_Fin||' / '||lcm.LpuSection_Name||' / '||lcml.Lpu_Nick
				end as \"caption\",
				case when ttc.TimetableObject_id > 1
				    then lc.LpuSectionProfile_id
					else lcm.LpuSectionProfile_id
				end as \"LpuSectionProfile_id\",
			    mp.Person_Fio as \"MedPersonal_FIO\",
			    case when ttc.TimetableObject_id > 1
			    	then lc.LpuUnit_id
			        else lcm.LpuUnit_id
			    end as \"LpuUnit_id\",
			    case when ttc.TimetableObject_id > 1
			        then lc.LpuSection_Name
			        else lcm.LpuSection_Name
			    end as \"LpuSection_Name\",
			    case when ttc.TimetableObject_id > 1
			        then lcl.Lpu_id
			        else lcml.Lpu_id
			    end as \"Lpu_id\",
			    case when ttc.TimetableObject_id > 1
			        then lcl.Lpu_Nick
			        else lcml.Lpu_Nick
			    end as \"Lpu_Nick\"
			from
				v_TimetableCount ttc
				left join v_TimetableObject tto on ttc.TimetableObject_id = tto.TimetableObject_id
				left join v_LpuSection lc on ttc.LpuSection_id = lc.LpuSection_id
				left join v_Lpu lcl on lc.Lpu_id = lcl.Lpu_id
				left join v_MedStaffFact msf on ttc.MedStaffFact_id = msf.MedStaffFact_id
				left join v_MedPersonal mp on ttc.MedPersonal_id = mp.MedPersonal_id AND msf.Lpu_id = mp.Lpu_id
				left join v_LpuSection lcm on msf.LpuSection_id = lcm.LpuSection_id
				left join v_Lpu lcml on msf.Lpu_id = lcml.Lpu_id
			where ttc.pmUser_insID = :pmUser_id
			order by ttc.TimetableCount_Count DESC
			limit 20
		";
		$params["pmUser_id"] = $data["pmUser_id"];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public static function getTTDescrHistory(TimetableGraf_model $callObject, $data)
	{
		$sql = "";
		if (isset($data["TimetableGraf_id"])) {
			$sql = "
				select
					to_char(TimetableExtendHist_insDT, '{$callObject->dateTimeForm104}')||' '||to_char(TimetableExtendHist_insDT, '{$callObject->dateTimeForm108}') as \"TimetableExtendHist_insDT\",
					rtrim(PMUser_Name) as \"PMUser_Name\",
					TimetableExtend_Descr as \"TimetableExtend_Descr\"
				from TimetableExtendHist tteh
					 left join v_pmUser pu on tteh.TimetableExtendHist_userID = pu.pmuser_id
				where TimetableGraf_id = :TimetableGraf_id
			";
		}
		if (isset($data["TimetableStac_id"])) {
			$sql = "
				select
					to_char(TimetableExtendHist_insDT, '{$callObject->dateTimeForm104}')||' '||to_char(TimetableExtendHist_insDT, '{$callObject->dateTimeForm108}') as \"TimetableExtendHist_insDT\",
					rtrim(PMUser_Name) as \"PMUser_Name\",
					TimetableExtend_Descr as \"TimetableExtend_Descr\"
				from TimetableExtendHist tteh
					 left join v_pmUser pu on tteh.TimetableExtendHist_userID = pu.pmuser_id
				where TimetableStac_id = :TimetableStac_id
			";
		}
		if (isset($data["TimetableMedService_id"])) {
			$sql = "
				select
					to_char(TimetableExtendHist_insDT, '{$callObject->dateTimeForm104}')||' '||to_char(TimetableExtendHist_insDT, '{$callObject->dateTimeForm108}') as \"TimetableExtendHist_insDT\",
					rtrim(PMUser_Name) as \"PMUser_Name\",
					TimetableExtend_Descr as \"TimetableExtend_Descr\"
				from TimetableExtendHist tteh
					 left join v_pmUser pu on tteh.TimetableExtendHist_userID = pu.pmuser_id
				where TimetableMedService_id = :TimetableMedService_id
			";
		}
		if (isset($data["TimetableResource_id"])) {
			$sql = "
				select
					to_char(TimetableExtendHist_insDT, '{$callObject->dateTimeForm104}')||' '||to_char(TimetableExtendHist_insDT, '{$callObject->dateTimeForm108}') as \"TimetableExtendHist_insDT\",
					rtrim(PMUser_Name) as \"PMUser_Name\",
					TimetableExtend_Descr as \"TimetableExtend_Descr\"
				from TimetableExtendHist tteh
					 left join v_pmUser pu on tteh.TimetableExtendHist_userID = pu.pmuser_id
				where TimetableResource_id = :TimetableResource_id
			";
		}
		$sqlParams = [
			"TimetableGraf_id" => $data["TimetableGraf_id"],
			"TimetableStac_id" => $data["TimetableStac_id"],
			"TimetableMedService_id" => $data["TimetableMedService_id"],
			"TimetableResource_id" => $data["TimetableResource_id"],
		];
		if ($sql == "") {
			throw new Exception("Не пришел обязательный параметр выполнения запроса.");
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public static function getTTGDataForMail(TimetableGraf_model $callObject, $data)
	{
		$selectPersonData = "
			rtrim(p.Person_Firname) as \"Person_Firname\",
			rtrim(p.Person_Surname) as \"Person_Surname\",
			rtrim(p.Person_Secname) as \"Person_Secname\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Surname) else rtrim(peh.PersonEncrypHIV_Encryp) end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Firname) else '' end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then rtrim(p.Person_Secname) else '' end as \"Person_Secname\",
			";
		}
		$selectString = "
			ttg.TimetableGraf_begTime as \"TimetableGraf_begTime\",
			{$selectPersonData}
			MedPersonal_FIO as \"MedPersonal_FIO\",
			msf.Person_Surname as \"Med_Person_Surname\",
			msf.Person_Firname as \"Med_Person_Firname\",
			msf.Person_Secname as \"Med_Person_Secname\",
			ttg.pmUser_updID as \"User_id\",
			TimetableGraf_IsModerated as \"TimetableGraf_IsModerated\",
			lsp.ProfileSpec_Name_Rod as \"ProfileSpec_Name_Rod\"
		";
		$query = "
			select {$selectString}
			from
				v_TimetableGraf_lite ttg
				left join v_Person_ER p on ttg.Person_id = p.Person_id
				left join v_MedStaffFact_ER msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_LpuSectionProfile lsp on msf.LpuSectionProfile_id = lsp.LpuSectionProfile_id
				{$joinPersonEncrypHIV}
			where TimetableGraf_id = :TimetableGraf_id
			  and ttg.Person_id is not null
		";
		$queryParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при получении данных пользователя для отправки сообщений");
		}
		$ttgData = $result->result("array");
		$callObject->dbkvrachu = $callObject->load->database("UserPortal", true); // загружаем БД к-врачу.ру
		if (KVRACHU_TYPE == 2) {
			$selectString = implode(", ", [
				"Users.id as \"User_id\"",
				"Users.first_name as \"FirstName\"",
				"Users.second_name as \"MidName\"",
				"UserNotify_Phone as \"UserNotify_Phone\"",
				"VizitNotify_Email as \"UserNotify_AcceptIsEmail\"",
				"VizitNotify_SMS as \"UserNotify_AcceptIsSMS\"",
				"Users.email as \"EMail\""
			]);
			$fromString = implode(" ", [
				"UserNotify",
				"inner join Users on Users.id = UserNotify.User_id",
				"inner join Person on Person.pmUser_id = UserNotify.User_id",
				"left join VizitNotify on VizitNotify.pmUser_id = UserNotify.User_id and VizitNotify.TimetableGraf_id = :TimetableGraf_id"
			]);
			$whereString = implode(" and ", [
				"Users.id = :User_id"
			]);
			$query = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
				limit 1
			";
		} else {
			$selectString = implode(", ", [
				"u.User_id as \"User_id\"",
				"u.EMail as \"EMail\"",
				"u.FirstName as \"FirstName\"",
				"u.MidName as \"MidName\"",
				"un.UserNotify_Phone as \"UserNotify_Phone\"",
				"un.UserNotify_AcceptIsEmail as \"UserNotify_AcceptIsEmail\"",
				"un.UserNotify_AcceptIsSMS as \"UserNotify_AcceptIsSMS\"",
			]);
			$fromString = implode(" ", [
				"Usr u",
				"left join UserNotify un on u.User_id = un.User_id"
			]);
			$whereString = implode(" and ", [
				"u.User_id = :User_id"
			]);
			$query = "
				select {$selectString}
				from {$fromString}
				where {$whereString}
			";
		}
		$queryParams = [
			"User_id" => $ttgData[0]["User_id"],
			"TimetableGraf_id" => $data["TimetableGraf_id"]
		];
		/**@var CI_DB_driver $dbkvrachu */
		$dbkvrachu = $callObject->dbkvrachu;
		$result = $dbkvrachu->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при получении данных пользователя для отправки сообщений");
		}
		$userData = $result->result("array");
		if (count($userData) > 0) {
			$userData[0] = array_merge($ttgData[0], $userData[0]);
		}
		return $userData;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getTTGForModeration(TimetableGraf_model $callObject, $data)
	{
		$queryParams = [];
		$sFilters = "(1 = 1)";
		$topFilters = "";

		$isSearchByEncryp = false;
		$selectPersonData = "
			p.Person_surname||' '||p.Person_firname||' '||coalesce(p.Person_secname, '') as \"PersonFullName\",
			case
				when p.Person_Phone is null and p.PersonInfo_InternetPhone is null then ''
				when p.Person_Phone is not null and p.PersonInfo_InternetPhone is not null then 'В нашей базе: '||p.Person_Phone||'<br />Указано человеком: '||coalesce(p.PersonInfo_InternetPhone, '')
				when p.Person_Phone is not null then 'В нашей базе: '||p.Person_Phone
				when p.PersonInfo_InternetPhone is not null then 'Указано человеком: '||coalesce(p.PersonInfo_InternetPhone, '')
			end as \"Person_Phone\",
			to_char(p.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
			rtrim(replace(coalesce(a1.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', '')) as \"PAddress_Address\",
			rtrim(replace(coalesce(a.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', ''))  as \"UAddress_Address\",
			case when a1.Address_id is not null
				then rtrim(a1.Address_House)
				else rtrim(a.Address_House)
			end as \"Address_House\",
			case when a1.Address_id is not null
				then a1.KLStreet_id
				else a.KLStreet_id
			end as \"KLStreet_id\",
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$isSearchByEncryp = isSearchByPersonEncrypHIV($data["Person_Surname"]);
			$joinPersonEncrypHIV = " left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null
					then p.Person_surname||' '||p.Person_firname||' '||coalesce(p.Person_secname, '')
					else rtrim(peh.PersonEncrypHIV_Encryp)
				end as \"PersonFullName\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when p.Person_Phone is null and p.PersonInfo_InternetPhone is null then ''
					when p.Person_Phone is not null and p.PersonInfo_InternetPhone is not null then 'В нашей базе: '||p.Person_Phone||'<br />Указано человеком: '||coalesce(p.PersonInfo_InternetPhone, '')
					when p.Person_Phone is not null then 'В нашей базе: '||p.Person_Phone
					when p.PersonInfo_InternetPhone is not null then 'Указано человеком: '||coalesce(p.PersonInfo_InternetPhone, '')
				end as \"Person_Phone\",
				case when peh.PersonEncrypHIV_Encryp is null then to_char(p.Person_BirthDay, '{$callObject->dateTimeForm104}') end as \"Person_BirthDay\",
				case when peh.PersonEncrypHIV_Encryp is null then (rtrim(replace(coalesce(a.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', ''))) end as \"UAddress_Address\",
				case when peh.PersonEncrypHIV_Encryp is null then (rtrim(replace(coalesce(a1.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', ''))) end as \"PAddress_Address\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then rtrim(a1.Address_House)
					else rtrim(a.Address_House)
				end as \"Address_House\",
				case when peh.PersonEncrypHIV_Encryp is not null then null
					when a1.Address_id is not null then rtrim(a1.KLStreet_id)
					else a.KLStreet_id
				end as \"KLStreet_id\",
			";
		}
		if (!empty($data["Person_Surname"])) {
			if (allowPersonEncrypHIV($data["session"]) && $isSearchByEncryp) {
				$sFilters .= " and peh.PersonEncrypHIV_Encryp ilike :Person_Surname";
			} else {
				$sFilters .= " and p.Person_SurName ilike :Person_Surname ";
			}
			$queryParams["Person_Surname"] = $data["Person_Surname"] . "%";
		}
		if (!empty($data["Person_Firname"])) {
			$sFilters .= " and p.Person_FirName ilike :Person_FirName ";
			$queryParams["Person_FirName"] = $data["Person_Firname"] . "%";
		}
		if (!empty($data["Person_Secname"])) {
			$sFilters .= " and p.Person_SecName ilike :Person_SecName ";
			$queryParams["Person_SecName"] = $data["Person_Secname"] . "%";
		}
		if (!empty($data["Person_Phone"])) {
			$sFilters .= " and p.Person_Phone ilike :Person_Phone ";
			$queryParams["Person_Phone"] = $data["Person_Phone"] . "%";
		}
		if (!empty($data["ModerateType_id"])) {
			if ($data["ModerateType_id"] == 1) {
				$sFilters .= " and g.TimetableGraf_IsModerated = 1 ";
			}
			if ($data["ModerateType_id"] == 2) {
				$sFilters .= " and (g.TimetableGraf_IsModerated is null or g.TimetableGraf_IsModerated = 2) ";
			}
			if ($data["ModerateType_id"] == 3) {
				$sFilters .= " and g.TimetableGraf_IsModerated = 2 ";
			}
			if ($data["ModerateType_id"] == 4) {
				$sFilters .= " and g.TimetableGraf_IsModerated is null";
			}
		}
		if (!empty($data["StartDate"])) {
			$sFilters .= " and g.timetablegraf_upddt = :StartDate ";
			$queryParams["StartDate"] = $data["StartDate"];
		}
		if (!empty($data["ZapDate"])) {
			$sFilters .= " and g.timetablegraf_begtime = :ZapDate ";
			$queryParams["ZapDate"] = $data["ZapDate"];
		}

		if (!empty($data["MedPersonal_id"])) {
			$sFilters .= " and msf.MedPersonal_id = :MedPersonal_id";
			$queryParams["MedPersonal_id"] = $data["MedPersonal_id"];
		}

		if (!empty($data["KLCity_id"])) {
			$sFilters .= " and lua.KLCity_id = :KLCity_id ";
			$queryParams["KLCity_id"] = $data["KLCity_id"];
		}
		if (!empty($data["KLTown_id"])) {
			$sFilters .= " and lua.KLTown_id = :KLTown_id ";
			$queryParams["KLTown_id"] = $data["KLTown_id"];
		}
		if (!empty($data["TTGLpu_id"])) {
			$sFilters .= " and msf.Lpu_id = :Lpu_id ";
			$queryParams["Lpu_id"] = $data["TTGLpu_id"];
			$topFilters = " and msf.Lpu_id = :Lpu_id ";
		}

		$query = "
		    --variables
			with ttg (
			    TimetableGraf_id,
			    TimetableGraf_updDT
			) as (
			    select
			           g.TimetableGraf_id,
			           g.TimetableGraf_updDT
			    from
			         v_TimetableGraf_lite g
			         inner join v_MedStaffFact msf on msf.MedStaffFact_id = g.MedStaffFact_id
			    where g.TimetableGraf_begTime >= tzgetdate()
			      and g.pmUser_updID between 1000000 and 5000000
			      and g.Person_id is not null
			      {$topFilters}
			)
			--end variables
			select
			    --select
			       g.TimetableGraf_id as \"TimetableGraf_id\",
			       g.TimetableGraf_Day as \"TimetableGraf_Day\",
			       to_char(g.TimetableGraf_begTime, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') as \"TimetableGraf_begTime\",
			       to_char(g.TimetableGraf_updDT, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') as \"TimetableGraf_updDT\",
			       g.TimetableGraf_IsModerated as \"TimetableGraf_IsModerated\",
			       l.Lpu_Nick as \"Lpu_Nick\",
			       luas.KLStreet_Name||', '||lua.Address_House as \"LpuUnit_Address\",
			       lu.LpuUnit_Name as \"LpuUnit_Name\",
			       msf.MedPersonal_FIO as \"MedPersonFullName\",
			       ls.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
			       substring(lr.LpuRegion_Name , 1, length(lr.LpuRegion_Name) -1) as \"MedLpuRegion_Name\",
			       LPR.LpuRegion_Name as \"LpuRegion_Name\",
			       null as \"LpuRegion_Name_Pr\",
			       lu.LpuUnit_id as \"LpuUnit_id\",
			       msf.MedStaffFact_id as \"MedStaffFact_id\",
			       p.Person_id as \"Person_id\",
			       {$selectPersonData}
			       l.Lpu_id as \"Lpu_id\",
			       case when coalesce(p.Polis_endDate, tzgetdate()) >= tzgetdate() then '' else 'true' end as \"Person_IsBDZ\",
			       p.Lpu_id as \"PrikLpu_id\",
			       p.Lpu_Nick as \"PrikLpu_Nick\",
			       null as \"MedpersonalDay_Descr\",
			       null as \"MedstaffFact_Descr\",
			       p.Server_pid as \"Server_pid\",
			       coalesce(g.pmUser_updID, -1) as \"pmUser_updID\"
			       --end select
			from
			    --from
			    ttg
			    inner join v_TimetableGraf_lite g on g.TimetableGraf_id = ttg.TimetableGraf_id
			    inner join v_Person_ER p on p.Person_id = g.Person_id
			    inner join v_MedStaffFact_ER msf on msf.MedStaffFact_id = g.MedStaffFact_id
			    left join v_LpuSection_ER ls on ls.LpuSection_id = msf.LpuSection_id
			    left join v_LpuUnit_ER lu on lu.LpuUnit_id = ls.LpuUnit_id
			    left join Address lua on lu.Address_id = lua.Address_id
			    left join KLStreet luas on lua.KLStreet_id = luas.KLStreet_id
			    left join v_Lpu l on l.Lpu_id = lu.Lpu_id
			    left join lateral (
			        select LpuRegion_id
			        from v_PersonCard_all
			        where Person_id = p.Person_id
			          and PersonCard_endDate is null
			        order by case when LpuAttachType_id = 4 and Lpu_id = p.Lpu_id then 0 else LpuAttachType_id end, PersonCard_begDate
			        limit 1
			    ) as PersonCard on true
			    left join v_LpuRegion LPR on LPR.LpuRegion_id = PersonCard.LpuRegion_id
			    left join lateral (
			        select (
                             select string_agg(LpuRegion_Name, ', ')
                             from v_MedStaffRegion t2
                                  left join v_LpuRegion lr2 on t2.LpuRegion_id = lr2.LpuRegion_id
                             where t1.MedPersonal_id = t2.MedPersonal_id
                             group by cast (lr2.LpuRegion_Name as int)
                             order by cast (lr2.LpuRegion_Name as int)
                            ) as LpuRegion_Name
                     from v_MedStaffRegion t1
                          left join v_LpuRegion lr1 on t1.LpuRegion_id = lr1.LpuRegion_id
                     where t1.MedPersonal_id = msf.MedPersonal_id
                     group by t1.MedPersonal_id
			    ) lr on true
				left outer join Address a on p.UAddress_id = a.Address_id
				left outer join Address a1 on p.PAddress_id = a1.Address_id
				left outer join KLStreet pas on a.KLStreet_id = pas.KLStreet_id
				left outer join KLStreet pas1 on a1.KLStreet_id = pas1.KLStreet_id
				left join MedpersonalDay mpd on mpd.MedStaffFact_id = g.MedStaffFact_id and mpd.Day_id = g.TimetableGraf_Day
				{$joinPersonEncrypHIV}
				--end from
			where 
			    --where
			    {$sFilters}
			    --end where
			order by 
			    --order by
			    ttg.TimetableGraf_updDT
			    --end order by
		";
		$response = $callObject->getPagingResponse($query, $queryParams, $data["start"], $data["limit"], true);
		if (!is_array($response) || !isset($response["data"])) {
			return false;
		}
		// Получаем данные из другой БД
		$callObject->dbkvrachu = $callObject->load->database("UserPortal", true); // загружаем БД к-врачу.ру
		$pmUserList = [];
		foreach ($response["data"] as $row) {
			if ($row["pmUser_updID"] != -1)
				$pmUserList[] = [
					"Person_id" => $row["Person_id"],
					"pmUser_updID" => $row["pmUser_updID"]
				];
		}
		// Если есть записи...
		if (count($pmUserList) > 0) {
            $selectString = "
                :pmUser_updID as \"pmUser_updID\",
                :Person_id as \"Person_id\",
                u.username as \"Login\",
                u.Email as \"Email\",
                rtrim(replace(coalesce(a2.Address_Address, ''), 'РОССИЯ, ПЕРМСКИЙ КРАЙ, ', ''))||' - <small>Указано человеком</small>' as \"Address_Address\"
            ";
            $fromString = "
                users u
                left join Person p1 on p1.Person_mainId = :Person_id and u.id = p1.pmUser_id and p1.pmUser_id = u.id
                left join Address a2 on a2.Address_id = p1.Address_id
            ";
            $whereString = "
                    u.id = :pmUser_updID
                and u.username is not null
            ";
            $query = "
                select {$selectString}
                from {$fromString}
                where {$whereString}
            ";
			// Для каждой записи будет получать отдельный запрос
			$sql = "";
			foreach ($pmUserList as $row) {
				$sql .= getDebugSql($query, $row);
			}
			/**
			 * @var CI_DB_driver $dbkvrachu
			 * @var CI_DB_result $resultUser
			 */
			$dbkvrachu = $callObject->dbkvrachu;
			$resultUser = $dbkvrachu->query($sql, []);
			if (!is_object($resultUser)) {
				return false;
			}
			$res = $resultUser->result("array");
			foreach ($res as $row) {
				foreach ($response["data"] as $key => $array) {
					if ($row["Person_id"] == $array["Person_id"] && $row["pmUser_updID"] == $array["pmUser_updID"]) {
						$response["data"][$key]["Login"] = $row["Login"];
						$response["data"][$key]["Email"] = $row["Email"];
					}
				}
			}
		}
		return $response;
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getTTGHistory(TimetableGraf_model $callObject, $data)
	{
		$selectPersonData = "
			rtrim(rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||' '||coalesce(rtrim(p.Person_Secname), '')) as \"Person_FIO\",
			to_char(Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\"
		";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data["session"])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh on peh.Person_id = p.Person_id";
			$selectPersonData = "
				case when peh.PersonEncrypHIV_Encryp is null
					then rtrim(rtrim(p.Person_Surname)||' '||rtrim(p.Person_Firname)||' '||coalesce(rtrim(p.Person_Secname), ''))
					else rtrim(peh.PersonEncrypHIV_Encryp)
				end as \"Person_FIO\",
				case when peh.PersonEncrypHIV_Encryp is null
					then to_char(Person_BirthDay, '{$callObject->dateTimeForm104}')
					else null
				end as \"Person_BirthDay\"
			";
		}
		if (!isset($data["ShowFullHistory"])) {
			$sql = "
				WITH hist as (
				    SELECT
				           to_char(ttg.TimeTableGraf_insDT, '{$callObject->dateTimeForm104}')||' '||to_char(ttg.TimeTableGraf_insDT, '{$callObject->dateTimeForm108}') as TimetableHist_insDT,
				           ttg.pmUser_insID AS TimetableGrafHist_userID,
				           1 AS TimetableGrafAction_id,
				           ttg.TimeTableType_id,
				           ttg.RecMethodType_id,
				           ttg.Person_id
				    FROM v_TimetableGraf_lite ttg
				    where TimetableGraf_id = :TimetableGraf_id
				    union all
				    SELECT
				        to_char(TimetableGrafHist_insDT, '{$callObject->dateTimeForm104}')||' '||to_char(TimetableGrafHist_insDT, '{$callObject->dateTimeForm108}') as TimetableHist_insDT,
				        ttgh.TimetableGrafHist_userID,
				        ttgh.TimetableGrafAction_id,
				        coalesce(ttgh.TimetableType_id, 1) AS TimetableType_id,
				        ttgh.RecMethodType_id,
				        ttgh.Person_id
				    FROM TimetableGrafHist ttgh
				    where TimetableGraf_id = :TimetableGraf_id
				)
				SELECT
				    hist.TimetableHist_insDT as \"TimetableHist_insDT\",
				    case
				        when hist.TimetableGrafHist_userID is not null then rtrim(PMUser_Name)
				        else '@@inet'
				    end as \"PMUser_Name\",
				    hist.TimetableGrafHist_userID as \"pmUser_id\",
				    ttga.TimetableActionType_Name as \"TimetableActionType_Name\",
				    ttt.TimetableType_Name as \"TimetableType_Name\",
				    hist.RecMethodType_id as \"RecMethodType_id\",
				    {$selectPersonData}
				FROM hist
				     left join v_pmUser pu on hist.TimetableGrafHist_userID = pu.pmuser_id
				     left join TimetableActionType ttga on ttga.TimetableActionType_id=hist.TimetableGrafAction_id
				     left join v_TimetableType ttt on ttt.TimetableType_id = coalesce(hist.TimetableType_id, 1)
				     left join v_Person_ER p on hist.Person_id = p.Person_id
				{$joinPersonEncrypHIV}
			";
			$sqlParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
		} else {
			$subValue = $callObject->getFirstRowFromQuery("
				select
					MedStaffFact_id as \"MedStaffFact_id\",
					TimetableGraf_begTime as \"TimetableGraf_begTime\"
				from v_TimetableGraf_lite
				where TimetableGraf_id = :TimetableGraf_id
				", ["TimetableGraf_id" => $data["TimetableGraf_id"]]
			);
			$sqlParams = [
				"MedStaffFact_id" => $subValue["MedStaffFact_id"],
				"TimetableGraf_begTime" => $subValue["TimetableGraf_begTime"],
			];
			$sql = "
				WITH hist as (
				    SELECT
				           to_char(ttg.TimeTableGraf_insDT, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') as TimetableHist_insDT,
				           ttg.pmUser_insID AS TimetableGrafHist_userID,
				           1 AS TimetableGrafAction_id,
				           ttg.TimeTableType_id,
				           ttg.RecMethodType_id,
				           ttg.Person_id
				    FROM v_TimetableGraf_lite ttg
				    where MedStaffFact_id = :MedStaffFact_id
				      and TimetableGraf_begTime = :TimetableGraf_begTime
				    union all
				    SELECT
				        to_char(TimetableGrafHist_insDT, '{$callObject->dateTimeForm104} {$callObject->dateTimeForm108}') as TimetableHist_insDT,
				        ttgh.TimetableGrafHist_userID,
				        ttgh.TimetableGrafAction_id,
				        coalesce(ttgh.TimetableType_id, 1) AS TimetableType_id,
				        ttgh.RecMethodType_id,
				        ttgh.Person_id
				    FROM TimetableGrafHist ttgh
				    where MedStaffFact_id = :MedStaffFact_id
				      and TimetableGraf_begTime = :TimetableGraf_begTime
				)
				SELECT
				    hist.TimetableHist_insDT as \"TimetableHist_insDT\",
				    case
				        when hist.TimetableGrafHist_userID is not null then rtrim(PMUser_Name)
				        else '@@inet'
				    end as \"PMUser_Name\",
				    hist.TimetableGrafHist_userID as \"pmUser_id\",
				    ttga.TimetableActionType_Name as \"TimetableActionType_Name\",
				    ttt.TimetableType_Name as \"TimetableType_Name\",
				    hist.RecMethodType_id as \"RecMethodType_id\",
				    {$selectPersonData}
				FROM hist
				     left join v_pmUser pu on hist.TimetableGrafHist_userID = pu.pmuser_id
				     left join TimetableActionType ttga on ttga.TimetableActionType_id=hist.TimetableGrafAction_id
				     left join v_TimetableType ttt on ttt.TimetableType_id = coalesce(hist.TimetableType_id, 1)
				     left join v_Person_ER p on hist.Person_id = p.Person_id
					 {$joinPersonEncrypHIV}
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($sql, $sqlParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param TimetableGraf_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getTTGInfo(TimetableGraf_model $callObject, $data)
	{
		$query = "
			select
				MP.MedPersonal_id as \"MedPersonal_id\",
				PA.Person_Fio as \"Person_Fio\",
				coalesce(TTG.TimetableGraf_begTime, TTG.TimetableGraf_factTime) as \"TimetableGraf_begTime\",
				to_char(TTG.TimetableGraf_begTime, '{$callObject->dateTimeForm120} {$callObject->dateTimeForm108}') as \"TimetableGraf_abegTime\",
				to_char(TTGN.TimetableGraf_begTime, '{$callObject->dateTimeForm120} {$callObject->dateTimeForm108}') as \"TimetableGraf_nextTime\",
				MSF.MedStaffFact_PriemTime as \"MedStaffFact_PriemTime\"
			from
			    v_TimetableGraf_lite TTG
			    left join v_MedStaffFact MSF on MSF.MedStaffFact_id = TTG.MedStaffFact_id
			    left join v_MedPersonal MP on MP.MedPersonal_id = MSF.MedPersonal_id
			    left join lateral (
			        select Person_Fio
			        from v_Person_all
			        where Person_id = TTG.Person_id
			        limit 1
			    ) as PA on true
			    left join lateral (
			        select TimetableGraf_begTime
			        from v_TimetableGraf_lite
			        where MedStaffFact_id = TTG.MedStaffFact_id
			        	and TimeTableGraf_Day = TTG.TimeTableGraf_Day
			        	and TimetableGraf_begTime > TTG.TimetableGraf_begTime
			        order by TimetableGraf_begTime asc
			        limit 1
			    ) as TTGN on true
			where TTG.TimetableGraf_id = :TimetableGraf_id
			limit 1
		";
		$queryParams = ["TimetableGraf_id" => $data["TimetableGraf_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получение типа бирки и id MO
	 * @param $callObject, $data
	 * @return array|bool
	 */
	public static function getTTGType(TimetableGraf_model $callObject, $data)
	{
		$query = "
			select
				TTG.TimeTableType_id as \"TimeTableType_id\",
				MSF.Lpu_id as \"Lpu_id\"
			from
				v_TimetableGraf_lite TTG
			left join v_MedStaffFact MSF on MSF.MedStaffFact_id = TTG.MedStaffFact_id
			where
				TTG.TimetableGraf_id = :TimetableGraf_id
				limit 1
		";
		$queryParams = ['TimetableGraf_id' => $data['TimetableGraf_id']];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query( $query, $queryParams);
		if ( !is_object( $result ) ) {
			return false;
		}
		return $result->result( 'array' );
	}
}
