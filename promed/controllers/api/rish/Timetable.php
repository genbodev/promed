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

class Timetable extends SwREST_Controller {
	protected  $inputRules = array(
		'mMakeTimetable' => array(
			array('field' => 'MedStaffFact_id','label' => 'Идентификатор места работы врача (если объект TimetableGraf)','rules' => '','type' => 'id'),
			array('field' => 'MedService_id','label' => 'Идентификатор службы (если объект TimetableMedService)','rules' => '','type' => 'id'),
			array('field' => 'UslugaComplexMedService_id','label' => 'Услуга на службе (если объект TimetableMedService)','rules' => '','type' => 'id'),
			array('field' => 'Resource_id','label' => 'Идентификатор ресурс (если объект TimetableResource)','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Идентификатор отделения (если объект TimetableStac)','rules' => '','type' => 'id'),
			array('field' => 'object', 'default' => 'TimetableGraf','label' => 'Объект расписания', 'rules' => '', 'type' => 'string'),
			array('field' => 'StartDay', 'label' => 'Начало расписания', 'rules' => '', 'type' => 'date'),
		),
		'mGetTimetableData' => array(
			array('field' => 'id','label' => 'Идентификатор бирки','rules' => 'required','type' => 'id'),
			array('field' => 'object', 'default'=> 'TimetableGraf', 'label' => 'Объект бирки','rules' => '','type' => 'string'),
		),
		'mGetRecordLpuSectionList' => array(
			array(
				'field' => 'LpuUnit_id',
				'label' => 'Идентификатор подразделения ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Date',
				'label' => 'Дата начала отображения расписания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
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
				'field' => 'LpuSectionProfile_id',
				'label' => 'Идентификатор профиля отделения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'includeDopProfiles',
				'label' => 'Признак необходимости учитывать доп. профили отделений',
				'rules' => 'trim',
				'type' => 'api_flag'
			),
			array(
				'field' => 'MedPersonal_FIO',
				'label' => 'ФИО врача',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'KLTown_Name',
				'label' => 'Населенный пункт',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'KLStreet_Name',
				'label' => 'Улица',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'KLHouse',
				'label' => 'Номер дома',
				'rules' => 'trim|mb_strtoupper',
				'type' => 'string'
			),
			array(
				'field' => 'ListForDirection',
				'label' => 'Показывать список для направлений',
				'rules' => '',
				'type' => 'api_flag'
			),
			array(
				'field' => 'WithoutChildLpuSectionAge',
				'label' => 'Скрыть детские отделения',
				'rules' => '',
				'type' => 'api_flag'
			)
		),
		'Apply' => array(
			array('field' => 'config', 'label' => 'Конфиг', 'rules' => '', 'type' => 'string'),
			array('field' => 'restriction_configs', 'label' => 'Конфиг', 'rules' => '', 'type' => 'string'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmuser_id', 'label' => 'Врач', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'TimetableGraf_id', 'label' => 'Идентификатор бирки поликлиники', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
		),
		'MakeTimetableForPortal' => array(
			array('field' => 'config', 'label' => 'Конфиг', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'string'),
			array('field' => 'allow_pay', 'label' => 'Флаг Разрешить платный прием', 'rules' => '', 'type' => 'string')
		),
		'CancelForPortal' => array(
			array('field' => 'pmuser_id', 'label' => 'Врач', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'TimetableGraf_id', 'label' => 'Идентификатор бирки поликлиники', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'allow_cancel_any_time', 'label' => 'Разрещить отменять записи в любое время', 'rules' => '', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth(array('securityLevel' => 'high'));
		$this->load->database();
	}

	/**
	 * Получаем расписание
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"id": "Идентификатор бирки",
				"date": "Дата бирки",
				"annotation": "коммент на  день (если есть, если нет то поле не возвращается)",
				"cells": [
					{
						"time": "время записи на бирку (null - если бирка в стационар)",
						"status": "статус бирки, может быть свободна, занята или просроченная (free || occupied || expired)",
						"annotation": "коммент на бирку (если есть, если нет то поле не возвращается)",
						"bedtype_id": "тип койки (если бирка в стационар)"
					}
				]
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"id": 43306,
					"date": "26.07.2018",
					"annotation": "ПРОСТО",
					"cells": [
						{
							"time": "08:00",
							"status": "free",
							"annotation": ""
						}
					]
	 * 			}
	 * 		}
	 * }
	 */
	function mMakeTimetable_get()
	{
		$data = $this->ProcessInputData('mMakeTimetable', null, true);
		$this->load->helper('Reg');
		$this->load->helper('text');

		$data['PanelID'] = 'TTGRecordPanel'; // надо чтобы было
		$tt = $data['object'];

		$timetable = array();

		try {

			$resp = null;
			switch ($tt) {

				case 'TimetableGraf':
					if (empty($data['MedStaffFact_id'])) throw new Exception('Не заполнено поле MedStaffFact_id', 6);
					$this->load->model('TimetableGraf_model', 'dbmodel');
					$resp = $this->dbmodel->getTimetableGrafForEdit($data);
					break;

				case 'TimetableMedService':

					$this->load->model('TimetableMedService_model', 'dbmodel');

					if (!empty($data['UslugaComplexMedService_id']) && empty($data['MedService_id'])) {
						$resp = $this->dbmodel->getTimetableUslugaComplexForEdit($data);
					} else if (!empty($data['MedService_id']) && empty($data['UslugaComplexMedService_id'])) {
						$resp = $this->dbmodel->getTimetableMedServiceForEdit($data);
					} else if (!empty($data['MedService_id']) && !empty($data['UslugaComplexMedService_id'])) {


						$count = $this->dbmodel->getTimetableUslugaComplexCount($data);

						// если по услуге службы бирок нет
						if (empty($count)) {
							// то ищем на службе
							$data['withoutUslugaComplexTimetable'] = true;
							$resp = $this->dbmodel->getTimetableMedServiceForEdit($data);
						} else {
							$resp = $this->dbmodel->getTimetableUslugaComplexForEdit($data);
						}

					} else if (empty($data['MedService_id']) && empty($data['UslugaComplexMedService_id'])) {
						throw new Exception('Не заполнены поля MedService_id или UslugaComplexMedService_id', 6);
					}

					break;

				case 'TimetableResource':

					if (empty($data['Resource_id'])) throw new Exception('Не заполнено поле Resource_id', 6);
					$this->load->model('TimetableResource_model', 'dbmodel');
					$resp = $this->dbmodel->getTimetableResourceForEdit($data);
					break;

				case 'TimetableStac':

					if (empty($data['LpuSection_id'])) throw new Exception('Не заполнено поле LpuSection_id', 6);
					$this->load->model('TimetableStac_model', 'dbmodel');
					$resp = $this->dbmodel->getTimetableStacForEdit($data);
					break;
			}

			if (!is_array($resp)) throw new Exception(self::HTTP_INTERNAL_SERVER_ERROR, null);
			//echo '<pre>',print_r($resp),'</pre>'; die();

			// комменты без срока действия
			$this->load->model("Annotation_model", "anmodel");
			$mpannotation = $this->anmodel->getRegAnnotation($data);

			$endless_annotations = "";

			if (!empty($mpannotation)) {
				foreach ($mpannotation as $a) {
					if (!empty($a['Annotation_Comment'])) $endless_annotations .= $a['Annotation_Comment'].';';
				}
			}

			$endless_annotations = rtrim($endless_annotations, ';');
			if (!empty($resp) && !empty($resp['data'])) {

				$dateTime = $this->dbmodel->getFirstRowFromQuery('select dbo.tzGetDate() as currDateTime')['currDateTime'];

				foreach ($resp['data'] as $day_id => $tt_day) {

					$day = array(
						'id' => $day_id,
						'date' =>  date('d.m.Y', DayMinuteToTime($day_id, 0))
					);

					// примечание на день
					$day['annotation'] = "";
					if (!empty($resp['descr']) && !empty($resp['descr'][$day_id])) {

						$day_annotations = $resp['descr'][$day_id];

						// особая логика
						if ($tt == 'TimetableStac') {

							if (!empty($day_annotations['LpuSectionDay_Descr'])) $day['annotation'] = $day_annotations['LpuSectionDay_Descr'];

						} else {
							foreach ($day_annotations as $a) {
								// особая логика
								if (!empty($a['Annotation_Comment'])) $day['annotation'] .= $a['Annotation_Comment'].';';
							}

							$day['annotation'] = rtrim($day['annotation'], ';');
						}
					}

					// комменты без срока действия на дне
					if (!empty($endless_annotations)) {
						if (!empty($day['annotation'])) $day['annotation'] .= ';'.$endless_annotations;
						else  $day['annotation'] = $endless_annotations;
					}

					// если пусто в примечаниях не будем их передавать
					if (empty($day['annotation'])) unset($day['annotation']);

					foreach ($tt_day as $cell) {

						$newCell = array();
						$newCell['id'] = $cell["{$tt}_id"];

						// время бирки
						if (!empty($cell["{$tt}_begTime"])) {
							$newCell['time'] = $cell["{$tt}_begTime"]->format('H:i');
						} else {
							$newCell['time'] = NULL;
						}

						// особая логика
						if ($tt == 'TimetableStac') {

							if (!empty($cell['Person_id']) || in_array($cell["{$tt}_id"], $resp['reserved'])) {
								$newCell['status'] = "occupied";
							} else { $newCell['status'] = "free"; }

							$newCell['bedtype_id'] = $cell["LpuSectionBedType_id"];
							$newCell['bedtype_name'] = $cell["LpuSectionBedType_Name"];

						} else {

							// статус
							if (!empty($cell['Person_id']) || in_array($cell["{$tt}_id"], $resp['reserved'])) {
								$newCell['status'] = "occupied";
							} else {

								if (!empty($cell["{$tt}_begTime"]) && $cell["{$tt}_begTime"] < $dateTime) {
									$newCell['status'] = "expired"; // если время прошло
								} else if (empty($cell["{$tt}_begTime"])) {
									$newCell['status'] = "occupied"; // если бирка без времени
								} else { $newCell['status'] = "free"; }
							}
						}

						// примечание на бирку
						$newCell['annotation'] = "";
						if (!empty($cell['annotation'])) {
							foreach ($cell['annotation'] as $a) {
								if (!empty($a['Annotation_Comment'])) $newCell['annotation'] .= $a['Annotation_Comment'] . ';';
							}

							$newCell['annotation'] = rtrim($newCell['annotation'], ';');
						}

						// если пусто в примечаниях не будем их передавать
						if (empty($newCell['annotation'])) unset($newCell['annotation']);
						$day['cells'][] = $newCell;
					}

					$timetable[] = $day;
				}
			}

			$cells_not_found = true;

			// удостоверяемся что бирки
			foreach ($timetable as $tt) {
				if (!empty($tt['cells'])) {
					$cells_not_found = false;
					break;
				}
			}

			if ($cells_not_found) throw new Exception("За указанный период бирки не найдены", 6);

		} catch (Exception $e) {
			$this->response(array('error_code' => $e->getCode(),'error_msg' => $e->getMessage()));
		}

		$this->response(array('error_code' => 0,'data' => $timetable));
	}

	/**
	 * Получаем расписание
	 */
	function mGetTimetableData_get()
	{
		$data = $this->ProcessInputData('mGetTimetableData');
		$this->load->model("Timetable_model");

		$data["{$data['object']}_id"] = $data['id'];

		$resp = $this->Timetable_model->getTimetableData($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение списка отделений для переданного подразделения для мастера записи
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"Lpu_id": "Идентификатор ЛПУ",
				"LpuSection_id": "Идентификатор отделения",
				"LpuSection_Name": "Наименования отделения",
				"LpuUnitType_id": "Идентификатор отделения",
				"LpuSection_Descr": "Описание",
				"LpuSectionProfile_Name": "Наименования профиля отделения",
				"MainLpuSectionProfile_Name": "Наименования основного профиля отделения",
				"LpuSectionAge_id": "Идентификатор возрастной группы",
				"LpuSectionAge_Name": "Наименование возрастной группы",
				"LpuSectionProfile_id": "Идентификатор профиля отделения",
				"LpuUnit_id": "Идентификатор подотделения",
				"LpuSectionType_Name": "Наименования типа отделения"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"Lpu_id": "10010833",
					"LpuSection_id": "99560020906",
					"LpuSection_Name": "гистологии. стац",
					"LpuUnitType_id": "1",
					"LpuSection_Descr": null,
					"LpuSectionProfile_Name": "гистологии",
					"MainLpuSectionProfile_Name": "гистологии",
					"LpuSectionAge_id": null,
					"LpuSectionAge_Name": null,
					"LpuSectionProfile_id": "117",
					"LpuUnit_id": "99560004667",
					"LpuSectionType_Name": "Круглосуточный стационар"
	 * 			}
	 * 		}
	 * }
	 */
	function mGetRecordLpuSectionList_get() {

		$data = $this->ProcessInputData('mGetRecordLpuSectionList');

		// ре-маппинг параметров
		$remap_fields = array(
			'Lpu_id' => 'Filter_Lpu_id',
			'LpuSection_id' => 'Filter_LpuSection_id',
			'LpuSectionProfile_id' => 'Filter_LpuSectionProfile_id',
			'includeDopProfiles' => 'Filter_includeDopProfiles',
			'MedPersonal_FIO' => 'Filter_MedPersonal_FIO',
			'KLTown_Name' => 'Filter_KLTown_Name',
			'KLStreet_Name' => 'Filter_KLStreet_Name',
			'KLHouse' => 'Filter_KLHouse',
		);

		// ремапим
		foreach ($remap_fields as $field_origin => $field_system) {
			if (isset($data[$field_origin])) {
				$data[$field_system] = $data[$field_origin];
				unset($data[$field_origin]);
			}
		}

		$data['fromApi'] = true;

		if (empty($data['Date'])) {
			$data['Date'] = date("Y-m-d");
		}
		$this->load->helper('Reg');	
		$this->load->model("Reg_model");
		$response = $this->Reg_model->getRecordLpuSectionListAPI($data);

		$this->response(array('error_code' => 0,'data' => $response));
	}


	/**
	 * @OA\get(
	path="/api/rish/Timetable/Apply",
	tags={"Timetable"},
	summary="Запись на бирку",

	@OA\Parameter(
	name="Lpu_id",
	in="query",
	description="Идентификатор МО",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="pmuser_id",
	in="query",
	description="Врач",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="TimetableGraf_id",
	in="query",
	description="Идентификатор бирки поликлиники",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Person_id",
	in="query",
	description="Идентификатор человека",
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
	property="success",
	description="Результат выполнения",
	type="string",

	)
	,
	@OA\Property(
	property="error_msg",
	description="Текст ошибки, появляется только при наличии ошибки",
	type="string",

	)

	)
	)

	)
	 */
	function Apply_get() {
		$data = $this->ProcessInputData('Apply');
		$this->load->model("Timetable_model");
		$response = $this->Timetable_model->ApplyPortal($data);
		$this->response(array('error_code' => 0,'data' => $response));
	}
	/**
	@OA\get(
	path="/api/rish/Timetable/MakeTimetableForPortal",
	tags={"Timetable"},
	summary="Получение расписания",

	@OA\Parameter(
	name="config",
	in="query",
	description="Конфиг",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="MedStaffFact_id",
	in="query",
	description="Идентификатор врача",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="allow_pay",
	in="query",
	description="Флаг Разрешить платный прием",
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
	property="data",
	description="Данные",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="date",
	description="Дата",
	type="string",

	)
	,
	@OA\Property(
	property="datetime",
	description="Дата/время",
	type="string",

	)
	,
	@OA\Property(
	property="date_readable",
	description="Число месяц",
	type="string",

	)
	,
	@OA\Property(
	property="date_readable_full",
	description="Число месяц год",
	type="string",

	)
	,
	@OA\Property(
	property="day_of_week",
	description="день недели сокращенно",
	type="string",

	)
	,
	@OA\Property(
	property="day_of_week_full",
	description="день недели полностью",
	type="string",

	)
	,
	@OA\Property(
	property="is_today",
	description="признак Бирка на сегодня",
	type="string",

	)
	,
	@OA\Property(
	property="day_num",
	description="Число",
	type="string",

	)
	,
	@OA\Property(
	property="month_add",
	description="месяц",
	type="string",

	)
	,
	@OA\Property(
	property="id",
	description="Идентификатор",
	type="string",

	)
	,
	@OA\Property(
	property="description",
	description="Примечание",
	type="string",

	)
	,
	@OA\Property(
	property="timetable",
	description="Массив с бирками",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="TimetableGraf_id",
	description="Идентификатор бирки",
	type="integer",

	)
	,
	@OA\Property(
	property="Person_id",
	description="Справочник идентификаторов человека, Идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="TimeTableGraf_Day",
	description="Расписание, бирки по поликлинике, день",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableGraf_begTime",
	description="Дата и время бирки",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableType_id",
	description="Тип бирки",
	type="integer",

	)
	,
	@OA\Property(
	property="pmUser_updID",
	description="Идентификатор пользователя создавшего бирку",
	type="integer",

	)
	,
	@OA\Property(
	property="IsFuture",
	description="
	 * 0 - бирка на сегодня
	 * 1 - бирка на будущее",
	type="integer",

	)
	,
	@OA\Property(
	property="DateDiff",
	description="Разница в днях от сегодняшней даты",
	type="string",

	)
	,
	@OA\Property(
	property="TimetableLock_lockTime",
	description="Время на которое бирка заблокирована",
	type="string",

	)
	,
	@OA\Property(
	property="time",
	description="Время бирки",
	type="string",

	)
	,
	@OA\Property(
	property="date",
	description="Дата бирки",
	type="string",

	)
	,
	@OA\Property(
	property="datetime_readable_desc",
	description="день недели, число, месяц",
	type="string",

	)
	,
	@OA\Property(
	property="datetime_readable",
	description="число, месяц, день недели, время",
	type="string",

	)
	,
	@OA\Property(
	property="is_free",
	description="признак Бирка свободна",
	type="string",

	)
	,
	@OA\Property(
	property="my_record",
	description="Признак На бирку записан я",
	type="string",

	)
	,
	@OA\Property(
	property="annot",
	description="Примечание",
	type="string",

	)
	,
	@OA\Property(
	property="MedStaffFactDay_Descr",
	description="Примечание от врача",
	type="string",

	)
	,
	@OA\Property(
	property="class",
	description="Класс бирки, нужен для раскрашивания бирок",
	type="string",

	)
	,
	@OA\Property(
	property="tooltip",
	description="Всплывающее сообщение",
	type="string",

	)

	)

	)
	,
	@OA\Property(
	property="timetable_is_free",
	description="Признак возможности записи на бирку",
	type="string",

	)

	)

	)

	)
	)

	)
	 */

	function MakeTimetableForPortal_get() {
		$data = $this->ProcessInputData('MakeTimetableForPortal');
		$this->load->model("Timetable_model");
		$response = $this->Timetable_model->MakeTimetable($data['MedStaffFact_id'], false, $data);
		$this->response(array('error_code' => 0,'data' => $response));
	}

	/**
	@OA\get(
	path="/api/rish/Timetable/CancelForPortal",
	tags={"Timetable"},
	summary="Отмена записи на бирку",

	@OA\Parameter(
	name="pmuser_id",
	in="query",
	description="Врач",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="TimetableGraf_id",
	in="query",
	description="Идентификатор бирки поликлиники",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="allow_cancel_any_time",
	in="query",
	description="Разрещить отменять записи в любое время",
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
	property="success",
	description="Результат выполнения",
	type="string",

	)
	,
	@OA\Property(
	property="error_msg",
	description="Сообщение об ошибке(если имеется)",
	type="string",

	)

	)
	)

	)
	 */
	function CancelForPortal_get()  {
		$data = $this->ProcessInputData('CancelForPortal');
		$this->load->model("Timetable_model");
		$response = $this->Timetable_model->Cancel($data['TimetableGraf_id'], false, $data);
		$this->response(array('error_code' => 0,'data' => $response));
	}
}
