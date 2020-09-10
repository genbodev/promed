<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Epicrisis - контроллер 
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

class Epicrisis extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadEpicrisisViewForm' => array(
				array(
					'field' => 'Epicrisis_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}


	/**
	*  Выводит печатную форму
	*  Входящие данные: $_POST['Epicrisis_id']
	*  На выходе: JSON-строка
	*  Используется: форма электронной медицинской карты
	*/
	function loadEpicrisisViewForm() {
		$data = $this->ProcessInputData('loadEpicrisisViewForm', true, true);
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

		switch ($data['Epicrisis_id'])
		{
			case 80:
				$title = 'Ангиографический эпикриз';
				$content = get_content('Epicrisis',80);
			break;
			case 79:
				$title = 'Предоперационный эпикриз';
				$content = get_content('Epicrisis',79);
			break;
			case 78:
				$title = 'Предоперационный эпикриз';
				$content = get_content('Epicrisis',78);
			break;
			case 81:
				$title = 'Ангиографический эпикриз';
				$content = get_content('Epicrisis',81);
			break;
			case 82:
				$title = 'Предоперационный эпикриз';
				$content = get_content('Epicrisis',82);
			break;
			case 83:
				$title = 'Выписной эпикриз';
				$content = get_content('Epicrisis',83);
			break;
			default:
				$title = 'Печатная форма не найдена';
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
