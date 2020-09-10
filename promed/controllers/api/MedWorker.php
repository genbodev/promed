<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с сотрудниками
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

class MedWorker extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MedWorker_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 * Получение сотрудника по человеку
	 */
	function index_get() {
		$data = $this->ProcessInputData('loadMedWorker');

		$resp = $this->dbmodel->loadMedWorker($data);
		if (!is_array($resp) && empty($resp[0]['MedWorker_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp[0]
		));
	}

	/**
	 * Получение сотрудника по идентификатору
	 */
	public function MedWorkerById_get() {
		$data = $this->ProcessInputData('getMedWorkerById');

		//$resp = $this->dbmodel->getMedWorkerById($data);
		$resp = $this->dbmodel->getMedWorkerByIdAPI($data);
		if (!is_array($resp) && empty($resp[0]['Person_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp[0]
		));
	}

	/**
	 * Создание сотрудника
	 */
	function index_post() {
		$data = $this->ProcessInputData('createMedWorker');
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorker($data);
		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp[0]['MedWorker_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('MedWorker_id'=>$resp[0]['MedWorker_id'])
		));
	}

	/**
	 * Создание данных о среднем или профессиональном образовании сотрудника
	 */
	public function SpecialityDiploma_post() {
		$data = $this->ProcessInputData('createSpecialityDiploma');

		if ( empty($data['Person_id']) && empty($data['MedWorker_id']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, MedWorker_id) должен быть заполнен',
				'error_code' => '3',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('SpecialityDiploma', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['SpecialityDiploma_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('id' => $resp[0]['SpecialityDiploma_id'])
		));
	}

	/**
	 * Изменение данных о среднем или профессиональном образовании сотрудника
	 */
	public function SpecialityDiploma_put() {
		$data = $this->ProcessInputData('updateSpecialityDiploma');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('SpecialityDiploma', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['SpecialityDiploma_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление данных о среднем или профессиональном образовании сотрудника
	 */
	public function SpecialityDiploma_delete() {
		$data = $this->ProcessInputData('deleteSpecialityDiploma');
		$resp = $this->dbmodel->deletePersisObject('SpecialityDiploma', $data);

		if ( !is_array($resp) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
	
	/**
	 * Получение данных о среднем или профессиональном образовании сотрудника
	 */
	public function SpecialityDiploma_get() {
		$data = $this->ProcessInputData('getSpecialityDiploma');
		$resp = $this->dbmodel->getSpecialityDiplomaForAPI($data);

		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}	

	/**
	 * Создание данных о послевузовском образовании сотрудника
	 */
	public function PostgraduateEducation_post() {
		$data = $this->ProcessInputData('createPostgraduateEducation');

		if ( empty($data['Person_id']) && empty($data['MedWorker_id']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, MedWorker_id) должен быть заполнен',
				'error_code' => '3',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('PostgraduateEducation', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['PostgraduateEducation_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('id' => $resp[0]['PostgraduateEducation_id'])
		));
	}

	/**
	 * Изменение данных о послевузовском образовании сотрудника
	 */
	public function PostgraduateEducation_put() {
		$data = $this->ProcessInputData('updatePostgraduateEducation');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('PostgraduateEducation', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['PostgraduateEducation_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление данных о послевузовском образовании сотрудника
	 */
	public function PostgraduateEducation_delete() {
		$data = $this->ProcessInputData('deletePostgraduateEducation');
		$resp = $this->dbmodel->deletePersisObject('PostgraduateEducation', $data);

		if ( !is_array($resp) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
	
	/**
	 * Получение данных о послевузовском образовании сотрудника
	 */
	public function PostgraduateEducation_get() {
		$data = $this->ProcessInputData('getQualificationCategory');
		$resp = $this->dbmodel->getPostgraduateEducationForAPI($data);

		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}

	/**
	 * Создание данных о курсах переподготовки сотрудника
	 */
	public function RetrainingCourse_post() {
		$data = $this->ProcessInputData('createRetrainingCourse');

		if ( empty($data['Person_id']) && empty($data['MedWorker_id']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, MedWorker_id) должен быть заполнен',
				'error_code' => '3',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('RetrainingCourse', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['RetrainingCourse_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('id' => $resp[0]['RetrainingCourse_id'])
		));
	}

	/**
	 * Изменение данных о курсах переподготовки сотрудника
	 */
	public function RetrainingCourse_put() {
		$data = $this->ProcessInputData('updateRetrainingCourse');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('RetrainingCourse', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['RetrainingCourse_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление данных о курсах переподготовки сотрудника
	 */
	public function RetrainingCourse_delete() {
		$data = $this->ProcessInputData('deleteRetrainingCourse');
		$resp = $this->dbmodel->deletePersisObject('RetrainingCourse', $data);

		if ( !is_array($resp) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Создание данных о курсах повышения квалификации
	 */
	public function QualificationImprovementCourse_post() {
		$data = $this->ProcessInputData('createQualificationImprovementCourse');

		if ( empty($data['Person_id']) && empty($data['MedWorker_id']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, MedWorker_id) должен быть заполнен',
				'error_code' => '3',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('QualificationImprovementCourse', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['QualificationImprovementCourse_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('id' => $resp[0]['QualificationImprovementCourse_id'])
		));
	}

	/**
	 * Изменение данных о курсах повышения квалификации
	 */
	public function QualificationImprovementCourse_put() {
		$data = $this->ProcessInputData('updateQualificationImprovementCourse');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('QualificationImprovementCourse', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['QualificationImprovementCourse_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление данных о курсах повышения квалификации
	 */
	public function QualificationImprovementCourse_delete() {
		$data = $this->ProcessInputData('deleteQualificationImprovementCourse');
		$resp = $this->dbmodel->deletePersisObject('QualificationImprovementCourse', $data);

		if ( !is_array($resp) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
	
	/**
	 * Получение данных о курсах повышения квалификации
	 */
	public function QualificationImprovementCourse_get() {
		$data = $this->ProcessInputData('getQualificationCategory');
		$resp = $this->dbmodel->getQualificationImprovementCourseForAPI($data);

		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}

	/**
	 * Создание данных о сертификатах сотрудника
	 */
	public function Certificate_post() {
		$data = $this->ProcessInputData('createCertificate');

		if ( empty($data['Person_id']) && empty($data['MedWorker_id']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, MedWorker_id) должен быть заполнен',
				'error_code' => '3',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('Certificate', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['Certificate_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('id' => $resp[0]['Certificate_id'])
		));
	}

	/**
	 * Изменение данных о сертификатах сотрудника
	 */
	public function Certificate_put() {
		$data = $this->ProcessInputData('updateCertificate');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('Certificate', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['Certificate_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление данных о сертификатах сотрудника
	 */
	public function Certificate_delete() {
		$data = $this->ProcessInputData('deleteCertificate');
		$resp = $this->dbmodel->deletePersisObject('Certificate', $data);

		if ( !is_array($resp) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение данных о сертификатах сотрудника
	 */
	public function Certificate_get() {
		$data = $this->ProcessInputData('getCertificate');
		$resp = $this->dbmodel->getMedWorkerForAPI($data);

		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}
	
	/**
	 * Получение данных о квалификационных категориях сотрудника
	 */
	public function QualificationCategory_get() {
		$data = $this->ProcessInputData('getQualificationCategory');
		$resp = $this->dbmodel->getQualificationCategoryForAPI($data);

		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}
	
	/**
	 * Создание данных о квалификационных категориях сотрудника
	 */
	public function QualificationCategory_post() {
		$data = $this->ProcessInputData('createQualificationCategory');

		if ( empty($data['Person_id']) && empty($data['MedWorker_id']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, MedWorker_id) должен быть заполнен',
				'error_code' => '3',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('QualificationCategory', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['QualificationCategory_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('id' => $resp[0]['QualificationCategory_id'])
		));
	}
	
	/**
	 * Получение данных о курсах переподготовки сотрудника
	 */
	public function RetrainingCourse_get() {
		$data = $this->ProcessInputData('getQualificationCategory');
		$resp = $this->dbmodel->getRetrainingCourseForAPI($data);

		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}

	/**
	 * Изменение данных о квалификационных категориях сотрудника
	 */
	public function QualificationCategory_put() {
		$data = $this->ProcessInputData('updateQualificationCategory');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('QualificationCategory', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['QualificationCategory_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление данных о квалификационных категориях сотрудника
	 */
	public function QualificationCategory_delete() {
		$data = $this->ProcessInputData('deleteQualificationCategory');
		$resp = $this->dbmodel->deletePersisObject('QualificationCategory', $data);

		if ( !is_array($resp) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Создание данных о наградах сотрудника
	 */
	public function Reward_post() {
		$data = $this->ProcessInputData('createReward');

		if ( empty($data['Person_id']) && empty($data['MedWorker_id']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, MedWorker_id) должен быть заполнен',
				'error_code' => '3',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('Reward', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['Reward_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('id' => $resp[0]['Reward_id'])
		));
	}

	/**
	 * Изменение данных о наградах сотрудника
	 */
	public function Reward_put() {
		$data = $this->ProcessInputData('updateReward');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('Reward', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['Reward_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление данных о наградах сотрудника
	 */
	public function Reward_delete() {
		$data = $this->ProcessInputData('deleteReward');
		$resp = $this->dbmodel->deletePersisObject('Reward', $data);

		if ( !is_array($resp) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
	
	/**
	 * Получение данных о наградах сотрудника
	 */
	public function Reward_get() {
		$data = $this->ProcessInputData('getQualificationCategory');
		$resp = $this->dbmodel->getRewardForAPI($data);

		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}

	/**
	 * Создание данных об аккредитации сотрудника
	 */
	public function Accreditation_post() {
		$data = $this->ProcessInputData('createAccreditation');

		if ( empty($data['Person_id']) && empty($data['MedWorker_id']) ) {
			$this->response(array(
				'error_msg' => 'Хотя бы один из параметров (Person_id, MedWorker_id) должен быть заполнен',
				'error_code' => '3',
				'data' => ''
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('Accreditation', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['Accreditation_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array('id' => $resp[0]['Accreditation_id'])
		));
	}

	/**
	 * Изменение данных об аккредитации сотрудника
	 */
	public function Accreditation_put() {
		$data = $this->ProcessInputData('updateAccreditation');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		
		$resp = $this->dbmodel->saveMedWorkerParam('Accreditation', $data);

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		if ( !is_array($resp) || empty($resp[0]['Accreditation_id']) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Удаление данных об аккредитации сотрудника
	 */
	public function Accreditation_delete() {
		$data = $this->ProcessInputData('deleteAccreditation');
		$resp = $this->dbmodel->deletePersisObject('Accreditation', $data);

		if ( !is_array($resp) ) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}
	
	/**
	 * Получение данных об аккредитации сотрудника
	 */
	public function Accreditation_get() {
		$data = $this->ProcessInputData('getQualificationCategory');
		$resp = $this->dbmodel->getAccreditationForAPI($data);

		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}

		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}
	
	/**
	 * Получение данных о месте работы сотрудника
	 */
	public function WorkPlace_get() {
		$data = $this->ProcessInputData('getWorkPlace');
		$resp = $this->dbmodel->getWorkPlaceForAPI($data);
		
		if ( !is_array($resp) ) {
			$this->response(array(
				'error_msg' => self::HTTP_INTERNAL_SERVER_ERROR,
				'error_code' => '6'
			));
		}
		
		if ( !empty($resp[0]['Error_Msg']) ) {
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		
		$this->response(array(
			'error_code' => 0,
			'data' => (count($resp)>0)?$resp:0
		));
	}
}