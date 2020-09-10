<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * @property Queue_model dbmodel
 * @property LpuRegion_model LpuRegion_model
 */
class Queue extends swController {

	public $inputRules = array(
		'loadQueueListGrid' => array(
			array('default' => 100, 'field' => 'limit', 'label' => 'Лимит записей', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальная запись', 'rules' => '', 'type' => 'int'),
			array('default' => 'select', 'field' => 'mode', 'label' => 'Режим просмотра', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор профиля', 'rules' => '', 'type' => 'id'),
			array('field' => 'f_Start_Date', 'label' => 'Начало периода', 'rules' => '', 'type' => 'string'),
			array('field' => 'f_End_Date', 'label' => 'Конец периода', 'rules' => '', 'type' => 'string'),
			array('field' => 'f_DirType_id', 'label' => 'Тип направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'f_LpuSectionProfile_id', 'label' => 'Идентификатор профиля', 'rules' => '', 'type' => 'id'),
			array('field' => 'f_Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'),
			array('field' => 'f_Person_FIO', 'label' => 'ФИО', 'rules' => '', 'type' => 'string'),
			array('field' => 'f_Person_birthDay', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'string')
		),
		'getDataForDirection' => array(
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор записи в очереди', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimetableGraf_id', 'label' => 'Идентификатор бирки', 'rules' => '', 'type' => 'id')
		),
		'loadEvnDirectionEditForm' => array(
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор записи в очереди', 'rules' => 'required', 'type' => 'id')
		),
		'sendToArchive' => array(
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор записи в очереди', 'rules' => 'required', 'type' => 'id')
		),
		'cancelQueueRecord' => array(
			array('field' => 'EvnComment_Comment', 'label' => 'Комментарий', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'EvnQueue_id', 'label' => 'Идентификатор записи в очереди', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'QueueFailCause_id', 'label' => 'Причина отмены направления', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnStatusCause_id', 'label' => 'Причина смены статуса', 'rules' => '', 'type' => 'id'),
			array('field' => 'cancelType', 'label' => 'Тип отмены направления', 'rules' => '', 'type' => 'string')
		),
		'ApplyFromQueue' => array(
			array(
				'field' => 'LpuUnit_did',
				'label' => 'Группа отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnQueue_id',
				'label' => 'Идентификатор записи в очереди',
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
				'field' => 'LpuSection_did',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор бирки поликлиники',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableStac_id',
				'label' => 'Идентификатор бирки стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableMedService_id',
				'label' => 'Идентификатор бирки службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableResource_id',
				'label' => 'Идентификатор бирки службы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirType_id',
				'label' => 'DirType_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'QueueFailCause_id',
				'label' => 'Идентификатор причины отмены',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'mode',
				'label' => 'Режим работы с очередью',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuUnitType_SysNick',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'record',
				'label' => 'Дата записи',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'pmUser',
				'label' => 'Оператор записи в очереди',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Direction_Num',
				'label' => 'Номер направления',
				'rules' => '',
				'type' => 'string'
			)
		),
		'addQueue' => array(
			array(
				'field' => 'Files',
				'label' => 'Приложенные к направлению файлы',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'bookingDateReserveId',
				'label' => 'bookingDateReserveId',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор заказаной параклинической услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnUslugaPar_id',
				'label' => 'Идентификатор заказаной параклинической услуги',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Prescr',
				'label' => 'Признак назначения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор электронного направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPrescr_id',
				'label' => 'Идентификатор назначения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnQueue_pid',
				'label' => 'Идентификатор родительного события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Сервер',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_did',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnit_did',
				'label' => 'Группа отделений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_SysNick',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Resource_id',
				'label' => 'Ресурс',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_did',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_did',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_Code',
				'label' => 'Код врача',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpec_fid',
				'label' => 'Специальность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_did',
				'label' => 'Комплексная услуга',
				'rules' => '',
				'type' => 'id'
			),
            array(
				'field' => 'FSIDI_id',
				'label' => 'Инструментальная диагностика',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_did',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_pzid',
				'label' => 'Пункт забора',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Resource_did',
				'label' => 'Ресурс',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'withResource',
				'label' => 'В очередь на ресурс',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор места работы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				// кто направил, для записи должности врача
				'field' => 'From_MedStaffFact_id',
				'label' => 'Рабочее место врача',
				'rules' => '',
				//'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_sid',
				'label' => 'Рабочее место врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirType_id',
				'label' => 'Тип направления',
				'rules' => '',
				'type' => 'id',
			),
			array(
				'field' => 'ARMType_id',
				'label' => 'Тип АРМа',
				'rules' => '',
				'type' => 'id',
			),
			array(
				'field' => 'addDirection',
				'label' => 'Признак необходимости добавления направления',
				'rules' => '',
				'type' => 'int',
				'default' => 1 // по умолчанию добавляем направление
			),
			array(
				'field' => 'order',
				'label' => 'Информация о заказе',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AnswerRecord',
				'label' => 'Ответ об отмене записи к врачу',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'AnswerQueue',
				'default'=>1,
				'label' => 'Ответ об отмене записи к врачу',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuUnitType_did',
				'label' => 'Тип подразделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Org_oid',
				'label' => 'Ссылка на справочник организаций',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'GetBed_id',
				'label' => 'Профиль койки',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'HIVContingentTypeFRMIS_id',
				'label' => 'Код контингента ВИЧ',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'CovidContingentType_id',
				'label' => 'Код контингента COVID',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'HormonalPhaseType_id',
				'label' => 'Идентификатор фазы цикла',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'IncludeInDirection',
				'label' => 'В какое направление включить текущее',
				'rules' => '',
				'type' => 'string'
			)
		),
		'clearToQueue'=>array(
			array(
				'field'=>'EvnDirection_id',
				'label'=>'Идентификатор направления',
				'rules'=>'required',
				'type'=>'id'
			)
		),
		'checkRecordQueue' => array(
			array(
				'field' => 'MedService_id',
				'label' => 'Идентификатор службы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'queueManager' => array(
			array(
				'field' => 'logging',
				'label' => 'Признак нужно логировать или нет',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'bypassRecordDelay',
				'label' => 'Признак пропуска задержи по времени создания бирки',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'max_accept_time',
				'label' => 'макс. время реакции на подтверждения бирки',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_list',
				'label' => 'Список ЛПУ для, которых необходимо запустить менеджер',
				'rules' => '',
				'type' => 'string'
			),
		),
		'loadWaitingListJournal' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор рабочего места', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnQueueStatus_id', 'label' => 'Идентификатор статуса ЛО', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnQueue_insDT_period', 'label' => 'Период постановки в ЛО', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_BirthDay', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'string'),
			array('field' => 'Polis_EdNum', 'label' => 'Единый номер полиса', 'rules' => '', 'type' => 'int'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Queue_model', 'dbmodel');
	}

	/**
	 * Проверка возможности постановки в очередь
	 */
	function checkRecordQueue()
	{
		$data = $this->ProcessInputData('checkRecordQueue', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkRecordQueue($data);
		if(true === $response){
			$this->ReturnError('Запись в очередь на службу запрещена');
		} else {
			$this->ReturnData(array('success' => true));
		}

		return true;
	}

	/**
	 * постановка человека с бирки в очередь
	 */
	function clearToQueue(){
		$this->load->helper('Reg_helper');
		$this->load->model('EvnDirection_model', 'EvnDirection');
		$data = $this->ProcessInputData('clearToQueue', true);
		if ( $data ) {
			$response = $this->dbmodel->clearToQueue($data);
			$resp = $this->dbmodel->insertQueue($response);
			$this->ProcessModelSave($resp, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Функция сохранения признака "В архив"
	 * Входящие данные: POST['EvnQueue_id']
	 * На выходе: JSON-строка
	 * Используется: рабочее место врача приемного отделения
	 */
	function sendToArchive() {
		$data = $this->ProcessInputData('sendToArchive', true);
		if ( $data ) {
			$response = $this->dbmodel->sendToArchive($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных
	 * Входящие данные: $_POST['EvnQueue_id']
	 * На выходе: JSON-строка
	 * Используется: форма просмотра направления
	 */
	function loadEvnDirectionEditForm() {
		$data = $this->ProcessInputData('loadEvnDirectionEditForm', true);
		if ( $data ) {
			$response = $this->dbmodel->loadEvnDirectionEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение очереди по определенному профилю
	 * Входящие данные: см.выше
	 * На выходе: JSON-строка
	 * Используется: АРМ полки - очередь по профилю (/jscore/Forms/Common/swMPQueueWindow.js)
	 */
	function loadQueueListGrid() {
		$data = $this->ProcessInputData('loadQueueListGrid', true);
		if ( $data ) {
			$response = $this->dbmodel->loadQueueListGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * TO-DO: описать функцию getDataForDirection
	 */
	function getDataForDirection() {
		$data = $this->ProcessInputData('getDataForDirection', true);
		if ( $data ) {
			$response = $this->dbmodel->getDataForDirection($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Отмена записи в очереди по профилю
	 */
	function cancelQueueRecord() {
		$data = $this->ProcessInputData('cancelQueueRecord', true);
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->cancelQueueRecord($data);

		$this->ProcessModelSave($response, true, 'При отмене записи в очереди по профилю возникли ошибки')->ReturnData();
		return true;
	}

		/**
	 * Запись человека на бирку из очереди
	 */
	function ApplyFromQueue() {
		$this->load->helper('Reg_helper');

		if (isset($_POST['pmUser_id'])) $_POST['pmUser'] = $_POST['pmUser_id'];

		$data = $this->ProcessInputData('ApplyFromQueue',true);
		if ($data === false) { return false; }

		if ( isset($data['TimetableGraf_id']) ) {
			$data['object'] = 'TimetableGraf';
		} else if ( isset($data['TimetableStac_id']) ) {
			$data['object'] = 'TimetableStac';
		} else if ( isset($data['TimetableMedService_id']) ) {
			$data['object'] = 'TimetableMedService';
		} else if ( isset($data['TimetableResource_id']) ) {
			$data['object'] = 'TimetableResource';
		}

		if (!isset($data['mode']) || $data['mode'] != 'redir') { //при перенаправлении не требуется проверка на чистоту записи
			$response = $this->dbmodel->checkQueueRecordFree($data); //проверяем не назначали уже запись из очереди
			if ($response !== true) {
				$this->ReturnData($response);
				return;
			}
		}

		$this->dbmodel->beginTransaction();
		try {
			if (isset($data['QueueFailCause_id']) && $data['QueueFailCause_id'] > 0) {
				//если есть причина отмены, например запись через перенаправление
				// Отменяем запись в очереди
				$res = $this->dbmodel->cancelQueueRecord($data);

				if ( isset($res['success'] ) && $res['success'] === false) {
					throw new Exception($res[0]['Error_Msg']);
				}
			} else {
				// Помечаем запись в очереди записанной
				$this->load->model('EvnDirection_model');
				$res = $this->EvnDirection_model->applyEvnDirectionFromQueue($data);
			}

			// И записываем ее на бирку
			$this->load->model('Timetable_model', 'ttmodel');
			$this->ttmodel->Apply($data);

			if ( isset($res['success'] ) && $res['success'] === false) {
				throw new Exception($res['Error_Msg']);
			}

		} catch (Exception $e) {
			$this->dbmodel->rollbackTransaction();
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка при записи из очереди: ' . $e->getMessage()));

			return false;
		}

		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}

	/**
	 * Выписка направления в очередь
	 */
	function addQueue($data=null) {
		$this->load->helper('Reg_helper');
		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->inputRules['Queue'] = array_merge($this->inputRules['addQueue'], $this->EvnDirection->getSaveRules(array(
			'lpuSectionProfileNotRequired' => true
		)));

		$data = $this->ProcessInputData('Queue', true);
		if ($data === false) { return false; }

		if (empty($data['LpuSectionProfile_id']) && !in_array($data['DirType_id'], [6,9,26]) && !in_array(getRegionNick(), array('astra', 'ekb'))) { // для ВК профиль не обязателен (refs #83337)
			$this->ReturnError('Поле "Профиль" обязательно для заполнения');
			return false;
		}

		$response = $this->dbmodel->insertQueue($data);

		$this->dbmodel->transferQueue($data, $response);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Задание для крона, на автоматическое обслуживание очереди
	 */
	function queueManager() {

		$data = $this->ProcessInputData('queueManager', true);
		$response = $this->dbmodel->queueManager($data);
		return true;
	}

	/**
	 * Загрузка журнала листов ожидания
	 */
	function loadWaitingListJournal() {

		$data = $this->ProcessInputData('loadWaitingListJournal', true);
		if ($data) {
			$response = $this->dbmodel->loadWaitingListJournal($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
