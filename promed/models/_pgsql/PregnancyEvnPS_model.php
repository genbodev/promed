<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PregnancyEvnPS_model - модель для работы со сведениями о беременности, связанными с КВС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			06.10.2016
 */

class PregnancyEvnPS_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение сведений о беременности, связанных с КВС
	 */
	function getPregnancyEvnPS($EvnPS_id) {
		$params = array('EvnPS_id' => $EvnPS_id);
		$query = "
			select
				PregnancyEvnPS_id as \"PregnancyEvnPS_id\",
				PregnancyEvnPS_Period as \"PregnancyEvnPS_Period\",
				EvnPS_id as \"EvnPS_id\"
			from v_PregnancyEvnPS 
			where EvnPS_id = :EvnPS_id
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}
		return count($resp) > 0 ? $resp[0] : null;
	}

	/**
	 * Сохранение сведений о беременности, связанных с КВС
	 */
	function savePregnancyEvnPS($data) {
		$params = array(
			'PregnancyEvnPS_id' => !empty($data['PregnancyEvnPS_id'])?$data['PregnancyEvnPS_id']:null,
			'PregnancyEvnPS_Period' => !empty($data['PregnancyEvnPS_Period'])?$data['PregnancyEvnPS_Period']:null,
			'EvnPS_id' => $data['EvnPS_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($params['PregnancyEvnPS_id'])) {
			$procedure = 'p_PregnancyEvnPS_ins';
		} else {
			$procedure = 'p_PregnancyEvnPS_upd';
		}

		$query = "
		    select 
		        PregnancyEvnPS_id as \"PregnancyEvnPS_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from {$procedure}
			(
				PregnancyEvnPS_id := :PregnancyEvnPS_id,
				PregnancyEvnPS_Period := :PregnancyEvnPS_Period,
				EvnPS_id := :EvnPS_id,
				pmUser_id := :pmUser_id			
            )	
			";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении сведений о беременности');
		}

		return $response;
	}

	/**
	 * Сохранение данных в сведениях о беременности, связанных с КВС
	 */
	function savePregnancyEvnPSData($data) {
		$params = array(
			'EvnPS_id' => $data['EvnPS_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$record = $this->getPregnancyEvnPS($params['EvnPS_id']);
		if ($record === false) {
			return $this->createError('','Ошибка при получении сведений о беременности, связанных с КВС');
		}
		if ($record) {
			$params['PregnancyEvnPS_id'] = $record['PregnancyEvnPS_id'];
		}

		$fields = array('PregnancyEvnPS_Period');

		$needSave = false;
		foreach($fields as $field) {
			$oldValue = $record?$record[$field]:null;
			$newValue = !empty($data[$field])?$data[$field]:null;
			if ((!$record && $newValue) || ($record && array_key_exists($field, $data) && $newValue != $oldValue)) {
				$needSave = true;
				$params[$field] = $newValue;
			} else {
				$params[$field] = $oldValue;
			}
		}

		$response = array(array('PregnancyEvnPS_id' => null, 'Error_Msg' => null, 'Error_Code' => null));
		if ($needSave) {
			$response = $this->savePregnancyEvnPS($params);
			if (!$this->isSuccessful($response)) {
				return $response;
			}
		}

		return $response;
	}
}