<?php
class EvnStick_model extends SwPgModel {
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
					select EvnStickbase_id from v_EvnStickBase where EvnStickBase_mid = :EvnStick_pid
					union all
					select Evn_lid from EvnLink where Evn_id =  :EvnStick_pid
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
			select EvnClass_SysNick as \"EvnClass_SysNick\" from v_Evn where Evn_id = :Evn_id limit 1
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
				ESB.EvnStickBase_id as \"EvnStickBase_id\",
				EC.EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_EvnStickBase ESB
				inner join EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
			where
				EvnStickBase_mid = :EvnStick_pid

			union

			select
				ESB.EvnStickBase_id ,
				EC.EvnClass_SysNick 
			from
				EvnLink EL
				inner join v_EvnStickBase ESB on ESB.EvnStickBase_id = EL.Evn_lid
				inner join EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
			where
				EL.Evn_id = :EvnStick_pid

			order by
				\"EvnStickBase_id\" desc
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
							ESB.EvnStickBase_id as \"EvnStick_id\",
							ESB.EvnStickBase_mid as \"EvnStick_mid\",
							ESBD.EvnStickBase_pid as \"EvnStick_pid\",
							:EvnStick_pid as \"Evn_pid\",
							ESB.Person_id as \"Person_id\",
							ESB.PersonEvn_id as \"PersonEvn_id\",
							ESB.Server_id as \"Server_id\",
							2 as \"evnStickType\",
							'ЛВН' as \"StickType_Name\",
							to_char(ESBD.EvnStickBase_setDT, 'dd.mm.yyyy') as \"EvnStick_setDate\",
							case when SWT.StickWorkType_Name is null then '' else '/ ' || RTRIM(SWT.StickWorkType_Name) end as \"StickWorkType_Name\",
							case
								when EC.EvnClass_SysNick = 'EvnStickStudent' then ''
								when ESB.EvnStickBase_mid = :EvnStick_pid then '/ Текущий'
								when EvnPL.EvnPL_id is not null then '/ ТАП'
								when EvnPLStom.EvnPLStom_id is not null then '/ Стом. ТАП'
								when EvnPS.EvnPS_id is not null then '/ КВС'
								else ''
							end as \"EvnStick_ParentTypeName\",
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
								when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
								when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
								else ''
							end as \"EvnStick_ParentNum\",
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then to_char(EvnPL.EvnPL_setDT, 'dd.mm.yyyy')
								when EvnPLStom.EvnPLStom_id is not null then to_char(EvnPLStom.EvnPLStom_setDT, 'dd.mm.yyyy')
								when EvnPS.EvnPS_id is not null then to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy')
								else ''
							end as \"EvnStick_ParentDate\", -- дата родительского документа
							case when SO.StickOrder_Name is null then '' else '/ ' || RTRIM(SO.StickOrder_Name) || ' /' end as \"StickOrder_Name\",
							RTRIM(coalesce(ESB.EvnStickBase_Ser, '')) as \"EvnStick_Ser\",
							RTRIM(coalesce(ESB.EvnStickBase_Num, '')) as \"EvnStick_Num\",
							case
								when SLT.StickLeaveType_id is null then '/ ЛВН открыт'
								else null
							end as \"StickLeaveType_OpenName\",
							case
								when  RESS.RegistryESStorage_id is not null or ESB.EvnStickBase_IsFSS = 2 then 1
								else 0
							end as \"EvnStick_isELN\",
							SFT.StickFSSType_Name as \"StickFSSType_Name\",
							coalesce(SFD.StickFSSData_id, 0) as \"requestExist\",
							SLT.StickLeaveType_id as \"StickLeaveType_id\",
							SLT.StickLeaveType_Code as \"StickLeaveType_Code\",
							SLT.StickLeaveType_Name as \"StickLeaveType_Name\",
							to_char(ESBD.EvnStickBase_disDT, 'dd.mm.yyyy') as \"EvnStick_disDate\",
							ps.Person_Surname as \"Person_Surname\",
							ps.Person_Firname as \"Person_Firname\",
							ps.Person_Secname as \"Person_Secname\",
							ESB.EvnStickBase_IsDelQueue as \"EvnStickBase_IsDelQueue\"
						from v_EvnStickBase ESB
							inner join EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
							inner join v_PersonState ps on ps.Person_id = ESB.Person_id
							left join v_EvnStickBase ESBD on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid
							left join v_MedStaffFact MSF on MSF.MedStaffFact_id = ESB.MedStaffFact_id
							-- ТАП/КВС
							left join v_EvnPL EvnPL on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
							left join v_EvnPLStom EvnPLStom on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
							left join v_EvnPS EvnPS on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
							-- end ТАП/КВС
							left join StickOrder SO on SO.StickOrder_id = ESBD.StickOrder_id
							left join StickWorkType SWT on SWT.StickWorkType_id = ESB.StickWorkType_id
							left join v_EvnStick ES on ES.EvnStick_id = ESB.EvnStickBase_pid
							left join v_StickLeaveType SLT on SLT.StickLeaveType_id = ES.StickLeaveType_id
							left join v_StickFSSType SFT on SFT.StickFSSType_id = ESB.StickFSSType_id
							left join lateral (
								select StickFSSData_id
								from v_StickFSSData
								where
									StickFSSData_StickNum = ESB.EvnStickBase_Num
									and StickFSSDataStatus_id not in (3, 4, 5)
								limit 1
							) as SFD on true
							left join v_RegistryESStorage RESS on RESS.EvnStickBase_id = ESB.EvnStickBase_id
							left join lateral(
								select RegistryESDataStatus_id
								from v_RegistryESData
								where Evn_id = ESB.EvnStickBase_id
								order by case when RegistryESDataStatus_id = 2 then 0 else 1 end
								limit 1
							) as RESD on true
							left join lateral (
								select Org_id
								from v_EvnStickWorkRelease ESWR
								where ESB.EvnStickBase_id = ESWR.EvnStickBase_id and ESWR.Org_id = :Org_id
								limit 1
							) as ESWR on true
						where
							ESB.EvnStickBase_id = :EvnStickBase_id
					";
				} else if ($respone['EvnClass_SysNick'] == 'EvnStickStudent') {
					$query = "
						select
							case when (
								case
									when ESB.Lpu_id = :Lpu_id then 1
									" . (count($data['session']['linkedLpuIdList']) > 1 ? "when ESB.Lpu_id in (" . implode(',', $data['session']['linkedLpuIdList']) . ") and coalesce(ESB.EvnStickBase_IsTransit, 1) = 2 then 1" : "") . "
									when " . (count($med_personal_list) > 0 ? 1 : 0) . " = 1 then 1
									when " . ($isMedStatUser || $isPolkaRegistrator || $isARMLVN ? 1 : 0) . " = 1 then 1
									else 0
								end = 1
								" . (!$isPolkaRegistrator && !$isMedStatUser && !$isARMLVN && count($med_personal_list)>0 ? "and (ESB.MedPersonal_id is null or ESB.MedPersonal_id in (".implode(',',$med_personal_list).") )" : "") . "
							) or (
								$parentAccessType
							) then 'edit' else 'view' end as \"accessType\",
							ESB.EvnStickBase_id as \"EvnStick_id\",
							ESB.EvnStickBase_mid as \"EvnStick_mid\",
							ESB.EvnStickBase_pid as \"EvnStick_pid\",
							:EvnStick_pid as \"Evn_pid\",
							ESB.Person_id as \"Person_id\",
							ESB.PersonEvn_id as \"PersonEvn_id\",
							ESB.Server_id as \"Server_id\",
							 3 as \"evnStickType\", -- Вид док-та (код)
							'Справка учащегося' as \"StickType_Name\", -- Вид док-та (наименование)
							to_char(ESB.EvnStickBase_setDT, 'dd.mm.yyyy') as \"EvnStick_setDate\", -- Дата выдачи
							case when SWT.StickWorkType_Name is null then '' else '/ ' || RTRIM(SWT.StickWorkType_Name) end as \"StickWorkType_Name\",
							case
								when EC.EvnClass_SysNick = 'EvnStickStudent' then ''
								when ESB.EvnStickBase_mid = :EvnStick_pid then '/ Текущий'
								when EvnPL.EvnPL_id is not null then '/ ТАП'
								when EvnPLStom.EvnPLStom_id is not null then '/ Стом. ТАП'
								when EvnPS.EvnPS_id is not null then '/ КВС'
								else ''
							end as \"EvnStick_ParentTypeName\", -- тип родительского документа
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
								when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
								when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
								else ''
							end as \"EvnStick_ParentNum\", -- номер родительского документа
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then to_char(EvnPL.EvnPL_setDT, 'dd.mm.yyyy')
								when EvnPLStom.EvnPLStom_id is not null then to_char(EvnPLStom.EvnPLStom_setDT, 'dd.mm.yyyy')
								when EvnPS.EvnPS_id is not null then to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy')
								else ''
							end as \"EvnStick_ParentDate\", -- дата родительского документа
							case when SO.StickOrder_Name is null then '' else '/ ' || RTRIM(SO.StickOrder_Name) || ' /' end as \"StickOrder_Name\",
							RTRIM(coalesce(ESB.EvnStickBase_Ser, '')) as \"EvnStick_Ser\",
							RTRIM(coalesce(ESB.EvnStickBase_Num, '')) as \"EvnStick_Num\",
							case
								when SLT.StickLeaveType_id is null then '/ ЛВН открыт'
								else null
							end as \"StickLeaveType_OpenName\",
							SFT.StickFSSType_Name as \"StickFSSType_Name\",
							SLT.StickLeaveType_id as \"StickLeaveType_id\",
							SLT.StickLeaveType_Code as \"StickLeaveType_Code\",
							SLT.StickLeaveType_Name as \"StickLeaveType_Name\",
							to_char(ESB.EvnStickBase_disDT, 'dd.mm.yyyy') as \"EvnStick_disDate\",
							ps.Person_Surname as \"Person_Surname\",
							ps.Person_Firname as \"Person_Firname\",
							ps.Person_Secname as \"Person_Secname\",
							ESB.EvnStickBase_IsDelQueue as \"EvnStickBase_IsDelQueue\"
						from v_EvnStickBase ESB
							inner join EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
							inner join v_PersonState ps on ps.Person_id = ESB.Person_id
							-- ТАП/КВС
							left join v_EvnPL EvnPL on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
							left join v_EvnPLStom EvnPLStom on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
							left join v_EvnPS EvnPS on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
							-- end ТАП/КВС
							left join StickOrder SO on SO.StickOrder_id = ESB.StickOrder_id
							left join StickWorkType SWT on SWT.StickWorkType_id = ESB.StickWorkType_id
							left join v_EvnStick ES on ES.EvnStick_id = ESB.EvnStickBase_id
							left join v_StickLeaveType SLT on SLT.StickLeaveType_id = ES.StickLeaveType_id
							left join v_StickFSSType SFT on SFT.StickFSSType_id = ESB.StickFSSType_id
							left join lateral(
								select RegistryESDataStatus_id
								from v_RegistryESData
								where Evn_id = ESB.EvnStickBase_id
								order by case when RegistryESDataStatus_id = 2 then 0 else 1 end
								limit 1
							) as RESD on true
						where
							ESB.EvnStickBase_id = :EvnStickBase_id
					";
				} else {
					$this->load->model('Stick_model');
					$accessType = $this->Stick_model->getEvnStickAccessType($data);

					$query = "
						select
							{$accessType}
							ESB.EvnStickBase_id as \"EvnStick_id\",
							ESB.EvnStickBase_mid as \"EvnStick_mid\",
							ESB.EvnStickBase_pid as \"EvnStick_pid\",
							:EvnStick_pid as \"Evn_pid\",
							ESB.Person_id as \"Person_id\",
							ESB.PersonEvn_id as \"PersonEvn_id\",
							ESB.Server_id as \"Server_id\",
							case
								when EC.EvnClass_SysNick = 'EvnStick' then 1
								when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
								else 0
							end as \"evnStickType\",
							case
								when EC.EvnClass_SysNick = 'EvnStick' then 'ЛВН'
								when EC.EvnClass_SysNick = 'EvnStickStudent' then 'Справка учащегося'
								else ''
							end as \"StickType_Name\",
							to_char(EBWR_d.evnStickWorkRelease_begDT, 'dd.mm.yyyy') as \"EvnStick_setDate\",
							case when SWT.StickWorkType_Name is null then '' else '/ ' || RTRIM(SWT.StickWorkType_Name) end as \"StickWorkType_Name\",
							case
								when EC.EvnClass_SysNick = 'EvnStickStudent' then ''
								when ESB.EvnStickBase_mid = :EvnStick_pid then '/ Текущий'
								when EvnPL.EvnPL_id is not null then '/ ТАП'
								when EvnPLStom.EvnPLStom_id is not null then '/ Стом. ТАП'
								when EvnPS.EvnPS_id is not null then '/ КВС'
								else ''
							end as \"EvnStick_ParentTypeName\",
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then EvnPL.EvnPL_NumCard
								when EvnPLStom.EvnPLStom_id is not null then EvnPLStom.EvnPLStom_NumCard
								when EvnPS.EvnPS_id is not null then EvnPS.EvnPS_NumCard
								else ''
							end as \"EvnStick_ParentNum\",
							case
								when ESB.EvnStickBase_mid = :EvnStick_pid then ''
								when EvnPL.EvnPL_id is not null then to_char(EvnPL.EvnPL_setDT, 'dd.mm.yyyy')
								when EvnPLStom.EvnPLStom_id is not null then to_char(EvnPLStom.EvnPLStom_setDT, 'dd.mm.yyyy')
								when EvnPS.EvnPS_id is not null then to_char(EvnPS.EvnPS_setDT, 'dd.mm.yyyy')
								else ''
							end as \"EvnStick_ParentDate\",
							case when SO.StickOrder_Name is null then '' else '/ ' || RTRIM(SO.StickOrder_Name) || ' /' end as \"StickOrder_Name\",
							RTRIM(coalesce(ESB.EvnStickBase_Ser, '')) as \"EvnStick_Ser\",
							RTRIM(coalesce(ESB.EvnStickBase_Num, '')) as \"EvnStick_Num\",
							case
								when SLT.StickLeaveType_id is null then '/ ЛВН открыт'
								else null
							end as \"StickLeaveType_OpenName\",
							case
								when RESS.RegistryESStorage_id is not null or ESB.EvnStickBase_IsFSS = 2 then 1
								else 0
							end as \"EvnStick_isELN\",
							SFT.StickFSSType_Name as \"StickFSSType_Name\",
							coalesce(SFD.StickFSSData_id, 0) as \"requestExist\",
							SLT.StickLeaveType_id as \"StickLeaveType_id\",
							SLT.StickLeaveType_Code as \"StickLeaveType_Code\",
							SLT.StickLeaveType_Name as \"StickLeaveType_Name\",
							to_char(ESB.EvnStickBase_disDT, 'dd.mm.yyyy') as \"EvnStick_disDate\",
							ps.Person_Surname as \"Person_Surname\",
							ps.Person_Firname as \"Person_Firname\",
							ps.Person_Secname as \"Person_Secname\",
							ESB.EvnStickBase_IsDelQueue as \"EvnStickBase_IsDelQueue\"
						from v_EvnStickBase ESB
							inner join EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
							inner join v_PersonState ps on ps.Person_id = ESB.Person_id
							left join v_MedStaffFact MSF on MSF.MedStaffFact_id = ESB.MedStaffFact_id
							-- ТАП/КВС
							left join v_EvnPL EvnPL on ESB.EvnStickBase_mid = EvnPL.EvnPL_id
							left join v_EvnPLStom EvnPLStom on ESB.EvnStickBase_mid = EvnPLStom.EvnPLStom_id
							left join v_EvnPS EvnPS on ESB.EvnStickBase_mid = EvnPS.EvnPS_id
							-- end ТАП/КВС
							left join StickOrder SO on SO.StickOrder_id = ESB.StickOrder_id
							left join StickWorkType SWT on SWT.StickWorkType_id = ESB.StickWorkType_id
							left join v_EvnStick ES on ES.EvnStick_id = ESB.EvnStickBase_id
							left join v_StickLeaveType SLT on SLT.StickLeaveType_id = ES.StickLeaveType_id
							left join v_StickFSSType SFT on SFT.StickFSSType_id = ESB.StickFSSType_id
							left join lateral (
								select StickFSSData_id
								from v_StickFSSData
								where
									StickFSSData_StickNum = ESB.EvnStickBase_Num
									and StickFSSDataStatus_id not in (3, 4, 5)
								limit 1
							) as SFD on true
							left join v_RegistryESStorage RESS on RESS.EvnStickBase_id = ESB.EvnStickBase_id
							left join lateral(
								select RegistryESDataStatus_id
								from v_RegistryESData
								where Evn_id = ESB.EvnStickBase_id
								order by case when RegistryESDataStatus_id = 2 then 0 else 1 end
								limit 1
							) as RESD on true
							left join lateral (
								select Org_id
								from v_EvnStickWorkRelease ESWR
								where ESB.EvnStickBase_id = ESWR.EvnStickBase_id and ESWR.Org_id = :Org_id
								limit 1
							) as ESWR on true
							left join lateral(
							select evnStickWorkRelease_begDT
								from v_EvnStickWorkRelease ESWR
								where ESB.EvnStickBase_id = ESWR.EvnStickBase_id
								limit 1
							) as EBWR_d on true
						where
							ESB.EvnStickBase_id = :EvnStickBase_id
					";
				}

				$result_evnstick = $this->db->query($query, $queryParams);
				if (is_object($result_evnstick)) {
					$resp_evnstick = $result_evnstick->result('array');
					if (count($resp_evnstick) > 0) {
						$resp_evnstick[0]['evnStickType'] = intval($resp_evnstick[0]['evnStickType']);
						$EvnStickArray[] = $resp_evnstick[0];
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
					select EvnStickbase_id from v_EvnStickBase where EvnStickBase_mid = :EvnStick_pid
					union all
					select Evn_lid from EvnLink where Evn_id =  :EvnStick_pid
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
			select EvnClass_SysNick from v_Evn where Evn_id = :Evn_id limit 1
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
				ESB.EvnStickBase_id as \"EvnStickBase_id\",
				EC.EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_EvnStickBase ESB
				inner join EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
			where
				EvnStickBase_mid = :EvnStick_pid

			union all

			select
				ESB.EvnStickBase_id as \"EvnStickBase_id\",
				EC.EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				EvnLink EL
				inner join v_EvnStickBase ESB on ESB.EvnStickBase_id = EL.Evn_lid
				inner join EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
			where
				EL.Evn_id = :EvnStick_pid

			order by
				\"EvnStickBase_id\" desc
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
						ESB.EvnStickBase_id as \"EvnStick_id\",
						ESB.EvnStickBase_mid as \"EvnStick_mid\",
						ESB.EvnStickBase_pid as \"EvnStick_pid\",
						:EvnStick_pid as \"Evn_pid\",
						ESB.Person_id as \"Person_id\",
						ESB.PersonEvn_id as \"PersonEvn_id\",
						ESB.Server_id as \"Server_id\",
						RTRIM(coalesce(ESB.EvnStickBase_Ser, '')) as \"EvnStick_Ser\",
						RTRIM(coalesce(ESB.EvnStickBase_Num, '')) as \"EvnStick_Num\",
						to_char(ESB.EvnStickBase_setDate, 'DD.MM.YYYY') as \"EvnStick_setDate\",
						to_char(ESB.EvnStickBase_disDate, 'DD.MM.YYYY') as \"EvnStick_disDate\",
						SWT.StickWorkType_Name as \"StickWorkType_Name\",
						ESB.EvnStickBase_IsDelQueue as \"EvnStickBase_IsDelQueue\",
						coalesce(ESWR.TotalDaysCount, 0) as \"TotalDaysCount\",
						(
							to_char(ESWR.minEvnStickWorkRelease_begDT, 'DD.MM.YYYY') || ' - ' || 
							to_char(ESWR.maxEvnStickWorkRelease_endDT, 'DD.MM.YYYY')
						) as \"WorkReleaseRange\",
						(
							lower(RLT.RelatedLinkType_Name)
							|| ' ' || rtrim(PS.Person_Surname)
							|| ' ' || rtrim(PS.Person_Firname)
							|| coalesce(' ' || rtrim(PS.Person_Secname), '')
						) as \"RelatedPerson\"
					from v_EvnStickBase ESB
						inner join EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
						inner join v_PersonState PS on PS.Person_id = ESB.Person_id
						left join StickWorkType SWT on SWT.StickWorkType_id = ESB.StickWorkType_id
						left join v_Evn E on E.Evn_id = :EvnStick_pid
						left join lateral (
							select
							min(ESWR.EvnStickWorkRelease_begDT) as minEvnStickWorkRelease_begDT,
							max(ESWR.EvnStickWorkRelease_endDT) as maxEvnStickWorkRelease_endDT,
							sum(datediff('day', ESWR.EvnStickWorkRelease_begDT, ESWR.EvnStickWorkRelease_endDT)) + 1 as TotalDaysCount
							from v_EvnStickWorkRelease ESWR
							where ESWR.EvnStickBase_id = ESB.EvnStickBase_id
							limit 1
						) ESWR on true
						left join lateral (
							select ESCP.RelatedLinkType_id
							from v_EvnStickCarePerson ESCP
							where ESCP.Evn_id = ESB.EvnStickBase_id
							and ESCP.Person_id = E.Person_id
							limit 1
						) ESCP on true
						left join v_RelatedLinkType RLT on RLT.RelatedLinkType_id = ESCP.RelatedLinkType_id
					where
						ESB.EvnStickBase_id = :EvnStickBase_id
				";

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
					select EvnStickbase_id from v_EvnStickBase where EvnStickBase_mid = :EvnStick_pid
					union all
					select Evn_lid from EvnLink where Evn_id =  :EvnStick_pid
				)';
			$queryParams['EvnStick_pid'] = $data['EvnStick_pid'];
		}

		$lpuFilter = getAccessRightsLpuFilter('ESB.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}

		$query = "
			select
				ESB.EvnStickBase_id as \"EvnStick_id\",
				ESB.EvnStickBase_mid as \"EvnStick_mid\",
				case
					when EC.EvnClass_SysNick = 'EvnStickDop' then ESBD.EvnStickBase_pid
					else ESB.EvnStickBase_pid
				end as \"EvnStick_pid\",
				ESB.EvnStickBase_id as \"EvnStickOpenInfo_id\",
				ESB.Person_id as \"Person_id\",
				ESB.PersonEvn_id as \"PersonEvn_id\",
				ESB.Server_id as \"Server_id\",
				ESB.EvnStickBase_Ser as \"EvnStick_Ser\",
				ESB.EvnStickBase_Num as \"EvnStick_Num\",
				to_char(ESB.EvnStickBase_setDT, 'dd.mm.yyyy') as \"EvnStick_setDate\",
				SWT.StickWorkType_Name as \"StickWorkType_Name\",
				rtrim(coalesce(SO.StickOrder_Name, '')) as \"StickOrder_Name\",
				case
					when EC.EvnClass_SysNick = 'EvnStick' then 1
					when EC.EvnClass_SysNick = 'EvnStickDop' then 2
					when EC.EvnClass_SysNick = 'EvnStickStudent' then 3
					else 0
				end as \"evnStickType\"
			from v_EvnStickBase ESB
				left join v_EvnClass EC on EC.EvnClass_id = ESB.EvnClass_id
				left join v_StickWorkType SWT on SWT.StickWorkType_id = ESB.StickWorkType_id
				left join v_EvnStickBase ESBD on ESBD.EvnStickBase_id = ESB.EvnStickBase_pid
					and EC.EvnClass_SysNick = 'EvnStickDop'
				left join v_StickOrder SO on SO.StickOrder_id = coalesce(ESBD.StickOrder_id,ESB.StickOrder_id)
			where
				ESB.Person_id = :Person_id
				and coalesce(ESB.EvnStickBase_disDT,ESBD.EvnStickBase_disDT) is null
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
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnStick_del(
				EvnStick_id := :EvnStick_id,
				pmUser_id := :pmUser_id
			)
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
				ES.EvnStick_id as \"EvnStick_id\",
				ES.EvnStick_pid as \"EvnStick_pid\",
				ES.Person_id as \"Person_id\",
				ES.PersonEvn_id as \"PersonEvn_id\",
				ES.Server_id as \"Server_id\",
				ES.StickType_id as \"StickType_id\",
				ES.StickCause_id as \"StickCause_id\",
				ES.Sex_id as \"Sex_id\",
				to_char(ES.EvnStick_begDate, 'dd.mm.yyyy') as \"EvnStick_begDate\",
				to_char(ES.EvnStick_endDate, 'dd.mm.yyyy') as \"EvnStick_endDate\",
				RTRIM(ST.StickType_Name) as \"StickType_Name\",
				RTRIM(ES.EvnStick_Ser) as \"EvnStick_Ser\",
				RTRIM(ES.EvnStick_Num) as \"EvnStick_Num\",
				ES.EvnStick_Age as \"EvnStick_Age\"
			from v_EvnStick ES
				left join StickType ST on ST.StickType_id = ES.StickType_id
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
			select
				EvnStick_id as \"EvnStick_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				EvnStick_id := :EvnStick_id,
				params := :params,
				pmUser_id := :pmUser_id
			)
		";
		
		$jsonParams = array(
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
		);

		$queryParams = array(
			'EvnStick_id' => $data['EvnStick_id'],
			'params' => json_encode(array_change_key_case($jsonParams, CASE_LOWER)),
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