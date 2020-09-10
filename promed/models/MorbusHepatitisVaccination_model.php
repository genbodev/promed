<?php
/**
* MorbusHepatitisVaccination_model - модель, для работы с таблицей MorbusHepatitisVaccination
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

class MorbusHepatitisVaccination_model extends CI_Model {
	/**
	 * MorbusHepatitisVaccination_model constructor.
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
				MorbusHepatitisVaccination_id,
				MorbusHepatitis_id,
				Evn_id as EvnSection_id,
				convert(varchar,cast(MorbusHepatitisVaccination_setDT as datetime),104) as MorbusHepatitisVaccination_setDT,
				Drug_id
			from
				v_MorbusHepatitisVaccination with(nolock)
			where
				MorbusHepatitisVaccination_id = ?
		";
		$res = $this->db->query($query, array($data['MorbusHepatitisVaccination_id']));
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

		if ( !isset($data['MorbusHepatitisVaccination_id']) ) {
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
			set @Res = :MorbusHepatitisVaccination_id;
			exec p_MorbusHepatitisVaccination_" . $procedure_action . "
				@MorbusHepatitisVaccination_id = @Res output,
				@MorbusHepatitis_id = :MorbusHepatitis_id,
				@MorbusHepatitisVaccination_setDT = :MorbusHepatitisVaccination_setDT,
				@Drug_id = :Drug_id,
				@Evn_id = :Evn_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MorbusHepatitisVaccination_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$queryParams = array(
			'MorbusHepatitisVaccination_id' => $data['MorbusHepatitisVaccination_id'],
			'MorbusHepatitis_id' => $data['MorbusHepatitis_id'],
			'MorbusHepatitisVaccination_setDT' => $data['MorbusHepatitisVaccination_setDT'],
			'Drug_id' => $data['Drug_id'],
			'Evn_id' => $data['EvnSection_id'],
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
	
	
}