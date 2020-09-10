<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PregnancyEvnVizitPL_model - модель для работы со сведениями о беременности, связанными с посещением
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.10.2017
 */

class PregnancyEvnVizitPL_model extends swPgModel
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение сведений о беременности, связанных с посещением
	 * @param $EvnVizitPL_id
	 * @return bool|mixed|null
	 */
	function getPregnancyEvnVizitPL($EvnVizitPL_id)
	{
		$params = ["EvnVizitPL_id" => $EvnVizitPL_id];
		$query = "
			select
				pregnancyevnvizitpl_id as \"PregnancyEvnVizitPL_id\",
				evnvizitpl_id as \"EvnVizitPL_id\",
				pregnancyevnvizitpl_period as \"PregnancyEvnVizitPL_Period\",
				pmuser_insid as \"pmUser_insID\",
				pmuser_updid as \"pmUser_updID\"
			from v_PregnancyEvnVizitPL
			where EvnVizitPL_id = :EvnVizitPL_id
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}
		return count($resp) > 0 ? $resp[0] : null;
	}

	/**
	 * Сохранение сведений о беременности, связанных с посещением
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function savePregnancyEvnVizitPL($data)
	{
		$params = [
			"PregnancyEvnVizitPL_id" => !empty($data["PregnancyEvnVizitPL_id"]) ? $data["PregnancyEvnVizitPL_id"] : null,
			"PregnancyEvnVizitPL_Period" => !empty($data["PregnancyEvnVizitPL_Period"]) ? $data["PregnancyEvnVizitPL_Period"] : null,
			"EvnVizitPL_id" => $data["EvnVizitPL_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$procedure = (empty($params["PregnancyEvnVizitPL_id"])) ? "p_PregnancyEvnVizitPL_ins" : "p_PregnancyEvnVizitPL_upd";
		$selectString = "
		    pregnancyevnvizitpl_id as \"PregnancyEvnVizitPL_id\",
		    error_code as \"Error_Code\",
		    error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    pregnancyevnvizitpl_id := :PregnancyEvnVizitPL_id,
			    evnvizitpl_id := :EvnVizitPL_id,
			    pregnancyevnvizitpl_period := :PregnancyEvnVizitPL_Period,
			    pmuser_id := :pmUser_id
			);
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			throw new Exception("Ошибка при сохранении сведений о беременности");
		}
		return $response;
	}

	/**
	 * Сохранение данных в сведениях о беременности, связанных с КВС
	 * @param $data
	 * @return array|false
	 * @throws Exception
	 */
	function savePregnancyEvnVizitPLData($data)
	{
		$params = [
			"EvnVizitPL_id" => $data["EvnVizitPL_id"],
			"pmUser_id" => $data["pmUser_id"]
		];
		$record = $this->getPregnancyEvnVizitPL($params["EvnVizitPL_id"]);
		if ($record === false) {
			throw new Exception("Ошибка при получении сведений о беременности, связанных с посещением");
		}
		if ($record) {
			$params["PregnancyEvnVizitPL_id"] = $record["PregnancyEvnVizitPL_id"];
		}
		$fields = ["PregnancyEvnVizitPL_Period"];
		$needSave = false;
		foreach ($fields as $field) {
			$oldValue = $record ? $record[$field] : null;
			$newValue = !empty($data[$field]) ? $data[$field] : null;
			if ((!$record && $newValue) || ($record && array_key_exists($field, $data) && $newValue != $oldValue)) {
				$needSave = true;
				$params[$field] = $newValue;
			} else {
				$params[$field] = $oldValue;
			}
		}
		$response = [["PregnancyEvnVizitPL_id" => null, "Error_Msg" => null, "Error_Code" => null]];
		if ($needSave) {
			$response = $this->savePregnancyEvnVizitPL($params);
			if (!$this->isSuccessful($response)) {
				return $response;
			}
		}
		return $response;
	}
}