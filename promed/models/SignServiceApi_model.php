<?php
/**
 * @property CureStandart_model $CureStandart_model
 */
class SignServiceApi_model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Функция достающая все данные о посещении
	 */
	function getEvnVizitPLData($data)
	{
		$queryParams['EvnVizitPL_id'] = $data['EvnVizitPL_id'];

		$query = "
			Select
				EvnVizit.Lpu_id,
				EvnVizit.EvnVizitPL_id,
				EvnVizit.EvnVizitPL_pid,
				-- EvnVizit.EvnVizitPL_Count, EvnVizit.EvnVizitPL_Index,
				EvnVizit.EvnClass_Name as EvnClass_Name,
				IsNull(convert(varchar,EvnVizitPL_setDT,104),'') as EvnVizitPL_setDate, 
				EvnVizitPL_setTime,
				Lpu.Lpu_Name as Lpu_Name,
				Lpu.UAddress_Address as Lpu_Address,
				LpuSection.LpuSection_id,
				LpuSection.LpuSection_Code as LpuSection_Code,
				LpuSection.LpuSection_Name as LpuSection_Name,
				LpuUnit.LpuUnitSet_id,
				MedPersonal.MedPersonal_id,
				MedPersonal.MedPersonal_TabCode,
				MedPersonal.Person_SurName +' '+ ISNULL(SUBSTRING(MedPersonal.Person_FirName,1,1) +'.', '')+ ISNULL(SUBSTRING(MedPersonal.Person_SecName,1,1) +'.', '') as MedPersonal_Fin,
				EvnVizit.VizitClass_id,
				VizitClass.VizitClass_Name,
				ServiceType.ServiceType_id,
				IsNull(ServiceType.ServiceType_Code,'') as ServiceType_Code,
				IsNull(ServiceType.ServiceType_Name,'') as ServiceType_Name,
				VizitType.VizitType_id,
				EvnVizit.RiskLevel_id,
				EvnVizit.WellnessCenterAgeGroups_id,
				RL.RiskLevel_Name,
				WCAG.WellnessCenterAgeGroups_Name,
				RTrim(IsNull(VizitType.VizitType_Name,'')) as VizitType_Name,
				VizitType.VizitType_SysNick,
				PG.ProfGoal_Name,
				PayType.PayType_id,
				PayType.PayType_SysNick,
				case when ISNULL(EvnVizit.EvnVizitPL_IsInReg, 1) = 1 then IsNull(PayType.PayType_Name,'') else '<b>' + IsNull(PayType.PayType_Name,'')  + '</b>' end as PayType_Name,
				PS.Person_SurName + ' ' + PS.Person_FirName + ' ' + isnull(PS.Person_SecName, '') as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				Sex.Sex_Name as Person_Sex_Name,
				EvnVizit.Diag_id as Diag_id,
				Diag.Diag_pid,
				IsNull(Diag.Diag_Code,'') as Diag_Code,
				IsNull(Diag.Diag_Name,'') as Diag_Name,
				DT.DeseaseType_id,
				DT.DeseaseType_Name,
				'' as Diag_Text,
				'' as PrehospDirect_Name,
				0 as Cabinet_Num
				,(select count(Evn_id) from v_Evn with (nolock) where Evn_pid = EvnVizit.EvnVizitPL_id) as Children_Count
				,rtrim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,'')) +' '+ rtrim(isnull(pucins.PMUser_firName,'')) +' '+ rtrim(isnull(pucins.PMUser_secName,'')) as ins_Name
				,SUBSTRING(convert(varchar,EvnVizit.EvnVizitPL_insDT,104) +' '+ convert(varchar,EvnVizit.EvnVizitPL_insDT,108),1,16) as insDT
			from v_EvnVizitPL EvnVizit with (nolock)
				left join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSection_id = EvnVizit.LpuSection_id
				left join v_LpuUnit LpuUnit with (nolock) on LpuSection.LpuUnit_id = LpuUnit.LpuUnit_id
				left join v_Lpu Lpu with (nolock) on EvnVizit.Lpu_id = Lpu.Lpu_id
				left join v_PersonState PS with (nolock) on EvnVizit.Person_id = PS.Person_id
				left join v_Sex Sex with (nolock) on PS.Sex_id = Sex.Sex_id
				left join RiskLevel RL with (nolock) on EvnVizit.RiskLevel_id = RL.RiskLevel_id
				left join WellnessCenterAgeGroups WCAG with (nolock) on EvnVizit.WellnessCenterAgeGroups_id = WCAG.WellnessCenterAgeGroups_id
				--left join v_LpuUnit LpuUnit with (nolock) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id
				left join v_MedPersonal MedPersonal with (nolock) on MedPersonal.MedPersonal_id = EvnVizit.MedPersonal_id and MedPersonal.Lpu_id = EvnVizit.Lpu_id
				left join v_ServiceType ServiceType with (nolock) on ServiceType.ServiceType_id = EvnVizit.ServiceType_id
				left join v_VizitType VizitType with (nolock) on VizitType.VizitType_id = EvnVizit.VizitType_id
				left join v_ProfGoal PG with (nolock) on VizitType.VizitType_SysNick = 'prof' and PG.ProfGoal_id = EvnVizit.ProfGoal_id
				left join v_VizitClass VizitClass with (nolock) on VizitClass.VizitClass_id = EvnVizit.VizitClass_id
				left join v_PayType PayType with (nolock) on PayType.PayType_id = EvnVizit.PayType_id
				left join v_Diag Diag with (nolock) on Diag.Diag_id = EvnVizit.Diag_id
				left join v_DeseaseType DT with (nolock) on EvnVizit.DeseaseType_id = DT.DeseaseType_id
				left join v_pmUserCache pucins with (nolock) on EvnVizit.pmUser_insID = pucins.PMUser_id
			where 
				EvnVizit.EvnVizitPL_id = :EvnVizitPL_id and
				EvnVizit.EvnClass_id != 13
			order by EvnVizit.EvnVizitPL_setDT DESC
		";

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			$this->load->library('swMorbus');
			$resp = swMorbus::processingEvnData($resp, 'EvnVizitPL');
			return $resp;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Функция достающая все данные о движении
	 */
	function getEvnSectionData($data)
	{
		$filter = 'ES.EvnSection_id = :EvnSection_id';
		$params['EvnSection_id'] = $data['EvnSection_id'];

		$query = "
			select
				ES.Lpu_id,
				Diag.Diag_id,
				Diag.Diag_pid,
				ES.EvnSection_id,
				ES.EvnSection_pid,
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

				ES.LeaveType_id,
				convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_leaveDate,
				ES.EvnSection_disTime as EvnSection_leaveTime,
				leave.Leave_id as Leave_id,
				leave.LeaveCause_id,
				leave.ResultDesease_id,
				leave.UKL as EvnLeave_UKL,
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
				Mes.Mes_KoikoDni as Mes_KoikoDni
				,rtrim(coalesce(pucins.PMUser_surName,pucins.PMUser_Name,'')) +' '+ rtrim(isnull(pucins.PMUser_firName,'')) +' '+ rtrim(isnull(pucins.PMUser_secName,'')) as ins_Name
				,SUBSTRING(convert(varchar,ES.EvnSection_insDT,104) +' '+ convert(varchar,ES.EvnSection_insDT,108),1,16) as insDT
				,LSBP.LpuSectionBedProfile_id
				,LSBP.LpuSectionBedProfile_Name
			from v_EvnSection ES with (nolock)
				left join v_PersonState PS with (nolock) on ES.Person_id = PS.Person_id
				left join v_pmUserCache pucins with (nolock) on ES.pmUser_insID = pucins.PMUser_id
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = ES.LpuSection_id
				inner join LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					-- данное условие не нужно, расхождение может быть только на тестовой, поскольку данные изначально кривые - на рабочей все отлично 
					-- or LU.LpuUnit_id = (select top 1 LS1.LpuUnit_id from LpuSection LS1 with (nolock) where LS1.LpuSection_id = LS.LpuSection_pid)
				inner join LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_MesOld Mes with (nolock) on Mes.Mes_id = ES.Mes_id
				left join PayType PT with (nolock) on PT.PayType_id = ES.PayType_id
				outer apply(select null as LpuSectionBedProfile_id, null as LpuSectionBedProfile_Name) LSBP
				left join LpuSectionWard LSW with (nolock) on LSW.LpuSectionWard_id = ES.LpuSectionWard_id
				left join v_TariffClass TC with (nolock) on TC.TariffClass_id = ES.TariffClass_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = ES.MedPersonal_id and MP.Lpu_id = ES.Lpu_id 
				left join v_MedStaffFact MSF with (nolock) on MSF.MedPersonal_id = ES.MedPersonal_id and MSF.LpuSection_id = ES.LpuSection_id 
				left join v_Diag Diag with (nolock) on Diag.Diag_id = ES.Diag_id
				-- если это последнее движение то берем данные об исходе  
				left join EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = ES.EvnSection_pid AND EvnPS.LpuSection_id = ES.LpuSection_id
				-- если есть следующее движение то исход - перевод в другое отделение
				left join v_EvnSection ESNEXT with (nolock) on ESNEXT.EvnSection_pid = ES.EvnSection_pid AND ESNEXT.EvnSection_Index = (ES.EvnSection_Index + 1)
				left join LpuSection LSNEXT with (nolock) on LSNEXT.LpuSection_id = ESNEXT.LpuSection_id
				--события исхода госпитализации
				outer apply (
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EL.EvnLeave_id as Leave_id,
							cast(EL.EvnLeave_UKL as numeric(10, 2)) as UKL,
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
							inner join LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EL.LeaveCause_id
							inner join ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EL.ResultDesease_id
							left join YesNo WITH (NOLOCK) on YesNo.YesNo_id = EL.EvnLeave_IsAmbul
						where
							EL.EvnLeave_pid = ES.EvnSection_id and ES.LeaveType_id = 1
					union
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOL.EvnOtherLpu_id as Leave_id,
							EOL.EvnOtherLpu_UKL as UKL,
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
							inner join LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOL.LeaveCause_id
							inner join ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOL.ResultDesease_id
							left join v_Org Org WITH (NOLOCK) on Org.Org_id = EOL.Org_oid
						where
							EOL.EvnOtherLpu_pid = ES.EvnSection_id and ES.LeaveType_id = 2
					union
						select top 1
							null as LeaveCause_id,
							null as ResultDesease_id,
							null as LeaveCause_Name,
							null as ResultDesease_Name,
							ED.EvnDie_id as Leave_id,
							ED.EvnDie_UKL as UKL,
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
							left join Diag WITH (NOLOCK) on Diag.Diag_id = ED.Diag_aid
							left join YesNo yesno1 WITH (NOLOCK) on yesno1.YesNo_id = ED.EvnDie_IsWait
							left join YesNo WITH (NOLOCK) on YesNo.YesNo_id = ED.EvnDie_IsAnatom
							left join v_LpuSection LSA with (nolock) on LSA.LpuSection_id = ed.LpuSection_aid
							left join v_Org OA with (nolock) on OA.Org_id = ed.Lpu_aid
							left join v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = ed.MedPersonal_aid and MPA.Lpu_id = LSA.Lpu_id
						where
							ED.EvnDie_pid = ES.EvnSection_id and ES.LeaveType_id = 3
					union
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOS.EvnOtherStac_id as Leave_id,
							EOS.EvnOtherStac_UKL as UKL,
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
							ISNULL(LUT.LpuUnitType_Name, '') as LpuUnitType_o_Name,
							ISNULL(LS.LpuSection_Name, '') as LpuSection_o_Name
						from
							v_EvnOtherStac EOS WITH (NOLOCK)
							inner join LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOS.LeaveCause_id
							inner join ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOS.ResultDesease_id
							inner join LpuUnitType LUT WITH (NOLOCK) on LUT.LpuUnitType_id = EOS.LpuUnitType_oid
							inner join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = EOS.LpuSection_oid
						where
							EOS.EvnOtherStac_pid = ES.EvnSection_id and ES.LeaveType_id = 4
					union
						select top 1
							LC.LeaveCause_id,
							RD.ResultDesease_id,
							RTRIM(LC.LeaveCause_Name) as LeaveCause_Name,
							RTRIM(RD.ResultDesease_Name) as ResultDesease_Name,
							EOS.EvnOtherSection_id as Leave_id,
							EOS.EvnOtherSection_UKL as UKL,
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
							ISNULL(LS.LpuSection_Name, '') as LpuSection_o_Name
						from
							v_EvnOtherSection EOS WITH (NOLOCK)
							inner join LeaveCause LC WITH (NOLOCK) on LC.LeaveCause_id = EOS.LeaveCause_id
							inner join ResultDesease RD WITH (NOLOCK) on RD.ResultDesease_id = EOS.ResultDesease_id
							inner join LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = EOS.LpuSection_oid
						where
							EOS.EvnOtherSection_pid = ES.EvnSection_id and ES.LeaveType_id = 5
				) leave
			where
				{$filter}
			order by
				EvnSection_id
		";
		
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			$this->load->library('swMorbus');
			$resp = swMorbus::processingEvnData($resp, 'EvnSection');
			return $resp;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Функция достающая все данные о заявке ФД
	 */
	function getEvnFuncRequestData($data)
	{
		$query = "
			select
				EFR.EvnFuncRequest_id,
				ED.EvnDirection_id,
				TTMS.TimetableMedService_id,
				ED.Server_id,
				ED.Person_id,
				ED.PersonEvn_id,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				IsNull(ED.PrehospDirect_id, EUP.PrehospDirect_id) as PrehospDirect_id,
				IsNull(ED.Lpu_sid, EUP.Lpu_did) AS Lpu_sid,
				IsNull(ED.LpuSection_id, EUP.LpuSection_did) as LpuSection_id,
				ED.Org_sid,
				case when 2 = coalesce(ED.EvnDirection_IsCito,EPFD.EvnPrescrFuncDiag_IsCito, 1) then 'Да' else 'Нет' end as EvnDirection_IsCito,
				MPA.Person_SurName +' '+ SUBSTRING(MPA.Person_FirName,1,1) +'.'+ SUBSTRING(MPA.Person_SecName,1,1) +'.' as MedPersonal_Fin,
				EUP.UslugaComplex_id,
				EUP.EvnUslugaPar_id,
				pt.PayType_Name,
				ls.LpuSection_Name,
				l.Lpu_Nick,
				pd.PrehospDirect_Name
			FROM v_EvnFuncRequest EFR with (nolock)
				LEFT JOIN v_EvnDirection_all ED with (nolock) ON ED.EvnDirection_id = EFR.EvnFuncRequest_pid
				LEFT JOIN v_EvnPrescrDirection EPD (nolock) ON EPD.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPD.EvnPrescr_id = EPFD.EvnPrescrFuncDiag_id
				LEFT JOIN v_EvnUslugaPar EUP with (nolock) on ED.EvnDirection_id = EUP.EvnDirection_id
				LEFT JOIN v_TimetableMedService_lite TTMS with (nolock) ON ED.EvnDirection_id = TTMS.EvnDirection_id
				LEFT JOIN v_PayType pt (nolock) on pt.PayType_id = IsNull(EFR.PayType_id, EUP.PayType_id)
				left join v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = IsNull(ED.MedPersonal_id, EUP.MedPersonal_did)
				left join v_PrehospDirect pd (nolock) on pd.PrehospDirect_id = IsNull(ED.PrehospDirect_id, EUP.PrehospDirect_id)
				left join v_Lpu l (nolock) on l.Lpu_id = IsNull(ED.Lpu_sid, EUP.Lpu_did)
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = IsNull(ED.LpuSection_id, EUP.LpuSection_did)
			WHERE EvnFuncRequest_id = :EvnFuncRequest_id
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных о рецепте
	 */
	function getEvnReceptData($data)
	{
		$query = "
			select
				er.EvnRecept_id,
				rf.ReceptForm_Name,
				er.EvnRecept_Ser,
				er.EvnRecept_Num,
				convert(varchar(10), er.EvnRecept_setDate, 104) as EvnRecept_setDate,
				rv.ReceptValid_Name,
				rd.ReceptDiscount_Name,
				l.Lpu_Name,
				mp.Person_Fin as MedPersonal_Fin,
				ps.Person_Surname,
				ps.Person_Firname,
				ps.Person_Secname,
				convert(varchar(10), ps.Person_Birthday, 104) as Person_Birthday,
				pt.PrivilegeType_Name,
				rfin.ReceptFinance_Name,
				case when er.WhsDocumentCostItemType_id is null and EvnRecept_Is7Noz = 2 then 'ВЗН' else wdcit.WhsDocumentCostItemType_Name end as WhsDocumentCostItemType_Name,
				d.Drug_Name,
				er.EvnRecept_Kolvo
			FROM
				v_EvnRecept ER with (nolock)
				left join v_ReceptForm rf with (nolock) on rf.ReceptForm_id = er.ReceptForm_id
				left join v_PrivilegeType pt with (nolock) on pt.PrivilegeType_id = er.PrivilegeType_id
				left join dbo.v_ReceptValid rv with (nolock) on rv.ReceptValid_id = er.ReceptValid_id
				left join v_ReceptDiscount rd with (nolock) on rd.ReceptDiscount_id = er.ReceptDiscount_id
				left join v_ReceptFinance rfin with (nolock) on rfin.ReceptFinance_id = er.ReceptFinance_id
				left join v_Lpu l with (nolock) on er.Lpu_id = l.Lpu_id
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = er.MedPersonal_id
				left join v_PersonState ps with (nolock) on ps.Person_id = er.Person_id
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = er.WhsDocumentCostItemType_id
				left join v_Drug d with (nolock) on d.Drug_id = er.Drug_id
			WHERE
				ER.EvnRecept_id = :EvnRecept_id
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных об обеспечении рецепта
	 */
	function getEvnReceptOtvData($data)
	{
		$query = "
			select
				rf.ReceptForm_Name,
				er.EvnRecept_Ser,
				er.EvnRecept_Num,
				convert(varchar(10), er.EvnRecept_setDate, 104) as EvnRecept_setDate,
				rv.ReceptValid_Name,
				rd.ReceptDiscount_Name,
				convert(varchar, er.EvnRecept_otpDT, 104) as EvnRecept_otpDate,
				orgf.OrgFarmacy_Name,
				s.Storage_Name as Storage_Name,
				mp.Person_Fin as MedPersonal_Fin,
				rfin.ReceptFinance_Name,
				cast(ROUND(ro.EvnRecept_Sum, 3) as varchar) as EvnRecept_Sum,
				case when er.WhsDocumentCostItemType_id is null and EvnRecept_Is7Noz = 2 then 'ВЗН' else wdcit.WhsDocumentCostItemType_Name end as WhsDocumentCostItemType_Name
			FROM
				v_EvnRecept ER with (nolock)
				left join v_ReceptForm rf with (nolock) on rf.ReceptForm_id = er.ReceptForm_id
				left join dbo.v_ReceptValid rv with (nolock) on rv.ReceptValid_id = er.ReceptValid_id
				left join v_ReceptDiscount rd with (nolock) on rd.ReceptDiscount_id = er.ReceptDiscount_id
				left join v_ReceptFinance rfin with (nolock) on rfin.ReceptFinance_id = er.ReceptFinance_id
				left join v_OrgFarmacy orgf with (nolock) on orgf.OrgFarmacy_id = er.OrgFarmacy_oid
				left join v_MedPersonal mp with (nolock) on mp.MedPersonal_id = er.MedPersonal_id
				left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = er.WhsDocumentCostItemType_id
				OUTER APPLY (
					select
						SUM(dus.DocumentUcStr_Sum) as EvnRecept_Sum,
						min(du.Storage_sid) as Storage_id
					from
						v_DocumentUcStr dus with (nolock)
						inner join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
						inner join v_Drug Dr on Dr.Drug_id = dus.Drug_id
					where
						dus.EvnRecept_id = er.EvnRecept_id
						and du.DrugDocumentStatus_id = 2
				) ro
				left join v_Storage s (nolock) on s.Storage_id = ro.Storage_id
			WHERE
				ER.EvnRecept_id = :EvnRecept_id
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');

			if (!empty($resp[0])) {
				$resp[0]['Drugs'] = $this->queryResult("
					select
						ps.PrepSeries_Ser,
						Dr.Drug_Name,
						dus.DocumentUcStr_Count as Drug_KolVo
					from
						v_DocumentUcStr dus with (nolock)
						inner join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
						inner join v_Drug Dr on Dr.Drug_id = dus.Drug_id
						left join rls.PrepSeries PS with (nolock) on PS.PrepSeries_id = dus.PrepSeries_id
					where
						dus.EvnRecept_id = :EvnRecept_id
						and du.DrugDocumentStatus_id = 2
				", $data);
			}

			return $resp;
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Функция достающая документ и кодирующая его в json-формат
	 */
	function get_doc($data) {
		$doc = array();
		
		$output = array();

		switch($data['Doc_Type']) {
			case 'EvnReceptOtv':
				// При подписании данных об обеспечении льготного рецепта хэш вычисляется из набора данных из полей совокупности таблиц: ReceptOtov, DocumentUcStr
				$query = "
					select
                    	otv.ReceptOtov_id,
						otv.Person_id,
						otv.Person_Snils,
						otv.PrivilegeType_id,
						otv.Lpu_id,
						otv.MedPersonalRec_id,
						otv.Diag_id,
						otv.EvnRecept_Ser,
						otv.EvnRecept_Num,
						otv.EvnRecept_setDT,
						otv.ReceptFinance_id,
						otv.ReceptValid_id,
						otv.OrgFarmacy_id,
						otv.Drug_id,
						otv.EvnRecept_Kolvo,
						otv.EvnRecept_obrDate,
						otv.EvnRecept_otpDate,
						otv.EvnRecept_Price,
						otv.ReceptDelayType_id,
						otv.EvnRecept_id,
						otv.EvnRecept_Is7Noz,
						otv.DrugFinance_id,
						otv.WhsDocumentCostItemType_id,
						otv.ReceptStatusType_id,
						otv.Drug_cid,
						otv.ReceptOtov_IsKEK,
						otv.Polis_Ser,
						otv.Polis_Num,
						dus.DocumentUc_id,
						dus.DocumentUcStr_Price,
						dus.DocumentUcStr_PriceR,
						dus.DocumentUcStr_EdCount,
						dus.DocumentUcStr_Count,
						dus.DocumentUcStr_Sum,
						dus.DocumentUcStr_SumR,
						dus.DocumentUcStr_SumNds,
						dus.DocumentUcStr_SumNdsR,
						dus.DocumentUcStr_Ser,
						dus.DocumentUcStr_CertNum,
						dus.DocumentUcStr_CertDate,
						dus.DocumentUcStr_CertGodnDate,
						dus.DocumentUcStr_CertOrg,
						dus.DocumentUcStr_IsLab,
						dus.DrugLabResult_Name,
						dus.DocumentUcStr_RashCount,
						dus.DocumentUcStr_RegDate,
						dus.DocumentUcStr_RegPrice,
						dus.DocumentUcStr_godnDate,
						dus.DocumentUcStr_setDate,
						dus.DocumentUcStr_Decl,
						dus.DocumentUcStr_Barcod,
						dus.DocumentUcStr_CertNM,
						dus.DocumentUcStr_CertDM,
						dus.DocumentUcStr_NTU,
						dus.DocumentUcStr_NZU,
						dus.DocumentUcStr_Reason
					from v_ReceptOtov otv with (nolock)
						left join v_DocumentUcStr DUS with (nolock) on otv.ReceptOtov_id = dus.ReceptOtov_id
						left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
					where
						otv.EvnRecept_id = :Doc_id 
						and (
							DUS.DocumentUcStr_id is null -- или нет вообще строк документа учёта
							or du.DrugDocumentStatus_id = 2 -- или документ учёта с этими строками исполнен
						)
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$doc = $resp[0];
						foreach ($doc as $key => $value) {
							if ($value instanceof DateTime) {
								$doc[$key] = $value->format('Y-m-d H:i:s');
							}
						}
					}
				}
				break;
			case 'EvnRecept':
				// При подписании данных о рецепте - все поля кроме полей по обеспечению/подписанию/updDT/updID
				$query = "
					select
						EvnClass_id,
						EvnClass_Name,
						EvnRecept_id,
						EvnRecept_setDate,
						EvnRecept_setTime,
						EvnRecept_didDate,
						EvnRecept_didTime,
						EvnRecept_disDate,
						EvnRecept_disTime,
						EvnRecept_pid,
						EvnRecept_rid,
						Lpu_id,
						Server_id,
						PersonEvn_id,
						EvnRecept_setDT,
						EvnRecept_disDT,
						EvnRecept_didDT,
						EvnRecept_insDT,
						pmUser_insID,
						Person_id,
						Morbus_id,	
						EvnRecept_IsArchive,
						EvnRecept_Guid,
						EvnStatus_id,
						EvnRecept_statusDate	
						EvnRecept_IsTransit,
						EvnRecept_Num,
						EvnRecept_Ser,
						Diag_id,
						ReceptDiscount_id,
						ReceptFinance_id,
						ReceptValid_id,
						PrivilegeType_id,
						EvnRecept_IsKEK,
						EvnRecept_Kolvo,
						MedPersonal_id,
						EvnRecept_UdostSer,
						EvnRecept_UdostNum,
						LpuSection_id,
						Drug_id,
						ReceptType_id,
						EvnRecept_IsMnn,
						EvnRecept_IsInReg,
						OrgFarmacy_id,
						EvnRecept_Signa,
						EvnRecept_IsNotOstat,
						DrugRequestRow_id,
						EvnRecept_ExtempContents,
						EvnRecept_IsExtemp,
						Person_Age,
						EvnRecept_Is7Noz,
						ReceptRemoveCauseType_id,
						DrugComplexMnn_id,
						PrepSeries_id	
						Drug_rlsid,
						DrugFinance_id,
						WhsDocumentCostItemType_id,
						EvnRecept_IsPaid	
						WhsDocumentUc_id,
						EvnRecept_IsOtherDiag,
						ReceptForm_id
					from
						v_EvnRecept with (nolock)
					where
						EvnRecept_id = :Doc_id 
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (count($resp) > 0) {
						$doc = $resp[0];
						foreach ($doc as $key => $value) {
							if ($value instanceof DateTime) {
								$doc[$key] = $value->format('Y-m-d H:i:s');
							}
						}
					}
				}
				break;
			default:
				if (in_array($data['Doc_Type'], array('Evn', 'EvnVizitPL', 'EvnPS', 'EvnSection', 'EvnPrescrPlan', 'EvnUslugaPar', 'EvnRecept', 'EvnDirection', 'EvnXml', 'EvnFuncRequest', 'EvnDirectionMorfoHistologic', 'EvnDirectionHistologic'))) {
					$query = "
						select
							*
						from
							v_{$data['Doc_Type']} (nolock)
						where
							{$data['Doc_Type']}_id = :Doc_id
					";
					$result = $this->db->query($query, $data);
					if (is_object($result)) {
						$resp = $result->result('array');
						if (count($resp) > 0) {
							$doc = $resp[0];
							foreach ($doc as $key => $value) {
								if ($value instanceof DateTime) {
									$doc[$key] = $value->format('Y-m-d H:i:s');
								}
							}
						}
					}

					unset($doc['Evn_IsSigned']);
					unset($doc[$data['Doc_Type'].'_IsSigned']);
					unset($doc['Evn_updDT']);
					unset($doc[$data['Doc_Type'].'_updDT']);
					unset($doc['Evn_signDT']);
					unset($doc[$data['Doc_Type'].'_signDT']);
					unset($doc['pmUser_updID']);
					unset($doc['pmUser_signID']);
				}
				break;
		}
		
		$output['doc'] = $doc;
		$output['html'] = '<table>';
		foreach($doc as $key => $field) {
			if ($field instanceof DateTime) {
				$field = $field->format('d.m.Y');
			}
			$output['html'] .= "<tr><td>{$key}</td><td>{$field}</td></tr>";
		}
		$output['html'] .= '</table>';
		
		$this->load->library('parser');
		switch($data['Doc_Type']) {
			case 'EvnVizitPL':
				$data['EvnVizitPL_id'] = $data['Doc_id'];
				$forhtml = $this->getEvnVizitPLData($data);
				if (is_array($forhtml) AND count($forhtml) > 0)
				{
					$forhtml = $forhtml[0];
				} else {
					return false;
				}
				$output['html'] = $this->parser->parse('signed_evn_vizit_pl', $forhtml, true);
			break;
			
			case 'EvnSection':
				$data['EvnSection_id'] = $data['Doc_id'];
				$forhtml = $this->getEvnSectionData($data);
				if (is_array($forhtml) AND count($forhtml) > 0)
				{
					$forhtml = $forhtml[0];
				} else {
					return false;
				}
				$output['html'] = $this->parser->parse('signed_evn_section', $forhtml, true);
			break;
			
			case 'EvnFuncRequest':
				$data['EvnFuncRequest_id'] = $data['Doc_id'];
				$forhtml = $this->getEvnFuncRequestData($data);
				if (is_array($forhtml) AND count($forhtml) > 0)
				{
					$forhtml = $forhtml[0];
				} else {
					return false;
				}
				$output['html'] = $this->parser->parse('signed_evn_func_request', $forhtml, true);
			break;

			case 'EvnRecept':
				$data['EvnRecept_id'] = $data['Doc_id'];
				$forhtml = $this->getEvnReceptData($data);
				if (is_array($forhtml) AND count($forhtml) > 0)
				{
					$forhtml = $forhtml[0];
				} else {
					return false;
				}
				$output['html'] = $this->parser->parse('signed_evn_recept', $forhtml, true);
			break;

			case 'EvnReceptOtv':
				$data['EvnRecept_id'] = $data['Doc_id'];
				$forhtml = $this->getEvnReceptOtvData($data);
				if (is_array($forhtml) AND count($forhtml) > 0)
				{
					$forhtml = $forhtml[0];
				} else {
					return false;
				}
				$output['html'] = $this->parser->parse('signed_evn_recept_otv', $forhtml, true);
			break;
		}
		
		// echo $output['html']; die();
		array_walk_recursive($output, 'ConvertFromWin1251ToUTF8');
		return $output;
	}
	
	/**
	 * Функция вывода JSON_P
	 */
	function json_p($data) {
		if (!empty($_REQUEST['callback'])) {
			echo $_REQUEST['callback']."(".json_encode($data).")";
		} else {
			echo json_encode($data);
		}
	}

	/**
	 * Обновление данных подписи в БД
	 */
	function updateSignedData($data) {
		$this->db->query("
			update {$data['table']} with (rowlock) set {$data['dateField']} = dbo.tzGetDate(), {$data['pmuserField']} = :pmUser_id, {$data['signedField']} = 2 where {$data['idField']} = :doc_id
		", array(
			'doc_id' => $data['doc_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}
}