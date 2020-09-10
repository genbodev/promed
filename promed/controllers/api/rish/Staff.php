<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с штатным расписанием
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

class Staff extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Staff_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 * Получение строки штатного расписания по идентификатору
	 */
	function StaffById_get() {
		$data = $this->ProcessInputData('loadStaffById');

		$sp = getSessionParams();
		$staffLpuID = $this->dbmodel->getStaffLpuID($data);
		if(!$staffLpuID || $staffLpuID != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		$resp = $this->dbmodel->loadStaffById($data);
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
	 * Создание строки штатного расписания
	 */
	function index_post() {
		$data = $this->ProcessInputData('createStaffAPI');
		if ($data === false) 
		{ 
			$this->response(array(
				'error_code' => 4,
				'error_msg' => 'Не переданы необходимые параметры'
			));
		}
		$secondProcess = false;
		if($data['Rate'] >= 0.2){
			$var = $this->inputRules['createStaff'];
			foreach ($var as $key => $value) {
				if($value['field'] == 'FRMPSubdivision_id') {
					$var[$key]['rules'] = 'required';
					break;
				}
			}
			$secondProcess = true;
		}
		$res = $this->dbmodel->getPostKind(array('Post_id'=>$data['Post_id']));
		if(!empty($res[0]['PostKind_Code']) && in_array($res[0]['PostKind_Code'], array('1','2','3'))){
			$var = $this->inputRules['createStaff'];
			foreach ($var as $key => $value) {
				if($value['field'] == 'MedicalCareKind_id') {
					$var[$key]['rules'] = 'required';
					break;
				}
			}
			$secondProcess = true;
		}
		if($secondProcess){
			$data = $this->ProcessInputData('createStaffAPI');
		}
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$this->load->model('LpuStructure_model', 'lsmodel');
		$res = $this->lsmodel->getLpuSectionData(array('LpuSection_id'=>$data['LpuSection_id']));
		if(!empty($res['data']['LpuBuilding_id'])){
			$data['LpuBuilding_id'] = $res['data']['LpuBuilding_id'];
		} else {
			$this->response(array(
				'error_msg' => 'Для переданного отделения не указано подразделение',
				'error_code' => '6',
				'data' => ''
			));
		}
		if(!empty($res['data']['LpuUnit_id'])){
			$data['LpuUnit_id'] = $res['data']['LpuUnit_id'];
		} else {
			$this->response(array(
				'error_msg' => 'Для переданного отделения не указана группа отделений',
				'error_code' => '6',
				'data' => ''
			));
		}
		if(!empty($res['data']['Lpu_id'])){
			if($sp['Lpu_id'] != $res['data']['Lpu_id']){
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Данный метод доступен только для своей МО',
					'data' => ''
				));
			}
			$data['Lpu_id'] = $res['data']['Lpu_id'];
		} else {
			$this->response(array(
				'error_msg' => 'Для переданного отделения не указано ЛПУ',
				'error_code' => '6',
				'data' => ''
			));
		}

		$fields = array('IsVillageBonus','isDummyStaff');
		foreach ($fields as $field) {
			if(!empty($data[$field]) && $data[$field] == 2){
				$data[$field] = 1;
			} else {
				$data[$field] = 0;
			}
		}
		
		$resp = $this->dbmodel->createStaff($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['Staff_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('Staff_id'=>$resp[0]['Staff_id'])
		));
	}

	/**
	 * Редактирование строки штатного расписания
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateStaff');

		// достаём обязательные поля из метода создания (их нельзя перезаписывать на пустые)
		$requiredFields = $this->getRequiredFields('createStaff');
		$data = $this->unsetEmptyFields($data, $requiredFields);

		$old_data = $this->dbmodel->loadStaffById($data);
		if (empty($old_data[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data = array_merge($old_data[0], $data);

		$secondProcess = false;
		if($data['Rate'] >= 0.2){
			if (empty($data['FRMPSubdivision_id'])) {
				$this->response(array(
					'error_msg' => 'Не указан параметр FRMPSubdivision_id',
					'error_code' => '6'
				));
			}
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];

		$resp = $this->dbmodel->loadStaffById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['Staff_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует строки для переданного идентификатора',
				'error_code' => '6'
			));
		}
		
		//получим идентификатор МО
		$staffLpuID = $this->dbmodel->getStaffLpuID($data);
		if(!$staffLpuID || $staffLpuID != $sp['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
	
		$fields = array('IsVillageBonus','isDummyStaff');
		foreach ($fields as $field) {
			if(!empty($data[$field]) && $data[$field] == 2){
				$data[$field] = 1;
			} else {
				$data[$field] = 0;
			}
		}
		
		$resp = $this->dbmodel->updateStaff($data);
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
	 * Получение строки штатного расписания по месту работы
	 */
	function StaffByMedStaffFact_get() {
		$data = $this->ProcessInputData('loadStaffByMedStaffFact');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->loadStaffByMedStaffFact($data);
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
	 * Получение строки штатного расписания по отделению МО
	 */
	function StaffByLpuSection_get() {
		$data = $this->ProcessInputData('loadStaffByLpuSection');
		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->loadStaffByLpuSection($data);
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