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
 * MorbusSimple_model - Модель "Простое заболевание"
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      12.2014
 *
 * Отношения объекта "Простое заболевание"
 * Morbus has one Evn 1:0..1 (Morbus.Evn_pid = Evn.Evn_id)
 * Morbus has one MorbusBase 1:1 (Morbus.MorbusBase_id = MorbusBase.MorbusBase_id)
 *
 * @property integer MorbusBase_id Общее заболевание
 * @property integer Evn_pid Учетный документ, в рамках которого было добавлено простое заболевание
 * @property integer Diag_id Диагноз (справочник МКБ-10)
 * @property integer MorbusKind_id Характер заболевания
 * @property string Name Описание
 * @property string Nick Краткое описание
 * @property datetime setDT Дата начала заболевания
 * @property datetime disDT Дата исхода заболевания
 * @property integer MorbusResult_id Результат (перечисление MorbusResult)
 *
 * @property MorbusCommon_model morbusCommon Общее заболевание
 */
class MorbusSimple_model extends swModel
{
	/**
	 * @var bool Требуется ли параметр pmUser_id для хранимки удаления
	 */
	protected $_isNeedPromedUserIdForDel = true;
	/**
	 * @var MorbusCommon_model
	 */
	private $_morbusCommon = null;

	/**
	 * @return MorbusCommon_model
	 */
	function getMorbusCommon()
	{
		if (empty($this->_morbusCommon)) {
			$this->load->model('MorbusCommon_model', 'morbusCommonSelf');
			$this->_morbusCommon = $this->morbusCommonSelf;
		}
		return $this->_morbusCommon;
	}

