<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с невыплатами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class SkipPayment extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('SkipPayment_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 *  Получение невыплаты по идентификатору
	 */
	function SkipPaymentById_get() {
		$data = $this->ProcessInputData('loadSkipPaymentById');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		
		$resp = $this->dbmodel->loadSkipPaymentById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание невыплаты
	 */
	function index_post() {
		$data = $this->ProcessInputData('createSkipPayment');
		if ($data === false) 
		{ 
			$this->response(array(
				'error_code' => 4,
				'error_msg' => 'Не переданы необходимые параметры'
			));
		}
		
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$result = $this->dbmodel->getFirstRowFromQuery("
			select Lpu_id from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id
		", $data);		
		if(isset($result['Lpu_id']) && $result['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		$resp = $this->dbmodel->createSkipPayment($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['SkipPayment_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('SkipPayment_id'=>$resp[0]['SkipPayment_id'])
		));
	}

	/**
	 * Редактирование невыплаты
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateSkipPayment');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createSkipPayment');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->loadSkipPaymentById($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);
		
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$result = $this->dbmodel->getFirstRowFromQuery("
			select Lpu_id from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id
		", $data);		
		if(isset($result['Lpu_id']) && $result['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		$resp = $this->dbmodel->updateSkipPayment($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 *  Получение невыплаты по месту работы
	 */
	function SkipPaymentByMedStaffFact_get() {
		$data = $this->ProcessInputData('loadSkipPaymentByMedStaffFact');
		
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->loadSkipPaymentByMedStaffFact($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}