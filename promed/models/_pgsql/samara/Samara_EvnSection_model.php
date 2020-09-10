<?php

require_once(APPPATH.'models/_pgsql/EvnSection_model.php');

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
		
		$query = "
			SELECT
				 EFR.EvnPS_id as \"EvnPS_id\"
				,EFR.Person_id as \"Person_id\"
				,EFR.Lpu_id as \"Lpu_id\"
				,Lpu_Name as \"Lpu_Name\"
				,addressLpu as \"addressLpu\"
				,coalesce(EFR.EvnPS_NumCard, '') as \"EvnPS_NumCard\"
				,EFR.EvnPS_setDT as \"EvnPS_setDT\"
				,coalesce(to_char(EFR.EvnPS_disDT, 'Mon dd yyyy hh:miAM'), '') as \"EvnPS_disDT\"
				,LpuSection_Name as \"LpuSection_Name\"
				,LpuSectionWard_Name as \"LpuSectionWard_Name\"
				,EvnPS_EntranceMode as \"EvnPS_EntranceMode\"
				,EFR.EvnPS_DrugActions as \"EFR.EvnPS_DrugActions\"
				,Lsection_name as \"Lsection_name\"
				,coalesce(cast(koikodni as varchar), '')  as \"koikodni\"
				,PrehospArrive_Name as \"PrehospArrive_Name\"
				,fio as \"fio\"
				,sex_name as \"sex_name\"
				,coalesce(cast(age as varchar), '') as \"age\"
				,Address_Address as \"Address_Address\"
				,EFR.PersonPhone_Phone as \"PersonPhone_Phone\"
				,DeputyKind_Name as \"DeputyKind_Name\"
				,EFR.EvnPs_DeputyFIO as \"EvnPs_DeputyFIO\"
				,EFR.EvnPs_DeputyContact as \"EvnPs_DeputyContact\"
				,Org_Name as \"Org_Name\"
				,Post_Name as \"Post_Name\"
				,WhoOrgDirected as \"WhoOrgDirected\"
				,WhoMedPersonalDirected as \"WhoMedPersonalDirected\"
				,extr as \"extr\"
				,EFR.EvnPS_TimeDesease as \"EvnPS_TimeDesease\"
				,pl as \"pl\"
				,diagdir_name as \"diagdir_name\"
				,EFR.EvnPS_PhaseDescr_pid as \"EvnPS_PhaseDescr_pid\"
				,EFR.EvnPS_PhaseDescr_did as \"EvnPS_PhaseDescr_did\"
				,diagpriem_name as \"diagpriem_name\"
				,coalesce(to_char(EFR.EvnPS_setDate, 'dd.mm.yyyy'), '') as \"EvnPS_setDate\"
				,diag_name as \"diag_name\"
				,coalesce(to_char(EvnSection_setDate, 'dd.mm.yyyy'), '') as \"EvnSection_setDate\"
				,diagOsl_name as \"diagOsl_name\"
				,DiagSop_name as \"DiagSop_name\"
				,LeaveType_Name as \"LeaveType_Name\"
				,ResultDesease_Name as \"ResultDesease_Name\"
				,coalesce(to_char(Person_BirthDay, 'dd.mm.yyyy'), '') as \"Person_BirthDay\"
				,KLAreaType_Name as \"KLAreaType_Name\"
				,KLAreaType_SysNick as \"KLAreaType_SysNick\"
				,RTRIM(coalesce(PersonPrivilege.PrivilegeType_Name, '')) as \"PrivilegeType_Name\"
				,RTRIM(coalesce(PersonPrivilege.PersonPrivilege_Serie, '')) as \"PersonPrivilege_Serie\"
				,RTRIM(coalesce(PersonPrivilege.PersonPrivilege_Number, '')) as \"PersonPrivilege_Number\"
				,RTRIM(coalesce(PersonPrivilege.PrivilegeType_Code, '')) as \"PrivilegeType_Code\"
				,RTRIM(coalesce(PersonPrivilege.PersonPrivilege_Group, '')) as \"PersonPrivilege_Group\"
				,OSM.Polis_Ser as \"Polis_Ser\"
				,OSM.Polis_Num as \"Polis_Num\"
				,OSM.OSM_Name as \"OSM_Name\"
				,Person_edNum as \"Person_edNum\"
				,SocStatus.SocStatus_Name as \"SocStatus_Name\"
				,EFR.TimeDeseaseType_Name as \"TimeDeseaseType_Name\"
				,EPS.Okei_id as \"Okei_id\"
				,case when EPS.Okei_id=100
					then 'ч.' when EPS.Okei_id=101
						then 'сут.' when EPS.Okei_id=102
							then 'нед.' when EPS.Okei_id=104
								then 'мес.'
				end as \"Okei_Name\"
				,document.Document_Ser as \"Document_Ser\"
				,document.Document_Num as \"Document_Num\"
				,document.Document_Org_Nick as \"Document_Org_Nick\"
			FROM dbo.v_EmkFormReport EFR
				join PersonState PS on PS.Person_id = EFR.Person_id
				left join dbo.v_EvnPS EPS on EPS.EvnPS_id = EFR.EvnPS_id
				left join lateral(
					select
						PrivilegeType_Name,
						PersonPrivilege_Serie,
						PersonPrivilege_Number,
						PrivilegeType_Code,
						PersonPrivilege_Group
					from
						v_PersonPrivilege
					where Person_id = EFR.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) PersonPrivilege on true
				left join lateral(
					select
						P.Polis_ser, P.Polis_num, O.Org_Nick as OSM_Name
					from polis P
						left join OrgSmo OSM on OSM.OrgSmo_id = P.OrgSmo_id
						left join Org O on O.Org_id = OSM.Org_id
					where P.Polis_id = PS.Polis_id
				) OSM on true
				left join lateral(
					select
						SocStatus_Name
					from SocStatus
					where SocStatus_id = PS.SocStatus_id
				) SocStatus on true
				left join lateral(
					SELECT
						d.Document_Ser,
						d.Document_Num,
						vod.Org_Nick as Document_Org_Nick
					FROM Document d
						LEFT JOIN v_OrgDep vod ON vod.OrgDep_id = d.OrgDep_id
					WHERE d.Document_id = ps.Document_id AND d.DocumentType_id = 13
					limit 1
				) document on true
				
			where  EvnPS_id = :EvnPS_id
			limit 1
			";
		
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
        $access_type_es = 'ES.Lpu_id = :Lpu_id'; // AND coalesce(ES.EvnSection_IsSigned,1) = 1 AND coalesce(leave.IsSigned,1) = 1
        $join_user_msf = '';
        $params['UserMedStaffFact_id'] = (!empty($data['session']['CurMedStaffFact_id']) ) ? $data['session']['CurMedStaffFact_id'] : null;
        if (!empty($data['session']['CurMedStaffFact_id'])) {
            $join_user_msf = 'left join v_MedStaffFact UMSF on UMSF.MedStaffFact_id = :UserMedStaffFact_id';
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

        $join_lpu_section_bed_profile = 'left join lateral(select null as LpuSectionBedProfile_id, null as LpuSectionBedProfile_Name) LSBP on true';
        if (in_array($data['session']['region']['nick'], array('samara', 'kareliya', 'astra'))) {
            // Для самары получаем профиль койки
            $join_lpu_section_bed_profile = 'left join lateral(
				SELECT
					s.LpuSectionBedProfile_id
				FROM v_LpuSection s
				WHERE s.LpuSection_id = (
					SELECT
						n.LpuSection_id
					from dbo.v_EvnSectionNarrowBed n
					WHERE n.EvnSectionNarrowBed_pid = ES.EvnSection_id
					limit 1
				)
				limit 1
			) SLSBP on true
			left join v_LpuSectionBedProfile LSBP ON SLSBP.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id';
        }
        $this->load->model('CureStandart_model');
		$cureStandartCountQueryEps = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'EvnPS.EvnPS_setDT');
		$cureStandartCountQueryEs = $this->CureStandart_model->getCountQuery('Diag', 'PS.Person_BirthDay', 'ES.EvnSection_setDT');
		$diagFedMesFileNameQuery = $this->CureStandart_model->getDiagFedMesFileNameQuery('Diag');
		
        $query = "
			select
				case when {$access_type_pr} then 'edit' else 'view' end as \"accessType\",
				EvnPS.Lpu_id as \"Lpu_id\",
				Diag.Diag_id as \"Diag_id\",
				Diag.Diag_pid as \"Diag_pid\",
				EvnPS.EvnPS_id as \"EvnSection_id\",
				EvnPS.EvnPS_id as \"EvnSection_pid\",
				EvnPS.EvnClass_id as \"EvnClass_id\",
				'EvnDiagPSRecep' as \"EvnDiagPS_class\",
				EvnPS.Person_id as \"Person_id\",
				EvnPS.PersonEvn_id as \"PersonEvn_id\",
				EvnPS.Server_id as \"Server_id\",
				RTRIM(coalesce(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
				coalesce(MP.Person_Fio,'') as \"MedPersonal_Fio\",
				to_char(EvnPS.EvnPS_setDate, 'dd.mm.yyyy') as \"EvnSection_setDate\",
				EvnPS.EvnPS_setTime as \"EvnSection_setTime\",
				'' as \"EvnSection_disDate\",
				'' as \"EvnSection_disTime\",
				RTRIM(coalesce(PT.PayType_Name, '')) as \"PayType_Name\",
				RTRIM(coalesce(LSW.LpuSectionWard_Name, '')) as \"LpuSectionWard_Name\",
				null as \"TariffClass_Name\",
				EvnPS.LpuSection_pid as \"LpuSection_id\",
				EvnPS.MedPersonal_pid as \"MedPersonal_id\",
				EvnPS.LpuSectionWard_id as \"LpuSectionWard_id\",
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				null as \"Mes_id\",
				EvnPS.PayType_id as \"PayType_id\",
				null as \"TariffClass_id\",
				coalesce(Diag.Diag_Name, '') as \"Diag_Name\",-- основной диагноз
				coalesce(Diag.Diag_Code, '') as \"Diag_Code\",

				case when ESNEXT.EvnSection_id is not null then -2 when EvnPS.PrehospWaifRefuseCause_id is not null then -1 else -3 end as \"LeaveType_id\",
				case when ESNEXT.EvnSection_id is not null then -2 when EvnPS.PrehospWaifRefuseCause_id is not null then -1 else -3 end as \"LeaveType_Code\",
				'' as \"LeaveType_Name\",
				to_char(ESNEXT.EvnSection_setDate, 'dd.mm.yyyy') as \"EvnSection_leaveDate\",
				ESNEXT.EvnSection_setTime as \"EvnSection_leaveTime\",
				null as \"Leave_id\",
				null as \"LeaveCause_id\",
				null as \"ResultDesease_id\",
				null as \"EvnLeave_UKL\",
				null as \"IsSigned\",
				null as \"LeaveCause_Name\",
				null as \"ResultDesease_Name\",
				null as \"EvnLeave_IsAmbul\",
				null as \"Lpu_l_Name\",-- перевод в <ЛПУ>
				null as \"MedPersonal_d_Fin\",
				null as \"EvnDie_IsWait\",
				null as \"EvnDie_IsAnatom\",
				null as \"EvnDie_expDate\",
				null as \"EvnDie_expTime\",
				null as \"EvnDie_locName\",
				null as \"MedPersonal_a_Fin\",
				null as \"Diag_a_Code\",
				null as \"Diag_a_Name\",
				null as \"LpuUnitType_o_Name\",
				LSNEXT.LpuSection_Name as \"LpuSection_o_Name\",
				LSP.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LUT.LpuUnitType_Code as \"LpuUnitType_Code\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				EvnPS.EvnPS_HospCount as \"EvnPS_HospCount\",
				EvnPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\",
				coalesce(IsNeglectedCase.YesNo_Name, '') as \"EvnPS_IsNeglectedCase\",
				PTX.PrehospToxic_Name as \"PrehospToxic_Name\",
				PTR.PrehospTrauma_Name as \"PrehospTrauma_Name\",
				coalesce(IsUnlaw.YesNo_Name, '') as \"EvnPS_IsUnlaw\",
				coalesce(IsUnport.YesNo_Name, '') as \"EvnPS_IsUnport\",
				PWRC.PrehospWaifRefuseCause_Name as \"PrehospWaifRefuseCause_Name\",
				PWRC.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\",
				LSNEXT.LpuSection_id as \"LpuSectionNEXT_id\",
				EvnPS.EvnPS_IsTransfCall as \"EvnPS_IsTransfCall\",
				null as \"Mes_Code\",
				null as \"Mes_Name\",
				null as \"EvnSection_KoikoDni\",
				null as \"Mes_KoikoDni\",
				null as \"Procent_KoikoDn\"
				,null as \"EvnSection_IsSigned\"
				,null as \"ins_Name\"
				,null as \"sign_Name\"
				,null as \"insDT\"
				,null as \"signDT\"
				,FM.CureStandart_Count as \"CureStandart_Count\"
				,DFM.DiagFedMes_FileName as \"DiagFedMes_FileName\"
				,null as \"LpuSectionBedProfile_id\"
				,null as \"LpuSectionBedProfile_Name\"
			from v_EvnPS EvnPS
				{$join_user_msf}
				left join v_PersonState PS on EvnPS.Person_id = PS.Person_id
				left join v_LpuSection LS on LS.LpuSection_id = EvnPS.LpuSection_pid
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_PayType PT on PT.PayType_id = EvnPS.PayType_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EvnPS.MedPersonal_pid and MP.Lpu_id = EvnPS.Lpu_id
				left join lateral(
					select
						MSF.MedStaffFact_id
					from v_MedStaffFact MSF
					where MSF.MedPersonal_id = EvnPS.MedPersonal_pid
						and MSF.LpuSection_id = EvnPS.LpuSection_pid
					limit 1
				) MSF on true
				left join v_Diag Diag on Diag.Diag_id = EvnPS.Diag_pid
				left join PrehospToxic PTX on PTX.PrehospToxic_id = EvnPS.PrehospToxic_id
				left join v_PrehospTrauma PTR on PTR.PrehospTrauma_id = EvnPS.PrehospTrauma_id
				left join v_PrehospWaifRefuseCause PWRC on PWRC.PrehospWaifRefuseCause_id = EvnPS.PrehospWaifRefuseCause_id
				left join YesNo IsUnlaw on IsUnlaw.YesNo_id = EvnPS.EvnPS_IsUnlaw
				left join YesNo IsUnport on IsUnport.YesNo_id = EvnPS.EvnPS_IsUnport
				left join YesNo IsNeglectedCase on IsNeglectedCase.YesNo_id = EvnPS.EvnPS_IsNeglectedCase
				left join LpuSectionWard LSW on LSW.LpuSectionWard_id = EvnPS.LpuSectionWard_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT on ESNEXT.EvnSection_pid = EvnPS.EvnPS_id AND ESNEXT.EvnSection_Index = 0
				left join LpuSection LSNEXT on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				-- для гиперссылки на МЭС на коде диагноза
				left join lateral(
					{$cureStandartCountQueryEps}
				) FM on true
				left join lateral(
					{$diagFedMesFileNameQuery}
				) DFM on true
			where
				{$filterp}
			union
			select
				case when {$access_type_es} then 'edit' else 'view' end as \"accessType\",
				ES.Lpu_id as \"Lpu_id\",
				Diag.Diag_id as \"Diag_id\",
				Diag.Diag_pid as \"Diag_pid\",
				ES.EvnSection_id as \"EvnSection_id\",
				ES.EvnSection_pid as \"EvnSection_pid\",
				ES.EvnClass_id as \"EvnClass_id\",
				'EvnDiagPSSect' as \"EvnDiagPS_class\",
				ES.Person_id as \"Person_id\",
				ES.PersonEvn_id as \"PersonEvn_id\",
				ES.Server_id as \"Server_id\",
				RTRIM(coalesce(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
				coalesce(MP.Person_Fio,'') as \"MedPersonal_Fio\",
				to_char(ES.EvnSection_setDate, 'dd.mm.yyyy') as \"EvnSection_setDate\",
				ES.EvnSection_setTime as \"EvnSection_setTime\",
				to_char(ES.EvnSection_disDate, 'dd.mm.yyyy') as \"EvnSection_disDate\",
				ES.EvnSection_disTime as \"EvnSection_disTime\",
				RTRIM(coalesce(PT.PayType_Name, '')) as \"PayType_Name\",
				RTRIM(coalesce(LSW.LpuSectionWard_Name, '')) as \"LpuSectionWard_Name\",
				coalesce(TC.TariffClass_Name,'') as \"TariffClass_Name\",
				ES.LpuSection_id as \"LpuSection_id\",
				ES.MedPersonal_id as \"MedPersonal_id\",
				ES.LpuSectionWard_id as \"LpuSectionWard_id\",
				MSF.MedStaffFact_id as \"MedStaffFact_id\",
				ES.Mes_id as \"Mes_id\",
				ES.PayType_id as \"PayType_id\",
				ES.TariffClass_id as \"TariffClass_id\",
				coalesce(Diag.Diag_Name, '') as \"Diag_Name,-- основной диагно\"з
				coalesce(Diag.Diag_Code, '') as \"Diag_Code\",
				LT.LeaveType_id as \"LeaveType_id\",
				LT.LeaveType_Code as \"LeaveType_Code\",
				LT.LeaveType_Name as \"LeaveType_Name\",
				to_char(ES.EvnSection_disDate, 'dd.mm.yyyy') as \"EvnSection_leaveDate\",
				ES.EvnSection_disTime as \"EvnSection_leaveTime\",
				leave.Leave_id as \"Leave_id\",
				leave.LeaveCause_id as \"LeaveCause_id\",
				leave.ResultDesease_id as \"ResultDesease_id\",
				leave.UKL as \"EvnLeave_UKL\",
				leave.IsSigned as \"IsSigned\",
				leave.LeaveCause_Name as \"LeaveCause_Name\",
				leave.ResultDesease_Name as \"ResultDesease_Name\",
				leave.EvnLeave_IsAmbul as \"EvnLeave_IsAmbul\",
				leave.Lpu_l_Name as \"Lpu_l_Name\",
				leave.MedPersonal_d_Fin as \"MedPersonal_d_Fin\",
				leave.EvnDie_IsWait as \"EvnDie_IsWait\",
				leave.EvnDie_IsAnatom as \"EvnDie_IsAnatom\",
				leave.EvnDie_expDate as \"EvnDie_expDate\",
				leave.EvnDie_expTime as \"EvnDie_expTime\",
				leave.EvnDie_locName as \"EvnDie_locName\",
				leave.MedPersonal_a_Fin as \"MedPersonal_a_Fin\",
				leave.Diag_a_Code as \"Diag_a_Code\",
				leave.Diag_a_Name as \"Diag_a_Name\",
				leave.LpuUnitType_o_Name as \"LpuUnitType_o_Name\",
				coalesce(leave.LpuSection_o_Name,LSNEXT.LpuSection_Name,'') as \"LpuSection_o_Name\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LUT.LpuUnitType_Code as \"LpuUnitType_Code\",
				LUT.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				null as \"EvnPS_HospCount\",
				null as \"EvnPS_TimeDesease\",
				null as \"EvnPS_IsNeglectedCase\",
				null as \"PrehospToxic_Name\",
				null as \"PrehospTrauma_Name\",
				null as \"EvnPS_IsUnlaw\",
				null as \"EvnPS_IsUnport\",
				null as \"PrehospWaifRefuseCause_Name\",
				null as \"PrehospWaifRefuseCause_id\",
				LSNEXT.LpuSection_id as \"LpuSectionNEXT_id\",
				null as \"EvnPS_IsTransfCall\",
				Mes.Mes_Code as \"Mes_Code\",
				Mes.Mes_Name as \"Mes_Name\",
				case
					when LUT.LpuUnitType_Code = 2 and DATEDIFF('DAY', ES.EvnSection_setDate, coalesce(ES.EvnSection_disDate,dbo.tzGetDate())) + 1 > 1
					then DATEDIFF('DAY', ES.EvnSection_setDate, coalesce(ES.EvnSection_disDate,dbo.tzGetDate()))
					else DATEDIFF('DAY', ES.EvnSection_setDate, coalesce(ES.EvnSection_disDate,dbo.tzGetDate())) + 1
				end as \"EvnSection_KoikoDni\",
				Mes.Mes_KoikoDni as \"Mes_KoikoDni\",
				case when Mes.Mes_KoikoDni is not null and Mes.Mes_KoikoDni > 0
					then
						case
							when LUT.LpuUnitType_Code = 2 and DATEDIFF('DAY', ES.EvnSection_setDate, coalesce(ES.EvnSection_disDate,dbo.tzGetDate())) + 1 > 1
							then CAST((DATEDIFF('DAY', ES.EvnSection_setDate, coalesce(ES.EvnSection_disDate,dbo.tzGetDate())))*100/Mes.Mes_KoikoDni as decimal (8,2))
							else CAST((DATEDIFF('DAY', ES.EvnSection_setDate, coalesce(ES.EvnSection_disDate,dbo.tzGetDate())) + 1)*100/Mes.Mes_KoikoDni as decimal (8,2))
						end
					else null
				end as \"Procent_KoikoDni\"
				,ES.EvnSection_IsSigned as \"EvnSection_IsSigned\"
				,rtrim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,''))
					||' '|| rtrim(coalesce(pucins.PMUser_firName,''))
					||' '|| rtrim(coalesce(pucins.PMUser_secName,'')
				) as \"ins_Name\"
				,rtrim(coalesce(pucsign.PMUser_surName,pucsign.PMUser_Name,''))
					||' '|| rtrim(coalesce(pucsign.PMUser_firName,''))
					||' '|| rtrim(coalesce(pucsign.PMUser_secName,'')
				) as \"sign_Name\"
				,to_char(ES.EvnSection_insDT, 'dd.mm.yyyy hh24:mi') as \"insDT\"
				,to_char(ES.EvnSection_signDT, 'dd.mm.yyyy hh24:mi') as \"signDT\"
				,FM.CureStandart_Count as \"CureStandart_Count\"
				,DFM.DiagFedMes_FileName as \"DiagFedMes_FileName\"
				,LSBP.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\"
				,LSBP.LpuSectionBedProfile_Name as \"LpuSectionBedProfile_Name\"
			from v_EvnSection ES
				left join v_PersonState PS on ES.Person_id = PS.Person_id
				left join v_pmUserCache pucins on ES.pmUser_insID = pucins.PMUser_id
				left join v_pmUserCache pucsign on ES.pmUser_signID = pucsign.PMUser_id
				{$join_user_msf}
				inner join LpuSection LS on LS.LpuSection_id = ES.LpuSection_id
				inner join LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
					-- данное условие не нужно, расхождение может быть только на тестовой, поскольку данные изначально кривые - на рабочей все отлично
					-- or LU.LpuUnit_id = (select LS1.LpuUnit_id from LpuSection LS1 where LS1.LpuSection_id = LS.LpuSection_pid limit 1)
				inner join LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_MesOld Mes on Mes.Mes_id = ES.Mes_id
				left join v_PayType PT on PT.PayType_id = ES.PayType_id
				left join v_LeaveType LT on LT.LeaveType_id = ES.LeaveType_id
				{$join_lpu_section_bed_profile}
				left join LpuSectionWard LSW on LSW.LpuSectionWard_id = ES.LpuSectionWard_id
				left join v_TariffClass TC on TC.TariffClass_id = ES.TariffClass_id
				left join v_MedPersonal MP on MP.MedPersonal_id = ES.MedPersonal_id and MP.Lpu_id = ES.Lpu_id
				--left join v_MedStaffFact MSF on MSF.MedPersonal_id = ES.MedPersonal_id and MSF.LpuSection_id = ES.LpuSection_id
				left join lateral( -- это неправильно, но пока выхода не вижу, надо думать
					Select
						MedStaffFact_id
					from v_MedStaffFact MSF
					where MSF.MedPersonal_id = ES.MedPersonal_id
						and MSF.LpuSection_id = ES.LpuSection_id
						and (MedStaffFact_id = :UserMedStaffFact_id or :UserMedStaffFact_id is null)
					limit 1
				) as MSF on true
				left join v_Diag Diag on Diag.Diag_id = ES.Diag_id
				-- если это последнее движение то берем данные об исходе
				left join EvnPS EvnPS on EvnPS.EvnPS_id = ES.EvnSection_pid AND EvnPS.LpuSection_id = ES.LpuSection_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT on ESNEXT.EvnSection_pid = ES.EvnSection_pid AND ESNEXT.EvnSection_Index = (ES.EvnSection_Index + 1)
				left join LpuSection LSNEXT on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				-- для гиперссылки на МЭС на коде диагноза
				left join lateral(
					{$cureStandartCountQueryEs}
				) FM on true
				left join lateral(
					{$diagFedMesFileNameQuery}
				) DFM on true
				--события исхода госпитализации
				left join lateral(
						(select
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EL.EvnLeave_id as Leave_id,
							cast(EL.EvnLeave_UKL as numeric(10, 2)) as UKL,
							EL.EvnLeave_IsSigned as IsSigned,
							to_char(EL.EvnLeave_setDate, 'dd.mm.yyyy') as setDate,
							coalesce(EL.EvnLeave_setTime, '') as setTime,
							coalesce(YesNo.YesNo_Name, '') as EvnLeave_IsAmbul,
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
							v_EvnLeave EL
							left join LeaveCause LC on LC.LeaveCause_id = EL.LeaveCause_id
							left join ResultDesease RD on RD.ResultDesease_id = EL.ResultDesease_id
							left join YesNo on YesNo.YesNo_id = EL.EvnLeave_IsAmbul
						where
							EL.EvnLeave_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (1, 101, 107, 108, 110, 201)
						limit 1)
					union
						(select
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOL.EvnOtherLpu_id as Leave_id,
							EOL.EvnOtherLpu_UKL as UKL,
							EOL.EvnOtherLpu_IsSigned as IsSigned,
							to_char(EOL.EvnOtherLpu_setDate, 'dd.mm.yyyy') as setDate,
							coalesce(EOL.EvnOtherLpu_setTime, '') as setTime,
							null as EvnLeave_IsAmbul,
							coalesce(Org.Org_Name, '') as Lpu_l_Name,
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
							v_EvnOtherLpu EOL
							inner join v_LeaveCause LC on LC.LeaveCause_id = EOL.LeaveCause_id
							inner join v_ResultDesease RD on RD.ResultDesease_id = EOL.ResultDesease_id
							left join v_Org Org on Org.Org_id = EOL.Org_oid
						where
							EOL.EvnOtherLpu_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (2, 102, 202)
						limit 1)
					union
						(select
							null as LeaveCause_id,
							null as ResultDesease_id,
							null as LeaveCause_Name,
							null as ResultDesease_Name,
							ED.EvnDie_id as Leave_id,
							ED.EvnDie_UKL as UKL,
							ED.EvnDie_IsSigned as IsSigned,
							to_char(ED.EvnDie_setDate, 'dd.mm.yyyy') as setDate,
							coalesce(ED.EvnDie_setTime, '') as setTime,
							null as EvnLeave_IsAmbul,
							null as Lpu_l_Name,
							coalesce(MP.Person_Fin, '') as MedPersonal_d_Fin,
							coalesce(yesno1.YesNo_Name, '') as EvnDie_IsWait,
							coalesce(YesNo.YesNo_Name, '') as EvnDie_IsAnatom,
							to_char(ED.EvnDie_expDate, 'dd.mm.yyyy') as EvnDie_expDate,
							ED.EvnDie_expTime as EvnDie_expTime,
							coalesce(LSA.LpuSection_Name,OA.Org_Name,'') as EvnDie_locName,
							coalesce(MPA.Person_Fin, '') as MedPersonal_a_Fin,
							coalesce(Diag.Diag_Code, '') as Diag_a_Code,
							coalesce(Diag.Diag_Name, '') as Diag_a_Name,
							null as LpuUnitType_o_Name,
							null as LpuSection_o_Name
						from
							v_EvnDie ED
							inner join v_MedPersonal MP on MP.MedPersonal_id = ED.MedPersonal_id
								and MP.Lpu_id = ED.Lpu_id
							left join v_Diag on Diag.Diag_id = ED.Diag_aid
							left join v_YesNo yesno1 on yesno1.YesNo_id = ED.EvnDie_IsWait
							left join v_YesNo YesNo on YesNo.YesNo_id = ED.EvnDie_IsAnatom
							left join v_LpuSection LSA on LSA.LpuSection_id = ed.LpuSection_aid
							left join v_Org OA on OA.Org_id = ed.Lpu_aid
							left join v_MedPersonal MPA on MPA.MedPersonal_id = ed.MedPersonal_aid and MPA.Lpu_id = LSA.Lpu_id
						where
							ED.EvnDie_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (3, 105, 106, 205, 206)
						limit 1)
					union
						(select
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOS.EvnOtherStac_id as Leave_id,
							EOS.EvnOtherStac_UKL as UKL,
							EOS.EvnOtherStac_IsSigned as IsSigned,
							to_char(EOS.EvnOtherStac_setDate, 'dd.mm.yyyy') as setDate,
							coalesce(EOS.EvnOtherStac_setTime, '') as setTime,
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
							coalesce(LLUT.LpuUnitType_Name, '') as LpuUnitType_o_Name,
							coalesce(LLS.LpuSection_Name, '') as LpuSection_o_Name
						from
							v_EvnOtherStac EOS
							inner join v_LeaveCause LC on LC.LeaveCause_id = EOS.LeaveCause_id
							inner join v_ResultDesease RD on RD.ResultDesease_id = EOS.ResultDesease_id
							inner join v_LpuUnitType LLUT on LLUT.LpuUnitType_id = EOS.LpuUnitType_oid
							inner join v_LpuSection LLS on LLS.LpuSection_id = EOS.LpuSection_oid
						where
							EOS.EvnOtherStac_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (4, 103, 203)
						limit 1)
					union
						(select
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOS.EvnOtherSection_id as Leave_id,
							EOS.EvnOtherSection_UKL as UKL,
							EOS.EvnOtherSection_IsSigned as IsSigned,
							to_char(EOS.EvnOtherSection_setDate, 'dd.mm.yyyy') as setDate,
							coalesce(EOS.EvnOtherSection_setTime, '') as setTime,
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
							coalesce(LLS.LpuSection_Name, '') as LpuSection_o_Name
						from
							v_EvnOtherSection EOS
							inner join v_LeaveCause LC on LC.LeaveCause_id = EOS.LeaveCause_id
							inner join v_ResultDesease RD on RD.ResultDesease_id = EOS.ResultDesease_id
							inner join v_LpuSection LLS on LLS.LpuSection_id = EOS.LpuSection_oid
						where
							EOS.EvnOtherSection_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (5)
						limit 1)
					union
						(select
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOSBP.EvnOtherSectionBedProfile_id as Leave_id,
							EOSBP.EvnOtherSectionBedProfile_UKL as UKL,
							EOSBP.EvnOtherSectionBedProfile_IsSigned as IsSigned,
							to_char(EOSBP.EvnOtherSectionBedProfile_setDate, 'dd.mm.yyyy') as setDate,
							coalesce(EOSBP.EvnOtherSectionBedProfile_setTime, '') as setTime,
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
							coalesce(LLS.LpuSection_Name, '') as LpuSection_o_Name
						from
							v_EvnOtherSectionBedProfile EOSBP
							left join v_LeaveCause LC on LC.LeaveCause_id = EOSBP.LeaveCause_id
							left join v_ResultDesease RD on RD.ResultDesease_id = EOSBP.ResultDesease_id
							left join v_LpuSection LLS on LLS.LpuSection_id = EOSBP.LpuSection_oid
						where
							EOSBP.EvnOtherSectionBedProfile_pid = ES.EvnSection_id
							and LT.LeaveType_Code in (104, 204)
						limit 1)
				) leave on true
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
					SELECT distinct
						EP.EvnPrescr_pid as \"EvnPrescr_pid\"
					FROM v_EvnPrescr EP
					WHERE EP.EvnPrescr_pid in ({$listEvnSectionId})
						and EP.PrescriptionType_id=10
						and exists (
							select
								EOD.EvnObservData_id
							from v_EvnPrescrObserv EPO
								INNER JOIN v_EvnObserv EO ON EO.EvnObserv_pid = EPO.EvnPrescrObserv_id
								INNER JOIN v_EvnObservData EOD ON EOD.EvnObserv_id = EO.EvnObserv_id
									and EOD.ObservParamType_id in (1,2,3,4)
							where EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							limit 1
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