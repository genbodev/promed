<?php
require_once("Abstract_model.php");
/**
 * @property int RecordStatus_Code
 */
class Collection_model extends Abstract_model implements ArrayAccess
{
	private $items = [];
	private $inputRules;
	private $tableName = '';


	function __construct()
	{
		parent::__construct();
	}
	/**
	 * @return string
	 */
	protected function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * @param $tableName
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}

	/**
	 * @return bool
	 */
	protected function canDelete()
	{
		return true;
	}

	/**
	 * @return $this|Abstract_model
	 */
	public function validate()
	{
		$this->valid = true;
		return $this;
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->items);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->items[$offset];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->items[$offset] = $value;
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

	/**
	 * @return int
	 */
	public function itemsCount()
	{
		return count($this->items);
	}

	/**
	 * Сохранение всех элементов коллекции
	 * @param $addFieldsValues
	 * @return array|bool
	 * @throws Exception
	 */
	public function saveAll($addFieldsValues)
	{
		$result = true;
		foreach ($this->items as &$value) {
			$valueadv = array_merge($value, $addFieldsValues);
			$err = getInputParams($itemData, $this->inputRules, true, $valueadv);
			if (strlen($err) != 0) {
				throw new Exception("Ошибка при сохранении коллекции: " . str_replace(chr(13), " ", str_replace(chr(10), "<br> ", $err)));
			}
			$this->assign($itemData);
			$result = $this->save();
			// присваиваем сохранённые идешники всей коллекции
			$keyFieldValue = $this->getKeyFieldValue();
			if (!empty($keyFieldValue)) {
				$value[$this->getKeyFieldName()] = $keyFieldValue;
			}
			$save_ok = self::save_ok($result);
			if (!$save_ok) {
				throw new Exception("Ошибка при сохранении коллекции: " . $result[0]["Error_Code"] . " " . $result[0]["Error_Msg"]);
			}
		}
		return $result;
	}

	/**
	 * Сохранение только новых элементов
	 * @param $addFieldsValues
	 * @return array|bool
	 * @throws Exception
	 */
	public function saveAllNew($addFieldsValues)
	{
		$result = true;
		foreach ($this->items as &$value) {
			$valueadv = array_merge($value, $addFieldsValues);
			$err = getInputParams($itemData, $this->inputRules, true, $valueadv);
			if (strlen($err) != 0) {
				throw new Exception("Ошибка при сохранении коллекции: " . str_replace(chr(13), " ", str_replace(chr(10), "<br> ", $err)));
			}
			$this->assign($itemData);
			$result = $this->save();
			// присваиваем сохранённые идешники всей коллекции
			$keyFieldValue = $this->getKeyFieldValue();
			if (empty($keyFieldValue)) {
				$save_ok = self::save_ok($result);
				if (!$save_ok) {
					throw new Exception("Ошибка при сохранении коллекции: " . $result[0]["Error_Code"] . " " . $result[0]["Error_Msg"]);
				}
			}
		}
		return $result;
	}

	/**
	 * @param $field
	 * @param $value
	 * @param string $fieldList
	 */
	public function loadAll($field, $value, $fieldList = "*")
	{
		$result = parent::load($field, $value, $fieldList . ", 2 as RecordStatus_Code");
		$this->items = [];
		foreach ($result as $item) {
			$this->items[] = $item;
		}
	}

	/**
	 * @param $params
	 * @param $query_paramlist_exclude
	 * @return array
	 */
	protected function getParamList($params, $query_paramlist_exclude)
	{
		$query_paramlist_exclude[] = "RecordStatus_Code";
		return parent::getParamList($params, $query_paramlist_exclude);
	}

	/**
	 * @param array $data
	 * @return array|bool
	 * @throws Exception
	 */
	public function save($data = [])
	{
		if (!isset($this->RecordStatus_Code)) {
			throw new Exception("У элемента коллекции не установлен идентификатор состояния");
		}
		switch ((int)$this->RecordStatus_Code) {
			case 1:
				//запись не изменена - ничего делать не требуется
				$result = true;
				break;
			case 0:
			case 2:
				//запись создана/изменена
				$result = parent::save();
				break;
			case 3:
				//запись удалена
				$result = parent::delete();
				break;
			default:
				throw new Exception("Неизвестный идентификатор состяния элемента коллекции: " . $this->RecordStatus_Code);
		}
		return $result;
	}

	/**
	 * @param $json
	 * @return bool
	 */
	public function parseJson($json)
	{
		ConvertFromWin1251ToUTF8($json);
		$items = json_decode($json, true);
		if (!is_array($items)) {
			return false;
		}
		$this->items = $items;
		return true;
	}

	/**
	 * @param $inputRules
	 */
	public function setInputRules($inputRules)
	{
		if (is_array($inputRules)) {
			$this->fields = [];
			$this->inputRules = [];
			foreach ($inputRules as $rule) {
				if (!(isset($rule["onlyRule"]) && ($rule["onlyRule"]))) {
					$this->fields[$rule["field"]] = null;
				}
				$this->inputRules[] = $rule;
			}
		}
	}

	/**
	 * @param $items
	 */
	public function setItems($items)
	{
		$this->items = $items;
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param null $name
	 * @return array
	 */
	public function getInputRules($name = null)
	{
		return $this->inputRules;
	}
}