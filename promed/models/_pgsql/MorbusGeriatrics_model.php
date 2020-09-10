<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
* MorbusGeriatrics_model - модель для MorbusGeriatrics
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2018 Swan Ltd.
* @author       Быков Станислав
* @version      12.2018
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
 *
 * @property CI_DB_driver $db
*/
class MorbusGeriatrics_model extends SwPgModel
{
	protected $_MorbusType_id = 100;

	private $dateTimeForm104 = "DD.MM.YYYY";

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return "geriatrics";
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library("swMorbus");
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception("Не удалось определить тип заболевания", 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return "MorbusGeriatrics";
	}

	/**
	 * Создание специфики заболевания
	 *
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 *
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data["MorbusBase_id"]) ||
			empty($data["Person_id"]) ||
			empty($data["Morbus_id"]) ||
			empty($data["Diag_id"]) ||
			empty($data["Morbus_setDT"]) ||
			empty($data["mode"]) ||
			false == in_array($data["mode"], ["onBeforeViewData", "onBeforeSavePersonRegister", "onBeforeSaveEvnNotify"])
		) {
			throw new Exception("Переданы неправильные параметры", 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = [];
		$queryParams["pmUser_id"] = $this->promedUserId;
		$queryParams["Morbus_id"] = $data["Morbus_id"];
		$queryParams["Lpu_id"] = isset($data["Lpu_id"]) ? $data["Lpu_id"] : $this->sessionParams["lpu_id"];
		$whereString = "Morbus_id = :Morbus_id";
		$query = "
			select coalesce({$tableName}_id, 0) as id
			from v_{$tableName}
			where {$whereString}
			limit 1
		";
		if($this->getFirstResultFromQuery($query, $queryParams)==false){
			$selectString = "
			    {$tableName}_id as \"{$tableName}_id\",
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			";
			$query = "
				select {$selectString}
				from p_{$tableName}_ins(
				    Morbus_id := :Morbus_id,
				    pmuser_id := :pmUser_id
				);
			";
		}
		if ($isAllowTransaction == true) {
			$this->beginTransaction();
		}
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			if ($isAllowTransaction == true) {
				$this->rollbackTransaction();
			}
			throw new Exception("Ошибка БД", 500);
		}
		$resp = $result->result("array");
		if (!empty($resp[0]["Error_Msg"])) {
			if ($isAllowTransaction == true) {
				$this->rollbackTransaction();
			}
			throw new Exception($resp[0]["Error_Msg"], 500);
		}
		if (empty($resp[0][$tableName . "_id"])) {
			if ($isAllowTransaction == true) {
				$this->rollbackTransaction();
			}
			throw new Exception("Не удалось создать объект {$tableName}", 500);
		}
		$this->_saveResponse[$tableName . "_id"] = $resp[0][$tableName . "_id"];
		return $this->_saveResponse;
	}

	/**
	 * Загрузка формы редактирования записи регистра
	 *
	 * @param $data
	 * @return array|false
	 */
	public function load($data)
	{
		$sql = "
			select
				MO.MorbusGeriatrics_id as \"MorbusGeriatrics_id\",
				MO.Morbus_id as \"Morbus_id\",
				M.Diag_id as \"Diag_id\",
				M.Person_id as \"Person_id\",
				to_char(PR.PersonRegister_setDate, '{$this->dateTimeForm104}') as \"PersonRegister_setDate\",
				MO.AgeNotHindrance_id as \"AgeNotHindrance_id\",
				MO.MorbusGeriatrics_IsKGO as \"MorbusGeriatrics_IsKGO\",
				MO.MorbusGeriatrics_IsWheelChair as \"MorbusGeriatrics_IsWheelChair\",
				MO.MorbusGeriatrics_IsFallDown as \"MorbusGeriatrics_IsFallDown\",
				MO.MorbusGeriatrics_IsWeightDecrease as \"MorbusGeriatrics_IsWeightDecrease\",
				MO.MorbusGeriatrics_IsCapacityDecrease as \"MorbusGeriatrics_IsCapacityDecrease\",
				MO.MorbusGeriatrics_IsCognitiveDefect as \"MorbusGeriatrics_IsCognitiveDefect\",
				MO.MorbusGeriatrics_IsMelancholia as \"MorbusGeriatrics_IsMelancholia\",
				MO.MorbusGeriatrics_IsEnuresis as \"MorbusGeriatrics_IsEnuresis\",
				MO.MorbusGeriatrics_IsPolyPragmasy as \"MorbusGeriatrics_IsPolyPragmasy\"
			from
				v_MorbusGeriatrics MO
				left join v_Morbus M on M.Morbus_id = MO.Morbus_id
				left join v_PersonRegister PR on PR.Morbus_id = M.Morbus_id
			where
				MO.MorbusGeriatrics_id = :MorbusGeriatrics_id
		";
		$sqlParams = ["MorbusGeriatrics_id" => $data["MorbusGeriatrics_id"]];
		return $this->queryResult($sql, $sqlParams);
	}

	/**
	 * Сохранение формы редактирования записи регистра
	 *
	 * @param $data
	 * @return array|false
	 */
	public function save($data)
	{
		if (!empty($data["MorbusGeriatrics_id"])) {
			$proc = "p_MorbusGeriatrics_upd";
			$sql = "
				select Diag_id
				from v_MorbusGeriatrics
				where MorbusGeriatrics_id = :MorbusGeriatrics_id
				limit 1
			";
			$sqlParams = ["MorbusGeriatrics_id" => $data["MorbusGeriatrics_id"]];
			$data["Diag_id"] = $this->getFirstResultFromQuery($sql, $sqlParams);
		} else {
			$data["MorbusGeriatrics_id"] = null;
			$proc = "p_MorbusGeriatrics_ins";
		}
		$selectString = "
			morbusgeriatrics_id as \"MorbusGeriatrics_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$sql = "
			select {$selectString}
			from {$proc}(
			    morbusgeriatrics_id := :MorbusGeriatrics_id,
			    morbus_id := :Morbus_id,
			    morbusgeriatrics_isfalldown := :MorbusGeriatrics_IsFallDown,
			    morbusgeriatrics_isweightdecrease := :MorbusGeriatrics_IsWeightDecrease,
			    morbusgeriatrics_iscapacitydecrease := :MorbusGeriatrics_IsCapacityDecrease,
			    morbusgeriatrics_iscognitivedefect := :MorbusGeriatrics_IsCognitiveDefect,
			    morbusgeriatrics_ismelancholia := :MorbusGeriatrics_IsMelancholia,
			    morbusgeriatrics_isenuresis := :MorbusGeriatrics_IsEnuresis,
			    morbusgeriatrics_ispolypragmasy := :MorbusGeriatrics_IsPolyPragmasy,
			    morbusgeriatrics_iswheelchair := :MorbusGeriatrics_IsWheelChair,
			    morbusgeriatrics_iskgo := :MorbusGeriatrics_IsKGO,
			    agenothindrance_id := :AgeNotHindrance_id,
			    pmuser_id := :pmUser_id
			);
		";
		return $this->queryResult($sql, $data);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	public function getIdForEmk($data)
	{
		$params = ["Person_id" => $data["Person_id"],];
		$query = "
			select MG.MorbusGeriatrics_id as \"MorbusGeriatrics_id\"
			from v_MorbusGeriatrics MG
				inner join v_Morbus M on M.Morbus_id = MG.Morbus_id
			where M.Person_id = :Person_id
			order by MG.MorbusGeriatrics_id desc
			limit 1
		";
		return $this->queryResult($query, $params);
	}
}