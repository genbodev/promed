<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnAbstract_model.php');
/**
 * EvnLeaveAbstract_model - Модель "Базовая выписка из стационара"
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnLeaveBase
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      09.2014
 *
 * @property-read int $rid КВС
 * @property-read int $pid Движение в отделении (профильном или приемном) или КВС (Движение в приемном отделение)
 * @property-read int $LeaveCause_id Причина выписки
 * @property-read int $ResultDesease_id Результат заболевания
 * @property-read float $UKL Уровень качества лечения EvnLeaveBase_UKL
 * @property-read int $PrehospWaifRetired_id Выбыл (Беспризорный)
 *
 * @property-read EvnSection_model $parent Или EvnPS_model
 */
abstract class EvnLeaveAbstract_model extends EvnAbstract_model
{
	protected $_parentClass = 'EvnSection_model';

    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
	    $this->_setScenarioList(array(
		    self::SCENARIO_LOAD_EDIT_FORM,
		    self::SCENARIO_AUTO_CREATE,
		    self::SCENARIO_DO_SAVE,
		    self::SCENARIO_DELETE,
	    ));
    }

	/**
	 * @return mixed
	 * @throws Exception
	 */
	function getParent()
	{
		if ($this->pid == $this->rid) {
			$this->_parentClass = 'EvnPS_model';
			throw new Exception(var_export($this));
		} else {
			$this->_parentClass = 'EvnSection_model';
		}
		return parent::getParent();
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
		$this->_params['needCheckEvnSectionLast'] = empty($data['needCheckEvnSectionLast']) ? null : $data['needCheckEvnSectionLast'];
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if ( empty($this->UKL) ) {
				throw new Exception('Не указан уровень качества лечения');
			}
			if ( $this->UKL <= 0 || $this->UKL > 1 ) {
				throw new Exception('Неверное значение уровня качества лечения');
			}
			if (empty($this->ResultDesease_id) && in_array($this->evnClassId, array(39,40,41,42))) {
				throw new Exception('Не указан исход заболевания', 400);
			}

			//Т.к. на Хакасии нет причины выписки - смерть, то определяем по результату госпитализации
			$noNeedLeaveCause = true;
			if ($this->getRegionNick() == 'khak'){
				$ResultDesease_SysNick = $this->getFirstResultFromQuery('select top 1 ResultDesease_SysNick from ResultDesease with(nolock) where ResultDesease_id = :ResultDesease_id', array('ResultDesease_id' => $this->ResultDesease_id));

				if ($ResultDesease_SysNick == 'umer') {
					$noNeedLeaveCause = false;
				}
			}

			if (empty($this->LeaveCause_id) && in_array($this->parent->leaveTypeSysNick, array(
				'leave','ksleave','dsleave'
			)) && $noNeedLeaveCause) {
				throw new Exception('Не указана причина выписки', 400);
			}
			if (empty($this->LeaveCause_id) && in_array($this->parent->leaveTypeSysNick, array(
				'other'
			)) && $noNeedLeaveCause) {
				throw new Exception('Не указана причина перевода', 400);
			}
		}
		if (self::SCENARIO_DELETE == $this->scenario && isset($this->_params['needCheckEvnSectionLast'])) {
			$result = $this->getFirstResultFromQuery('
				SELECT top 1 isnull(ES.EvnSection_id, ELB.EvnLeaveBase_pid) as EvnSection_id
				FROM v_EvnLeaveBase ELB with (nolock)
				outer apply (
					select top 1 ES.EvnSection_id
					from v_EvnSection ES with (nolock)
					where ES.EvnSection_pid = ELB.EvnLeaveBase_rid
					order by ES.EvnSection_Index desc
				) ES
				WHERE ELB.EvnLeaveBase_id = :id
			', array(
				'id' => $this->id,
			));
			if (false === $result) {
				throw new Exception('Не удалось выполнить проверку перед удалением исхода!');
			}
			if ($result != $this->pid) {
				throw new Exception('Нельзя удалить исход, т.к. имеется следующее движение');
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
		/*if (false == in_array($this->parent->leaveTypeSysNick, array(
			'leave','ksleave','dsleave'
		))) {
			$this->setAttribute('leavecause_id', null);
		}*/
		//Инициализация хелпера рассылки сообщений о смене статуса
		$this->personNoticeEvn->setEvnClassSysNick($this->evnClassSysNick);
		$this->personNoticeEvn->setEvnId($this->id);
		$this->personNoticeEvn->doStatusSnapshotFirst();

		// Для 1 родителя должен быть 1 исход, поэтому если id не задан, ищем в БД по родителю, если есть фэйлимся
		if (empty($this->id)) {
			// ищем ид по родителю
			$query = "
				select
					EvnLeaveBase_id
				from
					v_EvnLeaveBase (nolock)
				where
					EvnLeaveBase_pid = :EvnLeaveBase_pid
			";

			$resp = $this->queryResult($query, array(
				'EvnLeaveBase_pid' => $this->pid
			));

			if (!empty($resp[0]['EvnLeaveBase_id'])) {
				if (isset($data['scenario']) && $data['scenario'] == 'autoCreate') {
					$this->id = $resp[0]['EvnLeaveBase_id'];
				} else {
					throw new Exception('Исход уже сохранён, дублирование исходов не возможно');
				}
			}
		}
	}

	/**
	 * Логика после успешного сохранения объекта
	 * Все изменения уже доступны для чтения из БД.
	 * Тут нельзя выбрасывать исключения, т.к. возможно была вложенная транзакция!
	 */
	protected function _onSave()
	{
		$this->personNoticeEvn->setEvnId($this->id);
		$this->personNoticeEvn->doStatusSnapshotSecond();
		$this->personNoticeEvn->processStatusChange();
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr['setdate']['label'] = 'Дата исхода';
		$arr['settime']['label'] = 'Время исхода';
		$arr['leavecause_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LeaveCause_id',
			'label' => 'Причина выписки',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['resultdesease_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultDesease_id',
			'label' => 'Результат заболевания',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['prehospwaifretired_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PrehospWaifRetired_id',
			'label' => 'Выбыл (Беспризорный)',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['ukl'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => '_UKL',
			'label' => 'УКЛ',
			'save' => 'trim',
			'type' => 'float'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 34;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnLeaveBase';
	}

	/**
	 * Получение данных для формы
	 */
	function doLoadEditForm($data)
	{
		$data['scenario'] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return array(array(
			$this->tableName() . '_id' => $this->id,
			$this->tableName() . '_pid' => $this->pid,
			$this->tableName() . '_setDate' => $this->setDT->format('d.m.Y'),
			$this->tableName() . '_setTime' => $this->setTime,
			$this->tableName() . '_UKL' => round($this->UKL, 3),
			'LeaveCause_id' => $this->LeaveCause_id,
			'ResultDesease_id' => $this->ResultDesease_id,
			'Server_id' => $this->Server_id,
			'Person_id' => $this->Person_id,
			'PersonEvn_id' => $this->PersonEvn_id,
		));
	}

	/**
	 * Получение данных для копии
	 */
	function doLoadCopyData($data)
	{
		$this->applyData($data);
		if (empty($data[$this->primaryKey()]) && isset($this->pid)) {
			$data[$this->primaryKey()] = $this->getIdByPid($this->pid);
		}
		if (empty($data[$this->primaryKey()])) {
			throw new Exception('Не удалось определить идентификатор объекта');
		}
		$this->setAttributes($data);
		return array(
			'EvnLeave_UKL' => $this->UKL,
			'LeaveCause_id' => $this->LeaveCause_id,
			'ResultDesease_id' => $this->ResultDesease_id,
			'PrehospWaifRetired_id' => $this->PrehospWaifRetired_id,
		);
	}

	/**
	 *  Получение идентификатора случая исхода госпитализации
	 */
	function getIdByRid($rid)
	{
		return $this->getFirstResultFromQuery('
			SELECT ELB.EvnLeaveBase_id
			FROM v_EvnLeaveBase ELB with (nolock)
			WHERE ELB.EvnLeaveBase_rid = :rid
		', array(
			'rid' => $rid,
		));
	}

	/**
	 * Получение идентификатора случая исхода госпитализации
	 */
	function getIdByPid($pid)
	{
		return $this->getFirstResultFromQuery('
			SELECT top 1 ELB.EvnLeaveBase_id
			FROM v_EvnLeaveBase ELB with (nolock)
			WHERE ELB.EvnLeaveBase_pid = :pid
		', array(
			'pid' => $pid,
		));
	}
}