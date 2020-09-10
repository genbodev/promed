<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с расписанием
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

class VideoChat extends SwREST_Controller {
    public $inputRules = array(
        'mloadPMUserContactList' => array(
            array('field' => 'query', 'label' => 'Строка поиска', 'rules' => '', 'type' => 'string'),
            array('field' => 'searchInPromed', 'label' => 'Флаг поиска в промеде', 'rules' => '', 'type' => 'checkbox'),
            array('field' => 'Lpu_oid', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
            array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
            array('field' => 'Dolgnost_id', 'label' => 'Идентификатор должности', 'rules' => '', 'type' => 'id'),
            array('field' => 'pmUser_oid', 'label' => 'Идентификатор пользователя', 'rules' => '', 'type' => 'id'),
        ),
        'mAddPMUserContact' => array(
            array('field' => 'pmUserCache_rid', 'label' => 'Идентификатор пользователя контакта', 'rules' => 'required', 'type' => 'id'),
        ),
        'mSendTextMessage' => array(
            array('field' => 'pmUser_gid_list', 'label' => 'Идентификатор получателя', 'rules' => 'required', 'type' => 'json_array'),
            array('field' => 'text', 'label' => 'Текст сообщение', 'rules' => 'required', 'type' => 'string'),
        ),
        'mloadMessageList' => array(
            array('field' => 'pmUser_cid_list', 'label' => 'Идентификатор собеседника', 'rules' => '', 'type' => 'json_array'),
            array('field' => 'beforeDT', 'label' => 'Дата/время', 'rules' => '', 'type' => 'datetime'),
        ),
	    'mgetFileMessage' => array(
		    array('field' => 'id', 'label' => 'Идентификатор сообщения', 'rules' => 'required', 'type' => 'id'),
	    ),
    );

    /**
     * Конструктор
     */
    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->model('VideoChat_model', 'dbmodel');
    }

    /**
     * @OA\get(
    path="/api/rish/VideoChat/mloadPMUserContactList",
    tags={"VideoChat"},
    summary="Получение списка контактов пользователя",

    @OA\Parameter(
    name="query",
    in="query",
    description="Строка поиска",
    required=false,
    @OA\Schema(type="string")
    )
    ,
    @OA\Parameter(
    name="searchInPromed",
    in="query",
    description="Флаг поиска в промеде",
    required=false,
    @OA\Schema(type="string")
    )
    ,
    @OA\Parameter(
    name="Lpu_oid",
    in="query",
    description="Идентификатор МО",
    required=false,
    @OA\Schema(type="integer", format="int64")
    )
    ,
    @OA\Parameter(
    name="LpuSection_id",
    in="query",
    description="Идентификатор отделения",
    required=false,
    @OA\Schema(type="integer", format="int64")
    )
    ,
    @OA\Parameter(
    name="Dolgnost_id",
    in="query",
    description="Идентификатор должности",
    required=false,
    @OA\Schema(type="integer", format="int64")
    )
    ,
    @OA\Parameter(
    name="pmUser_oid",
    in="query",
    description="Идентификатор пользователя",
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
    property="data",
    description="Данные",
    type="array",

    @OA\Items(
    type="object",

    @OA\Property(
    property="pmUser_id",
    description="Идентификатор пользователя",
    type="integer",

    )
    ,
    @OA\Property(
    property="SurName",
    description="Фамилия",
    type="string",

    )
    ,
    @OA\Property(
    property="FirName",
    description="Имя",
    type="string",

    )
    ,
    @OA\Property(
    property="SecName",
    description="Отчество",
    type="string",

    )
    ,
    @OA\Property(
    property="Login",
    description="Логин",
    type="string",

    )
    ,
    @OA\Property(
    property="pmUserContacts_id",
    description="Контакты пользователя, Идентификатор записи",
    type="integer",

    )
    ,
    @OA\Property(
    property="VideoSettings_id",
    description="Идентификатор настроек видео",
    type="integer",

    )
    ,
    @OA\Property(
    property="Avatar",
    description="Аватар",
    type="string",

    )
    ,
    @OA\Property(
    property="Status",
    description="Статус",
    type="string",

    )
    ,
    @OA\Property(
    property="hasCamera",
    description="Наличие камеры",
    type="string",

    )
    ,
    @OA\Property(
    property="LpuList",
    description="Список ЛПУ",
    type="array",

    @OA\Items(
    type="object",

    @OA\Property(
    property="Lpu_id",
    description="справочник ЛПУ, ЛПУ",
    type="integer",

    )
    ,
    @OA\Property(
    property="Lpu_Nick",
    description="Краткое имя ЛПУ",
    type="string",

    )

    )

    )

    )

    )

    )
    )

    )
     */
    function mloadPMUserContactList_get() {
        $data = $this->ProcessInputData('mloadPMUserContactList', false, true);
        if ($data === false) return;
        $response = $this->dbmodel->mloadPMUserContactList($data);
        if (!empty($response['Error_Msg'])) {
            $this->response(array(
                'error_code' => 6,
                'error_msg' => $response['Error_Msg']
            ));
        }
        $this->response(array('success'=> true,'data' => $response));
    }

