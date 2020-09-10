<?php
/**
* Perm_User_model - модель для работы с учетными записями пользователей (Пермь)
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

class Perm_User_model extends User_model {
    /**
     * Конструктор
     */
	function __construct()
	{
		parent::__construct();
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
                $select = "null as \"MedStaffFact_id\",
						MS.LpuSection_id as \"LpuSection_id\",
						:pmUser_id as \"MedPersonal_id\",
						null as \"LpuSection_Name\",
						null as \"LpuSection_Nick\",
						null as \"PostMed_Name\",
						null as \"PostMed_Code\",
						null as \"PostMed_id\",
						null as \"LpuBuilding_id\",
						null as \"LpuBuilding_Name\",
						null as \"LpuUnit_id\",
						null as \"LpuUnitSet_id\",
						null as \"LpuUnit_Name\",
						null as \"Timetable_isExists\", 
						null as \"LpuUnitType_SysNick\",
						MS.LpuUnitType_id as \"LpuUnitType_id\",
						null as \"LpuSectionProfile_SysNick\",
						null as \"LpuSectionProfile_Code\",
						null as \"LpuSectionProfile_id\",
						MS.MedService_id::bigint as \"MedService_id\",
						MS.MedService_Nick as \"MedService_Nick\",
						MS.MedService_Name as \"MedService_Name\",
						MS.MedServiceType_id::bigint as \"MedServiceType_id\",
						MST.MedServiceType_SysNick as \"MedServiceType_SysNick\", 
						MS.MedService_IsExternal::integer as \"MedService_IsExternal\",
						MS.MedService_IsLocalCMP::integer as \"MedService_IsLocalCMP\",
						MS.MedService_LocalCMPPath as \"MedService_LocalCMPPath\",
						:pmUser_Name as MedPersonal_FIO,
						Org.Org_id as \"Org_id\",
						null as \"Lpu_id\",
						Org.Org_Nick as \"Org_Nick\",
						null as \"Lpu_Nick\",
						null as \"MedicalCareKind_id\",
						null as \"PostKind_id\"
				";
                $filter = "
                        MS.Org_id = :Org_id 
						and MS.MedService_begDT <= (select date from cte) and (MS.MedService_endDT >= (select date from cte) or MS.MedService_endDT is null)
						{$filter}
				";



				$sql = " 
					select
					    --select
					    {$select}
						--end select
					from 
						v_MedService MS
						left join v_Org Org on Org.Org_id = MS.Org_id
						left join v_MedServiceType mst on mst.MedServiceType_id = MS.MedServiceType_id
					where
					    --filter
						{$filter}
						--end filter
				";
			} else {
			    $select = "
			            null as \"MedStaffFact_id\",
						MS.LpuSection_id as \"LpuSection_id\",
						:pmUser_id as \"MedPersonal_id\",
						null as \"LpuSection_Name\",
						null as \"LpuSection_Nick\",
						null as \"PostMed_Name\",
						null as \"PostMed_Code\",
						null as \"PostMed_id\",
						null as \"LpuBuilding_id\",
						null as \"LpuBuilding_Name\",
						null as \"LpuUnit_id\",
						null as \"LpuUnitSet_id\",
						null as \"LpuUnit_Name\",
						null as \"Timetable_isExists\", 
						null as \"LpuUnitType_SysNick\",
						MS.LpuUnitType_id as \"LpuUnitType_id\",
						null as \"LpuSectionProfile_SysNick\",
						null as \"LpuSectionProfile_Code\",
						null as \"LpuSectionProfile_id\",
						MS.MedService_id::bigint as \"MedService_id\",
						MS.MedService_Nick as \"MedService_Nick\",
						MS.MedService_Name as \"MedService_Name\",
						MS.MedServiceType_id::bigint as \"MedServiceType_id\",
						MST.MedServiceType_SysNick as \"MedServiceType_SysNick\", 
						MS.MedService_IsExternal::integer as \"MedService_IsExternal\",
						MS.MedService_IsLocalCMP::integer as \"MedService_IsLocalCMP\",
						MS.MedService_LocalCMPPath as \"MedService_LocalCMPPath\",
						:pmUser_Name as \"MedPersonal_FIO\",
						Org.Org_id as \"Org_id\",
						null as \"Lpu_id\",
						Org.Org_Nick as \"Org_Nick\",
						null as \"Lpu_Nick\",
						null as \"MedicalCareKind_id\",
						null as \"PostKind_id\",
						null::varchar as \"SmpUnitType_Code\",
						null::integer as \"SmpUnitParam_IsKTPrint\"
				";

			    $filter = "
                    PUO.Org_id = :Org_id
                    and PUO.pmUserCache_id = :pmUser_id
                    and MS.MedService_begDT <= (select date from cte) and (MS.MedService_endDT >= (select date from cte) or MS.MedService_endDT is null)
                    {$filter}
			    ";

			    $from = "
                    v_pmUserCacheOrg PUO
                    inner join v_PersonWork PW on PW.pmUserCacheOrg_id = PUO.pmUserCacheOrg_id
                    inner join v_Org Org on Org.Org_id = PW.Org_id
                    left join v_OrgStruct OS on OS.OrgStruct_id = PW.OrgStruct_id
                    inner join v_MedService MS on MS.Org_id = Org.Org_id and coalesce(MS.OrgStruct_id, 0) = coalesce (OS.OrgStruct_id, 0)
                    left join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
			    ";

				$sql = " 
					select
						--select
						{$select}
						--end select
					from
					    --from
					    {$from}
						-- end from
					where
						{$filter}
				";
			}
		} else {
            // для пользователей, которые не связаны с врачами, нет смысла выполнять запрос
            if (empty($data['MedPersonal_id'])) {
                return false;
            }

			$filter_medStaffFact = "";
			$filter_medServiceMedPersonal = "";
			$filter_medService = "";
			$use_date = true;
			if ($use_date) {
				$filter_medStaffFact = "
				    and 
				        cast(msf.WorkData_begDate as date) <= (select date from cte)
				    and 
				        (cast(msf.WorkData_endDate as date) >= (select date from cte) 
				    or 
				        msf.WorkData_endDate is null)
				";
				$filter_medServiceMedPersonal = "
				    and 
				        (
				            cast(msmp.MedServiceMedPersonal_begDT as date) <= (select date from cte)
                        and
                            (
                                cast(msmp.MedServiceMedPersonal_endDT as date) >= (select date from cte)
                                or
                                msmp.MedServiceMedPersonal_endDT is null
                            )
                        )
				    ";
				$filter_medService = "
				    and 
				        cast(MS.MedService_begDT as date) <= (select date from cte) 
				    and 
				        (
				            cast(MS.MedService_endDT as date) >= (select date from cte)
				        or 
				            MS.MedService_endDT is null
				        )
				    ";
			}
			$filter = "";
			$params = [
			    "MedPersonal_id" => $data["MedPersonal_id"],
                "Lpu_id" => $data["Lpu_id"],
                "pmUser_id" => $data["pmUser_id"],
                "pmUser_Name" => toAnsi($data["session"]["user"])
            ];

			if ($data["MedService_id"] > 0) {
				$params["MedService_id"] = $data["MedService_id"];
				$filter = " and MedService_id = :MedService_id";
			} elseif ($data["MedStaffFact_id"]>0) {
				$params["MedStaffFact_id"] = $data["MedStaffFact_id"];
				$filter = "and msf.MedStaffFact_id = :MedStaffFact_id";

                // если передано отделение, то фильтруем и по отделению
				if ($data["LpuSection_id"] > 0) {
					$params["LpuSection_id"] = $data["LpuSection_id"];
					$filter .= " and ls.LpuSection_id = :LpuSection_id";
				}
			}

            // если передано отделение, то фильтруем и по отделению
			if (isset($data["LpuSection_id"]) && $data["LpuSection_id"] > 0) {
				$params["LpuSection_id"] = $data["LpuSection_id"];
			}

			$persisFields = "
				msf.MedicalCareKind_id as \"MedicalCareKind_id\",
				msf.PostKind_id as \"PostKind_id\"
			";

			$select_medStaffFact = "
			    msf.MedStaffFact_id as \"MedStaffFact_id\",
                msf.LpuSection_id as \"LpuSection_id\",
                msf.MedPersonal_id::bigint as \"MedPersonal_id\",
                coalesce(ls.LpuSection_FullName, '') as \"LpuSection_Name\",
                coalesce(ls.LpuSection_Name, '') as \"LpuSection_Nick\",
                coalesce(ps.PostMed_Name, '') as \"PostMed_Name\",
                ps.PostMed_Code as \"PostMed_Code\",
                ps.PostMed_id as \"PostMed_id\",
                lb.LpuBuilding_id as \"LpuBuilding_id\",
                coalesce(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
                lu.LpuUnit_id as \"LpuUnit_id\",
                lu.LpuUnitSet_id as \"LpuUnitSet_id\",
                coalesce(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
                case 
                    when lut.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap') 
                        then 
                            case 
                                when 
                                    (select count(*) from v_TimetableGraf_lite tt where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 
                            then 'true' else 'false' end
                    when lut.LpuUnitType_SysNick = 'parka' 
                        then 
                            case 
                                when (select count(*) from v_TimetablePar tt where msf.LpuSection_id = tt.LpuSection_id) > 0 
                            then 'true' else 'false' end
                    when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac')
                        then 
                            case 
                                when (select count(*) from v_TimetableStac_lite tt where msf.LpuSection_id = tt.LpuSection_id) > 0
                            then 'true' else 'false' end
                    else 'false'				
                end as \"Timetable_isExists\",
                lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
                lu.LpuUnitType_id as \"LpuUnitType_id\",
                lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
                lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
                lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
                ls.LpuSectionAge_id as \"LpuSectionAge_id\",
                null::bigint as \"MedService_id\",
                null as \"MedService_Nick\",
                null as \"MedService_Name\",
                null::bigint as \"MedServiceType_id\",
                null as \"MedServiceType_SysNick\", 
                null::integer as \"MedService_IsExternal\",
                null::integer as \"MedService_IsLocalCMP\",
                null as \"MedService_LocalCMPPath\",
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
                (
                    select
                        string_agg(coalesce(etl.ElectronicTreatment_id::varchar, ''), ',')
                    from 
                        v_ElectronicTreatmentLink etl
                        inner join v_ElectronicQueueInfo eqio on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
                        inner join v_ElectronicService eso on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
                    where 
                        eso.ElectronicService_id = eq.ElectronicService_id
                ) as \"ElectronicTreatment_ids\",
                eboard.ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
                eboard.ElectronicScoreboard_IPaddress as \"ElectronicScoreboard_IPaddress\",
                eboard.ElectronicScoreboard_Port as \"ElectronicScoreboard_Port\",
                null::varchar as \"SmpUnitType_Code\",
                null::integer as \"SmpUnitParam_IsKTPrint\",
                null::bigint as \"Storage_id\",
                null::bigint as \"Storage_pid\",
                $persisFields
			";
			$from_medStaffFact = "
                v_MedStaffFact msf
                left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
                left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
                left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
                left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
                left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
                left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
                left join v_PostMed ps on ps.PostMed_id = msf.Post_id
                left join lateral (
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
                        left join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
                        left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
                        left join v_ElectronicScoreboardQueueLink esql on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
                    where
                         mseq.MedStaffFact_id = msf.MedStaffFact_id
                         and eqi.ElectronicQueueInfo_IsOff = 1
                    limit 1
                ) eq on true
                left join lateral (
                    select 
                        ebd.ElectronicScoreboard_id,
                        ebd.ElectronicScoreboard_IPaddress,
                        ebd.ElectronicScoreboard_Port
                    from 
                        v_ElectronicScoreboard ebd
                        left join v_ElectronicScoreboardQueueLink esql on esql.ElectronicService_id = eq.ElectronicService_id
                    where
                        ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
                        and ebd.ElectronicScoreboard_IsLED = 2
                    limit 1
                ) eboard on true
			";
			// места работы
			$sql_medStaffFact = "
				select
					--select
					{$select_medStaffFact}
					--end select
				from
				    --from
				    {$from_medStaffFact}
				    --end from
				where
					msf.MedPersonal_id = :MedPersonal_id and msf.Lpu_id = :Lpu_id
					and msf.MedStaffFact_Stavka > 0
					{$filter_medStaffFact} {$filter}
				";

			$select_workGraph = "
			    msf.MedStaffFact_id as \"MedStaffFact_id\",
                ls.LpuSection_id as \"LpuSection_id\",
                msf.MedPersonal_id as \"MedPersonal_id\",
                coalesce(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
                coalesce(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
                coalesce(ps.PostMed_Name,'') as \"PostMed_Name\",
                ps.PostMed_Code as \"PostMed_Code\",
                ps.PostMed_id as \"PostMed_id\",
                lb.LpuBuilding_id as \"LpuBuilding_id\",
                coalesce(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
                lu.LpuUnit_id as \"LpuUnit_id\",
                lu.LpuUnitSet_id as \"LpuUnitSet_id\",
                coalesce(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
                case
                    when lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap') 
                        then
                            case 
                                when (select count(*) from v_TimetableGraf_lite tt where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 
                            then 'true' else 'false' end
                    when lut.LpuUnitType_SysNick = 'parka'
                        then
                            case 
                                when
                                    (select count(*) from v_TimetablePar tt where msf.LpuSection_id = tt.LpuSection_id) > 0
                                then 'true' else 'false' end
                    when lut.LpuUnitType_SysNick in ('stac','hstac','pstac','dstac')
                        then
                            case
                                when (select count(*) from v_TimetableStac_lite tt where msf.LpuSection_id = tt.LpuSection_id) > 0
                            then 'true' else 'false' end
                    else 'false'
                end as \"Timetable_isExists\",
                lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
                lu.LpuUnitType_id as \"LpuUnitType_id\",
                lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
                lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
                lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
                ls.LpuSectionAge_id as \"LpuSectionAge_id\",
                null::bigint as \"MedService_id\",
                null as \"MedService_Nick\",
                null as \"MedService_Name\",
                null::bigint as \"MedServiceType_id\",
                null as \"MedServiceType_SysNick\",
                null::integer as \"MedService_IsExternal\",
                null::integer as \"MedService_IsLocalCMP\",
                null as \"MedService_LocalCMPPath\",
                msf.Person_FIO as \"MedPersonal_FIO\",
                Lpu.Org_id as \"Org_id\",
                Lpu.Lpu_id as Lpu_id,
                Lpu.Lpu_Nick as Org_Nick,
                Lpu.Lpu_Nick as Lpu_Nick,
                null::bigint as \"MedStaffFactLink_id\",
                null as \"MedStaffFactLink_begDT\",
                null as \"MedStaffFactLink_endDT\",
                msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
                null as \"ElectronicQueueInfo_id\",
                null as \"ElectronicService_id\",
                null as \"ElectronicService_Num\",
                null as \"ElectronicQueueInfo_CallTimeSec\",
                null as \"ElectronicQueueInfo_PersCallDelTimeMin\",
                null as \"ElectronicQueueInfo_CallCount\",
                null as \"ElectronicService_isShownET\",
                null as \"ElectronicTreatment_ids\",
                null as \"ElectronicScoreboard_id\",
                null as \"ElectronicScoreboard_IPaddress\",
                null as \"ElectronicScoreboard_Port\",
                null::varchar as \"SmpUnitType_Code\",
                null::integer as \"SmpUnitParam_IsKTPrint\",
                null::bigint as \"Storage_id\",
                null::bigint as \"Storage_pid\",
                 $persisFields 
			";
			$from_workGraph = "
			    v_MedStaffFact msf
                left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
                inner join v_WorkGraph WG on (
                    WG.MedStaffFact_id = msf.MedStaffFact_id and
                    (
                        CAST(WG.WorkGraph_begDT as date) <= (select date from cte)
                        and CAST(WG.WorkGraph_endDT as date) >= (select date from cte)
                    )
                )
                left join v_WorkGraphLpuSection WGLS on WGLS.WorkGraph_id = WG.WorkGraph_id
                left join v_LpuSection ls on ls.LpuSection_id = WGLS.LpuSection_id
                left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
                left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
                left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
                left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
                left join v_PostMed ps on ps.PostMed_id = msf.Post_id
			";

			$sql_workGraph = "
			    select
			        --select
					{$select_workGraph}
					--end select
				from
					--from 
					{$from_workGraph}
					--end from
				where
					msf.MedPersonal_id = :MedPersonal_id and msf.Lpu_id = :Lpu_id
					{$filter}
			";

			$select_medService = "
					case 
						when mst.MedServiceType_SysNick = 'reanimation' 
						    then (
                                select
                                    t1.MedStaffFact_id 
                                from 
                                    v_MedStaffFact t1
                                    inner join dbo.v_LpuUnit t2 on t2.LpuUnit_id = t1.LpuUnit_id
                                where 
                                    t1.MedPersonal_id = msmp.MedPersonal_id
                                and
                                    t1.WorkData_endDate is null
                                and
                                    t2.LpuUnitType_SysNick in ('stac','dstac','hstac','pstac','priem')
                                limit 1
                            ) else null 
					end as \"MedStaffFact_id\",
					MS.LpuSection_id as \"LpuSection_id\",
					msmp.MedPersonal_id as \"MedPersonal_id\",
					coalesce(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
					coalesce(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
					null as \"PostMed_Name\",
					null as \"PostMed_Code\",
					null as \"PostMed_id\",
					lb.LpuBuilding_id as \"LpuBuilding_id\",
					coalesce(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
					lu.LpuUnit_id as \"LpuUnit_id\",
					lu.LpuUnitSet_id as \"LpuUnitSet_id\",
					coalesce(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
					null as \"Timetable_isExists\", 
					lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
					MS.LpuUnitType_id as \"LpuUnitType_id\",
					lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
					lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
					lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
					ls.LpuSectionAge_id as \"LpuSectionAge_id\",
					MS.MedService_id::bigint as \"MedService_id\",
					MS.MedService_Nick as \"MedService_Nick\",
					MS.MedService_Name as \"MedService_Name\",
					MS.MedServiceType_id::bigint as \"MedServiceType_id\",
					mst.MedServiceType_SysNick as \"MedServiceType_SysNick\", 
					ms.MedService_IsExternal::integer as \"MedService_IsExternal\",
					ms.MedService_IsLocalCMP::integer as \"MedService_IsLocalCMP\",
					ms.MedService_LocalCMPPath as \"MedService_LocalCMPPath\",
					msmp.Person_FIO as \"MedPersonal_FIO\",
					Lpu.Org_id as \"Org_id\",
					Lpu.Lpu_id as \"Lpu_id\",
					Lpu.Lpu_Nick as \"Org_Nick\",
					Lpu.Lpu_Nick as \"Lpu_Nick\",
					null::bigint as \"MedStaffFactLink_id\",
					null as \"MedStaffFactLink_begDT\",
					null as \"MedStaffFactLink_endDT\",
					null as \"MedStaffFactCache_IsDisableInDoc\",
					eq.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
					eq.ElectronicService_id as \"ElectronicService_id\",
					eq.ElectronicService_Num as \"ElectronicService_Num\",
					eq.ElectronicQueueInfo_CallTimeSec as \"ElectronicQueueInfo_CallTimeSec\",
					eq.ElectronicQueueInfo_PersCallDelTimeMin as \"ElectronicQueueInfo_PersCallDelTimeMin\",
					eq.ElectronicQueueInfo_CallCount as \"ElectronicQueueInfo_CallCount\",
					eq.ElectronicService_isShownET as \"ElectronicService_isShownET\",
					(
                        select
                    	    string_agg(coalesce(etl.ElectronicTreatment_id::varchar, ''), ',')
                    	from v_ElectronicTreatmentLink etl
                    	    inner join v_ElectronicQueueInfo eqio on eqio.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
                    	    inner join v_ElectronicService eso  on eso.ElectronicQueueInfo_id = eqio.ElectronicQueueInfo_id
                    	where 
                    	    eso.ElectronicService_id = eq.ElectronicService_id
                    ) as \"ElectronicTreatment_ids\",
					eboard.ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
					eboard.ElectronicScoreboard_IPaddress as \"ElectronicScoreboard_IPaddress\",
					eboard.ElectronicScoreboard_Port as \"ElectronicScoreboard_Port\",
					sut.SmpUnitType_Code::varchar as \"SmpUnitType_Code\",
					sup.SmpUnitParam_IsKTPrint::integer as \"SmpUnitParam_IsKTPrint\",
					strg.Storage_id::bigint as \"Storage_id\",
					strg.Storage_pid::bigint as \"Storage_pid\",
					null as \"MedicalCareKind_id\",
					msf.PostKind_id as \"PostKind_id\"
			";

			$from_medService = "
			    v_MedService MS
                inner join lateral (
                    select 
                        msmp.MedPersonal_id, Person_Fio 
                    from
                        v_MedServiceMedPersonal msmp
                        left join v_MedPersonal mp on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = MS.Lpu_id
                    where 
                        msmp.MedService_id = MS.MedService_id
                    and 
                        msmp.MedPersonal_id = :MedPersonal_id
                    {$filter_medServiceMedPersonal}
                ) msmp on true
                left join lateral (
                    select
                        msf.PostKind_id
                    from
                        v_MedStaffFact msf
                    where
                        msf.MedPersonal_id = msmp.MedPersonal_id
                    and 
                        msf.LpuSection_id = ms.LpuSection_id
                    limit 1
                ) msf on true
                left join lateral (
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
                        left join v_MedServiceMedPersonal msmp2 on msmp2.MedServiceMedPersonal_id = mseq.MedServiceMedPersonal_id
                        left join v_ElectronicService es on es.ElectronicService_id = mseq.ElectronicService_id
                        left join v_ElectronicQueueInfo eqi on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
                        left join v_ElectronicScoreboardQueueLink esql on esql.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
                    where
                         msmp2.MedPersonal_id = msmp.MedPersonal_id
                         and msmp2.MedService_id = MS.MedService_id
                         and eqi.ElectronicQueueInfo_IsOff = 1
                    limit 1
                ) eq on true
                left join lateral(
                    select
                        ebd.ElectronicScoreboard_id,
                        ebd.ElectronicScoreboard_IPaddress,
                        ebd.ElectronicScoreboard_Port
                    from
                        v_ElectronicScoreboard ebd
                        left join v_ElectronicScoreboardQueueLink esql on esql.ElectronicService_id = eq.ElectronicService_id
                    where
                        ebd.ElectronicScoreboard_id = esql.ElectronicScoreboard_id
                        and ebd.ElectronicScoreboard_IsLED = 2
                    limit 1
                ) eboard on true
                left join v_Lpu lpu on lpu.Lpu_id = MS.Lpu_id
                left join v_LpuSection ls on ls.LpuSection_id = MS.LpuSection_id
                left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
                left join v_LpuBuilding lb on lb.LpuBuilding_id = coalesce(ls.LpuBuilding_id,MS.LpuBuilding_id)
                left join lateral(
                    select
                        *
                    from
                        v_SmpUnitParam sup
                    where
                        sup.LpuBuilding_id = lb.LpuBuilding_id
                    order by sup.SmpUnitParam_id desc
                    limit 1
                ) sup on true
                left join v_SmpUnitType sut on sut.SmpUnitType_id = sup.SmpUnitType_id
                left join v_LpuUnit lu on lu.LpuUnit_id = coalesce(ls.LpuUnit_id,MS.LpuUnit_id)
                left join v_LpuUnitType lut on lut.LpuUnitType_id = coalesce(lu.LpuUnitType_id,MS.LpuUnitType_id)
                left join v_MedServiceType mst on mst.MedServiceType_id = MS.MedServiceType_id
                left join lateral (
                    select						
                        i_s.Storage_id,
                        i_s.Storage_pid
                    from
                        v_StorageStructLevel i_ssl
                        left join v_Storage i_s on i_s.Storage_id = i_ssl.Storage_id
                    where
                        i_ssl.MedService_id = MS.MedService_id
                    order by
                        i_ssl.StorageStructLevel_id
                    limit 1
                ) strg on true
			";
			//сотрудники служб
			$sql_medService = "
				select
				    --select
                    {$select_medService}
				    --end select 
				from 
					--from
					{$from_medService}
					--end from 
				where
					MS.Lpu_id = :Lpu_id 
				and
					(1=1)
                {$filter_medService} {$filter}
                and msmp.MedPersonal_id = :MedPersonal_id
                and mst.MedServiceType_SysNick in ('HTM','vk', 'mse', 'lab', 'pzm', 'func', 'patb', 'mstat', 'prock', 'dpoint', 'merch', 'regpol', 'sprst', 'okadr', 'minzdravdlo', 'leadermo', 'mekllo', 'spesexpertllo', 'adminllo', 'touz', 'reglab', 'oper_block', 'smp', 'slneotl', 'konsult', 'foodserv', 'vac', 'remoteconsultcenter','forenbiodprtwithmolgenlab','forenchemdprt','medforendprt','forenhistdprt','organmethdprt','forenmedcorpsexpdprt','forenmedexppersdprt','commcomplexp','forenareadprt', 'lvn', 'rpo', 'zmk', 'medosv', 'profosmotr', 'reanimation', 'microbiolab')
			";

			$select_medStaffFact_linked = "
			    msf.MedStaffFact_id as \"MedStaffFact_id\",
                msf.LpuSection_id as \"LpuSection_id\",
                msf.MedPersonal_id as \"MedPersonal_id\",
                coalesce(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
                coalesce(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
                coalesce(ps.PostMed_Name,'') as \"PostMed_Name\",
                ps.PostMed_Code as \"PostMed_Code\",
                ps.PostMed_id as \"PostMed_id\",
                lb.LpuBuilding_id as \"LpuBuilding_id\",
                coalesce(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
                lu.LpuUnit_id as \"LpuUnit_id\",
                lu.LpuUnitSet_id as \"LpuUnitSet_id\",
                coalesce(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
                case
                    when (select count(*) from v_TimetableGraf_lite tt where msf.MedStaffFact_id = tt.MedStaffFact_id and tt.TimetableGraf_Time is not null) > 0 
                    then 'true' else 'false'
                end as \"Timetable_isExists\",
                lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
                lu.LpuUnitType_id as \"LpuUnitType_id\",
                lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
                lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
                lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
                ls.LpuSectionAge_id as \"LpuSectionAge_id\",
                null::bigint as \"MedService_id\",
                null as \"MedService_Nick\",
                null as \"MedService_Name\",
                null::bigint as \"MedServiceType_id\",
                null as \"MedServiceType_SysNick\", 
                null::integer as \"MedService_IsExternal\",
                null::integer as \"MedService_IsLocalCMP\",
                null as \"MedService_LocalCMPPath\",
                msf.Person_FIO as \"MedPersonal_FIO\",
                Lpu.Org_id as \"Org_id\",
                Lpu.Lpu_id as \"Lpu_id\",
                Lpu.Lpu_Nick as \"Org_Nick\",
                Lpu.Lpu_Nick as \"Lpu_Nick\",
                msfl.MedStaffFactLink_id::bigint as \"MedStaffFactLink_id\",
                to_char(msfl.MedStaffFactLink_begDT, 'DD.MM.YYYY') as \"MedStaffFactLink_begDT\",
                to_char(msfl.MedStaffFactLink_endDT, 'DD.MM.YYYY') as \"MedStaffFactLink_endDT\",
                msf.MedStaffFactCache_IsDisableInDoc as \"MedStaffFactCache_IsDisableInDoc\",
                null as \"ElectronicQueueInfo_id\",
                null as \"ElectronicService_id\",
                null as \"ElectronicService_Num\",
                null as \"ElectronicQueueInfo_CallTimeSec\",
                null as \"ElectronicQueueInfo_PersCallDelTimeMin\",
                null as \"ElectronicQueueInfo_CallCount\",
                null as \"ElectronicService_isShownET\",
                null as \"ElectronicTreatment_ids\",
                null as \"ElectronicScoreboard_id\",
                null as \"ElectronicScoreboard_IPaddress\",
                null as \"ElectronicScoreboard_Port\",
                null::varchar as \"SmpUnitType_Code\",
                null::integer as \"SmpUnitParam_IsKTPrint\",
                null::bigint as \"Storage_id\",
                null::bigint as \"Storage_pid\",
                $persisFields
            ";

            // связанные места работы
			$sql_medStaffFact_linked = "
				select
					--select
					{$select_medStaffFact_linked}
					--end select
				from
				    --from
					v_MedStaffFactLink msfl
					inner join v_MedStaffFact msf on msf.MedStaffFact_id = msfl.MedStaffFact_id
					inner join v_MedStaffFact mmsf on mmsf.MedStaffFact_id = msfl.MedStaffFact_sid
					left join v_Lpu lpu on lpu.Lpu_id = msf.Lpu_id
					left join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
					left join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
					left join v_PostMed ps on ps.PostMed_id = msf.Post_id
					--end from
				where
					mmsf.MedPersonal_id = :MedPersonal_id
					and msf.Lpu_id = :Lpu_id
					and msf.MedStaffFact_Stavka > 0
					and lut.LpuUnitType_SysNick in ('polka','ccenter','traumcenter','fap')
					{$filter_medStaffFact} {$filter}
			";

			$select_medStaffFact_priem = " 
                msf.MedStaffFact_id as \"MedStaffFact_id\",
                ls.LpuSection_id as \"LpuSection_id\",
                msf.MedPersonal_id as \"MedPersonal_id\",
                coalesce(ls.LpuSection_FullName,'') as \"LpuSection_Name\",
                coalesce(ls.LpuSection_Name,'') as \"LpuSection_Nick\",
                coalesce(ps.PostMed_Name,'') as \"PostMed_Name\",
                ps.PostMed_Code as \"PostMed_Code\",
                ps.PostMed_id as \"PostMed_id\",
                lb.LpuBuilding_id as \"LpuBuilding_id\",
                coalesce(lb.LpuBuilding_Name,'') as \"LpuBuilding_Name\",
                lu.LpuUnit_id as \"LpuUnit_id\",
                lu.LpuUnitSet_id as \"LpuUnitSet_id\",
                coalesce(lu.LpuUnit_Name,'') as \"LpuUnit_Name\",
                case
                    when (select count(*) from v_TimetableStac_lite tt where tt.LpuSection_id = ls.LpuSection_id) > 0
                    then 'true' else 'false'
                end as \"Timetable_isExists\",
                lut.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
                lu.LpuUnitType_id as \"LpuUnitType_id\",
                lsp.LpuSectionProfile_SysNick as \"LpuSectionProfile_SysNick\",
                lsp.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
                lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
                ls.LpuSectionAge_id as \"LpuSectionAge_id\",
                null::bigint as \"MedService_id\",
                null as \"MedService_Nick\",
                null as \"MedService_Name\",
                null::bigint as \"MedServiceType_id\",
                null as \"MedServiceType_SysNick\", 
                null::integer as \"MedService_IsExternal\",
                null::integer as \"MedService_IsLocalCMP\",
                null as \"MedService_LocalCMPPath\",
                msf.Person_FIO as \"MedPersonal_FIO\",
                Lpu.Org_id as \"Org_id\",
                Lpu.Lpu_id as \"Lpu_id\",
                Lpu.Lpu_Nick as \"Org_Nick\",
                Lpu.Lpu_Nick as \"Lpu_Nick\",
                null::bigint as \"MedStaffFactLink_id\",
                null as \"MedStaffFactLink_begDT\",
                null as \"MedStaffFactLink_endDT\",
                msf.MedStaffFactCache_IsDisableInDoc,
                null as \"ElectronicQueueInfo_id\",
                null as \"ElectronicService_id\",
                null as \"ElectronicService_Num\",
                null as \"ElectronicQueueInfo_CallTimeSec\",
                null as \"ElectronicQueueInfo_PersCallDelTimeMin\",
                null as \"ElectronicQueueInfo_CallCount\",
                null as \"ElectronicService_isShownET\",
                null as \"ElectronicTreatment_ids\",
                null as \"ElectronicScoreboard_id\",
                null as \"ElectronicScoreboard_IPaddress\",
                null as \"ElectronicScoreboard_Port\",
                null::varchar as \"SmpUnitType_Code\",
                null::integer as \"SmpUnitParam_IsKTPrint\",
                null::bigint as \"Storage_id\",
                null::bigint as \"Storage_pid\",
                $persisFields
            ";

            // приемные отделения стационара
			$sql_medStaffFact_priem = "
				select
					--select
					{$select_medStaffFact_priem}
					--end select
				from
					v_LpuSection ls		
					inner join v_Lpu lpu on lpu.Lpu_id = ls.Lpu_id
					inner join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_LpuBuilding lb on lb.LpuBuilding_id = ls.LpuBuilding_id
					inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
					inner join v_LpuUnitType lut on lut.LpuUnitType_id = lu.LpuUnitType_id
					inner join lateral (
						select
							 Person_FIO,
							 Post_id,
							 MedicalCareKind_id,
							 PostKind_id,
							 MedStaffFact_id,
							 MedPersonal_id,
							 WorkData_begDate,
							 WorkData_endDate,
							 MedStaffFactCache_IsDisableInDoc
						from 
						    v_MedStaffFact
						where 
							LpuUnit_id = lu.LpuUnit_id
                        and
                            MedStaffFact_Stavka > 0
                        and
                            MedPersonal_id = :MedPersonal_id
                        and
                            WorkData_begDate <= (select date from cte)
                        and
                            (WorkData_endDate is null or WorkData_endDate >= (select date from cte))
                        and 
                            PostKind_id in (1, 10)
						limit 1
					) msf on true
					inner join v_PostMed ps on ps.PostMed_id = msf.Post_id
				where
					lut.LpuUnitType_SysNick = 'stac'
					and lsp.LpuSectionProfile_SysNick = 'priem'
					and ls.Lpu_id = :Lpu_id
					and coalesce(ls.LpuSection_setDate, (select date from cte)) <= (select date from cte)
					and coalesce(ls.LpuSection_disDate, (select date from cte)) >= (select date from cte)
					and not exists (
						select 
						    MedStaffFact_id
						from 
						    v_MedStaffFact
						where
						    LpuSection_id = ls.LpuSection_id
                        and
                            MedPersonal_id = :MedPersonal_id
                        and
                            WorkData_begDate <= (select date from cte)
                        and
                            (WorkData_endDate is null or WorkData_endDate >= (select date from cte))
                        limit 1
					)
					and (msf.PostKind_id = 1 or ps.PostMed_Code in (2, 6, 10002))
					{$filter}
			";

			if (!empty($data['StacPriemOnly']) && $data['StacPriemOnly'] == 2) {
				$sql = $sql_medStaffFact_priem;
			} elseif ($data['MedService_id']>0) {
				$sql = $sql_medService;
			} elseif ($data['MedStaffFact_id']>0) {
				$sql = "(" . $sql_medStaffFact . ") union all (" . $sql_workGraph . ") union all (" . $sql_medStaffFact_linked  . ") union all (" . $sql_medStaffFact_priem . ")";
			} else {
				$sql = "(" . $sql_medStaffFact . ") union all (" . $sql_workGraph . ") union all (" .$sql_medService . ") union all (" . $sql_medStaffFact_linked  . ") union all (" . $sql_medStaffFact_priem . ")";
			}
		}

		$sql = "
			with cte as (select dbo.tzGetDate() as date)
		" . $sql;


		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
}
