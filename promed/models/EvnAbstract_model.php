<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
 
/**
 * EvnAbstract_model - Модель абстрактного события
 *
 * Это попытка применить ООП к реализации бизнес-логики событий
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс Evn
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property int $pid Родительное событие
 * @property int $rid Корневое событие
 * @property DateTime $setDT Дата и время начала события
 * @property string $setDate Дата начала события в формате Y-m-d
 * @property string $setTime Время начала события в формате H:i
 * @property DateTime $disDT Дата и время окончания события
 * @property DateTime $didDT Дата и время выполнения
 * @property int $IsSigned Подписано
 * @property int $pmUser_signID Кто подписал
 * @property DateTime $signDT Когда подписал
 * @property int $Lpu_id Идентификатор МО, в котором создано событие
 * @property int $Person_id Идентификатор человека
 * @property int $PersonEvn_id Идентификатор состояния человека
 * @property int $Server_id Источник данных
 * @property int $Morbus_id Идентификатор заболевания
 * @property int $EvnStatus_id
 * @property DateTime $statusDate
 * @property-read int $Count
 * @property-read int $Index
 * @property-read int $IsArchive Признак архивной записи
 *
 * @property-read array $personData Ассоциативный массив с полями человека
 * @property-read DateTime $person_BirthDay
 * @property-read int $person_Age
 * @property-read int $person_Sex_id
 * @property-read int $person_OmsSprTerr_Code
 * @property-read int $evnClassId
 * @property-read string $evnClassSysNick
 * @property-read string $payTypeSysNickOMS
 *
 * @property EvnAbstract_model $parent
 * @property-read int $parentEvnClassId
 * @property-read string $parentEvnClassSysNick
 * @property-read PersonNoticeEvn $personNoticeEvn
 *
 * @property-read Evn_model $Evn_model
 * @property-read Options_model $Options_model
 * @property-read TimetableGraf_model $TimetableGraf_model
 */
abstract class EvnAbstract_model extends swModel
{
	/**
	 * @var EvnAbstract_model Родительский объект, модель которого, потомок EvnAbstract_model
	 */
	private $_parent = null;
	private $_parentEvnClassId = null;
	private $_parentEvnClassSysNick = null;
	protected $_parentClass = null;
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;
	/**
	 * Ассоциативный массив с полями человека
	 * @var array
	 */
	protected $_personData = array();

	private $_personNoticeEvn = null;

	/**
	 * Признак для отключения проверки МО (используется в PersonRegistrerNolos_model)
	 * @var bool 
	 */
	public $disableLpuIdChecks = false;

	/**
	 * Сброс данных объекта
	 */
	function reset()
	{
		parent::reset();
		$this->_parent = null;
		$this->_parentEvnClassId = null;
		$this->_parentEvnClassSysNick = null;
		$this->_personNoticeEvn = null;
		$this->_personData = array();
	}

