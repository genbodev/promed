<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnDocument - контроллер 
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

class EvnDocument extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'loadEvnDocumentViewForm' => array(
				array(
					'field' => 'EvnDocument_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				/*),
				array(
					'field' => 'EvnDocumentType_id',
					'label' => 'Идентификатор типа',
					'rules' => 'required',
					'type' => 'id'*/
				)
			)
		);
	}


	/**
	*  Выводит печатную форму
	*  Входящие данные: $_POST['EvnDocument_id']
	*  На выходе: JSON-строка
	*  Используется: форма электронной медицинской карты
	*/
	function loadEvnDocumentViewForm() {
		$data = $this->ProcessInputData('loadEvnDocumentViewForm', true, true);
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

		$title = 'Документ';
		$content = 'Печатная форма не найдена';
		if ( in_array($data['EvnDocument_id'], array(79,80,81)) )
			$content = get_content('EvnDocument',$data['EvnDocument_id']);
		
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
