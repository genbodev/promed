<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Research - контроллер 
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

class Research extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadResearchViewForm' => array(
				array(
					'field' => 'Research_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				/*),
				array(
					'field' => 'ResearchType_id',
					'label' => 'Идентификатор типа',
					'rules' => 'required',
					'type' => 'id'*/
				)
			)
		);
	}


	/**
	*  Выводит печатную форму
	*  Входящие данные: $_POST['Research_id']
	*  На выходе: JSON-строка
	*  Используется: форма электронной медицинской карты
	*/
	function loadResearchViewForm() {
		$data = $this->ProcessInputData('loadResearchViewForm', true, true);
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

		$title = 'Исследование';
		$content = 'Печатная форма не найдена';
		if ( in_array($data['Research_id'], array(1543,1912,1995,93,966,75,4747,3381,3299,5726,4997,2045,2134,3205,3163,3557,2556,5401,3560,5412,5410,5409,3459)) )
			$content = get_content('Research',$data['Research_id']);
		/*
		switch ($data['ResearchType_id'])
		{
			case 1: // инстр 
				if ( in_array($data['Research_id'], array(1543,1912,1995)) )
					$content = get_content('Research',$data['Research_id']);
			break;
			case 2: // луч
				if (in_array($data['Research_id'], array(93,966,75)) )
					$content = get_content('Research',$data['Research_id']);
			break;
			case 3: // лаб
				if (in_array($data['Research_id'], array(4747,3381,3299,5726,4997,2045,2134,3205,3163,3557,2556,5401,3560,5412,5410,5409,3459)) )
					$content = get_content('Research',$data['Research_id']);
			break;
			default:
		}*/
		
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
