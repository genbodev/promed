<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * UslugaPrivateClinic - контроллер для работы с услугами услугами частных медицинских учреждений
 *
 * @author       Gilmiyarov Artur aka gaf
 * @version      27.04.2018
 */

class EvnUslugaPrivateClinic extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'saveprivateclinic' => array(
				
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),				
				array(
					'field' => 'Lpu_id',
					'label' => 'Медицинское учреждение',
					'rules' => '',
					'type' => 'int'
				),				
				array(
					'field' => 'MedPersonal_iidd',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'int'
				),				
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Research_Data',
					'label' => 'Дата регистрации услуги',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getMedPersonalid' => array(				
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				)
			),
			'deleteData' => array(				
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				)
			)			
			
		);		
		$this->load->database();
		$this->load->model('EvnUslugaPrivateClinic_model', 'dbmodel');
		//$this->inputRules = $this->dbmodel->inputRules;
	}			
	
	/**
	 * Сохранение услуги
	 */	
	function save(){
		//echo "<pre>22".print_r($_POST, 1)."</pre>";
		
		$data = $this->ProcessInputData('saveprivateclinic', true);
		
		if ($data){
			$response = $this->dbmodel->save($data);
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении Услуги частных клиник')->ReturnData();
			return true;
		} else {
			return false;
		}
	}	
	
	/**
	 * Получение Идентификатора врача
	 */
	function getMedPersonalid() {
		$this->load->model('EvnUslugaPrivateClinic_model', 'dbmodel');
		$data = $this->ProcessInputData('getMedPersonalid', false);
		//echo "<pre>22".print_r($data, 1)."</pre>";
		$response = $this->dbmodel->getMedPersonalid($data);
		return $this->ReturnData($response);
	}		
		
	/**
	 * Помечаем запись в реестре на удаление
	 */
	function deleteData()
	{
		$this->load->model('EvnUslugaPrivateClinic_model', 'dbmodel');
		
		$data = $this->ProcessInputData('deleteData', true);
		if ($data === false) { return false; }	
		
		$response = $this->dbmodel->deleteData($data);
		if (strlen($response[0]['Error_Msg']) != 0) {
			$result = array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg']));
		} else {
			$result = array('success' => true);
		}
		$this->ReturnData($result);
		return true;
	}	
}