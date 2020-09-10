<?php	defined('BASEPATH') or die ('No direct script access allowed');
require_once('MorbusNephroLab_model.php');
/**
 * MorbusNephroDisp_model - модель "Динамическое наблюдение" регистра по нефрологии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      11.2014
 */
class MorbusNephroDisp_model extends MorbusNephroLab_model
{
	/**
	 * @return string
	 */
	function getObjectSysNick()
	{
		return 'MorbusNephroDisp';
	}
	/**
	 * Method description
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение Лабораторные исследования или Динамическое наблюдение
	 * @return int
	 */
	function getIsDinamic()
	{
		return 2;
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'MorbusNephroDisp_id';
		$arr['ratedt']['alias'] = 'MorbusNephroDisp_Date';
		return $arr;
	}
}