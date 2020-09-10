<?php defined('BASEPATH') or die ('No direct script access allowed');


require(APPPATH.'libraries/SwREST_Controller.php');

class Queue extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Queue_model', 'dbmodel');
		$this->inputRules = array(
			'mCancelQueueRecord' => array(
				array('field' => 'EvnComment_Comment', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'EvnQueue_id', 'label' => 'Идентификатор записи в очереди', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules' => '', 'type' => 'id'),
				array('field' => 'QueueFailCause_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnStatusCause_id', 'label' => 'Причина смены статуса', 'rules' => '', 'type' => 'id'),
				array('field' => 'cancelType', 'label' => 'Тип отмены направления', 'rules' => '', 'type' => 'string'),

			),
			'saveRecordRequest' => array(
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'RecMethodType_id', 'label' => 'Идентификатор источника записи', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnDirection_Descr', 'label' => 'Комментарий пациента', 'rules' => '', 'type' => 'string'),
				array('field' => 'User_id', 'label'=> 'Идентификатор учетной записи портала', 'rules' => 'required', 'type' => 'id')
			),
			'cancelRecordRequest' => array(
				array('field' => 'EvnQueue_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'User_id', 'label'=> 'Идентификатор учетной записи портала', 'rules' => 'required', 'type' => 'id')
			),
			'getPersonRecRequest' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
				array('field' => 'person_list', 'label' => 'Список идентификаторов пациента', 'rules' => '', 'type' => 'string'),
				array('field' => 'User_id', 'label'=> 'Идентификатор учетной записи портала', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'declinedRequests', 'label'=> 'Признак только отклоненные заявки', 'rules' => '', 'type' => 'int')
			)
		);
	}

	/**
	 *
	 * @OA\post(
	path="/api/Queue/mCancelQueueRecord",
	tags={"Queue"},
	summary="Отмена записи в очереди по профилю",

	@OA\Parameter(
	name="EvnComment_Comment",
	in="query",
	description="Комментарий",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="EvnQueue_id",
	in="query",
	description="Идентификатор записи в очереди",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnDirection_id",
	in="query",
	description="Идентификатор направления",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="QueueFailCause_id",
	in="query",
	description="Причина отмены направления
	 *     1 - Пролечен амбулаторно
	 *     2 - Госпитализирован экстренно
	 *     3 - Пролечен в другом ЛПУ
	 *     4 - Смерть пациента
	 *     5 - Неверный ввод
	 *     6 - Обслужен вне очереди
	 *     7 - Перенаправлен
	 *     8 - Отказ пациента
	 *     9 - Отсутствуют реагенты
	 *     10 - Отсутствует биоматериал
	 *     11 - Ошибочное направление
	 *     12 - Неявка пациента
	 *     13 - Предложенная бирка не подтверждена
	 *     14 - Отказ от предложенной бирки
	 *     15 - Изменение врача, ведущего прием",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnStatusCause_id",
	in="query",
	description="Причина смены статуса
	 *     1 - Отказ пациента
	 *     2 - Принят вне очереди
	 *     3 - Ошибочное направление
	 *     4 - Неверный ввод
	 *     5 - Смерть пациента
	 *     6 - Нет показаний для госпитализации
	 *     7 - Нет мест для госпитализации
	 *     8 - Нет специалиста на данный момент
	 *     9 - Пролечен амбулаторно
	 *     10 - Госпитализирован экстренно
	 *     11 - Пролечен в другой МО
	 *     12 - Диагноз не соответствует профилю стационара
	 *     13 - Эпидпоказания
	 *     14 - Отсутствуют реагенты
	 *     15 - Отсутствует биоматериал
	 *     16 - Обработка заявки заблокирована
	 *     17 - Перенаправлен
	 *     18 - Неявка пациента
	 *     19 - Направление не обосновано
	 *     20 - Карантин в отделении
	 *     21 - Уход пациента
	 *     22 - Непредоставление необходимого пакета документов
	 *     23 - Констатация факта смерти
	 *      ",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="cancelType",
	in="query",
	description="Тип отмены направления",
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
	property="success",
	description="Результат выполнения",
	type="string",

	)

	)
	)

	)
	 */
	function mCancelQueueRecord_post() {
		$data = $this->ProcessInputData('mCancelQueueRecord', false, true);

		try {
			$response = $this->dbmodel->mCancelQueueRecord($data);
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0 , 'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777 ,'error_msg' => toUtf($e->getMessage()));
		}

		$this->response($response);
	}


	/**
	 * Сохранение заявки на запись на сайте кврачу
	 */
	function saveRecordRequest_post() {

		$data = $this->ProcessInputData('saveRecordRequest');

		$resp = $this->dbmodel->saveRecordRequest($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array('error_code' => 844,'error_msg' => $resp['Error_Msg']));
		}
		$this->response(array('error_code' => 0,'data' => array('EvnDirection_id' => $resp['EvnDirection_id'])));
	}

	/**
	 * Отмена заявки на запись с сайта кврачу
	 */
	function cancelRecordRequest_post() {

		$data = $this->ProcessInputData('cancelRecordRequest');
		$resp = $this->dbmodel->cancelRecordRequest($data);
		
		if (!empty($resp['Error_Msg'])) {
			$this->response(array('error_code' => 845,'error_msg' => $resp['Error_Msg']));
		}
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение списка заявок к врачу
	 */
	function getPersonRecRequest_get() {
		
		$data = $this->ProcessInputData('getPersonRecRequest');
		$resp = $this->dbmodel->getPersonRecRequest($data);
		
		$this->response(array('error_code' => 0,'data' => $resp));
	}
}