<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* ProtocolSurgery - контроллер 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       SWAN developers
* @version      04.02.2011
*/

class ProtocolSurgery extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadProtocolSurgeryViewForm' => array(
				array(
					'field' => 'ProtocolSurgery_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}


	/**
	*  Выводит печатную форму
	*  Входящие данные: $_POST['ProtocolSurgery_id']
	*  На выходе: JSON-строка
	*  Используется: форма электронной медицинской карты
	*/
	function loadProtocolSurgeryViewForm() {
		$data = $this->ProcessInputData('loadProtocolSurgeryViewForm', true, true);
		if ( $data === false )
		{
			 return false;
		}
		
		/**
		 * Description
		 */
		function get_content($object,$id)
		{
			$content = file_get_contents ('./promed/demodata/'.$object.'/'.$id.'.txt');
			if (empty($content))
				$content = '';
			return $content;
		}

		$title = 'ПРОТОКОЛ ОПЕРАЦИИ';
		switch ($data['ProtocolSurgery_id'])
		{
			case 68:
				$content = get_content('ProtocolSurgery',68);
			break;
			case 75:
				$content = get_content('ProtocolSurgery',75);
			break;
			case 76:
				$content = get_content('ProtocolSurgery',76);
			break;
			default:
				$content = 'Печатная форма не найдена';
		}
		$val = <<<EOD
<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<title>{$title}</title></head>
<body>{$content}</body></html>
EOD;
		$val = toUTF($val);
		echo json_encode(array("success"=>true, "html" => $val));
		return true;
	}

}
