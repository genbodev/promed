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

class MorbusHepatitisQueue_model extends CI_Model {
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
				MorbusHepatitisQueue_id,
				MorbusHepatitis_id,
				HepatitisQueueType_id,
				MorbusHepatitisQueue_Num,
				MorbusHepatitisQueue_IsCure
			from
				v_MorbusHepatitisQueue with(nolock)
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
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :MorbusHepatitisQueue_id;
			exec p_MorbusHepatitisQueue_" . $procedure_action . "
				@MorbusHepatitisQueue_id = @Res output,
				@MorbusHepatitis_id = :MorbusHepatitis_id,
				@HepatitisQueueType_id = :HepatitisQueueType_id,
				@MorbusHepatitisQueue_Num = :MorbusHepatitisQueue_Num,
				@MorbusHepatitisQueue_IsCure = :MorbusHepatitisQueue_IsCure,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MorbusHepatitisQueue_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
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
		$query = "SELECT MAX(MorbusHepatitisQueue_Num) + 1 as MorbusHepatitisQueue_Num FROM v_MorbusHepatitisQueue with(nolock) WHERE HepatitisQueueType_id = :HepatitisQueueType_id ";
		$result = $this->db->query($query, array('HepatitisQueueType_id' => $data['HepatitisQueueType_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
		
	}
	
	
}