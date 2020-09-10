<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */
require_once(APPPATH.'models/EvnPL_model.php');
/**
 * Pskov_EvnPL_model - Лечение в поликлинике (Псков)
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Станислав Быков
 * @version      03.2015
 */
class Pskov_EvnPL_model extends EvnPL_model
{
	/**
	 * @var string
	 */
	public $resultClassFieldLabel = 'Результат обращения';

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr['resultclass_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'ResultClass_id',
			'label' => 'Результат обращения',
			'save' => 'trim',
			'type' => 'id'
		);
		return $arr;
	}
}