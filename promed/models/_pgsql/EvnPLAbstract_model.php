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
 * EvnPLAbstract_model - Модель абстрактного ТАП
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnPLBase
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property int $VizitCount
 * @property int $IsFinish
 * @property int $Person_Age
 */
abstract class EvnPLAbstract_model extends EvnAbstract_model
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
		$arr['setdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_IS_SP_PARAM,
			),
		);
		// чтобы не попали в правила при сохранении
		unset($arr['setdate']['type']);
		unset($arr['settime']['type']);
		$arr['pid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['vizitcount'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['person_age'] = array(
			'properties' => array(
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['isfinish'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPL_IsFinish',
			'label' => 'Случай закончен',
			'save' => 'trim',
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
		return 2;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnPLBase';
	}

}