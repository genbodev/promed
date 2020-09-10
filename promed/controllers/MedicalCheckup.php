<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MedicalCheckup - контроллер 
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

class MedicalCheckup extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadMedicalCheckupViewForm' => array(
				array(
					'field' => 'MedicalCheckup_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}


	/**
	*  Выводит печатную форму
	*  Входящие данные: $_POST['MedicalCheckup_id']
	*  На выходе: JSON-строка
	*  Используется: форма электронной медицинской карты
	*/
	function loadMedicalCheckupViewForm() {
		$data = $this->ProcessInputData('loadMedicalCheckupViewForm', true, true);
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

		$title = 'Осмотр специалиста';
		switch ($data['MedicalCheckup_id'])
		{
			case 70:
				$content = get_content('MedicalCheckup',70);
			break;
			case 71:
				$content = get_content('MedicalCheckup',71);
			break;
			case 72:
				$content = get_content('MedicalCheckup',72);
			break;
			case 74:
				$content = get_content('MedicalCheckup',74);
			break;
			case 75:
				$content = get_content('MedicalCheckup',75);
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
