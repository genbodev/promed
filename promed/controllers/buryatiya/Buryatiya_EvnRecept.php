<?php defined('BASEPATH') or die('No direct script access allowed');
/**
* Person - контроллер для работы с рецептами. Версия для Бурятии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access		public
* @copyright	Copyright (c) 2013 Swan Ltd.
* @author		Bykov Stanislav (savage@swan.perm.ru)
* @version		15.10.2014
*/

require_once(APPPATH.'controllers/EvnRecept.php');
 
class Buryatiya_EvnRecept extends EvnRecept {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules['getReceptNumberRls'] = array(
			array(
				'field' => 'WhsDocumentCostItemType_id',
				'label' => 'Программа ЛЛО',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnRecept_setDate',
				'label' => 'Дата выписки рецепта',
				'rules' => 'trim|required',
				'type' => 'date'
			)
		);
	}
	
	/**
	*  Получение номера рецепта для Бурятии
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function getReceptNumberRls($returnValue = false, $convertFromUTF8 = true) {
		$data = $this->ProcessInputData('getReceptNumberRls', true, true, false, false, $convertFromUTF8);
		if ( $data === false ) { return false; }

		$val = array();

		$result = $this->dbmodel->getReceptNumberRls($data);

		if ( is_array($result) && count($result) > 0 ) {
			$val['EvnRecept_Num'] = $result[0]['rnumber'];
		}

		if ( $returnValue === true ) {
			return $val['EvnRecept_Num'];
		}
		else {
			$this->ReturnData($val);
		}

		return true;
	}
}