	/**
	 * @return PersonNoticeEvn
	 * @throws Exception
	 */
	function getPersonNoticeEvn()
	{
		if (empty($this->_personNoticeEvn)) {
			if (empty($this->Person_id)) {
				throw new Exception('Для инициализации хелпера рассылки сообщений нужен идентификатор пациента');
			}
			$this->load->helper('PersonNotice');
			$this->_personNoticeEvn = new PersonNoticeEvn($this->Person_id);
			if (false === $this->_personNoticeEvn->loadPersonInfo($this->PersonEvn_id, $this->Server_id)) {
				throw new Exception('При инициализации хелпера рассылки сообщений не удалось загрузить информацию о человеке');
			}
		}
		return $this->_personNoticeEvn;
	}
	/**
	 * @param EvnAbstract_model $object
	 * @throws Exception
	 */
	function setParent(EvnAbstract_model $object)
	{
		if (empty($this->_parentClass)) {
			throw new Exception('Не определен класс родительского события...', 500);
		}
		if ($object instanceof $this->_parentClass) {
			$this->_parent = $object;
		} else {
			throw new Exception('Неправильный объект родительского события', 500);
		}
		if (!empty($object->id)) {
			$this->setAttribute('pid', $object->id);
		}
	}
	/**
	 * @return EvnAbstract_model
	 * @throws Exception
	 */
	function getParent()
	{
		if (empty($this->_parentClass)) {
			throw new Exception('Не определен класс родительского события!', 500);
		}
		if (empty($this->_parent)) {
			if (empty($this->pid)) {
				throw new Exception('Не определен идентификатор родительского события!', 500);
			}
			$pidVar = $this->_parentClass . '_pidModel';
			$this->load->model($this->_parentClass, $pidVar);
			$this->$pidVar->setAttributes(array($this->$pidVar->primaryKey(true) => $this->pid));
			$this->$pidVar->setParams($this->_params);
			$this->_parent = $this->$pidVar;
		}
		return $this->_parent;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	function getParentEvnClassSysNick()
	{
		if (isset($this->_parent)) {
			$this->_parentEvnClassSysNick = $this->_parent->evnClassSysNick;
			$this->_parentEvnClassId = $this->_parent->evnClassId;
		}
		if (empty($this->pid)) {
			$this->_parentEvnClassSysNick = null;
			$this->_parentEvnClassId = null;
		} else if (!isset($this->_parentEvnClassSysNick)) {
			$tmp = $this->getFirstRowFromQuery('
			SELECT EvnClass_id, EvnClass_SysNick FROM v_Evn with (nolock)
			WHERE Evn_id = :id', array('id' => $this->pid));
			if (empty($tmp) || !is_array($tmp)) {
				throw new Exception('Не удалось определить вид родительского учетного документа');
			}
			$this->_parentEvnClassId = $tmp['EvnClass_id'];
			$this->_parentEvnClassSysNick = $tmp['EvnClass_SysNick'];
		}
		return $this->_parentEvnClassSysNick;
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getParentEvnClassId()
	{
		if ($this->parentEvnClassSysNick) {
			return $this->_parentEvnClassId;
		} else {
			return null;
		}
	}

	/**
	 * Определение идентификатора класса события
	 * Метод должен быть определен в каждой модели, наследующей этот класс.
	 * @return int
	 */
	static function evnClassId()
	{
		return 1;
	}

	/**
	 * Определение кода класса события
	 * Метод должен быть определен в каждой модели, наследующей этот класс.
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'Evn';
	}

	/**
	 * Метод получения значения волшебного свойства объекта
	 * @return string
	 */
	public function getEvnClassSysNick()
	{
		return call_user_func(array(get_class($this),'evnClassSysNick'));
	}

	/**
	 * Метод получения значения волшебного свойства объекта
	 * @return string
	 */
	public function getEvnClassId()
	{
		return call_user_func(array(get_class($this),'evnClassId'));
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return $this->evnClassSysNick;
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => '_id',//указать в наследниках
				'label' => 'Идентификатор события',
				'save' => 'trim',
				'type' => 'id'
			),
			'pid' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => '_pid',//указать в наследниках
				'label' => 'Идентификатор родительного события',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'rid' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'setdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
				),
				'applyMethod'=>'_applySetDT',
				'dateKey'=>'setdate',
				'timeKey'=>'settime',
			),
			'setdate' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD,
				),
				// только для извлечения из POST и обработки методом _applySetDT
				'alias' => '_setDate',//указать в наследниках
				'label' => 'Дата начала события',
				'save' => 'trim|required',
				'type' => 'date'
			),
			'settime' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_LOAD,
				),
				// только для извлечения из POST и обработки методом _applySetDT
				'alias' => '_setTime',//указать в наследниках
				'label' => 'Время начала события',
				'save' => 'trim',
				'type' => 'time'
			),
			'disdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NOT_SAFE, // или определить свой applyMethod по аналогии с _applySetDT
				),
			),
			'diddt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_NOT_SAFE, // или определить свой applyMethod по аналогии с _applySetDT
				),
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
			'issigned' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'istransit' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'signdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'pmuser_signid' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'lpu_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					//@todo при создании события брать из объекта сессии,
					//оно берется из параметров сессии и его нельзя брать из POST
					//при сохранении т.к. оно может быть перезатерто!!!
					//self::PROPERTY_NOT_SAFE,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'Lpu_id',
			),
			'person_id' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
				),
				'alias' => 'Person_id',
				'label' => 'Идентификатор человека',
				'save' => 'required',
				'type' => 'id'
			),
			'personevn_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'save' => 'required',
				'type' => 'id'
			),
			'server_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'Server_id',
				'label' => 'Источник данных',
				'save' => 'required',
				'type' => 'int'
			),
			'morbus_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'Morbus_id',
			),
			'evnstatus_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => 'EvnStatus_id',
			),
			'statusdate' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_SAFE,
				),
				'alias' => '_statusDate',
			),
			'count' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					//self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'index' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					//self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'isarchive' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					//self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
		);
	}

    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Извлечение значений параметров модели из входящих параметров,
	 * переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		if (self::SCENARIO_DELETE == $this->scenario) {
			// параметры, которые используются при удалении
			// были ли выполнены общие проверки в контроллере Evn::doCommonChecksOnDelete
			$this->_params['isExecCommonChecksOnDelete'] = isset($data['isExecCommonChecksOnDelete']) ? (bool) $data['isExecCommonChecksOnDelete'] : false ;
			// игнорировать ли проверку файлов/документов прикрепленных к учетному документу
			$this->_params['isAllowIgnoreDoc'] = isset($data['isAllowIgnoreDoc']) ? (bool) $data['isAllowIgnoreDoc'] : false ;
			// при удалении из АРМа врача этот параметр указан
			$this->_params['user_MedStaffFact_id'] = isset($data['user_MedStaffFact_id']) ? $data['user_MedStaffFact_id'] : null ;
		}
		if (self::SCENARIO_SET_ATTRIBUTE) {
			// указание был ли вызов из ЭМК или нет
			$this->_params['isEmk'] = isset($data['isEmk']) ? $data['isEmk'] : false ;
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(
			self::SCENARIO_DELETE,
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE
		))) {
			if(
				!empty($_POST['PersonRegisterType_SysNick']) 
				&& in_array($_POST['PersonRegisterType_SysNick'],array('orphan','nolos'))
				&& empty($this->sessionParams['lpu_id'])
				&& !empty($this->Lpu_id)
			) {
				$_SESSION['lpu_id'] = $this->Lpu_id;
				$this->setSessionParams($_SESSION);
			}
			if ( empty($this->sessionParams['lpu_id']) && empty($this->disableLpuIdChecks) ) {
				throw new Exception('Не указан идентификатор МО пользователя');
			}
			if ( $this->isNewRecord) {
				$this->setAttribute('lpu_id', $this->sessionParams['lpu_id']);
			}
			if (
				!(array_key_exists('linkedLpuIdList', $this->sessionParams) && in_array($this->Lpu_id, $this->sessionParams['linkedLpuIdList']))
				&& $this->sessionParams['lpu_id'] != $this->Lpu_id
				&& false == $this->isNewRecord
				&& !isSuperAdmin()
				&& !isLpuAdmin($this->Lpu_id)
				&& empty($this->disableLpuIdChecks)
			) {
				throw new Exception('Вы не можете изменить объект, созданный в другой МО ');
			}
		}
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if ( !isset($this->Server_id) || $this->Server_id < 0 ) {
				throw new Exception('Не указан источник данных');
			}
			if ( empty($this->PersonEvn_id) || $this->PersonEvn_id < 0 ) {
				throw new Exception('Не указан человек');
			}
		}
		if (self::SCENARIO_DELETE == $this->scenario) {
			if (false == $this->_params['isExecCommonChecksOnDelete']) {
				// TODO реализовать тут общие проверки как в контроллере Evn::doCommonChecksOnDelete и в Evn_model::onBeforeDelete

				// $this->_params['isExecCommonChecksOnDelete'] = true;
			}
			if (in_array($this->evnClassId, array(3,6,30,11,13,32)) && false == $this->_params['isExecCommonChecksOnDelete']) {
				throw new Exception('Не были выполнены общие проверки перед удалением учетного документа');
			}
			if ( in_array($this->evnClassId, array(3,6,30,11,13,32)) ) {
				// Удалять случай/движение/посещение, из которых были выписаны направления, надо запретить
				$parent_field = 'EvnDirection_pid';
				if ( in_array($this->evnClassId, array(3,6,30)) ) {
					$parent_field = 'EvnDirection_rid';
				}

				// документ не содержит направления или направление/запись  имеет статус отменено, отклонено, удалено
				$check = $this->getFirstResultFromQuery("
					select top 1 ED.EvnDirection_id
					from v_EvnDirection_all ED with (nolock)
					LEFT JOIN v_EvnStatus ES ON ED.EvnClass_id = ES.EvnClass_id AND ED.EvnStatus_id = ES.EvnStatus_id
					WHERE ED.{$parent_field} = :{$parent_field}
					AND ES.EvnStatus_SysNick NOT IN('Canceled', 'Declined');
					
				", array($parent_field => $this->id));
				if (!empty($check)) {
					throw new Exception('Удаление невозможно. Случай содержит направления или записи');
				}
			}
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
		$this->_processingDtValue($column, $value, 'set');
		return parent::_processingSavedValue($column, $value);
	}

	/**
	 * Извлечение даты и времени события из входящих параметров
	 *
	 * Устанавливаем значение атрибута, если оно пришло (есть нужные ключи в массиве)
	 * @param array $data
	 * @return bool
	 */
	protected function _applySetDT($data)
	{
		return $this->_applyDT($data, 'set');
	}

	/**
	 * Извлечение даты и времени события из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyDisDT($data)
	{
		return $this->_applyDT($data, 'dis');
	}

	/**
	 * Получение данных по заданному человеку, прикрепленному к заданному серверу
	 * @param bool $isByPersonEvn Если передали $isByPersonEvn, значит определенная периодика нужна и читать будем из периодики
	 * @param array $params
	 * @return array|bool Ассоциативный массив с полями человека
	 * @throws Exception
	 */
	function loadPersonData($isByPersonEvn = false, $params = array())
	{
		if ($isByPersonEvn) {
			$object = "v_Person_bdz";
			if (!isset($params['Server_id'])) {
				$params['Server_id'] = $this->Server_id;
			}
			if (empty($params['PersonEvn_id'])) {
				$params['PersonEvn_id'] = $this->PersonEvn_id;
			}
			$filter = "PS.PersonEvn_id = :PersonEvn_id and PS.Server_id = :Server_id";
			if (!isset($params['Server_id'])) {
				return false;
			}
			if (empty($params['PersonEvn_id'])) {
				return false;
			}
		} else {
			$object = "v_PersonState";
			if (empty($params['Person_id'])) {
				$params['Person_id'] = $this->Person_id;
			}
			$filter = "PS.Person_id = :Person_id";
			if (empty($params['Person_id'])) {
				return false;
			}
		}
		return $this->getFirstRowFromQuery("
			SELECT TOP 1
				PS.Person_id,
				PS.PersonEvn_id,
				PS.Server_id,
				PS.Person_BirthDay,
				Sex.Sex_Name,
				Sex.Sex_Code,
				Sex.Sex_id,
				Person.Person_deadDT,
				Person.Person_closeDT,
				Person.Person_IsDead,
				Person.Person_IsAnonym,
				Person.PersonCloseCause_id,
				OmsSprTerr.OmsSprTerr_id,
				isnull(OmsSprTerr.OmsSprTerr_Code, 0) as OmsSprTerr_Code
			FROM {$object} PS WITH (NOLOCK)
				left join v_Sex Sex WITH (NOLOCK) on Sex.Sex_id = PS.Sex_id
				left join [Person] WITH (NOLOCK) on [Person].[Person_id] = PS.Person_id
				left join [Polis] WITH (NOLOCK) on [Polis].[Polis_id] = [PS].[Polis_id]
				left join [OmsSprTerr] WITH (NOLOCK) on [OmsSprTerr].[OmsSprTerr_id] = [Polis].[OmsSprTerr_id]
			WHERE {$filter}
		", $params);
	}

	/**
	 * Получение данных по заданному человеку
	 * @return array Ассоциативный массив с полями человека
	 * @throws Exception
	 */
	function getPersonData()
	{
		if (!empty($this->_personData)) {
			return $this->_personData;
		}
		$id = $this->getAttribute('person_id');
		$this->_personData = $this->loadPersonData(empty($id));
		if (empty($this->_personData)) {
			throw new Exception('Не удалось получить данные человека');
		}
		return $this->_personData;
	}

	/**
	 * @return int
	 */
	function getPerson_OmsSprTerr_Code()
	{
		return $this->personData['OmsSprTerr_Code'] + 0;
	}

	/**
	 * Возвращает дату рождения человека
	 * @return DateTime
	 */
	function getPerson_BirthDay()
	{
		return $this->personData['Person_BirthDay'];
	}

	/**
	 * Возвращает возраст человека в годах
	 * @param DateTime $birthDay Дата рождения, по умолчанию дата полученная методом EvnAbstract_model::getPerson_BirthDay
	 * @param DateTime $curDate Дата и время события, по умолчанию текущая дата и время полученная методом swModel::getCurrentDT
	 * @return int
	 * @comment Функция getCurrentAge из Date_helper возвращает неправильный результат
	 * для примеров getCurrentAge('2014-04-01', '2015-04-01') getCurrentAge('2015-01-01', '2015-01-01')
	 * Реализовано почти также как расчитывается возраст в [dbo].[Age2]
	 */
	function getPerson_Age(DateTime $birthDay = null, DateTime $curDate = null)
	{
		if (empty($birthDay) && $this->person_BirthDay instanceof DateTime) {
			$birthDay = $this->person_BirthDay;
		}
		if (empty($birthDay)) {
			return 0;
		}
		if (empty($curDate) && $this->setDT instanceof DateTime) {
			$curDate = $this->setDT;
		}
		if (empty($curDate)) {
			$curDate = $this->currentDT;
		}
		// должны быть в одинаковой временной зоне для правильности расчета
		$curDate->setTimezone($birthDay->getTimezone());
		$result = $curDate->diff($birthDay)->y;
		/* не совсем понятно зачем в [dbo].[Age2] идет сравнение @newdate > @curdate
		Поэтому пока убрал
		$newDt = new DateTime($birthDay->format('Y-m-d'), $birthDay->getTimezone());
		$newDt->add(new DateInterval("P{$result}Y"));
		if ( $newDt > $curDate ) {
			$result--;
		}
		*/
		return $result;
	}

	/**
	 * Возвращает пол человека
	 * @return int
	 */
	function getPerson_Sex_id()
	{
		return $this->personData['Sex_id'];
	}

	/**
	 * Возвращает id человека
	 * @return int
	 */
	function getPerson_id()
	{
		$id = $this->getAttribute('person_id');
		if ($id) {
			return $id;
		}
		if ($this->PersonEvn_id) {
			return $this->personData['Person_id'];
		}
		return null;
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		if ($this->_isAttributeChanged('diag_id')
			|| $this->_isAttributeChanged('person_id')
			|| $this->_isAttributeChanged('setdate')
		) {
			// в последнюю очередь обновляем заболевание
			$this->_updateMorbus();
		}

		if (!empty($this->pid)) {
			// сбрасываем актуальность подписи родительского документа
			$this->db->query("
				update Evn with (rowlock) set Evn_IsSigned = 1 where Evn_IsSigned = 2 and Evn_id = :Evn_id and EvnClass_id in (3, 6, 30)
			", array(
				'Evn_id' => $this->pid
			));
		}
		
		if (in_array($this->regionNick, ['perm', 'msk']) && $this->_isAttributeChanged('diag_spid')) {
			$this->load->model('MorbusOnkoSpecifics_model');
			$this->MorbusOnkoSpecifics_model->checkAndCreateSpecifics($this);
		}
	}

	/**
	 * Проверки и другая логика после удаления объекта
	 */
	protected function _afterDelete($result)
	{
		parent::_afterDelete($result);

		if (!empty($this->pid)) {
			// сбрасываем актуальность подписи родительского документа
			$this->db->query("
				update Evn with (rowlock) set Evn_IsSigned = 1 where Evn_IsSigned = 2 and Evn_id = :Evn_id and EvnClass_id in (3, 6, 30)
			", array(
				'Evn_id' => $this->pid
			));
		}
	}

	/**
	 * @throws Exception
	 */
	protected function _updateMorbus()
	{
		$this->load->library('swMorbus');
		$tmp = swMorbus::onAfterSaveEvn($this);
		$this->_saveResponse = array_merge($this->_saveResponse, $tmp);
		$this->load->library('swPersonRegister');
		$tmp = swPersonRegister::onAfterSaveEvn($this);
		$this->_saveResponse = array_merge($this->_saveResponse, $tmp);
	}

	/**
	 * Проверки и другая логика перед удалением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeDelete($data = array())
	{
		parent::_beforeDelete($data);
		// все проверки уже выполнены в методе _validate
		// удаляем дочерние объекты, которые не являются EvnClass-ами
		$this->load->library('swMorbus');
		swMorbus::onBeforeDeleteEvn($this);
		$this->load->library('swPersonRegister');
		swPersonRegister::onBeforeDeleteEvn($this);
	}

	/**
	 * Получение системного наименования для вида оплаты "ОМС"
	 * @task https://redmine.swan.perm.ru/issues/39841
	 */
	function getPayTypeSysNickOMS()
	{
		return getPayTypeSysNickOMS();
	}

	/**
	 * Получение вида оплаты по системному наименованию
	 */
	function loadPayTypeIdBySysNick($sysNick)
	{
		$res = $this->getFirstResultFromQuery('
			select top 1 PayType_id
			from v_PayType (nolock)
			where PayType_SysNick like :PayType_SysNick
		', array('PayType_SysNick'=>$sysNick));
		if (empty($res)) {
			$res = null;
		}
		return $res;
	}

	/**
	 * Операция отмены текущего статуса и записи предыдущего статуса
	 *
	 * @param array $params Обязательные ключи: EvnStatus_id или EvnStatus_SysNick текущего статуса
	 * При вызове в статическом контексте обязательно надо передать Evn_id, EvnClass_id, pmUser_id
	 * @throws Exception
	 */
	function rollbackStatus($params)
	{
		if (empty($params['EvnStatus_id']) && empty($params['EvnStatus_SysNick'])) {
			throw new Exception('Нужно указать текущий статус', 500);
		}
		if (empty($params['Evn_id'])) {
			$params['Evn_id'] = $this->id;
		}
		if (empty($params['Evn_id'])) {
			throw new Exception('Нужно указать учетный документ', 500);
		}
		if (empty($params['EvnClass_id'])) {
			$params['EvnClass_id'] = $this->evnClassId;
		}
		if (empty($params['EvnClass_id'])) {
			throw new Exception('Нужно указать класс учетного документа', 500);
		}
		if (empty($params['pmUser_id'])) {
			$params['pmUser_id'] = $this->promedUserId;
		}
		if (empty($params['pmUser_id'])) {
			throw new Exception('Нужно указать учетную запись', 500);
		}
		$join_current_status = 'inner join v_EvnStatus CurrentStatus (nolock) on CurrentStatus.EvnStatus_id = :EvnStatus_id';
		if (empty($params['EvnStatus_id']) && isset($params['EvnStatus_SysNick'])) {
			$join_current_status = 'inner join v_EvnStatus CurrentStatus (nolock) on CurrentStatus.EvnStatus_SysNick like :EvnStatus_SysNick';
		}
		$query = "
			select top 1 EvnStatusHistory.EvnStatus_id, CurrentStatus.EvnStatus_id as CurrentStatus_id
			from EvnStatusHistory (nolock)
			{$join_current_status}
			inner join Evn (nolock) on Evn.Evn_id = EvnStatusHistory.Evn_id and Evn.EvnStatus_id = CurrentStatus.EvnStatus_id and Evn.EvnClass_id = :EvnClass_id
			where EvnStatusHistory.Evn_id = :Evn_id and EvnStatusHistory.EvnStatusHistory_begDate < Evn.Evn_statusDate
			order by EvnStatusHistory_begDate desc
		";
		$tmp = $this->getFirstRowFromQuery($query, $params);
		if (empty($tmp) || empty($tmp['EvnStatus_id'])) {
			//.getDebugSQL($query, $params)
			//throw new Exception('Не удалось определить предыдущий статус', 500);
			/*
			 * фактический текущий статус не равен статусу, который надо откатить (CurrentStatus_id != $params['EvnStatus_id'])
			 * такое возможно при попытке откатить статус учетных документов, которые были измененны до внедрения статусов (например, ЭН) - в этом случае ничего не делаем
			 */
		} else if ($tmp['EvnStatus_id'] != $tmp['CurrentStatus_id']) {
			$this->setStatus(array(
				'Evn_id' => $params['Evn_id'],
				'EvnStatus_id' => $tmp['EvnStatus_id'],
				'EvnClass_id' => $params['EvnClass_id'],
				'pmUser_id' => $params['pmUser_id'],
			));
		}
	}

	/**
	 * Операция записи статуса
	 *
	 * @param array $params Обязательные ключи: EvnStatus_id или EvnStatus_SysNick
	 * При вызове в статическом контексте обязательно надо передать Evn_id, EvnClass_id, pmUser_id
	 * Для некоторых статусов обязательно надо передать EvnStatusCause_id, EvnStatusHistory_Cause
	 * @throws Exception
	 */
	function setStatus($params)
	{
		if (empty($params['EvnStatus_id']) && empty($params['EvnStatus_SysNick'])) {
			throw new Exception('Нужно указать статус', 500);
		}
		if (empty($params['Evn_id'])) {
			$params['Evn_id'] = $this->id;
		}
		if (empty($params['Evn_id'])) {
			throw new Exception('Нужно указать учетный документ', 500);
		}
		if (empty($params['EvnClass_id'])) {
			$params['EvnClass_id'] = $this->evnClassId;
		}
		if (empty($params['EvnClass_id'])) {
			throw new Exception('Нужно указать класс учетного документа', 500);
		}
		if (empty($params['pmUser_id'])) {
			$params['pmUser_id'] = $this->promedUserId;
		}
		if (empty($params['pmUser_id'])) {
			throw new Exception('Нужно указать учетную запись', 500);
		}
		if (empty($params['EvnStatusCause_id'])) {
			$params['EvnStatusCause_id'] = null;
		}
		if (empty($params['EvnStatusHistory_Cause'])) {
			$params['EvnStatusHistory_Cause'] = null;
		} else if (mb_strlen($params['EvnStatusHistory_Cause']) > 200) {
			throw new Exception('Описание не должно быть более 200 символов', 500);
		}
		$tmp = $this->execCommonSP('p_Evn_setStatus', $params, 'array_assoc');
		if (false == is_array($tmp)) {
			throw new Exception('Не удалось установить статус', 500);
		}
		if (false == empty($tmp['Error_Msg'])) {
			throw new Exception($tmp['Error_Msg'], 500);
		}
	}


	/**
	 * Проверка пересечения сохраняемого движения с посещением
	 */
	function checkIntersectEvnSectionWithVizit($data) {

		if (empty($data)){
			return true;
		}

		if (!empty($data) && (empty($data['EvnSection_setDate']) || empty($data['Person_id']) || empty($data['LpuSection_id']))){
			throw new Exception('Не переданы необходимые параметры для контроля пересечения учётных документов.');
		}

		$data['EvnSection_setDT'] = $data['EvnSection_setDate'] . (!empty($data['EvnSection_setTime']) && preg_match("/^\d{2}\:\d{2}$/", $data['EvnSection_setTime']) ? ' ' . $data['EvnSection_setTime'] : ' 00:00');

		// Добавил учет времени
		// @task https://redmine.swan.perm.ru/issues/62919
		if ( !empty($data['EvnSection_disDate']) ) {
			$data['EvnSection_disDT'] = $data['EvnSection_disDate'] . (!empty($data['EvnSection_disTime']) && preg_match("/^\d{2}\:\d{2}$/", $data['EvnSection_disTime']) ? ' ' . $data['EvnSection_disTime'] : ' 00:00');
		}
		else {
			$data['EvnSection_disDT'] = null;
		}

		$this->load->model('Options_model');
		$vizit_direction_control = $this->Options_model->getOptionsGlobals($data, 'vizit_direction_control');
		$vizit_direction_control = !empty($vizit_direction_control)?$vizit_direction_control:1;
		$control_paytype = $this->Options_model->getOptionsGlobals($data, 'vizit_direction_control_paytype');
		if (!empty($data['EvnSection_disDT'])
			&& ($vizit_direction_control == 3 || ($vizit_direction_control == 2 && (empty($data['vizit_direction_control_check']) || $data['vizit_direction_control_check'] == 0)))
		) {
			$payTypeFilter = $control_paytype ? "and EV.PayType_id = :PayType_id" : "";
			$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
			$diagFilter = !empty($diagFilter) ? "and {$diagFilter}" : '';
			$query = "
				select top 1
					EV.EvnVizitPL_id,
					convert(varchar(10), EV.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate,
					L.Lpu_Nick,
					D.Diag_FullName,
					D.Diag_Code
				from
					v_EvnVizitPL EV (nolock)
					inner join v_LpuSection LS with (nolock) on :LpuSection_id = LS.LpuSection_id
					inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join LpuUnitType LUT with (nolock) on LU.LpuUnitType_id = LUT.LpuUnitType_id
					left join v_Lpu L with (nolock) on L.Lpu_id = EV.Lpu_id
					left join v_Diag D with (nolock) on D.Diag_id = EV.Diag_id
				where
					EV.EvnVizitPL_setDT > :EvnSection_setDT
					and EV.EvnVizitPL_setDT < :EvnSection_disDT
					and EV.Person_id = :Person_id
					and LUT.LpuUnitType_Code = 2
					{$diagFilter}
					{$payTypeFilter}
			";

			//echo getDebugSQL($query, $data);die;
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$checkEvnVizit = $result->result('array');
			} else {
				return false;
			}

			if (is_array($checkEvnVizit) && count($checkEvnVizit) > 0 && !empty($checkEvnVizit[0]['EvnVizitPL_id']) ){
				$diagFullName = checkDiagAccessRights($checkEvnVizit[0]['Diag_Code'])?$checkEvnVizit[0]['Diag_FullName']:'';

				if ($vizit_direction_control == 3){
					//Запрет сохранения
					$msg = 'Данное движение имеет пересечение со случаем поликлинического лечения '.$checkEvnVizit[0]['EvnVizitPL_setDate'] .' / '.$diagFullName.' / '.$checkEvnVizit[0]['Lpu_Nick'].'. Сохранить невозможно.';
					throw new Exception($msg);
				} else if (empty($data['vizit_direction_control_check']) || $data['vizit_direction_control_check'] == 0){
					//предупреждение
					$this->_saveResponse['Alert_Msg'] = 'Данное движение имеет пересечение со случаем поликлинического лечения '.$checkEvnVizit[0]['EvnVizitPL_setDate'] .' / '.$diagFullName.' / '.$checkEvnVizit[0]['Lpu_Nick'].'.';
					throw new Exception('YesNo', 112);
				}
			} else if (is_array($checkEvnVizit) && count($checkEvnVizit) > 0 && !empty($checkEvnVizit[0]['Error_Msg'])){
				throw new Exception($checkEvnVizit[0]['Error_Msg']);
			} else if ($checkEvnVizit === false) {
				throw new Exception('Не удалось проверить пересечение посещения с движением.');
			}
		}
	}

	/**
	 * Проверка пересечения сохраняемого посещения с движением
	 */
	function checkIntersectVizitWithEvnSection($data) {
		/*  перенес проверку в EvnVizitPL_model::_checkChangeSetDate
		$data['EvnVizitPL_setDT'] = $data['EvnVizitPL_setDate'] . (!empty($data['EvnVizitPL_setTime']) && preg_match("/^\d{2}\:\d{2}$/", $data['EvnVizitPL_setTime']) ? ' ' . $data['EvnVizitPL_setTime'] : ' 00:00');

		if ( !empty($data['EvnVizitPL_disDate']) ) {
			$data['EvnVizitPL_disDT'] = $data['EvnVizitPL_disDate'] . (!empty($data['EvnVizitPL_disTime']) && preg_match("/^\d{2}\:\d{2}$/", $data['EvnVizitPL_disTime']) ? ' ' . $data['EvnVizitPL_disTime'] : ' 00:00');
		} else {
			$data['EvnVizitPL_disDT'] = null;
		}

		$data['EvnVizitPL_disDate'] = !empty($data['EvnVizitPL_disDate'])?$data['EvnVizitPL_disDate']:null;

		$this->load->model('Options_model');
		$vizit_kvs_control = $this->Options_model->getOptionsGlobals($data, 'vizit_kvs_control');
		$vizit_kvs_control = !empty($vizit_kvs_control)?$vizit_kvs_control:1;

		if ($vizit_kvs_control == 3 || ($vizit_kvs_control == 2 && (empty($data['vizit_kvs_control_check']) || $data['vizit_kvs_control_check'] == 0))){

			if (empty($data) || empty($data['EvnVizitPL_setDate']) || empty($data['Person_id'])){
				return true;
			}

			$and = " and LUT.LpuUnitType_Code = 2";
			if($this->regionNick == 'kareliya'){
				$and = "";
			}

			$query = "
				select top 1
					ES.EvnSection_id,
					convert(varchar(10), ES.EvnSection_setDate, 104) as EvnSection_setDate,
					convert(varchar(10), ES.EvnSection_disDate, 104) as EvnSection_disDate,
					L.Lpu_Nick,
					D.Diag_FullName,
					D.Diag_Code
				from
					v_EvnSection ES (nolock)
					inner join v_LpuSection LS with (nolock) on ES.LpuSection_id = LS.LpuSection_id
					inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join LpuUnitType LUT with (nolock) on LU.LpuUnitType_id = LUT.LpuUnitType_id
					left join v_Lpu L with (nolock) on L.Lpu_id = ES.Lpu_id
					left join v_Diag D with (nolock) on D.Diag_id = ES.Diag_id
				where
					ES.EvnSection_setDT < :EvnVizitPL_setDT
					and (ES.EvnSection_disDT > :EvnVizitPL_setDT or ES.EvnSection_disDT is null)
					and ES.Person_id = :Person_id
					{$and}
			";

			//echo getDebugSQL($query, $data);die;
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$checkEvnSection = $result->result('array');
			} else {
				return false;
			}

			if (is_array($checkEvnSection) && count($checkEvnSection) > 0 && !empty($checkEvnSection[0]['EvnSection_id']) ){
				$disDate = !empty($checkEvnSection[0]['EvnSection_disDate'])?$checkEvnSection[0]['EvnSection_disDate']:'текущее время';
				$diagFullName = checkDiagAccessRights($checkEvnSection[0]['Diag_Code'])?$checkEvnSection[0]['Diag_FullName']:'';

				if ($vizit_kvs_control == 3){
					//Запрет сохранения
					$msg = 'Данное посещение имеет пересечение со случаем стационарного лечения '.$checkEvnSection[0]['EvnSection_setDate'] . ' - '.$disDate.' / '.$diagFullName.' / '.$checkEvnSection[0]['Lpu_Nick'].'. Сохранить невозможно.';
					throw new Exception($msg);
				} else if ((empty($data['vizit_kvs_control_check']) || $data['vizit_kvs_control_check'] == 0)){
					//предупреждение
					$this->_saveResponse['ignoreParam'] = 'vizit_kvs_control_check';
					$this->_saveResponse['Alert_Msg'] = 'Данное посещение имеет пересечение со случаем стационарного лечения '.$checkEvnSection[0]['EvnSection_setDate'] . ' - '.$disDate.' / '.$diagFullName.' / '.$checkEvnSection[0]['Lpu_Nick'].'.';
					throw new Exception('YesNo', 111);
				}
			} else if (is_array($checkEvnSection) && count($checkEvnSection) > 0 && !empty($checkEvnSection[0]['Error_Msg'])){
				throw new Exception($checkEvnSection[0]['Error_Msg']);
			} else if ($checkEvnSection === false) {
				throw new Exception('Не удалось проверить пересечение посещения с движением.');
			}
		}
		*/
	}

	/**
	 * Проверка даты и времени услуг - не должно быть раньше начала периода лечения и больше даты выписки (если таковая есть)
	 * @param string $Evn_id идентификатор события лечения
	 * @param array $ignoreParentEvnDateCheck необходимость проверки
	 */
	public function CheckEvnUslugasDate($Evn_id, $ignoreParentEvnDateCheck, $fromEmk = '') {
		if (!$ignoreParentEvnDateCheck) {
			//Получаем максимальную и минимальную даты случая лечения
			$Evn_DT = $this->getFirstRowFromQuery(' select EvnClass_SysNick, Evn_setDT, Evn_disDT from v_Evn (nolock) where Evn_id = :Evn_id', array('Evn_id' => $Evn_id));

			$EvnUsluga_id = 'EvnUsluga_rid';

			switch($Evn_DT['EvnClass_SysNick']){
				case 'EvnSection':
					$EvnUsluga_id = 'EvnUsluga_pid';
					if (!empty($EvnSection_disDate)){
						$Evn_DT['Evn_disDT'] = $EvnSection_disDate;
					}
					break;
				case 'EvnPL':

					//Т.к. при закрытии ТАП дата окончания сохраняется после всех проверок. то на момент проверки дата окончания = дате начала ТАП, искуственно приравниваем дату окончания к текущей дате
					// Это некорректно, лучше взять дату окончания случая из последнего посещения.
					$lastEvnVizit = $this->queryResult(' select top 1 Evn_setDT from v_Evn (nolock) where Evn_pid = :Evn_id order by Evn_setDT desc', array('Evn_id' => $Evn_id));
					if (!empty($lastEvnVizit[0]['Evn_setDT'])) {
						$Evn_DT['Evn_disDT'] = $lastEvnVizit[0]['Evn_setDT'];
					} else {
						$Evn_DT['Evn_disDT'] = date_create();
					}
					// округляем дату окончания ТАП до конца дня
					if (!empty($Evn_DT['Evn_disDT'])) {
						$Evn_DT['Evn_disDT'] = date_create($Evn_DT['Evn_disDT']->format("Y-m-d")." 23:59:59");
					}
					break;
			}
			//Получаем даты начала и окончания всех услуг
			$query = "
				select
					'«'+UslugaComplex_Code+'. '+UslugaComplex_Name+'»' as UslugaComplex_FullName,
					EvnUsluga_setDT,
					EvnUsluga_disDT
				from
					v_EvnUsluga EU (nolock)
					left join v_UslugaComplex UC (nolock) on EU.UslugaComplex_id = UC.UslugaComplex_id
				where
					EU.{$EvnUsluga_id} = :Evn_id
					and (EU.EvnUsluga_IsVizitCode is null or EU.EvnUsluga_IsVizitCode = 1)
					and EU.EvnClass_SysNick in ('EvnUslugaCommon', 'EvnUslugaOper')
			";

			//echo getDebugSQL($query, array('Evn_id' => $Evn_id));die;
			$response = $this->queryResult($query, array('Evn_id' => $Evn_id));

			if (!is_array($response)) {
				return $response;
			}

			//Если услуг нет - проверка не нужна
			if (count($response) == 0) {
				return array(array('success' => true, 'Error_Msg' => ''));
			}

			//генерируем сообщение об ошибке сразу по всем услугам
			foreach ($response as $key => $value) {
				$alert_msg = "Период выполнения услуги {$value['UslugaComplex_FullName']} превышает период случая лечения. ";
				if (
					($value['EvnUsluga_setDT'] < $Evn_DT['Evn_setDT'] || (!empty($Evn_DT['Evn_disDT']) && $value['EvnUsluga_setDT'] > $Evn_DT['Evn_disDT'])) ||
					(!empty($value['EvnUsluga_disDT']) && ($value['EvnUsluga_disDT'] < $Evn_DT['Evn_setDT'] || (!empty($Evn_DT['Evn_disDT']) && $value['EvnUsluga_disDT'] > $Evn_DT['Evn_disDT'])))
				) {
					$this->_setAlertMsg($alert_msg);
				}
			}

			if (!empty($this->_saveResponse['Alert_Msg'])) {
				$this->_saveResponse['ignoreParam'] = 'ignoreParentEvnDateCheck';
				return $this->createError(109, 'YesNo');
			}
		}

		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Получение части запроса для определения прав доступа к форме редатирования события
	 * Используется так же при определении прав доступа к справке учащегося
	 */
	function getAccessTypeQueryPart($data, &$params) {
		$EvnClass = !empty($data['EvnClass'])?$data['EvnClass']:$this->evnClassSysNick;
		$EvnAlias = !empty($data['EvnAlias'])?$data['EvnAlias']:$this->evnClassSysNick;
		$session = $data['session'];

		$linkLpuIdList = isset($session['linkedLpuIdList'])?$session['linkedLpuIdList']:array();
		$linkLpuIdList_str = count($linkLpuIdList)>0?implode(',', $linkLpuIdList):'0';

		$queryPart = "
			case
				when {$EvnAlias}.Lpu_id = :Lpu_id then 1
				when {$EvnAlias}.Lpu_id in ({$linkLpuIdList_str}) and ISNULL({$EvnAlias}.{$EvnClass}_IsTransit, 1) = 2 then 1
				else 0
			end = 1
		";

		return $queryPart;
	}

	/**
	 * Получение прав доступа к событию
	 */
	function getAccessType($data) {
		$EvnClass = !empty($data['EvnClass'])?$data['EvnClass']:$this->evnClassSysNick;
		$EvnAlias = !empty($data['EvnAlias'])?$data['EvnAlias']:$this->evnClassSysNick;
		$params = $data;
		$params[$EvnClass.'_id'] = $data['Evn_id'];

		$queryPart = $this->getAccessTypeQueryPart($data, $params);

		$accessType = $this->getFirstResultFromQuery("
			select top 1
			case when {$queryPart} then 'edit' else 'view' end as accessType
			from v_{$EvnClass} {$EvnAlias} with(nolock)
			where {$EvnAlias}.{$EvnClass}_id = :{$EvnClass}_id
		", $params);

		return $accessType;
	}

	/**
	 * Логика перед сохранением
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

		if ($this->IsSigned == 2) {
			$this->setAttribute('issigned', 1); // сбрасываем признак актуальности подписи
		}
	}
}
