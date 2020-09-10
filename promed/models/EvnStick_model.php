<?php
class EvnStick_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает данные для отображения документа в ЭМК
	 */
	function getEvnStickViewData($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $data['session']['org_id']
		);
		if (isset($data['EvnStick_pid']))
		{
			$filter = '
				ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :EvnStick_pid
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id =  :EvnStick_pid
				)';
			$queryParams['EvnStick_pid'] = $data['EvnStick_pid'];
		}
		else
		{
			return false;
			// тут будет взрываться, поэтому эту функцию не следует использовать для получения данных по EvnStick_id
			$filter = 'ESB.EvnStickBase_id = :EvnStick_id';
			$queryParams['EvnStick_id'] = $data['EvnStick_id'];
			//$queryParams['EvnStick_pid'] = $data['EvnStick_pid'];
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$session = $data['session'];
		$med_personal_id = !empty($session['medpersonal_id'])?$session['medpersonal_id']:null;
		$isMedStatUser = !empty($session['isMedStatUser']) ? $session['isMedStatUser'] : false;
		$isPolkaRegistrator = (!empty($session['ARMList']) && in_array('regpol',$session['ARMList']))/* || (!empty($session['evnstickblank_access']) && $session['evnstickblank_access'])*/;
		$isARMLVN = !empty($session['ARMList']) && in_array('lvn',$session['ARMList']);

		$parentEvnClass = $this->getFirstResultFromQuery("
			select top 1 EvnClass_SysNick from v_Evn with(nolock) where Evn_id = :Evn_id
		", array('Evn_id' => $data['EvnStick_pid']));
		$this->load->model($parentEvnClass.'_model', 'parent_model');
		if (method_exists($this->parent_model,'getAccessTypeQueryPart')) {
			$parentAccessType = $this->parent_model->getAccessTypeQueryPart($data, $queryParams);
		} else {
			$parentAccessType = '(1 != 1)';
		}

		// запрашиваем все id-шники и классы ЛВН
		$query = "
			select
				ESB.EvnStickBase_id,
				EC.EvnClass_SysNick
			from
				v_EvnStickBase ESB with (nolock)
				inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
			where
				EvnStickBase_mid = :EvnStick_pid

			union

			select
				ESB.EvnStickBase_id,
				EC.EvnClass_SysNick
			from
				EvnLink EL with (nolock)
				inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
				inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
			where
				EL.Evn_id = :EvnStick_pid

			order by
				EvnStickBase_id desc
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) )
		{
			$resp =  $result->result('array');
			$EvnStickArray = array();
			// для каждого свой запрос
			foreach($resp as $respone) {
				$queryParams['EvnStickBase_id'] = $respone['EvnStickBase_id'];

				if ($respone['EvnClass_SysNick'] == 'EvnStickDop') {
					$this->load->model('Stick_model');
					$accessType = $this->Stick_model->getEvnStickAccessType($data);

					$query = "
						select
							{$accessType}
							ESB.EvnStickBase_id as EvnStick_id,
							ESB.EvnStickBase_mid as EvnStick_mid,
							ESBD.EvnStickBase_pid as EvnStick_pid,
							:EvnStick_pid as Evn_pid,
							ESB.Person_id,
							ESB.PersonEvn_id,
							ESB.Server_id,
							2 as evnStickType, -- Вид док-та (код)
							'ЛВН' as StickType_Name, -- Вид док-та (наименование)
							convert(varchar(10), ESBD.EvnStickBase_setDT, 104) as EvnStick_setDate, -- Дата выдачи
							case when SWT.StickWorkType_Name is null then '' else '/ ' + RTRIM(SWT.StickWorkType_Name) end as StickWorkType_Name,
							case
								when EC.EvnClass_SysNick = 'EvnStickStudent' then ''
								when ESB.EvnStickBase_mid = :EvnStick_pid then '/ Текущий'
								when EvnPL.EvnPL_id is not null then '/ ТАП'
								when EvnPLStom.EvnPLStom_id is not null then '/ Стом. ТАП'
								when EvnPS.EvnPS_id is not null then '/ КВС'
								else ''
							end as EvnStick_ParentTypeName, -- тип родительского документа
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
								when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
								when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
								else ''
							end as EvnStick_ParentNum, -- номер родительского документа
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then convert(varchar(10), EvnPL.EvnPL_setDT, 104)
								when EvnPLStom.EvnPLStom_id is not null then convert(varchar(10), EvnPLStom.EvnPLStom_setDT, 104)
								when EvnPS.EvnPS_id is not null then convert(varchar(10), EvnPS.EvnPS_setDT, 104)
								else ''
							end as EvnStick_ParentDate, -- дата родительского документа
							case when SO.StickOrder_Name is null then '' else '/ ' + RTRIM(SO.StickOrder_Name) + ' /' end as StickOrder_Name,
							RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
							RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
							case
								when SLT.StickLeaveType_id is null then '/ ЛВН открыт'
								else null
							end as StickLeaveType_OpenName,
							case
								when RESS.RegistryESStorage_id is not null or ESB.EvnStickBase_IsFSS = 2 then 1
								else 0
							end as EvnStick_isELN,
							SFT.StickFSSType_Name,
							SFT.StickFSSType_Code,
							isnull(SFD.StickFSSData_id, 0) as requestExist,
							SLT.StickLeaveType_id,
							SLT.StickLeaveType_Code,
							SLT.StickLeaveType_Name,
							convert(varchar(10), ESBD.EvnStickBase_disDT, 4) as EvnStick_disDate,
							ps.Person_Surname,
							ps.Person_Firname,
							ps.Person_Secname,
							ESB.EvnStickBase_IsDelQueue
						from v_EvnStickBase ESB with (nolock)
							inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
							inner join v_PersonState ps with (nolock) on ps.Person_id = ESB.Person_id
							left join v_EvnStickBase ESBD with (nolock) on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid
							left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ESB.MedStaffFact_id
							-- ТАП/КВС
							left join v_EvnPL EvnPL with (nolock) on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
							left join v_EvnPLStom EvnPLStom with (nolock) on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
							left join v_EvnPS EvnPS with (nolock) on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
							-- end ТАП/КВС
							left join StickOrder SO with (nolock) on SO.StickOrder_id = ESBD.StickOrder_id
							left join StickWorkType SWT with (nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
							left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESB.EvnStickBase_pid
							left join v_StickLeaveType SLT with(nolock) on SLT.StickLeaveType_id = ES.StickLeaveType_id
							left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
							outer apply (
								select top 1 StickFSSData_id
								from v_StickFSSData with (nolock)
								where
									StickFSSData_StickNum = ESB.EvnStickBase_Num
									and StickFSSDataStatus_id not in (3, 4, 5)
							) as SFD
							left join v_RegistryESStorage RESS with (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id
							outer apply(
								select top 1 RegistryESDataStatus_id
								from v_RegistryESData with(nolock)
								where Evn_id = ESB.EvnStickBase_id
								order by case when RegistryESDataStatus_id = 2 then 0 else 1 end
							) as RESD
							outer apply (
								select top 1 Org_id
								from v_EvnStickWorkRelease ESWR with (nolock)
								where ESB.EvnStickBase_id = ESWR.EvnStickBase_id and ESWR.Org_id = :Org_id
							) ESWR
						where
							ESB.EvnStickBase_id = :EvnStickBase_id
					";
				} else if ($respone['EvnClass_SysNick'] == 'EvnStickStudent') {
					$query = "
						select
							case when (
								case
									when ESB.Lpu_id = :Lpu_id then 1
									" . (count($data['session']['linkedLpuIdList']) > 1 ? "when ESB.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and ISNULL(ESB.EvnStickBase_IsTransit, 1) = 2 then 1" : "") . "
									when " . (count($med_personal_list) > 0 ? 1 : 0) . " = 1 then 1
									when " . ($isMedStatUser || $isPolkaRegistrator || $isARMLVN ? 1 : 0) . " = 1 then 1
									else 0
								end = 1
								" . (!$isPolkaRegistrator && !$isMedStatUser && !$isARMLVN && count($med_personal_list)>0 ? "and (ESB.MedPersonal_id is null or ESB.MedPersonal_id in (".implode(',',$med_personal_list).") )" : "") . "
							) or (
								$parentAccessType
							) then 'edit' else 'view' end as accessType,
							ESB.EvnStickBase_id as EvnStick_id,
							ESB.EvnStickBase_mid as EvnStick_mid,
							ESB.EvnStickBase_pid as EvnStick_pid,
							:EvnStick_pid as Evn_pid,
							ESB.Person_id,
							ESB.PersonEvn_id,
							ESB.Server_id,
							 3 as evnStickType, -- Вид док-та (код)
							'Справка учащегося' as StickType_Name, -- Вид док-та (наименование)
							convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStick_setDate, -- Дата выдачи
							case when SWT.StickWorkType_Name is null then '' else '/ ' + RTRIM(SWT.StickWorkType_Name) end as StickWorkType_Name,
							case
								when EC.EvnClass_SysNick = 'EvnStickStudent' then ''
								when ESB.EvnStickBase_mid = :EvnStick_pid then '/ Текущий'
								when EvnPL.EvnPL_id is not null then '/ ТАП'
								when EvnPLStom.EvnPLStom_id is not null then '/ Стом. ТАП'
								when EvnPS.EvnPS_id is not null then '/ КВС'
								else ''
							end as EvnStick_ParentTypeName, -- тип родительского документа
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
								when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
								when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
								else ''
							end as EvnStick_ParentNum, -- номер родительского документа
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then convert(varchar(10), EvnPL.EvnPL_setDT, 104)
								when EvnPLStom.EvnPLStom_id is not null then convert(varchar(10), EvnPLStom.EvnPLStom_setDT, 104)
								when EvnPS.EvnPS_id is not null then convert(varchar(10), EvnPS.EvnPS_setDT, 104)
								else ''
							end as EvnStick_ParentDate, -- дата родительского документа
							case when SO.StickOrder_Name is null then '' else '/ ' + RTRIM(SO.StickOrder_Name) + ' /' end as StickOrder_Name,
							RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
							RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
							case
								when SLT.StickLeaveType_id is null then '/ ЛВН открыт'
								else null
							end as StickLeaveType_OpenName,
							SFT.StickFSSType_Name,
							SFT.StickFSSType_Code,
							SLT.StickLeaveType_id,
							SLT.StickLeaveType_Code,
							SLT.StickLeaveType_Name,
							convert(varchar(10), ESB.EvnStickBase_disDT, 4) as EvnStick_disDate,
							ps.Person_Surname,
							ps.Person_Firname,
							ps.Person_Secname,
							ESB.EvnStickBase_IsDelQueue
						from v_EvnStickBase ESB with (nolock)
							inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
							inner join v_PersonState ps with (nolock) on ps.Person_id = ESB.Person_id
							-- ТАП/КВС
							left join v_EvnPL EvnPL with (nolock) on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
							left join v_EvnPLStom EvnPLStom with (nolock) on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
							left join v_EvnPS EvnPS with (nolock) on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
							-- end ТАП/КВС
							left join StickOrder SO with (nolock) on SO.StickOrder_id = ESB.StickOrder_id
							left join StickWorkType SWT with (nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
							left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESB.EvnStickBase_id
							left join v_StickLeaveType SLT with(nolock) on SLT.StickLeaveType_id = ES.StickLeaveType_id
							left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
							outer apply(
								select top 1 RegistryESDataStatus_id
								from v_RegistryESData with(nolock)
								where Evn_id = ESB.EvnStickBase_id
								order by case when RegistryESDataStatus_id = 2 then 0 else 1 end
							) as RESD
						where
							ESB.EvnStickBase_id = :EvnStickBase_id
					";
				} else {
					$this->load->model('Stick_model');
					$accessType = $this->Stick_model->getEvnStickAccessType($data);

					$query = "
						select
							{$accessType}
							ESB.EvnStickBase_id as EvnStick_id,
							ESB.EvnStickBase_mid as EvnStick_mid,
							ESB.EvnStickBase_pid as EvnStick_pid,
							:EvnStick_pid as Evn_pid,
							ESB.Person_id,
							ESB.PersonEvn_id,
							ESB.Server_id,
							case
								when EC.EvnClass_SysNick = 'EvnStick' then 1
								when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
								else 0
							end as evnStickType, -- Вид док-та (код)
							case
								when EC.EvnClass_SysNick = 'EvnStick' then 'ЛВН'
								when EC.EvnClass_SysNick = 'EvnStickStudent' then 'Справка учащегося'
								else ''
							end as StickType_Name, -- Вид док-та (наименование)
							--convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStick_setDate, -- Дата выдачи
							CONVERT(varchar(10), EBWR_d.evnStickWorkRelease_begDT,104) as EvnStick_setDate,
							case when SWT.StickWorkType_Name is null then '' else '/ ' + RTRIM(SWT.StickWorkType_Name) end as StickWorkType_Name,
							case
								when EC.EvnClass_SysNick = 'EvnStickStudent' then ''
								when ESB.EvnStickBase_mid = :EvnStick_pid then '/ Текущий'
								when EvnPL.EvnPL_id is not null then '/ ТАП'
								when EvnPLStom.EvnPLStom_id is not null then '/ Стом. ТАП'
								when EvnPS.EvnPS_id is not null then '/ КВС'
								else ''
							end as EvnStick_ParentTypeName, -- тип родительского документа
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
								when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
								when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
								else ''
							end as EvnStick_ParentNum, -- номер родительского документа
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then convert(varchar(10), EvnPL.EvnPL_setDT, 104)
								when EvnPLStom.EvnPLStom_id is not null then convert(varchar(10), EvnPLStom.EvnPLStom_setDT, 104)
								when EvnPS.EvnPS_id is not null then convert(varchar(10), EvnPS.EvnPS_setDT, 104)
								else ''
							end as EvnStick_ParentDate, -- дата родительского документа
							case
								when  RESS.RegistryESStorage_id is not null or ESB.EvnStickBase_IsFSS = 2 then 1
								else 0
							end as EvnStick_isELN,
							case when SO.StickOrder_Name is null then '' else '/ ' + RTRIM(SO.StickOrder_Name) + ' /' end as StickOrder_Name,
							RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
							RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
							case
								when SLT.StickLeaveType_id is null then '/ ЛВН открыт'
								else null
							end as StickLeaveType_OpenName,
							SFT.StickFSSType_Name,
							SFT.StickFSSType_Code,
							isnull(SFD.StickFSSData_id, 0) as requestExist,
							SLT.StickLeaveType_id,
							SLT.StickLeaveType_Code,
							SLT.StickLeaveType_Name,
							convert(varchar(10), ESB.EvnStickBase_disDT, 4) as EvnStick_disDate,
							ps.Person_Surname,
							ps.Person_Firname,
							ps.Person_Secname,
							ESB.EvnStickBase_IsDelQueue
						from v_EvnStickBase ESB with (nolock)
							inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
							inner join v_PersonState ps with (nolock) on ps.Person_id = ESB.Person_id
							left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = ESB.MedStaffFact_id
							-- ТАП/КВС
							left join v_EvnPL EvnPL with (nolock) on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
							left join v_EvnPLStom EvnPLStom with (nolock) on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
							left join v_EvnPS EvnPS with (nolock) on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
							-- end ТАП/КВС
							left join StickOrder SO with (nolock) on SO.StickOrder_id = ESB.StickOrder_id
							left join StickWorkType SWT with (nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
							left join v_EvnStick ES with (nolock) on ES.EvnStick_id = ESB.EvnStickBase_id
							left join v_StickLeaveType SLT with(nolock) on SLT.StickLeaveType_id = ES.StickLeaveType_id
							left join v_StickFSSType SFT with (nolock) on SFT.StickFSSType_id = ESB.StickFSSType_id
							left join v_RegistryESStorage RESS with (nolock) on RESS.EvnStickBase_id = ESB.EvnStickBase_id
							outer apply (
								select top 1 StickFSSData_id
								from v_StickFSSData with (nolock)
								where
									StickFSSData_StickNum = ESB.EvnStickBase_Num
									and StickFSSDataStatus_id not in (3, 4, 5)
							) as SFD
							outer apply(
								select top 1 RegistryESDataStatus_id
								from v_RegistryESData with(nolock)
								where Evn_id = ESB.EvnStickBase_id
								order by case when RegistryESDataStatus_id = 2 then 0 else 1 end
							) as RESD
							outer apply (
								select top 1 Org_id
								from v_EvnStickWorkRelease ESWR with (nolock)
								where ESB.EvnStickBase_id = ESWR.EvnStickBase_id and ESWR.Org_id = :Org_id
							) ESWR
							outer apply(
							select top 1 evnStickWorkRelease_begDT
								from v_EvnStickWorkRelease ESWR with (nolock)
								where ESB.EvnStickBase_id = ESWR.EvnStickBase_id
							) EBWR_d
						where
							ESB.EvnStickBase_id = :EvnStickBase_id
					";
				}

				$result_evnstick = $this->db->query($query, $queryParams);
				if (is_object($result_evnstick)) {
					$resp_evnstick = $result_evnstick->result('array');
					if (count($resp_evnstick) > 0) {
						$EvnStickArray[] = $resp_evnstick[0];
					}
				}
			}
			
			foreach($EvnStickArray as &$EvnStick) {
				$EvnStick['StickFSSDataStatus_Name'] = '';
				
				if ($EvnStick['StickFSSType_Code'] == '040') {
					$resp = $this->getFirstRowFromQuery("
						declare @curDT date = cast(dbo.tzGetDate() as date);
						select top 1 case 
							when sfds.StickFSSDataStatus_Code in (4,5) then 'Данные МСЭ не обновлены'
							else sfds.StickFSSDataStatus_Name
						end as StickFSSDataStatus_Name,
						sfds.StickFSSDataStatus_Code,
						sfd.StickFSSData_id
						from v_StickFSSData sfd with (nolock)
						inner join v_StickFSSDataStatus sfds (nolock) on sfd.StickFSSDataStatus_id = sfds.StickFSSDataStatus_id
						where 
							sfd.StickFSSData_insDT > dateadd(month, -3, @curDT)
							and sfd.EvnStickBase_id = :EvnStick_id
							and sfd.StickFSSData_IsNeedMSE = 2
						order by StickFSSData_insDT desc
					",[
						'EvnStick_id' => $EvnStick['EvnStick_id']
					]);
					if (!empty($resp)) {
						$EvnStick['StickFSSDataStatus_Code'] = $resp['StickFSSDataStatus_Code'];
						$EvnStick['StickFSSDataStatus_Name'] = $resp['StickFSSDataStatus_Name'];
						$EvnStick['StickFSSData_id'] = $resp['StickFSSData_id'];
					}
				}
			}

			return $EvnStickArray;
		}

		return false;
	}

	/**
	 * Возвращает данные для отображения документа в ЭМК
	 */
	function getEvnStickMarkerData($data) {
		$response = array(
			'data' => array(),
			'count' => 0
		);
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => $data['session']['org_id']
		);
		if (isset($data['EvnStick_pid']))
		{
			$filter = '
				ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :EvnStick_pid
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id =  :EvnStick_pid
				)';
			$queryParams['EvnStick_pid'] = $data['EvnStick_pid'];
		}
		else
		{
			return false;
			// тут будет взрываться, поэтому эту функцию не следует использовать для получения данных по EvnStick_id
			$filter = 'ESB.EvnStickBase_id = :EvnStick_id';
			$queryParams['EvnStick_id'] = $data['EvnStick_id'];
			//$queryParams['EvnStick_pid'] = $data['EvnStick_pid'];
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$session = $data['session'];
		$med_personal_id = !empty($session['medpersonal_id'])?$session['medpersonal_id']:null;
		$isMedStatUser = !empty($session['isMedStatUser']) ? $session['isMedStatUser'] : false;
		$isPolkaRegistrator = (!empty($session['ARMList']) && in_array('regpol',$session['ARMList']))/* || (!empty($session['evnstickblank_access']) && $session['evnstickblank_access'])*/;
		$isARMLVN = !empty($session['ARMList']) && in_array('lvn',$session['ARMList']);

		$parentEvnClass = $this->getFirstResultFromQuery("
			select top 1 EvnClass_SysNick from v_Evn with(nolock) where Evn_id = :Evn_id
		", array('Evn_id' => $data['EvnStick_pid']));
		$this->load->model($parentEvnClass.'_model', 'parent_model');
		if (method_exists($this->parent_model,'getAccessTypeQueryPart')) {
			$parentAccessType = $this->parent_model->getAccessTypeQueryPart($data, $queryParams);
		} else {
			$parentAccessType = '(1 != 1)';
		}

		// запрашиваем все id-шники и классы ЛВН
		$query = "
			select
				ESB.EvnStickBase_id,
				EC.EvnClass_SysNick
			from
				v_EvnStickBase ESB with (nolock)
				inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
			where
				EvnStickBase_mid = :EvnStick_pid

			union all

			select
				ESB.EvnStickBase_id,
				EC.EvnClass_SysNick
			from
				EvnLink EL with (nolock)
				inner join v_EvnStickBase ESB with (nolock) on ESB.EvnStickBase_id = EL.Evn_lid
				inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
			where
				EL.Evn_id = :EvnStick_pid

			order by
				EvnStickBase_id desc
		";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) )
		{
			$resp =  $result->result('array');
			$EvnStickArray = array();
			// для каждого свой запрос
			foreach($resp as $respone) {
				$queryParams['EvnStickBase_id'] = $respone['EvnStickBase_id'];

				$query = "
					select
						ESB.EvnStickBase_id as EvnStick_id,
						ESB.EvnStickBase_mid as EvnStick_mid,
						ESB.EvnStickBase_pid as EvnStick_pid,
						:EvnStick_pid as Evn_pid,
						ESB.Person_id,
						ESB.PersonEvn_id,
						ESB.Server_id,
						RTRIM(ISNULL(ESB.EvnStickBase_Ser, '')) as EvnStick_Ser,
						RTRIM(ISNULL(ESB.EvnStickBase_Num, '')) as EvnStick_Num,
						convert(varchar(10), ESB.EvnStickBase_setDate, 104) as EvnStick_setDate,
						convert(varchar(10), ESB.EvnStickBase_disDate, 104) as EvnStick_disDate,
						SWT.StickWorkType_Name,
						ESB.EvnStickBase_IsDelQueue,
						coalesce(ESWR.TotalDaysCount, 0) as TotalDaysCount,
						(
							convert(varchar(10), ESWR.minEvnStickWorkRelease_begDT, 104) + ' - ' + 
							convert(varchar(10), ESWR.maxEvnStickWorkRelease_endDT, 104)
						) as WorkReleaseRange,
						(
							lower(RLT.RelatedLinkType_Name)
							+ ' ' + rtrim(PS.Person_Surname)
							+ ' ' + rtrim(PS.Person_Firname)
							+ coalesce(' ' + rtrim(PS.Person_Secname), '')
						) as RelatedPerson
					from v_EvnStickBase ESB with (nolock)
						inner join EvnClass EC with (nolock) on EC.EvnClass_id = ESB.EvnClass_id
						inner join v_PersonState PS with (nolock) on PS.Person_id = ESB.Person_id
						left join StickWorkType SWT with (nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
						left join v_Evn E with(nolock) on E.Evn_id = :EvnStick_pid
						outer apply (
							select top 1
							min(ESWR.EvnStickWorkRelease_begDT) as minEvnStickWorkRelease_begDT,
							max(ESWR.EvnStickWorkRelease_endDT) as maxEvnStickWorkRelease_endDT,
							sum(datediff(day, ESWR.EvnStickWorkRelease_begDT, ESWR.EvnStickWorkRelease_endDT)) + 1 as TotalDaysCount
							from v_EvnStickWorkRelease ESWR with(nolock)
							where ESWR.EvnStickBase_id = ESB.EvnStickBase_id
						) ESWR
						outer apply (
							select top 1 ESCP.RelatedLinkType_id
							from v_EvnStickCarePerson ESCP with(nolock)
							where ESCP.Evn_id = ESB.EvnStickBase_id
							and ESCP.Person_id = E.Person_id
						) ESCP
						left join v_RelatedLinkType RLT with(nolock) on RLT.RelatedLinkType_id = ESCP.RelatedLinkType_id
					where
						ESB.EvnStickBase_id = :EvnStickBase_id
				";

				//echo getDebugSQL($query, $queryParams);exit;
				$result_evnstick = $this->db->query($query, $queryParams);
				if (is_object($result_evnstick)) {
					$resp_evnstick = $result_evnstick->result('array');
					if (count($resp_evnstick) > 0) {
						$EvnStickArray[] = $resp_evnstick[0];
					}
				}
			}

			$response['data'] = $EvnStickArray;
			$response['count'] = count($EvnStickArray);
		}

		return $response;
	}

	/**
	 * Возвращаяет данные для вывода списка открытых ЛВН в сигнальной информации ЭМК
	 */
	function getEvnStickOpenInfoViewData($data) {
		$queryParams = array('Person_id' => $data['Person_id']);
		$filter = '';
		if (isset($data['EvnStick_pid']))
		{
			$filter .= '
				ESB.EvnStickBase_id in (
					select EvnStickbase_id from v_EvnStickBase with (nolock) where EvnStickBase_mid = :EvnStick_pid
					union all
					select Evn_lid from EvnLink with (nolock) where Evn_id =  :EvnStick_pid
				)';
			$queryParams['EvnStick_pid'] = $data['EvnStick_pid'];
		}

		$lpuFilter = getAccessRightsLpuFilter('ESB.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}

		$query = "
			select
				ESB.EvnStickBase_id as EvnStick_id,
				ESB.EvnStickBase_mid as EvnStick_mid,
				case
					when EC.EvnClass_SysNick = 'EvnStickDop' then ESBD.EvnStickBase_pid
					else ESB.EvnStickBase_pid
				end as EvnStick_pid,
				ESB.EvnStickBase_id as EvnStickOpenInfo_id,
				ESB.Person_id,
				ESB.PersonEvn_id,
				ESB.Server_id,
				ESB.EvnStickBase_Ser as EvnStick_Ser,
				ESB.EvnStickBase_Num as EvnStick_Num,
				convert(varchar(10), ESB.EvnStickBase_setDT, 104) as EvnStick_setDate,
				SWT.StickWorkType_Name,
				rtrim(isnull(SO.StickOrder_Name, '')) as StickOrder_Name,
				case
					when EC.EvnClass_SysNick = 'EvnStick' then 1
					when EC.EvnClass_SysNick = 'EvnStickDop' then 2
					when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
					else 0
				end as evnStickType
			from v_EvnStickBase ESB with(nolock)
				left join v_EvnClass EC with(nolock) on EC.EvnClass_id = ESB.EvnClass_id
				left join v_StickWorkType SWT with(nolock) on SWT.StickWorkType_id = ESB.StickWorkType_id
				left join v_EvnStickBase ESBD with (nolock) on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid
					and EC.EvnClass_SysNick = 'EvnStickDop'
				left join v_StickOrder SO with(nolock) on SO.StickOrder_id = isnull(ESBD.StickOrder_id,ESB.StickOrder_id)
			where
				ESB.Person_id = :Person_id
				and isnull(ESB.EvnStickBase_disDT,ESBD.EvnStickBase_disDT) is null
				and EC.EvnClass_SysNick in ('EvnStick','EvnStickDop')
				{$filter}
			order by
				ESB.EvnStickBase_setDT
		";
		$result = $this->db->query($query, $queryParams);

		//echo getDebugSQL($query, $queryParams); exit();
		if ( is_object($result) )
		{
			return $result->result('array');
			//return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function deleteEvnStick($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnStick_del
				@EvnStick_id = :EvnStick_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnStick_id' => $data['EvnStick_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление листа нетрудоспособности)'));
		}
	}


	/**
	 * Загрузка грида
	 */
	function loadEvnStickGrid($data) {
		$query = "
			select
				ES.EvnStick_id,
				ES.EvnStick_pid,
				ES.Person_id,
				ES.PersonEvn_id,
				ES.Server_id,
				ES.StickType_id,
				ES.StickCause_id,
				ES.Sex_id,
				convert(varchar(10), ES.EvnStick_begDate, 104) as EvnStick_begDate,
				convert(varchar(10), ES.EvnStick_endDate, 104) as EvnStick_endDate,
				RTRIM(ST.StickType_Name) as StickType_Name,
				RTRIM(ES.EvnStick_Ser) as EvnStick_Ser,
				RTRIM(ES.EvnStick_Num) as EvnStick_Num,
				ES.EvnStick_Age
			from v_EvnStick ES with(nolock)
				left join StickType ST with(nolock) on ST.StickType_id = ES.StickType_id
			where ES.EvnStick_pid = :EvnStick_pid
		";
		$result = $this->db->query($query, array('EvnStick_pid' => $data['EvnStick_pid']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function saveEvnStick($data) {
		$procedure = '';

		if ( (!isset($data['EvnStick_id'])) || ($data['EvnStick_id'] <= 0) ) {
			$procedure = 'p_EvnStick_ins';
		}
		else {
			$procedure = 'p_EvnStick_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnStick_id;
			exec " . $procedure . "
				@EvnStick_id = @Res output,
				@EvnStick_pid = :EvnStick_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@StickType_id = :StickType_id,
				@StickCause_id = :StickCause_id,
				@EvnStick_Ser = :EvnStick_Ser,
				@EvnStick_Num = :EvnStick_Num,
				@EvnStick_begDate = :EvnStick_begDate,
				@EvnStick_endDate = :EvnStick_endDate,
				@Sex_id = :Sex_id,
				@EvnStick_Age = :EvnStick_Age,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnStick_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnStick_id' => $data['EvnStick_id'],
			'EvnStick_pid' => $data['EvnStick_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'StickType_id' => $data['StickType_id'],
			'StickCause_id' => $data['StickCause_id'],
			'EvnStick_Ser' => $data['EvnStick_Ser'],
			'EvnStick_Num' => $data['EvnStick_Num'],
			'EvnStick_begDate' => $data['EvnStick_begDate'],
			'EvnStick_endDate' => $data['EvnStick_endDate'],
			'Sex_id' => $data['Sex_id'],
			'EvnStick_Age' => $data['EvnStick_Age'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
?>