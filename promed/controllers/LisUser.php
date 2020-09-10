<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Lis - контроллер для работы с пользователем ЛИС
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Lis
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Markoff Andrew <markov@swan.perm.ru>
* @version      10.2011
 *
 * @property Textlog textlog
 * @property Options_model Options_model
 * @property LisUser_model dbmodel
 */
class LisUser extends swController {
	public $inputRules = array(
		'get' => array(
			//array('field' => 'User_id','label' => 'Пользователь','rules' => '','type' => 'id'),
			//array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '','type' => 'id')
		),
		'save' =>array(
			array('field' => 'User_id', 'label' => 'Пользователь', 'rules' => '','type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => 'required','type' => 'id'),
			array('field' => 'User_ClientId', 'label' => 'Id клиента', 'rules' => '','type' => 'string'),
			array('field' => 'User_Login', 'label' => 'Логин', 'rules' => 'required','type' => 'string'),
			array('field' => 'User_Password', 'label' => 'Пароль','rules' => 'required','type' => 'string')
		),
	);
	/**
	 * Конструктор
	 */ 
	function __construct() {
		parent::__construct();
		$this->load->model('LisUser_model', 'dbmodel');
		//$this->load->library('textlog', array('file'=>'LisUser.log'));
		//$this->load->model('Options_model', 'Options_model');

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('LisUser_model', 'dbmodel');
		}
	}

	/**
	 * Читает и отдает клиенту данные пользователя ЛИС 
	 */
	function get(){
		$data = $this->ProcessInputData('get', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->GET('LisUser', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			$response = $this->dbmodel->get($data);
			$this->ProcessModelList($response,true,true)->ReturnData();
		}
	}
	/**
	 * Сохраняет данные пользователя ЛИС
	 */
	function save(){
		$data = $this->ProcessInputData('save', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$response = $this->lis->POST('LisUser', $data);
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$response = $this->dbmodel->save($data);
			$outdata = $this->ProcessModelSave($response, true, 'При сохранении данных пользователя ЛИС-системы произошла ошибка!')->GetOutData();
			// после сохранения на всякий случай нужно прибить текущую сессию
			unset($_SESSION['lisrequestdata']);
		}
	}
}