<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnNotifyAbstract_model.php');
/**
 * EvnNotifyRegister_model - Модель "Извещение/направление для регистра"
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnNotifyRegister
 * и записи регистра не связаны с заболеваниями
 *
 * Типы извещений/направлений
 *
 * 1) Направление на включение в регистр:
 * Evn.Evn_pid – Ссылка на учетный документ, из которого создано направление. Нельзя удалять посещения, движения, в котором было создано направление на включение в регистр
 * EvnNotifyRegister_Num – Номер, целое число, уникальный номер направления проставлять автоматически в порядке возрастания
 * Evn.Lpu_id – МО создания направления.
 * EvnNotifyBase.MedPersonal_id – Врач, создавший направление.
 * EvnNotifyRegister.EvnVK_id – Ссылка на протокол ВК (только для ВЗН)
 * EvnNotifyRegister.Lpu_oid – Ссылка на МО, в которой пациенту впервые установлен диагноз орфанного заболевания (только для орфанных)
 * EvnNotifyRegister.Diag_id – Диагноз из справочника МКБ-10
 * EvnNotifyRegister_Comment – Обоснование, текст
 * Evn_SetDT – Дата создания, дата
 * EvnNotifyBase_niDate – Дата невключения в регистр, дата
 * EvnNotifyBase.PersonRegisterFailIncludeCause_id – Причина невключения в регистр, выбор из справочника (решение оператора)
 * Всегда содержат NULL:
 * EvnNotifyRegister.PersonRegister_id – ссылка на запись регистра. Ссылка на направление хранится в PersonRegister.EvnNotifyBase_id
 * EvnNotifyRegister.PersonRegisterOutCause_id – Причина исключения из регистра
 *
 * 2) Направление на внесение изменений в регистр:
 * EvnNotifyRegister_Num – Номер, целое число
 * EvnNotifyRegister.PersonRegister_id – ссылка на запись регистра
 * Evn.Lpu_id – МО создания направления.
 * EvnNotifyBase.MedPersonal_id – Врач, создавший направление.
 * Evn_SetDT – Дата создания, дата
 * EvnNotifyRegister.Diag_id – Диагноз из справочника МКБ-10
 * Всегда содержат NULL:
 * Evn.Evn_pid – Ссылка на учетный документ
 * EvnNotifyRegister.EvnVK_id
 * EvnNotifyRegister.Lpu_oid
 * EvnNotifyRegister_Comment – Обоснование, текст
 * EvnNotifyBase_niDate – Дата невключения в регистр, дата
 * EvnNotifyBase.PersonRegisterFailIncludeCause_id – Причина невключения в регистр, выбор из справочника (решение оператора)
 * EvnNotifyRegister.PersonRegisterOutCause_id – Причина исключения из регистра
 *
 * 3) Извещение об исключении из регистра
 * EvnNotifyRegister_Num – Номер, целое число
 * EvnNotifyRegister.PersonRegister_id – ссылка на запись регистра
 * Evn.Lpu_id – МО создания извещения.
 * EvnNotifyBase.MedPersonal_id – Врач, создавший извещение.
 * Evn_SetDT – Дата создания, дата
 * EvnNotifyRegister.EvnVK_id – Ссылка на протокол ВК (только для ВЗН)
 * EvnNotifyRegister.PersonRegisterOutCause_id – Причина исключения из регистра
 * Всегда содержат NULL:
 * EvnNotifyRegister.Lpu_oid
 * EvnNotifyRegister.Diag_id – Диагноз из справочника МКБ-10
 * Evn.Evn_pid – Ссылка на учетный документ
 * EvnNotifyRegister_Comment – Обоснование, текст
 * EvnNotifyBase_niDate – Дата невключения в регистр, дата
 * EvnNotifyBase.PersonRegisterFailIncludeCause_id – Причина невключения в регистр, выбор из справочника (решение оператора)
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Александр Пермяков
 * @version      02.2015
 *
 * @property-read int $NotifyType_id Тип извещения/направления
 * @property-read int $PersonRegisterType_id Тип регистра
 * @property int $PersonRegister_id Ссылка на запись регистра с типом PersonRegisterType_id
 * @property int $EvnVK_id Ссылка на протокол ВК
 * @property int $Lpu_oid МО
 * @property int $Diag_id Диагноз из справочника МКБ-10
 * @property int $num Номер извещения/направления
 * @property string $comment Обоснование, текст до 1024 символов
 * @property int $PersonRegisterOutCause_id Причина исключения из регистра
 *
 * @property-read string $personRegisterTypeSysNick Тип заболевания
 * @property-read bool $isAllowBlankComment
 */
class EvnNotifyRegister_model extends EvnNotifyAbstract_model
{
	protected $_personRegisterTypeSysNick = null;
	protected $_isLoadMorbusType = false;
	protected $_notifyTypeId = null;
	protected $_PersonRegisterType_id = null;
	protected $_isAllowBlankComment = true;
	private $_morbusTypeSysNick = null;

