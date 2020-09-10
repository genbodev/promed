<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
require_once('EvnVizitPL_model.php');
/**
 * EvnVizitPLWOW_model - Модель посещения ВОВ
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnVizitPLStom
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property int $DispWowSpec_id Специальность врача
 */
class EvnVizitPLWOW_model extends EvnVizitPL_model
{
    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr['dispwowspec_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DispWowSpec_id',
			'label' => 'Специальность врача',
			'save' => 'trim|required',
			'type' => 'id'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 36;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnVizitPLWOW';
	}
	/**
	 * Сохранение фактического времени посещения,
	 * если без записи, то создается бирка "без записи" с факт. временем посещения
	 * @throws Exception
	 */
	protected function _saveVizitFactTime()
	{
		// тут этого не надо
	}

	/**
	 * Обязательность основного диагноза посещения
	 * @return bool
	 */
	protected function _isRequiredDiag()
	{
		return in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE));
	}

	/**
	 * Проверка двойственности посещений пациентов
	 * @throws Exception
	 */
	protected function _controlDoubleVizit()
	{
		$isAllowControlDoubleVizit = $this->scenario == self::SCENARIO_DO_SAVE && $this->isNewRecord;
		//В соответствие с задачей 11179 необходимо проверить сохраняемый осмотр врача-специалиста на дублирование
		if ($isAllowControlDoubleVizit) {
			$params = array();
			$params['Lpu_id'] = $this->Lpu_id;
			$params['EvnPL_id'] = $this->pid;
			$params['DispWowSpec_id'] = $this->DispWowSpec_id;
			$result = $this->getFirstResultFromQuery("
				select count(EvnVizitPLWOW_id) as cnt
				from v_EvnVizitPLWOW with (nolock)
				where EvnVizitPLWOW_pid = :EvnPL_id
				and DispWowSpec_id = :DispWowSpec_id
				and Lpu_id = :Lpu_id
				", $params);
			if (false === $result) {
				throw new Exception('Ошибка при контроле двойственности осмотров врача-специалиста', 500);
			}
			if ($result > 0) {
				throw new Exception('Осмотр этого врача-специалиста уже заведен.', 400);
			}
		}
	}
}