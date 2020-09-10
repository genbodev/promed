<?php defined('BASEPATH') or die('No direct script access allowed');

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель объектов "Запись регистра"
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Александр Пермяков
 * @version      02.2015
 *
 * @property-read int $PersonRegisterType_id Тип регистра
 * @property integer Person_id Человек required
 * @property integer Diag_id Диагноз (справочник МКБ-10)
 * @property integer $MorbusType_id
 * @property integer Morbus_id Простое заболевание
 * @property string $code Код записи
 * @property datetime $setDate Дата включения в формате гггг-мм-дд
 * @property integer $MedPersonal_iid Добавил человека в регистр - врач
 * @property integer $Lpu_iid Добавил человека в регистр - ЛПУ
 * @property integer $EvnNotifyBase_id Извещение/направление, по которому человек был включен в регистр
 * @property datetime $disDate Дата исключения человека из регистра
 * @property integer PersonRegisterOutCause_id Причина исключения из регистра
 * @property integer $MedPersonal_did Кто исключил человека из регистра - врач
 * @property integer $Lpu_did Кто исключил человека из регистра - ЛПУ
 *
 * @property-read string $userGroupCode Код группы пользователей «Регистр по ...»
 * @property-read array $registerOperatorGroupCodeList Список кодов групп пользователей, имеющих полный доступ к форме «Регистр по ...» и действиям с записями регистра
 * @property-read array $exportOperatorGroupCodeList Список кодов групп пользователей, имеющих доступ для выгрузки в федеральный регистр
 * @property-read string $personRegisterTypeSysNick Тип регистра
 * @property-read int $personRegisterOutCauseCode Код причины исключения из регистра
 * @property-read array $personData Данные человека Person_BirthDay, Server_id, PersonEvn_id
 * @property string $morbusTypeSysNick Тип заболевания
 * @property-read string $exportPath Путь к папке для экспорта записей регистра этого типа
 * @property-read string $exportTemplateName Имя шаблона для экспорта записей регистра этого типа
 * @property-read int $exportLimit Ограничение размера xml-файла для выгрузки
 */
class PersonRegisterBase_model extends swPgModel
{
	protected $_exportLimit = null;
	protected $_exportPath = null;
	protected $_userGroupCode = null;
	protected $_personRegisterTypeSysNick = null;
	protected $_PersonRegisterType_id = null;
	protected $_morbusTypeSysNick = null;
	protected $_MorbusType_id = null;
	protected $_personData = array();
	private $_personRegisterOutCauseCode = null;

	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;

	/**
	 * Имя шаблона для экспорта записей регистра этого типа
	 * @return string
	 */
	function getExportTemplateName()
	{
		return $this->_personRegisterTypeSysNick . '_person_register';
	}

	/**
	 * Ограничение размера xml-файла для выгрузки
	 * @return int
	 */
	function getExportLimit()
	{
		return $this->_exportLimit;
	}

