<?php	defined('BASEPATH') or die ('No direct script access allowed');
class PersonChild extends swController {
	public $inputRules = array(
		'loadPersonChildData' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'chekPersonChild'=>array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'savePersonChild' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChild_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FeedingType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ChildTermType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChild_IsAidsMother',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChild_IsBCG',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChild_BCGSer',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonChild_BCGNum',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonChild_CountChild',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ChildPositionType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonChild_IsRejection',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
		)
	);


	/**
	 * @construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonChild_model', 'dbmodel');
	}
	/**
	 * @sdfsdf
	 */
	function savePersonChild(){
		$data = $this->ProcessInputData('savePersonChild', true);
		if ($data === false) return false;

		$response = $this->dbmodel->savePersonChild($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	*  Получение данных для фломы редактирования сведений о новорожденном
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
     * @return bool
     */
	function loadPersonChildData() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadPersonChildData', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPersonChildData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	/**
	 *
	 * @return type 
	 */
	function chekPersonChild() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('chekPersonChild', true);
		if ($data === false) return false;

		$response = $this->dbmodel->chekPersonChild($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
}
