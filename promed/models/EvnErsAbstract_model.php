<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnErsAbstract_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 */
 
require_once('EvnAbstract_model.php');

abstract class EvnErsAbstract_model extends EvnAbstract_model {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes() {
		$arr = parent::defAttributes();
		$arr['ersstatus_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM, self::PROPERTY_NOT_SAFE,
			),
			'alias' => 'ERSStatus_id',
		);
		$arr['lpufsscontract_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuFSSContract_id',
		);
		return $arr;
	}
}