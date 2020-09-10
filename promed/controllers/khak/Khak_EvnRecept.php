<?php defined('BASEPATH') or die('No direct script access allowed');
/**
* Person - контроллер для работы с рецептами.
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
 
class Khak_EvnRecept extends EvnRecept {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	
	/**
	*  Сохранение рецепта
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
