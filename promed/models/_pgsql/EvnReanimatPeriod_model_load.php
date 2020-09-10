<?php

class EvnReanimatPeriod_model_load
{
	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loadEvnReanimatPeriodViewData(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				ERP.EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
				ERP.EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\",
				ERP.EvnReanimatPeriod_rid as \"EvnReanimatPeriod_rid\",
				to_char(ERP.EvnReanimatPeriod_setDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatPeriod_setDate\",
				to_char(ERP.EvnReanimatPeriod_setTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatPeriod_setTime\",
				to_char(ERP.EvnReanimatPeriod_disDT, '{$callObject->dateTimeForm104}') as \"EvnReanimatPeriod_disDate\",
				to_char(ERP.EvnReanimatPeriod_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatPeriod_disTime\",
				ERP.ReanimReasonType_id as \"ReanimReasonType\",
				Rea.ReanimReasonType_Name as \"ReanimReasonType_Name\",
				ERP.ReanimResultType_id as \"ReanimResultType\",
				Res.ReanimResultType_Name as \"ReanimResultType_Name\",
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				MS.MedService_id as \"MedService_id\",
				MS.MedService_Name as \"MedService_Name\",
				case when exists(
					select * 
					from v_EvnSection ES
					inner join v_DiagFinance DF on DF.Diag_id = ES.Diag_id
					where ES.EvnSection_pid = EPS.EvnPS_id
					and DF.DiagFinance_IsRankin = 2
				) then 2 else 1 end as \"DiagFinance_IsRankin\"
			from
				v_EvnReanimatPeriod ERP
				left join v_LpuSection LS on LS.LpuSection_id = ERP.LpuSection_id
				left join v_MedService MS on MS.MedService_id = ERP.MedService_id
				left join v_EvnPS EPS on EPS.EvnPS_id = ERP.EvnReanimatPeriod_rid
				left join ReanimReasonType Rea on Rea.ReanimReasonType_id =  ERP.ReanimReasonType_id
				left join ReanimResultType Res on Res.ReanimResultType_id =  ERP.ReanimResultType_id
			where  ERP.EvnReanimatPeriod_pid = :EvnReanimatPeriod_pid
			order by ERP.EvnReanimatPeriod_setDT desc
		";
		$queryParams = ["EvnReanimatPeriod_pid" => $data["EvnReanimatPeriod_pid"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$Response = $result->result("array");
		foreach ($Response as &$row) {
			$data["EvnReanimatCondition_pid"] = $row["EvnReanimatPeriod_id"];
			$row["EvnReanimatCondition"] = $callObject->loudEvnReanimatConditionGridEMK($data);
			$data["EvnReanimatAction_pid"] = $row["EvnReanimatPeriod_id"];
			$row["EvnReanimatAction"] = $callObject->loudEvnReanimatActionEMK($data);
			$data["EvnScale_pid"] = $row["EvnReanimatPeriod_id"];
			$row["EvnScale"] = $callObject->loudEvnScaleGrid($data);
			foreach ($row["EvnScale"] as &$OneScale) {
				$data["EvnScale_id"] = $OneScale["EvnScale_id"];
				$OneScale["ScaleParam"] = $callObject->getEvnScaleContentEMK($data);
			}

		}
		return $Response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loudEvnReanimatPeriodGrid_PS(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				ERP.EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
				ERP.EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\",
				to_char(ERP.EvnReanimatPeriod_setDate, '{$callObject->dateTimeForm104}')||' '||to_char(ERP.EvnReanimatPeriod_setTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatPeriod_setDT\",
				to_char(ERP.EvnReanimatPeriod_disDT, '{$callObject->dateTimeForm104}')||' '||to_char(ERP.EvnReanimatPeriod_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatPeriod_disDT\",
				Rea.ReanimReasonType_Name as \"ReanimReasonType_Name\",
				Res.ReanimResultType_Name as \"ReanimResultType_Name\",
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				MS.MedService_id as \"MedService_id\",
				MS.MedService_Name as \"MedService_Name\"
			from
				v_EvnReanimatPeriod ERP
				left join v_LpuSection LS on LS.LpuSection_id = ERP.LpuSection_id
				left join v_MedService MS on MS.MedService_id = ERP.MedService_id
				left join ReanimReasonType Rea on Rea.ReanimReasonType_id =  ERP.ReanimReasonType_id
				left join ReanimResultType Res on Res.ReanimResultType_id =  ERP.ReanimResultType_id
			where  ERP.EvnReanimatPeriod_rid = :EvnReanimatPeriod_rid
			order by ERP.EvnReanimatPeriod_setDT
		";
		$queryParams = ["EvnReanimatPeriod_rid" => $data["EvnPS_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loudEvnScaleGrid(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				ESC.EvnScale_id as \"EvnScale_id\",
				ESC.EvnScale_pid as \"EvnScale_pid\",
				ESC.Person_id as \"Person_id\",
				ESC.PersonEvn_id as \"PersonEvn_id\",
				ESC.Server_id as \"Server_id\",
				to_char(ESC.EvnScale_setDate, '{$callObject->dateTimeForm104}') as \"EvnScale_setDate\",
				to_char(ESC.EvnScale_setTime, '{$callObject->dateTimeForm108}') as \"EvnScale_setTime\",
				ESC.ScaleType_id as \"ScaleType_id\",
				ESC.ScaleType_Name as \"ScaleType_Name\",
				ESC.ScaleType_SysNick as \"ScaleType_SysNick\",
				ESC.EvnScale_Result as \"EvnScale_Result\",
				ESC.EvnScale_ResultTradic as \"EvnScale_ResultTradic\"
			from v_EvnScale ESC
			where ESC.EvnScale_pid = :EvnScale_pid
			order by ESC.EvnScale_setDT desc
		";
		$queryParams = ["EvnScale_pid" => $data["EvnScale_pid"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @return array|bool
	 */
	public static function loadReanimatSyndromeType(EvnReanimatPeriod_model $callObject)
	{
		$query = "
			select
				ReanimatSyndromeType_id as \"ReanimatSyndromeType_id\",
			    ReanimatSyndromeType_Name as \"ReanimatSyndromeType_Name\"
			from ReanimatSyndromeType
			order by ReanimatSyndromeType_id
        ";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loudEvnDirectionGrid(EvnReanimatPeriod_model $callObject, $data)
	{
		$queryParams = [
			"EvnDirection_pid" => $data["EvnSection_id"],
			"EvnReanimatPeriod_id" => $data["EvnReanimatPeriod_id"],
			"Lpu_id" => $data["Lpu_id"]
		];
		$callObject->load->model("EvnDirection_model", "EvnDirection_model");
		$goAll = $callObject->getGlobalOptions();
		$go = $goAll["globals"];
		$query = "
			select
				ED.EvnDirection_id as \"EvnDirection_id\",
				to_char(ED.EvnDirection_setDT, '{$callObject->dateTimeForm104}') as \"EvnDirection_setDate\", 								
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				ED.Server_id as \"Server_id\",
				ED.Lpu_id as \"Lpu_id\",
				ED.Diag_id as \"Diag_id\",
				ED.EvnDirection_IsSigned as \"EvnDirection_IsSigned\",
				ED.EvnDirection_Num::varchar as \"EvnDirection_Num\",
				case
					when ED.Org_oid is not null then OO.Org_Nick
					when TTMS.TimetableMedService_id is not null then coalesce(MS.MedService_Name, '')||' / '||coalesce(Lpu.Lpu_Nick, '')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
								then coalesce(MS.MedService_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
								then coalesce(MS.MedService_Name, '')||' / '||coalesce(LSP.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							else coalesce(LSP.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
						end||' / '||coalesce(Lpu.Lpu_Nick, '')
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null
						then coalesce(LSP.LpuSectionProfile_Name, '')||' / '||coalesce(LS.LpuSection_Name, '')||' / '||coalesce(Lpu.Lpu_Nick, '')
					else null 
				end as \"RecTo\",
				case
					when TTMS.TimetableMedService_id is not null then
						coalesce(to_char(TTMS.TimetableMedService_begTime, '{$callObject->dateTimeForm104}'), '')||' '||coalesce(to_char(TTMS.TimetableMedService_begTime, '{$callObject->dateTimeForm108}'), '')
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then 'В очереди с '||coalesce(to_char(EQ.EvnQueue_setDate, '{$callObject->dateTimeForm104}'), '') else to_char(EUP.EvnUslugaPar_setDT, '{$callObject->dateTimeForm104}')||' '||to_char(EUP.EvnUslugaPar_setDT, '{$callObject->dateTimeForm108}') end
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null
						then 'Направление выписано '||to_char(ED.EvnDirection_setDT, '{$callObject->dateTimeForm104}')
					else null
				end as \"RecDate\",
				case 
					when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 12 
					else ED.EvnStatus_id 
				end as \"EvnStatus_id\",
				case 
					when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' 
					else EvnStatus.EvnStatus_Name 
				end as \"EvnStatus_Name\",
				to_char(coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), '{$callObject->dateTimeForm104}') as \"EvnDirection_statusDate\",
				CASE
					WHEN (ED.EvnStatus_id in (12, 13, 15)) THEN 0
					WHEN TT.recDate > tzgetdate() and ( ED.ARMType_id =24 OR TT.pmUser_updID BETWEEN 1000000 AND 5000000) THEN 0
					WHEN ED.EvnDirection_IsAuto = 2 AND " . ($go['disallow_canceling_el_dir_for_elapsed_time'] ? '1' : '0') . " = 1 AND TT.recDate <= tzgetdate() THEN 0
					WHEN coalesce(ED.EvnDirection_IsAuto, 1) = 1 AND " . ($go['disallow_canceling_el_dir_for_elapsed_time'] ? '1' : '0') . " = 0 and TT.recDate <= tzgetdate() THEN 0
					WHEN DF.DirectionFrom = 'incoming' THEN CASE WHEN {$callObject->EvnDirection_model->getDirectionCancelConditionsForIncoming()} THEN 1 ELSE 0 END
					WHEN DF.DirectionFrom = 'outcoming' THEN CASE WHEN {$callObject->EvnDirection_model->getDirectionCancelConditionsForOutcoming()} THEN 1 ELSE 0 END
					WHEN DF.DirectionFrom = 'both' THEN CASE WHEN {$callObject->EvnDirection_model->getDirectionCancelConditionsForIncoming()} OR {$callObject->EvnDirection_model->getDirectionCancelConditionsForOutcoming()} THEN 1 ELSE 0 END
					ELSE 1 
				END as \"allowCancel\",
				DT.DirType_Code as \"DirType_Code\",
				case
					when TTMS.TimetableMedService_id is not null then 'На исследование: '||coalesce(UC.UslugaComplex_Name, '')
					when EQ.EvnQueue_id is not null then
						case 
							when EUP.EvnUslugaPar_setDT is null then coalesce(DT.DirType_Name, 'Очередь') 
							else 'На исследование: '||coalesce(UC.UslugaComplex_Name, '') 
						end
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null and ED.DirType_id is not null then DT.DirType_Name||':'
					else null
				end as \"RecWhat\",
				case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then 'EvnQueue' else 'EvnUslugaPar' end
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null
						then 'EvnDirection'
					else null
				end as \"timetable\", 
				case
					when TTMS.TimetableMedService_id is not null  then TTMS.TimetableMedService_id
					when EQ.EvnQueue_id is not null then
						case when EUP.EvnUslugaPar_setDT is null then EQ.EvnQueue_id else EQ.EvnUslugaPar_id end
					when coalesce(TTMS.TimetableMedService_id, EQ.EvnQueue_id) is null
						then ED.EvnDirection_id
					else null
				end as \"timetable_id\", 
				coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as \"EvnStatusCause_Name\",
				fLpu.Lpu_Nick as \"StatusFromLpu\",
				fMP.Person_Fio as \"StatusFromMP\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				EvnXmlDir.EvnXml_id as \"EvnXmlDir_id\",
				EvnXmlDir.XmlType_id as \"EvnXmlDirType_id\"
			from
				v_EvnDirection_all ED
				inner join ReanimatPeriodDirectLink RPDL on RPDL.EvnDirection_id  = ED.EvnDirection_id
				left join lateral (
					select
						TimetableMedService_id,
						TimetableMedService_begTime
					from v_TimetableMedService_lite TTMS
					where TTMS.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) as TTMS on true
				left join v_EvnQueue EQ on EQ.EvnDirection_id = ED.EvnDirection_id
				left join v_MedService MS on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				left join v_EvnUslugaPar EUP on EUP.EvnDirection_id = ED.EvnDirection_id
				left join v_UslugaComplex UC on EUP.UslugaComplex_id = UC.UslugaComplex_id
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did, EQ.LpuUnit_did, MS.LpuUnit_id, LS.LpuUnit_id) = LU.LpuUnit_id
				left join v_LpuSectionProfile LSP on coalesce(ED.LpuSectionProfile_id, EQ.LpuSectionProfile_did, LS.LpuSectionProfile_id) = LSP.LpuSectionProfile_id
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join v_Org OO on OO.Org_id = ED.Org_oid
				left join lateral (
					select
						ESH.EvnStatus_id,
						ESH.EvnStatusCause_id,
						ESH.pmUser_insID
					from EvnStatusHistory ESH
					where ESH.Evn_id = ED.EvnDirection_id
					  and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
					limit 1
				) as ESH on true
				left join EvnStatus on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join EvnStatusCause on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				left join v_DirFailType DFT on DFT.DirFailType_id = ED.DirFailType_id
				left join v_QueueFailCause QFC on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				left join v_pmUserCache fUser on fUser.PMUser_id = coalesce(ED.pmUser_failID,EQ.pmUser_failID,ESH.pmUser_insID)
				left join v_Lpu fLpu on fLpu.Lpu_id = fUser.Lpu_id
				left join lateral (
					select MP.MedPersonal_id, MP.Person_Fio
					from v_MedPersonal MP
					where MP.MedPersonal_id = fUser.MedPersonal_id
					  and MP.Lpu_id = fUser.Lpu_id
					  and MP.WorkType_id = 1
					limit 1
				) as fMP on true
				left join lateral (
					select
						EvnXml.EvnXml_id,
						XmlType.XmlType_id
					from
						XmlType
						left join EvnXml on EvnXml.XmlType_id = XmlType.XmlType_id and EvnXml.Evn_id = ED.EvnDirection_id
					where XmlType.XmlType_id = case when 13 = DT.DirType_Code then 20 else null end
					limit 1
				) as EvnXmlDir on true
				left join lateral (
					select * from (
						select
							Timetable.pmUser_updID,
							Timetable.TimetableMedService_begTime as recDate
						from  v_TimetableMedService_lite Timetable
						where ED.DirType_id in (2, 3, 10, 11, 15, 25)
						  and Timetable.EvnDirection_id = ED.EvnDirection_id
						union all
						select
							EQ.pmUser_updID,
							null as recDate
						from v_EvnQueue EQ
						where EQ.EvnDirection_id = ED.EvnDirection_id
						  and (EQ.EvnQueue_recDT is null or EQ.pmUser_recID = 1)
						  and EQ.EvnQueue_failDT is null
						  and EQ.EvnQueue_IsArchived is null
						union all
						select
							ED.pmUser_updID,
							null as recDate
						where coalesce(ED.EvnDirection_IsAuto, 1) = 1
					) tt
					limit 1
				) as TT on true
				left join lateral (
					SELECT
						CASE
							WHEN coalesce(ED.Lpu_did, ED.Lpu_id) = coalesce(ED.Lpu_sid, ED.Lpu_id) THEN 'both'
							WHEN coalesce(ED.Lpu_did, ED.Lpu_id) = :Lpu_id THEN 'incoming'
							ELSE 'outcoming' END
						as DirectionFrom
				) as DF on true
			where ED.EvnDirection_pid = :EvnDirection_pid
			  and DT.DirType_Code = 13
			  and RPDL.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
			  and not exists (
			    select epd.EvnPrescr_id
			    from v_EvnPrescrDirection epd
			    where epd.EvnDirection_id = ED.EvnDirection_id
			  )
			  and coalesce(ED.DirFailType_id, 0) != 14
			  and coalesce(EQ.QueueFailCause_id, 0) != 5
			  and coalesce(ESH.EvnStatusCause_id, 0) != 4 
			order by
				ED.EvnDirection_setDT desc,
				ED.EvnDirection_id desc
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loudEvnPrescrGrid(EvnReanimatPeriod_model $callObject, $data)
	{
		$queryParams = [
			"EvnPrescr_pid" => $data["EvnSection_id"],
			"EvnReanimatPeriod_id" => $data["EvnReanimatPeriod_id"]
		];
		$query = "
			select
				EP.EvnPrescr_id as \"EvnPrescr_id\",								
				EP.EvnPrescr_pid as \"EvnPrescr_pid\",								
				EP.EvnPrescr_rid as \"EvnPrescr_rid\",								
				to_char(EP.EvnPrescr_setDT, '{$callObject->dateTimeForm104}') as \"EvnPrescr_setDate\", 								
				EP.PrescriptionType_id as \"PrescriptionType_id\",
				PT.PrescriptionType_Name as \"PrescriptionType_Name\",
				coalesce(EP.EvnPrescr_IsExec, 1) as \"EvnPrescr_IsExec\",
				coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\",
				case 
					when ED.EvnDirection_id is null then 'Отменено'
					when ED.DirFailType_id > 0 OR EQ.QueueFailCause_id  > 0 then
						case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'Отменено' else EvnStatus.EvnStatus_Name end||
						' - '||
						coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name)
					when TTMS.TimetableMedService_id is null and TTR.TimetableResource_id is null and EQ.EvnQueue_id is null then 'Отменено'
					when coalesce(EP.EvnPrescr_IsExec, 1) = 2 then 'Выполнено' 
					when coalesce(EP.EvnPrescr_IsCito, 1) = 2 then 'Cito!' 
					else null
				end as \"EvnPrescr_StatusTxt\",
				case EP.PrescriptionType_id
				   when 11 then UC11.UslugaComplex_id
				   when 12 then UC12.UslugaComplex_id
				   when 13 then UC13.UslugaComplex_id
				   else null
				end as \"UslugaComplex_id\",
				case EP.PrescriptionType_id
				   when 11 then UC11.UslugaComplex_Code
				   when 12 then UC12.UslugaComplex_Code
				   when 13 then UC13.UslugaComplex_Code
				   else null
				end as \"UslugaComplex_Code\",
				case EP.PrescriptionType_id
				   when 11 then UC11.UslugaComplex_Name
				   when 12 then UC12.UslugaComplex_Name
				   when 13 then UC13.UslugaComplex_Name
				   else null
				end as \"UslugaComplex_Name\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				case when ED.EvnDirection_Num is null then '' else ED.EvnDirection_Num::varchar end as \"EvnDirection_Num\",
				case
					when TTMS.TimetableMedService_id is not null then coalesce(MS.MedService_Name, '')||' / '||coalesce(Lpu.Lpu_Nick, '')
					when TTR.TimetableResource_id is not null then coalesce(MS.MedService_Name, '')||' / '||coalesce(R.Resource_Name, '')||' / '||coalesce(Lpu.Lpu_Nick, '')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then coalesce(MS.MedService_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name, '')||' / '||coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
							else coalesce(LSPD.LpuSectionProfile_Name, '')||' / '||coalesce(LU.LpuUnit_Name, '')
						end||' / '||coalesce(Lpu.Lpu_Nick, '')
					else null
				end as \"RecTo\",
				case
					when TTMS.TimetableMedService_id is not null then coalesce(to_char(TTMS.TimetableMedService_begTime, '{$callObject->dateTimeForm104}'), '')||' '||coalesce(to_char(TTMS.TimetableMedService_begTime, '{$callObject->dateTimeForm108}'), '')
					when TTR.TimetableResource_id is not null then coalesce(to_char(TTR.TimetableResource_begTime, '{$callObject->dateTimeForm104}'), '')||' '||coalesce(to_char(TTR.TimetableResource_begTime, '{$callObject->dateTimeForm108}'), '')
					when EQ.EvnQueue_id is not null then 'В очереди с '||coalesce(to_char(EQ.EvnQueue_setDate, '{$callObject->dateTimeForm104}'), '')
					else null
				end as \"RecDate\",
				case
					when TTMS.TimetableMedService_id is not null then 'TimetableMedService'
					when TTR.TimetableResource_id is not null then 'TimetableResource'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
					else null
				end as \"timetable\",
				case when 2 = EP.EvnPrescr_IsExec
				    then to_char(EP.EvnPrescr_updDT, '{$callObject->dateTimeForm104}')||' '||to_char(EP.EvnPrescr_updDT, '{$callObject->dateTimeForm108full}')
				    else null
				end as \"EvnPrescr_execDT\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				MS.MedService_id as \"MedService_id\",
				LS.LpuSection_id as \"LpuSection_id\",
				LU.LpuUnit_id as \"LpuUnit_id\",
				Lpu.Lpu_id as \"Lpu_id\",
				case EP.PrescriptionType_id
					when 12 then (
					    select EvnPrescrFuncDiagUsluga_id
					    from EvnPrescrFuncDiagUsluga
					    where EvnPrescrFuncDiag_id = EP.EvnPrescr_id
					    limit 1
					)
					else 0
				end as \"TableUsluga_id\"
			from
				v_EvnPrescr EP
				inner join PrescriptionType PT on EP.PrescriptionType_id = PT.PrescriptionType_id
				inner join ReanimatPeriodPrescrLink RPPL on EP.EvnPrescr_id = RPPL.EvnPrescr_id
				left join EvnPrescrLabDiag EPLD on EPLD.evn_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC11 on UC11.UslugaComplex_id = EPLD.UslugaComplex_id
				left join EvnPrescrFuncDiagUsluga EPFDU on EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC12 on UC12.UslugaComplex_id = EPFDU.UslugaComplex_id
				left join EvnPrescrConsUsluga EPCU on EPCU.evn_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC13 on UC13.UslugaComplex_id = EPCU.UslugaComplex_id
				left join lateral (
					select
				    	ED.EvnDirection_id,
						coalesce(ED.Lpu_sid, ED.Lpu_id) as Lpu_id,
						ED.EvnQueue_id,
						ED.EvnDirection_Num,
				    	ED.EvnDirection_IsAuto,
						ED.LpuSection_did,
				    	ED.LpuUnit_did,
						ED.Lpu_did,
				    	ED.MedService_id,
				    	ED.Resource_id,
						ED.LpuSectionProfile_id,
				    	ED.DirType_id,
				    	ED.EvnStatus_id,
				    	ED.EvnDirection_statusDate,
				    	ED.DirFailType_id,
				    	ED.EvnDirection_failDT,
				    	ED.MedPersonal_id
					from
				    	v_EvnPrescrDirection epd
						inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by
						case when coalesce(ED.EvnStatus_id, 16) in (12, 13) then 2 else 1 end,
				    	epd.EvnPrescrDirection_insDT desc
				    limit 1
				) as ED on true
				left join lateral (
					select
				    	TimetableMedService_id,
				    	TimetableMedService_begTime
				    from v_TimetableMedService_lite TTMS
				    where TTMS.EvnDirection_id = ED.EvnDirection_id
				    limit 1
				) as TTMS on true
				left join lateral (
					select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
					  and EQ.EvnQueue_recDT is null
					union
					select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					  and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					  and EQ.EvnQueue_failDT is null
				    limit 1
				) as EQ on true
				left join lateral (
					select ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH
					where ESH.Evn_id = ED.EvnDirection_id
					  and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
				    limit 1
				) as ESH on true
				left join EvnStatus on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join EvnStatusCause on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				left join v_DirFailType DFT on DFT.DirFailType_id = ED.DirFailType_id
				left join v_QueueFailCause QFC on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				left join lateral (
					select
				    	TimetableResource_id,
				    	TimetableResource_begTime
					from v_TimetableResource_lite TTR
				    where TTR.EvnDirection_id = ED.EvnDirection_id
				    limit 1
				) as TTR on true
				left join v_MedService MS on MS.MedService_id = ED.MedService_id
				left join v_Resource R on R.Resource_id = ED.Resource_id
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
			where EP.EvnPrescr_pid = :EvnPrescr_pid
			  and RPPL.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
			  and EP.PrescriptionType_id in (11, 12, 13)	
			order by
				EP.EvnPrescr_setDT desc,
				EP.EvnPrescr_id desc
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loudEvnReanimatActionEMK(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				ERA.EvnReanimatAction_id as \"EvnReanimatAction_id\",
				ERA.EvnReanimatAction_pid as \"EvnReanimatAction_pid\",
				to_char(ERA.EvnReanimatAction_setDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatAction_setDate\",
				to_char(ERA.EvnReanimatAction_setTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatAction_setTime\",
				to_char(ERA.EvnReanimatAction_disDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatAction_disDate\",
				to_char(ERA.EvnReanimatAction_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatAction_disTime\",
				ERA.ReanimatActionType_Name as \"ReanimatActionType_Name\",
				case
					when ERA.ReanimatActionType_SysNick = 'observation_saturation' then
						(
						    select RateSPO2_Value::int::varchar||'&nbsp%&nbsp'
						    from v_RateSPO2
							where EvnReanimatAction_id = ERA.EvnReanimatAction_id
							order by RateSPO2_setDT desc, RateSPO2_id desc
							limit 1
						)
					when ERA.ReanimatActionType_SysNick = 'invasive_hemodynamics' then
						(
						    select RateHemodynam_Value::int::varchar
							from v_RateHemodynam
							where EvnReanimatAction_id = ERA.EvnReanimatAction_id
							order by RateHemodynam_setDT desc, RateHemodynam_id desc
							limit 1
						)
					when ERA.ReanimatActionType_SysNick = 'endocranial_sensor' then
						(
						    select RateVCHD_Value::int::varchar
							from v_RateVCHD
							where EvnReanimatAction_id = ERA.EvnReanimatAction_id
							order by RateVCHD_setDT desc, RateVCHD_id desc
							limit 1
						)
				end as \"EvnReanimatAction_ObservValue\",
				case  
					when ERA.ReanimatActionType_SysNick = 'nutrition' then
				        (select  NutritiousType_Name  from dbo.NutritiousType RNT   where RNT.NutritiousType_id = ERA.NutritiousType_id) || 
							coalesce(': ' || ERA.EvnReanimatAction_MethodTxt, '') || ' ' ||  
							coalesce(' объём - ' || ERA.EvnReanimatAction_NutritVol, '') || 
							coalesce(' энергетическая ценность - ' || ERA.EvnReanimatAction_NutritEnerg , '') || '<br>'
					when ERA.ReanimatActionType_SysNick in ('lung_ventilation','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins') then
						(select UslugaComplex_Name from UslugaComplex where UslugaComplex_id = ERA.UslugaComplex_id)||'<br>'
					else null
				end as \"EvnReanimatAction_MethodName\",
				case
					when ERA.ReanimatActionType_SysNick in ('nutrition','lung_ventilation','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins')
				    then 'Метод &nbsp'
					else null
				end as \"EvnReanimatActionMethod_Field\",
				case
					when ERA.ReanimatActionType_SysNick in ('nutrition', 'lung_ventilation','hemodialysis','endocranial_sensor') then null
					when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics') then RD.ReanimDrugType_Name
					else null
				end as \"EvnReanimatAction_Medicoment\",
				case
					when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics')
				    then 'Медикамент &nbsp'
					else null
				end as \"EvnReanimatAction_MedicomentField\",
				case
					when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics')
				    then '&nbsp Дозировка &nbsp'
					else null
				end as \"EvnReanimatAction_DrugDoseField\",
				case
					when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics')
				    then ERA.EvnReanimatAction_DrugDose::int::varchar||' '||ERA.EvnReanimatAction_DrugUnit||'<br>'
					else null
				end as \"EvnReanimatAction_DrugDose\",
				case
					when ERA.ReanimatActionType_SysNick in ('catheterization_veins','epidural_analgesia','endocranial_sensor','hemodialysis','lung_ventilation')
				    then (select PT.PayType_Name from dbo.PayType PT where PT.Region_id is null and PT.PayType_id = EU.PayType_id limit 1)||'<br>'
					else  null
				end as \"PayType_name\",
				case
					when ERA.ReanimatActionType_SysNick in ('catheterization_veins','epidural_analgesia','endocranial_sensor','hemodialysis','lung_ventilation')
				    then 'Тип оплаты &nbsp'
					else null
				end as \"PayType_Field\",
				case
					when ERA.ReanimatActionType_SysNick = 'catheterization_veins'
				    then
				    	'<strong>Вена &nbsp</strong> '||
				    	(select ReanimatCathetVeins_NameI from ReanimatCathetVeins RCV  where ReanimatCathetVeins_id = ERA.ReanimatCathetVeins_id)||
						'<strong>&nbsp фиксация &nbsp</strong> '||
				    	(select CathetFixType_Name from CathetFixType where CathetFixType_id = ERA.CathetFixType_id)||
						'<strong>&nbsp набор &nbsp</strong> '||ERA.EvnReanimatAction_CathetNaborName||'<br>'
					else null
				end as \"ReanimatCathetVeins\",
				case
					when ERA.ReanimatActionType_SysNick in ('invasive_hemodynamics','observation_saturation','card_pulm')
				    then null
					else '<span class=\"link\" id=\"EvnReanimatAction_'||EvnReanimatAction_id::varchar||'_toggleDisplay\">Показать</span> '
				end as \"Pokazat\"
			from
				v_EvnReanimatAction ERA
				left join  dbo.EvnUsluga EU on EU.evn_id = ERA.EvnUsluga_id
				left join dbo.ReanimDrugType RD on RD.ReanimDrugType_id = ERA.ReanimDrugType_id
			where ERA.EvnReanimatAction_pid = :EvnReanimatAction_pid
			order by ERA.EvnReanimatAction_setDT desc			
		";
		$queryParams = ["EvnReanimatAction_pid" => $data["EvnReanimatAction_pid"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loudEvnReanimatActionGrid(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				ERA.EvnReanimatAction_id as \"EvnReanimatAction_id\", 
				ERA.EvnReanimatAction_pid as \"EvnReanimatAction_pid\", 
				ERA.Person_id as \"Person_id\", 
				ERA.PersonEvn_id as \"PersonEvn_id\", 
				ERA.Server_id as \"Server_id\",    
				to_char(ERA.EvnReanimatAction_setDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatAction_setDate\",
				to_char(ERA.EvnReanimatAction_setTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatAction_setTime\",
				to_char(ERA.EvnReanimatAction_disDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatAction_disDate\",
				to_char(ERA.EvnReanimatAction_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatAction_disTime\",
				ERA.ReanimatActionType_id as \"ReanimatActionType_id\",  
				ERA.ReanimatActionType_SysNick as \"ReanimatActionType_SysNick\",
				ERA.ReanimatActionType_Name as \"ReanimatActionType_Name\", 
				case when ERA.ReanimatActionType_id = 3
					then ERA.NutritiousType_id
					else ERA.UslugaComplex_id
				end as \"UslugaComplex_id\",
				ERA.EvnUsluga_id as \"EvnUsluga_id\",
				ERA.ReanimDrugType_id as \"ReanimDrugType_id\", 
				ERA.EvnReanimatAction_DrugDose as \"EvnReanimatAction_DrugDose\",
				ERA.EvnReanimatAction_DrugUnit as \"EvnReanimatAction_DrugUnit\",
				ERA.EvnDrug_id as \"EvnDrug_id\",
				ERA.EvnReanimatAction_MethodCode as \"EvnReanimatAction_MethodCode\",
				case when ERA.ReanimatActionType_SysNick = 'observation_saturation'
					then ERA.EvnReanimatAction_ObservValue::int
					else null
				end  as \"EvnReanimatAction_ObservValue\",
				ERA.ReanimatCathetVeins_id as \"ReanimatCathetVeins_id\", 
				ERA.CathetFixType_id as \"CathetFixType_id\", 
				ERA.EvnReanimatAction_CathetNaborName as \"EvnReanimatAction_CathetNaborName\",
				case  
					when ERA.ReanimatActionType_SysNick = 'nutrition'
					    then (select NutritiousType_Name from NutritiousType RNT where RNT.NutritiousType_id = ERA.NutritiousType_id)
					when ERA.ReanimatActionType_SysNick in ('lung_ventilation','hemodialysis','endocranial_sensor','epidural_analgesia','catheterization_veins')
					    then (select UslugaComplex_Name from UslugaComplex where UslugaComplex_id = ERA.UslugaComplex_id)
					else null
				end  as \"EvnReanimatAction_MethodName\",
				case
					when ERA.ReanimatActionType_SysNick in ('nutrition', 'lung_ventilation','hemodialysis','endocranial_sensor')
					    then null
					when ERA.ReanimatActionType_SysNick in ('vazopressors','epidural_analgesia','antifungal_therapy','catheterization_veins','invasive_hemodynamics')
					    then RD.ReanimDrugType_Name
					else null
				end as \"EvnReanimatAction_Medicoment\",
				EU.PayType_id as \"PayType_id\",
				ERA.EvnReanimatAction_MethodTxt as \"EvnReanimatAction_MethodTxt\",
				ERA.EvnReanimatAction_NutritVol as \"EvnReanimatAction_NutritVol\",
				ERA.EvnReanimatAction_NutritEnerg as \"EvnReanimatAction_NutritEnerg\"
			from
				v_EvnReanimatAction ERA
				left join EvnUsluga EU on EU.evn_id = ERA.EvnUsluga_id
				left join ReanimDrugType RD on RD.ReanimDrugType_id = ERA.ReanimDrugType_id
			where ERA.EvnReanimatAction_pid = :EvnReanimatAction_pid
			order by ERA.EvnReanimatAction_id desc
		";
		$queryParams = ["EvnReanimatAction_pid" => $data["EvnReanimatAction_pid"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loudEvnReanimatConditionGrid(EvnReanimatPeriod_model $callObject, $data)
	{
		$queryParams = ["EvnReanimatCondition_pid" => $data["EvnReanimatCondition_pid"]];

		$ReanimatAgeGroup_id = $callObject->getFirstResultFromQuery("select ReanimatAgeGroup_id from v_EvnReanimatPeriod where EvnReanimatPeriod_id = :EvnReanimatCondition_pid", $queryParams);
		
		if ($ReanimatAgeGroup_id == 1 || $ReanimatAgeGroup_id == 2)
		{
			$query = "
				select	
					ENS.EvnNeonatalSurvey_id as \"EvnReanimatCondition_id\", 
					ENS.EvnNeonatalSurvey_pid as \"EvnReanimatCondition_pid\", 
					ENS.Person_id as \"Person_id\", 
					ENS.PersonEvn_id as \"PersonEvn_id\", 
					ENS.Server_id as \"Server_id\",    
					to_char(ENS.EvnNeonatalSurvey_setDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatCondition_setDate\",
					to_char(ENS.EvnNeonatalSurvey_setTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatCondition_setTime\",
					to_char(ENS.EvnNeonatalSurvey_disDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatCondition_disDate\",
					to_char(ENS.EvnNeonatalSurvey_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatCondition_disTime\",
					ENS.ReanimStageType_id, 
					case 
						when RCP1.ReanimStageType_id = 1 then 'Первичный осмотр' 
						else RCP1.ReanimStageType_Name
					end as \"Stage_Name\", 
					ENS.ReanimConditionType_id as \"ReanimConditionType_id\",
					RCP2.ReanimConditionType_Name as \"Condition_Name\",
					ENS.EvnNeonatalSurvey_Doctor as \"EvnReanimatCondition_Doctor\"
				from
					v_EvnNeonatalSurvey ENS
					left join ReanimStageType RCP1 on ENS.ReanimStageType_id = RCP1.ReanimStageType_id
					left join ReanimConditionType RCP2 on ENS.ReanimConditionType_id = RCP2.ReanimConditionType_id
				where ENS.EvnNeonatalSurvey_pid = :EvnReanimatCondition_pid
				order by ENS.EvnNeonatalSurvey_setDT desc";
		}
		else
		{
			$query = "
				select
					ERC.EvnReanimatCondition_id as \"EvnReanimatCondition_id\",
					ERC.EvnReanimatCondition_pid as \"EvnReanimatCondition_pid\",
					ERC.Person_id as \"Person_id\",
					ERC.PersonEvn_id as \"PersonEvn_id\",
					ERC.Server_id as \"Server_id\",
					to_char(ERC.EvnReanimatCondition_setDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatCondition_setDate\",
					to_char(ERC.EvnReanimatCondition_setTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatCondition_setTime\",
					to_char(ERC.EvnReanimatCondition_disDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatCondition_disDate\",
					to_char(ERC.EvnReanimatCondition_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatCondition_disTime\",
					ERC.ReanimStageType_id as \"ReanimStageType_id\",
					RCP1.ReanimStageType_Name as \"Stage_Name\",
					ERC.ReanimConditionType_id as \"ReanimConditionType_id\",
					RCP2.ReanimConditionType_Name as \"Condition_Name\",
					ERC.EvnReanimatCondition_Complaint as \"EvnReanimatCondition_Complaint\",
					ERC.SkinType_id as \"SkinType_id\",
					ERC.EvnReanimatCondition_SkinTxt as \"EvnReanimatCondition_SkinTxt\",
					ERC.ConsciousType_id as \"ConsciousType_id\",
					ERC.BreathingType_id as \"BreathingType_id\",
					ERC.EvnReanimatCondition_IVLapparatus as \"EvnReanimatCondition_IVLapparatus\",
					ERC.EvnReanimatCondition_IVLparameter as \"EvnReanimatCondition_IVLparameter\",
					ERC.EvnReanimatCondition_Auscultatory as \"EvnReanimatCondition_Auscultatory\",
					ERC.HeartTonesType_id as \"HeartTonesType_id\",
					ERC.HemodynamicsType_id as \"HemodynamicsType_id\",
					ERC.EvnReanimatCondition_Pressure as \"EvnReanimatCondition_Pressure\",
					ERC.EvnReanimatCondition_HeartFrequency as \"EvnReanimatCondition_HeartFrequency\",
					ERC.EvnReanimatCondition_StatusLocalis as \"EvnReanimatCondition_StatusLocalis\",
					ERC.AnalgesiaType_id as \"AnalgesiaType_id\",
					ERC.EvnReanimatCondition_Diuresis as \"EvnReanimatCondition_Diuresis\",
					ERC.UrineType_id as \"UrineType_id\",
					ERC.EvnReanimatCondition_UrineTxt as \"EvnReanimatCondition_UrineTxt\",
					ERC.EvnReanimatCondition_Conclusion as \"EvnReanimatCondition_Conclusion\",
					ERC.EvnReanimatCondition_AnalgesiaTxt as \"EvnReanimatCondition_AnalgesiaTxt\",
					ERC.ReanimArriveFromType_id as \"ReanimArriveFromType_id\",
					ERC.EvnReanimatCondition_HemodynamicsTxt as \"EvnReanimatCondition_HemodynamicsTxt\",
					ERC.EvnReanimatCondition_NeurologicStatus as \"EvnReanimatCondition_NeurologicStatus\",
					ERC.EvnReanimatCondition_sofa as \"EvnReanimatCondition_sofa\",
					ERC.EvnReanimatCondition_apache as \"EvnReanimatCondition_apache\",
					ERC.EvnReanimatCondition_Saturation as \"EvnReanimatCondition_Saturation\",
					ERC.EvnReanimatCondition_OxygenFraction as \"EvnReanimatCondition_OxygenFraction\",
					ERC.EvnReanimatCondition_OxygenPressure as \"EvnReanimatCondition_OxygenPressure\",
					ERC.EvnReanimatCondition_PaOFiO as \"EvnReanimatCondition_PaOFiO\",
					--ERC.NutritiousType_id as \"NutritiousType_id\",
					--ERC.EvnReanimatCondition_NutritiousTxt as \"EvnReanimatCondition_NutritiousTxt\",
					ERC.EvnReanimatCondition_Temperature as \"EvnReanimatCondition_Temperature\",
					ERC.EvnReanimatCondition_InfusionVolume as \"EvnReanimatCondition_InfusionVolume\",
					ERC.EvnReanimatCondition_DiuresisVolume as \"EvnReanimatCondition_DiuresisVolume\",
					ERC.EvnReanimatCondition_CollectiveSurvey as \"EvnReanimatCondition_CollectiveSurvey\",
					ERC.EvnReanimatCondition_SyndromeType as \"EvnReanimatCondition_SyndromeType\",
					ERC.EvnReanimatCondition_ConsTxt as \"EvnReanimatCondition_ConsTxt\",
					ERC.SpeechDisorderType_id as \"SpeechDisorderType_id\",
					ERC.EvnReanimatCondition_rass as \"EvnReanimatCondition_rass\",
					ERC.EvnReanimatCondition_Eyes as \"EvnReanimatCondition_Eyes\",
					ERC.EvnReanimatCondition_WetTurgor as \"EvnReanimatCondition_WetTurgor\",
					ERC.EvnReanimatCondition_waterlow as \"EvnReanimatCondition_waterlow\",
					ERC.SkinType_mid as \"SkinType_mid\",
					ERC.EvnReanimatCondition_MucusTxt as \"EvnReanimatCondition_MucusTxt\",
					ERC.EvnReanimatCondition_IsMicrocDist as \"EvnReanimatCondition_IsMicrocDist\",
					ERC.EvnReanimatCondition_IsPeriphEdem as \"EvnReanimatCondition_IsPeriphEdem\",
					ERC.EvnReanimatCondition_Reflexes as \"EvnReanimatCondition_Reflexes\",
					ERC.EvnReanimatCondition_BreathFrequency as \"EvnReanimatCondition_BreathFrequency\",
					ERC.EvnReanimatCondition_HeartTones as \"EvnReanimatCondition_HeartTones\",
					ERC.EvnReanimatCondition_IsHemodStab as \"EvnReanimatCondition_IsHemodStab\",
					ERC.EvnReanimatCondition_Tongue as \"EvnReanimatCondition_Tongue\",
					ERC.EvnReanimatCondition_Paunch as \"EvnReanimatCondition_Paunch\",
					ERC.EvnReanimatCondition_PaunchTxt as \"EvnReanimatCondition_PaunchTxt\",
					ERC.PeristalsisType_id as \"PeristalsisType_id\",
					ERC.EvnReanimatCondition_VBD as \"EvnReanimatCondition_VBD\",
					ERC.EvnReanimatCondition_Defecation as \"EvnReanimatCondition_Defecation\",
					ERC.EvnReanimatCondition_DefecationTxt as \"EvnReanimatCondition_DefecationTxt\",
					ERC.LimbImmobilityType_id as \"LimbImmobilityType_id\",
					ERC.EvnReanimatCondition_MonopLoc as \"EvnReanimatCondition_MonopLoc\",
					ERC.EvnReanimatCondition_mrc as \"EvnReanimatCondition_mrc\",
					ERC.EvnReanimatCondition_MeningSign as \"EvnReanimatCondition_MeningSign\",
					ERC.EvnReanimatCondition_MeningSignTxt as \"EvnReanimatCondition_MeningSignTxt\",
					ERC.EvnReanimatCondition_glasgow as \"EvnReanimatCondition_glasgow\",
					ERC.EvnReanimatCondition_four as \"EvnReanimatCondition_four\",
					ERC.EvnReanimatCondition_SyndromeTxt as \"EvnReanimatCondition_SyndromeTxt\",
					ERC.EvnReanimatCondition_Doctor as \"EvnReanimatCondition_Doctor\"                
				from
					v_EvnReanimatCondition ERC
					left join dbo.ReanimStageType RCP1 on ERC.ReanimStageType_id = RCP1.ReanimStageType_id
					left join dbo.ReanimConditionType RCP2 on ERC.ReanimConditionType_id = RCP2.ReanimConditionType_id
				where ERC.EvnReanimatCondition_pid = :EvnReanimatCondition_pid
				order by ERC.EvnReanimatCondition_setDT desc
			";
		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function loudEvnReanimatConditionGridEMK(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				ERC.EvnReanimatCondition_id as \"EvnReanimatCondition_id\", 
				ERC.EvnReanimatCondition_pid as \"EvnReanimatCondition_pid\", 
				ERC.Person_id as \"Person_id\", 
				ERC.PersonEvn_id as \"PersonEvn_id\", 
				ERC.Server_id as \"Server_id\",    
				to_char(ERC.EvnReanimatCondition_setDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatCondition_setDate\",
				to_char(ERC.EvnReanimatCondition_setTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatCondition_setTime\",
				to_char(ERC.EvnReanimatCondition_disDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatCondition_disDate\",
				to_char(ERC.EvnReanimatCondition_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatCondition_disTime\",
				ERC.ReanimStageType_id as \"ReanimStageType_id\", 
				(select ReanimStageType_Name from ReanimStageType where ReanimStageType_id = ERC.ReanimStageType_id limit 1) as \"Stage_Name\", 
				ERC.ReanimConditionType_id as \"ReanimConditionType_id\",
				(select ReanimConditionType_Name from ReanimConditionType where ReanimConditionType_id = ERC.ReanimConditionType_id limit 1) as \"Condition_Name\",
				ERC.EvnReanimatCondition_Complaint as \"Complaint\",
				case
					when ERC.SkinType_id = 5 then ERC.EvnReanimatCondition_SkinTxt
					when ERC.SkinType_id in (1, 2, 3, 4)
					    then (select SkinType_Name from SkinType ST where ST.SkinType_id = ERC.SkinType_id)||
					         case when ERC.EvnReanimatCondition_SkinTxt is not null then ', '||ERC.EvnReanimatCondition_SkinTxt else null end
					else
					    case when ERC.EvnReanimatCondition_SkinTxt is not null then ERC.EvnReanimatCondition_SkinTxt else null end
					end
			 	as \"SkinTxt\",
				(select ConsciousType_Name from ConsciousType where ConsciousType_id = ERC.ConsciousType_id limit 1) as \"Conscious\",
				(select BreathingType_Name from BreathingType where BreathingType_id = ERC.BreathingType_id limit 1) as \"Breathing\",
				ERC.EvnReanimatCondition_IVLapparatus as \"IVLapparatus\",
				ERC.EvnReanimatCondition_IVLparameter as \"IVLparameter\",
				getReanimatBreathAuscultative(EvnReanimatCondition_id) as \"Auscultatory\",
				(
				    case substring(ERC.EvnReanimatCondition_HeartTones, 1, 1) when '1' then 'ритмичные' when '2' then 'аритмичные' else null end||' '||
				    case substring(ERC.EvnReanimatCondition_HeartTones, 2, 1) when '1' then 'ясные' when '2' then 'приглушенные' when '3' then 'глухие'  else null end
				) as \"Heart_tones\",
				(select HemodynamicsType_Name from HemodynamicsType where HemodynamicsType_id = ERC.HemodynamicsType_id limit 1)||
				case when ERC.EvnReanimatCondition_HemodynamicsTxt is null or ERC.EvnReanimatCondition_HemodynamicsTxt = '' then '' else ', параметры: ' end||coalesce(ERC.EvnReanimatCondition_HemodynamicsTxt, '')
			 	as \"Hemodynamics\",
				ERC.EvnReanimatCondition_Pressure as \"Pressure\",
				case when ERC.EvnReanimatCondition_HeartFrequency > 0 then ERC.EvnReanimatCondition_HeartFrequency::varchar||' / мин' else null end as \"Heart_frequency\",
				ERC.EvnReanimatCondition_StatusLocalis as \"Status_localis\",
				case when ERC.AnalgesiaType_id = 3
					then ERC.EvnReanimatCondition_AnalgesiaTxt
					else (select AnalgesiaType_Name from AnalgesiaType where AnalgesiaType_id = ERC.AnalgesiaType_id limit 1)
				end as \"Analgesia\",
				case substring(EvnReanimatCondition_Diuresis, 1, 1) when '1' then 'адекватный' when '2' then 'снижен' when '3' then 'олигурия'  when '4' then 'анурия' when '5' then 'полиурия'  else null end||' '||
				case substring(EvnReanimatCondition_Diuresis, 2, 1) when '1' then 'самостоятельно' when '2' then 'по уретральному катетеру'  else null end||' '||
				case substring(EvnReanimatCondition_Diuresis, 3, 1) when '1' then 'на фоне стимуляции' when '2' then 'без стимуляции' else null end||' '||
				case when EvnReanimatCondition_DiuresisVolume is not null then 'объём - '||EvnReanimatCondition_DiuresisVolume::int::varchar||' мл' else null end
			 	as \"Diuresis\",
				case when ERC.UrineType_id = 4
				    then ERC.EvnReanimatCondition_UrineTxt
					else (select UrineType_Name from UrineType where UrineType_id = ERC.UrineType_id limit 1)
				end as \"Urine\",
				ERC.EvnReanimatCondition_Conclusion as \"Conclusion\",
				ERC.EvnReanimatCondition_NeurologicStatus as \"Neurologic_Status\",
				case 
					when ERC.ReanimStageType_id = 1
					    then ' из '||(select ReanimArriveFromType_Name from ReanimArriveFromType where ReanimArriveFromType_id = ERC.ReanimArriveFromType_id limit 1)
						else null
				end  as \"ArriveFromTxt\",
				case ERC.ReanimStageType_id when 3 then 'Дополнительная информация' else 'Неврологический статус' end as \"NevroField\",
				case ERC.ReanimStageType_id when 3 then 'Проведено' else 'Заключение' end as \"ConclusionField\",
				ERC.EvnReanimatCondition_sofa as \"sofa\",
				ERC.EvnReanimatCondition_apache as \"apache\",
				case when ERC.EvnReanimatCondition_Saturation is not null then ERC.EvnReanimatCondition_Saturation::varchar||' %' else null end as \"SpO2\",
				--case when ERC.NutritiousType_id = 4
				--	then ERC.EvnReanimatCondition_NutritiousTxt
				--	else replace((select RNT.NutritiousType_Name from NutritiousType RNT where RNT.NutritiousType_id = ERC.NutritiousType_id limit 1), 'ое', 'ая') 
				--end as \"Nutritious\",
				case when ERC.EvnReanimatCondition_Temperature is not null then ERC.EvnReanimatCondition_Temperature::numeric::varchar||' °C' else null end  as \"Temperature\",
				case when ERC.EvnReanimatCondition_InfusionVolume is not null then ERC.EvnReanimatCondition_InfusionVolume::varchar||' мл' else null end  as \"InfusionVolume\",
				case when ERC.EvnReanimatCondition_DiuresisVolume is not null then ERC.EvnReanimatCondition_DiuresisVolume::varchar||' мл' else null end  as \"DiuresisVolume\",
				ERC.EvnReanimatCondition_CollectiveSurvey as \"CollectiveSurvey\"
			from v_EvnReanimatCondition ERC
			where ERC.EvnReanimatCondition_pid = :EvnReanimatCondition_pid
			order by ERC.EvnReanimatCondition_setDT desc
		";
		$queryParams = ["EvnReanimatCondition_pid" => $data["EvnReanimatCondition_pid"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

    /**
     * @param EvnReanimatPeriod_model $callObject
     * @param $data
     * @return array|bool
     */
	public static function loudEvnDrugCourseGrid(EvnReanimatPeriod_model $callObject, $data) {
        $queryParams = array(
            'EvnCourseTreat_pid' => $data['EvnSection_id'],
            'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
            'Lpu_id' => $data['Lpu_id']
        );

        // $goAll = $this->getGlobalOptions();
        // $go = $goAll['globals'];

        $query = "
			select 
				ECT.EvnCourseTreat_id as \"EvnCourseTreat_id\"
				,ECT.EvnCourseTreat_pid as \"EvnCourseTreat_pid\"
				,ECT.EvnCourseTreat_rid as \"EvnCourseTreat_rid\"
				,to_char(ECT.EvnCourseTreat_setDate, 'dd.mm.yyyy') as \"EvnCourseTreat_setDate\"			-- дата установки курса
				,EPT.EvnPrescrTreat_id as \"EvnPrescrTreat_id\"
				,to_char(EPT.EvnPrescrTreat_setDate, 'dd.mm.yyyy') as \"EvnPrescrTreat_setDate\"			-- дата начала курса
				,EPT.EvnPrescrTreat_Descr    as \"EvnPrescrTreat_Descr\"		--комментарий
				,case when EPT.EvnPrescrTreat_IsCito = 2 then 'Cito!' else '' end \"EvnPrescrTreat_IsCito\"			    -- Признак срочности (Да/Нет)
				,Drug.Drug_id as \"Drug_id\"						-- id медикамента
				,dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\"				-- id Справочника комплексных МНН
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as \"Drug_Name\"            -- наименование медикамента: вместо CourseDrug_Name -- наименование медикамента на курс - DrugForm_Nick
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as \"DrugTorg_Name\"		 -- торговое наименование медикамента
				,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as \"DrugForm_Name\" -- наименование формы медикамента: вместо CourseDrugForm_Name -- наименование формы медикамента на курс - DrugForm_Nick
				,EPTD.EvnPrescrTreatDrug_DoseDay as \"EvnPrescrTreatDrug_DoseDay\"													-- дневная доза - DoseDay, PrescrDoseDay
				,ec_drug.EvnCourseTreatDrug_id as \"EvnCourseTreatDrug_id\"														-- id Медикаментов курса лекарственных средств
				,ec_drug.EvnCourseTreatDrug_MaxDoseDay as \"EvnCourseTreatDrug_MaxDoseDay\"												--максимальная дневная доза - MaxDoseDay
				,ec_drug.EvnCourseTreatDrug_MinDoseDay as \"EvnCourseTreatDrug_MinDoseDay\"												--минимальная дневная доза - MinDoseDay
				,ec_drug.EvnCourseTreatDrug_PrescrDose as \"EvnCourseTreatDrug_PrescrDose\"												--назначенная курсовая доза - PrescrDose
				,ec_drug.EvnCourseTreatDrug_KolvoEd as \"EvnCourseTreatDrug_KolvoEd\" --количество на один прием в единицах дозировки - KolvoEd
				,ec_drug.EvnCourseTreatDrug_Kolvo as \"EvnCourseTreatDrug_Kolvo\"	--количество на один прием в единицах измерения	- Kolvo
				,coalesce(ECT.EvnCourseTreat_MaxCountDay, 0) as \"MaxCountInDay\"						--максимальное количество раз в сутки - DrugMaxCountInDay
				,coalesce(ECT.EvnCourseTreat_Duration, 0) as \"Duration\"								-- продолжительность курса
				,DTP.DurationType_Nick as \"DurationType_Nick\"																--тип продолжительности
				,coalesce(ec_mu.SHORTNAME, ec_cu.SHORTNAME, ec_au.SHORTNAME) as \"EdUnits_Nick\"	-- Краткое название единицы измерения  - EdUnits_Nick
				,ec_gu.GoodsUnit_Nick as \"GoodsUnit_Nick\"										-- Краткое название единицы измерения по региональному справочнику - GoodsUnit_Nick
				,coalesce(PIT.PrescriptionIntroType_Name, '') as \"PrescriptionIntroType_Name\"  -- наименование метода введения
				,coalesce(PFT.PerformanceType_Name, '') as \"PerformanceType_Name\"	-- наименование типа исполнения

			from
				v_EvnCourseTreat ECT
				inner join v_EvnPrescrTreat EPT  on EPT.EvnCourse_id = ECT.EvnCourseTreat_id   -- назначение лекарственных средств (v_EvnPrescrTreat)
				inner join dbo.ReanimatPeriodPrescrLink RPPL   on EPT.EvnPrescrTreat_id = RPPL.EvnPrescr_id
				left join v_EvnCourseTreatDrug ec_drug  on ec_drug.EvnCourseTreat_id = ECT.EvnCourseTreat_id  ---- Медикаменты курса лекарственных средств
				left join rls.v_Drug Drug  on Drug.Drug_id = ec_drug.Drug_id    -- Региональный справочник Медикаменты курса
				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = coalesce(ec_drug.DrugComplexMnn_id,Drug.DrugComplexMnn_id)  -- Справочник комплексных МНН
				left join v_EvnPrescrTreatDrug EPTD  on EPTD.EvnPrescrTreat_id = EPT.EvnPrescrTreat_id     --Медикаменты назначения с типом лекарственное лечение
								and EPTD.DrugComplexMnn_id = dcm.DrugComplexMnn_id
				left join rls.MASSUNITS ec_mu  on ec_drug.MASSUNITS_ID = ec_mu.MASSUNITS_ID	 --Названия единиц массы курса
				left join rls.CUBICUNITS ec_cu  on ec_drug.CUBICUNITS_id = ec_cu.CUBICUNITS_id  --Единицы объема упаковок курса
				left join rls.ACTUNITS ec_au  on ec_drug.ACTUNITS_id = ec_au.ACTUNITS_id		--Названия единиц действия препаратов курса
				left join v_GoodsUnit ec_gu   on ec_drug.GoodsUnit_id = ec_gu.GoodsUnit_id		--Региональный справочник единиц измерения курса
				left join PerformanceType PFT  on  ECT.PerformanceType_id = PFT.PerformanceType_id  --Тип исполнения с курса
				left join PrescriptionIntroType PIT  on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id  --Метод введения с курса  с курса
				left join rls.CLSDRUGFORMS df  on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID    --Классификация лекарственных форм препаратов со справочника МНН
				left join DurationType DTP  on ECT.DurationType_id = DTP.DurationType_id    -- тип продолжительности
			where EvnCourseTreat_pid = :EvnCourseTreat_pid
			  and RPPL.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
			order by ECT.EvnCourseTreat_id, EPT.EvnPrescrTreat_setDt
		";
        $result = $callObject->db->query($query, $queryParams);
        sql_log_message('error', 'Search_model exec query: ', getDebugSql($query, $queryParams));

        if ( is_object($result) ) {
            $QueryResult = $result->result('array');

            $callObject->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');

            $EvnCourseTreat_id = '';
            $EvnPrescrTreat_id = '';
            $EvnCourseTreatDrug_id = '';
            $numCourse = 0;
            $DrugCourse = array();

            foreach($QueryResult as $row){

                $DrugCourseRow = array(
                    'EvnCourseTreat_id' => '',
                    'EvnCourse_Title' => '',
                    'EvnCourseTreat_setDate' => '',
                    'EvnPrescrTreat_IsCito' => '',
                    'DrugTorg_Name' => '',
                    'EvnPrescrTreat_setDate' => '',
                    'DoseOne' => '',
                    'DoseDay' => '',
                    'DoseCourse' => '',
                    'Duration' => '',
                    'PrescriptionIntroType_Name' => '',
                    'PerformanceType_Name' => ''
                );

                //если первая запись курса
                if ($row['EvnCourseTreat_id'] != $EvnCourseTreat_id){
                    $EvnCourseTreat_id = $row['EvnCourseTreat_id'];
                    $EvnPrescrTreat_id = $row['EvnPrescrTreat_id'];
                    $EvnCourseTreatDrug_id = $row['EvnCourseTreatDrug_id'];
                    $numCourse++;

                    $DrugCourseRow['EvnCourseTreat_id'] = $row['EvnCourseTreat_id'];
                    $DrugCourseRow['EvnCourse_Title'] = $numCourse;										// Курс №
                    $DrugCourseRow['EvnCourseTreat_setDate'] = $row['EvnCourseTreat_setDate'];			// дата создания курса
                    $DrugCourseRow['EvnPrescrTreat_IsCito'] = $row['EvnPrescrTreat_IsCito'];			// Cito

                    //Продолжительность
                    if (!empty($row['Duration']) && !empty($row['DurationType_Nick']))
                        $DrugCourseRow['Duration'] = $row['Duration'] . ' ' . $row['DurationType_Nick'];
                    //Метод введения
                    if (!empty($row['PrescriptionIntroType_Name']))
                        $DrugCourseRow['PrescriptionIntroType_Name'] = $row['PrescriptionIntroType_Name'];
                    //$callObject->description[] = array('name' => 'Метод введения', 'value' => htmlspecialchars($data['PrescriptionIntroType_Name']));
                    //Исполнение
                    if (!empty($row['PerformanceType_Name']))
                        $DrugCourseRow['PerformanceType_Name'] = $row['PerformanceType_Name'];
                }
                $DrugCourseRow['EvnCourse_id'] = $row['EvnCourseTreat_id'];

                //если записи первого назначения (дня) курса
                if ($row['EvnPrescrTreat_id'] == $EvnPrescrTreat_id){
                    $DrugCourseRow['DrugTorg_Name'] = $row['DrugTorg_Name'];  // Препараты - торг наименование

                    //Дозы:
                    //!!! алгоритмы взял из promed\libraries\SwPrescription.php 293-327 и promed\models\EvnPrescrTreat_model.php 3593-3733
                    //Разовая
                    $row['DrugForm_Nick'] = $callObject->EvnPrescrTreat_model->getDrugFormNick($row['DrugForm_Name'], $row['Drug_Name']);
                    if (!empty($row['EvnCourseTreatDrug_Kolvo']) && !empty($row['GoodsUnit_Nick'])) {
                        $DrugCourseRow['DoseOne'] = $row['EvnCourseTreatDrug_Kolvo'] . ' ' . ($row['GoodsUnit_Nick']);
                    } else if (!empty($row['EvnCourseTreatDrug_Kolvo']) && !empty($row['EdUnits_Nick'])) {
                        $DrugCourseRow['DoseOne'] = $row['EvnCourseTreatDrug_Kolvo'] . ' ' . ($row['EdUnits_Nick']);
                    } else if (!empty($row['EvnCourseTreatDrug_KolvoEd']) && !empty($row['DrugForm_Nick'])) {
                        $DrugCourseRow['DoseOne'] = $row['EvnCourseTreatDrug_KolvoEd'] . ' ' . ($row['DrugForm_Nick']);
                    }
                    //дневная
                    if (!empty($row['EvnCourseTreatDrug_MaxDoseDay']) && !empty($row['EvnCourseTreatDrug_MinDoseDay'])) {
                        if ($row['EvnCourseTreatDrug_MaxDoseDay'] == $row['EvnCourseTreatDrug_MinDoseDay']) {
                            $DrugCourseRow['DoseDay'] = $row['EvnCourseTreatDrug_MaxDoseDay'];

                            if(!empty($row['MaxCountInDay']) && !empty($row['GoodsUnit_Nick']) && !empty($row['EvnCourseTreatDrug_Kolvo'])){
                                $DrugCourseRow['DoseDay'] = ($row['EvnCourseTreatDrug_Kolvo']*$row['MaxCountInDay']) . ' ' . ($row['GoodsUnit_Nick']);
                            } else if(!empty($row['EvnPrescrTreatDrug_DoseDay'])){
                                $DrugCourseRow['DoseDay'] = $row['EvnPrescrTreatDrug_DoseDay'];
                            }
                        } else {
                            $DrugCourseRow['DoseDay'] = $row['EvnCourseTreatDrug_MinDoseDay'] . ' - ' . $row['EvnCourseTreatDrug_MaxDoseDay'];
                        }
                    }
                    //курсовая
                    if (!empty($row['EvnCourseTreatDrug_PrescrDose']))
                        $DrugCourseRow['DoseCourse'] = $row['EvnCourseTreatDrug_PrescrDose'];

                    //если запись первого лекарства в первом назначении
                    if ($EvnCourseTreatDrug_id == $row['EvnCourseTreatDrug_id']) {
                        $DrugCourseRow['EvnPrescrTreat_setDate'] = $row['EvnPrescrTreat_setDate'];		//Период: с ...
                    }

                    $DrugCourse[] = $DrugCourseRow;
                }



            }

            return $DrugCourse;
        }
        else {
            return false;
        }
    }

    /**
     * @param EvnReanimatPeriod_model $callObject
     * @param $data
     * @return array|bool
     */
    public static function loudEvnPrescrTreatDrugGrid(EvnReanimatPeriod_model $callObject, $data) {
		$queryParams = array(
			'EvnCourse_id' => $data['EvnCourse_id']
		);

		$query = "
			select
				EPTD.EvnPrescrTreatDrug_id as \"EvnPrescrTreatDrug_id\"
				,EPT.EvnPrescrTreat_setDt as \"EvnPrescrTreat_setDt\"
				,case when EPT.EvnPrescrTreat_setDate < GetDate()  then 1 else 0 end as \"prosroch\"  --просрочено - 1 / в работе  - 0
				,Drug.Drug_id as \"Drug_id\"
				,dcm.DrugComplexMnn_id as \"DrugComplexMnn_id\"
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as \"Drug_Name\"				-- Разовая доза	
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as \"DrugTorg_Name\"     -- Медикамент
				,EPTD.EvnPrescrTreatDrug_KolvoEd as \"KolvoEd\"											-- Разовая доза
				,coalesce(df.CLSDRUGFORMS_NameLatinSocr,df.NAME,Drug.DrugForm_Name,'') as \"DrugForm_Name\" -- Разовая доза
				,EPTD.EvnPrescrTreatDrug_Kolvo as \"Kolvo\"												-- Разовая доза
				,coalesce(ep_mu.SHORTNAME, ep_cu.SHORTNAME, ep_au.SHORTNAME) as \"EdUnits_Nick\"		-- Разовая доза
				,EPTD.EvnPrescrTreatDrug_DoseDay as \"DoseDay\"                                         -- Суточная доза
				,EPTD.EvnPrescrTreatDrug_FactCount as \"FactCntDay\"									-- количество исполненных приемов на дату
				,EPT.EvnPrescrTreat_PrescrCount as \"PrescrCntDay\"										-- количество назначенных приемов на дату
				,EPT.EvnCourse_id as \"EvnCourse_id\"
				,EPT.EvnPrescrTreat_id as \"EvnPrescrTreat_id\"
				,EPT.EvnPrescrTreat_pid as \"EvnPrescrTreat_pid\"
				,EPT.EvnPrescrTreat_rid as \"EvnPrescrTreat_rid\"
				,to_char(EPT.EvnPrescrTreat_setDT, 'dd.mm.yyyy') as \"EvnPrescrTreat_setDate\"			--Выполнение
				,EPT.EvnPrescrTreat_IsExec as \"EvnPrescr_IsExec\"										--Выполнение
				,EPT.PrescriptionStatusType_id as \"PrescriptionStatusType_id\"
				,PT.PrescriptionType_id as \"PrescriptionType_id\"
				,PT.PrescriptionType_Code as \"PrescriptionType_Code\"
				,case when EDr.EvnDrug_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\"
		from
				v_EvnPrescrTreat EPT
				inner join PrescriptionType PT  on PT.PrescriptionType_id = EPT.PrescriptionType_id
				inner join v_EvnPrescrTreatDrug EPTD  on EPT.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
				left join rls.v_Drug Drug  on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = coalesce(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df  on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.MASSUNITS ep_mu  on EPTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu  on EPTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				left join rls.ACTUNITS ep_au  on EPTD.ACTUNITS_ID = ep_au.ACTUNITS_ID
				LEFT JOIN LATERAL (
					select EvnDrug_id, EvnDrug_setDT from v_EvnDrug
					where EPT.EvnPrescrTreat_IsExec = 2 and EvnPrescr_id = EPT.EvnPrescrTreat_id
					limit 1
				) EDr on true
			where
				EPT.EvnCourse_id = :EvnCourse_id
			order by EPT.EvnPrescrTreat_setDt, EPTD.EvnPrescrTreatDrug_id
		";

        // echo getDebugSQL($query, $queryParams); exit();
        $result = $callObject->db->query($query, $queryParams);
        if ( !is_object($result) ) {
            return false;
        }

        $callObject->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');

        $result = $result->result('array');
        $response = array();
        $dayNum = 0;
        $EvnPrescrTreat_id = '';

        foreach ($result as $row) {
            $drug = $row;
            //день №
            if ($EvnPrescrTreat_id != $row['EvnPrescrTreat_id']) {
                $dayNum++;
                $EvnPrescrTreat_id = $row['EvnPrescrTreat_id'];
            }
            $drug['dayNum'] = $dayNum;
            //Разовая доза   // алгоритм взят из jscore\libs\swComponentLibPanels.js – 7887
            $drug['DoseOne'] = '-';
            $row['DrugForm_Nick'] = $callObject->EvnPrescrTreat_model->getDrugFormNick($row['DrugForm_Name'], ($row['Drug_Name']));
            if (!empty($row['Kolvo']) && !empty($row['EdUnits_Nick'])) {
                $drug['DoseOne'] = $row['Kolvo'] . ' ' . ($row['EdUnits_Nick']);
            } else if (!empty($row['KolvoEd']) && !empty($row['DrugForm_Nick'])) {
                $drug['DoseOne'] = $row['KolvoEd'] . ' ' . ($row['DrugForm_Nick']);
            }
            //Приемов в день   // алгоритм взят из jscore\libs\swComponentLibPanels.js - 7892
            $drug['CntDay'] = '0';
            if (!empty($row['FactCntDay']) && !empty($row['PrescrCntDay'])) {
                $drug['CntDay'] = $row['FactCntDay'] . ' / ' . ($row['PrescrCntDay']);
            } else if (empty($row['FactCntDay']) && !empty($row['PrescrCntDay'])) {
                $drug['CntDay'] = '0 / ' . ($row['PrescrCntDay']);
            }
            //Выполнение       // алгоритм взят из jscore\libs\swComponentLibPanels.js - 7898
            $iconPositionTpl = '0';  // в работе
            if ($row['EvnPrescr_IsExec'] == 2)
                $iconPositionTpl = '-105px';    //выполнено
            else if ($row['prosroch'] == 1)
                $iconPositionTpl = '-22px';     // просрочено

            $drug['ExecDay'] = '<span style="width:16px; height:16px; background:url(/img/EvnPrescrPlan/icon.png) no-repeat left top; background-position:0 '.$iconPositionTpl.'; display: block; position: relative; top: 0; left: 20px;"></span>';

            $response[] = $drug;
        }
        return $response;
    }
}