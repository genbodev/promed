<?php
defined("BASEPATH") or die("No direct script access allowed");
/**
 * Replicator_model - модель, для отправки данных в ActiveMQ, для последующей записи в другую БД
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       SWAN Developers
 * @version      31.10.2017
 */
class Replicator_model extends swPgModel
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Начитываем данные и отправляем в ActiveMQ
	 * @param $data
	 * @param bool $destination
	 */
	public function sendRecordToActiveMQ($data, $destination = false)
	{
		$params = [];
		if ($data["type"] != "delete") {
			$resp = $this->queryResult("
				select *, 0 as {$data["table"]}_Rowversion
				from {$data["table"]}
				where {$data["keyParam"]} = :{$data["keyParam"]}
			", [$data["keyParam"] => $data["keyValue"]]);
			if (!empty($resp[0])) {
				$params = $resp[0];
			}
		}
		// преобразуем даты в строки
		foreach ($params as $key => $param) {
			if (is_object($param) && get_class($param) == "DateTime") {
				/**@var DateTime $param */
				$params[$key] = $param->format("Y-m-d H:i:s");
			}
		}
		if (!empty($params) || $data["type"] == "delete") {
			if ($destination === false) {
				$destination = ($this->config->item("IsLocalSMP") === true)
					? "/queue/ru.swan.emergency.localtomaindb"
					: "/queue/ru.swan.emergency.tomaindb";
			}
			$funcParams = [
				"type" => $data["type"], // тип (insert/update/delete)
				"table" => $data["table"], // таблица
				"params" => $params, // список полей и значений
				"keyParam" => $data["keyParam"] // имя поля для удаления
			];
			sendStompMQMessage($funcParams, "Rule", $destination);
		}
	}
}