<?php	defined('BASEPATH') or die ('No direct script access allowed');

class VolPeriods_model extends swModel
{
    /**
     * конструктор
     */
    function __construct() {
            parent::__construct();
            $this->db = $this->load->database('registry', true);
            //$this->db = $this->load->database('promed_develop', true);
    }

    /**
     * Загрузка периода планирования
     */
    function loadVolPeriod($data) {
        $q = "
            select
                    VolPeriod_id, VolPeriod_begDate, VolPeriod_endDate, VolPeriod_Name
            from
                    r2.VolPeriods with (nolock)
            where
                    VolPeriod_id = :VolPeriod_id
        ";
        $r = $this->db->query($q, array('VolPeriod_id' => $data['VolPeriod_id']));
        if ( is_object($r) ) {
            $r = $r->result('array');
            if (isset($r[0])) {
                    return $r;
            } else {
                    return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка списка периодов планирования
     */
    function loadVolPeriodList($data) {
        
        $q = "SELECT
                        VolPeriod_id,
                        convert(varchar(10), VolPeriod_begDate, 104) VolPeriod_begDate,
                        convert(varchar(10), VolPeriod_endDate, 104) VolPeriod_endDate,
                        cast(VolPeriod_begDate as date) period_sort,
                        (convert(varchar(10), VolPeriod_begDate, 104) + ' - ' + convert(varchar(10), VolPeriod_endDate, 104)) as Period_TimeRange,
                        VolPeriod_Name			
                FROM
                        r2.VolPeriods WITH (NOLOCK)
                where
                        :VolPeriod_id is null or VolPeriod_id = :VolPeriod_id
                order by
                        VolPeriod_begDate
        ";
        
        $result = $this->db->query($q, $data);
        //$result = $this->db->
        if ( is_object($result) ) {
                return $result->result('array');
        } else {
                return false;
        }
    }

    /**
     * Загрузка заявки
     */
    function loadVolRequest($data) {
        $q = "select rq.Request_id, 
                    vm.SprVidMp_Name VolumeType_Name, 
                    count(distinct rl.Lpu_id) Request_LpuCount, 
                    rs.SprRequestStatus_Name, 
                    ISNULL(sum(case when rd.RequestData_AllowPlan=1 then rd.RequestData_Plan end),0) as Request_VolCount, 
                    rq.Request_DeviationPrct, 
                    rq.Request_Year, 
                    vm.SprVidMp_id 
                from r2.Request rq with (nolock)
                    left join r2.RequestList rl with (nolock) on rl.Request_id = rq.Request_id 
                    left join r2.RequestData rd with (nolock) on rd.RequestList_id = rl.RequestList_id 
                    inner join r2.SprVidMp vm with (nolock) on vm.SprVidMp_id = rq.SprVidMp_id 
                    inner join r2.SprRequestStatus rs with (nolock) on rs.SprRequestStatus_id = rq.Request_SprRequestStatus_id 
                where rq.Request_Year = :year
                group by rq.Request_id, vm.SprVidMp_Name, rs.SprRequestStatus_Name, rq.Request_DeviationPrct, rq.Request_Year, vm.SprVidMp_id 
                order by vm.SprVidMp_Name ";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
	 * Загрузка информации о заявке
	 */
	function loadRequestInfo($data) {
		$q = "
                select RL.Lpu_id,
                    Lpu_Nick, 
                    rl.LevelType_Code, 
                    isnull(cast (RL.PacCount as varchar (50)),'-') as PacCount, 
                    Request_Year, 
                    SprRequestStatus_Name, 
                    cast(RL.KfLimit as varchar(50)) as KfLimit,   
                    Case
						when ISNULL(MesTariffMaxPlan_Value,0)=0
						then '-'
						else cast(MesTariffMaxPlan_Value as varchar(50))
					end as MesTariffMaxPlan_Value, 
                    case
						when ISNULL(RL.KfLimit,0)>0
						then ISNULL(cast(
							Case
								when ISNULL(RL.KfLimit,0)<ISNULL(MesTariffMaxPlan_Value,0)
								then ISNULL(MesTariffMaxPlan_Value,0)-ISNULL(RL.KfLimit,0)
							end as varchar(50)),'-')
						else '-'
					end as IsBadValue, 
                    ISNULL(cast (RequestData_Plan.Volume as varchar (50)),'-') as Volume, 
                    ISNULL(cast (RequestData_Plan.VolumeOld as varchar (50)),'-') as VolumeOld, 
                    ISNULL(cast (RequestData_Plan.VolumeYoung as varchar (50)),'-') as VolumeYoung, 
                    case
						when case
								when ISNULL(max_Volume,0)>0
								then ISNULL(cast(
									case
										when ISNULL(ceiling(ISNULL(max_Volume,0)*
											case
												when ISNULL(rl.DevPrc,0)=0
												then 1 
                                                else rl.DevPrc
											end/100),0)<ISNULL(RequestData_Plan.Volume,0) 
                                        then ISNULL(RequestData_Plan.Volume,0)- (ISNULL(max_Volume,0)+ceiling(ISNULL(max_Volume,0)*
											case
												when ISNULL(rl.DevPrc,0)=0
												then 1 
                                                else rl.DevPrc
											end/100))
									end as varchar (50)),'-')
								else '-'
							end < 0
						then '-'
						else
                            case
								when ISNULL(max_Volume,0)>0
								then ISNULL (cast(
									case
										when ISNULL(ceiling(ISNULL(max_Volume,0)*
											case
												when ISNULL(rl.DevPrc,0)=0
												then 1 
                                                else rl.DevPrc
											end/100),0)<ISNULL(RequestData_Plan.Volume,0) 
										then ISNULL(RequestData_Plan.Volume,0)- (ISNULL(max_Volume,0)+ceiling(ISNULL(max_Volume,0)*
											case
												when ISNULL(rl.DevPrc,0)=0
												then 1 
												else rl.DevPrc
											end/100))
									end as varchar (50)),'-')
								else '-'
							end
					end as IsBadVolume, 
                    ISNULL(cast (RL.KpAdults + RL.KpKids as varchar (50)),'-') as Kp,
                    ISNULL(cast (RL.KpAdults as varchar (50)),'-') as KpAdults, 
                    ISNULL(cast (RL.KpKids as varchar (50)),'-') as KpKids, 
                    ISNULL(cast(Case
                                    when ISNULL(RL.KpAdults,0) + ISNULL(RL.KpKids,0)>0
                                    then ISNULL(RL.KpAdults,0) + ISNULL(RL.KpKids,0)
                                        -(RequestData_Plan.VolumeKP
                                            + RequestData_Plan.VolumeKPDispNab
                                            + RequestData_Plan.VolumeKPRazObrCount
                                            + RequestData_Plan.VolumeKPMidMedStaff
                                            + RequestData_Plan.VolumeKPOtherPurp)
                                end as varchar(50)),'-') as IsBadVolKP, 
                    ISNULL(cast (RequestData_Plan.VolumeKP
                                + RequestData_Plan.VolumeKPDispNab
                                + RequestData_Plan.VolumeKPRazObrCount
                                + RequestData_Plan.VolumeKPMidMedStaff
                                + RequestData_Plan.VolumeKPOtherPurp as varchar (50)),'-') as VolumeKP, 
                    ISNULL(cast (RequestData_Plan.VolumeKPOld
                                + RequestData_Plan.VolumeKPDispNabOld
                                + RequestData_Plan.VolumeKPRazObrCountOld
                                + RequestData_Plan.VolumeKPMidMedStaffOld
                                + RequestData_Plan.VolumeKPOtherPurpOld as varchar (50)),'-') as VolumeKPOld, 
                    ISNULL(cast (RequestData_Plan.VolumeKPYoung
                                + RequestData_Plan.VolumeKPDispNabYoung
                                + RequestData_Plan.VolumeKPRazObrCountYoung
                                + RequestData_Plan.VolumeKPMidMedStaffYoung
                                + RequestData_Plan.VolumeKPOtherPurpYoung as varchar (50)),'-') as VolumeKPYoung,
					
					ISNULL(cast(Case when RL.DispNabKP is not null then RL.DispNabKP-ISNULL(RequestData_Plan.VolumeKPDispNab,0) end as varchar(50)),'-') as IsBadVolKPDispNab,
					ISNULL(cast(Case when RL.RazObrCountKP is not null then RL.RazObrCountKP-ISNULL(RequestData_Plan.VolumeKPRazObrCount,0) end as varchar(50)),'-') as IsBadVolKPRazObrCount,
					ISNULL(cast(Case when RL.MidMedStaffKP is not null then RL.MidMedStaffKP-ISNULL(RequestData_Plan.VolumeKPMidMedStaff,0) end as varchar(50)),'-') as IsBadVolKPMidMedStaff,
					ISNULL(cast(Case when RL.OtherPurpKP is not null then RL.OtherPurpKP-ISNULL(RequestData_Plan.VolumeKPOtherPurp,0) end as varchar(50)),'-') as IsBadVolKPOtherPurp,
					
					ISNULL(cast (RequestData_Plan.VolumeKPDispNab as varchar (50)),'-') as VolumeKPDispNab, 
                    ISNULL(cast (RequestData_Plan.VolumeKPDispNabOld as varchar (50)),'-') as VolumeKPDispNabOld, 
                    ISNULL(cast (RequestData_Plan.VolumeKPDispNabYoung as varchar (50)),'-') as VolumeKPDispNabYoung,
					ISNULL(cast (RequestData_Plan.VolumeKPRazObrCount as varchar (50)),'-') as VolumeKPRazObrCount, 
                    ISNULL(cast (RequestData_Plan.VolumeKPRazObrCountOld as varchar (50)),'-') as VolumeKPRazObrCountOld, 
                    ISNULL(cast (RequestData_Plan.VolumeKPRazObrCountYoung as varchar (50)),'-') as VolumeKPRazObrCountYoung,
					ISNULL(cast (RequestData_Plan.VolumeKPMidMedStaff as varchar (50)),'-') as VolumeKPMidMedStaff, 
                    ISNULL(cast (RequestData_Plan.VolumeKPMidMedStaffOld as varchar (50)),'-') as VolumeKPMidMedStaffOld, 
                    ISNULL(cast (RequestData_Plan.VolumeKPMidMedStaffYoung as varchar (50)),'-') as VolumeKPMidMedStaffYoung,
					ISNULL(cast (RequestData_Plan.VolumeKPOtherPurp as varchar (50)),'-') as VolumeKPOtherPurp, 
                    ISNULL(cast (RequestData_Plan.VolumeKPOtherPurpOld as varchar (50)),'-') as VolumeKPOtherPurpOld, 
                    ISNULL(cast (RequestData_Plan.VolumeKPOtherPurpYoung as varchar (50)),'-') as VolumeKPOtherPurpYoung,
					RL.DispNabKP,
					RL.RazObrCountKP,
					RL.MidMedStaffKP,
					RL.OtherPurpKP
					
            from r2.v_RequestList RL WITH(NOLOCK)
			
                    outer apply ( 
                        select 
                        ISNULL(ROUND(CAST(sum(ISNULL(Rd.RequestData_Plan,0)*MesTariff_KSG.MesTariff_Value)/case when sum(ISNULL(rd.RequestData_Plan,0))=0 then 1 else  sum(ISNULL(rd.RequestData_Plan,0)) end as numeric (10,3)),2),0) as MesTariffMaxPlan_Value, 
                        sum(isnull(rd.RequestData_Plan,0)) as Volume, 
                        sum(case when rd.MesAgeGroup_id=1 then isnull(rd.RequestData_Plan,0) end) as VolumeOld, 
                        sum(case when rd.MesAgeGroup_id=2 then isnull(rd.RequestData_Plan,0) end) as VolumeYoung, 
                        sum(isnull(rd.RequestData_PlanKP,0)) as VolumeKP, 
                        sum(case when rd.MesAgeGroup_id=1 then isnull(rd.RequestData_PlanKP,0) end) as VolumeKPOld, 
                        sum(case when rd.MesAgeGroup_id=2 then isnull(rd.RequestData_PlanKP,0) end) as VolumeKPYoung, 
						
						sum(isnull(rd.DispNabPlanKP,0)) as VolumeKPDispNab, 
                        sum(case when rd.MesAgeGroup_id=1 then isnull(rd.DispNabPlanKP,0) end) as VolumeKPDispNabOld, 
                        sum(case when rd.MesAgeGroup_id=2 then isnull(rd.DispNabPlanKP,0) end) as VolumeKPDispNabYoung,
						sum(isnull(rd.RazObrCountPlanKP,0)) as VolumeKPRazObrCount, 
                        sum(case when rd.MesAgeGroup_id=1 then isnull(rd.RazObrCountPlanKP,0) end) as VolumeKPRazObrCountOld, 
                        sum(case when rd.MesAgeGroup_id=2 then isnull(rd.RazObrCountPlanKP,0) end) as VolumeKPRazObrCountYoung,
						sum(isnull(rd.MidMedStaffPlanKP,0)) as VolumeKPMidMedStaff, 
                        sum(case when rd.MesAgeGroup_id=1 then isnull(rd.MidMedStaffPlanKP,0) end) as VolumeKPMidMedStaffOld, 
                        sum(case when rd.MesAgeGroup_id=2 then isnull(rd.MidMedStaffPlanKP,0) end) as VolumeKPMidMedStaffYoung,
						sum(isnull(rd.OtherPurpPlanKP,0)) as VolumeKPOtherPurp, 
                        sum(case when rd.MesAgeGroup_id=1 then isnull(rd.OtherPurpPlanKP,0) end) as VolumeKPOtherPurpOld, 
                        sum(case when rd.MesAgeGroup_id=2 then isnull(rd.OtherPurpPlanKP,0) end) as VolumeKPOtherPurpYoung,
						
                        sum(isnull(rd.VolCount1,0)) as V1, 
                        sum(isnull(rd.VolCount2,0)) as V2, 
                        sum(isnull(rd.VolCount3,0)) as V3, 
                        sum(isnull(rd.VolCount4,0)) as V4 
                    FROM r2.RequestData RD WITH(NOLOCK)
					
                    outer apply ( 
                        select top 1 ISNULL(MesTariff_KSG.MesTariff_Value,0) as MesTariff_Value 
                        from MesTariff MesTariff_KSG with (nolock) 
                        where Rd.Mes_id  = MesTariff_KSG.Mes_id 
                        order by MesTariff_KSG.MesTariff_begDT desc 
                    )MesTariff_KSG 
                    where RD.RequestData_AllowPlan=1 
                    and RD.RequestList_id=RL.RequestList_id) AS RequestData_Plan 
                    cross apply (select top 1 cast(volCount as decimal) 
                                from (values (V1),(V2),(V3),(V4)) t(VolCount) 
                                order by VolCount desc 
                    ) ca (max_Volume) 
                    where rl.RequestList_id = :RequestList_id
                ";
		$result = $this->db->query($q, $data);
		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
    
    /**
     * Загрузка списка лицензий
     */
    function loadLicenceList($data) {
        $q = "Select
                        Lpu_id,
                        LpuLicence_id,
                        LpuLicence_Ser,
                        LpuLicence_Num,
                        RTrim(IsNull(convert(varchar,cast(LpuLicence_setDate as datetime),104),'')) as LpuLicence_setDate,
                        LpuLicence_RegNum,
                        VidDeat.VidDeat_id,
                        VidDeat.VidDeat_Name,
                        RTrim(IsNull(convert(varchar,cast(LpuLicence_begDate as datetime),104),'')) as LpuLicence_begDate,
                        RTrim(IsNull(convert(varchar,cast(LpuLicence_endDate as datetime),104),'')) as LpuLicence_endDate
                from dbo.v_LpuLicence LpuLicence (nolock)
                inner join dbo.VidDeat VidDeat (nolock) on LpuLicence.VidDeat_id=VidDeat.VidDeat_id
                where VidDeat_Code='4' and 
                        Lpu_id = :lpu_id and 
                        ISNULL(YEAR(LpuLicence_endDate),2019)>=2019
                ";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка заявок
     */
    function loadVolRequestList($data) {
		$filter = "1=1";
		$params = array();
		//            if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		//		{
		//			return false;
		//		}
		if (!empty($data['mo_name'])) {
			$filter .= " and (rl.Lpu_id = :Lpu_Nick or rl.Lpu_id=Lpu_oid)  ";
			$params['Lpu_Nick'] = $data['mo_name'];
		}
		else {
			$params['Lpu_Nick'] = 0;
		}
		if (!empty($data['mo_lvl'])) {
			$filter .= " and rl.LevelType_id = :LpuLevel_Name";
			$params['LpuLevel_Name'] = $data['mo_lvl'];
		}
		if (!empty($data['request_status'])) {
			if ($data['mz'] == 1) {
				$filter .= " and SprRequestStatus_id = :SprRequestStatus_Name";
				$params['SprRequestStatus_Name'] = $data['request_status'];
			}
			else {
				$filter .= " and SprRequestStatus_id not in (1)";
				$params['SprRequestStatus_Name'] = $data['request_status'];
			}
		}
		if (!empty($data['VolRequest_id'])) {
			$filter .= " and rl.Request_id = :VolRequest_id";
			$params['VolRequest_id'] = $data['VolRequest_id'];
		}
		$params['VolRequest_id'] = $data['VolRequest_id'];
		$q = "select	Request_id as VolRequest_id 
                            ,Lpu_Nick as mo 
                            ,LevelType_Code  as mo_lvl
                            ,SprRequestStatus_id as status_id
                            ,SprRequestStatus_Name  as status_name 
                            ,doControl
                            ,KfLimit 
                            ,KfPlan.MesTariffMaxPlan_Value as KfPlan 
                            ,DevPrc 
                            ,Kp
                            ,KpAdults
                            ,KpKids
                            ,Comment
                            ,PacCount 
                            ,Lpu_id 
                            ,SprVidMp_id 
                            ,RequestList_id 
                            ,VolCount
							,DispNabKP
							,RazObrCountKP
							,MidMedStaffKP
							,OtherPurpKP
                   from r2.v_RequestList RL  with (nolock) 
                    outer apply 
	 (select top 1 ISNULL(lpu_oid,Lpu_id) as Lpu_oid 
	 from v_lpuUnitset LUS  with (nolock) 
	 where LUS.Lpu_id=:Lpu_Nick
	 and ISNULL(Lpu_oid,Lpu_id)<>Lpu_id) OID 

	OUTER apply (select 
	 case when sum(isnull(rd.RequestData_Plan,0)) = 0 then 0 else ISNULL(ROUND(CAST(sum(ISNULL(Rd.RequestData_Plan,0)*MesTariff_KSG.MesTariff_Value)/sum(isnull(rd.RequestData_Plan,0)) as numeric (10,3)),2),0) end as MesTariffMaxPlan_Value 
	FROM r2.RequestData RD with (nolock) 
	outer apply ( 
	 select top 1 MesTariff_KSG.MesTariff_Value
	 from MesTariff MesTariff_KSG with (nolock) 
	 where Rd.Mes_id  = MesTariff_KSG.Mes_id 
	 order by MesTariff_KSG.MesTariff_begDT desc 
	)MesTariff_KSG 

	where RD.RequestData_AllowPlan=1 and RD.RequestList_id=RL.RequestList_id) as KfPlan 

	 where 
        {$filter}";
		log_message('error', var_export(array('q' => $q,'p' => $params,'e' => sqlsrv_errors()) , true));
		$result = $this->db->query($q, $params, true);

		if (is_object($result)) {
			return $result->result('array');

			//return $this->getPagingResponse($q, $params, $data['start'], $data['limit'], true);

		}
		else {
			return false;
		}
	}

    /**
     * Загрузка заявки КС КПГ/КСГ
     */
    function loadRequestDataStacKSG($data) {
        $params = array();
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(MO.Mes_Name) like upper('%'+ :objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
                
                $query = "
                    select 
                                    rd.RequestData_id,
                                    cast(o.SprPlanObj_id as int) as SprPlanObj_id, 
                                    o.SprPlanObj_Code  + '. ' + o.SprPlanObj_Name + ' - ' + ISNULL(cast (round(MesTariff_KPG.MesTariff_Value,2) as Varchar (50)),'0') + '. КП - ' + cast(isnull(kp.Kp_Value,'') as varchar) as SprPlanObj_Name, 
                                    MesTariff_KPG.MesTariff_Value as MesTariff_ValueKpg,
                                    MO.Mes_code as KsgCur,
                                    isnull(mo.MesOld_Num,'') + '. ' + MO.Mes_Name as KsgCurName,
                                    MesTariff_KSG.MesTariff_Value as KsgCurKf,
                                    MO2.Mes_Code as KsgNew,
                                    isnull(mo2.MesOld_Num,'') + '. ' + MO2.Mes_Name as KsgNewName,
                                    MesTariff_KSG2.MesTariff_Value as KsgNewKf,
                                    case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                                    RD.VolCount1,
                                    RD.VolCount2,
                                    RD.VolCount3,
                                    RD.VolCount4,
                                    RD.SluchCountOwn1,
                                    RD.SluchCountZone1,
                                    RD.RequestData_Plan,
                                    RD.RequestData_KP,
                                    RD.RequestData_PlanKP,
                                    RD.RequestData_Comment

                    from  r2.v_RequestDataEvnPSMes RD with (nolock)
                                    join r2.SprPlanObj o with (nolock) on o.SprVidMp_id=rd.SprVidMp_id and o.SprPlanObj_Code = rd.Mes_Code_Kpg and o.SprPlanCat_id <> 43118
                                    left join (select k.RequestList_id,
                                                     k.SprPlanObj_id,
                                                     k.MesAgeGroup_id,
                                                     k.PlanYear,
                                                     sum(isnull(k.SprPlanObjKp_Value,0)) as Kp_Value
                                                from r2.SprPlanObjKp k with (nolock)
                                                group by k.RequestList_id,
                                                     k.SprPlanObj_id,
                                                     k.MesAgeGroup_id,
                                                     k.PlanYear
                                                ) kp on kp.PlanYear = rd.Request_Year 
                                                        and kp.SprPlanObj_id = O.SprPlanObj_id
                                                        and (kp.MesAgeGroup_id = rd.MesAgeGroup_id or kp.MesAgeGroup_id = null)
                                                        and kp.RequestList_id = rd.RequestList_id
                                    inner join MesOld MO with (nolock) on MO.Mes_id=RD.Mes_id
                                    FULL join MesLink ML with (nolock) on RD.Mes_id=ML.Mes_id and YEAR(ML.MesLink_begDT)=rd.Request_Year
                                    LEFT JOIN MesOld MO2 with (nolock) on MO2.Mes_id=ML.Mes_sid
                                    outer apply (
                                            select top 1 MesTariff_KSG.MesTariff_Value
                                            from MesTariff MesTariff_KSG with (nolock)
                                            where rd.Mes_id = MesTariff_KSG.Mes_id
                                            order by MesTariff_KSG.MesTariff_begDT desc
                                    )MesTariff_KSG

                                    outer apply (
                                            select top 1 MesTariff_KPG.MesTariff_Value
                                            from MesOld with (nolock) 
                                            inner join MesTariff MesTariff_KPG with (nolock) on MesOld.Mes_id=MesTariff_KPG.Mes_id and MesTariff_KPG.MesPayType_id=case when MO.MesType_id=13 then 9
                                                                                                                                                                        when MO.MesType_id=14 then 10 else MO.MesType_id end
                                            where o.SprPlanObj_Code = MesOld.Mes_Code
                                                    and MesType_id=4
                                                    and MesTariff_KPG.MesTariff_begDT<=RD.RequestData_insDT 
                                                    and (MesTariff_KPG.MesTariff_EndDT>=RD.RequestData_insDT or MesTariff_KPG.MesTariff_endDT is null)
                                            order by MesTariff_KPG.MesTariff_begDT desc
                                    )MesTariff_KPG
                                    outer apply (
                                                                    select top 1 MesTariff_KSG2.MesTariff_Value
                                                                    from MesTariff MesTariff_KSG2 with (nolock)
                                                                    where MO2.Mes_id = MesTariff_KSG2.Mes_id
                                                                    order by MesTariff_KSG2.MesTariff_begDT desc
                                    )MesTariff_KSG2
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id = :SprPlanCat_id
                    {$filter}
                    order by  cast (o.SprPlanObj_Code as float),cast (ISNULL(MO2.Mes_Code,MO.Mes_Code) as float) asc
            ";
            }
            else 
            {
                $query = "
                    
                    select ROW_NUMBER() OVER(ORDER BY cast(isnull(q.KsgCur,q.KsgNew) as float) ASC) AS RN,
                                    q.SprPlanObj_id,
                                    q.SprPlanObj_Name,
                                    q.KsgCur,
                                    q.KsgCurName,
                                    q.KsgCurKf,
                                    q.KsgNew,
                                    q.KsgNewName,
                                    q.KsgNewKf,
                                    sum(q.VolCount1) as VolCount1,
                                    sum(q.VolCount2) as VolCount2,
                                    sum(q.VolCount3) as VolCount3,
                                    sum(q.VolCount4) as VolCount4,
                                    sum(q.SluchCountOwn1) as SluchCountOwn1,
                                    sum(case when q.MesAgeGroup_id=1 then isnull(q.SluchCountOwn1,0) end) as SluchCountOwn1Adults, 
                                    sum(case when q.MesAgeGroup_id=2 then isnull(q.SluchCountOwn1,0) end) as SluchCountOwn1Kids, 
                                    sum(isnull(q.SluchCountZone1,0)) as SluchCountZone1,
                                    sum(case when q.MesAgeGroup_id=1 then q.SluchCountZone1 end) as SluchCountZone1Adults, 
                                    sum(case when q.MesAgeGroup_id=2 then q.SluchCountZone1 end) as SluchCountZone1Kids, 
                                    sum(case when q.RequestData_AllowPlan=1 then isnull(q.RequestData_Plan,0) end) as RequestData_Plan, 
                                    sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=1 then isnull(q.RequestData_Plan,0) end) as RequestData_PlanOld, 
                                    sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=2 then isnull(q.RequestData_Plan,0) end) as RequestData_PlanYoung, 
                                    sum(case when q.RequestData_AllowPlan=1 then isnull(q.RequestData_PlanKP,0) end) as RequestData_PlanKP, 
                                    sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=1 then isnull(q.RequestData_PlanKP,0) end) as RequestData_PlanKPOld, 
                                    sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=2 then isnull(q.RequestData_PlanKP,0) end) as RequestData_PlanKPYoung 
                    from (select 
                                    rd.RequestData_id,
                                    cast(o.SprPlanObj_id as int) as SprPlanObj_id, 
                                    cast(o.SprPlanObj_Code as varchar(50)) + '. ' + o.SprPlanObj_Name + ' - ' + ISNULL(cast (round(MesTariff_KPG.MesTariff_Value,2) as Varchar (50)),'0') + '. КП - ' + cast(isnull(kp.Kp_Value,0) as varchar) as SprPlanObj_Name, 
                                    MesTariff_KPG.MesTariff_Value as MesTariff_ValueKpg,
                                    MO.Mes_code as KsgCur,
                                    isnull(mo.MesOld_Num,'') + '. ' + MO.Mes_Name as KsgCurName,
                                    MesTariff_KSG.MesTariff_Value as KsgCurKf,
                                    MO2.Mes_Code as KsgNew,
                                    isnull(mo2.MesOld_Num,'') + '. ' + MO2.Mes_Name as KsgNewName,
                                    MesTariff_KSG2.MesTariff_Value as KsgNewKf,
                                    rd.MesAgeGroup_id,
                                    RD.RequestData_AllowPlan,
                                    RD.VolCount1,
                                    RD.VolCount2,
                                    RD.VolCount3,
                                    RD.VolCount4,
                                    RD.SluchCountOwn1,
                                    RD.SluchCountZone1,
                                    RD.RequestData_Plan,
                                    RD.RequestData_PlanKP,
                                    RD.RequestData_Comment

                    from  r2.v_RequestDataEvnPSMes RD with (nolock)
                                    join r2.SprPlanObj o with (nolock) on o.SprVidMp_id=rd.SprVidMp_id and cast(o.SprPlanObj_Code as varchar(50)) = rd.Mes_Code_Kpg and o.SprPlanCat_id <> 43118
                                    left join (select k.RequestList_id,
                                                     k.SprPlanObj_id,
                                                     k.PlanYear,
                                                     sum(isnull(k.SprPlanObjKp_Value,0)) as Kp_Value
                                                from r2.SprPlanObjKp k with (nolock)
                                                group by k.RequestList_id,
                                                     k.SprPlanObj_id,
                                                     k.PlanYear
                                                ) kp on kp.PlanYear = rd.Request_Year 
                                                        and kp.SprPlanObj_id = O.SprPlanObj_id
                                                        and kp.RequestList_id = rd.RequestList_id
                                    inner join MesOld MO with (nolock) on MO.Mes_id=RD.Mes_id
                                    FULL join MesLink ML with (nolock) on RD.Mes_id=ML.Mes_id and YEAR(ML.MesLink_begDT)=rd.Request_Year
                                    LEFT JOIN MesOld MO2 with (nolock) on MO2.Mes_id=ML.Mes_sid
                                    outer apply (
                                            select top 1 MesTariff_KSG.MesTariff_Value
                                            from MesTariff MesTariff_KSG with (nolock)
                                            where rd.Mes_id = MesTariff_KSG.Mes_id
                                            order by MesTariff_KSG.MesTariff_begDT desc
                                    )MesTariff_KSG

                                    outer apply (
                                            select top 1 MesTariff_KPG.MesTariff_Value
                                            from MesOld with (nolock) 
                                            inner join MesTariff MesTariff_KPG with (nolock) on MesOld.Mes_id=MesTariff_KPG.Mes_id and MesTariff_KPG.MesPayType_id=case when MO.MesType_id=13 then 9
                                                                                                                                                                        when MO.MesType_id=14 then 10 else MO.MesType_id end
                                            where o.SprPlanObj_Code = MesOld.Mes_Code
                                                    and MesType_id=4
                                                    and MesTariff_KPG.MesTariff_begDT<=RD.RequestData_insDT 
                                                    and (MesTariff_KPG.MesTariff_EndDT>=RD.RequestData_insDT or MesTariff_KPG.MesTariff_endDT is null)
                                            order by MesTariff_KPG.MesTariff_begDT desc
                                    )MesTariff_KPG
                                    outer apply (
                                                                    select top 1 MesTariff_KSG2.MesTariff_Value
                                                                    from MesTariff MesTariff_KSG2 with (nolock)
                                                                    where MO2.Mes_id = MesTariff_KSG2.Mes_id
                                                                    order by MesTariff_KSG2.MesTariff_begDT desc
                                    )MesTariff_KSG2
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id = :SprPlanCat_id
                    {$filter}
                    --order by  cast (o.SprPlanObj_Code as float),cast (ISNULL(MO2.Mes_Code,MO.Mes_Code) as float) asc
					) q
					group by q.SprPlanObj_id,
							q.SprPlanObj_Name,
							q.KsgCur,
							q.KsgCurName,
							q.KsgCurKf,
							q.KsgNew,
							q.KsgNewName,
							q.KsgNewKf
            ";
            }
        }
        
        $res = $this->db->query($query, $params);

        if (is_object($res))
                return $res->result('array');
        else
                return false;
    }
    
    /**
     * Загрузка заявки ДС КПГ/КСГ
     */
    function loadRequestDataDSKSG($data) {
        $params = array();
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(MO.Mes_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if( !empty($data['SprPlanCat_id']) ) 
        {
            $filter .= " and upper(rd.SprPlanCat_id) like upper('%'+:SprPlanCat_id + '%')";
            $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
                
                $query = "
                    select 
                                    rd.RequestData_id,
                                    cast(o.SprPlanObj_id as int) as SprPlanObj_id, 
                                    o.SprPlanObj_Code  + '. ' + o.SprPlanObj_Name + ' - ' + ISNULL(cast (MesTariff_KPG.MesTariff_Value as Varchar (50)),'0') + '. КП - ' + cast(isnull(kp.SprPlanObjKp_Value,'') as varchar) as SprPlanObj_Name, 
                                    MesTariff_KPG.MesTariff_Value as MesTariff_ValueKpg,
                                    MO.Mes_code as KsgCur,
                                    isnull(mo.MesOld_Num,'') + '. ' + MO.Mes_Name as KsgCurName,
                                    MesTariff_KSG.MesTariff_Value as KsgCurKf,
                                    MO2.Mes_Code as KsgNew,
                                    isnull(mo2.MesOld_Num,'') + '. ' + MO2.Mes_Name as KsgNewName,
                                    MesTariff_KSG2.MesTariff_Value as KsgNewKf,
                                    case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                                    RD.VolCount1,
                                    RD.VolCount2,
                                    RD.VolCount3,
                                    RD.VolCount4,
                                    RD.RequestData_Plan,
                                    RD.RequestData_PlanKP,
                                    RD.RequestData_KP,
                                    RD.RequestData_Comment

                    from  r2.v_RequestDataEvnPSMes RD with (nolock)
                                    join r2.SprPlanObj o with (nolock) on o.SprVidMp_id=rd.SprVidMp_id and o.SprPlanObj_Code = rd.Mes_Code_Kpg and o.SprPlanCat_id <> 43118
                                    left join r2.SprPlanObjKp kp on kp.PlanYear = rd.Request_Year 
                                                        and kp.SprPlanObj_id = O.SprPlanObj_id
                                                        and (kp.MesAgeGroup_id = rd.MesAgeGroup_id or kp.MesAgeGroup_id is null)
                                                        and kp.RequestList_id = rd.RequestList_id
                                    inner join MesOld MO with (nolock) on MO.Mes_id=RD.Mes_id
                                    FULL join MesLink ML with (nolock) on RD.Mes_id=ML.Mes_id and YEAR(ML.MesLink_begDT)=rd.Request_Year
                                    LEFT JOIN MesOld MO2 with (nolock) on MO2.Mes_id=ML.Mes_sid
                                    outer apply (
                                            select top 1 MesTariff_KSG.MesTariff_Value
                                            from MesTariff MesTariff_KSG with (nolock)
                                            where rd.Mes_id = MesTariff_KSG.Mes_id
                                            order by MesTariff_KSG.MesTariff_begDT desc
                                    )MesTariff_KSG

                                    outer apply (
                                            select top 1 MesTariff_KPG.MesTariff_Value
                                            from MesOld with (nolock) 
                                            inner join MesTariff MesTariff_KPG with (nolock) on MesOld.Mes_id=MesTariff_KPG.Mes_id and MesTariff_KPG.MesPayType_id=case when MO.MesType_id=13 then 9
                                                                                                                                                                        when MO.MesType_id=14 then 10 else MO.MesType_id end
                                            where o.SprPlanObj_Code = MesOld.Mes_Code
                                                    and MesType_id=4
                                                    and MesTariff_KPG.MesTariff_begDT<=RD.RequestData_insDT 
                                                    and (MesTariff_KPG.MesTariff_EndDT>=RD.RequestData_insDT or MesTariff_KPG.MesTariff_endDT is null)
                                            order by MesTariff_KPG.MesTariff_begDT desc
                                    )MesTariff_KPG
                                    outer apply (
                                                                    select top 1 MesTariff_KSG2.MesTariff_Value
                                                                    from MesTariff MesTariff_KSG2 with (nolock)
                                                                    where MO2.Mes_id = MesTariff_KSG2.Mes_id
                                                                    order by MesTariff_KSG2.MesTariff_begDT desc
                                    )MesTariff_KSG2
                    where rd.RequestList_id = :RequestList_id
                    {$filter}
                    order by  cast (o.SprPlanObj_Code as float),cast (ISNULL(MO2.Mes_Code,MO.Mes_Code) as float) asc
            ";
            }
            else 
            {
                $query = "
                    
                                    select 
                                   -- rd.RequestData_id, 
                                    cast(o.SprPlanObj_id as int) as SprPlanObj_id, 
                                    o.SprPlanObj_Code  + '. ' + o.SprPlanObj_Name + '. КП - ' + cast(isnull(kp.Kp_Value,'') as varchar) /*+ ' - ' + ISNULL(cast (MesTariff_KPG.MesTariff_Value as Varchar (50)),'0') */as SprPlanObj_Name, 
                                   -- MesTariff_KPG.MesTariff_Value as MesTariff_ValueKpg, 
                                    MO.Mes_code as KsgCur, 
                                    isnull(mo.MesOld_Num,'') + '. ' + MO.Mes_Name as KsgCurName,
                                    MesTariff_KSG.MesTariff_Value as KsgCurKf, 
                                    MO2.Mes_Code as KsgNew, 
                                    isnull(mo2.MesOld_Num,'') + '. ' + MO2.Mes_Name as KsgNewName, 
                                    MesTariff_KSG2.MesTariff_Value as KsgNewKf, 
                                  --  RD.RequestData_AllowPlan, 
                                    sum(RD.VolCount1)as VolCount1, 
                                    sum(RD.VolCount2)as VolCount2, 
                                    sum(RD.VolCount3)as VolCount3, 
                                    sum(RD.VolCount4)as VolCount4, 
                               sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids 
                                
                    from  r2.v_RequestDataEvnPSMes RD with (nolock) 
                                    join r2.SprPlanObj o with (nolock) on o.SprVidMp_id=rd.SprVidMp_id and o.SprPlanObj_Code = rd.Mes_Code_Kpg and o.SprPlanCat_id <> 43118
                                    inner join MesOld MO with (nolock) on MO.Mes_id=RD.Mes_id 
                                    FULL join MesLink ML with (nolock) on RD.Mes_id=ML.Mes_id and YEAR(ML.MesLink_begDT)=rd.Request_Year 
                                    LEFT JOIN MesOld MO2 with (nolock) on MO2.Mes_id=ML.Mes_sid
                                    left join (select k.RequestList_id,
                                                     k.SprPlanObj_id,
                                                     k.PlanYear,
                                                     sum(isnull(k.SprPlanObjKp_Value,0)) as Kp_Value
                                                from r2.SprPlanObjKp k with (nolock)
                                                group by k.RequestList_id,
                                                     k.SprPlanObj_id,
                                                     k.PlanYear
                                                ) kp on kp.PlanYear = rd.Request_Year 
                                                        and kp.SprPlanObj_id = O.SprPlanObj_id
                                                        and kp.RequestList_id = rd.RequestList_id
                                    outer apply ( 

             select top 1 MesTariff_KSG.MesTariff_Value 
                                            from MesTariff MesTariff_KSG with (nolock) 
                                            where rd.Mes_id = MesTariff_KSG.Mes_id 
                                            order by MesTariff_KSG.MesTariff_begDT desc 
                                    )MesTariff_KSG 

                                    outer apply (
                                            select top 1 MesTariff_KPG.MesTariff_Value
                                            from MesOld with (nolock) 
                                            inner join MesTariff MesTariff_KPG with (nolock) on MesOld.Mes_id=MesTariff_KPG.Mes_id and MesTariff_KPG.MesPayType_id=case when MO.MesType_id=13 then 9
                                                                                                                                                                        when MO.MesType_id=14 then 10 else MO.MesType_id end
                                            where o.SprPlanObj_Code = MesOld.Mes_Code
                                                    and MesType_id=4
                                                    and MesTariff_KPG.MesTariff_begDT<=RD.RequestData_insDT 
                                                    and (MesTariff_KPG.MesTariff_EndDT>=RD.RequestData_insDT or MesTariff_KPG.MesTariff_endDT is null)
                                            order by MesTariff_KPG.MesTariff_begDT desc
                                    )MesTariff_KPG 
                                    outer apply ( 
                                                                    select top 1 MesTariff_KSG2.MesTariff_Value 
                                                                    from MesTariff MesTariff_KSG2 with (nolock) 
                                                                    where MO2.Mes_id = MesTariff_KSG2.Mes_id 
                                                                    order by MesTariff_KSG2.MesTariff_begDT desc 
                                    )MesTariff_KSG2 
                    where rd.RequestList_id = :RequestList_id 
                    {$filter}

	 	 	GROUP BY   o.SprPlanObj_id, 
                               o.SprPlanObj_Code, 
	 	 	 	   o.SprPlanObj_Code  + '. ' + o.SprPlanObj_Name + '. КП - ' + cast(isnull(kp.Kp_Value,'') as varchar), 
	 	 	 	   MO.Mes_code, 
                               isnull(mo.MesOld_Num,'') + '. ' + MO.Mes_Name,
                                MesTariff_KSG.MesTariff_Value, 
                                MO2.Mes_Code, 
                                isnull(mo2.MesOld_Num,'') + '. ' + MO2.Mes_Name, 
                                MesTariff_KSG2.MesTariff_Value
            ";
            }
        }
        $res = $this->db->query($query, $params);

        if (is_object($res))
                return $res->result('array');
        else
                return false;
    }

    /**
     * Установить статус заявки
     */
    function setRequestStatus($data) {
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprRequestStatus_id'] = $data['SprRequestStatus_id'];
        $params['pmUser_id'] = $data['pmUser_id'];
        $q = "declare
                @RequestList_id bigint,
                @SprRequestStatus_id int,
                @ErrCode int,
                @ErrMessage varchar(4000);
            update r2.RequestList set
                    SprRequestStatus_id = :SprRequestStatus_id,
                    RequestStatus_updDT = dbo.tzGetDate(),
                    pmUser_RStatusID = :pmUser_id
            where RequestList_id = :RequestList_id
            select @RequestList_id as RequestData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
         ";
        //echo($q);
        $result = $this->db->query($q, $params, false);
        if ( is_object($result) ) {
                return $result->result('array');
        } else {
                return false;
        }
    }

    /**
     * Установить статус заявки
     */
    function setRequestStatusAll($data) {
        $params['Request_id'] = $data['Request_id'];
        $params['SprRequestStatus_id'] = $data['Status_id'];
        $params['pmUser_id'] = $data['pmUser_id'];
        $set = "@Request_id = :Request_id, 
                @Status_id = :SprRequestStatus_id,
                @pmUser_id = :pmUser_id";
        $q = "declare @Request_id bigint,
                    @ErrCode int,
                    @ErrMessage varchar(4000);	
                exec r2.p_SetVolRequestStatus
                        " . $set . "
                        ;
                select @Request_id as Request_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
         ";
        $result = $this->db->query($q, $params, false);
        if ( is_object($result) ) {
                return $result->result('array');
        } else {
                return false;
        }
    }
    
    /**
     * проставить галки объектам, имеющим лицензии
     */
    function updateLic($data) {
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['pmUser_id'] = $data['pmUser_id'];
        $q = "declare
                @RequestList_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            update r2.RequestData set
                    RequestData_AllowPlan = 1,
                    RequestData_updDT = dbo.tzGetDate(),
                    pmUser_updID = :pmUser_id
            where RequestList_id = :RequestList_id
            and (isnull(VolCount1,0) + isnull(VolCount2,0) + isnull(VolCount3,0) + isnull(VolCount4,0)) > 0
            and Mes_Code_Kpg in (select SprPlanObj_Code from r2.SprInfo with (nolock) where RequestList_id = :RequestList_id and LpuLicence_id is not null)
            select @RequestList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
         ";
        $result = $this->db->query($q, $params, false);
        if ( is_object($result) ) {
                return $result->result('array');
        } else {
                return false;
        }
    }

    /**
	 * Сохранить данные в списке заявок
	 */
	function saveRequestList($data) {
		$params = array();
		$params['RequestList_id'] = $data['RequestList_id'];

		$set = "@RequestList_id = :RequestList_id";

		if (!empty($data['KpAdults'])) {
			$set .= ", @KpAdults = :KpAdults";
			$params['KpAdults'] = $data['KpAdults'];
		}
		if (!empty($data['KpKids'])) {
			$set .= ", @KpKids = :KpKids";
			$params['KpKids'] = $data['KpKids'];
		}
		if (!empty($data['DevPrc'])) {
			$set .= ", @DevPrc = :DevPrc";
			$params['DevPrc'] = $data['DevPrc'];
		}
		if (!empty($data['KfLimit'])) {
			$set .= ", @KfLimit = :KfLimit";
			$params['KfLimit'] = $data['KfLimit'];
		}
		if (!empty($data['doControl'])) {
			$set .= ", @doControl = :doControl";
			$params['doControl'] = $data['doControl'];
		}
		if (!empty($data['Comment'])) {
			$set .= ", @Comment = :Comment";
			$params['Comment'] = $data['Comment'];
		}
		//if( !empty($data['DispNabKP']))
		//{
		$set .= ", @DispNabKP = :DispNabKP";
		$params['DispNabKP'] = $data['DispNabKP'];
		//}
		//if( !empty($data['RazObrCountKP']) )
		//{
		$set .= ", @RazObrCountKP = :RazObrCountKP";
		$params['RazObrCountKP'] = $data['RazObrCountKP'];
		//}
		//if( !empty($data['MidMedStaffKP']) )
		//{
		$set .= ", @MidMedStaffKP = :MidMedStaffKP";
		$params['MidMedStaffKP'] = $data['MidMedStaffKP'];
		//}
		//if( !empty($data['OtherPurpKP']) )
		//{
		$set .= ", @OtherPurpKP = :OtherPurpKP";
		$params['OtherPurpKP'] = $data['OtherPurpKP'];
		//}
		if (!empty($data['KpAdults_o'])) {
			$set .= ", @KpAdults_o = :KpAdults_o";
			$params['KpAdults_o'] = $data['KpAdults_o'];
		}
		if (!empty($data['KpKids_o'])) {
			$set .= ", @KpKids_o = :KpKids_o";
			$params['KpKids_o'] = $data['KpKids_o'];
		}
		if (!empty($data['DevPrc_o'])) {
			$set .= ", @DevPrc_o = :DevPrc_o";
			$params['DevPrc_o'] = $data['DevPrc_o'];
		}
		//if( !empty($data['KfLimit_o']) )
		//{
		$set .= ", @KfLimit_o = :KfLimit_o";
		$params['KfLimit_o'] = $data['KfLimit_o'];
		//}
		if (!empty($data['doControl_o'])) {
			$set .= ", @doControl_o = :doControl_o";
			$params['doControl_o'] = $data['doControl_o'];
		}
		if (!empty($data['Comment_o'])) {
			$set .= ", @Comment_o = :Comment_o";
			$params['Comment_o'] = $data['Comment_o'];
		}
		//if( !empty($data['DispNabKP_o']) )
		//{
		$set .= ", @DispNabKP_o = :DispNabKP_o";
		$params['DispNabKP_o'] = $data['DispNabKP_o'];
		//}
		//if( !empty($data['RazObrCountKP_o']) )
		//{
		$set .= ", @RazObrCountKP_o = :RazObrCountKP_o";
		$params['RazObrCountKP_o'] = $data['RazObrCountKP_o'];
		//}
		//if( !empty($data['MidMedStaffKP_o']) )
		//{
		$set .= ", @MidMedStaffKP_o = :MidMedStaffKP_o";
		$params['MidMedStaffKP_o'] = $data['MidMedStaffKP_o'];
		//}
		//if( !empty($data['OtherPurpKP_o']) )
		//{
		$set .= ", @OtherPurpKP_o = :OtherPurpKP_o";
		$params['OtherPurpKP_o'] = $data['OtherPurpKP_o'];
		//}
		$q = "declare @RequestList_id bigint,
                    @ErrCode int,
                    @ErrMessage varchar(4000);	
                exec r2.p_RequestList_upd
                        " . $set . "
                        ;
                select @RequestList_id as Request_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
         ";
		$result = $this->db->query($q, $params, false);
		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
	 * Сохранить данные в заявке
	 */
	function saveRequestData($data) {
		$params = array();
		$set = "@RequestData_id = :RequestData_id";
		$params['RequestData_id'] = $data['RequestData_id'];

		// массив принимаемых параметров
		// значение элемента массива - для возможности сброса параметра в 0 или ''
		// значение элемента может быть - '' (пустая строка) / '0' (ноль - цифра) / '-1' (игнор условия сброса)
		$paramNames = array(
			'RequestData_AllowPlan' => '-1',
			'EmerRoom' => '0',
			'RequestData_Plan' => '0',
			'VolCount1' => '0',
			'VolCount2' => '0',
			'VolCount3' => '0',
			'VolCount4' => '0',
			'RequestData_PlanKP' => '0',
			'RequestData_KP' => '0',
			'RequestData_Comment' => '0',
			'RequestData_AvgDur' => '0',
			'RequestData_BedCount' => '0',
			'RequestData_EstabPostCount' => '0',
			'RequestData_ActivePostCount' => '0',
			'RequestData_IndividCount' => '0',
			'RequestData_TeamCount' => '0',
			'LpuLicence_id' => '0',
			'SpecCertif_Num' => '0',
			'SpecCertif_endDate' => '0',
			'AssignedPacCount' => '0',
			'WomanCount' => '0',
			'SluchCountOwn1' => '0',
			'SluchCountZone1' => '0',
			'SluchCountOwn2' => '0',
			'SluchCountZone2' => '0',
			'ShiftCount' => '0',
			'PlaceCount' => '0',
			'AvgYearBed' => '0',
			'RazObrCount' => '0',
			'DispNab' => '0',
			'MedReab' => '0',
			'OtherPurp' => '0',
			'DispNabPlanKP' => '0',
			'RazObrCountPlanKP' => '0',
			'MidMedStaffPlanKP' => '0',
			'OtherPurpPlanKP' => '0',
			'PacCount' => '0',
			'TeamCount' => '0',
			'Post' => '0',
			'FIO' => '0',
			'Phone' => '0',
			'Email' => '0',
			'EmerRoom_o' => '0',
			'RequestData_Plan_o' => '0',
			'VolCount1_o' => '0',
			'VolCount2_o' => '0',
			'VolCount3_o' => '0',
			'VolCount4_o' => '0',
			'RequestData_PlanKP_o' => '0',
			'RequestData_KP_o' => '0',
			'RequestData_Comment_o' => '0',
			'RequestData_AvgDur_o' => '0',
			'RequestData_BedCount_o' => '0',
			'RequestData_EstabPostCount_o' => '0',
			'RequestData_ActivePostCount_o' => '0',
			'RequestData_IndividCount_o' => '0',
			'RequestData_TeamCount_o' => '0',
			'LpuLicence_id_o' => '0',
			'SpecCertif_Num_o' => '0',
			'SpecCertif_endDate_o' => '0',
			'AssignedPacCount_o' => '0',
			'WomanCount_o' => '0',
			'SluchCountOwn1_o' => '0',
			'SluchCountZone1_o' => '0',
			'SluchCountOwn2_o' => '0',
			'SluchCountZone2_o' => '0',
			'ShiftCount_o' => '0',
			'PlaceCount_o' => '0',
			'AvgYearBed_o' => '0',
			'RazObrCount_o' => '0',
			'DispNab_o' => '0',
			'MedReab_o' => '0',
			'OtherPurp_o' => '0',
			'DispNabPlanKP_o' => '0',
			'RazObrCountPlanKP_o' => '0',
			'MidMedStaffPlanKP_o' => '0',
			'OtherPurpPlanKP_o' => '0',
			'PacCount_o' => '0',
			'TeamCount_o' => '0',
			'Post_o' => '0',
			'FIO_o' => '0',
			'Phone_o' => '0',
			'Email_o' => '0'
		);
		foreach ($paramNames as $key => $val) {
			if (isset($data[$key]) && (!empty($data[$key]) || $data[$key] == $val)) {
				//если передан параметр, добавляем его в запрос
				//$val - для возможности записи пустого значения '' (пустой текст) или '0' (ноль - цифровое значение)
				$set .= " , @" . $key . " = :" . $key;
				$params[$key] = $data[$key];
			}
		}

		$q = " declare @RequestData_id bigint,
                    @ErrCode int,
                    @ErrMessage varchar(4000);	
                exec r2.p_SaveRequestData
                        " . $set . "
                        ;
                select @RequestData_id as RequestData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
		//sql_log_message('error','exec-p_SaveRequestData-query: ',getDebugSql($q, $params));
		$r = $this->db->query($q, $params);
		if (is_object($r)) {
			$result = $r->result('array');
			//echo($result[0]['RequestData_id']);
			$this->RequestData_id = $result[0]['RequestData_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q,'p' => $params,'e' => sqlsrv_errors()
			) , true));
			$result = array(
				array(
					'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'
				)
			);
		}
		return $result;
	}
    
