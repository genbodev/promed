<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с выплатами
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

class Payment extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Payment_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 *  Получение выплат по строке штатного расписания
	 */
	function PaymentByStaff_get() {
		$data = $this->ProcessInputData('loadPaymentByStaff');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->loadPaymentByStaff($data);
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
	 * Создание выплаты для строки штатного расписания
	 */
	function index_post() {
		$data = $this->ProcessInputData('createPayment');
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
			select Lpu_id from persis.v_Staff where id = :Staff_id
		", $data);		
		if(isset($result['Lpu_id']) && $result['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$resp = $this->dbmodel->createPayment($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['Payment_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Payment_id'=>$resp[0]['Payment_id'])
		));
	}

	/**
	 * Редактирование выплаты
	 */
	function index_put() {
		$data = $this->ProcessInputData('updatePayment');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createPayment');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->loadPaymentById($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);
		
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->queryResult("select top 1 id from persis.Payment with (nolock) where id = :Payment_id", $data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора выплаты',
				'error_code' => '6'
			));
		}
		
		$result = $this->dbmodel->getFirstRowFromQuery("
			SELECT top 1
				S.Lpu_id
			FROM
				persis.StaffPayment SP with(nolock)
				left join persis.v_Staff S with(nolock) on S.id = SP.StaffId
			WHERE SP.PaymentId = :Payment_id
		", $data);		
		if(isset($result['Lpu_id']) && $result['Lpu_id'] != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
	
		$resp = $this->dbmodel->updatePayment($data);
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
	 * Создание выплаты для места работы
	 */
	function PaymentMedStaffFact_post() {
		$data = $this->ProcessInputData('createPaymentMedStaffFact');
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
		
		$resp = $this->dbmodel->createPaymentMedStaffFact($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['Payment_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Payment_id'=>$resp[0]['Payment_id'])
		));
	}

	/**
	 *  Получение выплат по месту работы
	 */
	function PaymentByMedStaffFact_get() {
		$data = $this->ProcessInputData('loadPaymentByMedStaffFact');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		
		$resp = $this->dbmodel->loadPaymentByMedStaffFact($data);
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