	/**
	 * @return bool
	 */
	function getIsAllowBlankComment()
	{
		return $this->_isAllowBlankComment;
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
			select PersonRegisterType_id from dbo.v_PersonRegisterType (nolock) where PersonRegisterType_SysNick like :PersonRegisterType_SysNick
			', array('PersonRegisterType_SysNick' => $this->personRegisterTypeSysNick));
			if (empty($this->_PersonRegisterType_id)) {
				throw new Exception('Попытка получить идентификатор типа регистра провалилась', 500);
			}
		}
		return $this->_PersonRegisterType_id;
	}

	/**
	 * Определение типа извещения
	 * @return int
	 * @throws Exception
	 */
	function getNotifyType_id()
	{
		return $this->_notifyTypeId;
	}

	/**
	 * Определение типа заболевания
	 * @return int
	 * @throws Exception
	 */
	function getMorbusType_id()
	{
		if (empty($this->PersonRegisterType_id)
			|| false == swPersonRegister::isAllowMorbusType($this->personRegisterTypeSysNick)
			|| empty($this->Diag_id)
		) {
			return null;
		}
		if (false == $this->_isLoadMorbusType) {
			$type_id = $this->PersonRegisterType_id;
			if($type_id != 49){
				$data = swPersonRegister::getStaticPersonRegister($this->personRegisterTypeSysNick)->loadTypeListByDiag($this->Diag_id, $type_id);
				if (empty($data) || empty($data[$type_id])) {
					throw new Exception('Попытка получить идентификатор типа заболевания провалилась', 500);
				}
				$this->_MorbusType_id = $data[$type_id]['MorbusType_id'];
				$this->_morbusTypeSysNick = $data[$type_id]['MorbusType_SysNick'];
			} else {
				$data = swPersonRegister::getStaticPersonRegister($this->personRegisterTypeSysNick)->loadTypeListByDiag($this->Diag_id);
				if (empty($data)) {
					throw new Exception('Попытка получить идентификатор типа заболевания провалилась', 500);
				}
				$newdata = array_shift($data);
				$this->_MorbusType_id = $newdata['MorbusType_id'];
				$this->_morbusTypeSysNick = $newdata['MorbusType_SysNick'];
			}
			$this->_isLoadMorbusType = true;
		}
		return $this->_MorbusType_id;
	}

	/**
	 * Определение типа заболевания
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		if (empty($this->MorbusType_id)) {
			return null;
		}
		return $this->_morbusTypeSysNick;
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnNotifyRegister_id';
		$arr['pid']['alias'] = 'EvnNotifyRegister_pid';
		$arr['pid']['save'] = 'trim';
		$arr['setdate']['label'] = 'Дата создания извещения/направления';
		$arr['setdate']['alias'] = 'EvnNotifyRegister_setDate';
		$arr['setdate']['save'] = 'trim';
		$arr['disdt']['alias'] = 'EvnNotifyRegister_disDT';
		$arr['diddt']['alias'] = 'EvnNotifyRegister_didDT';
		$arr['nidate']['alias'] = 'EvnNotifyRegister_niDate';
		$arr['notifytype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'NotifyType_id',
			'label' => 'Тип извещения/направления',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['personregistertype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'PersonRegisterType_id',
			'label' => 'Тип регистра',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['personregister_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PersonRegister_id',
			'label' => 'Запись регистра',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['num'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'EvnNotifyRegister_Num',
			'label' => 'Номер',
			'save' => 'trim',
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
		$arr['evnvk_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnVK_id',
			'label' => 'Врачебная комиссия',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_oid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_oid',
			'label' => 'МО, в которой пациенту впервые установлен диагноз орфанного заболевания',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['comment'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyRegister_Comment',
			'label' => 'Обоснование',
			'save' => 'trim',
			'type' => 'string'
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
		$arr['evnnotifyregister_outcomment'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM
			),
			'alias' => 'EvnNotifyRegister_OutComment',
			'label' => 'Причина исключения из регистра',
			'save' => 'trim',
			'type' => 'string'
		);

		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 176;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnNotifyRegister';
	}

	/**
	 * Конструктор объекта
	 * @param string $personRegisterTypeSysNick
	 * @param int $notifyTypeId
	 * Решение с передачей в конструктор параметров приемлемо только тогда,
	 * когда данные объекта хранятся только в таблицах Evn EvnNotifyBase EvnNotifyRegister,
	 * когда бизнес-логика объекта мало отличается от общей логики этого класса.
	 * В остальных случаях нужно создать новый класс, который унаследует и перекроет свойства и методы этого класса,
	 * а также обязательно определит свойства _personRegisterTypeSysNick, _notifyTypeId
	 */
	function __construct($personRegisterTypeSysNick = '', $notifyTypeId = 0)
	{
		$this->load->library('swPersonRegister');
		if (empty($this->_personRegisterTypeSysNick) && !empty($personRegisterTypeSysNick)) {
			$this->_personRegisterTypeSysNick = $personRegisterTypeSysNick;
		}
		if (empty($this->_notifyTypeId) && !empty($notifyTypeId)) {
			$this->_notifyTypeId = $notifyTypeId;
		}

		parent::__construct();

		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_AUTO_UPDATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
			'doLoadPrintForm',
			'notInclude',
		));
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
			select top 1 {$fields}
			from {$viewName} with (nolock)
			{$joins}
			where {$where}
				and {$viewName}.PersonRegisterType_id = :PersonRegisterType_id
				and {$viewName}.NotifyType_id = :NotifyType_id
		";
		$params['NotifyType_id'] = $this->NotifyType_id;
		$params['PersonRegisterType_id'] = $this->PersonRegisterType_id;
		//throw new Exception(getDebugSql($sql, $params));
		return array(
			'sql' => $sql,
			'params' => $params,
		);
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
			case 'doLoadPrintForm':
				$rules = array(
					array(
						'field' => 'EvnNotifyRegister_id',
						'label' => 'Извещение/направление',
						'rules' => 'required',
						'type' => 'id'
					),
				);
				break;
			case 'notInclude':
				$rules = array(
					array(
						'field' => 'EvnNotifyBase_id',
						'label' => 'Извещение/направление',
						'rules' => 'required',
						'type' => 'id'
					),
					array(
						'field' => 'PersonRegisterFailIncludeCause_id',
						'label' => 'Причина невключения в регистр',
						'rules' => 'required',
						'type' => 'id'
					),
					array(
						'field' => 'Lpu_niid',
						'label' => 'МО',
						'rules' => 'required',
						'type' => 'id'
					),
					array(
						'field' => 'MedPersonal_niid',
						'label' => 'Врач',
						'rules' => 'required',
						'type' => 'id'
					)
				);
				break;
			case 'loadDataCheckExists':
				$rules = array(
					'PersonRegisterType_SysNick' => array(
						'field' => 'PersonRegisterType_SysNick',
						'label' => 'Тип регистра',
						'rules' => 'trim|required',
						'type' => 'string'
					),
					'Person_id' => array(
						'field' => 'Person_id',
						'label' => 'Человек',
						'rules' => 'trim|required',
						'type' => 'id'
					),
					'Diag_id' => array(
						'field' => 'Diag_id',
						'label' => 'Диагноз',
						'rules' => 'trim',
						'type' => 'id'
					),
				);
				if ( 'nolos' == $this->personRegisterTypeSysNick) {
					$rules['Diag_id']['rules'] = 'trim|required';
				}
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
		if ( false == $this->isAllowBlankComment) {
			$paramNameComment = $this->_getInputParamName('comment');
			$all[$paramNameComment]['rules'] = 'trim|required';
		}
		if ( 'nolos' == $this->personRegisterTypeSysNick && 1 == $this->_notifyTypeId) {
			$all['Diag_id']['rules'] = 'trim|required';
			// только создание
			/*if (isset($all['EvnNotifyRegister_id'])) {
				unset($all['EvnNotifyRegister_id']);
			}*/
		}
		if ( 'nolos' == $this->personRegisterTypeSysNick && in_array($this->_notifyTypeId, array(1,3))) {
			$all['EvnVK_id']['rules'] = 'trim';
		}
		if ( 'orphan' == $this->personRegisterTypeSysNick && 1 == $this->_notifyTypeId) {
			$all['Lpu_oid']['rules'] = 'trim|required';
		}
		// параметры
		if ( 1 == $this->_notifyTypeId) {
			$all['Lpu_did'] = array(
				'field' => 'Lpu_did',
				'label' => 'МО заполнения направления',// Может не совпадать с МО пользователя
				'rules' => 'trim|required',
				'type' => 'id'
			);
		}
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
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))
			|| (self::SCENARIO_AUTO_UPDATE == $this->scenario && 'orphan' == $this->personRegisterTypeSysNick)
			|| (self::SCENARIO_AUTO_UPDATE == $this->scenario && 'nolos' == $this->personRegisterTypeSysNick)
			|| (self::SCENARIO_AUTO_UPDATE == $this->scenario && 'prof' == $this->personRegisterTypeSysNick)
		) {
			$this->_params['Lpu_did'] = isset($data['Lpu_did']) ? $data['Lpu_did'] : null ;
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		if ( false == swPersonRegister::isAllow($this->personRegisterTypeSysNick) ) {
			throw new Exception('Работа с данным типом регистра недоступна!');
		}

		if (in_array($this->scenario, array(self::SCENARIO_DELETE, self::SCENARIO_SET_ATTRIBUTE, 'notInclude'))
			&& false == swPersonRegister::getStaticPersonRegister($this->personRegisterTypeSysNick)->isRegisterOperator($this->sessionParams)
		) {
			throw new Exception('Действия «Удалить», «Не включать в регистр» доступны только для оператора регистра');
		}

		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE)) && (empty($this->setDT) || empty($this->setDate))) {
			$this->setAttribute('setdt', $this->currentDT);
			$this->setAttribute('setdate', $this->currentDT->format('Y-m-d'));
		}

		parent::_validate();

		if ( empty($this->PersonRegisterType_id) ) {
			throw new Exception('Неправильный тип регистра');
		}

		if ( false == in_array($this->_notifyTypeId, array(1,2,3)) ) {
			throw new Exception('Неправильный тип извещения/направления');
		}

		if ('notInclude' == $this->scenario) {
			if ( empty($this->id) || empty($this->PersonRegisterFailIncludeCause_id)
				|| empty($this->Lpu_niid) || empty($this->MedPersonal_niid)
			) {
				throw new Exception('Неправильные входящие параметры для невключения в регистр', 500);
			}
			if ( empty($this->niDate) ) {
				$this->setAttribute('nidate', $this->currentDT);
			}
		}

		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE, self::SCENARIO_AUTO_UPDATE))) {
			if ( empty($this->Person_id)) {
				throw new Exception('Нужно указать Человека');
			}
			if (swPersonRegister::isMorbusRegister($this->personRegisterTypeSysNick) && (empty($this->morbusTypeSysNick) || empty($this->MorbusType_id))) {
				throw new Exception('Нужно указать тип заболевания');
			}
			switch ($this->_notifyTypeId) {
				case 1: // Направление на включение в регистр
					if ($this->regionNick != 'kz' && $this->personRegisterTypeSysNick == 'nolos') {
						$query = "
							select top 1
								PS.Person_Snils,
								MP.Person_Snils as MedPersonal_Snils
							from
								(select top 1 * from v_Person_all with(nolock) where PersonEvn_id = :PersonEvn_id and Server_id = :Server_id) PS,
								(select top 1 * from v_MedPersonal with(nolock) where MedPersonal_id = :MedPersonal_id) MP
						";
						$resp = $this->getFirstRowFromQuery($query, array(
							'PersonEvn_id' => $this->PersonEvn_id,
							'MedPersonal_id' => $this->MedPersonal_id,
							'Server_id' => $this->Server_id
						));
						if (!is_array($resp)) {
							throw new Exception('Ошибка при получении данных пацинета и врача');
						}
						if (empty($resp['Person_Snils'])) {
							throw new Exception('Для включения в регистр нужно указать СНИЛС пациента');
						}
						if (empty($resp['MedPersonal_Snils'])) {
							throw new Exception('Для включения в регистр нужно указать СНИЛС врача');
						}
					}
					if ( false == $this->isAllowBlankComment && empty($this->comment) ) {
						throw new Exception('Нужно ввести обоснование направления');
					}
					if ( !empty($this->comment) && mb_strlen($this->comment) > 1024 ) {
						throw new Exception('Обоснование направления превышает 1024 символов');
					}
					/*if ( empty($this->EvnVK_id) && 'nolos' == $this->personRegisterTypeSysNick) {
						throw new Exception('Нужно указать Протокол ВК');
					}*/
					if ( empty($this->_params['Lpu_did']) ) {
						throw new Exception('Нужно указать МО заполнения направления');
					}
					$this->setAttribute('lpu_id', $this->_params['Lpu_did']);
					if ( empty($this->Diag_id) && in_array($this->personRegisterTypeSysNick, array(
							'nolos',
							'orphan',
							'prof'
						))
					) {
						throw new Exception('Нужно указать Диагноз');
					}
					if ( empty($this->MorbusType_id) && false == in_array($this->personRegisterTypeSysNick, array(
							'orphan',
							'nolos',
							'prof'
						))
					) {
						throw new Exception('Диагноз не сопоставлен с нозологией');
					}
					if ( empty($this->Lpu_oid) && self::SCENARIO_DO_SAVE == $this->scenario && 'orphan' == $this->personRegisterTypeSysNick) {
						throw new Exception('Нужно указать МО, в которой пациенту впервые установлен диагноз орфанного заболевания');
					}
					if ($this->isNewRecord) {
						// контроль на наличие в системе Объекта «Направление на включение в регистр»
						$checkData = $this->checkExistsEvnNotifyRegisterInclude($this->Person_id, $this->Diag_id);
						if ( empty($checkData) || false == array_key_exists('isDisabledCreate', $checkData)) {
							throw new Exception('При контроле возможности создания направления на включение в регистр произошла ошибка');
						}
						if ( 'nolos' == $this->personRegisterTypeSysNick && $checkData['isDisabledCreate']) {
							// может сработать при добавлении из журнала
							throw new Exception('На выбранного пациента уже создано Направление на включение в регистр по ВЗН по указанной группе диагнозов');
						}
						if ( 'orphan' == $this->personRegisterTypeSysNick && $checkData['isDisabledCreate']) {
							throw new Exception('На выбранного пациента уже создано Направление на включение в регистр по орфанным заболеваниям с указанным диагнозом');
						}
						if ( 'prof' == $this->personRegisterTypeSysNick && $checkData['isDisabledCreate']) {
							throw new Exception('На выбранного пациента уже создано Направление на включение в регистр по профзаболеваниям с указанным диагнозом');
						}
						if ( $checkData['isDisabledCreate']) {
							throw new Exception('На выбранного пациента уже создано Направление на включение в регистр');
						}
					}
					break;
				case 2: // Направление на внесение изменений в регистр
					if ( empty($this->_params['Lpu_did']) ) {
						throw new Exception('Нужно указать МО заполнения направления');
					}
					$this->setAttribute('lpu_id', $this->_params['Lpu_did']);
					if ( empty($this->PersonRegister_id) ) {
						throw new Exception('Нужно указать запись регистра');
					}
					if ( empty($this->Diag_id) && in_array($this->personRegisterTypeSysNick, array(
							'nolos',
							'orphan',
							'prof'
						))
					) {
						throw new Exception('Нужно указать Диагноз');
					}
					break;
				case 3: // Извещение об исключении из регистра
					if ( empty($this->_params['Lpu_did']) ) {
						throw new Exception('Нужно указать МО заполнения извещения');
					}
					$this->setAttribute('lpu_id', $this->_params['Lpu_did']);
					if ( empty($this->PersonRegister_id) ) {
						throw new Exception('Нужно указать запись регистра');
					}
					if ( empty($this->PersonRegisterOutCause_id) ) {
						throw new Exception('Нужно указать причину исключения из регистраа');
					}
					
					break;
			}
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
		$this->setAttribute('notifytype_id', $this->_notifyTypeId);
		$this->setAttribute('personregistertype_id', $this->PersonRegisterType_id);
		$this->setAttribute('morbustype_id', $this->MorbusType_id);
		if (swPersonRegister::isMorbusRegister($this->personRegisterTypeSysNick)) {
			if (empty($this->Morbus_id) || empty($this->MorbusType_id)) {
				// проверка существования и создание заболевания
				/* пока этого тут не надо, т.к. для извещений по заболеваниям свои модели
				$this->load->library('swMorbus');
				$tmp = swMorbus::onBeforeSaveEvnNotify($this->morbusTypeSysNick, $this->pid, $this->sessionParams);
				$this->setAttribute('morbustype_id', $tmp['MorbusType_id']);
				$this->setAttribute('morbus_id', $tmp['Morbus_id']);
				$this->_params['Morbus_Diag_id'] = $tmp['Diag_id'];
				*/
			}
		} else {
			$this->setAttribute('morbus_id', null);
		}
		switch ($this->_notifyTypeId) {
			case 1: // Направление на включение в регистр
				$this->setAttribute('personregisteroutcause_id', null);
				if ( 'orphan' != $this->personRegisterTypeSysNick) {
					$this->setAttribute('lpu_oid', null);
				}
				break;
			case 2: // Направление на внесение изменений в регистр
				$this->setAttribute('pid', null);
				$this->setAttribute('lpu_oid', null);
				$this->setAttribute('evnvk_id', null);
				$this->setAttribute('comment', null);
				$this->setAttribute('nidate', null);
				$this->setAttribute('personregisterfailincludecause_id', null);
				$this->setAttribute('personregisteroutcause_id', null);
				break;
			case 3: // Извещение об исключении из регистра
				$this->setAttribute('pid', null);
				$this->setAttribute('lpu_oid', null);
				$this->setAttribute('diag_id', null);
				$this->setAttribute('comment', null);
				$this->setAttribute('nidate', null);
				$this->setAttribute('personregisterfailincludecause_id', null);
				break;
		}
		if ( $this->isNewRecord ) {
			// проставлять автоматически в порядке возрастания
			$max_num = $this->getFirstResultFromQuery("
				select MAX(EvnNotifyRegister_Num) as num
				from v_EvnNotifyRegister (nolock)
				where PersonRegisterType_id = :PersonRegisterType_id and NotifyType_id = :NotifyType_id
			", array(
				'PersonRegisterType_id' => $this->PersonRegisterType_id,
				'NotifyType_id' => $this->_notifyTypeId,
			));
			if (false === $max_num) {
				throw new Exception('Не удалось выполнить запрос максимального номера направления/извещения');
			}
			$this->setAttribute('num', $max_num + 1);
		}
		if (
			in_array($this->scenario, array(self::SCENARIO_AUTO_CREATE, swModel::SCENARIO_DO_SAVE))
			&& $this->PersonRegisterType_id == 49
			&& !empty($data['EvnNotifyRegister_Num']) 
		) {
			$this->setAttribute('num', $data['EvnNotifyRegister_Num']);
		}
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		if (swPersonRegister::isMorbusRegister($this->personRegisterTypeSysNick)) {
			/* пока этого тут не надо, т.к. для извещений по заболеваниям свои модели
			$tmp = swMorbus::onAfterSaveEvnNotify($this->morbusTypeSysNick, array(
				'EvnNotifyBase_id' => $this->id,
				'EvnNotifyBase_pid' => $this->pid,
				'EvnNotifyBase_setDate' => $this->setDate,
				'Server_id' => $this->Server_id,
				'PersonEvn_id' => $this->PersonEvn_id,
				'Person_id' => $this->Person_id,
				'Morbus_id' => $this->Morbus_id,
				'MorbusType_id' => $this->MorbusType_id,
				'Morbus_Diag_id' => $this->_params['Morbus_Diag_id'],
				'Lpu_id' => $this->Lpu_id,
				'MedPersonal_id' => $this->MedPersonal_id,
				'session' => $this->sessionParams
			));
			$this->_saveResponse = array_merge($this->_saveResponse, $tmp);
			*/
		}
	}

	/**
	 * Получение печатной формы
	 */
	function doLoadPrintForm($data)
	{
		if ( false == swPersonRegister::isAllow($this->personRegisterTypeSysNick) ) {
			return 'Работа с данным типом регистра недоступна!';
		}
		if ( empty($data[$this->tableName() . '_id']) ) {
			return 'Нужен идентификатор!';
		}
		$params = array(
			'id' => $data[$this->tableName() . '_id'],
			'NotifyType_id' => $this->NotifyType_id,
			'PersonRegisterType_id' => $this->PersonRegisterType_id,
		);
		switch (true) {
			case ('nolos' == $this->personRegisterTypeSysNick && 3 == $this->NotifyType_id):
				$tpl = 'Nolos_02_FR';
				$parse_data = $this->_loadDataForNolos02FR($params);
				break;
			case ('nolos' == $this->personRegisterTypeSysNick && 3 != $this->NotifyType_id):
				$tpl = 'Nolos_01_FR';
				$parse_data = $this->_loadDataForNolos01FR($params);
				break;
			default:
				$tpl = '';
				break;
		}
		if ( empty($tpl) ) {
			return 'Вывод печатной формы не реализован!';
		}
		if ( empty($parse_data) ) {
			return 'Данные для печатной формы не найдены!';
		}
		$this->load->library('parser');
		array_walk_recursive($parse_data, 'ConvertFromWin1251ToUTF8');
		return $this->parser->parse('person_register/'.$tpl, $parse_data, true);
	}

	/**
	 * Контроль возможности создания объекта «Направление на включение в регистр»
	 *
	 * В общем случае по одной группе диагнозов или одному типу регистра можно создать
	 * несколько направлений на включение в регистр (без причины не включения в регистр только одно),
	 * одну открытую запись регистра (если есть закрытая запись, то оператор может "вернуть" в регистр),
	 * несколько направлений на внесение изменений в регистр,
	 * несколько извещений об исключении из регистра (т.к. возможно повторное исключение) или надо удалять извещение об исключении из регистра при возвращении в регистр?
	 * @param $Person_id
	 * @param $evn_Diag_id
	 * @return bool|array Если нельзя создать, то возвращается массив с ключом isDisabledCreate = 1, а в случае ошибки - false
	 */
	function checkExistsEvnNotifyRegisterInclude($Person_id, $evn_Diag_id = null)
	{
		if (1 != $this->_notifyTypeId) {
			return false;
		}
		$resp = $this->loadDataCheckExists($Person_id, $evn_Diag_id);
		if (false === $resp || false == is_array($resp)) {
			return false;
		}
		/*
		$debug = $resp;
		$debug[] = $this->personRegisterTypeSysNick;
		throw new Exception(var_export($debug, true));
		*/
		switch ($this->personRegisterTypeSysNick) {
			case 'orphan':
			case 'nolos':
				// без направления не может быть записи регистра
				$this->_saveResponse['isDisabledCreate'] = 0;
				if (count($resp) > 0 && (empty($resp[0]['PersonRegister_id'])
						|| empty($resp[0]['PersonRegisterOutCause_Code'])
						|| 1 == $resp[0]['PersonRegisterOutCause_Code']
					)
				) {
					// есть направление без причины невключения и нет записи регистра
					// или нет причины исключения
					// или есть записи регистра с причиной исключения смерть
					$this->_saveResponse['isDisabledCreate'] = 1;
				}
				break;
			case 'onko':
				$this->_saveResponse['isDisabledCreate'] = 0;
				break;
			default:
				if (count($resp) == 0) {
					// нет направления и нет записи регистра
					$this->_saveResponse['isDisabledCreate'] = 0;
				} else {
					// есть направление без причины не включения или запись регистра
					$this->_saveResponse['isDisabledCreate'] = 1;
				}
				break;
		}
		return $this->_saveResponse;
	}

	/**
	 * Получаем данные для проверки возможности создания объекта «Направление на включение в регистр»
	 * @param $Person_id
	 * @param $evn_Diag_id
	 * @return bool|array Если нет направления на включение в регистр, возвращается пустой массив, а в случае ошибки - false
	 */
	function loadDataCheckExists($Person_id, $evn_Diag_id = null)
	{
		if (1 != $this->_notifyTypeId) {
			return false;
		}
		$params = array(
			'NotifyType_id' => $this->_notifyTypeId,
			'Person_id' => $Person_id,
			'PersonRegisterType_id' => swPersonRegister::getTypeIdBySysNick($this->personRegisterTypeSysNick),
		);
		if (empty($params['Person_id']) || empty($params['PersonRegisterType_id'])) {
			return false;
		}
		$and_diag = '';
		if(!empty($evn_Diag_id))
		{
			$params['Diag_id'] = $evn_Diag_id;
			$and_diag = "
				 and PR.Diag_id in (
					select DD.Diag_id 
					from v_Diag D with(nolock) 
					left join v_Diag DD with(nolock) on D.Diag_pid = DD.Diag_pid 
					where D.Diag_id = :Diag_id
				)
			";
		}
		$tableName = $this->tableName();
		if (in_array($this->personRegisterTypeSysNick, array(
			'orphan',
			'nolos',
		))) {
			// nolos в регистр можно включить только по направлению,
			// orphan в регистр можно включить по направлению, если его нет, то оно создается автоматически
			// используется контроль по одной группе диагнозов (нозологии) MorbusType_id
			// если указан диагноз С83.0 , то необходимо проводить контроль на наличие «Направление на включение в регистр»
			// с любым диагнозом из группы «Злокачественные  новообразования  лимфоидной,  кроветворной  и родственных им  тканей
			// (т.е. с диагнозом C92.1, C88.0 ,  C90.0,  C82, C83.0, C83.1, C83.3, C83.4, C83.8, C83.9, C85, C91.1)
			if (empty($evn_Diag_id)) {
				return false;
			}
			if($this->personRegisterTypeSysNick == 'nolos') {
				$arr = swPersonRegister::getStaticPersonRegister()->loadTypeListByDiag($evn_Diag_id);
				if (empty($arr) ) {
					throw new Exception('Попытка получить идентификатор типа заболевания провалилась', 500);
					return false;
				}
				$newarr = array_shift($arr);
				$params['MorbusType_id'] = $newarr['MorbusType_id'];
			} else {
				$arr = swPersonRegister::getStaticPersonRegister()->loadTypeListByDiag($evn_Diag_id, $params['PersonRegisterType_id']);
				$key = $params['PersonRegisterType_id'];
				if (empty($arr) || empty($arr[$key]) || empty($arr[$key]['MorbusType_id']) ) {
					return false;
				}
				$params['MorbusType_id'] = $arr[$key]['MorbusType_id'];
			}
			// если есть направление на включение в регистр по той же нозологии, но у которого есть причина не включения в регистр, можно повторно создать направление на включение в регистр
			// если есть направление на включение в регистр по той же нозологии, но пациент исключен из регистра по причине "выехал", то можно снова создать направление
			// возможны только 2 причины исключения из регистра по 7 нозологиям - смерть или выехал
			$query = "
				select top 1
					PR.PersonRegister_id,
					EN.{$tableName}_id as EvnNotifyBase_id,
					OutCause.PersonRegisterOutCause_Code
				from v_{$tableName} EN with (nolock)
					left join v_PersonRegister PR with (nolock) on PR.EvnNotifyBase_id = EN.{$tableName}_id
					left join v_PersonRegisterOutCause OutCause with (nolock) on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				where
					EN.Person_id = :Person_id
					AND EN.PersonRegisterType_id = :PersonRegisterType_id
					AND EN.NotifyType_id = :NotifyType_id
					AND EN.PersonRegisterFailIncludeCause_id is null
					AND EN.MorbusType_id = :MorbusType_id
					{$and_diag}
			";
			/*
					AND exists (
						select top 1 rtd.Diag_id from v_PersonRegisterDiag rtd with (nolock)
						where rtd.Diag_id = EN.Diag_id
							AND rtd.MorbusType_id = :MorbusType_id
					)
			 */
		} else {
			// в регистр можно включить по направлению и без него
			$add_where = '';
			$add_join_pr = '';
			$add_join_en = '';
			if (false == in_array($this->personRegisterTypeSysNick, swPersonRegister::listPersonRegisterTypeOneByPerson())) {
				// требуется проверять также по группе диагнозов
				if (empty($evn_Diag_id)) {
					return false;
				}
				$params['Diag_id'] = $evn_Diag_id;
				$add_join_en = "
					left join v_Diag Diag with (nolock) on Diag.Diag_id = EN.Diag_id";
				$add_join_pr = "
					left join v_Diag Diag with (nolock) on Diag.Diag_id = PR.Diag_id";
				$add_where .= ' AND Diag.Diag_id in (select DD.Diag_id from v_Diag D with (nolock) left join v_Diag DD with (nolock) on D.Diag_pid = DD.Diag_pid where D.Diag_id = :Diag_id)';
			}
			$query = "
				select top 1
					PR.PersonRegister_id,
					EN.{$tableName}_id as EvnNotifyBase_id,
					OutCause.PersonRegisterOutCause_Code
				from v_{$tableName} EN with (nolock)
					left join v_PersonRegister PR with (nolock) on PR.EvnNotifyBase_id = EN.{$tableName}_id
					left join v_PersonRegisterOutCause OutCause with (nolock) on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					{$add_join_en}
				where
					EN.Person_id = :Person_id
					AND EN.PersonRegisterType_id = :PersonRegisterType_id
					AND EN.NotifyType_id = :NotifyType_id
					AND EN.PersonRegisterFailIncludeCause_id is null
					{$add_where}
				union all
				select top 1
					PR.PersonRegister_id,
					PR.EvnNotifyBase_id,
					OutCause.PersonRegisterOutCause_Code
				from v_PersonRegister PR with (nolock)
					left join v_PersonRegisterOutCause OutCause with (nolock) on OutCause.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
					{$add_join_pr}
				where
					PR.Person_id = :Person_id
					AND PR.PersonRegisterType_id = :PersonRegisterType_id
					AND PR.EvnNotifyBase_id is null
					{$add_where}
			";
		}
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $params
	 * @return bool|array
	 */
	private function _loadDataForNolos01FR($params)
	{
		return $this->getFirstRowFromQuery("
			declare @curDate datetime = CAST(dbo.tzGetDate() as date);
			select top 1
				case
					when exists(
						select top 1 PP.PersonPrivilege_id
						from v_PersonPrivilege PP WITH (NOLOCK)
						inner join PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 1 /* ФЗ № 178 - если есть федеральная льгота */
						where PP.Person_id = PS.Person_id
							and PP.PersonPrivilege_begDate <= @curDate
							and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= @curDate)
							and not exists (
								select top 1 PR.PersonRefuse_id from PersonRefuse PR WITH (NOLOCK)
								where PR.Person_id = PP.Person_id
									and isnull(PR.PersonRefuse_IsRefuse,1) = 2
									and PR.PersonRefuse_Year = year(@curDate)
							)
					) then 2 else 1
				end as punct9,
				(
					select top 1 PP.PersonPrivilege_id
					from v_PersonPrivilege PP WITH (NOLOCK)
					inner join PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
						and PT.ReceptFinance_id = 1 /* ФЗ № 178 - если есть федеральная льгота */
					where PP.Person_id = PS.Person_id
						and PP.PersonPrivilege_begDate <= @curDate
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= @curDate)
						and not exists (
							select top 1 PR.PersonRefuse_id from PersonRefuse PR WITH (NOLOCK)
							where PR.Person_id = PP.Person_id
								and isnull(PR.PersonRefuse_IsRefuse,1) = 2
								and PR.PersonRefuse_Year = year(@curDate)
						)
				)
				as punct91,
				case
					when exists(
						select top 1 PP.PersonPrivilege_id
						from v_PersonPrivilege PP WITH (NOLOCK)
						inner join PrivilegeType PT WITH (NOLOCK) on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 2 /* постановление № 890 - если есть региональная льгота */
						where PP.Person_id = PS.Person_id
							and PP.PersonPrivilege_begDate <= @curDate
							and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= @curDate)
							and not exists (
								select top 1 PR.PersonRefuse_id from PersonRefuse PR WITH (NOLOCK)
								where PR.Person_id = PP.Person_id
									and isnull(PR.PersonRefuse_IsRefuse,1) = 2
									and PR.PersonRefuse_Year = year(@curDate)
							)
					) then 2 else 1
				end as punct11,
				isnull(RTRIM(PJ.Org_Name), '') as Person_Job,
				isnull(RTRIM(PP.Post_Name), '') as Person_Post,
				CASE
					WHEN PS.Sex_id = 1 then 'М'
					WHEN PS.Sex_id = 2 then 'Ж'
					ELSE NULL
				END as Sex_Nick,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE isnull(RTRIM(Polis.Polis_Ser), '') END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(ps.Person_EdNum), '') ELSE isnull(RTRIM(Polis.Polis_Num), '') END AS Polis_Num,
				RTRIM(ISNULL(OS.OrgSmo_Nick, OS.OrgSmo_Name)) as OrgSmo_Name,
				isnull(RTRIM(PS.Person_Snils), '') as Person_Snils,
				v_Lpu.Lpu_Name,
				v_Lpu.PAddress_Address as Lpu_Adress,
				v_Lpu.Lpu_OGRN,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				RTRIM(isnull([PAddress].Address_Nick, [PAddress].Address_Address)) as Person_PAddress,
				isnull(RTRIM(Oktmo.Oktmo_Code), '') as Oktmo_Code,
				Diag.Diag_Code,
				DocumentType.DocumentType_Name,
				RTRIM([Document].Document_Num) as Document_Num,
				RTRIM([Document].Document_Ser) as Document_Ser,
				convert(varchar(10), [Document].Document_begDate, 104) as Document_begDate,
				CASE WHEN vPP.PrivilegeType_Code = '81' THEN '3 группа'
					 WHEN vPP.PrivilegeType_Code = '82' THEN '2 группа'
					 WHEN vPP.PrivilegeType_Code = '83' THEN '1 группа'
					 WHEN vPP.PrivilegeType_Code = '84' THEN 'ребенок-инвалид'
				END as physically_challenged,
				RTRIM(DO.Org_Name) as OrgDep_Name,
				MP.MedPersonal_Code,
				MP.Person_Fio as Doctor_Fio,
				MPPS.Person_Phone as Doctor_Phone,
				predsed.Person_Fio as Predsedatel_Fio,
				E.EvnNotifyRegister_Comment,
				E.EvnNotifyRegister_Num,
				convert(varchar(10), E.EvnNotifyRegister_setDT, 104) as EvnNotifyRegister_setDate,
				(
					select top 1 PSN.PersonSurName_SurName
					from v_PersonSurName PSN WITH (NOLOCK)
					where PSN.Person_id = PS.Person_id
					ORDER BY PSN.PersonSurName_begDT ASC
				)
				as Birth_SurName
			from v_EvnNotifyRegister E (NOLOCK)
			inner join v_PersonState PS (NOLOCK) on PS.Person_id = E.Person_id
			left join PersonRegister PR WITH (NOLOCK) on PR.PersonRegister_id = E.PersonRegister_id
			left join [Address] [PAddress] WITH (NOLOCK) on [PAddress].Address_id = PS.PAddress_id
			left join [Document] WITH (NOLOCK) on [Document].[Document_id] = [PS].[Document_id]
			left join DocumentType WITH (NOLOCK) on DocumentType.DocumentType_id = [Document].DocumentType_id
			left join [OrgDep] WITH (NOLOCK) on [OrgDep].[OrgDep_id] = [Document].[OrgDep_id]
			left join [Org] [DO] WITH (NOLOCK) on [DO].[Org_id] = [OrgDep].[Org_id]
			left join v_Lpu (NOLOCK) on v_Lpu.Lpu_id = E.Lpu_id
			left join Diag (NOLOCK) on Diag.Diag_id = isnull(E.Diag_id,PR.Diag_id)
			left join v_EvnVK EvnVK (NOLOCK) on EvnVK.EvnVK_id = E.EvnVK_id
			outer apply (
				select top 1 MP.Person_Fio
				from v_EvnVKExpert (nolock)
				inner join v_MedServiceMedPersonal MSMP (nolock) on MSMP.MedServiceMedPersonal_id = v_EvnVKExpert.MedServiceMedPersonal_id
				inner join v_MedService MS (nolock) on MS.MedService_id = MSMP.MedService_id
				inner join v_MedPersonal MP (nolock) on MP.MedPersonal_id = MSMP.MedPersonal_id and MP.Lpu_id = MS.Lpu_id
				where v_EvnVKExpert.EvnVK_id = EvnVK.EvnVK_id and v_EvnVKExpert.ExpertMedStaffType_id = 1
			) predsed
			left join v_MedPersonal MP (NOLOCK) on MP.MedPersonal_id = E.MedPersonal_id and MP.Lpu_id = E.Lpu_id
			left join v_PersonState MPPS (NOLOCK) on MPPS.Person_id = MP.Person_id
			left join [Polis] WITH (NOLOCK) on [Polis].[Polis_id] = [PS].[Polis_id]
			left join [PolisType] WITH (NOLOCK) on [PolisType].[PolisType_id] = [Polis].[PolisType_id]
			left join v_OrgSMO OS with (NOLOCK) on OS.OrgSMO_id = [Polis].OrgSmo_id
			left join [v_Job] [Job] WITH (NOLOCK) on [Job].[Job_id] = [PS].[Job_id]
			left join [Org] [PJ] WITH (NOLOCK) on [PJ].[Org_id] = [Job].[Org_id]
			left join [Post] [PP] WITH (NOLOCK) on [PP].[Post_id] = [Job].[Post_id]
			left join v_Oktmo Oktmo with (NOLOCK) on Oktmo.KLArea_id = coalesce([PAddress].KLTown_id, [PAddress].KLCity_id, [PAddress].KLSubRgn_id, [PAddress].KLRgn_id)
			left join v_PersonPrivilege vPP with (nolock) on vPP.Person_id = PS.Person_id
			where E.EvnNotifyRegister_id = :id
			and E.PersonRegisterType_id = :PersonRegisterType_id
			and E.NotifyType_id = :NotifyType_id
			", $params
		);
	}

	/**
	 * @param $params
	 * @return bool|array
	 */
	private function _loadDataForNolos02FR($params)
	{
		return $this->getFirstRowFromQuery("
			select top 1
				v_Lpu.Lpu_Name,
				v_Lpu.PAddress_Address as Lpu_Adress,
				v_Lpu.Lpu_OGRN,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				RTRIM(isnull([PAddress].Address_Nick, [PAddress].Address_Address)) as Person_PAddress,
				isnull(RTRIM(Oktmo.Oktmo_Code), '') as Oktmo_Code,
				Diag.Diag_Code,
				DocumentType.DocumentType_Name,
				RTRIM([Document].Document_Num) as Document_Num,
				RTRIM([Document].Document_Ser) as Document_Ser,
				convert(varchar(10), [Document].Document_begDate, 104) as Document_begDate,
				RTRIM(DO.Org_Name) as OrgDep_Name,
				O.PersonRegisterOutCause_Name,
				MP.MedPersonal_Code,
				MP.Person_Fio as Doctor_Fio,
				MPPS.Person_Phone as Doctor_Phone,
				predsed.Person_Fio as Predsedatel_Fio,
				E.EvnNotifyRegister_Comment,
				E.EvnNotifyRegister_Num,
				convert(varchar(10), E.EvnNotifyRegister_setDT, 104) as EvnNotifyRegister_setDate,
				(
					select top 1 PSN.PersonSurName_SurName
					from v_PersonSurName PSN WITH (NOLOCK)
					where PSN.Person_id = PS.Person_id
					ORDER BY PSN.PersonSurName_begDT ASC
				)
				as Birth_SurName
			from v_EvnNotifyRegister E (NOLOCK)
			inner join v_PersonState PS (NOLOCK) on PS.Person_id = E.Person_id
			left join PersonRegister PR WITH (NOLOCK) on PR.PersonRegister_id = E.PersonRegister_id
			left join [Address] [PAddress] WITH (NOLOCK) on [PAddress].Address_id = PS.PAddress_id
			left join [Document] WITH (NOLOCK) on [Document].[Document_id] = [PS].[Document_id]
			left join DocumentType WITH (NOLOCK) on DocumentType.DocumentType_id = [Document].DocumentType_id
			left join [OrgDep] WITH (NOLOCK) on [OrgDep].[OrgDep_id] = [Document].[OrgDep_id]
			left join [Org] [DO] WITH (NOLOCK) on [DO].[Org_id] = [OrgDep].[Org_id]
			left join v_Lpu (NOLOCK) on v_Lpu.Lpu_id = E.Lpu_id
			left join PersonRegisterOutCause O (NOLOCK) on O.PersonRegisterOutCause_id = E.PersonRegisterOutCause_id
			left join Diag (NOLOCK) on Diag.Diag_id = isnull(E.Diag_id,PR.Diag_id)
			left join v_MedPersonal MP (NOLOCK) on MP.MedPersonal_id = E.MedPersonal_id and MP.Lpu_id = E.Lpu_id
			left join v_PersonState MPPS (NOLOCK) on MPPS.Person_id = MP.Person_id
			left join v_EvnVK EvnVK (NOLOCK) on EvnVK.EvnVK_id = E.EvnVK_id
			left join v_Oktmo Oktmo with (NOLOCK) on Oktmo.KLArea_id = coalesce([PAddress].KLTown_id, [PAddress].KLCity_id, [PAddress].KLSubRgn_id, [PAddress].KLRgn_id)
			outer apply (
				select top 1 MP.Person_Fio
				from v_EvnVKExpert (nolock)
				inner join v_MedServiceMedPersonal MSMP (nolock) on MSMP.MedServiceMedPersonal_id = v_EvnVKExpert.MedServiceMedPersonal_id
				inner join v_MedService MS (nolock) on MS.MedService_id = MSMP.MedService_id
				inner join v_MedPersonal MP (nolock) on MP.MedPersonal_id = MSMP.MedPersonal_id and MP.Lpu_id = MS.Lpu_id
				where v_EvnVKExpert.EvnVK_id = EvnVK.EvnVK_id and v_EvnVKExpert.ExpertMedStaffType_id = 1
			) predsed
			where E.EvnNotifyRegister_id = :id
			and E.PersonRegisterType_id = :PersonRegisterType_id
			and E.NotifyType_id = :NotifyType_id
			", $params
		);
	}
}