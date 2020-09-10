<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PrehospWaifInspection_model - модель осмотров беспризорных
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Пермяков Александр
* @version      июнь 2011 года
*/

class PrehospWaifInspection_model extends SwPgModel {
	var $scheme = "dbo";

	function __construct()
	{
		parent::__construct();
	}

	/**
	*  Читает часть данных (используя пейджинг)
	*/
	function loadRecordGrid($data)
	{
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}

		$params = array(
			'EvnPS_id' => $data['EvnPS_id']
		);
		
		$query = "				
			Select
				-- select
				PWI.PrehospWaifInspection_id as \"PrehospWaifInspection_id\"
				,PWI.EvnPS_id as \"EvnPS_id\"
				,PWI.LpuSection_id as \"LpuSection_id\"
				,PWI.MedStaffFact_id as \"MedStaffFact_id\"
				,PWI.Diag_id as \"Diag_id\"
				,to_char(PWI.PrehospWaifInspection_SetDT,'dd.mm.yyyy') ||' '|| to_char(PWI.PrehospWaifInspection_SetDT,'hh24:mi') as \"PrehospWaifInspection_SetDT\"
				,LS.LpuSection_Name as \"LpuSection_Name\"
				,MP.Person_Fio as \"MedPersonal_Fio\"
				,D.Diag_Name as \"Diag_Name\"
				-- end select
			from
				-- from
				v_PrehospWaifInspection PWI
				left join v_LpuSection LS on PWI.LpuSection_id = LS.LpuSection_id
				left join v_Diag D on PWI.Diag_id = D.Diag_id
				left join v_MedStaffFact MP on PWI.MedStaffFact_id = MP.MedStaffFact_id
				-- end from
			where
				-- where
				PWI.EvnPS_id = :EvnPS_id
				-- end where
			order by
				-- order by
				PWI.PrehospWaifInspection_SetDT
				-- end order by
		";
		/*
		echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		return false;
	}
	
	/**
	*  Читает одну строку для формы редактирования
	*/
	function getRecord($data)
	{
		$params = array(
			'PrehospWaifInspection_id' => $data['PrehospWaifInspection_id']
		);
		$query = "
			Select
				PWI.PrehospWaifInspection_id as \"PrehospWaifInspection_id\"
				,PWI.EvnPS_id as \"EvnPS_id\"
				,PWI.LpuSection_id as \"LpuSection_id\"
				,PWI.MedStaffFact_id as \"MedStaffFact_id\"
				,PWI.Diag_id as \"Diag_id\"
				,to_char(PWI.PrehospWaifInspection_SetDT,'dd.mm.yyyy') as \"PrehospWaifInspection_SetDT\"
			from
				v_PrehospWaifInspection PWI
			where
				PrehospWaifInspection_id = :PrehospWaifInspection_id
            limit 1
		";
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	*  Записывает одну строку
	*/
	function saveRecord($data)
	{
		if ($data['PrehospWaifInspection_id'] > 0)
		{
			$proc = 'p_PrehospWaifInspection_upd';
		}
		else
		{
			$proc = 'p_PrehospWaifInspection_ins';
			$data['PrehospWaifInspection_id'] = null;
		}

		$params = array
		(
			'PrehospWaifInspection_id' => $data['PrehospWaifInspection_id'],
			'EvnPS_id' => $data['EvnPS_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'Diag_id' => $data['Diag_id'],
			'PrehospWaifInspection_SetDT' => $data['PrehospWaifInspection_SetDT'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "
			select
			    PrehospWaifInspection_id as \"PrehospWaifInspection_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from " .$proc. " (
				PrehospWaifInspection_id := :PrehospWaifInspection_id, 
				EvnPS_id := :EvnPS_id,
				LpuSection_id := :LpuSection_id,
				MedStaffFact_id := :MedStaffFact_id,
				PrehospWaifInspection_SetDT := :PrehospWaifInspection_SetDT,
				Diag_id := :Diag_id,
				pmUser_id := :pmUser_id
				)
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}

	}
}