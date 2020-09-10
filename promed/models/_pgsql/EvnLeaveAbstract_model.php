<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnAbstract_model.php");
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 * 
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
	protected $_parentClass = "EvnSection_model";

	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList([
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
		]);
	}

	/**
	 * @return EvnAbstract_model
	 * @throws Exception
	 */
	function getParent()
	{
		if ($this->pid == $this->rid) {
			$this->_parentClass = "EvnPS_model";
			throw new Exception(var_export($this));
		}
		$this->_parentClass = "EvnSection_model";
		return parent::getParent();
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров, переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		$this->_params["needCheckEvnSectionLast"] = empty($data["needCheckEvnSectionLast"]) ? null : $data["needCheckEvnSectionLast"];
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, [self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE])) {
			if (empty($this->UKL)) {
				throw new Exception("Не указан уровень качества лечения");
			}
			if ($this->UKL <= 0 || $this->UKL > 1) {
				throw new Exception("Неверное значение уровня качества лечения");
			}
			if (empty($this->ResultDesease_id) && in_array($this->evnClassId, [39, 40, 41, 42])) {
				throw new Exception("Не указан исход заболевания", 400);
			}
			//Т.к. на Хакасии нет причины выписки - смерть, то определяем по результату госпитализации
			$noNeedLeaveCause = true;
			if ($this->getRegionNick() == "khak") {
				$query = "
					select ResultDesease_SysNick as \"ResultDesease_SysNick\"
					from ResultDesease
					where ResultDesease_id = :ResultDesease_id
					limit 1
				";
				$queryParams = ["ResultDesease_id" => $this->ResultDesease_id];
				$ResultDesease_SysNick = $this->getFirstResultFromQuery($query, $queryParams);
				if ($ResultDesease_SysNick == "umer") {
					$noNeedLeaveCause = false;
				}
			}
			if (empty($this->LeaveCause_id) && in_array($this->parent->leaveTypeSysNick, ["leave", "ksleave", "dsleave"]) && $noNeedLeaveCause) {
				throw new Exception("Не указана причина выписки", 400);
			}
			if (empty($this->LeaveCause_id) && in_array($this->parent->leaveTypeSysNick, ["other"]) && $noNeedLeaveCause) {
				throw new Exception("Не указана причина перевода", 400);
			}
		}
		if (self::SCENARIO_DELETE == $this->scenario && isset($this->_params["needCheckEvnSectionLast"])) {
			$query = "
				select coalesce(ES.EvnSection_id, ELB.EvnLeaveBase_pid) as \"EvnSection_id\"
				from v_EvnLeaveBase ELB
				left join lateral (
					select ES.EvnSection_id
					from v_EvnSection ES
					where ES.EvnSection_pid = ELB.EvnLeaveBase_rid
					order by ES.EvnSection_Index desc
					limit 1
				) as ES on true
				where ELB.EvnLeaveBase_id = :id
				limit 1
			";
			$queryParams = ["id" => $this->id];
			$result = $this->getFirstResultFromQuery($query, $queryParams);
			if (false === $result) {
				throw new Exception("Не удалось выполнить проверку перед удалением исхода!");
			}
			if ($result != $this->pid) {
				throw new Exception("Нельзя удалить исход, т.к. имеется следующее движение");
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data
	 * @throws Exception
	 */
	protected function _beforeSave($data = [])
	{
		parent::_beforeSave($data);
		//Инициализация хелпера рассылки сообщений о смене статуса
		$this->personNoticeEvn->setEvnClassSysNick($this->evnClassSysNick);
		$this->personNoticeEvn->setEvnId($this->id);
		$this->personNoticeEvn->doStatusSnapshotFirst();

		// Для 1 родителя должен быть 1 исход, поэтому если id не задан, ищем в БД по родителю, если есть фэйлимся
		if (empty($this->id)) {
			// ищем ид по родителю
			$query = "
				select EvnLeaveBase_id as \"EvnLeaveBase_id\"
				from v_EvnLeaveBase
				where EvnLeaveBase_pid = :EvnLeaveBase_pid
			";
			$queryParams = ["EvnLeaveBase_pid" => $this->pid];
			$result = $this->queryResult($query, $queryParams);
			if (!empty($result[0]["EvnLeaveBase_id"])) {
				if (!isset($data["scenario"]) || $data["scenario"] != "autoCreate") {
					throw new Exception("Исход уже сохранён, дублирование исходов невозможно");
				}
				$this->id = $result[0]["EvnLeaveBase_id"];
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
		$arr["setdate"]["label"] = "Дата исхода";
		$arr["settime"]["label"] = "Время исхода";
		$arr["leavecause_id"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "LeaveCause_id",
			"label" => "Причина выписки",
			"save" => "trim",
			"type" => "id"
		];
		$arr["resultdesease_id"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "ResultDesease_id",
			"label" => "Результат заболевания",
			"save" => "trim",
			"type" => "id"
		];
		$arr["prehospwaifretired_id"] = [
			"properties" => [self::PROPERTY_IS_SP_PARAM],
			"alias" => "PrehospWaifRetired_id",
			"label" => "Выбыл (Беспризорный)",
			"save" => "trim",
			"type" => "id"
		];
		$arr["ukl"] = [
			"properties" => [self::PROPERTY_NEED_TABLE_NAME, self::PROPERTY_IS_SP_PARAM],
			"alias" => "_UKL",
			"label" => "УКЛ",
			"save" => "trim",
			"type" => "float"
		];
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
		return "EvnLeaveBase";
	}

	/**
	 * Получение данных для формы
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadEditForm($data)
	{
		$data["scenario"] = self::SCENARIO_LOAD_EDIT_FORM;
		$this->applyData($data);
		$this->_validate();
		return [[
			$this->tableName() . "_id" => $this->id,
			$this->tableName() . "_pid" => $this->pid,
			$this->tableName() . "_setDate" => $this->setDT->format("d.m.Y"),
			$this->tableName() . "_setTime" => $this->setTime,
			$this->tableName() . "_UKL" => round($this->UKL, 3),
			"LeaveCause_id" => $this->LeaveCause_id,
			"ResultDesease_id" => $this->ResultDesease_id,
			"Server_id" => $this->Server_id,
			"Person_id" => $this->Person_id,
			"PersonEvn_id" => $this->PersonEvn_id,
		]];
	}

	/**
	 * Получение данных для копии
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function doLoadCopyData($data)
	{
		$this->applyData($data);
		if (empty($data[$this->primaryKey()]) && isset($this->pid)) {
			$data[$this->primaryKey()] = $this->getIdByPid($this->pid);
		}
		if (empty($data[$this->primaryKey()])) {
			throw new Exception("Не удалось определить идентификатор объекта");
		}
		$this->setAttributes($data);
		return [
			"EvnLeave_UKL" => $this->UKL,
			"LeaveCause_id" => $this->LeaveCause_id,
			"ResultDesease_id" => $this->ResultDesease_id,
			"PrehospWaifRetired_id" => $this->PrehospWaifRetired_id,
		];
	}

	/**
	 *  Получение идентификатора случая исхода госпитализации
	 * @param $rid
	 * @return bool|float|int|string
	 */
	function getIdByRid($rid)
	{
		$query = "
			select ELB.EvnLeaveBase_id as \"EvnLeaveBase_id\"
			from v_EvnLeaveBase ELB
			where ELB.EvnLeaveBase_rid = :rid
		";
		$queryParams = ["rid" => $rid];
		return $this->getFirstResultFromQuery($query, $queryParams);
	}

	/**
	 * Получение идентификатора случая исхода госпитализации
	 * @param $pid
	 * @return bool|float|int|string
	 */
	function getIdByPid($pid)
	{
		$query = "
			select ELB.EvnLeaveBase_id as \"EvnLeaveBase_id\"
			from v_EvnLeaveBase ELB
			where ELB.EvnLeaveBase_pid = :pid
			limit 1
		";
		$queryParams = ["pid" => $pid];
		return $this->getFirstResultFromQuery($query, $queryParams);
	}
}