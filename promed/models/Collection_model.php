<?php
require_once('Abstract_model.php');
/**
 * @property int RecordStatus_Code
 */
class Collection_model extends Abstract_model implements ArrayAccess
{

	private $items = array();
	private $inputRules;
	private $tableName = '';

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	protected function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	protected function canDelete()
	{
		return true;
	}

	/**
	 * @return Abstract_model
	 */
	public function validate()
	{
		$this->valid = true;
		return $this;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean Returns true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->items);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return $this->items[$offset];
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->items[$offset] = $value;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function itemsCount(){
		return count($this->items);
	}

	/**
	 * Сохранение всех элементов коллекции
	 */
	public function saveAll($addFieldsValues){
		$result = true;
		foreach ($this->items as &$value) {
			$valueadv = array_merge($value, $addFieldsValues);
			$err = getInputParams($itemData, $this->inputRules, true, $valueadv);
			if (0==strlen($err)) {
				$this->assign($itemData);//parent::assign($itemData);
				$result = $this->save();
				// присваиваем сохранённые идешники всей коллекции
				$keyFieldValue = $this->getKeyFieldValue();
				if (!empty($keyFieldValue)) {
					$value[$this->getKeyFieldName()] = $keyFieldValue;
				}
				$save_ok = self::save_ok($result);
				if (!$save_ok) {
					throw new Exception('Ошибка при сохранении коллекции: '.$result[0]['Error_Code'].' '.$result[0]['Error_Msg']);
				}
			} else {
				throw new Exception('Ошибка при сохранении коллекции: '.str_replace(chr(13),' ', str_replace(chr(10),'<br> ', $err)) );
			}
		}
		return $result;
	}
	
	/**
	 * Сохранение только новых элементов
	 */
	public function saveAllNew($addFieldsValues){
		$result = true;
		foreach ($this->items as &$value) {
			$valueadv = array_merge($value, $addFieldsValues);
			$err = getInputParams($itemData, $this->inputRules, true, $valueadv);
			if (0==strlen($err)) {
				$this->assign($itemData);//parent::assign($itemData);
				$result = $this->save();
				// присваиваем сохранённые идешники всей коллекции
				$keyFieldValue = $this->getKeyFieldValue();
				if (empty($keyFieldValue)) {
					$save_ok = self::save_ok($result);
					if (!$save_ok) {
						throw new Exception('Ошибка при сохранении коллекции: '.$result[0]['Error_Code'].' '.$result[0]['Error_Msg']);
					}
				}
			} else {
				throw new Exception('Ошибка при сохранении коллекции: '.str_replace(chr(13),' ', str_replace(chr(10),'<br> ', $err)) );
			}
		}
		return $result;
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function loadAll($field, $value, $fieldList = '*'){
		$result = parent::load($field, $value, $fieldList.', 2 as RecordStatus_Code');
		$this->items = array();
		foreach ($result as $item) {
			$this->items[] = $item;
		}
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	protected function getParamList($params, $query_paramlist_exclude)
	{
		$query_paramlist_exclude[] = 'RecordStatus_Code';
		return parent::getParamList($params, $query_paramlist_exclude);
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function save($data = array()){
		if (isset($this->RecordStatus_Code)) {
			switch ((int)$this->RecordStatus_Code){
				case 1:
					//запись не изменена - ничего делать не требуется
					$result = true;
					break;
				case 0: case 2:
					//запись создана/изменена
					$result = parent::save();
					break;
				case 3:
					//запись удалена
					$result = parent::delete();
					break;
				default:
					throw new Exception('Неизвестный идентификатор состяния элемента коллекции: '.$this->RecordStatus_Code);
			}
			return $result;
		} else {
			throw new Exception('У элемента коллекции не установлен идентификатор состояния');
		}
	}

	/**
	 * @param $json
	 * @return bool
	 */
	public function parseJson($json){
		ConvertFromWin1251ToUTF8($json);
		$items = json_decode($json, true);
		if (is_array($items)) {
			$this->items = $items;
			$result = true;
		} else {
			$result = false;
		}
		return $result;
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setInputRules($inputRules)
	{
		if (is_array($inputRules)) {
			$this->fields = array();
			$this->inputRules = array();
			foreach ($inputRules as $rule) {
				if (!(isset($rule['onlyRule']) && ($rule['onlyRule']))) {
					$this->fields[$rule['field']] = null;
				}
				$this->inputRules[] = $rule;
			}
		}
	}

	
	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function setItems($items)
	{
		$this->items = $items;
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Некая абстрактная функция TODO: описать
	 */
	public function getInputRules($name = null)
	{
		return $this->inputRules;
	}
}
