<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 *
 * @property string $workingDir
 * @property array $queries
 * @property CI_DB_driver $db
*/
class QueryToDbfExporter_model extends swPgModel
{
	private $queries = [];
	const OUT_SUBDIR = "QueryToDbf/";//Каталог для сохранения dbf-файлов. Обязательно со слешем на конце!
	const SESSION_KEY = "QueryToDbfExporter_session";//ключ для хранения всяких временных данных в сессии

	/**
	 * QueryToDbfExporter_model constructor.
	 * @throws Exception
	 */
	public function __construct()
	{
		parent::__construct();
		if (!array_key_exists($this::SESSION_KEY, $_SESSION)) {
			$_SESSION[$this::SESSION_KEY] = [];
		}
		if (!array_key_exists("done", $_SESSION[$this::SESSION_KEY])) {
			$_SESSION[$this::SESSION_KEY]["done"] = [];
		}
		$this->workingDir = EXPORTPATH_ROOT . self::OUT_SUBDIR;
		if (!is_dir($this->workingDir)) {
			if (!$this->mkdir($this->workingDir)) {
				throw new Exception("can't create dir " . $this->workingDir);
			}
		}
		$this->declareQueries();
	}

	/**
	 * @throws Exception
	 */
	private function declareQueries()
	{
		$query = "
			select
				Query_id as \"Query_id\",
			    Query_Nick as \"Query_Nick\",
			    Filename as \"Filename\",
			    Name as \"Name\",
			    Query as \"Query\",
			    Ord as \"Ord\"
			from rls.exp_Query
			where Region_id is null
				or Region_id = dbo.getRegion()
			order by ord
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query);
		if (!is_object($result)) {
			throw new Exception("Не удалось выполнить запрос: " . $query);
		}
		$result = $result->result("array");
		foreach ($result as $resultRow) {
			$this->queries[$resultRow["Query_Nick"]] = [
				"filename" => $resultRow["Filename"],
				"name" => $resultRow["Name"],
				"query" => $resultRow["Query"],
			];
			$query1 = "
				select
					DbaseStructure_id as \"DbaseStructure_id\",
					Query_id as \"Query_id\",
					trim(Query_ColumnName) as \"Query_ColumnName\",
					trim(Dbase_ColumnName) as \"Dbase_ColumnName\",
					Dbase_ColumnType as \"Dbase_ColumnType\",
					Dbase_ColumnLength as \"Dbase_ColumnLength\",
					Dbase_ColumnPrecision as \"Dbase_ColumnPrecision\",
					Description as \"Description\",
					Ord as \"Ord\"
				from rls.exp_DbaseStructure
				where Query_id = :Query_id
				order by ORD
			";
			$queryParams1 = ["Query_id" => $resultRow["Query_id"]];
			$cols = $this->db->query($query1, $queryParams1);
			if (!is_object($cols)) {
				throw new Exception("Не удалось выполнить запрос: " . getDebugSQL($query1, $queryParams1) . "<br />" . PHP_EOL . var_export(sqlsrv_errors(), true));
			}
			$cols = $cols->result("array");
			foreach ($cols as $col) {
				$this->queries[$resultRow["Query_Nick"]]["dbf_structure"][] = [
					$col["Dbase_ColumnName"],
					$col["Dbase_ColumnType"],
					$col["Dbase_ColumnLength"],
					$col["Dbase_ColumnPrecision"],
					"source_column" => $col["Query_ColumnName"],
					"name" => $col["Description"],
				];
			}
		}
	}

	/**
	 * @return mixed
	 */
	private function getDoneFilesList()
	{
		return $_SESSION[$this::SESSION_KEY]["done"];
	}

	/**
	 * @param $filename
	 */
	private function addDoneFile($filename)
	{
		$_SESSION[$this::SESSION_KEY]["done"][] = $filename;
	}

	/**
	 * @return array
	 */
	public function resetDoneFilesList()
	{
		$_SESSION[$this::SESSION_KEY]["done"] = [];
		return ["success" => true, "Error_Msg" => ""];
	}

	/**
	 * Рекурсивное создание пути.
	 * @param $dir
	 * @param int $mode
	 * @return bool
	 */
	private function mkdir($dir, $mode = 0755)
	{
		if (is_dir($dir) || @mkdir($dir, $mode)) {
			return TRUE;
		}
		if (!$this->mkdir(dirname($dir), $mode)) {
			return FALSE;
		}
		return @mkdir($dir, $mode);
	}

	/**
	 * Получение списка доступных запросов
	 * @return array
	 */
	public function getQueryList()
	{
		$result = [];
		foreach ($this->queries as $query_nick => $query) {
			$result[] = [
				"query_nick" => $query_nick,
				"query_name" => $query["name"]
			];
		}
		return $result;
	}

	/**
	 * Выполнение запроса и экспорт его результатов в dbf-файл согласно настройкам
	 * @param $query_name
	 * @param array $params
	 * @return array
	 * @throws Exception
	 */
	public function export($query_name, $params = [])
	{
		if (!array_key_exists($query_name, $this->queries)) {
			throw new Exception("Запрошен экспорт несуществующего запроса \"$query_name\"");
		}
		$data2export = $this->runQuery($this->queries[$query_name]["query"], $params);
		if (!is_array($data2export)) {
			throw new Exception("Ошибка выполнения запроса \"$query_name\"");
		}
		$dbf_full_name = $this->workingDir . $this->queries[$query_name]["filename"];
		$this->array2dbase($dbf_full_name, $this->queries[$query_name]["dbf_structure"], $data2export);
		$this->addDoneFile($dbf_full_name);
		return ["success" => true, "Error_Msg" => ""];
	}

	/**
	 * Сохранение массива в dbase
	 * @param $dbf_full_name
	 * @param $fields_dbf
	 * @param $response
	 * @throws Exception
	 */
	private function array2dbase($dbf_full_name, $fields_dbf, $response)
	{
		if (is_file($dbf_full_name)) {
			unlink($dbf_full_name);
		}
		$h = dbase_create($dbf_full_name, $fields_dbf);
		if (!$h) {
			throw new Exception("dbase_create() fails {$dbf_full_name}");
		}
		$add_ok = true;
		$cnt = 0;
		@trigger_error("");//сброс ошибки
		foreach ($response as $record) {
			$record = array_change_key_case($record, CASE_UPPER);
			foreach ($fields_dbf as $column) {
				$column[0] = strtoupper($column[0]);
				$column["source_column"] = (empty($column["source_column"])) ? $column[0] : strtoupper($column["source_column"]);
				switch ($column[1]) {
					case "D":
						if (!empty($record[$column["source_column"]])) {
							$record[$column["source_column"]] = explode('.', $record[$column["source_column"]])[0];
							$record[$column["source_column"]] = ConvertDateFormat($record[$column["source_column"]], 'Y-m-d H:i:s');
							$record[$column["source_column"]] = DateTime::createFromFormat('Y-m-d H:i:s', $record[$column["source_column"]]);
							if (!($record[$column["source_column"]] instanceOf DateTime)) {
								throw new Exception("Неверная дата в записи (" . implode(", ", $record) . ")");
							}
							/**@var Datetime $dt */
							$dt = $record[$column["source_column"]];
							$record[$column[0]] = $dt->format("Ymd");
						}
						break;
					case "C":
						if (!empty($record[$column["source_column"]])) {
							if ("object" == gettype($record[$column["source_column"]])) {
								throw new Exception("Поле {$column["source_column"]} в запросе для экспорта содержит данные типа " . get_class($record[$column["source_column"]]) . ", а поле назначения {$column[0]} в структуре DBF имеет тип {$column[1]}");
							}
							ConvertFromUtf8ToCp866($record[$column["source_column"]]);
							$record[$column[0]] = $record[$column["source_column"]];
						}
						break;
					default:
						if (!array_key_exists($column[0], $record)) {
							throw new Exception("В результатах выполнения запроса для выгрузки в файл {$dbf_full_name} отсутствует столбец {$column[0]}, описанный в структуре таблицы");
						}
						if (is_object($record[$column[0]])) {
							throw new Exception("Попытка записать объект без предварительного преобразования в строку. DBF-файл {$dbf_full_name} (Данные записи: " . var_export($record, true) . ")");
						}
						$record[$column[0]] = $record[$column["source_column"]];
				}
			}
			$add_ok = $add_ok && dbase_add_record($h, array_values($record));
			if (!$add_ok) {
				$err = error_get_last();
				$err = ("" !== $err["message"]) ? "Текст ошибки: {$err["message"]}, " : "";
				throw new Exception("Ошибка добавления записи в DBF-файл {$dbf_full_name} ({$err}Данные записи: " . var_export($record, true) . ")");
			} else {
				$cnt++;
			}
		}
		log_message("debug", "Записей добавлено в {$dbf_full_name}: {$cnt}");
		if (!dbase_close($h)) {
			throw new Exception("Не удалось сохранить изменения в {$dbf_full_name}");
		}
	}

	/**
	 * Выполнение запроса
	 * @param $query
	 * @param array $params
	 * @return array|bool
	 */
	private function runQuery($query, $params = [])
	{
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			log_message("error", __METHOD__ . ": query fails: " . getDebugSql($query, $params));
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Упаковка результатов (выполненных за сессию запросов) в zip-архив
	 * @return array|bool
	 * @throws Exception
	 */
	public function packResult()
	{
		$files = $this->getDoneFilesList();
		if (!is_array($files) || count($files) == 0) {
			throw new Exception("Упаковщик ZIP: в этой сессии нет выгруженных файлов");
		}
		do {
			$archivefilename = tempnam($this->workingDir, "exp");
			unlink($archivefilename);
		} while (is_file($archivefilename . ".zip"));
		$archivefilename = $archivefilename . ".zip";
		$result = true;
		$zip = new ZipArchive();
		$result = $result && $zip->open($archivefilename, ZIPARCHIVE::CREATE);
		if (!$result) {
			throw new Exception("Ошибка создания архива");
		}
		foreach ($files as $filename) {
			$result = $result && $zip->addFile($filename, basename($filename));
			if (!$result) {
				throw new Exception("Ошибка добавления файла в архив");
			}
		}
		$result = $result && $zip->close();
		if (!$result) {
			throw new Exception("Ошибка сохранения архива");
		}
		if (!$result) {
			throw new Exception("Непредвиденная ошибка при архивации результатов экспорта");
		}
		$this->resetDoneFilesList();
		$arch_base_name = pathinfo($archivefilename, PATHINFO_BASENAME);
		return ["success" => true, "filename" => $this->workingDir . $arch_base_name, "Error_Msg" => ""];
	}

	/**
	 * @return array
	 */
	public function getQueries()
	{
		return $this->queries;
	}
}