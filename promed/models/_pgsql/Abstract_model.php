<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Abstract_model - Модель сферического объекта в вакууме
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       IGabdushev
 * @version      12 2011
 *
 * @property ModelGenerator_model ModelGenerator_model
 * @property CI_DB_driver $db
 */
abstract class Abstract_model extends swPgModel
{
	protected abstract function canDelete();
	private $rawLoadResult = [];
	public $transactional = true;//Разрешить объекту начинать/откатывать/коммитить

	protected function start_transaction()
	{
		if (!$this->transactional) {
			return true;
		}
		return $this->start_transaction();
	}

	protected function commit()
	{
		if (!$this->transactional) {
			return true;
		}
		return $this->commitTransaction();
	}

	protected function rollback()
	{
		if (!$this->transactional) {
			return true;
		}
		return $this->rollbackTransaction();
	}

	/**
	 * @abstract
	 * @return string
	 */
	protected function getKeyFieldName()
	{
		return $this->getTableName() . "_id";
	}

	/**
	 * @abstract
	 * @return string
	 */
	protected function getSaveProcedureName()
	{
		return "p_" . $this->getTableName();
	}

	/**
	 * @return string
	 */
	protected function getSourceTableName()
	{
		return "v_" . $this->getTableName();
	}

	abstract protected function getTableName();
	protected $scheme = "dbo";
	protected $fields = [];
	protected $valid;
	private $errors = [];

	protected function invalidate()
	{
		$this->valid = false;
	}

	/**
	 * @abstract
	 * @return Abstract_model
	 */
	abstract public function validate();

