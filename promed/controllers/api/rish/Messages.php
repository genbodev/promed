<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Messages - контроллер API для работы с сообщениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package			API
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Messages extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();

		//$this->checkAuth();
		$this->load->database();
		$this->load->model('Messages_model', 'dbmodel');
	}

	protected $inputRules = array(
		'msetMessagesIsReaded' => array(
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id')
		),
		'mgetMessagesList' => array(
			array('field' => 'start', 'label' => 'Позиция просматриваемого сообщения', 'rules' => 'required|zero', 'type' => 'int'),
			array('field' => 'limit', 'label' => 'Кол-во подгружаемых сообщений', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'query', 'label' => 'Строка запроса', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnClass_SysNick', 'label' => 'Системное имя события', 'rules' => '', 'type' => 'string')
		),
		'mSendPush' => array(
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Message_id', 'label' => 'Идентификатор сообщения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Users_list', 'label' => 'Список идентификаторов(передается через запятую)', 'rules' => 'required', 'type' => 'string')
		),
		'mSetTimeWorkTime' => array(
			array('field' => 'pmUser_tid', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeJournal_BegDT', 'label' => 'Дата начала смены', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'TimeJournal_EndDT', 'label' => 'Дата окончания смены', 'rules' => 'required', 'type' => 'datetime'),
		),
		'mCheckActiveWorkTime' => array(
			array('field' => 'pmUser_tid', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'),
		),
		'mSetEndDTWorkTime' => array(
			array('field' => 'pmUser_tid', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'),
		),
	);


	/**
	 * @OA\get(
	path="/api/messages/mgetMessagesList",
	tags={"messages"},
	summary="Получение списка сообщений",

	@OA\Parameter(
	name="start",
	in="query",
	description="Позиция просматриваемого сообщения",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="limit",
	in="query",
	description="Кол-во подгружаемых сообщений",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="query",
	in="query",
	description="Строка запроса",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnClass_Name",
	in="query",
	description="Имя события",
	required=false,
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
	property="totalCount",
	description="Общее количество",
	type="string",

	)
	,
	@OA\Property(
	property="overLimit",
	description="Описание",
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
	property="Evn_id",
	description="Событие, Идентификатор события",
	type="integer",

	)
	,
	@OA\Property(
	property="Message_id",
	description="Сообщения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Message_pid",
	description="Сообщения, идентификатор родительского сообщения",
	type="string",

	)
	,
	@OA\Property(
	property="Message_setDT",
	description="Дата сообщения",
	type="string",

	)
	,
	@OA\Property(
	property="UserSend_ID",
	description="Идентификатор отправителя",
	type="string",

	)
	,
	@OA\Property(
	property="Message_Subject",
	description="Заголовок сообщения",
	type="string",

	)
	,
	@OA\Property(
	property="Message_Text",
	description="Сообщения, текст",
	type="string",

	)
	,
	@OA\Property(
	property="Message_isRead",
	description="Флаг прочтения сообщения",
	type="string",

	)
	,
	@OA\Property(
	property="MessageRecipient_id",
	description="Идентификатор получателя сообщения",
	type="integer",

	)
	,
	@OA\Property(
	property="UserRecipient_id",
	description="Идентификатор получателя сообщения",
	type="integer",

	)
	,
	@OA\Property(
	property="Message_isFlag",
	description="Флаг сообщения",
	type="string",

	)
	,
	@OA\Property(
	property="NoticeType_id",
	description="Тип новости",
	type="integer",

	)
	,
	@OA\Property(
	property="EvnClass_Name",
	description="класс события, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="PersonEvn_id",
	description="События по человеку, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Server_id",
	description="Идентификатор сервера",
	type="integer",

	)
	,
	@OA\Property(
	property="Evn_rid",
	description="Событие, получатель документа",
	type="string",

	)
	,
	@OA\Property(
	property="EvnClass_SysNick",
	description="класс события, системное наименование",
	type="string",

	)
	,
	@OA\Property(
	property="EvnClass_rSysNick",
	description="Краткое наименование события",
	type="string",

	)
	,
	@OA\Property(
	property="Person_id",
	description="Справочник идентификаторов человека, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Person_Fio",
	description="ФИО",
	type="string",

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
	function mgetMessagesList_get(){
		$data = $this->ProcessInputData('mgetMessagesList', null, true);
		$response = $this->dbmodel->getMessagesListData($data);// не возвращает false или ошибку

		$ret = array(
			'error_code' => 0
		);

		if(!isset($response['totalCount'])){// маловероятно, что не будет totalCount, но всё же
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		else{
			if($response['totalCount'] > 0 && !empty($response['totalCount'])){
				$ret['totalCount'] = $response['totalCount'];
				if(isset($response['overLimit'])) $ret['overLimit'] = $response['overLimit'];
				$ret['data'] = $response['data'];
			}else{
				$ret['totalCount'] = 0;
				$ret['data'] = array();
			}
		}

		$this->response($ret);
	}


	/**
 * Сделать все сообщения прочитанными
 */
	function msetMessagesIsReaded_get(){
		$data = $this->ProcessInputData('msetMessagesIsReaded', null, true);
		$response = $this->dbmodel->setMessagesIsReaded($data);// возвращает только true (есть обновление строк) и false
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	@OA\post(
	path="/api/rish/Messages/mSendPush",
	tags={"Messages"},
	summary="Отправка уведомлений",

	@OA\Parameter(
	name="pmUser_id",
	in="query",
	description="Идентификатор пользователя",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Message_id",
	in="query",
	description="Идентификатор сообщения",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Users_list",
	in="query",
	description="Список идентификаторов(передается через запятую)",
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
	property="data",
	description="Данные",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="Error_Code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="Error_Msg",
	description="Сообщение об ошибке",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mSendPush_post(){
		$data = $this->ProcessInputData('mSendPush', null, true);
		try {
			$Users_list = explode(",",$data['Users_list']);
			foreach ($Users_list as $user) {
				$data['UserRecipient_id'] = $user;
				$response = $this->dbmodel->mSendPush($data);// возвращает только true (есть обновление строк) и false
				if (!empty($response['Error_Msg'])) {
					throw new Exception($response['Error_Msg'], $response['Error_Code']);
				}
			}
		} catch (Exception $e) {
			$response = array('error_code' => toUtf($e->getCode()) ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response(array('error_code'=>0, 'data'=>$response));
	}

	/**
	@OA\post(
	path="/api/rish/Messages/mSetTimeWorkTime",
	tags={"Messages"},
	summary="Установить время смены",

	@OA\Parameter(
	name="pmUser_tid",
	in="query",
	description="Идентификатор пользователя",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="TimeJournal_BegDT",
	in="query",
	description="Дата начала смены",
	required=true,
	@OA\Schema(type="string", format="date")
	)
	,
	@OA\Parameter(
	name="TimeJournal_EndDT",
	in="query",
	description="Дата окончания смены",
	required=true,
	@OA\Schema(type="string", format="date")
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
	property="TimeJournal_id",
	description="Журнал учета рабочего времени сотрудников, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="Error_Code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="Error_Msg",
	description="Сообщение об ошибке",
	type="string",

	)

	)

	)

	)
	)

	)

	 */

	function mSetTimeWorkTime_post() {
		$data = $this->ProcessInputData('mSetTimeWorkTime', null, true);
		try {
			$response = $this->dbmodel->mSetTimeWorkTime($data);// возвращает только true (есть обновление строк) и false
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], $response['Error_Code']);
			}
		} catch (Exception $e) {
			$response = array('error_code' => toUtf($e->getCode()) ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response(array('error_code'=>0, 'data'=>$response));
	}

	/**
	 * @OA\get(
	path="/api/rish/Messages/mCheckActiveWorkTime",
	tags={"Messages"},
	summary="Проверка активной смены",

	@OA\Parameter(
	name="pmUser_tid",
	in="query",
	description="Идентификатор пользователя",
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
	property="TimeJournal_id",
	description="Журнал учета рабочего времени сотрудников, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="response",
	description="Ответ",
	type="string",

	)

	)

	)

	)
	)

	)
	 */

	function mCheckActiveWorkTime_get(){
		$data = $this->ProcessInputData('mCheckActiveWorkTime', null, true);
		$response = $this->dbmodel->mCheckActiveWorkTime($data);// возвращает только true (есть обновление строк) и false
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	@OA\post(
	path="/api/rish/Messages/mSetEndDTWorkTime",
	tags={"Messages"},
	summary="Установка окончания смены",

	@OA\Parameter(
	name="pmUser_tid",
	in="query",
	description="Идентификатор пользователя",
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

	)
	)

	)
	 */

	function mSetEndDTWorkTime_post() {
		$data = $this->ProcessInputData('mSetEndDTWorkTime', null, true);
		try {
			$response = $this->dbmodel->mSetEndDTWorkTime($data);// возвращает только true (есть обновление строк) и false

			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], $response['Error_Code']);
			}
		} catch (Exception $e) {
			$response = array('error_code' => toUtf($e->getCode()) ,'error_msg' => toUtf($e->getMessage()));
		}
		$this->response(array('error_code'=>0, 'data'=>$response));
	}
}
