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

class MorbusHepatitisVaccination_model extends SwPgModel
{

    protected $dateTimeFormat104 = "'dd.mm.yyyy'";

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
				MorbusHepatitisVaccination_id as \"MorbusHepatitisVaccination_id\",
				MorbusHepatitis_id as \"MorbusHepatitis_id\",
				Evn_id as EvnSection_id,
				to_char(cast(MorbusHepatitisVaccination_setDT as timestamp), {$this->dateTimeFormat104}) as \"MorbusHepatitisVaccination_setDT\",
				Drug_id as \"Drug_id\"
			from
				v_MorbusHepatitisVaccination
			where
				MorbusHepatitisVaccination_id = :MorbusHepatitisVaccination_id
		";

		$res = $this->db->query($query, $data);

		if ( !is_object($res) )
            return false;

        return $res->result('array');
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function save($data)
	{

		if ( isset($data['MorbusHepatitisVaccination_id']) ) {
            $procedure_action = "upd";
            $out = "";
		} else {
            $procedure_action = "ins";
            $out = "output";
		}

		$query = "
		    select 
		        MorbusHepatitisVaccination_id as \"MorbusHepatitisVaccination_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from p_MorbusHepatitisVaccination_" . $procedure_action . "
			(
				MorbusHepatitisVaccination_id := :MorbusHepatitisVaccination_id,
				MorbusHepatitis_id := :MorbusHepatitis_id,
				MorbusHepatitisVaccination_setDT := :MorbusHepatitisVaccination_setDT,
				Drug_id := :Drug_id,
				Evn_id := :Evn_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$queryParams = [
			'MorbusHepatitisVaccination_id' => $data['MorbusHepatitisVaccination_id'],
			'MorbusHepatitis_id' => $data['MorbusHepatitis_id'],
			'MorbusHepatitisVaccination_setDT' => $data['MorbusHepatitisVaccination_setDT'],
			'Drug_id' => $data['Drug_id'],
			'Evn_id' => $data['EvnSection_id'],
			'pmUser_id' => $data['pmUser_id']
		];
		
		$res = $this->db->query($query, $queryParams);

		if (!is_object($res) ) {
			return false;
		}

        return $response = $res->result('array');

    }
}