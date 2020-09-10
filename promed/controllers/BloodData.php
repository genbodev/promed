<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* BloodData - контроллер 
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

class BloodData extends swController {
	/**
	 * Method description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadBloodDataViewForm' => array(
				array(
					'field' => 'BloodData_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}


	/**
	*  Выводит печатную форму
	*  Входящие данные: $_POST['BloodData_id']
	*  На выходе: JSON-строка
	*  Используется: форма электронной медицинской карты
	*/
	function loadBloodDataViewForm() {
		$data = $this->ProcessInputData('loadBloodDataViewForm', true, true);
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

		switch ($data['BloodData_id'])
		{
			case 80:
				$content = get_content('BloodData',90);
			break;
			case 81:
				$content = get_content('BloodData',91);
			break;
			default:
				$content = 'Печатная форма не найдена';
		}
		$val = <<<EOD
<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<title>Группа крови и резус-фактор</title></head>
<body>{$content}</body></html>
EOD;
		$val = toUTF($val);
		echo json_encode(array("success"=>true, "html" => $val));
		return true;
	}

}