	/**
	 * Путь к папке для экспорта записей регистра этого типа
	 * @return string
	 */
	function getExportPath()
	{
		if (empty($this->_exportPath)) {
			$this->_exportPath = EXPORTPATH_ROOT . $this->_personRegisterTypeSysNick . '_person_register/';
		}
		return $this->_exportPath;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function getPersonData()
	{
		if ( empty($this->_personData) ) {
			if ( empty($this->Person_id) ) {
				throw new Exception('Нужно указать человека');
			}
			$this->_personData = $this->getFirstRowFromQuery('
				select
					ps.Person_BirthDay as "Person_BirthDay",
					PS.Server_id as "Server_id",
					PS.PersonEvn_id as "PersonEvn_id"
				from v_PersonState PS
				where PS.Person_id = :Person_id
				limit 1 
			', array('Person_id'=>$this->Person_id)
			);
			if (empty($this->_personData)) {
				throw new Exception('Человек не найден');
			}
		}
		return $this->_personData;
	}

	/**
	 * Определение кода группы пользователей «Регистр по ...»
	 * @return string
	 */
	function getUserGroupCode()
	{
		return $this->_userGroupCode;
	}

	/**
	 * @return array Список кодов групп пользователей, имеющих полный доступ к форме «Регистр по ...» и действиям с записями регистра
	 */
	function getRegisterOperatorGroupCodeList()
	{
		if (empty($this->userGroupCode)) {
			return array();
		}
		return array($this->userGroupCode);
	}

	/**
	 * @return array Список кодов групп пользователей, имеющих доступ для выгрузки в федеральный регистр
	 */
	function getExportOperatorGroupCodeList()
	{
		if (empty($this->userGroupCode)) {
			return array();
		}
		return array($this->userGroupCode);
	}

	/**
	 * Определение кода причины исключения из регистра
	 * @return int
	 * @throws Exception
	 */
	function getPersonRegisterOutCauseCode()
	{
		if (empty($this->PersonRegisterOutCause_id)) {
			return null;
		}
		if (empty($this->_personRegisterOutCauseCode)) {
			$this->_personRegisterOutCauseCode = (int) $this->getFirstResultFromQuery('
			select PersonRegisterOutCause_Code as "PersonRegisterOutCause_Code" from dbo.v_PersonRegisterOutCause where PersonRegisterOutCause_id = :PersonRegisterOutCause_id
			', array('PersonRegisterOutCause_id' => $this->PersonRegisterOutCause_id));
			if (empty($this->_personRegisterOutCauseCode)) {
				throw new Exception('Попытка получить код причины исключения из регистра провалилась', 500);
			}
		}
		return $this->_personRegisterOutCauseCode;
	}

	/**
	 * Определение типа регистра
	 * @return string
	 */
	function getPersonRegisterTypeSysNick()
	{
		return $this->_personRegisterTypeSysNick;
	}

	/**
	 * Определение типа регистра
	 * @return int
	 * @throws Exception
	 */
	function getPersonRegisterType_id()
	{
		if (empty($this->_PersonRegisterType_id)) {
			$this->_PersonRegisterType_id = $this->getFirstResultFromQuery('
			select PersonRegisterType_id as "PersonRegisterType_id" from dbo.v_PersonRegisterType where PersonRegisterType_SysNick like :PersonRegisterType_SysNick
			', array('PersonRegisterType_SysNick' => $this->personRegisterTypeSysNick));
			if (empty($this->_PersonRegisterType_id)) {
				throw new Exception('Попытка получить идентификатор типа регистра провалилась', 500);
			}
		}
		return $this->_PersonRegisterType_id;
	}

	/**
	 * Определение типа заболевания/нозологии
	 * @return int
	 * @throws Exception
	 */
	function getMorbusType_id()
	{
		if (empty($this->Diag_id)
			|| false == swPersonRegister::isAllowMorbusType($this->personRegisterTypeSysNick)
		) {
			return null;
		}
		if (empty($this->_MorbusType_id)) {
			// Проверка соответствия диагноза типу регистра
			$type_id = $this->PersonRegisterType_id;
			$arr = $this->loadTypeListByDiag($this->Diag_id, $type_id);
			if (empty($arr) || empty($arr[$type_id])) {
				throw new Exception('Выбранный диагноз не сопоставлен этому типу регистра');
			}
			//$this->_savedData['morbustype_id'] = $arr[$type_id]['MorbusType_id'];
			$this->_MorbusType_id = $arr[$type_id]['MorbusType_id'];
			$this->_morbusTypeSysNick = $arr[$type_id]['MorbusType_SysNick'];
		}
		return $this->_MorbusType_id;
	}

	/**
	 * Определение типа заболевания/нозологии
	 * @return string
	 * @throws Exception
	 */
	function getMorbusTypeSysNick()
	{
		if (empty($this->MorbusType_id)
			|| false == swPersonRegister::isAllowMorbusType($this->personRegisterTypeSysNick)
		) {
			return null;
		}
		return $this->_morbusTypeSysNick;
	}

	/**
	 * Конструктор
	 * @param string $personRegisterTypeSysNick
	 * Решение с передачей в конструктор параметров приемлемо только тогда,
	 * когда данные записи регистра хранятся только в таблице PersonRegister,
	 * когда записи регистра не надо выгружать в федерельный регистр,
	 * когда бизнес-логика записи регистра мало отличается от общей логики этого класса.
	 * В остальных случаях нужно создать новый класс, который унаследует и перекроет свойства и методы этого класса,
	 * а также обязательно определит свойства _personRegisterTypeSysNick и при необходимости _userGroupCode
	 */
	function __construct($personRegisterTypeSysNick = '')
	{
		$this->load->library('swPersonRegister');
		if (empty($this->_personRegisterTypeSysNick) && !empty($personRegisterTypeSysNick)) {
			$this->_personRegisterTypeSysNick = $personRegisterTypeSysNick;
		}
		if (empty($this->_userGroupCode)) {
			// По умолчанию образуется из PersonRegisterType_SysNick, например, nolos в NolosRegister
			$type = str_replace(' ', '_', $this->_personRegisterTypeSysNick);
			$words = explode('_', $type);
			foreach ($words as $i => $word) {
				$words[$i] = ucfirst($word);
			}
			$this->_userGroupCode = implode('', $words) . 'Register';
		}
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_DELETE,
			'include',//включение в регистр по извещению/направлению
			'create', // Добавление в регистр оператором в поточном вводе
			'except',//исключение из регистра
			'back',//возвращение в регистр
			'export',
		));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'PersonRegister';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'PersonRegister_id';
		$arr[self::ID_KEY]['label'] = 'Запись регистра';
		unset($arr['name']);
		$arr['code']['alias'] = 'PersonRegister_Code';
		$arr['code']['label'] = 'Код записи';
		$arr['code']['save'] = 'trim|max_length[13]';
		$arr['insdt']['alias'] = 'PersonRegister_insDT';
		$arr['upddt']['alias'] = 'PersonRegister_updDT';
		$arr['personregistertype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'PersonRegisterType_id',
		);
		$arr['person_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Person_id',
			'label' => 'Человек',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isresist'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonRegister_IsResist',
			'label' => 'Резистентность',
			'save' => '',
			'type' => 'id'
		);
		$arr['persondecreedgroup_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonDecreedGroup_id',
			'label' => 'Декретированная группа населения',
			'save' => '',
			'type' => 'id'
		);
		$arr['morbustype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MorbusType_id',
		);
		$arr['morbus_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Morbus_id',
			'label' => 'Простое заболевание',
			'save' => 'trim',
			'type' => 'id'
		);
		/*$arr['morbusprofdiag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MorbusProfDiag_id',
			'label' => 'Заболевание',
			'save' => 'trim',
			'type' => 'id'
		);*/
		$arr['setdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'applyMethod'=>'_applySetDate',
			'alias' => 'PersonRegister_setDate',
			'label' => 'Дата включения',
			'save' => 'trim|required',
			'type' => 'date'
		);
		$arr['medpersonal_iid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_iid',
			'label' => 'Добавил человека в регистр - врач',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['lpu_iid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_iid',
			'label' => 'Добавил человека в регистр - ЛПУ',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['evnnotifybase_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyBase_id',
			'label' => 'Извещение',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['disdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'applyMethod'=>'_applyDisDate',
			'alias' => 'PersonRegister_disDate',
			'label' => 'Дата исключения человека из регистра',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['personregisteroutcause_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonRegisterOutCause_id',
			'label' => 'Причина исключения из регистра',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medpersonal_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_did',
			'label' => 'Кто исключил человека из регистра - врач',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_did',
			'label' => 'Кто исключил человека из регистра - ЛПУ',
			'save' => 'trim',
			'type' => 'id'
		);
		return $arr;
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applySetDate($data)
	{
		return $this->_applyDate($data, 'setdate');
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyDisDate($data)
	{
		return $this->_applyDate($data, 'disdate');
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case 'export':
				$rules = array(
					'PersonRegisterType_SysNick' => array(
						'field' => 'PersonRegisterType_SysNick',
						'label' => 'Тип записи регистра',
						'rules' => 'required',
						'type' => 'string'
					),
					'ExportType' => array(
						'field' => 'ExportType',
						'label' => 'Тип выгрузки',
						'rules' => 'trim',
						'type' => 'int',
					),
					'ExportDate' => array(
						'field' => 'ExportDate',
						'label' => 'Дата выгрузки',
						'rules' => 'trim',
						'type' => 'date'
					),
					'ExportMod' => array(
						'field' => 'ExportMod',
						'label' => 'Формат выгрузки',
						'rules' => 'trim',
						'type' => 'string'
					),
					'BegDate' => array(
						'field' => 'BegDate',
						'label' => 'Начало периода',
						'rules' => 'trim',
						'type' => 'date'
					),
					'EndDate' => array(
						'field' => 'EndDate',
						'label' => 'Конец периода',
						'rules' => 'trim',
						'type' => 'date'
					),
				);
				break;
			case 'updateField':
				$rules = array(
					array(
						'field' => 'PersonRegister_id',
						'label' => 'Запись регистра',
						'rules' => 'required',
						'type' => 'id'
					),
					array(
						'field' => 'PersonRegisterType_SysNick',
						'label' => 'Тип записи регистра',
						'rules' => 'required',
						'type' => 'string'
					),
					array(
						'field' => 'field_name',
						'label' => 'Поле',
						'rules' => 'required',
						'type' => 'string'
					),
					array(
						'field' => 'field_value',
						'label' => 'Значение',
						'rules' => 'trim',
						'type' => 'string'
					),
					array(
						'field' => 'Mode',
						'label' => 'Откуда сохраняется',
						'rules' => 'trim',
						'type' => 'string',
						'default' => 'personregister_viewform',
					)
				);
				break;
			case 'include':
				$rules = $this->_getSaveInputRules();
				/* это так-то не нужно при включении и добавлении
				 * @property datetime $disDate Дата исключения человека из регистра
				 * @property integer PersonRegisterOutCause_id Причина исключения из регистра
				 * @property integer $MedPersonal_did Кто исключил человека из регистра - врач
				 * @property integer $Lpu_did Кто исключил человека из регистра - ЛПУ
				 */
				break;
			case 'except':
				$rules = array(
					array(
						'field' => 'PersonRegister_id',
						'label' => 'Запись регистра',
						'rules' => 'required',
						'type' => 'id'
					),
					array(
						'field' => 'PersonRegister_disDate',
						'label' => 'Дата исключения человека из регистра',
						'rules' => 'required',
						'type' => 'date'
					),
					array(
						'field' => 'PersonRegisterOutCause_id',
						'label' => 'Причина исключения из регистра',
						'rules' => 'required',
						'type' => 'id'
					),
					array(
						'field' => 'MedPersonal_did',
						'label' => 'Кто исключил человека из регистра - врач',
						'rules' => 'required',
						'type' => 'id'
					),
					array(
						'field' => 'Lpu_did',
						'label' => 'Кто исключил человека из регистра - ЛПУ',
						'rules' => 'required',
						'type' => 'id'
					)
				);
				break;
			case 'back':
				$rules = array(
					'PersonRegister_id' => array(
						'field' => 'PersonRegister_id',
						'label' => 'Запись регистра',
						'rules' => 'required',
						'type' => 'id'
					),
					'EvnNotifyBase_id' => array(
						'field' => 'EvnNotifyBase_id',
						'label' => 'Направление на включение в регистр',
						'rules' => 'trim',// Обязателен при включении из журнала извещений/направлений. Если указано, то диагноз берется из направления
						'type' => 'id'
					),
					'Diag_id' => array(
						'field' => 'Diag_id',
						'label' => 'Диагноз',
						'rules' => 'trim',// Диагноз из формы включения в регистр при ручном вводе
						'type' => 'id'
					),
					'PersonRegister_setDate' => array(
						'field' => 'PersonRegister_setDate',
						'label' => 'Дата повторного включения человека из регистра',
						'rules' => 'trim',// Дата из формы включения в регистр при ручном вводе. По умолчанию текущая дата
						'type' => 'date'
					),
					/*
					array(
						'field' => 'Morbus_id',
						'label' => 'Morbus_id',
						'rules' => '',
						'type' => 'id'
					),
					 */
				);
				break;
		}
		return $rules;
	}

	/**
	 * Правила для контроллера для извлечения входящих параметров при сохранении
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();
		// параметры
		$all['PersonRegisterType_SysNick'] = array(
			// нужен для создания экземпляра модели
			'field' => 'PersonRegisterType_SysNick',
			'label' => 'Тип регистра',
			'rules' => 'trim|required',
			'type' => 'string'
		);
		/*$all['MorbusType_SysNick'] = array(
			'field' => 'MorbusType_SysNick',
			'label' => 'Тип заболевания', // обязательно для записей регистра по заболеванию
			'rules' => 'trim',
			'type' => 'string'
		);*/
		$all['Mode'] = array(
			//'new' - был исключен по причине выздоровление, оператор сказал, что у пациента новое заболевание
			//'relapse' - был исключен по причине выздоровление
			//'homecoming' - был исключен по другим причинам
			'field' => 'Mode',
			'label' => 'Режим сохранения',
			'rules' => 'trim',
			'type' => 'string'
		);
		/*
		$all['ignoreCheckAnotherDiag'] = array(
			'field' => 'ignoreCheckAnotherDiag',
			'label' => 'Флаг игнорирования проверки на наличие записей с другим диагнозом',
			'rules' => '',
			'type' => 'int'
		);
		*/
		return $all;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров, переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		if (in_array($this->scenario, array('create', self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			//$this->_params['ignoreCheckAnotherDiag'] = !empty($data['ignoreCheckAnotherDiag']);
			$this->_params['Mode'] = isset($data['Mode']) ? $data['Mode'] : null ;
			//$this->_params['MorbusType_SysNick'] = isset($data['MorbusType_SysNick']) ? $data['MorbusType_SysNick'] : null ;
		}
	}

	/**
	 * Есть ли у пользователя промеда группа для полного доступа к форме «Регистр по ...»
	 * и действиям с записями регистра
	 *
	 * При вызове в статическом контексте обязательно надо передавать параметры
	 * @param array $sessionParams
	 * @throws Exception
	 * @return boolean
	 */
	function isRegisterOperator($sessionParams = array())
	{
		if (empty($sessionParams)) {
			$sessionParams = $this->sessionParams;
		}
		if (empty($sessionParams['groups']) || false == is_string($sessionParams['groups'])) { //$_SESSION['groups']
			throw new Exception('Неправильный формат списка групп пользователя');
		}
		$userGroups = explode('|', $sessionParams['groups']);
		foreach ($this->registerOperatorGroupCodeList as $code) {
			if (in_array($code, $userGroups)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Разрешен ли пользователю промеда указанный сценарий
	 *
	 * При вызове в статическом контексте обязательно надо передавать параметры
	 * @param string $scenario
	 * @param array $sessionParams
	 * @return boolean
	 * @throws Exception
	 */
	function isAllowScenario($scenario = '', $sessionParams = array())
	{
		if (empty($sessionParams)) {
			$sessionParams = $this->sessionParams;
		}
		if (empty($scenario)) {
			$scenario = $this->scenario;
		}
		if (empty($scenario)) {
			throw new Exception('Неизвестный сценарий');
		}
		$response = true;
		if (in_array($scenario, array('create', self::SCENARIO_DELETE, self::SCENARIO_DO_SAVE,
				self::SCENARIO_SET_ATTRIBUTE,self::SCENARIO_LOAD_EDIT_FORM,'include', 'except', 'back'
			))
			&& false == $this->isRegisterOperator($sessionParams)
		) {
			$response = false;
		}
		if (in_array($scenario, array('export'))) {
			$userGroups = explode('|', $sessionParams['groups']);
			foreach ($this->exportOperatorGroupCodeList as $code) {
				if (in_array($code, $userGroups)) {
					return true;
				}
			}
			$response = false;
		}
		return $response;
	}

	/**
	 * @param string $fields
	 * @param string $viewName
	 * @param string $joins
	 * @param string $where
	 * @param array $params
	 * @return array
	 */
	protected function _beforeQuerySavedData($fields, $viewName, $joins, $where, $params)
	{
		$sql = "
			select {$fields}
			from {$viewName}
			{$joins}
			where {$where}
				and {$viewName}.PersonRegisterType_id = :PersonRegisterType_id
			limit 1
		";
		$params['PersonRegisterType_id'] = $this->PersonRegisterType_id;
		//throw new Exception(getDebugSql($sql, $params));
		return array(
			'sql' => $sql,
			'params' => $params,
		);
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();

		if ( false == swPersonRegister::isAllow($this->personRegisterTypeSysNick) ) {
			throw new Exception('Работа с данным типом регистра недоступна!');
		}

		if (false == $this->isAllowScenario()) {
			throw new Exception('Действия «Удалить», «Включить в регистр», «Исключить из регистра», «Вернуть в регистр», «Выгрузка в федеральный регистр» доступны только для оператора регистра');
		}

		if (in_array($this->scenario, array('except', 'back'))
			&& empty($this->id)
		) {
			throw new Exception('Не указан идентификатор объекта', 500);
		}

		if ('include' == $this->scenario) {
			if (empty($this->EvnNotifyBase_id)) {
				// при включении из журнала извещений, всегда обязательно
				throw new Exception('Нужно указать извещение/направление');
			}
			if (empty($this->setDate)) {
				// при включении из журнала извещений, необязательно
				$this->setAttribute('setdate', $this->currentDT);
			}
		}
		if ('except' == $this->scenario) {
			//исключение из регистра, проверки независимые от типа регистра
			if (empty($this->disDate)) {
				$this->setAttribute('disdate', $this->currentDT);
			}
			if (empty($this->MedPersonal_did)) {
				throw new Exception('Нужно указать кто исключил - Врач');
			}
			if (empty($this->Lpu_did)) {
				throw new Exception('Нужно указать кто исключил - МО');
			}
		}

		if (in_array($this->scenario, array('create', self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE, 'include','except'))) {
			if ( empty($this->PersonRegisterType_id) ) {
				throw new Exception('Неправильный тип регистра');
			}
			if ( false == $this->isNewRecord && $this->_isAttributeChanged('PersonRegisterType_id') ) {
				throw new Exception('Нельзя изменить тип регистра');
			}
			if ( empty($this->MedPersonal_iid) && 'except' != $this->scenario ) {
				throw new Exception('Нужно указать кто добавил человека в регистр - врач');
			}
			if ( empty($this->Lpu_iid) && 'except' != $this->scenario ) {
				throw new Exception('Нужно указать кто добавил человека в регистр - ЛПУ');
			}
			// логика, которая зависит от типа регистра, чтобы была возможность перекрыть их проверки
			$this->_checkPersonRegisterOutCause();
			$this->_checkSetDate();
			$this->_checkDisDate();
			$this->_checkChangeCode();
			if ( 'except' !== $this->scenario ) $this->_checkEvnNotifyBase();
			$this->_checkPerson();
			$this->_checkChangeDiagId();
			if ( $this->isNewRecord && empty($this->_params['Mode']) ) {
				// Проверка на существование записи регистра после проверок человека и диагноза
				$this->_checkPersonRegisterExist();
			}
		}
	}

	/**
	 * Контроль причины исключения из регистра
	 * @throws Exception
	 */
	protected function _checkPersonRegisterOutCause()
	{
		if ('except' == $this->scenario) {
			if (empty($this->PersonRegisterOutCause_id)) {
				throw new Exception('Нужно указать причину исключения');
			}
			if (in_array($this->personRegisterTypeSysNick, array(
					//'orphan', // закомментил, ибо https://redmine.swan.perm.ru/issues/99492
					'nolos',
				)) && false == in_array($this->personRegisterOutCauseCode, array(1,2,9))
			) {
				throw new Exception('Нужно указать причину исключения смерть, смена места жительства или иное');
			}
		}
		if (in_array($this->personRegisterTypeSysNick, array(// эти не могут быть исключены без причины
				'orphan',
				'nolos',
			)) && empty($this->PersonRegisterOutCause_id)) {
			$this->setAttribute('disdate', null);

		}
	}

	/**
	 * Контроль направления/извещения включения в регистр
	 * @throws Exception
	 */
	protected function _checkEvnNotifyBase()
	{
		if ($this->_isAttributeChanged('EvnNotifyBase_id')) {
			if ( false == $this->isNewRecord ) {
				throw new Exception('Нельзя изменить направление/извещение включения в регистр');
			}
		}
	}

	/**
	 * Контроль человека
	 * @throws Exception
	 */
	protected function _checkPerson()
	{
		if ($this->_isAttributeChanged('Person_id')) {
			if ( false == $this->isNewRecord ) {
				throw new Exception('Нельзя изменить человека в записи регистра');
			}
			if ( empty($this->personData) ) {
				throw new Exception('Не удалось загрузить данные человека', 500);
			}
			/* возможно это надо будет сделать в модели для acs
			$this->load->helper('Date');
			if ($this->isNewRecord && 'acs' == $this->personRegisterTypeSysNick && getCurrentAge($this->personData['Person_BirthDay']) < 18) {
				throw new Exception('Возраст пациента составляет менее 18 лет на момент поступления в стационар.');
			}*/
		}
	}

	/**
	 * Контроль даты включения в регистр
	 * @throws Exception
	 */
	protected function _checkSetDate()
	{
		if ($this->_isAttributeChanged('setdate')) {
			if ( empty($this->setDate) || !($this->setDate instanceof DateTime)) {
				throw new Exception('Нужно указать дату включения в регистр');
			}
			// (не больше текущей)
			if ( $this->isNewRecord && $this->setDate->format('Y-m-d') > date('Y-m-d')) {
				throw new Exception('Дата включения в регистр не может быть больше текущей даты');
			}
		}
	}

	/**
	 * Контроль даты исключения из регистра
	 * @throws Exception
	 */
	protected function _checkDisDate()
	{
		if ('except' == $this->scenario && empty($this->disDate) ) {
			throw new Exception('Нужно указать дату исключения из регистра');
		}
		if (isset($this->disDate) && false == ($this->disDate instanceof DateTime)) {
			throw new Exception('Неправильный формат даты исключения из регистра');
		}
		if (isset($this->disDate) && $this->_isAttributeChanged('disDate')) {
			// не меньше или равно дате включения в регистр
			if ( $this->disDate->format('Y-m-d') <= $this->setDate->format('Y-m-d')) {
				throw new Exception('Дата исключения из регистра должна быть позже даты включения в регистр');
			}
			// не больше текущей
			if ( $this->disDate->format('Y-m-d') > date('Y-m-d')) {
				throw new Exception('Дата исключения из регистра не может быть больше текущей даты');
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeCode()
	{
		if ($this->_isAttributeChanged('Code')
			&& in_array($this->personRegisterTypeSysNick, array(
				'orphan',
				'nolos',
			))
			// на рабочей БД Перми поле PersonRegister_Code у orphan всегда пустое
			&& !($this->personRegisterTypeSysNick == 'orphan' && $this->getRegionNick() == 'perm')
		) {
			if (false == in_array($this->scenario, array(
					'include',
					'create',
				)) && empty($this->code)
			) {
				throw new Exception('Нужно указать номер записи регистра');
			}
			if ( false == empty($this->code)) {
				if ( is_numeric($this->code) == false) {
					throw new Exception('Номер должен быть целым числом');
				}
				if ( strlen($this->code) != 13) {
					throw new Exception('Номер должен содержать 13 знаков');
				}
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeResist()
	{
		if ($this->_isAttributeChanged('Resist')
			&& in_array($this->personRegisterTypeSysNick, array(
				'nolos',
			))
		) {
			if (false == in_array($this->scenario, array(
					'include',
					'create',
				)) && empty($this->Resist)
			) {
				throw new Exception('Нужно указать значение резистентности');
			}
			if ( false == empty($this->Resist)) {
				if ( is_numeric($this->Resist) == false) {
					throw new Exception('Номер должен быть целым числом');
				}
			}
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeDiagId()
	{
		if ( empty($this->Diag_id)
			&& in_array($this->personRegisterTypeSysNick, array(
				'orphan',
				'nolos',
			))
		) {
			throw new Exception('Нужно указать Диагноз!');
		}
		if ($this->_isAttributeChanged('Diag_id')) {
			if (isset($this->Diag_id)) {
				// Проверка соответствия диагноза типу регистра
				$this->setAttribute('morbustype_id', $this->MorbusType_id);
			} else {
				// без диагноза не может быть заболевания
				$this->_MorbusType_id = null;
				$this->_morbusTypeSysNick = null;
				$this->setAttribute('morbustype_id', null);
				$this->setAttribute('morbus_id', null);
			}
		}
		/*if ($this->_isAttributeChanged('morbustype_id') && false == $this->isNewRecord
			&& in_array($this->personRegisterTypeSysNick, array(
				'orphan',
				'nolos',
			))
		) {
			throw new Exception('Нельзя изменить нозологию');
		}*/
	}

	/**
	 * Обновление значения диагноза записи регистра
	 * @param int $id Идентификатор записи регистра
	 * @param mixed $value
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	function updateDiagId($id, $value, $isAllowTransaction = true)
	{
		if (!empty($value) && !is_numeric($value)) {
			// в конфиге комбика диагноза в панели просмотра указаны listeners, которые перебивают listeners в sw.Promed.SwDiagCombo, поэтому вместо айдишника код с названием диагноза
			throw new Exception('Идентификатор диагноза не число', 400);
		}
		return $this->_updateAttribute($id, 'diag_id', $value, $isAllowTransaction);
	}

	/**
	 * Обновление значения кода/номера записи регистра
	 * @param int $id Идентификатор записи регистра
	 * @param mixed $value
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	function updateCode($id, $value, $isAllowTransaction = true)
	{
		return $this->_updateAttribute($id, 'code', $value, $isAllowTransaction);
	}

	/**
	 * Обновление значения резистентности к терапии записи регистра
	 * @param int $id Идентификатор записи регистра
	 * @param mixed $value
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	function updateResist($id, $value, $isAllowTransaction = true)
	{
		return $this->_updateAttribute($id, 'isresist', $value, false);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @param bool $isAllowTransaction
	 * @return array
	 */
	function updatePersonDecreedGroupId($id, $value, $isAllowTransaction = true)
	{
		return $this->_updateAttribute($id, 'persondecreedgroup_id', $value, $isAllowTransaction);
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
		parent::_beforeUpdateAttribute($key);
		switch ($key) {
			case 'diag_id':
				$this->_checkChangeDiagId();
				break;
			case 'code':
				$this->_checkChangeCode();
				break;
			case 'resist':
				$this->_checkChangeResist();
				break;
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		$this->setAttribute('personregistertype_id', $this->PersonRegisterType_id);
		if (in_array($this->scenario, array('include','back'))
			|| $this->isNewRecord
			|| empty($this->PersonRegisterOutCause_id)
		) {
			$this->setAttribute('PersonRegisterOutCause_id', null);
			$this->setAttribute('disDate', null);
			$this->setAttribute('Lpu_did', null);
			$this->setAttribute('MedPersonal_did', null);
		}

		if ($this->isNewRecord && empty($this->EvnNotifyBase_id)) {
			//это ручной ввод новой записи регистра без извещения
			if (empty($this->Morbus_id) && swPersonRegister::isMorbusRegister($this->personRegisterTypeSysNick)) {
				throw new Exception('Должна быть реализована проверка существования и создание заболевания');
				// пока отключил, т.к. возможно это надо будет сделать в моделях записей регистра по заболеванию
				/*
				$this->load->library('swMorbus');
				//создание заболевания с проверкой на существование заболевания у человека
				$result = swMorbus::checkByPersonRegister($this->morbusTypeSysNick, array(
					'isDouble' => (isset($this->_params['Mode']) && $this->_params['Mode'] == 'new'),
					'Diag_id' => $this->Diag_id,
					'Person_id' => $this->Person_id,
					'Morbus_setDT' => $this->setDate->format('Y-m-d'),
					'session' => $this->sessionParams,
				), 'onBeforeSavePersonRegister');
				if (empty($result['Morbus_id'])) {
					throw new Exception('Проверка существования и создание заболевания. По какой-то причине заболевание не найдено и не создано');
				}
				$this->setAttribute('morbus_id', $result['Morbus_id']);
				*/
			}
		}
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
		parent::_afterSave($result);
		$this->_updateEvnNotifyRegisterInclude();
		$this->_createEvnNotifyRegisterChange();
		$this->_createEvnNotifyRegisterExcept();
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _afterUpdateAttribute($key)
	{
		$this->_createEvnNotifyRegisterChange();
	}

	/**
	 * Записываем ссылку на запись регистра после включения в регистр или возвращение в регистр по направлению
	 * @throws Exception
	 */
	protected function _updateEvnNotifyRegisterInclude()
	{
		if ($this->_isAttributeChanged('EvnNotifyBase_id')
			&& isset($this->EvnNotifyBase_id)
			&& !in_array($this->personRegisterTypeSysNick, array('palliat'))
		) {
			$instanceModelName = swPersonRegister::getEvnNotifyRegisterModelName($this->personRegisterTypeSysNick);
			$this->load->model($instanceModelName);
			// могут быть региональные модели типа ufa_EvnNotifyRegister_model, наследующие EvnNotifyRegister_model
			$className = get_class($this->{$instanceModelName});
			/**
			 * @var EvnNotifyRegister_model $instance
			 */
			$instance = new $className($this->personRegisterTypeSysNick, 1);
			$data = array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_UPDATE,
				'PersonRegister_id' => $this->id,
				'EvnNotifyRegister_id' => $this->EvnNotifyBase_id,
			);
			$instance->applyData($data);
			$data['Lpu_did'] = $instance->Lpu_id;
			$instance->setParams($data);
			$res = $instance->doSave(array(), false);
			if (!empty($res['Error_Msg'])) {
				// отменяем сохранение записи регистра
				throw new Exception($res['Error_Msg'], 500);
			}
		}
	}

	/**
	 * Создание направления на внесение изменений в регистр
	 * @throws Exception
	 */
	protected function _createEvnNotifyRegisterChange()
	{
		if ($this->_isAttributeChanged('diag_id')
			&& in_array($this->personRegisterTypeSysNick, array(
				'nolos','orphan',
			))
			&& false == $this->isNewRecord
		) {
			// для ВЗН и орфанных создаем только при изменении диагноза
			// по остальным атрибутам создается скриптом
			$instanceModelName = swPersonRegister::getEvnNotifyRegisterModelName($this->personRegisterTypeSysNick);
			$this->load->model($instanceModelName);
			// могут быть региональные модели типа ufa_EvnNotifyRegister_model, наследующие EvnNotifyRegister_model
			$className = get_class($this->{$instanceModelName});
			/**
			 * @var EvnNotifyRegister_model $instance
			 */
			$instance = new $className($this->personRegisterTypeSysNick, 2);
			$res = $instance->doSave(array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_CREATE,
				'EvnNotifyRegister_setDate' => $this->currentDT->format('Y-m-d'),
				'PersonRegister_id' => $this->id,
				'Diag_id' => $this->Diag_id,
				'Lpu_did' => $this->sessionParams['lpu_id'],
				'MedPersonal_id' => $this->sessionParams['medpersonal_id'],
				'Person_id' => $this->Person_id,
				'PersonEvn_id' => $this->personData['PersonEvn_id'],
				'Server_id' => $this->personData['Server_id'],
			), false);
			if (!empty($res['Error_Msg'])) {
				// отменяем сохранение записи регистра
				throw new Exception($res['Error_Msg'], 500);
			}
		}
	}

	/**
	 * Создание объекта «Извещение об исключении из регистра»
	 * @throws Exception
	 */
	protected function _createEvnNotifyRegisterExcept()
	{
		if ('except' == $this->scenario) {
			//при сохранении формы исключения записи из регистра
			$instanceModelName = swPersonRegister::getEvnNotifyRegisterModelName($this->personRegisterTypeSysNick);
			$this->load->model($instanceModelName);
			// могут быть региональные модели типа ufa_EvnNotifyRegister_model, наследующие EvnNotifyRegister_model
			$className = get_class($this->{$instanceModelName});
			/**
			 * @var EvnNotifyRegister_model $instance
			 */
			$instance = new $className($this->personRegisterTypeSysNick, 3);
			$res = $instance->doSave(array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_CREATE,
				'EvnNotifyRegister_setDate' => $this->disDate->format('Y-m-d'),
				'PersonRegister_id' => $this->id,
				'PersonRegisterOutCause_id' => $this->PersonRegisterOutCause_id,
				'Lpu_did' => $this->Lpu_did,
				'MedPersonal_id' => $this->MedPersonal_did,
				'Person_id' => $this->Person_id,
				'PersonEvn_id' => $this->personData['PersonEvn_id'],
				'Server_id' => $this->personData['Server_id'],
			), false);
			if (!empty($res['Error_Msg'])) {
				// отменяем исключение
				throw new Exception($res['Error_Msg'], 500);
			}
		}
	}

	/**
	 * Получение данных для контроля на существование записи регистра при добавлении записи
	 * @throws Exception
	 */
	protected function _loadDataCheckExist()
	{
		$queryParams = array(
			'Person_id' => $this->Person_id,
			'PersonRegisterType_id' => $this->PersonRegisterType_id,
		);
		$add_select = '';
		$add_join = '';
		$add_where = 'AND PR.PersonRegisterType_id = :PersonRegisterType_id ';
		switch (true) {
			case ('nolos' == $this->personRegisterTypeSysNick):
			case ('orphan' == $this->personRegisterTypeSysNick): // одинаково для 'nolos' и 'orphan'
				if (empty($this->MorbusType_id)) {
					throw new Exception('Выбранный диагноз не сопоставлен нозологии', 500);
				}
				$queryParams['MorbusType_id'] = $this->MorbusType_id;
				$add_where .= 'AND PR.MorbusType_id = :MorbusType_id ';
				break;
			default:
				$queryParams['Diag_id'] = $this->Diag_id;
				$add_where .= 'AND PR.Diag_id in (select DD.Diag_id from v_Diag D left join v_Diag DD on D.Diag_pid = DD.Diag_pid where D.Diag_id = :Diag_id) ';
				break;
		}
		$q = "
			SELECT
				PR.PersonRegister_id as \"PersonRegister_id\",
				to_char(PR.PersonRegister_disDate, 'dd.mm.yyyy') as \"PersonRegister_disDate\",
				OutCause.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
				OutCause.PersonRegisterOutCause_Code as \"PersonRegisterOutCause_Code\"
				{$add_select}
			FROM
				v_PersonRegister PR
				left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				{$add_join}
			WHERE
				PR.Person_id = :Person_id {$add_where}

			ORDER BY
				PR.PersonRegister_disDate ASC, PR.PersonRegister_insDT DESC
			limit 1
		";
		//throw new Exception(getDebugSQL($q, $queryParams));
		$result = $this->db->query($q, $queryParams);
		if (!is_object($result)) {
			throw new Exception('Ошибка БД!');
		}
		return $result->result('array');
	}

	/**
	 * Возвращение человека в регистр после исключения из регистра по некоторым причинам
	 * Может произойти при включении человека в регистр по извещению или при ручном добавлении в регистр
	 * @param array $data
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * @return array
	 * @throws Exception
	 */
	function doBack($data, $isAllowTransaction = true)
	{
		// одинаково для 'nolos' и 'orphan'
		if (empty($data['EvnNotifyBase_id']) || empty($data['PersonRegister_id'])) {
			throw new Exception('Неправильные параметры');
		}
		$this->setScenario('back');
		$this->setParams(array(
			'session' => $data['session'],
		));
		// Получаем данные записи регистра
		$this->setAttributes(array('PersonRegister_id' => $data['PersonRegister_id']));
		// Проверяем причину
		if ($this->personRegisterOutCauseCode != 2) {
			throw new Exception('Возвращение человека в регистр возможно, если был исключен по причине выехал');
		}
		// Получаем данные направления
		$this->load->model('EvnNotifyRegister_model');
		// могут быть региональные модели типа ufa_EvnNotifyRegister_model, наследующие EvnNotifyRegister_model
		$className = get_class($this->EvnNotifyRegister_model);
		/**
		 * @var EvnNotifyRegister_model $instance
		 */
		$instance = new $className($this->personRegisterTypeSysNick, 1);
		$instance->setParams(array(
			'session' => $this->sessionParams,
		));
		$instance->setAttributes(array('EvnNotifyRegister_id' => $data['EvnNotifyBase_id']));
		if (empty($instance->Diag_id)) {
			throw new Exception('Не удалось загрузить направление');
		}
		// Обновляем запись регистра
		$this->setAttribute('setDate', $this->currentDT);
		$this->setAttribute('EvnNotifyBase_id', $instance->id);
		$this->setAttribute('Lpu_iid', $instance->Lpu_id);
		$this->setAttribute('MedPersonal_iid', $instance->MedPersonal_id);
		$this->setAttribute('Diag_id', $instance->Diag_id);
		$this->setAttribute('PersonRegisterOutCause_id', null);
		$this->setAttribute('disDate', null);
		$this->setAttribute('Lpu_did', null);
		$this->setAttribute('MedPersonal_did', null);
		return $this->doSave(array(), $isAllowTransaction);
	}

	/**
	 * Выгрузка в федеральный регистр регионального сегмента
	 * @param array $data
	 * @return array
	 */
	function doExport($data)
	{
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		try {
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_".time();
			if (!file_exists($this->exportPath)) {
				if (false == mkdir( $this->exportPath )) {
					throw new Exception('Не удалось создать корневую папку для экспортируемых файлов регистра');
				}
			}
			if (false == mkdir( $this->exportPath.$out_dir )) {
				throw new Exception('Не удалось создать папку для экспортируемых файлов регистра');
			}

			$this->_saveResponse['ExportErrorArray'] = array();
			$this->_saveResponse['ExportErrorArray'][] = array(
				'Text' => 'Начало выполнения запроса к базе данных',
				'Time' => date('H:i:s'),
			);
			$export_data = $this->_loadExportData($data);
			if ( empty($export_data) ) {
				$this->_saveResponse['ExportErrorArray'][] = array(
					'Text' => 'Окончание выгрузки. При указанных параметрах нет записей для выгрузки',
					'Time' => date('H:i:s'),
				);
				return $this->_saveResponse;
			}
			$this->_saveResponse['ExportErrorArray'][] = array(
				'Text' => 'Получили все данные из базы данных',
				'Time' => date('H:i:s'),
			);

			$template = $this->exportTemplateName;
			$this->load->library('parser');
			array_walk_recursive($export_data, 'ConvertFromWin1251ToUTF8');
			$files_array = array();     //Массив файлов выгрузки
			if (empty($this->exportLimit)) {
				//Записываем в 1 файл
				$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r<root>";
				$short_file_name = strtoupper($template);
				$xml_file_name = $this->exportPath.$out_dir."/".$short_file_name.".xml";
				foreach ($export_data as $row) {
					$xml .= $this->parser->parse('export_xml/'.$template, $row, true);
				}
				$xml .= "</root>";
				$xml = str_replace('&', '&amp;', $xml);
				file_put_contents($xml_file_name, $xml);
				$files_array[$xml_file_name] = $short_file_name;
			} else {
				$j=1;   //счётчик частей файлов
				$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r<root>";
				$short_file_name = strtoupper($template) . '_'.$j;
				$xml_file_name = $this->exportPath.$out_dir."/".$short_file_name.".xml";
				//Записываем в файл построчно и смотрим сколько записали на очередной итерации, по достижению лимита создаём новый файл и пишем в него
				foreach ($export_data as $row) {
					$xml .= $this->parser->parse('export_xml/'.$template, $row, true);
					if (file_put_contents($xml_file_name, $xml) > $this->exportLimit) {
						$xml .= "</root>";
						$xml = str_replace('&', '&amp;', $xml);
						file_put_contents($xml_file_name, $xml);
						$files_array[$xml_file_name] = $short_file_name;
						$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r<root>";
						$j++;
						$short_file_name = strtoupper($template) . '_'.$j;
						$xml_file_name = $this->exportPath.$out_dir."/".$short_file_name.".xml";
					}
				}
				$xml .= "</root>";
				$xml = str_replace('&', '&amp;', $xml);
				file_put_contents($xml_file_name, $xml);
				$files_array[$xml_file_name] = $short_file_name;
			}

			$file_zip_sign = $short_file_name;
			$file_zip_name = $this->exportPath.$out_dir."/".$file_zip_sign.".zip";
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			foreach ($files_array as $key => $value) {
				$zip->AddFile( $key, $value . ".xml" );
			}
			$zip->close();
			$this->_saveResponse['ExportErrorArray'][] = array(
				'Text' => 'Создан файл архива реестра',
				'Time' => date('H:i:s'),
			);

			foreach ($files_array as $key => $value) {
				unlink($key);
			}

			if (file_exists($file_zip_name)) {
				$this->_saveResponse['Link'] = $file_zip_name;
			} else {
				$this->_saveResponse['ExportErrorArray'][] = array(
					'Text' => 'Ошибка создания файла архива реестра',
					'Time' => date('H:i:s'),
				);
			}
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
		}
		return $this->_saveResponse;
	}

	/**
	 * Запрос данных для выгрузки в федеральный регистр регионального сегмента
	 * @param $data
	 * @return array
	 * @throws Exception
	 * @todo перекрыть или реализовать тут
	 */
	protected function _loadExportData($data)
	{
		if ( false == swPersonRegister::isAllow($this->personRegisterTypeSysNick) ) {
			throw new Exception('Работа с данным типом регистра недоступна!');
		}
		$this->setScenario('export');
		$this->setParams($data);

		if (false == $this->isAllowScenario()) {
			throw new Exception('Действие «Выгрузка в федеральный регистр» не доступно');
		}
		return array();
	}

	/**
	 * @param $type_id
	 * @return array
	 * @throws Exception
	 */
	function loadChangedPersonRegisterIdList($type_id)
	{
		$params = array();
		$params['PersonRegisterType_id'] = $type_id;
		/*
		 * При выборе «Изменения» формировать xml-файл, содержащий сведения о таких записях регистра,
		 *  для которых с момента предыдущей выгрузки были созданы следующие  объекты:
		 * – Направление на включение в регистр
		 * – Направление на внесение изменений в регистр
		 * – Извещение об исключении из регистра
		 * (т.е. дата создания данных объектов больше даты последней выгрузки)
		 */
		// Получаем дату и время последней выгрузки
		$lastExport = $this->getFirstResultFromQuery('
				select MAX(PersonRegisterExport_updDT) as "lastExport"
				from PersonRegisterExport E
				inner join PersonRegister R on R.PersonRegister_id = E.PersonRegister_id
				where R.PersonRegisterType_id = :PersonRegisterType_id
			', $params);
		// Получаем список PersonRegister_id, по которым есть изменения
		$listId = array();
		if (isset($lastExport) && $lastExport instanceof DateTime) {
			$lastExport = $lastExport->format('Y-m-d');
			$query = "
					select E.PersonRegister_id as \"PersonRegister_id\"
					from v_EvnNotifyRegister E
					where E.PersonRegisterType_id = :PersonRegisterType_id and E.PersonRegister_id is not null
					and E.EvnNotifyRegister_insDT >= cast ('{$lastExport}' as timestamp)
				";
		} else {
			$query = "
					select E.PersonRegister_id as \"PersonRegister_id\"
					from v_EvnNotifyRegister E
					where E.PersonRegisterType_id = :PersonRegisterType_id and E.PersonRegister_id is not null
				";
		}
		//throw new Exception(getDebugSQL($query, $params));
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$tmp = $result->result('array');
			foreach ($tmp as $row) {
				$listId[] = $row['PersonRegister_id'];
			}
		} else {
			throw new Exception('Ошибка запроса к БД', 500);
		}
		return $listId;
	}

	/**
	 * Делаем записи о выгрузке
	 * @param $PersonRegister_id
	 * @param $PersonRegisterExportType_id
	 * @param $pmUser_id
	 * @throws Exception
	 */
	protected function _insertPersonRegisterExport($PersonRegister_id, $PersonRegisterExportType_id, $pmUser_id)
	{
		$query = "
			select
				PersonRegisterExport_id as \"PersonRegisterExport_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_PersonRegisterExport_ins(
				PersonRegisterExport_id := :PersonRegisterExport_id,
				PersonRegisterExportType_id := :PersonRegisterExportType_id,
				PersonRegister_id := :PersonRegister_id,
				PersonRegisterExport_setDate := dbo.tzGetDate(),
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'PersonRegisterExport_id' => null,
			'PersonRegister_id' => $PersonRegister_id,
			'PersonRegisterExportType_id' => $PersonRegisterExportType_id,
			'pmUser_id' => $pmUser_id
		));
		if ( !is_object($result) ) {
			throw new Exception('Ошибка запроса к БД при записи о выгрузке');
		}
		$res = $result->result('array');
		if ( false == empty($res[0]['Error_Msg']) ) {
			throw new Exception($res[0]['Error_Msg']);
		}
	}

	/**
	 * Контроль на существование записи регистра при добавлении записи
	 * @throws Exception
	 */
	protected function _checkPersonRegisterExist()
	{
		switch (true) {
			case ('orphan' == $this->personRegisterTypeSysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с данным орфанным заболеванием';
				break;
			case ('nolos' == $this->personRegisterTypeSysNick):
				$error_msg = 'На выбранного пациента уже существует запись регистра с типом «ВЗН»';
				break;
			default:
				$error_msg = 'На выбранного пациента уже существует запись регистра с выбранной группой диагнозов.';
				break;
		}
		$response = $this->_loadDataCheckExist();
		if (count($response) > 0) {
			switch (true) {
				case (empty($response[0]['PersonRegisterOutCause_Code'])):
					//Если уже есть открытая запись регистра, то выводить сообщение: "На выбранного пациента уже существует запись регистра ...", новую запись регистра не создавать.
					throw new Exception($error_msg);
					break;
				case ($response[0]['PersonRegisterOutCause_Code'] == 1):
					//Если уже есть запись регистра с причиной исключения из регистра "смерть"
					throw new Exception('Пациент был исключен из регистра по причине "смерть", <br />включение в регистр невозможно');
					break;
				case ($response[0]['PersonRegisterOutCause_Code'] == 2):
					//Если уже есть запись регистра с причиной исключения из регистра "выехал"
					$this->_saveResponse['PersonRegister_oid'] = $response[0]['PersonRegister_id'];
					$this->_saveResponse['PersonRegisterOutCause_Code'] = $response[0]['PersonRegisterOutCause_Code'];
					$this->_saveResponse['Alert_Msg'] = 'Пациент был исключен из регистра по причине "выехал". <br />Вернуть пациента в регистр?'; // Да/Нет
					$this->_saveResponse['Yes_Mode'] = 'homecoming';
					// При нажатии "Нет", форму закрывать, новую запись регистра не создавать.
					//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос
					throw new Exception('YesNo');
					break;
				case ($response[0]['PersonRegisterOutCause_Code'] == 3):
					//Если уже есть запись регистра с причиной исключения из регистра "Выздоровление"
					$this->_saveResponse['PersonRegister_oid'] = $response[0]['PersonRegister_id'];
					$this->_saveResponse['PersonRegisterOutCause_Code'] = $response[0]['PersonRegisterOutCause_Code'];
					$this->_saveResponse['Alert_Msg'] = 'Пациент был исключен из регистра по причине "Выздоровление". <br />У пациента новое заболевание?'; //(Новое/Предыдущее/Отмена)
					$this->_saveResponse['Yes_Mode'] = 'new';// Новое
					$this->_saveResponse['No_Mode'] = 'relapse'; //Предыдущее
					// При нажатии "Отмена", форму закрывать, новую запись регистра не создавать.
					//отменяем сохранение, пользователю показываем Alert_Msg и выводим вопрос
					throw new Exception('YesNo');
					//При нажатии "Новое" создавать новое заболевание/новую запись регистра.
					// При нажатии "Предыдущее" удалить дату закрытия заболевания/запись регистра (вся ранее введенная специфика становится доступна для ввода/редактирования)
					break;
			}
		}
	}

	/**
	 * Логика после успешного выполнения запроса удаления объекта внутри транзакции
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterDelete($result)
	{
		//$this->load->library('swMorbus');
		//swMorbus::onAfterDeletePersonRegister($this);
	}

	/**
	 * @return array Список типов в формате '3' => array('PersonRegisterType_SysNick' => 'onko')
	 * @throws Exception
	 */
	public function loadTypeList()
	{
		$result = $this->db->query('
			SELECT
				PersonRegisterType_id as "PersonRegisterType_id",
				PersonRegisterType_SysNick as "PersonRegisterType_SysNick"
			FROM dbo.v_PersonRegisterType
		', array());
		if (false == is_object($result)) {
			throw new Exception('При запросе к БД возникла ошибка', 500);
		}
		$tmp = $result->result('array');
		$response = array();
		foreach($tmp as $row) {
			$key = $row['PersonRegisterType_id'];
			unset($row['PersonRegisterType_id']);
			$response[$key] = $row;
		}
		return $response;
	}

	/**
	 * @param array $diag_list Диагнозы, для фильтрации типов
	 * @return array Список типов в формате '49' => array('PersonRegisterType_id' => '49','PersonRegisterType_SysNick' => 'nolos','MorbusType_id' => '3','MorbusType_SysNick' => 'onko', 'Diag_id' => '3516')
	 * @throws Exception
	 */
	public function loadTypeListByDiagList($diag_list)
	{
		if ( empty($diag_list) || false == is_array($diag_list) ) {
			return array();
		}
		$diag_list = implode(',', $diag_list);
		$result = $this->db->query("
			select
				prt.PersonRegisterType_id as \"PersonRegisterType_id\",
				prt.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\",
				mt.MorbusType_id as \"MorbusType_id\",
				mt.MorbusType_SysNick as \"MorbusType_SysNick\",
				rtd.Diag_id as \"Diag_id\"
			from v_PersonRegisterDiag rtd
				inner join v_PersonRegisterType prt on prt.PersonRegisterType_id = rtd.PersonRegisterType_id
				left join v_MorbusType mt on mt.MorbusType_id = rtd.MorbusType_id
			where rtd.Diag_id in ({$diag_list})
			order by prt.PersonRegisterType_id
		");
		if (false == is_object($result)) {
			throw new Exception('При запросе к БД возникла ошибка', 500);
		}
		$tmp = $result->result('array');
		$response = array();
		foreach($tmp as $row) {
			$key = $row['PersonRegisterType_id'];
			$response[$key] = $row;
		}
		return $response;
	}

	/**
	 * @param int $diag_id Диагноз, для фильтрации типов
	 * @param int $type_id Тип регистра для проверки правильности диагноза или проверки наличия MorbusType_id
	 * @return array Список типов в формате '49' => array('PersonRegisterType_id' => '49','PersonRegisterType_SysNick' => 'nolos','MorbusType_id' => '3','MorbusType_SysNick' => 'onko')
	 * @throws Exception
	 */
	public function loadTypeListByDiag($diag_id, $type_id = null)
	{
		if (empty($diag_id)) {
			return array();
		}
		$params = array('Diag_id' => $diag_id);
		$add_where = '';
		if (!empty($type_id)) {
			$params['PersonRegisterType_id'] = $type_id;
			$add_where .= ' AND prt.PersonRegisterType_id = :PersonRegisterType_id';
		}
		$result = $this->db->query("
			select
				prt.PersonRegisterType_id as \"PersonRegisterType_id\",
				prt.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\",
				mt.MorbusType_id as \"MorbusType_id\",
				mt.MorbusType_SysNick as \"MorbusType_SysNick\"
			from v_PersonRegisterDiag rtd
				inner join v_PersonRegisterType prt on prt.PersonRegisterType_id = rtd.PersonRegisterType_id
				left join v_MorbusType mt on mt.MorbusType_id = rtd.MorbusType_id
			where rtd.Diag_id = :Diag_id {$add_where}
			-- + refs #PROMEDWEB-4709 + >>
			-- Не знали куда условие лучше разместить сюда или во вьюху
			and (cast(rtd.PersonRegisterDiag_begDate as date)<=cast(getdate() as date)
		        and
		        (cast(rtd.PersonRegisterDiag_endDate as date)>=cast(getdate() as date)  or rtd.PersonRegisterDiag_endDate is null)
		    )
			-- + refs #PROMEDWEB-4709 + <<
			order by prt.PersonRegisterType_id
		", $params);
		if (false == is_object($result)) {
			throw new Exception('При запросе к БД возникла ошибка', 500);
		}
		$tmp = $result->result('array');
		$response = array();
		foreach($tmp as $row) {
			$key = $row['PersonRegisterType_id'];
			$response[$key] = $row;
		}
		return $response;
	}

	/**
	 * Получение данных для формы просмотра записи регистра
	 */
	function getViewData($data)
	{
		if (empty($data['PersonRegister_id'])) {
			throw new Exception('Не передан идентификатор записи регистра');
		}
		if (empty($data['PersonRegisterType_SysNick'])) {
			throw new Exception('Не передан тип записи регистра');
		}
		$queryParams = array(
			'PersonRegister_id' => $data['PersonRegister_id'],
			'PersonRegisterType_SysNick' => $data['PersonRegisterType_SysNick'],
		);
		$query = "
			SELECT
				case when PR.PersonRegisterOutCause_id is null then 1 else 0 end as \"accessType\",
				PR.PersonRegister_id as \"PersonRegister_id\",
				PR.Person_id as \"Person_id\",
				PRT.PersonRegisterType_id as \"PersonRegisterType_id\",
				PRT.PersonRegisterType_SysNick as \"PersonRegisterType_SysNick\",
				MT.MorbusType_id as \"MorbusType_id\",
				MT.MorbusType_SysNick as \"MorbusType_SysNick\",
				PR.Morbus_id as \"Morbus_id\",
				PR.PersonRegister_Code as \"PersonRegister_Code\",
				OutCause.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\",
				OutCause.PersonRegisterOutCause_Name as \"PersonRegisterOutCause_Name\",
				DNS.Diag_id as \"Diag_id\",
				DNS.Diag_FullName as \"Diag_Name\",
				to_char(PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\",
				to_char(PR.PersonRegister_disDate, 'dd.mm.yyyy') as \"PersonRegister_disDate\",
				null as \"PersonPhotoThumbName\",
				RTRIM(PS.Person_SurName) as \"Person_Surname\",
				RTRIM(PS.Person_FirName) as \"Person_Firname\",
				RTRIM(PS.Person_SecName) as \"Person_Secname\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_Birthday\",
				RTRIM(PS.Person_Snils) as \"Person_Snils\",
				RTRIM(v_Sex.Sex_Name) as \"Sex_Name\",
				RTRIM(v_SocStatus.SocStatus_Name) as \"SocStatus_Name\",
				RTRIM(coalesce(UAddress.Address_Nick, UAddress.Address_Address)) as \"Person_RAddress\",
				RTRIM(coalesce(PAddress.Address_Nick, PAddress.Address_Address)) as \"Person_PAddress\",
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(Polis.Polis_Ser) END as \"Polis_Ser\",
				CASE WHEN PolisType.PolisType_Code = 4 then RTRIM(ps.Person_EdNum) ELSE RTRIM(Polis.Polis_Num) END AS \"Polis_Num\",
				to_char(Polis.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
				to_char(Polis.Polis_endDate, 'dd.mm.yyyy') as \"Polis_endDate\",
				RTRIM(PO.Org_Name) as \"OrgSmo_Name\",
				RTRIM(Document.Document_Num) as \"Document_Num\",
				RTRIM(Document.Document_Ser) as \"Document_Ser\",
				to_char(Document.Document_begDate, 'dd.mm.yyyy') as \"Document_begDate\",
				RTRIM(DOO.Org_Name) as \"OrgDep_Name\",
				RTRIM(PJ.Org_Name) as \"Person_Job\",
				RTRIM(PP.Post_Name) as \"Person_Post\",
				pcard.PersonCard_id as \"PersonCard_id\",
				CASE WHEN (pcard.PersonCard_endDate IS NOT NULL)
					THEN coalesce(RTRIM(LpuAttach.Lpu_Nick), '') || ' (Прикрепление неактуально. Дата открепления: '||coalesce(to_char(pcard.PersonCard_endDate, 'dd.mm.yyyy'), '')||')'
					ELSE RTRIM(LpuAttach.Lpu_Nick)
				end as \"LpuAttach_Nick\",
				pcard.LpuRegion_Name as \"LpuRegion_Name\",
				to_char(pcard.PersonCard_begDate, 'dd.mm.yyyy') as \"PersonCard_begDate\",
				CASE WHEN PR.PersonRegister_IsResist = 2 THEN 1 ELSE 0 END as \"PersonRegister_IsResist\",
				PDG.PersonDecreedGroup_id as \"PersonDecreedGroup_id\",
				PDG.PersonDecreedGroup_Name as \"PersonDecreedGroup_Name\"
			FROM
				v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id AND PRT.PersonRegisterType_SysNick LIKE :PersonRegisterType_SysNick
				inner join v_PersonState PS on PS.Person_id = PR.Person_id
				left join v_MorbusType MT on MT.MorbusType_id = PR.MorbusType_id
				left join v_Diag DNS on DNS.Diag_id = PR.Diag_id
				left join v_PersonRegisterOutCause OutCause on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join v_Sex on v_Sex.Sex_id = PS.Sex_id
				left join v_SocStatus on v_SocStatus.SocStatus_id = PS.SocStatus_id
				left join Address UAddress on UAddress.Address_id = PS.UAddress_id
				left join Address PAddress on PAddress.Address_id = PS.PAddress_id
				left join Polis on Polis.Polis_id = PS.Polis_id
				left join PolisType on PolisType.PolisType_id = Polis.PolisType_id
				left join OrgSmo on OrgSmo.OrgSmo_id = Polis.OrgSmo_id
				left join Org PO on PO.Org_id = OrgSmo.Org_id
				left join Document on Document.Document_id = PS.Document_id
				left join OrgDep on OrgDep.OrgDep_id = Document.OrgDep_id
				left join Org DOO on DOO.Org_id = OrgDep.Org_id
				left join v_Job Job on Job.Job_id = PS.Job_id
				left join Org PJ on PJ.Org_id = Job.Org_id
				left join Post PP on PP.Post_id = Job.Post_id
				left join v_PersonDecreedGroup PDG on PDG.PersonDecreedGroup_id = PR.PersonDecreedGroup_id
				left join lateral (select
						pc.Lpu_id,
						pc.PersonCard_id,
						pc.PersonCard_begDate,
						pc.PersonCard_endDate,
						pc.LpuRegion_Name
					from v_PersonCard pc
					where pc.Person_id = PS.Person_id and pc.LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) as pcard on true
				left join v_Lpu LpuAttach on LpuAttach.Lpu_id = PS.Lpu_id
			WHERE
				PR.PersonRegister_id = :PersonRegister_id
			limit 1
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception('Ошибка БД');
		}
		$tmp = $result->result('array');
		if (empty($tmp)) {
			throw new Exception('Не удалось загрузить данные записи регистра');
		}
		if ('nolos' == $data['PersonRegisterType_SysNick'] && empty($tmp[0]['MorbusType_SysNick'])) {
			throw new Exception('Неправильная запись регистра');
		}
		return $tmp;
	}

	/**
	 * Получение данных раздела "Региональная льгота" формы просмотра записи регистра
	 */
	function getPersonPrivilegeRegAllViewData($data)
	{
		if (empty($data['Person_id'])) {
			throw new Exception('Не передан идентификатор человека');
		}
		$params = array(
			'Person_id'=>$data['Person_id'],
		);
		$query = "
			SELECT 
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PT.PrivilegeType_Code as \"PrivilegeType_Code\",
				to_char(PP.PersonPrivilege_begDate, 'dd.mm.yyyy') as \"PersonPrivilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, 'dd.mm.yyyy') as \"PersonPrivilege_endDate\",
				v_YesNo.YesNo_Name as \"PersonRefuse_IsRefuse_Name\"
			FROM
				v_PersonPrivilege PP
				inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id and PT.ReceptFinance_id = 2
				left join lateral (
					select PR.PersonRefuse_IsRefuse from PersonRefuse PR
					where PR.Person_id = PP.Person_id and PR.PersonRefuse_Year = date_part('year', dbo.tzGetDate())
					order by PR.PersonRefuse_IsRefuse desc
					limit 1
				) refuse on true
				left join v_YesNo on v_YesNo.YesNo_id = coalesce(refuse.PersonRefuse_IsRefuse, 1)
			WHERE
				PP.Person_id = :Person_id
			ORDER BY 
				PP.PersonPrivilege_begDate ASC
		";
		//throw new Exception(getDebugSQL($query, $params));
		$result = $this->db->query($query, $params);
		if (false == is_object($result)) {
			throw new Exception('Ошибка БД');
		}
		return $result->result('array');
	}

	/**
	 * Получение данных раздела "Федеральная льгота" формы просмотра записи регистра
	 */
	function getPersonPrivilegeFedAllViewData($data)
	{
		if (empty($data['Person_id'])) {
			throw new Exception('Не передан идентификатор человека');
		}
		$params = array(
			'Person_id'=>$data['Person_id'],
		);
		$query = "
			SELECT 
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PT.PrivilegeType_Code as \"PrivilegeType_Code\",
				to_char(PP.PersonPrivilege_begDate, 'dd.mm.yyyy') as \"PersonPrivilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, 'dd.mm.yyyy') as \"PersonPrivilege_endDate\",
				v_YesNo.YesNo_Name as \"PersonRefuse_IsRefuse_Name\"
			FROM
				v_PersonPrivilege PP
				inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id and PT.ReceptFinance_id = 1
				left join lateral (
					select PR.PersonRefuse_IsRefuse from PersonRefuse PR
					where PR.Person_id = PP.Person_id and PR.PersonRefuse_Year = date_part('year', dbo.tzGetDate())
					order by PR.PersonRefuse_IsRefuse desc
					limit 1
				) refuse on true
				left join v_YesNo on v_YesNo.YesNo_id = coalesce(refuse.PersonRefuse_IsRefuse, 1)
			WHERE
				PP.Person_id = :Person_id
			ORDER BY 
				PP.PersonPrivilege_begDate ASC
		";
		//throw new Exception(getDebugSQL($query, $params));
		$result = $this->db->query($query, $params);
		if (false == is_object($result)) {
			throw new Exception('Ошибка БД');
		}
		return $result->result('array');
	}

	/**
	 * Получение данных раздела "Лекарственные препараты" формы просмотра записи регистра
	 */
	function getPersonDrugViewData($data) {
		try {
			if (empty($data['PersonRegister_id'])) {
				throw new Exception('Не передан идентификатор записи регистра');
			}
			if (empty($data['PersonRegisterType_SysNick'])) {
				throw new Exception('Не передан тип записи регистра');
			}
			$queryParams = array(
				'PersonRegister_id' => $data['PersonRegister_id'],
				'PersonRegisterType_SysNick' => $data['PersonRegisterType_SysNick'],
			);
			$join = '';
			switch ($data['PersonRegisterType_SysNick']) {
				case 'nolos':
					// нужно рецепты искать по нозологии
					$diag_filter = '(PRD.MorbusType_id = PR.MorbusType_id OR PRD.Diag_id = PR.Diag_id)';
					break;
				case 'orphan':
					$join = 'inner join v_Diag on v_Diag.Diag_id = PR.Diag_id';
					$diag_filter = "(
						(v_Diag.Diag_Code not in ('E70.0','E70.1') AND PRD.Diag_id = PR.Diag_id)
						OR (v_Diag.Diag_Code in ('E70.0','E70.1') AND exists (
							select * from v_Diag ND
							where ND.Diag_id = PRD.Diag_id and ND.Diag_Code in ('E70.0','E70.1')
						))
					)";
					break;
				default:
					// По умолчанию ищем по диагнозу указанному в регистре
					$diag_filter = 'PRD.Diag_id = PR.Diag_id';
					break;
			}
			$query = "
				SELECT
					PRD.Diag_id as \"Diag_id\",
					PR.Person_id as \"Person_id\",
					PR.PersonRegisterType_id as \"PersonRegisterType_id\"
				FROM
					v_PersonRegister PR
					inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
						AND PRT.PersonRegisterType_SysNick LIKE :PersonRegisterType_SysNick
					{$join}
					inner join v_PersonRegisterDiag PRD on PRD.PersonRegisterType_id = PR.PersonRegisterType_id
						AND {$diag_filter}
				WHERE
					PR.PersonRegister_id = :PersonRegister_id
					AND PR.Diag_id is not null
			";
			// echo getDebugSQL($query, $queryParams); exit();
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			$tmp = $result->result('array');
			$diag_list = array();
			foreach ($tmp as $row) {
				$diag_list[] = $row['Diag_id'];
				$data['Person_id'] = $row['Person_id'];
			}
			if (empty($diag_list)) {
				throw new Exception('Ошибка получения данных по записи регистра');
			}
			if (empty($data['Person_id'])) {
				throw new Exception('Не передан идентификатор человека');
			}

			$query = "
				SELECT
					'view' as \"accessType\",
					EvnRecept.Entity || '_' || coalesce(EvnRecept.EvnRecept_id,EvnRecept.ReceptOtov_id) as \"PersonDrug_id\",
					Result.ReceptResult_Name as \"ReceptResult_Name\" -- Статус,
					rtrim(EvnRecept.EvnRecept_Ser) as \"EvnRecept_Ser\" -- Серия,
					rtrim(EvnRecept.EvnRecept_Num) as \"EvnRecept_Num\" -- Номер,
					rtrim(coalesce(DrugMnn.DrugMnn_Name, dcm.DrugComplexMnn_RusName)) as \"DrugMnn_Name\" -- МНН,
					rtrim(coalesce(RDR.Drug_Name, Drug.Drug_Name)) as \"DrugTorg_Name\" -- Торговое наименование,
					ROUND(EvnRecept.EvnRecept_Kolvo, 3) as \"EvnRecept_Kolvo\" -- Количество,
					to_char(EvnRecept.EvnRecept_setDate, 'dd.mm.yyyy') as \"EvnRecept_setDate\" -- Дата выписки,
					to_char(EvnRecept.EvnRecept_otpDate, 'dd.mm.yyyy') as \"EvnRecept_otpDate\" -- Дата отоваривания
				FROM
					(
						select
							E.EvnRecept_id
							,null as ReceptOtov_id
							,'EvnRecept' as Entity
							,E.Diag_id
							,E.Person_id
							,E.EvnRecept_Ser
							,E.EvnRecept_Num
							,E.EvnRecept_Kolvo
							,E.EvnRecept_setDate as EvnRecept_setDate
							,E.EvnRecept_otpDT as EvnRecept_otpDate
							,coalesce(E.Drug_oid,E.Drug_id) as Drug_id
							,E.DrugComplexMnn_id
							,E.Drug_rlsid
							,E.EvnRecept_obrDT as EvnRecept_obrDate
							,E.ReceptValid_id
							,E.ReceptDelayType_id
							,E.EvnRecept_deleted
						from v_EvnRecept_all E
						union all
						select
							EvnRecept_id
							,ReceptOtov_id
							,'ReceptOtov' as Entity
							,Diag_id
							,Person_id
							,EvnRecept_Ser
							,EvnRecept_Num
							,EvnRecept_Kolvo
							,EvnRecept_setDate as EvnRecept_setDate
							,EvnRecept_otpDT as EvnRecept_otpDate
							,Drug_id
							,DrugComplexMnn_id
							,Drug_cid as Drug_rlsid
							,EvnRecept_obrDT as EvnRecept_obrDate
							,ReceptValid_id
							,ReceptDelayType_id
							,null as EvnRecept_deleted
						from v_ReceptOtovUnSub
						where EvnRecept_id is null
					) EvnRecept
					LEFT JOIN v_Drug Drug on Drug.Drug_id = EvnRecept.Drug_id
					left join rls.v_Drug RDR on RDR.Drug_id = EvnRecept.Drug_rlsid
					left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(EvnRecept.DrugComplexMnn_id,RDR.DrugComplexMnn_id)
					LEFT JOIN DrugMnn on DrugMnn.DrugMnn_id = Drug.DrugMnn_id
					LEFT JOIN v_ReceptResult Result on (
						case
							when coalesce(EvnRecept.EvnRecept_deleted,1) = 2
							then 12 -- Удалённый МО

							when EvnRecept.ReceptDelayType_id = 3
							then 11 -- Отказ

							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and
								dbo.tzGetDate() <= (case when EvnRecept.ReceptValid_id = 1 then dateadd(day, 14, EvnRecept.EvnRecept_setDate) else dateadd(month, case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
							then 7 -- Рецепт отсрочен - имеется дата обращения, нет даты отоваривания, рецепт не просрочен и нет отказа

							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and
								dbo.tzGetDate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd(day, 14, EvnRecept.EvnRecept_setDate) else dateadd(month, case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
							then 8 -- Рецепт просрочен - имеется дата обращения, нет даты отоваривания, нет отказа и Текущая дата > Даты выписки + Срок действия рецепта

							when EvnRecept.EvnRecept_obrDate is null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and
								dbo.tzGetDate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd(day, 14, EvnRecept.EvnRecept_setDate) else dateadd(month, case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
							then 9 -- Рецепт просрочен без обращения - нет даты обращения и даты отоваривания, нет отказа и Текущая дата > Даты выписки + Срок действия рецепта

							when EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate is null and EvnRecept.ReceptDelayType_id != 3 and
								dbo.tzGetDate() >= (case when EvnRecept.ReceptValid_id = 1 then dateadd(day, 14, EvnRecept.EvnRecept_setDate) else dateadd(month, case when EvnRecept.ReceptValid_id = 2 then 1 else 3 end, EvnRecept.EvnRecept_setDate) end)
							then 10 -- Рецепт просрочен после отсрочки - нет даты отоваривания, есть дата обращения и нет отказа и Текущая дата > Даты выписки + Срок действия рецепта.

							when EvnRecept.EvnRecept_otpDate is not null and EvnRecept.EvnRecept_obrDate is not null and EvnRecept.EvnRecept_otpDate > EvnRecept.EvnRecept_obrDate
							then 6 -- Рецепт отоварен после отсрочки - имеются даты отоваривания и обращения и дата отоваривания > даты обращения

							when EvnRecept.EvnRecept_otpDate is not null and (EvnRecept.EvnRecept_obrDate is null or EvnRecept.EvnRecept_obrDate = EvnRecept.EvnRecept_otpDate)
							then 5 -- Рецепт отоварен без отсрочки - (имеются даты отоваривания и обращения и дата отоваривания = дате обращения) или (имеется дата отоваривания и нет даты обращения)

							when EvnRecept.EvnRecept_otpDate is not null
							then 4 -- Рецепт отоварен

							when EvnRecept.EvnRecept_otpDate is null and EvnRecept.EvnRecept_obrDate is not null
							then 3 -- Рецепт не отоварен

							when EvnRecept.EvnRecept_obrDate is not null
							then 1 -- Было обращение

							when EvnRecept.EvnRecept_obrDate is null
							then 2 -- Не было обращения

						end
					) = Result.ReceptResult_id
				WHERE
					EvnRecept.Person_id = :Person_id AND EvnRecept.Diag_id in (" . implode(', ', $diag_list) . ")
				ORDER BY
					EvnRecept.EvnRecept_setDate DESC
			";
			// echo getDebugSQL($query, $data); exit();
			$result = $this->db->query($query, $data);
			if (!is_object($result)) {
				throw new Exception('Ошибка БД');
			}
			return $result->result('array');
		} catch (Exception $e) {
			//$e->getMessage()
			return false;
		}
	}

	/**
	 * Получение списка пользователей с группой «Регистр по ...»
	 * @param array $data
	 * @return array
	 * @throws Exception
	 */
	function loadUsers($data)
	{
		$limit = '';
		$filter = '';
		$params = array();
		if (isset($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= ' and Lpu_id = :Lpu_id ';
		}
		if (isset($data['limit']) && is_int($data['limit']) && $data['limit'] > 0) {
			$limit = ' limit ' . $data['limit'];
		}
		$query = "
			select PMUser_id as \"PMUser_id\"
			from v_pmUserCache
			where pmUser_groups like '%{$this->userGroupCode}%'
			{$filter}
			{$limit}
		";
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else	{
			throw new Exception('Ошибка запроса списка пользователей регистра');
		}
	}
}