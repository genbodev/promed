<?php
/**
* MorbusHepatitisQueue_model - модель, для работы с таблицей MorbusHepatitisQueue
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Chebukin 
* @version      07.2012
*/

class MorbusHepatitisQueue_model extends SwPgModel {
	/**
	 * MorbusHepatitisQueue_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
   function load($data)
	{
		$query = "
			select
				MorbusHepatitisQueue_id as \"MorbusHepatitisQueue_id\",
				MorbusHepatitis_id as \"MorbusHepatitis_id\",
				HepatitisQueueType_id as \"HepatitisQueueType_id\",
				MorbusHepatitisQueue_Num as \"MorbusHepatitisQueue_Num\",
				MorbusHepatitisQueue_IsCure as \"MorbusHepatitisQueue_IsCure\"
			from
				v_MorbusHepatitisQueue 
			where
				MorbusHepatitisQueue_id = ?
		";
		$res = $this->db->query($query, array($data['MorbusHepatitisQueue_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
   function save($data)
	{

		if ( !isset($data['MorbusHepatitisQueue_id']) ) {
			$procedure_action = "ins";
		}
		else {
			$procedure_action = "upd";
		}

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            MorbusHepatitisQueue_id as \"MorbusHepatitisQueue_id\"
        from p_MorbusHepatitisQueue_{$procedure_action}
            (
  				MorbusHepatitisQueue_id := :MorbusHepatitisQueue_id,
				MorbusHepatitis_id := :MorbusHepatitis_id,
				HepatitisQueueType_id := :HepatitisQueueType_id,
				MorbusHepatitisQueue_Num := :MorbusHepatitisQueue_Num,
				MorbusHepatitisQueue_IsCure := :MorbusHepatitisQueue_IsCure,
				pmUser_id := :pmUser_id
            )";



		$queryParams = array(
			'MorbusHepatitisQueue_id' => $data['MorbusHepatitisQueue_id'],
			'MorbusHepatitis_id' => $data['MorbusHepatitis_id'],
			'HepatitisQueueType_id' => $data['HepatitisQueueType_id'],
			'MorbusHepatitisQueue_Num' => $data['MorbusHepatitisQueue_Num'],
			'MorbusHepatitisQueue_IsCure' => $data['MorbusHepatitisQueue_IsCure'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
  function getQueueNumber($data)
	{
		$query = "SELECT MAX(MorbusHepatitisQueue_Num) + 1 as MorbusHepatitisQueue_Num FROM v_MorbusHepatitisQueue  WHERE HepatitisQueueType_id = :HepatitisQueueType_id ";
		$result = $this->db->query($query, array('HepatitisQueueType_id' => $data['HepatitisQueueType_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}

	}
	
	
}