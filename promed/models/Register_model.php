<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Таблица регистров/справочников доступных для загрузки
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 EMSIS.
 * @author       Magafurov SM
 * @version
 * @property Register Register
 */
class Register_model extends swModel {

	/** 
	 * Сценарий добавления в регистр
	*/
	const SCENARIO_ADD = 'add';

	/**
	 * Сценарий для редактирование записи
	 */
	const SCENARIO_EDIT = 'edit';

	/**
	 * Сценарий исключения из регистра
	 */
	const SCENARIO_OUT = 'out';

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_ADD,
			self::SCENARIO_DELETE,
			self::SCENARIO_OUT,
			self::SCENARIO_EDIT
		));
	}

	/**
	 * @return string
	 */
	function getObjectSysNick() {
		return 'Register';
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName() {
		return 'Register';
	}

	/**
	 * Получение региональной схемы
	 * @return string
	 */
	function getScheme() {
		return 'r2';
	} 

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME
				),
				'alias' => 'Register_id',
				'label' => 'Идентификатор',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'person_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'registertype_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'RegisterType_id',
				'label' => 'Тип регистра',
				'save'  => 'trim',
				'type'  => 'id'
			),
			'setdate' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'Register_setDate',
				'label' => 'Дата включения в регистр',
				'save' => 'trim',
				'type' => 'date'
			),
			'disdate' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				),
				'alias' => 'Register_disDate',
				'label' => 'Дата исключения из регистра',
				'save' => 'trim|required',
				'type' => 'date'
			),
			'registerdiscause_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'RegisterDisCause_id',
				'label' => 'Причина исключения',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'lpu_id'=> array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'Lpu_id',
				'label' => 'Причина исключения',
				'save' => 'trim',
				'type' => 'id'
			),
			'medpersonal_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM
				),
				'alias' => 'MedPersonal_id',
				'label' => 'Врач добавивший в регистр',
				'save' => '',
				'type' => 'id'
			),
			'registertype_code' => array(
				'properties' => array(
					//self::PROPERTY_NOT_LOAD
				),
				'alias' => 'RegisterType_Code',
				'label' => 'Код регистра',
				'save' => 'trim|required',
				'type'  => 'string'
			)
		);
	}


	/**
	 * Список аттрибутов для сохранения для каждого из сценариев
	 */
	function getAttributesKeys($name) {
		switch($name) {
			case self::SCENARIO_ADD:
				$names = ['setdate','person_id','lpu_id','registertype_id','registertype_code','medpersonal_id'];
				break;
			case self::SCENARIO_EDIT:
				$names = ['id','setdate'];
				break;
			case self::SCENARIO_OUT:
				$names = ['id','disdate','registerdiscause_id','lpu_id','medpersonal_id'];
				break;
			case self::SCENARIO_DELETE:
				$names = ['id'];
				break;
			default:
				throw new Exception('Сообщение', 'Не задан список аттрибутов для сценария');
		}
		return $names;
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name) {
		$keys = $this->getAttributesKeys($name);

		$all = array();
		foreach ($this->defAttribute as $key => $info) {
			if(!in_array($key,$keys)) continue;
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
		return $all;
	}

	/**
	 * Проверка на существование пациента в регистре
	 */
	function getRegisterId($data) {
		$params = array();
		$params['RegisterType_id'] = $this -> getAttribute('registertype_id');
		$params['Person_id'] = $this -> getAttribute('person_id');

		$query = "select top 1 Register_id 
			from ".$this->getScheme().".Register
			where RegisterType_id = :RegisterType_id
				and Person_id = :Person_id
				and isNull(Register_deleted,1) = 1
				and Register_disDate is null";

		$result = $this->getFirstResultFromQuery($query,$params);
		return $result;
	}

	/**
	 * Логика перед сохранением
	 */
	protected function _beforeSave($data = null) {
		if (!empty($data)) {
			$this->applyData($data);
		}

		switch($this->getScenario()) {
			case self::SCENARIO_ADD:
				$params = array();
				$params['Code'] = $this->getAttribute('registertype_code');
				$query = "select top 1 RegisterType_id 
						from {$this->getScheme()}.RegisterType 
						where RegisterType_Code = :Code";
				$registertype_id = $this->getFirstResultFromQuery($query,$params);
				
				if(!$registertype_id)
					throw new Exception('Тип регистра не определен');

				if(empty($data['Register_setDate'])) {
					$this->setAttribute('setdate',date('Y-m-d'));
				}
				$this->setAttribute('registertype_id',$registertype_id);
				$this->setAttribute('lpu_id', $data['session']['lpu_id']);
				$this->setAttribute('medpersonal_id', $data['session']['medpersonal_id']);

				$Register_id = $this->getRegisterId($data);
				if($Register_id) {
					$this->setAttribute( self::ID_KEY, $Register_id );
				}
			break;
			case self::SCENARIO_OUT:
				$this->setAttribute('lpu_id', $data['session']['lpu_id']);
				$this->setAttribute('medpersonal_id', $data['session']['medpersonal_id']);
			break;
		}
	}


	/**
	 * Извлечение значений атрибутов из входящих параметров, переданных из контроллера.
	 * @param array $data
	 */
	function setAttributes($data) {
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
	}

	/**
	 * Получение процедуры для сценария
	 */
	function getProcedure($scenarioName) {
		$procedure = '';
		switch($scenarioName) {
			case self::SCENARIO_OUT:
				$procedure = 'p_Register_out';
				break;
			case self::SCENARIO_EDIT:
				$procedure = 'p_Register_upd';
				break;
			case self::SCENARIO_ADD:
				$procedure = 'p_Register_ins';
				break;
			case self::SCENARIO_DELETE:
				$procedure = 'p_Register_del';
				break;
			default: 
				throw new Exception('Сообщение', 'Не задана процедура для сценария');
		}
		return $procedure;
	}

	/**
	 * Запись данных объекта в БД
	 * @param array $queryParams Параметры запроса
	 * @return array Результат выполнения запроса
	 * @throws Exception В случае ошибки запроса или ошибки возвращенной хранимкой
	 */
	protected function _save($queryParams = array()) {
		$scenarioName = $this->getScenario();

		if($scenarioName == self::SCENARIO_ADD && $this->getAttribute(self::ID_KEY)) {
			$this->_saveResponse[$this->primaryKey(true)] = $this->id;
			return [$this->_saveResponse];
		}

		if (empty($queryParams)) {
			$queryParams = array();
			$queryParams[$this->primaryKey()] = array(
				'value' => $this->id,
				'out' => true,
				'type' => 'bigint'
			);
			$queryParams['pmUser_id'] = $this->promedUserId;

			$keys = $this->getAttributesKeys( $scenarioName );

			foreach ($this->defAttribute as $key => $info) {
				if(!in_array($key,$keys)) continue;
				if (!in_array(self::PROPERTY_IS_SP_PARAM, $info['properties'])) continue;
				
				$queryParams[$this->_getColumnName($key, $info)] = $this->getAttribute($key);
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

		$sp_name = $this->getScheme().'.'.$this->getProcedure($scenarioName);

		$result = $this->execCommonSP($sp_name, $queryParams);
		if (empty($result)) {
			throw new Exception('Ошибка запроса записи данных объекта в БД', 500);
		}
		if (isset($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
		}
		return $result;
	}
}