<?php defined('BASEPATH') or die('No direct script access allowed');
/**
* Person - контроллер для управления людьми. Версия для Перми
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2013 Swan Ltd.
* @author		Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version		27.05.2013
*/

require_once(APPPATH.'controllers/Person.php');
 
class Perm_Person extends Person {

	/**
	 * Дополнительные проверки при сохранении
	 * Для Перми для полиса единого образца проверяется поле Единый номер
	 */
	function validatePersonSaveRegional($data)
	{
        if ( isset($data['PolisType_id']) && $data['PolisType_id'] == 4 && empty($data['Federal_Num'])) {
			return 'Для полиса единого образца необходимо заполнить поле "Единый номер".';
		}
        return true;
	}

}

?>
