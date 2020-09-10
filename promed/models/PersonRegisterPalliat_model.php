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
 * Модель объектов "Запись регистра по паллиативной помощи"
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Александр Пермяков
 * @version      03.2015
 *
 * @property string $сode № регистровой записи. Целое число, 13
 *
 * @property-read PMMediaData_model $PMMediaData_model
 * @property-read EvnNotifyRegister_model $EvnNotifyRegister_model
 */
class PersonRegisterPalliat_model extends PersonRegisterBase_model
{
	protected $_personRegisterTypeSysNick = 'palliat'; // всегда перекрывать
	protected $_morbusTypeSysNick = 'palliat'; // всегда перекрывать
	protected $_userGroupCode = 'RegistryPalliatCare'; // можно не перекрывать, если задано стандартно, например "PalliatRegistry" для типа регистра "Palliat"
	protected $_PersonRegisterType_id = 64; // если не для всех регионов, то нельзя перекрывать

	/**
	 * @param array $data
	 */
	protected function _beforeSave($data = array()) {
		$this->load->library('swMorbus');

		$data['Morbus_setDT'] = $data['PersonRegister_setDate'];

		parent::_beforeSave($data);

		$tmp = swMorbus::checkByPersonRegister($this->_morbusTypeSysNick, $data, 'onBeforeSavePersonRegister');

		$this->setAttribute('Morbus_id', $tmp['Morbus_id']);
		$this->setAttribute('MorbusType_id', $tmp['MorbusType_id']);
	}
}