	/**
	 * Годно ли к сохранению
	 * @return bool
	 */
	public function isValid()
	{
		return $this->valid;
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	protected function clearErrors()
	{
		$this->errors = [];
	}

	public function addError($errmsg)
	{
		$this->valid = false;
		$this->errors[] = $errmsg;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return (array_key_exists($name, $this->fields)) ? $this->fields[$name] : parent::__get($name);
	}

	/**
	 * @return mixed
	 */
	public function getKeyFieldValue()
	{
		return $this->__get($this->getKeyFieldName());
	}

	/**
	 * @param $value
	 * @throws Exception
	 */
	public function setKeyFieldValue($value)
	{
		$this->__set($this->getKeyFieldName(), $value);
	}

	/**
	 * @param $name
	 * @param $value
	 * @throws Exception
	 */
	public function __set($name, $value)
	{
		if (!array_key_exists($name, $this->fields)) {
			throw new Exception("Попытка присвоить значение $value несуществующему полю $name");
		}
		$this->fields[$name] = $value;
		$this->invalidate();
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function __isset($name)
	{
		return isset($this->fields[$name]);
	}

	/**
	 * Заполняет модель модель переданными в массиве значениями (ключ массива соответствует имени поля в модели)
	 * @param $values
	 */
	protected function assign($values)
	{
		foreach ($this->fields as $field_name => $field_value) {
			if (array_key_exists($field_name, $values)) {
				$this->fields[$field_name] = $values[$field_name];
			}
		}
		$this->invalidate();
	}

	/**
	 * @param string $params
	 * @param array $data
	 * @return string
	 * для переопределения в _pgsql/EvnLabSample_model
	*/
	function processSaveParams($params, $data)
	{
		return $params;
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function save($data = [])
	{
		if (!$this->validate()->isValid()) {
			throw new Exception("При сохранении произошли ошибки: <br/>" . implode("<br/>", $this->getErrors()));
		}
		$proc = $this->chooseSaveProcedure();
		$params = [];
		list($params, $query_paramlist) = $this->getParamList($params, []);

		$query_paramlist = $this->processSaveParams($query_paramlist, $params);

		$query = "
			select p.name
			from (
				select
					unnest(p.proargnames) as name,
					unnest(p.proargmodes) as direction
				from 
					pg_catalog.pg_proc p
					inner join pg_catalog.pg_namespace s on s.oid = p.pronamespace
				where 
					lower(s.nspname) = lower(:scheme)
					and lower(p.proname) = lower(:proc) 
			) p
			where p.direction in ('b','o')
		";
		$fields = $this->queryList($query, array(
			'scheme' => $this->scheme,
			'proc' => $proc
		));

		if (!is_array($fields)) {
			throw new Exception('Ошибка при получении параметров процедуры');
		}

		$out_paramlist = array(
			"Error_Code as \"Error_Code\"",
			"Error_Message as \"Error_Msg\""
		);

		foreach($params as $field => $value) {
			if (in_array(strtolower($field), $fields)) {
				$out_paramlist[] = "{$field} as \"{$field}\"";
			}
		}

		$out_paramlist = implode(",\n", $out_paramlist);

		$query = "
			select
			 	{$out_paramlist}
			from {$this->scheme}.{$proc} (
				{$query_paramlist}
			);
		";
		try {
			/**@var CI_DB_result $dbresponse */
			$dbresponse = $this->db->query($query, $params);
			if (!is_object($dbresponse)) {
				throw new Exception("При сохранении произошли ошибки");
			}
			$result = $dbresponse->result("array");
			if (isset($result[0])) {
				$this->__set($this->getKeyFieldName(), $result[0][$this->getKeyFieldName()]);
			}
		} catch (Exception $e) {
			throw new Exception("При вызове процедуры сохранения произошли ошибки: <br> " . $e->getCode() . " " . $e->getMessage());
		}
		return $result;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function chooseSaveProcedure()
	{
		if ($this->__get($this->getKeyFieldName()) > 0) {
			$proc = $this->getSaveProcedureName() . "_upd";
		} else {
			$proc = $this->getSaveProcedureName() . "_ins";
			$this->__set($this->getKeyFieldName(), null);
		}
		return $proc;
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function delete($data = [])
	{
		if (!$this->canDelete()) {
			throw new Exception("При удалении произошли ошибки: <br/>" . implode("<br/>", $this->getErrors()));
		}
		if ($this->__get($this->getKeyFieldName()) == 0) {
			throw new Exception("Попытка удалить запись с неправильным идентификатором: " . $this->__get($this->getKeyFieldName()));
		}
		$proc = $this->getSaveProcedureName() . "_del";
		$params = [
			$this->getKeyFieldName() => $this->__get($this->getKeyFieldName())
		];
		$query = "
			select * from {$this->scheme}.{$proc}(
			    {$this->getKeyFieldName()} := :{$this->getKeyFieldName()}
			);
		";
		/**@var CI_DB_result $dbresponse */
		$dbresponse = $this->db->query($query, $params);
		if (!is_object($dbresponse)) {
			throw new Exception("При удалении произошли ошибки");
		}
		return $dbresponse->result("array");
	}

	/**
	 * @param null $field
	 * @param null $value
	 * @param string $selectFields
	 * @param bool $addNameEntries
	 * @return array|bool
	 */
	function load($field = null, $value = null, $selectFields = "t.*", $addNameEntries = true)
	{
		if ($field && $value) {
			$params = [$field => $value];
			$where = "t.{$field} = :{$field}";
		} else {
			$params = [$this->getKeyFieldName() => $this->__get($this->getKeyFieldName())];
			$where = "{$this->getKeyFieldName()} = :{$this->getKeyFieldName()}";
		}
		$nameEntries = "";
		$joins = "";
		if ($addNameEntries) {
			$this->load->model("ModelGenerator_model", "ModelGenerator_model");
			$tableFields = $this->ModelGenerator_model->getTableInfo($this->getTableName());
			$nameEntries_t = [];
			$joins_t = [];
			foreach ($tableFields as $tableField) {
				if ($tableField["ref_name"]) {
					$nameEntries_t[] = $tableField["column_name"] . "_ref" . "." . $tableField["ref_table"] . "_Name as " . $tableField["column_name"] . "_Name";
					$joins_t[] = "left join v_" . $tableField["ref_table"] . " " . $tableField["column_name"] . "_ref" . " on " .
						$tableField["column_name"] . "_ref." . $tableField["ref_col"] . " = t." . $tableField["column_name"];
				}
			}
			$nameEntries = "," . implode(",", $nameEntries_t);
			$joins = implode(PHP_EOL, $joins_t);
		}
		$query = "
   			select
                $selectFields
                {$nameEntries}
   			from
   		        {$this->scheme}.{$this->getSourceTableName()} t
   		        {$joins}
   		    where
   				$where
   		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result("array");
		if (isset($response[0])) {
			$this->rawLoadResult = $response[0];
			$this->assign($response[0]);
		} else {
			log_message("error", __CLASS__ . "::load() error: no any found. query:" . $query . " params: " . var_export($params, true));
		}
		return $response;
	}

	/**
	 * Получение массива параметров и списка аргументов(строка) для вызова хранимки
	 * @param $params - Массив параметров со значениями
	 * @param $query_paramlist_exclude - Массив имен параметров, которые надо исключить из строки со списком аргументов
	 * @return array
	 */
	protected function getParamList($params, $query_paramlist_exclude)
	{
		$query_paramlist = "";
		foreach ($this->fields as $key => $value) {
			$params[$key] = $value;
			if (!in_array($key, $query_paramlist_exclude)) {
				if (strlen($query_paramlist) > 0) {
					$query_paramlist = $query_paramlist . PHP_EOL;
				}
				$query_paramlist = $query_paramlist . " $key := :$key,";
			}
		}
        $query_paramlist = rtrim($query_paramlist, ',');
		$params[$this->getKeyFieldName()] = $this->fields[$this->getKeyFieldName()];
		return [$params, $query_paramlist];
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Проверяет заполнено ли обязательное поле
	 * @param $field
	 * @param $label
	 * @return bool
	 */
	function validateRequired($field, $label)
	{
		if ($this->$field === null) {
			$this->addError("Поле \"{$label}\" обязательно для заполнения.");
			return false;
		}
		return true;
	}

	/**
	 * Проверяет существование значия поля в справочнике
	 *
	 * @param string $reference
	 * @param string $label Метка поля на форме
	 *
	 * @return bool
	 */
	function validateByReference($reference, $label)
	{
		$id = $reference . "_id";
		$table = "v_" . $reference;
		$result = $this->getFirstResultFromQuery("select $id from dbo.$table where $id = :$id", [$id => $this->$id]);
		if (!$result) {
			$this->addError("Значение поля \"{$label}\" не найдено в справочнике.");
			return false;
		}
		return true;
	}

	/**
	 * @param $object
	 * @return string
	 */
	function getNameRetrieveFieldListEntry($object)
	{
		return "(select {$object}_Name from v_{$object} d223 where d223.{$object}_id = t.{$object}_id) as {$object}_Name";
	}

	/**
	 * Проверяет на правильность значения да/нет
	 * @param $field
	 * @param $label
	 * @return bool
	 */
	function validateYesNo($field, $label)
	{
		$result = $this->getFirstResultFromQuery("select YesNo_id from dbo.v_YesNo where YesNo_id = :id", ["id" => $this->$field]);
		if (!$result) {
			$this->addError("Значение поля \"{$label}\" не найдено в справочнике.");
			return false;
		}
		return true;
	}

	/**
	 * @static
	 * @param $save_response array
	 * @return bool
	 */
	public static function save_ok($save_response)
	{
		$result = false;
		if (is_array($save_response)) {
			if (isset($save_response[0])) {
				$result = ((empty($save_response[0]["Error_Code"])) && (empty($save_response[0]["Error_Msg"])));
			}
		} else {
			if ($save_response === true) {
				$result = true;
			}
		}
		return $result;
	}

	/**
	 * @param $rawLoadResult
	 */
	public function setRawLoadResult($rawLoadResult)
	{
		$this->rawLoadResult = $rawLoadResult;
	}

	/**
	 * @return array
	 */
	public function getRawLoadResult()
	{
		return $this->rawLoadResult;
	}
}