	/**
	 * @param MorbusCommon_model $model
	 */
	function setMorbusCommon(MorbusCommon_model $model)
	{
		$this->_morbusCommon = $model;
	}

    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_DELETE,
	    ));
    }

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'Morbus';
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'Morbus_id';
		$arr[self::ID_KEY]['label'] = 'Простое заболевание';
		unset($arr['code']);
		$arr['name']['alias'] = 'Morbus_Name';
		$arr['name']['label'] = 'Описание';
		$arr['name']['save'] = 'ban_percent|trim|max_length[100]';
		$arr['insdt']['alias'] = 'Morbus_insDT';
		$arr['upddt']['alias'] = 'Morbus_updDT';
		$arr['morbusbase_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MorbusBase_id',
			'label' => 'Общее заболевание',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['nick'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Morbus_Nick',
			'label' => 'Краткое описание',
			'save' => 'ban_percent|trim|max_length[100]',
			'type' => 'string'
		);
		$arr['morbuskind_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MorbusKind_id',
			'label' => 'Характер заболевания',
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
		$arr['evn_pid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Evn_pid',
			'label' => 'Учетный документ',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['setdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'Morbus_setDT',
			'applyMethod' => '_applySetDt',
			'label' => 'Дата начала заболевания',
			'save' => 'trim|required',
			'type' => 'date'
		);
		$arr['disdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'Morbus_disDT',
			'applyMethod' => '_applyDisDt',
			'label' => 'Дата исхода заболевания',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['morbusresult_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MorbusResult_id',
			'label' => 'Результат',
			'save' => 'trim',
			'type' => 'id'
		);
		return $arr;
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
			case self::SCENARIO_DELETE:
				// помимо идешника ещё нужны параметры
				$rules['Evn_id'] = array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор учетного документа',
					'rules' => 'required',
					'type' => 'id'
				);
				break;
		}
		return $rules;
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
			$this->_params['Evn_id'] = empty($data['Evn_id']) ? null : $data['Evn_id'];
		}
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applySetDt($data)
	{
		return $this->_applyDate($data, 'setdt');
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyDisDt($data)
	{
		return $this->_applyDate($data, 'disdt');
	}

	/**
	 * @throws Exception
	 */
	protected function _checkChangeDisDt()
	{
		// Дата окончания заболевания. Необязательный атрибут. Не может быть больше текущей даты и меньше даты начала заболевания.
		if (empty($this->disDT)) {
			$this->setAttribute('disdt', null);
			$this->setAttribute('morbusresult_id', null);
		} else {
			if (!is_object($this->setDT) || get_class($this->setDT) != 'DateTime') {
				throw new Exception('Неверный формат даты окончания простого заболевания', 500);
			}
			if ($this->disDT < $this->setDT){
				throw new Exception('Дата окончания простого заболевания наступила раньше даты начала простого заболевания');
			}
			// Исход заболевания. Обязательно для заполнения, если заполнен атрибут «Дата окончания» заболевания
			// пока поле не обязательное
			if (false && empty($this->MorbusResult_id)) {
				throw new Exception('Исход простого заболевания обязательно для заполнения, если указана дата окончания простого заболевания');
			}
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
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
		))) {
			if (empty($this->MorbusBase_id)) {
				throw new Exception('Не указано общее заболевание', 400);
			}
			if ($this->isNewRecord && empty($this->setDT)) {
				$this->setAttribute('setdt', $this->currentDT);
			}
			if (empty($this->setDT)) {
				throw new Exception('Не указана дата начала простого заболевания', 400);
			}
			if (!is_object($this->setDT) || get_class($this->setDT) != 'DateTime') {
				throw new Exception('Неверный формат даты начала простого заболевания', 500);
			}
			$this->_checkChangeDisDt();
		}

		if (self::SCENARIO_DELETE == $this->scenario) {
			if (empty($this->_params['Evn_id'])) {
				throw new Exception('При удалении необходимо указать, из какого учетного документа производится удаление заболевания', 400);
			}
			if ($this->Evn_pid != $this->_params['Evn_id']) {
				throw new Exception('Нельзя удалить это заболевание, поскольку оно создано из другого учетного документа', 400);
			}
			// нужно получить данные Evn
			$evnData = $this->getFirstRowFromQuery('
				select Evn.Person_id, ISNULL(PL.Diag_id,ST.Diag_id) as Diag_id
				from v_Evn Evn with (nolock)
				left join EvnVizitPL PL with (nolock) on Evn.Evn_id = PL.EvnVizitPL_id
				left join EvnSection ST with (nolock) on Evn.Evn_id = ST.EvnSection_id
				where Evn.Evn_id = :Evn_pid
				and Evn.EvnClass_id in (11,13,32)',
				array('Evn_pid' => $this->Evn_pid)
			);
			if (empty($evnData)) {
				throw new Exception('Не найден учетный документ, из которого производится удаление заболевания', 400);
			}
			if (empty($evnData['Person_id']) || empty($evnData['Diag_id'])) {
				throw new Exception('В учетном документе, из которого производится удаление заболевания, не указан человек или диагноз', 400);
			}
			$arr = $this->morbusCommon->loadMorbusList(array(
				'mode' => 'onBeforeDeleteEvn',
				'Person_id' => $evnData['Person_id'],
				'onlyOpen' => true,
				'diag_list' => array($evnData['Diag_id']),
				'evn_list' => array($this->Evn_pid),
			));
			$isOk = false;
			foreach ($arr as $row) {
				if (empty($row['Morbus_id']) || $row['Morbus_id'] != $this->id) {
					continue;
				}
				if (1 == $row['hasPersonRegister'] || 1 == $row['hasOtherEvn']) {
					throw new Exception('Удаление заболевания запрещено, т.к. по заболеванию создано извещение или запись регистра', 400);
				}
				$isOk = true;
				$this->_params['hasOtherMorbus'] = $row['hasOtherMorbus'];
			}
			/*if (false == $isOk) {
				throw new Exception('Не найдена связь по диагнозу учетного документа с заболеванием', 400);
			}*/
		}
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
		parent::_beforeSave($data);
		if (empty($this->setDT)) {
			$this->setAttribute('setdt', $this->currentDT);
		}
	}

	/**
	 * Логика после успешного выполнения запроса удаления объекта внутри транзакции
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterDelete($result)
	{
		if (empty($this->_params['hasOtherMorbus'])) {
			// нет открытых заболеваний данного типа, то общее заболевание удалять
			$queryParams = array();
			$queryParams['MorbusBase_id'] = $this->MorbusBase_id;
			$queryParams['pmUser_id'] = $this->promedUserId;
			$tmp = $this->execCommonSP('p_MorbusBase_del', $queryParams);
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса удаления записи из БД', 500);
			}
			if (isset($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
			}
		}
	}

	/**
	 * Открытие заболевания
	 * @param array $data
	 * @throws Exception
	 * @return array
	 */
	public function doOpen($data)
	{
		try {
			if (empty($data['Morbus_id'])) {
				throw new Exception('Не указан ключ объекта', 500);
			}
			$this->setScenario(self::SCENARIO_SET_ATTRIBUTE);
			$this->applyData($data);
			$this->setAttribute('disdt', null);
			$this->setAttribute('morbusresult_id', null);
			if ($this->_isAttributeChanged('disdt') || $this->_isAttributeChanged('morbusresult_id')) {
				// сохраняем только, если значение изменилось
				$this->_save();
			}
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			return $this->_saveResponse;
		}
		return $this->_saveResponse;
	}


	/**
	 * Закрытие заболевания
	 * @param array $data
	 * @throws Exception
	 * @return array
	 */
	public function doClose($data)
	{
		try {
			if (empty($data['Morbus_id'])) {
				throw new Exception('Не указан ключ объекта', 500);
			}
			if (empty($data['Morbus_disDT'])) {
				$data['Morbus_disDT'] = $this->currentDT->format('Y-m-d');
			}
			if (empty($data['MorbusResult_id'])) {
				//$data['MorbusResult_id'] = 2; // Завершение заболевания
			}
			$this->setScenario(self::SCENARIO_SET_ATTRIBUTE);
			$this->applyData($data);
			$this->_checkChangeDisDt();
			if ($this->_isAttributeChanged('disdt') || $this->_isAttributeChanged('morbusresult_id')) {
				// сохраняем только, если значение изменилось
				$this->_save();
			}
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
			$this->_saveResponse['Error_Code'] = $e->getCode();
			return $this->_saveResponse;
		}
		return $this->_saveResponse;
	}

	/**
	 * Открытие заболеваний при удалении записи регистра с причиной исключения
	 *
	 * Вызывается из swMorbus::onBeforeDeletePersonRegister
	 * @param array $data
	 * @throws Exception
	 */
	public function doOpenByList($data)
	{
		foreach ($data['MorbusIdList'] as $id) {
			$this->reset();
			$data['Morbus_id'] = $id;
			$tmp = $this->doOpen($data);
			if (false == empty($tmp['Error_Msg'])) {
				throw new Exception($tmp['Error_Msg'], $tmp['Error_Code']);
			}
		}
	}

	/**
	 * Обновление диагноза заболевания
	 * @param int $id
	 * @param int $value
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * Если транзакция была начата ранее, то нужно установить false
	 * @return array
	 * @throws Exception
	 */
	public function updateDiag_id($id, $value, $isAllowTransaction = true)
	{
		return $this->_updateAttribute($id, 'diag_id', $value, $isAllowTransaction);
	}

	/**
	 * Удаление нескольких специфик
	 *
	 * Перед вызовом метода должны быть выполнены проверки возможности удаления
	 * как в swMorbus::onBeforeDeleteEvn или как в swMorbus::onBeforeDeletePersonRegister
	 * @param array $data
	 * @throws Exception
	 */
	public function doDeleteByList($data)
	{
		$this->_params['session'] = $data['session'];
		foreach ($data['MorbusIdList'] as $id) {
			$queryParams = array();
			$queryParams[$this->primaryKey(false)] = $id;
			if ($this->_isNeedPromedUserIdForDel) {
				$queryParams['pmUser_id'] = $this->promedUserId;
			}
			// при вызове p_Morbus_del она удаляет также специфики по Morbus_id
			$tmp = $this->execCommonSP($this->deleteProcedureName(), $queryParams);
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса удаления записи из БД', 500);
			}
			if (isset($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
			}
		}
		if (empty($data['hasOtherMorbus'])) {
			$queryParams = array();
			$queryParams['MorbusBase_id'] = $data['MorbusBase_id'];
			if ($this->_isNeedPromedUserIdForDel) {
				$queryParams['pmUser_id'] = $this->promedUserId;
			}
			$tmp = $this->execCommonSP('p_MorbusBase_del', $queryParams);
			if (empty($tmp)) {
				throw new Exception('Ошибка запроса удаления записи из БД', 500);
			}
			if (isset($tmp[0]['Error_Msg'])) {
				throw new Exception($tmp[0]['Error_Msg'], $tmp[0]['Error_Code']);
			}
		}
	}

	/**
	 * Создание простого заболевания
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		$data['scenario'] = self::SCENARIO_AUTO_CREATE;
		$obj = new MorbusSimple_model();
		return $obj->doSave($data, $isAllowTransaction);
	}

	/**
	 * Устанавливает/удаляет связь между учетным документом и заболеванием.
	 *
	 * Обновление ссылки на заболевание в учетном документе разрешено, когда
	 * 	сохраняется специфика по заболеванию (onAfterSaveMorbusSpecific)
	 * 	создается извещение на включение в регистр по заболеванию (onAfterSaveEvnNotify)
	 * @param array $data Ключи Evn_id Morbus_id session mode
	 * @return array
	 */
	public function updateMorbusIntoEvn($data)
	{
		// Следует использовать этот метод вместо Morbus_model->evn_setMorbus
		if (empty($data['Evn_id'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], array(
				'onAfterSaveEvnNotify', 'onAfterSaveMorbusSpecific', 'onAfterSaveEvn'
			))
		) {
			// $data['mode'] in 'onAfterSaveEvn' 'onBeforePersonRegisterViewData' 'onBeforeEvnViewData'
			// если не занулять на 'onAfterSaveEvn' при смене диагноза будет подтягиваться старая
			// ничего не делаем
			return $this->_saveResponse;
		}
		$this->setParams($data);
		return $this->execCommonSP('p_Evn_setMorbus', array(
			'Evn_id' => $data['Evn_id'],
			'Morbus_id' => empty($data['Morbus_id']) ? null : $data['Morbus_id'] ,
			'pmUser_id' => $this->promedUserId,
		), 'array_assoc');
	}
}