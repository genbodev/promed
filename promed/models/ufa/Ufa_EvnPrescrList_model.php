<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Ufa_Dlo_EvnRecept_model - модель, для работы с таблицей EvnRecept. Версия для Уфы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version      31.05.2013
*/

require_once(APPPATH.'models/EvnPrescrList_model.php');

class Ufa_EvnPrescrList_model extends EvnPrescrList_model {
	
	/**
	 * Получение данных для шаблона печати "Лист врачебных назначений"
	 * Отображается список курсов или назначений и календарь с отметками об исполнении врачом или сестрой
	 * Имя шаблона: print_evnprescr_list
	 */
	
	function doloadEvnPrescrDoctorList($data) {
		$queryParams = array('Evn_pid'=>$data['Evn_pid']); 
		//получить данные по учетному документу ФИО, Название МО, Отделение, лечащий врач.
		//получаем первую дату назначения
		$query = "
			select top 1
				0 DocType_id,
				PS.Person_SurName + ' '+ isnull(PS.Person_FirName,'') + ' ' + isnull(PS.Person_SecName,'') as Person_FIO
				,convert(varchar(10), PS.Person_BirthDay, 104) as Person_Birthday
				,Lpu.Lpu_Name
				,Lpu.Lpu_Nick
				,LS.LpuSection_Code
				,LS.LpuSection_Name
				,MP.Person_Fio as MedPersonal_Fio
				,cast(cast(isnull(EP.EvnPrescr_setDT, evn.Evn_setDT) as date) as varchar(10)) as EvnPrescr_date --2014-01-23
				,evn.EvnClass_SysNick
				,PEH.PersonEncrypHIV_Encryp
				,case
					when evn.EvnClass_SysNick = 'EvnVizitPL' OR  evn.EvnClass_SysNick = 'EvnVizitPLStom' then EPLPID.EvnPL_NumCard
					else EPSPID.EvnPS_NumCard
				end as NumCard
			from
				v_Evn evn with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = evn.Person_id
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = evn.Lpu_id
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = evn.Evn_id
				left join v_EvnSection ES with (nolock) on ES.EvnSection_id = evn.Evn_id
				left join v_EvnVizitPL EV with (nolock) on EV.EvnVizitPL_id = evn.Evn_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = coalesce(EPS.LpuSection_pid, ES.LpuSection_id, EV.LpuSection_id)
				left join v_MedPersonal MP with (nolock) on MP.Lpu_id = evn.Lpu_id and MP.MedPersonal_id = coalesce(EPS.MedPersonal_pid, ES.MedPersonal_id, EV.MedPersonal_id)
				outer apply (
					select top 1
						coalesce( Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescr_setDT
					from v_EvnPrescr EP with (nolock)
					left join v_EvnPrescrRegime Regime with (nolock) on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
						and Regime.PrescriptionStatusType_id != 3
					left join v_EvnPrescrDiet Diet with (nolock) on Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
						and Diet.PrescriptionStatusType_id != 3
					left join v_EvnPrescrObserv Obs with (nolock) on Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
						and Obs.PrescriptionStatusType_id != 3
					where EP.EvnPrescr_pid = evn.Evn_id -- and EP.EvnPrescr_setDT is not null
					order by coalesce( Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT)
				) EP
				outer apply(
					SELECT
						EvnPS_NumCard
					FROM v_EvnPS WHERE EvnPS_id = evn.Evn_pid
				) EPSPID
				outer apply(
					SELECT
						EvnPL_NumCard
					FROM v_EvnPL WHERE EvnPL_id = evn.Evn_pid
				) EPLPID
				left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
			where
				evn.Evn_id = :Evn_pid
		";

		//echo '<pre>' . print_r(getDebugSQL($query, $queryParams), 1) . '</pre>'; exit;
		
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$response = $result->result('array');
		} else {
			throw new Exception('Не удалось запросить данные по учетному документу!',400);
		}
		if (empty($response) || empty($response[0]['Person_FIO'])) {
			throw new Exception('Не удалось получить данные по учетному документу!',400);
		}
		if (empty($response[0]['EvnPrescr_date'])) {
			throw new Exception('Не удалось определить дату первого назначения по учетному документу!',400);
		}
		$parse_data = $response[0];

		$isPolka = (in_array($parse_data['EvnClass_SysNick'], array('EvnVizitPL', 'EvnVizitPLStom')));
		$regionNick = $data['session']['region']['nick'];

		$addSelect = '';
		$addJoin = '';
		$filter = '';
		if ($isPolka) {
			// для полки не нужна разбивка по датам и выборка кто выполнил
		} else {
			// для стационара нужна разбивка по датам и выборка кто назначил, кто выполнил #38401
			$queryParams['EvnPrescr_begDate'] = $response[0]['EvnPrescr_date'];
			$addSelect .= "
				,DATEDIFF(DAY, cast(:EvnPrescr_begDate as datetime), coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT)) + 1 as DayNum
				,coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
				, PMUP.pmUserPrescr_Name
				, PMUP.pmUserPrescr_FIO
				,substring(ISNULL(PMUE.PMUser_surName, ''),1,1) + substring(ISNULL(PMUE.PMUser_FirName, ''),1,1) + substring(ISNULL(PMUE.PMUser_SecName, ''),1,1) as pmUserExec_Name
				,ISNULL(PMUE.PMUser_surName, '') + ' '+ substring(ISNULL(PMUE.PMUser_FirName, ''),1,1) + '.' + substring(ISNULL(PMUE.PMUser_SecName, ''),1,1) + '.' as pmUserExec_FIO";
			/*
			$addJoin .= "
				left join v_pmUser PMUP with (nolock) on PMUP.pmUser_id = coalesce(Regime.pmUser_insID, Diet.pmUser_insID, Obs.pmUser_insID, EP.pmUser_insID)
				left join v_pmUser PMUE with (nolock) on PMUE.pmUser_id = coalesce(Regime.pmUser_updID, Diet.pmUser_updID, Obs.pmUser_updID, EP.pmUser_updID)";
			*/
			$addJoin .= "
				left join v_pmUserCache PMUE with (nolock) on PMUE.pmUser_id = coalesce(Regime.pmUser_updID, Diet.pmUser_updID, Obs.pmUser_updID, EP.pmUser_updID)
				outer apply (SElect 
						case 
							when t2.pmUser_id is not null then 
								substring(ISNULL(t2.PMUser_surName, ''),1,1) + substring(ISNULL(t2.PMUser_FirName, ''),1,1) + substring(ISNULL(t2.PMUser_SecName, ''),1,1)
							else
								ISNULL(t.PMUser_Name, '')
						end as pmUserPrescr_FIO,
						case 
							when t2.pmUser_id is not null then 
								ISNULL(t2.PMUser_surName, '') + ' '+ substring(ISNULL(t2.PMUser_FirName, ''),1,1) + '.' + substring(ISNULL(t2.PMUser_SecName, ''),1,1) + '.'
							else
								ISNULL(t.PMUser_Name, '')
						end as pmUserPrescr_Name
					from v_pmUser t with (nolock) join v_pmUserCache t2 with (nolock) on t2.pmUser_id = coalesce(Regime.pmUser_insID, Diet.pmUser_insID, Obs.pmUser_insID, EP.pmUser_insID)
						where t.pmUser_id = coalesce(Regime.pmUser_insID, Diet.pmUser_insID, Obs.pmUser_insID, EP.pmUser_insID)
				) PMUP ";
				
			if ($regionNick == 'ufa') {
				$addJoin .= "
					outer apply (SElect * from r2.fn_getEvnCourseTreatTimeEntry_time(  EP.EvnPrescr_id) where 1=1) gect";
				$addSelect .= "
					,gect.EvnPrescr_allTime
				    , gect.pmUserExec_Time2FIO";
				if (isset($data['DocType_id']) && $data['DocType_id'] == 5) 
					$filter .= ' and PT.PrescriptionType_id = 5 ';
				if (isset($data['DocType_id']) && $data['DocType_id'] == -5) 
					$filter .= ' and PT.PrescriptionType_id != 5 ';
				
				/*
				else 
					$filter .= ' and PT.PrescriptionType_id != 5 ';
				*/
			}
		
		}

