<?php
defined("BASEPATH") or die ("No direct script access allowed");

/**
 * Class PhpLog_model
 *
 * @property CI_DB_driver $db
 * @property swMongodb|swMongodbPHP7 $swmongodb
 */
class PhpLog_model extends swPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	var $logs_table = 'PHPLog2';
	var $mongodb_log = 'sysLog';
	var $countRun = 2000000; // Количество записей обрабатываемых за один запуск задания
	var $countStep = 5000;   // Количество записей, обрабатываемых за один шаг задания
	
	/**
	 * Получаем список контроллеров
	 * @return array|bool
	 */
	function loadControllersList() {
		$query = "
			select
				Controller_ID as \"Controller_ID\",
			    Controller_Name as \"Controller_Name\"
			from Controllers
			order by Controller_Name
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Находим или создаем контроллер
	 * @param string $value
	 * @param $new
	 * @param array|null $data
	 * @return array
	 */
	private function findOrCreateController($value, &$new, $data = null) {
		$new = true;
		$value = ($value == null) ? "" : $value;
		if ($data == null) {
			$data = $this->loadControllersList();
		}
		if (is_array($data)) {
			foreach ($data as $dataItem) {
				if ($dataItem["Controller_Name"] == $value) {
					$new = false;
					return $dataItem;
				}
			}
		}
		$query = "
			insert into Controllers(Controller_Name, Controller_Name_Ru, pmUser_insID, pmUser_updID, Controllers_insDT, Controllers_updDT)
			values (:Controller_Name, :Controller_Name, :pmUser_id, :pmUser_id, getdate(), getdate());
		";
		$queryParams = ["Controller_Name" => $value, "pmUser_id" => 1];
		$this->db->query($query, $queryParams);

		/**@var CI_DB_result $result */
		$query = "
			select
			    Controller_ID as \"Controller_ID\",
			    Controller_Name as \"Controller_Name\"
			from Controllers
			order by Controller_ID desc
			limit 1
		";
		$result = $this->db->query($query);
		$result = $result->result_array();
		return $result[0];
	}

	/**
	 * Получаем список методов
	 * @param $data
	 * @return array|bool
	 */
	function loadMethodsList($data = []) {
		/**@var CI_DB_result $result */
		$whereString = (!empty($data["Controller_ID"])) ? "where Controller_ID = {$data["Controller_ID"]}" : "";
		$query = "
			select
				Method_ID as \"Method_ID\",
			    Controller_ID as \"Controller_ID\",
			    Method_Name as \"Method_Name\",
			    Method_Name_Ru as \"Method_Name_Ru\"
			from Methods
			{$whereString}
			order by Method_Name
		";
		$result = $this->db->query($query);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Получаем список методов которые имеют описание
	 * @param $data
	 * @return array|bool
	 */
	function loadRuMethodsList($data = []) {
		/**@var CI_DB_result $result */
		$whereString = (!empty($data["Controller_ID"])) ? " and Controller_ID = {$data["Controller_ID"]}" : "";
		$query = "
			select
				Method_ID as \"Method_ID\",
			    Controller_ID as \"Controller_ID\",
			    Method_Name as \"Method_Name\",
			    Method_Name_Ru as \"Method_Name_Ru\"
			from Methods
			where Method_Name <> Method_Name_Ru {$whereString}
			order by Method_Name_Ru
		";
		$result = $this->db->query($query);
		return (is_object($result)) ? $result->result_array() : false;
	}

	/**
	 * Находим или создаем метод
	 * @param string $value
	 * @param int $ControllerID
	 * @param $new
	 * @param array|null $data
	 * @return array
	 */
	private function findOrCreateMethod($value, $ControllerID, &$new, $data = null) {
		$new = true;
		$value = ($value == null) ? "" : $value;
		if ($data == null) {
			$data = $this->loadMethodsList(["Controller_ID" => $ControllerID]);
		}
		if (is_array($data)) {
			foreach ($data as $dataItem) {
				if ($dataItem["Method_Name"] == $value && $dataItem["Controller_ID"] == $ControllerID) {
					$new = false;
					return $dataItem;
				}
			}
		}
		$query = "
			insert into Methods(Method_Name, Controller_ID, Method_Name_Ru, pmUser_insID, pmUser_updID, Methods_insDT, Methods_updDT)
			values (:Method_Name, :Controller_ID, :Method_Name, :pmUser_id, :pmUser_id, getdate(), getdate());
		";
		$queryParams = [
			"Method_Name" => $value,
			"Controller_ID" => $ControllerID,
			"pmUser_id" => 1
		];
		$this->db->query($query, $queryParams);
		$query = "
			select
			    Method_ID as \"Method_ID\",
			    Controller_ID as \"Controller_ID\",
			    Method_Name as \"Method_Name\"
			from Methods
			order by Method_ID desc
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		$result = $result->result_array();
		return $result[0];
	}

	private function updateControllersAndMethodsNames() {
		/**@var CI_DB_result $result */
		$query = "
			select
				Controller_ID as \"Controller_ID\",
				Controller_Name as \"Controller_Name\",
				Controller_Name_Ru as \"Controller_Name_Ru\"
			from promed.Controllers
		";
		$result = $this->db->query($query);
		$dataControllers = $result->result_array();
		foreach ($dataControllers as $dataController) {
			$query = "
				update Controllers
				set Controller_Name_Ru = '{$dataController["Controller_Name_Ru"]}'
				where Controller_Name = '{$dataController["Controller_Name"]}'
				  and Controller_Name = Controller_Name_Ru;
			";
			$this->db->query($query);
		}

		$query = "
			select
				T1.Method_ID as \"Method_ID\",
				T1.Method_Name as \"Method_Name\",
				T1.Controller_ID as \"Controller_ID\",
			    T2.Controller_Name as \"Controller_Name\",
				T1.Method_Name_Ru as \"Method_Name_Ru\"
			from promed.Methods T1
				,promed.Controllers T2
			where T1.Controller_ID = T2.Controller_ID
		";
		$result = $this->db->query($query);
		$dataMethods = $result->result_array();
		foreach ($dataMethods as $dataMethod) {
			$query = "
				update Methods
				set Method_Name_Ru = '{$dataMethod["Method_Name_Ru"]}'
				where Controller_ID in (select Controller_ID from Controllers where Controller_Name = '{$dataMethod["Controller_Name"]}')
				  and Method_Name = '{$dataMethod["Method_Name"]}'
				  and Method_Name = Method_Name_Ru;
			";
			$this->db->query($query);
		}
	}

	/**
	 * @return array
	 */
	function loadOldLogToNewFormat() {
		/**
		 * @var CI_DB_result $result
		 * @var DateTime $dateTime
		 */
		$this->updateControllersAndMethodsNames();

		$controllers = $this->loadControllersList();
		$methods = $this->loadMethodsList();
		for ($i = 0; $i < 1000; $i++) {
			$query = "
				select
					PHPLog_id as \"PHPLog_id\",
					Controller as \"Controller\",
					Method as \"Method\",
					QueryString as \"QueryString\",
					ET as \"ET\",
					PHPLog_insDT as \"PHPLog_insDT\",
					pmUser_insID as \"pmUser_insID\",
					IP as \"IP\",
					POST as \"POST\",
					ET_Query as \"ET_Query\",
					Server_IP as \"Server_IP\",
					AnswerError as \"AnswerError\",
					ARMType_id as \"ARMType_id\"
				from PHPLog2
				order by PHPLog_id desc
				limit 100
			";
			$result = $this->db->query($query);
			$result = $result->result_array();

			$sql = "";
			$idxs = [];
			if (count($result) == 0) {
				$i = 1000;
			}
			foreach ($result as $resultItem) {
				$isNew = null;
				$controllerRecord = $this->findOrCreateController($resultItem["Controller"], $isNew, $controllers);
				if ($isNew == true) {
					$controllers[] = $controllerRecord;
				}
				$isNew = null;
				$methodRecord = $this->findOrCreateMethod($resultItem["Method"], $controllerRecord["Controller_ID"], $isNew, $methods);
				if ($isNew == true) {
					$methods[] = $methodRecord;
				}
				$dateTime = date_create($resultItem["PHPLog_insDT"]);
				$tablePrefix = $dateTime->format("m_Y");
				$sqlParams = [
					"Controller_ID" => $controllerRecord["Controller_ID"],
					"Method_ID" => $methodRecord["Method_ID"],
					"QueryString" => $resultItem["QueryString"],
					"ET" => str_replace(",", ".", $resultItem["ET"]),
					"PHPLog_insDT" => $resultItem["PHPLog_insDT"],
					"pmUser_insID" => $resultItem["pmUser_insID"],
					"IP" => $resultItem["IP"],
					"POST" => $resultItem["POST"],
					"ET_Query" => str_replace(",", ".", $resultItem["ET_Query"]),
					"Server_IP" => $resultItem["Server_IP"],
					"AnswerError" => $resultItem["AnswerError"],
					"ARMType_id" => $resultItem["ARMType_id"],
				];
				$lineFields = "Controller_ID, Method_ID, QueryString, ET, PHPLog_insDT, pmUser_insID, IP, POST, ET_Query, Server_IP, AnswerError, ARMType_id";
				$lineValues = ":Controller_ID, :Method_ID, :QueryString, :ET, :PHPLog_insDT, :pmUser_insID, :IP, :POST, :ET_Query, :Server_IP, :AnswerError, :ARMType_id";
				$sql .= getDebugSql("insert into PHPLog_{$tablePrefix}({$lineFields}) values ({$lineValues});", $sqlParams);
				$idxs[] = $resultItem["PHPLog_id"];
			}
			if (trim($sql) != "") {
				$this->db->query($sql);
				$this->db->query("delete from PHPLog2 where PHPLog_id in (" . implode(",", $idxs) . ");");
			}
		}
		return [];
	}

	/**
	 * Проверят использовать новую систему логгирования или старую
	 * @return bool
	 */
	function isNew() {
		return (in_array(getRegionNick(), ["vologda"])) ? true : false;
	}

	/**
	 * Сливаем данные из MongoDB в PHPLog
	 * @return array|bool
	 */
	function transferDataFromMongoDB() {
		$this->load->library('textlog', array('file'=>'TransferDataFromMongoDB_'.date('Y-m-d').'.log'));
		$this->textlog->add('Transfer start');

		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', array('config_file'=>'mongodblog', 'fail_gracefully'=>true), 'swmongodb');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', array('config_file'=>'mongodblog', 'fail_gracefully'=>true), 'swmongodb');
				break;
			default:
				return array('Error_Msg' => 'The MongoDB PECL extension has not been installed or enabled.');
				break;
		}

		if (!$this->swmongodb) {
			return false;
		}
		// библиотека загружена и с ней можно работать

		// может потребоваться прилично времени на перенос данных
		set_time_limit(0);
		$this->swmongodb->timeout = -1;
		// уберем запись queries и query_times, чтобы не забивать память.
		$this->db->save_queries = false;
		
		// определение общего количества записей для переноса данных
		$count = $this->swmongodb->count($this->mongodb_log);
		$this->textlog->add("Count of records for transfer: {$count}");
		$start = 0;
		$count_rows = 0;
		$limit = $this->countStep; // Читаем по 1000 записей
		// Ограничиваем количество переносимых данных какой-то цифрой
		if ($count > $this->countRun) {
			$count = $this->countRun;
		}
		$isNew = $this->isNew();
		if ($count > 0) {
			while ($count > 0) {
				$sql = "";
				try {
					if ($count < $limit) { // Если количество меньше лимита, то лимит подгоним под количество
						$limit = $count;
					}
					// читаем записи
					$rows = $this->swmongodb->offset($start)->limit($limit)->order_by(["_id" => ""])->get($this->mongodb_log);
					$count_rows = count($rows);
					$this->textlog->add("Read {$count_rows} records from Mongo");
					foreach ($rows as &$row) {
						$row["PHPLog_insDT"] = ($row["PHPLog_insDT"] instanceof MongoDB\BSON\UTCDateTime)
							? date("d F Y H:i:s", $row["PHPLog_insDT"]->toDateTime()->getTimestamp())
							: date("d F Y H:i:s", $row["PHPLog_insDT"]->sec);
						if (!isset($row["AnswerError"])) {
							$row["AnswerError"] = null;
						}
						// формируем SQL-запрос
						if (!empty(@$row["ARMType_id"])) {
							$sql .= getDebugSql("
								Insert into PHPLog2(Controller, Method, QueryString, ET, ET_Query, PHPLog_insDT, pmUser_insID, IP, Post, Server_IP, AnswerError, ARMType_id)
								Values (:Controller, :Method, :QueryString, :ET, :ET_Query, :PHPLog_insDT, :pmUser_insID, :IP, :POST, :Server_IP, :AnswerError, :ARMType_id);
							", $row);
						} else {
							$sql .= getDebugSql("
								Insert into PHPLog2(Controller, Method, QueryString, ET, ET_Query, PHPLog_insDT, pmUser_insID, IP, Post, Server_IP, AnswerError)
								Values (:Controller, :Method, :QueryString, :ET, :ET_Query, :PHPLog_insDT, :pmUser_insID, :IP, :POST, :Server_IP, :AnswerError);
							", $row);
						}
						/* $isdel = $this->swmongodb->where('PHPLog_id',$row['PHPLog_id'])->delete($this->mongodb_log);
						// PROMEDWEB-10080: Поле PHPLog_id удалено из коллекции sysLog в MongoDb, а значение _id не совпадает с $row['PHPLog_id'],
						// поэтому раскомментировать этот код не потребуется никогда. Чистка sysLog происходит ниже после комментария "Удаляем все записи одним запросом"
						if (!$isdel) {
							$this->textlog->add('Error deleting records from MongoDB!');
						}*/
					}
					// все прочитанные записи загоняем в БД PhpLog
					$sqlresult = $this->db->query($sql);
					$this->textlog->add("Add {$count_rows} records in DB");
					$this->textlog->add(($sqlresult) ? "Transfer {$count_rows} records" : "SQL Error on transfer {$count_rows} records");
				} catch (Exception $e) {
					log_message("error", "Transfer data from MongoDB error: " . $e->getMessage());
					continue;
				}
				$start = $start + $count_rows;
				$count = $count - $count_rows;
				$this->textlog->add("Count of records for transfer: {$count}");
			}
			// Удаляем все записи одним запросом
			try {
				$lastInsDT = $rows[$count_rows - 1]['PHPLog_insDT']; // Удаляются все записи, переваленные из MongoDb->sysLog в PgSQL->PHPLog2
				$isdel = $this->swmongodb->where_lte("PHPLog_insDT", $lastInsDT)->delete_all($this->mongodb_log);
				$this->textlog->add((!$isdel) ? "Error deleting records from MongoDB!" : "Delete records from Mongo");
			} catch (Exception $e) {
				log_message('error', 'Error deleting records from MongoDB: '. $e->getMessage());
			}
		}
		$this->textlog->add('Transfer finish');
		if ($isNew == true) {
			$this->loadOldLogToNewFormat();
		}
		return true;
	}

	/**
	 * Читаем записи лога из Mongo
	 * @param $data
	 * @return array|bool
	 */
	function loadPhpLogGridWithMongo($data) {
		// Проверяем есть ли данные в MongoDB и если есть, то читаем из MongoDB иначе из БД, хотя еще зависит от хуков
		if (!extension_loaded("mongo")) {
			return false;
		}
		$this->load->library("swMongodb", ["config_file" => "mongodblog", "fail_gracefully" => true]);
		if (!$this->swmongodb) {
			return false;
		}
		// библиотека загружена и с ней можно работать
		if (!empty($data["PHPLog_insDT"][0]) && !empty($data["PHPLog_insDT"][1])) {
			$begdate = new MongoDate(strtotime("{$data["PHPLog_insDT"][0]} 00:00:00"));
			$enddate = new MongoDate(strtotime("{$data["PHPLog_insDT"][1]} 23:59:59"));
			$this->swmongodb->where_between("PHPLog_insDT", $begdate, $enddate);
		}
		if (!empty($data["Controller"])) {
			$this->swmongodb->like("Controller", $data["Controller"]);
		}
		if (!empty($data["Method"])) {
			$this->swmongodb->like("Method", $data["Method"]);
		}
		if (!empty($data["ET_from"])) { // >=
			$this->swmongodb->where_gte("ET", (float)$data["ET_from"]);
		}
		if (!empty($data["ET_to"])) { // <=
			$this->swmongodb->where_lte("ET", (float)$data["ET_to"]);
		}
		if (!empty($data["ET_Query_from"])) {
			$this->swmongodb->where_gte("ET_Query", (float)$data["ET_Query_from"]);
		}
		if (!empty($data["ET_Query_to"])) {
			$this->swmongodb->where_lte("ET_Query", (float)$data["ET_Query_to"]);
		}
		if (!empty($data["PMUser_Login"])) {
			$this->swmongodb->like("PMUser_Login", $data["PMUser_Login"]);
		}
		if (!empty($data["IP"])) {
			$this->swmongodb->where("IP", $data["IP"]);
		}
		if (!empty($data["Server_IP"])) {
			$this->swmongodb->where("Server_IP", $data["Server_IP"]);
		}
		if (!empty($data["AnswerError"])) {
			$this->swmongodb->where("AnswerError", $data["AnswerError"]);
		}
		if (!empty($data["POST"])) {
			$this->swmongodb->like("POST", $data["POST"]);
		}
		$wheres = $this->swmongodb->wheres; // иначе сбрасываются после выполнения 
		// определение общего количества записей
		$count = $this->swmongodb->count($this->mongodb_log);
		if (isset($data["start"]) && $data["start"] >= 0 && isset($data["limit"]) && $data["limit"] >= 0) {
			$this->swmongodb->offset($data["start"]);
			$this->swmongodb->limit($data["limit"]);
		}
		// можно вместо $this->swmongodb сформировать массив $filter и передать в where()
		try {
			$rows = $this->swmongodb->where($wheres)->order_by(["_id" => "desc"])->get($this->mongodb_log);
		} catch (Exception $e) {
			return false;
		}
		// обработка
		foreach ($rows as &$row) {
			$row["PHPLog_insDT"] = ($row["PHPLog_insDT"] instanceof MongoDB\BSON\UTCDateTime)
				? date("d F Y H:i:s", $row["PHPLog_insDT"]->toDateTime()->getTimestamp())
				: date("d F Y H:i:s", $row["PHPLog_insDT"]->sec);
		}
		return [
			"data" => $rows,
			"totalCount" => $count
		];
	}

	/**
	 * Загрузка грида с логом
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function loadPhpLogGrid($data) {
		$isNew = $this->isNew();
		if ($isNew == true) {
			//Новая система логирования
			$filters = [];
			$queryParams = [];
			$tables = [];
			if (empty($data["PHPLog_insDT"][0]) && empty($data["PHPLog_insDT"][1])) {
				$tables[] = "PHPLog_" . date("m_Y");
			} else {
				$dateStart = DateTime::createFromFormat("Y-m-d", $data["PHPLog_insDT"][0]);
				$dateFinish = DateTime::createFromFormat("Y-m-d", $data["PHPLog_insDT"][1]);
				$dateCurrent = $dateStart;
				while ($dateFinish->getTimestamp() >= $dateCurrent->getTimestamp()) {
					$tables[] = "PHPLog_" . $dateCurrent->format("m_Y");
					$dateCurrent->add(new DateInterval("P1M"));
				}
			}
			if (!empty($data["ARMType"])) {
				$queryRow = $this->getFirstRowFromQuery("select ARMType_id as \"ARMType_id\" from promed.ARMType where ARMType_Name = '{$data["ARMType"]}'");
				if (@$queryRow["ARMType_id"] != null) {
					$filters[] = "PL[N].ARMType_id = :ARMType";
					$queryParams["ARMType"] = $queryRow["ARMType_id"];
				}
			}
			if (!empty($data["Controller"])) {
				$filters[] = "PL[N].Controller_ID in (select Controller_ID from Controllers where Controller_Name ilike :Controller || '%')";
				$queryParams["Controller"] = $data["Controller"];
			}
			if (!empty($data["Method"])) {
				$filters[] = "PL[N].Method_ID in (select Method_ID from Methods where Method_Name ilike :Method || '%')";
				$queryParams["Method"] = $data["Method"];
			}
			if (!empty($data["methodAdvanced"])) {
				$filters[] = "PL[N].Method_ID in (select Method_ID from Methods where Method_Name_Ru ilike :methodAdvanced || '%')";
				$queryParams["methodAdvanced"] = $data["methodAdvanced"];
			}
			if (!empty($data["ET_from"])) {
				$filters[] = "PL[N].ET >= :ET_from";
				$queryParams["ET_from"] = $data["ET_from"];
			}
			if (!empty($data["ET_to"])) {
				$filters[] = "PL[N].ET <= :ET_to";
				$queryParams["ET_to"] = $data["ET_to"];
			}
			if (!empty($data["ET_Query_from"])) {
				$filters[] = "ROUND(PL[N].ET_Query::numeric, 4) >= :ET_Query_from";
				$queryParams["ET_Query_from"] = $data["ET_Query_from"];
			}
			if (!empty($data["ET_Query_to"])) {
				$filters[] = "ROUND(PL[N].ET_Query::numeric, 4) <= :ET_Query_to";
				$queryParams["ET_Query_to"] = $data["ET_Query_to"];
			}
			if (!empty($data["PMUser_Login"])) {
				$filters[] = "PL[N].pmUser_insID in (select PMUser_id from promed.pmUserCache where PMUser_Login ilike '{$data["PMUser_Login"]}%' and pmuser_deleted = 1)";
			}
			if (!empty($data["IP"])) {
				$filters[] = "PL[N].IP = :IP";
				$queryParams["IP"] = $data["IP"];
			}
			if (!empty($data["Server_IP"])) {
				$filters[] = "PL[N].Server_IP = :Server_IP";
				$queryParams["Server_IP"] = $data["Server_IP"];
			}
			if (!empty($data["AnswerError"])) {
				$filters[] = ($data["AnswerError"] > 1)
					? "PL[N].AnswerError IS NOT NULL"
					: "PL[N].AnswerError IS NULL";
			}
			if (!empty($data["POST"])) {
				$filters[] = "lower(PL[N].POST) like '%' || lower(:POST) || '%'";
				$queryParams["POST"] = $data["POST"];
			}

			$querys = [];
			$n = 1;
			$nCount = 0;
			$filterString = (count($filters) != 0) ? implode(" and ", $filters) : "";
			foreach ($tables as $table) {
				$subFilterString = str_replace("[N]", $n, $filterString);
				$dateFilter = "";
				if (!empty($data["PHPLog_insDT"][0]) && !empty($data["PHPLog_insDT"][1])) {
					if (count($tables) == 1) {
						$dateFilter = "cast(PL{$n}.PHPLog_insDT as date) >= :PHPLog_insDT_from and cast(PL{$n}.PHPLog_insDT as date) <= :PHPLog_insDT_to";
						$queryParams["PHPLog_insDT_from"] = $data["PHPLog_insDT"][0];
						$queryParams["PHPLog_insDT_to"] = $data["PHPLog_insDT"][1];
					} else {
						if ($nCount == 0) {
							$dateFilter = "cast(PL{$n}.PHPLog_insDT as date) >= :PHPLog_insDT_from";
							$queryParams["PHPLog_insDT_from"] = $data["PHPLog_insDT"][0];
						} elseif ($nCount == count($tables) - 1) {
							$dateFilter = "cast(PL{$n}.PHPLog_insDT as date) <= :PHPLog_insDT_to";
							$queryParams["PHPLog_insDT_to"] = $data["PHPLog_insDT"][1];
						}
					}
				}
				$dateFilter = (trim($subFilterString) != "" && trim($dateFilter) != "") ? " and {$dateFilter}" : $dateFilter;
				if (trim($subFilterString) != "") {
					$subFilterString = "where {$subFilterString}";
				} else {
					if (trim($dateFilter) != "") {
						$dateFilter = "where {$dateFilter}";
					}
				}
				$querys[] = "
					select
						PL{$n}.PHPLog_id as PHPLog_id,
					    PL{$n}.PHPLog_insDT as PHPLog_insDT,
					    PL{$n}.Controller_ID as Controller_ID,
					    PL{$n}.Method_ID as Method_ID,
					    PL{$n}.ET as ET,
					    PL{$n}.QueryString as QueryString,
					    ROUND(PL{$n}.ET_Query::numeric, 4) as ET_Query,
					    PL{$n}.IP as IP,
					    PL{$n}.Server_IP as Server_IP,
					    PL{$n}.AnswerError as AnswerError,
					    PL{$n}.POST as POST,
					    PL{$n}.pmUser_insID as PMUser_id,
					    PL{$n}.ARMType_id as ARMType_id
					from {$table} PL{$n}
					{$subFilterString}
					{$dateFilter}
				";
				$nCount++;
			}
			$queryString = implode("\nunion all\n", $querys);
			$query = "
				select
					-- select
					Q.PHPLog_id as \"PHPLog_id\",
				    to_char(Q.PHPLog_insDT, 'YYYY-MM-DD HH24:MI:SS') as \"PHPLog_insDT\",
				    CTS.Controller_Name || ' ('|| CTS.Controller_Name_Ru ||')' as \"Controller\",
				    MTS.Method_Name as \"Method\",
				    MTS.Method_Name_Ru as \"Method_Name_Ru\",
				    Q.ET as \"ET\",
				    Q.QueryString as \"QueryString\",
				    ROUND(Q.ET_Query::numeric, 4) as \"ET_Query\",
				    Q.IP as \"IP\",
				    Q.Server_IP as \"Server_IP\",
				    Q.AnswerError as \"AnswerError\",
				    Q.POST as \"POST\",
				    Q.PMUser_id as \"PMUser_id\",
				    PUC.PMUser_Login as \"PMUser_Login\",
				    ARMT.ARMType_Name as \"ARMType_Name\"
					-- end select
				from
					-- from
					({$queryString}) Q
					left join promed.pmUserCache PUC on PUC.PMUser_id = Q.PMUser_id
					left join Controllers CTS on CTS.Controller_ID = Q.Controller_ID
					left join Methods MTS on MTS.Method_ID = Q.Method_ID
					left join promed.ARMType ARMT on ARMT.ARMType_id = Q.ARMType_id
					-- end from
				order by
					-- order by
					Q.PHPLog_insDT desc
					-- end order by
			";
			return $this->getPagingResponse($query, $queryParams, $data["start"], $data["limit"], true);
		} else {
			$filters = [];
			$queryParams = [];
			$joinMethods = " left join Methods MTS on MTS.Method_Name = PL.Method ";
			if (!empty($data["PHPLog_insDT"][0]) && !empty($data["PHPLog_insDT"][1])) {
				$filters[] = "cast(PL.PHPLog_insDT as date) >= :PHPLog_insDT_from and cast(PL.PHPLog_insDT as date) <= :PHPLog_insDT_to";
				$queryParams["PHPLog_insDT_from"] = $data["PHPLog_insDT"][0];
				$queryParams["PHPLog_insDT_to"] = $data["PHPLog_insDT"][1];
			}
			if (!empty($data["Controller"])) {
				$filters[] = "(PL.Controller ilike :Controller || '%' or CTS.Controller_Name_Ru ilike :Controller || '%')";
				$queryParams["Controller"] = $data["Controller"];
			}
			if (!empty($data["Method"])) {
				$filters[] = "(PL.Method ilike :Method || '%' or MTS.Method_Name_Ru ilike :Method || '%')";
				$queryParams["Method"] = $data["Method"];
			}
			if (!empty($data["methodAdvanced"])) {
				$filters[] = "(PL.Method ilike :methodAdvanced || '%' or MTS.Method_Name_Ru ilike :methodAdvanced || '%')";
				$queryParams["methodAdvanced"] = $data["methodAdvanced"];
			}
			if (!empty($data["ET_from"])) {
				$filters[] = "PL.ET >= :ET_from";
				$queryParams["ET_from"] = $data["ET_from"];
			}
			if (!empty($data["ET_to"])) {
				$filters[] = "PL.ET <= :ET_to";
				$queryParams["ET_to"] = $data["ET_to"];
			}
			if (!empty($data["ET_Query_from"])) {
				$filters[] = "ROUND(PL.ET_Query::numeric, 4) >= :ET_Query_from";
				$queryParams["ET_Query_from"] = $data["ET_Query_from"];
			}
			if (!empty($data["ET_Query_to"])) {
				$filters[] = "ROUND(PL.ET_Query::numeric, 4) <= :ET_Query_to";
				$queryParams["ET_Query_to"] = $data["ET_Query_to"];
			}
			if (!empty($data["PMUser_Login"])) {
				$filters[] = "PUC.PMUser_Login ilike :PMUser_Login || '%'";
				$queryParams["PMUser_Login"] = $data["PMUser_Login"];
			}
			if (!empty($data["IP"])) {
				$filters[] = "PL.IP = :IP";
				$queryParams["IP"] = $data["IP"];
			}
			if (!empty($data["Server_IP"])) {
				$filters[] = "PL.Server_IP = :Server_IP";
				$queryParams["Server_IP"] = $data["Server_IP"];
			}
			if (!empty($data["AnswerError"])) {
				$filters[] = ($data["AnswerError"] > 1)
					? "PL.AnswerError IS NOT NULL"
					: "PL.AnswerError IS NULL";
			}

			if(!empty($data['isUsePersonData']) && $data['isUsePersonData'] == 2 ) {
				$filters[] = "MTS.Methods_IsUsePersonData = 1";
				$joinMethods = " right join Methods MTS on MTS.Method_Name = PL.Method "; //так запрос выполнится быстрее
			}
			
			if (!empty($data["POST"])) {
				$filters[] = "PL.POST ilike '%' || :POST || '%'";
				$queryParams["POST"] = $data["POST"];
			}
			if (!empty($data["ARMType"])) {
				$queryRow = $this->getFirstRowFromQuery("select ARMType_id from promed.ARMType where ARMType_Name = '{$data["ARMType"]}'");
				$filters[] = "PL.ARMType_id = :ARMType";
				$queryParams["ARMType"] = $queryRow["ARMType_id"];
			}
			$filterString = (count($filters) != 0)
				? "
			where
				-- where
				" . implode(" and ", $filters) . "
				-- end where
			"
				: "";
			$join = ($this->isNew()) ? "left join promed.ARMType ARMT on ARMT.ARMType_id = PL.ARMType_id" : "";
			$fieldString = ($this->isNew()) ? ",ARMT.ARMType_Name as \"ARMType_Name\"" : "";
			$query = "
				select
					-- select
					PL.PHPLog_id as \"PHPLog_id\",
				    to_char(PL.PHPLog_insDT, 'YYYY-MM-DD HH24:MI:SS') as \"PHPLog_insDT\",
				    PL.Controller || ' ('|| CTS.Controller_Name_Ru ||')' as \"Controller\",
				    PL.Method || (case when MTS.Method_Name_Ru is not null then ' ('|| MTS.Method_Name_Ru ||')' else '' end) as \"Method\",
				    MTS.Method_Name_Ru as \"Method_Name_Ru\",
				    PL.ET as \"ET\",
				    PL.QueryString as \"QueryString\",
				    ROUND(PL.ET_Query::numeric, 4) as \"ET_Query\",
				    PL.IP as \"IP\",
				    PL.Server_IP as \"Server_IP\",
				    PL.AnswerError as \"AnswerError\",
				    PL.POST as \"POST\",
				    PL.pmUser_insID as \"PMUser_id\",
				    PUC.PMUser_Login as \"PMUser_Login\",
				    CASE
						WHEN MTS.Methods_IsUsePersonData = 1 THEN 'true'
						ELSE 'false'
					END AS \"Methods_IsUsePersonData\"
				    {$fieldString}
					-- end select
				from
					-- from
					{$this->logs_table} PL
					left join promed.pmUserCache PUC on PUC.PMUser_id = PL.pmUser_insID
					left join promed.Controllers CTS on CTS.Controller_Name = PL.Controller
					{$joinMethods}
					{$join}
					-- end from
				{$filterString}
				order by
					-- order by
					PL.PHPLog_id desc
					-- end order by
			";
			return $this->getPagingResponse($query, $queryParams, $data["start"], $data["limit"], true);
		}
	}
}