<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PregnancyEvnVizitPL_model - модель для работы со сведениями о беременности, связанными с посещением
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.10.2017
 */

class PregnancyEvnVizitPL_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение сведений о беременности, связанных с посещением
	 */
	function getPregnancyEvnVizitPL($EvnVizitPL_id) {
		$params = array('EvnVizitPL_id' => $EvnVizitPL_id);
		$query = "
			select top 1 * from v_PregnancyEvnVizitPL with(nolock) where EvnVizitPL_id = :EvnVizitPL_id
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}
		return count($resp) > 0 ? $resp[0] : null;
	}

	/**
	 * Сохранение сведений о беременности, связанных с посещением
	 */
	function savePregnancyEvnVizitPL($data) {
		$params = array(
			'PregnancyEvnVizitPL_id' => !empty($data['PregnancyEvnVizitPL_id'])?$data['PregnancyEvnVizitPL_id']:null,
			'PregnancyEvnVizitPL_Period' => !empty($data['PregnancyEvnVizitPL_Period'])?$data['PregnancyEvnVizitPL_Period']:null,
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($params['PregnancyEvnVizitPL_id'])) {
			$procedure = 'p_PregnancyEvnVizitPL_ins';
		} else {
			$procedure = 'p_PregnancyEvnVizitPL_upd';
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :PregnancyEvnVizitPL_id;
			exec {$procedure}
				@PregnancyEvnVizitPL_id = @Res output,
				@PregnancyEvnVizitPL_Period = :PregnancyEvnVizitPL_Period,
				@EvnVizitPL_id = :EvnVizitPL_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as PregnancyEvnVizitPL_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
	function savePregnancyEvnVizitPLData($data) {
		$params = array(
			'EvnVizitPL_id' => $data['EvnVizitPL_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$record = $this->getPregnancyEvnVizitPL($params['EvnVizitPL_id']);
		if ($record === false) {
			return $this->createError('','Ошибка при получении сведений о беременности, связанных с посещением');
		}
		if ($record) {
			$params['PregnancyEvnVizitPL_id'] = $record['PregnancyEvnVizitPL_id'];
		}

		$fields = array('PregnancyEvnVizitPL_Period');

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

		$response = array(array('PregnancyEvnVizitPL_id' => null, 'Error_Msg' => null, 'Error_Code' => null));
		if ($needSave) {
			$response = $this->savePregnancyEvnVizitPL($params);
			if (!$this->isSuccessful($response)) {
				return $response;
			}
		}

		return $response;
	}
}