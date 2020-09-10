<?php
/**
* Kareliya_User_model - модель для работы с учетными записями пользователей (Карелия)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      17.01.2014
*/

require_once(APPPATH.'models/User_model.php');

class Kareliya_User_model extends User_model {
    /**
     * Конструктор
     */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Дополнительное условие для отображения АРМ приемного отделения
	 * @task https://redmine.swan.perm.ru/issues/30589
	 */
	function getStacPriemAdditionalCondition($data = array()) {
		return (is_array($data) && !empty($data['LpuSectionProfile_Code']) && $data['LpuSectionProfile_Code'] == 160);
	}

	/**
	 * Возвращает список мест работы врача
	 */
	function getUserMedStaffFactList($data) {
		if ($data['session']['orgtype']!='lpu') { // если это не ЛПУ 
			$filter = '';
			$params = array('Org_id'=>$data['session']['org_id'],'pmUser_id'=>$data['pmUser_id'],'pmUser_Name'=>toAnsi($data['session']['user']));
			if ($data['MedService_id']>0) {
				$params['MedService_id'] = $data['MedService_id'];
				$filter = ' and MS.MedService_id = :MedService_id';
			}

			if (havingGroup('orgadmin')) {
				//Админу организации доступны все службы его организации
				$sql = " 
					SELECT
						null as MedStaffFact_id,
						MS.LpuSection_id,
						:pmUser_id as MedPersonal_id,
						null as LpuSection_Name,
						null as LpuSection_Nick,
						null as PostMed_Name,
						null as PostMed_Code,
						null as PostMed_id,
						null as LpuBuilding_id,
						null as LpuBuilding_Name,
						null as LpuUnit_id,
						null as LpuUnitSet_id,
						null as LpuUnit_Name,
						null as Timetable_isExists, 
						null as LpuUnitType_SysNick,
						MS.LpuUnitType_id,
						null as LpuSectionProfile_SysNick,
						null as LpuSectionProfile_Code,
						null as LpuSectionProfile_id,
						MS.MedService_id,
						MS.MedService_Nick,
						MS.MedService_Name,
						MS.MedServiceType_id,
						mst.MedServiceType_SysNick, 
						ms.MedService_IsExternal,
						:pmUser_Name as MedPersonal_FIO,
						Org.Org_id,
						null as Lpu_id,
						Org.Org_Nick,
						null as Lpu_Nick,
						null as MedicalCareKind_id,
						null as PostKind_id,
					    null as SmpUnitType_Code
					FROM 
						v_MedService MS with (NOLOCK)
						left join v_Org Org with (nolock) on Org.Org_id = MS.Org_id
						left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = MS.MedServiceType_id
					where
						MS.Org_id = :Org_id 
						and MS.MedService_begDT <= @date and (MS.MedService_endDT >= @date or MS.MedService_endDT is null) 
						{$filter}
				";
			} else {
				$sql = " 
					SELECT
						null as MedStaffFact_id,
						MS.LpuSection_id,
						:pmUser_id as MedPersonal_id,
						null as LpuSection_Name,
						null as LpuSection_Nick,
						null as PostMed_Name,
						null as PostMed_Code,
						null as PostMed_id,
						null as LpuBuilding_id,
						null as LpuBuilding_Name,
						null as LpuUnit_id,
						null as LpuUnitSet_id,
						null as LpuUnit_Name,
						null as Timetable_isExists, 
						null as LpuUnitType_SysNick,
						MS.LpuUnitType_id,
						null as LpuSectionProfile_SysNick,
						null as LpuSectionProfile_Code,
						null as LpuSectionProfile_id,
						MS.MedService_id,
						MS.MedService_Nick,
						MS.MedService_Name,
						MS.MedServiceType_id,
						mst.MedServiceType_SysNick, 
						ms.MedService_IsExternal,
						:pmUser_Name as MedPersonal_FIO,
						Org.Org_id,
						null as Lpu_id,
						Org.Org_Nick,
						null as Lpu_Nick,
						null as MedicalCareKind_id,
						null as PostKind_id,
					    null as SmpUnitType_Code
					FROM 
						v_pmUserCacheOrg PUO with(nolock)
						inner join v_PersonWork PW with(nolock) on PW.pmUserCacheOrg_id = PUO.pmUserCacheOrg_id
						inner join v_Org Org with (nolock) on Org.Org_id = PW.Org_id
						left join v_OrgStruct OS with(nolock) on OS.OrgStruct_id = PW.OrgStruct_id
						inner join v_MedService MS with(nolock) on MS.Org_id = Org.Org_id and isnull(MS.OrgStruct_id,0) = isnull(OS.OrgStruct_id,0)
						left join v_MedServiceType MST with (nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					where
						PUO.Org_id = :Org_id
						and PUO.pmUserCache_id = :pmUser_id
						and MS.MedService_begDT <= @date and (MS.MedService_endDT >= @date or MS.MedService_endDT is null)
						{$filter}
				";
			}
		} else {
			
			$filter_medstafffact = '';
			$filter_medservicemedpersonal = '';
			$filter_medservice = '';
			$use_date = true;
			if ($use_date) {
				$filter_medstafffact = 'and cast(msf.WorkData_begDate as date) <= @date and (cast(msf.WorkData_endDate as date) >= @date or msf.WorkData_endDate is null)';
				$filter_medservicemedpersonal = 'and (cast(msmp.MedServiceMedPersonal_begDT as date) <= @date and (cast(msmp.MedServiceMedPersonal_endDT as date) >= @date or msmp.MedServiceMedPersonal_endDT is null))';
				$filter_medservice = 'and cast(MS.MedService_begDT as date) <= @date and (cast(MS.MedService_endDT as date) >= @date or MS.MedService_endDT is null)';
			}
			$filter = '';
			$params = array('MedPersonal_id'=>$data['MedPersonal_id'],'Lpu_id'=>$data['Lpu_id'],'pmUser_id'=>$data['pmUser_id'],'pmUser_Name'=>toAnsi($data['session']['user']));
			if ($data['MedService_id']>0) {
				$params['MedService_id'] = $data['MedService_id'];
				$filter = ' and MedService_id = :MedService_id';
			} elseif ($data['MedStaffFact_id']>0) {
				$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
				$filter = ' and msf.MedStaffFact_id = :MedStaffFact_id';
				if ($data['LpuSection_id']>0) { // если передано отделение, то фильтруем и по отделению
					$params['LpuSection_id'] = $data['LpuSection_id'];
					$filter .= ' and ls.LpuSection_id = :LpuSection_id';
				}
			}

			$filter_lpusection = '';
			if (isset($data['LpuSection_id']) && $data['LpuSection_id']>0) { // если передано отделение, то фильтруем и по отделению
				$params['LpuSection_id'] = $data['LpuSection_id'];
				$filter_lpusection = ' and ls.LpuSection_id = :LpuSection_id';
			}

			$persisFields = '
				,msf.MedicalCareKind_id
				,msf.PostKind_id
			';

			$farmacy_filter = "(1=0)";
			if (isset($_SESSION['OrgFarmacy_id'])) {
				$farmacy_filter = " exists(Select OrgFarmacy_id from v_OrgFarmacy with (nolock) where OrgFarmacy_id = :Lpu_id) ";
			}
			
			$sql_medstafffact = "
				-- места работы 
				SELECT
					msf.MedStaffFact_id,
					msf.LpuSection_id,
					msf.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					isnull(ps.PostMed_Name,'') as PostMed_Name,
					ps.PostMed_Code,
					ps.PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					case 
						when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') then 
							case when (select count(*) from v_TimetableGraf_lite tt with (nolock) where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick = 'parka' then 
							case when (select count(*) from v_TimetablePar tt with (nolock) where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac') then 
							case when (select count(*) from v_TimetableStac_lite tt with (nolock) where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						else 'false'				
					end as Timetable_isExists,
					--case when ps.PostMed_code = '10002' or ps.PostMed_code = '6' then 2 else 1 end as mp_is_zav,
					--case when (select count(*) from v_MedStaffRegion msr where msf.MedPersonal_id = msr.MedPersonal_id and msr.Lpu_id = msf.Lpu_id) > 0 then 2 else 1 end as mp_is_uch,
					lut.LpuUnitType_SysNick,
					lu.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					null as MedService_id,
					null as MedService_Nick,
					null as MedService_Name,
					null as MedServiceType_id,
					null as MedServiceType_SysNick, 
					msf.Person_FIO as MedPersonal_FIO, 
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					null as MedStaffFactLink_id,
					null as MedStaffFactLink_begDT,
					null as MedStaffFactLink_endDT,
					msf.MedStaffFactCache_IsDisableInDoc,
					eq.ElectronicQueueInfo_id,
					eq.ElectronicService_id,
					eq.ElectronicService_Num,
					eq.ElectronicQueueInfo_CallTimeSec,
					eq.ElectronicQueueInfo_PersCallDelTimeMin,
					eq.ElectronicQueueInfo_CallCount,
					eq.ElectronicService_isShownET,
					STUFF(CAST((
						select ISNULL(CAST(etl.ElectronicTreatment_id as VARCHAR),'') + ',' as 'data()'
						from v_ElectronicTreatmentLink etl with (nolock)
							inner join v_ElectronicQueueInfo eqio with (nolock) on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
							inner join v_ElectronicService eso with (nolock) on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
						where eso.ElectronicService_id = eq.ElectronicService_id
						for xml path(''), TYPE) AS VARCHAR(MAX)), 1, 1, ''
					) as ElectronicTreatment_ids,
					eboard.ElectronicScoreboard_id,
					eboard.ElectronicScoreboard_IPaddress,
					eboard.ElectronicScoreboard_Port,
					null as SmpUnitType_Code,
					null as Storage_id,
					null as Storage_pid
					" . $persisFields . "
				FROM
					v_MedStaffFact msf with (nolock)
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
					outer apply (
						select top 1
							eqi.ElectronicQueueInfo_id,
							mseq.ElectronicService_id,
							es.ElectronicService_Num,
							eqi.ElectronicQueueInfo_CallTimeSec,
							eqi.ElectronicQueueInfo_PersCallDelTimeMin,
							eqi.ElectronicQueueInfo_CallCount,
							case when es.ElectronicService_isShownET = 2 then 1 else null end as ElectronicService_isShownET
						from
							v_MedServiceElectronicQueue mseq (nolock)
							left join v_ElectronicService es with(nolock) on es.ElectronicService_id = mseq.ElectronicService_id
							left join v_ElectronicQueueInfo eqi with(nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
							left join v_ElectronicScoreboardQueueLink esql with(nolock) on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
						where
							 mseq.MedStaffFact_id = msf.MedStaffFact_id
			    			 and eqi.ElectronicQueueInfo_IsOff = 1
					) eq
					outer apply (
						select top 1
							ebd.ElectronicScoreboard_id,
							ebd.ElectronicScoreboard_IPaddress,
							ebd.ElectronicScoreboard_Port
						from v_ElectronicScoreboard ebd with(nolock)
						left join v_ElectronicScoreboardQueueLink esql with(nolock) on esql.ElectronicService_id = eq.ElectronicService_id
						where
							ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
							and ebd.ElectronicScoreboard_IsLED = 2
					) as eboard
				WHERE
					msf.MedPersonal_id = :MedPersonal_id and msf.Lpu_id = :Lpu_id
					and msf.MedStaffFact_Stavka > 0
					{$filter_medstafffact} {$filter}
				";
			$sql_workgraph = "
			SELECT
					msf.MedStaffFact_id,
					ls.LpuSection_id,
					msf.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					isnull(ps.PostMed_Name,'') as PostMed_Name,
					ps.PostMed_Code,
					ps.PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					case
						when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') then
							case when (select count(*) from v_TimetableGraf_lite tt with (nolock) where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick = 'parka' then
							case when (select count(*) from v_TimetablePar tt with (nolock) where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac') then
							case when (select count(*) from v_TimetableStac_lite tt with (nolock) where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						else 'false'
					end as Timetable_isExists,
					--case when ps.PostMed_code = '10002' or ps.PostMed_code = '6' then 2 else 1 end as mp_is_zav,
					--case when (select count(*) from v_MedStaffRegion msr where msf.MedPersonal_id = msr.MedPersonal_id and msr.Lpu_id = msf.Lpu_id) > 0 then 2 else 1 end as mp_is_uch,
					lut.LpuUnitType_SysNick,
					lu.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					null as MedService_id,
					null as MedService_Nick,
					null as MedService_Name,
					null as MedServiceType_id,
					null as MedServiceType_SysNick,
					msf.Person_FIO as MedPersonal_FIO,
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					null as MedStaffFactLink_id,
					null as MedStaffFactLink_begDT,
					null as MedStaffFactLink_endDT,
					msf.MedStaffFactCache_IsDisableInDoc,
					null as ElectronicQueueInfo_id,
					null as ElectronicService_id,
					null as ElectronicService_Num,
					null as ElectronicQueueInfo_CallTimeSec,
					null as ElectronicQueueInfo_PersCallDelTimeMin,
					null as ElectronicQueueInfo_CallCount,
					null as ElectronicService_isShownET,
					'' as ElectronicTreatment_ids,
					null as ElectronicScoreboard_id,
					null as ElectronicScoreboard_IPaddress,
					null as ElectronicScoreboard_Port,
					null as Storage_id,
					null as Storage_pid,
			       	null as SmpUnitType_Code
					" . $persisFields . "
				FROM
					v_MedStaffFact msf with (nolock)
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = msf.Lpu_id

					inner join v_WorkGraph WG on (
						WG.MedStaffFact_id = msf.MedStaffFact_id and
						(
							CAST(WG.WorkGraph_begDT as date) <= @date
							and CAST(WG.WorkGraph_endDT as date) >= @date
						)
					)
					left join v_WorkGraphLpuSection WGLS on WGLS.WorkGraph_id = WG.WorkGraph_id
					--left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = WGLS.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id

					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
				WHERE
					msf.MedPersonal_id = :MedPersonal_id and msf.Lpu_id = :Lpu_id
					{$filter}
			";
			$sql_medservice = "
				-- сотрудники служб 
				SELECT
					case 
						when mst.MedServiceType_SysNick = 'reanimation' then (
							select top 1 t1.MedStaffFact_id 
							from v_MedStaffFact t1 with (nolock)
								inner join dbo.v_LpuUnit t2 on t2.LpuUnit_id = t1.LpuUnit_id
							where t1.MedPersonal_id = msmp.MedPersonal_id 
								and t1.WorkData_endDate is null
								and t2.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac','priem')
						) else null 
					end as MedStaffFact_id,
					MS.LpuSection_id,
					msmp.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					null as PostMed_Name,
					null as PostMed_Code,
					null as PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					null as Timetable_isExists, 
					lut.LpuUnitType_SysNick,
					MS.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					MS.MedService_id,
					MS.MedService_Nick,
					MS.MedService_Name,
					MS.MedServiceType_id,
					mst.MedServiceType_SysNick, 
					msmp.Person_FIO as MedPersonal_FIO, 
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					null as MedStaffFactLink_id,
					null as MedStaffFactLink_begDT,
					null as MedStaffFactLink_endDT,
					null as MedStaffFactCache_IsDisableInDoc,
					eq.ElectronicQueueInfo_id,
					eq.ElectronicService_id,
					eq.ElectronicService_Num,
					eq.ElectronicQueueInfo_CallTimeSec,
					eq.ElectronicQueueInfo_PersCallDelTimeMin,
					eq.ElectronicQueueInfo_CallCount,
					eq.ElectronicService_isShownET,
					STUFF(CAST((
						select ISNULL(CAST(etl.ElectronicTreatment_id as VARCHAR),'') + ',' as 'data()'
						from v_ElectronicTreatmentLink etl with (nolock)
							inner join v_ElectronicQueueInfo eqio with (nolock) on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
							inner join v_ElectronicService eso with (nolock) on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
						where eso.ElectronicService_id = eq.ElectronicService_id
						for xml path(''), TYPE) AS VARCHAR(MAX)), 1, 1, ''
					) as ElectronicTreatment_ids,
					eboard.ElectronicScoreboard_id,
					eboard.ElectronicScoreboard_IPaddress,
					eboard.ElectronicScoreboard_Port,
					sut.SmpUnitType_Code,
					strg.Storage_id,
					strg.Storage_pid,
					null as MedicalCareKind_id,
					msf.PostKind_id
				FROM 
					v_MedService MS with (NOLOCK)
					cross apply (
						Select top 1 msmp.MedPersonal_id, Person_Fio from v_MedServiceMedPersonal msmp with (NOLOCK)
						left join v_MedPersonal mp with (NOLOCK) on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = MS.Lpu_id
						where msmp.MedService_id = MS.MedService_id
						and msmp.MedPersonal_id = :MedPersonal_id
						{$filter_medservicemedpersonal}
					) as msmp
					outer apply (
						select top 1
							msf.PostKind_id
						from
							v_MedStaffFact msf (nolock)
						where
							msf.MedPersonal_id = msmp.MedPersonal_id
							and msf.LpuSection_id = ms.LpuSection_id
					) msf
					outer apply (
						select top 1
							eqi.ElectronicQueueInfo_id,
							mseq.ElectronicService_id,
							es.ElectronicService_Num,
							eqi.ElectronicQueueInfo_CallTimeSec,
							eqi.ElectronicQueueInfo_PersCallDelTimeMin,
							eqi.ElectronicQueueInfo_CallCount,
							case when es.ElectronicService_isShownET = 2 then 1 else null end as ElectronicService_isShownET
						from
							v_MedServiceElectronicQueue mseq (nolock)
							left join v_MedServiceMedPersonal msmp2 with(nolock) on msmp2.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
							left join v_ElectronicService es with(nolock) on es.ElectronicService_id = mseq.ElectronicService_id
							left join v_ElectronicQueueInfo eqi with(nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
							left join v_ElectronicScoreboardQueueLink esql with(nolock) on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
						where
							 msmp2.MedPersonal_id = msmp.MedPersonal_id
							 and msmp2.MedService_id = MS.MedService_id
			    			 and eqi.ElectronicQueueInfo_IsOff = 1
					) eq
					outer apply (
						select top 1
							ebd.ElectronicScoreboard_id,
							ebd.ElectronicScoreboard_IPaddress,
							ebd.ElectronicScoreboard_Port
						from v_ElectronicScoreboard ebd with(nolock)
						left join v_ElectronicScoreboardQueueLink esql with(nolock) on esql.ElectronicService_id = eq.ElectronicService_id
						where
							ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
							and ebd.ElectronicScoreboard_IsLED = 2
					) as eboard
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = MS.Lpu_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = MS.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = isnull(ls.LpuBuilding_id,MS.LpuBuilding_id)
					outer apply (
						select top 1 *
						from v_SmpUnitParam sup (nolock)
						where sup.LpuBuilding_id = lb.LpuBuilding_id
						order by sup.SmpUnitParam_id desc
					) sup
					left join v_SmpUnitType sut with (nolock) on sut.SmpUnitType_id = sup.SmpUnitType_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = isnull(ls.LpuUnit_id,MS.LpuUnit_id)
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = isnull(lu.LpuUnitType_id,MS.LpuUnitType_id)
					left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = MS.MedServiceType_id
					outer apply (
						select top 1						
							i_s.Storage_id,
							i_s.Storage_pid
						from
							v_StorageStructLevel i_ssl with (nolock)
							left join v_Storage i_s with (nolock) on i_s.Storage_id = i_ssl.Storage_id
						where
							i_ssl.MedService_id = MS.MedService_id
						order by
							i_ssl.StorageStructLevel_id
					) strg
				where
					MS.Lpu_id = :Lpu_id and
					(1=1)
					{$filter_medservice} {$filter}
					and msmp.MedPersonal_id = :MedPersonal_id
					and mst.MedServiceType_SysNick in ('HTM', 'vk', 'mse', 'lab', 'pzm', 'func', 'patb', 'mstat', 'prock', 'dpoint', 'merch', 'pmllo', 'regpol', 'sprst', 'okadr', 'minzdravdlo', 'leadermo', 'mekllo', 'spesexpertllo', 'adminllo', 'touz', 'reglab', 'oper_block', 'smp', 'slneotl', 'konsult', 'foodserv', 'vac', 'epidem_mo', 'remoteconsultcenter','smpdispatchstation','forenbiodprtwithmolgenlab','forenchemdprt','medforendprt','forenhistdprt','organmethdprt','forenmedcorpsexpdprt','forenmedexppersdprt','commcomplexp','forenareadprt', 'lvn', 'smpheaddoctor', 'zmk', 'rpo','spec_mz','reanimation', 'profosmotr', 'microbiolab')
			";

			$sql_medstafffact_linked = "
				-- связанные места работы 
				SELECT
					msf.MedStaffFact_id,
					msf.LpuSection_id,
					msf.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					isnull(ps.PostMed_Name,'') as PostMed_Name,
					ps.PostMed_Code,
					ps.PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					case
						when (select count(*) from v_TimetableGraf_lite tt with (nolock) where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true'
						else 'false'
					end as Timetable_isExists,
					lut.LpuUnitType_SysNick,
					lu.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					null as MedService_id,
					null as MedService_Nick,
					null as MedService_Name,
					null as MedServiceType_id,
					null as MedServiceType_SysNick, 
					msf.Person_FIO as MedPersonal_FIO, 
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					msfl.MedStaffFactLink_id,
					convert(varchar(10), msfl.MedStaffFactLink_begDT, 104) as MedStaffFactLink_begDT,
					convert(varchar(10), msfl.MedStaffFactLink_endDT, 104) as MedStaffFactLink_endDT,
					msf.MedStaffFactCache_IsDisableInDoc,
					null as ElectronicQueueInfo_id,
					null as ElectronicService_id,
					null as ElectronicService_Num,
					null as ElectronicQueueInfo_CallTimeSec,
					null as ElectronicQueueInfo_PersCallDelTimeMin,
					null as ElectronicQueueInfo_CallCount,
					null as ElectronicService_isShownET,
					'' as ElectronicTreatment_ids,
					null as ElectronicScoreboard_id,
					null as ElectronicScoreboard_IPaddress,
					null as ElectronicScoreboard_Port,
					null as Storage_id,
					null as Storage_pid,
					null as SmpUnitType_Code
					" . $persisFields . "
				FROM
					v_MedStaffFactLink msfl with (nolock)
					inner join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = msfl.MedStaffFact_id
					inner join v_MedStaffFact mmsf with (nolock) on mmsf.MedStaffFact_id = msfl.MedStaffFact_sid
					left join v_Lpu lpu with (nolock) on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
				WHERE
					mmsf.MedPersonal_id = :MedPersonal_id
					and msf.Lpu_id = :Lpu_id
					and msf.MedStaffFact_Stavka > 0
					and lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap')
					{$filter_medstafffact} {$filter}
			";

			$sql_medstafffact_priem = "
				-- приемные отделения стационара
				SELECT --distinct
					msf.MedStaffFact_id,
					ls.LpuSection_id,
					msf.MedPersonal_id,
					isnull(ls.LpuSection_FullName,'') as LpuSection_Name,
					isnull(ls.LpuSection_Name,'') as LpuSection_Nick,
					isnull(ps.PostMed_Name,'') as PostMed_Name,
					ps.PostMed_Code,
					ps.PostMed_id,
					lb.LpuBuilding_id,
					isnull(lb.LpuBuilding_Name,'') as LpuBuilding_Name,
					lu.LpuUnit_id,
					lu.LpuUnitSet_id,
					isnull(lu.LpuUnit_Name,'') as LpuUnit_Name,
					case
						when (select count(*) from v_TimetableStac_lite tt with (nolock) where tt.LpuSection_id = ls.LpuSection_id) > 0
						then 'true' else 'false'
					end as Timetable_isExists,
					lut.LpuUnitType_SysNick,
					lu.LpuUnitType_id,
					lsp.LpuSectionProfile_SysNick,
					lsp.LpuSectionProfile_Code,
					lsp.LpuSectionProfile_id,
					null as MedService_id,
					null as MedService_Nick,
					null as MedService_Name,
					null as MedServiceType_id,
					null as MedServiceType_SysNick, 
					msf.Person_FIO as MedPersonal_FIO, 
					Lpu.Org_id,
					Lpu.Lpu_id as Lpu_id,
					Lpu.Lpu_Nick as Org_Nick,
					Lpu.Lpu_Nick as Lpu_Nick,
					null as MedStaffFactLink_id,
					null as MedStaffFactLink_begDT,
					null as MedStaffFactLink_endDT,
					msf.MedStaffFactCache_IsDisableInDoc,
					null as ElectronicQueueInfo_id,
					null as ElectronicService_id,
					null as ElectronicService_Num,
					null as ElectronicQueueInfo_CallTimeSec,
					null as ElectronicQueueInfo_PersCallDelTimeMin,
					null as ElectronicQueueInfo_CallCount,
					null as ElectronicService_isShownET,
					'' as ElectronicTreatment_ids,
					null as ElectronicScoreboard_id,
					null as ElectronicScoreboard_IPaddress,
					null as ElectronicScoreboard_Port,
					null as Storage_id,
					null as Storage_pid,
					null as SmpUnitType_Code
					" . $persisFields . "
				FROM
					v_LpuSection ls with (nolock)
					inner join v_Lpu lpu with (nolock) on lpu.Lpu_id = ls.Lpu_id
					inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
					inner join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					inner join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
					cross apply (
						select top 1
							 Person_FIO
							,Post_id
							,MedicalCareKind_id
							,PostKind_id
							,MedStaffFact_id
							,MedPersonal_id
							,WorkData_begDate
							,WorkData_endDate
							,MedStaffFactCache_IsDisableInDoc
						from v_MedStaffFact with (nolock)
						where 
							LpuUnit_id = lu.LpuUnit_id
							and MedStaffFact_Stavka > 0
							and MedPersonal_id = :MedPersonal_id
							and WorkData_begDate <= @date
							and (WorkData_endDate is null or WorkData_endDate >= @date)
							and PostKind_id in (1, 10)
					) msf
					inner join v_PostMed ps with (nolock) on ps.PostMed_id = msf.Post_id
				WHERE
					lut.LpuUnitType_SysNick = 'stac'
					and (lsp.LpuSectionProfile_SysNick = 'priem' or lsp.LpuSectionProfile_Code = '160')
					and ls.Lpu_id = :Lpu_id
					and ISNULL(ls.LpuSection_setDate, @date) <= @date
					and ISNULL(ls.LpuSection_disDate, @date) >= @date
					and not exists (
						select top 1 MedStaffFact_id
						from v_MedStaffFact with (nolock)
						where LpuSection_id = ls.LpuSection_id
							and MedPersonal_id = :MedPersonal_id
							and WorkData_begDate <= @date
							and (WorkData_endDate is null or WorkData_endDate >= @date)
					)
					and (msf.PostKind_id = 1 or ps.PostMed_Code in (2, 6, 262, 10002))
					{$filter}
			";

			if (!empty($data['StacPriemOnly']) && $data['StacPriemOnly'] == 2) {
				$sql = $sql_medstafffact_priem;
			} elseif ($data['MedService_id']>0) {
				$sql = $sql_medservice;
			} elseif ($data['MedStaffFact_id']>0) {
				$sql = $sql_medstafffact . ' union all ' . $sql_workgraph . ' union all ' . $sql_medstafffact_linked  . ' union all ' . $sql_medstafffact_priem;
			} else {
				$sql =  $sql_medstafffact . ' union all ' . $sql_workgraph . ' union all ' . $sql_medservice . ' union all ' . $sql_medstafffact_linked  . ' union all ' . $sql_medstafffact_priem;
			}
		}

		$sql = "
			declare @date datetime = cast(dbo.tzGetDate() as date);
		" . $sql;

		//echo getDebugSql($sql,$params);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
}