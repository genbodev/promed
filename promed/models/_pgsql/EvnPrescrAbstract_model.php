<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once('EvnDiagAbstract_model.php');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 * 
 * Абстрактная модель назначения определенного типа
 *
 * Частные модели инкапсулируют бизнес-логику назначения,
 * данные которого хранятся в отдельной таблице
 *
 * Назначения любого типа может быть:
 * Удалено из БД или отменено (PrescriptionStatusType_id = 3), если оно подписано, но не выполнено
 * Подписано (PrescriptionStatusType_id = 2) или отменена подпись (PrescriptionStatusType_id = 1),
 * если оно было подписано
 * Выполнено или отменено выполнение
 *
 * По назначению любого типа, которое содержит услугу, может быть создано направление
 * на службу, которая оказывает эту или аналогичную услугу
 * Если создано направление, то назначение не может быть удалено или отменено.
 * При создании направления создается заказ услуги,
 * который становится доступным для выполнения в соотв. АРМе службы
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 *
 * @property CI_DB_driver $db
 */
abstract class EvnPrescrAbstract_model extends EvnDiagAbstract_model
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	abstract public function getPrescriptionTypeId();

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	abstract public function getTableName();

	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	abstract public function doLoad($data);

	/**
	 * Возвращает данные учетного документа
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	protected function getEvnData($id)
	{
		if (empty($id)) {
			throw new Exception("Отсутствует ключ учетного документа");
		}
		$query = "
			select
				EvnClass_SysNick as \"EvnClass_SysNick\",
				Evn_setDT as \"Evn_setDT\"
			from v_Evn
			where Evn_id = :Evn_id
			limit 1
		";
		$queryParams = ["Evn_id" => $id];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при запросе данных учетного документа");
		}
		$response = $result->result("array");
		if (empty($response) || empty($response[0])) {
			throw new Exception("Данные учетного документа не найдены");
		}
		return $response[0];
	}

	/**
	 * Возвращает все данные назначения по ключу
	 * @param int $id
	 * @param string $object
	 * @param string $format
	 * @return array
	 * @throws Exception
	 */
	protected function getAllData($id, $object = null, $format = "Y-m-d")
	{
		if (empty($id)) {
			throw new Exception("Отсутствует ключ назначения");
		}
		if (empty($object)) {
			$object = $this->getTableName();
		}
		$query = "select * from v_{$object} where {$object}_id = :{$object}_id limit 1";
		$queryParams = [$object . "_id" => $id];
		/**
		 * @var CI_DB_result $result
		 * @var DateTime $var
		 */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при запросе данных назначения");
		}
		$response = $result->result("array");
		if (empty($response) || empty($response[0])) {
			throw new Exception("Данные объекта [{$object}] не найдены!");
		}
		foreach ($response[0] as $key => $value) {
			if (is_object($value)) {
				if ($value instanceof DateTime) {
					$response[0][$key] = $value->format($format);
				}
			}
		}
		return $response[0];
	}

	/**
	 * Проверяет наличие назначений на указанную дату
	 * @param $set_date
	 * @param $id
	 * @return bool
	 * @throws Exception
	 */
	protected function isBusyDay($set_date, $id)
	{
		if (empty($set_date)) {
			throw new Exception("Не указана дата");
		}
		$object = $this->getTableName();
		$query = "select {$object}_id as \"{$object}_id\" from v_{$object} where {$object}_setDT::date = :set_date::date and {$object}_id = :id limit 1";
		$queryParams = [
			"set_date" => $set_date,
			"id" => $id,
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при проверке наличия назначений на указанную дату");
		}
		$response = $result->result("array");
		if (empty($response) || empty($response[0])) {
			return false;
		}
		return true;
	}

	/**
	 * Метод очистки дочерних таблиц назначений со списком услуг, с бирками
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	protected function clearEvnPrescrTable($data)
	{
		if (empty($data["fk_pid"]) || empty($data["object"]) || empty($data["pid"])) {
			throw new Exception("Отсутствует ключ");
		}
		$object = $data["object"];
		$fk_pid = $data["fk_pid"];
		$query = "select {$object}_id as \"{$object}_id\" from v_{$object} where {$fk_pid} = :pid";
		$queryParams = [
			"pid" => $data["pid"]
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result("array");
		if (!is_array($response)) {
			return false;
		}
		if (empty($response)) {
			return [["Error_Msg" => null]];
		}
		foreach ($response as $row) {
			try {
				$this->_destroy([
					"object" => $object,
					"id" => $row[$object . "_id"],
					"pmUser_id" => isset($data["pmUser_id"]) ? $data["pmUser_id"] : null,
				]);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
		return [["Error_Msg" => null]];
	}

	/**
	 * Перенос плановой даты
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function doMoveInDay($data)
	{
		$object = $this->getTableName();
		try {
			$row = $this->getAllData($data["EvnPrescr_id"]);
			if (array_key_exists("EvnDirection_id", $row) && !empty($row["EvnDirection_id"])) {
				throw new Exception("Переместить назначение, если создано направление нельзя!");
			}
			if (array_key_exists($object . "_IsExec", $row) && !empty($row[$object . "_IsExec"])) {
				throw new Exception("Переместить исполненное назначение нельзя!");
			}
			$newDate = new DateTime($data["EvnPrescr_setDate"]);
			if ($data["whither"] == "next") {
				$newDate->add(new DateInterval("P1D"));
			} else {
				$newDate->sub(new DateInterval("P1D"));
			}
			if ($this->isBusyDay($newDate->format("Y-m-d"), $data["EvnPrescr_id"])) {
				throw new Exception("Сдвинуть назначение в уже заполненную ячейку нельзя!");
			}
			$evn = $this->getEvnData($row[$object . "_pid"]);
			if ($data["whither"] == "prev" && $newDate < $evn["Evn_setDT"]) {
				throw new Exception("Сдвинуть назначение назад ранее дня поступления в отделение нельзя!");
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
		$row["pmUser_id"] = $data["pmUser_id"];
		$row[$object . "_setDT"] = $newDate->format("Y-m-d");
		$fields_arr = [];
		foreach ($row as $key => $value) {
			if (in_array($key, [
				$object . "_id",
				$object . "_setDate",
				$object . "_setTime",
				$object . "_rid",
				"EvnClass_id",
				"EvnClass_Name",
				"pmUser_insID",
				"pmUser_updID",
				"Person_id",
				$object . "_didDate",
				$object . "_didTime",
				$object . "_disDate",
				$object . "_disTime",
				$object . "_insDT",
				$object . "_updDT",
				$object . "_Index",
				$object . "_Count",
				$object . "_IsArchive",
				$object . "_Guid"
			])) {
				continue;
			}
			$fields_arr[] = "{$key} := :{$key}";
		}
		$fields = implode(", ", $fields_arr);
		$query = "
			select 
			 	{$object}_id as \"{$object}_id\",
			 	Error_Code as \"Error_Code\",
			 	Error_Message as \"Error_Msg\"
			from p_{$object}_upd(
			    {$object}_id := :{$object}_id,
			    {$fields}
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $row);
		if (!is_object($result)) {
			throw new Exception("Ошибка запроса к БД");
		}
		return $result->result("array");
	}

	/**
	 * Формирование списка дат для календаря
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	protected function _createDateList($data)
	{
		$dateList = [];
		if (isset($data["EvnPrescr_setDate_Range"]) && is_array($data["EvnPrescr_setDate_Range"]) && count($data["EvnPrescr_setDate_Range"]) == 2 && !empty($data["EvnPrescr_setDate_Range"][0]) && !empty($data["EvnPrescr_setDate_Range"][1])) {
			$compareResult = swCompareDates($data["EvnPrescr_setDate_Range"][0], $data["EvnPrescr_setDate_Range"][1]);
			if ($compareResult[0] == -1) {
				throw new Exception("Дата окончания курса больше даты начала", 400);
			} else if ($compareResult[0] == 100) {
				throw new Exception("Неверный формат дат продолжительности курса", 400);
			}
			$start = DateTime::createFromFormat("Y-m-d", $data["EvnPrescr_setDate_Range"][0]);
			$end = DateTime::createFromFormat("Y-m-d", $data["EvnPrescr_setDate_Range"][1]);
			$data["EvnPrescr_dayNum"] = $start->diff($end)->days + 1;
		}
		if (empty($data["EvnPrescr_setDate"])) {
			$start = DateTime::createFromFormat("Y-m-d", date("Y-m-d"));
		} else {
			$start = DateTime::createFromFormat("Y-m-d", $data["EvnPrescr_setDate"]);
			if (!$start) {
				throw new Exception("Неверный формат даты начала курса", 400);
			}
		}
		if (empty($data["EvnPrescr_dayNum"])) {
			$data["EvnPrescr_dayNum"] = 1;
		}
		$dateList[] = $start->format("Y-m-d");
		if ($data["EvnPrescr_dayNum"] > 1) {
			$interval = new DateInterval("P1D");
			$day_cnt = 1;
			while ($data["EvnPrescr_dayNum"] != $day_cnt) {
				$dateList[] = $start->add($interval)->format("Y-m-d");
				$day_cnt++;
			}
		}
		return $dateList;
	}

	/**
	 * Удаление записи назначения
	 * @param $data
	 * @return bool
	 * @throws Exception
	 */
	protected function _destroy($data)
	{
		if (empty($data["object"]) || empty($data["id"])) {
			throw new Exception("Отсутствует ключ");
		}
		$object = $data["object"];
		$pmuser = "";
		if (isset($data["pmUser_id"])) {
			$pmuser = ",pmUser_id := :pmUser_id";
			$row["pmUser_id"] = $data["pmUser_id"];
		}
		$query = "
			select
			 	Error_Code as \"Error_Code\",
			 	Error_Message as \"Error_Msg\"
			from p_{$object}_del(
			    {$object}_id := :id
			    {$pmuser}
			);
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			throw new Exception("Ошибка при удалении записи назначения");
		}
		$response = $result->result("array");
		if (!empty($response[0]["Error_Msg"])) {
			throw new Exception($response[0]["Error_Msg"]);
		}
		return true;
	}

	/**
	 * Сохранение назначения без календаря, графиков и других дочерних объектов в EvnPrescr
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	protected function _save($data = [])
	{
		$procedure = !empty($data['EvnPrescr_id']) && $data['EvnPrescr_id'] > 0 ? "p_EvnPrescr_upd" : "p_EvnPrescr_ins";
		$selectString = "
			evnprescr_id as \"EvnPrescr_id\",
			error_code as \"Error_Code\",
			error_message as \"Error_Message\"
		";
		$query = "
			select {$selectString}
			from {$procedure}(
			    evnprescr_id := :EvnPrescr_id,
			    evnprescr_pid := :EvnPrescr_pid,
			    lpu_id := :Lpu_id,
			    server_id := :Server_id,
			    personevn_id := :PersonEvn_id,
			    prescriptiontype_id := :PrescriptionType_id,
			    evnprescr_iscito := :EvnPrescr_IsCito,
			    prescriptionstatustype_id := :PrescriptionStatusType_id,
			    evnprescr_descr := :EvnPrescr_Descr,
			    pmuser_id := :pmUser_id
			);
		";
		$queryParams = [
			"EvnPrescr_id" => (!empty($data["EvnPrescr_id"]) && $data["EvnPrescr_id"] > 0 ? $data["EvnPrescr_id"] : null),
			"EvnPrescr_pid" => $data["EvnPrescr_pid"],
			"Lpu_id" => $data["Lpu_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"],
			"PrescriptionType_id" => $this->getPrescriptionTypeId(),
			"PrescriptionStatusType_id" => (!empty($data["PrescriptionStatusType_id"]) ? $data["PrescriptionStatusType_id"] : 1),
			"EvnPrescr_Descr" => (!empty($data["EvnPrescr_Descr"]) ? $data["EvnPrescr_Descr"] : null),
			"EvnPrescr_IsCito" => (!empty($data["EvnPrescr_IsCito"]) ? $data["EvnPrescr_IsCito"] : 1),
			"pmUser_id" => $data["pmUser_id"],
		];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception("Ошибка при запросе к БД при сохранении назначения", 500);
		}
		$response = $result->result("array");
		if (!empty($response[0]["Error_Msg"])) {
			throw new Exception($response[0]["Error_Msg"], 500);
		}
		return $response[0]["EvnPrescr_id"];
	}
}