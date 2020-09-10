<?php

require_once(APPPATH.'models/_pgsql/EvnPrescr_model.php');

class Samara_EvnPrescr_model extends EvnPrescr_model {
	
	/**
	 * __construct
	 */
    function __construct() {
		parent::__construct();
    }
	/**
	 * loadEvnPrescrJournalGrid
	 */
	function loadEvnPrescrJournalGrid($data) {
		$filter = "(1 = 1)";
		$queryParams = array(
				'Lpu_id' => $data['Lpu_id'],
				'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : NULL)
		);
	
		$filter .= " and EP.Lpu_id = :Lpu_id
		and MSF.LpuSection_id = coalesce(ES.LpuSection_id, EPS.LpuSection_pid)";
	
		if ( !empty($data['PrescriptionType_id']) ) {
			$filter .= " and EP.PrescriptionType_id = :PrescriptionType_id";
			$queryParams['PrescriptionType_id'] = $data['PrescriptionType_id'];
		}
	
		if ( isset($data['EvnPrescr_setDate_Range'][0]) ) {
			$filter .= " and cast(coalesce(EP.EvnPrescr_setDT,Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT) as date) >= cast(:EvnPrescr_setDate_Range_0 as date)";
			$queryParams['EvnPrescr_setDate_Range_0'] = $data['EvnPrescr_setDate_Range'][0];
		}
	
		if ( isset($data['EvnPrescr_setDate_Range'][1]) ) {
			$filter .= " and cast(coalesce(EP.EvnPrescr_setDT,Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT) as date) <= cast(:EvnPrescr_setDate_Range_1 as date)";
			$queryParams['EvnPrescr_setDate_Range_1'] = $data['EvnPrescr_setDate_Range'][1];
		}
	
		if ( !empty($data['EvnPrescr_IsExec']) ) {
			$filter .= " and coalesce(EP.EvnPrescr_IsExec,Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,1) = :EvnPrescr_IsExec";
			$queryParams['EvnPrescr_IsExec'] = $data['EvnPrescr_IsExec'];
		}
	
		if ( !empty($data['Person_SurName']) ) {
			$filter .= " and PS.Person_SurName ilike :Person_SurName || '%'";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}
		if ( !empty($data['Person_FirName']) ) {
			$filter .= " and PS.Person_FirName ilike :Person_FirName || '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}
		if ( !empty($data['Person_SecName']) ) {
			$filter .= " and PS.Person_SecName ilike :Person_SecName || '%'";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
		}
		if ( !empty($data['Person_BirthDay']) ) {
			$filter .= " and PS.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if ( !empty($data['MedPersonal_id']) ) {
			//$filter .= " and coalesce(EP.MedPersonal_sid,Regime.MedPersonal_sid,Diet.MedPersonal_sid,Obs.MedPersonal_sid) = :MedPersonal_id";
			$filter .= " and PMUI.pmUser_Medpersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}
	
		if ( !empty($data['PrescriptionIntroType_id']) ) {
			$filter .= " and PIT.PrescriptionIntroType_id = :PrescriptionIntroType_id";
			$queryParams['PrescriptionIntroType_id'] = $data['PrescriptionIntroType_id'];
		}
	
		if ( !empty($data['LpuSectionWard_id']) ) {
			$filter .= " and coalesce(ES.LpuSectionWard_id, EPS.LpuSectionWard_id) = :LpuSectionWard_id";
			$queryParams['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		}

		if (!empty($data['isClose'])) {
			if ($data['isClose'] == 2) {
				$filter .= " and ES.LeaveType_id is not null";
			} else if ($data['isClose'] == 1) {
				$filter .= " and ES.LeaveType_id is null";
			}
		}
	
		$query = "
			select
				-- select
				coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,EP.EvnPrescr_id) || '-0' as \"EvnPrescr_key\",
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as \"EvnPrescrDay_id\"
				,EP.EvnPrescr_pid as \"EvnPrescr_pid\"
				,EP.EvnPrescr_rid as \"EvnPrescr_rid\"
				,coalesce(EPS.Diag_pid,ES.Diag_id) as \"Diag_id\"
				,EP.Person_id as \"Person_id\"
				,EP.PersonEvn_id as \"PersonEvn_id\"
				,EP.Server_id as \"Server_id\"
				,EP.PrescriptionType_id as \"PrescriptionType_id\"
				,coalesce(EP.EvnPrescr_IsCito, 1) as \"EvnPrescr_IsCito\"
				,coalesce(EP.EvnPrescr_IsExec,Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,1) as \"EvnPrescr_IsExec\"
				,coalesce(EP.PrescriptionStatusType_id,Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id) as \"PrescriptionStatusType_id\"
				,to_char(coalesce(EP.EvnPrescr_setDT,Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT), 'dd.mm.yyyy') as \"EvnPrescr_setDate\"
				,coalesce(to_char(TTMS.TimetableMedService_begTime, 'hh24:mi'), EP.EvnPrescr_setTime) as \"EvnPrescr_setTime\"
				,RTRIM(LTRIM(coalesce(PS.Person_Surname, '')))
					|| ' ' || RTRIM(LTRIM(coalesce(PS.Person_Firname, '')))
					|| ' ' || RTRIM(LTRIM(coalesce(PS.Person_Secname, '')))
				as \"Person_FIO\"
				,to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
				,coalesce(PT.PrescriptionType_Code, 0) as \"PrescriptionType_Code\"
				,coalesce(PT.PrescriptionType_Name, '') as \"PrescriptionType_Name\"
				,PST.PrescriptionStatusType_Name as \"PrescriptionStatusType_Name\"
				,to_char(EP.EvnPrescr_insDT, 'dd.mm.yyyy') || ' ' || to_char(EP.EvnPrescr_insDT, 'hh24:mi') as \"EvnPrescr_insDT\"
				,YN.YesNo_Name as \"IsExec_Name\"
				--,MPS.Person_Fio as \"MedPersonal_SignFIO\"
				,coalesce(EP.EvnPrescr_Descr,Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,'') as \"EvnPrescr_Descr\"
				,'' as \"LpuSectionProfile_Name\"
				,coalesce(PMUI.pmUser_Name, '') as \"pmUser_insName\"
				,LSW.LpuSectionWard_id as \"LpuSectionWard_id\"
				,PS.Sex_id as \"Sex_id\"
				,ES.EvnSection_id as \"EvnSection_id\"
				,coalesce(LSW.LpuSectionWard_Name, '') as \"LpuSectionWard_Name\"
				--1
				,coalesce(PRT.PrescriptionRegimeType_Name, '') as \"PrescriptionRegimeType_Name\"
				--2
				,coalesce(PDT.PrescriptionDietType_Name, '') as \"PrescriptionDietType_Name\"
				--5
				,coalesce(PIT.PrescriptionIntroType_Name, '') as \"PrescriptionIntroType_Name\"
				,0 as \"PrescriptionTreatType_Code\"
				,'' as \"PrescriptionTreatType_Name\"
				,coalesce(ECT.EvnCourseTreat_MaxCountDay, 0) as \"EvnPrescrTreat_CountInDay\"
				,EPTD.EvnPrescrTreatDrug_id as \"EvnPrescrTreatDrug_id\"
				,LTRIM(STR(EPTD.EvnPrescrTreatDrug_KolvoEd, 10, 2)) as \"EvnPrescrTreatDrug_KolvoEd\"
				,coalesce(df.NAME,Drug.DrugForm_Name) as \"DrugForm_Name\"
				,LTRIM(STR(EPTD.EvnPrescrTreatDrug_Kolvo, 10, 2)) as \"EvnPrescrTreatDrug_Kolvo\"
				,coalesce(ep_mu.SHORTNAME, ep_cu.SHORTNAME) as \"Okei_NationSymbol\"
				,coalesce(PIT.PrescriptionIntroType_Name, '') as \"PrescriptionIntroType_Name\"
				,coalesce(PFT.PerformanceType_Name, '') as \"PerformanceType_Name\"
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as \"Drug_Name\"
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as \"DrugTorg_Name\"
				,(select count(EvnPrescrTreatDrug_id) from v_EvnPrescrTreatDrug where EP.PrescriptionType_id = 5 and EvnPrescrTreat_id = EP.EvnPrescr_id) as \"cntDrug\"
				,EPTD.EvnPrescrTreatDrug_DoseDay as \"DoseDay\"
				,Treat.EvnPrescrTreat_FactCount as \"FactCntDay\"
				,Treat.EvnPrescrTreat_PrescrCount as \"PrescrCntDay\"
				--5,6
				,coalesce(ECT.EvnCourseTreat_MaxCountDay,ECP.EvnCourseProc_MaxCountDay, '') as \"CountInDay\"
				,coalesce(ECT.EvnCourseTreat_Duration,ECP.EvnCourseProc_Duration, '') as \"CourseDuration\"
				,coalesce(ECT.EvnCourseTreat_ContReception,ECP.EvnCourseProc_ContReception, '') as \"ContReception\"
				,coalesce(ECT.EvnCourseTreat_Interval,ECP.EvnCourseProc_Interval, '') as \"Interval\"
				,coalesce(DTP.DurationType_Nick, '') as \"DurationTypeP_Nick\"
				,coalesce(DTN.DurationType_Nick, '') as \"DurationTypeN_Nick\"
				,coalesce(DTI.DurationType_Nick, '') as \"DurationTypeI_Nick\"
				,EP.EvnPrescr_id as \"EvnPrescrTreatTimetable_id\"
				,EP.EvnPrescr_id as \"EvnPrescrProcTimetable_id\"
				--6,7,11,12,13
				,coalesce(EPOU.EvnPrescrOperUsluga_id, EPLD.EvnPrescrLabDiag_id, EPFDU.EvnPrescrFuncDiagUsluga_id,EPCU.EvnPrescrConsUsluga_id,0) as \"TableUsluga_id\"
				,UC.UslugaComplex_Name as \"UslugaComplex_Name\"
				,UC.UslugaComplex_id as \"UslugaComplex_id\"
				,case
					when EP.PrescriptionType_id = 6 then 1
					when EP.PrescriptionType_id = 13 then 1
					when EP.PrescriptionType_id = 7 then (select count(EvnPrescrOperUsluga_id) from v_EvnPrescrOperUsluga where EvnPrescrOper_id = EP.EvnPrescr_id)
					when EP.PrescriptionType_id = 11 then 1
					when EP.PrescriptionType_id = 12 then (select count(EvnPrescrFuncDiagUsluga_id) from v_EvnPrescrFuncDiagUsluga where EvnPrescrFuncDiag_id = EP.EvnPrescr_id)
					else 0
				end as \"cntUsluga\"
				,ED.EvnDirection_id as \"EvnDirection_id\"
				--10
				,Obs.ObservTimeType_id as \"ObservTimeType_id\"
				,coalesce(OTT.ObservTimeType_Name, '') as \"ObservTimeType_Name\"
				,EP.EvnCourse_id as \"EvnCourse_id\"
				-- end select
			from
				-- from
				v_EvnPrescr EP
				left join lateral(
					select
						epd.EvnDirection_id
					from v_EvnPrescrDirection epd
					where EP.EvnPrescr_id = epd.EvnPrescr_id
					order by epd.EvnPrescrDirection_insDT desc
					limit 1
				) EPD on true
				left join v_EvnDirection_all ED on EPD.EvnDirection_id = ED.EvnDirection_id and ED.DirFailType_id is null
				inner join PrescriptionType PT on PT.PrescriptionType_id = EP.PrescriptionType_id
				left join v_EvnPrescrRegime Regime on EP.PrescriptionType_id = 1 and Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
				left join v_EvnPrescrDiet Diet on EP.PrescriptionType_id = 2 and Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
				left join v_EvnPrescrObserv Obs on EP.PrescriptionType_id = 10 and Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				inner join PrescriptionStatusType PST on PST.PrescriptionStatusType_id = coalesce(EP.PrescriptionStatusType_id,Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id)
				left join EvnPS EPS on EPS.EvnPS_id = EP.EvnPrescr_pid
				left join EvnSection ES on ES.EvnSection_id = EP.EvnPrescr_pid
				left join v_LpuSectionWard LSW on LSW.LpuSectionWard_id = coalesce(EPS.LpuSectionWard_id, ES.LpuSectionWard_id)
				inner join v_PersonState PS on PS.Person_id = EP.Person_id
				--left join v_MedPersonal MPS on MPS.MedPersonal_id = coalesce(EP.MedPersonal_sid,Regime.MedPersonal_sid,Diet.MedPersonal_sid,Obs.MedPersonal_sid) and MPS.Lpu_id = EP.Lpu_id
	
				left join lateral(
					select
						LpuSection_id,
						MedPersonal_id
					from
						v_MedStaffFact
					where
						MedStaffFact_id = :MedStaffFact_id
					limit 1
				) MSF on true
				left join v_pmUser PMUI on PMUI.pmUser_id = EP.pmUser_insID
				--1
				left join PrescriptionRegimeType PRT on EP.PrescriptionType_id = 1 and PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				--2
				left join PrescriptionDietType PDT on EP.PrescriptionType_id = 2 and PDT.PrescriptionDietType_id = Diet.PrescriptionDietType_id
				--5
				left join v_EvnPrescrTreat Treat on EP.PrescriptionType_id = 5 and Treat.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnPrescrTreatDrug EPTD on EP.PrescriptionType_id = 5 and EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnCourseTreat ECT on EP.PrescriptionType_id = 5 and Treat.EvnCourse_id = ECT.EvnCourseTreat_id
				--left join v_EvnCourseTreatDrug ECTD on EP.PrescriptionType_id = 5 and ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join PrescriptionIntroType PIT on EP.PrescriptionType_id = 5 and ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join PerformanceType PFT on  EP.PrescriptionType_id = 5 and ECT.PerformanceType_id = PFT.PerformanceType_id
				left join rls.MASSUNITS ep_mu on EP.PrescriptionType_id = 5 and EPTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu on EP.PrescriptionType_id = 5 and EPTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				left join rls.v_Drug Drug on EP.PrescriptionType_id = 5 and Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm on EP.PrescriptionType_id = 5 and dcm.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
				left join rls.CLSDRUGFORMS df on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				--6
				left join v_EvnPrescrProc EPPR on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECP on EP.PrescriptionType_id = 6 and EPPR.EvnCourse_id = ECP.EvnCourseProc_id
				--5,6
				left join DurationType DTP on  EP.PrescriptionType_id in (5,6) and coalesce(ECP.DurationType_id,ECT.DurationType_id) = DTP.DurationType_id
				left join DurationType DTN on  EP.PrescriptionType_id in (5,6) and coalesce(ECP.DurationType_recid,ECT.DurationType_recid) = DTN.DurationType_id
				left join DurationType DTI on  EP.PrescriptionType_id in (5,6) and coalesce(ECP.DurationType_intid,ECT.DurationType_intid) = DTI.DurationType_id
				--7,11,12,13
				left join v_EvnPrescrOperUsluga EPOU on EP.PrescriptionType_id = 7 and EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				left join v_EvnPrescrLabDiag EPLD on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_EvnPrescrFuncDiagUsluga EPFDU on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				left join v_EvnPrescrConsUsluga EPCU on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC on EP.PrescriptionType_id in (6,7,11,12,13)
					and UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPFDU.UslugaComplex_id,EPLD.UslugaComplex_id,EPOU.UslugaComplex_id,EPCU.UslugaComplex_id)
				--10
				left join ObservTimeType OTT on EP.PrescriptionType_id = 10 and OTT.ObservTimeType_id = Obs.ObservTimeType_id
				left join YesNo YN on YN.YesNo_id = coalesce(EP.EvnPrescr_IsExec,Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,1)
	
				left join v_TimetableMedService_lite TTMS on TTMS.EvnDirection_id = ED.EvnDirection_id
				-- end from
	
	
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				EP.PrescriptionType_id,
				coalesce(EP.EvnPrescr_setDT,Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT),
				EP.EvnPrescr_id,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				PS.Person_Birthday
				-- end order by
		";
	
		//echo getDebugSQL($query, $queryParams); exit();
	
		$result = $this->db->query($query, $queryParams);
	
		if ( !is_object($result) ) {
			return array();
		}
	
		$res_arr = $result->result('array');
		//print_r($res_arr); exit();
		$response = array();
		$last_item = null;
		$item = null;
		$timeType=null;
		$tmp_arr = array();
		$tmp2_arr = array();
		$cnt = -1;
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		foreach ( $res_arr as &$evnPrescr ) {
			$evnPrescr['PrescriptionType_Name'] = str_replace(' ', '<br />', $evnPrescr['PrescriptionType_Name']);
			switch($evnPrescr['PrescriptionType_Code'])
			{
				case 4:
					$last_item = $evnPrescr['EvnPrescr_id'];
					$evnPrescr['EvnPrescr_Name'] = '<div>'.$evnPrescr['LpuSectionProfile_Name'];
					if ( $evnPrescr['EvnPrescr_IsCito'] == 2 )
						$evnPrescr['EvnPrescr_Name'] .=  '&nbsp;<span style="color: red">Cito!</span>';
					$evnPrescr['EvnPrescr_Name'] .= '</div>';
					$evnPrescr['UslugaId_List'] = '';
					$response[] = $evnPrescr;
					break;
				case 5:
					//Лекарственное лечение
					if($last_item != $evnPrescr['EvnPrescr_id'])
					{
						$last_item = $evnPrescr['EvnPrescr_id'];
						$item = 0;
						$tmp_arr = array();
						$cnt = $evnPrescr['cntDrug'];
					}
					if (empty($tmp_arr[$evnPrescr['EvnPrescrTreatDrug_id']]))
					{
						$str = $evnPrescr['Drug_Name'];
						if (!empty($evnPrescr['DoseDay'])) {
							$str .= ', дневная доза – '.$evnPrescr['DoseDay'];
						}
						if (!empty($evnPrescr['PrescrCntDay'])) {
							$str .= ', '.(empty($evnPrescr['FactCntDay'])?0:$evnPrescr['FactCntDay']).'/'.$evnPrescr['PrescrCntDay'].'.';
						} else {
							$str .= '.';
						}
						$tmp_arr[$evnPrescr['EvnPrescrTreatDrug_id']] = $str;
					}
					$item = $item + 1;
					if($item == $cnt)
					{
						$evnPrescr['EvnPrescr_Name'] = '<div>';
						$evnPrescr['EvnPrescr_Name'] .= implode('<br>',$tmp_arr);
						if ( !empty($evnPrescr['EvnPrescr_Descr']) ) {
							$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> '. htmlspecialchars($evnPrescr['EvnPrescr_Descr']) .'</div>';
						}
						$evnPrescr['EvnPrescr_Name'] .= '</div>';
						$evnPrescr['UslugaId_List'] = '';
						$response[] = $evnPrescr;
					}
					break;
				case 6:
					//Манипуляции и процедуры
					if($last_item != $evnPrescr['EvnPrescr_id'])
					{
						$last_item = $evnPrescr['EvnPrescr_id'];
						$item = 0;
						$cnt = 1;
					}
					$item = $item + 1;
					if($item == $cnt)
					{
						$evnPrescr['EvnPrescr_Name'] = '<div>';
						$evnPrescr['EvnPrescr_Name'] .= '<b>'.$evnPrescr['UslugaComplex_Name'].'</b>';
						$evnPrescr['EvnPrescr_Name'] .=  '.';
						if ( $evnPrescr['EvnPrescr_IsCito'] == 2 )
							$evnPrescr['EvnPrescr_Name'] .=  '&nbsp;<span style="color: red">Cito!</span>';
						if ( !empty($evnPrescr['EvnPrescr_Descr']) )
						{
							$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> ' .  htmlspecialchars($evnPrescr['EvnPrescr_Descr']) . '</div>';
						}
						$evnPrescr['EvnPrescr_Name'] .= '</div>';
						$evnPrescr['UslugaId_List'] = $evnPrescr['UslugaComplex_id'];
						$response[] = $evnPrescr;
					}
					break;
				case 7:
					//Оперативное лечение
				case 11:
					//Лабораторная диагностика
				case 12:
					//Функциональная диагностика
				case 13:
					//Консультационная услуга
					if($last_item != $evnPrescr['EvnPrescr_id'])
					{
						$last_item = $evnPrescr['EvnPrescr_id'];
						$item = 0;
						$tmp_arr = array();
						$tmp2_arr = array();
						$cnt = $evnPrescr['cntUsluga'];
					}
					if( empty($tmp_arr[$evnPrescr['EvnPrescr_id']]))
					{
						$tmp_arr[$evnPrescr['EvnPrescr_id']] = '<b>'.$evnPrescr['UslugaComplex_Name'].'</b>';
						$tmp2_arr[] = $evnPrescr['UslugaComplex_id'];
					}
					$item = $item + 1;
					if($item == $cnt)
					{
						$evnPrescr['EvnPrescr_Name'] = '<div>';
						$evnPrescr['EvnPrescr_Name'] .= implode(((in_array($evnPrescr['PrescriptionType_id'],array(7, 12)))?'<br />':', '),$tmp_arr);
						if ( $evnPrescr['EvnPrescr_IsCito'] == 2 )
							$evnPrescr['EvnPrescr_Name'] .=  '&nbsp;<span style="color: red">Cito!</span>';
						if ( !empty($evnPrescr['EvnPrescr_Descr']) )
						{
							$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> ' .  htmlspecialchars($evnPrescr['EvnPrescr_Descr']) . '</div>';
						}
						$evnPrescr['EvnPrescr_Name'] .= '</div>';
						$evnPrescr['UslugaId_List'] = implode(',',$tmp2_arr);
						$response[] = $evnPrescr;
					}
					break;
				case 10:
					if($last_item != $evnPrescr['EvnPrescr_id'])
					{
						$last_item = $evnPrescr['EvnPrescr_id'];
						$item = 0;
						$tmp_arr = array();
						$tmp2_arr = array();
						$cnt = $evnPrescr['cntUsluga'];
						$timeType='';
							
					}
					$timeType .= htmlspecialchars($evnPrescr['ObservTimeType_Name']).' ';
					$evnPrescr['EvnPrescrDay_id']=$evnPrescr['EvnPrescr_id'];
					$evnPrescr['EvnPrescr_Name'] = '<div>';
					if ( !empty($evnPrescr['ObservTimeType_Name']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<span style="font-weight: bold;">Время наблюдения:</span> ' .  $timeType . '</div>';
					}
					if ( !empty($evnPrescr['EvnPrescr_Descr']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> ' .  htmlspecialchars($evnPrescr['EvnPrescr_Descr']) . '</div>';
					}
					$evnPrescr['EvnPrescr_Name'] .= '</div>';
					$evnPrescr['UslugaId_List'] = '';
					$response[] = $evnPrescr;
					break;
				default:
					$last_item = null;
					$item = null;
					$tmp_arr = array();
					$cnt = -1;
					$evnPrescr['EvnPrescr_Name'] = '<div>';
					if ( !empty($evnPrescr['PrescriptionRegimeType_Name']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Тип режима:</span> ' .  htmlspecialchars($evnPrescr['PrescriptionRegimeType_Name']) . '</div>';
					}
					if ( !empty($evnPrescr['PrescriptionDietType_Name']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Тип диеты:</span> ' .  htmlspecialchars($evnPrescr['PrescriptionDietType_Name']) . '</div>';
					}
					if ( !empty($evnPrescr['ObservTimeType_Name']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Время наблюдения:</span> ' .  htmlspecialchars($evnPrescr['ObservTimeType_Name']) . '</div>';
					}
					if ( !empty($evnPrescr['EvnPrescr_Descr']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> ' .  htmlspecialchars($evnPrescr['EvnPrescr_Descr']) . '</div>';
					}
					$evnPrescr['EvnPrescr_Name'] .= '</div>';
					$evnPrescr['UslugaId_List'] = '';
					$response[] = $evnPrescr;
					break;
			}
		}
		return $response;
	}
}
?>
