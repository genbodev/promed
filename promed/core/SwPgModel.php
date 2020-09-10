<?php
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Class SwPgModel
 *
 * Расширение модели SwModel для динамического подключения моделей через PostgresSQL
 *
 * @author Andrey Myslin (artdevision@gmail.com)
 *
 */
class SwPgModel extends SwModel
{
	protected $_db;

	protected $class_info;

	protected $origin_model;

	protected $schema = 'dbo';

	public $is_pg = true;

	public function __construct()
	{
		parent::__construct();

//        var_dump($this->db);
	}

	/**
	 * @return mixed
	 */
	public function getDb()
	{
		$ci = &get_instance();
		if (!empty($ci->db) && $ci->db->dbdriver == 'postgre') {
			$this->_db = $ci->db;
		} else if (empty($this->_db)) {
			$this->setDb();
		}
		return $this->_db;
	}

	/**
	 * @param string $name
	 */
	public function setDb($name = 'postgres')
	{
		parent::setDb($name);
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{

		if (method_exists($this, 'get' . ucfirst($name))) {
			$method = 'get' . ucfirst($name);
			return $this->$method();
		}
		return parent::__get($name);
	}

	/**
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 * @throws Error
	 */
	public function __call($method, $args) {
		if (empty($this->class_info)) {
			$this->class_info = new ReflectionClass($this);
		}

		if (empty($this->origin_model)) {
			$class_short_name = $this->class_info->getShortName();
			$parent_class_name = "Promed\\MSSQL\\{$class_short_name}";
			if (!class_exists($parent_class_name, false)) {
				$parent_class_name = str_replace("\\".ucfirst($this->regionNick)."_", "\\", $parent_class_name);
			}
			if (!class_exists($parent_class_name, false)) {
				throw new Exception("Method not found {$method} in class {$class_short_name}");
			}
			$this->origin_model = new $parent_class_name;
		}

		if (!method_exists($this->origin_model, $method)) {
			$class_name = get_class($this->origin_model);
			throw new Exception("Method not found {$method} in class {$class_name}");
		}
		return call_user_func_array(array($this->origin_model, $method), $args);
	}

	/**
	 * @param string $fields
	 * @param string $from
	 * @param string $joins
	 * @param string $where
	 * @param array $params
	 * @return array
	 */
	protected function _beforeQuerySavedData($fields, $viewName, $joins, $where, $params)
	{
		return array(
			'sql' => "
				select {$fields}
				from {$viewName}
				{$joins}
				where {$where}
			",
			'params' => $params,
		);
	}

	/**
	 * Дополнительная обработка значения атрибута сохраненного объекта из БД
	 * перед записью в модель
	 * @param string $column Имя колонки в строчными символами
	 * @param mixed $value Значение. Значения, которые в БД имеют тип datetime, являются экземлярами DateTime.
	 * @param string $prefix Префикс строчными буквами, например, 'set' или 'dis' или другой
	 * @return mixed
	 * @throws Exception
	 */
	protected function _processingDtValue($column, $value, $prefix)
	{
		$keyDT = $prefix . 'dt';
		if ( false !== strpos($column, $keyDT)) {
			$keyDate = $prefix . 'date';
			$keyTime = $prefix . 'time';
			$isAllowTime = $this->hasAttribute($keyTime);

			// пытаемся сконвертировать в DateTime
			if (!empty($value) && !($value instanceof DateTime)) {
				$format = 'Y-m-d H:i:s';
				$value = DateTime::createFromFormat($format, $value);
			}

			if ($value instanceof DateTime) {
				$this->_savedData[$this->_getColumnName($keyDate)] = $value->format('Y-m-d');
				if ($isAllowTime) {
					$this->_savedData[$this->_getColumnName($keyTime)] = $value->format('H:i');
				}
			} else if (empty($value)) {
				$this->_savedData[$this->_getColumnName($keyDate)] = null;
				if ($isAllowTime) {
					$this->_savedData[$this->_getColumnName($keyTime)] = null;
				}
			} else {
				throw new Exception('Неправильный формат значения даты времени ' . $keyDT, 500);
			}
		}
	}

	/**
	 * Запись данных атрибута объекта в БД
	 * @param array $queryParams Параметры запроса
	 * @return array Результат выполнения запроса
	 * @throws Exception В случае ошибки запроса или ошибки возвращенной хранимкой
	 */
	protected function _saveAttribute($updateTable, $paramName, $paramValue) {
		$this->db->trans_begin();

		try {
			$updateTable = strtolower($updateTable);
			$paramName = strtolower($paramName);

			$params = [
				'Evn_id' => $this->id,
				'paramValue' => $paramValue,
				'pmUser_id' => $this->promedUserId
			];

			$query = "
				update {$updateTable}
				set
					{$paramName} = :paramValue
				where
					evn_id = :Evn_id;
				update Evn
				set
					Evn_updDT = dbo.tzGetDate(), 
					pmUser_updID = :pmUser_id
				where
					Evn_id = :Evn_id;
			";
			$result = $this->db->query($query, $params);

			if (!$result){
				throw new Exception('Ошибка при работе с БД', 500);
			}

		} catch (Exception $e) {
			$this->db->trans_rollback();
			throw new Exception('Ошибка при работе с БД', 500);
		}
		$this->db->trans_commit();
		return [
			'Evn_id' => $this->id
		];
	}

	/**
	 * @desc Удаляет записи из зависимых таблиц
	 * @param array $main_id Ключ и значение идентификатора главной таблицы в формате array('key' => key, 'value' => value)
	 * @param array $linked_tables массив связанных таблиц, формат элементов array('schema' => schema, 'table' => table)
	 * @return type
	 */
	public function deleteRecordsFromLinkedTables($main_id, $linked_tables) {
		if (!is_array($linked_tables) || empty($main_id)) {
			return false;
		}

		foreach ($linked_tables as $row) {
			$query = "
                select
                    {$row['table']}_id as \"{$row['table']}_id\"
                from
                    {$row['schema']}.{$row['table']}
                where
                    {$main_id['key']} = {$main_id['value']}
            ";

			$result = $this->db->query( $query, $main_id);

			if ( is_object( $result ) ) {
				$response = $result->result('array');

				if (count($response) > 0 && !empty($response[0][$row['table']."_id"])){

					foreach ($response as $row_r) {
						foreach ($row_r as $key => $value) {
							$query = "
								select
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from {$row['schema']}.p_{$row['table']}_del (
									{$key} := {$value}
								);
							";

							//echo getDebugSQL($query, $row_r);
							$result = $this->db->query($query, $row_r);

							if (!is_object($result)) {
								return array('Error_Msg' => 'Ошибка при удалении эелемента связанной таблицы');
							}
						}
					}
				}
			} else {
				return array( 'Error_Msg' => 'Ошибка при удалении эелементов связанной таблицы' );
			}
		}
		return true;
	}

	/**
	 * Получение текущего времени с учетом временной зоны БД
	 * @return |null
	 */
	public function tzGetDate()
	{
		$query = "SELECT dbo.tzGetDate()";
		$res = $this->getFirstRowFromQuery($query);
		$DT = !empty($res['tzgetdate']) ? $res['tzgetdate'] : null;
		return $DT;
	}

	/**
	 * Обрабатывает входящие данные, проверяет их на ошибки
	 * @access public
	 * @param array $rules Массив правила для проверки входных данных.
	 * @param string $inData Сущестующие параметры.
	 * @param boolean $GetSessionParams По умолчанию: true. Установите false, если данные из сессии не требуется включать в входящие параметры.
	 * @param boolean $CloseSession По умолчанию: false. Установите true в случае, если нужно закрыть сессию после обработки входящих параметров (поскольку в большинстве случаев сессия не нужна).
	 * @param bool $PreferSession В первую очередь брать параметры из сессии
	 * @param bool $ParamsFromPost Брать параметры из $_POST
	 * @param bool $convertUTF8 Конвертировать входящие параметры из UTF 8
	 * @return array Обработанный массив входящих параметров
	 *
	 */
	public function checkInputData($rules, $inData, &$error, $GetSessionParams = true, $CloseSession = true, $PreferSession = false, $ParamsFromPost = false, $convertUTF8 = true)
	{
		$data = [];
		// Заменяем $_POST на $_GET, если $_POST пустой.
		if (!$ParamsFromPost) {
			if (empty($_POST) && (!empty($_GET))) {
				$_POST = $_GET;
			}
		}
		// Получаем сессионные переменные
		if ($GetSessionParams && (!$PreferSession)) {
			$data = array_merge($data, getSessionParams());
		}
		if (isset($rules)) {
			$err = getInputParams($data, $rules, $convertUTF8, $inData, true);
			if (strlen($err) > 0) {
				$error = $this->createError('', $err);
			}
		}
		if ($GetSessionParams && $PreferSession)
			$data = array_merge(getSessionParams(), $data);
		if ($GetSessionParams && isset($_SESSION)) {
			$data['session'] = $_SESSION;
		}
		if ($CloseSession)
			session_write_close();
		return $data;
	}

	/**
	 * Загрузка свойств модели из БД для чтения и проверок
	 * @param int $id
	 * @throws Exception
	 */
	function _load($id)
	{
		$this->_requestSavedData($id);
		foreach ($this->defAttribute as $key => $info) {
			if (in_array(self::PROPERTY_NOT_LOAD, $info['properties'])) {
				continue;
			}
			$name = $this->_getColumnName($key, $info);
			if (array_key_exists($name, $this->_savedData)) {
				if (in_array(self::PROPERTY_DATE_TIME, $info['properties']) && !empty($this->_savedData[$name])) {
					$dt = new DateTime($this->_savedData[$name]);
					$this->setAttribute($key, $dt);
					if (isset($info['dateKey'])) {
						$this->setAttribute($info['dateKey'], $dt->format('Y-m-d'));
					}
					if (isset($info['timeKey'])) {
						$this->setAttribute($info['timeKey'], $dt->format('H:i'));
					}
				} else {
					$this->setAttribute($key, $this->_savedData[$name]);
				}
			} else {
				throw new Exception('Не удалось получить данные поля '. $name, 500);
				//throw new Exception(var_export($this->defAttribute).var_export($this->_savedData));
			}
		}
	}


	/**
	 * Получение списка параметров хранимой процедуры
	 * @param string $sp - наименование хранимой процедуры
	 * @param string $schema - схема харнимой процедуры
	 * @return array|bool
	 */
	public function getStoredProcedureParamsList($sp, $schema)
	{
		$query = "
                select 
                  pg_catalog.pg_get_function_arguments(p.oid) as arguments
                from 
                  pg_catalog.pg_proc p
                  left join pg_catalog.pg_namespace n on n.oid = p.pronamespace
                where 
                  n.nspname = :scheme
                and 
                  p.proname = :proc
                limit 1
	      
	    ";
		$result = $this->db->query($query, array(
			'proc' => strtolower($sp),
			'scheme' => strtolower($schema)
		));

		if ( is_object($result) ) {
			$arguments = explode( ', ',$result->result('array')[0]['arguments']);
			$params = [];
			foreach ($arguments as $argument) {
				$argument = explode(' ', $argument);
				if($argument[0] === 'OUT') continue;
				if($argument[0] === 'INOUT') {
					$params[] = $argument[1];
					continue;
				}

				$params[] = $argument[0];
			}

			return $params;
		} else {
			return false;
		}
	}
	
	/**
	 * @param srting $schema
	 * @param string $table
	 * @return array|false
	 */
	function getTableColumnList($schema, $table) {
		$params = [
			'schema' => $schema,
			'table' => $table
		];
		$query = "
			select column_name
			from information_schema.columns
			where table_schema = :schema
			and lower(table_name) = lower(:table)
		";
		return $this->queryList($query, $params);
	}
	
	/**
	 * @param array $data
	 * @param string $schema
	 * @param string $table
	 * @param callable|null $additProcessing
	 * @return array
	 * @throws Exception
	 */
	function getJsonParamsForTable($data, $schema, $table, $additProcessing = null) {
		$columns = $this->getTableColumnList($schema, $table);
		if (!is_array($columns)) {
			throw new Exception("Ошибка при получении полей таблицы {$schema}.{$table}");
		}

		$jsonParams = [];
		foreach($data as $column => $value) {
			if (!is_array($value) && in_array(strtolower($column), $columns)) {
				if ($additProcessing) {
					$value = $additProcessing($column, $value);
				}
				$jsonParams[$column] = $value;
			}
		}

		return $jsonParams;
	}

	/**
	 * Вызывается процедура сохранение обьекта
	 *
	 * @param string $object_name
	 * @param array $data
	 * @return array|bool
	 * @throws Exception
	 */
	function saveObject($object_name, $data)
	{
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = preg_split('/\./', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		if (!isset($data[$key_field])) {
			$data[$key_field] = null;
		}

		$action = $data[$key_field] > 0 ? "upd" : "ins";
		$proc_name = "p_{$object_name}_{$action}";
		$params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		$save_data = [];
		$query_part = [];

		//получаем существующие данные если апдейт
		if ($action == "upd") {
			$query = "
				select
					*
				from
					{$schema}.v_{$object_name}
				where
					{$key_field} = :id;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'id' => $data[$key_field]
			));
			if (is_array($result)) {
				$save_data = $this->getProcedureData($result, $params_list);
			}
		}

		$save_data = array_merge($save_data, $this->getProcedureData($data,$params_list));

		if (empty($save_data['pmUser_id'])) {
			$save_data['pmUser_id'] = $data['pmuser_id'] ?? $this->getPromedUserId();
		}

		foreach($save_data as $key => $value) {
			if (in_array($key, $params_list)) {
				if (strtolower($key) == 'pmuser_id') {
					continue;
				}
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
					$save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
				}
				if(!isset($value)) {
					$query_part[] = "{$key} := null";
					continue;
				}

				$query_part[] = "{$key} := :{$key}";
			}
		}

