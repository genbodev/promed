<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class ElectronicTalon
 */
class ElectronicTalon extends SwRest_Controller {
	protected $inputRules = array(
		'cancelElectronicTalonByEvnDirection' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => 'required', 'type' => 'id'),
		),
		'sendElectronicTalonMessage' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDirection_TalonCode', 'label' => 'Код бронирования', 'rules' => 'required', 'type' => 'string'),
		),
		'getGridElectronicQueueData' => array(
			array('field' => 'DirectionList', 'label' => 'Список идентификаторов направлений', 'rules' => 'required', 'type' => 'array'),
			array('field' => 'ElectronicTalon_Num', 'label' => 'Номер талона ЭО', 'rules' => '', 'type' => 'string'),
			array('field' => 'ElectronicTalonPseudoStatus_id', 'label' => 'Идентификатор псевдостатуса', 'rules' => '', 'type' => 'int')
		),
		'redirectElectronicTalon' => array(
			array(
				'field' => 'ElectronicTalon_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicService_id',
				'label' => 'Идентификатор пункта обслуживания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplexMedService_id',
				'label' => 'Идентификатор услуги на службе',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedServiceType_SysNick',
				'label' => 'Краткое наименование типа службы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор текущего направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления для перенаправления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Идентификатор профиля отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'From_MedStaffFact_id',
				'label' => 'Идентификатор текущего места работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор персонала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'redirectBack',
				'label' => 'Признак вовзрата талона на предыдущий ПО',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор ЭО куда переадресовали',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'order',
				'label' => 'Заказ на услугу',
				'rules' => '',
				'type' => 'string'
			)
		),
		'sendElectronicQueueNodeMessage' => array(
			array('field' => 'message', 'label' => 'Сообщение', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'ElectronicTalon_id', 'label' => 'Идентификатор талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicService_id', 'label' => 'Идентификатор сервиса', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicQueueInfo_id', 'label' => 'Идентификатор ЭО', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTreatment_id', 'label' => 'Идентификатор повода', 'rules' => '', 'type' => 'id'),
			array('field' => 'msfReplaceElectronicService_id', 'label' => 'Идентификатор ПО зам. врача', 'rules' => '', 'type' => 'id'),
		),
		'setElectronicTalonStatus' => array(
			array('field' => 'ElectronicTalon_id', 'label' => 'Идентификатор талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicService_id', 'label' => 'Идентификатор ПО', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTalonStatus_id', 'label' => 'Идентификатор статуса талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя системы', 'rules' => '', 'type' => 'id'),
			array('field' => 'cancelCallCount', 'label' => 'число вызовов до отмены', 'rules' => '', 'type' => 'int'),
		),
		'finishCall' => array(
			array('field' => 'ElectronicTalon_id', 'label' => 'Идентификатор талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicService_id', 'label' => 'Идентификатор ПО', 'rules' => '', 'type' => 'id'),
			array('field' => 'ElectronicTalonStatus_id', 'label' => 'Идентификатор статуса талона', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя системы', 'rules' => '', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('ElectronicTalon_model', 'dbmodel');
	}

	/**
	 * @OA\Get(
	 * 		path="/api/ElectronicTalon/cancelByEvnDirection",
	 *     	tags={"ElectronicTalon"},
	 *     	summary="Отмена электронного талона по направлению",
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_id",
	 *     		in="query",
	 *     		description="Идентификатор направления",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function cancelByEvnDirection_post() {
		$data = $this->ProcessInputData('cancelElectronicTalonByEvnDirection', null, true);
		$response = $this->dbmodel->cancelElectronicTalonByEvnDirection($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * @OA\Get(
	 * 		path="/api/ElectronicTalon/sendMessage",
	 *     	tags={"ElectronicTalon"},
	 *     	summary="Отправка сообщения",
	 * 		@OA\Parameter(
	 *     		name="Person_id",
	 *     		in="query",
	 *     		description="Идентификатор человека",
	 *     		required=true,
	 *     		@OA\Schema(type="integer", format="int64")
	 * 	   	),
	 * 		@OA\Parameter(
	 *     		name="EvnDirection_TalonCode",
	 *     		in="query",
	 *     		description="Код бронирования",
	 *     		required=true,
	 *     		@OA\Schema(type="string")
	 * 	   	),
	 * 		@OA\Response(
	 *     		response="200",
	 *     		description="JSON response",
	 *     		@OA\JsonContent()
	 * 	   	)
	 * )
	 */
	function sendMessage_post() {
		$data = $this->ProcessInputData('sendElectronicTalonMessage', null, true);
		$response = $this->dbmodel->sendElectronicTalonMessage($data);
		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * получение данных по ЭО для грида
	 */
	function getGridElectronicQueueData_post() {

		$data = $this->ProcessInputData('getGridElectronicQueueData', null);
		$response = $this->dbmodel->getGridElectronicQueueData($data);

		if (!is_array($response)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Переадресация талона ЭО
	 */
	function redirectElectronicTalon_post() {

		$data = $this->ProcessInputData('redirectElectronicTalon');
		$resp = $this->dbmodel->redirectElectronicTalon($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0, 'data' => $resp));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function sendElectronicQueueNodeMessage_post() {

		$data = $this->ProcessInputData('sendElectronicQueueNodeMessage', null, false, false);

		$resp = $this->dbmodel->sendElectronicQueueNodeMessage($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function setElectronicTalonStatus_post() {

		$data = $this->ProcessInputData('setElectronicTalonStatus', null, false, false);

		$resp = $this->dbmodel->setElectronicTalonStatus($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array('error_code' => 0));
	}

	/**
	 * Рассылка сообщения для ЭО
	 */
	function finishCall_post() {

		$data = $this->ProcessInputData('finishCall', null, false, false);

		$this->load->model('ElectronicQueue_model');
		$result = $this->ElectronicQueue_model->finishCall($data);

		if (!empty($result['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $result['Error_Msg'],
				'error_code' => '6'
			));
		}

		$response_result = array('error_code' => 0);
		if (!empty($result['nextCab'])) {
			$response_result['nextCab'] = $result['nextCab'];
		}

		$this->response($response_result);
	}
}