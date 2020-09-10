<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 * @link		http://swan.perm.ru/PromedWeb
 * @version		15.07.2009
 */

/**
 * Расширение стандартной модели
 *
 * Расширенная версия модели, на будущее, пока никаких дополнительных действий не делается.
 *
 * @package		Library
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 *
 * @todo Реализовать подгрузку региональных классов модели с помощью автозагрузки классов
 * Сейчас это реализовано в swLoader, поэтому невозможно нормальное наследование
 * в региональных моделях, например,
 * есть EvnVizitPLStom_model extends EvnVizitPL_model
 * ufa_EvnVizitPL_model extends EvnVizitPL_model
 * ufa_EvnVizitPLStom_model extends EvnVizitPLStom_model
 * При текущей реализации ufa_EvnVizitPLStom_model не унаследует ничего из ufa_EvnVizitPL_model,
 * чтобы в ufa_EvnVizitPLStom_model использовать методы из ufa_EvnVizitPL_model
 * нужно загрузить EvnVizitPL_model с помощью swLoader
 * (проще реализовать региональные ветвления в EvnVizitPL_model)
 * PS а я так хотел отказаться от многочисленных региональных ветвлений
 * и использовать региональные модели
 *
 * Атрибуты общие для всех таблиц
 * @property int $id Идентификатор
 * @property DateTime $insDT Дата и время создания
 * @property int $pmUser_insID Идентификатор пользователя
 * @property DateTime $updDT Дата и время последнего обновления
 * @property int $pmUser_updID Идентификатор пользователя
 *
 * @property-read DateTime $currentDT
 * @property-read bool $isDebug
 * @property-read swLoader $load
 * @property-read array $defAttribute Ассоциативный массив описаний атрибутов объекта
 * @property-read array $attributes Ассоциативный массив атрибутов объекта в формате имя поля => значение
 * @property-read bool $isNewRecord
 * @property-read string $regionNick Параметр среды выполнения. Строковый код региона из списка $config['regions'] в \promed\config\config.php
 * @property-read array $options Ассоциативный массив настроек из LDAP
 * @property-read array $globalOptions Ассоциативный массив настроек из DataStorage
 * @property-read array $allOptions Ассоциативный массив настроек из DataStorage и LDAP
 * @property array $sessionParams Параметры из сессии
 * @property-read int $promedUserId Это pmUser_id, сделано так на случай, если где-то в таблице есть поле pmUser_id
 * @property-read string $scenario Сценарий бизнес-логики
 * @property-read array $scenarioList Список сценариев бизнес-логики, реализованных в модели
 * @property-read bool $usePostgre
 * @property-read bool $usePostgreLis
 */
class SwModel extends CI_Model
{
	const ID_KEY = 'id';
	/**
	 * Константы стандартных сценариев бизнес-логики.
	 * Используются для реализации проверок.
	 */
	/**
	 * Создание объекта с данными по умолчанию для дальнейшего редактирования.
	 * Большинство проверок игнорируется
	 */
	const SCENARIO_AUTO_CREATE = 'autoCreate';
	/**
	 * Программное обновление объекта.
	 * Большинство проверок игнорируется
	 */
	const SCENARIO_AUTO_UPDATE = 'autoUpdate';
	/**
	 * Сохранение объекта из формы редактирования,
	 * где есть возможность редактировать любой атрибут объекта
	 * Должны отработать все возможные проверки
	 */
	const SCENARIO_DO_SAVE = 'doSave';
	/**
	 * Запись значения одного атрибута из ЭМК или другого места
	 * Должны отработать проверки возможности записи значения
	 */
	const SCENARIO_SET_ATTRIBUTE = 'writeAttribute';
	/**
	 * Получение данных для формы редактирования
	 */
	const SCENARIO_LOAD_EDIT_FORM = 'doLoadEditForm';
	/**
	 * Получение данных для панели просмотра в ЭМК
	 */
	const SCENARIO_VIEW_DATA = 'viewData';
	/**
	 * Получение списка для грида
	 */
	const SCENARIO_LOAD_GRID = 'doLoadGrid';
	/**
	 * Сценарий сохранения грида
	 */
	const SCENARIO_DO_SAVE_GRID = 'doSaveGrid';
	/**
	 * Получение списка для комбика
	 */
	const SCENARIO_LOAD_COMBO_BOX = 'doComboBox';
	/**
	 * Удаление объекта
	 */
	const SCENARIO_DELETE = 'doDelete';
	/**
	 * Константы описания атрибутов
	 */
	/**
	 * Свойство атрибута. Означает, что в наименовании входящего параметра или хранимой процедуры
	 */
	const PROPERTY_NEED_TABLE_NAME = 'needTableName';
	/**
	 * Свойство атрибута. Означает, что атрибут только для чтения
	 */
	const PROPERTY_READ_ONLY = 'readOnly';
	/**
	 * Свойство атрибута. Означает, что значение атрибута может быть передано в хранимую процедуру
	 */
	const PROPERTY_IS_SP_PARAM = 'isParam';
	/**
	 * Свойство атрибута. Означает, что значение атрибута нельзя брать из входящих данных
	 */
	const PROPERTY_NOT_SAFE = 'notSafe';
	/**
	 * Свойство атрибута. Означает, что значение является объектом DateTime
	 */
	const PROPERTY_DATE_TIME = 'datetime';
	/**
	 * Свойство атрибута. Означает, что в поле таблицы нельзя записать NULL
	 * Если можно, то это не означает, что поле необязательное!
	 */
	const PROPERTY_NOT_NULL = 'notNull';
	/**
	 * Свойство атрибута. Означает, что не нужно выбирать значение из вьюхи при загрузке модели
	 */
	const PROPERTY_NOT_LOAD = 'notLoad';
	
	/**
	 * Да, нет из справочика YesNo. Неизменные на всех регионах.
	 */
	const YES_ID = 2;
	const NO_ID = 1;
	
	/**
	 * Значение чекбокса получаемое из запроса, если он отмечен
	 */
	const CHECKBOX_VAL = 'on';

	/**
	 * Разрешить начинать/откатывать/коммитить транзакцию
	 *
	 * Если транзакция была начата ранее, то нужно установить false
	 */
	public $isAllowTransaction = true;

	/**
	 * Массив значений атрибутов объекта,
	 * которые можно получить из представления сохраненного объекта
	 * Все значения доступны для чтения,
	 * запись значений должна производиться только в методах модели
	 * @var array
	 */
	private $_attributes = array();

	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = false;

	/**
	 * @var array
	 */
	protected $_savedData = array();
	/**
	 * Параметры модели, которые должны быть получены из входящих параметров,
	 * переданных из контроллера
	 *
	 * Всегда должна быть возможность получить значение session
	 * @var array
	 */
	protected $_params = array();
	/**
	 * Ассоциативный массив настроек из LDAP
	 * @var array
	 */
	private $_options = array();
	/**
	 * Ассоциативный массив настроек из LDAP
	 * @var array
	 */
	private $_globalOptions = array();

	/**
	 * Ассоциативный массив настроек из LDAP
	 * @var array
	 */
	private $_allOptions = array();

	/**
	 * Имя текущего сценария, определяющего правила валидации объекта
	 * @var string
	 */
	private $_scenario;

	/**
	 * Список имен сценариев, которые должны быть реализованы в модели
	 * @var array
	 */
	private $_scenarioList = array();

	/**
	 * @var array
	 */
	protected $_saveResponse = array(
		'Error_Msg' => null,
		'Error_Code' => null,
	);

	/**
	 * @var bool Флаг использования Монго
	 */
	protected $mongo_loaded = false;

	/**
	 * @var array Конфиг перевода запросов на MongoDb
	 */
	protected $mongo_switch = array();

	/**
	 * @var bool модель для БД PostgreSQL
	 */
	public $is_pg = false;

	/**
	 * @var CI_DB
	 */
	protected $_db = null;
	
	/**
	 * @var bool при сохранении передавать параметры через json
	 */
	protected $_useJsonParams = false;

	/**
	 * Волшебный метод для проверки наличия значения магического свойства
	 */
	function __isset($name)
	{
		$value = null;
		if ( method_exists($this, 'get' . ucfirst($name)) ) {
			$value = call_user_func(array($this, 'get' . ucfirst($name)));
		} else if ( $this->hasAttribute(strtolower($name)) ) {
			$value = $this->getAttribute(strtolower($name));
		} else {
			$value = parent::__get($name);
		}
		return isset($value);
	}

