<?php defined('BASEPATH') or die('No direct script access allowed');

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('PersonRegisterBase_model.php');
/**
 * Модель объектов "Запись регистра по суицидам"
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Александр Чебукин
 * @version      07.2016
 *
 * @property string $сode № регистровой записи. Целое число, 13
 *
 * @property-read PMMediaData_model $PMMediaData_model
 * @property-read EvnNotifyRegister_model $EvnNotifyRegister_model
 */
class PersonRegisterSuicide_model extends PersonRegisterBase_model
{
	protected $_personRegisterTypeSysNick = 'suicide'; // всегда перекрывать
	protected $_userGroupCode = 'SuicideRegistry'; // можно не перекрывать, если задано стандартно, например "SuicideRegistry" для типа регистра "suicide"
	protected $_PersonRegisterType_id = 62; // если не для всех регионов, то нельзя перекрывать
	//protected $_exportLimit = 3145728; // 3 Мб, рекомендуется создавать файлы не больше 2-3 Мб, но не более 8

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['label'] = 'Запись регистра по суицидам';
		$arr['diag_id']['save'] = 'trim|required';
		$arr['isresist']['save'] = '';
		return $arr;
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
	}
}