    /**
     * Сохранить данные по категориям
     */
    function savePlanCatData($data) {
        $params = array();
        $set = "@RequestList_id = :RequestList_id,
                @SprPlanCat_id = :SprPlanCat_id,
                @pmUser_id = :pmUser_id";
        
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        $params['pmUser_id'] = $data['pmUser_id'];
		
        $paramNames = array(
			'PlanCatData_KP' => '0',
			'PlanCatData_KP_o' => '0',
            'PlanCatData_KpAdults' => '0',
			'PlanCatData_KpAdults_o' => '0',
            'PlanCatData_KpKids' => '0',
			'PlanCatData_KpKids_o' => '0',
            'PlanCatData_PlanKpAdults' => '0',
			'PlanCatData_PlanKpAdults_o' => '0',
            'PlanCatData_PlanKpKids' => '0',
			'PlanCatData_PlanKpKids_o' => '0',
            'PlanCatData_KpEmer' => '0',
            'PlanCatData_KpEmer_o' => '0'
                    );
        
        foreach ($paramNames as $key=>$val) 
        {
            if( isset($data[$key]) && ( !empty($data[$key]) || $data[$key] == $val ) ) 
            {
                $set .= " , @".$key." = :".$key;
                $params[$key] = $data[$key];
            }
        }
        
        $q = " declare @RequestList_id bigint,
                    @ErrCode int,
                    @ErrMessage varchar(4000);	
                exec r2.p_SavePlanCatData
                        " . $set . "
                        ;
                select @RequestList_id as RequestList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
		//sql_log_message('error','exec-p_SaveRequestData-query: ',getDebugSql($q, $params));
        $r = $this->db->query($q, $params);
        if ( is_object($r) ) 
        {
            $result = $r->result('array');
            //echo($result[0]['RequestData_id']);
            $this->RequestList_id = $result[0]['RequestList_id'];
        } 
        else 
        {
            log_message('error', var_export(array('q' => $q, 'p' => $params, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Сохранить данные в справочной информации
     */
    function saveSprInfo($data) {
        $set = '';
        $params = array();
        $params['SprInfo_id'] = $data['SprInfo_id'];
        
        if( isset($data['Comment']) && ( !empty($data['Comment']) || $data['Comment'] == '') )
        {
            $set .= " , @Comment = :Comment";
            $params['Comment'] = $data['Comment'];
        }
        
		if( isset($data['Duration']) && ( !empty($data['Duration']) || $data['Duration'] == '0' ) )
        {
            $set .= " , @Duration = :Duration";
            $params['Duration'] = $data['Duration'];
        }
        /*
        if( !empty($data['BedCount']) || $data['BedCount'] == 0 ) 
        {
            $set .= " , @BedCount = :BedCount";
            $params['BedCount'] = $data['BedCount'];
        }
        */
        if( isset($data['EstabPostCount']) && ( !empty($data['EstabPostCount'])  || $data['EstabPostCount'] == '0' ) )
        {
            $set .= " , @EstabPostCount = :EstabPostCount";
            $params['EstabPostCount'] = $data['EstabPostCount'];
        }
        
        if( isset($data['ActivePostCount']) && ( !empty($data['ActivePostCount']) || $data['ActivePostCount'] == '0' ) )
        {
            $set .= " , @ActivePostCount = :ActivePostCount";
            $params['ActivePostCount'] = $data['ActivePostCount'];
        }
        
        if( isset($data['IndividCount']) && ( !empty($data['IndividCount']) || $data['IndividCount'] == '0' ) )
        {
            $set .= " , @IndividCount = :IndividCount";
            $params['IndividCount'] = $data['IndividCount'];
        }
        
        if( isset($data['TeamCount']) && ( !empty($data['TeamCount']) || $data['TeamCount'] == '0' ) )
        {
            $set .= " , @TeamCount = :TeamCount";
            $params['TeamCount'] = $data['TeamCount'];
        }
        
        if( !empty($data['LpuLicence_id'])) 
        {
            $set .= " , @LpuLicence_id = :LpuLicence_id";
            $params['LpuLicence_id'] = $data['LpuLicence_id'];
        }
        
        if( isset($data['SpecCertif_Num']) && ( !empty($data['SpecCertif_Num']) || $data['SpecCertif_Num'] == 0 ) )
        {
            $set .= " , @SpecCertif_Num = :SpecCertif_Num";
            $params['SpecCertif_Num'] = $data['SpecCertif_Num'];
        }
        
        if( !empty($data['SpecCertif_endDate'])) 
        {
            $set .= " , @SpecCertif_endDate = :SpecCertif_endDate";
            $params['SpecCertif_endDate'] = $data['SpecCertif_endDate'];
        }
        
        if( isset($data['AssignedPacCount']) && ( !empty($data['AssignedPacCount']) || $data['AssignedPacCount'] == '0' ) )
        {
            $set .= " , @AssignedPacCount = :AssignedPacCount";
            $params['AssignedPacCount'] = $data['AssignedPacCount'];
        }
        
        if( isset($data['WomanCount']) && ( !empty($data['WomanCount']) || $data['WomanCount'] == '0' ) )
        {
            $set .= " , @WomanCount = :WomanCount";
            $params['WomanCount'] = $data['WomanCount'];
        }
        
        if( isset($data['SluchCountOwn1']) && ( !empty($data['SluchCountOwn1']) || $data['SluchCountOwn1'] == '0' ) )
        {
            $set .= " , @SluchCountOwn1 = :SluchCountOwn1";
            $params['SluchCountOwn1'] = $data['SluchCountOwn1'];
        }
        
        if( isset($data['SluchCountZone1']) && ( !empty($data['SluchCountZone1']) || $data['SluchCountZone1'] == '0' ) )
        {
            $set .= " , @SluchCountZone1 = :SluchCountZone1";
            $params['SluchCountZone1'] = $data['SluchCountZone1'];
        }
        
        if( isset($data['SluchCountOwn2']) && ( !empty($data['SluchCountOwn2']) || $data['SluchCountOwn2'] == '0' ) )
        {
            $set .= " , @SluchCountOwn2 = :SluchCountOwn2";
            $params['SluchCountOwn2'] = $data['SluchCountOwn2'];
        }
        
        if( isset($data['SluchCountZone2']) && ( !empty($data['SluchCountZone2']) || $data['SluchCountZone2'] == '0' ) )
        {
            $set .= " , @SluchCountZone2 = :SluchCountZone2";
            $params['SluchCountZone2'] = $data['SluchCountZone2'];
        }
        
        if( isset($data['ShiftCount']) && ( !empty($data['ShiftCount']) || $data['ShiftCount'] == '0' ) )
        {
            $set .= " , @ShiftCount = :ShiftCount";
            $params['ShiftCount'] = $data['ShiftCount'];
        }
        
        if( isset($data['PlaceCount']) && ( !empty($data['PlaceCount']) || $data['PlaceCount'] == '0' ) )
        {
            $set .= " , @PlaceCount = :PlaceCount";
            $params['PlaceCount'] = $data['PlaceCount'];
        }
        
        if( !empty($data['AvgYearBed']) || $data['AvgYearBed'] == 0 ) 
        {
            $set .= " , @AvgYearBed = :AvgYearBed";
            $params['AvgYearBed'] = $data['AvgYearBed'];
        }
        
        if( isset($data['RazObrCount']) && ( !empty($data['RazObrCount']) || $data['RazObrCount'] == '0' ) )
        {
            $set .= " , @RazObrCount = :RazObrCount";
            $params['RazObrCount'] = $data['RazObrCount'];
        }
        
        if( isset($data['PacCount']) && ( !empty($data['PacCount']) || $data['PacCount'] == '0' ) )
        {
            $set .= " , @PacCount = :PacCount";
            $params['PacCount'] = $data['PacCount'];
        }
        /*
        if( !empty($data['Post']) || $data['Post'] == 0 ) 
        {
            $set .= " , @Post = :Post";
            $params['Post'] = $data['Post'];
        }
        
        if( !empty($data['FIO'])) 
        {
            $set .= " , @FIO = :FIO";
            $params['FIO'] = $data['FIO'];
        }
        
        if( !empty($data['Phone'])) 
        {
            $set .= " , @Phone = :Phone";
            $params['Phone'] = $data['Phone'];
        }
        
        if( !empty($data['Email'])) 
        {
            $set .= " , @Email = :Email";
            $params['Email'] = $data['Email'];
        }
        */
        
        if( isset($data['Comment_o']) && ( !empty($data['Comment_o']) || $data['Comment_o'] == '') )
        {
            $set .= " , @Comment_o = :Comment_o";
            $params['Comment_o'] = $data['Comment_o'];
        }
        
		if( isset($data['Duration_o']) && ( !empty($data['Duration_o']) || $data['Duration_o'] == '0' ) )
        {
            $set .= " , @Duration_o = :Duration_o";
            $params['Duration_o'] = $data['Duration_o'];
        }
        /*
        if( !empty($data['BedCount_o'])) 
        {
            $set .= " , @BedCount_o = :BedCount_o";
            $params['BedCount_o'] = $data['BedCount_o'];
        }
        */
        if( isset($data['EstabPostCount_o']) && ( !empty($data['EstabPostCount_o'])  || $data['EstabPostCount_o'] == '0') )
        {
            $set .= " , @EstabPostCount_o = :EstabPostCount_o";
            $params['EstabPostCount_o'] = $data['EstabPostCount_o'];
        }
        
        if( isset($data['ActivePostCount_o']) && ( !empty($data['ActivePostCount_o']) || $data['ActivePostCount_o'] == '0') )
        {
            $set .= " , @ActivePostCount_o = :ActivePostCount_o";
            $params['ActivePostCount_o'] = $data['ActivePostCount_o'];
        }
        
        if( isset($data['IndividCount_o']) && ( !empty($data['IndividCount_o']) || $data['IndividCount_o'] == '0') )
        {
            $set .= " , @IndividCount_o = :IndividCount_o";
            $params['IndividCount_o'] = $data['IndividCount_o'];
        }
        
        if( isset($data['TeamCount_o']) && ( !empty($data['TeamCount_o']) || $data['TeamCount_o'] == '0') )
        {
            $set .= " , @TeamCount_o = :TeamCount_o";
            $params['TeamCount_o'] = $data['TeamCount_o'];
        }
        
        if( !empty($data['LpuLicence_id_o'])) 
        {
            $set .= " , @LpuLicence_id_o = :LpuLicence_id_o";
            $params['LpuLicence_id_o'] = $data['LpuLicence_id_o'];
        }
        
        if( !empty($data['SpecCertif_Num_o']) || $data['SpecCertif_Num_o'] == 0) 
        {
            $set .= " , @SpecCertif_Num_o = :SpecCertif_Num_o";
            $params['SpecCertif_Num_o'] = $data['SpecCertif_Num_o'];
        }
        
        if( !empty($data['SpecCertif_endDate_o'])) 
        {
            $set .= " , @SpecCertif_endDate_o = :SpecCertif_endDate_o";
            $params['SpecCertif_endDate_o'] = $data['SpecCertif_endDate_o'];
        }
        
        if( isset($data['AssignedPacCount_o']) && ( !empty($data['AssignedPacCount_o']) || $data['AssignedPacCount_o'] == '0') )
        {
            $set .= " , @AssignedPacCount_o = :AssignedPacCount_o";
            $params['AssignedPacCount_o'] = $data['AssignedPacCount_o'];
        }
        
        if( isset($data['WomanCount_o']) && ( !empty($data['WomanCount_o']) || $data['WomanCount_o'] == '0') )
        {
            $set .= " , @WomanCount_o = :WomanCount_o";
            $params['WomanCount_o'] = $data['WomanCount_o'];
        }
        
        if( isset($data['SluchCountOwn1_o']) && ( !empty($data['SluchCountOwn1_o']) || $data['SluchCountOwn1_o'] == '0') )
        {
            $set .= " , @SluchCountOwn1_o = :SluchCountOwn1_o";
            $params['SluchCountOwn1_o'] = $data['SluchCountOwn1_o'];
        }
        
        if( isset($data['SluchCountZone1_o']) && ( !empty($data['SluchCountZone1_o']) || $data['SluchCountZone1_o'] == '0') )
        {
            $set .= " , @SluchCountZone1_o = :SluchCountZone1_o";
            $params['SluchCountZone1_o'] = $data['SluchCountZone1_o'];
        }
        
        if( isset($data['SluchCountOwn2_o']) && ( !empty($data['SluchCountOwn2_o']) || $data['SluchCountOwn2_o'] == '0') )
        {
            $set .= " , @SluchCountOwn2_o = :SluchCountOwn2_o";
            $params['SluchCountOwn2_o'] = $data['SluchCountOwn2_o'];
        }
        
        if( isset($data['SluchCountZone2_o']) && ( !empty($data['SluchCountZone2_o']) || $data['SluchCountZone2_o'] == '0') )
        {
            $set .= " , @SluchCountZone2_o = :SluchCountZone2_o";
            $params['SluchCountZone2_o'] = $data['SluchCountZone2_o'];
        }
        
        if( isset($data['ShiftCount_o']) && ( !empty($data['ShiftCount_o']) || $data['ShiftCount_o'] == '0') )
        {
            $set .= " , @ShiftCount_o = :ShiftCount_o";
            $params['ShiftCount_o'] = $data['ShiftCount_o'];
        }
        
        if( isset($data['PlaceCount_o']) && ( !empty($data['PlaceCount_o']) || $data['PlaceCount_o'] == '0') )
        {
            $set .= " , @PlaceCount_o = :PlaceCount_o";
            $params['PlaceCount_o'] = $data['PlaceCount_o'];
        }
        
        if( !empty($data['AvgYearBed_o']) || $data['AvgYearBed_o'] == 0) 
        {
            $set .= " , @AvgYearBed_o = :AvgYearBed_o";
            $params['AvgYearBed_o'] = $data['AvgYearBed_o'];
        }
        
        if( isset($data['RazObrCount_o']) && ( !empty($data['RazObrCount_o']) || $data['RazObrCount_o'] == '0') )
        {
            $set .= " , @RazObrCount_o = :RazObrCount_o";
            $params['RazObrCount_o'] = $data['RazObrCount_o'];
        }
        
		if( isset($data['PacCount_o']) && ( !empty($data['PacCount_o']) || $data['PacCount_o'] == '0') )
        {
            $set .= " , @PacCount_o = :PacCount_o";
            $params['PacCount_o'] = $data['PacCount_o'];
        }
        /*
        if( !empty($data['Post_o'])) 
        {
            $set .= " , @Post_o = :Post_o";
            $params['Post_o'] = $data['Post_o'];
        }
        
        if( !empty($data['FIO_o'])) 
        {
            $set .= " , @FIO_o = :FIO_o";
            $params['FIO_o'] = $data['FIO_o'];
        }
        
        if( !empty($data['Phone_o'])) 
        {
            $set .= " , @Phone_o = :Phone_o";
            $params['Phone_o'] = $data['Phone_o'];
        }
        
        if( !empty($data['Email_o'])) 
        {
            $set .= " , @Email_o = :Email_o";
            $params['Email_o'] = $data['Email_o'];
        }
        */
        $procedure = 'p_SaveSprInfo';

        $q = " declare @SprInfo_id int,
                    @ErrCode int,
                    @ErrMessage varchar(4000);	
                exec r2." . $procedure . "
                        @SprInfo_id = :SprInfo_id " . $set . ";
                select @SprInfo_id as SprInfo_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        //echo($q);
		//sql_log_message('error','exec-p_SaveSprInfo-query: ',getDebugSql($q, $params));
        $r = $this->db->query($q, $params);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->SprInfo_id = $result[0]['SprInfo_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $params, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Проверить наличие фактических объемов
     */
    function checkFact($data) {
        $params = array();
        $params['VolPeriod_id'] = $data['VolPeriod_id'];
        $params['VidMP_id'] = $data['VidMP_id'];

        $q = "select r2.VolCheckFact(:VolPeriod_id, :VidMP_id) as Cnt;
        ";
        $r = $this->db->query($q, $params);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->Cnt = $result[0]['Cnt'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $params, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Загрузка справочника разрешенности планирования
     */
    function loadSprAllowPlan($data) {
        $q = "SELECT sp.*
              FROM r2.SprAllowPlan sp with (nolock)";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка списка МО, не имеющих заявок
     */
    function getLpuList($data) {
        $q = "select Lpu_id
                from v_Lpu with (nolock)
                where Region_id = 2
                and Lpu_id not in (select Lpu_id 
                                from r2.RequestList with (nolock)
                                where Request_id = :Request_id)
                order by Lpu_id";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * контроль при утверждении заявки
     */
    function doControl($data) {
        $params = array();
        $params['RequestList_id'] = $data['RequestList_id'];
        $q = "select r2.CheckVolPlan(:RequestList_id) as rslt";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * проверяет, указано ли количество бригад СМП
     */
    function checkSmpTeamExists($data) {
        $params = array();
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanObj_Code'] = $data['SprPlanObj_Code'];
        $q = "select r2.VolCheckSmpTeamExists(:RequestList_id, :SprPlanObj_Code) as rslt";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Получить плановый объем по МО
     */
    function getPlanByMo($data) {
        $params = array();
        $params['RequestList_id'] = $data['RequestList_id'];

        $q = "select sum(rd.RequestData_Plan) Cnt
            from r2.RequestData rd with (nolock)
            where rd.RequestList_id = :RequestList_id
        ";
        $r = $this->db->query($q, $params);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->Cnt = $result[0]['Cnt'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $params, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
	 * Загрузка списка категорий
	 */
	function loadCatList($data) {
		$params = array();
		$filter = "";
		$filter2 = "";

		if (!empty($data['catCode'])) {
			$filter .= " and PC.SprPlanCat_Code = :catCode";
			$params['catCode'] = $data['catCode'];
		}

		if (!empty($data['catName'])) {
			$filter .= " and PC.SprPlanCat_Name like ('%' + :catName + '%')";
			$params['catName'] = $data['catName'];
		}

		if (!empty($data['catNoVol']) & $data['catNoVol'] == 'true') {
			$filter2 .= " where tt.VolCount = 0";
		}

		$q = "select tt.*
              from (select 
                        RL.RequestList_id 
                        ,PC.SprPlanCat_id, 
                        PC.SprPlanCat_Code VolCode, 
                        PC.SprPlanCat_Name,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNab,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNab,0) end end end),0) as DispNab,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNab,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNab,0) end end end end),0) as DispNabAdults,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNab,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNab,0) end end end end),0) as DispNabKids,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(RD.RazObrCount,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(RD.RazObrCount,0) end end end),0) as RazObrCount,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(RD.RazObrCount,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.RazObrCount,0) end end end end),0) as RazObrCountAdults,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(RD.RazObrCount,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.RazObrCount,0) end end end end),0) as RazObrCountKids,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(RD.MedReab,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(RD.MedReab,0) end end end),0) as MedReab,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.MedReab,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.MedReab,0) end end end end),0) as MedReabAdults,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(rd.MedReab,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.MedReab,0) end end end end),0) as MedReabKids,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(RD.OtherPurp,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(RD.OtherPurp,0) end end end),0) as OtherPurp,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.OtherPurp,0)  else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.OtherPurp,0) end end end end),0) as OtherPurpAdults,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(rd.OtherPurp,0)  else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.OtherPurp,0) end end end end),0) as OtherPurpKids,

						ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNabPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNabPlanKP,0) end end end),0) as PlanCatData_DispNabKP,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNabPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNabPlanKP,0) end end end end),0) as PlanCatData_DispNabKPAdults,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNabPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNabPlanKP,0) end end end end),0) as PlanCatData_DispNabKPKids,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(RD.RazObrCountPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(RD.RazObrCountPlanKP,0) end end end),0) as PlanCatData_RazObrKP,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(RD.RazObrCountPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.RazObrCountPlanKP,0) end end end end),0) as PlanCatData_RazObrKPAdults,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(RD.RazObrCountPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.RazObrCountPlanKP,0) end end end end),0) as PlanCatData_RazObrKPKids,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(RD.MidMedStaffPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(RD.MidMedStaffPlanKP,0) end end end),0) as PlanCatData_MidMedStaffKP,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.MidMedStaffPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.MidMedStaffPlanKP,0) end end end end),0) as PlanCatData_MidMedStaffKPAdults,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(rd.MidMedStaffPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.MidMedStaffPlanKP,0) end end end end),0) as PlanCatData_MidMedStaffKPKids,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(RD.OtherPurpPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(RD.OtherPurpPlanKP,0) end end end),0) as PlanCatData_OtherPurpKP,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.OtherPurpPlanKP,0)  else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.OtherPurpPlanKP,0) end end end end),0) as PlanCatData_OtherPurpKPAdults,
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(rd.OtherPurpPlanKP,0)  else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.OtherPurpPlanKP,0) end end end end),0) as PlanCatData_OtherPurpKPKids,
                        
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then case when PO.SprVidMp_id = 18 then (isnull(RD.RequestData_Plan,0) + isnull(rd.DispNab,0) + isnull(rd.MedReab,0) + isnull(rd.RazObrCount,0) + isnull(rd.OtherPurp,0)) else isnull(RD.RequestData_Plan,0) end else case when PC.SprPlanCat_id=RD.SprPlanCat_id then case when PO.SprVidMp_id = 18 then (isnull(RD.RequestData_Plan,0) + isnull(rd.DispNab,0) + isnull(rd.MedReab,0) + isnull(rd.RazObrCount,0) + isnull(rd.OtherPurp,0)) else isnull(RD.RequestData_Plan,0) end end end end),0) as VolCount, 
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then case when PO.SprVidMp_id = 18 then (isnull(RD.RequestData_Plan,0) + isnull(rd.DispNab,0) + isnull(rd.MedReab,0) + isnull(rd.RazObrCount,0) + isnull(rd.OtherPurp,0)) else isnull(RD.RequestData_Plan,0) end else case when PC.SprPlanCat_id=RD.SprPlanCat_id then case when PO.SprVidMp_id = 18 then (isnull(RD.RequestData_Plan,0) + isnull(rd.DispNab,0) + isnull(rd.MedReab,0) + isnull(rd.RazObrCount,0) + isnull(rd.OtherPurp,0)) else isnull(RD.RequestData_Plan,0) end end end end end),0) as VolCountOld, 
                        ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then case when PO.SprVidMp_id = 18 then (isnull(RD.RequestData_Plan,0) + isnull(rd.DispNab,0) + isnull(rd.MedReab,0) + isnull(rd.RazObrCount,0) + isnull(rd.OtherPurp,0)) else isnull(RD.RequestData_Plan,0) end else case when PC.SprPlanCat_id=RD.SprPlanCat_id then case when PO.SprVidMp_id = 18 then (isnull(RD.RequestData_Plan,0) + isnull(rd.DispNab,0) + isnull(rd.MedReab,0) + isnull(rd.RazObrCount,0) + isnull(rd.OtherPurp,0)) else isnull(RD.RequestData_Plan,0) end end end end end),0) as VolCountYoung, 
                        
                        ISNULL(sum(case when PC.SprPlanCat_Code=1 then rd.VolCount1 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount1 end end),0) as fVolCount1,
                        ISNULL(sum(case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then rd.VolCount1 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount1 end end end),0) as fVolCount1Old,
                        ISNULL(sum(case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then rd.VolCount1 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount1 end end end),0) as fVolCount1Young,
                        ISNULL(sum(case when PC.SprPlanCat_Code=1 then rd.VolCount2 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount2 end end),0) as fVolCount2,
                        ISNULL(sum(case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then rd.VolCount2 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount2 end end end),0) as fVolCount2Old,
                        ISNULL(sum(case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then rd.VolCount2 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount2 end end end),0) as fVolCount2Young,
                        ISNULL(sum(case when PC.SprPlanCat_Code=1 then rd.VolCount3 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount3 end end),0) as fVolCount3,
                        ISNULL(sum(case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then rd.VolCount3 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount3 end end end),0) as fVolCount3Old,
                        ISNULL(sum(case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then rd.VolCount3 else case when PC.SprPlanCat_id=RD.SprPlanCat_id then rd.VolCount3 end end end),0) as fVolCount3Young,
                        isnull(pd.PlanCatData_KpAdults,0) + isnull(pd.PlanCatData_KpKids,0) as PlanCatData_KP,
                        pd.PlanCatData_KpAdults,
                        pd.PlanCatData_KpKids,

						--isnull(pd.PlanCatData_PlanKpAdults,0) + isnull(pd.PlanCatData_PlanKpKids,0) as PlanCatData_PlanKP,
						--pd.PlanCatData_PlanKpAdults,
                        --pd.PlanCatData_PlanKpKids,

						case :SprVidMp_id
							when 18
							then
								ISNULL(
									sum(
										case
											when rd.RequestData_AllowPlan=1
											then
												case
													when PC.SprPlanCat_Code=1
													then isnull(rd.DispNabPlanKP,0)
														+ isnull(RD.RazObrCountPlanKP,0)
														+ isnull(RD.MidMedStaffPlanKP,0)
                                                        + isnull(RD.OtherPurpPlanKP,0)
                                                        + isnull(RD.RequestData_PlanKP,0)
													else
														case
															when PC.SprPlanCat_id=RD.SprPlanCat_id
															then isnull(rd.DispNabPlanKP,0)
																+ isnull(RD.RazObrCountPlanKP,0)
																+ isnull(RD.MidMedStaffPlanKP,0)
                                                                + isnull(RD.OtherPurpPlanKP,0)
                                                                + isnull(RD.RequestData_PlanKP,0)
													end
												end
										end
									),
									0
								)
							else
								isnull(pd.PlanCatData_PlanKpAdults,0)
								+ isnull(pd.PlanCatData_PlanKpKids,0)
						end as PlanCatData_PlanKP,

						case :SprVidMp_id
							when 18
							then
								ISNULL(
									sum(
										case
											when rd.RequestData_AllowPlan=1
											then
												case
													when rd.MesAgeGroup_id=1
													then
														case
															when PC.SprPlanCat_Code=1
															then isnull(rd.DispNabPlanKP,0)
																+ isnull(RD.RazObrCountPlanKP,0)
																+ isnull(RD.MidMedStaffPlanKP,0)
                                                                + isnull(RD.OtherPurpPlanKP,0)
                                                                + isnull(RD.RequestData_PlanKP,0)
															else
																case
																	when PC.SprPlanCat_id=RD.SprPlanCat_id
																	then isnull(rd.DispNabPlanKP,0)
																		+ isnull(RD.RazObrCountPlanKP,0)
																		+ isnull(RD.MidMedStaffPlanKP,0)
                                                                        + isnull(RD.OtherPurpPlanKP,0)
                                                                        + isnull(RD.RequestData_PlanKP,0)
																end
														end
												end
										end
									),
									0
								)
							else
								pd.PlanCatData_PlanKpAdults
						end as PlanCatData_PlanKpAdults,

						case :SprVidMp_id
							when 18
							then
								ISNULL(
									sum(
										case
											when rd.RequestData_AllowPlan=1
											then
												case
													when rd.MesAgeGroup_id=2
													then
														case
															when PC.SprPlanCat_Code=1
															then isnull(rd.DispNabPlanKP,0)
																+ isnull(RD.RazObrCountPlanKP,0)
																+ isnull(RD.MidMedStaffPlanKP,0)
                                                                + isnull(RD.OtherPurpPlanKP,0)
                                                                + isnull(RD.RequestData_PlanKP,0)
															else
																case
																	when PC.SprPlanCat_id=RD.SprPlanCat_id
																	then isnull(rd.DispNabPlanKP,0)
																		+ isnull(RD.RazObrCountPlanKP,0)
																		+ isnull(RD.MidMedStaffPlanKP,0)
                                                                        + isnull(RD.OtherPurpPlanKP,0)
                                                                        + isnull(RD.RequestData_PlanKP,0)
																end
														end
												end
										end
									),
									0
								)
							else
								pd.PlanCatData_PlanKpKids
						end as PlanCatData_PlanKpKids,

                        --ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNabPlanKP,0) + isnull(RD.RazObrCountPlanKP,0) + isnull(RD.MidMedStaffPlanKP,0) + isnull(RD.OtherPurpPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNabPlanKP,0) + isnull(RD.RazObrCountPlanKP,0) + isnull(RD.MidMedStaffPlanKP,0) + isnull(RD.OtherPurpPlanKP,0) end end end),0) as PlanCatData_PlanKP,
                        --ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=1 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNabPlanKP,0) + isnull(RD.RazObrCountPlanKP,0) + isnull(RD.MidMedStaffPlanKP,0) + isnull(RD.OtherPurpPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNabPlanKP,0) + isnull(RD.RazObrCountPlanKP,0) + isnull(RD.MidMedStaffPlanKP,0) + isnull(RD.OtherPurpPlanKP,0) end end end end),0) as PlanCatData_PlanKpAdults,
                        --ISNULL(sum(case when rd.RequestData_AllowPlan=1 then case when rd.MesAgeGroup_id=2 then case when PC.SprPlanCat_Code=1 then isnull(rd.DispNabPlanKP,0) + isnull(RD.RazObrCountPlanKP,0) + isnull(RD.MidMedStaffPlanKP,0) + isnull(RD.OtherPurpPlanKP,0) else case when PC.SprPlanCat_id=RD.SprPlanCat_id then isnull(rd.DispNabPlanKP,0) + isnull(RD.RazObrCountPlanKP,0) + isnull(RD.MidMedStaffPlanKP,0) + isnull(RD.OtherPurpPlanKP,0) end end end end),0) as PlanCatData_PlanKpKids,

                        pd.PlanCatData_KpEmer
                from r2.v_RequestList RL (nolock) 
                inner join r2.requestdata RD (nolock) on RD.RequestList_id=RL.RequestList_id 
                inner join r2.SprPlanObj PO (nolock) on RL.SprVidMp_id=PO.SprVidMp_id and Mes_Code_Kpg=PO.SprPlanObj_Code 
                inner join r2.SprPlanCat PC (nolock) on PC.SprPlanCat_id=PO.SprPlanCat_id
                left join r2.PlanCatData pd on pd.SprPlanCat_id = pc.SprPlanCat_id and pd.RequestList_id = RL.RequestList_id
                where RL.RequestList_id=:RequestList_id 
                        and PO.SprVidMp_id=:SprVidMp_id
                        {$filter}
                Group by 
                RL.RequestList_id, 
                PC.SprPlanCat_id, 
                PC.SprPlanCat_Code, 
                PC.SprPlanCat_Name,
                pd.PlanCatData_KP,
                pd.PlanCatData_KpAdults,
                pd.PlanCatData_KpKids,
                pd.PlanCatData_PlanKpAdults,
                pd.PlanCatData_PlanKpKids,
                pd.PlanCatData_KpEmer) tt
                {$filter2}
              ";
		//sql_log_message('error','exec-loadCatList-query: ',getDebugSql($q, $data));
		$result = $this->db->query($q, $data);
		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Загрузка списка МО
     */
    function loadLpuList($data) {
        $q = "select lpu.Lpu_id,
                     lpu.Lpu_Nick
        from dbo.v_Lpu lpu with (nolock)
        where lpu.Lpu_endDate is null";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка уровней МО
     */
    function loadLpuLevelList($data) {
        $q = "select * from v_LevelType LT (nolock) 
                inner join v_LevelTypecoeff LTC (nolock) ON LT.LevelType_id=LTC.Leveltype_id 
                where LevelTypeCoeff_endDT is null";
        
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка справочника статусов
     */
    function loadRequestStatusList($data) {
        $q = "select rs.SprRequestStatus_id,
                     rs.SprRequestStatus_Name
            from r2.SprRequestStatus rs with (nolock)";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка видов МП
     */
    function loadVidMPList($data) {
        $q = "
            select
                    SprVidMp_id,
                    SprVidMp_Name
            from
                    r2.SprVidMp with (nolock)
            order by
                    SprVidMp_id
        ";
        $result = $this->db->query($q, $data);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки КС
     */
    function loadRequestDataStac($data) {
        $params = array();
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
                
                $query = "select 
                        rd.RequestData_id, 
                        rd.MesAgeGroup_id, 
                        PO.SprPlanObj_Code as SprPlanObj_Code, 
                        PO.SprPlanObj_Name as SprPlanObj_Name, 
                        case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan, 
                        RD.VolCount1, 
                        RD.VolCount2, 
                        RD.VolCount3, 
                        RD.VolCount4,
                        RD.SluchCountOwn1, 
                        RD.SluchCountZone1, 
                        RD.SluchCountOwn2, 
                        RD.SluchCountZone2,
                        RD.RequestData_AvgDur, 
                        RD.RequestData_BedCount, 
                        RD.RequestData_Plan, 
                        RD.RequestData_PlanKP, 
                        RD.RequestData_KP,
                        RD.RequestData_Comment 
                from  r2.v_RequestDataEvnPS RD with (nolock)
                inner join r2.SprPlanObj PO with (nolock) on RD.SprVidMP_id=PO.SprVidMp_id and RD.SprPlanCat_id=PO.SprPlanCat_id and RD.SprPlanObj_Code=PO.SprPlanObj_Code 
                where rd.RequestList_id = :RequestList_id 
                         and RD.SprPlanCat_id= :SprPlanCat_id
                         {$filter}
                        order by  cast (PO.SprPlanObj_Code as float) asc ";
            }
            else 
            {
                $query = "
                    select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4,
                            sum(RD.SluchCountOwn1) as SluchCountOwn1, 
                            sum(RD.SluchCountZone1) as SluchCountZone1, 
                            sum(RD.SluchCountOwn2) as SluchCountOwn2, 
                            sum(RD.SluchCountZone2) as SluchCountZone2,
                            sum(RD.RequestData_AvgDur) as RequestData_AvgDur, 
                            sum(RD.RequestData_BedCount)as RequestData_BedCount, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids 

                    from   r2.v_RequestDataEvnPS RD with (nolock)
                    inner join r2.SprPlanObj PO with (nolock) on RD.SprVidMP_id=PO.SprVidMp_id and RD.SprPlanCat_id=PO.SprPlanCat_id and RD.SprPlanObj_Code=PO.SprPlanObj_Code 
                    where rd.RequestList_id = :RequestList_id
                             and RD.SprPlanCat_id= :SprPlanCat_id 
                                {$filter}
                    GROUP BY  PO.SprPlanObj_Code, 
                            PO.SprPlanObj_Name
                    order by  cast (PO.SprPlanObj_Code as float) asc
            ";
            }
        }
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки ДС
     */
    function loadRequestDataDS($data) {
        $params = array();
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
                
                $query = "select 
                        rd.RequestData_id, 
                        rd.MesAgeGroup_id, 
                        PO.SprPlanObj_Code as SprPlanObj_Code, 
                        PO.SprPlanObj_Name as SprPlanObj_Name, 
                        case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                        RD.VolCount1, 
                        RD.VolCount2, 
                        RD.VolCount3, 
                        RD.VolCount4,
                        RD.SluchCountOwn1, 
                        RD.SluchCountZone1, 
                        RD.SluchCountOwn2, 
                        RD.SluchCountZone2,
                        RD.ShiftCount,
                        RD.PlaceCount,
                        RD.RequestData_AvgDur, 
                        RD.RequestData_BedCount, 
                        RD.RequestData_Plan, 
                        RD.RequestData_KP,
                        RD.RequestData_PlanKP, 
                        RD.RequestData_Comment,
                        RD.Post,
                        RD.FIO,
                        RD.Phone,
                        RD.Email
                from  r2.v_RequestDataEvnPS RD with (nolock)
                inner join r2.SprPlanObj PO with (nolock) on RD.SprVidMP_id=PO.SprVidMp_id and RD.SprPlanCat_id=PO.SprPlanCat_id and RD.SprPlanObj_Code=PO.SprPlanObj_Code 
                where rd.RequestList_id = :RequestList_id 
                         and RD.SprPlanCat_id= :SprPlanCat_id
                         {$filter}
                        order by  cast (PO.SprPlanObj_Code as float) asc ";
            }
            else 
            {
                $query = "
                    

                    select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4,
                            sum(RD.SluchCountOwn1) as SluchCountOwn1, 
                            sum(RD.SluchCountZone1) as SluchCountZone1, 
                            sum(RD.SluchCountOwn2) as SluchCountOwn2, 
                            sum(RD.SluchCountZone2) as SluchCountZone2,
                            sum(rd.ShiftCount) as ShiftCount,
                            sum(rd.PlaceCount) as PlaceCount,
                            sum(RD.RequestData_AvgDur) as RequestData_AvgDur, 
                            sum(RD.RequestData_BedCount)as RequestData_BedCount, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 

                    from   r2.v_RequestDataEvnPS RD with (nolock)
                    inner join r2.SprPlanObj PO with (nolock) on RD.SprVidMP_id=PO.SprVidMp_id and RD.SprPlanCat_id=PO.SprPlanCat_id and RD.SprPlanObj_Code=PO.SprPlanObj_Code 
                    where rd.RequestList_id = :RequestList_id
                             and RD.SprPlanCat_id= :SprPlanCat_id 
                                {$filter}
                    GROUP BY  PO.SprPlanObj_Code, 
                            PO.SprPlanObj_Name
                    order by  cast (PO.SprPlanObj_Code as float) asc
            ";
            }
        }
        
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка справочной информации
     */
    function loadSprInfo($data) {
        $filter = "";
        $params = array();
        $params['RequestList_id'] = $data['RequestList_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(s.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        $q = "select s.SprInfo_id,
                    s.RequestList_id,
                    s.SprPlanObj_Code,
                    s.SprPlanObj_Name,
                    s.Duration,
                    s.EstabPostCount,
                    s.ActivePostCount,
                    s.IndividCount,
                    s.TeamCount,
                    s.LpuLicence_id,
                    s.SpecCertif_Num,
                    convert(varchar, s.SpecCertif_endDate, 104) as SpecCertif_endDate,
                    s.AssignedPacCount,
                    s.WomanCount,
                    s.SluchCountZone1,
                    s.SluchCountZone2,
                    s.SluchCountOwn1,
                    s.SluchCountOwn2,
                    s.EdCol_id,
                    s.ShiftCount,
                    s.PlaceCount,
                    s.AvgYearBed,
                    s.RazObrCount,
                    s.UslOk,
                    s.UslName,
                    s.UslCode,
                    s.PacCount,
                    s.Team,
                    s.Comment,
                    s.SprInfo_insDT,
                    s.SprInfo_updDT,
                    s.pmUser_insID,
                    s.pmUser_updID,
                    rl.Lpu_id,
                    r.Request_Year,
                    l.*
            from r2.SprInfo s with (nolock)
			join r2.RequestList rl with (nolock) on rl.RequestList_id = s.RequestList_id
			join r2.Request r with (nolock) on r.Request_id = rl.Request_id
			left join (Select
                        Lpu_id,
                        LpuLicence_id,
                        LpuLicence_Ser,
                        LpuLicence_Num,
                        RTrim(IsNull(convert(varchar,cast(LpuLicence_setDate as datetime),104),'')) as LpuLicence_setDate,
                        LpuLicence_RegNum,
                        VidDeat.VidDeat_id,
						VidDeat.VidDeat_Name,
                        RTrim(IsNull(convert(varchar,cast(LpuLicence_begDate as datetime),104),'')) as LpuLicence_begDate,
                        RTrim(IsNull(convert(varchar,cast(LpuLicence_endDate as datetime),104),'')) as LpuLicence_endDate,
						LpuLicence_endDate as LpuLicence_endDate2
				from dbo.v_LpuLicence LpuLicence (nolock)
				inner join dbo.VidDeat VidDeat (nolock) on LpuLicence.VidDeat_id=VidDeat.VidDeat_id
								where VidDeat_Code='4' 
                        ) l on cast(l.Lpu_id as int) = cast(rl.Lpu_id as int) 
						and ISNULL(YEAR(l.LpuLicence_endDate2), r.Request_Year)>=r.Request_Year
						and l.LpuLicence_id = s.LpuLicence_id
            where s.RequestList_id = :RequestList_id
            {$filter}
            order by s.SprPlanObj_Name";
        $result = $this->db->query($q, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
	 * Загрузка заявки АПП
	 */
	function loadRequestDataApp($data) {
		$filter = "";
		$query = "";
		$params['RequestList_id'] = $data['RequestList_id'];
		$params['SprPlanCat_id'] = $data['SprPlanCat_id'];
		if (!empty($data['objName'])) {
			$filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
			$params['objName'] = $data['objName'];
		}
		if ($data['allowPlan'] != '') {
			$filter .= " and RD.RequestData_AllowPlan = :allowPlan";
			$params['allowPlan'] = $data['allowPlan'];
		}
		if (!empty($data['MesAgeGroup_id'])) {
			if ($data['MesAgeGroup_id'] != 3) {
				$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
				//$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
				$params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

				$query = "select 
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                            RD.VolCount1, 
                            RD.VolCount2, 
                            RD.VolCount3, 
                            RD.VolCount4, 
                            RD.SluchCountOwn1,
                            RD.SluchCountZone1,
                            case r.SprVidMp_id when 13 then RD.RequestData_Plan else (isnull(RD.RequestData_Plan,0)+isnull(rd.DispNab,0)+isnull(rd.MedReab,0)+isnull(rd.OtherPurp,0)+isnull(rd.RazObrCount,0)) end as RequestData_Plan, 
                            --RD.RequestData_PlanKP,
                            case
                                when rd.SprPlanCat_id = 43123
                                then isnull(RD.DispNabPlanKP,0)
                                    + isnull(RD.RazObrCountPlanKP,0)
                                    + isnull(RD.MidMedStaffPlanKP,0)
                                    + isnull(RD.OtherPurpPlanKP,0)
                                else isnull(RD.RequestData_PlanKP,0)
                            end RequestData_PlanKP,
                            RD.RequestData_KP, 
                            RD.RazObrCount,
                            RD.DispNab,
                            RD.MedReab,
                            RD.OtherPurp,
                            RD.RequestData_Comment,
							rd.DispNabPlanKP,
							rd.RazObrCountPlanKP,
							rd.MidMedStaffPlanKP,
							rd.OtherPurpPlanKP
                    from  r2.RequestData RD with (nolock) 
                        join r2.RequestList rl with (nolock)  on rl.RequestList_id = rd.RequestList_id
                        join r2.Request r with (nolock)  on r.Request_id = rl.Request_id
                        join r2.SprPlanCat pc with (nolock)  on pc.SprPlanCat_id = rd.SprPlanCat_id
                        join r2.SprPlanObj po with (nolock)  on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}";
			}
			else {
				$query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4, 
                            sum(RD.SluchCountOwn1) as SluchCountOwn1,
                            sum(case when rd.MesAgeGroup_id=1 then RD.SluchCountOwn1 end) as SluchCountOwn1Adults, 
                            sum(case when rd.MesAgeGroup_id=2 then RD.SluchCountOwn1 end) as SluchCountOwn1Kids, 
                            sum(RD.SluchCountZone1) as SluchCountZone1,
                            sum(case when rd.MesAgeGroup_id=1 then RD.SluchCountZone1 end) as SluchCountZone1Adults, 
                            sum(case when rd.MesAgeGroup_id=2 then RD.SluchCountZone1 end) as SluchCountZone1Kids, 
                            sum(case when rd.RequestData_AllowPlan=1 then case r.SprVidMp_id when 13 then RD.RequestData_Plan else (isnull(RD.RequestData_Plan,0)+isnull(rd.DispNab,0)+isnull(rd.MedReab,0)+isnull(rd.OtherPurp,0)+isnull(rd.RazObrCount,0)) end end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then case r.SprVidMp_id when 13 then RD.RequestData_Plan else (isnull(RD.RequestData_Plan,0)+isnull(rd.DispNab,0)+isnull(rd.MedReab,0)+isnull(rd.OtherPurp,0)+isnull(rd.RazObrCount,0)) end end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then case r.SprVidMp_id when 13 then RD.RequestData_Plan else (isnull(RD.RequestData_Plan,0)+isnull(rd.DispNab,0)+isnull(rd.MedReab,0)+isnull(rd.OtherPurp,0)+isnull(rd.RazObrCount,0)) end end) as RequestData_PlanYoung,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                            sum(case
                                    when rd.RequestData_AllowPlan=1
                                    then
                                        case
                                            when rd.SprPlanCat_id = 43123
                                            then isnull(RD.DispNabPlanKP,0)
                                                + isnull(RD.RazObrCountPlanKP,0)
                                                + isnull(RD.MidMedStaffPlanKP,0)
                                                + isnull(RD.OtherPurpPlanKP,0)
                                            else isnull(RD.RequestData_PlanKP,0)
                                        end
                                end) as RequestData_PlanKP, 
                            sum(case
                                    when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1
                                    then
                                        case
                                            when rd.SprPlanCat_id = 43123
                                            then isnull(RD.DispNabPlanKP,0)
                                                + isnull(RD.RazObrCountPlanKP,0)
                                                + isnull(RD.MidMedStaffPlanKP,0)
                                                + isnull(RD.OtherPurpPlanKP,0)
                                            else isnull(RD.RequestData_PlanKP,0)
                                        end
                                end) as RequestData_PlanKPOld, 
                            sum(case
                                    when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2
                                    then
                                        case
                                            when rd.SprPlanCat_id = 43123
                                            then isnull(RD.DispNabPlanKP,0)
                                                + isnull(RD.RazObrCountPlanKP,0)
                                                + isnull(RD.MidMedStaffPlanKP,0)
                                                + isnull(RD.OtherPurpPlanKP,0)
                                            else isnull(RD.RequestData_PlanKP,0)
                                        end
                                end) as RequestData_PlanKPYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RazObrCount end) as RazObrCount,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RazObrCount end) as RazObrCountAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RazObrCount end) as RazObrCountKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.DispNab end) as DispNab,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.DispNab end) as DispNabAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.DispNab end) as DispNabKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.MedReab end) as MedReab,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.MedReab end) as MedReabAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.MedReab end) as MedReabKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.OtherPurp end) as OtherPurp,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.OtherPurp end) as OtherPurpAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.OtherPurp end) as OtherPurpKids,

							sum(case when rd.RequestData_AllowPlan=1 then RD.DispNabPlanKP end) as DispNabPlanKP,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.DispNabPlanKP end) as DispNabPlanKPAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.DispNabPlanKP end) as DispNabPlanKPKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RazObrCountPlanKP end) as RazObrCountPlanKP,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RazObrCountPlanKP end) as RazObrCountPlanKPAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RazObrCountPlanKP end) as RazObrCountPlanKPKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.MidMedStaffPlanKP end) as MidMedStaffPlanKP,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.MidMedStaffPlanKP end) as MidMedStaffPlanKPAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.MidMedStaffPlanKP end) as MidMedStaffPlanKPKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.OtherPurpPlanKP end) as OtherPurpPlanKP,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.OtherPurpPlanKP end) as OtherPurpPlanKPAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.OtherPurpPlanKP end) as OtherPurpPlanKPKids

                            --sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            --sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock)  on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r  with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock)  on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    outer apply (select top 1 PO.SprPlanObj_Code, PO.SprPlanObj_name
									from r2.SprPlanObj po with (nolock)  
									where po.SprPlanCat_id = rd.SprPlanCat_id 
										and po.SprVidMp_id = r.SprVidMp_id 
										and po.SprPlanObj_Code = rd.Mes_Code_KPG) PO
                                    --join r2.SprPlanObj po on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
			}
		}

		//sql_log_message('error','exec-loadRequestDataApp-query: ',getDebugSql($query, $params));
		$result = $this->db->query($query, $params, true);
		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
    
    /**
     * Загрузка заявки АПП НМП
     */
    function loadRequestDataAppNmp($data) {
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
                //$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

                $query = "select 
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                            RD.VolCount1, 
                            RD.VolCount2, 
                            RD.VolCount3, 
                            RD.VolCount4, 
                            RD.RequestData_Plan, 
                            RD.RequestData_PlanKP, 
                            RD.RequestData_KP, 
                            RD.RequestData_Comment,
                            RD.EmerRoom
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}";
            }
            else
            {
                $query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(isnull(RD.VolCount1,0))as VolCount1, 
                            sum(isnull(RD.VolCount2,0))as VolCount2, 
                            sum(isnull(RD.VolCount3,0))as VolCount3, 
                            sum(isnull(RD.VolCount4,0))as VolCount4, 
                            sum(isnull(RD.EmerRoom,0)) as EmerRoom,
                            sum(case when Rd.MesAgeGroup_id=1 then isnull(RD.EmerRoom,0) end) as EmerRoomAdults, 
                            sum(case when Rd.MesAgeGroup_id=2 then isnull(RD.EmerRoom,0) end) as EmerRoomKids, 
                            sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.RequestData_Plan,0) end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then isnull(RD.RequestData_Plan,0) end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then isnull(RD.RequestData_Plan,0) end) as RequestData_PlanYoung,
                            case sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.RequestData_KP,0) end) when 0 then null else sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.RequestData_KP,0) end) end as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then isnull(RD.RequestData_KP,0) end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then isnull(RD.RequestData_KP,0) end) as RequestData_KpKids,
                            case sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.RequestData_PlanKP,0) end) when 0 then null else sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.RequestData_PlanKP,0) end) end as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then isnull(RD.RequestData_PlanKP,0) end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then isnull(RD.RequestData_PlanKP,0) end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
            }
        }
        
        
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки АПП консульт. пос.
     */
    function loadRequestDataAppCons($data) {
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
                //$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

                $query = "select 
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan, 
                            RD.VolCount1, 
                            RD.VolCount2, 
                            RD.VolCount3, 
                            RD.VolCount4, 
                            RD.RequestData_Plan, 
                            RD.RequestData_PlanKP,
                            RD.RequestData_KP,
                            RD.RequestData_Comment 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    {$filter}";
            }
            else
            {
                $query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
            }
        }
        
        
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки АПП профилактич.
     */
    function loadRequestDataAppProf($data) {
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
                //$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

                $query = "select 
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                            RD.VolCount1, 
                            RD.VolCount2, 
                            RD.VolCount3, 
                            RD.VolCount4, 
                            RD.RequestData_Plan, 
                            RD.RequestData_PlanKP,
                            RD.RequestData_KP,
                            RD.RequestData_Comment 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}";
            }
            else
            {
                $query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
            }
        }
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
        
     /**
     * Загрузка заявки АПП диспансеризация
     */
    function loadRequestDataAppDisp($data) {
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
                //$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

                $query = "select 
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                            RD.VolCount1, 
                            RD.VolCount2, 
                            RD.VolCount3, 
                            RD.VolCount4, 
                            RD.RequestData_Plan, 
                            RD.RequestData_PlanKP,
                            RD.RequestData_KP,
                            RD.RequestData_Comment 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}";
            }
            else
            {
                $query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
            }
        }
        
        
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
	 *Загрузка заявки АПП прикрепленные
	 */
	function loadRequestDataAppProfAttach($data) {
		$filter = "";
		$query = "";
		$params['RequestList_id'] = $data['RequestList_id'];
		$params['SprPlanCat_id'] = $data['SprPlanCat_id'];
		if (!empty($data['objName'])) {
			$filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
			$params['objName'] = $data['objName'];
		}
		if ($data['allowPlan'] != '') {
			$filter .= " and RD.RequestData_AllowPlan = :allowPlan";
			$params['allowPlan'] = $data['allowPlan'];
		}
		if (!empty($data['MesAgeGroup_id'])) {
			if ($data['MesAgeGroup_id'] != 3) {
				$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
				//$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
				$params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

				$query = "select 
						rd.RequestData_id, 
						rd.MesAgeGroup_id, 
						PO.SprPlanObj_Code as SprPlanObj_Code, 
						PO.SprPlanObj_Name as SprPlanObj_Name, 
						case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
						RD.VolCount1, 
						RD.VolCount2, 
						RD.VolCount3, 
						RD.VolCount4, 
						RD.SluchCountOwn1,
						RD.SluchCountZone1,
						(isnull(RD.RequestData_Plan,0)+isnull(rd.DispNab,0)+isnull(rd.MedReab,0)+isnull(rd.OtherPurp,0)+isnull(rd.RazObrCount,0)) as RequestData_Plan, 
						--RD.RequestData_PlanKP,
						isnull(RD.DispNabPlanKP,0) + isnull(RD.RazObrCountPlanKP,0) + isnull(RD.MidMedStaffPlanKP,0) + isnull(RD.OtherPurpPlanKP,0) as RequestData_PlanKP,
						RD.RequestData_KP, 
						RD.RazObrCount,
						RD.DispNab,
						RD.MedReab,
						RD.OtherPurp,
						RD.RequestData_Comment,
						rd.DispNabPlanKP,
						rd.RazObrCountPlanKP,
						rd.MidMedStaffPlanKP,
						rd.OtherPurpPlanKP
                    from  r2.RequestData RD with (nolock) 
                        join r2.RequestList rl with (nolock) 
							on rl.RequestList_id = rd.RequestList_id
                        join r2.Request r  with (nolock)   on r.Request_id = rl.Request_id
                        join r2.SprPlanCat   pc  with (nolock) 
							on pc.SprPlanCat_id = rd.SprPlanCat_id
                        join r2.SprPlanObj   po with (nolock) 
							on po.SprPlanCat_id = rd.SprPlanCat_id
								and po.SprVidMp_id = r.SprVidMp_id
								and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}";
			}
			else {
				$query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4, 
                            sum(RD.SluchCountOwn1) as SluchCountOwn1,
                            sum(case when rd.MesAgeGroup_id=1 then RD.SluchCountOwn1 end) as SluchCountOwn1Adults, 
                            sum(case when rd.MesAgeGroup_id=2 then RD.SluchCountOwn1 end) as SluchCountOwn1Kids, 
                            sum(RD.SluchCountZone1) as SluchCountZone1,
                            sum(case when rd.MesAgeGroup_id=1 then RD.SluchCountZone1 end) as SluchCountZone1Adults, 
                            sum(case when rd.MesAgeGroup_id=2 then RD.SluchCountZone1 end) as SluchCountZone1Kids, 
                            sum(case when rd.RequestData_AllowPlan=1 then (isnull(RD.RequestData_Plan,0)+isnull(rd.DispNab,0)+isnull(rd.MedReab,0)+isnull(rd.OtherPurp,0)+isnull(rd.RazObrCount,0)) end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then (isnull(RD.RequestData_Plan,0)+isnull(rd.DispNab,0)+isnull(rd.MedReab,0)+isnull(rd.OtherPurp,0)+isnull(rd.RazObrCount,0)) end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then (isnull(RD.RequestData_Plan,0)+isnull(rd.DispNab,0)+isnull(rd.MedReab,0)+isnull(rd.OtherPurp,0)+isnull(rd.RazObrCount,0)) end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.RequestData_KP,0) end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then isnull(RD.RequestData_KP,0) end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then isnull(RD.RequestData_KP,0) end) as RequestData_KpKids,
                            sum(case
                                    when rd.RequestData_AllowPlan=1
                                    then
                                        case
                                            when rd.SprPlanCat_id in (43111, 43112)
                                            then isnull(RD.DispNabPlanKP,0)
                                                + isnull(RD.RazObrCountPlanKP,0)
                                                + isnull(RD.MidMedStaffPlanKP,0)
                                                + isnull(RD.OtherPurpPlanKP,0)
                                            else isnull(RD.RequestData_PlanKP,0)
                                        end
                                end) as RequestData_PlanKP, 
                            sum(case
                                    when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1
                                    then
                                        case
                                            when rd.SprPlanCat_id in (43111, 43112)
                                            then isnull(RD.DispNabPlanKP,0)
                                                + isnull(RD.RazObrCountPlanKP,0)
                                                + isnull(RD.MidMedStaffPlanKP,0)
                                                + isnull(RD.OtherPurpPlanKP,0)
                                            else isnull(RD.RequestData_PlanKP,0)
                                        end
                                end) as RequestData_PlanKPOld, 
                            sum(case
                                    when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2
                                    then
                                        case
                                            when rd.SprPlanCat_id in (43111, 43112)
                                            then isnull(RD.DispNabPlanKP,0)
                                                + isnull(RD.RazObrCountPlanKP,0)
                                                + isnull(RD.MidMedStaffPlanKP,0)
                                                + isnull(RD.OtherPurpPlanKP,0)
                                            else isnull(RD.RequestData_PlanKP,0)
                                        end
                                end) as RequestData_PlanKPYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RazObrCount end) as RazObrCount,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RazObrCount end) as RazObrCountAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RazObrCount end) as RazObrCountKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.DispNab end) as DispNab,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.DispNab end) as DispNabAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.DispNab end) as DispNabKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.MedReab end) as MedReab,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.MedReab end) as MedReabAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.MedReab end) as MedReabKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.OtherPurp end) as OtherPurp,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.OtherPurp end) as OtherPurpAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.OtherPurp end) as OtherPurpKids,

							sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.DispNabPlanKP,0) end) as DispNabPlanKP,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then isnull(RD.DispNabPlanKP,0) end) as DispNabPlanKPAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then isnull(RD.DispNabPlanKP,0) end) as DispNabPlanKPKids,
                            sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.RazObrCountPlanKP,0) end) as RazObrCountPlanKP,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then isnull(RD.RazObrCountPlanKP,0) end) as RazObrCountPlanKPAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then isnull(RD.RazObrCountPlanKP,0) end) as RazObrCountPlanKPKids,
                            sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.MidMedStaffPlanKP,0) end) as MidMedStaffPlanKP,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then isnull(RD.MidMedStaffPlanKP,0) end) as MidMedStaffPlanKPAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then isnull(RD.MidMedStaffPlanKP,0) end) as MidMedStaffPlanKPKids,
                            sum(case when rd.RequestData_AllowPlan=1 then isnull(RD.OtherPurpPlanKP,0) end) as OtherPurpPlanKP,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then isnull(RD.OtherPurpPlanKP,0) end) as OtherPurpPlanKPAdults,
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then isnull(RD.OtherPurpPlanKP,0) end) as OtherPurpPlanKPKids

                            --sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            --sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock) 
                                    join r2.RequestList rl  with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r  with (nolock)  on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock)  on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock)  on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
			}
		}

		$result = $this->db->query($query, $params, true);
		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Загрузка заявки ЗПТ
     */
    function loadRequestDataZpt($data) {
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
                //$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

                $query = "select 
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                            RD.VolCount1, 
                            RD.VolCount2, 
                            RD.VolCount3, 
                            RD.VolCount4, 
                            RD.SluchCountOwn1,
                            RD.SluchCountZone1,
                            RD.RequestData_Plan, 
                            RD.RequestData_PlanKP,
                            RD.RequestData_KP,
                            RD.RequestData_Comment 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}";
            }
            else
            {
                $query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4, 
                            sum(RD.SluchCountOwn1) as SluchCountOwn1,
                            sum(case when rd.MesAgeGroup_id=1 then RD.SluchCountOwn1 end) as SluchCountOwn1Adults, 
                            sum(case when rd.MesAgeGroup_id=2 then RD.SluchCountOwn1 end) as SluchCountOwn1Kids, 
                            sum(RD.SluchCountZone1) as SluchCountZone1,
                            sum(case when rd.MesAgeGroup_id=1 then RD.SluchCountZone1 end) as SluchCountZone1Adults, 
                            sum(case when rd.MesAgeGroup_id=2 then RD.SluchCountZone1 end) as SluchCountZone1Kids, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
            }
        }
        
        
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }
    
    /**
     * Загрузка заявки ЭКО
     */
    function loadRequestDataEco($data) {
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
                //$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

                $query = "select 
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                            RD.VolCount1, 
                            RD.VolCount2, 
                            RD.VolCount3, 
                            RD.VolCount4, 
                            RD.SluchCountOwn1,
                            RD.SluchCountZone1,
                            RD.RequestData_Plan, 
                            RD.RequestData_PlanKP,
                            RD.RequestData_KP,
                            RD.RequestData_Comment 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}";
            }
            else
            {
                $query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4, 
                            sum(RD.SluchCountOwn1) as SluchCountOwn1,
                            sum(case when rd.MesAgeGroup_id=1 then RD.SluchCountOwn1 end) as SluchCountOwn1Adults, 
                            sum(case when rd.MesAgeGroup_id=2 then RD.SluchCountOwn1 end) as SluchCountOwn1Kids, 
                            sum(RD.SluchCountZone1) as SluchCountZone1,
                            sum(case when rd.MesAgeGroup_id=1 then RD.SluchCountZone1 end) as SluchCountZone1Adults, 
                            sum(case when rd.MesAgeGroup_id=2 then RD.SluchCountZone1 end) as SluchCountZone1Kids, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
            }
        }
        
        
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки ЛДИ
     */
    function loadRequestDataLdi($data) {
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id ";
                //$filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id AND (ISNULL(po.MesAgeGroup_id, 3) = 3 OR po.MesAgeGroup_id = :MesAgeGroup_id) ";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];

                $query = "select 
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                            RD.VolCount1, 
                            RD.VolCount2, 
                            RD.VolCount3, 
                            RD.VolCount4, 
                            RD.RequestData_Plan, 
                            RD.RequestData_PlanKP,
                            RD.RequestData_KP,
                            RD.RequestData_Comment 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}";
            }
            else
            {
                $query = "select 
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 
                    from  r2.RequestData RD with (nolock)
                                    join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
                                    join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                                    join r2.SprPlanCat pc with (nolock) on pc.SprPlanCat_id = rd.SprPlanCat_id
                                    join r2.SprPlanObj po with (nolock) on po.SprPlanCat_id = rd.SprPlanCat_id and po.SprVidMp_id = r.SprVidMp_id and po.SprPlanObj_Code = rd.Mes_Code_KPG
                    where rd.RequestList_id = :RequestList_id
                    and RD.SprPlanCat_id= :SprPlanCat_id
                    {$filter}
                                    GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name";
            }
        }
        
        
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки СМП
     */
    function loadRequestDataSmp($data) {
        $params = array();
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            // Вкладки "Взрослые" и "Дети"
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
                
                $query = "select 
                                rd.RequestData_id, 
                                rd.MesAgeGroup_id, 
                                PO.SprPlanObj_Code as SprPlanObj_Code, 
                                PO.SprPlanObj_Name as SprPlanObj_Name, 
                                RD.RequestData_TeamCount as TeamCount, 
                                case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                                RD.VolCount1, 
                                RD.VolCount2, 
                                RD.VolCount3, 
                                RD.VolCount4, 
                                RD.RequestData_Plan, 
                                RD.RequestData_PlanKP, 
                                RD.RequestData_KP, 
                                RD.RequestData_Comment 
                        from  r2.v_RequestDataCmp RD with (nolock)
                        inner join r2.SprPlanObj PO with (nolock) on RD.SprVidMP_id=PO.SprVidMp_id and RD.SprPlanCat_id=PO.SprPlanCat_id and RD.SprPlanObj_Code=PO.SprPlanObj_Code 
                        where rd.RequestList_id = :RequestList_id 
                                and rd.MesAgeGroup_id = :MesAgeGroup_id 
                                 and RD.SprPlanCat_id= :SprPlanCat_id 
                                 {$filter}
                                order by  cast (PO.SprPlanObj_Code as float) asc ";
            }
            // Вкладка "Общее"
            else 
            {
                $query = "
                        select 
                                PO.SprPlanObj_Code as SprPlanObj_Code, 
                                PO.SprPlanObj_Name as SprPlanObj_Name, 
                                sum(RD.VolCount1)as VolCount1, 
                                sum(RD.VolCount2)as VolCount2, 
                                sum(RD.VolCount3)as VolCount3, 
                                sum(RD.VolCount4)as VolCount4, 
                                sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                                sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                                sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                                sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                                sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                                sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                                sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                                sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                                sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 

                        from   r2.v_RequestDataCmp RD with (nolock)
                        inner join r2.SprPlanObj PO with (nolock) on RD.SprVidMP_id=PO.SprVidMp_id and RD.SprPlanCat_id=PO.SprPlanCat_id and RD.SprPlanObj_Code=PO.SprPlanObj_Code 
                        where rd.RequestList_id = :RequestList_id
                                 and RD.SprPlanCat_id=:SprPlanCat_id
                                 {$filter}
                        GROUP BY  PO.SprPlanObj_Code, 
                                PO.SprPlanObj_Name 

                        order by  cast (PO.SprPlanObj_Code as float) asc
            ";
            }
        }
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки ВМП
     */
    function loadRequestDataVmp($data) {
        $params = array();
        $filter = "";
        $query = "";
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        if( !empty($data['objName']) ) 
        {
            $filter .= " and upper(PO.SprPlanObj_Name) like upper('%'+:objName + '%')";
            $params['objName'] = $data['objName'];
        }
        if($data['allowPlan'] != '' ) 
        {
            $filter .= " and RD.RequestData_AllowPlan = :allowPlan";
            $params['allowPlan'] = $data['allowPlan'];
        }
        //echo() $data['MesAgeGroup_id'];
        if( !empty($data['MesAgeGroup_id']) ) 
        {
            // Вкладки "Взрослые" и "Дети"
            if($data['MesAgeGroup_id'] != 3)
            {
                $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id";
                $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
                
                $query = "select 
                         rd.RequestData_id, 
                         rd.MesAgeGroup_id, 
                         cast(PO.SprPlanObj_id as int) as SprPlanObj_id, 
                         PO.SprPlanObj_Code  + '. ' + PO.SprPlanObj_Name + '. КП - ' + cast(isnull(kp.SprPlanObjKp_Value,'') as varchar) as SprPlanObj_Name, 
                         RD.HTMedicalCareClass_GroupCode as SprPlanObj_Code, 
                         isnull(MES_CODE,Group_new) as GroupCodenew,
                         case RD.RequestData_AllowPlan when 1 then 'true' else 'false' end RequestData_AllowPlan,
                         RD.VolCount1, 
                         RD.VolCount2, 
                         RD.VolCount3, 
                         RD.VolCount4, 
                         RD.RequestData_Plan, 
                         RD.RequestData_PlanKP, 
                         RD.RequestData_KP,
                         RD.RequestData_Comment

                from  r2.v_RequestDataEvnPSHTM RD with (nolock) 
                         --inner join r2.SprHTM Htm with (nolock) on RD.HTMedicalCareClass_GroupCode=htm.HTMedicalCareClass_GroupCode 
                         --inner join r2.SprPlanObj PO with (nolock) on PO.SprPlanObj_id=RD.Mes_Code_Kpg
                        inner join r2.SprPlanObj PO with (nolock) on PO.SprPlanObj_code=RD.Mes_Code_Kpg and PO.SprPlanCat_id=4 and PO.SprVidMp_id=5
                        left join r2.SprPlanObjKp kp on kp.PlanYear = rd.Request_Year 
                                                        and kp.SprPlanObj_id = PO.SprPlanObj_id
                                                        and (kp.MesAgeGroup_id = rd.MesAgeGroup_id or kp.MesAgeGroup_id is null)
                                                        and kp.RequestList_id = rd.RequestList_id
                         full join 	(select HTM_oid.HTMedicalCareClass_GroupCode as Group_old,HTM_nid.HTMedicalCareClass_GroupCode as Group_new ,htmctl.HTMedicalCareTypeLink_begDate, htmctl.HTMedicalCareTypeLink_endDate from r2.HTMedicalCareTypeLink HTMCTL with (nolock) 
                                                         outer apply ( 
                                            select top 1 HTMCC.HTMedicalCareClass_GroupCode 
                                            from dbo.HTMedicalCareClass HTMCC with (nolock) 
                                            where HTMCC.HTMedicalCareType_id=HTMCTL.HTMedicalCareType_oid 
                                                        ) as HTM_oid 
                                 outer apply ( 
                                                   select top 1 HTMCC.HTMedicalCareClass_GroupCode 
                                                   from dbo.HTMedicalCareClass HTMCC with (nolock) 
                                                   where HTMCC.HTMedicalCareType_id=HTMCTL.HTMedicalCareType_nid 
                                                         ) as HTM_nid) as Groupnew on RD.HTMedicalCareClass_GroupCode=Group_old and YEAR(Groupnew.HTMedicalCareTypeLink_begDate)=rd.Request_Year and Groupnew.HTMedicalCareTypeLink_endDate is null 
                where rd.RequestList_id = :RequestList_id
                    and rd.MesAgeGroup_id = :MesAgeGroup_id
                    {$filter}
                    order by cast (PO.SprPlanObj_Code as float) asc
                    ";
            }
            // Вкладка "Общее"
            else 
            {
                $query = "
                        select  ROW_NUMBER() OVER(ORDER BY isnull(q.SprPlanObj_Code,q.GroupCodenew) ASC) AS RN,
                                isnull(q.SprPlanObj_Code,0) as SprPlanObj_Code,
                                isnull(q.GroupCodenew,0) as GroupCodenew,
                                q.SprPlanObj_Name,
                                sum(isnull(q.VolCount1,0))as VolCount1,
                                sum(isnull(q.VolCount2,0))as VolCount2, 
                                sum(isnull(q.VolCount3,0))as VolCount3, 
                                sum(isnull(q.VolCount4,0))as VolCount4, 
                                sum(case when q.RequestData_AllowPlan=1 then isnull(q.RequestData_Plan,0) end) as RequestData_Plan, 
                                sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=1 then isnull(q.RequestData_Plan,0) end) as RequestData_PlanOld, 
                                sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=2 then isnull(q.RequestData_Plan,0) end) as RequestData_PlanYoung, 
                                sum(case when q.RequestData_AllowPlan=1 then isnull(q.RequestData_KP,0) end) as RequestData_KP, 
                                sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=1 then isnull(q.RequestData_KP,0) end) as RequestData_KpAdults, 
                                sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=2 then isnull(q.RequestData_KP,0) end) as RequestData_KpKids,
                                sum(case when q.RequestData_AllowPlan=1 then isnull(q.RequestData_PlanKP,0) end) as RequestData_PlanKP, 
                                sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=1 then isnull(q.RequestData_PlanKP,0) end) as RequestData_PlanKPOld, 
                                sum(case when q.RequestData_AllowPlan=1 and q.MesAgeGroup_id=2 then isnull(q.RequestData_PlanKP,0) end) as RequestData_PlanKPYoung
                        from  (select distinct
                            rd.RequestData_id, 
                            rd.MesAgeGroup_id, 
                            cast(PO.SprPlanObj_id as int) as SprPlanObj_id, 
                            PO.SprPlanObj_Code  + '. ' + PO.SprPlanObj_Name + '. КП - ' + cast(isnull(kp.SprPlanObjKp_Value,'') as varchar) as SprPlanObj_Name, 
                            RD.HTMedicalCareClass_GroupCode as SprPlanObj_Code,
                            isnull(MES_CODE,Group_new) as GroupCodenew,
                            RD.RequestData_AllowPlan,
                            RD.VolCount1,
                            RD.VolCount2,
                            RD.VolCount3,
                            RD.VolCount4,
                            RD.RequestData_Plan,
                            RD.RequestData_PlanKP,
                            RD.RequestData_KP,
                            RD.RequestData_Comment

                from  r2.v_RequestDataEvnPSHTM RD with (nolock) 
                            --inner join r2.SprHTM Htm with(nolock) on RD.HTMedicalCareClass_GroupCode=htm.HTMedicalCareClass_GroupCode 
                            --inner join r2.SprPlanObj PO with (nolock) on PO.SprPlanObj_id=RD.Mes_Code_Kpg
                        inner join r2.SprPlanObj PO with (nolock) on PO.SprPlanObj_code=RD.Mes_Code_Kpg and PO.SprPlanCat_id=4 and PO.SprVidMp_id=5
                        left join r2.SprPlanObjKp kp on kp.PlanYear = rd.Request_Year 
                                                        and kp.SprPlanObj_id = PO.SprPlanObj_id
                                                        and (kp.MesAgeGroup_id = rd.MesAgeGroup_id or kp.MesAgeGroup_id is null)
                                                        and kp.RequestList_id = rd.RequestList_id
                            full join 	(select HTM_oid.HTMedicalCareClass_GroupCode as Group_old,HTM_nid.HTMedicalCareClass_GroupCode as Group_new ,htmctl.HTMedicalCareTypeLink_begDate, htmctl.HTMedicalCareTypeLink_endDate from r2.HTMedicalCareTypeLink HTMCTL with (nolock) 
                                                            outer apply ( 
                                            select top 1 HTMCC.HTMedicalCareClass_GroupCode 
                                            from dbo.HTMedicalCareClass HTMCC with (nolock) 
                                            where HTMCC.HTMedicalCareType_id=HTMCTL.HTMedicalCareType_oid 
                                                        ) as HTM_oid 
                                    outer apply ( 
                                                    select top 1 HTMCC.HTMedicalCareClass_GroupCode 
                                                    from dbo.HTMedicalCareClass HTMCC with (nolock) 
                                                    where HTMCC.HTMedicalCareType_id=HTMCTL.HTMedicalCareType_nid 
                                                            ) as HTM_nid) as Groupnew on RD.HTMedicalCareClass_GroupCode=Group_old and YEAR(Groupnew.HTMedicalCareTypeLink_begDate)=rd.Request_Year and Groupnew.HTMedicalCareTypeLink_endDate is null 
                where rd.RequestList_id = :RequestList_id
                and rd.MesAgeGroup_id <> 3
                    {$filter}
                    ) q
                    group by q.SprPlanObj_Code, 
                                q.GroupCodenew,
                                q.SprPlanObj_Name
                    Order by isnull(q.SprPlanObj_Code,q.GroupCodenew)
            ";
            }
        }
        
        
        
        
        
        
        
        /*
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
        $q = "  select 
                         rd.RequestData_id, 
                         rd.MesAgeGroup_id, 
                         cast(o.SprPlanObj_id as int) as SprPlanObj_id, 
                         o.SprPlanObj_Code  + '. ' + o.SprPlanObj_Name as SprPlanObj_Name, 
                         RD.HTMedicalCareClass_GroupCode as SprPlanObj_Code, 
                         Group_new as GroupCodenew, 
                         RD.RequestData_AllowPlan, 
                         RD.VolCount1, 
                         RD.VolCount2, 
                         RD.VolCount3, 
                         RD.VolCount4, 
                         RD.RequestData_Plan, 
                         RD.RequestData_PlanKP, 
                         RD.RequestData_Comment 

                from  r2.v_RequestDataEvnPSHTM RD with (nolock) 
                         inner join r2.SprHTM Htm with(nolock) on RD.HTMedicalCareClass_GroupCode=htm.HTMedicalCareClass_GroupCode 
                         inner join r2.SprPlanObj o with (nolock) on o.SprPlanObj_id=htm.SprPlanObj_id 
                         full join 	(select HTM_oid.HTMedicalCareClass_GroupCode as Group_old,HTM_nid.HTMedicalCareClass_GroupCode as Group_new ,htmctl.HTMedicalCareTypeLink_begDate, htmctl.HTMedicalCareTypeLink_endDate from r2.HTMedicalCareTypeLink HTMCTL with (nolock) 
                                                         outer apply ( 
                                            select top 1 HTMCC.HTMedicalCareClass_GroupCode 
                                            from dbo.HTMedicalCareClass HTMCC with (nolock) 
                                            where HTMCC.HTMedicalCareType_id=HTMCTL.HTMedicalCareType_oid 
                                                        ) as HTM_oid 
                                 outer apply ( 
                                                   select top 1 HTMCC.HTMedicalCareClass_GroupCode 
                                                   from dbo.HTMedicalCareClass HTMCC with (nolock) 
                                                   where HTMCC.HTMedicalCareType_id=HTMCTL.HTMedicalCareType_nid 
                                                         ) as HTM_nid) as Groupnew on Htm.HTMedicalCareClass_GroupCode=Group_old and YEAR(Groupnew.HTMedicalCareTypeLink_begDate)=rd.Request_Year and Groupnew.HTMedicalCareTypeLink_endDate is null 
                where rd.RequestList_id = :RequestList_id
                    and rd.MesAgeGroup_id = :MesAgeGroup_id
                    {$filter}
                    order by  cast (o.SprPlanObj_Code as int),RD.HTMedicalCareClass_GroupCode asc";*/
        $result = $this->db->query($query, $params, true);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка заявки
     */
    function loadRequestData($data) {
        $filter = "";
        $params = array();
        
        //        if( !empty($data['MesAgeGroup_id']) ) {
        //            $filter .= " and rd.MesAgeGroup_id = :MesAgeGroup_id";
        //            $params['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
        //        } 
        //        if( !empty($data['SprPlanCat_id']) ) {
        //            $filter .= " and rd.SprPlanCat_id = :SprPlanCat_id";
        //            $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        //        } 
        
        $params['RequestList_id'] = $data['RequestList_id'];
        $params['SprPlanCat_id'] = $data['SprPlanCat_id'];
        $q = "
            select distinct rd.RequestData_id,
                            PO.SprPlanObj_Code as SprPlanObj_Code, 
                            PO.SprPlanObj_Name as SprPlanObj_Name, 
                            RD.Post,
                            RD.FIO,
                            RD.Phone,
                            RD.Email,
                            sum(RD.VolCount1)as VolCount1, 
                            sum(RD.VolCount2)as VolCount2, 
                            sum(RD.VolCount3)as VolCount3, 
                            sum(RD.VolCount4)as VolCount4,
                            sum(RD.SluchCountOwn1) as SluchCountOwn1, 
                            sum(RD.SluchCountZone1) as SluchCountZone1, 
                            sum(RD.SluchCountOwn2) as SluchCountOwn2, 
                            sum(RD.SluchCountZone2) as SluchCountZone2,
                            sum(rd.ShiftCount) as ShiftCount,
                            sum(rd.PlaceCount) as PlaceCount,
                            sum(RD.RequestData_AvgDur) as RequestData_AvgDur, 
                            sum(RD.RequestData_BedCount)as RequestData_BedCount,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_Plan end) as RequestData_Plan, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_Plan end) as RequestData_PlanOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_Plan end) as RequestData_PlanYoung, 
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_KP end) as RequestData_KP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_KP end) as RequestData_KpAdults, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_KP end) as RequestData_KpKids,
                            sum(case when rd.RequestData_AllowPlan=1 then RD.RequestData_PlanKP end) as RequestData_PlanKP, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=1 then RD.RequestData_PlanKP end) as RequestData_PlanKPOld, 
                            sum(case when rd.RequestData_AllowPlan=1 and Rd.MesAgeGroup_id=2 then RD.RequestData_PlanKP end) as RequestData_PlanKPYoung 

                    from   r2.RequestData RD with (nolock)
					join r2.RequestList rl with (nolock) on rl.RequestList_id = rd.RequestList_id
					join r2.Request r with (nolock) on r.Request_id = rl.Request_id
                    inner join r2.SprPlanObj PO with (nolock) on R.SprVidMP_id=PO.SprVidMp_id and RD.SprPlanCat_id=PO.SprPlanCat_id and RD.Mes_Code_Kpg=PO.SprPlanObj_Code  and rd.SprPlanCat_id = 43118
                    where rd.RequestList_id = :RequestList_id
                           and RD.SprPlanCat_id= :SprPlanCat_id 
                                {$filter}
                    GROUP BY  rd.RequestData_id,
                            PO.SprPlanObj_Code, 
                            PO.SprPlanObj_Name,
                            RD.Post,
                            RD.FIO,
                            RD.Phone,
                            RD.Email
                    --order by  cast (PO.SprPlanObj_Code as float) asc
            ";
        $result = $this->db->query($q, $params, true);
        if ( is_object($result) ) {
                return $result->result('array');
        } else {
                return false;
        }
    }

    /**
     * Новый ИД
     */
    function getNewId() {
        $q = "
            select max(VolPeriod_id)+1 as VolPeriod_id from r2.VolPeriods with (nolock);
            ";
        $r = $this->db->query($q, array());
        if (is_object($r)) {
            $r = $r->result('array');
            if (isset($r[0])) {
                    return $r;
            } else {
                    return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Сохранение периода планирования
     */
    function saveVolPeriod($data) {
        $procedure = 'p_VolPeriod_ins';
        if ( $data['VolPeriod_id'] > 0 ) {
            $procedure = 'p_VolPeriod_upd';
        }
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @plan_year = :Plan_year,
                @VolPeriod_begDate = :VolPeriod_begDate,
                @VolPeriod_endDate = :VolPeriod_endDate,
                @VolPeriod_Name = :VolPeriod_Name,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'VolPeriod_begDate' => $data['VolPeriod_begDate'],
            'VolPeriod_endDate' => $data['VolPeriod_endDate'],
            'VolPeriod_Name' => $data['VolPeriod_Name'],
            'pmUser_id' => $data['pmUser_id'],
            'Plan_year' => $data['Plan_year'],
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Добавление ЛПУ в заявку
     */
    function addLpu2Request($data) {
        $q = "
            set nocount on;
            declare
                @Request_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @Request_id = :Request_id;
            exec r2.p_RequestListLpu_ins
                @Request_id = :Request_id,
                @Lpu_id = :Lpu_id,
                @SprRequestStatus_id = :SprRequestStatus_id,
                @VidMp_id = :SprVidMp_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @Request_id as Request_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'Request_id' => $data['Request_id'],
            'Lpu_id' => $data['Lpu_id'],
            'SprRequestStatus_id' => $data['SprRequestStatus_id'],
            'SprVidMp_id' => $data['SprVidMp_id'],
            'pmUser_id' => $data['pmUser_id'],
        );
        //echo($q);
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            //$this->Request_id = $result[0]['Request_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Сборка фактических объемов по КС
     */
    function collectFactsStac($data) {
        $procedure = '[p_FactVolStac_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id'],
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Сборка фактических объемов по КС КПГ/КСГ
     */
    function collectFactsStacKSG($data) {
        $procedure = '[p_FactVolStacKSG_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id'],
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Сборка фактических объемов по ДС
     */
    function collectFactsDS($data) {
        $procedure = '[p_FactVolDS_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id'],
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Сборка фактических объемов по ДС КПГ/КСГ
     */
    function collectFactsDSKSG($data) {
        $procedure = '[p_FactVolDSKSG_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Сборка фактических объемов по ВМП
     */
    function collectFactsVMP($data) {
        $procedure = '[p_FactVolVMP_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Сборка фактических объемов по АПП
     */
    function collectFactsApp($data) {
        $procedure = '[p_FactVolApp_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП консультативные посещения
     */
    function collectFactsAppCons($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppCons_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП неотложка
     */
    function collectFactsAppNmp($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppNMP_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП консультативные посещения
     */
    function collectFactsAppTreatment($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppTreatment_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП неприкреп
     */
    function collectFactsAppProfNotAttach($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppProfNotAttach_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП прикреп
     */
    function collectFactsAppProfAttach($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppProfAttach_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП ЦЗ
     */
    function collectFactsAppProfCZ($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppProfCZ_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП диспансеризация
     */
    function collectFactsAppProfDisp($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppProfDisp_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП медосмотры
     */
    function collectFactsAppProf($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppProf_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по АПП медосмотры
     */
    function collectFactsAppProfAll($data) {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_FactVolAppProfAll_ins
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    
    /**
     * Сборка фактических объемов по СМП
     */
    function collectFactsSMP($data) {
        $procedure = '[p_FactVolSmp_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
                log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
                $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по ЛДИ
     */
    function collectFactsLdi($data) {
        $procedure = '[p_FactVolLDI_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
                log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
                $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Сборка фактических объемов по ЗПТ
     */
    function collectFactsZpt($data) {
        $procedure = '[p_FactVolDializ_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }
    
    /**
     * Сборка фактических объемов по ЭКО
     */
    function collectFactsEco($data) {
        $procedure = '[p_FactVolECO_ins]';
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @VolPeriod_id = :VolPeriod_id;
            exec r2." . $procedure . "
                @VolPeriod_id = :VolPeriod_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @VolPeriod_id as VolPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'VolPeriod_id' => $data['VolPeriod_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Создать заявку
     */
    function buildRequest($data) 
    {
        $q = "
            declare
                @VolPeriod_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
				
            set @VolPeriod_id = :VolPeriod_id;
            exec r2.p_RequestList_ins
                @VolPeriod_id = @VolPeriod_id,
                @Year = :Year,
                @VidMp_id = :VidMp,
                @DevPrc = :Prc,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
				
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
		
		
        $p = array(
            'VolPeriod_id' => 1, //$data['VolPeriod_id'],
            'Year' => $data['Year'],
            'VidMp' => $data['VidMp'],
            'Prc' => $data['Prc'],
            'pmUser_id' => $data['pmUser_id']
        );
		
		$result = $this->getFirstRowFromQuery($q, $p);

		if ($result && is_array($result)) {
			if(empty($result['Error_Msg'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		
		/*
		$result = $this->db->query($q, $p);
		
        if ( is_object($result) ) {
			echo 'Step 1';
            $result = $result->result('array');
			echo 'Step 2';
            //var_dump($result);
            //$this->VolPeriod_id = $result[0]['VolPeriod_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
		*/
        return $result;
    }

    /**
     * Удаление периода планирования
     */
    function deleteVolPeriod($data) {
        $q = "
            delete from
                r2.VolPeriods
            where
                VolPeriod_id = :volPeriod_id;
        ";
        $r = $this->db->query($q, array(
            'volPeriod_id' => $data['VolPeriod_id']
        ));
        if ( is_object($r) ) {
            return $r->result('array');
        } else {
            return false;
        }
    }

    /**
     * Удаление фактических объемов
     */
    function deleteVolFact($data) {
        $tbl = $data['Table'];
        if ($tbl == 'none')
        {
            $q = "
            --delete from r2.FactVolApp
            --where VolPeriod_id = :VolPeriod_id;

            delete from r2.FactVolDS
            where VolPeriod_id = :VolPeriod_id;

            delete from r2.FactVolDSKSG
            where VolPeriod_id = :VolPeriod_id;

            delete from r2.FactVolLdi
            where VolPeriod_id = :VolPeriod_id;

            delete from r2.FactVolSmp
            where VolPeriod_id = :VolPeriod_id;

            delete from r2.FactVolStac
            where VolPeriod_id = :VolPeriod_id;

            delete from r2.FactVolStacKSG
            where VolPeriod_id = :VolPeriod_id;

            delete from r2.FactVolVmp
            where VolPeriod_id = :VolPeriod_id;

            delete from r2.FactVolZpt
            where VolPeriod_id = :VolPeriod_id;
        ";
        }
        else
        {
            $q = "delete from r2." . $tbl . "
                  where VolPeriod_id = :VolPeriod_id;";
        }
        $r = $this->db->query($q, array(
            'VolPeriod_id' => $data['VolPeriod_id']
        ));
        if ( is_object($r) ) {
            return $r->result('array');
        } else {
            return false;
        }
    }

    /**
     * Удаление из списка заявок
     */
    function deleteVolRequestList($data) 
    {
        $q = "
            delete from r2.RequestData
            where RequestList_id = :RequestList_id

            delete from r2.RequestList
            where RequestList_id = :RequestList_id;
        ";
        $p = array(
            'RequestList_id' => $data['RequestList_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            return $r->result('array');
        } else {
            return false;
        }
    }

    /**
     * удаление заявки
     */
    function deleteVolRequest($data) {
        $procedure = '[p_Request_del]';
        $q = "
            declare
                @Request_id bigint,
                @ErrCode int,
                @ErrMessage varchar(4000);
            set @Request_id = :Request_id;
            exec r2." . $procedure . "
                @Request_id = :Request_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMessage output;
            select @Request_id as Request_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
        ";
        $p = array(
            'Request_id' => $data['Request_id']
        );
        $r = $this->db->query($q, $p);
        if ( is_object($r) ) {
            $result = $r->result('array');
            $this->Request_id = $result[0]['Request_id'];
        } else {
            log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
            $result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
        return $result;
    }

    /**
     * Получение максимальной конечной даты в справочнике периодов планирования
     */
    function getVolPeriodMaxDate() {
        $q = "select convert(varchar(10), MAX(VolPeriod_endDate), 104) as max_date
                from r2.VolPeriods with (nolock)";
        $r = $this->db->query($q, array());
        if (is_object($r)) {
            $r = $r->result('array');
            
            if (isset($r[0])) 
            {
                return $r;
            } else 
            {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
    *  выгрузка в Excel
    */
    function insSvodToWorksheet($Worksheet, $data) {
        $table = $Worksheet->Table;
        foreach($data as $rowIndex=>$row) {
            foreach($row as $columnIndex=>$value) {
                $cell = $Worksheet->Table->Row[$rowIndex]->Cell[$columnIndex];
                if(!empty($value) && !empty($cell)){
                    $cell->Data = $data[$rowIndex][$columnIndex];
                    if (empty($cell->Data->attributes('ss',TRUE)->Type))
                        $cell->Data-> addAttribute('xmlns:ss:Type','Number');
                }
            }
        }
    }

    /**
     * Заполнение страницы
     * @param obj $Worksheet - страница отчета
     * @param array $data - строки для заполнения
     * @param array $rows - номера строк для заполнения в странице
     * @param array $svod - суммированная информация для заполнения страницы свода
     */
    function insRequestDataToWorksheet(&$Worksheet,$data, $rows, &$svod = null){
        foreach($data as $i=>$sprObj) {
            $rowNum = $rows[$sprObj['num']];
            unset($sprObj['num']);
            foreach($sprObj as $col=>$value) {

                if(empty($value))
                    continue;

                $row = $Worksheet->Table->Row[$rowNum];
                if(empty($row))
                    throw new Exception("Строка $rowNum не найдена");

                $cell = $row->Cell[$col];
                if(!isset($cell)) //continue;
                {
                    throw new Exception("Ячейка (".strval($rowNum).",".strval($col).") не найдена");
                }

                $this -> setCellValue($cell,$value);

                if($col && is_numeric($value) && isset($svod))
                    $this -> calcSvod($svod[$rowNum][$col], $value);
            }
        }
    }

    /**
     * Заполнение информации (Наименование МО, Номер, Уровень, Руководитель МО, Заместитель, Исполнитель)
     */
    function insLpuDataToWorksheet($sheet, $request, $cells) {
        foreach($request as $field=>$lpuData) {
            if(array_key_exists($field,$cells)) {
                foreach ($cells[$field] as $pos) {
                    if(!is_array($pos)) 
                        throw new Exception('Неверно заданы координаты ячеек для заполнения: '.strval($lpuData));

                    $cell = $sheet->Table->Row[$pos[0]]->Cell[$pos[1]];
                    
                    if(!$cell)
                    {
                        throw new Exception("Ячейка (".strval($pos[0]).",".strval($pos[1]).") не найдена");
                    }
                    
                    $cell -> Data = $request[$field];
                    if (empty($cell->Data->attributes('ss',TRUE)->Type))
                    {
                        $cell -> Data -> addAttribute('xmlns:ss:Type','String');
                    }
                }
            }
        }
    }




    /**
     * Копирует строку $target, вставляет перед ним
     * @param SimpleXMLElement $target
     * @param int $rowCount
     */
    function insRowsToWorksheet(SimpleXMLElement $target,$rowCount) {
        $target_dom = dom_import_simplexml($target);
        for($i=0; $i<$rowCount; ++$i) {

            $row = new DOMElement('Row');

            $target_dom->parentNode->insertBefore($row,$target_dom);
            foreach($target_dom->getElementsByTagName('Cell') as $cell) {

                $newCell = $row->appendChild( new DOMElement('Cell'));

                $ns_uri = $cell->lookupNamespaceURI('ss');

                if($cell->hasAttributeNS($ns_uri,'StyleID')) {
                    $style = $cell->getAttributeNS($ns_uri,'StyleID');
                    $newCell->setAttributeNS($ns_uri,'StyleID',$style);
                }
            }
        }
    }

    /**
    *  выгрузка в Excel
    */
    function setCellValue($cell, $value) {
        $cell->Data = $value;
        if (empty($cell->Data->attributes('ss',TRUE)->Type))
            if(is_numeric($value))
                $cell->Data->addAttribute('xmlns:ss:Type','Number');
            else
                $cell->Data->addAttribute('xmlns:ss:Type','String');
    }

    /**
    *  выгрузка в Excel
    */
    function ChangeSheetName($sheet, $name) {
        $oldName = clone $sheet -> attributes('ss',TRUE) -> Name;
        $sheet -> attributes('ss',TRUE) -> Name = $name;
        if($sheet -> Names)
        foreach( $sheet -> Names -> NamedRange as $namedRange) {
            $attrValue = $namedRange -> attributes('ss',TRUE) -> RefersTo;
            $namedRange -> attributes('ss',TRUE) -> RefersTo = str_replace($oldName,$name, $attrValue);
        }
    }

    /**
    *  выгрузка в Excel
    */
    function calcSvod(&$svod, $sprObj){
        if($svod)
            $svod += $sprObj;
        else
            $svod = $sprObj;
    }

    /**
     * Список заявок от МО
     * @param int Request_id
     */
    function getRequestList($Request_id) {
        $params = [
                'Request_id' => $Request_id
        ];
        $query = "SELECT
                        RL.RequestList_id
                        ,L.Lpu_id as lpu_id
                        ,L.Lpu_Nick as lpu_nick
                        ,L.Lpu_Name as lpu_name
                        ,L.Lpu_f003mcod as lpu_f003mcod
                        ,LpuLevel.LevelType_Name as lpu_lvl
                        ,Persons.isp as lpu_executor
                        ,Persons.ruk as lpu_head
                        ,Persons.zam as lpu_deputy
                FROM r2.Request R with (nolock)
                inner join r2.RequestList RL on RL.Request_id = R.Request_id
                inner join v_Lpu L on L.Lpu_id = RL.Lpu_id and L.Lpu_id <> 4
                outer apply (select * from [rpt2].[vol_getResponsiblePersons](RL.RequestList_id)) Persons
                outer APPLY(select top 1 LT.LevelType_Name 
                                        From dbo.LpuLevelType LLT (nolock) 
                                        inner join dbo.LevelType LT (nolock) on LLT.LevelType_id=LT.LevelType_id 
                                        where LLT.Lpu_id=L.Lpu_id 
                                        order by ISNULL(LLT.LpuLevelType_begDate,'2000-01-01') desc) LpuLevel
                WHERE RL.Request_id=:Request_id
        ";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     *  Список запросов от МО
     */
    function getRequestListPart($Request_id,$file) {

        $doc_size = 40; //в страницах

        if( $file > 0) {
            $start = ($file - 1) * $doc_size;
            $end = $file * $doc_size + 1;
        }

        $params = [
                'Request_id' => $Request_id,
                'start' => $start,
                'end' => $end
        ];
        $query = "
                with Request as
                (

                SELECT 
                ROW_NUMBER() OVER(ORDER BY RequestList_id ASC) as RowNum,
                                        RL.RequestList_id
                                        ,L.Lpu_id as lpu_id
                                        ,L.Lpu_Nick as lpu_nick
                                        ,L.Lpu_Name as lpu_name
                                        ,L.Lpu_f003mcod as lpu_f003mcod
                                        ,LpuLevel.LevelType_Name as lpu_lvl
                                        ,Persons.isp as lpu_executor
                                        ,Persons.ruk as lpu_head
                                        ,Persons.zam as lpu_deputy
                                FROM r2.Request R with (nolock)
                                inner join r2.RequestList RL on RL.Request_id = R.Request_id
                                inner join v_Lpu L on L.Lpu_id = RL.Lpu_id
                                outer apply (select * from [rpt2].[vol_getResponsiblePersons](RL.RequestList_id)) Persons
                                outer APPLY(select top 1 LT.LevelType_Name 
                                                        From dbo.LpuLevelType LLT (nolock) 
                                                        inner join dbo.LevelType LT (nolock) on LLT.LevelType_id=LT.LevelType_id 
                                                        where LLT.Lpu_id=L.Lpu_id 
                                                        order by ISNULL(LLT.LpuLevelType_begDate,'2000-01-01') desc) LpuLevel
                                WHERE RL.Request_id=:Request_id
                )
                select *
                From Request
                where RowNum > :start and RowNum < :end
        ";

        $result = $this->db->query($query, $params);
        if ( is_object($result) ) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Тело запроса
     */
    function getRequestData($file ,$params) {
        switch($file) {
            case 'M3_analit_form_SMP_0108.xls':
                $query="SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 as '5'
                            ,v7 as '6'
                            ,v8 as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                        FROM rpt2.[vol_MO_analit_SMP_2018]($params)";
            break;

            case 'M3_analit_Form_VMP_0108.xls':
                $query="SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 as '5'
                            ,v7 as '6'
                            ,v8 as '7'
                            ,v12 as '8'
                            ,v13 as '9'
                            ,v14 as '10'
                            ,v15 as '11'
                            ,v16 as '12'
                            ,v17 as '13'
                            ,v18 as '14'
                            ,v19 as '15'
                            ,v20 as '16'
                            ,v21 as '17'
                        FROM rpt2.vol_MO_analit_VMP_2018($params)";
            break;

            case 'M3_analit_form_SVOD_ECO.xls':
                $query="SELECT 
                            v1 as 'num',
                            v2 as '1',
                            v3 as '2',
                            v4 as '3',
                            v5 as '4',
                            v6 as '5',
                            --v7 as '7',
                            --v8 as '8',
                            v9 as '6',
                            v10 as '7',
                            v11 as '8',
                            v12 as '9'
                        FROM [rpt2].[vol_MO_analit_EKO_2_3_2018]($params)";
            break;

            case 'M3_fed_form_KS_0108.xls':
                $query="SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v6 as '5'
                            ,v7 as '6'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v16 as '15'
                            ,v17 as '16'
                        FROM rpt2.vol_MO_analit_KS_KSG_2018($params)";
            break;

            case 'M3_analit_form_KS_0108.xls':
                $query="SELECT
                            v1 as num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 + ' ' as '5'
                            ,v7 as '6'
                            ,v8 as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                            ,v19 as '18'
                            ,v20 as '19'
                            ,v21 as '20'
                            ,v22 as '21'
                            ,v24 as '23'
                            ,v25 as '24'
                            ,v26 as '25'
                            ,v27 as '26'
                            ,v28 as '27'
                        FROM rpt2.vol_MO_analit_KS_2018($params)";
            break;

            case 'M3_analit_form_KSG_0108.xls':
                $query="SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 as '5'
                            ,v7 as '6'
                            ,v8 as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                        FROM rpt2.vol_MO_analit_KS_KSG_2018($params)";
            break;

            case 'M3_fed_form_KSG_0108.xls':
                    $query="SELECT
                                num
                                ,v6 as '2'
                                ,v7 as '3'
                                ,v12 as '5'
                                ,v13 as '6'
                                ,v16 as '8'
                                ,v17 as '9'
                            FROM rpt2.vol_MO_analit_KS_KSG_2018($params)
                            WHERE KPG_flag = 0";
            break;

            case 'M3_analit_form_SVOD_DS_0108.xls':
                    $query="SELECT
                                    v1 as num,
                                    v3 as '4',
                                    v4 as '5',
                                    v5 as '6',
                                    v6 + ' ' as '7',
                                    v7 as '8',
                                    v8 as '9',
                                    v9 as '10',
                                    v10 as '11',
                                    v11 as '12',
                                    v12 as '13',
                                    v13 as '14',
                                    v14 as '15',
                                    v15 as '16',
                                    v16 as '17',
                                    v17 as '18',
                                    v18 as '19',
                                    v19 as '20',
                                    v20 as '21',
                                    v21 as '22',
                                    v22 as '23',
                                    v23 as '24',
                                    v24 as '25',
                                    v25 as '26',
                                    v26 as '27',
                                    v27 as '28',
                                    v28 as '29',
                                    v29 as '30',
                                    v30 as '31',
                                    v31 as '32',
                                    v32 as '33',
                                    v33 as '34',
                                    v34 as '35',
                                    v35 as '36',
                                    v36 as '37',
                                    v37 as '38',
                                    v38 as '39',
                                    v39 as '40',
                                    v40 as '41',
                                    v41 as '42',
                                    v42 as '43',
                                    v43 as '44',
                                    v44 as '45',
                                    v45 as '46',
                                    v46 as '47',
                                    v47 as '48',
                                    v48 as '49',
                                    v49 as '50',
                                    v50 as '51',
                                    v51 as '52',
                                    v52 as '53',
                                    v53 as '54',
                                    v54 as '55',
                                    v55 as '56',
                                    v56 as '57',
                                    v57 as '58',
                                    v58 as '59'
                            FROM [rpt2].[vol_MO_analit_DS_2_1_2018]($params)";
            break;

            case 'M3_analit_form_SVOD_DS_KSG_0108.xls':
                    $query="SELECT	num,
                                    v3 as '2',
                                    v4 as '3',
                                    v5 as '4',
                                    v6 as '5',
                                    v7 as '6',
                                    v8 as '7',
                                    v9 as '8',
                                    v10 as '9',
                                    v11 as '10',
                                    v12 as '11',
                                    v13 as '12',
                                    v14 as '13',
                                    v15 as '14',
                                    v16 as '15',
                                    v17 as '16',
                                    v18 as '17'
                            FROM [rpt2].[vol_MO_analit_DS_KSG_2018]($params)
                            WHERE KPG_flag = 0";
            break;

            case 'M3_fed_form_SVOD_VMP_20190108.xls':
                $query = $this->getSelectQuery('',3,11);
            break;

            case 'M3_analit_neotl_0108.xls':
                $query = "SELECT num
                                --,v1 as '0'
                                --,v2 as '1'
                                ,v3 as '2'
                                ,v4 as '3'
                                ,v5 as '4'
                                ,v6 as '5'
                                ,v7 as '6'
                                ,v8 + ' ' as '7'
                                ,v9 as '8'
                                ,v10 as '9'
                                ,v11 as '10'
                                ,v12 as '11'
                                ,v13 as '12'
                                ,v14 as '13'
                                ,v15 as '14'
                                ,v16 as '15'
                                ,v17 as '16'
                                ,v18 as '17'
                                ,v19 as '18'
                                ,v20 as '19'
                                ,v21 as '20'
                        FROM rpt2.vol_MO_analit_Neotl_3_1_9_2018($params)
                ";
            break;

            case 'M3_analit_APU_obrasch_0108.xls': 
				$valList = array();
				$valList[] = 'num';
				for($i = 2 ; $i < 81; $i++) {
					$valList[] = 'v'.($i+1) . (($i==5 || $i==7) ? "+' ' " : "") . ' as "'.$i.'"';
				}
                $query = "
					SELECT ". join(', ', $valList) ."
					FROM rpt2.vol_MO_analit_APU_obrasch_3_1_10_2018($params)
				";
            break;
		
            case 'M3_analit_APU_LDU_0110.xls': 
                $query = "
					SELECT 
						ROW_NUMBER() OVER(order by num) num
						--,num as numm
						,v5 as '4'
						,v6 as '5'
						,v7 as '6'
						,v8 as '7'
						,v9 as '8'
					FROM rpt2.vol_MO_analit_APU_LDU_3_2_2018($params)
				";
            break;
		
            case 'M3_analit_dializ_0108.xls':
                    $query = "
                            SELECT 
                                    num
                                    ,v5 as '4'
                                    ,v6 as '5'
                                    ,v7 as '6'
                                    ,v8 as '7'
                                    ,v9 as '8'
                                    ,v10 as '9'
                                    ,v11 as '10'
                                    ,v12 as '11'
                                    ,v13 as '12'
                                    ,v14 as '13'
                                    ,v15 as '14'
                                    ,v16 as '15'
                                    ,v17 as '16'
                                    ,v18 as '17'
                                    ,v19 as '18'
                                    ,v20 as '19'
                                    ,v21 as '20'
                                    ,v22 as '21'
                                    ,v23 as '22'
                                    ,v24 as '23'
                                    ,v25 as '24'
                                    ,v26 as '25'
                                    ,v27 as '26'
                            FROM rpt2.vol_MO_analit_Dializ_5_2018($params)
                    ";
            break;

            case 'M3_analit_prof_0108.xls':
				$valList = array();
				$valList[] = 'num';
				for($i = 2 ; $i < 79; $i++) {
					$valList[] = 'all_'.($i+1).' as "'.$i.'"';
				}
				$query = "
					SELECT ". join(', ', $valList) ."
					FROM rpt2.vol_MO_analit_Prof_all_2018($params)
				";
			break;

            case 'MZ_analit_prof_dispancer_0108.xls':
                $query = "SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 + ' ' as '5'
                            ,v7 as '6'
                            ,v8 + ' ' as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                            ,v19 as '18'
                            ,v20 as '19'
                            ,v21 as '20'
                            ,v22 as '21'
                            ,v23 as '22'
                            ,v24 as '23'
                    FROM rpt2.vol_MO_analit_Disp_3_1_4_2018($params)
                ";
            break;

            case 'MZ_anallit_prof_0108.xls':
                $query = "SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 + ' ' as '5'
                            ,v7 as '6'
                            ,v8 + ' ' as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                            ,v19 as '18'
                            ,v20 as '19'
                            ,v21 as '20'
                            ,v22 as '21'
                            ,v23 as '22'
                            ,v24 as '23'
                            ,v25 as '24'
                            ,v26 as '25'
                            ,v27 as '26'
                    FROM rpt2.vol_MO_analit_Prof_Kons_3_1_1_2018($params)
                ";
            break;

            case 'MZ_analit_prof_neprikrep_0108.xls':
                $query = "SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 + ' ' as '5'
                            ,v7 as '6'
                            ,v8 + ' ' as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                            ,v19 as '18'
                            ,v20 as '19'
                            ,v21 as '20'
                            ,v22 as '21'
                            ,v23 as '22'
                            ,v24 as '23'
                            ,v25 as '24'
                            ,v26 as '25'
                            ,v27 as '26'
                    FROM rpt2.vol_MO_analit_Prof_notAttach_3_1_2_2018($params)
                ";
            break;

            case 'MZ_analit_prof_podushevka_0108.xls':
                $query = "SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 + ' ' as '5'
                            ,v7 as '6'
                            ,v8 + ' ' as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                            ,v19 as '18'
                            ,v20 as '19'
                            ,v21 as '20'
                            ,v22 as '21'
                            ,v23 as '22'
                            ,v24 as '23'
                            ,v25 as '24'
                            ,v26 as '25'
                            ,v27 as '26'
                            ,v28 as '27'
                            ,v29 as '28'
                            ,v30 as '29'
                            ,v31 as '30'
                            ,v32 as '31'
                            ,v33 as '32'
                            ,v34 as '33'
                            ,v35 as '34'
                            ,v36 as '35'
                            ,v37 as '36'
                            ,v38 as '37'
                            ,v39 as '38'
                            ,v40 as '39'
                            ,v41 as '40'
                            ,v42 as '41'
                            ,v43 as '42'
                            ,v44 as '43'
                            ,v45 as '44'
                            ,v46 as '45'
                            ,v47 as '46'
                    FROM rpt2.vol_MO_analit_Prof_Podush_3_1_3_2018($params)
                ";

            break;

            case 'MZ_analit_prof_medosmotr_0108.xls'://5
                $query = "SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 + ' ' as '5'
                            ,v7 as '6'
                            ,v8 + ' ' as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                            ,v19 as '18'
                    FROM rpt2.vol_MO_analit_Prof_Medos_3_1_5_2018($params)
                ";
            break;

            case 'MZ_analit_prof_cz_0108.xls'://6
                $query = "SELECT
                            num
                            ,v3 as '2'
                            ,v4 as '3'
                            ,v5 as '4'
                            ,v6 + ' ' as '5'
                            ,v7 as '6'
                            ,v8 + ' ' as '7'
                            ,v9 as '8'
                            ,v10 as '9'
                            ,v11 as '10'
                            ,v12 as '11'
                            ,v13 as '12'
                            ,v14 as '13'
                            ,v15 as '14'
                            ,v16 as '15'
                            ,v17 as '16'
                            ,v18 as '17'
                            ,v19 as '18'
                            ,v20 as '19'
                            ,v21 as '20'
                            ,v22 as '21'
                            ,v23 as '22'
                            ,v24 as '23'
                            ,v25 as '24'
                            ,v26 as '25'
                            ,v27 as '26'
                            ,v28 as '27'
                            ,v29 as '28'
                            ,v30 as '29'
                            ,v31 as '30'
                            ,v32 as '31'
                            ,v33 as '32'
                            ,v34 as '33'
                            ,v35 as '34'
                            ,v36 as '35'
                            ,v37 as '36'
                            ,v38 as '37'
                            ,v39 as '38'
                    FROM rpt2.vol_MO_analit_Prof_CZiG_3_1_6_2018($params)
                ";
            break;

            case 'MZ_FED_KS_0108.xls':
                $query = $this->getSelectQuery("rpt2.vol_MO_fed_KS_1_1_2018($params)",3,29,'v1');
            break;

            case 'MZ_FED_KS_0110.xls':
                $query = $this->getSelectQuery("rpt2.vol_MO_fed_KS_1_1_2018($params)",3,11,'v1','kp_v');
            break;

            case 'MZ_FED_KSG_0108.xls':
                $dbFunc = "rpt2.vol_MO_fed_KS_KSG_1_2_2018($params)";
                $query = $this->getSelectQuery($dbFunc,3,11);
            break;

            case 'MZ_FED_KSG_0110.xls':
                $dbFunc = "rpt2.vol_MO_fed_KS_KSG_1_2_2018($params)";
                $query = $this->getSelectQuery($dbFunc,3,5,'num','kp_v');
            break;

            case 'MZ_FED_DS_KSG_0108.xls':
                $dbFunc = "rpt2.vol_MO_fed_DS_KSG_2_2_2018($params)";
                $query = $this->getSelectQuery($dbFunc,3,11);
                //echo $query;
            break;

            case 'MZ_FED_DS_KSG_0110.xls':
                $dbFunc = "rpt2.vol_MO_fed_DS_KSG_2_2_2018($params)";
                $query = $this->getSelectQuery($dbFunc,3,5,'num','kp_v');
            break;

            case 'MZ_FED_DS_2017_0108.xls':
            case 'MZ_FED_DS_2018_0108.xls':
            case 'MZ_FED_DS_2019_0108.xls':
            case 'MZ_FED_DS_2019_0110.xls':
                if($file == 'MZ_FED_DS_2019_0110.xls') $vol = 4;
                else if($file == 'MZ_FED_DS_2019_0108.xls') $vol = 3;
                else if($file == 'MZ_FED_DS_2018_0108.xls') $vol = 2;
                else if($file == 'MZ_FED_DS_2017_0108.xls') $vol = 1;
                $dbFunc = "rpt2.vol_MO_fed_DS_2_1_2018($params,$vol)";
                $query = $this->getSelectQuery($dbFunc,3,53,'v1');
            break;

            case 'MZ_FED_APU_2016_0108.xls':
            case 'MZ_FED_APU_2017_0108.xls':
            case 'MZ_FED_APU_2018_0108.xls':
            case 'MZ_FED_APU_2019_0108.xls':
            case 'MZ_FED_APU_2019_0110.xls':
                if($file == "MZ_FED_APU_2017_0108.xls")      $prefix = "vol2_";
                else if($file == "MZ_FED_APU_2018_0108.xls") $prefix = "vol4_";
                else if($file == "MZ_FED_APU_2019_0108.xls") $prefix = "P_";
                else if($file == "MZ_FED_APU_2019_0110.xls") $prefix = "KP_";
                else if($file == "MZ_FED_APU_2016_0108.xls") $prefix = "vol1_";

                $query = "select num";
                for($i=2;$i<=19;++$i)
                    $query .= ",".$prefix.$i." as '$i' ";
                $query .= " from rpt2.vol_MO_fed_APU_2018($params)";
            break;

            case 'MZ_FED_VMP_2019_0110_SVOD':
                $query = "SELECT '1' as num ,'0' as '0'";
            break;

            case 'MZ_FED_VMP_2019_0110':
            case 'MZ_FED_VMP_2019_0108.xls':
            case 'MZ_FED_VMP_2018_0108.xls':
            case 'MZ_FED_VMP_2017_0108.xls':
                if($file == "MZ_FED_VMP_2017_0108.xls") $column = "Vol2";
                else if($file == "MZ_FED_VMP_2018_0108.xls") $column = "Vol4";
                else if ($file == "MZ_FED_VMP_2019_0108.xls") $column = "VolP";
                else if ($file == "MZ_FED_VMP_2019_0110") $column = "VolKP";
                $query = "select 
                    num
                    ,v2 as '1'
                    ,case when v3='ВСЕГО' then '' else v3 end '2'
                    ,t4 as '3'
                    ,t5 as '4'
                    ,t6 as '5'
                    ,t7 as '6'
                    ,t8 as '7'
                    ,$column as '9'
                from [rpt2].[vol_MO_fed_VMP_1_4_2018]($params)";
            break;


            case 'MZ_FED_SMP_2017_0108':
            case 'MZ_FED_SMP_2018_0108':
            case 'MZ_FED_SMP_2019_0108':
            case 'MZ_FED_SMP_2019_0110':
                if($file == "MZ_FED_SMP_2017_0108") $prefix = "vol2";
                else if($file == "MZ_FED_SMP_2018_0108") $prefix = "Vol4";
                else if ($file == "MZ_FED_SMP_2019_0108") $prefix = "VolP";
                else if ($file == "MZ_FED_SMP_2019_0110") $prefix = "VolKP";
                $query = "SELECT 
                    num
                    ,v3 as '2'
                    ,{$prefix}_v4 as '3'
                    ,{$prefix}_v5 as '4'
                    ,{$prefix}_v6 as '5'
                    FROM rpt2.vol_MO_fed_SMP_4_2_2018($params)
                ";
            break;

            case "MZ_FED_SMP_2017_0108_SVOD":
            case 'MZ_FED_SMP_2018_0108_SVOD':
            case 'MZ_FED_SMP_2019_0108_SVOD':
            case 'MZ_FED_SMP_2019_0110_SVOD':
                if($file == "MZ_FED_SMP_2017_0108_SVOD") $prefix = "vol2";
                else if($file == "MZ_FED_SMP_2018_0108_SVOD") $prefix = "Vol4";
                else if ($file == "MZ_FED_SMP_2019_0108_SVOD") $prefix = "VolP";
                else if ($file == "MZ_FED_SMP_2019_0110_SVOD") $prefix = "VolKP";
                $query = "SELECT 
                    row_number() over(order by num) as num
                    ,v1 as '0'
                    ,v2 as '1'
                    ,v3 as '2'
                    ,v4 as '3'
                    ,{$prefix}_5 as '4'
                    ,{$prefix}_6 as '5'
                    ,{$prefix}_7 as '6'
                    FROM [rpt2].[vol_MZ_fed_SMP_svodSummary_2018]($params)";
            break;
            
            case 'M3_analit_form_SVOD_DS_0108.xls':
                    $query="SELECT
                                    v1 as num,
                                    v3 as '4',
                                    v4 as '5',
                                    v5 as '6',
                                    v6 + ' ' as '7',
                                    v7 as '8',
                                    v8 as '9',
                                    v9 as '10',
                                    v10 as '11',
                                    v11 as '12',
                                    v12 as '13',
                                    v13 as '14',
                                    v14 as '15',
                                    v15 as '16',
                                    v16 as '17',
                                    v17 as '18',
                                    v18 as '19',
                                    v19 as '20',
                                    v20 as '21',
                                    v21 as '22',
                                    v22 as '23',
                                    v23 as '24',
                                    v24 as '25',
                                    v25 as '26',
                                    v26 as '27',
                                    v27 as '28',
                                    v28 as '29',
                                    v29 as '30',
                                    v30 as '31',
                                    v31 as '32',
                                    v32 as '33',
                                    v33 as '34',
                                    v34 as '35',
                                    v35 as '36',
                                    v36 as '37',
                                    v37 as '38',
                                    v38 as '39',
                                    v39 as '40',
                                    v40 as '41',
                                    v41 as '42',
                                    v42 as '43',
                                    v43 as '44',
                                    v44 as '45',
                                    v45 as '46',
                                    v46 as '47',
                                    v47 as '48',
                                    v48 as '49',
                                    v49 as '50',
                                    v50 as '51',
                                    v51 as '52',
                                    v52 as '53',
                                    v53 as '54',
                                    v54 as '55',
                                    v55 as '56',
                                    v56 as '57',
                                    v57 as '58',
                                    v58 as '59'
                            FROM [rpt2].[vol_MO_analit_DS_2_1_2018]($params)";
            break;
        
            case 'MZ_PGG_PROF_0108.xls':
                    $query="select num, prognoz as '2', zayav as '3'
                            from [rpt2].[vol_MO_analit_APU_Profilaktika_PGG_2019_XLS]($params)";
            break;

            case 'MZ_PGG_PROF_0110.xls':
                $query="select 
                            ROW_NUMBER() OVER(order by cast(isnull(v1,999) as float)) num,
                            v4 as '3',
                            v6 as '4',
                            v8 as '5',
                            v9 as '6',
                            v10 as '7',
                            v11 as '8',
                            v12 as '9',
                            v13 as '10',
                            v14 as '11',
                            v15 as '12',
                            v16 as '13',
                            v17 as '14',
                            v18 as '15',
                            v19 as '16',
                            v20 as '17',
                            v21 as '18',
                            v22 as '19',
                            v23 as '20',
                            v24 as '21',
                            v25 as '22',
                            v27 as '23',
                            v29 as '24',
                            v30 as '25',
                            v31 as '26',
                            v32 as '27',
                            v33 as '28',
                            v34 as '29',
                            v35 as '30',
                            v36 as '31',
                            v38 as '32',
                            v39 as '33',
                            v40 as '34',
                            v41 as '35',
                            v42 as '36',
                            v43 as '37',
                            v44 as '38',
                            v45 as '39',
                            v46 as '40'
                        from [rpt2].[vol_MO_PGG_Prof_3_1_8_2019]($params)";
            break;

            default:
                throw new Exception('Не задан запрос для получения данных');
            break;
        }
        $data = $this->db->query($query, true);
        return $data = $data->result('array');
    }

    /**
     * Генерим запрос для выборки данных
     * @param string $dbFuncName - схема + наименование функции в бд 
     * @param int $beg - начальный столбец
     * @param int $end - конечный столбец
     * @param array $excl - исключенные столбцы, переводим их в строковый формат
     */
    function getSelectQuery($dbFuncName, $beg, $end, $rowAlias = null,$prefix = null)
    {
        $rowAlias = !empty($rowAlias) ? $rowAlias : 'num';
        $prefix = !empty($prefix) ? $prefix : 'v';

        $query = "select $rowAlias as num";
        for($i=$beg;$i<=$end;++$i)
            $query .= ",".$prefix.$i." as '".($i-1)."' ";
        $query .= " from ".$dbFuncName;
        return $query;
    }

    /**
     * Выгрузка сводных отчетов в Excel
     */
    function ExportXls($data)
    {
        $request_id = $data['Request_id'];
        $form = $data['form'];
        switch($form) {
            case 'SMP_analit':
                $file = 'M3_analit_form_SMP_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [ [6,3] ],
                        'lpu_name'     => [ [6,5] ],
                        'lpu_lvl'      => [ [6,1] ],
                        'lpu_head'     => [ [27,2] ],
                        'lpu_deputy'   => [ [29,2] ],
                        'lpu_executor' => [ [31,2] ]
                ];
                for($i=1; $i<10; $i++)
                        $rows[$i] = $i + 15;
            break;//1

            case 'VMP_analit':
                $file = 'M3_analit_Form_VMP_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [ [7,1] ],
                        'lpu_name'     => [ [7,2] ],
                        'lpu_lvl'      => [ [7,3] ],
                        'lpu_head'     => [ [87,1] ],
                        'lpu_deputy'   => [ [89,1] ],
                        'lpu_executor' => [ [91,1] ]
                ];
                for($i=1; $i<74; ++$i){
                        $rows[$i] = $i + 13;
                }
            break;//2

            case 'KS_analit':
                $file = 'M3_analit_form_KS_0108.xls';
                $cells = [ //row,cell
                        'lpu_f003mcod' => [ [6,1] ],
                        'lpu_name'     => [ [6,2] ],
                        'lpu_lvl'      => [ [6,3] ],
                        'lpu_head'     => [ [47,1] ],
                        'lpu_deputy'   => [ [49,1] ],
                        'lpu_executor' => [ [51,1] ]
                ];
                for($i=1; $i<32; $i++)
                        $rows[$i] = $i<31 ? $i+11 : $i+14;
            break;//3

            case 'KSG_analit':
                $file = 'M3_analit_form_KSG_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [ [6,1] ],
                        'lpu_name'     => [ [6,2] ],
                        'lpu_lvl'      => [ [6,3] ],
                        'lpu_head'     => [ [414,4] ],
                        'lpu_deputy'   => [ [416,2] ],
                        'lpu_executor' => [ [418,2] ]
                ];
                for($i=1; $i<401; $i++)
                        $rows[$i] = $i + 11;
                
                
                if($data['part'])
                    $part = $data['part'];

                $hospitals = $this->getRequestListPart($request_id,$part);
                return $this->GenerateStaticXls($file,$cells,$hospitals,$rows);
            break;//4

            case 'MZ_fed_form_KSG':
                $file = 'M3_fed_form_KSG_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [ [6,1] ],
                        'lpu_name'     => [ [6,2] ],
                        'lpu_head'     => [ [396,2] ],
                        'lpu_deputy'   => [ [398,2] ],
                        'lpu_executor' => [ [400,2] ]
                ];
                for($i=1; $i<383; $i++)
                        $rows[$i] = $i + 11;
            break;//5

            case 'MZ_anal_form_ECO':
                $file = 'M3_analit_form_SVOD_ECO.xls';
                $cells = [
                        'lpu_lvl'      => [ [6,0] ],
                        'lpu_f003mcod' => [ [6,1] ],
                        'lpu_name'     => [ [6,2] ],
                        'lpu_head'     => [ [16,1] ],
                        'lpu_deputy'   => [ [18,1] ],
                        'lpu_executor' => [ [20,1] ]
                ];
                $rows[1] = 13;
            break;//8

            case 'MZ_anal_form_SVOD_DS':
                $file = 'M3_analit_form_SVOD_DS_0108.xls';
                $cells = [
                        'lpu_lvl'      => [ [4,3] ],
                        'lpu_f003mcod' => [ [4,1] ],
                        'lpu_name'     => [ [4,2] ],
                        'lpu_head'     => [ [44,1] ],
                        'lpu_deputy'   => [ [46,1] ],
                        'lpu_executor' => [ [48,1] ]
                ];
                for($i=1; $i<32; ++$i)
                    $rows[$i] = $i<30 ? $i+10 : $i+12;
            break;//6

            case 'MZ_anal_form_DS_SVOD_KSG':
                $file = 'M3_analit_form_SVOD_DS_KSG_0108.xls';
                $cells = [
                        'lpu_lvl'      => [ [6,3] ],
                        'lpu_f003mcod' => [ [6,1] ],
                        'lpu_name'     => [ [6,2] ],
                        'lpu_head'     => [ [203,1] ],
                        'lpu_deputy'   => [ [205,1] ],
                        'lpu_executor' => [ [207,1] ]
                ];
                for($i = 1; $i < 188; ++$i)
                        $rows[$i] = $i + 12;
            break;//7

            case 'MZ_analit_APU_neotl':
                $file = 'M3_analit_neotl_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [ [6,1] ],
                        'lpu_name'     => [ [6,6], [6,14] ],
                        'lpu_lvl'      => [ [6,10] ],
                        'lpu_head'     => [ [56,5], [56,11] ],
                        'lpu_deputy'   => [ [58,4], [58,9] ],
                        'lpu_executor' => [ [60,5], [60,11] ]
                ];
                for($i = 1; $i <= 39; ++$i )
                    $rows[$i] = $i + 15;
            break;

            case 'MZ_analit_APU_obrasch':
                $file = 'M3_analit_APU_obrasch_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [ [8,1] ],
                        'lpu_name'     => [ [8,7], [8,24], [8,41] ],
                        'lpu_lvl'      => [ [8,12], [8,28], [8,44] ],
                        'lpu_head'     => [ [60,5], [60,11], [60,17] ],
                        'lpu_deputy'   => [ [62,4], [62,9], [62,14] ],
                        'lpu_executor' => [ [64,5], [64,11], [64,17] ]
                ];//19
                for($i = 1; $i <= 40; ++$i )
                    $rows[$i] = $i + 17;
            break;
			
            case 'MZ_analit_APU_LDU':
                    $file = 'M3_analit_APU_LDU_0110.xls';
                    $cells = [
                                    //'lpu_f003mcod' => [ [6,1] ],
                                    'lpu_name'     => [ [6,3] ],
                                    'lpu_lvl'      => [ [6,1], [6,4] ],
                                    'lpu_head'     => [ [38,3] ],
                                    'lpu_deputy'   => [ [40,3] ],
                                    'lpu_executor' => [ [42,3] ]
                    ];
                    //for($i = 14; $i <= 33; ++$i )
                    //    $rows[$i-12] = $i;

                    for($i = 1; $i < 25; ++$i )
                    {
                        $rows[$i] = $i + 12;
                    }
            break;

            case 'MZ_analit_dializ':
                    $file = 'M3_analit_dializ_0108.xls';
                    $cells = [
                                    'lpu_f003mcod' => [ [6,3] ],
                                    'lpu_name'     => [ [6,8] ],
                                    'lpu_lvl'      => [ [6,15] ],
                                    'lpu_head'     => [ [31,5] ],
                                    'lpu_deputy'   => [ [33,5] ],
                                    'lpu_executor' => [ [35,5] ]
                    ];
                    $firstRow = 13;
                    for($i = 1; $i <= 15; ++$i ) {
                            switch ($i) {
                                    case 1:
                                    case 5:
                                    case 9:
                                            $rows[$i] = $i + $firstRow + 1;
                                            break;
                                    case 2:
                                    case 6:
                                    case 10:
                                            $rows[$i] = $i + $firstRow - 1;
                                            break;
                                    case 11:
                                    case 12:
                                            $rows[$i] = $i + $firstRow + 2;
                                            break;
                                    case 13:
                                    case 14:
                                            $rows[$i] = $i + $firstRow - 2;
                                            break;
                                    default:
                                            $rows[$i] = $i + $firstRow;
                                            break;
                            }
                    }
            break;

            case 'MZ_analit_prof':
                    $file = 'M3_analit_prof_0108.xls';
                    $cells = [
                            'lpu_f003mcod' => [ [8,1] ],
                            'lpu_name'     => [ [8,8], [8,26], [8,41] ],
                            'lpu_lvl'      => [ [8,12], [8,30], [8,45] ],
                            'lpu_head'     => [ [58,5], [58,13], [58,21] ],
                            'lpu_deputy'   => [ [60,5], [60,13], [60,21] ],
                            'lpu_executor' => [ [62,5], [62,13], [62,21] ]
                    ];
                    for($i = 1; $i <= 39; ++$i )
                            $rows[$i] = $i + 17;
            break;
		


            case 'MZ_analit_disp':
                $file = 'MZ_analit_prof_dispancer_0108.xls';
                $cells = [ //строка: 9, ячейки: 2,7,11
                        'lpu_f003mcod' => [[8,1]],
                        'lpu_name'     => [[8,6]],
                        'lpu_lvl'      => [[8,10]],
                        'lpu_head'     => [[58,5]],
                        'lpu_deputy'   => [[60,5]],
                        'lpu_executor' => [[62,5]]
                ];

                for($i = 1; $i <= 39; ++$i)
                        $rows[$i] = $i + 17;
            break;

            case 'MZ_analit_cons':
                $file = 'MZ_anallit_prof_0108.xls';
                $cells = [//9:2,8,11
                        'lpu_f003mcod' => [[8,1]],
                        'lpu_name'     => [[8,7]],
                        'lpu_lvl'      => [[8,10]],
                        'lpu_head'     => [[58,5]],
                        'lpu_deputy'   => [[60,4]],
                        'lpu_executor' => [[62,5]]
                ];

                for($i = 1; $i <= 39; ++$i)
                        $rows[$i] = $i + 17;
            break;

            case 'MZ_analit_notAttach':
                $file = 'MZ_analit_prof_neprikrep_0108.xls';
                $cells = [//8:2,6,9
                        'lpu_f003mcod' => [[8,1]],
                        'lpu_name'     => [[8,5]],
                        'lpu_lvl'      => [[8,8]],
                        'lpu_head'     => [[59,8]],
                        'lpu_deputy'   => [[61,7]],
                        'lpu_executor' => [[63,8]]
                ];

                for($i = 1; $i <= 39; ++$i)
                        $rows[$i] = $i + 17;
            break;

            case 'MZ_analit_podush':
                $file = 'MZ_analit_prof_podushevka_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [[7,1]],
                        'lpu_name'     => [[7,5],[7,2]],
                        'lpu_lvl'      => [[7,6],[7,3]],
                        'lpu_head'     => [[57,5],[57,10]],//10
                        'lpu_deputy'   => [[59,5],[59,10]],//10
                        'lpu_executor' => [[61,6],[61,11]]//12
                ];

                for($i = 1; $i <= 39; ++$i)
                        $rows[$i] = $i + 16;
            break;

            case 'MZ_analit_medos':
                $file = 'MZ_analit_prof_medosmotr_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [[8,1]],
                        'lpu_name'     => [[8,6]],
                        'lpu_lvl'      => [[8,9]],
                        'lpu_head'     => [[58,5]],
                        'lpu_deputy'   => [[60,5]],
                        'lpu_executor' => [[62,5]]
                ];

                for($i = 1; $i <= 39; ++$i)
                        $rows[$i] = $i + 17;
            break;

            case 'MZ_analit_cz':
                $file = 'MZ_analit_prof_cz_0108.xls';
                $cells = [
                        'lpu_f003mcod' => [[8,1]],
                        'lpu_name'     => [[8,7],[8,18]],
                        'lpu_lvl'      => [[8,9],[8,21]],
                        'lpu_head'     => [[58,5],[58,16]],
                        'lpu_deputy'   => [[60,5],[60,16]],
                        'lpu_executor' => [[62,5],[62,16]]
                ];

                for($i = 1; $i <= 39; ++$i)
                        $rows[$i] = $i + 17;
            break;


            case 'MZ_FED_KS_0108':
            case 'MZ_FED_KS_0110':
                $file = $form.'.xls';
                $cells = [
                        'lpu_f003mcod' => [[7,1]],
                        'lpu_name'     => [[7,3]],
                        'lpu_head'     => [[50,3]],
                        'lpu_deputy'   => [[52,3]],
                        'lpu_executor' => [[54,3]]
                ];

                for($i = 1; $i <= 30; ++$i)
                        $rows[$i] = $i + 13;
                $rows[31] = 47;
            break;

            case 'MZ_FED_KSG_0108':
            case 'MZ_FED_KSG_0110':
                $file = $form.'.xls';
                $cells = [
                        'lpu_f003mcod' => [[6,1]],
                        'lpu_name'     => [[6,3]],
                        'lpu_head'     => [[396,4]],
                        'lpu_deputy'   => [[398,4]],
                        'lpu_executor' => [[400,4]]
                ];

                for($i = 1; $i <= 382; ++$i)
                        $rows[$i] = $i + 11;
            break;

            case 'MZ_FED_DS_KSG_0108':
            case 'MZ_FED_DS_KSG_0110':
                $file = $form.'.xls';
                
                $cells = [
                        'lpu_f003mcod' => [[6,1]],
                        'lpu_name'     => [[6,3]],
                        'lpu_head'     => [[186,2]],
                        'lpu_deputy'   => [[188,2]],
                        'lpu_executor' => [[190,2]]
                ];

                for($i = 1; $i <= 171; ++$i)
                        $rows[$i] = $i + 11;
            break;

            case 'MZ_FED_DS_2017_0108':
            case 'MZ_FED_DS_2018_0108':
            case 'MZ_FED_DS_2019_0108':
            case 'MZ_FED_DS_2019_0110':
                $file = $form.'.xls';
                $cells = [
                    'lpu_f003mcod' => [[5,1],[5,8]],
                    'lpu_name'     => [[5,4],[5,11]],
                    'lpu_head'     => [[48,18],[48,24]],
                    'lpu_deputy'   => [[50,13],[50,19]],
                    'lpu_executor' => [[52,13],[52,15]]
                ];

                for($i = 1; $i <= 29; ++$i)
                        $rows[$i] = $i + 12;
                $rows[30] = 44;
                $rows[31] = 45;
            break;

            case 'MZ_FED_APU_2016_0108':
            case 'MZ_FED_APU_2018_0108':
            case 'MZ_FED_APU_2017_0108':
            case 'MZ_FED_APU_2019_0108':
            case 'MZ_FED_APU_2019_0110':

                $file = $form.'.xls';
                $cells = [
                    'lpu_f003mcod' => [[8,1]],
                    'lpu_name'     => [[8,4],[8,9]],
                    'lpu_head'     => [[57,7],[57,10]],
                    'lpu_deputy'   => [[59,3],[59,6]],
                    'lpu_executor' => [[61,3],[61,6]]
                ];

                for($i = 1; $i <= 39; ++$i)
                        $rows[$i] = $i + 16;
            break;

            case 'MZ_FED_VMP_2019_0108':
            case 'MZ_FED_VMP_2018_0108':
            case 'MZ_FED_VMP_2017_0108':
                $file = $form.'.xls';
                $cells = [
                        'lpu_f003mcod' => [ [5,1] ],
                        'lpu_name'     => [ [5,3] ],
                        'lpu_head'     => [ [13,1] ],
                        'lpu_deputy'   => [ [15,1] ],
                        'lpu_executor' => [ [17,2] ]
                ];

                $hospitals = $this->getRequestList($request_id);
                $this->GenerateDynamicXls($file,$cells,$hospitals,10,10);
            break;


            case 'MZ_FED_VMP_2019_0110':
                $this->getDynamicReportXls($form,$request_id);
            break;

            case 'MZ_FED_SMP_2017_0108':
            case 'MZ_FED_SMP_2018_0108':
            case 'MZ_FED_SMP_2019_0108':
            case 'MZ_FED_SMP_2019_0110':
                $this->getDynamicReportXls($form,$request_id);
            break;
        
            case 'MZ_PGG_PROF_0108':
                $file = $form.'.xls';
                $cells = [
                        'lpu_lvl'      => [ [3,3] ],
                        'lpu_f003mcod' => [ [3,0] ],
                        'lpu_name'     => [ [3,1] ],
                        'lpu_head'     => [ [24,2] ],
                        'lpu_deputy'   => [ [26,2] ],
                        'lpu_executor' => [ [28,2] ]
                ];
                
                for($i=1; $i<17; ++$i)
                {
                    $rows[$i] = $i+7;
                }
                
            break;

            case 'MZ_PGG_PROF_0110':
                $file = $form.'.xls';
                $cells = [
                        'lpu_lvl'      => [ [6,4] ],
                        'lpu_f003mcod' => [ [6,1] ],
                        'lpu_name'     => [ [6,2] ],
                        'lpu_head'     => [ [58,5] ],
                        'lpu_deputy'   => [ [60,5] ],
                        'lpu_executor' => [ [62,6] ]
                ];

                for($i=1; $i<41; ++$i)
                {
                    $rows[$i] = $i+17;
                }

            break;

            default:
                throw new Exception('Не объявлены параметры для генерации отчета');
            break;
        }

        $hospitals = $this->getRequestList($request_id);
        $this->GenerateStaticXls($file,$cells,$hospitals,$rows);
    }
    
    /**
     * Генерация формы отчета (на каждой странице одинаковое кол-во строк)
     */
    function GenerateStaticXls($fileName,$cells,$hospitals,$rows) {
        $xml = simplexml_load_file("export/template/".$fileName);
        $dom_thing = dom_import_simplexml($xml);
        $sum = [];

        foreach($hospitals as $i=>$request) {
            $tmpSheet = clone $xml->Worksheet[1];
            $this -> ChangeSheetName($tmpSheet,$request['lpu_nick']); //del $i
            $this -> insLpuDataToWorksheet($tmpSheet, $request, $cells);
            unset($hospitals[$i]);

            $data = $this -> getRequestData($fileName, $request['RequestList_id']);
            if($data){
                $this -> insRequestDataToWorksheet($tmpSheet, $data, $rows, $sum);
                unset($data);
            }

            $dom_thing -> appendChild(dom_import_simplexml($tmpSheet));
        }
        unset($xml ->Worksheet[1]);
        if($sum)
            $this -> insSvodToWorksheet($xml->Worksheet[0], $sum);
        unset($dom_node);

        $fileName = date("Y-m-d_His_").$fileName;
        $xml -> asXML($fileName);
        $this->downloadZip($fileName);
        unlink($fileName);
    }

    /**
     * Генерация формы отчета (не статическое кол-во строк на страницах)
     */
    function GenerateDynamicXls($fileName, $cells, $hospitals, $offset,$tmpRowIndex) {
        $xml = simplexml_load_file("export/template/".$fileName);
        $dom_thing = dom_import_simplexml($xml);
        $sum = [];

        foreach($hospitals as $i=>$request) {
            $tmpSheet = clone $xml->Worksheet[0];
            $tmpSheet -> Table-> attributes('ss',TRUE)->ExpandedRowCount = 1000;
            $this -> ChangeSheetName($tmpSheet,$request['lpu_nick']); //del $i
            $this -> insLpuDataToWorksheet($tmpSheet, $request, $cells);

            $data = $this -> getRequestData($fileName, $request['RequestList_id']);

            $dataCount = count($data);
            if($dataCount > 2)
                $this -> insRowsToWorksheet($tmpSheet->Table->Row[$tmpRowIndex], $dataCount - 2); //-2 потому что 2 строки уже есть в таблице

            foreach($data as $i=>$sprObj){
                $rows[$i+1] = $sprObj['num'] + $offset - 1;
            }
            unset($hospitals[$i]);


            if($data){
                $this -> insRequestDataToWorksheet($tmpSheet, $data, $rows);
                unset($data);
            }
            $dom_thing -> appendChild(dom_import_simplexml($tmpSheet));
        }
        unset($xml ->Worksheet[0]);
        unset($dom_thing);
        unset($dom_node);

        $fileName = date("Y-m-d_His_").$fileName;
        $xml -> asXML($fileName);
        $this->downloadZip($fileName);
        unlink($fileName);
    }

    /**
     * Генерация отчета (не статичное кол-во строк, страница СВОД'а получается c функции в БД)
     */
    function getStaticReportXls($formName,$Request_id) {

        //шаблон отчета
        $xml = simplexml_load_file("export/template/".$formName.".xls");
        $dom_thing = dom_import_simplexml($xml);

        //параметры для генетации отчета
        $param = $this->getFormParams($formName);

        //страница СВОД'а
        $vaultSheet = $xml->Worksheet[0];
        //получаем данные по свод'у
        $vaultData = $this->getRequestData($formName."_SVOD",$Request_id);

        //добавляем данные на страницу СВОД'а
        if(!empty($vaultData))
            $this -> insRequestDataToWorksheet($vaultSheet, $vaultData, $param['firSheetRows']);

        unset($vaultSheet,$vaultData);

        //заявки от МО
        $hospitals = $this->getRequestList($Request_id);

        foreach($hospitals as $i=>$request) {
            //клонируем страницу заявки
            $tmpSheet = clone $xml->Worksheet[1];

            //изменяем имя страницы
            $this -> ChangeSheetName($tmpSheet,$request['lpu_nick']);

            //добавляем шапку заявки
            $this -> insLpuDataToWorksheet($tmpSheet, $request, $param['cells']);
            unset($hospitals[$i]);

            //данные по заявке
            $data = $this -> getRequestData($formName, $request['RequestList_id']);
            if($data)
                $this -> insRequestDataToWorksheet($tmpSheet, $data, $param['secSheetRows']);
            unset($data);

            //прикрепляем страницу к документу
            $dom_thing -> appendChild(dom_import_simplexml($tmpSheet));
        }

        // удаляем временную страницу
        unset($xml ->Worksheet[1]);

        $fileName = date("Y-m-d_His_").$formName.'.xls';
        $xml -> asXML($fileName);
        $this->downloadZip($fileName);
        unlink($fileName);
    }

    /**
     * Генерация отчета (статичное кол-во строк, страница СВОД'а получается c функции в БД)
     */
    function getDynamicReportXls ($formName,$Request_id) {
        $xml = simplexml_load_file("export/template/".$formName.".xls");
        $dom_thing = dom_import_simplexml($xml);

        $param = $this->getFormParams($formName);

        $vaultSheet = $xml->Worksheet[0];
        $vaultSheet -> Table-> attributes('ss',TRUE)->ExpandedRowCount = 1000;

        //получаем данные по свод'у
        $vaultData = $this->getRequestData($formName."_SVOD",$Request_id);

        $vaultDataCount = count($vaultData);

        //добавляем необходимое количество строк
        if($vaultDataCount > 2)
            $this -> insRowsToWorksheet($vaultSheet->Table->Row[$param['firSheetRow']], $vaultDataCount - 2);

        //высчитываем номера строк
        foreach($vaultData as $i=>$sprObj)
            $rows[$i+1] = $sprObj['num'] + $param['firSheetOffset'];

        //добавляем данные
        $this -> insRequestDataToWorksheet($vaultSheet, $vaultData, $rows);

        unset($vaultSheet);
        unset($vaultData);
        unset($rows);

        $hospitals = $this->getRequestList($Request_id);

        foreach($hospitals as $i=>$request) {
            $tmpSheet = clone $xml->Worksheet[1];
            $tmpSheet -> Table-> attributes('ss',TRUE)->ExpandedRowCount = 1000;
            $this -> ChangeSheetName($tmpSheet,$request['lpu_nick']);

            $this -> insLpuDataToWorksheet($tmpSheet, $request, $param['cells']);

            $data = $this -> getRequestData($formName, $request['RequestList_id']);

            $dataCount = count($data);

            if(isset($param['secSheetRow'])) {

                if($dataCount > 2)
                    $this -> insRowsToWorksheet($tmpSheet->Table->Row[$param['secSheetRow']], $dataCount - 2);

                foreach($data as $i=>$sprObj){
                    $rows[$i+1] = $sprObj['num'] + $param['secSheetOffset'];
                }

            } else {

                $rows = $param['secSheetRows'];

            }

            if($data){
                $this -> insRequestDataToWorksheet($tmpSheet, $data, $rows);
                unset($data);
            }
            $dom_thing -> appendChild(dom_import_simplexml($tmpSheet));
        }
        unset($xml ->Worksheet[1]);
        unset($dom_thing);
        unset($dom_node);

        $fileName = date("Y-m-d_His_").$formName.".xls";
        $xml -> asXML($fileName);
        $this->downloadZip($fileName);
        unlink($fileName);
    }

    /**
     * Возвращает параметры для генерации форм отчетов
     */
    function getFormParams($form) {
        $param = array();
        switch($form) {
            case 'MZ_FED_VMP_2019_0110':
                $param['cells'] = [
                    'lpu_f003mcod' => [ [5,1] ],
                    'lpu_name'     => [ [5,3] ],
                    'lpu_head'     => [ [13,1] ],
                    'lpu_deputy'   => [ [15,1] ],
                    'lpu_executor' => [ [17,2] ]
                ];

                $param['firSheetOffset'] = 8;
                $param['firSheetRow'] = 9;
                $param['secSheetOffset'] = 9;
                $param['secSheetRow'] = 10;
            break;

            case 'MZ_FED_SMP_2017_0108':
            case 'MZ_FED_SMP_2018_0108':
            case 'MZ_FED_SMP_2019_0108':
            case 'MZ_FED_SMP_2019_0110':
                $param['cells'] = [
                    'lpu_f003mcod' => [ [5,1] ],
                    'lpu_name'     => [ [5,3] ]
                ];
                $param['firSheetOffset'] = 9;
                $param['firSheetRow'] = 10;
                //for($i=1;$i<=245;++$i)
                //    if($i!=239)
                //        $param['firSheetRows'][$i] = $i + 9;

                $param['secSheetOffset'] = 9;
                $param['secSheetRows'] = array();
                for($i=1;$i<=5;++$i)
                    $param['secSheetRows'][$i] = $i + 9;
            break;

        }
        return $param;
    }

    /**
    *  выгрузка в Excel
    */
    function downloadZip($fileName) {
        $zipName = time().'.zip';
        $zip = new ZipArchive;
        if ($zip->open($zipName,ZipArchive::CREATE) === TRUE) 
        {
            $zip->addFile($fileName);
            $zip->close();
        } 
        else 
        {
            echo 'ошибка';
        }

        if (ob_get_level()) 
        {
            ob_end_clean();
        }
        
        header("Content-type: application/zip"); 
        header("Content-Disposition: attachment; filename=".$zipName);
        header("Content-length: " . filesize($zipName));
        header("Pragma: no-cache"); 
        header("Expires: 0");
        readfile($zipName);
        unlink($zipName);
    }
}
