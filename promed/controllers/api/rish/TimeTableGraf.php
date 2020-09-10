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

class TimeTableGraf extends SwREST_Controller {
	protected  $inputRules = array(
		'TimeTableGrafListbyMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_beg','label' => 'Дата и время начала диапазона','rules' => 'required','type' => 'datetime'),
			array('field' => 'TimeTableGraf_end','label' => 'Дата и время начала диапазона','rules' => 'required','type' => 'datetime')
		),
		'TimeTableGrafByMedStaffFact' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_beg','label' => 'Дата и время начала диапазона','rules' => 'required','type' => 'datetime'),
			array('field' => 'TimeTableGraf_end','label' => 'Дата и время окончания диапазона','rules' => 'required','type' => 'datetime'),
			array('field' => 'extended', 'label' => '', 'rules' => '', 'type' => 'int')
		),
		'mTimeTableGrafByMedStaffFact' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_begDate','label' => 'Дата и время начала диапазона','rules' => 'required','type' => 'date'),
			array('field' => 'TimeTableGraf_endDate','label' => 'Дата и время окончания диапазона','rules' => 'required','type' => 'date')
		),
		'TimeTableGrafFreeDate' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_beg','label' => 'Дата начала диапазона','rules' => 'required','type' => 'date'),
			array('field' => 'TimeTableGraf_end','label' => 'Дата окончнания диапазона','rules' => 'required','type' => 'date'),
			array('field' => 'freeforinternetrecord','label' => 'Только разрешенные для записи бирки','rules' => '','type' => 'boolean', 'default' => 0)
		),
		'TimeTableGrafFreeTime' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_begTime','label' => 'Свободная дата приема','rules' => 'required','type' => 'date')
		),
		'TimeTableGrafWrite' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_id','label' => 'Идентификатор свободной бирки','rules' => 'required','type' => 'id'),
			array('field' => 'EvnQueue_id','label' => 'Идентификатор постановки в очередь','rules' => '','type' => 'id')
		),
		'TimeTableGrafbyMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_beg','label' => 'Дата начала диапазона','rules' => 'required','type' => 'date'),
			array('field' => 'TimeTableGraf_end','label' => 'Дата окончания диапазона','rules' => 'required','type' => 'date')
		),
		'TimeTableGrafById' => array(
			array('field' => 'TimeTableGraf_id','label' => 'Идентификатор бирки','rules' => 'required','type' => 'id'),
		),
		'TimeTableGraf_post' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGrafCreate','label' => 'Массив данных для создания бирок','rules' => 'required','type' => 'json_array')
		),
		'TimeTableGraf_put' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGrafEdit','label' => 'Массив данных для редактирования бирок','rules' => 'required','type' => 'array')
		),
		'TimeTableGrafStatus_get' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_id', 'label' => 'Идентификатор бирки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'extended', 'label' => '', 'rules' => '', 'type' => 'int')
		),
		'TimeTableGrafStatus_put' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TimeTableGraf_id', 'label' => 'Идентификатор бирки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStatus_id', 'label' => 'Идентификатор статуса направления', 'rules' => 'required', 'type' => 'id')
		),
		'TimeTableGrafByUpdPeriod_get' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'TimeTableGraf_updbeg', 'label' => 'Дата начала периода изменений', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'TimeTableGraf_updend', 'label' => 'Дата окончания периода изменений', 'rules' => 'required', 'type' => 'date'),
		),
		'mcreateTTGSchedule' => array(
			array(
				'field' => 'CreateDateRange_begDate',
				'label' => 'Дата начала приёмов',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'CreateDateRange_endDate',
				'label' => 'Дата конца приёмов',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор врача',
				'rules' => 'required',
				'type' => 'id',
				//'session_value' => 'CurMedStaffFact_id'
			),
			array(
				'field' => 'StartTime',
				'label' => 'Начало приёма',
				'rules' => '',
				'type' => 'time'
			),
			array(
				'field' => 'EndTime',
				'label' => 'Конец приёма',
				'rules' => '',
				'type' => 'time',
			),
			array(
				'field' => 'Duration',
				'label' => 'Длительность бирки',
				'rules' => 'required',
				'minValue' => '1',
				'maxValue' => '120',
				'type' => 'int'
			),
			array(
				'field' => 'TimetableType_id',
				'label' => 'Тип бирки',
				'rules' => '',
				'type' => 'int'
			),
			// Примечания на бирках в новом формате
			array(
				'field' => 'AnnotationType_id',
				'label' => 'Тип примечания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AnnotationVison_id',
				'label' => 'Видимость примечания',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Annotation_Comment',
				'label' => 'Текст примечания',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Annotation_begTime',
				'label' => 'Время действия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Annotation_endTime',
				'label' => 'Время действия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ignore_doubles',
				'label' => 'Игнорировать дубли',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CreateAnnotation',
				'label' => 'Создать примечание',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'copyAnnotationGridData',
				'label' => 'Данные для копирования',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'ScheduleCreationType',
				'label' => 'Тип создания расписания',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			),
		),
		'mApply' => array(
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор бирки',
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
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'ЛПУ кем направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DirType_id',
				'label' => 'Идентификатор типа направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_pid',
				'label' => 'Идентификатор родительского направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор того кто записывает',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Unscheduled',
				'label' => 'Флаг отсутвия расписания(незапланированная запись)',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'AnswerQueue',
				'label' => 'варнинг если есть запись в очереди',
				'rules' => '',
				'type' => 'int',
				'default' => 0 // пока уберем
			),
			array(
				'field' => 'From_MedStaffFact_id',
				'label' => 'Идентификатор записывающего врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'addDirection',
				'label' => 'Признак необходимости добавления направления',
				'rules' => '',
				'type' => 'int',
				'default' => 1 // по умолчанию добавляем направление
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OverrideWarning',
				'label' => 'признак пропуска ошибок при записи',
				'rules' => '',
				'type' => 'boolean'
			),
		),
		'mClear' => array(
			array(
				'field' => 'cancelType',
				'default' => 'cancel',
				'label' => 'Тип отмены направления',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор бирки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableStac_id',
				'label' => 'Идентификатор бирки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuUnitType_SysNick',
				'default' => 'polka',
				'label' => 'Тип',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DirFailType_id',
				'label' => 'Причина отмены направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatusCause_id',
				'label' => 'Причина смены статуса',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnComment_Comment',
				'label' => 'Комментарий',
				'rules' => 'trim',
				'type' => 'string'
			)
		),
        'getRecord' =>array(
            array('field' => 'TimeTableGraf_id', 'label' => 'Индетификатор бирки', 'rules' => 'required', 'type' => 'id'),
        )
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('TimetableGraf_model', 'dbmodel');
	}

	/**
	 * Получение списка записанных в МО
	 */
	function TimeTableGrafListbyMO_get() {
		$data = $this->ProcessInputData('TimeTableGrafListbyMO');

		$resp = $this->dbmodel->loadTimeTableGrafListbyMO($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка записанных к врачу
	 */
	public function TimeTableGrafByMedStaffFact_get() {
		$data = $this->ProcessInputData('TimeTableGrafByMedStaffFact');

		$resp = $this->dbmodel->loadTimeTableGrafByMedStaffFact($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка записанных к врачу для мобильного АРМ полки
	 */
	public function mTimeTableGrafByMedStaffFact_get() {

		$this->load->helper('Reg');
		$data = $this->ProcessInputData('mTimeTableGrafByMedStaffFact', null, true);

		$data['begDate'] = $data['TimeTableGraf_begDate'];
		$data['endDate'] = $data['TimeTableGraf_endDate'];
		$data['forMobileArm'] = true;

		$this->load->model('TimetableGraf6E_model');

		$resp = $this->TimetableGraf6E_model->loadPolkaWorkPlaceList($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		if (!empty($resp)) {
			foreach ($resp as &$tt) {
				if (!isset($tt['IsEvnDirection'])) $tt['IsEvnDirection'] = 'false';
			}
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение списка записанных в МО
	 */
	function TimeTableGrafFreeDate_get() {
		$data = $this->ProcessInputData('TimeTableGrafFreeDate');

		$resp = $this->dbmodel->getTimeTableGrafFreeDate($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение свободного времени приема
	 */
	function TimeTableGrafFreeTime_get() {
		$data = $this->ProcessInputData('TimeTableGrafFreeTime');

		$resp = $this->dbmodel->getTimeTableGrafFreeTime($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Запись пациента на приём
	 */
	function TimeTableGrafWrite_post() {
		$data = $this->ProcessInputData('TimeTableGrafWrite', null, true);
		
		if ($this->checkPersonId($data['Person_id']) === false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Пациент не найден в системе'));
		}
		
		if (false !== $this->dbmodel->getFirstResultFromQuery("select TimeTableGraf_id from v_TimeTableGraf_lite where TimeTableGraf_id = :TimeTableGraf_id and Person_id = :Person_id", $data)) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Человек уже записан на данную бирку'));
		}
		
		if (false === $this->dbmodel->getFirstResultFromQuery("select TimeTableGraf_id from v_TimeTableGraf_lite where TimeTableGraf_id = :TimeTableGraf_id and Person_id is null", $data)) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Не найдена свободная бирка'));
		}
		
		if (!empty($data['EvnQueue_id']) && false === $this->dbmodel->getFirstResultFromQuery("select EvnQueue_id from v_EvnQueue where EvnQueue_id  = :EvnQueue_id", $data)) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Не найдена запись о постановке в очередь'));
		}

		$this->load->helper('Reg');
		$resp = $this->dbmodel->writeTimetableGraf(array(
			'object' => 'TimetableGraf',
			'Person_id' => $data['Person_id'],
			'TimetableGraf_id' => $data['TimeTableGraf_id'],
			'EvnQueue_id' => $data['EvnQueue_id'],
			'pmUser_id' =>  $data['pmUser_id'],
			'session' =>  $data['session']
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение записей на прием по МО
	 */
	function TimeTableGrafbyMO_get() {
		$data = $this->ProcessInputData('TimeTableGrafbyMO');

		$resp = $this->dbmodel->getTimeTableGrafbyMO($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение атрибутов бирки по идентификатору
	 */
	public function TimeTableGrafById_get() {
		$data = $this->ProcessInputData('TimeTableGrafById');

		$resp = $this->dbmodel->getTimeTableGrafById($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание расписания врача
	 */
	function TimeTableGraf_post() {
		$data = $this->ProcessInputData('TimeTableGraf_post', null, true);

		$this->load->helper('Reg');
		$resp = $this->dbmodel->addTimetableGraf($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Изменение расписания врача
	 */
	function TimeTableGraf_put() {
		$data = $this->ProcessInputData('TimeTableGraf_put', null, true);

		$this->load->helper('Reg');
		$resp = $this->dbmodel->editTimetableGraf($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение статуса записи на прием
	 */
	function TimeTableGrafStatus_get() {
		$data = $this->ProcessInputData('TimeTableGrafStatus_get');

		$resp = $this->dbmodel->getTimeTableGrafStatus($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Изменение статуса записи на прием
	 */
	function TimeTableGrafStatus_put() {
		$data = $this->ProcessInputData('TimeTableGrafStatus_put', null, true);

		$resp = $this->dbmodel->setTimeTableGrafStatus($data);
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
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
	 * Получение данных об изменениях по биркам поликлиники
	 */
	function TimeTableGrafByUpdPeriod_get() {
		$data = $this->ProcessInputData('TimeTableGrafByUpdPeriod_get');

		if (date_create($data['TimeTableGraf_updend']) >= date_modify(date_create($data['TimeTableGraf_updbeg']), '+1 month')) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Период между TimeTableGraf_updbeg и TimeTableGraf_updend не должен превышать 1 мес'
			));
		}

		$resp = $this->dbmodel->getTimeTableGrafByUpdPeriod($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * Создание расписания в поликлинике
	 */
	function mcreateTTGSchedule_post(){

		$data = $this->ProcessInputData('mcreateTTGSchedule', null, true);
		if ($data === false) { return false; }
		
		$this->load->helper('Reg_helper');
		$data['CreateDateRange'] = array($data['CreateDateRange_begDate'], $data['CreateDateRange_endDate']);
		$data['fromApi'] = true;

		$resp = $this->dbmodel->createTTGSchedule($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$ttg_data = array();
		if (!empty($resp['ttg_list'])) {
			$ttg_data = $this->dbmodel->queryResult("
				select 
				    TimetableGraf_id,
					convert(varchar(10), TimetableGraf_begTime, 104)+' '+convert(varchar(5), TimetableGraf_begTime, 108) as TimetableGraf_begTime
				from v_TimetableGraf_lite (nolock)
				where
					TimetableGraf_id in ('".implode("','", $resp['ttg_list'])."')		
			", array());
		}

		if (!empty($resp['Error_Msg'])) {
			$resp = array(
				'success' => false,
				'error_code' => 6,
				'Error_Msg' => $resp['Error_Msg']
			);
		} else {
			if (isset($resp['Error_Msg'])) unset($resp['Error_Msg']);
			$resp = array('success'=> true, 'error_code' => 0, 'data' => $ttg_data);
		}

		$this->response($resp);
	}

	/**
	 * Записываемся на бирку
	 */
	function mApply_post(){

		// подготовка параметров по умолчанию
		$default_params = array(
			'EvnDirection_IsNeedOper'=>'off',
			'EvnDirection_IsCito'=>1,
			'EvnDirection_IsAuto'=>2,
			'EvnDirection_IsReceive'=>1,
			'LpuUnitType_SysNick'=>'polka',
			'MedPersonal_zid'=> NULL,
			'EvnDirection_Num'=> "0",
			'timetable'=>'TimetableGraf',
			'Lpu_id' => NULL,
			'EvnDirection_id' => NULL,
			'Diag_id' => NULL,
			'EvnDirection_Descr' => NULL,
			'LpuSection_id' => NULL
		);

		$this->load->model('EvnDirection_model');
		$data = $this->ProcessInputData('mApply', null, true);
		if ($data === false) return false;

		if ($this->dbmodel->checkPersonIsDeath($data) === true) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => "Нельзя записать на бирку умершего пациента."
			));
		}

		$timetable = $this->dbmodel->getApplyDataForApi($data);

		$data = array_merge($timetable, $data);
		$data = array_merge($default_params, $data); // извращенство конечно, но что поделать

		$this->load->helper('Reg');
		$data['Day'] = TimeToDay(time()) ;
		$data['object'] = 'TimetableGraf';
		$data['TimetableObject_id'] = 1;

		$resp = array();

		// Проверка наличия блокирующего примечания
		$this->load->model("Annotation_model", "anmodel");
		$anncheck = $this->anmodel->checkBlockAnnotation($data);

		if (is_array($anncheck) && count($anncheck)) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => "Запись на бирку невозможна. Смотрите примечание на бирку."
			));
		}

		$this->dbmodel->beginTransaction();

		try {

			if (!empty($data['Unscheduled'])) $this->createUnscheduled($data);

			// При записи на время из очереди определяем идентификатор направления
			if (isset($data['EvnQueue_id'])) {
				$this->load->model("Queue_model", "qmodel");
				$res = $this->model->getDirectionId($data['EvnQueue_id']);

				if ($res !== false) $data['EvnDirection_id'] = $res;
			}

			$apply_result = $this->dbmodel->Apply($data);
			if (!is_array($apply_result)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

			if ($apply_result['success']) {

				$data['EvnDirection_id'] = $apply_result['EvnDirection_id'];

				$resp = array(
					'object' => $apply_result['object'],
					'id' => $apply_result['id'],
					'EvnDirection_id' => $apply_result['EvnDirection_id']
				);

				if (!empty($apply_result['EvnDirection_TalonCode'])) {
					$resp['EvnDirection_TalonCode'] = $apply_result['EvnDirection_TalonCode'];
				}

				$this->load->model("TimetableGraf_model", "ttg_model");
				$this->ttg_model->countApply($data); // Подсчитаем факт использования расписания
				$this->genNotice($data); // Генерируем уведомления

				$resp['success'] = true;
				$this->dbmodel->commitTransaction();

			} elseif (isset($apply_result['queue'])) {

				array_walk($apply_result['queue'], 'ConvertFromWin1251ToUTF8');
				$resp = array(
					'Person_id' => $apply_result['Person_id'],
					'Server_id' => $apply_result['Server_id'],
					'PersonEvn_id' => $apply_result['PersonEvn_id'],
					'queue' => $apply_result['queue'],
				);

			} elseif (isset($apply_result['warning'])) {

				$resp = array(
					'Person_id' => $apply_result['Person_id'],
					'Server_id' => $apply_result['Server_id'],
					'PersonEvn_id' => $apply_result['PersonEvn_id'],
					'warning' => toUTF($apply_result['warning']),
				);

			} else {
				$resp['Error_Msg'] = toUTF($apply_result['Error_Msg']);
			};
		} catch (Exception $e) {
			$resp['Error_Msg'] = $e->getMessage();
		}

		if (empty($resp['success']) || !empty($resp['success']) && !$resp['success']) {

			$this->dbmodel->rollbackTransaction();

			if (!empty($resp['Error_Msg'])) {
				$resp = array_merge($resp, array(
					'error_code' => 6,
					'Error_Msg' => $resp['Error_Msg']
				));
			}

			$resp['success'] = false;
			$this->response($resp);
		}

		$resp = array_merge($resp, array('error_code' => 0));
		$this->response($resp);
	}

	/**
	 * выкрал из аналогичного контроллера для десктопа
	 */
	function genNotice($data)
	{
		$this->load->model("TimetableGraf_model", "ttg_model");
		// Находим инфу о бирке
		$ttgInfo = $this->ttg_model->getTTGInfo($data);
		//var_dump( $ttgInfo ); exit();
		if( !is_array($ttgInfo) || count($ttgInfo) == 0 ) {
			$this->ReturnError('Не удалось отправить уведомление о записи на прием');
			return false;
		}

		$noticeData = array(
			'autotype' => 1
		,'Lpu_rid' => $data['Lpu_id']
		,'MedPersonal_rid' => $ttgInfo[0]['MedPersonal_id']
		,'pmUser_id' => $data['pmUser_id']
		,'type' => 1
		,'title' => 'Запись на прием'
		,'text' => 'Пациент ' .$ttgInfo[0]['Person_Fio']. ' записан на прием на ' .$ttgInfo[0]['TimetableGraf_begTime']->format('d.m.Y H:i')
		);
		$this->load->model('Messages_model', 'Messages_model');
		$this->Messages_model->autoMessage($noticeData);

		return true;
	}

	/**
	 * Записываемся на бирку
	 */
	function mClear_post(){

		//$this->_args = array(
		//'cancelType' =>	'cancel',
		//'DirFailType_id' => NULL,
		//'EvnComment_Comment' => NULL,
		//'EvnStatusCause_id' => 1,
		//'LpuUnitType_SysNick' => 'polka',
		//'TimetableGraf_id' => 171793499
		//);

		$data = $this->ProcessInputData('mClear', null, true);

		if ($data === false) return false;

		switch ($data['LpuUnitType_SysNick']) {

			case 'polka':
				$data['object'] = 'TimetableGraf';
				break;
			case 'stac': case 'dstac': case 'hstac': case 'pstac':
				$data['object'] = 'TimetableStac';
				break;
			case 'parka':
				$data['object'] = 'TimetablePar';
				break;
			default:
				$this->response(array(
					'success' => false,
					'error_code' => 6,
					'Error_Msg' => toUTF("Неверно указаны входящие параметры!")
				));
				break;
		}

		$response = $this->dbmodel->Clear($data);

		if (!$response['success']) {
			$response['error_code'] = 6;
			$this->response($response);
		} else {
			$response = array_merge($response, array('error_code' => 0));
			$this->response($response);
		}
	}

    /**
     * Получение атрибутов бирки по идентификатору
     */
    public function getRecord_get() {
        $data = $this->ProcessInputData('getRecord');
        $resp = $this->dbmodel->getRecord($data);
        if (!is_array($resp)) {
            $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->response(array(
            'error_code' => 0,
            'data' => $resp
        ));
    }
}