	/**
	 * Волшебный метод для чтения значения магического свойства
	 * Атрибуты возможно будут доступны и для чтения и для записи (сейчас пока только для чтения)
	 * Геттеры используются для property-read
	 */
	public function __get($name)
	{
		if ( method_exists($this, 'get' . ucfirst($name)) ) {
			return call_user_func(array($this, 'get' . ucfirst($name)));
		} else if ( $this->hasAttribute(strtolower($name)) ) {
			return $this->getAttribute(strtolower($name));
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * Сброс данных объекта,
	 * должен происходить при установке значения первичного ключа.
	 * Метод необходим, т.к. фреймворк создает объект в методе load,
	 * если бы объекты создавались инструкцией new,
	 * необходимости в этом методе не было бы.
	 */
	function reset()
	{
		$this->_params = array();
		$this->_savedData = array();
		$this->_attributes = array();
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 *
	 * Метод должен быть определен в каждой модели, наследующей этот класс.
	 *
	 * Ключи могут содержать только строчные символы.
	 * Если имя атрибута содержит наименование таблицы этого объекта,
	 * то в качестве ключа используется суффикс, например,
	 * для атрибута MyTable_setDT ключом будет setdt.
	 * Так сделано, чтобы наследование атрибутов происходило без наименований таблиц.
	 *
	 * Описание всегда должно иметь ключ properties
	 *
	 * В properties могут быть следующие строковые константы:
	 * PROPERTY_NEED_TABLE_NAME - в полном имени поля используется имя таблицы
	 * PROPERTY_READ_ONLY,
	 * PROPERTY_IS_SP_PARAM - это параметр хранимой процедуры
	 * PROPERTY_NOT_SAFE - не брать значение из входящих параметров
	 * PROPERTY_DATE_TIME - значение является экземпляром класса DateTime
	 * PROPERTY_NOT_LOAD - исключить поле из запроса для чтения
	 *
	 * Также в описании могут быть ключи:
	 * Которые используются для чтения из БД
	 * select - строка для выборки значения
	 * join - строка с джойном, в строке доступны плейсхолдеры {ViewName} и {PrimaryKey}
	 * Ключи, которые используются для получения значений из входящих параметров
	 * applyMethod - имя метода, с помощью которого значение извекается из входящих параметров
	 * Устанавливаем только то, что пришло
	 * alias - имя входящего параметра со значением атрибута указывается,
	 * если оно не производное от ключа или отличается по регистру, также как для inputRules;
	 * save - аналог rules в правилах в контроллере, но только для сохранения
	 * label - аналогично правилам в контроллере
	 * type - аналогично правилам в контроллере
	 * @return array
	 */
	static function defAttributes()
	{
		// Для примера так выглядит описание атрибутов стандартного редактируемого справочника
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => '_id',//указать в наследниках
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'id'
			),
			'code' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => '_Сode',//указать в наследниках
				'label' => 'Код',
				'save' => 'required',
				'type' => 'string'
			),
			'name' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => '_Name',//указать в наследниках
				'label' => 'Наименование',
				'save' => 'required',
				'type' => 'string'
			),
			'insdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'pmuser_insid' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'upddt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'pmuser_updid' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
		);
	}

	/**
	 * Получение описания атрибута(ов)
	 * @param string $key Ключ строчными символами, если не указан, то вернется массив описаний всех атрибутов
	 * @return array
	 * @throws Exception
	 */
	public function getDefAttribute($key = null)
	{
		$def = call_user_func(array(get_class($this),'defAttributes'));
		if (empty($key)) {
			return $def;
		}
		if (array_key_exists($key, $def)) {
			return $def[$key];
		}
		throw new Exception('Попытка получить описание не существующего атрибута ' . $key, 500);
	}

	/**
	 * @return array Массив значений атрибутов объекта
	 */
	public function getAttributes()
	{
		$attributes = array();
		foreach ($this->defAttribute as $key => $info) {
			$column = $this->_getColumnName($key, $info);
			$attributes[$column] = null;
			if ($this->_isAttributeChanged($key)) {
				$attributes[$column] = $this->_attributes[$key];
			} else if (array_key_exists($column, $this->_savedData)) {
				$attributes[$column] = $this->_savedData[$column];
			}
		}
		return $attributes;
	}

	/**
	 * Установка значения атрибута
	 * @param string $key Ключ
	 * @param mixed $value
	 */
	protected function setAttribute($key, $value)
	{
		$key = strtolower($key);
		if (empty($value) && 'server_id' != $key && false !== strpos($key, self::ID_KEY)) {
			$value = null;
		}
		if ($this->isNewRecord) {
			// в $this->_attributes помещаем только то, что изменилось
			$this->_attributes[$key] = $value;
		} else if (
			((gettype($value) == 'object' || gettype($this->_savedData[$this->_getColumnName($key)]) == 'object') && $value != $this->_savedData[$this->_getColumnName($key)])
			|| (gettype($value) != 'object' && gettype($this->_savedData[$this->_getColumnName($key)]) != 'object' && (string)$value !== (string)$this->_savedData[$this->_getColumnName($key)])
		) {
			// в $this->_attributes помещаем только то, что изменилось
			$this->_attributes[$key] = $value;
		} else if (array_key_exists($key, $this->_attributes)) {
			//  && false == $this->isNewRecord && $value == $this->_savedData[$this->_getColumnName($key)]
			// попытка установить значение атрибута такое же, какое сохранено в БД, но который был изменен ранее
			unset($this->_attributes[$key]);
		}
	}

	/**
	 * Получение значения атрибута
	 * @param string $key Ключ строчными символами
	 * @return mixed
	 * @throws Exception
	 */
	public function getAttribute($key)
	{
		return $this->attributes[$this->_getColumnName($key)];
	}

	/**
	 * Проверка наличия атрибута
	 * @param string $key Ключ строчными символами
	 * @return bool
	 */
	public function hasAttribute($key)
	{
		return array_key_exists($key, $this->defAttribute);
	}

	/**
	 * Проверка факта изменения атрибута
	 * @param string $key Ключ строчными символами
	 * @return bool
	 */
	protected function _isAttributeChanged($key)
	{
		$key = strtolower($key);
		return array_key_exists($key, $this->_attributes);
	}

	/**
	 * @return bool
	 */
	public function getIsNewRecord()
	{
		return empty($this->_savedData);
	}

	/**
	 * Извлечение значений атрибутов из входящих параметров,
	 * переданных из контроллера.
	 * Устанавливаем только то, что пришло.
	 * Поэтому, чтобы при записи не потерять значения,
	 * предварительно подгружаем данные по идешнику,
	 * а потом их перезаписываем новыми
	 * @param array $data
	 */
	function setAttributes($data)
	{
		$pk = $this->primaryKey(true);
		if (array_key_exists($pk, $data)) {
			$this->_savedData = array();
			$this->_attributes = array();
			if (!empty($data[$pk])) {
				$this->_load($data[$pk]);
			}
		}

		foreach ($this->defAttribute as $key => $info) {
			if (in_array(self::PROPERTY_NOT_SAFE, $info['properties'])) {
				continue;
			}
			if (isset($info['applyMethod']) && method_exists($this, $info['applyMethod'])) {
				call_user_func(array($this, $info['applyMethod']), $data);
				continue;
			}
			$param = $this->_getInputParamName($key, $info);
			
			if (!array_key_exists($param, $data)) {
				continue;
			}
			$this->setAttribute($key, $data[$param]);
		}

		// print_r("============ set attributes ============ \r\n");
		// print_r($this->_attributes);
	}

	/**
	 * Обработчик чекбоксов
	 */
	protected function _applyCheckboxValue($data, $key) {
		$param = $this->_getInputParamName($key);
		if (!array_key_exists($param, $data)) {
			// Устанавливаем значение атрибута, если оно пришло (есть нужные ключи в массиве)
			return false;
		}

		if ($data[$param]) {
			$data[$param] = 2;
		} else {
			$data[$param] = 1;
		}

		$this->setAttribute($key, $data[$param]);
		return true;
	}

	/**
	 * Значение текущей даты и времени
	 * @return DateTime
	 */
	function getCurrentDT()
	{
		return new DateTime('now', new DateTimeZone(date_default_timezone_get()));
	}

	/**
	 * Извлечение даты и времени из входящих параметров
	 * @param array $data
	 * @param string $prefix Префикс строчными буквами, например, 'set' или 'dis' или другой
	 * @return bool
	 */
	protected function _applyDT($data, $prefix)
	{
		$keyDT = $prefix . 'dt';
		$keyDate = $prefix . 'date';
		$keyTime = $prefix . 'time';
		$isAllowTime = $this->hasAttribute($keyTime);
		$paramDate = $this->_getInputParamName($keyDate);
		if (!array_key_exists($paramDate, $data)) {
			// Устанавливаем значение атрибута, если оно пришло (есть нужные ключи в массиве)
			return false;
		}
		$dt = null;
		$paramTime = null;
		if ($isAllowTime) {
			$paramTime = $this->_getInputParamName($keyTime);
			if (isset($data[$paramDate]) && empty($data[$paramTime])) {
				$data[$paramTime] = date('H:i');
			}
			$dt = DateTime::createFromFormat('Y-m-d H:i', $data[$paramDate] . ' ' . $data[$paramTime]);
		} else {
			if (isset($data[$paramDate])) {
				$dt = DateTime::createFromFormat('Y-m-d', $data[$paramDate]);
			}
		}
		if ($dt) {
			$this->setAttribute($keyDT, $dt);
			$this->setAttribute($keyDate, $data[$paramDate]);
			if (isset($paramTime)) {
				$this->setAttribute($keyTime, $data[$paramTime]);
			}
		} else {
			$this->setAttribute($keyDT, null);
			$this->setAttribute($keyDate, null);
			if ($isAllowTime) {
				$this->setAttribute($keyTime, null);
			}
		}
		return true;
	}

	/**
	 * Извлечение даты из входящих параметров
	 *
	 * Устанавливаем значение атрибута, если оно пришло
	 * @param array $data
	 * @param string $keyDate
	 * @return bool
	 */
	protected function _applyDate($data, $keyDate)
	{
		$paramDate = $this->_getInputParamName($keyDate);
		if (!array_key_exists($paramDate, $data)) {
			// Устанавливаем значение атрибута, если оно пришло (есть нужные ключи в массиве)
			return false;
		}
		$dt = null;
		if (isset($data[$paramDate])) {
			$dt = DateTime::createFromFormat('Y-m-d', $data[$paramDate]);
		}
		$this->setAttribute($keyDate, $dt);
		return true;
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
				$this->setAttribute($key, $this->_savedData[$name]);
				if (in_array(self::PROPERTY_DATE_TIME, $info['properties']) && $this->_savedData[$name] instanceof DateTime) {
					if (isset($info['dateKey'])) {
						$this->setAttribute($info['dateKey'], $this->_savedData[$name]->format('Y-m-d'));
					}
					if (isset($info['timeKey'])) {
						$this->setAttribute($info['timeKey'], $this->_savedData[$name]->format('H:i'));
					}
				}
			} else {
				throw new Exception('Не удалось получить данные поля '. $name, 500);
				//throw new Exception(var_export($this->defAttribute).var_export($this->_savedData));
			}
		}
	}

	/**
	 * Запрос данных объекта из БД
	 *
	 * Эти данные могут быть изменены при сохранении!
	 * @param int $id
	 * @throws Exception
	 */
	protected function _requestSavedData($id)
	{
		$fields = array();
		$joins = array();
		//$external_query_list = array();
		//$external_query_params_list = array();
		// выбираем только то, что описано в self::defAttributes
		foreach ($this->defAttribute as $key => $info) {
			if (in_array(self::PROPERTY_NOT_LOAD, $info['properties'])) {
				continue;
			}
			/*if (isset($info['external_query'])) {
				$external_query_list[$key] = $info['external_query'];
				if (isset($info['external_query_params']) && is_array($info['external_query_params'])) {
					$external_query_params_list[$key] = $info['external_query_params'];
				}
				continue;
			}*/
			if (isset($info['select'])) {
				$fields[$key] = $info['select'];
			} else {
				$fields[$key] = $this->viewName() .'.'. $this->_getColumnName($key, $info);
			}
			if (isset($info['join'])) {
				$joins[$key] = strtr($info['join'], array(
					'{PrimaryKey}' => $this->primaryKey(),
					'{ViewName}' => $this->viewName(),
				));
			}
		}
		$fields = implode(', ', $fields);
		$viewName = $this->viewName();
		$joins = implode(' ', $joins);
		$where = $this->viewName().".{$this->primaryKey()} = :id";
		$params = array('id' => $id);
		$query = $this->_beforeQuerySavedData($fields, $viewName, $joins, $where, $params);
		$savedData = $this->getFirstRowFromQuery($query['sql'], $query['params']);
		if ( !is_array($savedData) ) {
			$msg = 'Ошибка при чтении объекта';
			if ($this->isDebug) {
				$msg .= '<br>' . getDebugSQL($query['sql'], $query['params']) . '<br>';
			}
			throw new Exception($msg);
		}
		$this->_processingSavedData($savedData);
		/*foreach ($external_query_list as $column => $external_query) {
			$external_query_params = array();

			if ( isset($external_query_params_list[$column]) ) {
				foreach ( $external_query_params_list[$column] as $param ) {
					$external_query_params[$param] = (isset($this->_savedData[strtolower($param)]) ? $this->_savedData[strtolower($param)] : null);
				}
			}

			$column = strtolower($column);
			$this->_savedData[$column] = $this->getFirstResultFromQuery($external_query, $external_query_params);
		}*/
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
				select top 1 {$fields}
				from {$viewName} with (nolock)
				{$joins}
				where {$where}
			",
			'params' => $params,
		);
	}

	/**
	 * Обработка данных объекта из БД
	 * @param array $savedData Ассоциативный массив
	 * @throws Exception
	 */
	protected function _processingSavedData($savedData)
	{
		foreach($savedData as $column => $value) {
			// переводим ключи в нижний регистр
			$column = strtolower($column);
			$this->_savedData[$column] = $this->_processingSavedValue($column, $value);
		}
	}

	/**
	 * Дополнительная обработка значения атрибута сохраненного объекта из БД
	 * перед записью в модель
	 * @param string $column Имя колонки в строчными символами
	 * @param mixed $value Значение. Значения, которые в БД имеют тип datetime, являются экземлярами DateTime.
	 * @return mixed
	 * @throws Exception
	 */
	protected function _processingSavedValue($column, $value)
	{
		return $value;
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @param array $info
	 * @return string Наименование столбца строчными символами
	 */
	protected function _getColumnName($key, $info = null)
	{
		if (!is_array($info)) {
			$info = $this->getDefAttribute($key);
		}
		if (in_array(self::PROPERTY_NEED_TABLE_NAME, $info['properties'])) {
			$key = strtolower($this->tableName()) .'_'. $key;
		}
		return $key;
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @param array $info
	 * @return string
	 */
	protected function _getInputParamName($key, $info = null)
	{
		if (!is_array($info)) {
			$info = $this->getDefAttribute($key);
		}
		if (isset($info['alias'])) {
			$key = $info['alias'];
		} else if (in_array(self::PROPERTY_NEED_TABLE_NAME, $info['properties'])) {
			$key = $this->tableName() .'_'. $key;
		}
		return $key;
	}

	/**
	 * @param array $values
	 * @return array
	 */
	public function processParams($data) {
		$_data = array();
		foreach($data as $key => $value) {
			if (is_array($value)) {
				$value = $this->processFields($value);
			}
			$_data[strtolower($key)] = $value;
		}
		return $_data;
	}

	/**
	 * Извлечение значений из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	public function applyData($data)
	{
		if (!empty($data['scenario'])) {
			$this->setScenario($data['scenario']);
		}
		$this->setParams($data);
		$this->setAttributes($data);
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 *
	 * Перед вызовом должен быть указан сценарий
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		if (empty($data['session'])) {
			throw new Exception('Не переданы параметры сессии в модель '. get_class($this), 500);
		}
		$this->_params = array();
		$this->_params['session'] = $data['session'];
	}

	/**
	 * @return array
	 */
	public function getSessionParams()
	{
		if (isset($this->_params['session']) && is_array($this->_params['session'])) {
			return $this->_params['session'];
		}
		$beda = getSessionParams();
		if (isset($beda['session'])) {
			return $beda['session'];
		} else {
			throw new Exception('Не удалось получить параметры сессии', 500);
		}
	}

	/**
	 * @param array $params
	 */
	function setSessionParams($params)
	{
		$this->_params['session'] = $params;
	}

	/**
	 * @return int
	 */
	public function getPromedUserId()
	{
		if (isset($this->sessionParams['pmuser_id'])) {
			return $this->sessionParams['pmuser_id'];
		}
		return null;
	}

	/**
	 * Получение строкового кода региона
	 * @return string
	 */
	function getRegionNick()
	{
        if (isset($this->sessionParams['region']))
		    return $this->sessionParams['region']['nick'];
        else
            return $this->load->getRegionNick();
	}

	/**
	 * Получение региональной схемы
	 * @return string
	 */
	function getScheme()
	{
        if (isset($this->sessionParams['region']))
		    return $this->sessionParams['region']['schema'];
        else if (!empty($this->scheme))
            return $this->scheme;
        else
            return 'dbo';
	}

	/**
	 * Получение числового кода региона
	 * @return int
	 */
	function getRegionNumber()
	{
        if (isset($this->sessionParams['region']))
		    return $this->sessionParams['region']['number'];
        else
            return 0;
	}

	/**
	 * @return array Ассоциативный массив настроек из LDAP
	 */
	function getOptions()
	{
		if (empty($this->_options)) {
			$this->load->helper('Options');
			$this->_options = getOptions();
		}
		return $this->_options;
	}

	/**
	 * @return array Ассоциативный массив настроек из LDAP
	 */
	function getGlobalOptions()
	{
		if (empty($this->_globalOptions)) {
			$this->load->model("Options_model");
			$this->_globalOptions = $this->Options_model->getOptionsGlobals($this->_params);
		}
		return $this->_globalOptions;
	}

	/**
	 * Сброс переменной, хронящей настройки системы
	 */
	function resetGlobalOptions($options = null)
	{
		$this->_globalOptions = $options;
	}

	/**
	 * @return array Ассоциативный массив настроек из LDAP
	 */
	function getAllOptions()
	{
		if (empty($this->_allOptions)) {
			$this->load->model("Options_model");
			$this->_allOptions = $this->Options_model->getOptionsAll($this->_params);
		}
		return $this->_allOptions;
	}

	/**
	 * @param array $scenarioList Список имен сценариев, которые должны быть реализованы в модели
	 */
	protected function _setScenarioList($scenarioList)
	{
		$this->_scenarioList = $scenarioList;
	}

	/**
	 * @return array Список имен сценариев, которые должны быть реализованы в модели
	 */
	public function getScenarioList()
	{
		return $this->_scenarioList;
	}

	/**
	 * @param string $scenario Имя текущего сценария, определяющего правила валидации модели
	 */
	public function setScenario($scenario)
	{
		$this->_scenario = $scenario;
	}

	/**
	 * @return string Имя текущего сценария, определяющего правила валидации модели
	 */
	public function getScenario()
	{
		return $this->_scenario;
	}

	/**
	 * @return bool
	 */
	function getIsDebug()
	{
		$isDebug = false;
		if (isset($this->config->config['IS_DEBUG'])) {
			$isDebug = ('1' === $this->config->item('IS_DEBUG'));
		}
		return $isDebug;
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		if (!in_array($this->scenario, $this->scenarioList)) {
			throw new Exception('Эта функция не реализована', 500);
		}
		if (in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DELETE, self::SCENARIO_LOAD_EDIT_FORM))) {
			if ( empty($this->id) ) {
				throw new Exception('Не указан идентификатор объекта', 500);
			}
		}
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = array();
		switch ($name) {
			case self::SCENARIO_AUTO_CREATE:
			case self::SCENARIO_DO_SAVE:
				$rules = $this->_getSaveInputRules();
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
			case self::SCENARIO_DELETE:
				$info = $this->getDefAttribute(self::ID_KEY);
				$paramName = $this->_getInputParamName(self::ID_KEY);
				$rules = array(self::ID_KEY => array(
					'field' => $paramName,
					'rules' => $info['save'],
					'label' => $info['label'],
					'type' => $info['type']
				));
				break;
		}
		return $rules;
	}

	/**
	 * Определение правил для входящих параметров по наименованиям аттрибутов
	 * @param array|string $attributes
	 * @return array
	 * @throws Exception
	 */
	function getInputRulesByAttributes($attributes) {
		if(empty($attributes) || (!is_array($attributes) && !is_string($attributes))) return [];

		if(is_string($attributes)) {
			$attributes = [ $attributes ];
		}

		$rules = [];

		foreach($attributes as $name) {
			$info = $this->getDefAttribute($name);
			$paramName = $this->_getInputParamName($name);
			$rules[$name] = array(
				'field' => $paramName,
				'rules' => !empty($info['save']) ? $info['save'] : '',
				'label' => !empty($info['label']) ? $info['label'] : '',
				'type' => !empty($info['type']) ? $info['type'] : '',
			);
		}
		return $rules;
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = array();
		foreach ($this->defAttribute as $key => $info) {
			if (empty($info['label']) || empty($info['type']) || !isset($info['save'])) {
				continue;
			}
			$paramName = $this->_getInputParamName($key, $info);
			$rules = array(
				'field' => $paramName,
				'rules' => $info['save'],
				'label' => $info['label'],
				'type' => $info['type']
			);
			if (isset($info['default'])) {
				$rules['default'] = $info['default'];
			}
			$all[$paramName] = $rules;
		}
		//Параметр, чтобы в контроллере указать SCENARIO_DO_SAVE или SCENARIO_AUTO_CREATE
		$all['isAutoCreate'] = array(
			'field' => 'isAutoCreate',
			'rules' => 'trim',
			'label' => 'Флаг автоматического сохранения',
			'type' => 'int',
			'default' => 0 // нет
		);
		$all['vizit_direction_control_check'] = array(
			'field' => 'vizit_direction_control_check',
			'label' => 'Контроль пересечения движения и посещения',
			'rules' => 'trim',
			'type' => 'int'
		);
		$all['vizit_kvs_control_check'] = array(
			'field' => 'vizit_kvs_control_check',
			'label' => 'Контроль пересечения посещения с КВС',
			'rules' => 'trim',
			'type' => 'int'
		);
		$all['vizit_intersection_control_check'] = array(
			'field' => 'vizit_intersection_control_check',
			'label' => 'Контроль пересечения посещений',
			'rules' => 'trim',
			'type' => 'int'
		);
		$all['ignoreParentEvnDateCheck'] = array(
			'field' => 'ignoreParentEvnDateCheck',
			'label' => 'Признак игнорирования проверки периода выполенения услуги',
			'rules' => '',
			'type' => 'int'
		);
		return $all;
	}

	/**
	 * Конструктор объекта
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * protected т.к. не фиг работать с БД не из моделей,
	 * по той же причине нет приставки get (@see __get)
	 * @return string
	 */
	protected function tableName()
	{
		throw new Exception('Не определено название таблицы');
	}

	/**
	 * Определение имени представления данных объекта
	 * @return string
	 */
	protected function viewName()
	{
		return 'v_' . $this->tableName();
	}

	/**
	 * Определение имени хранимой процедуры для создания
	 * @return string
	 */
	protected function createProcedureName()
	{
		return 'p_' . $this->tableName() . '_ins';
	}

	/**
	 * Определение имени хранимой процедуры для обновления
	 * @return string
	 */
	protected function updateProcedureName()
	{
		return 'p_' . $this->tableName() . '_upd';
	}

	/**
	 * Определение имени хранимой процедуры для удаления
	 * @return string
	 */
	protected function deleteProcedureName()
	{
		return 'p_' . $this->tableName() . '_del';
	}

	/**
	 * Определение имени поля первичного ключа объекта
	 * @param bool $allowAlias
	 * @return string
	 */
	protected function primaryKey($allowAlias = false)
	{
		if ($allowAlias && isset($this->defAttribute[self::ID_KEY]['alias'])) {
			$pk = $this->defAttribute[self::ID_KEY]['alias'];
		} else {
			$pk = $this->tableName() . '_' . self::ID_KEY;
		}
		return $pk;
	}

	/**
	 * Начало транзакции
	 */
	function beginTransaction()
	{
		if ($this->isAllowTransaction) {
			return $this->db->trans_begin();
		} else {
			return true;
		}
	}

	/**
	 * Завершение транзакции
	 */
	function commitTransaction()
	{
		if ($this->isAllowTransaction) {
			return $this->db->trans_commit();
		} else {
			return true;
		}
	}

	/**
	 * Откат транзакции
	 */
	function rollbackTransaction()
	{
		if ($this->isAllowTransaction) {
			return $this->db->trans_rollback();
		} else {
			return true;
		}
	}


	/**
	 * Выполняет запрос с параметром и возвращает первое поле первой строки
	 *
	 * @param string $query
	 * @param array $params
	 *
	 * @return bool|string|int|float
	 */
	function getFirstResultFromQuery($query, $params = array(), $nullIfNotExists = false)
	{
		$dbresult = $this->db->query($query, $params, true);
		if (is_object($dbresult)) {
			$response = $dbresult->result('array');
			if (isset($response[0])) {
				$keys = array_keys($response[0]);
				$result = $response[0][$keys[0]];
			} else {
				if ($nullIfNotExists) {
					$result = null;
				} else {
					$result = false;
				}
			}
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Выполняет запрос с параметром и возвращает первую строку
	 *
	 * @param string $query
	 * @param array $params
	 *
	 * @return array|bool
	 */
	function getFirstRowFromQuery($query, $params = array(), $nullIfNotExists = false)
	{
		$dbresult = $this->db->query($query, $params, true);
		if (is_object($dbresult)) {
			$response = $dbresult->result('array');
			if (isset($response[0])) {
				$result = $response[0];
			} else {
				if ($nullIfNotExists) {
					$result = null;
				} else {
					$result = false;
				}
			}
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * @param string $sp_name
	 * @param array $params
	 * @param string $return_type
	 * @param bool $debug_print
	 * @return mixed
	 */
	function execCommonSP($sp_name, $params, $return_type = 'array', $debug_print = false) {
		switch($this->db->dbdriver) {
			case 'sqlsrv':
				return $this->execCommonSPMSSQL($sp_name, $params, $return_type, $debug_print);
			case 'postgre':
				return $this->execCommonSPPostgre($sp_name, $params, $return_type, $debug_print);
		}
		return false;
	}
	
	/**
	 * Выполнение стандартной хранимой процедуры. 
	 *
	 * Выполняет стандартную хранимку. Всегда подставляются параметры Error_Code, Error_Msg, остальные берутся из массива входящих параметров, 
	 * в нем же задаются исходящие параметры если они нужны.
	 *
	 * $sp_name string Название хранимой процедуры
	 * $params array Параметры хранимой процедуры
	 * $return_type string Тип возвращаемого результата
	 * $debug_print boolean Возвращает текст выполняемой хранимки вместо выполнения
	 *
	 * @return mixed тип переданный в $return_type, или false в случае ошибки запроса
	 * 
	 */
	function execCommonSPMSSQL($sp_name, $params, $return_type = 'array', $debug_print = false) {

		$declare_str = "";
		$params_str = "";
		$query_params = array();
		$select_str = "";
		$additionalParams = array();

		foreach($params as $param_name => $param_value) {
			if ( is_array($param_value) ) {
				$params_str .= "@" . $param_name . " = " . ( isset($param_value['out']) && $param_value['out'] ? '@' : ':' ) . $param_name . ( isset($param_value['out']) && $param_value['out'] ? ' output' : '' ) . ",\r\n";
				$query_params[$param_name] = $param_value['value'];
				if ( $param_value['out'] ) {
					$declare_str .= "@" . $param_name . " " . $param_value['type'] . " = :" . $param_name . ",\r\n";
					$select_str .= "@" . $param_name . " as " . $param_name . ", ";
				}
			} else if ($param_value === '@curDT') {
				$params_str .= "@" . $param_name . " = @curDT,\r\n";
				$query_params[$param_name] = $param_value;
				$additionalParams['@curDT'] = '@curDT datetime = dbo.tzGetDate()';
			} else {
				$params_str .= "@" . $param_name . " = :" . $param_name  . ",\r\n";
				$query_params[$param_name] = $param_value;
			}
		}

		foreach($additionalParams as $additionalParam) {
			$declare_str .= $additionalParam . ",\r\n";
		}

		$query = "
			declare
				{$declare_str}
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$sp_name}
				{$params_str}
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select {$select_str} @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		if ($debug_print) {
			return getDebugSQL($query, $query_params);
		}

		if (false && $sp_name == 'p_EvnNotifyRegister_upd') {
			throw new Exception(getDebugSQL($query, $query_params));
		}

		// print_r("=================== BEFORE SAVE ========================= \r\n");
		// print_r($query);
		// print_r($query_params);

		$result = $this->db->query($query, $query_params);

		if ( ! is_object($result) ) {
			return false;
		}
		switch ($return_type) {
			default:
			case 'array':
				$result = $result->result('array');
				$result['success'] = (empty($result['Error_Code']) && empty($result['Error_Msg']));
				break;
			case 'array_assoc':
				$result = $result->result('array');
				if (is_array($result) && count($result) > 0) {
					$result = $result[0];
				} else {
					$result = array();
					$result['Error_Code'] = 500;
					$result['Error_Msg'] = 'Неправильный формат ответа при выполнении хранимой процедуры';
				}
				$result['success'] = (empty($result['Error_Code']) && empty($result['Error_Msg']));
				break;
			case 'object':
				$result = $result->result();
				break;
		}
		return $result;
	}

	/**
	 * То же самое, только Postgre
	 */
	function execCommonSPPostgre($sp_name, $params, $return_type = 'array', $debug_print = false) {
		$params_str = [];
		$query_params = [];
		$select_str = [
			"error_code as \"Error_Code\"",
			"error_message as \"Error_Msg\""
		];
		$cte = [];
		$cte[] = "select dbo.tzgetdate() as curtime";

		foreach($params as $param_name => $param_value) {
			if (is_array($param_value)) {
				$params_str[] = $param_name . " := :" . $param_name;
				$query_params[$param_name] = $param_value['value'];
				if ($param_value['out']) {
					$select_str[] = $param_name . " as \"" . $param_name . "\"";
				}
			} else if ($param_value === "curDT") {
				$params_str[] = $param_name . " := (select curtime from myvars)";
			} else {
				$params_str[] = $param_name . " := :" . $param_name . "";
				$query_params[$param_name] = $param_value;
			}
		}

		$query = "
			with myvars as (
				" . implode(', ',$cte) . "
			)
			select
				" . implode(",
				", $select_str) . "
			from {$sp_name}(
				" . implode(",
				", $params_str) . "
			)
		";
		
		if ($debug_print) {
			return getDebugSQL($query, $query_params);
		}

		$result = $this->db->query($query, $query_params);
		if (!is_object($result)) {
			return false;
		}

		switch ($return_type) {
			default:
			case 'array':
				$result = $result->result('array');
				$result['success'] = (empty($result['Error_Code']) && empty($result['Error_Msg']));
				break;
			case 'array_assoc':
				$result = $result->result('array');
				if (is_array($result) && count($result) >0) {
					$result = $result[0];
				} else {
					$result = [];
					$result['Error_Code'] = 500;
					$result['Error_Msg'] = 'Неправильный формат ответа при выполнении хранимой процедуры';
				}
				$result['success'] = (empty($result['Error_Code']) && empty($result['Error_Msg']));
				break;
			case 'object':
				$result = $result->result();
				break;
		}
		return $result;
	}

	/**
	 * Подключение базы Монго
	 */
	public function loadMongoDb() {
		if ( extension_loaded( 'mongo' ) ) {
			if ( $this->config->load( 'mongodb' ) ) {
				$this->mongo_loaded = true;
				$this->mongo_switch = $this->config->item( 'mongo_switch' );

				$this->load->library('swMongodb');
				$this->load->library('swMongoExt');
				$this->load->library('swMongoCache');
				$this->load->helper('MongoDB');
			}
		}
	}

	/**
	 * Сохранение объекта
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * Если массив не передается, то ранее должны быть установлены данные
	 * с помощью метода applyData($data) или методов setParams($data) и setAttributes($data)
	 * Также должен быть указан сценарий бизнес-логики с помощью метода setScenario
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array Ответ модели в формате ассоциативного массива,
	 * пригодном для обработки методом ProcessModelSave контроллера
	 * Обязательно должны быть ключи: Error_Msg и идешник объекта
	 */
	public function doSave($data = array(), $isAllowTransaction = true)
	{
		//SCENARIO_DO_SAVE или SCENARIO_AUTO_CREATE
		//$this->setScenario(self::SCENARIO_DO_SAVE);
		try {
			$this->isAllowTransaction = $isAllowTransaction;
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}
			$this->_beforeSave($data);
			$tmp = $this->_save();
			$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
			$this->_afterSave($tmp);
			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			if ($this->isDebug && $e->getCode() == 500) {
				// только на тестовом и только, если что-то пошло не так
				$this->_saveResponse['Error_Msg'] .= ' ' . $e->getTraceAsString();
			}
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		$this->_saveResponse[$this->primaryKey(true)] = $this->id;
		$this->_onSave();
		return $this->_saveResponse;
	}

	/**
	 * Логика перед валидацией
	 */
	protected function _beforeValidate() {

	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 *
	 * При запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		if (!empty($data)) {
			$this->applyData($data);
		}

		$this->_beforeValidate();

		/**
		 * свойства модели загружены внешними данными,
		 * а в _savedData загружены данные из БД, если был передан идешник
		 * теперь с этим можно работать
		 * читать используя доступ как к свойствам,
		 * а записывать, используя метод setAttribute
		 */
		$this->_validate();
	}

	/**
	 * Запись данных объекта в БД
	 * @param array $queryParams Параметры запроса
	 * @return array Результат выполнения запроса
	 * @throws Exception В случае ошибки запроса или ошибки возвращенной хранимкой
	 */
	protected function _save($queryParams = array())
	{
		if (empty($queryParams)) {
			$queryParams = array();
			$queryParams[$this->primaryKey()] = array(
				'value' => $this->id,
				'out' => true,
				'type' => 'bigint',
			);
			$queryParams['pmUser_id'] = $this->promedUserId;
			foreach ($this->defAttribute as $key => $info) {
				if (in_array(self::PROPERTY_IS_SP_PARAM, $info['properties'])) {
					$queryParams[$this->_getColumnName($key, $info)] = $this->getAttribute($key);
				}
			}
		}
		if (empty($queryParams[$this->primaryKey()])
			|| !array_key_exists('value', $queryParams[$this->primaryKey()])
		) {
			throw new Exception('Неправильный формат параметров запроса', 500);
		}
		// Конвертируем даты в строки
		foreach ($queryParams as $key => $value ) {
			if ($value instanceof DateTime) {
				$queryParams[$key] = $value->format('Y-m-d H:i:s');
			}
		}

		if (empty($queryParams[$this->primaryKey()]['value'])) {
			$sp_name = $this->createProcedureName();
		} else {
			$sp_name = $this->updateProcedureName();
		}
		if (false && 'p_EvnDiagPS_ins' == $sp_name) {
			throw new Exception($this->execCommonSP($sp_name, $queryParams, 'array', true));
		}
		if ($this->isDebug && isset($queryParams['evnvizitplstom_toothsurface'])
			&& ('' === $queryParams['evnvizitplstom_toothsurface'] || false === strpos($queryParams['evnvizitplstom_toothsurface'], 'ToothSurfaceTypeIdList'))
		) {
			log_message('error_evnvizitplstom_toothsurface', 'Неправильный формат списка поверхностей зуба. sp_name:'.$sp_name.' params: '.var_export($queryParams, true));
			throw new Exception('Неправильный формат списка поверхностей зуба ', 500);
		}
		
		// @task https://redmine.swan-it.ru/issues/197753
		// Передавать параметры через json. Используется для хранимок в postgre, у которых более 100 параметров
		if ($this->_useJsonParams) {
			$jsonParams = [];
			foreach ($this->defAttribute as $key => $info) {
				if (in_array(self::PROPERTY_IS_SP_PARAM, $info['properties'])) {
					$key = $this->_getColumnName($key, $info);
					$jsonParams[$key] = $queryParams[$key];
					unset($queryParams[$key]);
				}
			}
			if (count($jsonParams) > 0) {
				$queryParams['params'] = json_encode(array_change_key_case($jsonParams, CASE_LOWER));
			}
		}
		
		$tmp = $this->execCommonSP($sp_name, $queryParams);
		if (empty($tmp)) {
			throw new Exception('Ошибка запроса записи данных объекта в БД', 500);
		}
		if (isset($tmp[0]['Error_Msg'])) {
			throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
		}
		return $tmp;
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 *
	 * Если сохранение выполняется внутри транзакции,
	 * то при запросах данных этого объекта из БД будут возвращены старые данные!
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		//ниже должно быть реализовано сохранение составных частей объекта
	}

	/**
	 * Логика после успешного сохранения объекта в БД со всеми составными частями
	 * Все изменения уже доступны для чтения из БД.
	 * Тут нельзя выбрасывать исключения, т.к. возможно была вложенная транзакция!
	 */
	protected function _onSave()
	{
		//ниже может быть реализована логика, не влияющая на целостность объект,
		//например, отсылка сообщений и прочее
	}

	/**
	 * Запись данных атрибута объекта в БД
	 * @param array $queryParams Параметры запроса
	 * @return array Результат выполнения запроса
	 * @throws Exception В случае ошибки запроса или ошибки возвращенной хранимкой
	 */
	protected function _saveAttribute($updateTable, $paramName, $paramValue) {

		$resp_save = $this->queryResult("
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Evn_id bigint =  :Evn_id
			
			set nocount on
			
			begin try
				update {$updateTable} with (rowlock)
				set
					{$paramName} = :paramValue
				where
					{$updateTable}_id = :Evn_id
					
				update Evn with (rowlock)
				set
					Evn_updDT = dbo.tzGetDate(), 
					pmUser_updID = :pmUser_id
				where
					Evn_id = :Evn_id
			end try
			
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			
			set nocount off
			
			Select @Evn_id as Evn_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		", array(
			'Evn_id' => $this->id,
			'paramValue' => $paramValue,
			'pmUser_id' => $this->promedUserId
		));

		if (empty($resp_save) || false == is_array($resp_save)) {
			throw new Exception('Ошибка запроса к БД', 500);
		}
		if (false == empty($resp_save[0]['Error_Msg'])) {
			throw new Exception($resp_save[0]['Error_Msg'], 500);
		}
		return $resp_save;
	}

	/**
	 * Обновление значения отдельного поля объекта
	 * @param int $id
	 * @param string $key Ключ строчными символами
	 * @param mixed $value
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array
	 * @throws Exception
	 */
	protected function _updateAttribute($id, $key, $value = null, $isAllowTransaction = true)
	{
		try {
			$this->isAllowTransaction = false;
			// проверяем, все ли было сделано правильно перед вызовом этого метода
			if (empty($this->scenario)) {
				throw new Exception('Не указан сценарий', 500);
			}
			if ($this->scenario != self::SCENARIO_SET_ATTRIBUTE) {
				throw new Exception('Указан неправильный сценарий', 500);
			}
			if (empty($this->promedUserId) || empty($this->sessionParams)) {
				throw new Exception('Параметры не были установлены', 500);
			}
			if (empty($id)) {
				throw new Exception('Не указан ключ объекта', 500);
			}

			$data = array();

			$inputRule = $this->getInputRules(self::SCENARIO_DO_SAVE);
			$paramName = $this->_getInputParamName($key);
			$info = $this->getDefAttribute($key);

			if (!empty($inputRule[$paramName])) {
				// если есть обработка во входящих параметрах, надо её применить, чтобы проверить корректность данных + сконвертить дату в правильную, если надо.
				$err = getInputParams($data, array($inputRule[$paramName]), true, array(
					$paramName => $value
				));

				if (!empty($err)) {
					throw new Exception($err, 500);
				}
			} else {
				// иначе берём то что ввели
				$data[$paramName] = $value;
			}

			$data[$this->primaryKey(true)] = $id;
			$this->setAttributes($data);
			if ($this->_isAttributeChanged($key)) {
				// сохраняем только, если значение изменилось
				$this->isAllowTransaction = $isAllowTransaction;
				if ( !$this->beginTransaction() ) {
					$this->isAllowTransaction = false;
					throw new Exception('Не удалось запустить транзакцию', 500);
				}
				$this->_beforeUpdateAttribute($key);
				if (isset($info['updateTable']) && count($this->_attributes) == 1) {
					// разрешить простое сохранение
					$this->_saveAttribute($info['updateTable'], $paramName, $value);
				} else {
					$this->_save();
				}
				$this->_afterUpdateAttribute($key);
				if ( !$this->commitTransaction() ) {
					$this->isAllowTransaction = false;
					throw new Exception('Не удалось зафиксировать транзакцию', 500);
				}
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			return $this->_saveResponse;
		}
		$this->_saveResponse[$this->primaryKey(true)] = $id;
		return $this->_saveResponse;
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _afterUpdateAttribute($key)
	{
	}

	/**
	 * Удаление объекта
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array
	 */
	public function doDelete($data = array(), $isAllowTransaction = true)
	{
		$this->setScenario(self::SCENARIO_DELETE);
		try {
			$this->isAllowTransaction = $isAllowTransaction;
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}
			$this->_beforeDelete($data);
			$tmp = $this->_delete();
			$this->_afterDelete($tmp);
			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			if ($this->isDebug && $e->getCode() == 500) {
				// только на тестовом и только, если что-то пошло не так
				$this->_saveResponse['Error_Msg'] .= ' ' . $e->getTraceAsString();
			}
			$this->_saveResponse['Error_Code'] = $e->getCode();
		}
		$this->_saveResponse[$this->primaryKey(true)] = $this->id;
		$this->_onDelete();
		return $this->_saveResponse;
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		if (!empty($data)) {
			$this->applyData($data);
		}
		$this->_beforeValidate();
		$this->_validate();
		// ниже, например, может быть логика удаления составных частей
	}

	/**
	 * Вызов процедуры удаления объекта
	 *
	 * @param array $queryParams Параметры запроса
	 * @return array Результат выполнения запроса
	 * @throws Exception В случае ошибки запроса или ошибки возвращенной хранимкой
	 */
	protected function _delete($queryParams = array())
	{
		if (empty($queryParams)) {
			$queryParams = array();
			$queryParams[$this->primaryKey()] = $this->id;
			if ($this->_isNeedPromedUserIdForDel) {
				$queryParams['pmUser_id'] = $this->promedUserId;
			}
		}
		$tmp = $this->execCommonSP($this->deleteProcedureName(), $queryParams);
		if (empty($tmp)) {
			throw new Exception('Ошибка запроса удаления записи из БД', 500);
		}
		if (isset($tmp[0]['Error_Msg'])) {
			throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
		}
		return $tmp;
	}

	/**
	 * Логика после успешного выполнения запроса удаления объекта внутри транзакции
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterDelete($result)
	{
		//
	}

	/**
	 * Логика после успешного удаления объекта из БД со всеми составными частями.
	 * Все изменения уже доступны для чтения из БД.
	 * Тут нельзя выбрасывать исключения, т.к. возможно была вложенная транзакция!
	 */
	protected function _onDelete()
	{
		//ниже может быть реализована логика, не влияющая на целостность объект,
		//например, отсылка сообщений и прочее
	}

	/**
	 * Проверяет возможно ли перевести запрос на Монго
	 *
	 * @param string $param Ключ
	 * @param bool $check_mongo Флаг проверки подключения Монго
	 * @return boolean
	 */
	public function allowSwitchMongo( $param, $check_mongo = true ) {
		if ( $check_mongo && !$this->mongo_loaded ) {
			return false;
		}
		
		if ( is_array($this->mongo_switch) && array_key_exists( $param, $this->mongo_switch ) ) {
			return $this->mongo_switch[ $param ];
		}

		return false;
	}

	/**
	 * @param string $msg
	 */
	public function addWarningMsg($msg) {
		if (empty($this->_saveResponse['Warning_Msg'])) {
			$this->_saveResponse['Warning_Msg'] = array($msg);
		} else {
			$this->_saveResponse['Warning_Msg'][] = $msg;
		}
	}

	/**
	 * @return array
	 */
	public function getWarningMsg() {
		return isset($this->_saveResponse['Warning_Msg'])?$this->_saveResponse['Warning_Msg']:array();
	}

	/**
	 * @param string $msg
	 */
	public  function addInfoMsg($msg) {
		if (empty($this->_saveResponse['Info_Msg'])) {
			$this->_saveResponse['Info_Msg'] = array($msg);
		} else {
			$this->_saveResponse['Info_Msg'][] = $msg;
		}
	}

	/**
	 * @return array
	 */
	public function getInfoMsg() {
		return isset($this->_saveResponse['Info_Msg'])?$this->_saveResponse['Info_Msg']:array();
	}

	/**
	 * Устанавливает сообщение для отображения пользователю
	 * В случае, когда операция может продолжаться дальше.
	 * @param $msg
	 */
	protected function _setAlertMsg($msg)
	{
		if (empty($this->_saveResponse['Alert_Msg'])) {
			$this->_saveResponse['Alert_Msg'] = $msg;
		} else {
			$this->_saveResponse['Alert_Msg'] .= $msg;
		}
	}

	/**
	 * Публичный метод для возврата предупреждения из модели
	 * @return array
	 */
	public function getAlertMsg()
	{
		return (empty($this->_saveResponse['Alert_Msg'])?'':$this->_saveResponse['Alert_Msg']);
	}

	/**
	 * Публичный метод для вывода ответа после сохранения данных
	 * @return array
	 */
	public function getSaveResponse()
	{
		return $this->_saveResponse;
	}

	/**
	 * Обрабатывает входящие данные, проверяет их на ошибки
	 *
	 * @access public
	 * @param array $rules Массив правила для проверки входных данных.
	 * @param string $inData Сущестующие параметры.
	 * @param boolean $GetSessionParams По умолчанию: true. Установите false, если данные из сессии не требуется включать в входящие параметры.
	 * @param boolean $CloseSession По умолчанию: false. Установите true в случае, если нужно закрыть сессию после обработки входящих параметров (поскольку в большинстве случаев сессия не нужна).
	 * @param bool $PreferSession В первую очередь брать параметры из сессии
	 * @param bool $ParamsFromPost Брать параметры из $_POST
	 * @param bool $convertUTF8 Конвертировать входящие параметры из UTF 8
	 *
	 * @return array Обработанный массив входящих параметров
	 *
	 */
	protected function _checkInputData($rules, $inData=null, &$error, $GetSessionParams = true, $CloseSession = true, $PreferSession = false, $ParamsFromPost = false, $convertUTF8 = true) {
		$data = array();
		
		// Заменяем $_POST на $_GET, если $_POST пустой.
		if(!$ParamsFromPost){
			if(empty($_POST)&&(!empty($_GET))){
				$_POST = $_GET;
			}
		}
		// Получаем сессионные переменные
		If ( $GetSessionParams && (!$PreferSession)) {
			$data = array_merge($data, getSessionParams());
		}
		if ( isset($rules) ) {
			$err = getInputParams($data, $rules, $convertUTF8, $inData, true);
			if ( strlen($err) > 0 ) {
				$error = $this->createError('', $err);
			}
		}
		If ( $GetSessionParams && $PreferSession)
			$data = array_merge(getSessionParams(), $data);
		if ($GetSessionParams && isset($_SESSION)) {
			$data['session'] = $_SESSION;
		}
		if ( $CloseSession )
			session_write_close();
		return $data;
	}
	
	/**
	 * Функция выполнения и обработки результата запроса
	 * @param string $query
	 * @param array $queryParams
	 * @return array|false
	 */
	public function queryResult($query = '', $queryParams = array(), $dbNew = null)
	{
		$db = $this->db;
		if (!empty($dbNew) && is_object($dbNew)) {
			$db = $dbNew;
		}
		$result = $db->query($query, $queryParams, true);
		if (!is_object($result)) {
			return false;
		} else {
			$resp = $result->result('array');
			if (!empty($resp[0]['Error_Code']) && !empty($resp[0]['Error_Msg']) && in_array($resp[0]['Error_Code'], array('2601', '2627'))) {
				sql_log_message('error', $resp[0]['Error_Code'] . $resp[0]['Error_Msg'], getDebugSql($query, $queryParams));
				$resp[0]['Error_Msg'] = 'Обнаружено дублирование, сохранение невозможно';
			}
			return $resp;
		}
	}

	/**
	 * Выполняется запрос и возвращается список значений из первого поля
	 * @param type $query
	 * @param type $queryParams
	 * @return boolean|array
	 */
	public function queryList($query = '',$queryParams = array()) {
		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result)) {
			return false;
		}
		$list = array();
		if (count($result) > 0) {
			$keys = array_keys($result[0]);
			$key = $keys[0];
			foreach($result as $row) {
				$list[] = $row[$key];
			}
		}
		return $list;
	}
	
	/**
	 * Функция обработки резульата запроса
	 * @param mixed $result
	 * @return boolean
	 */
	public function isSuccessful($result) {
		if (is_array($result) && !empty($result['Error_Msg'])) return false;
		return (is_array($result) && empty($result[0]['Error_Msg']));
	}
	/**
	 * Функция создания ошибки
	 * @param int|string|null $Error_Code
	 * @param string $Error_Msg
	 * @return array
	 */
	public function createError($Error_Code = null, $Error_Msg = '') {
		return array(array('success'=>false, 'Error_Code'=>(string)$Error_Code,'Error_Msg'=>(string)$Error_Msg));
	}
	/**
	 * Функция создания предупреждения
	 * @param int|string|null $Error_Code
	 * @param string $Error_Msg
	 * @return array
	 */
	public function createAlert($Error_Code = null, $Error_Msg = '', $Alert_Msg = '') {
		return array(array('success'=>false, 'Error_Code'=>(string)$Error_Code,'Error_Msg'=>(string)$Error_Msg,'Alert_Msg'=>(string)$Alert_Msg));
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
                    {$row['table']}_id
                from
                    {$row['schema']}.{$row['table']} with (nolock)
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
								DECLARE
									@ErrCode int,
									@ErrMessage varchar(4000);
								EXEC " . $row['schema'] . ".p_" . $row['table'] . "_del
									@" . $key . " = " . $value . ",
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 * Метод обновления данных в БД без использования хранимых процедур
	 * @param string $object_name Объект БД запись которого редактируется
	 * @param array $data массив данных поле => значение
	 * @param bool $allow_rec_upd Надо ли записывать pmUser_updID и {$object_name}_updDT
	 * @return array|bool
	 */
	public function swUpdate($object_name='', $data=array(), $allow_rec_upd = true) {
		
		if (!is_string( $object_name )) {
			return $this->createError('', 'Неверный тип имени объекта БД');
		}
		
		$pmUser_field = 'pmUser_id'; //Наименование поля pmUser_id
		$schema = "dbo"; //Схема по умолчанию
		
		//При необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}
		
		if (empty($object_name)) {
			return $this->createError('', 'Не указан объект БД');
		}
		
		//Получаем наименование ключевого поля
		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		//Проверяем наличие значения ключевого поля
		if (empty($data[$key_field])) {
			return $this->createError('', 'Не указано значение ключевого поля объекта ' . $object_name);
		}
		//Проверяем наличие значения поля pmUser_id
		if (empty($data[$pmUser_field]) && $allow_rec_upd) {
			return $this->createError('', 'Не указан пользователь, который обновляет объект ' . $object_name);
		}
		
		//Инициализация запроса.
		$fields = array();
		if ($allow_rec_upd) {
			$fields[] = "pmUser_updID = :$pmUser_field";
			$fields[] = "{$object_name}_updDT = dbo.tzGetDate()";
		}
		//Флаг отсутсвия параметров
		$no_params = true;
		foreach ( $data as $field_name => $value ) {
			if ( !in_array( $field_name , array( $pmUser_field , $key_field, 'key_field' ) ) ) {
				$no_params = false ;
				$fields[] = "$field_name = :$field_name";
			}
		}
		
		if ($no_params) {
			return $this->createError('', 'Не передан ни один параметр для редактирования записи');
		}
		$fields = implode(',', $fields);

		$query = "
			UPDATE {$schema}.{$object_name}
			SET {$fields}
			WHERE {$key_field} = :{$key_field}
		";

		//echo getDebugSQL($query, $data);exit;

		$result = $this->db->query($query, $data);
		
		return ($result === TRUE) ? array(array('success'=>true, 'Error_Msg'=>null)) : $result;
		
	}

    /**
     * Получение списка параметров хранимой процедуры
     * @param string $sp - наименование хранимой процедуры
     * @param string $schema - схема харнимой процедуры
     * @return array|bool
     */
    function getStoredProcedureParamsList($sp, $schema) {
        $query = "
			select
				ps.[name]
			from
				sys.all_parameters ps with(nolock)
				left join sys.types t with(nolock) on t.user_type_id = ps.user_type_id
			where
				ps.[object_id] = (
					select
						top 1 [object_id]
					from
						sys.objects with(nolock)
					where
						[type_desc] = 'SQL_STORED_PROCEDURE' and
						[name] = :name and
						(
							:schema is null or
							[schema_id] = (select top 1 [schema_id] from sys.schemas with(nolock) where [name] = :schema)
						)
				) and
				ps.[name] not in ('@pmUser_id', '@Error_Code', '@Error_Message', '@isReloadCount') and
				t.[is_user_defined] = 0;
		";

        $queryParams = array(
            'name' => $sp,
            'schema' => $schema
        );

        $result = $this->db->query($query, $queryParams);

        if ( !is_object($result) ) {
            return false;
        }

        $outputData = array();
        $response = $result->result('array');

        foreach ( $response as $row ) {
            $outputData[] = str_replace('@', '', $row['name']);
        }

        return $outputData;
    }

    /**
     * Сохранение произвольного обьекта (без повреждения предыдущих данных).
     * @param string $object_name - наименование таблицы (при необходимости с указанием схемы)
     * @param array $data - данные для сохранения
     * @return array|bool
     */
    function saveObject($object_name, $data) {
        $schema = "dbo";

        //при необходимости выделяем схему из имени обьекта
        $name_arr = explode('.', $object_name);
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
        $save_data = array();
        $query_part = "";

        //получаем существующие данные если апдейт
        if ($action == "upd") {
            $query = "
				select
					*
				from
					{$schema}.v_{$object_name} with (nolock)
				where
					{$key_field} = :id;
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
        }

        foreach($data as $key => $value) {
            if (in_array($key, $params_list)) {
                $save_data[$key] = $value;
            }
        }

        foreach($save_data as $key => $value) {
            if (in_array($key, $params_list) && $key != $key_field) {
                //перобразуем даты в строки
                if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
                    $save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
                }
                $query_part .= "@{$key} = :{$key}, ";
            }
        }

        $save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : $this->getPromedUserId();

        $query = "
			declare
				@{$key_field} bigint = :{$key_field},
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @{$key_field} as {$key_field}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
            return array('Error_Msg' => 'При сохранении произошла ошибка');
        }
    }

    /**
     * Проверка на существование любой записи в таблице
     * @param string $table_name - таблица
     * @param string $schema - схема
     * @param array $data - поля по которым будет происходить поиск в таблице
     * @return int|bool - возвращает ключ найденной записи или false
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
            "select top 1 {$key_field}
            from {$schema}.v_{$object_name} with(nolock)
            where (1=1) {$where}";
        return $this->getFirstResultFromQuery($query,$params);
    }

    /**
     * Сохранение произвольного объекта (без повреждения предыдущих данных с проверкой на уникальность по полю $uniqueField)
     * @param string $object_name - наименование таблицы (при необходимости с указанием схемы)
     * @param array $data - данные для сохранения
     * @param string $uniqueField - поле для проверки уникальности
     * @return array|bool
     */
    function saveObjectWithCheckForUniqueness($object_name, $data, $uniqueField) {
        if( empty($object_name) || !is_array($data)) return false;
        $key_field = $object_name."_id";

        $searchParameters = [];
        $searchParameters[$uniqueField] = $data[$uniqueField];

        $key_value = $this->isExistObjectRecord($object_name, $searchParameters);

        if($key_value) {
            $data[$key_field] = $key_value;
        }

        return $this->saveObject($object_name, $data);
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
				{$schema}.{$object_name} with (nolock)
			where
				{$key_field} = :id;
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
            if (in_array($key, $params_list)) {
                $save_data[$key] = $value;
            }
        }

        foreach($save_data as $key => $value) {
            if (in_array($key, $params_list) && $key != $key_field) {
                //перобразуем даты в строки
                if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
                    $save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
                }
                $query_part .= "@{$key} = :{$key}, ";
            }
        }

        $save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : $this->getPromedUserId();

        $query = "
			declare
				@{$key_field} bigint = null,
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @{$key_field} as {$key_field}, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
     * Удаление произвольного обьекта.
     * @param string $object_name - наименование таблицы (при необходимости с указанием схемы)
     * @param array $data - массив должен содержать идентификатор удаляемой записи
     * @return array|bool
     */
    function deleteObject($object_name, $data) {
        $query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute dbo.p_{$object_name}_del
				@{$object_name}_id = :{$object_name}_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			select top 1
				{$object_name}_id
			from
				{$schema}.v_{$object_name} with (nolock)
			where
				{$object_name}_Code = :code
			order by
				{$object_name}_id;
		";
		$id = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $id && $id > 0 ? $id : null;
	}

	/**
	 * Получение ответа для пейджинга
	 */
	function getPagingResponse($query, $queryParams, $start, $limit, $ph = false, $allowOverLimit = false, $postgres = false) {
		$response = array();

		if (!empty($_REQUEST['getCountOnly'])) {
			if ($ph) {
				$get_count_query = getCountSQLPH($query);
			} else {
				$get_count_query = getCountSQL($query);
			}

			$get_count_result = $this->db->query($get_count_query, $queryParams);

			$response['data'] = array();
			if (is_object($get_count_result)) {
				$response['totalCount'] = $get_count_result->result('array');
				$response['totalCount'] = $response['totalCount'][0]['cnt'];
			}

			return $response;
		}

		if ($ph) {
			$limit_query = getLimitSQLPH($query, $start, $limit, $postgres);
		} else {
			$limit_query = getLimitSQL($query, $start, $limit, $postgres);
		}
		$result = $this->db->query($limit_query, $queryParams);

		if (is_object($result)) {
			$res = $result->result('array');
			if (is_array($res)) {
				$response['data'] = $res;
				$response['totalCount'] = $start + count($res);
				if (count($res) >= $limit) {
					if ((!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) || $allowOverLimit == true || !empty($_REQUEST['allowOverLimit'])) {
						// для архивных всё равно необходимо количество, т.к. они грузятся когда количество уже известно
						if ($ph) {
							$get_count_query = getCountSQLPH($query);
						} else {
							$get_count_query = getCountSQL($query);
						}
						$get_count_result = $this->db->query($get_count_query, $queryParams);

						if (is_object($get_count_result)) {
							$response['totalCount'] = $get_count_result->result('array');
							$response['totalCount'] = $response['totalCount'][0]['cnt'];
						}
					} else {
						$response['overLimit'] = true; // лимит весь вошел на страницу, а значит реальный каунт может отличаться от totalCount и пусть юезр запросит его сам, если он ему нужен
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}

		return $response;
	}

	/**
	 * получаем поля хранимки
	 */
	function getStoredProcedureParams($data) {

		$params['stored_procedure'] = $data['stored_procedure'];

		$sp_params = $this->dbmodel->queryResult(
			"
				select
					name
				from sys.parameters (nolock)
				where object_id = object_id(:stored_procedure)
			", $params
		);

		if (!empty($sp_params)) {

			// переведем ключи хранимки в нижний регистр и отформатируем
			foreach ($sp_params as $key => $val) { $sp_params[$key] = strtolower(ltrim($val['name'], '@')); }
			return $sp_params;

		} else return array();
	}


	/**
	 * конвертируем параметры входных правил в параметры хранимки из модели
	 */
	function convertAliasesToStoredProcedureParams($inputParams) {

		$bypass_params = array('setdate', 'settime', 'setdt');

		foreach ($inputParams as $inputAttr => $val) {

			$isEqual = false;

			if (!empty($val) || $val === 0) {
				foreach ($this->defAttribute as $spAttr => $attr) {
					if ($spAttr != 'id'
						&& !empty($attr['alias'])
						&& strtolower($attr['alias']) == strtolower($inputAttr)
					) {
						if (in_array(self::PROPERTY_IS_SP_PARAM, $attr['properties'])) {
							$inputParams[$spAttr] = $val;
							break;
						}
						$isEqual = true;
					}
				}

				if (in_array($inputAttr, $bypass_params)) $isEqual = true;
			}
			if (!$isEqual) unset($inputParams[$inputAttr]);
		}

		return $inputParams;
	}

	/**
	 * Проверяет наличие значений в таблицах БД
	 */
	public function checkForeignKeyValues($table, $schema = 'dbo', $data) {
		$result = true;

		// Получаем список внешних ключей
		$fkInfo = $this->queryResult("
			select
				c.name as column_name,
				rs.name as reference_schema,
				rt.name as reference_table,
				rc.name as reference_column
			from sys.columns c with (nolock)
				inner join sys.tables t on t.object_id = c.object_id
				inner join sys.schemas s on s.schema_id = t.schema_id
				outer apply (
					select top 1 referenced_object_id, referenced_column_id
					from sys.foreign_key_columns with (nolock)
					where parent_object_id = t.object_id
						and parent_column_id = c.column_id
				) fk
				left join sys.tables rt on rt.object_id = fk.referenced_object_id
				left join sys.schemas rs on rs.schema_id = rt.schema_id
				left join sys.columns rc on rc.column_id = fk.referenced_column_id
					and rc.object_id = fk.referenced_object_id
			where t.name = :table
				and s.name = :schema
				and rt.object_id is not null
		", array(
			'table' => $table,
			'schema' => $schema,
		));

		if ( $fkInfo !== false ) {
			foreach ( $fkInfo as $row ) {
				if ( empty($data[$row['column_name']]) ) {
					continue;
				}

				$val = $this->getFirstResultFromQuery("select top 1 " . $row['reference_column'] . " from " . $row['reference_schema'] . "." . $row['reference_table'] . " where " . $row['reference_column'] . " = :" . $row['column_name'], $data);

				if ( $val === false ) {
					$result = "Значение поля " . $row['reference_column'] . " отсутствует в связанном справочнике";
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * @return CI_DB
	 */
	public function getDb()
	{
		$ci = &get_instance();
		if ($this->is_pg) {
			if (!empty($ci->db) && $ci->db->dbdriver == 'postgre') {
				$this->_db = $ci->db;
			} else if (empty($this->_db) || $this->_db->dbdriver != 'postgre') {
				$this->setDb('postgres');
			}
		} else {
			if (!empty($ci->db)) {
				$this->_db = $ci->db;
			} else if (empty($this->_db) || $this->_db->dbdriver != 'sqlsrv') {
				$this->setDb('default');
			}
		}
		return $this->_db;
	}

	/**
	 * @param string $name
	 */
	public function setDb($name = 'default')
	{
		$this->_db = $this->load->database($name, true);
	}
}
// END swModel class
