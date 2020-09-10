<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PacketPrescr - пакетами назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @version			07.05.2018
 */
require(APPPATH.'libraries/SwREST_Controller.php');
class PacketPrescr extends SwREST_Controller
{
	public $inputRules = array(
		'mLoadPacketPrescrList' => array(
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => '', 'type' => 'id'),
			array('field' => 'mode', 'label' => 'Режим загрузки', 'rules' => '', 'type' => 'string'),
			array('field' => 'onlyFavor', 'label' => 'Выбрать только избранные пакеты', 'rules' => '', 'type' => 'boolean'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'query', 'label' => 'строка фильтра', 'rules' => '', 'type' => 'string'),
			array('field' => 'Sex_Code', 'label' => 'Пол', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonAgeGroup_Code', 'label' => 'Возрастная группа', 'rules' => '', 'type' => 'id')
		),
		'mSavePacketPrescrForm' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => '', 'type' => 'int'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'mode', 'label' => 'Режим сохранения/применения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'save_data', 'label' => 'Выделенные назначения', 'rules' => '', 'type' => 'string'),
		),
		'mGetPacketPrescrData' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'int'),
		),
		'mSetPacketFavorite' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Packet_IsFavorite', 'label' => '', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type'  => 'id'),
		),
	);

	/**
	 * PacketPrescr constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('PacketPrescr_model', 'dbmodel');
	}

	/**
	 * @OA\get(
	path="/api/PacketPrescr/mLoadPacketPrescrList",
	tags={"PacketPrescr"},
	summary="Получение списка пакетов назначений",

	@OA\Parameter(
	name="Diag_id",
	in="query",
	description="Диагноз",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Evn_id",
	in="query",
	description="Идентификатор случая",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="mode",
	in="query",
	description="Владелец пакета
	 *     my-мои пакеты
	 *     shared - пакеты общего пользования",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="onlyFavor",
	in="query",
	description="Выбрать только избранные пакеты
	 *     true - только избранные пакеты
	 *     false - все пакеты",
	required=false,
	@OA\Schema(type="boolean")
	)
	,
	@OA\Parameter(
	name="MedPersonal_id",
	in="query",
	description="Идентификатор сотрудника",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="query",
	in="query",
	description="строка фильтра",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Sex_Code",
	in="query",
	description="Пол",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonAgeGroup_Code",
	in="query",
	description="Возрастная группа",
	required=false,
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
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Массив выходных данных",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="PacketPrescr_id",
	description="Пакетное назначение, Идентификатор записи",
	type="integer",

	)
	,
	@OA\Property(
	property="PacketPrescr_Name",
	description="Пакетное назначение, Наименование записи",
	type="string",

	)
	,
	@OA\Property(
	property="PacketPrescr_Descr",
	description="Описание пакетного назначения",
	type="string",

	)
	,
	@OA\Property(
	property="Diag_Codes",
	description="Код диагноза",
	type="string",

	)
	,
	@OA\Property(
	property="Packet_IsFavorite",
	description="Идентификатор избранного пакетного назначения",
	type="boolean",

	)
	,
	@OA\Property(
	property="PacketPrescrVision_id",
	description="Область видимости стандарта лечения, Идентификатор записи",
	type="integer",

	)
	,
	@OA\Property(
	property="PacketPrescrVision_Name",
	description="Область видимости стандарта лечения, Наименование записи",
	type="string",

	)
	,
	@OA\Property(
	property="PersonAgeGroup_Name",
	description="Справочник возрастных категорий, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Sex_Name",
	description="Справочник половых признаков, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PacketPrescr_updDT",
	description="Дата обновления записи",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mLoadPacketPrescrList_get() {

		$data = $this->ProcessInputData('mLoadPacketPrescrList', false, true);
		$result = $this->dbmodel->loadPacketPrescrList($data);
		$response = array('error_code' => 0, 'data' => $result);
		$this->response($response);
	}

	/**
	 *
	 * @OA\post(
	path="/api/PacketPrescr/mSavePacketPrescrForm",
	tags={"PacketPrescr"},
	summary="Сохраняет назначения выделенные в шаблоне",

	@OA\Parameter(
	name="PacketPrescr_id",
	in="query",
	description="Идентификатор пакета",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="save_data",
	in="query",
	description="Выделенные назначения",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="parentEvnClass_SysNick",
	in="query",
	description="Системное имя события, породившего назначение",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="Evn_pid",
	in="query",
	description="Идентификатор события, породившего назначение",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="PersonEvn_id",
	in="query",
	description="Идентификатор события по человеку",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Server_id",
	in="query",
	description="Идентификатор сервера",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="mode",
	in="query",
	description="Режим сохранения/применения
	 *     apply
	 *     savePacket
	 *     applyAllPacket",
	required=true,
	@OA\Schema(type="string")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="boolean",

	)

	)
	)

	)
	 */
	function mSavePacketPrescrForm_post() {
		$data = $this->ProcessInputData('mSavePacketPrescrForm', false, true);

		$data['Server_id'] = $this->dbmodel->getServerId(array('PersonEvn_id'=>$data['PersonEvn_id']));

		$res['save_data'] = json_decode($data['save_data'], true);
		$res['PacketPrescr_id'] = $data['PacketPrescr_id'];
		
		$result = array();
		$result['funcdiag'] = $this->dbmodel->getFuncDiagData($res);
		$result['drug'] = $this->dbmodel->getDrugData($res);
		$result['labdiag'] = $this->dbmodel->getLabDiagData($res);
		$result['proc'] = $this->dbmodel->getProcData($res);
		$result['consusl'] = $this->dbmodel->getConsuslData($res);
		$result['regime'] = $this->dbmodel->getRegimeData($res);
		$result['diet'] = $this->dbmodel->getDietData($res);
		$data['save_data'] = json_encode($result, true);

			if (!empty($data['save_data'])) {
				$data['encode_data'] = json_decode($data['save_data'], true);
			}
			switch ($data['mode']) {
				case 'apply':
					$response = $this->dbmodel->applyPacketPrescr($data);
					break;
				case 'savePacket':
					$response = $this->dbmodel->editPacketPrescr($data);
					break;
				case 'applyAllPacket':
				default:
					$response = $this->dbmodel->applyAllPacketPrescr($data);
			}
			if (!empty($response[0]['Error_Msg'])) {
				throw new Exception($response[0]['Error_Msg']);
			}

			$response = array('error_code' => 0 ,'success' => true);


		$this->response($response);
	}

	/**
	@OA\get(
	path="/api/PacketPrescr/mGetPacketPrescrData",
	tags={"PacketPrescr"},
	summary="Получние состава исследований пакета назначений",

	@OA\Parameter(
	name="PacketPrescr_id",
	in="query",
	description="Идентификатор пакета",
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
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Данные",
	type="array",


	@OA\Items(
	type="object",

	@OA\Property(
	property="name",
	description="Наименование исследования",
	type="string",

	)
	,
	@OA\Property(
	property="content",
	description="Массив данных",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="id",
	description="Идентификатор параметра",
	type="string",

	)
	,
	@OA\Property(
	property="name",
	description="Имя параметра",
	type="string",

	)

	)

	)

	)

	)

	)

	)

	)
	)

	)
	 */

	function mGetPacketPrescrData_get() {
		$data = $this->ProcessInputData('mGetPacketPrescrData', false, true);
		$result = $this->dbmodel->mGetPacketPrescrData($data);
		$response = array('error_code' => 0, 'data'=>$result);
		$this->response($response);
	}

	/**
	 * @OA\post(
	path="/api/PacketPrescr/mSetPacketFavorite",
	tags={"PacketPrescr"},
	summary="Поменять признак избранности пакета назначений",

	@OA\Parameter(
	name="PacketPrescr_id",
	in="query",
	description="Идентификатор пакета",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Packet_IsFavorite",
	in="query",
	description="Признак избранности пакета
	 *     0-убрать избранность
	 *     2-установить избранность",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedPersonal_id",
	in="query",
	description="Идентификатор сотрудника",
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
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="error_msg",
	description="Сообщение об ошибке",
	type="string",

	)
	,
	@OA\Property(
	property="success",
	description="Признак успешности выполнения
	 *     true- запрос выполнился
	 *     false- не выполнился",
	type="string",

	)

	)
	)

	)
	 */

	function mSetPacketFavorite_post(){
		$data = $this->ProcessInputData('mSetPacketFavorite',false, true);
		try {
			$response = $this->dbmodel->mSetPacketFavorite($data);
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0, 'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()), 'success' => false);
		}
		$this->response($response);
	}
}