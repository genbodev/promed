<?php

require_once(APPPATH.'models/EvnSection_model.php');

class Samara_EvnSection_model extends EvnSection_model {
	/**
	 * Конструктор
	 */
    function __construct() {
		parent::__construct();
    }
	
	/**
	 * Печать ЭМК
	 */
	function printEmkForm($data){

		$params = array();
    	$params['EvnPS_id'] = $data['EvnPS_id'];		
		
		$query = "SELECT TOP 1 EFR.[EvnPS_id]
				  ,EFR.[Person_id]
				  ,EFR.[Lpu_id]
				  ,[Lpu_Name]
				  ,[addressLpu]
				  ,ISNULL(EFR.[EvnPS_NumCard], '') as EvnPS_NumCard
				  ,EFR.EvnPS_setDT --ISNULL(convert(varchar(20), [EvnPS_setDT], 100), '') EvnPS_setDT
				  ,ISNULL(convert(varchar(20), EFR.[EvnPS_disDT], 100), '') EvnPS_disDT
				  ,[LpuSection_Name]
				  ,[LpuSectionWard_Name]
				  ,[EvnPS_EntranceMode]
				  ,EFR.[EvnPS_DrugActions]
				  ,[Lsection_name]
				  ,ISNULL(cast([koikodni] as varchar), '') koikodni
				  ,[PrehospArrive_Name]
				  ,[fio]
				  ,[sex_name]
				  ,ISNULL(cast([age] as varchar), '') age
				  ,[Address_Address]
				  ,EFR.[PersonPhone_Phone]
				  ,[DeputyKind_Name]
				  ,EFR.[EvnPs_DeputyFIO]
				  ,EFR.[EvnPs_DeputyContact]
				  ,[Org_Name]
				  ,[Post_Name]
				  ,[WhoOrgDirected]
				  ,[WhoMedPersonalDirected]
				  ,[extr]
				  ,EFR.[EvnPS_TimeDesease]
				  ,[pl]
				  ,[diagdir_name]
				  ,EFR.[EvnPS_PhaseDescr_pid]
				  ,EFR.[EvnPS_PhaseDescr_did]
				  ,[diagpriem_name]
				  ,ISNULL(convert(varchar(10), EFR.[EvnPS_setDate], 104), '') EvnPS_setDate
				  ,[diag_name]
				  ,ISNULL(convert(varchar(10), [EvnSection_setDate], 104), '') EvnSection_setDate
				  ,[diagOsl_name]
				  ,[DiagSop_name]
				  ,[LeaveType_Name]
				  ,[ResultDesease_Name]
				  ,ISNULL(convert(varchar(10), [Person_BirthDay], 104), '') Person_BirthDay
				  ,[KLAreaType_Name]
				  ,[KLAreaType_SysNick]
				  ,RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Name, '')) as PrivilegeType_Name
				  ,RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_Serie, '')) as PersonPrivilege_Serie
				  ,RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_Number, '')) as PersonPrivilege_Number		
				  ,RTRIM(ISNULL(PersonPrivilege.PrivilegeType_Code, '')) as PrivilegeType_Code						  
				  ,RTRIM(ISNULL(PersonPrivilege.PersonPrivilege_Group, '')) as PersonPrivilege_Group				  
				  ,OSM.Polis_Ser
				  ,OSM.Polis_Num
				  ,OSM.OSM_Name
				  ,Person_edNum
				  ,SocStatus.SocStatus_Name
				  ,EFR.TimeDeseaseType_Name
				  ,EPS.Okei_id
				  ,case when EPS.Okei_id=100 then 'ч.' when EPS.Okei_id=101 then 'сут.' when EPS.Okei_id=102 then 'нед.' when EPS.Okei_id=104 then 'мес.' end as Okei_Name
				  ,document.*
			  FROM [dbo].[v_EmkFormReport] EFR
				join PersonState PS on PS.Person_id = EFR.Person_id
				left join [dbo].[v_EvnPS] EPS on EPS.EvnPS_id = EFR.[EvnPS_id]
				  outer apply (
					select top 1
						PrivilegeType_Name,
						PersonPrivilege_Serie,
						PersonPrivilege_Number,
						PrivilegeType_Code,
						PersonPrivilege_Group
					from
						v_PersonPrivilege WITH (NOLOCK)
					where Person_id = EFR.Person_id
					order by PersonPrivilege_begDate desc
				) PersonPrivilege				
				outer apply (
					select P.Polis_ser, P.Polis_num, O.Org_Nick as OSM_Name
						from polis P
						left join OrgSmo OSM on OSM.OrgSmo_id = P.OrgSmo_id
						left join Org O on O.Org_id = OSM.Org_id
						where P.Polis_id = PS.Polis_id
				) OSM
				outer apply (
					select SocStatus_Name from SocStatus where SocStatus_id = PS.SocStatus_id
				) SocStatus
				OUTER APPLY(
				    SELECT top 1 d.Document_Ser, d.Document_Num, vod.Org_Nick as Document_Org_Nick
				      FROM Document d
				      LEFT JOIN v_OrgDep vod ON vod.OrgDep_id = d.OrgDep_id
				      WHERE d.Document_id = ps.Document_id AND d.DocumentType_id = 13
				  ) document
				
			  where  [EvnPS_id] = :EvnPS_id";
		
		$result = $this->db->query($query,$params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * @param $data
     * @return bool
     */
    function getEvnSectionViewData($data) {
        $params = array('Lpu_id' => $data['Lpu_id']);
        if (isset($data['EvnSection_pid'])) {
            $filterp = 'EvnPS.EvnPS_id = :EvnSection_pid AND EvnPS.LpuSection_pid IS NOT NULL';
            $filter = 'ES.EvnSection_pid = :EvnSection_pid';
            $params['EvnSection_pid'] = $data['EvnSection_pid'];
        } else {
            //$filterp = '1 = 2'; старое значение
            $filterp = '1 = 2';
            $filter = 'ES.EvnSection_id = :EvnSection_id';
            $params['EvnSection_id'] = $data['EvnSection_id'];
        }
        $access_type_pr = 'EvnPS.Lpu_id = :Lpu_id';
        $access_type_es = 'ES.Lpu_id = :Lpu_id'; // AND isnull(ES.EvnSection_IsSigned,1) = 1 AND isnull(leave.IsSigned,1) = 1
        $join_user_msf = '';
        $params['UserMedStaffFact_id'] = (!empty($data['session']['CurMedStaffFact_id']) ) ? $data['session']['CurMedStaffFact_id'] : null;
        if (!empty($data['session']['CurMedStaffFact_id'])) {
            $join_user_msf = 'left join v_MedStaffFact UMSF with (nolock) on UMSF.MedStaffFact_id = :UserMedStaffFact_id';
            //до реализации расписания дежурств врачей, разрешить редактирование всем врачам отделения
            //$access_type_pr .= ' AND EvnPS.LpuSection_pid = UMSF.LpuSection_id';
            // если АРМ приемного отделения, то дать права на редактирование

            if (in_array('stacpriem', $data['session']['ARMList'])) {
                $access_type_es .= ' AND 1 = 1';
            }
            else{
                $access_type_es .= ' AND ES.LpuSection_id = UMSF.LpuSection_id';
            }
        } else {
                //если нет рабочего места врача, то доступ только на чтение
                $access_type_pr .= ' AND 1 = 2';
                $access_type_es .= ' AND 1 = 2';
        }

        $join_lpu_section_bed_profile = 'outer apply(select null as LpuSectionBedProfile_id, null as LpuSectionBedProfile_Name) LSBP';
        if (in_array($data['session']['region']['nick'], array('samara', 'kareliya', 'astra'))) {
            // Для самары получаем профиль койки
            $join_lpu_section_bed_profile = 'outer apply(
				SELECT TOP 1 s.LpuSectionBedProfile_id
				FROM v_LpuSection s
				WHERE s.LpuSection_id = (
					SELECT TOP 1 n.LpuSection_id
					from dbo.v_EvnSectionNarrowBed n
					WHERE n.EvnSectionNarrowBed_pid = ES.EvnSection_id
				)
			) SLSBP
			left join v_LpuSectionBedProfile LSBP with (nolock) ON SLSBP.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id';
        }
        /*
          ,isnull(EvnPS.EvnPS_IsSigned,1) as EvnSection_IsSigned
          ,rtrim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,'')) +' '+ rtrim(isnull(pucins.PMUser_firName,'')) +' '+ rtrim(isnull(pucins.PMUser_secName,'')) as ins_Name
          ,rtrim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,'')) +' '+ rtrim(isnull(pucsign.PMUser_firName,'')) +' '+ rtrim(isnull(pucsign.PMUser_secName,'')) as sign_Name
          ,SUBSTRING(convert(varchar,EvnPS.EvnPS_insDT,104) +' '+ convert(varchar,EvnPS.EvnPS_insDT,108),1,16) as insDT
          ,SUBSTRING(convert(varchar,EvnPS.EvnPS_signDT,104) +' '+ convert(varchar,EvnPS.EvnPS_signDT,108),1,16) as signDT

          left join v_pmUserCache pucins with (nolock) on EvnPS.pmUser_insID = pucins.PMUser_id
          left join v_pmUserCache pucsign with (nolock) on EvnPS.pmUser_signID = pucsign.PMUser_id
         */
        /*case when {$access_type_pr} then 'edit' else 'view' end as accessType,*/
		$this->load->model('CureStandart_model');
		$cureStandartCountQueryEps = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'EvnPS.EvnPS_setDT');
		$cureStandartCountQueryEs = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'ES.EvnSection_setDT');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('Diag');
		
        $query = "
			select
				case when {$access_type_pr} then 'edit' else 'view' end as accessType,
				EvnPS.Lpu_id,
				Diag.Diag_id,
				Diag.Diag_pid,
				EvnPS.EvnPS_id as EvnSection_id,
				EvnPS.EvnPS_id as EvnSection_pid,
				EvnPS.EvnClass_id,
				'EvnDiagPSRecep' as EvnDiagPS_class,
				EvnPS.Person_id,
				EvnPS.PersonEvn_id,
				EvnPS.Server_id,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				ISNULL(MP.Person_Fio,'') as MedPersonal_Fio,
				convert(varchar(10), EvnPS.EvnPS_setDate, 104) as EvnSection_setDate,
				EvnPS.EvnPS_setTime as EvnSection_setTime,
				'' as EvnSection_disDate,
				'' as EvnSection_disTime,
				RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name,
				RTRIM(ISNULL(LSW.LpuSectionWard_Name, '')) as LpuSectionWard_Name,
				null as TariffClass_Name,
				EvnPS.LpuSection_pid as LpuSection_id,
				EvnPS.MedPersonal_pid as MedPersonal_id,
				EvnPS.LpuSectionWard_id as LpuSectionWard_id,
				MSF.MedStaffFact_id as MedStaffFact_id,
				null as Mes_id,
				EvnPS.PayType_id as PayType_id,
				null as TariffClass_id,
				ISNULL(Diag.Diag_Name, '') as Diag_Name,-- основной диагноз
				ISNULL(Diag.Diag_Code, '') as Diag_Code,

				case when ESNEXT.EvnSection_id is not null then -2 when EvnPS.PrehospWaifRefuseCause_id is not null then -1 else -3 end as LeaveType_id,
				case when ESNEXT.EvnSection_id is not null then -2 when EvnPS.PrehospWaifRefuseCause_id is not null then -1 else -3 end as LeaveType_Code,
				'' as LeaveType_Name,
				convert(varchar(10), ESNEXT.EvnSection_setDate, 104) as EvnSection_leaveDate,
				ESNEXT.EvnSection_setTime as EvnSection_leaveTime,
				null as Leave_id,
				null as LeaveCause_id,
				null as ResultDesease_id,
				null as EvnLeave_UKL,
				null as IsSigned,
				null as LeaveCause_Name,
				null as ResultDesease_Name,
				null as EvnLeave_IsAmbul,
				null as Lpu_l_Name,-- перевод в <ЛПУ>
				null as MedPersonal_d_Fin,
				null as EvnDie_IsWait,
				null as EvnDie_IsAnatom,
				null as EvnDie_expDate,
				null as EvnDie_expTime,
				null as EvnDie_locName,
				null as MedPersonal_a_Fin,
				null as Diag_a_Code,
				null as Diag_a_Name,
				null as LpuUnitType_o_Name,
				LSNEXT.LpuSection_Name as LpuSection_o_Name,

				LSP.LpuSectionProfile_id,
				LUT.LpuUnitType_Code,
				LUT.LpuUnitType_SysNick,

				EvnPS.EvnPS_HospCount as EvnPS_HospCount,
				EvnPS.EvnPS_TimeDesease as EvnPS_TimeDesease,
				ISNULL(IsNeglectedCase.YesNo_Name, '') as EvnPS_IsNeglectedCase,
				PTX.PrehospToxic_Name as PrehospToxic_Name,
				PTR.PrehospTrauma_Name as PrehospTrauma_Name,
				ISNULL(IsUnlaw.YesNo_Name, '') as EvnPS_IsUnlaw,
				ISNULL(IsUnport.YesNo_Name, '') as EvnPS_IsUnport,
				PWRC.PrehospWaifRefuseCause_Name as PrehospWaifRefuseCause_Name,
				PWRC.PrehospWaifRefuseCause_id,
				LSNEXT.LpuSection_id as LpuSectionNEXT_id,
				EvnPS.EvnPS_IsTransfCall as EvnPS_IsTransfCall,
				null as Mes_Code,
				null as Mes_Name,
				null as EvnSection_KoikoDni,
				null as Mes_KoikoDni,
				null as Procent_KoikoDni
				,null as EvnSection_IsSigned
				,null as ins_Name
				,null as sign_Name
				,null as insDT
				,null as signDT
				,FM.CureStandart_Count
				,DFM.DiagFedMes_FileName
				,null as LpuSectionBedProfile_id
				,null as LpuSectionBedProfile_Name
			from v_EvnPS EvnPS with (nolock)
				{$join_user_msf}
				left join v_PersonState PS with (nolock) on EvnPS.Person_id = PS.Person_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EvnPS.LpuSection_pid
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_PayType PT with (nolock) on PT.PayType_id = EvnPS.PayType_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EvnPS.MedPersonal_pid and MP.Lpu_id = EvnPS.Lpu_id
				outer apply ( select top 1
					MSF.MedStaffFact_id
					from v_MedStaffFact MSF with (nolock)
					where MSF.MedPersonal_id = EvnPS.MedPersonal_pid and MSF.LpuSection_id = EvnPS.LpuSection_pid
				) MSF
				left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnPS.Diag_pid
				left join PrehospToxic PTX with (nolock) on PTX.PrehospToxic_id = EvnPS.PrehospToxic_id
				left join v_PrehospTrauma PTR with (nolock) on PTR.PrehospTrauma_id = EvnPS.PrehospTrauma_id
				left join v_PrehospWaifRefuseCause PWRC with (nolock) on PWRC.PrehospWaifRefuseCause_id = EvnPS.PrehospWaifRefuseCause_id
				left join YesNo IsUnlaw WITH (NOLOCK) on IsUnlaw.YesNo_id = EvnPS.EvnPS_IsUnlaw
				left join YesNo IsUnport WITH (NOLOCK) on IsUnport.YesNo_id = EvnPS.EvnPS_IsUnport
				left join YesNo IsNeglectedCase WITH (NOLOCK) on IsNeglectedCase.YesNo_id = EvnPS.EvnPS_IsNeglectedCase
				left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = EvnPS.LpuSectionWard_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT with (nolock) on ESNEXT.EvnSection_pid = EvnPS.EvnPS_id AND ESNEXT.EvnSection_Index = 0
				left join LpuSection LSNEXT with (nolock) on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				-- для гиперссылки на МЭС на коде диагноза
				outer apply (
					{$cureStandartCountQueryEps}
				) FM
				outer apply (
					{$diagFedMesFileNameQuery}
				) DFM
			where
				{$filterp}
			union
			select
				case when {$access_type_es} then 'edit' else 'view' end as accessType,
				ES.Lpu_id,
				Diag.Diag_id,
				Diag.Diag_pid,
				ES.EvnSection_id,
				ES.EvnSection_pid,
				ES.EvnClass_id,
				'EvnDiagPSSect' as EvnDiagPS_class,
				ES.Person_id,
				ES.PersonEvn_id,
				ES.Server_id,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				ISNULL(MP.Person_Fio,'') as MedPersonal_Fio,
				convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate,
				ES.EvnSection_setTime as EvnSection_setTime,
				convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate,
				ES.EvnSection_disTime as EvnSection_disTime,
				RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name,
				RTRIM(ISNULL(LSW.LpuSectionWard_Name, '')) as LpuSectionWard_Name,
				ISNULL(TC.TariffClass_Name,'') as TariffClass_Name,
				ES.LpuSection_id as LpuSection_id,
				ES.MedPersonal_id as MedPersonal_id,
				ES.LpuSectionWard_id as LpuSectionWard_id,
				MSF.MedStaffFact_id as MedStaffFact_id,
				ES.Mes_id as Mes_id,
				ES.PayType_id as PayType_id,
				ES.TariffClass_id as TariffClass_id,
				ISNULL(Diag.Diag_Name, '') as Diag_Name,-- основной диагноз
				ISNULL(Diag.Diag_Code, '') as Diag_Code,

				LT.LeaveType_id,
				LT.LeaveType_Code,
				LT.LeaveType_Name,
				convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_leaveDate,
				ES.EvnSection_disTime as EvnSection_leaveTime,
				leave.Leave_id as Leave_id,
				leave.LeaveCause_id,
				leave.ResultDesease_id,
				leave.UKL as EvnLeave_UKL,
				leave.IsSigned as IsSigned,
				leave.LeaveCause_Name as LeaveCause_Name,
				leave.ResultDesease_Name as ResultDesease_Name,
				leave.EvnLeave_IsAmbul as EvnLeave_IsAmbul,
				leave.Lpu_l_Name as Lpu_l_Name,
				leave.MedPersonal_d_Fin as MedPersonal_d_Fin,
				leave.EvnDie_IsWait as EvnDie_IsWait,
				leave.EvnDie_IsAnatom as EvnDie_IsAnatom,
				leave.EvnDie_expDate as EvnDie_expDate,
				leave.EvnDie_expTime as EvnDie_expTime,
				leave.EvnDie_locName as EvnDie_locName,
				leave.MedPersonal_a_Fin as MedPersonal_a_Fin,
				leave.Diag_a_Code as Diag_a_Code,
				leave.Diag_a_Name as Diag_a_Name,
				leave.LpuUnitType_o_Name as LpuUnitType_o_Name,
				coalesce(leave.LpuSection_o_Name,LSNEXT.LpuSection_Name,'') as LpuSection_o_Name,

				LS.LpuSectionProfile_id,
				LUT.LpuUnitType_Code,
				LUT.LpuUnitType_SysNick,

				null as EvnPS_HospCount,
				null as EvnPS_TimeDesease,
				null as EvnPS_IsNeglectedCase,
				null as PrehospToxic_Name,
				null as PrehospTrauma_Name,
				null as EvnPS_IsUnlaw,
				null as EvnPS_IsUnport,
				null as PrehospWaifRefuseCause_Name,
				null as PrehospWaifRefuseCause_id,
				LSNEXT.LpuSection_id as LpuSectionNEXT_id,
				null as EvnPS_IsTransfCall,
				Mes.Mes_Code as Mes_Code,
				Mes.Mes_Name as Mes_Name,
				case
					when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1 > 1
					then DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate()))
					else DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1
				end as EvnSection_KoikoDni,
				Mes.Mes_KoikoDni as Mes_KoikoDni,
				case when Mes.Mes_KoikoDni is not null and Mes.Mes_KoikoDni > 0
					then
						case
							when LUT.LpuUnitType_Code = 2 and DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1 > 1
							then CAST((DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())))*100/Mes.Mes_KoikoDni AS decimal (8,2))
							else CAST((DATEDIFF(DAY, ES.EvnSection_setDate, isnull(ES.EvnSection_disDate,dbo.tzGetDate())) + 1)*100/Mes.Mes_KoikoDni AS decimal (8,2))
						end
					else null
				end as Procent_KoikoDni
				,ES.EvnSection_IsSigned
				,rtrim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,'')) +' '+ rtrim(isnull(pucins.PMUser_firName,'')) +' '+ rtrim(isnull(pucins.PMUser_secName,'')) as ins_Name
				,rtrim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,'')) +' '+ rtrim(isnull(pucsign.PMUser_firName,'')) +' '+ rtrim(isnull(pucsign.PMUser_secName,'')) as sign_Name
				,SUBSTRING(convert(varchar,ES.EvnSection_insDT,104) +' '+ convert(varchar,ES.EvnSection_insDT,108),1,16) as insDT
				,SUBSTRING(convert(varchar,ES.EvnSection_signDT,104) +' '+ convert(varchar,ES.EvnSection_signDT,108),1,16) as signDT
				,FM.CureStandart_Count
				,DFM.DiagFedMes_FileName
				,LSBP.LpuSectionBedProfile_id
				,LSBP.LpuSectionBedProfile_Name
			from v_EvnSection ES with (nolock)
				left join v_PersonState PS with (nolock) on ES.Person_id = PS.Person_id
				left join v_pmUserCache pucins with (nolock) on ES.pmUser_insID = pucins.PMUser_id
				left join v_pmUserCache pucsign with (nolock) on ES.pmUser_signID = pucsign.PMUser_id
				{$join_user_msf}
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				inner join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					-- данное условие не нужно, расхождение может быть только на тестовой, поскольку данные изначально кривые - на рабочей все отлично
					-- or LU.LpuUnit_id = (select top 1 LS1.LpuUnit_id from LpuSection LS1 with (nolock) where LS1.LpuSection_id = LS.LpuSection_pid)
				inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_MesOld Mes with (nolock) on Mes.Mes_id = ES.Mes_id
				left join v_PayType PT with (nolock) on PT.PayType_id = ES.PayType_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = ES.LeaveType_id
				{$join_lpu_section_bed_profile}
				left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = ES.LpuSectionWard_id
				left join v_TariffClass TC with (nolock) on TC.TariffClass_id = ES.TariffClass_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ES.MedPersonal_id and MP.Lpu_id = ES.Lpu_id
				--left join v_MedStaffFact MSF with (nolock) on MSF.MedPersonal_id = ES.MedPersonal_id and MSF.LpuSection_id = ES.LpuSection_id
				outer apply ( -- это неправильно, но пока выхода не вижу, надо думать
					Select top 1 MedStaffFact_id from v_MedStaffFact MSF with (nolock)
					where MSF.MedPersonal_id = ES.MedPersonal_id and MSF.LpuSection_id = ES.LpuSection_id
					and (MedStaffFact_id = :UserMedStaffFact_id or :UserMedStaffFact_id is null)
				) as MSF
				left join v_Diag Diag with (nolock) on Diag.Diag_id = ES.Diag_id
				-- если это последнее движение то берем данные об исходе
				left join EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = ES.EvnSection_pid AND EvnPS.LpuSection_id = ES.LpuSection_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT with (nolock) on ESNEXT.EvnSection_pid = ES.EvnSection_pid AND ESNEXT.EvnSection_Index = (ES.EvnSection_Index + 1)
				left join LpuSection LSNEXT with (nolock) on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				-- для гиперссылки на МЭС на коде диагноза
				outer apply (
					{$cureStandartCountQueryEs}
				) FM
				outer apply (
					{$diagFedMesFileNameQuery}
				) DFM
				--события исхода госпитализации
				outer apply (
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EL.EvnLeave_id as Leave_id,
							cast(EL.EvnLeave_UKL as numeric(10, 2)) as UKL,
							EL.EvnLeave_IsSigned as IsSigned,
							convert(varchar(10), EL.EvnLeave_setDate, 104) as setDate,
							ISNULL(EL.EvnLeave_setTime, '') as setTime,
							ISNULL(YesNo.YesNo_Name, '') as EvnLeave_IsAmbul,
							null as Lpu_l_Name,
							null as MedPersonal_d_Fin,
							null as EvnDie_IsWait,
							null as EvnDie_IsAnatom,
							null as EvnDie_expDate,
							null as EvnDie_expTime,
							null as EvnDie_locName,
							null as MedPersonal_a_Fin,
							null as Diag_a_Code,
							null as Diag_a_Name,
							null as LpuUnitType_o_Name,
							null as LpuSection_o_Name
						from
							v_EvnLeave EL WITH (NOLOCK)
							left join LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EL.LeaveCause_id
							left join ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EL.ResultDesease_id
							left join YesNo WITH (NOLOCK) on YesNo.YesNo_id = EL.EvnLeave_IsAmbul
						where
							EL.EvnLeave_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (1, 101, 107, 108, 110, 201)
					union
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOL.EvnOtherLpu_id as Leave_id,
							EOL.EvnOtherLpu_UKL as UKL,
							EOL.EvnOtherLpu_IsSigned as IsSigned,
							convert(varchar(10), EOL.EvnOtherLpu_setDate, 104) as setDate,
							ISNULL(EOL.EvnOtherLpu_setTime, '') as setTime,
							null as EvnLeave_IsAmbul,
							ISNULL(Org.Org_Name, '') as Lpu_l_Name,
							null as MedPersonal_d_Fin,
							null as EvnDie_IsWait,
							null as EvnDie_IsAnatom,
							null as EvnDie_expDate,
							null as EvnDie_expTime,
							null as EvnDie_locName,
							null as MedPersonal_a_Fin,
							null as Diag_a_Code,
							null as Diag_a_Name,
							null as LpuUnitType_o_Name,
							null as LpuSection_o_Name
						from
							v_EvnOtherLpu EOL WITH (NOLOCK)
							inner join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOL.LeaveCause_id
							inner join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOL.ResultDesease_id
							left join v_Org Org WITH (NOLOCK) on Org.Org_id = EOL.Org_oid
						where
							EOL.EvnOtherLpu_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (2, 102, 202)
					union
						select top 1
							null as LeaveCause_id,
							null as ResultDesease_id,
							null as LeaveCause_Name,
							null as ResultDesease_Name,
							ED.EvnDie_id as Leave_id,
							ED.EvnDie_UKL as UKL,
							ED.EvnDie_IsSigned as IsSigned,
							convert(varchar(10), ED.EvnDie_setDate, 104) as setDate,
							ISNULL(ED.EvnDie_setTime, '') as setTime,
							null as EvnLeave_IsAmbul,
							null as Lpu_l_Name,
							ISNULL(MP.Person_Fin, '') as MedPersonal_d_Fin,
							ISNULL(yesno1.YesNo_Name, '') as EvnDie_IsWait,
							ISNULL(YesNo.YesNo_Name, '') as EvnDie_IsAnatom,
							convert(varchar(10), ED.EvnDie_expDate, 104) as EvnDie_expDate,
							ED.EvnDie_expTime as EvnDie_expTime,
							coalesce(LSA.LpuSection_Name,OA.Org_Name,'') as EvnDie_locName,
							ISNULL(MPA.Person_Fin, '') as MedPersonal_a_Fin,
							ISNULL(Diag.Diag_Code, '') as Diag_a_Code,
							ISNULL(Diag.Diag_Name, '') as Diag_a_Name,
							null as LpuUnitType_o_Name,
							null as LpuSection_o_Name
						from
							v_EvnDie ED WITH (NOLOCK)
							inner join v_MedPersonal MP WITH (NOLOCK) on MP.MedPersonal_id = ED.MedPersonal_id
								and MP.Lpu_id = ED.Lpu_id
							left join v_Diag WITH (NOLOCK) on Diag.Diag_id = ED.Diag_aid
							left join v_YesNo yesno1 WITH (NOLOCK) on yesno1.YesNo_id = ED.EvnDie_IsWait
							left join v_YesNo YesNo WITH (NOLOCK) on YesNo.YesNo_id = ED.EvnDie_IsAnatom
							left join v_LpuSection LSA with (nolock) on LSA.LpuSection_id = ed.LpuSection_aid
							left join v_Org OA with (nolock) on OA.Org_id = ed.Lpu_aid
							left join v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = ed.MedPersonal_aid and MPA.Lpu_id = LSA.Lpu_id
						where
							ED.EvnDie_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (3, 105, 106, 205, 206)
					union
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOS.EvnOtherStac_id as Leave_id,
							EOS.EvnOtherStac_UKL as UKL,
							EOS.EvnOtherStac_IsSigned as IsSigned,
							convert(varchar(10), EOS.EvnOtherStac_setDate, 104) as setDate,
							ISNULL(EOS.EvnOtherStac_setTime, '') as setTime,
							null as EvnLeave_IsAmbul,
							null as Lpu_l_Name,
							null as MedPersonal_d_Fin,
							null as EvnDie_IsWait,
							null as EvnDie_IsAnatom,
							null as EvnDie_expDate,
							null as EvnDie_expTime,
							null as EvnDie_locName,
							null as MedPersonal_a_Fin,
							null as Diag_a_Code,
							null as Diag_a_Name,
							ISNULL(LLUT.LpuUnitType_Name, '') as LpuUnitType_o_Name,
							ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
						from
							v_EvnOtherStac EOS WITH (NOLOCK)
							inner join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOS.LeaveCause_id
							inner join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOS.ResultDesease_id
							inner join v_LpuUnitType LLUT WITH (NOLOCK) on LLUT.LpuUnitType_id = EOS.LpuUnitType_oid
							inner join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOS.LpuSection_oid
						where
							EOS.EvnOtherStac_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (4, 103, 203)
					union
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOS.EvnOtherSection_id as Leave_id,
							EOS.EvnOtherSection_UKL as UKL,
							EOS.EvnOtherSection_IsSigned as IsSigned,
							convert(varchar(10), EOS.EvnOtherSection_setDate, 104) as setDate,
							ISNULL(EOS.EvnOtherSection_setTime, '') as setTime,
							null as EvnLeave_IsAmbul,
							null as Lpu_l_Name,
							null as MedPersonal_d_Fin,
							null as EvnDie_IsWait,
							null as EvnDie_IsAnatom,
							null as EvnDie_expDate,
							null as EvnDie_expTime,
							null as EvnDie_locName,
							null as MedPersonal_a_Fin,
							null as Diag_a_Code,
							null as Diag_a_Name,
							null as LpuUnitType_o_Name,
							ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
						from
							v_EvnOtherSection EOS WITH (NOLOCK)
							inner join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOS.LeaveCause_id
							inner join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOS.ResultDesease_id
							inner join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOS.LpuSection_oid
						where
							EOS.EvnOtherSection_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (5)
					union
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOSBP.EvnOtherSectionBedProfile_id as Leave_id,
							EOSBP.EvnOtherSectionBedProfile_UKL as UKL,
							EOSBP.EvnOtherSectionBedProfile_IsSigned as IsSigned,
							convert(varchar(10), EOSBP.EvnOtherSectionBedProfile_setDate, 104) as setDate,
							ISNULL(EOSBP.EvnOtherSectionBedProfile_setTime, '') as setTime,
							null as EvnLeave_IsAmbul,
							null as Lpu_l_Name,
							null as MedPersonal_d_Fin,
							null as EvnDie_IsWait,
							null as EvnDie_IsAnatom,
							null as EvnDie_expDate,
							null as EvnDie_expTime,
							null as EvnDie_locName,
							null as MedPersonal_a_Fin,
							null as Diag_a_Code,
							null as Diag_a_Name,
							null as LpuUnitType_o_Name,
							ISNULL(LLS.LpuSection_Name, '') as LpuSection_o_Name
						from
							v_EvnOtherSectionBedProfile EOSBP WITH (NOLOCK)
							left join v_LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOSBP.LeaveCause_id
							left join v_ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOSBP.ResultDesease_id
							left join v_LpuSection LLS WITH (NOLOCK) on LLS.LpuSection_id = EOSBP.LpuSection_oid
						where
							EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (104, 204)
				) leave
			where
				{$filter}
			order by
				EvnSection_id
		";


          /*echo getDebugSql($query, $params);
          exit;*/

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            $resp = $result->result('array');
            //$resp = swFilterResponse::filterNotViewDiag($resp, $data);
			$listEvnSectionId = array();
			$listEvnSectionIdIndex = array();
			foreach($resp as $i => &$respone) {
				$respone['displayEvnObservGraphs'] = 'none';
				if (!empty($respone['EvnSection_id'])) {
					$listEvnSectionId[] = $respone['EvnSection_id'];
					$listEvnSectionIdIndex[$respone['EvnSection_id']] = $i;
				}
			}
			if (count($listEvnSectionId) > 0) {
				// проверяем наличие данных для температурного листа
				// т.к. было реализовано так, что если нет параметров АД, пульса, температуры,
				// то все скрывалось, проверяем наличие только этих параметров
				$listEvnSectionId = implode(',', $listEvnSectionId);
				$result = $this->db->query("
					SELECT distinct EP.EvnPrescr_pid
					FROM v_EvnPrescr EP with (nolock)
					WHERE EP.EvnPrescr_pid in ({$listEvnSectionId})
						and EP.PrescriptionType_id=10
						and exists (
							select top 1 EOD.EvnObservData_id
							from v_EvnPrescrObserv EPO with (nolock)
							INNER JOIN v_EvnObserv EO with (nolock) ON EO.EvnObserv_pid = EPO.EvnPrescrObserv_id
							INNER JOIN v_EvnObservData EOD with (nolock) ON EOD.EvnObserv_id = EO.EvnObserv_id
							and EOD.ObservParamType_id in (1,2,3,4)
							where EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
						)
				");
				if (is_object($result)) {
					$resp_leave = $result->result('array');
					foreach($resp_leave as $row) {
						$id = $row['EvnPrescr_pid'];
						if (isset($listEvnSectionIdIndex[$id])) {
							$i = $listEvnSectionIdIndex[$id];
							$resp[$i]['displayEvnObservGraphs'] = 'block';
						}
					}
				}
			}
			$this->load->library('swMorbus');
			$resp = swMorbus::processingEvnData($resp, 'EvnSection');
			return $resp;
        } else {
            return false;
        }
    }

}