    /**
    @OA\post(
    path="/api/rish/VideoChat/mAddPMUserContact",
    tags={"VideoChat"},
    summary="Добавление контакта",

    @OA\Parameter(
    name="pmUserCache_rid",
    in="query",
    description="Идентификатор пользователя контакта",
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
    property="data",
    description="Данные",
    type="array",

    @OA\Items(
    type="object",

    @OA\Property(
    property="success",
    description="Результат выполнения",
    type="string",

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


    function mAddPMUserContact_post() {
        $data = $this->ProcessInputData('mAddPMUserContact', false, true);
        if ($data === false) return;
        $response = $this->dbmodel->mAddPMUserContact($data);
        if (!empty($response['Error_Msg'])) {
            $this->response(array(
                'success'=>false,
                'error_code' => 6,
                'error_msg' => $response['Error_Msg']
            ));
        }
        $this->response(array('success'=>true,'data' => $response));
    }


    /**
     @OA\post(
    path="/api/rish/VideoChat/mSendTextMessage",
    tags={"VideoChat"},
    summary="Отправка текстового сообщения",

    @OA\Parameter(
    name="pmUser_gid_list",
    in="query",
    description="Идентификатор получателя",
    required=true,
    @OA\Schema(type="string")
    )
    ,
    @OA\Parameter(
    name="text",
    in="query",
    description="Текст сообщение",
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
    type="string",

    )

    )
    )

    )
     */
    function mSendTextMessage_post() {
        $data = $this->ProcessInputData('mSendTextMessage', false, true);
        if ($data === false) return;
        $response = $this->dbmodel->mSendTextMessage($data);
        if (!empty($response['Error_Msg'])) {
            $this->response(array(
                'success'=>false,
                'error_code' => 6,
                'error_msg' => $response['Error_Msg']
            ));
        }
        $this->response(array('error_code'=>0,'success'=>true));
    }

    /**
    @OA\get(
    path="/api/rish/VideoChat/mloadMessageList",
    tags={"VideoChat"},
    summary="Получить список сообщений",

    @OA\Parameter(
    name="pmUser_cid_list",
    in="query",
    description="Идентификатор собеседника",
    required=false,
    @OA\Schema(type="string")
    )
    ,
    @OA\Parameter(
    name="beforeDT",
    in="query",
    description="Дата\время",
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
    property="id",
    description="Идентификатор",
    type="string",

    )
    ,
    @OA\Property(
    property="pmUser_sid",
    description="Идентификатор собеседника",
    type="string",

    )
    ,
    @OA\Property(
    property="text",
    description="Текст сообщения",
    type="string",

    )
    ,
     @OA\Property(
    property="file_name",
    description="Имя файла",
    type="string",

    )
    ,
    @OA\Property(
    property="dt",
    description="Дата",
    type="string",

    )

    )
    )

    )
     */
    function mloadMessageList_get() {
        $data = $this->ProcessInputData('mloadMessageList', false, true);
        if ($data === false) return;
        $response = $this->dbmodel->mloadMessageList($data);
        if (!empty($response['Error_Msg'])) {
            $this->response(array(
                'success'=>false,
                'error_code' => 6,
                'error_msg' => $response['Error_Msg']
            ));
        }
        $this->response(array('success'=>true,'data' => $response));
    }

	/**
	 * Вывод файла из сообщения
	 */
	function mgetFileMessage_get() {
		$data = $this->ProcessInputData('mgetFileMessage', false,true);
		if ($data === false) return;

		$message = $this->dbmodel->getFileMessage($data);

		if (empty($message) || empty($message['file_path'])) {
			echo 'Файл не найден';
			exit;
		}

		header("Content-type: {$message['file_type']}");
		header("Content-Disposition: attachment; filename={$message['file_name']}");
		header("Content-length: ".filesize($message['file_path']));
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile($message['file_path']);
		$this->response(array('success'=>true,'data' => $message));
	}

}