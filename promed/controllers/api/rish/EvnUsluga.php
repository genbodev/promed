<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnUsluga - контроллер API для работы с услугами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			12.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnUsluga extends SwREST_Controller {

	private $evnUslugaClasses = array(
		'EvnUsluga',
		'EvnUslugaCommon',
		'EvnUslugaOper',
		'EvnUslugaStom',
		'EvnUslugaPregnancySpec',//! только удаление
		'EvnUslugaOnkoBeam',
		'EvnUslugaOnkoChem',
		'EvnUslugaOnkoGormun',
		'EvnUslugaOnkoSurg',
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnUsluga_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 *  Получение информации по общей услуге
	 */
	function index_get() {
		if(!empty($this->_args['Evn_setDT']) && strlen(trim($this->_args['Evn_setDT']))>10){
			$allDateTime = trim($this->_args['Evn_setDT']);
			$this->_args['Evn_setDT'] = substr($allDateTime, 0, 10);
			$time = trim(substr($allDateTime, 10));
			if(!empty($time)){
				if(strlen($time) >= 5 && strlen($time) < 8){
					$time = substr($time, 0, 5).':00';
				} else if(strlen($time) < 5){
					$time = null;
				}
			}
		}
		$data = $this->ProcessInputData('loadEvnUsluga', null, true);

		$paramsExist = false;
		foreach ($data as $value) {
			if(!empty($value)){
				$paramsExist = true;
			}
		}
		if(!$paramsExist){
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6',
				'data' => ''
			));
		}
		if(!empty($data['Evn_setDT'])){
			if(!empty($time)){
				$data['Evn_setDT'] = $data['Evn_setDT'].' '.$time;
			} else {
				$data['Evn_setDT'] = $data['Evn_setDT'].' 00:00:00';
			}
		}

		$resp = $this->dbmodel->loadEvnUsluga($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Создание общей услуги
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnUsluga',null,true);
		
		$data['LpuSection_uid'] = $data['LpuSection_id'];
		$data['Org_uid'] = $data['Org_id'];
		$data['EvnUslugaCommon_pid'] = $data['Evn_pid'];
		$data['EvnUslugaCommon_id'] = null;
		$data['EvnUslugaCommon_setDate'] = $data['Evn_setDT'];
		$data['EvnUslugaCommon_disDate'] = $data['Evn_disDT'];
		$data['EvnUslugaCommon_Kolvo'] = $data['EvnUsluga_Kolvo'];
		$data['EvnUslugaCommon_Price'] = $data['EvnUsluga_Price'];
		$data['fromAPI'] = 1;

		if(!empty($data['MedStaffFact_id'])){
			$this->load->model('MedPersonal_model', 'mpmodel');
			$medPers = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id'=>$data['MedStaffFact_id']));
			if(!empty($medPers[0]['MedPersonal_id'])){
				$data['MedPersonal_id'] = $medPers[0]['MedPersonal_id'];
			}
		}

		$evnData = $this->dbmodel->loadEvnUslugaEvnData(array('Evn_pid'=>$data['Evn_pid']));
		if(isset($evnData[0]['PersonEvn_id'])){
			$data['PersonEvn_id'] = $evnData[0]['PersonEvn_id'];
		}
		if(isset($evnData[0]['Server_id'])){
			$data['Server_id'] = $evnData[0]['Server_id'];
		}
		if(isset($evnData[0]['Person_id'])){
			$data['Person_id'] = $evnData[0]['Person_id'];
		}
		
		$resp = $this->dbmodel->saveEvnUslugaCommon($data);
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		} else if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaCommon_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('EvnUsluga_id' => $resp[0]['EvnUslugaCommon_id'], 'Evn_id' => $resp[0]['EvnUslugaCommon_id'])
			)
		));
	}

	/**
	 *  Редактирование общей услуги
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnUsluga',null,true);
		
		$data['EvnUslugaCommon_id'] = $data['EvnUsluga_id'];
		$data['LpuSection_uid'] = $data['LpuSection_id'];
		$data['Org_uid'] = $data['Org_id'];
		$data['EvnUslugaCommon_pid'] = $data['Evn_pid'];
		$data['EvnUslugaCommon_setDate'] = $data['Evn_setDT'];
		$data['EvnUslugaCommon_disDate'] = $data['Evn_disDT'];
		$data['EvnUslugaCommon_Kolvo'] = $data['EvnUsluga_Kolvo'];
		$data['EvnUslugaCommon_Price'] = $data['EvnUsluga_Price'];
		$data['fromAPI'] = 1;

		$resp = $this->dbmodel->loadEvnUsluga(array(
			'EvnUsluga_id'=>$data['EvnUsluga_id'],
			'Lpu_id'=>$data['Lpu_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['EvnUsluga_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора услуги',
				'error_code' => '6',
				'data' => ''
			));
		}

		if(!empty($data['MedStaffFact_id'])){
			$this->load->model('MedPersonal_model', 'mpmodel');
			$medPers = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id'=>$data['MedStaffFact_id']));
			if(!empty($medPers[0]['MedPersonal_id'])){
				$data['MedPersonal_id'] = $medPers[0]['MedPersonal_id'];
			}
		}

		$evnPid = '';
		if(!empty($data['Evn_pid'])){
			$evnPid = $data['Evn_pid'];
		} else if(!empty($resp[0]['Evn_pid'])){
			$evnPid = $resp[0]['Evn_pid'];
		}
		if(!empty($evnPid)){
			$evnData = $this->dbmodel->loadEvnUslugaEvnData(array('Evn_pid'=>$evnPid));
			if(isset($evnData[0]['PersonEvn_id'])){
				$data['PersonEvn_id'] = $evnData[0]['PersonEvn_id'];
			}
			if(isset($evnData[0]['Server_id'])){
				$data['Server_id'] = $evnData[0]['Server_id'];
			}
			if(isset($evnData[0]['Person_id'])){
				$data['Person_id'] = $evnData[0]['Person_id'];
			}
		}
		
		$resData = $array = array_filter($data);
		$res_array = array_merge($resp[0], $resData);
		if(empty($res_array['EvnUslugaCommon_Kolvo']) && !empty($resp[0]['EvnUsluga_Kolvo'])){
			$res_array['EvnUslugaCommon_Kolvo'] = $resp[0]['EvnUsluga_Kolvo'];
		}
		
		if(empty($res_array['EvnUslugaCommon_setDate'])){
			$res_array['EvnUslugaCommon_setDate'] = (!empty($resp[0]['Evn_setDT'])) ? $resp[0]['Evn_setDT'] : NULL;
		}
		if(empty($res_array['EvnUslugaCommon_disDate'])){
			$res_array['EvnUslugaCommon_disDate'] = (!empty($resp[0]['Evn_disDT'])) ? $resp[0]['Evn_disDT'] : NULL;
		}
		if(empty($res_array['EvnUslugaCommon_rid'])){
			$res_array['EvnUslugaCommon_rid'] = (!empty($resp[0]['EvnUsluga_rid'])) ? $resp[0]['EvnUsluga_rid'] : NULL;
		}
		
		$resp = $this->dbmodel->saveEvnUslugaCommon($res_array);
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		} else if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaCommon_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => ''
		));
	}

	/**
	 *  Получение списка выполненных услуг
	 */
	function EvnUslugaList_get() {
		$data = $this->ProcessInputData('loadEvnUslugaList', null, true);

		$resp = $this->dbmodel->loadEvnUslugaList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Получение информации по оперативной услуге
	 */
	function EvnUslugaOper_get() {
		if(!empty($this->_args['Evn_setDT']) && strlen(trim($this->_args['Evn_setDT']))>10){
			$allDateTime = trim($this->_args['Evn_setDT']);
			$this->_args['Evn_setDT'] = substr($allDateTime, 0, 10);
			$time = trim(substr($allDateTime, 10));
			if(!empty($time)){
				if(strlen($time) >= 5 && strlen($time) < 8){
					$time = substr($time, 0, 5).':00';
				} else if(strlen($time) < 5){
					$time = null;
				}
			}
		}
		$data = $this->ProcessInputData('loadEvnUsluga', null, true);

		$paramsExist = false;
		foreach ($data as $value) {
			if(!empty($value)){
				$paramsExist = true;
			}
		}
		if(!$paramsExist){
			$this->response(array(
				'error_msg' => 'Не передан ни один параметр',
				'error_code' => '6',
				'data' => ''
			));
		}
		if(!empty($data['Evn_setDT'])){
			if(!empty($time)){
				$data['Evn_setDT'] = $data['Evn_setDT'].' '.$time;
			} else {
				$data['Evn_setDT'] = $data['Evn_setDT'].' 00:00:00';
			}
		}

		$resp = $this->dbmodel->loadEvnUslugaOper($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 *  Создание оперативной услуги
	 */
	function EvnUslugaOper_post() {
		$data = $this->ProcessInputData('createEvnUslugaOper', null, true);

		$data['LpuSection_uid'] = $data['LpuSection_id'];
		$data['Org_uid'] = $data['Org_id'];
		$data['EvnUslugaOper_pid'] = $data['Evn_pid'];
		$data['EvnUslugaOper_setDate'] = $data['Evn_setDT'];
		$data['EvnUslugaOper_disDate'] = $data['Evn_disDT'];
		$data['EvnUslugaOper_Kolvo'] = $data['EvnUsluga_Kolvo'];
		$data['EvnUslugaOper_Price'] = $data['EvnUsluga_Price'];

		if(!empty($data['MedStaffFact_id'])){
			$this->load->model('MedPersonal_model', 'mpmodel');
			$medPers = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id'=>$data['MedStaffFact_id']));
			if(!empty($medPers[0]['MedPersonal_id'])){
				$data['MedPersonal_id'] = $medPers[0]['MedPersonal_id'];
			}
		}

		$evnData = $this->dbmodel->loadEvnUslugaEvnData(array('Evn_pid'=>$data['Evn_pid']));
		if(isset($evnData[0]['PersonEvn_id'])){
			$data['PersonEvn_id'] = $evnData[0]['PersonEvn_id'];
		}
		if(isset($evnData[0]['Server_id'])){
			$data['Server_id'] = $evnData[0]['Server_id'];
		}
		if(isset($evnData[0]['Person_id'])){
			$data['Person_id'] = $evnData[0]['Person_id'];
		}

		$resp = $this->dbmodel->saveEvnUslugaOper($data);
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		} else if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		}
		if (!is_array($resp) || empty($resp[0]['EvnUslugaOper_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('EvnUsluga_id' => $resp[0]['EvnUslugaOper_id'], 'EvnUslugaOper_id' => $resp[0]['EvnUslugaOper_id'], 'Evn_id' => $resp[0]['EvnUslugaOper_id']
				)
			)
		));
	}

	/**
	 *  Редактирование оперативной услуги
	 */
	function EvnUslugaOper_put() {
		$data = $this->ProcessInputData('updateEvnUslugaOper', null, true);

		$data['EvnUslugaOper_id'] = $data['EvnUsluga_id'];
		$data['LpuSection_uid'] = $data['LpuSection_id'];
		$data['Org_uid'] = $data['Org_id'];
		$data['EvnUslugaOper_pid'] = $data['Evn_pid'];
		$data['EvnUslugaOper_setDate'] = $data['Evn_setDT'];
		$data['EvnUslugaOper_disDate'] = $data['Evn_disDT'];
		$data['EvnUslugaOper_Kolvo'] = $data['EvnUsluga_Kolvo'];
		$data['EvnUslugaOper_Price'] = $data['EvnUsluga_Price'];

		if(!empty($data['MedStaffFact_id'])){
			$this->load->model('MedPersonal_model', 'mpmodel');
			$medPers = $this->mpmodel->getMedPersonInfo(array('MedStaffFact_id'=>$data['MedStaffFact_id']));
			if(!empty($medPers[0]['MedPersonal_id'])){
				$data['MedPersonal_id'] = $medPers[0]['MedPersonal_id'];
			}
		}

		$resp = $this->dbmodel->loadEvnUslugaOper(array(
			'EvnUsluga_id'=>$data['EvnUsluga_id'],
			'Lpu_id'=>$data['Lpu_id']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['EvnUslugaOper_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует записи для переданного идентификатора услуги',
				'error_code' => '6',
				'data' => ''
			));
		}

		$evnPid = '';
		if(!empty($data['Evn_pid'])){
			$evnPid = $data['Evn_pid'];
		} else if(!empty($resp[0]['Evn_pid'])){
			$evnPid = $resp[0]['Evn_pid'];
		}
		if(!empty($evnPid)){
			$evnData = $this->dbmodel->loadEvnUslugaEvnData(array('Evn_pid'=>$evnPid));
			if(isset($evnData[0]['PersonEvn_id'])){
				$data['PersonEvn_id'] = $evnData[0]['PersonEvn_id'];
			}
			if(isset($evnData[0]['Server_id'])){
				$data['Server_id'] = $evnData[0]['Server_id'];
			}
			if(isset($evnData[0]['Person_id'])){
				$data['Person_id'] = $evnData[0]['Person_id'];
			}
		}
		
		//убираем пустые элементы
		$resData = $array = array_filter($data);
		$res_array = array_merge($resp[0], $resData);
		if(empty($res_array['EvnUslugaOper_setDate'])){
			$res_array['EvnUslugaOper_setDate'] = (!empty($resp[0]['Evn_setDT'])) ? $resp[0]['Evn_setDT'] : NULL;
		}
		if(empty($res_array['EvnUslugaOper_disDate'])){
			$res_array['EvnUslugaOper_disDate'] = (!empty($resp[0]['Evn_disDT'])) ? $resp[0]['Evn_disDT'] : NULL;
		}
		if(empty($res_array['LpuSection_uid'])){
			$res_array['LpuSection_uid'] = (!empty($resp[0]['LpuSection_id'])) ? $resp[0]['LpuSection_id'] : NULL;
		}
		if(empty($res_array['Org_uid'])){
			$res_array['Org_uid'] = (!empty($resp[0]['Org_id'])) ? $resp[0]['Org_id'] : NULL;
		}
		if(empty($res_array['EvnUslugaOper_Kolvo'])){
			$res_array['EvnUslugaOper_Kolvo'] = (!empty($resp[0]['EvnUsluga_Kolvo'])) ? $resp[0]['EvnUsluga_Kolvo'] : NULL;
		}
		$resp = $this->dbmodel->saveEvnUslugaOper($res_array);
		$this->response(array(
			'error_code' => 0
		));
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6',
				'data' => ''
			));
		} else if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6',
				'data' => ''
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
	@OA\get(
	path="/api/EvnUsluga/mloadEvnUslugaPanel",
	tags={"EvnUsluga"},
	summary="Загрузка списка услуг",

	@OA\Parameter(
	name="EvnUsluga_pid",
	in="query",
	description="Идентификатор случая",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="EvnUsluga_id",
	description="Оказание услуги, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnClass_SysNick",
	description="класс события, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="UslugaComplex_Name",
	description="Комплексные услуги, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnUsluga_setDate",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnUsluga_Count",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="EvnUsluga_Kolvo",
	description="Оказание услуги, Кол-во оказанных услуг",
	type="string",

	)
	,
	@OA\Property(
	property="EvnDiagPLStom_id",
	description="Заболевание: Стоматология, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnXml_id",
	description="Ненормализованные данные для событий , Идентификатор",
	type="integer",

	)

	)

	)

	)
	)

	)
	 */
	function mloadEvnUslugaPanel_get() {
		$data = $this->ProcessInputData('mloadEvnUslugaPanel');

		$resp = $this->dbmodel->loadEvnUslugaPanel($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Удаление услуги
	 */
	function mDeleteEvnUsluga_get() {

		$data = $this->ProcessInputData('mDeleteEvnUsluga', null, true);
		$data['id'] = $data['EvnUsluga_id'];

		$data['class'] = $this->dbmodel->getFirstResultFromQuery("
					select top 1 EvnClass_SysNick
					from v_EvnUsluga (nolock)
					where EvnUsluga_id = :EvnUsluga_id
				", array('EvnUsluga_id' => $data['EvnUsluga_id'])
		);

		$resp = $this->dbmodel->deleteEvnUsluga($data);
		if (!empty($resp[0])) $resp = $resp[0];

		$response = array('error_code' => 0);
		if (!empty($resp['Error_Code'])) $response['data'] = $resp;

		$this->response($response);
	}
	
	/**
	 * метод создания услуги
	 */
	function msaveEvnUslugaCommon_post() {

		// нагребем периодику по умолчанию, если не указана
		if (empty($this->_args['PersonEvn_id'])) {
			if (!empty($this->_args['Person_id'])) {

				$this->load->model('Common_model');
				$personEvnData = $this->Common_model->loadPersonDataForApi(array('Person_id' => $this->_args['Person_id']));

				if (!empty($personEvnData[0])) $personEvnData = $personEvnData[0];
				if (empty($personEvnData) || !empty($personEvnData) && empty($personEvnData['PersonEvn_id'])) {
					$this->response(array(
							'success' => false,
							'error_code' => 6,
							'Error_Msg' => (!empty($personEvnData['Error_Msg']) ? $personEvnData['Error_Msg']  : 'Ошибка при получении периодики пациента'))
					);
				}

				$this->_args['PersonEvn_id'] = $personEvnData['PersonEvn_id'];
				$this->_args['Server_id'] = $personEvnData['Server_id'];

			} else {
				$this->response(array(
						'success' => false,
						'error_code' => 6,
						'Error_Msg' => 'Не указан Person_id')
				);
			}
		}

		$data = $this->ProcessInputData('msaveEvnUslugaCommon', null, true);
		if ($data === false) { return false; }

		$msfData = array();
		if (!empty($data['MedStaffFact_id'])) $msfData = $this->dbmodel->getMsfDataForApi($data);

		$uslugaData = $this->dbmodel->getUslugaComplexDataForApi($data);
		if (!empty($uslugaData['Error_Msg'])) {
			$this->response(array(
				'success' => false,
				'error_msg' => $uslugaData['Error_Msg']
			));
		}

		$time = time();
		$timeData = array(
			'EvnUslugaCommon_setDate' =>  date('Y-m-d', $time),
			'EvnUslugaCommon_setTime' => date('H:i', $time),
			'EvnUslugaCommon_disDate' =>  date('Y-m-d', $time),
			'EvnUslugaCommon_disTime' => date('H:i', $time),
		);

		if( empty($data['EvnUslugaCommon_setDate']) ){
			$data['EvnUslugaCommon_setDate'] = $timeData['EvnUslugaCommon_setDate'];
			$data['EvnUslugaCommon_setTime'] = $timeData['EvnUslugaCommon_setTime'];
		}
		if( empty($data['EvnUslugaCommon_setTime']) ) $data['EvnUslugaCommon_setTime'] = $timeData['EvnUslugaCommon_setTime'];
		if( empty($data['EvnUslugaCommon_disDate']) ){
			$data['EvnUslugaCommon_disDate'] = $timeData['EvnUslugaCommon_disDate'];
			$data['EvnUslugaCommon_disTime'] = $timeData['EvnUslugaCommon_disTime'];
		}
		if( empty($data['EvnUslugaCommon_disTime']) ) $data['EvnUslugaCommon_disTime'] = $timeData['EvnUslugaCommon_disTime'];
		// объединим собранные данные
		$data = array_merge($data, $msfData, $uslugaData);
		$checkDate = $this->dbmodel->CheckEvnUslugaDate($data);

		if ( !empty($checkDate[0]['Error_Msg']) ) {
			$this->response(array(
				'success' => false,
				'error_code' => $checkDate[0]['Error_Msg'],
				'error_msg' => $checkDate[0]['Error_Code'])
			);
		}

		$resp = $this->dbmodel->saveEvnUslugaCommon($data);

		if (!is_array($resp))  $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (!empty($resp[0])) $resp = $resp[0];
		if (!empty($resp['Error_Msg'])) {

			$resp = array(
				'success' => false,
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			);

		} else {

			if (!empty($resp['EvnUslugaCommon_id'])) {
				$resp = array_merge(
					array('success'=> true, 'error_code' => 0),
					array('EvnUslugaCommon_id' => $resp['EvnUslugaCommon_id'])
				);
			} else $resp = array('success'=> false, 'error_code' => 6);
		}

		$this->response($resp);
	}

	/**
	 * Получение данных для формы редактирования услуги в зависимости от класса услуги
	 *
	 * @desсription
	 * {
	 *     "input_params": {
				"class": "Класс услуги (required)",
				"evnusluga_id": "Идентификатор услуги (required)"
			},
	 *     "example":{
				"error_code": 0,
				"data": [
					{
						"accessType": "view",
						"EvnUslugaCommon_id": "730022509167211",
						"EvnUslugaCommon_pid": "730022509167210",
						"EvnUslugaCommon_pid_Name": null,
						"EvnUslugaCommon_rid": "730022509167210",
						"Person_id": "2149708",
						"PersonEvn_id": "53277298",
						"Server_id": "0",
						"EvnUslugaCommon_setDate": "27.04.2015",
						"EvnUslugaCommon_setTime": "08:28",
						"EvnUslugaCommon_disDate": "27.04.2015",
						"EvnUslugaCommon_disTime": "08:28",
						"UslugaPlace_id": "1",
						"Lpu_uid": null,
						"Org_uid": null,
						"LpuSection_uid": "99560012292",
						"MedPersonal_id": "87522",
						"MedStaffFact_id": "99560114089",
						"Usluga_id": null,
						"UslugaComplex_id": "4565485",
						"PayType_id": "51",
						"EvnUslugaCommon_Kolvo": 1,
						"UslugaComplexTariff_UED": ".0000",
						"EvnUslugaCommon_Summa": ".0000",
						"EvnUslugaCommon_CoeffTariff": null,
						"EvnUslugaCommon_IsModern": null,
						"EvnUslugaCommon_IsMinusUsluga": null,
						"MesOperType_id": null,
						"UslugaComplexTariff_id": null,
						"DiagSetClass_id": null,
						"Diag_id": null,
						"EvnPrescr_id": null,
						"EvnPrescrTimetable_id": null,
						"MedSpecOms_id": null,
						"LpuSectionProfile_id": "2",
						"EvnDirection_id": null,
						"UslugaCategory_id": null,
						"EvnUslugaCommon_setDT": {
							"date": "2015-04-27 08:28:00.000000",
							"timezone_type": 3,
							"timezone": "UTC"
						},
						"EvnUslugaCommon_disDT": {
							"date": "2015-04-27 08:28:00.000000",
							"timezone_type": 3,
							"timezone": "UTC"
						},
						"UslugaComplex_Code": "B01.047.001",
						"UslugaComplex_Name": "Прием (осмотр, консультация) врача-терапевта первичный"
					}
				]
	 *     }
	 * }
	 */
	function mloadEvnUslugaEditForm_get(){
		$data = $this->ProcessInputData('mloadEvnUslugaEditForm', null, true);
		if($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if(!in_array($data['class'], $this->evnUslugaClasses)){
			$this->response(array(
				'error_code' => 3,
				'error_msg' => 'Неверный класс услуги'
			));
		}

		$data['id'] = $data['evnusluga_id'];// #106230#note-187 назвать поле с ид - evnusluga_id

		$response = $this->dbmodel->loadEvnUslugaEditForm($data);
		if (is_array($response)) {

			$response[0] = array_merge($response[0],
				array(
					'UslugaComplex_Code' => null,
					'UslugaComplex_Name' => null,
					'UslugaComplexTariff_Name' => null
				)
			);

			if (!empty($response[0]['UslugaComplex_id'])) {
				$uc_data = $this->dbmodel->getFirstRowFromQuery("
				select top 1
				 	UslugaComplex_Code,
				 	UslugaComplex_Name
				from v_UslugaComplex (nolock)
				where UslugaComplex_id = :UslugaComplex_id
			", array('UslugaComplex_id' => $response[0]['UslugaComplex_id']));
			}

			if (!empty($uc_data)) {
				$response[0] = array_merge($response[0], $uc_data);
			}

			if (!empty($response[0]['UslugaComplexTariff_id'])) {
				$tariff_data = $this->dbmodel->getFirstRowFromQuery("
				select top 1
				 	UslugaComplexTariff_Name
				from v_UslugaComplexTariff (nolock)
				where UslugaComplexTariff_id = :UslugaComplexTariff_id
				", array('UslugaComplexTariff_id' => $response[0]['UslugaComplexTariff_id']));
			}

			if (!empty($tariff_data)) {
				$response[0] = array_merge($response[0], $tariff_data);
			}
		}

		$this->response(array('error_code' => 0,'data' => $response));
	}
}