<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ImportInsured - ...
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       SWAN developers
 * @version      25.03.2015
 * @property ImportInsured_model dbmodel
 */
class ImportInsured extends swController {
	public $inputRules = array(
		'Import' => array(
			array('default'=>26,'field' => 'RegisterList_id', 'label' => 'RegisterList_id', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('ImportInsured_model', 'dbmodel');
	}
	/**
	 *
	 * @return type 
	 */
	function Import() {
		set_time_limit(100000);
		ini_set("memory_limit", "1024M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");
		$data = $this->ProcessInputData('Import', true);
		$this->load->helper('Xml');
		$root_dir = IMPORTPATH_ROOT;
		if( !is_dir($root_dir) ) {
			if( !mkdir($root_dir) ) {
				throw new Exception('Не удалось создать директорию для хранения загружаемых данных!');
			}
		}

		if( !isset($_FILES['importFileZL']) ) {
			throw new Exception('Ошибка! Отсутствует файл! (поле importFileZL)');
		}

		$file = $_FILES['importFileZL'];
		if( $file['error'] > 0 ) {
			throw new Exception('Ошибка при загрузке файла!');
		}
		$this->checkName($file['name']);
		$fileFullName = $root_dir.$file['name'];
		if( is_file($file['tmp_name']) ) {
			$fileFullName = $root_dir.time().'_'.$file['name'];
		}

		if( !rename($file['tmp_name'], $fileFullName) ) {
			throw new Exception('Не удалось создать файл ' . $fileFullName);
		}
		$data['FileFullName'] = $fileFullName;

		if ($data){
			$response = $this->dbmodel->Import($data);

            $this->ProcessModelSave($response, true)->ReturnData();
            
            unlink($fileFullName);
			return true;
		} else {
			unlink($fileFullName);
			return false;
		}
	}
	/**
	 *
	 * @param type $filename
	 * @return type 
	 */
	private function checkName($filename){
		$good = false;
		$name = explode('.', $filename);
		if (count($name) > 0) {
			$ext = strtolower($name[count($name)-1]);
		} else {
			$ext = null;
		}
		if( $ext != 'xml' ) {
			throw new Exception('Необходим файл с расширением xml.');
		}
		$this->dbmodel->setFileName($name[0]);
		if(mb_substr($filename, 0, 2)=="NP"){
			$this->dbmodel->setType("NP");
			if(mb_strlen($name[0])==22){
				$good=true;
				$this->dbmodel->setSMOCode(mb_substr($name[0], 2,5));
				$this->dbmodel->setMOCode(mb_substr($name[0], 7,6));
			}
		}else if(mb_substr($filename, 0, 1)=="E"){
			$this->dbmodel->setType("E");
			if(mb_strlen($name[0])==21){
				$good=true;
				$this->dbmodel->setSMOCode(mb_substr($name[0], 1,5));
				$this->dbmodel->setMOCode(mb_substr($name[0], 6,6));
			}
		}
		if(!$good){
			throw new Exception('Выбранный файл не соответствует формату импорта.');
		}
		
	}
	
	
	
}
?>