		$query = "
            select 
                {$key_field} as \"{$key_field}\", 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
			from {$schema}.{$proc_name}
			(
				".implode($query_part, ', ').",
				pmUser_id := :pmUser_id
			)
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}

		$result = $this->db->query($query, $save_data);


		if(!$result) {
			throw new Exception('При сохранении произошла ошибка');
		}

		$result = $result->result('array');

		if(!empty($result[0]['Error_Msg'])) {
			throw new Exception ($result[0]['Error_Msg']);
		}

		$result = $result[0];
		if($result[$key_field] > 0) {
			$result['success'] = true;
		}

		return $result;
	}

	/**
	 * Копирование произвольного обьекта.
	 * @param string $object_name - наименование таблицы (при необходимости с указанием схемы)
	 * @param array $data - для указания идентификатора копируемой записи и данных, которые требуется изменить
	 * @return array|bool
	 */
	function copyObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		if (!isset($data[$key_field])) {
			return array('Error_Message' => 'Не указано значение ключевого поля');
		}

		$proc_name = "p_{$object_name}_ins";
		$params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		$save_data = array();
		$query_part = "";

		//получаем данные оригинала
		$query = "
			select
				*
			from
				{$schema}.{$object_name}
			where
				{$key_field} = :id
		";
		$result = $this->getFirstRowFromQuery($query, array(
			'id' => $data[$key_field]
		));
		if (is_array($result)) {
			foreach($result as $key => $value) {
				if (in_array($key, $params_list)) {
					$save_data[$key] = $value;
				}
			}
		}


		foreach($data as $key => $value) {
			$lower_key = strtolower($key);
			if (in_array($lower_key, $params_list)) {
				$save_data[$lower_key] = $value;
			}
		}

		foreach($save_data as $key => $value) {
			if (in_array($key, $params_list) && $key != strtolower($key_field) && !in_array($key, [$key_field, 'pmuser_id'])) {
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
					$save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
				}
				$query_part .= "{$key} := :{$key},\n\t\t\t\t";
			}
		}

		$save_data['pmuser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : $this->getPromedUserId();

		$query = "
			select
				{$key_field} as \"{$key_field}\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$schema}.{$proc_name}(
				{$query_part}
				pmUser_id := :pmuser_id
			)
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}
		$result = $this->getFirstRowFromQuery($query, $save_data);
		if ($result && is_array($result)) {
			if($result[$key_field] > 0) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При копировании произошла ошибка');
		}
	}

	/**
	 * Возврашает массив данных для процедур
	 *
	 * @param $data
	 * @param $params_list
	 * @return array
	 */
	protected function getProcedureData($data, $params_list)
	{
		$save_data = [];
		foreach($data as $key => $value) {
			$key = strtolower($key);
			if (in_array($key, $params_list)) {
				$save_data[$key] = $value;
			}
		}
		return $save_data;
	}

	/**
	 * Проверка на существование любой записи в таблице
	 * @param $object_name
	 * @param array $data - поля по которым будет происходить поиск в таблице
	 * @param string $schema - схема
	 * @return int|bool - возвращает ключ найденной записи или false
	 * @throws Exception
	 */
	function isExistObjectRecord($object_name,$data,$schema = 'dbo') {
		if( empty($object_name) || !is_array($data)) return false;

		$key_field = $object_name."_id";
		$params = [];
		$where = '';

		foreach($data as $key => $value) {
			$params[$key] = $value;
			$where .= " and {$key}=:{$key}";
		}

		$query =
			"select {$key_field} as \"{$key_field}\"
			from {$schema}.v_{$object_name}
			where (1=1) {$where}
			limit 1";
		return $this->getFirstResultFromQuery($query,$params);
	}

	/**
	 * Удаление произвольного обьекта.
	 * @param string $object_name - наименование таблицы (при необходимости с указанием схемы)
	 * @param array $data - массив должен содержать идентификатор удаляемой записи
	 * @return array|bool
	 */
	function deleteObject($object_name, $data) {
		$query = "
			select
				Error_Code as Error_Code,
				Error_Message as Error_Msg
			from dbo.p_{$object_name}_del (
				{$object_name}_id := :{$object_name}_id
			)
		";
		//echo getDebugSQL($query, $data);// exit();
		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			if(empty($result['Error_Msg'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При удалении произошла ошибка');
		}
	}

    /**
     * Получение идентификатора объекта по коду
     */
    function getObjectIdByCode($object_name, $code) {
        $schema = "dbo";

        //при необходимости выделяем схему из имени обьекта
        $name_arr = explode('.', $object_name);
        if (count($name_arr) > 1) {
            $schema = $name_arr[0];
            $object_name = $name_arr[1];
        }

        $query = "
			select
				{$object_name}_id
			from
				{$schema}.v_{$object_name}
			where
				{$object_name}_Code = :code
			order by
				{$object_name}_id
            limit 1
		";
        $id = $this->getFirstResultFromQuery($query, array(
            'code' => $code
        ));

        return $id && $id > 0 ? $id : null;
    }
}
