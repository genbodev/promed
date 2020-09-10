<?php
require_once('MorbusHepatitisCureEffMonitoring_model.php');
/**
* MorbusHepatitisCure_model - модель, для работы с таблицей MorbusHepatitisCure
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

class MorbusHepatitisCure_model extends SwPgModel {

	/**
	 *	Method description
	 */
	function __construct()
	{
		parent::__construct();
        $this->MorbusHepatitisCureEffMonitoring = new MorbusHepatitisCureEffMonitoring_model();
	}
	
	/**
	 *	Method description
	 */
	function load($data)
	{
		$query = "
			select 
				MorbusHepatitisCure_id as \"MorbusHepatitisCure_id\",
				MorbusHepatitis_id as \"MorbusHepatitis_id\",
				Evn_id as \"EvnSection_id\",
				to_char(cast(MorbusHepatitisCure_begDT as timestamp(3)), 'dd.mm.yyyy') as \"MorbusHepatitisCure_begDT\",
				to_char(cast(MorbusHepatitisCure_endDT as timestamp(3)), 'dd.mm.yyyy') as \"MorbusHepatitisCure_endDT\",
				Drug_id as \"Drug_id\",
				HepatitisResultClass_id as \"HepatitisResultClass_id\",
				HepatitisSideEffectType_id as \"HepatitisSideEffectType_id\"
			from
				v_MorbusHepatitisCure
			where
				MorbusHepatitisCure_id = ?
		";
		$res = $this->db->query($query, array($data['MorbusHepatitisCure_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;		
	}
	
	/**
	 *	Method description
	 */
	function save($data)
	{

		if ( !isset($data['MorbusHepatitisCure_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			select 
				MorbusHepatitisCure_id as \"MorbusHepatitisCure_id\", 
				Error_Code as \"Error_Code\", 
				Error_Message as \"Error_Msg\"
			from p_MorbusHepatitisCure_" . $procedure_action . " (
				MorbusHepatitisCure_id := :MorbusHepatitisCure_id,
				MorbusHepatitis_id := :MorbusHepatitis_id,
				MorbusHepatitisCure_begDT := :MorbusHepatitisCure_begDT,
				MorbusHepatitisCure_endDT := :MorbusHepatitisCure_endDT,
				Drug_id := :Drug_id,
				HepatitisResultClass_id := :HepatitisResultClass_id,
				HepatitisSideEffectType_id := :HepatitisSideEffectType_id,
				Evn_id := :Evn_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$queryParams = array(
			'MorbusHepatitisCure_id' => $data['MorbusHepatitisCure_id'],
			'MorbusHepatitis_id' => $data['MorbusHepatitis_id'],
			'MorbusHepatitisCure_begDT' => $data['MorbusHepatitisCure_begDT'],
			'MorbusHepatitisCure_endDT' => $data['MorbusHepatitisCure_endDT'],
			'Drug_id' => $data['Drug_id'],
			'HepatitisResultClass_id' => $data['HepatitisResultClass_id'],
			'HepatitisSideEffectType_id' => $data['HepatitisSideEffectType_id'],
			'Evn_id' => $data['EvnSection_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			// Сохранение мониторинга эффективности лечения
			$response = $res->result('array');
			$data['MorbusHepatitisCure_id'] = $response[0]['MorbusHepatitisCure_id'];
			if (!empty($data['MorbusHepatitisCure_id'])) {
				$items_list = json_decode(toUTF($data['MorbusHepatitisCureEffMonitoring']), true);
				if (count($items_list)) {
					foreach ( $items_list as $item ) {
						switch ( $item['RecordStatus_Code'] ) {
							case 0:
							case 2:
								$this->MorbusHepatitisCureEffMonitoring->save(array_merge($item, $data));
							break;

							case 3:
								$this->MorbusHepatitisCureEffMonitoring->delete($item);
							break;
						}
					}
				}				
			}
			return $response;
		}
		else {
			return false;
		}
	}
	
	
}