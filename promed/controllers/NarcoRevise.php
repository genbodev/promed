<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* Person - контроллер для управления людьми
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author		Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version		12.07.2009
 * @property Person_model dbmodel
*/
class NarcoRevise extends swController {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model("NarcoRevise_model", "dbmodel");
		
		$this->inputRules = array(
			'getNumber' => array(
		),
			'loadNarcoReviseList' => array(
				array(
					'field'=>'PermitType_id',
					'label'=>'PermitType_id',
					'rules'=>'',
					'type'=>'id'
				),
				array(
					'field'=>'ReviseList_setDate',
					'label'=>'ReviseList_setDate',
					'rules' => 'trim',
					'type' => 'daterange'	
				),
				array(
					'field'=>'ReviseList_Performer',
					'label'=>'ReviseList_Performer',
					'rules'=>'trim',
					'type'=>'string'
				),
				array(
					'field'=>'isMatch',
					'label'=>'isMatch',
					'rules'=>'',
					'type'=>'id'
				),
				
			),
			'loadNarcoReviseEditWindow' =>array(
				array(
					'field'=>'ReviseList_id',
					'label'=>'ReviseList_id',
					'rules'=>'required',
					'type'=>'id'
				),
			),
			'Export' =>array(
				array(
					'field'=>'ReviseList_id',
					'label'=>'ReviseList_id',
					'rules'=>'required',
					'type'=>'id'
				),
				array(
					'field'=>'typeFormat',
					'label'=>'typeFormat',
					'rules'=>'required',
					'type'=>'string'
				),
			),
			'loadNarcoReviseListDataLink' =>array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field'=>'ReviseList_id',
					'label'=>'ReviseList_id',
					'rules'=>'required',
					'type'=>'id'
				),
			),
			'deleteReviseList' =>array(
				array(
					'field'=>'ReviseList_id',
					'label'=>'ReviseList_id',
					'rules'=>'required',
					'type'=>'id'
				),
			),
			'deleteReviseListDataLink' =>array(
				array(
					'field'=>'ReviseListDataLink_id',
					'label'=>'ReviseListDataLink_id',
					'rules'=>'required',
					'type'=>'id'
				),
				array(
					'field'=>'ReviseList_id',
					'label'=>'ReviseList_id',
					'rules'=>'required',
					'type'=>'id'
				),
			),
			'saveNarcoReviseEditWindow' => array(
				array(
					'field'=>'ReviseList_Code',
					'label'=>'ReviseList_Code',
					'rules'=>'trim|required',
					'type'=>'int'
				),
				array(
					'field'=>'ReviseList_setDate',
					'label'=>'ReviseList_setDate',
					'rules'=>'required',
					'type'=>'date'
				),
				array(
					'field'=>'PermitType_id',
					'label'=>'PermitType_id',
					'rules'=>'required',
					'type'=>'id'
				),
				array(
					'field'=>'Org_id',
					'label'=>'Org_id',
					'rules'=>'required',
					'type'=>'id'
				),
				array(
					'field'=>'ReviseList_Performer',
					'label'=>'ReviseList_Performer',
					'rules'=>'trim',
					'type'=>'string'
				),
			)
			);
	}
	/**
	 *
	 * @return type 
	 */
	function loadNarcoReviseList(){
		$data = $this->ProcessInputData('loadNarcoReviseList', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadNarcoReviseList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * rrr
	 */
	function getNumber(){
		$data = $this->ProcessInputData('getNumber', true);
		if ($data === false) { return false; }
		
		if ( empty($data['Lpu_id']) ) {
			$this->ReturnData(array('success' => false));
			return true;
		}
		
		$response = $this->dbmodel->getNumber($data);
		$this->ReturnData($response[0]);
	}
	
	/**
	 *
	 * @return type 
	 */
	function Export(){
		$data = $this->ProcessInputData('Export', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->Export($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 *
	 * @return type 
	 */
	function loadNarcoReviseEditWindow(){
		$data = $this->ProcessInputData('loadNarcoReviseEditWindow', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadNarcoReviseEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 *
	 * @return type 
	 */
	function deleteReviseList(){
		$data = $this->ProcessInputData('deleteReviseList', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->deleteReviseList($data);
		$this->ProcessModelSave($response, true, 'При удалении записи возникли ошибки')->ReturnData();
	}
	/**
	 *
	 * @return type 
	 */
	function deleteReviseListDataLink(){
		$data = $this->ProcessInputData('deleteReviseListDataLink', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->deleteReviseListDataLink($data);
		$this->ProcessModelSave($response, true, 'При удалении записи возникли ошибки')->ReturnData();
	}
	/**
	 *
	 * @return type 
	 */
	function loadNarcoReviseListDataLink(){
		$data = $this->ProcessInputData('loadNarcoReviseListDataLink', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadNarcoReviseListDataLink($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	/**
	 *
	 * @return type 
	 */
	function saveNarcoReviseEditWindow(){
		
		$data = $this->ProcessInputData('saveNarcoReviseEditWindow', true);
		if ($data === false) { return false; }
		$tmp_folder = tempnam(sys_get_temp_dir(), 'imp'); //создаю временный файл
		if ($tmp_folder) {
			unlink($tmp_folder); //удаляю его и использую как имя для временного каталога
			if (mkdir($tmp_folder)) {
				//перемещаю все файлы во временный каталог
				if (!empty($_FILES["sourcefiles"])) {
					$fl = strtolower(substr($_FILES["sourcefiles"]["name"], strlen($_FILES["sourcefiles"]["name"]) - 3));
					if ($_FILES["sourcefiles"]["error"]==0) {
						move_uploaded_file($_FILES["sourcefiles"]["tmp_name"], $tmp_folder . '/' . $_FILES["sourcefiles"]["name"]);
						$file = $tmp_folder . '/' . $_FILES["sourcefiles"]["name"];
					} else {
						@unlink($tmp_folder);
						throw new Exception('Не удалось загрузить файл');
					}
					if($fl!='dbf'){
						@unlink($tmp_folder);
						throw new Exception('Выбранный файл не соответствует данной загрузке');
					}
				}
			}
		}
		$data['file']=$file;
		$response = $this->dbmodel->saveNarcoReviseEditWindow($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelSave($response, true, 'При удалении записи возникли ошибки')->ReturnData();
	}
}
?>
