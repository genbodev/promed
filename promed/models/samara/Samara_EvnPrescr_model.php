<?php

require_once(APPPATH.'models/EvnPrescr_model.php');

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
	
		$filter .= " and EP.Lpu_id = :Lpu_id and MSF.LpuSection_id = ISNULL(ES.LpuSection_id, EPS.LpuSection_pid)";
	
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
			$filter .= " and PS.Person_SurName like :Person_SurName + '%'";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}
		if ( !empty($data['Person_FirName']) ) {
			$filter .= " and PS.Person_FirName like :Person_FirName + '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}
		if ( !empty($data['Person_SecName']) ) {
			$filter .= " and PS.Person_SecName like :Person_SecName + '%'";
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
			$filter .= " and ISNULL(ES.LpuSectionWard_id, EPS.LpuSectionWard_id) = :LpuSectionWard_id";
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
				convert(varchar,coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,EP.EvnPrescr_id))+
				'-0'
				as EvnPrescr_key,
				EP.EvnPrescr_id,
				coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id
				,EP.EvnPrescr_pid
				,EP.EvnPrescr_rid
				,isnull(EPS.Diag_pid,ES.Diag_id) as Diag_id
				,EP.Person_id
				,EP.PersonEvn_id
				,EP.Server_id
				,EP.PrescriptionType_id
				,ISNULL(EP.EvnPrescr_IsCito, 1) as EvnPrescr_IsCito
				,coalesce(EP.EvnPrescr_IsExec,Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,1) as EvnPrescr_IsExec
				,coalesce(EP.PrescriptionStatusType_id,Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id) as PrescriptionStatusType_id
				,convert(varchar(10), coalesce(EP.EvnPrescr_setDT,Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT), 104) as EvnPrescr_setDate
				,isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108), EP.EvnPrescr_setTime) as EvnPrescr_setTime
				,RTRIM(LTRIM(ISNULL(PS.Person_Surname, ''))) + ' ' + RTRIM(LTRIM(ISNULL(PS.Person_Firname, ''))) + ' ' + RTRIM(LTRIM(ISNULL(PS.Person_Secname, ''))) as Person_FIO
				,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
				,ISNULL(PT.PrescriptionType_Code, 0) as PrescriptionType_Code
				,ISNULL(PT.PrescriptionType_Name, '') as PrescriptionType_Name
				,PST.PrescriptionStatusType_Name
				,convert(varchar(10), EP.EvnPrescr_insDT, 104) + ' ' + convert(varchar(5), EP.EvnPrescr_insDT, 108) as EvnPrescr_insDT
				,YN.YesNo_Name as IsExec_Name
				--,MPS.Person_Fio as MedPersonal_SignFIO
				,coalesce(EP.EvnPrescr_Descr,Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,'') as EvnPrescr_Descr
				,'' as LpuSectionProfile_Name
				,ISNULL(PMUI.pmUser_Name, '') as pmUser_insName
				,LSW.LpuSectionWard_id
				,PS.Sex_id
				,ES.EvnSection_id
				,ISNULL(LSW.LpuSectionWard_Name, '') as LpuSectionWard_Name
				--1
				,ISNULL(PRT.PrescriptionRegimeType_Name, '') as PrescriptionRegimeType_Name
				--2
				,ISNULL(PDT.PrescriptionDietType_Name, '') as PrescriptionDietType_Name
				--5
				,ISNULL(PIT.PrescriptionIntroType_Name, '') as PrescriptionIntroType_Name
				,0 as PrescriptionTreatType_Code
				,'' as PrescriptionTreatType_Name
				,ISNULL(ECT.EvnCourseTreat_MaxCountDay, 0) as EvnPrescrTreat_CountInDay
				,EPTD.EvnPrescrTreatDrug_id
				,LTRIM(STR(EPTD.EvnPrescrTreatDrug_KolvoEd, 10, 2)) as EvnPrescrTreatDrug_KolvoEd
				,ISNULL(df.NAME,Drug.DrugForm_Name) as DrugForm_Name
				,LTRIM(STR(EPTD.EvnPrescrTreatDrug_Kolvo, 10, 2)) as EvnPrescrTreatDrug_Kolvo
				,isnull(ep_mu.SHORTNAME, ep_cu.SHORTNAME) as Okei_NationSymbol
				,ISNULL(PIT.PrescriptionIntroType_Name, '') as PrescriptionIntroType_Name
				,ISNULL(PFT.PerformanceType_Name, '') as PerformanceType_Name
				,coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as Drug_Name
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, '') as DrugTorg_Name
				,(select count(EvnPrescrTreatDrug_id) from v_EvnPrescrTreatDrug with (nolock) where EP.PrescriptionType_id = 5 and EvnPrescrTreat_id = EP.EvnPrescr_id) as cntDrug
				,EPTD.EvnPrescrTreatDrug_DoseDay as DoseDay
				,Treat.EvnPrescrTreat_FactCount as FactCntDay
				,Treat.EvnPrescrTreat_PrescrCount as PrescrCntDay
				--5,6
				,coalesce(ECT.EvnCourseTreat_MaxCountDay,ECP.EvnCourseProc_MaxCountDay, '') as CountInDay
				,coalesce(ECT.EvnCourseTreat_Duration,ECP.EvnCourseProc_Duration, '') as CourseDuration
				,coalesce(ECT.EvnCourseTreat_ContReception,ECP.EvnCourseProc_ContReception, '') as ContReception
				,coalesce(ECT.EvnCourseTreat_Interval,ECP.EvnCourseProc_Interval, '') as Interval
				,ISNULL(DTP.DurationType_Nick, '') as DurationTypeP_Nick
				,ISNULL(DTN.DurationType_Nick, '') as DurationTypeN_Nick
				,ISNULL(DTI.DurationType_Nick, '') as DurationTypeI_Nick
				,EP.EvnPrescr_id as EvnPrescrTreatTimetable_id
				,EP.EvnPrescr_id as EvnPrescrProcTimetable_id
				--6,7,11,12,13
				,coalesce(EPOU.EvnPrescrOperUsluga_id, EPLD.EvnPrescrLabDiag_id, EPFDU.EvnPrescrFuncDiagUsluga_id,EPCU.EvnPrescrConsUsluga_id,0) as TableUsluga_id
				,UC.UslugaComplex_Name
				,UC.UslugaComplex_id
				,case
					when EP.PrescriptionType_id = 6 then 1
					when EP.PrescriptionType_id = 13 then 1
					when EP.PrescriptionType_id = 7 then (select count(EvnPrescrOperUsluga_id) from v_EvnPrescrOperUsluga with (nolock) where EvnPrescrOper_id = EP.EvnPrescr_id)
					when EP.PrescriptionType_id = 11 then 1
					when EP.PrescriptionType_id = 12 then (select count(EvnPrescrFuncDiagUsluga_id) from v_EvnPrescrFuncDiagUsluga with (nolock) where EvnPrescrFuncDiag_id = EP.EvnPrescr_id)
					else 0
				end as cntUsluga
				,ED.EvnDirection_id
				--10
				,Obs.ObservTimeType_id
				,ISNULL(OTT.ObservTimeType_Name, '') as ObservTimeType_Name
				,EP.EvnCourse_id
				-- end select
			from
				-- from
				v_EvnPrescr EP with (nolock)
				outer apply (
					select top 1 epd.EvnDirection_id from v_EvnPrescrDirection epd with (nolock)
					where EP.EvnPrescr_id = epd.EvnPrescr_id
					order by epd.EvnPrescrDirection_insDT desc
				) EPD
				left join v_EvnDirection_all ED with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id and ED.DirFailType_id is null
				inner join PrescriptionType PT with (nolock) on PT.PrescriptionType_id = EP.PrescriptionType_id
				left join v_EvnPrescrRegime Regime with (nolock) on EP.PrescriptionType_id = 1 and Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
				left join v_EvnPrescrDiet Diet with (nolock) on EP.PrescriptionType_id = 2 and Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
				left join v_EvnPrescrObserv Obs with (nolock) on EP.PrescriptionType_id = 10 and Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
				inner join PrescriptionStatusType PST with (nolock) on PST.PrescriptionStatusType_id = coalesce(EP.PrescriptionStatusType_id,Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id)
				left join EvnPS EPS with (nolock) on EPS.EvnPS_id = EP.EvnPrescr_pid
				left join EvnSection ES with (nolock) on ES.EvnSection_id = EP.EvnPrescr_pid
				left join v_LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = ISNULL(EPS.LpuSectionWard_id, ES.LpuSectionWard_id)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EP.Person_id
				--left join v_MedPersonal MPS with (nolock) on MPS.MedPersonal_id = coalesce(EP.MedPersonal_sid,Regime.MedPersonal_sid,Diet.MedPersonal_sid,Obs.MedPersonal_sid) and MPS.Lpu_id = EP.Lpu_id
	
				outer apply (
					select top 1
						LpuSection_id,
						MedPersonal_id
					from
						v_MedStaffFact with (nolock)
					where
						MedStaffFact_id = :MedStaffFact_id
				) MSF
				left join v_pmUser PMUI with (nolock) on PMUI.pmUser_id = EP.pmUser_insID
				--1
				left join PrescriptionRegimeType PRT with (nolock) on EP.PrescriptionType_id = 1 and PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				--2
				left join PrescriptionDietType PDT with (nolock) on EP.PrescriptionType_id = 2 and PDT.PrescriptionDietType_id = Diet.PrescriptionDietType_id
				--5
				left join v_EvnPrescrTreat Treat with (nolock) on EP.PrescriptionType_id = 5 and Treat.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnPrescrTreatDrug EPTD with (nolock) on EP.PrescriptionType_id = 5 and EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnCourseTreat ECT with (nolock) on EP.PrescriptionType_id = 5 and Treat.EvnCourse_id = ECT.EvnCourseTreat_id
				--left join v_EvnCourseTreatDrug ECTD with (nolock) on EP.PrescriptionType_id = 5 and ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join PrescriptionIntroType PIT with (nolock) on EP.PrescriptionType_id = 5 and ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join PerformanceType PFT with (nolock) on  EP.PrescriptionType_id = 5 and ECT.PerformanceType_id = PFT.PerformanceType_id
				left join rls.MASSUNITS ep_mu with (nolock) on EP.PrescriptionType_id = 5 and EPTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu with (nolock) on EP.PrescriptionType_id = 5 and EPTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				left join rls.v_Drug Drug with (nolock) on EP.PrescriptionType_id = 5 and Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on EP.PrescriptionType_id = 5 and dcm.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				--6
				left join v_EvnPrescrProc EPPR with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECP with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnCourse_id = ECP.EvnCourseProc_id
				--5,6
				left join DurationType DTP with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_id,ECT.DurationType_id) = DTP.DurationType_id
				left join DurationType DTN with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_recid,ECT.DurationType_recid) = DTN.DurationType_id
				left join DurationType DTI with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_intid,ECT.DurationType_intid) = DTI.DurationType_id
				--7,11,12,13
				left join v_EvnPrescrOperUsluga EPOU with (nolock) on EP.PrescriptionType_id = 7 and EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				left join v_EvnPrescrLabDiag EPLD with (nolock) on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				left join v_EvnPrescrConsUsluga EPCU with (nolock) on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on EP.PrescriptionType_id in (6,7,11,12,13) and UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPFDU.UslugaComplex_id,EPLD.UslugaComplex_id,EPOU.UslugaComplex_id,EPCU.UslugaComplex_id)
				--10
				left join ObservTimeType OTT with (nolock) on EP.PrescriptionType_id = 10 and OTT.ObservTimeType_id = Obs.ObservTimeType_id
				left join YesNo YN with (nolock) on YN.YesNo_id = coalesce(EP.EvnPrescr_IsExec,Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,1)
	
				left join v_TimetableMedService_lite TTMS with (nolock) on TTMS.EvnDirection_id = ED.EvnDirection_id
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
						/*
							$tmp_arr[$evnPrescr['EvnPrescrTreatDrug_id']] = '<img src="/img/icons/dlo16.png" title="'.$evnPrescr['Drug_Name'].'"/>&nbsp;<b>'.$evnPrescr['DrugTorg_Name'].'</b>';
						$DrugForm_Nick = $this->EvnPrescrTreat_model->getDrugFormNick($evnPrescr['DrugForm_Name'], $evnPrescr['Drug_Name']);
						*/
					}
					$item = $item + 1;
					if($item == $cnt)
					{
						$evnPrescr['EvnPrescr_Name'] = '<div>';
						$evnPrescr['EvnPrescr_Name'] .= implode('<br>',$tmp_arr);
						/*
							if ( !empty($evnPrescr['EvnPrescrTreatDrug_KolvoEd']))
							$evnPrescr['EvnPrescr_Name'] .=  ' По '. htmlspecialchars($evnPrescr['EvnPrescrTreatDrug_KolvoEd']) .' '.(empty($DrugForm_Nick)?'ед.дозировки':$DrugForm_Nick).' ';
						if ( !empty($evnPrescr['EvnPrescrTreatDrug_Kolvo']) && empty($evnPrescr['EvnPrescrTreatDrug_KolvoEd']) )
							$evnPrescr['EvnPrescr_Name'] .=  htmlspecialchars($evnPrescr['EvnPrescrTreatDrug_Kolvo']) .' ';
						if ( !empty($evnPrescr['Okei_NationSymbol']) && empty($evnPrescr['EvnPrescrTreatDrug_KolvoEd']) )
							$evnPrescr['EvnPrescr_Name'] .=  htmlspecialchars($evnPrescr['Okei_NationSymbol']) .' ';
						if ( !empty($evnPrescr['CountInDay']))
							$evnPrescr['EvnPrescr_Name'] .=  htmlspecialchars($evnPrescr['CountInDay']) .'&nbsp;'.(in_array($evnPrescr['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
						if ( !empty($evnPrescr['ContReception']))
							$evnPrescr['EvnPrescr_Name'] .=  ', принимать '. htmlspecialchars($evnPrescr['ContReception']) .' '. htmlspecialchars($evnPrescr['DurationTypeN_Nick']);
						if ( !empty($evnPrescr['Interval']))
							$evnPrescr['EvnPrescr_Name'] .=  ', перерыв '. htmlspecialchars($evnPrescr['Interval']) .' '. htmlspecialchars($evnPrescr['DurationTypeI_Nick']);
						if ( !empty($evnPrescr['CourseDuration']) && $evnPrescr['CourseDuration'] != $evnPrescr['ContReception'] )
							$evnPrescr['EvnPrescr_Name'] .=  ', в течение '. htmlspecialchars($evnPrescr['CourseDuration']) .' '. htmlspecialchars($evnPrescr['DurationTypeP_Nick']);
						$evnPrescr['EvnPrescr_Name'] .=  '.';
						if ( !empty($evnPrescr['PrescriptionIntroType_Name']))
							$evnPrescr['EvnPrescr_Name'] .=  '<br />Метод введения: '. htmlspecialchars($evnPrescr['PrescriptionIntroType_Name']);
						if ( !empty($evnPrescr['PerformanceType_Name']))
							$evnPrescr['EvnPrescr_Name'] .=  '<br />Исполнение: '. htmlspecialchars($evnPrescr['PerformanceType_Name']);
						if ( $evnPrescr['EvnPrescr_IsCito'] == 2 )
							$evnPrescr['EvnPrescr_Name'] .=  '&nbsp;<span style="color: red">Cito!</span>';
						*/
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
