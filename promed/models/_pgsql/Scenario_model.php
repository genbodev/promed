<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * Scenario_model - модель содержит общие методы сохранения, загрузки, удаления
 * некоторые методы пришлось переопределить, тк swModel не умеет работать со схемами.
 * а добавление такой возможности к swModel может привести к ошибкам
 * todo описать аттрибуты так чтобы унифицировать: 1) метод получения входящих правил по сценарию и 2) методы загрузки форм
 * todo описать метод сохранения грида
 * todo Добавление сценария и метода к нему
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat (emsis.magafurov@gmail.com)
 * @version      01.07.2019
 */
abstract class Scenario_model extends SwPgModel
{

	const SCENARIO_DO_SAVE_JSON_MULTIPLE = 'doSaveJsonMultiple';
	const SCENARIO_DISABLE = 'disable';
	var $table_name = '';
	var $scheme = '';
	var $saveAsNewObject = false;
	var $useCommonEditLoader = false;
	protected $_isNeedPromedUserIdForDel = true;
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение региональной схемы
	 * @return string
	 */
	function getScheme()
	{
		if (empty($this->scheme)) {
			return 'dbo';
		}
		return $this->scheme;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	function tableName() {
		if(empty($this->table_name)) {
			throw new Exception('Не задано название таблицы в БД');
		}
		return $this->table_name;
	}

	/**
	 * Определение имени представления данных объекта
	 * @return string
	 */
	protected function viewName()
	{
		return $this->getScheme() . '.v_' . $this->tableName();
	}

	/**
	 * Определение имени хранимой процедуры для создания
	 * @return string
	 */
	protected function createProcedureName()
	{
		return $this->getScheme() . '.p_' . $this->tableName() . '_ins';
	}

	/**
	 * Определение имени хранимой процедуры для обновления
	 * @return string
	 */
	protected function updateProcedureName()
	{
		return  $this->getScheme() . '.p_' . $this->tableName() . '_upd';
	}

	/**
	 * Определение имени хранимой процедуры для удаления
	 * @return string
	 */
	protected function deleteProcedureName()
	{
		return  $this->getScheme() . '.p_' . $this->tableName() . '_del';
	}

	/**
	 * Получение правил для входящих параметров
	 * @param string - сценарий
	 * @return array
	 * todo в аттрибутах можно указать в каком сценарии они используются и с какими правилами
	 * в таком случае не нужно будет писать костыли для getInputRules()
	 */
	function getInputRules($name)
	{
		$rules = array();
		switch ($name) {
			case self::SCENARIO_AUTO_CREATE:
			case self::SCENARIO_DO_SAVE:
			case self::SCENARIO_SET_ATTRIBUTE:
				$rules = $this->_getSaveInputRules();
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
			case self::SCENARIO_DELETE:
				$rules = $this->getInputRulesByAttributes(self::ID_KEY);
				break;
			case self::SCENARIO_DISABLE:
				$attributes = [self::ID_KEY];
				if($this->hasAttribute('enddt')) {
					$attributes[] = 'enddt';
				}
				$rules = $this->getInputRulesByAttributes($attributes);
				break;
			case self::SCENARIO_DO_SAVE_JSON_MULTIPLE:
				if($this->hasAttribute('data')) {
					$attributes = ['data'];
					$rules = $this->getInputRulesByAttributes($attributes);
				}
				break;
		}
		return $rules;
	}
	
	/**
	 * Создание ошибки
	 * @param $msg
	 * @param string $code
	 * @return array
	 */
	function getErrorMessage($msg, $code = null) {
		$resp = [];
		$resp['success'] = false;
		$resp['Error_Msg'] = $msg;
		$resp['Error_Code'] = $code;
		return $resp;
	}
	/**
	 * Проверка на реализацию сценария
	 * @return null|Exception
	 */
	public function validateBeforeDoScenario()
	{
		if (!in_array($this->getScenario(), $this->scenarioList)) {
			throw new Exception('Сценарий не реализован', 500);
		}
	}

	/**
	 * Выполнение логики сценария
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array||boolean
	 */
	public function doScenario($data = array())
	{
		$result = false;
		$this->validateBeforeDoScenario();
		switch ($this->getScenario()) {
			case self::SCENARIO_DO_SAVE:
				$result = $this->doSave($data);
				break;
			case self::SCENARIO_DISABLE:
				$this->saveAsNewObject = false;
				$result = $this->doDisable($data);
				break;
			case self::SCENARIO_DO_SAVE_GRID:
				$result = $this->doSaveGrid($data);
				break;
			case self::SCENARIO_DELETE:
				$result = $this->doDelete($data);
				break;
			case self::SCENARIO_LOAD_EDIT_FORM:
				$result = $this->doLoadEditForm($data);
				break;
			case self::SCENARIO_LOAD_GRID:
				$result = $this->doLoadGrid($data);
				break;
			case self::SCENARIO_LOAD_COMBO_BOX:
				$result = $this->doLoadCombo($data);
				break;
			case self::SCENARIO_DO_SAVE_JSON_MULTIPLE:
				$result = $this->doSaveJsonMultiple($data);
				break;
			default:
				$func = $this->getScenario();
				if( method_exists($this,$func) ) {
					$result = $this->$func($data);
				}
		}
		return $result;
	}

	/**
	 * Загрузка комбобокса
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array||boolean
	 */
	public function doLoadCombo($data = array())
	{
		return $this->doLoadData($data);
	}

	/**
	 * Загрузка грида
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array||boolean
	 */
	public function doLoadGrid($data = array())
	{
		return $this->doLoadData($data);
	}

	/**
	 * Загрузка формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array||boolean
	 * @throws Exception
	 */
	public function doLoadEditForm($data = array()) {
		$resp = false;
		if($this->useCommonEditLoader) {
			$field = $this->primaryKey();
			$this->_load( $data[$field] );
			$resp = [ $this->getAttributesValues() ];
		} else {
			$resp = $this->doLoadData($data);
		}
		return $resp;
	}

	/**
	 * Возвращает значения из аттрибутов
	 * @return array
	 * @throws Exception
	 */
	public function getAttributesValues () {
		$result = array();
		foreach ($this->defAttribute as $key => $info) {
			$value = $this->getAttribute($key);
			if( $info['type']=='date' && !empty($value) && gettype($value) == 'string' ) {
				$value = substr($value,0,10);
			}
			$result[$info['alias']] = $value;
		}
		return $result;
	}

	/**
	 * Загрузка формы в зависимости от сценария
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array||boolean
	 */
	public function doLoadData($data = array())
	{
		/*  TODO создание запроса по аттрибутам в зависимости от сценария.
			1) Аттрибутам нужно добавить в property в каких сценариях они учавствуют.
			2) Прописать join'ы и select'ы.
			3) Формировать условия поиска по переданным аттрибутам? как узнать что аттрибут для условия? добавив дополнительное поле в property?
			Далее пробегаемся по аттрибутам и формируем запрос
			пока не реализовано возвращает Exception
		*/
		return $this->createError('', 'Метод doLoadData не реализован');
	}

	/**
	 * Сохранение данных грида
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array||boolean
	 */
	public function doSaveGrid($data = array())
	{
		//todo сохранение грида
		return $this->createError('', 'Метод doSaveGrid не реализован');
	}

	/**
	 * Логика после определения объекта
	 */
	function _beforeValidate()
	{
		$id = $this->getAttribute(self::ID_KEY);
		// у объекта есть аттрибут дата начала действия? записи и он не передан при добавлении?
		if ($this->hasAttribute('begdt') && empty($id)) {
			$begdt = $this->getAttribute('begdt');
			if (empty($begdt)) {
				$this->setAttribute('begdt', date('Y-m-d H:i:s'));
			}
		}

		//этот объект дизейблим и добавляем новый
		if ($this->saveAsNewObject && !empty($id) && $this->hasAttribute('enddt')) {
			$params = [
				$this->primaryKey() => $this->getAttribute(self::ID_KEY),
				$this->tableName() . '_enddt' => $this->getAttribute('enddt'),
				'session' => $this->sessionParams
			];
			$objectClass = get_class($this);
			$objectModel = new $objectClass(); //чтобы не перезаписывать данные текущего объекта в модели
			$result = $objectModel->doDisable($params);
			if (!empty($result['Error_Msg'])) {
				throw new Exception('Ошибка при сохраненнии');
			}

			$begdt = $this->getAttribute('begdt');
			if (empty($begdt)) {
				$this->setAttribute('begdt', date('Y-m-d H:i:s'));
			}
			$this->setAttribute(self::ID_KEY, null); //чтобы в _save сохранить новый объект, а не перезаписать старый
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		if (in_array($this->scenario, array(self::SCENARIO_SET_ATTRIBUTE, self::SCENARIO_DELETE, self::SCENARIO_LOAD_EDIT_FORM))) {
			if ( empty($this->id) ) {
				throw new Exception('Не указан идентификатор объекта', 500);
			}
		}
	}

	/**
	 * Логика для дизейбла
	 */
	function _beforeDisable($data = array())
	{

		if (!empty($data)) {
			$this->applyData($data);
		}

		$this->_validateBeforeDisable();
	}

	/**
	 * Проверка перед дизейблом
	 * @return null|Exception
	 */
	function _validateBeforeDisable()
	{
		return;
	}

	/**
	 * Процедура дизейбла записи
	 * @return array|Exception
	 */
	function doDisable($data = array(), $isAllowTransaction = true)
	{
		try {
			$this->isAllowTransaction = $isAllowTransaction;
			if (!$this->beginTransaction()) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}
			$this->_beforeDisable($data);
			$tmp = $this->_disable();
			$this->setAttribute(self::ID_KEY, $tmp[0][$this->primaryKey()]);
			$this->_afterDisable($tmp);
			if (!$this->commitTransaction()) {
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
		//todo $this->_onDisable();
		return $this->_saveResponse;
	}

	/**
	 * Логика после дизейбла
	 */
	function _afterDisable($data)
	{
	}

	/**
	 * Дизейблим объект не затрагивая значения аттрибутов определенных в данный момент
	 * @return Array|Exception
	 */
	function _disable()
	{

		$keyField = $this->primaryKey();
		$enddtField = $this->tableName() . '_enddt';
		$id = $this->getAttribute(self::ID_KEY);
		$date = $this->getAttribute('enddt');
		if (empty($date)) {
			$date = date('Y-m-d H:i:s');
		}
		$params = [
			$keyField => $id,
			$enddtField => $date,
			'pmUser_id' => $this->sessionParams['pmuser_id']
		];
		$table = $this->getScheme() . '.' . $this->tableName();
		$result = $this->swUpdate($table, $params);
		if (!is_array($result) || !$result[0]['success'] || !empty($result[0]['Error_Msg'])) {
			throw new Exception('Ошибка при сохранении');
		} else {
			$result[0][$keyField] = $id;
		}
		return $result;
	}
	
	/**
	 * Валидация перед массовым сохранением
	 * @param $data
	 * @return mixed
	 */
	function validateBeforeSaveMultiple($data) {
		return true;
	}
	
	/**
	 * Сохранение json объектов
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 */
	function doSaveJsonMultiple ($data = array(), $isAllowTransaction = true) {
		$rows = json_decode($data['data'], true);
		if(!is_array($rows) || !count($rows)) {
			return $this->createError(null, 'Нет данных для сохранения');
		}
		$data['data'] = $rows;
		return $this->doSaveMultiple($data, $isAllowTransaction);
	}
	
	/**
	 * Сохранение нескольких значений
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 */
	function doSaveMultiple($data = array(), $isAllowTransaction = true) {
		$response = array();
		try {
			if(!is_array($data['data']) || !count($data['data'])) {
				throw new Exception('Нет данных для сохранения');
			}

			$rows = $data['data'];
	
			//Дополнительная валидация описывается в дочерних моделях
			if(!$this->validateBeforeSaveMultiple($rows)) {
				throw new Exception('Ошибка валидации данных');
			}

			$this->isAllowTransaction = $isAllowTransaction;
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось запустить транзакцию', 500);
			}

			$response['data'] = array();
			$response['success'] = true;

			$keyField = $this->primaryKey(true);

			foreach($rows as $row) {
				$params = $row;
				$params['pmUser_id'] = $this->getPromedUserId();
				$params[$keyField] = array(
					'value' => empty($params[$keyField]) ? null : $params[$keyField],
					'out' => true,
					'type' => 'bigint'
				);

				$spname = empty($row[$keyField]) ? $this->createProcedureName() : $this->updateProcedureName();
				
				$result = $this->execCommonSP($spname, $params);
				
				if(!$result || !$result['success']) {
					throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
				}
				$row[$keyField] = $result[0][$keyField];

				array_push($response['data'], $row);
			}

			if ( !$this->commitTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Не удалось зафиксировать транзакцию', 500);
			}
			return $response;
		}
		catch( Exception $e ) {
			$this->rollbackTransaction();
			$response['success'] = false;
			$response['Error_Code'] = $e->getCode();
			$response['Error_Msg'] = $e->getMessage();
		}
		return  $response;
	}
	
	/**
	 * Получение значений аттрибутов из входящих параметров
	 * @param $data
	 * @return array
	 */
	function getSafelyUpdateParams($data) {
		$params = [];
		$rules = $this->_getSaveInputRules();
		foreach ($rules as $alias=>$rule ) {
			if(!array_key_exists($alias,$data))
				continue;
			$params[$alias] = $data[$alias];
		}
		return $params;
	}
	
	/**
	 * Валидация перед обновлением аттрибута объекта
	 * @param $data
	 * @throws Exception
	 */
	function validateBeforeSetAttributes($data) {
		if(!count($data)) {
			throw new Exception('Не получены параметры для сохранения');
		}
		if(empty($data[$this->primaryKey(true)])) {
			throw new Exception('Пустой идентификатор объекта');
		}
	}
	
	/**
	 * Обновление аттрибутов
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array|bool
	 * @throws Exception
	 */
	function SafelyUpdate($data = array(), $isAllowTransaction = true) {
		if($isAllowTransaction) {
			return $this->doTransaction($data, 'doSafelyUpdate');
		} else {
			return $this->doSafelyUpdate($data);
		}
	}
	
	/**
	 *
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function doSafelyUpdate($data) {
		$params = $this->getSafelyUpdateParams($data);

		$this->validateBeforeSetAttributes($params);

		return $this->_SafelyUpdate($params);
	}

	/**
	 * Выполнение трансакции для функций оперирующих с данными
	 * функция должна возвращать как минимум success, Error_Msg, Error_Code
	 * @param array $data
	 * @param $func
	 * @return array
	 */
	function doTransaction($data = array(), $func) {
		$response = [];
		try {
			if(!$this->db->trans_begin()) {
				throw new Exception('Не удалось запустить трансакцию', 500);
			}

			if(!count($data)) {
				throw new Exception('Нет переданы параметры в трансакцию');
			}
			
			if(!method_exists($this,$func) ) {
				throw new Exception('Не описана функция для совершения трансакции');
			}
			
			$result = $this->$func($data);
			
			if(empty($result['success'])) {
				throw new Exception($result['Error_Msg'], $result['Error_Code']);
			}

			if ( !$this->db->trans_commit() ) {
				throw new Exception('Не удалось зафиксировать транcакцию', 500);
			}
			$response = $result;
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$response['success'] = false;
			$response['Error_Code'] = $e->getCode();
			$response['Error_Msg'] = $e->getMessage();
		}
		return $response;
	}
	
	/**
	 * Метод обновления данных в БД без использования хранимых процедур
	 * @param array $data массив данных поле => значение
	 * @return array|bool
	 * @throws Exception
	 */
	private function _SafelyUpdate($data=array()) {

		$response = [];

		$keyField = $this->primaryKey(true);
		
		$schema = $this->getScheme();
		$object = $this->tableName();

		//Инициализация запроса.
		$fields = array();
		foreach ($data as $col=>$value) {
			if($col != $keyField) {
				$fields[] = "{$col} = :{$col}";
			}
		}
		$data['pmUser_updID'] = $this->getPromedUserId();
		$fields[] = "pmUser_updID = :pmUser_updID";
		
		$fields = implode(',', $fields);

		$query = "
			UPDATE {$schema}.{$object}
			SET {$fields}
			WHERE {$keyField} = :{$keyField}
			returning {$keyField} as \"{$keyField}\" 
		";

		//echo getDebugSQL($query, $data); die;
		$result = $this->queryResult($query, $data);

		if($result && $result[0] && $result[0][$keyField]) {
			$response['success'] = true;
			$response['Error_Msg'] = null;
			$response['Error_Code'] = null;
		} else {
			$response['success'] = false;
			$response['Error_Msg'] = 'Ошибка при обновлении';
			$response['Error_Code'] = 666;
		}

		return $response;
	}
}