		if ($regionNick == 'ufa') {
			// выбираем латинские наименования препаратов
			$addSelect .= "
				,coalesce(AM.LATNAME, dcm.DrugComplexMnn_LatName, '') as Drug_Name";
		} else {
			// отображаем торговое наименования препаратов на русском
			$addSelect .= "
				,'' as Drug_Name";

		}


		// получаем данные назначений
		$query = "
			select
			    --общие атрибуты назначения в конкретный день
				ECTD.Drug_id,
				coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id
				,PST.PrescriptionStatusType_id
				--чтобы отобразить в одной строке одно назначение-курс
				,coalesce(ECT.EvnCourseTreat_id,ECP.EvnCourseProc_id,EP.EvnPrescr_id) as EvnCoursePrescr_id
				,EP.EvnPrescr_Descr
				,PT.PrescriptionType_id
				,PT.PrescriptionType_Name
				--1
				,ISNULL(PRT.PrescriptionRegimeType_Name, '') as PrescriptionRegimeType_Name
				--2
				,ISNULL(PDT.PrescriptionDietType_Code, '') as PrescriptionDietType_Code
				,ISNULL(PDT.PrescriptionDietType_Name, '') as PrescriptionDietType_Name
				--5
				,ECTD.EvnCourseTreatDrug_id
				,LTRIM(STR(ECTD.EvnCourseTreatDrug_KolvoEd, 10, 2)) as KolvoEd
				,ISNULL(df.NAME,Drug.DrugForm_Name) as DrugForm_Name
				--,LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 2)) as Kolvo
				,case
					when ECTD.EvnCourseTreatDrug_Kolvo % 1 = 0
						then LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 0))
					when ECTD.EvnCourseTreatDrug_Kolvo * 10 % 1 = 0
						then LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 1))
					else
						LTRIM(STR(ECTD.EvnCourseTreatDrug_Kolvo, 10, 2))
				end as Kolvo
				--,isnull(ep_mu.SHORTNAME, ep_cu.SHORTNAME) as Okei_NationSymbol
				, coalesce(ep_mu.SHORTNAME, ep_cu.SHORTNAME, ep_au.SHORTNAME, ep_gu.GoodsUnit_Nick) as Okei_NationSymbol
				,coalesce(Drug.DrugTorg_Name, dcm.DrugComplexMnn_RusName, MnnName.DrugComplexMnnName_Name, '') as DrugTorg_Name
				--5,6 параметры графика
				,coalesce(ECT.EvnCourseTreat_MaxCountDay,ECP.EvnCourseProc_MaxCountDay, '') as CountInDay
				,coalesce(ECT.EvnCourseTreat_Duration,ECP.EvnCourseProc_Duration, '') as CourseDuration
				,coalesce(ECT.EvnCourseTreat_ContReception,ECP.EvnCourseProc_ContReception, '') as ContReception
				,coalesce(ECT.EvnCourseTreat_Interval,ECP.EvnCourseProc_Interval, '') as Interval
				,ISNULL(DTP.DurationType_Nick, '') as DurationTypeP_Nick
				,ISNULL(DTN.DurationType_Nick, '') as DurationTypeN_Nick
				,ISNULL(DTI.DurationType_Nick, '') as DurationTypeI_Nick
				--6,7,11,12,13 услуги
				,coalesce(EPPR.EvnPrescrProc_id,EPOU.EvnPrescrOperUsluga_id, EPLDU.EvnPrescrLabDiagUsluga_id, EPFDU.EvnPrescrFuncDiagUsluga_id,EPCU.EvnPrescrConsUsluga_id,0) as TableUsluga_id
				,UC.UslugaComplex_Code
				,UC.UslugaComplex_Name
				,PUC.UslugaComplex_Code as UslugaComplexP_Code
				,PUC.UslugaComplex_Name as UslugaComplexP_Name
				--10
				,ISNULL(OTT.ObservTimeType_id, 0) as ObservTimeType_id
				,ISNULL(OTT.ObservTimeType_Name, '') as ObservTimeType_Name
				--Параметры наблюдения
				,EPOP.EvnPrescrObservPos_id
				,ISNULL(OPT.ObservParamType_Name, '') as ObservParamType_Name
				, ECT.PrescriptionIntroType_id
				, isnull(pit.PrescriptionIntroType_Name, '') PrescriptionIntroType_Name
				{$addSelect}
			from
				v_EvnPrescr EP with (nolock)
				inner join v_PrescriptionType PT with (nolock) on PT.PrescriptionType_id = EP.PrescriptionType_id
				--1
				left join v_EvnPrescrRegime Regime with (nolock) on EP.PrescriptionType_id = 1
					and Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
					and Regime.PrescriptionStatusType_id != 3
				left join v_PrescriptionRegimeType PRT with (nolock) on EP.PrescriptionType_id = 1 and PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				--2
				left join v_EvnPrescrDiet Diet with (nolock) on EP.PrescriptionType_id = 2
					and Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
					and Diet.PrescriptionStatusType_id != 3
				left join v_PrescriptionDietType PDT with (nolock) on EP.PrescriptionType_id = 2 and PDT.PrescriptionDietType_id = Diet.PrescriptionDietType_id
				--10
				left join v_EvnPrescrObserv Obs with (nolock) on EP.PrescriptionType_id = 10
					and Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
					and Obs.PrescriptionStatusType_id != 3
				--5
				left join v_EvnPrescrTreat Treat with (nolock) on EP.PrescriptionType_id = 5 and Treat.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnCourseTreat ECT with (nolock) on EP.PrescriptionType_id = 5 and Treat.EvnCourse_id = ECT.EvnCourseTreat_id
				left join v_EvnCourseTreatDrug ECTD with (nolock) on EP.PrescriptionType_id = 5 and ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join rls.MASSUNITS ep_mu with (nolock) on EP.PrescriptionType_id = 5 and ECTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
				left join rls.CUBICUNITS ep_cu with (nolock) on EP.PrescriptionType_id = 5 and ECTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
				-- Добавлено https://redmine.swan.perm.ru/issues/136715
				left join rls.ACTUNITS ep_au with (nolock) on ECTD.ACTUNITS_id = ep_au.ACTUNITS_id
				left join GoodsUnit ep_gu with (nolock) on ECTD.GoodsUnit_id = ep_gu.GoodsUnit_id
				
				left join rls.v_Drug Drug with (nolock) on EP.PrescriptionType_id = 5 and Drug.Drug_id = ECTD.Drug_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on EP.PrescriptionType_id = 5 and dcm.DrugComplexMnn_id = isnull(ECTD.DrugComplexMnn_id, Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df with (nolock) on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join rls.v_DrugComplexMnnName MnnName with (nolock) on MnnName.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
				left join rls.v_ACTMATTERS AM with (nolock) on AM.ACTMATTERS_ID = MnnName.ActMatters_id
				left join v_PrescriptionIntroType pit with (nolock) on pit.PrescriptionIntroType_id = ect.PrescriptionIntroType_id
				--6
				left join v_EvnPrescrProc EPPR with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECP with (nolock) on EP.PrescriptionType_id = 6 and EPPR.EvnCourse_id = ECP.EvnCourseProc_id
				--5,6
				left join DurationType DTP with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_id,ECT.DurationType_id) = DTP.DurationType_id
				left join DurationType DTN with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_recid,ECT.DurationType_recid) = DTN.DurationType_id
				left join DurationType DTI with (nolock) on  EP.PrescriptionType_id in (5,6) and isnull(ECP.DurationType_intid,ECT.DurationType_intid) = DTI.DurationType_id
				--6,7,11,12,13
				--left join v_EvnPrescrOperUsluga EPOU with (nolock) on EP.PrescriptionType_id = 7 and EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrOperUsluga with (nolock) where EP.PrescriptionType_id = 7 and EvnPrescrOper_id = EP.EvnPrescr_id
				) EPOU
				--left join v_EvnPrescrLabDiag EPLD with (nolock) on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrLabDiag with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
				) EPLD
				--left join v_EvnPrescrLabDiagUsluga EPLDU with (nolock) on EP.PrescriptionType_id = 11 and EPLDU.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrLabDiagUsluga with (nolock) where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id
				) EPLDU
				--left join v_EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EP.PrescriptionType_id = 12 and EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrFuncDiagUsluga with (nolock) where EP.PrescriptionType_id = 12 and EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				) EPFDU
				--left join v_EvnPrescrConsUsluga EPCU with (nolock) on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				outer apply (
					select top 100 * from v_EvnPrescrConsUsluga with (nolock) where EP.PrescriptionType_id = 13 and EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				) EPCU
				left join v_UslugaComplex UC with (nolock) on EP.PrescriptionType_id in (6,7,11,12,13) and UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPFDU.UslugaComplex_id,EPLDU.UslugaComplex_id,EPOU.UslugaComplex_id,EPCU.UslugaComplex_id)
				left join v_UslugaComplex PUC with (nolock) on EP.PrescriptionType_id = 11 and PUC.UslugaComplex_id = EPLD.UslugaComplex_id
				--10
				left join ObservTimeType OTT with (nolock) on EP.PrescriptionType_id = 10 and OTT.ObservTimeType_id = Obs.ObservTimeType_id
				left join v_EvnPrescrObservPos EPOP with (nolock) on EP.PrescriptionType_id = 10 and EPOP.EvnPrescr_id = EP.EvnPrescr_id
				left join ObservParamType OPT with (nolock) on EP.PrescriptionType_id = 10 and OPT.ObservParamType_id = EPOP.ObservParamType_id

				left join PrescriptionStatusType PST with (nolock) on PST.PrescriptionStatusType_id = coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id)
				{$addJoin}
			where
				EP.EvnPrescr_pid = :Evn_pid
				and coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,ECTD.EvnCourseTreatDrug_id,Obs.EvnPrescrObserv_id,UC.UslugaComplex_id,PUC.UslugaComplex_id) is not null
				{$filter}
			order by
				PT.PrescriptionType_id,
				isnull(ECT.EvnCourseTreat_id,ECP.EvnCourseProc_id),
				EP.EvnPrescr_id,
				coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT),
				Obs.EvnPrescrObserv_id
		";
		/*
		убрал, пока не используется, но может позже понадобиться
				left join v_EvnPrescrTreatDrug EPTD with (nolock) on EP.PrescriptionType_id = 5 and EPTD.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join PrescriptionIntroType PIT with (nolock) on EP.PrescriptionType_id = 5 and ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join PerformanceType PFT with (nolock) on  EP.PrescriptionType_id = 5 and ECT.PerformanceType_id = PFT.PerformanceType_id
				left join YesNo IsCito with (nolock) on IsCito.YesNo_id = coalesce(Regime.EvnPrescrRegime_IsCito,Diet.EvnPrescrDiet_IsCito,Obs.EvnPrescrObserv_IsCito,EP.EvnPrescr_IsCito,1)
		 */
		//echo '<pre>' . print_r(getDebugSQL($query, $queryParams), 1) . '</pre>'; exit;
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Не удалось запросить данные назначений!',400);
		}
		$response = $result->result('array');

		//обработка выборки
		$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
		$tmp_arr = array();
		foreach ($response as $row) {
			$type_id = $row['PrescriptionType_id'];
			$row_id = $row['EvnCoursePrescr_id'];
			$day_id = $row['EvnPrescrDay_id'];

			if (empty($tmp_arr[$type_id])) {
				//выбираем данные типа назначения
				$tmp_arr[$type_id] = array(
					'PrescriptionType_Name'=>$row['PrescriptionType_Name'],
					'rows'=>array(),
				);
			}
			$row['EvnPrescr_allTime'] = isset($row['EvnPrescr_allTime']) ? $row['EvnPrescr_allTime'] : '';
			if (empty($tmp_arr[$type_id]['rows'][$row_id])) {
				//выбираем данные для отображения в строке
				$tmp_arr[$type_id]['rows'][$row_id] = array(
					'EvnPrescr_Descr'=>$row['EvnPrescr_Descr'],
					'PrescriptionIntroType_Name'=>$row['PrescriptionIntroType_Name'],
					'allTime' => $row['EvnPrescr_allTime'],
					'days'=>array()
				);
			};
			
			$arr_time = explode(", ", $row['EvnPrescr_allTime']);
			$kol = count($arr_time);
			if ($kol == 0)
				$kol = 1;
			
			$tmp_arr[$type_id]['rows'][$row_id]['CountTimes'] = $kol;
			
			if (!$isPolka && empty($tmp_arr[$type_id]['rows'][$row_id]['days'][$day_id])) {
				//выбираем данные для отображения в ячейке дня
				$tmp_arr[$type_id]['rows'][$row_id]['days'][$day_id] = array(
					'DayNum'=>$row['DayNum'],
					'EvnPrescr_IsExec'=>$row['EvnPrescr_IsExec'],
					'pmUserPrescr_Name'=>$row['pmUserPrescr_Name'],
					'pmUserPrescr_FIO'=>$row['pmUserPrescr_FIO'],
					'PrescriptionStatusType_id'=>$row['PrescriptionStatusType_id'],
					'execTime' => $row['pmUserExec_Time2FIO'],
					'pmUserExec_Name'=>$row['pmUserExec_Name'],
					'pmUserExec_FIO'=>$row['pmUserExec_FIO'],
				);					
			}
	

			switch($type_id) {
				case 1;
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionRegimeType_Name'] = $row['PrescriptionRegimeType_Name'];
					break;
				case 2;
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionDietType_Code'] = $row['PrescriptionDietType_Code'];
					$tmp_arr[$type_id]['rows'][$row_id]['PrescriptionDietType_Name'] = $row['PrescriptionDietType_Name'];
					break;
				case 10;
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList']) ) {
						//Параметры наблюдения
						$tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'] = array();
					}
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'][$row['EvnPrescrObservPos_id']]) ) {
						$tmp_arr[$type_id]['rows'][$row_id]['ParamTypeList'][$row['EvnPrescrObservPos_id']] = $row['ObservParamType_Name'];
					}
					break;
				case 5;
					$tmp_arr[$type_id]['rows'][$row_id]['CountInDay'] = $row['CountInDay'];
					$tmp_arr[$type_id]['rows'][$row_id]['CourseDuration'] = $row['CourseDuration'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeP_Nick'] = $row['DurationTypeP_Nick'];
					$tmp_arr[$type_id]['rows'][$row_id]['ContReception'] = $row['ContReception'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeN_Nick'] = $row['DurationTypeN_Nick'];
					$tmp_arr[$type_id]['rows'][$row_id]['Interval'] = $row['Interval'];
					$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeI_Nick'] = $row['DurationTypeI_Nick'];
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['DrugList']) ) {
						//медикаменты
						$tmp_arr[$type_id]['rows'][$row_id]['DrugList'] = array();
					}
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['DrugList'][$row['EvnCourseTreatDrug_id']]) ) {
						$tmp_arr[$type_id]['rows'][$row_id]['DrugList'][$row['EvnCourseTreatDrug_id']] = array(
							'Drug_id'=>$row['Drug_id'],
							'Drug_Name'=>$row['Drug_Name'],
							'DrugTorg_Name'=>$row['DrugTorg_Name'],
							'DrugForm_Name'=>$row['DrugForm_Name'],
							'KolvoEd'=>$row['KolvoEd'],
							'Kolvo'=>$row['Kolvo'],
							'Okei_NationSymbol'=>$row['Okei_NationSymbol'],
						);
					}
					break;
				case 6;
				case 7;
				case 11;
				case 12;
				case 13;
					if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList']) ) {
						//услуги(а)
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'] = array();
					}
					if (6 == $type_id) {
						$tmp_arr[$type_id]['rows'][$row_id]['CountInDay'] = $row['CountInDay'];
						$tmp_arr[$type_id]['rows'][$row_id]['CourseDuration'] = $row['CourseDuration'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeP_Nick'] = $row['DurationTypeP_Nick'];
						$tmp_arr[$type_id]['rows'][$row_id]['ContReception'] = $row['ContReception'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeN_Nick'] = $row['DurationTypeN_Nick'];
						$tmp_arr[$type_id]['rows'][$row_id]['Interval'] = $row['Interval'];
						$tmp_arr[$type_id]['rows'][$row_id]['DurationTypeI_Nick'] = $row['DurationTypeI_Nick'];
						if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row_id]) ) {
							$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row_id] = array(
								'UslugaComplex_Code'=>$row['UslugaComplex_Code'],
								'UslugaComplex_Name'=>$row['UslugaComplex_Name'],
							);
						}
					} else {
						if ( empty($tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row['TableUsluga_id']]) ) {
							$tmp_arr[$type_id]['rows'][$row_id]['UslugaList'][$row['TableUsluga_id']] = array(
								'UslugaComplex_Code'=>$row['UslugaComplex_Code'],
								'UslugaComplex_Name'=>$row['UslugaComplex_Name'],
							);
						}
					}
					if (11 == $type_id) {
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaComplexP_Code'] = $row['UslugaComplexP_Code'];
						$tmp_arr[$type_id]['rows'][$row_id]['UslugaComplexP_Name'] = $row['UslugaComplexP_Name'];
					}
					break;
			}
		}
		//echo '<pre>' . print_r($tmp_arr, 1) . '</pre>'; exit;
		//дальше собираем данные для отображения
		$parse_data['ep_list'] = array();
		$defRow = array(
			'EvnPrescr_Name'=>null,
			//EvnPrescr_Day{[0-9]+} // содержание ячейки строки сестра
			//EvnPrescr_Day{[0-9]+}S // содержание ячейки строки сестра
		);
		if ($isPolka) {
			$lastRow = array();
		} else {
			$lastRow = array(
				'max_day' => 0,
				'EvnPrescr_begDate' => $queryParams['EvnPrescr_begDate']
			);
		}
		foreach ($tmp_arr as $type_id => $type_data) {
			foreach ($type_data['rows'] as $row_data ) {
				$ep_data = $defRow;
				$ep_data['type_id'] = $type_id;
				$ep_data['CountTimes'] = $row_data['CountTimes'];
				$ep_data['allTime'] = $row_data['allTime'];
				//$ep_data['times'] = $row_data['times'];
				//print_r($ep_data['times'], 1); exit;
				//echo '<pre>' . print_r($row_data['times']['09:00'], 1) . '</pre>'; //exit;
				
				foreach ($row_data['days'] as $day_data ) {
					if($day_data['DayNum'] > $lastRow['max_day'])
					{
						$lastRow['max_day'] = $day_data['DayNum'];
					}
					$caption = 'EvnPrescr_Day'. $day_data['DayNum'];
					$ep_data[$caption] = $day_data['pmUserPrescr_Name'];
					$ep_data[$caption.'_FIO'] = $day_data['pmUserPrescr_FIO'];
					$ep_data[$caption.'S'] = '';
					$ep_data[$caption.'Exec'] = '';
					//$ep_data('times') = 
					$ep_data[$caption.'Exec'] = $day_data['execTime'];
					if($day_data['EvnPrescr_IsExec'] == 2) {
						//echo $caption .'<br>';
						$ep_data[$caption.'S'] = $day_data['pmUserExec_Name'];
						$ep_data[$caption.'S_FIO'] = $day_data['pmUserExec_FIO'];
						if (!$ep_data[$caption.'Exec'])
								$ep_data[$caption.'Exec'] = '_' .$day_data['pmUserExec_FIO'] .'_' . $day_data['pmUserExec_Name'];
								
					}
					
					
				}

				//формируем столбец EvnPrescr_Name
				if ($regionNick == 'ufa' && $type_id == 5) 
					$ep_data['EvnPrescr_Name'] = '';
				else 
					$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'. $type_data['PrescriptionType_Name'] .'</div>';
				switch($type_id) {
					case 1;
						$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'.$row_data['PrescriptionRegimeType_Name'] .' режим</div>';
						break;
					case 2;
						if ($regionNick == 'ufa') {
							$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">'.$row_data['PrescriptionDietType_Name'] .'</div>';
						} else {
							$ep_data['EvnPrescr_Name'] = '<div style="font-weight: bold;">Диета №'.$row_data['PrescriptionDietType_Code'] .'</div>';
						}
						break;
					case 10;
						$ep_data['EvnPrescr_Name'] .= "<div style='text-decoration: underline;'>Параметры наблюдения:</div>";
						$i = 1;
						foreach ( $row_data['ParamTypeList'] as $name ) {
							if ( $i % 3 == 1 ) {
								$ep_data['EvnPrescr_Name'] .= '<div>';
							}
							$ep_data['EvnPrescr_Name'] .= htmlspecialchars($name) .', ';
							$i++;
							if ( $i % 3 == 1 ) {
								$ep_data['EvnPrescr_Name'] .= '</div>';
							}
						}
						//$row_data['TimeTypeList']
						break;
					case 5;
						$drug_list = array();
						$cnt = 0;
						foreach ( $row_data['DrugList'] as $drug_data ) {
							$cnt += 1;
							if ($drug_data['Drug_id'])
								//  Если выписано то торговому наименованию
								$name = $drug_data['DrugTorg_Name'];
							else {
								$name = $drug_data['Drug_Name'];							
								if (empty($name)) {
									$name = $drug_data['DrugTorg_Name'];
								}
							}
							if ($regionNick == 'ufa')
								$name = $cnt .') ' .$name;
							$i = '<b>'.$name.'</b>';
							$DrugForm_Nick = $this->EvnPrescrTreat_model->getDrugFormNick($drug_data['DrugForm_Name'], $drug_data['Drug_Name']);
							if ($regionNick != 'ufa') {
								if ( !empty($drug_data['KolvoEd']))
									$i .=  ' По '. htmlspecialchars($drug_data['KolvoEd']) .' '.(empty($DrugForm_Nick)?'ед.дозировки':$DrugForm_Nick);
								if ( !empty($drug_data['Kolvo']) && empty($drug_data['KolvoEd']) )
									$i .=  htmlspecialchars($drug_data['Kolvo']) .' ';
								if ( !empty($drug_data['Okei_NationSymbol']) && empty($drug_data['KolvoEd']) )
									$i .=  htmlspecialchars($drug_data['Okei_NationSymbol']);
							}
							else {
								// Региональные изменения для Уфы https://redmine.swan.perm.ru/issues/136715
								if ( !empty($drug_data['Kolvo']))
									$i .= ' '  .htmlspecialchars($drug_data['Kolvo']) .' ';
								if ( !empty($drug_data['Okei_NationSymbol']))
									$i .=  htmlspecialchars($drug_data['Okei_NationSymbol']);
							}
							
							$drug_list[]=$i;
						}
						$ep_data['EvnPrescr_Name'] .= '<div>';
						$ep_data['EvnPrescr_Name'] .= implode(',<br>',$drug_list);
						if ( !empty($row_data['CountInDay']))
							//$ep_data['EvnPrescr_Name'] .=  '<br>'.htmlspecialchars($row_data['CountInDay']) .'&nbsp;'.(in_array($row_data['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
							$ep_data['EvnPrescr_Name'] =  htmlspecialchars($row_data['CountInDay'])
								.'&nbsp;'.(in_array($row_data['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки (' .$row_data['PrescriptionIntroType_Name'] .'):'
									.'<br>' .$ep_data['EvnPrescr_Name'];
						if ($regionNick != 'ufa') { 
							// Региональные изменения для Уфы https://redmine.swan.perm.ru/issues/136715
							if ( !empty($row_data['ContReception']))
								$ep_data['EvnPrescr_Name'] .=  ', принимать '. htmlspecialchars($row_data['ContReception']) .' '. htmlspecialchars($row_data['DurationTypeN_Nick']);
							if ( !empty($row_data['Interval']))
								$ep_data['EvnPrescr_Name'] .=  ', перерыв '. htmlspecialchars($row_data['Interval']) .' '. htmlspecialchars($row_data['DurationTypeI_Nick']);
							if ( !empty($row_data['CourseDuration']) && $row_data['CourseDuration'] != $row_data['ContReception'] )
								$ep_data['EvnPrescr_Name'] .=  ', в течение '. htmlspecialchars($row_data['CourseDuration']) .' '. htmlspecialchars($row_data['DurationTypeP_Nick']);
							$ep_data['EvnPrescr_Name'] .=  '.';
							$ep_data['EvnPrescr_Name'] .= '</div>';
						}
						break;
					case 6;
					case 7;
					case 11;
					case 12;
					case 13;
						$usluga_list = array();
						if ($this->options['prescription']['enable_show_service_code']) {
							$usluga_tpl = '{UslugaComplex_Code} {UslugaComplex_Name}';
						} else {
							$usluga_tpl = '{UslugaComplex_Name}';
						}
						if (11 == $type_id) {
							$usluga_list[] = strtr($usluga_tpl, array(
								'{UslugaComplex_Code}'=>$row_data['UslugaComplexP_Code'],
								'{UslugaComplex_Name}'=>$row_data['UslugaComplexP_Name'],
							));
						} else {
							//пока состав лаб.услуги не будем отображать, т.к. это не надо было
							foreach ( $row_data['UslugaList'] as $usluga_data ) {
								$usluga_list[] = strtr($usluga_tpl, array(
									'{UslugaComplex_Code}'=>$usluga_data['UslugaComplex_Code'],
									'{UslugaComplex_Name}'=>$usluga_data['UslugaComplex_Name'],
								));
							}
						}
						$ep_data['EvnPrescr_Name'] .= '<div>';
						$ep_data['EvnPrescr_Name'] .= implode('<br />',$usluga_list);
						if (6 == $type_id) {
							if ( !empty($row_data['CountInDay']))
								$ep_data['EvnPrescr_Name'] .=  ' '.htmlspecialchars($row_data['CountInDay']) .'&nbsp;'.(in_array($row_data['CountInDay'],array(2,3,4))?'раза':'раз').' в сутки';
							if ( !empty($row_data['ContReception']))
								$ep_data['EvnPrescr_Name'] .=  ', повторять непрерывно '. htmlspecialchars($row_data['ContReception']) .' '. htmlspecialchars($row_data['DurationTypeN_Nick']);
							if ( !empty($row_data['Interval']))
								$ep_data['EvnPrescr_Name'] .=  ', перерыв '. htmlspecialchars($row_data['Interval']) .' '. htmlspecialchars($row_data['DurationTypeI_Nick']);
							if ( !empty($row_data['CourseDuration']) && $row_data['CourseDuration'] != $row_data['ContReception'] )
								$ep_data['EvnPrescr_Name'] .=  ', всего '. htmlspecialchars($row_data['CourseDuration']) .' '. htmlspecialchars($row_data['DurationTypeP_Nick']);
						}
						$ep_data['EvnPrescr_Name'] .=  '.';
						$ep_data['EvnPrescr_Name'] .= '</div>';
						break;
				}
				if ( !empty($row_data['EvnPrescr_Descr']) )
				{
					// картинка в pdf не отображается <img src="/img/icons/comment16.png" />&nbsp;
					
					if ($regionNick != 'ufa') {
						$ep_data['EvnPrescr_Name'] .= '<div>' .htmlspecialchars($row_data['EvnPrescr_Descr']) .'</div>';
					}
					else {
						$ep_data['EvnPrescr_Name'] .= '<div>'. '<font style="text-decoration: underline;">Комментарий:</font> ' .htmlspecialchars($row_data['EvnPrescr_Descr']) .'</div>';
					}
				}
				//echo '<pre>' . print_r($ep_data, 1) . '</pre>'; exit;
				$parse_data['ep_list'][] = $ep_data;
			}
		}
		unset($tmp_arr);
		$parse_data['ep_list'][] = $lastRow;
		//var_dump($parse_data); exit;
		return $parse_data;
	}

	/**
	 * Получение данных для шаблона печати "Единое направление на лабораторные исследования"
	 * Имя шаблона: ufa_print_labdirection
	 */
	public function doloadLabDirectionList($data) {
		$queryParams = [];
		$where = [];

		if(!empty($data['Evn_id'])) {
			$queryParams['Evn_id'] = $data['Evn_id'];
			$where[] = 'ed.EvnDirection_pid = :Evn_id';
			$where[] = 'TimetableMedService_begTime > getdate()';
		} else {
			$where[] = 'TimetableMedService_begTime > cast(getdate() as date)';
			$where[] = "elr.EvnStatus_id = 1";
		}

		if(!empty($data['Person_id'])) {
			$queryParams['Person_id'] = $data['Person_id'];
			$where[] = 'ed.Person_id = :Person_id';
		}

		if(!empty($data['Lpu_id'])) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$where[] = 'ed.Lpu_did = :Lpu_id';
		}

		$where = implode(' and ', $where);

		$query = "
			select
				ROW_NUMBER() OVER(ORDER BY ttms.TimetableMedService_begTime) as row_number,
				format(ttms.TimetableMedService_begTime, N'dd.MM.yyy HH&#058;mm') as TimetableMedService_begTime,
				ms.MedService_Name,
				ad.Address_Address as Address,
				eup_usluga.UslugaComplex_Name
			from v_EvnDirection_all (nolock) ed
			inner join v_EvnLabRequest (nolock) elr on elr.EvnDirection_id = ed.EvnDirection_id
			left join v_TimeTableMedService ttms (nolock) on ttms.EvnDirection_id = elr.EvnDirection_id
			left join v_MedService (nolock) ms on ms.MedService_id = ttms.MedService_id
			left join v_Address (nolock) ad on ad.Address_id = ms.Address_id
			outer apply (
				select STUFF((select
					'; ' + uc.UslugaComplex_Name
				from dbo.v_EvnUslugaPar EUP (nolock)
				left join dbo.v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = EUP.UslugaComplex_id
				where EUP.EvnDirection_id = ed.EvnDirection_id
				for xml path('')), 1, 1, '') as UslugaComplex_Name
			) as eup_usluga
			where {$where} and ed.EvnClass_id = 27 
			order by ttms.TimetableMedService_begTime
		";
		
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$response = $result->result('array');
		} else {
			throw new Exception('Не удалось запросить данные!', 400);
		}
		if (empty($response)) {
			throw new Exception('Отсутствуют данные для вывода на печать!', 400);
		}

		return $response;
	}

	/**
	 * Получение данных для шаблона печати "Единое направление на лабораторные исследования"
	 * Имя шаблона: ufa_print_labdirection
	 */
	public function doloadPatientInfo($data) {
		$queryParams = [];
		$where = [];
		$join = '';

		if(!empty($data['Evn_id'])) {
			$queryParams['Evn_id'] = $data['Evn_id'];
			$where[] = 'evn.Evn_id = :Evn_id';
			$join .= 'inner join v_Evn (nolock) evn on ps.Person_id = evn.Person_id';
		}

		if(!empty($data['Person_id'])) {
			$queryParams['Person_id'] = $data['Person_id'];
			$where[] = 'ps.Person_id = :Person_id';
		}

		$where = implode(' and ', $where);

		$query = "
			select
				ps.Person_id as Person_id,
				convert(varchar, ps.Person_BirthDay, 104) as Person_Birthday,
				ISNULL(ps.Person_SurName, '') + ' ' + ISNULL(ps.Person_FirName, '') + ' ' + ISNULL(ps.Person_SecName, '') as Person_FIO
			from v_PersonState (nolock) ps
			{$join}
			where {$where}
		";
		
		$result = $this->db->query($query, $queryParams)->first_row();
		if (is_object($result)) {
			$response = $result;
		} else {
			throw new Exception('Не удалось запросить данные о пациенте!', 400);
		}
		if (empty($response)) {
			throw new Exception('Не удалось получить данные о пациенте!', 400);
		}

		return $response;
	}

	/**
	 * Получение данных для шаблона печати "Единое направление на лабораторные исследования"
	 * Имя шаблона: ufa_print_labdirection
	 */
	public function doloadMoInfo($data) {
		$queryParams = [];
		$where = [];
		$join = '';

		if(!empty($data['Evn_id'])) {
			$queryParams['Evn_id'] = $data['Evn_id'];
			$where[] = 'evn.Evn_id = :Evn_id';
			$join .= 'inner join v_Evn (nolock) evn on lpu.Lpu_id = evn.Lpu_id';
		}

		if(!empty($data['Lpu_id'])) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$where[] = 'lpu.Lpu_id = :Lpu_id';
		}

		$where = implode(' and ', $where);

		$query = "
			select top 1
				org.Org_Name,
				ad.Address_Address as Org_Address
			from v_Lpu (nolock) lpu
			left join v_Org (nolock) org on org.Org_id = lpu.Org_id
			left join v_Address (nolock) ad on ad.Address_id = org.UAddress_id
			{$join}
			where {$where}
		";
		
		$result = $this->db->query($query, $queryParams)->first_row();
		if (is_object($result)) {
			$response = $result;
		} else {
			throw new Exception('Не удалось запросить данные о МО!', 400);
		}
		if (empty($response)) {
			throw new Exception('Не удалось получить данные о МО!', 400);
		}

		return $response;
	}
	
	
	
}
