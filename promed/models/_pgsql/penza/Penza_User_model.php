<?php
/**
* Penza_User_model - модель для работы с учетными записями пользователей (Пенза)
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

require_once(APPPATH.'models/_pgsql/User_model.php');

class Penza_User_model extends User_model {
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
						null::bigint as \"MedStaffFact_id\",
						MS.LpuSection_id as \"LpuSection_id\",
						:pmUser_id as \"MedPersonal_id\",
						null as \"LpuSection_Name\",
						null as \"LpuSection_Nick\",
						null as \"PostMed_Name\",
						null as \"PostMed_Code\",
						null::bigint as \"PostMed_id\",
						null::bigint as \"LpuBuilding_id\",
						null as \"LpuBuilding_Name\",
						null::bigint as \"LpuUnit_id\",
						null::bigint as \"LpuUnitSet_id\",
						null as \"LpuUnit_Name\",
						null as \"Timetable_isExists\", 
						null as \"LpuUnitType_SysNick\",
						MS.LpuUnitType_id as \"LpuUnitType_id\",
						null as \"LpuSectionProfile_SysNick\",
						null as \"LpuSectionProfile_Code\",
						null::bigint as \"LpuSectionProfile_id\",
						MS.MedService_id as \"MedService_id\",
						MS.MedService_Nick as \"MedService_Nick\",
						MS.MedService_Name as \"MedService_Name\",
						MS.MedServiceType_id as \"MedServiceType_id\",
						mst.MedServiceType_SysNick as \"MedServiceType_SysNick\",
						ms.MedService_IsExternal as \"MedService_IsExternal\",
						:pmUser_Name as \"MedPersonal_FIO\",
						Org.Org_id as \"Org_id\",
						null::bigint as \"Lpu_id\",
						Org.Org_Nick as \"Org_Nick\",
						null as \"Lpu_Nick\",
						null::bigint as \"MedicalCareKind_id\",
						null::bigint as \"PostKind_id\",
						null as \"SmpUnitType_Code\",
						null as \"SmpUnitParam_IsKTPrint\"
					FROM 
						v_MedService MS 
						left join v_Org Org  on Org.Org_id = MS.Org_id
						left join v_MedServiceType mst  on mst.MedServiceType_id = MS.MedServiceType_id
					where
						MS.Org_id = :Org_id 
						and MS.MedService_begDT <= dbo.tzGetDate() and (MS.MedService_endDT >= dbo.tzGetDate() or MS.MedService_endDT is null) 
						{$filter}
				";
			} else {
				$sql = " 
					SELECT
						null::bigint as \"MedStaffFact_id\",
						MS.LpuSection_id as \"LpuSection_id\",
						:pmUser_id as \"MedPersonal_id\",
						null as \"LpuSection_Name\",
						null as \"LpuSection_Nick\",
						null as \"PostMed_Name\",
						null as \"PostMed_Code\",
						null::bigint as \"PostMed_id\",
						null::bigint as \"LpuBuilding_id\",
						null as \"LpuBuilding_Name\",
						null::bigint as \"LpuUnit_id\",
						null::bigint as \"LpuUnitSet_id\",
						null as \"LpuUnit_Name\",
						null as \"Timetable_isExists\", 
						null as \"LpuUnitType_SysNick\",
						MS.LpuUnitType_id as \"LpuUnitType_id\",
						null as \"LpuSectionProfile_SysNick\",
						null as \"LpuSectionProfile_Code\",
						null::bigint as \"LpuSectionProfile_id\",
						MS.MedService_id as \"MedService_id\",
						MS.MedService_Nick as \"MedService_Nick\",
						MS.MedService_Name as \"MedService_Name\",
						MS.MedServiceType_id as \"MedServiceType_id\",
						mst.MedServiceType_SysNick as \"MedServiceType_SysNick\", 
						ms.MedService_IsExternal as \"MedService_IsExternal\",
						:pmUser_Name as \"MedPersonal_FIO\",
						Org.Org_id as \"Org_id\",
						null::bigint as \"Lpu_id\",
						Org.Org_Nick as \"Org_Nick\",
						null as \"Lpu_Nick\",
						null::bigint as \"MedicalCareKind_id\",
						null::bigint as \"PostKind_id\",
						null::bigint as \"SmpUnitType_Code\",
						null as \"SmpUnitParam_IsKTPrint\"
					FROM 
						v_MedService MS 
						left join v_Org Org  on Org.Org_id = MS.Org_id
						left join v_MedServiceType mst  on mst.MedServiceType_id = MS.MedServiceType_id
					where
						MS.Org_id = :Org_id 
						and MS.MedService_begDT <= dbo.tzGetDate() and (MS.MedService_endDT >= dbo.tzGetDate() or MS.MedService_endDT is null)
						{$filter}
				";
			}
		} else {
			
			$filter_medstafffact = '';
			$filter_medservicemedpersonal = '';
			$filter_medservice = '';
			$use_date = true;
			if ($use_date) {
				$filter_medstafffact = 'and cast(msf.WorkData_begDate as date) <= dbo.tzGetDate() and (cast(msf.WorkData_endDate as date) >= dbo.tzGetDate() or msf.WorkData_endDate is null)';
				$filter_medservicemedpersonal = 'and (cast(msmp.MedServiceMedPersonal_begDT as date) <= dbo.tzGetDate() and (cast(msmp.MedServiceMedPersonal_endDT as date) >= dbo.tzGetDate() or msmp.MedServiceMedPersonal_endDT is null))';
				$filter_medservice = 'and cast(MS.MedService_begDT as date) <= dbo.tzGetDate() and (cast(MS.MedService_endDT as date) >= dbo.tzGetDate() or MS.MedService_endDT is null)';
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

			$persisFields = "
				,msf.MedicalCareKind_id as \"MedicalCareKind_id\"
				,msf.PostKind_id as \"PostKind_id\"
			";

			$farmacy_filter = "(1=0)";
			if (isset($_SESSION['OrgFarmacy_id'])) {
				$farmacy_filter = " exists(Select OrgFarmacy_id from v_OrgFarmacy  where OrgFarmacy_id = :Lpu_id) ";

			}
			
			$sql_medstafffact = "
				-- места работы 
				SELECT
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					msf.LpuSection_id as \"LpuSection_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
					COALESCE(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
					COALESCE(ps.PostMed_Name,'') as \"PostMed_Name\",
					ps.PostMed_Code as \"PostMed_Code\",
					ps.PostMed_id as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					COALESCE(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					COALESCE(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
					case 
						when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') then 
							case when (select count(*) from v_TimetableGraf_lite tt  where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick = 'parka' then 
							case when (select count(*) from v_TimetablePar tt  where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac') then 
							case when (select count(*) from v_TimetableStac_lite tt  where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						else 'false'
					end as \"Timetable_isExists\",
					--case when ps.PostMed_code = '10002' or ps.PostMed_code = '6' then 2 else 1 end as mp_is_zav,
					--case when (select count(*) from v_MedStaffRegion msr where msf.MedPersonal_id = msr.MedPersonal_id and msr.Lpu_id = msf.Lpu_id) > 0 then 2 else 1 end as mp_is_uch,
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					null::bigint as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null::bigint as \"MedServiceType_id\",
					null as \"MedServiceType_SysNick\", 
					msf.Person_FIO as \"MedPersonal_FIO\", 
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null::bigint as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
					eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
					eq.ElectronicService_id as \"ElectronicService_id\",
					eq.ElectronicService_Num as \"ElectronicService_Num\",
					eq.ElectronicQueueInfo_CallTimeSec as \"ElectronicQueueInfo_CallTimeSec\",
					eq.ElectronicQueueInfo_PersCallDelTimeMin as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					eq.ElectronicQueueInfo_CallCount as \"ElectronicQueueInfo_CallCount\",
					eq.ElectronicService_isShownET as \"ElectronicService_isShownET\",
					CAST((
						select string_agg(COALESCE(CAST(etl.ElectronicTreatment_id as VARCHAR),''), ',')
						from v_ElectronicTreatmentLink etl 
							inner join v_ElectronicQueueInfo eqio  on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
							inner join v_ElectronicService eso  on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
						where eso.ElectronicService_id = eq.ElectronicService_id
						) AS VARCHAR) as \"ElectronicTreatment_ids\",
					eboard.ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
					eboard.ElectronicScoreboard_IPaddress as \"ElectronicScoreboard_IPaddress\",
					eboard.ElectronicScoreboard_Port as \"ElectronicScoreboard_Port\",
					null::bigint as \"SmpUnitType_Code\",
					null::bigint as \"SmpUnitParam_IsKTPrint\",
					null::bigint as \"Storage_id\",
					null::bigint as \"Storage_pid\"
					" . $persisFields . "
				FROM
					v_MedStaffFact msf 
					left join v_Lpu lpu  on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls  on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb  on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut  on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps  on ps.PostMed_id = msf.Post_id
					LEFT JOIN LATERAL (
						select 
							eqi.ElectronicQueueInfo_id,
							mseq.ElectronicService_id,
							es.ElectronicService_Num,
							eqi.ElectronicQueueInfo_CallTimeSec,
							eqi.ElectronicQueueInfo_PersCallDelTimeMin,
							eqi.ElectronicQueueInfo_CallCount,
							case when es.ElectronicService_isShownET = 2 then 1 else null end as ElectronicService_isShownET
						from
							v_MedServiceElectronicQueue mseq 
							left join v_ElectronicService es  on es.ElectronicService_id = mseq.ElectronicService_id
							left join v_ElectronicQueueInfo eqi  on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
							left join v_ElectronicScoreboardQueueLink esql  on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
						where
							 mseq.MedStaffFact_id = msf.MedStaffFact_id
			    			 and eqi.ElectronicQueueInfo_IsOff = 1
                        limit 1
					) eq ON true
					LEFT JOIN LATERAL (
						select 
							ebd.ElectronicScoreboard_id,
							ebd.ElectronicScoreboard_IPaddress,
							ebd.ElectronicScoreboard_Port
						from v_ElectronicScoreboard ebd 
						left join v_ElectronicScoreboardQueueLink esql  on esql.ElectronicService_id = eq.ElectronicService_id
						where
							ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
							and ebd.ElectronicScoreboard_IsLED = 2
                        limit 1
					) as eboard ON true
				WHERE
					msf.MedPersonal_id = :MedPersonal_id and msf.Lpu_id = :Lpu_id
					and msf.MedStaffFact_Stavka > 0
					{$filter_medstafffact} {$filter}
				";
			$sql_workgraph = "
			SELECT
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					ls.LpuSection_id as \"LpuSection_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
					COALESCE(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
					COALESCE(ps.PostMed_Name,'') as \"PostMed_Name\",
					ps.PostMed_Code as \"PostMed_Code\",
					ps.PostMed_id as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					COALESCE(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					COALESCE(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
					case
						when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') then
							case when (select count(*) from v_TimetableGraf_lite tt  where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick = 'parka' then
							case when (select count(*) from v_TimetablePar tt  where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac') then
							case when (select count(*) from v_TimetableStac_lite tt  where msf.LpuSection_id = tt.LpuSection_id) > 0 then 'true' else 'false' end
						else 'false'
					end as \"Timetable_isExists\",
					--case when ps.PostMed_code = '10002' or ps.PostMed_code = '6' then 2 else 1 end as mp_is_zav,
					--case when (select count(*) from v_MedStaffRegion msr where msf.MedPersonal_id = msr.MedPersonal_id and msr.Lpu_id = msf.Lpu_id) > 0 then 2 else 1 end as mp_is_uch,
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					null::bigint as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null::bigint as \"MedServiceType_id\",
					null as \"MedServiceType_SysNick\",
					msf.Person_FIO as \"MedPersonal_FIO\",
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null::bigint as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
					null::bigint as \"ElectronicQueueInfo_id\",
					null::bigint as \"ElectronicService_id\",
					null as \"ElectronicService_Num\",
					null as \"ElectronicQueueInfo_CallTimeSec\",
					null as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					null as \"ElectronicQueueInfo_CallCount\",
					null::bigint as \"ElectronicService_isShownET\",
					'' as \"ElectronicTreatment_ids\",
					null::bigint as \"ElectronicScoreboard_id\",
					null as \"ElectronicScoreboard_IPaddress\",
					null as \"ElectronicScoreboard_Port\",
					null::bigint as \"SmpUnitType_Code\",
					null::bigint as \"SmpUnitParam_IsKTPrint\",
					null::bigint as \"Storage_id\",
					null::bigint as \"Storage_pid\"
					" . $persisFields . "
				FROM
					v_MedStaffFact msf 
					left join v_Lpu lpu  on lpu.Lpu_id = msf.Lpu_id
					inner join v_WorkGraph WG on (
						WG.MedStaffFact_id = msf.MedStaffFact_id and
						(
							CAST(WG.WorkGraph_begDT as date) <= dbo.tzGetDate()
							and CAST(WG.WorkGraph_endDT as date) >= dbo.tzGetDate()
						)
					)
					left join v_WorkGraphLpuSection WGLS on WGLS.WorkGraph_id = WG.WorkGraph_id
					--left join v_LpuSection ls  on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSection ls  on ls.LpuSection_id = WGLS.LpuSection_id
					left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb  on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut  on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps  on ps.PostMed_id = msf.Post_id
				WHERE
					msf.MedPersonal_id = :MedPersonal_id and msf.Lpu_id = :Lpu_id 
					{$filter}
			";
			$sql_medservice = "
				-- сотрудники служб 
				SELECT
					case 
						when mst.MedServiceType_SysNick = 'reanimation' then (
							select t1.MedStaffFact_id 
							from v_MedStaffFact t1 
								inner join dbo.v_LpuUnit t2 on t2.LpuUnit_id = t1.LpuUnit_id
							where t1.MedPersonal_id = msmp.MedPersonal_id 
								and t1.WorkData_endDate is null
								and t2.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac','priem')
                            limit 1
						) else null 
					end as \"MedStaffFact_id\",
					MS.LpuSection_id as \"LpuSection_id\",
					msmp.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
					COALESCE(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
					null as \"PostMed_Name\",
					null as \"PostMed_Code\",
					null::bigint as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					COALESCE(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					COALESCE(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
					null as \"Timetable_isExists\", 
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					MS.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					MS.MedService_id as \"MedService_id\",
					MS.MedService_Nick as \"MedService_Nick\",
					MS.MedService_Name as \"MedService_Name\",
					MS.MedServiceType_id as \"MedServiceType_id\",
					mst.MedServiceType_SysNick as \"MedServiceType_SysNick\", 
					msmp.Person_FIO as \"MedPersonal_FIO\", 
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null::bigint as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					null::bigint as \"MedStaffFactCache_IsDisableInDoc\",
					eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
					eq.ElectronicService_id as \"ElectronicService_id\",
					eq.ElectronicService_Num as \"ElectronicService_Num\",
					eq.ElectronicQueueInfo_CallTimeSec as \"ElectronicQueueInfo_CallTimeSec\",
					eq.ElectronicQueueInfo_PersCallDelTimeMin as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					eq.ElectronicQueueInfo_CallCount as \"ElectronicQueueInfo_CallCount\",
					eq.ElectronicService_isShownET as \"ElectronicService_isShownET\",
					CAST((
						select string_agg(COALESCE(CAST(etl.ElectronicTreatment_id as VARCHAR),''), ',')
						from v_ElectronicTreatmentLink etl 
							inner join v_ElectronicQueueInfo eqio  on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
							inner join v_ElectronicService eso  on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
						where eso.ElectronicService_id = eq.ElectronicService_id
						) AS VARCHAR) as \"ElectronicTreatment_ids\",
					eboard.ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
					eboard.ElectronicScoreboard_IPaddress as \"ElectronicScoreboard_IPaddress\",
					eboard.ElectronicScoreboard_Port as \"ElectronicScoreboard_Port\",
					sut.SmpUnitType_Code as \"SmpUnitType_Code\",
					sup.SmpUnitParam_IsKTPrint as \"SmpUnitParam_IsKTPrint\",
					strg.Storage_id as \"Storage_id\",
					strg.Storage_pid as \"Storage_pid\",
					null::bigint as \"MedicalCareKind_id\",
					msf.PostKind_id as \"PostKind_id\"
				FROM 
					v_MedService MS 
					INNER JOIN LATERAL (
						Select msmp.MedPersonal_id, Person_Fio from v_MedServiceMedPersonal msmp 
						left join v_MedPersonal mp  on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = MS.Lpu_id
						where msmp.MedService_id = MS.MedService_id
						and msmp.MedPersonal_id = :MedPersonal_id
						{$filter_medservicemedpersonal}
						limit 1
					) as msmp ON true
					LEFT JOIN LATERAL (
						select 
							msf.PostKind_id
						from
							v_MedStaffFact msf 
						where
							msf.MedPersonal_id = msmp.MedPersonal_id
							and msf.LpuSection_id = ms.LpuSection_id
                        limit 1
					) msf ON true
					LEFT JOIN LATERAL (
						select 
							eqi.ElectronicQueueInfo_id,
							mseq.ElectronicService_id,
							es.ElectronicService_Num,
							eqi.ElectronicQueueInfo_CallTimeSec,
							eqi.ElectronicQueueInfo_PersCallDelTimeMin,
							eqi.ElectronicQueueInfo_CallCount,
							case when es.ElectronicService_isShownET = 2 then 1 else null end as ElectronicService_isShownET
						from
							v_MedServiceElectronicQueue mseq 
							left join v_MedServiceMedPersonal msmp2  on msmp2.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
							left join v_ElectronicService es  on es.ElectronicService_id = mseq.ElectronicService_id
							left join v_ElectronicQueueInfo eqi  on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
							left join v_ElectronicScoreboardQueueLink esql  on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
						where
							 msmp2.MedPersonal_id = msmp.MedPersonal_id
							 and msmp2.MedService_id = MS.MedService_id
			    			 and eqi.ElectronicQueueInfo_IsOff = 1
                        limit 1
					) eq ON true
					LEFT JOIN LATERAL (
						select 
							ebd.ElectronicScoreboard_id,
							ebd.ElectronicScoreboard_IPaddress,
							ebd.ElectronicScoreboard_Port
						from v_ElectronicScoreboard ebd 
						left join v_ElectronicScoreboardQueueLink esql  on esql.ElectronicService_id = eq.ElectronicService_id
						where
							ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
							and ebd.ElectronicScoreboard_IsLED = 2
                        limit 1
					) as eboard ON true
					left join v_Lpu lpu  on lpu.Lpu_id = MS.Lpu_id
					left join v_LpuSection ls  on ls.LpuSection_id = MS.LpuSection_id
					left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb  on lb.LpuBuilding_id = COALESCE(ls.LpuBuilding_id,MS.LpuBuilding_id)
					LEFT JOIN LATERAL (
						select  *
						from v_SmpUnitParam sup 
						where sup.LpuBuilding_id = lb.LpuBuilding_id
						order by sup.SmpUnitParam_id desc
                       	limit 1
					) sup ON true
					left join v_SmpUnitType sut  on sut.SmpUnitType_id = sup.SmpUnitType_id
					left join v_LpuUnit lu  on lu.LpuUnit_id = COALESCE(ls.LpuUnit_id,MS.LpuUnit_id)
					left join v_LpuUnitType lut  on lut.LpuUnitType_id = COALESCE(lu.LpuUnitType_id,MS.LpuUnitType_id)
					left join v_MedServiceType mst  on mst.MedServiceType_id = MS.MedServiceType_id
					LEFT JOIN LATERAL (
						select
							i_s.Storage_id,
							i_s.Storage_pid
						from
							v_StorageStructLevel i_ssl 
							left join v_Storage i_s  on i_s.Storage_id = i_ssl.Storage_id
						where
							i_ssl.MedService_id = MS.MedService_id
						order by
							i_ssl.StorageStructLevel_id
                        limit 1
					) strg ON true
				where
					MS.Lpu_id = :Lpu_id and
					(1=1)
					{$filter_medservice} {$filter}
					and msmp.MedPersonal_id = :MedPersonal_id
					and mst.MedServiceType_SysNick in ('HTM', 'vk', 'mse', 'lab', 'pzm', 'func', 'patb', 'mstat', 'prock', 'dpoint', 'ooa', 'merch', 'pmllo', 'regpol', 'sprst', 'okadr', 'minzdravdlo', 'leadermo', 'mekllo', 'spesexpertllo', 'adminllo', 'touz', 'reglab', 'oper_block', 'smp', 'slneotl', 'konsult', 'foodserv', 'vac', 'epidem_mo', 'remoteconsultcenter','smpdispatchstation','forenbiodprtwithmolgenlab','forenchemdprt','medforendprt','forenhistdprt','organmethdprt','forenmedcorpsexpdprt','forenmedexppersdprt','commcomplexp','forenareadprt', 'lvn', 'smpheaddoctor', 'zmk', 'rpo','spec_mz', 'medosv','reanimation', 'microbiolab')
			";

			$sql_medstafffact_linked = "
				-- связанные места работы 
				SELECT
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					msf.LpuSection_id as \"LpuSection_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
					COALESCE(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
					COALESCE(ps.PostMed_Name,'') as \"PostMed_Name\",
					ps.PostMed_Code as \"PostMed_Code\",
					ps.PostMed_id as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					COALESCE(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					COALESCE(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
					case
						when (select count(*) from v_TimetableGraf_lite tt  where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 then 'true'
						else 'false'
					end as \"Timetable_isExists\",
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					null::bigint as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null::bigint as \"MedServiceType_id\",
					null as \"MedServiceType_SysNick\", 
					msf.Person_FIO as \"MedPersonal_FIO\", 
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					msfl.MedStaffFactLink_id as \"MedStaffFactLink_id\",
					to_char(msfl.MedStaffFactLink_begDT, 'DD.MM.YYYY') as \"MedStaffFactLink_begDT\",
					to_char(msfl.MedStaffFactLink_endDT, 'DD.MM.YYYY') as \"MedStaffFactLink_endDT\",
					msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
					null::bigint as \"ElectronicQueueInfo_id\",
					null::bigint as \"ElectronicService_id\",
					null as \"ElectronicService_Num\",
					null as \"ElectronicQueueInfo_CallTimeSec\",
					null as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					null as \"ElectronicQueueInfo_CallCount\",
					null::bigint as \"ElectronicService_isShownET\",
					'' as \"ElectronicTreatment_ids\",
					null::bigint as \"ElectronicScoreboard_id\",
					null as \"ElectronicScoreboard_IPaddress\",
					null as \"ElectronicScoreboard_Port\",
					null::bigint as \"SmpUnitType_Code\",
					null::bigint as \"SmpUnitParam_IsKTPrint\",
					null::bigint as \"Storage_id\",
					null::bigint as \"Storage_pid\"
					" . $persisFields . "
				FROM
					v_MedStaffFactLink msfl 
					inner join v_MedStaffFact msf  on msf.MedStaffFact_id = msfl.MedStaffFact_id
					inner join v_MedStaffFact mmsf  on mmsf.MedStaffFact_id = msfl.MedStaffFact_sid
					left join v_Lpu lpu  on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls  on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb  on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut  on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps  on ps.PostMed_id = msf.Post_id
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
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					ls.LpuSection_id as \"LpuSection_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
					COALESCE(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
					COALESCE(ps.PostMed_Name,'') as \"PostMed_Name\",
					ps.PostMed_Code as \"PostMed_Code\",
					ps.PostMed_id as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					COALESCE(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					COALESCE(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
					case
						when (select count(*) from v_TimetableStac_lite tt  where tt.LpuSection_id = ls.LpuSection_id) > 0
						then 'true' else 'false'
					end as \"Timetable_isExists\",
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					lu.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					null::bigint as \"MedService_id\",
					null as \"MedService_Nick\",
					null as \"MedService_Name\",
					null::bigint as \"MedServiceType_id\",
					null as \"MedServiceType_SysNick\", 
					msf.Person_FIO as \"MedPersonal_FIO\", 
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null::bigint as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
					null::bigint as \"ElectronicQueueInfo_id\",
					null::bigint as \"ElectronicService_id\",
					null as \"ElectronicService_Num\",
					null as \"ElectronicQueueInfo_CallTimeSec\",
					null as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					null as \"ElectronicQueueInfo_CallCount\",
					null::bigint as \"ElectronicService_isShownET\",
					'' as \"ElectronicTreatment_ids\",
					null::bigint as \"ElectronicScoreboard_id\",
					null as \"ElectronicScoreboard_IPaddress\",
					null as \"ElectronicScoreboard_Port\",
					null::bigint as \"SmpUnitType_Code\",
					null::bigint as \"SmpUnitParam_IsKTPrint\",
					null::bigint as \"Storage_id\",
					null::bigint as \"Storage_pid\"
					" . $persisFields . "
				FROM
					v_LpuSection ls 
					inner join v_Lpu lpu  on lpu.Lpu_id = ls.Lpu_id
					inner join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb  on lb.LpuBuilding_id = ls.LpuBuilding_id
					inner join v_LpuUnit lu  on lu.LpuUnit_id = ls.LpuUnit_id
					inner join v_LpuUnitType lut  on lut.LpuUnitType_id = lu.LpuUnitType_id
					INNER JOIN LATERAL (
						select 
							 Person_FIO
							,Post_id
							,MedicalCareKind_id
							,PostKind_id
							,MedStaffFact_id
							,MedPersonal_id
							,WorkData_begDate
							,WorkData_endDate
							,MedStaffFactCache_IsDisableInDoc
						from v_MedStaffFact 
						where 
							LpuUnit_id = lu.LpuUnit_id
							and MedStaffFact_Stavka > 0
							and MedPersonal_id = :MedPersonal_id
							and WorkData_begDate <= dbo.tzGetDate()
							and (WorkData_endDate is null or WorkData_endDate >= dbo.tzGetDate())
							and PostKind_id in (1, 10)
                            limit 1
					) msf ON true
					inner join v_PostMed ps  on ps.PostMed_id = msf.Post_id
				WHERE
					lut.LpuUnitType_SysNick = 'stac'
					and (lsp.LpuSectionProfile_SysNick = 'priem' or lsp.LpuSectionProfile_Code = '160')
					and ls.Lpu_id = :Lpu_id
					and COALESCE(ls.LpuSection_setDate, dbo.tzGetDate()) <= dbo.tzGetDate()
					and COALESCE(ls.LpuSection_disDate, dbo.tzGetDate()) >= dbo.tzGetDate()
					and not exists (
						select MedStaffFact_id
						from v_MedStaffFact 
						where LpuSection_id = ls.LpuSection_id
							and MedPersonal_id = :MedPersonal_id
							and WorkData_begDate <= dbo.tzGetDate()
							and (WorkData_endDate is null or WorkData_endDate >= dbo.tzGetDate())
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

		//echo getDebugSql($sql,$params);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
}