<?php

/**
 * Class Lis_Evn_model
 */
class Lis_Evn_model extends SwPgModel {
	function getPersonEvnClassList($data) {
		$filterList = array();
		$params = array();

		if (!empty($data['person_in'])) {
			$filterList[] = " Person_id in ({$data['person_in']}) ";
		} else {
			$filterList[] = " Person_id = :Person_id ";
			$params['Person_id'] = $data['Person_id'];
		}

		if (empty($data['ignoreFilterByEvnPid'])) {
			if (!empty($data['Evn_pid'])) {
				$filterList[] = "Evn_pid = :Evn_pid";
				$params['Evn_pid'] = $data['Evn_pid'];
			} else {
				$filterList[] = "(Evn_pid is null or EvnClass_SysNick in ('EvnPrescrMse','EvnUslugaTelemed'))";
			}
		}

		$query = "
			select distinct
				EvnClass_SysNick as \"EvnClass_SysNick\",
				Person_id as \"Person_id\"
			from v_Evn
			where " . implode(' and ', $filterList) . "
		";

		return $this->queryResult($query, $params);
	}

	/**
	 *	Получение списка связанных событий для Evn_id
	 */
	function getRelatedEvnList($data) {
		$query = "
			select
				 e.Evn_id as \"Evn_id\"
				,e.Evn_pid as \"Evn_pid\"
				,e.Evn_rid as \"Evn_rid\"
				,to_char(e.Evn_setDT, 'dd.mm.yyyy') as \"Evn_setDT\"
				,e.EvnClass_SysNick as \"EvnClass_SysNick\"
				,coalesce(e.Evn_IsSigned, 1) as \"Evn_IsSigned\"
				,e.Person_id as \"Person_id\"
				,e.Lpu_id as \"Lpu_id\"
			from v_Evn e
			where
				e.Evn_rid = :Evn_id
				or e.Evn_pid = :Evn_id
				or e.Evn_id = :Evn_id
		";

		$queryParams = array(
			'Evn_id' => $data['Evn_id']
		);

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение системного наименования типа события
	*/
	function getEvnClassSysNick($data)
	{
		return $this->getFirstRowFromQuery("
			select
				EvnClass_SysNick as \"EvnClass_SysNick\"
			from v_Evn
			where Evn_id = :Evn_id
		", $data, true);
	}
	
	/**
	 * Изменение статуса события
	 * @param array $data
	 * @return array|false
	 */
	function updateEvnStatus($data) {
		$params = [
			'Evn_id' => $data['Evn_id'],
			'EvnStatus_id' => !empty($data['EvnStatus_id']) ? $data['EvnStatus_id'] : null,
			'EvnStatus_SysNick' => !empty($data['EvnStatus_SysNick']) ? $data['EvnStatus_SysNick'] : null,
			'EvnClass_id' => !empty($data['EvnClass_id']) ? $data['EvnClass_id'] : null,
			'EvnClass_SysNick' => !empty($data['EvnClass_SysNick']) ? $data['EvnClass_SysNick'] : null,
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id']) ? $data['EvnStatusCause_id'] : null,
			'EvnStatusHistory_Cause' => !empty($data['EvnStatusHistory_Cause']) ? $data['EvnStatusHistory_Cause'] : null,
			'MedServiceMedPersonal_id' => !empty($data['MedServiceMedPersonal_id']) ? $data['MedServiceMedPersonal_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		];
		$query = "
			select
				error_message as \"Error_Msg\",
				error_code as \"Error_Code\"
			from p_evn_setstatus(
				Evn_id := :Evn_id,
				EvnStatus_id := :EvnStatus_id,
				EvnStatus_SysNick := :EvnStatus_SysNick,
				EvnClass_id := :EvnClass_id,
				EvnClass_SysNick := :EvnClass_SysNick,
				EvnStatusCause_id := :EvnStatusCause_id,
				EvnStatusHistory_Cause := :EvnStatusHistory_Cause,
				MedServiceMedPersonal_id := :MedServiceMedPersonal_id,
				pmUser_id := :pmUser_id
			)	
		";
		return $this->queryResult($query, $params);
	}
}
