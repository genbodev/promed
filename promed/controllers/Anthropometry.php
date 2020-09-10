<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Anthropometry - контроллер 
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

class Anthropometry extends swController {
	/**
	 * Method description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadAnthropometryViewForm' => array(
				array(
					'field' => 'Anthropometry_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}


	/**
	*  Выводит печатную форму
	*  Входящие данные: $_POST['Anthropometry_id']
	*  На выходе: JSON-строка
	*  Используется: форма электронной медицинской карты
	*/
	function loadAnthropometryViewForm() {
		$data = $this->ProcessInputData('loadAnthropometryViewForm', true, true);
		if ( $data === false )
		{
			 return false;
		}
		/**
		 * Function description
		 */
		function get_content($object,$id)
		{
			$content = file_get_contents ('./promed/demodata/'.$object.'/'.$id.'.txt');
			if (empty($content))
				$content = '';
			return $content;
		}

		switch ($data['Anthropometry_id'])
		{
			case 80:
				$content = get_content('Anthropometry',70);
			break;
			case 81:
				$content = get_content('Anthropometry',71);
			break;
			default:
				$content = 'Печатная форма не найдена';
		}
		$val = <<<EOD
<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<title>Антропометрические данные</title></head>
<body>{$content}</body></html>
EOD;
		$val = toUTF($val);
		echo json_encode(array("success"=>true, "html" => $val));
		return true;
	}

}
