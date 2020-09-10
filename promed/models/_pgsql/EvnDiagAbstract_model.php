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
 * EvnDiagAbstract_model - Модель "Установка диагноза"
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnDiag
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      09.2014
 *
 * @property-read int $rid КВС или ТАП
 * @property-read int $pid Движение в отделении или посещение
 * @property-read int $Diag_id Диагноз МКБ-10
 * @property-read int $DiagSetClass_id Класс диагноза
 *
 *
 * @property-read int $diagSetClassOsnId Класс диагноза - Основной osn
 * @property-read int $diagSetClassOslId Класс диагноза - Осложнение основного osl
 * @property-read int $diagSetClassSopId Класс диагноза - Сопутствующий	sop
 * @property-read int $diagSetClassUtcId Класс диагноза - Уточненный utc
17	16	1	Основной диагноз в поликлинике	EvnDiagPLOsn DiagSetClass_id in (1)
19	17	1	Основной диагноз в стоматологии	EvnDiagPLStom DiagSetClass_id in (1)
18	16	1	Сопутствующий диагноз в поликлинике	EvnDiagPLSop DiagSetClass_id = 3
99	18	1	Сопутствующий диагноз по стоматологии	EvnDiagPLStomSop DiagSetClass_id = 3
33	16	1	Установка диагноза в стационаре	EvnDiagPS DiagSetClass_id in (1,2,3)
102	16	1	Диагноз по диспансеризации	EvnDiagDopDisp
118	16	1	Уточненный диагноз	EvnDiagSpec DiagSetClass_id = 4
 */
abstract class EvnDiagAbstract_model extends EvnAbstract_model
{
	/**
	 * @return int
	 */
	function getDiagSetClassOsnId()
	{
		return 1;
	}
	/**
	 * @return int
	 */
	function getDiagSetClassOslId()
	{
		return 2;
	}
	/**
	 * @return int
	 */
	function getDiagSetClassSopId()
	{
		return 3;
	}
	/**
	 * @return int
	 */
	function getDiagSetClassUtcId()
	{
		return 4;
	}

    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if ( empty($this->pid) ) {
				throw new Exception('Не указан родительский учетный документ');
			}
			if ( empty($this->Diag_id) ) {
				throw new Exception('Не указан диагноз МКБ-10');
			}
			if ( empty($this->DiagSetClass_id) ) {
				throw new Exception('Не указан класс диагноза');
			}
		}
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr['setdate']['label'] = 'Дата установки диагноза';
		$arr['settime']['label'] = 'Время установки диагноза';
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Диагноз МКБ-10',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['diagsetclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetClass_id',
			'label' => 'Класс диагноза',
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
		return 16;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnDiag';
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
			'Diag_id' => $this->Diag_id,
			'DiagSetClass_id' => $this->DiagSetClass_id,
			'Server_id' => $this->Server_id,
			'Person_id' => $this->Person_id,
			'PersonEvn_id' => $this->PersonEvn_id,
		));
	}
}