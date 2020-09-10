<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * Person - контроллер для работы с рецептами. Версия для Пскова
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @author		Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version		31.05.2013
 */

require_once(APPPATH.'controllers/EvnRecept.php');

class Pskov_EvnRecept extends EvnRecept {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules['getReceptNumber'] = array(
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
	 *  Получение номера рецепта для Пскова
	 *  Входящие данные: нет
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования рецепта
	 */
	function getReceptNumber($returnValue = false, $convertFromUTF8 = true) {
		$data = $this->ProcessInputData('getReceptNumber', true, true, false, false, $convertFromUTF8);
		if ( $data === false ) { return false; }

		$val = array();

		// Для Пскова
		if ( $data['session']['region']['number'] != 60 ) {
			$this->ReturnError('Несоответствие региона и используемой формы!');
			return false;
		}

		$result = $this->dbmodel->getReceptNumber($data);

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


	/**
	 *  Сохранение рецепта для Пскова
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования рецепта
	 */
	function saveEvnRecept($recept_number = null, $convertFromUTF8 = true) {
		$data = $this->ProcessInputData('saveEvnRecept', true);
		if ($data === false) { return false; }

		$recept_number = $this->getReceptNumber(true, false);

		if ( !empty($recept_number) ) {
			// сохраняем рецепт
			return parent::saveEvnRecept($recept_number, false);
		}
		else {
			return false;
		}
	}

}

?>
